<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<?php
extract($_REQUEST);
ini_set('session.use_cookies','1');

//if (isset($sid)) session_id($sid);
//else $sid=session_id();
session_start();

require_once 'funct.php';
?>

<head>
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>�������� ������</title>
    <base href="/">
    <meta name="description" content="�������� ������">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?=PATH?>/js/jquery.maskedinput.js"></script>

    <?php
    if (isset($getdet))
    {
        if ($getdet < SOURCE_2GIS) { // � ��������� ������ ���
            if (SOURCE_FLAER == $getdet || SOURCE_CATALOG == $getdet ||
                SOURCE_FLAER_SUB == $getdet || SOURCE_FLAER_CAR == $getdet ||
                SOURCE_LIFT == $getdet || SOURCE_STOP == $getdet)
            {
                $getdetailstr = "SELECT ID, NAME FROM SUBWAYS WHERE city = 1"; // ���� ������ ������
                $strtitle = '������� �����';
            } else if (SOURCE_SERT == $getdet) {
                //$getdetailstr = "SELECT ID, NAME FROM HOSPITALS";
                $getdetailstr = "SELECT hosp.ID AS ID, (hosp.CITY || '-' || hosp.NAME || '(' || serv.NAME || ')') AS NAME
                    FROM HOSPITALS hosp, SERVICES serv 
                    WHERE hosp.DELETED IS NULL AND hosp.SERVICE_ID = serv.ID ORDER BY hosp.CITY, hosp.NAME, serv.NAME";
                $strtitle = '����������';
            } else {
                $nrows = GetData::GetIstochnik("ID = " . $getdet);
                $strtitle = '�����������';
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    $strtitle = $value['DETAIL'];
                }
                $getdetailstr = "SELECT ID, NAME FROM SOURCE_MAN_DETAIL WHERE source_man_id=" . $getdet;
            }
            echo "<script>parent.document.getElementById('AllInOne').innerHTML='" . $strtitle . ": ';</script>";
            $q = OCIParse(GetData::GetConnect(), $getdetailstr);
            if (OCIExecute($q)) {
                $i = 0;
                //$sel = "<select id=\"DetailList\" name=\"DetailList\" style=\"display: inline\">";
                $sel = "<select id=\"DetailList\" name=\"DetailList\" >";
                while (OCIFetch($q)) {
                    $i++;
                    $sel .= "<option value=" . OCIResult($q, "ID") . ">" . OCIResult($q, "NAME") . "</option>";
                }
                if (SOURCE_SERT == $getdet) {
                    $sel .= "<option value=".DETAILS_PROMO.">�� ����� � ����������</option>";
                    $sel .= "<option value=".DETAILS_OTHER.">������</option>";
                } else if (SOURCE_COUPON != $getdet) {
                    $sel .= "<option value=".DETAILS_AMNESY.">�� ������</option>";
                }
                $sel .= "</select>";
                if ($i == 0) {
                    $sel = '������ ������ �����������!';
                }
                echo "<script>parent.document.getElementById('AllSelect').innerHTML='" . $sel . "';</script>";
            }
        }
        else {
            echo "<script>parent.document.getElementById('AllInOne').innerHTML='';</script>";
            echo "<script>parent.document.getElementById('AllSelect').innerHTML='';</script>";
        }
        exit();
    }
    ?>
</head>

<body>
<?php
    $source_auto_id = 0;
    $source_auto_name = "???";
    if (isset($_GET['bnumber'])) {
        $nrowsAuto = GetData::GetSourceAuto(NULL, $_GET['bnumber']);
        if (isset($_POST['array_source_auto'])) {
            if (DB_OCI) {
                $source_auto_id = $_POST['array_source_auto'][0];
                if (TRUE == ENCODE_UTF)
                    $source_auto_name = iconv('windows-1251', 'utf-8', $_POST['array_source_auto'][1]);
                else
                    $source_auto_name = $_POST['array_source_auto'][1];
            }
            else {
                $source_auto_id = $_POST['array_source_auto'][0];
                //$source_auto_name = $_POST['array_source_auto'][1];
                $source_auto_name = iconv('utf-8', 'windows-1251', $_POST['array_source_auto'][1]);
            }
        }
        else {
// ???
        }
    }

    // ����� ��������� ������ � CALL_BASE � ���������� �������
    $date_call = date("d-m-Y  H:i:s");
    $insertstr = "INSERT INTO CALL_BASE (ID, DATE_CALL, ANUMBER, BNUMBER, SC_AGID, SC_CALL_ID, SC_PROJECT_ID,
        SOURCE_AUTO_ID, STATUS_ID, LAST_CHANGE )
        VALUES (SEQ_CALL_BASE_ID.NEXTVAL, to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'),
        '{$_GET['anumber']}', '{$_GET['bnumber']}', '{$_GET['sc_agid']}', {$_GET['sc_call_id']}, {$_GET['sc_project_id']},
        ".$source_auto_id.", ".STATUS_OPEN.", to_date('{$date_call}','DD.MM.YYYY hh24:mi:ss'))";
    //$insertstr .= "; returning id into last_id";
    //echo "<textarea>".$insertstr."</textarea>";
    $query = OCIParse(GetData::GetConnect(), $insertstr);
    $query_result = OCIExecute($query);

    $sqlstr = "SELECT SEQ_CALL_BASE_ID.CURRVAL from CALL_BASE";
    $query = OCIParse(GetData::GetConnect(), $sqlstr);
    if (OCIExecute($query)) {
        $objResult = OCI_Fetch_Row($query);
        $max_call = $objResult[0];
    }
    oci_free_statement($query);
?>

<div style="display: inline-block;">
    <a href="./"><h1 class="heading" style="margin-top: -5px;">�������� ������</h1></a>
</div>

<div>
<form action="<?=PATH?>/med_form.php" method="post">
    <h2 style="display: inline;">
        <label for="PurposeId">���� ������:&nbsp;</label>
        <?php
        if (GetData::GetThemes("DELETED IS NULL") > 0) {
            printf("<select id='PurposeId' name='PurposeId' onchange='PurposeSelected();' title='���� ������'>");
            // �������� �������� �� ������������
            if (DB_OCI) {
                foreach($_POST['array_theme'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_theme'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            printf("</select>");
        }
        ?>

        <label id="ServiceT" for="ServiceId">&nbsp;&nbsp;������:&nbsp;</label>
        <?php
        if (GetData::GetServices("DELETED IS NULL") > 0) {
            printf("<select id='ServiceId' name='ServiceId' title='������'>");
                    // �������� �������� �� ������������
            if (DB_OCI) {
                foreach($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            }
            else {
                foreach ($_POST['array_services'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            printf("</select>");
        }
        ?>
        <script type = "text/javascript">
            var select = document.getElementById("ServiceId");
            select.onchange = function(){
                //alert(this.options[this.selectedIndex].innerHTML);
            }
        </script>
    </h2>
    <div id="CallType">
        <h2>
            <label for="voice">��� ������:&nbsp;</label>
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="FirstCall" value=1 checked title="���������"/> ���������
            <input type="radio" name="voice" onclick="FirstNoCheck();" id="SecondCall" value=2 title="���������" /> ���������
        </h2>
    </div>

    <div id="NotTarget">
    <h2>
        <label for="Reservoir">�������� �������:&nbsp;</label>
        <iframe name=ifr1 style='display:none; width: 600px'></iframe>
        <?php
        if (GetData::GetIstochnik("DELETED IS NULL") > 0) {
            //printf("<select id='Reservoir' name='Reservoir' onchange='ifr1.location=\"med/med_call.php?getdet=\"+this.value+\"&getname=\"+this.options[this.selectedIndex].innerHTML' title='�������� �������'>");
            if (PATH != "med")
                 printf("<select id='Reservoir' name='Reservoir' onchange='ifr1.location=\"med_call.php?getdet=\"+this.value'>");
            else printf("<select id='Reservoir' name='Reservoir' onchange='ifr1.location=\"med/med_call.php?getdet=\"+this.value'>");
            if (DB_OCI) {
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                        $value['DETAIL'] = $tmpstr;
                    }
                    printf("<option value=\"%s\">%s</option>", $value['ID'], $value['NAME']);
                }
            } else {
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[2] . ': ');
                        $value[2] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
            printf("</select>");
        }
        echo"<script>$('#Reservoir').val(".SOURCE_2GIS.").change();</script>";
        ?>
    </h2>

    <h2 style="display: flex;">
        <label style="position: relative;" id="AllInOne" for="AllSelect">&nbsp;</label>
        <div id="AllSelect" style="margin-left: 15px; margin-top: 3px;"></div>
    </h2>
    </div>

    <div id="all_other">
<!--    <h2>���� ������: <input type="textarea" cols="10" rows="5" name="age" style="width: 400px; height: 50px;"/></h2> -->
    <h2><label for="comment">�����������:&nbsp;</label>
        <textarea name="comment" title="�����������" placeholder="������� �����������" rows=3 cols=70 style="vertical-align: text-top; "></textarea>
        <br/>
        <label for="surname">�������:&nbsp;</label>
        <input type="text" name="surname" placeholder="�������"/>
        <label for="name">���:&nbsp;</label>
        <input type="text" name="name" placeholder="���"/>
        <br/>
        <label for="patronymic">��������:&nbsp;</label>
        <input type="text" name="patronymic" placeholder="��������"/>
        <label for="ages">�������:&nbsp;</label>
        <input type="number" min="0" max="200" name="ages" style="width: 4em;"/>
        <br/>
        <label for="phone_mob">������� ���������:&nbsp;</label>
        <input type="text" id="phone_mob" name="phone_mob" style="width: 10em;" placeholder="<?= !empty($_GET['anumber']) ? $_GET['anumber'] : '��������� �������' ?>"/>
        <label for="phone_home">&nbsp;��������:&nbsp;</label>
        <input type="text" id="phone_home" name="phone_home" style="width: 10em;" placeholder="������� ��������"/>
    </h2>

    <!--h2>E-mail: <input type="email" name="e_mail" placeholder="e-mail" style="width: 22em;"/></h2-->
    <h2 style="display: inline-block; margin-top: 0"> ���������:
        <select id="ResultId" name="ResultId" onchange="ResultSelected();" title="���������">
            <option value="0">�������� ���������</option>
            <option value="<?=RESULT_KC?>">�������� � ��</option>
            <option value="<?=RESULT_CLINIC?>">�������� � �������</option>
            <option value="<?=RESULT_WAIT?>">���� ������</option>
        </select>

        <?php
        $trans_arr = date_parse(date("Y-m-d HH:MM"));
        $const_str = $trans_arr['year'].'-'.$trans_arr['month'].'-'.$trans_arr['day'].'-'.$trans_arr['hour'];
        $num_str = GetData::GetTransferNum($const_str)?>
        <div id="KC_Number" style="position: absolute; visibility: hidden">&nbsp; ���������� �����:&nbsp;
            <span style="color: black; font-size: smaller;"><?=$const_str?>-</span>
            <span style="color: red; font-size: larger; border-bottom: dashed"><?=$num_str?></span>
            <!--input type="text" name="call_center" style="width: 5em;"/-->
        </div>
        <div id="write_cl" style="visibility: hidden">&nbsp; �������:&nbsp;
        <?php
        if (GetData::GetHospitals(NULL) > 0) {
            printf("<select id='Clinic' name='Clinic'>");
            if (DB_OCI) {
                foreach ($_POST['array_hospitals'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
            } else {
                foreach ($_POST['array_hospitals'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[2]);
                        $value[2] = $tmpstr;
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[4]);
                        $value[4] = $tmpstr;
                    }
                    printf("<option value='%s'>%s</option>", $value[0], $value['1']." - ". $value[2]." (".$value[4].")");
                }
            }
            printf("</select>");
        }
        ?>
        </div>
    </h2>

    </div>

    <p>
        <input type="submit" name="save_but" id="save_but" value="���������" class="send_button" style="visibility: hidden"/>
        <input type="hidden" name="max_call" value="<?php echo $max_call; ?>"/>
        <input type="hidden" name="date_call" value="<?php echo $date_call; ?>"/>
        <input type="hidden" name="trans_num" value="<?php echo $const_str.'-'.$num_str; ?>"/>
        <input type="hidden" name="sc_agid" value="<?php echo $_GET['sc_agid']; ?>"/>
        <!--input type="hidden" name="anumber" value="< ?php echo $_GET['anumber']; ?>"/>
        <input type="hidden" name="bnumber" value="< ?php echo $_GET['bnumber']; ?>"/>
        <input type="hidden" name="sc_call_id" value="< ?php echo $_GET['sc_call_id']; ?>"/>
        <input type="hidden" name="sc_project_id" value="< ?php echo $_GET['sc_project_id']; ?>"/>
        <input type="hidden" name="Istochnik_auto" value="< ?php echo $source_auto_name; ?>"/>
        <input type="hidden" name="Istochnik_auto_Id" value="< ?php echo $source_auto_id; ?>"/-->
    </p>
    <script type="text/javascript">
        jQuery(function($){
            $("#phone_mob").mask("(999) 999-9999");
        });
    </script>
    <script type="text/javascript">
        jQuery(function($){
            $("#phone_home").mask("(999) 999-9999");
        });
    </script>
</form>
</div>

<script type="text/javascript">
    function PurposeSelected() {
        if (0 === document.getElementById("PurposeId").selectedIndex) { // ��� ����� �������� �� �����
            document.getElementById('ServiceId').style.visibility = 'visible';
            document.getElementById('ServiceT').style.visibility = 'visible';
            document.getElementById('CallType').style.visibility = 'visible';
            document.getElementById('NotTarget').style.visibility = 'visible';
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (<?=RESULT_KC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (<?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            document.getElementById('ServiceId').style.visibility = 'hidden';
            document.getElementById('ServiceT').style.visibility = 'hidden';
            document.getElementById('CallType').style.visibility = 'hidden';
            document.getElementById('NotTarget').style.visibility = 'hidden';
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }
    }

    function FirstNoCheck() {
        if (document.getElementById('FirstCall').checked) {
            document.getElementById('all_other').style.visibility = 'visible';
            document.getElementById('KC_Number').style.visibility =
                (<?=RESULT_KC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
            document.getElementById('write_cl').style.visibility =
                (<?=RESULT_CLINIC?> == document.getElementById("ResultId").value ? 'visible' : 'hidden');
        } else {
            document.getElementById('all_other').style.visibility = 'hidden';
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
        }
    }

    function ResultSelected()
    {
        if (<?=RESULT_KC?> == document.getElementById("ResultId").value) { // ������� � ��
            document.getElementById('KC_Number').style.visibility = 'visible';
            document.getElementById('write_cl').style.visibility = 'hidden';
            document.getElementById('save_but').style.visibility = 'visible';
        } else if (<?=RESULT_CLINIC?> == document.getElementById("ResultId").value) { // ������� � �������
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'visible';
            document.getElementById('save_but').style.visibility = 'visible';
        } else if (<?=RESULT_WAIT?> == document.getElementById("ResultId").value) { // ���� ������
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
            document.getElementById('save_but').style.visibility = 'visible';
        } else {
            document.getElementById('KC_Number').style.visibility = 'hidden';
            document.getElementById('write_cl').style.visibility = 'hidden';
            document.getElementById('save_but').style.visibility = 'hidden';
        }
    }
</script>

</body>
</html>