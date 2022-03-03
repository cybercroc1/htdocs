<?php
require_once 'med/check_auth.php';
require_once "med/conn_string.cfg.php";
extract($_REQUEST);
$_SESSION['reports']['date']=$rep_date;
//общие данные
$sql="select
ser.name,
count(*) vse,
count(case when b.status_id in ('10','3','6') and nvl(b.call_double,0)<2 and b.interstate is null then 1 else NULL end) ORDER_COUNT,
count(decode(ms.situation,'В работе',1,NULL)) as inwork

from CALL_BASE b
left join SOURCE_AUTO s on b.source_auto_id = s.id 
left join services ser on b.service_id = ser.id 
left join suppliers sp on s.supplier_id = sp.id
left join MED_STATUS ms on b.status_id = ms.id
where b.call_theme_id = 1
and date_call > to_date('$rep_date','DD.MM.YYYY')
-----------------------------------------------------------------
and (
b.source_auto_id,
b.source_man_id,
b.source_type_id,
b.service_id
) 
in 
(
select 
decode(ACCESS_DEP.source_auto_id,-1,b.source_auto_id,ACCESS_DEP.source_auto_id),
decode(ACCESS_DEP.source_man_id,-1,b.source_man_id,ACCESS_DEP.source_man_id),
decode(ACCESS_DEP.source_type_id,-1,b.source_type_id,ACCESS_DEP.source_type_id),
decode(ACCESS_DEP.service_id,-1,b.service_id,ACCESS_DEP.service_id)

from USER_DEP_ALLOC, DEPARTAMENTS, ACCESS_DEP
where 1=1
and USER_DEP_ALLOC.user_id=
'".$_SESSION['login_id_med']."' 
and USER_DEP_ALLOC.deleted is null
and DEPARTAMENTS.id=USER_DEP_ALLOC.dep_id and DEPARTAMENTS.deleted is null
and ACCESS_DEP.departament_id=DEPARTAMENTS.id 
)
-----------------------------------------------------------
GROUP BY 
ser.name
ORDER BY ser.name";
$q=OCIParse($c,$sql);
OCIExecute($q);
echo "<table border=1>";
echo "<th>Услуга</th><th>Все заявки</th><th>Принятые</th><th>В работе</th>";
while(OCIFetch($q)) {
    echo "<tr>";
        echo "<td align=center>";
            echo OCIResult($q,"NAME");
        echo "</td>"; 
        echo "<td align=center>";
            echo OCIResult($q,"VSE");
        echo "</td>";
        echo "<td align=center>";
            echo OCIResult($q,"ORDER_COUNT");
        echo "</td>";
        echo "<td align=center>";
            echo OCIResult($q,"INWORK");
        echo "</td>";
    echo "</tr>";
}
echo "</table>";


//источники(Сбор массива источников)
$source_auto=array();
$sql="select sp.id sup_id,sp.sup_name, sa.id source_id,sa.name source_name,sa.source_type 
from SOURCE_AUTO sa 
left join suppliers sp on sa.supplier_id = sp.id
where sa.id > 0 and sa.deleted is null 
".($_SESSION['user_role']==1?"":"and sa.id in (select decode(ad.source_auto_id,-1,sa.id,ad.source_auto_id)
from USER_DEP_ALLOC uda, DEPARTAMENTS d, ACCESS_DEP ad
where uda.user_id='".$_SESSION['login_id_med']."' and uda.deleted is null
and d.id=uda.dep_id and d.deleted is null and ad.departament_id=d.id)").
"order by sa.name,sa.source_type";
$q=OCIParse($c,$sql);
OCIExecute($q);
$tmp_supp_id='';
$i=0; while(OCIFetch($q)) {
    $source_auto[OCIResult($q,"SUP_ID")]['sup_name']=OCIResult($q,"SUP_NAME");
    $source_auto[OCIResult($q,"SUP_ID")]['sources'][$i]['source_id']=OCIResult($q,"SOURCE_ID");
    $source_auto[OCIResult($q,"SUP_ID")]['sources'][$i]['source_name']=OCIResult($q,"SOURCE_NAME");
    $source_auto[OCIResult($q,"SUP_ID")]['sources'][$i]['source_type']=OCIResult($q,"SOURCE_TYPE");
    $i++;
}
//проверка массива
//print_r($source_auto);

//Запрос за данными
//Значения - ALL;ORDER_COUNT;IN_WORK
$sql="select
sp.id,
sp.sup_name,
s.id,
s.NAME,
count(*) vse,
count(case when b.status_id in ('10','3','6') and nvl(b.call_double,0)<2 and b.interstate is null then 1 else NULL end) ORDER_COUNT,
count(decode(ms.situation,'В работе',1,NULL)) as inwork

from CALL_BASE b
left join SOURCE_AUTO s on b.source_auto_id = s.id 
left join services ser on b.service_id = ser.id 
left join suppliers sp on s.supplier_id = sp.id
left join MED_STATUS ms on b.status_id = ms.id
where b.call_theme_id = 1
and s.id = :source_id
and date_call > to_date('$rep_date','DD.MM.YYYY')
-----------------------------------------------------------------
and (
b.source_auto_id,
b.source_man_id,
b.source_type_id,
b.service_id
) 
in 
(
select 
decode(ACCESS_DEP.source_auto_id,-1,b.source_auto_id,ACCESS_DEP.source_auto_id),
decode(ACCESS_DEP.source_man_id,-1,b.source_man_id,ACCESS_DEP.source_man_id),
decode(ACCESS_DEP.source_type_id,-1,b.source_type_id,ACCESS_DEP.source_type_id),
decode(ACCESS_DEP.service_id,-1,b.service_id,ACCESS_DEP.service_id)

from USER_DEP_ALLOC, DEPARTAMENTS, ACCESS_DEP
where 1=1
and USER_DEP_ALLOC.user_id=
'".$_SESSION['login_id_med']."' 
and USER_DEP_ALLOC.deleted is null
and DEPARTAMENTS.id=USER_DEP_ALLOC.dep_id and DEPARTAMENTS.deleted is null
and ACCESS_DEP.departament_id=DEPARTAMENTS.id 
)
-----------------------------------------------------------
GROUP BY 
sp.id,
sp.sup_name,
s.id,
s.NAME
ORDER BY sp.sup_name";
$q=OCIParse($c,$sql);

$sql2 = "select
ser.name,
count(*) vse,
count(case when b.status_id in ('10','3','6') and nvl(b.call_double,0)<2 and b.interstate is null then 1 else NULL end) ORDER_COUNT,
count(decode(ms.situation,'В работе',1,NULL)) as inwork

from CALL_BASE b
left join SOURCE_AUTO s on b.source_auto_id = s.id 
left join services ser on b.service_id = ser.id 
left join suppliers sp on s.supplier_id = sp.id
left join MED_STATUS ms on b.status_id = ms.id
where b.call_theme_id = 1
and s.supplier_id = :supplier_id
and date_call > to_date('$rep_date','DD.MM.YYYY')
-----------------------------------------------------------------
and (
b.source_auto_id,
b.source_man_id,
b.source_type_id,
b.service_id
) 
in 
(
select 
decode(ACCESS_DEP.source_auto_id,-1,b.source_auto_id,ACCESS_DEP.source_auto_id),
decode(ACCESS_DEP.source_man_id,-1,b.source_man_id,ACCESS_DEP.source_man_id),
decode(ACCESS_DEP.source_type_id,-1,b.source_type_id,ACCESS_DEP.source_type_id),
decode(ACCESS_DEP.service_id,-1,b.service_id,ACCESS_DEP.service_id)

from USER_DEP_ALLOC, DEPARTAMENTS, ACCESS_DEP
where 1=1
and USER_DEP_ALLOC.user_id=
'".$_SESSION['login_id_med']."' 
and USER_DEP_ALLOC.deleted is null
and DEPARTAMENTS.id=USER_DEP_ALLOC.dep_id and DEPARTAMENTS.deleted is null
and ACCESS_DEP.departament_id=DEPARTAMENTS.id 
)
-----------------------------------------------------------
GROUP BY 
ser.name
ORDER BY ser.name";
$q2=OCIParse($c,$sql2);

foreach($source_auto as $sup_id=>$supl){
    echo "<h2>".$supl['sup_name']."</h2>";
    echo "<br>";
    echo "<table border=1>";
    echo "<th>Источник рекламы</th><th>Всего обращений</th><th>Принятых</th><th>В работе</th>";
    $vse = 0;
    $order_count= 0;
    $inwork = 0;
    foreach($supl['sources'] as $key => $sources){
        echo "<tr>";
        echo "<td>";
        echo $sources['source_name']."</td>";
        OCIBindByName($q,':source_id',$sources['source_id']);
        OCIExecute($q);
        if(OCIFetch($q)) {
            echo "<td align=center>";
            echo OCIResult($q, "VSE")."</td>";
            echo "<td align=center>";
            echo OCIResult($q, "ORDER_COUNT")."</td>";
            echo "<td align=center>";
            echo OCIResult($q, "INWORK")."</td>";
            $vse = $vse + OCIResult($q, "VSE");
            $order_count = $order_count + OCIResult($q, "ORDER_COUNT");
            $inwork = $inwork + OCIResult($q, "INWORK");
        }
        else{
            echo "<td align=center>0</td><td align=center>0</td><td align=center>0</td>";
        }
        echo "</tr>";
    }
    echo "<tr><td align=right style='font-weight: bold;'>Всего</td><td align=center>$vse</td><td align=center>$order_count</td><td align=center>$inwork</td></tr>";
    OCIBindByName($q2,':supplier_id',$sup_id);
    OCIExecute($q2);
    while(OCIFetch($q2)) {
        echo "<tr><td align=right style='font-weight: bold;'>".OCIResult($q2, "NAME")."</td>";
        echo "<td align=center>";
        echo OCIResult($q2, "VSE")."</td>";
        echo "<td align=center>";
        echo OCIResult($q2, "ORDER_COUNT")."</td>";
        echo "<td align=center>";
        echo OCIResult($q2, "INWORK")."</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</br>";
}




?>