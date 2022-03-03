<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
extract($_REQUEST);
include("sc/sc_title.php");

echo "<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7'>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">";
if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru') {
	echo "<title>Контакт-Центр Лайт</title>
	<link rel='icon' href='cclight-favicon.ico'>
	<link rel='apple-touch-icon' sizes='180x180' href='cclight-favico-150x150.png'>";
}
else {
	echo "<title>".$sc_title."</title>
	<link rel='icon' href='wilstream-favicon.ico'>";
}


echo "</head>";

if (!isset($_SESSION['login_id']) or !isset($refresh)) {
	session_destroy();

	if (isset($User_sc_1905) and isset($Pass_sc_1905)) {
		include("sc/sc_conn_string.php");

		$q=OCIParse($c,"select id,
		case 
when rep_period='Весь период' then null
when substr(rep_period,instr(rep_period,' ')+1,1)='Д' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1),'DD.MM.YYYY')
when substr(rep_period,instr(rep_period,' ')+1,1)='Н' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1)*7,'DD.MM.YYYY')
when substr(rep_period,instr(rep_period,' ')+1,1)='М' then to_char(add_months(sysdate,-(substr(rep_period,0,instr(rep_period,' ')-1))),'DD.MM.YYYY')
end rep_period, irs_admin, vsr_admin,allow_records,allow_noreport,allow_nocall,allow_record_full,allow_view_all_reports,allow_view_all_sc
		 from sc_login where login='".$User_sc_1905."' and password='".$Pass_sc_1905."' and login is not null and password is not null and disabled is null");
		OCIExecute($q, OCI_DEFAULT);
		if (OCIFetch($q)) {
			session_start();
			$auth=1; 
			$_SESSION['login_id']=OCIResult($q,"ID");
			$_SESSION['admin']=OCIResult($q,"IRS_ADMIN");
			$_SESSION['vsr_admin']=OCIResult($q,"VSR_ADMIN");			
			$_SESSION['rep_period']=OCIResult($q,"REP_PERIOD");	
			$_SESSION['allow_records']=OCIResult($q,"ALLOW_RECORDS");
			$_SESSION['allow_noreport']=OCIResult($q,"ALLOW_NOREPORT");
			$_SESSION['allow_nocall']=OCIResult($q,"ALLOW_NOCALL");
			$_SESSION['allow_record_full']=OCIResult($q,"ALLOW_RECORD_FULL");
			$_SESSION['allow_view_all_reports']=OCIResult($q,"ALLOW_VIEW_ALL_REPORTS");
			$_SESSION['allow_view_all_sc']=OCIResult($q,"ALLOW_VIEW_ALL_SC");
			//проверка прав доступа к отчетам
			/*if($_SESSION['admin']==1) {
				$_SESSION['view_rep']=1;
			} */
			/*else { 
				$q_access_form=OCIParse($c,"select count(*) from SC_ACCESS_FRM_REP r
				where r.login_id='".$_SESSION['login_id']."'");
				OCIExecute($q_access_form,OCI_DEFAULT);
				OCIFetch($q_access_form);
				if(OCIResult($q_access_form)>0) $_SESSION['view_rep']=1; else $_SESSION['view_rep']='';
			}*/
			$q_upd=OCIParse($c,"update sc_login set last_activity=sysdate where id='".$_SESSION['login_id']."'");
			OCIExecute($q_upd);
			OCICommit($c);
		} else {$auth=0;}
	}
	
	if (!isset($User_sc_1905) or !isset($Pass_sc_1905) or $auth==0) {
		if (isset($auth) and $auth==0) echo "<font color=red>Не верное имя или пароль</font>";
		echo "<body>
		<div align=\"center\"><center>";
		if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru') {
			echo "
			<table cellSpacing=\"0\" cellPadding=\"0\" width=\"778\" border=\"0\">
			<tr>
				<td width=\"759\"><p align=\"center\"><img src=\"logo_cclight.png\"
				alt=\"logo_cclight.png (72568 bytes)\"></td>
			</tr>
			</table>";			
		}
		else {
			echo "<h4>Колл-центр Wilstream</h4>
			<table cellSpacing=\"0\" cellPadding=\"0\" width=\"778\" border=\"0\">
			<tr>
				<td width=\"759\"><p align=\"center\"><img src=\"logo.gif\" width=\"200\" height=\"91\"
				alt=\"logo.gif (72568 bytes)\"></td>
			</tr>
			</table>";
		}
		echo "</center></div>";
		echo "<form name=login_sc_1905 action=login.php method=\"POST\">
		  <div align=\"center\"><center><table border=\"0\" width=\"778\" height=\"29\" 
		  cellspacing=\"1\" cellpadding=\"0\">
		</table>
		  </center></div><div align=\"center\"><center><table border=\"0\" width=\"778\" 
		  cellspacing=\"0\" cellpadding=\"0\" height=\"137\">
		   <tr>
		      <td width=\"20%\" height=\"25\"></td>
		      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пользователь</strong></font></td>
		      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"text\" name=\"User_sc_1905\" size=\"20\"></td>
		      <td width=\"20%\" height=\"25\"></td>
		      <td width=\"20%\" height=\"25\"></td>
		    </tr>
		    <tr>
		      <td width=\"20%\" height=\"25\"></td>
		      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пароль</strong></font></td>
		      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"password\" name=\"Pass_sc_1905\" size=\"20\"></td>
		      <td width=\"20%\" height=\"25\"></td>
		      <td width=\"20%\" height=\"25\"><div align=\"center\"></div></td>
		    </tr>
		    <tr align=\"center\">
		      <td width=\"20%\" height=\"65\"></td>
		      <td width=\"20%\" height=\"65\"></td>
		      <td width=\"20%\" align=\"center\" height=\"65\"><input type=\"submit\" value=\"Вход\"></td>
		      <td width=\"20%\" height=\"65\"></td>
		      <td width=\"20%\" height=\"65\">&nbsp;<p></td>
		    </tr>
		  </table>
		</form>
		</body>
		</html>";
	exit();
	}
}
if (isset($refresh)) unset($_SESSION['i']);
	$i=0;

//ФРЕЙМ
echo "<frameset frameborder=no rows='25,*'>";

echo "<frame scrolling=no noresize name=fr0 src=menu.php>";
echo "<frame name=fr12 src='blank.htm'>";
echo "</frameset>   

</HTML>"; 
?>
