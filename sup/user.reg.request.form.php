<?php
session_name('sup_reg');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>������������</title>
</head>
<body leftmargin="3" topmargin="3">


<?php
if(isset($_SESSION['end'])) {
	echo "<form method=post name=frm action='/'>";
	echo "<table>";
	if($_SESSION['end']=='ok') {
		echo "<tr><td align=center><font size=3><b>������ �� ����������� � ������������ ����������.</b></font></td></tr>";
	}
	if($_SESSION['end']=='wait') {
		echo "<tr><td align=center><font size=3><b>�� ��� ���������� ������ �� ����������� � ������������.</b></font></td></tr>";
	}
	echo "<tr><td align=center><font size=3><b>����������, ��������� ����������� �� ����������� ����� ��� ��� � ��������� ������� ������.</b></font><hr></td></tr>";
	echo "<tr><td align=center><input type=submit value='OK'></input></td></tr>";
	session_destroy();
	exit();
}
else {
	echo "<form method=post name=frm action='user.reg.request.save.php' target='log_iframe'>
	<table>
	<tr><td colspan=2 align=center><font size=3><b>������ �� ����������� � ������������</b></font><hr></td></tr>";
} 

if(!isset($_SESSION['code_sended'])) {

echo "<tr>
<td valign=top align=center colspan=2>
<b>������� ��������� ������� � ����� �������</b><br> 
<nobr><input type=text name=mob_phone></nobr>
<div id=div_mob_phone></div>

<i>���� ����� ����� �������������� � �������� ������, � ��� �� �� ���� �� ������� �������� ���-����������� � ������� ����� ������.</i><br>
<input type=checkbox name=mob_cont value='y' checked>������������ ���� ����� ��� ���������� ��� �������� �����<br>
<input type=checkbox name=sms value='y' checked>�������� ���-�����������
<hr>
<input type=submit name=send_code value='��������� ����������� ���'>
</td>
</tr>";

}
else {
unset($_SESSION['code_sended']);

echo "<tr>
<td valign=top align=right>
<b>��� �� ���</b>: 
</td>
<td>
<input type=text name=sms_code><br>
<div id=div_sms_code></div>
<i>���� ���� ��� � �����, �� ������ ��������� ��������� ����</i><br>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>�����:</b> 
</td>
<td>
<b>".$_SESSION['mob_phone']."</b>
<div id=div_login></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>����� ������:</b> 
</td>
<td>
<input type=password name=pwd>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>������ ��� ���:</b> 
</td>
<td>
<input type=password name=pwd2>
<div id=div_pwd2></div>
<i>������, �� ����� 6 ��������</i>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>�������: </b>
</td>
<td>
<input type=text name=f size='40'>
<div id=div_f></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>���: </b>
</td>
<td>
<input type=text name=i size='40'>
<div id=div_i></div>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>�������������� (��� �� ���������):</b> 
</td>
<td>
<input type=text name=location size='40'>
<i>������������ �������� ������, ������. \"��-1905\" ��� \"�������, �������������\"</i><br>
<div id=div_location></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>�����:</b> 
</td>
<td>
<input type=text name=otdel size='40'>
<div id=div_otdel></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>���������: </b>
</td>
<td>
<input type=text name=doljnost size='40'>
<div id=div_doljnost></div>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>������� ������� � ����������:</b> 
</td>
<td>
<input type=text name=rab_phone size='40'>
<div id=div_rab_phone></div>
<i>� �����, �������� �������, ������. 8(495)123-45-67 ���.1000</i>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>email:</b> 
</td>
<td>
<input type=text name=email size='40'>
<div id=div_email></div>
<hr>
<input type=submit name=send_ank value='���������'>
</td>
</tr>";
}
?>
</table>

</form>
<iframe name=log_iframe style="display:none"></iframe>
