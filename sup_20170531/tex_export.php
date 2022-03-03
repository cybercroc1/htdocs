<?php
session_name('tex');
session_start();
$sid=session_id();
extract($_REQUEST);
include("../../sup_conf/sup_conn_string");

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
if (!isset($lt_grp_id)) $lt_grp_id=$_SESSION['lt_grp_id']; 
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
if ($_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or  $_SESSION['create_new']<>'')) {
	
	//Только создатель
	if($_SESSION['look']<>'y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_only='';
		$no_kto='';
		$kto_id=$_SESSION['user_id'];
		$texnari_id='';
	//
	}
	//Создатель+обозреватель
	elseif ($_SESSION['look']=='y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_look='';
		//if($kto_id=='') $kto_id='auth_only'; //если раскомментировать, то заявки от анонимов не увидит создатель+обозреватель (закомментироано еще в 2-х местах
	}
	//
	//Список выбора технаря
	if($_SESSION['look']=='y' or $_SESSION['create_new']=='y') {
	}
	else if(($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y')) {
		$no_texn='';
		$texnari_id=$_SESSION['user_id'];
	}
	//
	//Список выбора групп
	if($_SESSION['lt_grp_id']==0) {
	}
	else {
		$q=OCIParse($c,"select id,name from sup_lt_group slg
		where id='".$lt_grp_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$lt_grp_ids[1]=OCIResult($q,"ID"); $lt_grp_names[1]=OCIResult($q,"NAME");
		$no_grp='';
	}
	//	
}
else {echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; echo "| <a href=tex.php?exit><font color=red>выход</font></a>"; exit();}
//
//
if($_SESSION['lt_grp_id']<>0) {
	//Список групп проблем
	$q=OCIParse($c,"select distinct stt.trbl_grp_id from SUP_LT slt, sup_trbl_type stt
	where slt.lt_grp_id='".$_SESSION['lt_grp_id']."'
	and stt.id=slt.trbl_id");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_grp_ids[$i]=OCIResult($q,"TRBL_GRP_ID");
	}
	//
	$q_from.=", sup_lt slt ";
	$q_where.="
	 	and k.id=slt.location_id and tt.id=slt.trbl_id and slt.lt_grp_id='".$_SESSION['lt_grp_id']."' ";
	if($i==1) $q_where.=" 
		and (b.trbl_grp_id='".$trbl_grp_ids[1]."' or b.trbl_grp_id is null)";
	elseif($i>1) $q_where.=" 
		and (b.trbl_grp_id in (".implode(',',$trbl_grp_ids).") or b.trbl_grp_id is null)";

	//ограничение по технарям и создателям
	if($_SESSION['solution']=='y' and  $_SESSION['create_new']<>'y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and (b.texnari_id='".$_SESSION['user_id']."' or b.texnari_id is null) ";	
	}
	else
	if($_SESSION['create_new']=='y' and $_SESSION['solution']<>'y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and b.kto_id='".$_SESSION['user_id']."' ";	
	}
	else
	if($_SESSION['create_new']=='y' and $_SESSION['solution']=='y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and (b.kto_id='".$_SESSION['user_id']."' or b.texnari_id='".$_SESSION['user_id']."' or b.texnari_id is null) ";
	} 
}
//
//фильтр выбора 
if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
if ($trbl_id<>"") $q_where.=" and tt.id='".$trbl_id."' ";
if ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
if ($kto_id=="not_auth") $q_where.=" and b.kto_id is null "; elseif ($kto_id=="auth_only") $q_where.=" and b.kto_id is not null "; elseif ($kto_id<>"") $q_where.=" and b.kto_id='".$kto_id."' ";
if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
//echo $q_where;
//
//echo $kto_id; //////////////////////////////////////////////////

$q_text="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'YYYY-MM-DD\"T\"HH24:MI:SS\".000\"') date_in_call,
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
  from sup_base b, sup_klinika k, sup_user t, sup_trbl_alloc ta, sup_trbl_type tt, sup_klinika_phones ph".$q_from."
 where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.id=ta.base_id(+)
   and ta.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   ".$q_where."
 order by d
";

$q_trbl=OCIParse($c,"select stt.id,stt.name, decode(sb.trbl_grp_id,stt.trbl_grp_id,'y',null) actual
from sup_base sb, sup_trbl_alloc sta,sup_trbl_type stt
where sb.id=:base_id
and sta.base_id=sb.id and stt.id=sta.trbl_type_id
order by stt.name");

$q_trbl_det=OCIParse($c,"select td.id,td.name from SUP_TRBL_DET_ALLOC tda, SUP_TRBL_DETAIL td
where tda.base_id=:base_id
and td.id=tda.trbl_detail_id
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
	if(isset($no_grp)) {
	  echo '<Font html:Color="#000000">Группа:</Font><B><Font html:Color="#000000">'.$lt_grp_names[1].'.</Font></B>';
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
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">';
	OCIBindByName($q_trbl_det,":base_id",OCIResult($q,"ID"));
	OCIExecute($q_trbl_det,OCI_DEFAULT);
	$w=0; while (OCIFetch($q_trbl_det)) {$w++;
		if($w>1) echo "&#10;";
		echo OCIResult($q_trbl_det,"NAME");
	}
	echo '</Data></Cell>';	
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.str_replace($search,$replace,(OCIResult($q,"OPER_COMMENT"))).'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"FIO").'</Data></Cell>';
	echo '<Cell ss:StyleID="s66"><Data ss:Type="String">'.OCIResult($q,"STATUS").'</Data></Cell>';
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