<?php 

if(isset($_POST['formname'])) $formname = $_POST['formname'];
if(isset($_POST['form-type'])) $form_type = $_POST['form-type'];


if(isset($formname)){

    $user_params = [];

    if(isset($_POST['question']) && !empty($_POST['question'])) $user_params['question'] = $_POST['question'];
    if(isset($_POST['name'])) $user_params['name'] = $_POST['name']; // Имя клиента
    if(isset($_POST['phone'])) $user_params['phone'] = $_POST['phone']; // Телефон клиента
    if(isset($_POST['email'])) $user_params['email'] = $_POST['email']; // Почта клиента
    if(isset($_POST['url'])) $user_params['url'] = $_POST['url']; // URL страницы заявки
    if(isset($_POST['company'])) $user_params['company'] = $_POST['company']; // Компания клиента
    if(isset($_POST['usluga'])) $user_params['usluga'] = $_POST['usluga']; // Услуга
    if(isset($_POST['comment'])) $user_params['comment'] = $_POST['comment']; // комментарий

    if(isset($user_params['question'])){
        $user_params['question'] = str_replace("Вопрос: ","",$user_params['question']);
        $user_params['question'] = str_replace("Комментарий: ","",$user_params['question']);
        $user_params['question'] = str_replace("Услуга: ","",$user_params['question']);
    }


    if(isset($user_params['usluga'])){
        $user_params['usluga'] = str_replace("Вопрос: ","",$user_params['usluga']);
        $user_params['usluga'] = str_replace("Комментарий: ","",$user_params['usluga']);
        $user_params['usluga'] = str_replace("Услуга: ","",$user_params['usluga']);
    }

if(isset($user_params['comment'])){
        $user_params['comment'] = str_replace("Вопрос: ","",$user_params['comment']);
        $user_params['comment'] = str_replace("Комментарий: ","",$user_params['comment']);
        $user_params['comment'] = str_replace("Услуга: ","",$user_params['comment']);
        $user_params['comment'] = str_replace("Меня интересует: ","",$user_params['comment']);
    }


    

    //$user_params['addToComment'] = 'Обратный звонок';
    
    
        include_once(__DIR__.'/script.php');
}













if(isset($form_type)){
	$ar_interest = [];
	if(isset($_POST['interest'])){
    foreach ($_POST['interest'] as $key => $value) {
    switch($value){
        case 80: 
        $interest = 'горячая линия';
        break;
        case 81:
        $interest = 'виртуальный офис';											
        break;
        case 82:
        $interest = 'смартлайн';
        break;															
        case 83:
        $interest = 'телефонные продажи';
        break;																
        case 84:
        $interest = 'актуализация базы данных';
        break;																
        case 85:
        $interest = 'телефонный опрос';
        break;															
        case 86:
        $interest = 'аренда рабочих мест';
        break;																
        case 87:
        $interest = 'идеальный секретарь';
        break;					
        case 88:
        $interest = 'информативный обзвон';
        break;					
        case 119:
        $interest = 'все услуги';
        break;
    }
    array_push($ar_interest, $interest);
}
    if(count($ar_interest)){
    $interest = implode(", ", $ar_interest);
    }
}
    
    $user_params = [];

    switch($form_type){
        case 'ask-quest-popup':
        case 'service-tab-quest':
        if(isset($_POST['text']) && !empty($_POST['text'])) $user_params['question'] = $_POST['text'];
        break;
        case 'callback':
        $user_params['addToComment'] = 'Обратный звонок';
        break;
        case 'order-price-popup':
        case 'service-tab-connect':
        $user_params['usluga'] = $interest;
        if(isset($_POST['text'])) $user_params['comment'] = $_POST['text']; // Комментарий
        break;

        
    }

if(isset($_POST['name'])) $user_params['name'] = $_POST['name']; // Имя клиента
if(isset($_POST['phone'])) $user_params['phone'] = $_POST['phone']; // Телефон клиента
if(isset($_POST['email'])) $user_params['email'] = $_POST['email']; // Почта клиента
if(isset($_POST['url'])) $user_params['url'] = $_POST['url']; // URL страницы заявки
if(isset($_POST['company'])) $user_params['company'] = $_POST['company']; // Компания клиента


    include_once(__DIR__.'/script.php');

}



/*
$var_id=0;

echo "<hr>";
echo date("d m Y H:i:s")."<hr>";

//extract($_REQUEST);

echo "<b>GET</b><br>";
show_array($_POST);
echo "<b>POST</b><br>";
show_array($_POST);
echo "<b>FILES</b><br>";
show_array($_FILES);
echo "<b>COOKIE</b><br>";
show_array($_COOKIE);
echo "<b>SERVER</b><br>";
show_array($_SERVER);
echo "<b>ENV</b><br>";
show_array($_ENV);

function show_array($arr) {
	foreach($arr as $key=>$val) {
		//echo "=========================================<br>";
		if(is_array($val)) {
			echo "$key<br>"; show_array($val);
		}
		else echo "$key = $val <br>";
		echo "<hr>";
	}
}*/
?>