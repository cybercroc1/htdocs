<?php
ini_set("log_errors", 1);
ini_set("error_log", "C:\Apache24\htdocs\bt24/te_log_er.data");
ini_set('max_execution_time', 2000);



function b24request($method,$data){
    usleep(600000);
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

/*$search = true;
$start = 0;
$isPure = 0;
$isNotPure = 0;
    while($search){
        
    
    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
        'select' => array(
           'PHONE'
        ),
        'start' => $start
    
    ));

$json = b24request('crm.contact.list', $data);








if(isset($json->result)){
    $res_c = $json->result;

if(count($res_c)){
for($i = 0; $i < count($res_c); $i++){


    if(isset($res_c[$i]->PHONE)){
    $phone = $res_c[$i]->PHONE[0]->VALUE;
    if(strpos($phone, '-') === false && strpos($phone, ' ') === false && strpos($phone, '+') === false && strpos($phone, '(') === false&& strpos($phone, ')') === false){
    $r = findContactByNumber($phone);
    $isPure = $isPure + 1;
    if(count($r->result) == 0){exit('errrrrrrrrrr');}
    }
    else{
        $isNotPure = $isNotPure +1;
    }
    }
}
}
if(!isset($json->next) || $isPure >= 500){
    $search = false;
}
else{
    $start = $start + 50;
}
}else{
    if(isset($json->error) && $json->error == 'QUERY_LIMIT_EXCEEDED'){
        
        $start = $start;
        }
        else{
    $search = false;
        }
}
}

echo '<br>';echo '<br>';
echo 'pure = '.$isPure;echo '<br>';
echo 'not_pure = '.$isNotPure;

*/

$phone = '89763830655';

var_dump(findContactByNumber($phone));


function findContactByNumber($phone){
    $phone_number = preg_replace("/[^0-9]/", "", $phone );
    $fin_phone = $phone_number;

    if(strlen($phone_number) > 10){
        $fin_phone = substr($phone_number, -10);
    }
    else{
        $fin_phone = $phone_number;
    }
    echo 'cp_'.$phone.' fin_'.$fin_phone.' num_'.$phone_number; echo '<br>';
    $data = http_build_query(array(
        'filter' => array(
            'PHONE' => $phone_number
        ),
        'select' => array(
            'PHONE', 'ID'
         )
    ));

    $res_number = b24request('crm.contact.list', $data);
    

    if(count($res_number->result) == 0){
        $possible_phones = array( '_' . $fin_phone, '__' . $fin_phone, $phone_number);
        
        $data = http_build_query(array(
            'filter' => array(
                'LOGIC' => 'OR',
                'PHONE' => $possible_phones
            ),
            'select' => array(
                'PHONE'
             )
        ));
        
        $res_fin = b24request('crm.contact.list', $data);
        return $res_fin;
    }
    else{
        return $res_number;
    }


}

?>