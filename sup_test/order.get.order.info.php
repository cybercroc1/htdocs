<?php
//функция возвращает все данные о заявке, включая права текущего пользователя

function get_order_info($c,$base_id) {
	$base=array();
	//информация о заявке
	$sql="select aaa.*,bbb.* from (select b.id,
         to_char(b.date_in_call, 'DD.MM.YYYY HH24:MI') date_in_call,
         b.cdpn,
         b.klinika_id,
       b.texnari_id,
         k.name,
       k.phone,
         b.kto,
       b.kto_id,
         b.oper_comment,
         b.u_kogo,
       b.quality,
       b.quality_coment,
       b.quality_who,
       b.ip_address,
       b.ready_to_close,
       b.date_close,
	   case when b.delay_to>sysdate then 'y' else NULL end delayed,
       b.trbl_type_id,
       b.trbl_detail_id,
	   to_char(b.last_change,'YYYYDDMMHH24MISS') last_change,
       tt.name trbl_type_name,
           td.name trbl_detail_name,
       case  
       when b.quality='1' then 'red'
       when b.quality='2' then 'red'
       when b.quality='3' then '#CC6633'
       when b.quality='4' then '#339966'
       when b.quality='5' then 'green'
         end q_color,
       b.dublikat,
       b.krivie_ruki     
    from sup_base b, sup_klinika k, sup_trbl_type tt, sup_trbl_detail td
   where b.klinika_id=k.id (+)
     and tt.id(+)=b.trbl_type_id
     and td.id(+)=b.trbl_detail_id   
   and b.id = '".$base_id."') aaa,
   (select slt.location_id,slt.trbl_id, max(sla.create_new) create_new, max(sla.solution) solution, max(sla.deny_close) deny_close, max(sla.redirect) redirect, max(sla.look) look, max(eval) eval 
   from  sup_lt slt, sup_user_lt_alloc sla
   where sla.lt_group_id=slt.lt_grp_id and sla.user_id='".$_SESSION['user_id']."' 
   group by slt.location_id,slt.trbl_id) bbb
   where bbb.location_id(+)=aaa.klinika_id and bbb.trbl_id(+)=aaa.trbl_type_id";
	$q=OCIParse($c,$sql);
	//echo "<textarea>".$sql."</textarea>";
	
	OCIExecute($q,OCI_DEFAULT);

	if(OCIFetch($q)) {
		$base['last_change']=OCIResult($q,"LAST_CHANGE"); //для проверки модификации заявки при сохранении
		$base['aon']=OCIResult($q,"CDPN");
		$base['ip_addr']=OCIResult($q,"IP_ADDRESS");
		$base['author_id']=OCIResult($q,"KTO_ID"); //ID автора заявки
		$base['author_name']=OCIResult($q,"KTO"); 
		$base['location_id']=OCIResult($q,"KLINIKA_ID");
		$base['location_name']=OCIResult($q,"NAME");
		$base['location_phone']=OCIResult($q,"PHONE");
		$base['trbl_id']=OCIResult($q,"TRBL_TYPE_ID");
		$base['trbl_name']=OCIResult($q,"TRBL_TYPE_NAME");
		$base['trbl_det_id']=OCIResult($q,"TRBL_DETAIL_ID");
		$base['trbl_det_name']=OCIResult($q,"TRBL_DETAIL_NAME");

		$base['from_user_id']=OCIResult($q,"TEXNARI_ID"); //текущий исполнитель
		
		$base['u_kogo']=OCIResult($q,"U_KOGO");
		$base['coment']=OCIResult($q,"OPER_COMMENT");
		
		$base['date_close']=OCIResult($q,"DATE_CLOSE");
		$base['ready_to_close']=OCIResult($q,"READY_TO_CLOSE");
		$base['delayed']=OCIResult($q,"DELAYED");
		$base['date_in_call']=OCIResult($q,"DATE_IN_CALL");
		$base['dublikat']=OCIResult($q,"DUBLIKAT");
		$base['krivie_ruki']=OCIResult($q,"KRIVIE_RUKI");
		
		$base['create_new']=OCIResult($q,"CREATE_NEW");
		$base['solution']=OCIResult($q,"SOLUTION");
		$base['redirect']=OCIResult($q,"REDIRECT");
		$base['deny_close']=OCIResult($q,"DENY_CLOSE");
		$base['look']=OCIResult($q,"LOOK");
		$base['eval']=OCIResult($q,"EVAL");
		
		$base['quality']=OCIResult($q,"QUALITY");
		$base['quality_who']=OCIResult($q,"QUALITY_WHO");
		$base['quality_coment']=OCIResult($q,"QUALITY_COMENT");	
		$base['q_color']=OCIResult($q,"Q_COLOR");
	
		$base['author']=''; //пользователь является автором заявки
		if($base['author_id']==$_SESSION['user_id']) $base['author']='y';
	
		$base['executor']=''; //пользователь является исполнителем заявки
		if($base['from_user_id']==$_SESSION['user_id']) $base['executor']='y';
	
		$base['opened']=''; //открытая заявка (без исполнителя)
		if($base['from_user_id']=='') $base['opened']='y';
	
		return $base;
	}
	else {
		$base['error']="<font color=red><b>ОШИБКА: Такой заявки не существует или у вас нет прав для доступа к ней</b></font>"; 
		return $base;
	}
}
?>