<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7'>
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0">

<?php

extract($_REQUEST);
if (!isset($project_num)) {
	if (isset($_SESSION['project_id']) and count($_SESSION['project_id'])==1) {$_SESSION['i']=0; $_SESSION['fr_w']=$_SESSION['fr_width'][$_SESSION['i']];}	
	if (isset($_SESSION['project_id']) and count($_SESSION['project_id'])>1) $_SESSION['fr_w']='200';
}
else {
$_SESSION['i']=$project_num;
$_SESSION['fr_w']=$_SESSION['fr_width'][$_SESSION['i']];
}

if (isset($blank) and $blank==1) exit();
if (isset($blank) and $blank==2) {
echo " | <a href=login.php target=_parent>Выход </a> | <font color=red><b>Пользователю не назначено ни одного проекта</b>"; 
exit();
}

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/sc_local_network");

if (count($_SESSION['project_id'])>1) {
echo "<form method=post><nobr><select name=project_num onchange=select_project()>";
/*if (isset($_SESSION['admin']) and $_SESSION['admin']==1) {
	echo "<option value=0>ВСЕ ПРОЕКТЫ</option>";
}*/
if (!isset($_SESSION['i'])) echo "<option>Выберите проект</option>";
for($i=0; $i<count($_SESSION['project_id']); $i++) {
echo "<option value=".$i;
if(isset($_SESSION['i']) and $_SESSION['i']==$i) echo " selected";
echo ">".$_SESSION['project_name'][$i]."</option>";
}
echo "</select>
<input type=submit name=logined value=Выбрать>";

echo "<script language='javascript'>
document.all.logined.style.display='none';
parent.fr12.location.reload(parent.fr12.location.href);
function select_project() {
document.all.logined.click();
}
</script>";
}
if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "<a href=login.php?refresh target=_parent><img border=0 src=refresh.gif title=Обновить></a>";
echo " | <a href=login.php target=_parent>Выход </a>"; 

if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "| <a href=adm_prj.php target=fr12>Админ </a>";
if (!isset($_SESSION['i'])) exit();
if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "| <a href=adm_files.php target=fr12>Файлы </a>";
if ($_SESSION['ch_sc'][$_SESSION['i']]==1) {echo "| <a href=edit_sc.php target=fr12>Сценарий </a> "; 
if ($from_local_addr=='y') echo "(<a href='".$path_to_scenary."?project_id=".$_SESSION['project_id'][$_SESSION['i']]."&' target=_blank><img border=0 src=visible.gif title='посмотреть сценарий оператора'></a> )";
echo " | <a href=edit_table.php target=fr12>Таблицы </a>";
echo " | <a href=edit_shedule.php target=fr12>Расписания </a> | <a href=edit_forw_list.php target=fr12>Переадресация </a>";} 
if ($_SESSION['ch_form'][$_SESSION['i']]==1) echo "| <a href=edit_form.php target=fr12>Формы </a>"; 
if ($_SESSION['ch_email'][$_SESSION['i']]==1) echo "| <a href=edit_email.php target=fr12>e-mail </a>";
if ($_SESSION['view_rep'][$_SESSION['i']]==1) echo "| <a href=rep_fr.php target=fr12>Отчеты </a>";
if ($_SESSION['view_billing'][$_SESSION['i']]==1) echo "| <a href=billing.php target=fr12>Биллинг </a>";
if ($_SESSION['view_sms_log'][$_SESSION['i']]==1) echo "| <a href=sms_log.php target=fr12>СМС-лог </a>";
if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "| <a href=superbilling.php target=fr12>Супербиллинг</a>";
echo " |</nobr>
</form>";
?>
</body>
</html>