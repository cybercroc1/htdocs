<script>
setTimeout("document.location='session_refresh.php'",60000);
</script>
<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("lk/defines.php");
session_name(SESSIONNAME);
session_start();

		require_once "lk/lk_ora_conn_string.php";		
		$q_upd=OCIParse($c,"update sc_login set last_activity=sysdate where id='".$_SESSION['login_id']."'");
		OCIExecute($q_upd);
		OCICommit($c);		
?>