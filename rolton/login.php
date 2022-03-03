<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Выгрузка базы для проведения платежей</title>
</head>
<body>
<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_start();

extract($_REQUEST);
if (isset($exit)) {
session_destroy();
echo "<script>location.reload('login.php');</script>";
}

if (!$c=OCILogon('stom','zubodir','bill')) exit();

//Логин
	if(isset($User) and isset($Pass)) {
		if ($User=='rolton' and $Pass=='Y896upRm') {
			$_SESSION['auth']='y';
			echo "<script>location.reload('download.php');</script>";
		}
		else {
			session_destroy();
			echo "<font color=red><b>Не верное имя или пароль!</b></font>";
		}
		
	}
	//форма логина
	echo "<form method=\"POST\">
  <div align=\"center\"><center><table border=\"0\" width=\"778\" height=\"29\" 
  cellspacing=\"1\" cellpadding=\"0\">
</table>
  </center></div><div align=\"center\"><center><table border=\"0\" width=\"778\" 
  cellspacing=\"0\" cellpadding=\"0\" height=\"137\">
   <tr>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пользователь</strong></font></td>
      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"text\" name=\"User\" size=\"20\"></td>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"></td>
    </tr>
    <tr>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пароль</strong></font></td>
      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"password\" name=\"Pass\" size=\"20\"></td>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"center\"></div></td>
    </tr>
    <tr align=\"center\">
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" align=\"center\" height=\"65\"><input type=\"submit\" value=\"Вход\"></td>
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" height=\"65\">&nbsp;<p></td>
    </tr>
  </table>";
	exit();  
//
?>