<?php
/*if (preg_match('/(?i)msie [1-9]/',$_SERVER['HTTP_USER_AGENT']))
    echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>';
else*/

if(!isset($Export_but) and !isset($Export_xlsx)) { //длЯ экспорта в файл цеплять jquery не нужно
	//if (file_exists("./js/jquery.min.js")) {
    if ('localhost' != $_SERVER['HTTP_HOST'] && '127.0.0.1' != $_SERVER['HTTP_HOST'])
        $query_path = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST']."/js/jquery.min.js"; //.$_SERVER['SCRIPT_NAME'];
    else {
        if('127.0.0.1' == $_SERVER['HTTP_HOST'] || 'localhost' == $_SERVER['HTTP_HOST'])
            $query_path = "http://127.0.0.1/med/js/jquery.min.js";
        else $query_path = "http://med.wilstream.ru/js/jquery.min.js";
    }
    //var_dump($query_path);
		echo "<script type='text/javascript'>
			if(!window.jQuery) { 
				document.write(unescape('<script src=\"".$query_path."\">%3C/script%3E'));
			}</script>";
	/*
	 * 				document.write(unescape('<script src="http://med.wilstream.ru/js/jquery.min.js">%3C/script%3E'));
} else {
		echo 'External loading of jquery: ';
		echo './js/jquery.min.js';
		echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
	}*/
}
include_once 'base.php';

function u8($text) {return iconv('CP1251','UTF-8',$text);}
function cp($text) {return iconv('UTF-8','CP1251',$text);}
//function suc() {return in_array($_SESSION['login_id_med'], $this->arr_sec_chance);}

abstract class GetData
{
    static $conn = NULL, $dbase = NULL;
    static $bReg = FALSE;

    static $array_dep = array();
    static $array_user = array();
    static $array_userd = array();
    static $array_userdeps = array();
    static $array_roles = array();
    static $array_supers = array();
	static $array_stype = array();
	static $array_ctype = array();
	static $array_theme = array();
	static $array_status = array();
	static $array_status_det = array();
	static $array_hospitals = array();
	static $array_cities = array();
	static $array_subway = array();
	static $array_hist = array();
	static $array_wc = array();
	static $array_services = array();
	static $arr_service_det = array();
	static $array_istochnik = array();
	static $array_details = array();
	static $array_sa_detail = array();
	static $array_source_auto = array();
	static $array_source_alloc = array();
	static $array_reports = array();
	static $array_oktell_hist = array();
	static $array_access = array();
	static $arr_sec_chance = array();
	static $array_providers = array();
	static $arr_provider_pay = array();
	static $arr_provider_get = array();
	static $arr_provider_balance = array();
	static $arr_call_base = array();

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

    static function my_log($string, $bError = FALSE)
    {
        $now_name = date("Y-m-d");
        $now = date("Y-m-d H:i:s");
        $dir_name = $_SERVER['DOCUMENT_ROOT'].PATH."/logs/".substr($now_name,0,7);
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

    static function GetAccess($UserID = 0)
    {
        $checkstr = "select DATA_ACC from USERS where DELETED is NULL and ID = '{$UserID}'";
        $query = OCIParse(self::GetConnect(), $checkstr);
        OCIExecute($query);
        self::$array_access = OCI_Fetch_Row($query);
        $_SESSION['data_acc'] = self::$array_access[0];
        oci_free_statement($query);
        $nrows = count(self::$array_access);
        return $nrows;
    }

    static function GetSecondChance()
    {
        $checkstr = "select USER_ID from USER_DEP_ALLOC where DEP_ID = ".SEC_CHANCE_CALL." and DELETED is NULL and 
        USER_ID in (select ID FROM USERS WHERE ROLE_ID = ".USER_USER.") ORDER BY USER_ID";
        $query = OCIParse(self::GetConnect(), $checkstr);
        OCIExecute($query);
        $nrows = OCI_Fetch_All($query, self::$arr_sec_chance,0,-1,OCI_NUM);
        self::$arr_sec_chance = self::$arr_sec_chance[0];
        oci_free_statement($query);
        return $nrows;
    }

    static function GetMedStatus($strWhere = NULL, $bAll = FALSE, $bDeleted = TRUE)
    {
        $sqlstr = "SELECT ID, NAME, COLOR FROM MED_STATUS ";
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
            $nrows = OCI_Fetch_All($query, self::$array_status,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            //$_POST['array_status'] = self::$array_status;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_status = mysqli_fetch_all($query);
            $nrows = count(self::$array_status);
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
            $nrows = OCI_Fetch_All($query,self::$array_status_det,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            //$_POST['array_status_det'] = self::$array_status_det;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_status_det = mysqli_fetch_all($query);
            $nrows = count(self::$array_status_det);
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
            $nrows = OCI_Fetch_All($query,self::$array_hospitals,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_hospitals'] = self::$array_hospitals;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_hospitals = mysqli_fetch_all($query);
            $nrows = count(self::$array_hospitals);
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

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_hist,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_hist'] = self::$array_hist;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_hist = mysqli_fetch_all($query);
            $nrows = count(self::$array_hist);
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
to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi') CLIENT_DATE, hosp.NAME as HOSPITAL_NAME, hosp.CITY FROM ".$table_name_cl;
        else $sqlstr = "SELECT HOSPITAL_ID, CLIENT_NAME, AGE, CLIENT_PHONE, CLIENT_STATUS, 
DATE_FORMAT(CLIENT_DATE,'%d.%m.%Y %H:%i') as CLIENT_DATE FROM ".$table_name_cl;
        $sqlstr .= " cbc LEFT JOIN HOSPITALS hosp ON hosp.ID = cbc.HOSPITAL_ID";
        if ($base_id != 0)
            $sqlstr .= " WHERE BASE_ID = " . $base_id;
        $sqlstr .= " ORDER BY CLIENT_DATE";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_wc,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_wc'] = self::$array_wc;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_wc = mysqli_fetch_all($query);
            $nrows = count(self::$array_wc);
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
			$nrows = OCI_Fetch_All($query,self::$array_theme,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_theme'] = self::$array_theme;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_theme = mysqli_fetch_all($query);
            $nrows = count(self::$array_theme);
        }

        return $nrows;
    }

    static function GetCallType()
    {
        $sqlstr = "SELECT ID, NAME FROM CALL_TYPE WHERE ID != -1 ORDER BY ID";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, self::$array_ctype, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_ctype'] = self::$array_ctype;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_ctype = mysqli_fetch_all($query);
            $nrows = count(self::$array_ctype);
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
            $nrows = OCI_Fetch_All($query,self::$array_stype,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_stype'] = self::$array_stype;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_stype = mysqli_fetch_all($query);
            $nrows = count(self::$array_stype);
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
            $nrows = OCI_Fetch_All($query,self::$array_services,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_services'] = self::$array_services;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_services = mysqli_fetch_all($query);
            $nrows = count(self::$array_services);
        }

        return $nrows;
    }
    static function GetServiceDetails($strID = NULL, $iService = SERVICE_NOT, $bDeleted = TRUE, $bAccess = FALSE)
    {
        if (DB_OCI)
            $sqlstr = "SELECT ID, SERVICE_ID, NAME, to_char(Deleted,'dd.mm.yyyy hh24:mi:ss') DELETED FROM SERVICE_DET d1 WHERE 1=1";
        else $sqlstr = "SELECT ID, SERVICE_ID, NAME, DELETED FROM SERVICE_DET WHERE 1=1";
        if (NULL != $strID)
            $sqlstr .= " and ID in (" . $strID . ")";
        if (SERVICE_NOT != $iService)
            $sqlstr .= " and SERVICE_ID = " . $iService;
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        if ($bAccess)
            $sqlstr .= " and SERVICE_ID in (SELECT decode(d2.service_id,-1,d1.service_id,d2.service_id) FROM access_dep d2 WHERE
			departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id_med'] . " and DELETED is null))";
			//$sqlstr .= " and SERVICE_ID in (SELECT service_id FROM access_dep WHERE service_id != ".SERVICE_ALL." and
			//departament_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id_med'] . " and DELETED is null))";
			$sqlstr .= " ORDER BY SERVICE_ID,NAME ";
//echo "<br><textarea>".$sqlstr."</textarea>";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$arr_service_det,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$arr_service_det= mysqli_fetch_all($query);
            $nrows = count(self::$arr_service_det);
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
            $nrows = OCI_Fetch_All($query,self::$array_istochnik,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_istochnik'] = self::$array_istochnik;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_istochnik = mysqli_fetch_all($query);
            $nrows = count(self::$array_istochnik);
        }

        return $nrows;
    }

    static function GetCities($bAll = FALSE, $bDeleted = TRUE)
    {
        $sqlstr = "SELECT ID, CITY FROM CITIES WHERE 1 = 1 ";
        if (!$bAll)
            $sqlstr .= " and ID != -1";
        if (!$bDeleted)
            $sqlstr .= " and DELETED IS NULL";
        $sqlstr .= " ORDER BY CITY ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_cities,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_subway = mysqli_fetch_all($query);
            $nrows = count(self::$array_subway);
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
            $nrows = OCI_Fetch_All($query,self::$array_subway,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_subway = mysqli_fetch_all($query);
            $nrows = count(self::$array_subway);
        }

        return $nrows;
    }

    static function GetProviders($bDeleted = TRUE)
    {
        $sqlstr = "SELECT sup.ID, SUP_NAME, BALANCE, to_char(sup.DELETED,'dd.mm.yyyy hh24:mi:ss') AS DELETED 
FROM SUPPLIERS sup WHERE 1=1";
        if (!$bDeleted)
            $sqlstr .= " and sup.DELETED IS NULL";
        $sqlstr .= " ORDER BY SUP_NAME ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_providers,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_providers = mysqli_fetch_all($query);
            $nrows = count(self::$array_providers);
        }
        return $nrows;
    }

    static function GetProviderBalance($supplierid = 0)
    {
        /*$sqlstr = "SELECT SUP_NAME, sb.SUPPLIER_ID, sb.SOURCE_ID, sa.SOURCE_TYPE, sa.NAME, to_char(sb.DATE_ADD,'dd.mm.yyyy hh24:mi:ss') AS DATE_ADD, RUB
FROM SUPPLIER_BALANCE sb
LEFT JOIN SUPPLIERS sup ON sup.ID = sb.SUPPLIER_ID
LEFT JOIN SOURCE_AUTO sa ON sa.ID = sb.SOURCE_ID
WHERE 1=1";*/
        $sqlstr = "SELECT SUP_NAME, sa.SUPPLIER_ID, sa.ID as SOURCE_ID, sa.SOURCE_TYPE, sa.NAME, 
to_char(sb.DATE_ADD,'dd.mm.yyyy hh24:mi:ss') AS DATE_ADD, sb.RUB, sb.COMMENTS, sa.deleted 
FROM SOURCE_AUTO sa
LEFT JOIN SUPPLIERS sup ON sup.ID = sa.SUPPLIER_ID
LEFT JOIN SUPPLIER_BALANCE sb ON sa.ID = sb.SOURCE_ID
WHERE sa.ID > 0";
        if (0 != $supplierid)
            $sqlstr .= " and sup.ID = ".$supplierid;
            //$sqlstr .= " and sb.SUPPLIER_ID = ".$supplierid;
        $sqlstr .= " ORDER BY SUP_NAME, sa.NAME, sa.SOURCE_TYPE, sb.DATE_ADD ";
        //var_dump($sqlstr);

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$arr_provider_balance,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$arr_provider_balance = mysqli_fetch_all($query);
            $nrows = count(self::$arr_provider_balance);
        }
        //var_dump(self::$arr_provider_balance);
        return $nrows;
    }

    static function GetProviderPays($sa_id = 0, $bsum = FALSE, $date_from = NULL, $date_to = NULL)
    {
        if ($bsum)
            $sqlstr = "SELECT cb.SOURCE_AUTO_ID, SUM(PAY_SUPPLIER) as ITOG FROM CALL_BASE cb WHERE 1=1";
        else $sqlstr = "SELECT cb.ID, cb.SOURCE_AUTO_ID, PAY_SUPPLIER FROM CALL_BASE cb WHERE 1=1";
//   sa.NAME,     LEFT JOIN SOURCE_AUTO sa ON sa.ID = cb.SOURCE_AUTO_ID
        if (0 != $sa_id)
            $sqlstr .= " and cb.SOURCE_AUTO_ID = ".$sa_id;

// " and (cb.DATE_CALL between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        if ($date_from)
            $sqlstr .= " and cb.DATE_CALL >= to_date('".$date_from."','DD.MM.YYYY')";
        if ($date_to)
            $sqlstr .= " and cb.DATE_CALL < to_date('".$date_to."','DD.MM.YYYY')+1";
        if ($bsum) {
            $sqlstr .= " GROUP BY cb.SOURCE_AUTO_ID";
            $sqlstr .= " ORDER BY cb.SOURCE_AUTO_ID";
        }
        else { $sqlstr .= " ORDER BY cb.SOURCE_AUTO_ID, cb.ID "; }

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$arr_provider_pay,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$arr_provider_pay = mysqli_fetch_all($query);
            $nrows = count(self::$arr_provider_pay);
        }
        return $nrows;
    }

    static function GetProviderCommis($sup_id = 0, $bsum = FALSE, $date_from = NULL, $date_to = NULL)
    {
        if ($bsum)
            $sqlstr = "SELECT SUP_NAME, SUM(RUB_GET) as ITOG FROM SUPPLIER_COMMIS sc ";
        else $sqlstr = "SELECT SUP_NAME, sa.ID as SOURCE_ID, sa.NAME as SOURCE_NAME, SOURCE_TYPE, to_char(DATE_GET,'dd.mm.yyyy') AS DATE_GETT, RUB_GET FROM SUPPLIER_COMMIS sc ";
        $sqlstr .= " LEFT JOIN SUPPLIERS sup ON sup.ID = sc.SUPPLIER_ID ";
        $sqlstr .= " LEFT JOIN SOURCE_AUTO sa ON sa.ID = sc.SOURCE_ID";
        $sqlstr .= " WHERE 1=1";
        if (0 != $sup_id)
            $sqlstr .= " and sc.SUPPLIER_ID = ".$sup_id;

        if ($date_from)
            $sqlstr .= " and DATE_GET >= to_date('".$date_from."','DD.MM.YYYY')";
        if ($date_to)
            $sqlstr .= " and DATE_GET < to_date('".$date_to."','DD.MM.YYYY')+1";
        if ($bsum) {
            $sqlstr .= " GROUP BY SUP_NAME";
            $sqlstr .= " ORDER BY SUP_NAME";
        }
        else { $sqlstr .= " ORDER BY SUP_NAME, SOURCE_NAME, DATE_GET "; }
//var_dump($sqlstr);
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$arr_provider_get,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$arr_provider_get = mysqli_fetch_all($query);
            $nrows = count(self::$arr_provider_get);
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
            $nrows = OCI_Fetch_All($query,self::$array_source_alloc,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_source_alloc = mysqli_fetch_all($query);
            $nrows = count(self::$array_source_alloc);
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
            $nrows = OCI_Fetch_All($query,self::$array_sa_detail,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_sa_detail = mysqli_fetch_all($query);
            $nrows = count(self::$array_sa_detail);
        }
        return $nrows;
    }

    static function GetSourceAuto($strWhere = NULL, $BNumber = NULL, $bAccess = FALSE, $key = 'Name', $sort = 'asc', $bSuppl = FALSE, $bDeleted = TRUE)
    {
        if (DB_OCI)
            $sqlstr = "SELECT sa.ID, BNUMBER, sa.NAME, SUPPLIER_ID, SOURCE_TYPE, SERVICE_ID, CITY_ID, to_char(sa.Deleted,'dd.mm.yyyy hh24:mi:ss') AS DELETED ";
        else $sqlstr = "SELECT sa.ID, BNUMBER, sa.NAME, SUPPLIER_ID, SOURCE_TYPE, SERVICE_ID, CITY_ID, sa.DELETED ";
        if ($bSuppl)
            $sqlstr .= ", SUP_NAME ";
        $sqlstr .= "FROM SOURCE_AUTO sa  ";
        if ($bSuppl)
            $sqlstr .= " LEFT JOIN SUPPLIERS sup ON sup.ID = sa.SUPPLIER_ID ";
        $sqlstr .= " WHERE sa.ID != -1 ";
        if ($strWhere)
            $sqlstr .= " and " . $strWhere;
        if (!$bDeleted)
            $sqlstr .= " and sa.DELETED IS NULL";
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

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            if ($BNumber) {
                self::$array_source_auto = OCI_Fetch_Assoc($query);
                if (self::$array_source_auto) {
                    $nrows = count(self::$array_source_auto);
                }
                else $nrows = 0;
			}
            else {
                $nrows = OCI_Fetch_All($query,self::$array_source_auto,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
			}
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            if ($BNumber) {
                self::$array_source_auto = mysqli_fetch_row($query);
                $nrows = count(self::$array_source_auto);
			}
            else {
                self::$array_source_auto = mysqli_fetch_all($query);
                $nrows = count(self::$array_source_auto);
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
            $nrows = OCI_Fetch_All($query,self::$array_details,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_details'] = self::$array_details;
            oci_free_statement($query);
        } else {
            $query = mysqli_query(self::GetConnect(), $sqlstr);
            if ($query == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_details = mysqli_fetch_all($query);
            $nrows = count(self::$array_details);
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
            $nrows = OCI_Fetch_All($query,self::$array_dep,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_dep'] = self::$array_dep;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_dep = mysqli_fetch_all($query);
            $nrows = count(self::$array_dep);
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
            $nrows = OCI_Fetch_All($query,self::$array_user,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_user'] = self::$array_user;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_user = mysqli_fetch_all($query);
            $nrows = count(self::$array_user);
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
        if ($userId != -1) {
            $sqlstr = "INSERT INTO CALL_DUTY (USER_ID, DATE_DUTY, COMMAND_ID) VALUES (" . $userId . ", sysdate," . $commandId . ")";
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
        }
        return;
    }

    static function SetUsersDep($MyDepId, $commandId)
    {
        // все департаменты в обычный режим
        $sqlstr = "UPDATE USER_DEP_ALLOC SET PRIORITY = 1 WHERE USER_ID = ".$commandId;//." and DELETED IS NULL";
        self::my_log($sqlstr,FALSE);
        $query = OCIParse(self::GetConnect(), $sqlstr);
        OCIExecute($query, OCI_DEFAULT);
        // один департамент главный
        $sqlstr = "UPDATE USER_DEP_ALLOC SET PRIORITY = 0 WHERE USER_ID = ".$commandId." and DEP_ID = ".$MyDepId." and DELETED IS NULL";
        self::my_log($sqlstr,FALSE);
        $query = OCIParse(self::GetConnect(), $sqlstr);
        OCIExecute($query, OCI_DEFAULT);

        OCICommit(self::GetConnect());
        oci_free_statement($query);
        return;
    }

    static function GetUserDeps($userID = -1, $bDeleted = TRUE, $strWhere = NULL)
    {
        $selectstr = "SELECT dep.ID, dep.NAME, uda.PRIORITY FROM DEPARTAMENTS dep, USER_DEP_ALLOC uda";
        $selectstr .= " WHERE dep.id = uda.dep_id AND uda.user_id = ".$userID." and uda.DELETED is null";
        $selectstr .= " ORDER BY dep.NAME";

        $query = OCIParse(self::GetConnect(), $selectstr);
        OCIExecute($query, OCI_DEFAULT);
        $nrows = OCI_Fetch_All($query,self::$array_userdeps,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($query);

        return $nrows;
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
        // исключаем спецпользователей и вторичных стоматологов
        $selectstr .= " AND usr.ID != ".SPEC_USER;
        if (!isset($_SESSION['sec_chance_arr'])) { // Если не выходили из программы
            self::GetSecondChance();
            $_SESSION['sec_chance_arr'] = self::$arr_sec_chance;
            $_SESSION['sec_chance'] = in_array($_SESSION['login_id_med'], self::$arr_sec_chance);
        }
        //if ($_SESSION['login_id_med'] != 10 ) // $income['serv'] - надо учесть операторов входа
        //    $selectstr .=  " AND usr.ID not in (".implode(',',$_SESSION['sec_chance_arr']).")";
        //else $selectstr .= " AND usr.ID not in (".implode(',',$_SESSION['sec_chance_arr']).")";

        /*if (isset($_SESSION['login_id_med']))
            $selectstr .= " OR usr.ID = ".$_SESSION['login_id_med'];*/

        if ('mix' == $bActive)
            $selectstr .= " ORDER BY ACTIVE, FIO";
        else $selectstr .= " ORDER BY FIO";
//var_dump($selectstr);
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $selectstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_userd,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $selectstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_userd = mysqli_fetch_all($query);
            $nrows = count(self::$array_userd);
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
            $nrows = OCI_Fetch_All($query,self::$array_supers,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_supers'] = self::$array_supers;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $selectstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_supers = mysqli_fetch_all($query);
            $nrows = count(self::$array_supers);
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
            $nrows = OCI_Fetch_All($query,self::$array_roles,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_roles'] = self::$array_roles;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_roles = mysqli_fetch_all($query);
            $nrows = count(self::$array_roles);
        }

        return $nrows;
    }

    static function GetReports($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME, SCRIPT_NAME FROM CALL_REPORTS WHERE deleted IS NULL and ( role_ids like '%".$_SESSION['user_role']."%'";
        if ($strWhere) $sqlstr .= " and " . $strWhere;
        $sqlstr .= " or ID in (select report_id from CALL_REPORTS_ACC WHERE user_id = ".$_SESSION['login_id_med']."))";
        $sqlstr .= " ORDER BY NAME";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_reports,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            $_POST['array_reports'] = self::$array_reports;
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_reports = mysqli_fetch_all($query);
            $nrows = count(self::$array_reports);
        }

        return $nrows;
    }

    static function GetOktellHistory($base_id = NULL, $strWhere = NULL)
    {
        $sqlstr = "SELECT BASE_ID, START_DATE, OKTELL_SERVER_ID, OKTELL_IDCHAIN, OKTELL_IDUSER, PHONE_PREFIX, PHONE_NUMBER, USER_ID FROM OKTELL_CALL_HIST WHERE 1=1";
        if ($base_id) $sqlstr .= " and BASE_ID = " . $base_id;
        if ($strWhere) $sqlstr .= " and " . $strWhere;
        $sqlstr .= " ORDER BY BASE_ID,START_DATE";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query,self::$array_oktell_hist,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        } else {
            if (($query = mysqli_query(self::GetConnect(), $sqlstr)) == NULL) {
                printf("Errormessage: %s\n", mysqli_error(self::GetConnect()));
            }
            self::$array_oktell_hist = mysqli_fetch_all($query);
            $nrows = count(self::$array_oktell_hist);
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

    static function GetCallByCallId($sc_call_id = '', $oktell_idchain = '')
    {
        $nrows = 0;
        if (FALSE == DEBUG_MODE)
            $table_name = 'CALL_BASE';
        else $table_name = 'CALL_BASE_TEST';
        if ('' != $oktell_idchain)
            $sqlstr = "SELECT * FROM ".$table_name." WHERE OKTELL_IDCHAIN = '" . $oktell_idchain ."' ORDER BY ID DESC";
        elseif ('' != $sc_call_id)
            $sqlstr = "SELECT * FROM ".$table_name." WHERE SC_CALL_ID = " . $sc_call_id ." ORDER BY ID DESC";
        else return $nrows;

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            if (OCIExecute($query) && FALSE != (self::$arr_call_base = OCI_Fetch_Assoc($query))) {
                $nrows = 1;
            }
            //$nrows = OCI_Fetch_Row($query,self::$arr_call_base,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
            oci_free_statement($query);
        }
        return $nrows;
    }

    static function UpdateActivity($user_id, $logon, $start_end)
    {
        if (DB_OCI) {
            if (TRUE == $logon) {
                $updatestr = "UPDATE USERS SET ACTIVITY = sysdate, ip_addr='".$_SERVER['REMOTE_ADDR']."' WHERE ID = '".$user_id."'";
            }
            else {
                $updatestr = "UPDATE USERS SET ACTIVITY = NULL, IP_ADDR = NULL WHERE ID = '".$user_id."'";
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
