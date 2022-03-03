<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_start();
extract($_REQUEST);
if (!isset($start_date)) $start_date=date('d.m.Y',strtotime('-8 day'));
if (!isset($end_date)) $end_date=date('d.m.Y',strtotime('-1 day'));

include("../../sc_conf/sc_conn_string");

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Загрузка базы платежей</title>
</head>
<script>
function change() {
if (document.all.new_file.value=="") {
document.all.test.disabled=true;
}
else {
document.all.test.disabled=false;
}
}
</script>
<body>
<form method="post" enctype=\"multipart/form-data\">
<?php

echo "<font size=3><b>Загрузка базы после проведения платежей</b></font><br><br>";
echo "<input type=file name=new_file onchange=change()>
<input type=submit name=upload disabled value=Загрузить><hr>";

if(isset($upload)) {
	if($_FILES["new_file"]["size"] > 1024*3*1024) {echo ("</font color=red>Размер файла превышает 3 мегабайта!</font>");}
	else {
		if (!@is_uploaded_file($_FILES['new_file']["tmp_name"])) {echo "<font color=red>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</font>";}
		else {
			$fp=fopen($_FILES['new_file']["tmp_name"],"r");
			while($str=fgetcsv($fp,1024,";")) {
				
			}
		}
	}	
}

?>
</form>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>
