<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("../../conf/starcall_conf/conn_string.cfg.php");

//очистка фреймов и сброс переменных
echo "<script>parent.surveyAnkFrame.location='survey.ank.php';</script>";
$_SESSION['survey']['base_id']='';

$project_id=$_SESSION['survey']['project']['id'];
if($project_id=='') echo "<script>parent.document.location='survey.projects.php?exit';</script>";

//сохраняем выбор квоты и статусов
if(isset($status_type)) $_SESSION['survey']['status_type']=$status_type;
if(!isset($_SESSION['survey']['status_type'])) $_SESSION['survey']['status_type']='auto';
$status_type=$_SESSION['survey']['status_type'];

if(isset($quote_id)) $_SESSION['survey']['quote_id']=$quote_id;
if(!isset($_SESSION['survey']['quote_id'])) $_SESSION['survey']['quote_id']='auto';
$quote_id=$_SESSION['survey']['quote_id'];
//
$user_id=$_SESSION['user']['id'];

if(!isset($base_id)) $base_id='';
if(!isset($phone)) $phone='';

if(isset($change)) {
	$base_id=''; 
	$phone='';
	//разблокировка записей
	func_unlock();
}

echo "<form name=frm method=post>";
echo "<input type=hidden name=set_status>";
echo "<input type=submit style=display:none name=change>";

//проверка прав доступа к проекту =================================================

if($_SESSION['user']['operator_only']=='y') $where_prj="and (p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id=".$_SESSION['user']['id']." and gp.group_id=gu.group_id))";

elseif($_SESSION['user']['all_projects']=='y') $where_prj=''; 
else $where_prj=" and (
		--проекты созданные мной или моими потомками
		p.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
		or
		--группы, в которых участвую я или мои потомки 
		p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=gu.group_id) 
		or 
		--группы созданные мной или моими потомками
		p.id in (select gp.project_id from STC_USER_GROUP g, STC_USER_GRP_PRJ gp where g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=g.id)
		)";

$q=OCIParse($c,"select p.id,p.name,to_char(p.create_date,'YYYY.MM.DD') create_date, p.from_time, p.to_time, p.nedoz_interval,
p.quote,p.stat_new,p.stat_end_norm,p.stat_inwork,p.stat_nedoz,p.stat_perez, p.quote-p.stat_end_norm estimate,
decode(p.quote,0,'100%',decode(p.quote,NULL,NULL,round(p.stat_end_norm/p.quote*100,0)||'%')) percent,
case when p.quote-p.stat_end_norm<=0 then 'quote_full' else p.status end status, 
p.num_src_fields, p.num_phone_fields, p.perez_policy, p.nedoz_chance
from STC_PROJECTS p
where status<>'Закрыт' and p.id='".$project_id."'
".$where_prj."
order by p.name, p.create_date");
OCIExecute($q);

//для безопасности: прерменная $_SESSION['survey']['project']['id'] установится заново после выборки только в случае, если пользователь имет доступ к проекту
$_SESSION['survey']['project']['id']='';

if(OCIFetch($q)) {
$color=''; $disabled='';
	if(OCIResult($q,"ID")==$project_id) {
		$_SESSION['survey']['project']['id']=OCIResult($q,"ID");
		$project_id=OCIResult($q,"ID");
		$project_name=OCIResult($q,"NAME");
		$project_status=OCIResult($q,"STATUS");
		$from_time=OCIResult($q,"FROM_TIME");
		$to_time=OCIResult($q,"TO_TIME");
		$nedoz_interval=OCIResult($q,"NEDOZ_INTERVAL");
		$quote=OCIResult($q,"QUOTE");
		$new_count=OCIResult($q,"STAT_NEW");
		$norm_count=OCIResult($q,"STAT_END_NORM");
		$nedoz_count=OCIResult($q,"STAT_NEDOZ");
		$perez_count=OCIResult($q,"STAT_PEREZ");
		$inwork_count=OCIResult($q,"STAT_INWORK");
		$estim_count=OCIResult($q,"ESTIMATE");
		$percent_full=OCIResult($q,"PERCENT");
		$num_src_fields=OCIResult($q,"NUM_SRC_FIELDS");
		$num_phone_fields=OCIResult($q,"NUM_PHONE_FIELDS");
		$perez_policy=OCIResult($q,"PEREZ_POLICY");
		$nedoz_chance=OCIResult($q,"NEDOZ_CHANCE");
	}
}
else {
	echo "<font color=red>У вас нет доступа к выбранному проекту</font> | <a href='survey.projects.php' target=_parent>Выбор проекта</a>"; 
	exit();
}



/*while (OCIFetch($q)) {
$color=''; $disabled='';
	if(OCIResult($q,"ID")==$project_id) {
		$_SESSION['survey']['project']['id']=OCIResult($q,"ID");
		$project_id=OCIResult($q,"ID");
		$project_status=OCIResult($q,"STATUS");
		$from_time=OCIResult($q,"FROM_TIME");
		$to_time=OCIResult($q,"TO_TIME");
		$nedoz_interval=OCIResult($q,"NEDOZ_INTERVAL");
		$quote=OCIResult($q,"QUOTE");
		$new_count=OCIResult($q,"STAT_NEW");
		$norm_count=OCIResult($q,"STAT_END_NORM");
		$nedoz_count=OCIResult($q,"STAT_NEDOZ");
		$perez_count=OCIResult($q,"STAT_PEREZ");
		$inwork_count=OCIResult($q,"STAT_INWORK");
		$estim_count=OCIResult($q,"ESTIMATE");
		$percent_full=OCIResult($q,"PERCENT");
		$num_src_fields=OCIResult($q,"NUM_SRC_FIELDS");
		$num_phone_fields=OCIResult($q,"NUM_PHONE_FIELDS");
		$perez_policy=OCIResult($q,"PEREZ_POLICY");
		$nedoz_chance=OCIResult($q,"NEDOZ_CHANCE");
	}
	if(OCIResult($q,"STATUS")=='Приостановлен') {$color='orange'; /*$disabled=' disabled';}
	if(OCIResult($q,"STATUS")=='quote_full') {$color='green'; /*$disabled=' disabled';}
	echo "<option style=color:".$color.$disabled." value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$_SESSION['survey']['project']['id']?' selected':'').">".OCIResult($q,"NAME")." (".OCIResult($q,"CREATE_DATE").") ".OCIResult($q,"PERCENT")."</option>";
}
echo "</select>";
*/

echo "<table width='100%'><tr><td align=left style='background:none;border:0'>";

echo "<nobr><font size=3><b>".$project_name."</b></font>";
echo "</td>";
echo "<td align=right style='background:none;border:0'>";
if($_SESSION['user']['operator_only']=='y') {
	echo "<nobr>";
	echo $_SESSION['user']['fio']." | ";
	echo $_SESSION['user']['role_name']." | ";		
	echo "<a href='survey.projects.php' target=_parent>Выбор проекта</a> | ";
	echo "<a href='login.php?exit'><font color=red>Выход</font></a>";
} 
echo "</td>";
echo "</tr></table><hr>";

//сохранение статуса звонка+++++++++++++++++++++++++++++++++++++++++++++
if(isset($set_status) and $set_status<>'') {

	if($set_status=='nedoz') {//ставим недозвон
		OCIExecute(OCIParse($c,"update STC_PHONES t set status='".$set_status."',status_date=sysdate,nedoz_count=nvl(nedoz_count,0)+1 where project_id=".$project_id." and base_id=".$base_id." and phone='".$phone."'"),OCI_DEFAULT);
	}
	//ставим статус БД
	
	OCICommit($c);
	echo "^1^";
	$phone=func_next_phone($project_id,$base_id,$nedoz_interval,$nedoz_count);
	echo $phone;
	if($phone=='') $base_id='';
	
}
//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

//проверка статуса проекта
if($project_id=='') exit();
if($project_status=='Приостановлен') {
	echo "<font color=orange>Проект приостановлен!</font><hr>";
	echo "<input type=button value='Обновить' onclick=frm.submit()>";
	//обновляем текущий проект оператора
	$project_id='';
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$project_id."' where id=".$user_id));
	exit();
}
if($project_status=='quote_full') {
	echo "<font color=green>Достигнута общая квота по проекту!</font><hr>"; 
	echo "<input type=button value='Обновить' onclick=frm.submit()>";
	//обновляем текущий проект оператора
	$project_id='';
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$project_id."' where id=".$user_id));
	exit();
}
if($project_status=='Закрыт') {echo "<font color=red>Проект закрыт!</font>"; exit();}

//обновляем текущий проект оператора
OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$project_id."' where id=".$user_id));

//если в проекте нет исходных полей
if($num_src_fields==0) {
	echo "<font color=red>В проекте нет исходных полей</font>";
	echo "<hr><br>";
	func_show_call_buttons('no_src_fields');
	exit();
}
//

//выбор квот ====================================================
//echo "<td align=left style='background:none;border:0'>";
echo "<nobr>Квоты:";
//список квотируемых полей
echo "<select name=quote_id onchange='frm.change.click()'><option value=auto>Авто (вып:".$norm_count."/".$quote.";новых:".$new_count.";недоз:".$nedoz_count.";перез:".$perez_count.";в работе:".$inwork_count.")</option>";

$q=OCIParse($c,"select f.id,f.text_name from STC_FIELDS f
where f.project_id=".$project_id." and f.src_type_id=1 and f.quoted is not null and f.deleted is null 
order by f.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}
//если есть квотируемые исходные поля
if(isset($quoted_fields)) {
	$q_quotes=OCIParse($c,"select q.id,q.src_new,q.src_norm,q.src_quote,q.perez,q.nedoz,q.inwork,
	q.src_quote-q.src_norm estimate,
	decode(q.src_quote,0,'100%',decode(q.src_quote,NULL,NULL,round(q.src_norm/q.src_quote*100,0)||'%')) percent,
	q.lock_by_index 
	from STC_SRC_QUOTES q
	where q.project_id=".$project_id."
	--and q.lock_by_index is null --не показывать заблокированные по независимым
	--and q.src_quote-q.src_norm<=0 --не показывать выполненные
");
	$q_idx=OCIParse($c,"select i.value from STC_SRC_QUOTE_INDEXES qi, Stc_Src_Indexes i
	where qi.project_id=".$project_id." and qi.quote_id=:quote_id
	and i.project_id=".$project_id." 
	and i.id=qi.index_id
	and i.field_id=:field_id");
	
	OCIExecute($q_quotes);
	while(OCIFetch($q_quotes)) {
		$color=''; $disabled='';
		$quote=OCIResult($q_quotes,"SRC_QUOTE");
		$qid=OCIResult($q_quotes,"ID");
		$new_count=OCIResult($q_quotes,"SRC_NEW");
		$norm_count=OCIResult($q_quotes,"SRC_NORM");
		$nedoz_count=OCIResult($q_quotes,"NEDOZ");
		$perez_count=OCIResult($q_quotes,"PEREZ");
		$inwork_count=OCIResult($q_quotes,"INWORK");
		$estim_count=OCIResult($q_quotes,"ESTIMATE");
		$percent_full=OCIResult($q_quotes,"PERCENT");
		if($estim_count<=0 and $estim_count!==NULL) {$color='green'; $disabled=' disabled';}
		elseif($new_count+$nedoz_count+$perez_count+$inwork_count<=0) {$color='red'; $disabled=' disabled';}
		OCIBindByName($q_idx,":quote_id",$qid);
		$i=0; foreach($quoted_fields as $field_id => $field_name) {$i++;
			OCIBindByName($q_idx,":field_id",$field_id);
			OCIExecute($q_idx);
			OCIFetch($q_idx);
			if($i==1) $quote_name=OCIResult($q_idx,"VALUE");
			else $quote_name.=" - ".OCIResult($q_idx,"VALUE");
		}
		$quote_name.=" (вып:".$norm_count."/".$quote.";новых:".$new_count.";недоз:".$nedoz_count.";перез:".$perez_count.";в работе:".$inwork_count.")";
		echo "<option style=color:".$color.$disabled." value=".$qid.($qid==$quote_id?' selected':'').">".$quote_name."</option>";
	}
	
}
echo "</select>";
//echo "</td>";
//конец выбора квот =======================================================

//выбор по статусам =======================================================
//echo "<td align=left style='background:none;border:0'>";
echo "<nobr>Статусы:";
echo "<select name=status_type onchange='frm.change.click()'>";
echo "<option value=auto>Авто</option>";
echo "<option value=auto_nedoz".($status_type=='auto_nedoz'?' selected':NULL).">Авто. Недозвоны/Перезвоны</option>";
echo "<option value=my_perez".($status_type=='my_perez'?' selected':NULL).">Свои перезвоны</option>";
if($perez_policy=='pub')
	echo "<option value=all_perez".($status_type=='all_perez'?' selected':NULL).">Все перезвоны</option>";
echo "<option value=my_inwork".($status_type=='my_inwork'?' selected':NULL).">Свои незавершенные</option>";
if($perez_policy=='pub')
	echo "<option value=all_inwork".($status_type=='all_inwork'?' selected':NULL).">Все незавершенные</option>";
echo "</select>";
//echo "</td>";
//конец выбора по статусам ================================================

//echo "</tr></table>";
echo "<hr>";

//Авто. новые записи, недозвоны ============================================================
if($base_id=='' and substr($status_type,0,4)=='auto') {
	//разблокировка записей
	if(!isset($change)) func_unlock();
	
	if($from_time=='00:00' and $to_time=='00:00') $sql_time_limit='';
	else $sql_time_limit="and to_char(sysdate+nvl(b.utc_msk,0)/24,'HH24:MI') between '".$from_time."' and '".$to_time."' --время из настроек проекта";

	if($perez_policy=='pub') //политика перезвонов: общие перезвоны, пользователи, не активные в системе по данному проекту
		$sql_perez_user="(b.status_user=".$user_id." 
		or b.status_user not in (select id from STC_USERS where last_oper_prj_id=".$project_id." and last_logout<=last_activity and last_activity>=sysdate-5/1440))";
	if($perez_policy=='priv') //политика перезвонов: частные перезвоны
		$sql_perez_user="b.status_user=".$user_id;

	if($base_id=='' and $status_type=='auto') {
		mt_rand(1,100)<=$nedoz_chance?$nedoz_ord=0:$nedoz_ord=2; //рандом подачи недозвона (из настроек проекта)
		
		//echo "|^$nedoz_chance^$nedoz_ord^|";
		$sql="
update STC_BASE b set b.lock_user=".$user_id.", b.lock_date=sysdate where b.project_id=".$project_id." and id=
(select * from (
select b.id
from STC_BASE b, STC_SRC_QUOTES q
where b.project_id=".$project_id." and b.allow='y' and b.lock_by_index is null 
and (b.lock_user=".$user_id." or b.lock_date is null or b.lock_date<sysdate-5/1440) --проверка блокировки
--отсеиваем выполненные квоты по исходным
and q.project_id(+)=".$project_id." and q.src_quote(+)-q.src_norm(+)<=0 and b.src_quote_id=q.id(+) and q.id is null
and (
(
--свои перезвоны и недозвоны по перезвонам
(b.status='perez' or (b.status='nedoz' and b.status_date<=sysdate+".$nedoz_interval."/1440)) --интервал недозвонов из настроек проета 
and ".$sql_perez_user." and b.perez_date_msk<=sysdate
".$sql_time_limit."
)
or
(
--новые записи, недозвоны
(b.status is null or (b.status='nedoz' and b.status_date<=sysdate-".$nedoz_interval."/1440))
--если выбрана конкретная квота
and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
".$sql_time_limit." 
)
)
order by b.perez_date_msk, decode(status,'nedoz',".$nedoz_ord.",null,1), nvl(b.utc_msk,0) desc, b.status_date
)
where rownum=1)
returning id into :base_id";
	}

	if($status_type=='auto_nedoz') {
		$sql="
update STC_BASE b set b.lock_user=".$user_id.", b.lock_date=sysdate where b.project_id=".$project_id." and id=
(select * from (
select b.id
from STC_BASE b, STC_SRC_QUOTES q
where b.project_id=".$project_id." and b.allow='y' and b.lock_by_index is null 
and (b.lock_user=".$user_id." or b.lock_date is null or b.lock_date<sysdate-5/1440) --проверка блокировки
--отсеиваем выполненные квоты по исходным
and q.project_id(+)=".$project_id." and q.src_quote(+)-q.src_norm(+)<=0 and b.src_quote_id=q.id(+) and q.id is null
and (
(
--свои перезвоны и недозвоны по перезвонам
(b.status='perez' or (b.status='nedoz' and b.status_date<=sysdate+".$nedoz_interval."/1440)) --интервал недозвонов из настроек проета 
and ".$sql_perez_user." and b.perez_date_msk<=sysdate
".$sql_time_limit."
)
or
(
--недозвоны
b.status = 'nedoz' and b.perez_date_msk is null and b.status_date<=sysdate-".$nedoz_interval."/1440
--если выбрана конкретная квота
and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
".$sql_time_limit." 
)
)
order by b.perez_date_msk,nvl(b.utc_msk,0) desc, b.status_date
)
where rownum=1)
returning id into :base_id";
	}
	
	$base_id='';
	//блокировка одновременных запросов
	OCIExecute(OCIParse($c,"update STC_X set X='x'"),OCI_DEFAULT);
	$upd=OCIParse($c,$sql);
	OCIBindByName($upd,":base_id",$base_id,16);
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
	$_SESSION['survey']['base_id']=$base_id;
	if($base_id=='') {
		echo "<font color=red>Нет записей, подходящих для обзвона в данное время</font><hr>";
		echo "<input type=button value='Обновить' onclick=frm.submit()>";
	}
	echo "басе ИД".$base_id;
}
//конец новых записей, недозвонов======================================================

echo "<input type=text name=base_id value='".$base_id."'>";

//если выбрана запись БД
if($base_id<>'') {

	//если в проекте нет поля телефон
	if($num_phone_fields==0) {
		echo "<font color=red>В проекте нет исходного поля \"Телефон\"</font>";
		func_show_call_buttons('no_phones');
	}
	else {
		if($phone=='') {//Если не выбран телефон, отображаем следующий телефон со статусом, предполагающим обзвон (сначала inwork, потом перезвон, потом новый, потом недозвон)
		echo "^2^";
		$phone=func_next_phone($project_id,$base_id,$nedoz_interval,$nedoz_count);
		
		/*$q=OCIParse($c, "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t
where project_id=".$project_id." and base_id=".$base_id." and allow='y' 
and (
status is null or status='inwork' 
or (status='perez' and nvl(perez_date_msk,sysdate)<=sysdate) 
or (status='nedoz' and status_date<=sysdate+".$nedoz_interval."/1440 and nvl(nedoz_count,0)<".$nedoz_count.")
)
order by decode(status,'inwork',1,'perez',2,null,3,'nedoz',4), perez_date_msk, ord");
		}
		else {*/
		}
		if($phone<>'') {//если вбран телефон, то отображаем конкретный телефон
			$q=OCIParse($c, "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t 
			where project_id=".$project_id." and base_id=".$base_id." and phone=".$phone." and allow='y'");
		//}
		
			OCIExecute($q, OCI_DEFAULT);
			OCIFetch($q);
			$phone=OCIResult($q,"PHONE");
			echo $phone;
			echo "<input type=text name=phone value='".$phone."'>";
			func_show_call_buttons('with_phone');
		}
		else {
			echo "<font color=red>Нет номера телефона</font>";
			func_show_call_buttons('blank_phone');
		}
	}
}

echo "</form>";

function func_show_call_buttons($type) {//функция отображения кнопок дозвона
/*	balnk_phone - в проекте есть поле "телефон", но нет ни одного телефона
	with_phone - телефон выбран
	no_phones - в проекте нет поля "телефон"
	no_src_fields - в проекте нет исходных полей
*/
	if($type=='no_src_fields') $star_buttom_value='Создать запись и начать опрос';
	elseif($type=='no_phones' or $type=='blank_phone') $star_buttom_value='Начать опрос без телефона';
	else $star_buttom_value='Дозвон/Начать опрос';
	echo "<hr><nobr>";
	echo "<input type=button style='background:green' value='".$star_buttom_value."' onclick=frm.set_status.value='inwork';frm.submit()>";
	if($type=='with_phone')
		echo "<input type=button style='background:orange' value='Недозвон' onclick=frm.set_status.value='nedoz';frm.submit()>";
	if($type=='with_phone')
		echo "<input type=button style='background:orange' value='Перезвон' onclick=frm.set_status.value='perez';frm.submit()>";
	if($type=='with_phone' or $type=='blank_phone' or $type=='no_phones')	
		echo "<input type=button style='background:red' value='Отказ' onclick=frm.set_status.value='otkaz';frm.submit()>";
		
	echo "<input type=button style='background:red' value='Ошибка' onclick=frm.set_status.value='error';frm.submit()>";		
}

function func_next_phone($project_id,$base_id,$nedoz_interval,$nedoz_count) {
	global $c;
	$phone='';
	$q=OCIParse($c, "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t
where project_id=".$project_id." and base_id=".$base_id." and allow='y' 
and (
status is null or status='inwork' 
or (status='perez' and nvl(perez_date_msk,sysdate)<=sysdate) 
or (status='nedoz' and status_date<=sysdate-".$nedoz_interval."/1440 and nvl(nedoz_count,0)<".$nedoz_count.")
)
order by decode(status,'inwork',1,'perez',2,null,3,'nedoz',4), perez_date_msk, ord");
/*echo "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t
where project_id=".$project_id." and base_id=".$base_id." and allow='y' 
and (
status is null or status='inwork' 
or (status='perez' and nvl(perez_date_msk,sysdate)<=sysdate) 
or (status='nedoz' and status_date<=sysdate+".$nedoz_interval."/1440 and nvl(nedoz_count,0)<".$nedoz_count.")
)
order by decode(status,'inwork',1,'perez',2,null,3,'nedoz',4), perez_date_msk, ord";*/
	OCIExecute($q, OCI_DEFAULT);
	if(OCIFetch($q)) $phone=OCIResult($q,"PHONE");
	return $phone;
}
function func_unlock() {
	global $c;
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
}
?>
