<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <link href="source_auto_cost.css" rel="stylesheet" type="text/css">
    <title></title>
</head>
<body>
<?php
extract($_REQUEST);

require_once("med/conn_string.cfg.php");
require_once("../funct.php");

GetData::GetProviderBalance($id);
GetData::GetProviderCommis($id, FALSE, NULL, NULL);

echo "<h3 style='margin: 5px;'>История пополнения баланса</h3>";
echo "<table class='white_table' align='center'>";
//Шапка
echo'<tr>
        <th style="width: 130px;">Поставщик</th>
        <th style="width: 450px;">Акция</th>
        <th style="width: 70px;">Цена</th>
        <th style="width: 70px;">Сумма</th>
        <th style="width: 120px;">Дата/Расход</th>
    </tr>';

$cbName = $cbType = '';
$act_name = $act_total = $pay_total = $sa_id = $prev_id = $line = 0;
foreach (GetData::$arr_provider_balance as $item => $value) {
    $sa_id = $value['SOURCE_ID'];
    if ($value['DELETED'] && (!isset($value['RUB']) || 0 == $value['RUB'] || '' == $value['RUB'])) {
        continue;
    }

    $line++;
    if (($value['NAME'] != $cbName || $value['SOURCE_TYPE'] != $cbType || $sa_id != $prev_id) && $line != 1) { // Промежуточный итог
        if (GetData::GetProviderPays($prev_id,TRUE,NULL,NULL) > 0) {
            $spend = -1*GetData::$arr_provider_pay[0]['ITOG'];
            $pay_total += $spend;
        } else $spend = 0;

        echo "<tr>";
        echo "<td colspan='2' style='font-weight: bold; text-align:center'>" . $prev_id.": ".$cbName . "</td>";
        echo "<td style='text-align:center'>" . number_format($cost_order+$cost_visit,0,',',' ') . "</td>";
        echo "<td style='font-weight: bold; text-align:center'>" . number_format($act_name,0,',',' ') . "</td>";
        echo "<td style='font-weight: bold; text-align:center'>" . number_format($spend,0,',',' ') . "</td>";
        echo "</tr>";
        $act_name = 0;
    }
    $act_name += $value['RUB'];
    $act_total += $value['RUB'];

    if ($sa_id != $prev_id) {
        $cbName = $value['NAME'];
        $cbType = $value['SOURCE_TYPE'];

        $q_cost = OCIParse($c, "select COST_ORDER, COST_VISIT from SOURCE_AUTO_COST where DELETED is null AND SOURCE_AUTO_ID=:sa_id");
        OCIBindByName($q_cost, ":sa_id", $sa_id);
        OCIExecute($q_cost, OCI_DEFAULT);
        if (OCIFetch($q_cost)) {
            $cost_order = OCIResult($q_cost, "COST_ORDER");
            $cost_visit = OCIResult($q_cost, "COST_VISIT");
        } else {
            $cost_order = $cost_visit = 0;
        }
    }
    $prev_id = $sa_id;

    if(isset($value['COMMENTS'])) $comment = " (".trim($value['COMMENTS']).")";
    else $comment = '';
    echo "<tr>";
    echo "<td style='text-align:center'>" . $value['SUP_NAME'] . "</td>";
    echo "<td style='text-align:center'>" . $value['NAME'] . " (" . DEVICES[$cbType] . ")".$comment."</td>";
    echo "<td style='text-align:center'>" . number_format($cost_order+$cost_visit,0,',',' ') . "</td>";
    echo "<td style='text-align:center'>" . number_format($value['RUB'],0,',',' ') . "</td>";
    echo "<td style='text-align:center'>" . $value['DATE_ADD'] . "</td>";

    echo "</tr>";
}
// Промежуточный итог для последнего блока
if ($prev_id != 0) {
    if (GetData::GetProviderPays($prev_id, TRUE, NULL, NULL) > 0) {
        //var_dump(GetData::$arr_provider_pay);
        $spend = -1 * GetData::$arr_provider_pay[0]['ITOG'];
        $pay_total += $spend;
    } else $spend = 0;
    echo "<tr>";
    echo "<td colspan='2' style='font-weight: bold; text-align:center'>" . $prev_id.": ".$cbName . "</td>";
    echo "<td style='text-align:center'>" . number_format($cost_order+$cost_visit,0,',',' ') . "</td>";
    echo "<td style='font-weight: bold; text-align:center'>" . number_format($act_name, 0, ',', ' ') . "</td>";
    echo "<td style='font-weight: bold; text-align:center'>" . number_format($spend, 0, ',', ' ') . "</td>";
    echo "</tr>";
}
// Итого
echo "<tr>";
echo "<td colspan='1' style='font-weight: bold; text-align:right'>Всего:&nbsp;</td>";
echo "<td colspan='2' style='font-weight: bold; text-align:center'>(".number_format($act_total+$pay_total,0,',',' ').")</td>";
echo "<td colspan='1' style='font-weight: bold; text-align:center'>" . number_format($act_total,0,',',' ') . "</td>";
echo "<td colspan='1' style='font-weight: bold; text-align:center'>" . number_format($pay_total,0,',',' ') . "</td>";
echo "</tr>";

// Разделл комиссии
echo "<tr>";
echo "<td colspan='5' style='font-weight: bold; text-align:center; color: red; background: bisque;'>Списание комиссии</td>";
echo "</tr>";
$get_total = $sa_id = $prev_id = $act_name = $line = 0;
$cbName = $cbType = '';
foreach (GetData::$arr_provider_get as $item => $value) {
    $sa_id = $value['SOURCE_ID'];
    $line++;
    if (($value['SOURCE_NAME'] != $cbName || $value['SOURCE_TYPE'] != $cbType|| $sa_id != $prev_id) && $line != 1) { // Промежуточный итог
        echo "<tr style='background: floralwhite'>";
        echo "<td colspan='3' style='font-weight: bold; text-align:center'>" . $cbName . "</td>";
        echo "<td colspan='2' style='font-weight: bold; text-align:center'>" . number_format($act_name,0,',',' ') . "</td>";
        echo "</tr>";
        $act_name = 0;
    }
    $act_name += $value['RUB_GET'];
    $get_total += $value['RUB_GET'];
    $prev_id = $sa_id;
    echo "<tr style='background: floralwhite'>";
    echo "<td style='text-align:center'>" . $value['SUP_NAME'] . "</td>";
    if (isset($value['SOURCE_TYPE']))
        echo "<td colspan='2' style='text-align:center'>" . $value['SOURCE_NAME'] . " (" . DEVICES[$value['SOURCE_TYPE']] . ")</td>";
    else echo "<td colspan='2' style='text-align:center'>" . $value['SOURCE_NAME'] . "</td>";
    echo "<td style='text-align:center'>" . number_format($value['RUB_GET'],0,',',' ') . "</td>";
    echo "<td style='text-align:center'>" . $value['DATE_GETT'] . "</td>";
    echo "</tr>";
    $cbName = $value['SOURCE_NAME'];
    $cbType = $value['SOURCE_TYPE'];
}
// Промежуточный итог для последнего блока
if ($sa_id != 0) {
    echo "<tr style='background: floralwhite'>";
    echo "<td colspan='3' style='font-weight: bold; text-align:center'>" . $cbName . "</td>";
    echo "<td colspan='2' style='font-weight: bold; text-align:center'>" . number_format($act_name, 0, ',', ' ') . "</td>";
    echo "</tr>";
}
// Итого
echo "<tr>";
echo "<td colspan='3' style='font-weight: bold; text-align:right'>Всего комиссия:&nbsp;</td>";
//echo "<td colspan='1' style='font-weight: bold; text-align:center'></td>";
echo "<td colspan='2' style='font-weight: bold; text-align:center'>" . number_format($get_total,0,',',' ') . "</td>";
echo "</tr>";
echo "</table>";
?>
</body>
</html>
