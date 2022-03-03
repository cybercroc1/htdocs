<?php include("starcall/session.cfg.php");
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<script>
var timerID='';
function find_timer() {
	if(frm_projects.find_string.value.length==0 || frm_projects.find_string.value.length>=3) {clearTimeout(timerID); timerID=setTimeout('frm_projects.submit()',1500);}
}
function sel_project(id) {
	frm_sel_project.project_id.value=id;
	frm_sel_project.submit();
}
</script>
</head>
<body>
<?php
include("starcall/conn_string.cfg.php");
if($_SESSION['user']['operator']<>'y') {echo "<font color=red>Access DENY!</font>"; exit();}

if(isset($_SESSION['survey']['project']['id'])) $project_id=$_SESSION['survey']['project']['id']; else $project_id='';

//разблокировка записей, очистка переменных опроса
OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
OCIExecute(OCIParse($c,"update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));	
if(isset($_SESSION['survey'])) {
	unset($_SESSION['survey']);
	$_SESSION['survey']['project']['id']=$project_id;
}	
//

extract($_REQUEST);

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr class=header_tr><td>";

echo "<form name=frm_projects method=post>";

if(!isset($order_by) and !isset($_SESSION['oper']['projects']['order_by'])) $_SESSION['oper']['projects']['order_by']='name';
if(isset($order_by)) $_SESSION['oper']['projects']['order_by']=$order_by;

if(!isset($find_string) and !isset($_SESSION['oper']['projects']['find_string'])) $_SESSION['oper']['projects']['find_string']='';
if(isset($find_string)) $_SESSION['oper']['projects']['find_string']=$find_string;

echo "<font size=4>Выбор проекта</font>";
echo " | ПОИСК:<input type=text name=find_string value='".$_SESSION['oper']['projects']['find_string']."' onkeyup=find_timer(); onpaste=find_timer(); onchange=frm_projects.submit();></input>";
echo " | <input type=submit value='Обновить'>";	 
echo "<script>
frm_projects.find_string.focus();
frm_projects.find_string.selectionStart = frm_projects.find_string.value.length;
</script>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
	
	echo "<table id=tbl class=white_table>
	<tr>
	<td align=center><b>ID</b></td>
	<td align=center><a href='survey.projects.php?order_by=p.name'>".($_SESSION['oper']['projects']['order_by']=='p.name'?'<b>':NULL)."Название</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.create_date desc'>".($_SESSION['oper']['projects']['order_by']=='p.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.creator'>".($_SESSION['oper']['projects']['order_by']=='p.creator'?'<b>':NULL)."Создатель</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.status,p.name'>".($_SESSION['oper']['projects']['order_by']=='p.status,p.name'?'<b>':NULL)."Статус</b></a></td>
	
	<td align=center>Квота</td>
	<td align=center>Выполнено</td>
	<td align=center>Новых</td>
	<td align=center>В работе</td>
	<td align=center>Перезвонов</td>
	<td align=center>Недозвонов</td>
	";

	echo "</tr>";
	
	//Список проектов
	
if($_SESSION['user']['operator_only']=='y') 
$where_prj="and (p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id=".$_SESSION['user']['id']." and gp.group_id=gu.group_id))";
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

$q=OCIParse($c,"select p.id,p.name,to_char(p.create_date,'YYYY.MM.DD') create_date,p.status,decode(p.status,'Активен','green','Приостановлен','orange','Закрыт','red') color,
u.fio creator, 
p.from_time, p.to_time, p.nedoz_interval,
p.quote,p.stat_new,p.stat_end_norm,p.stat_inwork,p.stat_nedoz,p.stat_perez, p.quote-p.stat_end_norm estimate,
decode(p.quote,0,'100%',decode(p.quote,NULL,NULL,round(p.stat_end_norm/p.quote*100,0)||'%')) percent,
case when p.quote-p.stat_end_norm<=0 then 'quote_full' else p.status end status, 
p.num_src_fields, p.num_phone_fields, p.perez_policy, p.nedoz_chance
from STC_PROJECTS p, STC_USERS u
where p.status<>'Закрыт' and u.id=p.creator
".$where_prj."
".($_SESSION['oper']['projects']['find_string']<>''?" and upper(replace(p.name,' ','')) like '%".(strtoupper(str_replace(" ","",$_SESSION['oper']['projects']['find_string'])))."%'":" and nvl(p.last_activity,p.create_date)>=add_months(sysdate,-1)")."
order by  ".$_SESSION['oper']['projects']['order_by']);

OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	OCIResult($q,"STATUS")=='Закрыт'?$tmp_class=' class=unselectable_row':$tmp_class='';
	OCIResult($q,"ID")==$project_id?$tmp_class=' class=selected_row':$tmp_class=' class=selectable_row';
	
	echo "<tr".$tmp_class.(OCIResult($q,"STATUS")<>'Закрыт'?" onclick='sel_project(".OCIResult($q,"ID").")'":NULL).">";
	echo "
	<td><b>".OCIResult($q,"ID")."</b></td>
	<td><b>".OCIResult($q,"NAME")."</b></td>
	<td><b>".OCIResult($q,"CREATE_DATE")."</b></td>
	<td><b>".OCIResult($q,"CREATOR")."</b></td>
	<td><font color=".OCIResult($q,"COLOR")."><b>".OCIResult($q,"STATUS")."</b></font></td>
	
	<td><b>".OCIResult($q,"QUOTE")."</b></td>
	<td><b>".OCIResult($q,"STAT_END_NORM")." (".OCIResult($q,"PERCENT").")</b></td>
	<td><b>".OCIResult($q,"STAT_NEW")."</b></td>
	<td><b>".OCIResult($q,"STAT_INWORK")."</b></td>
	<td><b>".OCIResult($q,"STAT_PEREZ")."</b></td>
	<td><b>".OCIResult($q,"STAT_NEDOZ")."</b></td>
	";
	echo "</tr>";
}
echo "</table>";

echo "</form>";	
echo "<form name=frm_sel_project method=post action='survey.main.menu.php' target='surveyMainTopFrame'>
<input type=hidden name=project_id>
</form>";	

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
</body></html>
