<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','1');
ini_set('max_execution_time','0');
include("../../sc_conf/sc_session");
session_start();

extract($_REQUEST);
//print_r($_POST);
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/ab_conn_string");

if(isset($selected_fields)) {
	$_SESSION['bill_selected_fields']=$selected_fields;
}

//Формирование дат

$_SESSION['start_bill_date']=$start_bill_date;
$_SESSION['end_bill_date']=$end_bill_date;

$_SESSION['start_bill_hh']=$start_bill_hh;
$_SESSION['start_bill_mi']=$start_bill_mi;
$_SESSION['end_bill_hh']=$end_bill_hh;
$_SESSION['end_bill_mi']=$end_bill_mi;

if($min_conn_dur_sec=='') $_SESSION['bill_min_conn_dur_sec']=0; else $_SESSION['bill_min_conn_dur_sec']=$min_conn_dur_sec;
$_SESSION['bill_call_type']=$call_type;


if(($_SESSION['start_bill_hh']<>'0' or $_SESSION['start_bill_mi']<>'0' or $_SESSION['end_bill_hh']<>'0' or $_SESSION['end_bill_mi']<>'0') and $_SESSION['start_bill_date']==$_SESSION['end_bill_date']) {
	$start_bill_datetime=$_SESSION['start_bill_date']." ".$_SESSION['start_bill_hh'].":".$_SESSION['start_bill_mi'].":0";
	$end_bill_datetime=$_SESSION['end_bill_date']." ".$_SESSION['end_bill_hh'].":".$_SESSION['end_bill_mi'].":0";
}
else {
	$start_bill_datetime=$_SESSION['start_bill_date']." 0:0:0";
	$end_bill_datetime=$_SESSION['end_bill_date']." 23:59:59";
}



//
if(isset($xls_go)) report();
if (isset($report_go)) report();

//ОТЧЕТ
function report() {
	global $c;
	global $abilling;
	global $fields;
	global $query;
	global $start_bill_datetime;
	global $end_bill_datetime;	
	extract($_REQUEST);
	//формируем список номеров $nums
	if ($cgpn=='') {
		$q=OCIParse($c,"select phone from sc_phones
		where project_id='".$_SESSION['project']['id']."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		$nums='';
			while (OCIFetch($q)) {
				if ($i>0) $nums.=",";
				$nums.="'".OCIResult($q,"PHONE")."'";
				$i++;
			}
		if ($i>1) $nums=" in (".$nums.") "; else $nums="=".$nums; 	
	}
	else {
		$q=OCIParse($c,"select phone from sc_phones
		where project_id='".$_SESSION['project']['id']."' and phone='".$cgpn."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$nums="='".OCIResult($q,"PHONE")."'";
	}
	//

	//СТРОИМ ЗАПРОС
	//список полей
	$sql_text="select";
	$q=OCIParse($abilling,"select full_name,short_name,sql,html_width,xml_width,xml_type from ab_inc_fields where field_name=:field_name");

	$i=0;
	foreach($_SESSION['bill_selected_fields'] as $field_name) {
		OCIBindByName($q,":field_name",$field_name);
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) {
			if($i==0) {$sql_text.=chr(10).chr(10); $sql_order_by="order by ";}
			else {$sql_text.=chr(10).','.chr(10); $sql_order_by.=",";}
			$sql_text.=OCIResult($q,"SQL");
			$sql_order_by.=$field_name." nulls first";
			$i++;
			$fields[$i]['field_name']=$field_name;
			$fields[$i]['full_name']=OCIResult($q,"FULL_NAME");
			$fields[$i]['short_name']=OCIResult($q,"SHORT_NAME");
			$fields[$i]['html_width']=OCIResult($q,"HTML_WIDTH");
			$fields[$i]['xml_width']=OCIResult($q,"XML_WIDTH");
			$fields[$i]['xml_type']=OCIResult($q,"XML_TYPE");
		}	
	}
	//
	//FROM
	$sql_text.=chr(10).chr(10)."from cdr_calls a, ab_tarif_mg b".chr(10);
	//
	//WHERE
	//выбранный период
	$sql_text.=chr(10)."where 
(
a.offered_date BETWEEN to_date('".$start_bill_datetime."','DD.MM.YYYY HH24:MI:SS') AND to_date('".$end_bill_datetime."','DD.MM.YYYY HH24:MI:SS') --Входящие 
or 
a.originated_date BETWEEN to_date('".$start_bill_datetime."','DD.MM.YYYY HH24:MI:SS') AND to_date('".$end_bill_datetime."','DD.MM.YYYY HH24:MI:SS') --Переводные
)".chr(10);
	//
	//номера, но не раньше включения номера

	$sql_text.="and".chr(10);

	if ($cgpn=='') {
		$q=OCIParse($c,"select phone,to_char(date_set,'DD.MM.YYYY') date_set from sc_phones
		where project_id='".$_SESSION['project']['id']."'");
	}	
	else {
		$q=OCIParse($c,"select phone,to_char(date_set,'DD.MM.YYYY') date_set from sc_phones
		where project_id='".$_SESSION['project']['id']."' and phone='".$cgpn."'");
	}
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $sql_or_vhod=''; $sql_or_forw='';
	while(OCIFetch($q)) {
		if($i==0) {$sql_or_vhod.=chr(10); $sql_or_forw.=chr(10);}
		else {$sql_or_vhod.=chr(10)."or "; $sql_or_forw.=chr(10)."or ";}
		$sql_or_vhod.="a.called_num = '".OCIResult($q,"PHONE")."' and a.offered_date >= to_date('".OCIResult($q,"DATE_SET")."','DD.MM.YYYY') and a.originated_date is null";
		$sql_or_forw.="substr(uui,instr(a.uui,'DNIS=')+5,7) = '".OCIResult($q,"PHONE")."' and a.originated_date >= to_date('".OCIResult($q,"DATE_SET")."','DD.MM.YYYY')";
		$i++;
	}

	$sql_text.="(".chr(10)."( --входящие";
	$sql_text.=$sql_or_vhod.chr(10);
	$sql_text.=")".chr(10)."or".chr(10)."( --переводные";
	$sql_text.=$sql_or_forw.chr(10);
	$sql_text.=")".chr(10).")".chr(10); 
	//
	//минимальная длительность разговора
	if($_SESSION['bill_min_conn_dur_sec']>0) {
		$sql_text.=chr(10)."and (a.call_end_date-a.connected_date)*24*60*60 >=".$_SESSION['bill_min_conn_dur_sec'].chr(10);
	}	
	//входящие или переводные
	if($_SESSION['bill_call_type']=='входящие') {
		$sql_text.=chr(10)."and a.originated_date is null".chr(10);
	}
	if($_SESSION['bill_call_type']=='переводные') {
		$sql_text.=chr(10)."and a.originated_date is not null".chr(10);
	}
	//завершаем построение запроса
	
	$sql_text.=chr(10)."and decode(a.originated_date,null,null,ab_dialcode(a.called_num))=b.dialcode(+)".chr(10);
	
	//$sql_text.=chr(10)."and ab_code(a.called_num)=b.code(+)".chr(10);
	$sql_text.=$sql_order_by;

	//echo str_replace(chr(10),"<br>",$sql_text);
	
	 
	$query=OCIParse($abilling,$sql_text);

	//ЗАПРОС ГОТОВ
	if(isset($report_go)) html();
	if(isset($xls_go)) xls();
	//
	//
	//
	//
}

function html() {
	global $fields;
	global $query;
	global $cgpn;
	global $start_bill_datetime;
	global $end_bill_datetime;		
	echo '<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
</head>
<link href="billing.css" rel="stylesheet" type="text/css">
<body topmargin="0">
<html>';
	echo "<font size=3><b>Детализация - \"".$_SESSION['project']['name']."\"</b></font><br>";		
	echo "<b>".$_SESSION['bill_call_type']."</b> звонки, ";
	if($_SESSION['bill_min_conn_dur_sec']>0) echo "длительностью >= <b>".$_SESSION['bill_min_conn_dur_sec']." сек. </b>";
	if ($cgpn<>'') {$num_txt="по номеру <B>".$cgpn."</B> "; echo $num_txt;} else {$num_txt="";}
	echo "за период с <B>".$start_bill_datetime."</B> по <B>".$end_bill_datetime."</B>";
	echo  "<table bgcolor=gray cellspacing=1 cellpadding=2><tr>";
	echo "<td bgcolor=white align=center><b>№</b></td>";
		foreach ($fields as $field) {
			echo "<td bgcolor=white align=center width='".$field['html_width']."'><b>".$field['short_name']."</b></td>";
		}
	echo "</tr>";
	flush();

	OCIExecute($query,OCI_DEFAULT);
	$i=0;
	while($row=oci_fetch_assoc($query)) {
		$i++;
		echo "<tr>";
		if(isset($row['CALL_TYPE']) and $row['CALL_TYPE']=='Переводной') $color='#DDDDDD'; else $color='white';
		echo "<td bgcolor='$color' align=center>$i</td>";
		foreach ($fields as $field) {
			//if($row[$field['field_name']]=='0') $row[$field['field_name']]='';
			echo "<td bgcolor='$color' align=center width='".$field['html_width']."' title='".$field['full_name']."'>".$row[$field['field_name']]."</td>";
		}		
		echo "</tr>";
		flush();
	}
echo "</table>";
flush();
}

function xls() {
	global $fields;
	global $query;
	global $cgpn;
	global $start_bill_datetime;
	global $end_bill_datetime;		
	header("Content-Type: application/vnd.ms-excel");
	//header("Content-type: application/xls");
	header("Content-Disposition: attachment; filename=\"bil-".$_SESSION['start_bill_date']."-".$_SESSION['end_bill_date'].".xls\"");	
	//стили, начало книги
		echo '<?xml version="1.0" encoding="windows-1251"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Arial Cyr" x:CharSet="204"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s21">
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#666699"/>
  </Style>
  <Style ss:ID="s35">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s36">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"/>
   <Interior ss:Color="#DDDDDD" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s40">
   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"
    ss:Bold="1"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
 </Styles>';
 	//
	flush();
	//Начало листа
		echo ' <Worksheet ss:Name="Биллинг">
  <AutoFilter x:Range="R2C2:R2C'.(count($fields)+1).'" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>  
  <Table x:FullColumns="1"
   x:FullRows="1">';
	echo '<Column ss:AutoFitWidth="0" ss:Width="30"/>'; 
	foreach ($fields as $field) {
		if($field['xml_width']=='') echo '<Column ss:AutoFitWidth="1"/>';
		else echo '<Column ss:AutoFitWidth="0" ss:Width="'.$field['xml_width'].'"/>'; 
	}
   	//
	flush();
	//первая строка
   	echo '<Row>
    <Cell ss:StyleID="s21"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40">';
	echo "<B>".$_SESSION['bill_call_type']."</B> звонки&cedil; ";
	if($_SESSION['bill_min_conn_dur_sec']>0) echo "длительностью &gt;= <B>".$_SESSION['bill_min_conn_dur_sec']." сек. </B>";
	if ($cgpn<>'') {$num_txt="по номеру <B>".$cgpn."</B> "; echo $num_txt;} else {$num_txt="";}
	echo "за период с <B>".$start_bill_datetime."</B> по <B>".$end_bill_datetime."</B>";
	echo '</ss:Data></Cell></Row>';
	//
	flush();
	//шапка
	echo  '<Row ss:AutoFitHeight="1">
    <Cell ss:StyleID="s40"><Data ss:Type="String">№</Data></Cell>';
    foreach ($fields as $field) {
		echo '<Cell ss:StyleID="s40"><Data ss:Type="String">'.$field['short_name'].'</Data></Cell>';
	}
	echo '</Row>';	
	//
	flush();
	//строки
	OCIExecute($query,OCI_DEFAULT);
	$i=0;
	while($row=oci_fetch_assoc($query)) {
		$i++;
		echo "<Row>";
		
		if(isset($row['CALL_TYPE']) and $row['CALL_TYPE']=='Переводной') $style='s36'; else $style='s35';
		
		echo '<Cell ss:StyleID="'.$style.'"><Data ss:Type="Number">'.$i.'</Data></Cell>';
		foreach ($fields as $field) {
			//if($row[$field['field_name']]=='' or $row[$field['field_name']]=='0') {
			if($row[$field['field_name']]=='') {
				echo '<Cell ss:StyleID="'.$style.'"/>';
			}
			else {
				
				echo '<Cell ss:StyleID="'.$style.'"><Data ss:Type="'.$field['xml_type'].'">'.$row[$field['field_name']].'</Data></Cell>';	
			}	
		}
		echo "</Row>";
		flush();
	}
	//
	flush();
	
	//конец листа
	echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.4"/>
    <PageMargins x:Bottom="0.58" x:Left="0.38" x:Right="0.41" x:Top="0.4"/>
   </PageSetup>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>0</VerticalResolution>
   </Print>
   <Selected/>
   <FreezePanes/>
   <FrozenNoSplit/>
   <SplitHorizontal>2</SplitHorizontal>
   <TopRowBottomPane>2</TopRowBottomPane>
   <ActivePane>2</ActivePane>
   <Panes>
    <Pane>
     <Number>3</Number>
    </Pane>
    <Pane>
     <Number>2</Number>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>'; 
 //
 //конец книги
 echo "</Workbook>";
 flush();
exit();
}
?>
</body>
</html>

