<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="8">	
<script src="adm.users.edit.js"></script>
<?php 

extract($_REQUEST);

if($_SESSION['user']['rw_users']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

$project_id=$_SESSION['adm']['project']['id'];


echo " | ";
echo "<font size=4>Пользователи</font> | ";
echo "<textarea id=buffer onpaste=this.value='' rows=1 cols=15>буфер обмена</textarea> | ";
echo "<font align=right><a href='help.adm.ank.list.edit.html' target='_blank'>Справка</a></font>";
echo "<hr>";

include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");

echo "<form name=frm method=post action=adm.users.save.php target='logFrame'>";	


echo "<table id=tbl name=tbl style='table-layout:fixed'>";
echo "<th width=12></th>";
echo "<th width=12 style='cursor:pointer' title='Добавить ниже. CTRL - вставить из буфера обмена (IE), остальные браузеры - из окошка обмена' onclick=plus(this)><font color=blue>+</font></th>";
echo "<th width=40>№ (ID)</th>
	<th width=80>Логин</th>
	<th width=80>Пароль</th>
	<th width=150>ФИО</th>
	<th width=150>Роль</th>	
	</tr>";

OCIExecute($q,OCI_DEFAULT);
$idx=0; while(OCIFetch($q)) {$idx++;

	echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
	echo "<th style='cursor:pointer' title='Удалить' onClick='del_old_user(\"".OCIResult($q,"ID")."\");del_user(this)'><font color=red>-</font></th>";	
	echo "<th style='cursor:pointer' title='Добавить ниже. CTRL - вставить из буфера обмена (IE), остальные браузеры - из окошка обмена' onClick=plus(this)><font color=blue>+</font></th>";
	echo "<th style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>
	
	<input type=hidden name=val_id[".$idx."] value='".OCIResult($q,"ID")."' onchange='notsaved()'>$idx(".OCIResult($q,"ID").")</th>";
	echo "<td><input style='width:100%' type=text name=login[".$idx."] value='".OCIResult($q,"TEXT_VALUE")."' onchange='notsaved()'></td>";
	echo "<td><input style='width:100%' type=text name=password[".$idx."] value='".OCIResult($q,"CODE_VALUE")."' onchange='notsaved()'></td>";
	echo "<td>";
	echo "</td>";
	echo "</tr>";
}
echo "</table>";
echo "<hr>";
if($_SESSION['user']['rw_users']<>'w') echo "<font color=red>Редактирование запрещено!</font>";
else {
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=Сохранить onclick=this.disabled=true;frm.cancel.disabled=true;frm.submit();> ";
echo "<input type=button name=cancel value=Отмена onclick={this.style.display='none';frm.frm_submit.value='save';frm.save.value='Сохранить';document.getElementById('save_status').innerHTML='';} style='display:none' >";
}
echo "</form>";
echo "<script>var new_idx=".$idx.";</script>";
?>
</body>
</html>
