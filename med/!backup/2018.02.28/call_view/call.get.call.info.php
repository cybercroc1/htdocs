<?php
//функция возвращает все данные о звонке, ??? включая права текущего пользователя

function get_call_info($c, $base_id) {
	$base = array();
    //информация о заявке
	if (DB_OCI) {
        $selectstr = "SELECT to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID, 
                    cb.CALL_THEME_ID AS THEME_ID, cb.CALL_TYPE_ID AS CT_ID, ctt.NAME AS CT, cb.SERVICE_ID AS SRV_ID, serv.NAME AS SRVNAME, 
                    cb.SOURCE_AUTO_ID AS SRA_ID, sr_a.BNUMBER AS SRABNUMBER, sr_a.NAME AS SRANAME, 
                    cb.SOURCE_MAN_ID AS SRM_ID, sr_man.NAME AS SRMNAME, 
                    cb.SOURCE_MAN_DET_ID AS SRDET_ID, cb.COMMENTS, 
                    cb.STATUS_ID, stat.NAME as STATUS, cb.FIO_ID AS FIO_ID, usr.FIO AS FIO, 
                    cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_HOME, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET,
                    cb.LAST_CHANGE, to_char(cb.CALL_BACK_DATE,'dd.mm.yyyy hh24:mi') as CALL_BACK_DATE, cb.CALL_BACK_NUM, 
                    to_char(cb.DATE_CLOSE,'dd.mm.yyyy hh24:mi:ss') as DATE_CLOSE
            FROM CALL_BASE cb 
             LEFT JOIN CALL_TYPE ctt ON cb.CALL_TYPE_ID = ctt.ID
             LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID
             LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID
             LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
             LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID
             LEFT JOIN MED_STATUS stat ON cb.STATUS_ID = stat.ID
             WHERE cb.ID = " . $base_id;
        //sr_det.NAME AS SRDETNAME,
        //LEFT JOIN SOURCE_MAN_DETAIL sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
        //    echo "<textarea>".$selectstr."</textarea>";

        $q = OCIParse($c, $selectstr);
        OCIExecute($q, OCI_DEFAULT);
        if (OCIFetch($q)) {
            $base['date_call'] = OCIResult($q, "DATE_CALL");
            $base['anumber'] = OCIResult($q, "ANUMBER");
            $base['bnumber'] = OCIResult($q, "BNUMBER");
            $base['sc_agid'] = OCIResult($q, "SC_AGID");
            $base['sc_call_id'] = OCIResult($q, "SC_CALL_ID");
            $base['sc_project_id'] = OCIResult($q, "SC_PROJECT_ID");
            $base['theme_id'] = OCIResult($q, "THEME_ID");
            $base['ct_id'] = OCIResult($q, "CT_ID");
            $base['ct'] = OCIResult($q, "CT");
            $base['srv_id'] = OCIResult($q, "SRV_ID");
            $base['srvname'] = OCIResult($q, "SRVNAME");
            $base['sra_id'] = OCIResult($q, "SRA_ID");
            $base['srabnumber'] = OCIResult($q, "SRABNUMBER");
            $base['sraname'] = OCIResult($q, "SRANAME");
            $base['srm_id'] = OCIResult($q, "SRM_ID");
            $base['srmname'] = OCIResult($q, "SRMNAME");
            $base['srdet_id'] = OCIResult($q, "SRDET_ID");
            //$base['srdetname'] = OCIResult($q, "SRDETNAME");
            $base['comment'] = OCIResult($q, "COMMENTS");
            $base['fio_id'] = OCIResult($q, "FIO_ID");
            $base['fio'] = OCIResult($q, "FIO");
            $base['client_name'] = OCIResult($q, "CLIENT_NAME");
            $base['age'] = OCIResult($q, "AGE");
            $base['phone_mob'] = OCIResult($q, "PHONE_MOB");
            $base['phone_home'] = OCIResult($q, "PHONE_HOME");
            $base['email'] = OCIResult($q, "EMAIL");
            $base['result_id'] = OCIResult($q, "RESULT_ID");
            $base['result_det'] = OCIResult($q, "RESULT_DET");
            $base['status_id'] = OCIResult($q, "STATUS_ID");
            $base['last_change'] = OCIResult($q, "LAST_CHANGE");
            $base['call_back_date'] = OCIResult($q, "CALL_BACK_DATE");
            $base['call_back_num'] = OCIResult($q, "CALL_BACK_NUM");
            $base['date_close'] = OCIResult($q, "DATE_CLOSE");

            if ($base['fio'] == '') $base['opened'] = 'y';
            else $base['opened'] = ''; //свободный звонок (без назначенного оператора)

            if (SOURCE_FLAER == $base['srm_id'] || SOURCE_CATALOG == $base['srm_id']||
                SOURCE_FLAER_SUB == $base['srm_id'] || SOURCE_FLAER_CAR == $base['srm_id'] ||
                SOURCE_LIFT == $base['srm_id'] || SOURCE_STOP == $base['srm_id']) {
                $q_detail_det = OCIParse($c,"SELECT NAME FROM SUBWAYS WHERE ID=:id" );
            }
            else if (SOURCE_SERT == $base['srm_id']) {
                $q_detail_det = OCIParse($c, "SELECT NAME FROM HOSPITALS WHERE ID=:id");
            }
            else {
                $q_detail_det = OCIParse($c, "SELECT NAME FROM SOURCE_MAN_DETAIL WHERE ID=:id");
            }
            OCIBindByName($q_detail_det, ":id", $base['srdet_id']);
            OCIExecute($q_detail_det, OCI_DEFAULT);
            OCIFetch($q_detail_det);
            $base['srdetname'] = OCIResult($q_detail_det,"NAME");
        } else {
            $base['error'] = "<b style='color: red'>ОШИБКА: Такого звонка не существует или у вас нет прав для доступа к нему!</b>";
        }
    }
    else {
        $selectstr = "SELECT DATE_FORMAT(cb.DATE_CALL,'%d.%m.%Y %H:%i:%s') AS DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_PROJECT_ID, 
                    cb.CALL_THEME_ID AS THEME_ID, cb.CALL_TYPE_ID AS CT_ID, ctt.NAME AS CT, cb.SERVICE_ID AS SRV_ID, serv.NAME AS SRVNAME, 
                    cb.SOURCE_AUTO_ID AS SRA_ID, sr_a.BNUMBER AS SRABNUMBER, sr_a.NAME AS SRANAME, 
                    cb.SOURCE_MAN_ID AS SRM_ID, sr_man.NAME AS SRMNAME, 
                    cb.SOURCE_MAN_DET_ID AS SRDET_ID, sr_det.NAME AS SRDETNAME, cb.COMMENTS, 
                    cb.STATUS_ID, cb.FIO_ID AS FIO_ID, usr.FIO AS FIO, 
                    cb.CLIENT_NAME, cb.AGE, cb.PHONE_MOB, cb.PHONE_HOME, cb.EMAIL, cb.RESULT_ID, cb.RESULT_DET,
                    cb.LAST_CHANGE, DATE_FORMAT(cb.CALL_BACK_DATE,'%d.%m.%Y %H:%i:%s') AS CALL_BACK_DATE, cb.CALL_BACK_NUM, 
                    DATE_FORMAT(cb.DATE_CLOSE,'%d.%m.%Y %H:%i:%s') AS DATE_CLOSE
            FROM CALL_BASE cb 
             LEFT JOIN CALL_TYPE ctt ON cb.CALL_TYPE_ID = ctt.ID
             LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID
             LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID
             LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
             LEFT JOIN SOURCE_MAN_DETAIL sr_det ON cb.SOURCE_MAN_DET_ID = sr_det.ID
             LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID
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
            $base['ct_id'] = $result["CT_ID"];
            $base['ct'] = $result["CT"];
            $base['srv_id'] = $result["SRV_ID"];
            $base['srvname'] = $result["SRVNAME"];
            $base['sra_id'] = $result["SRA_ID"];
            $base['srabnumber'] = $result["SRABNUMBER"];
            $base['sraname'] = $result["SRANAME"];
            $base['srm_id'] = $result["SRM_ID"];
            $base['srmname'] = $result["SRMNAME"];
            $base['srdet_id'] = $result["SRDET_ID"];
            $base['srdetname'] = $result["SRDETNAME"];
            $base['comment'] = $result["COMMENTS"];
            $base['fio_id'] = $result["FIO_ID"];
            $base['fio'] = $result["FIO"];
            $base['status_id'] = $result["STATUS_ID"];
            $base['client_name'] = $result["CLIENT_NAME"];
            $base['age'] = $result["AGE"];
            $base['phone_mob'] = $result["PHONE_MOB"];
            $base['phone_home'] = $result["PHONE_HOME"];
            $base['email'] = $result["EMAIL"];
            $base['result_id'] = $result["RESULT_ID"];
            $base['result_det'] = $result["RESULT_DET"];
            $base['last_change'] = $result["LAST_CHANGE"];
            $base['call_back_date'] = $result["CALL_BACK_DATE"];
            $base['call_back_num'] = $result["CALL_BACK_NUM"];
            $base['date_close'] = $result["DATE_CLOSE"];

            //$base['opened'] = ''; //открытая заявка (без исполнителя)
            //if ($base['fio'] == '') $base['opened'] = 'y';
        }
        else {
            $base['error'] = "<b style='color: red'>ОШИБКА: Такого звонка не существует или у вас нет прав для доступа к нему!</b>";
        }
    }
    return $base;
}
