<?php
/**
 * Created by PhpStorm.
 * User: Dexp
 * Date: 17.04.2018
 * Time: 19:47
 */
session_name('medc');
session_start();

extract($_REQUEST);
require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}

if (isset($_POST['trans_num']) && NULL != $_POST['trans_num']) {
    $trans_arr = date_parse(date("Y-m-d HH:MM"));
    if (USER_ADMIN == $_SESSION['user_role']) {
        $const_str = $trans_arr['year'].'-'.$_POST['trans_month']."-".$_POST['trans_day']."-".$_POST['trans_num'];
    } else {
        $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day']."-".$_POST['trans_num'];
    }
    $sqlstr = "SELECT ID FROM CALL_BASE WHERE TRANSFER_NUM like '" . $const_str . "'";
    if (DB_OCI) {
        $query = OCIParse($c, $sqlstr);
        if (OCIExecute($query)) {
            if (NULL != ($objResult = OCI_Fetch_Row($query))) {
                echo "<script type='application/javascript'>open_edit('".$objResult[0]."','".$_SESSION['login_id_med']."','".$sid."');</script>";
            } else {
                //echo "Звонок с переводным номером ".$const_str." не найден!!!";
                echo "<script type='text/javascript'>alert('Звонок не найден!!!')</script>";
            }
        }
        oci_free_statement($query);
    }
    exit;
}