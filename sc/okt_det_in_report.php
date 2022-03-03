<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','300');
include("sc/sc_session.php");
session_start();
extract($_REQUEST);

if (!isset($_SESSION['project']['view_okt_in_det']) or $_SESSION['project']['view_okt_in_det']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 

include("oktell_conn_string.php");

$_SESSION['start_bill_date']=$start_bill_date;
$_SESSION['end_bill_date']=$end_bill_date;

echo '<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0">';
	
	if($route_names[0]<>'all') {
		$sql_route_names=" and t.��������_�������� in ('".implode("','",$route_names)."') ";
	} 
	else $sql_route_names=''; 

	if($bnumbers[0]<>'all') {
		$sql_bnumbers=" and t.�����_� in ('".implode("','",$bnumbers)."') ";
	}
	else $sql_bnumbers=''; 
	
	if($directions[0]<>'all') {
		$sql_directions=" and t.���_����������� in ('".implode("','",$directions)."') ";
	}	
	else $sql_directions='';
	
	if($task_keys[0]<>'all') {
		$sql_task_keys=" and t.����_������ in ('".implode("','",$task_keys)."') ";
	}
	else $sql_task_keys=''; 
	
	$route_ids=array();
	
	$sql="SELECT [id]
	FROM [oktell].[dbo].[SVA_Inbound_Routes] t
	where t.location_id=1 
	and (����_��������� is null or ����_���������<getdate()) and (��������_���� is null or ��������_����>dateadd(DD,-45,getdate())) 
	and id in (".implode(',',$_SESSION['ACC_OKT_IN_ROUTE_IDS']).") 
	".$sql_route_names."
	".$sql_bnumbers."
	".$sql_directions."
	".$sql_task_keys;
	
	$q=sqlsrv_query($c_okt,$sql);
	echo "<textarea>$sql</textarea>";
	while($row=sqlsrv_fetch_array($q)) {
		$route_ids[]=$row[0];
	}
	if(count($route_ids)==0) {exit();}
	
//�������� ������
$sql="
SELECT 
convert(varchar(25),c.start_date,121) start_date, r.��������_��������, r.����_������, c.idChain [ID �������], c.a_number [����� �], c.b_number [����� �], c.a_line_id, c.b_line_id,
c.call_type+(case when c.is_first='y' then '(first)' else '' end) call_type,
t.name_det [��� ������], 
case when c.call_type in ('in', 'inop') and c.is_first='y' then 
datediff(ss,start_date,isnull((case 
	when c.queue_date is null then c.originate_date 
	when c.originate_date is null then c.queue_date
	when c.queue_date<=c.originate_date then c.queue_date
	else c.originate_date end),end_date))
else NULL end [IVR, ���],
convert(varchar(25),c.queue_date,121) [��������� � �������],
convert(varchar(25),c.originate_date,121) [������������� �� IVR],
datediff(ss,queue_date,isnull(c.ring_date,c.end_date)) [�������, ���],
convert(varchar(25),c.ring_date,121) [�������� �� ���������],
c.oper_user_name [��������],
datediff(ss,ring_date,isnull(c.answer_date,c.end_date)) [���, ���],
case when c.call_type in ('inout','inoutfail','cbout','cboutfail') 
then datediff(ss,start_date,isnull(c.answer_date,c.end_date)) 
else datediff(ss,queue_date,isnull(c.answer_date,c.end_date)) end [��������, ��� (�������+���)],
convert(varchar(25),c.answer_date,121) [�������],
datediff(ss,c.answer_date,isnull(c.oper_end_date,c.end_date)) [��������, ���],
convert(varchar(25),c.end_date,121) [��������],
c.transit_bnumber [���������� �����],
datediff(ss,isnull(c.start_transit_date,c.end_date),c.end_transit_date) [����������, ���],
convert(varchar(25),c.end_transit_date,121) [���������� ��������],
c.call_result_code [��� ����������],
c.call_result_info [����� ����������]

FROM [oktell_CDR].[dbo].[inbound_route_CDR] c, [oktell_CDR].[dbo].[list_inbound_calltypes] t, [oktell].[dbo].[SVA_Inbound_Routes] r
where t.id=c.call_type and r.id=c.in_route_id and r.location_id=1
and c.in_route_id in (".implode(",",$route_ids).")
and c.start_date between '".$start_bill_date."' and dateadd(dd,+1,cast('".$end_bill_date."' as date))

and (
	call_type in ('cbfail','cbout','cboutfail','cbtransit','inop','inout','inoutfail','intransit','cbinop','?outfail') or 
	(call_type in ('callback','in') and c.is_first='y')
	)

order by --c.start_date,
c.start_date_chain,c.start_date,
r.��������_��������, r.����_������
";

echo "<textarea>$sql</textarea>";

$q=sqlsrv_query($c_okt,$sql);
echo "<table class=white_table>";
$rnum=0; while($row=sqlsrv_fetch_array($q, SQLSRV_FETCH_ASSOC)) {$rnum++;
if($rnum==1) { 
echo "<tr>";
	foreach($row as $key => $val) {
		echo "<td>".$key."</td>";
	}
echo "</tr>";
}
echo "<tr>";
	foreach($row as $key => $val) {
		echo "<td>".$val."</td>";
	}
echo "</tr>";
}
echo "</table>";	
	
?>