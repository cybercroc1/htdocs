<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_error_handler ("my_error_handler");
set_time_limit(0);
ignore_user_abort(true);
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

if(!isset($mark)) {
	echo "<font color=red>������ �� �������</font>";
	//������������ ��� �������� �����
	echo "<script>
		parent.admBottomFrame.document.getElementById('status_div').innerHTML='';
		with(parent.admBottomFrame.frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=false;
			}
		}
	</script>";
	exit();
}


include("../../conf/starcall_conf/conn_string.cfg.php");

//�������� ������ ������ ������
$q=OCIParse($c,"select p.status from STC_PROJECTS p where p.id=".$_SESSION['adm']['project']['id']);
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$old_project_status=OCIResult($q,"STATUS");

//������ ���� ��������� ���������� ������� � ���������������� ������
OCIExecute(OCIParse($c,"update STC_PROJECTS p set p.last_activity=sysdate, p.status='�������������' where p.id=".$_SESSION['adm']['project']['id']));
OCICommit($c);
echo "������ �������������<br>";


$load_ids=implode(',',$mark);

//$commit_interval=100; //���������� ����� � ����� ����������
//$del_count=0; 

//$del=OCIParse($c,"delete from STC_BASE
//where load_hist_id=:load_id and project_id='".$_SESSION['adm']['project']['id']."' and status is null and rownum<='".$commit_interval."'");

$common_deleted_rows=0;
foreach($mark as $val) {

	$deleted_rows=0;
	$del=OCIParse($c,"begin STC_DEL_NEW_LOAD_RECORDS(".$_SESSION['adm']['project']['id'].",".$val.",:deleted_rows); end;");
	OCIBindByName($del,":deleted_rows",$deleted_rows,16);
	OCIExecute($del,OCI_DEFAULT);
		
	echo "ID ��������: ".$val." ������� �������: ".$deleted_rows."<br>";
	$common_deleted_rows+=$deleted_rows;

	//if($deleted_rows>0) {
		//��������� ���-�� ���������� ������� � ���� �� 0, �� ������� �������� �� �������
		$q=OCIParse($c,"select count(*) cnt from STC_BASE t where t.load_hist_id='".$val."' and t.project_id='".$_SESSION['adm']['project']['id']."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"CNT")==0) {
			//������� ������ �� ������� ��������
			$del_hist=OCIParse($c,"delete from STC_LOAD_HISTORY where id='".$val."'");
			OCIExecute($del_hist,OCI_DEFAULT);
			OCICommit($c);
		}
		else {
			$upd_hist=OCIParse($c,"update STC_LOAD_HISTORY set status='�������',del_rows=nvl(del_rows,0)+".$deleted_rows." where id='".$val."'");
			OCIExecute($upd_hist,OCI_DEFAULT);
			OCICommit($c);			
		}
	//}
}	

//if($common_deleted_rows>0) {
	//��������� ���������� ��������
	$upd=OCIParse($c,"update STC_LOAD_HISTORY h 
	set 
	(h.load_rows,h.allow_rows)=(select count(*),count(allow) from STC_BASE where project_id=h.project_id and load_hist_id=h.id),
	(h.load_phones,h.allow_phones)=(select count(*),count(allow) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id)
	where h.id in ($load_ids) and project_id='".$_SESSION['adm']['project']['id']."'");
	OCIExecute($upd); 
	echo "��������� ���������� ��������<br>";
	OCICommit($c);
//}
echo "<hr>����� �������: ".$common_deleted_rows."<hr>";
if($common_deleted_rows>0) {
	//��������� ���������� ����
	OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
	echo "��������� ���������� � ���������� �������� � ����<br>";
}

//���������� ������� ��������� ������
OCIExecute(OCIParse($c," update STC_PROJECTS p set p.status='".$old_project_status."',p.status_date=sysdate where p.id=".$_SESSION['adm']['project']['id']));
OCICommit($c);
echo "������� ��������� ��������� ������ ($old_project_status)<br>";

echo "<script>parent.admBottomFrame.document.getElementById('status_div').innerHTML=''</script>";
echo "<script>parent.admBottomFrame.location=parent.admBottomFrame.location.href;</script>";
//������������ ��� �������� �����
echo "<script>
	with(parent.admBottomFrame.frm) {
		for(i=0; i<elements.length; i++) {
			elements[i].disabled=false;
		}
	}
</script>";

function my_error_handler($code, $msg, $file, $line) {
	echo "<font color=red><hr>������: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	echo "<script>parent.admBottomFrame.document.getElementById('status_div').innerHTML='<font color=red>������: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';
	</script>";
	exit();
}
?>