<?php
//��� ����������� ������ ����� �����������!

//���� ���� ������ ���� �� ������ ��������, ��������� ��������������� �������
session_name('test_login'); //������������� ��� ������
session_start(); //������� ������ � ������ tex � ����� �� � cookies, ���� �� ���������� ��� ���� ����� � ����� ������ �����, �� ������������ � ������������
//

extract($_REQUEST); 

if (isset($exit)) { //���� ���� ���������� exit, ������ �������� ����� � �����, ������������ � ����� � �������� ��
	session_name('test_login'); //������������� ��� ������
	session_start(); //������� ������ � ������ tex � ����� �� � cookies, ���� �� ���������� ��� ���� ����� � ����� ������ �����, �� ������������ � ������������	
	session_destroy(); //������� ������
	header("Location:".$_SERVER['PHP_SELF']); //������������� ������ �������� � �������� get-����������
}
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>'y') {//���� ������������ ��� �����������, �� ����� ���������� �����������, � ���������� ��� � ������������ ������
	//�����������:
	if(isset($User) and isset($Pass)) {
		//����� ������ ���� ������ � �� ��� ������ ������������ �� ������ � ������
		if($User=='test' and $Pass=='test') { //���� � �� ������������ ������, �� ������������ ���� ������ ����� � ������
			setcookie('login',$User,mktime(0,0,0,1,1,2030)); //����� � ������ ������� �����, ����� ����������, ��� �� ��������� �����	
			if(isset($save_pass)) {
				setcookie('pass',$Pass,mktime(0,0,0,1,1,2030)); //���� ������� ����� ���������� ������, ������������� ����� � �������
			}
			else {
				setcookie('pass'); //����� ������� ����� � ������� �� ��������
				unset($_COOKIE['pass']); //� �� �������� ������ �� ������ ���� ������� ������, ��� ������ ��� ������� � ������� � ������ ������
			}
			//����� � ������ ����� ������ ���� �������� �� ��, ��� ������������ ��� ��������� �� ������ ����������
			//���� ���� ��������� ���������� ������������ < ������� ���� + ������ ������������� ���������� + 15 ������
			//IP-����� ���������� �� ����, ������, ��� ����� ���� ������� ���
			if(1==1) { //������ ������������ ��������� �� ������ �����.
				session_destroy(); //������� ��� ���������� ���������� 
				//������ � ������� � ������� �� �������, �.�. ������ �� ����������� ����� ������
				$err="������ ������������ ��� ��������� �� ���������� � ������� ... ���������� ����� �����";
			}
			else {//����� ������������ ����������� - ��� � �������.
				$_SESSION['auth']='y'; //������� ���������� ����������, ��������� � ���, ��� ������������ �����������
				//����� �� ������������� ��� ���������� ����������, ��������� � ����������� ������������
				//����� �� ��������� ���� ��������� ���������� ������������
			}
		}
		else {//���� ������������ ���� �� ������ ��� � ������
			session_destroy(); //������� ��� ���������� ���������� 
			//setcookie('login'); //������� ����� � ������� �� ��������
			//unset($_COOKIE['login']); //� �� �������� ������
			setcookie('pass'); //������� ����� � ������� �� ��������
			unset($_COOKIE['pass']); //� �� �������� ������
			$err="�� ������ ��� ��� ������!";		
		}
	}
}
if(isset($_SESSION['auth']) and $_SESSION['auth']=='y') {//���� ����� ���� ��������, ������������ �����������, �� �����
	//�������������� ��� �� ��������� �������� �������
	//header("Location:www.ya.ru"); ��������, ���� �������������� �� ������ ����, �� ������� ����������� ����
	
	//��� ��������� ���� ��� ��������� ��������
	echo '����������! �� ������������ <a href="?exit"><font color=red>�����</font></a>';	
}
else {//����� ���������� �������� ������

//������ �������� �� �������� autocomplete='off' � ����� ����� ������ � ������. �� ��������� ��������, ����� ������ �������� ���������� �� ������ ����� � ������. 
//����� � ������ ������ ������������ �������� ������ � ������ ��������� �����.
	
echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>������������</title>
</head>
<body>';

	if(isset($err)) echo "<font color=red><b>".$err."</b></font>";

	echo "<form method='POST'>
<div align='center'><center>
<h1>�������</h1>

 </center></div><div align='center'><center>
 <table border='0' width='100%' 
 cellspacing='0' cellpadding='0' height='137'>

    <tr align='center'>
      <td width=20%></td>
      <td width=20%></td>
      <td width=150 align='center' width=60><font color='#00000'><strong>����</strong></font></td>
      <td width=20%></td>
      <td width=20%>&nbsp;</td>
    </tr> 
 
   <tr>
      <td></td>
      <td align='right'><font color='#00000'><strong>������������: </strong></font></td>
      <td align='center'><input autocomplete='off' type='text' name='User' value='".(isset($_COOKIE['login'])?$_COOKIE['login']:"")."' size='20'></td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td><div align='right'><p><font color='#00000'><strong>������: </strong></font></td>
      <td align='center'><input autocomplete='off' type='password' name='Pass' value='".(isset($_COOKIE['pass'])?$_COOKIE['pass']:'')."' size='20'></td>
      <td></td>
      <td><div align='center'></div></td>
    </tr>";
	//���� ����� � ������ ��� ��������� � �������, �� ������� "��������� ������" �������� ����������
	echo "<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".((isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''||isset($save_pass))?' checked':'')."> ��������� ������</td></tr>
	
    <tr align='center'>
      <td height='50'></td>
      <td height='50'></td>
      <td align='center' height='50'><input type='submit' value='�����'></td>
      <td height='50'></td>
      <td height='50'>&nbsp;<p></td>
    </tr>
  </table>
  </body>
</html>";
exit();	
}
?>