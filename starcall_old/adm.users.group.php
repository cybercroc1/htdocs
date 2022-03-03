<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script>
var new_idx=0;
</script>
<script src="func.row_select.js"></script>
<script src="adm.users.group.js"></script>
<body>
<?php
extract($_REQUEST);
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['rw_users']=='' and $_SESSION['user']['rw_opers']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form name=frm method=post action='adm.users.group.save.php' target='logFrame'>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

if(!isset($order_by) and !isset($_SESSION['adm']['groups']['order_by'])) $_SESSION['adm']['groups']['order_by']='fio';
if(isset($order_by)) $_SESSION['adm']['groups']['order_by']=$order_by;

if(!isset($_SESSION['adm']['users']['group_id'])) $_SESSION['adm']['users']['group_id']='';

	echo "<font size=4>Группы пользователей</font>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
	
	echo "<table id=tbl>";
	echo "<tr>";
	
	if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w')
		echo "<td width=13 style='cursor:pointer' onclick=add_group(this) title='Создать группу'><img src=png/plus.png></img></td>";
	else 
		echo "<td></td>";
	echo "<td align=center width=20><b>ID</b></td>";
	echo "<td align=center><a href='adm.users.group.php?order_by=g.name'>".($_SESSION['adm']['groups']['order_by']=='g.name'?'<b>':NULL)."Название</b></a></td>";
	echo "<td align=center title='Все создаваемые проекты и пользователи будут автоматически добавляться в эту группу, если создатель в ней состоит'><b>Группа по умолчанию</b></td>";
	echo "<td align=center><a href='adm.users.group.php?order_by=g.create_date desc'>".($_SESSION['adm']['groups']['order_by']=='g.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
	<td align=center><a href='adm.users.group.php?order_by=creator_fio'>".($_SESSION['adm']['groups']['order_by']=='creator_fio'?'<b>':NULL)."Создатель</b></a></td>";
	echo "</tr>";
	
	//Список групп
	if($_SESSION['user']['all_users']=='y') $where_grp=''; 
	else $where_grp="and ((
    --группы, в которых участвую я или мои потомки (если не ALL_USERS) 
    g.id in (select gu.group_id from STC_USER_GRP_USR gu where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))
  ) 
  or (
    --группы, у которых создатель я или мои потомки (если не ALL_USERS)
    g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")  
  ))";

	$q=OCIParse($c,"
  select g.id,g.name, to_char(g.create_date,'DD.MM.YYYY') create_date, g.create_date cd, g.creator, g.default_group, u.fio creator_fio,
  (select decode(count(*),0,NULL,'y') from STC_USER_CHILD where child_user_id=g.creator and user_id=".$_SESSION['user']['id'].") my_child
  from STC_USER_GROUP g, STC_USERS u
  where u.id=g.creator
	".$where_grp."
	order by ".$_SESSION['adm']['groups']['order_by']);	
	
	$_SESSION['adm']['users']['group_id']==''?$tmp_class=' class=clicked_row':$tmp_class='';
	
	echo "<tr data-group_id='' onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
	echo "<td".$tmp_class."></td>
	<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class."></b></td>
	<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class." colspan=4><b>ВСЕ ПОЛЬЗОВАТЕЛИ</b></td>";
	echo "</tr>";	
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		$_SESSION['adm']['users']['group_id']==OCIResult($q,"ID")?$tmp_class=' class=clicked_row':$tmp_class='';
		
		echo "<tr data-group_id='".OCIResult($q,"ID")."' onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//редактирование разрешено
		if($_SESSION['user']['all_users']=='y' or
		(($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w') and OCIResult($q,"MY_CHILD")=='y' )) {
			echo "<td style='cursor:pointer' ondblclick='del_old_group(this)'".$tmp_class." title='Удалить группу (двойной щелчок)'><img src=png/del.png></img></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class.">".OCIResult($q,"ID")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class.">
			<input type=hidden name=creator[".OCIResult($q,"ID")."] value=".OCIResult($q,"CREATOR").">
			<input type=text name=name[".OCIResult($q,"ID")."] onkeyup='notsaved()' onchange='ch_group(".OCIResult($q,"ID").");notsaved()' onpaste='notsaved()' value='".OCIResult($q,"NAME")."'></input></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class.">
			<select name=default_group[".OCIResult($q,"ID")."] onchange='ch_group(".OCIResult($q,"ID").");notsaved()'>
			<option value=''>Нет</option>
			<option value='y'".(OCIResult($q,"DEFAULT_GROUP")=='y'?" selected":NULL).">Да</option>
			</select>
			</td>";		
		}
		//редактирование запрещено	
		else {
			echo "<td></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class.">".OCIResult($q,"ID")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class."><b>".OCIResult($q,"NAME")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class."><b>".(OCIResult($q,"DEFAULT_GROUP")=='y'?"Да":"Нет")."</b></td>";

		}
		echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class."><b>".OCIResult($q,"CREATE_DATE")."</b></td>";
		echo "<td style='cursor:pointer' onclick='select_group(this)'".$tmp_class."><b>".OCIResult($q,"CREATOR_FIO")."</b></td>";
		echo "</tr>";
	}
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
echo "<form name=frm_select_group method=post target=admUsersFrame action=adm.users.php><input type=hidden name=group_id value=''></input></form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
</body></html>