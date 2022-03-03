<?php 
ini_set('max_execution_time','600');

extract($_REQUEST);

	include("../../sc_conf/sc_conn_string");


//Отчет
$project_id='961';
$form_id='16544';
$s=array(chr(13),chr(10),'"');
$r=array('','',"'");
if (isset($start_date) and isset($end_date) and isset($last_id)) {


	//получаем список полей таблицы
	$col_num=0;
	echo '"REPORT_ID"'; $col_num++;
	echo ',"Дата звонка"'; $col_num++;
	echo ',"АОН"'; $col_num++;
	echo ',"Номер доступа"'; $col_num++;
	echo ',"ID Оператора"'; $col_num++;

	$h=OCIParse($c,"select id object_id, name object_name, ordering from SC_FORM_OBJECT t
where project_id='".$project_id."' and form_id='".$form_id."'
order by ordering");
	
	OCIExecute($h,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($h)) {
		if ($i>0) echo ',';
		echo 
		'"'.OCIResult($h,"OBJECT_NAME").'"';
		$object_id[$i]=OCIResult($h,"OBJECT_ID");
		$object_name[$i]=nl2br(str_replace($s,$r,OCIResult($h,"OBJECT_NAME")));
		$i++;
		}
	echo chr(13).chr(10);	
	$col_num+=$i-1;	
	//
	//Готовим запросы
	$q_text="select b.id call_id,
       to_char(b.date_call, 'DD.MM.YYYY HH24:MI:SS') date_call,
       b.cdpn aon,
       b.cgpn,
       b.agid,
       b.ivr_sec,
       b.queue_sec,
       b.alerting_sec,
       b.connected_sec,
       case when b.connected_sec<6 then 0 else ceil(b.connected_sec/60) end connected_min,
       case when b.connected_sec<6 then b.connected_sec else b.call_sec end call_sec,
       case when b.connected_sec<6 then 0 else ceil(b.call_sec/60) end call_min,
       r.id report_id
  from sc_call_base b, sc_call_report r
 where r.date_call between to_date(nvl('".$start_date."','01012011000000'), 'DDMMYYYYHH24MISS') and
       nvl(to_date('".$end_date."','DDMMYYYYHH24MISS'),sysdate)
   and r.project_id = '".$project_id."' and r.form_id='".$form_id."' and r.id>'".$last_id."'
   and b.id = r.call_base_id
   order by b.date_call,r.id";
   //echo $q_text;
	$q=OCIParse($c,$q_text);
	//
	OCIExecute($q,OCI_DEFAULT);
	$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id and object_id=:object_id");
	$row_num=0;
	while (OCIFetch($q)) {
		$row_num++;
		echo '"'.OCIResult($q,"REPORT_ID").'"';
		echo ',"'.OCIResult($q,"DATE_CALL").'"';
		echo ',"'.OCIResult($q,"AON").'"';
		echo ',"'.OCIResult($q,"CGPN").'"';
		echo ',"'.OCIResult($q,"AGID").'"';
		for($j=0; $j<$i; $j++) {
			OCIBindByName($q_val,":report_id",OCIResult($q,"REPORT_ID"));
			OCIBindByName($q_val,":object_id",$object_id[$j]);			
			OCIExecute($q_val,OCI_DEFAULT);
			echo ',"';
			$n=0;
			while(OCIFetch($q_val)) {
				if ($n>0) echo "<br>";
				echo nl2br(str_replace($s,$r,OCIResult($q_val,"VALUE")));
				$n++;
			}
			echo '"';	
		}		
	echo chr(13).chr(10);		
	}
	OCIFreeStatement($q);
}
//отчет
?>
