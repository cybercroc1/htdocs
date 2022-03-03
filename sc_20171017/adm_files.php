<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //if (!isset($_SESSION['i'])) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_path");

if (isset($upload)) {
	if($_FILES["new_file"]["size"] > 1024*3*1024) {echo ("</font color=red>Размер файла превышает 3 мегабайта!</font>");}
	else {
	 	if (!@is_uploaded_file($_FILES['new_file']["tmp_name"])) {echo "<font color=red>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</font>";}
		else {
			if (!@move_uploaded_file($_FILES['new_file']["tmp_name"],$path_to_folders.$_SESSION['project_name'][$_SESSION['i']]."\\".$_FILES['new_file']["name"])) {echo "<font color=red>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</font>";}
		}
	}
}	

if (isset($del_file) and $del_file<>'') {

unlink($path_to_folders.$_SESSION['project_name'][$_SESSION['i']]."\\".$del_file);

}

echo "<form name=files_frm action=adm_files.php method=post enctype=\"multipart/form-data\">";
	echo "<font size=4>Файлы</font><hr>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white align=center><b>Нмя</b></td>
	<td bgcolor=white align=center><b>Размер</b></td>
	<td bgcolor=white align=center><b>Дата<br>изменения</b></td>
	<td bgcolor=white></td>";
	echo "</tr>";
	
	//Добавить файл
	echo "<tr>
	<td bgcolor=green colspan=4><input type=file name=new_file onchange=ch_new_file()>
	<input type=submit name=upload disabled value=Загрузить>	
	</td>
	<input type=hidden name=del_file>";
	//
	//Список файлов

foreach (glob($path_to_folders.$_SESSION['project_name'][$_SESSION['i']]."\\*.*") as $filename) {
    echo "<tr>
	<td bgcolor=white><b>".basename($filename)."</b></td>
	<td bgcolor=white>".filesize($filename)."</td>
	<td bgcolor=white>".date("d.m.Y H:i:s",filemtime($filename))."</td>
	<td bgcolor=white>
	<a href=\"javascript:if(confirm('Действительно хотите УДАЛИТЬ ФАЙЛ ?')){files_frm.del_file.value='".basename($filename)."';files_frm.submit();}\"><img src=del.gif title=\"Удалить файл\" border=0></a>
	</td>
	</tr>";
}

	echo "</table></from>";
	//

?>
<script language="javascript">
function ch_new_file() {
	if (document.all.new_file.value=='') {
	document.all.upload.disabled=true;
	} else {
	document.all.upload.disabled=false;
	}
}
</script>