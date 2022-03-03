<?php

if(!isset($_POST['cost']) or !isset($_POST['email']) or !isset($_POST['comment']) or !isset($_POST['phone_number'])) {
	echo "Отсутсвуют данные"; exit();
}

$f=fopen('ordernum.ini','a+');

$ordernum=fgets($f);
if($ordernum=='') $ordernum=1000;
else $ordernum++;
ftruncate($f,0);
fwrite($f,$ordernum);

include("REST.php");
include("uid.php");
include("show_array.php");

//$url="https://securepayments.sberbank.ru/payment/rest/register.do";
$url="https://3dsec.sberbank.ru/payment/rest/register.do";

$post_values['userName']="T7736331614_2193-api";
$post_values['password']="T7736331614_2193";
$post_values['orderNumber']=$ordernum; //microtime(true)*10000;
$post_values['amount']=$_POST['cost']*100;
$post_values['email']=$_POST['email'];
$post_values['description']=$_POST['comment']." ".$_POST['phone_number'];
$post_values['returnUrl']="https://vse-svoi.ru/payBank/?type=APPROVED";
$post_values['failUrl']="https://vse-svoi.ru/payBank/?type=DECLINED";

//echo $post_values['amount'];

//echo $post_values['description'];

$res=json_post($url,'',$post_values,'',$ext_encoding='cp1251',$int_encoding='utf-8',$post_type='uform',$return_type='array');

//show_array($res);

if($res['text']=='OK') {

	if(isset($res['values']['formUrl'])) {
		echo "<script>document.location='".$res['values']['formUrl']."';</script>";
	}
	else {
		if(isset($res['values']['errorCode'])) echo $res['values']['errorMessage'];
	}
}
else echo $res['text']; 

?>