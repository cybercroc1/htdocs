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

// ----------------------------������������-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail ������
$date=date("d.m.Y"); // �����.�����.���
$time=date("H:i"); // ����:������:�������
$backurl="med_call_out.php"; // �� ����� ��������� ���������� ����� ������������
//---------------------------------------------------------------------- //
// ��������� ����� ������ �� �����

    //$service = $_POST['ServiceId']; // ��� Id ������
    $ages = (isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : "NULL");
    $phone_mob = (isset($_POST['phone_mob']) ? $_POST['phone_mob'] : "");
    $phone_home = (isset($_POST['phone_home']) ? $_POST['phone_home'] : "");
    $email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : ""); // none@wilstream

    $StatusId = (isset($_POST['StatusId']) ? $_POST['StatusId'] : STATUS_OPEN);
    $user = (isset($_POST['User']) ? $_POST['User'] : "");
    $call_back = (isset($_POST['datetimepicker']) ? $_POST['datetimepicker'] : "");
    if (STATUS_CALL_BACK == $StatusId && strlen($call_back) == 0 ) {
        echo "</br><p style='font-size: large; color: red'> ��������� <a href='javascript:history.back(1)' style='font-weight: bold; border-bottom: 1px solid '>�����.</a> �� ������� ���� � ����� ���������!</p>";
        exit;
    }
    $clinic = (isset($_POST['Clinic']) ? $_POST['Clinic'] : "");
    $surname_cl = (isset($_POST['surname_cl']) ? $_POST['surname_cl'] : "");
    $name_cl = (isset($_POST['name_cl']) ? $_POST['name_cl'] : "");
    $patronymic_cl = (isset($_POST['patronymic_cl']) ? $_POST['patronymic_cl'] : "");
    $ages_cl = (isset($_POST['ages_cl']) && $_POST['ages_cl'] != "" ? $_POST['ages'] : "NULL");

    $comment_cl = (isset($_POST['comment_cl']) ? $_POST['comment_cl'] : "");
    //echo '������: ' . $service;echo '<br />';

    echo '�������: ' . $ages . '<br />';
    echo '���������: ' . $phone_mob . '<br />';
    echo '��������: ' . $phone_home . '<br />';
    echo '����������� �����: ' . $email . '<br />';

    echo '������: ' . $StatusId . '<br />';
    if (STATUS_OPEN == $StatusId) { // ������ �������
        echo '�������.<br />';
    } else if (STATUS_WORK == $StatusId) { // ������� ���������
        echo '���������: ' . $user . '<br />';
    } else if (STATUS_CALL_BACK == $StatusId) { // ��������
        echo '���� ������: ' . $call_back . '<br />';
    } else if (STATUS_CALL_NOT == $StatusId) { // ��������
        echo '�� �����������.<br />';
    } else if (STATUS_CALL_STOP == $StatusId) { // ������ ��������
        echo '�����, ��� � �����.<br />';
    } else if (STATUS_CLINIC == $StatusId) { // �������� � �������
        echo '�������� �: ' . $clinic . '<br />';
        echo '������� �������: ' . $surname_cl . '<br />';
        echo '��� �������: ' . $name_cl . '<br />';
        echo '�������� �������: ' . $patronymic_cl . '<br />';
        echo '������� �������: ' . $ages_cl . '<br />';
    } else if ($StatusId == STATUS_NEGATIVE) { // �������
        echo '�����!<br />';
    } else if ($StatusId == STATUS_ERROR) { // ������
        echo '���-�� �� ��� � �������!!!<br />';
    } else {
        echo '!!!������!!!<br />';
    }

    //��������� ������
    $fio_cl = $surname_cl . "/" . $name_cl . "/" . $patronymic_cl; // � ���� CALL_BASE_CLINIC

    if (DB_OCI) {
        $date_call = date("d-m-Y  H:i:s");

        $updatestr = "UPDATE CALL_BASE SET AGE = {$ages}, PHONE_MOB= '{$phone_mob}', PHONE_HOME = '{$phone_home}', EMAIL =  '{$email}',
                        STATUS_ID = {$StatusId}, LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        if (STATUS_WORK == $StatusId) {
            $updatestr .= ", FIO_ID = '{$user}'"; // ���� ���������
        }
        if (STATUS_CALL_BACK == $StatusId) { // ���� �����������
            $updatestr .= ", CALL_BACK_DATE = to_date('{$call_back}','DD.MM.YYYY hh24:mi:ss'), CALL_BACK_NUM = 10 ";
        }
        if (STATUS_CALL_STOP == $StatusId || STATUS_CLINIC == $StatusId ||
            STATUS_NEGATIVE == $StatusId || STATUS_ERROR == $StatusId
        ) { // ��������� ������
            $updatestr .= ", DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        }
        $updatestr .= " WHERE ID = {$_POST['Base_Id']}";
//echo "<textarea>".$updatestr."</textarea>";
        $query = OCIParse(GetData::GetConnect(), $updatestr);
        $query_result = OCIExecute($query);

        // ��������� ������ ������ � ������� �� ����� ������.
        if (STATUS_CLINIC == $StatusId) {
            $insertstr = "INSERT INTO CALL_BASE_CLINIC ( ID, BASE_ID, HOSPITAL_ID, CLIENT_NAME, AGE ) 
                          VALUES (SEQ_CALL_BASE_CLINIC_ID.nextval, {$_POST['Base_Id']}, {$clinic}, '{$fio_cl}', {$ages_cl})";
//echo "<textarea>".$insertstr."</textarea>";
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
        }

        // ��������� ������ ������� �� ����� ������ � ID ���������� ���������
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
            <p style='color: green'>������ ������� ���������. ������������ �� ��������� ��������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ ���������� ������!</p>";
    }
    exit;
?>

</body>
</html>