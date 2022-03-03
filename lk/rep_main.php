<?php 
require_once "auth.php";
if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
require_once "lk/lk_ora_conn_string.php";
$_SESSION['last_url']='rep_main.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
<?php } ?>
</head>
<body class=rep-form>
<script src='js/jquery-3.5.1.min.js'></script>
<link rel="stylesheet" href="css/jquery.datetimepicker.css">
<script src='js/jquery.datetimepicker.full.min.js'></script>
<script>$.datetimepicker.setLocale('ru');</script>
<script src='js/report.js'></script>
<script src='js/form2div.js'></script>
<script>//эти функции можно переопределить на странице
form2div.allready=function(frm_id,div_id,clicked_button) {
	alert('Запрос уже выполняется!');	
}

form2div.loading=function(frm_id,div_id,clicked_button) {
	if(div_id=='rep_div') {
		document.getElementById('count_head_div').innerHTML='';
		document.getElementById('count_rep_div').innerHTML=''; 
	}
	if(div_id=='count_head_div') {
		document.getElementById('rep_div').innerHTML=''; 
	}
	var div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;"><img src="img/progress.gif"></img><br><a class="abort-href" href=\'javascript:form2div_abort("'+div_id+'")\'>прервать загрузку</a></div>';	
}
form2div.aborted=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;">загрузка прервана</div>';	
}
form2div.done=function(frm_id,div_id,clicked_button,response) {
	var div=document.getElementById(div_id);
	div.innerHTML=div.innerHTML=response;
}	
</script>
<script>
function get_phones () {
	form_id=document.getElementById('sel_form').value;
	phone=document.getElementById('sel_phone').value;
	$.post('rep_main_get_phones.php',
	{
		'get_phones':'y',
		'sel_form':form_id,
		'sel_phone':phone
	},
	function(data){document.getElementById('sel_phone').innerHTML=data;}
	//function(data){document.getElementById('ttt').innerHTML=data;}
	) 
	.fail(function() { alert("Ошибка AJAX"); });
}
</script>
<?php
//Формирование дат
if (!isset($_SESSION['start_rep_date'])) {
	$start_rep_date = strtotime("now");
	$_SESSION['start_rep_date'] = date("d.m.Y",$start_rep_date);
}
	
if (!isset($_SESSION['end_rep_date'])) {
	$end_rep_date = strtotime("now"); //текущая дата
	$_SESSION['end_rep_date'] = date("d.m.Y",$end_rep_date);
}
//доступ к отчетам
$forms=array();
$cdns=array();
if($_SESSION['admin']=='1' or $_SESSION['allow_view_all_reports']==1) {
	//доступ к формам
	$q=OCIParse($c,"select f.id,f.name from SC_FORMS f
	where f.deleted is null and f.id>0 and f.project_id='".$_SESSION['project']['id']."' 
	order by f.name");
	OCIExecute($q);
	 while(OCIFetch($q)) {
		$forms[OCIResult($q,"ID")]=OCIResult($q,"NAME");	
	}
}
else {
	//доступ к формам
	$q=OCIParse($c,"select f.id,f.name from SC_ACC_FORMS af
	left join sc_forms f on f.id=decode(af.form_id,0,f.id,af.form_id)
	where af.project_id='".$_SESSION['project']['id']."' and f.project_id='".$_SESSION['project']['id']."'
	and af.login_id='".$_SESSION['login_id']."'
	order by f.name");
	OCIExecute($q);
	 while(OCIFetch($q)) {
		$forms[OCIResult($q,"ID")]=OCIResult($q,"NAME");	
	}	
}
//
echo "<form id=frm method=post action=report_go.php>";
echo "<div class=rep_head>";
//if(count($forms)==0) {
//	echo "<nobr><font size=4> По проекту \"".$_SESSION['project']['name']."\" нет отчетов</font></nobr>";
//	exit();
//}
echo "<nobr><font size=4> Отчеты - \"".$_SESSION['project']['name']."\"</font></nobr>";
if ($_SESSION['rep_period']<>'') {
	$title="Отчеты предоставляются с ".$_SESSION['rep_period'];
	$and_rep_period=" and b.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
	$min_date=$_SESSION['rep_period'];
}
else {$and_rep_period=''; $min_date='01.01.2015'; $title='';}

if(strlen($_SESSION['start_rep_date'])==10) $_SESSION['start_rep_date'].=" 00:00";
if(strlen($_SESSION['end_rep_date'])==10) $_SESSION['end_rep_date'].=" 23:59";
echo "<br>";
echo "<nobr>";
echo "c:<INPUT TYPE=TEXT autocomplete=off id=start_rep_date NAME=start_rep_date value='".$_SESSION['start_rep_date']."' SIZE=16 title='".$title."'></input>";
?><script>$('#start_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php


echo "по:<INPUT TYPE=TEXT autocomplete=off id=end_rep_date NAME=end_rep_date value='".$_SESSION['end_rep_date']."' SIZE=16 ></input>";
?><script>$('#end_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php
echo "</nobr>";
echo "<br>";	
echo "<nobr>";
echo "Выберите тип отчета:";
echo "<select id=sel_form name=form_id onchange='get_phones()' class='sel_form'>";
echo "<option value=all>Все отчеты</option>";
foreach($forms as $id => $name) {echo "<option value='".$id."'>".$name."</option>";}	
//звонки без отчета
if($_SESSION['allow_noreport']==1) {echo "<option value=null>Нет отчета</option>";}
echo "</select>";
echo "</nobr>";
echo "<br>";
echo "<nobr>";
echo "Выберите номер доступа:";
echo "<select id=sel_phone name=cgpn class='sel_phone'>";
echo "</select>";
echo "</nobr>";
//
echo "<br>";
echo "<nobr>";
echo "<INPUT class=menubtn type=button name=html value=\"Показать отчет\" onclick=form2div('frm','rep_div',this,'report_go.php')>";
echo "<INPUT class=menubtn type=submit name=xls value=\"Скачать в Excel\">";
echo "<INPUT class=menubtn type=submit name=csv value=\"Скачать в CSV\">";
echo "<INPUT class=menubtn type=button name=count value=\"Количество\" onclick=\"form2div('frm','count_head_div',this,'report_count_main.php');\">";
echo "</nobr>";
echo "<script>get_phones();</script>";
echo "</div>";
echo "<div id=rep_div class=rep_div></div>";

echo "<div id=count_head_div class=rep_head></div>";
echo "<div id=count_rep_div class=rep_head></div>";	
echo "</form>";
?>
</body>
</html>