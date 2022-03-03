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
$backurl = "admin_report_acc.php";
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
    <title>Доступ к отчетам</title>
    <meta name="description" content="Доступ к отчетам">
</head>
<?php
if (isset($getdet))
{
    $_SESSION['rep_acc_id'] = $getdet;
    $sel = "<table class='white_table'>";
    $sel .= "<thead><tr style='direction: initial'>";
    $sel .= "<th style='width: 36px'>ID</th>";
    $sel .= "<th style='width: 261px'>Отчет</th>";
    $sel .= "<th style='width: 261px'>Сотрудник</th>";
    $sel .= "<th style='width: 101px'>Действие</th>";
    $sel .= "</tr></thead>";
    $sel .= "<tbody>";
    $selectstr = "SELECT cra.ID, usr.FIO as USRNAME, rep.NAME as REPORT
              FROM CALL_REPORTS_ACC cra, USERS usr, CALL_REPORTS rep 
              WHERE cra.USER_ID = usr.ID AND cra.REPORT_ID = rep.ID";
    if (/*'' != $getdet &&*/ SOURCE_ALL != $getdet)
        $selectstr .= " AND cra.REPORT_ID = " . $getdet;
        $selectstr .= " ORDER BY REPORT, USRNAME";
    $query = OCIParse(GetData::GetConnect(), $selectstr);
    $query_result = OCIExecute($query);
    if ($query_result) {
        while ($result_array = OCI_Fetch_Array($query)) {
            if (TRUE == ENCODE_UTF) {
                $result_array['REPORT'] = iconv('windows-1251', 'utf-8', $result_array['REPORT']);
                $result_array['USRNAME'] = iconv('windows-1251', 'utf-8', $result_array['USRNAME']);
            }

            $sel .= "<tr style='direction: initial'>";
            $sel .= "<td style='text-align: center; width: 35px'>" . $result_array['ID'] . "</td>";
            $sel .= "<td style='text-align: center; width: 260px'>" . $result_array['REPORT'] . "</td>";
            $sel .= "<td style='text-align: center; width: 260px'>" . $result_array['USRNAME'] . "</td>";
            $sel .= "<td style='text-align: center; width: 100px'><a href='?del_id=" . $result_array['ID'] . "'>Удалить</a></td>";
            $sel .= "</tr>";
        }
    }
    oci_free_statement($query);

    $sel .= "</tbody>";
    $sel .= "</table>";
    echo '<script>elem = parent.document.getElementById("AllTable"); if (elem) elem.innerHTML="' . $sel . '";</script>';

    if ($getdet != '' && $getdet != SOURCE_ALL) {
        echo "<script>elem = parent.document.getElementById('RightPart'); if (elem) elem.style.visibility = 'visible';</script>";
        //echo "<script>elem = parent.document.getElementById('Refreshing'); if (elem) elem.style.visibility = 'visible';</script>";
    }
    else {
        echo "<script>elem = parent.document.getElementById('RightPart'); if (elem) elem.style.visibility = 'hidden';</script>";
        //echo "<script>elem = parent.document.getElementById('Refreshing'); if (elem) elem.style.visibility = 'hidden';</script>";
    }
    exit();
}
?>
<body style="margin-top: 0;">
<form action="" method="POST">
<h3 style="margin-bottom: 0;margin-top: 0;">Доступ пользователей к отчетам</h3>
<div class="container">
    <iframe name='ifr_all' style='display: none; width: 75%'></iframe>
    <div id="LeftPart" style="float: left; width: 60%">
    <?php
    echo "<label id='ReportIdT' for='ReportId' style='font-weight: bold; font-size: 14px'>Отчет:&nbsp;</label>";
    echo "<select id='ReportId' name='ReportId' title='Отчеты' onchange='ifr_all.location=\"".PATH."/admin/admin_report_acc.php?getdet=\"+this.value'>";
//echo "<select id='ReportId' name='ReportId[]' title='Отчеты' style='font-size: 17px; background-color:".needs."' onchange='ReportChanged();'>";
    echo "<option selected=\"selected\" value=''>Выберите отчет</option>";
    echo "<option value='".SOURCE_ALL."'>Все отчеты</option>";
    if (GetData::GetReports(NULL) > 0) {
        foreach ($_POST['array_reports'] as $key => $value) {
            echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
        }
        echo "</select>";
    }
    if (isset($_SESSION['rep_acc_id']))
        echo "<script>$('#ReportId').val('" . $_SESSION['rep_acc_id'] . "').change();</script>";
    else echo "<script>$('#ReportId').val('" . SOURCE_ALL . "').change();</script>";
    ?>
    <hr>
    <div id="AllTable" style="height: 88%;overflow: auto"></div>
    <hr>
    </tbody>
    </table>
    </div>

    <div id="RightPart" class="footer" style="float: right; width: 39%">
        <?php
        if (GetData::GetUsers(FALSE, FALSE,"usr.ROLE_ID != ".USER_USER, "FIO") > 0) {
            echo "<label id='UserIdT' for='UserId' style='float: left'>&nbsp;Сотрудник:&nbsp;</label>";
            echo "<select multiple id='UserId' name='UserId[]' style='height: 92%'>";
            if (DB_OCI) {
                foreach($_POST['array_user'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = iconv ('windows-1251', 'utf-8', $value['FIO']);
                    echo "<option value='".$value['ID']."'>".$value['FIO']."</option>";
                }
            }
            else {
                foreach ($_POST['array_user'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                    echo "<option value='".$value['0']."'>".$value['1']."</option>";
                }
            }
            echo "</select>";
        }
        ?>
        <input type="submit" value="Добавить пользователя к отчету" name="Adding" class="add_button">
    </div>

    <?php
    if (isset($_GET['del_id'])) {
        $deletestr = "DELETE FROM CALL_REPORTS_ACC WHERE ID = '{$_GET['del_id']}'";
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
        <p style='color: green'>Строка удалена. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
        }
    }
    ?>
</div>
</form>

<?php function OnClick()
{
    if (isset($_POST['UserId'])) {
        $count_add = $count_exist = 0;
        foreach ($_POST['UserId'] as $key_user => $sUser) {
            $checkstr = "SELECT count(*) FROM CALL_REPORTS_ACC WHERE user_id = {$sUser} AND report_id = {$_POST['ReportId']}";
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
            if (isset($result) && $result[0] > 0) $count_exist++;

            if ( $result[0] == 0 ) { // Вставляем данные
                $insertstr = "INSERT INTO CALL_REPORTS_ACC (ID, REPORT_ID, USER_ID) VALUES (SEQ_REPORTS_ACC_ID.nextval, {$_POST['ReportId']}, {$sUser})";
                if (DB_OCI) {
                    $query = OCIParse(GetData::GetConnect(), $insertstr);
                    $query_result = OCIExecute($query);
                    oci_free_statement($query);
                } else {
                    $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
                }
                if ($query_result) $count_add++;
            }
        }

        if ($count_exist > 0) {
            echo "<p style='color: blue'>" . $count_exist . " строк уже существует.</p>";
        }
        if ($count_add > 0) {
            print "<script language='Javascript'>
                        function reload() {location = \"admin_report_acc.php\" }; setTimeout('reload()', 3000);
                        </script>";
            echo "<p style='color: green'>" . $count_add . " строк успешно добавлено в таблицу. Идет перезагрузка данных...</p>";
        } elseif ($count_exist == 0) {
            echo "<p style='color: red'>Произошла ошибка добавления записей!</p>";
        }
    }
}
if (isset($_POST['Adding']))
    OnClick();
?>

</body>
</html>