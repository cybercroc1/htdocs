<?php 
include("sc\sc_session.php");
session_start();
?>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="..\starcall.css" rel="stylesheet" type="text/css">
</head>
<BODY topmargin=10 leftmargin=10>
<?php
if (!isset($_SESSION['login_id'])) {echo "<font color=red>�������� ����������!</font>"; exit();} 

if(!isset($module_id)) $module_id='';

include("sc/sc_conn_string.php");

$sql_text="select id, name from SC_MODULES m
where m.project_id='".$_SESSION['project']['id']."'";
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>1) $sql_text.=" and m.id in (select a.module_id from SC_MODULES_ACC a where a.user_id='".$_SESSION['login_id']."')";
$q=OCIParse($c,$sql_text);
OCIExecute($q,OCI_DEFAULT);
$i=0;
while(OCIFetch($q)) { $i++;
	$modules[OCIResult($q,"ID")]=OCIResult($q,"NAME");
}
if($i==0) exit();

require_once 'oktell_conn_string.php';

if(isset($_POST['save'])) {
	$query = $c_okt->query("update [oktell_ccws].[dbo].[biz_telecom_ivr] set [ivr_num]='".$_POST['ivr_type']."'");
}

	
$query = $c_okt->query("SELECT TOP 1 [ivr_num]
  FROM [oktell_ccws].[dbo].[biz_telecom_ivr]");

$query->setFetchMode(PDO::FETCH_ASSOC);
$row=$query->fetch();

echo "<form method=post>";
echo "<hr>";
echo "<input type=radio name=ivr_type value='0'".($row['ivr_num']=='0'?" checked":"").">
<font size=3>".($row['ivr_num']=='0'?"<b>":"")."������� �����".($row['ivr_num']=='0'?"</b>":"")."</font></input><br>";
echo "������������! �� ��������� � �������� ����-�������. ��� ������ ����� ����� ��� ���! ����������, ����������� �� �����, � ��������� ����� ��� �������";
echo "<hr>";
echo "<input type=radio name=ivr_type value='1'".($row['ivr_num']=='1'?" checked":"").">
<font size=3>".($row['ivr_num']=='1'?"<b>":"")."������. 30 �����".($row['ivr_num']=='1'?"</b>":"")."</font></input><br>";
echo "���������� ��� � ��������� �������� ����� �� ���� ��� �������. ������� ����������.
��������������� ���� �������������� �������� 30 �����.
��� ���������� � ���������� Call-������ ����������� �� �����";
echo "<hr>";
echo "<input type=radio name=ivr_type value='2'".($row['ivr_num']=='2'?" checked":"").">
<font size=3>".($row['ivr_num']=='2'?"<b>":"")."������. 1 ���".($row['ivr_num']=='2'?"</b>":"")."</font></input><br>";
echo "���������� ��� � ��������� �������� ����� �� ���� ��� �������. ������� ����������.
��������������� ���� �������������� �������� 1 ���.
��� ���������� � ���������� Call-������ ����������� �� �����";
echo "<hr>";
echo "<input type=radio name=ivr_type value='3'".($row['ivr_num']=='3'?" checked":"").">
<font size=3>".($row['ivr_num']=='3'?"<b>":"")."������. 1,5 ����".($row['ivr_num']=='3'?"</b>":"")."</font></input><br>";
echo "���������� ��� � ��������� �������� ����� �� ���� ��� �������. ������� ����������.
��������������� ���� �������������� �������� 1,5 ����.
��� ���������� � ���������� Call-������ ����������� �� �����";
echo "<hr>";
echo "<input type=radio name=ivr_type value='4'".($row['ivr_num']=='4'?" checked":"").">
<font size=3>".($row['ivr_num']=='4'?"<b>":"")."������. 2 ����".($row['ivr_num']=='4'?"</b>":"")."</font></input><br>";
echo "���������� ��� � ��������� �������� ����� �� ���� ��� �������. ������� ����������.
��������������� ���� �������������� �������� 2 ����.
��� ���������� � ���������� Call-������ ����������� �� �����";
echo "<hr>";
echo "<input type=submit name=save value=���������></input>";
echo "</form>";

?>
