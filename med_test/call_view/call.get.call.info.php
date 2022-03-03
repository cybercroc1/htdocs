<?php
//функция возвращает все данные о звонке, ??? включая права текущего пользователя

function get_call_info($c, $base_id) {
	$base = array();
    if (FALSE == DEBUG_MODE)
        $table_name = 'CALL_BASE';
    else $table_name = 'CALL_BASE_TEST';
    //информация о заявке
	if (DB_OCI) {
        $selectstr = "SELECT to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER_NORM, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID, 
cb.CALL_THEME_ID AS THEME_ID, cb.CALL_TYPE_ID AS CT_ID, cb.SOURCE_TYPE_ID AS ST_ID, stt.NAME AS ST, 
cb.SERVICE_ID AS SRV_ID, serv.NAME AS SRVNAME, cb.SERVICE_DET_ID AS SRV_DET_ID,
cb.SOURCE_AUTO_ID AS SRA_ID, sr_a.BNUMBER AS SRABNUMBER, sr_a.NAME AS SRANAME, sr_a.SOURCE_TYPE AS SRATYPE, cb.COMMENTS,
cb.SOURCE_MAN_ID AS SRM_ID, sr_man.NAME AS SRMNAME, sr_man.DETAIL AS DETAIL, cb.SOURCE_MAN_DET_ID AS SRDET_ID, 
cb.SOURCE_MAN_ID_NEW AS SRM_ID_NEW, sra_det.NAME AS SRADETNAME, cb.SOURCE_MAN_DET_ID_NEW AS SRDET_ID_NEW,
cb.STATUS_ID, stat.NAME as STATUS, cb.STATUS_DET_ID, stat_det.NAME as STATUS_DET, cb.FIO_ID AS FIO_ID, usr.FIO AS FIO, cb.CALL_DIRECTION, 
cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_MOB_NORM, cb.PHONE_NEW, cb.PHONE_NEW_NORM, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET,
to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.CALL_BACK_DATE,'dd.mm.yyyy hh24:mi') as CALL_BACK_DATE, cb.CALL_BACK_NUM, cb.TRANSFER_NUM,
to_char(cb.DATE_CLOSE,'dd.mm.yyyy hh24:mi:ss') as DATE_CLOSE, cb.LEAD_ID, cb.CALL_DOUBLE, cb.INTERSTATE, cb.OKTELL_IDCHAIN, cb.OKTELL_SERVER_ID, cb.entry_date_1c,
cb.SECOND_STATUS_ID, cb.SECOND_STATUS_DET_ID, cb.SECOND_LAST_CHANGE, cb.SECOND_FIO_ID, to_char(cb.DATE_SECOND_CLOSE,'dd.mm.yyyy hh24:mi:ss') as DATE_SECOND_CLOSE,
cb.SECRET, cb.PAY_SUPPLIER, cb.POSSIBLE_CLINICS, c.CITY
FROM ".$table_name." cb 
  LEFT JOIN SOURCE_TYPE stt ON cb.SOURCE_TYPE_ID = stt.ID
  LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID
  LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID
  LEFT JOIN CITIES c ON c.id = sr_a.city_id
  LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
  LEFT JOIN SOURCE_AUTO_DETAIL sra_det ON cb.SOURCE_MAN_ID_NEW = sra_det.ID
  LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID
  LEFT JOIN MED_STATUS stat ON cb.STATUS_ID = stat.ID
  LEFT JOIN MED_STATUS_DET stat_det ON cb.STATUS_DET_ID = stat_det.ID
  WHERE cb.ID = " . $base_id;
        //sr_det.NAME AS SRDETNAME,
        //LEFT JOIN SOURCE_MAN sr_man_new ON cb.SOURCE_MAN_ID_NEW = sr_man_new.ID
        //LEFT JOIN SOURCE_MAN_DETAIL sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
//    echo "<textarea>".$selectstr."</textarea>";

        $q = OCIParse($c, $selectstr);
        OCIExecute($q, OCI_DEFAULT);
        if (OCIFetch($q)) {
            $base['date_call'] = OCIResult($q, "DATE_CALL");
            $base['anumber'] = OCIResult($q, "ANUMBER_NORM");
            $base['bnumber'] = OCIResult($q, "BNUMBER");
            $base['sc_agid'] = OCIResult($q, "SC_AGID");
            $base['sc_call_id'] = OCIResult($q, "SC_CALL_ID");
            $base['sc_project_id'] = OCIResult($q, "SC_PROJECT_ID");
            $base['call_direction'] = OCIResult($q, "CALL_DIRECTION");
            $base['theme_id'] = OCIResult($q, "THEME_ID");
            $base['st_id'] = OCIResult($q, "ST_ID");
            $base['st'] = OCIResult($q, "ST");
            $base['ct_id'] = OCIResult($q, "CT_ID");
            $base['srv_id'] = OCIResult($q, "SRV_ID");
            $base['srvname'] = OCIResult($q, "SRVNAME");
            $base['srv_det_id'] = OCIResult($q, "SRV_DET_ID");
            $base['sra_id'] = OCIResult($q, "SRA_ID");
            $base['srabnumber'] = OCIResult($q, "SRABNUMBER");
            $base['sraname'] = OCIResult($q, "SRANAME");
            $base['sratype'] = OCIResult($q,"SRATYPE");
            $base['srm_id'] = OCIResult($q, "SRM_ID");
            $base['srmname'] = OCIResult($q, "SRMNAME");
            $base['srmdetail'] = OCIResult($q, "DETAIL");
            $base['srdet_id'] = OCIResult($q, "SRDET_ID");
            //$base['srdetname'] = OCIResult($q, "SRDETNAME");
            $base['srm_id_new'] = OCIResult($q, "SRM_ID_NEW");
            //$base['srmname_new'] = OCIResult($q, "SRMNAME_NEW");
            $base['srmname_new'] = OCIResult($q, "SRADETNAME");
            $base['srdet_id_new'] = OCIResult($q, "SRDET_ID_NEW");
            $base['comment'] = OCIResult($q, "COMMENTS");
            $base['fio_id'] = OCIResult($q, "FIO_ID");
            $base['fio'] = OCIResult($q, "FIO");
            $base['fio_id_sec'] = OCIResult($q, "SECOND_FIO_ID");
            $base['client_name'] = OCIResult($q, "CLIENT_NAME");
            $base['age'] = OCIResult($q, "AGE");
            $base['phone_mob'] = OCIResult($q, "PHONE_MOB");
            $base['phone_mob_norm'] = OCIResult($q, "PHONE_MOB_NORM");
            $base['phone_new'] = OCIResult($q, "PHONE_NEW");
            $base['phone_new_norm'] = OCIResult($q, "PHONE_NEW_NORM");
            $base['email'] = OCIResult($q, "EMAIL");
            $base['result_id'] = OCIResult($q, "RESULT_ID");
            $base['result_det'] = OCIResult($q, "RESULT_DET");
            $base['status_id'] = OCIResult($q, "STATUS_ID");
            $base['status_id_sec'] = OCIResult($q, "SECOND_STATUS_ID");
            $base['status_name'] = OCIResult($q, "STATUS");
            $base['status_det_id'] = OCIResult($q, "STATUS_DET_ID");
            $base['status_det_id_sec'] = OCIResult($q, "SECOND_STATUS_DET_ID");
            $base['status_det_name'] = OCIResult($q, "STATUS_DET");
            $base['last_change'] = OCIResult($q, "LAST_CHANGE");
            $base['last_change_sec'] = OCIResult($q, "SECOND_LAST_CHANGE");
            $base['call_back_date'] = OCIResult($q, "CALL_BACK_DATE");
            $base['call_back_num'] = OCIResult($q, "CALL_BACK_NUM");
            $base['transfer_num'] = OCIResult($q, "TRANSFER_NUM");
            $base['date_close'] = OCIResult($q, "DATE_CLOSE");
            $base['date_close_sec'] = OCIResult($q, "DATE_SECOND_CLOSE");
            $base['entry_date_1c'] = OCIResult($q, "ENTRY_DATE_1C");
			$base['secret'] = OCIResult($q, "SECRET");
			$base['pay_supplier'] = OCIResult($q, "PAY_SUPPLIER");
			$base['possible_clinics'] = OCIResult($q, "POSSIBLE_CLINICS");
			$base['city_auto'] = OCIResult($q, "CITY");

            if ($base['fio'] == '') $base['opened'] = 'y';
            else $base['opened'] = ''; //свободный звонок (без назначенного оператора)

            if (DETAILS_AMNESY == $base['srdet_id']) {
                $base['srdetname'] = "Не помнит";
            } elseif (DETAILS_PROMO == $base['srdet_id']) {
                $base['srdetname'] = "На улице у промоутера";
            } elseif (DETAILS_OTHER == $base['srdet_id']) {
                $base['srdetname'] = "Другое";
            } else {
                if (SOURCE_FLAER == $base['srm_id'] || SOURCE_CATALOG == $base['srm_id'] ||
                    SOURCE_FLAER_SUB == $base['srm_id'] || SOURCE_FLAER_CAR == $base['srm_id'] ||
                    SOURCE_LIFT == $base['srm_id'] || SOURCE_STOP == $base['srm_id']) {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM SUBWAYS WHERE ID=:id");
                } elseif (SOURCE_SERT == $base['srm_id']) {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
                } else {
                    $q_detail_det = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
                }
                OCIBindByName($q_detail_det, ":id", $base['srdet_id']);
                OCIExecute($q_detail_det, OCI_DEFAULT);
                OCIFetch($q_detail_det);
                $base['srdetname'] = OCIResult($q_detail_det, "NAME");
            }

            if (DETAILS_AMNESY == $base['srdet_id_new']) {
                $base['srdetname_new'] = "Не помнит";
            } elseif (DETAILS_PROMO == $base['srdet_id_new']) {
                $base['srdetname_new'] = "На улице у промоутера";
            } elseif (DETAILS_OTHER == $base['srdet_id_new']) {
                $base['srdetname_new'] = "Другое";
            } else {
                if (SOURCE_FLAER == $base['srm_id_new'] || SOURCE_CATALOG == $base['srm_id_new']||
                    SOURCE_FLAER_SUB == $base['srm_id_new'] || SOURCE_FLAER_CAR == $base['srm_id_new'] ||
                    SOURCE_LIFT == $base['srm_id_new'] || SOURCE_STOP == $base['srm_id_new']) {
                    $q_detail_det_new = OCIParse($c,"SELECT NAME FROM SUBWAYS WHERE ID=:id" );
                }
                elseif (SOURCE_SERT == $base['srm_id_new']) {
                    $q_detail_det_new = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
                }
                else {
                    $q_detail_det_new = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
                }
                OCIBindByName($q_detail_det_new, ":id", $base['srdet_id_new']);
                OCIExecute($q_detail_det_new, OCI_DEFAULT);
                OCIFetch($q_detail_det_new);
                $base['srdetname_new'] = OCIResult($q_detail_det_new,"NAME");
            }

            /*if (FALSE == DEBUG_MODE)
                $table_name_cl = 'CALL_BASE_CLINIC';
            else $table_name_cl = 'CALL_BASE_CLINIC_TEST';
            $q_clinic = OCIParse($c, "SELECT HOSPITAL_ID, CLIENT_NAME, AGE, CLIENT_PHONE, CLIENT_STATUS, 
to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi') CLIENT_DATE FROM ".$table_name_cl." WHERE BASE_ID=:id");
            OCIBindByName($q_clinic, ":id", $base_id);
            OCIExecute($q_clinic, OCI_DEFAULT);
            //$base['hospital'] = OCI_Fetch_All($q_clinic);
            //$base['hospital'] = OCI_Fetch_Array($q_clinic);
            $base['hospital'] = OCIResult($q_clinic,"HOSPITAL_ID");
            $base['clinic_client_name'] = OCIResult($q_clinic,"CLIENT_NAME");
            $base['clinic_client_phone'] = OCIResult($q_clinic, "CLIENT_PHONE");
            $base['clinic_client_status'] = OCIResult($q_clinic, "CLIENT_STATUS");
            $base['clinic_client_date'] = OCIResult($q_clinic, "CLIENT_DATE");
            $base['ages_cl'] = OCIResult($q_clinic,"AGE");*/

            $base['lead_id'] = OCIResult($q, "LEAD_ID");
            if (isset($base['lead_id']) && NULL != $base['lead_id']) {
                //$q_mail = OCIParse($c, "SELECT DBMS_LOB.substr(mail_body, 5000) MAIL_BODY FROM MAIL_LEADCOLLECTOR WHERE ID=:lead_id");
                $q_mail = OCIParse($c, "SELECT mail_body_text as MAIL_BODY, h_from, h_subject FROM MAIL_LEADCOLLECTOR WHERE ID=:lead_id");
                OCIBindByName($q_mail, ":lead_id", $base['lead_id']);
                OCIExecute($q_mail, OCI_DEFAULT);
                OCIFetch($q_mail);
                $base['mail_body'] = trim(OCIResult($q_mail, "MAIL_BODY"));
				$base['mail_from'] = OCIResult($q_mail, "H_FROM");
				$base['mail_subject'] = OCIResult($q_mail, "H_SUBJECT");
            }
            else {
                $base['mail_body'] = "---";
                $base['mail_from'] = $base['mail_subject'] = '';
            }
            $base['call_double'] = OCIResult($q, "CALL_DOUBLE");
            $base['interstate'] = OCIResult($q, "INTERSTATE");
            $base['okt_idchain'] = OCIResult($q, "OKTELL_IDCHAIN");
            $base['okt_server_id'] = OCIResult($q, "OKTELL_SERVER_ID");
        } else {
            $base['error'] = "<b style='color: red'>ОШИБКА: Такого звонка не существует или у вас нет прав для доступа к нему!</b>";
        }
    }
    else {
        $selectstr = "SELECT DATE_FORMAT(cb.DATE_CALL,'%d.%m.%Y %H:%i:%s') AS DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID, 
                    cb.CALL_THEME_ID AS THEME_ID, cb.CALL_TYPE_ID AS CT_ID, cb.SOURCE_TYPE_ID AS ST_ID, stt.NAME AS ST, 
                    cb.SERVICE_ID AS SRV_ID, serv.NAME AS SRVNAME, cb.SERVICE_DET_ID AS SRV_DET_ID, 
                    cb.SOURCE_AUTO_ID AS SRA_ID, sr_a.BNUMBER AS SRABNUMBER, sr_a.NAME AS SRANAME, sr_a.SOURCE_TYPE AS SRATYPE, 
                    cb.SOURCE_MAN_ID AS SRM_ID, sr_man.NAME AS SRMNAME, cb.SOURCE_MAN_ID_NEW AS SRM_ID_NEW, sr_man_new.NAME AS SRMNAME_NEW, 
                    cb.SOURCE_MAN_DET_ID AS SRDET_ID, sr_det.NAME AS SRDETNAME, cb.SOURCE_MAN_DET_ID_NEW AS SRDET_ID_NEW, sr_det_new.NAME AS SRDETNAME_NEW,
                    cb.COMMENTS, cb.STATUS_ID, stat.NAME as STATUS, cb.FIO_ID AS FIO_ID, usr.FIO AS FIO, 
                    cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_MOB_NORM, cb.PHONE_NEW, cb.PHONE_NEW_NORM, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET,
                    DATE_FORMAT(cb.LAST_CHANGE,'%d.%m.%Y %H:%i:%s') AS LAST_CHANGE, DATE_FORMAT(cb.CALL_BACK_DATE,'%d.%m.%Y %H:%i:%s') AS CALL_BACK_DATE, cb.CALL_BACK_NUM, 
                    DATE_FORMAT(cb.DATE_CLOSE,'%d.%m.%Y %H:%i:%s') AS DATE_CLOSE, cb.LEAD_ID,
                    clinic.HOSPITAL_ID, clinic.CLIENT_NAME, clinic.CLIENT_PHONE, clinic.CLIENT_STATUS, 
                    DATE_FORMAT(clinic.CLIENT_DATE,'%d.%m.%Y %H:%i') AS CLIENT_DATE
            FROM CALL_BASE cb 
             LEFT JOIN SOURCE_TYPE stt ON cb.SOURCE_TYPE_ID = stt.ID
             LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID
             LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID
             LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
             LEFT JOIN SOURCE_MAN sr_man_new ON cb.SOURCE_MAN_ID_NEW = sr_man_new.ID
             LEFT JOIN SOURCE_MAN_DETAIL sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
             LEFT JOIN SOURCE_MAN_DETAIL sr_det_new ON cb.SOURCE_MAN_DET_ID_NEW = sr_det_new.ID
             LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID
             LEFT JOIN MED_STATUS stat ON cb.STATUS_ID = stat.ID
             LEFT JOIN CALL_BASE_CLINIC clinic ON cb.ID = clinic.BASE_ID
             WHERE cb.ID = " . $base_id;
 //   echo "<textarea>".$selectstr."</textarea>";
        $sql = mysqli_query($c, $selectstr);
        if ($sql && $result = $sql->fetch_array()) {
            $base['date_call'] = $result["DATE_CALL"];
            $base['anumber'] = $result["ANUMBER"];
            $base['bnumber'] = $result["BNUMBER"];
            $base['sc_agid'] = $result["SC_AGID"];
            $base['sc_project_id'] = $result["SC_PROJECT_ID"];
            $base['theme_id'] = $result["THEME_ID"];
            $base['st_id'] = $result["ST_ID"];
            $base['st'] = $result["ST"];
            $base['ct_id'] = $result["CT_ID"];
            $base['srv_id'] = $result["SRV_ID"];
            $base['srvname'] = $result["SRVNAME"];
            $base['srv_det_id'] = $result["SRV_DET_ID"];
            $base['sra_id'] = $result["SRA_ID"];
            $base['srabnumber'] = $result["SRABNUMBER"];
            $base['sraname'] = $result["SRANAME"];
            $base['sratype'] = $result["SRATYPE"];
            $base['srm_id'] = $result["SRM_ID"];
            $base['srmname'] = $result["SRMNAME"];
            $base['srdet_id'] = $result["SRDET_ID"];
            $base['srdetname'] = $result["SRDETNAME"];
            $base['srm_id_new'] = $result["SRM_ID_NEW"];
            $base['srmname_new'] = $result["SRMNAME_NEW"];
            $base['srdet_id_new'] = $result["SRDET_ID_NEW"];
            $base['srdetname_new'] = $result["SRDETNAME_NEW"];
            $base['comment'] = $result["COMMENTS"];
            $base['fio_id'] = $result["FIO_ID"];
            $base['fio'] = $result["FIO"];
            $base['status_id'] = $result["STATUS_ID"];
            $base['client_name'] = $result["CLIENT_NAME"];
            $base['age'] = $result["AGE"];
            $base['phone_mob'] = $result["PHONE_MOB"];
            $base['phone_mob_norm'] = $result["PHONE_MOB_NORM"];
            $base['phone_new'] = $result["PHONE_NEW"];
            $base['phone_new_norm'] = $result["PHONE_NEW_NORM"];
            $base['email'] = $result["EMAIL"];
            $base['result_id'] = $result["RESULT_ID"];
            $base['result_det'] = $result["RESULT_DET"];
            $base['status_name'] = $result["STATUS"];
            $base['last_change'] = $result["LAST_CHANGE"];
            $base['call_back_date'] = $result["CALL_BACK_DATE"];
            $base['call_back_num'] = $result["CALL_BACK_NUM"];
            $base['date_close'] = $result["DATE_CLOSE"];

            $base['hospital'] = $result["HOSPITAL_ID"];
            $base['clinic_client_name'] = $result["CLIENT_NAME"];
            $base['clinic_client_phone'] = $result["CLIENT_PHONE"];
            $base['clinic_client_status'] = $result["CLIENT_STATUS"];
            $base['clinic_client_date'] = $result["CLIENT_DATE"];

            //$base['opened'] = ''; //открытая заявка (без исполнителя)
            //if ($base['fio'] == '') $base['opened'] = 'y';

            $base['lead_id'] = $result["LEAD_ID"];
            /*if (isset($base['lead_id']) && NULL != $base['lead_id']) {
                //$q_mail = OCIParse($c, "SELECT DBMS_LOB.substr(mail_body, 5000) MAIL_BODY FROM MAIL_LEADCOLLECTOR WHERE ID=:lead_id");
                $q_mail = OCIParse($c, "SELECT mail_body_text as MAIL_BODY FROM MAIL_LEADCOLLECTOR WHERE ID=:lead_id");
                OCIBindByName($q_mail, ":lead_id", $base['lead_id']);
                OCIExecute($q_mail, OCI_DEFAULT);
                OCIFetch($q_mail);
                $base['mail_body'] = OCIResult($q_mail, "MAIL_BODY");
            }
            else*/ $base['mail_body'] = "---";
        }
        else {
            $base['error'] = "<b style='color: red'>ОШИБКА: Такого звонка не существует или у вас нет прав для доступа к нему!</b>";
        }
    }
    return $base;
}
