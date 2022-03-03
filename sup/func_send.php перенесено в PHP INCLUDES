<?php //функция отправки email через сокет
//new_order.php, order.edit.save.php

function send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess) {
	//reply_to_email: если несколько, то через запятую
	$res='';
	
	if($from_email=='') $from_email='support@wilstream.ru';
	if($server=='') $server='mailex';
	if ($to_name<>"") $to_name="=?koi8-r?B?".base64_encode(convert_cyr_string($to_name, "w","k"))."?=";
	$headers="To: ".$to_name."<".$to_email.">\r\n";
	if ($from_name<>"") $from_name="=?koi8-r?B?".base64_encode(convert_cyr_string($from_name, "w","k"))."?=";
	$headers.="From: ".$from_name."<".$from_email.">\r\n";
	if ($reply_to_name<>"") $reply_to_name="=?koi8-r?B?".base64_encode(convert_cyr_string($reply_to_name, "w","k"))."?=";
	if ($reply_to_email<>"") {
		$rpltoemls=explode(',',$reply_to_email);
		foreach($rpltoemls as $val) {
			$headers.="Reply-To: ".$reply_to_name."<".$val.">\r\n";
			$headers.="Return-Path: ".$reply_to_name."<".$val.">\r\n";
		}
	}
	else {
		$headers.="Reply-To: <noreply>\r\n";
		$headers.="Return-Path: <noreply>\r\n";
	}
	$headers.="Subject: ".$subj."\r\n";
	$headers.="MIME-Version: 1.0 \r\n";
	$headers.="Content-Type: text/html; charset=\"windows-1251\"\r\n";

	$fp = fsockopen($server, 25,$errno,$errstr,10); 
	if (!$fp) {
		$res="Error: Unable connect to server: ".$server; 
		return $res;
	}
	fputs($fp,"HELO bill\r\n"); 
	fputs($fp,"MAIL FROM: ".$from_email."\r\n"); 
	//echo " - ".fgets($fp,1024);
	fputs($fp,"RCPT TO: ".$to_email."\r\n"); 
	//echo " - ".fgets($fp,1024);
	fputs($fp,"DATA\r\n"); 
	//echo fgets($fp,1024);
	fputs($fp,$headers."\r\n".$mess."\r\n"."."."\r\n");  
	//echo fgets($fp,1024);
	fputs($fp,"QUIT\r\n"); 
	//echo fgets($fp,1024);
	while(!feof($fp)) {    
		$srv_msg=fgets($fp,1024);
		echo $srv_msg."<br>";
		if(substr($srv_msg,0,3)>400) {$res=$srv_msg; break;} else {$res='OK';}
	}
	fclose($fp);
	return($res);
}
?>
