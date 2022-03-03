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

if (isset($send)) {
include("sc/sc_smtp_conf.php"); //файл настроек SMTP
include("send_email.php");
include("sc/sc_adm_url.php");
$mess="Для доступа к системе зайдите на страничку: <a href=".$adm_url.">".$adm_url."</a><br>
<b>Пользователь: </b>".$login."<br>
<b>Пароль: </b>".$password."<hr>";
$headers="MIME-Version: 1.0 \n";
$headers.="Content-Type: text/html; charset=\"windows-1251\"\n";
//send($smtp_srv, $email, $from_email, $from_name='', "Доступ к отчетам колл-центра Wilstream",$mess,$headers);

		
		$res=send_email(
			$smtp_server, 
			$smtp_port,
			$smtp_auth_login, 
			$smtp_auth_pass, 
			$to_name='', 
			$to_email=$email, 
			$smtp_from_name, 
			$smtp_from_email, 
			$reply_to_name='', 
			$reply_to_email='' ,
			"Доступ к отчетам колл-центра Wilstream",
			$mess,
			$headers, 
			$debug=''
		);
		if (substr($res,0,2)=='OK') {$alert= "ОТПРАВЛЕНО"; echo "<script language='javascript'>alert('".substr($res,0,2)."')</script>";}
		else echo "<script language='javascript'>alert('".$res."')</script>";

}

if (!isset($cdpns)) {$cdpns=array();}
if (isset($save) or isset($send)) $login_id=save_usr($login_id,$description,$login,$password,$email,$rep_period,$c,$cdpns);
if (!isset($view_rep)) $view_rep='';
if (!isset($ch_email)) $ch_email='';
if (!isset($ch_form)) $ch_form='';
if (!isset($ch_sc)) $ch_sc='';
if (!isset($view_billing)) $view_billing='';
if (!isset($view_sms_log)) $view_sms_log='';
if (!isset($vsr_billing)) $vsr_billing='';
if (isset($add_irs_project)) add_project($login_id,$irs_project,$view_rep,$view_billing,$view_sms_log,$ch_email,$ch_form,$ch_sc,'',$c);
if (isset($add_vsr_project)) add_project($login_id,$vsr_project,'','','','','',$vsr_billing,$c);
if (isset($del_project)) del_project($login_id,$project_id,$c);
if (isset($del_usr)) {del_usr($login_id,$c); $login_id='';}

if (!isset($login_id) or $login_id=='') exit();



$q=OCIParse($c,"select * from sc_login where id='".$login_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$fio=OCIResult($q,"FIO");
$description=OCIResult($q,"DESCRIPTION");
$login=OCIResult($q,"LOGIN");
$password=OCIResult($q,"PASSWORD");
$email=OCIResult($q,"EMAIL");
$rep_period=OCIResult($q,"REP_PERIOD");	

echo "<form action=adm_usr.php method=post>"; //POST работает некорректно!
echo "<font size=4>Пользователь. ".$login.".</font>";	
echo " <a href=\"javascript:del_usr('".$login_id."')\"><img src=del.gif title=\"Удалить пользователя\" border=0></a>";
echo "<hr>";
//

echo "<table><tr><td valign=top>";

echo "<table>";

echo "<tr><td><font size=3><b>Описание:</b></font></td><td><input type=text name=description value=\"".$description."\"></td></tr>";
echo "<tr><td><font size=3><b>Логин:</b></font></td><td><input type=text name=login value=\"".$login."\"></td></tr>";
echo "<tr><td><font size=3><b>Пароль:</b></font></td><td><input type=text name=password value=\"".$password."\"></td></tr>";
echo "<tr><td colspan=2><font size=3><b>E-Mail: </b></font><input type=text name=email value=\"".$email."\">
<input type=submit name=send value=Отправить onclick=\"javascript:if(!chk_email()){alert('email неверен');return false;}\"></td></tr>";
echo "<tr><td><font size=3><b>Доступ к отчетам за:</b></font></td><td><select name=rep_period>
<option>".$rep_period."</option>
<option>Весь период</option>
<option>1 День</option>
<option>2 Дня</option>
<option>3 Дня</option>
<option>4 Дня</option>
<option>5 Дней</option>
<option>6 Дней</option>
<option>1 Неделя</option>
<option>2 Недели</option>
<option>3 Недели</option>
<option>1 Месяц</option>
<option>2 Месяца</option>
<option>3 Месяца</option>
<option>4 Месяца</option>
<option>5 Месяцев</option>
<option value='6 Месяцев'>Полгода</option>
<option value='12 Месяцев'>Год</option>
</td></tr>";

echo "</table></td>";

echo "<td>";

if(isset($login_id)) {
	echo "<table><tr><td valign=top>
	<b>Ограничение по номерам доступа.<br> Если ничего не выбрано, то ограничения по номерам нет</b><br>";

	$q=OCIParse($c,"select distinct pr.name,ph.phone,decode(a.phone,null,null,'y') checked from SC_ROLE r, sc_projects pr, sc_phones ph, sc_access_phone a
	where r.login_id='".$login_id."' and pr.id=r.project_id and ph.project_id=pr.id and a.phone(+)=ph.phone and a.login_id(+)='".$login_id."'
	order by pr.name,ph.phone");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<input type=checkbox name=cdpns[] value='".OCIResult($q,"PHONE")."'".(OCIResult($q,"CHECKED")=='y'?' checked':'').">".OCIResult($q,"PHONE")."</input> - ".OCIResult($q,"NAME")."<br>";
	}
echo "</td></tr></table>";
}

echo "</td></tr>";
echo "</table>";
echo "<input type=submit name=save value=Сохранить><hr>";	

if (isset($login_id) and $login_id<>'') {
if (isset($_SESSION['admin']) and $_SESSION['admin']=='1') {
	//Проекты IRS

	echo "<font size=4>Проекты пользователя (ИДЕАЛЬНЫЙ СЕКРЕТАРЬ)</font><br>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white align=center><b>Проект</b></td>
	<td bgcolor=white align=center><b>Смотреть<br>отчеты</b></td>
	<td bgcolor=white align=center><b>Смотреть<br>биллинг</b></td>
	<td bgcolor=white align=center><b>Смотреть<br>СМС-лог</b></td>
	<td bgcolor=white align=center><b>Редактировать<br>e-mail</b></td>
	<td bgcolor=white align=center><b>Редактировать<br>формы</b></td>
	<td bgcolor=white align=center><b>Редактировать<br>сценарий</b></td>
	<td bgcolor=white></td>";

	echo "</tr>";

	//Добавить проект IRS пользователю
	echo "<tr>";
	$q=OCIParse($c,"select id,name from sc_projects
where type='irs' and id not in(select project_id from sc_role where login_id='".$login_id."')
order by name");
	OCIExecute($q,OCI_DEFAULT);
	echo "<td bgcolor=green><select name=irs_project onchange=ch_irs_project()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
	echo "</select></td>";
	echo "<td bgcolor=green align=center><input type=checkbox checked value=1 name=view_rep></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=view_billing></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=view_sms_log></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_email></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_form></td>
	<td bgcolor=green align=center><input type=checkbox value=1 name=ch_sc></td>";
	
	echo "<td bgcolor=green colspan=2><input type=submit name=add_irs_project disabled value=\"Добавить проект\"></td></tr>";
	//

	$q=OCIParse($c,"select p.name,r.project_id,
decode(r.view_rep,1,'#80FF80','#FF8080') view_rep,
decode(r.ch_email,1,'#80FF80','#FF8080') ch_email,
decode(r.ch_form,1,'#80FF80','#FF8080') ch_form,
decode(r.ch_sc,1,'#80FF80','#FF8080') ch_sc,
decode(r.view_billing,1,'#80FF80','#FF8080') view_billing,
decode(r.view_sms_log,1,'#80FF80','#FF8080') view_sms_log
from sc_projects p, sc_role r
where p.id=r.project_id and r.login_id='".$login_id."' and p.type='irs'");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		echo "<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
		<td bgcolor=".OCIResult($q,"VIEW_REP")." align=center><a href='javascript:show_ifr(".OCIResult($q,"PROJECT_ID").",".$login_id.")'>отчеты</a>
		<iframe id='ifr_".OCIResult($q,"PROJECT_ID")."' style='display:none' width='500'></iframe></td>
		<td bgcolor=".OCIResult($q,"VIEW_BILLING")." align=center>биллинг</td>
		<td bgcolor=".OCIResult($q,"VIEW_SMS_LOG")." align=center>СМС-лог</td>
		<td bgcolor=".OCIResult($q,"CH_EMAIL")." align=center>e-mail</td>
		<td bgcolor=".OCIResult($q,"CH_FORM")." align=center>формы</td>
		<td bgcolor=".OCIResult($q,"CH_SC")." align=center>сценарий</td>
		<td bgcolor=white align=center><a href=\"?del_project=1&login_id=".$login_id."&project_id=".OCIResult($q,"PROJECT_ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td></tr>";
		
	}
echo "</table>";
echo "<script>
function ch_irs_project() {
if (document.all.irs_project.value=='') {document.all.add_irs_project.disabled=true;}
else {document.all.add_irs_project.disabled=false;}
}
function show_ifr(project_id,login_id) {
	with(document.getElementById('ifr_'+project_id)) {
	if(style.display=='') style.display='none'; else {
	style.display='';
	src='adm_form_access.php?login_id='+login_id+'&project_id='+project_id;
	}
	}
}

</script>";

	//
}
if (isset($_SESSION['vsr_admin']) and $_SESSION['vsr_admin']=='1') {
	//Проекты VSR

	echo "<hr><font size=4>Проекты пользователя (ВИРТУАЛЬНЫЙ СЕКРЕТАРЬ)</font><br>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white align=center><b>Проект</b></td>
	<td bgcolor=white align=center><b>Смотреть<br>биллинг</b></td>
	<td bgcolor=white></td>";

	echo "</tr>";

	//Добавить проект VSR пользователю
	echo "<tr>";
	$q=OCIParse($c,"select id,name from sc_projects
where type='vsr' and id not in(select project_id from sc_role where login_id='".$login_id."')
order by name");
	OCIExecute($q,OCI_DEFAULT);
	echo "<td bgcolor=green><select name=vsr_project onchange=ch_vsr_project()><option value=''>Выберите проект</option>";
		while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
		}
	echo "</select></td>";
	echo "<td bgcolor=green align=center><input type=checkbox checked value=1 name=vsr_billing></td>";
	
	echo "<td bgcolor=green colspan=2><input type=submit name=add_vsr_project disabled value=\"Добавить проект\"></td></tr>";
	//

	$q=OCIParse($c,"select p.name,r.project_id,
decode(r.vsr_billing,1,'#80FF80','#FF8080') vsr_billing
from sc_projects p, sc_role r
where p.id=r.project_id and r.login_id='".$login_id."' and p.type='vsr'");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<tr>";
		echo "<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
		<td bgcolor=".OCIResult($q,"VSR_BILLING")." align=center>биллинг</td>
		<td bgcolor=white align=center><a href=\"?del_project=1&login_id=".$login_id."&project_id=".OCIResult($q,"PROJECT_ID")."\"><img src=del.gif title=\"Удалить\" border=0></a></td></tr>";
		
	}
echo "</table>";
echo "<script>
function ch_vsr_project() {
if (document.all.vsr_project.value=='') {document.all.add_vsr_project.disabled=true;}
else {document.all.add_vsr_project.disabled=false;}
}
</script>";
}
	//
}
echo "</form>";

//Функция добавления и изменения пользователя
function save_usr($login_id,$description,$login,$password,$email,$rep_period,$c,$cdpns) {
	if ($login_id=='') {
	$q=OCIParse($c,"select seq_login_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_login_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_login (id,login,password,email,description,rep_period)
	values ('".$new_login_id."','".$login."','".$password."','".$email."','".$description."','".$rep_period."')");
		if (OCIExecute($ins,OCI_DEFAULT)) {
		OCICommit($c);
		$login_id=$new_login_id;
		} 
		else {
		echo "<font color=red>ОШИБКА! Пользователь с таким именем и паролем уже существует!</font>";
		}
	}
	else {
	$upd=OCIParse($c,"update sc_login set login='".$login."', password='".$password."', email='".$email."', description='".$description."', rep_period='".$rep_period."' 	where id='".$login_id."'");
		if (@OCIExecute($upd,OCI_DEFAULT)) {OCICommit($c);}
		else {
		echo "<font color=red>ОШИБКА! Пользователь с таким именем и паролем уже существует!</font>";
		}
	}
	cdpns_access_save($c,$login_id);
return $login_id;
}
//

//функция ограничения доступа к номерам
function cdpns_access_save($c,$login_id) {
	global $cdpns;
	$del_ph=OCIParse($c,"delete from SC_ACCESS_PHONE where login_id=".$login_id);
	
	//echo "<br>delete from SC_ACCESS_PHONE where login_id=".$login_id."<br>";
	
	OCIExecute($del_ph,OCI_DEFAULT);
	foreach($cdpns as $phone) {
		$ins_ph=OCIParse($c,"insert into SC_ACCESS_PHONE (login_id,phone,project_id) values (".$login_id.",'".$phone."',(select project_id from sc_phones where phone='".$phone."'))");
		OCIExecute($ins_ph,OCI_DEFAULT);
	}
	OCICommit($c);
}

//Функция добавления проекта пользователю
function add_project($login_id,$project_id,$view_rep,$view_billing,$view_sms_log,$ch_email,$ch_form,$ch_sc,$vsr_billing,$c) {
	$ins=OCIParse($c,"insert into sc_role (login_id,project_id,view_rep,ch_email,ch_form,ch_sc,view_billing,view_sms_log,vsr_billing) 
	values ('".$login_id."','".$project_id."','".$view_rep."','".$ch_email."','".$ch_form."','".$ch_sc."','".$view_billing."','".$view_sms_log."','".$vsr_billing."')");
	OCIExecute($ins,OCI_DEFAULT); 
	OCICommit($c);
}
//
//Функция удаления проекта пользователю
function del_project($login_id,$project_id,$c) {
	$del=OCIParse($c,"delete from sc_role where login_id='".$login_id."' and project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT); 
	$del_ph=OCIParse($c,"delete from SC_ACCESS_PHONE where login_id=".$login_id." and project_id=".$project_id);
	OCIExecute($del_ph,OCI_DEFAULT);	
	OCICommit($c);
}
//
//Функция пользователю
function del_usr($login_id,$c) {
	$del=OCIParse($c,"delete from sc_login where id='".$login_id."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//

//функция отправки через сокет
/*
function send($server, $to, $from_email,$from_name, $title,$mess,$headers) {
	$headers.="To: ".$to."\r\n";
	if ($from_name<>"") $from_name="=?koi8-r?B?".base64_encode(convert_cyr_string($from_name, "w","k"))."?=";	
	$headers.="From: ".$from_name."<".$from_email.">\r\n";
	$headers.="Subject: =?koi8-r?B?".base64_encode(convert_cyr_string($title, "w","k"))."?=\r\n";	
	$fp = fsockopen($server, 25,$errno,$errstr,30); 
	if (!$fp) die("Server $server. Connection failed: $errno, $errstr");
	socket_set_timeout($fp,10,0);	 
		fputs($fp,"HELO bill\r\n"); 
		fputs($fp,"MAIL FROM: ".$from_email."\r\n"); 
		fputs($fp,"RCPT TO: ".$to."\r\n"); 
		fputs($fp,"DATA\r\n"); 
		fputs($fp,$headers."\r\n".$mess."\r\n"."."."\r\n");  
		fputs($fp,"QUIT\r\n"); 
			while(!feof($fp)) {    
			$smtp_answ=fgets($fp,1024);
			//echo $smtp_answ."<br>";
				if (substr($smtp_answ,0,3)>420) {
				$err=$to." - ".str_replace(chr(13).chr(10),'',$smtp_answ); 
				$alert="Ошибка: ".$err; 
				break;
				}
			}		
		$stream_info=stream_get_meta_data($fp);
		if($stream_info['timed_out']==1) {
			$err=$server." - Истекло время ожидания SMTP ответа"; 
			$alert="Ошибка: ".$err;
		}
		fclose($fp);
		if (!isset($err)) $alert= "ОТПРАВЛЕНО";
	echo "<script language='javascript'>alert('".$alert."')</script>";
	}
*/


/*function send($server, $to, $from_email, $title,$mess,$headers) {
	$headers="To: ".$to."\nFrom: ".$from_email."\nSubject: ".$title."\n".$headers; 
	$fp = fsockopen($server, 25,$errno,$errstr,30); 
		if (!$fp) {$alert=$server." Ошибка: ".$errno.", ".$errstr."<br>";} 
		else {
		fgets($fp,128);
		fputs($fp,"HELO bill\n"); 
		fgets($fp,128);
		fputs($fp,"MAIL FROM: ".$from_email."\n");
		fgets($fp,128);
		fputs($fp,"RCPT TO: ".$to."\n"); 
		fgets($fp,128);
		fputs($fp,"DATA\n");
		fgets($fp,128);
		fputs($fp,$headers."\n".$mess."\r\n"."."."\r\n");
		fgets($fp,128);
		fputs($fp,"QUIT\n"); 
		fclose($fp); 
		$alert="ОТПРАВЛЕНО !";
		}
echo "<script language='javascript'>alert('".$alert."')</script>";
}*/
//

?>
<script language="javascript">
document.all.ch_login.style.display='none';
function del_usr(login_id) {
if (confirm('Действительно хотите УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ?')) document.location='?del_usr=1&login_id='+login_id;
}
function chk_email() {
var reg = new RegExp('^[^\.][0-9a-z_\.\-]+@[0-9a-z_\.\-]+\.[a-z\-]$','i');
if (reg.test(document.all.email.value)) return true;
}

</script>
