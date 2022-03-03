<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
$_SESSION['last_url']='edit_shedule.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0) exit();  
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

if (isset($save)) $shedule_id=save_shedule($shedule_id,$shedule_name,$c);
if (isset($add_worktime) and isset($day_of_week)) add_worktime($shedule_id,$day_of_week,$start_mi,$start_hh,$end_mi,$end_hh,$c);
if (isset($del_worktime)) del_worktime($worktime_id,$c);
if (isset($del_shedule)) {del_shedule($shedule_id,$c); $shedule_id='';}

echo "<form action=edit_shedule.php method=post>"; //POST работает некорректно!
	echo "<font size=4>Расписания</font><br>";

//Выбор списка
	echo "<select name=shedule_id onchange=document.all.ch_shedule.click()>";
	if (!isset($shedule_id) or $shedule_id=='') {
	echo "<option value=''>СОЗДАТЬ РАСПИСАНИЕ</option>";
	$name='';
	}
	else {
	$q=OCIParse($c,"select * from sc_shedule where id='".$shedule_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	$name=OCIResult($q,"NAME");
	echo "<option value=''>СОЗДАТЬ РАСПИСАНИЕ</option>";
	}
	$q=OCIParse($c,"select * from sc_shedule where project_id='".$_SESSION['project']['id']."' order by name");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>
	<input type=submit name=ch_shedule value=ВЫБРАТЬ>";
		if (isset($shedule_id) and $shedule_id<>'') {
		echo " <a href=\"javascript:del_shedule('".$shedule_id."')\"><img src=del.gif title=\"Удалить\" border=0></a>";
		}
	echo "<hr>";
//

echo "<table>";
echo "<tr><td><font size=3><b>Название:</b></font></td><td><input type=text name=shedule_name value=\"".$name."\"></td></tr>";
echo "</table>";
echo "<tr><td><input type=submit name=save value=Сохранить><hr>";	

if (isset($shedule_id) and $shedule_id<>'') {
	//Проекты

	echo "<font size=4>Рабочее время</font><br>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white align=center><b>Дени недели</b></td>
	<td bgcolor=white align=center><b>с</b></td>
	<td bgcolor=white align=center><b>по</b></td>
	<td bgcolor=white></td>";

	echo "</tr>";

	//Добавить проект пользователю
	echo "<tr>";
	echo "<td bgcolor=green><nobr>
	<input type=checkbox name=day_of_week[] value=1>ПН</input>
	<input type=checkbox name=day_of_week[] value=2>ВТ</input>
	<input type=checkbox name=day_of_week[] value=3>СР</input>	
	<input type=checkbox name=day_of_week[] value=4>ЧТ</input>	
	<input type=checkbox name=day_of_week[] value=5>ПТ</input>
	<input type=checkbox name=day_of_week[] value=8>Рабочий выходной</input></nobr><br>			
	<nobr>
	<input type=checkbox name=day_of_week[] value=6><font color=red>СБ</font></input>	
	<input type=checkbox name=day_of_week[] value=7><font color=red>ВС</font></input>	
	<input type=checkbox name=day_of_week[] value=9><font color=red>Праздник</font></input>	
	</nobr>
	</td>
	<td bgcolor=green align=center>
	<select name=start_hh>
	<option value=''></option>
	<option>00</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option>
	</select>
	:
	<select name=start_mi>
	<option value=''></option>
	<option>00</option><option>05</option><option>10</option><option>15</option><option>20</option><option>25</option><option>30</option><option>35</option>
	<option>40</option><option>45</option><option>50</option><option>55</option>
	</select>	
	</td>
	<td bgcolor=green align=center>
	<select name=end_hh>
	<option value=''></option>
	<option>00</option><option>01</option><option>02</option><option>03</option><option>04</option><option>05</option><option>06</option><option>07</option><option>08</option><option>09</option><option>10</option><option>11</option><option>12</option><option>13</option><option>14</option><option>15</option><option>16</option><option>17</option><option>18</option><option>19</option><option>20</option><option>21</option><option>22</option><option>23</option>
	</select>
	:
	<select name=end_mi>
	<option value=''></option>
	<option>00</option><option>05</option><option>10</option><option>15</option><option>20</option><option>25</option><option>30</option><option>35</option>
	<option>40</option><option>45</option><option>50</option><option>55</option>
	</select>	
	</td>
	";
	echo "<td bgcolor=green><input type=submit name=add_worktime value=\"Добавить время\"></td></tr>";
	//

	$q=OCIParse($c,"select t.id,w.name,
nvl(to_char(t.start_time,'HH24:MI'),'полночь') start_time,
nvl(to_char(t.end_time,'HH24:MI'),'полночь') end_time 
from sc_shedule_times t, sc_day_of_week w 
where t.day_of_week=w.num
and t.shedule_id='".$shedule_id."'
and t.project_id='".$_SESSION['project']['id']."'
order by t.day_of_week,to_char(t.start_time,'HH24:MI')");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		echo "<td bgcolor=white align=center><b>".OCIResult($q,"NAME")."</b></td>
		<td bgcolor=white align=center><b>".OCIResult($q,"START_TIME")."</b></td>
		<td bgcolor=white align=center><b>".OCIResult($q,"END_TIME")."</b></td>
		<td bgcolor=white align=center><a href=\"?del_worktime=1&shedule_id=".$shedule_id."&worktime_id=".OCIResult($q,"ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td></tr>";
		
	}
echo "</table>";
}
	//

echo "</form>";

//Функция добавления и изменения расписания
function save_shedule($shedule_id,$shedule_name,$c) {
	if ($shedule_id=='') {
	$q=OCIParse($c,"select seq_shedule_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_shedule_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_shedule (id,name,project_id) values ('".$new_shedule_id."','".$shedule_name."','".$_SESSION['project']['id']."')");
	OCIExecute($ins,OCI_DEFAULT); 
	OCICommit($c);
	$shedule_id=$new_shedule_id;
	}
	else {
	$upd=OCIParse($c,"update sc_shedule set name='".$shedule_name."' where id='".$shedule_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
	}
return $shedule_id;
}
//

//Функция удаления списка
function del_shedule($shedule_id,$c) {
	$del=OCIParse($c,"delete from sc_shedule where id='".$shedule_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//

//Функция добавления времени
function add_worktime($shedule_id,$day_of_week,$start_mi,$start_hh,$end_mi,$end_hh,$c) {
	for ($i=0; $i<count($day_of_week); $i++) {
		if ($start_hh=='') {$start_time='';} else {if($start_mi==''){$start_mi='00';} else {} $start_time=$start_hh.":".$start_mi;}
		if ($end_hh=='') {$end_time='';} else {if($end_mi==''){$end_mi='00';} else {} $end_time=$end_hh.":".$end_mi;}
		//if ($start_time<>'' or $end_time<>'') {
			$ins=OCIParse($c,"insert into sc_shedule_times (id,start_time,end_time,day_of_week,shedule_id,project_id) 
			values (seq_times_id.nextval,to_date('".$start_time."','HH24:MI'),to_date('".$end_time."','HH24:MI'),'".$day_of_week[$i]."',
			'".$shedule_id."','".$_SESSION['project']['id']."')");
			OCIExecute($ins,OCI_DEFAULT); 
			OCICommit($c);
		//}
	}
}
//
//Функция удаления времени
function del_worktime($worktime_id,$c) {
	$del=OCIParse($c,"delete from sc_shedule_times where id='".$worktime_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//

?>
<script language="javascript">
document.all.ch_shedule.style.display='none';
function ch_shedule() {
if (document.all.shedule_id.value=='') {document.all.add_shedule.disabled=true;}
else {document.all.add_shedule.disabled=false;}
}
function del_shedule(shedule_id) {
if (confirm('Действительно хотите УДАЛИТЬ РАСПИСАНИЕ ?')) document.location='?del_shedule=1&shedule_id='+shedule_id;
}

</script>