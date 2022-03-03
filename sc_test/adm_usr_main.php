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
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

//Удаление пользователя
if (isset($del_usr)) {
	$del=OCIParse($c,"delete from sc_login where id='".$login_id."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
	$login_id='';
	$_SESSION['edit_login']['id']='';
	echo "<script>parent.adm_usr_fr1.location.reload();</script>";
}
//

//сохранение пользователя
if (isset($save) or isset($send)) {
	if (isset($send) and $email<>'') {
		//include("../../sc_conf/sc_smtp");
		include("../../sc_conf/sc_smtp_conf.php"); //файл настроек SMTP
		include("send_email.php");		
		include("../../sc_conf/sc_adm_url");
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
		if (substr($res,0,2)=='OK') {$alert= "ОТПРАВЛЕНО"; echo "<script language='javascript'>alert('".$alert."')</script>";}
		else echo "<script language='javascript'>alert('".$res."')</script>";
		
	}
	if(isset($allow_records)) $allow_records=1; else $allow_records='';
	if(isset($allow_noreport)) $allow_noreport=1; else $allow_noreport='';
	if ($login_id=='') {
	$q=OCIParse($c,"select seq_login_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_login_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_login (id,login,password,email,description,rep_period,fio,allow_records,allow_noreport)
	values ('".$new_login_id."','".$login."','".$password."','".$email."','".$description."','".$rep_period."','".$fio."','".$allow_records."','".$allow_noreport."')");
		if (OCIExecute($ins,OCI_DEFAULT)) {
			OCICommit($c);
			$login_id=$new_login_id;
			$_SESSION['edit_login']['id']=$login_id;
			$_SESSION['edit_login']['fio']=$fio;
			$_SESSION['edit_login']['login']=$login;
			$_SESSION['edit_login']['desc']=$description;
			$_SESSION['edit_login']['allow_records']=$allow_records;
			$_SESSION['edit_login']['allow_noreport']=$allow_noreport;
			echo "<script>parent.adm_usr_fr1.location.reload();</script>";
		} 
		else {
		echo "<font color=red>ОШИБКА! Пользователь с таким именем и паролем уже существует!</font>";
		}
	}
	else {
	$upd=OCIParse($c,"update sc_login set login='".$login."', password='".$password."', email='".$email."', 
	description='".$description."', rep_period='".$rep_period."',fio='".$fio."',allow_records='".$allow_records."',allow_noreport='".$allow_noreport."' 
	where id='".$login_id."'");
		if (@OCIExecute($upd,OCI_DEFAULT)) {
			OCICommit($c);
			echo "<script>parent.adm_usr_fr1.location.reload();</script>";
		}
		else {
		echo "<font color=red>ОШИБКА! Пользователь с таким именем и паролем уже существует!</font>";
		}
	}
}
//

$_SESSION['adm_usr_last_url']='adm_usr_main.php';

//==================

function pass_gen($number) {
    $arr = array('a','b','c','d','e','f','g','h','i','j','k',
        'm','n','o','p','r','s','t','u','v','x','y','z',
        'A','B','C','D','E','F','G','H','J','K','L',
        'M','N','P','R','S','T','U','V','X','Y','Z',
        '2','3','4','5','6','7','8','9');
    $pass = "";
    for($i = 0; $i < $number; $i++) {
        $index = rand(0, count($arr) - 1);
        $pass .= $arr[$index];
    }
    return $pass;
}

if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id; 
else if(!isset($_SESSION['edit_login']['id'])) exit();

$q=OCIParse($c,"select * from sc_login where id='".$_SESSION['edit_login']['id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$_SESSION['edit_login']['id']=OCIResult($q,"ID");
$_SESSION['edit_login']['fio']=OCIResult($q,"FIO");
$_SESSION['edit_login']['login']=OCIResult($q,"LOGIN");
$_SESSION['edit_login']['desc']=OCIResult($q,"DESCRIPTION");
$_SESSION['edit_login']['allow_records']=OCIResult($q,"ALLOW_RECORDS");
$_SESSION['edit_login']['allow_noreport']=OCIResult($q,"ALLOW_NOREPORT");
$password=OCIResult($q,"PASSWORD");
$email=OCIResult($q,"EMAIL");
$rep_period=OCIResult($q,"REP_PERIOD");	

if($_SESSION['edit_login']['id']<>'') echo "<font size=4>".$_SESSION['edit_login']['login']."</font>";
else echo "<font size=4>Создать пользователя</font>";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_prj_frame.php>проекты</a> ";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_comrep_frame.php>Общие отчеты</a> | ";

//==================

echo "<form method=post>"; //POST работает некорректно!
echo "<input type=hidden name=login_id value='".$_SESSION['edit_login']['id']."'>";

echo "<hr>";
//

if($password=='') $password=pass_gen(8);

echo "<table><tr><td valign=top>";

echo "<table>";

echo "<tr><td><font size=3><b>Логин:</b></font></td><td><input type=text name=login value=\"".$_SESSION['edit_login']['login']."\"></td></tr>";
echo "<tr><td><font size=3><b>Пароль:</b></font></td><td><input type=text name=password value=\"".$password."\"></td></tr>";
echo "<tr><td><font size=3><b>ФИО:</b></font></td><td><input type=text name=fio value=\"".$_SESSION['edit_login']['fio']."\"></td></tr>";
echo "<tr><td><font size=3><b>Описание:</b></font></td><td><input type=text name=description value=\"".$_SESSION['edit_login']['desc']."\"></td></tr>";
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

echo "<tr><td colspan=2><font size=3><b>Разрешить прослушивать записи</b></font><input type=checkbox name=allow_records".($_SESSION['edit_login']['allow_records']==1?" checked":"")."></td></tr>";
echo "<tr><td colspan=2><font size=3><b>Разрешить доступ к звонкам без отчета</b></font><input type=checkbox name=allow_noreport".($_SESSION['edit_login']['allow_noreport']==1?" checked":"")."></td></tr>";

echo "</table></td>";

echo "<td>";

echo "</td></tr>";
echo "</table>";
echo "<input type=submit name=save value=Сохранить>";	

echo "</form>";

//функция отправки через сокет
/*function send($server, $to, $from_email,$from_name, $title,$mess,$headers) {
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
	}*/

//

?>
<script language="javascript">
//document.all.ch_login.style.display='none';
function chk_email() {
var reg = new RegExp('^[^\.][0-9a-z_\.\-]+@[0-9a-z_\.\-]+\.[a-z\-]$','i');
if (reg.test(document.all.email.value)) return true;
}

</script>
