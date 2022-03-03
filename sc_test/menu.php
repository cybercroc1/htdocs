<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
if(!isset($_SESSION['last_url'])) $_SESSION['last_url']='blank.htm';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="0">

<?php

if (!isset($_SESSION['login_id'])) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

extract($_REQUEST);

if (isset($blank) and $blank==1) exit();
if (isset($blank) and $blank==2) {
echo " | <font color=red><b>Пользователю не назначено ни одного проекта</b>"; 
exit();
}

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/sc_local_network");

if(isset($_SESSION['project'])) unset($_SESSION['project']);

//выбор проекта
$projects=array();
$p=0;

if($_SESSION['admin']==1) {
$q=OCIParse($c,"select decode(p.id,0,0,1) ord, p.id, p.name, p.tree_width,p.out_prefix
 from sc_projects p
 where (p.type = 'irs' or p.type is null) and p.hidden is null
 order by ord, p.name");
 			$_SESSION['project']['start_date']='01.01.2000 00:00:00';
			$_SESSION['project']['view_sc']=1;
			$_SESSION['project']['view_rep']=1;
			$_SESSION['project']['ch_sc']=1;
			$_SESSION['project']['ch_form']=1;
			$_SESSION['project']['ch_email']=1;
			$_SESSION['project']['view_billing']=1;
			$_SESSION['project']['view_sms_log']=1;
			$_SESSION['project']['view_okt_in_det']=1;
			$_SESSION['project']['view_okt_out_det']=1;
}
else {

	$q=OCIParse($c,"
	
	select 
	(select count(*) from SC_ACC_FORMS t where project_id=0 and login_id='".$_SESSION['login_id']."') view_rep,
	(select count(*) from SC_ACC_OKT_IN_ROUTE t where project_id=0 and login_id='".$_SESSION['login_id']."') view_okt_in_det
	from dual");	
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"VIEW_REP")>0 or OCIResult($q,"VIEW_OKT_IN_DET")>0) {$p++;

		//if($_SESSION['admin']<>1) {	

		//}
	
		$projects[0]['name']='ВСЕ ПРОЕКТЫ';
		$projects[0]['selected']='';
		if((!isset($project_id) and $p==1) or (isset($project_id) and $project_id==0)) {

		if (OCIResult($q,"VIEW_REP")>0) $_SESSION['project']['view_rep']=1;
		if (OCIResult($q,"VIEW_OKT_IN_DET")>0) $_SESSION['project']['view_okt_in_det']=1;
		
		$projects[0]['selected']=' selected';
			$_SESSION['project']['fr_width']=0;

			$_SESSION['project']['id']=0;
			$_SESSION['project']['name']=$projects[0]['name'];
			$_SESSION['project']['out_prefix']='';
		}
	}
	
	$q=OCIParse($c,"select 1 ord, p.id,p.name,p.tree_width,to_char(p.start_date,'DD.MM.YYYY HH24:MI:SS') start_date,p.out_prefix,
	r.view_sc,r.view_rep,r.ch_sc,r.ch_form,r.ch_email,r.view_billing,r.vsr_billing,r.view_sms_log, oir.view_okt_in_det 
	from sc_acc_project r, sc_projects p, sc_acc_okt_in_route oir
	where r.login_id='".$_SESSION['login_id']."' 
	and r.project_id=p.id and p.id>0 
	and oir.login_id(+)=r.login_id and oir.project_id(+)=r.project_id
	order by p.name");
}

OCIExecute($q, OCI_DEFAULT);
	while(OCIFetch($q)) {$p++;
	$projects[OCIResult($q,"ID")]['name']=OCIResult($q,"NAME");
	$projects[OCIResult($q,"ID")]['selected']='';
	if((!isset($project_id) and $p==1) or (isset($project_id) and $project_id==OCIResult($q,"ID"))) {
		$projects[OCIResult($q,"ID")]['selected']=' selected';
		
		if (OCIResult($q,"TREE_WIDTH")==NULL) $_SESSION['project']['fr_width']=200;
		else $_SESSION['project']['fr_width']=OCIResult($q,"TREE_WIDTH");
		
		$_SESSION['project']['id']=OCIResult($q,"ID");
		$_SESSION['project']['name']=OCIResult($q,"NAME");
		$_SESSION['project']['out_prefix']=OCIResult($q,"OUT_PREFIX");

		if($_SESSION['admin']<>1) {	
			$_SESSION['project']['view_rep']=OCIResult($q,"VIEW_REP");
			$_SESSION['project']['start_date']=OCIResult($q,"START_DATE");
			$_SESSION['project']['view_sc']=OCIResult($q,"VIEW_SC");
			$_SESSION['project']['ch_sc']=OCIResult($q,"CH_SC");
			$_SESSION['project']['ch_form']=OCIResult($q,"CH_FORM");
			$_SESSION['project']['ch_email']=OCIResult($q,"CH_EMAIL");
			$_SESSION['project']['view_billing']=OCIResult($q,"VIEW_BILLING");
			$_SESSION['project']['view_sms_log']=OCIResult($q,"VIEW_SMS_LOG");
			$_SESSION['project']['view_okt_in_det']=OCIResult($q,"VIEW_OKT_IN_DET");
		}
	}
}



if(count($projects)>1) {
	echo "<form method=post><nobr><select name=project_id onchange=select_project()>";	
	//echo "<option value=0>ВСЕ ПРОЕКТЫ</option>";
	//else echo "<option>Выберите проект</option>";
	foreach($projects as $id=>$name) {
		echo "<option value='".$id."'".$projects[$id]['selected'].">".$projects[$id]['name']."</option>";
	}
	echo "</select>
	<input type=submit name=logined value=Выбрать>";

	echo "<script>document.all.logined.style.display='none';
	function select_project() {
	document.all.logined.click();
	}
	</script>";
	if(!isset($no_refresh)) echo "<script>parent.fr12.location='".$_SESSION['last_url']."';</script>";
}
else if(count($projects)==1) {
	foreach($projects as $id=>$name) {
		echo "<font size=2><b>".$projects[$id]['name']."</b></font> ";
	}
}
if(count($projects)>0) {
	if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "<a href=login.php?refresh target=_parent><img border=0 src=refresh.gif title=Обновить></a>";
	
	if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "| <a href=adm_frame.php target=fr12>Админ </a>";
	
	if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "| <a href=adm_files.php target=fr12>Файлы </a>";
	
	if (isset($_SESSION['project']['ch_sc']) and $_SESSION['project']['ch_sc']==1) {
		if ($_SESSION['project']['id']>0) echo "| <a href=edit_sc.php target=fr12>Сценарий </a> "; 
		if ($_SESSION['project']['id']>0 and $from_local_addr=='y') echo "(<a href='".$path_to_scenary."?project_id=".$_SESSION['project']['id']."&' target=_blank><img border=0 src=visible.gif title='посмотреть сценарий оператора'></a> )";
	
		echo " | <a href=edit_table.php target=fr12>Таблицы </a>";
	
		if ($_SESSION['project']['id']>0) echo " | <a href=edit_shedule.php target=fr12>Расписания </a> | <a href=edit_forw_list.php target=fr12>Переадресация </a>";
	} 
	
	if (isset($_SESSION['project']['ch_form']) and $_SESSION['project']['ch_form']==1) echo "| <a href=edit_form.php target=fr12>Формы </a>"; 
	
	if (isset($_SESSION['project']['ch_email']) and $_SESSION['project']['ch_email']==1) echo "| <a href=edit_email.php target=fr12>e-mail </a>";
	
	if (isset($_SESSION['project']['view_rep']) and $_SESSION['project']['view_rep']==1) echo "| <a href=rep_fr.php target=fr12>Отчеты </a>";
	
	if (
	(isset($_SESSION['project']['view_okt_in_det']) and $_SESSION['project']['view_okt_in_det']==1) 
	or 
	(isset($_SESSION['project']['view_okt_out_det']) and $_SESSION['project']['view_okt_out_det']==1)
	) 
	echo "| <a href=okt_det_frame.php target=fr12>Детализация звонков </a>";
	
	
	if ($_SESSION['project']['id']>0 and isset($_SESSION['project']['view_sms_log']) and $_SESSION['project']['view_sms_log']==1) echo "| <a href=sms_log.php target=fr12>СМС-лог </a>";
	
	//if ($_SESSION['admin']==1) echo "| <a href=superbilling.php target=fr12>Супербиллинг</a>";
}

echo " | <a href=login.php target=_parent><font color=red>Выход</font></a>"; 

echo "</nobr>
</form>";
?>
</body>
<iframe name=session_refresh src="session_refresh.php" style="display:none"></iframe>
</html>