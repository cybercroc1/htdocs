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
$backurl = "admin_service_detail.php";
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
    <title>����������� ������</title>
    <meta name="description" content="����������� ������">
</head>

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">������ ����������� �����</h3>
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_ie">
        <?php } else { ?>
    <table class="scrolling-table">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px;">ID</th>
        <th style="width: 250px;">������</th>
        <th style="width: 200px;">�����������</th>
        <th style="width: 120px;">�������</th>
        <th style="width: 100px;">��������</th>
    </tr></thead>
    <tbody>
    <?php
    $max_id = 0; // ��� ������� ����� ������, ���� �����������

    if (DB_OCI) {
        $selectstr = "SELECT smd.ID, serv.Name as smName, smd.Name as smdName, to_char(smd.Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted 
                      FROM SERVICE_DET smd, SERVICES serv WHERE smd.ID != -1 AND serv.ID = smd.SERVICE_ID
                      ORDER BY smd.SERVICE_ID, smd.Name";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $result_array['SMDNAME'] = iconv('windows-1251', 'utf-8', $result_array['SMDNAME']);
                    $result_array['SMNAME'] = iconv('windows-1251', 'utf-8', $result_array['SMNAME']);
                }
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "���");
                if ($result_array['ID'] > $max_id)
                    $max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result_array['ID'] . '</td>
                <td style="text-align: center; width: 250px">' . $result_array['SMNAME'] . '</td>
				<td style="text-align: center; width: 200px">' . $result_array['SMDNAME'] . '</td>
				<td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($result_array['DELETED']) {
                    echo '<td style="text-align: center; width: 100px"><a href="?restore_id=' . $result_array['ID'] . '">������������</a></td></tr>';
                } else {
                    echo '<td style="text-align: center; width: 100px"><a href="?del_id=' . $result_array['ID'] . '">�������</a></td></tr>';
                }
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT smd.ID, serv.Name as smName, smd.Name as smdName, DATE_FORMAT(smd.Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted 
                      FROM SERVICE_DET as smd, SERVICES as serv WHERE smd.ID != -1 AND serv.ID = smd.SERVICE_ID
                      ORDER BY smd.SERVICE_ID, smd.Name";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (TRUE == ENCODE_UTF) {
                    $result['smdName'] = iconv ('utf-8', 'windows-1251', $result['smdName']);
                    $result['smName'] = iconv ('utf-8', 'windows-1251', $result['smName']);
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "���");
                if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result['ID'] . '</td>
                <td style="text-align: center; width: 250px">' . $result['smName'] . '</td>
                <td style="text-align: center; width: 200px">' . $result['smdName'] . '</td>
                <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ( $result['Deleted'] ) {
                    echo '<td style="text-align: center; width: 100px"><a href="?restore_id=' . $result['ID'] . '">������������</a></td></tr>';
                }
                else {
                    echo '<td style="text-align: center; width: 100px"><a href="?del_id=' . $result['ID'] . '">�������</a></td></tr>';
                }
            }
        }
    }
    ?>
        </tbody>
    </table>

<?php
// ��������� ��������
// ���� ���������� Name ��������
if (isset($_POST['Name'])) {
    $count = 0;
    $checkstr = "SELECT ID FROM SERVICE_DET WHERE NAME LIKE '{$_POST['Name']}' AND SERVICE_ID = {$_POST['Service']}";
    if (DB_OCI) {
		$objParse = OCIParse(GetData::GetConnect(), $checkstr);
		OCIExecute($objParse);
		$objResult = OCI_Fetch_Row($objParse);
		$count = ($objResult == TRUE ? 1 : 0);
	}
    else {
        $checkstr .= " limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>����������� '".$_POST['Name']."' ��� ����������.<br /></p>";
    }
    else {
        $max_id++;

        //��������� ������
        $insertstr = "INSERT INTO SERVICE_DET (ID, SERVICE_ID, NAME) VALUES ( {$max_id}, {$_POST['Service']}, '{$_POST['Name']}' )";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstr = iconv ('utf-8','windows-1251', $_POST['Name']);
                $insertstr = "INSERT INTO SERVICE_DET (ID, SERVICE_ID, NAME) VALUES ( {$max_id}, {$_POST['Service']}, '{$tmpstr}' )";
			}
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
				<p style='color: green'>������ ������� ��������� � �������. ���� ������������ ������...</p>";
        } else {
            echo "<p style='color: red'>��������� ������ ���������� ������!</p>";
        }
    }
}

//�������, ���� ���, �� ���� ���� ������ ������� �������� ������
if (isset($_GET['del_id'])) {
    //$deletestr = "DELETE FROM ".$_POST['table_name']." WHERE ID = '{$_GET['del_id']}'";
    if (DB_OCI) {
        $deletestr = "UPDATE SERVICE_DET SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE SERVICE_DET SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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

//��������������� ���������
if (isset($_GET['restore_id'])) {
    $deletestr = "UPDATE SERVICE_DET SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
        <h3>������:&nbsp;
        <?php
        if (GetData::GetServices(FALSE,FALSE, NULL,FALSE) > 0) {
            echo "<select id='Service' name='Service'>";
            if (DB_OCI) {
                foreach($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            echo "</select>";
        }
        ?>
        ����� �����������: <input type="text" name="Name" style="width: 250px" placeholder="�����������">
        </h3>
        <div>
            <input type="submit" name="Adding" value="�������� � ����" class="add_button">
            <input type="hidden" name = "table_name" value="SERVICE_DET">
        </div>
    </form>
</div>

</body>
</html>