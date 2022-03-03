<?php 
/* Включаем логирование PHP, 
прописываем максимальное время исполнения скрипта 
====================================================================
*/
ini_set('max_execution_time', 2000);
ini_set("log_errors", 1);
ini_set("error_log", "C:\Apache24\htdocs\bt24/new_parse.data");
extract($_POST);

$log_file_name = __DIR__ .'/new_logs/'.time().'_'.rand(0,9999).'.data'; // Файл логов для заявки

$req_dump = print_r($_REQUEST, TRUE);
$fp = fopen($log_file_name, 'a');
fwrite($fp, __FILE__ . 'Request_'.$req_dump);
fclose($fp);

/*==================================================================== */ 


require_once(__DIR__ .'/functions.php'); // Подключаем файл функций



if(isset($formname)){

    $user_params = [];
    
    $user_params['source'] = getSource($url); // Источник
    if(isset($question) && !empty($question)) $user_params['question'] = $question; // Вопрос
    if(isset($name)) $user_params['name'] = $name; // Имя клиента
    if(isset($phone)) $user_params['phone'] = $phone; // Телефон клиента
    if(isset($email)) $user_params['email'] = $email; // Почта клиента
    if(isset($url)) $user_params['url'] = $url; // URL страницы заявки
    if(isset($company)) $user_params['company'] = $company; // Компания клиента
    if(isset($usluga)) $user_params['usluga'] = $usluga; // Услуга
    if(isset($comment)) $user_params['comment'] = $comment; // комментарий

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
  
    
        include_once(__DIR__.'/script.php');
}













if(isset($form_type)){
	$ar_interest = [];
	if(isset($interest)){
    foreach ($interest as $key => $value) {
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
    else{
        $interest = '';
    }
}
    
    $user_params = [];
    $user_params['source'] = getSource($url);
    switch($form_type){
        case 'ask-quest-popup':
        case 'service-tab-quest':
        if(isset($text) && !empty($text)) $user_params['question'] = $text;
        break;
        case 'callback':
        $user_params['addToComment'] = 'Обратный звонок';
        break;
        case 'order-price-popup':
        case 'service-tab-connect':
        $user_params['usluga'] = $interest;
        if(isset($text)) $user_params['comment'] = $text; // Комментарий
        break;

        
    }

if(isset($name)) $user_params['name'] = $name; // Имя клиента
if(isset($phone)) $user_params['phone'] = $phone; // Телефон клиента
if(isset($email)) $user_params['email'] = $email; // Почта клиента
if(isset($url)) $user_params['url'] = $url; // URL страницы заявки
if(isset($company)) $user_params['company'] = $company; // Компания клиента


    include_once(__DIR__.'/script.php');

}


?>