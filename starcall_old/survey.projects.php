<?php include("../../conf/starcall_conf/session.cfg.php");?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['operator']<>'y') {echo "<font color=red>Access DENY!</font>"; exit();}

if(isset($_SESSION['survey'])) {
	unset($_SESSION['survey']);
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
	OCIExecute(OCIParse($c,"update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));	
}	
//
?>
<script>
//ежеминутное обновление сессии
aj();
window.setInterval(aj,60000);
var timerID='';
function aj()
{
     if (window.XMLHttpRequest)
     {
          req = new XMLHttpRequest();
     }
     else
     {
          if (window.ActiveXObject)
          {
               try
               {
                    req = new ActiveXObject('Msxml2.XMLHTTP'); 
               }
			   catch (e) {}
               try
               {
                    req = new ActiveXObject('Microsoft.XMLHTTP');  
               }
			   catch(e) {}
          }
     }   
     req.open('GET', 'session.refresh.php', true);
	 //req.onreadystatechange = function() {setTimeout(aj, 5000)}
     req.send(null);
}
function find_timer() {
	if(frm_projects.find_string.value.length==0 || frm_projects.find_string.value.length>=3) {clearTimeout(timerID); timerID=setTimeout('frm_projects.submit()',1500);}
}
</script>
<?php
extract($_REQUEST);

echo "<form name=frm_projects method=post>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

/*if(!isset($order_by) and !isset($_SESSION['survey']['projects']['order_by'])) $_SESSION['survey']['projects']['order_by']='p.name';
if(isset($order_by)) $_SESSION['survey']['projects']['order_by']=$order_by;*/

if(!isset($order_by) and !isset($_SESSION['adm']['projects']['order_by'])) $_SESSION['adm']['projects']['order_by']='name';
if(isset($order_by)) $_SESSION['adm']['projects']['order_by']=$order_by;

if(!isset($find_string) and !isset($_SESSION['adm']['projects']['find_string'])) $_SESSION['adm']['projects']['find_string']='';
if(isset($find_string)) $_SESSION['adm']['projects']['find_string']=$find_string;

echo "<table width='100%'><tr><td align=left style='background:none;border:0'>";
echo "<font size=4>Проекты</font>";
echo " | ПОИСК:<input type=text name=find_string value='".$_SESSION['adm']['projects']['find_string']."' onkeyup=find_timer(); onpaste=find_timer(); onchange=frm_projects.submit();></input>";
echo "</td>";

echo "<script>
frm_projects.find_string.focus();
frm_projects.find_string.selectionStart = frm_projects.find_string.value.length;
</script>";

echo "<td align=right style='background:none;border:0'>";
if($_SESSION['user']['operator_only']=='y') {
	echo "<nobr>";
	echo $_SESSION['user']['fio']." | ";
	echo $_SESSION['user']['role_name']." | ";		
	echo "<a href='login.php?exit'><font color=red>Выход</font></a>";
} 
echo "</td>";
echo "</tr></table><hr>";	

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
	
	echo "<table id=tbl>
	<tr>
	<td align=center><b>ID</b></td>
	<td align=center><a href='survey.projects.php?order_by=p.name'>".($_SESSION['adm']['projects']['order_by']=='p.name'?'<b>':NULL)."Название</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.create_date desc'>".($_SESSION['adm']['projects']['order_by']=='p.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.creator'>".($_SESSION['adm']['projects']['order_by']=='p.creator'?'<b>':NULL)."Создатель</b></a></td>
	<td align=center><a href='survey.projects.php?order_by=p.status,p.name'>".($_SESSION['adm']['projects']['order_by']=='p.status,p.name'?'<b>':NULL)."Статус</b></a></td>
	
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
".($_SESSION['adm']['projects']['find_string']<>''?" and upper(replace(p.name,' ','')) like '%".(strtoupper(str_replace(" ","",$_SESSION['adm']['projects']['find_string'])))."%'":" and p.create_date>=add_months(sysdate,-1)")."
order by  ".$_SESSION['adm']['projects']['order_by']);
OCIExecute($q);	
	
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)' ".(OCIResult($q,"STATUS")<>'Закрыт'?"style='cursor:pointer' onclick='sel_project(".OCIResult($q,"ID").")'":NULL).">";
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
echo "<form name=frm_sel_project method=post action='survey.call.frame.php'>
<input type=hidden name=project_id value>
<input type=hidden name=change>
</form>";	

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
<script language="javascript">
function sel_project(id) {
	frm_sel_project.project_id.value=id;
	frm_sel_project.submit();
}
function sel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].style.background='#66FFFF';
}}
function unsel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].style.background='White';
}}
</script>
</body></html>
