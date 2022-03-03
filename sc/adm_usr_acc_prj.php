<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
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

$_SESSION['adm_usr_last_url']='adm_usr_prj_frame.php';

extract($_REQUEST);

include("sc/sc_conn_string.php");

//==================

if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id; 
else if(!isset($_SESSION['edit_login']['id'])) exit();

$q=OCIParse($c,"select * from sc_login where id='".$_SESSION['edit_login']['id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$_SESSION['edit_login']['id']=OCIResult($q,"ID");
//$_SESSION['edit_login']['fio']=OCIResult($q,"FIO");
$_SESSION['edit_login']['login']=OCIResult($q,"LOGIN");
$_SESSION['edit_login']['desc']=OCIResult($q,"DESCRIPTION");
$password=OCIResult($q,"PASSWORD");
//$email=OCIResult($q,"EMAIL");
$rep_period=OCIResult($q,"REP_PERIOD");	

if($_SESSION['edit_login']['id']<>'') echo "<font size=4><a href=adm_usr_main.php target=_parent>".$_SESSION['edit_login']['login']."</a></font>";
if ($_SESSION['edit_login']['id']<>'') echo " | <font size=4>проекты</font> ";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_comrep_frame.php target=_parent>Общие отчеты</a> | ";

//==================

$login_id=$_SESSION['edit_login']['id'];

if (!isset($view_rep)) $view_rep='';
if (!isset($ch_email)) $ch_email='';
if (!isset($ch_form)) $ch_form='';
if (!isset($ch_sc)) $ch_sc='';
if (!isset($view_billing)) $view_billing='';
if (!isset($view_sms_log)) $view_sms_log='';
if (!isset($vsr_billing)) $vsr_billing='';
if (isset($add_irs_project)) add_project($login_id,$irs_project,$view_rep,$view_billing,$view_sms_log,$ch_email,$ch_form,$ch_sc,'',$c);
if (isset($add_vsr_project)) add_project($login_id,$vsr_project,'','','','','',$vsr_billing,$c);
if (isset($del_project)) del_project($login_id,$project_id,$c);


/*$q=OCIParse($c,"select * from sc_login where id='".$login_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$fio=OCIResult($q,"FIO");
$description=OCIResult($q,"DESCRIPTION");
$login=OCIResult($q,"LOGIN");
$password=OCIResult($q,"PASSWORD");
$email=OCIResult($q,"EMAIL");
$rep_period=OCIResult($q,"REP_PERIOD");	*/

echo "<form action='adm_usr_acc_prj.php' method=post>";

//if ($_SESSION['edit_login']['id'] and $_SESSION['edit_login']['id']<>'') {
//$login_id=$_SESSION['edit_login']['id'];

if (isset($_SESSION['admin']) and $_SESSION['admin']=='1') {
	//Проекты IRS
	//echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<table id='tbl' class='white_table'>";
	echo "<tr>
	<td align=center><b>Проект</b></td>
	<td align=center><b>Смотреть<br>отчеты</b></td>
	<td align=center><b>Смотреть<br>биллинг</b></td>
	<td align=center><b>Смотреть<br>СМС-лог</b></td>
	<td align=center><b>Редактировать<br>e-mail</b></td>
	<td align=center><b>Редактировать<br>формы</b></td>
	<td align=center><b>Редактировать<br>сценарий</b></td>
	<td></td>";
	echo "</tr>";

	//Добавить проект IRS пользователю
	echo "<tr>";
	$q=OCIParse($c,"select id,name from sc_projects
where type='irs' and hidden is null and id not in(select project_id from SC_ACC_PROJECT where login_id='".$login_id."')
order by name");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	echo "<td bgcolor=green colspan=8><select name=irs_project onchange=ch_irs_project()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$_SESSION['project']['id']?" selected":"").">".OCIResult($q,"NAME")."</option>";
		}
	echo "</select> <input type=submit name=add_irs_project disabled value=\"Добавить проект\"></td>";
	echo "</tr>";
	echo "<tr><td bgcolor=green align=center></td>
	<td bgcolor=green align=center><input type=checkbox checked value=1 name=view_rep></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=view_billing></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=view_sms_log></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_email></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_form></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_sc></td>";
	
	echo "<td bgcolor=green></td></tr>";
	//

	$q=OCIParse($c,"select p.name,r.project_id,
decode(r.view_rep,1,'#80FF80','#FF8080') view_rep,
decode(r.ch_email,1,'#80FF80','#FF8080') ch_email,
decode(r.ch_form,1,'#80FF80','#FF8080') ch_form,
decode(r.ch_sc,1,'#80FF80','#FF8080') ch_sc,
decode(r.view_billing,1,'#80FF80','#FF8080') view_billing,
decode(r.view_sms_log,1,'#80FF80','#FF8080') view_sms_log
from sc_projects p, SC_ACC_PROJECT r
where p.id=r.project_id and r.login_id='".$login_id."' and p.type='irs'
order by p.name");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		
		echo "<tr class='selectable_row' onclick=\"click_row(this,'sel');sel_prj('".OCIResult($q,"PROJECT_ID")."');\">";
		
		echo "<td><b>".OCIResult($q,"NAME")."</b></td>
		<td bgcolor=".OCIResult($q,"VIEW_REP")." align=center>отчеты</td>
		<td bgcolor=".OCIResult($q,"VIEW_BILLING")." align=center>биллинг</td>
		<td bgcolor=".OCIResult($q,"VIEW_SMS_LOG")." align=center>СМС-лог</td>
		<td bgcolor=".OCIResult($q,"CH_EMAIL")." align=center>e-mail</td>
		<td bgcolor=".OCIResult($q,"CH_FORM")." align=center>формы</td>
		<td bgcolor=".OCIResult($q,"CH_SC")." align=center>сценарий</td>
		<td bgcolor=white align=center><a href=\"?del_project=1&login_id=".$login_id."&project_id=".OCIResult($q,"PROJECT_ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td>
		</tr>";
	}
echo "</table>";
	//
}
if (isset($_SESSION['vsr_admin']) and $_SESSION['vsr_admin']=='1') {
	//Проекты VSR

	echo "<hr><font size=4>Проекты пользователя (ВИРТУАЛЬНЫЙ СЕКРЕТАРЬ)</font><br>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white align=center><b>Проект</b></td>
	<td bgcolor=white align=center><b>Смотреть<br>биллинг</b></td>
	<td bgcolor=white></td>";

	echo "</tr>";

	//Добавить проект VSR пользователю
	echo "<tr>";
	$q=OCIParse($c,"select id,name from sc_projects
where type='vsr' and id not in(select project_id from SC_ACC_PROJECT where login_id='".$login_id."')
order by name");
	OCIExecute($q,OCI_DEFAULT);
	echo "<td bgcolor=green><select name=vsr_project onchange=ch_vsr_project()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
	echo "</select></td>";
	echo "<td bgcolor=green align=center><input type=checkbox checked value=1 name=vsr_billing></td>";
	
	echo "<td bgcolor=green colspan=2><input type=submit name=add_vsr_project disabled value=\"Добавить проект\"></td></tr>";
	//

	$q=OCIParse($c,"select p.name,r.project_id,
decode(r.vsr_billing,1,'#80FF80','#FF8080') vsr_billing
from sc_projects p, SC_ACC_PROJECT r
where p.id=r.project_id and r.login_id='".$login_id."' and p.type='vsr'");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		echo "<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
		<td bgcolor=".OCIResult($q,"VSR_BILLING")." align=center>биллинг</td>
		<td bgcolor=white align=center><a href=\"?del_project=1&login_id=".$login_id."&project_id=".OCIResult($q,"PROJECT_ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td></tr>";
		
	}
echo "</table>";
}
	//
//}
echo "</form>";

//Функция добавления проекта пользователю
function add_project($login_id,$project_id,$view_rep,$view_billing,$view_sms_log,$ch_email,$ch_form,$ch_sc,$vsr_billing,$c) {
	$ins1=OCIParse($c,"insert into SC_ACC_PROJECT (login_id,project_id,view_rep,ch_email,ch_form,ch_sc,view_billing,view_sms_log,vsr_billing) 
	values ('".$login_id."','".$project_id."','".$view_rep."','".$ch_email."','".$ch_form."','".$ch_sc."','".$view_billing."','".$view_sms_log."','".$vsr_billing."')");
	OCIExecute($ins1,OCI_DEFAULT); 
	//добавляем доступ ко всем номерам доступа и формам проекта
	if($view_rep==1) {
		$ins2=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) 
		values ('".$login_id."','".$project_id."',0,'all')");
		OCIExecute($ins2,OCI_DEFAULT);
		$ins3=OCIParse($c,"insert into SC_ACC_FORMS (login_id,project_id,form_id,
		date_call,cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min) 
		values ('".$login_id."','".$project_id."',0,
		'y','y','y','y','y','y','y','y','y','y','y')");
		OCIExecute($ins3,OCI_DEFAULT);
		$ins4=OCIParse($c,"insert into sc_acc_frm_obj (project_id,login_id, form_id, obj_id)
		values ('".$project_id."','".$login_id."',0,0)");
		OCIExecute($ins4,OCI_DEFAULT);
	}
	OCICommit($c);
}
//
//Функция удаления проекта пользователю
function del_project($login_id,$project_id,$c) {
	//удаляем права доступа к формам проекта
	$del1=OCIParse($c,"delete from SC_ACC_FRM_OBJ where login_id='".$login_id."' and form_id=:form_id and form_id<>0");

	$q=OCIParse($c,"select t.form_id from SC_ACC_FORMS t
	where t.login_id='".$login_id."' and t.project_id='".$project_id."'");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		$tmp_form_id=OCIResult($q,"FORM_ID");
		OCIBindByName($del1,"form_id",$tmp_form_id);
		OCIExecute($del1,OCI_DEFAULT);		
	}

	$del2=OCIParse($c,"delete from SC_ACC_CDN where project_id='".$project_id."'");
	OCIExecute($del2,OCI_DEFAULT);	

	/*$del2=OCIParse($c,"delete from SC_ACC_FRM_OBJ t where t.login_id='".$login_id."' and t.form_id in (
	select id from SC_FORMS where project_id='".$project_id."'
	)");
	OCIExecute($del2,OCI_DEFAULT);*/
	
	$del3=OCIParse($c,"delete from SC_ACC_FORMS t where t.login_id='".$login_id."' and t.project_id='".$project_id."'");
	OCIExecute($del3,OCI_DEFAULT);
	
	$del5=OCIParse($c,"delete from SC_ACC_PROJECT where login_id='".$login_id."' and project_id='".$project_id."'");
	OCIExecute($del5,OCI_DEFAULT); 
	OCICommit($c);

}
//

?>
<script language="javascript">
parent.adm_usr_prj_fr2.location='adm_usr_prj_num_frame.php';
ch_irs_project();
function ch_irs_project() {
if (document.all.irs_project.value=='') {document.all.add_irs_project.disabled=true;}
else {document.all.add_irs_project.disabled=false;}
}
function sel_prj(project_id) {
	parent.adm_usr_prj_fr2.location='adm_usr_prj_num_frame.php?project_id='+project_id;
}
</script>
