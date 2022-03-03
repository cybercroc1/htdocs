<?php 
extract($_REQUEST);
include("sc/sc_session.php");
session_start();
$_SESSION['last_url']='adm_frame.php';

if(!isset($_SESSION['adm_last_url'])) $_SESSION['adm_last_url']='adm_prj.php';

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no rows='25,*'>
<frame name=adm_fr1 src=adm_menu.php>
<frame name=adm_fr2 src=".$_SESSION['adm_last_url'].">
</frameset><noframes></noframes>   
";
?>