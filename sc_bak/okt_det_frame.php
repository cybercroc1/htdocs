<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();
$_SESSION['last_url']='okt_det_frame.php';

if(!isset($_SESSION['okt_det_last_url'])) $_SESSION['okt_det_last_url']='okt_det_in.php';

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no rows='25,*'>
<frame name=okt_det_fr1 src=okt_det_menu.php>
<frame name=okt_det_fr2 src=".$_SESSION['okt_det_last_url'].">
</frameset><noframes></noframes>   
";
?>