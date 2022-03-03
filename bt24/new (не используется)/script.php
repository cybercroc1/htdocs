<?php

/* Сохраняем лог */
$req_dump = print_r($user_params, TRUE);
$fp = fopen($log_file_name, 'a');
fwrite($fp, __FILE__ . 'user_params_'.$req_dump);
fclose($fp);
/* Сохраняем лог */

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



$comment_text = genTaskComment($user_params); // Текст комментария для дубля

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
        addActivity($contact_id, $resp_id, $title, $activity_text, 'C',$comment_text); //дело в контакт

    }
    if($fleads != false && count($fleads) > 1){ // если больше 1 лида
        foreach ($fleads as $key => $value) {
            moveToDouble($key); // Все в дубль
         }
        $new_lead_id = createNewLead($user_params); // Создаем новый лид
        $resp_id = getRespId($contact_id, 'contact');
        addActivity($contact_id, $resp_id, $title, $activity_text, 'C',$comment_text); // добавляем дело 
    }
 
   }
   else{ // Если нет лида
    $resp_id = getRespId($contact_id, 'contact');
    addActivity($contact_id, $resp_id, $title, $activity_text, 'C',$comment_text); //дело в контакт
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
    $comments = updateLead($user_params, $lead_id, $must_update,$comment_text); //обновляем лид 
    addActivity($lead_id, $resp_id, $title, $activity_text, 'L', $comments.'<br><br>'.$comment_text); // добавляем дело 
    }
    if($fleads != false && count($fleads) > 1){ // Если больше 1 лида
        foreach ($fleads as $key => $value) {
           moveToDouble($key); // Все в дубль
        }
       $new_lead_id = createNewLead($user_params); // Создаем новый лид
       $resp_id = getRespId($new_lead_id, 'lead');
       $comments = 'Было больше 1 лида, все перенесены в "Дубль", создан лид на основе заявки';
       addActivity($new_lead_id, $resp_id, $title, $activity_text, 'L',$comments.'<br><br>'.$comment_text); // добавляем дело 
    }
    }
    else{ // Если нет лида
        createNewLead($user_params); // Создаем новый лид
    }
   



}





?>