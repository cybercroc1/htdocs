<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);
require_once 'base.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./billing.css">
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>��������</title>
    <base href="/">
    <meta name="description" content="��������.">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .cross { cursor: crosshair; }
        .help { cursor: help; }
    </style>
</head>

<body style="margin-left: 2px;">
<script type="application/javascript">
    function OpenIncomingCall(path)
    {
        var myWindow;
        <?php if (DB_OCI) { ?>
        //$url="http://med.wilstream.ru/med_call.php?&anumber=".$aon."&aon_for_backcall=".$aon_for_backcall."&bnumber=".$cdpn."&out_prefix=".$out_prefix."&oktell_srv_id=".$tel_server_id.
        //"&oktell_uid=".$uid."&sc_agid=".$agid."&sc_project_id=".$project_id."&sc_call_id=".$call_id."&oktell_idchain=".$thrid."&call_direction=".$call_direction;
        myWindow = window.open(path+"med_call.php?anumber=6022&bnumber=7884984&aon_for_backcall=7884984&out_prefix=7&oktell_srv_id=VG&oktell_uid=3B70B300-0B23-4FA0-80E8-9EAA06459CA8&oktell_idchain=4d89a8b0-d5d7-4fc8-9148-aef693d701ad&call_direction=in&sc_agid=�������_����&sc_project_id=41&sc_call_id=1111111",
            "MsgWindow", "width=720, height=600, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
        <?php } else { ?>
        myWindow = window.open(path+"med_call_my.php?anumber=6022&bnumber=5555555557&oktell_srv_id=VG&oktell_uid=3B70B300-0B23-4FA0-80E8-9EAA06459CA8&oktell_idchain=4d89a8b0-d5d7-4fc8-9148-aef693d701ad&call_direction=in&sc_agid=�������&sc_project_id=41&sc_call_id=11111111",
            "MsgWindow", "width=720, height=600, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
        <?php } ?>
        myWindow.focus();
    }
</script>

<div id='sidebar'>
    <?php if (isset($_SESSION['admin_med']) && $_SESSION['admin_med'] == 1) { ?>
        <div>
            <form action="<?=PATH?>/admin/admin_theme.php" method="post" target="admRightFrame" title="���� ������">
                <button name="Theme" class="enter_button">���� ������</button>
                <!--input type="hidden" name = "table_name" value="CALL_THEMES"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_service.php" method="post" target="admRightFrame" title="��������������� ������">
                <button name="Serv" class="enter_button">���������������<br>������</button>
                <!--input type="hidden" name = "table_name" value="SERVICES"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_service_detail.php" method="post" target="admRightFrame" title="����������� ������">
                <input type="submit" name="Serv_det" value="����������� ������" style="background-color: #339999" disabled>
                <!--input type="hidden" name = "table_name" value="SERVICE_DET"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/frames.php?page=1" method="post" target="admRightFrame" title="���������� (������)">
                <button name="Ist_Auto" class="enter_button">����������<br>(������)</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_AUTO"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/frames.php?page=2" method="post" target="admRightFrame" title="��������� ������� (��������������)">
                <button name="Ist_Auto" class="enter_button">��������� �������<br>(��������������)</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_AUTO"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source_auto.php" method="post" target="admRightFrame" title="��������� ������� (Auto)">
                <button name="Ist_Auto" class="enter_button">��������� �������<br>(Auto)</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_AUTO"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source_detail_new.php" method="post" target="admRightFrame" title="���������� ��������� (Auto)">
                <button name="Ist_Det_Auto" class="enter_button">���������� ��������� (Auto)</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_AUTO_DETAIL"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source.php" method="post" target="admRightFrame" title="��������� �������">
                <button name="Ist" class="enter_button">��������� �������</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_MAN"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_source_detail.php" method="post" target="admRightFrame" title="����������� ����������">
                <button name="Ist_Det" class="enter_button">����������� ����������</button>
                <!--input type="hidden" name = "table_name" value="SOURCE_MAN_DETAIL"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_department.php" method="post" target="admRightFrame" title="������������">
                <button name="Dep" class="enter_button">������������</button>
                <!--input type="hidden" name = "table_name" value="DEPARTAMENTS"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_user.php" method="post" target="admRightFrame" title="������������">
                <button name="Usr" class="enter_button">������������</button>
                <!--input type="hidden" name = "table_name" value="USERS"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_user_dep.php" method="post" target="admRightFrame" title="������������ �� �������������">
                <button name="Usr_Dep" class="enter_button">������������<br>�� �������������</button>
                <!--input type="hidden" name = "table_name" value="USER_DEP_ALLOC"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_access_dep_new.php" method="post" target="admRightFrame" title="����� �������">
                <button name="Access_Dep" class="enter_button">����� �������</button>
                <!--input type="hidden" name = "table_name" value="ACCESS_DEP"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_report_acc.php" method="post" target="admRightFrame" title="������ � �������">
                <button name="Report_Access" class="enter_button">������ � �������</button>
                <!--input type="hidden" name = "table_name" value="CALL_REPORTS_ACC"-->
            </form>
        </div>
        <div>
            <form action="<?=PATH?>/admin/admin_clinic.php" method="post" target="admRightFrame" title="�������">
                <button name="Hospital" class="enter_button">�������</button>
                <!--input type="hidden" name = "table_name" value="HOSPITALS"-->
            </form>
        </div>
        <div style="padding-top: 5px">
            <!--form action="< ?=PATH?>/med_regexr.php" method="post" target="admRightFrame" title="�������"-->
            <form action="<?=PATH?>/leadcollector/frames.php" method="post" target="admRightFrame" title="�������">
                <button name="Parsing" class="enter_button">�������</button>
                <!--input type="hidden" name = "table_name" value=""-->
            </form>
        </div>

        <form action="" method="post">
            <?php $to_path = '"'.PATH.'/"' ?>
            <input type="button" name="Give_Call" value=" �������� ������ " onclick='OpenIncomingCall(<?=$to_path?>)' class="cross" style="margin-left: 3px; width: 150px; font-weight: bold; background-color: red;color: white; height: 25px">
        </form>
    <?php } ?>

    <?php if (DB_OCI) { ?>
        <form action="<?=PATH?>/call_view/med_call_view.php" method="post" target="admRightFrame" title="�������� ������">
            <input type="submit" name="Med_Call_View" id="Med_Call_View" value="�������� ������" class="enter_button" style="margin-top: 10px; width: 150px; color: wheat; background-color: darkgreen;">
        </form>
    <?php } else { ?>
        <form action="<?=PATH?>/call_view/med_call_view_my.php" method="post" target="admRightFrame" title="�������� ������">
            <input type="submit" name="Med_Call_View" id="Med_Call_View" value="�������� ������" class="enter_button" style="margin-top: 10px; width: 150px; color: wheat; background-color: darkgreen;">
        </form>
    <?php } ?>

    <?php if (isset($_SESSION['admin_med']) && $_SESSION['admin_med'] == 1) { ?>
    <div style="padding-top: 5px">
        <form action="<?=PATH?>/call_view/med_export.php?start_date=<?=date('d.m.Y')?>&end_date=<?=date('d.m.Y')?>" method="post" target="admRightFrame" title="������">
            <button name="Reports" class="enter_button" style="background-color: chocolate;">������</button>
        </form>
    </div>
    <?php } ?>
</div>
</body>
</html>

