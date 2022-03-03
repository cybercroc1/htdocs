<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();
$_SESSION['last_url']='edit_sc.php';
if ($_SESSION['project']['id']==0) exit(); 

if (isset($width_save)) {
	include("../../sc_conf/sc_conn_string");
$_SESSION['project']['fr_width']=$fr_width;
//$_SESSION['fr_w']=$fr_width;
$upd=OCIParse($c,"update sc_projects set tree_width='".$fr_width."'
where id='".$_SESSION['project']['id']."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
</head>
<frameset frameborder=no cols='".$_SESSION['project']['fr_width'].",*'>
<frame name=fr1 src=tree.php>
<frame name=fr2 src=body.php>
</frameset><noframes></noframes>   

</HTML>";
?>