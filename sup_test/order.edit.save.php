<?php
extract($_POST);
session_start();
$sid=session_id();
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

if(!isset($save) and !isset($ready_z) and !isset($close_z) and !isset($delay_z) and !isset($resume_z)) {exit();}

set_error_handler ("my_error_handler");
//echo session_name()."--".session_id();

if (!isset($_SESSION['auth'])) {
	echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра данной страницы. Вы не прошли авторизацию</b></font>";
	exit();
}
if (!isset($base_id) or $base_id=='') {exit();}

include("sup/sup_conn_string");

$old_last_change=$last_change;

//информация о заявке
include("order.get.order.info.php");
extract(get_order_info($c,$base_id));
if(isset($error)) {echo $error; exit();}

if($last_change<>$old_last_change) {
	echo "<script>alert('Ошибка! Заявка модифицирована другим пользователелм. Действие отменено!')</script>";
	echo "<script>parent.window.opener.location.reload(); parent.window.location.reload(); </script>
";
	exit();
}

$hist_id='';
$result='';


//смена местоположеиня 
if(isset($new_location_id) and $new_location_id<>$location_id) {
	$result=670;
	$q=OCIParse($c,"select name new_location_name from SUP_KLINIKA where id=".$new_location_id);
	OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
	$tmp_coment=$location_name." -> ".OCIResult($q,"NEW_LOCATION_NAME");

	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tmp_coment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);
	
	$upd=OCIParse($c,"update sup_base set 
	klinika_id='".$new_location_id."',
	last_change=sysdate
	where id='".$base_id."'");
	
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);			
}

	//смена типа проблемы 
if(isset($new_trbl_type_id) and $new_trbl_type_id<>$trbl_id) {
	$result=675;
	if(!isset($new_trbl_det_id)) $new_trbl_det_id='';
		
	$q=OCIParse($c,"select name new_trbl_type_name from SUP_TRBL_TYPE where id=".$new_trbl_type_id);
	OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
	$tmp_coment=$trbl_name." -> ".OCIResult($q,"NEW_TRBL_TYPE_NAME");
				
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tmp_coment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);
	
	$upd=OCIParse($c,"update sup_base set 
	trbl_type_id='".$new_trbl_type_id."',
	trbl_detail_id='".$new_trbl_det_id."',
	last_change=sysdate
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);			
}	
	//обновление детализации проблем
if(isset($new_trbl_det_id) and ($solution=='y' or $redirect=='y')) {
	$upd=OCIParse($c,"update sup_base set 
	trbl_detail_id='".$new_trbl_det_id."',
	last_change=sysdate
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);			
}
	
//дубликат, кривые руки
if(isset($new_dublikat) or isset($new_krivie_ruki)) {
	if($new_dublikat=='y' and $new_dublikat<>$dublikat) {
		$result=560;
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);
	}
	if($new_krivie_ruki=='y' and $new_krivie_ruki<>$krivie_ruki) {
		$result=565;
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);
	}		
	$upd=OCIParse($c,"update sup_base set 
	dublikat='".$new_dublikat."',
	krivie_ruki='".$new_krivie_ruki."'
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
}
//смена типа проблемы

//Оценка (700)
if(isset($new_quality) and $new_quality<>'') {
	$result=700;
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,quality,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'".$new_quality."',sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);
		
	$upd=OCIParse($c,"update sup_base 
	set quality='".$new_quality."', quality_coment=:coment, quality_who=:fio, last_change=sysdate 
	where id='".$base_id."'");
	OCIBindByName($upd,":coment",$tex_comment);
	OCIBindByName($upd,":fio",$_SESSION['fio']);
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
}
//

//********************************
if(isset($save)) {
	//открыть (110) (переадресовать на группу)	
	if(isset($to_user_id) and $to_user_id=='open') {
		$result=110;
			
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'',sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);		
			
		$upd=OCIParse($c,"update sup_base 
		set 
		texnari_id=null, 
		date_close=null, 
		ready_to_close=null, 
		who_close=null, 
		last_change=sysdate, 
		in_work=null,
		delay_to=null 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);		
	}
	//принять в работу (225)
	elseif(isset($to_user_id) and $to_user_id=='to_work') {
		$result=225;
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);
			
		$upd=OCIParse($c,"update sup_base set 
		texnari_id='".$_SESSION['user_id']."', 
		date_close=null, 
		ready_to_close=null, 
		who_close=null, 
		last_change=sysdate, 
		in_work=nvl(in_work,sysdate),
		delay_to=null 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
			
		OCICommit($c);		
	}
	//прокомментировал (666)
	elseif(isset($to_user_id) and $to_user_id=='coment'/* and trim($tex_comment)<>''*/) {
		$result=666;

		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);
		
		$upd=OCIParse($c,"update sup_base set 
		last_change=sysdate,
		delay_to=null
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
			
		OCICommit($c);		
	}		
	//перевод конкретному инжденеру (250)
	elseif(isset($to_user_id) and $to_user_id<>'coment' and $to_user_id<>'to_work' and $to_user_id<>'open') {
		echo $to_user_id;
		$q=OCIParse($c,"select fio from sup_user where id='".$to_user_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$to_user_fio=OCIResult($q,"FIO");
		$result=250;
			
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,:to_user_fio,sysdate,'".$result."') returning id into :hist_id");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIBindByName($ins,":to_user_fio",$to_user_fio);
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);		
			
		$upd=OCIParse($c,"update sup_base 
		set texnari_id='".$to_user_id."', 
		date_close=null, 
		ready_to_close=null, 
		who_close=null, 
		last_change=sysdate, 
		in_work=nvl(in_work,sysdate),
		delay_to=null 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}	
}	
//*******************************
//готова к проверке (444)
if(isset($ready_z)) {
	$result=444;	

	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);
		
	$upd=OCIParse($c,"update sup_base set 
	texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), 
	ready_to_close=sysdate, 
	date_close=null, 
	who_close=null, 
	last_change=sysdate, 
	in_work=nvl(in_work,sysdate),
	delay_to=null
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
		
	OCICommit($c);
}
//закрыта (555)
elseif(isset($close_z)) {
	$result=555;
		
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);
		
	$upd=OCIParse($c,"update sup_base set 
	texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), 
	date_close=sysdate, 
	who_close='".$_SESSION['user_id']."', 
	last_change=sysdate, 
	in_work=nvl(in_work,sysdate),
	delay_to=null 
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
		
	OCICommit($c);
}
//Отложеине (333)
elseif(isset($delay_z)) {
	$result=333;
		
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call, to_who)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."','".$delay_to_date."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);		

	$upd=OCIParse($c,"update sup_base set 
	last_change=sysdate, 
	delay_to=to_date('".$delay_to_date."')
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
		
	OCICommit($c);
}
//возобновить (открыть) (120)
elseif(isset($resume_z) and $opened=='y') {
	$result=120;
		
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);		

	$upd=OCIParse($c,"update sup_base set 
	date_close=null, 
	ready_to_close=null, 
	who_close=null, 
	last_change=sysdate, 
	delay_to=null
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
		
	OCICommit($c);
}
//вернуть в работу (230)
elseif(isset($resume_z) and $opened<>'y') {
	$result=230;
		
	$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
	values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
	OCIBindByName($ins,":coment",$tex_comment);
	OCIBindByName($ins,":hist_id",$hist_id,16);
	OCIExecute($ins,OCI_DEFAULT);		

	$upd=OCIParse($c,"update sup_base set 
	date_close=null, 
	ready_to_close=null, 
	who_close=null, 
	last_change=sysdate, 
	delay_to=null
	where id='".$base_id."'");
	OCIExecute($upd,OCI_DEFAULT);
		
	OCICommit($c);
}
//

//***************************	
//сохранение файлов
$upd=OCIParse($c,"update sup_files set tmp='', hist_id='".$hist_id."' where base_id='".$base_id."' and sess_id='".$sid."' and tmp='y'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);	

//echo "update sup_files set tmp='', hist_id='".$hist_id."' where base_id='".$base_id."' and sess_id='".$sid."' and tmp='y'";

//include("func_send.php");
include("send_email.php");
include("sup/smtp_conf.php");
include("func_notifications.php");

if($result==110) {
	//email открыта (перевод на группу инженеров)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.3.1.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --если хотябы в одной из групп пользователь должен получать свои заявки, то свои получает независимо от группы    
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_new='all') --если стоит опция получать все открытые, то пользователь получает все открытые заявки в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' открыта (переадресована на группу)', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' открыта (переадресована на группу)', $mail_mess,
		$headers='', $debug='y');	

		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' открыта (переадресована на группу)',$email_res);		
		
	}	
}

if($result==250) {
	//email перевод конкретному инжденеру (250)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.3.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_redir in ('my','all') and su.id in (b.kto_id,b.texnari_id,'".$from_user_id."')) --автору, новому исполнителю, предыдущему исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_redir='all') --все переадресованные в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' переадресована на '.$to_user_fio, $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' переадресована на '.$to_user_fio, $mail_mess,
		$headers='', $debug='y');
		
		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' переадресована на '.$to_user_fio,$email_res);			
	}
}

if($result==666) {
	//email прокомментировал (666)  
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.2.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_coment in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_coment='all') --все прокомментированные в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' - комментарий', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' - комментарий', $mail_mess,
		$headers='', $debug='y');
		
		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' - комментарий',$email_res);		
	}
}

if($result==225) {
	//email присвоил (225)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.4.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_prisv in ('my','all') and su.id in (b.kto_id,'".$from_user_id."')) --автору, предыдущему исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_prisv='all') --все присвоенные в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' принята в работу', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' принята в работу', $mail_mess,
		$headers='', $debug='y');

		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' принята в работу',$email_res);			
	}
}

if($result==444) {
	//email готова к проверке (444)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.5.2. 
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_ready in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_ready='all') --все готовые в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' готова к проверке', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' готова к проверке', $mail_mess,
		$headers='', $debug='y');

		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' готова к проверке',$email_res);					
	}
}

if($result==555) {
	//email Закрыл (555)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.7.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_close in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_close='all') --все закрытые в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' закрыта', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' закрыта', $mail_mess,
		$headers='', $debug='y');
		
		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' закрыта',$email_res);
	}
}

if($result==333) {
	//email отложил (333)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//2.1.6.2. 
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_delay in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_delay='all') --все отложенные в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' отложена на '.$delay_to_date, $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' отложена на '.$delay_to_date, $mail_mess,
		$headers='', $debug='y');

		$smtp_dur_sec=time()-$time1;
		
		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' отложена на '.$delay_to_date,$email_res);				
	}
}
if($result==120) {
	//EMAIL возобновил (открыл) (120)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --если хотябы в одной из групп пользователь должен получать свои заявки, то свои получает независимо от группы    
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_new='all') --если стоит опция получать все открытые, то пользователь получает все открытые заявки в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' возобновлена (открыта)', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' возобновлена (открыта)', $mail_mess,
		$headers='', $debug='y');

		$smtp_dur_sec=time()-$time1;

		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' возобновлена (открыта)', $email_res);			
	}
}
if($result==230) {
	//EMAIL вернул в работу (230)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_resume in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_resume='all') --все возобновленные в группе
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		//send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'Заявка №'.$base_id.' возобновлена (в работе)', $mail_mess);
		
		$time1=time();
		
		$email_res=send_email($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' возобновлена (в работе)', $mail_mess,
		$headers='', $debug='y');

		$smtp_dur_sec=time()-$time1;

		smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass, 
		OCIResult($q,"FIO"), OCIResult($q,"EMAIL"),$_SESSION['fio'], $smtp_from_email, $_SESSION['fio'], $reply_to_email,'Заявка №'.$base_id.' возобновлена (в работе)', $email_res);			
	}
}
//

function smtp_log($smtp_server,$smtp_port,$smtp_auth_login,$smtp_auth_pass,$to_name,$to_email,$from_name,$smtp_from_email,$reply_to_name,$reply_to_email,$subj,$email_res) {
		global $c;
		global $smtp_dur_sec;
		global $base_id;
		
		$ins=OCIParse($c,"insert into sup_smtp_log 
		(datetime, sup_base_id, history_id, to_user_id, smtp_server, smtp_port, smtp_login, smtp_from_email, smtp_to_email, subj, smtp_result, dur_sec)
		values
		(sysdate,'".$base_id."','','',:smtp_server,".$smtp_port.",:smtp_login,:smtp_from_email,:smtp_to_email,:subj,:smtp_result,".$smtp_dur_sec.")");
		OCIBindByName($ins,":smtp_server",$smtp_server);
		OCIBindByName($ins,":smtp_login",$smtp_auth_login);
		OCIBindByName($ins,":smtp_from_email",$smtp_from_email);
		OCIBindByName($ins,":smtp_to_email",$to_email);
		OCIBindByName($ins,":subj",$subj);
		OCIBindByName($ins,":smtp_result",$email_res);
		OCIExecute($ins);
		OCICommit($c);

}

//СМС-уведомления
include("sup/send_sms.php");	
$sms_text='';
if($result==110) {
	//SMS открыта (110)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q); 

	$sms_text.=$base_id.'-заявка открыта (переадресована на группу).'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;			

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
	and (
       (sla.sm_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --если хотябы в одной из групп пользователь должен получать свои заявки, то свои получает независимо от группы    
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_new='all') --если стоит опция получать все открытые, то пользователь получает все открытые заявки в группе
    )
    and su.id=sla.user_id	
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}
if($result==250) {
	//SMS перевод конкретному инжденеру (250)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q); 

	$sms_text.=$base_id.'-заявка переадресована на инженера '.chr(10);
	$sms_text.=$to_user_fio.".".chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;			

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_redir in ('my','all') and su.id in (b.kto_id,b.texnari_id,'".$from_user_id."')) --автору, новому исполнителю, предыдущему исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_redir='all') --все переадресованные в группе
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}
if($result==225) {
	//SMS присвоил (225)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка принята в работу.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);	
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_prisv in ('my','all') and su.id in (b.kto_id,'".$from_user_id."')) --автору, предыдущему исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_prisv='all') --все присвоенные в группе
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}

if($result==444) {
	//SMS готова к проверке (444)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка готова к проверке.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_ready in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_ready='all') --все готовые в группе
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}

if($result==555) { //закрыта
	//SMS Закрыл (555)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка закрыта.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_close in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_close='all') --все закрытые в группе
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}		
//

if($result==333) { //отложена
	//SMS отложил (333)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка отложена на '.$delay_to_date.'.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_delay in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --автору, исполнителю, независимо от групп   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_delay='all') --все отложенные в группе
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}		
//

if($result==120) { //возобновил (открыл)
	//SMS возобновил (открыл) (120)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка возобновлена (открыта).'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --если хотябы в одной из групп пользователь должен получать свои заявки, то свои получает независимо от группы    
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_new='all') --если стоит опция получать все открытые, то пользователь получает все открытые заявки в группе	
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}		
//

if($result==230) { //возобновил (вернул в работу)
	//SMS вернул в работу (230)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-заявка возобновлена (в работе).'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_resume in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --если хотябы в одной из групп пользователь должен получать свои заявки, то свои получает независимо от группы    
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_resume='all') --если стоит опция получать все открытые, то пользователь получает все открытые заявки в группе	
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms_old($base_id,$Phone_list,$sms_text,$sms_type);
	}
}		
//

echo "
<script>
parent.window.opener.location.reload();
parent.window.close();
//parent.window.location.reload();
</script>
";

//

//Функция обработки ошибок
function my_error_handler($code, $msg, $file, $line) {

global $err_count;
global $c;
$err_count++;
OCIRollback($c);
echo "<font color=red>ОШИБКА! (см. SUP_ERR_LOG)</font><br>";
echo "<font color=red>ОШИБКА! $code - ".(str_replace('\'',' ',$msg))." - ".(str_replace('\'',' ',$file))." - ".(str_replace('\'',' ',$line))."'</font><hr>";
$ins=OCIParse($c,"insert into SUP_ERROR_LOG (datetime,IP_ADDRESS,ACTION_TYPE,ERR_CODE,ERR_MSG,ERR_FILE,ERR_LINE,RESULT)
values (sysdate,'".$_SERVER['REMOTE_ADDR']."','save_order',:err_code,:err_msg,:err_file,:err_line,'part_commit')");
OCIBindByName($ins,":err_code",$code);
OCIBindByName($ins,":err_msg",$msg);
OCIBindByName($ins,":err_file",$file);
OCIBindByName($ins,":err_line",$line);
OCIExecute($ins,OCI_DEFAULT);
OCICommit($c);
}
?>
</body>
</html>
