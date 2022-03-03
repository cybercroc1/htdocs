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
include("sup/sup_conn_string");
if(!isset($new_login)) $login=''; else $new_login=trim($new_login);
if(!isset($new_pwd)) $new_pwd=''; else $new_pwd=trim($new_pwd);
if(!isset($new_pwd2)) $new_pwd2=''; else $new_pwd2=trim($new_pwd2);

echo "<form method=post>";
echo "<table width=100%><tr><td align=left><font size=4>Смена логина/пароля</font></td>";
echo "<td align=right>"; 
echo "<a href=/>Вернуться к заявкам</a> | <a href=/?exit><font color=red>выход</font></a></td></tr></table>";

if(isset($ok)) {
if(trim($new_login)=='') echo "<font size=3 color=red>Логин не может быть пустым!</font><br>";
else {
	$q=OCIParse($c,"select login from sup_user where id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_login=OCIResult($q,"LOGIN");

	$q=OCIParse($c,"select count(*) cnt from sup_user where login='".$new_login."' and deleted is null and id<>'".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(OCIResult($q,"CNT")>0) echo "<font size=3 color=red>ОШИБКА: Такой логин уже есть! Логин и пароль не изменены.</font><br>";
	else if(trim($new_login)<>$old_login and !preg_match('/^[a-zA-Zа-яА-Я].*$/',$new_login)) echo "<font size=3 color=red>ОШИБКА: Логин должен начинаться с буквы.</font><br>";
	else if($new_pwd=='') echo "<font size=3 color=red>Пароль не может быть пустым! Логин и пароль не изменены.</font><br>";
	else if($new_pwd<>$new_pwd2) echo "<font size=3 color=red>Введенные пароли не совпадают! Логин и пароль не изменены.</font><br>";
	else if(strlen($new_pwd)<6) echo "<font color=red>ОШИБКА: Пароль должен быть не менее 6 символов! Логин и пароль не изменены.</font><br>";
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
echo "<input type=password name=new_pwd2 value=''><br>";
echo "<i>пароль, не менее 6 символов</i>";
echo "</td>";
echo "</tr>"; 
echo "</table>";

echo "<input type=submit name=ok value='Сохранить'>";
echo "</form>";
?>