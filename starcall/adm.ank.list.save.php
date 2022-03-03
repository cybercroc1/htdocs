<?php	
include("starcall/session.cfg.php");
include("starcall/conn_string.cfg.php");
set_error_handler ("my_error_handler");
extract($_POST);

if($_SESSION['user']['rw_ank']<>'w') exit();

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

$project_id=$_SESSION['adm']['project']['id'];

echo "Сохранение списка ответов<hr>";

$q=OCIParse($c,"select quote_num from STC_OBJECTS where project_id=".$project_id." and id=".$obj_id);
OCIExecute($q);
OCIFetch($q);
if(OCIResult($q,"QUOTE_NUM")<>'') $quote_num=OCIResult($q,"QUOTE_NUM");

if(!isset($val_id)) $val_id=array();	
$error='';
$warning='';
$info='';
	if($frm_submit=='save' or $frm_submit=='continue') {
		//ОШИБКИ
		//$error='ошибка';
		//ПРЕДУПРЕЖДЕНИЕ: Удаление объектов
		if(isset($del_val) and $frm_submit<>'continue') {
			$warning.='<font color=red>ПРЕДУПРЕЖДЕНИЕ: Будет удалено одио или несколько значений.</font><br>';
		}
		//

	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.admAnkEditSecondFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.frm_submit.value='save';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.save.value='Сохранить';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditSecondFrame.frm.cancel.style.display='none';
		</script>";	
		exit();
	}
	if($warning<>'') {
		echo $warning;
		echo "<script>
		parent.admBottomFrame.admAnkEditSecondFrame.document.getElementById('save_status').innerHTML='".$warning."';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.frm_submit.value='continue';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.save.value='Продолжить';
		parent.admBottomFrame.admAnkEditSecondFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditSecondFrame.frm.cancel.disabled=false;
		parent.admBottomFrame.admAnkEditSecondFrame.frm.cancel.style.display='';
		</script>";	
		exit();
	}	

	//СОХРАНЯЕМ

	//удаляем
	if(isset($del_val)) {
		OCIExecute(OCIParse($c,"update STC_LIST_VALUES set deleted=sysdate where id in (".implode(",",$del_val).")"),OCI_DEFAULT);
		OCICommit($c);
	}
	
	//Влияет на
	if(!isset($impact_on_field)) $impact_on_field='';
	OCIExecute(OCIParse($c,"update STC_OBJECTS set impact_on_field='".$impact_on_field."' where id=".$obj_id),OCI_DEFAULT);
	
	//Зависит от
	if(!isset($depend_of_field)) $depend_of_field='';
	OCIExecute(OCIParse($c,"update STC_OBJECTS set depend_of_field='".$depend_of_field."' where id=".$obj_id),OCI_DEFAULT);

	//обновляем сортировку существующих значений
	$upd=OCIParse($c,"update STC_LIST_VALUES t set text_value=:text_value, code_value=:code_value, quote_key=:quote_key, other_count=:other_count, always_bottom=:always_bottom, ord=decode(:always_bottom,'on',:ord+100000,:ord)
	where id=:id and project_id='".$project_id."' and object_id='".$obj_id."'");

	//добавляем новые значения
	$ins=OCIParse($c,"insert into STC_LIST_VALUES (id,project_id,object_id,text_value,code_value,quote_key,other_count,always_bottom,ord)
		values (:id,'".$project_id."','".$obj_id."',:text_value,:code_value,:quote_key,:other_count,:always_bottom,
		decode(:always_bottom,'on',:ord+100000,:ord))");	
		
	$seq=OCIParse($c,"select SEQ_STC_LIST_VALUE_ID.nextval from dual");
	
	//добавляем поля в БД

	$i=0; foreach($val_id as $key=>$id) {$i++;
		//добавляем новые значения
		$text_value[$key]=trim($text_value[$key]);
		$code_value[$key]=trim($code_value[$key]);
		$quote_key[$key]=trim($quote_key[$key]);
		if(!intval(trim($other_count[$key])) or trim($other_count[$key])==0) $other_count[$key]='';
		
		if($text_value[$key]=='' and $other_count[$key]=='') continue; //пустые пропускаем
		
		if($text_value[$key]=='' and $other_count[$key]>0) $text_value[$key]='Другое';
		
		//КВОТЫ И ИНДЕКСЫ. Проверка на изменение ключей квоты
		if(isset($old_quote_key[$key]) and $old_quote_key[$key]<>$quote_key[$key]) {
			$changed_stat='y';
			$info.="<font color=red>ВНИМАНИЕ! Изменены ключи квоты. Необходимо пересчитать статистику<font><br>";			
		}
		
		
		if($id=='new') {
			OCIExecute($seq);
			OCIFetch($seq);
			$id=OCIResult($seq,"NEXTVAL");
			
			if($code_value[$key]=='') $code_value[$key]="A".$id;
			if($quote_key[$key]=='') $quote_key[$key]=$text_value[$key];
			
			OCIBindByName($ins,":id",$id);
			OCIBindByName($ins,":text_value",$text_value[$key]);
			OCIBindByName($ins,":code_value",$code_value[$key]);
			OCIBindByName($ins,":quote_key",$quote_key[$key]);
			OCIBindByName($ins,":other_count",$other_count[$key]);
			OCIBindByName($ins,":always_bottom",$always_bottom[$key]);
			OCIBindByName($ins,":ord",$i);
			OCIExecute($ins,OCI_DEFAULT);			
		}
		//обновляем существующие
		else {
			OCIBindByName($upd,":id",$id);
			OCIBindByName($upd,":text_value",$text_value[$key]);
			OCIBindByName($upd,":code_value",$code_value[$key]);
			OCIBindByName($upd,":quote_key",$quote_key[$key]);
			OCIBindByName($upd,":other_count",$other_count[$key]);
			OCIBindByName($upd,":always_bottom",$always_bottom[$key]);			
			OCIBindByName($upd,":ord",$i);
			OCIExecute($upd,OCI_DEFAULT);
		}
	}
	OCICommit($c);
	//сортировка
	if($order_by<>'') {
		$q=OCIParse($c,"select id from  STC_LIST_VALUES a
where project_id='".$_SESSION['adm']['project']['id']."' and object_id='".$obj_id."'
order by ".$order_by);
		$upd=OCIParse($c,"update STC_LIST_VALUES set ord=decode(always_bottom,'on',:ord+100000,:ord) where id=:id");
		OCIExecute($q);
		$i=0; while (OCIFetch($q)) {$i++;
			$id=OCIResult($q,"ID");
			OCIBindByName($upd,":id",$id);
			OCIBindByName($upd,":ord",$i);
			OCIExecute($upd,OCI_DEFAULT);		
		}
		OCICommit($c);
	}
	//ИНДЕКСЫ.
	if(isset($quote_num)) {
	
		//КВОТЫ и ИНДЕКСЫ. Удаляем квоты и индексы для удаленных ключей квот.
		//помечаем к удалению квоты по индексам несуществующих ключей квоты
		$upd=OCIParse($c,"update Stc_Qst_Quotes q set q.quote_level=0-".$quote_num."
		where q.project_id=".$project_id." and q.index_id in ( 
		select id from STC_QST_INDEXES i
		where i.project_id=".$project_id." and i.object_id=".$obj_id." and i.value not in ( 
		select distinct quote_key from Stc_List_Values v
		where v.project_id=".$project_id." and v.object_id=".$obj_id." and v.deleted is null
		))
		and q.quote_level=".$quote_num);
		OCIExecute($upd,OCI_DEFAULT);
		//выполняем процедуру удаления всех дочерних квот
		if(oci_num_rows($upd)>0) {
			OCIExecute(OCIParse($c,"begin stc_del_qst_quotes(".$project_id.",".$obj_id."); end;"));
			OCICommit($c);
			echo "Удалены квоты и индексы по несуществующим ключам.<hr>";
		}
		//ИНДЕКСЫ. добавление индексов квот
		$ins=OCIParse($c,"insert into STC_QST_INDEXES (id,project_id,field_id,object_id,value)
select SEQ_STC_INDEX_ID.nextval, a.* from 
(select distinct v.project_id, o.field_id, v.object_id, quote_key from STC_LIST_VALUES v, STC_OBJECTS o
where v.project_id=".$project_id." and v.object_id=".$obj_id." and v.deleted is null
and o.id=v.object_id
minus
select i.project_id,i.field_id,i.object_id,i.value from STC_QST_INDEXES i
where i.project_id=".$project_id." and i.object_id=".$obj_id."
) a");
		OCIExecute($ins,OCI_DEFAULT);
		if(oci_num_rows($ins)>0) {
			OCICommit($c);
			//сделать процедуру добавления ключей квоты, не пересчитывать после добавления!
			OCIExecute(OCIParse($c,"begin stc_add_qst_quotes(".$project_id."); end;"));
			//$changed_quote='y';
		
			$info.="<font color=red>ВНИМАНИЕ! Добавлены новые ключи квоты, не забудьте прописать значения<font><br>";
		}
	}
	/*if(isset($changed_quote)) {
		//обновляем статус проекта, если добавились ключи квот
		$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes' where id='".$project_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}*/
	if(isset($changed_stat)) {
		//обновляем статус проекта, если изменились ключи квот, не перестраиваем квоты, только пересчитываем
		$upd=OCIParse($c,"update STC_PROJECTS set QST_STAT_BROKEN='yes' where id='".$project_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	echo $info;		
	echo "<font color=green>СОХРАНЕНО</font><br>";
	echo "<script>
	parent.admBottomFrame.admAnkEditSecondFrame.document.getElementById('save_status').innerHTML='".$info."<font color=green>СОХРАНЕНО</font>';
	parent.admBottomFrame.admAnkEditSecondFrame.frm.save.disabled=false;
	parent.admBottomFrame.admAnkEditSecondFrame.frm.save.value='Сохранить';
	parent.admBottomFrame.admAnkEditSecondFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.admAnkEditSecondFrame.location.reload();</script>";
	if(isset($changed_quote) or isset($changed_stat)) echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
	exit();
}
function my_error_handler($code, $msg, $file, $line) {
	echo "<font color=red><hr>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	echo "<script>parent.admBottomFrame.admAnkEditSecondFrame.document.getElementById('save_status').innerHTML='<font color=red>ОШИБКА: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>
	parent.admBottomFrame.admAnkEditSecondFrame.frm.save.disabled=false;
	parent.admBottomFrame.admAnkEditSecondFrame.frm.save.value='Сохранить';
	parent.admBottomFrame.admAnkEditSecondFrame.frm.cancel.style.display='none';
	</script>";
	exit();
}
?>
