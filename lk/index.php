<?php 
$is_main_page='y';
require_once "auth.php";
//ФРЕЙМ
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<title>Контакт-Центр Лайт</title>
	<link rel='icon' href='img/cclight-favicon.ico'>
	<link rel='apple-touch-icon' sizes='180x180' href='img/cclight-favico-150x150.png'>
<?php }
else { ?>
	<title><?php echo TITLE; ?></title>
	<link rel='icon' href='img/wilstream-favicon.ico'>
	<link rel='apple-touch-icon' sizes='180x180' href='img/wilstream-favicon.ico'>
<?php } ?></head>
<frameset frameborder=0 rows='55,*'>
<frame scrolling=no name=fr0 src=menu.php>
<frame name=fr12 src='blank.htm'>
</frameset>
</HTML>
