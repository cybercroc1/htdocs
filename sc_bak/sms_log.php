<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','300');
include("../../sc_conf/sc_session");
session_start();
$_SESSION['last_url']='sms_log.php';
extract($_REQUEST);
if (!isset($xls_go)) {
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

include("../../sc_conf/sc_conn_string");

	if (isset($xls_go)) {
	header("Content-type: application/xls");
	header("Content-Disposition: attachment; filename=\"bil-".$_SESSION['start_bill_date']."-".$_SESSION['end_bill_date'].".xls\""); 
	}
	else {

	echo "<form method=post>";

	echo "<nobr><font size=4> СМС-лог - \"".$_SESSION['project']['name']."\"</font> ";

	echo " c: <INPUT TYPE=TEXT NAME=start_bill_date value=".$_SESSION['start_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 

	echo " по: <INPUT TYPE=TEXT NAME=end_bill_date value=".$_SESSION['end_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A> (включительно)"; 

	//
	echo "<INPUT type=submit name=report_go value=\"Показать отчет\"><INPUT type=submit name=xls_go value=\"XML\">";
	echo "</nobr></form><hr>";
}
//ОТЧЕТ
if (isset($report_go) or isset($xls_go)) {

$q=OCIParse($c,"select to_char(t.datetime,'DD.MM.YYYY HH24:MI:SS') datetime,
to_char(t.datetime,'YYYY-MM-DD')||'T'||to_char(t.datetime,'HH24:MI:SS') datetime_xml,
t.fromphone,t.phone_list,t.error_num,t.message,t.summ_phone,t.summ_parts,t.packet_cost from SC_SMS_LOG t
where t.project_id='".$_SESSION['project']['id']."'
and t.datetime BETWEEN to_date('".$_SESSION['start_bill_date']."','DD.MM.YYYY') AND to_date('".$_SESSION['end_bill_date']."','DD.MM.YYYY')+1 
order by t.datetime");

	//HTML
	if (isset($report_go)) { 
	echo "СМС за период с <b>".$_SESSION['start_bill_date']."</b> по <b>".$_SESSION['end_bill_date']."</b> включительно";
	
	echo  "<table bgcolor=gray cellspacing=1 cellpadding=2><tr>
<td bgcolor=white align=center><b>Дата</b></td>
<td bgcolor=white align=center><b>Имя отправителя</b></td>
<td bgcolor=white align=center><b>Номера назначения</b></td>
<td bgcolor=white align=center><b>Результат отправки</b></td>
<td bgcolor=white align=center><b>Текст сообщения</b></td>
<td bgcolor=white align=center><b>Кол-во получателей</b></td>	
<td bgcolor=white align=center><b>Кол-во частей СМС</b></td>
</tr>";

OCIExecute($q, OCI_DEFAULT);
while(OCIFetch($q)) {
	echo  "<tr>
	<td bgcolor=white align=center>".OCIResult($q,"DATETIME")."</td>
	<td bgcolor=white align=center>".OCIResult($q,"FROMPHONE")."</td>
	<td bgcolor=white align=center>".OCIResult($q,"PHONE_LIST")."</td>
	<td bgcolor=white align=center>".OCIResult($q,"ERROR_NUM")."</td>
	<td bgcolor=white align=center>".OCIResult($q,"MESSAGE")."</td>
	<td bgcolor=white align=center>".OCIResult($q,"SUMM_PHONE")."</td>	
	<td bgcolor=white align=center>".OCIResult($q,"SUMM_PARTS")."</td>
</tr>";
}

		

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
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>Стародубов Виктор</Author>
  <LastAuthor>Стародубов Виктор</LastAuthor>
  <Created>2015-11-19T08:48:06Z</Created>
  <Company>Grizli777</Company>
  <Version>12.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>10500</WindowHeight>
  <WindowWidth>22935</WindowWidth>
  <WindowTopX>360</WindowTopX>
  <WindowTopY>105</WindowTopY>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 <Styles>
  <Style ss:ID="Default" ss:Name="Normal">
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <Interior/>
   <NumberFormat/>
   <Protection/>
  </Style>
  <Style ss:ID="s62">
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s63">
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s64">
   <NumberFormat ss:Format="@"/>
  </Style>
 </Styles>';

		echo ' <Worksheet ss:Name="Лист1">
  <Names>
   <NamedRange ss:Name="_FilterDatabase" ss:RefersTo="=Лист1!R2C1:R2C7"
    ss:Hidden="1"/>
  </Names>
  <Table ss:ExpandedColumnCount="7" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:Width="80.25"/>
   <Column ss:Width="89.25"/>
   <Column ss:Width="102"/>
   <Column ss:Width="100.5"/>
   <Column ss:Width="397.5"/>
   <Column ss:Width="69"/>
   <Column ss:Width="60.75"/>
   <Row ss:AutoFitHeight="0">
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40">СМС по проекту: "<B>'.$_SESSION['project']['name'].'</B>" за период с <B>'.$_SESSION['start_bill_date'].'</B> по <B>'.$_SESSION['end_bill_date'].'</B> включительно</ss:Data></Cell>
   </Row>';
	
	echo '<Row ss:AutoFitHeight="0" ss:StyleID="s62">
    <Cell><Data ss:Type="String">Дата</Data><NamedCell ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Имя отправителя</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Номера назначения</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Результат отправки</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Текст сообщения</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Получателей</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell><Data ss:Type="String">Частей СМС</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
   </Row>';

	OCIExecute($q, OCI_DEFAULT);
	while(OCIFetch($q)) {
	
	echo '<Row ss:AutoFitHeight="0">
    <Cell ss:StyleID="s63"><Data ss:Type="DateTime">'.OCIResult($q,"DATETIME_XML").'.000</Data></Cell>
    <Cell ss:StyleID="s64"><Data ss:Type="String">'.OCIResult($q,"FROMPHONE").'</Data></Cell>
    <Cell ss:StyleID="s64"><Data ss:Type="String">'.OCIResult($q,"PHONE_LIST").'</Data></Cell>
    <Cell><Data ss:Type="String">'.str_replace("<br>"," ",OCIResult($q,"ERROR_NUM")).'</Data></Cell>
    <Cell ss:StyleID="s64"><Data ss:Type="String">'.OCIResult($q,"MESSAGE").'</Data></Cell>
    <Cell><Data ss:Type="Number">'.OCIResult($q,"SUMM_PHONE").'</Data></Cell>
    <Cell><Data ss:Type="Number">'.OCIResult($q,"SUMM_PARTS").'</Data></Cell>
   </Row>';
	}
   
	echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <Unsynced/>
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
     <ActiveRow>0</ActiveRow>
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
  <AutoFilter x:Range="R2C1:R2C7" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>
 </Worksheet>
</Workbook>';
	
	}//XML
//
}


if (!isset($xls_go)) echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>';
?>