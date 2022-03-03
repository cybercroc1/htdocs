<?php
/*if (preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT']))
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>';
else*/

if(!isset($Export_but) and !isset($Export_xlsx)) { //длЯ экспорта в файл цеплять jquery не нужно

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
if (file_exists("./js/jquery.min.js")) {
    echo "<script type='text/javascript'>
        if(!window.jQuery) { 
            document.write(unescape('<script src=\"./js/jquery.min.js\">%3C/script%3E'));
        }</script>";
}

}
include 'base.php';

function u8($text) {return iconv('CP1251','UTF-8',$text);}
function cp($text) {return iconv('UTF-8','CP1251',$text);}

abstract class GetData
{
    static $conn = NULL, $dbase = NULL;
    static $bReg = FALSE;

    static function my_log($string, $bError = FALSE)
    {
        $now_name = date("Y-m-d");
        $now = date("Y-m-d H:i:s");
        $dir_name = $_SERVER['DOCUMENT_ROOT']."/logs/".substr($now_name,0,7);
        if (!file_exists($dir_name)) {
            mkdir($dir_name);
        }
        if (file_exists($dir_name)) {
            if ($bError) {
                $log_file_name = $dir_name . "/sql_log_err-" . $now_name . ".txt";
                file_put_contents($log_file_name, $now . " " . $string . "\r\n", FILE_APPEND);
            }
            $log_file_name = $dir_name . "/sql_log-" . $now_name . ".txt";
            file_put_contents($log_file_name, $now . " " . $string . "\r\n", FILE_APPEND);
        }
    }

    static function CreateConnect()
    {
        if (DB_OCI) {
            if (!self::$conn) {
                if (FALSE == (self::$conn = OCILogon(DB_USER, DB_PWD, DB_DB, "CL8MSWIN1251"))) {
                    $err = OCIError();
                    echo "Oracle Connect Error " . $err['message'];
                    exit();
                }
            }
        } else {
            if (!self::$dbase) {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                self::$dbase = mysqli_connect(DB_HOST, DB_USER, DB_PWD);

                if (mysqli_connect_errno()) {
                    printf("Connect failed: %s\n", mysqli_connect_error());
                    exit();
                }
                mysqli_select_db(self::$dbase, DB_DB);
            }
        }
    }

    static function GetConnect()
    {
        if (DB_OCI) {
            if (!self::$conn) self::CreateConnect();
            return (self::$conn);
        }
        if (!self::$dbase) self::CreateConnect();
        return(self::$dbase);
    }

    static function CloseConnect()
    {
        if (DB_OCI) {
            OCILogoff(self::$conn);
        } else {
            mysqli_close(self::$dbase);
        }
    }

    static function GetMedStatus($strWhere = NULL, $bAll = FALSE, $bDeleted = TRUE)
    {
        $sqlstr = "SELECT ID, NAME FROM MED_STATUS ";
        if ($strWhere)
            $sqlstr .= " where " . $strWhere;
        if (!$bAll)
            $sqlstr .= " and ID != " . STATUS_CLINIC_CALL; // они уже закрыты
        if (!$bDeleted)
            $sqlstr .= " and Redundant IS NULL";
        $sqlstr .= " ORDER BY SITUATION, ID";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_status'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_status'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_status']);
        }

        return $nrows;
    }

    static function GetMedStatusDet($strWhere = NULL, $iStatus = NULL, $bDeleted = TRUE)
    {
        $sqlstr = "SELECT ID, STATUS_ID, NAME FROM MED_STATUS_DET ";
        if ($iStatus)
            $sqlstr .= " where STATUS_ID = " . $iStatus; // для конкретного статуса
        else $sqlstr .= " where STATUS_ID = " . STATUS_NOT; // получим пустой список
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        $sqlstr .= " ORDER BY NAME"; // ?ID?
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_status_det'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_status_det'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_status_det']);
        }

        return $nrows;
    }

    static function GetHospitals($strWhere = NULL)
    {
        /*$sqlstr = "SELECT hosp.ID, hosp.CITY, hosp.NAME as NAME, SERVICE_ID, serv.NAME as SERVICE
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID";*/
        if (DB_OCI)
            $sqlstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME";
        else $sqlstr = "SELECT hosp.ID AS ID, CONCAT(hosp.CITY, '-', hosp.NAME, '(', serv.NAME, ')') AS NAME";
        $sqlstr .= " FROM HOSPITALS hosp, SERVICES serv WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ";
        if ($strWhere)
            $sqlstr .= " AND " . $strWhere;
        $sqlstr .= " ORDER BY hosp.CITY, hosp.NAME, serv.NAME ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_hospitals'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_hospitals'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_hospitals']);
        }

        return $nrows;
    }

    static function GetCallHistory($base_id = 0)
    {
        if (FALSE == DEBUG_MODE)
            $table_name = 'CALL_BASE_HIST';
        else $table_name = 'CALL_BASE_HIST_TEST';
        if (DB_OCI)
            $sqlstr = "SELECT BASE_ID, to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET_C, STATUS_ID, stat.NAME, stat.COLOR,
 USER_ID, OPERATOR || usr.FIO as FIO, COMMENTS FROM ".$table_name." hist
 LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
 LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID";
        else $sqlstr = "SELECT DATE_FORMAT(DATE_DET,'%d.%m.%Y %H:%i:%s') as DATE_DET_C, STATUS_ID, stat.NAME, stat.COLOR, 
 USER_ID, case WHEN operator is null then usr.fio else operator end as FIO, COMMENTS 
 FROM CALL_BASE_HIST hist
 LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
 LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID";
        if ($base_id != 0)
            $sqlstr .= " WHERE BASE_ID = " . $base_id;
        $sqlstr .= " ORDER BY DATE_DET, STATUS_ID";
        //var_dump($sqlstr);
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_hist'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_hist'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_hist']);
        }

        return $nrows;
    }
    static function GetCallWriteClinic($base_id = 0)
    {
        if (FALSE == DEBUG_MODE)
            $table_name_cl = 'CALL_BASE_CLINIC';
        else $table_name_cl = 'CALL_BASE_CLINIC_TEST';
        if (DB_OCI)
            $sqlstr = "SELECT HOSPITAL_ID, CLIENT_NAME, AGE, CLIENT_PHONE, CLIENT_STATUS, 
to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi') CLIENT_DATE FROM ".$table_name_cl;
        else $sqlstr = "SELECT HOSPITAL_ID, CLIENT_NAME, AGE, CLIENT_PHONE, CLIENT_STATUS, 
DATE_FORMAT(CLIENT_DATE,'%d.%m.%Y %H:%i') as CLIENT_DATE FROM ".$table_name_cl;
        if ($base_id != 0)
            $sqlstr .= " WHERE BASE_ID = " . $base_id;
        $sqlstr .= " ORDER BY CLIENT_DATE";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_wc'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_wc'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_wc']);
        }

        return $nrows;
    }

    static function GetThemes($strWhere = NULL)
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, NAME, TARGET, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') DELETED FROM CALL_THEME WHERE ID != ". THEME_NOT;
        else $sqlstr = "SELECT ID, NAME, TARGET, DELETED FROM CALL_THEME WHERE ID != ". THEME_NOT;
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        $sqlstr .= " ORDER BY ID ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_theme'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_theme'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_theme']);
        }

        return $nrows;
    }

    static function GetCallType()
    {
        $sqlstr = "SELECT ID, NAME FROM CALL_TYPE WHERE ID != -1 ORDER BY ID";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_ctype'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_ctype'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_ctype']);
        }

        return $nrows;
    }

    static function GetSourceType($bAll = FALSE, $bAccess = FALSE)
    {
        $sqlstr = "SELECT ID, NAME FROM SOURCE_TYPE WHERE 1=1";
        if (!$bAll)
            $sqlstr .= " and ID != -1";
        if ($bAccess)
            $sqlstr .= " and ID in ( select source_type_id from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
        $sqlstr .= " ORDER BY ID ";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_stype'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_stype'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_stype']);
        }

        return $nrows;
    }

    static function GetServices($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL, $bAccess = FALSE)
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, NAME, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') DELETED FROM SERVICES WHERE ID != ". SERVICE_NOT;
        else $sqlstr = "SELECT ID, NAME, DELETED FROM SERVICES WHERE ID != ". SERVICE_NOT;
        if (!$bAll)
            $sqlstr .= " and ID != " . SERVICE_ALL;
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if ($bAccess)
            $sqlstr .= " and ID in (SELECT service_id FROM access_dep WHERE service_id != ".SERVICE_ALL." and
    departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id_med'] . " and DELETED is null))";
        $sqlstr .= " ORDER BY NAME ";
//echo "<br><textarea>".$sqlstr."</textarea>";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_services'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_services'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_services']);
        }

        return $nrows;
    }

    static function GetIstochnik($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL, $bAccess = FALSE)
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, NAME, PRIORITY, DETAIL, IN_DEP, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') DELETED FROM SOURCE_MAN sm WHERE ID != ". SOURCE_NOT;
        else $sqlstr = "SELECT ID, NAME, PRIORITY, DETAIL, DELETED FROM SOURCE_MAN WHERE ID != ". SOURCE_NOT;
        if (!$bAll)
            $sqlstr .= " and ID != " . SOURCE_ALL;
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if ($bAccess)
            $sqlstr .= " and ID in (
        select decode(ad.source_man_id, -1, sm.ID, ad.source_man_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
        /*$sqlstr .= " and ID in (SELECT source_man_id FROM access_dep WHERE source_man_id != ".SOURCE_ALL." and
    departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id_med'] . "))";*/
        $sqlstr .= " ORDER BY PRIORITY, NAME ";
//echo "<br><textarea>".$sqlstr."</textarea>";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_istochnik'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
			oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_istochnik'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_istochnik']);
        }

        return $nrows;
    }

    static function GetSubway($strCity = NULL)
    {
        $sqlstr = "SELECT ID, NAME, CITY FROM SUBWAYS WHERE ID != -1 ";
        if ($strCity)
            $sqlstr .= " and CITY = " . $strCity;
        $sqlstr .= " ORDER BY NAME ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_subway'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_subway'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_subway']);
        }

        return $nrows;
    }

    static function GetSourceAlloc($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL, $bAccess = FALSE)
    {
        $sqlstr = "SELECT saa.ID, saa.SA_DETAIL_ID, saa.SOURCE_AUTO_ID, sa.NAME SANAME, sa.SOURCE_TYPE, sad.NAME as NAME, saa.SERVICE_IDS,
 to_char(saa.Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED FROM SOURCE_AUTO_ALLOC saa
 LEFT JOIN SOURCE_AUTO sa ON sa.ID = saa.SOURCE_AUTO_ID
 LEFT JOIN SOURCE_AUTO_DETAIL sad ON sad.ID = saa.SA_DETAIL_ID
 WHERE 1=1 ";
        if (!$bAll)
            $sqlstr .= " and saa.ID != -1";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if (!$bDeleted)
            $sqlstr .= " and saa.DELETED IS NULL";
        if ($bAccess)
            $sqlstr .= " and saa.SOURCE_AUTO_ID in (
        select decode(ad.source_auto_id, -1, saa.SOURCE_AUTO_ID, ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
        $sqlstr .= " ORDER BY SANAME, NAME ";
        //$sqlstr .= " ORDER BY NAME ";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_source_alloc'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_source_alloc'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_source_alloc']);
        }
        return $nrows;
    }

    static function GetSourceAutoDetail($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL, $bAccess = FALSE)
    {
        if (DB_OCI)
            $sqlstr = "SELECT sad.ID, sad.NAME, to_char(sad.Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED 
 FROM SOURCE_AUTO_DETAIL sad WHERE 1=1";
        else $sqlstr = "SELECT sad.ID, sad.SOURCE_AUTO_ID, sad.NAME, sad.SERVICE_IDS, sad.Deleted AS DELETED 
 FROM SOURCE_AUTO_DETAIL sad, SOURCE_AUTO sa WHERE sa.ID = sad.SOURCE_AUTO_ID";
        if (!$bAll)
            $sqlstr .= " and sad.ID != -1";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if (!$bDeleted)
            $sqlstr .= " and sad.DELETED IS NULL";
        /*if ($bAccess)
            $sqlstr .= " and sad.SOURCE_AUTO_ID in (
        select decode(ad.source_auto_id, -1, sad.SOURCE_AUTO_ID, ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";*/
        $sqlstr .= " ORDER BY sad.NAME ";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_sa_detail'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_sa_detail'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_sa_detail']);
        }
        return $nrows;
    }

    static function GetSourceAuto($strWhere = NULL, $BNumber = NULL, $bAccess = FALSE, $key = 'Name', $sort = 'asc')
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, BNUMBER, NAME, SOURCE_TYPE, SERVICE_ID, CITY_ID, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED 
FROM SOURCE_AUTO sa WHERE ID != -1";
        else $sqlstr = "SELECT ID, BNUMBER, NAME, SOURCE_TYPE, SERVICE_ID, CITY_ID, DELETED FROM SOURCE_AUTO WHERE ID != -1";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if ($BNumber)
            $sqlstr .= " and BNUMBER LIKE '".$BNumber."' ";
        if ($bAccess)
            $sqlstr .= " and sa.ID in (
        select decode(ad.source_auto_id, -1, sa.ID, ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
        /*$sqlstr .= " and ID in (SELECT source_auto_id FROM access_dep WHERE source_auto_id != ".SOURCE_ALL." and
    departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id_med'] . "))";*/
        //$sqlstr .= " ORDER BY SOURCE_TYPE, NAME ";
        $sqlstr .= " ORDER BY ".$key." ".$sort;
//var_dump($sqlstr);
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            if ($BNumber) {
                $_POST['array_source_auto'] = OCI_Fetch_Row($query);
				$nrows = 1;
			}
            else {
                $nrows = OCI_Fetch_All($query, $_POST['array_source_auto'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
			}
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            if ($BNumber) {
                $_POST['array_source_auto'] = mysqli_fetch_row($query);
				$nrows = 1;
			}
            else {
                $_POST['array_source_auto'] = mysqli_fetch_all($query);
                $nrows = count($_POST['array_source_auto']);
            }
        }
        return $nrows;
    }

    static function GetSourceDetail($bDeleted = TRUE, $strWhere = NULL, $Istochnik = 0)
    {
        $IstochnikId = intval($Istochnik);
        //$sqlstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id = ".$IstochnikId." AND " . $strWhere;
        $sqlstr = "SELECT ID, NAME, SOURCE_MAN_ID FROM SOURCE_MAN_DETAIL WHERE 1=1";
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if ($IstochnikId != 0)
            $sqlstr .= " and SOURCE_MAN_ID = " . $IstochnikId;
        $sqlstr .= " ORDER BY NAME";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_details'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            $query = mysqli_query(self::GetConnect(), $sqlstr);
            if ($query == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_details'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_details']);
        }

        return $nrows;
    }

    static function GetDepartments($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL)
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, NAME, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') DELETED FROM DEPARTAMENTS WHERE 1=1";
        else $sqlstr = "SELECT ID, NAME, DELETED FROM DEPARTAMENTS WHERE 1=1";
        if (!$bAll)
            $sqlstr .= " and ID != -1";
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        $sqlstr .= " ORDER BY NAME ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_dep'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_dep'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_dep']);
        }

        return $nrows;
    }

    static function GetUsers($bAll = FALSE, $bDeleted = TRUE, $strWhere = NULL, $strOrder = "FIO")
    {
        $sqlstr = "SELECT usr.ID, usr.FIO, usr.ROLE_ID, rls.NAME as ROLE, usr.LOGIN, usr.PASSWORD, usr.EMAIL,
        usr.PIN, usr.SMTP_SERVER, usr.SMTP_PORT, usr.SMTP_FROM, usr.SMTP_LOGIN, usr.SMTP_PASS, usr.DATA_ACC,
        to_char(usr.ACTIVITY,'dd.mm.yyyy hh24:mi:ss') ACTIVITY";
        if (DB_OCI)
            $sqlstr .= ", to_char(usr.DELETED,'dd.mm.yyyy hh24:mi:ss') DELETED";
        else $sqlstr .= ", DELETED";
        $sqlstr .= " FROM USERS usr, ROLES rls WHERE usr.ROLE_ID = rls.ID";
        // WHERE usr.ID != -1 ORDER BY ROLE_ID, FIO";
        if (!$bAll)
            $sqlstr .= " and usr.ID != -1";
        if (!$bDeleted)
            $sqlstr .= " and usr.DELETED IS NULL";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        $sqlstr .= " ORDER BY ". $strOrder;

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_user'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_user'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_user']);
        }
        return $nrows;
    }

    static function GetUserDuty($userId = NULL, $commandId = NULL)
    {
        $UserId = -1;
        if (NULL != $commandId) { // получаем последнего оператора, назначенного дежурным
            $sqlstr = "SELECT USER_ID FROM CALL_DUTY WHERE DATE_CANCEL IS NULL and COMMAND_ID = " . $commandId;
        }
        elseif (NULL != $userId) { // проверяем является ли оператор дежурным
            $sqlstr = "SELECT USER_ID FROM CALL_DUTY WHERE DATE_CANCEL IS NULL and USER_ID = ".$userId;
        }
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            if (OCIExecute($query) && FALSE != ($objResult = OCI_Fetch_Row($query)) && $objResult[0] != 0)
                $UserId = $objResult[0];
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
        }
        return $UserId;
    }

    static function SetUserDuty($userId, $commandId)
    {
        $sqlstr = "UPDATE CALL_DUTY SET DATE_CANCEL = sysdate WHERE COMMAND_ID = ".$commandId." and DATE_CANCEL IS NULL";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
        }
        $sqlstr = "INSERT INTO CALL_DUTY (USER_ID, DATE_DUTY, COMMAND_ID) VALUES (".$userId.", sysdate,".$commandId.")";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            OCICommit(self::GetConnect());
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
        }
        return;
    }

    static function GetUsersDep($bDeleted = TRUE, $strWhere = NULL, $income = NULL, $bActive = 'not')
    {
        $selectstr = "SELECT DISTINCT usr.ID, usr.FIO, usr.ROLE_ID, usr.EMAIL, usr.PIN";
        if ('mix' == $bActive)
            $selectstr .= ", case when ACTIVITY is NULL then 2 else 1 end ACTIVE";
        $selectstr .= " FROM USERS usr, USER_DEP_ALLOC uda WHERE usr.id = uda.user_id";
        if (!$bDeleted)
            $selectstr .= " AND usr.DELETED IS NULL";
        if ($income) {
            $selectstr .= " AND uda.dep_id IN (SELECT distinct departament_id FROM access_dep 
            WHERE (service_id = ".SERVICE_ALL." or service_id = ".$income['serv'].") and 
            (source_man_id = ".SOURCE_ALL." or source_man_id = ".$income['ist'] . ") ) ";
        } else {
            if (USER_ADMIN != $_SESSION['user_role'])
                $selectstr .= " AND uda.dep_id IN (SELECT distinct dep_id FROM user_dep_alloc WHERE DELETED is null and user_id = " . $_SESSION['login_id_med'] . ") ";
        }
        if ($strWhere)
            $selectstr .= " AND " . $strWhere;

        if ('yes' == $bActive)
            $selectstr .= " AND ACTIVITY IS NOT NULL and (sysdate-ACTIVITY)*86400 < 30 AND (ROLE_ID = ".USER_USER." or ROLE_ID = ".USER_SUPER.")";
        elseif ('mix' == $bActive)
            $selectstr .= " AND (ROLE_ID = ".USER_USER." or ROLE_ID = ".USER_SUPER.")";

        if ('mix' == $bActive)
            $selectstr .= " ORDER BY ACTIVE, FIO";
        else $selectstr .= " ORDER BY FIO";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $selectstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_userd'],0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $selectstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_userd'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_userd']);
        }

        return $nrows;
    }

    static function GetSupervisorByService($strWhere = NULL, $serv_id = -1)
    {
        $selectstr = "SELECT DISTINCT usr.ID, usr.FIO, usr.ROLE_ID, usr.EMAIL FROM USERS usr, USER_DEP_ALLOC uda 
WHERE usr.id = uda.user_id AND usr.ROLE_ID = ".USER_SUPER;
        //if (USER_ADMIN != $_SESSION['user_role'])
        $selectstr .= " AND uda.dep_id IN (SELECT departament_id FROM access_dep WHERE service_id = " . $serv_id . ") ";
        if ($strWhere)
            $selectstr .= " AND " . $strWhere;
        $selectstr .= " ORDER BY FIO";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $selectstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_supers'],0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $selectstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_supers'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_supers']);
        }

        return $nrows;
    }

    static function GetRoles()
    {
        $sqlstr = "SELECT ID, NAME FROM ROLES";
        $sqlstr .= " ORDER BY ID";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_roles'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_roles'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_roles']);
        }

        return $nrows;
    }

    static function GetReports($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME FROM CALL_REPORTS WHERE deleted IS NULL and ( role_ids like '%".$_SESSION['user_role']."%'";
        if ($strWhere) $sqlstr .= " and " . $strWhere;
        $sqlstr .= " or ID in (select report_id from CALL_REPORTS_ACC WHERE user_id = ".$_SESSION['login_id_med']."))";
        $sqlstr .= " ORDER BY NAME";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_reports'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $_POST['array_reports'] = mysqli_fetch_all($query);
            $nrows = count($_POST['array_reports']);
        }

        return $nrows;
    }

    static function GetTransferNum($trans_date)
    {
        if (FALSE == DEBUG_MODE)
            $table_name = 'CALL_BASE';
        else $table_name = 'CALL_BASE_TEST';
        $sqlstr = "SELECT MAX(to_number(substr(TRANSFER_NUM, instr(TRANSFER_NUM,'-',1,3)+1,".TRANS_NUM."))) as tr_num 
            FROM ".$table_name." WHERE TRANSFER_NUM like '" . $trans_date ."%'";
        $tr_num = 1000;
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            if (OCIExecute($query) && FALSE != ($objResult = OCI_Fetch_Row($query)) && $objResult[0] != 0) {
                $tr_num = $objResult[0]+1;
            }
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            $objResult = mysqli_fetch_row($query);
            $tr_num = $objResult[0];
        }
        return $tr_num;
    }

    static function UpdateActivity($user_id, $logon, $start_end)
    {
        if (DB_OCI) {
            if (TRUE == $logon) {
                $updatestr = "UPDATE USERS SET ACTIVITY = sysdate, ip_addr='".$_SERVER['REMOTE_ADDR']."' WHERE ID = '{$user_id}'";
            }
            else {
                $updatestr = "UPDATE USERS SET ACTIVITY = NULL, IP_ADDR = NULL WHERE ID = '{$user_id}'";
                self::$bReg = FALSE;
            }
            $query = OCIParse(self::GetConnect(), $updatestr);
            $query_result = OCIExecute($query, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($query);
            if ($start_end && $query_result && FALSE == DEBUG_MODE && USER_ADMIN != $_SESSION['user_role']) {
                $insertstr = "INSERT INTO USERS_ACTIVITY (USER_ID, DELO_ID, DELO_DATE) VALUES (" . $user_id . "," . (TRUE == $logon ? 1 : 0) . ", sysdate)";
                $query = OCIParse(self::GetConnect(), $insertstr);
                OCIExecute($query, OCI_COMMIT_ON_SUCCESS);
                oci_free_statement($query);
            }
        } else {
            if ($logon) {
                $updatestr = "UPDATE USERS SET ACTIVITY = sysdate, ip_addr='".$_SERVER['REMOTE_ADDR']."' WHERE ID = '{$user_id}'";
            }
            else {
                $updatestr = "UPDATE USERS SET ACTIVITY = NULL, IP_ADDR = NULL WHERE ID = '{$user_id}'";
                self::$bReg = FALSE;
            }
            $query_result = mysqli_query(self::GetConnect(), $updatestr);
        }
        if ($query_result) {
            // Actually do nothing
        }

        return;
    }

    /*static function CheckLogin() {
        //if (isset($_SESSION['login_id_med']) && isset($_SESSION['user_role']) && isset($_SESSION['login_name']))
        //    return;
        $result = FALSE;
        self::$bReg = FALSE;
        if (!isset($_POST['login'])) {
            echo "<p style='color: red'>Введите логин.<br /></p>";
        }
        elseif (!isset($_POST['password'])) {
            echo "<p style='color: red'>Введите пароль.<br /></p>";
        }
        else {
            if (DB_OCI) {
                $checkstr = "select ID, ROLE_ID, FIO, case when activity >= sysdate-30/86400 then 'Y' else NULL end ALREADY_LOGINED, IP_ADDR
                          from USERS where LOGIN = '{$_POST['login']}' AND PASSWORD = '{$_POST['password']}'";
                $objParse = OCIParse(self::GetConnect(), $checkstr);
                OCIExecute($objParse);
                $result = OCI_Fetch_Row($objParse);
                oci_free_statement($objParse);
            } else {
                $checkstr = "select ID, ROLE_ID, FIO, case when activity >= sysdate()-30/86400 then 'Y' else NULL end ALREADY_LOGINED, IP_ADDR
                          from USERS where LOGIN = '{$_POST['login']}' AND PASSWORD = '{$_POST['password']}'";
                $query = mysqli_query(self::GetConnect(), $checkstr);
                if (FALSE !== $query) {
                    $result = mysqli_fetch_row($query);
                    if ($result && TRUE == ENCODE_UTF) {
                        $result['2'] = iconv('utf-8', 'windows-1251', $result['2']);
                    }
                }
            }

            //$count = ($result == TRUE ? 1 : 0);
            if ($result) {
                //echo "<p style='color: green'>Корректная пара Логин/Пароль. Открываем страницу...<br /></p>";
                $_SESSION['login_id_med'] = $result['0'];
                $_SESSION['user_role'] = $result['1'];
                $_SESSION['login_name'] = $_POST['login'];
                $_SESSION['ip_addr'] = $result['4'];
                if ($result['3'] == 'Y') {
                    return;
                }
                self::$bReg = TRUE;
                if (USER_ADMIN == $_SESSION['user_role']) {
                    //echo "<p style='color: green'>Админ Логин.<br /></p>";
                    $_SESSION['admin_med'] = 1;
                }
                else $_SESSION['admin_med'] = 0;
				
                setcookie('login', $result['2'], mktime(0,0,0,1,1,2030));
                if (USER_ADMIN == $result['1']) {
                    $tmpstr = "Ввод справочников (Админ)";
                } elseif (USER_SUPER == $result['1']) {
                    $tmpstr = "Медицина (Супервайзер)";
                } elseif (USER_VIEW == $result['1']) {
                    $tmpstr = "Медицина (Обозреватель)";
                } else {
                    $tmpstr = "Входящие звонки (оператор)";
                }
                setcookie('business', $tmpstr, mktime(0,0,0,1,1,2030));

                self::UpdateActivity($_SESSION['login_id_med'], TRUE);
            }
            else {
                self::$bReg = FALSE;
                $_SESSION['admin_med'] = 0;
                echo "<p style='color: red'>Некорректная пара Логин/Пароль<br /></p>";
            }
        }
        return;
    }

    static function is_registered () {
        return self::$bReg;
    }*/
}

/* if (isset($_POST['Enter'])) {
    //if (isset($_SESSION['login_id_med']))
    //    session_destroy();
    GetData::CheckLogin();
}*/
