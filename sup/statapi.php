<?php
include("sup/sup_conn_string");


//1
$q=OCIParse($c,"select count(*) alll, count(texnari_id) inwork, count(*)-count(texnari_id) opene from 
(select distinct b.id,
       b.date_in_call d,
                   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI') date_in_call,
       k.name,
       k.id location_id,
                   slg.name loc_grp_name,
                   b.trbl_type_id,
                   tt.name trbl_name,
                   stg.name trbl_grp_name,
                   t.fio,
                   t.id texnari_id,
       b.kto,
                   b.kto_id,
       b.u_kogo,
       b.oper_comment,
                   nvl(to_char(b.in_work,'MISS'),0)+nvl(to_char(b.date_close,'MISS'),0)+nvl(to_char(b.ready_to_close,'MISS'),0)+nvl(to_char(b.delay_to,'MMDD'),0) checksum,
       case
         when b.delay_to>sysdate then 300 --Отложена
                               when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then 100 --Открыта
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then 200 --В работе
         when b.date_close is null and b.ready_to_close is not null then 400 --Гот.к пров.
                               when b.date_close is not null then 500 --Закрыта
       end status_id,
     '<b>'||to_char(trunc((nvl(b.date_close,sysdate)-b.date_in_call)))||'</b>д. <b>'||
     to_char(trunc(((nvl(b.date_close,sysdate)-b.date_in_call)-trunc((nvl(b.date_close,sysdate)-b.date_in_call)))*24))||'</b>ч.' dur,
                                b.quality,
       case
                     when b.quality='1' then 'red'  
                                when b.quality='2' then 'red'
                               when b.quality='3' then '#CC6633'
                               when b.quality='4' then '#339966'
                               when b.quality='5' then 'green'
       end q_color,
                   b.quality_who,
                   b.quality_coment,
                   ph.phone,
                   b.cdpn,
                   b.dublikat,
                   b.krivie_ruki
                   from sup_base b, sup_klinika k, sup_user t, sup_trbl_type tt, sup_klinika_phones ph, sup_location_group slg, SUP_TRBL_GROUP stg,sup_lt slt 
                where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   and slg.id=k.location_grp_id
   and stg.id=tt.trbl_grp_id
   and k.id=slt.location_id and tt.id=slt.trbl_id 
   and (1=2 
 or slt.lt_grp_id in (20,6,5,14) 
 or (b.texnari_id is NULL and slt.lt_grp_id in (20,6,5,14)) 
 or (b.texnari_id='7') or b.kto_id='7')
and (b.date_in_call>to_date('02.10.2020','DD.MM.YYYY') or b.date_close is null)  and (b.date_in_call<to_date('02.11.2020','DD.MM.YYYY')+1 or b.date_close is null)  and b.date_close is null  and nvl(b.delay_to,sysdate)<=sysdate)");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);

$all = OCIResult($q,"ALLL");
$inwork = OCIResult($q,"INWORK");
$opened = OCIResult($q,"OPENE");

echo '[{"all": '.$all.',"inwork": '.$inwork.',"opened": '.$opened.'}]';
?>