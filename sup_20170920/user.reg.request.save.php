<?php
session_name('sup_reg');
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
include("sup/sup_conn_string");

if(!isset($mob_cont)) $mob_cont='';
if(!isset($sms)) $sms='';

$n_send=3; //кол-во попыток отправки кода
$n_timeout=15; //тыймаут повторной отпраки (минут)
$n_enter=5; //кол-во попыток ввода кода

if(isset($send_code)) {
	if(!isset($err)) $err=0;

	$err_tmp='';
	$mob_phone=preg_replace('/[\D]/','',$mob_phone);
	if(preg_match('/^[78]?9[\d]{9}$/',$mob_phone)) {
		if(strlen($mob_phone)==11) $mob_phone=substr($mob_phone,1);
	}
	else{$err_tmp="ОШИБКА: Не верный телефон!"; $err++;}
	echo "<script>parent.document.getElementById('div_mob_phone').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

}
if(isset($send_code) and $err==0) {
	include("sup/send_sms.php");
	echo "<script>parent.frm.send_code.disabled=true;</script>";
	
	//проверка существования введенного номера телефона и исчерпанных попыток отправки кода
	$q=OCIParse($c,"select 
p.phone,p.sms,p.contact,p.type,p.valid_code,ceil((sysdate-p.valid_last_send)*24*60) valid_code_age_min,p.valid_send_tryes,p.valid_enter_tryes,
u.login,u.id user_id, case when nvl(u.create_date,sysdate)=nvl(u.deleted,sysdate) then 'n' else 'y' end active
from SUP_TEXNARI_PHONES p, SUP_USER u
where p.type='mob' and p.phone='".$mob_phone."'
and u.id=p.texnari_id 
and (u.deleted is null or u.create_date=u.deleted)
order by u.create_date desc");
	OCIExecute($q,OCI_DEFAULT);

	
	
	if(OCIFetch($q)) {
		$code=OCIResult($q,"VALID_CODE");
		$user_id=OCIResult($q,"USER_ID");
		//если есть такой пользователь и он активирован, то предлагаем отправить логин-пароль на мобильный телефон и переадресовываем на страницу восстановления пароля
		if(OCIResult($q,"ACTIVE")=='y') {
			session_destroy();
			$err_tmp="Пользователь с таким телефоном уже существует!";
			$err++;
			echo "<script>alert('".$err_tmp."');</script>";
			echo "<script>parent.document.location='/';</script>";
			exit();
		}
		elseif(OCIResult($q,"ACTIVE")=='n' and OCIResult($q,"LOGIN")<>'') {
			//$err_tmp="Вы уже отправлили заявку на регистрацию, пожалуйста, ожидайте уведомления.";
			$err++;
			//session_destroy();
			/*echo "<script>alert('".$err_tmp."');</script>";*/
			/*echo "<script>parent.document.getElementById('div_mob_phone').innerHTML='<font color=red>".$err_tmp."</font>';</script>"; */
			/*echo "<script>parent.document.location='/';</script>";*/
			$_SESSION['end']='wait';
			echo "<script>
			parent.location.reload();
			</script>";
			exit();			
		}
		//исчерпано количество попыток ввода кода
		elseif(OCIResult($q,"VALID_ENTER_TRYES")>=$n_enter) {
			session_destroy();
			$err_tmp="Исчерпано количество попыток ввода СМС-кода!";
			$err++;
			echo "<script>alert('".$err_tmp."');</script>";
			/* echo "<script>parent.document.getElementById('div_mob_phone').innerHTML='<font color=red>".$err_tmp."</font>';</script>"; */
			echo "<script>parent.document.location='/';</script>";
			exit();				
		}
		//исчерпано количество попыток отправки кода
		elseif(OCIResult($q,"VALID_SEND_TRYES")>=$n_send) {
			$_SESSION['code_sended']='y';
			$err_tmp="Исчерпано количество попыток отправки СМС-кода!";
			$err++;
			echo "<script>alert('".$err_tmp."');</script>";
			/* echo "<script>parent.document.getElementById('div_mob_phone').innerHTML='<font color=red>".$err_tmp."</font>';</script>"; */
			echo "<script>parent.document.location='/';</script>";
			exit();				
		}
		//таймаут повторной отправки СМС
		if(OCIResult($q,"VALID_CODE_AGE_MIN")<>'' and OCIResult($q,"VALID_CODE_AGE_MIN")<$n_timeout) {
			$timeout = $n_timeout-OCIResult($q,"VALID_CODE_AGE_MIN");
		}	
	}

	if($err==0) {	
		//генерируем код, если его нет
		if(!isset($code) or $code=='') {
			$code=rand(1000,9999);
		}
		
		//если пользователь существует, обновляем код
		if(isset($user_id)) {
			$upd=OCIParse($c,"update sup_texnari_phones set sms='".$sms."',contact='".$mob_cont."', valid_code='".$code."' where texnari_id=".$user_id." and phone='".$mob_phone."'");
			OCIExecute($upd,OCI_DEFAULT);
			OCICommit($c);
		} 
		else {
			$user_id='';
			//если пользователя не существует - добавление ползователя, номера и проверочного кода
			$ins=OCIParse($c,"insert into SUP_USER (ID,CREATE_DATE,DELETED) 
			values (sup_user_id.nextval,sysdate,sysdate)
			returning id into :user_id");
			OCIBindByName($ins,":user_id",$user_id,16);
			OCIExecute($ins,OCI_DEFAULT);
			$ins2=OCIParse($c,"insert into SUP_TEXNARI_PHONES (TEXNARI_ID,PHONE,SMS,CONTACT,ORD,TYPE,VALID_CODE)
			values (".$user_id.",'".$mob_phone."','".$sms."','".$mob_cont."',2,'mob','".$code."')");
			OCIExecute($ins2,OCI_DEFAULT);	
			OCICommit($c);	
			
		}

		//отправка проверочного кода
		//если не вышел таймаут повторной отправки
		if(isset($timeout)) {
			echo "<script>alert('СМС с кодом уже отправлено. Дождитесь СМС или попробуйте снова через ".$timeout." минут.');</script>";
		}
		else {
		//иначе шлем СМС
			$res=send_sms('',$mob_phone,$code,'send_code');
			if($res<>'OK') {
				echo "<script>parent.document.getElementById('div_mob_phone').innerHTML='<font color=red>ОШИБКА: Не удалось отправить СМС</font>';</script>";
				echo "<script>parent.frm.send_code.disabled=false;</script>";
			}
			else {
				//обновляем количество попыток и дату последней попытки
				$upd=OCIParse($c,"update sup_texnari_phones set valid_code='".$code."', valid_last_send=sysdate, valid_send_tryes=nvl(valid_send_tryes,0)+1 where texnari_id=".$user_id." and phone='".$mob_phone."'");
				OCIExecute($upd,OCI_DEFAULT);
				OCICommit($c);
				echo "<script>alert('Вам отправлено СМС с кодом подтверждения');</script>";
			}
		}
		//переход к заполнению регистрационной анкеты
		$_SESSION['user_id']=$user_id;
		$_SESSION['mob_phone']=$mob_phone;
		$_SESSION['code_sended']='y';
		
		echo "<script>
		parent.location.reload();</script>";
			
	}
}

if(isset($send_ank) and isset($_SESSION['user_id']) and isset($_SESSION['mob_phone'])) {
	$err=0;
	$login=$_SESSION['mob_phone'];
	$user_id=$_SESSION['user_id'];
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
	
	$err_tmp='';
	//if($login=='') {$err_tmp="ОШИБКА: Логин не может быть пустым!"; $err++;}
	//else {
		
//}
	echo "<script>parent.document.getElementById('div_sms_code').innerHTML='<font color=red>".$err_tmp."</font>';</script>";	

	$err_tmp='';
	if($login=='') {$err_tmp="ОШИБКА: Логин не может быть пустым!"; $err++;}
	/*else {
		$q=OCIParse($c,"select count(*) cnt from sup_user where login='".$login."' or fio='".$f." ".$i."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"CNT")>0) {$err_tmp="ОШИБКА: Такой логин уже существует!"; $err++;}
	}*/
	echo "<script>parent.document.getElementById('div_login').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if($pwd=='') {$err_tmp="ОШИБКА: Пароль не может быть пустым!"; $err++;}
	elseif(strlen($pwd)<6) {$err_tmp="ОШИБКА: Пароль должен быть не менее 6 символов!"; $err++;}
	elseif($pwd<>$pwd2) {$err_tmp="ОШИБКА: Введенные пароли не совпадают!"; $err++;}
	echo "<script>parent.document.getElementById('div_pwd2').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if(strlen(trim($f))<2) {$err_tmp="ОШИБКА: Не заполнено поле \"Фамилия\"!"; $err++;}
	echo "<script>parent.document.getElementById('div_f').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if(strlen(trim($i))<2) {$err_tmp="ОШИБКА: Не заполнено поле \"Имя\"!"; $err++;}
	echo "<script>parent.document.getElementById('div_i').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if(strlen(trim($location))<2) {$err_tmp="ОШИБКА: Укажите место работы."; $err++;}
	echo "<script>parent.document.getElementById('div_location').innerHTML='<font color=red>".$err_tmp."</font>';</script>";
	
	$err_tmp='';
	if(trim($otdel)=='') {$err_tmp="ОШИБКА: Укажите отдел."; $err++;}
	echo "<script>parent.document.getElementById('div_otdel').innerHTML='<font color=red>".$err_tmp."</font>';</script>";
	
	$err_tmp='';
	if(strlen(trim($doljnost))<2) {$err_tmp="ОШИБКА: Укажите Вашу должность."; $err++;}
	echo "<script>parent.document.getElementById('div_doljnost').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if(strlen(trim($rab_phone))<7) {$err_tmp="ОШИБКА: Уажите рабочий телефон."; $err++;}
	echo "<script>parent.document.getElementById('div_rab_phone').innerHTML='<font color=red>".$err_tmp."</font>';</script>";

	$err_tmp='';
	if(strlen(trim($email))=='')  {$err_tmp="ОШИБКА: Укажите адрес электронной почты!"; $err++;}
	else if(!preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", trim($email))) 
	{$err_tmp="ОШИБКА: Не верный адрес электронной почты!"; $err++;}
	echo "<script>parent.document.getElementById('div_email').innerHTML='<font color=red>".$err_tmp."</font>';</script>";


	if($err==0) {
		//не верный СМС-код
		$q=OCIParse($c,"select valid_code,nvl(valid_enter_tryes,0) valid_enter_tryes from SUP_TEXNARI_PHONES t
		where texnari_id='".$_SESSION['user_id']."' and phone='".$_SESSION['mob_phone']."'");	
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$enter_tryes=OCIResult($q,"VALID_ENTER_TRYES");
		if(OCIResult($q,"VALID_CODE")<>trim($sms_code)) {
			if($enter_tryes>=$n_enter) {
				session_destroy();
				$err_tmp="Исчерано количество попыток ввода СМС-кода"; $err++;
				echo "<script>alert('".$err_tmp."');</script>";
				/*echo "<script>parent.document.getElementById('div_sms_code').innerHTML='<font color=red>".$err_tmp."</font>';</script>"; */
				echo "<script>parent.document.location='/';</script>";
				exit();
			}
			$err_tmp="ОШИБКА: Не верный СМС-код! Осталось ".($n_enter-$enter_tryes)." попыток"; $err++;
			$upd=OCIParse($c,"update SUP_TEXNARI_PHONES t
			set valid_enter_tryes=nvl(valid_enter_tryes,0)+1
			where texnari_id='".$_SESSION['user_id']."' and phone='".$_SESSION['mob_phone']."'");
			OCIExecute($upd,OCI_DEFAULT);
			OCICommit($c);
			echo "<script>parent.document.getElementById('div_sms_code').innerHTML='<font color=red>".$err_tmp."</font>';</script>";
			echo "<script>alert('".$err_tmp."');</script>";
		}

	}
}
if(isset($send_ank) and isset($_SESSION['user_id']) and isset($_SESSION['mob_phone']) and $err==0) {

	include("func_send.php");
	include("sup/send_sms.php"); 

	$upd=OCIParse($c,"update sup_user u set
	u.login='".$login."', u.password='".$pwd."', u.fio='".$f." ".$i."', u.location='".$location."', u.otdel='".$otdel."', u.doljnost='".$doljnost."'
	where u.id='".$user_id."'");
	OCIExecute($upd,OCI_DEFAULT);

	$upd2=OCIParse($c,"update SUP_TEXNARI_PHONES t 
	set valid_date=sysdate
	where texnari_id='".$_SESSION['user_id']."' and phone='".$_SESSION['mob_phone']."'");
	OCIExecute($upd2,OCI_DEFAULT);
	
	$ins2=OCIParse($c,"insert into sup_texnari_emails (texnari_id,email,valid_date) values('".$user_id."','".$email."',sysdate)");
	OCIExecute($ins2,OCI_DEFAULT);

	$ins3=OCIParse($c,"insert into sup_texnari_phones (texnari_id,phone,contact,ord,type,valid_date) values('".$user_id."','".$rab_phone."','y','1','rab',sysdate)");
	OCIExecute($ins3,OCI_DEFAULT);

	OCICommit($c); 

	//отапрвка уведомления администратору по email
	$server='';
	$from_name='Техподдержка';
	$from_email='support@wilstream.ru';
	$reply_to_name=$i.' '.$f;
	$reply_to_email=$email;
	$subj='Регистрация пользователя';
	$mess="Пользователь <b>$f $i</b> запросил регистрацию в системе техподдержки<br>
	<a href='http://sup.wilstream.ru' target=_balnk>sup.wilstream.ru</a>";

	$q=OCIParse($c,"select distinct ste.email,su.fio from sup_user su, sup_texnari_emails ste
	where su.registrar='y'  
	and su.deleted is null
	and ste.texnari_id=su.id");
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
	and stp.valid_date is not null
	and stp.texnari_id=su.id
");
OCIExecute($q,OCI_DEFAULT);
$Phone_list='';
while (OCIFetch($q)) {
	$Phone_list.=OCIResult($q,"PHONE").";";
}	
if($Phone_list<>'') {
	$num_zayavki='';
	$sms_type='reg_query';
	$sms_text="Польз. $i $f зпросил регистарцию в ТП. 
	http://sup.wilstream.ru";
	echo "Отправка СМС: ".send_sms($num_zayavki,$Phone_list,$sms_text,$sms_type);
}
// 
//session_destroy();
$_SESSION['end']='ok';
echo "<script>
//alert('Заявка на регистрацию отправлена. Дождитесь ответа по электронной почте или СМС.');
//parent.location='/';
parent.location.reload();
</script>";
}
?>