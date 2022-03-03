<?php
session_name('tex');
session_start();

extract($_REQUEST);
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>'y' or $_SESSION['max_rep_stat']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("sup/sup_conn_string");

if(isset($day_xls) and isset($trbl_ids) and isset($obj_ids) and isset($user_ids)) {

	header("Content-type: application/xls");
	header("Content-Disposition: attachment; filename=\"stat-".$start_date_day."-".$end_date_day.".xls\""); 

echo '<?xml version="1.0" encoding="windows-1251"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Version>12.00</Version>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12270</WindowHeight>
  <WindowWidth>28635</WindowWidth>
  <WindowTopX>120</WindowTopX>
  <WindowTopY>60</WindowTopY>
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
  <Style ss:ID="m78116416">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78116436">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78116456">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78116096">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1" ss:Italic="1"/>
  </Style>
  <Style ss:ID="m78116736">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78116756">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78116776">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="m78117056">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s16">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
  </Style>
  <Style ss:ID="s17">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s18">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:Bold="1"/>   
  </Style>
  <Style ss:ID="s181">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:Italic="1"/>   
  </Style>   
  <Style ss:ID="s19">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom" ss:WrapText="1"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s20">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s21">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s22">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <NumberFormat ss:Format="Short Date"/>
  </Style>
  <Style ss:ID="s24">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s25">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s28">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s29">
   <Alignment ss:Horizontal="Center" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
  <Style ss:ID="s30">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s31">
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="2"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
  </Style>
  <Style ss:ID="s43">
   <Alignment ss:Horizontal="Left" ss:Vertical="Bottom"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>
   </Borders>
   <Font ss:FontName="Calibri" x:CharSet="204" x:Family="Swiss" ss:Size="11"
    ss:Color="#000000" ss:Bold="1"/>
  </Style>
 </Styles>';

//по дням

echo '<Worksheet ss:Name="По дням">
  <Table x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>';


$q1=OCIParse($c,"select id,name from SUP_LOCATION_GROUP t order by name");
OCIExecute($q1); $loc_grps=array(); $columns=1; while (OCIFetch($q1)) {$loc_grps[OCIResult($q1,"ID")]=OCIResult($q1,"NAME"); $columns++;}
$sum_grp=array(); $sum=0; $sum_dub=0; $sum_kr=0;

echo '<Row>
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
       html:Color="#000000">количество открываемых заявок </Font><B><Font
        html:Color="#000000">по дням</Font></B><Font html:Color="#000000"> за период с </Font><B><Font
        html:Color="#000000">'.$start_date_day.' по '.$end_date_day.'</Font></B><Font
       html:Color="#000000"> включительно</Font></ss:Data></Cell>
   </Row>';

echo '<Row ss:Height="28">
    <Cell ss:StyleID="s19"><Data ss:Type="String">Дата</Data></Cell>';
    foreach($loc_grps as $key => $val) {
		echo '<Cell ss:StyleID="s19"><Data ss:Type="String">'.$val.'</Data></Cell>';
		$sum_grp[$val]=0;
	}
    echo '<Cell ss:StyleID="s19"><Data ss:Type="String">общее</Data></Cell>
		<Cell ss:StyleID="s19"><Data ss:Type="String">Дубли</Data></Cell>
		<Cell ss:StyleID="s19"><Data ss:Type="String">Ошибки</Data></Cell>
   </Row>';



$q2=OCIParse($c,"select count(*) cnt, count(dublikat) cnt_dub, count(krivie_ruki) cnt_kr from 
(select distinct b.id,b.dublikat,b.krivie_ruki from sup_base b, sup_klinika k
where b.date_in_call between to_date(:dat,'DD.MM.YYYY') and to_date(:dat,'DD.MM.YYYY')+1
and b.klinika_id in (".implode(",",$obj_ids).")
--and b.texnari_id in (".implode(",",$user_ids).") --считаются все заявки или только назначенные
and k.id=b.klinika_id
and k.location_grp_id=nvl(:loc_grp,k.location_grp_id)
and b.trbl_type_id in (".implode(",",$trbl_ids).")
)");

$date1=strtotime($start_date_day);
$date2=strtotime($end_date_day);
while ($date1<=$date2) {
	$date=date('d.m.Y',$date1);
	echo '<Row>
    <Cell ss:StyleID="s22"><Data ss:Type="DateTime">'.date('Y-m-d',$date1).'T00:00:00.000</Data></Cell>';
	foreach($loc_grps as $key=>$val) {
		OCIBindByName($q2,":dat",$date); OCIBindByName($q2,":loc_grp",$key);
		OCIExecute($q2); OCIFetch($q2);
		echo '<Cell ss:StyleID="s25"><Data ss:Type="Number">'.OCIResult($q2,"CNT").'</Data></Cell>';
		$sum_grp[$val]+=OCIResult($q2,"CNT");
	}
	$key=''; OCIBindByName($q2,":dat",$date); OCIBindByName($q2,":loc_grp",$key);
	OCIExecute($q2); OCIFetch($q2);
	echo '<Cell ss:StyleID="s19"><Data ss:Type="Number">'.OCIResult($q2,"CNT").'</Data></Cell>';
	echo '<Cell ss:StyleID="s25">'; echo OCIResult($q2,"CNT_DUB")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_DUB").'</Data>'; echo'</Cell>';
	echo '<Cell ss:StyleID="s25">'; echo OCIResult($q2,"CNT_KR")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_KR").'</Data>'; echo'</Cell>';
	echo '</Row>';
	$sum+=OCIResult($q2,"CNT");
	$sum_dub+=OCIResult($q2,"CNT_DUB");
	$sum_kr+=OCIResult($q2,"CNT_KR");
	$date1=strtotime("+1 day", $date1);
}
	//ИТОГО
	echo '<Row>
    <Cell ss:StyleID="s22"><Data ss:Type="String">ИТОГО:</Data></Cell>';
	foreach($loc_grps as $key=>$val) {
		echo '<Cell ss:StyleID="s19"><Data ss:Type="Number">'.$sum_grp[$val].'</Data></Cell>';
	}
	echo '<Cell ss:StyleID="s19"><Data ss:Type="Number">'.$sum.'</Data></Cell>
	<Cell ss:StyleID="s25"><Data ss:Type="Number">'.$sum_dub.'</Data></Cell>
	<Cell ss:StyleID="s25"><Data ss:Type="Number">'.$sum_kr.'</Data></Cell>
	</Row>';

echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
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

//по типам проблем

echo '<Worksheet ss:Name="По типам проблем">
  <Table ss:ExpandedColumnCount="7" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="1" ss:Width="261"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>   
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>';

echo '<Row>
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
       html:Color="#000000">количество открываемых заявок и средняя длительность закрытия в часах по </Font><B><Font
        html:Color="#000000">типам проблем</Font></B><Font html:Color="#000000"> за период с </Font><B><Font
        html:Color="#000000">'.$start_date_day.' по '.$end_date_day.'</Font></B><Font
       html:Color="#000000"> включительно</Font></ss:Data></Cell>
   </Row>';

echo '<Row ss:Height="28">
    <Cell ss:StyleID="s19"><Data ss:Type="String">Тип проблемы</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">кол-во</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">дублей</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">ошибок</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.закрытия(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции</Data></Cell>	
   </Row>';


$q1=OCIParse($c,"select tg.name group_name, tg.id group_id, tt.name trbl_name,tt.id trbl_id
from sup_trbl_group tg, sup_trbl_type tt
where tt.trbl_grp_id=tg.id
and tt.id in (".implode(",",$trbl_ids).")
and tt.deleted is null
order by tg.name,tt.name");

$q2=OCIParse($c,"select count(*) cnt, count(b.dublikat) cnt_dub, count(b.krivie_ruki) cnt_kr, round(avg(nvl(b.date_close,sysdate)-b.date_in_call)*24,2) avg_hrs, 
round(avg(nvl(b.in_work,nvl(b.ready_to_close,b.date_close))-b.date_in_call)*24,2) avg_react  
from sup_base b, sup_trbl_type tt
where b.date_in_call between to_date('".$start_date_day."','DD.MM.YYYY') and to_date('".$end_date_day."','DD.MM.YYYY')+1
and b.klinika_id in (".implode(",",$obj_ids).")
--and b.texnari_id in (".implode(",",$user_ids).") --считаются все заявки или только назначенные
and b.trbl_type_id=tt.id
and tt.id=:trbl_id");

$q3=OCIParse($c,"select id,name from SUP_TRBL_DETAIL t
where trbl_id=:trbl_id
order by ord");

$q4=OCIParse($c,"select count(*) cnt, count(b.dublikat) cnt_dub, count(b.krivie_ruki) cnt_kr, round(avg(nvl(b.date_close,sysdate)-b.date_in_call)*24,2) avg_hrs, 
round(avg(nvl(b.in_work,nvl(b.ready_to_close,b.date_close))-b.date_in_call)*24,2) avg_react  
from sup_base b, sup_trbl_detail dd
where b.date_in_call between to_date('".$start_date_day."','DD.MM.YYYY') and to_date('".$end_date_day."','DD.MM.YYYY')+1
and b.klinika_id in (".implode(",",$obj_ids).")
--and b.texnari_id in (".implode(",",$user_ids).") --считаются все заявки или только назначенные
and b.trbl_detail_id=dd.id
and dd.id=:trbl_det_id");

OCIExecute($q1);
$group_id='';
while (OCIFetch($q1)) {
	$tmp_q1_trbl_id=OCIResult($q1,"TRBL_ID");
	//$tmp_q1_group_id=OCIResult($q1,"GROUP_ID");
	if(OCIResult($q1,"GROUP_ID")<>$group_id) {
		echo '<Row>
    <Cell ss:MergeAcross="4" ss:StyleID="m78116096"><Data ss:Type="String">'.OCIResult($q1,"GROUP_NAME").'</Data></Cell>
   </Row>';
	}
	$group_id=OCIResult($q1,"GROUP_ID");

	echo '<Row>
    <Cell ss:StyleID="s18"><Data ss:Type="String">'.OCIResult($q1,"TRBL_NAME").'</Data></Cell>';
	OCIBindByName($q2,":trbl_id",$tmp_q1_trbl_id);
	//OCIBindByName($q2,":trbl_grp_id",$tmp_q1_group_id);
	OCIExecute($q2);
	OCIFetch($q2);
	
	echo '<Cell ss:StyleID="s18"><Data ss:Type="Number">'.OCIResult($q2,"CNT").'</Data></Cell>';
	echo '<Cell ss:StyleID="s18">'; echo OCIResult($q2,"CNT_DUB")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_DUB").'</Data>'; echo '</Cell>';	
	echo '<Cell ss:StyleID="s18">'; echo OCIResult($q2,"CNT_KR")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_KR").'</Data>'; echo '</Cell>';
    echo '<Cell ss:StyleID="s18"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_HRS")).'</Data></Cell>
    <Cell ss:StyleID="s18"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_REACT")).'</Data></Cell>
	<Cell ss:StyleID="s18"
     ss:Formula="=CONCATENATE(INT(INT(RC[-1])/24),&quot;д &quot;,INT(RC[-1]-INT(INT(RC[-1])/24)*24),&quot;ч &quot;, ROUND((RC[-1]-INT(RC[-1]))/100*6000,0),&quot;м &quot;)"><Data
      ss:Type="String">5д 12ч 21м </Data></Cell>
   </Row>';

	OCIBindByName($q3,":trbl_id",$tmp_q1_trbl_id);
	OCIExecute($q3);
	while(OCIFetch($q3)) {
		$tmp_q3_id=OCIResult($q3,"ID");
		echo '<Row>
    		<Cell ss:StyleID="s181"><Data ss:Type="String">'.OCIResult($q3,"NAME").'</Data></Cell>';	
		//OCIBindByName($q4,":trbl_id",$tmp_q1_trbl_id);	
		OCIBindByName($q4,":trbl_det_id",$tmp_q3_id);
		OCIExecute($q4);
		OCIFetch($q4);
		echo '<Cell ss:StyleID="s181"><Data ss:Type="Number">'.OCIResult($q4,"CNT").'</Data></Cell>';
		echo '<Cell ss:StyleID="s181">'; echo OCIResult($q4,"CNT_DUB")==0?'':'<Data ss:Type="Number">'.OCIResult($q4,"CNT_DUB").'</Data>'; echo '</Cell>';
		echo '<Cell ss:StyleID="s181">'; echo OCIResult($q4,"CNT_KR")==0?'':'<Data ss:Type="Number">'.OCIResult($q4,"CNT_KR").'</Data>'; echo '</Cell>';
    	echo '<Cell ss:StyleID="s181"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q4,"AVG_HRS")).'</Data></Cell>
    	<Cell ss:StyleID="s181"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q4,"AVG_REACT")).'</Data></Cell>
		<Cell ss:StyleID="s181"
     	ss:Formula="=CONCATENATE(INT(INT(RC[-1])/24),&quot;д &quot;,INT(RC[-1]-INT(INT(RC[-1])/24)*24),&quot;ч &quot;, ROUND((RC[-1]-INT(RC[-1]))/100*6000,0),&quot;м &quot;)"><Data
      ss:Type="String">5д 12ч 21м </Data></Cell>
	   </Row>';			
	}
}
echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
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


//по объектам

echo ' <Worksheet ss:Name="По объектам">
  <Table ss:ExpandedColumnCount="7" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="1" ss:Width="198"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>   
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>';

echo '<Row>
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
       html:Color="#000000">количество открываемых заявок и средняя длительность закрытия в часах </Font><B><Font
        html:Color="#000000">по объектам</Font></B><Font html:Color="#000000"> за период с </Font><B><Font
        html:Color="#000000">'.$start_date_day.' по '.$end_date_day.'</Font></B><Font
       html:Color="#000000"> включительно</Font></ss:Data></Cell>
   </Row>';

echo '<Row ss:Height="28">
    <Cell ss:StyleID="s19"><Data ss:Type="String">Объект</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">кол-во</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">дублей</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">ошибок</Data></Cell>	
    <Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.закрытия(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции</Data></Cell>
   </Row>';

$q1=OCIParse($c,"select slg.name group_name, slg.id group_id,k.name loc_name,k.id loc_id
from sup_klinika k, sup_location_group slg
where k.location_grp_id=slg.id
and k.id in (".implode(",",$obj_ids).")
and k.deleted is null
order by slg.name,k.name");

$q2=OCIParse($c,"select count(*) cnt, count(dublikat) cnt_dub, count(krivie_ruki) cnt_kr, round(avg(nvl(date_close,sysdate)-date_in_call)*24,2) avg_hrs, 
round(avg(nvl(in_work,nvl(ready_to_close,date_close))-date_in_call)*24,2) avg_react  
from
(select distinct b.id,b.date_in_call,b.in_work,b.ready_to_close,b.date_close,b.dublikat,b.krivie_ruki from sup_base b
where b.date_in_call between to_date('".$start_date_day."','DD.MM.YYYY') and to_date('".$end_date_day."','DD.MM.YYYY')+1
and b.klinika_id=:loc_id
--and b.texnari_id in (".implode(",",$user_ids).")  --считаются все заявки или только назначенные
and b.trbl_type_id in (".implode(",",$trbl_ids).")
)");

OCIExecute($q1);
$group_id='';
while (OCIFetch($q1)) {
	$tmp_q1_loc_id=OCIResult($q1,"LOC_ID");
	if(OCIResult($q1,"GROUP_ID")<>$group_id) {
		echo '<Row>
    <Cell ss:MergeAcross="4" ss:StyleID="m78116736"><Data ss:Type="String">'.OCIResult($q1,"GROUP_NAME").'</Data></Cell>
   </Row>';
	}
	$group_id=OCIResult($q1,"GROUP_ID");
	echo '<Row>
    <Cell ss:StyleID="s17"><Data ss:Type="String">'.OCIResult($q1,"LOC_NAME").'</Data></Cell>';
	OCIBindByName($q2,":loc_id",$tmp_q1_loc_id);
	OCIExecute($q2);
	OCIFetch($q2);
	echo '<Cell ss:StyleID="s17"><Data ss:Type="Number">'.OCIResult($q2,"CNT").'</Data></Cell>';
	echo '<Cell ss:StyleID="s17">'; echo OCIResult($q2,"CNT_DUB")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_DUB").'</Data>'; echo '</Cell>';
	echo '<Cell ss:StyleID="s17">'; echo OCIResult($q2,"CNT_KR")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_KR").'</Data>'; echo '</Cell>';	
    echo '<Cell ss:StyleID="s17"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_HRS")).'</Data></Cell>
	<Cell ss:StyleID="s17"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_REACT")).'</Data></Cell>
	<Cell ss:StyleID="s17"
     ss:Formula="=CONCATENATE(INT(INT(RC[-1])/24),&quot;д &quot;,INT(RC[-1]-INT(INT(RC[-1])/24)*24),&quot;ч &quot;, ROUND((RC[-1]-INT(RC[-1]))/100*6000,0),&quot;м &quot;)"><Data
      ss:Type="String">5д 12ч 21м </Data></Cell>
   </Row>';
	
}
echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
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

//по инженерам

echo '<Worksheet ss:Name="По инженерам">
  <Table ss:ExpandedColumnCount="7" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="264.75"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>   
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="54"/>
   <Column ss:AutoFitWidth="1" ss:Width="60"/>';
 
echo '<Row>
    <Cell><ss:Data ss:Type="String" xmlns="http://www.w3.org/TR/REC-html40"><Font
       html:Color="#000000">количество открываемых заявок и средняя длительность закрытия в часах </Font><B><Font
        html:Color="#000000">по инженерам</Font></B><Font html:Color="#000000"> за период с </Font><B><Font
        html:Color="#000000">'.$start_date_day.' по '.$end_date_day.'</Font></B><Font
       html:Color="#000000"> включительно</Font></ss:Data></Cell>
   </Row>';    

//echo "количество открываемых заявок и средняя длительность закрытия в часах по инженерам за период с $start_date_day по $end_date_day включительно";
//echo "<table border=1>";
//echo "<tr>";
//echo "<th>Инженер</th><th>кол-во</th><th>ср.длит(ч)</th>";

echo '<Row ss:Height="28">
    <Cell ss:StyleID="s19"><Data ss:Type="String">Инженер</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">кол-во</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">дублей</Data></Cell>
    <Cell ss:StyleID="s19"><Data ss:Type="String">ошибок</Data></Cell>	
    <Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.закрытия(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции(ч)</Data></Cell>
	<Cell ss:StyleID="s19"><Data ss:Type="String">ср.вр.реакции</Data></Cell>
   </Row>';

$q1=OCIParse($c,"select su.fio fio, su.id user_id
from sup_user su
where su.id in (".implode(",",$user_ids).")
and su.deleted is null --and su.solution is not null
order by su.fio");

$q2=OCIParse($c,"

select count(*) cnt, count(dublikat) cnt_dub, count(krivie_ruki) cnt_kr, round(avg(nvl(date_close,sysdate)-date_in_call)*24,2) avg_hrs, 
round(avg(nvl(in_work,nvl(ready_to_close,date_close))-date_in_call)*24,2) avg_react  
from
(select distinct b.id,b.date_in_call,b.in_work,b.ready_to_close,b.date_close,b.dublikat,b.krivie_ruki from sup_base b
where b.date_in_call between to_date('".$start_date_day."','DD.MM.YYYY') and to_date('".$end_date_day."','DD.MM.YYYY')+1
and b.klinika_id in (".implode(",",$obj_ids).")
and b.texnari_id=:user_id  
and b.trbl_type_id in (".implode(",",$trbl_ids).")
)");

OCIExecute($q1);
$group_id='';
while (OCIFetch($q1)) {
	$tmp_q1_user_id=OCIResult($q1,"USER_ID");
	echo '<Row>
    <Cell ss:StyleID="s17"><Data ss:Type="String">'.OCIResult($q1,"FIO").'</Data></Cell>';
	OCIBindByName($q2,":user_id",$tmp_q1_user_id);
	OCIExecute($q2);
	OCIFetch($q2);
	//echo "<td>".OCIResult($q2,"CNT")."</td><td>".OCIResult($q2,"AVG_HRS")."</td>";		
	//echo "</tr>";
	echo '<Cell ss:StyleID="s17"><Data ss:Type="Number">'.OCIResult($q2,"CNT").'</Data></Cell>';
	echo '<Cell ss:StyleID="s17">'; echo OCIResult($q2,"CNT_DUB")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_DUB").'</Data>'; echo '</Cell>';
	echo '<Cell ss:StyleID="s17">'; echo OCIResult($q2,"CNT_KR")==0?'':'<Data ss:Type="Number">'.OCIResult($q2,"CNT_KR").'</Data>'; echo '</Cell>';		
    echo '<Cell ss:StyleID="s17"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_HRS")).'</Data></Cell>
	<Cell ss:StyleID="s17"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q2,"AVG_REACT")).'</Data></Cell>	
	<Cell ss:StyleID="s17"
     ss:Formula="=CONCATENATE(INT(INT(RC[-1])/24),&quot;д &quot;,INT(RC[-1]-INT(INT(RC[-1])/24)*24),&quot;ч &quot;, ROUND((RC[-1]-INT(RC[-1]))/100*6000,0),&quot;м &quot;)"><Data
      ss:Type="String">5д 12ч 21м </Data></Cell>
   </Row>';
}

//echo "</table>";
echo '</Table>
  <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
   <PageSetup>
    <Header x:Margin="0.3"/>
    <Footer x:Margin="0.3"/>
    <PageMargins x:Bottom="0.75" x:Left="0.7" x:Right="0.7" x:Top="0.75"/>
   </PageSetup>
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
echo '</Workbook>';
//
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
echo "<table width=100%><tr><td align=left><font size=4>Общая статистика</font></td>";
echo "<td align=right>"; 
echo "<a href=/>Вернуться к заявкам</a> | <a href=/?exit><font color=red>выход</font></a></td></tr></table>";

$start_date_day=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
$end_date_day=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));

if(isset($day_xls) and !isset($trbl_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны типы проблем</font><br>";
if(isset($day_xls) and !isset($obj_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны объекты</font><br>";
if(isset($day_xls) and !isset($user_ids))  echo "<font color=red size=3>ОШИБКА: Не выбраны инженеры</font><br>";

echo "за период: ";
echo "c <input type=text value='"; if (isset($start_date_day)) echo $start_date_day; echo "' size=7 name=start_date_day onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date_day);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date_day)) echo $end_date_day; echo "' size=7 name=end_date_day onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date_day);return false; HIDEFOCUS' onchange=ok.click()>";
echo " <input type=submit name=day_xls value='в EXCEL'><br>";
echo "<font color=black><b>Выберите типы проблем/ообекты/инженеров, которые будут учитываться в отчете.</b></font><br>";
echo "<font color=red><b>Что бы выбрать несколько значений зажмите клавишу CTRL, или проведите мышкой с зажатой левой кнопкой</b></font><br>";

echo "<table cellpadding=1 cellspacing=1 bgcolor=black>";
echo "<tr><th bgcolor=white>Типы проблем</th><th bgcolor=white>Объекты</th><th bgcolor=white>Инженеры</th></tr>";
echo "<tr><td valign=top bgcolor=white>";

$q1=OCIParse($c,"select tg.name group_name, tg.id group_id, tt.name trbl_name,tt.id trbl_id, tt.ord, tt.color
from sup_trbl_group tg, sup_trbl_type tt
where tt.trbl_grp_id=tg.id
and tt.deleted is null
order by tg.name, tt.ord nulls first, tt.name");

echo "<select multiple id=trbl_ids name=trbl_ids[]>";

OCIExecute($q1);
$group_id='';
$size=0; while (OCIFetch($q1)) {
	if(OCIResult($q1,"GROUP_ID")<>$group_id) {
		$size++; echo "<optgroup label='".OCIResult($q1,"GROUP_NAME")."'></optgroup>";
	}
	$group_id=OCIResult($q1,"GROUP_ID");

	$size++; echo "<option value='".OCIResult($q1,"TRBL_ID")."' style='color:".OCIResult($q1,"COLOR")."'>".OCIResult($q1,"TRBL_NAME")."</option>";
}

echo "</select>";
echo "</td>";
echo "<script>document.all.trbl_ids.size=".$size.";</script>";
echo "<td valign=top bgcolor=white>";

$q1=OCIParse($c,"select slg.name group_name, slg.id group_id,k.name loc_name,k.id loc_id
from sup_klinika k, sup_location_group slg
where k.location_grp_id=slg.id
and k.deleted is null
order by slg.name,k.name");

echo "<select multiple id=obj_ids name=obj_ids[]>";

OCIExecute($q1);
$group_id='';
$size=0; while (OCIFetch($q1)) {
	if(OCIResult($q1,"GROUP_ID")<>$group_id) {
		$size++; echo "<optgroup label='".OCIResult($q1,"GROUP_NAME")."'></optgroup>";
	}
	$group_id=OCIResult($q1,"GROUP_ID");
	$size++; echo "<option value='".OCIResult($q1,"LOC_ID")."'>".OCIResult($q1,"LOC_NAME")."</option>";
}
echo "</select>";
echo "</td>";
echo "<script>document.all.obj_ids.size=".$size.";</script>";
echo "<td valign=top bgcolor=white>";

$q1=OCIParse($c,"select decode(slg.id,null,'без групп',slg.name) group_name, slg.id group_id,su.fio fio, su.id user_id
from sup_user su, sup_user_lt_alloc sla ,sup_lt_group slg
where sla.lt_group_id=slg.id(+)
and su.id=sla.user_id(+)
and su.deleted is null and sla.solution='y'
order by slg.name, su.fio");

echo "<select multiple id=user_ids name=user_ids[]>";

OCIExecute($q1);
$group_id='';
$size=0; while (OCIFetch($q1)) {
	if(OCIResult($q1,"GROUP_ID")<>$group_id) {
		$size++; echo "<optgroup label='".OCIResult($q1,"GROUP_NAME")."'></optgroup>";
	}
	$group_id=OCIResult($q1,"GROUP_ID");
	$size++; echo "<option value='".OCIResult($q1,"USER_ID")."'>".OCIResult($q1,"FIO")."</option>";
}
echo "</select>";
echo "</td></tr>";
echo "<script>document.all.user_ids.size=".$size.";</script>";
echo "</table>";

echo "</form>";

echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_rep.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">';

}

?>
