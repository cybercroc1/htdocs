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
include("sup/sup_conn_string");
$_SESSION['cur_edit_user']='';
if(isset($user_id) and $user_id<>'') {
$_SESSION['cur_edit_user']=$user_id;

if(!isset($grp_id)) $grp_id='';

//информация о пользователе
$q_user=OCIParse($c,"select u.id,u.login,u.fio, u.admin, u.registrar, --регистрационные данные
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

if($status=='wait_activation' or $status=='deleted') echo "<input type=submit name=activate value='Активировать' style='background:green'> | ";
if($status=='active') echo "<input type=submit name=save value='Сохранить' style='background:blue'> | ";
if($status=='active') echo "<input type=submit name=send_reg_sms value='Отправить рег. данные (СМС)' style='background:yellow'> | ";
if($status=='active') echo "<input type=submit name=send_reg_email value='Отправить рег. данные (email)' style='background:yellow'> | ";
if($status<>'deleted') echo "<input type=button name=delete value='Удалить пользователя' style='background:red' onclick='fn_del_confirm()'><input type=hidden name=del_confirm></input>";
echo "<hr>";

if($status<>'sended_code') {

//список групп

	$q_grp=OCIParse($c,"select g.id,g.name, decode(sla.user_id,null,null,'y') curr_grp from SUP_LT_GROUP g, SUP_USER_LT_ALLOC sla
	where g.type='common'
	and sla.user_id(+)=".$user_id."
	and sla.lt_group_id(+)=g.id
	order by g.name");
	OCIExecute($q_grp,OCI_DEFAULT);
	echo "<b>Группа: </b><select name='grp_id' onchange='document.location=\"adm.user.php?user_id=".$user_id."&grp_id=\"+this.value'><option value=''>-- Выберите группу --</option>";
	while(OCIFetch($q_grp)) {
		echo "<option value='".OCIResult($q_grp,"ID")."'".(OCIResult($q_grp,"CURR_GRP")=='y'?' style="font-weight:bold;color:green"':'').(OCIResult($q_grp,"ID")==$grp_id?' selected':'').">".OCIResult($q_grp,"NAME")."</option>";
	}
	echo "</select> ";

if($grp_id<>'') {
//Список шаблонов ролей
$q_role=OCIParse($c,"select r.id role_id,r.role_name, decode(ua.user_id,null,null,'y') curr_role 
from sup_role_pattern r, sup_user_lt_alloc ua
where ua.user_id(+)=".$user_id." and ua.lt_group_id(+)=".$grp_id."
and nvl(r.look,'n')       =  nvl(ua.look(+),'n')
and nvl(r.solution,'n')   =  nvl(ua.solution(+),'n')
and nvl(r.redirect,'n')   =  nvl(ua.redirect(+),'n')
and nvl(r.eval,'n')       =  nvl(ua.eval(+),'n')
and nvl(r.trudozatrati,'n')=  nvl(ua.trudozatrati(+),'n')
and nvl(r.deny_close,'n') =  nvl(ua.deny_close(+),'n')
and nvl(r.create_new,'n') =  nvl(ua.create_new(+),'n')
and nvl(r.rep_stat,'n')   =  nvl(ua.rep_stat(+),'n')
and nvl(r.em_new,'n')     =  nvl(ua.em_new(+),'n')
and nvl(r.em_coment,'n')  =  nvl(ua.em_coment(+),'n')
and nvl(r.em_redir,'n')   =  nvl(ua.em_redir(+),'n')
and nvl(r.em_prisv,'n')   =  nvl(ua.em_prisv(+),'n')
and nvl(r.em_delay,'n')   =  nvl(ua.em_delay(+),'n')
and nvl(r.em_ready,'n')   =  nvl(ua.em_ready(+),'n')
and nvl(r.em_close,'n')   =  nvl(ua.em_close(+),'n')
and nvl(r.sm_new,'n')     =  nvl(ua.sm_new(+),'n')
and nvl(r.sm_redir,'n')   =  nvl(ua.sm_redir(+),'n')
and nvl(r.sm_prisv,'n')   =  nvl(ua.sm_prisv(+),'n')
and nvl(r.sm_delay,'n')   =  nvl(ua.sm_delay(+),'n')
and nvl(r.sm_ready,'n')   =  nvl(ua.sm_ready(+),'n')
and nvl(r.sm_close,'n')   =  nvl(ua.sm_close(+),'n')
and nvl(r.em_resume,'n')   =  nvl(ua.em_resume(+),'n')
and nvl(r.sm_resume,'n')   =  nvl(ua.sm_resume(+),'n')
order by r.role_name");
OCIExecute($q_role,OCI_DEFAULT);

$old_role_id='';

//echo "<table>";
//echo "<tr>";
//echo "<td align=center>";

	echo "<b>Роль: </b><select name=role_id onchange='logFrame.location=\"adm.user.func.php?user_id=".$user_id."&grp_id=".$grp_id."&new_role_id=\"+this.value'>
	<option></option>";
	while(OCIFetch($q_role)) {
		echo "<option value='".OCIResult($q_role,"ROLE_ID")."'";
		if(OCIResult($q_role,"CURR_ROLE")=='y') {
			echo " selected style='font-weight:bold;color:green'";
			$old_role_id=OCIResult($q_role,"ROLE_ID");
		}
		echo ">".OCIResult($q_role,"ROLE_NAME")."</option>";
	}
	echo "<option value='clear' style='color:red'>Нет достпа</option>";
	echo "</select>";
}	
echo "<hr>";
echo "<div id=div_role></div>";

		echo "<tr><th colspan=4>Общие привилегии</th></tr>";
		echo "<tr><td colspan=4><table><tr>";
		echo "<td><input name=admin type=checkbox value='y'".(OCIResult($q_user,"ADMIN")=='y'?' checked':'')."><b>Админ</b></input><br><i>Редактирование групп, списков, пользователей</i></td>";
		echo "<td><input name=registrar type=checkbox value='y'".(OCIResult($q_user,"REGISTRAR")=='y'?' checked':'')."><b>Регистратор</b></input><br><i>Разрешает регистрировать новых пользователей</i></td>";

//echo "</td>";

echo "</tr>";
echo "</table>";

echo "<form>";	

}

echo "<iframe style='display:' name='logFrame' src='adm.user.func.php?user_id=".$user_id."&grp_id=".$grp_id."&new_role_id='></iframe>";
}

?>
