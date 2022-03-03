<?php
if(isset ($_SERVER['REDIRECT_STATUS'])) {  
	
	ini_set('session.use_cookies',0);
	session_id('fail2ban');
	session_start();
	
	$debug='n';
	
	$log_str=date('d.m.Y H:i:s').chr(9).$_SERVER['REMOTE_ADDR'].chr(9).$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
	
	//���������� IP �� �������� �������������� �������
	if($_SERVER['REDIRECT_STATUS']=='404') { //���� �������� �� �������
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		$log_str.=chr(9).$_SERVER['SERVER_PROTOCOL']." 404 Not Found";
		
		$ip=$_SERVER['REMOTE_ADDR'];
		$url=$_SERVER['REDIRECT_URL'];
		
		//�������� ��������
		$count=20; //������������ ���������� �������� ����� �� ������������ �������
		$timeout_sec=3600;  
			
		if( //����� �������� ������� 
			(
				!isset($_SESSION[$ip]) //���� ������ � ������ IP
			) 
				or
			(
				!isset($_SESSION[$ip]['urls'][$url]) //��� ������ � ����� �������
				and 
				time() - $_SESSION[$ip]['date'] > $timeout_sec //� ������� ��� �����
			)
		) { 
			$_SESSION[$ip]['urls'][$url]='';
			$_SESSION[$ip]['date']=time(); //���� ������� ������� ����� �� ������������ ��������
			$_SESSION[$ip]['count']=1; //���������� �������� � ���������� �� ������������ ���������

			$log_str.=chr(9)."file2ban: �����";
			$log_str.=chr(9)."file2ban: ����� ������� ".$_SESSION[$ip]['count'];
			$log_str.=chr(9)."file2ban: �� ".(time() - $_SESSION[$ip]['date'])." ������";
		}
		elseif (isset($_SESSION[$ip]) and !isset($_SESSION[$ip]['urls'][$url])) { //������ ������ URL
			$_SESSION[$ip]['urls'][$url]='';
			$_SESSION[$ip]['count']++;

			$log_str.=chr(9)."file2ban: ����� URL";
			$log_str.=chr(9)."file2ban: ����� ������� ".$_SESSION[$ip]['count'];
			$log_str.=chr(9)."file2ban: �� ".(time() - $_SESSION[$ip]['date'])." ������";

			
			if(time() - $_SESSION[$ip]['date'] <= $timeout_sec and $_SESSION[$ip]['count'] >= $count) {

				require_once('check_ip.php');
				if(check_local_network($ip)) {
					//��������� IP �� �������!
					$log_str.=chr(9)."��������� IP �� �������";
				}
				else {
					//� ���!
					$log_str.=chr(9)."IP � ���!";
					if($debug<>'y') kerio_fail2ban($ip);					
				}
			
				unset($_SESSION[$ip]);					
			}
		}
		if($debug=="y") echo $log_str;
		else {
			$f=fopen('fail2ban.log','a');
			fputs($f,$log_str."\r\n");
			fclose($f);
		}
		exit();
	}
}

function kerio_fail2ban($ip) {

$debug='n';

//Custom variables
$api_cookie="/tmp/kerio-api-cookie";
$api_url="https://192.168.13.1:4081/admin/api/jsonrpc/";
$api_user="admin";
$api_password="quistis";
  
//Initialize the cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json"));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $api_cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $api_cookie);
  
//Server login request to obtain the API token and session cookie
$api_login = array(
  'jsonrpc' => '2.0',
  'id' => 1,
  'method' => 'Session.login',
  'params' => array(
    'userName' => $api_user,
    'password' => $api_password,
    'application' => array(
      'name' => 'Sample app',
      'vendor' => 'Kerio',
      'version' => '1.0'
    )
  )
);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_login));
$return=json_decode(curl_exec($ch),true);

if($debug=='y') {
	echo '<br>';
	echo '<hr>Kerio: ����� �� ������ �����������:<br>';
	print_r($return);
	echo "<hr>";
}
  
//Verify the token, otherwise return the JSON response and exit the script.
if (!isset($return['result']['token']) or $return['result']['token']=="") {/*print_r($return);*/ exit();}
else $token=$return['result']['token'];
  
//Add the token to the cURL headers
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:application/json","X-Token:".$token));
  
//Add host to group
$api_query = array (
  'jsonrpc' => '2.0',
  'id' => 1,
  'method' => 'IpAddressGroups.create',
  'params' => 
	array (
    'groups' => 
		array (
			0 => 
				array (
				'description' => 'Apache f2ban 404 '.date('d.m.y H:i:s').'. '.$_SESSION[$ip]['count'].' new of '.(time() - $_SESSION[$ip]['date']).' sec',
				'enabled' => true,
				'groupId' => 'YXBhY2hlIGZhaWwyYmFu',
				'groupName' => 'Apache fail2ban',
				'host' => $ip,
				'type' => "Host",
		),
    ),
    'refresh' => true,
  ),
);
 
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($api_query));
$return=json_decode(curl_exec($ch),true);

if($debug=='y') {
	echo '<br>';
	echo '<hr>Kerio: ����� �� ������ ���������� IP-������:<br>';
	print_r($return);
	echo "<hr>";
}

//Apply changes
$api_query = array (
  'jsonrpc' => '2.0',
  'id' => 1,
  'method' => 'Batch.run',
  'params' => 
	array (
    'commandList' => 
		array (
			0 => 
				array (
				'method' => 'IpAddressGroups.apply',
			),
			1 => 
				array (
				'method' => 'Session.getConfigTimestamp',
			),
		),
	),
);
 
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($api_query));
$return=json_decode(curl_exec($ch),true);

if($debug=='y') {
	echo '<br>';
	echo '<hr>Kerio: ����� �� ������ ���������� ���������:<br>';
	print_r($return);
	echo "<hr>";
}

if(isset($return['result'][1]['result']['clientTimestampList'][0]['timestamp'])) $timestamp=$return['result'][1]['result']['clientTimestampList'][0]['timestamp'];
else $timestamp='';

//Apply changes
$api_query = array (
  'jsonrpc' => '2.0',
  'id' => 1,
  'method' => 'Session.confirmConfig',
  'params' => 
	array(
	'clientTimestampList' => 
		array(
		0 => array(
			'name' => 'config',
			'timestamp' => $timestamp,
			),
		),
	),
);
 
curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($api_query));
$return=json_decode(curl_exec($ch),true);

if($debug=='y') {
	echo '<br>';
	echo '<hr>Kerio: ����� �� ������ ���������� ���������:<br>';
	print_r($return);
	echo "<hr>";
}
  
curl_close($ch);	
}
?>



