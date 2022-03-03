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

if (!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>������ ��������</b>"; exit();}
if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">C������� ����������!</p>'; exit();
}
// ----------------------------������������-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail ������
$date=date("d.m.Y"); // �����.�����.���
$time=date("H:i"); // ����:������:�������
$backurl = "admin_source.php";
//---------------------------------------------------------------------- //
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>��������� �������</title>
    <meta name="description" content="��������� �������">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">������ ���������� �������</h3>
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width:  35px;">ID</th>
        <th style="width: 250px;">������������</th>
        <th>���������</th>
        <th>������</th>
        <th style="width: 120px;">�������</th>
        <th style="width:  85px;">��������</th>
    </tr>

    <?php
    //$max_id = 0; // ��� ������� ����� ������, ���� �����������
    if (GetData::GetIstochnik(FALSE, TRUE,NULL, FALSE) > 0 &&
        GetData::GetServices(FALSE,TRUE,NULL) > 0) {
        foreach(GetData::$array_istochnik as $key => $value) {
            if (TRUE == ENCODE_UTF) {
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            }
            $str_deleted = ($value['DELETED'] ? $value['DELETED'] : "���");
            /*if ($value['ID'] > $max_id)
                $max_id = $value['ID'];*/

            if ('' == $value['IN_DEP'])
                $serv_name = '�� ����������';
            elseif (SERVICE_ALL === $value['IN_DEP'])
                $serv_name = '�����';
            else {
                $serv_name = '';
                $arr_dep = explode(',', $value['IN_DEP']);
                for ($ii=0; $ii < sizeof($arr_dep); $ii++) {
                    foreach(GetData::$array_services as $key_dep => $deps) {
                        if (TRUE == ENCODE_UTF)
                            $deps['NAME'] = iconv('windows-1251', 'utf-8', $deps['NAME']);
                        if ($arr_dep[$ii] == -1) {
                            $serv_name .= "�����, ";
                            break;
                        }
                        elseif ($arr_dep[$ii] == $deps['ID']) {
                            $serv_name .= $deps['NAME'].", ";
                            break;
                        }
                    }
                }
                $serv_name = substr($serv_name, 0, -2);
            }

            echo '<tr><td style="text-align: center">' . $value['ID'] . '</td>
				<td style="text-align: center">' . $value['NAME'] . '</td>
				<td style="text-align: center">' . $value['PRIORITY'] . '</td>
				<td style="text-align: center">' . $serv_name . '</td>
				<td style="text-align: center">' . $str_deleted . '</td>';
            if ($value['DELETED']) {
                echo '<td style="text-align: center"><a href="?restore_id=' . $value['ID'] . '">������������</a></td></tr>';
            } else {
                echo '<td style="text-align: center"><a href="?del_id=' . $value['ID'] . '">�������</a></td></tr>';
            }
        }
    }
    ?>
</table>

<?php
// ��������� ��������
// ���� ���������� Name ��������
if (isset($_POST['NameIst'])) {
    $bCanAdd = 0;
    foreach(GetData::$array_istochnik as $key => $value) {
        if (DB_OCI)
            $bCanAdd = strcasecmp($_POST['NameIst'], $value['NAME']);
        else $bCanAdd = strcmp(strtolower($_POST['NameIst']), strtolower($value['1']));
        if ($bCanAdd == 0)
            break;
    }

    if ($bCanAdd == 0) {
        echo "<p style='color: red'>�������� ������� '".$_POST['NameIst']."' ��� ����������.<br /></p>";
    }
    else {
        //$max_id++;
        if (TRUE == ENCODE_UTF)
            $_POST['NameIst'] = iconv('utf-8','windows-1251', $_POST['NameIst']);
        //��������� ������
        $serv_list = implode(',', $_POST['Services']);
        $insertstr = "INSERT INTO SOURCE_MAN (ID, NAME, DETAIL, PRIORITY, IN_DEP) 
VALUES ( seq_source_man_id.nextval, '{$_POST['NameIst']}', '�����������', 3, '{$serv_list}' )";
        if (DB_OCI) {
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			print "<script language='Javascript'>
					function reload() {location = \"$backurl\"}; setTimeout('reload()', 3000);
					</script>
				<p style='color: green'>����� �������� ������� ������� �������� � �������. ���� ������������ ������...</p>";
        } else {
            echo "<p style='color: red'>��������� ������ ���������� ��������� �������!</p>";
        }
    }
}

//�������, ���� ���, �� ���� ���� ������ ������� �������� ������
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE SOURCE_MAN SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE SOURCE_MAN SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
		$query_result = mysqli_query(GetData::GetConnect(), $deletestr);
	}
    if ($query_result) {
        print "<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
			<p style='color: green'>������ ��������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ �������� ������.</p>";
	}
}

//��������������� ���������s
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE SOURCE_MAN SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
			<p style='color: green'>������ �������������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ �������������� ������.</p>";
    }
}
?>

<div>
    <form action="" method="post">
        <h3>����� �������� �������: <input type="text" name="NameIst" style="width: 250px" placeholder="�������� �������"><br>
            <?php if (GetData::GetServices(FALSE,FALSE,NULL) > 0) {
                echo "<label for='Services' id='ServicesT' style='float: left;'>������:&nbsp;</label>";
                echo "<select id='Services' name='Services[]' multiple style='height: 123px;'>";
                echo "<option value='".SERVICE_ALL."' selected='selected'>��� ������</option>";
                if (DB_OCI) {
                    foreach(GetData::$array_services as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach(GetData::$array_services as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                echo "</select>";
            } ?>
        </h3>
        <div>
            <input type="submit" name="Adding" value="�������� � ����" class="add_button">
            <input type="hidden" name = "table_name" value="SOURCE_MAN">
        </div>
    </form>
</div>

</body>
</html>