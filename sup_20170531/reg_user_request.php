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
extract($_POST);
include("../../sup_conf/sup_conn_string");
if(!isset($login)) $login=''; else $login=trim($login);
if(!isset($pwd)) $pwd=''; else $pwd=trim($pwd);
if(!isset($pwd2)) $pwd2=''; else $pwd2=trim($pwd2);
if(!isset($f)) $f=''; else $f=trim($f);
if(!isset($i)) $i=''; else $i=trim($i);
if(!isset($location)) $location=''; else $location=trim($location);
if(!isset($otdel)) $otdel=''; else $otdel=trim($otdel);
if(!isset($doljnost)) $doljnost=''; else $doljnost=trim($doljnost);
if(!isset($rab_phone)) $rab_phone=''; else $rab_phone=trim($rab_phone);
if(!isset($mob_phone)) $mob_phone=''; else $mob_phone=trim($mob_phone);
if(!isset($email)) $email=''; else $email=trim($email);
if(!isset($mob_cont)) $mob_cont='';
if(!isset($sms)) $sms='';
if(!isset($err)) $err=0;

echo "<form method=post>";
echo "<table>";
echo "<tr><td colspan=2 align=center><font size=3><b>Заявка на регистрацию в техподдержке<b></font><hr></td></tr>";
echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>логин</b> (имя для входа): ";
echo "</td>";
echo "<td>";
echo "<input type=text name=login value='".$login."'><br>";
if(isset($ok)) {
if($login=='') {echo "<font color=red>ОШИБКА: Логин не может быть пустым!</font><br>"; $err++;}
else {
	$q=OCIParse($c,"select count(*) cnt from sup_user where login='".$login."' or fio='".$f." ".$i."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(OCIResult($q,"CNT")>0) {echo "<font color=red>ОШИБКА: Такой логин уже существует!</font><br>"; $err++;}
	}
}
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>новый пароль:</b> ";
echo "</td>";
echo "<td>";
echo "<input type=password name=pwd value='".$pwd."'>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>пароль еще раз:</b> ";
echo "</td>";
echo "<td>";
echo "<input type=password name=pwd2 value='".$pwd2."'><br>";
if(isset($ok)) {
if($pwd=='') {echo "<font color=red>ОШИБКА: Пароль не может быть пустым!</font><br>"; $err++;}
if($pwd<>$pwd2) {echo "<font color=red>ОШИБКА: Введенные пароли не совпадают!</font><br>"; $err++;}
if(strlen($pwd)<6) {echo "<font color=red>ОШИБКА: Пароль должен быть не менее 6 символов!</font><br>"; $err++;}
}
echo "<i>пароль, не менее 6 символов</i>";
echo "<hr>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Фамилия: </b>";
echo "</td>";
echo "<td>";
echo "<input type=text name=f value='".$f."' size='40'><br>";
if(isset($ok)) if(strlen(trim($f))<2) {echo "<font color=red>ОШИБКА: Не заполнено поле \"Фамилия\"!</font><br>"; $err++;}
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Имя: </b>";
echo "</td>";
echo "<td>";
echo "<input type=text name=i value='".$i."' size='40'><br>";
if(isset($ok)) if(strlen(trim($i))<2) {echo "<font color=red>ОШИБКА: Не заполнено поле \"Имя\"!</font><br>"; $err++;}
echo "<hr>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Местоположение (где Вы работаете):</b> ";
echo "</td>";
echo "<td>";
echo "<input type=text name=location value='".$location."' size='40'><br>";
if(isset($ok)) if(strlen(trim($location))<2) {echo "<font color=red>ОШИБКА: Укажите место работы.</font><br>"; $err++;}
echo "<i>общерпинятое название обекта, наприм. \"КЦ-1905\" или \"клиника, братиславская\"</i><br>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Отдел:</b> ";
echo "</td>";
echo "<td>";
echo "<input type=text name=otdel value='".$otdel."' size='40'><br>";
if(isset($ok)) if(trim($otdel)=='') {echo "<font color=red>ОШИБКА: Укажите отдел.</font><br>"; $err++;}
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Должность: </b>";
echo "</td>";
echo "<td>";
echo "<input type=text name=doljnost value='".$doljnost."' size='40'><br>";
if(isset($ok)) if(strlen(trim($doljnost))<2) {echo "<font color=red>ОШИБКА: Укажите Вашу должность.</font><br>"; $err++;}
echo "<hr>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Рабочий телефон с добавочным:<b> ";
echo "</td>";
echo "<td>";
echo "<input type=text name=rab_phone value='".$rab_phone."' size='40'><br><i>в любом, читаемом формате, наприм. 8(495)123-45-67 доб.1000</i><br>";
if(isset($ok)) if(strlen(trim($rab_phone))<7) {echo "<font color=red>ОШИБКА: Уажите рабочий телефон.</font><br>"; $err++;}
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>Мобильный телефон:</b> ";
echo "</td>";
echo "<td>";
echo "<nobr><b>+7</b><input type=text maxlength=10 name=mob_phone value='".$mob_phone."'></nobr><br>";
if(isset($ok)) {
	if($mob_phone<>'')  {
		if(!preg_match("/^[9]\d{9}$/", trim($mob_phone))) 
		{echo "<font color=red>ОШИБКА: Мобильный номер должен начинаться с \"9\" и состоять из 10 цифр!</font><br>"; $err++;}
	}
}
echo "<i>строго в формате 9ХХХХХХХХХ (10 цифр)</i><br>";
echo "<input type=checkbox name=mob_cont value='y'"; if($mob_cont=='y') echo " checked"; echo ">разрешить обратную связь на мобильный<br>";
echo "<input type=checkbox name=sms value='y'"; if($sms=='y') echo " checked"; echo ">разрешить автоматические СМС-уведомления<hr>";
echo "</td>";
echo "</tr>"; 

echo "<tr>";
echo "<td valign=top align=right>";
echo "<b>email:</b> ";
echo "</td>";
echo "<td>";
echo "<input type=text name=email value='".$email."' size='40'><br>";
if(isset($ok)) {
	if(strlen(trim($email))=='')  {echo "<font color=red>ОШИБКА: Укажите адрес электронной почты!</font><br>"; $err++;}
	else if(!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", trim($email))) 
	{echo "<font color=red>ОШИБКА: Не верный адрес электронной почты!</font><br>"; $err++;}
}
echo "<hr>";
echo "<input type=submit name=ok value='Отправить'>";
echo "</td>";
echo "</tr>"; 

echo "</table>";

echo "</form>";

if(isset($ok) and $err==0) {

include("func_send.php");
include("../../sup_conf/send_sms.php"); 

$new_iser_id='';

$ins=OCIParse($c,"insert into sup_user u 
(u.id,u.login,u.password,u.fio,u.location,u.otdel,u.doljnost,u.create_date,u.deleted)
values (sup_user_id.nextval,'".$login."','".$pwd."','".$f." ".$i."','".$location."','".$otdel."','".$doljnost."',sysdate,sysdate)
returning u.id into :user_id");
OCIBindByName($ins,":user_id",$new_iser_id,1024);
OCIExecute($ins,OCI_DEFAULT);

$ins2=OCIParse($c,"insert into sup_texnari_emails (texnari_id,email) values(:user_id,'".$email."')");
OCIBindByName($ins2,":user_id",$new_iser_id);
OCIExecute($ins2,OCI_DEFAULT);

$ins3=OCIParse($c,"insert into sup_texnari_phones (texnari_id,phone,contact,ord,type) values(:user_id,'".$rab_phone."','y','1','rab')");
OCIBindByName($ins3,":user_id",$new_iser_id);
OCIExecute($ins3,OCI_DEFAULT);

if($mob_phone<>'') {
	$ins4=OCIParse($c,"insert into sup_texnari_phones (texnari_id,phone,contact,sms,ord,type) 
										values(:user_id,'".$mob_phone."','".$mob_cont."','".$sms."','2','mob')");
	OCIBindByName($ins4,":user_id",$new_iser_id);
	OCIExecute($ins4,OCI_DEFAULT);
}
OCICommit($c); 

//отапрвка уведомления администратору по email
$server='';
$from_name='Техподдержка';
$from_email='support@wilstream.ru';
$reply_to_name=$i.' '.$f;
$reply_to_email=$email;
$subj='Регистрация пользователя';
$mess="Пользователь <b>$f $i</b> запросил регистрацию в системе техподдержки<br>
<a href='http://gw.wilstream.ru/sup/tex.php' target=_balnk>http://gw.wilstream.ru/sup/tex.php</a>";

$q=OCIParse($c,"select distinct ste.email,su.fio from sup_user su, sup_texnari_emails ste
where su.registrar='y'  
and su.deleted is null
and ste.texnari_id=su.id
");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$to_name=OCIResult($q,"FIO");
	$to_email=OCIResult($q,"EMAIL");
	send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess);
}
// 

//отапрвка уведомления администратору по SMS
$q=OCIParse($c,"select distinct stp.phone from sup_user su, sup_texnari_phones stp
	where su.registrar='y'  
	and su.deleted is null
	and stp.type='mob'
	and stp.texnari_id=su.id
");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$num_zayavki='';
	$from_phone='Support';
	$Phone_list=OCIResult($q,"PHONE");
	$sms_text="Польз. $i $f зпросил регистарцию в ТП. 
	http://gw.wilstream.ru/sup/tex.php";
	send_sms($num_zayavki,$from_phone,$Phone_list,$sms_text);
}
// 

echo "<script>alert('Заявка на регистрацию отправлена. Дождитесь ответа по эелектронной почте (в течение дня) или попробуйте войти позже.');document.location='http://gw.wilstream.ru/sup/tex.php';</script>";
}
?>