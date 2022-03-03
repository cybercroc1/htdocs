<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);
require_once 'funct.php';

require_once "med/conn_string.cfg.php";
require_once "phone_conv_single.php";

if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./js/jquery.datetimepicker.css">
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>Исходящий звонок</title>
    <base href="/">
    <meta name="description" content="Исходящий звонок">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?=PATH?>/js/report.js"></script>
    <script src="<?=PATH?>/js/jquery.maskedinput.js"></script>
    <script src="<?=PATH?>/js/jquery.datetimepicker.full.js"></script>
    <!--script src="< ?=PATH?>/js/report.js"></script-->

    <script type='application/javascript'>
    function ResSelected(status_id) { // изменение источника рекламы (исх)
        var elem = document.getElementById('StatusId');
        var ResS = document.getElementById('Reservoir_new');
        if (<?=SOURCE_NOT?> == ResS.value) {
            if (<?=STATUS_OPEN?> != elem.value && <?=STATUS_CALL_NOT?> != elem.value && <?=STATUS_BREAK_LINE?> != elem.value ||
            (<?=STATUS_ERROR?> == elem.value && <?=STATUS_NOT?> == document.getElementById('status_det').value))
                document.getElementById('save_but').style.visibility = 'hidden';
            ResS.style.backgroundColor = '<?=needs?>';
            elem = ResS;
        }
        else {
            if (status_id == elem.value &&
                <?=STATUS_OPEN?> != elem.value && <?=STATUS_CALL_NOT?> != elem.value && <?=STATUS_BREAK_LINE?> != elem.value ||
                (<?=STATUS_ERROR?> == elem.value && <?=STATUS_NOT?> == document.getElementById('status_det').value) ||
            (<?=SERVICE_GINE?> != document.getElementById('ServiceId').value && <?=STOM_NOT?> == document.getElementById('ServiceStom').value))
                document.getElementById('save_but').style.visibility = 'hidden';
            else document.getElementById('save_but').style.visibility = 'visible';
            ResS.style.backgroundColor = 'white';
        }
        if (elem) elem.focus();
    }
    </script>

    <?php
    if (!isset($getserv) && !isset($getdet) && !isset($send_mail) && !isset($save_but))
        $date_start_work = date("d-m-Y H:i:s");

    if (isset($send_mail))
    {
        //echo "<script type='text/javascript'>alert('меняем данные в базе')</script>";
        if (FALSE == DEBUG_MODE)
            $updatemail = " UPDATE CALL_BASE SET SENT_MAIL = sysdate WHERE ID = ".$send_mail;
        else $updatemail = " UPDATE CALL_BASE_TEST SET SENT_MAIL = sysdate WHERE ID = ".$send_mail;
        GetData::my_log($updatemail, FALSE);
        $query = OCIParse(GetData::GetConnect(), $updatemail);
        $query_result = OCIExecute($query);
        if (!$query_result)
            GetData::my_log($updatemail, TRUE);
        oci_free_statement($query);
        unset($send_mail);
    }

    if (isset($getdet)) // Reservoir_new changed (!!! не используется, ибо выбор детализации отменили !!!)
    {
        /*if (SOURCE_NOT == $getdet)
            echo "<script>parent.document.getElementById('Reservoir_new').style.backgroundColor = '".needs."';</script>";
        else echo "<script>parent.document.getElementById('Reservoir_new').style.backgroundColor = 'white';</script>";*/

        $nrows = GetData::GetSourceDetail(TRUE, NULL, $getdet);
        if ($getdet < SOURCE_2GIS || $nrows > 0 /*SOURCE_BANNER_SUB == $getdet*/) { // у остальных списка нет
            if (SOURCE_FLAER == $getdet || SOURCE_CATALOG == $getdet ||
                SOURCE_FLAER_SUB == $getdet || SOURCE_FLAER_CAR == $getdet ||
                SOURCE_LIFT == $getdet || SOURCE_STOP == $getdet) {
                //$getdetailstr = "SELECT ID, NAME FROM SUBWAYS WHERE city = 1"; // пока только метро Москвы
                $getdetailstr = "SELECT ID, NAME FROM SUBWAYS";
                $strtitle = 'Станция метро';
            } elseif (SOURCE_SERT == $getdet) { //$getdetailstr = "SELECT ID, NAME FROM HOSPITALS";
                $getdetailstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ORDER BY hosp.CITY, hosp.NAME, serv.NAME";
                $strtitle = 'Сертификат';
            } else {
                $nrows = GetData::GetIstochnik(FALSE,TRUE,"ID = " . $getdet, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
                $strtitle = 'Детализация';
                if (isset(GetData::$array_istochnik)) {
                    foreach(GetData::$array_istochnik as $key => $value) {
                        $strtitle = $value['DETAIL'];
                    }
                }
                $getdetailstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id=" . $getdet;
            }
            if (TRUE == ENCODE_UTF)
                $strtitle = iconv ('utf-8', 'windows-1251', $strtitle);
            echo "<script>elem = parent.document.getElementById('AllInOne_new'); if (elem) elem.innerHTML='&nbsp;" . $strtitle . ":&nbsp;';</script>";
            $i = 0;
            $sel = "<select id=\"DetailList\" name=\"DetailList\">";
            $sel .= "<option value=\"\">Выберите детализацию</option>";
            $q = OCIParse($c, $getdetailstr);
            if (OCIExecute($q)) {
                while (OCIFetch($q)) {
                    $sel .= "<option value=\"".OCIResult($q,'ID')."\">".OCIResult($q,'NAME')."</option>";
                    $i++;
                }
            }
            if (SOURCE_SERT == $getdet) {
                $sel .= "<option value=".DETAILS_PROMO.">На улице у промоутера</option>";
                $sel .= "<option value=".DETAILS_OTHER.">Другое</option>";
                $i += 2;
            } elseif (SOURCE_NOT != $getdet && SOURCE_COUPON != $getdet) {
                $sel .= "<option value=".DETAILS_AMNESY.">Не помнит</option>";
                $i++;
            }
            $sel .= "</select>";
            if ($i == 0) {
                $sel = '-----';//'Пустой список детализации!';
            }
            echo "<script>elem = parent.document.getElementById('AllSelect_new'); if (elem) elem.innerHTML='&nbsp;" . $sel . "';</script>";
            echo "<script>elem = parent.document.getElementById('DetailList'); if (elem) elem.focus();</script>";
            echo "<script type = 'text/javascript'> var sel = parent.document.getElementById('DetailList');
                if (sel) { sel.onchange = function() { var elem = parent.document.getElementById('StatusId'); if (elem) elem.focus(); } }
                </script>";
        }
        else {
            echo "<script>elem = parent.document.getElementById('AllInOne_new'); if (elem) elem.innerHTML='';</script>";
            echo "<script>elem = parent.document.getElementById('AllSelect_new'); if (elem) elem.innerHTML='';</script>";
            echo "<script>elem = parent.document.getElementById('StatusId'); if (elem) elem.focus();</script>";
        }
        /*if (SOURCE_NOT == $getdet)
            echo "<script>parent.document.getElementById('save_but').style.visibility = 'hidden';</script>";
        else*/
        echo "<script> 
             if (parent.document.getElementById('StatusId').value != ".STATUS_NOT.") 
                 parent.document.getElementById('save_but').style.visibility = 'visible';
             </script>";

        exit();
    }

    if (isset($getserv)) { //ServiceChanged
        if (SERVICE_GINE != $getserv) { // Детализация теперь для всех кроме гинекологии
            echo "<script>parent.document.getElementById('ServiceStom').style.visibility = 'visible';</script>";
            if (STOM_NOT == $srv_det_id)
                echo "<script>parent.document.getElementById('ServiceStom').style.backgroundColor = '".needs."';</script>";
            else echo "<script>parent.document.getElementById('ServiceStom').style.backgroundColor = 'white';</script>";
        }
        else {
            echo "<script>parent.document.getElementById('ServiceStom').style.visibility = 'hidden';</script>";
        }

        if ($srv_id != $getserv) {
            echo "<script>parent.document.getElementById('call_back_picker').required = '';</script>";
            if (STATUS_NOT != $status_id_unic)
                echo "<script>elem = parent.document.getElementById('save_but'); if (elem) elem.style.visibility = 'visible';</script>";
        }
        else {
            if (STATUS_CALL_BACK == $status_id_unic)
                echo "<script>parent.document.getElementById('call_back_picker').required = 'required';</script>";
            if (STATUS_CALL_NOT != $status_id_unic)
                echo "<script>elem = parent.document.getElementById('save_but'); if (elem) elem.style.visibility = 'hidden';</script>";
        }

        //echo "<iframe name=ifr1 style='display: none; width: 90%'></iframe>";
        if (USER_USER == $_SESSION['user_role'])
            $strtitle = "Источник рекламы:&nbsp;";
        else {
            $strtitle = "";
            //if ($srm_id >= SOURCE_2GIS) $strtitle .= "<br/>"; // предыдущий селект без детализации
            $strtitle .= "Источник рекламы (исх.):&nbsp;";
        }
        echo "<script>elem = parent.document.getElementById('SelectReservoirT'); if (elem) elem.innerHTML='" . $strtitle . "';</script>";

        /*if (SERVICE_KOSM == $srv_id || SERVICE_PLAS == $srv_id || SERVICE_TRIH == $srv_id)
            $strfilt = "instr(in_dep, ".$srv_id.") != 0";
        else $strfilt = "instr(in_dep, '-1') != 0";
        if (GetData::GetIstochnik(FALSE, FALSE, $strfilt, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE)) > 0) {*/
        //echo "<select id='Reservoir_new' name='Reservoir_new' onchange='ifr1.location=\"".PATH."/med_call_out.php?getdet=\"+this.value'";

        //if (USER_ADMIN == $_SESSION['user_role'])
        //$strfiltDetail = NULL;
        /*elseif (SERVICE_STOM == $srv_id)
            $strfiltDetail = "instr(service_ids, '-1') != 0";*/
        //else $strfiltDetail = "instr(service_ids, " . $getserv . ") != 0";
        //$strfiltDetail = "sad.SOURCE_AUTO_ID=".$sra_id." and instr(service_ids, " . $getserv . ") != 0";
        //$RowsAutoDetail = GetData::GetSourceAutoDetail(FALSE, FALSE, $strfiltDetail, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        $strfiltDetail = "saa.SOURCE_AUTO_ID=".$sra_id." and instr(saa.service_ids, " . $getserv . ") != 0";
        $RowsAutoDetail = GetData::GetSourceAlloc(FALSE, FALSE, $strfiltDetail, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        $sel = "<select id=\"Reservoir_new\" name=\"Reservoir_new\" onchange=\"ResSelected($status_id_unic);\"";
        if ($RowsAutoDetail > 0) {
            if (isset($srm_id_new) && $srm_id_new > 0)
                $sel .= " style=\"max-width: 500px;\"";
            else $sel .= " style=\"max-width: 500px; background-color:" . needs . "\"";
        }
        else //if ($RowsAutoDetail == 0 || $status_id_unic >= STATUS_CALL_STOP /*|| USER_VIEW == $_SESSION['user_role'] && STATUS_OPEN == $status_id_unic*/)
            $sel .= " disabled";
        $sel .= ">";
        if ($RowsAutoDetail > 0 && ($status_id_unic < STATUS_CALL_STOP || isset($srm_id_new) && $srm_id_new != 0))
            $sel .= "<option value=\"" . SOURCE_NOT . "\">Выберите источник рекламы</option>";
        else $sel .= "<option value=\"\">Источник не указан</option>";
        if ($RowsAutoDetail > 0) {
            //foreach (GetData::$array_sa_detail as $key => $value)
            foreach(GetData::$array_source_alloc as $key => $value)
            {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                if (isset($srm_id_new) && $srm_id_new == $value['SA_DETAIL_ID'])
                    $sel .= "<option value=\"".$value['SA_DETAIL_ID']."\" selected=\"selected\">".$value['NAME']."</option>";
                else $sel .= "<option value=\"".$value['SA_DETAIL_ID']."\">".$value['NAME']."</option>";
            }
        }
        $sel .= "</select>";
        /*if (!isset($srm_id_new))
            $sel .= "<script>$('#Reservoir_new').val('" . SOURCE_NOT . "').change();</script>";
        else $sel .= "<script>$('#Reservoir_new').val('" . $srm_id_new . "').change();</script>";*/

        if (preg_match('/(?i)msie [10]/', $_SERVER['HTTP_USER_AGENT']) ||  // Долбаный IE 10 !!!
            preg_match('/(?i)msie [1-9]/', $_SERVER['HTTP_USER_AGENT']))
            $sel .= "<br/>";


        echo "<script>elem = parent.document.getElementById('SelectReservoir'); if (elem) elem.innerHTML='" . $sel . "';</script>";
        echo "<script>elem = parent.document.getElementById('Reservoir_new'); if (elem) elem.focus();</script>";

        exit();
    }
echo "</head>";

if ("POST" == $_SERVER["REQUEST_METHOD"] && isset($save_but)) {
    // Принимаем новые данные из формы
    $service_new = (isset($ServiceId) ? $ServiceId : NULL); // тут Id измененной Услуги
    $reservoir_new = (isset($Reservoir_new) && SOURCE_NOT != $Reservoir_new ? $Reservoir_new : "NULL"); // тут Id повторного Источника
    //$ages = "NULL"; //(isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : "NULL");
    $surname = (isset($surname) ? stripcslashes(htmlspecialchars($surname,ENT_QUOTES)) : NULL);
    $phone_mob = (isset($phone_mob) ? $phone_mob : "");
    $phone_new = (isset($phone_new1) ? $phone_new1 : "");
    $phone_new2 = (isset($phone_new2) ? $phone_new2 : "");
    //$email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : NULL); // none@wilstream

    $source_man_det_new = "NULL"; //DETAILS_OTHER или пустое лучше сделать?
    if ($reservoir_new < SOURCE_2GIS) { // Что-то из списков
        if (isset($DetailList)) {
            $source_man_det_new = $DetailList;
        }
    }

    if (SERVICE_GINE != $service_new && isset($ServiceStom)) // Детализация теперь для всех кроме гинекологии
        $service_det_new = $ServiceStom;
    else $service_det_new = NULL;

    $StatusId = (isset($StatusId) ? $StatusId : STATUS_OPEN);
    $UserId = (isset($UserId) ? $UserId : $_SESSION['login_id_med']); // кто выбран или кто меняет статус
    $call_back = (isset($call_back_picker) ? $call_back_picker : "");
    $Clinic = (isset($Clinic) ? $Clinic : '');
    $name_cl = (isset($name_cl1) ? stripcslashes(htmlspecialchars($name_cl1,ENT_QUOTES)) : "---");
    $surname_cl = (isset($surname_cl1) ? stripcslashes(htmlspecialchars($surname_cl1,ENT_QUOTES)) : "---");
    $patronymic_cl = (isset($patronymic_cl1) ? stripcslashes(htmlspecialchars($patronymic_cl1,ENT_QUOTES)) : "---");
    $Clinic2 = (isset($Clinic2) ? $Clinic2 : '');
    $name_cl2 = (isset($name_cl2) ? stripcslashes(htmlspecialchars($name_cl2,ENT_QUOTES)) : "---");
    $surname_cl2 = (isset($surname_cl2) ? stripcslashes(htmlspecialchars($surname_cl2,ENT_QUOTES)) : "---");
    $patronymic_cl2 = (isset($patronymic_cl2) ? stripcslashes(htmlspecialchars($patronymic_cl2,ENT_QUOTES)) : "---");
    //$ages_cl = "NULL"; //(isset($_POST['ages_cl']) && $_POST['ages_cl'] != "" ? $_POST['ages_cl'] : 0);
    $comment_cl = (isset($comment_cl) ? htmlspecialchars($comment_cl, ENT_QUOTES) : "");
    $fio = $surname;// . "/" . $name . "/" . $patronymic;

    //Вставляем данные
    $query_result = FALSE;
    $date_write = date("d-m-Y H:i:s");
    $phone_mob_norm = phone_norm_single($phone_mob, 'ru_dial'); //'8'.preg_replace("/\D/", "", $phone_mob);
    $phone_mob = phone_segment($phone_mob_norm, NULL);
    $phone_new_norm = phone_norm_single($phone_new, 'ru_dial');
    $phone_new = phone_segment($phone_new_norm, NULL);
    if (isset($service_new) && NULL != $service_new && $service_new != $serv_id) { // изменение услуги
        $StatusId = STATUS_OPEN;
    }
    if (FALSE == DEBUG_MODE) {
        $table_name = 'CALL_BASE';
        $lock_table = 'CALL_BASE_LOCK';
        $table_hist = 'CALL_BASE_HIST';
        $seq_hist = 'SEQ_CALL_BASE_HIST_ID.nextval';
        $table_cl = 'CALL_BASE_CLINIC';
        $seq_cl = 'SEQ_CALL_BASE_CLINIC_ID.nextval';
    }
    else {
        $table_name = 'CALL_BASE_TEST';
        $lock_table = 'CALL_BASE_LOCK_TEST';
        $table_hist = 'CALL_BASE_HIST_TEST';
        $seq_hist = 'SEQ_CALL_BASE_HIST_ID_TEST.nextval';
        $table_cl = 'CALL_BASE_CLINIC_TEST';
        $seq_cl = 'SEQ_CALL_BASE_CLINIC_ID_TEST.nextval';
    }

    if (STATUS_NOT == $StatusId) $StatusId = $status_id_unic; // strange zero in status_id sometimes

    // для спецпользователя надо получить ID оператора, сделавшего последнее изменение и время+30 секунд
    $change_user = $_SESSION['login_id_med'];
    if (SPEC_USER == $_SESSION['login_id_med'] || $_SESSION['on_duty_today']) {
        $getstr = "select USER_ID, to_char(DATE_DET+30/86400,'dd.mm.yyyy hh24:mi:ss') as NEW_DATE from ".$table_hist.
            " where base_id = ".$Base_Id." order by id desc";
        $query = OCIParse($c, $getstr);
        if (OCIExecute($query)) {
            $result = OCI_Fetch_Array($query);
            $change_user = $result['USER_ID'];
            $change_date = $result['NEW_DATE'];
        }
        oci_free_statement($query);
    }

    $updatestr = "UPDATE ".$table_name." SET PHONE_NEW = '{$phone_new}', PHONE_NEW_NORM = '{$phone_new_norm}'";
    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
        $updatestr .= ", SECOND_STATUS_ID = {$StatusId}, SECOND_LAST_CHANGE = to_date('{$date_write}','DD.MM.YYYY hh24:mi:ss')";
    else $updatestr .= ", STATUS_ID = {$StatusId}, LAST_CHANGE = to_date('{$date_write}','DD.MM.YYYY hh24:mi:ss')";

    if (isset($reservoir_new) && NULL != $reservoir_new && 'NULL' != $reservoir_new && 0 != $reservoir_new)
        $updatestr .= ", SOURCE_MAN_ID_NEW = {$reservoir_new}";
    if (isset($source_man_det_new) && NULL != $source_man_det_new && 'NULL' != $source_man_det_new)
        $updatestr .= ", SOURCE_MAN_DET_ID_NEW = {$source_man_det_new}";
    if (isset($DoubleId) && NULL != $DoubleId)
        $updatestr .= ", CALL_DOUBLE = {$DoubleId}";
    if (isset($service_new) && NULL != $service_new && $service_new != $serv_id) { // изменение услуги
        $updatestr .= ", SERVICE_ID = {$service_new}";
    }
    if ($StatusId > STATUS_WORK && isset($service_det_new)) { // изменение детализации услуги
        $updatestr .= ", SERVICE_DET_ID = '{$service_det_new}'";
    }
    if (isset($interstate))
        $updatestr .= ", INTERSTATE = 1";
    else $updatestr .= ", INTERSTATE = NULL";
    if (DEVICE_MAIL == $source_type) {
        $updatestr .= ", PHONE_MOB = '{$phone_mob}', PHONE_MOB_NORM = '{$phone_mob_norm}', CLIENT_NAME = '{$fio}'";
    }
    if (STATUS_OPEN != $StatusId) { // для всех изменений статуса, кроме переоткрытия записываем кому назначено или кто изменил статус
        if (SPEC_USER != $_SESSION['login_id_med'] /*&& !$_SESSION['on_duty_today']*/) {
            if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
                if (STATUS_WORK == $StatusId) {
                    $updatestr .= ", SECOND_FIO_ID = {$UserId}";
                }
                else {
                    if ($_SESSION['on_duty_today'])
                        $updatestr .= ", SECOND_FIO_ID = '{$change_user}'";
                    else $updatestr .= ", SECOND_FIO_ID = '{$_SESSION['login_id_med']}'";
                }
            } else {
                if (STATUS_WORK == $StatusId) {
                    $updatestr .= ", FIO_ID = {$UserId}";
                }
                else {
                    if ($_SESSION['on_duty_today'])
                        $updatestr .= ", FIO_ID = '{$change_user}'";
                    else $updatestr .= ", FIO_ID = '{$_SESSION['login_id_med']}'";
                }
            }
        }
    }
    else {
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $updatestr .= ", SECOND_FIO_ID = ''";
        else $updatestr .= ", FIO_ID = ''";
    }

    if (STATUS_ERROR == $StatusId) {
        if (isset($status_det)) { // может быть детализации ошибки
            if (in_array($_SESSION['login_id_med'], SPEC_USER_CALL))
                $updatestr .= ", SECOND_STATUS_DET_ID = '{$status_det}'";
            else $updatestr .= ", STATUS_DET_ID = '{$status_det}'";
        }
    }
    else {
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
            $updatestr .= ", SECOND_STATUS_DET_ID = ''";
        else $updatestr .= ", STATUS_DET_ID = ''";
    }

    if (isset($call_back) && $call_back != "") { //STATUS_CALL_BACK == $StatusId) // надо перезвонить
        $updatestr .= ", CALL_BACK_DATE = to_date('{$call_back}','DD.MM.YYYY hh24:mi:ss'), CALL_BACK_NUM = CALL_BACK_NUM-1";
    }
    else {
        $updatestr .= ", CALL_BACK_DATE = '', CALL_BACK_NUM = ''";
    }

    if (STATUS_CLINIC == $StatusId || STATUS_CLINIC_NOT == $StatusId ||
        STATUS_REPEAT == $StatusId || STATUS_ERROR == $StatusId ||
        STATUS_BREAK_LINE == $StatusId) { // Закрываем звонок
        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
            $updatestr .= ", DATE_SECOND_CLOSE = to_date('{$date_write}','DD.MM.YYYY hh24:mi:ss')";
            $q_lock = "update ".$lock_table." SET lock_date_end = sysdate WHERE base_id = {$Base_Id} and user_id = {$_SESSION['login_id_med']}";
            $query_lock = OCIParse($c, $q_lock);
            if (OCIExecute($query_lock)) {
                OCICommit($c);
            }
        }
        else $updatestr .= ", DATE_CLOSE = to_date('{$date_write}','DD.MM.YYYY hh24:mi:ss')";
    }
    $updatestr .= " WHERE ID = " . $Base_Id;
if (TRUE == DEBUG_MODE) echo "<textarea>" . $updatestr . "</textarea><br/>";

    GetData::my_log($updatestr,FALSE);
    $query = OCIParse($c, $updatestr);
    $query_result = OCIExecute($query);
    if (!$query_result)
        GetData::my_log($updatestr,TRUE);

    if ($query_result) { // добавляем строку истории по этому звонку с ID исходящего оператора
        if (isset($call_back) && $call_back != "") //STATUS_CALL_BACK == $StatusId) // надо перезвонить
            $full_comment = "(c_b=" . $call_back . ") " . $comment_cl;
        elseif (STATUS_WORK == $StatusId)
            $full_comment = "(fio_id=" . $UserId . ") " . $comment_cl;
        else $full_comment = $comment_cl;

        if (SPEC_USER == $_SESSION['login_id_med'] || $_SESSION['on_duty_today'])
            $insertstr = "INSERT INTO ".$table_hist." (ID, BASE_ID, USER_ID, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
                VALUES (".$seq_hist.", ".$Base_Id.", '".$change_user."', 
                ".$StatusId.", to_date('".$change_date."','DD.MM.YYYY hh24:mi:ss'), '{$full_comment}', 
                to_date('{$change_date}','DD.MM.YYYY hh24:mi:ss'))";
        else $insertstr = "INSERT INTO ".$table_hist." (ID, BASE_ID, USER_ID, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
                VALUES (".$seq_hist.", ".$Base_Id.", ".$_SESSION['login_id_med'].", 
                ".$StatusId.", to_date('{$date_write}','DD.MM.YYYY hh24:mi:ss'), '{$full_comment}', 
                to_date('{$date_start_work}','DD.MM.YYYY hh24:mi:ss'))";
        if (TRUE == DEBUG_MODE)
            echo "<textarea> user_id:".$_SESSION['login_id_med']." query:". $insertstr . "</textarea><br/>";
        GetData::my_log($insertstr,FALSE);
        $query = OCIParse($c, $insertstr);
        $query_result = OCIExecute($query);
        if (!$query_result)
            GetData::my_log($insertstr,TRUE);
    }
    // добавляем строку записи в клинику по этому звонку.
    if ($query_result && STATUS_CLINIC == $StatusId /*&& SPEC_USER != $_SESSION['login_id_med']*/) { // спецпользователь тоже может записать при исправлении
        $fio_cl = $surname_cl . "/" . $name_cl . "/" . $patronymic_cl;
        // проверяем существование записи для этой заявки
        $checkstr = "select ID from ".$table_cl." where BASE_ID=".$Base_Id." and HOSPITAL_ID=".$Clinic." and CLIENT_NAME='".$fio_cl."'";
        $q=OCIParse($c, $checkstr);
        if(OCIExecute($q) && OCIFetch($q)) {
            $write_id=OCIResult($q,"ID");

            $insertclinic = "UPDATE " . $table_cl . " SET CLIENT_PHONE = '".$phone_new."', CLIENT_DATE = to_date('".$write_cl_picker."','DD.MM.YYYY hh24:mi')
            WHERE ID = ".$write_id;
if (TRUE == DEBUG_MODE) echo "<textarea>" . $insertclinic . "</textarea><br/>";
            GetData::my_log($insertclinic, FALSE);
            $query = OCIParse($c, $insertclinic);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($insertclinic, TRUE);
        }
        else {
            $insertclinic = "INSERT INTO " . $table_cl . " (ID, BASE_ID, HOSPITAL_ID, CLIENT_NAME, CLIENT_PHONE, CLIENT_DATE, WRITE_ADD) 
            VALUES (" . $seq_cl . ", " . $Base_Id . ", " . $Clinic . ", '{$fio_cl}', '{$phone_new}', 
            to_date('{$write_cl_picker}','DD.MM.YYYY hh24:mi'), sysdate)";
//            to_date('{$write_cl_picker}','DD.MM.YYYY hh24:mi')) returning ID into :max_clinic_id1";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $insertclinic . "</textarea><br/>";
            GetData::my_log($insertclinic, FALSE);
            $query = OCIParse($c, $insertclinic);
            //OCIBindByName($query,":max_clinic_id1",$max_clinic_id1,16);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($insertclinic, TRUE);
        }

        if (isset($show_second)) { // добавляем вторую строку записи в клинику по этому звонку.
            $fio_cl2 = $surname_cl2 . "/" . $name_cl2 . "/" . $patronymic_cl2;

            // проверяем существование второй записи для этой заявки
            $checkstr = "select ID from ".$table_cl." where BASE_ID=".$Base_Id." and HOSPITAL_ID=".$Clinic2." and CLIENT_NAME='".$fio_cl2."'";
            $q=OCIParse($c, $checkstr);
            if(OCIExecute($q) && OCIFetch($q)) {
                $write_id=OCIResult($q,"ID");

                $insertclinic = "UPDATE " . $table_cl . " SET CLIENT_PHONE = '".$phone_new2."', CLIENT_DATE = to_date('".$write_cl_picker2."','DD.MM.YYYY hh24:mi')
            WHERE ID = ".$write_id;
if (TRUE == DEBUG_MODE) echo "<textarea>" . $insertclinic . "</textarea><br/>";
                GetData::my_log($insertclinic, FALSE);
                $query = OCIParse($c, $insertclinic);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($insertclinic, TRUE);
            }
            else {
                $insertclinic = "INSERT INTO " . $table_cl . " (ID, BASE_ID, HOSPITAL_ID, CLIENT_NAME, CLIENT_PHONE, CLIENT_DATE, WRITE_ADD) 
                VALUES (" . $seq_cl . ", " . $Base_Id . ", " . $Clinic2 . ", '{$fio_cl2}', '{$phone_new2}', 
                to_date('{$write_cl_picker2}','DD.MM.YYYY hh24:mi'), sysdate)";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $insertclinic . "</textarea><br/>";
                GetData::my_log($insertclinic, FALSE);
                $query = OCIParse($c, $insertclinic);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($insertclinic, TRUE);
            }
        }
    }

    // списание затрат на заявку для качественных статусов
    $amount = $suppl_id = 0; // не проводить операции, если сумма списания нулевая ?
    //if ((STATUS_CLINIC == $StatusId || STATUS_CLINIC_NOT == $StatusId || STATUS_CALL_BACK == $StatusId) && // качественная заявка
      //  (!isset($pay_supplier) || $pay_supplier == 0))
    {
        $sqlstr = "SELECT COST_ORDER, COST_VISIT, sa.SUPPLIER_ID FROM SOURCE_AUTO_COST sac ";
        $sqlstr .= " LEFT JOIN SOURCE_AUTO sa ON sa.ID = sac.SOURCE_AUTO_ID";
        $sqlstr .= " WHERE sac.DELETED is NULL and sac.SOURCE_AUTO_ID=".$sra_id;
        if (TRUE == DEBUG_MODE) echo "<textarea>" . $sqlstr . "</textarea><br/>";
        $query = OCIParse(GetData::GetConnect(), $sqlstr);
        if (OCIExecute($query)) {
            if ($result = OCI_Fetch_Array($query)) {
                $suppl_id = $result['SUPPLIER_ID'];
                $amount = $result['COST_ORDER'];
                //$amount_visit = $result['COST_VISIT']; // тут не используем, ибо не пришел еще пациент
            }
        }
    }

    if (USER_ADMIN == $_SESSION['user_role'] || in_array($_SESSION['login_id_med'], COST_EDIT)) {
        if ((STATUS_CLINIC == $StatusId || STATUS_CLINIC_NOT == $StatusId || STATUS_CALL_BACK == $StatusId) &&
            (!isset($pay_supplier) || $pay_supplier == 0) && // переводим в качественный статус
            !isset($interstate) && (!isset($DoubleId) || $DoubleId == 1)) {
            if ($amount > 0) {
                $upd_pay = "UPDATE " . $table_name . " SET PAY_SUPPLIER = '{$amount}' WHERE ID = '{$Base_Id}'";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);

                $upd_pay = "UPDATE SUPPLIERS SET BALANCE = BALANCE - '{$amount}' WHERE ID = '{$suppl_id}'";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);
            }
        }
        elseif (isset($pay_supplier) && $pay_supplier > 0 &&
            (STATUS_CLINIC != $StatusId && STATUS_CLINIC_NOT != $StatusId && STATUS_CALL_BACK != $StatusId ||
            isset($interstate) || (isset($DoubleId) && $DoubleId == 2))) {
                $upd_pay = "UPDATE " . $table_name . " SET PAY_SUPPLIER = 0 WHERE ID = '{$Base_Id}'"; // некачественный - не списываем
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);

                $upd_pay = "UPDATE SUPPLIERS SET BALANCE = BALANCE + '{$amount}' WHERE ID = '{$suppl_id}'"; // возвращаем деньги
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);
        }
    }
    else {
        if ((STATUS_CLINIC == $StatusId || STATUS_CLINIC_NOT == $StatusId || STATUS_CALL_BACK == $StatusId) && // качественная заявка
            (!isset($pay_supplier) || $pay_supplier == 0) && // еще не уплочено
            !isset($interstate) && (!isset($DoubleId) || $DoubleId == 1))
        {
            if ($amount > 0) {
                $upd_pay = "UPDATE " . $table_name . " SET PAY_SUPPLIER = '{$amount}' WHERE ID = '{$Base_Id}'";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);

                $upd_pay = "UPDATE SUPPLIERS SET BALANCE = BALANCE - '{$amount}' WHERE ID = '{$suppl_id}'";
if (TRUE == DEBUG_MODE) echo "<textarea>" . $upd_pay . "</textarea><br/>";
                GetData::my_log($upd_pay,FALSE);
                $query = OCIParse($c, $upd_pay);
                $query_result = OCIExecute($query);
                if (!$query_result)
                    GetData::my_log($upd_pay,TRUE);
            }
        }
    }
    oci_free_statement($query);

    if ($query_result) {
        echo "<p style='font-size: larger; color: green'>Данные успешно обновлены.</p>";
        echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close(); window.opener.location.reload();'>Закрыть</button>";
        /* print "<script language='Javascript'>function close_win() { window.close(); } setTimeout('close_win()', 100);</script>*/
    } else {
        echo "<p style='font-size: larger; color: red'>Произошла ошибка изменения данных!</p>";
    }
    unset($save_but);
    if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
        $_SESSION['reload_at_save'] = TRUE;

    exit;
}
?>

<body>
<?php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] == USER_VIEW && !in_array($_SESSION['login_id_med'], SPEC_USER_VIEW)) { // обозревателю детализация не полагается?
    echo "<b style='color: red'>ОШИБКА: У Вас нет прав для просмотра данной страницы.</b>";
    exit();
}
if (!isset($base_id) or $base_id == '') { exit(); }

if (GetData::GetAccess($_SESSION['login_id_med']) > 0 && isset($_SESSION['data_acc'])) // Права доступа к разным данным
    $data_acc_arr = explode(',', $_SESSION['data_acc']);
else $data_acc_arr = array();
?>
<script>
    //-----------------------------------------------------------------
    //ФУНКЦИИ РАСКРАСКИ СТРОК В РОДИТЕЛЬСКОМ ОКНЕ
    var base_id='<?php echo $base_id;?>';
    var isfocus = true;
    if (window.opener && window.opener.click_row) window.opener.click_row('row_' + base_id); else null;
    window.onbeforeunload = function () {
        if (window.opener && window.opener.unclick_row) window.opener.unclick_row('row_' + base_id); else null;
    };
    window.onfocus = function () {
        isfocus = true;
        //if(window.opener && window.opener.sel_click_row) window.opener.sel_click_row('row_'+base_id); else null;
    };
    window.onblur = function () {
        isfocus = false;
        //if(window.opener && window.opener.unsel_click_row) window.opener.unsel_click_row('row_'+base_id); else null;
    };
    setInterval("parent_color('row_'+base_id)", 200);

    //setInterval("if(window.opener && window.opener.click_row) window.opener.click_row('row_'+base_id); else null;",300);
    function parent_color(row_id) {
        if (window.opener && window.opener.click_row) {
            if (isfocus)
                window.opener.sel_click_row('row_' + base_id);
            else window.opener.unsel_click_row('row_' + base_id);
        }
        //else null;
    }
    //-----------------------------------------------------------------
</script>

<?php
//информация о заявке
include("./call_view/call.get.call.info.php");
extract(get_call_info($c, $base_id));
if (isset($error)) {echo $error; exit();}
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
?>

<?php
if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
    $status_id_unic = $status_id_sec;
else $status_id_unic = $status_id;

if (isset($bnumber)) {
    $nrowsAuto = GetData::GetSourceAuto("DELETED IS NULL", $bnumber, FALSE);
    if (isset(GetData::$array_source_auto)) {
        $source_auto_id = GetData::$array_source_auto[0];
        if (TRUE == ENCODE_UTF)
            $source_auto_name = iconv('windows-1251', 'utf-8', GetData::$array_source_auto[2]);
        else $source_auto_name = GetData::$array_source_auto[2];
        $source_auto_type = GetData::$array_source_auto[3];
    }
    else {
        $source_auto_id = 0;
        $source_auto_name = "???";
        $source_auto_type = DEVICE_PHONE;
    }
}
else {
    if (TRUE == ENCODE_UTF)
        $source_auto_name = iconv('utf-8', 'windows-1251', $sraname);
    else $source_auto_name = $sraname;
    $source_auto_type = $sratype;
}

if (($status_id_unic >= STATUS_CALL_STOP || CALL_SECOND == $call_double || isset($interstate) && 1 == $interstate) &&
    (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])) {
    $mess_subj = "Заявка: ".$base_id.". Маршрутный номер: ".(DEVICE_PHONE == $source_auto_type ? $bnumber : $source_auto_name);
    $mess_subj_smtp = "Заявка: ".$base_id.". ".$srvname.".".(DEVICE_PHONE == $source_auto_type ? " Маршрутный: ".$bnumber."." : "")." ".$source_auto_name.".";

    // текст письма
    $mess = "<table>
        <tr><th>ID заявки:</th><th style='text-align: left;'>".$base_id."</th></tr>
        <tr><td><b>Дата заявки:</b></td><td>".$date_call."</td></tr>";
    if (isset($call_double) && CALL_SECOND == $call_double)
        $mess .= "<tr><td><b>Статус заявки:</b></td><td><b>!Дубль!</b></td></tr>";
    elseif (isset($interstate) && 1 == $interstate)
        $mess .= "<tr><td><b>Статус заявки:</b></td><td><b>!Межгород!</b></td></tr>";
    else $mess .= "<tr><td><b>Статус заявки:</b></td><td>".$status_name."</td></tr>";
    if (STATUS_ERROR == $status_id_unic)
        $mess .= "<tr><td><b>Причина ошибки:</b></td><td>".$status_det_name."</td></tr>";
    $mess .= "<tr><td><b>Источник рекламы (Авто):</b></td><td>".$sraname."</td></tr>";
    $mess .= "<tr><td><b>Источник рекламы (вход):</b></td><td>".$srmname;
    if (isset($srdetname) && "NULL" != $srdetname && strlen($srdetname) > 0)
        $mess .= " (".$srdetname.")";
    $mess .= "</td></tr>";
    $mess .= "<tr><td><b>Источник рекламы (исх.):</b></td><td>".$srmname_new."</td></tr>";
    $mess .= "<tr><td><b>Услуга:</b></td><td>".$srvname."</td></tr>
        <tr><td><b>Тип источника:</b></td><td>".$st."</td></tr>";
    if (DEVICE_PHONE == $source_auto_type) {
        $mess .= "<tr><td><b>Направление звонка:</b></td><td>" . CALL_WAY[$call_direction] . "</td></tr>
        <tr><td><b>АОН:</b></td><td>" . $anumber . "</td></tr>
        <tr><td><b>Маршрутный номер:</b></td><td>" . $bnumber . "</td></tr>
        <tr><td><b>Оператор:</b></td><td>" . $sc_agid . "</td></tr>";
    }
    $mess .= "<tr><td><b>ФИО:</b></td><td>".$client_name."</td></tr>
        <tr><td><b>Телефон:</b></td><td>".$phone_mob."</td></tr>";
    if (RESULT_KC == $result_id)
        $mess .= "<tr><td><b>Соединили с:</b></td><td>" . $result_det."</td></tr>";
    //$mess .= "<tr><td><b>Комментарий:</b></td><td>".$comment."</td></tr>";
    $mess .= "<tr><td><b>Комментарий:</b></td><td>".(isset($mess_comment)?$mess_comment:"")."</td></tr>";
    
	if($secret<>'') 
		$mess_link_record = "<tr><td><b>Запись звонков:</b></td><td><a href='http://med.wilstream.ru/get_record.php?baseid=".$base_id."&secret=".$secret."' target='_blank'><b>Ссылка на записи</b></a></td></tr>";	
    else 
		$mess_link_record='';
	
	//$mess .= "</table>"; //таблицу закроем при отправке письма
//$mess = htmlspecialchars($mess);
    $body = "ID заявки: " .$base_id.chr(13).
        "\nДата заявки: " . $date_call.chr(13);
    if (isset($call_double) && CALL_SECOND == $call_double)
        $body .= "\nСтатус заявки: !Дубль!" . chr(13);
    elseif (isset($interstate) && 1 == $interstate)
        $body .= "\nСтатус заявки: !Межгород!" . chr(13);
    else $body .= "\nСтатус заявки: " . $status_name.chr(13);
    if (STATUS_ERROR == $status_id_unic)
        "\nПричина ошибки: " . $status_det_name.chr(13);
    $body .= " ".chr(13);
    $body .= "\nИсточник(Авто): " . $sraname.chr(13).
        "\nИсточник рекламы (вход): " . $srmname;
    if (isset($srdetname) && "NULL" != $srdetname && strlen($srdetname) > 0)
        $body .= " (".$srdetname.")";
    $body .= "\nИсточник рекламы (исх.): ".$srmname_new.chr(13);
    $body .= " ".chr(13);
    $body .= "\nУслуга: " . $srvname.chr(13)." ".chr(13);
    $body .= "\nТип источника: " . $st.chr(13);
    //$body1 = "";
    if (DEVICE_PHONE == $source_auto_type) {
        $body .= "\nНаправление звонка: ".CALL_WAY[$call_direction].chr(13).
            "\nМаршрутный номер: " . $bnumber.chr(13).
            "\nАОН: " . $anumber.chr(13).
            "\nОператор: " . $sc_agid.chr(13);
    }
    $body .= " ".chr(13);
    $body .= "\nФИО: " . $client_name.chr(13).
        "\nТелефон: " . $phone_mob.chr(13); //(Стародубов) здесь, как договорились, надо брать нормализованный номер и прогонять его через функцию phone_segment($phone_norm) из файла test_mailto.php
    if (RESULT_KC == $result_id)
        $body .= "\nСоединили с: " . $result_det.chr(13);
    $body .= "\nКомментарий: " . $comment; //.chr(13)."."; (Стародубов) перевод строки и точка тут не нужны, эта последовательность нужна только в SMTP-диалоге для завершения передачи данных.
    //$body .= $body1;
    $body = str_replace(array(chr(13),chr(10)),array("%0D",""),$body);
    $body = substr($body,0,2152);

    /*echo "<iframe name=ifr2 style='display: none; width: 50%'></iframe>";
    echo '<form action="mailto:?subject='.$mess_subj.'&body='.$body.'" method="post" enctype="text/html" style="position: absolute; top: 30px; margin-left: 505px;">';
    echo "<button type='submit' onclick='ifr2.location=\".".PATH."/med_call_out.php?send_mail=\"+$base_id' style='width: 60px; height: 40px; color: forestgreen'>
    <img style='height: 90%' src='".PATH."/images/envelope1.png' title='Отправить письмо' alt='Отправить письмо'>
    </button>";
    //echo '<input type="hidden" name="body" value="'.$body1.'"/>';
    echo '</form>';*/
}
?>

<div style='display: inline-block;'>
    <?php
    //$to_day = date("Y-m-d HH:MM");
    echo "<h1 class='heading' style='margin-top: -5px; margin-bottom: 1px;'>Входящая заявка (№ ".$base_id.")";
    if (DEVICE_PHONE == $st_id) {
        echo ". Звонок - ".$sc_call_id;
        $yesterday_date=date('Y-m-d',mktime(0,0,0,date("m"),date("d")-1,date("Y")));
        if ( $status_id_unic <= STATUS_CALL_NOT && isset($transfer_num) && '' != $transfer_num && date('Y-m-d',strtotime ($date_call)) > $yesterday_date )
            echo ".<br/><span style='color: brown;'>&nbsp;Код перевода - </span><span style='color: maroon; border-bottom: dashed'>".substr($transfer_num, -4, 4)."</span>";
    }
    if (STATUS_CALL_STOP <= $status_id_unic && STATUS_NOT_COME != $status_id_unic)
        echo "<span style='color: firebrick;'> (закрыто)</span>";
    echo "</h1>";
    if (SERVICE_STOM == $srv_id) $date_stop = $date_call;
    else $date_stop = $last_change;
    ?>
</div>
<div>
    <!--form action="< ?=PATH?>/med_form_out.php" method="post"-->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
        <h2 style="display: inline;">
            <?php
            echo "<label for='PurposeId'>Тема заявки:&nbsp;</label>";
            if (GetData::GetThemes("DELETED IS NULL") > 0) {
                echo "<select id='PurposeId' name='PurposeId' title='Темы заявок' disabled>";
                foreach(GetData::$array_theme as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    if (isset($theme_id) && $theme_id == $value['ID'])
                        echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                    else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
                echo "</select>";
            }
            if (DEVICE_MAIL != $source_auto_type
                /*&& (STATUS_CALL_STOP > $status_id_unic || STATUS_NOT_COME == $status_id_unic || USER_ADMIN == $_SESSION['user_role'])*/) {
                if (trim($phone_new) == '') {
                    $phone_call = phone_segment(trim($anumber), NULL);
                } else {
                    $phone_call = phone_segment(trim($phone_new_norm), NULL);
                }
                echo " АОН: " . $phone_call;
                //} else $phone_call = '';
                $phone_call_norm = phone_norm_single($phone_call, 'ru_dial');
                if (strlen($phone_call_norm) >= 11 && strlen($phone_call_norm) <= 18) {
                    echo "<input disabled id='callto_button_aon' type='button' onclick='this.disabled=true; callto(" . $phone_call_norm . "," . $base_id . ")' value='    Позвонить' title='" . $phone_call . "' 
style='height: 25px; background-image: url(\"" . PATH . "/images/call.png\"); background-repeat: no-repeat;' />";
//style='background-color: darkgreen; color: wheat; font-weight: bold' />;
                    echo "<input disabled id='endcall_button_aon' type='button' onclick='endcall()' value='    Завершить звонок'  
style='height: 25px; background-image: url(\"" . PATH . "/images/call_stop.png\"); background-repeat: no-repeat;' />";
//style='background-color: red; color: wheat'/>";
                    echo "<input type='text' id='oktell_server_status_aon' style='color: crimson;' disabled>";
                } else {
                    echo "<input disabled id='call_not_button_aon' type='button' value='Некорректный номер. Звонок невозможен.' title='" . $phone_call . "' style='height: 25px; font-weight: bold' />";
                }
            }
            echo "<br/>";

            echo "<iframe name='ifr_res' style='display: none; width: 90%'></iframe>"; // sosed
            echo "<label id='ServiceT' for='ServiceId'>Услуги:&nbsp;</label>";
            if (GetData::GetServices(FALSE,FALSE,NULL,FALSE) > 0) {
                //echo "<select id='ServiceId' name='ServiceId' title='Услуги' onchange='ServiceChanged();'";
                if (!isset($srm_id_new)) $srm_id_new = 0;
                echo "<select id='ServiceId' name='ServiceId' title='Услуги' 
onchange='ifr_res.location=\"".PATH."/med_call_out.php?getserv=\"+this.value+\"&srv_id=".$srv_id."&srv_det_id=".$srv_det_id."&sra_id=".$sra_id."&srm_id=".$srm_id."&status_id_unic=".$status_id_unic."&srm_id_new=".$srm_id_new."\"'";
                if (($status_id_unic >= STATUS_CALL_STOP || in_array($_SESSION['login_id_med'], SPEC_USER_VIEW))
                    && SPEC_USER != $_SESSION['login_id_med'])
                    echo " disabled";
                echo ">";
                foreach(GetData::$array_services as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
                echo "</select>";
                if (isset($srv_id))
                    echo "<script>$('#ServiceId').val('".$srv_id."').change();</script>";

                echo "<label id='ServiceStomT' for='ServiceStom'>&nbsp;</label>";
                echo "<select id='ServiceStom' name='ServiceStom' title='Стоматология'";
                if (/*$status_id_unic >= STATUS_CALL_STOP ||*/ USER_SUPER == $_SESSION['user_role'] && STATUS_OPEN == $status_id_unic)
                    echo " disabled";
                if (SERVICE_STOM != $srv_id) {
                    echo " style='visibility: hidden'";
                } else {
                    if (STOM_NOT == $srv_det_id || NULL == $srv_det_id)
                        echo " style='background-color:" . needs . "'";
                }
                echo ">";
                if (GetData::GetServiceDetails(NULL, $srv_id, FALSE) > 0) // пока только стоматология
                //if (GetData::GetServiceDetails(NULL, SERVICE_STOM, FALSE) > 0)
                {
                echo "<option value='" . STOM_NOT . "'>Выберите уточнение</option>";
                foreach(GetData::$arr_service_det as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
                }
                echo "</select>";
                if (isset($srv_det_id))
                    echo "<script>$('#ServiceStom').val('" . $srv_det_id . "').change();</script>";
                else echo "<script>$('#ServiceStom').val('" . STOM_NOT . "').change();</script>";
            }
            ?>
            <script type = "text/javascript">
                var select = document.getElementById("ServiceStom");
                select.onchange = function()
                {
                    if (<?=STOM_NOT?> == this.value)
                        this.style.backgroundColor = '<?=needs?>';
                    else this.style.backgroundColor = 'white';

                    var srv_det_id = '<?php echo $srv_det_id;?>';
                    var elem = this;
                    if (<?=STOM_NOT?> != this.value)
                        elem = document.getElementById('StatusId');
                    elem.focus();

                    if (<?=STOM_NOT?> != this.value && srv_det_id != this.value &&
                        <?=STATUS_NOT?> != document.getElementById("StatusId").value )
                        document.getElementById('save_but').style.visibility = 'visible';
                    else document.getElementById('save_but').style.visibility = 'hidden';
                }
            </script>
        </h2>
        <!--div id="CallType">
            <h2>
                <label for="voice">Тип звонка:&nbsp;</label>
                < ?php if (CALL_FIRST == $ct_id) { ?>
                <input type="radio" name="voice" id="FirstCall" value=< ?=CALL_FIRST?> checked disabled title="Первичный"/> Первичный
                <input type="radio" name="voice" id="SecondCall" value=< ?=CALL_SECOND?> disabled title="Повторный"/> Повторный
                < ?php } else { ?>
                <input type="radio" name="voice" id="FirstCall" value=< ?=CALL_FIRST?> disabled title="Первичный"/> Первичный
                <input type="radio" name="voice" id="SecondCall" value=< ?=CALL_SECOND?> checked disabled title="Повторный"/> Повторный
                < ?php } ?>
            </h2>
        </div-->
        <div id="NotTarget">
            <h2>
                <?php
                if (USER_ADMIN == $_SESSION['user_role'])
                    echo "<label for='Istochnik_auto'>Источник рекламы (автоопределение) (платеж: ".$pay_supplier." руб.)</label>";
                else echo "<label for='Istochnik_auto'>Источник рекламы (автоопределение):&nbsp;</label>";
                echo "<input type='text' name='Istochnik_auto' id='Istochnik_auto' style='width: 98%; margin-bottom: 7px;' placeholder='".$source_auto_name."' disabled/>";
                echo "<br/>";
                if (USER_SUPER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role']) {
                    /*if (USER_ADMIN == $_SESSION['user_role'] || 11 == $_SESSION['login_id_med']) {
                        echo "<label for='Istochnik_auto'>Источник рекламы (автоопределение):&nbsp;</label>";
                        echo "<input type='text' name='Istochnik_auto' style='width: 290px; margin-bottom: 7px;' placeholder='".$source_auto_name."' disabled/>";
                        echo "<br/>";
                    }*/
                    if (DEVICE_MAIL != $source_auto_type) {
                        if (USER_USER == $_SESSION['user_role'])
                            echo "<label for='Reservoir'>Источник рекламы:&nbsp;</label>";
                        else echo "<label for='Reservoir'>Источник рекламы (вх.):&nbsp;</label>";
                        if (GetData::GetIstochnik(FALSE, FALSE, "instr(in_dep, '-1') != 0", (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE)) > 0) {
                            echo "<select id='Reservoir' name='Reservoir' title='Источник рекламы' disabled>";
                            foreach(GetData::$array_istochnik as $key => $value) {
                                if (TRUE == ENCODE_UTF) {
                                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                                    $value['DETAIL'] = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                                }
                                if (isset($srm_id) && $value['ID'] == $srm_id)
                                    echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                                else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                            }
                            echo "</select>";
                        }

                        if ($srm_id < SOURCE_2GIS) {
                            echo "<div style='display: flex'>";
                            if (TRUE == ENCODE_UTF)
                                $srmdetail = iconv('utf-8', 'windows-1251', $srmdetail);
                            echo "<label style='position: relative; float: left' id='AllInOne' for='AllSelect'>&nbsp;" . $srmdetail . ":&nbsp;</label>";
                            echo "<div id='AllSelect' style='margin-left: 15px;'>";
                            echo "<select disabled id='Detail_Name' name='Detail_Name' style='margin-top: 0;'";
                            if ($status_id_unic >= STATUS_CALL_STOP || USER_SUPER == $_SESSION['user_role'] && STATUS_OPEN == $status_id_unic)
                                echo " disabled";
                            echo ">";
                            echo "<option value=\"0\">Детализация не выбрана</option>";
                            if (SOURCE_FLAER == $srm_id || SOURCE_CATALOG == $srm_id ||
                                SOURCE_FLAER_SUB == $srm_id || SOURCE_FLAER_CAR == $srm_id ||
                                SOURCE_LIFT == $srm_id || SOURCE_STOP == $srm_id) {
                                $nrows = GetData::GetSubway(NULL); // Все одинаково с Флаером (пока?)
                                $array_todo = GetData::$array_subway;
                            } elseif (SOURCE_SERT == $srm_id) {
                                $nrows = GetData::GetHospitals(NULL);
                                $array_todo = GetData::$array_hospitals;
                                $strtitle = 'Сертификат';
                            } else {
                                $nrows = GetData::GetSourceDetail(FALSE, NULL, $srm_id);
                                $array_todo = GetData::$array_details;
                            }
                            if ($nrows > 0) {
                                foreach ($array_todo as $key => $value) {
                                    if (TRUE == ENCODE_UTF)
                                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                                    if (isset($srdet_id) && $srdet_id == $value['ID'])
                                        echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                                    else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                                }
                            }
                            if (SOURCE_SERT == $srm_id) {
                                //printf ("<option selected=".DETAILS_PROMO == $srdet_id ? 'selected' : ''." value=".DETAILS_PROMO.">На улице у промоутера</option>");
                                //printf ("<option selected=".DETAILS_OTHER == $srdet_id ? 'selected' : ''." value=".DETAILS_OTHER.">Другое</option>");
                                if (DETAILS_PROMO == $srdet_id)
                                    echo "<option value=" . DETAILS_PROMO . " selected=\"selected\">На улице у промоутера</option>";
                                else echo "<option value=" . DETAILS_PROMO . ">На улице у промоутера</option>";
                                if (DETAILS_OTHER == $srdet_id)
                                    echo "<option value=" . DETAILS_OTHER . " selected=\"selected\">Другое</option>";
                                else echo "<option value=" . DETAILS_OTHER . ">Другое</option>";
                            } elseif (SOURCE_COUPON != $srm_id) {
                                //printf("<option selected=".DETAILS_AMNESY == $srdet_id ? 'selected' : ''." value=".DETAILS_AMNESY.">Не помнит</option>");
                                if (DETAILS_AMNESY == $srdet_id)
                                    echo "<option value=" . DETAILS_AMNESY . " selected=\"selected\">Не помнит</option>";
                                else echo "<option value=" . DETAILS_AMNESY . ">Не помнит</option>";
                            }
                            echo "</select></div></div>";
                        }
                    }
                }
                echo "<div style='display: flex'>";
                echo "<label style='position: relative;' id='SelectReservoirT' for='SelectReservoir'>&nbsp;</label>";
                echo "<div id='SelectReservoir'>&nbsp;</div>";
                echo "</div>";
                /*if (isset($srm_id_new) && NULL != $srm_id_new)
                    echo "<script>$('#Reservoir_new').val('" . $srm_id_new . "').change();</script>";
                else echo "<script>$('#Reservoir_new').val('" . SOURCE_NOT . "').change();</script>";*/
                ?>
            </h2>
        </div>

        <div id="all_other" style="margin-top: 0">
            <?php if (DEVICE_MAIL != $source_auto_type) { ?>
                <h2><label for="comment">Комментарий:</label>
                    <textarea name="comment" id="comment" disabled title="Комментарий" placeholder="<?=$comment?>" rows=30 cols=68 style="vertical-align: text-top; height: 45px;"></textarea>
                </h2>
            <?php } ?>
            <!--?php
        $surname = substr ($client_name, 0, strpos($client_name, '/'));
        $name = substr ($client_name, strpos($client_name, '/')+1, strrpos($client_name, '/')-strpos($client_name, '/')-1);
        $patronymic = substr ($client_name, strripos ($client_name, '/')+1, strlen($client_name));
        ?-->
            <h2>
                <?php
                if (DEVICE_MAIL == $source_auto_type) {
                    $mail_unit = "Отправитель: ".htmlentities($mail_from)."<br>Тема: ".htmlentities($mail_subject)."<br>".str_replace("\n".chr(10),"\n",$mail_body);
                    $field_height = '120';
                    if (strlen($mail_unit) > 500 || substr_count($mail_unit,'<br/>') > 4 ||
                        substr_count($mail_unit,"\n") > 4 || substr_count($mail_unit,"\r") > 4)
                        $field_height = "height:".(substr_count($mail_unit,"\n")+1)*25.0."px;";
                    $mail_unit = nl2br($mail_unit);
                    echo "<div style='".$field_height." overflow-y: scroll; font-size: 13px; outline: 3px solid #14b14b; background-color: whitesmoke; margin-bottom: 5px;'>$mail_unit</div>";
                }
                echo "<label for='surname'>ФИО:&nbsp;</label><input type='text' id='surname' name='surname' style='width: 98%' value='{$client_name}'";
                if (DEVICE_MAIL != $source_auto_type || in_array($_SESSION['login_id_med'], SPEC_USER_VIEW)) {
                    echo " disabled />";
                } else {
                    echo " />";
                }
                /*<label for="name">Имя:&nbsp;</label><input type="text" id="name" name="name" style="width: 7em" disabled value="< ?=$name?>"/>
                <label for="patronymic">Отчество:&nbsp;</label><input type="text" id="patronymic" name="patronymic" disabled value="< ?=$patronymic?>"/>
                <label for="ages">Возраст:&nbsp;</label>
                <input type="number" min="0" max="200" disabled value="< ?=$age?>" id="ages" name="ages" style="width: 4em;"/>*/
                $phone_text = ''; $pwidth = 13;
                if (!empty($phone_mob_norm)) {
                    $phone_text = phone_segment(trim($phone_mob_norm), NULL);
                    $phone_call = $phone_text;
                    $phone_call_norm = $phone_mob_norm;
                }
                elseif (RESULT_AON == $result_id || empty($phone_mob_norm)) {
                    if (trim($phone_new) == '') {
                        $phone_call = phone_segment(trim($anumber), NULL);
                    } else {
                        $phone_call = phone_segment(trim($phone_new_norm), NULL);
                    }
                    $phone_text .= trim($phone_mob) . ": " . $phone_call;
                    $phone_call_norm = phone_norm_single($phone_call,"ru_dial");
                    $phone_text .= "(АОН)";
                    $pwidth = 19;
                }
                echo "<br/><label for='phone_mob'>Контактный телефон:&nbsp;</label>";
                //echo "<input type='text' id='phone_mob' name='phone_mob' style='width: 10em;' value='".$phone_text."'";
                echo "<input type='text' name='phone_mob' style='width: ".$pwidth."em;' value='".$phone_text."'";
                if (DEVICE_MAIL != $source_auto_type || in_array($_SESSION['login_id_med'], SPEC_USER_VIEW))
                    echo " disabled ";
                echo "/>";
                if (strlen($phone_call_norm) >= 11 && strlen($phone_call_norm) <= 18) {
                    echo "<input disabled id='callto_button' type='button' onclick='this.disabled=true; callto(".$phone_call_norm.",".$base_id.")' value='    Позвонить' title='".$phone_call."' 
style='height: 25px; background-image: url(\"".PATH."/images/call.png\"); background-repeat: no-repeat;' />";
//style='background-color: darkgreen; color: wheat; font-weight: bold' />;
                    echo "<input disabled id='endcall_button' type='button' onclick='endcall()' value='    Завершить звонок'  
style='height: 25px; background-image: url(\"".PATH."/images/call_stop.png\"); background-repeat: no-repeat;' />";
//style='background-color: red; color: wheat'/>";
                    echo "<input type='text' id='oktell_server_status' style='color: crimson;' disabled>";
                }
                else {
                    echo "<input disabled id='call_not_button' type='button' value='Некорректный номер. Звонок невозможен.' title='".$phone_call."' style='height: 25px; font-weight: bold' />";
                }

                echo "<br/>Междугородный звонок: &nbsp;
				<input type=checkbox id='interstate' name='interstate' title='Ошибка (Межгород)' alt='Ошибка (Межгород)'";
                if (isset($interstate) && 1 == $interstate)
                    echo " checked ";
                if (($status_id_unic >= STATUS_CALL_STOP || SERVICE_STOM == $srv_id
                        || in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                        || in_array($_SESSION['login_id_med'],SPEC_USER_VIEW))
                    && SPEC_USER != $_SESSION['login_id_med'])
                    echo " disabled";
                echo ">";
                ?>
                <hr>
                <!--label for="e_mail">E-mail:&nbsp;</label>
                < ?php if (USER_SUPER == $_SESSION['user_role'] && STATUS_OPEN == $status_id_unic) { ?>
                    <input type="email" id="e_mail" name="e_mail" placeholder="< ?= !empty($email) ? $email : 'Введите e-mail' ?>" style="width: 22em;" disabled/>
                < ?php } else { ?>
                    <input type="email" id="e_mail" name="e_mail" placeholder="< ?= !empty($email) ? $email : 'Введите e-mail' ?>" style="width: 22em;"/>
                < ?php } ?-->
                <!--?php if (USER_SUPER == $_SESSION['user_role']) { ?>
                    <script>document.all.phone_mob.disabled=true;</script>
                    <script>document.all.phone_new.disabled=true;</script>
                    <script>document.all.e_mail.disabled=true;</script>
                    <script>document.all.ages.disabled=true;</script>
                < ?php } ?-->
            </h2>

            <h2 style="display: inline-block; margin-top: 0;">
                <?php
                $nRowsClinic = 0;
                $nRowsHist = GetData::GetCallHistory($base_id);
                if ($nRowsHist > 0) {
                    foreach (GetData::$array_hist as $key => $value) {
                        if (STATUS_CLINIC == $value['STATUS_ID'])
                            $nRowsClinic++;
                    }
                }
                if (!in_array($_SESSION['login_id_med'],SPEC_USER_CALL)) {
                    echo "Клиент обращается: <select id='DoubleId' name='DoubleId' title='Клиент'";
                    if (($status_id_unic >= STATUS_CALL_STOP
                            || in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                            || in_array($_SESSION['login_id_med'],SPEC_USER_VIEW))
                        && SPEC_USER != $_SESSION['login_id_med'])
                        echo " disabled";
                    echo">";
                    /*if ($status_id_unic >= STATUS_CALL_STOP && USER_ADMIN != $_SESSION['user_role'] && SPEC_USER != $_SESSION['login_id_med']
                        || in_array($_SESSION['login_id_med'], SPEC_USER_VIEW))
                        echo " disabled";
                    echo ">";*/
                    echo "<option value='" . CALL_FIRST . "'>Первый раз</option>";
                    echo "<option value='" . CALL_SECOND . "'>Повторно</option>";
                    echo "</select>"; // DoubleId
                    if (isset($call_double))
                        echo "<script>$('#DoubleId').val('" . $call_double . "').change();</script>";
                }

                if (FALSE == DEBUG_MODE)
                    $table_name = 'CALL_BASE';
                else $table_name = 'CALL_BASE_TEST';
                $phone_find = substr($phone_call_norm, 1);
                $sqlstr = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm hh24:mi:ss') FOUND_CALL FROM ".$table_name." cb
 LEFT JOIN SOURCE_AUTO sa ON sa.id = cb.source_auto_id
 WHERE STATUS_ID < 99 and (PHONE_MOB_NORM like '%".$phone_find."%' or PHONE_MOB like '%".$phone_find."%' or 
       PHONE_NEW_NORM like '%".$phone_find."%' or PHONE_NEW like '%".$phone_find."%' or 
       ANUMBER like '%".$phone_find."%' or cb.BNUMBER like '%".$phone_find."%') ORDER BY ID desc";
                $query = OCIParse($c, $sqlstr);
                if (OCIExecute($query)) {
                    $found_id = 0;
                    //echo '<form action="" id="found_ph" name="found_ph" method="post" target="ifr_found">';
                    while ($objResult = OCI_Fetch_Row($query)) {
                        $found_base_id = $objResult[0];
                        $found_date = $objResult[1];
                        if ($found_base_id != $base_id) {
                            if (0 == $found_id++) echo " Ещё:";
                            echo "<input type='submit' form='found_ph' value='" . $found_base_id . "(" . $found_date . ")' class='enter_button'
                            onclick=\"open_edit('" . $found_base_id . "','" . $_SESSION['login_id_med'] . "','" . $sid . "')\" style='height: 1.6em;' disabled />";
                        }
                        if ($found_id == 3) break;
                    }
                    //echo '</form>';

                }
                echo "<br/>";

                if (STATUS_CLINIC_CALL == $status_id_unic)
                    echo "Результат:&nbsp;";
                else echo "Статус:&nbsp;";
                if (GetData::GetMedStatus("1=1", STATUS_CLINIC_CALL == $status_id_unic, FALSE) > 0) {
                    echo "<select id='StatusId' name='StatusId' title='Статус'";
                    if (($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && STATUS_CLINIC_NOT != $status_id_unic
                        /*&& (STATUS_CLINIC != $status_id_unic ||
                                STATUS_CLINIC == $status_id_unic && $nRowsClinic >= WRITE_CLINIC_NUM &&
                                strtotime($entry_date_1c) <= strtotime("0 days"))*/ // возможность перезаписать
                        && (strtotime($date_stop) <= strtotime("-11 days") && STATUS_CLINIC == $status_id_unic ||
                            strtotime($date_stop) <= strtotime("-3 days") && STATUS_ERROR != $status_id_unic)
                        && SERVICE_STOM == $srv_id && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                        || in_array($_SESSION['login_id_med'],SPEC_USER_VIEW)
                        || STATUS_ERROR == $status_id_unic && SERVICE_STOM != $srv_id)
                        && USER_ADMIN != $_SESSION['user_role'] && SPEC_USER != $_SESSION['login_id_med'])
                        echo " disabled";
                    if (USER_USER == $_SESSION['user_role'])
                        echo " style='background-color:".needs."'";
                    echo ">";
                    if (USER_USER == $_SESSION['user_role']) // пользователю придется сделать выбор статуса!
                        echo "<option selected=\"selected\" value='".STATUS_NOT."'>Выберите статус заявки</option>";
                    if (USER_SUPER == $_SESSION['user_role'] && $status_id_unic <= STATUS_CALL_NOT && $status_id_unic != STATUS_CALL_BACK) {
                        if (STATUS_WORK == $status_id_unic)
                            echo "<option selected=\"selected\" value='".STATUS_WORK."'>Назначено</option>";
                        else echo "<option selected=\"selected\" value='".STATUS_WORK."'>Назначить</option>";
                    }
                    foreach (GetData::$array_status as $key => $value) {
                        if (USER_ADMIN != $_SESSION['user_role'] && STATUS_NOT_COME == $value['ID'] && STATUS_NOT_COME != $status_id_unic)
                            continue; // Пациент не пришел доступно админу и если статус выставлен скриптом в базе
                        //if (STATUS_CLINIC == $value['ID'] && $nRowsClinic >= WRITE_CLINIC_NUM) continue; // ограниченное количество перезаписей (2 раза)
                        if (USER_ADMIN != $_SESSION['user_role'] && STATUS_CLOSED == $value['ID']) continue; // админ может просто закрыть заявку
                        if (in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && STATUS_REPEAT == $value['ID']) continue; // второй дозвон без статуса "Повтор"
                        //if (USER_SUPER == $_SESSION['user_role'] && STATUS_WORK == $value['ID']) continue; // "Назначено" уже добавлено выше
                        if (USER_ADMIN != $_SESSION['user_role'] && STATUS_WORK == $value['ID']) continue; // "Назначено" уже добавлено выше
                        if (USER_USER == $_SESSION['user_role'] && STATUS_CALL_STOP == $value['ID']) continue; // Оператору не может надоесть звонить!
                        if ((STATUS_CLINIC != $status_id_unic && STATUS_CL_CANCEL != $status_id_unic) && STATUS_CL_CANCEL == $value['ID']) continue; // отмена записи только для уже записанных
                        if (STATUS_CALL_BACK == $status_id_unic && (STATUS_CLINIC_NOT != $value['ID'] && STATUS_CLINIC != $value['ID'] && STATUS_CALL_BACK != $value['ID'])
                            && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && SPEC_USER != $_SESSION['login_id_med'])
                            continue; // для проведенной консультации можно поменять только на запись или отказ (кроме спецпользователей)
                        if (STATUS_CLINIC == $status_id_unic && (STATUS_CLINIC_NOT != $value['ID'] && STATUS_CLINIC != $value['ID'] && STATUS_CALL_BACK != $value['ID'] && STATUS_CL_CANCEL != $value['ID'])
                            && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && SPEC_USER != $_SESSION['login_id_med']) {
                            if (SERVICE_STOM != $srv_id || (SERVICE_STOM == $srv_id && strtotime($date_stop) <= strtotime("-3 days") || STATUS_ERROR != $value['ID'])) {
                                continue; // для уже записанного можно поменять только на еще запись, или отказ, или снова Консультация (кроме спецпользователей и стоматологии в течение трех дней)
                            }
                        }
                        if (STATUS_CLINIC_NOT == $status_id_unic && (STATUS_CLINIC_NOT != $value['ID'] && STATUS_CLINIC != $value['ID'] && STATUS_CALL_BACK != $value['ID'])
                            && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && SPEC_USER != $_SESSION['login_id_med']) {
                            if (SERVICE_STOM != $srv_id || (SERVICE_STOM == $srv_id && strtotime($date_stop) <= strtotime("-3 days") || STATUS_ERROR != $value['ID'])) {
                                continue; // для отказа от записи можно поменять только на Запись или снова Консультация (кроме спецпользователей и стоматологии в течение трех дней)
                            }
                        }
                        if (STATUS_ERROR == $status_id_unic && (STATUS_CLINIC_NOT != $value['ID'] && STATUS_CLINIC != $value['ID'] && STATUS_ERROR != $value['ID'])
                            && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && SPEC_USER != $_SESSION['login_id_med']) {
                            //echo '<script>alert('.$srv_id.$value['ID'].');</script>';
                            if (SERVICE_STOM != $srv_id || (STATUS_WORK != $value['ID'] || SERVICE_STOM == $srv_id && strtotime($date_stop) <= strtotime("-5 days"))) {
                                continue; // для ошибки можно поменять только на Запись, Отказ или Назначить (кроме спецпользователей и стоматологии в течение трех дней)
                            }
                        }
                        if (USER_USER == $_SESSION['user_role'] && (STATUS_OPEN == $value['ID'] || STATUS_WORK == $value['ID']))
                            continue; // Оператор не может в Новое или Назначить
                        if (SERVICE_STOM == $srv_id && STATUS_CALL_BACK == $value['ID'] && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) && SPEC_USER != $_SESSION['login_id_med'])
                            continue; // Стоматологам не нужна "Консультация"

                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        if ($status_id_unic == $value['ID'] &&
                            (USER_USER != $_SESSION['user_role'] || in_array($_SESSION['login_id_med'],SPEC_USER_CALL)))
                            echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                        else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                    }
                    echo "</select>"; // StatusId

                    $nRowsWC = GetData::GetCallWriteClinic($base_id);
                    echo "<label for='show_second' id='show_secondT' style='margin-top: 2px; position: absolute;";
                    if (STATUS_CLINIC != $status_id_unic) echo "visibility: hidden;";
                    echo "'>&nbsp;&nbsp;Второй пациент:</label>";
                    echo "<input type=checkbox style='margin-left: 12.2em; margin-top: 6px;";
                    if (STATUS_CLINIC != $status_id_unic) echo "visibility: hidden;";
                    echo "'";
                    if ($nRowsWC > 1) echo " checked ";
                    echo "name='show_second' id='show_second' onclick='SecondClient()'>";

                    if (GetData::GetMedStatusDet(NULL, STATUS_ERROR, FALSE) > 0) {
                        echo "<label for='status_det' id='status_detT' style='visibility: hidden'>&nbsp;Причина:&nbsp;</label>";
                        echo "<select id='status_det' name='status_det' ";
                        if (STATUS_ERROR != $status_id_unic || !isset($status_det_id))
                            echo " style='visibility: hidden; background-color:".needs."'";
                        if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && STATUS_CLINIC_NOT != $status_id_unic
                            && SPEC_USER != $_SESSION['login_id_med']
                            && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                            && USER_ADMIN != $_SESSION['user_role'] && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                            echo " disabled";
                        echo ">";
                        if ($status_id_unic >= STATUS_CALL_STOP)
                            echo "<option value='".STATUS_NOT."'>Причина не указана</option>"; // select disabled anyway
                        else echo "<option value=''>Выберите причину</option>";
                        foreach (GetData::$array_status_det as $key => $value) {
                            if (TRUE == ENCODE_UTF)
                                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                            if (isset($status_det_id) && $status_det_id == $value['ID'])
                                echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                            else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                        }
                        echo "</select>";
                        if (STATUS_ERROR == $status_id_unic) {
                            echo "<script>document.getElementById('status_detT').style.visibility = 'visible';
                            document.getElementById('status_det').style.visibility = 'visible';
                            </script>";
                        }
                    }

                    if (STATUS_CLINIC_CALL == $status_id_unic) {
                        echo "<select id='call_clinic' name='call_clinic' disabled>";
                        if (GetData::GetHospitals(NULL) > 0) {
                            foreach (GetData::$array_hospitals as $key => $value) {
                                if (TRUE == ENCODE_UTF)
                                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                                if (isset($result_det) && $result_det == $value['ID'])
                                    echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                                else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                            }
                        }
                    }
                    echo "</select>";
                }

                if (STATUS_WORK != $status_id_unic || USER_SUPER != $_SESSION['user_role'] && USER_ADMIN != $_SESSION['user_role']) {
                    echo "<div id='assign_cl' style='position: absolute; visibility: hidden'>";
                } else {
                    echo "<div id='assign_cl' style='position: inherit'>";
                }
                echo "&nbsp;Оператор:&nbsp;";
                /*$strfilt = "usr.DELETED IS NULL AND ACTIVITY IS NOT NULL and (sysdate-ACTIVITY)*86400 < 30 and
                usr.id != ".$_SESSION['login_id_med']." AND (ROLE_ID = " . USER_USER . " or ROLE_ID = " . USER_SUPER .")";*/
                //$strfilt = "usr.id != ".$_SESSION['login_id_med']." AND (ROLE_ID = ".USER_USER." or ROLE_ID = ".USER_SUPER.")";
                $strfilt = "(ROLE_ID = ".USER_USER." or ROLE_ID = ".USER_SUPER.")";
                if (STATUS_WORK == $status_id_unic && isset($texnari_id) && '' != $texnari_id)
                    $strfilt .= " OR usr.ID = " . $texnari_id;
                if (USER_ADMIN == $_SESSION['user_role'])
                    $strfilt .= " or ROLE_ID = " . USER_ADMIN;

                if (GetData::GetUsersDep(FALSE, $strfilt,NULL, 'yes') > 0) { // dep_id or user_id of Superviser ?
                    echo "<select id='UserId' name='UserId'";
                    if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && USER_ADMIN != $_SESSION['user_role'] &&
                        (SERVICE_STOM != $srv_id || strtotime($date_stop) <= strtotime("-3 days")))
                        echo " disabled";
                    echo ">";
                    foreach(GetData::$array_userd as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                        if (isset($texnari_id) && $texnari_id == $value['ID'])
                            echo "<option value='".$value['ID']."' selected=\"selected\">".$value['FIO']."</option>";
                        else echo "<option value='".$value['ID']."'>".$value['FIO']."</option>";
                    }
                    echo "</select>";
                }
                else {
                    echo "<select id='UserId' name='UserId' disabled style='color:".needs."'>";
                    echo "<option value=''>Нет доступных операторов</option>";
                    echo "</select>";
                }
                //echo "<script>if (document.getElementById('save_but')) document.getElementById('save_but').style.visibility = 'hidden';</script>";
                echo "</div>"; // assign_cl

                /*if (STATUS_CALL_BACK != $status_id_unic)
                    echo "<div id='call_cl' style='position: absolute; visibility: hidden'>";
                else*/ echo "<div id='call_cl' style='position: inherit'>";
                echo "&nbsp;Дата и время перезвона:&nbsp;";
                echo "<input type='text' name='call_back_picker' id='call_back_picker' autocomplete='off' placeholder='Выберите время звонка'";
                if (!empty($call_back_date))
                    echo " value='".$call_back_date."'";
                if (in_array($_SESSION['login_id_med'], SPEC_USER_VIEW) && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL))
                    echo " disabled";
                echo "/>";
                echo "<input type='button' name='ClearDate' value='Очистить' style='color: #336699; font-weight: bold;'
onclick=\"javascript:document.getElementById('call_back_picker').value = ''\" />";
                echo "</div>"; // call_cl

                if (STATUS_CLINIC != $status_id_unic)
                    echo "<div id='write_cl' style='position: absolute; visibility: hidden'>";
                else echo "<div id='write_cl' style='position: inherit'>";
                $clinic_client_name = $clinic_client_phone = $clinic_client_status = $clinic_client_date = "";
                $clinic_client_name2 = $clinic_client_phone2 = $clinic_client_status2 = $clinic_client_date2 = "";
                if ($nRowsWC > 0) {
                    $hospital = GetData::$array_wc[0]['HOSPITAL_ID'];
                    $clinic_client_name = GetData::$array_wc[0]['CLIENT_NAME'];
                    $clinic_client_date = GetData::$array_wc[0]['CLIENT_DATE'];
                    $clinic_client_phone = GetData::$array_wc[0]['CLIENT_PHONE'];
                    $clinic_client_status = GetData::$array_wc[0]['CLIENT_STATUS'];
                    if ($nRowsWC > 1) {
                        $hospital2 = GetData::$array_wc[1]['HOSPITAL_ID'];
                        $clinic_client_name2 = GetData::$array_wc[1]['CLIENT_NAME'];
                        $clinic_client_date2 = GetData::$array_wc[1]['CLIENT_DATE'];
                        $clinic_client_phone2 = GetData::$array_wc[1]['CLIENT_PHONE'];
                        $clinic_client_status2 = GetData::$array_wc[1]['CLIENT_STATUS'];
                    }
                }

                echo "&nbsp;Клиника:&nbsp;";
                $nRowsClinic = GetData::GetHospitals(NULL);
                if ($nRowsClinic > 0) {
                    echo "<select id='Clinic' name='Clinic'";
                    if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && USER_ADMIN != $_SESSION['user_role']
                        && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                        && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                        && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                        echo " disabled";
                    echo ">";
                    echo "<option value='NULL'>Выберите клинику</option>";
                    foreach (GetData::$array_hospitals as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        if (isset ($hospital) && $hospital == $value['ID'])
                            echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                        else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                    }
                    echo "</select>";
                }
                echo "&nbsp;Дата:&nbsp;";
                echo "<input type='text' name='write_cl_picker' id='write_cl_picker' autocomplete='off' placeholder='Укажите время записи'";
                if (!empty($clinic_client_date)) {
                    echo " value='" . $clinic_client_date . "'";
                    /*if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                        && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                        echo " disabled";*/
                }
                echo " />";
                echo "<br/>";
                if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
                    strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
                    echo "<input type=button id=copy_button value='Копировать' onclick='fCopyIE(1)' style='float: right; height: 3em; background-color: chartreuse; color: indigo; font-weight: bold;' />";
                } else {
                    echo "<input type=button id=copy_button value='Копировать' onclick='fCopyChrome(1)' style='float: right; height: 3em; background-color: chartreuse; color: indigo; font-weight: bold;' />";
                }

                //$surname_cl = substr ($clinic_client_name, 0, strpos($clinic_client_name, '/'));
                //$name_cl = substr ($clinic_client_name, strpos($clinic_client_name, '/')+1, strrpos($clinic_client_name, '/')-strpos($clinic_client_name, '/')-1);
                //$patronymic_cl = substr ($clinic_client_name, strripos ($clinic_client_name, '/')+1, strlen($clinic_client_name));
                $pacient = explode('/',$clinic_client_name);
                echo "<label for='surname_cl1'>&nbsp;Фамилия:&nbsp;</label>";
                echo "<input type='text' id='surname_cl1' name='surname_cl1' placeholder='Фамилия'";
                if (!empty($pacient[0]))
                    echo " value='$pacient[0]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<label for='name_cl1'>&nbsp;Имя:&nbsp;</label>";
                echo "<input type='text' id='name_cl1' name='name_cl1' placeholder='Имя'";
                if (!empty($pacient[1]))
                    echo " value='$pacient[1]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<br/>";
                echo "<label for='patronymic_cl1'>&nbsp;Отчество:&nbsp;</label>";
                echo "<input type='text' id='patronymic_cl1' name='patronymic_cl1' placeholder='Отчество'";
                if (!empty($pacient[2]))
                    echo " value='$pacient[2]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<label for='phone_new1'>&nbsp;Телефон:&nbsp;</label>";
                echo "<input type='text' id='phone_new1' name='phone_new1' style='width: 10em;' placeholder='Введите номер'";
                if (!empty($clinic_client_phone))
                    echo " value='$clinic_client_phone'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                //echo "<label for='ages_cl'>Возраст:&nbsp;</label>";
                //echo "<input type='number' min='0' max='200' id='ages_cl' name='ages_cl' style='width: 4em;' value='".(!empty($ages_cl) ? $ages_cl : '')."' />";
                /*if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
                    strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false)
                    echo "<textarea id='text_to_copy' hidden></textarea>";
                else*/
                echo "<textarea id='text_to_copy' style='margin-left: -5px; height: 0; width: 0; padding: 1px; border: hidden'></textarea>";

                if ($nRowsWC > 1)
                    echo "<div id='second_cl' style='position: inherit'>";
                else echo "<div id='second_cl' style='position: absolute; visibility: hidden'>";
                echo "&nbsp;Клиника:&nbsp;";
                if ($nRowsClinic > 0) {
                    echo "<select id='Clinic2' name='Clinic2'";
                    if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && USER_ADMIN != $_SESSION['user_role']
                        && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                        && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                        && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                        echo " disabled";
                    echo ">";
                    echo "<option value='NULL'>Выберите клинику</option>";
                    foreach (GetData::$array_hospitals as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        if (isset($hospital2) && $hospital2 == $value['ID'])
                            echo "<option value='".$value['ID']."' selected=\"selected\">".$value['NAME']."</option>";
                        else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                    }
                    echo "</select>";
                }
                echo "&nbsp;Дата:&nbsp;";
                echo "<input type='text' name='write_cl_picker2' id='write_cl_picker2' autocomplete='off' placeholder='Укажите время записи'";
                if (!empty($clinic_client_date2)) {
                    echo " value='".$clinic_client_date2."'";
                    /*if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                        && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                        echo " disabled";*/
                }
                echo " />";
                echo "<br/>";
                if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
                    strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) {
                    echo "<input type=button id=copy_button2 value='Копировать' onclick='fCopyIE(2)' style='float: right; height: 3em; background-color: chartreuse; color: indigo; font-weight: bold;' />";
                } else {
                    echo "<input type=button id=copy_button2 value='Копировать' onclick='fCopyChrome(2)' style='float: right; height: 3em; background-color: chartreuse; color: indigo; font-weight: bold;' />";
                }

                $pacient2 = explode('/',$clinic_client_name2);
                echo "<label for='surname_cl2'>&nbsp;Фамилия:&nbsp;</label>";
                echo "<input type='text' id='surname_cl2' name='surname_cl2' placeholder='Фамилия'";
                if (!empty($pacient2[0]))
                    echo " value='$pacient2[0]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<label for='name_cl2'>&nbsp;Имя:&nbsp;</label>";
                echo "<input type='text' id='name_cl2' name='name_cl2' placeholder='Имя'";
                if (!empty($pacient2[1]))
                    echo " value='$pacient2[1]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<br/>";
                echo "<label for='patronymic_cl2'>&nbsp;Отчество:&nbsp;</label>";
                echo "<input type='text' id='patronymic_cl2' name='patronymic_cl2' placeholder='Отчество'";
                if (!empty($pacient2[2]))
                    echo " value='$pacient2[2]'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "<label for='phone_new2'>&nbsp;Телефон:&nbsp;</label>";
                echo "<input type='text' id='phone_new2' name='phone_new2' style='width: 10em;' placeholder='Введите номер'";
                if (!empty($clinic_client_phone2))
                    echo " value='$clinic_client_phone2'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && SPEC_USER != $_SESSION['login_id_med']
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM))
                    echo " disabled";
                echo " />";
                echo "</div>"; // second_cl
                echo "</div>"; // write_cl

                echo "<div id='History'> История событий:<br/>";
                if ($nRowsHist > 0) {
                    require_once "med_player.php"; // остался только сам плеер
                    require_once "func_get_okt_connections_info.php";

                    echo "<table class='clear_table'><tr><th style='width: 120px;'>Дата</th><th>Оператор</th><th>Статус</th><th>Примечание</th>";
                    if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                        $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr) /*&& DEVICE_PHONE == $st_id*/) {
                        echo "<th style='width: 180px;'>Информация о звонке</th>";
                    }
                    echo "</tr>";

                    if (RESULT_KC == $result_id && count(GetData::$array_hist) == 3 /*&& GetData::GetOktellHistory($base_id) == 3*/) {
                        $idchain_then = '';
                        if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                            $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) {
                            //ищем заявку по цепочке коммутаций
                            $arr_hear = array();
                            $res = get_okt_connections_info($c, $okt_server_id, $okt_idchain);
                            if(!$res) {echo "Запись не найдена<br>";}
                            elseif(isset($res['error'])) {/*echo $res['error']."<br>";*/} // ошибку не отображаем?
                            else $arr_hear = get_connections_array($res, "Входящий");

                            $arr_hear_then = array();
                            if (GetData::GetOktellHistory($base_id) > 0) {
                                //var_dump(GetData::$array_oktell_hist);
                                foreach (GetData::$array_oktell_hist as $ok_hist) {
                                    $idchain_then = $ok_hist['OKTELL_IDCHAIN'];
                                    $okt_server_id = $ok_hist["OKTELL_SERVER_ID"];

                                    $res = get_okt_connections_info($c, $okt_server_id, $idchain_then);
                                    if(!$res) {echo "Запись не найдена<br>";}
                                    elseif(isset($res['error'])) {/*echo $res['error']."<br>";*/} // ошибку не отображаем?
                                    else $arr_hear_then = array_merge($arr_hear_then, get_connections_array($res,"Исходящий"));
                                }
                            }
                        }
                        $hear_key = $hear_key_then = $show_all_hear_in = $show_all_hear_out = 0;
                        foreach (GetData::$array_hist as $key => $value) {
                            $comment_full = $value['COMMENTS'];
                            if (TRUE == ENCODE_UTF)
                                $comment_full = iconv('windows-1251', 'utf-8', $comment_full);

                            if (STATUS_CALL_NOT == $value['STATUS_ID'])
                                $value['NAME'] = "Недозвон";
                            if (strstr($comment_full, "c_b=")) { //STATUS_CALL_BACK == $value['STATUS_ID']
                                $comment_cut = substr($comment_full, stripos($comment_full, ')') + 1);
                                $comment_add = substr($comment_full, 5, stripos($comment_full, ')') - 5);
                                $comment_add = str_replace("c_b=", "", $comment_add);
                                if (STATUS_CALL_NOT == $value['STATUS_ID']) {
                                    $value['NAME'] = "Перезвонить";
                                }
                            } elseif (STATUS_WORK == $value['STATUS_ID']) {
                                $oper_id = substr($comment_full, 8, stripos($comment_full, ')') - 8);
                                $comment_cut = substr($comment_full, stripos($comment_full, ')') + 1);
                                if ($oper_id && GetData::GetUsers(TRUE, TRUE, "usr.ID = " . $oper_id, "FIO") > 0) {
                                    $comment_add = GetData::$array_user[0]['FIO'];
                                } else $comment_add = str_replace("fio_id=", "", $comment_add);
                            } else {
                                $comment_cut = $comment_full;
                                $comment_add = '';
                            }
                            echo '<tr><td>' . $value['DATE_DET_C'] . '</td>';
                            echo '<td>' . $value['FIO'] . '</td>';
                            echo '<td>' . $value['NAME'] . '<br/>' . $comment_add . '</td>';
                            echo '<td style="text-align: left">' . $comment_cut . '</td>';
                            if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                                $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr) /*&& DEVICE_PHONE == $st_id*/) {
                                $hear_rows = count($arr_hear) + count($arr_hear_then);
                                if (RESULT_KC == $result_id && count(GetData::$array_hist) == $hear_rows ||
                                    RESULT_KC != $result_id && (count(GetData::$array_hist) == $hear_rows || count(GetData::$array_hist) == $hear_rows + 1) ||
                                    DEVICE_MAIL == $st_id && count(GetData::$array_hist) >= count($arr_hear_then) + 1) {
                                    if (isset($arr_hear[$hear_key])) {
                                        if (RESULT_KC == $result_id || STATUS_WORK != $value['STATUS_ID'] || 3 == count($arr_hear))
                                            echo '<td style="text-align: left">' . $arr_hear[$hear_key++] . '</td>';
                                        else echo '<td style="text-align: center"></td>';
                                    } else {
                                        if (isset($arr_hear_then[$hear_key_then]) && STATUS_OPEN != $value['STATUS_ID'] && STATUS_WORK != $value['STATUS_ID'])
                                            echo '<td style="text-align: left">' . $arr_hear_then[$hear_key_then++] . '</td>';
                                        else echo '<td style="text-align: center"></td>';
                                    }
                                } else {
                                    if (STATUS_OPEN == $value['STATUS_ID'] || STATUS_WORK == $value['STATUS_ID']) {
                                        echo '<td style="text-align: left">';
                                        if (0 == $show_all_hear_in) {
                                            foreach ($arr_hear as $row_hear) {
                                                $hear_key++;
                                                echo $row_hear . '<br/>';
                                            }
                                            $show_all_hear_in = 1;
                                        }
                                        echo '</td>';
                                    } elseif ($value['STATUS_ID'] > STATUS_WORK) { // или раскладываем по строкам, или все сразу в одну ячейку.
                                        if (count(GetData::$array_hist) >= count($arr_hear_then) + $key - $hear_key_then) {
                                            echo '<td style="text-align: left">' . (isset($arr_hear_then[$hear_key_then]) ? $arr_hear_then[$hear_key_then++] : '') . '</td>';
                                        } else {
                                            echo '<td style="text-align: left">';
                                            if (isset($arr_hear_then)) {
                                                if (0 == $show_all_hear_out) {
                                                    foreach ($arr_hear_then as $row_hear) {
                                                        $hear_key_then++;
                                                        echo $row_hear . '<br/>';
                                                    }
                                                    $show_all_hear_out = 1;
                                                }
                                            }
                                            echo '</td>';
                                        }
                                    } else echo '<td style="text-align: center"></td>';
                                }
                            }
                            echo '</tr>';
                        }
                        // если вдруг исходящих звонков больше чем статусов и мы их не отобразили в одной куче.
                        if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                            $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) {
                            if (isset($arr_hear_then[$hear_key_then])) {
                                echo '<tr><td colspan="4"></td><td style="text-align: left">';
                                while (isset($arr_hear_then[$hear_key_then])) {
                                    echo $arr_hear_then[$hear_key_then++] . "<br/>";
                                }
                                echo '</td></tr>';
                            }
                        }
                        echo "</table>";
                    }
                    else {
                        if (FALSE == DEBUG_MODE)
                            $table_hist = 'CALL_BASE_HIST';
                        else $table_hist = 'CALL_BASE_HIST_TEST';
                        $sel_str = "
	SELECT BASE_ID,DATE_DET, to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET_C, STATUS_ID, stat.NAME, stat.COLOR,
	USER_ID, OPERATOR || usr.FIO as FIO, COMMENTS, 
	NULL OKTELL_CALL_HIST_ID, NULL OKTELL_SERVER_ID, NULL OKTELL_IDCHAIN, 1 as WHAT_THIS 
	FROM ".$table_hist." hist
	LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
	LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID
	WHERE BASE_ID = ".$base_id;
                        if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                            $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) {
                            $sel_str .= " UNION
	select oh.base_id,oh.start_date, to_char(oh.start_date,'dd.mm.yyyy hh24:mi:ss'), NULL,'Исходящий звонок','black',
	oh.user_id, usr.FIO as FIO,'' as COMMENTS,
	oh.id,oh.oktell_server_id,oh.oktell_idchain, 2 as WHAT_THIS 
	from OKTELL_CALL_HIST oh
	LEFT JOIN USERS usr ON usr.ID = oh.USER_ID
	where oh.base_id=" . $base_id;
                        }
                        $sel_str .= " order by 2,4 ";	// Сортировка по второму полю
                        $query=OCIParse($c, $sel_str);

                        OCIExecute($query, OCI_DEFAULT);
                        $nrows = OCI_Fetch_All($query,$array_hist,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
                        oci_free_statement($query);
//show_array($array_hist);
//echo "<pre>"; var_dump($array_hist);echo "</pre>";
                        if ($nrows > 0) {
                            foreach($array_hist as $rownum => $value) {
                                $comment_full = $value['COMMENTS'];
                                if (TRUE == ENCODE_UTF)
                                    $comment_full = iconv('windows-1251', 'utf-8', $comment_full);

                                if (STATUS_CALL_NOT == $value['STATUS_ID'])
                                    $value['NAME'] = "Недозвон";
                                if (strstr($comment_full, "c_b=")) { //STATUS_CALL_BACK == $value['STATUS_ID']
                                    $comment_cut = substr($comment_full, stripos($comment_full, ')') + 1);
                                    $comment_add = substr($comment_full, 5, stripos($comment_full, ')') - 5);
                                    $comment_add = str_replace("c_b=", "", $comment_add);
                                    if (STATUS_CALL_NOT == $value['STATUS_ID']) {
                                        $value['NAME'] = "Перезвонить";
                                    }
                                } elseif (STATUS_WORK == $value['STATUS_ID']) {
                                    $oper_id = substr($comment_full, 8, stripos($comment_full, ')') - 8);
                                    $comment_cut = substr($comment_full, stripos($comment_full, ')') + 1);
                                    if ($oper_id && GetData::GetUsers(TRUE, TRUE, "usr.ID = " . $oper_id, "FIO") > 0) {
                                        $comment_add = GetData::$array_user[0]['FIO'];
                                    } else $comment_add = str_replace("fio_id=", "", $comment_add);
                                } else {
                                    $comment_cut = $comment_full;
                                    $comment_add = '';
                                }
                                echo "<tr>";
                                echo "<td>".$value['DATE_DET_C']."</td>";
                                echo "<td>".$value['FIO']."</td>";
                                echo "<td> " . $value['NAME'] . "<br/>" . $comment_add . "</td>";
                                echo "<td style='text-align: left'>" . $comment_cut . "</td>";

                                if ((USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) &&
                                    $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) { //информация о звонке
                                    echo "<td style='text-align: left'>";
                                    //если статус "новый", то присваиваем idchain входящего звонка
                                    if ($value['STATUS_ID'] == STATUS_OPEN) {
                                        $value['OKTELL_IDCHAIN'] = $okt_idchain;
                                        $value['OKTELL_SERVER_ID'] = $okt_server_id;
                                    }
                                    //echo $value['OKTELL_IDCHAIN'];
                                    if ($value['OKTELL_IDCHAIN'] == '00000000-0000-0000-0000-000000000000') echo "Ошибка";
                                    else if ($value['OKTELL_IDCHAIN'] <> '') {
                                        $res = get_okt_connections_info($c, $value['OKTELL_SERVER_ID'], $value['OKTELL_IDCHAIN']);
                                        if (!$res) echo "Запись не найдена";
                                        else if (isset($res['error'])) echo $res['error'];
                                        else {
                                            $arr_call = get_connections_array2($res, '');
                                            foreach ($arr_call as $val) {
                                                echo $val . "<br>";
                                            }
                                        }
                                    }
                                    echo "</td>";
                                }
                                //echo "<td>".$value['WHAT_THIS']."</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        }
                    }
                    $_SESSION['last_comment'] = $comment_cut;
                }
                echo "</div>"; // History

                echo "<label for='comment_cl'>Примечание: </label>";
                echo "<textarea name='comment_cl' id='comment_cl' title='Примечание' rows=30 cols=68 style='vertical-align: text-top; height: 45px;'";
                if ($status_id_unic >= STATUS_CALL_STOP && STATUS_NOT_COME != $status_id_unic && (STATUS_CLINIC != $status_id_unic || $nRowsClinic >= WRITE_CLINIC_NUM)
                    && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL)
                    && strtotime($date_stop) <= strtotime("-3 days") && SERVICE_STOM == $srv_id
                    && USER_ADMIN != $_SESSION['user_role'] && SPEC_USER != $_SESSION['login_id_med'])
                    echo " disabled";
                else echo " placeholder='Введите примечание'";
                echo " /></textarea>";
                ?>
                <script>
                    var select = document.getElementById("Clinic");
                    select.onchange = function()
                    {
                        var elem = document.getElementById('write_cl_picker');
                        elem.focus();
                    };

                    select = document.getElementById("Clinic2");
                    select.onchange = function()
                    {
                        var elem = document.getElementById('write_cl_picker2');
                        elem.focus();
                    };

                    select = document.getElementById("call_back_picker");
                    select.onchange = function() {
                        if (<?=STATUS_NOT?> != document.getElementById('StatusId').value &&
                        (<?=SERVICE_GINE?> == document.getElementById('ServiceId').value ||
                        <?=STOM_NOT?> != document.getElementById('ServiceStom').value))
                            document.getElementById('save_but').style.visibility = 'visible';
                        //var elem = document.getElementById('comment_cl');
                        //elem.focus();
                    };

                    /*select = document.getElementById("write_cl_picker");
                    select.onchange = function() {
                        var elem = document.getElementById('surname_cl1');
                        elem.focus();
                    };

                    select = document.getElementById("write_cl_picker2");
                    select.onchange = function() {
                        var elem = document.getElementById('surname_cl2');
                        elem.focus();
                    };*/

                    select = document.getElementById("status_det");
                    select.onchange = function() {
                        var elem;
                        if (<?=STATUS_NOT?> == this.value) {
                            this.style.backgroundColor = '<?=needs?>';
                            elem = this;
                            document.getElementById('save_but').style.visibility = 'hidden';
                        }
                        else {
                            this.style.backgroundColor = 'white';
                            elem = document.getElementById('comment_cl');
                            if (document.getElementById('Reservoir_new').length == 1 ||
                                <?=SOURCE_NOT?> != document.getElementById('Reservoir_new').value &&
                        (<?=SERVICE_GINE?> == document.getElementById('ServiceId').value ||
                        <?=STOM_NOT?> != document.getElementById('ServiceStom').value))
                                document.getElementById('save_but').style.visibility = 'visible';
                        }
                        elem.focus();
                    };

                    select = document.getElementById("UserId");
                    select.onchange = function() {
                        document.getElementById('save_but').style.visibility = 'visible';
                        var elem = document.getElementById('comment_cl');
                        elem.focus();
                    }
                </script>

                <script> // StatusChanged
                    var select = document.getElementById("StatusId");
                    select.onchange = function()
                    {
                    var spec_user_call='<?php echo in_array($_SESSION['login_id_med'],SPEC_USER_CALL);?>';
                    if (<?=STATUS_NOT?> == this.value)
                        this.style.backgroundColor = '<?=needs?>';
                    else this.style.backgroundColor = 'white';
                    //var servid = document.getElementById("ServiceId").value;
                    //var stomid = document.getElementById("ServiceStom").value;

                    var elem = document.getElementById('comment_cl');
                    document.getElementById('assign_cl').style.position = 'absolute';
                    //document.getElementById('call_cl').style.position = 'absolute';
                    document.getElementById('write_cl').style.position = 'absolute';
                    document.getElementById('call_back_picker').required = '';
                    document.getElementById('call_back_picker').value = '';
                    document.getElementById('call_cl').style.visibility = 'visible';
                    document.getElementById('call_cl').style.position = 'relative';
                    document.getElementById('status_detT').style.visibility = 'hidden';
                    document.getElementById('status_det').style.visibility = 'hidden';
                    document.getElementById('second_cl').style.visibility = 'hidden';
                    document.getElementById('show_second').style.position = 'absolute';
                    document.getElementById('show_second').style.visibility = 'hidden';
                    document.getElementById('show_secondT').style.visibility = 'hidden';

                    //var source_auto_type = < ?=$source_auto_type?>; && < ?=DEVICE_MAIL?> != source_auto_type
                    elem_save = document.getElementById('save_but');
                    if (elem_save) {
                        elem_ist = document.getElementById('Reservoir_new');
                        if (<?=STATUS_NOT?> == this.value ||
                        (<?=SERVICE_GINE?> != document.getElementById('ServiceId').value && <?=STATUS_WORK?> != this.value && <?=STATUS_CALL_NOT?> != this.value &&
                        <?=STOM_NOT?> == document.getElementById('ServiceStom').value && !spec_user_call))
                            elem_save.style.visibility = 'hidden';
                        else if ((<?=STATUS_OPEN?> != this.value && <?=STATUS_CALL_NOT?> != this.value && <?=STATUS_BREAK_LINE?> != this.value
                            && (null != elem_ist && elem_ist.length > 1 && <?=SOURCE_NOT?> == elem_ist.value)
                            || (<?=STATUS_ERROR?> == this.value && <?=STATUS_NOT?> == document.getElementById('status_det').value)
                                )
                            && <?=SPEC_USER?> != <?=$_SESSION['login_id_med']?>
                            && <?php echo $status_id_unic ?> != <?=STATUS_NOT_COME?> && <?php echo $status_id_unic ?> != <?=STATUS_CLINIC?>)
                            elem_save.style.visibility = 'hidden';
                        else elem_save.style.visibility = 'visible';
                    }

                    if (<?=STATUS_OPEN?> == this.value || <?=STATUS_NOT?> == this.value) { // только создана
                        if (<?=STATUS_NOT?> == this.value) elem = this;
                        document.getElementById('assign_cl').style.visibility = 'hidden';
                        document.getElementById('write_cl').style.visibility = 'hidden';
                    }
                    else if (<?=STATUS_WORK?> == this.value) { // В работе
                        document.getElementById('assign_cl').style.visibility = 'visible';
                        document.getElementById('write_cl').style.visibility = 'hidden';
                        document.getElementById('assign_cl').style.position = 'relative';
                        document.getElementById('call_cl').style.visibility = 'hidden';
                        document.getElementById('call_cl').style.position = 'absolute';
                        elem = document.getElementById('UserId');
                    }
                    else if (<?=STATUS_CALL_BACK?> == this.value) { // Просьба перезвонить
                        document.getElementById('assign_cl').style.visibility = 'hidden';
                        document.getElementById('write_cl').style.visibility = 'hidden';
                        //document.getElementById('call_cl').style.visibility = 'visible';
                        //document.getElementById('call_cl').style.position = 'relative';
                        //document.getElementById('call_back_picker').required = 'required';
                        elem = document.getElementById('call_back_picker');
                    }
                    else if (<?=STATUS_CLINIC?> == this.value) { // Перевод в Клинику
                        document.getElementById('assign_cl').style.visibility = 'hidden';
                        document.getElementById('write_cl').style.visibility = 'visible';
                        document.getElementById('write_cl').style.position = 'relative';
                        document.getElementById('show_second').style.position = 'inherit';
                        document.getElementById('show_second').style.visibility = 'visible';
                        document.getElementById('show_secondT').style.visibility = 'visible';
                        if (document.getElementById('show_second').checked)
                            document.getElementById('second_cl').style.visibility = 'visible';
                        elem = document.getElementById('Clinic');
                    }
                    else if (<?=STATUS_CALL_NOT?> == this.value || <?=STATUS_CALL_STOP?> == this.value ||
                    <?=STATUS_ERROR?> == this.value || <?=STATUS_CL_CANCEL?> == this.value ||
                    <?=STATUS_CLINIC_NOT?> == this.value || <?=STATUS_REPEAT?> == this.value ||
                    <?=STATUS_BREAK_LINE?> == this.value || <?=STATUS_CLOSED?> == this.value ||
                    <?=STATUS_NOT_COME?> == this.value) { // Недозвон/Отказ/Ошибка/Повторный/Незапись/Обрыв
                        document.getElementById('assign_cl').style.visibility = 'hidden';
                        document.getElementById('write_cl').style.visibility = 'hidden';
                        if (<?=STATUS_CALL_NOT?> == this.value) { // Недозвон
                            var call_back_date = '<?=$call_back_date?>';
                            var cur_date = '<?=date('d.m.Y H:i',mktime(date("H"),date("i")+15,0,date("m"),date("d"),date("Y")))?>';
                            elem = document.getElementById('call_back_picker');
                            if('' != call_back_date)
                                elem.value = call_back_date;
                            else elem.value = cur_date;
                        }
                        else if (<?=STATUS_ERROR?> == this.value) {
                            document.getElementById('status_detT').style.visibility = 'visible';
                            document.getElementById('status_det').style.visibility = 'visible';
                            elem = document.getElementById('status_det');
                        }
                    }
                    elem.focus();
                    }
                </script>
                <script type="text/javascript">
                    function SecondClient() {
                        if (document.getElementById('show_second').checked) {
                            document.getElementById('second_cl').style.visibility = 'visible';
                            document.getElementById('second_cl').style.position = 'inherit';
                            <?php
                            /*if (FALSE == DEBUG_MODE)
                                $insertclinic = "INSERT INTO CALL_BASE_CLINIC (ID, BASE_ID, CLIENT_STATUS) 
                        VALUES (SEQ_CALL_BASE_CLINIC_ID.nextval, {$base_id}, 99) returning ID into :max_clinic_id2";
                            else $insertclinic = "INSERT INTO CALL_BASE_CLINIC_TEST (ID, BASE_ID, CLIENT_STATUS) 
                        VALUES (SEQ_CALL_BASE_CLINIC_ID_TEST.nextval, {$base_id}, 99) returning ID into :max_clinic_id2";
                            $query = OCIParse(GetData::GetConnect(), $insertclinic);
                            OCIBindByName($query,":max_clinic_id2",$max_clinic_id2,16);
                            $query_result = OCIExecute($query);*/
                            ?>
                        } else {
                            document.getElementById('second_cl').style.visibility = 'hidden';
                            document.getElementById('second_cl').style.position = 'absolute';
                            <?php
                                /*if (isset($max_clinic_id2)) {
                                    if (FALSE == DEBUG_MODE)
                                        $deleteclinic = "DELETE FROM CALL_BASE_CLINIC WHERE ID = " . $max_clinic_id2;
                                    else $deleteclinic = "DELETE FROM CALL_BASE_CLINIC_TEST WHERE ID = " . $max_clinic_id2;
                                    if (TRUE == DEBUG_MODE) echo "<textarea>" . $deleteclinic . "</textarea><br/>";
                                    GetData::my_log($deleteclinic, FALSE);
                                    $query = OCIParse($c, $deleteclinic);
                                    $query_result = OCIExecute($query);
                                    if (!$query_result)
                                        GetData::my_log($deleteclinic, TRUE);
                                }*/
                            ?>
                        }
                    }
                </script>
            </h2>
        </div>

        <?php
        if (USER_ADMIN == $_SESSION['user_role'] || ($status_id_unic < STATUS_CALL_STOP && USER_VIEW != $_SESSION['user_role'])
            || in_array($_SESSION['login_id_med'],SPEC_USER_CALL) || SPEC_USER == $_SESSION['login_id_med']
            || STATUS_NOT_COME == $status_id_unic || STATUS_CLINIC == $status_id_unic
            || strtotime($date_stop) > strtotime("-3 days") || SERVICE_STOM != $srv_id) {
            echo '<input type="submit" name="save_but" id="save_but" value="Сохранить" class="send_button" ';
            if (USER_USER == $_SESSION['user_role'] && !in_array($_SESSION['login_id_med'],SPEC_USER_CALL) ||
                USER_SUPER == $_SESSION['user_role'] && count(GetData::$array_userd) == 0 ) {
                echo ' style="visibility: hidden; position: absolute" ';
            }
            echo '/>';
            echo '<input type="hidden" name="Base_Id" value="'.$base_id.'"/>';
            echo '<input type="hidden" name="serv_id" value="'.$srv_id.'"/>';
            echo '<input type="hidden" name="source_type" value="'.$source_auto_type.'"/>';
            echo '<input type="hidden" name="date_start_work" value="'.$date_start_work.'"/>';
			echo '<input type="hidden" id="sra_id" name="sra_id" value="'.$sra_id.'"/>';
            echo '<input type="hidden" id="pay_supplier" name="pay_supplier" value="'.$pay_supplier.'"/>';
            //echo '<input type="hidden" id="status_id_old" name="status_id_old" value="'.$status_id_unic.'"/>';
            echo '<br/>';
        }
        ?>
        <!--script type="text/javascript">
            function ServiceChanged() {
                var srv_id = '< ?php echo $srv_id;?>';
                var srv_id_sel = document.getElementById('ServiceId').value;
                var srv_det_id = '< ?php echo $srv_det_id;?>';
                if (srv_id !== srv_id_sel) {
                    document.getElementById('call_back_picker').required = '';
                    if (document.getElementById('save_but') && < ?=STATUS_NOT?> != document.getElementById("StatusId").value)
                        document.getElementById('save_but').style.visibility = 'visible';
                } else {
                    if (< ?=STATUS_CALL_BACK?> == document.getElementById("StatusId").value) {
                        document.getElementById('call_back_picker').required = 'required';
                    }
                    else document.getElementById('call_back_picker').required = '';
                    if (document.getElementById('save_but'))
                        document.getElementById('save_but').style.visibility = 'hidden';
                }

                elem = document.getElementById('Reservoir_new');
                /*if (document.getElementById('ServiceStom')) {
                    if (< ?=SERVICE_STOM?> == srv_id_sel)
                    {
                        elem = document.getElementById('ServiceStom');
                        document.getElementById('ServiceStom').style.visibility = 'visible';
                        /*document.getElementById('ServiceStom').val(srv_det_id).change();
                        if (0 == srv_det_id) {
                            document.getElementById('ServiceStom').style.backgroundColor = '< ?=needs?>';
                            document.getElementById('save_but').style.visibility = 'hidden';
                        }
                        else document.getElementById('ServiceStom').style.backgroundColor = 'white';*
                    }
                    else {
                        document.getElementById('ServiceStom').style.visibility = 'hidden';
                    }
                }*/
                elem.focus();
            }
        </script-->
    </form>

    <script type="text/javascript">
        jQuery(function($){
            $("#phone_mob").mask("8(999) 999-9999");
        });
    </script>
    <script type="text/javascript">
        jQuery(function($){
            $("#phone_new1").mask("8(999) 999-9999");
        });
    </script>
    <script type="text/javascript">
        jQuery(function($){
            $("#phone_new2").mask("8(999) 999-9999");
        });
    </script>
    <script>
        $('#call_back_picker').datetimepicker({
            format: 'd.m.Y H:i',
            lang: 'ru',
            timepicker: true,
            closeOnDateSelect: false
        });
    </script>
    <script>
        $('#write_cl_picker').datetimepicker({
            format: 'd.m.Y H:i',
            lang: 'ru',
            timepicker: true,
            closeOnDateSelect: false
        });
    </script>
    <script>
        $('#write_cl_picker2').datetimepicker({
            format: 'd.m.Y H:i',
            lang: 'ru',
            timepicker: true,
            closeOnDateSelect: false
        });
    </script>
    <script>
        function fCopyIE(pac) {
            var elem = document.getElementById('text_to_copy'); //нашли наш контейнер
            window.clipboardData.setData('Text',<?=$base_id?>+'\t'+document.getElementById('phone_new'+pac).value.replace(/\D/g, "")+'\t'+
                document.getElementById('surname_cl'+pac).value+'\t'+document.getElementById('name_cl'+pac).value+'\t'+
                document.getElementById('patronymic_cl'+pac).value);
            elem.innerText=window.clipboardData.getData('Text');
        }

        function fCopyChrome(pac) {
            window.getSelection().removeAllRanges();
            var elem = document.getElementById('text_to_copy'); //нашли наш контейнер
            elem.innerText = <?=$base_id?>+'\t'+document.getElementById('phone_new'+pac).value.replace(/\D/g, "")+'\t'+
                document.getElementById('surname_cl'+pac).value+'\t'+document.getElementById('name_cl'+pac).value+'\t'+
                document.getElementById('patronymic_cl'+pac).value;

            var targetId = "_hiddenCopyText_";
            var isInput = elem.tagName === "INPUT" || elem.tagName === "TEXTAREA";
            var origSelectionStart, origSelectionEnd;
            if (isInput) { // can just use the original source element for the selection and copy
                target = elem;
                origSelectionStart = elem.selectionStart;
                origSelectionEnd = elem.selectionEnd;
            } else { // must use a temporary form element for the selection and copy
                target = document.getElementById(targetId);
                if (!target) {
                    var target = document.createElement("textarea");
                    target.style.position = "absolute";
                    target.style.left = "-9999px";
                    target.style.top = "0";
                    target.id = targetId;
                    document.body.appendChild(target);
                }
                target.textContent = elem.textContent;
            }
            // select the content
            var currentFocus = document.activeElement;
            target.focus();
            target.setSelectionRange(0, target.value.length);

            // copy the selection
            var succeed;
            try {
                succeed = document.execCommand("copy");
            } catch (e) {
                succeed = false;
            }
            // restore original focus
            if (currentFocus && typeof currentFocus.focus === "function") {
                currentFocus.focus();
            }

            if (isInput) { // restore prior selection
                elem.setSelectionRange(origSelectionStart, origSelectionEnd);
            } else { // clear temporary content
                target.textContent = "";
            }
        }
    </script>

    <script>
        var oktell_server_address='';
		var oktell_phone_prefix='';
		var source_auto_id=document.getElementById('Istochnik_auto').value;
		var pervStatusUser='';
        getcurrentserveraddress();
        t=setInterval("getcurrentserveraddress()",2000);
        function getcurrentserveraddress() {
            var xml;
            if(window.XMLHttpRequest) {
                xml = new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/getcurrentserveraddress', true);
                xml.timeout = 2000;
                xml.send("");
                xml.onreadystatechange = function() {
                    if (4 === xml.readyState) {
                        if(200 === xml.status) {
                            var response=xml.responseText;
                            var regex=/serveraddress.*><!\[CDATA\[([^\]]*)\]/im;
                            var matches=response.match(regex);
                            if(matches[1] != oktell_server_address) {
                                oktell_server_address = matches[1];
                                ch_oktell_server_address(oktell_server_address);
                            }
                        }
                        else {
                            oktell_server_address='';
                            ch_oktell_server_address(oktell_server_address);
                        }
                    }
                }
            }
        }
        function ch_oktell_server_address(new_oktell_server_address) {
            //document.getElementById('oktell_server_address').value=oktell_server_address;
            if(oktell_server_address != '') {
                //document.getElementById('oktell_server_status').value='Октелл подключен'; //oktell_server_address;//
                if (document.getElementById('oktell_server_status')) {
                    document.getElementById('oktell_server_status').style.visibility = 'hidden';
                    document.getElementById('oktell_server_status').style.position = 'absolute';
                }
                if (document.getElementById('oktell_server_status_aon')) {
                    document.getElementById('oktell_server_status_aon').style.visibility = 'hidden';
                    document.getElementById('oktell_server_status_aon').style.position = 'absolute';
                }
				//получаем префикс набора
                xml = new window.XMLHttpRequest();
                xml.open("GET", '<?=PATH?>/get_oktell_out_prefix.php?'
                            +'oktell_server_address='+oktell_server_address
                            +'&source_auto_id='+source_auto_id, false);
                xml.send("");
				//alert(xml.responseText);	
                var response=xml.responseText;
                var regex=/prefix=([^\"]*);/im;
                if(matches=response.match(regex)) {
                    oktell_phone_prefix=matches[1];
					//alert(oktell_phone_prefix);
				}
				else {
					//ошибка получения префикса
					alert(xml.responseText);
					return;
				}
				
				//меняем статус кнопок
                if (document.getElementById('callto_button')) document.getElementById('callto_button').style.visibility = 'visible';
                if (document.getElementById('endcall_button')) document.getElementById('endcall_button').style.visibility = 'visible';
                if (document.getElementById('callto_button_aon')) document.getElementById('callto_button_aon').style.visibility = 'visible';
                if (document.getElementById('endcall_button_aon')) document.getElementById('endcall_button_aon').style.visibility = 'visible';
                ch_callto();
                //
            }
            else {
                if (document.getElementById('oktell_server_status')) {
                    document.getElementById('oktell_server_status').style.visibility = 'visible';
                    document.getElementById('oktell_server_status').value = 'Октелл не подключен';
                    document.getElementById('oktell_server_status').style.position = 'inherit';
                }
                if (document.getElementById('oktell_server_status_aon')) {
                    document.getElementById('oktell_server_status_aon').style.visibility = 'visible';
                    document.getElementById('oktell_server_status_aon').value = 'Октелл не подключен';
                    document.getElementById('oktell_server_status_aon').style.position = 'inherit';
                }
                oktell_phone_prefix='';
				//меняем статус кнопок
                ch_oktell_disconnected();
                if (document.getElementById('callto_button')) document.getElementById('callto_button').style.visibility = 'hidden';
                if (document.getElementById('endcall_button')) document.getElementById('endcall_button').style.visibility = 'hidden';
                if (document.getElementById('callto_button_aon')) document.getElementById('callto_button_aon').style.visibility = 'hidden';
                if (document.getElementById('endcall_button_aon')) document.getElementById('endcall_button_aon').style.visibility = 'hidden';
            }
        }
        function callto(phone_number,base_id) {
			var call_hist_id='';
			var IdChain='00000000-0000-0000-0000-000000000000';
			//if(document.getElementById('oktell_server_address').value=='') alert('Ошибка: Нет подключения к октелл');
            //else {
            //alert('Тест подключения к октелл');

                //проверка активного звонка и статуса пользователя
                xml=new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false);
                xml.send("");
                var response=xml.responseText;
                //document.getElementById('check_active_call').innerText=response;

                var regex=/IdChain:([^;]*)/im;
                matches=response.match(regex);
                var IdChain=matches[1];
                
                var regex=/IdUser:([^;]*)/im;
                matches=response.match(regex);
                var IdUser=matches[1];				
				
				if(IdChain!='00000000-0000-0000-0000-000000000000') {
                    alert('Сначала завершите активный звонок');
					ch_callto(); //энейблим кнопку
                    return;
                }
                var regex=/StatusUser:([^;]*)/im;
                matches=response.match(regex);
                var StatusUser=matches[1];
                pervStatusUser=StatusUser;
				if(StatusUser != '1' && StatusUser != '2' && StatusUser != '3') {
					alert('Статус пользователя не позволяет сделать исх. звонок');
                    ch_callto(); //энейблим кнопку
					return;
                }
                //если нет активного звонка
                //фиксируем факт нажатия кнопки и получаем идентификатор попытки звонка
                //функция отправки информации о попытке звонка в историю звонков
                xml = new window.XMLHttpRequest();
				xml.open("GET", 'put_call_to_hist.php?'
							+'call_hist_id='
                            +'&base_id='+base_id
                            +'&oktell_server_address='+oktell_server_address
                            +'&okt_IdChain='+IdChain
                            +'&okt_IdUser='+IdUser
                            +'&phone_prefix='+oktell_phone_prefix
                            +'&phone_number='+phone_number
                            +'&base_id='+base_id, false);
				xml.send("");			
                var response=xml.responseText;
				//alert(response);
				var regex=/new_id:([^;]*)/im;
                var matches=response.match(regex);
                var call_hist_id=matches[1];

				//alert(call_hist_id);
				
				//пытаемся набрать номер
                xml=new window.XMLHttpRequest();
				//alert(oktell_phone_prefix+phone_number);
                xml.open("GET", 'http://127.0.0.1:4059/callto?number='+oktell_phone_prefix+phone_number, false);
                xml.send("");
                //проверка активного звонка после набора номера
                //рекурсивный SetTimeout
				var iii=0;
				var t2=setTimeout(function CallMonitor() {
                    iii++;
					xml=new window.XMLHttpRequest();
                    xml.open("GET", 'http://127.0.0.1:4059/getcurrentcallinfo', false);
                    xml.send("");
                    var response=xml.responseText;
                    var regex=/mode.*value="([^"]*)"/im;
                    var matches=response.match(regex);

                    if(matches[1]=='calling' || matches[1]=='ringing' || matches[1]=='connected' || matches[1]=='flashed') {
                        //document.getElementById('check_active_calling').innerText=response;

                        //меняем статус кнопок
                        ch_endcall();

                        //если набор начался, то получаем ID пользователя и ID цепочки коммутаций
                        xml = new window.XMLHttpRequest();
                        xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false);
                        xml.send("");
                        var response=xml.responseText;
                        var regex=/IdChain:([^;]*)/im;
                        matches=response.match(regex);
                        var IdChain=matches[1];
						
						//перенесено выше, получение ID пользователя при первом запросе, когда это возможно
                        //var regex=/IdUser:([^;]*)/im;
                        //matches=response.match(regex);
                        //var IdUser=matches[1];
                        
						//document.getElementById('get_chain_id').value='user: '+IdUser+' chain: '+IdChain;
						
                        //функция отправки информации о попытке звонка в историю звонков
                        
						xml = new window.XMLHttpRequest();
                        xml.open("GET", 'put_call_to_hist.php?'
							+'call_hist_id='+call_hist_id
                            +'&base_id='+base_id
                            +'&oktell_server_address='+oktell_server_address
                            +'&okt_IdChain='+IdChain
                            +'&okt_IdUser='+IdUser
                            +'&phone_prefix='+oktell_phone_prefix
                            +'&phone_number='+phone_number
                            +'&base_id='+base_id, false);
                        xml.send("");
                        //alert(xml.responseText);
                    }
					else if(iii < 7) { //делаем 7 попыток мониторинга общей сложностью 1,5 сек.
						t2=setTimeout(CallMonitor,250);
					}
					else {//останавливаем цикл
						ch_callto();
					}
                },0)
            //}
        }
        function endcall() {
            //пытаемся завершить звонок
            xml = new window.XMLHttpRequest();
            xml.open("GET", 'http://127.0.0.1:4059/disconnectcall', false);
            xml.send("");
            //проверка активного звонка после завершения
            var t=setInterval(function() {
                xml = new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/getcurrentcallinfo', false);
                xml.send("");
                var response=xml.responseText;
                var regex=/mode.*value="([^"]*)"/im;
                var matches=response.match(regex);

                if(matches[1]=='none') {
                    //меняем статус кнопок
                    ch_callto();
                    //останавливаем цикл
                    clearInterval(t);
					if(pervStatusUser=='3') {
						//меняем статус пользователя на "Отсутсвует", если он стоял в этом статусе до совершения звонка
						xml=new window.XMLHttpRequest();
						xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-012-Set-User-State&async=0&timeout=10&startparam2=3', false);
						xml.send("");
					}	
                }
            },500)
        }
        //функции смены статусов кнопок
        function ch_callto() {
            if (document.getElementById('callto_button')) {
                document.getElementById('callto_button').disabled = false;
                document.getElementById('callto_button').style.backgroundImage = 'url("<?=PATH?>/images/call.png")';
            }
            if (document.getElementById('endcall_button')) {
                document.getElementById('endcall_button').disabled = true;
                document.getElementById('endcall_button').style.backgroundImage = '';
            }
            if (document.getElementById('callto_button_aon')) {
                document.getElementById('callto_button_aon').disabled = false;
                document.getElementById('callto_button_aon').style.backgroundImage = 'url("<?=PATH?>/images/call.png")';
            }
            if (document.getElementById('endcall_button_aon')) {
                document.getElementById('endcall_button_aon').disabled = true;
                document.getElementById('endcall_button_aon').style.backgroundImage = '';
            }
        }
        function ch_endcall() {
            if (document.getElementById('callto_button')) {
                document.getElementById('callto_button').disabled = true;
                document.getElementById('callto_button').style.backgroundImage = '';
            }
            if (document.getElementById('endcall_button')) {
                document.getElementById('endcall_button').disabled = false;
                document.getElementById('endcall_button').style.backgroundImage = 'url("<?=PATH?>/images/call_stop.png")';
            }
            if (document.getElementById('callto_button_aon')) {
                document.getElementById('callto_button_aon').disabled = true;
                document.getElementById('callto_button_aon').style.backgroundImage = '';
            }
            if (document.getElementById('endcall_button_aon')) {
                document.getElementById('endcall_button_aon').disabled = false;
                document.getElementById('endcall_button_aon').style.backgroundImage = 'url("<?=PATH?>/images/call_stop.png")';
            }
        }
        function ch_oktell_disconnected() {
            if (document.getElementById('callto_button')) document.getElementById('callto_button').disabled=true;
            if (document.getElementById('endcall_button')) document.getElementById('endcall_button').disabled=true;
            if (document.getElementById('callto_button_aon')) document.getElementById('callto_button_aon').disabled=true;
            if (document.getElementById('endcall_button_aon')) document.getElementById('endcall_button_aon').disabled=true;
        }
    </script>

    <script>
        function open_edit(found_id, usr_id, sid) {
            if (found_id > 0) {
                win = window.open("<?=PATH?>/med_call_out.php?base_id="+found_id+"&texnari_id="+usr_id+"&sid="+sid, "med_found_"+found_id, "width=800, height=640, toolbar=no, scrollbars=yes, resizable=yes");
                win.focus();
            }
        }
    </script>

    <?php
    if (($status_id_unic >= STATUS_CALL_STOP || CALL_SECOND == $call_double || isset($interstate) && 1 == $interstate)
        && (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])) {
        if (isset($texnari_id) && GetData::GetUsers(FALSE, FALSE, "usr.ID = ".$_SESSION['login_id_med'], "FIO") > 0 &&
            GetData::$array_user[0]['SMTP_SERVER'] != '' && GetData::$array_user[0]['SMTP_PORT'] != '' && GetData::$array_user[0]['SMTP_FROM'] != '' &&
            GetData::$array_user[0]['SMTP_LOGIN'] != '' && GetData::$array_user[0]['SMTP_PASS'] != '') {

            require_once "med_upload_file.php";

        }
    }
    /*if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {*/
        //echo '---------- Не обращайте внимания на этот блок. Идет подготовка к набору номера прямо с данного экрана ----------<br>';
        //echo "UserID: <input type='text' id='oktell_user_id' style='width: 21em' disabled><br>";
// Состояние сервера: <input type='text' id='oktell_server_status'>";
        //echo "Адрес сервера: ".$oktell_server_address."<input type='text' id='oktell_server_address' disabled><br>";
        //echo "Позвонить на 89262392967: <input disabled id=callto_button type=button onclick=callto('89262392967','111111') value='позвонить'></input><br>";
        //echo "Завершить звонок: <input disabled id=endcall_button type=button onclick=endcall() value='завершить звонок'></input><br>";
        //echo "Проверка активного звонка<br>";
        //echo "<textarea id=check_active_call></textarea>";
        //echo "Идет дозвон<br>";
        //echo "<textarea id=check_active_calling></textarea>";
        //echo "ID Цепочки<br>";
        //echo "<textarea id=get_chain_id></textarea>";
    /*}*/

    function show_array($arr,$lvl=0,$varnames=array()) {
        if(is_array($arr) && count($arr)>0) {echo "<table border=5>"; /* } */
            $lvl++;
            foreach($arr as $key=>$val) {
                echo "<tr>";
                if(is_array($val)) {
                    $varnames[$lvl]=$key;
                    echo "<td>";
                    for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
                    echo "= array(";
                    show_array($val,$lvl,$varnames);
                    echo ")</td>";
                }
                else {
                    $varnames[$lvl]=$key;
                    echo "<td>";
                    for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
                    echo "= $val </td>";
                }
                echo "</tr>";
            }
            /*if(count($arr)>0) {*/echo "</table>";}
    }
    ?>

</div>

</body>
</html>