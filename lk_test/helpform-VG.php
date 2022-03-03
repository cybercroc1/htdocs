<?php 
//Обвязка
require_once "auth.php";
if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
require_once "../../htdocs_local/sc/func_form_save.php";
require_once "../../htdocs_local/sc/func_send_rep2email_utf8.php";
require_once "lk/lk_ora_conn_string.php";
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
<?php } ?>
</head>
<body class=rep-form>
<?php
//Обработчик формы
if(isset($_POST['id23573'])){
    //Собираем данные в переменные и отправляем отчет в ЛК
    $id19023=$_POST['id19023'];
    $id19024=$_POST['id19024'];
    require_once "phones_conv.php";
	$id19025=phones_segment(phones_norm($_POST['id19025'],'int_dial+'),'+','array');	
    $id19026=$_POST['id19026'];
    $id19027=$_POST['id19027'];
    $id19028=$_SESSION['project']['name'];
    //$form_values[28192]=mb_convert_encoding($id28192, 'cp1251', 'utf-8');
    $form_values[19023]=$id19023;
    $form_values[19024]=$id19024;
    $form_values[19025]=$id19025;
    $form_values[19026]=$id19026;
    $form_values[19027]=$id19027;
    $form_values[19028]=$id19028;
    $res=form_save($c,$form_id='23573',$call_values=array(),$form_values=$form_values,$call_id='',$project_id='',$tree_id='');
    if($res['result_text']=='OK'){
        $repid = $res['report_id'];
        
        echo "<br><div class=rep_head>Обращение сформировано.<br>";
        //Выбор почты и отправка отчета на почту
        if($id19026=="34979"){
            //Техническая проблема - не работает линия
            $email = "helpdesk1@wilstream.ru";
        }
        elseif($id19026=="34980"){
            //Качество обслуживания звонков
            $email = "helpdesk1@wilstream.ru";
        }
        elseif($id19026=="34981"){
            //Проблема с менеджером проектов
            $email = "panfilova@wilstream.ru,bdarya@wilstream.ru";
        }
        else{
            //Другое
            $email = "helpdesk1@wilstream.ru";
        }
		//$email='cybercroc@gmail.com';
        $res2=send_rep2email($c,$repid,$email,$send_record_link='');
        if($res2['result_text']=='OK'){
            echo"Отчет отправлен на EMail ответственного лица. Ожидайте с вами свяжутся.</div>";
            exit();
        }
        else{
            echo "<br>Ошибка2:".$res2['result_text'];
            exit();
        }
    }
    else{
        echo "<br>Ошибка:".$res['result_text'];
        exit();
    }
}
else{
//Форма для отправки заявки    
?>
<form action="" method="post">
<div class=rep_head>
<nobr><font size=4>Обратная связь</font></nobr><br>
    Ваше Имя<input type="text" name="id19023"><br>
    Ваш E-mail<input type="text" name="id19024"><br>
    Контактный телефон<input type="text" name="id19025"><br>
    Тип обращения<select name="id19026" class='sel_form'>
        <option value="34979">Техническая проблема/не работает линия</option>
        <option value="34980">Качество обслуживания звонков</option>
        <option value="34981">Проблема с менеджером проекта</option>
        <option value="34982">Другое</option>
    </select><br>
    Опишите ситуацию:<br><textarea cols="100" rows="5" name="id19027"></textarea><br>
    <input class=menubtn type="submit" name="id23573" value="Отправить"><br>
</div>
</form>
<?php } ?>