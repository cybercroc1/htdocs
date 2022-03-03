<?php
require_once 'med/check_auth.php';
//var_dump($_SESSION['reports']['ID']);
$report_id=16;
$form_action='oper_visit_xlsx.php';

//$_SESSION['reports']['ID'];
//Проверка прав доступа к данному отчету
if(!isset($_SESSION['access']['report'][$report_id])) {
	echo "<div style='color:red'>Ошибка: доступ запрещен</div></br>";
	exit();
}
?>
<!DOCTYPE html>
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
<?php
echo "<table style='height:100%; border:0 solid;'>";
echo "<tr>";

//Даты
echo "<td valign=top colspan=2>";
echo "<h3 style='margin: 0'>Даты:
с <input type='text' name='rep_start_date' id='rep_start_date' autocomplete='off' style='width: 5em;' value='".$_SESSION['reports']['start_date']."'/>
по <input type='text' name='rep_end_date' id='rep_end_date' autocomplete='off' style='width: 5em;' value='".$_SESSION['reports']['end_date']."'/>
</h3>";
echo "</td>";
echo "</tr>";
echo "<tr>";

/*
//Отделы
echo "<td valign=top>";
echo "<h3 style='margin: 0'>Департаменты:<br>";
echo "<select multiple id='sel_deps' name='sel_opers[]' style='height:380px; width:100%'>";
echo "<option selected value='-1'>Все Департаменты</option>";

$sql="SELECT distinct dep_id, d.name FROM user_dep_alloc uda 
left join departaments d on d.id = uda.dep_id
WHERE uda.DELETED is null and uda.user_id ='".$_SESSION['login_id_med']."'";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    echo "<option value='".OCIResult($q,"DEP_ID")."'>".OCIResult($q,"NAME")."</option>";
	//$_SESSION['reports']['services'][OCIResult($q,"ID")]=OCIResult($q,"NAME");
}
echo "</select></h3>";
echo "</td>";
*/
//Операторы
echo "<td valign=top>";
echo "<h3 style='margin: 0'>Операторы:<br>";
echo "<select multiple id='sel_opers' name='sel_opers[]' style='height:380px; width:100%'>";
echo "<option selected value='-1'>Все Операторы</option>";

$sql="SELECT usr.ID, usr.FIO
FROM USERS usr 
WHERE usr.deleted is null
and usr.role_id in (2,4) --операторы и супервайзеры
".($_SESSION['user_role']==1?"":"AND usr.id IN 
(select uda.user_id from USER_DEP_ALLOC uda where uda.dep_id in (SELECT distinct dep_id FROM user_dep_alloc WHERE DELETED is null and user_id ='".$_SESSION['login_id_med']."'))")."
order by usr.fio";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"FIO")."</option>";
	//$_SESSION['reports']['services'][OCIResult($q,"ID")]=OCIResult($q,"NAME");
}
echo "</select></h3>";
echo "</td>";

echo "</tr>";
?>
<tr><td>
<input type="submit" name="xlsx" value="В эксель" class="xlsx_button" onclick="frm.action='<?php echo $form_action; ?>';frm.submit();"/><br>
<input type="submit" name="csv" value="В CSV" class="xlsx_button" onclick="frm.action='<?php echo $form_action; ?>';frm.submit();"/><br>
</td></tr>
</table>

<script type="text/javascript">
    $('#rep_start_date').datetimepicker({
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