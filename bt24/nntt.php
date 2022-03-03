<?php
  function findLeads($phone,$email,$company_name){
    $leads = [];
    $check_phone = preg_replace("/[^0-9]/", "", $phone ); // Последние 10 цифр номера телефона
    $email_domain = getLeadEmailDomain($email);
  
    $fin_phone = $check_phone;
  
    $first_char = $check_phone[0];

    if($first_char == 7){
       $change_char = 8;
       $phone_number_with_8 = $change_char. substr($check_phone, 1);
       $phone_number_with_7 = $check_phone;
    }
    if($first_char == 8){
       $change_char = 7;
       $phone_number_with_7 = $change_char. substr($check_phone, 1);
       $phone_number_with_8 = $check_phone;
    }

    $pure_phone_number_with_7 = preg_replace("/[^0-9]/", "", $phone_number_with_7 );
    $pure_phone_number_with_8 = preg_replace("/[^0-9]/", "", $phone_number_with_8 );

    if(strlen($check_phone) > 10){
        $fin_phone = substr($check_phone, -10);
    }
    else{
        $fin_phone = $check_phone;
    }


    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
        'filter' => array(
            'PHONE' => $check_phone
        ),
        'select' => array(
           'PHONE','EMAIL','STATUS_SEMANTIC_ID','COMPANY_TITLE'
        ),
        'ORDER' =>array('DATE_CREATE' => 'DESC'),
      
    
    ));
    $json = b24request('crm.lead.list', $data);

    if(count($json->result) == 0){
        $possible_phones = array( '_' . $fin_phone, '__' . $fin_phone, $check_phone, $phone_number_with_7, $phone_number_with_8);
        
        $data = http_build_query(array(
            'filter' => array(
                'LOGIC' => 'OR',
                'PHONE' => $possible_phones
            ),
            'select' => array(
                'PHONE','EMAIL','STATUS_SEMANTIC_ID','COMPANY_TITLE'
             ),
        ));

        $json = b24request('crm.lead.list', $data);
       
    }

    if(isset($json->result)){
        $res_c = $json->result;
 
    if(count($res_c)){
        for($i = 0; $i < count($res_c); $i++){  
           $semantic_id = $res_c[$i]->STATUS_SEMANTIC_ID;
           if(isset($res_c[$i]->PHONE)) $lead_phone = $res_c[$i]->PHONE;
           if(isset($res_c[$i]->EMAIL)) $lead_email = $res_c[$i]->EMAIL;
           if(isset($res_c[$i]->COMPANY_TITLE))  $lead_company_title = $res_c[$i]->COMPANY_TITLE;

           $com_email = false;
           $com_title = false;

            if($semantic_id == 'P'){
                $lead_id = $res_c[$i]->ID;         
                if(isset($lead_phone) && !empty($lead_phone)){
                $phones_q = count($lead_phone);
                for($p = 0; $p < $phones_q; $p++){
                $phone = $lead_phone[$p]->VALUE;
                $pure_phone = preg_replace("/[^0-9]/", "", $phone );
                if(strrpos($check_phone, $pure_phone) !== false || strrpos($pure_phone_number_with_8, $pure_phone) !== false ||  strrpos($pure_phone_number_with_7,$pure_phone) !== false){
                    $leads[$lead_id]['phone'] = true;
                 
                }
                }
            }  
            if(isset($lead_email) && !empty($lead_email)){
                $emails_q = count($lead_email);
                for($p = 0; $p < $emails_q; $p++){
                $l_email = $lead_email[$p]->VALUE;
                if(strrpos($l_email, $email) !== false){
                    $leads[$lead_id]['email'] = true;
                }
                $l_email_domain = getLeadEmailDomain($l_email);
                if(strtolower($email_domain) == strtolower($l_email_domain)){
                    $com_email = true;  
                }
                }
            }
            if(isset($lead_company_title) && !empty($lead_company_title) && isset($company_name) && !empty($company_name)){
                if((strtolower($company_name) == strtolower($lead_company_title))){
                    $com_title = true;
                }
                
            }
            
        }
        if($com_email == true && $com_title == true){
            $leads[$lead_id]['company'] = true;
        }
        
    }
    }
  
}
    
        if(!count($leads)){
            return false;
        }
        else{
            return $leads;
        }
           
}

function getLeadEmailDomain($email){
    if(empty($email)){return false;}
    $domain = explode('@', $email);
    $domain = explode('.', $domain[1]);
    $fin_domain = $domain[0]; // Название домена
    include(__DIR__.'/config.php');

    if(in_array($fin_domain, $mails_list)){ // Если домен совпадает с листом
        return false;
    }
    else{
        return $fin_domain;
    }  
}





function b24request($method,$data){
usleep(300000);
$key = 'qcggtk1141rpdfms';
$link = "https://wilstream.bitrix24.ru/rest/1/$key/$method?$data";
$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $link); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
$output = curl_exec($ch);
$json = json_decode($output);

return $json;

}

$user_params['phone'] = '8 662 211-23-22';
$fleads = findLeads($user_params['phone'],$user_params['email'],$user_params['company']);

var_dump($fleads);
?>