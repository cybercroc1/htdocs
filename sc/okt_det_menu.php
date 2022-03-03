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
//if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

echo "<font size=4>Детализация звонков. </font>";
if (isset($_SESSION['project']['view_okt_in_det']) and $_SESSION['project']['view_okt_in_det']==1) echo "<a href=okt_det_in.php target=okt_det_fr2> Детализация входящих</a>";
if (isset($_SESSION['project']['view_okt_out_det']) and $_SESSION['project']['view_okt_out_det']==1) echo " | <a href=okt_det_out.php target=okt_det_fr2> Детализация исходящих</a>";
?>
