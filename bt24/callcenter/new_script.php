<?php
set_time_limit(300);
//закомментировано из-за непонятного глюка в апаче
/*if (isset($_GET['platform'])) $platform = $_GET['platform'];
if (isset($_GET['project_id'])) $project_id = $_GET['project_id'];
if (isset($_GET['cdn'])) $cdn = $_GET['cdn'];
if (isset($_GET['aon'])) $aon = $_GET['aon'];
if (isset($_GET['trough_id'])) $trough_id = $_GET['trough_id'];
if (isset($_GET['userguid'])) $userguid = $_GET['userguid'];
if (isset($_GET['caller_name'])) $caller_name = $_GET['caller_name'];
if (isset($_GET['out_prefix'])) $out_prefix = $_GET['out_prefix'];
if (isset($_GET['agid'])) $agid = $_GET['agid'];
*/
//все закомментированное можно смело заменить на это:
extract($_GET);

//задать кодировку браузеру, без нее открывает как попало
header('Content-Type: text/html; charset=utf-8');
echo '<link href="billing.css" rel="stylesheet" type="text/css">';


if (!isset($aon) || empty($aon)) {
	exit('Номер телефона не указан');
}
$source = '';
if(isset($cdn) && !empty($cdn)){
      switch($cdn){
        case '2290136':
        case '7883558':
        case '4993700256':
        $source = '5'; //Звонок ДМТ
        break;
        case '7883554':
        case 'Кокос-SIP-Wilstream':
        $source = '6'; //Звонок Кокос
        break;
    }
} 



$contacts = findContactsByNumber($aon);



$leads = findLeadsByNumber($aon);


$companies = findCompaniesByNumber($aon);




if($contacts == false && $leads == false && $companies == false){
    //showForm(); // Если нет контактов, лидов, компаний - выводим форму. 
	showFormLink(); //Если нет контактов, лидов, компаний - выводим ссылку на форму
    exit;
}
else{
 include_once("phone_conv_single.php");   
 echo '<iframe name=blank_frame width="700" style="display:none"></iframe>';
}


if($companies != false){
    $companies_info = getCompaniesInfo($companies, $aon);
}

if(isset($companies_info['deals']) && count($companies_info['deals']) != 0){
    showDealsInfo($companies_info['deals']);
}

if(isset($companies_info['contacts']) && count($companies_info['contacts']) != 0){
    if($contacts == false){
    $contacts = [];        
    }
    $contacts = array_merge($companies_info['contacts'], $contacts);
}

if($contacts != false){   
    showContactsInfo($contacts,$aon);
}

if($leads != false){
    showLeadsInfo($leads, $aon);
}



function getCompaniesInfo($companies, $aon){
    for($i=0; $i < count($companies); $i++){
        $company_id = $companies[$i];
        $deals = false;//findDealsByCompanies($company_id);
        $contacts = findContactByCompanies($company_id);
    }
    $data = [];

    if($deals != false){
        $data['deals'] = $deals;
    }
    if($contacts != false){
        $data['contacts'] = $contacts;
    }
    return $data;


}


function showDealsInfo($deals){
    if($deals && count($deals)){
        
        for($d = 0; $d < count($deals); $d++){
            $deal = b24request('crm.deal.get', 'id='.$deals[$d]);
            $res = $deal->result;
            if($res){
              
            $manager_podgotovki_id = $res->UF_CRM_1536146065;
            $manager_projecta_id = $res->UF_CRM_1536146043;

            if($manager_podgotovki_id != ''){
                $manager_user = getUser($manager_podgotovki_id);
                $manager_pod_phone_inner = getUserInfo($manager_user,"UF_PHONE_INNER");
                $manager_podgotovki_inner_num = getPhone($manager_pod_phone_inner); // внешний номер
                
                if($manager_pod_phone_inner == ''){ // Если внешний номер не указан
                    $manager_podgotovki_inner_num = 'не указан';
                  
                }
                $manager_podgotovki = 'Менеджер подготовки '.getUserInfo($manager_user,"NAME").' '.getUserInfo($manager_user,"LAST_NAME").', номер телефона '.$manager_podgotovki_inner_num;
            }
            else{
                $manager_podgotovki = '';
            }

            if($manager_projecta_id != ''){
                $manager_projecta_user = getUser($manager_projecta_id);
                $manager_proj_phone_inner = getUserInfo($manager_projecta_user,"UF_PHONE_INNER"); // внешний номер
              
                $manager_projecta_inner_num = getPhone($manager_proj_phone_inner);
              

                if($manager_proj_phone_inner == ''){ // Если внешний номер не указан
                    $manager_projecta_inner_num = 'не указан';
                   
                }
                $manager_projecta = '<br>Менеджер проекта '.getUserInfo($manager_projecta_user,"NAME").' '.getUserInfo($manager_projecta_user,"LAST_NAME").', номер телефона '.$manager_projecta_inner_num;
            }
            else{
                $manager_projecta = '';
            }
            
            echo '
            <br><br>Сделка '.($d+1).', '.$res->TITLE.', Статус: '.getStageNameById($res->STAGE_ID, $res->CATEGORY_ID).'<br>
            '.$manager_podgotovki.'
            '.$manager_projecta.'
            ';
        
    }
}
}
}


function findDealsByCompanies($company_id){
    $deals = [];

    $search = true;
    $start = 0;
    while($search){
    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
        'select' => array(
           'COMPANY_ID'
        ),
        'start' => $start
    
    ));
    $json = b24request('crm.deal.list', $data);

    if(isset($json->result)){
        $res_c = $json->result;

    if(count($res_c)){
        for($i = 0; $i < count($res_c); $i++){  
            $deal_id = $res_c[$i]->ID;
            $deal_company_id = $res_c[$i]->COMPANY_ID;         
                if($company_id == $deal_company_id){
                 array_push($deals, $deal_id);   
                }
                }
            }
    
    if(!isset($json->next)){
        $search = false;
    }
    else{
        $start = $start + 50;
    }
}else{
    $search = false;
}
    }
        if(!count($deals)){
            return false;
        }
        else{
            return $deals;
        }
           
}

function findContactByCompanies($company_id){
    $contacts = [];

  
    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
       'id' => $company_id
    
    ));
    $json = b24request('crm.company.contact.items.get', $data);
    
    if(isset($json->result)){
        $res_c = $json->result;
    if(count($res_c)){
        for($i = 0; $i < count($res_c); $i++){  
            $contact_id = $res_c[$i]->CONTACT_ID;
                  
                
                 array_push($contacts, $contact_id);   
                
                }
            }
  
}
else
    
        if(!count($contacts)){
            return false;
        }
        else{
            return $contacts;
        }
           
}


function showLeadsInfo($leads, $aon){
    for($i=0; $i < count($leads); $i++){
        $lead_id = $leads[$i];
        $lead = b24request('crm.lead.get', "ID=$lead_id");
        if(isset($lead->result)){
        $responsible = getUser($lead->result->ASSIGNED_BY_ID);      
        $phone_inner = getUserInfo($responsible,"UF_PHONE_INNER");    
         
       
        $inner_num = getPhone($phone_inner); // внешний номер

       
        if(isset($lead->result->EMAIL[0]->VALUE)){    
        $lead_email = ', Email: '.$lead->result->EMAIL[0]->VALUE;
        }
        else{
        $lead_email = '';
        }
        if($phone_inner == ''){ // Если внешний номер не указан
            $inner_num = 'не указан';
            
        }
        // Выводим информацию о лиде + ответственном контакте
        echo '<br>
        Название лида: '.$lead->result->TITLE.', Имя лида - '.$lead->result->NAME.' '.$lead->result->LAST_NAME.', Телефон: '.getPhone($aon).' '.$lead_email.'<br>
        Ответственный - '.getUserInfo($responsible,"NAME").' '.getUserInfo($responsible,"LAST_NAME").', номер телефона '.$inner_num;    
        
        }
    }
}


function showContactsInfo($contacts,$aon){
    for($i=0; $i < count($contacts); $i++){
        $contact_id = $contacts[$i];
        $contact = b24request('crm.contact.get', "ID=$contact_id");
        
        if(isset($contact->result)){
        $responsible = getUser($contact->result->ASSIGNED_BY_ID);
        $phone_inner = getUserInfo($responsible,"UF_PHONE_INNER");    
        $inner_num = getPhone($phone_inner); // внешний номер

        $company_id = $contact->result->COMPANY_ID;

        if(isset($company_id)){
            $c_data = http_build_query(array( // Фильтруем и ищем лиды с номером 
                'select' => array(
                   'TITLE'
                ),
                'ID'=>$company_id
            
            ));
            $comp = b24request('crm.company.get', $c_data);

            if(isset($comp->result)){
                $company_info = 'компания: '.$comp->result->TITLE;
            }
            
        }
        else{
            $company_info = "";
        }

        if($phone_inner == ''){ // Если внешний номер не указан
            $inner_num = 'не указан';
            $inner_link = '';
        }
       
        // Выводим информацию о контакте + ответственном контакте
        echo '
        Имя контакта - '.$contact->result->NAME.' '.$contact->result->LAST_NAME.', Телефон: '.getPhone($aon).' '.$company_info.'<br>
        Ответственный - '.getUserInfo($responsible,"NAME").' '.getUserInfo($responsible,"LAST_NAME").', номер телефона '.$inner_num;    
        
        $user_deals = getUserDeals($contact_id);
        
     if($user_deals != false){
         showDealsInfo($user_deals);
     }
}   
    }
}


function findCompaniesByNumber($aon){

    $check_phone = substr(preg_replace("/[^0-9]/", "", $aon ), -10); // Последние 10 цифр номера телефона
    $fin_phone = $check_phone;
    $companies = [];

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

    $json = b24request('crm.company.list', $data);
    

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
        
        $json = b24request('crm.company.list', $data);
       
    }
   
    if(isset($json->result)){
        $res_c = $json->result;

    if(count($res_c)){
        for($i = 0; $i < count($res_c); $i++){  
            $company_id = $res_c[$i]->ID;         
                if(isset($res_c[$i]->PHONE)){
                $phones_q = count($res_c[$i]->PHONE);
                for($p = 0; $p < $phones_q; $p++){
                $phone = $res_c[$i]->PHONE[$p]->VALUE;
                $pure_phone = substr(preg_replace("/[^0-9]/", "", $phone ), -10);
                if(strrpos($check_phone, $pure_phone) !== false){
                 array_push($companies, $company_id);   
                }
                }
            }
            
        }
    }
    
}
    
        if(!count($companies)){
            return false;
        }
        else{
            return $companies;
        }
           
}

function findLeadsByNumber($aon){
    $leads = [];
    $check_phone = substr(preg_replace("/[^0-9]/", "", $aon ), -10); // Последние 10 цифр номера телефона
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

function getPhone($phone){
    
    $phone=phone_norm_single($phone,'ru_dial','4');
    return "<a href='http://127.0.0.1:4059/switchto?number=".$phone."' title=".$phone." target='blank_frame'>".phone_segment($phone)."</a>";
}

function findContactsByNumber($aon){

    $contacts = [];

    $check_phone = substr(preg_replace("/[^0-9]/", "", $aon ), -10); // Последние 10 цифр номера телефона
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

    $json = b24request('crm.contact.list', $data);
    

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
        
        $json = b24request('crm.contact.list', $data);
       
    }
   

    if(isset($json->result)){
        $res_c = $json->result;

    if(count($res_c)){
for($i = 0; $i < count($res_c); $i++){
    $contact_id = $res_c[$i]->ID;
    
        if(isset($res_c[$i]->PHONE)){
        $phones_q = count($res_c[$i]->PHONE);
        for($p = 0; $p < $phones_q; $p++){
        $phone = $res_c[$i]->PHONE[$p]->VALUE;
        $pure_phone = substr(preg_replace("/[^0-9]/", "", $phone ), -10);
        if(strrpos($check_phone, $pure_phone) !== false){
         array_push($contacts, $contact_id);   
        }
        }
    }
    
}
    }
    
  
}
    
if(!count($contacts)){
    return false;
}
else{
    return $contacts;
}

 

    

}




function getStageNameById($stage_id, $category_id){

if($category_id){
$stages = b24request('crm.status.list', "filter[ENTITY_ID]=DEAL_STAGE_$category_id");
}
else{
$stages = b24request('crm.status.list', "filter[ENTITY_ID]=DEAL_STAGE");
}
$res = $stages->result;
for($i = 0; $i < count($res); $i++){
if($res[$i]->STATUS_ID == $stage_id){
    return $res[$i]->NAME;
}
}

}

function getUserDeals($contact_id){

    $data = http_build_query(array( // Ищем контакта по номеру
        'filter' => array(
            'CONTACT_ID' => $contact_id
        ),
        'select'=> array('ID')
    ));

    $json = b24request('crm.deal.list',$data); // Получаем все лиды контакта
    $total = $json->total; // кол-во лидов 

    if($total){ 
    $pre_leads = $json->result;
    $leads = [];
    for($i = 0; $i < count($pre_leads); $i++){
        $leads[] = $pre_leads[$i]->ID;
    }    
    return $leads;
    }
    else{
        return false;
    }
    
}

function getUserInfo($user,$info){
    
  if(isset($user->$info)){
      return $user->$info;
  }
  else{
      return false;
  }
}

function getUser($user_id){
    $user = b24request('user.get.json',"id=$user_id")->result;
    
    if($user){
        return $user[0];
    }
    else{
        return false;
    }
}


function showForm(){

global $aon;

echo '

<form id="my_form" action="/ajax.php" class="form-style-9">
<div style="text-align:center">Контакт не существует, форма для создания лида</div><br>
<ul>
<li>
<input type="text" name="name" placeholder="Имя" required>
</li>
<li>
<input type="text" name="phone" placeholder="Телефон" required value="'.$aon.'">
</li>
<li>
<input type="email" name="email" placeholder="E-mail">
</li>
<li>
<textarea class="field-style" name="comment" placeholder="Комментарий" ></textarea>
</li>
<li>
<div class="form_response"></div>
</li>
<li>
<input class="sub_form" type="submit" value="Создать">
</li>
</ul>
</form>







<style type="text/css">
input[type="text"], input[type="email"]{
    width: 100%;
    height: 30px;
    padding: 5px;
}
.form-style-9{
    max-width: 450px;
    background: #FAFAFA;
    padding: 30px;
    margin: 50px auto;
    box-shadow: 1px 1px 25px rgba(0, 0, 0, 0.35);
    border-radius: 10px;
    border: 2px solid #305A72;
}
.form-style-9 ul{
    padding:0;
    margin:0;
    list-style:none;
}
.form-style-9 ul li{
    display: block;
    margin-bottom: 10px;
    min-height: 35px;
}
.form-style-9 ul li  .field-style{
    box-sizing: border-box; 
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box; 
    padding: 8px;
    outline: none;
    border: 1px solid #B0CFE0;
    -webkit-transition: all 0.30s ease-in-out;
    -moz-transition: all 0.30s ease-in-out;
    -ms-transition: all 0.30s ease-in-out;
    -o-transition: all 0.30s ease-in-out;

}.form-style-9 ul li  .field-style:focus{
    box-shadow: 0 0 5px #B0CFE0;
    border:1px solid #B0CFE0;
}
.form-style-9 ul li .field-split{
    width: 49%;
}
.form-style-9 ul li .field-full{
    width: 100%;
}
.form-style-9 ul li input.align-left{
    float:left;
}
.form-style-9 ul li input.align-right{
    float:right;
}
.form-style-9 ul li textarea{
    width: 100%;
    height: 100px;
}
.form-style-9 ul li input[type="button"], 
.form-style-9 ul li input[type="submit"] {
    -moz-box-shadow: inset 0px 1px 0px 0px #3985B1;
    -webkit-box-shadow: inset 0px 1px 0px 0px #3985B1;
    box-shadow: inset 0px 1px 0px 0px #3985B1;
    background-color: #216288;
    border: 1px solid #17445E;
    display: inline-block;
    cursor: pointer;
    color: #FFFFFF;
    padding: 8px 18px;
    text-decoration: none;
    font: 12px Arial, Helvetica, sans-serif;
    float: right;
}
.form-style-9 ul li input[type="button"]:hover, 
.form-style-9 ul li input[type="submit"]:hover {
    background: linear-gradient(to bottom, #2D77A2 5%, #337DA8 100%);
    background-color: #28739E;
}
.sub_form[disabled] {
    opacity: 0.5;
}
</style>

<script type="text/javascript" src="jquery.min.js"></script>

<script>

$(document).ready(function(){

    $("form").on("submit", function(e){
        $(".sub_form").prop("disabled", "disabled");
        e.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: form_data,
            success: function(data)
            {
           
            $(".form_response").text(data);
            },
            error: function(data){
                $(".sub_form").prop("disabled", "");
            }
            });
    });
});


</script>

';


}

function showFormLink() {
	global $aon;
    global $source;
    global $sec_phone;
	echo "
	<TABLE cellSpacing=1 cellPadding=2 bgColor=gray border=0>
	<TBODY>
	<TR>
	<TD bgColor=#F0E68C>
	
	<a href='' onclick='window.open(\"http://bt24.wilstream.ru/callcenter/form.php?source=".$source."&aon=".$aon."\",\"bitwin\",\"location=no,width=350,height=600,top=100,left=100,toolbar=no,menubar=no,status=yes,resizable=yes,scrollbars=yes\");return false;'>
	<B>Контакт не существует, форма для создания лида</B></A></FONT>
	</TD></TR></TBODY></TABLE>";	
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