<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','300');
include("../../sc_conf/sc_session");
session_start();
$_SESSION['okt_det_last_url']='okt_det_in.php';
extract($_REQUEST);

if (!isset($_SESSION['project']['view_okt_in_det']) or $_SESSION['project']['view_okt_in_det']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 

//������������ ���

if (!isset($_SESSION['start_bill_date'])) {
	$start_rep_date = strtotime("-1 day");
	$_SESSION['start_bill_date'] = date("d.m.Y",$start_rep_date);
}
	
if (!isset($_SESSION['end_bill_date'])) {
	$end_rep_date = strtotime("-1 day"); //������� ����
	$_SESSION['end_bill_date'] = date("d.m.Y",$end_rep_date);
}

$yesterday = strtotime("- 1 day");
$yesterday = date("d.m.Y",$yesterday);
$curdate = date("d.m.Y");
//

if(isset($_SESSION['admin']) and $_SESSION['admin']==1) $admin='y'; else $admin='';

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/sc_oktell_conn_string");

echo '<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0">';

echo "<form method=post target=res_ifr action=okt_det_in_report.php>";

echo "<nobr><font size=4> ����������� �������� - \"".$_SESSION['project']['name']."\"</font> ";

echo " c: <INPUT TYPE=TEXT NAME=start_bill_date value=".$_SESSION['start_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=���������></A>"; 

echo " ��: <INPUT TYPE=TEXT NAME=end_bill_date value=".$_SESSION['end_bill_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_bill_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=���������></A> (������������)"; 

//������ ��������� ���������
$_SESSION['ACC_OKT_IN_ROUTE_IDS']=array();
if($_SESSION['project']['id']==0) {
	$q=OCIParse($c,"select air.okt_in_route_id from SC_ACC_OKT_IN_ROUTE air
	where air.login_id='".$_SESSION['login_id']."' 
	and air.project_id=0 
	and view_okt_in_det=1");
}
else {
	$q=OCIParse($c,"select r.okt_in_route_id from SC_ACC_OKT_IN_ROUTE air, SC_OKTELL_IN_ROUTES r
	where air.login_id='".$_SESSION['login_id']."'
	and air.project_id='".$_SESSION['project']['id']."' and r.project_id=air.project_id and r.okt_in_route_id=decode(air.okt_in_route_id,0,r.okt_in_route_id,air.okt_in_route_id)
	and view_okt_in_det=1");	
}
OCIExecute($q);
while(OCIFetch($q)) {
	$_SESSION['ACC_OKT_IN_ROUTE_IDS'][]=OCIResult($q,"OKT_IN_ROUTE_ID");
}
//
if(count($_SESSION['ACC_OKT_IN_ROUTE_IDS'])>0) {
	//�������� ���������
	$route_names=array();
	$q=sqlsrv_query($c_okt,"SELECT distinct [��������_��������]
	FROM [oktell].[dbo].[SVA_Inbound_Routes] t 
	where (����_��������� is null or ����_���������<getdate()) and (��������_���� is null or ��������_����>dateadd(DD,-45,getdate()))  
	and t.location_id=1 and [��������_��������] is not null and t.id in (".implode(',',$_SESSION['ACC_OKT_IN_ROUTE_IDS']).")
	order by [��������_��������]");
	while($row=sqlsrv_fetch_array($q)) {
		$route_names[]=$row[0];
	}
	//���������� �����
	$bnumbers=array();
	$q=sqlsrv_query($c_okt,"SELECT distinct [�����_�]
	FROM [oktell].[dbo].[SVA_Inbound_Routes] t 
	where (����_��������� is null or ����_���������<getdate()) and (��������_���� is null or ��������_����>dateadd(DD,-45,getdate())) 
	and t.location_id=1 and [�����_�] is not null and t.id in (".implode(',',$_SESSION['ACC_OKT_IN_ROUTE_IDS']).")
	order by [�����_�]");
	while($row=sqlsrv_fetch_array($q)) {
		$bnumbers[]=$row[0];
	}
	//�����������
	$directions=array();
	$q=sqlsrv_query($c_okt,"SELECT distinct [���_�����������]
	FROM [oktell].[dbo].[SVA_Inbound_Routes] t 
	where (����_��������� is null or ����_���������<getdate()) and (��������_���� is null or ��������_����>dateadd(DD,-45,getdate())) 
	and t.location_id=1 and [���_�����������] is not null and t.id in (".implode(',',$_SESSION['ACC_OKT_IN_ROUTE_IDS']).")
	order by [���_�����������]");
	while($row=sqlsrv_fetch_array($q)) {
		$directions[]=$row[0];
	}
	//������
	$task_keys=array();
	$q=sqlsrv_query($c_okt,"SELECT distinct [����_������]
	FROM [oktell].[dbo].[SVA_Inbound_Routes] t
	where (����_��������� is null or ����_���������<getdate()) and (��������_���� is null or ��������_����>dateadd(DD,-45,getdate())) 
	and t.location_id=1 and [����_������] is not null and t.id in (".implode(',',$_SESSION['ACC_OKT_IN_ROUTE_IDS']).")
	order by [����_������]");
	while($row=sqlsrv_fetch_array($q)) {
		$task_keys[]=$row[0];
	}
}

echo "</nobr>";

echo "<table border=0>";
echo "<tr>";

echo "<td><select name=route_names[] multiple size=10><option value='all' selected>��� ��������</option>";
foreach($route_names as $val) {
	echo "<option value='".htmlentities($val)."'>".htmlentities($val)."</option>"; 
}
echo "</select></td>";

echo "<td><select name=bnumbers[] multiple size=10><option value='all' selected>��� ������</option>";
foreach($bnumbers as $val) {
	echo "<option value='".htmlentities($val)."'>".htmlentities($val)."</option>"; 
}
echo "</select></td>";

echo "<td><select name=directions[] multiple size=10><option value='all' selected>��� �����������</option>";
foreach($directions as $val) {
	echo "<option value='".htmlentities($val)."'>".htmlentities($val)."</option>"; 
}
echo "</select></td>";

echo "<td><select name=task_keys[] multiple size=10><option value='all' selected>��� ������</option>";
foreach($task_keys as $val) {
	echo "<option value='".htmlentities($val)."'>".htmlentities($val)."</option>"; 
}
echo "</select></td>";

echo "</tr>";
echo "</table>";
echo "<INPUT type=submit name=report_go value=\"�������� �����\"> <INPUT type=submit name=csv_go value=\"CSV\">";	
echo "</form>";
echo '<iframe name=res_ifr width="100%" height="750" scrolling="yes" frameborder="0" src=blank.htm></iframe>';
echo '<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe></body>
</html>';
?>