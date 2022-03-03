<?php
exit;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 2000);
require_once(__DIR__.'/db.php');
echo '<pre>';
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

$varos = [
    'db_name' => 'companies',
    'sush_id' => 'company_id',
    'api_request' => 'company',

];

$varos = [
    'db_name' => 'companies',
    'sush_id' => 'company_id',
    'api_request' => 'company',
    
];

/*

$run = $db->query("SELECT * FROM `companies` WHERE `isChanged` is NULL");

while($row = $run->fetch_assoc()){
    $cont_id = $row['company_id'];
    $id = $row['id'];
    $phones = json_decode($row['numbers']);
    for($i = 0; $i < count($phones); $i++){
        if(strpos($phones[$i]->phone,',') !== false){
            echo 'np'. $cont_id.'<br>';
        }
        if(!ctype_digit($phones[$i]->phone) && strpos($phones[$i]->phone,',') === false){
           //echo 'g'. $cont_id;
           $upd = updatePhones($cont_id, $phones);
       //   exit;
           if($upd != true){
            echo 'er'.$cont_id;
            exit;
           }
           
            break;
        }
    }
   
    

}

*/
function updatePhones($sush_id, $phones){
  //  var_dump($phones); exit;
    $pure_phones = [];
    for($i = 0; $i < count($phones); $i++){
        $pure_phones[$i] = array(
            'ID' => $phones[$i]['id'],
            'VALUE' => preg_replace("/[^0-9]/", "", $phones[$i]['phone'] ),
            'VALUE_TYPE' => 'WORK'
        );
    }

    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
       'ID' => $sush_id,
       'FIELDS'=> array(
          'PHONE' => $pure_phones
       )
    ));

    $json = b24request('crm.contact.update', $data);

   /* if($json->result == true){
        $SQL = "UPDATE `companies` SET `isChanged` = '1' WHERE `company_id` = '$sush_id' LIMIT 1";
        $run = $GLOBALS['db']->query($SQL);
        return $run;
    }*/
}








    $search = true;
    $start = 0;
    while($search){
    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
        'select' => array(
           'PHONE', 'ID', 'DATE_CREATE'
        ),
        'start' => $start,
        'order'=> array('DATE_CREATE' => 'DESC')
    
    ));

    $json = b24request('crm.lead.list', $data);
   
    if(isset($json->result)){
        $res_c = $json->result;

    if(count($res_c)){
    for($i = 0; $i < count($res_c); $i++){
    $sush_id = $res_c[$i]->ID;
    $phones = [];
        if(isset($res_c[$i]->PHONE)){
        $phones_q = count($res_c[$i]->PHONE);
        
        for($p = 0; $p < $phones_q; $p++){
            if(!ctype_digit($res_c[$i]->PHONE[$p]->VALUE)){
                echo $sush_id.' | ';
            echo $res_c[$i]->PHONE[$p]->VALUE; echo '<br>';
            
            $phones[] = array('phone' => $res_c[$i]->PHONE[$p]->VALUE, 'id' => $res_c[$i]->PHONE[$p]->ID);
            }
      
        }
        updatePhones($sush_id, $phones);

        //exit;

        //$phone = json_encode($phones);
       // $res = $db->query("INSERT into `companies` (`numbers`, `company_id`) VALUES ('$phone', '$company_id')");
        
        
        
    }
    
}
    }
    if($start >= 550){
    exit;
    }
    if(!isset($json->next)){
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



    

?>