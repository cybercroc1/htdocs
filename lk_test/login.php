<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<title>Контакт-Центр Лайт</title>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
	<link rel='icon' href='img/cclight-favicon.ico'>
	<link rel='apple-touch-icon' sizes='180x180' href='img/cclight-favico-150x150.png'>
<?php }
else { ?>
	<title><?php echo TITLE; ?></title>
	<link rel='icon' href='img/wilstream-favicon.ico'>
	<link rel='apple-touch-icon' sizes='180x180' href='img/wilstream-favicon.ico'>
<?php } ?>

</head>
<body>
<form class='login-form' method='POST' name='login_frm' autocomplete="off">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
<img src='img/logo-cclight.png' width='227'>
<?php }
else { ?>
<img src='img/logo.png' width='227'>
<?php } ?>
<br>
<br>
<input autocomplete='off' type='text' name='usrlgn' value='<?php echo (isset($_COOKIE['usrlgn']) ? $_COOKIE['usrlgn'] : "") ?>' placeholder='Логин'/>
<br>
<input autocomplete='off' type='password' name='usrpss' value='<?php echo (isset($_COOKIE['usrpss']) ? $_COOKIE['usrpss'] : '') ?>' placeholder='Пароль'/>
<br>
<input type=checkbox name='save_pass' <?php echo ((isset($_COOKIE['usrpss']) && $_COOKIE['usrpss'] <> '' || isset($save_pass)) ? ' checked' : '')?>/> Запомнить пароль
<br>
<?php 
if (isset($auth_err)) echo "<b style='color: red; font-size: large'>" . $auth_err . "</b><br>";
else echo "<span style='display:block; color: red; font-size: small; line-height: 1.5em'>Доступ в личный кабинет предоставляются персональным менеджером после оплаты сервиса</span>"; 
?>
<input class='menubtn' type='submit' name='Enter' value='Войти'>
</form>
</body>
</html>
<?php
    exit();
?>