<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<table id=tbl><tr><td>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/sc_path");
if (isset($add_blog)) {
echo "<form name=frm action=edit_body.php method=post>
<table id=tbl1><tr><td valign=top>
<h4>Добавление блока:</h4>
<table id=tbl2><tr>
<td>расписание: </td><td><select name=shedule_id>
<option value=''>Виден всегда</option>";
$q=OCIParse($c,"select id,name from sc_shedule where project_id='".$_SESSION['project']['id']."' order by name");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	if(OCIResult($q,"ID")=='FV' and $_SESSION['admin']<>1) continue;
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
echo "</select></td></tr>";

echo "<td valign=top>Номера доступа: </td><td>";
$q=OCIParse($c,"select phone from SC_PHONES where project_id='".$_SESSION['project']['id']."' order by phone");
OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
	$i++;
	echo "<input type=checkbox name=cgpns[$i] checked value=".OCIResult($q,"PHONE").">".OCIResult($q,"PHONE")."</input>";
	if(($i/2)==round($i/2)) echo "<br>";
	else echo " | ";
	}
echo "<input type=hidden name=cgpns_count value='$i'></td></tr>";

//------------------------------------

echo "<tr><td valign=top>Направления: </td><td>";

echo "<input type=checkbox name=directions[in] value=y checked>Входящие</input>";
echo " | ";
echo "<input type=checkbox name=directions[callback] value=y checked>Автоперезвоны</input>";
echo " | ";
echo "<input type=checkbox name=directions[out] value=y checked>Исходящие</input>";
echo "</td></tr>";

//-----------------------------------

echo "<tr><td>тип: </td><td><select name=blog_type onchange=ch_blog_type()>";
//$q=OCIParse($c,"select * from sc_blog_type where id='".$blog_type."'
//union all
//select * from sc_blog_type");
$q=OCIParse($c,"select * from sc_blog_type order by name");

OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$blog_type?' selected':'').">".OCIResult($q,"NAME")."</option>";
	}
echo "</select></td></tr>";
echo "<tr><td><input type=hidden name=general value=n>";
echo "<input type=checkbox"; if ($general=='y') echo " checked"; else {} echo " name=general value=y> Общий для всех пунктов</tr></td>";
echo "<tr><td><input type=checkbox checked name=colapsed value=y> Свернуто</tr></td>";
echo "<tr><td><input type=checkbox checked name=new_window value=y> Открывать в отдельном окне</tr></td>";
//echo "<tr><td><input type=checkbox name=faq value=y> ЧаВо</tr></td>";
echo "<tr><td>
Заголовок: <input type=text name=header value=''></input><br>
</td></tr>";

echo "<tr><td>";

echo "<select name=inject_id onchange=ch_inject_id()><option value=>ВЫБЕРИТЕ ВНЕШНЮЮ ФОРМУ</option>";
$q=OCIParse($c,"select * from sc_injects where project_id='".$_SESSION['project']['id']."' and deleted is null order by name");
OCIExecute($q,OCI_DEFAULT);
echo "<optgroup label='ВНЕШНИЕ ФОРМЫ ПРОЕКТА'>";
while (OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
}
echo "</optgroup>";

if($_SESSION['admin']==1) {
	$q=OCIParse($c,"select * from sc_injects where project_id='0' and id>0 and deleted is null order by name");
	OCIExecute($q,OCI_DEFAULT);	
	echo "<optgroup label='ОБЩИЕ ВНЕШНИЕ ФОРМЫ'>";	
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</optgroup>";
}
echo "</select>";
echo "</td></tr>";
echo "</table>";

echo "<select name=form_id onchange=ch_form_id()><option value=>ВЫБЕРИТЕ ФОРМУ</option>";
$q=OCIParse($c,"select * from sc_forms where project_id='".$_SESSION['project']['id']."' and deleted is null order by name");
OCIExecute($q,OCI_DEFAULT);
echo "<optgroup label='ФОРМЫ ПРОЕКТА'>";
while (OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
}
echo "</optgroup>";

if($_SESSION['admin']==1) {
	$q=OCIParse($c,"select * from sc_forms where project_id='0' and id>0 and deleted is null order by name");
	OCIExecute($q,OCI_DEFAULT);	
	echo "<optgroup label='ОБЩИЕ ФОРМЫ'>";	
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</optgroup>";
}
echo "</select>";

echo "<select name=forw_list_id onchange=ch_forw_list_id()><option value=>ВЫБЕРИТЕ СПИСОК ПЕРЕАДРЕСАЦИИ</option>";
$q=OCIParse($c,"select * from sc_forw_list where project_id='".$_SESSION['project']['id']."' /*and id not in (select forw_list_id from sc_body
where forw_list_id is not null and (punkt_id = '".$punkt_id."' or general='y'))*/");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
echo "</select>";

echo "<select name=file_name onchange=ch_file_name()><option>Выберите файл</option>";
echo "<optgroup label='ФАЙЛЫ ПРОЕКТА'>";
foreach (glob($path_to_folders.$_SESSION['project']['name']."\\*.*") as $filename) {
    if(filetype($filename)=='file') echo "<option value=\"".basename($filename)."\">".basename($filename)."</option>";
}
echo "</optgroup>";
echo "<optgroup label='ОБЩИЕ ФАЙЛЫ'>";
foreach (glob($path_to_folders."*.*") as $filename) {
    if(filetype($filename)=='file') echo "<option value=\"..\\".basename($filename)."\">".basename($filename)."</option>";
}
echo "</optgroup>";
echo "</select>";
echo "<div id=div_out_prefix>исходящий префикс: <input type=text name=out_prefix></input></div>";
echo "<br><br><br>";
echo "<input type=submit name=add_blog_go value=СОХРАНИТЬ>";
echo "</td>
<td>
<input type=text disabled size=2 value=\"<b>\"><b>текст</b><input type=text disabled size=2 value=\"</b>\"> - жирный шрифт<br>
<input type=text disabled size=2 value=\"<i>\"><i>текст</i><input type=text disabled size=2 value=\"</i>\"> - курсив<br>
<input type=text disabled size=2 value=\"<h4>\">текст<input type=text disabled size=2 value=\"</h4>\"> - маленький заголовок<br>
<input type=text disabled size=13 value=\"<font color=red>\"><font color=red>текст</font><input type=text disabled size=2 value=\"</font>\"> - красный шрифт<br>
<input type=text disabled size=2 value=\"<p>\"> - новый абзац<br>
<input type=text disabled size=2 value=\"<hr>\"> - горизонтальная черта<br>
<input type=text disabled size=35 value=\"<a target=blank href=http://www.ya.ru>\">текст<input type=text disabled size=2 value=\"</a>\"> - ссылка на сайт www.ya.ru<br>
<input type=text disabled size=35 value=\"<a name=a1></a>\"> - якорь<br>
<input type=text disabled size=35 value=\"<a href=#a1>\">текст<input type=text disabled size=2 value=\"</a>\"> - ссылка на якорь<br>
</td></tr></table>";
echo "<table id=myTable>
<tr><td><font size=4 id=h1>ВОПРОС</font></td><td><font size=4 id=h2></font></td></tr>
<tr><td colspan=2>
<table>
<tr><td rowspan=2>
Размер:
нет<input type=radio name=font_size value=''>
<font size=1>1</font><input type=radio name=font_size value=1>
<font size=2>2</font><input type=radio name=font_size value=2>
<font size=3>3</font><input type=radio name=font_size value=3>
<font size=4>4</font><input type=radio name=font_size value=4>
<font size=5>5</font><input type=radio name=font_size value=5>
<font size=6>6</font><input type=radio name=font_size value=6>
<font size=7>7</font><input type=radio name=font_size value=7>
</td><td colspan=13 align=center>Выравнивание по центру:<input type=checkbox name=align value=center>
</td></tr>
<tr>
<td>
<b>Ж</b><input type=checkbox name=b> 
<i>К</i><input type=checkbox name=i> 
<u>Ч</u><input type=checkbox name=u> 
</td>
<td>
Цвет:
<input type=radio name=color value=''></td>
<td bgcolor=#000000><input type=radio name=color value=#000000></td>
<td bgcolor=#828282><input type=radio name=color value=#828282></td>
<td bgcolor=#FF0000><input type=radio name=color value=#FF0000></td>
<td bgcolor=#228B22><input type=radio name=color value=#228B22></td>
<td bgcolor=#0000FF><input type=radio name=color value=#0000FF></td>
<td bgcolor=#A020F0><input type=radio name=color value=#A020F0></td>
<td bgcolor=#FF00FF><input type=radio name=color value=#FF00FF></td>
<td bgcolor=#FFA500><input type=radio name=color value=#FFA500></td>
<td bgcolor=#333333><input type=radio name=color value=#333333></td>
<td bgcolor=#003366><input type=radio name=color value=#003366></td>
<td bgcolor=#006666><input type=radio name=color value=#006666></td>
</tr>
</table>

</td></tr>
<tr>
<td><textarea name=quest rows=20 cols=30 ></textarea></td>
<td><textarea name=answer rows=20 cols=70 ></textarea></td></tr>
</table>
<input type=hidden name=punkt_id value=".$punkt_id.">
<input type=hidden name=tree_id value=".$tree_id.">
<input type=hidden name=ordering value=".$ordering.">";
echo "</form>";
echo "<script language='javascript'>
ch_blog_type();
function ch_blog_type() {
	if (document.all.blog_type.value=='DT') {
	document.location='edit_table.php?add_blog=1&blog_type=DT&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."';
	}
	if (document.all.blog_type.value=='TA') {
	document.all.h2.innerText='ОТВЕТ';
	document.all.colapsed.checked=false;
	document.all.answer.cols=70;
	document.all.add_blog_go.disabled=false;
	document.all.tbl1.rows[0].cells[1].style.display='';
	document.all.tbl2.rows[4].style.display='none';
	document.all.tbl2.rows[5].style.display='none';		
	document.all.form_id.style.display='none';
	document.all.myTable.style.display='';
	document.all.myTable.rows[0].cells[0].style.display='';	
	document.all.myTable.rows[1].style.display='none';	
	document.all.myTable.rows[2].cells[0].style.display='';	
	document.all.file_name.style.display='none';
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='none';	
	document.all.tbl2.rows[7].style.display='none';	
	document.all.tbl2.rows[8].style.display='none';	
	document.getElementById('div_out_prefix').style.display='none';
	}
	if (document.all.blog_type.value=='TE') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=false;	
	document.all.answer.cols=100;
	document.all.add_blog_go.disabled=false;
	document.all.tbl1.rows[0].cells[1].style.display='';
	document.all.tbl2.rows[4].style.display='none';
	document.all.tbl2.rows[5].style.display='none';	
	document.all.form_id.style.display='none';
	document.all.myTable.style.display='';
	document.all.myTable.rows[0].cells[0].style.display='none';
	document.all.myTable.rows[1].style.display='';	
	document.all.myTable.rows[2].cells[0].style.display='none';
	document.all.file_name.style.display='none';
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='none';
	document.all.tbl2.rows[7].style.display='none';
	document.all.tbl2.rows[8].style.display='none';	
	document.getElementById('div_out_prefix').style.display='none';	
	}
	if (document.all.blog_type.value=='FO') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=true;	
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.tbl2.rows[4].style.display='';
	document.all.tbl2.rows[5].style.display='';		
	document.all.form_id.style.display='';
	document.all.add_blog_go.disabled=true;
	document.all.myTable.style.display='none';
	document.all.file_name.style.display='none';	
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='';	
	document.all.tbl2.rows[7].style.display='';
	document.all.tbl2.rows[8].style.display='none';
	document.getElementById('div_out_prefix').style.display='none';
	}
	if (document.all.blog_type.value=='FV') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=true;	
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.tbl2.rows[4].style.display='';
	document.all.tbl2.rows[5].style.display='none';		
	document.all.form_id.style.display='none';
	document.all.add_blog_go.disabled=true;
	document.all.myTable.style.display='none';
	document.all.file_name.style.display='none';	
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='none';	
	document.all.tbl2.rows[7].style.display='';
	document.all.tbl2.rows[8].style.display='';
	document.getElementById('div_out_prefix').style.display='none';
	}	
	if (document.all.blog_type.value=='FI') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=false;	
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.tbl2.rows[4].style.display='none';	
	document.all.tbl2.rows[5].style.display='none';	
	document.all.form_id.style.display='';
	document.all.add_blog_go.disabled=true;
	document.all.myTable.style.display='none';
	document.all.file_name.style.display='';	
	document.all.form_id.style.display='none';
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='none';
	document.all.tbl2.rows[7].style.display='none';
	document.all.tbl2.rows[8].style.display='none';
	document.getElementById('div_out_prefix').style.display='none';	
	}
	if (document.all.blog_type.value=='LI') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=false;	
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.tbl2.rows[4].style.display='';
	document.all.tbl2.rows[5].style.display='none';		
	document.all.form_id.style.display='none';
	document.all.add_blog_go.disabled=true;
	document.all.myTable.style.display='none';
	document.all.file_name.style.display='none';	
	document.all.forw_list_id.style.display='';
	document.all.tbl2.rows[6].style.display='none';
	document.all.tbl2.rows[7].style.display='none';
	document.all.tbl2.rows[8].style.display='none';
	document.getElementById('div_out_prefix').style.display='none';	
	}
	if (document.all.blog_type.value=='OU') {
	document.all.h2.innerText='';
	document.all.colapsed.checked=false;	
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.tbl2.rows[4].style.display='none';	
	document.all.tbl2.rows[5].style.display='none';	
	document.all.form_id.style.display='';
	document.all.add_blog_go.disabled=false;
	document.all.myTable.style.display='none';
	document.all.file_name.style.display='none';	
	document.all.form_id.style.display='none';
	document.all.forw_list_id.style.display='none';
	document.all.tbl2.rows[6].style.display='none';
	document.all.tbl2.rows[7].style.display='none';
	document.all.tbl2.rows[8].style.display='none';
	document.getElementById('div_out_prefix').style.display='';		
	}
}
function ch_form_id() {
	if (document.all.form_id.value=='') {
	document.all.add_blog_go.disabled=true;
	}
	else {
	document.all.add_blog_go.disabled=false;
	}
}
function ch_inject_id() {
	if (document.all.inject_id.value=='') {
	document.all.add_blog_go.disabled=true;
	}
	else {
	document.all.add_blog_go.disabled=false;
	}
}
function ch_file_name() {
	if (document.all.file_name.value=='') {
	document.all.add_blog_go.disabled=true;
	}
	else {
	document.all.add_blog_go.disabled=false;
	}
}
function ch_forw_list_id() {
	if (document.all.forw_list_id.value=='') {
	document.all.add_blog_go.disabled=true;
	}
	else {
	document.all.add_blog_go.disabled=false;
	}
}
</script>";
}

if (isset($add_blog_go)) {
$txt_tag_before='';
$txt_tag_after='';
$txt_align='';
if (!isset($colapsed)) $colapsed='';
if (!isset($new_window)) $new_window='';
if (!isset($faq)) $faq='';
if (!isset($form_id)) $form_id="";
		if ($general=='y') {
			$where_punkt_id="";
			$new_punkt_id="";
			} else {
			$new_punkt_id=$punkt_id;
				if ($punkt_id=='') {
				$where_punkt_id=" and punkt_id is null";
				} else {
				$where_punkt_id=" and punkt_id='".$punkt_id."'";
				}
			}
	$upd=OCIParse($c,"update sc_body set ordering=ordering+1
		where project_id='".$_SESSION['project']['id']."'
		".$where_punkt_id."
		and general='".$general."'
		and ordering>'".$ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	if ($general=='n') {
		$upd2=OCIParse($c,"update sc_punkt set with_blog=1 where id='".$punkt_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		}
	if ($blog_type=='FI') $quest=$file_name;
	if ($blog_type=='TE') {
		if ((isset($font_size) and $font_size<>'') or (isset($color) and $color<>'')) {
		$txt_tag_before.="<font"; 
		if(isset($color) and $color<>'') $txt_tag_before.=" color=".$color;
		if(isset($font_size) and $font_size<>'') $txt_tag_before.=" size=".$font_size;
		$txt_tag_before.=">";
		$txt_tag_after="</font>".$txt_tag_after;
		}
	if (isset($b)) {$txt_tag_before.="<b>"; $txt_tag_after="</b>".$txt_tag_after;}		
	if (isset($i)) {$txt_tag_before.="<i>"; $txt_tag_after="</i>".$txt_tag_after;}		
	if (isset($u)) {$txt_tag_before.="<u>"; $txt_tag_after="</u>".$txt_tag_after;}		
	if (isset($align) and $align<>'') $txt_align=" align=".$align;
	}
	if ($blog_type=='FO') {
	$forw_list_id='';
	$forw_list_id='';
	$inject_id='';
	}
	if ($blog_type=='LI') {
	$form_id='';
	$inject_id='';
	}
	if($blog_type=='FV') {
		$quest=$header;
		$form_id='';
		$forw_list_id='';
	}
	if($blog_type=='FV' or $blog_type=='FO') {
		$quest=$header;
	}
	$ins=OCIParse($c,"insert into sc_body (id,head,body,type,ordering,project_id,punkt_id,general,form_id,shedule_id,txt_tag_before,txt_tag_after,txt_align,forw_list_id,colapsed,new_window,faq,out_prefix,inject_id) 
	values ((select nvl(max(id),0)+1 from sc_body),'".$quest."',:body,'".$blog_type."',".$ordering."+1,'".$_SESSION['project']['id']."','".$new_punkt_id."','".$general."','".$form_id."','".$shedule_id."','".$txt_tag_before."','".$txt_tag_after."','".$txt_align."','".$forw_list_id."','".$colapsed."','".$new_window."','".$faq."','".$out_prefix."','".$inject_id."') returning id into :new_id");
	if ($answer=='') $answer=' ';
	OCIBindByName($ins,":body",$answer);
	OCIBindByName($ins,":new_id",$new_id,128);
	OCIExecute($ins,OCI_DEFAULT);
	
	if(isset($cgpns) and count($cgpns)<$cgpns_count) {
	$ins2=OCIParse($c,"insert into SC_BODY_CGPN (cgpn,body_id) values (:cgpn,'".$new_id."')");
		foreach($cgpns as $val) {
		OCIBindByName($ins2,":cgpn",$val);
		OCIExecute($ins2,OCI_DEFAULT);
		}
	}
	if(isset($directions) and count($directions)<3) {
		$ins5=OCIParse($c,"insert into SC_BODY_DIRECTIONS (direction,body_id) values (:direction,'".$new_id."')");
			foreach($directions as $key=>$val) {
			OCIBindByName($ins5,":direction",$key);
			OCIExecute($ins5,OCI_DEFAULT);
		}
	}		
	
	OCICommit($c);	

echo "<script language='javascript'>
document.location='body.php?punkt_id=".$punkt_id."&tree_id=".$tree_id."';
</script>";

}

if (isset($del_blog)) {
echo "<form name=frm action=edit_body.php method=post>";
echo "<font color=red><h4>Действительно удалить блок?</h4></font>";
echo "<input type=hidden name=blog_id value=".$blog_id.">
<input type=hidden name=punkt_id value=".$punkt_id.">
<input type=hidden name=tree_id value=".$tree_id."><br>";
echo "<input type=button name=cancel onclick=canc() value=ОТМЕНА>
<input type=submit name=del_blog_go value=ОК><br>";
echo "</form>";
echo "<script language=jscript>
function canc() {
document.location='body.php?punkt_id=".$punkt_id."&tree_id=".$tree_id."#".$blog_id."';
}
</script>";
}

if (isset($del_blog_go)) {
	$q=OCIParse($c,"select count(*) cnt from sc_body where punkt_id='".$punkt_id."' and general='n' and invisible is null");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
		if (OCIResult($q,"CNT")<=1) {
		$upd=OCIParse($c,"update sc_punkt set with_blog=null where id='".$punkt_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		}
	$del=OCIParse($c,"delete from sc_body where id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);

echo "<script language='javascript'>
document.location='body.php?punkt_id=".$punkt_id."&tree_id=".$tree_id."';
</script>";

}

if (isset($edit_blog)) {
echo "<form name=frm action=edit_body.php method=post>
<table id=tbl1><tr><td>
<h4>Редактирование блока:</h4>
<table><tr>
<td>расписание: </td><td><select name=shedule_id>";

$q=OCIParse($c,"select b.shedule_id, s.name from sc_body b, sc_shedule s 
where b.id='".$blog_id."' and b.project_id='".$_SESSION['project']['id']."'
and b.shedule_id=s.id");
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
echo "</select></td></tr>";

echo "<td valign=top>Номера доступа: </td><td>";

$q=OCIParse($c,"select count(*) cnt from SC_BODY_CGPN where body_id='".$blog_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
if(OCIResult($q,"CNT")>0) {
	$q=OCIParse($c,"select p.phone,decode(c.cgpn,null,null,'checked ') chk from SC_PHONES p,
(select * from SC_BODY_CGPN where body_id='".$blog_id."') c 
where project_id='".$_SESSION['project']['id']."' 
and p.phone=c.cgpn(+)
order by p.phone");
}
else {
	$q=OCIParse($c,"select p.phone,'checked ' chk from SC_PHONES p 
where project_id='".$_SESSION['project']['id']."' 
order by p.phone");
	}

OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
	$i++;
	echo "<input type=checkbox name=cgpns[$i] ".OCIResult($q,"CHK")."value=".OCIResult($q,"PHONE").">".OCIResult($q,"PHONE")."</input>";
	if(($i/2)==round($i/2)) echo "<br>";
	else echo " | ";
	}
echo "<input type=hidden name=cgpns_count value='$i'></td></tr>";

/*echo "<td valign=top>АОНы: </td><td>";
	$q=OCIParse($c,"select aon from SC_BODY_AONS where body_id='".$blog_id."'
order by aon");

OCIExecute($q,OCI_DEFAULT);
	$AONs="";
	while (OCIFetch($q)) {
		$AONs.=OCIResult($q,"AON").",";
	}
echo "<input type=text name=aons value='".trim($AONs,",")."'></input> несколько АОНов указывать через запятую";
echo "</td></tr>";*/

//----------------------------

echo "<tr><td valign=top>Направления: </td><td>";

$q=OCIParse($c,"select direction from SC_BODY_DIRECTIONS where body_id='".$blog_id."'");
$directions=array();
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$directions[OCIResult($q,"DIRECTION")]='y';
}
if(count($directions)==0) {
	$directions['in']='y';
	$directions['out']='y';
	$directions['callback']='y';
}
echo "<input type=checkbox name=directions[in] value=y".(isset($directions['in'])?" checked":"").">Входящие</input>";
echo " | ";
echo "<input type=checkbox name=directions[callback] value=y".(isset($directions['callback'])?" checked":"").">Автоперезвоны</input>";
echo " | ";
echo "<input type=checkbox name=directions[out] value=y".(isset($directions['out'])?" checked":"").">Исходящие</input>";
echo "</td></tr>";

//-----------------------------------

echo "<td valign=top>АОНы: </td><td>";
	$q=OCIParse($c,"select aon from SC_BODY_AONS where body_id='".$blog_id."'
order by aon");

OCIExecute($q,OCI_DEFAULT);
	$AONs="";
	while (OCIFetch($q)) {
		$AONs.=OCIResult($q,"AON").",";
	}
echo "<input type=text name=aons value='".trim($AONs,",")."'></input> несколько АОНов указывать через запятую";
echo "</td></tr>";

echo "<td valign=top>Тональный набор: </td><td>";
	$q=OCIParse($c,"select tonedial from SC_BODY_TONEDIAL where body_id='".$blog_id."'");

OCIExecute($q,OCI_DEFAULT);
	$tonedials="";
	while (OCIFetch($q)) {
		$tonedials.=OCIResult($q,"TONEDIAL").",";
	}
echo "<input type=text name=tonedials value='".trim($tonedials,",")."'></input> \"null\" - ничего не набрано, несколько значений указывать через запятую";
echo "</td></tr>";

echo "<tr><td>тип: </td><td><select name=blog_type onchange=ch_blog_type()>";
	if ($blog_type=='TA' or $blog_type=='TE') {
	$q=OCIParse($c,"select * from sc_blog_type where id='".$blog_type."'
	union all
	select * from sc_blog_type where id='TA' or id='TE'");
	OCIExecute($q,OCI_DEFAULT);
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
	}
	else {
	if ($blog_type=='FO') echo "<option value='FO'>Форма</option>";
	if ($blog_type=='FI') echo "<option value='FI'>Файл HTML</option>";
	if ($blog_type=='LI') echo "<option value='LI'>Список переадресации</option>";
	if ($blog_type=='OU') echo "<option value='OU'>Исходящий звонок</option>";
	if ($blog_type=='FV' and $_SESSION['admin']==1) echo "<option value='FV'>Внешняя форма</option>";
	}		
echo "</select></td></tr></table>";
echo "<input type=hidden name=old_general value=".$general.">";
echo "<input type=hidden name=general value=n>";
echo "<input type=checkbox"; if ($general=='y') echo " checked"; else {} echo " name=general value=y> Общий для всех пунктов<br>";
if ($blog_type=='TA' or $blog_type=='TE') {echo "<input type=checkbox"; if (isset($faq) and $faq=='y') echo " checked"; else {} echo " name=faq value=y> ЧаВо<br>";}
if ($blog_type=='FO' or $blog_type=='LI') {
echo "<input type=checkbox"; if ($colapsed=='y') echo " checked"; else {} echo " name=colapsed value=y> Свернуто<br>";
	if ($blog_type=='FO') {
	echo "<input type=checkbox"; if ($new_window=='y') echo " checked"; else {} echo " name=new_window value=y> Открывать в отдельном окне<br>";
	}
}
$q=OCIParse($c,"select * from sc_body where id='".$blog_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);

$font_size=substr(OCIResult($q,"TXT_TAG_BEFORE"),strpos(OCIResult($q,"TXT_TAG_BEFORE"),"size=")+5,1);
$b=strpos(OCIResult($q,"TXT_TAG_BEFORE"),"<b>");
$i=strpos(OCIResult($q,"TXT_TAG_BEFORE"),"<i>");
$u=strpos(OCIResult($q,"TXT_TAG_BEFORE"),"<u>");
$color=substr(OCIResult($q,"TXT_TAG_BEFORE"),strpos(OCIResult($q,"TXT_TAG_BEFORE"),"color=")+6,7);
$align=substr(OCIResult($q,"TXT_ALIGN"),strpos(OCIResult($q,"TXT_ALIGN"),"align=")+6);
if ($blog_type=='FO' or $blog_type=='FV') {
	echo "Заголовок: <input type=text name=header value='".OCIResult($q,"HEAD")."'></input><br>";
}
echo "<br><br><input type=submit name=edit_blog_go value=СОХРАНИТЬ>";
echo "</td>
<td>
<input type=text disabled size=2 value=\"<b>\"><b>текст</b><input type=text disabled size=2 value=\"</b>\"> - жирный шрифт<br>
<input type=text disabled size=2 value=\"<i>\"><i>текст</i><input type=text disabled size=2 value=\"</i>\"> - курсив<br>
<input type=text disabled size=2 value=\"<h4>\">текст<input type=text disabled size=2 value=\"</h4>\"> - маленький заголовок<br>
<input type=text disabled size=13 value=\"<font color=red>\"><font color=red>текст</font><input type=text disabled size=2 value=\"</font>\"> - красный шрифт<br>
<input type=text disabled size=2 value=\"<p>\"> - новый абзац<br>
<input type=text disabled size=2 value=\"<hr>\"> - горизонтальная черта<br>
<input type=text disabled size=35 value=\"<a target=blank href=http://www.ya.ru>\">текст<input type=text disabled size=2 value=\"</a>\"> - ссылка на сайт www.ya.ru<br>
<input type=text disabled size=35 value=\"<a name=a1></a>\"> - якорь<br>
<input type=text disabled size=35 value=\"<a href=#a1>\">текст<input type=text disabled size=2 value=\"</a>\"> - ссылка на якорь<br>
</td></tr></table>";

echo "<table id=myTable>
<tr><td><font size=4 id=h1>ВОПРОС</font></td><td><font size=4 id=h2></font></td></tr>
<tr><td colspan=2>
<table>
<tr><td rowspan=2>
Размер:";
echo "нет<input type=radio name=font_size value=''>";
echo "<font size=1>1</font><input type=radio name=font_size value=1 "; if ($font_size=='1') echo"checked";  echo ">";
echo "<font size=2>2</font><input type=radio name=font_size value=2 "; if ($font_size=='2') echo"checked";  echo ">";
echo "<font size=3>3</font><input type=radio name=font_size value=3 "; if ($font_size=='3') echo"checked";  echo ">";
echo "<font size=4>4</font><input type=radio name=font_size value=4 "; if ($font_size=='4') echo"checked";  echo ">";
echo "<font size=5>5</font><input type=radio name=font_size value=5 "; if ($font_size=='5') echo"checked";  echo ">";
echo "<font size=6>6</font><input type=radio name=font_size value=6 "; if ($font_size=='6') echo"checked";  echo ">";
echo "<font size=7>7</font><input type=radio name=font_size value=7 "; if ($font_size=='7') echo"checked";  echo ">";
echo "</td><td colspan=13 align=center>Выравнивание по центру:<input type=checkbox name=align value=center "; if ($align=='center') echo"checked";  echo ">";
echo "</td></tr>
<tr>
<td>";
echo "<b>Ж</b><input type=checkbox name=b "; if ($b<>'') echo"checked";  echo ">"; 
echo "<i>К</i><input type=checkbox name=i "; if ($i<>'') echo"checked";  echo ">"; 
echo "<u>Ч</u><input type=checkbox name=u "; if ($u<>'') echo"checked";  echo ">"; 
echo "</td>
<td>
Цвет:";
echo "<input type=radio name=color value=''></td>";
echo "<td bgcolor=#000000><input type=radio name=color value=#000000 "; if ($color=='#000000') echo"checked";  echo "></td>";
echo "<td bgcolor=#828282><input type=radio name=color value=#828282 "; if ($color=='#828282') echo"checked";  echo "></td>";
echo "<td bgcolor=#FF0000><input type=radio name=color value=#FF0000 "; if ($color=='#FF0000') echo"checked";  echo "></td>";
echo "<td bgcolor=#228B22><input type=radio name=color value=#228B22 "; if ($color=='#228B22') echo"checked";  echo "></td>";
echo "<td bgcolor=#0000FF><input type=radio name=color value=#0000FF "; if ($color=='#0000FF') echo"checked";  echo "></td>";
echo "<td bgcolor=#A020FO><input type=radio name=color value=#A020F0 "; if ($color=='#A020F0') echo"checked";  echo "></td>";
echo "<td bgcolor=#FF00FF><input type=radio name=color value=#FF00FF "; if ($color=='#FF00FF') echo"checked";  echo "></td>";
echo "<td bgcolor=#FFA500><input type=radio name=color value=#FFA500 "; if ($color=='#FFA500') echo"checked";  echo "></td>";
echo "<td bgcolor=#333333><input type=radio name=color value=#333333 "; if ($color=='#333333') echo"checked";  echo "></td>";
echo "<td bgcolor=#003366><input type=radio name=color value=#003366 "; if ($color=='#003366') echo"checked";  echo "></td>";
echo "<td bgcolor=#006666><input type=radio name=color value=#006666 "; if ($color=='#006666') echo"checked";  echo "></td>";
echo "</tr>
</table>

</td></tr>
<tr>
<td><textarea name=quest rows=20 cols=30 >".OCIResult($q,"HEAD")."</textarea></td>
<td><textarea name=answer rows=20 cols=70 >".OCIResult($q,"BODY")->load()."</textarea></td></tr>
</table>
<input type=hidden name=invisible value=".OCIResult($q,"INVISIBLE").">
<input type=hidden name=ordering value=".$ordering.">
<input type=hidden name=punkt_id value=".$punkt_id.">
<input type=hidden name=tree_id value=".$tree_id.">
<input type=hidden name=blog_id value=".$blog_id.">";
echo "</form>";
echo "<script language='javascript'>
ch_blog_type();
function ch_blog_type() {
	if (document.all.blog_type.value=='TA') {
	document.all.h2.innerText='ОТВЕТ';
	document.all.answer.cols=70;
	document.all.myTable.rows[0].cells[0].style.display='';	
	document.all.myTable.rows[1].style.display='none';
	document.all.myTable.rows[2].cells[0].style.display='';	
	}
	else {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.myTable.rows[0].cells[0].style.display='none';
	document.all.myTable.rows[1].style.display='';	
	document.all.myTable.rows[2].cells[0].style.display='none';
	}
	if (document.all.blog_type.value=='FO') {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.myTable.style.display='none';
	}
	if (document.all.blog_type.value=='FV') {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.myTable.style.display='none';
	}	
	if (document.all.blog_type.value=='FI') {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.myTable.style.display='none';
	}	
	if (document.all.blog_type.value=='LI') {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.myTable.style.display='none';
	}	
	if (document.all.blog_type.value=='OU') {
	document.all.h2.innerText='';
	document.all.answer.cols=100;
	document.all.tbl1.rows[0].cells[1].style.display='none';
	document.all.myTable.style.display='none';
	}	
}
</script>";
}

if (isset($edit_blog_go)) {
	$txt_tag_before='';
	$txt_tag_after='';
	$txt_align='';
	if (!isset($colapsed)) $colapsed='';	
	if (!isset($new_window)) $new_window='';
	if (!isset($faq)) $faq='';
	if ($general=='y') {
	$new_punkt_id="";
	$q=OCIParse($c,"select count(*) cnt from sc_body where punkt_id='".$punkt_id."' and general='n' and invisible is null");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
		if (OCIResult($q,"CNT")<=1) {
		$upd=OCIParse($c,"update sc_punkt set with_blog=null where id='".$punkt_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		}
		if ($old_general<>$general) {
		$q=OCIParse($c,"select nvl(max(ordering),0)+1 new_ordering from sc_body where project_id='".$_SESSION['project']['id']."' and general='".$general."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$new_ordering=OCIResult($q,"NEW_ORDERING");
		}
		else {
		$new_ordering=$ordering;
		}
	}
	else {
		if ($old_general<>$general) {
		$q=OCIParse($c,"select nvl(min(ordering),0)-1 new_ordering from sc_body where punkt_id='".$punkt_id."' and general='".$general."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$new_ordering=OCIResult($q,"NEW_ORDERING");
		}
		else {
		$new_ordering=$ordering;
		}	
	$new_punkt_id=$punkt_id;
		if ($invisible<>'1') {
		$upd2=OCIParse($c,"update sc_punkt set with_blog=1 where id='".$punkt_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		}
	}
	if ($blog_type=='TE') {
		if ((isset($font_size) and $font_size<>'') or (isset($color) and $color<>'')) {
		$txt_tag_before.="<font"; 
		if(isset($color) and $color<>'') $txt_tag_before.=" color=".$color;
		if(isset($font_size) and $font_size<>'') $txt_tag_before.=" size=".$font_size;
		$txt_tag_before.=">";
		$txt_tag_after="</font>".$txt_tag_after;
		}
	if (isset($b)) {$txt_tag_before.="<b>"; $txt_tag_after="</b>".$txt_tag_after;}		
	if (isset($i)) {$txt_tag_before.="<i>"; $txt_tag_after="</i>".$txt_tag_after;}		
	if (isset($u)) {$txt_tag_before.="<u>"; $txt_tag_after="</u>".$txt_tag_after;}		
	if (isset($align) and $align<>'') $txt_align=" align=".$align;
	}
	if($blog_type=='FV' or $blog_type=='FO') {
		$quest=$header;
	}
	$upd=OCIParse($c,"update sc_body set 
	head='".$quest."',
	body=:body,
	type='".$blog_type."',
	punkt_id='".$new_punkt_id."',
	general='".$general."',
	ordering='".$new_ordering."',
	shedule_id='".$shedule_id."',
	txt_tag_before='".$txt_tag_before."',
	txt_tag_after='".$txt_tag_after."',
	txt_align='".$txt_align."',
	colapsed='".$colapsed."',
	new_window='".$new_window."',
	faq='".$faq."'
	where id='".$blog_id."'");
	if ($answer=='') $answer=' ';
	OCIBindByName($upd,":body",$answer);
	OCIExecute($upd,OCI_DEFAULT);
	
	/*$del=OCIParse($c,"delete from SC_BODY_CGPN where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if(isset($cgpns) and count($cgpns)<$cgpns_count) {
	$ins=OCIParse($c,"insert into SC_BODY_CGPN (cgpn,body_id) values (:cgpn,'".$blog_id."')");
		foreach($cgpns as $val) {
		OCIBindByName($ins,":cgpn",$val);
		OCIExecute($ins,OCI_DEFAULT);
		}
	}*/
	
	$del=OCIParse($c,"delete from SC_BODY_CGPN where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);

	if(isset($cgpns) and count($cgpns)<$cgpns_count) {
		$ins2=OCIParse($c,"insert into SC_BODY_CGPN (cgpn,body_id) values (:cgpn,'".$blog_id."')");
			foreach($cgpns as $val) {
			$val=trim($val);
			OCIBindByName($ins2,":cgpn",$val);
			OCIExecute($ins2,OCI_DEFAULT);
		}
	}

//------------------------

	$del=OCIParse($c,"delete from SC_BODY_DIRECTIONS where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if(isset($directions) and count($directions)<3) {
		$ins5=OCIParse($c,"insert into SC_BODY_DIRECTIONS (direction,body_id) values (:direction,'".$blog_id."')");
			foreach($directions as $key=>$val) {
			OCIBindByName($ins5,":direction",$key);
			OCIExecute($ins5,OCI_DEFAULT);
		}
	}	
	
//------------------------

	$del=OCIParse($c,"delete from SC_BODY_AONS where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if($aons<>'') {
		$aons_arr=explode(",",$aons);
		foreach($aons_arr as $val) {
			$val=trim($val);
			$ins3=OCIParse($c,"insert into SC_BODY_AONS (aon,body_id) values (:aons,'$blog_id')");
			OCIBindByName($ins3,":aons",$val);
			OCIExecute($ins3,OCI_DEFAULT);
		}
	}
	$del=OCIParse($c,"delete from SC_BODY_TONEDIAL where body_id='".$blog_id."'");
	OCIExecute($del,OCI_DEFAULT);
	
	if($tonedials<>'') {
		$td_arr=explode(",",$tonedials);
		foreach($td_arr as $val) {
			$val=trim($val);
			$ins4=OCIParse($c,"insert into SC_BODY_TONEDIAL (tonedial,body_id) values (:tonedials,'$blog_id')");
			OCIBindByName($ins4,":tonedials",$val);
			OCIExecute($ins4,OCI_DEFAULT);
		}
	}		
	
	OCICommit($c);
	echo $general;

echo "<script language='javascript'>
document.location='body.php?punkt_id=".$punkt_id."&tree_id=".$tree_id."#".$blog_id."';
</script>";

}

?>
</table></tr></td>
</body>
</html>