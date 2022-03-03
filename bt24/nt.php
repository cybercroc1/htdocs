<?php

/*for($i = 1; $i < 11; $i++){
    $num = randomNumber($i*1);
    echo $num.'<br>';
}*/




   
    $lead_data = http_build_query(array( // Формируем запрос для создания нового лида
        'FIELDS' => array(
            'TITLE' => 'Тестовый лид(не удалять)',
            'NAME'=> 'Тест',
          
            'PHONE' => array(
                'n1' =>array(
                   'VALUE' => '79763840655',
                   'VALUE_TYPE' => 'WORK' 
                )
            ),
            
            
        )
    ));

  //  $json = b24request('crm.lead.add', $lead_data);
   // var_dump($json);

addActivity('4725','1','test', 'test', 'L');

addCommentToTask('1079', 'teest');

function addCommentToTask($id, $comment){
    $data = http_build_query(array(
        
       $id,array(
                'POST_MESSAGE'=>$comment
            )
                  
    
    )); 

    $json = b24request('task.commentitem.add',$data);
    var_dump($json);
}

    function addActivity($id, $resp_id, $title, $text, $type){

        $data = http_build_query(array(
            array(
                'TITLE'=>$title,
                'RESPONSIBLE_ID'=>$resp_id,
                'DESCRIPTION' => $text,
                'DEADLINE' => date("Y-m-d H:i:s", strtotime('+1 hour')),
                'CREATED_BY' => 98
                )      
        
        )); 
    
        $json = b24request('task.item.add',$data);
        var_dump($json);
        $task_id = $json->result;
    
        if($task_id){
            $data = http_build_query(array(
              $task_id,
              array(
                  'UF_CRM_TASK'=>[$type.'_'.$id]
              )    
            
            ));
            $upd = b24request('task.item.update', $data);
        }
     
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

/*function randomNumber($length) {
    $result = '';

    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}
*/
?>