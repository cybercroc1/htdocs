<?php include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
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
include("starcall/conn_string.cfg.php");
if($_SESSION['user']['rw_users']=='' and $_SESSION['user']['rw_opers']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form name=frm method=post action='adm.users.group.save.php' target='logFrame'>";

//�����-�����. �����
echo "<table class=content_table><tr class=header_tr><td>";

if(!isset($order_by) and !isset($_SESSION['adm']['groups']['order_by'])) $_SESSION['adm']['groups']['order_by']='fio';
if(isset($order_by)) $_SESSION['adm']['groups']['order_by']=$order_by;

if(!isset($_SESSION['adm']['users']['group_id'])) $_SESSION['adm']['users']['group_id']='';

	echo "<font size=4>������ �������������</font>";

//�����-�����. �������
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
	
	echo "<table id=tbl class=white_table>";
	echo "<tr>";
	
	if($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w')
		echo "<td width=13 style='cursor:pointer' onclick=add_group(this) title='������� ������'><img src=png/plus.png></img></td>";
	else 
		echo "<td></td>";
	echo "<td align=center width=20><b>ID</b></td>";
	echo "<td align=center><a href='adm.users.group.php?order_by=g.name'>".($_SESSION['adm']['groups']['order_by']=='g.name'?'<b>':NULL)."��������</b></a></td>";
	echo "<td align=center title='��� ����������� ������� � ������������ ����� ������������� ����������� � ��� ������, ���� ��������� � ��� �������'><b>������ �� ���������</b></td>";
	echo "<td align=center><a href='adm.users.group.php?order_by=g.create_date desc'>".($_SESSION['adm']['groups']['order_by']=='g.create_date desc'?'<b>':NULL)."���� ��������</b></a></td>
	<td align=center><a href='adm.users.group.php?order_by=creator_fio'>".($_SESSION['adm']['groups']['order_by']=='creator_fio'?'<b>':NULL)."���������</b></a></td>";
	echo "</tr>";
	
	//������ �����
	if($_SESSION['user']['all_users']=='y') $where_grp=''; 
	else $where_grp="and ((
    --������, � ������� �������� � ��� ��� ������� (���� �� ALL_USERS) 
    g.id in (select gu.group_id from STC_USER_GRP_USR gu where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))
  ) 
  or (
    --������, � ������� ��������� � ��� ��� ������� (���� �� ALL_USERS)
    g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")  
  ))";

	$q=OCIParse($c,"
  select g.id,g.name, to_char(g.create_date,'DD.MM.YYYY') create_date, g.create_date cd, g.creator, g.default_group, u.fio creator_fio,
  (select decode(count(*),0,NULL,'y') from STC_USER_CHILD where child_user_id=g.creator and user_id=".$_SESSION['user']['id'].") my_child
  from STC_USER_GROUP g, STC_USERS u
  where u.id=g.creator
	".$where_grp."
	order by ".$_SESSION['adm']['groups']['order_by']);	
	
	$_SESSION['adm']['users']['group_id']==''?$tmp_class=' class=selected_row':$tmp_class=' class=selectable_row';
	
	echo "<tr data-group_id=''".$tmp_class.">";
	echo "<td onclick='select_group(this)'></td>
	<td style='cursor:pointer' onclick='select_group(this)'></b></td>
	<td style='cursor:pointer' colspan=4 onclick='select_group(this)'><b>��� ������������</b></td>";
	echo "</tr>";	
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		$_SESSION['adm']['users']['group_id']==OCIResult($q,"ID")?$tmp_class=' class=selected_row':$tmp_class=' class=selectable_row';
		
		echo "<tr data-group_id='".OCIResult($q,"ID")."'".$tmp_class.">";
		//�������������� ���������
		if($_SESSION['user']['all_users']=='y' or
		(($_SESSION['user']['rw_users']=='w' or $_SESSION['user']['rw_opers']=='w') and OCIResult($q,"MY_CHILD")=='y' )) {
			echo "<td style='cursor:pointer' ondblclick='del_old_group(this)' title='������� ������ (������� ������)'><img src=png/del.png></img></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'>".OCIResult($q,"ID")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'>
			<input type=hidden name=creator[".OCIResult($q,"ID")."] value=".OCIResult($q,"CREATOR").">
			<input type=text name=name[".OCIResult($q,"ID")."] onkeyup='notsaved()' onchange='ch_group(".OCIResult($q,"ID").");notsaved()' onpaste='notsaved()' value='".OCIResult($q,"NAME")."'></input></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'>
			<select name=default_group[".OCIResult($q,"ID")."] onchange='ch_group(".OCIResult($q,"ID").");notsaved()'>
			<option value=''>���</option>
			<option value='y'".(OCIResult($q,"DEFAULT_GROUP")=='y'?" selected":NULL).">��</option>
			</select>
			</td>";		
		}
		//�������������� ���������	
		else {
			echo "<td></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'>".OCIResult($q,"ID")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'><b>".OCIResult($q,"NAME")."</b></td>";
			echo "<td style='cursor:pointer' onclick='select_group(this)'><b>".(OCIResult($q,"DEFAULT_GROUP")=='y'?"��":"���")."</b></td>";

		}
		echo "<td style='cursor:pointer' onclick='select_group(this)'><b>".OCIResult($q,"CREATE_DATE")."</b></td>";
		echo "<td style='cursor:pointer' onclick='select_group(this)'><b>".OCIResult($q,"CREATOR_FIO")."</b></td>";
		echo "</tr>";
	}
	echo "</table>";

//�����-�����. �����
echo "</div></td></tr><tr class=footer_tr><td>";

	if($_SESSION['user']['rw_users']<>'w' and $_SESSION['user']['rw_opers']<>'w')  echo "<font color=red>�������������� ���������!</font>";
	else {
		echo "<div id=save_status></div>";
		echo "<input type=hidden name=frm_submit value='save'>";
		echo "<input type=submit name='save' value='���������'></input> ";
		echo "<input type=submit name='cancel' value='������' style='display:none'></input> ";
	}
	//

echo "</form>";
echo "<form name=frm_select_group method=post target=admUsersFrame action=adm.users.php><input type=hidden name=group_id value=''></input></form>";

//�����-�����. �����
echo "</td></tr></table>";

?>
</body></html>