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
$backurl = "admin_theme.php";
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
    <title>Темы звонка</title>
    <meta name="description" content="Тема звонка">
</head>

<body>
<h3>Список Тем</h3>
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width:  35px;">ID</th>
        <th style="width: 200px;">Наименование</th>
        <th style="width: 100px;">Целевой</th>
        <th style="width: 120px;">Удалена</th>
        <th style="width:  85px;">Действие</th>
    </tr>

    <?php
    $max_id = 0; // для вставки новой строки, если потребуется

    if (DB_OCI) {
        $selectstr = "SELECT ID, NAME, TARGET, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted FROM CALL_THEME ORDER BY ID";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['NAME']);
                    $result_array['NAME'] = $tmpstr;
                }
                $str_target = ($result_array['TARGET'] ? 'Да' : "Нет");
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "нет");
                if ($result_array['ID'] > $max_id)
                    $max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center">' . $result_array['ID'] . '</td>
				<td style="text-align: center">' . $result_array['NAME'] . '</td>
                <td style="text-align: center">' . $str_target . '</td>
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
        $selectstr = "SELECT ID, Name, Target, DATE_FORMAT(Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted FROM CALL_THEME ORDER BY ID";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251',  $result['Name']);
                    $result['Name'] = $tmpstr;
                }
                $str_target = ($result['Target'] ? 'Целевой' : "Нет");
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "нет");
                if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center">' . $result['ID'] . '</td>
                <td style="text-align: center">' . $result['Name'] . '</td>
                <td style="text-align: center">' . $str_target . '</td>
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
if (isset($_POST['Name'])) {
    $iTarget = 0;
    if ($_POST['checkbox_target'] == "on")
        $iTarget = 1;

    $count = 0;
    if (DB_OCI) {
        $count = (in_array($_POST['Name'], $result_array['NAME']) == TRUE ? 1 : 0);
    }
    else {
        $count = (in_array($_POST['Name'], $result['Name']) == TRUE ? 1 : 0);
/*        $checkstr = "SELECT ID FROM CALL_THEME WHERE NAME LIKE '{$_POST['Name']}' limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }*/
    }

    if ($count === 1) {
        echo "<p style='color: red'>Тема '".$_POST['Name']."' уже существует.<br /></p>";
        var_dump("Существует");
    }
    else {
        $max_id++;

        //Вставляем данные
        $insertstr = "INSERT INTO CALL_THEME (ID, NAME, TARGET) VALUES ( {$max_id}, '{$_POST['Name']}', '{$iTarget}' )";
echo "<textarea>".$insertstr."</textarea>";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
				$tmpstr = iconv ('utf-8','windows-1251', $_POST['Name']);
                $insertstr = "INSERT INTO CALL_THEME (ID, NAME, TARGET) VALUES ( {$max_id}, '{$tmpstr}', '{$iTarget}' )";
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
			//print "<script language='Javascript'>
			//		function reload() {location = \"$backurl\"}; setTimeout('reload()', 3000);
			//		</script>
			echo"	<p style='color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
        }
    }
}

//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE CALL_THEME SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE CALL_THEME SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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
    $deletestr = "UPDATE CALL_THEME SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
        <h3>
            Новая тема звонка: <input type="text" name="Name" style="width: 330px;" placeholder="Тема звонка">
            <label for="checkbox_target" style="margin-left: 10px"> Целевой:</label>
            <input type="checkbox" name="checkbox_target" title="Целевой"/>
        </h3>
        <div>
            <input type="submit" name="Adding" value="Добавить в базу" class="add_button">
            <input type="hidden" name = "table_name" value="CALL_THEME">
        </div>
    </form>
</div>

</body>
</html>