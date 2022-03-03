<?php 
require_once "auth.php";
$_SESSION['last_url']='rep_sms_main.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
<?php } ?>
<link rel="stylesheet" href="css/jquery.datetimepicker.css">
</head>
<body class=rep-form>
<script src='js/jquery-3.5.1.min.js'></script>
<script src='js/jquery.datetimepicker.full.min.js'></script>
<script>$.datetimepicker.setLocale('ru');</script>
<script src='js/report.js'></script>
<script src='js/form2div.js'></script>
<script>
//эти функции можно переопределить на странице
form2div.allready=function(frm_id,div_id,clicked_button) {
	alert('Запрос уже выполняется!');	
}
form2div.loading=function(frm_id,div_id,clicked_button) {
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
<?php

if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['view_sms_log']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
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
//
if(strlen($_SESSION['start_rep_date'])==10) $_SESSION['start_rep_date'].=" 00:00";
if(strlen($_SESSION['end_rep_date'])==10) $_SESSION['end_rep_date'].=" 23:59";
//

echo "<form id=frm method=post action=report_sms.php>";
echo "<div class=rep_head>";
echo "<nobr><font size=4>СМС-лог - \"".$_SESSION['project']['name']."\"</font></nobr><br>";
$min_date='01.01.2015';
echo "<nobr>";
echo " c:<INPUT TYPE=TEXT autocomplete=off id=start_rep_date NAME=start_rep_date value='".$_SESSION['start_rep_date']."' SIZE=16></input>";
?><script>$('#start_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php
	

echo " по:<INPUT TYPE=TEXT autocomplete=off id=end_rep_date NAME=end_rep_date value='".$_SESSION['end_rep_date']."' SIZE=16></input>";
?><script>$('#end_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php
//
echo "</nobr>";
echo "<br>";
echo "<INPUT class=menubtn type=button name=html value=\"Показать отчет\" onclick=form2div('frm','rep_div',this,'report_sms.php')>";
echo "<INPUT class=menubtn type=submit name=xlsx value=\"Скачать XLSX\">";
echo "<INPUT class=menubtn type=submit name=csv value=\"Скачать CSV\">";
echo "</div>";
echo "<div id=rep_div class=rep_div></div>";
echo "</form>";
echo '</body>
</html>';
?>