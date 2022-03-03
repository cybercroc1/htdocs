<?php //функция отправки email через сокет
//new_order.php, tex_edit.php

function send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess) {
	//reply_to_email: если несколько, то через запятую
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
	//$headers="MIME-Version: 1.0 \r\n";
	//$headers.="Content-Type: text/html; charset=\"windows-1251\"\r\n";
	//$headers="To: ".$to."\r\nFrom: ".$from."\r\nSubject: ".$title."\r\n".$headers; 
	$fp = fsockopen($server, 25,$errno,$errstr,30); 
	if (!$fp) die("Server $server. Connection failed: $errno, $errstr"); 
	fputs($fp,"HELO bill\r\n"); 
	fputs($fp,"MAIL FROM: ".$from_email."\r\n"); echo "MAIL FROM: ".$from_email."<br>"; 
	fputs($fp,"RCPT TO: ".$to_email."\r\n"); echo "RCPT TO: ".$to_email."<br>"; 
	fputs($fp,"DATA\r\n"); 
	fputs($fp,$headers."\r\n".$mess."\r\n"."."."\r\n");  
	fputs($fp,"QUIT\r\n"); 
	while(!feof($fp)) {    
		$srv_msg=fgets($fp,1024);
		echo $srv_msg."<br>";
	}
	fclose($fp);
	echo "<hr>";    
}
?>
