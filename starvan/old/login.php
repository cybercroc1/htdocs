<?php 
//session.cfg
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_name('starcall_1905');
session_start();
//
include("../../conf/starcall_conf/conn_string.cfg.php");
if(isset($_GET['exit'])) {
	if(isset($_SESSION['user']['id'])) {
		OCIExecute(OCIParse($c,"update STC_USERS set last_logout=sysdate where id=".$_SESSION['user']['id']));
		//разблокировка записей
		OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));	
		session_destroy();
		echo session_id();
		echo "<script>parent.parent.location='login.php'</script>";
	
	}
}

extract($_POST);
$err='';
//авторизация
if (!isset($_SESSION['user']['id'])) {
	if (isset($starcall_user) and isset($starcall_pass)) {
		session_destroy();
		session_name('starcall_1905');
		session_start();		
		$err=auth($starcall_user,$starcall_pass);
	}
}
//
//echo 5;
//echo $_SESSION['user']['id'];
if(isset($_SESSION['user']['id'])) {
	if($_SESSION['user']['operator_only']=='y') func_survey_frame();
	else func_adm_frame();
}
else login_form($err);

//===============================================================================================================================
//форма логина
function login_form($err) {
//echo session_id();
echo '<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
		<title>Старколл</title>
	</head>
	<body>';
		echo $err;
		echo '<form method="POST" name="login_frm">
			<table border="0" width="200" cellspacing="0" cellpadding="8" height="137" align="center">
				<tr>
					<td colspan="2" align="center"><img src="gif/logo.gif" width="200" height="100" alt="logo.gif (72568 bytes)"></td>
				</tr>
				<tr>
					<td width="20%" height="25"><p><font color="#00000"><strong>Пользователь</strong></font></td>
					<td width="20%" align="center" height="25"><input type="text" name="starcall_user" size="20"></td>
				</tr>
				<tr>
					<td width="20%" height="25"><p><font color="#00000"><strong>Пароль</strong></font></td>
					<td width="20%" align="center" height="25"><input type="password" name="starcall_pass" size="20"></td>
				</tr>
				<tr align="center">
					<td colspan=2 align="center" height="65"><input type="submit" value="Вход"></td>
				</tr>
			</table>
		</form>
	</body>
</html>';
}		
//	

function auth($starcall_user,$starcall_pass) { 
	global $c;
	$q=OCIParse($c,"select u.id, u.role_id, u.fio, u.last_php_ssid, 
	r.name role_name, r.operator, r.operator_only, r.all_projects, r.all_users, r.rw_projects,r.rw_project,rw_ank, rw_src_bd, rw_quote, rw_stat, rw_report, rw_users, rw_tools, rw_opers, role_id, role_level,
	CASE WHEN (u.last_logout is null or u.last_logout<=u.last_activity) and u.last_activity>=sysdate-2/1440 THEN 'y' END other_lock  
	from STC_USERS u, STC_LI_ROLES r 
	where upper(u.login)=upper('".$starcall_user."') and u.pass='".$starcall_pass."' and login is not null and u.pass is not null and u.deleted is null
	and r.id=u.role_id");
	OCIExecute($q, OCI_DEFAULT);
	if (OCIFetch($q)) {
		if(OCIResult($q,"OTHER_LOCK")=='y') {
			session_destroy();
			$err="<font color=red>Данный ползователь уже подключен в другом месте или не вышел из системы, попробуйте через 2 минуты!</font>";
			return $err;
		}
		else {
			//убиваем предыдущую сессию пользователя
			if(OCIResult($q,"LAST_PHP_SSID")<>'' and OCIResult($q,"LAST_PHP_SSID")<>session_id()) {
				session_id(OCIResult($q,"LAST_PHP_SSID"));
				session_destroy();
				session_start();
			}
			if(OCIResult($q,"OPERATOR_ONLY")=='y') {
				$_SESSION['user']['id']=OCIResult($q,"ID");
				$_SESSION['user']['fio']=OCIResult($q,"FIO");
				$_SESSION['user']['role_id']=OCIResult($q,"ROLE_ID");
				$_SESSION['user']['role_name']=OCIResult($q,"ROLE_NAME");
				$_SESSION['user']['role_level']=OCIResult($q,"ROLE_LEVEL");
				$_SESSION['user']['operator']='y';
				$_SESSION['user']['operator_only']='y';
				$_SESSION['user']['all_projects']='';
				$_SESSION['user']['all_users']='';
				$_SESSION['user']['rw_projects']='';
				$_SESSION['user']['rw_project']='';
				$_SESSION['user']['rw_ank']='';
				$_SESSION['user']['rw_src_bd']='';
				$_SESSION['user']['rw_quote']='';
				$_SESSION['user']['rw_stat']='';
				$_SESSION['user']['rw_report']='';
				$_SESSION['user']['rw_users']='';
				$_SESSION['user']['rw_opers']='';
				$_SESSION['user']['rw_tools']='';
				$_SESSION['refresh_lock_project']='n';
				$_SESSION['refresh_lock_records']='n';
			} 
			else { 
				$_SESSION['user']['id']=OCIResult($q,"ID");
				$_SESSION['user']['fio']=OCIResult($q,"FIO");
				$_SESSION['user']['role_id']=OCIResult($q,"ROLE_ID");
				$_SESSION['user']['role_name']=OCIResult($q,"ROLE_NAME");
				$_SESSION['user']['role_level']=OCIResult($q,"ROLE_LEVEL");
				$_SESSION['user']['operator']=OCIResult($q,"OPERATOR");
				$_SESSION['user']['operator_only']=OCIResult($q,"OPERATOR_ONLY");
				$_SESSION['user']['all_projects']=OCIResult($q,"ALL_PROJECTS");
				$_SESSION['user']['all_users']=OCIResult($q,"ALL_USERS");
				$_SESSION['user']['rw_projects']=OCIResult($q,"RW_PROJECTS");
				$_SESSION['user']['rw_project']=OCIResult($q,"RW_PROJECT");
				$_SESSION['user']['rw_ank']=OCIResult($q,"RW_ANK");
				$_SESSION['user']['rw_src_bd']=OCIResult($q,"RW_SRC_BD");
				$_SESSION['user']['rw_quote']=OCIResult($q,"RW_QUOTE");
				$_SESSION['user']['rw_stat']=OCIResult($q,"RW_STAT");
				$_SESSION['user']['rw_report']=OCIResult($q,"RW_REPORT");
				$_SESSION['user']['rw_users']=OCIResult($q,"RW_USERS");
				$_SESSION['user']['rw_opers']=OCIResult($q,"RW_OPERS");
				$_SESSION['user']['rw_tools']=OCIResult($q,"RW_TOOLS");
				$_SESSION['refresh_lock_project']='n';
				$_SESSION['refresh_lock_records']='n';
			}
		}
	}
	else {
		$err="<font color=red>Не верное имя или пароль!</font>";
		return $err;
	}
}
//	
function func_adm_frame() {
		if(!isset($_SESSION['user']['id'])) {
		echo "<font color=red>ACCESS DENY !</font>";
		echo "<script>parent.parent.location='login.php'</script>";
	}
	echo '<!DOCTYPE html>
	<html>
	<head>
		<title>StarCall admin</title>';
echo "
<script src='session.refresh.js'></script>
<script>
//ежеминутное обновление сессии
session_refresh();
window.setInterval(session_refresh,60000);
</script>";
	echo '</head>
	<frameset id=admFrameset rows="50,*,1">
		<frame src=adm.main.menu.php name=admMainTopFrame id=admTopFrame title=admTopFrame noresize="noresize" scrolling="no"></frame>

		<frame src=blank_page.php name=admBottomFrame id=admBottomFrame title=admBottomFrame></frame>
  
		<frame src=blank_page.php name=logFrame id=logFrame title=admBottomFrame></frame>
	</frameset>
	</html>';
}	
function func_survey_frame() {
		if(!isset($_SESSION['user']['id'])) {
		echo "<font color=red>ACCESS DENY !</font>";
		echo "<script>parent.parent.location='login.php'</script>";
	}
	echo '<!DOCTYPE html>
	<html>
	<head>
		<title>StarCall survey</title>';
echo "
<script src='session.refresh.js'></script>
<script>
//ежеминутное обновление сессии
session_refresh();
window.setInterval(session_refresh,60000);
</script>";
	echo '</head>
	<frameset name=surveyFrameset id=surveyFrameset rows="50,*">
		<frame src=survey.main.menu.php name=surveyMainTopFrame id=surveyMainTopFrame title=surveyMainTopFrame noresize="noresize" scrolling="no">

		<frame src=blank_page.php name=surveyMainBottomFrame id=surveyMainBottomFrame title=surveyMainBottomFrame>
	</frameset>
	<noframes></noframes>
	</html>';
}	
?>		
