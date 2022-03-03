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
$backurl = "admin_service.php";
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
    <title>Предоставляемые услуги</title>
    <meta name="description" content="Предоставляемые услуги">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">Список доступных Услуг</h3>
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width:  35px;">ID</th>
        <th style="width: 150px;">Наименование</th>
        <th style="width: 120px;">Удалена</th>
        <th style="width:  85px;">Действие</th>
    </tr>

    <?php
    $max_id = 0; // для вставки новой строки, если потребуется
    if (GetData::GetServices(FALSE,TRUE, NULL,NULL) > 0) {
        foreach($_POST['array_services'] as $key => $value) {
            if (TRUE == ENCODE_UTF) {
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            }
            $str_deleted = ($value['DELETED'] ? $value['DELETED'] : "нет");
            if ($value['ID'] > $max_id)
                $max_id = $value['ID'];

            echo '<tr><td style="text-align: center">' . $value['ID'] . '</td>
				<td style="text-align: center">' . $value['NAME'] . '</td>
				<td style="text-align: center">' . $str_deleted . '</td>';
            if ($value['DELETED']) {
                echo '<td style="text-align: center"><a href="?restore_id=' . $value['ID'] . '">Восстановить</a></td></tr>';
            } else {
                echo '<td style="text-align: center"><a href="?del_id=' . $value['ID'] . '">Удалить</a></td></tr>';
            }
        }
    }
    ?>
</table>

<?php
// Обработка действий
// Если переменная Name передана
if (isset($_POST['Name'])) {
    $bCanAdd = 0;
    foreach($_POST['array_services'] as $key => $value) {
        if (DB_OCI)
            $bCanAdd = strcasecmp($_POST['Name'], $value['NAME']);
        else $bCanAdd = strcmp(strtolower($_POST['Name']), strtolower($value['1']));
        if ($bCanAdd == 0)
            break;
    }

    if ($bCanAdd == 0) {
        echo "<p style='font-size: larger; color: red'>Услуга '".$_POST['Name']."' уже существует.<br /></p>";
    }
    else {
        //Вставляем данные
        if (DB_OCI) {
            $insertstr = "INSERT INTO SERVICES (ID, NAME) VALUES (SEQ_SERVICE_ID.NEXTVAL, '".trim($_POST['Name'])."' )";
			if (TRUE == ENCODE_UTF) {
				$tmpstr = trim(iconv ('utf-8','windows-1251', $_POST['Name']));
                $insertstr = "INSERT INTO SERVICES (ID, NAME) VALUES (SEQ_SERVICE_ID.NEXTVAL, '{$tmpstr}' )";
			}
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $max_id++;
            $insertstr = "INSERT INTO SERVICES (ID, NAME) VALUES ( {$max_id}, '".trim($_POST['Name'])."' )";
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			print "<script language='Javascript'>
					function reload() {location = \"$backurl\"}; setTimeout('reload()', 3000);
					</script>
				<p style='font-size: larger; color: green'>Данные успешно добавлены в таблицу. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='font-size: larger; color: red'>Произошла ошибка добавления записи!</p>";
        }
    }
}

//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE SERVICES SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE SERVICES SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
		$query_result = mysqli_query(GetData::GetConnect(), $deletestr);
	}
    if ($query_result) {
        print "<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
			<p style='font-size: larger; color: green'>Строка изменена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='font-size: larger; color: red'>Произошла ошибка удаления записи.</p>";
	}
}

//Восстанавливаем удаленное
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE SERVICES SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
			<p style='font-size: larger; color: green'>Строка восстановлена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='font-size: larger; color: red'>Произошла ошибка восстановления записи.</p>";
    }
}

?>

<div>
    <form action="" method="post">
        <h3>Новая услуга: <input type="text" name="Name" style="width: 250px" placeholder="Услуга"></h3>
        <div>
            <input type="submit" name="Adding" value="Добавить в базу" class="add_button">
            <input type="hidden" name = "table_name" value="SERVICES">
        </div>
    </form>
</div>

</body>
</html>