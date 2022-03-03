<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\"
   \"http://www.w3.org/TR/REC-html40/frameset.dtd\">
<HEAD>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=no cols='".$_SESSION['fr_w'].",*'>
<frame name=fr1 src=tree.php>
<frame name=fr2 src=body.php>
</frameset><noframes></noframes>   
";
?>