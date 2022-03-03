<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./billing.css">
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>��������</title>
    <base href="/">
    <meta name="description" content="��������.">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="application/javascript">
        function OpenIncomingCall(path)
        {
            var myWindow = window.open(path+"med_call.php?anumber=6022&bnumber=5555555557&sc_agid=�������� �������&sc_project_id=41&sc_call_id=16960183",
                "MsgWindow", "width=720, height=600, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
            myWindow.focus();
        }
    </script>
</head>

<body>
<?php
ini_set('session.use_cookies','1');
//ini_set('session.use_trans_sid','0');

//session_name('medc');
session_start();

extract($_REQUEST);

require_once 'funct.php';
?>

<div id='sidebar'>
    <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) { ?>
        <div>
            <form action="<?=PATH?>/admin/admin_theme.php" method="post" target=admRightFrame title="���� ������">
                <input type="submit" name="Theme" value="���� ������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="CALL_THEMES"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_service.php" method="post" target=admRightFrame title="��������������� ������">
                <input type="submit" name="Serv" value="��������������� ������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="SERVICES"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source_auto.php" method="post" target=admRightFrame title="��������� ������� (Auto)">
                <input type="submit" name="Ist_Auto" value="��������� ������� (Auto)" class="enter_button" >
                <!--input type="hidden" name = "table_name" value="SOURCE_AUTO"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source.php" method="post" target=admRightFrame title="��������� �������">
                <input type="submit" name="Ist" value="��������� �������" class="enter_button" >
                <!--input type="hidden" name = "table_name" value="SOURCE_MAN"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source_detail.php" method="post" target=admRightFrame title="���������� ���������">
                <input type="submit" name="Ist_Det" value="���������� ���������" class="enter_button" >
                <!--input type="hidden" name = "table_name" value="SOURCE_MAN_DETAIL"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_department.php" method="post" target=admRightFrame title="������������">
                <input type="submit" name="Dep" value="������������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="DEPARTAMENTS"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_user.php" method="post" target=admRightFrame title="������������">
                <input type="submit" name="Usr" value="������������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="USERS"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_user_dep.php" method="post" target=admRightFrame title="������������ �� �������������">
                <input type="submit" name="Usr_Dep" value="������������ �� �������������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="USER_DEP_ALLOC"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_access_dep.php" method="post" target=admRightFrame title="����� �������">
                <input type="submit" name="Access_Dep" value="����� �������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="ACCESS_DEP"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_clinic.php" method="post" target=admRightFrame title="�������">
                <input type="submit" name="Hospital" value="�������" class="enter_button">
                <!--input type="hidden" name = "table_name" value="HOSPITALS"-->
            </form>
        </div>

        <form action="" method="post">
            <?php $to_path = '"'.PATH.'/"' ?>
            <input type="button" name="Give_Call" value=" �������� ������ " onclick='OpenIncomingCall(<?=$to_path?>)' style="margin-left: 3px; font-weight: bold; background-color: red;color: white; height: 25px">
        </form>

    <?php } ?>

    <br/>
    <!--form action="< ?=PATH?>/call_view/med_call_frame.php" method="post" target="_blank" rel="noopener"-->
    <form action="<?=PATH?>/call_view/med_call_view.php" method="post" target=admRightFrame title="�������� ������">
        <input type="submit" name="Med_Call_View" value="�������� �������" class="enter_button" style="margin-top: 10px; width:  165px; color: wheat; background-color: darkgreen;">
    </form>

    <!--form action="< ?= PATH ?>/med_call.php?table_name='CALL_BASE'&anumber='89011234567'&bnumber='89163217802'&sc_agid='operator'&sc_call_id=112233&sc_project_id=123987" method="post" target=admRightFrame title="�������� ������...">
        <input type="submit" name="Give_Call" value="�������� ������..." style="margin-left: 3px; font-weight: bold; background-color: red;color: white; height: 25px">
    </form-->
</div>
</body>
</html>

