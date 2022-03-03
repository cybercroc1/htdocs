<?php
require_once("../funct.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <link href="source_auto_cost.css" rel="stylesheet" type="text/css">
    <script src="../js/jquery.datetimepicker.full.js"></script>
    <title>Ввод данных платежа</title>
<?php
extract($_REQUEST);
require_once("med/conn_string.cfg.php");

if (isset($getdet))
{
    if ($getdet > 0) {
        $strWhere = "supplier_id = " . $getdet;
        GetData::GetSourceAuto($strWhere,NULL,FALSE,'Name','asc',FALSE,FALSE);
        $sel = " Источник (авто):&nbsp;<select id=\"SelectReservoir\" name=\"SelectReservoir\" style=\"width: 68%;\">";
        $sel_opt = '';
        foreach (GetData::$array_source_auto as $key => $value) {
            if (DEVICE_PHONE == $value['SOURCE_TYPE'])
                $sel_opt .= "<option value=\"" . $value['ID'] . "\">" . $value['NAME'] . " (" . $value['BNUMBER'] . ")</option>";
            else $sel_opt .= "<option value=\"" . $value['ID'] . "\">" . $value['NAME'] . " (E-mail)</option>";
        }
        $sel .= $sel_opt."</select>";
        echo "<script>parent.document.getElementById('S_AutoSel').innerHTML='" . $sel . "';</script>";

        $sel = " Перевести на:&nbsp;<select id=\"DestReservoir\" name=\"DestReservoir\" style=\"width: 37%;\">";
        $sel .= $sel_opt."</select>";
        echo "<script>parent.document.getElementById('S_AutoDest').innerHTML='" . $sel . "';</script>";

        echo "<script>var elem = parent.document.getElementById('S_Auto'); if (elem) elem.focus();</script>";

        echo "<script>var field = parent.document.getElementById('S_AutoSel'); field.style.display = '';</script>";
        echo "<script>var field = parent.document.getElementById('S_AutoDest'); field.style.display = '';</script>";
        echo "<script>var field = parent.document.getElementById('new_ist_name'); field.style.display = 'none';</script>";
        echo "<script>var field = parent.document.getElementById('get_commis'); field.style.display = '';</script>";
    }
    else {
        echo "<script>var field = parent.document.getElementById('S_AutoSel'); field.style.display = 'none';</script>";
        echo "<script>var field = parent.document.getElementById('S_AutoDest'); field.style.display = 'none';</script>";
        echo "<script>var field = parent.document.getElementById('new_ist_name'); field.style.display = '';</script>";
        echo "<script>var field = parent.document.getElementById('get_commis'); field.style.display = 'none';</script>";
    }
    exit;
}
?>
</head>

<body>
<?php
$backurl = "frames.php?page=1";

if (isset($Saving) && isset($sum_add) && isset($date_pay)) {
    //var_dump($S_Type);
    if ($sum_add != 0) {
        $insertstr = '';
        if ('' == $S_Type) {
            $checkstr = "select id from SUPPLIERS where sup_name='" . $new_sup_name . "' and deleted is null";
            $q = OCIParse($c, $checkstr);
            OCIExecute($q);
            if (OCIFetch($q)) {
                $supplier_id = OCIResult($q, "ID");
            } else { // сразу устанавливаем и баланс поставщика
                $insertstr = "insert into SUPPLIERS (ID, SUP_NAME, BALANCE) 
values (SEQ_SUPPLIERS_ID.nextval,'" . $new_sup_name . "'," . $sum_add . ") returning id into :id";
                $ins = OCIParse($c, $insertstr);
                OCIBindByName($ins, ":id", $supplier_id, 16);
                OCIExecute($ins, OCI_DEFAULT);
            }
        } else {
            $supplier_id = $S_Type;
        }

        if ('' == $insertstr) { // изменяем существующий баланс
            $updatestr = "update SUPPLIERS SET BALANCE = BALANCE + " . $sum_add . " where id = " . $supplier_id;
            GetData::my_log($updatestr,FALSE);
            $ins = OCIParse($c, $updatestr);
            $query_result = OCIExecute($ins, OCI_DEFAULT);
            if (!$query_result) GetData::my_log($updatestr,TRUE);
        }

        //Вставляем данные пополнения баланса
        $insertstr = "INSERT INTO SUPPLIER_BALANCE (ID, SUPPLIER_ID, SOURCE_ID, RUB, DATE_ADD, DATE_FACT) 
VALUES (SEQ_SUPPLIER_BALANCE_ID.nextval, " . $S_Type . "," . $SelectReservoir . "," . $sum_add . ",
 to_date('{$date_pay}','DD.MM.YYYY hh24:mi'), sysdate)";
        GetData::my_log($insertstr,FALSE);
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            //print "<script>function reload() {parent.location = \"$backurl\"} setTimeout('reload()', 3000);</script>";
            //echo "<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
            echo "<p style='color: green'>Данные успешно добавлены в таблицу.</p>";
        } else {
            GetData::my_log($insertstr,TRUE);
            echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
        }
        oci_free_statement($query);
    } else {
        echo "<p style='color: red'>Сумма пополнения не должна быть нулевой!</p>";
    }
    unset($Saving);
}

if (isset($Kommis) && isset($sum_get) && isset($date_get)) {
    //Вставляем данные списания комиссии
    if ($sum_get != 0) {
        $insertstr = "INSERT INTO SUPPLIER_COMMIS (ID, SUPPLIER_ID, SOURCE_ID, RUB_GET, DATE_GET, DATE_FACT) 
VALUES (SEQ_SUPPLIER_COMMIS_ID.nextval, " . $S_Type . "," . $SelectReservoir . "," . $sum_get . ",
 to_date('{$date_get}','DD.MM.YYYY hh24:mi'), sysdate)";
        GetData::my_log($insertstr,FALSE);
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            //print "<script>function reload() {parent.location = \"$backurl\"} setTimeout('reload()', 3000);</script>";
            //echo "<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
            echo "<p style='color: green'>Данные списания комиссии добавлены в таблицу.</p>";
        } else {
            GetData::my_log($insertstr,TRUE);
            echo "<p style='color: red'>Произошла ошибка списания комиссии!</p>";
        }
        oci_free_statement($query);
    } else {
        echo "<p style='color: red'>Сумма списания не должна быть нулевой!</p>";
    }
    unset($Kommis);
}

if (isset($Moving) && isset($sum_move) && isset($date_move)) {
    $insertstr = '';

    if ($sum_move == 0) {
        echo "<p style='color: red'>Сумма перевода не должна быть нулевой!</p>";
    }
    elseif ($SelectReservoir == $DestReservoir) {
        echo "<p style='color: red'>Источники должны быть разными!</p>";
    }
    else //if ($sum_move != 0 && $SelectReservoir != $DestReservoir)
    {
        // данные списания баланса
        $insertstr = "INSERT INTO SUPPLIER_BALANCE (ID, SUPPLIER_ID, SOURCE_ID, RUB, DATE_ADD, DATE_FACT,COMMENTS) 
VALUES (SEQ_SUPPLIER_BALANCE_ID.nextval, " . $S_Type . "," . $SelectReservoir . "," . (-1) * $sum_move . ",
 to_date('{$date_move}','DD.MM.YYYY hh24:mi'), sysdate, 'Забрали')";
        GetData::my_log($insertstr,FALSE);
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        if (!$query_result) GetData::my_log($insertstr,TRUE);

        // данные пополнения баланса
        $insertstr = "INSERT INTO SUPPLIER_BALANCE (ID, SUPPLIER_ID, SOURCE_ID, RUB, DATE_ADD, DATE_FACT,COMMENTS) 
VALUES (SEQ_SUPPLIER_BALANCE_ID.nextval, " . $S_Type . "," . $DestReservoir . "," . $sum_move . ",
 to_date('{$date_move}','DD.MM.YYYY hh24:mi'), sysdate, 'Отдали')";
        GetData::my_log($insertstr,FALSE);
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            //print "<script>function reload() {parent.location = \"$backurl\"} setTimeout('reload()', 3000);</script>";
            //echo "<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
            echo "<p style='color: green'>Данные успешно добавлены в таблицу.</p>";
        } else {
            GetData::my_log($insertstr,TRUE);
            echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
        }
        oci_free_statement($query);
    }
    unset($Moving);
}

if (isset($id) && $id != 0 && GetData::GetProviders(TRUE) > 0) {
    echo "<form action='' method='post'>";
    echo "<iframe name='ifr_all' style='display: none; width: 95%;'></iframe>";
    echo "<label for='S_Type'>Поставщик:&nbsp;</label>";
    echo "<select id='S_Type' name='S_Type' onchange='ifr_all.location=\"admin_supplier_edit.php?getdet=\"+this.value'>";
    echo "<option value=''>Создать поставщика</option>";
    foreach (GetData::$array_providers as $key => $value) {
        echo "<option value='" . $value["ID"] . "'>" . $value["SUP_NAME"] . "</option>";
    }
    echo "</select>";
    //echo '<script>$("#S_Type").prop("selectedIndex",0).change();</script>';
    echo "<script>$('#S_Type').val(" . $id . ").change();</script>";

    echo "<span id='S_AutoSel'>&nbsp;</span>";

    echo "<span id='new_ist_name' style='display: none'><label for='new_sup_name'> Новый поставщик: </label>";
    echo "<input type='text' name='new_sup_name' id='new_sup_name' style='width: 67%' value='' placeholder='Введите название поставщика'/></span>";

    echo "<div><label for='sum_add'>Сумма платежа: </label>";
    echo "<input type='number' name='sum_add' placeholder='Введите сумму' style='width: 9em'/>";
    $date_pay = date("d.m.Y H:i");
    echo "<label for='date_pay'> Дата платежа: </label>";
    echo "<input type='text' id='date_pay' name='date_pay' value='".$date_pay."' autocomplete='off' placeholder='Дата платежа' style='width: 9em'/>";
    echo '<input type="submit" name="Saving" value="Добавить сумму" style="font-weight: bold; background-color: green; color: wheat; height: 2em"></div>';
    echo "<div id='get_commis'><label for='sum_get'> Сумма комиссии: </label>";
    echo "<input type='number' name='sum_get' placeholder='Введите сумму' style='width: 9em'/>";
    list($day, $month, $year) = explode(".", date("d.m.Y"));
    $date_get = date("d.m.Y H:i", mktime(10, 0, 0, $month, 1, $year));
    echo "<label for='date_get'> Дата списания: </label>";
    echo "<input type='text' id='date_get' name='date_get' value='".$date_get."' autocomplete='off' placeholder='Дата списания' style='width: 9em'/>";
    echo '<input type="submit" name="Kommis" value="Списать комиссию" style="font-weight: bold; background-color: green; color: wheat; height: 2em;"></div>';

    echo "<div id='move_pay'><label for='sum_move'> Сумма перевода: </label>";
    echo "<input type='number' name='sum_move' placeholder='Введите сумму' style='width: 9em'/>";
    echo "<span id='S_AutoDest'>&nbsp;</span>";
    $date_move = date("d.m.Y H:i");
    echo "<label for='date_move'> Дата перевода: </label>";
    echo "<input type='text' id='date_move' name='date_move' value='".$date_move."' autocomplete='off' placeholder='Дата перевода' style='width: 9em'/>";
    echo '<input type="submit" name="Moving" value="Перевести сумму" style="font-weight: bold; background-color: green; color: wheat; height: 2em;"></div>';

    echo '<input type="hidden" name = "supplier_id" value="' . $id . '"/>';
    echo "</form>";
}
?>

<script>
    //$(document).ready(function(){
    $('#date_pay').datetimepicker({
        format: 'd.m.Y H:i',
        lang: 'ru',
        timepicker: true,
        closeOnDateSelect: false
    });
    $('#date_get').datetimepicker({
        format: 'd.m.Y H:i',
        lang: 'ru',
        timepicker: true,
        closeOnDateSelect: false
    });
    $('#date_move').datetimepicker({
        format: 'd.m.Y H:i',
        lang: 'ru',
        timepicker: true,
        closeOnDateSelect: false
    });
    //});
</script>
</body>
</html>
