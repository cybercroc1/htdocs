<?php 
extract($_REQUEST);
include("../../sc_conf/sc_session");
session_start();

if (isset($width_save)) {
	include("../../sc_conf/sc_conn_string");
$_SESSION['fr_width'][$_SESSION['i']]=$fr_width;
$_SESSION['fr_w']=$fr_width;
$upd=OCIParse($c,"update sc_projects set tree_width='".$fr_width."'
where id='".$_SESSION['project_id'][$_SESSION['i']]."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\"
   \"http://www.w3.org/TR/REC-html40/frameset.dtd\">
<HTML>

<frameset frameborder=no cols='".$_SESSION['fr_w'].",*'>
<frame name=fr1 src=tree.php>
<frame name=fr2 src=body.php>
</frameset><noframes></noframes>   

</HTML>";
?>