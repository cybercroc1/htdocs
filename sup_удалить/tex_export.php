<?php
session_name('tex');
session_start();
$sid=session_id();
extract($_REQUEST);
include("sup/sup_conn_string");

//описание переменных
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if(isset($week)) $start_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d")-7,date("Y")));
if(isset($month)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if(isset($year)) $start_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")-1));

$klinika_id='';
$trbl_id='';
//if (!isset($texnari_id) and $_SESSION['solution']=='y') $texnari_id=$_SESSION['user_id']; elseif(!isset($texnari_id)) 
$texnari_id='';
//if (!isset($kto_id) and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']=='y') $kto_id=$_SESSION['user_id']; 
//elseif (!isset($kto_id)) 
$kto_id='';
//if (!isset($lt_grp_id)) $lt_grp_id=$_SESSION['lt_grp_id']; 
//if (!isset($ok) and $_SESSION['eval']=='y' and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']<>'y')


header("Content-type: application/xls");
header("Content-Disposition: attachment; filename=\"rep-".$start_date."-".$end_date.".xls\""); 

$show_closed='';
$klinika_ids=array();
$klinika_names=array();
$trbl_ids=array();
$trbl_names=array();
$texnari_ids=array();
$texnari_names=array();
$kto_ids=array();
$kto_names=array();
$lt_grp_ids=array();
$lt_grp_names=array();
$trbl_grp_ids=array();

$q_where='';
$q_from='';
//

//смена группы
if(isset($ch_grp)) {
$klinika_id='';
$trbl_id='';
$texnari_id='';
}
//

//фильтр административных ограничений
if(!isset($_SESSION['user_id']) or !isset($_SESSION['export_where'])) {echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; echo "| <a href=/?exit><font color=red>выход</font></a>"; exit();}
//

//список групп
$q=OCIParse($c,"select g.id,g.name from SUP_USER_LT_ALLOC a, sup_lt_group g
where a.user_id=".$_SESSION['user_id']." and g.id=a.lt_group_id and g.type='common'");
OCIExecute($q,OCI_DEFAULT);
$i=0; while (OCIFetch($q)) {$i++;
	$lt_grp_ids[$i]=OCIResult($q,"ID");
}

$q_text="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'YYYY-MM-DD\"T\"HH24:MI:SS\".000\"') date_in_call,
       k.name,
	   b.trbl_type_id,
	   tt.name trbl_name,
	   b.trbl_detail_id,
       t.fio,
	   t.id texnari_id,
       b.kto,
       b.u_kogo,
       b.oper_comment,
       case
         when b.delay_to>sysdate then 300 --Отложена
		 when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then 100 --Открыта
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then 200 --В работе
         when b.date_close is null and b.ready_to_close is not null then 400 --Гот.к пров.
		 when b.date_close is not null then 500 --Закрыта
       end status_id,
    '<B>'||to_char(trunc((nvl(b.date_close,sysdate)-b.date_in_call)))||'</B>д. <B>'||
     to_char(trunc(((nvl(b.date_close,sysdate)-b.date_in_call)-trunc((nvl(b.date_close,sysdate)-b.date_in_call)))*24))||'</B>ч.' dur_days,
	   round((nvl(b.date_close,sysdate)-b.date_in_call)*24,2) dur_hrs,

    '<B>'||to_char(trunc((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)))||'</B>д. <B>'||
     to_char(trunc(((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)-trunc((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)))*24))||'</B>ч.' dur_wrk_days,
	   round((nvl(b.in_work,nvl(b.date_close,sysdate))-b.date_in_call)*24,2) dur_wrk_hrs,	   
	   
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
	   b.cdpn,
	   case when b.dublikat='y' then 'Дубликат' when b.krivie_ruki='y' then 'Ошибка' end dub_err
from sup_base b, sup_klinika k, sup_user t, sup_trbl_type tt, sup_klinika_phones ph, sup_lt slt 
	where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   and k.id=slt.location_id and tt.id=slt.trbl_id
   and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) 
   and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null)    
   ".$_SESSION['export_where']."
 order by d
";

$q_trbl_det=OCIParse($c,"select td.id,td.name from SUP_TRBL_DETAIL td
where td.id=:id
order by td.name");

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
  </Styles>
 <Worksheet ss:Name="Лист1">
  <Names>
   <NamedRange ss:Name="_FilterDatabase" ss:RefersTo="=Лист1!R2C1:R2C13"
    ss:Hidden="1"/>
  </Names>
  <Table ss:ExpandedColumnCount="15" x:FullColumns="1"
   x:FullRows="1" ss:DefaultRowHeight="15">
   <Column ss:AutoFitWidth="0" ss:Width="54.75"/>
   <Column ss:AutoFitWidth="0" ss:Width="100"/>
   <Column ss:AutoFitWidth="0" ss:Width="106.5"/>
   <Column ss:AutoFitWidth="0" ss:Width="122.25"/>
   <Column ss:AutoFitWidth="0" ss:Width="132"/>
   <Column ss:AutoFitWidth="0" ss:Width="132"/>   
   <Column ss:AutoFitWidth="0" ss:Width="140"/>   
   <Column ss:AutoFitWidth="0" ss:Width="98.25"/>
   <Column ss:Width="50"/>
   <Column ss:Width="50"/>   
   <Column ss:Width="44.25"/>
   <Column ss:Width="44.25"/>
   <Column ss:Width="44.25"/>
   <Column ss:Width="44.25"/>   
   <Column ss:Width="43.5"/>
';

echo '<Row>
    <Cell ss:StyleID="s62"><ss:Data ss:Type="String"
      xmlns="http://www.w3.org/TR/REC-html40"><Font html:Color="#000000">Пользователь: </Font>
	  <B><Font html:Color="#000000">'.$_SESSION['fio'].'. </Font></B>';
	  if(isset($_SESSION['export_grp_name']) and $_SESSION['export_grp_name']<>'') {
		  echo '<Font html:Color="#000000">Группа: </Font><B><Font html:Color="#000000">'.$_SESSION['export_grp_name'].'.</Font></B>';
	  }
	  echo '</ss:Data></Cell>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>	
    <Cell ss:StyleID="s62"/>	
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
	<Cell ss:StyleID="s62"/>
    <Cell ss:StyleID="s62"/>
	<Cell ss:StyleID="s62"/>	
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
    <Cell ss:StyleID="s63"><Data ss:Type="String">Деталь</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Суть проблемы</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	  
    <Cell ss:StyleID="s63"><Data ss:Type="String">Кто занимается</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Статус</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">Дубль/ Ошибка</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	  
    <Cell ss:StyleID="s63"><Data ss:Type="String">(ч)Вр.реакц.</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">(д)Вр.реакц.</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	    
	<Cell ss:StyleID="s63"><Data ss:Type="String">(ч)Длит.решен.</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
    <Cell ss:StyleID="s63"><Data ss:Type="String">(д)Длит.решен.</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>	  
    <Cell ss:StyleID="s63"><Data ss:Type="String">Оцен ка</Data><NamedCell
      ss:Name="_FilterDatabase"/></Cell>
   </Row>
   ';

//статусы
$q_stat=OCIParse($c,"select  name, color from sup_status where id=:id");   
   
$rownum=0;
OCIExecute($q,OCI_DEFAULT);
$search=array("&","<",">",chr(10));
$replace=array("&amp;","&lt;","&gt;","&#10;");
while(OCIFetch($q)) {
$tmp_det_id=OCIResult($q,"TRBL_DETAIL_ID");
$rownum++;

	//статусы
	$status_id=OCIResult($q,"STATUS_ID");
	OCIBindByName($q_stat,":id",$status_id);
	OCIExecute($q_stat,OCI_DEFAULT);
	OCIFetch($q_stat);
	$status_name=OCIResult($q_stat,"NAME");
	$status_color=OCIResult($q_stat,"COLOR");

	echo '<Row ss:AutoFitHeight="1" ss:Height="30">';
	echo '<Cell ss:StyleID="s64"><Data ss:Type="Number">'.OCIResult($q,"ID").'</Data></Cell>';
	echo '<Cell ss:StyleID="s65"><Data ss:Type="DateTime">'.OCIResult($q,"DATE_IN_CALL").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"NAME").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"KTO").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"TRBL_NAME").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">';
	OCIBindByName($q_trbl_det,":id",$tmp_det_id);
	OCIExecute($q_trbl_det,OCI_DEFAULT);
	$w=0; while (OCIFetch($q_trbl_det)) {$w++;
		if($w>1) echo "&#10;";
		echo OCIResult($q_trbl_det,"NAME");
	}
	echo '</Data></Cell>';	
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.str_replace($search,$replace,(OCIResult($q,"OPER_COMMENT"))).'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"FIO").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.$status_name.'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"DUB_ERR").'</Data></Cell>';	
	echo '<Cell ss:StyleID="s66"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q,"DUR_WRK_HRS")).'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.str_replace(',','.',OCIResult($q,"DUR_WRK_DAYS")).'</Data></Cell>';	
	echo '<Cell ss:StyleID="s66"><Data ss:Type="Number">'.str_replace(',','.',OCIResult($q,"DUR_HRS")).'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.str_replace(',','.',OCIResult($q,"DUR_DAYS")).'</Data></Cell>';	
	echo '<Cell ss:StyleID="s63"></Cell>';
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
  <AutoFilter x:Range="R2C1:R2C15" xmlns="urn:schemas-microsoft-com:office:excel">
  </AutoFilter>
 </Worksheet>
</Workbook>';

?>