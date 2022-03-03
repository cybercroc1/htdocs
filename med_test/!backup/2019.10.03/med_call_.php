<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);
require_once 'funct.php';

include("med/conn_string.cfg.php");
include("med/smtp_conf.php");
include("send_email.php");
include("phone_conv_single.php");

require_once "check_ip.php";
//if(!check_local_network($_SERVER['REMOTE_ADDR'])) {echo "<font color=red><b>Запрещенный IP</b></font>"; exit();}

?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./js/number-polyfill.css">
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>Входящий звонок</title>
    <base href="/">
    <meta name="description" content="Входящий звонок">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?=PATH?>/js/jquery.maskedinput.js"></script>
    <script src="<?=PATH?>/js/number-polyfill.min.js"></script>
    <script src="<?=PATH?>/js/number-polyfill.js"></script>
    <style> .error {color: #FF0000;} </style>

    <script>
    function OperatorSelected() {
        elem = document.getElementById('OperatorsId');
        arrval = elem.value.split(':');
        if (arrval[0] != -1) {
            elem.style.backgroundColor = 'white';
            if (document.getElementById('PurposeId').value != <?=THEME_NOT?> &&
                document.getElementById('ServiceId').value != <?=SERVICE_NOT?> &&
                document.getElementById('Reservoir').value != <?=SOURCE_NOT?>)
                document.getElementById('save_but').style.visibility = 'visible';
            document.getElementById('call_center').value = arrval[1];
            /*if (arrval[1] != '')
                document.getElementById('call_center').disabled = true;
            else document.getElementById('call_center').disabled = false;*/
            elem = document.getElementById('save_but');
        }
        else {
            elem.style.backgroundColor = '<?=needs?>';
            document.getElementById('save_but').style.visibility = 'hidden';
            document.getElementById('call_center').value = '';
        }
        if (elem) elem.focus();
    }
    </script>

    <?php
    if (isset($getoper)) { ?>
        <script>
        if (<?=RESULT_KC?> == parent.document.getElementById("ResultId").value) { // если уже выбран перевод в КЦ
            if (parent.document.getElementById('save_but'))
                parent.document.getElementById('save_but').style.visibility = 'hidden'; // ибо список операторов поменяется
            parent.document.getElementById('call_center').value = '';
        }
        else {
            if (parent.document.getElementById("PurposeId").value >= <?=THEME_INFO?> ||
                <?=THEME_MED?> == parent.document.getElementById("PurposeId").value &&
                <?=SERVICE_NOT?> != parent.document.getElementById("ServiceId").value &&
                /*(< ?=SERVICE_STOM?> != parent.document.getElementById("ServiceId").value ||
                < ?=STOM_NOT?> != parent.document.getElementById("ServiceStom").value) &&*/
                <?=SOURCE_NOT?> != parent.document.getElementById("Reservoir").value &&
                <?=RESULT_NOT?> != parent.document.getElementById("ResultId").value &&
                (<?=RESULT_WAIT?> == parent.document.getElementById("ResultId").value ||
                parent.document.getElementById("OperatorsId").value > 0)
            ) {
                parent.document.getElementById("save_but").style.visibility = "visible";
            } else {
                parent.document.getElementById("save_but").style.visibility = "hidden";
            }
        }
        </script>

        <?php
        if (SERVICE_NOT == $getoper) {
            echo "<script>parent.document.getElementById('ServiceId').style.backgroundColor = '" . needs . "';</script>";
            echo "<script>elem = parent.document.getElementById('ServiceId');</script>";

            echo "<script>parent.document.getElementById('assign_clT').innerHTML='&nbsp;Услуга не выбрана!';</script>";
            echo "<script>parent.document.getElementById('assign_cl').innerHTML='';</script>";
        }
        else {
            /*if (SERVICE_STOM == $getoper) {
                echo "<script>elem = parent.document.getElementById('ServiceStom'); elem.style.visibility = 'visible';</script>";
            }
            else {*/
                //echo "<script>parent.document.getElementById('ServiceStom').style.visibility = 'hidden';</script>";
                echo "<script>parent.document.getElementById('ServiceId').style.backgroundColor = 'white';</script>";
                echo "<script>elem = parent.document.getElementById('Reservoir');</script>";
            //}

            //echo "<iframe name=ifr1 style='display:none; width: 90%'></iframe>";
            $sel = "<select id=\"Reservoir\" name=\"Reservoir\" style=\"background-color:".needs."\" onchange=\"ifr1.location=\'".PATH."/med_call.php?getdet=\'+this.value\">";
            $sel .= "<option value=\"".SOURCE_NOT."\">Выберите источник рекламы</option>";
            if (GetData::GetIstochnik(FALSE,FALSE,"(instr(in_dep, '".$getoper."') != 0)", FALSE) > 0)
            {
                foreach(GetData::$array_istochnik as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        $value['DETAIL'] = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                    }
                    $sel .= "<option value=\"".$value['ID']."\">".$value['NAME']."</option>";
                }
            }
            $sel .= "</select>";
            echo "<script>parent.document.getElementById('AllInOne').innerHTML='';</script>";
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='';</script>";
            echo "<script>parent.document.getElementById('AllInOneIst').innerHTML='Источник рекламы:&nbsp;';</script>";
            echo "<script>parent.document.getElementById('SelectIst').innerHTML='" . $sel . "';</script>";
            //echo "<script>$('#Reservoir').val('".SOURCE_NOT."').change();</script>";

            $arr_filt = array('serv' => $getoper, 'ist' => -1); // ибо оператор входящих не залогинен
            if (GetData::GetUsersDep(FALSE, NULL, $arr_filt, 'mix') > 0) {
                $sel = "<select id=\"OperatorsId\" name=\"OperatorsId\" onchange=\"OperatorSelected();\" style=\"background-color:".needs."\">";
                $sel .= "<option value=\"-1\">Выберите оператора</option>";
                $bAct = TRUE; $sel .= "<optgroup label=\"Активные\">";
                foreach(GetData::$array_userd as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                    if ($bAct && 2 == $value['ACTIVE']) {
                        $bAct = FALSE;
                        $sel .= "</optgroup>";
                        $sel .= "<optgroup label=\"Отключены\">";
                    }
                    $sel .= "<option value=\"" . $value['ID'] . ":".$value['PIN']."\">" . $value['FIO'] . "</option>";
                }
                $sel .= "</optgroup>";
            }
            else {
                $sel = "<select id=\"OperatorsId\" name=\"OperatorsId\" disabled style=\"color:".needs."\">";
                $sel .= "<option value=\"\">Нет доступных операторов</option>";
            }
            $sel .= "</select>";
            echo "<script>parent.document.getElementById('assign_clT').innerHTML='&nbsp;Оператор: ';</script>";
            echo "<script>parent.document.getElementById('assign_cl').innerHTML='&nbsp;" . $sel . "';</script>";
            /*echo "<script type = 'text/javascript'> var sel = parent.document.getElementById('OperatorsId');
                if (sel) { sel.onchange = function() { 
                    if (sel.value > 0) {
                        sel.style.backgroundColor = 'white';
                        if (parent.document.getElementById('PurposeId').value != " . THEME_NOT . " &&
                            parent.document.getElementById('ServiceId').value != " . SERVICE_NOT . " &&
                            parent.document.getElementById('Reservoir').value != " . SOURCE_NOT . ")
                          parent.document.getElementById('save_but').style.visibility = 'visible';
                    }
                    else {
                        sel.style.backgroundColor = '" . needs . "';
                        parent.document.getElementById('save_but').style.visibility = 'hidden';
                    } 
                }}
            </script>";*/
        }
        echo "<script>if (elem) elem.focus();</script>";

        exit();
    }

    if (isset($getdet))
    {
        if (SOURCE_NOT == $getdet)
            echo "<script>parent.document.getElementById('Reservoir').style.backgroundColor = '".needs."';</script>";
        else echo "<script>parent.document.getElementById('Reservoir').style.backgroundColor = 'white';</script>";

        $nrows = GetData::GetSourceDetail(FALSE, NULL, $getdet);
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
                $nrows = GetData::GetIstochnik(FALSE,FALSE,"ID = " . $getdet, FALSE);
                $strtitle = 'Детализация';
                if (isset(GetData::$array_istochnik)) {
                    foreach(GetData::$array_istochnik as $key => $value) {
                        $strtitle = $value['DETAIL'];
                    }
                }
                $getdetailstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id=" . $getdet . " and DELETED IS NULL";
            }
            if (TRUE == ENCODE_UTF)
                $strtitle = iconv ('utf-8', 'windows-1251', $strtitle);
            echo "<script>elem = parent.document.getElementById('AllInOne'); if (elem) elem.innerHTML='&nbsp;" . $strtitle . ": ';</script>";
            $i = 0;
            $sel = "<select id=\"DetailList\" name=\"DetailList\">";
            $sel .= "<option value=\"\">Выберите детализацию</option>";
            $q = OCIParse($c, $getdetailstr);
            if (OCIExecute($q)) {
                while (OCIFetch($q)) {
                    $sel .= "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
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
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='&nbsp;" . $sel . "';</script>";
            echo "<script>elem = parent.document.getElementById('DetailList'); if (elem) elem.focus();</script>";
            echo "<script type = 'text/javascript'> var sel = parent.document.getElementById('DetailList');
                if (sel) { sel.onchange = function() { var elem = parent.document.getElementById('comment'); elem.focus(); } }
                </script>";
        }
        else {
            echo "<script>parent.document.getElementById('AllInOne').innerHTML='';</script>";
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='';</script>";
            echo "<script>elem = parent.document.getElementById('comment'); elem.focus();</script>";
        }

        if (SOURCE_NOT == $getdet ) {
            echo "<script>parent.document.getElementById('save_but').style.visibility = 'hidden';</script>";
        }
        else echo "<script> 
             if (parent.document.getElementById('ServiceId').value != ".SERVICE_NOT." &&
                 parent.document.getElementById('ResultId').value  != ".RESULT_NOT." &&
                 (parent.document.getElementById('ResultId').value == ".RESULT_WAIT." ||
                  parent.document.getElementById('OperatorsId').value > 0))
                 parent.document.getElementById('save_but').style.visibility = 'visible';
             </script>";

        exit();
    }
    ?>

    <script type="text/javascript">
    function CheckFields() {
        var inpObj = document.getElementById("call_center");
        if (!inpObj.checkValidity()) {
            document.getElementById("valid_str").innerHTML = inpObj.validationMessage;
        }/* else {
            document.getElementById("valid_str").innerHTML = "Input OK";
        }*/
    }
    </script>

    <script type="text/javascript">
    function PurposeSelected() {
        if (<?=THEME_MED?> == document.getElementById("PurposeId").value) { // Для Услуг доступен их выбор
            elem = document.getElementById("ServiceId");
            document.getElementById('PurposeId').style.backgroundColor = 'white';
            document.getElementById('ServiceId').style.visibility = 'visible';
            document.getElementById('ServiceT').style.visibility = 'visible';
            /*document.getElementById('ServiceStom').style.visibility =
                (< ?=SERVICE_STOM?> == document.getElementById("ServiceId").value ? 'visible' : 'hidden');*/
            //document.getElementById('CallType').style.visibility = 'visible';
            document.getElementById('NotTarget').style.visibility = 'visible';
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (<?=RESULT_KC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (<?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            elem = document.getElementById("save_but");
            if (<?=THEME_NOT?> == document.getElementById("PurposeId").value)
                document.getElementById('PurposeId').style.backgroundColor = '<?=needs?>';
            else document.getElementById('PurposeId').style.backgroundColor = 'white';
            document.getElementById('ServiceId').style.visibility = 'hidden';
            document.getElementById('ServiceT').style.visibility = 'hidden';
            //document.getElementById('ServiceStom').style.visibility = 'hidden';
            //document.getElementById('CallType').style.visibility = 'hidden';
            document.getElementById('NotTarget').style.visibility = 'hidden';
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }

        if (document.getElementById("PurposeId").value >= <?=THEME_INFO?> ||
            <?=THEME_MED?> == document.getElementById("PurposeId").value &&
            <?=SERVICE_NOT?> != document.getElementById("ServiceId").value &&
            /*(< ?=SERVICE_STOM?> != document.getElementById("ServiceId").value ||
             < ?=STOM_NOT?> != document.getElementById("ServiceStom").value) &&*/
            <?=SOURCE_NOT?> != document.getElementById("Reservoir").value &&
            <?=RESULT_NOT?> != document.getElementById("ResultId").value &&
            (<?=RESULT_WAIT?> == parent.document.getElementById("ResultId").value ||
            parent.document.getElementById("OperatorsId").value > 0)
        ) {
            document.getElementById('save_but').style.visibility = 'visible';
        } else {
            document.getElementById('save_but').style.visibility = 'hidden';
        }
        elem.focus();
    }

    function ResultSelected()
    {
        res_id = document.getElementById("ResultId").value;
        document.getElementById('ResultId').style.backgroundColor = 'white';
        document.getElementById('KC_Number').style.visibility = 'hidden';
        document.getElementById('write_cl').style.visibility = 'hidden';
        if ( <?=RESULT_KC?> == res_id ) { // Перевод в КЦ
            //elem = document.getElementById('call_center');
            elem = document.getElementById('OperatorsId');
            document.getElementById('KC_Number').style.visibility = 'visible';
            //document.getElementById('call_center').required = 'required';
        } else if ( <?=RESULT_CLINIC?> == res_id ) { // Перевод в Клинику
            elem = document.getElementById('call_clinic');
            document.getElementById('write_cl').style.visibility = 'visible';
            //document.getElementById('call_center').required = '';
        } else if ( <?=RESULT_WAIT?> == res_id || <?=RESULT_AON?> == res_id) { // Ждет звонка/АОН
            elem = document.getElementById('save_but');
            //document.getElementById('call_center').required = '';
        } else {
            elem = document.getElementById('save_but');
            document.getElementById('ResultId').style.backgroundColor = '<?=needs?>';
            //document.getElementById('call_center').required = '';
        }

        if (document.getElementById("PurposeId").value >= <?=THEME_INFO?> ||
            <?=THEME_MED?> == document.getElementById("PurposeId").value &&
            <?=SERVICE_NOT?> != document.getElementById("ServiceId").value &&
            /*(< ?=SERVICE_STOM?> != document.getElementById("ServiceId").value ||
            < ?=STOM_NOT?> != document.getElementById("ServiceStom").value) &&*/
            <?=SOURCE_NOT?>  != document.getElementById("Reservoir").value &&
            <?=RESULT_NOT?>  != res_id &&
            (<?=RESULT_WAIT?> == res_id || <?=RESULT_AON?> == res_id ||
            parent.document.getElementById("OperatorsId").value > 0)
        ) {
            document.getElementById('save_but').style.visibility = 'visible';
        } else {
            document.getElementById('save_but').style.visibility = 'hidden';
        }
        elem.focus();
    }

    /*function FirstNoCheck() {
        if (document.getElementById('FirstCall').checked) {
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (< ?=RESULT_KC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (< ?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }
    }*/
    </script>
</head>
<body>
<?php
$nameErr = "";
if ("POST" == $_SERVER["REQUEST_METHOD"] && isset($save_but)) { // Принимаем данные из формы
    if (THEME_MED == $PurposeId) {
        $service = (isset($ServiceId) ? $ServiceId : NULL); // тут Id Услуги
        $reservoir = (isset($Reservoir) ? $Reservoir : NULL); // тут Id Источника
        $source_man_det = $Result_det = "NULL";

        $comment = (isset($comment) ? htmlspecialchars($comment, ENT_QUOTES) : "");
        $surname = (isset($surname) ? stripcslashes(htmlspecialchars($surname,ENT_QUOTES)) : "---");
        //$name = (isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : "---");
        //$patronymic = (isset($_POST['patronymic']) ? htmlspecialchars($_POST['patronymic'], ENT_QUOTES) : "---");
        //$ages = "NULL"; //(isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : 0);
        
		//(sva 23/04/2018) -------------------------------------------------------
		//$phone_mob = (isset($_POST['phone_mob']) ? $_POST['phone_mob'],0,14) : "");
		$phone_mob = (isset($phone_mob) ? $phone_mob : "");
        $phone_mob_norm = phone_norm_single($phone_mob,'ru_dial');
		
		if($phone_mob<>'' && $phone_mob_norm=='') { //если в поле телефона что то ввели, а нормализатор не смог привести его в нормальный вид
			$phone_mob_seg = $phone_mob;
			//$phone_mob_norm=$phone_mob;
		} //Данная ситуация означает, что номер веден неверно! Сюда нужно добавить вывод ошибки, а пока, чтобы не потерять введенную оператором информацию, пишем номер, как есть.
		else {		
			$phone_mob_seg = phone_segment($phone_mob_norm,NULL);
		}
		//(sva 23/04/2018) -------------------------------------------------------
		
		$phone_new = ""; //(isset($_POST['phone_new']) ? $_POST['phone_new'] : "");
        //$email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : "");
        $ResultId = (isset($ResultId) ? $ResultId : RESULT_WAIT);
        $call_center = (isset($call_center) ? $call_center : "");
        $OperatorsId = (isset($OperatorsId) ? $OperatorsId : "");
        if ("" != $OperatorsId)
            $OperatorsId = substr($OperatorsId, 0, strpos($OperatorsId, ':'));
        $clinic = (isset($call_clinic) ? $call_clinic : "");
    } else {
        $service = $reservoir = $source_man_det = "NULL";
        $ResultId = $Result_det = $call_center = $OperatorsId = $clinic = "NULL";
        $comment = $surname = $name = $patronymic = $phone_mob = $phone_mob_norm = $phone_new = $phone_new_norm = "";
    }

    /*$service_det = "NULL";
        if (SERVICE_STOM == $service) { // Детализация для стоматологии
        if (isset($ServiceStom)) {
            $service_det = $ServiceStom;
        }
    }*/

    //if ($reservoir < SOURCE_2GIS || SOURCE_BANNER_SUB == $reservoir) { // Что-то из списков
        if (isset($DetailList)) {
            $source_man_det = $DetailList;
        }
    //}

    if (RESULT_KC == $ResultId) {
        if (intval($call_center) != strval($call_center) || !is_int($call_center)) {
            $nameErr = "Требуется числовое значение";
            //exit;
        } else {
            $call_center = check_input($call_center);
        }
    }
    if (RESULT_KC == $ResultId) { // Перевели в КЦ
        $Result_det = (NULL != $call_center ? $call_center : NULL);
    } elseif (RESULT_CLINIC == $ResultId) { // Перевели в Клинику
        $Result_det = $clinic;
    } elseif (RESULT_WAIT == $ResultId || RESULT_AON == $ResultId) { // Ждет звонка/АОН
        $Result_det = NULL;
    }

    //Обновляем таблицу введенными данными
    $status = STATUS_OPEN;
    $query_result = FALSE;
	$fio = substr($surname,0,64);// . "/" . $name . "/" . $patronymic;

    if (FALSE == DEBUG_MODE)
        $table_name = 'CALL_BASE';
    else $table_name = 'CALL_BASE_TEST';
    if (THEME_MED == $PurposeId) {
        $updatestr = "UPDATE ".$table_name." SET CALL_THEME_ID = {$PurposeId}, SOURCE_MAN_ID = {$reservoir}, SERVICE_ID = {$service}, 
    COMMENTS = '{$comment}', CLIENT_NAME = '{$fio}', PHONE_MOB = '{$phone_mob_seg}', PHONE_MOB_NORM = '{$phone_mob_norm}', CALL_BACK_NUM = 10, 
    RESULT_ID = {$ResultId}, LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        /*if (isset($service_det) && NULL != $service_det && 'NULL' != $service_det)
            $updatestr .= ", SERVICE_DET_ID = {$service_det}";*/
        if (SERVICE_STOM == $service)
            $updatestr .= ", SERVICE_DET_ID = ".STOM_NOT;
        if (isset($source_man_det) && NULL != $source_man_det && 'NULL' != $source_man_det)
            $updatestr .= ", SOURCE_MAN_DET_ID = {$source_man_det}";
        if ($Result_det && 'NULL' != $Result_det)
            $updatestr .= ", RESULT_DET = {$Result_det}";
        if (isset($interstate))
            $updatestr .= ", INTERSTATE = 1";
        if (RESULT_WAIT == $ResultId || RESULT_AON == $ResultId)
            $updatestr .= ", STATUS_ID = ".STATUS_OPEN;
        elseif (RESULT_KC == $ResultId) {
            $updatestr .= ", STATUS_ID = ".STATUS_WORK.", FIO_ID = '{$OperatorsId}'"; // сразу назначаем оператору исходящих, раз он выбран
            $trans_arr = date_parse(date("Y-m-d HH:MM"));
            $const_str = $trans_arr['year'] . '-' . $trans_arr['month'] . '-' . $trans_arr['day'];
            $num_str = GetData::GetTransferNum($const_str);
            $updatestr .= ", TRANSFER_NUM = '" . $const_str . '-' . $num_str . "'";
        }
        elseif (RESULT_CLINIC == $ResultId) { // переведенные в клинику сразу в закрытое состояние
            $updatestr .= ", STATUS_ID = " . STATUS_CLINIC_CALL .", DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        }
    }
    else { // немедицинские сразу в закрытое состояние
        $updatestr = "UPDATE ".$table_name." SET CALL_THEME_ID = {$PurposeId}, STATUS_ID = " . STATUS_CLOSED .",
         LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
         DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
    }
    $updatestr .= " WHERE ID = " . $max_call;
if (TRUE == DEBUG_MODE) echo "<br/><textarea>" . $updatestr . "</textarea><br/>";
    GetData::my_log($updatestr, FALSE);
    $query = OCIParse($c, $updatestr);
    $query_result = OCIExecute($query);
    if (!$query_result)
        GetData::my_log($updatestr, TRUE);

// для медицинских заявок добавляем первую строку истории по этому звонку c именем оператора
    if ($query_result && THEME_MED == $PurposeId) {
        $date_det = date("d-m-Y H:i:s");
        if (FALSE == DEBUG_MODE)
            $insertstr = "INSERT INTO CALL_BASE_HIST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID.nextval, $max_call, '{$sc_agid}', $status, 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '{$comment}', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
        else $insertstr = "INSERT INTO CALL_BASE_HIST_TEST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID_TEST.nextval, $max_call, '{$sc_agid}', $status, 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '{$comment}', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
if (TRUE == DEBUG_MODE) echo "<textarea>".$insertstr."</textarea><br/>";
        GetData::my_log($insertstr, FALSE);
        $query = OCIParse($c, $insertstr);
        $query_result = OCIExecute($query);
        if (!$query_result)
            GetData::my_log($insertstr, TRUE);

        if (RESULT_KC == $ResultId && $query_result && THEME_MED == $PurposeId &&
            isset($OperatorsId) && $OperatorsId != "" && $OperatorsId != "NULL" && $OperatorsId != '-1')
        {
            if (FALSE == DEBUG_MODE)
                $insertstr = "INSERT INTO CALL_BASE_HIST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID.nextval, $max_call, '{$sc_agid}', ".STATUS_WORK.", 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '(fio_id=".$OperatorsId.") перевод в КЦ', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
            else $insertstr = "INSERT INTO CALL_BASE_HIST_TEST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID_TEST.nextval, $max_call, '{$sc_agid}', ".STATUS_WORK.", 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '(fio_id=".$OperatorsId.") перевод в КЦ', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
if (TRUE == DEBUG_MODE) echo "<textarea>".$insertstr."</textarea><br/>";
            GetData::my_log($insertstr, FALSE);
            $query = OCIParse($c, $insertstr);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($insertstr, TRUE);
        }
    }
    oci_free_statement($query);

    // Отправка письма супервизору
    if (GetData::GetIstochnik(TRUE,TRUE,"ID=".$reservoir, FALSE) > 0) {
        $reservoir_text = GetData::$array_istochnik[0]['NAME'];
    }
    else $reservoir_text = $reservoir;
    if (GetData::GetThemes("ID=".$PurposeId) > 0) {
        $theme_text = GetData::$array_theme[0]['NAME'];
    }
    else $theme_text = $PurposeId;

    $detail_text = $source_man_det;
    if (SOURCE_FLAER == $reservoir || SOURCE_CATALOG == $reservoir ||
        SOURCE_FLAER_SUB == $reservoir || SOURCE_FLAER_CAR == $reservoir ||
        SOURCE_LIFT == $reservoir || SOURCE_STOP == $reservoir) {
        $nrows = GetData::GetSubway(NULL); // Все одинаково с Флаером (пока?)
        $array_todo = GetData::$array_subway;
    } elseif (SOURCE_SERT == $reservoir) {
        $nrows = GetData::GetHospitals(NULL);
        $array_todo = GetData::$array_hospitals;
    } else {
        $nrows = GetData::GetSourceDetail(FALSE, NULL, $reservoir);
        $array_todo = GetData::$array_details;
    }
    if ($nrows > 0) {
        foreach ($array_todo as $key => $value) {
            if ($source_man_det == $value['ID'])
                $detail_text =  $value['NAME'];
        }
    }

    //$from_email = $reply_to_email = "report@wilstream.ru";
    //$from_name = $reply_to_name = "Служба отправки";
    // тема письма
    $headers="MIME-Version: 1.0 \n";
    $headers.="Content-Type: text/html; charset=\"windows-1251\"\n";
    $mess_subj = $source_auto_name." - ".$bnumber." Тема звонка - ".$theme_text." (".$sc_call_id.")";
    if (RESULT_KC == $ResultId) $mess_subj .= ' (Переводной)';
    $mess_subj .= ' Заявка - ' . $max_call;

    // текст письма
    $mess = "<html><head><title>Информация о заявке</title></head><body><table>
        <tr><th>ID звонка:</th><th>".$sc_call_id."</th></tr>
        <tr><td><b>Направление звонка:</b></td><td>".CALL_WAY[$call_direction]."</td></tr>
        <tr><td><b>Дата звонка:</b></td><td>".$date_call."</td></tr>
        <tr><td><b>АОН:</b></td><td>".$anumber."</td></tr>
        <tr><td><b>Маршрутный номер:</b></td><td>".$bnumber."</td></tr>
        <tr><td><b>Оператор:</b></td><td>".$sc_agid."</td></tr>";
    if (THEME_MED == $PurposeId) {
        $mess .= "<tr><td><b>Какие медицинские услуги интересуют:</b></td><td>".SERVICE_LIST[$service]."</td></tr>
        <tr><td><b>Источник рекламы(Авто):</b></td><td>".$source_auto_name."</td></tr>
        <tr><td><b>Источник рекламы(Ручной):</b></td><td>" . $reservoir_text;
        if (isset($detail_text) && "NULL" != $detail_text)
            $mess .= " (".$detail_text.")";
        $mess .= "</td></tr>";
        $mess .= "<tr><td><b>ФИО:</b></td><td>".$fio."</td></tr>
        <tr><td><b>Телефон:</b></td><td>".$phone_mob."</td></tr>";
        if (RESULT_KC == $ResultId && $Result_det)
            $mess .= "<tr><td><b>Соединили с:</b></td><td>".$Result_det."</td></tr>";
            //$mess .= "<tr><td><b>Соединили с:</b></td><td>".$Result_det."&nbsp;\tПин оператора: \t???</td></tr>";
        $mess .= "<tr><td><b>Комментарий:</b></td><td>".$comment."</td></tr>";
    }
    $mess .= "</table></body></html>";

    if (THEME_MED == $PurposeId && TRUE == SEND_EMAIL && ($query_result || DEBUG_MODE)) {
        if (GetData::GetSupervisorByService(NULL, $service) > 0) {
            foreach(GetData::$array_supers as $key => $value) {
                if (isset($value['EMAIL'])) {
                    $to_email = $value['EMAIL'];
                    $to_name = $value['FIO'];
                }
                $res = "";
                //отправка с резервированием каналов
                foreach ($smtp_conf as $conf_num => $cur_smtp_conf) {
                    if (DEBUG_MODE) echo "<br>----------------------------------------------</br>";
                    $res = send_email(
                        $cur_smtp_conf['smtp_server'],
                        $cur_smtp_conf['smtp_port'],
                        $cur_smtp_conf['smtp_auth_login'],
                        $cur_smtp_conf['smtp_auth_pass'],
                        $to_name,
                        $to_email,
                        $cur_smtp_conf['smtp_from_name'],
                        $cur_smtp_conf['smtp_from_email'],
                        //$from_name, $from_email,
                        $reply_to_name = '',
                        $reply_to_email = '',
                        $mess_subj,
                        $mess,
                        $headers,
                        $debug = 'n'
                    );
                    /*$email_res = send_email($smtp_server, $smtp_port, $smtp_auth_login, $smtp_auth_pass,
                        $to_name, $to_email, $smtp_from_name, $smtp_from_email, $reply_to_name, $reply_to_email,
                        $mess_subj, $mess, '', $debug = 'y');*/
                    if ('OK' == substr($res,0,2)) {
                        echo "<p style='font-size: larger; color: green'>Супервайзеру ".$to_name." отправлен E-Mail с параметрами входящего звонка - ".$res."</p>";
                        break;
                    }
                    else {
                        echo "<p style='font-size: larger; color: red'>Ошибка отправки E-mail!!! - </p>" . $res;
                    }
                }
            }
        }
    } elseif(DEBUG_MODE) {
        echo "Нижеприведенное письмо о новой заявке может быть отправлено нуждающимся.";
        echo $mess;
    }

    if ($query_result) {
        echo "<p style='font-size: larger; color: green'>Звонок успешно добавлен в базу данных. </p>";
        if (RESULT_KC == $ResultId) {
            //echo "<h1>&nbsp;Код перевода:&nbsp;<span style='color: black; font-size: smaller;'>" . $const_str . "-</span>
            echo "<h1>&nbsp;Код перевода:&nbsp;
            <span style='color: red; font-size: larger; border-bottom: dashed'>" . $num_str . "</span><br/>
            <span style='color: maroon; font-size: smaller; font-style: italic'>(Сообщите этот код оператору исходящего отдела)</span></h1>";
        }
        echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close();'>Закрыть</button>";
        /*} else {
            print "<script language='Javascript'> function close_win() { window.close(); } setTimeout('close_win()', 100);</script>";
        }*/
    } else {
        echo "<p style='font-size: larger; color: red'>Произошла ошибка сохранения записи!</p>";
    }
    unset($save_but);

    exit;
}

function check_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

echo '<div style="display: inline-block;">
    <a href="./"><h1 class="heading" style="margin-top: -5px; margin-bottom: 1px;">Входящий звонок</h1></a>
</div>';

//----------------------------------------
$source_auto_id = 0;
$source_auto_name = "???";
if (isset($sc_call_id) && NULL != $sc_call_id && isset($sc_project_id) && NULL != $sc_project_id) {
    if (isset($bnumber) && NULL != $bnumber) {
        $nrowsAuto = GetData::GetSourceAuto("DELETED IS NULL", $bnumber, FALSE);
        if (isset(GetData::$array_source_auto)) {
            $source_auto_id = GetData::$array_source_auto[0];
            if (TRUE == ENCODE_UTF)
                $source_auto_name = iconv('windows-1251', 'utf-8', GetData::$array_source_auto[2]);
            else $source_auto_name = GetData::$array_source_auto[2];
        }
    }
}

$date_call = date("d-m-Y H:i:s");
if(!isset($oktell_idchain)) $oktell_idchain='';
if(!isset($oktell_srv_id)) $oktell_srv_id='';

if (!isset($source_auto_id) || 0 == $source_auto_id || NULL == $source_auto_id) {
    echo "<h1 style='color: red'>Ошибка данных!!!</h1>";
    if (!isset($sc_call_id) || NULL == $sc_call_id)
        echo "<h2>Сообщите админу, что <span style='color: mediumvioletred; border-bottom: dashed'>sc_call_id пустой!</span></h2><br/>";
    if (!isset($sc_project_id) || NULL == $sc_project_id)
        echo "<h2>Сообщите админу, что <span style='color: mediumvioletred; border-bottom: dashed'>sc_project_id пустой!</span></h2><br/>";
    echo "<h2>Автоопределение источника рекламы не удалось!<br/>";
    if (!isset($bnumber) || NULL != $bnumber)
        echo "Сообщите админу ошибочный номер BNumber: <span style='color: mediumvioletred; text-decoration: underline'>".$bnumber."</span>";
    else echo "Сообщите админу, что <span style='color: mediumvioletred; border-bottom: dashed'>BNumber пустой!</span>";
    echo "</h2>";
} else { // Сразу вставляем строку в CALL_BASE с начальными данными
    $query_result = FALSE;
    if (FALSE == DEBUG_MODE) {
        $insertstr = "INSERT INTO CALL_BASE (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, CALL_DIRECTION,
        CALL_THEME_ID, SOURCE_AUTO_ID, SOURCE_MAN_ID, SOURCE_TYPE_ID, CALL_TYPE_ID, SERVICE_ID, STATUS_ID, LAST_CHANGE,
        OKTELL_IDCHAIN, OKTELL_SERVER_ID)
        VALUES (SEQ_CALL_BASE_ID.NEXTVAL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
        '{$anumber}', '{$bnumber}', '{$sc_agid}', '{$sc_call_id}', '{$sc_project_id}', '{$call_direction}'," .
        THEME_NOT . ",'{$source_auto_id}'," . SOURCE_NOT . "," . DEVICE_PHONE . "," . CALL_FIRST . "," . SERVICE_NOT . "," . STATUS_CLOSED . ",
        to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$oktell_idchain}', '{$oktell_srv_id}') returning ID into :max_call";
    } else {
        $insertstr = "INSERT INTO CALL_BASE_TEST (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, CALL_DIRECTION,
        CALL_THEME_ID, SOURCE_AUTO_ID, SOURCE_MAN_ID, SOURCE_TYPE_ID, CALL_TYPE_ID, SERVICE_ID, STATUS_ID, LAST_CHANGE,
        OKTELL_IDCHAIN, OKTELL_SERVER_ID)
        VALUES (SEQ_CALL_BASE_ID_TEST.NEXTVAL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
        '{$anumber}', '{$bnumber}', '{$sc_agid}', '{$sc_call_id}', '{$sc_project_id}', '{$call_direction}'," .
        THEME_NOT . ",'{$source_auto_id}'," . SOURCE_NOT . "," . DEVICE_PHONE . "," . CALL_FIRST . "," . SERVICE_NOT . "," . STATUS_CLOSED . ",
        to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$oktell_idchain}', '{$oktell_srv_id}') returning ID into :max_call";
    }
if (TRUE == DEBUG_MODE) echo "<br/><textarea>" . $insertstr . "</textarea><br/>";

    GetData::my_log($insertstr, FALSE);
    $query = OCIParse($c, $insertstr);
    OCIBindByName($query,":max_call",$max_call,16);
    $query_result = OCIExecute($query);
    if (!$query_result)
        GetData::my_log($insertstr, TRUE);

    /*$max_call = 1;
    if ($query_result) {
        if (FALSE == DEBUG_MODE)
            $sqlstr = "SELECT SEQ_CALL_BASE_ID.CURRVAL FROM CALL_BASE";
        else $sqlstr = "SELECT SEQ_CALL_BASE_ID_TEST.CURRVAL FROM CALL_BASE_TEST";
        $query = OCIParse($c, $sqlstr);
        if (OCIExecute($query)) {
            $objResult = OCI_Fetch_Row($query);
            $max_call = $objResult[0];
        }
    }*/
    oci_free_statement($query);
}
?>
<div>
    <form action="<?php echo $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];?>" method="POST">
    <h2 style="display: inline;">
        <?php
        echo '<label for="PurposeId">Тема звонка:&nbsp;</label>';
        echo '<select id="PurposeId" name="PurposeId" onchange="PurposeSelected();" title="Темы звонка" style="background-color:'.needs.'">';
            echo "<option value='".THEME_NOT."'>Выберите тему</option>";
            if (GetData::GetThemes("DELETED IS NULL") > 0) {
                foreach(GetData::$array_theme as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
        echo '</select>';

		//кнопка перезвонка по АОН
        if (isset($aon_for_backcall) and $aon_for_backcall<>'') {
            echo " | <input disabled id='callto_button' type='button' onclick='callto(".$aon_for_backcall.",".$max_call.")' value='     Перезвонить оп АОН' title='".$aon_for_backcall."' 
style='height: 25px; background-image: url(\"".PATH."/images/call.png\"); background-repeat: no-repeat; display:none;' />";
            echo "<input disabled id='endcall_button' type='button' onclick='endcall()' value='    Завершить звонок'  
style='height: 25px; background-image: url(\"".PATH."/images/call_stop.png\"); background-repeat: no-repeat; display:none;' />";
			echo "<script>var oktell_phone_prefix='".$out_prefix."';</script>";
			include('med_call_makecall.js');
        }			
		
        echo "<iframe name=ifr2 style='display:none; width: 90%'></iframe>";
        echo '<br/><label id="ServiceT" for="ServiceId">Услуги:&nbsp;</label>';
        echo "<select id='ServiceId' name='ServiceId' style='background-color:".needs."' onchange='ifr2.location=\"".PATH."/med_call.php?getoper=\"+this.value'>";
        echo "<option value='".SERVICE_NOT."'>Выберите услугу</option>";
        if (GetData::GetServices(FALSE,FALSE,NULL,FALSE) > 0) {
            foreach(GetData::$array_services as $key => $value) {
                if (TRUE == ENCODE_UTF) {
                    $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                }
                /*if (SERVICE_STOM == $value['ID'])
                    printf("<option value='%s' selected=\"selected\">%s</option>", $value['ID'], $value['NAME']);
                else*/ echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        echo "</select>";
        ?>
        <!--label id="ServiceStomT" for="ServiceStom">&nbsp;&nbsp;</label>
        <select id="ServiceStom" name="ServiceStom" title="Стоматология" style="visibility: hidden; background-color:< ?=needs?>">
            <option value='< ?=STOM_NOT?>'>Можете выбрать уточнение</option>
            <option value='< ?=STOM_CHILD?>'>Детская стоматология</option>
            <option value='< ?=STOM_NEYLON?>'>Нейлоновый протез</option>
            <option value='< ?=STOM_HOUR?>'>Протезирование за час</option>
            <option value='< ?=STOM_OTHER?>'>Другое</option>
        </select>
        <script type = "text/javascript">
            var select = document.getElementById("ServiceStom");
            select.onchange = function()
            {
                /*if (< ?=STOM_NOT?> == this.value)
                    this.style.backgroundColor = '< ?=needs?>';
                else this.style.backgroundColor = 'white';*/

                var elem = document.getElementById('Reservoir');
                //if (< ?=STOM_NOT?> == this.value) elem = this;

                if (document.getElementById("PurposeId").value >= < ?=THEME_INFO?> ||
                    < ?=THEME_MED?> == document.getElementById("PurposeId").value &&
                    < ?=SERVICE_NOT?> != document.getElementById("ServiceId").value &&
                    /*(< ?=SERVICE_STOM?> != document.getElementById("ServiceId").value ||
                    < ?=STOM_NOT?> != document.getElementById("ServiceStom").value) &&*/
                    < ?=SOURCE_NOT?> != document.getElementById("Reservoir").value &&
                    < ?=RESULT_NOT?> != document.getElementById("ResultId").value &&
                    (< ?=RESULT_WAIT?> == document.getElementById("ResultId").value ||
                     document.getElementById("OperatorsId").value > 0)
                    document.getElementById('save_but').style.visibility = 'visible';
                else document.getElementById('save_but').style.visibility = 'hidden';
                elem.focus();
            }
        </script-->
    </h2>
    <!--div id="CallType"><h2>
            <label for="voice">Тип звонка:&nbsp;</label>
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="FirstCall" value=< ?=CALL_FIRST?> checked title="Первичный"/> Первичный
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="SecondCall" value=< ?=CALL_SECOND?> title="Повторный" /> Повторный
        </h2></div-->

    <div id="NotTarget">
    <h2>
        <!--label for="Istochnik_auto">Источник рекламы (автоопределение):</label>
        <input type="text" name="Istochnik_auto" style="width: 290px;" placeholder="< ?php echo $source_auto_name ?>" disabled/>
        <br/-->

        <iframe name=ifr1 style='display:none; width: 90%'></iframe>
        <!--?php
        echo '<label for="Reservoir">Источник рекламы:&nbsp;</label>';
        if (GetData::GetIstochnik(FALSE,FALSE,"(instr(in_dep, '-1') != 0 or instr(in_dep, '1') != 0)", FALSE) > 0) {
            printf("<select id='Reservoir' name='Reservoir' style='background-color:".needs."' onchange='ifr1.location=\"".PATH."/med_call.php?getdet=\"+this.value'>");
            printf("<option value='".SOURCE_NOT."'>Выберите источник рекламы</option>");
            foreach(GetData::$array_istochnik as $key => $value) {
                if (TRUE == ENCODE_UTF) {
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    $value['DETAIL'] = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                }
                printf("<option value=\"%s\">%s</option>", $value['ID'], $value['NAME']);
            }
            echo "</select>";
        }
        echo "<script>$('#Reservoir').val('".SOURCE_NOT."').change();</script>";
        ?-->
        <label style="position: relative; float: left;" id="AllInOneIst" for="SelectIst">&nbsp;</label>
        <div id="SelectIst" style="margin-left: 15px;">&nbsp;</div>
        <label style="position: relative; float: left;" id="AllInOne" for="AllSelect">&nbsp;</label>
        <div id="AllSelect" style="margin-left: 15px;">&nbsp;</div>
    </h2>
    </div>

    <div id="all_other">
<!--    <h2>Цель звонка: <input type="textarea" cols="10" rows="5" name="age" style="width: 400px; height: 50px;"/></h2> -->
    <h2><label for="comment">Комментарий:</label>
        <textarea name="comment" id="comment" title="Комментарий" placeholder="Введите комментарий" rows=30 cols=68 style="vertical-align: text-top; height: 45px"></textarea>
        <br/>
        <label for="surname">ФИО:&nbsp;</label><input type="text" name="surname" style="width: 90%" placeholder="Фамилия Имя Отчество"/>
        <!--label for="name">Имя:&nbsp;</label><input type="text" name="name" style="width: 7em" placeholder="Имя"/>
        <label for="patronymic">Отчество:&nbsp;</label><input type="text" name="patronymic" placeholder="Отчество"/>
        <label for="ages">Возраст:&nbsp;</label>
        <input type="number" min="0" max="200" name="ages" style="width: 4em;"/-->
        <br/>
        <label for="phone_mob">Контактный телефон:&nbsp;</label>
		<!--(sva 23/04/2018)--/>
		<!--<input type="text" id="phone_mob" name="phone_mob" style="width: 10em;" placeholder="Введите номер"/>/-->
		<input type="text" name="phone_mob" title="Контактный телефон" style="width: 10em;"/>
		
        <!--label for="phone_home">&nbsp;Домашний:&nbsp;</label>
        <input type="text" id="phone_home" name="phone_home" style="width: 10em;" placeholder="Телефон домашний"/-->
        <!--br/>Междугородний звонок: &nbsp;<input type=checkbox id='interstate' name='interstate' title='межгород'-->
    </h2>

    <!--h2>E-mail: <input type="email" name="e_mail" placeholder="e-mail" style="width: 22em;"/></h2-->
    <h2 style="display: inline-block; margin-top: 0"> Результат:
        <select id="ResultId" name="ResultId" onchange="ResultSelected();" title="Результат" style="background-color:<?=needs?>">
            <option value="<?=RESULT_NOT?>">Выберите результат</option>
            <option value="<?=RESULT_WAIT?>">Ждет звонка</option>
            <option value="<?=RESULT_KC?>">Перевели в КЦ</option>
            <!--option value="< ?=RESULT_CLINIC?>">Перевели в Клинику</option-->
            <option value="<?=RESULT_AON?>">Не оставил номер</option>
        </select>

        <!--?php
        $trans_arr = date_parse(date("Y-m-d HH:MM"));
        $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'];//.'-'.$trans_arr['hour'];
        $num_str = GetData::GetTransferNum($const_str);?-->
        <div id="KC_Number" style="position: absolute; visibility: hidden; display: inline-block; width: 50%">
            <label style="position: relative; float: left;" id="assign_clT" for="assign_cl">&nbsp;Услуга не выбрана!</label>
            <div id="assign_cl">&nbsp;</div>
            <label for='call_center'>&nbsp;Пин оператора:&nbsp;</label>
            <input type='number' size='4' name='call_center' id='call_center' style='width: 5em;'/>
            <!--input type="number" minlength="4" maxlength="4" pattern="[0-9]{4}" min="1000" max="9999" size="4" name="call_center" id="call_center" placeholder="1000" style="width: 5em;"-->
            <!--span class="error">* < ?php echo $nameErr;?></span-->
            <!--p style="color: magenta; margin: 0;" id="valid_str"></p-->
            <!--script>document.getElementById('call_center').validity.patternMismatch;</script-->
<!--          &nbsp;Номер заявки:&nbsp;<span style="color: black; font-size: smaller;">< ?=$const_str?>-</span>
            <span style="color: #FF733C; font-size: larger; border-bottom: dashed">< ?=$num_str?></span-->
        </div>
        <div id="write_cl" style="visibility: hidden">&nbsp; Клиника:&nbsp;
            <select id='call_clinic' name='call_clinic'>
            <?php
            if (GetData::GetHospitals(NULL) > 0) {
                foreach (GetData::$array_hospitals as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
            ?>
            </select>
        </div>
    </h2>
    </div>
    <div>
        <!--button name="save_but" id="save_but" class="send_button" onclick="CheckFields()" style="visibility: hidden">Сохранить</button-->
        <input type="submit" name="save_but" id="save_but" value="Сохранить" class="send_button" onClick="CheckFields()" style="visibility: hidden"/>
        <input type="hidden" name="max_call" value="<?php echo $max_call; ?>"/>
        <input type="hidden" name="date_call" value="<?php echo $date_call; ?>"/>
        <input type="hidden" name="sc_agid" value="<?php echo $sc_agid; ?>"/>
        <input type="hidden" name="anumber" value="<?php echo $anumber; ?>"/>
        <input type="hidden" name="bnumber" value="<?php echo $bnumber; ?>"/>
        <input type="hidden" name="sc_call_id" value="<?php echo $sc_call_id; ?>"/>
        <input type="hidden" name="sc_project_id" value="<?php echo $sc_project_id; ?>"/>
        <input type="hidden" name="call_direction" value="<?php echo $call_direction; ?>"/>
        <input type="hidden" name="source_auto_name" value="<?php echo $source_auto_name; ?>"/>
    </div>

    <script type="text/javascript">
        jQuery(function($){
            $("#phone_mob").mask("8(999) 999-9999");
        });
    </script>
    <!--script type="text/javascript">
        jQuery(function($){
            $("#phone_new").mask("8(999) 999-9999");
        });
    </script-->
</form>
</div>

</body>
</html>