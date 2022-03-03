<?php 
ini_set('max_execution_time','600');
function my_error_handler($errno, $errstr, $errfile, $errline) {
	header('Content-Type: text/plain');
	header('Content-Disposition: attachment;filename=error.txt');
	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0	
	echo "Error ".$errno." file: ".$errfile." line: ".$errline." ".$errstr;
	exit();
}

extract($_REQUEST);
if(isset($xlsx) or isset($csv)) {
	/** Error reporting */
	set_error_handler ("my_error_handler");
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	ini_set('memory_limit', '2048M');
}
require_once "auth.php";

if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['view_sms_log']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

//Формирование дат
if(isset($start_rep_date)) $_SESSION['start_rep_date']=$start_rep_date;
if(isset($end_rep_date)) $_SESSION['end_rep_date']=$end_rep_date;

require_once "lk/lk_ora_conn_string.php";

$filename="SMS - ".$_SESSION['start_rep_date']." - ".$_SESSION['end_rep_date'];
$head="СМС по проекту: ".$_SESSION['project']['name']." за период с ".$_SESSION['start_rep_date']." по ".$_SESSION['end_rep_date'];

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
and t.datetime BETWEEN to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY HH24:MI') AND to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY HH24:MI')+1/1440
order by t.datetime";

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

//в csv
if(isset($csv)) {
	require_once 'sql_to_csv.php';
	$options['head']=$head;
	//$options['encoding']='UTF8';
	sql_to_csv($c,$sql_text,$filename,$options);
	exit();
}

//HTML
if (isset($html)) {
	echo "<div class=rep_head>";
	echo "СМС за период с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b> включительно";
	
	$query = OCIParse($c,$sql_text);
	OCIExecute($query);
	$column_count=oci_num_fields($query);	
	echo "</div>";
	
	echo "<table class='report_table' style='cellspacing:2;cellpadding:3;'>";	
	echo "<tr>";
	for ($i = 0; $i < $column_count; $i++) {
		$column['name'] = oci_field_name($query,$i+1);
		echo "<th>".htmlentities($column['name'])."</th>";
	}	
	echo "</tr>";
	
	$rownum=0;
	while ($row=oci_fetch_row($query)) {
		$rownum++;
		echo "<tr>";
		foreach($row as $val) {
			if(preg_match('/^http[s]?:\/\//',$val)) $val="<a href=".$val." target=_blank>".$val."</a>";
			else $val=htmlentities($val);
			echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)'>".$val."</td>";
		}
		echo "</tr>";		
	}	
	echo "<tr>";
	echo "<td colspan='".$column_count."'><b>ИТОГО: Строк ".$rownum."</b></td>";	
	echo "</tr>";
	echo "</table>";
}	
echo '</body></html>';
?>