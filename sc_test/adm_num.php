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
<body class="body_marign">
<?php  
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

$_SESSION['adm_last_url']='adm_num.php';

include("../../sc_conf/sc_conn_string");

if (isset($add_number)) add_number($new_number,$project_id,$new_number_name,$c);
if (isset($del_number)) del_number($number,$c);

if(!isset($find_string)) $find_string='';

echo "<form name=projects action=adm_num.php method=post>";
	echo "<font size=4>Номера</font> ";

	echo "<table class=white_table>
	<tr>
	<td align=center><b>Номер доступа</b></td>
	<td align=center><b>Название номера</b></td>
	<td align=center><b>Название проекта</b></td>
	<td align=center></td>";
	echo "</tr>";
	
	//Добавить объект
	echo "<tr>
	<td bgcolor=green><input type=text name=new_number onkeyup=ch_new_number() onpaste=ch_new_number()></td>
	<td bgcolor=green><input type=text name=new_number_name onkeyup=ch_new_number() onpaste=ch_new_number()></td>";
	
	$q=OCIParse($c,"select id,name from sc_projects where id<>0 order by name");
	OCIExecute($q,OCI_DEFAULT);
		echo "<td bgcolor=green><select name=project_id onchange=ch_new_number()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$_SESSION['project']['id']?" selected":"").">".OCIResult($q,"NAME")."</option>";
		}
		echo "</select></td>";
	echo "<td bgcolor=green><input type=submit name=add_number disabled value=\"Добавить номер\"></td>";
	//
	//Список проектов
	$q=OCIParse($c,"select p.phone,p.phone_name,p.project_id,pr.name from sc_phones p, sc_projects pr
where pr.type='irs' and p.project_id=pr.id(+) order by phone");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr class='selectable_row' id=tr_".OCIResult($q,"PHONE").">
	<td><b>".OCIResult($q,"PHONE")."</b></td>
	<td><b>".OCIResult($q,"PHONE_NAME")."</b></td>
	<td><b>".OCIResult($q,"NAME")."</b></td>
	<td align=center><a href=\"?del_number=1&number=".OCIResult($q,"PHONE")."\"><img src=del.gif title=\"Удалить\" border=0></a></td>";
	echo "</tr>";
	}
	echo "</table>";
	//

echo "</form><hr>";

function add_number($new_number,$project_id,$new_number_name,$c) {
$q=OCIParse($c,"select count(*) count from sc_phones where phone=trim('".$new_number."')");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo "<font color=red>ОШИБКА! номер \"".$new_number."\" уже существует</font>";}
	else {
	$ins=OCIParse($c,"insert into sc_phones (phone,project_id,phone_name) values (trim('".$new_number."'),'".$project_id."',trim('".$new_number_name."'))");
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);
	}
}

function del_number($number,$c) {
$del=OCIParse($c,"delete from sc_phones where phone='".$number."'");
OCIExecute($del,OCI_DEFAULT);
OCICommit($c);
}

?>
<script language="javascript">
var t;
function fn_find(val) {
	clearTimeout(t);
	if(val.length==0 || val.length>=2) t=setTimeout('frm.submit()',2000);
}
function ch_new_number() {
	if (document.all.new_number.value==''||document.all.project_id.value=='0') {
	document.all.add_number.disabled=true;
	} else {
	document.all.add_number.disabled=false;
	}
}
</script>