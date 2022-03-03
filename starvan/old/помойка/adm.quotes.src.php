<?php include("../../starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
extract($_REQUEST);

if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();
$project_id=$_SESSION['project']['id'];

include("../../starcall_conf/conn_string.cfg.php");

if(isset($src_quote_rebuild)) {
	$q=OCIParse($c,"select t.src_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"SRC_QUOTE_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_src_quote_rebuild(".$_SESSION['project']['id']."); end;"))) {
		$_SESSION['project']['src_quote_broken']='';
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";		
		}
	} 
}

if(isset($qst_quote_rebuild)) {
	$q=OCIParse($c,"select t.qst_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"QST_QUOTE_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_qst_quote_rebuild(".$_SESSION['project']['id']."); end;"))) {
		$_SESSION['project']['qst_quote_broken']='';
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
	}
}

if($_SESSION['project']['src_quote_broken']<>'') {
	echo "<font color=red><a href='?src_quote_rebuild'>Изменены квоты по исходным полям! Нажмите сюда, что бы перестроить квоты (может занять длительное время) </a></font> ";
	exit();
}
else if($_SESSION['project']['qst_quote_broken']<>'') {
	echo "<font color=red><a href='?qst_quote_rebuild'>Изменены квоты по вопросам! Нажмите сюда, что бы престроить квоты (может занять длительное время) </font> ";
	exit();
}

echo " | ";
echo "<a href='adm.quotes.src.php'><font size=4>Квоты по исходным полям</font></a> | ";
echo "<a href='adm.quotes.qst.php'>Зависимые квоты по вопросам</a> | ";
echo "<a href='adm.quotes.sng.php'>Независимые квоты по вопросам</a> | ";
echo "<font align=right>Справка</font>";
echo "<hr>";

//список исходных полей
$i=0;
$sql_1='';
$sql_2='';
$q=OCIParse($c,"select id,text_name from STC_FIELDS t
where project_id=".$project_id." and t.src_type_id=1 and t.quoted is not null and t.deleted is null
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {$i++;
	$fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
	$sql_1.="max(decode(i.field_id,".OCIResult($q,"ID").",i.value)) f".OCIResult($q,"ID").",";
	if($i>1) $sql_2.=",";
	$sql_2.="f".OCIResult($q,"ID");
	
}
if($i>0) {

	echo "<table>";
	echo "<tr>";
	foreach($fields as $id => $name) {
		echo "<th>".$name."</th>";
	}
	echo "<th>Квота</th>";
	echo "<th>Новых</th>";
	echo "<th>Успешных</th>";
	echo "</tr>";
	//квоты
	$q=OCIParse($c,"select 
	 ".$sql_1."
	qi.quote_id, q.src_quote, q.src_new, q.src_norm
	from stc_src_indexes i, stc_src_quote_indexes qi, stc_src_quotes q
	where i.project_id=".$project_id." and qi.index_id=i.id and q.id=qi.quote_id
	group by qi.quote_id, q.src_quote, q.src_new, q.src_norm
	order by ".$sql_2);
	OCIExecute($q);
	while(OCIFetch($q)) {
		echo "<tr>";
		foreach($fields as $id => $name) {
			echo "<td>".OCIResult($q,"F".$id)."</td>";
		}
		echo "<td>".OCIResult($q,"SRC_QUOTE")."</td>";
		echo "<td>".OCIResult($q,"SRC_NEW")."</td>";
		echo "<td>".OCIResult($q,"SRC_NORM")."</td>";
		
		echo "</tr>";
	}	
}
else echo "<font size=3><b>Нет квотируемых полей</b></font>";

?>
</body>
</html>

