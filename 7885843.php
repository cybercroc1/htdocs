<?php 
exit();
extract($_REQUEST);
	
	if(!isset($id) or !isset($start_date)) exit();
	
	$project_id=25;
	
	if($id==1) $form_id=8264;
	else if($id==2) $form_id=765;
	else if($id==3) $form_id=764;
	else $form_id='';
	
	if(!isset($end_date)) $end_date='';
		
	include("../sc_conf/sc_conn_string");
	
	//header("Content-type: application/csv");
	//header("Content-Disposition: attachment; filename=\"rep.csv\""); 
	
	//получаем список полей таблицы
	$col_num=0;
	echo '"Дата звонка"'; $col_num++;
	echo ';"АОН"'; $col_num++;
	echo ';"Номер доступа"'; $col_num++;
	echo ';"ID Оператора"'; $col_num++;
	
	$h=OCIParse($c,"select id,name from sc_form_object b
	where b.form_id='".$form_id."'
	and b.project_id='".$project_id."'
	order by ordering");
	
	OCIExecute($h,OCI_DEFAULT);
	$decode=''; 
		$i=0;
		while(OCIFetch($h)) {
		echo ';"'.OCIResult($h,"NAME").'"';
		$object_id[$i]=OCIResult($h,"ID");
		$object_name[$i]=OCIResult($h,"NAME");
		$i++;
		}
	$col_num+=$i-1;	
	echo "\n\r";
	//
	//Готовим запросы
	$q=OCIParse($c,"select b.id call_id,
       to_char(b.date_call, 'DD.MM.YYYY HH24:MI:SS') date_call,
       b.cdpn aon,
       b.cgpn,
       b.agid,
	   b.call_sec,
	   ceil(b.call_sec/60) call_min,
       r.id report_id
  from sc_call_base b, sc_call_report r
 where b.date_call between to_date('".$start_date."','YYYYMMDDHH24MISS') and to_date(nvl('".$end_date."',to_char(sysdate,'YYYYMMDDHH24MISS')),'YYYYMMDDHH24MISS')
   and b.id = r.call_base_id
   and b.project_id = '".$project_id."'
   and r.form_id=".$form_id."
   order by b.date_call,report_id");
   
	//
	OCIExecute($q,OCI_DEFAULT);
		$row_num=0;
		while (OCIFetch($q)) {
			$row_num++;
			echo '"'.OCIResult($q,"DATE_CALL").'"';
			echo ';"'.OCIResult($q,"AON").'"';
			echo ';"'.OCIResult($q,"CGPN").'"';
			echo ';"'.OCIResult($q,"AGID").'"';
				for($j=0; $j<$i; $j++) {
				echo ';"';
				$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id and object_id='".$object_id[$j]."' and object_name=:object_name");	

				OCIBindByName($q_val,":report_id",OCIResult($q,"REPORT_ID"));
				OCIBindByName($q_val,":object_name",$object_name[$j]);			
				OCIExecute($q_val,OCI_DEFAULT);
					$n=0;
					while(OCIFetch($q_val)) {
					if($n>0) echo ',';
					echo OCIResult($q_val,"VALUE");
					$n++;
					}
				echo '"';	
				}		
			echo "\n\r";		
		}
		
	  //отчет по кнкретной форме
?>
