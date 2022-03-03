<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
extract($_POST);
include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");

//декларирование переменных
if(isset($project_id)) {
	$_SESSION['adm']['project']['id']=$project_id;
	if(!isset($norefresh)) echo "<script>parent.admBottomFrame.location=parent.admBottomFrame.location.href;</script>";
}
else if(!isset($_SESSION['adm']['project']['id'])) $_SESSION['adm']['project']['id']='';

isset($_SESSION['adm']['load_id'])?NULL:$_SESSION['adm']['load_id']='';

//


echo "<form name=frm method=post>";

echo "<table width='100%'><tr><td align=left valign=top style='background:none;border:0'>";
//выбор проекта

if($_SESSION['adm']['project']['id']<>'') {

if($_SESSION['user']['all_projects']=='y') $where_prj=''; 
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

$q=OCIParse($c,"select p.id,p.name,to_char(p.create_date,'YYYY.MM.DD') create_date, p.uniq_term, p.status,p.SRC_QUOTE_BROKEN,p.QST_QUOTE_BROKEN,p.QST_STAT_BROKEN 
from STC_PROJECTS p
where p.id=".$_SESSION['adm']['project']['id']."
".$where_prj."
order by p.name, p.create_date");
OCIExecute($q);

//для безопасности: прерменная $_SESSION['adm']['project']['id'] установится только в случае, если пользователь имет доступ к проекту
$project_id=$_SESSION['adm']['project']['id'];
$_SESSION['adm']['project']['id']='';
$_SESSION['adm']['project']['name']='';
$_SESSION['adm']['project']['create_date']='';
$_SESSION['adm']['project']['uniq_term']='';
$_SESSION['adm']['project']['status']='';
$_SESSION['adm']['project']['src_quote_broken']='';
$_SESSION['adm']['project']['qst_quote_broken']='';
$_SESSION['adm']['project']['qst_stat_broken']='';

//echo "<nobr><select name=project_id onchange='frm.submit()'>
//<option value=''>ВЫБЕРИТЕ ПРОЕКТ</option>";
//while (OCIFetch($q)) {
if(OCIFetch($q)) {
	//$selected='';
	//if(OCIResult($q,"ID")==$project_id) {
	//	$selected=' selected';
		$_SESSION['adm']['project']['id']=OCIResult($q,"ID");
		$_SESSION['adm']['project']['name']=OCIResult($q,"NAME");
		$_SESSION['adm']['project']['create_date']=OCIResult($q,"CREATE_DATE");
		$_SESSION['adm']['project']['uniq_term']=OCIResult($q,"UNIQ_TERM");
		$_SESSION['adm']['project']['status']=OCIResult($q,"STATUS");
		$_SESSION['adm']['project']['src_quote_broken']=OCIResult($q,"SRC_QUOTE_BROKEN");
		$_SESSION['adm']['project']['qst_quote_broken']=OCIResult($q,"QST_QUOTE_BROKEN");
		$_SESSION['adm']['project']['qst_stat_broken']=OCIResult($q,"QST_STAT_BROKEN");	
		
		echo "<nobr><font size=3><b>".$_SESSION['adm']['project']['name']."</b></font>";	
	//}
	//echo "<option value='".OCIResult($q,"ID")."'".$selected.">".OCIResult($q,"NAME")." (".OCIResult($q,"CREATE_DATE").")</option>";
} else echo "<font color=red>доступ к проекту запрещен!</font>";

//echo "</select>";
}
//echo "<input type=submit name=ok value=ok style='display:none'>";
echo " | ";
if($_SESSION['user']['rw_projects']<>'') echo "<a href='adm.projects.php' target='admBottomFrame'>Проекты</a> | ";
if($_SESSION['user']['rw_project']<>'' and $_SESSION['adm']['project']['id']<>'') echo "<a href='adm.project.settings.php' target='admBottomFrame'>Настройки проекта</a> | ";
if($_SESSION['user']['rw_users']<>'' or $_SESSION['user']['rw_opers']<>'') echo "<a href='adm.users.frame.php' target='admBottomFrame'>Пользователи</a> | ";
if($_SESSION['user']['rw_src_bd']<>'' and $_SESSION['adm']['project']['id']<>'') echo "<a href='adm.loads.loads.php' target='admBottomFrame'>Исх. БД</a> | ";
if($_SESSION['user']['rw_ank']<>'' and $_SESSION['adm']['project']['id']<>'') echo "<a href='adm.ank_edit.frame.php' target='admBottomFrame'>Анкета</a> | ";

if($_SESSION['user']['rw_quote']<>'' and $_SESSION['adm']['project']['id']<>'') echo "<a href='adm.quotes.php' target='admBottomFrame'>Квоты</a> | ";
if($_SESSION['adm']['project']['id']<>'') echo "<a href='adm.stat.status.php' target='admBottomFrame'>Статистика</a> | ";
if($_SESSION['user']['rw_report']<>'' and $_SESSION['adm']['project']['id']<>'') echo "<a href='adm.report.test.php' target='admBottomFrame'>Отчеты</a> | ";
if($_SESSION['user']['operator']=='y') echo "<a href='survey.call.frame.php' target='admBottomFrame'>Начать опрос</a> | ";
if($_SESSION['user']['rw_tools']<>'') echo "<a href='http://sc/local/phone_conv.php' target='admBottomFrame'>Инструменты</a> | ";
echo "<font align=right><a href='help.adm.main.html' target='_blank'>Справка</a></font>";
echo "</td>";
echo "<td align=right style='background:none;border:0'>";

echo "<td align=right style='background:none;border:0'>";
echo "<nobr>";
echo $_SESSION['user']['fio']." | ";
echo $_SESSION['user']['role_name']." | ";
echo "<a href='login.php?exit'><font color=red>Выход</font></a>";

echo "</td>";
echo "</tr></table>";

if($_SESSION['adm']['project']['id']<>'') {

	if($_SESSION['adm']['project']['status']=='Активен') echo "<font color=green>";
	if($_SESSION['adm']['project']['status']=='Приостановлен') echo "<font color=orange>";
	if($_SESSION['adm']['project']['status']=='Закрыт') echo "<font color=red>";
	$q=OCIParse($c,"select count(*) cnt from STC_USERS where last_oper_prj_id=".$_SESSION['adm']['project']['id']." and last_logout<=last_activity and last_activity>=sysdate-5/1440");
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	echo $_SESSION['adm']['project']['status']."</font> (активных операторов: <b>".OCIResult($q,"CNT")."</b>)";

	if($_SESSION['adm']['project']['src_quote_broken']<>'') {
		echo " <font color=red>Изменены квоты по исходным полям!</font> ";
	}
	if($_SESSION['adm']['project']['qst_quote_broken']<>'') {
		echo " <font color=red>Изменены квоты по вопросам!</font> ";
	}
	if($_SESSION['adm']['project']['qst_stat_broken']<>'') {
		echo " <font color=red>Нарушена статистика по квотам!</font> ";
	}
}
echo "<form>";
?>
</form>
</body>
</html>
