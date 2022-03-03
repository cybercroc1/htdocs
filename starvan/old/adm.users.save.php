<?php include("../../conf/starcall_conf/session.cfg.php");
extract($_REQUEST);
set_error_handler ("my_error_handler");
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

$error='';
$warning='';
$new_user_id='';

//������
if(isset($cancel)) {
	echo "<script>
	parent.admBottomFrame.admUsersFrame.location=parent.admBottomFrame.admUsersFrame.location.href;
	</script>";	
	exit();
}
if (isset($del_user) and $frm_submit<>'continue') {
	$warning.='<font color=red>��������������: ����� ������� ���� ��� ��������� �������������.</font><br>';
}
if ($warning<>'') {
	echo "<script>
	parent.admBottomFrame.admUsersFrame.document.getElementById('save_status').innerHTML='".$warning."';
	parent.admBottomFrame.admUsersFrame.frm.frm_submit.value='continue';
	parent.admBottomFrame.admUsersFrame.frm.save.value='����������';
	parent.admBottomFrame.admUsersFrame.frm.cancel.style.display='';
	</script>";
	exit();	
}
//=====================================

//������� �������������
if(isset($del_user)) {
	$del_user_ids=implode(",",$del_user);
	//�������� ������������ 
	OCIExecute(OCIParse($c,"update STC_USERS set deleted=sysdate where id in (".$del_user_ids.")
	and ('".$_SESSION['user']['all_users']."'='y' or id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")) --�������� ����
	"),OCI_DEFAULT);
	//������ ������� LOST_CREATOR ����� ��������� �������������
	OCIExecute(OCIParse($c,"update STC_USERS set lost_creator='y' where id in (select id from STC_USERS where creator in (".$del_user_ids.") 
	and ('".$_SESSION['user']['all_users']."'='y' or id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))) --�������� ����
	"),OCI_DEFAULT);	
	//������� ������� �������� ������������
	OCIExecute(OCIParse($c,"delete from STC_USER_INWORK where user_id=".$_SESSION['user']['id']
	),OCI_DEFAULT);		
	//�������� ������������ �� �����
	OCIExecute(OCIParse($c,"delete from STC_USER_GRP_USR where user_id in (".$del_user_ids.")
	and ('".$_SESSION['user']['all_users']."'='y' or user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")) --�������� ����
	"),OCI_DEFAULT);
	echo "������� ������������<hr>";	
}
//

//�������� ������������
if (isset($new_user)) {
	foreach($new_user as $idx => $null) {
		$new_user_id='';
		$new_login[$idx]=trim($new_login[$idx]);
		
		//�������� ������
		if($_SESSION['user']['rw_users']<>'w' and $new_role[$idx]<>'operator') {$error.="<font color=red>������! � ��� ��� ���� ��������� ������������ � ����� \"".$new_role[$idx]."\".</font><br>";}
		if($_SESSION['user']['all_users']<>'y') {
			$q=OCIParse($c,"select count(*) count from STC_LI_ROLES t where t.all_users is null and t.id='".$new_role[$idx]."' and t.role_level<=".$_SESSION['user']['role_level']);
			OCIExecute($q,OCI_DEFAULT);
			OCIFetch($q);
			if (OCIResult($q,"COUNT")==0) {
				$error.="<font color=red>������! � ��� ��� ���� ��������� ������������ � ����� \"".$new_role[$idx]."\".</font><br>";
				continue;
			}		
		}
		if($new_fio[$idx]=='' or $new_login[$idx]=='' or $new_pass[$idx]=='') {
			$error.="<font color=red>������! ���, �����, ������ �� ������ ���� �������.</font><br>";
			continue;
		}
		if($new_role[$idx]<>'operator' and strlen($new_pass[$idx])<6) {
			$error.="<font color=red>������! ������ ������ ������������, ����� ��������� �� ������ ���� ������ 6 ��������.</font><br>";
			continue;
		}
		$q=OCIParse($c,"select count(*) count from STC_USERS where upper(login)=upper('".$new_login[$idx]."') and deleted is null and (pass='".$new_pass[$idx]."' or creator=".$_SESSION['user']['id'].")");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"COUNT")>0) {
			$error.="<font color=red>������! ������������ \"".$new_login[$idx]."\" ��� ����������</font><br>";
			continue;
		}
		//��������� ������������
		$ins=OCIParse($c,"insert into STC_USERS (id,login,pass,create_date,fio,role_id,creator) 
		values (SEQ_STC_USER_ID.nextval,'".$new_login[$idx]."','".$new_pass[$idx]."',sysdate,'".$new_fio[$idx]."','".$new_role[$idx]."',".$_SESSION['user']['id'].") returning id into :new_user_id");
		OCIBindByName($ins,':new_user_id',$new_user_id,16);
		OCIExecute($ins,OCI_DEFAULT);
		echo "�������� ������������ \"".$new_fio[$idx]."\" id: ".$new_user_id."<hr>";
		//��������� � ������ �� ���������, � ������� �� ������� � � ��������� ������
		$ins=OCIParse($c,"insert into STC_USER_GRP_USR (User_Id,GROUP_ID)
		select distinct ".$new_user_id.", ug.group_id from STC_USER_GRP_USR ug, STC_USER_GROUP g where g.id=ug.group_id and ((g.default_group='y' and ug.user_id=".$_SESSION['user']['id'].") or ug.group_id='".$group_id."')");
		
		OCIExecute($ins,OCI_DEFAULT);		
		//��������� ������������ � ������� �����������
		if($new_role<>'operator') {
			//��������� ���������� ������ ����
			$ins=OCIParse($c,"insert into STC_USER_CHILD (USER_ID,CHILD_USER_ID) values (".$new_user_id.",".$new_user_id.")");
			OCIExecute($ins,OCI_DEFAULT);	
		}
		if($_SESSION['user']['all_users']<>'y') {
			//��������� ���������� ����
			$ins=OCIParse($c,"insert into STC_USER_CHILD (USER_ID,CHILD_USER_ID) values (".$_SESSION['user']['id'].",".$new_user_id.")");
			OCIExecute($ins,OCI_DEFAULT);	
			
			//��������� ���������� ���� ����� �������
			$parent_user=$_SESSION['user']['id'];
			$q=OCIParse($c,"select creator from STC_USERS where id=:parent_user");
			$ins=OCIParse($c,"insert into STC_USER_CHILD (USER_ID,CHILD_USER_ID) values (:parent_user,".$new_user_id.")");
			while($parent_user<>'') {
				OCIBindByName($q,":parent_user",$parent_user);
				OCIExecute($q, OCI_DEFAULT);
				OCIFetch($q);
				if(OCIResult($q,"CREATOR")==$parent_user or OCIResult($q,"CREATOR")=='') break;
				$parent_user=OCIResult($q,"CREATOR");
				if($parent_user<>'') {
					OCIBindByName($ins,":parent_user",$parent_user);
					OCIExecute($ins, OCI_DEFAULT);
				}
			}
		}
	}
}
//==============================

if(isset($ch_user)) {
	foreach($ch_user as $user_id => $null) {
		if(isset($del_user[$user_id])) continue;
		//�������� ������
		if($_SESSION['user']['rw_users']<>'w' and $role[$user_id]<>'operator') {$error.="<font color=red>������! � ��� ��� ���� ������������� ������������ \"".$login[$user_id]."\" � ����� \"".$role[$user_id]."\".</font><br>";}
		if($_SESSION['user']['all_users']<>'y') {
			$q=OCIParse($c,"select count(*) count from STC_LI_ROLES t where t.all_users is null and t.id='".$role[$user_id]."' and t.role_level<=".$_SESSION['user']['role_level']);
			OCIExecute($q,OCI_DEFAULT);
			OCIFetch($q);
			if (OCIResult($q,"COUNT")==0) {
				echo "select count(*) count from STC_LI_ROLES t where t.all_users is null and t.id='".$role[$user_id]."' and t.role_level<=".$_SESSION['user']['role_level'];
				$error.="<font color=red>������!! � ��� ��� ���� ������������� ������������ \"".$login[$user_id]."\" � ����� \"".$role[$user_id]."\".</font><br>";
				continue;
			}		
		}
		if($fio[$user_id]=='' or $login[$user_id]=='' or $pass[$user_id]=='') {
			$error.="<font color=red>������! ���, �����, ������ �� ������ ���� �������.</font><br>";
			continue;
		}
		if($role[$user_id]<>'operator' and strlen($pass[$user_id])<6) {
			$error.="<font color=red>������! ������ ������ ������������, ����� ��������� �� ������ ���� ������ 6 ��������.</font><br>";
			continue;
		}
		$q=OCIParse($c,"select count(*) count from STC_USERS where id<>".$user_id." and upper(login)=upper('".$login[$user_id]."') and deleted is null and (pass='".$pass[$user_id]."' or creator=".$creator[$user_id].")");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"COUNT")>0) {
			$error.="<font color=red>������! ������������ \"".$login[$user_id]."\" ��� ����������</font><br>";
			continue;
		}
		//���������� ������������
		OCIExecute(OCIParse($c,"update STC_USERS set fio='".$fio[$user_id]."', login='".$login[$user_id]."', pass='".$pass[$user_id]."' where id=".$user_id),OCI_DEFAULT);
		echo "�������� ������������ \"".$fio[$user_id]."\"<hr>";
	}
}

//����� ����������� ���������
if(isset($ch_creator)) {
	foreach($ch_creator as $user_id => $null) {
		if(isset($del_user[$user_id])) continue;
		$q=OCIParse($c,"select creator from STC_USERS where id=".$user_id." and lost_creator='y'");
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) {
			$old_creator=OCIResult($q,"CREATOR");
			if($old_creator<>$creator[$user_id]) { //������ ��������� �� ����� ������
				//����� ���������
				OCIExecute(OCIParse($c,"update STC_USERS u set u.creator=".$creator[$user_id].", u.lost_creator='' --����� ���������
				where u.id=".$user_id." --������������ "),OCI_DEFAULT);
				//���������� �������� ������ ���������
				OCIExecute(OCIParse($c,"insert into STC_USER_CHILD (USER_ID,CHILD_USER_ID)
				select ".$creator[$user_id]." user_id, --����� ���������
				child_user_id 
				from STC_USER_CHILD 
				where user_id=".$old_creator." --������ ���������
				minus
				select ".$creator[$user_id]." user_id, --����� ���������
				child_user_id from stc_user_child
				where user_id=".$creator[$user_id]." --����� ���������"),OCI_DEFAULT);
				OCIExecute(OCIParse($c,""),OCI_DEFAULT);

				echo "������� ��������� ������������ ".$user_id." � ".$old_creator." �� ".$creator[$user_id]."<hr>";
			}
		}
	}
}
//����������� ������������ ����� ��������
if(isset($move_user) and $group_id<>'') {
	foreach($move_user as $user_id => $direction) {
		if($direction=='to_group') {
			OCIExecute(OCIParse($c,"insert into STC_USER_GRP_USR (user_id,group_id) 
			select ".$user_id." user_id, ".$group_id." group_id from dual
			minus 
			select user_id,group_id from STC_USER_GRP_USR where user_id=".$user_id." and group_id=".$group_id),OCI_DEFAULT);
			echo "������������ ".$user_id." �������� � ������ ".$group_id."<hr>";
		}
		if($direction=='from_group') {
			OCIExecute(OCIParse($c,"delete from STC_USER_GRP_USR where user_id=".$user_id." and group_id=".$group_id),OCI_DEFAULT);
			echo "������������ ".$user_id." ������ �� ������ ".$group_id."<hr>";
		}
	}		
}

//���� ���� ������
if($error<>'') {
	OCIRollback($c);
	$error="<font color=red>��������� �� ���������!</font><br>".$error;
	echo $error;
	echo "Rollback.<hr>";
	echo "<script>
	parent.admBottomFrame.admUsersFrame.document.getElementById('save_status').innerHTML='".$error."';
	parent.admBottomFrame.admUsersFrame.frm.cancel.style.display='';
	</script>";	
	exit();
}
else {
	OCICommit($c);
	if($new_user_id<>'' or isset($del_group)) {
		if($new_user_id<>'') {
			$order_by='u1.create_date desc'; $_SESSION['adm']['users']['order_by']=$order_by; //���� ��������� ������������, �� ��������� �� ����		
		}
		echo "<script>parent.admBottomFrame.admUsersFrame.location='adm.users.php'</script>";
	}	
	$_SESSION['adm']['users']['added_user']=$new_user_id;		
	echo "Commit.<hr>";
	echo "<script>parent.admBottomFrame.admUsersFrame.location='adm.users.php'</script>";	
}

function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<font color=red><br>������: ".$code."; ".$msg."; ".$file."; ".$line."<br></font>";
	echo "<script>parent.admBottomFrame.admUsersFrame.document.getElementById('save_status').innerHTML='<font color=red>������: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';
	parent.admBottomFrame.admUsersFrame.frm.cancel.style.display='';
	</script>";
	exit();
}
?>

