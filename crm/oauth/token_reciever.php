<?php
require_once "sc-crm/oauth_funct.php";

//$app_id='aa0fb7b3-61b4-493f-9fdb-4f7cb56f3156'; //amo
//$app_id='local.5fa95159e99329.13335518'; //bitrix
//первичная авторизация: https://sc.wilstream.ru/crm/oauth/token_reciever.php?first=local.5bd1bda476e763.59707593

//первичная авторизация
if(isset($_GET['first'])) {
	$res=first_auth($_GET['first']);
	if($res['result_text']=='OK') echo "Проект: ".$res['project_name']. " <a href='".$res['auth_url']."' target= _blank>Авторизоваться</a>";
	else echo $res['result_text'];
}
//первичное получение ключей
elseif(
	isset($_GET['state']) //в state передаем application_id 
) {
	$app_id=$_GET['state']; 
	
	if(isset($_GET['code'])) $code=$_GET['code']; else $code='';
	$res=refresh_token($app_id,$code);
	//print_r($res);
}
elseif(isset($_GET['refresh_all'])) {
	$res=refresh_all();
	if(count($res)==0) echo "Нет токенов для обновления";
	else var_dump($res);
}
else 
//обновление ключа по refresh_token
$res=refresh_token($app_id);
print_r($res);

?>
