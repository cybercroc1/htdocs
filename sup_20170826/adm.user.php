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

<body leftmargin="3" topmargin="3">
<script>
function fn_del_confirm() {
	if(confirm('Действительно хотите удалить пользователя?')) {
		frm.del_confirm.value='yes';
		frm.submit();
	} 
	else {
		frm.del_confirm.value='';
	}
}
</script>
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");
$_SESSION['cur_edit_user']='';
if(isset($user_id) and $user_id<>'') {
$_SESSION['cur_edit_user']=$user_id;

//информация о пользователе
$q_user=OCIParse($c,"select u.id,u.login,u.fio, --регистрационные данные
u.location,u.otdel,u.doljnost,u.coment, --анкетные данные
to_char(u.create_date,'YYYYDDMMHH24MISS') create_date, to_char(u.deleted,'YYYYDDMMHH24MISS') deleted 
from SUP_USER u where id=".$user_id);

OCIExecute($q_user,OCI_DEFAULT);
if(!OCIFetch($q_user)) exit();

$status='';
if(OCIResult($q_user,"DELETED")==OCIResult($q_user,"CREATE_DATE") and OCIResult($q_user,"LOGIN")=='') {$status='sended_code'; $status_text='<font color=yellow>Отправлен код подтверждения</font>';}
else if (OCIResult($q_user,"DELETED")==OCIResult($q_user,"CREATE_DATE") and OCIResult($q_user,"LOGIN")<>'') {$status='wait_activation'; $status_text='<font color=blue>Ожидает активации</font>';}
else if (OCIResult($q_user,"DELETED")=='') {$status='active'; $status_text='<font color=green>Активен</font>';}
else if (OCIResult($q_user,"DELETED")<>'') {$status='deleted'; $status_text='<font color=red>Удалён</font>';}

echo "<font size=3>Пользователь <b><font color=black>".OCIResult($q_user,"FIO").".</font> ".$status_text."</b></font>";

echo "<form name=frm action=adm.user.save.php target=logFrame>";

echo "<hr>";
echo "<input type=hidden name=user_id value='".$user_id."'>";

echo "<table><tr><td>ФИО: <b>".OCIResult($q_user,"FIO")."</b> | </td><td>Местоположеине: <b>".OCIResult($q_user,"LOCATION")."</b> | </td><td>Отдел: <b>".OCIResult($q_user,"OTDEL")."</b> | </td><td>Должность: <b>".OCIResult($q_user,"DOLJNOST")."</b> | </td><td>Комментарий: <b>".OCIResult($q_user,"COMENT")."</b></td></tr></table>";
echo "<hr>";

	
//Список ролей
$q_role=OCIParse($c,"select r.id role_id,r.role_name, decode(u.id,null,null,'y') curr_role 
from sup_role_pattern r, sup_user u
where u.id(+)=".$user_id."
and nvl(r.look,'n')  =  nvl(u.look(+),'n')
and nvl(r.solution,'n')  =  nvl(u.solution(+),'n')
and nvl(r.redirect,'n')  =  nvl(u.redirect(+),'n')
and nvl(r.eval,'n')  =  nvl(u.eval(+),'n')
and nvl(r.admin,'n')  =  nvl(u.admin(+),'n')
and nvl(r.oper,'n')  =  nvl(u.oper(+),'n')
and nvl(r.deny_close,'n')  =  nvl(u.deny_close(+),'n')
and nvl(r.create_new,'n')  =  nvl(u.create_new(+),'n')
and nvl(r.sms_new,'n')  =  nvl(u.sms_new(+),'n')
and nvl(r.rep_stat,'n')  =  nvl(u.rep_stat(+),'n')
and nvl(r.registrar,'n')  =  nvl(u.registrar(+),'n')
and nvl(r.send,'n')  =  nvl(u.send(+),'n')
and nvl(r.email_coment,'n')  =  nvl(u.email_coment(+),'n')
and nvl(r.email_redir,'n')  =  nvl(u.email_redir(+),'n')
and nvl(r.email_prisv,'n')  =  nvl(u.email_prisv(+),'n')
and nvl(r.email_ready,'n')  =  nvl(u.email_ready(+),'n')
and nvl(r.email_close,'n')  =  nvl(u.email_close(+),'n')
and nvl(r.sms_redir,'n')  =  nvl(u.sms_redir(+),'n')
and nvl(r.sms_prisv,'n')  =  nvl(u.sms_prisv(+),'n')
and nvl(r.sms_ready,'n')  =  nvl(u.sms_ready(+),'n')
and nvl(r.sms_close,'n')   =  nvl(u.sms_close(+),'n')
order by r.role_name");
OCIExecute($q_role,OCI_DEFAULT);
$old_role_id='';

if($status=='wait_activation' or $status=='deleted') echo "<input type=submit name=activate value='Активировать' style='background:green'> | ";
if($status=='active') echo "<input type=submit name=save value='Сохранить' style='background:blue'> | ";
if($status=='active') echo "<input type=submit name=send_reg_sms value='Отправить рег. данные (СМС)' style='background:yellow'> | ";
if($status=='active') echo "<input type=submit name=send_reg_email value='Отправить рег. данные (email)' style='background:yellow'> | ";
if($status<>'deleted') echo "<input type=button name=delete value='Удалить пользователя' style='background:red' onclick='fn_del_confirm()'><input type=hidden name=del_confirm></input>";
echo "<hr>";

if($status<>'sended_code') {

echo "<table>";
echo "<tr>";
echo "<td align=center>";

	echo "<b>Роль: </b><select name=role_id onchange='logFrame.location=\"adm.user.func.php?user_id=".$user_id."&new_role_id=\"+this.value'>
	<option></option>";
	while(OCIFetch($q_role)) {
		echo "<option value='".OCIResult($q_role,"ROLE_ID")."'";
		if(OCIResult($q_role,"CURR_ROLE")=='y') {
			echo " selected style='color:green'";
			$old_role_id=OCIResult($q_role,"ROLE_ID");
		}
		echo ">".OCIResult($q_role,"ROLE_NAME")."</option>";
	}
	echo "</select><hr>";
echo "<div id=div_role></div>";

echo "</td>";
echo "<td valign=top>"; 
	
	echo "<table class=white_table><tr><th>Группы</td></tr>";
	$q_grp=OCIParse($c,"select g.id,g.name, decode(sla.user_id,null,null,'y') curr_grp from SUP_LT_GROUP g, SUP_USER_LT_ALLOC sla
	where g.type='common'
	and sla.user_id(+)=".$user_id."
	and sla.lt_group_id(+)=g.id
	order by g.name");
	OCIExecute($q_grp,OCI_DEFAULT);
	while(OCIFetch($q_grp)) {
		echo "<tr><td>";
		echo "<input type=checkbox name=groups[] value='".OCIResult($q_grp,"ID")."'".(OCIResult($q_grp,"CURR_GRP")=='y'?' checked':'')."><b>".OCIResult($q_grp,"NAME")."</b></input>";
		echo "</td></tr>";
	}
	echo "</table>";

echo "</td>";
echo "</tr>";
echo "</table>";

echo "<form>";	

}

echo "<iframe style='display:none' name='logFrame' src='adm.user.func.php?user_id=".$user_id."&new_role_id=".$old_role_id."'></iframe>";
}

?>
