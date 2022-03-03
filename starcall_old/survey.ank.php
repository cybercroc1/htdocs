<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
Здесь будет анкета
<?php
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y' or !isset($page_num)) exit();

$project_id=$_SESSION['survey']['project']['id'];
$user_id=$_SESSION['user']['id'];
$base_id=$_SESSION['survey']['base_id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

//=======================================================

//Страница
$q=OCIParse($c,"select id,message from stc_object_page where project_id='".$project_id."' and num='".$page_num."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$page_id=OCIResult($q,"ID");
echo "<div align=center>Страница $page_num</div>";
//Группы
$q=OCIParse($c,"select id,message,t.quest_ord_type from STC_OBJECT_GROUP t where t.project_id='".$project_id."' and t.page_id='".$page_id."' order by t.num_on_page");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$group_id=OCIResult($q,"ID");
	$group_message=OCIResult($q,"MESSAGE");
	$quest_ord_type=OCIResult($q,"QUEST_ORD_TYPE");
	
	echo "<font size=5>$group_message</font><br>";

	echo "<hr>";
}

?>

