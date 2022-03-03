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
<?php if ($_SESSION['project']['id']==0 and $_SESSION['admin']<>1) exit(); 
if ($_SESSION['project']['ch_form']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

//Функция сохранения значений
if (isset($go_save)) {

$upd = OCIParse($c,"update sc_form_values set br=:br, dop_info=:dop_info where id=:id");
foreach($val_id as $id=>$fuck) {
	if(isset($br[$id])) $tmp_br=1; else $tmp_br='';
	OCIBindByName($upd,":id",$id);
	OCIBindByName($upd,":br",$tmp_br);
	OCIBindByName($upd,":dop_info",$dop_info[$id]);
	OCIExecute($upd, OCI_DEFAULT);
}
OCICommit($c);
}

//
if (!isset($new_br)) $new_br='';

if (isset($upload)) {
	if($_FILES["upload_file"]["size"] > 1024*1024) {echo ("</font color=red>Размер файла превышает 1 мегабайт!</font>");}
	else {
		$f = fopen($_FILES['upload_file']["tmp_name"],"r");
		
		$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_form_values
where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$ordering=OCIResult($q,"ORDERING");		
		
		$ins=OCIParse($c,"insert into sc_form_values (id,obj_id,name,project_id,br,ordering)
values (
SEQ_SC_FORM_VAL_ID.nextval,
'".$obj_id."',
:ins_value,
'".$_SESSION['project']['id']."',
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

if (isset($new_val)) new_val($obj_id,$new_val_name,str_replace("<br>","\n",$new_dop_info),$new_br,$c);
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
	where form_id='".$form_id."' and project_id=".$_SESSION['project']['id']." 
	and type_id in ('SE','MS','RA','CH','SA')
	order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").(OCIResult($q,"ID")==$obj_id?' selected':'').">".OCIResult($q,"NAME")."</option>";
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
	<td bgcolor=white><b>доп информация</b></td>
	<td bgcolor=white><b>Перенос<br>строки</b></td>
	<td bgcolor=white colspan=2><input type=submit name=go_save value=СОХРАНИТЬ></td>";
	echo "</tr>";
	
	//Загрузка списка из файла
	echo "<input type=file name=upload_file onchange=ch_upload_file()><input type=submit name=upload disabled value=Загрузить><hr>";

	//Добавить значение
	echo "<tr>";
	echo "<td bgcolor=green id='td_new_val_name'><input type=text name='new_val_name[]' size=35 onpaste='fPaste();return false'></td>";
	echo "<td bgcolor=green id='td_new_dop_info'><textarea name='new_dop_info[]' cols=40 rows=4></textarea></td>";	
	echo "<td bgcolor=green align=center valign=top><input type=checkbox checked name=new_br value=1></td>";
	echo "<td bgcolor=green colspan=2 valign=top><input type=submit name=new_val value=ДОБАВИТЬ></td></tr>";
	//
	//Значения объекта
	$q=OCIParse($c,"select * from sc_form_values where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."' order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	while (OCIFetch($q)) {
	echo "<td bgcolor=white><input type=hidden name=val_id[".ociresult($q, "ID")."]>".OCIResult($q,"NAME")."</td>";
	echo "<td bgcolor=white style='padding:0px'><textarea name='dop_info[".ociresult($q, "ID")."]' cols=40 rows=".(substr_count(OCIResult($q,"DOP_INFO"),"\n")+1).">".OCIResult($q,"DOP_INFO")."</textarea></td>";

	echo "<td bgcolor=white align=center><input type=checkbox name=br[".ociresult($q, "ID")."]".(ociresult($q, "BR")==1?" checked":"").">";
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
where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update sc_form_values set ordering='".$ordering."'
	where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update sc_form_values set ordering='".$perv_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$val_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//

//Функция вниз
	function down($obj_id,$val_id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_form_values
where obj_id='".$obj_id."' and project_id=".$_SESSION['project']['id']." and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_form_values set ordering='".$ordering."'
		where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_form_values set ordering='".$next_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$val_id."'");
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
function new_val($obj_id,$new_val_name,$new_dop_info,$new_br,$c) {
	$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_form_values
where obj_id='".$obj_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$ordering=OCIResult($q,"ORDERING");
	$ins=OCIParse($c,"insert into sc_form_values (id,obj_id,name,dop_info,project_id,br,ordering)
	values (
	SEQ_SC_FORM_VAL_ID.nextval,
	'".$obj_id."',
	:val,
	:dop_info,
	'".$_SESSION['project']['id']."',
	'".$new_br."',
	:ordering)");
	foreach($new_val_name as $key => $val) { 
	OCIBindByName($ins,":val",$new_val_name[$key]);
	OCIBindByName($ins,":dop_info",$new_dop_info[$key]);
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
	where project_id='".$_SESSION['project']['id']."' and obj_id='".$obj_id."' and id='".$val_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}
//Функция удаления всех значений
function del_all($obj_id,$c) {
	$del=OCIParse($c,"delete from sc_form_values 
	where project_id='".$_SESSION['project']['id']."' and obj_id='".$obj_id."'");
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
			if(!vRow[1]) vRow[1]='';
			if(i==0) {
				for(j=0; j<vRow.length; j++) {
					if(j==0) {
						document.all['new_val_name[]'].value=vRow[0];
						document.all['new_dop_info[]'].value=vRow[1];
					}	
				}
			}
			else {
				for(j=0; j<vRow.length; j++) {
					if(j==0) {
						td_new_val_name.innerHTML+='<br><input type=text name=\'new_val_name[]\' value=\''+vRow[0]+'\' size=35>';
						td_new_dop_info.innerHTML+='<br><textarea name=\'new_dop_info[]\' cols=40 rows=1>'+vRow[1]+'</textarea>';
					}
				}
			}	
		}
//
//}
}
</script>