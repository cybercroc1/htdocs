<?php
//ВСЕ КОММЕНТАРИИ ЧИТАТЬ ОЧЕНЬ ВНИМАТЕЛЬНО!

//этот блок должен быть на каждой странице, требующей авторизованного доступа
session_name('test_login'); //устанавливает имя сессии
session_start(); //создает сесисю с именем tex и ложит ее в cookies, если на компьютере уже есть кукес с таким именем сесии, то подключается к существующей
//

extract($_REQUEST); 

if (isset($exit)) { //если есть переменная exit, значит запрошен выход с сайта, подключаемся к сесии и дестроим ее
	session_name('test_login'); //устанавливает имя сессии
	session_start(); //создает сесисю с именем tex и ложит ее в cookies, если на компьютере уже есть кукес с таким именем сесии, то подключается к существующей	
	session_destroy(); //удаляем сессию
	header("Location:".$_SERVER['PHP_SELF']); //перезагружаем данную страницу с очисткой get-параметров
}
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>'y') {//если пользователь уже авторизован, то можно пропустить авторизацию, и подключить его к существующей сессии
	//АВТОРИЗАЦИЯ:
	if(isset($User) and isset($Pass)) {
		//здесь должен быть запрос к БД для поиска пользователя по логину и паролю
		if($User=='test' and $Pass=='test') { //если в БД пользователь найден, то пользователь ввел верные логин и пароль
			setcookie('login',$User,mktime(0,0,0,1,1,2030)); //логин и пароль введены верно, этого достаточно, что бы запомнить логин	
			if(isset($save_pass)) {
				setcookie('pass',$Pass,mktime(0,0,0,1,1,2030)); //если выбрана опция сохранения пароля, устанавливаем кукес с паролем
			}
			else {
				setcookie('pass'); //иначе удаляем кукес с паролем из браузера
				unset($_COOKIE['pass']); //и из текущего сеанса из сеанса надо удалять потому, что куккес уже прочтен и сотался в данном сеансе
			}
			//здесь и только здесь должна быть проверка на то, что пользователь уже залогинен на другом компьютере
			//если дата последней активности пользователя < текущая дата + период подтверждения активности + 15 секунд
			//IP-адрес сравнивать не надо, потому, что может быть включен НАТ
			if(1==1) { //значит пользователь подключен на другом компе.
				session_destroy(); //удаляем все сессионные переменные 
				//кукесы с логином и паролем не трогаем, т.к. небыло не правильного ввода пароля
				$err="Данный пользователь уже подключен на компьютере с адресом ... Попробуйте войти позже";
			}
			else {//иначе пользователь авторизован - все в порядке.
				$_SESSION['auth']='y'; //создаем сессионную переменную, говорящую о том, что пользователь авторизован
				//здесь же устанавливаем все сессионные переменные, связанные с настройками пользователя
				//здесь же обновляем дату последней активности пользователя
			}
		}
		else {//если пользователь ввел не верные имя и пароль
			session_destroy(); //удаляем все сессионные переменные 
			//setcookie('login'); //удаляем кукес с логином из браузера
			//unset($_COOKIE['login']); //и из текущего сеанса
			setcookie('pass'); //удаляем кукес с паролем из браузера
			unset($_COOKIE['pass']); //и из текущего сеанса
			$err="Не верное имя или пароль!";		
		}
	}
}
if(isset($_SESSION['auth']) and $_SESSION['auth']=='y') {//если после всех проверок, пользователь авторизован, то можно
	//переадресоавть его на начальную страницу проекта
	//header("Location:www.ya.ru"); ВНИМАНИЕ, если переадресовать на самого себя, то получим бесконечный цикл
	
	//или поместить сюда код начальной страницы
	echo 'ПОЗДРАВЛЯЮ! Вы авторизованы <a href="?exit"><font color=red>Выход</font></a>';	
}
else {//иначе показываем страницу логина

//ОБРАТИ ВНИМАНИЕ на параметр autocomplete='off' в полях воода логина и пароля. Он исключает ситуацию, когда глупые браузеры запоминают не верный логин и пароль. 
//Логин и пароль должны запоминаться кукесами только в случае успешного ввода.
	
echo '<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<body>';

	if(isset($err)) echo "<font color=red><b>".$err."</b></font>";

	echo "<form method='POST'>
<div align='center'><center>
<h1>Небытие</h1>

 </center></div><div align='center'><center>
 <table border='0' width='100%' 
 cellspacing='0' cellpadding='0' height='137'>

    <tr align='center'>
      <td width=20%></td>
      <td width=20%></td>
      <td width=150 align='center' width=60><font color='#00000'><strong>Вход</strong></font></td>
      <td width=20%></td>
      <td width=20%>&nbsp;</td>
    </tr> 
 
   <tr>
      <td></td>
      <td align='right'><font color='#00000'><strong>Пользователь: </strong></font></td>
      <td align='center'><input autocomplete='off' type='text' name='User' value='".(isset($_COOKIE['login'])?$_COOKIE['login']:"")."' size='20'></td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td><div align='right'><p><font color='#00000'><strong>Пароль: </strong></font></td>
      <td align='center'><input autocomplete='off' type='password' name='Pass' value='".(isset($_COOKIE['pass'])?$_COOKIE['pass']:'')."' size='20'></td>
      <td></td>
      <td><div align='center'></div></td>
    </tr>";
	//если лоигн и пароль уже сохранены в кукесах, то галочка "запомнить пароль" остается включенной
	echo "<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".((isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''||isset($save_pass))?' checked':'')."> запомнить пароль</td></tr>
	
    <tr align='center'>
      <td height='50'></td>
      <td height='50'></td>
      <td align='center' height='50'><input type='submit' value='Войти'></td>
      <td height='50'></td>
      <td height='50'>&nbsp;<p></td>
    </tr>
  </table>
  </body>
</html>";
exit();	
}
?>