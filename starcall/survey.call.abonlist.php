<?php include("starcall/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();

include("starcall/conn_string.cfg.php");

echo "Abonlist";

//=======================================================

?>

