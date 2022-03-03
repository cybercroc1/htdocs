<?php

function getSource($url)
{ // Подставление источника

  /*  $utm_source = isset($_REQUEST['UTM_source']) && !empty($_REQUEST['UTM_source']) ? $_REQUEST['UTM_source'] : null;

    if ($utm_source == 'DMT_special') {
        return 10; // ДМТ
    }
    if ($utm_source == 'yandex_profi') {
        return 3; // кокос
    }
    if ($utm_source == 'DMT') {
        return 4; // ДМТ
    }*/


    switch (true) {
        case strpos($url, 'wilstream-msk.ru') !== false:
            $source = 3; // кокос
            break;
        case strpos($url, 'contact-center.ru') !== false:
        case strpos($url, 'wilstream.ru') !== false:
        case strpos($url, 'вилстрим.ру.фб') !== false:
            $source = 4; //дмт
            break;
        default:
            $source = 'WEB';
    }
    return $source;
}


function findLeads($phone, $email, $company_name)
{
    $leads = [];
    $check_phone = preg_replace("/[^0-9]/", "", $phone); // Последние 10 цифр номера телефона
    $email_domain = getLeadEmailDomain($email);

    $fin_phone = $check_phone;

    $first_char = $check_phone[0];

    if ($first_char == 7) {
        $change_char = 8;
        $phone_number_with_8 = $change_char . substr($check_phone, 1);
        $phone_number_with_7 = $check_phone;
    }
    if ($first_char == 8) {
        $change_char = 7;
        $phone_number_with_7 = $change_char . substr($check_phone, 1);
        $phone_number_with_8 = $check_phone;
    }

    $pure_phone_number_with_7 = preg_replace("/[^0-9]/", "", $phone_number_with_7);
    $pure_phone_number_with_8 = preg_replace("/[^0-9]/", "", $phone_number_with_8);

    if (strlen($check_phone) > 10) {
        $fin_phone = substr($check_phone, -10);
    } else {
        $fin_phone = $check_phone;
    }


    $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
        'filter' => array(
            'PHONE' => $check_phone
        ),
        'select' => array(
            'PHONE', 'EMAIL', 'STATUS_SEMANTIC_ID', 'COMPANY_TITLE'
        ),
        'ORDER' => array('DATE_CREATE' => 'DESC'),


    ));
    $json = b24request('crm.lead.list', $data);

    if (count($json->result) == 0) {
        $possible_phones = array('_' . $fin_phone, '__' . $fin_phone, $check_phone, $phone_number_with_7, $phone_number_with_8);

        $data = http_build_query(array(
            'filter' => array(
                'LOGIC' => 'OR',
                'PHONE' => $possible_phones
            ),
            'select' => array(
                'PHONE', 'EMAIL', 'STATUS_SEMANTIC_ID', 'COMPANY_TITLE'
            ),
        ));

        $json = b24request('crm.lead.list', $data);
    }

    if (isset($json->result)) {
        $res_c = $json->result;

        if (count($res_c)) {
            for ($i = 0; $i < count($res_c); $i++) {
                $semantic_id = $res_c[$i]->STATUS_SEMANTIC_ID;
                if (isset($res_c[$i]->PHONE)) $lead_phone = $res_c[$i]->PHONE;
                if (isset($res_c[$i]->EMAIL)) $lead_email = $res_c[$i]->EMAIL;
                if (isset($res_c[$i]->COMPANY_TITLE))  $lead_company_title = $res_c[$i]->COMPANY_TITLE;

                $com_email = false;
                $com_title = false;

                if ($semantic_id == 'P') {
                    $lead_id = $res_c[$i]->ID;
                    if (isset($lead_phone) && !empty($lead_phone)) {
                        $phones_q = count($lead_phone);
                        for ($p = 0; $p < $phones_q; $p++) {
                            $phone = $lead_phone[$p]->VALUE;
                            $pure_phone = preg_replace("/[^0-9]/", "", $phone);
                            if (strrpos($check_phone, $pure_phone) !== false || strrpos($pure_phone_number_with_8, $pure_phone) !== false ||  strrpos($pure_phone_number_with_7, $pure_phone) !== false) {
                                $leads[$lead_id]['phone'] = true;
                            }
                        }
                    }
                    if (isset($lead_email) && !empty($lead_email)) {
                        $emails_q = count($lead_email);
                        for ($p = 0; $p < $emails_q; $p++) {
                            $l_email = $lead_email[$p]->VALUE;
                            if (strrpos($l_email, $email) !== false) {
                                $leads[$lead_id]['email'] = true;
                            }
                            $l_email_domain = getLeadEmailDomain($l_email);
                            if (strtolower($email_domain) == strtolower($l_email_domain)) {
                                $com_email = true;
                            }
                        }
                    }
                    if (isset($lead_company_title) && !empty($lead_company_title) && isset($company_name) && !empty($company_name)) {
                        if ((strtolower($company_name) == strtolower($lead_company_title))) {
                            $com_title = true;
                        }
                    }
                }
                if ($com_email == true && $com_title == true) {
                    $leads[$lead_id]['company'] = true;
                }
            }
        }
    }

    if (count($leads)) {
        return $leads;
    }

    $email_lead_id = checkLeadEmail($email);

    if ($email_lead_id == false) {
        return false;
    } else {
        $leads = [];
        $leads[$email_lead_id]['email'] = true;
        return $leads;
    }
}


function genTaskComment($user_params)
{
    $comments = '';
    if (!isset($user_params['company']) && empty($user_params['company'])) $user_params['company'] = '';
    if (!isset($user_params['email']) && empty($user_params['email'])) $user_params['email'] = '';
    if (isset($user_params['question'])) $comments .= 'Вопрос: ' . $user_params['question'];
    if (isset($user_params['comment'])) $comments .= '<br>Комментарий: ' . $user_params['comment'];
    if (isset($user_params['usluga'])) $comments .= '<br>Услуга: ' . $user_params['usluga'];
    if (isset($user_params['url'])) $comments .= '<br>URL: ' . $user_params['url'];

    if (isset($user_params['addToComment']) && !empty($user_params['addToComment'])) {

        $comments = $user_params['addToComment'] . '<br>' . $comments;
    }

    $text = "Данные с формы: <br>
    Имя: " . $user_params['name'] . "<br>
    Email: " . $user_params['email'] . "<br>
    Номер: " . $user_params['phone'] . "<br>
    Название компании: " . $user_params['company'] . "<br>
    Комментарии: " . $comments;


    return $text;
}

function createNewLead($user_params)
{ // Создаем новый лид
    $comments = '';
    if (!isset($user_params['company']) && empty($user_params['company'])) $user_params['company'] = '';
    if (!isset($user_params['source']) && empty($user_params['source'])) $user_params['source'] = '';
    if (!isset($user_params['email']) && empty($user_params['email'])) $user_params['email'] = '';
    if (isset($user_params['question'])) $comments .= 'Вопрос: ' . $user_params['question'];
    if (isset($user_params['comment'])) $comments .= '<br>Комментарий: ' . $user_params['comment'];
    if (isset($user_params['usluga'])) $comments .= '<br>Услуга: ' . $user_params['usluga'];
    if (isset($user_params['url'])) $comments .= '<br>URL: ' . $user_params['url'];

    if (isset($user_params['addToComment']) && !empty($user_params['addToComment'])) {

        $comments = $user_params['addToComment'] . '<br>' . $comments;
    }
    include(__DIR__ . '/config.php');
    $user_params['phone'] = preg_replace("/[^0-9]/", "", $user_params['phone']);
    $lead_data = http_build_query(array( // Формируем запрос для создания нового лида
        'FIELDS' => array(
            'TITLE' => 'Заявка с формы',
            'NAME' => $user_params['name'],
            'EMAIL' => array(
                'n1' => array(
                    'VALUE' => $user_params['email'],
                    'VALUE_TYPE' => 'WORK'
                )
            ),
            'PHONE' => array(
                'n1' => array(
                    'VALUE' => $user_params['phone'],
                    'VALUE_TYPE' => 'WORK'
                )
            ),
            'COMMENTS' => $comments,
            'COMPANY_TITLE' => $user_params['company'],
            'ASSIGNED_BY_ID' => 98,
            'SOURCE_ID' => $user_params['source'],
			'UF_CRM_1625554320423' => $user_params['stats_id'], //внешний идентификатор заявки
			'UF_CRM_1625555246801' => $user_params['stats_descruptor'] //что интересовало
        )
    ));

    $json = b24request('crm.lead.add', $lead_data);
    $lead_id = $json->result;
    return $lead_id;
}


function moveToDouble($lead_id)
{
    $data = http_build_query(array(
        'ID' => $lead_id,
        'FIELDS' => array(
            'STATUS_ID' => 7
        )
    ));


    $json = b24request('crm.lead.update', $data);
}

function updateContact($user_params, $contact_id)
{

    $contact = b24request('crm.contact.get', "ID=$contact_id");

    if ($contact->result->ID == $contact_id) {

        $phones_q = count($contact->result->PHONE);
        $email_q = count($contact->result->EMAIL);

        $new_phone_q = $phones_q + 1;
        $new_email_q = $email_q + 1;



        $data = http_build_query(array(
            'ID' => $contact_id,
            'FIELDS' => array(
                'EMAIL' => array(
                    "n$new_email_q" => array(
                        'VALUE' => $user_params['email'],
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                'PHONE' => array(
                    "n$new_phone_q" => array(
                        'VALUE' => $user_params['phone'],
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                // 'COMPANY_TITLE'=> $user_params['company']
            )
        ));


        $json = b24request('crm.contact.update', $data);
    }
}

function updateLead($user_params, $lead_id, $must_update, $comment_text)
{

    $lead = b24request('crm.lead.get', "ID=$lead_id");

    $what_update_text = 'Что было сделано по объединению:<br>Добавлены комментарии к лиду';


    if ($lead->result->ID == $lead_id) {
        $comments = $lead->result->COMMENTS;
        $comments = $comments . '<br>' . $comment_text;
        $phones_q = '';
        $new_phone_q = '';
        $email_q = '';
        $new_email_q = '';
        if (isset($user_params['phone']) && strlen($user_params['phone']) > 1) {
            if (isset($lead->result->PHONE)) {
                $phones_q = count($lead->result->PHONE);
                $new_phone_q = $phones_q + 1;
            }

            if (isset($must_update['phone']) && $must_update['phone'] == true) {
                $phone = '';
                $what_update_text .= '<br>Номер не изменен';
            } else {
                $phone = $user_params['phone'];
                $what_update_text .= '<br>Добавлен номер телефона: ' . $phone;
            }
        } else {
            $phone = '';
            $what_update_text .= '<br>Номер не изменен';
        }

        if (isset($user_params['email']) && strlen($user_params['email']) > 1) {
            if (isset($lead->result->EMAIL)) {
                $email_q = count($lead->result->EMAIL);
                $new_email_q = $email_q + 1;
            }

            if (isset($must_update['email']) && $must_update['email'] == true) {
                $email = '';
                $what_update_text .= '<br>Почта не изменена';
            } else {
                $email = $user_params['email'];
                $what_update_text .= '<br>Добавлена почта: ' . $email;
            }
        } else {
            $email = '';
            $what_update_text .= '<br>Почта не изменена';
        }


        $data = http_build_query(array(
            'ID' => $lead_id,
            'FIELDS' => array(
                'EMAIL' => array(
                    "n$new_email_q" => array(
                        'VALUE' => $email,
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                'PHONE' => array(
                    "n$new_phone_q" => array(
                        'VALUE' => $phone,
                        'VALUE_TYPE' => 'WORK'
                    )
                ),
                'COMMENTS' => $comments
            )
        ));


        $json = b24request('crm.lead.update', $data);
        return $what_update_text;
    }

    return '';
}

function getRespId($id, $el)
{ // Получаем ID ответственного 
    $json = b24request('crm.' . $el . '.get', "ID=$id"); // Получаем элемент 
    if (isset($json->result)) {
        return $json->result->ASSIGNED_BY_ID;
    } else {
        return '';
    }
}


function addCommentToTask($id, $comment)
{
    $data = http_build_query(array(

        $id, array(
            'POST_MESSAGE' => $comment
        )


    ));

    $json = b24request('task.commentitem.add', $data);
}

function addActivity($id, $resp_id, $title, $text, $type, $comments)
{

    $data = http_build_query(array(
        array(
            'TITLE' => $title,
            'RESPONSIBLE_ID' => $resp_id,
            'DESCRIPTION' => $text,
            'DEADLINE' => date("Y-m-d H:i:s", strtotime('+1 hour')),
            'CREATED_BY' => 98
        )

    ));

    $json = b24request('task.item.add', $data);

    $task_id = $json->result;

    if ($task_id) {
        $data = http_build_query(array(
            $task_id,
            array(
                'UF_CRM_TASK' => [$type . '_' . $id]
            )

        ));
        $upd = b24request('task.item.update', $data);
        addCommentToTask($task_id, $comments);
    }
}

function contactHasLead($contact_id)
{
    $search = true;
    $start = 0;
    while ($search) {
        $data = http_build_query(array( // Фильтруем и ищем лиды с номером 
            'select' => array(
                'CONTACT_ID'
            ),
            'start' => $start

        ));
        $json = b24request('crm.lead.list', $data); // Получаем все лиды
        if (isset($json->result)) {
            $total = $json->total; // кол-во лидов
            if ($total) {
                $leads = $json->result;

                for ($i = 0; $i < $total; $i++) {
                    if (isset($leads[$i]) && $leads[$i]->CONTACT_ID == $contact_id) { // Если контакт найден, возвращаем id контакта
                        return $leads[$i]->ID;
                    }
                }
            }
            if (!isset($json->next)) {
                $search = false;
            } else {
                $start = $start + 50;
            }
        } else {
            $search = false;
        }
    }

    return false; // Если контакт не найден
}

function getLeadEmailDomain($email)
{
    if (empty($email)) {
        return false;
    }
    $domain = explode('@', $email);
    $domain = explode('.', $domain[1]);
    $fin_domain = $domain[0]; // Название домена
    include(__DIR__ . '/config.php');

    if (in_array($fin_domain, $mails_list)) { // Если домен совпадает с листом
        return false;
    } else {
        return $fin_domain;
    }
}

function checkCompanyDomain($email)
{

    /* Получаем название домена */
    $domain = explode('@', $email);
    $domain = explode('.', $domain[1]);
    $fin_domain = $domain[0]; // Название домена
    include(__DIR__ . '/config.php');

    if (in_array($fin_domain, $mails_list)) { // Если домен совпадает с листом
        return false;
    }


    /* Получаем название домена */

    $company_id = getCompanyId($fin_domain); // Получаем id компании


    if ($company_id == false) { // Если компания не найдена
        return false;
    } else { // Если компания найдена
        $contacts = getCompanyContacts($company_id);
        return $contacts;
    }
}

function getCompanyContacts($id)
{
    $contacts = b24request('crm.company.contact.items.get', "ID=$id");

    if (count($contacts->result) == 1) { // Если найден 1 контакт
        // Возвращаем ID контакта
        $id = $contacts->result[0]->CONTACT_ID;
        return $id;
    } else {
        // если нет контакта или больше 1 контакта
        return false;
    }
}


function getCompanyId($company_name)
{ // Получаем ID компании по названию
    $data = http_build_query(array(
        'filter' => array(
            'TITLE' => $company_name
        )
    ));
    $json = b24request('crm.company.list', $data); // Получаем инфо о компании
    if (isset($json->total) && $json->total == 1) { // Если компания найдена
        // Возвращаем ID компании
        $id = $json->result[0]->ID;
        return $id;
    } else {
        // Компания не найдена
        return false;
    }
}


function checkEmail($email)
{

    $data = http_build_query(array( // Фильтруем и ищем контакта по почте
        'filter' => array(
            'EMAIL' => $email
        )
    ));

    $json = b24request('crm.contact.list', $data); // Получаем информацию о контакте

    if (isset($json->total) && $json->total == 1) { // Если контакт найден
        $id = $json->result[0]->ID;
        return $id; // Возвращаем ID контакта
    } else { // Если контакт не найден
        return false;
    }
}

function checkLeadEmail($email)
{

    $data = http_build_query(array( // Фильтруем и ищем контакта по почте
        'filter' => array(
            'EMAIL' => $email
        )
    ));

    $json = b24request('crm.lead.list', $data); // Получаем информацию о контакте

    if (isset($json->total) && $json->total == 1) { // Если контакт найден
        $id = $json->result[0]->ID;
        return $id; // Возвращаем ID контакта
    } else { // Если контакт не найден
        return false;
    }
}

function checkCompany($company_name)
{

    $company_id = getCompanyId($company_name); // Получаем ID компании

    if ($company_id == false) { // Если компания не найдена
        return false;
    } else { // Если компания найдена
        return $company_id;
    }
}

function checkPhoneNumber($phone)
{
    $phone_number = preg_replace("/[^0-9]/", "", $phone);
    $fin_phone = $phone_number;

    $first_char = $phone_number[0];

    if ($first_char == 7) {
        $change_char = 8;
        $phone_number_with_8 = $change_char . substr($phone_number, 1);
        $phone_number_with_7 = $phone_number;
    }
    if ($first_char == 8) {
        $change_char = 7;
        $phone_number_with_7 = $change_char . substr($phone_number, 1);
        $phone_number_with_8 = $phone_number;
    }

    $pure_phone_number_with_7 = preg_replace("/[^0-9]/", "", $phone_number_with_7);
    $pure_phone_number_with_8 = preg_replace("/[^0-9]/", "", $phone_number_with_8);

    $contacts = [];
    if (strlen($phone_number) > 10) {
        $fin_phone = substr($phone_number, -10);
    } else {
        $fin_phone = $phone_number;
    }

    $data = http_build_query(array(
        'filter' => array(
            'PHONE' => $phone_number
        ),
        'select' => array(
            'PHONE', 'ID'
        )
    ));

    $json = b24request('crm.contact.list', $data);


    if (count($json->result) == 0) {
        $possible_phones = array('_' . $fin_phone, '__' . $fin_phone, $phone_number, $phone_number_with_7, $phone_number_with_8);

        $data = http_build_query(array(
            'filter' => array(
                'LOGIC' => 'OR',
                'PHONE' => $possible_phones
            ),
            'select' => array(
                'PHONE', 'ID'
            )
        ));

        $json = b24request('crm.contact.list', $data);
    }

    if (isset($json->result)) {
        $res_c = $json->result;

        if (count($res_c)) {
            for ($i = 0; $i < count($res_c); $i++) {
                $contact_id = $res_c[$i]->ID;

                if (isset($res_c[$i]->PHONE)) {
                    $phones_q = count($res_c[$i]->PHONE);
                    for ($p = 0; $p < $phones_q; $p++) {
                        $phone = $res_c[$i]->PHONE[$p]->VALUE;
                        $pure_phone = preg_replace("/[^0-9]/", "", $phone);
                        if (strrpos($phone_number, $pure_phone) !== false || strrpos($pure_phone_number_with_8, $pure_phone) !== false ||  strrpos($pure_phone_number_with_7, $pure_phone) !== false) {
                            return $contact_id;
                        }
                    }
                }
            }
        }
    }

    return false;
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
