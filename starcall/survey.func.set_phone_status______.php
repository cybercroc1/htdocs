<?php
function set_call_status($new_status,$base_id,$phone,$perez_date) {
global $c;

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);

echo "**".$phone['ord']." | ".$phone['num']."**".$new_status."**";

/*
1. если нет base_id (соответственно нет phone_ord) и есть phone значит добавляем запись и номер, возвращаем новый base_id и новый phone_ord
если нет phone_ord и есть phone, значит добавляем телефон к записи, возвращаем base_id и новый phone_ord
если нет phone_ord и нет phone, значит запись без телефона
если статус - inwork - возвращаем base_id, phone_ord, phone (если есть) и start_ank
если если в итоге записи утановлен какой-либо статус (нет телефонов для обзвона в данный момент) возвращаем next_base 
*/

echo "ДО статус:".$new_status." базИД:".$base_id." тел:".$phone['num']." фонорд:".$phone['ord']." перездат:".$perez_date."<br>";

//1. если нет base_id и нет телефона и статус inwork, возвращаем start_ank (запись со статусом добавится в survey.ank.frame)
if($base_id=='' and $phone['num']=='' and $new_status=='inwork') {
	$result['action']='start_ank';
	$result['base_id']='';
	$result['phone']['ord']='';
	$result['phone']['num']='';
	return $result;
}

//2. если нет base_id (соответственно нет phone_ord) и есть phone значит добавляем запись и номер, возвращаем новый base_id и новый phone_ord
if($base_id=='' and $phone['num']<>'') {
	//создаем запись
	$ins=OCIParse($c, "insert into STC_BASE (id,project_id,allow,lock_user,lock_date) 
	values (seq_stc_base_id.nextval, ".$_SESSION['survey']['project']['id'].",'y',".$_SESSION['user']['id'].",sysdate)
	returning id into :base_id");
	OCIBindByName($ins,":base_id",$base_id,16);
	OCIExecute($ins,OCI_DEFAULT);
	//добавляем к статистике по проекту
	OCIExecute(OCIParse($c,"update STC_PROJECTS set stat_new=stat_new+1 where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);	
	//создаем телефон
	$ins=OCIParse($c, "insert into STC_PHONES (base_id, project_id, phone, ord, allow)
	values (".$base_id.", ".$_SESSION['survey']['project']['id'].",
	substr(".$phone['num'].",0,25),nvl((select max(ord) from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".base_id."),1),'y') 
	returning (phone,ord) into (:phone,:phone_ord)");
	OCIBindByName($ins,":phone",$phone['num'],16);
	OCIBindByName($ins,":phone_ord",$phone['ord'],16);
}

//получаем старый статус записи
$q=OCIParse($c, "select status from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$old_base_status=OCIResult($q,"STATUS");
//

//установка статусов================================================================================================================================
//Статусы телефонов+++++++++++++++++++++++++++
if($phone['ord']<>'') {
//inwork,otkaz, error
if($new_status=='inwork' or $new_status=='otkaz' or $new_status=='error') {
	//телефон
	OCIExecute(OCIParse($c,"update STC_PHONES set 
	status='".$new_status."', status_date=sysdate
	where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and ord=".$phone['ord'])
	,OCI_DEFAULT);
}
//
//perez
if($new_status=='perez') {
	//телефон
	OCIExecute(OCIParse($c,"update STC_PHONES set 
	status='".$new_status."', status_date=sysdate,
	perez_date_msk=decode('".$perez_date."',NULL,sysdate+15/1440,to_date('".$perez_date."','YYYYMMDDHH24MISS')),
	nedoz_count=0
	where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and ord=".$phone['ord'])
	,OCI_DEFAULT);	
}
//
//nedoz
if($new_status=='nedoz') {
	//телефон
	OCIExecute(OCIParse($c,"update STC_PHONES set 
	status='".$new_status."', status_date=sysdate,
	nedoz_count=nvl(nedoz_count,0)+1
	where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and ord=".$phone['ord'])
	,OCI_DEFAULT);	
}
}
//++++++++++++++++++++++++++++++

//статусы записей==============================
//inwork
if($new_status=='inwork') {
	OCIExecute(OCIParse($c,"update STC_BASE set
	start_date=sysdate, status_date=sysdate,status='".$new_status."',lock_user=".$_SESSION['user']['id'].",
	phone='".$phone['num']."',lock_date=sysdate, status_user=".$_SESSION['user']['id']."
	where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id'])
	,OCI_DEFAULT);
	//добавляем в список незавешенных пользователя
	OCIExecute(OCIParse($c,"insert into STC_USER_INWORK (user_id,project_id,base_id) values (".$_SESSION['user']['id'].",".$_SESSION['survey']['project']['id'].",".$base_id.")")
	,OCI_DEFAULT);
	$_SESSION['survey']['inwork_id']=$base_id;
}
//
else {
	//анализ статусов телефонов для установки нового статуса записи=================
	$q=OCIParse($c,"select count(*) count_phones, 
count(perez) count_perez, to_char(min(perez_date),'YYYYMMDDHH24MISS') min_perez_date,
count(nedoz) count_nedoz, to_char(min(nedoz_date),'YYYYMMDDHH24MISS') min_nedoz_date, min(nedoz_count) min_nedoz_count,
count(otkaz) count_otkaz, count(error) count_error
from (select 
decode(p1.status,'perez',1,NULL) perez,
decode(p1.status,'perez',p1.perez_date_msk,NULL) perez_date,
decode(p1.status,'nedoz',1,NULL) nedoz,
decode(p1.status,'nedoz',p1.status_date,NULL) nedoz_date,
decode(p1.status,'nedoz',p1.nedoz_count,NULL) nedoz_count,
decode(p1.status,'otkaz',1,NULL) otkaz, 
decode(p1.status,'error',1,NULL) error
from STC_PHONES p1
where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id.")");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(OCIResult($q,"COUNT_PHONES")>0) {
		$min_nedoz_count='';
		$min_nedoz_date='';
		//perez
		if(OCIResult($q,"COUNT_PEREZ")>0) {
			$new_base_status='perez';
			$perez_date=OCIResult($q,"MIN_PEREZ_DATE");
		}
		//nedoz
		elseif (OCIResult($q,"COUNT_NEDOZ")>0 and OCIResult($q,"COUNT_NEDOZ")+OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")>=OCIResult($q,"COUNT_PHONES")) {
			$min_nedoz_count=OCIResult($q,"MIN_NEDOZ_COUNT");
			$min_nedoz_date=OCIResult($q,"MIN_NEDOZ_DATE");
			$q_prj=OCIParse($c,"select nedoz_count from STC_PROJECTS where id=".$_SESSION['survey']['project']['id']);
			OCIExecute($q_prj, OCI_DEFAULT);
			OCIFetch($q_prj);
			if($min_nedoz_count>=OCIResult($q_prj,"NEDOZ_COUNT")) $new_base_status='nedoz';
			else $new_base_status='end_nedoz';
		}
		//otkaz
		elseif(OCIResult($q,"COUNT_OTKAZ")>=OCIResult($q,"COUNT_PHONES")) {
			$new_base_status='end_otkaz';
		}
		//
		//error
		elseif(OCIResult($q,"COUNT_ERROR")>=OCIResult($q,"COUNT_PHONES")) {
			$new_base_status='end_error';
		}
		//
		else $new_base_status='';
	}
	else $new_base_status=$new_status; //если нет номеров, то ставится запрошенный статус

	echo "---".$new_base_status."---";

	//установка статуса записи
	OCIExecute(OCIParse($c,"update STC_BASE set
	status='".$new_base_status."',
	status_date=sysdate, 
	status_user=".$_SESSION['user']['id'].",
	perez_date_msk=decode('".$new_base_status."','perez',to_date('".$perez_date."','YYYYMMDDHH24MISS'),perez_date_msk),
	nedoz_count=decode('".$new_base_status."','nedoz','".$min_nedoz_count."','end_nedoz','".$min_nedoz_count."',NULL),
	nedoz_date=decode('".$new_base_status."','nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),'end_nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),NULL)
	where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
	),OCI_DEFAULT);
	//сброс блокировки пользователя
	OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id)
	,OCI_DEFAULT);

	$_SESSION['survey']['inwork_id']='';
}
//====================================


//учет смены статусов в статистике и квотах



//

OCICommit($c);
}
?>