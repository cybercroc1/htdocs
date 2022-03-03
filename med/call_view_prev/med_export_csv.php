<?php
session_name('medc');
session_start();
//$sid=session_id();
extract($_REQUEST);
require_once '../funct.php';

if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
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

    <?php if (EXPORT_CALL == $export_type) { ?>
        <title>Экспорт звонков</title>
        <meta name="description" content="Экспорт звонков">
    <?php } else { ?>
        <title>Отчет по операторам</title>
        <meta name="description" content="Отчет по операторам">
    <?php } ?>
    <script src="../js/jquery.datetimepicker.full.js"></script>
</head>
<body>
<?php
    if (!isset($_SESSION['login_id_med'])) {
        echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; echo "| <a href=/?exit><span style='color: red'>выход</span></a>";
        exit();
    }
?>
<form action="./export.php" method="POST">
    <h2>Даты:&nbsp;
        с <input type="text" name="rep_start_date" id="rep_start_date" style="width: 7em;" value="<?= !empty($start_date) ? $start_date : '' ?>" />
        по <input type="text" name="rep_end_date" id="rep_end_date" style="width: 7em;" value="<?= !empty($end_date) ? $end_date : '' ?>" />
        <?php
        echo "<div style='display: table; margin-top: 5px; margin-bottom: 5px'>";
        if (EXPORT_CALL == $export_type) {
            $nServices = 18 * (GetData::GetServices(FALSE, FALSE, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE)) + 1);
            echo "<label for='ServiceId' style='float: left;'>Услуга:&nbsp;</label>";
            echo "<select multiple='multiple' id='ServiceId' name='ServiceId[]' style='float: left; height: " . $nServices . "px; overflow-y: unset'>";
            echo "<option selected=\"selected\" value='".SERVICE_ALL."'>Все услуги</option>";
            if ($nServices > 0) {
                if (DB_OCI) {
                    foreach ($_POST['array_services'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv('windows-1251', 'utf-8', $value['NAME']);
                            $value['NAME'] = $tmpstr;
                        }
                        if (SERVICE_ALL == $value['ID'])
                            printf("<option selected='selected' value='%s'>%s</option>", $value['ID'], $value['NAME']);
                        else printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                } else {
                    foreach ($_POST['array_services'] as $key => $value) {
                        if (TRUE == ENCODE_UTF) {
                            $tmpstr = iconv('utf-8', 'windows-1251', $value[1]);
                            $value[1] = $tmpstr;
                        }
                        if (SERVICE_ALL == $value[0])
                            printf("<option selected='selected' value='%s'>%s</option>", $value[0], $value[1]);
                        else printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
            }
            echo "</select>";

            echo "<label for='StatusId' style='float: left;'>&nbsp;&nbsp;Статус:&nbsp;</label>";
            if (GetData::GetMedStatus("ID >= " . STATUS_CALL_STOP . " and ID <= " . STATUS_NOT_COME, false, FALSE) > 0) {
                echo "<select multiple='multiple' id='StatusId' name='StatusId[]' title='Статус' style='height: 105px; overflow-y: hidden;'>";
                echo "<option selected=\"selected\" value='-1'>Все статусы</option>";
                foreach ($_POST['array_status'] as $key => $value) {
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
                echo "</select><br/>";
            }

            echo "<br><label for='S_Type'>Тип источника рекламы:&nbsp;</label>";
            echo "<select id='S_Type' name='S_Type'>";
            if (GetData::GetSourceType(TRUE, TRUE) > 0) {
                if (DB_OCI) {
                    foreach($_POST['array_stype'] as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv ('windows-1251', 'utf-8', $value['NAME']);
                        printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                    }
                }
                else {
                    foreach ($_POST['array_stype'] as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[1] = iconv ('utf-8', 'windows-1251', $value[1]);
                        printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                    }
                }
            }
            echo "</select>";
            //echo "<script>$('#S_Type').val('-1').change();</script>";
        }
        echo "</div>";
        if (EXPORT_CALL == $export_type) {
            echo "<label for='Reservoir' style='float: left;'>Источник рекламы:&nbsp;</label>";
            $nSources = GetData::GetIstochnik(TRUE, FALSE, "instr(in_dep, '-1') != 0", (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
            if (++$nSources < 9) { $nSources *= 19; $scroll = ' overflow-y: unset'; }
            else { $nSources = 175; $scroll = '';}
            if ($nSources > 0) {
                echo "<select id='Reservoir' name='Reservoir[]' multiple='multiple' style='height: " . $nSources . "px;" . $scroll . "' title='Источник рекламы'>";
                foreach ($_POST['array_istochnik'] as $key => $value) {
                    if (TRUE == ENCODE_UTF) {
                        $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        $value['DETAIL'] = iconv('windows-1251', 'utf-8', $value['DETAIL'] . ': ');
                    }
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['NAME']);
                }
                echo "</select>";
                echo "<script>$('#Reservoir').val('".SOURCE_ALL."').change();</script>";
            }

            echo "<br><label for='S_Auto'>Источник (авто):&nbsp;</label>";
            $nSourceAuto = GetData::GetSourceAuto(NULL, NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
            if (++$nSourceAuto < 9) { $nSourceAuto *= 19; $scroll = ' overflow-y: unset'; }
            else { $nSourceAuto = 175; $scroll = ''; }
            echo "<select id='S_Auto' name='S_Auto[]' multiple='multiple' style='margin-bottom: 5px; width: 100%; height: " . $nSourceAuto . "px;" . $scroll . "'>";
            echo "<option selected=\"selected\" value='" . SOURCE_ALL . "'>Все источники</option>";
            if ($nSourceAuto > 0) {
                if (DB_OCI) {
                    foreach ($_POST['array_source_auto'] as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['NAME'] = iconv('windows-1251', 'utf-8', $value['NAME']);
                        printf("<option value='%s'>(" . DEVICES[$value['SOURCE_TYPE']] . ")-%s</option>", $value['ID'], $value['NAME']);
                    }
                } else {
                    foreach ($_POST['array_source_auto'] as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value[2] = iconv('utf-8', 'windows-1251', $value[2]);
                        printf("<option value='%s'>%s</option>", $value[0], $value[2]);
                    }
                }
            }
            echo "</select>";
        }

        echo "<div style='display: inline; width: 250px'>";
        $strfilt = "(ROLE_ID = " . USER_USER . " or ROLE_ID = " . USER_SUPER .")";
        $nUsers = GetData::GetUsersDep(FALSE, $strfilt, NULL, 'not');
        if (++$nUsers < 9 ) { $nUsers *= 19; $scroll = ' overflow-y: unset'; }
        else { $nUsers = 175; $scroll = ''; }
        echo "<label for='UserId' style='float: left;'>Оператор:&nbsp;</label>";
        echo "<select id='UserId' name='UserId[]' multiple='multiple' style='height: ".$nUsers."px;".$scroll."'>";
        echo "<option selected=\"selected\" value='-1'>Все операторы</option>";
        if ($nUsers > 0) { // dep_id or user_id of Supervise ?
            if (DB_OCI) {
                foreach ($_POST['array_userd'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value['FIO'] = iconv('windows-1251', 'utf-8', $value['FIO']);
                    printf("<option value='%s'>%s</option>", $value['ID'], $value['FIO']);
                }
            } else {
                foreach ($_POST['array_userd'] as $key => $value) {
                    if (TRUE == ENCODE_UTF)
                        $value[1] = iconv('utf-8', 'windows-1251', $value[1]);
                    printf("<option value='%s'>%s</option>", $value[0], $value[1]);
                }
            }
        }
        echo "</select>";
        echo "</div>";
        if (EXPORT_CALL == $export_type) {
            echo "<br><span >Добавить нецелевые:&nbsp;<input type=checkbox id='all_type' name='all_type'></span>";
        }
        ?>
    </h2>
    <?php if (EXPORT_CALL == $export_type) { ?>
        <input type="submit" name="Export_but" id="Export_but" value="Экспорт" class="send_button"/>
        <input type="submit" name="Export_xlsx" id="Export_xlsx" value="Экспорт в xlsx" class="send_button"/>
    <?php } elseif (EXPORT_OPERATOR == $export_type) { ?>
        <input type="submit" name="Export_but" id="Export_but" value="Сформировать" class="send_button"/><br/>
        <input type="submit" name="Export_xlsx" id="Export_xlsx" value="Сформировать в xlsx" class="send_button"/>
    <?php } ?>
    <input type="hidden" name="export_type" value="<?php echo $export_type; ?>"/>
</form>

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