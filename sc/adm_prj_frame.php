<?php 
extract($_REQUEST);
include("sc/sc_session.php");
session_start();

if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

$_SESSION['adm_last_url']='adm_prj_frame.php';

/*if(isset($login_id)) {
	$_SESSION['edit_login']['id']=$login_id;
	$_SESSION['adm_prj_last_url']='adm_prj_main.php';
}
*/
echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=yes rows='70%,*'>
<frame name=adm_prj_fr1 id=adm_prj_fr1 src=adm_prj.php>
<frame name=adm_prj_fr2 id=adm_prj_fr2 src=blank.htm>
</frameset><noframes></noframes>   
";
?>