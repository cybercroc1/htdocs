<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
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
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

//сохранение настроек
if (isset($save)) {
	if(isset($sms_disabled)) $sms_disabled='y'; else $sms_disabled='';
	if($convert_aon<>'') {
		if(isset($aon_mod)) $convert_aon.="+";
	}
	$upd=OCIParse($c,"update SC_PROJECTS set 
	out_prefix='".$out_prefix."', 
	sms_disabled=case when '".$sms_disabled."'='y' and sms_disabled is not null then sms_disabled else decode('".$sms_disabled."','y',sysdate,null) end, 
	convert_aon='".$convert_aon."' 
	where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
}
//

$_SESSION['adm_usr_last_url']='adm_usr_main.php';

//==================

if(!isset($prj_id) or $prj_id=='') exit();

$q=OCIParse($c,"select t.id,t.name,t.out_prefix,t.sms_disabled,t.convert_aon from SC_PROJECTS t
where id='{$prj_id}'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$project_id=OCIResult($q,"ID");
$project_name=OCIResult($q,"NAME");
$out_prefix=OCIResult($q,"OUT_PREFIX");
$sms_disabled=OCIResult($q,"SMS_DISABLED");
$convert_aon=OCIResult($q,"CONVERT_AON");

echo "<font size=4>Настройки проекта <b>{$project_name}</b></font>";

//==================

echo "<form method=post>";
echo "<input type=hidden name=project_id value='".$project_id."'>";

echo "<hr>";
//

echo "<table><tr><td valign=top>";

echo "<table>";

echo "<tr><td><font size=3><b>Исходящий префикс:</b></font></td><td><input type=text name=out_prefix value=\"{$out_prefix}\"></td></tr>";
echo "<tr><td colspan=2><font size=2><i>Данный префикс будет использоваться при перезвоне по АОНу и при звонках из форм для исходящего звонка.</i></td></tr>";
echo "<tr><td><font size=3><b>Запретить отправку СМС:</b></font></td><td><input type=checkbox name=sms_disabled".($sms_disabled<>''?' checked':'')."></td></tr>";
echo "<tr><td colspan=2><font size=2><i>прямой запрет на отправку СМС из проекта (для неучтенных форм сторонних программистов)</i></td></tr>";
echo "<tr><td><font size=3><b>Конвертировать АОН:</b></font></td><td>
<select name=convert_aon><option value=''>Оставить как есть</option>
<option value='ru_dial'".(preg_match('/ru_dial/',$convert_aon)?" selected":"").">8[номер],810[код страны][номер]</option>
<option value='int_dial'".(preg_match('/int_dial/',$convert_aon)?" selected":"").">+[код страны][номер]</option>
<option value='ru_aon'".(preg_match('/ru_aon/',$convert_aon)?" selected":"").">10 цифр</option>
<option value='encode'".(preg_match('/encode/',$convert_aon)?" selected":"").">кодировать</option>
</select><br>
<input type=checkbox name=aon_mod".(preg_match('/\+/',$convert_aon)?" checked":"").">оставлять ошибочный</input>
</td></tr>";
echo "<tr><td colspan=2><font size=2><i>Если выбран тип конвертации АОН, то АОН будет преобразовываться, если АОН преобразовать не удалось, то АОН будет пустым. Опция \"оставлять ошибочный\" оставит не преобразованный АОН, как есть.</i></td></tr>";

echo "</table></td>";

echo "<td>";

echo "</td></tr>";
echo "</table>";
echo "<input type=submit name=save value=Сохранить>";	

echo "</form>";

?>
