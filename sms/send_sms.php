<?php

include('check_ip.php');
if(!check_ip($_SERVER['REMOTE_ADDR'],'
192.168.12.0/255.255.254.0
172.31.1.1
')) {
echo 'Error: Access Denied(IP)';
exit();
}

extract($_REQUEST);

if(!isset($project_id)) {echo 'Error: Access Denied(project)'; exit();}

include("../../sc_conf/sc_conn_string");
include("send_sms_redundant.php");

$q=OCIParse($c,"select * from SC_PROJECTS t
where id='".$project_id."' and t.sms_disabled is null");
OCIExecute($q);
if(!OCIFetch($q)) {echo 'Error: Access Denied(project)'; exit();}

if(!isset($call_id)) $call_id='';
if(!isset($log_id)) $log_id='';
if(!isset($Phone_list)) $Phone_list='';
if(!isset($Message)) $Message='';
if(!isset($fromPhone)) $fromPhone='Wilstream';

	$phone=trim($Phone_list);

	$service_name='smsc.ru';
	
	$q=OCIParse($c,"select nvl(max(user_packet_id)+1,200) user_packet_id from SC_SMS_LOG t");
	OCIExecute($q);
	OCIFetch($q);
	$packet_id=OCIResult($q,"USER_PACKET_ID");
	
	$results=send_sms($service_name,$phone,$fromPhone,$packet_id,$Message,'n');
	//���
	$ins=OCIParse($c,"insert into sc_sms_log (id,project_id,call_id,datetime,sender_ip,fromphone,phone_list,message,
	service_name,error_num,packet_id,user_packet_id,summ_phone,summ_parts,packet_cost) 
	values (SEQ_SMS_LOG_ID.nextval,	:project_id, :call_id, sysdate,'".$_SERVER['REMOTE_ADDR']."', :fromPhone, :phone, :Message,	:service_name, :result_text, :packet_id, :user_packet_id,
	:summ_phones, :summ_parts, :packet_cost)");
	foreach($results as $key => $val) {
		OCIBindByName($ins,":project_id",$project_id);
		OCIBindByName($ins,":call_id",$call_id);
		OCIBindByName($ins,":fromPhone",$fromPhone);
		OCIBindByName($ins,":phone",$phone);
		OCIBindByName($ins,":Message",$Message);
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
	
	echo $result['result_text'];

?>