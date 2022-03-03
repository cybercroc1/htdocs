<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\"
   \"http://www.w3.org/TR/REC-html40/frameset.dtd\">
<HTML>

<frameset frameborder=no cols='175,*'>
<frame name=fr1 src=rep_main.php>
<frame name=fr2 src=report.php>
</frameset>
 

</HTML>";
?>