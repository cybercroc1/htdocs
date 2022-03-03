<?php
session_name('tex');
session_start();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<script src="func.row_select.js"></script>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("sup/sup_conn_string");

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

//список ролей
$q=OCIParse($c,"select r.id,r.role_name from SUP_ROLE_PATTERN r
order by r.role_name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$roles[OCIResult($q,"ID")]=OCIResult($q,"ROLE_NAME");
}
$roles['x']="Особые привилегии";

//список групп
$q=OCIParse($c,"select id,name from SUP_LT_GROUP t
where type='common'
order by name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$lt_groups[OCIResult($q,"ID")]=OCIResult($q,"NAME");
}

echo "<form name=frm>";

echo "<font size=3><b>Пользователи</b></font>";

//if($_SESSION['filter_role']<>'') $filter_role='and r.id='.$_SESSION['filter_role']; else $filter_role='';

$q=OCIParse($c,"select u.id,u.login,u.fio,u.admin,u.registrar,to_char(u.create_date,'DD.MM.YYYY') create_date,to_char(u.last_logon,'DD.MM.YYYY') last_logon,u.coment,
case when u.deleted=u.create_date and u.login is null then 'отправлен код подтверждения'
when u.deleted=u.create_date and u.login is not null then 'ожидает активации'
when u.deleted is null then 'активен'
when u.deleted is not null then 'удален'
end status
from sup_user u 
where 1=1 
".$_SESSION['filter_status']."
order by ".$_SESSION['users_order_by'].", status, u.fio");

$q_lt_grp=OCIParse($c,"select g.id group_id, g.name group_name, r.id role_id, r.role_name from (

select u.id,a.lt_group_id,u.admin,u.registrar,a.create_new,a.solution,a.deny_close,a.redirect,a.look,a.eval,a.rep_stat,
a.em_new,a.em_redir,a.em_prisv,a.em_ready,a.em_delay,a.em_close,a.em_coment,a.em_resume,
a.sm_new,a.sm_redir,a.sm_prisv,a.sm_delay,a.sm_ready,a.sm_close,            a.sm_resume 
from SUP_USER_LT_ALLOC a, SUP_USER u
where u.id=:user_id and a.user_id(+)=u.id

) au, SUP_LT_GROUP g, sup_role_pattern r
where g.id(+)=au.lt_group_id
and nvl(au.look,'n')      =  nvl(r.look(+),'n')
and nvl(au.solution,'n')  =  nvl(r.solution(+),'n')
and nvl(au.redirect,'n')  =  nvl(r.redirect(+),'n')
and nvl(au.eval,'n')      =  nvl(r.eval(+),'n')
and nvl(au.deny_close,'n')=  nvl(r.deny_close(+),'n')
and nvl(au.create_new,'n')=  nvl(r.create_new(+),'n')
and nvl(au.rep_stat,'n')  =  nvl(r.rep_stat(+),'n')
and nvl(au.em_new,'n')    =  nvl(r.em_new(+),'n')
and nvl(au.em_coment,'n') =  nvl(r.em_coment(+),'n')
and nvl(au.em_redir,'n')  =  nvl(r.em_redir(+),'n')
and nvl(au.em_prisv,'n')  =  nvl(r.em_prisv(+),'n')
and nvl(au.em_delay,'n')  =  nvl(r.em_delay(+),'n')
and nvl(au.em_ready,'n')  =  nvl(r.em_ready(+),'n')
and nvl(au.em_close,'n')  =  nvl(r.em_close(+),'n')
and nvl(au.sm_new,'n')    =  nvl(r.sm_new(+),'n')
and nvl(au.sm_redir,'n')  =  nvl(r.sm_redir(+),'n')
and nvl(au.sm_prisv,'n')  =  nvl(r.sm_prisv(+),'n')
and nvl(au.sm_delay,'n')  =  nvl(r.sm_delay(+),'n')
and nvl(au.sm_ready,'n')  =  nvl(r.sm_ready(+),'n')
and nvl(au.sm_close,'n')  =  nvl(r.sm_close(+),'n') 
and nvl(au.em_resume,'n')   =  nvl(r.em_resume(+),'n')
and nvl(au.sm_resume,'n')   =  nvl(r.sm_resume(+),'n')

order by g.name");

	echo "<table id='tbl' class=white_table>
	<tr>
	<th><b>ID</b></td>
	<th>".($_SESSION['users_order_by']=='u.fio'?'<b>ФИО *</b>':'<a href=?order_by=u.fio>ФИО</a>')."</td>
	<th>".($_SESSION['users_order_by']=='u.login'?'<b>Логин *</b>':'<a href=?order_by=u.login>Логин</a>')."</td>
	<th>
	".($_SESSION['users_order_by']=='status'?'<b>Статус *</b>':'<a href=?order_by=status>Статус</a>')."<br>
	<select name=filter_status onchange=frm.submit()>
	<option value=''>ВСЕ</option>
	<option value='and u.deleted=u.create_date and u.login is null'".($_SESSION['filter_status']=='and u.deleted=u.create_date and u.login is null'?' selected':'').">Отправлен код</option>
	<option value='and u.deleted=u.create_date and u.login is not null'".($_SESSION['filter_status']=='and u.deleted=u.create_date and u.login is not null'?' selected':'').">Ожидает активации</option>
	<option value='and u.deleted is null'".($_SESSION['filter_status']=='and u.deleted is null'?' selected':'').">Активен</option>
	<option value='and u.deleted is not null and u.deleted<>u.create_date'".($_SESSION['filter_status']=='and u.deleted is not null and u.deleted<>u.create_date'?' selected':'').">Удалён</option>
	</select>
	</td>
	
	<th><b>Группа - Роль</b><br>
	<select name=filter_lt_group onchange=frm.submit()>
	<option value=''>ВСЕ</option>";
	foreach($lt_groups as $id=>$name) {
		echo "<option value='".$id."'".($_SESSION['filter_lt_group']==$id?' selected':'').">".$name."</option>";
	}
	echo "</select> - 
	<select name=filter_role onchange=frm.submit()>
	<option value=''>ВСЕ</option>";
	echo "<option value='admin'".($_SESSION['filter_role']=='admin'?' selected':'')."> - Админ</option>";
	echo "<option value='registrar'".($_SESSION['filter_role']=='registrar'?' selected':'')."> - Регистратор</option>";
	foreach($roles as $id=>$name) {
		echo "<option value='".$id."'".($_SESSION['filter_role']==$id?' selected':'').">".$name."</option>";
	}
	//echo "<option value='x'".($_SESSION['filter_role']==$id?' selected':'').">Особые привилегии</option>";
	echo "</select>	
	</td>
	<th>".($_SESSION['users_order_by']=='u.create_date desc'?'<b>Дата создания *</b>':'<a href=\'?order_by=u.create_date desc\'>Дата создания</a>')."</td>
	<th>".($_SESSION['users_order_by']=='u.last_logon desc'?'<b>Последний вход *</b>':'<a href=\'?order_by=u.last_logon desc\'>Последний вход</a>')."</td>	
	<th><b>Комментарий</b></td>";
	echo "</tr>";
	
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		$tmp_user_id=OCIResult($q,"ID");
		OCIBindByName($q_lt_grp,":user_id",$tmp_user_id);
		OCIExecute($q_lt_grp,OCI_DEFAULT);
		$g_r=array();
		
		$role_ok='';
		$group_ok='';
		
		if($_SESSION['filter_role']=='admin' and OCIResult($q,"ADMIN")=='y') $role_ok='y';
		if($_SESSION['filter_role']=='registrar' and OCIResult($q,"REGISTRAR")=='y') $role_ok='y';
		
		$i=0; while(OCIFetch($q_lt_grp)) {$i++;
			$g_r[$i]['group_id']=OCIResult($q_lt_grp,"GROUP_ID");
			$g_r[$i]['group_name']=OCIResult($q_lt_grp,"GROUP_NAME");
			$g_r[$i]['role_id']=OCIResult($q_lt_grp,"ROLE_ID");
			$g_r[$i]['role_name']=OCIResult($q_lt_grp,"ROLE_NAME");
			if($_SESSION['filter_role']<>'' and $g_r[$i]['role_id']==$_SESSION['filter_role']) $role_ok='y';
			if($_SESSION['filter_role']=='x' and $g_r[$i]['role_id']=='') $role_ok='y';
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
			if(OCIResult($q,"ADMIN")=='y') echo " - Админ;<br>";
			if(OCIResult($q,"REGISTRAR")=='y') echo " - Регистратор;<br>";
			foreach($g_r as $val) {
				if($val['group_id']<>'' and $val['role_id']=='') $val['role_name']='Особые привилегии';
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