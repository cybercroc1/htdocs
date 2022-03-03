<?php
require_once 'med/check_auth.php';

//�������� ���� ������� � ������� ������
$report_id=18;
if(!isset($_SESSION['access']['report'][$report_id])) {
    echo "<div style='color:red'>������: ������ ��������</div></br>";
    //echo "<font color=red>������: ������ ��������</font>";
	exit();
}

extract($_REQUEST);
require_once "med/conn_string.cfg.php";

$_SESSION['reports']['start_date']=$rep_start_date;
$_SESSION['reports']['end_date']=$rep_end_date;

//������
$sql="select 
to_char(date_chance,'YYYY-MM-DD') as \"����\",
sum(otkaz) as \"������\",
sum(notvisit) as \"�����������\",
sum(unknown) as \"����������\",
sum(rows_chance) as \"�����\"

from
(select 
t.date_chance,
t.rows_chance,
case when t.reason in ('�������� �� 5-� ����','����� ����� �� 8-� ����','����� �� ������ �� 2-� ����','�������� 2 ���') 
then t.rows_chance else 0 end as otkaz,
case when t.reason in ('������� �� ������ �� 8-� ����','��������� � �� ������ �� 12-� ����') 
then t.rows_chance else 0 end as notvisit, 
case when t.reason is null then t.rows_chance else 0 end as unknown  

from SECOND_CHANCE t
where t.date_chance between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
) a
group by to_char(date_chance,'YYYY-MM-DD')
order by to_char(date_chance,'YYYY-MM-DD')";

//� ������
if(isset($xlsx)) {
	require_once 'sql_to_xlsx.php';
	$sheets[0]['sql']=$sql;
	$sheets[0]['filter']='y';
	//$sheets[0]['name']='�������';
	$sheets[0]['head']='���������� ������ ���� '.$_SESSION['reports']['start_date']." - ".$_SESSION['reports']['end_date'];
	$sheets[0]['sum']=array(2,3,4,5);
	$sheets[0]['colwidth']=15;
	sql_to_xlsx($c,$sheets,'sec_chance_stat');
	exit();
}

//� csv
if(isset($csv)) {
	require_once 'sql_to_csv.php';
	sql_to_csv($c,$sql,'sec_chance_stat');
	exit();
}


?>