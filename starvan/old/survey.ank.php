<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body onresize=frame_resize()>

<script>
function frame_resize () {
	document.cookie="ank_frame_width="+(Math.round(document.body.offsetWidth/(document.body.offsetWidth+parent.ankInfoFrame.document.body.offsetWidth)*100))+"%,*;";
}
</script>
<?php
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y' or $_SESSION['survey']['project']['id']=='' or $_SESSION['survey']['ank']['base']['id']=='') exit();

$project_id=$_SESSION['survey']['project']['id'];
$user_id=$_SESSION['user']['id'];
$base_id=$_SESSION['survey']['ank']['base']['id'];
$page_num=1;

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

$project_id=$_SESSION['survey']['project']['id'];
$user_id=$_SESSION['user']['id'];
$base_id=$_SESSION['survey']['ank']['base']['id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

echo "select b.id, s.name from STC_BASE b, STC_LI_ANK_STATUS s
where b.project_id=".$project_id." and b.id=".$base_id." and s.id(+)=b.status";

$q=OCIParse($c,"select b.id, s.name from STC_BASE b, STC_LI_ANK_STATUS s
where b.project_id=".$project_id." and b.id=".$base_id." and s.id(+)=b.status");
OCIExecute($q);
OCIFetch($q);
echo "ИД:".OCIResult($q,"ID")." Статус: ".OCIResult($q,"NAME");
echo "<br>";

$q=OCIParse($c,"select p.phone,p.status from STC_PHONES p
where project_id=".$project_id." and base_id=".$base_id."
order by ord");
OCIExecute($q);
while (OCIFetch($q)) {
	echo "Тел.: ".OCIResult($q,"PHONE")." Статус: ".OCIResult($q,"STATUS")."<br>";

}
echo "<hr>";
if($_SESSION['survey']['ank']['base']['status']=='inwork') {
echo "<form name=frm_set_status method=post action='survey.ank.save_status.php' target=callLogFrame>";
echo "<input type=hidden name=set_ank_status></input>";
echo "<input type=button name=end_norm value='Успешное' onclick=frm_set_status.set_ank_status.value='end_norm';frm_set_status.submit()></input>";
echo "<input type=button name=end_false value='Нецелевой' onclick=frm_set_status.set_ank_status.value='end_false';frm_set_status.submit()></input>";
echo "<input type=button name=perez disabled value='Перезвонить' onclick=frm_set_status.set_ank_status.value='perez';frm_set_status.submit()></input>";
echo "<input type=button name=end_otkaz value='Отказ' onclick=frm_set_status.set_ank_status.value='end_otkaz';frm_set_status.submit()></input>";
echo "<input type=button name=end_error value='Ошибка' onclick=frm_set_status.set_ank_status.value='end_error';frm_set_status.submit()></input>";
echo "<input type=button name=end_quote value='Превышена квота' onclick=frm_set_status.set_ank_status.value='end_quote';frm_set_status.submit()></input>";
echo "</form>";
}
?>

