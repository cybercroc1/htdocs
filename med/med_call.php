<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);

include("med/oktell_conn_string.php");
include("med/conn_string.cfg.php");
include("med/smtp_conf.php");
include("send_email.php");
include("phone_conv_single.php");

require_once "check_ip.php";
//if(!check_local_network($_SERVER['REMOTE_ADDR'])) {echo "<font color=red><b>����������� IP</b></font>"; exit();}

?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <?php 
		require_once 'funct.php'; 
	?>
    <link rel="stylesheet" type="text/css" href="./js/number-polyfill.css">
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>�������� ������</title>
    <base href="/">
    <meta name="description" content="�������� ������">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?=PATH?>/js/jquery.maskedinput.js"></script>
    <script src="<?=PATH?>/js/number-polyfill.min.js"></script>
    <script src="<?=PATH?>/js/number-polyfill.js"></script>
    <style> .error {color: #FF0000;} </style>

    <script>
        function open_call(base_id, texnari_id, sid) {
            if (base_id > 0) {
                window.close();
                var width = 850;
                var height = 750;
                //win = window.open("< ?=PATH?>/med_call_out.php?base_id="+base_id+"&texnari_id="+texnari_id, "med_call_"+base_id,
                win = window.open("<?=PATH?>/med_call_out.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid, "med_call_"+base_id,
                    "width=" + width + ", height=" + height + ",left=" + ((window.innerWidth - width)/2) + ",top=30, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
                win.focus();
            }
        }
    </script>

    <script>
    function OperatorSelected() {
        var elem = document.getElementById('OperatorsId');
        var arrval = elem.value.split(':');
        if (arrval[0] != -1) {
            elem.style.backgroundColor = 'white';
            if (document.getElementById('PurposeId').value != <?=THEME_NOT?> &&
                document.getElementById('ServiceId').value != <?=SERVICE_NOT?> &&
                document.getElementById('Reservoir') && document.getElementById('Reservoir').value != <?=SOURCE_NOT?>)
                document.getElementById('save_but').style.visibility = 'visible';
            document.getElementById('call_center').value = arrval[1];
            /*if (arrval[1] != '')
                document.getElementById('call_center').disabled = true;
            else document.getElementById('call_center').disabled = false;*/
            elem = document.getElementById('save_but');
        }
        else {
            elem.style.backgroundColor = '<?=needs?>';
            document.getElementById('save_but').style.visibility = 'hidden';
            document.getElementById('call_center').value = '';
        }
        if (elem) elem.focus();
    }
    </script>

    <?php
    //$user_id = 0; $role_id = USER_USER;
    if (GetData::GetCallByCallId((isset($sc_call_id) ? $sc_call_id : ''),(isset($oktell_idchain) ? $oktell_idchain : '')) > 0) {
        $existing_call = 1;
        $max_call = GetData::$arr_call_base['ID'];
        $reservoir = GetData::$arr_call_base['SOURCE_MAN_ID'];
        $service = GetData::$arr_call_base['SERVICE_ID'];
        $status = GetData::$arr_call_base['STATUS_ID'];
        //$getoper = GetData::$arr_call_base['SERVICE_ID'];
        //$user_id = GetData::$arr_call_base['FIO_ID'];

        /*$PurposeId = GetData::$arr_call_base['CALL_THEME_ID'];
        $source_man_det = GetData::$arr_call_base['SOURCE_MAN_DET_ID'];
        $ResultId = GetData::$arr_call_base['RESULT_ID'];
        $Result_det = GetData::$arr_call_base['RESULT_DET'];
        $fio = GetData::$arr_call_base['CLIENT_NAME'];
        $phone_mob_norm = GetData::$arr_call_base['PHONE_MOB'];
        $comment = GetData::$arr_call_base['COMMENTS'];*/
        //var_dump('Exist - '.$max_call. ' ������ - '.$status);
    } else {
        //var_dump('New one');
        $existing_call = 0;
    }

    if (isset($getoper)) {
        $need_self = 0;
        if (isset($user_id) && $user_id > 0 /*&& 0 == $existing_call*/) {
            // ���� ���� � ��������� ������, �� ��������� ��� ������������
            $sel_acc = "select ID from ACCESS_DEP where service_id = " . $getoper . " and departament_id in 
(select dep_id from USER_DEP_ALLOC where user_id = " . $user_id . ")";
            $query = OCIParse($c, $sel_acc);
            OCIExecute($query, OCI_DEFAULT);
            $objResult = OCI_Fetch_Row($query);
            $need_self = ($objResult ? 1 : 0);
        }
        ?>
        <script>
        var elem;
        if ( <?= $user_id ?> > 0 )
        {
            var selectobject = parent.document.getElementById("ResultId");
            if (selectobject) {
                for (var i = 0; i < selectobject.length; i++) {
                    if (selectobject.options[i].value == <?=RESULT_KC_SELF?> ) {
                        selectobject.remove(i);
                    }
                }
                if ( 1 == <?= $need_self ?> ) {
                    //var node = parent.document.createElement("Option"); // Create a <pption> node
                    //var textnode = parent.document.createTextNode("��������� ����"); // Create a text node
                    //node.appendChild(textnode);                         // Append the text to <li>
                    //parent.document.getElementById("ResultId").appendChild(node); // Append <option> to <select> with id="ResultId"
                    var option = new Option("��������� ����", <?=RESULT_KC_SELF?>, true, true);
                    selectobject.appendChild(option); // Append <option> to <select> with id="ResultId"
                    selectobject.style.backgroundColor = 'white';

                    elem = document.getElementById('KC_Number');
                    if (elem) elem.style.visibility = 'visible';
                }
                else {
                    //selectobject.style.backgroundColor = '< ?=needs?>';
                }
            }

            if (<?=RESULT_KC?> == selectobject.value /*&& 0 == < ?= $existing_call ?>*/ ) { // ���� ��� ������ ������� � ��
                if (parent.document.getElementById('save_but'))
                    parent.document.getElementById('save_but').style.visibility = 'hidden'; // ��� ������ ���������� ����������
                //parent.document.getElementById('call_center').value = ''; // TODO ��������� ����� ��������� � sc_call_id
            }
            else {
                if (parent.document.getElementById("PurposeId").value >= <?=THEME_INFO?> ||
                    <?=THEME_MED?> == parent.document.getElementById("PurposeId").value &&
                    <?=SERVICE_NOT?> != parent.document.getElementById("ServiceId").value &&
                    /*(< ?=SERVICE_STOM?> != parent.document.getElementById("ServiceId").value ||
                    < ?=STOM_NOT?> != parent.document.getElementById("ServiceStom").value) &&*/
                    document.getElementById('Reservoir') && <?=SOURCE_NOT?> != parent.document.getElementById("Reservoir").value &&
                    <?=RESULT_NOT?> != selectobject.value && (<?=RESULT_WAIT?> == selectobject.value ||
                    parent.document.getElementById("OperatorsId").value > 0)) {
                    parent.document.getElementById("save_but").style.visibility = "visible";
                } else {
                    parent.document.getElementById("save_but").style.visibility = "hidden";
                }
            }
        }
        </script>

        <?php
        if (SERVICE_NOT == $getoper) {
            echo "<script>parent.document.getElementById('ServiceId').style.backgroundColor = '" . needs . "';</script>";
            echo "<script>elem = parent.document.getElementById('ServiceId');</script>";

            echo "<script>parent.document.getElementById('assign_clT').innerHTML='&nbsp;������ �� �������!';</script>";
            echo "<script>parent.document.getElementById('assign_cl').innerHTML='';</script>";
        }
        else {
            /*if (SERVICE_STOM == $getoper) {
                echo "<script>elem = parent.document.getElementById('ServiceStom'); elem.style.visibility = 'visible';</script>";
            }
            else {*/
                //echo "<script>parent.document.getElementById('ServiceStom').style.visibility = 'hidden';</script>";
                echo "<script>elem = parent.document.getElementById('ServiceId'); if (elem) elem.style.backgroundColor = 'white';</script>";
                echo "<script>elem = parent.document.getElementById('Reservoir');</script>";
            //}

            if (true || !$existing_call) { // ������� ��������� ����� � ������� �� �����, ��� ��� � call_out
                $sel = "<select id=\"Reservoir\" name=\"Reservoir\" onchange=\"ifr1.location=\'" . PATH . "/med_call.php?getdet=\'+this.value\">";
                if (1 != $need_self)
                    $sel .= " style=\"background-color:" . needs . "\" ";
                $sel .= ">";
                $sel .= "<option value=\"" . SOURCE_NOT . "\">�������� �������� �������</option>";
                if (GetData::GetIstochnik(FALSE, FALSE, "(instr(in_dep, '" . $getoper . "') != 0)", FALSE) > 0) {
                    foreach (GetData::$array_istochnik as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $value['NAME'] = u8($value['NAME']);
                            $value['DETAIL'] = u8($value['DETAIL'] . ': ');
                        }
                        if (1 == $need_self && SOURCE_OTHER == $value['ID']) {
                            $sel .= "<option value=\"" . $value['ID'] . "\" selected=\"selected\">" . $value['NAME'] . "</option>";
                        } else {
                            $sel .= "<option value=\"" . $value['ID'] . "\">" . $value['NAME'] . "</option>";
                        }
                    }
                }
                $sel .= "</select>";

                echo "<script>var ins_sel = parent.document.getElementById('AllInOne'); if (ins_sel) ins_sel.innerHTML='';</script>";
                echo "<script>var ins_sel = parent.document.getElementById('AllSelect'); if (ins_sel) ins_sel.innerHTML='';</script>";
                echo "<script>var ins_sel = parent.document.getElementById('AllInOneIst'); if (ins_sel) ins_sel.innerHTML='�������� �������:&nbsp;';</script>";
                echo "<script>var ins_sel = parent.document.getElementById('SelectIst'); if (ins_sel) ins_sel.innerHTML='" . $sel . "';</script>";
            }

            if ( 1 == $need_self ) {
                // echo "<script>$('#Reservoir').val('".SOURCE_OTHER."').change();</script>";
                // echo '<script>$("#Reservoir").prop("selectedIndex",28).change();</script>';
                echo "<script>if (parent.document.getElementById('PurposeId').value != " . SERVICE_NOT . ") parent.document.getElementById('save_but').style.visibility = 'visible';</script>";
                echo "<script>elem = document.getElementById('comment');</script>";
            }

            $arr_filt = array('serv' => $getoper, 'ist' => -1); // ��� �������� �������� �� ���������
            if (GetData::GetUsersDep(FALSE, NULL, $arr_filt, 'mix') > 0) {
                $sel = "<select id=\"OperatorsId\" name=\"OperatorsId\" onchange=\"OperatorSelected();\" style=\"background-color:".($user_id > 0?'white':needs)."\">";
                $sel .= "<option value=\"-1\">�������� ���������</option>";
                $bAct = TRUE; $sel .= "<optgroup label=\"��������\">";
                foreach(GetData::$array_userd as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = u8($value['FIO']);
                    if ($bAct && 2 == $value['ACTIVE']) {
                        $bAct = FALSE;
                        $sel .= "</optgroup>";
                        $sel .= "<optgroup label=\"���������\">";
                    }
                    if ($user_id > 0 && $user_id == $value['ID']) {
                        $sel .= "<option value=\"" . $value['ID'] . ":".$value['PIN']."\" selected=\"selected\">" . $value['FIO'] . "</option>";
                    }
                    else $sel .= "<option value=\"" . $value['ID'] . ":".$value['PIN']."\">" . $value['FIO'] . "</option>";
                }
                $sel .= "</optgroup>";
            }
            else {
                $sel = "<select id=\"OperatorsId\" name=\"OperatorsId\" disabled style=\"color:".needs."\">";
                $sel .= "<option value=\"\">��� ��������� ����������</option>";
            }
            $sel .= "</select>";

            echo "<script>parent.document.getElementById('assign_clT').innerHTML='&nbsp;��������: ';</script>";
            echo "<script>parent.document.getElementById('assign_cl').innerHTML='&nbsp;" . $sel . "';</script>";
        }
        echo "<script>if (elem) elem.focus();</script>";

        exit();
    }

    if (isset($getdet))
    {
        if (SOURCE_NOT == $getdet)
            echo "<script>parent.document.getElementById('Reservoir').style.backgroundColor = '".needs."';</script>";
        else echo "<script>parent.document.getElementById('Reservoir').style.backgroundColor = 'white';</script>";

        $nrows = GetData::GetSourceDetail(FALSE, NULL, $getdet);
        if ($getdet < SOURCE_2GIS || $nrows > 0 /*SOURCE_BANNER_SUB == $getdet*/) { // � ��������� ������ ���
            if (SOURCE_FLAER == $getdet || SOURCE_CATALOG == $getdet ||
                SOURCE_FLAER_SUB == $getdet || SOURCE_FLAER_CAR == $getdet ||
                SOURCE_LIFT == $getdet || SOURCE_STOP == $getdet) {
                //$getdetailstr = "SELECT ID, NAME FROM SUBWAYS WHERE city = 1"; // ���� ������ ����� ������
                $getdetailstr = "SELECT ID, NAME FROM SUBWAYS";
                $strtitle = '������� �����';
            } elseif (SOURCE_SERT == $getdet) { //$getdetailstr = "SELECT ID, NAME FROM HOSPITALS";
                $getdetailstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ORDER BY hosp.CITY, hosp.NAME, serv.NAME";
                $strtitle = '����������';
            } else {
                $nrows = GetData::GetIstochnik(FALSE,FALSE,"ID = " . $getdet, FALSE);
                $strtitle = '�����������';
                if (isset(GetData::$array_istochnik)) {
                    foreach(GetData::$array_istochnik as $key => $value) {
                        $strtitle = $value['DETAIL'];
                    }
                }
                $getdetailstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id=" . $getdet . " and DELETED IS NULL";
            }
            if (TRUE == ENCODE_UTF)
                $strtitle = u8($strtitle);
            echo "<script>elem = parent.document.getElementById('AllInOne'); if (elem) elem.innerHTML='&nbsp;" . $strtitle . ": ';</script>";
            $i = 0;
            $sel = "<select id=\"DetailList\" name=\"DetailList\" style=\"background-color: ".needs."\">";
            $sel .= "<option value=\"\">�������� �����������</option>";
            $q = OCIParse($c, $getdetailstr);
            if (OCIExecute($q)) {
                while (OCIFetch($q)) {
                    $sel .= "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
                    $i++;
                }
            }
            if (SOURCE_SERT == $getdet) {
                $sel .= "<option value=".DETAILS_PROMO.">�� ����� � ����������</option>";
                $sel .= "<option value=".DETAILS_OTHER.">������</option>";
                $i += 2;
            } elseif (SOURCE_NOT != $getdet && SOURCE_COUPON != $getdet) {
                $sel .= "<option value=".DETAILS_AMNESY.">�� ������</option>";
                $i++;
            }
            $sel .= "</select>";
            if ($i == 0) {
                $sel = '-----';//'������ ������ �����������!';
            }
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='&nbsp;" . $sel . "';</script>";
            echo "<script>elem = parent.document.getElementById('DetailList'); if (elem) elem.focus();</script>";
            echo "<script type = 'text/javascript'> var sel = parent.document.getElementById('DetailList');
                if (sel) { sel.onchange = function() {
                    if (0 == sel.value) { sel.style.backgroundColor = '".needs."'; 
                        parent.document.getElementById('save_but').style.visibility = 'hidden';
                    } 
                    else { sel.style.backgroundColor = 'white'; } 
                    var elem = parent.document.getElementById('comment'); elem.focus(); 
                } }
                </script>";
        }
        else {
            echo "<script>parent.document.getElementById('AllInOne').innerHTML='';</script>";
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='';</script>";
            echo "<script>elem = parent.document.getElementById('comment'); elem.focus();</script>";
        }

        if (SOURCE_NOT == $getdet ) {
            echo "<script>parent.document.getElementById('save_but').style.visibility = 'hidden';</script>";
        }
        else {
            echo "<script> 
            if (parent.document.getElementById('DetailList') && 
                parent.document.getElementById('DetailList').value == ".SOURCE_NOT.") {
              parent.document.getElementById('save_but').style.visibility = 'hidden';
            } else {
               if (parent.document.getElementById('PurposeId').value != " . SERVICE_NOT . ") {
                 if (parent.document.getElementById('ServiceId').value != " . SERVICE_NOT . " &&
                     parent.document.getElementById('ResultId').value  != " . RESULT_NOT . " &&
                     (parent.document.getElementById('ResultId').value == " . RESULT_WAIT . " ||
                      parent.document.getElementById('OperatorsId').value > 0) ||
                      parent.document.getElementById('ResultId').value == " . RESULT_KC_SELF . ")
                     parent.document.getElementById('save_but').style.visibility = 'visible';
                 }
                 else parent.document.getElementById('save_but').style.visibility = 'hidden';
            }</script>";
        }
        exit();
    }
    ?>

    <script type="text/javascript">
    function CheckFields() {
        var inpObj = document.getElementById("call_center");
        if (!inpObj.checkValidity()) {
            document.getElementById("valid_str").innerHTML = inpObj.validationMessage;
        }/* else {
            document.getElementById("valid_str").innerHTML = "Input OK";
        }*/
    }
    </script>

    <script type="text/javascript">
    function PurposeSelected() {
        var elem;
        if (<?=THEME_MED?> == document.getElementById("PurposeId").value) { // ��� ����� �������� �� �����
            elem = document.getElementById("ServiceId");
            document.getElementById('PurposeId').style.backgroundColor = 'white';
            document.getElementById('ServiceId').style.visibility = 'visible';
            document.getElementById('ServiceT').style.visibility = 'visible';
            /*document.getElementById('ServiceStom').style.visibility =
                (< ?=SERVICE_STOM?> == document.getElementById("ServiceId").value ? 'visible' : 'hidden');*/
            //document.getElementById('CallType').style.visibility = 'visible';
            document.getElementById('NotTarget').style.visibility = 'visible';
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (<?=RESULT_KC?> == document.getElementById("ResultId").value || <?=RESULT_KC_SELF?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (<?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            elem = document.getElementById("save_but");
            if (<?=THEME_NOT?> == document.getElementById("PurposeId").value)
                document.getElementById('PurposeId').style.backgroundColor = '<?=needs?>';
            else document.getElementById('PurposeId').style.backgroundColor = 'white';
            document.getElementById('ServiceId').style.visibility = 'hidden';
            document.getElementById('ServiceT').style.visibility = 'hidden';
            //document.getElementById('ServiceStom').style.visibility = 'hidden';
            //document.getElementById('CallType').style.visibility = 'hidden';
            document.getElementById('NotTarget').style.visibility = 'hidden';
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }

        if (document.getElementById("PurposeId").value >= <?=THEME_INFO?> ||
            <?=THEME_MED?> == document.getElementById("PurposeId").value &&
            <?=SERVICE_NOT?> != document.getElementById("ServiceId").value &&
            /*(< ?=SERVICE_STOM?> != document.getElementById("ServiceId").value ||
             < ?=STOM_NOT?> != document.getElementById("ServiceStom").value) &&*/
            <?=SOURCE_NOT?> != document.getElementById("Reservoir").value &&
            <?=RESULT_NOT?> != document.getElementById("ResultId").value &&
            (<?=RESULT_WAIT?> == parent.document.getElementById("ResultId").value ||
            parent.document.getElementById("OperatorsId").value > 0)
        ) {
            document.getElementById('save_but').style.visibility = 'visible';
        } else {
            document.getElementById('save_but').style.visibility = 'hidden';
        }
        if (elem) elem.focus();
    }

    function ResultSelected()
    {
        var res_id = document.getElementById("ResultId").value;
        var elem;
        document.getElementById('ResultId').style.backgroundColor = 'white';
        document.getElementById('KC_Number').style.visibility = 'hidden';
        document.getElementById('write_cl').style.visibility = 'hidden';
        if ( <?=RESULT_KC?> == res_id || <?=RESULT_KC_SELF?> == res_id ) { // ������� � �� (� �� ����)
            //elem = document.getElementById('call_center');
            elem = document.getElementById('OperatorsId');
            document.getElementById('KC_Number').style.visibility = 'visible';
            //document.getElementById('call_center').required = 'required';
        } else if ( <?=RESULT_CLINIC?> == res_id ) { // ������� � �������
            elem = document.getElementById('call_clinic');
            document.getElementById('write_cl').style.visibility = 'visible';
            //document.getElementById('call_center').required = '';
        } else if ( <?=RESULT_WAIT?> == res_id || <?=RESULT_AON?> == res_id) { // ���� ������/���
            elem = document.getElementById('save_but');
            //document.getElementById('call_center').required = '';
        } else {
            elem = document.getElementById('save_but');
            document.getElementById('ResultId').style.backgroundColor = '<?=needs?>';
            //document.getElementById('call_center').required = '';
        }
        //alert(res_id);
        //alert(parent.document.getElementById("OperatorsId").value);
        if (document.getElementById("PurposeId").value > <?=THEME_MED?> ||
            <?=THEME_MED?> == document.getElementById("PurposeId").value &&
            <?=SERVICE_NOT?> != document.getElementById("ServiceId").value &&
            /*(< ?=SERVICE_STOM?> != document.getElementById("ServiceId").value ||
            < ?=STOM_NOT?> != document.getElementById("ServiceStom").value) &&*/
            document.getElementById("Reservoir") && <?=SOURCE_NOT?> != document.getElementById("Reservoir").value &&
            (!document.getElementById("DetailList") ||
                document.getElementById("DetailList") && <?=SOURCE_NOT?> != document.getElementById("DetailList").value) &&
            <?=RESULT_NOT?> != res_id &&
            (<?=RESULT_WAIT?> == res_id || <?=RESULT_AON?> == res_id ||
            document.getElementById("OperatorsId") && document.getElementById("OperatorsId").value != -1)
        ) {
            document.getElementById('save_but').style.visibility = 'visible';
        } else {
            document.getElementById('save_but').style.visibility = 'hidden';
        }
        if (elem) elem.focus();
    }

    /*function FirstNoCheck() {
        if (document.getElementById('FirstCall').checked) {
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (< ?=RESULT_KC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (< ?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }
    }*/
    </script>
</head>
<body>
<?php
$nameErr = "";
if ("POST" == $_SERVER["REQUEST_METHOD"] && isset($save_but)) { // ��������� ������ �� �����
    if (THEME_MED == $PurposeId) {
        $service = (isset($ServiceId) ? $ServiceId : NULL); // ��� Id ������
        $reservoir = (isset($Reservoir) ? $Reservoir : NULL); // ��� Id ���������
        $source_man_det = $Result_det = "NULL";

        $comment = (isset($comment) ? htmlspecialchars($comment, ENT_QUOTES) : "");
        $surname = (isset($surname) ? stripcslashes(htmlspecialchars($surname,ENT_QUOTES)) : "---");
        //$name = (isset($_POST['name']) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : "---");
        //$patronymic = (isset($_POST['patronymic']) ? htmlspecialchars($_POST['patronymic'], ENT_QUOTES) : "---");
        //$ages = "NULL"; //(isset($_POST['ages']) && $_POST['ages'] != "" ? $_POST['ages'] : 0);
        
		//(sva 23/04/2018) -------------------------------------------------------
		//$phone_mob = (isset($_POST['phone_mob']) ? $_POST['phone_mob'],0,14) : "");
		$phone_mob = (isset($phone_mob) ? $phone_mob : "");
        $phone_mob_norm = phone_norm_single($phone_mob,'ru_dial');
		
		if($phone_mob<>'' && $phone_mob_norm=='') { //���� � ���� �������� ��� �� �����, � ������������ �� ���� �������� ��� � ���������� ���
			$phone_mob_seg = $phone_mob;
			//$phone_mob_norm=$phone_mob;
		} //������ �������� ��������, ��� ����� ����� �������! ���� ����� �������� ����� ������, � ����, ����� �� �������� ��������� ���������� ����������, ����� �����, ��� ����.
		else {		
			$phone_mob_seg = phone_segment($phone_mob_norm,NULL);
		}
		//(sva 23/04/2018) -------------------------------------------------------
		
		$phone_new = ""; //(isset($_POST['phone_new']) ? $_POST['phone_new'] : "");
        //$email = (isset($_POST['e_mail']) ? $_POST['e_mail'] : "");
        $ResultId = (isset($ResultId) ? $ResultId : RESULT_WAIT);
        $call_center = (isset($call_center) ? $call_center : "");
        $OperatorsId = (isset($OperatorsId) ? $OperatorsId : "");
        if ("" != $OperatorsId)
            $OperatorsId = substr($OperatorsId, 0, strpos($OperatorsId, ':'));
        $clinic = (isset($call_clinic) ? $call_clinic : "");
    } else {
        $service = $reservoir = $source_man_det = "NULL";
        $ResultId = $Result_det = $call_center = $OperatorsId = $clinic = "NULL";
        $comment = $surname = $name = $patronymic = $phone_mob = $phone_mob_norm = $phone_new = $phone_new_norm = "";
    }

    /*$service_det = "NULL";
        if (SERVICE_STOM == $service) { // ����������� ��� ������������
        if (isset($ServiceStom)) {
            $service_det = $ServiceStom;
        }
    }*/

    //if ($reservoir < SOURCE_2GIS || SOURCE_BANNER_SUB == $reservoir) { // ���-�� �� �������
        if (isset($DetailList)) {
            $source_man_det = $DetailList;
        }
    //}

    if (RESULT_KC == $ResultId) {
        if (intval($call_center) != strval($call_center) || !is_int($call_center)) {
            $nameErr = "��������� �������� ��������";
            //exit;
        } else {
            $call_center = check_input($call_center);
        }
    }
    if (RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) { // �������� � ��
        $Result_det = (isset($call_center) ? $call_center : NULL);
    } elseif (RESULT_CLINIC == $ResultId) { // �������� � �������
        $Result_det = $clinic;
    } elseif (RESULT_WAIT == $ResultId || RESULT_AON == $ResultId) { // ���� ������/���
        $Result_det = NULL;
    }

    //��������� ������� ���������� �������
    $status = STATUS_OPEN;
    $query_result = FALSE;
	$fio = substr($surname,0,64);// . "/" . $name . "/" . $patronymic;

    if (FALSE == DEBUG_MODE)
        $table_name = 'CALL_BASE';
    else $table_name = 'CALL_BASE_TEST';
    if (THEME_MED == $PurposeId) {
        if (RESULT_KC_SELF == $ResultId) $ResultId = RESULT_KC; // ����������� ���������, ��� ��� �� ������� ������ �� ����
        $updatestr = "UPDATE ".$table_name." SET CALL_THEME_ID = {$PurposeId}, SOURCE_MAN_ID = {$reservoir}, SERVICE_ID = {$service}, 
    COMMENTS = '{$comment}', CLIENT_NAME = '{$fio}', PHONE_MOB = '{$phone_mob_seg}', PHONE_MOB_NORM = '{$phone_mob_norm}', CALL_BACK_NUM = 10, 
    RESULT_ID = {$ResultId}, LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        /*if (isset($service_det) && NULL != $service_det && 'NULL' != $service_det)
            $updatestr .= ", SERVICE_DET_ID = {$service_det}";*/
        if (SERVICE_STOM == $service)
            $updatestr .= ", SERVICE_DET_ID = ".STOM_NOT;
        if (isset($source_man_det) && NULL != $source_man_det && 'NULL' != $source_man_det)
            $updatestr .= ", SOURCE_MAN_DET_ID = '{$source_man_det}'";
        if (isset($Result_det) && 'NULL' != $Result_det)
            $updatestr .= ", RESULT_DET = '{$Result_det}'";
        if (isset($interstate))
            $updatestr .= ", INTERSTATE = 1";
        if (RESULT_WAIT == $ResultId || RESULT_AON == $ResultId)
            $updatestr .= ", STATUS_ID = ".STATUS_OPEN;
        elseif (RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) {
            $updatestr .= ", STATUS_ID = ".STATUS_WORK.", FIO_ID = '{$OperatorsId}'"; // ����� ��������� ��������� ���������, ��� �� ������
            $trans_arr = date_parse(date("Y-m-d HH:MM"));
            $const_str = $trans_arr['year'] . '-' . $trans_arr['month'] . '-' . $trans_arr['day'];
            $num_str = GetData::GetTransferNum($const_str);
            $updatestr .= ", TRANSFER_NUM = '" . $const_str . '-' . $num_str . "'";
        }
        elseif (RESULT_CLINIC == $ResultId) { // ������������ � ������� ����� � �������� ���������
            $updatestr .= ", STATUS_ID = " . STATUS_CLINIC_CALL .", DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
        }
    }
    else { // ������������� ����� � �������� ���������
        if(isset($max_call) and $max_call!='') {
			$sql_text="select count(*) CNT_HIST from call_base_hist where base_id = '".$max_call."'";
			$check_hist = OCIParse($c,$sql_text);
			OCIExecute($check_hist);
		}
		if (isset($check_hist) && OCIFetch($check_hist) && OCIResult($check_hist,"CNT_HIST")>2) {
			$updatestr = "UPDATE ".$table_name." SET LAST_CHANGE = sysdate"; // ���-�� ���� ��������, ���� ����� ������ �����
		}
		else {
			$updatestr = "UPDATE ".$table_name." SET CALL_THEME_ID = {$PurposeId}, STATUS_ID = " . STATUS_CLOSED .",
			LAST_CHANGE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), DATE_CLOSE = to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss')";
		}
    }
	if(isset($updatestr) and $updatestr!='') {
		$updatestr .= " WHERE ID = " . $max_call;
		if (TRUE == DEBUG_MODE) echo "<br/><textarea>" . $updatestr . "</textarea><br/>";
		GetData::my_log($updatestr, FALSE);
		$query = OCIParse($c, $updatestr);
		$query_result = OCIExecute($query);
		if (!$query_result)
			GetData::my_log($updatestr, TRUE);
	}
// ��� ����������� ������ ��������� ������ ������ ������� �� ����� ������ c ������ ���������
    if ($query_result && THEME_MED == $PurposeId) {
        $date_det = date("d-m-Y H:i:s");
        if (FALSE == DEBUG_MODE) {
            $insertstr = "INSERT INTO CALL_BASE_HIST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID.nextval, $max_call, '{$sc_agid}', $status, 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '{$comment}', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
        }
        else {
            $insertstr = "INSERT INTO CALL_BASE_HIST_TEST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID_TEST.nextval, $max_call, '{$sc_agid}', $status, 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '{$comment}', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
        }
if (TRUE == DEBUG_MODE) echo "<textarea>".$insertstr."</textarea><br/>";
        GetData::my_log($insertstr, FALSE);
        $query = OCIParse($c, $insertstr);
        $query_result = OCIExecute($query);
        if (!$query_result)
            GetData::my_log($insertstr, TRUE);

        if ((RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) && $query_result && THEME_MED == $PurposeId &&
            isset($OperatorsId) && $OperatorsId != "" && $OperatorsId != "NULL" && $OperatorsId != '-1')
        {
            if (FALSE == DEBUG_MODE)
                $insertstr = "INSERT INTO CALL_BASE_HIST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID.nextval, $max_call, '{$sc_agid}', ".STATUS_WORK.", 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '(fio_id=".$OperatorsId.") ������� � ��', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
            else $insertstr = "INSERT INTO CALL_BASE_HIST_TEST (ID, BASE_ID, OPERATOR, STATUS_ID, DATE_DET, COMMENTS, DATE_START) 
    VALUES (SEQ_CALL_BASE_HIST_ID_TEST.nextval, $max_call, '{$sc_agid}', ".STATUS_WORK.", 
    to_date('{$date_det}','DD.MM.YYYY hh24:mi:ss'), '(fio_id=".$OperatorsId.") ������� � ��', 
    to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
if (TRUE == DEBUG_MODE) echo "<textarea>".$insertstr."</textarea><br/>";
            GetData::my_log($insertstr, FALSE);
            $query = OCIParse($c, $insertstr);
            $query_result = OCIExecute($query);
            if (!$query_result)
                GetData::my_log($insertstr, TRUE);
        }
    }
    oci_free_statement($query);

    // �������� ������ �����������
    if (GetData::GetIstochnik(TRUE,TRUE,"ID=".$reservoir, FALSE) > 0) {
        $reservoir_text = GetData::$array_istochnik[0]['NAME'];
    }
    else $reservoir_text = $reservoir;
    if (GetData::GetThemes("ID=".$PurposeId) > 0) {
        $theme_text = GetData::$array_theme[0]['NAME'];
    }
    else $theme_text = $PurposeId;

    $detail_text = $source_man_det;
    if (SOURCE_FLAER == $reservoir || SOURCE_CATALOG == $reservoir ||
        SOURCE_FLAER_SUB == $reservoir || SOURCE_FLAER_CAR == $reservoir ||
        SOURCE_LIFT == $reservoir || SOURCE_STOP == $reservoir) {
        $nrows = GetData::GetSubway(NULL); // ��� ��������� � ������� (����?)
        $array_todo = GetData::$array_subway;
    } elseif (SOURCE_SERT == $reservoir) {
        $nrows = GetData::GetHospitals(NULL);
        $array_todo = GetData::$array_hospitals;
    } else {
        $nrows = GetData::GetSourceDetail(FALSE, NULL, $reservoir);
        $array_todo = GetData::$array_details;
    }
    if ($nrows > 0) {
        foreach ($array_todo as $key => $value) {
            if ($source_man_det == $value['ID'])
                $detail_text =  $value['NAME'];
        }
    }

    //$from_email = $reply_to_email = "report@wilstream.ru";
    //$from_name = $reply_to_name = "������ ��������";
    // ���� ������
    $headers="MIME-Version: 1.0 \n";
    $headers.="Content-Type: text/html; charset=\"windows-1251\"\n";
    $mess_subj = $source_auto_name." - ".$bnumber." ���� ������ - ".$theme_text." (".$sc_call_id.")";
    if (RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) $mess_subj .= ' (����������)';
    $mess_subj .= ' ������ - ' . $max_call;

    // ����� ������
    $mess = "<html><head><title>���������� � ������</title></head><body><table>
        <tr><th>ID ������:</th><th>".$sc_call_id."</th></tr>
        <tr><td><b>����������� ������:</b></td><td>".CALL_WAY[$call_direction]."</td></tr>
        <tr><td><b>���� ������:</b></td><td>".$date_call."</td></tr>
        <tr><td><b>���:</b></td><td>".$anumber."</td></tr>
        <tr><td><b>���������� �����:</b></td><td>".$bnumber."</td></tr>
        <tr><td><b>��������:</b></td><td>".$sc_agid."</td></tr>";
    if (THEME_MED == $PurposeId) {
        $mess .= "<tr><td><b>����� ����������� ������ ����������:</b></td><td>".SERVICE_LIST[$service]."</td></tr>
        <tr><td><b>�������� �������(����):</b></td><td>".$source_auto_name."</td></tr>
        <tr><td><b>�������� �������(������):</b></td><td>" . $reservoir_text;
        if (isset($detail_text) && "NULL" != $detail_text)
            $mess .= " (".$detail_text.")";
        $mess .= "</td></tr>";
        $mess .= "<tr><td><b>���:</b></td><td>".$fio."</td></tr>
        <tr><td><b>�������:</b></td><td>".$phone_mob."</td></tr>";
        if ((RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) && $Result_det)
            $mess .= "<tr><td><b>��������� �:</b></td><td>".$Result_det."</td></tr>";
            //$mess .= "<tr><td><b>��������� �:</b></td><td>".$Result_det."&nbsp;\t��� ���������: \t???</td></tr>";
        $mess .= "<tr><td><b>�����������:</b></td><td>".$comment."</td></tr>";
    }
    $mess .= "</table></body></html>";

    if (THEME_MED == $PurposeId && TRUE == SEND_EMAIL && ($query_result || DEBUG_MODE)) {
        if (GetData::GetSupervisorByService(NULL, $service) > 0) {
            foreach(GetData::$array_supers as $key => $value) {
                if (isset($value['EMAIL'])) {
                    $to_email = $value['EMAIL'];
                    $to_name = $value['FIO'];
                }
                $res = "";
                //�������� � ��������������� �������
                foreach ($smtp_conf as $conf_num => $cur_smtp_conf) {
                    if (DEBUG_MODE) echo "<br>----------------------------------------------</br>";
                    $res = send_email(
                        $cur_smtp_conf['smtp_server'],
                        $cur_smtp_conf['smtp_port'],
                        $cur_smtp_conf['smtp_auth_login'],
                        $cur_smtp_conf['smtp_auth_pass'],
                        $to_name,
                        $to_email,
                        $cur_smtp_conf['smtp_from_name'],
                        $cur_smtp_conf['smtp_from_email'],
                        //$from_name, $from_email,
                        $reply_to_name = '',
                        $reply_to_email = '',
                        $mess_subj,
                        $mess,
                        $headers,
                        $debug = 'n'
                    );
                    /*$email_res = send_email($smtp_server, $smtp_port, $smtp_auth_login, $smtp_auth_pass,
                        $to_name, $to_email, $smtp_from_name, $smtp_from_email, $reply_to_name, $reply_to_email,
                        $mess_subj, $mess, '', $debug = 'y');*/
                    if ('OK' == substr($res,0,2)) {
                        echo "<p style='font-size: larger; color: green'>������������ ".$to_name." ��������� E-Mail � ����������� ��������� ������ - ".$res."</p>";
                        break;
                    }
                    else {
                        echo "<p style='font-size: larger; color: red'>������ �������� E-mail!!! - </p>" . $res;
                    }
                }
            }
        }
    } elseif(DEBUG_MODE) {
        echo "��������������� ������ � ����� ������ ����� ���� ���������� �����������.";
        echo $mess;
    }

    if ($query_result) {
        echo "<p style='font-size: larger; color: green'>������ ������� �������� � ���� ������. </p>";
        if (RESULT_KC == $ResultId || RESULT_KC_SELF == $ResultId) {
            //echo "<h1>&nbsp;��� ��������:&nbsp;<span style='color: black; font-size: smaller;'>" . $const_str . "-</span>
            echo "<h1>&nbsp;��� ��������:&nbsp;
            <span style='color: red; font-size: larger; border-bottom: dashed'>" . $num_str . "</span><br/>
            <span style='color: maroon; font-size: smaller; font-style: italic'>(�������� ���� ��� ��������� ���������� ������)</span></h1>";
            //echo "��� ������: '".session_name() . "'.";
            if (isset($max_call) && $max_call > 0 && isset($OperatorsId) && $OperatorsId > 0) {
                $_SESSION['auth'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
                echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='open_call(".$max_call.",".$OperatorsId.",\"".session_id()."\");'>�������</button>";
            }
            else echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close();'>�������</button>";

        }
        else echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close();'>�������</button>";
        /*} else {
            print "<script language='Javascript'> function close_win() { window.close(); } setTimeout('close_win()', 100);</script>";
        }*/
    } else {
        echo "<p style='font-size: larger; color: red'>��������� ������ ���������� ������!</p>";
    }
    unset($save_but);

    exit;
}

function check_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

echo '<div style="display: inline-block;">
    <!--a href="./"><h1 class="heading" style="margin-top: -5px; margin-bottom: 1px;">�������� ������</h1></a-->
    <h1 class="heading" style="margin-top: -5px; margin-bottom: 1px;">�������� ������ 
    '.(isset($max_call) ? ' (������ '.$max_call.')' : "").'</h1>
</div>';
if (isset($max_call) && isset($status) && $status < STATUS_CLOSED) {
    GetData::GetMedStatus("ID = ".$status, TRUE, TRUE);
    echo '<h2 style="color: '.GetData::$array_status[0]['COLOR'].'">������ ��� � ������! ������: "' . GetData::$array_status[0]['NAME'] . '".</h2>';
    if (isset($max_call) && $max_call > 0 && isset($OperatorsId) && $OperatorsId > 0) {
        $_SESSION['auth'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='open_call(".$max_call.",".$OperatorsId.",\"".session_id()."\");'>�������</button>";
    }
    else echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close();'>�������</button>";
    exit;
}
//----------------------------------------
$source_auto_id = 0;
$source_auto_name = "???";
if (isset($sc_call_id) && isset($sc_project_id) && isset($bnumber)) {
    $nrowsAuto = GetData::GetSourceAuto(NULL, $bnumber,FALSE,'sa.ID','asc',FALSE,FALSE);
    if (0 == $nrowsAuto) // ���� ������ �� ��� ���������� ���������, ��� ����� �� ������������� �������
        $nrowsAuto = GetData::GetSourceAuto(NULL, $bnumber,FALSE,'sa.Deleted','desc',FALSE,TRUE);
    if (isset(GetData::$array_source_auto) && $nrowsAuto > 0) {
        $source_auto_id = GetData::$array_source_auto['ID'];
        $ServiceId = GetData::$array_source_auto['SERVICE_ID'];
        if (TRUE == ENCODE_UTF)
            $source_auto_name = u8(GetData::$array_source_auto['NAME']);
        else $source_auto_name = GetData::$array_source_auto['NAME'];
    }
}
//var_dump($source_auto_name);

$date_call = date("d-m-Y H:i:s");
if(!isset($oktell_idchain)) $oktell_idchain='';
if(!isset($oktell_srv_id)) $oktell_srv_id='';

if (!isset($source_auto_id) || 0 == $source_auto_id) {
    echo "<h1 style='color: red'>������ ������!!!</h1>";
    if (!isset($sc_call_id) || NULL == $sc_call_id)
        echo "<h2>�������� ������, ��� <span style='color: mediumvioletred; border-bottom: dashed'>sc_call_id ������!</span></h2><br/>";
    if (!isset($sc_project_id) || NULL == $sc_project_id)
        echo "<h2>�������� ������, ��� <span style='color: mediumvioletred; border-bottom: dashed'>sc_project_id ������!</span></h2><br/>";
    echo "<h2>��������������� ��������� ������� �� �������!<br/>";
    if (!isset($bnumber) || NULL != $bnumber)
        echo "�������� ������ ��������� �������� BNumber: <span style='color: mediumvioletred; text-decoration: underline'>'".$bnumber."'</span>";
    else echo "�������� ������, ��� <span style='color: mediumvioletred; border-bottom: dashed'>BNumber ������!</span>";
    echo "</h2>";
} else { // ����� ��������� ������ � CALL_BASE � ���������� �������, ���� ������ call_id ��� ���.
    $query_result = FALSE;
    if ($existing_call) { // �������� ������ ��������� ������
        $max_call = GetData::$arr_call_base['ID']; // Base_ID
        $PurposeId = GetData::$arr_call_base['CALL_THEME_ID'];
        $service = GetData::$arr_call_base['SERVICE_ID'];
        //$getoper = $service;
        $reservoir = GetData::$arr_call_base['SOURCE_MAN_ID'];
        $source_man_det = GetData::$arr_call_base['SOURCE_MAN_DET_ID'];
        $ResultId = GetData::$arr_call_base['RESULT_ID'];
        $Result_det = GetData::$arr_call_base['RESULT_DET'];
        $fio = GetData::$arr_call_base['CLIENT_NAME'];
        $phone_mob_norm = GetData::$arr_call_base['PHONE_MOB'];
        $user_id = GetData::$arr_call_base['FIO_ID'];
        $comment = GetData::$arr_call_base['COMMENTS'];
        //var_dump($Result_det);
    }
    else {
        if (FALSE == DEBUG_MODE) {
			$anumber_norm = (isset($anumber) ? phone_norm_single($anumber,'ru_dial') : "");
            $insertstr = "INSERT INTO CALL_BASE (ID, DATE_CALL, ANUMBER, ANUMBER_NORM, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, CALL_DIRECTION,
        CALL_THEME_ID, SOURCE_AUTO_ID, SOURCE_MAN_ID, SOURCE_TYPE_ID, CALL_TYPE_ID, SERVICE_ID, STATUS_ID, LAST_CHANGE,
        OKTELL_IDCHAIN, OKTELL_SERVER_ID,CREATE_DATE)
        VALUES (SEQ_CALL_BASE_ID.NEXTVAL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
        '{$anumber}', '{$anumber_norm}', '{$bnumber}', '{$sc_agid}', '{$sc_call_id}', '{$sc_project_id}', '{$call_direction}'," .
                THEME_NOT . ",'{$source_auto_id}'," . SOURCE_NOT . "," . DEVICE_PHONE . "," . CALL_FIRST . "," . SERVICE_NOT . "," . STATUS_CLOSED . ",
        to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$oktell_idchain}', '{$oktell_srv_id}',sysdate) returning ID into :max_call";
        } else {
            $insertstr = "INSERT INTO CALL_BASE_TEST (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID, CALL_DIRECTION,
        CALL_THEME_ID, SOURCE_AUTO_ID, SOURCE_MAN_ID, SOURCE_TYPE_ID, CALL_TYPE_ID, SERVICE_ID, STATUS_ID, LAST_CHANGE,
        OKTELL_IDCHAIN, OKTELL_SERVER_ID,CREATE_DATE)
        VALUES (SEQ_CALL_BASE_ID_TEST.NEXTVAL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
        '{$anumber}', '{$bnumber}', '{$sc_agid}', '{$sc_call_id}', '{$sc_project_id}', '{$call_direction}'," .
                THEME_NOT . ",'{$source_auto_id}'," . SOURCE_NOT . "," . DEVICE_PHONE . "," . CALL_FIRST . "," . SERVICE_NOT . "," . STATUS_CLOSED . ",
        to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'), '{$oktell_idchain}', '{$oktell_srv_id}',sysdate) returning ID into :max_call";
        }
if (TRUE == DEBUG_MODE) echo "<br/><textarea>" . $insertstr . "</textarea><br/>";

        GetData::my_log($insertstr, FALSE);
        $query = OCIParse($c, $insertstr);
        OCIBindByName($query, ":max_call", $max_call, 16);
        $query_result = OCIExecute($query);
        if (!$query_result)
            GetData::my_log($insertstr, TRUE);

        /*$max_call = 1;
        if ($query_result) {
            if (FALSE == DEBUG_MODE)
                $sqlstr = "SELECT SEQ_CALL_BASE_ID.CURRVAL FROM CALL_BASE";
            else $sqlstr = "SELECT SEQ_CALL_BASE_ID_TEST.CURRVAL FROM CALL_BASE_TEST";
            $query = OCIParse($c, $sqlstr);
            if (OCIExecute($query)) {
                $objResult = OCI_Fetch_Row($query);
                $max_call = $objResult[0];
            }
        }*/
        oci_free_statement($query);
        $fio = $phone_mob_norm = $comment = $Result_det = '';
    }
    //var_dump($max_call);

    $user_id = $role_id = $data_acc = 0;
    if (isset($oktell_uid) && preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $oktell_uid)) {
        $sql = "SELECT login FROM [oktell_settings].[dbo].[A_Users] where id='" . $oktell_uid . "'";
        $q = $c_okt->prepare($sql);
        $q->execute();

        if ($row = $q->fetch()) {
            //var_dump($row);
            if (GetData::GetUsers(FALSE, FALSE, "usr.login = '" . $row['login'] . "'", "FIO") > 0) {
                //var_dump(GetData::$array_user);
                $user_id = GetData::$array_user[0]['ID'];
                $role_id = GetData::$array_user[0]['ROLE_ID'];
                $data_acc = GetData::$array_user[0]['DATA_ACC'];
                $Result_det = GetData::$array_user[0]['PIN']; // ��� ���������� ��� ���������� ������ ����
                $_SESSION['login_id_med'] = $user_id;
                $_SESSION['user_role'] = $role_id;
                $_SESSION['data_acc'] = $data_acc;
                if (-1 != GetData::GetUserDuty($_SESSION['login_id_med'], NULL))
                    $_SESSION['on_duty_today'] = TRUE;
                else $_SESSION['on_duty_today'] = FALSE;
                //var_dump('"'.$user_id .'"="'. $role_id .'"="'. $data_acc.'"');
            }
        }
    //    else var_dump('Error');
    }
    else echo '<br/>������������ GUID.';
}
?>

<div>
    <form action="<?php echo $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];?>" method="POST">
    <h2 style="display: inline;">
        <?php
        echo '<label for="PurposeId">���� ������:&nbsp;</label>';
        echo '<select id="PurposeId" name="PurposeId" onchange="PurposeSelected();" title="���� ������" style="background-color:'.needs.'">';
            echo "<option value='".THEME_NOT."'>�������� ����</option>";
            if (GetData::GetThemes("DELETED IS NULL") > 0) {
                foreach(GetData::$array_theme as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = u8 ($value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
        echo '</select>';

		//������ ��������� �� ���
        if (isset($aon_for_backcall) and $aon_for_backcall<>'' && isset($max_call)) {
            echo " | <input disabled id='callto_button' type='button' onclick='callto(".$aon_for_backcall.",".$max_call.")' value='     ����������� �� ���' title='".$aon_for_backcall."' 
style='height: 25px; background-image: url(\"".PATH."/images/call.png\"); background-repeat: no-repeat; display:none;' />";
            echo "<input disabled id='endcall_button' type='button' onclick='endcall()' value='    ��������� ������'  
style='height: 25px; background-image: url(\"".PATH."/images/call_stop.png\"); background-repeat: no-repeat; display:none;' />";
			echo "<script>var oktell_phone_prefix='".$out_prefix."';</script>";
			include('med_call_makecall.js');
        }			
		
        echo "<iframe name=ifr2 style='display:none; width: 90%'></iframe>";
        echo '<br/><label id="ServiceT" for="ServiceId" style="visibility: hidden">������:&nbsp;</label>';
        if (!isset($user_id)) $user_id = 0;
        echo "<select id='ServiceId' name='ServiceId' style='visibility: hidden; background-color:".needs."' 
        onchange='ifr2.location=\"".PATH."/med_call.php?getoper=\"+this.value+\"&user_id=$user_id&sc_call_id=$sc_call_id\"'>";
        //".($existing_call ? 'disabled' : 'onchange=\'ifr2.location=\"".PATH."/med_call.php?getoper=\"+this.value+\"&user_id=$user_id\"\'').">";
        echo "<option value='".SERVICE_NOT."'>�������� ������</option>";
        if (GetData::GetServices(FALSE,FALSE,NULL,FALSE) > 0) {
            foreach(GetData::$array_services as $key => $value) {
                if (TRUE == ENCODE_UTF) {
                    $value['NAME'] = u8($value['NAME']);
                }
                /*if (SERVICE_STOM == $value['ID'])
                    printf("<option value='%s' selected=\"selected\">%s</option>", $value['ID'], $value['NAME']);
                else*/ echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
        }
        echo "</select>";
        if (isset($ServiceId) && $ServiceId > 0)
            echo "<script>$('#ServiceId').val('" . $ServiceId . "').change();</script>";
        ?>
    </h2>
    <!--div id="CallType"><h2>
            <label for="voice">��� ������:&nbsp;</label>
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="FirstCall" value=< ?=CALL_FIRST?> checked title="���������"/> ���������
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="SecondCall" value=< ?=CALL_SECOND?> title="���������" /> ���������
        </h2></div-->

    <div id="NotTarget" style="visibility: hidden">
    <h2>
        <!--label for="Istochnik_auto">�������� ������� (���������������):</label>
        <input type="text" name="Istochnik_auto" style="width: 290px;" placeholder="< ?php echo $source_auto_name ?>" disabled/>
        <br/-->

        <iframe name=ifr1 style='display:none; width: 90%'></iframe>
        <?php
        if (true || !$existing_call) { // ������� ��������� ����� � ������� �� �����, ��� ��� � call_out
            echo '<label style="position: relative; float: left;" id="AllInOneIst" for="SelectIst">&nbsp;</label>';
            echo '<span id="SelectIst" style="margin-left: 15px;">&nbsp;</span><br/>';
            echo '<label style="position: relative; float: left;" id="AllInOne" for="AllSelect">&nbsp;</label>';
            echo '<span id="AllSelect" style="margin-left: 15px;">&nbsp;</span><br/>';
        }
        else { // ��� �������� ��������� � ��������� ������ ?
            echo "<label style='position: relative; float: left;' for='Reservoir'>�������� �������:&nbsp;</label>";
            echo "<select id='Reservoir' name='Reservoir' onchange='ifr1.location=\"" . PATH . "/med_call.php?getdet=\"+this.value'>";
            //echo "<select id='Reservoir' name='Reservoir'>"; // disabled
            echo "<option value=\"" . SOURCE_NOT . "\">�������� �������� �������</option>";
            if (GetData::GetIstochnik(FALSE, FALSE, "(instr(in_dep, '" . $getoper . "') != 0)", FALSE) > 0) {
                foreach (GetData::$array_istochnik as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $value['NAME'] = u8($value['NAME']);
                        $value['DETAIL'] = u8($value['DETAIL'] . ': ');
                    }
                    echo "<option value=\"" . $value['ID'] . "\">" . $value['NAME'] . "</option>";
                }
            }
            echo "</select>";
//var_dump($reservoir); var_dump($source_man_det);
            if (!isset($reservoir) || 0 == $reservoir || !isset($source_man_det)) {
                echo '<label style="position: relative; float: left;" id="AllInOne" for="AllSelect">&nbsp;</label>';
                echo '<span id="AllSelect" style="margin-left: 15px;">&nbsp;</span><br/>';
            }
            else {
                $nrows = GetData::GetSourceDetail(FALSE, NULL, $reservoir);
                if ($nrows > 0) { // � ��������� ������ ���
                    if (SOURCE_FLAER == $reservoir || SOURCE_CATALOG == $reservoir ||
                        SOURCE_FLAER_SUB == $reservoir || SOURCE_FLAER_CAR == $reservoir ||
                        SOURCE_LIFT == $reservoir || SOURCE_STOP == $reservoir) {
                        $getdetailstr = "SELECT ID, NAME FROM SUBWAYS";
                        $strtitle = '������� �����';
                    } elseif (SOURCE_SERT == $reservoir) { //$getdetailstr = "SELECT ID, NAME FROM HOSPITALS";
                        $getdetailstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ORDER BY hosp.CITY, hosp.NAME, serv.NAME";
                        $strtitle = '����������';
                    } else {
                        $nrows = GetData::GetIstochnik(FALSE, FALSE, "ID = " . $reservoir, FALSE);
                        $strtitle = '�����������';
                        if (isset(GetData::$array_istochnik)) {
                            foreach (GetData::$array_istochnik as $key => $value) {
                                $strtitle = $value['DETAIL'];
                            }
                        }
                        $getdetailstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id=" . $reservoir . " and DELETED IS NULL";
                    }
                    if (TRUE == ENCODE_UTF)
                        $strtitle = u8($strtitle);
                    echo '<label style="position: relative;" for="DetailList">&nbsp;' . $strtitle . ':&nbsp;</label>';

                    $i = 0;
                    echo "<select id='DetailList' name='DetailList'>"; // disabled
                    echo "<option value=''>�������� �����������</option>";
                    $q = OCIParse($c, $getdetailstr);
                    if (OCIExecute($q)) {
                        while (OCIFetch($q)) {
                            echo "<option value=" . OCIResult($q, "ID") . ">" . OCIResult($q, "NAME") . "</option>";
                            $i++;
                        }
                    }
                    if (SOURCE_SERT == $reservoir) {
                        echo "<option value=" . DETAILS_PROMO . ">�� ����� � ����������</option>";
                        echo "<option value=" . DETAILS_OTHER . ">������</option>";
                        $i += 2;
                    } elseif (SOURCE_NOT != $reservoir && SOURCE_COUPON != $reservoir) {
                        echo "<option value=" . DETAILS_AMNESY . ">�� ������</option>";
                        $i++;
                    }
                    echo "</select>";
                    if ($i == 0) {
                        echo '-----';
                    } //'������ ������ �����������!';
                }
            }
            if (isset($reservoir) && $reservoir > 0)
                echo "<script>$('#Reservoir').val('" . $reservoir . "').change();</script>";
            if (isset($source_man_det) && $source_man_det > 0)
                echo "<script>$('#DetailList').val('".$source_man_det."').change();</script>";
        }
        ?>
    </h2>
    </div>

    <div id="all_other" style="visibility: hidden">
<!--    <h2>���� ������: <input type="textarea" cols="10" rows="5" name="age" style="width: 400px; height: 50px;"/></h2> -->
    <h2><label for="comment">�����������:</label>
        <textarea name="comment" id="comment" title="�����������" placeholder="������� �����������" rows=30 cols=68 style="vertical-align: text-top; height: 45px"><?= isset($comment)?$comment:'' ?></textarea>
        <br/>
        <label for="surname">���:&nbsp;</label><input type="text" name="surname" style="width: 90%" value="<?= isset($fio)?$fio:'' ?>" placeholder="������� ��� ��������"/>
        <!--label for="name">���:&nbsp;</label><input type="text" name="name" style="width: 7em" placeholder="���"/>
        <label for="patronymic">��������:&nbsp;</label><input type="text" name="patronymic" placeholder="��������"/>
        <label for="ages">�������:&nbsp;</label>
        <input type="number" min="0" max="200" name="ages" style="width: 4em;"/-->
        <br/>
        <label for="phone_mob">���������� �������:&nbsp;</label>
		<!--(sva 23/04/2018)-->
		<!--<input type="text" id="phone_mob" name="phone_mob" style="width: 10em;" placeholder="������� �����"/>-->
		<input type="text" name="phone_mob" title="���������� �������" style="width: 10em;" value="<?= isset($phone_mob_norm)?$phone_mob_norm:'' ?>"/>
		
        <!--label for="phone_home">&nbsp;��������:&nbsp;</label>
        <input type="text" id="phone_home" name="phone_home" style="width: 10em;" placeholder="������� ��������"/-->
        <!--br/>������������� ������: &nbsp;<input type=checkbox id='interstate' name='interstate' title='��������'-->
    </h2>

    <!--h2>E-mail: <input type="email" name="e_mail" placeholder="e-mail" style="width: 22em;"/></h2-->
    <h2 style="display: inline-block; margin-top: 0"> ���������:
        <select id="ResultId" name="ResultId" onchange="ResultSelected();" title="���������" style="background-color:<?=needs?>">
            <option value="<?=RESULT_NOT?>">�������� ���������</option>
            <option value="<?=RESULT_WAIT?>">���� ������</option>
            <option value="<?=RESULT_KC?>">�������� � ��</option>
            <!--option value="< ?=RESULT_CLINIC?>">�������� � �������</option-->
            <option value="<?=RESULT_AON?>">�� ������� �����</option>
            <!--?php if ($user_id > 0) echo '<option value="'.RESULT_KC_SELF.'">��������� ����</option>'; ?-->
        </select>

        <!--?php
        $trans_arr = date_parse(date("Y-m-d HH:MM"));
        $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'];//.'-'.$trans_arr['hour'];
        $num_str = GetData::GetTransferNum($const_str);?-->
        <span id="KC_Number" style="position: absolute; visibility: hidden; display: inline-block; width: 50%">
            <label style="position: relative; float: left;" id="assign_clT" for="assign_cl">&nbsp;������ �� �������!</label>
            <span id="assign_cl">&nbsp;</span><br/>
            <label for='call_center'>&nbsp;��� ���������:&nbsp;</label>
            <input type='number' size='4' name='call_center' id='call_center' style='width: 5em;' value="<?=$Result_det?>"/>
            <!--input type="number" minlength="4" maxlength="4" pattern="[0-9]{4}" min="1000" max="9999" size="4" name="call_center" id="call_center" placeholder="1000" style="width: 5em;"-->
            <!--span class="error">* < ?php echo $nameErr;?></span-->
            <!--p style="color: magenta; margin: 0;" id="valid_str"></p-->
            <!--script>document.getElementById('call_center').validity.patternMismatch;</script-->
<!--          &nbsp;����� ������:&nbsp;<span style="color: black; font-size: smaller;">< ?=$const_str?>-</span>
            <span style="color: #FF733C; font-size: larger; border-bottom: dashed">< ?=$num_str?></span-->
        </span><br/>
        <span id="write_cl" style="visibility: hidden">&nbsp; �������:&nbsp;
            <select id='call_clinic' name='call_clinic' title="�������">
            <?php
            if (GetData::GetHospitals(NULL) > 0) {
                foreach (GetData::$array_hospitals as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['NAME'] = u8($value['NAME']);
                    echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
                }
            }
            ?>
            </select>
        </span>
    </h2>
    </div>
    <div>
        <!--button name="save_but" id="save_but" class="send_button" onclick="CheckFields()" style="visibility: hidden">���������</button-->
        <input type="submit" name="save_but" id="save_but" value="���������" class="send_button" onClick="CheckFields()" style="visibility: hidden"/>
        <input type="hidden" name="max_call" value="<?php echo $max_call; ?>"/>
        <input type="hidden" name="date_call" value="<?php echo $date_call; ?>"/>
        <input type="hidden" name="sc_agid" value="<?php echo $sc_agid; ?>"/>
        <input type="hidden" name="anumber" value="<?php echo $anumber; ?>"/>
        <input type="hidden" name="bnumber" value="<?php echo $bnumber; ?>"/>
        <input type="hidden" name="sc_call_id" value="<?php echo $sc_call_id; ?>"/>
        <input type="hidden" name="sc_project_id" value="<?php echo $sc_project_id; ?>"/>
        <input type="hidden" name="call_direction" value="<?php echo $call_direction; ?>"/>
        <input type="hidden" name="source_auto_name" value="<?php echo $source_auto_name; ?>"/>
    </div>
    <?php
    if (!$existing_call) {
        if (isset($PurposeId)) echo "<script>$('#PurposeId').val('" . $PurposeId . "').change();</script>";
        if (isset($service)) echo "<script>$('#ServiceId').val('" . $service . "').change();</script>";
        //if (isset($reservoir)) echo "<script>$('#Reservoir').val('".$reservoir."').change();</script>";
        //if (isset($source_man_det)) echo "<script>$('#DetailList').val('".$source_man_det."').change();</script>";
        if (isset($ResultId)) echo "<script>$('#ResultId').val('" . $ResultId . "').change();</script>";
        if (isset($user_id)) echo "<script>$('#OperatorsId').val('" . $user_id . "').change();</script>";
        //if (isset($OperatorsId)) echo "<script>$('#OperatorsId').val('".$OperatorsId."').change();</script>";
    }
    ?>

    <script type="text/javascript">
        jQuery(function($){
            $("#phone_mob").mask("8(999) 999-9999");
        });
    </script>
    <!--script type="text/javascript">
        jQuery(function($){
            $("#phone_new").mask("8(999) 999-9999");
        });
    </script-->
    </form>
</div>

</body>
</html>