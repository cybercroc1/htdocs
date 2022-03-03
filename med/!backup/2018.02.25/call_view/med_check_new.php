<script>
setTimeout("document.location='med_check_new.php?check'",15000);
</script>
<?php
//session_name('med');
session_start();

require_once '../funct.php';

extract($_REQUEST);

if(isset($check)) {
// обновляем статус, что подключены
    GetData::UpdateActivity($_SESSION['login_id'], TRUE);

    $parsestr = "select count(*) cnt, nvl(sum(checksum),0) checksum from (
        select distinct cb.ID, 
		(nvl(to_char(cb.DATE_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.LAST_CHANGE,'MMDD'),0)+
		nvl(cb.FIO_ID,0)+nvl(cb.STATUS_ID,0)+nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)) as checksum"
    .$_SESSION['refresh_where'].")";
    $q = OCIParse(GetData::GetConnect(),$parsestr);
//echo "<br/><textarea>".$parsestr."</textarea>";

	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	
	echo OCIResult($q,"CNT")."=".$_SESSION['q_count']."<br>";
	echo OCIResult($q,"CHECKSUM")."=".$_SESSION['q_checksum']."<br>";

	if (OCIResult($q,"CNT")<>$_SESSION['q_count'] or OCIResult($q,"CHECKSUM")<>$_SESSION['q_checksum'])
	{
        echo "<script>parent.location.reload();</script>";
	}
}
?>