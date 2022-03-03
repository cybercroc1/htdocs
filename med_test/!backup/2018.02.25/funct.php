<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<?php
include 'base.php';

abstract class GetData
{
    static $conn = NULL, $dbase = NULL;//, $query;
    static $bReg = FALSE;

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

    static function GetMedStatus()
    {
        $sqlstr = "SELECT ID, NAME FROM MED_STATUS ORDER BY ID";
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

    static function GetHospitals($strWhere = NULL)
    {
        /*$sqlstr = "SELECT hosp.ID, hosp.CITY, hosp.NAME as NAME, SERVICE_ID, serv.NAME as SERVICE
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID";*/
        $sqlstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ";
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
        $sqlstr = "SELECT to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET, STATUS_ID, stat.NAME, USER_ID, OPERATOR || usr.FIO as FIO, COMMENTS 
                    FROM CALL_BASE_HIST hist
                    LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
                    LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID
                    ";
        if ($base_id != 0)
            $sqlstr .= " WHERE BASE_ID = " . $base_id;
        $sqlstr .= " ORDER BY DATE_DET";
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

    static function GetThemes($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME, TARGET FROM CALL_THEME ";
        if ($strWhere)
            $sqlstr .= " WHERE " . $strWhere;
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

    static function GetServices($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME FROM SERVICES WHERE ID != -1 ";
        if ($strWhere)
            $sqlstr .= " AND " . $strWhere;
        $sqlstr .= " ORDER BY NAME ";

        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $sqlstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_services'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
            $array_details = $_POST['array_services'];
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

    static function GetIstochnik($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME, DETAIL FROM SOURCE_MAN WHERE ID != -1 ";
        if ($strWhere)
            $sqlstr .= " AND " . $strWhere;
        $sqlstr .= " ORDER BY PRIORITY, NAME ";

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
            $sqlstr .= " AND CITY = " . $strCity;
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

    static function GetSourceAuto($strWhere = NULL, $BNumber = NULL)
    {
        $sqlstr = "SELECT ID, NAME FROM SOURCE_AUTO WHERE ID != -1 ";
        if ($strWhere)
            $sqlstr .= " AND " . $strWhere;
        if ($BNumber)
            $sqlstr .= " AND bnumber LIKE " . $BNumber;
        $sqlstr .= " ORDER BY NAME ";

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

    static function GetSourceDetail($strWhere = NULL, $Istochnik = 0)
    {
        $IstochnikId = intval($Istochnik);
        //$sqlstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id = ".$IstochnikId." AND " . $strWhere;
        $sqlstr = "SELECT ID, NAME, SOURCE_MAN_ID FROM SOURCE_MAN_DETAIL ";
        if ($strWhere)
            $sqlstr .= " WHERE " . $strWhere;
        if ($IstochnikId != 0)
            $sqlstr .= " AND source_man_id = " . $IstochnikId;
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

    static function GetDepartaments($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, NAME FROM DEPARTAMENTS WHERE ID != -1 ";
        if ($strWhere)
            $sqlstr .= " AND " . $strWhere;
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

    static function GetUsers($strWhere = NULL)
    {
        $sqlstr = "SELECT ID, FIO, ROLE_ID FROM USERS ";
        if ($strWhere)
            $sqlstr .= " WHERE " . $strWhere;
        $sqlstr .= " ORDER BY FIO ";

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

    static function GetUsersDep($strWhere = " TRUE ", $dep_id = -1)
    {
        $selectstr = "SELECT DISTINCT usr.ID, usr.FIO, usr.ROLE_ID FROM USERS usr, USER_DEP_ALLOC uda
                WHERE " . $strWhere . " AND usr.id = uda.user_id ";
        if (USER_ADMIN != $_SESSION['user_role'])
            $selectstr .= " AND uda.dep_id IN (SELECT dep_id FROM user_dep_alloc WHERE user_id = " . $_SESSION['login_id'] . ") ";
        $selectstr .= " ORDER BY FIO";
//echo "<br/><textarea>".$selectstr."</textarea>";
        if (DB_OCI) {
            $query = OCIParse(self::GetConnect(), $selectstr);
            OCIExecute($query, OCI_DEFAULT);
            $nrows = OCI_Fetch_All($query, $_POST['array_userd'], 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
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

    static function GetRoles()
    {
        $sqlstr = "SELECT ID, NAME FROM ROLES ORDER BY ID";
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

    static function UpdateActivity($user_id, $logon)
    {
        $updatestr = "UPDATE USERS SET ACTIVITY = NULL WHERE ID = '{$user_id}'";
        if (DB_OCI) {
            if (TRUE == $logon)
                $updatestr = "UPDATE USERS SET ACTIVITY = to_date('" . date("d-m-Y  H:i:s") . "','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$user_id}'";
//echo $updatestr;
            $query = OCIParse(self::GetConnect(), $updatestr);
            $query_result = OCIExecute($query, OCI_COMMIT_ON_SUCCESS);
            oci_free_statement($query);
        } else {
            if ($logon)
                $updatestr = "UPDATE USERS SET ACTIVITY = '" . date("Y-m-d H:i:s") . "' WHERE ID = '{$user_id}'";
            $query_result = mysqli_query(self::GetConnect(), $updatestr);
        }
        if ($query_result) {
            // Actually do nothing
            //echo $updatestr;
            //var_dump($updatestr);
        }
        return;
    }

    static function CheckLogin() {
        $result = FALSE;
        if (!isset($_POST['login'])) {
            echo "<p style='color: red'>Введите логин.<br /></p>";
        }
        elseif (!isset($_POST['password'])) {
            echo "<p style='color: red'>Введите пароль.<br /></p>";
        }
        else {
            $checkstr = "SELECT ID, ROLE_ID, FIO FROM USERS WHERE LOGIN LIKE '{$_POST['login']}' AND PASSWORD LIKE '{$_POST['password']}'";
            if (DB_OCI) {
                $objParse = OCIParse(self::GetConnect(), $checkstr);
                OCIExecute($objParse);
                $result = OCI_Fetch_Row($objParse);
                oci_free_statement($objParse);
            } else {
                $query = mysqli_query(self::GetConnect(), $checkstr);
                if (FALSE !== $query) {
                    $result = mysqli_fetch_row($query);
                }
            }

            //$count = ($result == TRUE ? 1 : 0);
            if ($result) {
                //echo "<p style='color: green'>Корректная пара Логин/Пароль. Открываем страницу...<br /></p>";
                self::$bReg = TRUE;
                $_SESSION['login_id'] = $result['0'];
                $_SESSION['user_role'] = $result['1'];
                $_SESSION['login_name'] = $_POST['login'];
                if (USER_ADMIN == $_SESSION['user_role']) {
                    //echo "<p style='color: green'>Админ Логин.<br /></p>";
                    $_SESSION['admin'] = 1;
                }
                else $_SESSION['admin'] = 0;
				
                setcookie('login', $result['2'], mktime(0,0,0,1,1,2030));
                if (USER_ADMIN == $result['1']) {
                    $tmpstr = "Ввод справочников (Админ)";
                } else if (USER_SUPER == $result['1']) {
                    $tmpstr = "Медицина (Супервайзер)";
                } else if (USER_VIEW == $result['1']) {
                    $tmpstr = "Медицина (Обозреватель)";
                } else {
                    $tmpstr = "Входящие звонки (оператор)";
                }
                setcookie('business', $tmpstr, mktime(0,0,0,1,1,2030));

                self::UpdateActivity($_SESSION['login_id'], TRUE);
            }
            else {
                self::$bReg = FALSE;
                $_SESSION['admin'] = 0;
                echo "<p style='color: red'>Некорректная пара Логин/Пароль<br /></p>";
            }
        }
        return;
    }

    static function is_registered () {
        return self::$bReg;
    }
}

    if (isset($_POST['Enter'])) {
        //if (isset($_SESSION['login_id']))
        //    session_destroy();
        GetData::CheckLogin();
    }
