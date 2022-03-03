<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);
require_once 'funct.php';
require_once "send_email.php";

include("med/conn_string.cfg.php");
include("phone_conv_single.php");

if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./billing.css">
	<meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <title>История звонков</title>
    <base href="/">
    <meta name="description" content="История звонков">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script type="text/javascript">
        $(function() {
            $(window).scroll(function() {
                if($(this).scrollTop() != 0) {
                    $('#toTop').fadeIn();
                } else {
                    $('#toTop').fadeOut();
                }
            });
            $('#toTop').click(function() {
                $('body,html').animate({scrollTop:0},800);
            });
        });
    </script>
    <script>
        function open_edit(base_id, texnari_id, sid) {
            if (base_id > 0) {
                win = window.open("<?=PATH?>/med_call_out.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid, "med_call_"+base_id, "width=800, height=640, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
                win.focus();
            }
        }
    </script>
</head>

<body>
<?php
if (!isset($_SESSION['user_role']) || USER_VIEW == $_SESSION['user_role'] && !in_array($_SESSION['login_id_med'], SPEC_USER_VIEW)) {
    echo "<b style='color: red'>ОШИБКА: У Вас нет прав для просмотра данной страницы.</b>";
    exit();
}
elseif (strlen($phone_num) < 3) {
    echo "<b style='color: red'>ОШИБКА: Для поиска требуется хотя бы 3 символа.</b>";
    exit();
}

/*if (strlen($phone_num) == 10)
    $phone_mob = "(".substr($phone_num, 0, 3).") ".substr($phone_num, 3, 3)."-".substr($phone_num, 6, 4);
else $phone_mob = $phone_num;*/
$phone_mob = phone_norm_single($phone_num, 'ru_dial');
if (strlen($phone_mob) >= 11 && '8' == substr($phone_mob,0,1))
    $phone_mob = substr($phone_mob, 1);
if ('' == $phone_mob) $phone_mob = $phone_num;

function show_res($Result, $sid)
{
    $base_id = $Result[0];
    if (GetData::GetCallHistory($base_id) > 0) {
        echo "<h3 style='margin: 0'>";
        if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
            echo "<input type='checkbox' name='sent_" . $base_id . "' title='Пометить отправленным'";
            if (STATUS_ERROR == $Result[6])
                echo " checked ";
            echo "/>";
        }
        if (USER_VIEW != $_SESSION['user_role'] && (in_array($_SESSION['login_id_med'],SPEC_USER_VIEW) || in_array($_SESSION['login_id_med'],SPEC_USER_CALL) || $_SESSION['user_role'] <= USER_SUPER)) {
            echo "<input type='submit' name='History' id='History' value='ID заявки: " . $base_id . "' class='enter_button' onclick=\"javascript:open_edit('".$base_id."','".$_SESSION['login_id_med']."','".$sid."')\" 
            style='margin-top: 0; height: 1.4em'/>";
        }
        else echo " ID заявки: ".$base_id;
        if ('2' == $Result[7]) echo "<span style='color: red'> (Дубль)</span>";

        $person = (strlen(trim($Result[1])) > 0 ? trim($Result[1]) : '---');
        echo "&nbsp;Представился: ".$person.". &nbsp;Контактный телефон: ".$Result[2]."<br/></h3>";
        if ($Result[3])
            echo "<h3 style='margin: 0'> Маршрутный номер: ".$Result[3].". &nbsp;Источник рекламы (авто): ".trim($Result[4])."<br/></h3>";
        else echo "<h3 style='margin: 0'><span style='color: navy'> E-mail. </span>&nbsp;Источник рекламы (авто): ".trim($Result[4])."<br/></h3>";
        /*if ($Result[7]) {
            echo "<h3 style='color: darkviolet; margin: 0'>Тема письма: " . $Result[7] . "<br/></h3>";
        }*/
        echo "<table style='display: inline-block; border-spacing: 0;'><tr><th style='width: 115px; border:1px solid grey;'>Дата</th><th style='width: 150px; border:1px solid grey;'>Оператор</th><th style='width: 100px; border:1px solid grey;'>Статус</th><th style='width: 360px; border:1px solid grey;'>Примечание</th></tr>";
        foreach (GetData::$array_hist as $key => $value) {
            if (TRUE == ENCODE_UTF)
                $value['COMMENTS'] = iconv('windows-1251', 'utf-8', $value['COMMENTS']);

            $l_color = 'color:'.$value['COLOR'].';';
            if (STATUS_CALL_NOT == $value['STATUS_ID'])
                $value['NAME'] = "Недозвон";

            $comment_cl = trim($value['COMMENTS']);
            if (strstr($comment_cl, "c_b=")) {//STATUS_CALL_BACK == $value['STATUS_ID']
                $comment_cut = str_replace("c_b=", "", $comment_cl);
                if (STATUS_CALL_NOT == $value['STATUS_ID']) {
                    $l_color = "color: #218aca;"; // 23a504
                    $value['NAME'] = "Перезвонить";
                }
            }
            elseif (STATUS_WORK == $value['STATUS_ID']) {
                $oper_id = substr($comment_cl, 8, stripos($comment_cl, ')')-8);
                if ($oper_id && GetData::GetUsers(TRUE, TRUE,"usr.ID = ".$oper_id, "FIO") > 0) {
                    $substr_name = GetData::$array_user[0]['FIO'];
                    $comment_cut = str_replace("fio_id=".$oper_id, "$substr_name", $comment_cl);
                }
                else $comment_cut = str_replace("fio_id=", "", $comment_cl);
            }
            elseif (STATUS_CLINIC == $value['STATUS_ID']) {
                if (GetData::GetCallWriteClinic($base_id) > 0) {
                    $comment_cut = '';
                    foreach (GetData::$array_wc as $which => $rec_wc) {
                        $comment_cut .= ($which>0?"<br/>":('' != $comment_cl ? $comment_cl."<br/>":""))." (".$rec_wc['CLIENT_NAME']." ".$rec_wc['HOSPITAL_NAME']." (".$rec_wc['CITY'].")"." ".$rec_wc['CLIENT_DATE'].")";
                    }
                }
                else $comment_cut = $comment_cl." ()";
            }
            else $comment_cut = $comment_cl;

            if (STATUS_CLINIC_NOT == $value['STATUS_ID'])
                $value['NAME'] = 'Отказ от записи';

            if (STATUS_CALL_STOP <= $value['STATUS_ID'])
                $border = 'border-bottom:1px solid grey;';
            else $border = 'border-bottom:1px dashed grey;';

            echo '<tr>
<td style="text-align: center; '.$l_color.$border.'">' . $value['DATE_DET_C'] . '</td>
<td style="text-align: center;'.$l_color.$border.'">' . $value['FIO'] . '</td>
<td style="text-align: center;'.$l_color.$border.'">' . $value['NAME'] . '</td>
<td style="text-align: left; color: #0a0a0a'.$l_color.$border.'">' . $comment_cut . '</td></tr>';
        }
        echo "</table><br/>";
    }
}

if(isset($send_email)) {
    $headers = "MIME-Version: 1.0 \r\n";
    $headers.= "Content-Type: text/html; charset=\"windows-1251\"\r\n";

    if (GetData::GetUsers(FALSE, FALSE,"usr.ID = ".$_SESSION['login_id_med'], "FIO") > 0) {
        $server = GetData::$array_user[0]['SMTP_SERVER'];
        $port = GetData::$array_user[0]['SMTP_PORT'];
        $from_email = GetData::$array_user[0]['SMTP_FROM'];
        $auth_login = GetData::$array_user[0]['SMTP_LOGIN'];
        $auth_pass = GetData::$array_user[0]['SMTP_PASS'];

        $mess_subj = "История заявок по номеру: ".$phone_mob;
        $mess = "<b>Комментарий: </b>".(isset($mess_comment)?$mess_comment:"")."<br/><br/>";
        if (FALSE == DEBUG_MODE)
            $table_name = 'CALL_BASE';
        else $table_name = 'CALL_BASE_TEST';
        $sqlstr = "SELECT cb.ID, cb.CLIENT_NAME, cb.PHONE_MOB, cb.BNUMBER, sa.NAME FROM ".$table_name." cb
         LEFT JOIN SOURCE_AUTO sa ON sa.id = cb.source_auto_id
         WHERE PHONE_MOB_NORM like '%".$phone_mob."%' or PHONE_MOB like '%".$phone_mob."%' or 
               PHONE_NEW_NORM like '%".$phone_mob."%' or PHONE_NEW like '%".$phone_mob."%' or 
               ANUMBER like '%".$phone_mob."%' or cb.BNUMBER like '%".$phone_mob."%' ORDER BY ID";
        $query = OCIParse($c, $sqlstr);
        if (OCIExecute($query)) {
            while ($Result = OCI_Fetch_Row($query)) {
                if (GetData::GetCallHistory($base_id) > 0) {
                    $person = (strlen(trim($Result[1])) > 0 ? trim($Result[1]) : '---');
                    $mess .= "<h3 style='margin: 0'> ID заявки: " .$base_id . ". &nbsp;Представился: " . $person . ". &nbsp;Контактный телефон: " . $Result[2] . "<br/></h3>";
                    if ($Result[3])
                        $mess .= "<h3 style='margin: 0'> Маршрутный номер: " . $Result[3] . ". &nbsp;Источник рекламы (авто): " . trim($Result[4]) . "<br/></h3>";
                    else $mess .= "<h3 style='margin: 0'><span style='color: navy'> E-mail. </span>&nbsp;Источник рекламы (авто): " . trim($Result[4]) . "<br/></h3>";
                    $mess .= "<table style='display: inline-block; border-spacing: 0;'>
<tr>
<th style='width: 150px; border:1px solid grey;'>Дата</th>
<th style='width: 150px; border:1px solid grey;'>Оператор</th>
<th style='width: 150px; border:1px solid grey;'>Статус</th>
<th style='width: 360px; border:1px solid grey;'>Примечание</th>
</tr>";
                    foreach (GetData::$array_hist as $key => $value) {
                        if (TRUE == ENCODE_UTF)
                            $value['COMMENTS'] = iconv('windows-1251', 'utf-8', $value['COMMENTS']);

                        $comment_cl = $value['COMMENTS'];
                        if (strstr($comment_cl, "c_b=")) //STATUS_CALL_BACK == $value['STATUS_ID']
                            $comment_cut = str_replace("c_b=", "", $comment_cl);
                        elseif (STATUS_WORK == $value['STATUS_ID']) {
                            $oper_id = substr($comment_cl, 8, stripos($comment_cl, ')') - 8);
                            if ($oper_id && GetData::GetUsers(TRUE, TRUE, "usr.ID = " . $oper_id, "FIO") > 0) {
                                $substr_name = GetData::$array_user[0]['FIO'];
                                $comment_cut = str_replace("fio_id=" . $oper_id, "$substr_name", $comment_cl);
                            } else $comment_cut = str_replace("fio_id=", "", $comment_cl);
                        } else $comment_cut = $comment_cl;
                        if (STATUS_CLINIC_NOT == $value['STATUS_ID'])
                            $value['NAME'] = 'Отказ от записи';

                        $l_color = 'color:' . $value['COLOR'] . ';';
                        if (STATUS_CALL_STOP <= $value['STATUS_ID'])
                            $border = 'border-bottom:1px solid grey;';
                        else $border = 'border-bottom:1px dashed grey;';

                        $mess .= '<tr>
<td style="text-align: center; ' . $l_color . $border . '">' . $value['DATE_DET_C'] . '</td>
<td style="text-align: center;' . $l_color . $border . '">' . $value['FIO'] . '</td>
<td style="text-align: center;' . $l_color . $border . '">' . $value['NAME'] . '</td>
<td style="text-align: left;' . $l_color . $border . '">' . $comment_cut . '</td></tr>';
                    }
                    $mess .= "</table><br/>";
                }
            }
        }

        $res = send_email($server, $port, $auth_login, $auth_pass, $to_name = '', $to_email, $_SESSION['login_name'], $from_email, $reply_to_name = '', $reply_to_email = '', $mess_subj, $mess, $headers, $debug = 'y');
        /*if (0 == strncmp($res, "OK", 2)) {
            if ($file_count == 0)
                $mess_my = "Отправлено по адресу: " . $to_email . "\r\n" . $mess;
            if (10 == $_SESSION['login_id_med']) // Соколовой дублируем на ее почту
                $to_email_my = 'anna@wilstream.ru';
            elseif (1 == $_SESSION['login_id_med'])
                $to_email_my = '2392967@gmail.com';

            if (10 == $_SESSION['login_id_med'] || 1 == $_SESSION['login_id_med'])
                $res &= send_email($server, $port, $auth_login, $auth_pass, $to_name = '', $to_email_my, $from_name, $from_email, $reply_to_name = '', $reply_to_email = '', $mess_subj, $mess_my, $headers, $debug = 'y');
        }*/

        $ins = OCIParse($c,"insert into send_mail_hist (send_date,base_id,user_id,server,port,from_email,to_email,file_list,result)
        values (sysdate,'{$base_id}','{$_SESSION['login_id_med']}','{$server}','{$port}','{$from_email}','{$to_email}','','{$res}')");
        OCIExecute($ins);
        OCICommit($c);

        if (0 == strncmp($res, "OK", 2)) {
            $arr_to_sent = explode(",", $id_list);
            foreach($arr_to_sent as $value) { // смотрим, какие были выбраны
                $strsd = "sent_".$value;
                if (isset($GLOBALS[$strsd])) {
                    $updatemail = " UPDATE ".$table_name." SET SENT_MAIL = sysdate WHERE ID = '{$value}'";
                    GetData::my_log($updatemail, FALSE);
                    $query = OCIParse(GetData::GetConnect(), $updatemail);
                    $query_result = OCIExecute($query);
                    if (!$query_result)
                        GetData::my_log($updatemail,TRUE);
                    oci_free_statement($query);
                }
            }
            echo "<script>alert('Письмо отправлено.')</script>";
        } else {
            echo "<script>alert('Ошибка отправки письма!')</script>";
        }
    }
    exit();
}
?>

<div style='display: inline-block; text-align: center; width: 100%;'>
    <h1 style='margin-top: -5px; margin-bottom: 1px;'>История заявок с номером, содержащим '<span style='color: #3aa311;'>&nbsp;<?=$phone_mob?></span>'.</h1>
</div>
<iframe name='hidden_ifr' style='width:95%; display: none'></iframe>
<?php
    echo "<div id='History'>";
    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) {
        echo "<form method='post' target='hidden_ifr'>";
    }
    if (FALSE == DEBUG_MODE)
        $table_name = 'CALL_BASE';
    else $table_name = 'CALL_BASE_TEST';
    $sqlstr = "SELECT cb.ID, cb.CLIENT_NAME, cb.PHONE_MOB, cb.BNUMBER, sa.NAME, cb.source_auto_id, cb.status_id, cb.CALL_DOUBLE 
 FROM ".$table_name." cb
 LEFT JOIN SOURCE_AUTO sa ON sa.id = cb.source_auto_id
 WHERE PHONE_MOB_NORM like '%".$phone_mob."%' or PHONE_MOB like '%".$phone_mob."%' or 
       PHONE_NEW_NORM like '%".$phone_mob."%' or PHONE_NEW like '%".$phone_mob."%' or 
       ANUMBER like '%".$phone_mob."%' or cb.BNUMBER like '%".$phone_mob."%' ORDER BY ID";
    $query = OCIParse($c, $sqlstr);
    $id_list='';//array();
    if (OCIExecute($query)) {
        while ($objResult = OCI_Fetch_Row($query)) {
            //$id_list[] = $objResult[0];
            $id_list .= $objResult[0].',';
            show_res($objResult, $sid);
        }
        if (strlen($id_list) > 0)
            $id_list = substr($id_list, 0, -1);
    }

    /*$sqlstr = "SELECT cb.ID, cb.CLIENT_NAME, cb.PHONE_MOB, cb.BNUMBER, sa.NAME, cb.source_auto_id, mlc.H_SUBJECT as m_subj
 FROM CALL_BASE cb
 LEFT JOIN SOURCE_AUTO sa ON sa.id = cb.source_auto_id
 LEFT JOIN MAIL_LEADCOLLECTOR mlc ON mlc.CALL_BASE_ID = cb.ID WHERE 1=1 ";
    if ( count($id_list) > 0 )
        $sqlstr .= " and cb.ID not in (".implode(",",$id_list).") ";
    $sqlstr .=" and cb.ID in (select CALL_BASE_ID from MAIL_LEADCOLLECTOR WHERE H_SUBJECT like '%".$phone_mob."%' or MAIL_BODY_TEXT like '%".$phone_mob."%') ORDER BY cb.ID";
    $query = OCIParse($c, $sqlstr);
    if (OCIExecute($query)) {
        $objResult = OCI_Fetch_Row($query);
        if ($objResult) {
            echo "<h3 style='text-align: center; color: black; margin: 0'>Дополнительно из e-mail...<br/></h3>";
            show_res($objResult);
            while ($objResult = OCI_Fetch_Row($query)) {
                show_res($objResult);
            }
        }
    }*/

    echo '<div id="toTop"> ^Наверх </div>';

    if (USER_ADMIN == $_SESSION['user_role'] || USER_SUPER == $_SESSION['user_role']) { ?>
    <!--form method='post' target='hidden_ifr'-->
        <label for="to_email" style="font-weight: bold">*E-mail: </label>
        <input type='text' id='to_email' name='to_email' required /><br/>
        <b>Тема письма: История заявок по номеру: <?=$phone_mob?></b><br/>
        <label for="mess_comment" style="font-weight: bold">Комментарий: </label><br/>
        <textarea name='mess_comment' title="Дополнительный текст" rows=4 cols=80></textarea><br/>
        <input type='submit' name='send_email' style='cursor:pointer; width: 117px; height: 75px; background: url("<?=PATH?>/images/envelope2.png"); background-size:100% 100%;' value="" title='Отправить письмо' />
        <input type='hidden' name="id_list" value='<?=$id_list?>'/>
    </form>
    <?php } ?>
</div>

</body>
</html>