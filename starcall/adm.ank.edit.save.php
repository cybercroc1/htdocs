<?php	
include("starcall/session.cfg.php");
include("starcall/conn_string.cfg.php");
set_error_handler ("my_error_handler");
extract($_POST);

if($_SESSION['user']['rw_ank']<>'w') exit();

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

if(isset($hide_pages)) $_SESSION['adm']['project']['hide_pages']='on'; else unset($_SESSION['adm']['project']['hide_pages']);
$error='';
$warning='';

if($frm_submit=='save' or $frm_submit=='continue') {
	echo "Сохранение анкеты<hr>";
	//ОШИБКИ
	foreach($obj_id as $idx=>$id) {
		//ОШИБКА: зарезервированное слово "Квота"
		if($type[$idx]=='obj' and substr($obj_type_id[$idx],0,5)=='q_ls_' and  $text_name[$idx]=="Квота") {
			$error.="<font color=red>ОШИБКА: Нельзя называть вопрос зарезервированным словом \"Квота\"</font><br>";
		}		
	}
	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='save';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='Сохранить';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='none';
		</script>";	
		exit();
	}
	//ПРЕДУПРЕЖДЕНИЯ
	//список сохраняемых квот $quoted
	$new_quoted_ids=array();
	$new_quoted=array();
	if(isset($quoted)) {
		$i=0; foreach($quoted as $idx => $fuck) {
			if(!isset($del_obj[$obj_id[$idx]])) {$i++; $new_quoted_ids[$i]=$obj_id[$idx]; $new_quoted[$obj_id[$idx]]=$obj_id[$idx];}		
	}}	
	//перебираем старые объекты
	$old_quoted_ids=array();
	$old_quoted=array();
	$old_quote_num=0;
	$q=OCIParse($c,"select o.id,o.num,o.quote_num,o.field_id,o.depend_of_field,o.page_num from STC_OBJECTS o
where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
order by num");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
			//список старых квотируемых объектов
			if(OCIResult($q,"QUOTE_NUM")<>'') {
			$old_quote_num++;
			$old_quoted_ids[$old_quote_num]=OCIResult($q,"ID");
			$old_quoted[OCIResult($q,"ID")]=OCIResult($q,"ID");	
			if(!isset($new_quoted[OCIResult($q,"ID")])) {$del_quoted[OCIResult($q,"ID")]=OCIResult($q,"ID");	$changed_quote='y';}
		}
	}
	foreach ($new_quoted_ids as $i => $id) {
		if(count($new_quoted_ids)==count($old_quoted_ids) and $new_quoted_ids[$i]<>$old_quoted_ids[$i]) $changed_quote='y';
		if(!isset($old_quoted[$id])) {if($id<>'new') {$add_quoted[$id]=$id;} $changed_quote='y';} //список добавленных квотируемых полей для индексации
	}	

	//ПРЕДУПРЕЖДЕНИЕ: Удаление объектов
	if((isset($del_grp) or isset($del_obj)) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Будет удалено один или несколько объектов анкеты.</font><br>';
	}
	//
	//ПРЕДУПРЕЖДЕНИЕ: изменились квоты.
	if(isset($changed_quote) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Изменено количество или порядок квотируемых вопросов. Если продолжить сохранение, то проект будет приостановлен, а квоты по вопросам перестроить и прописать значения заново.</font><br>';
	}
	//
	if($warning<>'') {
		echo $warning;
		echo "<script>
		parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='".$warning."';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='continue';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='Продолжить';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='';
		</script>";	
		exit();
	}
	
	//СОХРАНЕНИЕ

	$ins_page=OCIParse($c,"insert into STC_OBJECT_PAGE (id, project_id, num, message) 
	values (SEQ_STC_PAGE_ID.nextval,".$project_id.",:num,:message)
	returning id into :page_id");

	$upd_page=OCIParse($c,"update STC_OBJECT_PAGE set num=:num,message=:message where project_id=".$project_id." and id=:page_id");
	
	$ins_grp=OCIParse($c,"insert into STC_OBJECT_GROUP (id, project_id,page_id, message,quest_ord_type,num,num_on_page)
					values(SEQ_STC_OBJECT_GROUP_ID.nextval,".$project_id.",:page_id,:message,:ord_type,:num,:g_on_p) 
					returning id into :grp_id");	
	
	$upd_grp=OCIParse($c,"update STC_OBJECT_GROUP set page_id=:page_id, message=:message, quest_ord_type=:ord_type,num=:num,num_on_page=:g_on_p
					where id=:grp_id and project_id=".$project_id);
	
	$ins_obj=OCIParse($c,"insert into STC_OBJECTS (id, project_id, field_id, obj_type_id,group_id,message,answ_ord_type,num,must,quote_num,page_num,quest_num,num_on_group)
					values(SEQ_STC_OBJECT_ID.nextval,".$project_id.",:field_id,:type_id,:group_id,:message,:ord_type,:num,:must,:quote_num,:page_num,:quest_num,:o_on_g) 					returning id into :id");

	$upd_obj=OCIParse($c,"update STC_OBJECTS set obj_type_id=nvl(:type_id,obj_type_id), group_id=:group_id, message=:message, answ_ord_type=:ord_type, num=:num, must=:must, quote_num=:quote_num ,page_num=:page_num, quest_num=:quest_num, num_on_group=:o_on_g
					where id=:obj_id and project_id=".$project_id);

	$ins_field=OCIParse($c,"insert into STC_FIELDS (id, project_id, text_name, code_name, ord, src_type_id, must, quoted)
					values(SEQ_STC_FIELDS_ID.nextval,".$project_id.",
					nvl(:text_name,'Вопрос-'||SEQ_STC_FIELDS_ID.nextval),nvl(:code_name,'Q'||SEQ_STC_FIELDS_ID.nextval),:ord,2, :must, :quoted) returning id into :field_id");

	$upd_field=OCIParse($c,"update STC_FIELDS set text_name=:text_name, code_name=:code_name, ord=:ord, quoted=:quoted, must=:must
					where id=(select field_id from STC_OBJECTS where id=:obj_id and project_id=".$project_id.")");
	
	//==УДАЛЕНИЕ
	if(isset($del_page)) { //удаление страниц
		//переносим все группы с этой страницы на предыдущую
		$upd=OCIParse($c,"--переносим все группы с этой страницы на предыдущую
		update STC_OBJECT_GROUP ug set (ug.page_id,ug.num_on_page)=
		--получаем ID пред. страницы, максимальный номер группы на пред.странице
		(select max(p.id),nvl(max(g.num_on_page),0)+ug.num_on_page from STC_OBJECT_PAGE p, STC_OBJECT_GROUP g 
			where p.project_id=".$project_id." 
		and g.project_id(+)=".$project_id." and g.page_id(+)=p.id
		and p.num=
		--с максимальным номером
		(select max(p.num) from STC_OBJECT_PAGE p 
		where p.project_id=".$project_id." and p.num<
		--меньше номера текущей страницы
		(select p.num from STC_OBJECT_PAGE p
		where p.project_id=".$project_id." and p.id=:page_id)))
		--обновляем страницу всех групп ID удаляемой страницы
		where ug.project_id=".$project_id." and ug.page_id=:page_id");
		//удаляем страницу
		$del=OCIParse($c,"delete from STC_OBJECT_PAGE p where p.project_id=".$project_id." and p.id=:page_id");
		foreach($del_page as $id) {
			OCIBindByName($upd,":page_id",$id);
			OCIExecute($upd,OCI_DEFAULT);
			OCIBindByName($del,":page_id",$id);
			OCIExecute($del,OCI_DEFAULT);
			echo "Удалена страница<hr>";
		}
		OCICommit($c); //exit();
	}	
	if(isset($del_grp)) { //удаление групп
		//переносим все объекты с этой группы на предыдущую
		$upd=OCIParse($c,"update STC_OBJECTS oo set (oo.group_id,oo.num_on_group)=
		--получаем ID предыдущей группы, максимальный номер объекта в группе 
		(select max(g.id),nvl(max(o.num_on_group),0)+oo.num_on_group from STC_OBJECT_GROUP g, STC_OBJECTS o
		where g.project_id=".$project_id." 
		and o.project_id(+)=".$project_id." and o.group_id(+)=g.id
		and g.num= 
		--с максимальным номером меньше текущей группы
		(select max(g.num) from STC_OBJECT_GROUP g
		where g.project_id=".$project_id." and g.num<
		--номер текущей группы
		(select g.num from STC_OBJECT_GROUP g
		where g.project_id=".$project_id." and g.id=:grp_id)))
		where oo.project_id=".$project_id." and oo.group_id=:grp_id");		
		$del=OCIParse($c,"delete from STC_OBJECT_GROUP g where g.project_id=".$project_id." and g.id=:grp_id");
		foreach($del_grp as $id) {
			OCIBindByName($upd,":grp_id",$id);
			OCIExecute($upd,OCI_DEFAULT);
			OCIBindByName($del,":grp_id",$id);
			OCIExecute($del,OCI_DEFAULT);		
			echo "Удалена группа<br>";
		}
	}	
	if(isset($del_obj) and count($del_obj)>0) { //удаление объектов
			//удаляем объекты, кроме вопросов 
			$del=OCIParse($c,"delete from STC_OBJECTS where id in (".implode(",",$del_obj).") and project_id=".$project_id." and obj_type_id not like 'q_%'");
			OCIExecute($del,OCI_DEFAULT);
			//вопросы помечаем, как удаленные
			$upd=OCIParse($c,"update STC_OBJECTS set deleted=sysdate where id in (".implode(",",$del_obj).") and project_id='".$project_id."' and obj_type_id like 'q_%'");
			OCIExecute($upd,OCI_DEFAULT);
			//поля помечаем, как удаленные
			$upd=OCIParse($c,"update STC_FIELDS set deleted=sysdate where id in (
			select field_id from STC_OBJECTS 
			where id in (".implode(",",$del_obj).") and project_id=".$project_id."
			)");
			OCIExecute($upd,OCI_DEFAULT);
			//ответы помечаем, как удаленные
			$upd=OCIParse($c,"update STC_LIST_VALUES set deleted=sysdate where object_id in (".implode(",",$del_obj).") and project_id=".$project_id);
			OCIExecute($upd,OCI_DEFAULT);
	}
	//==

	$p=0;$g=0;$g_on_p=0;$o=0;$o_on_g=0;$q=0; //номера страницы, группы, объекты, вопросы
	$cur_page_id=''; //текущая страница
	$cur_grp_id=''; //текущая группа вопросов
	$cur_obj_type_id='';
	$perv_obj_type_id=''; //тип предыдущего объекта
	$nl_replace_ser=array(chr(10),chr(13));
	$nl_replace_rep=array(' ','');
	$quote_num=0;
	
	$i=0; foreach($obj_id as $idx => $id) {$i++; 	//ключ - индекс объекта на странице редактирования; значения: для существующих - ID объекта; для новых: new

		//СТРАНИЦА
		if($type[$idx]=='page') {
			$p++;
			$g_on_p=0;
			$o_on_g=0;
			$cur_obj_type_id='page';
			//страница связана с группой, поэтому прибавляем счетчики группы
			$g++;
			$g_on_p++;
			$o_on_g=0;
			if($id=='new') { //новая страница
				OCIBindByName($ins_page,":num",$p);
				OCIBindByName($ins_page,":message",$message[$idx]);
				OCIBindByName($ins_page,":page_id",$cur_page_id,16);
				OCIExecute($ins_page,OCI_DEFAULT);	
				//добавляем первую группу на странице
				$cur_obj_type_id='group';
				OCIBindByName($ins_grp,":page_id",$cur_page_id);
				OCIBindByName($ins_grp,":message",$message[$idx]);
				OCIBindByName($ins_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_grp,":num",$g);
				OCIBindByName($ins_grp,":g_on_p",$g_on_p);
				OCIBindByName($ins_grp,":grp_id",$cur_grp_id,16);
				OCIExecute($ins_grp,OCI_DEFAULT);
											
			}
			else { //существующая страница
				$cur_page_id=$obj_id[$idx];
				OCIBindByName($upd_page,":num",$p);
				OCIBindByName($upd_page,":message",$message[$idx]);
				OCIBindByName($upd_page,":page_id",$id);
				OCIExecute($upd_page,OCI_DEFAULT);
				//обновляем связанную со страницей группу
				$cur_obj_type_id='group';
				$cur_grp_id=$page_group[$idx];
				OCIBindByName($upd_grp,":page_id",$cur_page_id);
				OCIBindByName($upd_grp,":message",$message[$idx]);
				OCIBindByName($upd_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_grp,":num",$g);
				OCIBindByName($upd_grp,":g_on_p",$g_on_p);
				OCIBindByName($upd_grp,":grp_id",$cur_grp_id);
				OCIExecute($upd_grp,OCI_DEFAULT);				
			}	
			$perv_obj_type_id='page';
		}
		//ГРУППА
		if($type[$idx]=='group') {
			$g++;
			$g_on_p++;
			$o_on_g=0;
			$cur_obj_type_id='group';
			if($id=='new') { //новая группа
				OCIBindByName($ins_grp,":page_id",$cur_page_id);
				OCIBindByName($ins_grp,":message",$message[$idx]);
				OCIBindByName($ins_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_grp,":num",$g);
				OCIBindByName($ins_grp,":g_on_p",$g_on_p);
				OCIBindByName($ins_grp,":grp_id",$cur_grp_id,16);
				OCIExecute($ins_grp,OCI_DEFAULT);
			}					
			else { //существующая группа
				$cur_grp_id=$obj_id[$idx];
				OCIBindByName($upd_grp,":page_id",$cur_page_id);
				OCIBindByName($upd_grp,":message",$message[$idx]);
				OCIBindByName($upd_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_grp,":num",$g);
				OCIBindByName($upd_grp,":g_on_p",$g_on_p);
				OCIBindByName($upd_grp,":grp_id",$id);
				OCIExecute($upd_grp,OCI_DEFAULT);				
			}					
			$perv_obj_type_id='group';
		}
		//ОБЪЕКТ
		if($type[$idx]=='obj' and $obj_type_id[$idx]<>'') { //игнорим объекты с пустым типом
			$o++;
			$o_on_g++;
			$cur_obj_type_id=$obj_type_id[$idx];
			if(isset($quoted[$idx])) {$quote_num++; $quoted[$idx]=$quote_num;} else $quoted[$idx]='';
			$field_id='';
			!isset($order_type[$idx])?$order_type[$idx]='':NULL;
			!isset($must[$idx])?$must[$idx]='':NULL;			
			if(substr($obj_type_id[$idx],0,5)<>'q_ls_') $quoted[$idx]=''; //квота применима только для списковых вопросов
						
			if($id=='new') { //новый объект
				if((isset($newpage[$idx]) and $perv_obj_type_id<>'page') or ($i==1 and $cur_obj_type_id<>'page')) { //добавляем страницу и группу, если стоит галочка "нов.стр"
					$p++;
					$g_on_p=0;
					$o_on_g=0;
					$cur_obj_type_id='page';
					//страница связана с группой, поэтому прибавляем счетчики группы
					$g++;
					$g_on_p++;
					$o_on_g=0;
					$tmp_mess='';
					$tmp_order_type='По порядку';					
					OCIBindByName($ins_page,":num",$p);
					OCIBindByName($ins_page,":message",$tmp_mess);
					OCIBindByName($ins_page,":page_id",$cur_page_id,16);
					OCIExecute($ins_page,OCI_DEFAULT);	
					//добавляем первую группу на странице
					$cur_obj_type_id='group';
					OCIBindByName($ins_grp,":page_id",$cur_page_id);
					OCIBindByName($ins_grp,":message",$tmp_mess);
					OCIBindByName($ins_grp,":ord_type",$tmp_order_type);
					OCIBindByName($ins_grp,":num",$g);
					OCIBindByName($ins_grp,":g_on_p",$g_on_p);
					OCIBindByName($ins_grp,":grp_id",$cur_grp_id,16);
					OCIExecute($ins_grp,OCI_DEFAULT);
				}
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //для вопросов обновлям поле в БД				
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //удаляем переносы строки
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);					
					OCIBindByName($ins_field,":text_name",$text_name[$idx]);
					OCIBindByName($ins_field,":code_name",$code_name[$idx]);
					OCIBindByName($ins_field,":ord",$o);
					OCIBindByName($ins_field,":must",$must[$idx]);
					OCIBindByName($ins_field,":quoted",$quoted[$idx]);
					OCIBindByName($ins_field,":field_id",$field_id,16);
					OCIExecute($ins_field,OCI_DEFAULT);								
				}
				else {$q_num='';}				
				OCIBindByName($ins_obj,":field_id",$field_id);
				OCIBindByName($ins_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($ins_obj,":group_id",$cur_grp_id);
				OCIBindByName($ins_obj,":message",$message[$idx]);
				OCIBindByName($ins_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_obj,":must",$must[$idx]);
				OCIBindByName($ins_obj,":quote_num",$quoted[$idx]);
				OCIBindByName($ins_obj,":num",$o);
				OCIBindByName($ins_obj,":page_num",$p);
				OCIBindByName($ins_obj,":quest_num",$q_num);
				OCIBindByName($ins_obj,":o_on_g",$o_on_g);
				OCIBindByName($ins_obj,":id",$id,16);
				OCIExecute($ins_obj,OCI_DEFAULT);
				if($quoted[$idx]<>'') $add_quoted[$id]=$id;					
			}
			else { //существующий объект
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //для вопросов обновлям поле в БД
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //удаляем переносы строки
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);
					OCIBindByName($upd_field,":text_name",$text_name[$idx]);
					OCIBindByName($upd_field,":code_name",$code_name[$idx]);
					OCIBindByName($upd_field,":must",$must[$idx]);
					OCIBindByName($upd_field,":quoted",$quoted[$idx]);
					OCIBindByName($upd_field,":ord",$o);
					OCIBindByName($upd_field,":obj_id",$id);
					OCIExecute($upd_field,OCI_DEFAULT);
				}
				else {$q_num='';}
				OCIBindByName($upd_obj,":obj_id",$id);
				OCIBindByName($upd_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($upd_obj,":group_id",$cur_grp_id);
				OCIBindByName($upd_obj,":message",$message[$idx]);
				OCIBindByName($upd_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_obj,":num",$o);
				OCIBindByName($upd_obj,":must",$must[$idx]);
				OCIBindByName($upd_obj,":quote_num",$quoted[$idx]);
				OCIBindByName($upd_obj,":page_num",$p);
				OCIBindByName($upd_obj,":quest_num",$q_num);
				OCIBindByName($upd_obj,":o_on_g",$o_on_g);
				OCIExecute($upd_obj,OCI_DEFAULT);					
			}						
			$perv_obj_type_id='obj';
		}
	}	
	//если последний объект не конец опроса, то добавляем успешный конец
	if(substr($cur_obj_type_id,0,4)<>'end_') {
		$field_id='';
		$obj_type_id='end_norm';
		$message='';
		$order_type='';
		$must='';
		$quoted='';
		$o++;
		$o_on_g++;
		$q_num='';
		OCIBindByName($ins_obj,":id",$id,16);
		OCIBindByName($ins_obj,":field_id",$field_id);
		OCIBindByName($ins_obj,":type_id",$obj_type_id);
		OCIBindByName($ins_obj,":group_id",$cur_grp_id);
		OCIBindByName($ins_obj,":message",$message);
		OCIBindByName($ins_obj,":ord_type",$order_type);
		OCIBindByName($ins_obj,":must",$must);
		OCIBindByName($ins_obj,":quote_num",$quoted);
		OCIBindByName($ins_obj,":num",$o);
		OCIBindByName($ins_obj,":page_num",$p);
		OCIBindByName($ins_obj,":quest_num",$q_num);
		OCIBindByName($ins_obj,":o_on_g",$o_on_g);
		OCIExecute($ins_obj,OCI_DEFAULT);				
		$perv_obj_type_id='obj';
	}

	//КВОТЫ. Индексируем добавленные квотируемые поля
	if(isset($add_quoted)) {
		/*OCIExecute(OCIParse($c,"insert into STC_QST_INDEXES (id,project_id,field_id,object_id,value)
select SEQ_STC_INDEX_ID.nextval, a.* from 
(select distinct v.project_id, o.field_id, v.object_id, quote_key from STC_LIST_VALUES v, STC_OBJECTS o
where v.project_id=".$project_id." and v.object_id in (".implode(",",$add_quoted).") and v.deleted is null
and o.id=v.object_id
minus
select i.project_id, i.field_id,i.object_id,i.value from STC_QST_INDEXES i
where i.project_id=".$project_id." and i.object_id in (".implode(",",$add_quoted).")
) a"),OCI_DEFAULT);
		OCICommit($c);
		echo "Добавлены индексы квот<hr>";*/		
	}
	//КВОТЫ. Удаляем квоты и индексы для отмененных квот
	if(isset($del_quoted)) {
		OCIExecute(OCIParse($c,"delete from STC_QST_QUOTES q where q.project_id=".$project_id." 
		and index_id in (select id from STC_QST_INDEXES where project_id=".$project_id." and object_id in (".implode(",",$del_quoted)."))"));
		OCIExecute(OCIParse($c,"delete from STC_QST_INDEXES where project_id=".$project_id." and object_id in (".implode(",",$del_quoted).")"));
		OCICommit($c);
		echo "Удалены индексы удаленных квот<hr>";	
	}
	
	//обновляем статус, если изменились квотируемые поля
	if(isset($changed_quote)) {
		$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes', QST_STAT_BROKEN='yes', status='Приостановлен' where id='".$project_id."'");
		OCIExecute($upd,OCI_DEFAULT);
	}	
	OCICommit($c);

	echo "<font color=green>СОХРАНЕНО</font><br>";
	echo "<script>
	parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='<font color=green>СОХРАНЕНО</font>';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='saved';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='Сохранено';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.admAnkEditFirstFrame.location.reload();
	parent.admBottomFrame.admAnkEditSecondFrame.location.reload();
	</script>";
	if(isset($changed_quote)) echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>"; 
}

function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<font color=red><br>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."<br></font>";
	echo "<script>parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='<font color=red>ОШИБКА: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
	parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value=Сохранить;
	parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='save';</script>";
	exit();
}
?>
