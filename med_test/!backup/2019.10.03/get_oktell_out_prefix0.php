<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);

include("med/conn_string.cfg.php");

if(!isset($_SESSION['login_id_med'])
	or(!isset($oktell_server_address))
	or(!isset($source_auto_id))
	) { 
		echo "Неверный запрос префикса";
		exit();
	}

//опетаоры Грачевой по вторичному обзвону	
require_once 'base.php';
if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
	echo 'prefix=9900030;';
	exit();
}	
	
	
$q = OCIParse($c,"select nvl((select oktell_phone_prefix from SOURCE_AUTO where id='".$source_auto_id."'),oss.oktell_phone_prefix) prefix
from OKTELL_SERVER_SETTING oss, OKTELL_SERVER_ADDR osa
where oss.server_id=osa.server_id
and osa.server_address='".$oktell_server_address."'");
OCIExecute($q);
if(OCIFetch($q)) {
	echo 'prefix='.trim(OCIResult($q,"PREFIX")).';';
}
else {
		echo "Префикс не найден";
		exit();	
}

?>

