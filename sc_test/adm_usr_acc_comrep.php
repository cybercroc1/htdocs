<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script src="func.row_select.js"></script>
<body class="body_marign">
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

$_SESSION['adm_usr_last_url']='adm_usr_comrep_frame.php';

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

//==================

if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id; 
else if(!isset($_SESSION['edit_login']['id'])) exit();

$q=OCIParse($c,"select * from sc_login where id='".$_SESSION['edit_login']['id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$_SESSION['edit_login']['id']=OCIResult($q,"ID");
$_SESSION['edit_login']['fio']=OCIResult($q,"FIO");
$_SESSION['edit_login']['login']=OCIResult($q,"LOGIN");
$_SESSION['edit_login']['desc']=OCIResult($q,"DESCRIPTION");
$password=OCIResult($q,"PASSWORD");
$email=OCIResult($q,"EMAIL");
$rep_period=OCIResult($q,"REP_PERIOD");	

if($_SESSION['edit_login']['id']<>'') echo "<font size=4><a href=adm_usr_main.php target=_parent>".$_SESSION['edit_login']['login']."</a></font>";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_prj_frame.php target=_parent>проекты</a> ";
if ($_SESSION['edit_login']['id']<>'') echo " | <font size=3>Общие отчеты</font> | ";

//==================

$login_id=$_SESSION['edit_login']['id'];

if (isset($add_form)) add_form($login_id,$form_id,$c);
if (isset($del_form)) del_form($login_id,$form_id,$c);

echo "<form action='adm_usr_acc_comrep.php' method=post>";

if (isset($_SESSION['admin']) and $_SESSION['admin']=='1') {
	//список отчетов
	//echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<table id='tbl' class='white_table'>";
	echo "<tr>
	<td align=center><b>Общий отчет</b></td>
	<td align=center><b>Смотреть<br>отчеты</b></td>
	<td></td>";

	echo "</tr>";

	//Добавить общий отчет пользователю
	echo "<tr>";
	$q=OCIParse($c,"select f.id form_id, f.name form_name from SC_FORMS f
	where f.project_id=0 and f.deleted is null and id>0
	and f.id not in (select af.form_id from SC_ACC_FORMS af where af.project_id=0 and af.login_id='".$login_id."')
	order by f.name");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	echo "<td bgcolor=green colspan=3><select name=form_id onchange=ch_form()><option value=''>Выберите общую форму</option>";
		while (OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"FORM_ID")."'>".OCIResult($q,"FORM_NAME")."</option>";
		}
	echo "</select> <input type=submit name=add_form disabled value=\"Добавить форму\"></td>";
	echo "</tr>";
	//

	$q=OCIParse($c,"select f.id form_id, f.name form_name from SC_FORMS f, SC_ACC_FORMS af 
	where af.project_id=0 and af.login_id='".$login_id."'
	and f.project_id=0 and f.id=af.form_id --and f.deleted is null
	order by f.name");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		
		echo "<tr class='selectable_row' onclick=\"click_row(this,'sel');sel_frm('".OCIResult($q,"FORM_ID")."');\">";
		
		echo "<td><b>".OCIResult($q,"FORM_NAME")."</b></td>
		<td bgcolor='#80FF80' align=center>отчеты</td>
		<td bgcolor=white align=center><a href=\"?del_form=1&login_id=".$login_id."&form_id=".OCIResult($q,"FORM_ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td>
		</tr>";
	}
echo "</table>";
	//
}
echo "</form>";

//Функция добавления общего отчета пользователю
function add_form($login_id,$form_id,$c) {
	//доступ к форме
	$ins3=OCIParse($c,"insert into SC_ACC_FORMS (login_id,project_id,form_id,
	date_call,cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min) 
	values ('".$login_id."',0,'".$form_id."',
	'y','y','y','y','y','y','y','y','y','y','y')");
	OCIExecute($ins3,OCI_DEFAULT);
	
	$ins4=OCIParse($c,"insert into sc_acc_frm_obj (project_id,login_id, form_id, obj_id)
	values (0,'".$login_id."','".$form_id."',0)");
	OCIExecute($ins4,OCI_DEFAULT);

	//добавляем доступ ко всем номерам доступа формы
	$ins2=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) 
	values ('".$login_id."',0,'".$form_id."','all')");
	OCIExecute($ins2,OCI_DEFAULT);	
	OCICommit($c);
}
//
//Функция удаления общей формы пользователю
function del_form($login_id,$form_id,$c) {
	//удаляем права доступа к формам проекта
	$del1=OCIParse($c,"delete from SC_ACC_FRM_OBJ where login_id='".$login_id."' and form_id='".$form_id."'");
	OCIExecute($del1,OCI_DEFAULT);
	$del2=OCIParse($c,"delete from SC_ACC_FORMS where login_id='".$login_id."' and form_id='".$form_id."'");
	OCIExecute($del2,OCI_DEFAULT);
	$del3=OCIParse($c,"delete from SC_ACC_CDN where login_id='".$login_id."' and form_id='".$form_id."'");
	OCIExecute($del3,OCI_DEFAULT);		
	OCICommit($c);
}
//

?>
<script language="javascript">
parent.adm_usr_comrep_fr2.location='adm_usr_acc_comrep_obj.php';
ch_form();
function ch_form() {
if (document.all.form_id.value=='') {document.all.add_form.disabled=true;}
else {document.all.add_form.disabled=false;}
}
function sel_frm(form_id) {
	parent.adm_usr_comrep_fr2.location='adm_usr_acc_comrep_obj.php?form_id='+form_id;
}
</script>
