<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<br>
<?php
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_project']=='') {echo "<font color=red>Access DENY!</font>"; exit();}
$project_id=$_SESSION['adm']['project']['id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

if($_SESSION['user']['rw_project']=='w') { 
if(isset($frm_submit) and $frm_submit=='save') {
	echo "���������� �������� �������<hr>";
	$info='';
	$error='';
	$nedoz_count=trim($nedoz_count);
	$nedoz_interval=trim($nedoz_interval);
	$quote=trim($quote);
	if(!isset($project_name)) $project_name='';
	else $project_name=trim($project_name);
	//�������� ������
	$q=OCIParse($c,"select count(*) count from STC_PROJECTS where trim(upper(name))=trim(upper('".$project_name."')) and trunc(create_date)=(select trunc(create_date) from STC_PROJECTS where id=".$_SESSION['adm']['project']['id'].") 
	and id<>".$_SESSION['adm']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo $error.="<font color=red>������! ������ � ������ \"".$project_name."\" ��� ����������</font><br>";}
	
	if(!isset($set_status)) $set_status='';
	else if($set_status=='�������') {
		$q=OCIParse($c,"select name,SRC_QUOTE_BROKEN,QST_QUOTE_BROKEN from STC_PROJECTS where id=".$_SESSION['adm']['project']['id']);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'' or OCIResult($q,"QST_QUOTE_BROKEN")<>'') {
			$error.="<font color=red>������! ������ ������������ ������. ���������� ����������� �����.</font><br>";
		}	
	}
	if(!preg_match('/^\d{1,15}$/',$nedoz_count)) {
		$error.="<font color=red>������! ���������� ������� ��������� ������ ���� ����� ������������� ������.</font><br>";
	}
	if(!preg_match('/^\d{1,15}$/',$nedoz_interval)) {
		$error.="<font color=red>������! �������� ���������� ����� ���� ����� ������������� ������.</font><br>";
	}
	if(!preg_match('/^\d{0,15}$/',$quote)) {
		$error.="<font color=red>������! ����� ������ ���� ����� ������ ��� ������.</font><br>";
	}
	if(!preg_match('/^\d{0,3}$/',$nedoz_chance) or $nedoz_chance>100) {
		$error.="<font color=red>������! ����������� ��������� ������ ���� ����� ������ �� 0 �� 100.</font><br>";
	}			
	if($to_time<$from_time) {
		$error.="<font color=red>������! ����� ������ ������ ������� ���������.</font><br>";
	}	
	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.frm.save.disabled=false;
		</script>";
		exit();		
	}
	//
	//������ ���-�� ����������
	$q=OCIParse($c,"select nedoz_count, quote from STC_PROJECTS where id=".$project_id);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_nedoz_count=OCIResult($q,"NEDOZ_COUNT");	
	$old_quote=OCIResult($q,"QUOTE");	
	//���������===============================
	$upd=OCIParse($c,"update STC_PROJECTS
	set	name=nvl('".$project_name."',name), status=nvl('".$set_status."',status), nedoz_count=".$nedoz_count.", nedoz_interval=".$nedoz_interval.", from_time='".$from_time."', to_time='".$to_time."', quote='".$quote."', perez_policy='".$perez_policy."', nedoz_chance='".$nedoz_chance."'
	where id=".$project_id);
	OCIExecute($upd, OCI_DEFAULT);
	
	if($nedoz_count<>$old_nedoz_count) {
		if($nedoz_count>$old_nedoz_count) {
			//������ ������ � ������� ��������� �� ��������, ��� �������, ��� ������=end_nedoz � ���-�� �������<$nedoz_count
			$upd=OCIParse($c,"update STC_BASE 
			set status='nedoz'
			where project_id=".$project_id." and status='end_nedoz' and nedoz_count<".$nedoz_count);
			OCIExecute($upd, OCI_DEFAULT);
			if(oci_num_rows($upd)>0) {
				$changed_status='y';
				echo "������� ������ ".oci_num_rows($upd)." ������� � \"������ ��������\" �� \"��������\"<hr>";
			}
		}
		if($nedoz_count<$old_nedoz_count) {
			//������ ������ � ��������� �� ������ ��������, ��� �������, ��� ������=nedoz � ���-�� �������>=$nedoz_count
			$upd=OCIParse($c,"update STC_BASE 
			set status='end_nedoz'
			where project_id=".$project_id." and status='nedoz' and nedoz_count>=".$nedoz_count);
			OCIExecute($upd, OCI_DEFAULT);
			if(oci_num_rows($upd)>0) {
				$changed_status='y';
				echo "������� ������ ".oci_num_rows($upd)." ������� � \"��������\" �� \"������ ��������\"<hr>";
			}
		}	
	}
	if($quote<>$old_quote) {
		$changed_quote='y';
	}
	if(isset($groups)) {
		$chk_grp=OCIParse($c,"select gp.project_id from STC_USER_GRP_PRJ gp where gp.project_id=".$project_id." and gp.group_id=:group_id");
		$ins_grp=OCIParse($c,"insert into STC_USER_GRP_PRJ gp (gp.project_id,gp.group_id) values (".$project_id.",:group_id)");
		$del_grp=OCIParse($c,"delete from STC_USER_GRP_PRJ gp where gp.project_id=".$project_id." and gp.group_id=:group_id");
		foreach($groups as $grp_id => $fuck) {
			if(isset($checked_groups[$grp_id])) {//��������� ������
				OCIBindByName($chk_grp,":group_id",$grp_id);
				OCIExecute($chk_grp, OCI_DEFAULT);
				if(!OCIFetch($chk_grp)) {
					OCIBindByName($ins_grp,":group_id",$grp_id);
					OCIExecute($ins_grp, OCI_DEFAULT);
				}
			}
			else {//������� ������
				OCIBindByName($del_grp,":group_id",$grp_id);
				OCIExecute($del_grp, OCI_DEFAULT);
			}		
		}
	}
	OCICommit($c);
	//=========================================
	//���� ���������� ������� �������, �� ������������� ���������� �� �������� �����
	if(isset($changed_status)) {
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$project_id."); end;"));
		echo "����������� ���������� ���� �� �������� ����� (STC_SRC_QUOTE_CALC)<hr>";
	}
	//�������� ����� ����� �� �������
	if(isset($changed_quote)) {
		OCIExecute(OCIParse($c,"begin STC_QUOTE_COMMON_CALC(".$project_id."); end;"));
		echo "����������� ����� ����� (STC_QUOTE_COMMON_CALC)<hr>";
	}
	echo $info;		
	echo "<font color=green>���������</font><br>";
	echo "<script>
	parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$info."<font color=green>���������</font>';
	parent.admBottomFrame.frm.save.disabled=false;
	parent.admBottomFrame.frm.save.value='���������';
	parent.admBottomFrame.location.reload();
	parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;
	</script>";
	exit();
}
}
$q=OCIParse($c,"select p.name,to_char(p.create_date,'DD.MM.YYYY') create_date,p.status,p.nedoz_count,p.nedoz_interval,p.from_time,p.to_time, p.quote, p.perez_policy, p.nedoz_chance,u.fio creator from STC_PROJECTS p, STC_USERS u 
where p.id=".$project_id."
and u.id=p.creator");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);

echo "<form name=frm method=post target=logFrame>";

	echo "<font size=4>��������� ������� \"".OCIResult($q,"NAME")."\" (id:$project_id)</font><hr>";
	echo "<table id=tbl>";
	echo "<tr>";
	echo "<td colspan=3><b>���� ��������: </b>".OCIResult($q,"CREATE_DATE");
	echo "<b> ������: </b>";
	if($_SESSION['user']['rw_projects']=='w') {
		echo "<select name=set_status>
		".(OCIResult($q,"STATUS")=='������'?'<option value=������ style=color:red selected>������</option>':NULL)."
		<option value='�������' style=color:green".(OCIResult($q,"STATUS")=='�������'?' selected':NULL).">�������</option>
		<option value='�������������' style=color:orange".(OCIResult($q,"STATUS")=='�������������'?' selected':NULL).">�������������</option>
		</select>";
	}
	else {
		echo OCIResult($q,"STATUS")=='������'?'<font color=red><b>������</b></font>':NULL;
		echo OCIResult($q,"STATUS")=='�������'?'<font color=green><b>�������</b></font>':NULL;
		echo OCIResult($q,"STATUS")=='�������������'?'<font color=orange><b>�������������</b></font>':NULL;
	}
	
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>�������� �������: </b></td>";
	echo "<td colspan=2>";
	if($_SESSION['user']['rw_projects']=='w') {
		echo "<input type=text name=project_name value='".OCIResult($q,"NAME")."'>";
	}
	else {
		echo "<b>".OCIResult($q,"NAME")."</b>";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>�����: </b></td><td><input type=text name=quote value='".OCIResult($q,"QUOTE")."'></td>";
	echo "<td><i>����������� �� ���-�� �������� �����, ���� �� �����������, �� �� ����������. ������������� �������������, ���� ���� �����.</i></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td align=center><b>����������� ����� </b></td><td> 
	<select name=from_time>
	<option value='".OCIResult($q,"FROM_TIME")."'>".OCIResult($q,"FROM_TIME")."</option>
	<option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option><option>02:00</option><option>02:30</option><option>03:00</option>
	<option>03:30</option><option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option><option>06:00</option><option>06:30</option>
	<option>07:00</option><option>07:30</option><option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option><option>10:00</option>
	<option>10:30</option><option>11:00</option><option>11:30</option><option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
	<option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option><option>16:00</option><option>16:30</option><option>17:00</option>
	<option>17:30</option><option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option><option>20:00</option><option>20:30</option>
	<option>21:00</option><option>21:30</option><option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
	</select> 
	 - 
	<select name=to_time>
	<option value='".OCIResult($q,"TO_TIME")."'>".OCIResult($q,"TO_TIME")."</option>
	<option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option><option>02:00</option><option>02:30</option><option>03:00</option>
	<option>03:30</option><option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option><option>06:00</option><option>06:30</option>
	<option>07:00</option><option>07:30</option><option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option><option>10:00</option>
	<option>10:30</option><option>11:00</option><option>11:30</option><option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
	<option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option><option>16:00</option><option>16:30</option><option>17:00</option>
	<option>17:30</option><option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option><option>20:00</option><option>20:30</option>
	<option>21:00</option><option>21:30</option><option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
	</select> 
	</td>";	
	echo "<td><i>������� �����, � ������ �������� ������ (������� �� �������� ����� �������, ���� � ������ ��� �������� �����, �� ��������, ��� ����� ����������). �������� \"00:00 - 00:00\" - �������� �������������. </i></td>";
	echo "</tr>";	
	
	echo "<tr>";
	echo "<td align=center><b>���������: �������� ���������� � ������������� �������:</b></td><td>";
	echo "<select name=perez_policy>
	<option value='pub'".(OCIResult($q,"PEREZ_POLICY")=='pub'?' selected':NULL).">�����</option>
	<option value='priv'".(OCIResult($q,"PEREZ_POLICY")=='priv'?' selected':NULL).">�������</option>
	</select>";
	echo "</td>";
	echo "<td><i><b>�����.</b><br>
	<b>���������:</b> ��������� ����� ���������� ����� ���������, ���� ������� ����� ���������, � �� ������ � ������ ������ �� ��������� � ������� � �� �������� �� ������� �������.
	<b>�������������:</b> �������� ����� ����� ������ � ����� �������������, ���� �� ������ � ������ ������ �� ��������� � ������� � �� �������� �� ������� �������.
	<hr>
	<b>�������.</b> �������� �� ����� ����� ������ � ����� ���������� � �������������.
	</i></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td align=center><b>���������: ���-�� ������� </b></td><td><input type=text name=nedoz_count value='".OCIResult($q,"NEDOZ_COUNT")."'></td>";
	echo "<td><i>����������� ����� ����������, ����� ������� ������ ���������� ������� �� �������� (������ ��������)</i></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>���������: ��������(���) </b></td><td><input type=text name=nedoz_interval value='".OCIResult($q,"NEDOZ_INTERVAL")."'></td>";	
	echo "<td><i>��������� ������� �� ��������� ����� ���������, ������ �� ��������� ����� �������</i></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td align=center><b>���������: ������� ���������� � �������������� ������</b></td><td><nobr><input type=text name=nedoz_chance value='".OCIResult($q,"NEDOZ_CHANCE")."'><b></b></td>";	
	echo "<td><i>����������� ������ ��������� � ������������� ������ (0-100%)</i></td>";
	echo "</tr>";	
	
	echo "<tr>";
	echo "<td align=center><b>���������:</b></td><td>";
	echo OCIResult($q,"CREATOR");
	echo "</td>";
	echo "<td><i>������ ������������ ������ ����� ������ � ����� �������.</i></td>";
	echo "</tr>";		
	echo "<td align=center><b>������ ������������� (���������):</b></td><td>";
	if($_SESSION['user']['all_users']=='y') $where_grp=''; 
	//������ �����, � ������� ������� ������������ � ��� �������, � ��� �� ����������� ������� �������� �� � ��� �������
	else $where_grp=" and (g.id in (
	select gu.group_id from STC_USER_GRP_USR gu
	where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))
	or g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))";
	
	$q=OCIParse($c,"select g.id,g.name,max(gp.project_id) project_id, u.fio from STC_USER_GROUP g, STC_USER_GRP_PRJ gp, STC_USERS u 
	where 1=1
	".$where_grp."
	and gp.project_id(+)=".$_SESSION['adm']['project']['id']." and gp.group_id(+)=g.id
	and u.id=g.creator
	group by g.id,g.name,u.fio
	order by g.name");
	OCIExecute($q);
	$i=0;
	while(OCIFetch($q)) {$i++;
		$grp_ids[$i]=OCIResult($q,"ID");
		echo "<input type=hidden name=groups[".OCIResult($q,"ID")."]>
		<input type=checkbox name=checked_groups[".OCIResult($q,"ID")."]".(OCIResult($q,"PROJECT_ID")<>''?' checked':NULL).">".OCIResult($q,"NAME")." (".OCIResult($q,"FIO").")</input><br>";
	}
	//������ ��������� �����, ������� �������� ������
	if(isset($grp_ids)) {
		$grp_ids=implode(',',$grp_ids);
		$where_grp="and g.id not in (".$grp_ids.")";
	}
	else {
		$where_grp="";
	}
	
	$q=OCIParse($c,"select g.id,g.name,max(gp.project_id) project_id, u.fio from STC_USER_GROUP g, STC_USER_GRP_PRJ gp, STC_USERS u 
	where gp.project_id=".$_SESSION['adm']['project']['id']."
	".$where_grp."
	and g.id=gp.group_id
	and u.id=g.creator
	group by g.id,g.name, u.fio
	order by g.name");
	OCIExecute($q);
	$i=0;
	while(OCIFetch($q)) {$i++;
		if($i==1) echo "<hr>";
		echo "<input type=checkbox checked disabled>".OCIResult($q,"NAME")." (".OCIResult($q,"FIO").")</input><br>";
	}		

	
	echo "</td>";	
	echo "<td><i>���������� ������� �������. (� ������ �������� ����� �������� ������������ � ���������, �������� � ��� ������).</i></td>";
	echo "</tr>";	
	echo "</table>";

echo "<hr>";


if($_SESSION['user']['rw_project']<>'w') echo "<font color=red>�������������� ���������!</font>";
else {
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=��������� onclick=this.disabled=true;frm.submit();> ";
}
echo "</form>";

?>
