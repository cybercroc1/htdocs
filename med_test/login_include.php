<?php
require_once "funct.php";
require_once "med/conn_string.cfg.php";
require_once "check_ip.php";

extract($_REQUEST);

$q_logon_log=OCIParse($c,"insert into USER_LOGON_LOG (date_try,login,pass,ip,user_id,result)
values (sysdate,:login,:pass,'".$_SERVER['REMOTE_ADDR']."',:user_id,:result)");

if (isset($exit)) { //если есть переменная exit, значит запрошен выход с сайта, подключаемся к сесии и дестроим ее
    if (isset($_SESSION['login_id_med'])) {
        GetData::UpdateActivity($_SESSION['login_id_med'],FALSE, TRUE);
		
		//лог
		$log_login='';
		OCIBindByName($q_logon_log,":login",$log_login);
		$log_pass='';
		OCIBindByName($q_logon_log,":pass",$log_pass);
		OCIBindByName($q_logon_log,":user_id",$_SESSION['login_id_med']);
		$log_result='exit';
		OCIBindByName($q_logon_log,":result",$log_result);
		OCIExecute($q_logon_log); OCIcommit($c);
	}
    session_name('medc'); //устанавливает имя сессии
    //session_start(); //создает сесисю с именем medc и кладет ее в cookies, если на компьютере уже есть кукис с таким именем сесии, то подключается к существующей
    session_destroy(); //удаляем сессию
    header("Location:" . $_SERVER['PHP_SELF']); //перезагружаем данную страницу с очисткой get-параметров
}

if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {
    //если пользователь уже авторизован, то можно пропустить авторизацию, и подключить его к существующей сессии
    //АВТОРИЗАЦИЯ:
    if (isset($User) && isset($Pass)) {
        $checkstr = "select ID, ROLE_ID, FIO, IP_ADDR, DATA_ACC, case when ACTIVITY >= sysdate-30/86400 then 'Y' else NULL end ALREADY_LOGINED 
from USERS where DELETED is NULL and LOGIN = '{$User}' and PASSWORD = '{$Pass}'";
        if (DB_OCI) {
            $objParse = OCIParse($c, $checkstr);
            OCIExecute($objParse);
            $result = OCI_Fetch_Row($objParse);
            oci_free_statement($objParse);
        } else {
            $result = mysqli_query($c, $checkstr);
        }
		if ($result and $result['1'] != USER_VIEW
			&& !check_local_network($_SERVER['REMOTE_ADDR'])
			//&& isset($result['3']) && $result['3'] != "::1" && $result['3'] != "127.0.0.1" && $result['3'] != "207.189.31.194" && $result['3'] != "128.72.118.118"
			&& !check_ip($_SERVER['REMOTE_ADDR'],"213.248.20.64") // IP Прокофьевой извне
			)
		{
			//из неизвестных сетей могут заходить только обозреватели	
            session_destroy(); //удаляем все сессионные переменные
            setcookie('login'); //удаляем кукис с логином из браузера
            unset($_COOKIE['login']); //и из текущего сеанса
            setcookie('pass'); //удаляем кукис с паролем из браузера
            unset($_COOKIE['pass']); //и из текущего сеанса
			$err = "Запрещенный IP.".$_SERVER['REMOTE_ADDR'];
		} else if ($result) { //если в БД пользователь найден, то пользователь ввел верные логин и пароль
            setcookie('login', $User, mktime(0, 0, 0, 1, 1, 2030)); //логин и пароль введены верно, этого достаточно, что бы запомнить логин
            if (isset($save_pass)) {
                setcookie('pass', $Pass, mktime(0, 0, 0, 1, 1, 2030)); //если выбрана опция сохранения пароля, устанавливаем кукес с паролем
            } else {
                setcookie('pass'); //иначе удаляем кукис с паролем из браузера
                unset($_COOKIE['pass']); //и из текущего сеанса из сеанса надо удалять потому, что кукис уже прочтен и остался в данном сеансе
            }
            // проверка на то, что пользователь уже залогинен на другом компьютере
            // если дата последней активности пользователя < текущая дата + период подтверждения активности + 15 секунд
            // IP-адрес сравнивать не надо, потому, что может быть включен НАТ
            //if (isset($_SESSION['ip_addr']) && NULL != $_SESSION['ip_addr'] && $_SERVER['REMOTE_ADDR'] != $_SESSION['ip_addr'])
			if ('Y' == $result['5'] /*&& $_SERVER['REMOTE_ADDR'] != $result['3']*/) { //значит пользователь подключен на другом компе.
                session_destroy(); //удаляем все сессионные переменные
                //кукисы с логином и паролем не трогаем, т.к. небыло неправильного ввода пароля
                $err = "Данный пользователь уже подключен на компьютере с адресом ".$result['3']." Попробуйте войти позже.";
			} else { //иначе пользователь авторизован - все в порядке.
                $_SESSION['auth']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']); //создаем сессионную переменную, говорящую о том, что пользователь авторизован, в качестве параметра записываем хеш для ипадреса и версии браузера
				// устанавливаем все сессионные переменные, связанные с настройками пользователя
                $_SESSION['login_id_med'] = $result['0'];
                $_SESSION['user_role'] = $result['1'];
                $_SESSION['login_name'] = $result['2'];
                $_SESSION['ip_addr'] = $result['3'];
                $_SESSION['data_acc'] = $result['4'];
                $_SESSION['reload_at_save'] = FALSE; // только для перезагрузки экрана операторов перехвата заявок
				
				//список прав доступа к данным
				$_SESSION['access']['data_acc']=array();
				$q=OCIParse($c,"select NAME from USER_DATA_ACC where id in ('".str_replace(",","','",$result['4'])."')");
				OCIExecute($q);
				while(OCIFetch($q)) {
					$_SESSION['access']['data_acc'][OCIResult($q,"NAME")]='y';
				}
				
				//Список доступных пользователю отчетов
				$_SESSION['access']['report']=array();
				$q=OCIParse($c,"select distinct r.id,r.SCRIPT_NAME,r.name from CALL_REPORTS r, call_reports_acc a
				where a.report_id(+)=r.id and r.deleted is null
				and (a.user_id='".$_SESSION['login_id_med']."' or ','||r.role_ids||',' like '%,'||'".$_SESSION['user_role']."'||',%') 
				order by r.name");
				OCIExecute($q);
				while(OCIFetch($q)) {
					$_SESSION['access']['report'][OCIResult($q,"ID")]["name"]=OCIResult($q,"NAME");
					$_SESSION['access']['report'][OCIResult($q,"ID")]["script_name"]=OCIResult($q,"SCRIPT_NAME");
				}
                //Список операторов второго шанса
                if (GetData::GetSecondChance() > 0) { // Права доступа к разным данным
                    $_SESSION['sec_chance_arr'] = GetData::$arr_sec_chance;
                    $_SESSION['sec_chance'] = in_array($_SESSION['login_id_med'], GetData::$arr_sec_chance);
                }
                else {
                    $_SESSION['sec_chance_arr'] = array();
                    $_SESSION['sec_chance'] = false;
                }

                if (USER_ADMIN == $_SESSION['user_role'])
                    $_SESSION['admin_med'] = 1;
                else $_SESSION['admin_med'] = 0;

                if (-1 != GetData::GetUserDuty($_SESSION['login_id_med'], NULL))
                    $_SESSION['on_duty_today'] = TRUE;
                else $_SESSION['on_duty_today'] = FALSE;

                if (USER_ADMIN == $_SESSION['user_role'])
                    $tmpstr = "Ввод справочников (Админ)";
                elseif (USER_SUPER == $_SESSION['user_role'])
                    $tmpstr = "Медицина (Супервайзер)";
                elseif (USER_VIEW == $_SESSION['user_role'])
                    $tmpstr = "Медицина (Обозреватель)";
                else $tmpstr = "Входящие заявки (оператор)";
                setcookie('business', $tmpstr, mktime(0,0,0,1,1,2030));

                // обновляем дату последней активности пользователя
                GetData::UpdateActivity($_SESSION['login_id_med'], TRUE, TRUE);
            }
        } else { //если пользователь ввел неверные имя и пароль
            session_destroy(); //удаляем все сессионные переменные
            //setcookie('login'); //удаляем кукис с логином из браузера
            //unset($_COOKIE['login']); //и из текущего сеанса
            setcookie('pass'); //удаляем кукис с паролем из браузера
            unset($_COOKIE['pass']); //и из текущего сеанса
            $err = "Неверное имя или пароль!";
        }
		
		//лог
		if(isset($User)) $log_login=$User; else $log_login=''; 
		OCIBindByName($q_logon_log,":login",$log_login);
		if(isset($Pass)) $log_pass=$Pass; else $log_pass='';
		OCIBindByName($q_logon_log,":pass",$log_pass);
		if(isset($_SESSION['login_id_med'])) $log_user_id=$_SESSION['login_id_med']; else $log_user_id='';
		OCIBindByName($q_logon_log,":user_id",$log_user_id);
		if(isset($err)) $log_result=$err; else $log_result='auth ok';
		OCIBindByName($q_logon_log,":result",$log_result);
		OCIExecute($q_logon_log); OCIcommit($c);
	}
}

//СТРАНИЦА ЛОГИНА
if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) { //Пользователь не авторизован! показываем страницу логина и exit()
// Параметр autocomplete='off' в полях ввода логина и пароля исключает ситуацию, когда глупые браузеры запоминают неверный логин и пароль.
// Логин и пароль должны запоминаться кукисами только в случае успешного ввода.
echo '<!DOCTYPE HTML>
<html>
<head>
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="./billing.css">
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <title>Медицина</title>
    <base href="/">
    <meta name="description" content="Медицина">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>';
    if (isset($err)) echo "<b style='color: red; font-size: large'>" . $err . "</b>";
    echo "<form action='' method='POST' name='login_frm'>
    <table border='0' cellspacing='0' cellpadding='8' align='center'>
        <tr>
            <td colspan=1 style='position: absolute; margin-left: -5em;'><img src='".PATH."/images/ws-logo.png' alt='Wilstream'></td>
            <td colspan=1><img src='".PATH."/images/icon175x175.png' style='padding-left: 2em;' alt='Logo'></td>
        </tr>
        <!--tr><td colspan=2 align='center' width=60px><strong style='color: black; font-size: large;'>Вход</strong></td></tr-->
        <tr>
            <td><p style='color: black; font-weight: bold'>Пользователь</p></td>
            <td><input autocomplete='off' type='text' name='User' value='" . (isset($_COOKIE['login']) ? $_COOKIE['login'] : "") . "' placeholder='Логин'/></td>
        </tr>
        <tr>
            <td><p style='color: black; font-weight: bold'>Пароль</p></td>
            <td><input autocomplete='off' type='password' name='Pass' value='" . (isset($_COOKIE['pass']) ? $_COOKIE['pass'] : '') . "' placeholder='Пароль'/></td>
        </tr>
        <tr><td colspan=2 align='center'>
        <input type=checkbox name='save_pass' " . ((isset($_COOKIE['pass']) && $_COOKIE['pass'] <> '' || isset($save_pass)) ? ' checked' : '') . "/>
         Запомнить пароль
        </td></tr>
        <tr align='center'><td colspan=2 align='center'><input type='submit' name='Enter' value='Войти'></td></tr>
    </table>
    </form>
</body>
</html>";
    exit();
}
//этот блок должен быть на каждой странице, требующей авторизованного доступа: require_once 'med/check_auth.php';
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
//
