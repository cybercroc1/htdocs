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

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Сотрудники по Департаментам</h3>
<div class="container">
    <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
        strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
        echo '<table class="scrolling-table_uie">';
    } else {
        echo '<table class="scrolling-table_u">';
    }
    $theads = array(
        'uda.ID' => array('name' => 'ID', 'width' => '35'),
        'usr.ID' => array('name' => 'UserID', 'width' => '40'),
        'usr.PIN' => array('name' => 'ПИН', 'width' => '50'),
        'usr.FIO' => array('name' => 'Сотрудник', 'width' => '161'),
        'dep.NAME' => array('name' => 'Департамент', 'width' => '201'),
        'usr.ROLE_ID' => array('name' => 'Роль', 'width' => '101'),
        'usr.ACTIVITY' => array('name' => 'Активность', 'width' => '121'),
        'uda.DELETED' => array('name' => 'Удалена', 'width' => '121'),
        '' => array('name' => 'Действие', 'width' => '86')
    );

    if (isset($_GET['key'])) {
        $key=$_GET['key'];
        $sort=$_GET['sort'];
    }
    else {
        $key='usr.ROLE_ID';
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
    echo '</tr></thead><tbody>';

    //$max_id = 1; // для вставки новой строки, если потребуется
    if (DB_OCI) {
        $selectstr = "SELECT uda.ID, dep.NAME as depName, usr.ID as usrID, usr.FIO as USRNAME, usr.PIN, role.NAME as ROLE, to_char(uda.DELETED,'dd.mm.yyyy hh24:mi:ss') Deleted, to_char(usr.ACTIVITY,'dd.mm.yyyy hh24:mi:ss') ACTIVITY
              FROM USER_DEP_ALLOC uda, DEPARTAMENTS dep, USERS usr, ROLES role 
              WHERE uda.DEP_ID != -1 AND uda.DEP_ID = dep.ID AND uda.USER_ID = usr.ID  AND usr.ROLE_ID = role.ID";
        $selectstr .= " ORDER BY ".$key." ".$sort.", usr.FIO ASC";
//              ORDER BY usr.ROLE_ID, usr.FIO, dep.NAME ";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $result_array['DEPNAME'] = iconv('windows-1251', 'utf-8', $result_array['DEPNAME']);
                    $result_array['USRNAME'] = iconv('windows-1251', 'utf-8', $result_array['USRNAME']);
                    $result_array['ROLE'] = iconv('windows-1251', 'utf-8', $result_array['ROLE']);
                }
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                //if ($result_array['ID'] > $max_id)
                //	$max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result_array['ID'] . '</td>
                    <td style="text-align: center; width: 41px">' . $result_array['USRID'] . '</td>
                    <td style="text-align: center; width: 51px">' . $result_array['PIN'] . '</td>
                    <td style="text-align: center; width: 160px">' . $result_array['USRNAME'] . '</td>
                    <td style="text-align: center; width: 200px">' . $result_array['DEPNAME'] . '</td>
                    <td style="text-align: center; width: 100px">' . $result_array['ROLE'] . '</td>
                    <td style="text-align: center; width: 120px">' . $result_array['ACTIVITY'] . '</td>
                    <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($result_array['DELETED']) {
                    echo '<td style="text-align: center; width: 100px;"><a href="?restore_id=' . $result_array['ID'] . '">Восстановить</a></td></tr>';
                } else {
                    echo '<td style="text-align: center; width: 100px;"><a href="?del_id=' . $result_array['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT uda.ID, dep.NAME as depName, usr.FIO as usrName, role.NAME as ROLE, DATE_FORMAT(uda.DELETED,'%d.%m.%Y %H:%i:%s') as Deleted
              FROM USER_DEP_ALLOC uda, DEPARTAMENTS dep, USERS usr, ROLES role 
              WHERE uda.DEP_ID != -1 AND uda.DEP_ID = dep.ID AND uda.USER_ID = usr.ID  AND usr.ROLE_ID = role.ID
              ORDER BY usr.ROLE_ID, usr.FIO, dep.NAME ";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['usrName']);
                    $result['usrName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['depName']);
                    $result['depName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['ROLE']);
                    $result['ROLE'] = $tmpstr;
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                //if ($result['ID'] > $max_id)
                //    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result['ID'] . '</td>
                    <td style="text-align: center; width: 150px">' . $result['usrName'] . '</td>
                    <td style="text-align: center; width: 150px">' . $result['depName'] . '</td>
                    <td style="text-align: center; width: 100px">' . $result['ROLE'] . '</td>
                    <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($result['Deleted']) {
                    echo '<td style="text-align: center; width: 100px;"><a href="?restore_id=' . $result['ID'] . '">Восстановить</a></td></tr>';
                } else {
                    echo '<td style="text-align: center; width: 100px;"><a href="?del_id=' . $result['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
    }
    ?>
    </tbody>
    </table>

    <?php
    //Удаляем, если что, но пока лишь меняем признак удаления строки
    if (isset($_GET['del_id'])) {
        if (DB_OCI) {
            $deletestr = "UPDATE USER_DEP_ALLOC SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
            $query = OCIParse(GetData::GetConnect(), $deletestr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $deletestr = "UPDATE USER_DEP_ALLOC SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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
        $deletestr = "UPDATE USER_DEP_ALLOC SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
</div>

    <form action="" method="POST">
        <h3>Департамент:&nbsp;
        <?php if (GetData::GetDepartments(FALSE,FALSE,NULL) > 0) {
            echo "<select id='Depart' name='Depart'>";
            echo "<option value=''>Выберите департамент</option>";
            if (DB_OCI) {
                foreach(GetData::$array_dep as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
            else {
                foreach(GetData::$array_dep as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
            echo "</select>";
        }
        echo "&nbsp;Сотрудник:&nbsp;";
        if (GetData::GetUsers(FALSE, FALSE,NULL, "FIO") > 0) {
            echo "<select id='UserId' name='User'>";
            echo "<option value=''>Выберите пользователя</option>";
            if (DB_OCI) {
                foreach(GetData::$array_user as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['FIO']);
                        $value['FIO'] = $tmpstr;
                    }
                    echo "<option value='".$value['ID']."'>".$value['FIO']."</option>";
                }
            }
            else {
                foreach(GetData::$array_user as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
            echo "</select>";
        } ?>
        </h3>

        <input type="submit" value="Добавить сочетание в базу" name="Adding" class="add_button">
        <!--onclick="AddPair();" input type="hidden" name = "table_name" value="USER_DEP_ALLOC"-->
    </form>

    <?php
    if (isset($Adding)) // Обработка действий
    {
        //echo "<script>alert('Adding');</script>";
        if (isset($Depart) && isset($User)) { // Если Департамент и Пользователь выбраны
            $checkstr = "SELECT DEP_ID FROM USER_DEP_ALLOC WHERE DEP_ID = {$Depart} AND USER_ID = {$User}";
            if (DB_OCI) {
                $objParse = OCIParse(GetData::GetConnect(), $checkstr);
                OCIExecute($objParse);
                $result = OCI_Fetch_Row($objParse);
                $count = ($result == TRUE ? 1 : 0);
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

            if ($count === 1) {
                echo "<p style='color: red'>Такое сочетание '" . $Depart . "'/'" . $User . "' уже существует.<br /></p>";
            } else {
                //Вставляем данные
                //$insertstr = "INSERT INTO USER_DEP_ALLOC (ID, DEP_ID, USER_ID) VALUES ( {$max_id}, {$Depart}, {$User} )";
                $insertstr = "INSERT INTO USER_DEP_ALLOC (ID, DEP_ID, USER_ID) VALUES ( SEQ_USER_DEP_ID.nextval, {$Depart}, {$User} )";
                if (DB_OCI) {
                    $query = OCIParse(GetData::GetConnect(), $insertstr);
                    $query_result = OCIExecute($query);
                    oci_free_statement($query);
                } else {
                    $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
                }
                if ($query_result) {
                    print "<script language='Javascript'>
                            function reload() {location = \"admin_user_dep.php\" }; setTimeout('reload()', 3000);
                            </script>";
                    echo "<p style='color: green'>Строка успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
                } else {
                    echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
                }
            }
            //return;
        }
    }
    ?>

</body>
</html>