<?php
session_name('sup_reg');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<body leftmargin="3" topmargin="3">


<?php
if(isset($_SESSION['end'])) {
	echo "<form method=post name=frm action='/'>";
	echo "<table>";
	if($_SESSION['end']=='ok') {
		echo "<tr><td align=center><font size=3><b>Заявка на регистрацию в техподдержке отправлена.</b></font></td></tr>";
	}
	if($_SESSION['end']=='wait') {
		echo "<tr><td align=center><font size=3><b>Вы уже отправляли заявку на регистрацию в техподдержке.</b></font></td></tr>";
	}
	echo "<tr><td align=center><font size=3><b>Пожалуйста, дождитесь уведомления по электронной почте или СМС о активации учетной записи.</b></font><hr></td></tr>";
	echo "<tr><td align=center><input type=submit value='OK'></input></td></tr>";
	session_destroy();
	exit();
}
else {
	echo "<form method=post name=frm action='user.reg.request.save.php' target='log_iframe'>
	<table>
	<tr><td colspan=2 align=center><font size=3><b>Заявка на регистрацию в техподдержке</b></font><hr></td></tr>";
} 

if(!isset($_SESSION['code_sended'])) {

echo "<tr>
<td valign=top align=center colspan=2>
<b>Введите мобильный телефон в любом формате</b><br> 
<nobr><input type=text name=mob_phone></nobr>
<div id=div_mob_phone></div>

<i>Этот номер будет использоваться в качестве логина, а так же на него Вы сможете получать СМС-уведомления о статусе Ваших заявок.</i><br>
<input type=checkbox name=mob_cont value='y' checked>использовать этот номер как контактный для обратной связи<br>
<input type=checkbox name=sms value='y' checked>получать СМС-уведомления
<hr>
<input type=submit name=send_code value='Отправить проверочный код'>
</td>
</tr>";

}
else {
unset($_SESSION['code_sended']);

echo "<tr>
<td valign=top align=right>
<b>код из СМС</b>: 
</td>
<td>
<input type=text name=sms_code><br>
<div id=div_sms_code></div>
<i>пока идет СМС с кодом, Вы можете заполнить остальные поля</i><br>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>логин:</b> 
</td>
<td>
<b>".$_SESSION['mob_phone']."</b>
<div id=div_login></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>новый пароль:</b> 
</td>
<td>
<input type=password name=pwd>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>пароль еще раз:</b> 
</td>
<td>
<input type=password name=pwd2>
<div id=div_pwd2></div>
<i>пароль, не менее 6 символов</i>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Фамилия: </b>
</td>
<td>
<input type=text name=f size='40'>
<div id=div_f></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Имя: </b>
</td>
<td>
<input type=text name=i size='40'>
<div id=div_i></div>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Местоположение (где Вы работаете):</b> 
</td>
<td>
<input type=text name=location size='40'>
<i>общерпинятое название обекта, наприм. \"КЦ-1905\" или \"клиника, братиславская\"</i><br>
<div id=div_location></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Отдел:</b> 
</td>
<td>
<input type=text name=otdel size='40'>
<div id=div_otdel></div>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Должность: </b>
</td>
<td>
<input type=text name=doljnost size='40'>
<div id=div_doljnost></div>
<hr>
</td>
</tr> 

<tr>
<td valign=top align=right>
<b>Рабочий телефон с добавочным:</b> 
</td>
<td>
<input type=text name=rab_phone size='40'>
<div id=div_rab_phone></div>
<i>в любом, читаемом формате, наприм. 8(495)123-45-67 доб.1000</i>
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
<input type=submit name=send_ank value='Отправить'>
</td>
</tr>";
}
?>
</table>

</form>
<iframe name=log_iframe style="display:none"></iframe>
