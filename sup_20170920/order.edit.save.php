<?php
extract($_REQUEST);
session_start();
$sid=session_id();
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

if (!isset($_SESSION['auth'])) {
	echo "<font color=red><b>������: � ��� ��� ���� ��� ��������� ������ ��������. �� �� ������ �����������</b></font>";
	exit();
}
if (!isset($base_id) or $base_id=='') {exit();}

include("sup/sup_conn_string");

$old_last_change=$last_change;

//���������� � ������
include("order.get.order.info.php");
extract(get_order_info($c,$base_id));
if(isset($error)) {echo $error; exit();}

if($last_change<>$old_last_change) {
	echo "<script>alert('������! ������ �������������� ������ ��������������. �������� ��������!')</script>";
	exit();
}

$hist_id='';

if(isset($save) or isset($close_z) or isset($delay_z)) {
	//����� �������������� 
	if(isset($new_location_id) and $new_location_id<>$location_id and ($solution=='y' or $redirect=='y')) {
		$result=12;
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
		last_change=sysdate,
		delay_to=''
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}
	//����� ���� �������� 
	if(isset($new_trbl_type_id) and $new_trbl_type_id<>$trbl_id and ($solution=='y' or $redirect=='y')) {
		$result=13;
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
		last_change=sysdate,
		delay_to='' 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}	
	//���������� ����������� �������
	if(isset($new_trbl_det_id) and ($solution=='y' or $redirect=='y')) {
		$upd=OCIParse($c,"update sup_base set 
		trbl_detail_id='".$new_trbl_det_id."',
		last_change=sysdate,
		delay_to='' 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);			
	}
	
	//��������, ������ ����
	if(isset($new_dublikat) or isset($new_krivie_ruki)) {
		if($new_dublikat=='y' and $new_dublikat<>$dublikat) {
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'10') returning id into :hist_id");
			OCIBindByName($ins,":hist_id",$hist_id,16);
			OCIExecute($ins,OCI_DEFAULT);
		}
		if($new_krivie_ruki=='y' and $new_krivie_ruki<>$krivie_ruki) {
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',sysdate,'11') returning id into :hist_id");
			OCIBindByName($ins,":hist_id",$hist_id,16);
			OCIExecute($ins,OCI_DEFAULT);
		}		
		$upd=OCIParse($c,"update sup_base set 
		dublikat='".$new_dublikat."',
		krivie_ruki='".$new_krivie_ruki."',
		delay_to=''
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//����� ���� ��������
	
	
	//
	
	//
	//������ (7)
	if($eval=='y' and isset($new_quality) and $new_quality<>'') {
		$result=7;
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
	//������� (0,5) 
		if($redirect=='y' and isset($to_user_id) and $to_user_id<>'' and $to_user_id<>'coment' and $to_user_id<>$_SESSION['user_id']) {
			if($to_user_id=='group') { //������� � ������ ���������, ����� ������� (0)
				$result=0;
			
				$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
				values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'',sysdate,'".$result."') returning id into :hist_id");
				OCIBindByName($ins,":coment",$tex_comment);
				OCIBindByName($ins,":hist_id",$hist_id,16);
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
				values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,:to_user_fio,sysdate,'".$result."') returning id into :hist_id");
				OCIBindByName($ins,":coment",$tex_comment);
				OCIBindByName($ins,":to_user_fio",$to_user_fio);
				OCIBindByName($ins,":hist_id",$hist_id,16);
				OCIExecute($ins,OCI_DEFAULT);		
			
				$upd=OCIParse($c,"update sup_base 
				set texnari_id='".$to_user_id."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate),
				delay_to='' 
				where id='".$base_id."'");
				OCIExecute($upd,OCI_DEFAULT);
				OCICommit($c);
			}
		}
		//�������� (8)
		else if($solution=='y' and $from_user_id<>$_SESSION['user_id'] and (!isset($to_user_id) or $to_user_id<>'coment') and (!isset($quality) or $quality=='')) {
			$result=8;
				
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":hist_id",$hist_id,16);
			OCIExecute($ins,OCI_DEFAULT);
				
			$upd=OCIParse($c,"update sup_base set 
			texnari_id='".$_SESSION['user_id']."', date_close=null, ready_to_close=null, who_close=null, last_change=sysdate, in_work=nvl(in_work,sysdate),
			delay_to='' 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
				
			OCICommit($c);			
		}
		//���������������� (6)
		else if((!isset($quality) or $quality=='') and $tex_comment<>'') {
			$result=6;

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":hist_id",$hist_id,16);
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
	//
	}
	//��������
	if(isset($close_z)) {
		if($deny_close=='y') {
			//������ � �������� (9)
			$result=9;	

			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":hist_id",$hist_id,16);
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
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."') returning id into :hist_id");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":hist_id",$hist_id,16);
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
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."','".$delay_to_date."') returning id into :hist_id");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIBindByName($ins,":hist_id",$hist_id,16);
		OCIExecute($ins,OCI_DEFAULT);		

		$upd=OCIParse($c,"update sup_base set last_change=sysdate, 
		delay_to=to_date('".$delay_to_date."')
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		
		OCICommit($c);
	}
	//
	
//���������� ������
$upd=OCIParse($c,"update sup_files set tmp='', hist_id='".$hist_id."' where base_id='".$base_id."' and sess_id='".$sid."' and tmp='y'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);	

echo "update sup_files set tmp='', hist_id='".$hist_id."' where base_id='".$base_id."' and sess_id='".$sid."' and tmp='y'";

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
	//2.1.3.1.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --���� ������ � ����� �� ����� ������������ ������ �������� ���� ������, �� ���� �������� ���������� �� ������    
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_new='all') --���� ����� ����� �������� ��� ��������, �� ������������ �������� ��� �������� ������ � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������� (�������������� �� ������)', $mail_mess);
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
	//2.1.3.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_redir in ('my','all') and su.id in (b.kto_id,b.texnari_id,'".$from_user_id."')) --������, ������ �����������, ����������� �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_redir='all') --��� ���������������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������������� �� '.$to_user_fio, $mail_mess);
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
	//2.1.2.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_coment in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_coment='all') --��� ������������������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' - �����������', $mail_mess);
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
	//2.1.4.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_prisv in ('my','all') and su.id in (b.kto_id,'".$from_user_id."')) --������, ����������� �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_prisv='all') --��� ����������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������� � ������', $mail_mess);
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
	//2.1.5.2. 
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_ready in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_ready='all') --��� ������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' ������ � ��������', $mail_mess);
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
	//2.1.7.2.
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_close in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_close='all') --��� �������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������', $mail_mess);
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
	//2.1.6.2. 
	$q=OCIParse($c,"select distinct ste.email, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
    where b.id=".$base_id."
    and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.em_delay in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.em_delay='all') --��� ���������� � ������
    )
    and su.id=sla.user_id
    and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
    and su.deleted is null
    and ste.texnari_id=su.id
    and ste.valid_date is not null");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		send('', OCIResult($q,"FIO"), OCIResult($q,"EMAIL"), $_SESSION['fio'], '', $_SESSION['fio'], $reply_to_email ,'������ �'.$base_id.' �������� �� '.$delay_to_date, $mail_mess);
	}
}
//

//���-�����������
include("sup/send_sms.php");	
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
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;			

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
	and (
       (sla.sm_new in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --���� ������ � ����� �� ����� ������������ ������ �������� ���� ������, �� ���� �������� ���������� �� ������    
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_new='all') --���� ����� ����� �������� ��� ��������, �� ������������ �������� ��� �������� ������ � ������
    )
    and su.id=sla.user_id	
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
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
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;			

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_redir in ('my','all') and su.id in (b.kto_id,b.texnari_id,'".$from_user_id."')) --������, ������ �����������, ����������� �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_redir='all') --��� ���������������� � ������
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
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
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_prisv in ('my','all') and su.id in (b.kto_id,'".$from_user_id."')) --������, ����������� �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_prisv='all') --��� ����������� � ������
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
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
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;

	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_ready in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_ready='all') --��� ������� � ������
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
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
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_close in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_close='all') --��� �������� � ������
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
		send_sms($base_id,$Phone_list,$sms_text,$sms_type);
	}
}		
//

if($result==14) { //��������
	//SMS ������� (14)
	$sms_type='status';
	$q=OCIParse($c,"select tt.name trbl_name, k.name location_name from SUP_BASE b, SUP_TRBL_TYPE tt, sup_klinika k
	where b.id=".$base_id." and tt.id=b.trbl_type_id and k.id=b.klinika_id");
	OCIExecute($q); OCIFetch($q);

	$sms_text.=$base_id.'-������ �������� �� '.$delay_to_date.'.'.chr(10);
	$sms_text.=OCIResult($q,"LOCATION_NAME").".".chr(10);
	$sms_text.=OCIResult($q,"TRBL_NAME").".".chr(10);
	$sms_text.=$_SESSION['fio'].".".chr(10);
	$sms_text.="http://sup.wilstream.ru/?ticketId=".$base_id;
	
	$q=OCIParse($c,"select distinct stp.phone, su.fio from sup_base b, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where b.id=".$base_id."
	and slt.trbl_id=b.trbl_type_id and slt.location_id=b.klinika_id 
    and (
       (sla.sm_delay in ('my','all') and su.id in (b.kto_id,b.texnari_id)) --������, �����������, ���������� �� �����   
    or (sla.lt_group_id=slt.lt_grp_id and sla.sm_delay='all') --��� ���������� � ������
    )
    and su.id=sla.user_id
	and su.id<>nvl('".$_SESSION['user_id']."','0') --2.a
	and su.deleted is null
	and stp.texnari_id=su.id
	and stp.sms='y' and stp.valid_date is not null and stp.type='mob'");
	OCIExecute($q);
	while (OCIFetch($q)) {
		$Phone_list=OCIResult($q,"PHONE");
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
