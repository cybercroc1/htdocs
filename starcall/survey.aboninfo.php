<?php include("starcall/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php

if($_SESSION['user']['operator']<>'y' or $_SESSION['survey']['project']['id']=='' or $_SESSION['survey']['ank']['base']['id']=='') exit();

$project_id=$_SESSION['survey']['project']['id'];
$user_id=$_SESSION['user']['id'];
$base_id=$_SESSION['survey']['ank']['base']['id'];

include("starcall/conn_string.cfg.php");

$q=OCIParse($c,"select b.id, s.name, b.status_type, b.STATUS_DATE, b.PEREZ_DATE_MSK, to_char(b.NEDOZ_DATE,'HH24:MI:SS') NEDOZ_DATE , b.nedoz_count  from STC_BASE b, STC_LI_ANK_STATUS s
where b.project_id=".$project_id." and b.id=".$base_id." and s.id(+)=b.status");
OCIExecute($q);
OCIFetch($q);
echo "ИД:".OCIResult($q,"ID")." Статус: ".OCIResult($q,"NAME")."-".OCIResult($q,"STATUS_TYPE")." дата недозвона:".OCIResult($q,"NEDOZ_DATE")." ко-во недозвонов:".OCIResult($q,"NEDOZ_COUNT");
echo "<br>";

$q=OCIParse($c,"select p.phone,p.status, to_char(p.status_date,'HH24:MI:SS') status_date, p.nedoz_count from STC_PHONES p
where project_id=".$project_id." and base_id=".$base_id."
order by ord");
OCIExecute($q);
while (OCIFetch($q)) {
	echo "Тел.: ".OCIResult($q,"PHONE")." Статус: ".OCIResult($q,"STATUS")." дата статуса/недоз:".OCIResult($q,"STATUS_DATE")." ко-во недозвонов:".OCIResult($q,"NEDOZ_COUNT")." <br>";

}
echo "<hr>";
//=======================================================

?>

