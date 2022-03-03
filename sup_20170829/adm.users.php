<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
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
include("../../sup_conf/sup_conn_string");

if(!isset($order_by) and !isset($_SESSION['users_order_by'])) $_SESSION['users_order_by']='status';
else if(isset($order_by)) $_SESSION['users_order_by']=$order_by;

if(!isset($filter_status) and !isset($_SESSION['filter_status'])) $_SESSION['filter_status']='and u.deleted=u.create_date and u.login is not null';
else if(isset($filter_status)) $_SESSION['filter_status']=$filter_status;

if(!isset($filter_role) and !isset($_SESSION['filter_role'])) $_SESSION['filter_role']='';
else if(isset($filter_role)) $_SESSION['filter_role']=$filter_role;

$roles=array();
$groups=array();

//список ролей
$q=OCIParse($c,"select r.id,r.role_name from SUP_ROLE_PATTERN r
order by r.role_name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$roles[OCIResult($q,"ID")]=OCIResult($q,"ROLE_NAME");
}

echo "<form name=frm>";

echo "<font size=3><b>Пользователи</b></font>";

if($_SESSION['filter_role']<>'') $filter_role='and r.id='.$_SESSION['filter_role']; else $filter_role='';

$q=OCIParse($c,"select u.id,u.login,u.fio,to_char(u.create_date,'DD.MM.YYYY') create_date,to_char(u.last_logon,'DD.MM.YYYY') last_logon,u.coment,r.id role_id,r.role_name,
case when u.deleted=u.create_date and u.login is null then 'отправлен код подтверждения'
when u.deleted=u.create_date and u.login is not null then 'ожидает активации'
when u.deleted is null then 'активен'
when u.deleted is not null then 'удален'
end status
from sup_role_pattern r, sup_user u where 
    nvl(u.look,'n')  =  nvl(r.look(+),'n')
and nvl(u.solution,'n')  =  nvl(r.solution(+),'n')
and nvl(u.redirect,'n')  =  nvl(r.redirect(+),'n')
and nvl(u.eval,'n')  =  nvl(r.eval(+),'n')
and nvl(u.admin,'n')  =  nvl(r.admin(+),'n')
and nvl(u.deny_close,'n')  =  nvl(r.deny_close(+),'n')
and nvl(u.create_new,'n')  =  nvl(r.create_new(+),'n')
and nvl(u.sms_new,'n')  =  nvl(r.sms_new(+),'n')
and nvl(u.rep_stat,'n')  =  nvl(r.rep_stat(+),'n')
and nvl(u.registrar,'n')  =  nvl(r.registrar(+),'n')
and nvl(u.send,'n')  =  nvl(r.send(+),'n')
and nvl(u.email_coment,'n')  =  nvl(r.email_coment(+),'n')
and nvl(u.email_redir,'n')  =  nvl(r.email_redir(+),'n')
and nvl(u.email_prisv,'n')  =  nvl(r.email_prisv(+),'n')
and nvl(u.email_ready,'n')  =  nvl(r.email_ready(+),'n')
and nvl(u.email_close,'n')  =  nvl(r.email_close(+),'n')
and nvl(u.sms_redir,'n')  =  nvl(r.sms_redir(+),'n')
and nvl(u.sms_prisv,'n')  =  nvl(r.sms_prisv(+),'n')
and nvl(u.sms_ready,'n')  =  nvl(r.sms_ready(+),'n')
and nvl(u.sms_close,'n')   =  nvl(r.sms_close(+),'n') 
".$_SESSION['filter_status']."
".$filter_role."
order by ".$_SESSION['users_order_by'].", status, u.fio, r.role_name");

$q_lt_grp=OCIParse($c,"select g.name from SUP_USER_LT_ALLOC a, SUP_LT_GROUP g
where a.user_id=:user_id and g.id=a.lt_group_id
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
	
	<th>".($_SESSION['users_order_by']=='r.role_name nulls first'?'<b>Роль *</b>':'<a href=\'?order_by=r.role_name nulls first\'>Роль</a>')."<br>
	<select name=filter_role onchange=frm.submit()>
	<option value=''>ВСЕ</option>";
	foreach($roles as $id=>$name) {
		echo "<option value='".$id."'".($_SESSION['filter_role']==$id?' selected':'').">".$name."</option>";
	}
	echo "</select>
	</td>
	<th><b>Группы</b></td>
	<th>".($_SESSION['users_order_by']=='u.create_date desc'?'<b>Дата создания *</b>':'<a href=\'?order_by=u.create_date desc\'>Дата создания</a>')."</td>
	<th>".($_SESSION['users_order_by']=='u.last_logon desc'?'<b>Последний вход *</b>':'<a href=\'?order_by=u.last_logon desc\'>Последний вход</a>')."</td>	
	<th><b>Комментарий</b></td>";
	echo "</tr>";
	
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		$tmp_user_id=OCIResult($q,"ID");
		echo "<tr class=selectable_row onclick=sel_user('".OCIResult($q,"ID")."')>";
		echo "<td>".OCIResult($q,"ID")."</td>";
		echo "<td>".OCIResult($q,"FIO")."</td>";
		echo "<td>".OCIResult($q,"LOGIN")."</td>";
		echo "<td>".OCIResult($q,"STATUS")."</td>";
		echo "<td>".OCIResult($q,"ROLE_NAME")."</td>";
		echo "<td>";
			OCIBindByName($q_lt_grp,":user_id",$tmp_user_id);
			OCIExecute($q_lt_grp,OCI_DEFAULT);
			while(OCIFetch($q_lt_grp)) {
				echo OCIResult($q_lt_grp,"NAME").";<br>";
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