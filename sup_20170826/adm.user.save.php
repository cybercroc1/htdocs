<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

if(isset($user_id) and $user_id<>'') {

	//информация о пользователе
	$q_user=OCIParse($c,"select   
	to_char(u.create_date,'YYYYDDMMHH24MISS') create_date, to_char(u.deleted,'YYYYDDMMHH24MISS') deleted, u.login, u.password, u.fio
	from SUP_USER u where u.id=".$_SESSION['cur_edit_user']);		
	OCIExecute($q_user,OCI_DEFAULT);
	OCIFetch($q_user);	
	$login=OCIResult($q_user,"LOGIN");
	$pass=OCIResult($q_user,"PASSWORD");
	$fio=OCIResult($q_user,"FIO");
	$status='';
	if(OCIResult($q_user,"DELETED")==OCIResult($q_user,"CREATE_DATE") and OCIResult($q_user,"LOGIN")=='') {
		$status='sended_code'; $status_text='<font color=yellow>Отправлен код подтверждения</font>';
	}
	elseif (OCIResult($q_user,"DELETED")==OCIResult($q_user,"CREATE_DATE") and OCIResult($q_user,"LOGIN")<>'') {
		$status='wait_activation'; $status_text='<font color=blue>Ожидает активации</font>';
	}
	elseif (OCIResult($q_user,"DELETED")=='') {
		$status='active'; $status_text='<font color=green>Активен</font>';
	}
	elseif (OCIResult($q_user,"DELETED")<>'') {
		$status='deleted'; $status_text='<font color=red>Удалён</font>';
	}

	if((isset($save) or  isset($activate) or isset($send_reg_sms) or isset($send_reg_email)) and ($status=='active' or $status=='wait_activation' or $status=='deleted')) {
		//сохранение настроек пользователя
		if(!isset($look)) $look='';
		if(!isset($solution)) $solution='';
		if(!isset($redirect)) $redirect='';
		if(!isset($eval)) $eval='';
		if(!isset($admin)) $admin='';
		if(!isset($oper)) $oper='';
		if(!isset($deny_close)) $deny_close='';
		if(!isset($create_new)) $create_new='';
		if(!isset($sms_new)) $sms_new='';
		if(!isset($rep_stat)) $rep_stat='';
		if(!isset($registrar)) $registrar='';
		if(!isset($email_new)) $email_new='';
		if(!isset($email_coment)) $email_coment='';
		if(!isset($email_redir)) $email_redir='';
		if(!isset($email_prisv)) $email_prisv='';
		if(!isset($email_ready)) $email_ready='';
		if(!isset($email_close)) $email_close='';
		if(!isset($sms_redir)) $sms_redir='';
		if(!isset($sms_prisv)) $sms_prisv='';
		if(!isset($sms_ready)) $sms_ready='';
		if(!isset($sms_close)) $sms_close='';
	
		$q_upd1=OCIParse($c,"update sup_user u
		set
		admin='$admin',
		oper='$oper',
		registrar='$registrar',

		create_new='$create_new',
		solution='$solution',
		deny_close='$deny_close',
		redirect='$redirect',
		look='$look',
		eval='$eval',
		rep_stat='$rep_stat',
		send='$email_new',
		email_redir='$email_redir',
		email_prisv='$email_prisv',
		email_ready='$email_ready',
		email_close='$email_close',
		email_coment='$email_coment',
		sms_new='$sms_new',
		sms_redir='$sms_redir',
		sms_prisv='$sms_prisv',
		sms_ready='$sms_ready',
		sms_close ='$sms_close'
		
		where u.id=".$user_id);	
		OCIExecute($q_upd1,OCI_DEFAULT);
		
		//сохранение групп
		$del=OCIParse($c,"delete from SUP_USER_LT_ALLOC t where t.user_id=".$user_id);
		OCIExecute($del,OCI_DEFAULT);
		
		if(isset($groups)) {
			foreach($groups as $val) {
				$ins=OCIParse($c,"insert into SUP_USER_LT_ALLOC (USER_ID,LT_GROUP_ID,
				create_new, solution, deny_close, redirect, look, eval, rep_stat, 
				
				send, email_redir, email_prisv, email_ready, email_close, email_coment, sms_new, sms_redir, sms_prisv, sms_ready, sms_close)
				
				values (".$user_id.",".$val.",
				
				'".$create_new."','".$solution."','".$deny_close."','".$redirect."','".$look."','".$eval."','".$rep_stat."',
				'".$email_new."','".$email_redir."','".$email_prisv."','".$email_ready."','".$email_close."','".$email_coment."','".$sms_new."','".$sms_redir."','".$sms_prisv."','".$sms_ready."','".$sms_close."')");											
				OCIExecute($ins,OCI_DEFAULT);
			}
		}
		OCICommit($c);
	}
	
	if(isset($activate) and ($status=='wait_activation' or $status=='deleted')) {
		$q=OCIParse($c,"select * from SUP_USER u
		where u.login='".$login."' and u.id<>".$user_id." and u.deleted is null");
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) {
			echo "<script>alert('ОШИБКА! Уже существует активный пользователь с таким логином!');</script>"; exit();
		}
		$q_upd2=OCIParse($c,"update sup_user u
		set
		deleted=null,
		login=nvl(login,'".$login."')
		where u.id=".$user_id);	
		OCIExecute($q_upd2,OCI_DEFAULT);
		OCICommit($c);
	}
	
	if((isset($activate) and $status=='wait_activation') or isset($send_reg_sms)) {
		//SMS
		include("../../sup_conf/send_sms.php");
		$num_zayavki='';
		$sms_type='reg_data';
		if(isset($send_reg_sms)) {
			$sms_text="Вход в техподдержку:
			http://sup.wilstream.ru
			Логин: ".$login."
			Пароль: ".$pass;
		}
		else {
			$sms_text="Вы зарегистрированы в техподдержке.
			http://sup.wilstream.ru
			Логин: ".$login."
			Пароль: ".$pass;
		}
		$phones=array();
		$q=OCIParse($c,"select distinct phone from SUP_TEXNARI_PHONES t
		where t.texnari_id=".$user_id." and t.valid_date is not null and type='mob'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; while(OCIFetch($q)) {$i++;
			$phones[$i]=OCIResult($q,"PHONE");
		}
		if(count($phones)>0) {
			$Phone_list=implode(',',$phones);
			$sms_result=send_sms($num_zayavki,$Phone_list,$sms_text,$sms_type);
			echo "Отправка СМС: ".$sms_result."<hr>";
			if($sms_result=='OK') {
				echo "<script>alert('Пользователю отправлено СМС с рег. данными');</script>";
			}
			else {
				echo "<script>alert('Ошибка отправки СМС: ".$sms_result."');</script>";
			}
		}
	}		
	
	if((isset($activate) and $status=='wait_activation') or isset($send_reg_email)) {
		//EMAIL
		include("func_send.php");
		$server='';
		$from_email='support@wilstream.ru';		
		$q=OCIParse($c,"select email from sup_texnari_emails where texnari_id='".$_SESSION['user_id']."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
		if($i>0) $reply_to_email=implode(',',$eml);
		$reply_to_name=$_SESSION['fio'];
		$from_name=$_SESSION['fio'];		
		$to_name=$fio;
		if(isset($send_reg_email)) {
			$subj='Данные для входа в техподдержку';
			$mess="Данные для входа в техподдержку:<br>
			<a href='http://sup.wilstream.ru'>sup.wilstream.ru</a><br>
			Логин: ".$login."<br>
			Пароль: ".$pass;
		} else {
			$subj='Регистрация в техподдержке';
			$mess="Вы зарегистрированы в техподдержке.<br>
			http://sup.wilstream.ru<br>
			Логин: ".$login."<br>
			Пароль: ".$pass;
		}
		$q=OCIParse($c,"select distinct t.email from SUP_TEXNARI_EMAILS t
		where t.texnari_id=".$user_id." and valid_date is not null");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; while(OCIFetch($q)) {$i++;
			$to_email=OCIResult($q,"EMAIL");
			echo send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess)."<hr>";
		}
		if($i>0) echo "<script>alert('Пользователю отправлен EMAIL с рег. данными');</script>";
	}
	
	//удалеине пользователя
	if($del_confirm=='yes' and ($status=='sended_code' or $status=='wait_activation' or $status=='active')) {
		if($status=='sended_code' or $status=='wait_activation') {
			$del=OCIParse($c,"delete from sup_user where id=".$user_id);
			OCIExecute($del,OCI_DEFAULT);
			OCICommit($c);
		}
		else {
			$upd=OCIParse($c,"update sup_user set deleted=sysdate where id=".$user_id);
			OCIExecute($upd,OCI_DEFAULT);
			OCICommit($c);			
		}
	}	
	
echo "<script>
parent.parent.admUsersLeftFrame.document.location.reload();
parent.location.reload();</script>";
}
?>
