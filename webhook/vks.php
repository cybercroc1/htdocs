<?php 
extract($_REQUEST);

$fp=fopen('request.log','a+');
fputs($fp,date('Y.m.d H:i:s').chr(13).chr(10));
fwrite($fp,"REQUEST: ".print_r($_REQUEST,1).chr(13).chr(10));
fwrite($fp,"SERVER: ".print_r($_SERVER,1).chr(13).chr(10));
fclose($fp);

if(!isset($token) or $token!='5ef331a4-a95d-425d-be60-ae3fe945700a') exit();

require_once 'phone_conv_single.php';

if(!isset($phone)) {
	echo "Wrong phone";
	exit();
}
if(!isset($deal_id)) {
	echo "Wrong deal_id";
	exit();
}
if(!isset($request_type)) {
	echo "Wrong request_type";
	exit();
}

$phone_norm=phone_norm_single($phone,'ru_dial');

require_once 'oktadmin/oktell_conn_string.php';

$ins_data=array(
'phone'=>$phone_norm,
'deal_id'=>$deal_id,
'request_type'=>$request_type,
'custom_data'=>$request_type,
'src_phone'=>$phone
);
$query = $c_okt->prepare('
insert into [Учебный центр ВКС] 
(date_add,phone,deal_id,request_type,custom_data,src_phone) 
values (getdate(),:phone,:deal_id,:request_type,:custom_data,:src_phone)');
$query->execute($ins_data);
echo 'OK:'.$query->rowCount();

?>
</BODY>
</HTML>

