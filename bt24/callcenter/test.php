<?php
extract($_GET);

header('Content-Type: text/html; charset=utf-8');

$leads = findLeadsByNumber($aon);

echo '<pre>';
var_dump($leads);

function findLeadsByNumber($aon){
    $leads = [];
    $check_phone = substr( $aon , -10); // Последние 10 цифр номера телефона
    $fin_phone = $check_phone;
  

    if(strlen($check_phone) > 10){
        $fin_phone = substr($check_phone, -10);
    }
    else{
        $fin_phone = $check_phone;
    }
    
    $data = http_build_query(array(
        'filter' => array(
            'PHONE' => $check_phone
        ),
        'select' => array(
            'PHONE', 'ID'
         )
    ));

    $json = b24request('crm.lead.list', $data);
    

    if(count($json->result) == 0){
        $possible_phones = array( '_' . $fin_phone, '__' . $fin_phone, $check_phone);
        
        $data = http_build_query(array(
            'filter' => array(
                'LOGIC' => 'OR',
                'PHONE' => $possible_phones
            ),
            'select' => array(
                'PHONE','ID'
             )
        ));
        
        $json = b24request('crm.lead.list', $data);
       
    }
    if(isset($json->result)){
        $res_c = $json->result;
  

    if(count($res_c)){
        for($i = 0; $i < count($res_c); $i++){  
            $lead_id = $res_c[$i]->ID;         
                if(isset($res_c[$i]->PHONE)){
                $phones_q = count($res_c[$i]->PHONE);
                for($p = 0; $p < $phones_q; $p++){
                $phone = $res_c[$i]->PHONE[$p]->VALUE;
                $pure_phone = substr(preg_replace("/[^0-9]/", "", $phone ), -10);
                if(strrpos($check_phone, $pure_phone) !== false){
                 array_push($leads, $lead_id);   
                }
                }
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

function b24request($method, $data)
{
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
?>