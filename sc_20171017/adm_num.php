<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //if (!isset($_SESSION['i'])) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if (isset($add_number)) add_number($new_number,$project_id,$c);
if (isset($del_number)) del_number($number,$c);

echo "<form name=projects action=adm_num.php method=post>";
	echo "<font size=4>Администрирование</font><br>";
	echo "<a href=adm_prj.php>Проекты</a> | <font size=4>Номера</font> | <a href=adm_usr.php>Пользователи</a> | <a href=adm_holidays.php>Специальные дни</a><hr>";
	
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white align=center><b>Номер доступа</b></td>
	<td bgcolor=white align=center><b>Название проекта</b></td>
	<td bgcolor=white align=center></td>";
	echo "</tr>";
	
	//Добавить объект
	echo "<tr>
	<td bgcolor=green><input type=text name=new_number onchange=ch_new_number()></td>";
	
	$q=OCIParse($c,"select id,name from sc_projects order by name");
	OCIExecute($q,OCI_DEFAULT);
		echo "<td bgcolor=green><select name=project_id onchange=ch_new_number()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
		echo "</select></td>";
	echo "<td bgcolor=green><input type=submit name=add_number disabled value=\"Добавить номер\"></td>";
	//
	//Список проектов
	$q=OCIParse($c,"select p.phone,p.project_id,pr.name from sc_phones p, sc_projects pr
where pr.type='irs' and p.project_id=pr.id(+) order by phone");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr id =tr_".OCIResult($q,"PHONE").">
	<td bgcolor=white><b>".OCIResult($q,"PHONE")."</b></td>
	<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
	<td bgcolor=white align=center><a href=\"?del_number=1&number=".OCIResult($q,"PHONE")."\"><img src=del.gif title=\"Удалить\" border=0></a></td>";
	echo "</tr>";
	}
	echo "</table>";
	//

echo "</form><hr>";

function add_number($new_number,$project_id,$c) {
$q=OCIParse($c,"select count(*) count from sc_phones where phone=trim('".$new_number."')");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo "<font color=red>ОШИБКА! номер \"".$new_number."\" уже существует</font>";}
	else {
	$ins=OCIParse($c,"insert into sc_phones (phone,project_id) values (trim('".$new_number."'),'".$project_id."')");
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
function ch_new_number() {
	if (document.all.new_number.value==''||document.all.project_id.value=='') {
	document.all.add_number.disabled=true;
	} else {
	document.all.add_number.disabled=false;
	}
}
</script>