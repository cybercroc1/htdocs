<?php
//этот блок должен быть на каждой странице, требующей авторизованного доступа
session_name('medc'); //устанавливает имя сессии
session_start(); //создает сессию с именем medc и кладет ее в cookies, если на компьютере уже есть кукис с таким именем сессии, то подключается к существующей

extract($_REQUEST); 

include("login_include.php"); //инклудим модель авторизации
if (!isset($work_date)) $work_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));

// Сюда попадем только после авторизации
echo '<body style="overflow: hidden; margin: 0">';
echo '<iframe src="'.PATH.'/main_menu.php" name="admMainTopFrame" id="admTopFrame" title="admTopFrame" width="100%" height="35px" frameborder="no" scrolling="no"></iframe>';
echo '<hr style="margin: 0">';
if (USER_ADMIN == $_SESSION['user_role']) {
    echo '<iframe src="'.PATH.'/index-f.php" name="admLeftFrame" id="admLeftFrame" title="admLeftFrame" width="13%" height="94%" frameborder="no" scrolling="no"></iframe>';
    echo '<iframe name="admRightFrame" id="admRightFrame" title="admRightFrame" width="87%" height="94%" frameborder="0"></iframe>';
} else {
    if (DB_OCI) {
        if (USER_VIEW == $_SESSION['user_role'] && !in_array($_SESSION['login_id_med'], SPEC_USER_VIEW))
            echo '<iframe src="'.PATH.'/call_view/med_export.php?start_date='.$work_date.'&end_date='.$work_date.'" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="94%" frameborder="no" scrolling="yes">></iframe>';
        else echo '<iframe src="'.PATH.'/call_view/med_call_view.php" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="94%" frameborder="no" scrolling-x="yes"></iframe>';
    } else {
        echo '<iframe src="'.PATH.'/call_view/med_call_view_my.php" name="admRightFrame" id="admRightFrame" title="admRightFrame" width="100%" height="94%" frameborder="no" scrolling-x="yes"></iframe>';
    }
}
echo '</body>';
