<?php 
ini_set('max_execution_time','600');
require_once "auth.php";
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

if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
extract($_POST);
?>
<?php
//Формирование дат
if(isset($start_rep_date)) $_SESSION['start_rep_date']=$start_rep_date;
if(isset($end_rep_date)) $_SESSION['end_rep_date']=$end_rep_date;


include("lk/lk_ora_conn_string.php");
include("phones_conv.php");
	
include("report_build_query.php");

	//готовим текст запроса
	$i=0; $ii=0; $j=0;
	$sql1=''; $sql2=''; $sql3=''; $sql4=''; $sql5=''; $sql6=''; $sql7=''; $sql8=''; $sql9='';
	if (isset($order_by_count)) $sql6=' count(*) desc,';
	echo "<table class='report_table'>
	<tr>";
	
	if (!isset($chk_data) and !isset($chk_direction) and !isset($chk_cdpn) and !isset($chk_cgpn) and !isset($chk_agid) and !isset($chk_form) and !isset($chk_project) and !isset($selected_columns)) exit();

	if (isset($chk_data) or isset($chk_direction) or isset($chk_cdpn) or isset($chk_cgpn) or isset($chk_agid) or isset($chk_form) or isset($chk_project) or $cdn<>'all') {

		if (isset($chk_data) and isset($access_fix['date_call']) and $access_fix['date_call']=='y') {
		$sql1.=" trunc(t.date_call) date_call,"; 
		$sql5.=" trunc(t.date_call),";
		$sql6.=" trunc(t.date_call),";
		echo "<th bgcolor=white>Дата звонка</th>";
		$ii++;
		}
		if (isset($chk_direction)) {
		$sql1.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL) call_direction,"; 
		$sql5.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL),";
		$sql6.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL),";
		echo "<th bgcolor=white>Направлене звонка</th>";
		$ii++;
		}		
		if (isset($chk_cgpn) and isset($access_fix['cdn']) and $access_fix['cdn']=='y') {
		$sql1.=" t.cgpn,"; 
		$sql5.=" t.cgpn,";
		$sql6.=" t.cgpn,";
		echo "<th bgcolor=white>Номер доступа</th>";
		$ii++;
		}
		if (isset($chk_agid) and isset($access_fix['agid']) and $access_fix['agid']=='y') {
		$sql1.=" t.agid,"; 
		$sql5.=" t.agid,";
		$sql6.=" t.agid,";
		echo "<th bgcolor=white>ID Оператора</th>";
		$ii++;
		}
		if (isset($chk_form)) {
		$sql1.=" t.form_name,"; 
		$sql5.=" t.form_name,";
		$sql6.=" t.form_name,";
		echo "<th bgcolor=white>Форма</th>";
		$ii++;
		}
		if (isset($chk_project) and $_SESSION['project']['id']=='0') {
		$sql1.=" t.project_name,"; 
		$sql5.=" t.project_name,";
		$sql6.=" t.project_name,";
		echo "<th bgcolor=white>Форма</th>";
		$ii++;
		}

	}	
	
	if (isset($selected_columns)) {
		foreach ($selected_columns as $obj_id => $obj_name) {
			if($i==0) $sql3.="where ";
			$sql1.="t".$obj_id.".value t".$obj_id.",";			
			$sql2.=",SC_CALL_REPORT_VALUES t".$obj_id;
			if ($i>0) $sql3.="and ";
			$sql3.="t".$obj_id.".object_id(+)='".$obj_id."' and t".$obj_id.".object_name(+)='".$obj_name."' ";
			$sql4.="and t".$obj_id.".call_report_id(+)=t.call_report_id ";
			$sql5.="t".$obj_id.".value,";
			$sql6.="t".$obj_id.".value,";
			echo "<th bgcolor=white>".$obj_name."</th>";
			$i++;
		}
	}
	
	if(!isset($selected_columns) and !isset($chk_form)) {//только поля SC_CALL_BASE
		$subquery_select="distinct b.id, b.date_call,b.call_direction, b.cgpn,b.agid,p.name project_name";
	}
	else {
		$subquery_select="b.date_call,b.call_direction, b.cgpn,b.agid,p.name project_name, r.id call_report_id, r.call_base_id, f.name form_name";
	}

echo "<th bgcolor=white>Кол-во</th>";	

$sql2=rtrim($sql2,",");
$sql5=rtrim($sql5,",");
$sql6=rtrim($sql6,",");



$sql_main="select 
".$sql1." 
count(*) count
from 
(
select ".$subquery_select."

	from sc_call_base b
	left join sc_projects p on p.id=b.project_id
	left join sc_call_report r on r.call_base_id=b.id
	left join sc_forms f on f.id=r.form_id
	left join sc_phones ph on ph.project_id=b.project_id and ph.phone=b.cgpn 	   
		
	where 
	b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY HH24:MI') 
					and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY HH24:MI')+1/1440
	".$and_b_rep_period."
		
	".$and_r_form_id."
	
	".$and_b_cdn."
	".$and_project_id."	
	".$and_form_ids."
	".$and_cdns."	
	
) t 
".$sql2."
".$sql3."
".$sql4."
 group by 
".$sql5."
 order by 
".$sql6;

$row_cnt=0; $sum=0;
//echo $sql_main;
$q=OCIParse($c,$sql_main);
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr>";
	if (isset($chk_data)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"DATE_CALL")."</td>";
	if (isset($chk_direction)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"CALL_DIRECTION")."</td>";
	if (isset($chk_cdpn)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"CDPN")."</td>";
	if (isset($chk_cgpn)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"CGPN")."</td>";
	if (isset($chk_agid)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"AGID")."</td>";
	if (isset($chk_form)) echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"FORM_NAME")."</td>";
	if (isset($chk_project) and $_SESSION['project']['id']=='0') echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,"PROJECT_NAME")."</td>";
		if (isset($selected_columns)) {
			for($j=0; $j<count($selected_columns); $j++) {
			echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".OCIResult($q,$j+1+$ii)."</td>";
			}
		}
	echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'><b>".OCIResult($q,"COUNT")."</b></td></tr>";
	$sum+=OCIResult($q,"COUNT");
	$row_cnt++;
	}	
echo "<tr><td bgcolor=white colspan=\"".($j+$ii)."\"><b>ИТОГО: строк ".$row_cnt."</b></td><td bgcolor=white><b>".$sum."</b></td></tr>";
?>
