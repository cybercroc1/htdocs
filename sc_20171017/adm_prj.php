<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php //if (!isset($_SESSION['i'])) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/sc_path");
if(!isset($new_project_platform)) $new_project_platform="";

if (isset($ch_platform)) {
echo substr($id,3)."-".$check."-".$platform;
if($check=='false') $platform='';
$upd=OCIParse($c,"update sc_projects set platform='".$platform."' where id='".substr($id,3)."'");
OCIExecute($upd);
OCICommit($c);
exit();
}
if (isset($ch_hide)) {
echo substr($id,3)."-".$check;
if($check=='false') $hidden=''; else $hidden='y';
$upd=OCIParse($c,"update sc_projects set hidden='".$hidden."' where id='".substr($id,3)."'");
OCIExecute($upd);
OCICommit($c);
exit();
}

if (isset($order_by)) {
$_SESSION['adm_prj_orderby']=$order_by;
echo "<script> parent.location.reload()</script>";
}

if (isset($add_project)) add_project($new_project_name,$new_project_platform,$c);

if (isset($del_prj)) {
echo "<form method=post action=adm_prj.php>";
$q=OCIParse($c,"select name from sc_projects
where id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$project_name=OCIResult($q,"NAME");
echo "<font size=4 color=red>��������! ������ � �������� \"".OCIResult($q,"NAME")."\" ����� �������:</font><br>
<font size=4>";

$q=OCIParse($c,"select count(*) cnt from sc_call_base
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." �������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_call_report
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." �������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_forms
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ����<br>";

$q=OCIParse($c,"select count(*) cnt from sc_forw_list
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ������� �������������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_punkt
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." �������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_body
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_phones
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ������� �������<br>";

$q=OCIParse($c,"select count(*) cnt from sc_role
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." �����<br>";

$q=OCIParse($c,"select count(*) cnt from sc_shedule
where project_id='".$del_prj."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." ����������<br>";

$q=OCIParse($c,"select count(*) cnt from 
(select login_id,count(*) cnt from sc_role
where login_id in 
(select login_id from sc_role where project_id='".$del_prj."')
group by login_id)
where cnt='1'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
echo OCIResult($q,"CNT")." �������������<br>";

echo "����������� ������� ������� �����: ".$project_name.",<br>";
$i=0;
foreach (@glob($path_to_folders.$project_name."\\*.*") as $filename) {
$i++;
}
echo "���������� ".$i." ������<br>";

echo "</font>
�������� \"�������\", ����� �������<br>
<input type=text name=sure>
<input type=hidden name=project_id value=".$del_prj.">
<input type=submit name=del_prj_go value=�������>
<hr>";
}

if (isset($del_prj_go)) {
	if ($del_prj_go<>$sure) {echo "<font color=red size=4>������ �� ������</font><hr>";}
	else {
	set_time_limit(0);
	$q=OCIParse($c,"select id from sc_call_base
	where project_id='".$project_id."' order by date_call");
	$del=OCIParse($c,"delete from sc_call_base
	where id=:del_prj");
	OCIExecute($q,OCI_DEFAULT);
		while (OCIFetch($q)) {
		$del_prj_tmp=OCIResult($q,"ID");
		OCIBindByName($del,":del_prj",$del_prj_tmp);
		OCIExecute($del,OCI_COMMIT_ON_SUCCESS);
		}
	OCIFreeStatement($q);	
	OCIFreeStatement($del);

	$del=OCIParse($c,"delete from sc_forms where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);
	
	$del=OCIParse($c,"delete from sc_forw_list where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);	
	
	$del=OCIParse($c,"delete from sc_punkt where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);
		
	$del=OCIParse($c,"delete from sc_body where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);
		
	$del=OCIParse($c,"delete from sc_phones where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);
	
	$del=OCIParse($c,"delete from sc_shedule where project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);	

	$del=OCIParse($c,"delete from sc_login where id in (
	select login_id from 
	(select login_id,count(*) cnt from sc_role
	where login_id in 
	(select login_id from sc_role where project_id='".$project_id."')
	group by login_id)
	where cnt='1')");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);
	OCIFreeStatement($del);
	
	$q=OCIParse($c,"select name from sc_projects where id='".$project_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$project_name=OCIResult($q,"NAME");
		
	$del=OCIParse($c,"delete from sc_projects where id='".$project_id."'");
	if (OCIExecute($del,OCI_DEFAULT)) $deleted=1; else $deleted=0;
	OCICommit($c);
	OCIFreeStatement($del);
	
	//�������� �����
	foreach (@glob($path_to_folders.$project_name."\\*.*") as $filename) {
		if (@!unlink($path_to_folders.$project_name."\\".basename($filename))) echo "<br><font color=red>����: ".basename($filename)." �� ������!</font>";
	}
	if (@!rmdir($path_to_folders.$project_name)) echo "<br><font color=red>�����: ".$project_name." �� �������!</font>";
	//
	
	if ($deleted==1) echo "<script language='javascript'>
	parent.document.location='login.php?refresh=1';
	</script>";
	}
}

echo "<form name=projects action=adm_prj.php method=post>";
	echo "<font size=4>�����������������</font><br>";
	echo "<font size=4>�������</font> | <a href=adm_num.php>������</a> | <a href=adm_usr.php>������������</a> | <a href=adm_holidays.php>����������� ���</a><hr>";
	
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white align=center><b>ID</b></td>
	<td bgcolor=white align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.name'>��������</b></td>
	<td bgcolor=white align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.platform'>������</b></td>
	<td bgcolor=white align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.start_date'>���� ��������</a></b></td>
	<td bgcolor=white align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.hidden'>�����</b></td>
	<td bgcolor=white align=center><b><a target='adm_prj_blank_frame' href='?order_by=p.last_call_date'>���� ����.��.</b></td>
	<td bgcolor=white align=center><b>������</b></td>
	<td bgcolor=white align=center><b>������������</b></td>
	<td bgcolor=white align=center></td>";

	echo "</tr>";
	
	//�������� ������
	echo "<tr>
	<td bgcolor=green colspan=2><input type=text name=new_project_name onkeyup=ch_new_project_name()></td>
	<td bgcolor=green align=center><input type=checkbox name=new_project_platform value='Oktell'></td>";
	echo "<td bgcolor=green colspan=4><input type=submit name=add_project disabled value=\"������� ������\"></td>";
	//
	//������ ��������
	if(!isset($_SESSION['adm_prj_orderby']) or $_SESSION['adm_prj_orderby']=='') {$order_by='p.name';} else {$order_by=$_SESSION['adm_prj_orderby'];}
	$q=OCIParse($c,"select p.id,p.name,p.start_date, p.platform, p.last_call_date,p.hidden from sc_projects p  where p.type='irs' order by ".$order_by.", p.name");
	OCIExecute($q,OCI_DEFAULT);
	$q_num=OCIParse($c,"select phone from sc_phones where project_id=:project_id order by phone");
	$q_user=OCIParse($c,"select l.id,l.login, 
decode(r.view_rep,1,'#80FF80','#FF8080') view_rep,
decode(r.ch_email,1,'#80FF80','#FF8080') ch_email,
decode(r.ch_form,1,'#80FF80','#FF8080') ch_form,
decode(r.ch_sc,1,'#80FF80','#FF8080') ch_sc,
decode(r.view_billing,1,'#80FF80','#FF8080') view_billing
from sc_login l, sc_role r
where l.id=r.login_id and r.project_id=:project_id");
	while (OCIFetch($q)) {
	echo "<tr id =tr_".OCIResult($q,"ID").">
	<td bgcolor=white><b>".OCIResult($q,"ID")."</b></td>
	<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
	<td bgcolor=white align=center><b><input type=checkbox id='pl_".OCIResult($q,"ID")."' name=platform value='Oktell' ".(OCIResult($q,"PLATFORM")=="Oktell"?" checked":"")." onclick='ch_platform(this.id,this.checked,this.value)'></b></td>
	<td bgcolor=white><b>".OCIResult($q,"START_DATE")."</b></td>
	<td bgcolor=white align=center><b><input type=checkbox id='hi_".OCIResult($q,"ID")."' name=hidden value='y' ".(OCIResult($q,"HIDDEN")=="y"?" checked":"")." onclick='ch_hide(this.id,this.checked,this.value)'></b></td>
	<td bgcolor=white><b>".OCIResult($q,"LAST_CALL_DATE")."</b></td>";
	$v_id=OCIResult($q,"ID");
	OCIBindByName($q_num,":project_id",$v_id);
	OCIBindByName($q_user,":project_id",$v_id);
	
	OCIExecute($q_num,OCI_DEFAULT);
	echo "<td bgcolor=white><b>";
		while(OCIFetch($q_num)) {
		echo OCIResult($q_num,"PHONE")."<br>";
		}
	echo "</b></td>";

		OCIExecute($q_user,OCI_DEFAULT);
	echo "<td bgcolor=white><b>";
		echo "<table>";
		while(OCIFetch($q_user)) {
		echo "<tr>";
		
		echo "<td><a href=\"adm_usr.php?login_id=".OCIResult($q_user,"ID")."\"><b>".OCIResult($q_user,"LOGIN")."</b></td>";
		echo "<td bgcolor=".OCIResult($q_user,"VIEW_REP").">������</td>
			<td bgcolor=".OCIResult($q_user,"VIEW_BILLING").">�������</td>
			<td bgcolor=".OCIResult($q_user,"CH_EMAIL").">���.e-mail</td>
			<td bgcolor=".OCIResult($q_user,"CH_FORM").">���.�����</td>
			<td bgcolor=".OCIResult($q_user,"CH_SC").">���.��������</td>";
		
		echo "</tr>";
		}
		echo "</table>";
	echo "</b></td>
	<td bgcolor=white><a href=\"adm_prj.php?del_prj=".OCIResult($q,"ID")."\"><img src=del.gif title=\"������� ������\" border=0></a></td>";
	echo "</tr>";
	}
	echo "</table>";
	//

echo "</form><hr>";

function add_project($new_project_name,$new_project_platform,$c) {
include("../../sc_conf/sc_path");
$q=OCIParse($c,"select count(*) count from sc_projects where trim(upper(name))=trim(upper('".$new_project_name."'))");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo "<font color=red>������! ������ � ������ \"".$new_project_name."\" ��� ����������</font>";}
	else if (@!mkdir($path_to_folders.$new_project_name)) {echo "<font color=red>������! �� ������� ������� ����� � ������ \"".$new_project_name."\"</font>";}	
	else {
	$ins=OCIParse($c,"insert into sc_projects (id,name,start_date,type,platform,last_call_date) 
	values (seq_project_id.nextval,'".trim($new_project_name)."',sysdate,'irs','".$new_project_platform."',sysdate)");
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);
	}
}

?>
<iframe width="1px" height="1px" name="adm_prj_blank_frame"></iframe>
<script language="javascript">
function ch_new_project_name() {
	if (document.all.new_project_name.value=='') {
	document.all.add_project.disabled=true;
	} else {
	document.all.add_project.disabled=false;
	}
}
function ch_platform(project_id,check,val) {
	adm_prj_blank_frame.location='adm_prj.php?ch_platform&id='+project_id+'&check='+check+'&platform='+val; 
}
function ch_hide(project_id,check,val) {
	adm_prj_blank_frame.location='adm_prj.php?ch_hide&id='+project_id+'&check='+check; 
}
/*function ch_order_by(order_by) {
	adm_prj_blank_frame.location='adm_prj.php?order_by='+order_by; 
}*/
</script>
