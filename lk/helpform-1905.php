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
if(isset($_POST['id31705'])){
    //Собираем данные в переменные и отправляем отчет в ЛК
    $id28192=$_POST['id28192'];
    $id28193=$_POST['id28193'];
    require_once "phones_conv.php";
	$id28194=phones_segment(phones_norm($_POST['id28194'],'int_dial+'),'+','array');
    $id28195=$_POST['id28195'];
    $id28196=$_POST['id28196'];
    $id28197=$_SESSION['project']['name'];
    //$form_values[28192]=mb_convert_encoding($id28192, 'cp1251', 'utf-8');
    $form_values[28192]=$id28192;
    $form_values[28193]=$id28193;
    $form_values[28194]=$id28194;
    $form_values[28195]=$id28195;
    $form_values[28196]=$id28196;
    $form_values[28197]=$id28197;
    $res=form_save($c,$form_id='31705',$call_values=array(),$form_values=$form_values,$call_id='',$project_id='',$tree_id='');
    if($res['result_text']=='OK'){
        $repid = $res['report_id'];
        
        echo "<br><div class=rep_head>Обращение сформировано.<br>";
        //Выбор почты и отправка отчета на почту
        if($id28195=="124959"){
            //Техническая проблема - не работает линия
            $email = "helpdesk@wilstream.ru";
        }
        elseif($id28195=="124960"){
            //Качество обслуживания звонков
            $email = "helpdesk@wilstream.ru";
        }
        elseif($id28195=="124961"){
            //Проблема с менеджером проектов
            $email = "liana@wilstream.ru,bdarya@wilstream.ru";
        }
        else{
            //Другое
            $email = "helpdesk@wilstream.ru";
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
    Ваше Имя<input type="text" name="id28192"><br>
    Ваш E-mail<input type="text" name="id28193"><br>
    Контактный телефон<input type="text" name="id28194"><br>
    Тип обращения<select name="id28195" class='sel_form'>
        <option value="124959">Техническая проблема/не работает линия</option>
        <option value="124960">Качество обслуживания звонков</option>
        <option value="124961">Проблема с менеджером проекта</option>
        <option value="124962">Другое</option>
    </select><br>
    Опишите ситуацию:<br><textarea cols="100" rows="5" name="id28196"></textarea><br>
    <input class=menubtn type="submit" name="id31705" value="Отправить"><br>
</div>
</form>
<?php } ?>