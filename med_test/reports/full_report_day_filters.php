<?php
require_once 'med/check_auth.php';
$report_id=6;
//Проверка прав доступа к данному отчету
if(!isset($_SESSION['access']['report'][$report_id])) {
	echo "<font color=red>Ошибка: доступ запрещен</font>";
	exit();
}
?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="../billing.css">
	<script src="../js/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <script src="../js/jquery.datetimepicker.full.js"></script>
</head>	
<body style="margin-top: 0; margin-bottom: 0">
<?php
require_once "med/conn_string.cfg.php";
?>
<form name='frm' method=post target='rep_result'>
<table style='height:100%; border:0 solid;'><tr><td valign=top>
<?php
//Даты
echo "<h3 style='margin: 0'>Дата:
<input type='text' name='rep_date' id='rep_date' autocomplete='off' style='width: 5em;'/>
</h3>";

?>
</td></tr>
<tr><td>
<input type="submit" name="Export_xlsx" id="Export_xlsx" value="В эксель" class="xlsx_button" onclick="frm.action='full_report_day_xlsx.php';frm.submit();"/>
</td></tr>
</table>

<script type="text/javascript">
    $('#rep_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
    });
</script>
<script type="text/javascript">
    $('#rep_end_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
	});
</script>

</body>
</html>