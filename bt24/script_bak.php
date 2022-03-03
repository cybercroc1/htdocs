<?php

include_once(__DIR__.'/functions.php');

if(isset($user_params['phone']) && !empty($user_params['phone'])) {
    $isPhoneContactExist = checkPhoneNumber($user_params['phone']); // Существует ли контакт с номером телефона. false или ид контакта
  //  $isLeadByPhoneExits = findLeadsByPhone($user_params['phone']); // todo
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

    $contactHasLead = contactHasLead($contact_id); // Есть ли у контакта лид. false или ID лида


    /* Определяем переменные которые должны обновиться */
    
    $must_update = [];

    if($isPhoneContactExist !== false){
        //$user_params['phone'] = '';
    }

    if($isEmailContactExist !== false){
       // $user_params['email'] = '';
    }

    if($isCompanyExist !== false){
      //  $user_params['company'] = '';
    }

 /* Определяем переменные которые должны обновиться */

    if($contactHasLead !== false){ // Если у контакта есть лид
        $title = 'Обнаружен дубль';
        $activity_text = "Оставлена заявка с сайта URL-страницы: ".$user_params['url'];

        $resp_id = getRespId($contactHasLead); 
       
        addActivity($contactHasLead, $resp_id, $title, $activity_text);  // Добавляем дело в лид
        updateContact($user_params, $contact_id);   // Добавляем инфу в контакт
        
     
    }
    else{ // Если у контакта нет лида
       
        createNewLead($user_params); // Создаем новый лид
        updateContact($user_params, $contact_id);   // Добавляем инфу в контакт
       
    }

 }

}
else{ // Если контакт не существует
   
    createNewLead($user_params); // Создаем новый лид



}





?>