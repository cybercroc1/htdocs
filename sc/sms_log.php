<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','300');
include("sc/sc_session.php");
session_start();
$_SESSION['last_url']='sms_log.php';
extract($_REQUEST);

if (!isset($xlsx) and !isset($csv)) {
echo '<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>';
}

if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['view_sms_log']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

//Формирование дат

if(isset($start_bill_date)) $_SESSION['start_bill_date']=$start_bill_date;
if(isset($end_bill_date)) $_SESSION['end_bill_date']=$end_bill_date;

	if (!isset($_SESSION['start_bill_date'])) {
	$start_rep_date = strtotime("-1 month");
	$_SESSION['start_bill_date'] = date("d.m.Y",$start_rep_date);
	}
	
	if (!isset($_SESSION['end_bill_date'])) {
	$end_rep_date = strtotime("now"); //текущая дата
	$_SESSION['end_bill_date'] = date("d.m.Y",$end_rep_date);
	}

$yesterday = strtotime("- 1 day");
$yesterday = date("d.m.Y",$yesterday);
$curdate = date("d.m.Y");
//

include("sc/sc_conn_string.php");

if(isset($xlsx) or isset($csv) or isset($html)) {
	$filename="SMS - ".$_SESSION['start_bill_date']." - ".$_SESSION['end_bill_date'];
	$head="СМС по проекту: ".$_SESSION['project']['name']." за период с ".$_SESSION['start_bill_date']." по ".$_SESSION['end_bill_date']." (включительно)";

	$sql_text="select 
	to_char(t.datetime,'DD.MM.YYYY HH24:MI:SS') as \"Дата\",
	t.fromphone as \"Имя отправителя\",
	t.phone_list as \"Номера назначения\",
	t.error_num as \"Результат отправки\",
	t.message as \"Текст сообщения\", 
	t.summ_phone as \"Кол-во получателей\",
	t.summ_parts as \"Кол-во частей СМС\"
	from SC_SMS_LOG t
	where t.project_id='".$_SESSION['project']['id']."'
	and t.datetime BETWEEN to_date('".$_SESSION['start_bill_date']."','DD.MM.YYYY') AND to_date('".$_SESSION['end_bill_date']."','DD.MM.YYYY')+1
	order by t.datetime";
}
if(isset($xlsx)) {
	//в эксель
	if(isset($xlsx)) {
		require_once 'sql_to_xlsx.php';
		$sheets[0]['sql']=$sql_text;
		$sheets[0]['filter']='y';
		$sheets[0]['head']=$head;
		$sheets[0]['colwidth']=array(1=>20,2=>15,3=>15,4=>20,5=>100,6=>15,7=>15);
		sql_to_xlsx($c,$sheets,$filename);
		exit();
	}	
}
//в csv
if(isset($csv)) {
	require_once 'sql_to_csv.php';
	$options['head']=$head;
	sql_to_csv($c,$sql_text,$filename,$options);
	exit();
}

echo "<form method=post>";

echo "<nobr><font size=4> СМС-лог - \"".$_SESSION['project']['name']."\"</font> ";

echo " c: <INPUT TYPE=TEXT NAME=start_bill_date value=".$_SESSION['start_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 

echo " по: <INPUT TYPE=TEXT NAME=end_bill_date value=".$_SESSION['end_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A> (включительно)"; 

//
echo " | <INPUT type=submit name=html value=\"Показать отчет\"> | <INPUT type=submit name=xlsx value=\"Скачать в Эксель\"> | <INPUT type=submit name=csv value=\"Скачать в CSV\">";
echo "</nobr></form><hr>";

//HTML
if (isset($html)) {
	echo "<div class=head_div>";
	echo "СМС за период с <b>".$_SESSION['start_bill_date']."</b> по <b>".$_SESSION['end_bill_date']."</b> включительно";
	
	$query = OCIParse($c,$sql_text);
	OCIExecute($query);
	$column_count=oci_num_fields($query);	
	echo "</div>";
	
	echo "<table bgcolor=gray cellspacing=1 cellpadding=2>";	
	echo "<tr>";
	for ($i = 0; $i < $column_count; $i++) {
		$column['name'] = oci_field_name($query,$i+1);
		echo "<th bgcolor=white align=center>".htmlentities($column['name'])."</th>";
	}	
	echo "</tr>";
	
	$rownum=0;
	while ($row=oci_fetch_row($query)) {
		$rownum++;
		echo "<tr>";
		foreach($row as $val) {
			if(preg_match('/^http[s]?:\/\//',$val)) $val="<a href=".$val." target=_blank>".$val."</a>";
			else $val=htmlentities($val);
			echo "<td bgcolor=white align=center>".$val."</td>";
		}
		echo "</tr>";		
	}	
	echo "<tr>";
	echo "<td bgcolor=white align=center colspan='".$column_count."'><b>ИТОГО: Строк ".$rownum."</b></td>";	
	echo "</tr>";
	echo "</table>";
}	

if (!isset($xls_go)) echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>';
?>