<?php	
include("../../starcall_conf/session.cfg.php");
include("../../starcall_conf/conn_string.cfg.php");
set_error_handler ("my_error_handler");
extract($_POST);

$error='';
$warning='';
if($frm_submit=='save' or $frm_submit=='continue') {
	//ОШИБКИ
	//$error='ошибка';
	//ПРЕДУПРЕЖДЕНИЕ: Удаление объектов
	if((isset($del_grp) or isset($del_obj)) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Будет удалено один или несколько объектов анкеты.</font><br>';
	}
	//


	//список старых квотируемых объектов
	$q=OCIParse($c,"select o.id from STC_FIELDS f, STC_OBJECTS o
where f.project_id=".$project_id." and f.src_type_id=2 and quoted is not null and f.deleted is null
and o.project_id=".$project_id." and o.deleted is null and o.field_id=f.id
order by f.ord");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
	}
	
	//ПРЕДУПРЕЖДЕНИЕ: Добалено квотируемое поле
	if(isset($added_quote_obj) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Добавлены квотируемые вопросы.</font><br>';
	}
	//
	//ПРЕДУПРЕЖДЕНИЕ: Удалено квотируемое поле
	if(isset($deleted_quote_obj) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Удалены квотируемые вопросы.</font><br>';
	}
	//
	//ПРЕДУПРЕЖДЕНИЕ: Изменены квотируемые поля
	if(isset($changed_qoute_obj) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Изменены квотируемые вопросы.</font><br>';
	}
	//	
	
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
	$upd_grp=OCIParse($c,"update STC_OBJECT_GROUP set page_num=:page, name=:name, quest_ord_type=:ord_type,num=:num,num_on_page=:g_on_p
					where id=:grp_id and project_id='".$project_id."'");
					
	$ins_grp=OCIParse($c,"insert into STC_OBJECT_GROUP (id, project_id, page_num, name,quest_ord_type,num,num_on_page)
					values(SEQ_STC_OBJECT_GROUP_ID.nextval,'".$project_id."',:page,:name,:ord_type,:num,:g_on_p) returning id into :grp_id");

	$upd_obj=OCIParse($c,"update STC_OBJECTS set obj_type_id=nvl(:type_id,obj_type_id), group_id=:group_id, message=:message, answ_ord_type=:ord_type, num=:num, must=:must,obj_full_num=:full_num, quest_num=:quest_num, num_on_group=:o_on_g
					where id=:obj_id and project_id='".$project_id."'");
	$ins_obj=OCIParse($c,"insert into STC_OBJECTS (id, project_id, field_id, obj_type_id,group_id,message,answ_ord_type,num,must,obj_full_num,quest_num,num_on_group)
					values(SEQ_STC_OBJECT_ID.nextval,'".$project_id."',:field_id,:type_id,:group_id,:message,:ord_type,:num,:must,:full_num,:quest_num,:o_on_g)");

	$upd_field=OCIParse($c,"update STC_FIELDS set text_name=:text_name, code_name=:code_name, ord=:ord, quoted=:quoted, must=:must
					where id=(select field_id from STC_OBJECTS where id=:obj_id and project_id='".$project_id."')");
					
	$ins_field=OCIParse($c,"insert into STC_FIELDS (id, project_id, text_name, code_name, ord, src_type_id, must, quoted)
					values(SEQ_STC_FIELDS_ID.nextval,'".$project_id."',
					nvl(:text_name,'Вопрос-'||SEQ_STC_FIELDS_ID.nextval),nvl(:code_name,'Q'||SEQ_STC_FIELDS_ID.nextval),:ord,2, :must, :quoted) returning id into :field_id");

	$p=0;$g=0;$g_on_p=0;$o=0;$o_on_g=0;$q=0; //номера страницы, группы, объекты, вопросы
	$current_grp_id=''; //текущая группа вопросов
	$last_obj_type=''; //тип последнего объекта
	$nl_replace_ser=array(chr(10),chr(13));
	$nl_replace_rep=array(' ','');


	if(isset($del_grp)) { //удаление групп
		foreach($del_grp as $key => $val) {
				
			//если группа первая в анкете, то не удаляем ее, а просто обнуляем
			$q1=OCIParse($c,"select num from STC_OBJECT_GROUP where project_id='".$project_id."' and id='".$del_grp[$key]."'");
			OCIExecute($q1,OCI_DEFAULT);
			OCIFetch($q1);
			//переносим все вопросы в предудущую группу и удаляем ее
			$q2=OCIParse($c,"select id from STC_OBJECT_GROUP where project_id='".$project_id."' and num=
			(select max(num) from STC_OBJECT_GROUP where project_id='".$project_id."' and num<'".OCIResult($q1,"NUM")."')"); //получаем ID предыдущей группы
				
			OCIExecute($q2,OCI_DEFAULT);
			OCIFetch($q2);
			$upd=OCIParse($c,"update STC_OBJECTS set group_id='".OCIResult($q2,"ID")."'
			where group_id='".$del_grp[$key]."' and project_id='".$project_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			$del=OCIParse($c,"delete from STC_OBJECT_GROUP where id='".$del_grp[$key]."' and project_id='".$project_id."'");					
			OCIExecute($del,OCI_DEFAULT);
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


	$i=0; foreach($obj_idx as $idx => $idx_val) {$i++; //ключ - индекс объекта на странице редактирования; значение - для новых: page,group,new; для существующих: ID объекта.
		
		if($p==0 and $idx_val<>'page' and $idx_val<>'group' and $obj_type_id[$idx]<>'') {$p++; $g++; $g_on_p=1; $o_on_g=0; //если первый объект в анкете не страница, не группа и не пустой объект, то создаем страницу и группу
			$def_name='';
			$def_ord_type='По порядку';
			OCIBindByName($ins_grp,":page",$p);
			OCIBindByName($ins_grp,":name",$def_name);
			OCIBindByName($ins_grp,":ord_type",$def_ord_type);
			OCIBindByName($ins_grp,":num",$g);
			OCIBindByName($ins_grp,":grp_id",$current_grp_id,128);
			OCIExecute($ins_grp,OCI_DEFAULT);			
		}
		
		if($idx_val=='page' or $idx_val=='group') {$g++; $g_on_p++; $o_on_g=0; //страница и группа
			if($idx_val=='page' or $p==0) {$p++; $g_on_p=1;} //если первая группа не начинает новую страницу, то добавляем страницу

			if(isset($obj_id[$idx]) and $obj_id[$idx]<>'')	{ //если группа существует, то обновляем имя и порядковый номер
				$current_grp_id=$obj_id[$idx];
				OCIBindByName($upd_grp,":page",$p);
				OCIBindByName($upd_grp,":name",$grp_name[$idx]);
				OCIBindByName($upd_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_grp,":num",$g);
				OCIBindByName($upd_grp,":grp_id",$current_grp_id);
				OCIBindByName($upd_grp,":g_on_p",$g_on_p);
				OCIExecute($upd_grp,OCI_DEFAULT);
			}
			else { //если не существует, то добавляем новую

				!isset($grp_name[$idx])?$grp_name[$idx]='':NULL; 
				!isset($order_type[$idx])?$order_type[$idx]='По порядку':NULL;

				OCIBindByName($ins_grp,":page",$p);
				OCIBindByName($ins_grp,":name",$grp_name[$idx]);
				OCIBindByName($ins_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_grp,":num",$g);
				OCIBindByName($ins_grp,":grp_id",$current_grp_id,128);
				OCIBindByName($ins_grp,":g_on_p",$g_on_p);
				OCIExecute($ins_grp,OCI_DEFAULT);
		}}
		//объекты
		if($idx_val<>'page' and $idx_val<>'group') {
			if($idx_val=='new' and $obj_type_id[$idx]=='') { //игнорим пустые новые объекты 
				continue;
			} 
			$o++;
			$o_on_g++;
			$last_obj_type=$obj_type_id[$idx];
			if($idx_val<>'new') { //обновляем существующие
				$full_num=$p.".".$g_on_p.".".$o_on_g;
				$field_id='';
				!isset($obj_type_id[$idx])?$obj_type_id[$idx]='':NULL;
				!isset($order_type[$idx])?$order_type[$idx]='':NULL;
				!isset($must[$idx])?$must[$idx]='':NULL;
				!isset($quoted[$idx])?$quoted[$idx]='':NULL;
				if(substr($obj_type_id[$idx],0,2)<>'q_') $quoted[$idx]=''; //квота применима только для списковых вопросов
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //для вопросов обновлям поле в БД
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //удаляем переносы строки
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);
					OCIBindByName($upd_field,":text_name",$text_name[$idx]);
					OCIBindByName($upd_field,":code_name",$code_name[$idx]);
					OCIBindByName($upd_field,":must",$must[$idx]);
					OCIBindByName($upd_field,":quoted",$quoted[$idx]);
					OCIBindByName($upd_field,":ord",$o);
					OCIBindByName($upd_field,":obj_id",$idx_val);
					OCIExecute($upd_field,OCI_DEFAULT);
				}
				else {$q_num='';}
				OCIBindByName($upd_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($upd_obj,":group_id",$current_grp_id);
				OCIBindByName($upd_obj,":message",$message[$idx]);
				OCIBindByName($upd_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_obj,":num",$o);
				OCIBindByName($upd_obj,":must",$must[$idx]);
				OCIBindByName($upd_obj,":obj_id",$idx_val);
				OCIBindByName($upd_obj,":full_num",$full_num);
				OCIBindByName($upd_obj,":quest_num",$q_num);
				OCIBindByName($upd_obj,":o_on_g",$o_on_g);				
				OCIExecute($upd_obj,OCI_DEFAULT);	
			}
			else { //добавляем новые
				$full_num=$p.".".$g_on_p.".".$o_on_g;
				$field_id='';
				!isset($obj_type_id[$idx])?$obj_type_id[$idx]='':NULL;
				!isset($order_type[$idx])?$order_type[$idx]='По порядку':NULL;
				!isset($must[$idx])?$must[$idx]='':NULL;
				!isset($quoted[$idx])?$quoted[$idx]='':NULL;
				if(substr($obj_type_id[$idx],0,2)<>'q_') $quoted[$idx]=''; //квота применима только для списковых вопросов				
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //для вопросов обновлям поле в БД
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //удаляем переносы строки
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);					
					OCIBindByName($ins_field,":text_name",$text_name[$idx]);
					OCIBindByName($ins_field,":code_name",$code_name[$idx]);
					OCIBindByName($ins_field,":ord",$o);
					OCIBindByName($ins_field,":must",$must[$idx]);
					OCIBindByName($ins_field,":quoted",$quoted[$idx]);					
					OCIBindByName($ins_field,":field_id",$field_id,128);
					OCIExecute($ins_field,OCI_DEFAULT);
				}
				else {$q_num='';}				
				OCIBindByName($ins_obj,":field_id",$field_id);
				OCIBindByName($ins_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($ins_obj,":group_id",$current_grp_id);
				OCIBindByName($ins_obj,":message",$message[$idx]);
				OCIBindByName($ins_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_obj,":must",$must[$idx]);
				OCIBindByName($ins_obj,":num",$o);
				OCIBindByName($ins_obj,":full_num",$full_num);
				OCIBindByName($ins_obj,":quest_num",$q_num);
				OCIBindByName($ins_obj,":o_on_g",$o_on_g);
				OCIExecute($ins_obj,OCI_DEFAULT);				
	}}}
	//если последний объект не конец опроса, то добавляем успешный конец
	if(substr($last_obj_type,0,4)<>'end_') {
		$field_id='';
		$obj_type_id='end_norm';
		$message='';
		$order_type='';
		$must='';
		$o++;
		$o_on_g++;
		$full_num=$p.".".$g_on_p.".".$o_on_g;
		$q_num='';
		OCIBindByName($ins_obj,":field_id",$field_id);
		OCIBindByName($ins_obj,":type_id",$obj_type_id);
		OCIBindByName($ins_obj,":group_id",$current_grp_id);
		OCIBindByName($ins_obj,":message",$message);
		OCIBindByName($ins_obj,":ord_type",$order_type);
		OCIBindByName($ins_obj,":must",$must);
		OCIBindByName($ins_obj,":num",$o);
		OCIBindByName($ins_obj,":full_num",$full_num);
		OCIBindByName($ins_obj,":quest_num",$q_num);
		OCIBindByName($ins_obj,":o_on_g",$o_on_g);
		OCIExecute($ins_obj,OCI_DEFAULT);				
	}
	
	OCICommit($c);

	echo "<font color=green>СОХРАНЕНО</font><br>";
	echo "<script>
	parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='<font color=green>СОХРАНЕНО</font>';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='saved';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='Сохранено';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.admAnkEditFirstFrame.location.reload();
	</script>";
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
