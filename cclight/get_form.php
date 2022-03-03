<?php
//http://sc.wilstream.ru/cclight/get_form.php?1e84eec9-4e50-47c2-9830-4fa01092fc87&NAME=%D0%B2%D0%B8%D0%BA%D1%82%D0%BE%D1%80&PHONE=9251166091&EMAIL=jjjjjhjhjhjuj

if(!isset($_POST['1e84eec9-4e50-47c2-9830-4fa01092fc87'])) {
	echo 'Error: Запрещено';
	exit();
}

if(isset($_REQUEST['phone'])) $phone=$_REQUEST['phone']; else $phone='';
if(isset($_REQUEST['name'])) $name=$_REQUEST['name']; else $name='';
if(isset($_REQUEST['email'])) $email=$_REQUEST['email']; else $email='';
if(isset($_REQUEST['product'])) $product=$_REQUEST['product']; else $product='';



$f=fopen('log.log','a+');
$t=date('d.m.Y H:i:s')."<br>\n";
$t.="POST\n".$_SERVER['REMOTE_ADDR']."<br>\n";
$t.=var_export($_POST,true)."<br>\n";
$t.="GET\n".$_SERVER['REMOTE_ADDR']."<br>\n";
$t.=var_export($_GET,true)."<br>\n";
$t.="<br>\n----------------<br>\n";
fwrite($f,$t);
fclose($f);

//exit();

//добавление отчета в проект
//http://sc.wilstream.ru/local/sc/sc_form_save.php?project_id=2252&form_id=31665&form_values[28172]=Виктор&form_values[28173]=89251166091&form_values[28174]=sva@wilstream.ru&form_values[28175]=124945
$send_email_url="http://sc.wilstream.ru/local/sc/sc_form_save.php".
"?project_id=2252".
"&form_id=31665".
"&form_values[28172]=".urlencode(iconv('utf-8','windows-1251',$name)).
"&form_values[28173]=".urlencode(iconv('utf-8','windows-1251',$phone)).
"&form_values[28174]=".urlencode(iconv('utf-8','windows-1251',$email)).
"&form_values[28241]=".urlencode(iconv('utf-8','windows-1251',$product)).
"&form_values[28175]=124945"; //соглачие на обработку персональных данных

$res=file_get_contents($send_email_url);
echo "SAVE REPORT: ".trim($res);
//exit();

//отправка уведомления через mailer
$to_email='lena@wilstream.ru,cclight@willstream.ru,sva@wilstream.ru,CCLight@sberbank.ru,panfilova@wilstream.ru';

$send_email_url="http://sc.wilstream.ru/local/mailer/send.php?token=ZachBRDAz8X2L3af&to_email=".urlencode($to_email)."&email=".urlencode($email).
"&name=".urlencode($name)."&phone=".urlencode($phone)."&product=".urlencode($product);

$email_res=file_get_contents($send_email_url);
echo "Email: ".trim($email_res);
//exit();
//

echo "
SMS: ";

include("../../sc_conf/sc_conn_string");
include("send_sms_redundant.php");

$project_id='2252';

if(!isset($call_id)) $call_id='';
if(!isset($log_id)) $log_id='';
if(!isset($phone)) $phone='';
require_once 'phone_conv_single.php';
$phone=phone_norm_single($phone,'ru_dial');

$Message='Благодарим за обращение. 
В ближайшее время наш специалист свяжется с Вами для уточнения подробностей.
Контакт-Центр Лайт.
http://cclight.ru/
КП: https://cclight.wilstream.ru/kp.pdf';
$fromPhone='Wilstream';
$account='littlehorse';

	$phone=trim($phone);
	
	if(substr($phone,0,2)!='89') {
		echo 'Error: Неверный номер';
		exit();		
	}
	
	$service_name='all';
	
	$q=OCIParse($c,"select nvl(max(user_packet_id)+1,200) user_packet_id from SC_SMS_LOG t");
	OCIExecute($q);
	OCIFetch($q);
	$packet_id=OCIResult($q,"USER_PACKET_ID");
	
	$results=send_sms($service_name,$phone,$fromPhone,$packet_id,$Message,'n',$account);
	//ЛОГ
	$ins=OCIParse($c,"insert into sc_sms_log (id,project_id,call_id,datetime,sender_ip,fromphone,phone_list,message,
	service_name,error_num,packet_id,user_packet_id,summ_phone,summ_parts,packet_cost,account) 
	values (SEQ_SMS_LOG_ID.nextval,	:project_id, :call_id, sysdate,'".$_SERVER['REMOTE_ADDR']."', :fromPhone, :phone, :Message,	:service_name, :result_text, :packet_id, :user_packet_id,
	:summ_phones, :summ_parts, :packet_cost, :account)");
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
		OCIBindByName($ins,":account",$account);
		OCIExecute($ins);
		OCICommit($c);
		$result=$val;
	}
	
	echo $result['result_text'];

?>
