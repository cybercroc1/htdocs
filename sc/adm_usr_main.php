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
<script>
function new_pass_gen() {
	try {
		xml = new ActiveXObject("Msxml2.XMLHTTP");
	} 
	catch (e) {
		try {
			xml = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		catch (E) {
			xml = false;
		}
	}
	if (!xml && typeof XMLHttpRequest!='undefined') {
		xml = new XMLHttpRequest();
	}
	//xml.open('GET', 'new_pass_gen.php?rand='+Math.random(), false);
	//?rand='+Math.random() - �������� ������������, ��� �� ��������� �����������
	xml.open('POST', 'new_pass_gen.php', false);
	xml.send("");
	var response=xml.responseText;
	document.all.password.value=response;
}
</script>
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");
include("pass_gen.php");

//�������� ������������
if (isset($del_usr)) {
	$del=OCIParse($c,"delete from sc_login where id='".$login_id."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
	$login_id='';
	$_SESSION['edit_login']['id']='';
	echo "<script>parent.adm_usr_fr1.location.reload();</script>";
}
//

//���������� ������������
if (isset($save) or isset($send)) {
$pass_check=pass_check($password);
if($pass_check<>"OK") {
	echo "<font color=red><b>������: �������� ��������. �������: ".iconv("utf-8","windows-1251",$pass_check)."</b></font><br>";
}
else {
	if (isset($send) and $email_tmp<>'') {
		//include("../../sc_conf/sc_smtp");
		include("sc/sc_smtp_conf.php"); //���� �������� SMTP
		include("send_email.php");		
		include("sc/sc_adm_url.php");
		if($lk_type_tmp=='old_cabinet') $lk_url_tmp=$adm_url;
		else if($lk_type_tmp=='new_cabinet') $lk_url_tmp=$lk_url;
		$mess="��� ������� � ������� ������� �� ���������: <a href=".$lk_url_tmp.">".$lk_url_tmp."</a><br>
		<b>������������: </b>".$login."<br>
		<b>������: </b>".$password."<hr>";
		$headers="MIME-Version: 1.0 \n";
		$headers.="Content-Type: text/html; charset=\"windows-1251\"\n";
		//send($smtp_srv, $email, $from_email, $from_name='', "������ � ������� ����-������ Wilstream",$mess,$headers);
		
				$res=send_email(
			$smtp_server, 
			$smtp_port,
			$smtp_auth_login, 
			$smtp_auth_pass, 
			$to_name='', 
			$to_email=$email_tmp, 
			$smtp_from_name, 
			$smtp_from_email, 
			$reply_to_name='', 
			$reply_to_email='' ,
			"������ � ������� ����-������ Wilstream",
			$mess,
			$headers, 
			$debug=''
		);
		if (substr($res,0,2)=='OK') {$alert= "����������"; echo "<script language='javascript'>alert('".$alert."')</script>";}
		else echo "<script language='javascript'>alert('".$res."')</script>";
		
	}
	if(isset($allow_records)) $allow_records=1; else $allow_records='';
	if(isset($allow_noreport)) $allow_noreport=1; else $allow_noreport='';
	if(isset($allow_record_full)) $allow_record_full=1; else $allow_record_full='';
	if(isset($allow_nocall)) $allow_nocall=1; else $allow_nocall='';
	if ($login_id=='') {
	$q=OCIParse($c,"select seq_login_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_login_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_login (id,login,password,email_tmp,description,rep_period,f,i,allow_records,allow_noreport,allow_record_full,allow_nocall,lk_type_tmp,emails)
	values ('".$new_login_id."','".$login."','".$password."','".$email_tmp."','".$description."','".$rep_period."','".$f."','".$i."','".$allow_records."','".$allow_noreport."',
	'".$allow_record_full."','".$allow_nocall."','".$lk_type_tmp."','".trim($emails)."')");
		if (OCIExecute($ins,OCI_DEFAULT)) {
			OCICommit($c);
			$login_id=$new_login_id;
			$_SESSION['edit_login']['id']=$login_id;
			$_SESSION['edit_login']['f']=$f;
			$_SESSION['edit_login']['i']=$i;
			$_SESSION['edit_login']['login']=$login;
			$_SESSION['edit_login']['desc']=$description;
			$_SESSION['edit_login']['allow_records']=$allow_records;
			$_SESSION['edit_login']['allow_noreport']=$allow_noreport;
			$_SESSION['edit_login']['allow_record_full']=$allow_record_full;
			$_SESSION['edit_login']['allow_nocall']=$allow_nocall;
			$_SESSION['edit_login']['lk_type_tmp']=$lk_type_tmp;
			$_SESSION['edit_login']['emails']=$emails;
			echo "<script>parent.adm_usr_fr1.location.reload();</script>";
		} 
		else {
		echo "<font color=red>1������! ������������ � ����� ������ � ������� ��� ����������!</font>";
		}
	}
	else {
	$upd=OCIParse($c,"update sc_login set login='".$login."', password='".$password."', email_tmp='".$email_tmp."', 
	description='".$description."', rep_period='".$rep_period."',f='".$f."',i='".$i."',allow_records='".$allow_records."',allow_noreport='".$allow_noreport."',
	allow_record_full='".$allow_record_full."', allow_nocall='".$allow_nocall."', lk_type_tmp='".$lk_type_tmp."', emails='".trim($emails)."'
	where id='".$login_id."'");
		if (@OCIExecute($upd,OCI_DEFAULT)) {
			OCICommit($c);
			echo "<script>parent.adm_usr_fr1.location.reload();</script>";
		}
		else {
		echo "<font color=red>2������! ������������ � ����� ������ � ������� ��� ����������!</font>";
		}
	}
}}
//

$_SESSION['adm_usr_last_url']='adm_usr_main.php';

//==================

if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id; 
else if(!isset($_SESSION['edit_login']['id'])) exit();

$q=OCIParse($c,"select * from sc_login where id='".$_SESSION['edit_login']['id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$_SESSION['edit_login']['id']=OCIResult($q,"ID");
$_SESSION['edit_login']['f']=OCIResult($q,"F");
$_SESSION['edit_login']['i']=OCIResult($q,"I");
$_SESSION['edit_login']['login']=OCIResult($q,"LOGIN");
$_SESSION['edit_login']['desc']=OCIResult($q,"DESCRIPTION");
$_SESSION['edit_login']['allow_records']=OCIResult($q,"ALLOW_RECORDS");
$_SESSION['edit_login']['allow_noreport']=OCIResult($q,"ALLOW_NOREPORT");
$_SESSION['edit_login']['allow_record_full']=OCIResult($q,"ALLOW_RECORD_FULL");
$_SESSION['edit_login']['allow_nocall']=OCIResult($q,"ALLOW_NOCALL");
$_SESSION['edit_login']['lk_type_tmp']=OCIResult($q,"LK_TYPE_TMP");
$_SESSION['edit_login']['emails']=OCIResult($q,"EMAILS");
$password=OCIResult($q,"PASSWORD");
$email_tmp=OCIResult($q,"EMAIL_TMP");
$rep_period=OCIResult($q,"REP_PERIOD");	

if($_SESSION['edit_login']['id']<>'') echo "<font size=4>".$_SESSION['edit_login']['login']."</font>";
else echo "<font size=4>������� ������������</font>";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_prj_frame.php>�������</a> ";
if ($_SESSION['edit_login']['id']<>'') echo " | <a href=adm_usr_comrep_frame.php>����� ������</a> | ";

//==================

echo "<form method=post>"; //POST �������� �����������!
echo "<input type=hidden name=login_id value='".$_SESSION['edit_login']['id']."'>";

echo "<hr>";
//

if($password=='') $password=pass_gen();

echo "<table><tr><td valign=top>";

echo "<table>";

echo "<tr><td><font size=3><b>�����:</b></font></td><td><input type=text name=login value=\"".$_SESSION['edit_login']['login']."\"></td></tr>";
echo "<tr><td><font size=3><b>������:</b></font></td><td><input type=text name=password value=\"".$password."\"> <input type=button value='����� ������' onclick='new_pass_gen()'></td></tr>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "<tr><td><font size=3><b>�������:</b></font></td><td><input type=text name=f value=\"".$_SESSION['edit_login']['f']."\"></td></tr>";
echo "<tr><td><font size=3><b>���:</b></font></td><td><input type=text name=i value=\"".$_SESSION['edit_login']['i']."\"></td></tr>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "<tr><td><font size=3><b>EMAIL(�):</b></font></td><td><textarea name=emails rows=2 cols=50>".htmlspecialchars($_SESSION['edit_login']['emails'])."</textarea>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "<tr><td><font size=3><b>��������:</b></font></td><td><textarea name=description rows=3 cols=50>".htmlspecialchars($_SESSION['edit_login']['desc'])."</textarea>
</td></tr>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "<tr><td colspan=2><font size=3><b>��������� ������ �� E-Mail: </b></font><input type=text name=email_tmp value=\"".$email_tmp."\">
 <select name=lk_type_tmp>
 <option value=old_cabinet".($_SESSION['edit_login']['lk_type_tmp']=='old_cabinet'?' selected':'').">������ �������</option>
 <option value=new_cabinet".($_SESSION['edit_login']['lk_type_tmp']=='new_cabinet'?' selected':'').">����� �������</option>
 </select> 
<input type=submit name=send value=��������� onclick=\"javascript:if(!chk_email()){alert('email �������');return false;}\"></td></tr>";
echo "<tr><td colspan=2><hr></td></tr>";
echo "<tr><td><font size=3><b>������ � ������� ��:</b></font></td><td><select name=rep_period>
<option>".$rep_period."</option>
<option>���� ������</option>
<option>1 ����</option>
<option>2 ���</option>
<option>3 ���</option>
<option>4 ���</option>
<option>5 ����</option>
<option>6 ����</option>
<option>1 ������</option>
<option>2 ������</option>
<option>3 ������</option>
<option>1 �����</option>
<option>2 ������</option>
<option>3 ������</option>
<option>4 ������</option>
<option>5 �������</option>
<option value='6 �������'>�������</option>
<option value='12 �������'>���</option>
</td></tr>";

echo "<tr><td colspan=2><font size=3><b>��������� ������������ ������<input type=checkbox name=allow_records".($_SESSION['edit_login']['allow_records']==1?" checked":"").">
 ������ � ������ ���������� �� ������ <input type=checkbox name=allow_record_full".($_SESSION['edit_login']['allow_record_full']==1?" checked":"")."></b></font></td></tr>";
echo "<tr><td colspan=2><font size=3><b>��������� ������ � ������� ��� ������</b></font><input type=checkbox name=allow_noreport".($_SESSION['edit_login']['allow_noreport']==1?" checked":"")."></td></tr>";
echo "<tr><td colspan=2><font size=3><b>��������� ������ � ������� ��� ������</b></font><input type=checkbox name=allow_nocall".($_SESSION['edit_login']['id']<>''?($_SESSION['edit_login']['allow_nocall']==1?" checked":""):' checked')."></td></tr>";

echo "</table></td>";

echo "<td>";

echo "</td></tr>";
echo "</table>";
echo "<input type=submit name=save value=���������>";	

echo "</form>";

//������� �������� ����� �����
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
				$alert="������: ".$err; 
				break;
				}
			}		
		$stream_info=stream_get_meta_data($fp);
		if($stream_info['timed_out']==1) {
			$err=$server." - ������� ����� �������� SMTP ������"; 
			$alert="������: ".$err;
		}
		fclose($fp);
		if (!isset($err)) $alert= "����������";
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
