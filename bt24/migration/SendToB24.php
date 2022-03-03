<?php //exit;
//SELECT * FROM `b24` WHERE (company_id IS NOT NULL && deal_id IS NULL && contact_id IS NOT NULL)
ini_set('max_execution_time', 300);
header('Content-Type: text/html; charset=utf-8');
require_once(__DIR__ . '/db.php');
//echo '<pre>';
$sql = 'SELECT * from `b24` WHERE ISNULL(`deal_id`) && ISNULL(`contact_id`) && ISNULL(`company_id`) LIMIT 25';
$run = $GLOBALS['db']->query($sql);
while ($infos = $run->fetch_assoc()) {
    $infos['email1'] = cleanEmail($infos['email1']);
    $infos['email2'] = cleanEmail($infos['email2']);
    $infos['email3'] = cleanEmail($infos['email3']);
    $infos['email2_1'] = cleanEmail($infos['email2_1']);
    if($infos['fio1'] == '') {$infos['fio1'] = '-';}
    createCompany($infos);


}

$run = ($GLOBALS['db']->query("SELECT `id` FROM `b24` WHERE (`deal_id` IS NOT null && `contact_id` IS NOT null && `company_id` IS NOT null)"));



$c = [];
while ($r = $run->fetch_row()) {
    $c[] = $r;
}

if(is_int(count($c))){
$prog = count($c).' / 12639';
echo $prog;
echo '<script>document.title = "'.$prog.'";setTimeout(function(){location.reload();}, 3000);</script>';
}

function cleanEmail($email)
{
    $comments = '';
    $p_emails = '';
    $val = [];
    if ($email != '') {
        $email = trim($email);
        $email = preg_replace('/^([^a-zA-Z0-9])*/', '', $email);
        $email = str_replace('"', "", $email);
        $email = str_replace("'", "", $email);
        $email = preg_replace('/\s+/', '', $email);
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    } else {
        return '';
    }

}


function getManagerIdByPin($manager_pin)
{

    switch ($manager_pin) {
        case 1616: //Анна Зимина
            return 42;
            break;
        case 1974: //Анна Моисеева 
            return 48;
            break;
        case 9547: // Елена Тимофеева
            return 40;
            break;
        case 1885: //Наталья Цхадая
            return 54;
            break;
        case 1017: // Екатерина Зимина 
            return 44;
            break;
        case 1112:
            return 32;
            break;

        case 1888:
            return 60;
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

        case 9595:
            return 56;
            break;
        case 8787:
            return 34;
            break;
        case 9501:
            return 38;
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

function getSourceByText($source)
{

    $source = trim($source);

    switch ($source) {
        case 'рекомендация':
            return 'RECOMMENDATION';
            break;
        case 'повторное обращение':
            return 'PARTNER';
            break;
        case 'входящая заявка':
        case 'интернет':
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


function getDealStatusBySostoyanie($sostoyanie)
{


    switch ($sostoyanie) {
        case '10% - первый контакт - удачный':
            return 'C4:NEW';
            break;
        case '25% - состоялся повторный разговор, подтвердили интерес':
            return 'C4:PREPAYMENT_INVOICE';
            break;
        case '100% - оплата осуществлена':
            return 'C4:WON';
            break;
        case '0% - клиент сразу отказался':
            return 'C4:LOSE';
            break;
        case '100% - оплата осуществлена (1)':
            return 'C4:5';
            break;
        default:
            return 'C4:NEW';
    }

}


function getUsluga($usluga)
{
    $usluga = trim($usluga);
    switch ($usluga) {
        case 'горячая линия':
        case 'смарт-лайн':
        case 'идеальный секретарь':
        case 'виртуальный офис':
            return 90;
            break;
        case 'телефонные презентации':
        case 'анкетирование':
        case 'информативный обзвон (оператор)':
        case 'актуализация':
        case 'информативный обзвон (автомат)':
        case 'назначение встреч':
            return 88;
            break;
        case 'покупка базы':
            return 94;
            break;
        case 'аренда рабочего места':
            return 92;
            break;

        default:
            return '';




    }
}

/*function getDealStatusBySostoyanie($sostoyanie){
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

}*/

function createNewDeal($infos, $company_id, $contact_ids)
{ // Создаем новый лид

    $manager_id = getManagerIdByPin($infos['manager_pin']);

    $source = getSourceByText($infos['source']);

    $deal_status = getDealStatusBySostoyanie($infos['sostoyanie']);

    $comments = '';

    if (isset($infos['sfera']) && !empty($infos['sfera'])) $comments .= 'Сфера деятельности: ' . $infos['sfera'];
    if (isset($infos['usluga1']) && !empty($infos['usluga1'])) $comments .= '<br>Заинтересовавшая услуга 1: ' . $infos['usluga1'];
    if (isset($infos['usluga2']) && !empty($infos['usluga2'])) $comments .= '<br>Заинтересовавшая услуга 2: ' . $infos['usluga2'];
    if (isset($infos['sostoyanie']) && !empty($infos['sostoyanie'])) $comments .= '<br>Состояние клиента: ' . $infos['sostoyanie'];
    if (isset($infos['budget']) && !empty($infos['budget'])) $comments .= '<br>Примерная сумма оплаты: ' . $infos['budget'];
    if (isset($infos['otkaz']) && !empty($infos['otkaz'])) $comments .= '<br>Причина отказа: ' . $infos['otkaz'];
    if (isset($infos['comment']) && !empty($infos['comment'])) $comments .= '<br>Комментарии: ' . $infos['comment'];
    if (isset($infos['dob1']) && !empty($infos['dob1'])) $comments .= '<br>Доб1: ' . $infos['dob1'];
    if (isset($infos['dob2']) && !empty($infos['dob2'])) $comments .= '<br>Доб2: ' . $infos['dob2'];
    if (isset($infos['dob3']) && !empty($infos['dob3'])) $comments .= '<br>Доб3: ' . $infos['dob3'];
    if (isset($infos['dob4']) && !empty($infos['dob4'])) $comments .= '<br>Доб4: ' . $infos['dob4'];
    if (isset($infos['dob5']) && !empty($infos['dob5'])) $comments .= '<br>Доб5: ' . $infos['dob5'];
    if ($source == 'not_found') {
        $comments .= '<br>Источник: ' . $infos['source'];
        $source = '';
    }


    $deal_data = http_build_query(array( // Формируем запрос для создания нового лида
        'FIELDS' => array(
            'TITLE' => 'ИМПОРТ_' . $infos['code'].'_'.$infos['manager_pin'],
            'STAGE_ID' => $deal_status,
            'CATEGORY_ID' => 4,
            'COMPANY_ID' => $company_id,
            'CONTACT_ID' => $contact_ids[0],
            'COMMENTS' => $comments,
            'ASSIGNED_BY_ID' => $manager_id,
            'SOURCE_ID' => $source,
            'OPPORTUNITY' => $infos['budget'],
            'UF_CRM_1536143591' => getUsluga($infos['usluga1'])

        )
    ));

    $json = b24request('crm.deal.add', $deal_data);
    if (!isset($json->result)) {
        echo 'cant create deal.<pre>';
        var_dump($infos);
        var_dump($json);
        exit;
    }
    $deal_id = $json->result;

    if (is_int($deal_id) && $deal_id != 0) {
        $id = $infos['id']; 
        //echo 'deal_id'.$deal_id.'<br>';   
        $GLOBALS['db']->query("UPDATE `b24` SET `deal_id` = '$deal_id' WHERE `id` = '$id' LIMIT 1");
        for ($i = 1; $i < count($contact_ids); $i++) {
            addContactToDeal($deal_id, $contact_ids[$i]);
        }

    }

}

function addContactToDeal($deal_id, $contact_id)
{
    $data = http_build_query(array( // Формируем запрос для создания нового лида
        'id' => $deal_id,
        'FIELDS' => array(
            'CONTACT_ID' => $contact_id
        )
    ));
    b24request('crm.deal.contact.add', $data);
}

function createCompany($infos)
{

    if (isset($infos['company_name']) && !empty($infos['company_name'])) {
        $company_name = $infos['company_name'];
    }

    if (!isset($company_name)) $company_name = $infos['fio1'];
    if (!isset($company_name)) $company_name = 'Компания';
    $manager_id = getManagerIdByPin($infos['manager_pin']);
    $data = http_build_query(array(
        'fields' => array(
            'TITLE' => $company_name,
            'ASSIGNED_BY_ID' => $manager_id,
        )
    ));

    $company = b24request('crm.company.add', $data);

    if (isset($company->result)) {
        $company_id = $company->result;
        updateManager('crm.company.update', $infos['manager_pin'], $company_id);
        $id = $infos['id'];  
//echo 'company_id'.$company_id.'<br>';  
        $GLOBALS['db']->query("UPDATE `b24` SET `company_id` = '$company_id' WHERE `id` = '$id' LIMIT 1");
        if (isset($infos['address']) && !empty($infos['address'])) setCompanyAddress($infos['address'], $company_name, $company_id);

        $contact_ids = createContacts($infos);

        createNewDeal($infos, $company_id, $contact_ids);

        if (count($contact_ids)) {
            for ($i = 0; $i < count($contact_ids); $i++) {
                addContactToCompany($contact_ids[$i], $company_id);
            }
        }
    }
}

function addContactToCompany($contact_id, $company_id)
{
    $data = http_build_query(array(
        'id' => $company_id,
        'fields' => array(
            'CONTACT_ID' => $contact_id
        )
    ));

    b24request('crm.company.contact.add', $data);
}

function setCompanyAddress($address, $company_name, $company_id)
{

    $data = http_build_query(array(
        'fields' => array(
            'ENTITY_TYPE_ID' => 4,
            'ENTITY_ID' => $company_id,
            'PRESET_ID' => 1,
            'NAME' => $company_name
        )
    ));

    $requisite = b24request('crm.requisite.add', $data);
    if (isset($requisite->result)) {
        $requisite_id = $requisite->result;
        $data = http_build_query(array(
            'fields' => array(
                'ENTITY_TYPE_ID' => 8,
                'TYPE_ID' => 2,
                'ENTITY_ID' => $requisite_id,
                'ADDRESS_1' => $address
            )
        ));

        $add_address = b24request('crm.address.add', $data);

    }

}

function updateManager($method, $pin, $id)
{
    $manager_id = getManagerIdByPin($pin);
    $data = http_build_query(array(
        'id' => $id,
        'fields' => array(
            'ASSIGNED_BY_ID' => $manager_id
        )
    ));

    $upd = b24request($method, $data);
}



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
    usleep(100000);
    return $json;

}

function createContacts($infos)
{
    $contacts = [];
    $manager_id = getManagerIdByPin($infos['manager_pin']);

    $comments = '';
    $data = http_build_query(array( // Фильтруем и ищем контакта с номером 
        'fields' => array(
            'NAME' => $infos['fio1'],
            'POST' => $infos['doljnost1'],
            'EMAIL' => array(
                'n1' => array(
                    'VALUE' => $infos['email1'],
                    'VALUE_TYPE' => 'WORK'
                ), 'n2' => array(
                    'VALUE' => $infos['email2'],
                    'VALUE_TYPE' => 'WORK'
                ), 'n3' => array(
                    'VALUE' => $infos['email3'],
                    'VALUE_TYPE' => 'WORK'
                ),
            ),
            'PHONE' => array(
                'n1' => array(
                    'VALUE' => $infos['phone1'],
                    'VALUE_TYPE' => 'WORK'
                ), 'n2' => array(
                    'VALUE' => $infos['phone2'],
                    'VALUE_TYPE' => 'WORK'
                ), 'n4' => array(
                    'VALUE' => $infos['phone3'],
                    'VALUE_TYPE' => 'WORK'
                )
            ),
            'COMMENTS' => $comments
        ),
       // 'ASSIGNED_BY_ID'=> $manager_id,

    ));
    $contact = b24request('crm.contact.add', $data);
    if (isset($contact->result)) {
        $contact_id = $contact->result;
        updateManager('crm.contact.update', $infos['manager_pin'], $contact_id);
        $id = $infos['id']; 
        //echo 'contact_id'.$contact_id.'<br>';     
        $GLOBALS['db']->query("UPDATE `b24` SET `contact_id` = '$contact_id' WHERE `id` = '$id' LIMIT 1");
        $contacts[] = $contact_id;

    } else {
        echo 'cant create contact.<pre>';
        var_dump($infos);
        var_dump($contact);
        exit;
    }

    if (isset($infos['fio2']) && !empty($infos['fio2'])) {

        $data = http_build_query(array( // Фильтруем и ищем контакта с номером 
            'fields' => array(
                'NAME' => $infos['fio2'],
                'POST' => $infos['doljnost2'],
                'EMAIL' => array(
                    'n1' => array(
                        'VALUE' => $infos['email2_1'],
                        'VALUE_TYPE' => 'WORK'

                    )
                ),
                'PHONE' => array(
                    'n1' => array(
                        'VALUE' => $infos['phone2_1'],
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                'COMMENTS' => $comments
            ),
           // 'ASSIGNED_BY_ID'=> $manager_id,

        ));
        $contact = b24request('crm.contact.add', $data);
        if (isset($contact->result)) {
            $contact_id = $contact->result;
            updateManager('crm.contact.update', $infos['manager_pin'], $contact_id);
            $id = $infos['id']; 
            //echo 'contact_id'.$contact_id.'<br>';     
            $GLOBALS['db']->query("UPDATE `b24` SET `contact_id` = CONCAT(`contact_id`, ',$contact_id') WHERE `id` = '$id' LIMIT 1");
            $contacts[] = $contact_id;
        } else {

        }

    }

    if (isset($infos['fio3']) && !empty($infos['fio3'])) {
        $data = http_build_query(array( // Фильтруем и ищем контакта с номером 
            'fields' => array(
                'NAME' => $infos['fio3'],
                'POST' => $infos['doljnost3'],
                'EMAIL' => array(
                    'n1' => array(
                        'VALUE' => $infos['email3_1'],
                        'VALUE_TYPE' => 'WORK'

                    )
                ),
                'PHONE' => array(
                    'n1' => array(
                        'VALUE' => $infos['phone3_1'],
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                'COMMENTS' => $comments
            ),
           // 'ASSIGNED_BY_ID'=> $manager_id,

        ));
        $contact = b24request('crm.contact.add', $data);
        if (isset($contact->result)) {
            $contact_id = $contact->result;
            updateManager('crm.contact.update', $infos['manager_pin'], $contact_id);
            $id = $infos['id']; 
            //echo 'contact_id'.$contact_id.'<br>';     
            $GLOBALS['db']->query("UPDATE `b24` SET `contact_id` = CONCAT(`contact_id`, ',$contact_id') WHERE `id` = '$id' LIMIT 1");
            $contacts[] = $contact_id;
        }
    }

    return $contacts;
}
?>