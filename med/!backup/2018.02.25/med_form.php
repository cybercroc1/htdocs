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
$backurl="med_call.php"; // �� ����� ��������� ���������� ����� ������������

//---------------------------------------------------------------------- //
//$_SERVER;

//print_r($GLOBALS);

// ��������� ������ �� �����
$purpose = (isset($_POST['PurposeId']) ? $_POST['PurposeId'] : -1); // ��� Id ���� ������
$voice = (isset($_POST['voice']) ? $_POST['voice'] : 1); // �������, ��� ���������
if ($purpose == 1) {
    $service = (isset($_POST['ServiceId']) ? $_POST['ServiceId'] : -1); // ��� Id ������
    $reservoir = (isset($_POST['Reservoir']) ? $_POST['Reservoir'] : -1); // ��� Id ���������
    $sources = (isset($_POST['sources']) ? $_POST['sources'] : ""); // ������ ��������
    $source_man_det = -1;

    $comment = (isset($_POST['comment']) ? $_POST['comment'] : "");
    $surname = (isset($_POST['surname']) ? $_POST['surname'] : "");
    $name = (isset($_POST['name']) ? $_POST['name'] : "");
    $patronymic = (isset($_POST['patronymic']) ? $_POST['patronymic'] : "");
    $ages = (isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : "NULL");
    $phone_mob = (isset($_POST['phone_mob']) ? $_POST['phone_mob'] : "");
    $phone_home = (isset($_POST['phone_home']) ? $_POST['phone_home'] : "");
    $email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : "");
    $ResultId = (isset($_POST['ResultId']) ? $_POST['ResultId'] : RESULT_WAIT);
    $Result_det = "";
    $call_center = (isset($_POST['call_center']) ? $_POST['call_center'] : "");
    $clinic = (isset($_POST['Clinic']) ? $_POST['Clinic'] : "");
}
else {
    $service = "NULL"; $reservoir = "NULL"; $sources = "NULL"; $source_man_det = "NULL";
    $comment = ""; $surname = ""; $name = ""; $patronymic = ""; $ages = "NULL";
    $phone_mob = ""; $phone_home = ""; $email = "";
    $ResultId = "NULL"; $Result_det = "NULL"; $call_center = "NULL"; $clinic = "NULL";
}
/*if ( strlen($surname) == 0 ) {
    echo "</br>��������� <a href='javascript:history.back(1)'>�����</a>. ������� �� �������!";
}
else if ( strlen($name) == 0 ) {
    echo "</br>��������� <a href='javascript:history.back(1)'>�����</a>. ��� �� �������!";
}
else if ( strlen($patronymic) == 0 ) {
    echo "</br>��������� <a href='javascript:history.back(1)'>�����</a>. �� ������� ��������!";
}
else if (!preg_match('|^([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', strtolower($email)))
{ // ��������� ���������� e-mail
   echo "</br>��������� <a href='javascript:history.back(1)'>�����</a>. �� ������� �������� e-mail!";
}
else */
{
    echo '���� ������: ' . $purpose . '<br />';
    switch ($voice) {
        case 1:
            echo '��������� ������.<br />';
            break;
        case 2:
            echo '��������� ������.<br />';
            break;
    }
    echo '������: ' . $service . '<br />';
    echo '�������� �������: ' . $reservoir . '<br />';

    if ( $reservoir < SOURCE_2GIS ) { // ���-�� �� ������
        if (isset($_POST['DetailList'])) {
            echo '�����������: ' . $_POST['DetailList'] . '<br />';
            $source_man_det = $_POST['DetailList'];
        }
    } else { //if ($reservoir == SOURCE_OTHER) { // ������
        echo '������ ��������: ' . $sources . '<br />';
        $source_man_det = DETAILS_OTHER;
    }
    echo '�����������: ' . $comment . '<br />';
    echo '�������: ' . $surname . '<br />';
    echo '���: ' . $name . '<br />';
    echo '��������: ' . $patronymic . '<br />';
    echo '�������: ' . $ages . '<br />';
    echo '���������: ' . $phone_mob . '<br />';
    echo '��������: ' . $phone_home . '<br />';
    echo '����������� �����: ' . $email . '<br />';
    echo '���������: ' . $ResultId . '<br />';
    if ($ResultId == RESULT_KC) { // �������� � ��
        echo '����� � ��: ' . $call_center . '<br />';
        $Result_det = $call_center;
    } else if ($ResultId == RESULT_CLINIC) { // �������� � �������
        echo '�������: ' . $clinic . '<br />';
        $Result_det = $clinic;
    } else if ($ResultId == RESULT_WAIT) { // �������� � �������
        echo '���� ������. <br />';
        $Result_det = -1;
    } /*else if ($ResultId == RESULT_NOT_ANSWER) { // �������� � �������
        echo '�� �������: ';
    }*/
    else {
        echo '!!!������!!!<br />';
    }

    //��������� ������
    $status = STATUS_OPEN;
    $fio = $surname . "/" . $name . "/" . $patronymic;
    $maxstr = "SELECT MAX(ID) FROM CALL_BASE";
    $max_call = 1;
    //$maxdetail = "SELECT MAX(ID) FROM SOURCE_MAN_DETAIL";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $maxstr);
        if (OCIExecute($query)) {
			$objResult = OCI_Fetch_Row($query);
            $max_call = $objResult[0] + 1;
		}

		/*if (DETAILS_OTHER == $source_man_det) {
            $query = OCIParse(GetData::GetConnect(), $maxdetail);
            if (OCIExecute($query)) {
                $objResult = OCI_Fetch_Row($query);
                $max_id = $objResult[0] + 1;
                $source_man_det = $max_id;
            }
		    $insertfirst = "INSERT INTO SOURCE_MAN_DETAIL (ID, SOURCE_MAN_ID, NAME) VALUES ( {$max_id}, {$reservoir}, '{$sources}' )";
            $query = OCIParse(GetData::GetConnect(), $insertfirst);
            $query_result = OCIExecute($query);
		}*/

        $date_call = date("d-m-Y  H:i:s");
        if (TRUE == ENCODE_UTF) {
            $tmpstra = iconv('utf-8', 'windows-1251', $_POST['anumber']);
            $tmpstrb = iconv('utf-8', 'windows-1251', $_POST['bnumber']);
            $tmpstrc = iconv('utf-8', 'windows-1251', $_POST['sc_agid']);
        }
        else  {
            $tmpstra = $_POST['anumber'];
            $tmpstrb = $_POST['bnumber'];
            $tmpstrc = $_POST['sc_agid'];
        }
        $insertstr = "INSERT INTO CALL_BASE (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, 
                          SOURCE_AUTO_ID, SOURCE_MAN_ID, CALL_THEME_ID, CALL_TYPE_ID, SERVICE_ID, SOURCE_MAN_DET_ID, 
                          COMMENTS, CLIENT_NAME, AGE, PHONE_MOB, PHONE_HOME, EMAIL, RESULT_ID, RESULT_DET,
                          STATUS_ID, FIO_ID, DATE_CLOSE, LAST_CHANGE, CALL_BACK_DATE, CALL_BACK_NUM) 
                          VALUES ($max_call, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), 
                          '{$tmpstra}', '{$tmpstrb}', '{$tmpstrc}', {$_POST['sc_call_id']}, {$_POST['sc_project_id']},
                          {$_POST['Istochnik_auto_Id']}, {$reservoir}, {$purpose}, {$voice}, {$service}, {$source_man_det},
                          '{$comment}', '{$fio}', {$ages}, '{$phone_mob}', '{$phone_home}', '{$email}', {$ResultId}, '{$Result_det}',
                          {$status}, NULL, NULL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), NULL, NULL)";
//echo "<textarea>".$insertstr."</textarea>";
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);

// ����� ��������� ������ ������ ������� �� ����� ������ c ������ ���������
        $insertstr = "INSERT INTO CALL_BASE_HIST ( ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS ) 
                          VALUES (SEQ_CALL_BASE_HIST_ID.nextval, $max_call, '{$tmpstrc}', $status, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$comment}')";
//echo "<textarea>".$insertstr."</textarea>";
        $query = OCIParse(GetData::GetConnect(), $insertstr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
        $sql = mysqli_query(GetData::GetConnect(), $maxstr);
        if (FALSE !== $sql) {
            $result = mysqli_fetch_row($sql);
            $max_call = $result[0] + 1;
            $date_call = date("Y-m-d H:i:s");
            $insertstr = "INSERT INTO CALL_BASE (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, 
                          SOURCE_AUTO_ID, SOURCE_MAN_ID, CALL_THEME_ID, CALL_TYPE_ID, SERVICE_ID, SOURCE_MAN_DET_ID, 
                          COMMENTS, CLIENT_NAME, AGE, PHONE_MOB, PHONE_HOME, EMAIL, RESULT_ID, RESULT_DET,
                          STATUS_ID, FIO_ID, DATE_CLOSE, LAST_CHANGE, CALL_BACK_DATE, CALL_BACK_NUM)   
                          VALUES ($max_call, '{$date_call}', 
                          {$_POST['anumber']}, {$_POST['bnumber']}, {$_POST['sc_agid']}, {$_POST['sc_call_id']}, {$_POST['sc_project_id']},
                          {$_POST['Istochnik_auto_Id']}, {$reservoir}, {$purpose}, {$voice}, {$service}, {$source_man_det},
                          '{$comment}', '{$fio}', {$ages}, '{$phone_mob}', '{$phone_home}', '{$email}', {$ResultId}, {$Result_det},
                          {$status}, NULL, NULL, '{$date_call}', NULL, NULL)";
//echo "<textarea>".$insertstr."</textarea>";
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
    }

    if ($query_result) {
        print "<script language='Javascript'>
                //    function reload() {location = \"$ backurl\"}; setTimeout('reload()', 3000);
               function close_win() { window.close(); }; setTimeout('close_win()', 100);
               </script>
               <p style='color: green'>������ ������� ��������� � �������. ������������ �� ��������� ��������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ ���������� ������!</p>";
    }

    exit;
}

?>

</body>
</html>