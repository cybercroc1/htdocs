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
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>';
    exit();
}

include("med/conn_string.cfg.php");

// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail = "2392967@mail.ru";  // e-mail админа
$date = date("d.m.Y"); // число.месяц.год
$time = date("H:i"); // часы:минуты:секунды
$backurl = "admin_access_dep_new.php";
//$service_arr = [];
//$service_arr_ids = [];
//---------------------------------------------------------------------- //
?>

<html>
<head>
<link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
<meta http-equiv=Content-Type content="text/html; charset=utf-8"/>
    <?php } else { ?>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251"/>
    <?php } ?>
<title>Права доступа по Департаментам</title>
<meta name="description" content="Права доступа по Департаментам">
</head>

<body style="margin-top: 0;">
<?php
/*if (GetData::GetServices(FALSE,FALSE,NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE:TRUE)) > 0) {
    if (DB_OCI) {
        foreach ($_POST['array_services'] as $key => $value) {
            $service_arr[$value['ID']] = $value['NAME'];
            $service_arr_ids[$value['ID']] = $value['ID'];
        }
    } else {
        foreach($_POST['array_services'] as $key => $value) {
            array_push($service_arr, iconv('utf-8', 'windows-1251', $value[1]));
            array_push($service_arr_ids, $value[0]);
        }
    }
}*/

if (isset($getdet))
{
    $_SESSION['dep_id_det'] = $getdet;
    //$sel = "<form action='' method='post'>";
    $sel = "";
    /*if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
        $sel .= "<table class='scrolling-table_ie'>";
    } else {
        $sel .= "<table class='scrolling-table' style='height: 99%'>";
    }*/
    $sel .= "<table class='white_table'>";
    $theads = array(
        'uda.ID' => array('name' => 'ID', 'width' => '35'),
        'dep.NAME' => array('name' => 'Департамент', 'width' => '161'),
        'sr_a.NAME' => array('name' => 'Источник (авто)', 'width' => '451'),
        'sr_t.ID' => array('name' => 'Тип', 'width' => '71'),
        'sr_man.NAME' => array('name' => 'Источник', 'width' => '181'),
        'serv.NAME' => array('name' => 'Услуга', 'width' => '101'),
        '' => array('name' => 'Действие', 'width' => '95')
    );

    /*if (isset($_GET['key'])) {
        $key=$_GET['key'];
        $sort=$_GET['sort'];
    }
    else {
        $key='dep.NAME';
        $sort='asc';
    }*/
    $sel .= "<thead><tr>";
    foreach ($theads as $k => $thead) {
        /*if ($k === $key) {
            $img = PATH."/images/$sort.png";
            $soort = ($sort == 'desc' ? 'asc' : 'desc');
        } else {
            $img = '';
            $soort = 'asc';
        }
        if ('Действие' != $thead['name']) {
            $get = http_build_query(array('key' => $k, 'sort' => $soort));
            $sel .= "<th style='width:{$thead['width']}px; text-decoration: underline'><img src='$img'><a href='?$get' style='color: black'>{$thead['name']}</a></th>";
        }
        else*/ $sel .= "<th style='width:{$thead['width']}px'>{$thead['name']}</th>";
    }

    //foreach($service_arr as $service_id => $service_name) {$sel .= "<th style='width: 91px;'>".$service_name."</th>";}
    $sel .= "</tr></thead><tbody>";

    if (SOURCE_ALL == $getdet)
        $filt_str = "";
    elseif ('' == $getdet)
        $filt_str = " FALSE and";
    else $filt_str = " dep.ID=".$getdet." and ";

    if (FALSE == DEBUG_MODE)
        $table_name = 'ACCESS_DEP';
    else $table_name = 'ACCESS_DEP_TEST';
    $selectstr = "SELECT acdep.ID as ID, dep.NAME as DEPNAME, sr_a.ID as SRAID, sr_a.NAME as SRANAME, sr_t.ID as SRTID, sr_t.NAME as SRTNAME, 
    sr_man.ID as SRMID, sr_man.NAME as SRMNAME, serv.ID as SRVID, serv.NAME as SRVNAME, sr_a.SOURCE_TYPE as SRATYPE
    FROM ".$table_name." acdep, DEPARTAMENTS dep, SOURCE_AUTO sr_a, SOURCE_TYPE sr_t, SOURCE_MAN sr_man, SERVICES serv
    WHERE ". $filt_str;
    $selectstr .= " acdep.DEPARTAMENT_ID = dep.ID AND acdep.SOURCE_AUTO_ID = sr_a.ID AND acdep.SOURCE_TYPE_ID = sr_t.ID AND acdep.SOURCE_MAN_ID = sr_man.ID AND acdep.SERVICE_ID = serv.ID";
    //$selectstr .= " ORDER BY ".$key." ".$sort.", sr_a.NAME ASC, sr_man.NAME ASC, serv.NAME ASC";
    $selectstr .= " ORDER BY DEPNAME asc, SRANAME ASC, SRMNAME ASC, SRVNAME ASC";

    $query = OCIParse($c, $selectstr);
    if (OCIExecute($query)) {
        //$nRows = OCI_Fetch_All($query, $result_all);
        //var_dump($result_all);
        while ($result = OCI_Fetch_Array($query)) {
            $id = $result['ID'];
            if (TRUE == ENCODE_UTF) {
                $result['DEPNAME'] = iconv('windows-1251', 'utf-8', $result['DEPNAME']);
                $result['SRANAME'] = iconv('windows-1251', 'utf-8', $result['SRANAME']);
                $result['SRTNAME'] = iconv('windows-1251', 'utf-8', $result['SRTNAME']);
                $result['SRMNAME'] = iconv('windows-1251', 'utf-8', $result['SRMNAME']);
                $result['SRVNAME'] = iconv('windows-1251', 'utf-8', $result['SRVNAME']);
            }

            $sel .= "<tr><td style='width: 35px'>" . $result['ID'] . "</td>";
            $sel .= "<td style='width: 160px'>" . $result['DEPNAME'] . "</td>";
            if (SOURCE_ALL == $result['SRAID'])
                $sel .= "<td style='width: 450px;'>Все</td>";
            else $sel .= "<td style='width: 450px; text-align: left'>(" . DEVICES[$result['SRATYPE']] .") ". $result['SRANAME'] . "</td>";
            $sel .= "<td style='width: 70px'>" . ($result['SRTID'] == SOURCE_ALL ? 'Все' : $result['SRTNAME']) . "</td>";
            $sel .= "<td style='width: 180px'>" . ($result['SRMID'] == SOURCE_ALL ? 'Все' : $result['SRMNAME']) . "</td>";
            $sel .= "<td style='width: 100px'>" . ($result['SRVID'] == SERVICE_ALL ? 'Все' : $result['SRVNAME']) . "</td>";
            /*foreach($service_arr as $service_id => $service_name) {
                if (in_array($service_id, $checked_service_arr)) {
                    $colors = "background-color: springgreen";
                    $checked = " checked";
                }
                else {
                    $colors = "background-color: red";
                    $checked = "";
                }
                $sel .= "<td style='text-align: center; width: 90px;".$colors."'><input type=hidden name=source_det_ids[".$id."]><input type='checkbox' name='on_".$id."[]' value='".$service_id."'".$checked."/></td>";
            }*/
            //$sel .= "<td style='width: 80px; text-align: center'><a href=\"?del_id='".$result['ID']."'\">Удалить</a></td>";
            $sel .= "<td style='text-align: center; width: 95px'><a href='?del_id=" . $result['ID'] . "'>Удалить</a></td>";
            $sel .= "</tr>";
        }
    }
    oci_free_statement($query);
    
    $sel .= "</tbody>";
    $sel .= "</table>";

    /*if ($getdet != '' && $getdet != SOURCE_ALL /*&& $nDetailRows > 0/)
        $sel .= "<input type='submit' name='save' style='background-color: plum; height: 30px; font-weight: bold' value='Сохранить изменения'/>";
    $sel .= "</form>";*/
    echo '<script>elem = parent.document.getElementById("AllTable"); if (elem) elem.innerHTML="' . $sel . '";</script>';

    if ($getdet != '' && $getdet != SOURCE_ALL) {
        //echo "<script>elem = parent.document.getElementById('add_table'); if (elem) elem.style.visibility = 'visible';</script>";
        echo "<script>elem = parent.document.getElementById('Adding'); if (elem) elem.style.visibility = 'visible';</script>";
        echo "<script>elem = parent.document.getElementById('Refreshing'); if (elem) elem.style.visibility = 'visible';</script>";
    }
    else {
        //echo "<script>elem = parent.document.getElementById('add_table'); if (elem) elem.style.visibility = 'hidden';</script>";
        echo "<script>elem = parent.document.getElementById('Adding'); if (elem) elem.style.visibility = 'hidden';</script>";
        echo "<script>elem = parent.document.getElementById('Refreshing'); if (elem) elem.style.visibility = 'hidden';</script>";
    }
    exit();
}

// !!! Удаляем, а не меняем признак удаления строки!!!
if (isset($del_id)) {  // удаляем одну строку!
    if (FALSE == DEBUG_MODE)
        $deletestr = "DELETE FROM ACCESS_DEP WHERE ID = '{$del_id}'";
    else $deletestr = "DELETE FROM ACCESS_DEP_TEST WHERE ID = '{$del_id}'";
    if (DB_OCI) {
        //$deletestr = "UPDATE ACCESS_DEP SET DELETED = to_date('" . date("d-m-Y  H:i:s") . "','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$del_id}'";
        $query = OCIParse($c, $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        //$deletestr = "UPDATE ACCESS_DEP SET DELETED = '" . date("Y-m-d H:i:s") . "' WHERE ID = '{$del_id}'";
        $query_result = mysqli_query($c, $deletestr);
    }
    if ($query_result) {
        print "<script language='Javascript'>
                    function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
                    </script>";
        echo "<p style='color: green'>Строка удалена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
    }
}
?>

<form action="" method="post" style="margin-bottom: 0">
    <div class="wrapper" style="width: 100%">
        <h3 style="margin: 0;">Права доступа по Департаментам</h3>
        <div class="content_table_source">
    <iframe name='ifr_all' style='display: none; width: 95%;'></iframe>
    <?php
    echo "<label id='DepartT' for='Depart' style='font-weight: bold; font-size: 14px'>Департамент:&nbsp;</label>";
    echo "<select id='Depart' name='Depart' onchange='ifr_all.location=\"".PATH."/admin/admin_access_dep_new.php?getdet=\"+this.value'>";
    echo "<option value=''>Выберите департамент</option>";
    echo "<option value='".SOURCE_ALL."'>Все департаменты</option>";
    if (GetData::GetDepartments(FALSE,FALSE,NULL) > 0)
    {
        if (DB_OCI) {
            foreach ($_POST['array_dep'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        } else {
            foreach ($_POST['array_dep'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                echo "<option value='".$value[0]."'>".$value[1]."</option>";
            }
        }
    }
    echo "</select>";
    if (isset($_SESSION['dep_id_det']))
        echo "<script>$('#Depart').val('" . $_SESSION['dep_id_det'] . "').change();</script>";
    else echo "<script>$('#Depart').val('" . SOURCE_ALL . "').change();</script>";
//    echo '<a href="'.PATH.'/admin/admin_access_dep_new.php"><button name="UndoAll" class="enter_button" style="width: 190px; height: 2.1em;">Отменить все изменения</button></a>';
    ?>
    <hr>
    <div id="AllTable" style="margin-left: 5px"></div>
    <hr>
        </div>

        <div class="footer">
    <?php
    //echo '<table class="white_table"><tr><th style="width: 300px;">Выберите источник</th>';
    /*echo '<table id="add_table" class="white_table" style="visibility: hidden"><tr><th style="width: 300px;">Новый источник</th>';
    foreach($service_arr as $service_id => $service_name) {
        echo '<th style="width: 101px;">'.$service_name.'</th>';
    }

    echo '<th style="border-bottom: none"></th></tr>';
    echo '<tr style="vertical-align: middle">';
    echo '<td style="text-align: center; width: 300px">';
    echo '<input type="text" name="NameIst" id="NameIst" style="width: 250px" placeholder="Источник рекламы">';
    echo '</td>';
    foreach($service_arr as $service_id => $service_name) {
        echo '<td style="text-align: center;"><input type="checkbox" name="newservice_'.$service_id.'"/></td>';
    }
    echo '<td style="border-top: none;"><input type="submit" name="Adding" id="Adding" value="Добавить источник" class="add_button"></td>';
    echo '</tr></table>';*/

    echo "<div style='float: left; width: 51%;'>";
    echo "<label id='S_AutoT' for='S_Auto' style='float: left'>Источник (авто):&nbsp;</label>";
    if (GetData::GetSourceAuto("DELETED IS NULL", NULL, FALSE) > 0) {
        echo "<select id='S_Auto' name='S_Auto[]' multiple style='height: 350px'>";
        echo "<option value='".SOURCE_ALL."'>Все источники</option>";
        if (DB_OCI) {
            foreach($_POST['array_source_auto'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>(".DEVICES[$value['SOURCE_TYPE']].") ".$value['NAME']."</option>";
            }
        }
        else {
            foreach ($_POST['array_source_auto'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[2] = iconv ('utf-8', 'windows-1251', $value[2]);
                echo "<option value='".$value[0]."'>(".DEVICES[$value[3]].") ".$value[2]."</option>";
            }
        }
        echo "</select>";
    }
    echo "<script>$('#S_Auto').val('" . SOURCE_ALL . "').change();</script>";
    echo "</div>";

    echo "<div style='float: right; width: 40%'>";

    echo "<label id='ServNameT' for='ServName' style='float: left'>Услуга:&nbsp;</label>";
    echo "<select id='ServName' name='ServName[]' multiple style='height: 125px'>";
    if (GetData::GetServices(TRUE,FALSE, NULL,FALSE) > 0) {
        if (DB_OCI) {
            foreach($_POST['array_services'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        else {
            foreach ($_POST['array_services'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                echo "<option value='".$value[0]."'>".$value[1]."</option>";
            }
        }
    }
    echo "</select>";
    echo "<script>$('#ServName').val('" . SERVICE_ALL . "').change();</script>";

    echo "<br/><label id='S_TypeT' for='S_Type'>Тип Источника:&nbsp;</label>";
    echo "<select id='S_Type' name='S_Type'>";
    if (GetData::GetSourceType(TRUE, FALSE) > 0) {
        if (DB_OCI) {
            foreach($_POST['array_stype'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        else {
            foreach ($_POST['array_stype'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                echo "<option value='".$value[0]."'>".$value[1]."</option>";
            }
        }
    }
    echo "</select>";
    echo "<script>$('#S_Type').val('-1').change();</script>";

    echo "<br/><label id='S_ManT' for='S_Man' style='float: left'>Источник:&nbsp;</label>";
    echo "<select id='S_Man' name='S_Man[]' multiple style='height: 250px'>";
    //if (GetData::GetIstochnik(TRUE,FALSE,"instr(in_dep, '-1') != 0", FALSE) > 0) {
    if (GetData::GetIstochnik(TRUE,FALSE,NULL, FALSE) > 0) {
        if (DB_OCI) {
            foreach($_POST['array_istochnik'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        else {
            foreach ($_POST['array_istochnik'] as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                echo "<option value='".$value[0]."'>".$value[1]."</option>";
            }
        }
    }
    echo "</select>";
    echo "<script>$('#S_Man').val('" . SOURCE_ALL . "').change();</script>";

    echo "</div>";

    ?>
        </div>
        <br/>
        <input type='submit' value='Добавить новые сочетания' name='Adding' id='Adding' class="add_button">
        <input type='submit' value='Удалить все и добавить новые' name='Refreshing' id='Refreshing' class="add_button" style="float: right; background-color: rebeccapurple;">
    </div>
</form>

<?php
function OnClick()
{
    if (isset($S_Auto) && isset($S_Man) && isset($S_Type) && isset($ServName)) {
        $query_delete = TRUE;
        $query_result = FALSE;
        if (FALSE == DEBUG_MODE)
            $table_name = 'ACCESS_DEP';
        else $table_name = 'ACCESS_DEP_TEST';
        if (isset($_POST['Refreshing'])) { // удаляем все для выбранного департамента
            $deletestr = "DELETE FROM ".$table_name." WHERE departament_id = " . $_POST['Depart'];
            $query = OCIParse(GetData::GetConnect(), $deletestr);
            $query_delete = OCIExecute($query);
            oci_free_statement($query);
        }

        if ($query_delete) {
            $count_add = $count_exist = 0;
            foreach ($S_Auto as $key_sAuto => $sAuto) {
            foreach ($S_Man as $key_sMan => $sMan) {
            foreach ($ServName as $key_serv => $sServ) {
                if (isset($Adding)) { // проверяем наличие аналогичной записи
                    $checkstr = "SELECT count(*) FROM ".$table_name." WHERE 
                    source_auto_id = ".$sAuto." AND source_type_id = ".$S_Type." AND source_man_id = ".$sMan." AND service_id = ".$sServ;
                    /*if ($sServ != -1)
                        $checkstr .= " or source_auto_id = {$sAuto} AND source_type_id = {$_POST['S_Type']} AND source_man_id = {$sMan} AND service_id = -1";
                    if ($sMan != -1)
                        $checkstr .= " or source_auto_id = {$sAuto} AND source_type_id = {$_POST['S_Type']} AND source_man_id = -1 AND service_id = {$sServ}";
                    if ($_POST['S_Type'] != -1)
                        $checkstr .= " or source_auto_id = {$sAuto} AND source_type_id = -1 AND source_man_id = {$sMan} AND service_id = {$sServ}";
                    if ($sAuto != -1)
                        $checkstr .= " or source_auto_id = -1 AND source_type_id = {$_POST['S_Type']} AND source_man_id = {$sMan} AND service_id = {$sServ}";*/
//echo $checkstr;
                    if (DB_OCI) {
                        $objParse = OCIParse(GetData::GetConnect(), $checkstr);
                        OCIExecute($objParse);
                        $result = OCI_Fetch_Row($objParse);
                    } else {
                        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
                        if (FALSE !== $query_result) {
                            $result = mysqli_fetch_row($query_result);
                        } else {
                            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
                        }
                    }
                    if (isset($result) && $result[0] > 0) $count_exist++;
                }

                if (!isset($_POST['Adding']) || $result[0] == 0) { // Вставляем данные
                    if (FALSE == DEBUG_MODE)
                        $insertstr = "INSERT INTO ACCESS_DEP (id, departament_id, source_auto_id, source_man_id, source_type_id, service_id) 
VALUES (SEQ_ACCESS_DEP_ID.nextval, {$_POST['Depart']}, {$sAuto}, {$sMan}, {$_POST['S_Type']}, {$sServ})";
                    else $insertstr = "INSERT INTO ACCESS_DEP_TEST (id, departament_id, source_auto_id, source_man_id, source_type_id, service_id) 
VALUES (SEQ_ACCESS_DEP_ID_TEST.nextval, {$_POST['Depart']}, {$sAuto}, {$sMan}, {$_POST['S_Type']}, {$sServ})";
                    if (DB_OCI) {
                        $query = OCIParse(GetData::GetConnect(), $insertstr);
                        $query_result = OCIExecute($query);
                        oci_free_statement($query);
                    } else {
                        $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
                    }
                    if ($query_result) $count_add++;
                }
            }
            }
            }

            if ($count_exist > 0) {
                echo "<p style='color: blue'>".$count_exist." строк уже существует.</p>";
            }
            if ($count_add > 0) {
                /*print "<script language='Javascript'>
                        function reload() {location = \"admin_access_dep_new.php\" }; setTimeout('reload()', 3000);
                        </script>";*/
                echo "<p style='color: green'>".$count_add." строк успешно добавлено в таблицу.</p>";
            } elseif ($count_exist == 0) {
                echo "<p style='color: red'>Произошла ошибка добавления записей!</p>";
            }
        }
    }
}
if (isset($_POST['Adding']) || isset($_POST['Refreshing']))
    OnClick();
?>
</body>
</html>