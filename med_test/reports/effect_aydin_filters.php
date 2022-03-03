<?php
require_once 'med/check_auth.php';
extract($_REQUEST);

$report_id=6; //$_SESSION['reports']['ID'];
//Проверка прав доступа к данному отчету
if(!isset($_SESSION['access']['report'][$report_id])) {
	echo "<div style='color:red'>Ошибка: доступ запрещен</div></br>";
	exit();
}
require_once "med/conn_string.cfg.php";
?>
<!DOCTYPE html >
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="../billing.css">
	<script src="../js/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="../js/jquery.datetimepicker.css">
    <script src="../js/jquery.datetimepicker.full.js"></script>
    <?php
    //DEFINE("DEVICES", array('', 'Телефон', 'e-mail'));

    if (isset($getdet)) {
        if ($getdet != -1)
            $strfilt = " and SOURCE_TYPE = " . $getdet;
        else $strfilt = NULL;
        $sql = "select sa.id,sa.bnumber,sa.name,sa.source_type from SOURCE_AUTO sa where id > 0 --deleted is null and 
" . ($_SESSION['user_role'] == 1 ? "" : "and id in (select decode(ad.source_auto_id,-1,sa.id,ad.source_auto_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='" . $_SESSION['login_id_med'] . "' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)") . $strfilt .
            "order by sa.name,sa.source_type";
        $query = OCIParse($c, $sql);
        OCIExecute($query);
        $nrows = OCI_Fetch_All($query, $array_source_auto, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        $sel = "<select multiple id=\"sel_source_auto\" name=\"sel_source_auto[]\" style=\"margin-bottom: 5px; width: 100%; height:425px;\">";
        $sel .= "<option selected=\"selected\" value=\"-1\">Все источники</option>";
        if ($nrows > 0) {
            foreach ($array_source_auto as $key => $value) {
                if (strstr($value['NAME'], '"')) $value['NAME'] = str_replace('"', '\"', $value['NAME']);
                elseif (strstr($value['NAME'], '\'')) $value['NAME'] = str_replace('\'', '\"', $value['NAME']);
                if (1 == $value['SOURCE_TYPE'])
                    $sel .= "<option value=\"" . $value['ID'] . "\">(" . $value['BNUMBER'] . ")&nbsp;" . $value['NAME'] . "</option>";
                else $sel .= "<option value=\"" . $value['ID'] . "\">(e-mail)&nbsp;" . $value['NAME'] . "</option>";
            }
        }
        $sel .= "</select>";

        echo "<script>parent.document.getElementById('S_AutoSel').innerHTML='" . $sel . "';</script>";
        exit();
    }
    ?>
</head>	
<body style="margin-top: 0; margin-bottom: 0">

<form name='frm' method=post target='rep_result'>
<table style='height:100%; border:0 solid;'><tr><td valign=top>
<?php
//Даты
echo "<h3 style='margin: 0'>Даты:
с <input type='text' name='rep_start_date' id='rep_start_date' autocomplete='off' style='width: 5em;' value='".$_SESSION['reports']['start_date']."'/>
по <input type='text' name='rep_end_date' id='rep_end_date' autocomplete='off' style='width: 5em;' value='".$_SESSION['reports']['end_date']."'/>
</h3>";

//Тип источника
echo "<iframe name='ifr_all' style='display: none; width: 99%;'></iframe>";
echo "<h3 style='margin: 0'>Тип источника:<br/>";
echo "<select id='sel_source_type' name='sel_source_type' style='width:225px'
    onchange='ifr_all.location=\"effect_aydin_filters.php?getdet=\"+this.value'>";
echo "<option selected value='-1'>Все</option>";

$sql="select st.id,st.name from source_type st where id > 0
".($_SESSION['user_role']==1?"":"and id in (select decode(ad.source_type_id,-1,st.id,ad.source_type_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
" order by st.name";

$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"NAME")."</option>";
}
echo "</select></h3>";
echo '<script>$("#sel_source_type").prop("selectedIndex",0).change();</script>';

//Услуги
echo "<h3 style='margin: 0'>Услуги:<br>";
echo "<select multiple id='sel_services' name='sel_services[]' size=6 style='min-width:225px'>";
echo "<option selected value='-1'>Все услуги</option>";

$sql="select s.id,s.name from services s where deleted is null and id > 0
".($_SESSION['user_role']==1?"":"and id in (select decode(ad.service_id,-1,s.id,ad.service_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
" order by s.name";
$q=OCIParse($c,$sql);
OCIExecute($q);
$nServices=0;
while(OCIFetch($q)) {
    $nServices++;
    echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"NAME")."</option>";
	$_SESSION['reports']['services'][OCIResult($q,"ID")]=OCIResult($q,"NAME");
}
echo "</select></h3>";

//Детализация
echo "<h3 style='margin: 0'>Уточнение:<br>";
echo "<select multiple id='sel_serv_det' name='sel_serv_det[]' size=10 style='min-width:225px;width:225px'>";
echo "<option selected value='-1'>Все уточнения</option>";

$sql="select s.id,s.service_id,s.name from SERVICE_DET s where deleted is null and id > 0
".($_SESSION['user_role']==1?"":"and service_id in (select decode(ad.service_id,-1,s.id,ad.service_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
" order by s.service_id,s.name";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    //echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"NAME")."</option>";
    if ($nServices > 2) {
        switch (OCIResult($q,"SERVICE_ID")) {
            case 1: $startstr = '(Стом.) '; break;
            case 2: $startstr = '(Косм.) '; break;
            case 3: $startstr = '(Гинек.) '; break;
            case 4: $startstr = '(Пласт.) '; break;
            case 5: $startstr = '(Трих.) '; break;
            default: $startstr = '';
        }
        echo "<option value='".OCIResult($q,"ID")."'>".$startstr.OCIResult($q,"NAME")."</option>";
    }
    else echo "<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"NAME")."</option>";
    $_SESSION['reports']['serv_det'][OCIResult($q,"ID")]=OCIResult($q,"NAME");
}
echo "</select></h3>";
?>
</td><td valign=top rowspan=2>
            <h3 style='margin: 0'>Источник (авто):
                <span id='S_AutoSel'>&nbsp;</span>
            </h3>
<!--h3 style='margin: 0'>Источники (авто):

<select multiple id='sel_source_auto' name='sel_source_auto[]' style='height:425px; width:100%'>
<option selected value='-1'>Все источники</option>
    < ?php

$sql="select sa.id,sa.bnumber,sa.name,sa.source_type from SOURCE_AUTO sa where id > 0 --deleted is null and 
".($_SESSION['user_role']==1?"":"and id in (select decode(ad.source_auto_id,-1,sa.id,ad.source_auto_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
"order by sa.name,sa.source_type";
$q=OCIParse($c,$sql);
OCIExecute($q);
while(OCIFetch($q)) {
    if (OCIResult($q,"SOURCE_TYPE")=='1')
        echo "<option value='".OCIResult($q,"ID")."'>(".OCIResult($q,"BNUMBER").")&nbsp;".OCIResult($q,"NAME")."</option>";
    else echo "<option value='".OCIResult($q,"ID")."'>(e-mail)&nbsp;".OCIResult($q,"NAME")."</option>";
}
?>
</select></h3-->
</td></tr>
<tr><td>
<input type="submit" name="Export_xlsx" id="Export_xlsx" value="В эксель" class="xlsx_button" onclick="frm.action='effect_aydin_xlsx.php';frm.submit();"/>
</td></tr>
</table>

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