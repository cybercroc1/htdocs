<?php
//записи разговоров
if($_SESSION['allow_records']==1) {
	include("oktell_conn_string.php");
	//include("sc/sc_path.php"); //перенесено в defines
	//include("sc/sc_adm_url.php"); //перенесено в defines
}	
//
	
if(!isset($form_id) or !isset($cgpn)) exit();

if($form_id=='all') $form_name='';
else {
	$q=OCIParse($c,"select name from SC_FORMS where id='".$form_id."'");
	OCIExecute($q);	
	if(OCIFetch($q)) $form_name=OCIResult($q,"NAME");
	else $form_name='';
}
	
$cdn=$cgpn; //all, null, номер

//доступ к отчетам
$and_form_ids='';
$and_cdns='';
if($_SESSION['admin']!=1 and $_SESSION['allow_view_all_reports']<>1) {
	$acc_forms=array(); //id форм, к которым есть доступ
	$acc_all_cdns=array(); //id форм, по которым есть доступ ко всем номерам
	$acc_all_forms=array(); //номера, к которым есть доступ по всем формам
	$acc_forms_cdns=array(); //id форм и номер к которому есть доступ
	
	if($_SESSION['project']['id']==0) {
		//доступ к формам
		$q=OCIParse($c,"select form_id from SC_ACC_FORMS where login_id='".$_SESSION['login_id']."' and project_id=0");
		OCIExecute($q);
		$f=0; while(OCIFetch($q)) {$f++;
			$acc_forms[]=OCIResult($q,"FORM_ID");	
		}
		//доступ к номерам
		$q=OCIParse($c,"select form_id, phone from SC_ACC_CDN where login_id='".$_SESSION['login_id']."' and project_id=0");		
		OCIExecute($q);
		$n=0; while(OCIFetch($q)) {$n++;
			if(OCIResult($q,"PHONE")=='all') $acc_all_cdns[]=OCIResult($q,"FORM_ID");
			else $acc_forms_cdns[]=array(OCIResult($q,"FORM_ID"),OCIResult($q,"PHONE"));
		}
	}
	elseif($_SESSION['project']['id']>0) {
		//доступ к формам
		$q=OCIParse($c,"select form_id from SC_ACC_FORMS where login_id='".$_SESSION['login_id']."' and project_id='".$_SESSION['project']['id']."'");
		OCIExecute($q);
		$f=0; while(OCIFetch($q)) {$f++;
			if(OCIResult($q,"FORM_ID")==0) break;
			else $acc_forms[]=OCIResult($q,"FORM_ID");
		}
		
		//доступ к номерам
		$q=OCIParse($c,"select form_id, phone from SC_ACC_CDN where login_id='".$_SESSION['login_id']."' and project_id='".$_SESSION['project']['id']."'");		
		OCIExecute($q);
		$n=0; while(OCIFetch($q)) {$n++;
			if(OCIResult($q,"FORM_ID")==0 and OCIResult($q,"PHONE")=='all') break;
			elseif(OCIResult($q,"FORM_ID")==0) $acc_all_forms[]=OCIResult($q,"PHONE");
			elseif(OCIResult($q,"PHONE")=='all') $acc_all_cdns[]=OCIResult($q,"FORM_ID");
			else $acc_forms_cdns[]=array(OCIResult($q,"FORM_ID"),OCIResult($q,"PHONE"));
		}		
	}
	
	if($f==0) $and_form_ids.=" and 1=2 ";
	elseif(count($acc_forms)>0) $and_form_ids.=" and r.form_id in ('".implode("','",$acc_forms)."') ";
	
	if($n==0) $and_cdns.=" and 1=2 ";
	elseif(count($acc_all_cdns)+count($acc_all_forms)+count($acc_forms_cdns)>0) {
		$and_cdns.=" and ( ";
		
		$x=0;
		if(count($acc_all_cdns)>0) {$x++;
			if($x>1) $and_cdns.=" or ";
			$and_cdns.=" r.form_id in ('".implode("','",$acc_all_cdns)."') ";
		}
		if(count($acc_all_forms)>0) {$x++;
			if($x>1) $and_cdns.=" or ";
			$and_cdns.=" b.cgpn in ('".implode("','",$acc_all_forms)."') ";
		}			
		foreach($acc_forms_cdns as $tmp_form_cdn) {$x++;
			if($x>1) $and_cdns.=" or ";
			$and_cdns.=" (r.form_id='".$tmp_form_cdn[0]."' and b.cgpn='".$tmp_form_cdn[1]."') ";
		}
		
		$and_cdns.=" ) ";		
	}
}
//

if($_SESSION['admin']==1 or $_SESSION['allow_view_all_reports']==1) {
	//все строки
	$and_project_id="
		--без отчета
		and ((r.id is null 
		and b.project_id='".$_SESSION['project']['id']."'    
		)or 
		(
		--или с отчетом		
		f.project_id='".$_SESSION['project']['id']."'
		and f.deleted is null
		))	
	";
}	
elseif($_SESSION['allow_noreport']!=1 and $_SESSION['allow_nocall']!=1) {
	//Строки только со звонком и отчетом
	$and_project_id="
		--Только с отчетом
		and f.project_id='".$_SESSION['project']['id']."'
		and f.deleted is null		
		--Только по звонку
		and (b.cdpn is not null or b.cgpn is not null or b.agid is not null or b.cdr_thr_id is not null)
	";	
}
elseif($_SESSION['allow_noreport']==1 and $_SESSION['allow_nocall']==1) {
	//Строки только со звонком или отчетом
	$and_project_id="
		and ((
		--с отчетом
		f.project_id='".$_SESSION['project']['id']."'
		and f.deleted is null
		)or
		(
		-- или без отчета
		r.id is null 
		and b.project_id='".$_SESSION['project']['id']."' 		
		--но Только по звонку
		and (b.cdpn is not null or b.cgpn is not null or b.agid is not null or b.cdr_thr_id is not null)
		))
	";	
}
elseif($_SESSION['allow_noreport']==1) {
	//Строки только со звонком
	$and_project_id="
		and ((
		--без отчета
		r.id is null 
		and b.project_id='".$_SESSION['project']['id']."'    
		)or 
		(
		--или с отчетом
		f.project_id='".$_SESSION['project']['id']."'
		and f.deleted is null
		))
		--но только по звонку
		and (b.cdpn is not null or b.cgpn is not null or b.agid is not null or b.cdr_thr_id is not null)			
	";
}
elseif($_SESSION['allow_nocall']==1) {
	//Строки только с отчетом
	$and_project_id="
		--Только звонки с отчетом
		and f.project_id='".$_SESSION['project']['id']."'
		and f.deleted is null			
	";
}

if ($_SESSION['rep_period']<>'') {
	$and_b_rep_period=" and b.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
	$and_v_rep_period=" and v.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
} 
else {
	$and_b_rep_period='';
	$and_v_rep_period='';
}

if($cdn<>'all' and $cdn<>'null') $and_b_cdn="and b.cgpn='".$cdn."'";
else if($cdn=='null') $and_b_cdn="and b.cgpn is null";
else $and_b_cdn="";

//список стандартных полей
$access_fix['date_call']	='y';
$access_fix['cdn']			='y';
$access_fix['aon']			='y';
$access_fix['agid']			='y';
$access_fix['ivr_sec']		='y';
$access_fix['queue_sec']	='y';
$access_fix['alerting_sec']	='y';
$access_fix['connected_sec']='y';
$access_fix['connected_min']='y';
$access_fix['call_sec']		='y';
$access_fix['call_min']		='y';

if($form_id=='all') {
	//$and_form_id_name='';
	$and_f_form_id='';
	$and_r_form_id='';	
}
elseif($form_id=='null') {
	$and_f_form_id="";
	$and_r_form_id="and r.id is null";	
}
else {
	//$and_form_id_name="and r.form_id='".$form_id."' and r.form_name='".$form_name."'";
	$and_f_form_id="and f.id='".$form_id."'";
	$and_r_form_id="and r.form_id='".$form_id."'";	
}

if($form_id<>'null') {	
	if($_SESSION['admin']==1 or $_SESSION['allow_view_all_reports']==1) {
		$q=OCIParse($c,"select f.id form_id,
		'y' 						date_call, 
		'y' 						cdpn, 
		'y'							cgpn, 
		'y'							agid,
		f.show_ivr_sec				ivr_sec,
		f.show_queue_sec			queue_sec,
		f.show_alerting_sec			alerting_sec,
		f.show_connected_sec		connected_sec,
		f.show_connected_min		connected_min,
		f.show_call_sec				call_sec,
		f.show_call_min				call_min,
		CODED_AON
		from sc_forms f 
		where f.project_id='".$_SESSION['project']['id']."' 
		and f.deleted is null
		".$and_f_form_id."
		");
	}
	else {
		$q=OCIParse($c,"select af.form_id, 
		af.date_call,		
		af.cdpn,
		af.cgpn,
		af.agid,
		case when af.ivr_sec='y'		 and f.show_ivr_sec='y'			 then 'y' else null end ivr_sec,
		case when af.queue_sec='y' 	     and f.show_queue_sec='y'		 then 'y' else null end queue_sec,
		case when af.alerting_sec='y'	 and f.show_alerting_sec='y'	 then 'y' else null end alerting_sec,
		case when af.connected_sec='y'	 and f.show_connected_sec='y'	 then 'y' else null end connected_sec,
		case when af.connected_min='y'	 and f.show_connected_min='y'	 then 'y' else null end connected_min,
		case when af.call_sec='y'		 and f.show_call_sec='y'		 then 'y' else null end call_sec,
		case when af.call_min='y'		 and f.show_call_min='y'		 then 'y' else null end call_min,
		f.CODED_AON
		from sc_acc_forms af, sc_forms f 
		where af.login_id=".$_SESSION['login_id']." 
		and af.project_id='".$_SESSION['project']['id']."' 
		and f.project_id=af.project_id
		".$and_f_form_id."
		and (f.id = af.form_id or af.form_id=0)
		and f.deleted is null
		order by af.form_id");
	}	
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while(OCIFetch($q)) {$i++;
		if(isset($access_fix['date_call']		) and	OCIResult($q,"DATE_CALL")<>'y' 			) unset($access_fix['date_call']);
		if(isset($access_fix['cdn']				) and	OCIResult($q,"CGPN")<>'y' 				) unset($access_fix['cdn']);
		if(isset($access_fix['aon']				) and	OCIResult($q,"CDPN")<>'y' 				) unset($access_fix['aon']);
		if(isset($access_fix['agid']			) and	OCIResult($q,"AGID")<>'y'				) unset($access_fix['agid']);
		if(isset($access_fix['ivr_sec']			) and	OCIResult($q,"IVR_SEC")<>'y'	  		) unset($access_fix['ivr_sec']);
		if(isset($access_fix['queue_sec']		) and	OCIResult($q,"QUEUE_SEC")<>'y'	 		) unset($access_fix['queue_sec']);
		if(isset($access_fix['alerting_sec']	) and	OCIResult($q,"ALERTING_SEC")<>'y' 		) unset($access_fix['alerting_sec']);
		if(isset($access_fix['connected_sec']	) and	OCIResult($q,"CONNECTED_SEC")<>'y'		) unset($access_fix['connected_sec']);
		if(isset($access_fix['connected_min']	) and	OCIResult($q,"CONNECTED_MIN")<>'y'		) unset($access_fix['connected_min']);
		if(isset($access_fix['call_sec']		) and	OCIResult($q,"CALL_SEC")<>'y'     		) unset($access_fix['call_sec']);
		if(isset($access_fix['call_min']		) and	OCIResult($q,"CALL_MIN")<>'y'     		) unset($access_fix['call_min']);
		if(OCIResult($q,"CODED_AON")=='y') 		$CODED_AON='y'; 
	}

	//ограничение доступа к полям отчета
	if($form_id<>'all') {
		if($_SESSION['admin']==1 or $_SESSION['allow_view_all_reports']==1) {
			$all_obj='y';
		}
		else {	
			$all_obj='';
			$q_obj_acc=OCIParse($c,"select obj_id from sc_acc_frm_obj ao 
			where ao.login_id='".$_SESSION['login_id']."' and ao.project_id='".$_SESSION['project']['id']."' and (ao.form_id=0 or ao.form_id='".$form_id."')
			order by obj_id");
			OCIExecute($q_obj_acc,OCI_DEFAULT);
			while(OCIFetch($q_obj_acc)) {
				if(OCIResult($q_obj_acc,"OBJ_ID")=='0') {
					$all_obj='y'; //все поля
					break;
				}
				else {	
					$obj_ids[]="'".OCIResult($q_obj_acc,"OBJ_ID")."'";
				}
			}			
		} 
		if($all_obj=='y') $and_obj_id=''; //все поля
		else if(isset($obj_ids)) {
			$and_obj_id=' and v.object_id in ('.implode(",",$obj_ids).') '; //список полей
		} 
		else {
			$and_obj_id=" and v.object_id in ('') "; //нет доступа к полям
		}		
	
		$q_obj=OCIParse($c,"select v.object_id,nvl(v.object_name,o.name) object_name,o.type_id,max(v.ordering), max(v.selected) selectable from SC_CALL_REPORT_VALUES v, sc_form_object o
		where v.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY HH24:MI') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY HH24:MI')+1/1440
		".$and_v_rep_period."
		and o.id=v.object_id
		and v.form_id='".$form_id."'
		".($_SESSION['project']['id']==0?'':" and v.project_id='".$_SESSION['project']['id']."' ")."
		".$and_obj_id." 
		group by v.object_id,nvl(v.object_name,o.name),o.type_id
		order by max(o.ordering)");
		
		OCIExecute($q_obj,OCI_DEFAULT);
		while(OCIFetch($q_obj)) {
			$object_id[$i]=OCIResult($q_obj,"OBJECT_ID");
			$object_name[$i]=OCIResult($q_obj,"OBJECT_NAME");
			$object_type[$i]=OCIResult($q_obj,"TYPE_ID");
			$object_selectable[$i]=OCIResult($q_obj,"SELECTABLE");	
			$i++;
		}
	
		//запрс на получение данных по полям
		$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id 
		and object_id=:object_id and (object_name=:object_name or object_name is null)");	
	}
}

//кастомные поля звонка
$q_call_fields=OCIParse($c,"select id,name from SC_CALL_FILEDS f where project_id='".$_SESSION['project']['id']."' and reports='y' and deleted is null order by ord");
OCIExecute($q_call_fields,OCI_DEFAULT);
$i=0; while(OCIFetch($q_call_fields)) {$i++;
	$call_filed_id[$i]=OCIResult($q_call_fields,"ID");
	$call_filed_name[$i]=OCIResult($q_call_fields,"NAME");
}		
//запрос на получение данных по кастомным полям		
$q_call_val=OCIParse($c,"select value from SC_CALL_VALUES v
where project_id='".$_SESSION['project']['id']."' and call_id=:call_id and field_id=:field_id");
?>