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
	unset($_SESSION['nedoz_lock']); //снимаем блокировку случайного недозвона
	$result=set_call_status($set_status,$_SESSION['survey']['ank']['base']['id'],$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date);
	if($result['action']=='next_phone') {
		$_SESSION['survey']['ank']['phone']['id']='';
		$_SESSION['survey']['ank']['phone']['status']='';
	}
	elseif($result['action']=='next_base') {
		$_SESSION['survey']['ank']['base']['id']='';
		$_SESSION['survey']['ank']['base']['status']='';	
		$_SESSION['survey']['ank']['phone']['id']='';
		$_SESSION['survey']['ank']['phone']['status']='';
	}
	else {
		$_SESSION['survey']['ank']['base']['id']=$result['base_id'];
		$_SESSION['survey']['ank']['base']['status']=$result['new_base_status'];
		$_SESSION['survey']['ank']['phone']['id']=$result['phone_id'];
		$_SESSION['survey']['ank']['phone']['status']=$result['phone_status'];
	}
	if($result['new_base_status']=='inwork' and $_SESSION['survey']['ank']['base']['id']<>'') {
		//добавляем в список незавешенных пользователя
		OCIExecute(OCIParse($c,"insert into STC_USER_INWORK (user_id,project_id,base_id) values (".$_SESSION['user']['id'].",".$_SESSION['survey']['project']['id'].",".$_SESSION['survey']['ank']['base']['id'].")")
		,OCI_DEFAULT);
echo "Запись добавлена в список незавершенных пользователя<br>";		
	}
	else {
		//удаляем из списка незавешенных пользователя
		if($_SESSION['survey']['ank']['base']['id']<>'') {
		OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']." and base_id=".$_SESSION['survey']['ank']['base']['id'])
		,OCI_DEFAULT);
echo "Запись удалена из списка незавершенных пользователя<br>";
		}		
	}
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	OCICommit($c);
}
//=======================================================

//ФУНКЦИИ=================================================================================================================================
function set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date) {
	echo 'set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<br>';
	echo "set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<hr>";
	global $c;
	$old_base_status='';
	$new_base_status='';
	$min_nedoz_count='';
	$min_nedoz_date='';
	$src_quote_id='';
echo "действие: установка статуса телефона; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
	//ставим дату последней активности проекту
	OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "Обновлена дата активности проекта<br>";

	//если нет base_id
	if($base_id=='') {
echo "нет base_id<br>";	
		//создаем запись
		$ins=OCIParse($c, "insert into STC_BASE (id,project_id,allow,lock_user,lock_date) 
		values (seq_stc_base_id.nextval, ".$_SESSION['survey']['project']['id'].",'y',".$_SESSION['user']['id'].",sysdate)
		returning id into :base_id");
		OCIBindByName($ins,":base_id",$base_id,16);
		OCIExecute($ins,OCI_DEFAULT);
echo "действие: создана запись; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
		//добавляем к статистике по проекту
		OCIExecute(OCIParse($c,"update STC_PROJECTS set stat_new=stat_new+1 where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);	
echo "действие: обновлена статистика проекта; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
	}
/*	//если нет телефона, но телефон добавлен вручную
	if($phone_id=='' and $phone['num']<>'') {
		//создаем телефон
		$ins=OCIParse($c, "insert into STC_PHONES (id, base_id, project_id, phone, ord, allow)
		values (SEQ_STC_PHONE_ID.nextval,".$base_id.", ".$_SESSION['survey']['project']['id'].",
		substr(".$phone['num'].",0,25),nvl((select max(ord) from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id."),1),'y') 
		returning (phone,id) into (:phone,:phone_id)");
		OCIBindByName($ins,":phone",$phone['num'],16);
		OCIBindByName($ins,":phone_id",$phone_id,16);		
echo "действие: добавлен телефон; телефон: $phone[num]; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";	
	}
*/
	//получаем старый статус записи
	$q=OCIParse($c, "select status, src_quote_id, utc_msk from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_base_status=OCIResult($q,"STATUS");
	$src_quote_id=OCIResult($q,"SRC_QUOTE_ID");
	$utc_msk=OCIResult($q,"UTC_MSK");
echo "действие: получен старый статус записи; utc_msk: $utc_msk; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";	
	//

	//установка статусов================================================================================================================================
	//Статусы телефонов+++++++++++++++++++++++++++
	if($phone_id<>'') {	
		//inwork,otkaz, error
		if($new_phone_status=='inwork' or $new_phone_status=='otkaz' or $new_phone_status=='error') {
			//телефон
			OCIExecute(OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id)
			,OCI_DEFAULT);
echo "действие: установлен статус телефона; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
		}
		//
		//perez
		if($new_phone_status=='perez') {
			//телефон
			if($perez_phone<>'') {
				//если номер перезвона не пустой
				$q=OCIParse($c,"select id from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." and phone='".$perez_phone."' and nvl(ext,0)=nvl('".$perez_ext."',0)");
				OCIExecute($q,OCI_DEFAULT);
				if(OCIFetch($q)) {
					echo "Номер телефона и добавочный не поменялся<br>";
				}
				else {
					//ставим старому телефону статус end_perez
					OCIExecute(OCIParse($c,"update STC_PHONES set 
					status='end_perez', 
					status_date=sysdate,
					nedoz_count=''
					where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id),OCI_DEFAULT);
					echo "Поменялся тел. или доб. Старому телефону (ID: $phone_id) установлен статус end_perez<br>";
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
			
			$upd=OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			perez_date_msk=decode('".$perez_min."',NULL, decode('".$perez_date."',NULL,sysdate,to_date('".$perez_date."','DD.MM.YYYY HH24:MI')-nvl('".$utc_msk."',0)/24) ,sysdate+nvl('".$perez_min."',0)/1440),
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." returning to_char(perez_date_msk,'DD.MM.YYYY HH24:MI:SS') into :perez_date_msk");
			
			OCIBindByName($upd,":perez_date_msk",$perez_date,16);
			OCIExecute($upd,OCI_DEFAULT);	
echo "действие: установлен статус телефона; дата перезвона (мск): $perez_date; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
		}
		//
		//nedoz
		if($new_phone_status=='nedoz') {
			//телефон
			OCIExecute(OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			nedoz_count=nvl(nedoz_count,0)+1
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id)
			,OCI_DEFAULT);	
echo "действие: установлен статус телефона; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
		}
	}
	//++++++++++++++++++++++++++++++
echo "<hr>установка статуса записи<br>";
	//статусы записей==============================
	//inwork
	if($new_phone_status=='inwork') {
		OCIExecute(OCIParse($c,"update STC_BASE set
		start_date=sysdate, status_date=sysdate,status='".$new_phone_status."',lock_user='',
		phone_id='".$phone_id."',lock_date='', status_user=".$_SESSION['user']['id'].",
		status_type='call'
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id'])
		,OCI_DEFAULT);
		$new_base_status=$new_phone_status;
echo "действие: установлен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
	}
	//
	else {
		//анализ статусов телефонов для установки нового статуса записи=================
echo "анализ статусов телефонов для установки нового статуса записи<br>";		
		$q=OCIParse($c,"select count(*) count_phones, 
		count(perez) count_perez, 
		count(end_perez) count_end_perez, 
		to_char(min(perez_date),'YYYYMMDDHH24MISS') min_perez_date,
		count(nedoz) count_nedoz,
		min(nedoz_count) min_nedoz_count,
		count(otkaz) count_otkaz, count(error) count_error
		from (select 
		decode(p1.status,'perez',1,NULL) perez,
		decode(p1.status,'end_perez',1,NULL) end_perez,
		decode(p1.status,'perez',p1.perez_date_msk,NULL) perez_date,
		decode(p1.status,'nedoz',1,NULL) nedoz,
		decode(p1.status,'nedoz',p1.nedoz_count,NULL) nedoz_count,
		decode(p1.status,'otkaz',1,NULL) otkaz, 
		decode(p1.status,'error',1,NULL) error
		from STC_PHONES p1
		where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and allow='y')");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"COUNT_PHONES")>0) {
			$min_nedoz_count='';
			$min_nedoz_date='';
			//perez
			if(OCIResult($q,"COUNT_PEREZ")>0) {
				$new_base_status='perez';
				$perez_date=OCIResult($q,"MIN_PEREZ_DATE");
echo "действие: (1) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status; дата перезвона: $perez_date.<br>";
			}
			//nedoz
			elseif (OCIResult($q,"COUNT_NEDOZ")>0 and OCIResult($q,"COUNT_NEDOZ")+OCIResult($q,"COUNT_END_PEREZ")+OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")>=OCIResult($q,"COUNT_PHONES")) {
				$min_nedoz_count=OCIResult($q,"MIN_NEDOZ_COUNT");
				//минимальная дата недозвона из телефонов с минимальным количеством недозвонов
				$q1=OCIParse($c,"select to_char(min(status_date),'YYYYMMDDHH24MISS') min_nedoz_date from STC_PHONES 
				where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and allow='y' and status='nedoz' and nvl(nedoz_count,0)=nvl('".OCIResult($q,"MIN_NEDOZ_COUNT")."',0)");				
				OCIExecute($q1,OCI_DEFAULT);
				OCIFetch($q1);
				$min_nedoz_date=OCIResult($q1,"MIN_NEDOZ_DATE");
				$q_prj=OCIParse($c,"select nedoz_count from STC_PROJECTS where id=".$_SESSION['survey']['project']['id']);
				OCIExecute($q_prj, OCI_DEFAULT);
				OCIFetch($q_prj);
				if($min_nedoz_count>=OCIResult($q_prj,"NEDOZ_COUNT")) $new_base_status='end_nedoz';
				else $new_base_status='nedoz';
echo "действие: (2) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status; кол-во недозвонов: $min_nedoz_count; дата последнего недозвона: $min_nedoz_date.<br>";
			}
			//otkaz
			elseif(OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")+OCIResult($q,"COUNT_END_PEREZ")>=OCIResult($q,"COUNT_PHONES") and $new_phone_status=='otkaz') {
				$new_base_status='end_otkaz';
echo "действие: (3) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
			}
			//
			//error
			elseif(OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")+OCIResult($q,"COUNT_END_PEREZ")>=OCIResult($q,"COUNT_PHONES") and $new_phone_status=='error') {
				$new_base_status='end_error';
echo "действие: (4) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";
			}
			//
			else { 
			$new_base_status='';
echo "действие: (5) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";	
			}		
		}
		else {
			//если нет номеров, то ставится запрошенный статус
			$new_base_status=str_replace(array('error','otkaz'),array('end_error','end_otkaz'),$new_phone_status);	
echo "действие: (6) определен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
		}
		//установка статуса записи
		OCIExecute(OCIParse($c,"update STC_BASE set
		lock_date='',
		lock_user='',
		phone_id='".$phone_id."',
		status='".$new_base_status."',
		status_date=sysdate, 
		status_user=".$_SESSION['user']['id'].",
		status_type='call',
		perez_date_msk=decode('".$new_base_status."','perez',to_date('".$perez_date."','YYYYMMDDHH24MISS'),perez_date_msk),
		nedoz_count=decode('".$new_base_status."','nedoz','".$min_nedoz_count."','end_nedoz','".$min_nedoz_count."',NULL),
		nedoz_date=decode('".$new_base_status."','nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),'end_nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),NULL)
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
		),OCI_DEFAULT);
echo "действие: установлен статус записи; ID телефона: $phone_id; новый статус телефона: $new_phone_status; ИД записи: $base_id; старый статус записи: $old_base_status; новый статус записи: $new_base_status.<br>";		
	}

	//====================================


	//учет смены статусов в статистике и квотах
	if($old_base_status<>$new_base_status) {
		if($old_base_status=='') $minus=",stat_new=stat_new-1";
		else $minus=",stat_".$old_base_status."=stat_".$old_base_status."-1";
		if($new_base_status=='') $plus=",stat_new=stat_new+1";
		else $plus=",stat_".$new_base_status."=stat_".$new_base_status."+1";
		//общая по проекту
		OCIExecute(OCIParse($c,"update STC_PROJECTS set id=id ".$minus.$plus." where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "пересчитана статистика по проекту: $minus.$plus<br>";
		//статистика индексов
		OCIExecute(OCIParse($c,"update STC_SRC_INDEXES i set id=id ".$minus.$plus."
		where (i.field_id,i.value) in (select v.field_id,v.text_value from STC_FIELD_VALUES v where v.project_id=".$_SESSION['survey']['project']['id']." and v.base_id=".$base_id.") and i.project_id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "пересчитана статистика по исходным индексам: $minus.$plus<br>";
		if($src_quote_id<>'') {
			//статистика по исходным квотам
			OCIExecute(OCIParse($c,"update STC_SRC_QUOTES set id=id ".$minus.$plus." where project_id=".$_SESSION['survey']['project']['id']." and id=".$src_quote_id),OCI_DEFAULT);
echo "пересчитана статистика по исходной квоте: ID: $src_quote_id; $minus.$plus<br>";
		}
	}
	//

	if($new_base_status=='inwork') $result['action']='start_ank';
	elseif($old_base_status=='perez' and $new_base_status=='') $result['action']='next_base'; 
	elseif($new_base_status=='') $result['action']='next_phone';
	else $result['action']='next_base';
	
	$result['base_id']=$base_id;
	$result['new_base_status']=$new_base_status;
	$result['phone_id']=$phone_id;
	$result['phone_status']=$new_phone_status;
	OCICommit($c);
echo "результат установки статуса: action: $result[action]; base_id: $base_id; new_base_status: $new_base_status; phone id: ".$result['phone_id'].".";	
	return $result;

}
function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	echo "<br><font color=red>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	exit();
}
?>
