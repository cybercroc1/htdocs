<?php
ini_set('session.use_cookies', '1');

session_name('medc');
session_start();
extract($_REQUEST);

require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth'] <> md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])) {
    echo "<b style='color: red'>Доступ запрещен</b>";
    exit();
}
if (!isset($_SESSION['user_role']) || USER_ADMIN != $_SESSION['user_role']) {
    if (USER_SUPER != $_SESSION['user_role'] /*|| (84 != $_SESSION['login_id_med'] && 11 != $_SESSION['login_id_med'])*/)
    { // только для Грачевой и Алибековой?
        echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>';
        exit();
    }
}
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail = "2392967@mail.ru";  // e-mail админа
$date = date("d.m.Y"); // число.месяц.год
$time = date("H:i"); // часы:минуты:секунды
$backurl = "admin_source_detail_new.php";
$service_arr = [];
//---------------------------------------------------------------------- //
?>

<html>
<?php
echo '<head>';
echo '<link rel="stylesheet" type="text/css" href="../billing.css">';
if (TRUE == ENCODE_UTF)
    echo '<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>';
else echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251"/>';
echo '<title>Детализация Источников рекламы</title>';
echo '<meta name="description" content="Детализация Источников рекламы">';
echo '</head>';

if (GetData::GetServices(FALSE,FALSE,NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE:TRUE)) > 0) {
    if (DB_OCI) {
        foreach ($_POST['array_services'] as $key => $value) {
            $service_arr[$value['ID']] = $value['NAME'];
            $service_arr_ids[$value['ID']] = $value['ID'];
        }
    } else {
        foreach($_POST['array_services'] as $key => $value) {
            array_push($service_arr, $value[0]);
        }
    }
}

if (isset($save) and $S_Auto > 0 && isset($source_det_ids)) {
    foreach($source_det_ids as $source_det_id => $fucking_val) {
        //если есть хотя бы
        if (isset($GLOBALS["on_".$source_det_id])) {
            //меняем услуги для источника
            $service_ids = implode(",",$GLOBALS["on_".$source_det_id]);
            //echo $S_Auto." - ".$source_det_id." - ". $service_ids."<br>";
        }
        else {
            $service_ids='';
        }

        // смотрим, что было в базе
        $checkstr = "SELECT SERVICE_IDS FROM SOURCE_AUTO_ALLOC WHERE ID = ".$source_det_id." and SOURCE_AUTO_ID=".$S_Auto;
        $query = OCIParse(GetData::GetConnect(), $checkstr);
        OCIExecute($query, OCI_DEFAULT);
        $Row_IDS = OCI_Fetch_Row($query);
        $Row_IDS_arr = explode(',', $Row_IDS[0]);
        oci_free_statement($query);

        foreach($Row_IDS_arr as $value) { // добавляем если что-то из сервисов недоступно этому поользователю
            if (!in_array($value[0],$service_arr_ids) /*&& !in_array($value[0], $GLOBALS["on_".$source_det_id])*/) {
                if (strlen($service_ids) > 0)
                    $service_ids .= ','.$value[0];
                else $service_ids .= $value[0];
            }
        }

        $updatestr = "UPDATE SOURCE_AUTO_ALLOC SET SERVICE_IDS = '".$service_ids."' WHERE ID = ".$source_det_id." and SOURCE_AUTO_ID=".$S_Auto;
        if (DB_OCI) {
            $query = OCIParse(GetData::GetConnect(), $updatestr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        } else {
            $query_result = mysqli_query(GetData::GetConnect(), $updatestr);
        }
    }
    //echo "<script>$('#S_Auto').val('" . $S_Auto . "').change();</script>";
}

if (isset($getdet))
{
    $_SESSION['sa_id_det'] = $getdet;
    $sel = "<form action='' method='post' >";
    if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
        $sel .= "<table class='scrolling-table_ie'>";
    } else {
        $sel .= "<table class='scrolling-table' style='height: 100%; direction: rtl;'>";
    }
    $sel .= "<thead><tr style='direction: initial;'>";
    $sel .= "<th style='width: 36px;'>ID</th>";
    if (SOURCE_ALL == $getdet)
        $sel .= "<th style='width: 250px;'>Источник&nbsp;(&nbsp;Авто&nbsp;)</th>";
    $sel .= "<th style='width: 260px;'>Источник</th>";
    foreach($service_arr as $service_id => $service_name) {$sel .= "<th style='width: 91px;'>".$service_name."</th>";}
    $sel .= "<th style='width: 121px;'>Удалена</th>";
    $sel .= "<th style='width: 101px;'>Действие</th>";
    $sel .= "</tr></thead>";
    $sel .= "<tbody>";
    /*if (SOURCE_ALL == $getdet)
        $filt_str = NULL;
    else $filt_str = "saa.SOURCE_AUTO_ID=".$getdet;
    $nDetailRows = GetData::GetSourceAutoDetail(TRUE, TRUE, $filt_str, (USER_ADMIN == $_SESSION['user_role'] ? FALSE:TRUE));*/
    $sqlstr = "SELECT saa.ID, saa.SOURCE_AUTO_ID, sa.NAME SANAME, sa.SOURCE_TYPE, sad.NAME SADNAME, saa.SERVICE_IDS, to_char(saa.Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED 
 FROM SOURCE_AUTO_ALLOC saa
 LEFT JOIN SOURCE_AUTO sa ON sa.ID = saa.SOURCE_AUTO_ID
 LEFT JOIN SOURCE_AUTO_DETAIL sad ON sad.ID = saa.SA_DETAIL_ID";
    if (SOURCE_ALL != $getdet)
        $sqlstr .= " WHERE saa.SOURCE_AUTO_ID=".$getdet;
    $query = OCIParse(GetData::GetConnect(), $sqlstr);
    OCIExecute($query, OCI_DEFAULT);
    $nDetailRows = OCI_Fetch_All($query, $_POST['array_sa_detail'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
    oci_free_statement($query);

    if ($nDetailRows > 0) {
        foreach ($_POST['array_sa_detail'] as $key => $value) {
            $id = $value['ID'];
            $name = $value['SADNAME'];
            $checked_service_ids = $value['SERVICE_IDS'];
            $deleted = $value['DELETED'];
            $str_deleted = ($deleted ? $deleted : "нет");
            if (TRUE == ENCODE_UTF)
                $name = iconv('windows-1251', 'utf-8', $name);

            $checked_service_arr = array();
            $checked_service_arr = explode(',', $checked_service_ids);
            foreach($checked_service_arr as $checked_id) {$checked_service_arr[$checked_id] = $checked_id;}

            $sel .= "<tr style='direction: initial;'>";
            $sel .= "<td style='text-align: center; width: 35px'>" . $id . "</td>";
            if (SOURCE_ALL == $getdet)
                $sel .= "<td style='width: 250px;'>(".DEVICES[$value['SOURCE_TYPE']].") ".$value['SANAME']."</td>";
            $sel .= "<td style='text-align: center; width: 260px'>" . $name . "</td>";
            foreach($service_arr as $service_id => $service_name) {
                if (in_array($service_id, $checked_service_arr)) {
                    $colors = "background-color: springgreen";
                    $checked = " checked";
                }
                else {
                    $colors = "background-color: red";
                    $checked = "";
                }
                $sel .= "<td style='text-align: center; width: 90px;".$colors."'><input type=hidden name=source_det_ids[".$id."]><input type='checkbox' name='on_".$id."[]' value='".$service_id."'".$checked."/></td>";
            }
            $sel .= "<td style='text-align: center; width: 120px'>" . $str_deleted . "</td>";
            if ($value['DELETED'])
                $sel .= "<td style='text-align: center; width: 100px'><a href='?restore_id=" . $value['ID'] . "'>Восстановить</a></td>";
            else $sel .= "<td style='text-align: center; width: 100px'><a href='?del_id=" . $value['ID'] . "'>Удалить</a></td>";
            $sel .= "</tr>";
        }
    }
    $sel .= "</tbody>";
    $sel .= "</table>";
    if ($getdet != '' && $getdet != SOURCE_ALL && $nDetailRows > 0)
        $sel .= "<input type='submit' name='save' style='background-color: plum; height: 30px; font-weight: bold' value='Сохранить изменения'/>";
    $sel .= "</form>";

    echo '<script>elem = parent.document.getElementById("AllTable"); if (elem) elem.innerHTML="' . $sel . '";</script>';

    if ($getdet != '' && $getdet != SOURCE_ALL)
        echo "<script>parent.document.getElementById('add_table').style.visibility = 'visible';</script>";
    else echo "<script>parent.document.getElementById('add_table').style.visibility = 'hidden';</script>";
    exit();
}
?>

<body style="margin-top: 0;">
<form action="" method="post" style="margin-bottom: 0">
    <div class="wrapper">
        <h3 style="margin: 0;">Детализация Источников рекламы</h3>
        <div class="content_table_source">
    <iframe name='ifr_all' style='display: none; width: 75%;'></iframe>
    <?php
    echo "<label id='S_AutoT' for='S_Auto'>Источник (авто):&nbsp;</label>";
    echo "<select id='S_Auto' name='S_Auto' onchange='ifr_all.location=\"".PATH."/admin/admin_source_detail_new.php?getdet=\"+this.value'>";
    echo "<option value=''>Выберите автоматический источник рекламы</option>";
    echo "<option value='".SOURCE_ALL."'>Все источники</option>";
    if (GetData::GetSourceAuto("DELETED IS NULL", NULL, FALSE) > 0) {
        if (DB_OCI) {
            foreach ($_POST['array_source_auto'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                if (DEVICE_PHONE == $value['SOURCE_TYPE'])
                    echo "<option value='".$value['ID']."'>(".$value['BNUMBER'].") ".$value['NAME']."</option>";
                else echo "<option value='".$value['ID']."'>(".DEVICES[$value['SOURCE_TYPE']].") ".$value['NAME']."</option>";
            }
        } else {
            foreach ($_POST['array_source_auto'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[2] = iconv('utf-8', 'windows-1251', $value[2]);
                echo "<option value='".$value[0]."'>".$value[2]."</option>";
            }
        }
    }
    echo "</select>";
    if (isset($_SESSION['sa_id_det']))
        echo "<script>$('#S_Auto').val('" . $_SESSION['sa_id_det'] . "').change();</script>";
    else echo "<script>$('#S_Auto').val('" . SOURCE_ALL . "').change();</script>";
//    echo '<a href="'.PATH.'/admin/admin_source_detail_new.php"><button name="UndoAll" class="enter_button" style="width: 190px; height: 2.1em;">Отменить все изменения</button></a>';
    ?>
    <hr>
    <div id="AllTable" style="height: 86%"></div>
    <hr>
        </div>

        <div class="footer">
    <?php
    //echo '<table class="white_table"><tr><th style="width: 300px;">Выберите источник</th>';
    echo '<table id="add_table" class="white_table" style="visibility: hidden"><tr><th style="width: 300px;">Детализация источника</th>';
    foreach($service_arr as $service_id => $service_name) {
        echo '<th style="width: 101px;">'.$service_name.'</th>';
    }

    echo '<th style="border-bottom: none"></th></tr>';
    echo '<tr style="vertical-align: middle">';
    echo '<td style="text-align: center; width: 300px">';
    echo "<select id='Reservoir' name='Reservoir' style='max-width: 500px'>";
    echo "<option value='" . SOURCE_NOT . "'>Новая детализация источника</option>";
    if (GetData::GetSourceAutoDetail(FALSE, FALSE, NULL, FALSE) > 0)
    {
        if (DB_OCI) {
            foreach ($_POST['array_sa_detail'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        } else {
            foreach ($_POST['array_sa_detail'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                echo "<option value='".$value[0]."'>".$value[1]."</option>";
            }
        }
    }
    echo "</select><br>";
    //echo "<label for='NameIst' id='NameIstT' style='font-weight: bold'>или введите новый:&nbsp;</label>";
    echo '<input type="text" name="NameIst" id="NameIst" style="width:490px;max-width: 500px" placeholder="Детализация источника">';
    echo '</td>';
    foreach($service_arr as $service_id => $service_name) {
        echo '<td style="text-align: center;"><input type="checkbox" name="newservice_'.$service_id.'"/></td>';
    }
    echo '<td style="border-top: none;"><input type="submit" name="Adding" id="Adding" value="Добавить источник" class="add_button"></td>';
    echo '</tr></table>';
    echo "<script type = 'text/javascript'> var sel = document.getElementById('Reservoir');
    if (sel) {
        sel.onchange = function() {
            if (" . SOURCE_NOT . " == sel.value) { // новый источник
                //document.getElementById('NameIstT').style.visibility = 'visible';
                //document.getElementById('NameIstT').style.position = 'inherit';
                document.getElementById('NameIst').style.visibility = 'visible';
                document.getElementById('NameIst').style.position = 'inherit';
            }
            else {
                //document.getElementById('NameIstT').style.visibility = 'hidden';
                //document.getElementById('NameIstT').style.position = 'absolute';
                document.getElementById('NameIst').style.visibility = 'hidden';
                document.getElementById('NameIst').style.position = 'absolute';
            }}
    }
    </script>";
    ?>



<?php
//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($del_id)) {
    if (DB_OCI) {
        $deletestr = "UPDATE SOURCE_AUTO_ALLOC SET DELETED = to_date('" . date("d-m-Y  H:i:s") . "','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$del_id}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        $deletestr = "UPDATE SOURCE_AUTO_ALLOC SET DELETED = '" . date("Y-m-d H:i:s") . "' WHERE ID = '{$del_id}'";
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        /*print "<script language='Javascript'>
                function reload() {parent.location = \"$backurl\"}; setTimeout('reload()', 100);
				</script>";
        echo "<p style='color: green'>Строка изменена. Идет перезагрузка данных...</p>";*/
    } else {
        echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
    }
}

//Восстанавливаем удаленное
if (isset($restore_id)) {
    $deletestr = "UPDATE SOURCE_AUTO_ALLOC SET DELETED = NULL WHERE ID = '{$restore_id}'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        /*print "<script language='Javascript'>
            function reload() {parent.location = \"$backurl\"}; setTimeout('reload()', 100);
                </script>";
        echo "<p style='color: green'>Строка восстановлена. Идет перезагрузка данных...</p>";*/
    } else {
        echo "<p style='color: red'>Произошла ошибка восстановления записи.</p>";
    }
}


if (isset($_POST['Adding']))
{
    $query_result = TRUE;
    $service_list = '';
    foreach($service_arr as $service_id => $service_name) {
        if (isset($_POST['newservice_'.$service_id.'']) && $_POST['newservice_'.$service_id.'']) $service_list .= $service_id . ',';
    }
    if (strlen($service_list) > 0)
        $service_list = substr($service_list, 0, -1);
    if (isset($_POST['Reservoir']) && SOURCE_NOT == $_POST['Reservoir'] && isset($_POST['NameIst'])) { //Сначала добавляем новую детализацию
        $bCanAdd = 0;
        //if (GetData::GetIstochnik(FALSE, FALSE, NULL, FALSE) > 0)
        if (GetData::GetSourceAutoDetail(FALSE, FALSE, NULL, FALSE) > 0)
        {
            foreach ($_POST['array_sa_detail'] as $key => $value) {
                if (DB_OCI)
                    $bCanAdd = strcasecmp($_POST['NameIst'], $value['NAME']);
                else $bCanAdd = strcmp(strtolower($_POST['NameIst']), strtolower($value['1']));
                if ($bCanAdd == 0)
                    break;
            }
        }
        if ($bCanAdd == 0) {
            echo "<p style='color: red'>Детализация '" . $_POST['NameIst'] . "' уже существует.<br /></p>";
        } else {
            //Вставляем данные
            if (TRUE == ENCODE_UTF)
                $_POST['NameIst'] = iconv('utf-8', 'windows-1251', $_POST['NameIst']);

            $insertstr = "INSERT INTO SOURCE_AUTO_DETAIL (ID, SOURCE_AUTO_ID, NAME, SERVICE_IDS)
VALUES (SEQ_SOURCE_AD_ID.NEXTVAL, '{$_POST['S_Auto']}', '{$_POST['NameIst']}', '{$service_list}')";
            if (DB_OCI) {
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
                oci_free_statement($query);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }
            $insertstr = "INSERT INTO SOURCE_AUTO_DETAIL (ID, NAME) VALUES (SEQ_SOURCE_AD_TEST_ID.NEXTVAL, '{$_POST['NameIst']}')";
            if (DB_OCI) {
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
                oci_free_statement($query);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }
            if ($query_result)
                echo "<p style='color: green'>Новая детализация успешно добавлена в таблицу.</p>";
            else echo "<p style='color: red'>Произошла ошибка добавления детализации!</p>";
        }
    }

    if ($query_result) { // Записываем детализацию
        $count = 0;
        //$checkstr = "SELECT ID FROM SOURCE_AUTO_DETAIL WHERE SOURCE_AUTO_ID = {$_POST['S_Auto']} AND SOURCE_MAN_ID = {$_POST['Reservoir']}";
        $checkstr = "SELECT ID FROM SOURCE_AUTO_ALLOC WHERE SOURCE_AUTO_ID = {$_POST['S_Auto']} AND SA_DETAIL_ID = '{$_POST['Reservoir']}'";
        if (DB_OCI) {
            $objParse = OCIParse(GetData::GetConnect(), $checkstr);
            OCIExecute($objParse);
            $objResult = OCI_Fetch_Row($objParse);
            $count = ($objResult == TRUE ? 1 : 0);
        } else {
            $checkstr .= " limit 1";
            $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
            if (FALSE !== $query_result)
                $count = mysqli_num_rows($query_result);
            else {
                $count = 1;
                printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
            }
        }

        if ($count >= 1) {
            echo "<p style='color: red'>Детализация '".$_POST['NameIst']."' уже существует.<br /></p>";
        } else { //Вставляем данные
            if (SOURCE_NOT == $_POST['Reservoir']) // добавился новый источник рекламы
                $insertstr = "INSERT INTO SOURCE_AUTO_ALLOC (ID, SOURCE_AUTO_ID, SA_DETAIL_ID, SERVICE_IDS) VALUES ( SEQ_SOURCE_ALLOC_ID.NEXTVAL, '{$_POST['S_Auto']}', SEQ_SOURCE_AD_ID.CURRVAL, '{$service_list}' )";
            else $insertstr = "INSERT INTO SOURCE_AUTO_ALLOC (ID, SOURCE_AUTO_ID, SA_DETAIL_ID, SERVICE_IDS) VALUES ( SEQ_SOURCE_ALLOC_ID.NEXTVAL, {$_POST['S_Auto']}, '{$_POST['Reservoir']}', '{$service_list}' )";
            if (DB_OCI) {
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
                oci_free_statement($query);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }
            if ($query_result) {
                print "<script language='Javascript'>
                    function reload() { parent.location = \"$backurl\"}; setTimeout('reload()', 3000);
                            </script>";
                echo "<p style='color: green'>Новое сочетание успешно добавлено в таблицу. Идет перезагрузка данных...</p>";
            } else {
                echo "<p style='color: red'>Произошла ошибка добавления данных!</p>";
            }
        }
    }
    exit();
}
?>
        </div>
    </div>
</form>
</body>
</html>