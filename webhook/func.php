<?php
function json_post(
    $url,
    $get_values,
    $post_values,
    $headers=array(),
    $session_cookie_file='',
    $ext_encoding='cp1251',
    $int_encoding='utf-8',
    $post_type='json',
    $return_type='array',
    $send_method=''
    ) {
        //функция принимает:
        //$url - например "https://wilstream.bitrix24.ru/rest/22/ybs6v1hv5mowqb2b/crm.contact.list;
        //$get_values(array) - массив переменных, которые необходимо передать методом GET
        //$post_values(array) - массив переменных, которые нужно передать методом POST
        //$session_cookie_file (необязательный) - путь к файлу кукеса, если не задан, то кукесы не используются
        //$ext_encoding (необязательный, 'cp1251') - внешняя кодировка - кодировка, в которой передаются параметры в данную функцию, функция так же вернет данные в этой кодировке
        //$int_encoding (необязательный, 'utf-8') - внутренняя кодировка - кодировка, в которой идет обмен данными с внешним сервером 
        //$post_type  (необязательный, 'array') - 'json' - ковенртирует post_values в json,
            //var, mform - отправляет ввиде переменных multipart/form-data, 
            //uform - отправляет ввиде переменных application/x-www-form-urlencoded
        //$return_type  (необязательный, 'array') - 'array' - функция вернет ответ в ввиде массива; любое другое значение - функция вернет данные как есть.
        
        //функция возвращает результат ($res) ввиде массива:
        //всегда возвращает ответ сервера:
        //$res['code'] - числовой код ответа сервера
        //$res['text'] - текстовая рвсшифровка кода ответа
        //проверку успешного выполнения функции следует проверять по текстовому ответу 'OK' if($res['text']=='OK')
        //$res['values'] - массив с переменными или строка, в зависимости от $return_type 
    
        //дефолтовые кодировки
        if($ext_encoding=='') $ext_encoding='cp1251';
        if($int_encoding=='') $int_encoding='utf-8';
    
        if($post_values!='' and $ext_encoding<>$int_encoding) {
            mb_convert_variables($int_encoding,$ext_encoding,$post_values); //конвертируем переменную в UTF8
        }
    
        if($get_values<>'') {
            if($get_values<>'' and $ext_encoding<>$int_encoding) {
                mb_convert_variables($int_encoding,$ext_encoding,$get_values); //конвертируем массив в UTF8
            }
            if (is_array($get_values)) {
                $i = 0;
                foreach ($get_values as $key => $val) {
                    $i++;
                    if ($i == 1) $url .= '?'; else $url .= '&';
                    $url .= $key . '=' . $val;
                }
            }
        }
        //echo "<hr>".$url."</hr>";
        $curl=curl_init(); #Сохраняем дескриптор сеанса cURL
        #Устанавливаем необходимые опции для сеанса cURL
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl,CURLOPT_URL,$url);
        //print_r($headers);
        //curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'PATCH');
        if($post_values!='' and $send_method=='') curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        elseif($send_method!='') curl_setopt($curl,CURLOPT_CUSTOMREQUEST,$send_method);
        
        if($post_values!='') {
            //curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
            if(in_array($post_type,array('var','mform','uform'))) { //отправка ввиде переменных
                curl_setopt($curl,CURLOPT_POST, TRUE);
                if($post_type=='uform') {
                    $post_url_encoded=""; $i=0;
                    foreach($post_values as $key => $val) {$i++;
                        if($i>1) $post_url_encoded.="&";
                        $post_url_encoded.=$key."=".$val;
                    }
                    curl_setopt($curl,CURLOPT_POSTFIELDS,$post_url_encoded);
                }
                else {
                    curl_setopt($curl,CURLOPT_POSTFIELDS,$post_values);
                }
            }
            else { //JSON
                curl_setopt($curl,CURLOPT_POSTFIELDS,json_encode($post_values));
                $headers=array_merge(array('Content-Type: application/json'),$headers);
            }	
        }
        if(count($headers)>0) {curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);}
        curl_setopt($curl,CURLOPT_HEADER,false);
        if($session_cookie_file<>'') {
            curl_setopt($curl,CURLOPT_COOKIEFILE,$session_cookie_file); 
            curl_setopt($curl,CURLOPT_COOKIEJAR,$session_cookie_file); 
        }
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,0);
    
    
        $answer=send_curl_query($curl);
    
        $res['code']=$answer['result_code'];
        $res['text']=$answer['result_text'];
        $res['values']=json_decode($answer['content'],true); 
        
        //if($res['text']<>'OK') return $res;
        
        if($return_type=='array') {
            $res['values']=json_decode($answer['content'],true); 
        }
        else $res['values']=$answer['content'];
        
        if($ext_encoding<>$int_encoding) {
            mb_convert_variables($ext_encoding,$int_encoding,$res); //конвертируем массив в UTF8
        }
        return $res;
    }
    function send_curl_query($curl) {
        $res['content']=curl_exec($curl); #Инициируем запрос к API и сохраняем ответ в переменную
        $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); #Получим HTTP-код ответа сервера
        curl_close($curl); #Завершаем сеанс cURL
        /* Теперь мы можем обработать ответ, полученный от сервера. Это пример. Вы можете обработать данные своим способом. */
        $code=(int)$code;
        
        $errors=array(
        301=>'Moved permanently',
        400=>'Bad request',
        401=>'Unauthorized',
        402=>'Payment Required',
        403=>'Forbidden',
        404=>'Not found',
        405=>'Method Not Allowed',
        500=>'Internal server error',
        502=>'Bad gateway',
        503=>'Service unavailable'
        );	
        if($code!=200 && $code!=204) {
            $res['result_code']=$code;
            if(isset($errors[$code])) $res['result_text']=$errors[$code];
            else $res['result_text']='Undescribed error';
            return $res;
        }
        $res['result_code']=$code;
        $res['result_text']='OK';
        return $res;
    }
?>