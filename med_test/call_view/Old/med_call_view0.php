<?php
//header('Refresh: 15; url=' . $_SERVER['PHP_SELF']); // автоматическая перезагрузка
ini_set('session.use_cookies','1');

//session_name('medc');
//session_start();

require_once 'med/check_auth.php';

$sid=session_id();

extract($_REQUEST);

if(!isset($find_id)) $find_id = '';
else $find_id = trim($find_id);

require_once 'med/conn_string.cfg.php';
require_once 'phone_conv_single.php';
require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}

if (file_exists("jquery.min.js")) {
    echo "<script type='text/javascript'>
        if(!window.jQuery) { 
            document.write(unescape('<script src=\"jquery.min.js\">%3C/script%3E'));
        }</script>";
}
//<script src="jquery.min.js"></script>';

date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "med_call_view.php";
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if (!isset($start_date)) $start_date=$end_date;//date('d.m.Y',mktime(0,0,0,date("m"),date("d")-7,date("Y")));
//---------------------------------------------------------------------- //
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>Входящие заявки</title>
    <meta name="description" content="Входящие заявки">
    <?php if (preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT'])) { ?>
        <script src="../js/jquery.datetimepicker.full.min.js"></script>
    <?php } else { ?>
        <script src="../js/jquery.datetimepicker.full.js"></script>
    <?php } ?>

    <style> input.send_button { cursor: url(../images/aero_pen.cur), pointer; } </style>

<script type="application/javascript">
    function make_sound(){
        $('<audio id="ListAudio"><source src="notify.mp3" type="audio/mpeg"></audio>').appendTo('body');
        $('#ListAudio')[0].play();
    }

    function table_scrolled() {
        $('#toTop').fadeIn();

        $('#toTop').click(function() {
            $('#toTop').fadeOut();
            $('body,html,div').animate({scrollTop:0},800);
        });
    }

    function add_options(obj,opt_id,opt_val,opt_selected) {
        if (obj) {
            //obj = document.getElementsByName(obj0);
            len = obj.options.length;
            obj.options[len] = new Option(opt_val, opt_id);
            if (opt_selected == 'selected') obj.options[len].selected = true;
        }
    }

	//ФУНКЦИИ ПОДСВЕТКИ СТРОК в report.js
    var sel_color='#66fffe';
    var unsel_color='white';
    var clicked_color='#cccccc';
    var clicked_sel_color='#66ccff';
    function sel_row(row) {
        for(i=0; i<row.cells.length; i++) {
            if(row.cells[i].bgColor==sel_color) {//если уже выделена, но не нажата, то ничего не делаем
                row.cells[i].bgColor=sel_color;
            }
            else if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата, то выделяем
                row.cells[i].bgColor=sel_color;
            }
            else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата, то ничего не делаем
                row.cells[i].bgColor=clicked_sel_color;
            }
            else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата, то красим в нажато-выделенный цвет
                row.cells[i].bgColor=clicked_sel_color;
            }
        }
    }
    function unsel_row(row) {
        for(i=0; i<row.cells.length; i++) {
            //alert(row.cells[i].bgColor+" - "+unsel_color);
            if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата, то ничего не делаем
                row.cells[i].bgColor=unsel_color;
            }
            else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата, то ничего не делаем
                row.cells[i].bgColor=clicked_color;
            }
            else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата, то красим в нажато-невыделенный цвет
                row.cells[i].bgColor=clicked_color;
            }
            else if(row.cells[i].bgColor==sel_color) {//если выделена и не нажата, то снимаем выделение
                row.cells[i].bgColor=unsel_color;
            }
        }
    }
    function click_row(row_id) {
        row=document.getElementById(row_id);
        for(i=0; i<row.cells.length; i++) {
            if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата
                row.cells[i].bgColor=clicked_color;
            }
            if(row.cells[i].bgColor==sel_color) {//если не выделена и не нажата
                row.cells[i].bgColor=clicked_sel_color;
            }
        }
    }
    function unclick_row(row_id) {
        row=document.getElementById(row_id);
        for(i=0; i<row.cells.length; i++) {
            row.cells[i].bgColor=unsel_color;
        }
    }
    function sel_click_row(row_id) {
        row=document.getElementById(row_id);
        for(i=0; i<row.cells.length; i++) {
            row.cells[i].bgColor=clicked_sel_color;
        }
    }
    function unsel_click_row(row_id) {
        row=document.getElementById(row_id);
        for(i=0; i<row.cells.length; i++) {
            row.cells[i].bgColor=clicked_color;
        }
    }

	//----------------------
    function open_edit(base_id, texnari_id, sid) {
        if (base_id > 0) {
            var width = 870;
            var height = 700;
            win = window.open("../med_call_out.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid, "med_call_"+base_id,
                    "width=" + width + ", height=" + height + ",left=" + ((window.innerWidth - width)/2) + ",top=30, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
            win.focus();
        }
    }
    function open_hist(sid) {
        var phone_num = document.getElementById('phone_num').value;
        if (null != phone_num) {
            var width = 850;
            var height = 750;
            win = window.open("../med_call_hist.php?phone_num="+phone_num+"&sid="+sid, "med_call_"+phone_num,
                "width=" + width + ", height=" + height + ",left=" + ((window.innerWidth - width)/2) + ",top=30, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
            win.focus();
        }
    }
    function export_screen(start_date, end_date, sid, type) {
        var width = 750;
        var height = 650;
        win = window.open("./med_export.php?start_date="+start_date+"&end_date="+end_date, "med_export_"+sid,
            "width=" + width + ", height=" + height + ",left=" + ((window.innerWidth - width)/2) + ",top=30, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
        win.focus();
    }
    function ShowTransfer(sid) {
        var trans_num = document.getElementById('trans_num').value;
        var parse_date = new Date();
        var trans_month = parse_date.getMonth();
        var trans_day = parse_date.getDay();
        if (<?=USER_ADMIN?> == <?=$_SESSION['user_role']?>) {
            trans_month = document.getElementById('trans_month').value;
            trans_day = document.getElementById('trans_day').value;
        }
        //получаем ID заявки
        xml = new window.XMLHttpRequest();
        xml.open("GET", 'get_id_by_transfernum.php?'
            +'trans_num='+trans_num+'&trans_month='+trans_month+'&trans_day='+trans_day, false);
        xml.send("");
        //alert(xml.responseText);
        var response=xml.responseText;
        var regex=/got_base_id=([^\"]*);/im;
        if(matches=response.match(regex)) {
            show_base_id = matches[1]; //alert(show_base_id);
            open_edit(show_base_id, <?=$_SESSION['login_id_med']?>, sid);
        }
        else { //ошибка получения заявки
            alert(xml.responseText);
        }
    }

    var t;
    function fn_find_id() {
        clearTimeout(t);
        if(document.getElementById('find_id').value.length==0 || document.getElementById('find_id').value.length>=3)
            t = setTimeout('document.all.ok.click()',3000);
    }
</script>
</head>

<body class='body_margin'>

<?php


if (!isset($_SESSION['on_duty_today']))
	$_SESSION['on_duty_today'] = FALSE;
echo "<div style='display: inline-table; width: 100%'>";
echo "<div style='float: left; width: 35%'>";
echo "<h3 style='margin-top: 0;margin-bottom: 0;'>Просмотр заявок";
    $q = OCIParse($c,"SELECT to_char(max(LAST_CHANGE),'DD.MM.YYYY HH24:MI') LAST_CHANGE FROM call_base");
    OCIExecute($q,OCI_DEFAULT);
    OCIFetch($q);
    echo "<span style='display: inline; color: #007fff'> / Последнее изменение:&nbsp;<span style='color: darkblue;'>". OCIResult($q, "LAST_CHANGE") ."</span></span><br/>";
    oci_free_statement($q);
echo "</h3>";

if (USER_USER != $_SESSION['user_role']) {
    echo "<input type='button' name='Export' value='Отчеты' class='send_button' style='width: 9em; height: 1.5em'
    onclick=\"javascript:export_screen('".$start_date."','".$end_date."','".$sid."',0)\" />&nbsp;";

    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'] /*&&
        (84 == $_SESSION['login_id_med'] || 11 == $_SESSION['login_id_med'])*/) { // только для Грачевой и Алибековой?
        echo '<a href="../admin/admin_source_detail_new.php" target="_blank" rel="noopener"><button name="Ist_Det_Auto" class="enter_button">Источники рекламы</button></a>';
        echo '<a href="../med_call_create.php" target="_blank" rel="noopener"><button name="Order_Create" class="enter_button">Создать заявку</button></a>';
        /*if (USER_ADMIN == $_SESSION['user_role']) {
            echo "<b style='color: darkorchid'>|&nbsp;Не обновлять</b><input type=checkbox ";
            if (isset($show_refresh)) echo "checked ";
            echo "name='show_refresh' id='show_refresh' title='Freeze'>|";
        }*/
    }
}
echo "</div>";

echo "<iframe name='RightFrame' id='RightFrame' style='display: none'> </iframe>";

if (isset($UserId) && '' != $UserId) {
    GetData::SetUserDuty($UserId, $_SESSION['login_id_med']);
}

if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
    $DutyUser = GetData::GetUserDuty(NULL, $_SESSION['login_id_med']);
    $strfilt = "(ROLE_ID = " . USER_USER . ")";
    $nUsers = GetData::GetUsersDep(FALSE, $strfilt, NULL, 'not');
    $DutyUserStr = "не найден";
    if (-1 != $DutyUser) {
        foreach (GetData::$array_userd as $key => $value) {
            if ($value['ID'] == $DutyUser) {
                $DutyUserStr = $value['FIO']; break;
            }
        }
    } else { $DutyUserStr = "не назначен"; }
    echo "<div style='display: inherit; width: 30%'>";
    echo "<form action='' method='post' target='' style='margin-bottom: 0'>";
    echo "<span style='font-weight: bold; color: crimson'>Дежурный оператор:&nbsp;</span>";
    echo "<span style='font-weight: bold; color: black'>".$DutyUserStr."</span><br/>";
    echo "<label for='UserId' style='color: crimson; font-weight: bold'>Назначить:&nbsp;</label>";
    echo "<select id='UserId' name='UserId'>";
    echo "<option value=''>Выберите оператора</option>";
    echo "<option value='-1'>Отменить дежурного</option>";
    foreach(GetData::$array_userd as $key => $value) {
        if ($DutyUser == $value['ID']) continue;
        if (TRUE == ENCODE_UTF)
            $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
        echo "<option value='" . $value['ID'] . "'>" . $value['FIO'] . "</option>";
    }
    echo "</select>";
    echo "<input type='submit' name='Adding' value='Назначить' class='add_button' style='margin-top: 0'/>";
    echo "</form>";
    echo "</div>";
}

echo "<div style='float: right; width: 30%'>";
/*if (isset($trans_num) && '' != $trans_num) {
    $trans_arr = date_parse(date("Y-m-d HH:MM"));
    if (USER_ADMIN == $_SESSION['user_role']) {
        $const_str = $trans_arr['year'].'-'.$_POST['trans_month']."-".$_POST['trans_day']."-".$trans_num;
    } else {
        $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day']."-".$trans_num;
    }

    $sqlstr = "SELECT ID FROM CALL_BASE WHERE TRANSFER_NUM like '" . $const_str . "'";
    $query = OCIParse($c, $sqlstr);
    if (OCIExecute($query)) {
        if (NULL != ($objResult = OCI_Fetch_Row($query))) {
            echo "<script type='application/javascript'>open_edit('".$objResult[0]."','".$_SESSION['login_id_med']."','".$sid."');</script>";
        } else {
            echo "<script type='text/javascript'>alert('Звонок не найден!!!')</script>";
        }
    }
    oci_free_statement($query);
    echo "<script>document.location.href=(document.location.pathname);</script>";
    //unset($_POST['trans_num']);
    exit;
}*/

/*if (isset($phone_num) && '' != $phone_num) {
    echo "<script type='application/javascript'>open_hist('".$phone_num."','".$sid."');</script>";
    echo "<script>document.location.href=(document.location.pathname);</script>";
    //unset($phone_num);
    exit;
}*/

if (USER_VIEW != $_SESSION['user_role']) {
    //echo "<div style='float: left;'><form action='' method='post' target='RightFrame' style='float: right; width: 400px; margin-bottom: 0'>";
    $trans_arr = date_parse(date("Y-m-d HH:MM"));
    //$const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'].'-'.$trans_arr['hour'];
    if (USER_ADMIN == $_SESSION['user_role']) {
        echo "<label for='trans_num' style='color: brown; font-size: 1.17em; font-weight: bold'>Код перевода:&nbsp;<span style='color: black'>".$trans_arr['year']."-</span></label>";
        echo "<input type='my_number' class='my_number' name='trans_month' id='trans_month' value='".$trans_arr['month']."' style='font-size: 1.17em; text-align: right;'>";
        echo "<label for='trans_month' style='color: black; font-size: 1.17em; font-weight: bold'>-</label>";
        echo "<input type='my_number' class='my_number' name='trans_day' id='trans_day' value='".$trans_arr['day']."' style='font-size: 1.17em'>";
    }
    else echo "<label for='trans_num' style='color: brown; font-size: 1.17em; font-weight: bold'>Код перевода:&nbsp;<span style='color: black'>".$trans_arr['year']."-".$trans_arr['month']."-".$trans_arr['day']."-&nbsp;</span></label>";

    echo "<input type='number' name='trans_num' id='trans_num' style='width: 5em; margin-top: 3px;' value='' placeholder='Код'/>";
    echo "<input type='submit' name='Adding' value='Открыть звонок' class='add_button' style='margin-top: 0' onclick=\"ShowTransfer('".$sid."')\"/>";
    //echo "<input type='submit' name='Adding' value='Открыть звонок' class='add_button' style='margin-top: 0'/>";
    //echo "</form></div><br/>";
}

//echo "<form action='' method='post' target='RightFrame' style='float: right; width: 400px; margin-bottom: 0'>"; чтоб не захламлять RightFrame
echo "<label for='phone_num' style='color: brown; font-size: 1.17em; font-weight: bold; float: left'>Поиск по телефону:&nbsp;</label>";
echo "<input name='phone_num' id='phone_num' style='width: 9.5em; margin-top: 3px;' value='' placeholder='Введите номер'/>";
echo "<input type='submit' name='History' id='History' value='Посмотреть' class='add_button' onclick=\"javascript:open_hist('".$sid."')\" style='margin-top: 0'/>";
//echo "</form>";
//echo "</frame>";
echo "</div>";
echo "</div>";

//хедер-футер. ХЕДЕР
if (!isset($_POST['trans_num']) && !isset($_POST['phone_num'])) {
    //var_dump($phone_num);
    echo "<form method='get' style='height: 90%; margin-bottom: 0'><input type=hidden name=refresh value=y>";
    echo "<table id='content_table' class='content_table' align='center' width='100%'><tr class='header_tr'><td>";
//описание переменных (если не выбран фильтр)
    if (!isset($anumber_id)) $anumber_id = '';
    if (!isset($bnumber_id)) $bnumber_id = '';
    if (!isset($project_id)) $project_id = '';
    if (!isset($agid_id)) $agid_id = '';
    if (!isset($agid_id)) $agid_id = '';
//if (!isset($ct_id)) $ct_id = '';
    if (!isset($st_id)) $st_id = '';
    if (!isset($service_id)) $service_id = '';
    if (!isset($service_det_id)) $service_det_id='';
    if (!isset($source_a_id)) $source_a_id = '';
    if (!isset($source_m_id)) $source_m_id = '';
    if (!isset($source_man_id_new)) $source_man_id_new = '';
    if (!isset($detail_id)) $detail_id = '';
//if (!isset($detail_id_new)) $detail_id_new='';
    if (!isset($texnari_id)) $texnari_id = '';
    if (!isset($stat_id)) $stat_id = '';
    if (!isset($stat_det_id)) $stat_det_id = '';
    if (!isset($res_id)) $res_id = '';
    if (!isset($mail_id)) $mail_id = '';

    $anumber_arr = array();
    $bnumber_arr = array();
    $agid_arr = array();
    $project_arr = array();
//$ct_arr = array();
    $st_arr = array();
    $service_arr = array();
//$service_det_arr = array();
    $usluga_auto_arr = array();
    $usluga_arr = array();
    $usluga_arr_new = array();
    $detail_arr = array();
//$detail_arr_new = array();
    $texnari_arr = array();
    $status_arr = array();
    $status_det_arr = array();
    $res_arr = array();

    echo "<table align='center' style='width: 99%'><tr><td>";
    echo "<nobr>Поиск: <input type=text name='find_id' id='find_id' value='" . $find_id . "' onkeyup='fn_find_id()'; onpaste='fn_find_id()'; 
    title='" . htmlspecialchars('Введите не менее 3-х символов и подождите 3 секунды. Будет выполнен поиск совпадений по всем полям.') . "'>";

    echo " Показать: <b style='color: blue'>перезвон</b><input type=checkbox ";
    if (isset($show_delayed)) echo "checked ";
    echo "name='show_delayed' id='show_delayed' onclick=ok.click() title='Весь перезвон'> | ";

    echo "<b style='color: orange'>недозвон</b><input type=checkbox ";
    if (isset($show_nedo)) echo "checked ";
    echo "name='show_nedo' id='show_nedo' onclick=ok.click() title='Весь недозвон'> | ";

    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'] || $_SESSION['on_duty_today']) {
        echo "<b style='color: green'>назначено</b><input type=checkbox ";
        if (isset($show_work)) echo "checked ";
        echo "name='show_work' id='show_work' onclick=ok.click() title='Назначенное'> | ";
    }
    echo "<b style='color: brown'>переводные</b><input type=checkbox ";
    if (isset($show_trans)) echo "checked ";
    echo "name='show_trans' id='show_trans' onclick=ok.click() title='Все переводные'> | ";

    echo "<b style='color: red'>все заявки</b><input type=checkbox ";
    if (isset($show_closed)) echo "checked ";
    echo "name='show_closed' id='show_closed' onclick=ok.click() title='Все заявки'> | ";

    echo "<b style='color: navy'>комментарии</b><input type=checkbox ";
    if (isset($show_text)) echo "checked ";
    echo "name='show_text' id='show_text' onclick=ok.click() title='Дополнительная информация'>";

    echo "<span id='blink1' style='padding-left: 1em; font-size: 11pt; margin-top: -8px; visibility: hidden; position: absolute'>Включены фильтры столбцов!</span>";
    echo "<span id='blink2' style='padding-left: 1em; font-size: 11pt; visibility: hidden'>Даты не включают сегодняшний день!</span>";
    echo "</nobr></td></tr></table>";
    echo "<script>document.getElementById('show_text').disabled=true;</script>";
    echo "<script>document.getElementById('show_nedo').disabled=true;</script>";
    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])
        echo "<script>document.getElementById('show_work').disabled=true;</script>";
    echo "<script>document.getElementById('show_trans').disabled=true;</script>";
    echo "<script>document.getElementById('show_closed').disabled=true;</script>";
    echo "<script>document.getElementById('show_delayed').disabled=true;</script>";
//Хедер-футер. КОНТЕНТ
    echo "</td></tr><tr class='content_tr'><td><div id='content_div' class='content_div' style='overflow: auto' onscroll='table_scrolled()'>";

    $theads = array(
        'cb.DATE_CALL' => array('name' => 'Дата заявки', 'width' => '182', 'vari' => 'date_call'),
        'cb.SC_AGID' => array('name' => 'Оператор', 'width' => '121', 'vari' => 'agid_id'),
        'cb.ANUMBER' => array('name' => 'АОН', 'width' => '121', 'vari' => 'anumber_id'),
        'serv.NAME' => array('name' => 'Услуга', 'width' => '81', 'vari' => 'service_id'),
        'sr_a.NAME' => array('name' => 'Источник(Авто)', 'width' => '151', 'vari' => 'source_a_id'),
        'cb.SOURCE_TYPE_ID' => array('name' => 'Тип', 'width' => '65', 'vari' => 'st_id'),
        'cb.CLIENT_NAME' => array('name' => 'Звонил', 'width' => '120', 'vari' => ''),
        'cb.PHONE_MOB_NORM' => array('name' => 'Телефон', 'width' => '120', 'vari' => ''),
        'sr_man.NAME' => array('name' => 'Источник(вход)', 'width' => '100', 'vari' => 'source_m_id'),
        'cb.SOURCE_MAN_DET_ID' => array('name' => 'Детализация(вход)', 'width' => '100', 'vari' => 'detail_id'),
        'sra_det.NAME' => array('name' => 'Источник(исх)', 'width' => '100', 'vari' => 'source_man_id_new'),
        'usr.FIO' => array('name' => 'Назначено', 'width' => '120', 'vari' => 'texnari_id'),
        'usr.SECOND_FIO' => array('name' => 'Назначено', 'width' => '120', 'vari' => 'texnari_id'),
        'cb.STATUS_ID' => array('name' => 'Статус', 'width' => '90', 'vari' => 'stat_id'),
        'cb.STATUS_DET_ID' => array('name' => 'Уточнение', 'width' => '90', 'vari' => 'stat_det_id'),
        'cb.LAST_CHANGE' => array('name' => 'Время cобытия', 'width' => '90', 'vari' => ''),
        'cb.RESULT_ID' => array('name' => 'Результат', 'width' => '90', 'vari' => 'res_id'),
        'cb.SENT_MAIL' => array('name' => 'Письма', 'width' => '60', 'vari' => 'mail_id'),
        'cb.PAY_SUPPLIER' => array('name' => '$$', 'width' => '60', 'vari' => '')
    );

    if (isset($_GET['key'])) {
        $key = $_GET['key'];
        $sort = $_GET['sort'];
    } else {
        /*if (USER_ADMIN == $_SESSION['user_role'] || USER_VIEW == $_SESSION['user_role']) {
            $key = " cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
            $sort = 'asc';
        }
        else {*/
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
                $key = " cb.DATE_CALL ";
                $sort = 'desc';
            }
            else {
                $key = " cb.STATUS_ID, cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
                $sort = 'asc';
            }
        //}
    }

    echo "<table id='scrolled_table' align='center' bgcolor='black' cellspacing=1 cellpadding=1><tr>";

    foreach ($theads as $k => $thead) {
        if ("usr.FIO" == $k && in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            continue;
        elseif ("usr.SECOND_FIO" == $k && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            continue;

        if ($k === $key) {
            $img = "../images/".$sort.".png";
            $soort = ($sort == 'desc' ? 'asc' : 'desc');
        } else {
            $img = '';
            $soort = 'asc';
        }

        $get = http_build_query(array('key' => $k, 'sort' => $soort));
        $get .= '&start_date=' . $start_date;
        $get .= '&end_date=' . $end_date;
        if (isset($show_text)) $get .= '&show_text=on';
        if (isset($show_nedo)) $get .= '&show_nedo=on';
        if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
            if (isset($show_work)) $get .= '&show_work=on';
        }
        if (isset($show_trans)) $get .= '&show_trans=on';
        if (isset($show_closed)) $get .= '&show_closed=on';
        if (isset($show_delayed)) $get .= '&show_delayed=on';
        //if (isset($show_refresh)) $get .= '&show_refresh=on';

        if ('Дата заявки' == $thead['name']) {
            echo "<th style='background-color: white; vertical-align: top; white-space: nowrap' colspan=2>
<img src='$img'><a href=\"?$get\" style='color: black'>Дата заявки<br/></a>
    c <input type='text' name='start_date' id='start_date' autocomplete='off' size=6 onchange='ok.click()' value='" . (isset($start_date) ? $start_date : '') . "' />
    по <input type='text' name='end_date' id='end_date' autocomplete='off' size=6 onchange='ok.click()' value='" . (isset($end_date) ? $end_date : '') . "' />";
            echo "<script>document.getElementById('start_date').disabled=true; document.getElementById('end_date').disabled=true;</script>";
            echo "</th>";
        } elseif ('Время cобытия' != $thead['name'] && 'Звонил' != $thead['name'] && 'Телефон' != $thead['name'] && '$$' != $thead['name']) {
            /*if ('Результат' == $thead['name'] && !isset($show_trans))
                $res_id = '';
            else*/
            if ('Уточнение' == $thead['name'] && !isset($show_closed))
                $stat_det_id = '';
            elseif ('Назначено' == $thead['name'] && USER_USER == $_SESSION['user_role'] && !$_SESSION['on_duty_today'])
                $texnari_id = '';
            elseif ('Письма' == $thead['name']) {
                if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
                    echo "<th style='background-color: white; vertical-align: top; width:{$thead['width']}px'>
                <img src='$img'><a href=\"?$get\" style='color: black'>{$thead['name']}</a>";
                    echo "<select style='width:100%' name='mail_id' id='mail_id' onchange='ok.click()'>";
                    echo "<option value='' style='color:black'>ВСЕ</option>";
                    if ('0' == $mail_id) $selected = 'selected'; else $selected = '';
                    echo "<option value='0' " . $selected . " style='color:red'>Неотправленные</option>";
                    if ('1' == $mail_id) $selected = 'selected'; else $selected = '';
                    echo "<option value='1' " . $selected . " style='color:green'>Отправленные</option>";
                    echo "</select>";
                    echo "</th>";
                    echo "<script>document.getElementById('mail_id').disabled=true;</script>";
                } else $mail_id = '';
            } elseif (USER_ADMIN != $_SESSION['user_role'] || ('Оператор' != $thead['name'] && 'АОН' != $thead['name'])) {
                echo "<th style='background-color: white; vertical-align: top; width:{$thead['width']}px'>
                <img src='$img'><a href=\"?$get\" style='color: black'>{$thead['name']}</a>";
                echo "<select style='width:100%' name={$thead['vari']} id={$thead['vari']} onchange='ok.click()'>";
                echo "<option value='' style='color:green'>ВСЕ</option>";
                echo "</select>";
                echo "</th>";
                echo "<script>document.getElementById('".$thead['vari']."').disabled=true;</script>";
            }
        } else {
            if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'] || '$$' != $thead['name'])
                echo "<th style='background-color: white; vertical-align: middle; width:{$thead['width']}px'>{$thead['name']}</th>";
        }
    }
    echo "</tr>";

    //фильтр выбора
    $q_where = " cb.CALL_THEME_ID=" . THEME_MED; // только Медицинские услуги ...
    /*if ("admin_new" == $_SESSION['login_name']) // одному админу пока только телефонные, чтобы спокойно тестировать
        $q_where .= " and cb.SOURCE_TYPE_ID=".DEVICE_PHONE;*/
    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
        if ($find_id == "")
            $q_where .= " and cb.CALL_TYPE_ID=" . CALL_SECOND; // все отобранное //$q_where .= " and rownum <= 1
    }
    elseif (USER_USER == $_SESSION['user_role']) {
        if ($_SESSION['on_duty_today'])
            $q_where .= " and cb.STATUS_ID !=" . STATUS_OPEN; // не супервизор же!
        else $q_where .= " and cb.FIO_ID=" . $_SESSION['login_id_med'];// . " and cb.CALL_TYPE_ID=" . CALL_FIRST; // только свои
    }
    if (!isset($show_closed) /*&& !isset($show_delayed)*/) { // Перезвон может быть и у закрытых заявок
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $q_where .= " and cb.DATE_SECOND_CLOSE is null";
        else $q_where .= " and cb.DATE_CLOSE is null"; // and CALL_BACK_DATE is not null ???
    }
    $q_where .= " and cb.service_id != 0 ";

    if (USER_ADMIN != $_SESSION['user_role'] && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) { // права доступа по департаментам
        $q_where .= " and (cb.source_auto_id,cb.source_man_id,cb.source_type_id,cb.service_id) in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id),
    decode(ad.source_man_id,-1,cb.source_man_id,ad.source_man_id),
    decode(ad.source_type_id,-1,cb.source_type_id,ad.source_type_id),
    decode(ad.service_id,-1,cb.service_id,ad.service_id)
     from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id 
     and uda.deleted is NULL and uda.user_id=" . $_SESSION['login_id_med'] . ") ";
    }

    $bchecked = FALSE; // выбраны ли чекбоксы
    $btop = FALSE; // используются ли фильтры верхней строки таблицы
    $q_where .= " and (";
    if (isset($show_delayed) || isset($show_nedo) || isset($show_trans) || isset($show_closed))
        $bchecked = TRUE; // чекбоксы в деле

    if (strtotime($date) < strtotime($start_date) || strtotime($date) > strtotime($end_date))
        echo "<script>document.getElementById('blink2').style.visibility = 'visible';</script>";
    else echo "<script>document.getElementById('blink2').style.visibility = 'hidden';</script>";

    if (!in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
        //if (!$_SESSION['on_duty_today'])
            $date_where = " cb.DATE_CALL between to_date('$start_date','DD.MM.YYYY') and to_date('$end_date','DD.MM.YYYY')+1";
        //else $date_where = " cb.DATE_CALL <= sysdate";
    }
    else $date_where = " cb.DATE_SECOND_CHANCE > to_date('01.08.2018','DD.MM.YYYY')";
    $q_where .= $date_where;

    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
        //if (!isset($show_delayed) && !isset($show_nedo) && !isset($show_closed)) {
            //$q_where .= " and (cb.CALL_BACK_DATE <= sysdate or cb.CALL_BACK_DATE is NULL and cb.SECOND_LAST_CHANGE+15/1440 <= sysdate)";
            $q_where .= " and (cb.CALL_BACK_DATE <= sysdate or cb.SECOND_FIO_ID is NULL)";
        //}
    } else {
        if (!isset($show_delayed) && !isset($show_nedo) && !isset($show_closed)) {
            $q_where .= " and (cb.CALL_BACK_DATE <= sysdate or cb.CALL_BACK_DATE is NULL and cb.LAST_CHANGE+15/1440 <= sysdate)";
        }
    }

    $q_where_top = "";
    if (USER_ADMIN != $_SESSION['user_role']) {
        if ($anumber_id <> "") {
            $q_where_top .= " cb.ANUMBER='" . $anumber_id . "'"; $btop = TRUE;
        }
        if ($agid_id <> "") {
            $q_where_top .= ($btop ? " and cb.SC_AGID='" : " cb.SC_AGID='") . $agid_id . "'"; $btop = TRUE;
        }
        //if ($bnumber_id <> "") { $q_where_top .= ($btop ? " and cb.BNUMBER='" : " cb.BNUMBER='") . $bnumber_id . "'"; $btop = TRUE; }
        //if ($project_id <> "") { $q_where_top .= ($btop ? " and cb.SC_PROJECT_ID='" : " cb.SC_PROJECT_ID='") . $project_id . "'"; $btop = TRUE; }
        //if ($ct_id <> "") { $q_where_top .= ($btop ? " and cb.CALL_TYPE_ID='" : " cb.CALL_TYPE_ID='") . $ct_id . "'"; $btop = TRUE; }
    }
    if ($st_id <> "") {
        $q_where_top .= ($btop ? " and cb.SOURCE_TYPE_ID='" : " cb.SOURCE_TYPE_ID='") . $st_id . "'"; $btop = TRUE;
    }
    if ($service_id <> "") {
        $q_where_top .= ($btop ? " and cb.SERVICE_ID='" : " cb.SERVICE_ID='") . $service_id . "' "; $btop = TRUE;
    }
    //if ($service_det_id <> "")  {$q_where_top .= ($btop ? " and cb.SERVICE_DET_ID='" : " cb.SERVICE_DET_ID='") . $service_det_id . "' ";  $btop = TRUE; }
    if ($source_a_id <> "") {
        $q_where_top .= ($btop ? " and cb.SOURCE_AUTO_ID='" : " cb.SOURCE_AUTO_ID='") . $source_a_id . "'"; $btop = TRUE;
    }
    if ($source_m_id <> "") {
        $q_where_top .= ($btop ? " and cb.SOURCE_MAN_ID='" : " cb.SOURCE_MAN_ID='") . $source_m_id . "'"; $btop = TRUE;
    }
    if ($source_man_id_new <> "") {
        $q_where_top .= ($btop ? " and cb.SOURCE_MAN_ID_NEW='" : " cb.SOURCE_MAN_ID_NEW='") . $source_man_id_new . "'"; $btop = TRUE;
    }
    if ($detail_id <> "") {
        $q_where_top .= ($btop ? " and cb.SOURCE_MAN_DET_ID='" : " cb.SOURCE_MAN_DET_ID='") . $detail_id . "'"; $btop = TRUE;
    }
    //if ($detail_id_new <> "") { $q_where_top .= ($btop ? " and cb.SOURCE_MAN_DET_ID_NEW='" : " cb.SOURCE_MAN_DET_ID_NEW='") . $detail_id_new . "'"; $btop = TRUE; }
    if ($texnari_id <> "") {
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $q_where_top .= ($btop ? " and cb.SECOND_FIO_ID='" : " cb.SECOND_FIO_ID='") . $texnari_id . "'";
        else $q_where_top .= ($btop ? " and cb.FIO_ID='" : " cb.FIO_ID='") . $texnari_id . "'";
        $btop = TRUE;
    }
    if ($stat_id <> "") {
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $q_where_top .= ($btop ? " and cb.SECOND_STATUS_ID='" : " cb.SECOND_STATUS_ID='") . $stat_id . "'";
        else $q_where_top .= ($btop ? " and cb.STATUS_ID='" : " cb.STATUS_ID='") . $stat_id . "'";
        $btop = TRUE;
    }
    if ($stat_det_id <> "") {
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $q_where_top .= ($btop ? " and cb.SECOND_STATUS_DET_ID='" : " cb.SECOND_STATUS_DET_ID='") . $stat_det_id . "'";
        else $q_where_top .= ($btop ? " and cb.STATUS_DET_ID='" : " cb.STATUS_DET_ID='") . $stat_det_id . "'";
        $btop = TRUE;
    }
    if ($mail_id <> "") {
        if ('0' == $mail_id)
            $q_where_top .= ($btop ? " and cb.SENT_MAIL is null" : " cb.SENT_MAIL is null");
        else $q_where_top .= ($btop ? " and cb.SENT_MAIL is not null" : " cb.SENT_MAIL is not null");
        $btop = TRUE;
    }
    if ($res_id <> "") { /*isset($show_trans) &&*/
        $q_where_top .= ($btop ? " and cb.RESULT_ID='" : " cb.RESULT_ID='") . $res_id . "'"; $btop = TRUE;
    }
//echo "<br/><textarea>".$q_where_top."</textarea>";
    if ($btop && ($bchecked || $_SESSION['on_duty_today'])) {
        $q_where .= " and " . $q_where_top;
        echo "<script>document.getElementById('blink1').style.visibility = 'visible';</script>";
    } elseif ($btop && (USER_ADMIN == $_SESSION['user_role'] || in_array($_SESSION['login_id_med'],SPEC_USER_CALL)))
        echo "<script>document.getElementById('blink1').style.visibility = 'visible';</script>";
    else echo "<script>document.getElementById('blink1').style.visibility = 'hidden';</script>";

    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) { // операторы второго дозвона
        $q_where .= " or cb.SECOND_FIO_ID=" . $_SESSION['login_id_med'];
        $q_where .= " and (cb.CALL_BACK_DATE <= sysdate and cb.SECOND_STATUS_ID <= " . STATUS_CALL_NOT . ")";
        //$q_where .= " or cb.CALL_BACK_DATE is NULL and cb.SECOND_LAST_CHANGE+15/1440 <= sysdate and cb.SECOND_STATUS_ID=".STATUS_CALL_NOT.")";
    } elseif (USER_USER == $_SESSION['user_role']) {
        $q_where .= " or cb.FIO_ID=" . $_SESSION['login_id_med'] . " and (cb.STATUS_ID=" . STATUS_WORK;
        $q_where .= " or cb.CALL_BACK_DATE <= sysdate and cb.STATUS_ID <= " . STATUS_CALL_NOT; // оставшиеся открытые
        $q_where .= " or cb.CALL_BACK_DATE is NULL and cb.LAST_CHANGE+15/1440 <= sysdate and cb.STATUS_ID=" . STATUS_CALL_NOT . ")";
    } elseif (USER_SUPER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role']) { // для всех кроме наблюдателя ???
        $q_where .= " and cb.STATUS_ID=" . STATUS_OPEN . " and cb.RESULT_ID != " . RESULT_KC;
        $q_where .= " or (cb.STATUS_ID=" . STATUS_OPEN . " and cb.RESULT_ID != " . RESULT_KC;
        $q_where .= " or cb.CALL_BACK_DATE <= sysdate and cb.STATUS_ID <= " . STATUS_CALL_NOT . " and cb.FIO_ID=" . $_SESSION['login_id_med'];
        $q_where .= " or cb.CALL_BACK_DATE is NULL and cb.LAST_CHANGE+15/1440 <= sysdate and cb.STATUS_ID=" . STATUS_CALL_NOT . " and cb.FIO_ID=" . $_SESSION['login_id_med'];
        $q_where .= ")";
    }
    if ($btop) $q_where .= " and " . $q_where_top;

    if (isset($show_closed)) { // показываем все звонки
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $q_where .= " and cb.SECOND_FIO_ID = " . $_SESSION['login_id_med'];
        elseif (USER_ADMIN == $_SESSION['user_role'])
            $q_where .= " or (cb.STATUS_ID <= " . STATUS_CLOSED . " and " . $date_where . ")"; // cb.DATE_CLOSE is not null
        else $q_where .= " or (cb.STATUS_ID < " . STATUS_CLOSED . " and " . $date_where . ")";
    } else {
        if (isset($show_delayed) && !isset($show_nedo)) { // перезвон и открытые
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                $q_where .= " or (CALL_BACK_DATE is not null and DATE_SECOND_CLOSE is null";
            else $q_where .= " or (CALL_BACK_DATE is not null and DATE_CLOSE is null";
            if ((USER_SUPER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role']) && isset($show_trans))
                $q_where .= " or cb.RESULT_ID=" . RESULT_KC . " and DATE_CLOSE is null";
            $q_where .= ")";// and " . $date_where;
        } elseif (!isset($show_delayed) && isset($show_nedo)) { // недозвон отложенный на 15 минут и открытые
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                $q_where .= " or (CALL_BACK_DATE is null and DATE_SECOND_CLOSE is null and cb.SECOND_STATUS_ID=" . STATUS_CALL_NOT;
            else $q_where .= " or (CALL_BACK_DATE is null and DATE_CLOSE is null and cb.STATUS_ID=" . STATUS_CALL_NOT;
            if ((USER_SUPER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role']) && isset($show_trans))
                $q_where .= " or cb.RESULT_ID=" . RESULT_KC . " and DATE_CLOSE is null";
            $q_where .= ")";// and " . $date_where;
        } elseif (isset($show_delayed) && isset($show_nedo)) { // перезвон, недозвон отложенный на 15 минут и открытые
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                $q_where .= " or (cb.SECOND_STATUS_ID in (".STATUS_CALL_NOT.",".STATUS_CALL_BACK.")";
            else $q_where .= " or (cb.STATUS_ID in (".STATUS_CALL_NOT.",".STATUS_CALL_BACK.")";
            $q_where .= " and ((cb.CALL_BACK_DATE is NULL or cb.CALL_BACK_DATE is not null) and cb.DATE_CLOSE is null)";
            if ((USER_SUPER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role']) && isset($show_trans))
                $q_where .= " or cb.RESULT_ID=" . RESULT_KC . " and DATE_CLOSE is null";
            $q_where .= ")";// and " . $date_where;
        } elseif (isset($show_trans)) { // переведенные в КЦ ??? or cb.RESULT_ID=" . RESULT_WAIT . "
            $q_where .= " or (cb.RESULT_ID=" . RESULT_KC . " and DATE_CLOSE is null and " . $date_where . ")";
        }
        if (isset($show_work))
            $q_where .= " or cb.STATUS_ID = " . STATUS_WORK;
    }
    if ($btop && $bchecked)
        $q_where .= " and " . $q_where_top;
    $q_where .= " )";

    if ($btop && (USER_ADMIN == $_SESSION['user_role'] || in_array($_SESSION['login_id_med'],SPEC_USER_CALL)))
        $q_where .= " and " . $q_where_top;

    if ($find_id != "") {
        $q_where .= " and (cb.ID like '%" . $find_id . "%'
         or upper(replace(cb.ANUMBER,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.BNUMBER,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.SC_AGID,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.SC_PROJECT_ID,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.CLIENT_NAME,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.PHONE_MOB,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.PHONE_MOB_NORM,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.PHONE_NEW,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.PHONE_NEW_NORM,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(cb.COMMENTS,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(serv.NAME,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(sr_a.NAME,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(sr_man.NAME,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(sra_det.NAME,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(usr.FIO,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(mlc.H_SUBJECT,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(mlc.MAIL_BODY_TEXT,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         or upper(replace(mlc.H_FROM,' ')) like '%'||upper(replace('" . $find_id . "',' '))||'%'
         ) ";
//        or upper(replace(ctt.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
//        or upper(replace(stat.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
//        or upper(replace(sr_det.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
    }
    //if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])
        //$q_where .= " or cb.STATUS_ID=" . STATUS_OPEN. "and cb.service_id != 0";

    $q_text1 = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID,
    cb.CALL_THEME_ID as THEME_ID, cb.SERVICE_ID as SRV_ID, serv.NAME as SRVNAME, cb.SERVICE_DET_ID,
    cb.SOURCE_AUTO_ID as SRA_ID, sr_a.NAME as SRANAME, sr_a.BNUMBER as SRABNUMBER, cb.SOURCE_TYPE_ID, cb.CALL_TYPE_ID as CT_ID,
    cb.SOURCE_MAN_ID as SRM_ID, sr_man.NAME as SRMNAME, cb.SOURCE_MAN_DET_ID as SRDET_ID,
    cb.SOURCE_MAN_ID_NEW AS SRM_ID_NEW, sra_det.NAME AS SRADETNAME, cb.SOURCE_MAN_DET_ID_NEW AS SRDET_ID_NEW,
    cb.STATUS_ID, cb.STATUS_DET_ID, stat_det.NAME as STATUS_DET, cb.FIO_ID as FIO_ID, usr.FIO as FIO, cb.CALL_DIRECTION,
    cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_NEW, cb.PHONE_MOB_NORM, cb.PHONE_NEW_NORM, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET, cb.COMMENTS,
    to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.CALL_BACK_DATE,'dd.mm.yyyy hh24:mi') CALL_BACK_DATE,
    cb.CALL_BACK_NUM, to_char(cb.DATE_CLOSE,'dd.mm.yyyy hh24:mi:ss') DATE_CLOSE, cb.SENT_MAIL, cb.CALL_DOUBLE, cb.INTERSTATE,
    cb.SECOND_STATUS_ID, cb.SECOND_STATUS_DET_ID, stat_det_sec.NAME as SECOND_STATUS_DET,
    cb.DATE_SECOND_CHANCE, to_char(cb.SECOND_LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') SECOND_LAST_CHANGE, cb.PAY_SUPPLIER,
    cb.SECOND_FIO_ID as SECOND_FIO_ID, usr_sec.FIO as SECOND_FIO, to_char(cb.DATE_SECOND_CLOSE,'dd.mm.yyyy hh24:mi:ss') DATE_SECOND_CLOSE,
    case when cb.SENT_MAIL is NULL then 0 else 1 end SENT_ALREADY";
    if ($find_id != "")
        $q_text1 .= ", mlc.H_SUBJECT,mlc.MAIL_BODY_TEXT, mlc.H_FROM";

    if (!in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
        $q_text1 .= ",(nvl(to_char(cb.DATE_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.LAST_CHANGE,'MMDD'),0)+
    nvl(to_char(cb.SENT_MAIL,'MMDD'),0)+
    nvl(cb.SERVICE_ID,0)+nvl(cb.SERVICE_DET_ID,0)+nvl(cb.FIO_ID,0)+nvl(cb.STATUS_ID,0)+nvl(cb.STATUS_DET_ID,0)+
    nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)+nvl(cb.SOURCE_MAN_ID_NEW,0)+nvl(cb.SOURCE_MAN_DET_ID_NEW,0)) as checksum";
    else $q_text1 .= ",(nvl(to_char(cb.DATE_SECOND_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.SECOND_LAST_CHANGE,'MMDD'),0)+
    nvl(cb.SERVICE_ID,0)+nvl(cb.SERVICE_DET_ID,0)+nvl(cb.SECOND_FIO_ID,0)+nvl(cb.SECOND_STATUS_ID,0)+nvl(cb.SECOND_STATUS_DET_ID,0)+
    nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)+nvl(cb.SOURCE_MAN_ID_NEW,0)+nvl(cb.SOURCE_MAN_DET_ID_NEW,0)) as checksum";
    if (FALSE == DEBUG_MODE) {
        $q_text2 = " FROM CALL_BASE cb ";
        $lock_table = 'CALL_BASE_LOCK';
        $lock_table_seq = 'SEQ_CALL_LOCK_ID';
    } else {
        $q_text2 = " FROM CALL_BASE_TEST cb ";
        $lock_table = 'CALL_BASE_LOCK_TEST';
        $lock_table_seq = 'SEQ_CALL_LOCK_ID_TEST';
    }
    $q_text3 = " 
    LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID 
    LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID 
    LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
    LEFT JOIN SOURCE_AUTO_DETAIL sra_det ON cb.SOURCE_MAN_ID_NEW = sra_det.ID
    LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID
    LEFT JOIN USERS usr_sec ON cb.SECOND_FIO_ID = usr_sec.ID
    LEFT JOIN MED_STATUS_DET stat_det ON cb.STATUS_DET_ID = stat_det.ID
    LEFT JOIN MED_STATUS_DET stat_det_sec ON cb.SECOND_STATUS_DET_ID = stat_det_sec.ID";
        if ($find_id != "")
            $q_text3 .= " LEFT JOIN MAIL_LEADCOLLECTOR mlc ON mlc.CALL_BASE_ID = cb.ID ";
//LEFT JOIN SOURCE_MAN sr_man_new ON cb.SOURCE_MAN_ID_NEW = sr_man_new.ID
//LEFT JOIN SOURCE_TYPE sr_t ON cb.SOURCE_TYPE_ID = sr_t.ID

    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
        $q_where .= " and (cb.ID not in (select base_id from " . $lock_table . " where user_id != {$_SESSION['login_id_med']}) 
        or cb.ID in (select base_id from " . $lock_table . " where lock_date_end is not null and user_id = {$_SESSION['login_id_med']}))";
        if (isset($show_closed) && $find_id == "") {
            $q_where .= " or SECOND_FIO_ID = " . $_SESSION['login_id_med'];
            if ($btop ) $q_where .= " and " . $q_where_top;
        }
    }
    $q_text4 = $q_where;
    $q_text5 = " ORDER BY " . $key . " " . $sort;
    $sort = ($sort == 'desc' ? 'asc' : 'desc');

    $q_text = $q_text1 . $q_text2 . $q_text3 . " WHERE " . $q_text4 . $q_text5;

    $_SESSION['refresh_where_med'] = $q_text2 . $q_text3 . " WHERE " . $q_text4; //эта переменная нужна для автоматического обновления окна со звонками

//if (TRUE == DEBUG_MODE || USER_ADMIN == $_SESSION['user_role'] || 8 == $_SESSION['login_id_med'])  echo "<textarea>" . $q_text . "</textarea>";

    $q = OCIParse($c, $q_text);
    //файлы, примечания
    if (isset($show_text)) {
        //$q_files = OCIParse($c,"select id,filename from MED_FILES where base_id=:base_id and tmp is null and hist_id is null order by filename");
        $sqlstr = "SELECT to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET_C, STATUS_ID, stat.NAME, OPERATOR || usr.FIO as FIO, COMMENTS 
 FROM CALL_BASE_HIST hist
 LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
 LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID
 WHERE hist.BASE_ID=:base_id ORDER BY DATE_DET";
        $q_comment = OCIParse($c, $sqlstr);
    }
    // статусы
    $q_stat = OCIParse($c, "select name, color from MED_STATUS where id=:id");
    // детализация услуги
    $q_serv_det = OCIParse($c, "select name, service_id from SERVICE_DET where id=:id");

    $locked_row = $row_num = $checksum = 0;
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $tmp_base_id = OCIResult($q, "ID");
        //$second_last_change = OCIResult($q, "SECOND_LAST_CHANGE");
        $second_fio_id = OCIResult($q, "SECOND_FIO_ID");
        $checksum += OCIResult($q, "CHECKSUM");
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
            if (NULL == $second_fio_id) {
                if ($locked_row > 0) continue; //break; // чтобы загрузить только одну свободную запись, но $checksum совпал
                $locked_row++;
                //$q_lock = "select ID from " . $lock_table . " where base_id = ".$tmp_base_id." and user_id = ".$_SESSION['login_id_med']." and lock_date_end is null ";
                $q_lock = "select ID from " . $lock_table . " where base_id = ".$tmp_base_id; // проверяем работает ли уже кто-то с заявкой
                $query_lock = OCIParse($c, $q_lock);
                OCIExecute($query_lock, OCI_DEFAULT);
                if (!OCI_Fetch_Array($query_lock)) {
                    $q_lock = "insert into " . $lock_table . " (id, base_id, user_id, lock_date_start) 
                    VALUES (".$lock_table_seq.".NEXTVAL,".$tmp_base_id.", ".$_SESSION['login_id_med'].", sysdate)";
                      //returning  lock_date_start into :sec_chance";
                    GetData::my_log($q_lock, FALSE);
                    $query_lock = OCIParse($c, $q_lock);
                    //OCIBindByName($query_lock,":sec_chance",$sec_chance,19);
                    if (OCIExecute($query_lock, OCI_COMMIT_ON_SUCCESS)) {
                        if (FALSE == DEBUG_MODE) {
                            $table_name = 'CALL_BASE';
                            $table_hist = 'CALL_BASE_HIST';
                            $seq_hist = 'SEQ_CALL_BASE_HIST_ID.nextval';
                        } else {
                            $table_name = 'CALL_BASE_TEST';
                            $table_hist = 'CALL_BASE_HIST_TEST';
                            $seq_hist = 'SEQ_CALL_BASE_HIST_ID_TEST.nextval';
                        }
                        // Пишем в базу точное время начала второго шанса
                        $updatestr = "UPDATE ".$table_name." SET DATE_SECOND_CHANCE = sysdate, SECOND_LAST_CHANGE = sysdate WHERE ID = ".$tmp_base_id;
                        GetData::my_log($updatestr, FALSE);
                        $query = OCIParse($c, $updatestr);
                        if (!OCIExecute($query))
                            GetData::my_log($updatestr, TRUE);

                        // Пишем в историю, что оператор назначил сам себе
                        $full_comment = "(fio_id=".$_SESSION['login_id_med'].") второй шанс";
                        $insertstr = "INSERT INTO " . $table_hist . " (ID, BASE_ID, USER_ID, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
                        VALUES (".$seq_hist.",".$tmp_base_id.",".$_SESSION['login_id_med'].",".STATUS_WORK.",sysdate,'".$full_comment."',sysdate)";
                        //VALUES (".$seq_hist.",".$tmp_base_id.",".$_SESSION['login_id_med'].",".STATUS_WORK.",'".$sec_chance."','".$full_comment."','".$sec_chance."')";
                        GetData::my_log($insertstr, FALSE);
                        $query = OCIParse($c, $insertstr);
                        if (!OCIExecute($query))
                            GetData::my_log($insertstr, TRUE);
                        //else OCICommit($c);
                    }
                    else GetData::my_log($q_lock, TRUE);
                }
            }
        }

        $row_num++;

        //статусы
        $call_double = OCIResult($q, "CALL_DOUBLE");
        if (!in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
            $status_id = OCIResult($q, "STATUS_ID");
            $status_det_id = OCIResult($q, "STATUS_DET_ID");
            $status_det_name = OCIResult($q, "STATUS_DET");
        }
        else {
            $status_id = OCIResult($q, "SECOND_STATUS_ID");
            $status_det_id = OCIResult($q, "SECOND_STATUS_DET_ID");
            $status_det_name = OCIResult($q, "SECOND_STATUS_DET");
        }
        OCIBindByName($q_stat, ":id", $status_id);
        OCIExecute($q_stat, OCI_DEFAULT);
        OCIFetch($q_stat);
        $status_name = OCIResult($q_stat, "NAME");

        $serv_det_id = OCIResult($q,"SERVICE_DET_ID");
        if (isset($serv_det_id) && 0 != $serv_det_id) {
            OCIBindByName($q_serv_det, ":id", $serv_det_id);
            OCIExecute($q_serv_det, OCI_DEFAULT);
            OCIFetch($q_serv_det);
            $serv_det_name = OCIResult($q_serv_det, "NAME");
        }
        else $serv_det_name = '';


        $ct_id = OCIResult($q, "CT_ID");
        $source_type_id = OCIResult($q, "SOURCE_TYPE_ID");
        $source_id = OCIResult($q, "SRM_ID");
        $source_id_new = OCIResult($q, "SRM_ID_NEW");

        // Название Детализации из разных баз
        $detailed_id = OCIResult($q, "SRDET_ID");
        $nrows = GetData::GetSourceDetail(TRUE, NULL, $source_id);
        if ($source_id < SOURCE_2GIS || $nrows > 0) { // у остальных списка нет if ($source_id < SOURCE_2GIS)
            if (DETAILS_AMNESY == $detailed_id) {
                $detail_name = "Не помнит";
            } elseif (DETAILS_PROMO == $detailed_id) {
                $detail_name = "На улице у промоутера";
            } elseif (DETAILS_OTHER == $detailed_id) {
                $detail_name = "Другое";
            } else {
                if (SOURCE_FLAER == $source_id || SOURCE_CATALOG == $source_id ||
                    SOURCE_FLAER_SUB == $source_id || SOURCE_FLAER_CAR == $source_id ||
                    SOURCE_LIFT == $source_id || SOURCE_STOP == $source_id) {
                    $q_detail_det = OCIParse($c, "SELECT 'м.'||NAME as NAME FROM SUBWAYS WHERE ID=:id");
                } elseif (SOURCE_SERT == $source_id) {
                    $q_detail_det = OCIParse($c, "SELECT 'Клиника '||NAME as NAME FROM HOSPITALS WHERE ID=:id");
                } else {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
                }
                OCIBindByName($q_detail_det, ":id", $detailed_id);
                OCIExecute($q_detail_det, OCI_DEFAULT);
                OCIFetch($q_detail_det);
                if (SOURCE_RAIL == $source_id)
                    $detail_name = "ст." . OCIResult($q_detail_det, "NAME");
                else $detail_name = OCIResult($q_detail_det, "NAME");
            }
        } else {
            $detail_name = "---";
        }

        /*$detailed_id_new = OCIResult($q,"SRDET_ID_NEW");
        if ($source_id_new < SOURCE_2GIS) { // у остальных списка детализации нет
            if (DETAILS_AMNESY == $detailed_id_new) {
                $detail_name_new = "Не помнит";
            } elseif (DETAILS_PROMO == $detailed_id_new) {
                $detail_name_new = "На улице у промоутера";
            } elseif (DETAILS_OTHER == $detailed_id_new) {
                $detail_name_new = "Другое";
            } else {
                if (SOURCE_FLAER == $source_id_new || SOURCE_CATALOG == $source_id_new ||
                    SOURCE_FLAER_SUB == $source_id_new || SOURCE_FLAER_CAR == $source_id_new ||
                    SOURCE_LIFT == $source_id_new || SOURCE_STOP == $source_id_new
                ) {
                    $q_detail_det_new = OCIParse($c, "SELECT 'м.'||NAME as NAME FROM SUBWAYS WHERE ID=:id");
                } elseif (SOURCE_SERT == $source_id_new) {
                    $q_detail_det_new = OCIParse($c, "SELECT 'Клиника '||NAME as NAME FROM HOSPITALS WHERE ID=:id");
                } else {
                    $q_detail_det_new = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
                }
                OCIBindByName($q_detail_det_new, ":id", $detailed_id_new);
                OCIExecute($q_detail_det_new, OCI_DEFAULT);
                OCIFetch($q_detail_det_new);
                if (SOURCE_RAIL == $source_id_new)
                    $detail_name_new = "ст.".OCIResult($q_detail_det_new, "NAME");
                else $detail_name_new = OCIResult($q_detail_det_new, "NAME");
            }
        }
        else {
            $detail_name_new = "---";
        }*/

        $anumber_text = OCIResult($q, "ANUMBER");
        if (NULL == $anumber_text) $anumber_text = "-----";
        if (!isset($anumber_arr[$anumber_text])) {
            $anumber_arr[$anumber_text] = $anumber_text;
        }
        $bnumber_text = OCIResult($q, "BNUMBER");
        if (!isset($bnumber_arr[$bnumber_text])) {
            $bnumber_arr[$bnumber_text] = $bnumber_text;
        }
        $agid_text = OCIResult($q, "SC_AGID");
        if (NULL == $agid_text) $agid_text = "-----";
        if (!isset($agid_arr[$agid_text])) {
            $agid_arr[$agid_text] = $agid_text;
        }
        if (!isset($project_arr[OCIResult($q, "SC_PROJECT_ID")])) {
            $project_arr[OCIResult($q, "SC_PROJECT_ID")] = OCIResult($q, "SC_PROJECT_ID");
        }
        /*if (!isset($ct_arr[OCIResult($q,"CT_ID")])) {
            $ct_arr[OCIResult($q, "CT_ID")] = OCIResult($q, "CT");
        }*/
        if ($source_type_id != NULL) {
            $source_type_name = DEVICES[$source_type_id];
            if (!isset($st_arr[$source_type_id])) {
                $st_arr[$source_type_id] = $source_type_name;
            }
        } else $source_type_name = "?????";
        $serv_id = OCIResult($q, "SRV_ID");
        $serv_name = OCIResult($q, "SRVNAME");
        if (SERVICE_NOT != $serv_id && !isset($service_arr[$serv_id])) {
            $service_arr[$serv_id] = $serv_name;
        }
        if ($serv_det_name) $serv_name .= " (".$serv_det_name.")";

        if (!isset($usluga_auto_arr[OCIResult($q, "SRA_ID")])) {
            $usluga_auto_arr[OCIResult($q, "SRA_ID")] = OCIResult($q, "SRANAME");
        }
        if (isset($source_id) && $source_id != SOURCE_NOT && !isset($usluga_arr[$source_id])) {
            $usluga_arr[$source_id] = OCIResult($q, "SRMNAME");
        }
        if (isset($source_id_new) && $source_id_new != SOURCE_NOT && !isset($usluga_arr_new[$source_id_new])) {
            //$usluga_arr_new[$source_id_new] = OCIResult($q, "SRMNAME_NEW");
            $usluga_arr_new[$source_id_new] = OCIResult($q, "SRADETNAME");
        }
        //if (isset($source_id) && $source_id < SOURCE_2GIS) { // у остальных списка детализации нет
        if (isset($detailed_id) && !isset($detail_arr[$detailed_id]) && $detailed_id != 0) {
            $detail_arr[$detailed_id] = $detail_name; //OCIResult($q, "SRDETNAME");
        }
        //}
        /*if (isset($source_id_new) && $source_id_new < SOURCE_2GIS) { // у остальных списка детализации нет
            if (isset($detailed_id_new) && !isset($detail_arr_new[$detailed_id_new]) && $detailed_id_new != 0) {
                $detail_arr_new[$detailed_id_new] = $detail_name_new; //OCIResult($q, "SRDETNAME_NEW");
            }
        }*/

        if (!isset($status_arr[$status_id]))
            $status_arr[$status_id] = $status_name;
        if (isset($status_det_id) && !isset($status_det_arr[$status_det_id]) && $status_det_id != 0)
            $status_det_arr[$status_det_id] = $status_det_name;

        //собираем список операторов
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
            $fio_id = OCIResult($q, "SECOND_FIO_ID");
            if (OCIResult($q, "SECOND_FIO")) {
                if (NULL != $fio_id && !isset($texnari_arr[$fio_id]))
                    $texnari_arr[$fio_id] = OCIResult($q, "SECOND_FIO");
            }
        }
        else {
            $fio_id = OCIResult($q, "FIO_ID");
            if (OCIResult($q, "FIO")) {
                if (!isset($texnari_arr[$fio_id]))
                    $texnari_arr[$fio_id] = OCIResult($q, "FIO");
            }
        }

        $result_id = OCIResult($q, "RESULT_ID");
        $res_det_id = OCIResult($q, "RESULT_DET");
        if (/*isset($show_trans) &&*/
            NULL != $result_id) { // результат входящего звонка
            if (!isset($res_arr[$result_id]))
                $res_arr[$result_id] = CALL_RES[$result_id];
        }

        $cbd = OCIResult($q, "CALL_BACK_DATE");
        if (STATUS_OPEN == $status_id && RESULT_KC == $result_id)
            $status_color = "brown";
        elseif (STATUS_CALL_NOT == $status_id && $cbd != '')
            $status_color = "#218aca"; // 23a504
        else $status_color = OCIResult($q_stat, "COLOR");

        if ("grey" == $status_color)
            $status_color .= ";text-decoration: line-through";
        if (CALL_SECOND == $ct_id && NULL == $second_fio_id)
            $status_color .= ";text-decoration: underline";

        if (USER_VIEW != $_SESSION['user_role'] || in_array($_SESSION['login_id_med'], SPEC_USER_VIEW)) {
            $onclick = "onclick=\"javascript:open_edit('" . $tmp_base_id . "','" . $fio_id . "','" . $sid . "')\"";
        } else {
            $onclick = '';
        }

        if ($status_id >= STATUS_CALL_STOP) {
            //$mess_subj = "Заявка ".$tmp_base_id." - ".OCIResult($q, "SRANAME").".".
            $mess_subj = "Заявка: " . $tmp_base_id . ". Маршрутный номер: " . (DEVICE_PHONE == $source_type_id ? $bnumber_text : $source_type_name);

            $body = "ID заявки: " . $tmp_base_id . chr(13) .
                "\nДата заявки: " . OCIResult($q, "DATE_CALL") . chr(13) .
                "\nСтатус заявки: " . $status_name . chr(13);
            if (STATUS_ERROR == $status_id)
                "\nПричина ошибки: " . $status_det_name . chr(13);
            $body .= " " . chr(13);
            $body .= "\nИсточник рекламы(Авто): " . OCIResult($q, "SRANAME") . chr(13) .
                "\nИсточник рекламы(Ручной): " . OCIResult($q, "SRMNAME");
            if (isset($detail_name) && "NULL" != $detail_name && "---" != $detail_name)
                $body .= " (" . $detail_name . ")";
            $body .= chr(13);
            $body .= " " . chr(13);
            $body .= "\nУслуга: " . $serv_name . chr(13);
            $body .= " " . chr(13);
            $body .= "\nТип источника: " . $source_type_name . chr(13);
            if (DEVICE_PHONE == $source_type_id) { //(Стародубов) данная информация отображается только при телефонном типе источника
                $body .= "\nНаправление звонка: " . CALL_WAY[OCIResult($q, "CALL_DIRECTION")] . chr(13) .
                    "\nМаршрутный номер: " . $bnumber_text . chr(13) . //(Стародубов) Понятие номер доступа - устаревшее в данном случае номер "Б" - это маршрутный номер
                    "\nАОН: " . $anumber_text . chr(13) .
                    "\nОператор: " . $agid_text . chr(13);
            }
            $body .= " " . chr(13);
            $body .= "\nФИО: " . OCIResult($q, "CLIENT_NAME") . chr(13) .
                "\nТелефон: " . OCIResult($q, "PHONE_MOB") . chr(13); //(Стародубов) здесь, как договорились, надо брать нормализованный номер и прогонять его через функцию phone_segment($phone_norm) из файла test_mailto.php
            if (RESULT_KC == $result_id)
                $body .= "\nСоединили с: " . $res_det_id . chr(13);
            $body .= "\nКомментарий:\n" . OCIResult($q, "COMMENTS"); //.chr(13)."."; (Стародубов) перевод строки и точка тут не нужны, эта последовательность нужна только в SMTP-диалоге для завершения передачи данных.
            //echo $body;
        } else {
            $body = '';
            $mess_subj = '';
        }

        echo "<tr id='row_" . $tmp_base_id . "' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)'>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; width: 35px; color:" . $status_color . ";'>" . $tmp_base_id . "</td>";

        if (isset($show_text)) {
            echo "<td " . $onclick . " bgcolor=white style='white-space: normal; color:" . $status_color . ";'";
            if (USER_ADMIN != $_SESSION['user_role'])
                echo " colspan=3>";
            else echo " colspan=1>";
            echo "Дата: <b>" . OCIResult($q, "DATE_CALL") . "</b><br/>";
            if (DEVICE_PHONE == $source_type_id) {
                echo "АОН: <b>" . $anumber_text . "</b>";
                echo "; BNumber: <b>" . $bnumber_text . "</b><br/>";
                echo "Оператор: <b>" . $agid_text . "</b>";
                echo "; Проект: <b>" . OCIResult($q, "SC_PROJECT_ID") . "</b>";
                echo "; ID&nbsp;звонка: <b>" . OCIResult($q, "SC_CALL_ID") . "</b><br/>";
            }
            OCIBindByName($q_comment, ":base_id", $tmp_base_id);
            OCIExecute($q_comment);
            echo "Комментарии:";
            while (OCIFetch($q_comment)) {
                $comment_cl = OCIResult($q_comment, "COMMENTS");
                if (strstr($comment_cl, "c_b=")) // статус может быть любой, кроме Назначено STATUS_WORK
                    $comment_cut = str_replace("c_b=", "", $comment_cl);
                elseif (STATUS_WORK == OCIResult($q_comment, "STATUS_ID")) {
                    $oper_id = substr($comment_cl, 8, stripos($comment_cl, ')') - 8);
                    if ($oper_id && GetData::GetUsers(TRUE, TRUE, "usr.ID = " . $oper_id, "FIO") > 0) {
                        $substr_name = GetData::$array_user[0]['FIO'];
                        $comment_cut = str_replace("fio_id=" . $oper_id, "$substr_name", $comment_cl);
                    } else $comment_cut = str_replace("fio_id=", "", $comment_cl);
                } else $comment_cut = $comment_cl;
                echo "<hr style='margin: 0'>" . OCIResult($q_comment, "DATE_DET_C");
                if (STATUS_OPEN != OCIResult($q_comment, "STATUS_ID"))
                    echo "; " . OCIResult($q_comment, "FIO");
                echo " => " . OCIResult($q_comment, "NAME");
                if ($comment_cut)
                    echo "<br/>|" . $comment_cut . "|";
            }
            //файлы
            /*OCIBindByName($q_files,":base_id",$tmp_base_id);
            OCIExecute($q_files);
            $f=0; while(OCIFetch($q_files)) { $f++;
                if($f==1) { echo "<hr>Файлы: "; }
                echo "<a href='http://med.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
            }*/
            echo "</td>";
        } else {
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "DATE_CALL") . '<br/>' . OCIResult($q, "DATE_SECOND_CHANCE") . "</td>";
            else echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "DATE_CALL") . "</td>";
            if (USER_ADMIN != $_SESSION['user_role']) {
                //Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>".nl2br(htmlentities(OCIResult($q,"TRBL_NAME")))."</td>";
                echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . $agid_text . "</td>";
                echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . $anumber_text . "</td>";
                //echo "<td bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . $bnumber_text . "</td>"; // KTO
                //echo "<td bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "SC_PROJECT_ID") . "</td>";
            }
        }
        /*if (USER_ADMIN != $_SESSION['user_role']) {
            echo "<td bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "CT") . "</td>";
        }*/
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . $serv_name . "</td>";
        /*if ($serv_det_id) //echo "<td bgcolor=white style='text-align: center; color:".$status_color.";'>".$serv_det_name."</td>";
        else echo "<td bgcolor=white style='text-align: center; color:".$status_color.";'>---</td>";*/
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "SRANAME") . "</td>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . $source_type_name . "</td>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "CLIENT_NAME") . "</td>";
        $phone_text = phone_segment(trim(OCIResult($q, "PHONE_MOB_NORM")),NULL);
        if (strlen($phone_text) == 0)
            $phone_text = OCIResult($q, "PHONE_MOB");
        $interstate = OCIResult($q, "INTERSTATE");
        if (isset($interstate) && 1 == $interstate)
            $phone_text .= " (Межгород)";
        if (RESULT_AON == $result_id) $phone_text .= " (АОН)";
        if (STATUS_CALL_NOT == $status_id)
            echo "<td " . $onclick . " bgcolor=white style='text-align: center; color: black; font-weight: bold; font: caption;'>" . $phone_text . "</td>";
        else echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . "; font-weight: bold; font: caption;'>" . $phone_text . "</td>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "SRMNAME") . "</td>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>$detail_name</td>";
        echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "SRADETNAME") . "</td>";
        //echo "<td ".$onclick." bgcolor=white style='text-align: center; color:".$status_color.";'>".OCIResult($q,"SRMNAME_NEW")."</td>";
        //echo "<td ".$onclick." bgcolor=white style='text-align: center; color:".$status_color.";'>$detail_name_new</td>";
        if (USER_USER != $_SESSION['user_role'] || $_SESSION['on_duty_today']) {
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
                echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "SECOND_FIO") . "</td>";
            } else {
                echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>" . OCIResult($q, "FIO") . "</td>";
            }
        }
        echo "<td " . $onclick . " bgcolor=white style='text-align: center;";
        if (STATUS_CLINIC_NOT == $status_id)
            echo " color:" . $status_color . ";'>Отказ от записи";
        elseif (STATUS_CALL_NOT == $status_id) {
            if ($cbd != '')
                echo " color:" . $status_color . ";'>Перезвонить";
            else echo " color:" . $status_color . ";'>Недозвон";
        }
        else {
            echo " color:" . $status_color . ";'>" . $status_name;
        }
        if (CALL_SECOND == $call_double) echo " (Дубль)";
        if (RESULT_KC == $result_id) echo " (перевод)";
        echo "</td>";
        if (isset($show_closed))
            echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>$status_det_name</td>";

        echo "<td " . $onclick . " bgcolor=white style='text-align: center; font-weight: bold; font: caption;";
        if ($cbd != '') { //STATUS_CALL_BACK == $status_id
            echo " color:" . $status_color . ";'>" . $cbd . "</td>";
        } else {
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                echo " color:" . $status_color . ";'>" . OCIResult($q, "SECOND_LAST_CHANGE") . "</td>";
            else echo " color:" . $status_color . ";'>" . OCIResult($q, "LAST_CHANGE") . "</td>";
        }

        //if (isset($show_trans)) { // переводные
        echo "<td " . $onclick . " bgcolor=white style='text-align: center;";
        if (RESULT_WAIT == $result_id || RESULT_AON == $result_id)
            echo " color:" . $status_color . ";'>" . CALL_RES[$result_id] . "</td>";
        elseif (RESULT_CLINIC == $result_id) {
            $q_res_det = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
            OCIBindByName($q_res_det, ":id", $res_det_id);
            OCIExecute($q_res_det, OCI_DEFAULT);
            OCIFetch($q_res_det);
            $clinic_name = OCIResult($q_res_det, "NAME");
            echo " color:" . $status_color . ";'>" . CALL_RES[$result_id] . "<br/>(" . $clinic_name . ")</td>";
        } else echo " color:" . $status_color . ";'>" . CALL_RES[$result_id] . "<br/>(" . $res_det_id . ")</td>";
        //}

        if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
            //echo "<td onclick='ifr2.location=\"../med_call_out.php?send_mail=\"+$tmp_base_id' bgcolor=white style='text-align: center; color:" . $status_color . "'>";
            echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>";
            if ($status_id >= STATUS_CALL_STOP || CALL_SECOND == $call_double || isset($interstate) && 1 == $interstate) {
                if (NULL == OCIResult($q, "SENT_MAIL"))
                    $envelop = 'mail.png';
                else $envelop = 'envelope.png';

                //echo "<a href='mailto:?subject=".rawurlencode(iconv("Windows-1251","utf-8",$mess_subj))."&body=".rawurlencode(iconv("Windows-1251","utf-8",$body))."'><img src='../images/mail.png'></a>";
                //Печально, но оказалось, что URL может содержать только 2152 символа, поэтому от URL-инкодинга приходится отказаться
                //поэтому делаем так: (в аутлуке работает, а на остальное пофиг)
                $tmp_mailto_url = "mailto:?subject=" . str_replace(array(chr(13), chr(10)), array("%0D", ""), $mess_subj) . "&body=" . str_replace(array(chr(13), chr(10)), array("%0D", ""), $body);
                //$tmp_mailto_url="mailto:?subject=11&body=".str_replace(array(chr(13),chr(10)),array("%0D",""),$body);

                $tmp_mailto_url = substr($tmp_mailto_url, 0, 2152);
                //echo "<a href='".$tmp_mailto_url."'><img src='../images/".$envelop."'></a>";
                echo "<img src='../images/" . $envelop . "'>";
            }
            echo "</td>";
            $sum_pay = OCIResult($q,"PAY_SUPPLIER");
            if (isset($sum_pay) && $sum_pay == 0) $str_pay = '0';
            elseif (isset($sum_pay) && $sum_pay > 0) $str_pay = '$$';
            else $str_pay = '-';
            echo "<td " . $onclick . " bgcolor=white style='text-align: center; color:" . $status_color . ";'>".$str_pay."</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    OCIFreeStatement($q);
    echo "<script>document.getElementById('show_text').disabled=false;</script>";
    echo "<script>document.getElementById('show_nedo').disabled=false;</script>";
    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])
        echo "<script>document.getElementById('show_work').disabled=false;</script>";
    echo "<script>document.getElementById('show_trans').disabled=false;</script>";
    echo "<script>document.getElementById('show_closed').disabled=false;</script>";
    echo "<script>document.getElementById('show_delayed').disabled=false;</script>";
    //Хедер-футер. ФУТЕР
    echo "</div></td></tr><tr class=footer_tr><td>";
    $_SESSION['q_count_med'] = $row_num; //эта переменная нужна для автоматического обновления окна со звонками
    $_SESSION['q_checksum_med'] = $checksum; //эта переменная нужна для автоматического обновления окна со звонками
    echo "Количество строк: <b>" . $row_num . "</b><br/>";
    //echo "Контрольная сумма: <b>".$checksum."</b><br/>";
    echo '<input type="submit" style="display:none" name=ok value="">';
    //Хедер-футер. КОНЕЦ
    echo "</td></tr></table>";
    //echo '<a href="#" title="Вернуться к началу" class="topbutton">^ Наверх ^</a>';
    echo '<div id="toTop">^ Наверх ^</div>';
    echo "</form>";

    echo "<script type='application/javascript'>";
    echo "make_sound();"; // при перезагрузке

    if (USER_ADMIN != $_SESSION['user_role']) {
        asort($agid_arr);
        foreach ($agid_arr as $key => $val) {
            if ($key == $agid_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.agid_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }
        asort($anumber_arr);
        foreach ($anumber_arr as $key => $val) {
            if ($key == $anumber_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.anumber_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }
        /*asort($bnumber_arr);
        foreach ($bnumber_arr as $key => $val) {
            if ($key == $bnumber_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.bnumber_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }
        asort($project_arr);
        foreach ($project_arr as $key => $val) {
            if ($key == $project_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.project_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }
        asort($ct_arr);
        foreach ($ct_arr as $key => $val) {
            if ($key == $ct_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.ct_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }*/
    }
    asort($st_arr);
    foreach ($st_arr as $key => $val) {
        if ($key == $st_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.st_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($service_arr);
    foreach ($service_arr as $key => $val) {
        if ($key == $service_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.service_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    /*asort($service_det_arr);
    foreach($service_det_arr as $key => $val) {
        if($key == $service_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.service_det_id,'".$key."','".$val."','".$selected."');";
    }*/
    asort($usluga_auto_arr);
    foreach ($usluga_auto_arr as $key => $val) {
        if ($key == $source_a_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.source_a_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($usluga_arr);
    foreach ($usluga_arr as $key => $val) {
        if ($key == $source_m_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.source_m_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($usluga_arr_new);
    foreach ($usluga_arr_new as $key => $val) {
        if ($key == $source_man_id_new) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.source_man_id_new,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($detail_arr);
    foreach ($detail_arr as $key => $val) {
        if ($key == $detail_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.detail_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    /*asort($detail_arr_new);
    foreach($detail_arr_new as $key => $val) {
        if($key == $detail_id_new) $selected='selected'; else $selected='';
        echo "add_options(document.all.detail_id_new,'".$key."','".$val."','".$selected."');";
    }*/
    if (USER_USER != $_SESSION['user_role'] || $_SESSION['on_duty_today']) {
        asort($texnari_arr);
        foreach ($texnari_arr as $key => $val) {
            if ($key == $texnari_id) $selected = 'selected'; else $selected = '';
            echo "add_options(document.all.texnari_id,'" . $key . "','" . $val . "','" . $selected . "');";
        }
    }
    asort($status_arr);
    foreach ($status_arr as $key => $val) {
        if ($key == $stat_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.stat_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($status_det_arr);
    foreach ($status_det_arr as $key => $val) {
        if ($key == $stat_det_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.stat_det_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    //asort($res_arr);
    foreach ($res_arr as $key => $val) {
        if ($key == $res_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.res_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }

    if ($find_id == '' || $row_num == 0) {
        echo "if (document.all.start_date) document.all.start_date.disabled=false;";
        echo "if (document.all.end_date) document.all.end_date.disabled=false;";
        echo "if (document.all.show_nedo) document.all.show_nedo.disabled=false;";
        echo "if (document.all.show_trans) document.all.show_trans.disabled=false;";
        echo "if (document.all.show_closed) document.all.show_closed.disabled=false;";
        echo "if (document.all.show_delayed) document.all.show_delayed.disabled=false;";
        if (USER_ADMIN != $_SESSION['user_role']) {
            echo "if (document.all.agid_id) document.all.agid_id.disabled=false;";
            echo "if (document.all.anumber_id) document.all.anumber_id.disabled=false;";
            /*echo "document.all.bnumber_id.disabled=false;";
            echo "document.all.project_id.disabled=false;";
            echo "document.all.ct_id.disabled=false;";*/
        }
        echo "if (document.all.st_id) document.all.st_id.disabled=false;";
        echo "if (document.all.service_id) document.all.service_id.disabled=false;";
        //echo "document.all.service_det_id.disabled=false;";
        echo "if (document.all.source_a_id) document.all.source_a_id.disabled=false;";
        echo "if (document.all.source_m_id) document.all.source_m_id.disabled=false;";
        echo "if (document.all.source_man_id_new) document.all.source_man_id_new.disabled=false;";
        echo "if (document.all.detail_id) document.all.detail_id.disabled=false;";
        //echo "document.all.detail_id_new.disabled=false;";
        echo "if (document.all.texnari_id) document.all.texnari_id.disabled=false;";
        echo "if (document.all.stat_id) document.all.stat_id.disabled=false;";
        echo "if (document.all.stat_det_id) document.all.stat_det_id.disabled=false;";
        //if (isset($show_trans))
        echo "if (document.all.res_id) document.all.res_id.disabled=false;";
        echo "if (document.all.mail_id) document.all.mail_id.disabled=false;";
    }
    echo "</script>";
}
    ?>

<script type="text/javascript">
    $('#start_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
    });
</script>
<script type="text/javascript">
    $('#end_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
    });
</script>

<?php if (!isset($find_id) || $find_id == "") { ?>
    <iframe name=check_new src="med_check_new.php" style="display:none"></iframe>
    <iframe name=ifr2 style='display:none; width: 50%'></iframe>
<?php } ?>

</body>
</html>

