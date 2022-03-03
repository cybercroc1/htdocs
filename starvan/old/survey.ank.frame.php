<?php 
include("../../conf/starcall_conf/session.cfg.php");

if($_SESSION['user']['operator']<>'y') exit();

extract($_REQUEST);

echo '<!DOCTYPE html>
<html>
<head>
<title>StarCall</title>
<script>
function set_frameset_size(obj) {
	if(w=getCookie("ank_frame_width")) {document.all.ankFrameset.setAttribute("cols",w);}
	else document.all.ankFrameset.setAttribute("cols","50%,*");
}
function getCookie(name) {
   var r = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
   if (r) return r[2];
   else return "";
}
</script>
</head>
	<frameset name=ankFrameset id=name=ankFrameset cols="80%,*" onload=set_frameset_size(this)>
		<frame src=survey.ank.php name=ankMainFrame id=ankMainFrame title=ankMainFrame>
		<frame src=survey.aboninfo.php name=ankInfoFrame id=ankInfoFrame title=ankInfoFrame>
	</frameset>  
</html>';
?>
