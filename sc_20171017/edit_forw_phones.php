<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7'>
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['ch_sc'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

//Функция сохранения значений

if (!isset($doljnost)) $doljnost='-';
if (!isset($email)) $email='-'; 
if (!isset($grafik)) $grafik='-';
if (!isset($coment)) $coment='-';
if (!isset($otdel)) $otdel='-';
if (isset($save)) save($fio_id,$fio,$doljnost,$email,$grafik,$coment,$otdel,$c);

if (isset($add_phone)) add_phone($fio_id,$phone,$ext,$c);
if (isset($del_phone)) del_phone($fio_id,$phone_id,$c);

if (isset($up)) up($fio_id,$phone_id,$ordering,$c);
if (isset($down)) down($fio_id,$phone_id,$ordering,$c);

echo "<form action=edit_forw_phones.php method=post>";
echo "<font size=3><a href=edit_forw_list.php?list_id=".$list_id.">Редактирование списка переадресации</a></font> | <font size=4>Редактирование сотрудников</font><hr>";


//Выбор сотрудника
if (isset($fio_id) and $fio_id<>'') {
	echo "<select name=fio_id onchange=ch_fio_id()>";
	
	$q=OCIParse($c,"select t.id,t.list_id,
	replace(t.fio,'\"','&quot;') fio,
replace(t.doljnost,'\"','&quot;') doljnost,
t.project_id,t.ordering,
replace(t.email,'\"','&quot;') email,
replace(t.otdel,'\"','&quot;') otdel,
replace(t.grafik,'\"','&quot;') grafik,
replace(t.coment,'\"','&quot;') coment
from sc_forw_fio t
	where t.id='".$fio_id."' and t.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' ");

//	$q=OCIParse($c,"select * from sc_forw_fio t
//	where t.id='".$fio_id."' and t.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' ");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"FIO")."</option>";
	$fio=OCIResult($q,"FIO");
	$doljnost=OCIResult($q,"DOLJNOST");
	$email=OCIResult($q,"EMAIL");
	$grafik=OCIResult($q,"GRAFIK");
	$coment=OCIResult($q,"COMENT");
	$otdel=OCIResult($q,"OTDEL");
	
	$q=OCIParse($c,"select * from sc_forw_fio
	where list_id='".$list_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' 
	order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"FIO")."</option>";
	}
	echo "</select>
	<input type=submit name=ch_fio value=ВЫБРАТЬ><hr>";
//
	echo "<input type=hidden name=list_id value=".$list_id.">";

	$q=OCIParse($c,"select * from sc_forw_list where id='".$list_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	echo "<table>";
	echo "<tr><td><font size=3><b>ФИО:</b></font><input type=text size=54 name=fio value=\"".$fio."\"></td>";
	if (OCIResult($q,"OTDEL")=='1') echo "<td><font size=3><b>Отдел:</b></font><input type=text size=55 name=otdel value=\"".$otdel."\"></td></tr>";

	if (OCIResult($q,"DOLJNOST")=='1') echo "<tr><td><font size=3><b>Должность:</b></font><input type=text size=45 name=doljnost value=\"".$doljnost."\"></td>";
	if (OCIResult($q,"GRAFIK")=='1') echo "<td><font size=3><b>График работы:</b></font><input type=text size=45 name=grafik value=\"".$grafik."\"></td></tr>";
	if (OCIResult($q,"COMENT")=='1') echo "<tr><td colspan=2><font size=3><b>Комментарий:</b><br></font><textarea cols=105 rows=5 name=coment>".$coment."</textarea></td></tr>";
	if (OCIResult($q,"EMAIL")=='1') echo "<tr><td><font size=2><b>E-mail:</b></font><input type=text size=50 name=email value=\"".$email."\"></td></tr>";
	echo "</td>
	</tr>";
	echo "</table>";
	echo "<tr><td><input type=submit name=save value=Сохранить><hr>";	

	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white><b>Номер</b></td>
	<td bgcolor=white><b>Доб.</b></td>
	<td bgcolor=white colspan=2></td>";
	echo "</tr>";
	
	//Добавить номер
	echo "<tr>
	<td bgcolor=green><input type=text name=phone size=35></td>";
	echo "<td bgcolor=green><input type=text name=ext size=4></td></td>";
	echo "<td bgcolor=green colspan=2><input type=submit name=add_phone value=ДОБАВИТЬ></td></tr>";
	//
	//Номера
	echo "<font size=4>Номера</font><font size=3>";	
	
	$q=OCIParse($c,"select * from sc_forw_phone where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	while (OCIFetch($q)) {
	echo "<td bgcolor=white>".OCIResult($q,"NAME")."</td>";
	echo "<td bgcolor=white align=center>".OCIResult($q,"EXT")."</td>";
	
	echo "<td bgcolor=white align=center>";
	echo "<a href=\"?up=1&phone_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&fio_id=".$fio_id."&list_id=".$list_id."\"><img border=0 src=up.gif></a>
		<a href=\"?down=1&phone_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&fio_id=".$fio_id."&list_id=".$list_id."\"><img border=0 src=down.gif></a>
		</td>";

	echo "<td bgcolor=white align=center>
	<a href=\"?del_phone=1&phone_id=".OCIResult($q,"ID")."&fio_id=".$fio_id."&list_id=".$list_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";
	echo "</td>";

	echo "</tr>";
	}
	echo "</table>";
}
echo "</form>";

//Функция сохранения
function save($fio_id,$fio,$doljnost,$email,$grafik,$coment,$otdel,$c) {

$qqq="update sc_forw_fio set fio='".$fio."'";
if ($doljnost<>'-') $qqq.=", doljnost='".$doljnost."'";
if ($email<>'-') $qqq.=", email='".$email."'";
if ($grafik<>'-') $qqq.=", grafik='".$grafik."'";
if ($coment<>'-') $qqq.=", coment='".$coment."'";
if ($otdel<>'-') $qqq.=", otdel='".$otdel."'";
$qqq.="where id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'";
$upd=OCIParse($c,$qqq);
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}	
//
//Функция вверх
	function up($fio_id,$phone_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ordering),1) perv_ordering from sc_forw_phone
where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update sc_forw_phone set ordering='".$ordering."'
	where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update sc_forw_phone set ordering='".$perv_ordering."' where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and id='".$phone_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//
//Функция вниз
	function down($fio_id,$phone_id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_forw_phone
where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_forw_phone set ordering='".$ordering."'
		where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_forw_phone set ordering='".$next_ordering."' where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and id='".$phone_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//


//Функция добавления номера
function add_phone($fio_id,$phone,$ext,$c) {
	$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_forw_phone
where fio_id='".$fio_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$ordering=OCIResult($q,"ORDERING");
	
	$ins=OCIParse($c,"insert into sc_forw_phone (id,fio_id,phone,ext,name,project_id,ordering)
	values (
	SEQ_FORW_PHONES.nextval,
	'".$fio_id."',
	regexp_replace('".$phone."','[^0-9]',''),
	'".$ext."',
	'".$phone."',
	'".$_SESSION['project_id'][$_SESSION['i']]."',
	'".$ordering."'
	)");
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);	
}
//
//Функция удаления значения
function del_phone($fio_id,$phone_id,$c) {
	$del=OCIParse($c,"delete from sc_forw_phone 
	where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and fio_id='".$fio_id."' and id='".$phone_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}
//
?>
<script language='javascript'>
document.all.ch_fio.style.display='none';
function ch_fio_id() {
	document.all.ch_fio.click();
}
</script>

