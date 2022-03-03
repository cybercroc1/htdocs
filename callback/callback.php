<?php
//echo "Временно не доступно"; exit();

//if(substr($_SERVER['REMOTE_ADDR'],0,9)=='192.168.1') {
echo '
<form method=post>
<input type="text" name="phone">
<input type="submit">
</form>';
//}

extract($_REQUEST);

//echo $_SERVER['REMOTE_ADDR'];


if(isset($phone) and strlen($phone>=7) /*and (substr($_SERVER['REMOTE_ADDR'],0,11)=='213.171.60.' or substr($_SERVER['REMOTE_ADDR'],0,9)=='192.168.1')*/) {
include("../../sc_conf/smt_conn_string");

$q_chk=OCIParse($smt,"select count(*) count from vmd_campaign_numbers t
where t.call_group_ref=141 and t.campaign_phone_no='".$phone."' and stateref<3");
OCIExecute($q_chk,OCI_DEFAULT);
OCIFetch($q_chk);
	if(OCIResult($q_chk,"COUNT")==0) {
	$q=OCIParse($smt,"insert into vmd_campaign_numbers
(campaign_phone_no, phoneno_type_ref, createdate, alterdate, closedate, closed, call_group_ref, alteredby, createdby, resultref, stateref)
values
('".$phone."','1', SYSDATE, SYSDATE, NULL, '0', '141', '1', '1', '1', '1')");
	OCIExecute($q,OCI_DEFAULT);
	OCICommit($smt);
	}
}

else {
echo "Доступ запрещен";
}

?>
