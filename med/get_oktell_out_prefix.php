<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);

include("med/conn_string.cfg.php");

if(!isset($_SESSION['login_id_med'])
	or(!isset($oktell_server_address))
	or(!isset($base_id))
	) { 
		echo "Неверный запрос префикса";
		exit();
	}

//опетаоры Грачевой по вторичному обзвону	
require_once 'base.php';
/*
if ($_SESSION['sec_chance']) {
	echo 'prefix=9900030;';
	exit();
}	
*/
//определение идентификатора сервера
$oktell_server_id='';
$q=OCIParse($c,"select server_id from OKTELL_SERVER_ADDR where server_address='".$oktell_server_address."'");
OCIExecute($q);
if(OCIFetch($q)) {
	$oktell_server_id=OCIResult($q,"SERVER_ID"); 
}

//источник, услуга, поставщик, город
$source_auto_id='';
$service_id='';
$supplier_id='';
$city_id='';
$q=OCIParse($c,"select b.source_auto_id,b.service_id,a.supplier_id,a.city_id from CALL_BASE b
left join source_auto a on a.id=b.source_auto_id
where b.id='".$base_id."'");
OCIExecute($q);
if(OCIFetch($q)) {
	$source_auto_id	=OCIResult($q,"SOURCE_AUTO_ID");
	$service_id		=OCIResult($q,"SERVICE_ID");
	$supplier_id	=OCIResult($q,"SUPPLIER_ID");
	$city_id		=OCIResult($q,"CITY_ID");
} 	

//департамент пользователя
$departaments_arr=array();
$departaments_str='';
$q=OCIParse($c,"select dep_id from USER_DEP_ALLOC
where deleted is null and user_id='".$_SESSION['login_id_med']."'");
OCIExecute($q);
while(OCIFetch($q)) {
	$departaments_arr[]=OCIResult($q,"DEP_ID");
} 	
$departaments_str="'".implode("','",$departaments_arr)."'";

//Ишем префикс:
//1. Если в таблице префиксов какой-то параметр не указан, это значит, что данный префикс подходит под все значения этого параметра.
//2. Выбираются только строки со всеми подходящими или пустыми параметрами
//3. Из полученного набора строк выбирается строка с непустым значением в порядке приоритета:
//Источник авто , если он не указан, то
//Департамент пользователя,
//Услуга
//Поставщик
//Город
//Сервер

$sql="select prefix from OUT_PREFIXES x
where (x.oktell_server_id is null or x.oktell_server_id='".$oktell_server_id."')
and   (x.source_auto_city_id is null or x.source_auto_city_id='".$city_id."')
and   (x.supplier_id is null or x.supplier_id='".$supplier_id."')
and   (x.service_id is null or x.service_id='".$service_id."')
and   (x.source_auto_id is null or x.source_auto_id='".$source_auto_id."')
and   (x.department_id is null or x.department_id in (".$departaments_str.")) 
order by
x.source_auto_id,
x.department_id,
x.service_id,
x.supplier_id,
x.source_auto_city_id, 
x.oktell_server_id";
$q = OCIParse($c,$sql);

OCIExecute($q);
if(OCIFetch($q)) {
	echo 'prefix='.trim(OCIResult($q,"PREFIX")).';';
	exit();	
}
else {
		//echo "Префикс не найден";
		//echo $sql;
		echo 'prefix=;';
		exit();	
}

?>

