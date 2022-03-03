<?php
ini_set( 'default_charset', 'UTF-8' );
require_once "lk/defines.php";
//выход
if(isset($_GET['exit'])) auth_exit();

$session_login_page='login.php';

if(!auth_check()) { //если пользователь не авторизован
	if(isset($is_main_page)) { //если переход со страницы логина
		if(!isset($_POST['usrlgn']) or !isset($_POST['usrpss'])) { //и не запрашивает авторизацию
			include($session_login_page); exit();
		}
		else { //иначе, пользователь запрашивает авторизацию
			if(!isset($_POST['save_pass'])) $save_pass='n'; else $save_pass='y';
			if(!auth($_POST['usrlgn'],$_POST['usrpss'],$save_pass)) { //и не проходит ее
				$auth_err='Не верное имя или пароль';
				include($session_login_page);
				exit();
			}		
		}
	} 
	else {
		echo "<font color=red>Вы не авторизованы, доступ запрещен.</font>"; exit();
	}
}

///////////////ФУНКЦИИ//////////
function auth_exit() {
    session_name(SESSIONNAME); //устанавливает имя сессии
    session_start();
	//session_start(); //создает сесисю с именем medc и кладет ее в cookies, если на компьютере уже есть кукис с таким именем сесии, то подключается к существующей
    session_destroy(); //удаляем сессию
	header("Location:./"); //перезагружаем данную страницу с очисткой get-параметров
}

function auth_check() {
	session_name(SESSIONNAME);
	session_start();
	if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {
		return false;
	} 
	else return true;
}

function auth($usrlgn,$usrpss,$save_pass='n') {
	require_once "lk/lk_ora_conn_string.php";
	$q=OCIParse($c,"select id,
	case 
	when rep_period='Весь период' then null
	when substr(rep_period,instr(rep_period,' ')+1,1)='Д' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1),'DD.MM.YYYY')
	when substr(rep_period,instr(rep_period,' ')+1,1)='Н' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1)*7,'DD.MM.YYYY')
	when substr(rep_period,instr(rep_period,' ')+1,1)='М' then to_char(add_months(sysdate,-(substr(rep_period,0,instr(rep_period,' ')-1))),'DD.MM.YYYY')
	end rep_period, irs_admin, vsr_admin,allow_records,allow_noreport,allow_nocall,allow_record_full,allow_view_all_reports,allow_view_all_sc
	from sc_login where upper(login)=upper('".$usrlgn."') and password='".$usrpss."' and login is not null and password is not null and disabled is null");
	OCIExecute($q, OCI_DEFAULT);
	if (OCIFetch($q)) {
		//если пользователь прошел авторизацию
		setcookie('usrlgn', $usrlgn, mktime(0, 0, 0, 1, 1, 2030)); //логин и пароль введены верно, этого достаточно, что бы запомнить логин
		if ($save_pass=='y') {
			setcookie('usrpss', $usrpss, mktime(0, 0, 0, 1, 1, 2030)); //если выбрана опция сохранения пароля, устанавливаем кукес с паролем
		} 
		else {
			setcookie('usrpss'); //иначе удаляем кукис с паролем из браузера
			unset($_COOKIE['usrpss']); //и из текущего сеанса из сеанса надо удалять потому, что кукис уже прочтен и остался в данном сеансе
		}
		$_SESSION['auth']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']); //создаем сессионную переменную, говорящую о том, что пользователь авторизован, в качестве параметра записываем хеш для ипадреса и версии браузера
		$_SESSION['login_id']=OCIResult($q,"ID");
		$_SESSION['admin']=OCIResult($q,"IRS_ADMIN");
		$_SESSION['rep_period']=OCIResult($q,"REP_PERIOD");	
		$_SESSION['allow_records']=OCIResult($q,"ALLOW_RECORDS");
		$_SESSION['allow_noreport']=OCIResult($q,"ALLOW_NOREPORT");
		$_SESSION['allow_nocall']=OCIResult($q,"ALLOW_NOCALL");
		$_SESSION['allow_record_full']=OCIResult($q,"ALLOW_RECORD_FULL");
		$_SESSION['allow_view_all_reports']=OCIResult($q,"ALLOW_VIEW_ALL_REPORTS");
		$_SESSION['last_url']='';
		
		$q_upd=OCIParse($c,"update sc_login set last_activity=sysdate where id='".$_SESSION['login_id']."'");
		OCIExecute($q_upd);
		OCICommit($c);		
		
		return true;
	} 
	else { //не удачная попытка входа
        session_destroy(); //удаляем все сессионные переменные
        setcookie('usrpss'); //удаляем кукис с паролем из браузера
        unset($_COOKIE['usrpss']); //и из текущего сеанса
        return false;	
	}
}