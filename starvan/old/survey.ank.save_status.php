<?php include("../../conf/starcall_conf/session.cfg.php"); 
set_error_handler ("my_error_handler");

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("../../conf/starcall_conf/conn_string.cfg.php");

if(!isset($perez_date)) $perez_date='';

//СОХРАНЕНИЕ СТАТУСА=====================================
if(isset($set_ank_status) and $set_ank_status<>'') {
	set_ank_status($set_ank_status,$_SESSION['survey']['ank']['base']['id'],$perez_date);
	$_SESSION['survey']['ank']['base']['id']='';
	$_SESSION['survey']['ank']['base']['status']='';
	$_SESSION['survey']['ank']['phone']['id']='';
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
}
//=======================================================

//ФУНКЦИИ=================================================================================================================================
function set_ank_status($new_base_status,$base_id,$perez_date) {
	global $c;
	$old_base_status='';

	//получаем старый статус записи
	$q=OCIParse($c, "select status, src_quote_id, phone_id from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_base_status=OCIResult($q,"STATUS");
	$src_quote_id=OCIResult($q,"SRC_QUOTE_ID");
	$phone_id=OCIResult($q,"PHONE_ID");
	echo "действие: получен старый статус записи; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";	
	//
	if($old_base_status<>'inwork' or ($new_base_status<>'end_norm' and $new_base_status<>'end_false' and $new_base_status<>'perez' and $new_base_status<>'end_otkaz' and $new_base_status<>'end_error' and $new_base_status<>'end_quote'))
	{
		echo "ОШИБКА: не верная установка статуса с $old_base_status на $new_base_status<br>";
		exit(); 
	}

echo "<hr>установка статуса записи<br>";
		//установка статуса записи
		OCIExecute(OCIParse($c,"update STC_BASE set
		status='".$new_base_status."',
		status_date=sysdate, 
		status_user=".$_SESSION['user']['id'].",
		status_type='ank',
		perez_date_msk=decode('".$new_base_status."','perez',to_date('".$perez_date."','YYYYMMDDHH24MISS'),perez_date_msk),
		nedoz_count='',
		nedoz_date=''
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
		),OCI_DEFAULT);
echo "действие: установлен статус записи;  ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		

	//удаляем из списка незавешенных пользователя
	OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id)
	,OCI_DEFAULT);

	//====================================


	//учет смены статусов в статистике и квотах
	if($old_base_status<>$new_base_status) {
		if($old_base_status=='') $minus=",stat_new=stat_new-1";
		else $minus=",stat_".$old_base_status."=stat_".$old_base_status."-1";
		if($new_base_status=='') $plus=",stat_new=stat_new+1";
		else $plus=",stat_".$new_base_status."=stat_".$new_base_status."+1";
		//общая по проекту
		OCIExecute(OCIParse($c,"update STC_PROJECTS set id=id ".$minus.$plus." where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
		//статистика индексов
		OCIExecute(OCIParse($c,"update STC_SRC_INDEXES i set id=id ".$minus.$plus."
		where (i.field_id,i.value) in (select v.field_id,v.text_value from STC_FIELD_VALUES v where v.project_id=".$_SESSION['survey']['project']['id']." and v.base_id=".$base_id.") and i.project_id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
		if($src_quote_id<>'') {
			//статистика по исходным квотам
			OCIExecute(OCIParse($c,"update STC_SRC_QUOTES set id=id ".$minus.$plus." where project_id=".$_SESSION['survey']['project']['id']." and id=".$src_quote_id),OCI_DEFAULT);
		}
echo "пересчитана статистика: $minus.$plus<br>";	
	}
	//
	
	//блокировка запсией по независимым исходным квотам
	if($new_base_status=='end_norm') {
		//$q=OCIParse($c,"select * from STC_SRC_INDEXES where project_id=".$_SESSION['survey']['project']['id']." and STAT_end_norm>=src_idx_quote");
		//OCIExecute($q,OCI_DEFAULT);
		//if(OCIFetch($q)) {
			//блокируем записи, квоты и проект по независимым исходным квотам
			OCIExecute(OCIParse($c,"begin STC_SRC_SINGLE_QUOTE_SETLOCK(".$_SESSION['survey']['project']['id']."); end;"));
			echo "Обновлена блокировка по исходным квотам (STC_SRC_SINGLE_QUOTE_SETLOCK)<br>";			
		//}
	}
	OCICommit($c);
}
function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	echo "<br><font color=red>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	exit();
}
?>
