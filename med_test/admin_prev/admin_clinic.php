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
        strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
    echo '<table class="scrolling-table_uie">';
} else {
    echo '<table class="scrolling-table_u">';
}

$theads = array(
    'smd.ID' => array('name' => 'ID', 'width' => '35'),
    'smd.NAME' => array('name' => 'Название', 'width' => '131'),
    'smd.SERVICE_ID' => array('name' => 'Специализация', 'width' => '111'),
    'smd.CITY' => array('name' => 'Город', 'width' => '61'),
    'smd.ADDRESS' => array('name' => 'Адрес', 'width' => '201'),
    'smd.PHONE' => array('name' => 'Телефон', 'width' => '101'),
    'smd.TRADEMARK' => array('name' => 'Торговая марка', 'width' => '111'),
    'smd.DELETED' => array('name' => 'Удалена', 'width' => '121'),
    '' => array('name' => 'Действие', 'width' => '80')
);

if (isset($_GET['key'])) {
    $key=$_GET['key'];
    $sort=$_GET['sort'];
}
else {
    $key='smd.NAME';
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

echo '</tr></thead>';
echo '<tbody>';

$max_id = 0; // для вставки новой строки, если потребуется
if (DB_OCI) {
    $selectstr = "SELECT smd.ID, smd.NAME as SMDNAME, smd.SERVICE_ID, sm.Name as SMNAME, smd.CITY, smd.ADDRESS, smd.PHONE, smd.TRADEMARK, to_char(smd.Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted 
                  FROM HOSPITALS smd, SERVICES sm WHERE sm.ID = smd.SERVICE_ID";
    $selectstr .= " ORDER BY ".$key." ".$sort.", smd.NAME ASC";
    $sort = ($sort == 'desc' ? 'asc' : 'desc');

    $query = OCIParse(GetData::GetConnect(), $selectstr);
    $query_result = OCIExecute($query);
    if ($query_result) {
        while ($result_array = OCI_Fetch_Array($query)) {
            if (TRUE == ENCODE_UTF) {
                $result_array['SMDNAME'] = iconv('windows-1251', 'utf-8', $result_array['SMDNAME']);
                $result_array['SMNAME'] = iconv('windows-1251', 'utf-8', $result_array['SMNAME']);
                $result_array['CITY'] = iconv('windows-1251', 'utf-8', $result_array['CITY']);
                $result_array['ADDRESS'] = iconv('windows-1251', 'utf-8', $result_array['ADDRESS']);
                $result_array['PHONE'] = iconv('windows-1251', 'utf-8', $result_array['PHONE']);
                $result_array['TRADEMARK'] = iconv('windows-1251', 'utf-8', $result_array['TRADEMARK']);
            }
            $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
            if ($result_array['ID'] > $max_id)
                $max_id = $result_array['ID'];

            echo '<tr><td style="text-align: center; width: 35px">' . $result_array['ID'] . '</td>
            <td style="text-align: center; width: 130px">' . $result_array['SMDNAME'] . '</td>
            <td style="text-align: center; width: 110px">' . $result_array['SMNAME'] . '</td>
            <td style="text-align: center; width: 60px">' . $result_array['CITY'] . '</td>
            <td style="text-align: center; width: 200px">' . $result_array['ADDRESS'] . '</td>
            <td style="text-align: center; width: 100px">' . $result_array['PHONE'] . '</td>
            <td style="text-align: center; width: 110px">' . $result_array['TRADEMARK'] . '</td>
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
    $selectstr = "SELECT smd.ID, smd.NAME as SMDNAME, smd.SERVICE_ID, sm.Name as SMNAME, smd.CITY, smd.ADDRESS, smd.PHONE, smd.TRADEMARK, DATE_FORMAT(smd.Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted
                  FROM HOSPITALS smd, SERVICES sm WHERE sm.ID = smd.SERVICE_ID
                  ORDER BY smd.CITY, SMNAME, smd.NAME";
    $sql = mysqli_query(GetData::GetConnect(), $selectstr);

    if ($sql) {
        while ($result = $sql->fetch_array()) {
            if (TRUE == ENCODE_UTF) {
                $result['SMDNAME'] = iconv ('utf-8', 'windows-1251', $result['SMDNAME']);
                $result['SMNAME'] = iconv ('utf-8', 'windows-1251', $result['SMNAME']);
                $result['CITY'] = iconv ('utf-8', 'windows-1251', $result['CITY']);
                $result['ADDRESS'] = iconv ('utf-8', 'windows-1251', $result['ADDRESS']);
                $result['PHONE'] = iconv ('utf-8', 'windows-1251', $result['PHONE']);
                $result['TRADEMARK'] = iconv ('utf-8', 'windows-1251', $result['TRADEMARK']);
            }
            $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
            if ($result['ID'] > $max_id)
                $max_id = $result['ID'];

            echo '<tr><td style="text-align: center; width: 35px">' . $result['ID'] . '</td>
            <td style="text-align: center; width: 130px">' . $result['SMDNAME'] . '</td>
            <td style="text-align: center; width: 100px">' . $result['SMNAME'] . '</td>
            <td style="text-align: center; width: 60px">' . $result['CITY'] . '</td>
            <td style="text-align: center; width: 200px">' . $result['ADDRESS'] . '</td>
            <td style="text-align: center; width: 100px">' . $result['PHONE'] . '</td>
            <td style="text-align: center; width: 100px">' . $result['TRADEMARK'] . '</td>
            <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
            if ( $result['Deleted'] ) {
                echo '<td style="text-align: center; width: 80px"><a href="?restore_id=' . $result['ID'] . '">Восстановить</a></td></tr>';
            }
            else {
                echo '<td style="text-align: center; width: 80px"><a href="?del_id=' . $result['ID'] . '">Удалить</a></td></tr>';
            }
        }
    }
}
echo '</tbody>';
echo '</table>';

// Обработка действий
if (isset($_POST['Name'])) { // Если переменная Name передана
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
        <?php
        echo "<h3>Новая клиника: <input type='text'  style='width: 190px' name='Name' placeholder='Название клиники'>";
        echo "&nbsp;Профиль:&nbsp;";
        if (GetData::GetServices(FALSE,FALSE, NULL,FALSE) > 0) {
            echo "<select id='ServicesId' name='Services'>";
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
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            echo "</select>";
        }
        echo "&nbsp;Супервайзер:&nbsp;";
        if (GetData::GetUsers(FALSE, FALSE, "ROLE_ID = ".USER_SUPER, "FIO") > 0) {
            echo "<select id='UserId' name='UserId'>" ;
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
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            echo "</select>";
        }
        echo "<br><label for='CityId'>Город:</label>
        <select id='CityId' name='CityId' title='Город'>
            <option value='".CITY_MOSCOW."'>Москва</option>
            <option value='".CITY_PITER."'>Питер</option>
            <option value='".CITY_NN."'>НН</option>
            <option value='".CITY_SOCHI."'>Сочи</option>
        </select>";
        echo "Адрес: <input type='text' style='width: 250px' name='Address' placeholder='Адрес клиники'>";
        echo "Телефон: <input type='text' id='phone_с' name='Phone' placeholder='Телефон клиники'>";
        echo "Торговая марка:&nbsp;";
        echo "<select id='TrademarkId' name='TrademarkId'>";
        $markstr = "SELECT DISTINCT TRADEMARK FROM HOSPITALS ORDER BY TRADEMARK";
        if (DB_OCI) {
            $q = OCIParse(GetData::GetConnect(), $markstr);
            $q_result = OCIExecute($q);
            if ($q_result) {
                while ($value = OCI_Fetch_Array($q)) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['TRADEMARK']);
                        $value['TRADEMARK'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['TRADEMARK'], $value['TRADEMARK']);
                }
            }
        }
        else {
            $sql = mysqli_query(GetData::GetConnect(), $markstr);
            if ($sql) {
                while ($value = $sql->fetch_array()) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[0]);
                        $value[0] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[0]);
                }
            }
        }
        echo "</select>";
        ?>
        </h3>
            <script type="text/javascript">
                jQuery(function($){
                    $("#phone_с").mask("9(999) 999-9999");
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