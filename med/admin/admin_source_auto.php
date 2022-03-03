<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

session_name('medc');
session_start();
extract($_REQUEST);
/*if ($_SERVER['REQUEST_METHOD'] == "POST"){
    header("location:{$_SERVER['PHP_SELF']}");
}*/
require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>'; exit();
}
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "admin_source_auto.php";
//---------------------------------------------------------------------- //
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>Источники рекламы (Auto)</title>
    <meta name="description" content="Источники рекламы (Auto)">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Список BNumber</h3>
<?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
          strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
    echo '<table class="scrolling-table_uie">';
} else {
    echo '<table class="scrolling-table_u">';
}
    $theads = array(
        'ID' => array('name' => 'ID', 'width' => '35'),
        'BNUMBER' => array('name' => 'Б Номер', 'width' => '81'),
        'NAME' => array('name' => 'Наименование', 'width' => '401'),
        'SUPPLIER_ID' => array('name' => 'Поставщик', 'width' => '101'),
        'SOURCE_TYPE' => array('name' => 'Устройство', 'width' => '71'),
        'SERVICE_ID' => array('name' => 'Услуга', 'width' => '81'),
        'CITY_ID' => array('name' => 'Город', 'width' => '51'),
        'DELETED' => array('name' => 'Удалена', 'width' => '121'),
        '' => array('name' => 'Действие', 'width' => '86')
    );

    if (isset($_GET['key'])) {
        $key=$_GET['key'];
        $sort=$_GET['sort'];
    }
    else {
        $key='NAME';
        $sort='asc';
    }
    echo '<thead><tr>';
    foreach ($theads as $k => $thead) {
        if ($k === $key) {
            $img = "../images/".$sort.".png";
            $soort = ($sort == 'desc' ? 'asc' : 'desc');
        } else {
            $img = '';
            $soort = 'asc';
        }
        if ('Действие' != $thead['name']) {
            $get = http_build_query(array('key' => $k, 'sort' => $soort));
            echo "<th style='width:{$thead['width']}px; text-decoration: underline'><img src='$img'><a href=\"?$get\" style='color: black'>{$thead['name']}</a></th>";
        }
        else echo "<th style='width:{$thead['width']}px'>{$thead['name']}</th>";
    }
    echo '</tr></thead>';
    echo '<tbody>';

    $max_id = 0; // для вставки новой строки, если потребуется
    if (GetData::GetSourceAuto(NULL, NULL, FALSE, $key, $sort,TRUE) > 0) {
        // Получение списка столбцов
        /*foreach (GetData::$array_source_auto as $source => $row) {
            $field[$source]  = $row[$key];
            $name[$source]  = $row['NAME'];
        }
        if ('desc' == $sort)
            array_multisort($field, SORT_DESC, $name, SORT_ASC, GetData::$array_source_auto);
        else array_multisort($field, SORT_ASC, $name, SORT_ASC, GetData::$array_source_auto);*/
        $sort = ($sort == 'desc' ? 'asc' : 'desc');
        foreach(GetData::$array_source_auto as $key => $value) {
            if (TRUE == ENCODE_UTF) {
                $value['BNUMBER'] = iconv('windows-1251', 'utf-8', $value['BNUMBER']);
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            }

            $ser_ids = $value['SERVICE_ID'];
            if (strlen($ser_ids) < 3)
                $str_serv = (isset($ser_ids) && $ser_ids != SERVICE_ALL ? SERVICE_LIST[$ser_ids] : "Все услуги");
            else {
                $str_serv = '';
                foreach (explode(',',$ser_ids) as $kk=>$valkk) {
                    if (isset($valkk) && '' != $valkk)
                        $str_serv .= SERVICE_LIST[$valkk] . ', ';
                }
                $str_serv = substr($str_serv,0,strlen($str_serv)-2);
            }
            $str_deleted = ($value['DELETED'] ? $value['DELETED'] : "нет");
            $city_id = ($value['CITY_ID'] ? $value['CITY_ID'] : 0);
            if ($value['ID'] > $max_id)
                $max_id = $value['ID'];

            echo '<tr><td style="text-align: center; width: 35px">' . $value['ID'] . '</td>
				<td style="text-align: center; width: 80px">' . $value['BNUMBER'] . '</td>
				<td style="text-align: center; width: 400px">' . $value['NAME'] . '</td>
				<td style="text-align: center; width: 100px">' . $value['SUP_NAME'] . '</td>
				<td style="text-align: center; width: 70px">' . DEVICES[$value['SOURCE_TYPE']] . '</td>
				<td style="text-align: center; width: 80px">' . $str_serv . '</td>
				<td style="text-align: center; width: 50px">' . CITIES[$city_id] . '</td>
				<td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
            if ($value['DELETED']) {
                echo '<td style="text-align: center; width: 85px"><a href="?restore_id=' . $value['ID'] . '">Восстановить</a></td></tr>';
            } else {
                echo '<td style="text-align: center; width: 85px"><a href="?del_id=' . $value['ID'] . '">Удалить</a></td></tr>';
            }
        }
    }
    ?>
    </tbody>
</table>

<?php
// Обработка действий
// Если переменная BNumber передана
if (isset($BNumber)) {
    $bCanAdd = 1;
    if (GetData::GetSourceAuto("DELETED IS NULL", NULL, FALSE) > 0) {
        foreach (GetData::$array_source_auto as $key => $value) {
            if (DEVICE_PHONE == $Device && $Device == $value['SOURCE_TYPE']) {
                if (DB_OCI)
                    $bCanAdd = strcasecmp($BNumber, $value['BNUMBER']);
                else $bCanAdd = strcmp(strtolower($BNumber), strtolower($value['1']));
                if ($bCanAdd == 0)
                    break;
            }
            elseif (DEVICE_MAIL == $Device && $Device == $value['SOURCE_TYPE']) {
                if (DB_OCI)
                    $bCanAdd = strcasecmp($Reservoir, $value['NAME']);
                else $bCanAdd = strcmp(strtolower($BNumber), strtolower($value['1']));
                if ($bCanAdd == 0)
                    break;
            }
        }
    }
    if ($bCanAdd == 0) {
        echo "<p style='color: red'>BNumber '".$BNumber."' уже существует.<br /></p>";
    }
    else {
        $max_id++;
        //Вставляем данные
        $insertstr = "INSERT INTO SOURCE_AUTO (ID, BNUMBER, NAME, SOURCE_TYPE, SUPPLIER_ID) 
VALUES (seq_source_auto_id.nextval, '{$BNumber}', '{$Reservoir}', '{$Device}', '{$s_suppl}')";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstr = iconv ('utf-8','windows-1251', $Reservoir);
                $tmpstrbn = iconv ('utf-8','windows-1251', $BNumber);
                $insertstr = "INSERT INTO SOURCE_AUTO (ID, BNUMBER, NAME, SOURCE_TYPE, SUPPLIER_ID) 
VALUES (seq_source_auto_id.nextval, '{$tmpstrbn}', '{$tmpstr}', '{$Device}', '{$s_suppl}')";
			}
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			print "<script language='Javascript'>
					function reload() {location = \"$backurl\"}; setTimeout('reload()', 3000);
					</script>
				<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
        }
    }
}

//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE SOURCE_AUTO SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE SOURCE_AUTO SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
		$query_result = mysqli_query(GetData::GetConnect(), $deletestr);
	}
    if ($query_result) {
        print "<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
			<p style='color: green'>Строка изменена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
	}
}

//Восстанавливаем удаленноеs
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE SOURCE_AUTO SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        print "<script language='Javascript'>
            function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
                </script>
			<p style='color: green'>Строка восстановлена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка восстановления записи.</p>";
    }
}
?>

<div>
    <form action="" method="post">
        <h3>
            <label id="DeviceT" for="Device">Устройство:</label>
            <select id="Device" name="Device" title="Стоматология">
                <option value='<?=DEVICE_PHONE?>'>Телефон</option>
                <option value='<?=DEVICE_MAIL?>'>E-Mail</option>
            </select>
            BNumber: <input type="text" name="BNumber" placeholder="BNumber"><br/>
            Источник:&nbsp;<input type="text" name="Reservoir" style="width: 350px" placeholder="Источник рекламы">
            <label for='s_suppl'> Поставщик: </label>
            <select name='s_suppl'>
                <option value=''>Неопределен</option>
                <?php
                GetData::GetProviders(TRUE);
                foreach (GetData::$array_providers as $row) {
                    if ($arr_source_auto['SUPPLIER_ID'] == $row['ID']) $selected = " selected"; else $selected = '';
                    echo "<option value='" . $row['ID'] . "'" . $selected . ">" . $row['SUP_NAME'] . "</option>";
                }
                ?>
            </select>
            <!--?php
            if (GetData::GetServices(TRUE,FALSE, NULL,FALSE) > 0) {
                echo '<label id="ServiceT" for="Service">&nbsp;Услуга:</label>';
                echo '<select id="Service" name="Service">';
                if (DB_OCI) {
                    foreach(GetData::$array_services as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach(GetData::$array_services as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                echo "</select>";
            }
            ?-->
        </h3>
        <div>
            <input type="submit" name="Adding" value="Добавить в базу" class="add_button">
            <input type="hidden" name = "table_name" value="SOURCE_AUTO">
        </div>
    </form>
</div>

</body>
</html>