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
$backurl = "admin_theme.php";
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
    <title>���� ������</title>
    <meta name="description" content="���� ������">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">������ ��� �������� �������</h3>
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width:  35px;">ID</th>
        <th style="width: 200px;">������������</th>
        <th style="width: 100px;">�������</th>
        <th style="width: 120px;">�������</th>
        <th style="width:  85px;">��������</th>
    </tr>

    <?php
    $max_id = 0; // ��� ������� ����� ������, ���� �����������
    if (GetData::GetThemes(NULL) > 0) {
        foreach(GetData::$array_theme as $key => $value) {
            if (TRUE == ENCODE_UTF) {
                $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
            }
            if ($value['ID'] > $max_id)
                $max_id = $value['ID'];

            echo '<tr><td style="text-align: center">' . $value['ID'] . '</td>
				<td style="text-align: center">' . $value['NAME'] . '</td>
                <td style="text-align: center">' . ($value['TARGET'] ? '��' : "���") . '</td>
				<td style="text-align: center">' . ($value['DELETED'] ? $value['DELETED'] : "���") . '</td>';
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
if (isset($Name)) { // ���� ���������� Name ��������
    $iTarget = 0;
    if (isset($_POST['checkbox_target']) && $_POST['checkbox_target'] == "on")
        $iTarget = 1;

    $bCanAdd = 0;
    foreach(GetData::$array_theme as $key => $value) {
        if (DB_OCI)
            $bCanAdd = strcasecmp($Name, $value['NAME']);
        else $bCanAdd = strcmp(strtolower($Name), strtolower($value['1']));
        if ($bCanAdd == 0)
            break;
    }

    if ($bCanAdd == 0) {
        echo "<p style='font-size: larger; color: red'>���� '".$Name."' ��� ����������.<br /></p>";
    }
    else {
        //��������� ������
        if (DB_OCI) {
            $insertstr = "INSERT INTO CALL_THEME (ID, NAME, TARGET) VALUES (SEQ_CALL_THEME_ID.NEXTVAL, '".trim($_POST['Name'])."', '{$iTarget}' )";
			if (TRUE == ENCODE_UTF) {
				$tmpstr = trim(iconv ('utf-8','windows-1251', $Name));
                $insertstr = "INSERT INTO CALL_THEME (ID, NAME, TARGET) VALUES (SEQ_CALL_THEME_ID.NEXTVAL, '{$tmpstr}', '{$iTarget}' )";
			}
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $max_id++;
            $insertstr = "INSERT INTO CALL_THEME (ID, NAME, TARGET) VALUES ( {$max_id}, '".trim($Name)."', '{$iTarget}' )";
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			echo"<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
				<p style='font-size: larger; color: green'>������ ������� ��������� � �������. ���� ������������ ������...</p>";
        } else {
            echo "<p style='font-size: larger; color: red'>��������� ������ ���������� ������!</p>";
        }
    }
}

//�������, ���� ���, �� ���� ���� ������ ������� �������� ������
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE CALL_THEME SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE CALL_THEME SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
		$query_result = mysqli_query(GetData::GetConnect(), $deletestr);
	}
    if ($query_result) {
        echo "<script language='Javascript'>
                function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
				</script>
			<p style='font-size: larger; color: green'>������ ��������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='font-size: larger; color: red'>��������� ������ �������� ������.</p>";
	}
}

//��������������� ���������
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE CALL_THEME SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
    if (DB_OCI) {
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
        $query_result = mysqli_query(GetData::GetConnect(), $deletestr);
    }
    if ($query_result) {
        echo "<script language='Javascript'>
            function reload() {location = \"$backurl\" }; setTimeout('reload()', 3000);
                </script>
			<p style='font-size: larger; color: green'>������ �������������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='font-size: larger; color: red'>��������� ������ �������������� ������.</p>";
    }
}
?>

<div>
    <form action="" method="post">
        <h3>
            ����� ���� ������: <input type="text" name="Name" style="width: 330px;" placeholder="���� ������">
            <label for="checkbox_target" style="margin-left: 10px"> �������:</label>
            <input type="checkbox" name="checkbox_target" title="�������"/>
        </h3>
        <div>
            <input type="submit" name="Adding" value="�������� � ����" class="add_button">
            <input type="hidden" name = "table_name" value="CALL_THEME">
        </div>
    </form>
</div>

</body>
</html>