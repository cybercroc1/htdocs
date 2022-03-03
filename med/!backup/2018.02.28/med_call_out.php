<!DOCTYPE html>
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
    <link rel="stylesheet" type="text/css" href="./js/jquery.datetimepicker.css">
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>Исходящий звонок</title>
    <base href="/">
    <meta name="description" content="Исходящий звонок">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="<?=PATH?>/js/jquery.maskedinput.js"></script>
    <script src="<?=PATH?>/js/jquery.datetimepicker.full.js"></script>
</head>

<body>
<?php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] == USER_VIEW ) { // обозревателю детализация не полагается?
    echo "<b style='color: red'>ОШИБКА: У Вас нет прав для просмотра данной страницы.</b>";
    exit();
}
if (!isset($base_id) or $base_id=='') {exit();}

//информация о звонке
include("./call_view/call.get.call.info.php");
extract(get_call_info(GetData::GetConnect(), $base_id));
if(isset($error)) {echo $error; exit();}

if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));

echo "<div style=\"display: inline-block;\"><h1 class=\"heading\" style=\"margin-top: -5px;\">Входящий звонок (№ ".$base_id.")</h1></div>";
?>

<div>
<form action="<?=PATH?>/med_form_out.php" method="post">
    <h2 style="display: inline;">
        <label for="PurposeId">Тема звонка:&nbsp;</label>
        <?php
        if (GetData::GetThemes("DELETED IS NULL") > 0) {
            printf("<select id='PurposeId' name='PurposeId' title='Темы звонка' disabled>");
            if (DB_OCI) {
                foreach($_POST['array_theme'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    if ($theme_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            else {
                foreach ($_POST['array_theme'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    if ($theme_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            printf("</select>");
        }
        ?>

        <label id="ServiceT" for="ServiceId">&nbsp;&nbsp;Услуги:&nbsp;</label>
        <?php
        if (GetData::GetServices("DELETED IS NULL") > 0) {
            //if (USER_SUPER == $_SESSION['user_role'])
                 printf("<select id='ServiceId' name='ServiceId' title='Услуги' disabled>");
            //else printf("<select id='ServiceId' name='ServiceId' title='Услуги'>");
            if (DB_OCI) {
                foreach($_POST['array_services'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                    }
                    if ($srv_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            else {
                foreach ($_POST['array_services'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv ('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    if ($srv_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            printf("</select>");
        }
        ?>
    </h2>
    <div id="CallType">
        <h2>
            <label for="voice">Тип звонка:&nbsp;</label>
            <?php if ($ct_id == 1) { ?>
            <input type="radio" name="voice" id="FirstCall" value=1 checked disabled title="Первичный"/> Первичный
            <input type="radio" name="voice" id="SecondCall" value=2 disabled title="Повторный" /> Повторный
            <?php } else { ?>
            <input type="radio" name="voice" id="FirstCall" value=1 disabled title="Первичный"/> Первичный
            <input type="radio" name="voice" id="SecondCall" value=2 checked disabled title="Повторный" /> Повторный
            <?php } ?>
        </h2>
    </div>

    <?php
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
            $source_auto_id = 0;
            $source_auto_name = "???";
        }
    }
    else $source_auto_name = $sraname;
    ?>

    <div id="NotTarget">
    <h2>
        <label for="Istochnik_auto">Источник рекламы (автоопределение):</label>
        <input type="text" name="Istochnik_auto" style="width: 290px;" placeholder="<?php echo $source_auto_name ?>" disabled/>
        <br/>
        <label for="Reservoir">Источник рекламы:&nbsp;</label>
        <?php
        if (GetData::GetIstochnik("DELETED IS NULL") > 0) {
            printf("<select id='Reservoir' name='Reservoir' title='Источник рекламы' disabled>");
            if (DB_OCI) {
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                        $value['NAME'] = $tmpstr;
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                        $value['DETAIL'] = $tmpstr;
                    }
                    if ($srm_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            } else {
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[2] . ': ');
                        $value[2] = $tmpstr;
                    }
                    if ($srm_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            printf("</select>");
        }
        ?>
    </h2>

    <h2 style="display: flex">
        <?php if ($srm_id < SOURCE_2GIS) {
            echo "<label style='position: relative;' id='AllInOne' for='AllSelect'>".$srmname.":&nbsp;</label>";
            echo "<div id='AllSelect' style='margin-left: 15px;'>";

            printf("<select disabled id='Detail_Name' name='Detail_Name' style='margin-top: 8px;'>");
            if (SOURCE_FLAER == $srm_id || SOURCE_CATALOG == $srm_id ||
                SOURCE_FLAER_SUB == $srm_id || SOURCE_FLAER_CAR == $srm_id ||
                SOURCE_LIFT == $srm_id || SOURCE_STOP == $srm_id)
            {
                $nrows = GetData::GetSubway(NULL); // Все одинаково с Флаером (пока?)
                $array_todo = $_POST['array_subway'];
            } else if (SOURCE_SERT == $getdet) {
                $nrows = GetData::GetHospitals(NULL);
                $array_todo = $_POST['array_hospitals'];
                $strtitle = 'Сертификат';
            } else {
                $nrows = GetData::GetSourceDetail("DELETED IS NULL", $srm_id);
                $array_todo = $_POST['array_details'];
            }
            if ($nrows > 0) {
                if (DB_OCI) {
                    foreach ($array_todo as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        if ($srdet_id == $value['ID']) {
                            printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        } else {
                            printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        }
                    }
                } else {
                    foreach ($array_todo as $key => $value) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        if ($srdet_id == $value['ID']) {
                            printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        } else {
                            printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        }
                    }
                }
            }
            if (SOURCE_SERT == $getdet) {
                //printf("<option selected=".DETAILS_PROMO == $srdet_id ? 'selected' : ''." value=".DETAILS_PROMO.">На улице у промоутера</option>");
                //printf("<option selected=".DETAILS_OTHER == $srdet_id ? 'selected' : ''." value=".DETAILS_OTHER.">Другое</option>");
                if (DETAILS_PROMO == $srdet_id)
                    printf("<option selected=\"selected\" value=".DETAILS_PROMO.">На улице у промоутера</option>");
                else printf("<option value=".DETAILS_PROMO.">На улице у промоутера</option>");
                if (DETAILS_OTHER == $srdet_id)
                    printf("<option selected=\"selected\" value=".DETAILS_OTHER.">Другое</option>");
                else printf("<option value=".DETAILS_OTHER.">Другое</option>");
            } else if (SOURCE_COUPON != $getdet) {
                //printf("<option selected=".DETAILS_AMNESY == $srdet_id ? 'selected' : ''." value=".DETAILS_AMNESY.">Не помнит</option>");
                if (DETAILS_AMNESY == $srdet_id)
                    printf("<option selected=\"selected\" value=".DETAILS_AMNESY.">Не помнит</option>");
                else printf("<option value=".DETAILS_AMNESY.">Не помнит</option>");
            }
            printf("</select>");
        }
        echo "</div>";
        ?>
    </h2>
    </div>

    <div id="all_other" style="margin-top: 20px;">
    <h2><label for="comment">Комментарий:&nbsp;</label>
        <textarea name="comment" disabled title="Комментарий" placeholder="<?=$comment?>" rows=3 cols=70 style="vertical-align: text-top; "></textarea>
    </h2>
    <?php
    $surname = substr ($client_name, 0, strpos($client_name, '/'));
    $name = substr ($client_name, strpos($client_name, '/')+1, strrpos($client_name, '/')-strpos($client_name, '/')-1);
    $patronymic = substr ($client_name, strripos ($client_name, '/')+1, strlen($client_name));
    ?>
    <h2>
        <label for="surname">Фамилия:&nbsp;</label>
        <input type="text" name="surname" disabled placeholder="<?=$surname?>"/>
        <label for="name">Имя:&nbsp;</label>
        <input type="text" name="name" disabled placeholder="<?=$name?>"/>
        <br/>
        <label for="patronymic">Отчество:&nbsp;</label>
        <input type="text" name="patronymic" disabled placeholder="<?=$patronymic?>"/>
        <label for="ages">Возраст:&nbsp;</label>
        <input type="number" min="0" max="200" value="<?=$age?>" name="ages" style="width: 4em;"/>
        <br/>
        <label for="phone_mob">Телефон мобильный:&nbsp;</label>
        <input type="text" id="phone_mob" name="phone_mob" style="width: 10em;" placeholder="<?= !empty($phone_mob) ? $phone_mob : 'Мобильный телефон' ?>"/>
        <label for="phone_home">&nbsp;Домашний:&nbsp;</label>
        <input type="text" id="phone_home" name="phone_home" style="width: 10em;" placeholder="<?= !empty($phone_home) ? $phone_home : 'Домашний телефон' ?>"/>
        <br/>
        <label for="e_mail">E-mail:&nbsp;</label>
        <input type="email" name="e_mail" placeholder="<?= !empty($email) ? $email : 'Введите e-mail' ?>" style="width: 22em;"/>
        <?php if (USER_SUPER == $_SESSION['user_role']) { ?>
            <script>document.all.phone_mob.disabled=true;</script>
            <script>document.all.phone_home.disabled=true;</script>
            <script>document.all.e_mail.disabled=true;</script>
            <script>document.all.ages.disabled=true;</script>
        <?php } ?>
    </h2>

    <h2 style="display: inline-block; margin-top: 0;"> Статус:
        <?php
        if (GetData::GetMedStatus() > 0) {
            if ($status_id >= STATUS_CALL_STOP && USER_ADMIN != $_SESSION['user_role'])
                printf("<select id='StatusId' name='StatusId' title='Статус' disabled>");
            else printf("<select id='StatusId' name='StatusId' title='Статус'>");
            if (DB_OCI) {
                if (USER_SUPER == $_SESSION['user_role'] && $status_id < STATUS_CALL_NOT && $status_id != STATUS_CALL_BACK) {
                    if (STATUS_WORK == $status_id)
                        printf("<option selected=\"selected\" value='2'>Назначено</option>");
                    else printf("<option selected=\"selected\" value='2'>Назначить</option>");
                }
                else {
                    foreach ($_POST['array_status'] as $key => $value) {
                        if (STATUS_CLOSED == $value['ID']) continue;
                        if (USER_USER == $_SESSION['user_role'] && (STATUS_OPEN == $value['ID'] || STATUS_WORK == $value['ID']))
                            continue;
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        if ($status_id == $value['ID']) {
                            printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        } else {
                            printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        }
                    }
                }
            } else {
                foreach ($_POST['array_status'] as $key => $value) {
                    if (STATUS_CLOSED == $value['ID']) continue;
                    if (USER_USER == $_SESSION['user_role'] && (STATUS_OPEN == $value['ID'] || STATUS_WORK == $value['ID']))
                        continue;
                    if (FALSE == ENCODE_UTF) {
                        $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                        $value[1] = $tmpstr;
                    }
                    if ($status_id == $value['ID']) {
                        printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                    else {
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
            }
            printf("</select>");
        }
        ?>

        <?php if (USER_USER == $_SESSION['user_role'] || 
				STATUS_OPEN != $status_id && STATUS_WORK != $status_id) { ?>
            <div id="assign_cl" style="position: absolute; visibility: hidden">
        <?php } else { ?>
            <div id="assign_cl" style="<?php (USER_SUPER != $_SESSION['user_role'] ? 'position: absolute' : '' )?>">
        <?php } ?>
        &nbsp;Оператор:&nbsp;
            <?php
            $strfilt = "usr.DELETED IS NULL AND ACTIVITY IS NOT NULL AND ROLE_ID = " . USER_USER;
            if (GetData::GetUsersDep($strfilt, NULL) > 0) { // dep_id or user_id of Supervise ?
                if ($status_id >= STATUS_CALL_STOP && USER_ADMIN != $_SESSION['user_role'])
                    printf("<select id='UserId' name='UserId' disabled>");
                else printf("<select id='UserId' name='UserId'>");
                if (DB_OCI) {
                    foreach ($_POST['array_userd'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv('windows-1251', 'utf-8', $value['FIO']);
                            $value['FIO'] = $tmpstr;
                        }
                        if ($texnari_id == $value['ID']) {
                            printf("<option selected=\"selected\" value='%s'>%s</option>", $value['ID'], $value['FIO']);
                        }
                        else {
                            printf("<option value='%s'>%s</option>", $value['ID'], $value['FIO']);
                        }
                    }
                } else {
                    foreach ($_POST['array_userd'] as $key => $value) {
                        if (FALSE == ENCODE_UTF) {
                            $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
                printf("</select>");
            }
            ?>
            <br/>
        </div>
                <!--?php if (STATUS_CALL_BACK != $status_id && STATUS_WORK != $status_id) { ?>
                <div id="call_cl" style="position: absolute; visibility: hidden">
                < ?php } else if (STATUS_CALL_BACK == $status_id && USER_USER != $_SESSION['user_role'] && USER_ADMIN != $_SESSION['user_role']) { ?>
                <div id="call_cl" style="position: inherit">
                < ?php } else { ?>
                <div id="call_cl" style="position: absolute">
                < ?php } ?-->
        <?php if (STATUS_CALL_BACK == $status_id) { ?>
            <div id="call_cl" style="position: inherit">
        <?php } else if (STATUS_WORK == $status_id && (USER_USER == $_SESSION['user_role'] || USER_ADMIN == $_SESSION['user_role'])) { ?>
            <div id="call_cl" style="position: absolute">
        <?php } else { ?>
            <div id="call_cl" style="position: absolute; visibility: hidden">
        <?php } ?>
            &nbsp; Дата и время перезвона:&nbsp;
            <input type="text" name="datetimepicker" id="datetimepicker" placeholder="<?= !empty($call_back_date) ? $call_back_date : '' ?>"/>
        </div>

        <?php if (STATUS_CLINIC != $status_id) {
            if (USER_SUPER == $_SESSION['user_role']) { ?>
        <div id="write_cl" style="visibility: hidden; position: absolute">
            <?php } else { ?>
        <div id="write_cl" style="visibility: hidden">
            <?php } ?>
        <?php } else { ?>
        <div id="write_cl" style="position: inherit">
            <?php }
            echo "&nbsp;Клиника:&nbsp;";
            if (GetData::GetHospitals(NULL) > 0) {
                if ($status_id >= STATUS_CALL_STOP && USER_ADMIN != $_SESSION['user_role'])
                    printf("<select id='Clinic' name='Clinic' disabled>");
                else printf("<select id='Clinic' name='Clinic'>");
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
                        }
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]." (".$value[2].")");
                    }
                }
                printf("</select>");
            }
            ?>
            <br/>
            <label for="surname_cl">Фамилия:&nbsp;</label>
            <input type="text" name="surname_cl" placeholder="<?= !empty($surname) ? $surname : 'Фамилия' ?>" />
            <label for="name_cl">Имя:&nbsp;</label>
            <input type="text" name="name_cl" placeholder="<?= !empty($name) ? $name : 'Имя' ?>"/>
            <br/>
            <label for="patronymic_cl">Отчество:&nbsp;</label>
            <input type="text" name="patronymic_cl" placeholder="<?= !empty($patronymic) ? $patronymic : 'Отчество' ?>"/>
            <label for="ages_cl">Возраст:&nbsp;</label>
            <input type="number" min="0" max="200" value="<?=$age?>" name="ages_cl" style="width: 4em;"/>
        </div>

        <div id="History"> История событий:
            <br/>
            <?php
            if (GetData::GetCallHistory($base_id) > 0) {
                echo "<table style='display: inline-block; border-spacing: 0;'>
                <tr>
                    <th style='width: 120px; border:1px solid grey;'>Дата</th>
                    <th style='border:1px solid grey;'>Пользователь</th>
                    <th style='border:1px solid grey;'>Статус</th>
                    <th style='width: 320px; border:1px solid grey;'>Примечание</th>
                </tr>";
            foreach ($_POST['array_hist'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $tmpstr = iconv('windows-1251', 'utf-8', $value['COMMENTS']);
                        $value['COMMENTS'] = $tmpstr;
                    }
                    $comment_cut = str_replace("c_b=", "", $value['COMMENTS']);
                    echo '<tr><td style="text-align: center; border:1px solid grey;">' . $value['DATE_DET'] . '</td>
                        <td style="text-align: center; border:1px solid grey;">' . $value['FIO'] . '</td>
                        <td style="text-align: center; border:1px solid grey;">' . $value['NAME'] . '</td>
                        <td style="text-align: left; border:1px solid grey;">' . $comment_cut . '</td></tr>';
                }
                echo "</table>";
            }
            ?>
        </div>
        <br/>
        <label for="comment_cl">Примечание:&nbsp;</label>
        <textarea name="comment_cl" title="Примечание" placeholder="Введите примечание" rows=3 cols=70 style="vertical-align: text-top; "></textarea>

        <script type = "text/javascript">
            var select = document.getElementById("StatusId");
            select.onchange = function()
            {
            if (<?=STATUS_OPEN?> == this.value || 0 == this.value) { // только создана
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            if (<?=STATUS_WORK?> == this.value) { // В работе
                document.getElementById('assign_cl').style.visibility = 'visible';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            else if (<?=STATUS_CALL_BACK?> == this.value) { // Просьба перезвонить
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'visible';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            else if (<?=STATUS_CALL_NOT?> == this.value) { // Недозвон
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            else if (<?=STATUS_CALL_STOP?> == this.value) { // Глухой недозвон
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            else if (<?=STATUS_CLINIC?> == this.value) { // Перевод в Клинику
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'visible';
            }
            else if (<?=STATUS_NEGATIVE?> == this.value) { // Отказ
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            else  if (<?=STATUS_ERROR?> == this.value) { // Ошибка
                document.getElementById('assign_cl').style.visibility = 'hidden';
                document.getElementById('call_cl').style.visibility = 'hidden';
                document.getElementById('write_cl').style.visibility = 'hidden';
            }
            }
        </script>
    </h2>
    </div>

    <?php if (USER_ADMIN == $_SESSION['user_role'] ||
			($status_id < STATUS_CALL_STOP && (USER_USER == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role'])) ) { ?>
        <input type="submit" value="Сохранить" class="send_button" />
        <input type="hidden" name="Base_Id" value="<?php echo $base_id; ?>"/>
    <?php } ?>

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
    <script type="text/javascript">
        $('#datetimepicker').datetimepicker({
            format: 'd.m.Y H:i',
            lang: 'ru',
            timepicker: true
        });
    </script>
</form>
</div>

</body>
</html>