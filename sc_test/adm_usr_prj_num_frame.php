<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();


if(isset($login_id)) $_SESSION['edit_login']['id']=$login_id;
if(isset($project_id)) $_SESSION['edit_login']['project_id']=$project_id; else $_SESSION['edit_login']['project_id']='';


$_SESSION['adm_usr_last_url']='adm_usr_prj_frame.php';

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</HEAD>
<frameset frameborder=yes cols='50%,*'>
<frame name=adm_usr_prj_num_fr1 id=adm_usr_prj_num_fr1 src=adm_usr_acc_frm.php>
<frame name=adm_usr_prj_num_fr2 id=adm_usr_prj_num_fr2 src=adm_usr_acc_num.php>
</frameset><noframes></noframes>   
";
?>