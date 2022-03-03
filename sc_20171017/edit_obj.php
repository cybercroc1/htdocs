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
<?php if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['ch_form'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

//Функция сохранения значений
if (isset($no_br)) {
$i=0;
while (@$_REQUEST['no_br'][$i]) {
if (!isset($_REQUEST[$no_br[$i]])) {
$upd = OCIParse($c,"update sc_form_values set br=null where id=".$no_br[$i]."");
OCIExecute($upd, OCI_DEFAULT);}

$i++;}
OCICommit($c);
}

if (isset($br)) {

$i=0;
while (@$_REQUEST['br'][$i]) {

$upd = OCIParse($c,"update sc_form_values set br='1' where id=".$br[$i]."");
OCIExecute($upd, OCI_DEFAULT);
$i++;}
}
OCICommit($c);
if (isset($go_save)) echo "<script>document.location='edit_form.php?form_id=".$form_id."'</script>";
//
if (!isset($new_br)) $new_br='';

if (isset($upload)) {
	if($_FILES["upload_file"]["size"] > 1024*1024) {echo ("</font color=red>Размер файла превышает 1 мегабайт!</font>");}
	else {
		$f = fopen($_FILES['upload_file']["tmp_name"],"r");
		
		$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_form_values
where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$ordering=OCIResult($q,"ORDERING");		
		
		$ins=OCIParse($c,"insert into sc_form_values (id,obj_id,name,project_id,br,ordering)
values (
SEQ_SC_FORM_VAL_ID.nextval,
'".$obj_id."',
:ins_value,
'".$_SESSION['project_id'][$_SESSION['i']]."',
'".$new_br."',
:ordering
)");
		$i=0;
		while (!feof($f)) {
			$ordering++;
			$ins_value=fgets($f);
			if ($ins_value<>'') {
				OCIBindByName($ins,":ordering",$ordering);
				OCIBindByName($ins,":ins_value",$ins_value);
				if (OCIExecute($ins,OCI_COMMIT_ON_SUCCESS)) $i++;
			}
		}
		echo "<font color=red><b>Загружено ".$i." записей</b></font><hr>";
		fclose($f);
		unlink($_FILES['upload_file']["tmp_name"]);
	}
}

if (isset($new_val)) new_val($obj_id,$new_val_name,$new_br,$c);
if (isset($del_val)) del_val($obj_id,$val_id,$c);
if (isset($del_all)) del_all($obj_id,$c);
if (isset($sort_by_name)) sort_by_name($obj_id,$c);
if (isset($up)) up($obj_id,$val_id,$ordering,$c);
if (isset($down)) down($obj_id,$val_id,$ordering,$c);
echo "<form action=edit_obj.php method=post enctype=\"multipart/form-data\">";
echo "<font size=4>Редактирование значений объекта</font><br>";


//Выбор объекта
if (isset($obj_id) and $obj_id<>'') {
	echo "<select name=obj_id onchange=ch_obj_id()>";
	$q=OCIParse($c,"select * from sc_form_object
	where id='".$obj_id."' and form_id='".$form_id."' and project_id=".$_SESSION['project_id'][$_SESSION['i']]." ");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	$q=OCIParse($c,"select * from sc_form_object
	where form_id='".$form_id."' and project_id=".$_SESSION['project_id'][$_SESSION['i']]." 
	and type_id in ('SE','MS','RA','CH')
	order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>
	<input type=submit name=ch_obj value=ВЫБРАТЬ>";
	echo " <a href=\"javascript:del_all('".$obj_id."','".$form_id."')\"><img src=del.gif title=\"Удалить все значения\" border=0></a><hr>";
//
	echo "<font size=4><a href=edit_form.php?form_id=".$form_id.">Редактирование формы</a></font><hr>";

	echo "<input type=hidden name=form_id value=".$form_id.">";

	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white><b>Значение </b><a href=\"?sort_by_name=1&obj_id=".$obj_id."&form_id=".$form_id."\">сортировать по имени</a></td>
	<td bgcolor=white><b>Перенос<br>строки</b></td>
	<td bgcolor=white colspan=2><input type=submit name=go_save value=СОХРАНИТЬ></td>";
	echo "</tr>";
	
	//Загрузка списка из файла
	echo "<input type=file name=upload_file onchange=ch_upload_file()><input type=submit name=upload disabled value=Загрузить><hr>";

	//Добавить значение
	echo "<tr>
	<td bgcolor=green id='td_new_val_name'><input type=text name='new_val_name[]' size=35 onpaste='fPaste();return false'></td>";
	echo "<td bgcolor=green align=center valign=top><input type=checkbox checked name=new_br value=1></td>";
	echo "<td bgcolor=green colspan=2 valign=top><input type=submit name=new_val value=ДОБАВИТЬ></td></tr>";
	//
	//Значения объекта
	$q=OCIParse($c,"select * from sc_form_values where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	while (OCIFetch($q)) {
	echo "<td bgcolor=white>".OCIResult($q,"NAME")."</td>";
	
    if (ociresult($q, "BR")==1) 
   {echo "<td bgcolor=white align=center><input type=hidden name=no_br[] value=".ociresult($q, "ID")."><input type=checkbox name =".ociresult($q, "ID")." checked>";} 
	else 
   {echo "<td bgcolor=white align=center><input type=checkbox name=br[] value=".ociresult($q, "ID").">";}
	echo "</td>";
	
	echo "<td bgcolor=white align=center>";
	echo "<a href=\"?up=1&val_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&obj_id=".$obj_id."&form_id=".$form_id."\"><img border=0 src=up.gif></a>
		<a href=\"?down=1&val_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&obj_id=".$obj_id."&form_id=".$form_id."\"><img border=0 src=down.gif></a>
		</td>";

	echo "<td bgcolor=white align=center>
	<a href=\"?del_val=1&val_id=".OCIResult($q,"ID")."&obj_id=".$obj_id."&form_id=".$form_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";
	echo "</td>";

	echo "</tr>";
	}
	echo "</table>";
}
echo "</form>";

//Функция вверх
	function up($obj_id,$val_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ordering),0) perv_ordering from sc_form_values
where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update sc_form_values set ordering='".$ordering."'
	where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update sc_form_values set ordering='".$perv_ordering."' where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and id='".$val_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//

//Функция вниз
	function down($obj_id,$val_id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_form_values
where obj_id='".$obj_id."' and project_id=".$_SESSION['project_id'][$_SESSION['i']]." and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_form_values set ordering='".$ordering."'
		where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_form_values set ordering='".$next_ordering."' where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and id='".$val_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//

//Функция сортировки по имени
function sort_by_name($obj_id,$c) {
	$q=OCIParse($c,"select id from sc_form_values where obj_id='".$obj_id."' order by name nulls first");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++;
		$upd=OCIParse($c,"update sc_form_values set ordering='".$i."' where id='".OCIResult($q,"ID")."'");
		OCIExecute($upd,OCI_DEFAULT);
	}
	OCICommit($c);
}
//

//Функция добавления значения
function new_val($obj_id,$new_val_name,$new_br,$c) {
	$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_form_values
where obj_id='".$obj_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$ordering=OCIResult($q,"ORDERING");
	$ins=OCIParse($c,"insert into sc_form_values (id,obj_id,name,project_id,br,ordering)
	values (
	SEQ_SC_FORM_VAL_ID.nextval,
	'".$obj_id."',
	:val,
	'".$_SESSION['project_id'][$_SESSION['i']]."',
	'".$new_br."',
	:ordering)");
	foreach($new_val_name as $val) { 
	OCIBindByName($ins,":val",$val);
	OCIBindByName($ins,":ordering",$ordering);
	OCIExecute($ins,OCI_DEFAULT);
	$ordering++;
	}
	OCICommit($c);	
}
//
//Функция удаления значения
function del_val($obj_id,$val_id,$c) {
	$del=OCIParse($c,"delete from sc_form_values 
	where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and obj_id='".$obj_id."' and id='".$val_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}
//Функция удаления всех значений
function del_all($obj_id,$c) {
	$del=OCIParse($c,"delete from sc_form_values 
	where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and obj_id='".$obj_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}
//
?>
<script language='javascript'>
document.all.ch_obj.style.display='none';
function ch_obj_id() {
	document.all.ch_obj.click();
}
function ch_upload_file() {
	if (document.all.upload_file.value=='') {
	document.all.upload.disabled=true;
	} else {
	document.all.upload.disabled=false;
	}
}
function del_all(obj_id,form_id) {
if (confirm('Действительно хотите УДАЛИТЬ ВСЕ ЗНАЧЕНИЯ ?')) document.location='?del_all=1&obj_id='+obj_id+'&form_id='+form_id;
}
function fPaste() {
//with(add_client) {
	vClipBoard=clipboardData.getData('Text');
	vRows=vClipBoard.split(/\n|\r\n/);
		for(i=0; i<vRows.length; i++) {
			vRow=vRows[i].split(/	/);
			if(i==0) {
				for(j=0; j<vRow.length; j++) {
					if(j==0) document.all['new_val_name[]'].value=vRow[0];
				}
			}
			else {
				for(j=0; j<vRow.length; j++) {
					if(j==0) td_new_val_name.innerHTML+='<br><input type=text name=\'new_val_name[]\' value=\''+vRow[0]+'\' size=35>';
				}
			}	
		}
//
//}
}
</script>