<script>
setTimeout("document.location='tex_check_new.php?check'",15000);
</script>
<?php
session_name('tex');
session_start();
extract($_REQUEST);

if(isset($check) and (!isset($_SESSION['no_check']) or $_SESSION['no_check']<>'y')) {
	include("sup/sup_conn_string");
	$q=OCIParse($c,"select count(*) cnt, nvl(sum(checksum),0) checksum from (
select distinct b.id, 
nvl(to_char(b.in_work,'MISS'),0)+nvl(to_char(b.date_close,'MISS'),0)+nvl(to_char(b.ready_to_close,'MISS'),0)+nvl(to_char(b.delay_to,'MMDD'),0) checksum 
".$_SESSION['refresh_where'].")");

	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	
	echo OCIResult($q,"CNT")."=".$_SESSION['q_count']."<br>";
	echo OCIResult($q,"CHECKSUM")."=".$_SESSION['q_checksum']."<br>";

	if (OCIResult($q,"CNT")<>$_SESSION['q_count'] or OCIResult($q,"CHECKSUM")<>$_SESSION['q_checksum']) {
		echo "<script>parent.location.reload();</script>";
	}
	echo "Last check: ".date('d.m.Y H:i:s');

	echo "<textarea>select count(*) cnt, nvl(sum(checksum),0) checksum from (
select distinct b.id, 
nvl(to_char(b.in_work,'MISS'),0)+nvl(to_char(b.date_close,'MISS'),0)+nvl(to_char(b.ready_to_close,'MISS'),0)+nvl(to_char(b.delay_to,'MMDD'),0) checksum 
".$_SESSION['refresh_where'].")</textarea>";

}
?>
