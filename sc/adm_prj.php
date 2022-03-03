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
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script src="func.row_select.js"></script>
<script>
function sel_prj(id) {
	//alert(document.forms[1].name);
	alert('ddsdasdasdsdasd');
	parent.adm_prj_fr2.location='adm_prj_settings.php?prj_id='+id;
	frm_sel_prj.project_id.value=id;
	frm_sel_prj.submit();
//	parent.parent.fr0.location=parent.parent.fr0.location+'?project_id='+id;
}
</script>
<body class="body_marign">
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

$_SESSION['adm_last_url']='adm_prj.php';

include("sc/sc_conn_string.php");
include("sc/sc_path.php");
if(!isset($new_project_platform)) $new_project_platform="";

/*if (isset($ch_platform)) {
echo substr($id,3)."-".$check."-".$platform;
if($check=='false') $platform='';
$upd=OCIParse($c,"update sc_projects set platform='".$platform."' where id='".substr($id,3)."'");
OCIExecute($upd);
OCICommit($c);
exit();
}*/
if (isset($ch_out_prefix)) {
echo substr($id,3)."-".trim($out_prefix);
$upd=OCIParse($c,"update sc_projects set out_prefix='".trim($out_prefix)."' where id='".substr($id,3)."'");
OCIExecute($upd);
OCICommit($c);
exit();
}
if (isset($ch_hide)) {
echo substr($id,3)."-".$check;
if($check=='false') $hidden=''; else $hidden='y';
$upd=OCIParse($c,"update sc_projects set hidden='".$hidden."' where id='".substr($id,3)."'");
OCIExecute($upd);
OCICommit($c);
exit();
}

if (isset($order_by)) {
$_SESSION['adm_prj_orderby']=$order_by;
echo "<script> parent.location.reload()</script>";
}

echo "<form name='frm_sel_prj' action='menu.php' target='fr0' method='post'><input type=hidden name=project_id><input type=hidden name=no_refresh></form>";

if (isset($add_project)) add_project($new_project_name,$new_project_platform,$c);

if (isset($del_prj)) {
echo "<form method=post action=adm_prj.php>";
$q=OCIParse($c,"select name from sc_projects
where id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$project_name=OCIResult($q,"NAME");
echo "<font size=4 color=red>Внимание! Вместе с проектом \"".OCIResult($q,"NAME")."\" будут удалены:</font><br>
<font size=4>";

$q=OCIParse($c,"select count(*) cnt from sc_call_base
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." звонков<br>";

$q=OCIParse($c,"select count(*) cnt from sc_call_report
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." отчетов<br>";

$q=OCIParse($c,"select count(*) cnt from sc_forms
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." форм<br>";

$q=OCIParse($c,"select count(*) cnt from sc_forw_list
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." списков переадресации<br>";

$q=OCIParse($c,"select count(*) cnt from sc_punkt
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." пунктов<br>";

$q=OCIParse($c,"select count(*) cnt from sc_body
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." блоков<br>";

$q=OCIParse($c,"select count(*) cnt from sc_phones
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." номеров доступа<br>";

$q=OCIParse($c,"select count(*) cnt from sc_role
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ролей<br>";

$q=OCIParse($c,"select count(*) cnt from sc_shedule
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." расписаний<br>";

$q=OCIParse($c,"select count(*) cnt from 
(select login_id,count(*) cnt from sc_role
where login_id in 
(select login_id from sc_role where project_id='".$del_prj."')
group by login_id)
where cnt='1'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." пользователей<br>";

echo "Предпринята попытка удалить папку: ".$project_name.",<br>";
$i=0;
foreach (@glob($path_to_folders.$project_name."\\*.*") as $filename) {
$i++;
}
echo "содержащую ".$i." файлов<br>";

echo "</font>
Напишите \"Удалить\", чтобы удалить<br>
<input type=text name=sure>
<input type=hidden name=del_project_id value=".$del_prj.">
<input type=submit name=del_prj_go value=Удалить>
<hr>";
}

if (isset($del_prj_go) and isset($del_project_id) and $del_project_id>0) {
	if ($del_prj_go<>$sure) {echo "<font color=red size=4>Проект НЕ удален</font><hr>";}
	else {
		set_time_limit(0);
		$q=OCIParse($c,"select id from sc_call_base
		where project_id='".$del_project_id."' order by date_call");
		$del=OCIParse($c,"delete from sc_call_base
		where id=:del_prj");
		OCIExecute($q,OCI_DEFAULT);
			while (OCIFetch($q)) {
			$del_prj_tmp=OCIResult($q,"ID");
			OCIBindByName($del,":del_prj",$del_prj_tmp);
			OCIExecute($del,OCI_COMMIT_ON_SUCCESS);
		}
		OCIFreeStatement($q);	
		OCIFreeStatement($del);

		$del=OCIParse($c,"delete from sc_forms where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);
	
		$del=OCIParse($c,"delete from sc_forw_list where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);	
	
		$del=OCIParse($c,"delete from sc_punkt where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);
		
		$del=OCIParse($c,"delete from sc_body where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);
		
		$del=OCIParse($c,"delete from sc_phones where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);
	
		$del=OCIParse($c,"delete from sc_shedule where project_id='".$del_project_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);	

		$del=OCIParse($c,"delete from sc_login where id in (
		select login_id from 
		(select login_id,count(*) cnt from sc_role
		where login_id in 
		(select login_id from sc_role where project_id='".$del_project_id."')
		group by login_id)
		where cnt='1')");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);
		OCIFreeStatement($del);
	
		$q=OCIParse($c,"select name from sc_projects where id='".$del_project_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$project_name=OCIResult($q,"NAME");
	
		$del=OCIParse($c,"delete from sc_projects where id='".$del_project_id."'");
		if (OCIExecute($del,OCI_DEFAULT)) $deleted=1; else $deleted=0;
		OCICommit($c);
		OCIFreeStatement($del);
	
		//удаление папки
		foreach (@glob($path_to_folders.$project_name."\\*.*") as $filename) {
			if (@!unlink($path_to_folders.$project_name."\\".basename($filename))) echo "<br><font color=red>Файл: ".$path_to_folders.$project_name."\\".basename($filename)." не удален!</font>";
			echo "<br>удален файл: ".$path_to_folders.$project_name."\\".basename($filename);
		}
		if (@!rmdir($path_to_folders.$project_name)) echo "<br><font color=red>Папка: ".$path_to_folders.$project_name." не удалена!</font>";
		//echo "<br>удалена папка: ".$path_to_folders.$project_name;
		//
		
		if ($deleted==1 and $del_project_id==$_SESSION['project']['id']) echo "<script>sel_prj(0);</script>";
	}
}

echo "<form name=projects action=adm_prj.php method=post>";
	echo "<font size=4>Проекты</font>";
	
	echo "<table id='tbl' class='white_table'>
	<tr>
	<td align=center><b>ID</b></td>
	<td align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.name'>Название</b></td>";
	//echo "<td align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.platform'>Октелл</b></td>
	echo "<td align=center><b><a label='Префикс для исходящих звонков, для привязки к исходящим маршрутам' target='adm_prj_blank_frame' href='?order_by=p.out_prefix'>Исх.префикс</b></td>";
	echo "<td align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.start_date'>Дата создания</a></b></td>
	<td align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.hidden'>Скрыт</b></td>
	<td align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.last_call_date'>Дата посл.зв.</b></td>
	<td align=center><b>Номера</b></td>
	<td align=center><b>Пользователи</b></td>
	<td align=center></td>";

	echo "</tr>";
	
	//Добавить объект
	echo "<tr>
	<td bgcolor=green colspan=2><input type=text name=new_project_name onkeyup=ch_new_project_name()></td>";
	//echo <td bgcolor=green align=center><input type=checkbox checked name=new_project_platform value='Oktell'></td>";
	echo "<td bgcolor=green colspan=4><input type=submit name=add_project disabled value=\"Создать проект\"></td>";
	//
	//Список проектов
	if(!isset($_SESSION['adm_prj_orderby']) or $_SESSION['adm_prj_orderby']=='') {$order_by='p.name';} else {$order_by=$_SESSION['adm_prj_orderby'];}
	$q=OCIParse($c,"select p.id,p.name,p.start_date, p.platform, p.out_prefix ,p.last_call_date,p.hidden from sc_projects p  where p.type='irs' order by ".$order_by.", p.name");
	OCIExecute($q,OCI_DEFAULT);
	$q_num=OCIParse($c,"select phone from sc_phones where project_id=:project_id order by phone");
	$q_user=OCIParse($c,"select l.id,l.login, 
decode(r.view_rep,1,'#80FF80','#FF8080') view_rep,
decode(r.ch_email,1,'#80FF80','#FF8080') ch_email,
decode(r.ch_form,1,'#80FF80','#FF8080') ch_form,
decode(r.ch_sc,1,'#80FF80','#FF8080') ch_sc,
decode(r.view_billing,1,'#80FF80','#FF8080') view_billing
from sc_login l, SC_ACC_PROJECT r
where l.id=r.login_id and r.project_id=:project_id");
	while (OCIFetch($q)) {
	echo "<tr id =tr_".OCIResult($q,"ID")." class='selectable_row'>
	<td onclick=\"click_row(this.parentNode,'sel');sel_prj('".OCIResult($q,"ID")."')\"><b>".OCIResult($q,"ID")."</b></td>
	<td onclick=\"click_row(this.parentNode,'sel');sel_prj('".OCIResult($q,"ID")."')\"><b>".OCIResult($q,"NAME")."</b></td>";
	//echo "<td align=center><b><input type=checkbox id='pl_".OCIResult($q,"ID")."' name=platform value='Oktell' ".(OCIResult($q,"PLATFORM")=="Oktell"?" checked":"")." onclick='ch_platform(this.id,this.checked,this.value)'></b></td>";
	echo "<td align=center><b><input type=text size=8 id='px_".OCIResult($q,"ID")."' name=out_prefix value='".OCIResult($q,"OUT_PREFIX")."' onchange='ch_out_prefix(this.id,this.value)'></b></td>";
	echo "<td onclick=\"click_row(this.parentNode,'sel');sel_prj('".OCIResult($q,"ID")."')\"><b>".OCIResult($q,"START_DATE")."</b></td>
	<td align=center><b><input type=checkbox id='hi_".OCIResult($q,"ID")."' name=hidden value='y' ".(OCIResult($q,"HIDDEN")=="y"?" checked":"")." onclick='ch_hide(this.id,this.checked,this.value)'></b></td>
	<td onclick=\"click_row(this.parentNode,'sel');sel_prj('".OCIResult($q,"ID")."')\"><b>".OCIResult($q,"LAST_CALL_DATE")."</b></td>";
	$v_id=OCIResult($q,"ID");
	OCIBindByName($q_num,":project_id",$v_id);
	OCIBindByName($q_user,":project_id",$v_id);
	
	OCIExecute($q_num,OCI_DEFAULT);
	echo "<td onclick=\"click_row(this.parentNode,'sel');sel_prj('".OCIResult($q,"ID")."')\"><b>";
		while(OCIFetch($q_num)) {
		echo OCIResult($q_num,"PHONE")."<br>";
		}
	echo "</b></td>";

		OCIExecute($q_user,OCI_DEFAULT);
	echo "<td><b>";
		echo "<table>";
		while(OCIFetch($q_user)) {
		echo "<tr>";
		
		echo "<td><a href=\"adm_usr_frame.php?login_id=".OCIResult($q_user,"ID")."\" target=adm_fr2><b>".OCIResult($q_user,"LOGIN")."</b></td>";
		echo "<td bgcolor=".OCIResult($q_user,"VIEW_REP").">Отчеты</td>
			<td bgcolor=".OCIResult($q_user,"VIEW_BILLING").">Биллинг</td>
			<td bgcolor=".OCIResult($q_user,"CH_EMAIL").">Ред.e-mail</td>
			<td bgcolor=".OCIResult($q_user,"CH_FORM").">Ред.формы</td>
			<td bgcolor=".OCIResult($q_user,"CH_SC").">Ред.сценарий</td>";
		
		echo "</tr>";
		}
		echo "</table>";
	echo "</b></td>
	<td bgcolor=white><a href=\"adm_prj.php?del_prj=".OCIResult($q,"ID")."\"><img src=del.gif title=\"Удалить проект\" border=0></a></td>";
	echo "</tr>";
	}
	echo "</table>";
	//

echo "</form><hr>";

function add_project($new_project_name,$new_project_platform,$c) {
include("sc/sc_path.php");
$q=OCIParse($c,"select count(*) count from sc_projects where trim(upper(name))=trim(upper('".$new_project_name."'))");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo "<font color=red>ОШИБКА! Проект с именем \"".$new_project_name."\" уже существует</font>";}
	else if (@!mkdir($path_to_folders.$new_project_name)) {echo "<font color=red>ОШИБКА! не удалось создать папку с именем \"".$new_project_name."\"</font>";}	
	else {
	$ins=OCIParse($c,"insert into sc_projects (id,name,start_date,type,platform,last_call_date) 
	values (seq_project_id.nextval,'".trim($new_project_name)."',sysdate,'irs','".$new_project_platform."',sysdate)");
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);
	}
}

?>
<iframe width="1px" height="1px" name="adm_prj_blank_frame"></iframe>
<script language="javascript">
function ch_new_project_name() {
	if (document.all.new_project_name.value=='') {
	document.all.add_project.disabled=true;
	} else {
	document.all.add_project.disabled=false;
	}
}
//function ch_platform(project_id,check,val) {
//	adm_prj_blank_frame.location='adm_prj.php?ch_platform&id='+project_id+'&check='+check+'&platform='+val; 
//}
function ch_out_prefix(project_id,val) {
	adm_prj_blank_frame.location='adm_prj.php?ch_out_prefix&id='+project_id+'&out_prefix='+val; 
}
function ch_hide(project_id,check,val) {
	adm_prj_blank_frame.location='adm_prj.php?ch_hide&id='+project_id+'&check='+check; 
}
function sel_prj(id) {
	//alert(document.forms[1].name);
	frm_sel_prj.project_id.value=id;
	frm_sel_prj.submit();
	parent.adm_prj_fr2.location='adm_prj_settings.php?prj_id='+id;
//	parent.parent.fr0.location=parent.parent.fr0.location+'?project_id='+id;
}
/*function ch_order_by(order_by) {
	adm_prj_blank_frame.location='adm_prj.php?order_by='+order_by; 
}*/
</script>
