<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php

extract($_REQUEST);

if(isset($load_id) and isset($abort_pwd)) {
include("../../conf/starcall_conf/conn_string.cfg.php");
	$q_abort_load=OCIParse($c,"update STC_LOAD_HISTORY set abort='y' where id=".$load_id." and abort_pwd='".$abort_pwd."'");
	OCIExecute($q_abort_load);
	OCICommit($c);
}
?>
</body>
</html>