<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','300');
include("../../sc_conf/sc_session");
session_start();

extract($_REQUEST);
if (!isset($xls_go)) {
echo '<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>';
}

if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['view_billing'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

//Формирование дат

if(isset($start_bill_date)) $_SESSION['start_bill_date']=$start_bill_date;
if(isset($end_bill_date)) $_SESSION['end_bill_date']=$end_bill_date;

	if (!isset($_SESSION['start_bill_date'])) {
	$start_rep_date = strtotime("-1 day");
	$_SESSION['start_bill_date'] = date("d.m.Y",$start_rep_date);
	}
	
	if (!isset($_SESSION['end_bill_date'])) {
	$end_rep_date = strtotime("-1 day"); //текущая дата
	$_SESSION['end_bill_date'] = date("d.m.Y",$end_rep_date);
	}

$yesterday = strtotime("- 1 day");
$yesterday = date("d.m.Y",$yesterday);
$curdate = date("d.m.Y");
//

include("../../sc_conf/sc_conn_string");

	if (isset($report_go) or isset($xls_go)) {
		//проверка доступа к номерам
		$q=OCIParse($c,"select phone from SC_ACCESS_PHONE where login_id=".$_SESSION['login_id']);
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($q)) {
			$cdpns[$i]="'".OCIResult($q,"PHONE")."'";
			$i++;
		}
		if(isset($cdpns)) {
			$and_cdpns=" and phone in (".implode(",",$cdpns).") ";
		}			
		else {
			$and_cdpns="";
		}
	}

	if (isset($xls_go)) {
	header("Content-type: application/xls");
	header("Content-Disposition: attachment; filename=\"bil-".$_SESSION['start_bill_date']."-".$_SESSION['end_bill_date'].".xls\""); 
	}
	else {

	echo "<form method=post>";

	echo "<nobr><font size=4> Биллинг - \"".$_SESSION['project_name'][$_SESSION['i']]."\"</font> ";

	echo " c: <INPUT TYPE=TEXT NAME=start_bill_date value=".$_SESSION['start_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 

	echo " по: <INPUT TYPE=TEXT NAME=end_bill_date value=".$_SESSION['end_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A> (включительно)"; 

		//список номеров
		echo "<tr><td>";
	
		$q=OCIParse($c,"select phone from sc_phones
		where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
		".$and_cdpns);
		
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		$opt="";
			while(OCIFetch($q)) {
			$opt.="<option value=".OCIResult($q,"PHONE").">".OCIResult($q,"PHONE")."</option>";
			$phone=OCIResult($q,"PHONE");
			$i++;
			}
		if ($i==0) {echo "<font color=red> Ошибка! Проекту не назначено ни одного номера доступа!"; exit();}
		if ($i==1) {
		echo "<input type=hidden name=cgpn value=".$phone.">";
		}
		if ($i>1) {
		echo "<select name=cgpn>";
		if ($cgpn<>'') echo "<option value=".$cgpn.">".$cgpn."</option>";
		echo "<option value=>Все номера</option>";
		echo $opt;
		echo "</select>";
		}
	//
	echo "<INPUT type=submit name=report_go value=\"Показать отчет\"><INPUT type=submit name=xls_go value=\"XML\">";
	echo "</nobr></form><hr>";
}
//ОТЧЕТ
if (isset($report_go) or isset($xls_go)) {

	if ($cgpn=='') {
	$q=OCIParse($c,"select phone from sc_phones
where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
".$and_cdpns);
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
where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and phone='".$cgpn."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$nums="='".OCIResult($q,"PHONE")."'";
	}

include("../../sc_conf/ab_conn_string");

	//запросы $q_inc, $q_forw
	///*+ FIRST_ROWS*/
	$q_inc=OCIParse($abilling,"select  
	to_char(a.offered_date,'DD.MM.YYYY') START_DATE,
	to_char(a.offered_date,'HH24:MI:SS') START_TIME, 
	to_char(a.call_end_date,'HH24:MI:SS') END_TIME, 
	a.calling_num AON, 
	called_num CGPN,
	case 
	when a.connected_num is null then 'busy'
	else a.connected_num end EXT,
	a.agid, 
	
	round((a.call_end_date-a.connected_date)*24*60*60) DUR_SEC, 
	ceil((a.call_end_date-a.connected_date)*24*60) DUR_MIN,
	
	(a.alerting_date-a.offered_date)*24*60*60 IVR_SEC
	from cdr_calls a
	where 
	a.originated_date is null
	and a.offered_date BETWEEN to_date('".$_SESSION['start_bill_date']."','DD.MM.YYYY') AND to_date('".$_SESSION['end_bill_date']."','DD.MM.YYYY')+1 
	and connected_date is not null
	and a.called_num".$nums." 
	and a.offered_date >= to_date('".$_SESSION['start_date'][$_SESSION['i']]."','DD.MM.YYYY HH24:MI:SS')
	and 
	(a.call_end_date-a.connected_date)*24*60*60 > 5 
	order by start_date,start_time");
		
	///*+ FIRST_ROWS*/	
	$q_forw=OCIParse($abilling,"select case
         when length(a.called_num) = 7 or a.called_num like '8495%' or
              a.called_num like '8499%' then
          'msk'
         when a.called_num like '89%' then
          'mob'
         when a.called_num like '810%' then
          'mn'
         when a.called_num like '8%' then
          'mg'
		 when length(a.called_num) = 6 then
          'ip' 
         when length(a.called_num) = 4 then
          'op'
       end CALL_TYPE,
       to_char(a.originated_date, 'DD.MM.YYYY') START_DATE,
       to_char(a.originated_date, 'HH24:MI:SS') START_TIME,
       to_char(a.call_end_date, 'HH24:MI:SS') END_TIME,
       substr(substr(a.uui, instr(a.uui, 'CPN=') + 4),
              1,
              instr(substr(a.uui, instr(a.uui, 'CPN=') + 4), ';') - 1) AON,
       substr(substr(a.uui,instr(a.uui,'DNIS=')+5),1,instr(substr(a.uui,instr(a.uui,'DNIS=')+5),';')-1) CGPN,
       case
         when a.connected_num is null then
          'busy'  
         else
          a.calling_num
       end EXT,
       a.agid,
       round((a.call_end_date - a.originated_date) * 24 * 60 * 60) DUR_SEC,
       ceil((a.call_end_date - a.originated_date) * 24 * 60) DUR_MIN,
       called_num FORWARD_NUM,
       b.code CODE,
       b.coutry REGION
  from cdr_calls a, ab_tarif_mg b
 where a.originated_date BETWEEN to_date('".$_SESSION['start_bill_date']."', 'DD.MM.YYYY') AND
       to_date('".$_SESSION['end_bill_date']."', 'DD.MM.YYYY') + 1 
   and substr(substr(a.uui,instr(a.uui,'DNIS=')+5),1,instr(substr(a.uui,instr(a.uui,'DNIS=')+5),';')-1) ".$nums."
   and a.originated_date >= to_date('".$_SESSION['start_date'][$_SESSION['i']]."', 'DD.MM.YYYY HH24:MI:SS') 
   and (a.call_end_date - a.connected_date) * 24 * 60 * 60 > 5 --between 6 and 3600
   and called_num <> '#####'
   and ab_code(a.called_num) = b.code(+)
 order by start_date, start_time");
 	//
	//HTML
	if (isset($report_go)) { 
	echo "<a name=inc></a><font size=3><b>Входящие звонки</b></font> | <a href=#forw><b>Переадресация</b></a> | <a href=#itog><b>ИТОГО</b></a><br>";
	echo "Входящие звонки "; if ($cgpn<>'') {$num_txt="по номеру <b>".$cgpn."</b> "; echo $num_txt;} else {$num_txt="";}
	echo "за период с <b>".$_SESSION['start_bill_date']."</b> по <b>".$_SESSION['end_bill_date']."</b> включительно";
		
	inc_html($cgpn,$q_inc,$num_txt);
	
	echo "<a name=forw></a><a href=#inc><b>Входящие звонки</b></a> | <font size=3><b>Переадресация</b></font> | <a href=#itog><b>ИТОГО</b></a><br>";
	echo "Переадресованные звонки "; if ($cgpn<>'') echo "по номеру <b>".$cgpn."</b> ";
	echo "за период с <b>".$_SESSION['start_bill_date']."</b> по <b>".$_SESSION['end_bill_date']."</b> включительно";

	forw_html($cgpn,$q_forw,$num_txt);	

	echo "<a name=itog></a><a href=#inc><b>Входящие звонки</b></a> | <a href=#forw><b>Переадресация</b></a> | <font size=3><b>ИТОГО</b></font><br>";
	echo "Итого "; if ($cgpn<>'') echo "по номеру <b>".$cgpn."</b> ";
	echo "за период с <b>".$_SESSION['start_bill_date']."</b> по <b>".$_SESSION['end_bill_date']."</b> включительно";	
		
	echo "<table bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white align=center><b>ИТОГО</b></td>
	<td bgcolor=white align=center><b>Звонков</b></td>
	<td bgcolor=white align=center><b>Минут</b></td>
	</tr>
	<tr></tr>
	<tr>
	<td bgcolor=white><b>Входящих</b></td>
	<td bgcolor=white align=center>".$inc_cou."</td>
	<td bgcolor=white align=center>".$inc_min."</td>
	</tr>
	<tr></tr>
	<tr>
	<td bgcolor=white><b>Переадресованных</b></td>
	<td bgcolor=white align=center>".$forw_cou."</td>
	<td bgcolor=white align=center>".$forw_min."</td>
	</tr>
	<tr>
	<td bgcolor=white colspan=3>из них:</td>
	</tr>
	<tr>
	<td bgcolor=white><b>Москва</b></td>
	<td bgcolor=white align=center>".$msk."</td>
	<td bgcolor=white align=center>".$min_msk."</td>
	</tr>
	<tr>
	<td bgcolor=white><b>Мобильные</b></td>
	<td bgcolor=white align=center>".$mob."</td>
	<td bgcolor=white align=center>".$min_mob."</td>
	</tr>
	<tr>
	<td bgcolor=white><b>Межгород</b></td>
	<td bgcolor=white align=center>".$mg."</td>
	<td bgcolor=white align=center>".$min_mg."</td>
	</tr>
	<td bgcolor=white><b>Междунар</b></td>
	<td bgcolor=white align=center>".$mn."</td>
	<td bgcolor=white align=center>".$min_mn."</td>
	</tr>
	<td bgcolor=white><b>IP</b></td>
	<td bgcolor=white align=center>".$ip."</td>
	<td bgcolor=white align=center>".$min_ip."</td>
	</tr>	
	<td bgcolor=white><b>Внутренние</b></td>
	<td bgcolor=white align=center>".$op."</td>
	<td bgcolor=white align=center>".$min_op."</td>
	</tr></table>";
}// HTML

	//XML
	if (isset($xls_go)) {

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
  <Style ss:ID="s34">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"
    ss:Bold="1"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
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
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s40">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"
    ss:Bold="1"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="s43">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="h:mm:ss;@"/>
  </Style>
  <Style ss:ID="s44">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Color="#333333"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <NumberFormat ss:Format="0"/>
  </Style>
  <Style ss:ID="i1">
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9"/>
  </Style>
    <Style ss:ID="i2">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial Cyr" x:CharSet="204" ss:Bold="1"/>
  </Style>
  <Style ss:ID="i3">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial Cyr" x:CharSet="204" ss:Bold="1"/>
  </Style>
  <Style ss:ID="i4">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial Cyr" x:CharSet="204" ss:Bold="1"/>
  </Style>
  <Style ss:ID="i5">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Bold="1"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="i6">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Arial Cyr" x:CharSet="204"/>
  </Style>
  <Style ss:ID="i7">
   <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9" ss:Bold="1"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="i8">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="i9">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style>
    <Style ss:ID="i10">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font x:CharSet="204" x:Family="Swiss" ss:Size="9"/>
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
  </Style> 
 </Styles>';
		flush();
	
		echo ' <Worksheet ss:Name="Входяшие">
  <AutoFilter x:Range="R2C2:R2C9" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>  
  <Table x:FullColumns="1"
   x:FullRows="1">
   <Column ss:AutoFitWidth="0" ss:Width="33.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="68.25"/>';
   if ($cgpn=='') echo '<Column ss:AutoFitWidth="0" ss:Width="68.25"/>'; 
   echo '<Column ss:AutoFitWidth="0" ss:Width="52.5" ss:Span="1"/>
   <Column ss:AutoFitWidth="0" ss:Width="75"/>
   <Column ss:AutoFitWidth="0" ss:Width="56.25" ss:Span="1"/>
   <Column ss:AutoFitWidth="0" ss:Width="52.5" ss:Span="1"/>
   <Row>
    <Cell ss:StyleID="s21"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40">';
	echo "Входящие звонки "; if ($cgpn<>'') {$num_txt="по номеру <B>".$cgpn."</B> "; echo $num_txt;} else {$num_txt="";}
	echo "за период с <B>".$_SESSION['start_bill_date']."</B> по <B>".$_SESSION['end_bill_date']."</B> включительно";
	echo '</ss:Data></Cell></Row>';
	
	$itogo_inc=inc_xml($cgpn,$q_inc,$num_txt);
	
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
 
 	echo ' <Worksheet ss:Name="Переадресация">
  <AutoFilter x:Range="R2C2:R2C12" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>
  <Table x:FullColumns="1"
   x:FullRows="1">
   <Column ss:AutoFitWidth="0" ss:Width="33.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="68.25"/>';
	if ($cgpn=='') echo '<Column ss:AutoFitWidth="0" ss:Width="68.25"/>';   
	echo '<Column ss:AutoFitWidth="0" ss:Width="52.5" ss:Span="1"/>
   <Column ss:AutoFitWidth="0" ss:Width="78"/>
   <Column ss:AutoFitWidth="0" ss:Width="46.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="90"/>
   <Column ss:AutoFitWidth="0" ss:Width="75"/>
   <Column ss:AutoFitWidth="0" ss:Width="56.25" ss:Span="3"/>
   <Row>
    <Cell ss:StyleID="s21"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40">';
	echo "Переадресованные звонки "; if ($cgpn<>'') echo "по номеру <B>".$cgpn."</B> ";
	echo "за период с <B>".$_SESSION['start_bill_date']."</B> по <B>".$_SESSION['end_bill_date']."</B> включительно";	  
	echo '</ss:Data></Cell></Row>';
	
	$itogo_forw=forw_xml($cgpn,$q_forw,$num_txt);
	
	echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Layout x:Orientation="Landscape"/>
    <Header x:Margin="0.4"/>
    <PageMargins x:Bottom="0.6" x:Left="0.38" x:Right="0.41" x:Top="0.4"/>
   </PageSetup>
   <Print>
    <ValidPrinterInfo/>
    <PaperSizeIndex>9</PaperSizeIndex>
    <HorizontalResolution>600</HorizontalResolution>
    <VerticalResolution>0</VerticalResolution>
   </Print>
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
	
	echo '<Worksheet ss:Name="ИТОГО">
	
 <Table x:FullColumns="1" x:FullRows="1">
  <Column ss:AutoFitWidth="0" ss:Width="113.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="52.5" ss:Span="1"/>
    <Row>
    <Cell ss:StyleID="s21"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40">';
	echo "ИТОГО "; if ($cgpn<>'') echo "по номеру <B>".$cgpn."</B> ";
	echo "за период с <B>".$_SESSION['start_bill_date']."</B> по <B>".$_SESSION['end_bill_date']."</B> включительно";	  
	echo '</ss:Data></Cell></Row>';
 
    echo '<Row>
    <Cell ss:StyleID="i1"/>
    <Cell ss:StyleID="i2"><Data ss:Type="String">звонков</Data></Cell>
    <Cell ss:StyleID="i3"><Data ss:Type="String">минут</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i4"><Data ss:Type="String">Входящих</Data></Cell>
    <Cell ss:StyleID="i6"><Data ss:Type="Number">'.$inc_cou.'</Data></Cell>
    <Cell ss:StyleID="i6"><Data ss:Type="Number">'.$inc_min.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i5"><Data ss:Type="String">Пререадресованных</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$forw_cou.'</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$forw_min.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i10"><Data ss:Type="String">из них:</Data></Cell>
    <Cell ss:StyleID="i8"/>
    <Cell ss:StyleID="i8"/>
   </Row>
   <Row>
    <Cell ss:StyleID="i5"><Data ss:Type="String">Москва</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$msk.'</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$min_msk.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i5"><Data ss:Type="String">Мобильные</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$mob.'</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$min_mob.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i5"><Data ss:Type="String">Межгород</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$mg.'</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$min_mg.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i5"><Data ss:Type="String">Междунар.</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$mn.'</Data></Cell>
    <Cell ss:StyleID="i8"><Data ss:Type="Number">'.$min_mn.'</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="i7"><Data ss:Type="String">Внутренние</Data></Cell>
    <Cell ss:StyleID="i9"><Data ss:Type="Number">'.$op.'</Data></Cell>
    <Cell ss:StyleID="i9"><Data ss:Type="Number">'.$min_op.'</Data></Cell>
   </Row>
	</Table>
  </Worksheet></Workbook>';
	
	}//XML
//
}

function inc_html($cgpn,$q_inc,$num_txt) {
global $inc_cou;
global $inc_min;
echo  "<table bgcolor=gray cellspacing=1 cellpadding=2><tr>
<td bgcolor=white align=center><b>№</b></td>
<td bgcolor=white align=center><b>Откуда</b></td>";
	if ($cgpn=='') echo "<td bgcolor=white align=center><b>Куда</b></td>";
echo "<td bgcolor=white align=center><b>Внутр.<br>номер</b></td>
<td bgcolor=white align=center><b>ID<br>Оператора</b></td>
<td bgcolor=white align=center><b>Дата звонка</b></td>
<td bgcolor=white align=center><b>Начало звонка</b></td>
<td bgcolor=white align=center><b>Конец звонка</b></td>	
<td bgcolor=white align=center><b>Длит.(сек)</b></td>
<td bgcolor=white align=center><b>Длит.(мин)</b></td>	
</tr>";

OCIExecute($q_inc,OCI_DEFAULT);
$inc_cou=0;
$inc_min=0;
	while (OCIFetch($q_inc)) {
		echo "<tr>
		<td bgcolor=white align=center>".($inc_cou+1)."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"AON")."</td>";
			if ($cgpn=='') echo "<td bgcolor=white align=center>".OCIResult($q_inc,"CGPN")."</td>";
		echo "<td bgcolor=white align=center>".OCIResult($q_inc,"EXT")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"AGID")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"START_DATE")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"START_TIME")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"END_TIME")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"DUR_SEC")."</td>
		<td bgcolor=white align=center>".OCIResult($q_inc,"DUR_MIN")."</td>	
		</tr>";
		$inc_min+=OCIResult($q_inc,"DUR_MIN");		
		$inc_cou++;
		flush();
	}
echo "</table>";
OCIFreeStatement($q_inc);
	echo "<hr>";
}
function forw_html($cgpn,$q_forw,$num_txt) {
	global $forw_cou; global $msk; global $mob; global $mn; global $mg; global $ip; global $op; global $min_msk; global	$min_mob; global $min_mn; global $min_mg; global $min_ip; global $min_op; global	$forw_min;
	
	echo "<table bgcolor=gray cellspacing=1 cellpadding=2><tr>
	<td bgcolor=white align=center><b>№</b></td>
	<td bgcolor=white align=center><b>Откуда</b></td>";
	if ($cgpn=='') echo "<td bgcolor=white align=center><b>Куда</b></td>";
	echo "<td bgcolor=white align=center><b>Внутр.<br>номер</b></td>
	<td bgcolor=white align=center><b>ID<br>Оператора</b></td>
	<td bgcolor=white align=center><b>Переведен</b></td>
	<td bgcolor=white align=center><b>Код</b></td>
	<td bgcolor=white align=center><b>Город</b></td>
	<td bgcolor=white align=center><b>Дата звонка</b></td>
	<td bgcolor=white align=center><b>Начало звонка</b></td>
	<td bgcolor=white align=center><b>Конец звонка</b></td>	
	<td bgcolor=white align=center><b>Длит.(сек)</b></td>	
	<td bgcolor=white align=center><b>Длит.(мин)</b></td>	
	</tr>";

	OCIExecute($q_forw,OCI_DEFAULT);
	$forw_cou=0;
	$msk=0;	$mob=0;	$mn=0;	$mg=0; $ip=0; $op=0;
	$min_msk=0;	$min_mob=0;	$min_mn=0;	$min_mg=0; $min_ip=0; $min_op=0;
	$forw_min=0;
	while (OCIFetch($q_forw)) {
		echo "<tr>
		<td bgcolor=white align=center>".($forw_cou+1)."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"AON")."</td>";
		if ($cgpn=='') echo "<td bgcolor=white align=center>".OCIResult($q_forw,"CGPN")."</td>";
		echo "<td bgcolor=white align=center>".OCIResult($q_forw,"EXT")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"AGID")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"FORWARD_NUM")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"CODE")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"REGION")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"START_DATE")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"START_TIME")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"END_TIME")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"DUR_SEC")."</td>
		<td bgcolor=white align=center>".OCIResult($q_forw,"DUR_MIN")."</td>		
		</tr>";
		$forw_min+=OCIResult($q_forw,"DUR_MIN");		
		if (OCIResult($q_forw,"CALL_TYPE")=='msk') {$msk++; $min_msk+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mob') {$mob++; $min_mob+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mn') {$mn++; $min_mn+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mg') {$mg++; $min_mg+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='ip') {$ip++; $min_ip+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='op') {$op++; $min_op+=OCIResult($q_forw,"DUR_MIN");}
		$forw_cou++;
		flush();
	}
	echo "</table>";
	OCIFreeStatement($q_forw);	
	echo "<hr>";
}
function inc_xml($cgpn,$q_inc,$num_txt) {
global $inc_cou;
global $inc_min;
echo  '<Row ss:AutoFitHeight="0" ss:Height="25.5">
    <Cell ss:StyleID="s40"><Data ss:Type="String">№</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Откуда</Data></Cell>';
	if ($cgpn=='') echo '<Cell ss:StyleID="s40"><Data ss:Type="String">Куда</Data></Cell>';
	echo '<Cell ss:StyleID="s34"><Data ss:Type="String">Внутр. Номер</Data></Cell>
    <Cell ss:StyleID="s34"><Data ss:Type="String">ID оператора</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Дата звонка</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Начало звонка</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Конец звонка</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Длит.(сек)</Data></Cell>
    <Cell ss:StyleID="s40"><Data ss:Type="String">Длит.(мин)</Data></Cell>
   </Row>';

	OCIExecute($q_inc,OCI_DEFAULT);
	$inc_cou=0;
	$inc_min=0;
	while (OCIFetch($q_inc)) {
		echo '<Row>
		<Cell ss:StyleID="s35"><Data ss:Type="Number">'.($inc_cou+1).'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_inc,"AON").'</Data></Cell>';
		if ($cgpn=='') echo '<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_inc,"CGPN").'</Data></Cell>';
		echo '<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_inc,"EXT").'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_inc,"AGID").'</Data></Cell>
		<Cell ss:StyleID="s36"><Data ss:Type="String">'.OCIResult($q_inc,"START_DATE").'</Data></Cell>
		<Cell ss:StyleID="s43"><Data ss:Type="String">'.OCIResult($q_inc,"START_TIME").'</Data></Cell>
		<Cell ss:StyleID="s43"><Data ss:Type="String">'.OCIResult($q_inc,"END_TIME").'</Data></Cell>
		<Cell ss:StyleID="s44"><Data ss:Type="Number">'.OCIResult($q_inc,"DUR_SEC").'</Data></Cell>
		<Cell ss:StyleID="s44"><Data ss:Type="Number">'.OCIResult($q_inc,"DUR_MIN").'</Data></Cell>
		</Row>';
		$inc_min+=OCIResult($q_inc,"DUR_MIN");		
		$inc_cou++;
		flush();
	}
	OCIFreeStatement($q_inc);
}
function forw_xml($cgpn,$q_forw,$num_txt) {
	global $forw_cou; global $msk; global $mob; global $mn; global $mg; global $ip; global $op; global $min_msk; global	$min_mob; global $min_mn; global $min_mg; global $min_ip; global $min_op; global	$forw_min;
	echo '<Row ss:AutoFitHeight="0" ss:Height="25.5">
	<Cell ss:StyleID="s40"><Data ss:Type="String">№</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Откуда</Data></Cell>';
	if ($cgpn=='') echo '<Cell ss:StyleID="s40"><Data ss:Type="String">Куда</Data></Cell>';
	echo '<Cell ss:StyleID="s34"><Data ss:Type="String">Внутр. номер</Data></Cell>
	<Cell ss:StyleID="s34"><Data ss:Type="String">ID Оператора</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Переведен</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Код</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Город</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Дата звонка</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Начало звонка</Data></Cell>
	<Cell ss:StyleID="s40"><Data ss:Type="String">Конец звонка</Data></Cell>	
	<Cell ss:StyleID="s40"><Data ss:Type="String">Длит.(сек)</Data></Cell>	
	<Cell ss:StyleID="s40"><Data ss:Type="String">Длит.(мин)</Data></Cell>	
	</Row>';

	OCIExecute($q_forw,OCI_DEFAULT);
	$forw_cou=0;
	$msk=0;	$mob=0;	$mn=0;	$mg=0; $ip=0;	$op=0;
	$min_msk=0;	$min_mob=0;	$min_mn=0;	$min_mg=0; $min_ip=0;	$min_op=0;
	$dur_min=0;
	while (OCIFetch($q_forw)) {
		echo '<Row>
		<Cell ss:StyleID="s35"><Data ss:Type="Number">'.($forw_cou+1).'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"AON").'</Data></Cell>';
		if ($cgpn=='') echo '<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"CGPN").'</Data></Cell>';
		echo '<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"EXT").'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"AGID").'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"FORWARD_NUM").'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"CODE").'</Data></Cell>
		<Cell ss:StyleID="s35"><Data ss:Type="String">'.OCIResult($q_forw,"REGION").'</Data></Cell>
		<Cell ss:StyleID="s36"><Data ss:Type="String">'.OCIResult($q_forw,"START_DATE").'</Data></Cell>
		<Cell ss:StyleID="s43"><Data ss:Type="String">'.OCIResult($q_forw,"START_TIME").'</Data></Cell>
		<Cell ss:StyleID="s43"><Data ss:Type="String">'.OCIResult($q_forw,"END_TIME").'</Data></Cell>
		<Cell ss:StyleID="s44"><Data ss:Type="Number">'.OCIResult($q_forw,"DUR_SEC").'</Data></Cell>
		<Cell ss:StyleID="s44"><Data ss:Type="Number">'.OCIResult($q_forw,"DUR_MIN").'</Data></Cell>	
		</Row>';
		$dur_min+=OCIResult($q_forw,"DUR_MIN");		
		if (OCIResult($q_forw,"CALL_TYPE")=='msk') {$msk++; $min_msk+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mob') {$mob++; $min_mob+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mn') {$mn++; $min_mn+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='mg') {$mg++; $min_mg+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='ip') {$op++; $min_ip+=OCIResult($q_forw,"DUR_MIN");}
		else if (OCIResult($q_forw,"CALL_TYPE")=='op') {$op++; $min_op+=OCIResult($q_forw,"DUR_MIN");}
		$forw_cou++;
		flush();
	}
	OCIFreeStatement($q_forw);	
}

if (!isset($xls_go)) echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>';
?>