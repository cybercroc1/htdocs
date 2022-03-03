<?php include("../../conf/starcall_conf/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<script>
var new_idx=0;
var role_ids=new Array();
var role_names=new Array();
var default_role='operator';
</script>
<script src="func.row_select.js"></script>
<script src="adm.users.js"></script>
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

if(!isset($group_id) and !isset($_SESSION['adm']['users']['group_id'])) $_SESSION['adm']['users']['group_id']='';
if(isset($group_id)) $_SESSION['adm']['users']['group_id']=$group_id;
$group_id=$_SESSION['adm']['users']['group_id'];

$group_name='';

if(!isset($_SESSION['adm']['users']['added_user'])) $_SESSION['adm']['users']['added_user']='';

echo "<form name=frm method=post action='adm.users.save.php' target='logFrame'>";
echo "<input type=hidden name='group_id' value='".$group_id."'></input>";
//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

if($group_id<>'') {
	$q=OCIParse($c,"select name from STC_USER_GROUP where id=".$group_id);
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	$group_name=OCIResult($q,"NAME");
}

echo "<font size=4>Пользователи. ";
	if($group_id<>'') echo "Группа: \"<b>".$group_name."</b>\"";
echo "</font>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
	
echo "<table id=tbl>";
echo "<tr>";
if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w')
	echo "<td width=13 style='cursor:pointer' onclick=add_user(this) title='Создать пользователя'><img src=png/plus.png></img></td>";
else 
	echo "<td></td>";
echo "<td width=13></td><td align=center width=40><b>ID</b></td>
<td align=center><a href='adm.users.php?order_by=u1.fio'>".($_SESSION['adm']['users']['order_by']=='u1.fio'?'<b>':NULL)."ФИО</b></a></td>
<td align=center><a href='adm.users.php?order_by=u1.login'>".($_SESSION['adm']['users']['order_by']=='u1.login'?'<b>':NULL)."Логин</b></a></td>
<td align=center><b>Пароль</b></td>
<td align=center><a href='adm.users.php?order_by=role_level desc'>".($_SESSION['adm']['users']['order_by']=='role_level desc'?'<b>':NULL)."Роль</b></a></td>
<td align=center><a href='adm.users.php?order_by=u1.create_date desc'>".($_SESSION['adm']['users']['order_by']=='u1.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
<td align=center><a href='adm.users.php?order_by=creator_fio'>".($_SESSION['adm']['users']['order_by']=='creator_fio'?'<b>':NULL)."Создатель</b></a></td>";
echo "</tr>";

//список ролей для создания пользователя
if($_SESSION['user']['all_users']=='y') $where_role='';
else if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']=='w') $where_role=" and id='operator'";

else $where_role=" and all_users is null and all_projects is null";
$q=OCIParse($c,"select id, name from STC_LI_ROLES where id<>'root' and role_level<=".$_SESSION['user']['role_level']." ".$where_role);
OCIExecute($q, OCI_DEFAULT);
$i=0; while(OCIFetch($q)) {
	echo "<script>
		role_ids[".$i."]='".OCIResult($q,"ID")."';
		role_names[".$i."]='".OCIResult($q,"NAME")."';
	</script>";
$i++;
}
//
	
//Добавить пользователя
if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w') {
	//запрос для смены владельца
	$q_creators=OCIParse($c,"
	select distinct u.id,u.fio from STC_USER_CHILD c1,STC_USER_CHILD c2, STC_USERS u, STC_LI_ROLES r
	where (
		c1.child_user_id=".$_SESSION['user']['id']." --мои предки
		or
		c1.child_user_id=:user_id --его предки 
		or 
		c2.user_id=".$_SESSION['user']['id']." --мои потомки
		or
		c2.user_id=:user_id --его потомки
	)
	and u.id=c1.user_id and	u.id=c2.child_user_id
	and u.deleted is null
	and r.id=u.role_id
	and nvl(r.all_users,'n')<>'y'
	and (
		r.rw_users='w'
		or 
		r.rw_opers=(select decode(r.operator_only,'y','w',NULL) from STC_USERS u, STC_LI_ROLES r where r.id=u.role_id and u.id=:user_id /*он*/)
	)
	order by u.fio");
}

//если выбрана группа
if($group_id<>'') {
	$select_group="(select decode(count(*),0,NULL,'y') from STC_USER_GRP_USR where user_id=u1.id and group_id=".$group_id.") in_group --если выбрана группа";
	$tmp_order="in_group, --если выбрана группа";
}
//если не выбрана группа
else {
	//$where_grp="in (select g.group_id from STC_USER_CHILD c, STC_USER_GRP_USR g where c.user_id=".$_SESSION['user']['id']." and g.user_id=c.child_user_id) --все группы (grp_id=='')";
	$select_group="'' in_group --если не выбрана группа";
	$tmp_order='';
}
//только операторы
if($_SESSION['user']['rw_users']=='' and $_SESSION['user']['rw_opers']<>'') {
	$where_oper="and r.operator_only='y' --если только RW_OPERS";
}
//все роли
else {
	$where_oper="";
}

//Список пользователей ВСЕ ПОЛЬЗОВАТЕЛИ
if($_SESSION['user']['all_users']=='y') { //все пользователи
	$q=OCIParse($c,"--все пользователи (ALL_USERS)
	select u1.id,u1.fio,u1.login,u1.pass,u1.lost_creator,r.id role_id, r.name role_name, r.role_level, u1.create_date cd,
	to_char(u1.create_date,'DD.MM.YYYY HH24:MI') create_date,
	u1.creator creator_id, u2.fio creator_fio,
	(select decode(count(*),0,NULL,'y') from STC_USER_CHILD where child_user_id=u1.id and user_id=".$_SESSION['user']['id'].") my_child,	
	".$select_group."
	from STC_USERS u1, STC_LI_ROLES r, STC_USERS u2
	where u1.deleted is null and r.id=u1.role_id and u2.id=u1.creator
	and (
	u1.id=".$_SESSION['user']['id']." --Я 
	or (
	(r.id<>'root' or '".$_SESSION['user']['role_id']."'='root') 
	))
	order by 
	".$tmp_order."
	".$_SESSION['adm']['users']['order_by']);   	
} 
else {//свои пользователи, потомки и одногруппники
	$q=OCIParse($c,"--дети и одногруппники
	select u1.id,u1.fio,u1.login,u1.pass,u1.lost_creator,r.id role_id, r.name role_name, r.role_level, u1.create_date cd,
	to_char(u1.create_date,'DD.MM.YYYY HH24:MI') create_date,
	u1.creator creator_id, u2.fio creator_fio,
	(select decode(count(*),0,NULL,'y') from STC_USER_CHILD where child_user_id=u1.id and user_id=".$_SESSION['user']['id'].") my_child,
	".$select_group."
	from STC_USERS u1, STC_LI_ROLES r, STC_USERS u2
	where u1.deleted is null and r.id=u1.role_id and u2.id=u1.creator
	and (
	u1.id=".$_SESSION['user']['id']." --Я 
	or (
	r.id<>'root' 
	".$where_oper."
	and ((
	--мои потомки
	u1.id in
	(select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
	)
	or
	(
	--одногруппники мои и моих потомков
	u1.id in 
	(select gu.user_id from STC_USER_GRP_USR gu where gu.group_id 
	in (select g.group_id from STC_USER_CHILD c, STC_USER_GRP_USR g where c.user_id=".$_SESSION['user']['id']." and g.user_id=c.child_user_id))
	))))
	order by 
	".$tmp_order."
	".$_SESSION['adm']['users']['order_by']);
}//	
	
	OCIExecute($q,OCI_DEFAULT);
	if($group_id<>'') {
		$header_in_group="<tr data-in_group='y' data-type='head_in_group'><td colspan=9 style='background-color:#E5E5E5'>Пользователи группы: \"<b>".$group_name."</b>\"</td>";
		$header_no_group="<tr data-in_group='n' data-type='head_no_group'><td colspan=9 style='background-color:#E5E5E5'>Пользователи, не входящие в группу: ".$group_name."</td>";
	}
	
	$in_group='n';
	$i=1; $j=1; while (OCIFetch($q)) {
		//показываем выбранную группу
		if($group_id<>'') {
			if($i==1) {
				$in_group='y';
				$tmp_style="";
				echo $header_in_group;
				$i++;
			}						
			if(OCIResult($q,"IN_GROUP")=='y') $i++;
			else {
				if($j==1) {
					$in_group='n';
					$tmp_style=" style='background-color:#E5E5E5'";
					echo $header_no_group;
					$j++;
				}
				
			} 
		}
		
		//OCIResult($q,"ID")==$_SESSION['adm']['users']['added_user']?$tmp_class=' class=clicked_row':$tmp_class=''; //подсвечиваем синим добавленного пользователя
		OCIResult($q,"IN_GROUP")=='y'?$tmp_class=' class=clicked_row':$tmp_class=''; //подсвечиваем синим всех пользователей в выбранной группе
		
		echo "<tr data-in_group='".$in_group."' data-user_id='".OCIResult($q,"ID")."' onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//разрешено редактировать, кнопки редактирования
		if(OCIResult($q,"ID")<>$_SESSION['user']['id'] and (($_SESSION['user']['all_users']=='y' and $_SESSION['user']['rw_users']=='w')
		or ($_SESSION['user']['rw_users']=='w' and OCIResult($q,"MY_CHILD")=='y') 
		or ($_SESSION['user']['rw_opers']=='w' and OCIResult($q,"MY_CHILD")=='y' and OCIResult($q,"ROLE_ID")=='operator'))) {
			echo "<td style='cursor:pointer' ondblclick=del_old_user(this)".$tmp_class." title='Удалить пользователя (двойной щелчок)'><img src='png/del.png'></img></td>";
		}
		else {
			echo "<td".$tmp_class."></td>";
		}
		//кнопка перемещения между группами
		if($group_id<>'' and OCIResult($q,"ROLE_ID")<>'root' and 
		(($_SESSION['user']['all_users']=='y' and $_SESSION['user']['rw_users']=='w')
		or ($_SESSION['user']['rw_users']=='w') 
		or ($_SESSION['user']['rw_opers']=='w' and OCIResult($q,"ROLE_ID")=='operator'))) {		
			if(OCIResult($q,"IN_GROUP")=='y') {
				echo "<td style='cursor:pointer' onclick=minus(this)".$tmp_class." title='Исключить пользователя из группы'><img src='png/minus.png'></img></td>";	
			}
			else {
				echo "<td style='cursor:pointer' onclick=plus(this)".$tmp_class." title='Добавить пользователя в группу'><img src='png/plus.png'></img></td>";
			}				
		}
		else {
			echo "<td".$tmp_class."></td>";
		}	
		
		//разрешено редактировать, кнопки редактирования
		if(($_SESSION['user']['all_users']=='y' and $_SESSION['user']['rw_users']=='w')
		or ($_SESSION['user']['rw_users']=='w' and OCIResult($q,"MY_CHILD")=='y') 
		or ($_SESSION['user']['rw_opers']=='w' and OCIResult($q,"MY_CHILD")=='y' and OCIResult($q,"ROLE_ID")=='operator')) {
			echo "<td".$tmp_class."><b>".OCIResult($q,"ID")." г: ".OCIResult($q,"IN_GROUP")." с: ".OCIResult($q,"MY_CHILD")."</b></td>";
			echo "<td".$tmp_class."><input type=text onkeyup='notsaved()' onchange='ch_user(".OCIResult($q,"ID").");notsaved()' onpaste='notsaved()' name=fio[".OCIResult($q,"ID")."] value='".OCIResult($q,"FIO")."'></input></td>";
			echo "<td".$tmp_class."><input type=text onkeyup='notsaved()' onchange='ch_user(".OCIResult($q,"ID").");notsaved()' onpaste='notsaved()' name=login[".OCIResult($q,"ID")."] value='".OCIResult($q,"LOGIN")."'></input></td>";
			echo "<td".$tmp_class."><input type=text onkeyup='notsaved()' onchange='ch_user(".OCIResult($q,"ID").");notsaved()' onpaste='notsaved()' name=pass[".OCIResult($q,"ID")."] value='".OCIResult($q,"PASS")."'></input></td>";
		}		
		else {	
			echo "<td".$tmp_class."><b>".OCIResult($q,"ID")." г: ".OCIResult($q,"IN_GROUP")." с: ".OCIResult($q,"MY_CHILD")."</b></td>";
			echo "<td".$tmp_class."><b>".OCIResult($q,"FIO")."</b></td>";
			echo "<td".$tmp_class."><b>".OCIResult($q,"LOGIN")."</b></td>";
			if(OCIResult($q,"MY_CHILD")=='y') {
				echo "<td".$tmp_class."><b>".OCIResult($q,"PASS")."</b></td>";
			}
			else {
				echo "<td".$tmp_class."><b>******</b></td>";
			}

			
		}
		echo "<td".$tmp_class."><input type=hidden name=role[".OCIResult($q,"ID")."] value='".OCIResult($q,"ROLE_ID")."'><b>".OCIResult($q,"ROLE_NAME")."</b></td>";
		echo "<td".$tmp_class."><b>".OCIResult($q,"CREATE_DATE")."</b></td>";
		echo "<td".$tmp_class.">";
		//протерянный владелец
		if(OCIResult($q,"LOST_CREATOR")=='y' and ($_SESSION['user']['rw_users']=='w' or ($_SESSION['user']['rw_opers']=='w' and OCIResult($q,"ROLE_ID")=='operator'))) {
			echo "<select name=creator[".OCIResult($q,"ID")."] onchange='ch_creator(".OCIResult($q,"ID").");notsaved()'><option value=".OCIResult($q,"CREATOR_ID").">".OCIResult($q,"CREATOR_FIO")."</option>";
			$tmp_user_id=OCIResult($q,"ID");
			OCIBindByName($q_creators,":user_id",$tmp_user_id);
						
			OCIExecute($q_creators, OCI_DEFAULT);
			while(OCIFetch($q_creators)) {
				echo "<option value=".OCIResult($q_creators,"ID").">".OCIResult($q_creators,"FIO")."</option>";
			}
			echo "</select>";
		}
		else {		
			echo "<input type=hidden name=creator[".OCIResult($q,"ID")."] value='".OCIResult($q,"CREATOR_ID")."'><b>".OCIResult($q,"CREATOR_FIO")."</b>";		
		}
		echo "</td>";
		echo "</tr>";
	}
	if($group_id<>'' and $j==1) {
		echo $header_no_group;
	}		
//}
echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";

if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']<>'w')  echo "<font color=red>Редактирование запрещено!</font>";
else {
	echo "<div id=save_status></div>";
	echo "<input type=hidden name=frm_submit value='save'>";
	echo "<input type=submit name='save' value='Сохранить'></input> ";
	echo "<input type=submit name='cancel' value='Отмена' style='display:none'></input> ";
}
//
echo "</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

function func_pass_gen($number) {
	for($i=0; $i < $number; $i++) {
		$rand=rand(1,3);
		if($rand=='1') $pass[$i]=chr(rand(48,57)); //цифры
		if($rand=='2') $pass[$i]=chr(rand(65,90)); //большие лат
		if($rand=='3') $pass[$i]=chr(rand(97,122)); //маленькие лат
	}
	$pass=implode('',$pass);
	return $pass;
}

?>
</body></html>
