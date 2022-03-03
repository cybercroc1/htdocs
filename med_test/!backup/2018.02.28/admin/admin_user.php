<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();
extract($_REQUEST);
/*if ($_SERVER['REQUEST_METHOD'] == "POST"){
    header("location:{$_SERVER['PHP_SELF']}");
}*/
require_once '../funct.php';

if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">C������� ����������!</p>'; exit();
}
// ----------------------------������������-------------------------- //
date_default_timezone_set('Europe/Moscow');
$adminemail="2392967@mail.ru";  // e-mail ������
$date=date("d.m.Y"); // �����.�����.���
$time=date("H:i"); // ����:������:�������
$backurl = "admin_user.php";
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
    <title>������������</title>
    <meta name="description" content="������������">
</head>
<?php
//define('ROLES', array('', '�������������', '�����������', '������������', '��������'));
?>
<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">������ �������������</h3>
        <?php if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE") !== false ||
				strpos($_SERVER["HTTP_USER_AGENT"], "rv:11.0") !== false) { ?>
    <table class="scrolling-table_uie">
        <?php } else { ?>
    <table class="scrolling-table_u">
        <?php } ?>
    <thead><tr>
        <th style="width: 35px;">ID</th>
        <th style="width: 150px;">���</th>
        <th style="width: 80px;">�����</th>
        <th style="width: 80px;">������</th>
        <th style="width: 90px;">������</th>
        <th style="width: 120px;">�������</th>
        <th style="width: 100px;">��������</th>
    </tr></thead>
    <tbody>
    <?php
    $max_id = 1; // ��� ������� ����� ������, ���� �����������
    if (DB_OCI) {
        $selectstr = "SELECT usr.ID, usr.FIO, usr.LOGIN, usr.PASSWORD, usr.ROLE_ID, rls.NAME as ROLE, to_char(usr.Deleted,'dd.mm.yyyy hh24:mi:ss') Deleted 
                      FROM USERS usr
                      LEFT JOIN ROLES rls ON usr.ROLE_ID = rls.ID
                      WHERE usr.ID != -1 ORDER BY ROLE_ID, FIO";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        $query_result = OCIExecute($query);
        if ($query_result) {
            while ($result_array = OCI_Fetch_Array($query)) {
                if (TRUE == ENCODE_UTF) {
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['FIO']);
                    $result_array['FIO'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['LOGIN']);
                    $result_array['LOGIN'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['PASSWORD']);
                    $result_array['PASSWORD'] = $tmpstr;
                    $tmpstr = iconv('windows-1251', 'utf-8', $result_array['ROLE']);
                    $result_array['ROLE'] = $tmpstr;
                }
                $str_deleted = ($result_array['DELETED'] ? $result_array['DELETED'] : "���");
                if ($result_array['ID'] > $max_id)
                    $max_id = $result_array['ID'];

                echo '<tr><td style="text-align: center; width: 35px">' . $result_array['ID'] . '</td>
                <td style="text-align: left; width: 150px">' . $result_array['FIO'] . '</td>
                <td style="text-align: center; width: 80px">' . $result_array['LOGIN'] . '</td>
                <td style="text-align: center; width: 80px">' . $result_array['PASSWORD'] . '</td>
                <td style="text-align: center; width: 90px">' . $result_array['ROLE'] . '</td>
                <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ($result_array['DELETED']) {
                    echo '<td style="text-align: center; width: 100px;"><a href="?restore_id=' . $result_array['ID'] . '">������������</a></td></tr>';
                } else {
                    echo '<td style="text-align: center; width: 100px;"><a href="?del_id=' . $result_array['ID'] . '">�������</a></td></tr>';
                }
            }
        }
        oci_free_statement($query);
    }
    else {
        $selectstr = "SELECT usr.ID, usr.FIO, usr.LOGIN, usr.PASSWORD, usr.ROLE_ID, rls.NAME as ROLE, DATE_FORMAT(usr.Deleted,'%d.%m.%Y %H:%i:%s') AS Deleted 
                      FROM USERS usr
                      LEFT JOIN ROLES rls ON usr.ROLE_ID = rls.ID
                      WHERE usr.ID != -1 ORDER BY ROLE_ID, FIO";
        $sql = mysqli_query(GetData::GetConnect(), $selectstr);

        if ($sql) {
            while ($result = $sql->fetch_array()) {
                if (FALSE == ENCODE_UTF) {
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['FIO']);
                    $result['FIO'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['LOGIN']);
                    $result['LOGIN'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['PASSWORD']);
                    $result['PASSWORD'] = $tmpstr;
                    $tmpstr = iconv ('utf-8', 'windows-1251', $result['ROLE']);
                    $result['ROLE'] = $tmpstr;
                }
                $str_deleted = ($result['Deleted'] ? $result['Deleted'] : "���");
                if ($result['ID'] > $max_id)
                    $max_id = $result['ID'];

                echo '<tr><td style="text-align: center">' . $result['ID'] . '</td>
                <td style="text-align: center; width: 150px">' . $result['FIO'] . '</td>
                <td style="text-align: center; width: 80px">' . $result['LOGIN'] . '</td>
                <td style="text-align: center; width: 80px">' . $result['PASSWORD'] . '</td>
                <td style="text-align: center; width: 90px">' . $result['ROLE'] . '</td>
                <td style="text-align: center; width: 120px">' . $str_deleted . '</td>';
                if ( $result['Deleted'] ) {
                    echo '<td style="text-align: center"><a href="?restore_id=' . $result['ID'] . '">������������</a></td></tr>';
                }
                else {
                    echo '<td style="text-align: center"><a href="?del_id=' . $result['ID'] . '">�������</a></td></tr>';
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
if (isset($_POST['Name']) && isset($_POST['login']) && isset($_POST['password'])) {
    $count = 0;
    if (DB_OCI) {
        $count = (in_array($_POST['Name'], $result_array['FIO']) == TRUE ? 1 : 0);
    }
    else {
        $checkstr = "SELECT ID FROM USERS WHERE FIO LIKE '{$_POST['Name']}' limit 1";
        $query_result = mysqli_query(GetData::GetConnect(), $checkstr);
        if (FALSE !== $query_result)
            $count = mysqli_num_rows($query_result);
        else {
            $count = 1;
            printf("Errormessage: %s\n", mysqli_error(GetData::GetConnect()));
        }
    }

    if ($count === 1) {
        echo "<p style='color: red'>������������ '".$_POST['Name']."' ��� ����������.<br /></p>";
    }
    else {
        $max_id++;

        //��������� ������
        $insertstr = "INSERT INTO USERS (ID, FIO, LOGIN, PASSWORD, ROLE_ID) VALUES ( {$max_id}, '{$_POST['Name']}', '{$_POST['login']}', '{$_POST['password']}', {$_POST['Role']} )";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstrfio   = iconv ('utf-8','windows-1251', $_POST['Name']);
                $tmpstrlogin = iconv ('utf-8','windows-1251', $_POST['login']);
                $tmpstrpassw = iconv ('utf-8','windows-1251', $_POST['password']);
                $insertstr = "INSERT INTO USERS (ID, FIO, LOGIN, PASSWORD, ROLE_ID) VALUES ( {$max_id}, '{$tmpstrfio}', '{$tmpstrlogin}', '{$tmpstrpassw}', {$_POST['Role']} )";
			}
            $query = OCIParse(GetData::GetConnect(), $insertstr);
            $query_result = OCIExecute($query);
            oci_free_statement($query);
        }
        else {
            $query_result = mysqli_query(GetData::GetConnect(), $insertstr);
        }
        if ($query_result) {
			$max_id++; // ��� ������� ��������� ������
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
        $deletestr = "UPDATE USERS SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
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
			<p style='color: green'>������ ��������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ �������� ������.</p>";
	}
}

//��������������� ���������
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
			<p style='color: green'>������ �������������. ���� ������������ ������...</p>";
    } else {
        echo "<p style='color: red'>��������� ������ �������������� ������.</p>";
    }
}
?>


<div>
    <form action="" method="post">
        <h3>����� ������������: <input type="text" name="Name" style="width: 305px;" placeholder="������� ��� ��������"/>
        <?php
        if (GetData::GetRoles() > 0) {
            printf("<td><select id='RoleId' name='Role'>");
            // �������� �������� �� ������������
            if (DB_OCI) {
                foreach($_POST['array_roles'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_roles'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            printf("</select></td>");
        }
        ?>
        </h3>
        <h3>
            �����: <input type="text" name="login" placeholder="�����"/>
            ������: <input type="text" name="password" placeholder="������"/>
        </h3>
        <div>
            <input type="submit" name="Adding" value="�������� � ����" class="add_button">
            <input type="hidden" name = "table_name" value="USERS">
        </div>
    </form>
</div>

</body>
</html>