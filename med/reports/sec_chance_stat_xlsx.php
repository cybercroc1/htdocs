<?php
require_once 'med/check_auth.php';

//Проверка прав доступа к данному отчету
$report_id=18;
if(!isset($_SESSION['access']['report'][$report_id])) {
    echo "<div style='color:red'>Ошибка: доступ запрещен</div></br>";
    //echo "<font color=red>Ошибка: доступ запрещен</font>";
	exit();
}

extract($_REQUEST);
require_once "med/conn_string.cfg.php";

$_SESSION['reports']['start_date']=$rep_start_date;
$_SESSION['reports']['end_date']=$rep_end_date;

//Запрос
$sql="select 
to_char(date_chance,'YYYY-MM-DD') as \"Дата\",
sum(otkaz) as \"Отказы\",
sum(notvisit) as \"Непришедшие\",
sum(unknown) as \"Неизвестно\",
sum(rows_chance) as \"Итого\"

from
(select 
t.date_chance,
t.rows_chance,
case when t.reason in ('Недозвон на 5-й день','Обрыв связи на 8-й день','Отказ от записи на 2-й день','Недозвон 2 дня') 
then t.rows_chance else 0 end as otkaz,
case when t.reason in ('Пациент не пришел на 8-й день','Записался и не пришел на 12-й день') 
then t.rows_chance else 0 end as notvisit, 
case when t.reason is null then t.rows_chance else 0 end as unknown  

from SECOND_CHANCE t
where t.date_chance between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
) a
group by to_char(date_chance,'YYYY-MM-DD')
order by to_char(date_chance,'YYYY-MM-DD')";

//в эксель
if(isset($xlsx)) {
	require_once 'sql_to_xlsx.php';
	$sheets[0]['sql']=$sql;
	$sheets[0]['filter']='y';
	//$sheets[0]['name']='Приходы';
	$sheets[0]['head']='Статистика второй шанс '.$_SESSION['reports']['start_date']." - ".$_SESSION['reports']['end_date'];
	$sheets[0]['sum']=array(2,3,4,5);
	$sheets[0]['colwidth']=15;
	sql_to_xlsx($c,$sheets,'sec_chance_stat');
	exit();
}

//в csv
if(isset($csv)) {
	require_once 'sql_to_csv.php';
	sql_to_csv($c,$sql,'sec_chance_stat');
	exit();
}


?>