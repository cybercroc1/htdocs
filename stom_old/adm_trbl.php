<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка Все-Свои</title>
</head>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

echo "<table width=100%><tr><td align=left><font size=3>
<a href=adm_usr.php>Пользователи</a> | 
<a href=adm_grp.php>Группы</a> | 
<b>Проблемы</b> | 
<a href=adm_locat.php>Места</a>
</font></td><td align=right>";
echo "<a href=tex.php>Заявки</a> | "; 
echo "<a href=tex.php?exit><font color=red>выход</font></a></td></tr></table><hr>";

?>