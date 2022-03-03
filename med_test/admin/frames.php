<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru" style="height: 100%">
<head style="height: 100%">
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
    <?php
//    ini_set('session.use_cookies','1');
    session_name('medc');
    session_start();
    require_once '../base.php';
    if(!isset($_SESSION['auth']) || $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])
    || (!in_array($_SESSION['login_id_med'],COST_EDIT))) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}

    extract($_REQUEST);
    ?>
    <title>Входящие маршруты</title>
</head>
<body style="height: 99%; margin: 0;">
<?php if (isset($page) && $page == 1) { ?>
    <iframe name="fr_source_cost_list" id="fr_source_cost_list" title="Список поставщиков" width="25%"
        height="75%" frameborder="0" src='admin_supplier.php'></iframe>
    <iframe name="fr_supplier_hist" id="fr_supplier_hist" title="История пополнения баланса" width="74%"
        height="75%" frameborder="0" src='blank_page.php'></iframe>
    <iframe name="fr_source_cost_edit" id="fr_source_cost_edit" title="Редактирование поставщиков" width="100%"
        height="26%" frameborder="0" src='blank_page.php'></iframe>
<?php } else { ?>
    <iframe name="fr_source_cost_list" id="fr_source_cost_list" title="Список источников" width="100%"
            height="80%" frameborder="0" src='source_auto_cost.php'></iframe>
    <iframe name="fr_source_cost_edit" id="fr_source_cost_edit" title="Редактирование источников" width="100%"
            height="20%" frameborder="0" src='blank_page.php'></iframe>
<?php } ?>
</body>
</html>