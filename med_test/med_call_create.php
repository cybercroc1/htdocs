<?php
ini_set('session.use_cookies','1');

session_name('medc');
session_start();

extract($_REQUEST);

include("med/conn_string.cfg.php");
include("phone_conv_single.php");

require_once "check_ip.php";

$backurl = "med_call_create.php";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <?php require_once 'funct.php';?>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251" />
    <link rel="stylesheet" type="text/css" href="./billing.css">
    <title>Входящая заявка от Лейбман</title>
    <meta name="description" content="Входящая заявка от Лейбман" />
    <script src="js/jquery.maskedinput.js"></script>
</head>

<body>
<?php
$nameErr = "";
if ("POST" == $_SERVER["REQUEST_METHOD"] && isset($save_but)) { // Принимаем данные из формы
    //$date_call = date("d-m-Y H:i:s");

    $comment = (isset($comment) ? htmlspecialchars($comment,ENT_QUOTES) : "");
    $surname = (isset($surname) ? stripcslashes(htmlspecialchars($surname,ENT_QUOTES)) : "---");

    switch ($S_Auto) {
        //case 546: $service_id = SERVICE_NOT; break; // Лейбман (квиз/ call center - горячая линия)
        case 547: $service_id = SERVICE_STOM; break; // Лейбман (квиз/ стоматология)
        case 548: $service_id = SERVICE_STOM; break; // Лейбман (квиз/ кап стоматология)
        case 549: $service_id = SERVICE_TRIH; break; // Лейбман (квиз/ трихология)
        case 550: $service_id = SERVICE_PLAS; break; // Лейбман (квиз/ пластическая хирургия)
        case 551: $service_id = SERVICE_KOSM; break; // Лейбман (квиз/ косметология)
        case 552: $service_id = SERVICE_KOSM; break; // Лейбман (квиз/ похудение)
        default: $service_id = SERVICE_STOM; break;
    }
    $phone_norm = phone_norm_single($phone_mob, 'ru_dial');
    $lead_id = $call_base_id = 0;

    /*$q_curdate=OCIParse($c,"select to_char(sysdate,'YYYYMMDDHH24MISS') curdate from dual");
    OCIExecute($q_curdate);
    OCIFetch($q_curdate);
    $curdate=OCIResult($q_curdate,"CURDATE");*/

    if (FALSE == DEBUG_MODE) {
        $table_name = 'CALL_BASE';
        $table_hist = 'CALL_BASE_HIST';
        $seq_table = 'SEQ_CALL_BASE_ID';
        $seq_hist = 'SEQ_CALL_BASE_HIST_ID';
    }
    else {
        $table_name = 'CALL_BASE_TEST';
        $table_hist = 'CALL_BASE_HIST_TEST';
        $seq_table = 'SEQ_CALL_BASE_ID_TEST';
        $seq_hist = 'SEQ_CALL_BASE_HIST_ID_TEST';
    }

    $query_result = FALSE;
    $insertstr = "insert into ".$table_name." (id,date_call,call_theme_id,SOURCE_MAN_ID,SOURCE_MAN_DET_ID,source_auto_id,
        call_type_id,service_id,result_id,status_id,last_change,lead_id,source_type_id,client_name,CALL_DOUBLE,COMMENTS,
            phone_mob,phone_mob_norm,phone_new,phone_new_norm,CALL_DIRECTION,CREATE_DATE)
          values (".$seq_table.".nextval,sysdate,".THEME_MED.",".SOURCE_INTERNET.",558,".$S_Auto.",
          ".CALL_FIRST.",".$service_id.",".RESULT_WAIT.",".STATUS_OPEN.",sysdate,".$lead_id.",".DEVICE_PHONE.",'".$surname."',".CALL_FIRST.",'".$comment."',
          '".$phone_mob."','".$phone_norm."','".$phone_mob."','".$phone_norm."','in',sysdate) returning id into :call_base_id";
// 558 = PlatformaLP
    if (TRUE == DEBUG_MODE) echo "<br/><textarea>" . $insertstr . "</textarea><br/>";
    GetData::my_log($insertstr, FALSE);
    $query = OCIParse($c, $insertstr);
    OCIBindByName($query,":call_base_id",$call_base_id,125);
    $query_result = OCIExecute($query);
    if (!$query_result) GetData::my_log($insertstr, TRUE);

    if ($query_result && $call_base_id > 0) {
        $insertstr = "insert into ".$table_hist." (id,base_id,date_det,status_id,date_start,OPERATOR)
              values (".$seq_hist.".nextval,".$call_base_id.",sysdate,".STATUS_OPEN.",sysdate,'".$_SESSION['login_name']."')";
if (TRUE == DEBUG_MODE) echo "<textarea>".$insertstr."</textarea><br/>";
        GetData::my_log($insertstr, FALSE);
        $query = OCIParse($c, $insertstr);
        $query_result = OCIExecute($query);
        if (!$query_result) GetData::my_log($insertstr, TRUE);
    }
    oci_free_statement($query);


    if ($query_result) {
        echo "<p style='font-size: larger; color: green'>Звонок успешно добавлен в базу данных. </p>";
        //echo "<button type='button' name='close_but' id='close_but' class='send_button' onclick='window.close();'>Закрыть</button>";
        print "<script>function reload() {parent.location = \"$backurl\"} setTimeout('reload()', 3000);</script>";
    } else {
        echo "<p style='font-size: larger; color: red'>Произошла ошибка сохранения записи!</p>";
    }
    unset($save_but);
    //exit;
}
?>


<h1 style="display: inline-block;">Входящая заявка от Лейбман</h1>
<form action="<?php echo $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];?>" method="POST">
    <h2>
        <label id='S_AutoT' for='S_Auto'>Источник (авто):&nbsp;</label>
        <select id='S_Auto' name='S_Auto' style="background-color:<?=needs?>">
            <option value=''>Выберите источник рекламы</option>
            <?php
            $sources_auto = GetData::GetSourceAuto(" SUPPLIER_ID = 15 and sa.ID != 546 ", NULL, (USER_ADMIN == $_SESSION['user_role'] ? FALSE : TRUE));
            foreach(GetData::$array_source_auto as $key => $value) {
                echo "<option value='".$value['ID']."'>".$value['NAME']."</option>";
            }
            ?>
        </select>
    </h2>
    <h2 style="line-height: 35px;">
        <label for="surname">ФИО:&nbsp;</label>
        <input type="text" id="surname" name="surname" style="width: 50%" placeholder="Фамилия Имя Отчество"/>
        <br/>
        <label for="phone_mob">Контактный телефон:&nbsp;</label>
		<input type="text" id="phone_mob" name="phone_mob" title="Контактный телефон" placeholder="Номер телефона" style="width: 10em;"/>
        <br/>
        <label for="comment">Комментарий:</label>
        <textarea name="comment" id="comment" title="Комментарий" placeholder="Введите комментарий" rows=30 cols=68 style="vertical-align: text-top; height: 45px"></textarea>
    </h2>
    <div>
        <input type="submit" name="save_but" id="save_but" value="Сохранить" class="send_button" style="visibility: hidden"/>
    </div>
</form>

<script>
    var sel = document.getElementById('S_Auto');
    if (sel) { sel.onchange = function() {
        var elem = document.getElementById('surname');
        if (sel.value > 0) {
            sel.style.backgroundColor = 'white';
            document.getElementById('save_but').style.visibility = 'visible';
        }
        else {
            sel.style.backgroundColor = '<?=needs?>';
            document.getElementById('save_but').style.visibility = 'hidden';
            elem = document.getElementById('S_Auto');
        }
        if (elem) elem.focus();
    }}
</script>
<script type="text/javascript">
    jQuery(function($){
        $("#phone_mob").mask("8(999) 999-9999");
    });
</script>

</body>
</html>