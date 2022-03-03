<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");

extract($_REQUEST);
//дл€ операторов вход€щего отдела
if(isset($anonym)) {
	$user_id='';
}
//дл€ авторизованных пользователей
else {
	ini_set('session.use_cookies','1');

	session_name('medc');
	session_start();
	
	if(!isset($_SESSION['login_id_med'])) {
		echo "Ќеверный запрос1";
		exit();		
	}
	$user_id=$_SESSION['login_id_med'];
}

if(
	  (!isset($base_id))	
	or(!isset($oktell_server_address))
	or(!isset($phone_prefix))
	or(!isset($phone_number))
	or(!isset($okt_IdUser))
	or(!isset($okt_IdChain))	
) { 
	echo "Ќеверный запрос2";
	exit();
}


include("med/conn_string.cfg.php");
	
//добавл€ем новую попытку звонка
if(!isset($call_hist_id) or $call_hist_id=='') {
	$call_hist_id='';
	$q = OCIParse($c,"insert into oktell_call_hist 
	(id,start_date,base_id,oktell_server_id,oktell_idchain,oktell_iduser,phone_prefix,phone_number,user_id)
	values (SEQ_OKTELL_CALL_HIST_ID.nextval,sysdate,'".$base_id."',
	(select server_id from OKTELL_SERVER_ADDR where server_address='".$oktell_server_address."'),
	'".$okt_IdChain."','".$okt_IdUser."','".$phone_prefix."','".$phone_number."','".$user_id."') returning id into :call_hist_id");
	OCIBindByName($q,":call_hist_id",$call_hist_id,16);
	if(OCIExecute($q)) {
		OCICommit($c);
	}
	echo "new_id:".$call_hist_id.";";
}
//если такой звонок уже есть обновл€ем ID цепочки
else {
	$upd = OCIParse($c,"update oktell_call_hist set oktell_idchain='".$okt_IdChain."' where id='".$call_hist_id."'");
	OCIExecute($upd);
	OCICommit($c);	
}

?>

