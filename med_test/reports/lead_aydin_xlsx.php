<?php
require_once 'med/check_auth.php';
//Проверка прав доступа к данному отчету
$report_id=11;
if(!isset($_SESSION['access']['report'][$report_id])) {
	echo "<div style='color:red'>Ошибка: доступ запрещен</div></br>";
	exit();
}

function show_array($arr,$lvl=0,$varnames=array()) {
    if(is_array($arr) && count($arr)>0) {echo "<table border=5>"; /* } */
        $lvl++;
        foreach($arr as $key=>$val) {
            echo "<tr>";
            if(is_array($val)) {
                $varnames[$lvl]=$key;
                echo "<td>";
                for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
                echo "= array(";
                show_array($val,$lvl,$varnames);
                echo ")</td>";
            }
            else {
                $varnames[$lvl]=$key;
                echo "<td>";
                for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
                echo "= $val </td>";
            }
            echo "</tr>";
        }
        /*if(count($arr)>0) {*/echo "</table>";}
}

extract($_REQUEST);
require_once "med/conn_string.cfg.php";

function u8($text) {return iconv('CP1251','UTF-8',$text);}
function cp($text) {return iconv('UTF-8','CP1251',$text);}

$result_arr=array();
$result_work=array();
$result_serv=array();
$result_itog=array();


$_SESSION['reports']['start_date']=$rep_start_date;
$_SESSION['reports']['end_date']=$rep_end_date;

$filename = "lead-" . date("Ymd", strtotime($_SESSION['reports']['start_date']));
if ($_SESSION['reports']['start_date'] != $_SESSION['reports']['end_date'])
	$filename .= "-" . date("Ymd", strtotime($_SESSION['reports']['end_date']));

//выбранные фильтры
if($sel_source_auto[0]=='-1') $sel_source_auto_str='-1';
else $sel_source_auto_str="'".(implode("','",$sel_source_auto))."'";

if($sel_services[0]=='-1') $sel_services_str='-1';
else $sel_services_str="'".(implode("','",$sel_services))."'";

if($sel_serv_det[0]=='-1') $sel_serv_det_str='-1';
else $sel_serv_det_str="'".(implode("','",$sel_serv_det))."'";

$sel_source_type_str=$sel_source_type;

$services_text='';
if($sel_services_str=='-1') $services_text="Все услуги. ";
else {
	$i=0; foreach($sel_services as $val) {$i++;
		if($i>1) $services_text.=", ";
        $result_itog[$val] = array();
		$services_text.=$_SESSION['reports']['services'][$val];
	}
	if($i>0) $services_text.=".";
}

$serv_det_text='';
if($sel_serv_det_str=='-1') $serv_det_text="Все уточнения. ";
else {
	$i=0; foreach($sel_serv_det as $val) {$i++;
		if($i>1) $serv_det_text.=", ";
        $serv_det_text.=$_SESSION['reports']['serv_det'][$val];
	}
	if($i>0) $serv_det_text.=".";
}

$period_text='';
if ($_SESSION['reports']['start_date'] != $_SESSION['reports']['end_date']) 
	$period_text .= 'За период c ' . $_SESSION['reports']['start_date'] . " по " . $_SESSION['reports']['end_date'];
else $period_text .='За ' . $_SESSION['reports']['start_date'];
$period_text .= ". На дату ".date('d.m.Y');

$result_sources=array();
//$result_payment_sum=array();

/*
echo $sel_source_type_str."<br>";
echo $sel_source_auto_str."<br>";
echo $sel_services_str."<br>";
echo $services_text."<br>";
echo $services_text." ".$period_text;
exit();
*/

//часть запроса с ограничением прав доступа к заявкам
if($_SESSION['user_role']==1) $sql_access_part='';
else $sql_access_part=" and (cb.source_auto_id, cb.source_man_id, cb.source_type_id, cb.service_id) 
in (select 
decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id),
decode(ad.source_man_id,-1,cb.source_man_id,ad.source_man_id),
decode(ad.source_type_id,-1,cb.source_type_id,ad.source_type_id),
decode(ad.service_id,-1,cb.service_id,ad.service_id)

from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null
and ad.departament_id=d.id 
)";

// Всего, зачот и брак
$sql = "select --cb.SOURCE_AUTO_ID, --cb.id, cb.status_id,
sup.SUP_NAME, to_char (cb.DATE_CALL,'dd.mm.yyyy') CALL_DATE, sa.NAME SOURCE_AUTO_NAME, cb.SOURCE_TYPE_ID, --st.NAME SOURCE_TYPE_NAME, cb.service_id,
count(*) COUNT_ALL, --Поступило обращений,
count(case when cb.status_id in ('3','6','10') and nvl(cb.call_double,0)!=2 and cb.interstate is null then 1 else NULL end) ORDER_COUNT, --Принято обращений
count(case when cb.status_id not in ('1','2','4','3','6','10') or nvl(cb.call_double,0)=2 or cb.interstate is not null then 1 else NULL end) BREAK_COUNT -- Брак
from CALL_BASE cb
left join source_auto sa on sa.id=cb.source_auto_id
left join suppliers sup on sup.id=sa.supplier_id
--left join services s on s.id=cb.service_id
--left join source_type st on st.id=cb.source_type_id
where cb.call_theme_id=1 and 
cb.DATE_CALL between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
 and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1      
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and cb.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and cb.service_id in (".$sel_services_str.")":"")."
".($sel_serv_det_str<>"-1"?"and cb.service_det_id in (".$sel_serv_det_str.")":"")."
".($sel_source_type_str<>"-1"?"and cb.source_type_id in (".$sel_source_type_str.")":"")."
group by sup.SUP_NAME, sa.NAME, cb.SOURCE_TYPE_ID, to_char (cb.DATE_CALL,'dd.mm.yyyy')
order by sup.SUP_NAME, sa.NAME, cb.SOURCE_TYPE_ID, to_date(CALL_DATE)";
//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
	//строки с нулевыми значениями не в счет
	if (OCIResult($q,"COUNT_ALL") > 0) {
        $sup_name = OCIResult($q,"SUP_NAME");
        $sa_name = OCIResult($q,"SOURCE_AUTO_NAME");
        $sa_type = OCIResult($q,"SOURCE_TYPE_ID");
        $sa_date = OCIResult($q,"CALL_DATE");
		$result_sources[$sup_name][$sa_name."_".$sa_type] = $sa_name; //массив для сортировки

		$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['SUP_NAME']=$sup_name;
		$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['SOURCE_AUTO_NAME']=$sa_name;
		//$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");
		//$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");

        $result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['COUNT_ALL']=OCIResult($q,"COUNT_ALL");
		$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['ORDER_COUNT']=OCIResult($q,"ORDER_COUNT");
		$result_arr[$sup_name][$sa_name."_".$sa_type][$sa_date]['BREAK_COUNT']=OCIResult($q,"BREAK_COUNT");
	}
}
//echo "<textarea>".show_array($result_sources)."</textarea>";
//echo "<textarea>".show_array($result_arr)."</textarea>";
//exit();

// Итого по поставщикам: всего, зачот и брак
$sql = "select sup.SUP_NAME, sa.NAME SOURCE_AUTO_NAME, cb.SOURCE_TYPE_ID, count(*) COUNT_ALL, --Поступило обращений,
count(case when cb.status_id in ('3','6','10') and nvl(cb.call_double,0)!=2 and cb.interstate is null then 1 else NULL end) ORDER_COUNT, --Принято обращений
count(case when cb.status_id not in ('1','2','4','3','6','10') or nvl(cb.call_double,0)=2 or cb.interstate is not null then 1 else NULL end) BREAK_COUNT -- Брак
from CALL_BASE cb
left join source_auto sa on sa.id=cb.source_auto_id
left join suppliers sup on sup.id=sa.supplier_id
where cb.call_theme_id=1 and 
cb.DATE_CALL between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
 and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1      
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and cb.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and cb.service_id in (".$sel_services_str.")":"")."
".($sel_serv_det_str<>"-1"?"and cb.service_det_id in (".$sel_serv_det_str.")":"")."
".($sel_source_type_str<>"-1"?"and cb.source_type_id in (".$sel_source_type_str.")":"")."
group by sup.SUP_NAME, sa.NAME, cb.SOURCE_TYPE_ID
order by sup.SUP_NAME, sa.NAME, cb.SOURCE_TYPE_ID";
//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    //строки с нулевыми значениями не в счет
    if (OCIResult($q,"COUNT_ALL") > 0) {
        $sup_name = OCIResult($q,"SUP_NAME");
        $sa_name = OCIResult($q,"SOURCE_AUTO_NAME");
        $sa_type = OCIResult($q,"SOURCE_TYPE_ID");

        $result_arr[$sup_name][$sa_name."_".$sa_type]['Итого']['SUP_NAME']=$sup_name;
        $result_arr[$sup_name][$sa_name."_".$sa_type]['Итого']['SOURCE_AUTO_NAME']=$sa_name;
        $result_arr[$sup_name][$sa_name."_".$sa_type]['Итого']['COUNT_ALL']=OCIResult($q,"COUNT_ALL");
        $result_arr[$sup_name][$sa_name."_".$sa_type]['Итого']['ORDER_COUNT']=OCIResult($q,"ORDER_COUNT");
        $result_arr[$sup_name][$sa_name."_".$sa_type]['Итого']['BREAK_COUNT']=OCIResult($q,"BREAK_COUNT");
    }
}
//echo "<textarea>".show_array($result_arr)."</textarea>"; exit();

// еще в работе
$sql = "select sup.sup_name, to_char (cb.DATE_CALL,'dd.mm.yyyy') CALL_DATE,
count(case when cb.status_id in ('1','2','4') then 1 else NULL end) IN_WORK --в работе
from CALL_BASE cb
left join source_auto sa on sa.id=cb.source_auto_id
left join suppliers sup on sup.id=sa.supplier_id
where cb.call_theme_id=1 and
cb.DATE_CALL between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
 and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1     
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and cb.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and cb.service_id in (".$sel_services_str.")":"")."
".($sel_serv_det_str<>"-1"?"and cb.service_det_id in (".$sel_serv_det_str.")":"")."
".($sel_source_type_str<>"-1"?"and cb.source_type_id in (".$sel_source_type_str.")":"")."
group by to_char (cb.DATE_CALL,'dd.mm.yyyy'), sup.sup_name
order by to_date(CALL_DATE), sup.sup_name";
//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    $sup_name = OCIResult($q,"SUP_NAME");
    $sa_date = OCIResult($q,"CALL_DATE");
    $result_work[$sup_name][$sa_date]['IN_WORK']=OCIResult($q,"IN_WORK");
}
//echo "<textarea>".show_array($result_work)."</textarea>"; exit();

// По услугам
$sql = "select sup.sup_name, cb.service_id, to_char (cb.DATE_CALL,'dd.mm.yyyy') CALL_DATE,
count(case when cb.status_id in ('3','6','10') and nvl(cb.call_double,0)!=2 and cb.interstate is null then 1 else NULL end) BY_SERV --Принято обращений по услугам
from CALL_BASE cb
left join source_auto sa on sa.id=cb.source_auto_id
left join suppliers sup on sup.id=sa.supplier_id
where cb.call_theme_id=1 and
cb.DATE_CALL between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
 and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1     
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and cb.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and cb.service_id in (".$sel_services_str.")":"")."
".($sel_serv_det_str<>"-1"?"and cb.service_det_id in (".$sel_serv_det_str.")":"")."
".($sel_source_type_str<>"-1"?"and cb.source_type_id in (".$sel_source_type_str.")":"")."
group by to_char (cb.DATE_CALL,'dd.mm.yyyy'), sup.sup_name, cb.service_id
order by to_date(CALL_DATE), sup.sup_name, cb.service_id";
//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    $serv_id = OCIResult($q,"SERVICE_ID");
    $sup_name = OCIResult($q,"SUP_NAME");
    $sa_date = OCIResult($q,"CALL_DATE");
    $result_serv[$sup_name][$serv_id][$sa_date]['BY_SERV']=OCIResult($q,"BY_SERV");
}
//echo "<textarea>".show_array($result_serv)."</textarea>"; exit();


// Суммарно: всего, зачот и брак
$sql = "select cb.service_id, to_char (cb.DATE_CALL,'dd.mm.yyyy') CALL_DATE, count(*) COUNT_ALL, --Поступило обращений,
count(case when cb.status_id in ('3','6','10') and nvl(cb.call_double,0)!=2 and cb.interstate is null then 1 else NULL end) ORDER_COUNT, --Принято обращений
count(case when cb.status_id not in ('1','2','4','3','6','10') or nvl(cb.call_double,0)=2 or cb.interstate is not null then 1 else NULL end) BREAK_COUNT -- Брак
from CALL_BASE cb
where cb.call_theme_id=1 and 
cb.DATE_CALL between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
 and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1      
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and cb.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and cb.service_id in (".$sel_services_str.")":"")."
".($sel_serv_det_str<>"-1"?"and cb.service_det_id in (".$sel_serv_det_str.")":"")."
".($sel_source_type_str<>"-1"?"and cb.source_type_id in (".$sel_source_type_str.")":"")."
group by to_char (cb.DATE_CALL,'dd.mm.yyyy'), cb.service_id
order by to_date(CALL_DATE), cb.service_id";
//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    $serv_id = OCIResult($q,"SERVICE_ID");
    $sa_date = OCIResult($q,"CALL_DATE");
    $result_itog[$serv_id][$sa_date]['COUNT_ALL']=OCIResult($q,"COUNT_ALL");
    $result_itog[$serv_id][$sa_date]['ORDER_COUNT']=OCIResult($q,"ORDER_COUNT");
    $result_itog[$serv_id][$sa_date]['BREAK_COUNT']=OCIResult($q,"BREAK_COUNT");
}
//echo "<textarea>".show_array($result_itog)."</textarea>"; exit();

///////////////////////////////////////////////////////////////////////////////
//РАБОТА С EXCEL

//ИНИЦИИРУЕМ КНИГУ
/** Include PHPExcel */
require_once 'PHPExcel.php';

$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize ' => '256MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//Свойства документа
$objPHPExcel->getProperties()
			->setTitle(u8("Отчет по Lead"))
			->setSubject(u8("Отчет по Lead"))
			->setDescription(u8("Отчет по Lead"))
			->setKeywords(u8("Отчет по Lead"))
			->setCategory(u8("Отчет по Lead"));

//ЛИСТ 1
include('lead_xlsx_sheet.php');

//////////////////////////////////////////////////
//ОТДАЕМ КНИГУ НА СКАЧИВАНИЕ

$objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;