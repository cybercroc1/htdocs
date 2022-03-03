<?php 

include("../../conf/starcall_conf/session.cfg.php"); 
if(!isset($_SESSION['user']['id'])) exit();
include("../../conf/starcall_conf/conn_string.cfg.php");
if(isset($_SESSION['survey']['project']['id']) and isset($_GET['survey'])) $project_id=$_SESSION['survey']['project']['id']; else $project_id='';

OCIExecute(OCIParse($c,"update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$project_id."'
where id=".$_SESSION['user']['id']));
//подтверждение блокировки текущих записей
if(isset($_GET['with_lock'])) {
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_date=sysdate where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));	
}
?>
