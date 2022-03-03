<?php
require_once 'med/check_auth.php';

extract($_REQUEST);
require_once '../funct.php';

?>

<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <link rel="stylesheet" type="text/css" href="../billing.css">
    <?php if (TRUE == ENCODE_UTF) { ?>
        <meta http-equiv=Content-Type content="text/html; charset=utf-8" />
    <?php } else { ?>
        <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <?php } ?>
    <title>�������� �������</title>
    <meta name="description" content="�������� �������">
    <script src="../js/jquery.datetimepicker.full.js"></script>
<?php
if (isset($getdet))
{
    if ($getdet != -1)
        $strfilt = "SOURCE_TYPE = ".$getdet;
    else $strfilt = NULL;
    $nSourceAuto = GetData::GetSourceAuto($strfilt, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
    if (++$nSourceAuto < 9) { $nSourceAuto *= 19; $scroll = ' overflow-y: unset'; }
    else { $nSourceAuto = 175; $scroll = ''; }
    $sel = "<select multiple id=\"S_Auto\" name=\"S_Auto[]\" style=\"margin-bottom: 5px; width: 100%; height: " . $nSourceAuto . "px;" . $scroll . "\">";
    $sel .= "<option selected=\"selected\" value=\"" . SOURCE_ALL . "\">��� ���������</option>";
    if ($nSourceAuto > 0) {
        if (DB_OCI) {
            foreach (GetData::$array_source_auto as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                if (strstr($value['NAME'],'"'))      $value['NAME'] = str_replace('"','\"',$value['NAME']);
                elseif (strstr($value['NAME'],'\'')) $value['NAME'] = str_replace('\'','\"',$value['NAME']);
                if (DEVICE_PHONE == $value['SOURCE_TYPE'])
                    $sel .= "<option value=\"".$value['ID']."\">(".$value['BNUMBER'].")&nbsp;".$value['NAME']."</option>";
                else $sel .=  "<option value=\"".$value['ID']."\">(".DEVICES[$value['SOURCE_TYPE']].")&nbsp;".$value['NAME']."</option>";

            }
        } else {
            foreach(GetData::$array_source_auto as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value[2] = iconv('utf-8', 'windows-1251', $value[2]);
                $sel .=  "<option value=\"".$value[0]."\">".$value[1]."</option>";
            }
        }
    }
    $sel .= "</select>";

    echo "<script>parent.document.getElementById('S_AutoSel').innerHTML='" . $sel . "';</script>";
    echo "<script>elem = parent.document.getElementById('S_Auto'); if (elem) elem.focus();</script>";

    exit();
}
?>
</head>

<body>
<?php if (in_array($_SESSION['login_id_med'],COST_EDIT)) { ?>
<div style="display: flex;">
    <form action="../admin/frames.php?page=1" method="post" target="_blank" title="���������� (������)" style="float: left;">
        <button name="Ist_Auto1" class="enter_button">����������<br>(������)</button>
    </form>
    <form action="../admin/frames.php?page=2" method="post" target="_blank" title="��������� ������� (��������������)">
        <button name="Ist_Auto2" class="enter_button">��������� �������<br>(��������������)</button>
    </form>
    <form action="../admin/admin_access_dep_new.php" method="post" target="_blank" title="����� �������">
        <button name="Access_Dep" class="enter_button">����� �������</button>
    </form>
</div>
<?php } ?>

<form action="export_xlsx.php" method="POST">
    <h1 style='margin-bottom: 0; margin-top: 0'><label for='ReportId'>&nbsp;������������ ������:&nbsp;</label>
    <?php
    if (GetData::GetAccess($_SESSION['login_id_med']) > 0)
        $data_acc_arr = explode(',', $_SESSION['data_acc']);
    else $data_acc_arr = array();

    //var_dump($_GET);
    if (GetData::GetReports(NULL) > 0) {
        echo "<select id='ReportId' name='ReportId' title='������' style='font-size: 17px; background-color:".needs."' onchange='ReportChanged();'>";
        echo "<option selected=\"selected\" value='0'>�������� �����</option>";
        foreach(GetData::$array_reports as $key => $value) {
            if($value["SCRIPT_NAME"]<>'') {
				echo "<option value='new_interface|".$value['ID']."'>".$value['NAME']."</option>";
			}
			else {
			    if(EXPORT_VISITS != $value['ID'] && APPLELOVERS == $_SESSION['login_id_med'])
			        continue;
				echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
			}
		}
		echo "</select>";
    }

    if (99 == $_SESSION['login_id_med'] || 1 == $_SESSION['login_id_med']) {//��������� ��� Mac OS
        if (strstr($_SERVER['HTTP_USER_AGENT'],'Macintosh')) {
            $bMac = TRUE;
            echo '<br/>' . $_SERVER['HTTP_USER_AGENT'] . '<br/>';
        }
        else $bMac = FALSE;
        //echo '<br/>'.php_uname();
        //echo '<br/>'.$_SERVER['HTTP_USER_AGENT'] .'<br/>';
    }
    ?>
    </h1>
    <script type="text/javascript">
    function ReportChanged() {
        var report_sel = document.getElementById('ReportId').value;
        var start_date = document.getElementById('rep_start_date').value;
        var end_date = document.getElementById('rep_end_date').value;
		if('new' === report_sel.substring(0,3)) {
			rep_id_arr=report_sel.split("|");
			this.location='../reports/frame.php?report_id='+rep_id_arr[1]+'&start_date='+start_date+'&end_date='+end_date;
		} else {
            //this.location='med_export.php?start_date='+start_date+'&end_date='+end_date;
        }
        
		//elem = document.getElementById('rep_start_date');
        document.getElementById('Export_but').style.visibility = 'hidden';
        if ('0' === report_sel) {
            document.getElementById('ReportId').style.backgroundColor = '<?=needs?>';
            document.getElementById('Export_xlsx').style.visibility = 'hidden';
            //elem = document.getElementById('ReportId');
        }
        else {
            document.getElementById('ReportId').style.backgroundColor = 'white';
            document.getElementById('Export_xlsx').style.visibility = 'visible';
        }

        if (<?=EXPORT_VISITS?> == report_sel) {
            document.getElementById('FirstPart').style.visibility = 'hidden';
            //document.getElementById('FirstPart').style.position = 'absolute';
            if(<?=APPLELOVERS?> != <?=$_SESSION['login_id_med']?>)
            {
                document.getElementById('Providers').style.visibility = 'visible';
                document.getElementById('Providers').style.position = 'inherit';
            }
            document.getElementById('repeates').style.visibility = 'visible';
            document.getElementById('repeates').style.position = 'inherit';
        }
        else {
            document.getElementById('Providers').style.visibility = 'hidden';
            document.getElementById('Providers').style.position = 'absolute';
            document.getElementById('repeates').style.visibility = 'hidden';
            document.getElementById('repeates').style.position = 'absolute';
        }
        document.getElementById('DateTypes').style.visibility = 'hidden';
        document.getElementById('DateTypes').style.position = 'absolute';
        document.getElementById('StatusIdT').style.visibility = 'hidden';
        document.getElementById('StatusId').style.visibility = 'hidden';
        document.getElementById('StatusId').style.position = 'absolute';
        if (<?=USER_ADMIN?> != <?=$_SESSION['user_role']?> || <?=EXPORT_VISITS?> == report_sel) {
            document.getElementById('FirstPart').style.visibility = 'hidden';
            //document.getElementById('FirstPart').style.position = 'absolute';
        } else
        {
            document.getElementById('FirstPart').style.visibility = 'visible';
            document.getElementById('FirstPart').style.position = 'inherit';
        }
        document.getElementById('ThirdPart').style.visibility = 'hidden';
        document.getElementById('ThirdPart').style.position = 'absolute';
        document.getElementById('FourthPart').style.visibility = 'hidden';
        document.getElementById('FourthPart').style.position = 'absolute';
        document.getElementById('mistakes').style.visibility = 'hidden';
        document.getElementById('mistakes').style.position = 'absolute';
        document.getElementById('PersonalPart').style.visibility = 'hidden';
        document.getElementById('PersonalPart').style.position = 'absolute';
        document.getElementById('unused').style.visibility = 'hidden';
        document.getElementById('unused').style.position = 'absolute';
        if (<?=EXPORT_CALL?> == report_sel || <?=EXPORT_CALL_SEC?> == report_sel || <?=EXPORT_OPERATOR_SEC_CALL?> == report_sel) {
            document.getElementById('FirstPart').style.visibility = 'visible';
            document.getElementById('FirstPart').style.position = 'inherit';
            document.getElementById('Export_but').style.visibility = 'visible';
            if (<?=EXPORT_CALL_SEC?> == report_sel || <?=EXPORT_OPERATOR_SEC_CALL?> == report_sel) {
                if (<?=EXPORT_OPERATOR_SEC_CALL?> == report_sel)
                    document.getElementById('Export_but').style.visibility = 'hidden';
                document.getElementById('ServiceId').style.visibility = 'hidden';
                document.getElementById('ServiceIdT').style.visibility = 'hidden';
                document.getElementById('sel_serv_det').style.visibility = 'hidden';
                document.getElementById('sel_serv_detT').style.visibility = 'hidden';
                document.getElementById('ServiceId').style.position = 'absolute';
                document.getElementById('sel_serv_det').style.position = 'absolute';
            } else {
                document.getElementById('ServiceId').style.visibility = 'visible';
                document.getElementById('ServiceIdT').style.visibility = 'visible';
                document.getElementById('sel_serv_det').style.visibility = 'visible';
                document.getElementById('sel_serv_detT').style.visibility = 'visible';
                document.getElementById('ServiceId').style.position = 'inherit';
                document.getElementById('sel_serv_det').style.position = 'inherit';
            }
            if (<?=EXPORT_OPERATOR_SEC_CALL?> != report_sel) {
                document.getElementById('SecondPart').style.visibility = 'visible';
                document.getElementById('SecondPart').style.position = 'inherit';
                document.getElementById('DateTypes').style.visibility = 'visible';
                document.getElementById('DateTypes').style.position = 'inherit';
            } else {
                document.getElementById('SecondPart').style.visibility = 'hidden';
                document.getElementById('SecondPart').style.position = 'absolute';
            }
            document.getElementById('StatusIdT').style.visibility = 'visible';
            document.getElementById('StatusId').style.visibility = 'visible';
            document.getElementById('StatusId').style.position = 'inherit';
            document.getElementById('mistakes').style.position = 'inherit';
            if (<?=STATUS_ERROR?> == document.getElementById("StatusId").value) {
                document.getElementById('mistakes').style.visibility = 'visible';
            }

            if (<?=EXPORT_CALL_SEC?> == report_sel || <?=EXPORT_OPERATOR_SEC_CALL?> == report_sel) {
                document.getElementById('FourthPart').style.visibility = 'visible';
                document.getElementById('FourthPart').style.position = 'inherit';
                document.getElementById('LastPart').style.visibility = 'hidden';
                document.getElementById('LastPart').style.position = 'absolute';
            } else {
                document.getElementById('ThirdPart').style.visibility = 'visible';
                document.getElementById('ThirdPart').style.position = 'inherit';
                document.getElementById('LastPart').style.visibility = 'visible';
                document.getElementById('LastPart').style.position = 'inherit';
            }
        }
        else if (<?=EXPORT_OPERATOR?> == report_sel || <?=EXPORT_OPERATOR_ALL?> == report_sel ||
                <?=EXPORT_OPERATOR_SEC?> == report_sel ) {
            document.getElementById('SecondPart').style.visibility = 'hidden';
            document.getElementById('SecondPart').style.position = 'absolute';
            document.getElementById('ServiceId').style.visibility = 'hidden';
            document.getElementById('ServiceIdT').style.visibility = 'hidden';
            document.getElementById('sel_serv_det').style.visibility = 'hidden';
            document.getElementById('sel_serv_detT').style.visibility = 'hidden';
            document.getElementById('ServiceId').style.position = 'absolute';
            document.getElementById('sel_serv_det').style.position = 'absolute';
            if (<?=EXPORT_OPERATOR_SEC?> == report_sel) {
                document.getElementById('FourthPart').style.visibility = 'visible';
                document.getElementById('FourthPart').style.position = 'inherit';
                document.getElementById('unused').style.visibility = 'visible';
                document.getElementById('unused').style.position = 'inherit';
            } else { //  EXPORT_OPERATOR || EXPORT_OPERATOR_ALL
                document.getElementById('ThirdPart').style.visibility = 'visible';
                document.getElementById('ThirdPart').style.position = 'inherit';
            }
            document.getElementById('LastPart').style.visibility = 'hidden';
            document.getElementById('LastPart').style.position = 'absolute';
        }
        else if (<?=EXPORT_EFFECT?> == report_sel || <?=EXPORT_EFFECT_ISH?> == report_sel ||
                <?=EXPORT_EFFECT_IDYN?> == report_sel || <?=EXPORT_BILLING?> == report_sel) {
            document.getElementById('FirstPart').style.visibility = 'visible';
            document.getElementById('FirstPart').style.position = 'inherit';
            document.getElementById('ServiceId').style.visibility = 'visible';
            document.getElementById('ServiceIdT').style.visibility = 'visible';
            document.getElementById('sel_serv_det').style.visibility = 'visible';
            document.getElementById('sel_serv_detT').style.visibility = 'visible';
            document.getElementById('ServiceId').style.position = 'inherit';
            document.getElementById('sel_serv_det').style.position = 'inherit';
            if (<?=EXPORT_EFFECT?> == report_sel || <?=EXPORT_EFFECT_IDYN?> == report_sel ||
                <?=EXPORT_BILLING?> == report_sel)
            {
                document.getElementById('SecondPart').style.visibility = 'visible';
                document.getElementById('SecondPart').style.position = 'inherit';
            }
            else if (<?=EXPORT_EFFECT_ISH?> == report_sel) {
                document.getElementById('SecondPart').style.visibility = 'hidden';
                document.getElementById('SecondPart').style.position = 'absolute';
                document.getElementById('PersonalPart').style.visibility = 'visible';
                document.getElementById('PersonalPart').style.position = 'inherit';
            }
            if (<?=EXPORT_BILLING?> == report_sel)
            {
                document.getElementById('Export_xlsx').style.visibility = 'hidden';
                document.getElementById('Export_but').style.visibility = 'visible';
            }
            document.getElementById('LastPart').style.visibility = 'hidden';
            document.getElementById('LastPart').style.position = 'absolute';
        }
        else {
            document.getElementById('SecondPart').style.visibility = 'hidden';
            document.getElementById('SecondPart').style.position = 'absolute';
            document.getElementById('LastPart').style.visibility = 'hidden';
            document.getElementById('LastPart').style.position = 'absolute';
        }
        //elem.focus();
    }
    </script>

    <h2>����:&nbsp;
        <?php
        if (isset($start_date)) $_SESSION['reports']['start_date'] = $start_date;
        elseif ($_SESSION['reports']['start_date']) $start_date = $_SESSION['reports']['start_date'];
        if (isset($end_date)) $_SESSION['reports']['end_date'] = $end_date;
        elseif ($_SESSION['reports']['end_date']) $end_date = $_SESSION['reports']['end_date'];

        echo '� <input type="text" name="rep_start_date" id="rep_start_date" autocomplete="off" style="width: 7em;" value="'.(!empty($start_date) ? $start_date : "").'" />';
        echo '�� <input type="text" name="rep_end_date" id="rep_end_date" autocomplete="off" style="width: 7em;" value="'.(!empty($end_date) ? $end_date : "").'"/>';
        echo "<div id='DateTypes' style='visibility: hidden;'><label for='DateType'>������:&nbsp;</label>";
        echo "<select id='DateType' name='DateType'>";
        echo "<option selected=\"selected\" value='1'>�� ���� ��������� ������</option>";
        echo "<option value='2'>�� ���� ���������� ���������</option>";
        echo "</select></div>";
//..position: absolute;
        echo "<div id='FirstPart' style='visibility: hidden; display: table; margin-top: 5px; margin-bottom: 5px; max-width: 695px; width: 88%'>";
        if (USER_VIEW == $_SESSION['user_role'])
            echo "<div id='ServStat' style='float: left; width: 59%'>";
        else echo "<div id='ServStat' style='float: left; width: 65%'>";
        echo "<label for='ServiceId' id='ServiceIdT' style='float: left;'>������:&nbsp;</label>";
        $nServices = GetData::GetServices(FALSE,FALSE,NULL,(USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE))+1;
        echo "<select multiple id='ServiceId' name='ServiceId[]' size=".$nServices." style='margin-top: 3px; float: left; overflow-y: unset'>";
        echo "<option selected=\"selected\" value='".SERVICE_ALL."'>��� ������</option>";
        if ($nServices > 0) {
            if (DB_OCI) {
                foreach(GetData::$array_services as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    if (SERVICE_ALL == $value['ID'])
                        echo "<option selected='selected' value='".$value['ID']."'>".$value['NAME']."</option>";
                    else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            } else {
                foreach(GetData::$array_services as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                    if (SERVICE_ALL == $value[0])
                        echo "<option selected='selected' value='".$value[0]."'>".$value[1]."</option>";
                    else echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
        }
        echo "</select>";

        //�����������
        echo "<label for='sel_serv_det' id='sel_serv_detT' style='float: left;'>���������:&nbsp;</label>";
        echo "<select multiple id='sel_serv_det' name='sel_serv_det[]' size=10 style='max-width:248px;'>";
        echo "<option selected value='-1'>��� ���������</option>";
        /*$sql="select s.id,s.service_id,s.name from SERVICE_DET s
where deleted is null and id > 0
".($_SESSION['user_role']==1?"":"and service_id in (select decode(ad.service_id,-1,s.id,ad.service_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
            " order by s.name";
        $q=OCIParse($c,$sql);
        OCIExecute($q);
        while(OCIFetch($q)) {
            echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"NAME")."</option>";
            $_SESSION['reports']['serv_det'][OCIResult($q,"ID")]=OCIResult($q,"NAME");
        }*/
        $nServ_Det = GetData::GetServiceDetails(NULL,SERVICE_NOT,FALSE,(USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        foreach(GetData::$arr_service_det as $key => $value) {
            if (TRUE == ENCODE_UTF)
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            if (SERVICE_ALL == $value['ID'])
                echo "<option selected='selected' value='".$value['ID']."'>".$value['NAME']."</option>";
            else {
                if ($nServices > 2) {
                    switch ($value['SERVICE_ID']) {
                        case SERVICE_STOM: $startstr = '(����.) '; break;
                        case SERVICE_KOSM: $startstr = '(����.) '; break;
                        case SERVICE_GINE: $startstr = '(�����.) '; break;
                        case SERVICE_PLAS: $startstr = '(�����.) '; break;
                        case SERVICE_TRIH: $startstr = '(����.) '; break;
                        default: $startstr = '';
                    }
                    echo "<option value='".$value['ID']."'>".$startstr.$value['NAME']."</option>";
                }
                else echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        echo "</select></br>";

        echo "<label for='StatusId' id='StatusIdT' style='float: left;'>&nbsp;������:&nbsp;</label>";
        echo "<select multiple id='StatusId' name='StatusId[]' title='������' style='margin-left: 5px; height: 137px; overflow-y: hidden;'>";
        //if (!in_array($_SESSION['login_id_med'],WRITE_ONLY))
            echo "<option selected=\"selected\" value='-1'>��� �������</option>";
        if (GetData::GetMedStatus("ID=" . STATUS_CALL_BACK . " or ID >= " . STATUS_CALL_STOP . " and ID <= " . STATUS_NOT_COME, false, FALSE) > 0) {
            //sort($_POST['array_status']);
            sort(GetData::$array_status);
            foreach (GetData::$array_status as $key => $value) {
                if (USER_VIEW == $_SESSION['user_role'] &&
                    (STATUS_NOT_COME == $value['ID'] || STATUS_CL_CANCEL == $value['ID'])) continue;
                //if (in_array($_SESSION['login_id_med'],WRITE_ONLY) && STATUS_CLINIC != $value['ID']) continue;
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                /*if (in_array($_SESSION['login_id_med'],WRITE_ONLY))
                    echo "<option value='".$value['ID']."' selected='selected'>".$value['NAME']."</option>";
                else*/ echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        echo "</select>";
        echo "</div></br>"; // ServStat

        echo "<div id='mistakes' style='float: right; width: 34%; visibility: hidden'>";
        echo "<label for='status_det' id='status_detT' style='float: left'>�������:&nbsp;</label>";
        echo "<select multiple id='status_det' name='status_det[]' style='height: 137px; overflow-y: hidden'>";
        if (GetData::GetMedStatusDet(NULL, STATUS_ERROR, FALSE) > 0) {
            echo "<option selected=\"selected\" value='-1'>��� �������</option>";
            foreach (GetData::$array_status_det as $key => $value) {
                if (TRUE == ENCODE_UTF)
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        echo "</select>";
        echo "</div>"; // mistakes
        echo "</div>"; // FirstPart

        echo "<div id='Providers' style='visibility: hidden; position: absolute; display: table; width: 98%; margin-top: 5px; margin-bottom: 5px'>";
        GetData::GetProviders(TRUE);
        echo "<label for='Providers' style='float: left;'>���������:&nbsp;</label>";
        //echo "<select id='Providers' name='Providers' onchange='ifr_all.location=\"admin_supplier_edit.php?getdet=\"+this.value'>";
        echo "<select multiple id='Providers' name='Providers[]' style='height: 295px;'>"; // overflow-y: hidden
        echo "<option value='".SOURCE_ALL."'>��� ����������</option>";
        foreach (GetData::$array_providers as $key => $value) {
            echo "<option value='" . $value["ID"] . "'>" . $value["SUP_NAME"] . "</option>";
        }
        echo "</select>";
        //echo '<script>$("#Providers").prop("selectedIndex",0).change();</script>';
        echo "<script>$('#Providers').val('".SOURCE_ALL."').change();</script>";
        echo "</div>"; // Providers

        echo "<div id='SecondPart' style='visibility: hidden; position: absolute; display: table; width: 98%; margin-top: 5px; margin-bottom: 5px'>";
        echo "<iframe name='ifr_all' style='display: none; width: 95%;'></iframe>";
        echo "<label for='S_Type'>��� ��������� �������:&nbsp;</label>";
        echo "<select id='S_Type' name='S_Type' onchange='ifr_all.location=\"med_export.php?getdet=\"+this.value'>";
        if (GetData::GetSourceType(FALSE, (USER_USER != $_SESSION['user_role'] ? FALSE : TRUE)) > 0)
        {
            if (DB_OCI) {
                if (count(GetData::$array_stype) > 1)
                    echo "<option value='-1'>��� ����</option>";
                foreach(GetData::$array_stype as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
            else {
                foreach(GetData::$array_stype as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
        }
        echo "</select><br/>";
        echo '<script>$("#S_Type").prop("selectedIndex",0).change();</script>';

        /*echo "<label for='Reservoir' style='float: left;'>�������� �������:&nbsp;</label>";
        $nSources = GetData::GetIstochnik(TRUE, FALSE, "instr(in_dep, '-1') != 0", (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        if (++$nSources < 9) { $nSources *= 19; $scroll = ' overflow-y: unset'; }
        else { $nSources = 175; $scroll = '';}
        if ($nSources > 0) {
            echo "<select multiple id='Reservoir' name='Reservoir[]' style='height: " . $nSources . "px;" . $scroll . "' title='�������� �������'>";
            foreach(GetData::$array_istochnik as $key => $value) {
                if (TRUE == ENCODE_UTF) {
                    $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    $value['DETAIL'] = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                }
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
            echo "</select>";
            echo "<script>$('#Reservoir').val('".SOURCE_ALL."').change();</script>";
        }*/

        echo "<label for='S_Auto'>�������� (����):</label>";
        echo "<div id='S_AutoSel'>&nbsp;</div>";
/*        $nSourceAuto = GetData::GetSourceAuto(NULL, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        if (++$nSourceAuto < 9) { $nSourceAuto *= 19; $scroll = ' overflow-y: unset'; }
        else { $nSourceAuto = 175; $scroll = ''; }
        echo "<select multiple id='S_Auto' name='S_Auto[]' style='margin-bottom: 5px; width: 100%; height: " . $nSourceAuto . "px;" . $scroll . "'>";
        echo "<option selected=\"selected\" value='" . SOURCE_ALL . "'>��� ���������</option>";
        if ($nSourceAuto > 0) {
            if (DB_OCI) {
                foreach(GetData::$array_source_auto as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                    if (DEVICE_PHONE == $value['SOURCE_TYPE'])
                        echo "<option value='".$value['ID']."'>(".$value['BNUMBER'].")&nbsp;".$value['NAME']."</option>";
                    else echo "<option value='".$value['ID']."'>(".DEVICES[$value['SOURCE_TYPE']].")&nbsp;".$value['NAME']."</option>";

                }
            } else {
                foreach(GetData::$array_source_auto as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[2] = iconv('utf-8', 'windows-1251', $value[2]);
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
        }
        echo "</select>";*/
        echo "</div>"; // SecondPart

        echo "<div id='PersonalPart' style='visibility: hidden; position: absolute; display: table; margin-top: 5px;'>";
        $nSources = GetData::GetSourceAutoDetail(FALSE, FALSE, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
        if (++$nSources < 9) { $nSources *= 19; $scroll = ' overflow-y: unset'; }
        else { $nSources = 175; $scroll = '';}
        echo "<label for='Reservoir_new' style='float: left;'>�������� ������� (���.):&nbsp;</label>";
        echo "<select multiple id='Reservoir_new' name='Reservoir_new[]' style='height: " . $nSources . "px;" . $scroll . "' title='�������� ������� (���.)'>";
        echo "<option selected=\"selected\" value='-1'>��� ���������</option>";
        foreach (GetData::$array_sa_detail as $key => $value) {
            if (TRUE == ENCODE_UTF)
                $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
            echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
        }
        echo "</select><br/>";
        echo "<script>$('#Reservoir_new').val('".SOURCE_ALL."').change();</script>";
        echo "</div>"; // PersonalPart

        echo "<div id='ThirdPart' style='visibility: hidden; position: absolute; display: table; margin-top: 5px;'>";
        $strfilt = "(ROLE_ID = " . USER_USER . " or ROLE_ID = " . USER_SUPER .")";
        $nUsers = GetData::GetUsersDep(FALSE, $strfilt, NULL, 'not');
        if (++$nUsers < 9 ) { $nUsers *= 19; $scroll = ' overflow-y: unset'; }
        else { $nUsers = 175; $scroll = ''; }
        echo "<label for='UserId' style='float: left;'>��������:&nbsp;</label>";
        echo "<select multiple id='UserId' name='UserId[]' style='height: ".$nUsers."px;".$scroll."'>";
        echo "<option selected=\"selected\" value='-1'>��� ���������</option>";
        if ($nUsers > 0) { // dep_id or user_id of Supervise ?
            if (DB_OCI) {
                foreach(GetData::$array_userd as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                    echo "<option value='".$value['ID']."'>".$value['FIO']."</option>";
                }
            } else {
                foreach(GetData::$array_userd as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
        }
        echo "</select>";
        echo "</div>"; // ThirdPart

        echo "<div id='FourthPart' style='visibility: hidden; position: absolute; display: table; margin-top: 5px;'>";
        //$strfilt = "(ROLE_ID = " . USER_USER . " or ROLE_ID = " . USER_SUPER .")";
        //$nUsers = GetData::GetUsersDep(FALSE, $strfilt, NULL, 'not');
        if (!isset($_SESSION['sec_chance_arr'])) {
            GetData::GetSecondChance();
            $_SESSION['sec_chance_arr'] = GetData::$arr_sec_chance;
        }
        $selectstr = "SELECT DISTINCT ID, FIO FROM USERS WHERE ID in (".implode(',',$_SESSION['sec_chance_arr']).") ORDER BY FIO";
        $query = OCIParse(GetData::GetConnect(), $selectstr);
        OCIExecute($query, OCI_DEFAULT);
        $nUsers = OCI_Fetch_All($query, $array_spec_call,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
        oci_free_statement($query);

        if (++$nUsers < 9 ) { $nUsers *= 19; $scroll = ' overflow-y: unset'; }
        else { $nUsers = 175; $scroll = ''; }
        echo "<label for='UserIdSpec' style='float: left;'>��������:&nbsp;</label>";
        echo "<select multiple id='UserIdSpec' name='UserIdSpec[]' style='height: ".$nUsers."px;".$scroll."'>";
        echo "<option selected=\"selected\" value='-1'>��� ���������</option>";
        if ($nUsers > 0) { // dep_id or user_id of Supervise ?
            if (DB_OCI) {
                foreach ($array_spec_call as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                    echo "<option value='".$value['ID']."'>".$value['FIO']."</option>";
                }
            } else {
                foreach ($array_spec_call as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                    echo "<option value='".$value[0]."'>".$value[1]."</option>";
                }
            }
        }
        echo "</select>";
        echo "</div>"; // FourthPart

        echo "<div id='LastPart' style='visibility: hidden; position: absolute;'>";
        if (USER_VIEW != $_SESSION['user_role'])
            echo "<span>������ ��������������:&nbsp;<input type=checkbox id='not_sent' name='not_sent' title='��� �� ����������'></span><br/>";
        echo "<span>�������� ���������:&nbsp;<input type=checkbox id='all_type' name='all_type' title='�� ������ �����������'></span>";
        echo "</div>";
        echo "<div id='repeates' style='visibility: hidden; position: absolute;'>
�������� �����:&nbsp;<input type=checkbox id='add_repeat' name='add_repeat' title='� ������ ��������� ��������'></div>";

        $check_unused = "select count(*) from CALL_BASE 
where status_id < 99 and service_id = ".SERVICE_STOM." and date_second_chance is not null and second_fio_id is null and date_call > '31.05.2019'";
        $query = OCIParse(GetData::GetConnect(), $check_unused);
        OCIExecute($query, OCI_DEFAULT);
        $Unused = OCI_Fetch_Array($query);
        echo "<div id='unused' style='visibility: hidden; position: absolute'>������������: ".$Unused[0]." </div>";
        ?>
    </h2><br/>
    <input type="submit" name="Export_xlsx" id="Export_xlsx" value="������������ �����" class="send_button" style="visibility: hidden"/>
    <input type="submit" name="Export_but" id="Export_but" value="������� � csv" class="send_button" style="visibility: hidden"/>
    <!--input type="hidden" name="export_type" value="< ?php echo $export_type; ?>"/-->
</form>

<script type="text/javascript">
    var select = document.getElementById("StatusId");
    select.onchange = function()
    {
        if (<?=STATUS_ERROR?> == this.value) {
            document.getElementById('mistakes').style.visibility = 'visible';
        }
        else {
            document.getElementById('mistakes').style.visibility = 'hidden';
        }
    };
</script>

<script type="text/javascript">
    $('#rep_start_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
    });
</script>
<script type="text/javascript">
    $('#rep_end_date').datetimepicker({
        format: 'd.m.Y',
        lang: 'ru',
        timepicker: false
    });
</script>

</body>
</html>