<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>������������</title>
</head>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>�� ���������� ����!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

if(isset($user_id) and $user_id<>'') {
	//���������� � ������������
	if($new_role_id=='') {//������� ��������� �����������
		$q_user=OCIParse($c,"select 
		t.look,t.solution,t.redirect,t.eval,t.admin,t.create_new,t.deny_close,t.rep_stat,t.registrar, --�����
		t.send email_new,t.email_coment,t.email_redir,t.email_prisv,t.email_ready,t.email_close, --��������� ������� �� �����
		t.sms_new,t.sms_redir,t.sms_prisv,t.sms_ready,t.sms_close --��������� ������� �� ���
		from SUP_USER t where t.id=".$user_id);		
	}
	else {
		$q_user=OCIParse($c,"select 
	    t.look,t.solution,t.redirect,t.eval,t.admin,t.create_new,t.deny_close,t.rep_stat,t.registrar, --�����
	    t.send email_new,t.email_coment,t.email_redir,t.email_prisv,t.email_ready,t.email_close, --��������� ������� �� �����
	    t.sms_new,t.sms_redir,t.sms_prisv,t.sms_ready,t.sms_close --��������� ������� �� ���
	    from sup_role_pattern t where t.id=".$new_role_id);			
	}
	OCIExecute($q_user,OCI_DEFAULT);
	OCIFetch($q_user);	
	$role_HTML='';	
	
	$role_HTML.="<table><tr>"; 
	$role_HTML.="<td valign=top>";
		$role_HTML.="<table class=white_table><tr><th colspan>����������</th></tr>";
		$role_HTML.="<tr><td><input name=look type=checkbox value='y'".(OCIResult($q_user,"LOOK")=='y'?' checked':'')."><b>������������</b></input><br><i>��������� �������� �� ����� �������� � �������</i></td></tr>";
		$role_HTML.="<tr><td><input name=solution type=checkbox value='y'".(OCIResult($q_user,"SOLUTION")=='y'?' checked':'')."><b>�����������</b></input><br><i>��������� �����������, �������������, ��������� ������</i></td></tr>";
		$role_HTML.="<tr><td><input name=redirect type=checkbox value='y'".(OCIResult($q_user,"REDIRECT")=='y'?' checked':'')."><b>����������</b></input><br><i>��������� ���������������� ������</i></td></tr>";
		$role_HTML.="<tr><td><input name=eval type=checkbox value='y'".(OCIResult($q_user,"EVAL")=='y'?' checked':'')."><b>�������</b></input><br><i>��������� ������� ������</i></td></tr>";
		$role_HTML.="<tr><td><input name=admin type=checkbox value='y'".(OCIResult($q_user,"ADMIN")=='y'?' checked':'')."><b>�����</b></input><br><i>�������������� �����, �������, �������������</i></td></tr>";
		$role_HTML.="<tr><td><input name=create_new type=checkbox value='y'".(OCIResult($q_user,"CREATE_NEW")=='y'?' checked':'')."><b>���������</b></input><br><i>��������� ��������� ������</i></td></tr>";
		$role_HTML.="<tr><td><input name=deny_close type=checkbox value='y'".(OCIResult($q_user,"DENY_CLOSE")=='y'?' checked':'')."><b>��������� ���������</b></input><br><i>��������� ���������� ��������� ������</i></td></tr>";		
		$role_HTML.="<tr><td><input name=rep_stat type=checkbox value='y'".(OCIResult($q_user,"REP_STAT")=='y'?' checked':'')."><b>���������</b></input><br><i>��������� ������ � ����������</i></td></tr>";
		$role_HTML.="<tr><td><input name=registrar type=checkbox value='y'".(OCIResult($q_user,"REGISTRAR")=='y'?' checked':'')."><b>�����������</b></input><br><i>��������� �������������� ����� �������������</i></td></tr>";												
		$role_HTML.="</table>";
	$role_HTML.="</td>";
	$role_HTML.="<td valign=top>";
		$role_HTML.="<table class=white_table><tr><th colspan=3>���������� �����������:</th></tr>";
		$role_HTML.="<tr><th></th><th>email</th><th>���</th>";

		$role_HTML.="<tr><th>������� ������</th><td><input name=email_new type=checkbox value='y'".(OCIResult($q_user,"EMAIL_NEW")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td><input name=sms_new type=checkbox value='y'".(OCIResult($q_user,"SMS_NEW")=='y'?' checked':'')."></input></td></tr>";

		$role_HTML.="<tr><th>�����������</th><td><input name=email_coment type=checkbox value='y'".(OCIResult($q_user,"EMAIL_COMENT")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td></td></tr>";

		$role_HTML.="<tr><th>��������������</th><td><input name=email_redir type=checkbox value='y'".(OCIResult($q_user,"EMAIL_REDIR")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td><input name=sms_redir type=checkbox value='y'".(OCIResult($q_user,"SMS_REDIR")=='y'?' checked':'')."></input></td></tr>";

		$role_HTML.="<tr><th>������� � ������</th><td><input name=email_prisv type=checkbox value='y'".(OCIResult($q_user,"EMAIL_PRISV")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td><input name=sms_prisv type=checkbox value='y'".(OCIResult($q_user,"SMS_PRISV")=='y'?' checked':'')."></input></td></tr>";

		$role_HTML.="<tr><th>������ � ��������</th><td><input name=email_ready type=checkbox value='y'".(OCIResult($q_user,"EMAIL_READY")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td><input name=sms_ready type=checkbox value='y'".(OCIResult($q_user,"SMS_READY")=='y'?' checked':'')."></input></td></tr>";

		$role_HTML.="<tr><th>�������</th><td><input name=email_close type=checkbox value='y'".(OCIResult($q_user,"EMAIL_CLOSE")=='y'?' checked':'')."></input></td>";
		$role_HTML.="<td><input name=sms_close type=checkbox value='y'".(OCIResult($q_user,"SMS_CLOSE")=='y'?' checked':'')."></input></td></tr>";

		$role_HTML.="</table>";
	$role_HTML.="</td>";
	$role_HTML.="</tr></table>";
	
echo "<script>
parent.document.getElementById('div_role').innerHTML='".str_replace("'","\'",$role_HTML)."';
</script>";
}
?>
