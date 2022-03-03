<?php
session_start();
extract($_REQUEST);
if(isset($date_last_change) and isset($_SESSION['auth'])) {
	echo $date_last_change;
	include("../../sup_conf/sup_conn_string");
	$q=OCIParse($c,"select count(*) cnt from sup_base
	where last_change>to_date('".$date_last_change."','DD.MM.YYYY HH24:MI:SS')");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"CNT")>0) {
	echo "<script>parent.location=parent.location;</script>";
	}
}
?>
<script>
setTimeout("document.location='tex_check_new.php?date_last_change='+parent.document.all.date_last_change.value",30000);
</script>
