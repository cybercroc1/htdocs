<?php include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_records']='n';
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body class="body_marign" onLoad="parent.surveyFrameset.rows=Math.round(document.getElementById('end_page').offsetTop)+8+',*'">
<?php

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("starcall/conn_string.cfg.php");

if(isset($_SESSION['survey']['src_quotes']['order_by'])) unset($_SESSION['survey']['src_quotes']['order_by']);

if(isset($_POST['project_id'])) $_SESSION['survey']['project']['id']=$_POST['project_id'];
elseif (!isset($_SESSION['survey']['project']['id'])) $_SESSION['survey']['project']['id']='';


//if($_SESSION['survey']['project']['id']=='' and isset($_SESSION['adm']['project']['id'])) $_SESSION['survey']['project']['id']=$_SESSION['adm']['project']['id'];

$project_name='';

//echo "<form name=frm>"; //эта форма только для измерения высоты содержимого


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

$q=OCIParse($c,"select p.id,p.name from STC_PROJECTS p
where status<>'Закрыт' and p.id='".$_SESSION['survey']['project']['id']."'
".$where_prj."
order by p.name, p.create_date");
OCIExecute($q);
//для безопасности: прерменная $_SESSION['survey']['project']['id'] установится заново после выборки только в случае, если пользователь имет доступ к проекту
$project_id=$_SESSION['survey']['project']['id'];
$_SESSION['survey']['project']['id']='';
if(OCIFetch($q)) {$_SESSION['survey']['project']['id']=OCIResult($q,"ID"); $project_name=OCIResult($q,"NAME");}
//===============================================================

echo "<table width='100%' name=tbl><tr><td align=left style='background:none;border:0'>";

echo "<nobr><font size=3><b>".$_SESSION['survey']['project']['id']." | ".$project_name."</b></font> | ";
echo "<a href='survey.projects.php' target=surveyMainBottomFrame><b>Выбор проекта</b></a> | ";
echo "</td>";
echo "<td align=right style='background:none;border:0'>";
//if($_SESSION['user']['operator_only']=='y') {
	echo "<nobr>";
	echo $_SESSION['user']['fio']." | ";
	echo $_SESSION['user']['role_name']." | ";		
	echo "<a href='login.php?exit' target=_parent><font color=red>Выход</font></a>";
//} 
echo "</td>";
echo "</tr></table>";
//echo "</form>";
echo "<div id=end_page></div>"; //низ документа (для определения высоты фрейма

if($_SESSION['survey']['project']['id']<>'') {
	echo "<script>parent.surveyMainBottomFrame.document.location='survey.call.frame.php';</script>";
}
else echo "<script>parent.surveyMainBottomFrame.document.location='survey.projects.php';</script>";


?>
</body>
</html>
