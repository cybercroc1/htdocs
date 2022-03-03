<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

session_name('medc');
session_start();
extract($_REQUEST);
/*if ($_SERVER['REQUEST_METHOD'] == "POST"){
    header("location:{$_SERVER['PHP_SELF']}");
}*/
require_once '../funct.php';

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>'; exit();
}
// ----------------------------конфигурация-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail админа
$backurl = "admin_user.php";
//---------------------------------------------------------------------- //
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>Пользователи</title>
    <meta name="description" content="Пользователи">

<?php
//define('ROLES', array('', 'Администратор', 'Супервайзер', 'Обозреватель', 'Оператор'));
$acc_array = array('1'=>'Контакты', '2'=>'Финансы', '4'=>'Слушать');
function pass_gen($number) { 
//функция генерирует случайный пароль из $number символов,
//Паротль гаранртированно содержит одну большую, одну маленькую, одну цифру. Если длина пароль задана меньше 3 символов, то гарантированно содержит 2 варианта.
		
	$arr[0] = array('A','B','C','D','E','F','G','H','J','K','L',
        'M','N','P','R','S','T','U','V','X','Y','Z');
	$arr[1] = array('a','b','c','d','e','f','g','h','i','j','k',
        'm','n','o','p','r','s','t','u','v','x','y','z');
	$arr[2] = array('2','3','4','5','6','7','8','9');
	
    $pass = "";
	$arr_y = array();
    for($i = 0; $i < $number; $i++) {
		
		if((strlen($pass)==$number-2 and count($arr_y)<2) or (strlen($pass)==$number-1 and count($arr_y)<3)) {
				 if(!isset($arr_y[0])) $arr_index=0;
			else if(!isset($arr_y[1])) $arr_index=1;
			else if(!isset($arr_y[2])) $arr_index=2;
		}
		else $arr_index=rand(0, 2);
	
		$arr_y[$arr_index]=$arr_index;
		$index = rand(0, count($arr[$arr_index]) - 1);
        $pass.= $arr[$arr_index][$index];
		
	}
    return $pass;
}

if (isset($save) && isset($data_acc_ids)) {
    foreach($data_acc_ids as $data_acc_id => $fucking_val) {
        if (isset($GLOBALS["on_".$data_acc_id])) { //меняем доступ для пользователя
            $service_ids = implode(",",$GLOBALS["on_".$data_acc_id]);
        }
        else $service_ids='';

        $updatestr = "UPDATE USERS SET DATA_ACC = '".$service_ids."' WHERE ID = ".$data_acc_id;
        if (DB_OCI) {
            $query = OCIParse(GetData::GetConnect(), $updatestr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        } else {
            $query_result = mysqli_query(GetData::GetConnect(), $updatestr);
        }
    }
}
?>
</head>

<body style="margin-top: 0; margin-bottom: 0">
<h3 style="margin-top: 0; margin-bottom: 0">Список пользователей</h3>
<form action='' method='post' style="margin-bottom: 3px;">
    <?php if (strpos($_SERVER["HTTP_USER_AGENT"],"MSIE") !== false ||
            strpos($_SERVER["HTTP_USER_AGENT"],"rv:11.0") !== false) { ?>
    <table class="scrolling-table_uie">
        <?php } else { ?>
    <table class="scrolling-table_u">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px">ID</th>
        <th style="width: 141px">ФИО</th>
        <th style="width: 51px">ПИН</th>
        <th style="width: 96px">Логин</th>
        <th style="width: 81px">Пароль</th>
        <th style="width: 151px">E-Mail</th>
        <th style="width: 91px">Роль</th>
        <th style="width: 161px">Департамент</th>
        <th style="width: 61px">Контакты</th>
        <th style="width: 61px">Финансы</th>
        <th style="width: 61px">Слушать</th>
        <th style="width: 116px">Активность</th>
        <th style="width: 116px">Удалена</th>
        <th style="width: 91px">Действие</th>
    </tr></thead>
    <tbody>
    <?php
    $max_id = 1; // для вставки новой строки, если потребуется
    if (GetData::GetUsers(TRUE, $bDeleted=FALSE, NULL, "ROLE_ID, FIO")) {
        foreach(GetData::$array_user as $key => $value) {
            if (DB_OCI) {
                $id = $value['ID'];
                $str_deleted = ($value['DELETED'] ? $value['DELETED'] : "нет");

                $strfilt = "select Name from departaments where Id in (select dep_id from USER_DEP_ALLOC where user_id = ". $id.")";
                $qu = OCIParse(GetData::GetConnect(), $strfilt);
                OCIExecute($qu, OCI_DEFAULT);
                $arr_dep = oci_fetch_row($qu);
                oci_free_statement($qu);

                if (TRUE == ENCODE_UTF) {
                    $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                    $value['ROLE'] = iconv('windows-1251', 'utf-8', $value['ROLE']);
                    $value['LOGIN'] = iconv('windows-1251', 'utf-8', $value['LOGIN']);
                    $value['PASSWORD'] = iconv('windows-1251', 'utf-8', $value['PASSWORD']);
                }

                $checked_acc_ids = $value['DATA_ACC'];
                $checked_acc_arr = array();
                $checked_acc_arr = explode(',', $checked_acc_ids);
                foreach($checked_acc_arr as $checked_id) {$checked_acc_arr[$checked_id] = $checked_id;}

                echo '<tr><td style="text-align: center; width: 35px">' . $id . '</td>
                        <td style="text-align: left; width: 140px">' . $value['FIO'] . '</td>
                        <td style="text-align: center; width: 50px">' . $value['PIN'] . '</td>
                        <td style="text-align: center; width: 95px">' . $value['LOGIN'] . '</td>
                        <td style="text-align: center; width: 80px">' . $value['PASSWORD'] . '</td>
                        <td style="text-align: center; width: 150px">' . $value['EMAIL'] . '</td>
                        <td style="text-align: center; width: 90px">' . $value['ROLE'] . '</td>
                        <td style="text-align: center; width: 160px">' . $arr_dep[0] . '</td>
                        ';
                foreach($acc_array as $acc_id => $acc_name) {
                    if (in_array($acc_id, $checked_acc_arr)) {
                        $colors = "background-color: springgreen";
                        $checked = " checked";
                    }
                    else {
                        $colors = "background-color: red";
                        $checked = "";
                    }
                    echo "<td style='text-align: center; width: 60px;".$colors."'><input type=hidden name=data_acc_ids[".$id."]><input type='checkbox' name='on_".$id."[]' value='".$acc_id."'".$checked."/></td>";
                }
                echo '<td style="text-align: center; width: 115px">' . $value['ACTIVITY'] . '</td>
                        <td style="text-align: center; width: 115px">' . $str_deleted . '</td>';
                if ($value['ID'] > $max_id)
                    $max_id = $value['ID'];
                if ($value['DELETED']) {
                    echo '<td style="text-align: center; width: 90px;"><a href="?restore_id=' . $value['ID'] . '">Восстановить</a></td>';
                } else {
                    echo '<td style="text-align: center; width: 90px;"><a href="?del_id=' . $value['ID'] . '">Удалить</a></td>';
                }
                echo '</tr>';
            }
            else {
                if (TRUE == ENCODE_UTF) {
                    $value['1'] = iconv('utf-8', 'windows-1251', $value['1']);
                    $value['3'] = iconv('utf-8', 'windows-1251', $value['3']);
                    $value['4'] = iconv('utf-8', 'windows-1251', $value['4']);
                    $value['5'] = iconv('utf-8', 'windows-1251', $value['5']);
                }
                $str_deleted = ($value['7'] ? $value['7'] : "нет");
                if ($value['0'] > $max_id)
                    $max_id = $value['0'];
                echo '<tr><td style="text-align: center; width: 35px">' . $value['0'] . '</td>
                        <td style="text-align: left; width: 150px">' . $value['1'] . '</td>
                        <td style="text-align: center; width: 95px">' . $value['4'] . '</td>
                        <td style="text-align: center; width: 80px">' . $value['5'] . '</td>
                        <td style="text-align: center; width: 150px">' . $value['6'] . '</td>
                        <td style="text-align: center; width: 90px">' . $value['3'] . '</td>
                        <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($value['7']) {
                    echo '<td style="text-align: center; width: 100px;"><a href="?restore_id=' . $value['0'] . '">Восстановить</a></td>';
                } else {
                    echo '<td style="text-align: center; width: 100px;"><a href="?del_id=' . $value['0'] . '">Удалить</a></td>';
                }
                echo '</tr>';
            }
        }
    }
    ?>
    </tbody>
</table>
<input type='submit' name='save' style='background-color: plum; height: 30px; font-weight: bold' value='Сохранить изменения'/>
</form>

<?php
// Обработка действий
if (isset($AddName) && isset($AddLogin) && isset($AddPassword)) {
    $count = 0; $err_mess = '';
    $nrowsUser = GetData::GetUsers(TRUE, TRUE, NULL, "FIO");
    if (DB_OCI) {
        foreach(GetData::$array_user as $key => $value) {
            if (NULL == $value['DELETED'] && $AddName == $value['FIO']) {
                $err_mess = "Пользователь '".$AddName."' уже существует.<br />";
                $count = 1;
                break;
            }
            elseif (NULL == $value['DELETED'] && $AddLogin == $value['LOGIN'] && $AddPassword == $value['PASSWORD']) {
                $err_mess = "Такое сочетание логина и пароля уже существует.<br />";
                $count = 1;
                break;
            }
        }
    }
    else {
        $checkstr = "SELECT ID FROM USERS WHERE FIO LIKE '{$AddName}' limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>$err_mess</p>";
    }
    else {
        $max_id++;
        $Row_acc = '';
        foreach($acc_array as $acc_id => $acc_name) {
            if (isset($_POST['newacc_'.$acc_id.'']) && $_POST['newacc_'.$acc_id.'']) $Row_acc .= $acc_id . ',';
        }
        if (strlen($Row_acc) > 0)
            $Row_acc = substr($Row_acc, 0, -1);
			if (TRUE == ENCODE_UTF) {
                $AddName   = iconv ('utf-8','windows-1251', $AddName);
                $AddLogin = iconv ('utf-8','windows-1251', $AddLogin);
                $AddPassword = iconv ('utf-8','windows-1251', $AddPassword);
			}
			//Вставляем данные
        $insertstr = "INSERT INTO USERS (ID, FIO, LOGIN, PASSWORD, PIN, EMAIL, ROLE_ID, DATA_ACC) 
VALUES ('{$max_id}', '{$AddName}', '{$AddLogin}', '{$AddPassword}', '{$AddPIN}', '{$email}', '{$RoleId}', '{$Row_acc}')";
        if (DB_OCI) {
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            if ($query_result) { //Добавляем пользователя и в департамент
                $insertstr = "INSERT INTO USER_DEP_ALLOC (ID, DEP_ID, USER_ID) VALUES (SEQ_USER_DEP_ID.nextval, '{$_POST['Depart']}', '{$max_id}')";
                $query = OCIParse(GetData::GetConnect(), $insertstr);
                $query_result = OCIExecute($query);
            }
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            /*if ($query_result) { //Добавляем пользователя и в департамент
                $insertstr = "INSERT INTO USER_DEP_ALLOC (ID, DEP_ID, USER_ID) VALUES (SEQ_USER_DEP_ID.nextval, {$_POST['Depart']}, {$_POST['User']})";
                $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
            }*/
        }
        if ($query_result) {
			print "<script language='Javascript'>
					function reload() {location = \"$backurl\"}; setTimeout('reload()', 3000);
					</script>
				<p style='color: green'>Строка успешно добавлена в таблицу. Идет перезагрузка данных...</p>";
        } else {
            echo "<p style='color: red'>Произошла ошибка добавления записи!</p>";
        }
    }
}

//Удаляем, если что, но пока лишь меняем признак удаления строки
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE USERS SET DELETED = to_date('".date("d-m-Y H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
        if ($query_result) {
            $deletestr = "UPDATE USER_DEP_ALLOC SET DELETED = to_date('".date("d-m-Y H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE USER_ID = '{$_GET['del_id']}'";
            $query = OCIParse(GetData::GetConnect(), $deletestr);
            $query_result = OCIExecute($query);
        }
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE USERS SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
		$query_result = mysqli_query(GetData::GetConnect(), $deletestr);
	}
    if ($query_result) {
        print "<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
			<p style='color: green'>Строка изменена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка удаления записи.</p>";
	}
}

//Восстанавливаем удаленное
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE USERS SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        print "<script language='Javascript'>
            function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
                </script>
			<p style='color: green'>Строка восстановлена. Идет перезагрузка данных...</p>";
    } else {
        echo "<p style='color: red'>Произошла ошибка восстановления записи.</p>";
    }
}
?>
<div>
    <form action="" method="post">
        <h3 style="margin-top: 0; margin-bottom: 0">Новый пользователь: <input type="text" name="AddName" style="width: 15em;" placeholder="Фамилия Имя Отчество"/>
            Департамент:&nbsp;
            <?php
            if (GetData::GetDepartments(FALSE,FALSE,NULL) > 0) {
                echo "<select id='Depart' name='Depart'>";
                echo "<option value='-1'>Выберите департамент</option>";
                if (DB_OCI) {
                    foreach(GetData::$array_dep as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        echo "<option value='" . $value['ID'] . "'>" . $value['NAME'] . "</option>";
                    }
                } else {
                    foreach(GetData::$array_dep as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                        echo "<option value='" . $value[0] . "'>" . $value[1] . "</option>";
                    }
                }
                echo "</select>";
                //echo "<script>$('#Depart').val('7').change();</script>"; // Отдел по умолчанию - Стоматология
            }

            echo "&nbsp;Статус: ";
            echo "<select id='RoleId' name='RoleId'>";
            if (GetData::GetRoles() > 0) {
                if (DB_OCI) {
                    foreach(GetData::$array_roles as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                    }
                }
                else {
                    foreach(GetData::$array_roles as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                        echo "<option value='".$value[0]."'>".$value[1]."</option>";
                    }
                }
            }
            echo "</select>";
            echo "<script>$('#RoleId').val('".USER_USER."').change();</script>";
            echo '&nbsp;ПИН: <input type="text" name="AddPIN" id="AddPIN" style="width: 120px;" placeholder="ПИН"/><br/>';

            echo 'Логин: <input type="text" name="AddLogin" id="AddLogin" style="width: 120px;" placeholder="Логин"/>';
            echo '&nbsp;Пароль: <input type="text" name="AddPassword" value="'.pass_gen(8).'" style="width: 120px;" placeholder="Пароль"/>';
            echo '&nbsp;E-Mail: <input type="text" name="email" id="email" style="width: 200px;" placeholder="E-Mail"/>';
            //echo '<table id="add_table" class="white_table" style="visibility: inherit"><tr>';
            foreach($acc_array as $acc_id => $acc_name) {
                echo '<th style="width: 101px;">&nbsp;'.$acc_name.':<input type="checkbox" name="newacc_'.$acc_id.'"/></th>';
            }
            //echo '</tr></table>';
            echo '<input type="submit" name="Adding" id="Adding" value="Добавить в базу" class="add_button">';
            //<input type="hidden" name = "table_name" value="USERS">
            ?>
            <script type = "text/javascript">
                var select = document.getElementById("RoleId");
                select.onchange = function() {
                    var elem = document.getElementById('AddLogin');
                    if (<?=USER_USER?> == this.value) {
                        //this.style.backgroundColor = '< ?=needs?>';
                        //elem = this;
                        document.getElementById('AddPIN').style.visibility = 'visible';
                    }
                    else {
                        //this.style.backgroundColor = 'white';
                        document.getElementById('AddPIN').style.visibility = 'hidden';
                    }
                    elem.focus();
                }
            </script>
        </h3>
    </form>
</div>

</body>
</html>