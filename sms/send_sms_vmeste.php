<?php
//API для отправки СМС по проекту "VMESTE"

function autoutf8($text) {
	if(preg_match('/^.{1}/us',$text)) {
		$text=iconv('UTF-8','windows-1251//IGNORE',$text);
	}
	return $text;
}


extract($_REQUEST);

include("../../sc_conf/sc_conn_string");
include("send_sms_redundant.php");

$p=fopen('send_sms_vmeste.log',"a");
$log_str='';
foreach($_REQUEST as $name=>$val) {
	$log_str.=$name."=".$val.";";
}

	if(!isset($phone_code) or $phone_code=='' or !isset($secret_uuid) or !isset($message)) {
		echo "Error: Invalid request";
		$log_str=date('d.m.Y H:m:s').'; '.$_SERVER['REMOTE_ADDR'].'; Error: Invalid request; '.$log_str.chr(13).chr(10);
		fputs($p,$log_str);
		fclose($p);
		exit();
	}
	//$message=iconv("UTF-8", "windows-1251",$message);
	require_once 'uid.php';
	if(!check_uuid(trim($secret_uuid))) {
		echo "Error: Invalid secret uuid";
		$log_str=date('d:m:Y H:m:s').'; '.$_SERVER['REMOTE_ADDR'].'; Error: Invalid secret uuid; '.$log_str.chr(13).chr(10);
		fputs($p,$log_str);
		fclose($p);
		exit();
	}
	//получаем телефон по коду и секрету
	$q=OCIParse($c,"select phone,phone_type from vmeste_base where code='".trim($phone_code)."' and secret_uuid='".$secret_uuid."'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		$phone=OCIResult($q,"PHONE");
		$phone_type=OCIResult($q,"PHONE_TYPE");
	}	
	else {
		echo "Error: Phone not found";
		$log_str=date('d.m.Y H:m:s').'; '.$_SERVER['REMOTE_ADDR'].'; Error: Phone not found; '.$log_str.chr(13).chr(10);
		fputs($p,$log_str);		
		fclose($p);
		exit();
	}
	if($phone_type<>'ru_mob') {
		echo "Error: Invalid phone type: ".$phone_type;
		$log_str=date('d.m.Y H:m:s').'; '.$_SERVER['REMOTE_ADDR'].'; Error: Invalid phone type: '.$phone_type.'; '.$log_str.chr(13).chr(10);
		fputs($p,$log_str);	
		fclose($p);
	}
	
	$project_id='2048';
	$fromPhone='VMESTE';	
	$service_name='all';
	
	$q=OCIParse($c,"select nvl(max(user_packet_id)+1,200) user_packet_id from SC_SMS_LOG t");
	OCIExecute($q);
	OCIFetch($q);
	$packet_id=OCIResult($q,"USER_PACKET_ID");
	
	$message=autoutf8($message);
	
	$results=send_sms($service_name,$phone,$fromPhone,$packet_id,$message,$debug='n');
	//ЛОГ
	$ins=OCIParse($c,"insert into sc_sms_log (id,project_id,call_id,datetime,sender_ip,fromphone,phone_list,message,
	service_name,error_num,packet_id,user_packet_id,summ_phone,summ_parts,packet_cost) 
	values (SEQ_SMS_LOG_ID.nextval,	:project_id, :call_id, sysdate,'".$_SERVER['REMOTE_ADDR']."', :fromPhone, :phone, :Message,	:service_name, :result_text, :packet_id, :user_packet_id,
	:summ_phones, :summ_parts, :packet_cost)");
	foreach($results as $key => $val) {
		OCIBindByName($ins,":project_id",$project_id);
		OCIBindByName($ins,":call_id",$call_id);
		OCIBindByName($ins,":fromPhone",$fromPhone);
		OCIBindByName($ins,":phone",$phone);
		OCIBindByName($ins,":Message",$message);
		OCIBindByName($ins,":service_name",$val['service_name']);
		OCIBindByName($ins,":result_text",$val['result_text']);
		OCIBindByName($ins,":packet_id",$val['packet_id']);
		OCIBindByName($ins,":user_packet_id",$val['user_packet_id']);
		OCIBindByName($ins,":summ_phones",$val['summ_phones']);
		OCIBindByName($ins,":summ_parts",$val['summ_parts']);
		OCIBindByName($ins,":packet_cost",$val['packet_cost']);
		OCIExecute($ins);
		OCICommit($c);
		$result=$val;
	}

	$log_str=date('d.m.Y H:m:s').'; '.$_SERVER['REMOTE_ADDR'].'; '.$result['result_text'].'; '.$log_str.chr(13).chr(10);
	fputs($p,$log_str);	
	fclose($p);
	
	echo $result['result_text'];

?>
