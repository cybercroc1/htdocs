<?php

require_once(__DIR__.'/db.php');

$sql = 'SELECT * from `b24` WHERE ISNULL(`deal_id`) && ISNULL(`contact_id`) && ISNULL(`company_id`) && `manager_pin` = "1888" LIMIT 20';
$run = $GLOBALS['db']->query($sql);
while($infos = $run->fetch_assoc()){
$infos['email'] = cleanEmail($infos['email']);
//usleep(10000);    
createCompany($infos);
}



function cleanEmail($email){
    $comments = '';
    $p_emails = '';
    $val = [];
    if($email != ''){
$email = trim($email);
$email = preg_replace('/[^a-z0-9]$/', '', $email);
$email = preg_replace('/^([^a-zA-Z0-9])*/', '', $email);
$email = str_replace('"', "", $email);
$email = str_replace("'", "", $email);
    }

    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
      $val = array('isEmail'=> true, 'values'=> array($email), 'comments' => ''); 
    }
    else{
        if(strpos($email, ',') == true){
            $emails = array_map('trim', explode(',', $email));
            for($i = 0; $i< count($emails); $i++){
                if(filter_var($emails[$i], FILTER_VALIDATE_EMAIL)){
                    $p_emails[] = $emails[$i];
                }
                else{
                    $comments .= $emails[$i].'<br>';
                }
                
            }
            if(count($p_emails)){
                $val = array('isEmail'=> true, 'values'=> $p_emails, 'comments' => $comments); 
            }
            else{
                $val = array('isEmail'=> false, 'values'=> '', 'comments' => $comments);  
            }
        }
        else{
            $val = array('isEmail'=> false, 'values'=> '', 'comments' => $email);  
        }
    }
    

    return $val;
}


function getManagerIdByPin($manager_pin){
   
    switch($manager_pin){
case 1112:
return 32;
break;
case 1017:
return 44;
break;
case 1888:
return 60;
break;
case 1974:
return 48;
break;
case 2828:
return 42;
break;
case 3697:
return 52;
break;
case 8211:
return 58;
break;
case 9547:
return 40;
break;
case 9595:
return 56;
break;
case 8787:
return 34;
break;
case 9501:
return 38;
break;
case 1885:
return 54;
break;
case 7777:
return 50;
break;
case 8888:
return 46;
break;
case 1308:
return 36;
break;

default:
return 1;
    }
}

function getSourceByText($source){
    $source = strtolower($source);
    switch($source){
        case 'рекомендации':
        return 'RECOMMENDATION';
        break;
        case 'повторное обращение':
        return 'PARTNER';
        break;
        case 'входящая заявка':
        return 'WEB';
        break;
        case 'СМИ':
        return '1';
        break;
        case 'встреча':
        return '2';
        break;
        case 'х/о':
        return 'CALL';
        break;

        default:
        return 'not_found';
    }
}


function getDealStatusBySostoyanie($sostoyanie){
    $percent = explode("%", $sostoyanie)[0];
    
    switch($percent){
        case 10:
        return 'C4:NEW';
        break;
        case 25:
        return 'C4:PREPARATION';
        break;
        case 50:
        return 'C4:EXECUTING';
        break;
        case 75: 
        return 'C4:FINAL_INVOICE';
        break;
        case 90:
        return 'C4:2';
        break;
        case 100:
        return 'C4:3';
        break;
        case 0:
        return 'C4:14';
        break;
        default: 
        return 'C4:NEW';
    }

}

function createNewDeal($infos,$company_id,$contact_id){ // Создаем новый лид

    $manager_id = getManagerIdByPin($infos['manager_pin']);

    $source = getSourceByText($infos['source']);
    
    $deal_status = getDealStatusBySostoyanie($infos['sostoyanie']);
    
    $comments = ''; 

    if(isset($infos['sfera']) && !empty($infos['sfera'])) $comments .= 'Сфера деятельности: '.$infos['sfera'];
    if(isset($infos['usluga1']) && !empty($infos['usluga1'])) $comments .= '<br>Заинтересовавшая услуга 1: '.$infos['usluga1'];
    if(isset($infos['usluga2']) && !empty($infos['usluga2'])) $comments .= '<br>Заинтересовавшая услуга 2: '.$infos['usluga2'];
    if(isset($infos['sostoyanie']) && !empty($infos['sostoyanie'])) $comments .= '<br>Состояние клиента: '.$infos['sostoyanie'];
    if(isset($infos['budget']) && !empty($infos['budget'])) $comments .= '<br>Примерная сумма оплаты: '.$infos['budget'];
    if(isset($infos['otkaz']) && !empty($infos['otkaz'])) $comments .= '<br>Причина отказа: '.$infos['otkaz'];
    if(isset($infos['comment']) && !empty($infos['comment'])) $comments .= '<br>Комментарии: '.$infos['comment'];
    if($source == 'not_found'){ $comments .= '<br>Источник: '.$infos['source'];$source = '';}

    $deal_data = http_build_query(array( // Формируем запрос для создания нового лида
        'FIELDS' => array(
            'TITLE' => 'ИМПОРТ_'.$infos['code'],
            'STAGE_ID'=>$deal_status,
            'CATEGORY_ID'=>4,
            'COMPANY_ID'=> $company_id,
            'CONTACT_ID'=>$contact_id,
            'COMMENTS' => $comments,
            'ASSIGNED_BY_ID'=> $manager_id,
            'SOURCE_ID'=> $source,
            'OPPORTUNITY'=>$infos['budget']
            
        )
    ));

    $json = b24request('crm.deal.add', $deal_data);
    $deal_id = $json->result;

    if(is_int($deal_id) && $deal_id != 0){
        $id = $infos['id']; 
        //echo 'deal_id'.$deal_id.'<br>';   
        $GLOBALS['db']->query("UPDATE `b24` SET `deal_id` = '$deal_id' WHERE `id` = '$id' LIMIT 1"); 
      // addContactToDeal($deal_id,$contact_id);
    }

}

function addContactToDeal($deal_id, $contact_id){
    $data = http_build_query(array( // Формируем запрос для создания нового лида
        'id' => $deal_id,
        'FIELDS' => array(
            'CONTACT_ID' => $contact_id            
        )
    ));
    b24request('crm.deal.contact.add', $data);
}

function createCompany($infos){

    if(isset($infos['company_name']) && !empty($infos['company_name'])) {
        $company_name = $infos['company_name'];
    }

    if(!isset($company_name)) $company_name = $infos['fio'];
    if(!isset($company_name)) $company_name = 'Компания';
    $manager_id = getManagerIdByPin($infos['manager_pin']);
    $data = http_build_query(array( 
        'fields' => array(
            'TITLE' => $company_name,
            'ASSIGNED_BY_ID'=> $manager_id,
        )
    ));
   
$company = b24request('crm.company.add', $data);

if(isset($company->result)){
$company_id = $company->result;   
updateManager('crm.company.update',$infos['manager_pin'], $company_id);
$id = $infos['id'];  
//echo 'company_id'.$company_id.'<br>';  
$GLOBALS['db']->query("UPDATE `b24` SET `company_id` = '$company_id' WHERE `id` = '$id' LIMIT 1");  
if(isset($infos['address']) && !empty($infos['address'])) setCompanyAddress($infos['address'], $company_name,$company_id);

$contact_id = createContact($infos);

createNewDeal($infos,$company_id,$contact_id);  

if($contact_id != false){
addContactToCompany($contact_id, $company_id);
}

}
}

function addContactToCompany($contact_id, $company_id){
    $data = http_build_query(array(
        'id' => $company_id,
        'fields' => array(
            'CONTACT_ID' => $contact_id
        )
    ));

    b24request('crm.company.contact.add', $data);
}

function setCompanyAddress($address, $company_name,$company_id){

    $data = http_build_query(array( 
        'fields' => array(
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID' => $company_id,
            'PRESET_ID'=>1,
            'NAME'=>$company_name
        )
    ));

    $requisite = b24request('crm.requisite.add', $data);
    if(isset($requisite->result)){
        $requisite_id = $requisite->result;
        $data = http_build_query(array( 
            'fields' => array(
                'ENTITY_TYPE_ID' => 8,
                'TYPE_ID' => 2,
                'ENTITY_ID'=>$requisite_id,
                'ADDRESS_1'=>$address
            )
        ));
    
        $add_address = b24request('crm.address.add', $data);
       
    }

}

function updateManager($method,$pin, $id){
    $manager_id = getManagerIdByPin($pin);
    $data = http_build_query(array( 
        'id' => $id,
        'fields' => array(
            'ASSIGNED_BY_ID' => $manager_id
        )
    ));

    $upd =  b24request($method, $data);
}



function b24request($method,$data){

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

function createContact($infos){
    $manager_id = getManagerIdByPin($infos['manager_pin']);
    $emails = [];
    $comments = '';
    if($infos['email']['isEmail']){
        for($i = 0; $i < count($infos['email']['values']); $i++){
            $emails['n'.($i+1)] = array(
             'VALUE' => $infos['email']['values'][$i],
             'VALUE_TYPE' => 'WORK' 
            );
            }
          
    }
    else{
        $comments = $infos['email']['comments'];
    }
    $data = http_build_query(array( // Фильтруем и ищем контакта с номером 
        'fields' => array(
            'NAME' => $infos['fio'],
            'POST'=>$infos['doljnost'],
            'EMAIL' => $emails,
            'PHONE' => array(
                'n1' =>array(
                   'VALUE' => $infos['phone'],
                   'VALUE_TYPE' => 'WORK' 
                )
            ),
            'COMMENTS'=> $comments
        ),
       // 'ASSIGNED_BY_ID'=> $manager_id,
        
    ));
    $contact = b24request('crm.contact.add', $data);
    if(isset($contact->result)){
        $contact_id = $contact->result;
        updateManager('crm.contact.update',$infos['manager_pin'], $contact_id);
        $id = $infos['id']; 
        //echo 'contact_id'.$contact_id.'<br>';     
        $GLOBALS['db']->query("UPDATE `b24` SET `contact_id` = '$contact_id' WHERE `id` = '$id' LIMIT 1"); 
        return $contact_id;
    }
    else{
        return false;
    }
}
?>