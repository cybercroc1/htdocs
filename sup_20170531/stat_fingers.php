<?php
session_name('tex');
session_start();

extract($_REQUEST);
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>'y' or $_SESSION['rep_stat']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

if(isset($fingers_xls)) {
header("Content-type: application/xls");
header("Content-Disposition: attachment; filename=\"fingers-".$start_date_day."-".$end_date_day.".xls\"");

$q_text="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'YYYY-MM-DD\"T\"HH24:MI:SS\".000\"') date_in_call,
	   to_char(nvl(b.in_work,b.date_close),'YYYY-MM-DD\"T\"HH24:MI:SS\".000\"') date_in_work,
	   to_char(b.date_close,'YYYY-MM-DD\"T\"HH24:MI:SS\".000\"') date_close,
	   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI:SS') date1,
	   to_char(nvl(b.in_work,nvl(b.date_close,sysdate)),'DD.MM.YYYY HH24:MI:SS') date2,
   	   to_char(nvl(b.date_close,sysdate),'DD.MM.YYYY HH24:MI:SS') date3,
       k.name,
       t.fio,
	   t.id texnari_id,
       b.kto,
       b.u_kogo,
       b.oper_comment,
	   b.trbl_grp_id,   
       case
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then
          'Открыта'
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then
          'В работе'
         when b.date_close is null and b.ready_to_close is not null then
		  'Гот.к пров.'
		 when b.date_close is not null then
          'Закрыта'
       end status,
       case  

         when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then
          'blue'
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then
          'green'
         when b.date_close is null and b.ready_to_close is not null then
		  '#001000'
		 when b.date_close is not null then
          'red'
       end color,
    '<B>'||to_char(trunc((nvl(b.date_close,sysdate)-b.date_in_call)))||'</B>д. <B>'||
     to_char(trunc(((nvl(b.date_close,sysdate)-b.date_in_call)-trunc((nvl(b.date_close,sysdate)-b.date_in_call)))*24))||'</B>ч.' dur_days,
	   round((nvl(b.date_close,sysdate)-b.date_in_call)*24,2) dur_hrs,

    '<B>'||to_char(trunc((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)))||'</B>д. <B>'||
     to_char(trunc(((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)-trunc((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)))*24))||'</B>ч.' dur_wrk_days,round((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)*24,2) dur_wrk_hrs,	
		 b.quality,
       case
	     when b.quality='1' then 'red'  
		 when b.quality='2' then 'red'
		 when b.quality='3' then '#CC6633'
		 when b.quality='4' then '#339966'
		 when b.quality='5' then 'green'
       end q_color,
	   b.quality_who,
	   b.quality_coment,
	   ph.phone,
	   b.cdpn
  from sup_base b, sup_klinika k, sup_user t, sup_trbl_alloc ta, sup_trbl_type tt, sup_klinika_phones ph, sup_lt sl
 where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.id=ta.base_id(+)
   and ta.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   and sl.location_id=k.id
   and sl.trbl_id=tt.id
   and sl.lt_grp_id=2
   and (b.date_in_call>to_date('".$start_date_day."','DD.MM.YYYY') or b.date_close is null)
   and (b.date_in_call<to_date('".$end_date_day."','DD.MM.YYYY')+1 or b.date_close is null) 
 order by d
";

$q_trbl=OCIParse($c,"select stt.id,stt.name, decode(sb.trbl_grp_id,stt.trbl_grp_id,'y',null) actual
from sup_base sb, sup_trbl_alloc sta,sup_trbl_type stt
where sb.id=:base_id
and sta.base_id=sb.id and stt.id=sta.trbl_type_id
order by stt.name");

$q=OCIParse($c,$q_text);
//echo $q_text;

echo '<?xml version="1.0" encoding="windows-1251"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Created>2013-09-29T06:38:04Z</Created>
  <Company>Grizli777</Company>
  <Version>12.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>9975</WindowHeight>
  <WindowWidth>17355</WindowWidth>
  <WindowTopX>960</WindowTopX>
  <WindowTopY>795</WindowTopY>
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
   <Alignment ss:Vertical="Bottom"/>
   <Borders/>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s63">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s64">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s65">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <NumberFormat ss:Format="General Date"/>
  </Style>
  <Style ss:ID="s66">
   <Alignment ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
  </Style>
  <Style ss:ID="s67">
   <Alignment ss:Horizontal="Center" ss:Vertical="Top" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"
     ss:Color="#000000"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000"/>
   <NumberFormat ss:Format="d&quot;д&quot;hh&quot;ч&quot;mm&quot;м&quot;"/>
  </Style>
  <Style ss:ID="s69">
   <Alignment ss:Horizontal="Left" ss:Vertical="Top" ss:WrapText="1"/>
  </Style>
  </Styles>
 <Worksheet ss:Name="Отчет">
  <Names>
   <NamedRange ss:Name="_FilterDatabase" ss:RefersTo="=Лист1!R2C1:R2C13"
    ss:Hidden="1"/>
  </Names>
  <Table ss:ExpandedColumnCount="13" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="54.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="100"/>
   <Column ss:AutoFitWidth="0" ss:Width="106.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="122.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="132"/>
   <Column ss:AutoFitWidth="0" ss:Width="140"/>   
   <Column ss:AutoFitWidth="0" ss:Width="98.25"/>
   <Column ss:Width="50"/>
   <Column ss:Width="44.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="100"/>
   <Column ss:Width="100"/>
';

echo '
   
<Row>
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
       html:Color="#000000">Техподдержка "Отпечатки пальцев" </Font><Font html:Color="#000000"> за период с </Font><B><Font
        html:Color="#000000">'.$start_date_day.' по '.$end_date_day.'</Font></B><Font
       html:Color="#000000"> включительно</Font></ss:Data></Cell>
   </Row>   
   
';


echo '<Row ss:AutoFitHeight="1" ss:Height="30">
    <Cell ss:StyleID="s63"><Data ss:Type="String">№ заявки</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Дата поступления заявки</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Объект</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Кто обратился</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Тип проблемы</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Суть проблемы</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	  
    <Cell ss:StyleID="s63"><Data ss:Type="String">Кто занимается</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Статус</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Дата принятия в работу</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Дата закрытия</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	    
    <Cell ss:StyleID="s63"><Data ss:Type="String">Наруш.регламента (статья)</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
   </Row>
   ';

$rownum=0;
OCIExecute($q,OCI_DEFAULT);
$search=array("&","<",">",chr(10));
$replace=array("&amp;","&lt;","&gt;","&#10;");
while(OCIFetch($q)) {
$rownum++;

	echo '<Row ss:AutoFitHeight="1" ss:Height="30">';
	echo '<Cell ss:StyleID="s64"><Data ss:Type="Number">'.OCIResult($q,"ID").'</Data></Cell>';
	echo '<Cell ss:StyleID="s65"><Data ss:Type="DateTime">'.OCIResult($q,"DATE_IN_CALL").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"NAME").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"KTO").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">';
	OCIBindByName($q_trbl,":base_id",OCIResult($q,"ID"));
	OCIExecute($q_trbl,OCI_DEFAULT);
	$w=0; while (OCIFetch($q_trbl)) {$w++;
		//if(OCIResult($q_trbl,"ACTUAL")=='y') echo "<FONT COLOR=BLACK>";
		//else echo "<FONT COLOR=GRAY>";
		if($w>1) echo "&#10;";
		echo OCIResult($q_trbl,"NAME");
	}
	echo '</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.str_replace($search,$replace,(OCIResult($q,"OPER_COMMENT"))).'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"FIO").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"STATUS").'</Data></Cell>';
	echo '<Cell ss:StyleID="s65">'; if(OCIResult($q,"DATE_IN_WORK")<>'') echo '<Data ss:Type="DateTime">'.OCIResult($q,"DATE_IN_WORK").'</Data>'; echo '</Cell>';
	echo '<Cell ss:StyleID="s65">'; if(OCIResult($q,"DATE_CLOSE")<>'') echo '<Data ss:Type="DateTime">'.OCIResult($q,"DATE_CLOSE").'</Data>'; echo '</Cell>';	
	echo '<Cell ss:StyleID="s63"><Data ss:Type="String">'.check_3_1_2(OCIResult($q,"DATE1"),OCIResult($q,"DATE2")).check_3_1_3(OCIResult($q,"DATE1"),OCIResult($q,"DATE3")).'</Data></Cell>';
	echo '</Row>
   ';
}
echo '<Row ss:AutoFitHeight="1" ss:Height="30"><Cell ss:StyleID="s62"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40">Итого строк: '.$rownum.'</ss:Data></Cell></Row>';
OCIFreeStatement($q);

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
    </Pane>
   </Panes>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
  <AutoFilter x:Range="R2C1:R2C11" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>
 </Worksheet>';
 
  echo '<Worksheet ss:Name="Статьи регламента">
  <Table ss:ExpandedColumnCount="1" ss:ExpandedRowCount="1" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="1131.75"/>
   <Row ss:AutoFitHeight="0" ss:Height="409.5">
    <Cell ss:StyleID="s69"><Data ss:Type="String">Выдержка из договора:&#10; &#10;3.1.2. Реагировать на заявки, поступившие в рабочие дни с 10:00 до 18:00ч. в течение двух часов с момента поступление заявки; &#10;3.1.3. Устранять выявленные по заявке работы в течение трех рабочих дней за исключением следующего периода с 1 по 5 и с 16 по 19 числа месяца, в этот период Исполнитель обязуется выполнить заявку в течение одного рабочего дня;&#10; &#10;5.1.1. В случае если Исполнитель нарушит своя обязанности согласно п.п.п 3.1.2. а именно не среагирует на Заявку по истечению следующего рабочего дня после поступление Заявки, на Исполнителя накладывается штраф удержание денежных средств из ежемесячной стоимости Т.О. в размере 500р. (Пятьсот рублей 00 копеек).&#10;5.1.2. Заказчик в праве удержать денежные средства из ежемесячной стоимости Т.О. в размере 500р. (Пятьсот рублей 00 копеек) за необоснованный день просрочки согласно 3.1.3.&#10;Т.е. нужен отчёт, который за выбранный период будет предоставлять следующую информацию:&#10;Список заявок по пальцам по группам нарушений, т.е. заявки с1-5 и с 16-19 вылолненные в регламент и с превышением. Аналогично в прочие дни.&#10;Заявки принятые по регламенты и нет. (до 2 часов, от 2 часов до 18:00 следующего дня, больше чем 18:00 следубщего дня ) &#10;Нужно сначала обсужить и может что-то нарисовать, потом программить.&#10;С уважением,&#10;Директор по информационным технологиям&#10;УК Все свои&#10;Цыркевич Виталий Борисович&#10;&#10;+7 (495) 646-97-27 доб.137</Data></Cell>
   </Row>
  </Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
   <ProtectObjects>False</ProtectObjects>
   <ProtectScenarios>False</ProtectScenarios>
  </WorksheetOptions>
 </Worksheet>';
  
echo '</Workbook>';
exit();
}
else {
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
	<link href="billing.css" rel="stylesheet" type="text/css">
	<title>Техподдержка Все-Свои</title>
	</head>
	<body leftmargin="3" topmargin="3">';

echo "<form method=post>";
echo "<table width=100%><tr><td align=left><font size=3><a href='statistic.php'>Общая статистика</a></font> | <font size=4>Отпечатки пальцев</font></td>";
echo "<td align=right>"; 
echo "<a href=tex.php>Вернуться к заявкам</a> | <a href=tex.php?exit><font color=red>выход</font></a></td></tr></table>";

$start_date_day=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
$end_date_day=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));

if(isset($day_xls) and !isset($trbl_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны типы проблем</font><br>";
if(isset($day_xls) and !isset($obj_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны объекты</font><br>";
if(isset($day_xls) and !isset($user_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны инженеры</font><br>";

echo "за период: ";
echo "c <input type=text value='"; if (isset($start_date_day)) echo $start_date_day; echo "' size=7 name=start_date_day onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date_day);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date_day)) echo $end_date_day; echo "' size=7 name=end_date_day onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date_day);return false; HIDEFOCUS' onchange=ok.click()>";
echo " <input type=submit name=fingers_xls value='в EXCEL'><br>";

echo "</form>";

echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_rep.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">';

}

function check_3_1_2($date1,$date2) {
$res='';
$date1=strtotime($date1);
$date2=strtotime($date2);

//Рабочее время по дням недели
$work_time[1]=array(10,00,18,00);
$work_time[2]=array(10,00,18,00);
$work_time[3]=array(10,00,18,00);
$work_time[4]=array(10,00,18,00);
$work_time[5]=array(10,00,18,00);

//лимит времени принятия в работу, сек (2 часа рабочего времени)
$to_work_time_limit=7200;
$to_work_limit_date='';
$tmp_date=mktime(0,0,0,date('m',$date1),date('d',$date1),date('Y',$date1));
$a=0;

//echo date('N',$tmp_date);
while($to_work_limit_date=='') {

if(mktime(0,0,0,date('m',$date1),date('d',$date1),date('Y',$date1))==$tmp_date) {
	
	if(!isset($work_time[date('N',$tmp_date)])) { //если выходной, то переносим дату открытия на начало след. дня
		$date1=mktime(0,0,0,date('m',$tmp_date),date('d',$tmp_date)+1,date('Y',$tmp_date));
		continue;
	}
	else if($date1>=mktime($work_time[date('N',$tmp_date)][2],$work_time[date('N',$tmp_date)][3],0,date('m',$date1),date('d',$date1),date('Y',$date1))) { //если дата открытия после конца рабочего времени, то переносим дату открытия на начало следующего дня
		$date1=mktime(0,0,0,date('m',$tmp_date),date('d',$tmp_date)+1,date('Y',$tmp_date));
		continue;
	}
	else if($date1<mktime($work_time[date('N',$tmp_date)][0],$work_time[date('N',$tmp_date)][1],0,date('m',$date1),date('d',$date1),date('Y',$date1))) { //если дата открытия до начала рабочего времени, то переносим дату открытия на начало рабочего времени
		$date1=mktime($work_time[date('N',$tmp_date)][0],$work_time[date('N',$tmp_date)][1],0,date('m',$tmp_date),date('d',$tmp_date),date('Y',$tmp_date));
	}
	if($date1+$to_work_time_limit-$a<=mktime($work_time[date('N',$tmp_date)][2],$work_time[date('N',$tmp_date)][3],0,date('m',$date1),date('d',$date1),date('Y',$date1))) {
		$to_work_limit_date=$date1+$to_work_time_limit-$a;	
	}
	else {
		$a=$a+(mktime($work_time[date('N',$tmp_date)][2],$work_time[date('N',$tmp_date)][3],0,date('m',$date1),date('d',$date1),date('Y',$date1))-$date1);
		$date1=mktime(0,0,0,date('m',$tmp_date),date('d',$tmp_date)+1,date('Y',$tmp_date));
	}
}

//echo date('N d.m.Y H:i:s',$tmp_date)."-".date('d.m.Y H:i:s',$date1)."<br>";

$tmp_date=mktime(0,0,0,date('m',$tmp_date),date('d',$tmp_date)+1,date('Y',$tmp_date));
}
//echo date('N d.m.Y H:i:s',$to_work_limit_date)."<br>";
if($date2>$to_work_limit_date) $res="3.1.2;";
return $res;
}

function check_3_1_3($date1,$date3) {
$res='';
$date1=strtotime($date1);
$date3=strtotime($date3);

//лимит закрытия заявки в сутках (рабочих) по числам месяца
$close_limit[1]=1;
$close_limit[2]=1;
$close_limit[3]=1;
$close_limit[4]=1;
$close_limit[5]=1;
$close_limit[6]=3;
$close_limit[7]=3;
$close_limit[8]=3;
$close_limit[9]=3;
$close_limit[10]=3;
$close_limit[11]=3;
$close_limit[12]=3;
$close_limit[13]=3;
$close_limit[14]=3;
$close_limit[15]=3;
$close_limit[16]=1;
$close_limit[17]=1;
$close_limit[18]=1;
$close_limit[19]=1;
$close_limit[20]=3;
$close_limit[21]=3;
$close_limit[22]=3;
$close_limit[23]=3;
$close_limit[24]=3;
$close_limit[25]=3;
$close_limit[26]=3;
$close_limit[27]=3;
$close_limit[28]=3;
$close_limit[29]=3;
$close_limit[30]=3;
$close_limit[31]=3;

$close_limit_date='';
$tmp_date=$date1;
$a=0;
//echo date('N j',$date1)."<br>";
while($a<=$close_limit[date('j',$date1)]) {

if(date('N',$tmp_date)<6) $a++;

//echo date('N j d.m.Y H:i:s',$tmp_date)."<br>";
$tmp_date=$tmp_date+86400;
}
$close_limit_date=$tmp_date;
if($date3>$close_limit_date) $res="3.1.3;";
return $res;
}
?>
