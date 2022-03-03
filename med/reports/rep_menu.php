<?php
require_once 'med/check_auth.php';
require_once '../base.php';
?>

<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="../billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
</head>
<body>
<?php if (in_array($_SESSION['login_id_med'],COST_EDIT)) { ?>
<div style="display: flex;">
    <form action="../admin/frames.php?page=1" method="post" target="_blank" title="Поставщики (баланс)" style="float: left;">
        <button name="Ist_Auto1" class="enter_button">Поставщики<br>(баланс)</button>
    </form>
    <form action="../admin/frames.php?page=2" method="post" target="_blank" title="Источники рекламы (редактирование)">
        <button name="Ist_Auto2" class="enter_button">Источники рекламы<br>(редактирование)</button>
    </form>
    <form action="../admin/admin_access_dep_new.php" method="post" target="_blank" title="Права доступа">
        <button name="Access_Dep" class="enter_button">Права доступа</button>
    </form>
</div>
<?php } ?>
<?php
require_once "med/conn_string.cfg.php";
extract($_REQUEST);
if(!isset($report_id)) $report_id='';
if(!isset($start_date)) $start_date=date('d.m.Y');
if(!isset($end_date)) $end_date=date('d.m.Y');
$_SESSION['reports']['ID']=$report_id;
$_SESSION['reports']['start_date']=$start_date;
$_SESSION['reports']['end_date']=$end_date;
//echo "user_id:".$_SESSION['login_id_med']." role_id:".$_SESSION['user_role'];

//Список отчетов

/*
$sql="select distinct r.id,r.SCRIPT_NAME,r.name from CALL_REPORTS r, call_reports_acc a
where a.report_id(+)=r.id and r.deleted is null
and (a.user_id='".$_SESSION['login_id_med']."' or ','||r.role_ids||',' like '%,'||'".$_SESSION['user_role']."'||',%') 
order by r.name";
$q=OCIParse($c,$sql);
*/
//echo "<textarea>$sql</textarea>";
//echo "<textarea>$report_id</textarea>";
//echo var_dump($_SESSION['access']['report']['6']['script_name']);
//форма нужна только для перехода на старый интерфейс
if('127.0.0.1' == $_SERVER['HTTP_HOST'] || 'localhost' == $_SERVER['HTTP_HOST'])
    echo "<form name='old_interface' action=".$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/med/call_view/med_export.php method='post' target='_parent' title='Отчеты'>";
else echo "<form name='old_interface' action=".$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/call_view/med_export.php method='post' target='_parent' title='Отчеты'>";
//echo "<form name='old_interface' action='../call_view/med_export.php?start_date=".$start_date."&end_date=".$end_date."' method='post' target='_parent' title='Отчеты'>";
//echo "<form name='old_interface' action='../call_view/med_export.php' method='post' target='_parent' title='Отчеты'>";
echo "<input type=hidden name=start_date value='".$_SESSION['reports']['start_date']."'>";
echo "<input type=hidden name=end_date value='".$_SESSION['reports']['end_date']."'>";

echo "<h1 style='margin-bottom: 0; margin-top: 0'><label for='ReportId'>&nbsp;Наименование отчета:&nbsp;</label>";
echo "<select id='ReportId' name='ReportId' title='Отчеты' style='font-size: 17px;' onchange='ReportChanged()'>";
echo "<option value='null'>Выберите отчет</option>";	

foreach($_SESSION['access']['report'] as $rep_id => $rep_arr) {
	echo "<option value='".$rep_arr['script_name']."'".($report_id==$rep_id?" selected":"").">".$rep_arr['name']."</option>";
}

/*
OCIExecute($q);
while(OCIFetch($q)) {
	echo "<option value='".OCIResult($q,"SCRIPT_NAME")."'".($report_id==OCIResult($q,"ID")?" selected":"").">".OCIResult($q,"NAME")."</option>";
	//список отчетов, доступных в данной сессии
	$_SESSION['access']['report'][OCIResult($q,"ID")]=OCIResult($q,"SCRIPT_NAME");
}
*/
echo "</select>";
echo "</form>";
/*
//права доступа к источникам и службам
$_SESSION['access']['source_auto']=array();
$_SESSION['access']['source_man']=array();
$_SESSION['access']['source_type']=array();
$_SESSION['access']['service']=array();
$q=OCIParse($c,"select ad.source_auto_id,ad.source_man_id,ad.source_type_id,ad.service_id 
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null
and ad.departament_id=d.id");
OCIExecute($q);
while(OCIFetch($q)) {
	if(-1==OCIResult($q,"SOURCE_AUTO_ID")) $_SESSION['access']['source_auto']=array(-1 => -1); 
	if(-1==OCIResult($q,"SOURCE_MAN_ID"))  $_SESSION['access']['source_man'] =array(-1 => -1); 
	if(-1==OCIResult($q,"SOURCE_TYPE_ID")) $_SESSION['access']['source_type']=array(-1 => -1); 
	if(-1==OCIResult($q,"SERVICE_ID")) 	   $_SESSION['access']['service']    =array(-1 => -1); 
		
	if(!isset($_SESSION['access']['source_auto'][-1])) $_SESSION['access']['source_auto'][OCIResult($q,"SOURCE_AUTO_ID")]=OCIResult($q,"SOURCE_AUTO_ID");
	if(!isset($_SESSION['access']['source_man'][-1]))  $_SESSION['access']['source_man'][OCIResult($q,"SOURCE_MAN_ID")]=OCIResult($q,"SOURCE_MAN_ID");
	if(!isset($_SESSION['access']['source_type'][-1])) $_SESSION['access']['source_type'][OCIResult($q,"SOURCE_TYPE_ID")]=OCIResult($q,"SOURCE_TYPE_ID");
	if(!isset($_SESSION['access']['service'][-1]))     $_SESSION['access']['service'][OCIResult($q,"SERVICE_ID")]=OCIResult($q,"SERVICE_ID");	
}

print_r ($_SESSION['access']);
*/
?>
<script>
ReportChanged();
function ReportChanged() {
	var sel=document.getElementById('ReportId')
	if(sel.value=='') {
		//alert('dsds');
		document.old_interface.submit(); //редирект на старый интерфейс
        /*var report_sel = document.getElementById('ReportId').value;
        var start_date = document.getElementById('rep_start_date').value
        var end_date = document.getElementById('rep_end_date').value
        if('new' === report_sel.substring(0,3)) {
            rep_id_arr=report_sel.split("|");
            this.location='../call_view/med_export?report_id='+rep_id_arr[1]+'&start_date='+start_date+'&end_date='+end_date;
        }
        //return;
		*/
	}
    else if(sel.value=='null') {
		parent.rep_filter.location='_blank_page.php';
		parent.rep_result.location='_blank_page.php';
		sel.style.backgroundColor='#FF733C';
	}
	else {
		parent.rep_filter.location=sel.value;
		parent.rep_result.location='_blank_page.php';
		sel.style.backgroundColor='';
	} 
}

</script>
</body>
</html>