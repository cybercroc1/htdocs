<?php include("starcall/session.cfg.php"); 
set_error_handler ("my_error_handler");

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("starcall/conn_string.cfg.php");

if($set_status<>'perez') {
	$perez_phone='';
	$perez_ext='';
	$perez_min='';
	$perez_date='';	
}

//СОХРАНЕНИЕ СТАТУСА=====================================
if(isset($set_status) and $set_status<>'') {
	set_ank_status($set_status,$_SESSION['survey']['ank']['base']['id'],$perez_phone,$perez_ext,$perez_min,$perez_date);
	$_SESSION['survey']['ank']['base']['id']='';
	$_SESSION['survey']['ank']['base']['status']='';
	$_SESSION['survey']['ank']['phone']['id']='';
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
}
//=======================================================

//ФУНКЦИИ=================================================================================================================================
function set_ank_status($new_base_status,$base_id,$perez_phone,$perez_ext,$perez_min,$perez_date) {
	echo 'set_ank_status($new_base_status,$base_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<br>';
	echo "set_ank_status($new_base_status,$base_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<hr>";
	global $c;
	$old_base_status='';

	//получаем старый статус записи
	$q=OCIParse($c, "select status, src_quote_id, phone_id, utc_msk from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_base_status=OCIResult($q,"STATUS");
	$src_quote_id=OCIResult($q,"SRC_QUOTE_ID");
	$phone_id=OCIResult($q,"PHONE_ID");
	$utc_msk=OCIResult($q,"UTC_MSK");
	echo "действие: получен старый статус записи; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";	
	//
	if($old_base_status<>'inwork' or ($new_base_status<>'end_norm' and $new_base_status<>'end_false' and $new_base_status<>'perez' and $new_base_status<>'end_otkaz' and $new_base_status<>'end_error' and $new_base_status<>'end_quote'))
	{
		echo "ОШИБКА: не верная установка статуса с $old_base_status на $new_base_status<br>";
		exit(); 
	}

		//перезвон
		if($new_base_status=='perez') {
			//телефон
			if($perez_phone<>'') {
				//если номер перезвона не пустой
				$q=OCIParse($c,"select id from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." and phone='".$perez_phone."' and nvl(ext,0)=nvl('".$perez_ext."',0)");
				OCIExecute($q,OCI_DEFAULT);
				if(OCIFetch($q)) {
					echo "Номер телефона и добавочный не поменялся<br>";
				}
				else {
					//добавляем новый телефон, возвращаем его ID, ниже ему установится статус и дата перезвона
					$ins=OCIParse($c, "insert into STC_PHONES (id, base_id, project_id, phone, ext, ord, allow)
					values (SEQ_STC_PHONE_ID.nextval,".$base_id.", ".$_SESSION['survey']['project']['id'].",
					'".$perez_phone."','".$perez_ext."',nvl((select max(ord) from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id."),1),'y') 
					returning id into :phone_id");
					OCIBindByName($ins,":phone_id",$phone_id,16);
					OCIExecute($ins, OCI_DEFAULT);
					echo "Добавлен новый телефон: ID: $phone_id; phone_num: $perez_phone; ext: $perez_ext<br>";	
				}
			}
			else echo "Номер телефона и добавочный не поменялся<br>";
			//ставим статус end_perez всем остальным телефонам со статусом perez
			OCIExecute(OCIParse($c,"update STC_PHONES set 
			status='end_perez', status_date=sysdate,
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and status='perez'"),OCI_DEFAULT);
echo "установлен статус end_perez всем перезвонам<br>";			
			
			//ставим перезвон текущему телефону
			$upd=OCIParse($c,"update STC_PHONES set 
			status='perez', status_date=sysdate,
			perez_date_msk=decode('".$perez_min."',NULL, decode('".$perez_date."',NULL,sysdate,to_date('".$perez_date."','DD.MM.YYYY HH24:MI')-nvl('".$utc_msk."',0)/24) ,sysdate+nvl('".$perez_min."',0)/1440),
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." returning to_char(perez_date_msk,'DD.MM.YYYY HH24:MI:SS') into :perez_date_msk");
			
			OCIBindByName($upd,":perez_date_msk",$perez_date,16);
			OCIExecute($upd,OCI_DEFAULT);	
echo "действие: установлен статус телефона; дата перезвона (мск): $perez_date; ID телефона: $phone_id; новый статус телефона: perez; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
			//ставим статус записи
			OCIExecute(OCIParse($c,"update STC_BASE set
			status='perez',
			perez_date_msk=decode('".$perez_min."',NULL, decode('".$perez_date."',NULL,sysdate,to_date('".$perez_date."','DD.MM.YYYY HH24:MI')-nvl('".$utc_msk."',0)/24) ,sysdate+nvl('".$perez_min."',0)/1440),
			status_date=sysdate, 
			status_user=".$_SESSION['user']['id'].",
			status_type='ank',
			nedoz_count='',
			nedoz_date=''
			where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
			),OCI_DEFAULT);			
		}
		else {
			//все остальные статусы
			OCIExecute(OCIParse($c,"update STC_BASE set
			status='".$new_base_status."',
			status_date=sysdate, 
			status_user=".$_SESSION['user']['id'].",
			status_type='ank',
			nedoz_count='',
			nedoz_date=''
			where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
			),OCI_DEFAULT);
		}
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
