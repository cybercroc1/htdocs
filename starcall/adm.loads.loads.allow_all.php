<?php 
include("starcall/session.cfg.php");
set_error_handler ("my_error_handler");
set_time_limit(0);
//ignore_user_abort(true);
extract($_POST);

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

include("starcall/conn_string.cfg.php");

//������ ���� ��������� ���������� �������
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

echo "<form method=post name=frm>";
echo "��������� �������<hr>";

$count_base=0;
$count_phones=0;

if(isset($mark)) {
	$common_changed_records=0;
	$common_changed_phones=0;
	foreach($mark as $load_id) {
		$changed_records=0;
		$changed_phones=0;
		$upd=OCIParse($c,"begin STC_DIS_ALLOW_ALL(".$_SESSION['adm']['project']['id'].",".$load_id.",'allow',:changed_records,:changed_phones); end;");
		OCIBindByName($upd,":changed_records",$changed_records,16);
		OCIBindByName($upd,":changed_phones",$changed_phones,16);
		OCIExecute($upd,OCI_DEFAULT);
		
		echo "ID ��������: ".$load_id." �������� �������: ".$changed_records."; ���������: ".$changed_phones."<br>";
		$common_changed_records+=$changed_records;	
		$common_changed_phones+=$changed_phones;		
	}
	echo "�����: �������� �������: ".$common_changed_records."; ���������: ".$common_changed_phones."<br>";
	if($common_changed_records>0) {
		$load_ids=implode(',',$mark);	
		//���������� ���������� ��������
		$q_upd_stat=OCIParse($c,"update STC_LOAD_HISTORY h 
		set 
		h.allow_rows=(select count(*) from STC_BASE where project_id=h.project_id and load_hist_id=h.id and allow='y'),
		h.allow_phones=(select count(*) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id and allow='y')
		where h.project_id='".$_SESSION['adm']['project']['id']."' and h.id in (".$load_ids.")");
		OCIExecute($q_upd_stat,OCI_DEFAULT); //���������� ����������
		echo "��������� ���������� ��������<br>";
		OCICommit($c);		

		//��������� ���������� ����
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
		echo "��������� ���������� � ���������� ����<br>";
	}
}

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

