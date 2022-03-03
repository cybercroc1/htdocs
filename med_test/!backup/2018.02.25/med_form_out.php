<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();
//if ($_SERVER['REQUEST_METHOD'] == "POST"){
//    header("location:{$_SERVER['PHP_SELF']}");
//}

require_once 'funct.php';
?>

<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="./billing.css">
</head>

<body>
<?php

extract($_REQUEST);

// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl="med_call_out.php"; // На какую страничку переходить после перезагрузки
//---------------------------------------------------------------------- //
// Принимаем новые данные из формы

    //$service = $_POST['ServiceId']; // тут Id Услуги
    $ages = (isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : "NULL");
    $phone_mob = (isset($_POST['phone_mob']) ? $_POST['phone_mob'] : "");
    $phone_home = (isset($_POST['phone_home']) ? $_POST['phone_home'] : "");
    $email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : ""); // none@wilstream

    $StatusId = (isset($_POST['StatusId']) ? $_POST['StatusId'] : STATUS_OPEN);
    $user = (isset($_POST['User']) ? $_POST['User'] : "");
    $call_back = (isset($_POST['datetimepicker']) ? $_POST['datetimepicker'] : "");
    if (STATUS_CALL_BACK == $StatusId && strlen($call_back) == 0 ) {
        echo "</br><p style='font-size: large; color: red'> Вернитесь <a href='javascript:history.back(1)' style='font-weight: bold; border-bottom: 1px solid '>назад.</a> Не указаны дата и время перезвона!</p>";
        exit;
    }
    $clinic = (isset($_POST['Clinic']) ? $_POST['Clinic'] : "");
    $surname_cl = (isset($_POST['surname_cl']) ? $_POST['surname_cl'] : "");
    $name_cl = (isset($_POST['name_cl']) ? $_POST['name_cl'] : "");
    $patronymic_cl = (isset($_POST['patronymic_cl']) ? $_POST['patronymic_cl'] : "");
    $ages_cl = (isset($_POST['ages_cl']) && $_POST['ages_cl'] != "" ? $_POST['ages'] : "NULL");

    $comment_cl = (isset($_POST['comment_cl']) ? $_POST['comment_cl'] : "");
    //echo 'Услуга: ' . $service;echo '<br />';

    echo 'Возраст: ' . $ages . '<br />';
    echo 'Мобильный: ' . $phone_mob . '<br />';
    echo 'Домашний: ' . $phone_home . '<br />';
    echo 'Электронный адрес: ' . $email . '<br />';

    echo 'Статус: ' . $StatusId . '<br />';
    if (STATUS_OPEN == $StatusId) { // Только создано
        echo 'Создано.<br />';
    } else if (STATUS_WORK == $StatusId) { // Перевод оператору
        echo 'Назначаем: ' . $user . '<br />';
    } else if (STATUS_CALL_BACK == $StatusId) { // Перезвон
        echo 'Ждет звонка: ' . $call_back . '<br />';
    } else if (STATUS_CALL_NOT == $StatusId) { // Недозвон
        echo 'Не дозвонились.<br />';
    } else if (STATUS_CALL_STOP == $StatusId) { // Глухой недозвон
        echo 'Глухо, как в танке.<br />';
    } else if (STATUS_CLINIC == $StatusId) { // Записали в клинику
        echo 'Записали в: ' . $clinic . '<br />';
        echo 'Фамилия клиента: ' . $surname_cl . '<br />';
        echo 'Имя клиента: ' . $name_cl . '<br />';
        echo 'Отчество клиента: ' . $patronymic_cl . '<br />';
        echo 'Возраст клиента: ' . $ages_cl . '<br />';
    } else if ($StatusId == STATUS_NEGATIVE) { // Негатив
        echo 'Отказ!<br />';
    } else if ($StatusId == STATUS_ERROR) { // Ошибка
        echo 'Что-то не так с номером!!!<br />';
    } else {
        echo '!!!Ошибка!!!<br />';
    }

    //Вставляем данные
    $fio_cl = $surname_cl . "/" . $name_cl . "/" . $patronymic_cl; // в базу CALL_BASE_CLINIC

    if (DB_OCI) {
        $date_call = date("d-m-Y  H:i:s");

        $updatestr = "UPDATE CALL_BASE SET AGE = {$ages}, PHONE_MOB= '{$phone_mob}', PHONE_HOME = '{$phone_home}', EMAIL =  '{$email}',
                        STATUS_ID = {$StatusId}, LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        if (STATUS_WORK == $StatusId) {
            $updatestr .= ", FIO_ID = '{$user}'"; // кому назначено
        }
        if (STATUS_CALL_BACK == $StatusId) { // надо перезвонить
            $updatestr .= ", CALL_BACK_DATE = to_date('{$call_back}','DD.MM.YYYY hh24:mi:ss'), CALL_BACK_NUM = 10 ";
        }
        if (STATUS_CALL_STOP == $StatusId || STATUS_CLINIC == $StatusId ||
            STATUS_NEGATIVE == $StatusId || STATUS_ERROR == $StatusId
        ) { // Закрываем звонок
            $updatestr .= ", DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        }
        $updatestr .= " WHERE ID = {$_POST['Base_Id']}";
//echo "<textarea>".$updatestr."</textarea>";
        $query = OCIParse(GetData::GetConnect(), $updatestr);
        $query_result = OCIExecute($query);

        // добавляем строку записи в клинику по этому звонку.
        if (STATUS_CLINIC == $StatusId) {
            $insertstr = "INSERT INTO CALL_BASE_CLINIC ( ID, BASE_ID, HOSPITAL_ID, CLIENT_NAME, AGE ) 
                          VALUES (SEQ_CALL_BASE_CLINIC_ID.nextval, {$_POST['Base_Id']}, {$clinic}, '{$fio_cl}', {$ages_cl})";
//echo "<textarea>".$insertstr."</textarea>";
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
        }

        // добавляем строку истории по этому звонку с ID исходящего оператора
        $insertstr = "INSERT INTO CALL_BASE_HIST ( ID, BASE_ID, USER_ID, STATUS_ID, DATE_DET, COMMENTS ) 
                      VALUES (SEQ_CALL_BASE_HIST_ID.nextval, {$_POST['Base_Id']}, '{$_SESSION['login_id']}', {$StatusId}, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$comment_cl}')";
//echo "<textarea>".$insertstr."</textarea>";
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    } else {
        $sql = mysqli_query(GetData::GetConnect(), $maxstr);
        if (FALSE !== $sql) {
            $result = mysqli_fetch_row($sql);
            $max_call = $result[0] + 1;
            $date_call = date("Y-m-d H:i:s");
            $insertstr = "UPDATE CALL_BASE ";
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
    }

    if ($query_result) {
        print "<script language='Javascript'>
               function close_win() { window.close(); }; setTimeout('close_win()', 100);
               </script>
            <p style='color: green'>Данные успешно обновлены. Возвращаемся на начальную страницу...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
    }
    exit;
?>

</body>
</html>