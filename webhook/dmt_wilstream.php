<?php
ini_set( 'default_charset', 'UTF-8' );



$log_file_name = __DIR__ . '/dmt_wilstream_logs/' . date('Y-m-d') . '-'. time() . '.log'; // Файл логов для заявки

//$fp = fopen($log_file_name, 'a');

//fwrite($fp, $_SERVER['REMOTE_ADDR']);
//fwrite($fp, "\nPOST:\n".print_r($_POST, TRUE));
//fwrite($fp, "\nGET:\n".print_r($_GET, TRUE));

//в папке PHP7/includes:
include('sc-crm/btx_funct_oauth.php');
include('show_array.php');
include('phone_conv_single.php');

if(isset($_POST['event']) and $_POST['event']=='ONCRMLEADUPDATE') {
	$result=get_lead_info($_POST['data']['FIELDS']['ID']);
	
	$leadinfo['source_id']=$result['values']['result']['SOURCE_ID'];
	$sources=array(
		4=>'ДМТ',
		5=>'звонок ДМТ'	
	);
	
	$leadinfo['status_id']=$result['values']['result']['STATUS_ID'];
	$statuses=array(
		'CONVERTED'=>'лид квалифицирован',
		1=>'Звонок по действующему клиенту',
		2=>'Нет таких услуг',
		4=>'Иное',
		7=>'Дубль',
		8=>'Недозвон больше 2дн',
		9=>'Ищет работу',
		'JUNK'=>'ошибка'
	);

	$leadinfo['status_dop']=$result['values']['result']['STATUS_SEMANTIC_ID']; //Сотояние статуса
	//P - в работе
	//S - квалифицирован
	//F - дубль, нет таких услуг, ошибка

	$leadinfo['other_city']=$result['values']['result']['UF_CRM_1623063345408']; //Другой город,
	//другой город не идет в зачет ДМТ
	//NULL,0 - нет 
	//1 - да

	$leadinfo['cennost']=$result['values']['result']['UF_CRM_1626081897026']; //Ценность обращения
	//NULL,0 - обычное 
	//1 - ценное
	
	if($leadinfo['cennost']==1) $leadinfo['type']='ценное';
	else $leadinfo['type']='обычное';

	$leadinfo['status_commited']=$result['values']['result']['UF_CRM_1625740001317']; //Статус подтвержден - 
	//секретарь подтвердил статус заявки, заявка готова к отправке в ДМТ
	//NULL,0 - нет
	//1 - да
	
	$leadinfo['prichina_otkaza']=$result['values']['result']['UF_CRM_1542366115']; //Причина отказа

	$leadinfo['record']=$result['values']['result']['UF_CRM_1626085424526']; //Ссылка на запись звонка - 
	//секретарь добавляет ссылку на запись в битриксе

	$leadinfo['name']=$result['values']['result']['NAME']; //Имя
	$leadinfo['phone']=$result['values']['result']['PHONE']['0']['VALUE']; //Контактная информация
	$leadinfo['phone_norm']=phone_norm_single($leadinfo['phone'],'int_dial');
	$leadinfo['comments']=$result['values']['result']['COMMENTS']; //Комментарий
	
	$leadinfo['stats_id']=$result['values']['result']['UF_CRM_1625554320423']; //внешний идентификатор заявки
	$leadinfo['stats_descruptor']=$result['values']['result']['UF_CRM_1625555246801']; //что интересовало

	
	if($leadinfo['status_id']=='CONVERTED' and $leadinfo['other_city']!=1) {
		$leadinfo['status_name']='подтверждено';
		$leadinfo['status_otkaza']='';
		$leadinfo['prichina_otkaza']='';
		
	}
	else if($leadinfo['status_id']=='CONVERTED' and $leadinfo['other_city']==1) {
		$leadinfo['status_name']='отклонено';
		$leadinfo['status_otkaza']='другой город';
		$leadinfo['prichina_otkaza']='другой город';
	}
	else {
		$leadinfo['status_name']='отклонено';
		$leadinfo['status_otkaza']=$statuses[$leadinfo['status_id']];
	}

	//fwrite($fp, "\nLEAD INFO:\n".print_r($leadinfo, TRUE));
	
	if(
		in_array($leadinfo['source_id'], array(4,5))
		and
		in_array($leadinfo['status_id'], array('CONVERTED',1,2,4,7,8,9,'JUNK'))
		and 
		$leadinfo['status_commited']==1
	) {
		//show_array($leadinfo);
		
		$subj=$sources[$leadinfo['source_id']]."; ".$leadinfo['status_name']."; ".$leadinfo['type']."; ".$leadinfo['phone_norm'];
		
		echo $subj;
		echo "<hr>";
		
		$mess="Источник: <strong>".$sources[$leadinfo['source_id']]."</strong><br>";
		$mess.="Имя: <strong>".$leadinfo['name']."</strong><br>";
		$mess.="Контактная информация: <strong>".$leadinfo['phone']."</strong><br>";
		$mess.="Ценность: <strong>".$leadinfo['type']."</strong><br>";
		$mess.="Статус: <strong>".$leadinfo['status_name']."</strong><br>";
		$mess.="Статус отказа: <strong>".$leadinfo['status_otkaza']."</strong><br>";
		$mess.="Причина отказа: <strong>".$leadinfo['prichina_otkaza']."</strong><br>";
		$mess.="Комментарий: <strong>".$leadinfo['comments']."</strong><br>";
		if(trim($leadinfo['record'])!='') {
			$mess.="<strong><a href='".trim($leadinfo['record'])."'>Ссылка на запись</a></strong><br>";
		}
		else {
			$mess.="Ссылка на запись: <strong>отсутствует</strong><br>";
		}
		$mess.="stats_id=".$leadinfo['stats_id']."<br>";
		$mess.="stats_descruptor=".$leadinfo['stats_descruptor']."<br>";
		$mess.="phone=".$leadinfo['phone_norm']."<br>";
		$mess.="status=".$leadinfo['status_name']."<br>";
		$mess.="type=".$leadinfo['type']."<br>";
		$mess.="voice=".trim($leadinfo['record'])."<br>";
		
		echo $mess;
	
		include('send_email_utf8.php');
		
		//$to_email='cybercroc@gmail.com,sva@wilstream.ru,sytnik@wilstream.ru';
		
		$to_email='sva@wilstream.ru,sytnik@wilstream.ru,checkup@dmt.ru';
		
		$send_res=send_email('mail.wilstream.ru','25','','','',$to_email,'','report@wilstream.ru','','',$subj,$mess,'','');
		
		echo $send_res;
		
		//fwrite($fp, "\nSEND RESULT:\n".print_r($send_res, TRUE));
		
	}
}
//fclose($fp);

function get_lead_info($lead_id) {
	$app_id='local.5fa95159e99329.13335518';
	
	$method='crm.lead.get';
	$get_values='';
	$post_values['id']=$lead_id;
	
	$res=btx_request($app_id,$method,$get_values,$post_values);
	if($res['text']<>'OK') {echo $res['code']." - ".$res['text']; exit();}
	if($res['code']=='204') {echo $res['code']." - No content"; exit();}
	return $res;
}
?>