<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>������������</title>
</head>
<script src="func.row_select.js"></script>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>�� ���������� ����!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

if(!isset($order_by) and !isset($_SESSION['users_order_by'])) $_SESSION['users_order_by']='status';
else if(isset($order_by)) $_SESSION['users_order_by']=$order_by;

if(!isset($filter_status) and !isset($_SESSION['filter_status'])) $_SESSION['filter_status']='and u.deleted=u.create_date and u.login is not null';
else if(isset($filter_status)) $_SESSION['filter_status']=$filter_status;

if(!isset($filter_role) and !isset($_SESSION['filter_role'])) $_SESSION['filter_role']='';
else if(isset($filter_role)) $_SESSION['filter_role']=$filter_role;

if(!isset($filter_lt_group) and !isset($_SESSION['filter_lt_group'])) $_SESSION['filter_lt_group']='';
else if(isset($filter_lt_group)) $_SESSION['filter_lt_group']=$filter_lt_group;
$roles=array();
$groups=array();

//������ �����
$q=OCIParse($c,"select r.id,r.role_name from SUP_ROLE_PATTERN r
order by r.role_name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$roles[OCIResult($q,"ID")]=OCIResult($q,"ROLE_NAME");
}
//������ �����
$q=OCIParse($c,"select id,name from SUP_LT_GROUP t
where type='common'
order by name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$lt_groups[OCIResult($q,"ID")]=OCIResult($q,"NAME");
}

echo "<form name=frm>";

echo "<font size=3><b>������������</b></font>";

//if($_SESSION['filter_role']<>'') $filter_role='and r.id='.$_SESSION['filter_role']; else $filter_role='';

$q=OCIParse($c,"select u.id,u.login,u.fio,to_char(u.create_date,'DD.MM.YYYY') create_date,to_char(u.last_logon,'DD.MM.YYYY') last_logon,u.coment,
case when u.deleted=u.create_date and u.login is null then '��������� ��� �������������'
when u.deleted=u.create_date and u.login is not null then '������� ���������'
when u.deleted is null then '�������'
when u.deleted is not null then '������'
end status
from sup_user u 
where 1=1 
".$_SESSION['filter_status']."
order by ".$_SESSION['users_order_by'].", status, u.fio");

$q_lt_grp=OCIParse($c,"select g.id group_id, g.name group_name, r.id role_id, r.role_name from (

select u.id,a.lt_group_id,u.admin,u.oper,u.registrar,a.create_new,a.solution,a.deny_close,a.redirect,a.look,a.eval,a.rep_stat,
a.send,a.email_redir,a.email_prisv,a.email_ready,a.email_close,a.email_coment,
a.sms_new,a.sms_redir,a.sms_prisv,a.sms_ready,a.sms_close 
from SUP_USER_LT_ALLOC a, SUP_USER u
where u.id=:user_id and a.user_id(+)=u.id

) au, SUP_LT_GROUP g, sup_role_pattern r
where g.id(+)=au.lt_group_id

and nvl(au.admin,'n')  =  nvl(r.admin(+),'n')
and nvl(au.registrar,'n')  =  nvl(r.registrar(+),'n')
and nvl(au.oper,'n')  =  nvl(r.oper(+),'n')

and nvl(au.look,'n')  =  nvl(r.look(+),'n')
and nvl(au.solution,'n')  =  nvl(r.solution(+),'n')
and nvl(au.redirect,'n')  =  nvl(r.redirect(+),'n')
and nvl(au.eval,'n')  =  nvl(r.eval(+),'n')
and nvl(au.deny_close,'n')  =  nvl(r.deny_close(+),'n')
and nvl(au.create_new,'n')  =  nvl(r.create_new(+),'n')
and nvl(au.sms_new,'n')  =  nvl(r.sms_new(+),'n')
and nvl(au.rep_stat,'n')  =  nvl(r.rep_stat(+),'n')
and nvl(au.send,'n')  =  nvl(r.send(+),'n')
and nvl(au.email_coment,'n')  =  nvl(r.email_coment(+),'n')
and nvl(au.email_redir,'n')  =  nvl(r.email_redir(+),'n')
and nvl(au.email_prisv,'n')  =  nvl(r.email_prisv(+),'n')
and nvl(au.email_ready,'n')  =  nvl(r.email_ready(+),'n')
and nvl(au.email_close,'n')  =  nvl(r.email_close(+),'n')
and nvl(au.sms_redir,'n')  =  nvl(r.sms_redir(+),'n')
and nvl(au.sms_prisv,'n')  =  nvl(r.sms_prisv(+),'n')
and nvl(au.sms_ready,'n')  =  nvl(r.sms_ready(+),'n')
and nvl(au.sms_close,'n')   =  nvl(r.sms_close(+),'n') 

order by g.name");

	echo "<table id='tbl' class=white_table>
	<tr>
	<th><b>ID</b></td>
	<th>".($_SESSION['users_order_by']=='u.fio'?'<b>��� *</b>':'<a href=?order_by=u.fio>���</a>')."</td>
	<th>".($_SESSION['users_order_by']=='u.login'?'<b>����� *</b>':'<a href=?order_by=u.login>�����</a>')."</td>
	<th>
	".($_SESSION['users_order_by']=='status'?'<b>������ *</b>':'<a href=?order_by=status>������</a>')."<br>
	<select name=filter_status onchange=frm.submit()>
	<option value=''>���</option>
	<option value='and u.deleted=u.create_date and u.login is null'".($_SESSION['filter_status']=='and u.deleted=u.create_date and u.login is null'?' selected':'').">��������� ���</option>
	<option value='and u.deleted=u.create_date and u.login is not null'".($_SESSION['filter_status']=='and u.deleted=u.create_date and u.login is not null'?' selected':'').">������� ���������</option>
	<option value='and u.deleted is null'".($_SESSION['filter_status']=='and u.deleted is null'?' selected':'').">�������</option>
	<option value='and u.deleted is not null and u.deleted<>u.create_date'".($_SESSION['filter_status']=='and u.deleted is not null and u.deleted<>u.create_date'?' selected':'').">�����</option>
	</select>
	</td>
	
	<th><b>������ - ����</b><br>
	<select name=filter_lt_group onchange=frm.submit()>
	<option value=''>���</option>";
	foreach($lt_groups as $id=>$name) {
		echo "<option value='".$id."'".($_SESSION['filter_lt_group']==$id?' selected':'').">".$name."</option>";
	}
	echo "</select> - 
	<select name=filter_role onchange=frm.submit()>
	<option value=''>���</option>";
	foreach($roles as $id=>$name) {
		echo "<option value='".$id."'".($_SESSION['filter_role']==$id?' selected':'').">".$name."</option>";
	}
	echo "</select>	
	</td>
	<th>".($_SESSION['users_order_by']=='u.create_date desc'?'<b>���� �������� *</b>':'<a href=\'?order_by=u.create_date desc\'>���� ��������</a>')."</td>
	<th>".($_SESSION['users_order_by']=='u.last_logon desc'?'<b>��������� ���� *</b>':'<a href=\'?order_by=u.last_logon desc\'>��������� ����</a>')."</td>	
	<th><b>�����������</b></td>";
	echo "</tr>";
	
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		$tmp_user_id=OCIResult($q,"ID");
		OCIBindByName($q_lt_grp,":user_id",$tmp_user_id);
		OCIExecute($q_lt_grp,OCI_DEFAULT);
		$g_r=array();
		
		$role_ok='';
		$group_ok='';
		
		$i=0; while(OCIFetch($q_lt_grp)) {$i++;
			$g_r[$i]['group_id']=OCIResult($q_lt_grp,"GROUP_ID");
			$g_r[$i]['group_name']=OCIResult($q_lt_grp,"GROUP_NAME");
			$g_r[$i]['role_id']=OCIResult($q_lt_grp,"ROLE_ID");
			$g_r[$i]['role_name']=OCIResult($q_lt_grp,"ROLE_NAME");
			if($_SESSION['filter_role']<>'' and $g_r[$i]['role_id']==$_SESSION['filter_role']) $role_ok='y';
			if($_SESSION['filter_lt_group']<>'' and $g_r[$i]['group_id']==$_SESSION['filter_lt_group']) $group_ok='y';
		}	
		if($_SESSION['filter_role']<>'' and $role_ok<>'y') continue;
		if($_SESSION['filter_lt_group']<>'' and $group_ok<>'y') continue;
		
		echo "<tr class=selectable_row onclick=sel_user('".OCIResult($q,"ID")."')>";
		echo "<td>".OCIResult($q,"ID")."</td>";
		echo "<td>".OCIResult($q,"FIO")."</td>";
		echo "<td>".OCIResult($q,"LOGIN")."</td>";
		echo "<td>".OCIResult($q,"STATUS")."</td>";
		echo "<td>";
			foreach($g_r as $val) {
				if($val['group_id']<>'' and $val['role_id']=='') $val['role_name']='������ ����������';
				echo $val['group_name']." - ".$val['role_name'].";<br>";				
			}
		echo "</td>";
		echo "<td>".OCIResult($q,"CREATE_DATE")."</td>";
		echo "<td>".OCIResult($q,"LAST_LOGON")."</td>";
		echo "<td>".OCIResult($q,"COMENT")."</td>";
		echo "</tr>";
	}
	echo "</table>";

echo "</form>";
?>
<script>
function sel_user(id) {
	parent.admUsersRightFrame.location='adm.user.php?user_id='+id;
}
</script>