<?php
ini_set('max_execution_time', 700);
$user_params['phone'] = '473892935223';
$user_params['email'] = 'usero@test1.tusr';
$user_params['name'] = 'Тестер';
$user_params['company'] = 'Ипо ком1';


echo '<pre>';
include_once(__DIR__.'/functions.php');


if(isset($user_params['phone']) && !empty($user_params['phone'])) {
    $isPhoneContactExist = checkPhoneNumber($user_params['phone']); // Существует ли контакт с номером телефона. false или ид контакта
}
else{
    $isPhoneContactExist = false;
}

if(isset($user_params['email']) && !empty($user_params['email'])){
    $isCompanyContactExist = checkCompanyDomain($user_params['email']); // Совпадает ли почтовый домен с компанией. false или id контакта
    $isEmailContactExist = checkEmail($user_params['email']); // Совпадает ли контакт с почтой. false или ид контакта
    
}
else{
    $isCompanyContactExist = false;
    $isEmailContactExist = false;
}

if(isset($user_params['company']) && !empty($user_params['company'])){
    $isCompanyExist = checkCompany($user_params['company']); // Совпадает ли название компании | false или ид компании
}
else{
    $isCompanyExist = false;
}


$isContactExist = (($isPhoneContactExist !== false || $isEmailContactExist !== false || $isCompanyContactExist !== false) ? true : false); // Если контакт найден

$fleads = findLeads($user_params['phone'],$user_params['email'],$user_params['company']);



if($isContactExist) { // Если контакт существует

 /* Устанавливаем ID контакта */
 $contact_id = false;

 if($isPhoneContactExist !== true){
     $contact_id = $isPhoneContactExist;
 } 
 
 if($contact_id === false && $isEmailContactExist !== true){
     $contact_id = $isEmailContactExist;
 }
 
 if($contact_id === false && $isCompanyContactExist !== true){
     $contact_id = $isCompanyContactExist;
 }
  /* Устанавливаем ID контакта */

 if($contact_id) { // Если ID контакта установлен

$title = 'Обнаружен дубль';
$activity_text = "Оставлена заявка с сайта URL-страницы: ".$user_params['url'];
   if($fleads != false && count($fleads)){ // Если есть лид
   
    if($fleads != false && count($fleads) == 1) { // если 1 лид
        $lead_id = array_keys($fleads)[0]; 
        moveToDouble($key); //  в дубль
        $resp_id = getRespId($contact_id, 'contact');
        addActivity($contact_id, $resp_id, $title, $activity_text, 'C'); //дело в контакт

    }
    if($fleads != false && count($fleads) > 1){ // если больше 1 лида
        foreach ($fleads as $key => $value) {
            moveToDouble($key); // Все в дубль
         }
        $new_lead_id = createNewLead($user_params); // Создаем новый лид
        $resp_id = getRespId($contact_id, 'contact');
        addActivity($contact_id, $resp_id, $title, $activity_text, 'C'); // добавляем дело 
    }
 
   }
   else{ // Если нет лида
    $resp_id = getRespId($contact_id, 'contact');
    addActivity($contact_id, $resp_id, $title, $activity_text, 'C'); //дело в контакт
   }


 }

}
else{ // Если контакт не существует
    if($fleads != false && count($fleads) ){ // Если есть лид
    $title = 'Обнаружен дубль';
    $activity_text = "Оставлена заявка с сайта URL-страницы: ".$user_params['url'];
    if($fleads != false && count($fleads) == 1){  // Если 1 лид  
    $el = reset($fleads); 
    $lead_id = array_keys($fleads)[0]; 
    $resp_id = getRespId($lead_id, 'lead');
    
    $must_update = $el;   
    updateLead($user_params, $lead_id, $must_update); //обновляем лид 
    addActivity($lead_id, $resp_id, $title, $activity_text, 'L'); // добавляем дело 
    }
    if($fleads != false && count($fleads) > 1){ // Если больше 1 лида
        foreach ($fleads as $key => $value) {
           moveToDouble($key); // Все в дубль
        }
       $new_lead_id = createNewLead($user_params); // Создаем новый лид
       $resp_id = getRespId($new_lead_id, 'lead');
       addActivity($new_lead_id, $resp_id, $title, $activity_text, 'L'); // добавляем дело 
    }
    }
    else{ // Если нет лида
        createNewLead($user_params); // Создаем новый лид
    }
   



}








?>