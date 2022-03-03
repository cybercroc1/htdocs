<?php
require_once 'med/check_auth.php';
//�������� ���� ������� � ������� ������
$report_id=6;
if(!isset($_SESSION['access']['report'][$report_id])) {
	echo "<font color=red>������: ������ ��������</font>";
	exit();
}

extract($_REQUEST);
require_once "med/conn_string.cfg.php";

function u8($text) {return iconv('CP1251','UTF-8',$text);}
function cp($text) {return iconv('UTF-8','CP1251',$text);}

$result_arr=array();

$_SESSION['reports']['start_date']=$rep_start_date;
$_SESSION['reports']['end_date']=$rep_end_date;

$filename = "effect-" . date("Ymd", strtotime($_SESSION['reports']['start_date']));
if ($_SESSION['reports']['start_date'] != $_SESSION['reports']['end_date']) $filename .= "-" . date("Ymd", strtotime($_SESSION['reports']['end_date']));

//��������� �������
if($sel_source_auto[0]=='-1') $sel_source_auto_str='-1';
else $sel_source_auto_str="'".(implode("','",$sel_source_auto))."'";

if($sel_services[0]=='-1') $sel_services_str='-1';
else $sel_services_str="'".(implode("','",$sel_services))."'";	

$sel_source_type_str=$sel_source_type;

$services_text='';
if($sel_services_str=='-1') $services_text="��� ������. ";
else {
	$i=0; foreach($sel_services as $val) {$i++;
		if($i>1) $services_text.=", ";
		$services_text.=$_SESSION['reports']['services'][$val];
	}
	if($i>0) $services_text.=".";
}
$period_text='';
if ($_SESSION['reports']['start_date'] != $_SESSION['reports']['end_date']) 
	$period_text .= '�� ������ c ' . $_SESSION['reports']['start_date'] . " �� " . $_SESSION['reports']['end_date'];
else 
	$period_text .='�� ' . $_SESSION['reports']['start_date']; 
$period_text .= ". �� ���� ".date('d.m.Y');

$result_sources=array();
$result_payment_sum=array();

/*
echo $sel_source_type_str."<br>";
echo $sel_source_auto_str."<br>";
echo $sel_services_str."<br>";
echo $services_text."<br>";
echo $services_text." ".$period_text;
exit();
*/

//����� ������� � ������������ ���� ������� � �������
if($_SESSION['user_role']==1) $sql_access_part='';
else $sql_access_part="and (
b.source_auto_id,
b.source_man_id,
b.source_type_id,
b.service_id
) 
in 
(
select 
decode(ad.source_auto_id,-1,b.source_auto_id,ad.source_auto_id),
decode(ad.source_man_id,-1,b.source_man_id,ad.source_man_id),
decode(ad.source_type_id,-1,b.source_type_id,ad.source_type_id),
decode(ad.service_id,-1,b.service_id,ad.service_id)

from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null
and ad.departament_id=d.id 
)";

//��������� ��� ����������� ����� �����
/*	
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['SOURCE_AUTO_NAME']=OCIResult($q,"SOURCE_AUTO_NAME");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['SERVICE_ID']=OCIResult($q,"SERVICE_ID");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['SERVICE_NAME']=OCIResult($q,"SERVICE_NAME");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");	
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");
	
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['ORDER_COUNT']=OCIResult($q,"ORDER_COUNT");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['VISIT_OF_ORDERS']=OCIResult($q,"VISIT_OF_ORDERS");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['PAYED_OF_ORDERS']=OCIResult($q,"PAYED_OF_ORDERS");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['PAYMENT_SUM_OF_ORDERS']=OCIResult($q,"PAYMENT_SUM_OF_ORDERS");
	
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['VISIT_BY_PER']=OCIResult($q,"VISIT_BY_PER");
	
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['PAYED_BY_PER']=OCIResult($q,"PAYED_BY_PER");
	$result_arr[OCIResult($q,"SOURCE_AUTO_ID")]['PAYMENT_SUM_BY_PER']=OCIResult($q,"PAYMENT_SUM_BY_PER");	
*/
//
	
//�� �� ��������
$sql="select 
--B.SOURCE_AUTO_ID,
SA.NAME SOURCE_AUTO_NAME,
B.SOURCE_TYPE_ID,
ST.NAME SOURCE_TYPE_NAME,
count(*) COUNT_ALL, --��������� ���������,
count(case when b.status_id in ('10','3','6') and nvl(b.call_double,0)<2 and b.interstate is null then 1 else NULL end) ORDER_COUNT, --������� ���������
sum(v.cnt_visit) VISIT_OF_ORDERS, --������ �� ��������
count(p.base_id) PAYED_OF_ORDERS, --�������� �� ��������
sum(p.rub) PAYMENT_SUM_OF_ORDERS, --����� ������� �� ��������
sum(ph.plan_sum) PLAN_SUM_OF_ORDERS, --����� ������������ ������ �� ��������
sum(ph.plan_sum_2500_plus) PLAN_SUM_OF_ORDERS_2500_PLUS --����� ������������ ������ �� �������� 2500+
 
from CALL_BASE b
left join (select base_id,count(*) cnt_payment ,sum(rub) rub from PAYMENT_HIST group by base_id) p on p.base_id=b.id
left join (select base_id,count(*) cnt_visit from VISIT_HIST group by base_id) v on v.base_id=b.id
left join (select base_id, sum(rub) plan_sum, (case when sum(rub)>=2500 then sum(rub) else null end) plan_sum_2500_plus from PLAN_HIST group by base_id) ph on ph.base_id=b.id

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where b.date_call between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
					  and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
and b.call_theme_id=1
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and b.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and b.service_id in (".$sel_services_str.")":"")."
".($sel_source_type_str<>"-1"?"and b.source_type_id in (".$sel_source_type_str.")":"")."
group by 
--B.SOURCE_AUTO_ID,
SA.NAME,
B.SOURCE_TYPE_ID,
ST.NAME
order by
SA.NAME";

//echo "<textarea>".$sql."</textarea>"; exit();

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
	//������ � �������� ���������� �� � ����
	if(OCIResult($q,"COUNT_ALL")>0 or OCIResult($q,"ORDER_COUNT")>0 or OCIResult($q,"VISIT_OF_ORDERS")>0 or OCIResult($q,"PAYED_OF_ORDERS")>0 or OCIResult($q,"PAYMENT_SUM_OF_ORDERS")>0) {
		$result_sources[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"SOURCE_AUTO_NAME"); //������ ��� ����������

		$result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"PAYMENT_SUM_OF_ORDERS"); //������ ��� ����������
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['COUNT_ALL']=OCIResult($q,"COUNT_ALL");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_AUTO_NAME']=OCIResult($q,"SOURCE_AUTO_NAME");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['ORDER_COUNT']=OCIResult($q,"ORDER_COUNT");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['VISIT_OF_ORDERS']=OCIResult($q,"VISIT_OF_ORDERS");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PAYED_OF_ORDERS']=OCIResult($q,"PAYED_OF_ORDERS");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PAYMENT_SUM_OF_ORDERS']=OCIResult($q,"PAYMENT_SUM_OF_ORDERS");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PLAN_SUM_OF_ORDERS']=OCIResult($q,"PLAN_SUM_OF_ORDERS");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PLAN_SUM_OF_ORDERS_2500_PLUS']=OCIResult($q,"PLAN_SUM_OF_ORDERS_2500_PLUS");
	}
}

//��������� �� ������
$sql="select 
--B.SOURCE_AUTO_ID,
SA.NAME SOURCE_AUTO_NAME,
B.SOURCE_TYPE_ID,
ST.NAME SOURCE_TYPE_NAME,

count(*) VISIT_BY_PER --������ �� ������

from  visit_hist vh, CALL_BASE b

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where vh.date_visit between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
					  and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
and b.id=vh.base_id
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and b.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and b.service_id in (".$sel_services_str.")":"")."
".($sel_source_type_str<>"-1"?"and b.source_type_id in (".$sel_source_type_str.")":"")."
group by 
--B.SOURCE_AUTO_ID,
SA.NAME,
B.SOURCE_TYPE_ID,
ST.NAME
order by
SA.NAME";


$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
	//������ � �������� ���������� �� � ����
	if(OCIResult($q,"VISIT_BY_PER")>0) {
		$result_sources[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"SOURCE_AUTO_NAME"); //������ ��� ����������
		if(!isset($result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]))
				$result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=0; //������ ��� ����������
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_AUTO_NAME']=OCIResult($q,"SOURCE_AUTO_NAME");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['VISIT_BY_PER']=OCIResult($q,"VISIT_BY_PER");	
	}
}

//�������� �� ������
$sql="select
--B.SOURCE_AUTO_ID,
SA.NAME SOURCE_AUTO_NAME,
B.SOURCE_TYPE_ID,
ST.NAME SOURCE_TYPE_NAME,

count(pp.base_id) PAYED_BY_PER, --����� ���������� �� ������ 
sum(pp.rub) PAYMENT_SUM_BY_PER --����� ������� �� ������

from (select base_id,count(*) cnt_payment ,sum(rub) rub
from PAYMENT_HIST p 
where p.date_payment between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
					     and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
group by base_id) pp, 
CALL_BASE b

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where b.id=pp.base_id 
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and b.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and b.service_id in (".$sel_services_str.")":"")."
".($sel_source_type_str<>"-1"?"and b.source_type_id in (".$sel_source_type_str.")":"")."
group by 
--B.SOURCE_AUTO_ID,
SA.NAME,
B.SOURCE_TYPE_ID,
ST.NAME
order by
SA.NAME";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
	//������ � �������� ���������� �� � ����
	if(OCIResult($q,"PAYED_BY_PER")>0 or OCIResult($q,"PAYMENT_SUM_BY_PER")>0) {	
		$result_sources[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"SOURCE_AUTO_NAME"); //������ ��� ����������
		
		if(!isset($result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]))
				$result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=0; //������ ��� ����������
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_AUTO_NAME']=OCIResult($q,"SOURCE_AUTO_NAME");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");
	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PAYED_BY_PER']=OCIResult($q,"PAYED_BY_PER");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PAYMENT_SUM_BY_PER']=OCIResult($q,"PAYMENT_SUM_BY_PER");	
	}
}

//����� �� ������
$sql="select
--B.SOURCE_AUTO_ID,
SA.NAME SOURCE_AUTO_NAME,
B.SOURCE_TYPE_ID,
ST.NAME SOURCE_TYPE_NAME,

sum(phh.plan_sum) PLAN_SUM_BY_PER, --����� ������ �� ������
sum(phh.plan_sum_2500_plus) PLAN_SUM_BY_PER_2500_PLUS --����� ������ �� ������ 2500+

from (select base_id, sum(rub) plan_sum, (case when sum(rub)>=2500 then sum(rub) else null end) plan_sum_2500_plus
from PLAN_HIST ph 
where ph.plan_date between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
					     and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
group by base_id) phh, 
CALL_BASE b

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where b.id=phh.base_id 
".$sql_access_part." 
".($sel_source_auto_str<>"-1"?"and b.source_auto_id in (".$sel_source_auto_str.")":"")."
".($sel_services_str<>"-1"?"and b.service_id in (".$sel_services_str.")":"")."
".($sel_source_type_str<>"-1"?"and b.source_type_id in (".$sel_source_type_str.")":"")."
group by 
--B.SOURCE_AUTO_ID,
SA.NAME,
B.SOURCE_TYPE_ID,
ST.NAME
order by
SA.NAME";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
	//������ � �������� ���������� �� � ����
	if(OCIResult($q,"PLAN_SUM_BY_PER")>0 or OCIResult($q,"PLAN_SUM_BY_PER_2500_PLUS")>0) {	
		$result_sources[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"SOURCE_AUTO_NAME"); //������ ��� ����������
		
		if(!isset($result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]))
				$result_payment_sum[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]=0; //������ ��� ����������
		
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_AUTO_NAME']=OCIResult($q,"SOURCE_AUTO_NAME");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_ID']=OCIResult($q,"SOURCE_TYPE_ID");	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['SOURCE_TYPE_NAME']=OCIResult($q,"SOURCE_TYPE_NAME");
	
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PLAN_SUM_BY_PER']=OCIResult($q,"PLAN_SUM_BY_PER");
		$result_arr[OCIResult($q,"SOURCE_AUTO_NAME")."_".OCIResult($q,"SOURCE_TYPE_ID")]['PLAN_SUM_BY_PER_2500_PLUS']=OCIResult($q,"PLAN_SUM_BY_PER_2500_PLUS");	
	}
}

///////////////////////////////////////////////////////////////////////////////
//������ � EXCELL

//���������� �����
/** Include PHPExcel */
require_once 'PHPExcel.php';

$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize ' => '256MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//�������� ���������
$objPHPExcel->getProperties()
			->setTitle(u8("����� � ������������� �������"))
			->setSubject(u8("����� � ������������� �������"))
			->setDescription(u8("����� � ������������� �������"))
			->setKeywords(u8("����� � ������������� �������"))
			->setCategory(u8("����� � ������������� �������"));


//���� 1
include('effect_aydin_xlsx_sheet1.php');

//���� 2
include('effect_aydin_xlsx_sheet2.php');

//���� 3
include('effect_aydin_xlsx_sheet3.php');

//////////////////////////////////////////////////
//������ ����� �� ����������

$objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client�s web browser (Excel5)
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
?>
