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

if(!isset($form_id)) {echo "<font color=red>Такой формы не существует!</font>"; exit();} 

if(isset($save)) {
		$upd=OCIparse($c,"update SC_FORMS 
		set PHP_BEFORE_SAVE=EMPTY_CLOB()
		where id='".$form_id."'
		returning PHP_BEFORE_SAVE into :php_before_save");	
		$php_before_save_clob = oci_new_descriptor($c, OCI_D_LOB);
		OCIBindByName($upd,":php_before_save", $php_before_save_clob, -1, OCI_B_CLOB);
		OCIExecute($upd, OCI_DEFAULT);
		$php_before_save_clob->save($php_before_save);
		OCICommit($c);
} 

$q=OCIParse($c,"select f.name form_name, f.php_before_save, p.name project_name from SC_FORMS f, sc_projects p
where p.id=f.project_id and f.id='".$form_id."'");
OCIExecute($q);
OCIFetch($q);
$project_name=OCIResult($q,"PROJECT_NAME");
$form_name=OCIResult($q,"FORM_NAME");
if(OCIResult($q,"PHP_BEFORE_SAVE")<>'') $php_before_save=OCIResult($q,"PHP_BEFORE_SAVE")->load(); else $php_before_save='';

echo "<form name=frm method=post>";	
echo "<font size=4>PHP-код перед сохранением формы</font>";
echo "<hr>";
echo "<font size=4>Проект: <b>".$project_name."</b>. Форма: <b>".$form_name."</b></font>";
echo "<hr>";
	
	echo "PHP: Обязательно использовать &lt;?php ... ?>";
	echo "<input type=hidden name=form_id value='".$form_id."'></input>";
	echo "<br><textarea id=inj_code name=php_before_save rows=30 cols=150 style='wrap:nowrap;overflow:auto;'>". htmlspecialchars($php_before_save)."</textarea><hr>";
	echo "<input type=submit name=save value='СОХРАНИТЬ'>";
	echo "</form>";
	echo 'Данный PHP-код будет выполняться перед сохранением и отправкой формы.<br>
Можно читать параметры звонка:<br>
<b>$res[\'call_id\']</b> - число, идентификатор звонка, оно же идентификатор экземпляра открытия сценария<br>
<b>$res[\'report_id\']</b> - число, id отчета<br>
<b>$res[\'form_id\']</b> - число, id формы<br>
<b>$res[\'project_id\']</b> - число, id проекта<br>
<hr>
Можно читать и изменять значения полей отчета, обращаясь к ним по их ID.<br>
Например значение поля с идентификатором 28533 можно поменять так:<br> 
$form_values[28533]=\'Абракадабра\'; <br>
Так же поля отчета могут принимать множественные значения в виде массива, так:<br>
$form_values[28533]=array(\'Абракадабра\',\'Бумс\'); <hr>
Для вызова ошибки сохранения, нужно переменной $res[\'result_text\'] присвоить текст ошибки и сделать возврат из функции.<br> 
Пример:<br>
if($form_values[28532]==\'\') {<br>
	$res[\'result_text\']=\'Ошибка: Не заполнено поле\';<br>
	return $res;<br>
}<br>
';
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

