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

include("med/conn_string.cfg.php");
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "admin_access_dep.php";
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
    <title>Права доступа</title>
    <meta name="description" content="Права доступа">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Права доступа по Департаментам</h3>
<div class="container">
    <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
        echo '<table class="scrolling-table_uie">';
    } else {
        echo '<table class="scrolling-table_u">';
    }
    $theads = array(
        'uda.ID' => array('name' => 'ID', 'width' => '35'),
        'dep.NAME' => array('name' => 'Департамент', 'width' => '181'),
        'sr_a.NAME' => array('name' => 'Источник (авто)', 'width' => '301'),
        'sr_t.ID' => array('name' => 'Тип', 'width' => '81'),
        'sr_man.NAME' => array('name' => 'Источник', 'width' => '201'),
        'serv.NAME' => array('name' => 'Услуга', 'width' => '101')
        //'uda.DELETED' => array('name' => 'Удалена', 'width' => '121'),
        //'' => array('name' => 'Действие', 'width' => '86')
    );

    if (isset($_GET['key'])) {
        $key=$_GET['key'];
        $sort=$_GET['sort'];
    }
    else {
        $key='dep.NAME';
        $sort='asc';
    }
    echo '<thead><tr>';
    foreach ($theads as $k => $thead) {
        if ($k === $key) {
            $img = PATH."/images/$sort.png";
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
    echo '</tr></thead><tbody>';

    //$max_id = 0; // для вставки новой строки, если потребуется
    $selectstr = "SELECT acdep.ID as ID, dep.NAME as DEPNAME, sr_a.ID as SRAID, sr_a.NAME as SRANAME, sr_t.ID as SRTID, sr_t.NAME as SRTNAME, 
    sr_man.ID as SRMID, sr_man.NAME as SRMNAME, serv.ID as SRVID, serv.NAME as SRVNAME
    FROM ACCESS_DEP acdep, DEPARTAMENTS dep, SOURCE_AUTO sr_a, SOURCE_TYPE sr_t, SOURCE_MAN sr_man, SERVICES serv
    WHERE acdep.DEPARTAMENT_ID = dep.ID AND acdep.SOURCE_AUTO_ID = sr_a.ID AND acdep.SOURCE_TYPE_ID = sr_t.ID AND acdep.SOURCE_MAN_ID = sr_man.ID AND acdep.SERVICE_ID = serv.ID";
    $selectstr .= " ORDER BY ".$key." ".$sort.", sr_a.NAME ASC, sr_man.NAME ASC, serv.NAME ASC";
//    "ORDER BY dep.NAME, sr_a.NAME, sr_man.NAME, serv.NAME";
    if (DB_OCI) {
        $query = OCIParse($c, $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $result['DEPNAME'] = iconv('windows-1251', 'utf-8', $result['DEPNAME']);
                    $result['SRANAME'] = iconv('windows-1251', 'utf-8', $result['SRANAME']);
                    $result['SRTNAME'] = iconv('windows-1251', 'utf-8', $result['SRTNAME']);
                    $result['SRMNAME'] = iconv('windows-1251', 'utf-8', $result['SRMNAME']);
                    $result['SRVNAME'] = iconv('windows-1251', 'utf-8', $result['SRVNAME']);
                }
                //$str_deleted = ($result['DELETED'] ? $result['DELETED'] : "нет");
                //if ($result['ID'] > $max_id)
                //	$max_id = $result['ID'];

                $res_id = $result['ID'];
                echo '<tr>
                    <td style="width: 35px">' . $result['ID'] . '</td>
                    <td style="width: 180px">' . $result['DEPNAME'] . '</td>
                    <td style="width: 300px">' . ($result['SRAID'] == SOURCE_ALL ? "Все" : $result['SRANAME']) . '</td>
                    <td style="width: 80px">' . ($result['SRTID'] == SOURCE_ALL ? "Все" : $result['SRTNAME']) . '</td>
                    <td style="width: 200px">' . ($result['SRMID'] == SOURCE_ALL ? "Все" : $result['SRMNAME']) . '</td>
                    <td style="width: 100px">' . ($result['SRVID'] == SERVICE_ALL ? "Все" : $result['SRVNAME']) . '</td>
                    <!--td style="width: 80px; text-align: center"><a href="?del_id='.$res_id.'">Удалить</a></td-->
				</tr>';
            }
        }
        oci_free_statement($query);
    }
    else {
        $sql = mysqli_query($c, $selectstr);
        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (TRUE == ENCODE_UTF) {
                    $result['DEPNAME'] = iconv ('utf-8', 'windows-1251', $result['DEPNAME']);
                    $result['SRANAME'] = iconv ('utf-8', 'windows-1251', $result['SRANAME']);
                    $result['SRTNAME'] = iconv ('utf-8', 'windows-1251', $result['SRTNAME']);
                    $result['SRMNAME'] = iconv ('utf-8', 'windows-1251', $result['SRMNAME']);
                    $result['SRVNAME'] = iconv ('utf-8', 'windows-1251', $result['SRVNAME']);
                }
				//$str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                //if ($result['ID'] > $max_id)
                //    $max_id = $result['ID'];

                $res_id = $result['ID'];
                echo '<tr>
                    <td style="width: 35px">' . $result['ID'] . '</td>
                    <td style="width: 120px">' . $result['DEPNAME'] . '</td>
                    <td style="width: 150px">' . ($result['SRAID'] == SOURCE_ALL ? "Все" : $result['SRANAME']) . '</td>
                    <td style="width: 200px">' . ($result['SRTID'] == SOURCE_ALL ? "Все" : $result['SRTNAME']) . '</td>
                    <td style="width: 200px">' . ($result['SRMID'] == SOURCE_ALL ? "Все" : $result['SRMNAME']) . '</td>
                    <td>' . ($result['SRVID'] == SERVICE_ALL ? "Все" : $result['SRVNAME']) . '</td>
                    <!--td style="text-align: center"><a href="?del_id='.$res_id.'">Удалить</a></td-->
                </tr>';
            }
        }
    }

    //Удаляем, если что, но пока лишь меняем признак удаления строки
    if (isset($_GET['del_id'])) {
        $deletestr = "DELETE FROM  ACCESS_DEP WHERE ID = '{$_GET['del_id']}'";
        if (DB_OCI) {
            //$deletestr = "UPDATE ACCESS_DEP SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
            $query = OCIParse($c, $deletestr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            //$deletestr = "UPDATE ACCESS_DEP SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
            $query_result = mysqli_query($c, $deletestr);
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
    ?>
    </tbody>
    </table>
</div>

    <form action="" method="POST">
        <h3>Департамент:&nbsp;
        <?php if (GetData::GetDepartments(FALSE,FALSE,NULL) > 0) {
            echo "<select id='Depart' name='Depart'>";
            if (DB_OCI) {
                foreach($_POST['array_dep'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
            else {
                foreach ($_POST['array_dep'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
            echo "</select>";
        }
        echo "<br><label id='ServNameT' for='ServName'>Услуга:&nbsp;</label>";
        echo "<select id='ServName' name='ServName'>";
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
        echo "<label id='S_ManT' for='S_Man'>&nbsp;&nbsp;Источник:&nbsp;</label>";
        echo "<select id='S_Man' name='S_Man'>";
        if (GetData::GetIstochnik(TRUE,FALSE,"instr(in_dep, '-1') != 0", FALSE) > 0) {
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
        echo "<label id='S_TypeT' for='S_Type'>&nbsp;&nbsp;Тип Источника:&nbsp;</label>";
        echo "<select id='S_Type' name='S_Type'>";
        if (GetData::GetSourceType(TRUE, FALSE) > 0) {
            // вставить проверку на соответствие
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
        echo "<br><label id='S_AutoT' for='S_Auto'>Источник (авто):&nbsp;</label>";
        if (GetData::GetSourceAuto("DELETED IS NULL", NULL, FALSE) > 0) {
            echo "<select id='S_Auto' name='S_Auto'><option value='".SOURCE_ALL."'>Все источники</option>";
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
                    echo "<option value='".$value[0]."'>".$value[2]."</option>";
                }
            }
            echo "</select>";
        }

        /*echo "&nbsp;Тип звонка:&nbsp;";
        printf("<select id='Call_Type' name='Call_Type'><option value='-1'>Все типы</option>");
        if (GetData::GetCallType() > 0) {
            // вставить проверку на соответствие
            if (DB_OCI) {
                foreach($_POST['array_ctype'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_ctype'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
        }
        echo "</select>";*/
        ?>
        <br>
        <input type='submit' value='Добавить сочетание в базу' name='Adding' class="add_button">
        <!--input type="hidden" name = "table_name" value="ACCESS_DEP"-->
        </h3>
    </form>

    <?php
    function OnClick()
    {
        // Обработка действий
        //$count = 0;
        if (isset($_POST['S_Auto']) && isset($_POST['S_Man']) && isset($_POST['S_Type']) && isset($_POST['ServName'])) {
            $checkstr = "SELECT departament_id FROM ACCESS_DEP 
                        WHERE source_auto_id = {$_POST['S_Auto']} AND source_type_id = {$_POST['S_Type']} 
                        AND source_man_id = {$_POST['S_Man']} AND service_id = {$_POST['ServName']}";
            if (DB_OCI) {
                $objParse = OCIParse(GetData::GetConnect(), $checkstr);
                OCIExecute($objParse);
                $result = OCI_Fetch_Row($objParse);
                //$count = ($objResult == TRUE ? 1 : 0);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
                if (FALSE !== $query_result) {
                    $result = mysqli_fetch_row($query_result);
                    //$count = mysqli_num_rows($query_result);
                } else {
                    //$count = 1;
                    printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
                }
            }
        } // что делать, если данные не выбраны?

        if ($result[0] === $_POST['Depart']) {
            echo "<p style='color: red'>Такое сочетание уже существует для выбранного Департамента.<br /></p>";
        } else {
            //Вставляем данные
            $insertstr = "INSERT INTO ACCESS_DEP (id, departament_id, source_auto_id, source_man_id, source_type_id, service_id)
                VALUES (SEQ_ACCESS_DEP_ID.nextval, {$_POST['Depart']}, {$_POST['S_Auto']}, {$_POST['S_Man']}, {$_POST['S_Type']}, {$_POST['ServName']} )";
            if (DB_OCI) {
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
                oci_free_statement($query);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }
            if ($query_result) {
                /*print "<script language='Javascript'>
					function reload() {location = \"admin_access_dep.php\"}; setTimeout('reload()', 3000);
					</script>";*/
				//echo "<p style='color: green'>Строка успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
				echo "<p style='color: green'>Строка успешно добавлена в таблицу.</p>";
            } else {
                echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
            }
        }
    }
    if (isset($_POST['Adding']))
        OnClick();
    ?>

</body>
</html>