<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="oktadmin.css" rel="stylesheet" type="text/css">
<title>������� ������</title>
<style type="text/css">
.div_main{
	text-align:center;
	font-size: 18px;
	font-weight: 600;
    border: 2px solid #bbb;
	max-width: 320px;
	padding: 0px 10px 70px 10px;
	margin: 0 auto;
	border-radius: 10px 10px 150px 150px;.
	white-space: nowrap;
}
.div_left{
	text-align: left !important;
	padding: 3px 5px;
	margin: 0px 15px;
	border-radius: 15px;
	height: 25px;
	
}
.div_right{
	text-align: right !important;
	padding: 2px 0;
	margin: 0px 30px;
	border: 1px solid #fff;
	border-radius: 15px;
	height: 50px;
}
a{
	text-decoration: none !important;
	    white-space: nowrap;
}
</style>
</head>

<BODY>
<?php
$mylogin = "admin";
$mypass = "quistis";
if(isset($_POST['auth_linkss']))
{
if (($_POST['login'] == $mylogin) && ($_POST['password'] == $mypass))
{
?>
<div class="div_main">
<h1>������� ������</h1>
<div class="div_left"><a href="http://sc/local/oktadmin/frames.php">Oktell Admin Panel</a></div>
<div class="div_left"><a href="http://sup.wilstream.ru/">���� ������ Wilstream</a></div>
<div class="div_left"><a href="http://mantis.vse-svoi.net">���� ������ Vse-svoi(Mantis)</a></div>
<div class="div_left"><a href="http://sc.wilstream.ru/local/sc/frame.php">C�����(1905)</a></div>
<div class="div_left"><a href="http://sc.wilstream.ru/login">������ ���������(1905)</a></div>
<div class="div_left"><a href="http://sc-vg.wilstream.ru/login">������ ��������� (�����������)</a></div>
<div class="div_left"><a href="http://sc/local/oktadmin/ind.php">pls-pls-pls</a></div>
<div class="div_left"><a href="http://sc.wilstream.ru/local/sc/frame.php?project_id=2094">����� ����� ���.������</a></div>
</div>
<?php  
}
else
{
echo '�������� ������';
}
}
else
{
echo
'<form method="post">
�����: <input type="text" name="login" />
������: <input type="password" name="password" />
<input type="submit" value="�����" name="auth_linkss" />
</form>';
}
?>
</BODY>
</HTML>