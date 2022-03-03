<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //if (!isset($_SESSION['i'])) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if(!isset($login_id) or !isset($project_id)) exit();

if(isset($form)) {
	$del1=OCIParse($c,"delete from sc_access_form where project_id='".$project_id."' and login_id='".$login_id."'");
	$del2=OCIParse($c,"delete from sc_access_form_fix where project_id='".$project_id."' and login_id='".$login_id."'");
	OCIExecute($del1,OCI_DEFAULT);	
	OCIExecute($del2,OCI_DEFAULT);
	$ins1=OCIParse($c,"insert into sc_access_form_fix (project_id,login_id,form_id,date_call,cdpn,cgpn,agid,ivr_sec,queue_sec,alerting_sec,connected_sec,connected_min,call_sec,call_min)
	values ('".$project_id."','".$login_id."',:form_id,:date_call,:cdpn,:cgpn,:agid,:ivr_sec,:queue_sec,:alerting_sec,:connected_sec,:connected_min,:call_sec,:call_min)");
	$ins2=OCIParse($c,"insert into sc_access_form (project_id,login_id,form_id,obj_id)
	select project_id,'".$login_id."',form_id,id from sc_form_object where id=:obj_id");
	
	
	foreach($form as $key=>$val) {
		if(isset($date_call[$key]) 
		or isset($cdpn[$key]) 
		or isset($cgpn[$key]) 
		or isset($agid[$key]) 
		or isset($ivr_sec[$key])
		or isset($queue_sec[$key])
		or isset($alerting_sec[$key])
		or isset($connected_sec[$key])
		or isset($connected_min[$key])
		or isset($call_sec[$key]) 
		or isset($call_min[$key])) {
			if(isset($date_call[$key])) $date_cal='y'; else $date_cal='';
			if(isset($aon[$key])) $ao='y'; else $ao='';
			if(isset($cgpn[$key])) $cgp='y'; else $cgp='';
			if(isset($agid[$key])) $agi='y'; else $agi='';
			if(isset($ivr_sec[$key])) $is='y'; else $is='';
			if(isset($queue_sec[$key])) $qs='y'; else $qs='';
			if(isset($alerting_sec[$key])) $as='y'; else $as='';
			if(isset($connected_sec[$key])) $cns='y'; else $cns='';
			if(isset($connected_min[$key])) $cnm='y'; else $cnm='';
			if(isset($call_sec[$key])) $cs='y'; else $cs='';
			if(isset($call_min[$key])) $cm='y'; else $cm='';
			OCIBindByName($ins1,":form_id",$key);
			OCIBindByName($ins1,":date_call",$date_cal);
			OCIBindByName($ins1,":cdpn",$ao);
			OCIBindByName($ins1,":cgpn",$cgp);
			OCIBindByName($ins1,":agid",$agi);
			OCIBindByName($ins1,":ivr_sec",$is);
			OCIBindByName($ins1,":queue_sec",$qs);
			OCIBindByName($ins1,":alerting_sec",$as);
			OCIBindByName($ins1,":connected_sec",$cns);
			OCIBindByName($ins1,":connected_min",$cnm);
			OCIBindByName($ins1,":call_sec",$cs);
			OCIBindByName($ins1,":call_min",$cm);
			OCIExecute($ins1,OCI_DEFAULT);
		}
	}
	if(isset($obj)) {
		foreach($obj as $key => $val) {
			OCIBindByName($ins2,":obj_id",$key);
			OCIExecute($ins2,OCI_DEFAULT);
		}
	}		
	
OCICommit($c);
}

$q1=OCIParse($c,"select f.id form_id,f.name, a.form,a.date_call,a.cdpn,a.cgpn,a.agid,a.ivr_sec,a.queue_sec,a.alerting_sec,a.connected_sec,a.connected_min,a.call_sec,a.call_min
from sc_forms f, 
(select form_id,decode(count(*),0,null,'checked') form,
decode(max(date_call),null,null,'checked') date_call,
decode(max(cdpn),null,null,'checked') cdpn,
decode(max(cgpn),null,null,'checked') cgpn,
decode(max(agid),null,null,'checked') agid,
decode(max(ivr_sec),null,null,'checked') ivr_sec,
decode(max(queue_sec),null,null,'checked') queue_sec,
decode(max(alerting_sec),null,null,'checked') alerting_sec,
decode(max(connected_sec),null,null,'checked') connected_sec,
decode(max(connected_min),null,null,'checked') connected_min,
decode(max(call_sec),null,null,'checked') call_sec,
decode(max(call_min),null,null,'checked') call_min
from
(select form_id,date_call,cdpn,cgpn,agid,ivr_sec,queue_sec,alerting_sec,connected_sec,connected_min,call_sec,call_min from sc_access_form_fix where login_id='".$login_id."'
union
select form_id,'' date_call,'' cdpn,'' cgpn,'' agid,'' ivr_sec,'' queue_sec,'' alerting_sec,'' connected_sec,'' connected_min,'' call_sec,'' call_min 
from sc_access_form where login_id='".$login_id."')
group by form_id) a
where f.project_id='".$project_id."'
and f.id=a.form_id(+)
order by f.id");

$q2=OCIParse($c,"select o.id,o.name,o.type_id,decode(a.obj_id,null,null,'checked') grand from sc_form_object o, sc_access_form a
where o.form_id=:form_id
and a.login_id(+)='".$login_id."'
and o.id=a.obj_id(+)
order by o.ordering");

echo "<form method=POST>";
OCIExecute($q1,OCI_DEFAULT);
while(OCIFetch($q1)) {
echo "<input type='hidden' ".OCIResult($q1,"FORM")." name='form[".OCIResult($q1,"FORM_ID")."]'><b>".OCIResult($q1,"NAME")."</b></input><br>";
echo "<nobr><input type='checkbox' ".OCIResult($q1,"DATE_CALL")." name='date_call[".OCIResult($q1,"FORM_ID")."]'>Дата звонка</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"CDPN")." name='aon[".OCIResult($q1,"FORM_ID")."]'>АОН</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"AGID")." name='agid[".OCIResult($q1,"FORM_ID")."]'>ID Оператора</input><br>";
echo "<input type='checkbox' ".OCIResult($q1,"IVR_SEC")." name='ivr_sec[".OCIResult($q1,"FORM_ID")."]'>Длит.IVR(сек)</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"QUEUE_SEC")." name='queue_sec[".OCIResult($q1,"FORM_ID")."]'>Время в очереди(сек)</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"ALERTING_SEC")." name='alerting_sec[".OCIResult($q1,"FORM_ID")."]'>Длит.КПВ(сек)</input><br>";
echo "<input type='checkbox' ".OCIResult($q1,"CONNECTED_SEC")." name='connected_sec[".OCIResult($q1,"FORM_ID")."]'>Длит.разговора(сек)</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"CONNECTED_MIN")." name='connected_min[".OCIResult($q1,"FORM_ID")."]'>Длит.разговора(мин)</input><br>";
echo "<input type='checkbox' ".OCIResult($q1,"CALL_SEC")." name='call_sec[".OCIResult($q1,"FORM_ID")."]'>Длит.вызова(сек)</input> | ";
echo "<input type='checkbox' ".OCIResult($q1,"CALL_MIN")." name='call_min[".OCIResult($q1,"FORM_ID")."]'>Длит.вызова(мин)</input><br>";

$form_id=OCIResult($q1,"FORM_ID");
OCIBindByName($q2,":form_id",$form_id);
OCIExecute($q2,OCI_DEFAULT);
	while(OCIFetch($q2)) {
		echo "<input type='checkbox' ".OCIResult($q2,"GRAND")." name='obj[".OCIResult($q2,"ID")."]'>".OCIResult($q2,"NAME")."</input><br>";
	}
echo "<hr>";
}
echo "<input type=submit value='СОХРАНИТЬ'></form>";
?>
</body>
</html>