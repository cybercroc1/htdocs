<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
����� ����� ����������� ������� ���������� ������� � �������� �� ��������� ��������
<?php
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y' or !isset($_SESSION['survey']['base_id']) or $_SESSION['survey']['base_id']=='') exit();

$project_id=$_SESSION['survey']['project']['id'];
$user_id=$_SESSION['user']['id'];
$base_id=$_SESSION['survey']['base_id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

//=======================================================

?>

