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
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");
if(!isset($new_login)) $login='';
if(!isset($new_pwd)) $new_pwd='';
if(!isset($new_pwd2)) $new_pwd2='';

echo "<form method=post>";
echo "<table width=100%><tr><td align=left><a href=tex.php>Вернуться к заявкам</a></td><td align=right>";
echo ""; 
echo "<a href=tex.php?exit><font color=red>выход</font></a></td></tr></table>";


echo "<font size=4>Смена пароля</font><br>";
if(isset($ok)) {
if($new_login=='') echo "<font size=3 color=red>Логин не может быть пустым!</font><br>";
else {
	$q=OCIParse($c,"select count(*) cnt from sup_user where login='".$new_login."' and id<>'".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(OCIResult($q,"CNT")>0) echo "<font size=3 color=red>Такой логин уже есть!</font><br>";
	else if($new_pwd=='') echo "<font size=3 color=red>Пароль не может быть пустым!</font><br>";
	else if($new_pwd<>$new_pwd2) echo "<font size=3 color=red>Введенные пароли не совпадают!</font><br>";
	else {
		$upd=OCIParse($c,"update sup_user set login=:login, password=:password where id='".$_SESSION['user_id']."'");
		OCIBindByName($upd,":login",$new_login);
		OCIBindByName($upd,":password",$new_pwd);
		if(OCIExecute($upd,OCI_DEFAULT)) {
			OCICommit($c);
			echo "<font size=4 color=green>Пароль изменен!</font><br>";
			$changed='';
		}
		
	}
}
if(isset($changed)) exit();
}

$q=OCIParse($c,"select login from sup_user where id='".$_SESSION['user_id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$login=OCIResult($q,"LOGIN");

echo "<table>";
echo "<tr>";
echo "<td>";
echo "логин: ";
echo "</td>";
echo "<td>";
echo "<input type=text name=new_login value='".$login."'>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td>";
echo "новый пароль: ";
echo "</td>";
echo "<td>";
echo "<input type=password name=new_pwd value=''>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td>";
echo "еще раз: ";
echo "</td>";
echo "<td>";
echo "<input type=password name=new_pwd2 value=''>";
echo "</td>";
echo "</tr>"; 
echo "</table>";

echo "<input type=submit name=ok value='Сохранить'>";
echo "</form>";
?>