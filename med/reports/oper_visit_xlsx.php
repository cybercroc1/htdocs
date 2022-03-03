<?php
require_once 'med/check_auth.php';

//Проверка прав доступа к данному отчету
$report_id=16;
if(!isset($_SESSION['access']['report'][$report_id])) {
    echo "<div style='color:red'>Ошибка: доступ запрещен</div></br>";
    //echo "<font color=red>Ошибка: доступ запрещен</font>";
	exit();
}

extract($_REQUEST);
require_once "med/conn_string.cfg.php";

//часть запроса с ограничением прав доступа к заявкам
if($_SESSION['user_role']==1) $sql_access_part='';
else $sql_access_part=" and (b.source_auto_id, b.source_man_id, b.source_type_id, b.service_id) 
in (select 
decode(ad.source_auto_id,-1,b.source_auto_id,ad.source_auto_id),
decode(ad.source_man_id,-1,b.source_man_id,ad.source_man_id),
decode(ad.source_type_id,-1,b.source_type_id,ad.source_type_id),
decode(ad.service_id,-1,b.service_id,ad.service_id)

from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null
and ad.departament_id=d.id 
)";

$_SESSION['reports']['start_date']=$rep_start_date;
$_SESSION['reports']['end_date']=$rep_end_date;

//выбранные фильтры
if($sel_opers[0]=='-1') $sel_opers_str='-1';
else $sel_opers_str="'".(implode("','",$sel_opers))."'";

//Запрос
$sql="SELECT 
--usr.ID, 
usr.FIO \"Оператор\",
a.SERVICE_NAME \"Услуга\",

/*nvl(a.count_all,0) \"Всего\nобращений\",*/

nvl(a.order_count,0) \"Принято\nобращений\",

/*nvl(a.visit_of_orders,0) \"Пришло\nиз принятых\",*/
/*
nvl(case 
when a.order_count>0 then 
round(a.visit_of_orders/a.order_count*100,0)
else NULL
end,0) \"% пришедших\nот принятых\",
*/

nvl(a1.visit_by_per,0) \"Пришло\nза период\",

nvl(case 
when a.order_count>0 then 
round(a1.visit_by_per/a.order_count*100,0)
else NULL
end,0) \"% приш.за пер.\nот принятых\"

FROM USERS usr 

--все из принятых
left join 
(
select 
B.FIO_ID,
B.SERVICE_ID,
S.NAME SERVICE_NAME,
--B.SOURCE_AUTO_ID,
--SA.NAME SOURCE_AUTO_NAME,
--B.SOURCE_TYPE_ID,
--ST.NAME SOURCE_TYPE_NAME,
count(*) COUNT_ALL, --Поступило обращений,
count(case when b.status_id in ('10','3','6') and nvl(b.call_double,0)<2 and b.interstate is null then 1 else NULL end) ORDER_COUNT, --Принято обращений
sum(v.cnt_visit) VISIT_OF_ORDERS, --Пришло из принятых
count(p.base_id) PAYED_OF_ORDERS, --Оплачено из принятых
sum(p.rub) PAYMENT_SUM_OF_ORDERS, --Сумма проплат из принятых
sum(ph.plan_sum) PLAN_SUM_OF_ORDERS, --Сумма утвержденных планов из принятых
sum(ph.plan_sum_2500_plus) PLAN_SUM_OF_ORDERS_2500_PLUS --Сумма утвержденных планов из принятых 2500+
 
from CALL_BASE b
left join (select base_id,count(*) cnt_payment ,sum(rub) rub from PAYMENT_HIST group by base_id) p on p.base_id=b.id
left join (select base_id,count(*) cnt_visit from VISIT_HIST group by base_id) v on v.base_id=b.id
left join (select base_id, sum(rub) plan_sum, (case when sum(rub)>=2500 then sum(rub) else null end) plan_sum_2500_plus from PLAN_HIST group by base_id) ph on ph.base_id=b.id

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where b.date_call between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
            and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 

and b.call_theme_id=1
".$sql_access_part." 

group by 
B.FIO_ID
,B.SERVICE_ID
,S.NAME
--B.SOURCE_AUTO_ID,
--SA.NAME,
--B.SOURCE_TYPE_ID,
--ST.NAME
--order by B.FIO_ID
-- SA.NAME, B.SOURCE_TYPE_ID
) a on a.fio_id=usr.id
-------------------------

 --пришедшие за период
left join 
(
select 
B.FIO_ID,
B.SERVICE_ID,
S.NAME SERVICE_NAME,
--B.SOURCE_AUTO_ID,
--SA.NAME SOURCE_AUTO_NAME,
--B.SOURCE_TYPE_ID,
--ST.NAME SOURCE_TYPE_NAME,

count(*) VISIT_BY_PER --Пришло за период

from  visit_hist vh, CALL_BASE b

left join source_auto sa on sa.id=b.source_auto_id
left join services s on s.id=b.service_id
left join source_type st on st.id=b.source_type_id

where vh.date_visit between to_date('".$_SESSION['reports']['start_date']."','DD.MM.YYYY') 
					  and to_date('".$_SESSION['reports']['end_date']."','DD.MM.YYYY')+1 
and b.id=vh.base_id
".$sql_access_part." 
group by 
B.FIO_ID
,B.SERVICE_ID
,S.NAME
--B.SOURCE_AUTO_ID,
--SA.NAME,
--B.SOURCE_TYPE_ID,
--ST.NAME
--order by SA.NAME, B.SOURCE_TYPE_ID
) a1 on a1.fio_id=a.fio_id and a1.service_id=a.service_id
--------------------------------

WHERE usr.deleted is null
and usr.role_id in (2,4) --операторы и супервайзеры
".($sel_opers_str==-1?"":"AND usr.id IN (".$sel_opers_str.")")."
".($_SESSION['user_role']==1?"":"AND usr.id IN 
(select uda.user_id from USER_DEP_ALLOC uda where uda.dep_id in (SELECT distinct dep_id FROM user_dep_alloc WHERE DELETED is null and user_id ='".$_SESSION['login_id_med']."'))")."
order by usr.fio,a.SERVICE_NAME";

//echo "<textarea>".$sql."</textarea>";
//exit();

//в эксель
if(isset($xlsx)) {
	require_once 'sql_to_xlsx.php';
	$sheets[0]['sql']=$sql;
	$sheets[0]['filter']='y';
	//$sheets[0]['name']='Приходы';
	$sheets[0]['head']='Приходы по операторам '.$_SESSION['reports']['start_date']." - ".$_SESSION['reports']['end_date'].". На ".date('d.m.Y');
	$sheets[0]['sum']=array(3,4);
	sql_to_xlsx($c,$sheets,'report_visits');
	exit();
}

//в csv
if(isset($csv)) {
	require_once 'sql_to_csv.php';
	sql_to_csv($c,$sql,'report_visits');
	exit();
}


?>