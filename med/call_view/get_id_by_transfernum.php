<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);


if(!isset($_SESSION['login_id_med']) or (!isset($trans_num) or !isset($trans_month) or !isset($trans_month))) {
    echo "Неверный запрос заявки! Введите код перевода.";
    exit();
}

include("med/conn_string.cfg.php");
require_once '../base.php';

$trans_arr = date_parse(date("Y-m-d HH:MM"));
if (USER_ADMIN == $_SESSION['user_role']) {
    $const_str = $trans_arr['year'].'-'.$trans_month."-".$trans_day."-".$trans_num;
} else {
    $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day']."-".$trans_num;
}

$sqlstr = "SELECT ID FROM CALL_BASE WHERE TRANSFER_NUM like '" . $const_str . "'";
$query = OCIParse($c, $sqlstr);
if (OCIExecute($query)) {
    if (OCI_Fetch($query)) {
        echo 'got_base_id='.trim(OCIResult($query,"ID")).';';
    } else {
        echo 'Звонок не найден!!!';
        exit;
    }
}
