<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
header('X-UA-Compatible: IE=EmulateIE7');
?>
<HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<form name=frm method=post action=tree.php target=fr1>
<table id=tbl><tr><td>
<?php

extract($_REQUEST);

//if (isset($del_node) or isset($edit_node)) {
//	include("../../sc_conf/sc_conn_string");
//	$q=OCIParse($c,"select name from sc_punkt where punkt_id='".$punkt_id."'");
//	OCIExecute($q,OCI_DEFAULT);
//	OCIFetch($q);
//	$node_name=OCIResult($q,"NAME");
//}

include("../../sc_conf/sc_conn_string");

echo "<h4 style='color:#9900FF'>".$_SESSION['project']['name']."</h4>";

if (isset($add_node)) {
echo "<h4>Добавление ветви</h4>";

echo "<table>";

echo "<tr><td><font size=3><b>Название: </b></font></td></tr><tr><td><textarea cols=70 rows=3 name=text></textarea><hr></td></tr>";

echo "<tr><td><font size=3><b>Комментарий</b> (можно добавить в форму): </b></font></td></tr><tr><td><textarea cols=70 rows=3 name=tree_name></textarea><hr></td></tr>";

echo "<tr><td><font size=3><b>Ссылка</b></font><input type=text size='80' name=link><hr></td></tr>";

echo "<tr><td><font size=3><b>расписание: </b></font><select name=shedule_id>
<option value=''>Виден всегда</option>";
$q=OCIParse($c,"select id,name from sc_shedule where project_id='".$_SESSION['project']['id']."' order by name");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
echo "</select><hr></td></tr>";

echo "<tr><td><input type=submit name=add_node value=ДОБАВИТЬ></tr></td>";
echo "</table>";

echo "<input type=hidden name=parent_id value=".$parent_id.">";
echo "<input type=hidden name=parent_lvl value=".$parent_lvl.">";

echo "<script language=jscript>
function document.all.add_node.onclick() {
	if (frm.text.value=='') {
	alert('Ведите название ветви!')
	return false;
	}
document.all.tbl.style.display='none';
}
</script>";
}

if (isset($edit_node)) {
echo "<h4>Редактированеи ветви</h4>";

if ($punkt_id<>'') {
	echo "<table>";

	$q=OCIParse($c,"select replace(t.name,'\"','&quot;') tree_name, replace(p.text,'\"','&quot;') punkt_name,replace(t.link,'\"','&quot;') link from sc_punkt_tree t, sc_punkt p 
	where p.project_id='".$_SESSION['project']['id']."' 
	and t.punkt_id=p.id and t.id='".$tree_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);


	echo "<tr><td><font size=3><b>Название: </b></font></td></tr><tr><td><textarea cols=70 rows=3 name=node_name>".OCIResult($q,"PUNKT_NAME")."</textarea><hr></td></tr>";

	echo "<tr><td><font size=3><b>Комментарий</b> (можно добавить в форму): </b></font></td></tr><tr><td><textarea cols=70 rows=3 name=tree_name>".OCIResult($q,"TREE_NAME")."</textarea><hr></td></tr>";
	
	echo "<tr><td><font size=3><b>Ссылка</b></font><input type=text size='80' name=link value=\"".OCIResult($q,"LINK")."\"><hr></td></tr>";
	
	echo "<tr><td><font size=3><b>расписание: </b></font><select name=shedule_id>";

	$q=OCIParse($c,"select t.shedule_id, s.name from sc_punkt_tree t, sc_shedule s 
	where t.id='".$tree_id."' and s.project_id='".$_SESSION['project']['id']."'
	and t.shedule_id=s.id");
	OCIExecute($q,OCI_DEFAULT);
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"SHEDULE_ID").">".OCIResult($q,"NAME")."</option>";
		}
	echo "<option value=''>Виден всегда</option>";
	$q=OCIParse($c,"select id,name from sc_shedule where project_id='".$_SESSION['project']['id']."' order by name");
	OCIExecute($q,OCI_DEFAULT);
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
	echo "</select><hr></td></tr>";

	echo "<tr><td><input type=submit name=edit_node value=СОХРАНИТЬ></tr></td>";
	echo "</table>";


	echo "<input type=hidden name=punkt_id value=".$punkt_id.">";
	echo "<input type=hidden name=tree_id value=".$tree_id.">";
}
else {
	echo "<table>";

	$q=OCIParse($c,"select punkt_name from sc_projects 
	where id='".$_SESSION['project']['id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);

	echo "<tr><td><font size=3><b>Комментарий</b> (можно добавить в форму): </b></font></td></tr><tr><td><textarea cols=70 rows=3 name=tree_name>".OCIResult($q,"PUNKT_NAME")."</textarea><hr></td></tr>";

//	echo "Комментарий (можно добавить в форму):<input type=text name=tree_name value=\"".OCIResult($q,"PUNKT_NAME")."\"><br>";	

	echo "<tr><td><input type=submit name=edit_node value=СОХРАНИТЬ></tr></td>";
	echo "</table>";

	echo "<input type=hidden name=node_name value='main'>";
	echo "<input type=hidden name=punkt_id value='main'>";
	echo "<input type=hidden name=tree_id value='main'>";
}
echo "<script language=jscript>
function document.all.edit_node.onclick() {
	if (frm.node_name.value=='') {
	alert('Ведите название ветви!')
	return false;
	}
document.all.tbl.style.display='none';
}
</script>";
}

if (isset($del_node)) {
$q=OCIParse($c,"select text from sc_punkt where id='".$punkt_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo "<font color=red><h4>Действительно удалить пункт \"".OCIResult($q,"TEXT")."\" со всеми подпунктами и из всех ветвей?</h4></font>";
echo "<input type=hidden name=punkt_id value=".$punkt_id.">
      <input type=hidden name=parent_id value=".$parent_id."><br>";
echo "<input type=button name=cancel value=ОТМЕНА>
<input type=submit name=del_node value=ОК><br>";

echo "<script language=jscript>
function document.all.del_node.onclick() {
document.all.tbl.style.display='none';
}
function document.all.cancel.onclick() {
document.all.tbl.style.display='none';
}
</script>";
}

?>
</table></tr></td>
</form>
</body>
</html>