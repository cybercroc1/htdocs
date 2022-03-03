<?php 
extract($_REQUEST);
include("sc/sc_session.php");
session_start();
$_SESSION['last_url']='rep_fr.php';
echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no cols='190,*'>
<frame name=fr1 src=rep_main.php>
<frame name=fr2 src=report.php>
</frameset>
 

</HTML>";
?>