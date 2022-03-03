<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
session_name('starcall_1905');
session_start();
if(!isset($_SESSION['user']['id'])) {
	echo "<font color=red>ACCESS DENY !</font>";
	echo "<script>parent.parent.location='login.php'</script>";
}
?>