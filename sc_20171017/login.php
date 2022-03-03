<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
extract($_REQUEST);
include("../../sc_conf/sc_title");
if (!isset($_SESSION['login_id']) or !isset($refresh)) {
	session_destroy();

	if (isset($User_sc_1905) and isset($Pass_sc_1905)) {
		include("../../sc_conf/sc_conn_string");

		$q=OCIParse($c,"select id,
		case 
when rep_period='Весь период' then null
when substr(rep_period,instr(rep_period,' ')+1,1)='Д' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1),'DD.MM.YYYY')
when substr(rep_period,instr(rep_period,' ')+1,1)='Н' then to_char(sysdate-substr(rep_period,0,instr(rep_period,' ')-1)*7,'DD.MM.YYYY')
when substr(rep_period,instr(rep_period,' ')+1,1)='М' then to_char(add_months(sysdate,-(substr(rep_period,0,instr(rep_period,' ')-1))),'DD.MM.YYYY')
end rep_period, irs_admin, vsr_admin
		 from sc_login where login='".$User_sc_1905."' and password='".$Pass_sc_1905."' and login is not null and password is not null");
		OCIExecute($q, OCI_DEFAULT);
		if (OCIFetch($q)) {
			session_start();
			$auth=1; 
			$_SESSION['login_id']=OCIResult($q,"ID");
			$_SESSION['admin']=OCIResult($q,"IRS_ADMIN");
			$_SESSION['vsr_admin']=OCIResult($q,"VSR_ADMIN");			
			$_SESSION['rep_period']=OCIResult($q,"REP_PERIOD");			
		} else {$auth=0;}
	}
	if (!isset($User_sc_1905) or !isset($Pass_sc_1905) or $auth==0) {
		echo "<html>
		<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=windows-1251\">
		<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7'>
		
		<title>".$sc_title."</title>
		</head>";
		if (isset($auth) and $auth==0) echo "<font color=red>Не верное имя или пароль</font>";
		echo "<body>
		<div align=\"center\"><center>
		<h4>Колл-центр Wilstream</h4>
		<table cellSpacing=\"0\" cellPadding=\"0\" width=\"778\" border=\"0\">
		  <tr>
		    <td width=\"759\"><p align=\"center\"><img src=\"logo.gif\" width=\"200\" height=\"91\"
		    alt=\"logo.gif (72568 bytes)\"></td>
		  </tr>
		</table>
		</center></div>

		<form name=login_sc_1905 action=login.php method=\"POST\">
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
//if (!isset($i)) {
if (isset($refresh)) unset($_SESSION['i']);
	$i=0;
//	unset($_SESSION['i']);
//	unset($_SESSION['project_id']);
//	unset($_SESSION['project_name']);
//	unset($_SESSION['admin']);
//	unset($_SESSION['view_sc']);
//	unset($_SESSION['view_rep']);
//	unset($_SESSION['ch_sc']);
//	unset($_SESSION['ch_form']);
//	unset($_SESSION['ch_email']);
//	unset($_SESSION['view_billing']);
//	unset($_SESSION['fr_width']);

	include("../../sc_conf/sc_conn_string");

	$q_txt='';
	$q_num=0;
	if (isset($_SESSION['admin']) and $_SESSION['admin']==1) {
	$q_txt.="select p.id,
       p.name,
       p.tree_width,
       '01.01.2000 00:00:00' start_date,
       '1' view_rep,
       '1' view_sc,
       '1' ch_sc,
       '1' ch_form,
       '1' ch_email,
       '1' view_billing,
       null vsr_billing,
	   '1' view_sms_log
  from sc_projects p
 where (p.type = 'irs' or p.type is null) and p.hidden is null";
	$q_num++;
	}
	if (isset($_SESSION['vsr_admin']) and $_SESSION['vsr_admin']==1) {
		if ($q_num>=1) $q_txt.=" union ";
	$q_txt.="select p.id,
       p.name,
       p.tree_width,
       '01.01.2000 00:00:00' start_date,
       null view_rep,
       null view_sc,
       null ch_sc,
       null ch_form,
       null ch_email,
       null view_billing,
       '1' vsr_billing,
	   null view_sms_log
  from sc_projects p
 where p.type = 'vsr' and p.hidden is null";	
	$q_num++;
	}
	$q_txt.=" order by name";
	if ($q_num==0) {
	$q_txt="select p.id,p.name,p.tree_width,to_char(p.start_date,'DD.MM.YYYY HH24:MI:SS') start_date,r.view_rep,r.view_sc,r.ch_sc,r.ch_form,r.ch_email,r.view_billing,r.vsr_billing,r.view_sms_log from sc_role r, sc_projects p
	where r.login_id='".$_SESSION['login_id']."' and r.project_id=p.id order by p.name";}
	$q=OCIParse($c,$q_txt);

	OCIExecute($q, OCI_DEFAULT);
	while(OCIFetch($q)) {
	$_SESSION['project_id'][$i]=OCIResult($q,"ID");
	$_SESSION['project_name'][$i]=OCIResult($q,"NAME");
	$_SESSION['start_date'][$i]=OCIResult($q,"START_DATE");
	if (OCIResult($q,"TREE_WIDTH")==NULL) $_SESSION['fr_width'][$i]=200;
	else $_SESSION['fr_width'][$i]=OCIResult($q,"TREE_WIDTH");
	$_SESSION['view_sc'][$i]=OCIResult($q,"VIEW_SC");
	$_SESSION['view_rep'][$i]=OCIResult($q,"VIEW_REP");
	$_SESSION['ch_sc'][$i]=OCIResult($q,"CH_SC");
	$_SESSION['ch_form'][$i]=OCIResult($q,"CH_FORM");
	$_SESSION['ch_email'][$i]=OCIResult($q,"CH_EMAIL");
	$_SESSION['view_billing'][$i]=OCIResult($q,"VIEW_BILLING");
	$_SESSION['view_sms_log'][$i]=OCIResult($q,"VIEW_SMS_LOG");
	$i++;
	}
//	if (isset($_SESSION['project_id']) and count($_SESSION['project_id'])==1) {$_SESSION['i']=0; //$_SESSION['fr_w']=$_SESSION['fr_width'][$_SESSION['i']];}	
//	if (isset($_SESSION['project_id']) and count($_SESSION['project_id'])>1) $_SESSION['fr_w']='200';
//}
//else {
//$_SESSION['i']=$i;
//$_SESSION['fr_w']=$_SESSION['fr_width'][$_SESSION['i']];
//}

//ФРЕЙМ
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\"
   \"http://www.w3.org/TR/REC-html40/frameset.dtd\">
<HTML>
<HEAD>
<TITLE>".$sc_title."</TITLE>
</HEAD>

<frameset frameborder=no rows='25,*'>";

if(!isset($_SESSION['project_id'])) echo "<frame scrolling=no noresize name=fr0 src=menu.php?blank=2>";
else echo "<frame scrolling=no noresize name=fr0 src=menu.php>";

//if(!isset($_SESSION['i'])) echo "<frame name=fr12 src=menu.php?blank=1>";
//else if($_SESSION['view_rep'][$_SESSION['i']]=='1') echo "<frame name=fr12 src=rep_fr.php>";
//else if($_SESSION['ch_sc'][$_SESSION['i']]=='1') echo "<frame name=fr12 src=edit_sc.php>";
//else if($_SESSION['ch_form'][$_SESSION['i']]=='1') echo "<frame name=fr12 src=edit_form.php>";
//else if($_SESSION['ch_email'][$_SESSION['i']]=='1') echo "<frame name=fr12 src=edit_email.php>";
//else if($_SESSION['view_billing'][$_SESSION['i']]=='1') echo "<frame name=fr12 src=billing.php>";
//else 
echo "<frame name=fr12 src='blank.htm'>";
echo "</frameset>   

</HTML>"; 
?>
