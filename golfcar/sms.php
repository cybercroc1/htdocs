<?php

if(isset($_POST['send'])) {
	if(!isset($_POST['id'])) exit();
	$id=$_POST['id'];
		
	?><script>
	parent.document.getElementById('send').disabled=true;
	</script><?php

	require_once "send_sms_redundant.php";
	$phone=phone_norm_single($_POST['phone'],'ru_dial');
	//echo $phone;
	if(substr($phone,1,1)!="9") {
		?><script>
		parent.document.getElementById('divlog').innerHTML='<font color=red>Ошибка: Введенный номер не является мобильным.</font>';
		parent.document.getElementById('send').disabled=false;
		</script><?php
		exit();
	} 
	include("sc/sc_conn_string.php");
	
	$q=OCIParse($c,"select t.call_base_id,t.project_id,t.report_id,t.form_id,t.obj_id,t.value_id,t.phone,t.new_phone,t.client_fio 
	from GOLFCAR_SMS_VIZITKI t
	where secret='".$id."' 
	order by date_add desc");
	OCIExecute($q);
	if(!$row=oci_fetch_assoc($q)) exit();
	
	$q=OCIparse($c,"select name manager_fio, dop_info from SC_FORM_VALUES t
	where id='".$row['VALUE_ID']."'");
	OCIExecute($q);
	if(!OCIFetch($q)) exit();
	$dop_info=trim(preg_replace("/\{[^{}]*\}/","",OCIResult($q,"DOP_INFO")));
	
	$Message=$dop_info;
	
	$results=send_sms($service_name='all',$phone,$fromPhone='Wilstream',$packet_id='',$Message,'n',$account='');
	
	foreach($results as $key => $val) {
		$ins=OCIParse($c,"insert into sc_sms_log (id,project_id,call_id,datetime,sender_ip,fromphone,phone_list,message,
		service_name,error_num,packet_id,user_packet_id,summ_phone,summ_parts,packet_cost,account) 
		values (SEQ_SMS_LOG_ID.nextval,'".$row['PROJECT_ID']."','".$row['CALL_BASE_ID']."',sysdate,'".$_SERVER['REMOTE_ADDR']."','".$fromPhone."','".$phone."','".$Message."',
		'".$val['service_name']."','".$val['result_text']."','".$val['packet_id']."','".$val['user_packet_id']."','".$val['summ_phones']."','".$val['summ_parts']."','".$val['packet_cost']."','".$account."')");
		OCIExecute($ins);
		OCICommit($c);
		$result=$val;
	}	
	
	$upd=OCIParse($c,"update GOLFCAR_SMS_VIZITKI 
	set
	send_date=sysdate,
	sms_result=:result,
	New_Phone='".$phone."'
	where call_base_id='".$row['CALL_BASE_ID']."' and secret='".$id."'");
	OCIBindByName($upd,":result",$result['result_text'],500);
	OCIExecute($upd);
	OCICommit($c);	
	
	//var_dump($result);
	
	if(substr($result['result_text'],0,2)=='OK') {
		?><script>
		parent.document.getElementById('divlog').innerHTML='<font color=green><b>Визитка отправлена.</b></font>';
		parent.document.getElementById('send').value='Отправлено';
		</script><?php
		exit();		
	}
	else {
		?><script>
		parent.document.getElementById('divlog').innerHTML='<font color=red><b>Ошбика отправки СМС: '<?=$result['result_text']?>'</b></font>';
		parent.document.getElementById('send').disabled=false;
		</script><?php
		exit();			
	}
	
exit();
}

if(!isset($_GET['id'])) exit();
$id=$_GET['id'];
include("sc/sc_conn_string.php");

$q=OCIParse($c,"select t.call_base_id,t.project_id,t.report_id,t.form_id,t.obj_id,t.value_id,t.phone,t.new_phone,t.client_fio,to_char(t.send_date,'DD.MM.YYYY HH24:MI:SS') send_date,t.sms_result 
from GOLFCAR_SMS_VIZITKI t
where secret='".$id."' 
order by date_add desc");
OCIExecute($q);
if(!$row=oci_fetch_assoc($q)) {header($_SERVER['SERVER_PROTOCOL']." 404 Not Found"); exit();}

$q=OCIparse($c,"select name manager_fio, dop_info from SC_FORM_VALUES t
where id='".$row['VALUE_ID']."'");
OCIExecute($q);
if(!OCIFetch($q)) {header($_SERVER['SERVER_PROTOCOL']." 404 Not Found"); exit();}
$dop_info=trim(preg_replace("/\{[^{}]*\}/","",OCIResult($q,"DOP_INFO")));
$manager_fio=OCIResult($q,"MANAGER_FIO");
require_once "send_sms_redundant.php";
?>
<!DOCTYPE HTML>
<html>
<head>
	<link href="style.css" rel="stylesheet" type="text/css">
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
	<div>
    <form action='sms.php' method='POST' target=ifr class='authform'>
	<input type=hidden name=id value='<?=$id?>'></input>
    <table border='0' cellspacing='0' cellpadding='8' align='center'>
        <tr><td colspan=2 align='center'><strong style='color: black; font-size: large;'>СМС-визитка:</strong></td></tr>
		<tr><td colspan=2 align='center'>Текст СМС:</td></tr><tr><td colspan=2 align='center'><strong><?php echo nl2br(htmlentities($dop_info)); ?></strong></td></tr>
		<?php if($row['CLIENT_FIO']<>"") {
			?><tr><td colspan=2 align='center'>Имя клиента:<br><strong><?php echo htmlentities($row['CLIENT_FIO']); ?></strong></td></tr><?php 
		} ?>
		<?php if(substr($row['SMS_RESULT'],0,2)=='OK') {?>
		<tr><td colspan=2 align='center'><strong>Телефон: <?=phone_segment($row['NEW_PHONE'])?></strong></td></tr>	
		<tr><td colspan=2 align='center'><strong><font color=green>Эта визитка уже отправлена<br>( <?=$row['SEND_DATE']?> )</font></strong></td></tr>
		<?php }
		else { ?>
		<tr><td colspan=2 align='center'>Телефон (ввод в любом формате):<br><strong><input name="phone" value='<?=phone_segment($row['PHONE'])?>'></strong></td></tr>
		<tr><td colspan=2 align='center'><input type='submit' id='send' name='send' value='Отправить визитку' class='submitbtn'></td></tr>
		<?php } ?>
	</table>
<div id=divlog class='inpitarea' align='center'>
</div>
</form>	</div>
<iframe name=ifr style=display:none></iframe>
</body>
</html>
