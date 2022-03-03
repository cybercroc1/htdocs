<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0">
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

echo "<font size=4>Администрирование. </font>
<a href=adm_prj_frame.php target=adm_fr2> Проекты</a> | 
<a href=adm_num.php target=adm_fr2> Номера</a> | 
<a href=adm_usr_frame.php target=adm_fr2> Пользователи</a> | 
<a href=adm_holidays.php target=adm_fr2> Специальные дни</a>";

?>
