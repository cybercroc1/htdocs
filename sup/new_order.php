<?php
extract($_REQUEST);
if (isset($sid) and $sid<>'') {session_id($sid); session_start();}
else $sid='';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Новая заявка на техподдержку</title>
<script src='new_order.js'></script>
</head>
<body>
<script>
function fFindLoc() {
	with(document.all) {
		if(find_loc.value.length>=3) {
			//alert(find_loc.value);	
			location_id.options[0].selected=true;
			var pat = new RegExp("^.*"+find_loc.value.toUpperCase()+".*$");
			for(i=0; i<location_id.length; i++) {
				//alert(location_id.options[i].innerText);
				if(pat.test(location_id.options[i].innerText.toUpperCase())) {
					location_id.options[i].selected=true; break;
				}
			}
		}
		else {
			location_id.options[0].selected=true;
		}
	}
}
/*
function add_file() {
	et=frm.enctype;
	tg=frm.target;
	ac=frm.action;
	frm.enctype='multipart/form-data';
	frm.target='oper_ifr';
	frm.action='files.php';
	frm.submit();
	frm.enctype=et;
	frm.target=tg;
	frm.action=ac;	
}
*/
</script>
<?php
set_error_handler ("my_error_handler");
$err_count=0;

if (!isset($cdpn)) $cdpn='';
if (!isset($cgpn)) $cgpn='';
if (!isset($agid)) $agid='';
if (!isset($location_id)) $location_id='';
if (!isset($trbl_id)) $trbl_id='';
if (!isset($location_names)) $location_names=array();
if (!isset($kto)) $kto='';
if (!isset($kto_id)) $kto_id='';
if (!isset($reply_to_email)) $reply_to_email='';
if (!isset($reply_to_name)) $reply_to_name='';
if (!isset($u_kogo)) $u_kogo='';
if (!isset($oper_coment)) $oper_coment='';

include("sup/sup_conn_string");
//include("func_send.php");
include("send_email.php");
include("sup/send_sms.php");

//авторизованный пользователь
if($sid<>'') {
	$q=OCIParse($c,"select lt_group_id from SUP_USER_LT_ALLOC where user_id=".$_SESSION['user_id']." and create_new='y'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $lt_grp_id=array(); while(OCIFetch($q)) {$i++; $lt_grp_id[$i]=OCIResult($q,"LT_GROUP_ID");}
	$lt_grp_ids=implode(',',$lt_grp_id);
	if($i>0) {
		
		$kto_id=$_SESSION['user_id'];

		$q=OCIParse($c,"select fio from sup_user where id='".$kto_id."'");
		OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
		$kto=OCIResult($q,"FIO");

		$q=OCIParse($c,"select email from sup_texnari_emails where texnari_id='".$kto_id."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
		if($i>0) $reply_to_email=implode(',',$eml);
		$reply_to_name=$_SESSION['fio'];
	}
	else {
		echo "<font color=red><b>ОШИБКА: У Вас нет прав для создания заявок ни в одной из групп.</b></font>";
		exit();
	}
}
//
//неавторизованный пользователь (оператор)
else {
	if(
		substr($_SERVER['REMOTE_ADDR'],0,8)<>'192.168.'
	) {
		echo "<font color=red><b>ОШИБКА: Неавторизованный доступ возможен только из локальной сети</b></font>";
		exit();
	}
	//проверка существования запрошенной группы
	if(isset($lt_oper_grp) and $lt_oper_grp<>'') {
		$q=OCIParse($c,"select id from SUP_LT_GROUP t where id='".$lt_oper_grp."'");
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) $lt_grp_ids=OCIResult($q,"ID"); else $lt_grp_ids='';
		$noauth='y';
	}
	if($lt_grp_ids=='') {
		echo "<font color=red><b>ОШИБКА: Неуказана группа или группа несуществует</b></font>";
		exit();
	}
}
if($lt_grp_ids=='') {
	echo "<font color=red><b>ОШИБКА: Вам неназначена ни одна группа</b></font>";
	exit();
}
//

if (isset($send)) {
	
	if(!isset($trbl_id) or $trbl_id=='') {echo "<font color=red><b>ОШИБКА: Не выбран тип проблемы</b></font><br>"; $err='';}
	if(!isset($kto) or $kto=='') {echo "<font color=red><b>ОШИБКА: Не заполнено поле \"Кто звонил\"</b></font><br>"; $err='';}
	if(!isset($u_kogo) or $u_kogo=='') {echo "<font color=red><b>ОШИБКА: Не заполнено поле \"У кого\"</b></font><br>"; $err='';}
	if(!isset($oper_coment) or $oper_coment=='') {echo "<font color=red><b>ОШИБКА: Не заполнено поле \"Комментарий\"</b></font><br>"; $err='';}
	echo "<hr>";
	if(!isset($err)) {
		zayavka();
		
		$tmp="<script>alert('Заявка № $num_zayavki отправлена!');</script>";
		$num_zayavki=''; $location_id=''; $trbl_ids=array(); $kto=''; $u_kogo=''; $oper_coment='';
		if ($err_count==0) {
			echo $tmp;
			if(!isset($noauth))	{echo "<script>
				window.opener.location.reload();
				self.close();
				</script>";}
			else {echo "<script>
				document.location=document.location;
				</script>";
			}
			exit();
		}
	}
}


echo "<form name=frm method=post>";

echo "<input type=hidden name=cdpn value='$cdpn'>";
echo "<input type=hidden name=cgpn value='$cgpn'>";
echo "<input type=hidden name=agid value='$agid'>";
echo "<input type=hidden name=sid value='$sid'>";


//выбор локации
$location_ids=array(); $location_names=array();
if($location_id<>'') {
	$q=OCIParse($c,"select sk.id,sk.name location_name from sup_klinika sk
	where sk.id='".$location_id."' and sk.deleted is null
	order by sk.name");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$location_name=OCIResult($q,"LOCATION_NAME");
	$location_ids[1]=$location_id;
	$location_names[1]=$location_name;
}
else {//можно выбрать только локации из групп, в которых есть стрелочники или исполнители
	$q=OCIParse($c,"select distinct k.id,lg.id group_id,lg.name group_name,k.name location_name 
from sup_lt lt, sup_klinika k, sup_lt lt2, sup_user_lt_alloc sla, sup_user u, SUP_LOCATION_GROUP lg
where lt.lt_grp_id in (".$lt_grp_ids.")
and k.id=lt.location_id
and k.deleted is null
and lt2.location_id=k.id
and sla.lt_group_id=lt2.lt_grp_id and (sla.redirect='y' or sla.solution='y')
and u.id=sla.user_id and u.deleted is null
and lg.id=k.location_grp_id
order by lg.name,k.name");
	
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$location_ids[$i]=OCIResult($q,"ID");
		$location_names[$i]=OCIResult($q,"LOCATION_NAME");
		$location_grp_ids[$i]=OCIResult($q,"GROUP_ID");
		$location_grp_names[$i]=OCIResult($q,"GROUP_NAME");
	}
}

if(count($location_ids)==1) {
	if(!isset($num_zayavki) or $num_zayavki=='') {
		$q=OCIParse($c,"select sup_base_id.nextval from dual");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$num_zayavki=OCIResult($q,"NEXTVAL");
	}

	$location_id=$location_ids[1];
	$location_name=$location_names[1];
	
	echo "<script>window.name='sup_order_".$num_zayavki."';</script>"; //переименовываем окно, что бы оно не перекрылось другой заявокй		
	
	echo "<font size=4>".$location_name.". <font color=green>Новая заявка № $num_zayavki</font></font><br>";
	echo "<font color=red>ВНИМАНИЕ! По разным типам проблем создавайте разные заявки.<br>
	Например, если не работет интернет и 1С, должно быть 2 разные заявки.</font>"; 
	echo "<input type=hidden name=location_id value='".$location_id."'>";
}
elseif(count($location_ids)>1) {
	echo "поиск: <input type=text name=find_loc value='' onkeyup=fFindLoc()><br>";
	echo "<select name=location_id onchange='frm.ok.click()'>";
	echo "<option value='' style='color:red'>ВЫБЕРИТЕ МЕСТО</option>";
	foreach($location_ids as $i => $id) {
		if(!isset($tmp_group_id) or $tmp_group_id<>$location_grp_ids[$i]) {echo "<optgroup label='".$location_grp_names[$i]."'></optgroup>";}
		echo "<option value='".$id."'";
		if($id==$location_id) {
			echo " selected";
		}
	echo ">".$location_names[$i]."</option>";
	$tmp_group_id=$location_grp_ids[$i];
	}
	echo "</select>";
	echo "<input type=submit name='ok' value='ok'>";
	/*echo "<script>frm.ok.style.display='none';</script>";*/
	exit();
}
else {echo "<font color=red>Ошибка! Нет доступных локаций</font>"; exit();}
//выбор локации

echo "<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'>
<tr>";
echo "<td bgcolor=white valign=top>";
echo "№ заявки: <b>$num_zayavki</b>";
echo "<input type=hidden name=num_zayavki value='$num_zayavki'>";
echo "<hr>";

if(!isset($_SESSION['user_id'])) echo "<b><font color=red size=4>*</font>ФИО (кто звонил/обратился):</b><br><input type=text name=kto style='width:97%' value='".$kto."' onkeyup='check()'></input>";
else echo "<b>".$kto." <input type=hidden name=kto value='".$kto."'></b>";
echo "<hr>";	
echo "<b><font color=red size=4>*</font>ФИО, номер места/добавочного<br>(у кого не работает):</b><br> <input type=text size=30 name=u_kogo style='width:97%' value='".$u_kogo."' onkeyup='check()'></input><hr>";
//echo "</td>";

/*	//шлем СМС-уведомление
	$q=OCIParse($c,"select distinct stp.phone from sup_trbl_type stt, sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where stt.id in (".implode(',',$trbl_ids).") and slt.location_id='".$location_id."' 
	and slt.trbl_id=stt.id 

	and sla.lt_group_id=slt.lt_grp_id 
	and su.id=sla.user_id";
*/

echo "<b><font color=red size=4>*</font>Тип проблемы: </b><br>";
//можно выбрать только проблемы из групп, в которых есть стрелочники или исполнители
$q=OCIParse($c,"select distinct stt.id,stt.name, stg.name group_name
from sup_lt lt, sup_trbl_type stt, sup_lt lt2, sup_user_lt_alloc sla, sup_user u, sup_trbl_group stg
where lt.lt_grp_id in (".$lt_grp_ids.") and lt.location_id=".$location_id."
and stt.id=lt.trbl_id
and stt.deleted is null
and lt2.trbl_id=stt.id
and lt2.location_id=".$location_id."
and sla.lt_group_id=lt2.lt_grp_id and (sla.redirect='y' or sla.solution='y')
and u.id=sla.user_id and u.deleted is null
and stg.id=stt.trbl_grp_id
order by stg.name,stt.name");
OCIExecute($q,OCI_DEFAULT);
echo "<select name=trbl_id onchange='check()'><option></option>";
while (OCIFetch($q)) {
	echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$trbl_id?' selected':'').">".OCIResult($q,"NAME")."</option>";
	
	
	/*echo "<input type=radio name=trbl_id value='".OCIResult($q,"ID")."'";
	if($trbl_id==OCIResult($q,"ID")) echo " checked";
	echo ">".OCIResult($q,"NAME")."</input><br>";*/
}
echo "</select>";
echo "</td>
</tr>";
echo "<tr><td bgcolor=white valign=top>";	
echo "<b><font color=red size=4>*</font>Описание проблемы:</b>";
echo "<br>
<textarea rows=7 name=oper_coment style='width:98%' onkeyup='check()'>".$oper_coment."</textarea><br>";
echo "</td></tr></table>";

/*
echo "<hr><nobr>Прикрепить файлы: <input type=file multiple name=new_file[] onchange=add_file()><input type=submit name=upload_file style='display:none'></nobr>";
echo "<div id=div_tmp_files></div>";
echo "<hr>";
*/
echo "<hr><div id=div_add_file>";

		echo "Загрузите файл, перетащив его в данную область";
		echo  "<input type='file' id='fileElem' multiple onchange='handleFiles(this.files)' />";
		echo  "<label class='file_button' for='fileElem'>";
		echo  "Выбрать файлы</label>";

		echo "</div><hr>";
		//список временных файлов со ссылками
		$q_files=OCIParse($c,"select id,filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where base_id='".$num_zayavki."' and tmp='y' and nvl(sess_id,0)=nvl('".$sid."',0)
		order by filename");
		OCIExecute($q_files);
		echo "<script>";
		$i=0; while(OCIFetch($q_files)) { $i++;
			echo "parent.add_file_link('".OCIResult($q_files,"ID")."','".OCIResult($q_files,"FILETYPE")."','".OCIResult($q_files,"FILENAME")."'); ";
		}
		echo "</script>";


echo "<table style='max-width:550px'><tr><td align=left>";

echo "<nobr><input type=submit name=send value='Отправить'> <b><font color=red size=4>*</font></b> - обязательное поле</nobr>";

echo "</td>";
echo "<td align=right width='100%'>";
echo "</td></tr></table>";
echo "</form>";
echo "<iframe name=oper_ifr style='display:none'></iframe>";
echo "
<script>
check();
function check() {
	if (frm.location_id.value=='' || frm.oper_coment.value=='' || frm.trbl_id.value=='' || frm.u_kogo.value=='' || ('kto' in frm && frm.kto.value=='')) {
		frm.send.disabled=true;
		frm.send.style.background='';
	}
	else {
		frm.send.disabled=false;
		frm.send.style.background='#66FF66';
	}
}
</script>
";


function zayavka() {
global $c;
global $num_zayavki;
global $cdpn;
global $cgpn;
global $agid;
global $oper_coment;
global $location_id;
global $kto_id;
global $kto;
global $reply_to_email;
global $reply_to_name;
global $u_kogo;
global $trbl_id;
global $err_count;
global $sid;

include("sup/smtp_conf.php");

$q=OCIParse($c,"select id from sup_base where id='$num_zayavki'");
OCIExecute($q,OCI_DEFAULT);
if(OCIFetch($q)) {echo "<font color=green>Заявка №<b>$num_zayavki</b>УЖЕ ОТПРАВЛЕНА!</font><hr>"; $err_count++; return;}
$ins=OCIParse($c,"insert into sup_base (id,date_in_call,cdpn,cgpn,agid,oper_comment,klinika_id,kto_id,kto,u_kogo,ip_address,trbl_type_id)
values ('$num_zayavki',sysdate,'$cdpn','$cgpn','$agid',:oper_coment,'$location_id','$kto_id','$kto','$u_kogo','".$_SERVER['REMOTE_ADDR']."','$trbl_id')");
OCIBindByName($ins,":oper_coment",$oper_coment);
OCIExecute($ins,OCI_DEFAULT);
//файлы
$upd=OCIParse($c,"update sup_files set tmp='' where base_id='".$num_zayavki."' and nvl(sess_id,0)=nvl('".$sid."',0) and tmp='y'");
OCIExecute($upd,OCI_DEFAULT);

OCICommit($c);

//контактные телефоны
$cont_phones=array();
if($kto_id<>'') {
	$q_tmp=OCIParse($c,"select decode(type,'mob','8'||phone,phone) phone from SUP_TEXNARI_PHONES t where texnari_id='".$_SESSION['user_id']."' and contact='y' and valid_date is not null order by ord");
	OCIExecute($q_tmp,OCI_DEFAULT);
	$i=0; while (OCIFetch($q_tmp)) {$i++;
		$cont_phones[$i]=OCIResult($q_tmp,"PHONE");
	}
}

$q=OCIParse($c,"select name,phone from sup_klinika where id='".$location_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$klinika_name=OCIResult($q,"NAME");
$klinika_phone=OCIResult($q,"PHONE");

//формируем письмо
$server='';
if($kto_id<>'') $from_name=$kto; else $from_name='Техподдержка';
$from_email='';
$subj='Заявка №'.$num_zayavki.' - '.$klinika_name;

$mess="<font size=4><nobr>".$klinika_name.($klinika_phone<>''?' ('.$klinika_phone.')':'').".</nobr><font color=green> Новая заявка</font></font>";
$mess.="<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'><tr>";
$mess.="<td bgcolor=white valign=top><nobr>№ заявки: <b>".$num_zayavki." </b></nobr>";
if($cdpn<>'') $mess.="<nobr>АОН: <b>".$cdpn." </b></nobr>";
$mess.="<nobr>IP: <b>".$_SERVER['REMOTE_ADDR']." </b></nobr><hr>";

$mess.="Кто обратился: <nobr><b>".$kto."</b>; </nobr>";
if($kto_id<>'') {
	if(isset($cont_phones)) {
		$mess.="<nobr>";
		$mess.=implode(', ',$cont_phones);
		$mess.="; </nobr>";
	}	
	$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".$kto_id."'");
	OCIExecute($q_tmp,OCI_DEFAULT);
	$i=0; while(OCIFetch($q_tmp)) {$i++;
		$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
	}
	if(isset($mailtos)) {
		$mess.="<nobr>";
		$mailtos=implode(', ',$mailtos);
		$mess.="<a href='mailto:".$mailtos."?subject=Заявка №".$num_zayavki." - ответ'>".$mailtos."</a>";
		$mess.="; </nobr>";
	}
}
$mess.="<hr>
У кого не работает: <nobr><b>".$u_kogo." </b><hr>
Тип проблемы: ";
$q=OCIParse($c,"select t.name from sup_trbl_type t
where t.id='".$trbl_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$trbl_name=OCIResult($q,"NAME");
if(isset($trbl_name)) {
		$mess.="<b>".$trbl_name."<br>";
}
$mess.="</b><hr>Суть проблемы: <b>".nl2br($oper_coment)."</b>";

//файлы
$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id='".$num_zayavki."' and tmp is null and hist_id is null");
OCIExecute($q_files);
$i=0; while(OCIFetch($q_files)) { $i++;
	if($i==1) {
		$mess.="<hr>Файлы: ";
	}
	$mess.="<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
}

$mess.="</td></tr></table>";
$mess.="<br>ссылка на заявку: <a href='http://sup.wilstream.ru?ticketId=".$num_zayavki."' target=_balnk>sup.wilstream.ru</a>";
//

	//2.1.1. Шлём только тем, кто может заниматься этой конкретной проблемой в конкретном месте, кроме себя 
	$q=OCIParse($c,"select distinct ste.email from sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_emails ste
	where slt.trbl_id=".$trbl_id." and slt.location_id='".$location_id."' 
	and sla.lt_group_id=slt.lt_grp_id 
	and su.id=sla.user_id
	and sla.em_new='all' --только тем, кто должен получать все открытые заявки
	and su.id<>nvl('".$kto_id."','0') 
	and su.deleted is null
	and ste.valid_date is not null
	and ste.texnari_id=su.id");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		$to_name='';
		$to_email=OCIResult($q,"EMAIL");
		//$email_res=send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess);
		
		$time1=time();
		
		$email_res=send_email(
			$smtp_server, 
			$smtp_port,
			$smtp_auth_login, 
			$smtp_auth_pass, 
			$to_name='', 
			$to_email, 
			$from_name, 
			$smtp_from_email, 
			$reply_to_name, 
			$reply_to_email,
			$subj, 
			$mess,
			'', 
			$debug=''
		);
		
		$smtp_dur_sec=time()-$time1;
		
		echo "Отправка EMAIL: ".$num_zayavki." - ".$smtp_server.":".$smtp_port." - ".$to_email." - ".$email_res;
		
		$ins=OCIParse($c,"insert into sup_smtp_log 
		(datetime, sup_base_id, history_id, to_user_id, smtp_server, smtp_port, smtp_login, smtp_from_email, smtp_to_email, subj, smtp_result, dur_sec)
		values
		(sysdate,".$num_zayavki.",'','',:smtp_server,".$smtp_port.",:smtp_login,:smtp_from_email,:smtp_to_email,:subj,:smtp_result,".$smtp_dur_sec.")");
		OCIBindByName($ins,":smtp_server",$smtp_server);
		OCIBindByName($ins,":smtp_login",$smtp_auth_login);
		OCIBindByName($ins,":smtp_from_email",$smtp_from_email);
		OCIBindByName($ins,":smtp_to_email",$to_email);
		OCIBindByName($ins,":subj",$subj);
		OCIBindByName($ins,":smtp_result",$email_res);
		OCIExecute($ins);
		OCICommit($c);
		
		if(substr($email_res,0,31)=='Error: Unable connect to server') {echo "<hr>"; break;} //если ошибка подключения к серверу, то нет смысла пытаться отправить остальным
		echo "<hr>";
	}

	//шлем СМС-уведомление
	$q=OCIParse($c,"select distinct stp.phone from sup_lt slt, sup_user_lt_alloc sla, sup_user su, sup_texnari_phones stp
	where slt.trbl_id=".$trbl_id." and slt.location_id='".$location_id."'  
	and sla.lt_group_id=slt.lt_grp_id 
	and su.id=sla.user_id
	and sla.sm_new='all' --только тем, кто должен получать все открытые заявки
	and su.id<>nvl('".$kto_id."','0') 
	and su.deleted is null
	and stp.sms='y'
	and stp.valid_date is not null
	and stp.texnari_id=su.id");

	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$sms_phones[$i]=OCIResult($q,"PHONE");
	}
	if(isset($sms_phones)) {
		$Phone_list=implode(',',$sms_phones);
		$sms_type='status';
		$sms_text=$num_zayavki."-новая заявка.".chr(10);
		$sms_text.=$klinika_name.".".chr(10);
		if($kto_id=='' and $klinika_phone<>'') $sms_text.=$klinika_phone.chr(10);
		if(isset($trbl_name)) {
			$sms_text.=$trbl_name.";".chr(10);
		}
		if(trim($oper_coment)<>'') {
			if(count(trim($oper_coment))>30) $sms_text.=substr(trim($oper_coment),0,27).'...'.chr(10);
			else $sms_text.=substr(trim($oper_coment),0,30).';'.chr(10);
		}
		$sms_text.=$kto.chr(10);
		if($kto_id<>'') {
			foreach ($cont_phones as $phone) {
				$sms_text.=$phone.chr(10);
			}
		}
		$sms_text.="http://sup.wilstream.ru/?ticketId=".$num_zayavki;
		
		$sms_result=send_sms_old($num_zayavki,$Phone_list,$sms_text,$sms_type);
		
		echo "Отправка СМС: ".$sms_result['result_text'];

		echo "<hr>";
	}
    //
}

//Функция обработки ошибок
function my_error_handler($code, $msg, $file, $line) {

global $err_count;
global $c;
$err_count++;
OCIRollback($c);
echo "<font color=red>ОШИБКА! (см. SUP_ERR_LOG)</font><br>";
echo "<font color=red>ОШИБКА! $code - ".(str_replace('\'',' ',$msg))." - ".(str_replace('\'',' ',$file))." - ".(str_replace('\'',' ',$line))."'</font><hr>";
$ins=OCIParse($c,"insert into SUP_ERROR_LOG (datetime,IP_ADDRESS,ACTION_TYPE,ERR_CODE,ERR_MSG,ERR_FILE,ERR_LINE,RESULT)
values (sysdate,'".$_SERVER['REMOTE_ADDR']."','new_order',:err_code,:err_msg,:err_file,:err_line,'rollback')");
OCIBindByName($ins,":err_code",$code);
OCIBindByName($ins,":err_msg",$msg);
OCIBindByName($ins,":err_file",$file);
OCIBindByName($ins,":err_line",$line);
OCIExecute($ins,OCI_DEFAULT);
OCICommit($c);
}
?>
<script>
//РАБОТА С ФАЙЛАМИ
var dropArea = document.getElementById('div_add_file');

dropArea.addEventListener('dragenter', preventDefaults, false);
dropArea.addEventListener('dragover', preventDefaults, false);
dropArea.addEventListener('dragleave', preventDefaults, false);
dropArea.addEventListener('drop', preventDefaults, false);

dropArea.addEventListener('dragenter', highlight, false);
dropArea.addEventListener('dragover', highlight, false);

dropArea.addEventListener('dragleave', unhighlight, false);
dropArea.addEventListener('drop', unhighlight, false);

function preventDefaults (e) {
  e.preventDefault();
  e.stopPropagation();
}
function highlight(e) {
  dropArea.classList.add('highlight');
}
function unhighlight(e) {
  dropArea.classList.remove('highlight');
}
dropArea.addEventListener('drop', handleDrop, false);
function handleDrop(e) {
  var dt = e.dataTransfer;
  var files = dt.files;
  handleFiles(files);
}
function handleFiles(files) {
  for (var i = 0; i < files.length; i++) {
	uploadFile(files[i]);
  }  
}
</script>
</body>
</html>
