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
<script>function edit(id) {parent.fr_source_cost_edit.location = 'source_auto_cost_edit.php?id=' + id}</script>
<body style="margin: 0">
<?php
extract($_REQUEST);
DEFINE("SERVICE_LIST", array('Любая услуга', 'Стоматология', 'Косметология', 'Гинекология', 'Пластика', 'Трихология', 'Мишлен'));

//построение запроса: запрос должен иметь адекватные названия полей на выходе, эти названия будут отражены в шапке таблицы
//$sql_text = "SELECT sa.ID, BNUMBER, sa.NAME||'('||st.NAME||')' as NAME, st.NAME as SOURCE_TYPE, serv.NAME as SERVICE, SERVICE_ID, IN_ROUTE_ID,
$sql_text = "SELECT sa.ID, BNUMBER, sa.NAME as NAME, st.NAME as SOURCE_TYPE, SERVICE_ID, IN_ROUTE_ID, 
sa.CITY_ID, CITY, SUP_NAME, COST_ORDER, COST_VISIT,
to_char(sa.Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED FROM SOURCE_AUTO sa 
LEFT JOIN SOURCE_TYPE st ON st.ID = sa.SOURCE_TYPE
LEFT JOIN CITIES ci ON ci.ID = sa.CITY_ID
LEFT JOIN SOURCE_AUTO_COST sac ON sac.SOURCE_AUTO_ID = sa.ID
LEFT JOIN SUPPLIERS sup ON sup.ID = sa.SUPPLIER_ID
WHERE sa.ID != -1 and sa.DELETED is NULL and sac.DELETED is NULL
/*filters*/ 
/*orders*/ 
";
//, COST_ORDER+COST_VISIT as COST_ITOG
//serv.NAME as SERVICE,  LEFT JOIN SERVICES serv ON serv.ID = sa.SERVICE_ID
//WHERE sa.ID != -1 and sa.DELETED is NULL and sac.DELETED is NULL

//Настрока полей
//Подмассив name - имя столбца, отображаемое на странице
//Подмассив case - выражение для поля, по которому работает where и order
$fields = array(
    "ID" => "", "NAME" => array("name" => "Название источника", "case"=>"sa.NAME"),
    "SUP_NAME" => array("name" => "Поставщик"),
    "SOURCE_TYPE" => array("name" => "Тип", "case"=>"st.NAME"),
    "BNUMBER" => "",
    //"SERVICE" => array("name" => "Услуга", "case"=>"serv.NAME"),
    "SERVICE_ID" => array("name" => "Услуга", "case"=>"SERVICE_ID"),
    "COST_ORDER" => array("name" => "Цена заявки"), "COST_VISIT" => array("name" => "Цена прихода"),
    //"COST_ITOG" => array("name" => "Цена итог"),
    "IN_ROUTE_ID" => array("name" => "ROUTE_ID"),
    //"OKTELL_PHONE_PREFIX" => array("name" => "Префикс"),
    "CITY" => array("name" => "Город"),//,"case"=>"sa.CITY_ID"),
    //"DELETED" => array("name" => "Удалено")
);

// Включение фильтров: массив с номерами полей таблицы, начиная с 1.
// Чтобы данные фильтры работали, должно работать условие where по названию полей запроса
// Место в запросе, куда нужно вставить фитьтр (начиная с and) должно быть обозначено комментарием "/*filters*/"
// если фильтру назначен подмассив со значениями, этот массив устанавливает значения фильтра по умолчанию
$filters = array(
    "BNUMBER" => "",
    "NAME" => "",
    "SUP_NAME" => "",
    "SOURCE_TYPE" => "",
    "SERVICE_ID" => "",
    "IN_ROUTE_ID" => "",
    //"OKTELL_PHONE_PREFIX" => "",
    "CITY" => "",
    //"DELETED" => "NULL"
);

// Включение возможности сортировки пустое значение включает возможность сортировки.
// Для задания параметров сортировки по умолчанию можно использовать up,asc - по возрастанию; down,desc - по убыванию
$orders = array(
    "ID" => "",
    "BNUMBER" => "",
    "NAME" => "asc",
    "SUP_NAME" => "",
    "SOURCE_TYPE" => "",
    "SERVICE_ID" => "",
    "IN_ROUTE_ID" => "",
    "COST_ORDER" => "",
    "COST_VISIT" => "",
    //"OKTELL_PHONE_PREFIX" => "",
    "CITY" => "",
    //"DELETED" => ""
);


//фильтры
if (isset($filters) && count($filters) > 0) {
    if (!isset($filter_no_default)) { //выставление фильтра в значение по умолчанию
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

//сортировка
if (isset($orders) and count($orders) > 0) {
    if (!isset($order_no_default)) { //выставление сортировки в значение по умолчанию
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

//require_once 'oktadmin/oktell_conn_string.php';
//$query = $c_okt->query($sql_text);
require_once("med/conn_string.cfg.php");
//var_dump($sql_text);

$query = OCIParse($c, $sql_text);
OCIExecute($query, OCI_DEFAULT);
//$nrows = OCI_Fetch_All($query,$array_source_auto,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
echo "<form name='frm' method='post'>";
echo "<table class='white_table' align='center'>";

//Шапка
echo "<tr onmouseover='filters_open(this)' onmouseout='filters_close(this)'>";
//фильтры
if (isset($filters) and count($filters) > 0) {
    echo "<input type=hidden name=filter_no_default />"; //это поле нужно, чтобы отключить фильтр по-умолчанию после сабмита формы
    foreach ($fields as $cname => $field_settings) {
        echo "<td>";
        if (isset($filters[$cname])) {
            echo "<select multiple id=\"fil_" . $cname . "\" name=\"filter_selected_values[" . $cname . "][]\" onchange='ch_filter()' size=1 style='width:100%'>
                    <option value='all'>ВСЕ</option></select>";
        }
        echo "</td>";
    }
    echo "</tr>";
}

echo "<tr id='table_head' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='res=click_row(this);if(res==\"click\"){edit(\"\");}else{edit(\"\")}'>";
//echo "<tr id='table_head' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)'>";
if (isset($orders) and count($orders) > 0) {
    echo "<input type=hidden name=order_no_default />"; //это поле нужно, что бы отключить сортировку по-умолчанию после сабмита формы
}
foreach ($fields as $cname => $field_settings) {
    echo "<th><div>";
    echo "<div>";
    if (isset($field_settings['name']) && $field_settings['name'] != '') echo $field_settings['name'];
    else echo $cname;
    echo "</div>";
    //сортировка
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
    //сортировка
    if(isset($orders_selected[$cname])) {
        echo "<div style='color:blue;cursor:pointer;display:inline;' onclick=ch_order(this) field_name=\"".$cname."\" order_type='".$orders_selected[$cname]['type']."' order_num='".$orders_selected[$cname]['num']."'></div> ";
    }

    echo "</tr></table></th>";
}*/
//применение настроек сортировки
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
        //var_dump($cname."---".$row[$cname]);
        if ('SERVICE_ID' == $cname) {
            if (1 == strlen($row[$cname]))
                $val = SERVICE_LIST[$row[$cname]];
            else {
                $val = '';
                foreach (explode(',',$row[$cname]) as $kk=>$valkk) {
                    if (isset($valkk) && '' != $valkk)
                        $val .= SERVICE_LIST[$valkk] . ', ';
                    else $val .= SERVICE_LIST[0];
                }
                if (strchr($val, ','))
                    $val = substr($val,0,strlen($val)-2);
            }
        }
        else $val = $row[$cname];
        $align = ($cname != 'NAME' ? 'center' : 'left');
        echo "<td style='text-align: ".$align. "'>" . $val . "</td>";
        //сбор значений для фильтров
        if (isset($filters[$cname])) {
            if (!isset($filter_list_values[$cname]) or !in_array($val, $filter_list_values[$cname])) {
                $filter_list_values[$cname][] = $val;
            }
        }
    }
    echo "</tr>";
}
//сортировка значений фильтров
if (isset($filter_list_values)) {
    foreach ($filter_list_values as $filter_name => &$list_values) { //Для того, чтобы напрямую изменять элементы массива внутри цикла, переменной $value должен предшествовать знак &
        asort($list_values);
        //передаем методом post значения фильтров, что бы отображать все значения, если это закомменторовать, то будут отображаться только существующие в наборе
        foreach ($list_values as $value) {
            echo "<input type=hidden name='filter_list_values[" . $filter_name . "][]' value='" . $value . "' />";
        }

    }
}

echo "</table>";
echo "</form>";

//заполнение фильтров в шапке таблицы
if (isset($filters)) {
    //установка значения "ВСЕ" для фильтров, в которых ничего не выбрано
    foreach ($filters as $filter_name => $foo) {
        if (!isset($filter_selected_values[$filter_name])) { //по данному фильтру выбрано ВСЕ
            echo "<script>";
            echo "document.getElementById(\"fil_" . $filter_name . "\").options[0].selected=true;";
            echo "</script>";
        }
    }
    //заполнение фильтров значениями
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
?>
</body>
</HTML>
