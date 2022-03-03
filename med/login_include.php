<?php
require_once "funct.php";
require_once "med/conn_string.cfg.php";
require_once "check_ip.php";

extract($_REQUEST);

$q_logon_log=OCIParse($c,"insert into USER_LOGON_LOG (date_try,login,pass,ip,user_id,result)
values (sysdate,:login,:pass,'".$_SERVER['REMOTE_ADDR']."',:user_id,:result)");

if (isset($exit)) { //���� ���� ���������� exit, ������ �������� ����� � �����, ������������ � ����� � �������� ��
    if (isset($_SESSION['login_id_med'])) {
        GetData::UpdateActivity($_SESSION['login_id_med'],FALSE, TRUE);
		
		//���
		$log_login='';
		OCIBindByName($q_logon_log,":login",$log_login);
		$log_pass='';
		OCIBindByName($q_logon_log,":pass",$log_pass);
		OCIBindByName($q_logon_log,":user_id",$_SESSION['login_id_med']);
		$log_result='exit';
		OCIBindByName($q_logon_log,":result",$log_result);
		OCIExecute($q_logon_log); OCIcommit($c);
	}
    session_name('medc'); //������������� ��� ������
    //session_start(); //������� ������ � ������ medc � ������ �� � cookies, ���� �� ���������� ��� ���� ����� � ����� ������ �����, �� ������������ � ������������
    session_destroy(); //������� ������
    header("Location:" . $_SERVER['PHP_SELF']); //������������� ������ �������� � �������� get-����������
}

if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {
    //���� ������������ ��� �����������, �� ����� ���������� �����������, � ���������� ��� � ������������ ������
    //�����������:
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
			&& !check_ip($_SERVER['REMOTE_ADDR'],"213.248.20.64") // IP ����������� �����
			)
		{
			//�� ����������� ����� ����� �������� ������ ������������	
            session_destroy(); //������� ��� ���������� ����������
            setcookie('login'); //������� ����� � ������� �� ��������
            unset($_COOKIE['login']); //� �� �������� ������
            setcookie('pass'); //������� ����� � ������� �� ��������
            unset($_COOKIE['pass']); //� �� �������� ������
			$err = "����������� IP.".$_SERVER['REMOTE_ADDR'];
		} else if ($result) { //���� � �� ������������ ������, �� ������������ ���� ������ ����� � ������
            setcookie('login', $User, mktime(0, 0, 0, 1, 1, 2030)); //����� � ������ ������� �����, ����� ����������, ��� �� ��������� �����
            if (isset($save_pass)) {
                setcookie('pass', $Pass, mktime(0, 0, 0, 1, 1, 2030)); //���� ������� ����� ���������� ������, ������������� ����� � �������
            } else {
                setcookie('pass'); //����� ������� ����� � ������� �� ��������
                unset($_COOKIE['pass']); //� �� �������� ������ �� ������ ���� ������� ������, ��� ����� ��� ������� � ������� � ������ ������
            }
            // �������� �� ��, ��� ������������ ��� ��������� �� ������ ����������
            // ���� ���� ��������� ���������� ������������ < ������� ���� + ������ ������������� ���������� + 15 ������
            // IP-����� ���������� �� ����, ������, ��� ����� ���� ������� ���
            //if (isset($_SESSION['ip_addr']) && NULL != $_SESSION['ip_addr'] && $_SERVER['REMOTE_ADDR'] != $_SESSION['ip_addr'])
			if ('Y' == $result['5'] /*&& $_SERVER['REMOTE_ADDR'] != $result['3']*/) { //������ ������������ ��������� �� ������ �����.
                session_destroy(); //������� ��� ���������� ����������
                //������ � ������� � ������� �� �������, �.�. ������ ������������� ����� ������
                $err = "������ ������������ ��� ��������� �� ���������� � ������� ".$result['3']." ���������� ����� �����.";
			} else { //����� ������������ ����������� - ��� � �������.
                $_SESSION['auth']=md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']); //������� ���������� ����������, ��������� � ���, ��� ������������ �����������, � �������� ��������� ���������� ��� ��� �������� � ������ ��������
				// ������������� ��� ���������� ����������, ��������� � ����������� ������������
                $_SESSION['login_id_med'] = $result['0'];
                $_SESSION['user_role'] = $result['1'];
                $_SESSION['login_name'] = $result['2'];
                $_SESSION['ip_addr'] = $result['3'];
                $_SESSION['data_acc'] = $result['4'];
                $_SESSION['reload_at_save'] = FALSE; // ������ ��� ������������ ������ ���������� ��������� ������
				
				//������ ���� ������� � ������
				$_SESSION['access']['data_acc']=array();
				$q=OCIParse($c,"select NAME from USER_DATA_ACC where id in ('".str_replace(",","','",$result['4'])."')");
				OCIExecute($q);
				while(OCIFetch($q)) {
					$_SESSION['access']['data_acc'][OCIResult($q,"NAME")]='y';
				}
				
				//������ ��������� ������������ �������
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
                //������ ���������� ������� �����
                if (GetData::GetSecondChance() > 0) { // ����� ������� � ������ ������
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
                    $tmpstr = "���� ������������ (�����)";
                elseif (USER_SUPER == $_SESSION['user_role'])
                    $tmpstr = "�������� (�����������)";
                elseif (USER_VIEW == $_SESSION['user_role'])
                    $tmpstr = "�������� (������������)";
                else $tmpstr = "�������� ������ (��������)";
                setcookie('business', $tmpstr, mktime(0,0,0,1,1,2030));

                // ��������� ���� ��������� ���������� ������������
                GetData::UpdateActivity($_SESSION['login_id_med'], TRUE, TRUE);
            }
        } else { //���� ������������ ���� �������� ��� � ������
            session_destroy(); //������� ��� ���������� ����������
            //setcookie('login'); //������� ����� � ������� �� ��������
            //unset($_COOKIE['login']); //� �� �������� ������
            setcookie('pass'); //������� ����� � ������� �� ��������
            unset($_COOKIE['pass']); //� �� �������� ������
            $err = "�������� ��� ��� ������!";
        }
		
		//���
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

//�������� ������
if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) { //������������ �� �����������! ���������� �������� ������ � exit()
// �������� autocomplete='off' � ����� ����� ������ � ������ ��������� ��������, ����� ������ �������� ���������� �������� ����� � ������.
// ����� � ������ ������ ������������ �������� ������ � ������ ��������� �����.
echo '<!DOCTYPE HTML>
<html>
<head>
    <link rel="shortcut icon" href="./favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" type="text/css" href="./billing.css">
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <title>��������</title>
    <base href="/">
    <meta name="description" content="��������">
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
        <!--tr><td colspan=2 align='center' width=60px><strong style='color: black; font-size: large;'>����</strong></td></tr-->
        <tr>
            <td><p style='color: black; font-weight: bold'>������������</p></td>
            <td><input autocomplete='off' type='text' name='User' value='" . (isset($_COOKIE['login']) ? $_COOKIE['login'] : "") . "' placeholder='�����'/></td>
        </tr>
        <tr>
            <td><p style='color: black; font-weight: bold'>������</p></td>
            <td><input autocomplete='off' type='password' name='Pass' value='" . (isset($_COOKIE['pass']) ? $_COOKIE['pass'] : '') . "' placeholder='������'/></td>
        </tr>
        <tr><td colspan=2 align='center'>
        <input type=checkbox name='save_pass' " . ((isset($_COOKIE['pass']) && $_COOKIE['pass'] <> '' || isset($save_pass)) ? ' checked' : '') . "/>
         ��������� ������
        </td></tr>
        <tr align='center'><td colspan=2 align='center'><input type='submit' name='Enter' value='�����'></td></tr>
    </table>
    </form>
</body>
</html>";
    exit();
}
//���� ���� ������ ���� �� ������ ��������, ��������� ��������������� �������: require_once 'med/check_auth.php';
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>������ ��������</b>"; exit();}
//
