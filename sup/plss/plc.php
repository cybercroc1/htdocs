<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="oktadmin.css" rel="stylesheet" type="text/css">
<link href="style.css" rel="stylesheet" type="text/css">
<title>��������� ������</title>
</head>
<BODY>

<?php
date_default_timezone_set('Europe/Moscow');
session_start();
require_once'plss.php';
if ($_SESSION['activ'] == 'admin')	
	{
	echo '<h1>������� � �����...</h1>';

	$id = '';
	$ui = '';
		
		//���������� �� �������������
		if(isset($_POST['users']))
		{
			if ($_POST['userforinfo'] == 'gamycheg')
			{
				$id = '5191';
				$ui = '5492';  
			}
			elseif ($_POST['userforinfo'] == 'lexa')
			{
				$id = '7827';
				$ui = '7006'; 
			}
			elseif ($_POST['userforinfo'] == 'dessin')
			{
				$id = '8424';
				$ui = '14014';
			}
			elseif ($_POST['userforinfo'] == 'seregina')
			{
				$id = '7826';
				$ui = '7005'; 
			}
			else
			{
				$_SESSION['message'] = '���-�� ����� �� ���...';
				header ('Location: end.php');  
				exit(); 
			}
			$sql="INSERT INTO [dbo].[NGAC_AUTHLOG]
			([UserIDIndex],[TransactionTime],[UserID],[TerminalID],[AuthType],[AuthResult],[FunctionKey],[ServerRecordTime],[Reserved],[LogType],[TempValue],[MinIndex])
			VALUES (".$ui.",'".date('Y-m-d G:i:s.000')."',".$id.",23,128,0,0,'".date('Y-m-d G:i:s.000')."',0,1,0,0)";
			echo '<textarea>'.$sql.'</textarea>';
			$sqlz = $c_okt->query($sql);
			$_SESSION['message'] = '�������� ID-'.$id.' Nickname-'.$_POST['userforinfo'].' �����- '.date('Y-m-d G:i:s.000').' �������<br><div class=okay><img src=okay.jpg></img></div>';
			header ('Location: end.php');  
			exit(); 
		}
		else
		{	
			//����� ������ ������������
			echo
					'<div class=formishe>
					<form method="post" class="authformplc">
					<h2>��� ��?<h2>
					<select name="userforinfo">
					<option value="gamycheg">�������� ���������</option>
					<option value="lexa">������ �������</option>
					<option value="dessin">������ �������</option>
					<option value="seregina">�������� �������</option>
					<option value="error">����</option></select>
					</br>
					<input class="submitbtnplc" type="submit" value="��������" name="users" />
					</form></div>';
		}
	
	}
	else
	{
		header ('Location: ind.php');  
		exit();    
	};
?>

</BODY>
</HTML>