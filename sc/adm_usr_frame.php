<?php 
extract($_REQUEST);
include("sc/sc_session.php");
session_start();

$_SESSION['adm_last_url']='adm_usr_frame.php';

if(!isset($_SESSION['adm_usr_last_url'])) $_SESSION['adm_usr_last_url']='adm_usr_main.php';

if(isset($login_id)) {
	$_SESSION['edit_login']['id']=$login_id;
	$_SESSION['adm_usr_last_url']='adm_usr_main.php';
}

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=yes cols='30%,*'>
<frame name=adm_usr_fr1 id=adm_usr_fr1 src=adm_users.php>
<frame name=adm_usr_fr2 id=adm_usr_fr2 src=".$_SESSION['adm_usr_last_url'].">
</frameset><noframes></noframes>   
";
?>