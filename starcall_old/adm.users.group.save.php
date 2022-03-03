<?php include("../../conf/starcall_conf/session.cfg.php");
extract($_REQUEST);
set_error_handler ("my_error_handler");
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

$error='';
$warning='';
$new_group_id='';

//������
if(isset($cancel)) {
	echo "<script>
	parent.admBottomFrame.admGroupsFrame.location=parent.admBottomFrame.admGroupsFrame.location.href;
	</script>";	
	exit();
}
if (isset($del_group) and $frm_submit<>'continue') {
	$warning.='<font color=red>��������������: ����� ������� ���� ��� �������� �����.</font><br>';
}
if ($warning<>'') {
	echo "<script>
	parent.admBottomFrame.admGroupsFrame.document.getElementById('save_status').innerHTML='".$warning."';
	parent.admBottomFrame.admGroupsFrame.frm.frm_submit.value='continue';
	parent.admBottomFrame.admGroupsFrame.frm.save.value='����������';
	parent.admBottomFrame.admGroupsFrame.frm.cancel.style.display='';
	</script>";
	exit();	
}

//�������� �����
if (isset($del_group)) {
	$del_group_ids=implode(",",$del_group);
	OCIExecute(OCIParse($c,"delete from STC_USER_GRP_PRJ where group_id in (".$del_group_ids.") 
	and ('".$_SESSION['user']['all_users']."'='y' or group_id in (select id from STC_USER_GROUP where creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")))  --�������� ����"),OCI_DEFAULT);
	OCIExecute(OCIParse($c,"delete from STC_USER_GRP_USR where group_id in (".$del_group_ids.")
	and ('".$_SESSION['user']['all_users']."'='y' or group_id in (select id from STC_USER_GROUP where creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")))  --�������� ����"),OCI_DEFAULT);
	OCIExecute(OCIParse($c,"delete from STC_USER_GROUP where id in (".$del_group_ids.")
	and ('".$_SESSION['user']['all_users']."'='y' or id in (select id from STC_USER_GROUP where creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")))  --�������� ����"),OCI_DEFAULT);	
	echo "������� ������<hr>";
}

//���������� �����
if (isset($new_group)) {
	$new_group_id='';
	//�������� ������

	//������� ������
	$ins=OCIParse($c,"insert into STC_USER_GROUP (id,name,creator,create_date,default_group) values (SEQ_STC_GROUP_ID.nextval,:new_name,".$_SESSION['user']['id'].",sysdate,:default_group) returning id into :new_group_id");
	/*//��������� ���� � ������
	if($_SESSION['user']['all_users']<>'y')
	$ins2=OCIParse($c,"insert into STC_USER_GRP_USR (user_id,group_id) values (".$_SESSION['user']['id'].",:new_group_id)");*/
		
	foreach($new_group as $idx => $null) {
	if(trim($new_name[$idx])=='') {
		continue;		
	}
	$q=OCIParse($c,"select count(*) count from STC_USER_GROUP where upper(name)=upper('".$new_name[$idx]."') and creator=".$_SESSION['user']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {
		$error.="<font color=red>������! ������ � ������ \"".$new_name[$idx]."\" ��� ����������</font><br>";
		continue;
	}
		OCIBindByName($ins,":new_name",$new_name[$idx]);
		OCIBindByName($ins,":default_group",$new_default[$idx]);
		OCIBindByName($ins,":new_group_id",$new_group_id,16);
		OCIExecute($ins,OCI_DEFAULT);
		echo "������� ������ \"".$new_name[$idx]."\"<hr>";
		/*if($_SESSION['user']['all_users']<>'y') {
			OCIBindByName($ins2,":new_group_id",$new_group_id);
			OCIExecute($ins2,OCI_DEFAULT);
			echo "�������� ��������� � ������ \"".$new_name."\"<hr>";
		}*/
		
	}
}

//��������� �����
if (isset($ch_group)) {
	//�������� ������
	$q=OCIParse($c,"select count(*) count from STC_USER_GROUP where id<>:group_id and upper(name)=upper(:name) and creator=:creator");
	//��������� ��� ������ ������
	$upd=OCIParse($c,"update STC_USER_GROUP set name=:name, default_group=:default_group where id=:group_id
	and ('".$_SESSION['user']['all_users']."'='y' or id in (select id from STC_USER_GROUP where creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")))  --�������� ����");
		
	foreach($ch_group as $group_id => $null) {
		if(isset($del_group[$group_id])) continue;
		if(trim($name[$group_id])=='') {
			$error.="<font color=red>������! ��� ������ �� ����� ���� ������</font><br>";
			continue;		
		}
		OCIBindByName($q,":group_id",$group_id);
		OCIBindByName($q,":name",$name[$group_id]);
		OCIBindByName($q,":creator",$creator[$group_id]);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"COUNT")>0) {
			$error.="<font color=red>������! ������ � ������ \"".$name[$group_id]."\" ��� ����������</font><br>";
			continue;
		}
		OCIBindByName($upd,":name",$name[$group_id]);
		OCIBindByName($upd,":default_group",$default_group[$group_id]);
		OCIBindByName($upd,":group_id",$group_id);
		OCIExecute($upd,OCI_DEFAULT);
		echo "��������� ������ \"".$name[$group_id]."\"<hr>";
	}	
}

//���� ���� ������
if($error<>'') {
	OCIRollback($c);
	$error="<font color=red>��������� �� ���������!</font><br>".$error;
	echo $error;
	echo "Rollback.<hr>";
	echo "<script>
	parent.admBottomFrame.admGroupsFrame.document.getElementById('save_status').innerHTML='".$error."';
	parent.admBottomFrame.admGroupsFrame.frm.cancel.style.display='';
	</script>";	
	exit();
}
else {
	OCICommit($c);
	if($new_group_id<>'' or isset($del_group)) {
		if($new_group_id<>'') {
			$_SESSION['adm']['groups']['order_by']='g.create_date desc';
		}
		/*echo "<script>parent.admBottomFrame.admUsersFrame.location='adm.users.php';</script>";
		*/
	}
	$_SESSION['adm']['users']['group_id']=$new_group_id;
	echo "Commit.<hr>";
	echo "<script>
	parent.admBottomFrame.admUsersFrame.location='adm.users.php';
	parent.admBottomFrame.admGroupsFrame.location='adm.users.group.php';</script>";	
}

function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<font color=red><br>������: ".$code."; ".$msg."; ".$file."; ".$line."<br></font>";
	echo "<script>parent.admBottomFrame.admGroupsFrame.document.getElementById('save_status').innerHTML='<font color=red>������: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';
	parent.admBottomFrame.admGroupsFrame.frm.cancel.style.display='';
	</script>";
	exit();
}
?>

