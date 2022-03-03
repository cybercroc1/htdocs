<!DOCTYPE html>
<HTML>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251"/>
    <link href="source_auto_cost.css" rel="stylesheet" type="text/css">
    <title></title>
</head>
<script src="../js/func.row_select.js"></script>
<!--script src="../js/report.js"></script ? ROW VS ROW_ID ? -->
<script src="../js/func.filters.js"></script>
<script src="../js/func.orders.js"></script>

<script>
    function edit(id) {
        parent.fr_source_cost_edit.location = 'admin_supplier_edit.php?id=' + id;
        parent.fr_supplier_hist.location = 'admin_supplier_hist.php?id=' + id;
    }
</script>

<body style="margin: 0">
<?php

extract($_REQUEST);

//���������� �������: ������ ������ ����� ���������� �������� ����� �� ������, ��� �������� ����� �������� � ����� �������
//$sql_text = "SELECT sa.ID, BNUMBER, sa.NAME||'('||st.NAME||')' as NAME, st.NAME as SOURCE_TYPE, serv.NAME as SERVICE, SERVICE_ID, IN_ROUTE_ID,
$sql_text = "SELECT sup.ID, SUP_NAME, BALANCE, to_char(sup.DELETED,'dd.mm.yyyy hh24:mi:ss') AS DELETED
FROM SUPPLIERS sup WHERE 1=1 and DELETED is NULL
/*filters*/ 
/*orders*/ 
";

//�������� �����
//��������� name - ��� �������, ������������ �� ��������
//��������� case - ��������� ��� ����, �� �������� �������� where � order
$fields = array(
    "ID" => "", "SUP_NAME" => array("name" => "�������� ����������", "case"=>"sup.SUP_NAME"),
    "BALANCE" => array("name" => "������"),
    //"DELETED" => array("name" => "�������")
);

// ��������� ��������: ������ � �������� ����� �������, ������� � 1.
// ����� ������ ������� ��������, ������ �������� ������� where �� �������� ����� �������
// ����� � �������, ���� ����� �������� ������ (������� � and) ������ ���� ���������� ������������ "/*filters*/"
// ���� ������� �������� ��������� �� ����������, ���� ������ ������������� �������� ������� �� ���������
$filters = array(
    "SUP_NAME" => "",
    "BALANCE" => "",
    //"DELETED" => "NULL"
);

// ��������� ����������� ���������� ������ �������� �������� ����������� ����������.
// ��� ������� ���������� ���������� �� ��������� ����� ������������ up,asc - �� �����������; down,desc - �� ��������
$orders = array(
    "ID" => "",
    "SUP_NAME" => "asc",
    "BALANCE" => "",
    //"DELETED" => ""
);


//�������
if (isset($filters) && count($filters) > 0) {
    if (!isset($filter_no_default)) { //����������� ������� � �������� �� ���������
        foreach ($filters as $filter_name => $filter_defaults) {
            if (is_array($filter_defaults)) {
                $filter_selected_values[$filter_name] = $filter_defaults;
            }
        }
    }
    $filter_sql = '';
    $filter_sql_tmp = '';
    if (isset($filter_selected_values)) {
        foreach ($filter_selected_values as $filter_name => $value_arr) {
            if (count($value_arr) > 0) {
                if (isset($fields[$filter_name]['case']) && $fields[$filter_name]['case'] != "")
                    $filter_case = $fields[$filter_name]['case'];
                else $filter_case = $filter_name;
                $filter_sql_tmp .= " and " . $filter_case . " in (";
                $n = 0;
                foreach ($value_arr as $val) {
                    $n++;
                    if ($val == 'all') {
                        unset($filter_selected_values[$filter_name]);
                        $filter_sql_tmp = 'all';
                        break;
                    } else {
                        if ($n > 1) $filter_sql_tmp .= ",";
                        $filter_sql_tmp .= "'" . $val . "'";
                    }
                }
                if ($filter_sql_tmp == 'all') {
                    $filter_sql_tmp = '';
                } else {
                    $filter_sql_tmp .= ")";
                }
                $filter_sql .= $filter_sql_tmp;
                $filter_sql_tmp = '';
            }
        }
    }
}
if (isset($filter_sql)) $sql_text = str_replace("/*filters*/", $filter_sql, $sql_text);

//����������
if (isset($orders) and count($orders) > 0) {
    if (!isset($order_no_default)) { //����������� ���������� � �������� �� ���������
        $order_num = 0;
        foreach ($orders as $field_name => $order_type) {

            if (in_array($order_type, array('up', 'asc'))) {
                $order_num++;
                $orders_selected[$field_name]['type'] = 'asc';
                $orders_selected[$field_name]['num'] = $order_num;
            } else if (in_array($order_type, array('down', 'desc'))) {
                $order_num++;
                $orders_selected[$field_name]['type'] = 'desc';
                $orders_selected[$field_name]['num'] = $order_num;
            } else {
                $orders_selected[$field_name]['type'] = 'none';
                $orders_selected[$field_name]['num'] = '';
            }
        }
    }
}

if (isset($orders_selected)) {
    foreach ($orders_selected as $field_name => $order) {
        if ($order['num'] <> '') {
            $orders_tmp[$order['num']]['field_name'] = $field_name;
            $orders_tmp[$order['num']]['order_type'] = $order['type'];
        }
    }

    if (isset($orders_tmp)) {
        ksort($orders_tmp);

        foreach ($orders_tmp as $order) {
            if (isset($fields[$order['field_name']]['case']) && $fields[$order['field_name']]['case'] != "")
                $order_case = $fields[$order['field_name']]['case'];
            else $order_case = $order['field_name'];

            $order_sql[] = $order_case . ' ' . $order['order_type'];
        }

        if (isset($order_sql)) {
            $order_sql_text = "order by " . implode(", ", $order_sql);
            $sql_text = str_replace("/*orders*/", $order_sql_text, $sql_text);
        }
    }
}
//var_dump($sql_text);

//require_once 'oktadmin/oktell_conn_string.php';
//$query = $c_okt->query($sql_text);
require_once("med/conn_string.cfg.php");

$query = OCIParse($c, $sql_text);
OCIExecute($query, OCI_DEFAULT);
//$nrows = OCI_Fetch_All($query,$array_source_auto,0,-1,OCI_FETCHSTATEMENT_BY_ROW);

echo "<h3 style='margin: 5px;'>������ �����������</h3>";
echo "<form name='frm' method='post'>";
echo "<table class='white_table' align='center'>";

//�����
echo "<tr onmouseover='filters_open(this)' onmouseout='filters_close(this)'>";
//�������
if (isset($filters) and count($filters) > 0) {
    echo "<input type=hidden name=filter_no_default />"; //��� ���� �����, ����� ��������� ������ ��-��������� ����� ������� �����
    foreach ($fields as $cname => $field_settings) {
        echo "<td>";
        if (isset($filters[$cname])) {
            echo "<select multiple id=\"fil_" . $cname . "\" name=\"filter_selected_values[" . $cname . "][]\" onchange='ch_filter()' size=1 style='width:100%'>
                    <option value='all'>���</option></select>";
        }
        echo "</td>";
    }
    echo "</tr>";
}

//echo "<tr id='table_head' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onselect='show_hist(this);' onclick='res=click_row(this);if(res==\"click\"){edit(\"\");}else{edit(\"\")}'>";
echo "<tr id='table_head' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='res=click_row(this);if(res==\"click\"){edit(\"\");}else{edit(\"\")}'>";

if (isset($orders) and count($orders) > 0) {
    echo "<input type=hidden name=order_no_default />"; //��� ���� �����, ��� �� ��������� ���������� ��-��������� ����� ������� �����
}
foreach ($fields as $cname => $field_settings) {
    echo "<th><div>";
    echo "<div>";
    if (isset($field_settings['name']) && $field_settings['name'] != '') echo $field_settings['name'];
    else echo $cname;
    echo "</div>";
    //����������
    if (isset($orders_selected[$cname])) {
        echo "<div style='color:blue;cursor:pointer;' onclick=ch_order(this) field_name=\"" . $cname . "\" order_type='" . $orders_selected[$cname]['type'] . "' order_num='" . $orders_selected[$cname]['num'] . "'></div> ";
    }

    echo "</div></th>";
}
/*foreach($fields as $cname=>$field_settings) {
    echo "<th><table style='border:none;background-color:transparent;'><tr>";
    echo "<td style='border:none;background-color: transparent;'>";
    if(isset($field_settings['name']) and $field_settings['name']!='') echo $field_settings['name'];
    else echo $cname;
    echo "</td>";
    //����������
    if(isset($orders_selected[$cname])) {
        echo "<div style='color:blue;cursor:pointer;display:inline;' onclick=ch_order(this) field_name=\"".$cname."\" order_type='".$orders_selected[$cname]['type']."' order_num='".$orders_selected[$cname]['num']."'></div> ";
    }

    echo "</tr></table></th>";
}*/
//���������� �������� ����������
if (isset($orders_selected)) echo "<script>show_all_orders();</script>";
echo "</tr>";

//$query->setFetchMode(PDO::FETCH_ASSOC);
//$rnum=0; while ($row=$query->fetch()) {$rnum++;

$rnum = 0;
while ($row = oci_fetch_assoc($query)) {
    $rnum++;
    echo "<tr style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='res=click_row(this);if(res==\"click\"){edit(\"" . $row['ID'] . "\");}else{edit(\"\")}'>";
    //echo "<tr style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='click_row(this)'>";
    foreach ($fields as $cname => $field_settings) {
        $val = $row[$cname];
        $align = 'center'; //($cname != 'SUP_NAME' ? 'center' : 'left');
        if ($cname == 'BALANCE')
            echo "<td style='text-align: ".$align. "'>" . number_format($val,0, ',', ' ') . "</td>";
        else echo "<td style='text-align: ".$align. "'>" . $val . "</td>";
        //���� �������� ��� ��������
        if (isset($filters[$cname])) {
            if (!isset($filter_list_values[$cname]) or !in_array($val, $filter_list_values[$cname])) {
                $filter_list_values[$cname][] = $val;
            }
        }
    }
    echo "</tr>";
}
if (0 == $rnum) { // ��� ������
    echo "<script>edit(0)</script>";
}
//���������� �������� ��������
if (isset($filter_list_values)) {
    foreach ($filter_list_values as $filter_name => &$list_values) { //��� ����, ����� �������� �������� �������� ������� ������ �����, ���������� $value ������ �������������� ���� &
        asort($list_values);
        //�������� ������� post �������� ��������, ��� �� ���������� ��� ��������, ���� ��� ����������������, �� ����� ������������ ������ ������������ � ������
        foreach ($list_values as $value) {
            echo "<input type=hidden name='filter_list_values[" . $filter_name . "][]' value='" . $value . "' />";
        }

    }
}

echo "</table>";
echo "</form>";

//���������� �������� � ����� �������
if (isset($filters)) {
    //��������� �������� "���" ��� ��������, � ������� ������ �� �������
    foreach ($filters as $filter_name => $foo) {
        if (!isset($filter_selected_values[$filter_name])) { //�� ������� ������� ������� ���
            echo "<script>";
            echo "document.getElementById(\"fil_" . $filter_name . "\").options[0].selected=true;";
            echo "</script>";
        }
    }
    //���������� �������� ����������
    if (isset($filter_list_values)) {
        foreach ($filter_list_values as $filter_name => $values) {
            foreach ($values as $value) {
                $selected = '';
                if (isset($filter_selected_values[$filter_name]) && is_array($filter_selected_values[$filter_name]) && count($filter_selected_values[$filter_name]) > 0) {
                    foreach ($filter_selected_values[$filter_name] as $selected_val) {
                        if ($selected_val == $value) $selected = 'selected';
                    }
                }
                if (trim($value) != '') {
                    echo "<script>";
                    echo "add_options(document.getElementById(\"fil_" . $filter_name . "\"),'" . $value . "','" . $value . "','" . $selected . "');";
                    echo "</script>";
                }
            }
        }
    }
}

//require_once 'pay_suppliers.php';
echo "<iframe name=ifr1 style='display: none; width: 90%'></iframe>";
//echo '<form action="pay_suppliers.php" method="post" target="ifr1">';
echo '<form action="" method="post" target="ifr1">';
echo "<input name='pay_visits' type='submit' value='��������� �� ������' style='font-weight: bold; background-color: green; color: wheat; height: 2em' />";
echo '</form>';
if (isset($pay_visits)) {
    pay_visits(FALSE);
    echo "<script>parent.location.reload();</script>";
}

function pay_visits($bDebug=TRUE)
{
    if (!$bDebug)
        $table_name = " CALL_BASE ";
    else $table_name = " CALL_BASE_TEST ";

    $load_sql = "select cb.ID, sa.SUPPLIER_ID, sac.COST_VISIT from " . $table_name . " cb "; //cb.SOURCE_AUTO_ID,
    $load_sql .= " LEFT JOIN SOURCE_AUTO sa ON sa.ID = cb.SOURCE_AUTO_ID";
    $load_sql .= " LEFT JOIN SOURCE_AUTO_COST sac ON sac.SOURCE_AUTO_ID = cb.SOURCE_AUTO_ID";
    $load_sql .= " where DATE_CALL >= '01.06.2019' and cb.STATUS_ID in (".STATUS_CLINIC.",".STATUS_CLINIC_NOT.")
    and (PAY_SUPPLIER is NULL or PAY_SUPPLIER = 0) and sac.COST_VISIT > 0 and sac.DELETED is NULL 
    and cb.ID in (select distinct base_id from visit_hist)";
//$load_sql .= " order by cb.SOURCE_AUTO_ID";
//var_dump($load_sql);

    require_once '../funct.php';
//require_once "../med/conn_string.cfg.php";
    //include("../med/conn_string.cfg.php");
    if (TRUE == DEBUG_MODE) echo "<textarea>" . $load_sql . "</textarea><br/>";
    //$q = OCIParse($c, $load_sql);
    $q = OCIParse(GetData::GetConnect(), $load_sql);
    if (OCIExecute($q)) {
        //$cur_sa = -1;
        //$amount_visit = $suppl_id = 0;
        while (OCIFetch($q)) {
            $Base_Id = OCIResult($q, "ID");
            //$sra_id = OCIResult($q,"SOURCE_AUTO_ID");
            $suppl_id = OCIResult($q, 'SUPPLIER_ID');
            $amount_visit = OCIResult($q, 'COST_VISIT');

            //if ($amount > 0) { // �� ��������� ��������, ���� ����� �������� ������� ?
            $upd_pay = "UPDATE " . $table_name . " SET PAY_SUPPLIER = '{$amount_visit}' WHERE ID = '{$Base_Id}'";
            if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
            GetData::my_log($upd_pay, FALSE);
            $query = OCIParse(GetData::GetConnect(), $upd_pay);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($upd_pay, TRUE);

            $upd_pay = "UPDATE SUPPLIERS SET BALANCE = BALANCE - '{$amount_visit}' WHERE ID = '{$suppl_id}'";
            if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
            GetData::my_log($upd_pay, FALSE);
            $query = OCIParse(GetData::GetConnect(), $upd_pay);
            $query_result = OCIExecute($query);
            if (!$query_result) GetData::my_log($upd_pay, TRUE);
            //}
        }
    }
}
?>
</body>
</HTML>