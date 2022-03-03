<?php
//закомментировано из-за непонятного глюка в апаче
/*if(isset($_POST['name'])) $name = $_POST['name'];
if(isset($_POST['phone'])) $phone = $_POST['phone'];
if(isset($_POST['email'])) $email = $_POST['email'];
if(isset($_POST['comment'])) $comment = $_POST['comment'];
*/
//все закомментированное можно смело заменить на это:
ini_set('max_execution_time', 700);
header('Content-Type: text/html; charset=utf-8');
extract($_POST);


if(!isset($name)){
    exit('Введите пожалуйста имя');
}

if(!isset($phone)){
    exit('Введите пожалуйста номер телефона');
}

if(!isset($comment)){
    exit('Введите пожалуйста комментарий');
}


if(!isset($source)){
    $source = '';
}

include_once("phone_conv_single.php"); 

/*
$aon=phone_norm_single($aon,'ru_dial','4');
$phone=phone_norm_single($phone,'ru_dial','4');
$second_phone=phone_norm_single($second_phone,'ru_dial','4');

if($phone==$aon) $phone='';
if($second_phone==$phone or $second_phone==$aon) $second_phone=''; 

    $phones = array(
        'n1' =>array(
           'VALUE' => $second_phone,
           'VALUE_TYPE' => 'WORK' 
        ), 'n2' =>array(
           'VALUE' => $phone,
           'VALUE_TYPE' => 'WORK' 
        )
        );
*/

$phone = preg_replace("/[^0-9]/", "",$phone); 
$second_phone = preg_replace("/[^0-9]/", "",$second_phone); 


$sec_phone = '';
if(isset($second_phone) && !empty($second_phone)){
    if($second_phone != $phone){
		$sec_phone = $second_phone;
    }
}


$comments = '
Форма отправлена оператором<br>
Комментарий: '.$comment.'
';


if(!empty($sec_phone)){
    $phones = array(
        'n1' =>array(
           'VALUE' => $sec_phone,
           'VALUE_TYPE' => 'WORK' 
        ), 'n2' =>array(
           'VALUE' => $phone,
           'VALUE_TYPE' => 'WORK' 
        )
        );
}
else{
$phones = array(
    'n1' =>array(
       'VALUE' => $phone,
       'VALUE_TYPE' => 'WORK' 
    )
    );
}

$lead_data = http_build_query(array( // Формируем запрос для создания нового лида
    
    'FIELDS' => array(
        'TITLE' => $name,
        'NAME'=> $name,
        'EMAIL' => array(
            'n1' =>array(
               'VALUE' => $email,
               'VALUE_TYPE' => 'WORK' 
            )
        ),
        'PHONE' => $phones,
        'COMMENTS' => $comments,
        //'ASSIGNED_BY_ID'=> $general_responsible_id,
        'SOURCE_ID'=> $source
        
    )
));

/*if(isset($lead_id) and $lead_id!='') {
	$lead_data='ID='.$lead_id.'&'.$lead_data;
	$json = b24request('crm.lead.update', $lead_data);	
	echo "Лид обновлен. ID лида $lead_id";
}
else {*/
	$json = b24request('crm.lead.add', $lead_data);
	$lead_id = $json->result;
	
	if(is_int($lead_id) && $lead_id){
		echo "Лид создан. ID лида $lead_id";
	}
//}

function b24request($method, $data)
{

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