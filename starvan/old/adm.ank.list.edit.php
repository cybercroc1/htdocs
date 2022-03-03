<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="8">	
<script src="adm.ank.list.edit.js"></script>
<?php 

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_ank']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

$project_id=$_SESSION['adm']['project']['id'];
if(!isset($obj_id) or $obj_id=='') exit();

echo "<form name=frm method=post action=adm.ank.list.save.php target='logFrame'>";	

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

echo " | ";
echo "<font size=4>Список ответов</font> | ";
echo "<textarea id=buffer onpaste=this.value='' rows=1 cols=15>буфер обмена</textarea> | ";
echo "<font align=right><a href='help.adm.ank.list.edit.html' target='_blank'>Справка</a></font>";
echo "<hr>";

include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");

echo "<input type=hidden name=obj_id value='".$obj_id."'>";

$q=OCIParse($c,"select t.name||' (id:'||o.id||'); '||f.text_name||'; '||f.code_name obj_name, o.obj_type_id,o.num,o.impact_on_field,o.depend_of_field,o.quote_num from STC_OBJECTS o, STC_LI_OBJECT_TYPE t, STC_FIELDS f
where o.id=".$obj_id."
and t.id=o.obj_type_id
and f.id=o.field_id");
OCIExecute($q);
OCIFetch($q);
$object_name=OCIResult($q,"OBJ_NAME");
$obj_type_id=OCIResult($q,"OBJ_TYPE_ID");
$obj_num=OCIResult($q,"NUM");
$impact_on_field=OCIResult($q,"IMPACT_ON_FIELD");
$depend_of_field=OCIResult($q,"DEPEND_OF_FIELD");
if(OCIResult($q,"QUOTE_NUM")<>'') $quote_num=OCIResult($q,"QUOTE_NUM");

echo "<font size=3><b>".$object_name."</b></font><hr>";
if($obj_type_id=='q_ls_select' or $obj_type_id=='q_ls_radio') {
	//влияет на
	echo "Влияет на: ";
	$q=OCIParse($c,"select f.id,f.text_name from STC_FIELDS f
	where f.project_id=".$project_id." and (f.src_type_id=1 and f.uniq is null and f.deleted is null)
	or (f.id='".$impact_on_field."')
	order by f.text_name");
	OCIExecute($q);
	echo "<select name=impact_on_field><option></option>";
	echo "<optgroup label='Исходное поле'>";
	while(OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").($impact_on_field==OCIResult($q,"ID")?' selected':NULL).">".OCIResult($q,"TEXT_NAME")."</option>";
	}
	echo "</optgroup></select>";
	echo "<hr>";
}



//Зависит от
echo "Зависит от: ";
$q=OCIParse($c,"select f.id,f.text_name from STC_FIELDS f
where f.project_id=".$project_id." and f.src_type_id=1 and (f.quoted is not null or f.idx is not null) and f.deleted is null
order by f.text_name");
OCIExecute($q);
echo "<select name=depend_of_field><option></option>";
echo "<optgroup label='Исходного поля'>";
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").($depend_of_field==OCIResult($q,"ID")?' selected':NULL).">".OCIResult($q,"TEXT_NAME")."</option>";
}
echo "</optgroup>";
$q=OCIParse($c,"select f.id,o.quest_num||'. '||f.text_name text_name from STC_OBJECTS o, STC_FIELDS f
where o.project_id=".$project_id." and o.num<".$obj_num." and o.deleted is null
and f.project_id=".$project_id." and f.src_type_id=2 and f.id=o.field_id
order by o.num");
OCIExecute($q);
echo "<optgroup label='Первичного ключа'>";
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").($depend_of_field==OCIResult($q,"ID")?' selected':NULL).">".OCIResult($q,"TEXT_NAME")."</option>";
}
echo "</optgroup></select>";
echo "<hr>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";

$q=OCIParse($c,"select t.id,t.text_value,t.code_value,t.quote_key,other_count,always_bottom,FOREIGN_KEY from STC_LIST_VALUES t
where t.object_id=".$obj_id." and t.deleted is null
order by ord");

echo "<table id=tbl name=tbl style='table-layout:fixed'>";
echo "<th width=12></th>";
echo "<th width=12 style='cursor:pointer' title='Добавить ниже. CTRL - вставить из буфера обмена (IE), остальные браузеры - из окошка обмена' onclick=plus(this)><img src='png/plus.png'></img></th>";
echo "<th width=40>№ (ID)</th>
	<th width=150>Текстовое значение</th>
	<th width=80>Кодовое значение</th>
	<th width=150>Ключ квоты / Первич.ключ</th>
	<th width=150>Вторичный ключ</th>	
	<th width=20>\"Другое\" (кол-во доп.полей)</th>
	<th width=20>Всегда в конце</th>
	<th>Условие</th>
	</tr>";

OCIExecute($q,OCI_DEFAULT);
$idx=0; while(OCIFetch($q)) {$idx++;

	echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
	echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick='del_old_val(\"".OCIResult($q,"ID")."\");del_val(this)'><img src='png/del.png'></img></th>";	
	echo "<th style='cursor:pointer' title='Добавить ниже. CTRL - вставить из буфера обмена (IE), остальные браузеры - из окошка обмена' onClick=plus(this)><img src='png/plus.png'></img></th>";
	echo "<th style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>
	
	<input type=hidden name=val_id[".$idx."] value='".OCIResult($q,"ID")."' onchange='notsaved()'>$idx(".OCIResult($q,"ID").")</th>";
	echo "<td><input style='width:100%' type=text name=text_value[".$idx."] value='".OCIResult($q,"TEXT_VALUE")."' onchange='notsaved()'></td>";
	echo "<td><input style='width:100%' type=text name=code_value[".$idx."] value='".OCIResult($q,"CODE_VALUE")."' onchange='notsaved()'></td>";
	echo "<td>";
	if(isset($quote_num)) echo "<input type=hidden name=old_quote_key[".$idx."] value='".OCIResult($q,"QUOTE_KEY")."'>";
	echo "<input style='width:100%' type=text name=quote_key[".$idx."] value='".OCIResult($q,"QUOTE_KEY")."' onchange='notsaved()'></td>";
	echo "<td><input style='width:100%' type=text name=foreign_key[".$idx."] value='".OCIResult($q,"FOREIGN_KEY")."' onchange='notsaved()'></td>";
	echo "<td align=center><input size=1 type=text name=other_count[".$idx."] value='".OCIResult($q,"OTHER_COUNT")."' onchange='notsaved()'></td>";
	echo "<td align=center><input type=checkbox name=always_bottom[".$idx."]".(OCIResult($q,"ALWAYS_BOTTOM")<>''?' checked':'')." onchange='notsaved()'></td>";
	echo "</tr>";
}
echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";

echo "<hr>";
if($_SESSION['user']['rw_ank']<>'w') echo "<font color=red>Редактирование запрещено!</font>";
else {
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=Сохранить onclick=this.disabled=true;frm.cancel.disabled=true;frm.submit();> ";
echo "<input type=button name=cancel value=Отмена onclick={this.style.display='none';frm.frm_submit.value='save';frm.save.value='Сохранить';document.getElementById('save_status').innerHTML='';} style='display:none' >";
		//echo "<input type=button name=cancel value=Отмена onclick=document.location.reload(); style='display:none' >";	
echo "и отсортировать <select name=order_by>
<option value=''>Как есть</option>
<option value='text_value'>По текстовому значению, по возрастанию</option>
<option value='text_value desc'>По текстовому значению, по убыванию</option>
<option value='quote_key'>По ключу квоты, по возрастанию, пустые в конце</option>
<option value='quote_key nulls first'>По ключу квоты, по возрастанию, пустые в начале</option>
<option value='quote_key desc nulls last'>По ключу квоты, по убыванию, пустые в конце</option>
<option value='quote_key desc'>По ключу квоты, по убыванию, пустые в начале</option>
<option value='code_value'>По кодовому значению, по возрастанию, пустые в конце</option>
<option value='code_value nulls first'>По кодовому значению, по возрастанию, пустые в начале</option>
<option value='code_value desc nulls last'>По кодовому значению, по убыванию, пустые в конце</option>
<option value='code_value desc'>По кодовому значению, по убыванию, пустые в начале</option>
</select>";
}
echo "</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

echo "<script>var new_idx=".$idx.";</script>";
?>
</body>
</html>
