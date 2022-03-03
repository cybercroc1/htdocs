<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_start();
extract($_REQUEST);

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>'y') {
echo "<font color=red><b>У Вас не прав для просмотра данной страницы или Вы не прошли авторизацию</b></font>";
exit();
}

if (!isset($start_date)) $start_date=date('d.m.Y',strtotime('-8 day'));
if (!isset($end_date)) $end_date=date('d.m.Y',strtotime('-1 day'));

include("../../sc_conf/sc_conn_string");

if(isset($download)) {
header("Content-type: application/csv");
header("Content-Disposition: attachment; filename=\"reg-".$start_date."-".$end_date.".csv\""); 
	echo "DATE_REG_CODE;CODE;NOMINAL;NAME1;NAME2;NAME3;DATE_AGE;MOBILE_PHONE;ADRES;DATE_PAY;STATUS_PAY".chr(13).chr(10);
	
	$q=OCIParse($c,"select to_char(DATE_REG_CODE,'DD.MM.YYYY') DATE_REG_CODE, CODE, NOMINAL, NAME1, NAME2, NAME3, to_char(DATE_AGE,'DD.MM.YYYY') DATE_AGE, MOBILE_PHONE, ADRES, to_char(DATE_PAY,'DD.MM.YYYY') DATE_PAY, STATUS_PAY
	from rolton_coupon_base 
	where DATE_REG_CODE between to_date('".$start_date."','DD.MM.YYYY') and to_date('".$end_date."','DD.MM.YYYY')+1
	order by DATE_REG_CODE");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo OCIResult($q,"DATE_REG_CODE").";".OCIResult($q,"CODE").";".OCIResult($q,"NOMINAL").";".OCIResult($q,"NAME1").";".OCIResult($q,"NAME2").";".OCIResult($q,"NAME3").";".OCIResult($q,"DATE_AGE").";".OCIResult($q,"MOBILE_PHONE").";".OCIResult($q,"ADRES").";".OCIResult($q,"DATE_PAY").";".OCIResult($q,"STATUS_PAY").chr(13).chr(10);
	}
exit();
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Выгрузка базы для проведения платежей</title>
</head>
<body>
<form method="post">
<?php

echo "<font size=3><b>Выгрузка базы для проведения платежей</b></font><br><br>";

echo "Коды, зарегистрированные с <td bgcolor=white><INPUT TYPE=TEXT NAME=start_date SIZE=10 value='".$start_date."' onClick='if(self.gfPop)gfPop.fPopCalendar(document.all.start_date);return false;' HIDEFOCUS> 
по <td bgcolor=white><INPUT TYPE=TEXT NAME=end_date SIZE=10 value='".$end_date."' onClick='if(self.gfPop)gfPop.fPopCalendar(document.all.end_date);return false;' HIDEFOCUS> 
(включительно)<br>
<input type=submit name=show value='Показать'><input type=submit name=download value='Выгрузить в файл'><hr>";

if(isset($show)) {
	echo "<table bgcolor=black cellspacing=1 cellpadding=1>";
	echo "<tr>
	<td bgcolor=white><b>DATE_REG_CODE</b></td>
	<td bgcolor=white><b>CODE</b></td>
	<td bgcolor=white><b>NOMINAL</b></td>
	<td bgcolor=white><b>NAME1</b></td>
	<td bgcolor=white><b>NAME2</b></td>
	<td bgcolor=white><b>NAME3</b></td>
	<td bgcolor=white><b>DATE_AGE</b></td>
	<td bgcolor=white><b>MOBILE_PHONE</b></td>
	<td bgcolor=white><b>ADRES</b></td>
	<td bgcolor=white><b>DATE_PAY</b></td>
	<td bgcolor=white><b>STATUS_PAY</b></td>
	</tr>";
	
	$q=OCIParse($c,"select to_char(DATE_REG_CODE,'DD.MM.YYYY') DATE_REG_CODE, CODE, NOMINAL, NAME1, NAME2, NAME3, to_char(DATE_AGE,'DD.MM.YYYY') DATE_AGE, MOBILE_PHONE, ADRES, to_char(DATE_PAY,'DD.MM.YYYY') DATE_PAY, STATUS_PAY
	from rolton_coupon_base 
	where DATE_REG_CODE between to_date('".$start_date."','DD.MM.YYYY') and to_date('".$end_date."','DD.MM.YYYY')+1
	order by DATE_REG_CODE");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>
		<td bgcolor=white><b>".OCIResult($q,"DATE_REG_CODE")."</b></td>
		<td bgcolor=white><b>".OCIResult($q,"CODE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NOMINAL")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME1")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME2")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"NAME3")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"DATE_AGE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"MOBILE_PHONE")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"ADRES")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"DATE_PAY")."</b></b></td>
		<td bgcolor=white><b>".OCIResult($q,"STATUS_PAY")."</b></b></td>
		</tr>";
	}
}

?>
</form>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
