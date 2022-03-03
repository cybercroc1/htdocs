<?php 
set_time_limit(600);
include("../../conf/starcall_conf/session.cfg.php"); 

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_quote']=='') {echo "<font color=red>Access DENY!</font>"; exit();}
$project_id=$_SESSION['adm']['project']['id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
//*������������ ����
if(isset($src_quote_rebuild) or isset($manual_quote_rebuild)) {
	$q=OCIParse($c,"select t.src_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['adm']['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"SRC_QUOTE_BROKEN")=='yes' or isset($manual_quote_rebuild)) {
		if(OCIExecute(OCIParse($c,"begin stc_src_quote_rebuild(".$_SESSION['adm']['project']['id']."); end;"))) {
			$_SESSION['adm']['project']['src_quote_broken']='';
			echo "����������� � ����������� ����� �� �������� ����� (STC_SRC_QUOTE_REBUILD(); STC_SRC_QUOTE_CALC())<hr>";
			echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
		if(OCIExecute(OCIParse($c,"begin stc_qst_quote_rebuild(".$_SESSION['adm']['project']['id']."); end;"))) {
			$_SESSION['adm']['project']['qst_quote_broken']='';
			$_SESSION['adm']['project']['qst_stat_broken']='';
			echo "����������� � ����������� ����� �� �������� (STC_QST_QUOTE_REBUILD(); STC_QST_QUOTE_CALC())<hr>";
			echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}		
	} 
}

if(isset($qst_quote_rebuild)) {
	$q=OCIParse($c,"select t.qst_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['adm']['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"QST_QUOTE_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_qst_quote_rebuild(".$_SESSION['adm']['project']['id']."); end;"))) {
		$_SESSION['adm']['project']['qst_quote_broken']=''; 
		$_SESSION['adm']['project']['qst_stat_broken']='';
		echo "����������� � ����������� ����� �� �������� (STC_QST_QUOTE_REBUILD(); STC_QST_QUOTE_CALC())<hr>";
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
	}
}

if(isset($qst_quote_calc)) {
	$q=OCIParse($c,"select t.qst_stat_broken from STC_PROJECTS t
	where id=".$_SESSION['adm']['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"QST_STAT_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_qst_quote_calc(".$_SESSION['adm']['project']['id']."); end;"))) {
		$_SESSION['adm']['project']['qst_stat_broken']='';
		echo "����������� ����� �� �������� (STC_QST_QUOTE_CALC())<hr>";
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
	}
}

if($_SESSION['adm']['project']['src_quote_broken']<>'') {
	echo "<font color=red>�������� ����� �� �������� �����! ���������� ����������� ����� (����� ������ ���������� �����) </font><br> ";
	echo "<input type=button value='����������� ����� �� �������� �����' onclick=this.disabled=true;document.location='adm.quotes.php?src_quote_rebuild'>";
	exit();
}
else if($_SESSION['adm']['project']['qst_quote_broken']<>'') {
	echo "<font color=red>�������� ����� �� ��������! ���������� ����������� ����� (����� ������ ���������� �����) </font><br> ";
	echo "<input type=button value='����������� ����� �� ��������' onclick=this.disabled=true;document.location='adm.quotes.php?qst_quote_rebuild'>";
	exit();
}
else if($_SESSION['adm']['project']['qst_stat_broken']<>'') {
	echo "<font color=red>�������� ���������� �� ��������! ���������� ����������� ���������� (����� ������ ���������� �����) </font><br> ";
	echo "<input type=button value='����������� ����������' onclick=this.disabled=true;document.location='adm.quotes.php?qst_quote_calc'>";
	exit();
}
//*

//������ ��������
echo "<br> | ";
echo "<font size=4>�����</font> | ";
echo "<a href='?manual_quote_rebuild' title='����������� ����� � ����������� ��� ���������� (����� ������ ���������� �����)'><img onClick=this.style.display='none' src='gif/refresh.gif'></img></a> | ";
echo "<a href='help.adm.quotes.html' target=_blank>�������</a> | ";
echo "<hr>";

$src_fields=array();
//������ �������� �����
$q=OCIParse($c,"select id,text_name from STC_FIELDS t
where project_id=".$project_id." and t.src_type_id=1 and t.quoted is not null and t.deleted is null
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$src_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}

$quest_fields=array();
//������ ����������� ��������
$q=OCIParse($c,"select o.quote_num,f.text_name from STC_OBJECTS o, Stc_Fields f
where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
and f.deleted is null and f.id=o.field_id
order by o.quote_num");
OCIExecute($q); $i=0; while(OCIFetch($q)) {$i++;
	$quest_fields[OCIResult($q,"QUOTE_NUM")]=OCIResult($q,"TEXT_NAME");
	$old_qst_quote_id[$i]='';
}

if(count($src_fields)==0 and count($quest_fields)==0) {echo "<font size=3><b>��� ����������� ����� � ��������</b></font>"; exit();}

echo "<form name=frm_download method=get>";
echo "<input type=submit value='��������� � XLSX' onclick=this.disabled=true;parent.logFrame.location='adm.quotes.xlsx_exp.php'>";
echo "</form>";
if($_SESSION['user']['rw_quote']<>'w') echo "<font color=red>�������������� ���������!</font>";
else {
echo "<form name=imp_from_xlsx method=post enctype=\"multipart/form-data\">";
echo "<input type=file name=imp_file onchange=this.value!=''?import_from_file.disabled=false:import_from_file.disabled=true></input>
<input type=button name=import_from_file disabled value='���������' onclick=this.disabled=true;imp_from_xlsx.submit()><br>";	
echo "</form><hr>";
}
//
include('adm.quotes.xlsx_imp.php');

?>
</body>
</html>

