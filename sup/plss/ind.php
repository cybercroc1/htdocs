<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="oktadmin.css" rel="stylesheet" type="text/css">
<link href="style.css" rel="stylesheet" type="text/css">
<title>LOGIN INFO</title>
</head>
<BODY>
<?php
$mylogin = "admin";
$mypass = "quistis";

$vlogin = "val";
$vpass = "89262805354";

$slogin = "serge";
$spass = "3940";

if(isset($_POST['pls_auth']))
{
if (($_POST['login'] == $mylogin) && ($_POST['password'] == $mypass))
{
    echo "����������� ������ �������";
    session_start();
    $_SESSION['activ'] = 'admin';
	header ('Location: plc.php');  
	exit();   
}
elseif(($_POST['login'] == $vlogin) && ($_POST['password'] == $vpass)){
    echo "����������� ������ �������";
    session_start();
    $_SESSION['activ'] = 'val';
	header ('Location: plcv.php');  
	exit();   
}
elseif(($_POST['login'] == $slogin) && ($_POST['password'] == $spass)){
    echo "����������� ������ �������";
    session_start();
    $_SESSION['activ'] = 'serge';
	header ('Location: plcs.php');  
	exit();   
}
else
{
echo '�������� ������';
}
}
else
{
echo
'<form method="post" class="authform">
<input type="text" name="login" placeholder="�����" class="inpitarea"/>
<input type="password" name="password" placeholder="������" class="inpitarea"/>
<input type="submit" value="�����" name="pls_auth" class="submitbtn"/>
</div>
</form>';
}
?>
</BODY>
</HTML>