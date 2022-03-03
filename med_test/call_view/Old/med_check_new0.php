<script>
setTimeout("document.location='med_check_new.php?check'",15000);
</script>
<?php
//session_name('medc');
//session_start();
require_once 'med/check_auth.php';

extract($_REQUEST);
require_once '../funct.php';

// обновляем статус, что подключены
GetData::UpdateActivity($_SESSION['login_id_med'], TRUE, FALSE);
if (isset($check)) {
    $parsestr = "select count(*) cnt, nvl(sum(checksum),0) checksum from (";
    if (!in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
        $parsestr .= "select distinct cb.ID, 
    (nvl(to_char(cb.DATE_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.LAST_CHANGE,'MMDD'),0)+
    nvl(to_char(cb.SENT_MAIL,'MMDD'),0)+
    nvl(cb.SERVICE_ID,0)+nvl(cb.SERVICE_DET_ID,0)+nvl(cb.FIO_ID,0)+nvl(cb.STATUS_ID,0)+nvl(cb.STATUS_DET_ID,0)+
    nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)+nvl(cb.SOURCE_MAN_ID_NEW,0)+nvl(cb.SOURCE_MAN_DET_ID_NEW,0)) as checksum ";
    else $parsestr .= "select distinct cb.ID, 
    (nvl(to_char(cb.DATE_SECOND_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.SECOND_LAST_CHANGE,'MMDD'),0)+
    nvl(cb.SERVICE_ID,0)+nvl(cb.SERVICE_DET_ID,0)+nvl(cb.SECOND_FIO_ID,0)+nvl(cb.SECOND_STATUS_ID,0)+nvl(cb.SECOND_STATUS_DET_ID,0)+
    nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)+nvl(cb.SOURCE_MAN_ID_NEW,0)+nvl(cb.SOURCE_MAN_DET_ID_NEW,0)) as checksum ";
    $parsestr .= $_SESSION['refresh_where_med']." )";
//echo "<br><textarea>".$parsestr."</textarea>";

    $q = OCIParse(GetData::GetConnect(), $parsestr);
    OCIExecute($q, OCI_DEFAULT);
    OCIFetch($q);

    //echo OCIResult($q, "CNT") . "=" . $_SESSION['q_count_med'] . "<br>";
    //echo OCIResult($q, "CHECKSUM") . "=" . $_SESSION['q_checksum_med'] . "<br>";
    $_SESSION['reload_at_save'] = FALSE;

    if (in_array($_SESSION['login_id_med'], SPEC_USER_CALL)) {
        if (OCIResult($q, "CHECKSUM") <> $_SESSION['q_checksum_med']) {
            echo "<script>parent.location.reload();</script>";
        }
    } else {
        if (OCIResult($q, "CNT") <> $_SESSION['q_count_med'] or OCIResult($q, "CHECKSUM") <> $_SESSION['q_checksum_med']) {
            echo "<script>parent.location.reload();</script>";
        }
    }
}
?>