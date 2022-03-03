<?php
if(!isset($_GET['token']) or $_GET['token']<>'e1345ed7-fcb8-4a51-98f0-dda299da490c') exit(); 
//extract($_POST);

//названия полей отчетов, содержищих контактный номер
//индекс массива - ID формы
//гольф технолоджи
$client_phone_field['9484']='';
$client_fio_field['9484']='';
$manager_fio_field['9484']='Если звонок перевели, записать на кого, ФИО';
//Гольф кар
$client_phone_field['30202']='';
$client_fio_field['30202']='';
$manager_fio_field['30202']='Если звонок перевели, записать на кого, ФИО';
//Мобайл кар
$client_phone_field['26062']='';
$client_fio_field['26062']='';
$manager_fio_field['26062']='Если звонок перевели, записать на кого, ФИО';

//Спайдер тур
$client_phone_field['30685']='';
$client_fio_field['30685']='';
$manager_fio_field['30685']='Если звонок перевели, записать на кого, ФИО';

$call_id=$_POST['call_id'];
$report_id=$_POST['report_id'];
$form_id=$_POST['form_id'];
if(isset($_POST[str_replace(" ","_",$client_phone_field[$form_id])])) $client_phone=$_POST[str_replace(" ","_",$client_phone_field[$form_id])]; else $client_phone='';
if(isset($_POST[str_replace(" ","_",$client_fio_field[$form_id])])) $client_fio=$_POST[str_replace(" ","_",$client_fio_field[$form_id])]; else $client_fio='';
if(isset($_POST[str_replace(" ","_",$manager_fio_field[$form_id])])) $manager_fio=$_POST[str_replace(" ","_",$manager_fio_field[$form_id])]; else {echo "Нет ФИО менеджера"; exit();}

//var_dump($_POST);

//include('phone_conv_single.php');
include("send_sms_redundant.php");
$client_phone = (phone_norm_single($client_phone,'ru_dial'));
if($client_phone=='') {
	//$client_phone='';
	if(isset($_POST['caller_num'])) {
		$client_phone = (phone_norm_single($_POST['caller_num'],'ru_dial'));
	}
}

include("sc/sc_conn_string.php");

$q=OCIParse($c,"select project_id from SC_CALL_BASE 
where id='".$call_id."'");
OCIExecute($q);
if(OCIFetch($q)) {
$project_id=OCIResult($q,"PROJECT_ID");
}

$q=OCIParse($c,"select o.id obj_id, fv.id val_id from SC_CALL_REPORT r, SC_FORM_OBJECT o, SC_CALL_REPORT_VALUES v, sc_form_values fv
where r.id='".$report_id."' 
and o.project_id='".$project_id."' and o.form_id='".$form_id."' and o.name='".$manager_fio_field[$form_id]."'
and v.call_report_id=r.id and v.object_id=o.id
and fv.project_id=r.project_id and fv.obj_id=o.id and fv.name=v.value");

OCIExecute($q);
if(OCIFetch($q)) {
$obj_id=OCIResult($q,"OBJ_ID");
$value_id=OCIResult($q,"VAL_ID");
}

include('pass_gen.php');
$secret=pass_gen(4);

$ins=OCIParse($c,"insert into GOLFCAR_SMS_VIZITKI
(date_add,call_base_id,project_id,report_id,form_id,obj_id,value_id,secret,phone,client_fio)
values(sysdate,'".$call_id."','".$project_id."','".$report_id."','".$form_id."','".$obj_id."','".$value_id."','".$secret."','".$client_phone."',:fio)");
OCIBindByName($ins,":fio",$client_fio);
OCIExecute($ins);
OCICommit($c);

//поиск телефонов менеджера
echo "<hr>Поиск телефона менеджера<br>";
if(isset($obj_id) and isset($value_id)) {
	echo "<hr>Выполнение запроса<br>";
	$q=OCIParse($c,"select dop_info from SC_FORM_VALUES t
	where t.project_id='".$project_id."' and t.obj_id='".$obj_id."' and t.id='".$value_id."'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		echo "<hr>Менеджер найден<br>";
		$dop_info=OCIResult($q,"DOP_INFO");
		if(preg_match("/\{[^{}]*\}/",$dop_info,$matches)) {
			$phone=str_replace(array("{","}"),"",$matches[0]);
			echo $phone."<br>";
		}
	}
}

//отправка смс со ссылкой на отправку визитки
if(isset($phone)) {
	echo "<hr>Отпрака ссылки на СМС-визитку<br>";
	$Message="SMS-визитка. ".($client_fio<>""?$client_fio.". ":"")." https://sc.wilstream.ru/golfcar/sms.php?id=$secret";
	
	$results=send_sms($service_name='all',$phone,$fromPhone='Wilstream',$packet_id='',$Message,'n',$account='');
	
	echo "Результат отправки ссылки на СМС-визитку.<br>";
	var_dump($results);
	
	foreach($results as $key => $val) {
		$ins=OCIParse($c,"insert into sc_sms_log (id,project_id,call_id,datetime,sender_ip,fromphone,phone_list,message,
		service_name,error_num,packet_id,user_packet_id,summ_phone,summ_parts,packet_cost,account) 
		values (SEQ_SMS_LOG_ID.nextval,'".$project_id."','".$call_id."',sysdate,'".$_SERVER['REMOTE_ADDR']."','".$fromPhone."','".$phone."','".$Message."',
		'".$val['service_name']."','".$val['result_text']."','".$val['packet_id']."','".$val['user_packet_id']."','".$val['summ_phones']."','".$val['summ_parts']."','".$val['packet_cost']."','".$account."')");
		OCIExecute($ins);
		OCICommit($c);
		$result=$val;
	}
	
}

?>