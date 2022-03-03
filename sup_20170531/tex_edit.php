<?php
extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Заявка на техподдержку</title>
</head>
<body>
<?php
//echo session_name()."--".session_id();


if (isset($_SESSION['auth']) and $_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or $_SESSION['eval']<>'create_new')) {
}
else {
echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра данной страницы или Вы не прошли авторизацию</b></font>";
exit();
}

include("../../sup_conf/sup_conn_string");

if(isset($save) or isset($close_z)) {
	//дубликат, кривые руки
	if(isset($dublikat) or isset($krivie_ruki)) {
		$q=OCIParse($c,"select dublikat,krivie_ruki from sup_base where id='".$base_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$dublikat_old=OCIResult($q,"DUBLIKAT");
		$krivie_ruki_old=OCIResult($q,"KRIVIE_RUKI");
		if($dublikat=='y' and $dublikat<>$dublikat_old) {
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'10')");
			OCIExecute($ins,OCI_DEFAULT);
		}
		if($krivie_ruki=='y' and $krivie_ruki<>$krivie_ruki_old) {
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'11')");
			OCIExecute($ins,OCI_DEFAULT);
		}		
		$upd=OCIParse($c,"update sup_base set 
		dublikat='".$dublikat."',
		krivie_ruki='".$krivie_ruki."'
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	
	//Отзвон клиенту по проблеме (4)
	if(isset($callback_who) and isset($callback_fio) and $callback_who<>'' and trim($callback_fio)<>'' and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {
		$result=4;
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$callback_who."',:callback_fio,sysdate,'".$result."')");
		OCIBindByName($ins,":callback_fio",$callback_fio);
		OCIExecute($ins,OCI_DEFAULT);
	
		$upd=OCIParse($c,"update sup_base set 
		callback_date=decode(callback_date,null,sysdate,callback_date), 
		callback_who=decode(callback_date,null,'$callback_who',callback_who), 
		callback_fio=decode(callback_date,null,'$callback_fio',callback_fio),
		last_change=sysdate 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	} 
	//
	//Оценка (7)
	if(isset($quality) and $quality<>'' and $tex_comment<>'' and  $_SESSION['eval']=='y') {
		$result=7;
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,quality,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'".$quality."',sysdate,'".$result."')");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIExecute($ins,OCI_DEFAULT);
		
		$upd=OCIParse($c,"update sup_base 
		set quality='".$quality."', quality_coment=:coment, quality_who=:fio, last_change=sysdate 
		where id='".$base_id."'");
		OCIBindByName($upd,":coment",$tex_comment);
		OCIBindByName($upd,":fio",$_SESSION['fio']);
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//
	if(isset($save)) {
	//Перевел (5) 
		if(isset($to_user_id) and  $to_user_id<>'' and $to_user_id<>$_SESSION['user_id'] and $_SESSION['redirect']=='y') {
			$q=OCIParse($c,"select fio from sup_user where id='".$to_user_id."'");
			OCIExecute($q,OCI_DEFAULT);
			OCIFetch($q);
			$to_user_fio=OCIResult($q,"FIO");
			$result=5;
			
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,:to_user_fio,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":to_user_fio",$to_user_fio);
			OCIExecute($ins,OCI_DEFAULT);

			$upd=OCIParse($c,"update sup_base 
			set texnari_id='".$to_user_id."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate) 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			OCICommit($c);
			
		}
	//
	//Комментарий/присвоение 
	//если to_user_id есть, но пустой, то технаря не назначаем, в истории пишем комментарий от имени сессионного пользоваетля
		//если to_user_id не существует, то назначем пользователя сессии
		
		//присвоил
		else if($_SESSION['solution']=='y' /*and $kto_id<>$_SESSION['user_id']*/ and (!isset($quality) or $quality=='') and $tex_comment<>'') {
			if(($from_user_id<>$_SESSION['user_id'] and (!isset($to_user_id) or $to_user_id==$_SESSION['user_id']))) {
			//if($from_user_id=='' and (!isset($to_user_id) or $to_user_id==$_SESSION['user_id'])) {
				$user_id=$_SESSION['user_id'];
				$result=8;
				
				$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
				values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
				OCIBindByName($ins,":coment",$tex_comment);
				OCIExecute($ins,OCI_DEFAULT);
				
				$upd=OCIParse($c,"update sup_base set 
				texnari_id='".$user_id."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate) 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
				
				OCICommit($c);
			}
			//прокомментировал (6)
			else if ($tex_comment<>'') {
				$user_id='';
				$result=6;
				$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
				values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
				OCIBindByName($ins,":coment",$tex_comment);
				OCIExecute($ins,OCI_DEFAULT);
			
				$upd=OCIParse($c,"update sup_base set 
				--date_close=null, 
				--ready_to_close=null, 
				--who_close=null, 
				last_change=sysdate 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
			
				OCICommit($c);
			}

		}
		//комментарий (6), для создателей заявок, если нет преава решать проблемы (технаря не назначем)
		else if(($_SESSION['solution']<>'y' or $kto_id==$_SESSION['user_id']) and (!isset($quality) or $quality=='') and $tex_comment<>'') {
			$user_id='';
			$result=6;

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
			
			$upd=OCIParse($c,"update sup_base set 
			last_change=sysdate 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			
			OCICommit($c);
		}
		 	
	//
	}
	//Закрытие
	if(isset($close_z)) {
		if($_SESSION['deny_close']=='y') {
			//готова к проверке (9)
			$result=9;	

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
		
			$upd=OCIParse($c,"update sup_base set 
			texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), ready_to_close=sysdate, date_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate) 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
		
			OCICommit($c);
		}
		else {	
			//Закрыл (3)
			$result=3;
			
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
			
			$upd=OCIParse($c,"update sup_base set 
			texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), date_close=sysdate, who_close='".$_SESSION['user_id']."', last_change=sysdate, in_work=nvl(in_work,sysdate) 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			
			OCICommit($c);
		}
	}	
	//Обновление списка проблем
	if(isset($trbl_id) and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {
		$q_del=OCIParse($c,"delete from SUP_TRBL_ALLOC where base_id='".$base_id."' and trbl_type_id not in(".implode(',',$trbl_id).")");
		OCIExecute($q_del,OCI_DEFAULT);
		$q_ins=OCIParse($c,"insert into sup_trbl_alloc
		select to_number(:base_id),to_number(:trbl_id) from dual
		minus
		select t.base_id,t.trbl_type_id 
		from sup_trbl_alloc t
		where t.base_id=:base_id");
		OCIBindByName($q_ins,":base_id",$base_id);
		foreach($trbl_id as $key => $val) {
			OCIBindByName($q_ins,":trbl_id",$val);
			OCIExecute($q_ins,OCI_DEFAULT);
		}
		
		//обновление детализации проблем
		$q_del=OCIParse($c,"delete from SUP_TRBL_DET_ALLOC where base_id='".$base_id."'");
		OCIExecute($q_del,OCI_DEFAULT);
		
		$q_ins=OCIParse($c,"insert into SUP_TRBL_DET_ALLOC (base_id,trbl_detail_id)
		values (:base_id,:trbl_det_id)");
		OCIBindByName($q_ins,":base_id",$base_id);
		foreach($trbl_id as $key => $val) {
			if(isset($trbl_detail)) {
				foreach($trbl_detail as $key1 => $val1) {
					if($val1<>'') {
						OCIBindByName($q_ins,":trbl_det_id",$val1);
						OCIExecute($q_ins,OCI_DEFAULT);
					}
				}
			}
		}
		OCICommit($c);		
	}
	//
	//

include("func_send.php");
//отправляем отчет о закрытии заявки нужно для Андрея!
if($result==3) {
$mess="
Номер заявки:<b> ".$base_id." </b><br>
Закрыл:<b> ".$_SESSION['fio']."</b>";
send('mailex','','it-itil@yandex.ru','','support@wilstream.ru','','','Закрыта заявка №'.$base_id,$mess);
}
//
//Отправляем email уведомление
include("func_notifications.php");

if($result==5) {

	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."','".$to_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_redir='y' and u.deleted is null
and e.texnari_id=u.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'support@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' переадресована на '.$to_user_fio, $mail_mess);
	}
}

if($result==6) {

	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_coment='y' and u.deleted is null
and e.texnari_id=u.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'support@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' - комментарий', $mail_mess);
	}
}

if($result==8) {

	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_prisv='y' and u.deleted is null
and e.texnari_id=u.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'support@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' принята в работу', $mail_mess);
	}
}

if($result==9) {

	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_ready='y' and u.deleted is null
and e.texnari_id=u.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'support@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' готова к проверке', $mail_mess);
	}
}

if($result==3) {

	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_close='y' and u.deleted is null
and e.texnari_id=u.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'support@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' закрыта', $mail_mess);
	}
}
//

//

//СМС-уведомления
include("../../sup_conf/send_sms.php");	
$from_phone='Wilstream';
$sms_text='';
if($result==5) {
	$q=OCIParse($c,"select tt.name from SUP_BASE b, SUP_TRBL_ALLOC ta, SUP_TRBL_TYPE tt
where b.id=".$base_id." and ta.base_id=b.id and tt.id=ta.trbl_type_id 
and tt.trbl_grp_id=b.trbl_grp_id
order by tt.name");
	OCIExecute($q);$trbl_names=array();$n=0;while(OCIFetch($q)) {$n++;$trbl_names[$n]=OCIResult($q,"NAME");}

	$sms_text.=$base_id.'-заявка переадресована на инженера '.chr(10);
	$sms_text.=$to_user_fio.".".chr(10);
	$sms_text.=$klinika_name.".".chr(10);
	$n=0; foreach($trbl_names as $val) {$n++;
			if($n>3) {$sms_text.="...".chr(10); break;}		
			$sms_text.=$val.";".chr(10);
		}
	$sms_text.=$_SESSION['fio'].".";			

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$from_phone,$Phone_list,$sms_text);
	}
}
if($result==8) {

	$q=OCIParse($c,"select tt.name from SUP_BASE b, SUP_TRBL_ALLOC ta, SUP_TRBL_TYPE tt
where b.id=".$base_id." and ta.base_id=b.id and tt.id=ta.trbl_type_id 
and tt.trbl_grp_id=b.trbl_grp_id
order by tt.name");
	OCIExecute($q);$trbl_names=array();$n=0;while(OCIFetch($q)) {$n++;$trbl_names[$n]=OCIResult($q,"NAME");}

	$sms_text.=$base_id.'-заявка принята в работу.'.chr(10);
	$sms_text.=$klinika_name.".".chr(10);
	$n=0; foreach($trbl_names as $val) {$n++;
			if($n>3) {$sms_text.="...".chr(10); break;}		
			$sms_text.=$val.";".chr(10);
		}		
	$sms_text.=$_SESSION['fio'].".";

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$from_phone,$Phone_list,$sms_text);
	}
}

if($result==9) {
	$q=OCIParse($c,"select tt.name from SUP_BASE b, SUP_TRBL_ALLOC ta, SUP_TRBL_TYPE tt
where b.id=".$base_id." and ta.base_id=b.id and tt.id=ta.trbl_type_id 
and tt.trbl_grp_id=b.trbl_grp_id
order by tt.name");
	OCIExecute($q);$trbl_names=array();$n=0;while(OCIFetch($q)) {$n++;$trbl_names[$n]=OCIResult($q,"NAME");}

	$sms_text.=$base_id.'-заявка готова к проверке.'.chr(10);
	$sms_text.=$klinika_name.".".chr(10);
	$n=0; foreach($trbl_names as $val) {$n++;
			if($n>3) {$sms_text.="...".chr(10); break;}		
			$sms_text.=$val.";".chr(10);
		}
	$sms_text.=$_SESSION['fio'].".";

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$from_phone,$Phone_list,$sms_text);
	}
}

if($result==3) {
	$q=OCIParse($c,"select tt.name from SUP_BASE b, SUP_TRBL_ALLOC ta, SUP_TRBL_TYPE tt
where b.id=".$base_id." and ta.base_id=b.id and tt.id=ta.trbl_type_id 
and tt.trbl_grp_id=b.trbl_grp_id
order by tt.name");
	OCIExecute($q);$trbl_names=array();$n=0;while(OCIFetch($q)) {$n++;$trbl_names[$n]=OCIResult($q,"NAME");}

	$sms_text.=$base_id.'-заявка закрыта.'.chr(10);
	$sms_text.=$klinika_name.".".chr(10);
	$n=0; foreach($trbl_names as $val) {$n++;
			if($n>3) {$sms_text.="...".chr(10); break;}		
			$sms_text.=$val.";".chr(10);
		}
	$sms_text.=$_SESSION['fio'].".";
	
	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$from_phone,$Phone_list,$sms_text);
	}
}		
//

echo "
<script>
document.location='".$document_location."';
window.opener.location.reload();
//self.close();
</script>
";

exit();

}
//

if (isset($base_id)) {
	if(!isset($callback_fio)) $callback_fio='';
	$q=OCIParse($c,"select b.id,
	       to_char(b.date_in_call, 'DD.MM.YYYY HH24:MI') date_in_call,
	       b.cdpn,
	       b.klinika_id,
		   b.texnari_id,
	       k.name,
		   k.phone,
	       b.kto,
		   b.kto_id,
	       b.oper_comment,
	       b.u_kogo,
		   b.quality,
		   b.quality_coment,
		   b.quality_who,
		   b.trbl_grp_id,
		   b.ip_address,
		   b.ready_to_close,
		   b.date_close,
		   case  
			 when b.quality='1' then 'red'
			 when b.quality='2' then 'red'
			 when b.quality='3' then '#CC6633'
			 when b.quality='4' then '#339966'
			 when b.quality='5' then 'green'
	       end q_color,
		   b.dublikat,
		   b.krivie_ruki	   
	  from sup_base b, sup_klinika k
	 where b.klinika_id=k.id (+)
	 and b.id = '".$base_id."'
	");
	
	OCIExecute($q,OCI_DEFAULT);
	if(!OCIFetch($q)) {echo "<font color=red><b>ОШИБКА: Такой заявки не существует</b></font>"; exit();}
	$texnari_id=OCIResult($q,"TEXNARI_ID");
	$kto_id=OCIResult($q,"KTO_ID");
	$date_close=OCIResult($q,"DATE_CLOSE");
	$ready_to_close=OCIResult($q,"READY_TO_CLOSE");
	$date_in_call=OCIResult($q,"DATE_IN_CALL");
	$dublikat=OCIResult($q,"DUBLIKAT");
	$krivie_ruki=OCIResult($q,"KRIVIE_RUKI");	
	
	echo "<form name=tex_edit_frm>";
	echo "<input type=hidden name=document_location value='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."'>";
	echo "<input type=hidden name=from_user_id value='".$texnari_id."'>";
	echo "<input type=hidden name=kto_id value='".$kto_id."'>";

	echo "<font size=4>".OCIResult($q,"NAME").(OCIResult($q,"PHONE")<>''?' ('.OCIResult($q,"PHONE").')':'');
	echo ($dublikat?"<font color=red> (дубликат) </font>":"");
	echo ($krivie_ruki?"<font color=red> (ошибка пользователя) </font>":"");
	echo "</font>";
	
	echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>
	<tr><td bgcolor=white valign=top>
	<nobr>№ заявки: <b>".$base_id."</b></nobr><nobr> дата: <b>".$date_in_call."</b></nobr><hr>";
	if(OCIResult($q,"CDPN")<>'') echo "АОН: <b>".OCIResult($q,"CDPN")."</b><br>";
	echo "IP: <b>".OCIResult($q,"IP_ADDRESS")."</b><hr>
	Кто обратился:<br><b>".OCIResult($q,"KTO")."</b><br>";
	if(OCIResult($q,"KTO_ID")<>'') {
		$q_tmp=OCIParse($c,"select phone from SUP_TEXNARI_PHONES t where texnari_id='".OCIResult($q,"KTO_ID")."' and contact='y' order by ord");
		OCIExecute($q_tmp,OCI_DEFAULT);
		while (OCIFetch($q_tmp)) {
			echo OCIResult($q_tmp,"PHONE")."<br>";
		}
		$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".OCIResult($q,"KTO_ID")."'");
		OCIExecute($q_tmp,OCI_DEFAULT);
		//$mailtos=array();
		$i=0; while(OCIFetch($q_tmp)) {$i++;
			$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
		}
		if(isset($mailtos)) {
			$mailtos=implode(',',$mailtos);
			echo "<a href='mailto:".$mailtos."?subject=Заявка №".$base_id." - ответ'>".$mailtos."</a><br>";
		}
	}
	echo "<hr>
	У кого не работает:<br><b>".OCIResult($q,"U_KOGO")."</b><br>
	</td>
	<td bgcolor=white valign=top>Тип проблемы: <font color=red>ВНИМАНИЕ! Не забудьте уточнить тип проблемы!</font><br>";

	$q2=OCIParse($c,"
	select distinct a.id trbl_id, a.name, a.ord,a.color, decode(b.trbl_type_id, null, null, 'checked ') checked
  	from (select distinct stt.id, stt.name, stt.deleted, stt.ord,stt.color
        from sup_lt slt, sup_lt_group slg, sup_trbl_type stt
        where slt.location_id = '".OCIResult($q,"KLINIKA_ID")."'
        and slt.trbl_id=stt.id      
        and slg.type='common' 
        and slt.lt_grp_id=slg.id
        and stt.trbl_grp_id = decode('".OCIResult($q,"TRBL_GRP_ID")."', '0', stt.trbl_grp_id, '".OCIResult($q,"TRBL_GRP_ID")."')
        and slt.lt_grp_id = decode('".$_SESSION['lt_grp_id']."', '0', slt.lt_grp_id, '".$_SESSION['lt_grp_id']."')
        ) a,
       
        (select sta.trbl_type_id
         from sup_trbl_alloc sta
         where sta.base_id = '".$base_id."') b
 	where a.id = b.trbl_type_id(+) and (a.deleted is null or b.trbl_type_id is not null) order by a.ord nulls first, a.name");
	
	OCIExecute($q2,OCI_DEFAULT);
	$i=0;
	
	$q3=OCIParse($c,"select d.id,d.name,decode(a.trbl_detail_id,null,null,' selected') selected from SUP_TRBL_DETAIL d, sup_trbl_det_alloc a
where d.trbl_id=:trbl_id
and d.deleted is null
and a.base_id(+)='".$base_id."' 
and a.trbl_detail_id(+)=d.id
order by d.ord");
	
	while(OCIFetch($q2)) {
		$i++;
		echo "<nobr>";
		if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
			//echo "<input type=checkbox ".OCIResult($q2,"CHECKED")."name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."' onclick='fn_chk_trbl(this)'>";
			echo "<input type=radio ".OCIResult($q2,"CHECKED")."name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."' onclick=ch_trbl(this)>";			
			echo "<font color='".OCIResult($q2,"COLOR")."'>";
			if(OCIResult($q2,"CHECKED")<>NULL) echo "<b>".OCIResult($q2,"NAME")."</b>";
			else echo OCIResult($q2,"NAME"); 	
			echo "</font>";
			echo "</nobr><br>";
			$trbl_id=OCIResult($q2,"TRBL_ID");
			OCIBindByName($q3,":trbl_id",$trbl_id);
			OCIExecute($q3);
			$j=0;
			while(OCIFetch($q3)) {
				$j++;
				if($j==1) {echo "<div align=center id=div_det_".OCIResult($q2,"TRBL_ID").(OCIResult($q2,"CHECKED")<>"checked "?" style='display:none'":"").">";
					echo "<select id=sel_det_".OCIResult($q2,"TRBL_ID")." name=trbl_detail[]>";
					echo "<option value='' style='color:red'>выберите, что конкретно?</option>";
				}
				echo "<option value='".OCIResult($q3,"ID")."'".OCIResult($q3,"SELECTED").">".OCIResult($q3,"NAME")."</option>";
			}
			if($j>0) {echo "</select></div>";} 
		}
		else {
			//echo "<input type=checkbox ".OCIResult($q2,"CHECKED")."disabled>";
			echo "<input type=radio ".OCIResult($q2,"CHECKED")."disabled>";
			if(OCIResult($q2,"CHECKED")<>NULL) {echo "<b>".OCIResult($q2,"NAME")."</b>
				<input type='hidden' name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."'>";
			}
			else echo OCIResult($q2,"NAME");	
			echo "</nobr><br>";
		}
		$trbl_ids[$i]=OCIResult($q2,"TRBL_ID");
	}
	
	if(isset($trbl_ids)) $q2_sql=" and sta.trbl_type_id not in (".implode(',',$trbl_ids).") ";
	else $q2_sql="";
	
	$q2=OCIParse($c,"
	select distinct stt.id trbl_id, stt.name from sup_trbl_alloc sta, sup_trbl_type stt
	where sta.base_id='".$base_id."'".$q2_sql."
	and stt.id=sta.trbl_type_id
	order by stt.name");
	OCIExecute($q2,OCI_DEFAULT);
	$i=0;
	while(OCIFetch($q2)) {
		if($i==0) echo "<hr>";
		$i++;
		//echo "<input type=checkbox checked disabled><b>".OCIResult($q2,"NAME")."</b>";
		echo "<input type=radio checked disabled><b>".OCIResult($q2,"NAME")."</b>";
		echo "<input type='hidden' name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."'><br>";
	}


	echo "</b></td>
	</tr>
	<tr>
	<td bgcolor=white valign=top colspan=2>Суть проблемы:<br><b>".nl2br(OCIResult($q,"OPER_COMMENT"))."</b></td>
	</tr>
	</table>";

	echo "<input type=hidden name=base_id value=".OCIResult($q,"ID").">";
	echo "<input type=hidden name=klinika_id value=".OCIResult($q,"KLINIKA_ID").">";
	echo "<input type=hidden name=klinika_name value='".OCIResult($q,"NAME")."'>";
	$quality=OCIResult($q,"QUALITY");
	$quality_who=OCIResult($q,"QUALITY_WHO");
	$quality_coment=OCIResult($q,"QUALITY_COMENT");
	$trbl_grp_id=OCIResult($q,"TRBL_GRP_ID");
	$location_id=OCIResult($q,"KLINIKA_ID");
	$q_color=OCIResult($q,"Q_COLOR");

	if($quality<>'') {
		echo "Оценка:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>Оценил: <b>$quality_who:</b> <font color='$q_color'><b>$quality</b></font><br>$quality_coment</td></tr>";
		echo "</table>";
	}

	$q=OCIParse($c,"select distinct su.id, su.fio from sup_trbl_type stt, sup_lt slt, sup_user su
	where stt.trbl_grp_id='".$trbl_grp_id."' and slt.location_id='".$location_id."'
	and slt.trbl_id=stt.id and slt.lt_grp_id=decode(su.lt_grp_id,'0',slt.lt_grp_id,su.lt_grp_id)
	and su.solution='y' and su.deleted is null
	order by su.fio");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	if($i==0) {$texnari_ids[1]=$_SESSION['user_id']; $texnari_names[1]=$_SESSION['fio'];}


	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
		echo "Отзвон клиенту по проблеме:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white><b>Кто отзвонился:</b> ";
		echo "<select name=callback_who onchange=fn_check()>";
		echo "<option></option>";
		if($_SESSION['look']=='y') {
			foreach ($texnari_ids as $key => $val) {
				echo "<option value='".$val."'";
				//if($val==$_SESSION['user_id']) echo " selected";
				echo ">".$texnari_names[$key]."</option>";	
			}
		
		}
		else {
			echo "<option value='".$_SESSION['user_id']."'>".$_SESSION['fio']."</option>";	
		}
		echo "</select></td></tr>";
		echo "<tr><td bgcolor=white><b>Кому отзвонился</b>: <input style='width:98%' type=text name=callback_fio value='".$callback_fio."' onkeyup=fn_check()></td>";
		echo "</table>";
	}

	//история
	$q3=OCIParse($c,"select to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who,
	decode(sth.result_call,1,'передал по телефону',2,'не дозвонился',3,'закрыл',4,'отзвонился ',5,'переадресовал',6,'комментарий',7,'оценил',8,'присвоил',9,'готово к проверке',10, 'статус \"дубликат\"', 11, 'статус \"ошибка пользоваетля\"',null) result, 
	decode(sth.result_call,1,'green',2,'blue',3,'red',4,'blue',5,'maroon',6,'indigo',7,'black',8,'green',9,'#006400',10,'red',11,'red',null) color, sth.quality 
	from sup_texnari_history sth, sup_user su
	where sth.base_id='".$base_id."'
	and su.id(+)=sth.texnari_id
	order by sth.datetime");
	OCIExecute($q3,OCI_DEFAULT);
	
	$i=0;
	while (OCIFetch($q3)) {
		$i++; if($i==1) {
			echo "История:";
			echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		}
		echo "<tr><td bgcolor=white valign=top>";
		echo "<b>".OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"COLOR")."'>".OCIResult($q3,"RESULT")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT")=='оценил') {
			if(OCIResult($q3,"QUALITY")=='1') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') echo ": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') echo ": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') echo ": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT")<>'отзвонился ') echo "</b><br>";

		echo nl2br(OCIResult($q3,"TEXNARY_COMENT"));
		echo "</td></tr>";
	}
	if($i>0) echo "</table>";
	//

	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y' or $_SESSION['create_new']=='y') {
		echo "Комментарий: <font color=red>(обязательное поле)</font><br><textarea onkeyup=fn_check() style='width:98%' rows=5 name=tex_comment></textarea><hr>";
	}

	if($_SESSION['eval']=='y') {
		echo "<font size=3><b>Оценка: </b></font><select name=quality onchange=fn_check()><option></option>
		<option style='color:red' value='1'>1</option>
		<option style='color:red' value='2'>2</option>
		<option style='color:#CC6633' value='3'>3</option>
		<option style='color:#339966' value='4'>4</option>
		<option style='color:green' value='5'>5</option>
		</select><hr>";
	}

	if($_SESSION['solution']=='y') {
		echo "<input type=hidden name='dublikat'><input type='checkbox' name='dublikat' value='y'"; echo ($dublikat?" checked":""); echo "><font color=red>Дубликат</font></input>
		 | <input type=hidden name='krivie_ruki'><input type='checkbox' name='krivie_ruki' value='y'"; echo ($krivie_ruki?" checked":""); echo"><font color=red>Ошибка пользоваетля</font></input><hr>";
	}

	if($_SESSION['redirect']=='y') {
		echo "<font color=indigo>Комменитровать</font> / <font color=maroon>Переадресовать</font> / <font color=green>Присвоить заявку</font>: ";
		echo "<nobr>";
		echo "<select name=to_user_id onchange=fn_check()>";
		//if($_SESSION['solution']=='y') $def_usr=$_SESSION['user_id']; else $def_usr=$texnari_id;
		echo "<option value='' style='color:indigo'>оставить комментарий</option>";
		//if($def_usr=='') echo "<option></option>";
		foreach ($texnari_ids as $key => $val) {
			echo "<option value='".$val."'";
			if($val==$_SESSION['user_id']) echo " style='color:green'";
			else echo " style='color:maroon'";
			if($texnari_id=='' and $val==$_SESSION['user_id']) echo " selected"; //если заявка еще не принята в работу, то по умолчанию - присвоить
			echo ">".$texnari_names[$key]."</option>";	
		}
		echo "</select>";
	}
	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y' or $_SESSION['create_new']=='y') {
		echo " <input type=submit disabled name=save style='background-color:#66FF66' value='Сохранить'>";
	}
	echo "</nobr>";
	echo "<hr>";
	
	if($_SESSION['solution']=='y' and $date_close=='') {
		if($_SESSION['deny_close']=='y' and $ready_to_close=='') {echo "<input type=submit disabled name=close_z style='background-color:#458B00' value='Готово к проверке'> ";}
		else if($_SESSION['deny_close']<>'y' and $date_close=='') {echo "<input type=submit disabled name=close_z style='background-color:#FF6600' value='Закрыть заявку'> ";}
	}
	echo "</form>";
}

?>
<script>
function fn_check() {
	with(tex_edit_frm) {
		if(
		('callback_fio' in tex_edit_frm && callback_fio.value!='' && callback_who.value!='') //отзвон
	||	('quality' in tex_edit_frm && tex_comment.value!='' && quality.value!='') //оценка
	||	('to_user_id' in tex_edit_frm && tex_comment.value!='') //перенаправление
	||	((!('callback_fio' in tex_edit_frm) || (callback_fio.value=='' && callback_who.value=='')) && !('quality' in tex_edit_frm) && tex_comment.value!='') //закрытие, комментарий, отзвон
	||  (!('callback_fio' in tex_edit_frm) && 'quality' in tex_edit_frm && ((quality.value!='' && tex_comment.value!='')||tex_comment.value!=''))	
		)
		{
			if('close_z' in tex_edit_frm) {if(tex_comment.value!='') close_z.disabled=false; else close_z.disabled=true;}
			if('save' in tex_edit_frm) save.disabled=false;
		}
		else {
			if('close_z' in tex_edit_frm) close_z.disabled=true;
			if('save' in tex_edit_frm) save.disabled=true;
		}
	}	
}
function fn_chk_trbl(this_obj) {
	var n=0;
	for(i=0;i<document.forms[0].elements.length;i++) {
		obj=document.forms[0].elements[i];
		if(obj.type=='checkbox' && obj.checked && !obj.disabled) {n++; break;}
	}
	if(n==0){alert('Должен быть выбран хотя бы одни тип проблемы!');this_obj.checked=true;}
}
function ch_trbl(obj) {
	with(document.forms[0]) {
		if(div=document.getElementById('div_det_'+obj.value)) {div.style.display='';}
		for(i=0;i<elements['trbl_id[]'].length;i++) {
			cur_obj=elements['trbl_id[]'][i];
			if(cur_obj!=obj && cur_obj.checked!=true) {
				if(div=document.getElementById('div_det_'+cur_obj.value)) {div.style.display='none';}
				if(sel=document.getElementById('sel_det_'+cur_obj.value)) {sel.options[0].selected=true;}
			}
		}
	}
}
</script>
</body>
</html>
