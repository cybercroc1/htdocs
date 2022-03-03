<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="oktadmin.css" rel="stylesheet" type="text/css">
<title>Волшебные Кнопки</title>
</head>
<BODY>


<?php
date_default_timezone_set('Europe/Moscow');
session_start();
require_once'plss.php';
if ($_SESSION['activ'] == 'serge')	
	{
	echo '<p align=center>Заполни и нажми...</br>';

	$id = '';
	$ui = '';
		
		//Обработчик по пользователям
		if(isset($_POST['users']))
		{
			if ($_POST['userforinfo'] == 'serge')
			{
				$id = '3940';
				$ui = '11381';  
			}
			else
			{
				$_SESSION['message'] = 'Что-то пошло не так...';
				header ('Location: end.php');  
				exit(); 
			}
			$sql="INSERT INTO [dbo].[NGAC_AUTHLOG]
			([UserIDIndex],[TransactionTime],[UserID],[TerminalID],[AuthType],[AuthResult],[FunctionKey],[ServerRecordTime],[Reserved],[LogType],[TempValue],[MinIndex])
			VALUES (".$ui.",'".date('Y-m-d G:i:s.000')."',".$id.",23,128,0,0,'".date('Y-m-d G:i:s.000')."',0,1,0,0)";
			echo '<textarea>'.$sql.'</textarea>';
			$sqlz = $c_okt->query($sql);
			$_SESSION['message'] = 'Отмечено ID-'.$id.' Nickname-'.$_POST['userforinfo'].' Время- '.date('Y-m-d G:i:s.000').' УСПЕШНО';
			header ('Location: end.php');  
			exit(); 
		}
		else
		{	
			//Форма выбора пользователя
			echo
					'<form method="post" style="text-align:center;">
			Кто вы?	<select name="userforinfo">
					<option value="serge">Сергей</option>
					</br>
					<input type="submit" value="Отметить" name="users" />
					</form>';
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