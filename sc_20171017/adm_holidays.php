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

//удаляем старые праздники
$del=OCIParse($c,"delete from sc_holidays where dat<trunc(sysdate,'DD')");
OCIExecute($del,OCI_COMMIT_ON_SUCCESS);
//

if (isset($add_day)) add_day($dat,$holiday_name,$day_type,$c);
if (isset($del_day)) del_day($dat,$c);

echo "<form action=adm_holidays.php method=post>"; 
	echo "<font size=4>Администрирование</font><br>";
	echo "<a href=adm_prj.php>Проекты</a> | <a href=adm_num.php>Номера</a> | <a href=adm_usr.php>Пользователи</a> | <font size=4>Специальные дни</font><hr>";
	
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white align=center><b>Дата</b></td>
	<td bgcolor=white align=center><b>Название</b></td>
	<td bgcolor=white align=center><b>Тип дня</b></td>
	<td bgcolor=white><b></b></td>";

	echo "</tr>";

	//Добавить день
	echo "<tr>
	<td bgcolor=green nowrap><INPUT TYPE=TEXT NAME=dat SIZE=9 onchange=ch_day_type()>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].dat);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A></td>
	<td bgcolor=green><input type=text name=holiday_name></td>
	<td bgcolor=green><select name=day_type onchange=ch_day_type()>
	<option value=''></option>
	<option value='1'>Понедельник</option>
	<option value='2'>Вторник</option>
	<option value='3'>Среда</option>
	<option value='4'>Четверг</option>
	<option value='5'>Пятница</option>
	<option value='6'>Суббота</option>
	<option value='7'>Воскресенье</option>
	<option value='9'>Праздник</option>
	<option value='8'>Рабочий выходной</option>
	</select>";
	
	echo "<td bgcolor=green><input type=submit name=add_day disabled value=\"Добавить день\"></td></tr>";
	//

	$q=OCIParse($c,"select to_char(h.dat,'DD.MM.YYYY') dat,to_char(h.dat,'DD-Mon-YYYY (Dy)') d,h.coment,w.name day_of_week 
	from sc_holidays h, sc_day_of_week w
where h.day_of_week=w.num
and h.dat>=trunc(sysdate,'DD')
order by h.dat");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		echo "<td bgcolor=white><b>".OCIResult($q,"D")."</b></td>
			<td bgcolor=white><b>".OCIResult($q,"COMENT")."</b></td>		
			<td bgcolor=white><b>".OCIResult($q,"DAY_OF_WEEK")."</b></td>		
		<td bgcolor=white align=center><a href=\"?del_day=1&dat=".OCIResult($q,"DAT")."\"><img src=del.gif title=\"Удалить\" border=0></a></td></tr>";
		
	}
echo "</table>";
	//

echo "</form>";

//Функция добавления проекта пользователю
function add_day($dat,$holiday_name,$day_type,$c) {
	$ins=OCIParse($c,"insert into sc_holidays (dat,day_of_week,coment) 
	values (to_date('".$dat."','DD.MM.YYYY'),'".$day_type."','".$holiday_name."')");
	OCIExecute($ins,OCI_DEFAULT); 
	OCICommit($c);
}
//
//Функция удаления проекта пользователю
function del_day($dat,$c) {
	$del=OCIParse($c,"delete from sc_holidays where dat=to_date('".$dat."','DD.MM.YYYY')");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//

?>
<script language="javascript">
function ch_day_type() {
if (document.all.dat.value==''||document.all.day_type.value=='') {document.all.add_day.disabled=true;}
else {document.all.add_day.disabled=false;}
}
</script>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>