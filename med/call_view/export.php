<?php
if (EXPORT_BILLING == $ReportId)
    require_once("med/oktell_conn_string.php");

if (EXPORT_CALL == $ReportId || EXPORT_CALL_SEC == $ReportId) {
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')) { //тестируем для Mac OS
        //header("Content-type: application/csv; charset=MacRomanEncoding");
        header("Content-type: application/xls; charset=utf-8");
        //header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        $bMac = TRUE;
    }
    else {
        header("Content-type: application/xls; charset=windows-1251");
        //header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        $bMac = FALSE;
    }
    //header("Content-type: application/xls; charset=windows-1251");
    header("Content-Disposition: attachment; filename=" . $filename.".xls");

    $q_text1 = "SELECT cb.ID, cb.secret, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID,
them.NAME as THEME, cb.SERVICE_ID, serv.NAME as SRVNAME, serv_det.NAME as SERV_DET, cb.SOURCE_TYPE_ID, sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, 
case
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_PROMO . " then 'У промоутера'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_OTHER . " then '---'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_AMNESY . " then 'Не помнит'
    when cb.SOURCE_MAN_DET_ID>=500 then srd.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_SERT . " then hosp_det.CITY || '-' || hosp_det.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_FLAER . " or cb.SOURCE_MAN_ID=" . SOURCE_CATALOG . " or
    cb.SOURCE_MAN_ID=" . SOURCE_FLAER_SUB . " or cb.SOURCE_MAN_ID=" . SOURCE_FLAER_CAR . " or
    cb.SOURCE_MAN_ID=" . SOURCE_LIFT . " or cb.SOURCE_MAN_ID=" . SOURCE_STOP . " then 'м.' || metro.NAME
    else to_char(cb.SOURCE_MAN_DET_ID)
end SOURCE_MAN_DET,
sra_det.NAME as SRADETNAME,
case
    when cb.RESULT_ID=" . RESULT_NOT . " then '---'
    when cb.RESULT_ID=" . RESULT_KC . " then 'в КЦ'
    when cb.RESULT_ID=" . RESULT_CLINIC . " then 'в Клинику'
    when cb.RESULT_ID=" . RESULT_WAIT . " then 'Ждет звонка'
    when cb.RESULT_ID=" . RESULT_AON. " then 'Не оставил номер'
end RESULT,
case
    when cb.RESULT_ID=" . RESULT_KC . " then 'Номер: ' || cb.RESULT_DET
    when cb.RESULT_ID=" . RESULT_CLINIC . " then hosp.CITY || '-' || hosp.NAME
    else to_char(cb.RESULT_DET)
end RESULT_DET,
cb.CLIENT_NAME, cb.PHONE_MOB, cb.COMMENTS, stat.NAME as STATUS, stat_det.NAME as STATUS_DET, 
usr.FIO as FIO, cb.CALL_DOUBLE, cb.INTERSTATE, cb.OKTELL_IDCHAIN, 
to_char(cb.ENTRY_DATE_1C,'dd.mm.yyyy hh24:mi') ENTRY_DATE_1C, cb.outer_order_id, to_char(cb.CREATE_DATE,'dd.mm.yyyy hh24:mi:ss') CREATE_DATE,
lea.PARS_PHONE_STRING,to_char(lea.PARS_RECALL_DATE_DATE,'DD.MM.YYYY HH24:MI:SS') PARS_RECALL_DATE_DATE,
lea.PARS_RECALL_DATE_STRING,decode(lea.RECALL_DATE_ACTIVE,'on','on','off') RECALL_DATE_ACTIVE,";
    if (EXPORT_CALL_SEC == $ReportId)
        $q_text1 .= "cb.SECOND_STATUS_ID as STATUS_ID, to_char(cb.SECOND_LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.DATE_SECOND_CLOSE,'dd.mm.yyyy') DATE_CLOSE";
    else $q_text1 .= "cb.STATUS_ID, to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.DATE_CLOSE,'dd.mm.yyyy') DATE_CLOSE";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN CALL_THEME them ON them.ID = cb.CALL_THEME_ID
    LEFT JOIN SERVICES serv ON serv.ID = cb.SERVICE_ID
    LEFT JOIN SERVICE_DET serv_det ON serv_det.ID = cb.SERVICE_DET_ID 
    LEFT JOIN SOURCE_AUTO sr_a ON sr_a.ID = cb.SOURCE_AUTO_ID
    LEFT JOIN SOURCE_MAN sr_man ON sr_man.ID = cb.SOURCE_MAN_ID
    LEFT JOIN SUBWAYS metro ON metro.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN HOSPITALS hosp_det ON hosp_det.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_MAN_DETAIL srd ON srd.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_AUTO_DETAIL sra_det ON sra_det.ID = cb.SOURCE_MAN_ID_NEW
    LEFT JOIN HOSPITALS hosp ON hosp.ID = cb.RESULT_DET 
	LEFT JOIN MAIL_LEADCOLLECTOR lea ON lea.call_base_id = cb.id ";
    if (EXPORT_CALL_SEC == $ReportId)
        $q_text3 .= "LEFT JOIN MED_STATUS stat ON stat.ID = cb.SECOND_STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.SECOND_STATUS_DET_ID
    LEFT JOIN USERS usr ON usr.ID = cb.SECOND_FIO_ID";
    else $q_text3 .= "LEFT JOIN MED_STATUS stat ON stat.ID = cb.STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.STATUS_DET_ID
    LEFT JOIN USERS usr ON usr.ID = cb.FIO_ID";
/* sr_man_new.NAME as SRMNAME_NEW,
case
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_PROMO . " then 'У промоутера'
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_OTHER . " then '---'
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_AMNESY . " then 'Не помнит'
    when cb.SOURCE_MAN_DET_ID_NEW>=500 then srd_new.NAME
    when cb.SOURCE_MAN_ID_NEW=" . SOURCE_SERT . " then hosp_det_new.CITY || '-' || hosp_det_new.NAME
    when cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_CATALOG . " or
    cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER_SUB . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER_CAR . " or
    cb.SOURCE_MAN_ID_NEW=" . SOURCE_LIFT . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_STOP . " then 'м.' || metro_new.NAME
    else to_char(cb.SOURCE_MAN_DET_ID_NEW)
end SOURCE_MAN_DET_NEW, */
    //LEFT JOIN SOURCE_MAN sr_man_new ON cb.SOURCE_MAN_ID_NEW = sr_man_new.ID
    //LEFT JOIN SUBWAYS metro_new ON cb.SOURCE_MAN_DET_ID_NEW = metro_new.ID
    //LEFT JOIN HOSPITALS hosp_det_new ON cb.SOURCE_MAN_DET_ID_NEW = hosp_det_new.ID
    //LEFT JOIN SOURCE_MAN_DETAIL srd_new ON cb.SOURCE_MAN_DET_ID_NEW = srd_new.ID

// $q_text4 и $q_filt_interval формируются в export_xlsx.php
    if (isset($_POST['all_type'])) // остальные фильтры формируются в начале файла
        $q_text4 .= " or (call_theme_id > " . THEME_MED .
"  and cb.source_auto_id in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id)
     from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id 
     and uda.deleted is NULL and uda.user_id=".$_SESSION['login_id_med'].") )";

    if (isset($S_Auto) && !in_array(SOURCE_ALL, $S_Auto)) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in (" . implode(',', $S_Auto) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in ( 
        select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }
    $q_text4 .= ")"; // !!! закрывающая скобка

    $q_text5 = " ORDER BY cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_interval . $q_text5;
//echo "<br><textarea>".$q_text."</textarea><br>";

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);

    $head_arr = array(0=>"№ ".chr(10)."заявки", 1=>"Дата ".chr(10)."звонка", 2=>"ID звонка",
        3=>"Проект", 4=>"ANumber", 5=>"Оператор", 6=>"Тема", 7=>"Услуга",
        8=>"Тип ".chr(10)."источника", 9=>"BNumber", 10=>"Источник (Авто)",
        11=>"Источник".chr(10)." (вход.)", 12=>"Детализация".chr(10)." источника (вход.)",
        13=>"Источник".chr(10)." (исх.)", 14=>"Результат".chr(10)." входящего", 15=>"Детализация".chr(10)." результата",
        16=>"ФИО", 17=>"Контактный".chr(10)." телефон", 18=>"Статус", 19=>"Уточнение".chr(10)." ошибки",
        20=>"Комментарий".chr(10)." вход", 21=>"Комментарий".chr(10)." последний",
        22=>"Назначено", 23=>"Клиника", 24=>"Записан", 25=>"Контакт", 26=>"Дата".chr(10)." записи", 27=>"Итог",
        28=>"Время посл.".chr(10)." события", 29=>"Закрыто", 30=>"ID Цепочки", 31=>"Запись".chr(10)." звонка");
    if (in_array($_SESSION['login_id_med'],OUTER_ORDER)) {
        $head_arr[32]='Внешний идентификатор';
    }
    if (in_array($_SESSION['login_id_med'],CALL_STATISTIC)) {
        $head_arr[33]='Дата поступления заявки';
		$head_arr[34]='Дата первой попытки';
		$head_arr[35]='Дата успешной попытки';
		$head_arr[36]='Дата последней попытки';
		$head_arr[37]='Кол-во попыток';
		$head_arr[38]='Телефон (строка)';
		$head_arr[39]='Желаемое время (строка)';
		$head_arr[40]='Желаемое время (дата)';
		$head_arr[41]='Желаемое время (on/off)';		
    }		
    /*if (EXPORT_CALL_SEC == $ReportId) { // убираем столбцы с записью разговора
        array_pop($head_arr);
        array_pop($head_arr);
    }*/
    $remove_column = array(0,2,3,4,5,9,11,12,13,14,15,22,23,24,25,26,27,30,31);
    $gus_column = array(5,12,13,15,22,23,24,25,26,27,28,29,30,31); // убираем у Гусарова
    $contact_column = array(4,17,25); // убираем номера телефонов, кому нет к ним доступа.

    echo '<table>';
    echo '<tr>';
    if ($rep_start_date != $rep_end_date)
        echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8('Экспорт заявок с '.$rep_start_date." по ".$rep_end_date) . '</td>';
    else echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8('Экспорт заявок за '.$rep_start_date) . '</td>';
    echo '</tr>';
    echo '<tr>';
    foreach($head_arr as $key=>$val) {
        if ((IT_PLANET != $_SESSION['login_id_med'] || !in_array($key, $remove_column)) &&
            ($data_acc_arr && in_array(CAN_HEAR, $data_acc_arr) || (30 != $key && 31 != $key))) { // ограничение для прослушивания записей
            if (in_array($_SESSION['login_id_med'],EXPORT_CUT) && in_array($key, $gus_column) ||
                !in_array(CAN_PHONE, $data_acc_arr) && in_array($key, $contact_column)||
                (109 == $_SESSION['login_id_med'] || 113 == $_SESSION['login_id_med']) && 27 == $key) { // Убираем у Кокоса
                if (in_array($_SESSION['login_id_med'],SHOW_ITOG) && 27 == $key) { // Отдельно показываем итог
                    echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8($val) . '</td>';
                } else {
                    continue;
                }
            }
            else {
                echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8($val) . '</td>';
            }
        }
    }
    echo '</tr>';

    $q_hist = OCIParse($c, "SELECT STATUS_ID, COMMENTS FROM CALL_BASE_HIST WHERE BASE_ID=:id and STATUS_ID=:stat_id ORDER BY ID desc ");	
    $q_clinic = OCIParse($c, "SELECT (hosp.CITY || '-' || hosp.NAME) AS HOSP_NAME, 
CLIENT_NAME, CLIENT_PHONE, CLIENT_STATUS, to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi:ss') CLIENT_DATE FROM CALL_BASE_CLINIC 
LEFT JOIN HOSPITALS hosp ON HOSPITAL_ID = hosp.ID WHERE BASE_ID=:id");
    $q_write = OCIParse($c,"SELECT to_char(DATE_WRITE,'dd.mm.yyyy hh24:mi') DATE_WRITE FROM WRITE_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");
    $q_visit = OCIParse($c,"SELECT to_char(DATE_VISIT,'dd.mm.yyyy hh24:mi') DATE_VISIT FROM VISIT_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");		
	$q_callstat = OCIParse($c,"select 
	to_char(min(start_date),'DD.MM.YYYY HH24:MI:SS') first_try, 
	to_char(min(case when okt_reasonfailed_code='0' and okt_duration_sec>5 then start_date else null end),'DD.MM.YYYY HH24:MI:SS') first_good_try, 
	to_char(max(start_date),'DD.MM.YYYY HH24:MI:SS') last_try, 
	count(*) count_tryes
	from OKTELL_CALL_HIST
	where base_id=:base_id 
	and (okt_reasonfailed_code<>'25' or (okt_reasonfailed_code='25' and okt_duration_sec>1)) 
	--раскомментировать это, если нужна статистика только до первого успешного звонка
	/*
	and start_date<=
	(
	select min(case when okt_reasonfailed_code='0' and okt_duration_sec>5 then start_date else to_date('2030','YYYY') end)
	from OKTELL_CALL_HIST
	where base_id=:base_id)
	*/
	");	
	
    while (OCIFetch($q)) {
        $base_id = OCIResult($q, "ID");
        $status_id = OCIResult($q, "STATUS_ID");
        $service_id = OCIResult($q, "SERVICE_ID");
        $client_name = OCIResult($q, "CLIENT_NAME");
        //$surname = substr($client_name, 0, strpos($client_name, '/'));
        //$name = substr($client_name, strpos($client_name, '/') + 1, strrpos($client_name, '/') - strpos($client_name, '/') - 1);
        //$patronymic = substr($client_name, strripos($client_name, '/') + 1, strlen($client_name));

        OCIBindByName($q_hist, ":id", $base_id);
        OCIBindByName($q_hist, ":stat_id", $status_id);
        OCIExecute($q_hist, OCI_DEFAULT);
        //$last_result = OCI_Fetch_Row($q_hist);
        $comment_cut = '';
        while (OCIFetch($q_hist)) {
            $last_comment = trim(OCIResult($q_hist, "COMMENTS"));
            //if (STATUS_CALL_BACK == $status_id || STATUS_WORK == $status_id)
			if (STATUS_WORK == $status_id)
                $comment_cut = substr($last_comment, strpos($last_comment,')')+1);
            else $comment_cut = $last_comment;
            break;
        }

        echo '<tr>';
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            echo '<td style="vertical-align: middle; text-align: center;">' . u8($base_id) . '</td>';
        }
		echo '<td style="vertical-align: middle; text-align: left;">' . u8( OCIResult($q, "DATE_CALL") ) . '</td>';
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "SC_CALL_ID")) . '</td>
                <td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "SC_PROJECT_ID")) . '</td>';
            if (in_array(CAN_PHONE, $data_acc_arr)) {
                echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "ANUMBER")) . '</td>';
            }
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "SC_AGID")) . '</td>';
            }
        }
        echo '<td style="vertical-align: middle; text-align: center;">' . u8( OCIResult($q, "THEME") ) . '</td>';
        if (SERVICE_GINE != $service_id) {
            echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRVNAME")) . ' / ' . u8(OCIResult($q, "SERV_DET")) . '</td>';
        }
        else {
            echo '<td style="vertical-align: middle; text-align: left;">' . u8( OCIResult($q,"SRVNAME") ) . '</td>';
        }
        echo '<td style="vertical-align: middle; text-align: center;">' . u8( DEVICES[OCIResult($q, "SOURCE_TYPE_ID")] ) . '</td>';
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "BNUMBER")) . '</td>';
        }
		echo '<td style="vertical-align: middle; text-align: left;">' . u8( OCIResult($q, "SRANAME") ) . '</td>';
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRMNAME")) . '</td>';
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SOURCE_MAN_DET")) . '</td>
		            <td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRADETNAME")) . '</td>';
            }
		    echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "RESULT")) . '</td>';
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "RESULT_DET")) . '</td>';
            }
        }
		echo '<td style="vertical-align: middle; text-align: center;">' . u8( OCIResult($q, "CLIENT_NAME") ) . '</td>';
        if (in_array(CAN_PHONE, $data_acc_arr)) {
            echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "PHONE_MOB")) . '</td>';
        }
        if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE") || 1 == OCIResult($q, "INTERSTATE")) {
            $tmp_str = "";
            if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE"))
                $tmp_str .= " (Дубль)";
            if (1 == OCIResult($q, "INTERSTATE"))
                $tmp_str .= " (Межгород)";
            if (USER_VIEW == $_SESSION['user_role'])
                echo '<td style="vertical-align: middle; text-align: center;">' . u8( $tmp_str ) . '</td>';
            else echo '<td style="vertical-align: middle; text-align: center;">' . u8( OCIResult($q, "STATUS").$tmp_str ) . '</td>';
        }
        else {
            echo '<td style="vertical-align: middle; text-align: center;">' . u8( OCIResult($q, "STATUS") ) . '</td>';
        }
        echo '<td style="vertical-align: middle; text-align: center;">' . u8( OCIResult($q, "STATUS_DET") ) . '</td>
		<td style="vertical-align: middle; text-align: left;">' . trim(u8( OCIResult($q, "COMMENTS")) ) . '</td>
		<td style="vertical-align: middle; text-align: left;">' . trim(u8( $comment_cut )) . '</td>';

        OCIBindByName($q_clinic, ":id", $base_id);
        OCIExecute($q_clinic, OCI_DEFAULT);
        OCIFetch($q_clinic);
        $hospital = OCIResult($q_clinic, "HOSP_NAME");
        $clinic_client_name = OCIResult($q_clinic, "CLIENT_NAME");
        $clinic_client_phone = OCIResult($q_clinic, "CLIENT_PHONE");
        //$clinic_client_status = OCIResult($q_clinic, "CLIENT_STATUS");
        $clinic_client_date = OCIResult($q_clinic, "CLIENT_DATE");
        $clinic_surname = substr($clinic_client_name, 0, strpos($clinic_client_name, '/'));
        $clinic_name = substr($clinic_client_name, strpos($clinic_client_name, '/') + 1, strrpos($clinic_client_name, '/') - strpos($clinic_client_name, '/') - 1);
        $clinic_patronymic = substr($clinic_client_name, strripos($clinic_client_name, '/') + 1, strlen($clinic_client_name));
        //$nrowhosp = GetData::GetHospitals("hosp.ID = ". $hospital);

        //$date_write = OCIResult($q, "ENTRY_DATE_1C"); теперь есть таблица с историей перезаписи...
        OCIBindByName($q_write, ":id", $base_id);
        $date_write = '';
        if (OCIExecute($q_write, OCI_DEFAULT) && OCIFetch($q_write))
            $date_write = OCIResult($q_write, "DATE_WRITE");
        if ('' == $date_write) $date_write = $clinic_client_date;

        OCIBindByName($q_visit, ":id", $base_id);
        $date_visit = '';
        if (OCIExecute($q_visit, OCI_DEFAULT) && OCIFetch($q_visit))
            $date_visit = OCIResult($q_visit, "DATE_VISIT");

        $date = date("d.m.Y");
        if ($date_visit != '')
            $visit = 'Пришел';
        else {
            if ($date_write != '') {
                if (strtotime($date_write) >= strtotime($date))
                    $visit = 'Ждем';
                elseif (strtotime($date_write) < strtotime($date))
                    $visit = 'Не пришел';
            }
            else $visit = '';
        }

        if (IT_PLANET != $_SESSION['login_id_med'] && !in_array($_SESSION['login_id_med'],EXPORT_CUT)) { // ограничение для IT Planet и Гусарова/Savitsky
            echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "FIO")) . '</td>
                <td style="vertical-align: middle; text-align: left;">' . u8($hospital) . '</td>
                <td style="vertical-align: middle; text-align: center;">' . u8($clinic_surname . ' ' . $clinic_name . ' ' . $clinic_patronymic) . '</td>';
            if (in_array(CAN_PHONE, $data_acc_arr)) {
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($clinic_client_phone) . '</td>';
            }
            echo '<td style="vertical-align: middle; text-align: center;">' . u8($date_write) . '</td>';
            if (109 != $_SESSION['login_id_med'] && 113 != $_SESSION['login_id_med']) { // Убираем у Кокоса
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($visit) . '</td>';
            }
        }
        else {
            if (in_array($_SESSION['login_id_med'],SHOW_ITOG)) { // Отдельно показываем итог
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($visit) . '</td>';
            }
        }

        if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
			//время посл.события
			//время закрытия
            echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "LAST_CHANGE")) . '</td>
		        <td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "DATE_CLOSE")) . '</td>';
        }
        if (EXPORT_CALL_SEC != $ReportId && $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) { // ограничение для прослушивания записей
            //ID цепочки
			echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "OKTELL_IDCHAIN")). '</td>';
            //if (DEVICE_PHONE == OCIResult($q, "SOURCE_TYPE_ID")) {
			if(OCIResult($q, "SECRET")<>'') {	
                //echo '<td style="vertical-align: middle; text-align: center;">
//<a href="'.$oktell_records_url.'?idchain='.OCIResult($q,"OKTELL_IDCHAIN").'">'.u8("Ссылка").'</a></td>';
                //Запись звонка
				echo '<td style="vertical-align: middle; text-align: center;"> 
<a href="'.$oktell_records_url.'?baseid=' . OCIResult($q, "ID").'&secret='.OCIResult($q, "SECRET").'">'.u8("Ссылка").'</a></td>';
            }
        }
        if (in_array($_SESSION['login_id_med'],OUTER_ORDER)) {
            echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "OUTER_ORDER_ID")) . '</td>';
        }
        if (in_array($_SESSION['login_id_med'],CALL_STATISTIC)) {
            
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "CREATE_DATE")) . '</td>'; //33
			
			OCIBindByName($q_callstat,":base_id",$base_id);
			OCIExecute($q_callstat, OCI_DEFAULT);
			OCIFetch($q_callstat);
			
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q_callstat, "FIRST_TRY")) . '</td>'; //34
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q_callstat, "FIRST_GOOD_TRY")) . '</td>'; //35
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q_callstat, "LAST_TRY")) . '</td>'; //36
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q_callstat, "COUNT_TRYES")) . '</td>'; //37
			
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "PARS_PHONE_STRING")) . '</td>'; //38
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "PARS_RECALL_DATE_STRING")) . '</td>'; //39
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "PARS_RECALL_DATE_DATE")) . '</td>'; //40
			echo '<td style="vertical-align: middle; text-align: center;">'. u8(OCIResult($q, "RECALL_DATE_ACTIVE")) . '</td>'; //41
        }		
		echo '</tr>';
	}
	echo '</table>';
}
elseif (EXPORT_CALL_SHORT == $ReportId) { // Экспорт заявок укороченный
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')) { //тестируем для Mac OS
        //header("Content-type: application/csv; charset=MacRomanEncoding");
        header("Content-type: application/xls; charset=utf-8");
        //header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        $bMac = TRUE;
    }
    else {
        header("Content-type: application/xls; charset=windows-1251");
        //header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        $bMac = FALSE;
    }
    //header("Content-type: application/xls; charset=windows-1251");
    header("Content-Disposition: attachment; filename=" . $filename.".xls");

    $q_text1 = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.BNUMBER,
them.NAME as THEME, cb.SERVICE_ID, serv.NAME as SRVNAME, serv_det.NAME as SERV_DET, cb.SOURCE_TYPE_ID, 
sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, 
case
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_PROMO . " then 'У промоутера'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_OTHER . " then '---'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_AMNESY . " then 'Не помнит'
    when cb.SOURCE_MAN_DET_ID>=500 then srd.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_SERT . " then hosp_det.CITY || '-' || hosp_det.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_FLAER . " or cb.SOURCE_MAN_ID=" . SOURCE_CATALOG . " or
    cb.SOURCE_MAN_ID=" . SOURCE_FLAER_SUB . " or cb.SOURCE_MAN_ID=" . SOURCE_FLAER_CAR . " or
    cb.SOURCE_MAN_ID=" . SOURCE_LIFT . " or cb.SOURCE_MAN_ID=" . SOURCE_STOP . " then 'м.' || metro.NAME
    else to_char(cb.SOURCE_MAN_DET_ID)
end SOURCE_MAN_DET,
sra_det.NAME as SRADETNAME, cb.CALL_DOUBLE, cb.INTERSTATE, 
stat.NAME as STATUS, stat_det.NAME as STATUS_DET, cb.STATUS_ID";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN CALL_THEME them ON them.ID = cb.CALL_THEME_ID
    LEFT JOIN SERVICES serv ON serv.ID = cb.SERVICE_ID
    LEFT JOIN SERVICE_DET serv_det ON serv_det.ID = cb.SERVICE_DET_ID 
    LEFT JOIN SOURCE_AUTO sr_a ON sr_a.ID = cb.SOURCE_AUTO_ID
    LEFT JOIN SOURCE_MAN sr_man ON sr_man.ID = cb.SOURCE_MAN_ID
    LEFT JOIN SUBWAYS metro ON metro.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN HOSPITALS hosp_det ON hosp_det.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_MAN_DETAIL srd ON srd.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_AUTO_DETAIL sra_det ON sra_det.ID = cb.SOURCE_MAN_ID_NEW
    LEFT JOIN MED_STATUS stat ON stat.ID = cb.STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.STATUS_DET_ID";

    if (isset($all_type)) // остальные фильтры формируются в начале файла
        $q_text4 .= " or (call_theme_id > " . THEME_MED .
            "  and cb.source_auto_id in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id)
     from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id 
     and uda.deleted is NULL and uda.user_id=".$_SESSION['login_id_med'].") )";

    if (isset($S_Auto) && !in_array(SOURCE_ALL, $S_Auto)) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in (" . implode(',', $S_Auto) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in ( 
        select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }
    $q_text4 .= ")"; // !!! закрывающая скобка

    $q_text5 = " ORDER BY cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_interval . $q_text5;
//echo "<br><textarea>".$q_text."</textarea><br>";

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);

    $head_arr = array(0=>"№ ".chr(10)."заявки", 1=>"Дата ".chr(10)."заявки", 2=>"Тема", 3=>"Услуга",
        4=>"Тип ".chr(10)."источника", 5=>"BNumber", 6=>"Источник (Авто)",
        7=>"Источник".chr(10)." (вход.)", 8=>"Детализация".chr(10)." источника (вход.)",
        9=>"Источник".chr(10)." (исх.)", 10=>"Статус", 11=>"Уточнение".chr(10)." ошибки",
        12=>"Дата".chr(10)." записи", 13=>"Итог");

    echo '<table>';
    echo '<tr>';
    if ($rep_start_date != $rep_end_date)
        echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8('Сокращенный экспорт заявок с '.$rep_start_date." по ".$rep_end_date) . '</td>';
    else echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8('Сокращенный экспорт заявок за '.$rep_start_date) . '</td>';
    echo '</tr>';
    echo '<tr>';
    // сначала вторая строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach($head_arr as $key=>$val) {
        echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . u8($val) . '</td>';
    }
    echo '</tr>';

    $rnum=2;
    while (OCIFetch($q)) {
        $rnum++; //номер строки
        $base_id = OCIResult($q, "ID");
        $status_id = OCIResult($q, "STATUS_ID");
        $service_id = OCIResult($q, "SERVICE_ID");

        echo '<tr>';
        echo '<td style="vertical-align: middle; text-align: center;">' . u8($base_id) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "DATE_CALL")) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "THEME")) . '</td>';

        if (SERVICE_GINE != $service_id) {
            echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRVNAME") . " / " . OCIResult($q, "SERV_DET")) . '</td>';
        } else {
            echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRVNAME")) . '</td>';
        }
        echo '<td style="vertical-align: middle; text-align: center;">' . u8(DEVICES[OCIResult($q, "SOURCE_TYPE_ID")]) . '</td>';
        echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "BNUMBER")) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRANAME")) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRMNAME")) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SOURCE_MAN_DET")) . '</td>';
        echo '<td style="vertical-align: middle; text-align: left;">' . u8(OCIResult($q, "SRADETNAME")) . '</td>';

        if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE") || 1 == OCIResult($q, "INTERSTATE")) {
            $tmp_str = "";
            if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE"))
                $tmp_str .= " (Дубль)";
            if (1 == OCIResult($q, "INTERSTATE"))
                $tmp_str .= " (Межгород)";
            if (USER_VIEW == $_SESSION['user_role'])
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($tmp_str) . '</td>';
            else echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "STATUS") .$tmp_str) . '</td>';
        } else {
            echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "STATUS")) . '</td>';
        }
        echo '<td style="vertical-align: middle; text-align: center;">' . u8(OCIResult($q, "STATUS_DET")) . '</td>';

        $q_write = OCIParse($c, "SELECT to_char(DATE_WRITE,'dd.mm.yyyy hh24:mi') DATE_WRITE FROM WRITE_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");
        OCIBindByName($q_write, ":id", $base_id);
        $date_write = '';
        if (OCIExecute($q_write, OCI_DEFAULT) && OCIFetch($q_write))
            $date_write = trim(OCIResult($q_write, "DATE_WRITE"));
        if ('' == $date_write) {
            $q_clinic = OCIParse($c, "SELECT to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi:ss') as CLIENT_DATE 
FROM CALL_BASE_CLINIC WHERE BASE_ID=:id ORDER BY CLIENT_DATE desc, WRITE_ADD desc");
            OCIBindByName($q_clinic, ":id", $base_id);
            OCIExecute($q_clinic, OCI_DEFAULT);
            OCIFetch($q_clinic);
            $date_write = OCIResult($q_clinic, "CLIENT_DATE");
        }

        $q_visit = OCIParse($c, "SELECT to_char(DATE_VISIT,'dd.mm.yyyy hh24:mi') DATE_VISIT FROM VISIT_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");
        OCIBindByName($q_visit, ":id", $base_id);
        $date_visit = '';
        if (OCIExecute($q_visit, OCI_DEFAULT) && OCIFetch($q_visit))
            $date_visit = OCIResult($q_visit, "DATE_VISIT");

        $date = date("d.m.Y");
        if ($date_visit != '')
            $visit = 'Пришел';
        else {
            if ($date_write != '') {
                if (strtotime($date_write) >= strtotime($date))
                    $visit = 'Ждем';
                elseif (strtotime($date_write) < strtotime($date))
                    $visit = 'Не пришел';
            } else $visit = '';
        }

        if (IT_PLANET != $_SESSION['login_id_med'] && !in_array($_SESSION['login_id_med'],EXPORT_CUT)) { // ограничение для IT Planet и Гусарова
            echo '<td style="vertical-align: middle; text-align: center;">' . u8($date_write) . '</td>';
            if (109 != $_SESSION['login_id_med'] && 113 != $_SESSION['login_id_med']) { // Убираем у Кокоса
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($visit) . '</td>';
            }
        }
        else {
            if (in_array($_SESSION['login_id_med'],SHOW_ITOG)) { // Отдельно показываем итог
                echo '<td style="vertical-align: middle; text-align: center;">' . u8($visit) . '</td>';
            }
        }
        echo '</tr>';
    }
    echo '</table>';
}
elseif (EXPORT_BILLING == $ReportId) {
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'Macintosh')) { //тестируем для Mac OS
        header("Content-type: application/csv; charset=UTF-8");
        //header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        $bMac = TRUE;
    }
    else {
        header("Content-type: application/xls; charset=windows-1251");
        //header("Content-Disposition: attachment; filename=" . $filename . ".xls");
        $bMac = FALSE;
    }
    //header("Content-type: application/xls; charset=windows-1251");
//header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=".$filename.".xls");
//header("Pragma: no-cache");
	
	$sql_route_ids[1]=" select in_route_id ";
	$sql_route_ids[2]=" from SOURCE_AUTO sa";
	$sql_route_ids[3]="";
	$sql_route_ids[4]=" where in_route_id is not null ";
	if (isset($_POST['S_Auto']) && !in_array(SOURCE_ALL, $_POST['S_Auto'])) {
        $sql_route_ids[4] .= " and sa.ID in ('" . implode("','", $_POST['S_Auto']) . "')";
	}	
    if (USER_ADMIN != $_SESSION['user_role']) {
        $sql_route_ids[4] .= " and sa.ID in ( 
        select decode(ad.source_auto_id,-1,sa.id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
	}
	//echo implode(" ",$sql_route_ids);
	$q=OCIParse($c,implode(" ",$sql_route_ids));
	if(!OCIExecute($q)) exit();
	while(OCIFetch($q)) {
		$route_ids[]=OCIResult($q,"IN_ROUTE_ID");
	}
	if(count($route_ids)>0) $and_route_ids=" and c.in_route_id in ('".implode("','",str_replace(",","','",$route_ids))."') ";
	//echo implode("-",$route_ids);

    if ($bMac) {
        $q_text1 = "SELECT convert(varchar(25),start_date,120) start_date, c.in_route_id [ID], r.Название_Маршрута [Route_Name], 
        r.Ключ_Задачи [Key Task], c.idChain [ID Chain], ";
        if (in_array(CAN_PHONE, $data_acc_arr)) $q_text1 .= "c.a_number [Num_A], ";
        $q_text1 .= "c.b_number [Num_B], 
	--c.a_line_id, c.b_line_id,
	--c.call_type+(case when c.is_first='y' then '(first)' else '' end) call_type,
    t.name_det [Call Type], 
    case when c.call_type in ('in', 'inop') and c.is_first='y' then 
    datediff(ss,start_date,isnull((case 
           when c.queue_date is null then c.originate_date 
           when c.originate_date is null then c.queue_date
           when c.queue_date<=c.originate_date then c.queue_date
           else c.originate_date end),end_date))
    else NULL end [IVR, sec],
    convert(varchar(25),c.queue_date,120) [Queued],
    convert(varchar(25),c.originate_date,120) [Redirected_from_IVR],
    datediff(ss,queue_date,isnull(c.ring_date,c.end_date)) [Queue, sec],
    convert(varchar(25),c.ring_date,120) [Entered the operator],
    c.oper_user_name [Operator],
    datediff(ss,ring_date,isnull(c.answer_date,c.end_date)) [KPV, sec],
    case when c.call_type in ('inout','inoutfail','cbout','cboutfail') 
    then datediff(ss,start_date,isnull(c.answer_date,c.end_date)) 
    else datediff(ss,queue_date,isnull(c.answer_date,c.end_date)) end [Waiting, sec (Queue+KPV)],
    convert(varchar(25),c.answer_date,120) [Answered],
    datediff(ss,c.answer_date,isnull(c.oper_end_date,c.end_date)) [Talk, sec],
    convert(varchar(25),c.end_date,120) [Finished],
    c.transit_bnumber [Transit Number],
    datediff(ss,isnull(c.start_transit_date,c.end_date),c.end_transit_date) [Transit, sec],
    c.end_transit_date [Transit finished],
    c.call_result_code [Result Code],
    c.call_result_info [Result Text],
    case when aaa.isrecorded=1 and call_type in ('callback','in') then 
    '" . $oktell_records_url . "?idchain='+cast(c.idChain as varchar(36)) end [Link to record],
	c.in_sip_call_id [SIP Call-ID]";
    }
    else {
        $q_text1 = "SELECT convert(varchar(25),start_date,120) start_date, c.in_route_id [ID Маршрута], r.Название_Маршрута, r.Ключ_Задачи, c.idChain [ID цепочки], ";
        if (in_array(CAN_PHONE, $data_acc_arr)) $q_text1 .= "c.a_number [Номер А], ";
        $q_text1 .= "c.b_number [Номер Б], 
	--c.a_line_id, c.b_line_id,
	--c.call_type+(case when c.is_first='y' then '(first)' else '' end) call_type,
    t.name_det [Тип звонка], 
    case when c.call_type in ('in', 'inop') and c.is_first='y' then 
    datediff(ss,start_date,isnull((case 
           when c.queue_date is null then c.originate_date 
           when c.originate_date is null then c.queue_date
           when c.queue_date<=c.originate_date then c.queue_date
           else c.originate_date end),end_date))
    else NULL end [IVR, сек],
    convert(varchar(25),c.queue_date,120) [поставлен в очередь],
    convert(varchar(25),c.originate_date,120) [переадресован из IVR],
    datediff(ss,queue_date,isnull(c.ring_date,c.end_date)) [Очередь, сек],
    convert(varchar(25),c.ring_date,120) [поступил на оператора],
    c.oper_user_name [Оператор],
    datediff(ss,ring_date,isnull(c.answer_date,c.end_date)) [КПВ, сек],
    case when c.call_type in ('inout','inoutfail','cbout','cboutfail') 
    then datediff(ss,start_date,isnull(c.answer_date,c.end_date)) 
    else datediff(ss,queue_date,isnull(c.answer_date,c.end_date)) end [Ожидание, сек (Очередь+КПВ)],
    convert(varchar(25),c.answer_date,120) [Отвечен],
    datediff(ss,c.answer_date,isnull(c.oper_end_date,c.end_date)) [Разговор, сек],
    convert(varchar(25),c.end_date,120) [Завершен],
    c.transit_bnumber [транзитный номер],
    datediff(ss,isnull(c.start_transit_date,c.end_date),c.end_transit_date) [Транзитный, сек],
    c.end_transit_date [транзитный завершен],
    c.call_result_code [код результата],
    c.call_result_info [текст результата],
    case when aaa.isrecorded=1 and call_type in ('callback','in') then 
    '" . $oktell_records_url . "?idchain='+cast(c.idChain as varchar(36)) end [Ссылка на запись],
	c.in_sip_call_id [SIP Call-ID]";
    }
    $q_text2 = " FROM [oktell_CDR].[dbo].[inbound_route_CDR] c ";
    $q_text3 = " left join (select max(isrecorded) isrecorded, 
    IdChain from [oktell].[dbo].[A_Stat_Connections_1x1] with (nolock) group by IdChain) aaa on aaa.IdChain=c.idChain, 
    [oktell_CDR].[dbo].[list_inbound_calltypes] t, [oktell].[dbo].[SVA_Inbound_Routes] r ";

    $q_text4 = " where t.id=c.call_type and r.id=c.in_route_id and r.location_id=1 \n";
	
	$q_text4 .= $and_route_ids."\n";

    $q_text4 .= " and c.start_date between '".$rep_start_date."' and dateadd(dd,+1,cast('".$rep_end_date."' as date))";
    $q_text4 .= " and (call_type in ('cbfail','cbout','cboutfail','cbtransit','inop','inout','inoutfail','intransit','cbinop','?outfail') or 
        (call_type in ('callback','in') and c.is_first='y'))";
    $q_text5 = ""; // group by
    $q_text6 = " order by c.start_date_chain, c.start_date, r.Название_Маршрута, r.Ключ_Задачи";
    $sql_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_text5 . $q_text6;
	
	//echo $sql_text;
	
    $q = $c_okt->prepare($sql_text);
    $q->execute();

	$out=fopen('php://output', 'w');
	
    $i=0; while($row = $q->fetch(PDO::FETCH_ASSOC)) {$i++;
        if($i==1) {
			foreach($row as $columnname => $columnvalue) {
				if($columnname=='ID цепочки' or $columnname=='Ссылка на запись') { // ограничение для прослушивания записей
					if ($data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) {
                        /*if ($bMac)
                            $heads[]=u8($columnname);
                        else*/ $heads[]=$columnname;
					}
				}
				else {
                    /*if ($bMac)
                        $heads[]=u8($columnname);
					else*/ $heads[]=$columnname;
				}
			}
            if ($bMac)
                fputcsv($out,$heads,';');
			else fputcsv($out,$heads,chr(9));
		}
		$out_row=array();
		foreach($heads as $columnnumber => $columnname) {
            if ($bMac) {
                if (isset($row[$columnname]))
                    $out_row[] = u8($row[$columnname]);
                else $out_row[] = "";
            }
            else $out_row[]=$row[$columnname];
		}
        if ($bMac)
            fputcsv($out,$out_row,';');
		else fputcsv($out,$out_row,chr(9));
    }
	fclose($out);
}
/*elseif (EXPORT_OPERATOR == $ReportId) {
    $q_start = "SELECT id, fio from USERS ";
    if (isset($_POST['UserId'])) {
        if (!in_array('-1', $_POST['UserId']))
            $q_users = implode(',', $_POST['UserId']);
        else {
            $q_users = "";
            $strfilt = " (ROLE_ID = ".USER_USER." or ROLE_ID = ".USER_SUPER.")";
            if (GetData::GetUsersDep(TRUE, $strfilt, NULL, 'not')) {
                foreach(GetData::$array_userd as $key => $value) {
                    $q_users .= $value['ID'] . ",";
                }
                $q_users = substr($q_users, 0, -1);
            }
            //$q_users .= $_SESSION['login_id_med']; // и сам супервизор
        }
        if (strlen($q_users) > 0)
            $q_start .= " WHERE ID in (".$q_users.")";
    }
    $q_start .= " order by fio";

    $q = OCIParse($c, $q_start);
    OCIExecute($q);
    $calls_arr = array();
    while (OCIFetch($q)) { // составим и инициируем список всех операторов департамента, включая супервизора
        $operator_id = OCIResult($q, "ID");
        $calls_arr[$operator_id]['FIO'] = OCIResult($q, "FIO");
        $calls_arr[$operator_id]['DAYS'] = 0;
        $calls_arr[$operator_id]['TOTAL'] = 0;
        $calls_arr[$operator_id]['ZACHET'] = 0;
        $calls_arr[$operator_id]['CLINIC'] = 0;
        $calls_arr[$operator_id]['CLINIC_NOT'] = 0;
        $calls_arr[$operator_id]['ERROR_ALL'] = 0;
        $calls_arr[$operator_id]['BREAK'] = 0;
        $calls_arr[$operator_id]['ERROR'] = 0;
        $calls_arr[$operator_id]['REPEAT'] = 0;
    }

    $q_text1 = "select count(*) as pnum, usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy') as CALL_DATE, status_id ";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " left join users usr on usr.id = cb.fio_id ";
    $q_text4 = " WHERE STATUS_ID between ".STATUS_CLINIC." and ".STATUS_CLINIC_NOT;
    $q_text4 .= " and (DATE_CALL between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";

    if (isset($_POST['UserId']) && strlen($q_users) > 0)
        $q_text4 .= " and cb.FIO_ID in (".$q_users.")";

    if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId']))
        $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $_POST['ServiceId']) . ")";

    $q_text5 = " group by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
    $q_text6 = " order by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_text5 . $q_text6;

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    $operator_id = -1;
    $date_last = "";
    $itog_call = $itog_zachet = $itog_clinic = $itog_clinic_not = 0;
    $itog_error_all = $itog_error = $itog_repeat = $itog_break = 0;
    while (OCIFetch($q)) { // наполняем данными о звонках
        $pnum = OCIResult($q, "PNUM");
        $itog_call += $pnum;
        if (OCIResult($q, "FIO_ID") != $operator_id) { // новый оператор
            $operator_id = OCIResult($q, "FIO_ID");
            $date_last = OCIResult($q, "CALL_DATE");
            $calls_arr[$operator_id]['DAYS'] = 1;
        }

        $calls_arr[$operator_id]['TOTAL'] += $pnum;
        if (STATUS_CLINIC == OCIResult($q, "STATUS_ID") ||
            STATUS_CLINIC_NOT == OCIResult($q, "STATUS_ID")) { // успешный статус?!
            $itog_zachet += $pnum;
            $calls_arr[$operator_id]['ZACHET'] += $pnum;

            if (STATUS_CLINIC == OCIResult($q, "STATUS_ID")) {
                $itog_clinic += $pnum;
                $calls_arr[$operator_id]['CLINIC'] += $pnum;
            } else { // STATUS_CLINIC_NOT // STATUS_CALL_STOP
                $itog_clinic_not += $pnum;
                $calls_arr[$operator_id]['CLINIC_NOT'] += $pnum;
            }
        } else {
            $itog_error_all += $pnum;
            $calls_arr[$operator_id]['ERROR_ALL'] += $pnum;

            if (STATUS_ERROR == OCIResult($q, "STATUS_ID")) {
                $itog_error += $pnum;
                $calls_arr[$operator_id]['ERROR'] += $pnum;
            } elseif (STATUS_REPEAT == OCIResult($q, "STATUS_ID")) {
                $itog_repeat += $pnum;
                $calls_arr[$operator_id]['REPEAT'] += $pnum;
            } else { //STATUS_BREAK_LINE
                $itog_break += $pnum;
                $calls_arr[$operator_id]['BREAK'] += $pnum;
            }
        }

        if (OCIResult($q, "CALL_DATE") != $date_last) { // новая смена у оператора
            $calls_arr[$operator_id]['DAYS'] += 1;
            $date_last = OCIResult($q, "CALL_DATE");
        }
    }

    $head_arr = array("Оператор", "Кол-во смен", "Кол-во звонков", "Успешных за смену", "Запись в клинику", "Отказ от записи",
        "Кол-во ошибок", "Повторный пациент", "Обрыв", "Ошибка", "% ошибок");
    echo '<table>';
    echo '<tr style="font-weight: bold; vertical-align: middle;"><td>Отчет по количеству звонков </td>';
    if ($rep_start_date != $rep_end_date)
        echo '<td style="text-align: center">c ' . $rep_start_date . '</td><td> по ' . $rep_end_date . '</td>';
    else echo '<td style="text-align: center">на ' . $rep_start_date . '</td>';
    echo '</tr>';
    echo '<tr>';
    foreach ($head_arr as $value)
        echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $value . '</td>';
    echo '</tr>';

    foreach ($calls_arr as $value) { // отрисовываем полученные данные
        echo '<tr>';
        echo '<td style="text-align: left; vertical-align: middle;">' . $value['FIO'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['DAYS'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['TOTAL'] . '</td>';
        //echo '<td style="text-align: center; vertical-align: middle;">' . $value['ZACHET'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">&nbsp;' . ($value['DAYS'] != 0 ? round($value['ZACHET'] / $value['DAYS'],2) : 0) . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['CLINIC'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['CLINIC_NOT'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['ERROR_ALL'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['REPEAT'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['BREAK'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">' . $value['ERROR'] . '</td>';
        echo '<td style="text-align: center; vertical-align: middle;">&nbsp;' . ($value['TOTAL'] != 0 ? round($value['ERROR_ALL'] / $value['TOTAL'] * 100, 2) : 0) . '</td>';
        echo '</tr>';
    }
    echo '<tr>';
    echo '<td style="font-weight: bold; text-align: right; vertical-align: middle;">Итого: </td><td></td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_call . '</td><td></td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_clinic . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_clinic_not . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_error_all . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_repeat . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_break . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $itog_error . '</td>
<td style="font-weight: bold; text-align: center; vertical-align: middle;">&nbsp;' . ($itog_call != 0 ? round($itog_error_all/$itog_call*100,2) : 0) . '</td>';
    echo '</tr>';
    echo '<tr>';
    foreach ($head_arr as $value)
        echo '<td style="font-weight: bold; text-align: center; vertical-align: middle;">' . $value . '</td>';
    echo '</tr>';
    echo '</table>';
}*/

exit;