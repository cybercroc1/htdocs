<?php
include("starcall/session.cfg.php"); 

extract($_REQUEST);
if(isset($user_id)) {
	include("starcall/conn_string.cfg.php");
	$q=OCIParse($c, "select last_php_ssid from STC_USERS where id=".$user_id);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$kill_ssid=OCIResult($q, "LAST_PHP_SSID");
	$cur_ssid=session_id();
	session_write_close();
	session_id($kill_ssid);	
	session_start();
	session_destroy();
	session_id($cur_ssid);
	session_start();
	OCIExecute(OCIParse($c,"update STC_USERS set last_logout=sysdate where id=".$user_id),OCI_DEFAULT);
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$user_id." and lock_date is not null"));
	OCICommit($c);
	echo "<script>window.parent.admBottomFrame.admUsersFrame.document.location.reload(true);</script>";
}
?>
