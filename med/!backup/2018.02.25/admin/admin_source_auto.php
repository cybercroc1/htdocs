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

<body>
<h3>Список BNumber</h3>
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width: 35px;">ID</th>
        <th>Б Номер</th>
        <th style="width: 400px;">Наименование</th>
        <th style="width: 120px;">Удалена</th>
        <th style="width:  85px;">Действие</th>
    </tr>

    <?php
    $max_id = 0; // для вставки новой строки, если потребуется

    if (DB_OCI) {
        $selectstr = "SELECT ID, BNUMBER, NAME, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') AS Deleted FROM SOURCE_AUTO WHERE ID != -1";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['BNUMBER']);
                    $result_array['BNUMBER'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['NAME']);
                    $result_array['NAME'] = $tmpstr;
                }
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                if ($result_array['ID'] > $max_id)
                    $max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center">' . $result_array['ID'] . '</td>
				<td style="text-align: center">' . $result_array['BNUMBER'] . '</td>
				<td style="text-align: center">' . $result_array['NAME'] . '</td>
				<td style="text-align: center">' . $str_deleted . '</td>';
                if ($result_array['DELETED']) {
                    echo '<td style="text-align: center"><a href="?restore_id=' . $result_array['ID'] . '">Восстановить</a></td></tr>';
                } else {
                    echo '<td style="text-align: center"><a href="?del_id=' . $result_array['ID'] . '">Удалить</a></td></tr>';
                }
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT ID, BNumber, Name, DATE_FORMAT(Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted FROM SOURCE_AUTO WHERE ID != -1";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8','windows-1251', $result['BNumber']);
                    $result['BNumber'] = $tmpstr;
                    $tmpstr = iconv ('utf-8','windows-1251', $result['Name']);
                    $result['Name'] = $tmpstr;
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center">' . $result['ID'] . '</td>
                <td style="text-align: center">' . $result['BNumber'] . '</td>
                <td style="text-align: center">' . $result['Name'] . '</td>
                <td style="text-align: center">' . $str_deleted . '</td>';
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
</table>

<?php
// Обработка действий
// Если переменная Name передана
if (isset($_POST['BNumber'])) {
    $count = 0;
    if (DB_OCI) {
        $count = (in_array($_POST['BNumber'], $result_array['BNUMBER']) == TRUE ? 1 : 0);
    }
    else {
        $checkstr = "SELECT ID FROM SOURCE_AUTO WHERE BNUMBER LIKE '{$_POST['BNumber']}' limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>BNumber '".$_POST['BNumber']."' уже существует.<br /></p>";
    }
    else {
        $max_id++;

        //Вставляем данные
        $insertstr = "INSERT INTO SOURCE_AUTO (ID, BNUMBER, NAME) VALUES ( seq_source_auto_id.nextval, '{$_POST['BNumber']}', '{$_POST['Reservoir']}' )";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstr = iconv ('utf-8','windows-1251', $_POST['Reservoir']);
                $tmpstrbn = iconv ('utf-8','windows-1251', $_POST['BNumber']);
                $insertstr = "INSERT INTO SOURCE_AUTO (ID, BNUMBER, NAME) VALUES ( {seq_source_auto_id.nextval}, '{$tmpstrbn}', '{$tmpstr}' )";
			}
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
        <h3>Источник:&nbsp;<input type="text" name="Reservoir" style="width: 350px" placeholder="Источник">
            <!--?php
            $nrows = GetData::GetIstochnik("DELETED IS NULL");

            if ($nrows > 0) {
                printf("<td><select id='Reservoir' name='Reservoir'>");
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
                        printf("<option value='%s'>%s</option>", $value[1], $value[1]);
                    }
                }
                printf("</select></td>");
            }
            ?-->

            BNumber: <input type="text" name="BNumber" placeholder="BNumber"></h3>
        <div>
            <input type="submit" name="Adding" value="Добавить в базу" class="add_button">
            <input type="hidden" name = "table_name" value="SOURCE_AUTO">
        </div>
    </form>
</div>

</body>
</html>