<?php 
require_once "auth.php";
if(!isset($_SESSION['last_url'])) $_SESSION['last_url']='blank.htm';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=u'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
<?php } ?>
</head>
<body class="menu-body" style="margin:0">
<?php

if (!isset($_SESSION['login_id'])) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

require_once "lk/lk_ora_conn_string.php";
require_once "sc/sc_local_network.php";

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
	$_SESSION['project']['view_rep']=1;
	$_SESSION['project']['view_sms_log']=1;
}
else {
	$q=OCIParse($c,"
	
	select 
	(select count(*) from SC_ACC_FORMS t where project_id=0 and login_id='".$_SESSION['login_id']."') view_rep
	from dual");	
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"VIEW_REP")>0) {$p++;
		$projects[0]['name']='ВСЕ ПРОЕКТЫ';
		$projects[0]['selected']='';
		if((!isset($project_id) and $p==1) or (isset($project_id) and $project_id==0)) {
			if (OCIResult($q,"VIEW_REP")>0) $_SESSION['project']['view_rep']=1;
			$projects[0]['selected']=' selected';
			$_SESSION['project']['id']=0;
			$_SESSION['project']['name']=$projects[0]['name'];
		}
	}
	
	if($_SESSION['allow_view_all_reports']<>1) $and_login="and r.login_id='".$_SESSION['login_id']."'"; else $and_login='';
	$q=OCIParse($c,"select 1 ord, p.id,p.name,p.tree_width,to_char(p.start_date,'DD.MM.YYYY HH24:MI:SS') start_date,p.out_prefix,
	".($_SESSION['allow_view_all_reports']==1?"'1' view_rep,":"r.view_rep,")."
	".($_SESSION['allow_view_all_reports']==1?"'1' view_sms_log,":"r.view_sms_log,")."
	r.ch_sc,r.ch_form,r.ch_email,r.view_billing,r.vsr_billing,
	oir.view_okt_in_det 
	from sc_projects p
	left join sc_acc_project r on r.project_id=p.id and r.login_id='".$_SESSION['login_id']."'
	left join sc_acc_okt_in_route oir on oir.login_id=r.login_id
	where p.id>0
	".$and_login."
	order by p.name");
}

OCIExecute($q, OCI_DEFAULT);
	while(OCIFetch($q)) {$p++;
	$projects[OCIResult($q,"ID")]['name']=OCIResult($q,"NAME");
	$projects[OCIResult($q,"ID")]['selected']='';
	if((!isset($_POST['project_id']) and $p==1) or (isset($_POST['project_id']) and $_POST['project_id']==OCIResult($q,"ID"))) {
		$projects[OCIResult($q,"ID")]['selected']=' selected';

		$_SESSION['project']['id']=OCIResult($q,"ID");
		$_SESSION['project']['name']=OCIResult($q,"NAME");
		$_SESSION['project']['out_prefix']=OCIResult($q,"OUT_PREFIX");

		if($_SESSION['admin']<>1) {	
			$_SESSION['project']['view_rep']=OCIResult($q,"VIEW_REP");
			$_SESSION['project']['start_date']=OCIResult($q,"START_DATE");
			$_SESSION['project']['view_sms_log']=OCIResult($q,"VIEW_SMS_LOG");
		}
	}
}
echo "<table class=menu-table><tr>";
echo "<form method=post name=frm_prj>";
echo "<td class=menu-left>";
if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') echo "<img class='img-logo' src='img/logo-cclight.png'></img>";
else echo "<img class='img-logo' src='img/logo.png'></img>";
echo "</td>";
echo "<td class=menu-center><nobr>";
if(count($projects)>1) {
	echo "<select name=project_id onchange=select_project()>";	
	foreach($projects as $id=>$name) {
		echo "<option value='".$id."'".$projects[$id]['selected'].">".$projects[$id]['name']."</option>";
	}
	echo "</select>";
	echo "<input type=submit name=logined value=Выбрать>";
	echo "<script>document.all.logined.style.display='none';
	function select_project() {
	document.all.logined.click();
	}
	</script>";
	echo "<script>parent.fr12.location='".$_SESSION['last_url']."';</script>";
}
else if(count($projects)==1) {
	foreach($projects as $id=>$name) {
		echo "<font size=2><b>".$projects[$id]['name']."</b></font> ";
	}
}
if(count($projects)>0) {
	if (isset($_SESSION['project']['view_rep']) and $_SESSION['project']['view_rep']==1) {
		$btnname="Отчеты";
		$btnurl="rep_main.php";
		$btnurls[]=$btnurl;
		echo "<input type=button class=menubtn value='".$btnname."' onclick='parent.fr12.location=\"".$btnurl."\";return false;'></input>";
	}
	if ($_SESSION['project']['id']>0 and isset($_SESSION['project']['view_sms_log']) and $_SESSION['project']['view_sms_log']==1) {
		$btnname="СМС-лог";
		$btnurl="rep_sms_main.php";
		$btnurls[]=$btnurl;
		echo "<input type=button class=menubtn value='".$btnname."' onclick='parent.fr12.location=\"".$btnurl."\";return false;'></input>";
	}
	if($_SESSION['last_url']=='' or !in_array($_SESSION['last_url'],$btnurls)) echo "<script>parent.fr12.location='".$btnurls[0]."';</script>";
	else echo "<script>parent.fr12.location='".$_SESSION['last_url']."';</script>";
}
echo "</nobr></td>";
echo "<td class=menu-right>";
echo "<nobr>";
if ($_SESSION['project']['id']>0) {
	$btnname="Обратная связь";
	if(LOCATION_ID=='1905') $btnurl="helpform-1905.php";
	if(LOCATION_ID=='VG') $btnurl="helpform-VG.php";
	$btnurls[]=$btnurl;
	echo "<input type=button class=menubtn value='".$btnname."' onclick='parent.fr12.location=\"".$btnurl."\";return false;'></input>";
}
echo "<input type=button class=exitbtn value='Выход' onclick='parent.location=\"./?exit\";return false;'></input>"; 
echo "</nobr>";
echo "</td>";
echo "</form>";
echo "</tr></table>";
?>
</body>
<iframe name=session_refresh src="session_refresh.php" style="display:none"></iframe>
</html>