<script>
setTimeout("document.location='tex_check_new.php?check'",30000);
</script>
<?php
session_name('tex');
session_start();
extract($_REQUEST);

if(isset($check)) {
	include("../../sup_conf/sup_conn_string");
	$q=OCIParse($c,"select count(*) cnt from (
select distinct b.id ".$_SESSION['refresh_where']."
)");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	
	echo OCIResult($q,"CNT")."=".$_SESSION['q_count'];

	if (OCIResult($q,"CNT")<>$_SESSION['q_count']) {
		echo "<script>parent.location.reload();</script>";
	}
}
?>
