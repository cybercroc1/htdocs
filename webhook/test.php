<?php 
ini_set( 'default_charset', 'UTF-8' );

echo ini_get('default_charset');
/*extract($_REQUEST);

$fp=fopen('test.log','a+');
fputs($fp,date('Y.m.d H:i:s').chr(13).chr(10));
fwrite($fp,"REQUEST: ".print_r($_REQUEST,1).chr(13).chr(10));
fwrite($fp,"SERVER: ".print_r($_SERVER,1).chr(13).chr(10));
if(isset($_SERVER['CONTENT_TYPE']) and $_SERVER['CONTENT_TYPE']=='application/json') {
//fwrite($fp,"PHP:INPUT : ".file_get_contents('php://input').chr(13).chr(10));
	$data=json_decode(file_get_contents('php://input'));
	fwrite($fp,"PHP:INPUT : ".print_r($data,1).chr(13).chr(10));
}
fclose($fp);

if(!isset($token) or $token!='5ef331a4-a95d-425d-be60-ae3fe945700a') exit();
*/

require_once "sc-crm/btx_funct_oauth.php";
require_once "show_array.php";
//require_once "oktell_conn_string.php";
require_once "phone_conv_single.php";
require_once "send_email.php";

//ИД приложения в базе
$app_id='local.5fa95159e99329.13335518';

/*
$lead_id = 29575;//56;
if(isset($lead_id)) {
echo "<br>ИНФОРМАЦИЯ О ЛИДЕ:<br>";
$method='crm.lead.get';
$get_values='';
$post_values['id']=$lead_id;
//$post_values['SELECT']=array('UF_*');

$res=btx_request($app_id,$method,$get_values,$post_values);
if($res['text']<>'OK') {echo $res['code']." - ".$res['text']; exit();}
if($res['code']=='204') {echo $res['code']." - No content"; exit();}
show_array($res);
}
*/

/*
echo "<br>ПОЛЯ ЛИДОВ:<br>";
$method='crm.lead.fields';
$get_values='';
$post_values='';

$res=btx_request($app_id,$method,$get_values,$post_values);
if($res['text']<>'OK') {echo $res['code']." - ".$res['text']; exit();}
if($res['code']=='204') {echo $res['code']." - No content"; exit();}
show_array($res);

*/
echo "<br>СПИСОК ЗВОНКОВ:<br>";
$method='voximplant.statistic.get';
$get_values='';
$post_values['SORT']='ID';
$post_values['ORDER']='DESC';
$post_values['FILTER']=array('>CALL_START_DATE' => date('Y').'-'.date('m').'-'.date('d'));

$res=btx_request($app_id,$method,$get_values,$post_values);
if($res['text']<>'OK') {echo $res['code']." - ".$res['text']; exit();}
if($res['code']=='204') {echo $res['code']." - No content"; exit();}
show_array($res);

?>
</BODY>
</HTML>

