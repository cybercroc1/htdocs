<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
//header('X-UA-Compatible: IE=EmulateIE7');
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<script>
<?php
extract($_REQUEST);
include("../../sc_conf/sc_path");
echo "function open_local(filename) {
open('".str_replace('\\','\\\\',$net_path_to_folders).$_SESSION['project']['name']."\\\'+filename);
}";
?>
</script>
<body topmargin=0>
<?php if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

include("../../sc_conf/sc_conn_string");

echo "<form method=post action=edit_sc.php target=fr12>";

//if (isset($width_save)) {
//$_SESSION['project']['fr_width']=$fr_width;
//$_SESSION['fr_w']=$fr_width;
//$upd=OCIParse($c,"update sc_projects set tree_width='".$fr_width."'
//where id='".$_SESSION['project']['id']."'");
//OCIExecute($upd,OCI_DEFAULT);
//OCICommit($c);
//}

if (isset($del_node)) {
$del=OCIParse($c,"delete from sc_punkt
where id in (
select p.id from sc_punkt_tree t, sc_punkt p
where t.punkt_id=p.id and p.deleted is null and p.project_id='".$_SESSION['project']['id']."'
connect by prior t.punkt_id=t.parent_id start with t.punkt_id='".$punkt_id."'
)
and deleted is null");
OCIExecute($del,OCI_DEFAULT);

$q=OCIParse($c,"select t.id from sc_punkt_tree t, sc_punkt p
where t.punkt_id=p.id and p.deleted is null and p.project_id='".$_SESSION['project']['id']."'
connect by prior t.punkt_id=t.parent_id start with parent_id='".$parent_id."'");
OCIExecute($q,OCI_DEFAULT);
if (!OCIFetch($q)) {
	$upd2=OCIParse($c,"update sc_punkt_tree set end_node='1'
	where punkt_id='".$parent_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	}
OCICommit($c);
}


if (isset($add_node)) {
$q=OCIParse($c,"select nvl(max(id)+1,1) id from sc_punkt");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q); $punkt_id=OCIResult($q,"ID");
$ins=OCIParse($c,"insert into sc_punkt (id,text,project_id) values ('".$punkt_id."',:node_name,'".$_SESSION['project']['id']."')");
OCIBindByName($ins,":node_name",$text);
OCIExecute($ins,OCI_DEFAULT);
$ins2=OCIParse($c,"insert into sc_punkt_tree (id,parent_id,punkt_id,lvl,end_node,shedule_id,name,link) values ((select nvl(max(id)+1,1) from sc_punkt),'".$parent_id."','".$punkt_id."','".($parent_lvl+1)."','1','".$shedule_id."','".$tree_name."',:link)");
OCIBindByName($ins2,":link",$link);
OCIExecute($ins2,OCI_DEFAULT);
$upd=OCIParse($c,"update sc_punkt_tree  set end_node=null where punkt_id='".$parent_id."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

if (isset($edit_node)) {

	if($punkt_id=='main') {
	$upd=OCIParse($c,"update sc_projects set punkt_name='".$tree_name."' where id='".$_SESSION['project']['id']."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
	}
	else {
	$upd=OCIParse($c,"update sc_punkt set text=:node_name where id='".$punkt_id."'");
	OCIBindByName($upd,":node_name",$node_name);
	OCIExecute($upd,OCI_DEFAULT);
	
	$upd2=OCIParse($c,"update sc_punkt_tree set name='".$tree_name."', link=:link where id='".$tree_id."'");
	OCIBindByName($upd2,":link",$link);
	OCIExecute($upd2,OCI_DEFAULT);
	
	
	$upd3=OCIParse($c,"update sc_punkt_tree set shedule_id='".$shedule_id."' where id in (
	select t.id 
	from sc_punkt_tree t, sc_punkt p
	where t.punkt_id=p.id and p.deleted is null
	and p.project_id='".$_SESSION['project']['id']."'
	connect by prior t.punkt_id=t.parent_id start with t.id='".$tree_id."' 
	)");
	OCIExecute($upd3,OCI_DEFAULT);
	OCICommit($c);
	}
}

if (isset($invisible)) {


$upd=OCIParse($c,"update sc_punkt_tree set invisible='1' where id in (
select t.id 
from sc_punkt_tree t, sc_punkt p
where t.punkt_id=p.id and p.deleted is null
and p.project_id='".$_SESSION['project']['id']."'
connect by prior t.punkt_id=t.parent_id start with t.id='".$tree_id."' 
)");
//$upd=OCIParse($c,"update sc_punkt_tree set invisible='1' where id='".$tree_id."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

if (isset($visible)) {
$upd=OCIParse($c,"update sc_punkt_tree set invisible=null where id  in
(select t.id
from sc_punkt_tree t, sc_punkt p
where t.punkt_id=p.id and p.deleted is null
and p.project_id='".$_SESSION['project']['id']."'
connect by prior t.parent_id=t.punkt_id
start with t.id='".$tree_id."'
)");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

$q=OCIParse($c,"select name,punkt_name from sc_projects 
where id='".$_SESSION['project']['id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$project_name=OCIResult($q,"NAME");
$punkt_name=OCIResult($q,"PUNKT_NAME");

$q=OCIParse($c,"select t.id tree_id,p.id punkt_id,t.parent_id,t.lvl,p.text,p.link,t.end_node,t.invisible,s.name shedule_name, t.name tree_name, t.link tlink 
from sc_punkt_tree t, sc_punkt p, sc_shedule s
where t.punkt_id=p.id and p.deleted is null and t.shedule_id=s.id(+) and p.project_id='".$_SESSION['project']['id']."'
connect by prior t.punkt_id=t.parent_id start with t.parent_id is null
order siblings by p.text");
OCIExecute($q,OCI_DEFAULT);

echo "ширина:<input type=text size=2 name=fr_width value=".$_SESSION['project']['fr_width']."><input type=submit name=width_save value=ok><hr>";
echo "<table><tr><td nowrap>";
echo "<a href=\"edit_tree.php?add_node=1&parent_id=&parent_lvl=0\" target=fr2><img src=add_node.gif title=\"Добавить ветвь\" border=0> </a>
<a href=\"edit_tree.php?edit_node=1&punkt_id=&tree_id=\" target=fr2><img src=edit.gif title=\"Редактировать ветвь\" border=0></a>";
if ($punkt_name<>'') echo "(<font color=red>".$punkt_name."</font>)<br>";
echo "<a href=\"body.php\" target=fr2><font size=4> ".$project_name." </font></a>";
echo "</table></tr></td>";
echo "<table id=tbl cellspacing=0 cellpadding=0 border=0>";
$row_id=0;
while(OCIFetch($q)) {
	echo "<tr id=".$row_id." "; if (OCIResult($q,"LVL")>1 and OCIResult($q,"PUNKT_ID")<>@$punkt_id) echo "style=display:''"; else {}; echo " lvl=".OCIResult($q,"LVL")."><td>";
	if (OCIResult($q,"LVL")>1) echo "<hr style='height:1px;border:0px;background-color:gray;'>"; else echo "<hr style='height:2px;border:0px;background-color:gray;'>";
	for ($i=1; $i<OCIResult($q,"LVL"); $i++) {echo "&nbsp;&nbsp;&nbsp;&nbsp;";}
	if (OCIResult($q,"END_NODE")<>1) {
		echo "<a href=\"javascript:menu(".OCIResult($q,"LVL").",".$row_id.")\"><img id=img".$row_id." border=0 op=opened src=nodeopened.gif></a>";
		}
		else {
		echo "<img src=node.gif>";
		}

		if (OCIResult($q,"INVISIBLE")=='1') {
		echo " <a href=\"tree.php?visible=1&tree_id=".OCIResult($q,"TREE_ID")."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
		}
		else {
		echo " <a href=\"tree.php?invisible=1&tree_id=".OCIResult($q,"TREE_ID")."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
		}
		
		if (OCIResult($q,"SHEDULE_NAME")<>'') {
		echo "(<font color=red>".OCIResult($q,"SHEDULE_NAME")."</font>)";
		}

		echo "<a href=\"edit_tree.php?add_node=1&parent_id=".OCIResult($q,"PUNKT_ID")."&parent_lvl=".OCIResult($q,"LVL")."\" target=fr2> <img src=add_node.gif title=\"Добавить ветвь\" border=0></a>
	<a href=\"edit_tree.php?edit_node=1&punkt_id=".OCIResult($q,"PUNKT_ID")."&tree_id=".OCIResult($q,"TREE_ID")."\" target=fr2><img src=edit.gif title=\"Редактировать ветвь\" border=0></a>
	<a href=\"edit_tree.php?del_node=1&parent_id=".OCIResult($q,"PARENT_ID")."&punkt_id=".OCIResult($q,"PUNKT_ID")."&node_name=".OCIResult($q,"TEXT")."\" target=fr2><img src=del.gif title=\"Удалить ветвь\" border=0></a>"; 
	if (OCIResult($q,"TREE_NAME")<>'') {
	echo "<br>(<font color=red>".OCIResult($q,"TREE_NAME")."</font>)";
	}
	echo "<br>";
	if (OCIResult($q,"TLINK")=='') echo "<a href=\"body.php?tree_id=".OCIResult($q,"TREE_ID")."&punkt_id=".OCIResult($q,"PUNKT_ID")."#p".OCIResult($q,"PUNKT_ID")."\" target=fr2>";
	else echo "<a href='".OCIResult($q,"TLINK")."' target=fr2>";
	if (OCIResult($q,"LVL")>1) echo "<font size=2>"; else echo "<font size=3>";
	echo " ".OCIResult($q,"TEXT")." </font>";
	echo"</a></td>
	</tr>";
	$row_id++;
}
if ($row_id>0) echo "<tr><td><hr style='height:2px;border:0px;background-color:gray;'></td></tr>";
?>
</table>
</form>
<script>
window.onresize = function() {
document.all.fr_width.value=document.body.offsetWidth;
}

function menu(lvl,row_id) {
	with (document.all.tbl) {
		if (document.images['img'+row_id].op=='opened') {document.images['img'+row_id].src='nodeclosed.gif'; document.images['img'+row_id].op='closed'}
		else if (document.images['img'+row_id].op=='closed') {document.images['img'+row_id].src='nodeopened.gif'; document.images['img'+row_id].op='opened'}
		
		for (i=row_id; i+1<rows.length; i++) {
			if (rows[i+1].lvl==lvl+1 && rows[i+1].style.display=='none') {
			rows[i+1].style.display='';
			if (document.images['img'+rows[i].id]) {document.images['img'+rows[i].id].src='nodeopened.gif'; rows[i].op='opened'}
			}
			else if (rows[i+1].lvl>lvl && rows[i+1].style.display=='') {
			rows[i+1].style.display='none';
			if (document.images['img'+rows[i].id]) {document.images['img'+rows[i].id].src='nodeclosed.gif'; rows[i].op='closed'}
			}
			if (rows[i+1].lvl<=lvl) break;
		}			
	}
}
</script>
</body>
</html>