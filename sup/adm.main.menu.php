<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>������������ ���-����</title>
</head>
<form name=frm method=post>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
//if(!isset($_SESSION['admin']) or $_SESSION['admin']<>'y') {
//	echo "<font size=3 color=red>�� ���������� ����!</font>"; exit();
//}
include("sup/sup_conn_string");

if($_SESSION['admin']=='y') 
	echo "<table width=100%><tr><td align=left><font size=3>
<a href=adm_grp.php target=admBottomFrame>������</a> | ";
if($_SESSION['admin']=='y' or $_SESSION['registrar']=='y') 
	echo "<a href=adm.users.frame.php target=admBottomFrame>������������</a> | ";
if($_SESSION['admin']=='y') 
	echo "<a href=adm_trbl.php target=admBottomFrame>��������</a> | 
<a href=adm_locat.php target=admBottomFrame>�����</a>
</font></td><td align=right>";
echo "<a href=/ target=_parent>������</a> | "; 
echo "<a href=/?exit target=_parent><font color=red>�����</font></a></td></tr></table>";

?>