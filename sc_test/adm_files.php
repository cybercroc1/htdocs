<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
$_SESSION['last_url']='adm_files.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //if ($_SESSION['project']['id']==0) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_path");

if ($_SESSION['project']['id']==0) $path_to_project_files=$path_to_folders;
else $path_to_project_files=$path_to_folders.$_SESSION['project']['name']."\\";

if (isset($upload)) {
	if($_FILES["new_file"]["size"] > 1024*3*1024) {echo ("</font color=red>������ ����� ��������� 3 ���������!</font>");}
	else {
	 	if (!@is_uploaded_file($_FILES['new_file']["tmp_name"])) {echo "<font color=red>������ �������� �����!</font>";}
		else {
			if (!@move_uploaded_file($_FILES['new_file']["tmp_name"],$path_to_project_files.$_FILES['new_file']["name"])) {echo "<font color=red>������ �������� �����!</font>";}
		}
	}
}	

if (isset($del_file) and $del_file<>'') {

unlink($path_to_project_files.$del_file);

}

echo "<form name=files_frm action=adm_files.php method=post enctype=\"multipart/form-data\">";
	echo "<font size=4>�����</font><hr>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white align=center><b>���</b></td>
	<td bgcolor=white align=center><b>������</b></td>
	<td bgcolor=white align=center><b>����<br>���������</b></td>
	<td bgcolor=white></td>";
	echo "</tr>";
	
	//�������� ����
	echo "<tr>
	<td bgcolor=green colspan=4><input type=file name=new_file onchange=ch_new_file()>
	<input type=submit name=upload disabled value=���������>	
	</td>
	<input type=hidden name=del_file>";
	//
	//������ ������

	//echo $path_to_project_files."*.*";
	
foreach (glob($path_to_project_files."*.*") as $filename) {
    if(filetype($filename)=='file') { //������ �����
		echo "<tr>
		<td bgcolor=white><b>".basename($filename)."</b></td>
		<td bgcolor=white>".filesize($filename)."</td>
		<td bgcolor=white>".date("d.m.Y H:i:s",filemtime($filename))."</td>
		<td bgcolor=white>
		<a href=\"javascript:if(confirm('������������� ������ ������� ���� ?')){files_frm.del_file.value='".basename($filename)."';files_frm.submit();}\"><img src=del.gif title=\"������� ����\" border=0></a>
		</td>
		</tr>";
	}
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