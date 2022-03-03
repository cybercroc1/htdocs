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
$backurl = "admin_department.php";
//---------------------------------------------------------------------- //
?>

<?php
    $checkboxes = array(
    'show_closed' => (isset($_SESSION['my_checkbox_checked']) && $_SESSION['my_checkbox_checked'] == 1 ? true : false)
    );
    $_SESSION['my_checkbox_checked'] = (int) isset($_POST['show_closed']);
?>

<script type="application/javascript" >

function ch_show_closed() {
    if (true == document.all.show_closed.checked) { location.reload('/?show_closed=1');}
    else {location.reload('/');}
}

</script>

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

<body style="margin-top: 0;">
<h3 style="margin-bottom: 0;margin-top: 0;">������ �������������</h3>

<div id="content2">
<table border='2' style="display: inline-block;">
    <tr>
        <th style="width:  35px;">ID</th>
        <th style="width: 150px;">������������</th>
        <th style="width: 120px;">�������</th>
        <th style="width:  85px;">��������</th>
    </tr>

    <?php
    $max_id = 0; // ��� ������� ����� ������, ���� �����������
    if (GetData::GetDepartments(FALSE,TRUE,NULL) > 0) {
        foreach($_POST['array_dep'] as $key => $value) {
            if (TRUE == ENCODE_UTF) {
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            }
            $str_deleted = ($value['DELETED'] ? $value['DELETED'] : "���");
            if ($value['ID'] > $max_id)
                $max_id = $value['ID'];

            echo '<tr><td style="text-align: center">' . $value['ID'] . '</td>
				<td style="text-align: center">' . $value['NAME'] . '</td>
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
</div>

<?php
// ��������� ��������
// ���� ���������� Name ��������
if (isset($_POST['Name'])) {
    $bCanAdd = 0;
    foreach($_POST['array_dep'] as $key => $value) {
        if (DB_OCI)
            $bCanAdd = strcasecmp($_POST['Name'], $value['NAME']);
        else $bCanAdd = strcmp(strtolower($_POST['Name']), strtolower($value['1']));
        if ($bCanAdd == 0)
            break;
    }

    if ($bCanAdd == 0) {
        echo "<p style='color: red'>����������� '".$_POST['Name']."' ��� ����������.<br /></p>";
    }
    else {
        $max_id++;
        //��������� ������
        $insertstr = "INSERT INTO DEPARTAMENTS (ID, NAME) VALUES ( {$max_id}, '{$_POST['Name']}' )";
        if (DB_OCI) {
			if (TRUE == ENCODE_UTF) {
                $tmpstr = iconv ('utf-8','windows-1251', $_POST['Name']);
                $insertstr = "INSERT INTO DEPARTAMENTS (ID, NAME) VALUES ( {$max_id}, '{$tmpstr}' )";
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
        $deletestr = "UPDATE DEPARTAMENTS SET DELETED = to_date('".date("d-m-Y  H:i:s")."','DD.MM.YYYY hh24:mi:ss') WHERE ID = '{$_GET['del_id']}'";
        $query = OCIParse(GetData::GetConnect(), $deletestr);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
    }
    else {
		$deletestr = "UPDATE DEPARTAMENTS SET DELETED = '".date("Y-m-d H:i:s")."' WHERE ID = '{$_GET['del_id']}'";
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
    $deletestr = "UPDATE DEPARTAMENTS SET DELETED = NULL WHERE ID = '{$_GET['restore_id']}'";
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
    <!--form action="" method="post" id="MyForm"-->
    <!--label for="show_closed">�������� ���������</label>
    <input type="checkbox" name="show_closed" title="�������� ���������" onclick=ch_show_closed()
        <=($checkboxes['show_closed'] ? 'checked' : null); ?> />
            <!--?=(isset($show_closed) ? 'checked' : null); ?>

    <!--/form-->
    <!--?php if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()> | "; ?-->
</div>

<div>
    <form action="" method="post">
        <h3>����� �����������: <input type="text" name="Name" placeholder="�����������"></h3>
        <div>
            <input type="submit" name="Adding" value="�������� � ����" class="add_button" >
            <input type="hidden" name = "table_name" value="DEPARTAMENTS">
        </div>
    </form>
</div>

</body>
</html>