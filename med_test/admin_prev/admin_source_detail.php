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
if (!isset($_SESSION['user_role']) || USER_ADMIN != $_SESSION['user_role']) {
    if (USER_SUPER != $_SESSION['user_role'] /*|| (84 != $_SESSION['login_id_med'] && 11 != $_SESSION['login_id_med'])*/)
    { // только для Грачевой и Алибековой?
        echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>';
        exit();
    }
}
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "admin_source_detail.php";
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
    <title>Детализация Источников рекламы</title>
    <meta name="description" content="Детализация Источников рекламы">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Список Детализации Источников</h3>
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_ie">
        <?php } else { ?>
    <table class="scrolling-table">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px;">ID</th>
        <th style="width: 250px;">Источник</th>
        <th style="width: 200px;">Детализация</th>
        <th style="width: 230px;">Услуги</th>
        <th style="width: 120px;">Удалена</th>
        <th style="width: 100px;">Действие</th>
    </tr></thead>
    <tbody>
    <?php
    //$max_id = 0; // для вставки новой строки, если потребуется
    if (DB_OCI) {
        $selectstr = "SELECT smd.ID, sm.Name as smName, smd.Name as smdName, sm.IN_DEP, to_char(smd.Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted 
                      FROM SOURCE_MAN sm LEFT JOIN SOURCE_MAN_DETAIL smd ON sm.ID = smd.SOURCE_MAN_ID";
//FROM SOURCE_MAN_DETAIL smd, SOURCE_MAN sm WHERE smd.ID != ".SOURCE_ALL." AND sm.ID = smd.SOURCE_MAN_ID";
        if (USER_ADMIN != $_SESSION['user_role']) {
            $selectstr .= " where ( instr(in_dep, ".SERVICE_KOSM.") != 0 or instr(in_dep, ".SERVICE_PLAS.") != 0 or instr(in_dep, ".SERVICE_TRIH.") != 0) ";
        }
        $selectstr .= " ORDER BY sm.NAme, smd.Name";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result && GetData::GetServices(FALSE,TRUE,NULL) > 0) {
            while ($result = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $result['SMDNAME'] = iconv('windows-1251', 'utf-8', $result['SMDNAME']);
                    $result['SMNAME'] = iconv('windows-1251', 'utf-8', $result['SMNAME']);
                }
                $str_deleted = ($result['DELETED'] ? $result['DELETED'] : "нет");
                /*if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];*/

                if ('' == $result['IN_DEP'])
                    $serv_name = 'Не определены';
                elseif (SERVICE_ALL === $result['IN_DEP'])
                    $serv_name = 'Общее';
                else {
                    $serv_name = '';
                    $arr_dep = explode(',', $result['IN_DEP']);
                    for ($ii=0; $ii < sizeof($arr_dep); $ii++) {
                        foreach ($_POST['array_services'] as $key_dep => $deps) {
                            if (TRUE == ENCODE_UTF)
                                $deps['NAME'] = iconv('windows-1251', 'utf-8', $deps['NAME']);
                            if ($arr_dep[$ii] == -1) {
                                $serv_name .= "Общее, ";
                                break;
                            }
                            elseif ($arr_dep[$ii] == $deps['ID']) {
                                $serv_name .= $deps['NAME'].", ";
                                break;
                            }
                        }
                    }
                    $serv_name = substr($serv_name, 0, -2);
                }

                echo '<tr><td style="text-align: center; width: 35px">' . (isset($result['ID']) ? $result['ID'] : "---") . '</td>
                <td style="text-align: center; width: 250px">' . $result['SMNAME'] . '</td>
				<td style="text-align: center; width: 200px">' .  (isset($result['SMDNAME']) ? $result['SMDNAME'] : "-------")  . '</td>
				<td style="text-align: center; width: 230px">' . $serv_name . '</td>
				<td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if (isset($result['ID'])) {
                    if ($result['DELETED']) {
                        echo '<td style="text-align: center; width: 100px"><a href="?restore_id=' . $result['ID'] . '">Восстановить</a></td></tr>';
                    } else {
                        echo '<td style="text-align: center; width: 100px"><a href="?del_id=' . $result['ID'] . '">Удалить</a></td></tr>';
                    }
                }
                else echo '<td style="text-align: center; width: 100px">н/д</td></tr>';
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT smd.ID, sm.Name as smName, smd.Name as smdName, DATE_FORMAT(smd.Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted 
                      FROM SOURCE_MAN_DETAIL as smd, SOURCE_MAN as sm WHERE smd.ID != ".SOURCE_ALL." AND sm.ID = smd.SOURCE_MAN_ID
                      ORDER BY smd.SOURCE_MAN_ID, smd.Name";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (TRUE == ENCODE_UTF) {
                    $result['smdName'] = iconv ('utf-8', 'windows-1251', $result['smdName']);
                    $result['smName'] = iconv ('utf-8', 'windows-1251', $result['smName']);
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                /*if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];*/

                echo '<tr><td style="text-align: center; width: 35px">' . $result['ID'] . '</td>
                <td style="text-align: center; width: 250px">' . $result['smName'] . '</td>
                <td style="text-align: center; width: 200px">' . $result['smdName'] . '</td>
                <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ( $result['Deleted'] ) {
                    echo '<td style="text-align: center; width: 100px"><a href="?restore_id=' . $result['ID'] . '">Восстановить</a></td></tr>';
                }
                else {
                    echo '<td style="text-align: center; width: 100px"><a href="?del_id=' . $result['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
    }
    ?>
        </tbody>
    </table>

<?php
// Обработка действий
$query_result = TRUE;
if (isset($_POST['Reservoir']) && SOURCE_NOT == $_POST['Reservoir'] && isset($_POST['NameIst'])) { //Сначала добавляем новый источник рекламы
    $bCanAdd = 0;
    if (GetData::GetIstochnik(FALSE, FALSE,NULL, FALSE) > 0) {
        foreach ($_POST['array_istochnik'] as $key => $value) {
            if (DB_OCI)
                $bCanAdd = strcasecmp($_POST['NameIst'], $value['NAME']);
            else $bCanAdd = strcmp(strtolower($_POST['NameIst']), strtolower($value['1']));
            if ($bCanAdd == 0)
                break;
        }
    }
    if ($bCanAdd == 0) {
        echo "<p style='color: red'>Источник рекламы '".$_POST['NameIst']."' уже существует.<br /></p>";
    }
    else {
        //Вставляем данные
        if (TRUE == ENCODE_UTF)
            $_POST['NameIst'] = iconv ('utf-8','windows-1251', $_POST['NameIst']);
        $serv_list = implode(',', $_POST['Services']);
        $insertstr = "INSERT INTO SOURCE_MAN (ID, NAME, DETAIL, PRIORITY, IN_DEP) 
VALUES ( seq_source_man_id.NEXTVAL, '{$_POST['NameIst']}', 'Детализация', 3, '{$serv_list}' )";
        if (DB_OCI) {
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result)
            echo "<p style='color: green'>Новый источник рекламы успешно добавлен в таблицу.</p>";
        else echo "<p style='color: red'>Произошла ошибка добавления источника рекламы!</p>";
    }
}

if ($query_result && isset($_POST['Name'])) { // Записываем детализацию
    $count = 0;
    $checkstr = "SELECT ID FROM SOURCE_MAN_DETAIL WHERE NAME LIKE '{$_POST['Name']}' AND SOURCE_MAN_ID = {$_POST['Reservoir']}";
    if (DB_OCI) {
		$objParse = OCIParse(GetData::GetConnect(), $checkstr);
		OCIExecute($objParse);
		$objResult = OCI_Fetch_Row($objParse);
		$count = ($objResult == TRUE ? 1 : 0);
	}
    else {
        $checkstr .= " limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>Детализация '".$_POST['Name']."' уже существует.<br /></p>";
    }
    else {
        //$max_id++;
        if (TRUE == ENCODE_UTF)
            $_POST['Name'] = iconv ('utf-8','windows-1251', $_POST['Name']);
        //Вставляем данные
        if (SOURCE_NOT == $_POST['Reservoir']) // добавился новый источник рекламы
            $insertstr = "INSERT INTO SOURCE_MAN_DETAIL (ID, SOURCE_MAN_ID, NAME) VALUES ( seq_source_detail_id.NEXTVAL, seq_source_man_id.CURRVAL, '{$_POST['Name']}' )";
        else $insertstr = "INSERT INTO SOURCE_MAN_DETAIL (ID, SOURCE_MAN_ID, NAME) VALUES ( seq_source_detail_id.NEXTVAL, {$_POST['Reservoir']}, '{$_POST['Name']}' )";
        if (DB_OCI) {
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
				<p style='color: green'>Новая детализация успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='color: red'>Произошла ошибка добавления детализации!</p>";
        }
    }
}

//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE SOURCE_MAN_DETAIL SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE SOURCE_MAN_DETAIL SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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

//Восстанавливаем удаленное
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE SOURCE_MAN_DETAIL SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
        <h3 style="margin-bottom: 0;">Источник:&nbsp;
        <?php
        if (GetData::GetIstochnik(FALSE, FALSE,NULL, FALSE) > 0) {
            echo "<select id='Reservoir' name='Reservoir'>";
            echo "<option value='".SOURCE_NOT."'>Новый источник</option>";
            if (DB_OCI) {
                foreach($_POST['array_istochnik'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            echo "</select>";
            echo "<script type = 'text/javascript'> var sel = document.getElementById('Reservoir');
            if (sel) { 
                sel.onchange = function() { 
                if (".SOURCE_NOT." == sel.value) { // новый источник
                    document.getElementById('NameIstT').style.visibility = 'visible';
                    document.getElementById('NameIst').style.visibility = 'visible';
                    document.getElementById('ServicesT').style.visibility = 'visible';
                    document.getElementById('Services').style.visibility = 'visible';
                    document.getElementById('ServicesT').style.position = 'inherit';
                    document.getElementById('Services').style.position = 'inherit';
                }
                else {
                    document.getElementById('NameIstT').style.visibility = 'hidden';
                    document.getElementById('NameIst').style.visibility = 'hidden';
                    document.getElementById('ServicesT').style.visibility = 'hidden';
                    document.getElementById('Services').style.visibility = 'hidden';
                    document.getElementById('ServicesT').style.position = 'absolute';
                    document.getElementById('Services').style.position = 'absolute';
                }}
            }
            </script>";
        }

        echo '<label for="NameIst" id="NameIstT">&nbsp;Новый Источник рекламы:&nbsp;</label>';
        echo '<input type="text" name="NameIst" id="NameIst" style="width: 250px" placeholder="Источник рекламы"><br>';
        if (GetData::GetServices((USER_ADMIN == $_SESSION['user_role'] ? TRUE:FALSE),FALSE,NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE:TRUE)) > 0) {
            echo "<label for='Services' id='ServicesT' style='float: left;'>Услуги:&nbsp;</label>";
            if (USER_ADMIN == $_SESSION['user_role'])
                echo "<select id='Services' name='Services[]' multiple style='height: 123px;'>";
            else echo "<select id='Services' name='Services[]' multiple style='height: 70px;'>";
            if (DB_OCI) {
                foreach($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            echo "</select>";
        } ?>
        </h3>
        <h3 style="margin-top: 0;">
            <label for="Name" id="NameT">Новая Детализация: </label>
            <input type="text" name="Name" style="width: 250px" placeholder="Детализация"><br>
        </h3>
        <div>
            <input type="submit" name="Adding" value="Добавить в базу" class="add_button">
            <input type="hidden" name = "table_name" value="SOURCE_MAN_DETAIL">
        </div>
    </form>
</div>

</body>
</html>