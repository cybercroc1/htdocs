<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();


if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id;

$_SESSION['adm_usr_last_url']='adm_usr_prj_frame.php';

if(!isset($_SESSION['adm_usr_last_url'])) $_SESSION['adm_usr_last_url']='adm_usr_main.php';

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=yes rows='50%,*'>
<frame name=adm_usr_prj_fr1 id=adm_usr_prj_fr1 src=adm_usr_acc_prj.php>
<frame name=adm_usr_prj_fr2 id=adm_usr_prj_fr2 src=blank.htm>
</frameset><noframes></noframes>   
";
?>