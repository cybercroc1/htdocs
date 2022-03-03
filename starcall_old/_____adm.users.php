<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
extract($_REQUEST);
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['rw_users']=='' and $_SESSION['user']['rw_opers']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

if(!isset($order_by) and !isset($_SESSION['adm']['users']['order_by'])) $_SESSION['adm']['users']['order_by']='fio';
if(isset($order_by)) $_SESSION['adm']['users']['order_by']=$order_by;

if(!isset($new_role) and !isset($_SESSION['adm']['users']['new_role'])) $_SESSION['adm']['users']['new_role']='operator';
if(isset($new_role)) $_SESSION['adm']['users']['new_role']=$new_role;

if(!isset($group_id)) $group_id='';
$group_name='';

echo "<form name=frm_projects method=post>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

echo "<script>parent.parent.logFrame.location='blank_page.php';</script>";

//сохранение
if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w') { 
if (isset($add_project)) {
	$q=OCIParse($c,"select count(*) count from STC_PROJECTS where trim(upper(name))=trim(upper('".$new_project_name."')) and trunc(create_date)=trunc(sysdate)");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo "<b><font color=red>ОШИБКА! Проект с именем \"".$new_project_name."\" уже существует</font><br>";}
	else {
	$ins=OCIParse($c,"insert into STC_PROJECTS (id,name,create_date,status,creator) 
	values (SEQ_STC_PROJECT_ID.nextval,'".$new_project_name."',sysdate,'Приостановлен',".$_SESSION['user']['id'].") returning id into :new_project_id");
	$new_project_id='';
	OCIBindByName($ins,':new_project_id',$new_project_id,256);
	OCIExecute($ins,OCI_DEFAULT);
	//добавляем проект в группы пользователя
	OCIExecute(OCIParse($c,"insert into STC_USER_GRP_PRJ select ".$new_project_id.",group_id from STC_USER_GRP_USR where user_id=".$_SESSION['user']['id']));
	OCICommit($c);
	$order_by='p.create_date desc'; $_SESSION['adm']['projects']['order_by']=$order_by; //если добавлнен проект, то сортируем по дате
	$_SESSION['adm']['project']['id']=$new_project_id;
	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
}}
if((isset($close) or isset($pause) or isset($open)) and isset($mark)) {
	isset($close)?$set_status='Закрыт':NULL;
	isset($pause)?$set_status='Приостановлен':NULL;
	isset($open)?$set_status='Активен':NULL;
	$q=OCIParse($c,"select name,SRC_QUOTE_BROKEN,QST_QUOTE_BROKEN from STC_PROJECTS where id=:id");
	$upd=OCIParse($c,"update STC_PROJECTS set status='".$set_status."', status_date=sysdate where id=:id");
	foreach($mark as $id) {
		OCIBindByName($q,":id",$id);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'' or OCIResult($q,"QST_QUOTE_BROKEN")<>'' and $set_status=='Активен') {
			echo "<font color=red><b>ОШИБКА: Нельзя активировать проект \"".OCIResult($q,"NAME")."\". Необходимо перестроить квоты.</b></font><br>";
			continue;
		}
		OCIBindByName($upd,":id",$id);
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//unset($_SESSION['adm']['project']);
	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
}
}

if($group_id<>'') {
	$q=OCIParse($c,"select name from STC_USER_GROUP where id=".$group_id);
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	$group_name=OCIResult($q,"NAME");
	$where_grp='and gu.group_id='.$group_id;
}
else $where_grp='';

echo "<font size=4>Пользователи. ";
if($group_id<>'') {
	echo "Группа: \"".$group_name."\""; 
	
}
else echo "Все пользователи";
echo "</font>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
	
echo "<table id=tbl>";
echo "<tr>
<td width=40></td><td align=center width=40><b>ID</b></td>
<td align=center><a href='adm.users.php?order_by=u1.fio'>".($_SESSION['adm']['users']['order_by']=='u1.fio'?'<b>':NULL)."ФИО</b></a></td>
<td align=center><a href='adm.users.php?order_by=u1.login'>".($_SESSION['adm']['users']['order_by']=='u1.login'?'<b>':NULL)."Логин</b></a></td>
<td align=center><a href='adm.users.php?order_by=u1.login'><b>Пароль</b></a></td>
<td align=center><a href='adm.users.php?order_by=role_name'>".($_SESSION['adm']['users']['order_by']=='role_name'?'<b>':NULL)."Роль</b></a></td>
<td align=center><a href='adm.users.php?order_by=u1.create_date desc'>".($_SESSION['adm']['users']['order_by']=='u1.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
<td align=center><a href='adm.users.php?order_by=creator_fio'>".($_SESSION['adm']['users']['order_by']=='creator_fio'?'<b>':NULL)."Создатель</b></a></td>";
echo "</tr>";

//список ролей для создания пользователя
//все пользователи
if($_SESSION['user']['all_users']=='y') $where_role='';
//только операторы
else if($_SESSION['user']['rw_users']=='' and $_SESSION['user']['rw_opers']<>'') {
	$where_role=" and id='operator'";
	$where_role2=" and u1.role_id='operator'";
}
else {
	$where_role=" and all_users is null and all_projects is null";
	$where_role2="";
}

//список ролей для создания пользователя
$q=OCIParse($c,"select id, name from STC_LI_ROLES where id<>'root'".$where_role);
OCIExecute($q, OCI_DEFAULT);
$i=0; while(OCIFetch($q)) {$i++;
	$role_ids[$i]=OCIResult($q,"ID");
	$role_names[$i]=OCIResult($q,"NAME");
}
//
	
//Добавить пользователя
if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w') {
	echo "<tr>
	<td style='background-color:green' colspan=3><input type=text name=new_fio onkeyup=ch_new()></td>
	<td style='background-color:green'><input type=text name=new_login onkeyup=ch_new()></td>
	<td style='background-color:green'><input type=text name=new_pass onkeyup=ch_new()></td>";
	echo "<td style='background-color:green'>";
	echo "<select name=new_role>";
	foreach($role_ids as $key => $val) {
		echo "<option value='".$val."'".($_SESSION['adm']['users']['new_role']==$val?" selected":NULL).">".$role_names[$key]."</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td style='background-color:green' colspan=3><input type=submit name=add_user disabled value=\"Создать пользователя\"></td>";
	echo "</tr>";
}
//

//Список пользователей ВСЕ ПОЛЬЗОВАТЕЛИ
if($_SESSION['user']['all_users']=='y') { //все пользователи
	$q=OCIParse($c,"select u1.id,u1.fio,u1.login,u1.pass,r.id role_id, r.name role_name, 
	to_char(u1.create_date,'DD.MM.YYYY HH24:MI') create_date,
	u1.creator creator_id, u2.fio creator_fio  
	from STC_USERS u1, STC_LI_ROLES r, STC_USERS u2
	where u1.deleted is null
	and r.id=u1.role_id and u2.id=u1.creator
	order by ".$_SESSION['adm']['users']['order_by']);	

	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$usr_ids[$i]=OCIResult($q,"ID");
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		echo "<td><input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'></input></td>
		<td><b>".OCIResult($q,"ID")."</b></td>
		<td><b>".OCIResult($q,"FIO")."</b></td>
		<td><b>".OCIResult($q,"LOGIN")."</b></td>
		<td><b>".OCIResult($q,"PASS")."</b></td>
		<td><b>".OCIResult($q,"ROLE_NAME")."</b></td>
		<td><b>".OCIResult($q,"CREATE_DATE")."</b></td>
		<td><b>".OCIResult($q,"CREATOR_FIO")."</b></td>";
		echo "</tr>";
	}	
} 
else {//свои пользователи и потомки (для редактирования)
	$q=OCIParse($c,"select u1.id,u1.fio,u1.login,u1.pass,r.id role_id, r.name role_name, 
	to_char(u1.create_date,'DD.MM.YYYY HH24:MI') create_date,
	u1.creator creator_id, u2.fio creator_fio  
	from STC_USERS u1, STC_LI_ROLES r, STC_USERS u2
	where u1.deleted is null
	and r.id=u1.role_id and u2.id=u1.creator
	and u1.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
	order by ".$_SESSION['adm']['users']['order_by']);
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$usr_ids[$i]=OCIResult($q,"ID");
		if($i==1) echo "<tr><td colspan=8 style='background-color:#E5E5E5'><b>Мои пользователи</b></td>";
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		echo "<td><input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'></input></td>
		<td><b>".OCIResult($q,"ID")."</b></td>
		<td><b>".OCIResult($q,"FIO")."</b></td>
		<td><b>".OCIResult($q,"LOGIN")."</b></td>
		<td><b>".OCIResult($q,"PASS")."</b></td>
		<td><b>".OCIResult($q,"ROLE_NAME")."</b></td>
		<td><b>".OCIResult($q,"CREATE_DATE")."</b></td>
		<td><b>".OCIResult($q,"CREATOR_FIO")."</b></td>";
		echo "</tr>";
	}		
	//пересекающиеся по группам со мной и потомками (только для чтения)
	if(isset($usr_ids)) {
		$usr_ids=implode(',',$usr_ids);
		$where_usr="and u1.id not in (".$usr_ids.")";
	}
	else {
		$where_usr="";
	}
	$q=OCIParse($c,"select u1.id,u1.fio,u1.login,u1.pass,r.id role_id, r.name role_name, 
	to_char(u1.create_date,'DD.MM.YYYY HH24:MI') create_date,
	u1.creator creator_id, u2.fio creator_fio  
	from STC_USERS u1, STC_LI_ROLES r, STC_USERS u2
	where u1.deleted is null ".$where_role2."
	and r.id=u1.role_id and u2.id=u1.creator
	and u1.id in (select gu.user_id from STC_USER_GRP_USR gu 
	where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
	".$where_grp."
	)
	".$where_usr."
	order by ".$_SESSION['adm']['users']['order_by']);
	
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$usr_ids[$i]=OCIResult($q,"ID");
		if($i==1) echo "<tr><td colspan=8 style='background-color:#E5E5E5'><b>Пользователи</b></td>";
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		echo "<td><input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'></input></td>
		<td><b>".OCIResult($q,"ID")."</b></td>
		<td><b>".OCIResult($q,"FIO")."</b></td>
		<td><b>".OCIResult($q,"LOGIN")."</b></td>
		<td><b>".OCIResult($q,"PASS")."</b></td>
		<td><b>".OCIResult($q,"ROLE_NAME")."</b></td>
		<td><b>".OCIResult($q,"CREATE_DATE")."</b></td>
		<td><b>".OCIResult($q,"CREATOR_FIO")."</b></td>";
		echo "</tr>";
	}			
	
}
echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";

if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']<>'w')  echo "<font color=red>Редактирование запрещено!</font>";
else {
	echo "<input type=submit name=save value='Сохранить'></input> ";
}
//
echo "</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
<script language="javascript">
function ch_new() {
	if (document.all.new_fio.value=='' || document.all.new_login.value=='' || document.all.new_pass.value=='') {
	document.all.add_user.disabled=true;
	} else {
	document.all.add_user.disabled=false;
}}
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
