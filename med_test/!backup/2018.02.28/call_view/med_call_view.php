<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<?php
//header('Refresh: 15; url=' . $_SERVER['PHP_SELF']); // автоматическая перезагрузка
extract($_REQUEST);
ini_set('session.use_cookies','1');

//session_name('medc');
session_start();
$sid=session_id();

if(!isset($find_id)) $find_id = '';
else $find_id = trim($find_id);
/*if (isset($exit)) {
//setcookie('login');
//setcookie('pass');
    session_destroy();
    header('Location:/');
}*/

require_once '../funct.php';

$c = GetData::GetConnect();

// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$date=date("d.m.Y"); // число.месяц.год
$time=date("H:i"); // часы:минуты:секунды
$backurl = "med_call_view.php";
//---------------------------------------------------------------------- //
?>
<head>
    <link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>Входящие звонки</title>
    <meta name="description" content="Входящие звонки">
    <script src="../js/jquery.datetimepicker.full.js"></script>
    <!--script type="text/javascript">
        $(function(){
            $('<audio id="chatAudio"><source src="notify.mp3" type="audio/mpeg"></audio>').appendTo('body');
            $("#trig").on("click",function(){
                $('#chatAudio')[0].play();
            });
        });
//echo "<input type='button' value=' Send ' id='trig' />";
    </script-->
</head>

<script type="application/javascript">
    function make_sound(){
        $('<audio id="ListAudio"><source src="notify.mp3" type="audio/mpeg"></audio>').appendTo('body');
        $('#ListAudio')[0].play();
    }

    function add_options(obj,opt_id,opt_val,opt_selected) {
        len=obj.options.length;
        obj.options[len] = new Option(opt_val,opt_id);
        if(opt_selected=='selected') obj.options[len].selected=true;
    }
    function sel_row(row) {
        for(i=0; i<row.cells.length; i++) {
            row.cells[i].bgColor='#66FFFF';
        }
    }
    function unsel_row(row) {
        for(i=0; i<row.cells.length; i++) {
            row.cells[i].bgColor='white';
        }
    }
    function ch_show_nedo() {
        if (document.all.show_nedo.checked==true) {location.reload('/?show_nedo=1');}
        else {location.reload('/');}
    }
    function ch_show_trans() {
        if (document.all.show_trans.checked==true) {location.reload('/?show_trans=1');}
        else {location.reload('/');}
    }
    function ch_show_closed() {
        if (document.all.show_closed.checked==true) {location.reload('/?show_closed=1');}
        else {location.reload('/');}
    }
    function ch_show_delayed() {
        if (document.all.show_delayed.checked==true) {location.reload('/?show_delayed=1');}
        else {location.reload('/');}
    }
    function open_edit(base_id, texnari_id, sid) {
        if (base_id > 0) {
            win = window.open("../med_call_out.php?base_id=" + base_id + "&texnari_id=" + texnari_id + "&sid=" + sid, "med_call_" + base_id, "width=720, height=640, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
            win.focus();
        }
    }
    /*function open_new(sid) {
        win=window.open("new_call.php?sid="+sid,"sup_order_new","width=700, height=750, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
        win.focus();
    }*/
    var t;
    function fn_find_id() {
        clearTimeout(t);
        if(document.all.find_id.value.length==0 || document.all.find_id.value.length>=3) t=setTimeout('document.all.ok.click()',3000);
    }
</script>

<body style="margin-top: 0;">
<?php
if (isset($_POST['trans_num'])) {
    $trans_arr = date_parse(date("Y-m-d HH:MM"));
    //$const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'].'-'.$trans_arr['hour'];
    //$sqlstr = "SELECT ID FROM CALL_BASE WHERE TRANSFER_NUM like '".$const_str."-".$_POST['trans_num'] . "'";
    $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'];
    $sqlstr = "SELECT ID FROM CALL_BASE WHERE TRANSFER_NUM like '".$const_str."-".$_POST['trans_hour']."-".$_POST['trans_num'] . "'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $sqlstr);
        if (OCIExecute($query)) {
            if (NULL != ($objResult = OCI_Fetch_Row($query))) {
                echo "<script type='application/javascript'>open_edit('" . $objResult[0] . "','" . $_SESSION['login_id'] . "','" . $sid . "');</script>";
            } else {
                //echo "Звонок с переводным номером ".$const_str." не найден!!!";
                echo "<script type='text/javascript'>alert('Звонок не найден!!!')</script>";
            }
        }
        oci_free_statement($query);
    }
}
?>
<form action="" method="post">
    <h3 style='margin-top: 0;margin-bottom: 0;'>Просмотр входящих звонков
    <?php if (DB_OCI) {
        $q = OCIParse($c, "SELECT to_char(max(LAST_CHANGE),'DD.MM.YYYY HH24:MI') last_change FROM call_base");
        OCIExecute($q, OCI_DEFAULT);
        OCIFetch($q);
        echo "<span style='display: inline; color: #007fff'> / Последнее изменение:&nbsp;<span style='color: darkblue;'>". OCIResult($q, "LAST_CHANGE") ."</span></span>";
        oci_free_statement($q);
    }
    $trans_arr = date_parse(date("Y-m-d HH:MM"));
    $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'].'-'.$trans_arr['hour'];
    ?>
    <!--input type='submit' name='Adding' value='Открыть звонок' class='add_button' style='float: right; margin-top: -5px;'
           onclick="javascript:open_edit('< ?=$_POST['trans_num']?>','< ?=$_SESSION['login_id']?>','< ?=$sid?>')"-->
    <input type='submit' name='Adding' value='Открыть звонок' class='add_button' style='float: right; margin-top: -5px;'/>
    <input type='my_number' name='trans_num' style='float: right; width: 4em;' placeholder='Номер'/>
    <input type='my_number' name='trans_hour' class='my_number' style='float: right; vertical-align: top;' value="<?=$trans_arr['hour']?>"/>
    <label for='trans_num' style='float: right; color: brown'>Переводной номер:&nbsp;<span style="color: black"><?=$trans_arr['year']?>-<?=$trans_arr['month']?>-<?=$trans_arr['day']?>-</span></label>
    </h3>
</form>
<?php
/*echo "<h3 style='margin-bottom: 0;'>Просмотр входящих звонков";

if (DB_OCI) {
    $q = OCIParse($c, "SELECT to_char(max(LAST_CHANGE),'DD.MM.YYYY HH24:MI') last_change FROM call_base");
    OCIExecute($q, OCI_DEFAULT);
    OCIFetch($q);
    echo "<span style='display: inline; color: #007fff'> / Последнее изменение:&nbsp;<span style='color: darkblue;'>". OCIResult($q, "LAST_CHANGE") ."</span></span>";
    oci_free_statement($q);
}
echo "</h3>";*/
echo "<form method=get><input type=hidden name=refresh value=y>";
//описание переменных (если не выбран фильтр)
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
//if (USER_ADMIN != $_SESSION['user_role']) {
if (!isset($anumber_id)) $anumber_id = '';
if (!isset($bnumber_id)) $bnumber_id = '';
if (!isset($project_id)) $project_id = '';
if (!isset($agid_id)) $agid_id = '';
if (!isset($ct_id)) $ct_id = '';
//}
if (!isset($service_id)) $service_id='';
if (!isset($usluga_a_id)) $usluga_a_id='';
if (!isset($usluga_id)) $usluga_id='';
if (!isset($detail_id)) $detail_id='';
if (!isset($texnari_id)) $texnari_id='';
if (!isset($stat_id)) $stat_id='';
if (!isset($res_id)) $res_id='';
//if (!isset($trbl_id)) $trbl_id='';

$anumber_arr = array();
$bnumber_arr = array();
$agid_arr = array();
$project_arr = array();
$ct_arr = array();
$service_arr = array();
$usluga_auto_arr = array();
$usluga_arr = array();
$detail_arr = array();
$texnari_arr = array();
$status_arr = array();
$res_arr = array();
//$trbl_grp_arr=array();
//$location_arr=array();
//$kto_arr=array();
//$trbl_arr=array();

echo "<table align=center style='width: 99%'><tr><td>";
if (DB_OCI) {
    echo "<nobr>Поиск: <input type=text name=find_id value='".$find_id."' onkeyup=fn_find_id(); onpaste=fn_find_id(); 
    title='Введите не менее 3-х символов и подождите 3 секунды. Будет выполнен поиск совпадений по всем полям.'>";

    echo " Показать: <b style='color: blue'>перезвон</b><input type=checkbox ";
    if (isset($show_delayed)) echo "checked "; echo "name=show_delayed onclick=ok.click()> | ";

    echo "<b style='color: orange'>недозвон</b><input type=checkbox ";
    if (isset($show_nedo)) echo "checked "; echo "name=show_nedo onclick=ok.click()> | ";

    echo "<b style='color: brown'>переводные</b><input type=checkbox ";
    if (isset($show_trans)) echo "checked "; echo "name=show_trans onclick=ok.click()> | ";

    echo "<b style='color: red'>все записи</b><input type=checkbox ";
    if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()> | ";

    echo "<b style='color: darkgreen'>комментарии</b><input type=checkbox ";
    if (isset($show_text)) echo "checked "; echo "name=show_text onclick=ok.click()></nobr> ";

    echo "<script>document.all.show_nedo.disabled=true;</script>";
    echo "<script>document.all.show_trans.disabled=true;</script>";
    echo "<script>document.all.show_closed.disabled=true;</script>";
    echo "<script>document.all.show_delayed.disabled=true;</script>";

    /*width='auto'*/
    echo "<table align=center bgcolor=black cellspacing=1 cellpadding=1><tr>
        <th style='background-color: white; vertical-align: top; white-space: nowrap' colspan=2 >Дата звонка<br>
        c <input type=text name='start_date' id='start_date' size=6 onchange=ok.click() value='"; if (isset($start_date)) echo $start_date; echo "' />
        по <input type=text name='end_date' id='end_date' size=6 onchange=ok.click() value='"; if (isset($end_date)) echo $end_date; echo "' />";
/*        c <input type=text name='start_date' id='start_date' size=6 onchange=ok.click() onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date);return false; HIDEFOCUS' value='"; if (isset($start_date)) echo $start_date; echo "' />
        по <input type=text name='end_date' id='end_date' size=6 onchange=ok.click() onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date);return false; HIDEFOCUS'  value='"; if (isset($end_date)) echo $end_date; echo "' />";*/
    echo "<script>document.all.start_date.disabled=true; document.all.end_date.disabled=true;</script>";
    echo "</th>";
if (USER_ADMIN != $_SESSION['user_role']) {
    echo "<th style='background-color: white; vertical-align: top; width: 80px'>ANumber<br>";
    echo "<select style='width:100%' name=anumber_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.anumber_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 80px'>BNumber<br>";
    echo "<select style='width:100%' name=bnumber_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.bnumber_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top;; width: 120px'>Оператор<br>";
    echo "<select style='width:100%' name=agid_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.agid_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top;; width: 80px'>project_id<br>";
    echo "<select style='width:100%' name=project_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.project_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 65px'>Звонок<br>";
    echo "<select style='width:100%' name=ct_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.ct_id.disabled=true;</script>";
}
    echo "<th style='background-color: white; vertical-align: top; width: 80px'>Услуга<br>";
    echo "<select style='width:100%' name=service_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.service_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 150px'>Источник(Авто)<br>";
    echo "<select style='width:100%' name=usluga_a_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.usluga_a_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 100px'>Источник<br>";
    echo "<select style='width:100px' name=usluga_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.usluga_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 100px'>Детализация<br>";
    echo "<select style='width:100%' name=detail_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.detail_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 120px'>Назначено<br>";
    echo "<select style='width:100%' name=texnari_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.texnari_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 90px'>Статус<br>";
    echo "<select style='width:100%' name=stat_id onchange=ok.click()>";
    echo "<option value='' style='color:green'>ВСЕ</option>";
    echo "</select>";
    echo "</th>";
    echo "<script>document.all.stat_id.disabled=true;</script>";

    echo "<th style='background-color: white; vertical-align: top; width: 90px'>Время<br/>cобытия<br>";
    echo "</th>";

    if (isset($show_trans)) { // переводные
        echo "<th style='background-color: white; vertical-align: top; width: 90px'>Переведено<br>";
        echo "<select style='width:100%' name=res_id onchange=ok.click()>";
        echo "<option value='' style='color:green'>ВСЕ</option>";
        echo "</select>";
        echo "</th>";
        echo "<script>document.all.res_id.disabled=true;</script>";
    }
    else $res_id='';

    echo "</tr>";

    if (USER_USER == $_SESSION['user_role'])
        $mystatus = STATUS_WORK;
    elseif (USER_SUPER == $_SESSION['user_role'])
        $mystatus = STATUS_OPEN;
    else //(USER_SUPER == $_SESSION['user_role'])
        $mystatus = STATUS_OPEN;

    //фильтр выбора
    $q_where = " WHERE cb.CALL_THEME_ID = 1"; // только Медицинские услуги
    if (!isset($show_trans)) // без переводных
        $q_where .= " and cb.RESULT_ID = " . RESULT_WAIT;
    if (USER_USER == $_SESSION['user_role'])
        $q_where .= " and cb.FIO_ID = " . $_SESSION['login_id']; // только свои

    //строка поиска отменяет все фильтры ???
    if ($find_id<>"") {
        $q_where.=" and (cb.ID like '".$find_id."%'
         or upper(replace(cb.ANUMBER,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(cb.BNUMBER,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(cb.SC_AGID,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(cb.SC_PROJECT_ID,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(ctt.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(serv.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(sr_a.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(sr_man.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(usr.FIO,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         or upper(replace(stat.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
         ) ";
//        or upper(replace(sr_det.NAME,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
    }
    else {
        //if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
        //if (!isset($show_delayed)) $q_where.=" and nvl(b.delay_to,sysdate)<=sysdate ";

        if ($start_date<>"" && $end_date<>"") {
            if (isset($show_closed))
                $q_where .= " and (cb.STATUS_ID <= " . STATUS_CLOSED . "
                and cb.DATE_CALL between to_date('$start_date','DD.MM.YYYY') AND to_date('$end_date','DD.MM.YYYY')+1 
                OR cb.CALL_BACK_DATE <= sysdate and cb.STATUS_ID = " . STATUS_CALL_BACK .")"; // будут все записи в интервале дат
            else {
                if (isset($show_delayed)) // предстоящие и открытые
                    //$q_where .= " and (to_char(cb.CALL_BACK_DATE,'dd.mm.yyyy') <= to_date('$date','DD.MM.YYYY') and cb.STATUS_ID = " . STATUS_CALL_BACK . "
                    $q_where .= " and (cb.STATUS_ID = " . STATUS_CALL_BACK . "
                    OR cb.LAST_CHANGE+15/1440 <= sysdate and cb.STATUS_ID = " . STATUS_CALL_NOT . "
                    OR cb.DATE_CALL between to_date('$start_date','DD.MM.YYYY') AND to_date('$end_date','DD.MM.YYYY')+1 and cb.STATUS_ID = " . $mystatus .")";
                elseif (isset($show_nedo)) // недозвон отложенный на 15 минут и открытые
                    $q_where .= " and (cb.STATUS_ID = " . STATUS_CALL_NOT . " 
                    OR cb.CALL_BACK_DATE <= sysdate and cb.STATUS_ID = " . STATUS_CALL_BACK ."
                    OR cb.DATE_CALL between to_date('$start_date','DD.MM.YYYY') AND to_date('$end_date','DD.MM.YYYY')+1 and cb.STATUS_ID = " . $mystatus .")";
                else // чекбоксы не выбраны
                    $q_where .= " and (cb.CALL_BACK_DATE <= sysdate and cb.STATUS_ID = " . STATUS_CALL_BACK ."
                    OR cb.LAST_CHANGE+15/1440 <= sysdate and cb.STATUS_ID = " . STATUS_CALL_NOT . "
                    OR cb.DATE_CALL between to_date('$start_date','DD.MM.YYYY') AND to_date('$end_date','DD.MM.YYYY')+1 and cb.STATUS_ID = " . $mystatus .")";
            }
        }

        if (USER_ADMIN != $_SESSION['user_role']) {
            if ($anumber_id <> "") $q_where .= " and cb.ANUMBER='" . $anumber_id . "' ";
            if ($bnumber_id <> "") $q_where .= " and cb.BNUMBER='" . $bnumber_id . "' ";
            if ($project_id <> "") $q_where .= " and cb.SC_PROJECT_ID='" . $project_id . "' ";
            if ($agid_id <> "")    $q_where .= " and cb.SC_AGID='" . $agid_id . "' ";
            if ($ct_id <> "")      $q_where .= " and cb.CALL_TYPE_ID='" . $ct_id . "' ";
        }
        if ($service_id<>"") $q_where.=" and cb.SERVICE_ID='".$service_id."' ";
        if ($usluga_a_id<>"")$q_where.=" and cb.SOURCE_AUTO_ID='".$usluga_a_id."' ";
        if ($usluga_id<>"")  $q_where.=" and cb.SOURCE_MAN_ID='".$usluga_id."' ";
        if ($detail_id<>"")  $q_where.=" and cb.SOURCE_MAN_DET_ID='".$detail_id."' ";
        if ($texnari_id<>"") $q_where.=" and cb.FIO_ID='".$texnari_id."' ";
        if ($stat_id<>"")    $q_where.=" and cb.STATUS_ID='".$stat_id."' ";
        if (isset($show_trans) && $res_id<>"") $q_where.=" and cb.RESULT_ID='".$res_id."' ";
    }

    // права доступа по департаментам
    $q_where .= " and (cb.source_auto_id,cb.source_man_id,cb.call_type_id,cb.service_id) in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id),
        decode(ad.source_man_id,-1,cb.source_man_id,ad.source_man_id),
        decode(ad.call_type_id,-1,cb.call_type_id,ad.call_type_id),
        decode(ad.service_id,-1,cb.service_id,ad.service_id)
        from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id and uda.user_id='".$_SESSION['login_id']."') ";

    /*$query = oci_parse($c,"SELECT MIN(dep_id) as DEP_ID FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id']);
    if (OCIExecute($query)) {
        $row = oci_fetch_assoc($query);
        if ($row['DEP_ID'] != -1) {
            $query = oci_parse($c, "SELECT MIN(service_id) AS service_id FROM access_dep
                  WHERE departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . ")");
            if (OCIExecute($query)) {
                $row = oci_fetch_assoc($query);
                if ($row['SERVICE_ID'] != -1) {
                    $query = OCIParse($c, "DROP TABLE temp_table_service ");
                    OCIExecute($query);
                    $query = oci_parse($c, "create GLOBAL TEMPORARY table temp_table_service ON COMMIT PRESERVE ROWS as
                      (SELECT service_id FROM access_dep WHERE service_id != -1 and
                            departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . "))");
                    if (OCIExecute($query) and oci_num_rows($query) > 0)
                        $q_where .= " and cb.service_id in (select service_id from temp_table_service) ";
                }
            }

            $query = oci_parse($c, "SELECT MIN(source_auto_id) AS source_auto_id FROM access_dep
                  WHERE departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . ")");
            if (OCIExecute($query)) {
                $row = oci_fetch_assoc($query);
                if ($row['SOURCE_AUTO_ID'] != -1) {
                    $query = OCIParse($c, "DROP TABLE temp_table_source_auto ");
                    OCIExecute($query);
                    $query = oci_parse($c, "create GLOBAL TEMPORARY table temp_table_source_auto ON COMMIT PRESERVE ROWS as
                      (SELECT source_auto_id FROM access_dep WHERE source_auto_id != -1 and
                            departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . "))");
                    if (OCIExecute($query) and oci_num_rows($query) > 0)
                        $q_where .= " and cb.source_auto_id in (select source_auto_id from temp_table_source_auto) ";
                }
            }

            $query = oci_parse($c, "SELECT MIN(source_man_id) AS source_man_id FROM access_dep
                  WHERE departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . ")");
            if (OCIExecute($query)) {
                $row = oci_fetch_assoc($query);
                if ($row['SOURCE_MAN_ID'] != -1) {
                    $query = OCIParse($c, "DROP TABLE temp_table_source_man ");
                    OCIExecute($query);
                    $query = oci_parse($c, "create GLOBAL TEMPORARY table temp_table_source_man ON COMMIT PRESERVE ROWS as
                      (SELECT source_man_id FROM access_dep WHERE source_man_id != -1 and
                            departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . "))");
                    if (OCIExecute($query) and oci_num_rows($query) > 0)
                        $q_where .= " and cb.source_man_id in (select source_man_id from temp_table_source_man) ";
                }
            }

            $query = oci_parse($c, "SELECT MIN(call_type_id) AS call_type_id FROM access_dep
                  WHERE departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . ")");
            if (OCIExecute($query)) {
                $row = oci_fetch_assoc($query);
                if ($row['CALL_TYPE_ID'] != -1) {
                    $query = OCIParse($c, "DROP TABLE temp_table_call_type ");
                    OCIExecute($query);
                    $query = oci_parse($c, "create GLOBAL TEMPORARY table temp_table_call_type ON COMMIT PRESERVE ROWS as
                      (SELECT call_type_id FROM access_dep WHERE call_type_id != -1 and
                            departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . "))");
                    if (OCIExecute($query) and oci_num_rows($query) > 0)
                        $q_where .= " and cb.call_type_id in (select call_type_id from temp_table_call_type) ";
                }
            }
        }
    }
    else $q_where .= " and 1 = 0 ";
    oci_free_statement($query);*/

    $q_text1 = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID,
    cb.CALL_THEME_ID as THEME_ID, cb.CALL_TYPE_ID as CT_ID, ctt.NAME as CT, cb.SERVICE_ID as SRV_ID, serv.NAME as SRVNAME,
    cb.SOURCE_AUTO_ID as SRA_ID, sr_a.NAME as SRANAME,  sr_a.BNUMBER as SRABNUMBER,
    cb.SOURCE_MAN_ID as SRM_ID, sr_man.NAME as SRMNAME, cb.SOURCE_MAN_DET_ID as SRDET_ID, cb.COMMENTS,
    cb.STATUS_ID, stat.NAME as STATUS, cb.FIO_ID as FIO_ID, usr.FIO as FIO, 
    cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_HOME, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET,
    to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.CALL_BACK_DATE,'dd.mm.yyyy hh24:mi') CALL_BACK_DATE,
    cb.CALL_BACK_NUM, to_char(cb.DATE_CLOSE,'dd.mm.yyyy hh24:mi:ss') DATE_CLOSE,
    (nvl(to_char(cb.DATE_CLOSE,'MMDD'),0)+nvl(to_char(cb.CALL_BACK_DATE,'MMDD'),0)+nvl(to_char(cb.LAST_CHANGE,'MMDD'),0)+
    nvl(cb.FIO_ID,0)+nvl(cb.STATUS_ID,0)+nvl(cb.SOURCE_MAN_ID,0)+nvl(cb.SOURCE_MAN_DET_ID,0)) as checksum
    ";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN CALL_TYPE ctt ON cb.CALL_TYPE_ID = ctt.ID
        LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID 
        LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID 
        LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
        LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID 
        LEFT JOIN MED_STATUS stat ON cb.STATUS_ID = stat.ID ";
    $q_text4 = $q_where;
    $q_text5 = " ORDER BY cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME, CALL_TYPE_ID ";

    $q_text=$q_text1.$q_text2.$q_text3.$q_text4.$q_text5;
//echo "<br/><textarea>".$q_text."</textarea>";

    $_SESSION['refresh_where']=$q_text2.$q_text3.$q_text4; //эта переменная нужна для автоматического обновления окна со звонками
    //$_SESSION['export_where']=$export_where;
//echo "<br/><textarea>".$_SESSION['refresh_where']."</textarea>";

    $q = OCIParse($c, $q_text);
    //файлы, примечания
    if (isset($show_text)) {
        //$q_files = OCIParse($c,"select id,filename from MED_FILES where base_id=:base_id and tmp is null and hist_id is null order by filename");
        $sqlstr = "SELECT to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET, stat.NAME, OPERATOR || usr.FIO as FIO, COMMENTS 
                    FROM CALL_BASE_HIST hist
                    LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
                    LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID
                    WHERE hist.BASE_ID=:base_id";
        $q_comment = OCIParse($c,$sqlstr );
    }
    //статусы
    $q_stat = OCIParse($c,"select name, color from MED_STATUS where id=:id");

    $rownum = 0;
    $checksum = 0;
    OCIExecute($q,OCI_DEFAULT);
    while(OCIFetch($q)) {
        $tmp_base_id = OCIResult($q,"ID");
        $rownum++;
        $checksum += OCIResult($q,"CHECKSUM");

        //статусы
        $status_id = OCIResult($q,"STATUS_ID");
        OCIBindByName($q_stat,":id",$status_id);
        OCIExecute($q_stat,OCI_DEFAULT);
        OCIFetch($q_stat);
        $status_name = OCIResult($q_stat,"NAME");
        $status_color = OCIResult($q_stat,"COLOR");

        // Название Детализации из разных баз
        $source_id = OCIResult($q,"SRM_ID");
        $detailed_id = OCIResult($q,"SRDET_ID");
        if ($source_id < SOURCE_2GIS) { // у остальных списка детализации нет
            if (DETAILS_AMNESY == $detailed_id) {
                $detail_name = "Не помнит";
            } else if (DETAILS_PROMO == $detailed_id) {
                $detail_name = "На улице у промоутера";
            } else if (DETAILS_OTHER == $detailed_id) {
                $detail_name = "Другое";
            } else {
                if (SOURCE_FLAER == $source_id || SOURCE_CATALOG == $source_id ||
                    SOURCE_FLAER_SUB == $source_id || SOURCE_FLAER_CAR == $source_id ||
                    SOURCE_LIFT == $source_id || SOURCE_STOP == $source_id
                ) {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM SUBWAYS WHERE ID=:id");
                } else if (SOURCE_SERT == $source_id) {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
                } else {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
                }
                OCIBindByName($q_detail_det, ":id", $detailed_id);
                OCIExecute($q_detail_det, OCI_DEFAULT);
                OCIFetch($q_detail_det);
                $detail_name = OCIResult($q_detail_det, "NAME");
            }
        }
        else {
            $detail_name = "---";
        }

        /*if (in_array(OCIResult($q, "ANUMBER"), $anumber_arr) == FALSE ) {
            array_push($anumber_arr, OCIResult($q, "ANUMBER"));
        }*/
        if (!isset($anumber_arr[OCIResult($q,"ANUMBER")])) {
            $anumber_arr[OCIResult($q, "ANUMBER")] = OCIResult($q, "ANUMBER");
        }
        if (!isset($bnumber_arr[OCIResult($q,"BNUMBER")])) {
            $bnumber_arr[OCIResult($q, "BNUMBER")] = OCIResult($q, "BNUMBER");
        }
        if (!isset($agid_arr[OCIResult($q,"SC_AGID")])) {
            $agid_arr[OCIResult($q, "SC_AGID")] = OCIResult($q, "SC_AGID");
        }
        if (!isset($project_arr[OCIResult($q,"SC_PROJECT_ID")])) {
            $project_arr[OCIResult($q, "SC_PROJECT_ID")] = OCIResult($q, "SC_PROJECT_ID");
        }
        if (!isset($ct_arr[OCIResult($q,"CT_ID")])) {
            $ct_arr[OCIResult($q, "CT_ID")] = OCIResult($q, "CT");
        }
        if (!isset($service_arr[OCIResult($q,"SRV_ID")])) {
            $service_arr[OCIResult($q, "SRV_ID")] = OCIResult($q, "SRVNAME");
        }
        if (!isset($usluga_auto_arr[OCIResult($q,"SRA_ID")])) {
            $usluga_auto_arr[OCIResult($q, "SRA_ID")] = OCIResult($q, "SRANAME");
        }
        if (!isset($usluga_arr[$source_id])) {
            $usluga_arr[$source_id] = OCIResult($q, "SRMNAME");
        }
        if ($source_id < SOURCE_2GIS) { // у остальных списка детализации нет
            if (!isset($detail_arr[$detailed_id])) {
                $detail_arr[$detailed_id] = $detail_name; //OCIResult($q, "SRDETNAME");
            }
        }
        //else $detail_arr[DETAILS_OTHER] = '---';

        if (!isset($status_arr[$status_id]))
            $status_arr[$status_id] = OCIResult($q,"STATUS");

        //собираем список операторов
        if (OCIResult($q, "FIO")) {
            if (!isset($texnari_arr[OCIResult($q, "FIO_ID")]))
                $texnari_arr[OCIResult($q, "FIO_ID")] = OCIResult($q, "FIO");
        }

        $result_id = OCIResult($q, "RESULT_ID");
        $res_det_id = OCIResult($q, "RESULT_DET");
        if (isset($show_trans)) { // результат входящего звонка
            if (!isset($res_arr[$result_id]))
                $res_arr[$result_id] = CALL_RES[$result_id];
        }

        echo "<tr";
        //if (OCIResult($q,"DUBLIKAT")=="y") echo " title='Дубликат'";
        //else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " title='Ошибка'";
        echo " style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='javascript:open_edit(\"".
            OCIResult($q,"ID")."\",\"".OCIResult($q,"FIO_ID")."\",\"".$sid."\")'>
        <td bgcolor=white style='text-align: center; width: 35px;";
        /*    if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
        else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
        else if(OCIResult($q,"CDPN")=='') echo " title='Нет АОНа'";
        else if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номером клиники!'";
        else echo " style='color:green'";*/
        echo " color:".$status_color.";'>".OCIResult($q,"ID")."</td>";

    if (isset($show_text)) {
        echo "<td bgcolor=white style='white-space: normal; color:".$status_color.";'";
        if (USER_ADMIN != $_SESSION['user_role'])
             echo " colspan=5>";
        else echo " colspan=1>";
        echo "Дата: <b>".OCIResult($q,"DATE_CALL")."</b><br>";
        echo "ANumber: <b>".OCIResult($q,"ANUMBER")."</b>";
        echo "; BNumber: <b>".OCIResult($q,"BNUMBER")."</b><br>";
        echo "Оператор: <b>".OCIResult($q,"SC_AGID")."</b>";
        echo "; Проект: <b>".OCIResult($q,"SC_PROJECT_ID")."</b>";
        echo "; ID&nbsp;звонка: <b>".OCIResult($q,"SC_CALL_ID")."</b><br>";

        OCIBindByName($q_comment,":base_id",$tmp_base_id);
        OCIExecute($q_comment);
        echo "Комментарии:";
        while(OCIFetch($q_comment)) {
            $comment_cut = str_replace("c_b=", "", OCIResult($q_comment,"COMMENTS"));
            echo "<hr style='margin: 0'>".OCIResult($q_comment,"DATE_DET")."; ФИО:".OCIResult($q_comment,"FIO")."; Статус: ".OCIResult($q_comment,"NAME").";<br/>".$comment_cut."; ";
        }
        //файлы
        /*OCIBindByName($q_files,":base_id",$tmp_base_id);
        OCIExecute($q_files);
        $f=0; while(OCIFetch($q_files)) { $f++;
            if($f==1) { echo "<hr>Файлы: "; }
            echo "<a href='http://med.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
        }*/
        echo "</td>";
    }
    else {
        echo "<td bgcolor=white style='text-align: center;";
        //if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
        //else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
        echo" color:".$status_color.";'>".OCIResult($q,"DATE_CALL")."</td>";

        if (USER_ADMIN != $_SESSION['user_role']) {
            echo "<td bgcolor=white style='text-align: center;";
            echo " color:" . $status_color . ";'>" . OCIResult($q, "ANUMBER") . "</td>";
            echo "<td bgcolor=white style='text-align: center;";
            echo " color:" . $status_color . ";'>" . OCIResult($q, "BNUMBER") . "</td>"; // KTO
            echo "<td bgcolor=white style='text-align: center;";
            echo " color:" . $status_color . ";'>" . OCIResult($q, "SC_AGID") . "</td>";
            //Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>".nl2br(htmlentities(OCIResult($q,"TRBL_NAME")))."</td>";
            echo "<td bgcolor=white style='text-align: center;";
            echo " color:" . $status_color . ";'>" . OCIResult($q, "SC_PROJECT_ID") . "</td>";
        }
    }
        if (USER_ADMIN != $_SESSION['user_role']) {
            echo "<td bgcolor=white style='text-align: center;";
            echo " color:" . $status_color . ";'>" . OCIResult($q, "CT") . "</td>";
        }
        echo "<td bgcolor=white style='text-align: center; width: 100px;";
        echo " color:".$status_color.";'>".OCIResult($q,"SRVNAME")."</td>";
        echo "<td bgcolor=white style='text-align: center; width: 150px !important;";
        echo " color:".$status_color.";'>".OCIResult($q,"SRANAME")."</td>";
        echo "<td bgcolor=white style='text-align: center;";
        echo " color:".$status_color.";'>".OCIResult($q,"SRMNAME")."</td>";
        echo "<td bgcolor=white style='text-align: center;";
        echo " color:".$status_color.";'>$detail_name</td>";
        echo "<td bgcolor=white style='text-align: center;";
        echo " color:".$status_color.";'>".OCIResult($q,"FIO")."</td>";

        echo "<td bgcolor=white style='text-align: center;";
        echo " color:".$status_color.";'>".$status_name."</td>";
        echo "<td bgcolor=white style='text-align: center;";
        if (STATUS_CALL_BACK == $status_id)
            echo " color:".$status_color.";'>".OCIResult($q,"CALL_BACK_DATE")."</td>";
        else //if (STATUS_CALL_NOT == $status_id)
            echo " color:".$status_color.";'>".OCIResult($q,"LAST_CHANGE")."</td>";

        if (isset($show_trans)) { // переводные
            echo "<td bgcolor=white style='text-align: center;";
            if (RESULT_WAIT == $result_id)
                echo " color:".$status_color.";'>".CALL_RES[$result_id]."</td>";
            elseif (RESULT_CLINIC == $result_id) {
                $q_res_det = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
                OCIBindByName($q_res_det, ":id", $res_det_id);
                OCIExecute($q_res_det, OCI_DEFAULT);
                OCIFetch($q_res_det);
                $clinic_name = OCIResult($q_res_det, "NAME");
                echo " color:" . $status_color . ";'>" . CALL_RES[$result_id] . "<br/>(".$clinic_name.")</td>";
            }
            else echo " color:" . $status_color.";'>" . CALL_RES[$result_id] . "<br/>(".$res_det_id.")</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    OCIFreeStatement($q);

    $_SESSION['q_count'] = $rownum; //эта переменная нужна для автоматического обновления окна со звонками
    $_SESSION['q_checksum'] = $checksum; //эта переменная нужна для автоматического обновления окна со звонками
    echo "Количество строк: <b>".$rownum."</b></br>";
    //echo "Контрольная сумма: <b>".$checksum."</b></br>";
	echo '<input type="submit" style="display:none" name=ok value="">
</form>';

    echo "<script type='application/javascript'>";
    echo "make_sound();"; // при перезагрузке

if (USER_ADMIN != $_SESSION['user_role']) {
    asort($anumber_arr);
    foreach ($anumber_arr as $key => $val) {
        if ($key == $anumber_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.anumber_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($bnumber_arr);
    foreach ($bnumber_arr as $key => $val) {
        if ($key == $bnumber_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.bnumber_id,'" . $key . "','" . $val . "','" . $selected . "');";
    }
    asort($agid_arr);
    foreach ($agid_arr as $key => $val) {
        if ($key == $agid_id) $selected = 'selected'; else $selected = '';
        echo "add_options(document.all.agid_id,'" . $key . "','" . $val . "','" . $selected . "');";
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
    }
}
    asort($service_arr);
    foreach($service_arr as $key => $val) {
        if($key == $service_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.service_id,'".$key."','".$val."','".$selected."');";
    }
    asort($usluga_auto_arr);
    foreach($usluga_auto_arr as $key => $val) {
        if($key == $usluga_a_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.usluga_a_id,'".$key."','".$val."','".$selected."');";
    }
    asort($usluga_arr);
    foreach($usluga_arr as $key => $val) {
        if($key == $usluga_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.usluga_id,'".$key."','".$val."','".$selected."');";
    }
    asort($detail_arr);
    foreach($detail_arr as $key => $val) {
        if($key == $detail_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.detail_id,'".$key."','".$val."','".$selected."');";
    }
    asort($texnari_arr);
    foreach($texnari_arr as $key => $val) {
        if($key == $texnari_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.texnari_id,'".$key."','".$val."','".$selected."');";
    }
    asort($status_arr);
    foreach($status_arr as $key => $val) {
        if($key == $stat_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.stat_id,'".$key."','".$val."','".$selected."');";
    }
    //asort($res_arr);
    foreach($res_arr as $key => $val) {
        if($key == $res_id) $selected='selected'; else $selected='';
        echo "add_options(document.all.res_id,'".$key."','".$val."','".$selected."');";
    }

    if ($find_id=='') {
        echo "document.all.start_date.disabled=false;";
        echo "document.all.end_date.disabled=false;";
        echo "document.all.show_nedo.disabled=false;";
        echo "document.all.show_trans.disabled=false;";
        echo "document.all.show_closed.disabled=false;";
        echo "document.all.show_delayed.disabled=false;";
        if (USER_ADMIN != $_SESSION['user_role']) {
            echo "document.all.anumber_id.disabled=false;";
            echo "document.all.bnumber_id.disabled=false;";
            echo "document.all.project_id.disabled=false;";
            echo "document.all.agid_id.disabled=false;";
            echo "document.all.ct_id.disabled=false;";
        }
        echo "document.all.service_id.disabled=false;";
        echo "document.all.usluga_a_id.disabled=false;";
        echo "document.all.usluga_id.disabled=false;";
        echo "document.all.detail_id.disabled=false;";
        echo "document.all.texnari_id.disabled=false;";
        echo "document.all.stat_id.disabled=false;";
        if (isset($show_trans))
            echo "document.all.res_id.disabled=false;";
    }

    echo "</script>";

} else { // MySql

}
echo "</td></tr></table>";
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

<?php if (0) { ?>
<div class="container">
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
                  strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_cie">
        <?php } else { ?>
    <table class="scrolling-table_c">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px;">ID</th>
        <th>Дата звонка</th>
        <th>ANumber</th>
        <th>BNumber</th>
        <th>agid</th>
        <th>project_id</th>
        <th>Тип звонка</th>
        <th>Услуга</th>
        <th style="width: 150px !important;">Источник(Авто)</th>
        <th>Источник</th>
        <th>Детализация</th>
        <th>Операция</th>
    </tr></thead>
    <tbody>
    <?php
    if (DB_OCI) {
		$nrows = 0;
        $selectstr = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID, 
                    sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, CALL_TYPE_ID as CT_ID, ctt.NAME as CT, serv.NAME as SRVNAME, sr_det.NAME as SRDETNAME
                FROM CALL_BASE cb
                LEFT JOIN CALL_TYPE ctt ON cb.CALL_TYPE_ID = ctt.ID
                LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID 
                LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
                LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID 
                LEFT JOIN SOURCE_MAN_DETAIL sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
                ORDER BY cb.DATE_CALL, serv.NAME, sr_a.NAME, sr_man.NAME, CALL_TYPE_ID";
        /*"SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID,
                sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, CALL_TYPE_ID as ct, serv.NAME as SRVNAME, sr_det.NAME as SRDETNAME
                FROM CALL_BASE cb, SOURCE_AUTO sr_a, SOURCE_MAN sr_man, SERVICES serv, SOURCE_MAN_DETAIL sr_det
                WHERE cb.SOURCE_AUTO_ID = sr_a.ID AND cb.SOURCE_MAN_ID = sr_man.ID AND cb.SERVICE_ID = serv.ID AND cb.SOURCE_MAN_DET_ID = sr_det.ID
                ORDER BY cb.DATE_CALL, sr_a.NAME, sr_man.NAME, serv.NAME, CALL_TYPE_ID";*/
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array( $query )) {
			if (TRUE == ENCODE_UTF) {
			$tmpstr = iconv ('windows-1251', 'utf-8', $result_array['DEPNAME']);
            $result_array['DEPNAME'] = $tmpstr;
			$tmpstr = iconv ('windows-1251', 'utf-8', $result_array['SRANAME']);
			$result_array['SRANAME'] = $tmpstr;
			$tmpstr = iconv ('windows-1251', 'utf-8', $result_array['SRMNAME']);
			$result_array['SRMNAME'] = $tmpstr;
            $tmpstr = iconv ('windows-1251', 'utf-8', $result_array['SRVNAME']);
            $result_array['SRVNAME'] = $tmpstr;
            $tmpstr = iconv ('windows-1251', 'utf-8', $result_array['SRDETNAME']);
            $result_array['SRDETNAME'] = $tmpstr;
			}

			echo '<tr>
                    <td>' . $result_array['ID'] . '</td>
                    <td>' . $result_array['DATE_CALL'] . '</td>
                    <td>' . $result_array['ANUMBER'] . '</td>
                    <td>' . $result_array['BNUMBER'] . '</td>
                    <td>' . $result_array['SC_AGID'] . '</td>
                    <td>' . $result_array['SC_PROJECT_ID'] . '</td>
                    <td>' . $result_array['CT'] . '</td>
                    <td>' . $result_array['SRVNAME'] . '</td>
                    <td style="width: 150px !important;">' . $result_array['SRANAME'] . '</td>
                    <td>' . $result_array['SRMNAME'] . '</td>
                    <td>' . $result_array['SRDETNAME'] . '</td>
                    <td style="text-align: center"><a href="?send_id=' . $result_array['ID'] . '">Назначить</a></td>
				</tr>';
			}
		}
        oci_free_statement($query);
	}
    else {
        $selectstr = "SELECT cb.ID, DATE_FORMAT(cb.DATE_CALL,'%d.%m.%Y %H:%i:%s') as Date_Call, 
                  cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID, cb.CALL_TYPE_ID as ct, 
                  cb.service_id, serv.NAME as srvName, cb.source_auto_id, sr_a.NAME as sraName, 
                  cb.source_man_id, sr_man.NAME as srmName, cb.source_man_det_id, sr_det.NAME as srDetName
                FROM CALL_BASE as cb
                LEFT JOIN SOURCE_AUTO as sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID 
                LEFT JOIN SOURCE_MAN as sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
                LEFT JOIN SERVICES as serv ON cb.SERVICE_ID = serv.ID 
                LEFT JOIN SOURCE_MAN_DETAIL as sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
                ORDER BY cb.DATE_CALL, serv.NAME, sr_a.NAME, sr_man.NAME, CALL_TYPE_ID";
        /*"SELECT cb.ID, DATE_FORMAT(cb.DATE_CALL,'%d.%m.%Y %H:%i:%s') as Date_Call, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID,
                sr_a.NAME as sraName, sr_man.NAME as srmName, CALL_TYPE_ID as ct, serv.NAME as srvName, sr_det.NAME as srDetName
                FROM CALL_BASE as cb, SOURCE_AUTO as sr_a, SOURCE_MAN as sr_man, SERVICES as serv, SOURCE_MAN_DETAIL as sr_det
                WHERE cb.SOURCE_AUTO_ID = sr_a.ID AND cb.SOURCE_MAN_ID = sr_man.ID AND cb.SERVICE_ID = serv.ID AND cb.SOURCE_MAN_DET_ID = sr_det.ID
                ORDER BY cb.DATE_CALL, sr_a.NAME, sr_man.NAME, serv.NAME, CALL_TYPE_ID";*/
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['DEPNAME']);
                    $result['DEPNAME'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['sraName']);
                    $result['sraName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['srmName']);
                    $result['srmName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['srvName']);
                    $result['srvName'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['srDetName']);
                    $result['srDetName'] = $tmpstr;
                }
                $checkstr = "SELECT departament_id FROM ACCESS_DEP 
                  WHERE call_type_id = {$result['ct']} AND service_id = {$result['service_id']} AND
                  source_auto_id = {$result['source_auto_id']} AND source_man_id = {$result['source_man_id']}
                  LIMIT 1";
//echo $checkstr; echo "<br/>";
                $check_query = mysqli_query(GetData::GetConnect(), $checkstr);
                if (FALSE !== $check_query) {
                    $result_check = mysqli_fetch_row($check_query);

                    echo '<tr>
                    <td>' . $result['ID'] . '</td>
                    <td>' . $result['Date_Call'] . '</td>
                    <td>' . $result['ANUMBER'] . '</td>
                    <td>' . $result['BNUMBER'] . '</td>
                    <td>' . $result['SC_AGID'] . '</td>
                    <td>' . $result['SC_PROJECT_ID'] . '</td>
                    <td>' . $result['ct'] . '</td>
                    <td>' . $result['srvName'] . '</td>
                    <td style="width: 150px !important;">' . $result['sraName'] . '</td>
                    <td>' . $result['srmName'] . '</td>
                    <td>' . $result['srDetName'] . '</td>
                    <td style="text-align: center">
                    <a href="?send_id=' . $result['ID'] . '">Назначить</a>
                    </td>
                </tr>';
                }
            }
        }
    }
    ?>

    </tbody>
    </table>
</div>

<?php } ?>

</body>
</html>

<iframe name=check_new src="med_check_new.php" style="display:none"></iframe>
<!--iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="../clndrxp94/ipopeng_tex.htm"
        scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe-->