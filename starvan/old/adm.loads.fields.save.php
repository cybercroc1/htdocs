<?php	
include("../../conf/starcall_conf/session.cfg.php");
include("../../conf/starcall_conf/conn_string.cfg.php");
set_error_handler ("my_error_handler");
extract($_POST);

if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

if($frm_submit=='save' or $frm_submit=='continue') {

	if(isset($uniq_term)) $_SESSION['adm']['project']['uniq_term']=$uniq_term;
	else $uniq_term=$_SESSION['adm']['project']['uniq_term'];
	
	//===========================================================================================
	//проверка ОШИБОК и ПРЕДУПРЕЖДЕНИЙ	
	$error='';
	$warning='';

	//ПРЕДУПРЕЖДЕНИЕ: Удаление объектов
	if(isset($del_field) and $frm_submit<>'continue') {
		$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Будет безвозвратно удалено одно или несколко полей, а также, все загруженные в эти поля данные и телефоны.</font><br>';
	}
	//	
	
	//ОШИБКА: Нечего сохранять
	if(!isset($base_fields_id) and !isset($del_field)) {
		$base_fields_id=array();
		$error.="<font color=red>ОШИБКА: Нечего сохранять</font><br>";
	}		
	//проверка изменения квот и индексов. Получаем список и последовательность полей, сама проверка ниже, после проверки ошибок
	$q=OCIParse($c,"select id,quoted,idx,std_field_name from STC_FIELDS t
where project_id='".$project_id."' and src_type_id=1 and (quoted is not null or idx is not null or std_field_name='PHONE')
order by ord");
	OCIExecute($q,OCI_DEFAULT);
	$old_quoted_fields=array(); //список старых квотируемых полей
	$del_quoted_fields=array();
	$add_quoted_fields=array();
	
	$old_idx_fields=array(); //список старых индексируемых полей 
	$del_idx_fields=array(); //список полей для удаления индексов
	$add_idx_fields=array(); //список полей для добавления индексов
	while (OCIFetch($q)) {
		if(OCIResult($q,"QUOTED")<>'') { //считаем кол-во и формируем массив существующих квот
			$old_quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"ID");
			if(!isset($base_fields_quoted[OCIResult($q,"ID")]) or isset($del_field[OCIResult($q,"ID")])) $del_quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"ID"); //список полей для удаления квот
		} 
		if(OCIResult($q,"IDX")<>'') { //считаем кол-во и формируем массив существующих индексов
			$old_idx_fields[OCIResult($q,"ID")]=OCIResult($q,"ID");
			if(!isset($base_fields_idx[OCIResult($q,"ID")]) or isset($del_field[OCIResult($q,"ID")])) $del_idx_fields[OCIResult($q,"ID")]=OCIResult($q,"ID"); //список полей для удаления индексов
		} 
		if(OCIResult($q,"STD_FIELD_NAME")=='PHONE') $phone_field_id=OCIResult($q,"ID"); //идентификатор поля Телефон
	}
	
	//перебираем все схраняемые поля
	if(!isset($base_fields_id)) $base_fields_id=array();
	foreach($base_fields_id as $key=>$id) {
		//ОШИБКА: пустые имена полей
		if(trim($base_fields_text_name[$id])=='' or trim($base_fields_code_name[$id]=='')) {
			$error.="<font color=red>ОШИБКА: Пустое имя или кодовое имя поля (id:$id)</font><br>";
		}
		//ОШИБКА: обязательность квотируемого поля
		if(isset($base_fields_quoted[$id]) and (!isset($base_fields_must[$id]) or isset($base_fields_uniq[$id]))) {
			$error.="<font color=red>ОШИБКА: Квотируемое поле \"$base_fields_text_name[$id]\" (id:$id) должно быть обязательным и не уникальным</font><br>";
		}
		//ОШИБКА: индексируемое поле не должно быть уникальным
		if(isset($base_fields_idx[$id]) and isset($base_fields_uniq[$id])) {
			$error.="<font color=red>ОШИБКА: Индексируемое поле \"$base_fields_text_name[$id]\" (id:$id) не должно быть уникальным</font><br>";
		}
		//ОШИБКА: зарезервированное слово "Квота"
		if(trim($base_fields_text_name[$id])=="Квота") {
			$error.="<font color=red>ОШИБКА: Нельзя называть поле зарезервированным словом \"Квота\"</font><br>";
		}	
		//ПРЕДУПРЕЖДЕНИЕ: нет смысла индексировать квотируемое поле, квота предполагает индексацию
		if(isset($base_fields_idx[$id]) and isset($base_fields_quoted[$id])) {
			unset($base_fields_idx[$id]);
			if($frm_submit<>'continue') $warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Нет смысла индексировать квотируемое поле \"$base_fields_text_name[$id]\" (id:$id). Признак \"Индекс\" с этого поля снят.</font><br>";
		}
		//список новых индексируемых полей для индексации
		if(isset($base_fields_idx[$id]) and !isset($old_idx_fields[$id])) {
			$add_idx_fields[$id]=$id;
		}
		//список новых квотируемых полей
		if(isset($base_fields_quoted[$id]) and !isset($old_quoted_fields[$id])) {
			$add_quoted_fields[$id]=$id;
		}				
	
		//ОШИБКИ: совпадение имен
		foreach($base_fields_id as $key2=>$id2) {
			if($id<>$id2) {
				//ОШИБКА: совпадение имени полей
				if(strtoupper($base_fields_text_name[$id])==strtoupper(trim($base_fields_text_name[$id2]))) {
					$error.="<font color=red>ОШИБКА: Дублируется имя поля \"$base_fields_text_name[$id]\" (id:$id)</font><br>";
				}
				//ОШИБКА: совпадение кодовых имени полей
				if(strtoupper($base_fields_code_name[$id])==strtoupper(trim($base_fields_code_name[$id2]))) {
					$error.="<font color=red>ОШИБКА: Дублируется кодовое имя поля \"$base_fields_code_name[$id]\" (id:$id)</font><br>";
				}
				//ОШИБКА: совпадение стандартных полей
				if($base_fields_std_name[$id]<>'' and $base_fields_std_name[$id]==$base_fields_std_name[$id2]) {
					$error.="<font color=red>ОШИБКА: Дублируется стандартное поле \"$base_fields_std_name[$id]\" (id:$id)</font><br>";
				}
	}}}
	//
	//ПРЕДУПРЕЖДЕНИЕ: изменились квотируемые поля
	if(count($del_quoted_fields)>0 or count($add_quoted_fields)>0) {
		if($frm_submit<>'continue') {
			$warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Изменились квотируемые поля. Если продолжить сохранение, то проект будет приостановлен, а квоты, включая зависимые квоты по вопросам придется перестроить и прописать значения заново.</font><br>";
			$warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Изменились индексируемые поля. Будут проиндексированы все загруженные ранее данные. Индексация может занять длительное время</font><br>";
		}
		else $changed_quote='y';
	}	
	//ПРЕДУПРЕЖДЕНИЕ: изменились индексируемые поля
	if(count($del_idx_fields)>0 or count($add_idx_fields)>0) {
		if($frm_submit<>'continue') $warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Изменились индексируемые поля. Будут проиндексированы все загруженные ранее данные. Индексация может занять длительное время</font><br>";
	}	
	//	
	//==================================================================================================
	//если есть ошибки или предупреждения, тормозим сохранение
	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.frm.frm_submit.value='save';
		parent.admBottomFrame.frm.save.value='Сохранить';
		parent.admBottomFrame.frm.save.disabled=false;
		parent.admBottomFrame.frm.cancel.style.display='none';
		</script>";	
		exit();
	}
	if($warning<>'') {
		echo $warning;
		echo "<script>
		parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$warning."';
		parent.admBottomFrame.frm.frm_submit.value='continue';
		parent.admBottomFrame.frm.save.value='Продолжить';
		parent.admBottomFrame.frm.save.disabled=false;
		parent.admBottomFrame.frm.cancel.disabled=false;
		parent.admBottomFrame.frm.cancel.style.display='';
		</script>";	
		exit();
	}	
	
	//СОХРАНЯЕМ
	
	//УДАЛЯЕМ
	//КВОТЫ. удаление удаленных квот
	if(count($del_idx_fields)>0 or count($del_quoted_fields)>0) {
		if(count($del_quoted_fields)>0) {
			$del=OCIParse($c,"delete from stc_qst_quotes where project_id=".$project_id);
			OCIExecute($del,OCI_DEFAULT);
			$del=OCIParse($c,"delete from STC_SRC_QUOTE_INDEXES where project_id=".$project_id);
			OCIExecute($del,OCI_DEFAULT);		
			$del=OCIParse($c,"delete from STC_SRC_QUOTES where project_id=".$project_id);
			OCIExecute($del,OCI_DEFAULT);
			echo "Удалены все квоты<hr>";
		}	
		//ИНДЕКСЫ. удаление удаленных индексов
		$del=OCIParse($c,"delete from STC_SRC_INDEXES where field_id in (".implode(",",array_merge($del_idx_fields,$del_quoted_fields)).") and project_id=".$project_id);
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		echo "Удалены отмененные индексы<hr>";
		if(count($del_idx_fields)>0) {
			//обновление блокировки записей по независимым исходным квотам
			OCIExecute(OCIParse($c,"begin STC_SRC_SINGLE_QUOTE_RELOCK(".$project_id."); end;"));
			echo "Обновлена блокировка записей по независимым исх.квотам STC_SRC_SINGLE_QUOTE_RELOCK<hr>";
			OCICommit($c);
		}
	}		
	//удаление полей и данных
	if(isset($del_field)) {
		//удаление телефонов
		if(isset($phone_field_id) and isset($del_field[$phone_field_id])) {
			$del=OCIParse($c,"delete from STC_PHONES where project_id=".$project_id." and base_field_id=".$phone_field_id);
			OCIExecute($del,OCI_DEFAULT);
			echo "Удалены телефоны<hr>";
			$upd=OCIParse($c,"update STC_LOAD_HISTORY set load_phones=0, found_phones=0, allow_phones=0
			where project_id=".$project_id);
			OCIExecute($upd,OCI_DEFAULT);
			echo "Обновлена статистика загрузок (телефонов)<hr>";
		}
		$del=OCIParse($c,"delete from STC_FIELD_VALUES where project_id=".$project_id." and field_id in (".implode(",",$del_field).")");
		OCIExecute($del,OCI_DEFAULT);
		echo "Удалены значения удаляемых полей<hr>";
		$del=OCIParse($c,"delete from STC_FIELDS where project_id=".$project_id." and id in (".implode(",",$del_field).")");
		OCIExecute($del,OCI_DEFAULT);
		echo "Удалены поля<hr>";
		OCICommit($c);
	}	
	
	//обновляем условие проверки уникальности полей
	if(isset($uniq_term)) {
		$upd=OCIParse($c,"update STC_PROJECTS set uniq_term='".$uniq_term."' where id=".$project_id);
		OCIExecute($upd,OCI_DEFAULT);
	}
	//обновляем статус, если изменились квотируемые поля
	if(isset($changed_quote)) {
		$upd=OCIParse($c,"update STC_PROJECTS set SRC_QUOTE_BROKEN='yes',QST_QUOTE_BROKEN='yes',QST_STAT_BROKEN='yes', status='Приостановлен' where id='".$project_id."'");
		OCIExecute($upd,OCI_DEFAULT);
	}
	//проверяем существование и получаем значение сортировки и уникальности существующих полей
	$q=OCIParse($c,"select id from STC_FIELDS t where id=:id and project_id='".$project_id."'");
	//обновляем сортировку существующих полей
	$upd=OCIParse($c,"update STC_FIELDS t set ord=:ord, text_name=:text_name, code_name=:code_name, std_field_name=:std_name, uniq=:uniq, must=:must, quoted=:quoted, idx=:idx, ank_show=:ank_show		
	where id=:id and project_id='".$project_id."'");
	//добавляем новые поля
	$ins=OCIParse($c,"insert into STC_FIELDS (id,project_id,text_name,code_name,ord,src_type_id,std_field_name,uniq,must,quoted,idx,ank_show)
	values (:id,'".$project_id."',:text_name,:code_name,:ord,'1',:std_name,:uniq,:must,:quoted,:idx,:ank_show)");	
	
	//ДОБАВЛЯЕМ ПОЛЯ В БД
	foreach($base_fields_id as $key=>$id) {
		OCIBindByName($q,":id",$id);
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) {
			!isset($base_fields_uniq[$id])?$base_fields_uniq[$id]='':NULL;
			!isset($base_fields_must[$id])?$base_fields_must[$id]='':NULL;
			!isset($base_fields_idx[$id])?$base_fields_idx[$id]='':NULL;
			//!isset($base_fields_std_name[$id])?$base_fields_std_name[$id]='':NULL;
			//обановляем
			OCIBindByName($upd,":id",$id);
			OCIBindByName($upd,":ord",$key);
			OCIBindByName($upd,":text_name",$base_fields_text_name[$id]);
			OCIBindByName($upd,":code_name",$base_fields_code_name[$id]);
			OCIBindByName($upd,":std_name",$base_fields_std_name[$id]);
			OCIBindByName($upd,":uniq",$base_fields_uniq[$id]);
			OCIBindByName($upd,":must",$base_fields_must[$id]);  
			OCIBindByName($upd,":quoted",$base_fields_quoted[$id]); 
			OCIBindByName($upd,":idx",$base_fields_idx[$id]);
			OCIBindByName($upd,":ank_show",$base_fields_ank_show[$id]);  
			OCIExecute($upd,OCI_DEFAULT);
		} 
		else {
			//если такого поля не оказалось, то добавляем его
			OCIBindByName($ins,":id",$id);
			OCIBindByName($ins,":ord",$key);
			OCIBindByName($ins,":text_name",$base_fields_text_name[$id]);
			OCIBindByName($ins,":code_name",$base_fields_code_name[$id]);
			OCIBindByName($ins,":std_name",$base_fields_std_name[$id]);
			OCIBindByName($ins,":uniq",$base_fields_uniq[$id]);
			OCIBindByName($ins,":must",$base_fields_must[$id]);  
			OCIBindByName($ins,":quoted",$base_fields_quoted[$id]);
			OCIBindByName($ins,":idx",$base_fields_idx[$id]); 
			OCIBindByName($ins,":ank_show",$base_fields_ank_show[$id]);  
			OCIExecute($ins,OCI_DEFAULT);
		}
	}
	//ИНДЕКСЫ. Индексирование существующих данных по новым индексируемым и квотируемым полям
	if(count($add_idx_fields)>0 or count($add_quoted_fields)>0) {
		$q_add_idx=OCIParse($c,"insert into STC_SRC_INDEXES i (id, project_id, field_id, Value, STAT_new, STAT_end_norm,i.STAT_inwork,i.STAT_end_error,i.STAT_end_false,
i.STAT_end_nedoz,i.STAT_end_otkaz,i.STAT_end_quote,i.STAT_nedoz,i.STAT_perez)
		select SEQ_STC_INDEX_ID.nextval, a.*
		from (
		select b.project_id, v.field_id, v.text_value, 
		count(decode(status,NULL,decode(allow,'y',1,NULL),NULL)),
		count(decode(status,'end_norm',1,NULL)),
		count(decode(status,'inwork',1,NULL)),
		count(decode(status,'end_error',1,NULL)),
		count(decode(status,'end_false',1,NULL)),
		count(decode(status,'end_nedoz',1,NULL)),
		count(decode(status,'end_otkaz',1,NULL)),
		count(decode(status,'end_quote',1,NULL)),
		count(decode(status,'nedoz',1,NULL)),
		count(decode(status,'perez',1,NULL))
		from STC_BASE b, STC_FIELD_VALUES v
		where b.project_id=".$project_id."
		and v.project_id=".$project_id."
		and v.base_id=b.id
		and v.field_id in (".implode(",",array_merge($add_idx_fields,$add_quoted_fields)).")
		group by b.project_id, v.field_id, v.text_value
		) a");
		
		OCIExecute($q_add_idx,OCI_DEFAULT);
		echo "Проиндексированы новые поля<hr>";
	}
	//обновление настроек проекта (кол-во исходных полей)
	OCIExecute(OCIParse($c, "update STC_PROJECTS set (num_src_fields,num_phone_fields)=
(select count(*), count(decode(std_field_name,'PHONE',1,NULL)) from STC_FIELDS where project_id=".$project_id." and src_type_id='1' and deleted is null)
where id=".$project_id),OCI_DEFAULT);
		
	OCICommit($c);
	echo "<font color=green>СОХРАНЕНО</font><br>";
	echo "<script>
	parent.admBottomFrame.document.getElementById('save_status').innerHTML='<font color=green>СОХРАНЕНО</font>';
	parent.admBottomFrame.frm.frm_submit.value='saved';
	parent.admBottomFrame.frm.save.value='Сохранено';
	parent.admBottomFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.location.reload();
	</script>";	
	if(isset($changed_quote)) echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>"; 
}

function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<font color=red><br>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."<br></font>";
	echo "<script>parent.admBottomFrame.document.getElementById('save_status').innerHTML='<font color=red>ОШИБКА: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>
	parent.admBottomFrame.frm.frm_submit.value='save';
	parent.admBottomFrame.frm.save.value='Сохранить';
	parent.admBottomFrame.frm.save.disabled=false;
	parent.admBottomFrame.frm.cancel.style.display='none';	
	</script>";
	exit();
}
?>
