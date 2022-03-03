<?php 

ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
header('X-UA-Compatible: IE=edge');
$_SESSION['last_url']='edit_inject.php';
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="codemirror/lib/codemirror.css">
</head>
<script>
function ch_inject_id() {
	frm.ch_inject.click();
}
function del_inject(inject_id) {
	if (confirm('Действительно хотите УДАЛИТЬ ФОРМУ ?')) frm.del_inject.click();
}
</script>
<script src="codemirror/lib/codemirror.js"></script>
<script src="codemirror/addon/edit/matchbrackets.js"></script>
<script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="codemirror/mode/xml/xml.js"></script>
<script src="codemirror/mode/javascript/javascript.js"></script>
<script src="codemirror/mode/css/css.js"></script>
<script src="codemirror/mode/clike/clike.js"></script>
<script src="codemirror/mode/php/php.js"></script>
<body>
<?php if ($_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

if(!isset($inject_id)) $inject_id='';

if(isset($del_inject)) {del_inject($inject_id,$c); $inject_id='';}
if(isset($save)) {
	if($inject_id=='') {
		$ins=OCIparse($c,"insert into SC_INJECTS (id,project_id,name,inj_code,window_left,window_top,window_width,window_height,bgcolor) 
		values (SEQ_INJECT_ID.nextval,'".$_SESSION['project']['id']."','".$inject_name."',EMPTY_CLOB(),:window_left,:window_top,:window_width,:window_height,:bgcolor)
		returning id,inj_code into :id,:inj_code");
		$inj_code_clob = oci_new_descriptor($c, OCI_D_LOB);
		OCIBindByName($ins, ":inj_code", $inj_code_clob, -1, OCI_B_CLOB);
		OCIBindByName($ins,":window_left",$window_left);
		OCIBindByName($ins,":window_top",$window_top);
		OCIBindByName($ins,":window_width",$window_width);
		OCIBindByName($ins,":window_height",$window_height);
		OCIBindByName($ins,":bgcolor",$bgcolor);

		OCIBindByName($ins,":id",$inject_id,16);
		OCIExecute($ins, OCI_DEFAULT);
		$inj_code_clob->save($inj_code);
		OCICommit($c);
	}
	else {
		$upd=OCIparse($c,"update SC_INJECTS 
		set name='".$inject_name."',
		inj_code=EMPTY_CLOB(),
		window_left=:window_left,
		window_top=:window_top,
		window_width=:window_width,
		window_height=:window_height,
		bgcolor=:bgcolor	
		where id='".$inject_id."' and project_id='".$_SESSION['project']['id']."'
		returning inj_code into :inj_code");	
		$inj_code_clob = oci_new_descriptor($c, OCI_D_LOB);
		OCIBindByName($upd,":inj_code", $inj_code_clob, -1, OCI_B_CLOB);
		OCIBindByName($upd,":window_left",$window_left);
		OCIBindByName($upd,":window_top",$window_top);
		OCIBindByName($upd,":window_width",$window_width);
		OCIBindByName($upd,":window_height",$window_height);
		OCIBindByName($upd,":bgcolor",$bgcolor);
		
		OCIExecute($upd, OCI_DEFAULT);
		$inj_code_clob->save($inj_code);
		OCICommit($c);
	}
	
} 

echo "<form name=frm method=post>";	
if ($_SESSION['project']['ch_form']==1) echo "<a href=edit_form.php>Редактирование формы</a> ";
if ($_SESSION['project']['ch_email']==1) echo " | <a href=edit_email.php>Редактирование е-мейлов</a>";
echo "<font size=4> | Внешние формы (PHP-injects)</font>";
if ($_SESSION['admin']==1) echo " | <a href=edit_call_fields.php>Дополнительные поля звонка </a>";
echo "<hr>";

//Выбор формы
	echo "<select name='inject_id' onchange='ch_inject_id()'>";
	echo "<option value=''>СОЗДАТЬ</option>";
	$q=OCIParse($c,"select i.id, i.name,i.inj_code,i.window_left,i.window_top,i.window_width,i.window_height,i.bgcolor from SC_INJECTS i
	where i.project_id=".$_SESSION['project']['id']." and id>0 and deleted is null order by name");
	OCIExecute($q,OCI_DEFAULT);
	$inject_name='';
	$inj_code='';
	$window_left='';
	$window_top='';
	$window_width='700';
	$window_height='600';
	$bgcolor='#F0E68C';
	while (OCIFetch($q)) {
		$selected='';
		if($inject_id==OCIResult($q,"ID")) {
			$selected=' selected';
			$inject_name=OCIResult($q,"NAME");
			if(OCIResult($q,"INJ_CODE")<>'') $inj_code=OCIResult($q,"INJ_CODE")->load(); else $inj_code='';
			$window_left=OCIResult($q,"WINDOW_LEFT");
			$window_top=OCIResult($q,"WINDOW_TOP");
			$window_width=OCIResult($q,"WINDOW_WIDTH");
			$window_height=OCIResult($q,"WINDOW_HEIGHT");
			$bgcolor=OCIResult($q,"BGCOLOR");
		}
		echo "<option value='".OCIResult($q,"ID")."'".$selected.">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>";
	if ($inject_id<>'') {
		echo " <a href=\"javascript:del_inject('".$inject_id."')\"><img src=del.gif title=\"Удалить форму\" border=0></a>";
	}	
	echo "<input type=submit name=ch_inject value=ВЫБРАТЬ style='display:none'><input type=submit name=del_inject value=УДАЛИТЬ style='display:none'><hr>";
	
	echo "Название <input type=text name=inject_name value='".$inject_name."' size='80'></select>";
	echo "<br>";
	echo "
	Лево:<input name=window_left type=text value='".$window_left."' size=5> | 
	Верх:<input name=window_top type=text value='".$window_top."' size=5> | 

	Ширина:<input name=window_width type=text value='".$window_width."' size=5> | 
	Высота:<input name=window_height type=text value='".$window_height."' size=5> | 
	Цвет:<input name=bgcolor type=text value='".$bgcolor."' size=10 style='background-color:".$bgcolor."'>
	<br>";
	
	echo "Код. ";
	echo "<br><textarea id=inj_code name=inj_code rows=30 cols=150 style='wrap:nowrap;overflow:auto;'>". htmlspecialchars($inj_code)."</textarea><hr>";
	echo "<input type=submit name=save value='СОХРАНИТЬ'>";
	echo "</form>";
	echo 'Возможно использовать следующие переменные:<br>
<b>Стандартные параметры звонка:</b><br>
<b>$call_id</b> - число, идентификатор звонка, оно же идентификатор экземпляра открытия сценария<br>
<b>$blog_id</b> - идентификатор текущего блока сченария (в редакторе сценариев помечен "#")
<b>$date_call</b> - дата звонка ДД.ММ.ГГГГ (момент открытия сценария)<br>
<b>$time_call</b>  - время звонка ЧЧ:ММ:СС (момент открытия сценария)<br>
<b>$project_id</b> - число, идентификатор проекта, оно же сценария<br>
<b>$aon</b> - АОН  - значение, преданное сценарию в параметре aon или cgpn (устар.)<br> 
<b>$cgpn</b> - устаревший синоним $aon (использовать не желательно)<br>
<b>$aon_norm</b> - нормализованный АОН в формате 8хххххххххх или 810хххххххххх.. (для междунар). Если АОН нормализовать не удалось, то значение этой переменной будет пустым<br>	
<b>$aon_e164</b> - нормализованный АОН в формате e164 +[код_страны][номер]. Если АОН нормализовать не удалось, то значение этой переменной будет пустым<br>	
<b>$cdn</b> - маршрутный номер - значение, переданное сценарию в параметре cdn или cdpn(устар.)<br>
<b>$cdpn</b>  - устаревший синоним $cdn (использовать не желательно)<br>
<b>$agid</b> - имя оператора Октелл<br>
<b>$uid</b> - строка, ID оператора Октелл<br>
<b>$thrid</b> - строка, внешний идентификатор звонка (idchain, октелл)<br>
<b>$sip_call_id</b> - строка, SIP-идентификатор звонка<br>
<b>$caller_name</b> - значение из SIP-заголовка caller(name)<br> 
<b>$call_direction</b> - in,out,callback - направление звонка (по умолчанию in)<br>
<b>$tel_server_id</b> - идентификатор экземпляра сервера телефонии<br>
<b>$project_name</b> - название проекта<br>
<b>$url_to_project</b> - url к файлам проекта<br>
<b>$aon_for_backcall</b> - АОН для перезвона, отфильтрованный по таблице фейковых АОНов<br>
<b>$custom_data</b> - свободное поле, будет содержать ровно то, что передали в сценарий<br> 
<b>$oktell_task_id</b> - строка, идентификатор задач октелл<br>
<b>$out_prefix</b> - префикс для исходящего звонка<br>
<b>$dialed_number</b> - номер для исходящего звонка<br>
<hr><b>Дополнительные поля звонка:</b><br>
Дополнительные поля звонка могут передаваться сценарию методом POST или GET в виде строки или массива, в каком виде передано, в таком будет и тут.<br>
Список полей для данного проекта:';
$q=OCIParse($c,"select id,var_name,name from SC_CALL_FILEDS f where project_id='".$_SESSION['project']['id']."' and deleted is null order by ord");
OCIExecute($q);
while(OCIFetch($q)) {
	echo '<b>$call_data[\''.OCIResult($q,"VAR_NAME").'\']</b> - '.OCIResult($q,"NAME").'<br>';
}
echo "<hr><b>Функции:</b><br>
<b>u8( текст )</b> - преобразовать текст в UTF-8<br>
<b>cp( текст )</b> - преобразовать текст в Windows-1251<br>
<b>set_additional_field( имя поля, новое значение )</b> - устанавливает новое значение дополнительного поля звонка. Значения в виде массива не поддерживаются, только строка";	
//
//Функция удаления объекта
function del_inject($inject_id,$c) {
	$del=OCIParse($c,"update SC_INJECTS set deleted=sysdate 
	where project_id='".$_SESSION['project']['id']."' and id='".$inject_id."'");
	OCIExecute($del,OCI_DEFAULT);
	$del=OCIParse($c,"delete from SC_BODY where type='FV' and inject_id='".$inject_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}//
?>
<script>
var editor = CodeMirror.fromTextArea(document.getElementById("inj_code"), {
	lineNumbers: true,
	matchBrackets: true,
	mode: "application/x-httpd-php",
	indentUnit: 4,
	indentWithTabs: true
});
</script>

