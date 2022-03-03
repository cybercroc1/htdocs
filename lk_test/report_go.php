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

if(isset($html)) {
	$link_type='html';
}

if(isset($xls)) {
	include("sql_to_xlsx.php");
	$link_type='xls';
}
if(isset($csv)) {
	include("sql_to_csv.php");
	$link_type='xls';
}

$filename="rep-".$_SESSION['start_rep_date']."-".$_SESSION['end_rep_date']."-ID".str_replace(array(",","\\","/",":","*","?",'"',"<",">","'","|","+","%","!","@")," ",$form_name);

if(isset($xls)) {
	$book_opt['filename']=$filename;
	xlsx_create_book($book_opt);
	$sheet_opt['filter']='y';
	xlsx_create_sheet($sheet_opt);
}
if(isset($csv)) {
	$csv_out=csv_create_file($filename);
}


	//записи разговоров
	if($_SESSION['allow_records']==1) {
		$recnum=0;
		if(isset($html)) {
			echo "<audio id=player controls preload=metadata style='width:100%;position:fixed;bottom:0;display:none' onplay='player_onplay()' onpause='player_onpause()'>
			</audio>";	
			echo "<iframe name=hidden_frame id=hidden_frame height=0 width=0 style=display:none></iframe>";
			$sql="SELECT
			t.id IdConnection, t.IdChain,
			convert(varchar(25),timestart,121) timestart,
			substring(convert(varchar(25),timestart,121),1,4)+
			substring(convert(varchar(25),timestart,121),6,2)+
			substring(convert(varchar(25),timestart,121),9,2)+'\\'+
			substring(convert(varchar(25),timestart,121),12,2)+
			substring(convert(varchar(25),timestart,121),15,2)+'\\' file_path,
			'mix_'+(case when alinenum<blinenum then alinenum else blinenum end)+'_'+(case when blinenum>alinenum then blinenum else alinenum end)+'__'+
			substring(convert(varchar(25),timestart,121),1,4)+'_'+
			substring(convert(varchar(25),timestart,121),6,2)+'_'+
			substring(convert(varchar(25),timestart,121),9,2)+'__'+
			substring(convert(varchar(25),timestart,121),12,2)+'_'+
			substring(convert(varchar(25),timestart,121),15,2)+'_'+
			substring(convert(varchar(25),timestart,121),18,2)+'_'+
			substring(convert(varchar(25),timestart,121),21,3)+'.mp3' file_name,
			t.AOutNumber,t.BOutNumber,
			case 
			when t.ConnectionType=1 then 'Изнутри наружу'
			when t.ConnectionType=2 then 'Изнутри в IVR'
			when t.ConnectionType=3 then 'Изнутри внутрь'
			when t.ConnectionType=4 then 'Снаружи в IVR'
			when t.ConnectionType=5 then 'Снаружи внутрь'
			when t.ConnectionType=6 then 'Снаружи наружу'
			when t.ConnectionType=7 then 'С IVR наружу'
			when t.ConnectionType=8 then 'С IVR внутрь'
			end call_direction_text,
			case 
			when t.ConnectionType=1 then 'in_out'
			when t.ConnectionType=2 then 'in_ivr'
			when t.ConnectionType=3 then 'in_in'
			when t.ConnectionType=4 then 'out_ivr'
			when t.ConnectionType=5 then 'out_in'
			when t.ConnectionType=6 then 'out_out'
			when t.ConnectionType=7 then 'ivr_out'
			when t.ConnectionType=8 then 'ivr_in'
			end call_direction_type			
			FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
			where IdChain=:idchain and IsRecorded=1
			order by TimeStart";
			$q_rec=$c_okt->prepare($sql);
		}
	}	
	
	function show_record_link($idchain,$datecall,$link_type) {
		//global $oktell_records_path;
		//global $oktell_records_url;
		global $q_rec;
		global $recnum;
		$res='';
		if(preg_match('/^[0-9abcdef]{8}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{12}$/i',$idchain)) { //проверка корректности UUID
			if($link_type=='xls') {
				//fullinfo
				if($_SESSION['allow_record_full']=='1') {
					$acc='&acc='.substr(md5($idchain.'-full'),0,8);
				}
				else $acc='';
				$src=OKTELL_RECORDS_URL.'?idchain='.$idchain.$acc."&datecall=".$datecall;
				$res=$src;
				//$res.="<a href='".$src."' target='wil_records'>Ссылка</a></br>";
			}
			else if($link_type=='html') {
				$q_rec->bindValue(':idchain',$idchain);
				
				$q_rec->execute();
				
				$partnum=0; while($row=$q_rec -> fetch()) {$partnum++;
					
					$file_path=OKTELL_RECORDS_PATH.$row['file_path'];
					$file_name=$row['file_name'];
					$new_file_name=$row['file_name'];	

					if(file_exists($file_path.$file_name)) {
						
						if($link_type=='html') {
							$recnum++;
							$src=OKTELL_RECORDS_URL.'?idconnection='.$row['IdConnection']."&datecall=".$datecall."&partnum=".$partnum;
							//$res.="<nobr><img id='".$row['IdConnection']."' class='imgplay' alt='".$row['call_direction_text']."' title='".$row['call_direction_text'].". Послушать' src='imgplay/".$row['call_direction_type']."_new.png' onclick='click_play(this,\"".$src."\")'></img>";
							$res.="<nobr><img id='imgid".$recnum."' class='imgplay' alt='".$row['call_direction_text']."' title='".$row['call_direction_text'].". Послушать' src='imgplay/".$row['call_direction_type']."_new.png' onclick='click_play(this,\"".$src."\")'></img>";
							
							//$res.=" <img class='imgplay' title='Скачать' src='imgplay/download.png' onclick='if(hidden_frame.location=\"".$src."\"){this.src=\"imgplay/downloaded.png\";}else{}'></img>";
							
							$res.=" <img class='imgplay' title='Скачать' src='imgplay/download.png' onclick='down_click(this,\"".$src."\")'></img>";
						}
						$res.= "<br>";
					}
				}
			}
		}		
		return $res;
	}
	function show_rows($rows,$fields,$type) {
		foreach($rows as $row_num => $row) {
			echo "<tr>";
			foreach($row as $col_num => $values) {
				if($fields[$col_num]=='Запись разговора') {
					if($row_num==1) echo "<td bgcolor=white rowspan='".count($rows)."' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_row(this.parentNode)'>";
					else continue;
				}
				else echo "<td bgcolor=white style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>";
				if(is_array($values)) {
					foreach($values as $value) {
						echo $value."<br>";
					}
				}
				else echo $values;
				echo "</td>";
			}
			echo "</tr>";
		}
	}
	//
	if(isset($html)) {
		echo "<div class=rep_head>";
		echo "<font size=4>\"".$_SESSION['project']['name']."\"";
		if ($form_id<>'all') echo " - \"".$form_name."\"";
		if ($cdn<>'all' and $cdn<>'null') echo " - ".$cdn;
		else if ($cdn=='null') echo " - без номера доступа"; 
		echo "</font><br>";
		echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b>";
		echo "</div>";
	}
	if(isset($xls)) {
		$head=$_SESSION['project']['name'];
		if ($form_id<>'all') $head.=" - \"".$form_name."\"";
		if ($cdn<>'all' and $cdn<>'null') $head.=" - ".$cdn;
		else if ($cdn=='null') $head.=" - без номера доступа"; 
		xlsx_put_row(1,array($head));
		$head="За период: с ".$_SESSION['start_rep_date']." по ".$_SESSION['end_rep_date'];
		xlsx_put_row(1,array($head));
	}
	if(isset($csv)) {
		$head=$_SESSION['project']['name'];
		if ($form_id<>'all') $head.=" - \"".$form_name."\"";
		if ($cdn<>'all' and $cdn<>'null') $head.=" - ".$cdn;
		else if ($cdn=='null') $head.=" - без номера доступа"; 
		csv_put_row($csv_out,array($head));
		$head="За период: с ".$_SESSION['start_rep_date']." по ".$_SESSION['end_rep_date'];
		csv_put_row($csv_out,array($head));
	}	
	$sql_main="select to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call, b.id call_base_id,
	decode(b.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL) direction,
	decode(b.call_direction,'in',b.cdpn,'out',b.dialed_number,'callback',b.cdpn,b.cdpn) aon,
	b.cgpn, b.agid, r.id report_id, r.form_id, 
	replace(f.name,'\"','&quot;') form_name, 
	replace(ph.phone_name,'\"','&quot;') phone_name, 
	replace(p.name,'\"','&quot;') project_name,

    b.ivr_sec,
    b.queue_sec,
    b.alerting_sec,
    b.connected_sec,
    case when b.connected_sec<6 then 0 else ceil(b.connected_sec/60) end connected_min,
    case when b.connected_sec<6 then b.connected_sec else b.call_sec end call_sec,
    case when b.connected_sec<6 then 0 else ceil(b.call_sec/60) end call_min,
	b.cdr_thr_id,
    r.id report_id	
	
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
	
	order by b.date_call, b.id, replace(f.name,'\"','&quot;')";	
	
	//echo "<textarea>$sql_main</textarea>";
	//echo "</div>";
	$q=OCIParse($c,$sql_main);
	
	OCIExecute($q,OCI_DEFAULT);
		$col_num=1;
		$fields=array();
		//if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y') 	{$fields[]="ID";}
		if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y') 	{$fields[]="Дата звонка";}
		if($_SESSION['project']['id']=='0') {
			$fields[]="Проект";
		}
		$fields[]="Направление звонка";
		if(isset($access_fix['aon']) 			and $access_fix['aon']=='y')		{$fields[]="АОН";}
		//if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y')  {$fields[]="Номер доступа";}
		if(isset($access_fix['cdn']) 			and $access_fix['cdn']=='y')  		{$fields[]="Номер доступа";}
		if(isset($access_fix['agid']) 			and $access_fix['agid']=='y')		{$fields[]="ID Оператора";}

		if(isset($call_filed_id)) {
			foreach($call_filed_id as $key => $id) {
				$fields[]=$call_filed_name[$key];
			}
		}

		if($form_id=='all') {
			$fields[]="Отчет";
		}
		if($form_id<>'all' and $form_id<>'null') {
			if(isset($access_fix['ivr_sec']) 		and $access_fix['ivr_sec']=='y') 		{$fields[]="Длит.IVR(сек)";}
			if(isset($access_fix['queue_sec'])	 	and $access_fix['queue_sec']=='y') 		{$fields[]="Время в очереди(сек)";}
			if(isset($access_fix['alerting_sec']) 	and $access_fix['alerting_sec']=='y') 	{$fields[]="Длит.КПВ(сек)";}
			if(isset($access_fix['connected_sec'])	and $access_fix['connected_sec']=='y')	{$fields[]="Длит.разговора(сек)";}
			if(isset($access_fix['connected_min'])	and $access_fix['connected_min']=='y')	{$fields[]="Длит.разговора(мин)";}			
			if(isset($access_fix['call_sec']) 		and $access_fix['call_sec']=='y') 		{$fields[]="Длит.(сек)";}
			if(isset($access_fix['call_min']) 		and $access_fix['call_min']=='y') 		{$fields[]="Длит.(мин)";}			
			
			if(isset($object_id)) {
				foreach ($object_id as $key=>$id) {
					$fields[]=$object_name[$key];
				}
			}

		}
		
		if($_SESSION['allow_records']==1) {$fields[]="Запись разговора";}
		
		if(isset($html)) {
			echo "<table class='report_table'>";
			echo "<tr>";		
			foreach($fields as $fname) {
				echo "<th>".$fname."</th>";
			}
			echo "</tr>";		
		}
		if(isset($xls)) {
			xlsx_put_row(1,$fields,'heads');
		}
		if(isset($csv)) {
			csv_put_row($csv_out,$fields);
		}		
		$row_num=0;
		$rows_buff='';
		$rows_in_buff='0';
		$perv_call_base_id='';
		$row=array();
		$t=0;
		while (OCIFetch($q)) {
			$curr_call_base_id=OCIResult($q,"CALL_BASE_ID");
			if(isset($html)) {
				if(count($row)>0 and $curr_call_base_id<>$perv_call_base_id) {
					show_rows($row,$fields,$link_type);
					$row=array();
					$t=0;
				}
				$t++;
			}
			$row_num++;
			$row[$t]=array();
			
			if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y') $row[$t][]=OCIResult($q,"DATE_CALL");

			if($_SESSION['project']['id']=='0') {
				$row[$t][]=OCIResult($q,"PROJECT_NAME");
			}
			
			$row[$t][]=OCIResult($q,"DIRECTION");			
			if(isset($access_fix['aon']) 			and $access_fix['aon']=='y')	{
				if(isset($CODED_AON)) $row[$t][]=phones_encode(phones_norm(OCIResult($q,"AON"),"+"),"+");  
				else $row[$t][]=OCIResult($q,"AON");
			}
			//if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y')	$row[$t][]=OCIResult($q,"CGPN");
			if(isset($access_fix['cdn']) 				  and $access_fix['cdn']=='y')	$row[$t][]=OCIResult($q,"CGPN");
			if(isset($access_fix['agid']) 				  and $access_fix['agid']=='y')	$row[$t][]=OCIResult($q,"AGID");
			
			if(isset($call_filed_id)) {
				foreach($call_filed_id as $key=>$cal_fld_id) {
					$tmp_call_id=OCIResult($q,"CALL_BASE_ID");
					OCIBindByName($q_call_val,":call_id",$tmp_call_id);
					OCIBindByName($q_call_val,":field_id",$cal_fld_id);
					OCIExecute($q_call_val,OCI_DEFAULT);
					
					$fidx=count($row[$t]);
					$row[$t][$fidx]=array();
					while(OCIFetch($q_call_val)) {
						$row[$t][$fidx][]=OCIResult($q_call_val,"VALUE");
					}
				}
			}
			
			if($form_id=='all') $row[$t][]=OCIResult($q,"FORM_NAME");
			
			if($form_id<>'all' and $form_id<>'null') {
				if(isset($access_fix['ivr_sec']) 				and $access_fix['ivr_sec']=='y') 		{$row[$t][]=OCIResult($q,"IVR_SEC");}
				if(isset($access_fix['queue_sec'])	 			and $access_fix['queue_sec']=='y') 		{$row[$t][]=OCIResult($q,"QUEUE_SEC");}
				if(isset($access_fix['alerting_sec']) 			and $access_fix['alerting_sec']=='y') 	{$row[$t][]=OCIResult($q,"ALERTING_SEC");}
				if(isset($access_fix['connected_sec']) 			and $access_fix['connected_sec']=='y')	{$row[$t][]=OCIResult($q,"CONNECTED_SEC");}
				if(isset($access_fix['connected_min'])			and $access_fix['connected_min']=='y')	{$row[$t][]=OCIResult($q,"CONNECTED_MIN");}
				if(isset($access_fix['call_sec']) 				and $access_fix['call_sec']=='y') 		{$row[$t][]=OCIResult($q,"CALL_SEC");}
				if(isset($access_fix['call_min']) 				and $access_fix['call_min']=='y') 		{$row[$t][]=OCIResult($q,"CALL_MIN");}			

				if(isset($object_id)) {
					foreach ($object_id as $key=>$obj_id) {
						
						$tmp_report_id=OCIResult($q,"REPORT_ID");
						OCIBindByName($q_val,":report_id",$tmp_report_id);
						OCIBindByName($q_val,":object_id",$obj_id);
						OCIBindByName($q_val,":object_name",$object_name[$key]);			
						OCIExecute($q_val,OCI_DEFAULT);						

						$fidx=count($row[$t]);
						$row[$t][$fidx]=array();
							
						while(OCIFetch($q_val)) {
							if($object_type[$key]=='CT') {
								$row[$t][$fidx][]=phones_encode(phones_norm(OCIResult($q_val,"VALUE"),"+"),"+");
							}
							else {						
								$row[$t][$fidx][]=OCIResult($q_val,"VALUE");
							}
						}
					}
				}			
			}
			
			if($_SESSION['allow_records']==1) {
				$row[$t][]=show_record_link(OCIResult($q,"CDR_THR_ID"),
				substr(OCIResult($q,"DATE_CALL"),6,4).
				substr(OCIResult($q,"DATE_CALL"),3,2).
				substr(OCIResult($q,"DATE_CALL"),0,2)."-".
				substr(OCIResult($q,"DATE_CALL"),11,2).
				substr(OCIResult($q,"DATE_CALL"),14,2).
				substr(OCIResult($q,"DATE_CALL"),17,2),
				$link_type
				);
			}
			$perv_call_base_id=$curr_call_base_id;
			if(isset($xls)) {
				$row_tmp='';
				foreach ($row[$t] as $col_num => $data) {
					if(is_array($data)) $row_tmp[]=implode("\n",$data);
					else $row_tmp[]=$data;
				}
				xlsx_put_row(1,$row_tmp);
			}
			if(isset($csv)) {
				$row_tmp='';
				foreach ($row[$t] as $col_num => $data) {
					if(is_array($data)) $row_tmp[]=implode(", ",$data);
					else $row_tmp[]=$data;
				}
				csv_put_row($csv_out,$row_tmp);
			}				
		}
		if(isset($html)) show_rows($row,$fields,$link_type);
		if(isset($xls)) {
			xlsx_end_sheet(1);
			xlsx_end_book();
		}

	if (isset($html)) {
		echo "<tr>"."<td bgcolor=white align=left style='text-align:left' colspan='".count($fields)."'><b>ИТОГО: строк ".$row_num."</b></td>"."</tr>";		
		echo "</table>";
	}
	if($_SESSION['allow_records']==1 and isset($html)) {echo "<br><br>";}
	OCIFreeStatement($q);	
?>