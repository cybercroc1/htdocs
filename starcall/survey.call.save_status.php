<?php include("starcall/session.cfg.php"); 
set_error_handler ("my_error_handler");

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("starcall/conn_string.cfg.php");


if($set_status<>'perez') {
	$perez_phone='';
	$perez_ext='';
	$perez_min='';
	$perez_date='';	
}

//���������� �������=====================================
if(isset($set_status) and $set_status<>'') {
	unset($_SESSION['nedoz_lock']); //������� ���������� ���������� ���������
	$result=set_call_status($set_status,$_SESSION['survey']['ank']['base']['id'],$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date);
	if($result['action']=='next_phone') {
		$_SESSION['survey']['ank']['phone']['id']='';
		$_SESSION['survey']['ank']['phone']['status']='';
	}
	elseif($result['action']=='next_base') {
		$_SESSION['survey']['ank']['base']['id']='';
		$_SESSION['survey']['ank']['base']['status']='';	
		$_SESSION['survey']['ank']['phone']['id']='';
		$_SESSION['survey']['ank']['phone']['status']='';
	}
	else {
		$_SESSION['survey']['ank']['base']['id']=$result['base_id'];
		$_SESSION['survey']['ank']['base']['status']=$result['new_base_status'];
		$_SESSION['survey']['ank']['phone']['id']=$result['phone_id'];
		$_SESSION['survey']['ank']['phone']['status']=$result['phone_status'];
	}
	if($result['new_base_status']=='inwork' and $_SESSION['survey']['ank']['base']['id']<>'') {
		//��������� � ������ ������������ ������������
		OCIExecute(OCIParse($c,"insert into STC_USER_INWORK (user_id,project_id,base_id) values (".$_SESSION['user']['id'].",".$_SESSION['survey']['project']['id'].",".$_SESSION['survey']['ank']['base']['id'].")")
		,OCI_DEFAULT);
echo "������ ��������� � ������ ������������� ������������<br>";		
	}
	else {
		//������� �� ������ ������������ ������������
		if($_SESSION['survey']['ank']['base']['id']<>'') {
		OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']." and base_id=".$_SESSION['survey']['ank']['base']['id'])
		,OCI_DEFAULT);
echo "������ ������� �� ������ ������������� ������������<br>";
		}		
	}
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	OCICommit($c);
}
//=======================================================

//�������=================================================================================================================================
function set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date) {
	echo 'set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<br>';
	echo "set_call_status($new_phone_status,$base_id,$phone_id,$perez_phone,$perez_ext,$perez_min,$perez_date)<hr>";
	global $c;
	$old_base_status='';
	$new_base_status='';
	$min_nedoz_count='';
	$min_nedoz_date='';
	$src_quote_id='';
echo "��������: ��������� ������� ��������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
	//������ ���� ��������� ���������� �������
	OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "��������� ���� ���������� �������<br>";

	//���� ��� base_id
	if($base_id=='') {
echo "��� base_id<br>";	
		//������� ������
		$ins=OCIParse($c, "insert into STC_BASE (id,project_id,allow,lock_user,lock_date) 
		values (seq_stc_base_id.nextval, ".$_SESSION['survey']['project']['id'].",'y',".$_SESSION['user']['id'].",sysdate)
		returning id into :base_id");
		OCIBindByName($ins,":base_id",$base_id,16);
		OCIExecute($ins,OCI_DEFAULT);
echo "��������: ������� ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
		//��������� � ���������� �� �������
		OCIExecute(OCIParse($c,"update STC_PROJECTS set stat_new=stat_new+1 where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);	
echo "��������: ��������� ���������� �������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
	}
/*	//���� ��� ��������, �� ������� �������� �������
	if($phone_id=='' and $phone['num']<>'') {
		//������� �������
		$ins=OCIParse($c, "insert into STC_PHONES (id, base_id, project_id, phone, ord, allow)
		values (SEQ_STC_PHONE_ID.nextval,".$base_id.", ".$_SESSION['survey']['project']['id'].",
		substr(".$phone['num'].",0,25),nvl((select max(ord) from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id."),1),'y') 
		returning (phone,id) into (:phone,:phone_id)");
		OCIBindByName($ins,":phone",$phone['num'],16);
		OCIBindByName($ins,":phone_id",$phone_id,16);		
echo "��������: �������� �������; �������: $phone[num]; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";	
	}
*/
	//�������� ������ ������ ������
	$q=OCIParse($c, "select status, src_quote_id, utc_msk from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_base_status=OCIResult($q,"STATUS");
	$src_quote_id=OCIResult($q,"SRC_QUOTE_ID");
	$utc_msk=OCIResult($q,"UTC_MSK");
echo "��������: ������� ������ ������ ������; utc_msk: $utc_msk; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";	
	//

	//��������� ��������================================================================================================================================
	//������� ���������+++++++++++++++++++++++++++
	if($phone_id<>'') {	
		//inwork,otkaz, error
		if($new_phone_status=='inwork' or $new_phone_status=='otkaz' or $new_phone_status=='error') {
			//�������
			OCIExecute(OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id)
			,OCI_DEFAULT);
echo "��������: ���������� ������ ��������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		
		}
		//
		//perez
		if($new_phone_status=='perez') {
			//�������
			if($perez_phone<>'') {
				//���� ����� ��������� �� ������
				$q=OCIParse($c,"select id from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." and phone='".$perez_phone."' and nvl(ext,0)=nvl('".$perez_ext."',0)");
				OCIExecute($q,OCI_DEFAULT);
				if(OCIFetch($q)) {
					echo "����� �������� � ���������� �� ���������<br>";
				}
				else {
					//������ ������� �������� ������ end_perez
					OCIExecute(OCIParse($c,"update STC_PHONES set 
					status='end_perez', 
					status_date=sysdate,
					nedoz_count=''
					where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id),OCI_DEFAULT);
					echo "��������� ���. ��� ���. ������� �������� (ID: $phone_id) ���������� ������ end_perez<br>";
					//��������� ����� �������, ���������� ��� ID, ���� ��� ����������� ������ � ���� ���������
					$ins=OCIParse($c, "insert into STC_PHONES (id, base_id, project_id, phone, ext, ord, allow)
					values (SEQ_STC_PHONE_ID.nextval,".$base_id.", ".$_SESSION['survey']['project']['id'].",
					'".$perez_phone."','".$perez_ext."',nvl((select max(ord) from STC_PHONES where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id."),1),'y') 
					returning id into :phone_id");
					OCIBindByName($ins,":phone_id",$phone_id,16);
					OCIExecute($ins, OCI_DEFAULT);
					echo "�������� ����� �������: ID: $phone_id; phone_num: $perez_phone; ext: $perez_ext<br>";	
				}
			}
			else echo "����� �������� � ���������� �� ���������<br>";
			
			$upd=OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			perez_date_msk=decode('".$perez_min."',NULL, decode('".$perez_date."',NULL,sysdate,to_date('".$perez_date."','DD.MM.YYYY HH24:MI')-nvl('".$utc_msk."',0)/24) ,sysdate+nvl('".$perez_min."',0)/1440),
			nedoz_count=''
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id." returning to_char(perez_date_msk,'DD.MM.YYYY HH24:MI:SS') into :perez_date_msk");
			
			OCIBindByName($upd,":perez_date_msk",$perez_date,16);
			OCIExecute($upd,OCI_DEFAULT);	
echo "��������: ���������� ������ ��������; ���� ��������� (���): $perez_date; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		
		}
		//
		//nedoz
		if($new_phone_status=='nedoz') {
			//�������
			OCIExecute(OCIParse($c,"update STC_PHONES set 
			status='".$new_phone_status."', status_date=sysdate,
			nedoz_count=nvl(nedoz_count,0)+1
			where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and id=".$phone_id)
			,OCI_DEFAULT);	
echo "��������: ���������� ������ ��������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		
		}
	}
	//++++++++++++++++++++++++++++++
echo "<hr>��������� ������� ������<br>";
	//������� �������==============================
	//inwork
	if($new_phone_status=='inwork') {
		OCIExecute(OCIParse($c,"update STC_BASE set
		start_date=sysdate, status_date=sysdate,status='".$new_phone_status."',lock_user='',
		phone_id='".$phone_id."',lock_date='', status_user=".$_SESSION['user']['id'].",
		status_type='call'
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id'])
		,OCI_DEFAULT);
		$new_base_status=$new_phone_status;
echo "��������: ���������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
	}
	//
	else {
		//������ �������� ��������� ��� ��������� ������ ������� ������=================
echo "������ �������� ��������� ��� ��������� ������ ������� ������<br>";		
		$q=OCIParse($c,"select count(*) count_phones, 
		count(perez) count_perez, 
		count(end_perez) count_end_perez, 
		to_char(min(perez_date),'YYYYMMDDHH24MISS') min_perez_date,
		count(nedoz) count_nedoz,
		min(nedoz_count) min_nedoz_count,
		count(otkaz) count_otkaz, count(error) count_error
		from (select 
		decode(p1.status,'perez',1,NULL) perez,
		decode(p1.status,'end_perez',1,NULL) end_perez,
		decode(p1.status,'perez',p1.perez_date_msk,NULL) perez_date,
		decode(p1.status,'nedoz',1,NULL) nedoz,
		decode(p1.status,'nedoz',p1.nedoz_count,NULL) nedoz_count,
		decode(p1.status,'otkaz',1,NULL) otkaz, 
		decode(p1.status,'error',1,NULL) error
		from STC_PHONES p1
		where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and allow='y')");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"COUNT_PHONES")>0) {
			$min_nedoz_count='';
			$min_nedoz_date='';
			//perez
			if(OCIResult($q,"COUNT_PEREZ")>0) {
				$new_base_status='perez';
				$perez_date=OCIResult($q,"MIN_PEREZ_DATE");
echo "��������: (1) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status; ���� ���������: $perez_date.<br>";
			}
			//nedoz
			elseif (OCIResult($q,"COUNT_NEDOZ")>0 and OCIResult($q,"COUNT_NEDOZ")+OCIResult($q,"COUNT_END_PEREZ")+OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")>=OCIResult($q,"COUNT_PHONES")) {
				$min_nedoz_count=OCIResult($q,"MIN_NEDOZ_COUNT");
				//����������� ���� ��������� �� ��������� � ����������� ����������� ����������
				$q1=OCIParse($c,"select to_char(min(status_date),'YYYYMMDDHH24MISS') min_nedoz_date from STC_PHONES 
				where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id." and allow='y' and status='nedoz' and nvl(nedoz_count,0)=nvl('".OCIResult($q,"MIN_NEDOZ_COUNT")."',0)");				
				OCIExecute($q1,OCI_DEFAULT);
				OCIFetch($q1);
				$min_nedoz_date=OCIResult($q1,"MIN_NEDOZ_DATE");
				$q_prj=OCIParse($c,"select nedoz_count from STC_PROJECTS where id=".$_SESSION['survey']['project']['id']);
				OCIExecute($q_prj, OCI_DEFAULT);
				OCIFetch($q_prj);
				if($min_nedoz_count>=OCIResult($q_prj,"NEDOZ_COUNT")) $new_base_status='end_nedoz';
				else $new_base_status='nedoz';
echo "��������: (2) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status; ���-�� ����������: $min_nedoz_count; ���� ���������� ���������: $min_nedoz_date.<br>";
			}
			//otkaz
			elseif(OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")+OCIResult($q,"COUNT_END_PEREZ")>=OCIResult($q,"COUNT_PHONES") and $new_phone_status=='otkaz') {
				$new_base_status='end_otkaz';
echo "��������: (3) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
			}
			//
			//error
			elseif(OCIResult($q,"COUNT_OTKAZ")+OCIResult($q,"COUNT_ERROR")+OCIResult($q,"COUNT_END_PEREZ")>=OCIResult($q,"COUNT_PHONES") and $new_phone_status=='error') {
				$new_base_status='end_error';
echo "��������: (4) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";
			}
			//
			else { 
			$new_base_status='';
echo "��������: (5) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";	
			}		
		}
		else {
			//���� ��� �������, �� �������� ����������� ������
			$new_base_status=str_replace(array('error','otkaz'),array('end_error','end_otkaz'),$new_phone_status);	
echo "��������: (6) ��������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		
		}
		//��������� ������� ������
		OCIExecute(OCIParse($c,"update STC_BASE set
		lock_date='',
		lock_user='',
		phone_id='".$phone_id."',
		status='".$new_base_status."',
		status_date=sysdate, 
		status_user=".$_SESSION['user']['id'].",
		status_type='call',
		perez_date_msk=decode('".$new_base_status."','perez',to_date('".$perez_date."','YYYYMMDDHH24MISS'),perez_date_msk),
		nedoz_count=decode('".$new_base_status."','nedoz','".$min_nedoz_count."','end_nedoz','".$min_nedoz_count."',NULL),
		nedoz_date=decode('".$new_base_status."','nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),'end_nedoz',to_date('".$min_nedoz_date."','YYYYMMDDHH24MISS'),NULL)
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
		),OCI_DEFAULT);
echo "��������: ���������� ������ ������; ID ��������: $phone_id; ����� ������ ��������: $new_phone_status; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		
	}

	//====================================


	//���� ����� �������� � ���������� � ������
	if($old_base_status<>$new_base_status) {
		if($old_base_status=='') $minus=",stat_new=stat_new-1";
		else $minus=",stat_".$old_base_status."=stat_".$old_base_status."-1";
		if($new_base_status=='') $plus=",stat_new=stat_new+1";
		else $plus=",stat_".$new_base_status."=stat_".$new_base_status."+1";
		//����� �� �������
		OCIExecute(OCIParse($c,"update STC_PROJECTS set id=id ".$minus.$plus." where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "����������� ���������� �� �������: $minus.$plus<br>";
		//���������� ��������
		OCIExecute(OCIParse($c,"update STC_SRC_INDEXES i set id=id ".$minus.$plus."
		where (i.field_id,i.value) in (select v.field_id,v.text_value from STC_FIELD_VALUES v where v.project_id=".$_SESSION['survey']['project']['id']." and v.base_id=".$base_id.") and i.project_id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
echo "����������� ���������� �� �������� ��������: $minus.$plus<br>";
		if($src_quote_id<>'') {
			//���������� �� �������� ������
			OCIExecute(OCIParse($c,"update STC_SRC_QUOTES set id=id ".$minus.$plus." where project_id=".$_SESSION['survey']['project']['id']." and id=".$src_quote_id),OCI_DEFAULT);
echo "����������� ���������� �� �������� �����: ID: $src_quote_id; $minus.$plus<br>";
		}
	}
	//

	if($new_base_status=='inwork') $result['action']='start_ank';
	elseif($old_base_status=='perez' and $new_base_status=='') $result['action']='next_base'; 
	elseif($new_base_status=='') $result['action']='next_phone';
	else $result['action']='next_base';
	
	$result['base_id']=$base_id;
	$result['new_base_status']=$new_base_status;
	$result['phone_id']=$phone_id;
	$result['phone_status']=$new_phone_status;
	OCICommit($c);
echo "��������� ��������� �������: action: $result[action]; base_id: $base_id; new_base_status: $new_base_status; phone id: ".$result['phone_id'].".";	
	return $result;

}
function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	echo "<br><font color=red>������: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	exit();
}
?>
