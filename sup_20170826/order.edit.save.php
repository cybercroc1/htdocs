<?php
extract($_REQUEST);
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>������ �� ������������</title>
</head>
<body>
<?php
set_error_handler ("my_error_handler");
//echo session_name()."--".session_id();


if (isset($_SESSION['auth']) and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or $_SESSION['eval']<>'create_new')) {
}
else {
echo "<font color=red><b>������: � ��� ��� ���� ��� ��������� ������ �������� ��� �� �� ������ �����������</b></font>";
exit();
}

include("../../sup_conf/sup_conn_string");

if(isset($save) or isset($close_z) or isset($delay_z)) {
	//����� �������������� 
	if(isset($new_location_id) and isset($old_location_id) and $new_location_id<>$old_location_id and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {
		$result=12;
		$q=OCIParse($c,"select 
		(select name from SUP_KLINIKA where id=".$old_location_id.") old_location_name, 
		(select name from SUP_KLINIKA where id=".$new_location_id.") new_location_name 
		from dual");
		OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
		$tmp_coment=OCIResult($q,"OLD_LOCATION_NAME")." -> ".OCIResult($q,"NEW_LOCATION_NAME");

		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
		OCIBindByName($ins,":coment",$tmp_coment);
		OCIExecute($ins,OCI_DEFAULT);
	
		$upd=OCIParse($c,"update sup_base set 
		klinika_id='".$new_location_id."',
		last_change=sysdate,
		delay_to=''
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}
	//����� ���� �������� 
	if(isset($new_trbl_type_id) and isset($old_trbl_type_id) and $new_trbl_type_id<>$old_trbl_type_id and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {
		$result=13;
		if(!isset($new_trbl_det_id)) $new_trbl_det_id='';
		
		$q=OCIParse($c,"select 
		(select name from SUP_TRBL_TYPE where id=".$old_trbl_type_id.") old_trbl_type_name, 
		(select name from SUP_TRBL_TYPE where id=".$new_trbl_type_id.") new_trbl_type_name 
		from dual");
		OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
		$tmp_coment=OCIResult($q,"OLD_TRBL_TYPE_NAME")." -> ".OCIResult($q,"NEW_TRBL_TYPE_NAME");
				
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
		OCIBindByName($ins,":coment",$tmp_coment);
		OCIExecute($ins,OCI_DEFAULT);
	
		$upd=OCIParse($c,"update sup_base set 
		trbl_type_id='".$new_trbl_type_id."',
		trbl_detail_id='".$new_trbl_det_id."',
		last_change=sysdate,
		delay_to='' 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}	
	//���������� ����������� �������
	if(isset($new_trbl_det_id) and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {
		$upd=OCIParse($c,"update sup_base set 
		trbl_detail_id='".$new_trbl_det_id."',
		last_change=sysdate,
		delay_to='' 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}
	
	//��������, ������ ����
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
		krivie_ruki='".$krivie_ruki."',
		delay_to=''
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//����� ���� ��������
	
	
	//
	
	//������ ������� �� �������� (4)
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
		last_change=sysdate,
		delay_to='' 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	} 
	//
	//������ (7)
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
//********************************
	if(isset($save)) {
	//������� (0,5) 
		if(isset($to_user_id) and  $to_user_id<>'' and $to_user_id<>$_SESSION['user_id'] and $_SESSION['redirect']=='y') {
			if($to_user_id=='group') { //������� � ������ ���������, ����� ������� (0)
				$result=0;
			
				$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
				values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'',sysdate,'".$result."')");
				OCIBindByName($ins,":coment",$tex_comment);
				OCIExecute($ins,OCI_DEFAULT);		
			
				$upd=OCIParse($c,"update sup_base 
				set texnari_id=null, date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=null,
				delay_to='' 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
				OCICommit($c);
				
			}
			else { //������� ����������� ��������� (5)
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
				set texnari_id='".$to_user_id."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate),
				delay_to='' 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
				OCICommit($c);
			}
		}
	//
	//�����������/���������� 
	//���� to_user_id ����, �� ������, �� ������� �� ���������, � ������� ����� ����������� �� ����� ����������� ������������
		//���� to_user_id �� ����������, �� �������� ������������ ������
		//�������� (8)
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
				texnari_id='".$user_id."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate),
				delay_to='' 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
				
				OCICommit($c);
			}
			//���������������� (6)
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
				last_change=sysdate,
				delay_to='' 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
			
				OCICommit($c);
			}

		}
		//����������� (6), ��� ���������� ������, ���� ��� ������ ������ �������� (������� �� ��������)
		else if(($_SESSION['solution']<>'y' or $kto_id==$_SESSION['user_id']) and (!isset($quality) or $quality=='') and $tex_comment<>'') {
			$user_id='';
			$result=6;

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
			
			$upd=OCIParse($c,"update sup_base set 
			last_change=sysdate,
			delay_to='' 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			
			OCICommit($c);
		}
		 	
	//
	}
	//��������
	if(isset($close_z)) {
		if($_SESSION['deny_close']=='y') {
			//������ � �������� (9)
			$result=9;	

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
		
			$upd=OCIParse($c,"update sup_base set 
			texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), ready_to_close=sysdate, date_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate),
			delay_to='' 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
		
			OCICommit($c);
		}
		else {	
			//������ (3)
			$result=3;
			
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
			
			$upd=OCIParse($c,"update sup_base set 
			texnari_id=nvl(texnari_id,'".$_SESSION['user_id']."'), date_close=sysdate, who_close='".$_SESSION['user_id']."', last_change=sysdate, in_work=nvl(in_work,sysdate),
			delay_to='' 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			
			OCICommit($c);
		}
	}	
	//
	//��������� (14)
	if(isset($delay_z)) {
		$result=14;
		
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call, to_who)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."','".$delay_to_date."')");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIExecute($ins,OCI_DEFAULT);		

		$upd=OCIParse($c,"update sup_base set last_change=sysdate, 
		delay_to=to_date('".$delay_to_date."')
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		
		OCICommit($c);
	}
	//

include("func_send.php");
include("func_notifications.php");

if($result==0) {
	//email �������
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	//��� ������ ���, ��� ����� ���������� ���� ���������� ��������� � ���������� �����, � ������, ����� ���� 
	$q=OCIParse($c,"select distinct ste.email,su.fio from sup_base b,sup_trbl_type stt, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
	where 
	b.id=".$base_id."
	and stt.id=b.trbl_type_id 
	and ((slt.location_id=b.klinika_id and slt.trbl_id=b.trbl_type_id) or su.id=b.kto_id)
  
	and sla.lt_group_id=slt.lt_grp_id
	and su.id=sla.user_id

	and su.send='y' and (su.solution='y' or su.redirect='y' or su.look='y') 
	and su.deleted is null
	and su.id<>".$_SESSION['user_id']."
	and ste.valid_date is not null
	and ste.texnari_id=su.id");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������� (�������������� �� ������)', $mail_mess);
	}	
}

if($result==5) {
	//email ������� ����������� ��������� (5)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."','".$to_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_redir='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������������� �� '.$to_user_fio, $mail_mess);
	}
}

if($result==6) {
	//email ���������������� (6)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_coment='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' - �����������', $mail_mess);
	}
}

if($result==8) {
	//email �������� (8)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_prisv='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������� � ������', $mail_mess);
	}
}

if($result==9) {
	//email ������ � �������� (9)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_ready='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������ � ��������', $mail_mess);
	}
}

if($result==3) {
	//email ������ (3)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_close='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������', $mail_mess);
	}
}

if($result==14) {
	//email ������� (14)
	$q=OCIParse($c,"select t.email from SUP_TEXNARI_EMAILS t
	where t.texnari_id='".$_SESSION['user_id']."' and valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $reply_to_email=''; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
	if($i>0) $reply_to_email=implode(',',$eml);
	create_notice($base_id,$result,$tex_comment);
	//echo $mail_mess;
	$q=OCIParse($c,"select distinct e.email,u.fio from SUP_USER u, SUP_TEXNARI_EMAILS e
where u.id in ('".$kto_id."','".$from_user_id."') and u.id<>'".$_SESSION['user_id']."'
and u.email_close='y' and u.deleted is null
and e.texnari_id=u.id and e.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], 'noreply@wilstream.ru', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������� �� '.$delay_to_date, $mail_mess);
	}
}
//

//

//���-����������� � �������
include("../../sup_conf/send_sms.php");	
$sms_text='';
if($result==0) {
	//SMS ������� (0)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q); 

	$sms_text.=$base_id.'-������ ������� (�������������� �� ������).'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".";			

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
	}
}
if($result==5) {
	//SMS ������� ����������� ��������� (5)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q); 

	$sms_text.=$base_id.'-������ �������������� �� �������� '.chr(10);
	$sms_text.=$to_user_fio.".".chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".";			

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
	}
}
if($result==8) {
	//SMS �������� (8)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-������ ������� � ������.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);	
	$sms_text.=$_SESSION['fio'].".";

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
	}
}

if($result==9) {
	//SMS ������ � �������� (9)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-������ ������ � ��������.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".";

	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
	}
}

if($result==3) { //�������
	//SMS ������ (3)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-������ �������.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".";
	
	$q=OCIParse($c,"select k.mobile_phone from SUP_BASE b, sup_klinika k
	where k.id=b.klinika_id
	and b.id='".$base_id."' and  k.mobile_phone is not null");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"MOBILE_PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
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
}
//

//������� ��������� ������
function my_error_handler($code, $msg, $file, $line) {

global $err_count;
global $c;
$err_count++;
OCIRollback($c);
echo "<font color=red>������! (��. SUP_ERR_LOG)</font><br>";
echo "<font color=red>������! $code - ".(str_replace('\'',' ',$msg))." - ".(str_replace('\'',' ',$file))." - ".(str_replace('\'',' ',$line))."'</font><hr>";
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
