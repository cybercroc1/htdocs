<?php
extract($_REQUEST);
session_start();
$sid=session_id();

if (!isset($_SESSION['auth'])) {
	echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра данной страницы. Вы не прошли авторизацию</b></font>";
	exit();
}
if (!isset($base_id) or $base_id=='') {exit();}

include("sup/sup_conn_string");

//информация о заявке
include("order.get.order.info.php");
extract(get_order_info($c,$base_id));
if(isset($error)) {echo $error; exit();}

$need_save_button='';
$need_coment='';

//списки местоположений и проблем
if($executor=='y' 
or ($redirect=='y' and ($opened=='y' or $author=='y')) 
or ($solution=='y' and ($opened=='y' or $author=='y'))
or ($look=='y' and ($redirect=='y' or $solution=='y'))) {
	$location_id_opts='';
	$location_id_HTML='';
	//список местоположений
	if($redirect=='y') {//можно выбрать только локации из групп, в которых есть стрелочники или исполнители
		$q=OCIParse($c,"
		select distinct k.id,lg.id group_id,lg.name group_name,k.name location_name from sup_user_lt_alloc sla,sup_lt lt,sup_user u,SUP_LOCATION_GROUP lg, sup_klinika k
		where sla.lt_group_id=lt.lt_grp_id
		and (sla.redirect='y' or sla.solution='y')
	    and u.id=sla.user_id
	    and u.deleted is null
	    and k.id=lt.location_id
	    and k.location_grp_id=lg.id
		and (k.deleted is null or k.id=".$new_location_id.")
		order by lg.name,k.name");
	} else { //если пользователь не имеет права переадресовывать, то он может выбрать только те локации, где имеет права решать проблемы
		$q=OCIParse($c,"select k.id,lg.id group_id,lg.name group_name,k.name location_name from SUP_LOCATION_GROUP lg, sup_klinika k
 		where k.location_grp_id=lg.id
  	 	and k.id in (select lt.location_id from sup_lt lt, sup_user_lt_alloc sla where sla.user_id=".$_SESSION['user_id']." and sla.solution='y' and lt.lt_grp_id=sla.lt_group_id)
		and (k.deleted is null or k.id=".$new_location_id.")
  		order by lg.name,k.name");	
	}
	OCIExecute($q,OCI_DEFAULT);
	//набор опций для выбора местоположения
	$l=0; while (OCIFetch($q)) {$l++;
		if(!isset($tmp) or $tmp<>OCIResult($q,"GROUP_ID")) {
			$location_id_opts.="<optgroup label='".OCIResult($q,"GROUP_NAME")."'></optgroup>";
		}
		$location_id_opts.="<option value='".OCIResult($q,"ID")."'";
		if(OCIResult($q,"ID")==$location_id) $location_id_opts.=" style='color:green'";
		if(OCIResult($q,"ID")==$new_location_id) $location_id_opts.=" selected";
		$location_id_opts.=">".OCIResult($q,"LOCATION_NAME")."</option>";	
		$tmp=OCIResult($q,"GROUP_ID");
	}
	if($l>0) {
		$need_save_button='y';
		$location_id_HTML.="Местоположение: <select name=new_location_id onchange=ch_loc_trbl()>";
		$location_id_HTML.=$location_id_opts;
		$location_id_HTML.="</select>";
	}
	else {
		$location_id_HTML.="Местоположение: <b>".$location_name."</b>";		
	}
	
	echo "<script>
	parent.document.getElementById('div_location_id').innerHTML='".str_replace("'","\'",$location_id_HTML)."';
	</script>";

	//список типов проблем
	if($redirect=='y') { //можно выбрать только типы проблем из групп, в которых есть стрелочники или исполнители
		$q=OCIParse($c,"select distinct tg.id group_id,tg.name group_name,t.id trbl_id,t.name trbl_name,t.ord from sup_lt lt, sup_user_lt_alloc sla,sup_user u, SUP_TRBL_TYPE t, Sup_Trbl_Group tg
	    where lt.location_id=".$new_location_id."
	    and lt.lt_grp_id<>0
	    and sla.lt_group_id=lt.lt_grp_id
		and (sla.redirect='y' or sla.solution='y')
	    and u.id=sla.user_id
	    and u.deleted is null
	    and t.id=lt.trbl_id
	    and t.trbl_grp_id=tg.id 
	    and (t.deleted is null or t.id='".$new_trbl_type_id."')
	    order by tg.name,t.name");
	} 
	else { //если пользователь не имеет права переадресовывать, то он может выбрать только те проблемы, которые имеет права решать сам
		$q=OCIParse($c,"select distinct tg.id group_id,tg.name group_name,t.id trbl_id,t.name trbl_name,t.ord 
		from sup_lt lt, sup_user_lt_alloc sla ,SUP_TRBL_TYPE t, Sup_Trbl_Group tg
	    where lt.location_id=".$new_location_id."
	    and t.id=lt.trbl_id
	    and sla.lt_group_id=lt.lt_grp_id
		and lt.lt_grp_id<>0
	   	and sla.user_id=".$_SESSION['user_id']." and sla.solution='y'
	    and t.trbl_grp_id=tg.id 
	    and (t.deleted is null or t.id='".$new_trbl_type_id."')
   		order by tg.name,t.name");
	}
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	//набор опций для выбора проблемы
	$trbl_type_id_opts='<option></option>';
	$trbl_type_id_HTML='';
	$t=0; while (OCIFetch($q)) {$t++;
		if(!isset($tmp) or $tmp<>OCIResult($q,"GROUP_ID")) {
			$trbl_type_id_opts.="<optgroup label='".OCIResult($q,"GROUP_NAME")."'></optgroup>";
		}
		$trbl_type_id_opts.="<option value='".OCIResult($q,"TRBL_ID")."'";
		if(OCIResult($q,"TRBL_ID")==$trbl_id) $trbl_type_id_opts.=" style='color:green'";
		if(OCIResult($q,"TRBL_ID")==$new_trbl_type_id) $trbl_type_id_opts.=" selected";
		$trbl_type_id_opts.=">".OCIResult($q,"TRBL_NAME")."</option>";	
		$tmp=OCIResult($q,"GROUP_ID");
	}
	if($l>0) {
		$need_save_button='y';
		$trbl_type_id_HTML.="Тип проблемы: <font color=red>ВНИМАНИЕ! Не забудьте уточнить тип проблемы!</font><br>";
		$trbl_type_id_HTML.="<select name='new_trbl_type_id' onchange=ch_loc_trbl()>";
		$trbl_type_id_HTML.=$trbl_type_id_opts;
		$trbl_type_id_HTML.="</select>";
	}
	else {
		$trbl_type_id_HTML.="Тип проблемы: <b>".$trbl_name."</b>";
	}
	
	echo "<script>
	parent.document.getElementById('div_trbl_type').innerHTML='".str_replace("'","\'",$trbl_type_id_HTML)."';
	</script>";
	//
	
	//деталь проблемы
	$q=OCIParse($c,"select t.id,t.name from SUP_TRBL_DETAIL t
	where t.trbl_id='".$new_trbl_type_id."' and (t.deleted is null or t.id='".$new_trbl_det_id."')
	order by name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	//набор опций для выбора детали проблемы
	$trbl_detail_opts='';
	$trbl_detail_HTML='';
	while (OCIFetch($q)) {
		$trbl_detail_opts.="<option value='".OCIResult($q,"ID")."'";
		if(OCIResult($q,"ID")==$trbl_det_id) $trbl_detail_opts.=" style='color:green'";
		if(OCIResult($q,"ID")==$new_trbl_det_id) $trbl_detail_opts.=" selected";
		$trbl_detail_opts.=">".OCIResult($q,"NAME")."</option>";
		//echo 	OCIResult($q,"NAME");
	}
	
	if($trbl_detail_opts<>'') {
		$trbl_detail_HTML.="точнее: <select name=new_trbl_det_id><option value='' style='color:red'>выберите, что конкретно?</option>";
		$trbl_detail_HTML.=$trbl_detail_opts;
		$trbl_detail_HTML.="</select>";	
		
		echo "<script>
		parent.document.getElementById('div_trbl_detail').innerHTML='".str_replace("'","\'",$trbl_detail_HTML)."';
		</script>";
	}
	else {
		echo "<script>
		parent.document.getElementById('div_trbl_detail').innerHTML='';
		</script>";		
	}
}
//

//оценить
$eval_innerHTML='';
if(($eval=='y' and ($look=='y' or $author=='y')) and $executor<>'y' and $opened<>'y' and $delayed<>'y') {
	$need_save_button='y';
	$eval_innerHTML.="<font size=3><b>Оценка: </b></font><select name=new_quality onchange=fn_check()><option></option>";
	$eval_innerHTML.="<option style='color:red' value='1'>1</option>";
	$eval_innerHTML.="<option style='color:red' value='2'>2</option>";
	$eval_innerHTML.="<option style='color:#CC6633' value='3'>3</option>";
	$eval_innerHTML.="<option style='color:#339966' value='4'>4</option>";
	$eval_innerHTML.="<option style='color:green' value='5'>5</option>";
	$eval_innerHTML.="</select><hr>";
}
echo "<script>
parent.document.getElementById('div_eval').innerHTML='".str_replace("'","\'",$eval_innerHTML)."';
</script>";
//

//трудозатраты, оценненные исполнителем
$turdozatarti_isp_innerHTML='';
if($trudozatrati=='y' and $executor=='y') {
	$q=OCIParse($c,"select t.trudozatrati_ispolnitel from SUP_BASE t where id='".$base_id."'");
	OCIExecute($q);
	OCIFetch($q);
	$old_turdozatarti_isp=OCIResult($q,'TRUDOZATRATI_ISPOLNITEL');
		
	$need_save_button='y';
	$turdozatarti_isp_innerHTML.="<input type=hidden name=old_turdozatarti_isp value='".$old_turdozatarti_isp."'></input>";
	$turdozatarti_isp_innerHTML.="<font size=3><b>Трудозатраты, оцененные исполнителем: </b></font><select name=new_turdozatarti_isp onchange=fn_check()>";

	$turdozatarti_isp_innerHTML.="<option></option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='5'?' selected':'')." value='5'>5 минут</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='10'?' selected':'')." value='10'>10 минут</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='15'?' selected':'')." value='15'>15 минут</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='30'?' selected':'')." value='30'>30 минут</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='45'?' selected':'')." value='45'>45 минут</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='60'?' selected':'')." value='60'>1 час</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='90'?' selected':'')." value='90'>1,5 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='120'?' selected':'')." value='120'>2 час</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='150'?' selected':'')." value='150'>2,5 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='180'?' selected':'')." value='180'>3 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='210'?' selected':'')." value='210'>3,5 часа</option>";	
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='240'?' selected':'')." value='240'>4 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='270'?' selected':'')." value='270'>4,5 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='300'?' selected':'')." value='300'>5 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='360'?' selected':'')." value='360'>6 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='420'?' selected':'')." value='420'>7 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='480'?' selected':'')." value='480'>8 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='540'?' selected':'')." value='540'>9 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='600'?' selected':'')." value='600'>10 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='660'?' selected':'')." value='660'>11 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='720'?' selected':'')." value='720'>12 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='780'?' selected':'')." value='780'>13 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='840'?' selected':'')." value='840'>14 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='900'?' selected':'')." value='900'>15 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1020'?' selected':'')." value='1020'>17 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1080'?' selected':'')." value='1080'>18 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1140'?' selected':'')." value='1140'>19 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1200'?' selected':'')." value='1200'>20 часов</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1260'?' selected':'')." value='1260'>21 час</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1320'?' selected':'')." value='1320'>22 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1380'?' selected':'')." value='1380'>23 часа</option>";
	$turdozatarti_isp_innerHTML.="<option".($old_turdozatarti_isp=='1440'?' selected':'')." value='1440'>24 часа</option>";
	$turdozatarti_isp_innerHTML.="</select><hr>";
}
echo "<script>
parent.document.getElementById('div_turdozatarti_isp').innerHTML='".str_replace("'","\'",$turdozatarti_isp_innerHTML)."';
</script>";
//

//Трудозатраты, одобренные заказчиком
$turdozatarti_zak_innerHTML='';
if($trudozatrati=='y' and ($author=='y' or $look=='y')) {
	$q=OCIParse($c,"select t.trudozatrati_zakazchik from SUP_BASE t where id='".$base_id."'");
	OCIExecute($q);
	OCIFetch($q);
	$old_turdozatarti_zak=OCIResult($q,'TRUDOZATRATI_ZAKAZCHIK');
		
	$need_save_button='y';
	$turdozatarti_zak_innerHTML.="<input type=hidden name=old_turdozatarti_zak value='".$old_turdozatarti_zak."'></input>";
	$turdozatarti_zak_innerHTML.="<font size=3><b>Трудозатраты, одобренные заказчиком: </b></font><select name=new_turdozatarti_zak onchange=fn_check()>";

	$turdozatarti_zak_innerHTML.="<option></option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='5'?' selected':'')." value='5'>5 минут</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='10'?' selected':'')." value='10'>10 минут</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='15'?' selected':'')." value='15'>15 минут</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='30'?' selected':'')." value='30'>30 минут</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='45'?' selected':'')." value='45'>45 минут</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='60'?' selected':'')." value='60'>1 час</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='90'?' selected':'')." value='90'>1,5 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='120'?' selected':'')." value='120'>2 час</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='150'?' selected':'')." value='150'>2,5 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='180'?' selected':'')." value='180'>3 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='210'?' selected':'')." value='210'>3,5 часа</option>";	
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='240'?' selected':'')." value='240'>4 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='270'?' selected':'')." value='270'>4,5 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='300'?' selected':'')." value='300'>5 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='360'?' selected':'')." value='360'>6 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='420'?' selected':'')." value='420'>7 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='480'?' selected':'')." value='480'>8 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='540'?' selected':'')." value='540'>9 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='600'?' selected':'')." value='600'>10 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='660'?' selected':'')." value='660'>11 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='720'?' selected':'')." value='720'>12 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='780'?' selected':'')." value='780'>13 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='840'?' selected':'')." value='840'>14 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='900'?' selected':'')." value='900'>15 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1020'?' selected':'')." value='1020'>17 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1080'?' selected':'')." value='1080'>18 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1140'?' selected':'')." value='1140'>19 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1200'?' selected':'')." value='1200'>20 часов</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1260'?' selected':'')." value='1260'>21 час</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1320'?' selected':'')." value='1320'>22 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1380'?' selected':'')." value='1380'>23 часа</option>";
	$turdozatarti_zak_innerHTML.="<option".($old_turdozatarti_zak=='1440'?' selected':'')." value='1440'>24 часа</option>";
	$turdozatarti_zak_innerHTML.="</select><hr>";
}
echo "<script>
parent.document.getElementById('div_turdozatarti_zak').innerHTML='".str_replace("'","\'",$turdozatarti_zak_innerHTML)."';
</script>";
//


//дубликат, ошибка
$dubl_innerHTML='';
if($solution=='y' and ($opened=='y' or $executor=='y')) {
	$need_save_button='y';
	$dubl_innerHTML.="<input type=hidden name='new_dublikat'><input type='checkbox' name='new_dublikat' value='y'"; 
	$dubl_innerHTML.=($dublikat?" checked":""); 
	$dubl_innerHTML.="><font color=red>Дубликат</font></input>";
	$dubl_innerHTML.=" | <input type=hidden name='new_krivie_ruki'><input type='checkbox' name='new_krivie_ruki' value='y'"; 
	$dubl_innerHTML.=($krivie_ruki?" checked":""); 
	$dubl_innerHTML.="><font color=red>Ошибка пользователя</font></input>";
	$dubl_innerHTML.="<hr>";
}
echo "<script>
parent.document.getElementById('div_dubl').innerHTML='".str_replace("'","\'",$dubl_innerHTML)."';
</script>";
//

$to_user_id_HTML='';
//список технарей для переадресации
$to_user_arr=array();
	
//опция "комментировать"
if($author=='y' or $executor=='y' or ($redirect=='y' and $opened=='y') or ($look=='y' and ($solution=='y' or $create_new=='y' or $redirect=='y')) ) {
	$to_user_arr['coment']['option_name']='оставить комментарий';
	$to_user_arr['coment']['button_name']='Комменитровать';
	$to_user_arr['coment']['color']='indigo';
	$to_user_arr['coment']['selected']='';
	//если исполнитель, то комментарий по умолчанию
	if($executor=='y') $to_user_arr['coment']['selected']=' selected';
}
	
//опция "принять в работу"
if($executor<>'y' and (($solution=='y' and $opened=='y') or ($look=='y' and $solution=='y'))) {
	$to_user_arr['to_work']['option_name']='принять в работу';
	$to_user_arr['to_work']['button_name']='Принять в работу';
	$to_user_arr['to_work']['color']='green';
	$to_user_arr['to_work']['selected']='';
	//для открытых заявок и исполнителя "принять в работу" по умолчиню
	if($opened=='y' and $solution=='y') $to_user_arr['to_work']['selected']=' selected';
}

//опции выбора пользователя для переадресации
if(($redirect=='y' and $opened=='y') or ($look=='y' and $redirect=='y')){	
	$q=OCIParse($c,"select distinct su.id, su.fio from sup_lt slt, sup_user_lt_alloc sla, sup_user su
	where slt.location_id=".$new_location_id." and slt.trbl_id='".$new_trbl_type_id."'
	and sla.lt_group_id=slt.lt_grp_id
	and sla.solution='y'
	and su.id=sla.user_id
	and su.deleted is null
	and su.id<>".$_SESSION['user_id']."
	order by su.fio");
	OCIExecute($q,OCI_DEFAULT);
	//набор опций для выбора пользователей для переадресации
	while (OCIFetch($q)) {
		$to_user_arr[OCIResult($q,"ID")]['option_name']=OCIResult($q,"FIO");
		$to_user_arr[OCIResult($q,"ID")]['button_name']='Переадресовать';
		$to_user_arr[OCIResult($q,"ID")]['color']='maroon';
		$to_user_arr[OCIResult($q,"ID")]['selected']='';
	}
}
	
//опция "переадресовать на группу (открыть)"
if($look=='y' and $redirect=='y') {
	$to_user_arr['open']['option_name']='переадресовать на группу инженеров (открыть заявку)';
	$to_user_arr['open']['button_name']='Открыть заявку';
	$to_user_arr['open']['color']='blue';
	$to_user_arr['open']['selected']='';
}
//
if(count($to_user_arr)==1) {
	foreach($to_user_arr as $key=>$val) {
		$need_save_button='n';
		$to_user_id_HTML.="<nobr><input type='hidden' name='to_user_id' value='".$key."'> <input type=submit name=save style='background-color:".$to_user_arr[$key]['color']."' value='".$to_user_arr[$key]['button_name']."'></nobr>";
	}
}
elseif(count($to_user_arr)>1) {
	if($need_save_button<>'n') $need_save_button='y';
	$to_user_id_HTML.="<nobr><font color=indigo>Комментировать</font> / <font color=maroon>Переадресовать</font> / <font color=green>Принять в работу</font>: </nobr><br>";
	$to_user_id_HTML.="<nobr>";
	$to_user_id_HTML.="<select name=to_user_id onchange=fn_check()>";
	foreach($to_user_arr as $key=>$val) { 
		$to_user_id_HTML.="<option value='".$key."' style='color:".$to_user_arr[$key]['color']."'".$to_user_arr[$key]['selected'].">".$to_user_arr[$key]['option_name']."</option>";
	}
	$to_user_id_HTML.="<select> ";
}

//кнопка SAVE
if($need_save_button=='y') {
	$need_coment='y';
	$to_user_id_HTML.="<input type=submit name=save style='color: white; font-weight: bold; background-color:#66FF66' value='Сохранить'></nobr>";
}
echo "<script>
parent.document.getElementById('div_save').innerHTML='".str_replace("'","\'",$to_user_id_HTML)."';
</script>";


//кнопки
$buttons_HTML='';
$buttons_HTML.="<hr><nobr>";

//кнопка готово к проверке
if($deny_close=='y' and $date_close=='' and $ready_to_close=='' 
and ($executor=='y' 
or ($solution=='y' and ($opened=='y' or $author=='y' or $executor=='y'))
or ($look=='y' and ($redirect=='y' or $solution=='y')))) {
	$need_coment='y';
	$buttons_HTML.="<input type=submit name=ready_z style='background-color:#458B00' value='Готово к проверке'> | ";
}

//кнопка закрыть
if($deny_close<>'y' and $date_close=='' 
and ($executor=='y' 
or ($solution=='y' and ($opened=='y' or $author=='y' or $executor=='y'))
or ($look=='y' and ($redirect=='y' or $solution=='y')))) {
	$need_coment='y';
	$buttons_HTML.="<input type=submit name=close_z style='background-color:#FF5050' value='Закрыть заявку'> | ";
}

//кнопка возобновить
if(($delayed=='y' or $date_close<>'' or $ready_to_close<>'') 
and (($create_new=='y' and $author=='y') 
or ($redirect=='y' and $opened=='y') 
or ($solution=='y' and ($author=='y' or $executor=='y')) 
or ($look=='y' and ($redirect=='y' or $solution=='y')))) {
	$need_coment='y';
	$buttons_HTML.="<input type=submit name=resume_z style='background-color:yellow' value='Вернуть в работу'> | ";
}

//кнопка отложить
if($delayed<>'y' and $date_close=='' and $ready_to_close=='' 
and ($author=='y' 
or $executor=='y' 
or ($redirect=='y' and $opened=='y') 
or ($look=='y' and $redirect=='y'))) {
	$need_coment='y';
	$tomorrow=date('d.m.Y',mktime(0,0,0,date("m"),date("d")+1,date("Y")));
	$buttons_HTML.="<nobr><input type=text value='".$tomorrow."' size=8 name=delay_to_date style='background-color:#CC6633' onclick='if(self.gfPop)gfPop.fPopCalendar(this);return false; HIDEFOCUS' onchange=ok.click()>";
	$buttons_HTML.="<input type=submit name=delay_z style='background-color:#CC6633' value='Отложить'> | ";
}
echo "<script>
parent.document.getElementById('div_buttons').innerHTML='".str_replace("'","\'",$buttons_HTML)."';
</script>";
//

//комментарий и прикрепление файлов
$coment_innerHTML='';
if($need_coment=='y') {
	$coment_innerHTML.="Комментарий: ";
	$coment_innerHTML.="<br><textarea onkeyup=fn_check() style='width:98%' rows=5 name=tex_comment></textarea>";
	echo "<script>
	parent.document.getElementById('div_coment').innerHTML='".str_replace("'","\'",$coment_innerHTML)."';
	</script>";		
	//прикреплять файлы
	$add_file_innerHTML='';
	if($create_new=='y' or $redirect=='y' or $solution=='y' or $look=='y') {
		$add_file_innerHTML.= "Загрузите файл, перетащив его в данную область";
		$add_file_innerHTML.= "<input type='file' id='fileElem' multiple onchange='handleFiles(this.files)' />";
		$add_file_innerHTML.= "<label class='file_button' for='fileElem'>";
		$add_file_innerHTML.= "Выбрать файлы</label>";
		echo "<script>
		parent.document.getElementById('div_add_file').innerHTML='".str_replace("'","\'",$add_file_innerHTML)."';
		parent.document.getElementById('div_add_file').style.display='';
		</script>";			

		//список временных файлов со ссылками
		$q_files=OCIParse($c,"select id,filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where base_id='".$base_id."' and tmp='y' and nvl(sess_id,0)=nvl('".$sid."',0)
		order by filename");
		OCIExecute($q_files);
		echo "<script>";
		$i=0; while(OCIFetch($q_files)) { $i++;
			echo "parent.add_file_link('".OCIResult($q_files,"ID")."','".OCIResult($q_files,"FILETYPE")."','".OCIResult($q_files,"FILENAME")."'); ";
		}
		echo "</script>";

	}
	echo "<script>
	parent.document.getElementById('div_coment_hr').innerHTML='<hr>';
	</script>";		
}

?>
<script>parent.fn_check();</script>