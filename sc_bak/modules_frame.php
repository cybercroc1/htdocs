<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();
//$_SESSION['last_url']='modules_frame.php';

if(!isset($_SESSION['modules_last_url'])) $_SESSION['modules_last_url']='blank.htm';

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no rows='25,*'>
<frame name=module_fr1 src=modules_menu.php>
<frame name=module_fr2 src=blank.htm>
</frameset><noframes></noframes>   
";
?>