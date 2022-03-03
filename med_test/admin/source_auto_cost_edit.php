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
//$backurl = "source_auto_cost_edit.php";
$backurl = "frames.php?page=2";

if (isset($id) && $id != '') {
    $sql_text = "SELECT sa.ID, BNUMBER, NAME, SOURCE_TYPE, SERVICE_ID, IN_ROUTE_ID, CITY_ID, 
COST_ORDER, COST_VISIT, sa.SUPPLIER_ID,
to_char(sac.DATE_ADD,'dd.mm.yyyy hh24:mi:ss') AS DATE_ADD, 
to_char(sac.DELETED,'dd.mm.yyyy hh24:mi:ss') AS DELETED 
FROM SOURCE_AUTO sa
LEFT JOIN SOURCE_AUTO_COST sac ON sac.SOURCE_AUTO_ID = sa.ID
WHERE sa.ID = " . $id . " and sac.deleted is NULL and rownum <= 1 ORDER BY DATE_ADD desc"; // берем последнюю запись по источнику
    //LEFT JOIN SUPPLIERS sup ON sup.ID = sa.SUPPLIER_ID
    //var_dump($sql_text);
//    to_number(sac.COST_ORDER, '9999.99',AMERICAN) as COST_ORDER, to_number(sac.COST_VISIT, '9999.99',AMERICAN) as COST_VISIT,

    $query = OCIParse($c, $sql_text);
    OCIExecute($query, OCI_DEFAULT);
    $arr_source_auto = OCI_Fetch_Array($query);

    echo "<form>";
    echo "<div id='old_ist_name'><label for='old_source_id'> Источник: </label>";
    echo "<select name='old_source_id' id='old_source_id' style='width: 70%'>";
    echo "<option value=''>Создать новый источник</option>";
    GetData::GetSourceAuto(FALSE,NULL,FALSE,'Name','asc',FALSE,FALSE);
    foreach(GetData::$array_source_auto as $key => $value) {
        echo "<option value='".$value["ID"]."'>".$value["NAME"]."</option>";
    }
    echo "</select>";
    echo "<script>$('#old_source_id').val(".$id.").change();</script>";

    GetData::GetServices(FALSE,FALSE,NULL,FALSE);
    echo "<div style='width: 170px; float: right;'><label for='serv_id' style='float: left;'> Услуга:&nbsp;</label>";
    echo "<select multiple name='serv_id[]' id='serv_id' size='6'>";
    if (!isset($arr_source_auto['SERVICE_ID']) || '' == trim($arr_source_auto['SERVICE_ID']))
        echo "<option value='' selected>Любая услуга</option>";
    else echo "<option value=''>Любая услуга</option>";
    foreach (GetData::$array_services as $row) {
        if (1 == strlen($arr_source_auto['SERVICE_ID']))
            if ($arr_source_auto['SERVICE_ID'] != $row['ID']) $selected='';
            else $selected=" selected";
        else {
            $row_arr = explode(',',$arr_source_auto['SERVICE_ID']);
            if (!in_array($row['ID'],$row_arr)) $selected='';
            else $selected=" selected";
        }
        echo "<option value='".$row['ID']."'".$selected.">".$row['NAME']."</option>";
    }
    echo "</select></div></div>";

    echo "<div id='new_ist_name' style='display: none'><label for='new_source_name'> Новый источник: </label>";
    echo "<input type='text' name='new_source_name' id='new_source_name' style='width: 750px' value='" . ($arr_source_auto ? $arr_source_auto['NAME'] : "") . "'></input></div>";

    echo "<label for='s_type'>Тип источника: </label>";
    echo "<select name='s_type'>";
    GetData::GetSourceType(FALSE,FALSE);
    foreach (GetData::$array_stype as $row) {
        if($arr_source_auto['SOURCE_TYPE']==$row['ID']) $selected=" selected"; else $selected='';
        echo "<option value='".$row['ID']."'".$selected.">".$row['NAME']."</option>";
    }
    echo "</select>";

    echo "<label for='bnumber'> BNUMBER: </label>";
    echo "<input type='text' name='bnumber' value='" . ($arr_source_auto ? $arr_source_auto['BNUMBER'] : "") . "'></input>";
    echo "<label for='route'> ROUTE ID: </label>";
    echo "<input type='text' name='route' value='" . ($arr_source_auto ? $arr_source_auto['IN_ROUTE_ID'] : "") . "'></input>";
    //echo "<label for='prefix'> Префикс: </label>";
    //echo "<input type='text' name='prefix' value='" . ($arr_source_auto ? $arr_source_auto['OKTELL_PHONE_PREFIX'] : "") . "'></input>";
    //echo "<input type='text' name='prefix' value='---'></input>";

    echo "<label for='s_city'> Город: </label>";
    echo "<select name='s_city'>";
    echo "<option value=''>Любой город</option>";
    GetData::GetCities(FALSE,FALSE);
    foreach (GetData::$array_cities as $row) {
        if($arr_source_auto['CITY_ID']==$row['ID']) $selected=" selected"; else $selected='';
        echo "<option value='".$row['ID']."'".$selected.">".$row['CITY']."</option>";
    }
    echo "</select>";
    echo "<br/><label for='s_suppl'> Поставщик: </label>";
    echo "<select name='s_suppl'>";
    echo "<option value=''>Неопределен</option>";
    GetData::GetProviders(TRUE);
    foreach (GetData::$array_providers as $row) {
        if($arr_source_auto['SUPPLIER_ID']==$row['ID']) $selected=" selected"; else $selected='';
        echo "<option value='".$row['ID']."'".$selected.">".$row['SUP_NAME']."</option>";
    }
    echo "</select>";
    echo "<label for='cost_order'> Стоимость принятой заявки: </label>";
    //echo "<input type='number' step='0.01' name='cost_order' value='" . ($arr_source_auto['COST_ORDER'] != '' ? $arr_source_auto['COST_ORDER'] : 0) . "'></input>";
    echo "<input type='number' name='cost_order' value='" . ($arr_source_auto['COST_ORDER'] != '' ? $arr_source_auto['COST_ORDER'] : 0) . "'></input>";
    echo "<label for='cost_visit'> Стоимость посещенной заявки: </label>";
    //echo "<input type='number' step='0.01' name='cost_visit' value='" . ($arr_source_auto['COST_VISIT'] != '' ? $arr_source_auto['COST_VISIT'] : 0) . "'></input>";
    echo "<input type='number' name='cost_visit' value='" . ($arr_source_auto['COST_VISIT'] != '' ? $arr_source_auto['COST_VISIT'] : 0) . "'></input>";
   echo '<div>
<input type="submit" name="Saving" value="Сохранить" style="font-weight: bold; background-color: green; color: wheat; height: 2em">
<input type="hidden" name = "source_id" value="'.$id.'">
</div>';
    echo "</form>";
}

if (isset($Saving)) {
    //var_dump($old_source_id);
    $insertstr = '';
    if($old_source_id=='') {
        $checkstr = "select id from SOURCE_AUTO t where t.name=:source_name and deleted is null and source_type=".$s_type;
        if (DEVICE_PHONE == $s_type)
            $checkstr .= " and bnumber like '".$bnumber."'";
        //var_dump($checkstr);
        $q=OCIParse($c, $checkstr);
        OCIBindByname($q,":source_name",$new_source_name);
        OCIExecute($q);
        if(OCIFetch($q)) {
            $source_id=OCIResult($q,"ID");
        }
        else {
            // TODO вставить проверку на существование bnumber для телефонных источников

            $insertstr = "insert into SOURCE_AUTO (ID, NAME, SOURCE_TYPE, BNUMBER, SERVICE_ID, CITY_ID, SUPPLIER_ID, IN_ROUTE_ID) 
values (SEQ_SOURCE_AUTO_ID.nextval,'".$new_source_name."','".$s_type."','".$bnumber."','".('' == $serv_id[0]?'':implode(',',$serv_id))."','".$s_city."','".$s_suppl."','".$route."') returning id into :id";
            $ins=OCIParse($c, $insertstr);
            //OCIBindByName($ins,":source_name",$new_source_name);
            OCIBindByName($ins,":id",$source_id,16);
            OCIExecute($ins,OCI_DEFAULT);
        }
        //die();
    }

    if ('' == $insertstr) {
        $updatestr = "update SOURCE_AUTO set NAME = '" . $new_source_name . "', SOURCE_TYPE = '" . $s_type . "', 
        SERVICE_ID = '" . ('' == $serv_id[0]?'':implode(',',$serv_id)) . "', CITY_ID = '" . $s_city . "', SUPPLIER_ID = '" . $s_suppl . "', 
        IN_ROUTE_ID = '" . $route . "'";
        if (DEVICE_PHONE == $s_type)
            $updatestr .= ", BNUMBER = '".$bnumber."'";
        $updatestr .= " where id = ".$source_id;
        GetData::my_log($updatestr,FALSE);
        $ins = OCIParse($c, $updatestr);
        $query_result = OCIExecute($ins, OCI_DEFAULT);
        if (!$query_result)
            GetData::my_log($updatestr,TRUE);
    }

    $checkstr = "SELECT ID FROM SOURCE_AUTO_COST WHERE SOURCE_AUTO_ID = ".$source_id." AND DELETED is NULL";
    $objParse = OCIParse(GetData::GetConnect(), $checkstr);
    OCIExecute($objParse);
    $objResult = OCI_Fetch_Row($objParse);
    if ($objResult) { // отменяем старую запись
        $updatestr = 'UPDATE SOURCE_AUTO_COST SET DELETED = sysdate WHERE ID = '.$objResult[0];
        $query = OCIParse(GetData::GetConnect(), $updatestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }

    //Вставляем данные
    if (!isset($cost_order)) $cost_order = 0;
    if (!isset($cost_visit)) $cost_visit = 0;
    $insertstr = "INSERT INTO SOURCE_AUTO_COST (ID, SOURCE_AUTO_ID, COST_ORDER, COST_VISIT, DATE_ADD) 
VALUES (SEQ_SOURCE_AUTO_COST_ID.nextval, ".$source_id.",".$cost_order.",".$cost_visit.",sysdate)";
    $query = OCIParse(GetData::GetConnect(), $insertstr);
    $query_result = OCIExecute($query);
    oci_free_statement($query);

    if ($query_result) {
        print "<script>function reload() {parent.location = \"$backurl\"} setTimeout('reload()', 3000);</script>";
        echo "<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
    }
    unset($Saving);
}
?>
<script>
    var old_select = document.getElementById("old_source_id");
    if (old_select) {
        old_select.onchange = function () {
            var field = document.getElementById('new_ist_name');
            if (field) {
                if (old_select.value !== '') field.style.display = 'none';
                else field.style.display = '';
            }
        }
    }
</script>

</body>
</html>
