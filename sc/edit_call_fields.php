<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
header('X-UA-Compatible: IE=EmulateIE7');
?>
<HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0 or $_SESSION['admin']<>1) exit(); 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

//Функция сохранения значений
//if (isset($go_save)) {
if(isset($id) and is_array($id)) {
$upd = OCIParse($c,"update SC_CALL_FILEDS set var_name=:var_name, name=:name, reports=:rep, email=:email, url=:url where id=:id and project_id='".$_SESSION['project']['id']."'");
foreach($id as $id=>$fuck) {
	$v1=trim($var_name[$id]);
	$v2=trim($name[$id]);
	isset($rep[$id])?$tmp_rep='y':$tmp_rep='';
	isset($email[$id])?$tmp_email='y':$tmp_email='';
	isset($url[$id])?$tmp_url='y':$tmp_url='';
	OCIBindByName($upd,":id",$id);
	OCIBindByName($upd,":var_name",$v1);
	OCIBindByName($upd,":name",$v2);
	OCIBindByName($upd,":rep",$tmp_rep);
	OCIBindByName($upd,":email",$tmp_email);
	OCIBindByName($upd,":url",$tmp_url);
	OCIExecute($upd, OCI_DEFAULT);
}
OCICommit($c);
}

//
if (isset($add)) add(trim($new_var_name),trim($new_name),$c,(isset($new_rep)?'y':''),(isset($new_email)?'y':''),(isset($new_url)?'y':''));
if (isset($del)) del($id,$c);
if (isset($sort_by_var_name)) sort_by_var_name($c);
if (isset($sort_by_name)) sort_by_name($c);
if (isset($up)) up($id,$ordering,$c);
if (isset($down)) down($id,$ordering,$c);
echo "<form method=post action=edit_call_fields.php>";
if ($_SESSION['project']['ch_form']==1) echo "<a href=edit_form.php>Редактирование формы</a> ";
if ($_SESSION['project']['ch_email']==1) echo " | <a href=edit_email.php>Редактирование е-мейлов</a>";
if ($_SESSION['admin']==1) echo " | <a href=edit_inject.php>Внешние формы (PHP-injects) </a>";
echo "<font size=4> | Дополнительные поля звонка </font>";
echo "<hr>";
echo "Данные поля будут отображаться в отчетах, отправляться по емейл и на URL.<br>
Имя переменной - переменная, переданная сценарию методом POST или GET.<br>
Имя в отчете - название поля в отчете<br>
Сортировка влияет на порядок отображения полей в отчетах<br>";

//Выбор объекта
if ($_SESSION['project']['id']<>'0') {

	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white><b>ID</b></td>
	<td bgcolor=white><b>Имя переменной</b><a href=\"?sort_by_var_name\"> отсортировать</a></td>
	<td bgcolor=white><b>Имя в отчете</b><a href=\"?sort_by_name\"> отсортировать</a></td>
	
	<td bgcolor=white><b>отчет</b></td>	
	<td bgcolor=white><b>URL</b></td>
	<td bgcolor=white><b>email</b></td>
	
	<td bgcolor=white colspan=2><input type=submit name=go_save value=СОХРАНИТЬ></td>";
	echo "</tr>";
	
	//Добавить значение
	echo "<tr><td bgcolor=green></td>";
	echo "<td bgcolor=green id='td_new_var_name'><input type=text name='new_var_name' size=35></td>";
	echo "<td bgcolor=green id='td_new_name'><input type=text name='new_name' size=35></td>";

	echo "<td bgcolor=green id='td_new_rep'><input type=checkbox name='new_rep' checked></td>";
	echo "<td bgcolor=green id='td_new_email'><input type=checkbox name='new_email' checked></td>";
	echo "<td bgcolor=green id='td_new_url'><input type=checkbox name='new_url' checked></td>";

	echo "<td bgcolor=green colspan=5 valign=top><input type=submit name=add value=ДОБАВИТЬ></td></tr>";	
	
	//
	//Значения
	$q=OCIParse($c,"select t.id,t.var_name,t.name,t.ord,
	reports,url,email
	from SC_CALL_FILEDS t
	where project_id=".$_SESSION['project']['id']." and deleted is null
	order by ord");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	while (OCIFetch($q)) {
	echo "<td bgcolor=white><input type=hidden name=id[".ociresult($q, "ID")."]><b>".OCIResult($q,"ID")."</b></td>";
	echo "<td bgcolor=white style='padding:0px'><input type=text name='var_name[".ociresult($q, "ID")."]' value='".OCIResult($q,"VAR_NAME")."' size=35></input></td>";
	echo "<td bgcolor=white style='padding:0px'><input type=text name='name[".ociresult($q, "ID")."]' value='".OCIResult($q,"NAME")."' size=35></input></td>";
	
	echo "<td bgcolor=white style='padding:0px'><input type=checkbox name='rep[".ociresult($q, "ID")."]'".(OCIResult($q,"REPORTS")=='y'?" checked":"")."></input></td>";
	echo "<td bgcolor=white style='padding:0px'><input type=checkbox name='email[".ociresult($q, "ID")."]'".(OCIResult($q,"EMAIL")=='y'?" checked":"")."></input></td>";
	echo "<td bgcolor=white style='padding:0px'><input type=checkbox name='url[".ociresult($q, "ID")."]'".(OCIResult($q,"URL")=='y'?" checked":"")."></input></td>";
	
	echo "</td>";
	
	echo "<td bgcolor=white align=center>";
	echo "<a href=\"?up&id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORD")."\"><img border=0 src=up.gif></a>
		<a href=\"?down&id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORD")."\"><img border=0 src=down.gif></a>
		</td>";

	echo "<td bgcolor=white align=center>
	<a onclick=del('".OCIResult($q,"ID")."')><img src=del.gif title=\"Удалить\" border=0></a>";
	echo "</td>";

	echo "</tr>";
	}
	echo "</table>";
}
echo "</form>";

//Функция вверх
	function up($id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ord),0) perv_ordering from SC_CALL_FILEDS
	where project_id='".$_SESSION['project']['id']."' and ord<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update SC_CALL_FILEDS set ord='".$ordering."'
	where project_id='".$_SESSION['project']['id']."' and ord='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update SC_CALL_FILEDS set ord='".$perv_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//

//Функция вниз
	function down($id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ord) next_ordering from SC_CALL_FILEDS
	where project_id=".$_SESSION['project']['id']." and ord>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update SC_CALL_FILEDS set ord='".$ordering."'
		where project_id='".$_SESSION['project']['id']."' and ord='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update SC_CALL_FILEDS set ord='".$next_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//

//Функция сортировки по имени
function sort_by_var_name($c) {
	$q=OCIParse($c,"select id from SC_CALL_FILEDS where project_id='".$_SESSION['project']['id']."' order by var_name nulls first");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++;
		$upd=OCIParse($c,"update SC_CALL_FILEDS set ord='".$i."' where id='".OCIResult($q,"ID")."' and project_id='".$_SESSION['project']['id']."'");
		OCIExecute($upd,OCI_DEFAULT);
	}
	OCICommit($c);
}
//
//Функция сортировки по имени
function sort_by_name($c) {
	$q=OCIParse($c,"select id from SC_CALL_FILEDS where project_id='".$_SESSION['project']['id']."' order by name nulls first");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++;
		$upd=OCIParse($c,"update SC_CALL_FILEDS set ord='".$i."' where id='".OCIResult($q,"ID")."' and project_id='".$_SESSION['project']['id']."'");
		OCIExecute($upd,OCI_DEFAULT);
	}
	OCICommit($c);
}
//

//Функция добавления значения
function add($new_var_name,$new_name,$c,$new_rep,$new_email,$new_url) {
	$q=OCIParse($c,"select nvl(max(ord),0)+1 ordering from SC_CALL_FILEDS
where project_id='".$_SESSION['project']['id']."' and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$ordering=OCIResult($q,"ORDERING");
	$ins=OCIParse($c,"insert into SC_CALL_FILEDS (id,project_id,var_name,name,ord,reports,email,url)
	values (
	SEQ_SC_FORM_OBJ_ID.nextval,
	'".$_SESSION['project']['id']."',
	:new_var_name,
	:new_name,
	:ordering,
	'".$new_rep."',
	'".$new_email."',
	'".$new_url."'
	)");
	OCIBindByName($ins,":new_var_name",$new_var_name);
	OCIBindByName($ins,":new_name",$new_name);
	OCIBindByName($ins,":ordering",$ordering);
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);	
}
//
//Функция удаления значения
function del($id,$c) {
	$upd=OCIParse($c,"update SC_CALL_FILEDS set deleted=sysdate  
	where project_id='".$_SESSION['project']['id']."' and id='".$id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);	
}
?>
<script language='javascript'>
function del(id) {
if (confirm('Действительно хотите УДАЛИТЬ?')) document.location='?del&id='+id;
}
</script>