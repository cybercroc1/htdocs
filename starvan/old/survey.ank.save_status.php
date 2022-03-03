<?php include("../../conf/starcall_conf/session.cfg.php"); 
set_error_handler ("my_error_handler");

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("../../conf/starcall_conf/conn_string.cfg.php");

if(!isset($perez_date)) $perez_date='';

//���������� �������=====================================
if(isset($set_ank_status) and $set_ank_status<>'') {
	set_ank_status($set_ank_status,$_SESSION['survey']['ank']['base']['id'],$perez_date);
	$_SESSION['survey']['ank']['base']['id']='';
	$_SESSION['survey']['ank']['base']['status']='';
	$_SESSION['survey']['ank']['phone']['id']='';
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
}
//=======================================================

//�������=================================================================================================================================
function set_ank_status($new_base_status,$base_id,$perez_date) {
	global $c;
	$old_base_status='';

	//�������� ������ ������ ������
	$q=OCIParse($c, "select status, src_quote_id, phone_id from STC_BASE where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_base_status=OCIResult($q,"STATUS");
	$src_quote_id=OCIResult($q,"SRC_QUOTE_ID");
	$phone_id=OCIResult($q,"PHONE_ID");
	echo "��������: ������� ������ ������ ������; �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";	
	//
	if($old_base_status<>'inwork' or ($new_base_status<>'end_norm' and $new_base_status<>'end_false' and $new_base_status<>'perez' and $new_base_status<>'end_otkaz' and $new_base_status<>'end_error' and $new_base_status<>'end_quote'))
	{
		echo "������: �� ������ ��������� ������� � $old_base_status �� $new_base_status<br>";
		exit(); 
	}

echo "<hr>��������� ������� ������<br>";
		//��������� ������� ������
		OCIExecute(OCIParse($c,"update STC_BASE set
		status='".$new_base_status."',
		status_date=sysdate, 
		status_user=".$_SESSION['user']['id'].",
		status_type='ank',
		perez_date_msk=decode('".$new_base_status."','perez',to_date('".$perez_date."','YYYYMMDDHH24MISS'),perez_date_msk),
		nedoz_count='',
		nedoz_date=''
		where id=".$base_id." and project_id=".$_SESSION['survey']['project']['id']
		),OCI_DEFAULT);
echo "��������: ���������� ������ ������;  �� ������: $base_id; ������ ������ ������: $old_base_status; ����� ������ ������: $new_base_status.<br>";		

	//������� �� ������ ������������ ������������
	OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']." and base_id=".$base_id)
	,OCI_DEFAULT);

	//====================================


	//���� ����� �������� � ���������� � ������
	if($old_base_status<>$new_base_status) {
		if($old_base_status=='') $minus=",stat_new=stat_new-1";
		else $minus=",stat_".$old_base_status."=stat_".$old_base_status."-1";
		if($new_base_status=='') $plus=",stat_new=stat_new+1";
		else $plus=",stat_".$new_base_status."=stat_".$new_base_status."+1";
		//����� �� �������
		OCIExecute(OCIParse($c,"update STC_PROJECTS set id=id ".$minus.$plus." where id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
		//���������� ��������
		OCIExecute(OCIParse($c,"update STC_SRC_INDEXES i set id=id ".$minus.$plus."
		where (i.field_id,i.value) in (select v.field_id,v.text_value from STC_FIELD_VALUES v where v.project_id=".$_SESSION['survey']['project']['id']." and v.base_id=".$base_id.") and i.project_id=".$_SESSION['survey']['project']['id']),OCI_DEFAULT);
		if($src_quote_id<>'') {
			//���������� �� �������� ������
			OCIExecute(OCIParse($c,"update STC_SRC_QUOTES set id=id ".$minus.$plus." where project_id=".$_SESSION['survey']['project']['id']." and id=".$src_quote_id),OCI_DEFAULT);
		}
echo "����������� ����������: $minus.$plus<br>";	
	}
	//
	
	//���������� ������� �� ����������� �������� ������
	if($new_base_status=='end_norm') {
		//$q=OCIParse($c,"select * from STC_SRC_INDEXES where project_id=".$_SESSION['survey']['project']['id']." and STAT_end_norm>=src_idx_quote");
		//OCIExecute($q,OCI_DEFAULT);
		//if(OCIFetch($q)) {
			//��������� ������, ����� � ������ �� ����������� �������� ������
			OCIExecute(OCIParse($c,"begin STC_SRC_SINGLE_QUOTE_SETLOCK(".$_SESSION['survey']['project']['id']."); end;"));
			echo "��������� ���������� �� �������� ������ (STC_SRC_SINGLE_QUOTE_SETLOCK)<br>";			
		//}
	}
	OCICommit($c);
}
function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<script>parent.callTopFrame.document.location='survey.call.php';</script>";
	echo "<br><font color=red>������: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	exit();
}
?>
