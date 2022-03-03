<?php 
include("starcall/session.cfg.php");
set_time_limit(0);

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if($_SESSION['user']['rw_src_bd']<>'r' and $_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

if(!isset($mark)) exit();

$load_ids=implode(',',$mark);

header("Content-type: application/txt; charset=windows-1251");
header("Content-Disposition: attachment; filename=\"выгрузка.txt\""); 

//echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251">';

include("starcall/conn_string.cfg.php");

if(isset($allowed_only)) $where=" and t.allow='y'"; else $where='';
	
$q_phone=OCIParse($c,"select phone from STC_PHONES t
where t.project_id='".$_SESSION['adm']['project']['id']."' and t.load_hist_id in (".$load_ids.") ".$where."
order by ord");
OCIExecute($q_phone,OCI_DEFAULT);				
$phones=array();
while(OCIFetch($q_phone)) {
	$phones[]=OCIResult($q_phone,"PHONE");
}
foreach($phones as $phone) {
	echo $phone.chr(13).chr(10);		
}
?>