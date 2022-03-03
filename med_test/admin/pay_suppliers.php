<?php
include("../med/conn_string.cfg.php");
$bDebug = TRUE;
//function pay_to_supplier_for_visits($bDebug=TRUE)
function pay_visits($bDebug=TRUE)
{
    if (!$bDebug) {
        $table_name = " CALL_BASE ";
    } else {
        $table_name = " CALL_BASE_TEST ";
    }
    echo "<script>alert('pay_visits');</script>";
    die();
    $load_sql = "select cb.ID, sa.SUPPLIER_ID, sac.COST_VISIT from " . $table_name . " cb "; //cb.SOURCE_AUTO_ID,
    $load_sql .= " LEFT JOIN SOURCE_AUTO sa ON sa.ID = cb.SOURCE_AUTO_ID";
    $load_sql .= " LEFT JOIN SOURCE_AUTO_COST sac ON sac.SOURCE_AUTO_ID = cb.SOURCE_AUTO_ID";
    $load_sql .= " where (PAY_SUPPLIER is NULL or PAY_SUPPLIER = 0) and sac.COST_VISIT > 0 and sac.DELETED is NULL 
and cb.ID in (select distinct base_id from visit_hist)";
//$load_sql .= " order by cb.SOURCE_AUTO_ID";
    if (TRUE == $bDebug) echo "<textarea>" . $load_sql . "</textarea><br/>";
    $q = OCIParse($c, $load_sql);
    if (OCIExecute($q)) {
        $cur_sa = -1;
        $amount_visit = $suppl_id = 0;
        while (OCIFetch($q)) {
            $Base_Id = OCIResult($q, "ID");
            //$sra_id = OCIResult($q,"SOURCE_AUTO_ID");
            $suppl_id = OCIResult($q, 'SUPPLIER_ID');
            $amount_visit = OCIResult($q, 'COST_VISIT');

            //if ($amount > 0) { // не проводить операции, если сумма списания нулевая ?
            $upd_pay = "UPDATE " . $table_name . " SET PAY_SUPPLIER = '{$amount_visit}' WHERE ID = '{$Base_Id}'";
            if (TRUE == $bDebug) echo "<textarea>" . $upd_pay . "</textarea><br/>";
            GetData::my_log($upd_pay, FALSE);
            $query = OCIParse($c, $upd_pay);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($upd_pay, TRUE);

            $upd_pay = "UPDATE SUPPLIERS SET BALANCE = BALANCE - '{$amount_visit}' WHERE ID = '{$suppl_id}'";
            if (TRUE == $bDebug) echo "<textarea>" . $upd_pay . "</textarea><br/>";
            GetData::my_log($upd_pay, FALSE);
            $query = OCIParse($c, $upd_pay);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($upd_pay, TRUE);
            //}
        }
    }
}

