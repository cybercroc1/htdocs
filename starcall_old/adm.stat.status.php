<?php 
include("../../conf/starcall_conf/session.cfg.php"); 

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_stat']=='') {echo "<font color=red>Access DENY!</font>"; exit();}
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

//НАЧАЛО СТРАНИЦЫ

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

echo "<font size=4><a href='adm.stat.status.php' target='admBottomFrame'>Статистика по статусам</a></font> | ";
echo "<a href='adm.stat.quotes.php' target='admBottomFrame'>Статистика по квотам</a> | ";
echo "Справка | ";
echo "<hr>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";

$q=OCIParse($c,"select p.quote,p.stat_new,p.stat_end_norm,p.stat_inwork,p.stat_end_error,
p.stat_end_false,p.stat_end_nedoz,p.stat_end_otkaz,p.stat_end_quote,p.stat_nedoz,p.stat_perez from STC_PROJECTS p
where p.id=".$project_id);
OCIExecute($q);
OCIFetch($q);
echo "<table>";
echo "<tr><th>Квота</td><td>".OCIResult($q,"QUOTE")."</td></tr>";	
echo "<tr><th>Новых</td><td>".OCIResult($q,"STAT_NEW")."</td></tr>";	
echo "<tr><th>Успешных</td><td>".OCIResult($q,"STAT_END_NORM")."</td></tr>";	
echo "<tr><th>Нецелевых</td><td>".OCIResult($q,"STAT_END_FALSE")."</td></tr>";	
echo "<tr><th>Глухих недозвонов</td><td>".OCIResult($q,"STAT_END_NEDOZ")."</td></tr>";	
echo "<tr><th>Превышена квота</td><td>".OCIResult($q,"STAT_END_QUOTE")."</td></tr>";	
echo "<tr><th>Отказ</td><td>".OCIResult($q,"STAT_END_OTKAZ")."</td></tr>";	
echo "<tr><th>Ошибка</td><td>".OCIResult($q,"STAT_END_ERROR")."</td></tr>";	
echo "<tr><th>В работе</td><td>".OCIResult($q,"STAT_INWORK")."</td></tr>";	
echo "<tr><th>Недозвонов</td><td>".OCIResult($q,"STAT_NEDOZ")."</td></tr>";	
echo "<tr><th>Перезвонов</td><td>".OCIResult($q,"STAT_PEREZ")."</td></tr>";	
echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
</body>
</html>

