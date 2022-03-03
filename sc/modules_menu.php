<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
header('X-UA-Compatible: IE=EmulateIE7');
//$_SESSION['last_url']='modules_frame.php';
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
if (!isset($_SESSION['login_id'])) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

extract($_REQUEST);
if(!isset($module_id)) $module_id='';

include("sc/sc_conn_string.php");

$sql_text="select id, name from SC_MODULES m
where m.project_id='".$_SESSION['project']['id']."'";
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>1) $sql_text.=" and m.id in (select a.module_id from SC_MODULES_ACC a where a.user_id='".$_SESSION['login_id']."')";
$q=OCIParse($c,$sql_text);
OCIExecute($q,OCI_DEFAULT);
$i=0;
while(OCIFetch($q)) { $i++;
	$modules[OCIResult($q,"ID")]=OCIResult($q,"NAME");
}

if($i==0) exit();

if($i==1) {
	foreach($modules as $key=>$val) {
		$module_id=$key;
		echo "<font size=4>Модуль: <b>".$val."</b></font>";
	}
}
else {

	echo "<form method=post>";
	
	echo "<font size=4>Модуль: </font><select name=module_id onchange=ch_module_id()>";
	
	foreach($modules as $key=>$val) {
		echo "<option value='".$key."'".($module_id==$key?' selected':'').">".$val."</option>";	
	}
echo "</select>";
echo "<input type=submit name=ch_module value='Выбрать'>";
echo "</form>";
}

if($module_id<>'') {
	$q=OCIparse($c,"select url from SC_MODULES m where id='".$module_id."'");
	OCIExecute($q);
	OCIFetch($q);
	$url=OCIResult($q,"URL");
}
else {
	$url='blank.htm';
}
echo "<script>parent.module_fr2.location='".$url."';</script>";


?>
<script language='javascript'>
document.all.ch_module.style.display='none';
function ch_module_id() {
	document.all.ch_module.click();
}
</script>
