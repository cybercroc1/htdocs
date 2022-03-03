<?php 
session_name('medc');
session_start();
//extract($_REQUEST);
if (!isset($_SESSION['login_id_med'])) {
    echo "ОШИБКА ДОСТУПА";
    exit();
}

//ФРЕЙМ
header('X-UA-Compatible: IE=EmulateIE7');
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// дата в прошлом
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
 // всегда модифицируется
header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");// HTTP/1.0

echo "<!DOCTYPE HTML>
<HEAD>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
<TITLE>Сценарий ".$project_name."</TITLE>
</HEAD>";

echo "
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no cols='50,50,*'>
<frame name=rep_main_menu src=rep_menu.php>
<frame name=rep_filtr src='_blank_page.php'>
<frame name=rep_result src='_blank_page.php'>
</frameset><noframes></noframes>   
";
?>