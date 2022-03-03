<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка Все-Свои</title>
</head>
<body>
<?php
extract($_REQUEST);
set_error_handler ("my_error_handler");
$err_count=0;

if (!isset($cdpn)) $cdpn='';
if (!isset($cgpn)) $cgpn='';
if (!isset($agid)) $agid='';

include("../../sup_conf/sup_conn_string");

if (isset($send)) {

if(!isset($trbl_id)) {echo "<script>alert('ОШИБКА! Не выбран тип проблемы!');</script>"; exit();}

$trbls=implode(',',$trbl_id);

$q=OCIParse($c,"select distinct trbl_grp_id from sup_trbl_type
where id in ($trbls)");
OCIExecute($q,OCI_DEFAULT);
$i=0;
while(OCIFetch($q)) {
	zayavka($num_zayavki,OCIResult($q,"TRBL_GRP_ID"),$i);
	//echo $num_zayavki."-".OCIResult($q,"TEXNARI_GROUP_ID")."<hr>";
$i++;
}
if ($err_count==0) echo "<script>alert('Заявка №$num_zayavki отправлена.');
parent.document.all.send.style.display='none';
parent.close();
</script>";
}

else {
echo "<form target=oper_ifr>";
$q=OCIParse($c,"select sup_base_id.nextval from dual");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$num_zayavki=OCIResult($q,"NEXTVAL");

echo "<input type=hidden name=cdpn value='$cdpn'>";
echo "<input type=hidden name=cgpn value='$cgpn'>";
echo "<input type=hidden name=agid value='$agid'>";
echo "<input type=hidden name=num_zayavki value='$num_zayavki'>";

echo "Заявка №$num_zayavki<hr>";
echo "<input type=hidden name=klinika_id value='20'>";

echo "ФИО (кто звонил):<input type=text name=kto></input><br>";

echo "Тип проблмы:<br>";
$q=OCIParse($c,"select id,name from sup_trbl_type
where id in (9,10,11,12,13,14,15,16,17,19,20)");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
//if(isset($perv_grp) and $perv_grp<>OCIResult($q,"TEX_WRKGRP_ID")) echo "<hr>";
//$perv_grp=OCIResult($q,"TEX_WRKGRP_ID");

echo "<input type=checkbox name=trbl_id[] value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</input><br>";}
echo "</select><br>";

echo "ФИО (у кого не работает):<input type=text name=u_kogo></input><br>";

echo "Описание проблемы:<br><textarea cols=30 rows=5 name=oper_coment></textarea><br>";
echo "<input type=submit name=send value='Отправить'>";
echo "</form>";
echo "<iframe name=oper_ifr <iframe style='display:none'></iframe>";
echo "
<script>
//document.all.send.disabled=true;
//function ch_klinik() {
//if (document.all.klinika_id=='') document.all.send.disabled=true;
//else document.all.send.disabled=false;
//}
</script>";
}

function zayavka($num_zayavki,$trbl_grp_id,$num_copy) {
global $c;
global $cdpn;
global $cgpn;
global $agid;
global $oper_coment;
global $klinika_id;
global $kto;
global $u_kogo;
global $trbl_id;
global $trbls;
global $err_count;

if($num_copy>0) {
	$q=OCIParse($c,"select sup_base_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$num_zayavki=OCIResult($q,"NEXTVAL");
}

$ins=OCIParse($c,"insert into sup_base (id,date_in_call,cdpn,cgpn,agid,oper_comment,klinika_id,kto,u_kogo,wrkgrp_id,trbl_grp_id)
values ('$num_zayavki',sysdate,'$cdpn','$cgpn','$agid','$oper_coment','$klinika_id','$kto','$u_kogo','$trbl_grp_id','$trbl_grp_id')");
OCIExecute($ins);

//все проблемы
foreach($trbl_id as $value) {
	$ins=OCIParse($c,"insert into sup_trbl_alloc (base_id,trbl_type_id)
values ('$num_zayavki','$value')");
	OCIExecute($ins);
}
//

OCICommit($c);

$q=OCIParse($c,"select name from sup_klinika where id='".$klinika_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$klinika_name=OCIResult($q,"NAME");

$mess="
<b>Клиника:</b> ".$klinika_name." </b><br>
<b>Кто звонил:</b> ".$kto." <b>АОН:</b> ".$cdpn."<br>
<b>Тип проблемы:</b><br>";

$q=OCIParse($c,"select t.name from sup_trbl_alloc a, sup_trbl_type t
where a.trbl_type_id=t.id and a.base_id='$num_zayavki'");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	$mess.=OCIResult($q,"NAME")."<br>";
	}
$mess.="<b>У кого не работает:</b>".$u_kogo."<br>";
$mess.="<b>Описание проблемы:</b><br>
".nl2br($oper_coment);


$q=OCIParse($c,"select distinct ste.email from sup_trbl_type stt, sup_lt slt, sup_user su, sup_texnari_emails ste
where stt.trbl_grp_id='".$trbl_grp_id."' and slt.location_id='".$klinika_id."' 
and slt.trbl_id=stt.id 
and decode(su.lt_grp_id,0,slt.lt_grp_id,su.lt_grp_id)=slt.lt_grp_id and su.send='y' and su.deleted is null 
and ste.texnari_id=su.id");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	send('bulka',OCIResult($q,"EMAIL"), 'stomsupport@wilstream.ru', 'Заявка №'.$num_zayavki,$mess);
	//echo OCIResult($q,"EMAIL")."<br>".$mess."<hr>";
	}
}

//функция отправки через сокет

function send($server, $to, $from, $title,$mess) {
	$headers="MIME-Version: 1.0 \r\n";
	$headers.="Content-Type: text/html; charset=\"windows-1251\"\r\n";
	$headers="To: ".$to."\r\nFrom: ".$from."\r\nSubject: ".$title."\r\n".$headers; 
	$fp = fsockopen($server, 25,$errno,$errstr,30); 
	if (!$fp) die("Server $server. Connection failed: $errno, $errstr"); 
		fputs($fp,"HELO bill\r\n"); 
		fputs($fp,"MAIL FROM: ".$from."\r\n"); 
		fputs($fp,"RCPT TO: ".$to."\r\n"); 
		fputs($fp,"DATA\r\n"); 
		fputs($fp,$headers."\r\n".$mess."\r\n"."."."\r\n");  
		fputs($fp,"QUIT\r\n"); 
		while(!feof($fp)) {    
		echo fgets($fp,1024);
		echo "<br>";    
		}
		fclose($fp);
		echo "<hr>";    
}
//Функция обработки ошибок
function my_error_handler($code, $msg, $file, $line) {

global $err_count;
global $c;
$err_count++;
OCIRollback($c);
echo "<script language='JavaScript'> 
alert('ОШИБКА! $code - ".(str_replace('\'',' ',$msg))." - ".(str_replace('\'',' ',$file))." - ".(str_replace('\'',' ',$line))."');
parent.document.all.send.style.display='';
parent.document.all.send.value='ПОВТОРИТЬ';
</script>";
exit();
}
?>
</body>
</html>
