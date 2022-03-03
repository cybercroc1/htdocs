<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();
extract($_REQUEST);
if ($_SERVER['REQUEST_METHOD'] == "POST"){
    header("location:{$_SERVER['PHP_SELF']}");
}
require_once '../funct.php';

if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>'; exit();
}
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "admin_user_dep.php";
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
    <title>Пользователи по Департаментам</title>
    <meta name="description" content="Пользователи по Департаментам">
</head>

<body>
<h3>Сотрудники по Департаментам</h3>
<div class="container">
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_uie">
        <?php } else { ?>
    <table class="scrolling-table_u">
        <?php } ?>
    <thead><tr>
        <!--<th style="width: 35px;">ID</th>-->
        <th style="width: 150px">Сотрудник</th>
        <th style="width: 150px">Департамент</th>
        <th style="width: 100px">Роль</th>
        <!--<th>Удалено</th>
        <th>Действие</th>-->
    </tr></thead>
    <tbody>

    <?php
    //$max_id = 0; // для вставки новой строки, если потребуется
    if (DB_OCI) {
        $selectstr = "SELECT dep.NAME as depName, usr.FIO as usrName, role.NAME as ROLE 
              FROM USER_DEP_ALLOC uda, DEPARTAMENTS dep, USERS usr, ROLES role 
              WHERE uda.DEP_ID != -1 AND uda.DEP_ID = dep.ID AND uda.USER_ID = usr.ID  AND usr.ROLE_ID = role.ID
              ORDER BY usr.ROLE_ID, usr.FIO, dep.NAME ";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['DEPNAME']);
                    $result_array['DEPNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['USRNAME']);
                    $result_array['USRNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['ROLE']);
                    $result_array['ROLE'] = $tmpstr;
                }
                //$str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                //if ($result_array['ID'] > $max_id)
                //	$max_id = $result_array['ID'];

                echo '<tr>
                    <td style="text-align: center; width: 150px">' . $result_array['USRNAME'] . '</td>
                    <td style="text-align: center; width: 150px">' . $result_array['DEPNAME'] . '</td>
                    <td style="text-align: center; width: 100px">' . $result_array['ROLE'] . '</td>
                </tr>';
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT dep.NAME as depName, usr.FIO as usrName FROM USER_DEP_ALLOC as uda, DEPARTAMENTS as dep, USERS as usr 
                      WHERE uda.DEP_ID = dep.ID AND uda.USER_ID = usr.ID ORDER BY dep.NAME, usr.FIO";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['depName']);
                    $result['depName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['usrName']);
                    $result['usrName'] = $tmpstr;
                }
                //$str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                //if ($result['ID'] > $max_id)
                //    $max_id = $result['ID'];

                echo '<tr>
                    <td style="text-align: center">' . $result['usrName'] . '</td>
                    <td style="text-align: center">' . $result['depName'] . '</td>
                </tr>';
            }
        }
    }
    ?>
    </tbody>
    </table>
</div>

    <form action="" method="POST">
        <h3>Департамент:&nbsp;
        <?php
        $nrowsDeps = GetData::GetDepartaments("DELETED IS NULL");

        if ($nrowsDeps > 0) {
            printf("<td><select id='Depart' name='Depart'>");
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

        Сотрудник:&nbsp;
            <?php
            $nrowsUser = GetData::GetUsers("DELETED IS NULL");

            if ($nrowsUser > 0) {
                printf("<td><select id='UserId' name='User'>");
                // вставить проверку на соответствие
                if (DB_OCI) {
                    foreach($_POST['array_user'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv ('windows-1251', 'utf-8', $value['FIO']);
                            $value['FIO'] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['FIO']);
                    }
                }
                else {
                    foreach ($_POST['array_user'] as $key => $value) {
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

        <input type="submit" value="Добавить сочетание в базу" name="Adding" class="add_button">
        <!--onclick="AddPair();" input type="hidden" name = "table_name" value="USER_DEP_ALLOC"-->
    </form>

    <?php
    function OnClick()
    {
        // Обработка действий
        // Если Департамент и Пользователь выбраны... хотя, всегда первый, если данные там уже есть
        if (isset($_POST['Depart']) && isset($_POST['User'])) {

            $checkstr = "SELECT DEP_ID FROM USER_DEP_ALLOC WHERE DEP_ID = {$_POST['Depart']} AND USER_ID = {$_POST['User']}";
            if (DB_OCI) {
                echo "<br/>";
                echo $checkstr;
                echo "<br/>";
                $objParse = OCIParse(GetData::GetConnect(), $checkstr);
                OCIExecute($objParse);
                $result = OCI_Fetch_Row($objParse);
                var_dump($result);
                $count = ($result == TRUE ? 1 : 0);
            } else {
                $checkstr .= " limit 1";
                echo "<br/>";
                echo $checkstr;
                echo "<br/>";
                $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
                if (FALSE !== $query_result)
                    $count = mysqli_num_rows($query_result);
                else {
                    $count = 1;
                    printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
                }
            }

            if ($count === 1) {

                echo "<p style='color: red'>Такое сочетание '" . $_POST['Depart'] . "'/'" . $_POST['User'] . "' уже существует.<br /></p>";
            } else {
                //$max_id++;

                //Вставляем данные
                //$insertstr = "INSERT INTO USER_DEP_ALLOC (ID, DEP_ID, USER_ID) VALUES ( {$max_id}, {$_POST['Depart']}, {$_POST['User']} )";
                $insertstr = "INSERT INTO USER_DEP_ALLOC (DEP_ID, USER_ID) VALUES ( {$_POST['Depart']}, {$_POST['User']} )";
                if (DB_OCI) {
                    $query = OCIParse(GetData::GetConnect(), $insertstr);
                    $query_result = OCIExecute($query);
                    oci_free_statement($query);
                } else {
                    $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
                }
                if ($query_result) {
                    //$max_id++; // для вставки следующей строки
                    print "<script language='Javascript'>
            function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
                    		</script>
                    <p style='color: green'>Строка успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
                } else {
                    echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
                }
            }
            //return;
        }
    }
    if (isset($_POST['Adding']))
        OnClick();
    ?>

</body>
</html>

