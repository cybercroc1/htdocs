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
$backurl = "admin_clinic.php";
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
    <title>Клиники</title>
    <meta name="description" content="Клиники">
    <script src="../js/jquery.maskedinput.js"></script>
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Список Клиник</h3>
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_uie">
        <?php } else { ?>
    <table class="scrolling-table_u">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px;">ID</th>
        <th style="width: 130px;">Название</th>
        <th style="width: 100px;">Специализация</th>
        <th style="width: 200px;">Адрес</th>
        <th style="width: 100px;">Телефон</th>
        <th style="width: 100px;">Торговая марка</th>
        <th style="width: 120px;">Удалена</th>
        <th style="width: 80px;">Действие</th>
    </tr></thead>
    <tbody>
    <?php
    $max_id = 0; // для вставки новой строки, если потребуется

    if (DB_OCI) {
        $selectstr = "SELECT smd.ID, smd.NAME as SMDNAME, smd.SERVICE_ID, sm.Name as SMNAME, smd.ADDRESS, smd.PHONE, smd.TRADEMARK, to_char(smd.Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted 
                      FROM HOSPITALS smd, SERVICES sm WHERE sm.ID = smd.SERVICE_ID
                      ORDER BY smd.NAME, sm.Name";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['SMDNAME']);
                    $result_array['SMDNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['SMNAME']);
                    $result_array['SMNAME'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['ADDRESS']);
                    $result_array['ADDRESS'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['PHONE']);
                    $result_array['PHONE'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['TRADEMARK']);
                    $result_array['TRADEMARK'] = $tmpstr;
                }
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                if ($result_array['ID'] > $max_id)
                    $max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result_array['ID'] . '</td>
				<td style="text-align: center; width: 130px">' . $result_array['SMDNAME'] . '</td>
                <td style="text-align: center; width: 100px">' . $result_array['SMNAME'] . '</td>
				<td style="text-align: center; width: 200px">' . $result_array['ADDRESS'] . '</td>
				<td style="text-align: center; width: 100px">' . $result_array['PHONE'] . '</td>
				<td style="text-align: center; width: 100px">' . $result_array['TRADEMARK'] . '</td>
				<td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($result_array['DELETED']) {
                    echo '<td style="text-align: center; width: 80px"><a href="?restore_id=' . $result_array['ID'] . '">Восстановить</a></td></tr>';
                } else {
                    echo '<td style="text-align: center; width: 80px"><a href="?del_id=' . $result_array['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT smd.ID, smd.NAME as smdName, smd.SERVICE_ID, sm.Name as smName, smd.ADDRESS, smd.PHONE, DATE_FORMAT(smd.Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted 
                      FROM HOSPITALS smd, SERVICES sm WHERE sm.ID = smd.SERVICE_ID
                      ORDER BY smd.NAME, sm.Name";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['smdName']);
                    $result['smdName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['smName']);
                    $result['smName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['ADDRESS']);
                    $result['ADDRESS'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['PHONE']);
                    $result['PHONE'] = $tmpstr;
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center">' . $result['ID'] . '</td>
                <td style="text-align: center">' . $result['smName'] . '</td>
                <td style="text-align: center; width: 120px">' . $result['smdName'] . '</td>
                <td style="text-align: center; width: 120px">' . $result['ADDRESS'] . '</td>
                <td style="text-align: center; width: 120px">' . $result['PHONE'] . '</td>
                <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ( $result['Deleted'] ) {
                    echo '<td style="text-align: center"><a href="?restore_id=' . $result['ID'] . '">Восстановить</a></td></tr>';
                }
                else {
                    echo '<td style="text-align: center"><a href="?del_id=' . $result['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
    }
    ?>
        </tbody>
    </table>

<?php
// Обработка действий
// Если переменная Name передана
if (isset($_POST['Name'])) {
    $count = 0;
    $checkstr = "SELECT ID FROM HOSPITALS WHERE NAME LIKE '{$_POST['Name']}' AND SERVICE_ID = {$_POST['Services']}";
    if (DB_OCI) {
		$objParse = OCIParse(GetData::GetConnect(), $checkstr);
		OCIExecute($objParse);
		$objResult = OCI_Fetch_Row($objParse);
		$count = ($objResult == TRUE ? 1 : 0);
	}
    else {
        $checkstr .= " limit 1 ";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !==  $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>Клиника с таким профилем '".$_POST['Name']."' уже существует.<br /></p>";
    }
    else {
        $max_id++;

        //Вставляем данные
        $insertstr = "INSERT INTO HOSPITALS (ID, NAME, SERVICE_ID, ADDRESS, PHONE, CITY, MANAGER_ID, TRADEMARK) 
                  VALUES ( {$max_id}, '{$_POST['Name']}', {$_POST['Services']}, 
                  '{$_POST['Address']}', '{$_POST['Phone']}', 'CITIES[{$_POST['CityId']}]', {$_POST['UserId']}, 
                  '{$_POST['TrademarkId']}' )";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstr = iconv ('utf-8','windows-1251', $_POST['Name']);
                $adrstr = iconv ('utf-8','windows-1251', $_POST['Address']);
                $phonestr = iconv ('utf-8','windows-1251', $_POST['Phone']);
                $insertstr = "INSERT INTO HOSPITALS (ID, NAME, SERVICE_ID, ADDRESS, PHONE) 
                      VALUES ( {$max_id}, {$_POST['Services']}, '{$tmpstr}', '{$adrstr}', '{$phonestr}' )";
			}
			var_dump($insertstr);
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			$max_id++; // для вставки следующей строки
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
        $deletestr = "UPDATE HOSPITALS SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE HOSPITALS SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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
    $deletestr = "UPDATE HOSPITALS SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
        <h3>Новая клиника: <input type="text"  style="width: 190px" name="Name" placeholder="Клиника">
            Профиль:&nbsp;
        <?php
        $nrows = GetData::GetServices("DELETED IS NULL");

        if ($nrows > 0) {
            printf("<td><select id='ServicesId' name='Services'>");
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
        </h3>
        <h3>
            <label for="CityId">Город:&nbsp;</label>
            <select id="CityId" name="CityId" title="Город">
                <option value="<?=CITY_MOSCOW?>">Москва</option>
                <option value="<?=CITY_PITER?>">Питер</option>
                <option value="<?=CITY_NN?>">НН</option>
                <option value="<?=CITY_SOCHI?>">Сочи</option>
            </select>
            Адрес: <input type="text" style="width: 250px" name="Address" placeholder="Адрес">
            Телефон: <input type="text" id="phone_с" name="Phone" placeholder="Телефон клиники">
        </h3>
        <h3>Супервайзер:&nbsp;
            <?php
            if (GetData::GetUsers("DELETED IS NULL AND ROLE_ID = ".USER_SUPER) > 0) {
                printf("<td><select id='UserId' name='UserId'>");
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
            Торговая марка:&nbsp;
            <?php
            $markstr = "SELECT DISTINCT TRADEMARK FROM HOSPITALS ORDER BY TRADEMARK";
            $q = OCIParse(GetData::GetConnect(), $markstr);
            $q_result = OCIExecute($q);
            if ($q_result) {
                printf("<td><select id='TrademarkId' name='TrademarkId'>");
                if (DB_OCI) {
                    while ($value = OCI_Fetch_Array($q)) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv ('windows-1251', 'utf-8', $value['TRADEMARK']);
                            $value['TRADEMARK'] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value['TRADEMARK'], $value['TRADEMARK']);
                    }
                }
                else {
                    while ($value = OCI_Fetch_Array($q)) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[1], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?>
        </h3>
            <script type="text/javascript">
                jQuery(function($){
                    $("#phone_с").mask("(999) 999-9999");
                });
            </script>
        <div>
            <input type="submit" name="Adding" value="Добавить клинику в базу" class="add_button">
            <input type="hidden" name = "table_name" value="SOURCE_MAN_DETAIL">
        </div>
    </form>
</div>

</body>
</html>