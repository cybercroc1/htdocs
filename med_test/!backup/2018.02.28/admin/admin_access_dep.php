<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();
extract($_REQUEST);
/*if ($_SERVER['REQUEST_METHOD'] == "POST"){
    header("location:{$_SERVER['PHP_SELF']}");
}*/
require_once '../funct.php';

if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>'; exit();
}
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
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_uie">
        <?php } else { ?>
    <table class="scrolling-table_u">
        <?php } ?>
    <thead><tr>
    <!--<th style="width: 35px;">ID</th>-->
    <th style="width: 120px">Департамент</th>
    <th>Источник(авто)</th>
    <th>Источник</th>
    <th>Услуга</th>
    <th>Тип звонка</th>
    <!--th>Удалено</th>
    <th>Действие</th-->
    </tr></thead>
    <tbody>

    <?php
    //$max_id = 0; // для вставки новой строки, если потребуется
    if (DB_OCI) {
        $selectstr = "SELECT dep.NAME as DEPNAME, sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, CALL_TYPE_ID as CT, serv.NAME as SRVNAME
                      FROM ACCESS_DEP acdep, DEPARTAMENTS dep, SOURCE_AUTO sr_a, SOURCE_MAN sr_man, SERVICES serv
                      WHERE acdep.DEPARTAMENT_ID = dep.ID AND acdep.SOURCE_AUTO_ID = sr_a.ID AND acdep.SOURCE_MAN_ID = sr_man.ID AND acdep.SERVICE_ID = serv.ID  
                      ORDER BY dep.NAME, sr_a.NAME, sr_man.NAME, serv.NAME, CALL_TYPE_ID";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['DEPNAME']);
                    $result_array['DEPNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['SRANAME']);
                    $result_array['SRANAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['SRMNAME']);
                    $result_array['SRMNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['SRVNAME']);
                    $result_array['SRVNAME'] = $tmpstr;
                }
                if ($result_array['CT'] === "1")
                    $tmpstr = "Первичный";
                else if ($result_array['CT'] === "2")
                    $tmpstr = "Повторный";
                else $tmpstr = "Все";
                //$str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                //if ($result_array['ID'] > $max_id)
                //	$max_id = $result_array['ID'];

                echo '<tr>
                    <td style="width: 120px">' . $result_array['DEPNAME'] . '</td>
                    <td>' . ($result_array['SRANAME'] === "all" ? "Все" : $result_array['SRANAME']) . '</td>
                    <td>' . ($result_array['SRMNAME'] === "all" ? "Все" : $result_array['SRMNAME']) . '</td>
                    <td>' . ($result_array['SRVNAME'] === "all" ? "Все" : $result_array['SRVNAME']) . '</td>
                    <td>' . $tmpstr . '</td>
				</tr>';
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT dep.NAME as depName, sr_a.NAME as sraName, sr_man.NAME as srmName, CALL_TYPE_ID as ct, serv.NAME as srvName
                      FROM ACCESS_DEP as acdep, DEPARTAMENTS as dep, SOURCE_AUTO as sr_a, SOURCE_MAN as sr_man, SERVICES as serv
                      WHERE acdep.DEPARTAMENT_ID = dep.ID AND acdep.SOURCE_AUTO_ID = sr_a.ID AND acdep.SOURCE_MAN_ID = sr_man.ID AND acdep.SERVICE_ID = serv.ID  
                      ORDER BY dep.NAME, sr_a.NAME, sr_man.NAME, serv.NAME, CALL_TYPE_ID";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['depName']);
                    $result['depName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['sraName']);
                    $result['sraName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['srmName']);
                    $result['srmName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['srvName']);
                    $result['srvName'] = $tmpstr;
                }
                if ($result['ct'] === "1")
					$tmpstr = "Первичный";
				else if ($result['ct'] === "2")
					$tmpstr =  "Повторный";
				else $tmpstr = "Все";
				//$str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                //if ($result['ID'] > $max_id)
                //    $max_id = $result['ID'];

                echo '<tr>
                    <td style="width: 120px">' . $result['depName'] . '</td>
                    <td>' . ($result['sraName'] === "all" ? "Все" : $result['sraName']) . '</td>
                    <td>' . ($result['srmName'] === "all" ? "Все" : $result['srmName']) . '</td>
                    <td>' . ($result['srvName'] === "all" ? "Все" : $result['srvName']) . '</td>
                    <td>' . $tmpstr . '</td>
                </tr>';
            }
        }
    }
    ?>

    <!--?php
    //Удаляем, если что, но пока лишь меняем признак удаления строки
    if (isset($_GET['del_id'])) {
        //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
        if (DB_OCI) {
            $deletestr = "UPDATE ACCESS_DEP SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
            $query = OCIParse(GetData::GetConnect(), $deletestr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $deletestr = "UPDATE ACCESS_DEP SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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
    ?-->
    </tbody>
    </table>
</div>

    <form action="" method="POST">
        <h3>Департамент:&nbsp;
        <?php
        $nrowsDeps = GetData::GetDepartaments("DELETED IS NULL");

        if ($nrowsDeps > 0) {
            printf("<td><select id='DepId' name='Depart'>");
            // вставить проверку на соответствие
            if (DB_OCI) {
                foreach($_POST['array_dep'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_dep'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            printf("</select></td>");
        }
        ?>
        </h3>

        <h3> Источник (авто):&nbsp;
            <?php
            $nrowsAuto = GetData::GetSourceAuto("DELETED IS NULL", NULL);
            if ($nrowsAuto > 0) {
                printf("<td><select id='SAId' name='S_Auto'><option value='-1'>Все источники</option>");
                // вставить проверку на соответствие
                if (DB_OCI) {
                    foreach($_POST['array_source_auto'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach ($_POST['array_source_auto'] as $key => $value) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?>

            <br/>
            &nbsp;Источник:&nbsp;
            <?php
            $nrowsIst = GetData::GetIstochnik("DELETED IS NULL");
            if ($nrowsIst > 0) {
                printf("<td><select id='SMId' name='S_Man'><option value='-1'>Все источники</option>");
                // вставить проверку на соответствие
                if (DB_OCI) {
                    foreach($_POST['array_istochnik'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach ($_POST['array_istochnik'] as $key => $value) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?>

            &nbsp;Услуга:&nbsp;
            <?php
            $nrowsServ = GetData::GetServices("DELETED IS NULL");
            if ($nrowsServ > 0) {
                printf("<td><select id='ServId' name='ServName'><option value='-1'>Все услуги</option>");
                // вставить проверку на соответствие
                if (DB_OCI) {
                    foreach($_POST['array_services'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach ($_POST['array_services'] as $key => $value) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?>
            
            &nbsp;Тип звонка:&nbsp;
            <?php
            $nrowsCType = GetData::GetCallType();
            if ($nrowsCType > 0) {
                printf("<td><select id='CTId' name='Call_Type'><option value='-1'>Все типы</option>");
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
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?>
        </h3>

        <input type='submit' value='Добавить сочетание в базу' name='Adding' class="add_button">
        <!--input type="hidden" name = "table_name" value="ACCESS_DEP"-->
    </form>

    <?php
    function OnClick()
    {
        // Обработка действий
        //$count = 0;
        if (isset($_POST['S_Auto']) && isset($_POST['S_Man']) && isset($_POST['Call_Type']) && isset($_POST['ServName'])) {
            $checkstr = "SELECT departament_id FROM ACCESS_DEP 
                        WHERE source_auto_id = {$_POST['S_Auto']} AND source_man_id = {$_POST['S_Man']} 
                        AND call_type_id = {$_POST['Call_Type']} AND service_id = {$_POST['ServName']}";
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
            $insertstr = "INSERT INTO ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
                VALUES ( {$_POST['Depart']}, {$_POST['S_Auto']}, {$_POST['S_Man']}, {$_POST['Call_Type']}, {$_POST['ServName']} )";
            if (DB_OCI) {
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
                oci_free_statement($query);
            } else {
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }
            if ($query_result) {
                print "<script language='Javascript'>
					function reload() {location = \"admin_access_dep.php\"}; setTimeout('reload()', 3000);
					</script>
				<p style='color: green'>Строка успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
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

