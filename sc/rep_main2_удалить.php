<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
extract($_REQUEST);
include("sc/sc_conn_string.php");
//получение селектов для выбора номера
if(isset($get_phones)) {
	$cdns=array();
	if($_SESSION['admin']=='1' or $_SESSION['allow_view_all_reports']==1) {
		//доступ к номерам
		$q=OCIParse($c,"select p.phone, p.phone_name from SC_PHONES p
		where project_id='".$_SESSION['project']['id']."'
		order by p.phone_name");		
		OCIExecute($q);
		while(OCIFetch($q)) {
			$cdns[OCIResult($q,"PHONE")]=OCIResult($q,"PHONE_NAME");
		}		
	}
	else {
		//доступ к номерам
		$q=OCIParse($c,"select p.phone,p.phone_name,ac.phone ac_phones from SC_ACC_CDN ac
		left join sc_phones p on p.phone = decode(ac.phone,'all',p.phone,ac.phone)
		where ac.project_id='".$_SESSION['project']['id']."' and p.project_id='".$_SESSION['project']['id']."'
		and ac.login_id='".$_SESSION['login_id']."' and decode(ac.form_id,0,'".$sel_form."',ac.form_id)='".$sel_form."'
		order by p.phone_name");		
		OCIExecute($q);
		$ac_phones='';
		while(OCIFetch($q)) {
			$cdns[OCIResult($q,"PHONE")]=OCIResult($q,"PHONE_NAME");
			$ac_phones=OCIResult($q,"AC_PHONES");
		}			
	}
	echo "<option value=all>Все номера</option>";
	foreach($cdns as $ph => $name) {echo "<option value='".$ph."'".($ph==$sel_phone?" selected":"").">".$ph."</option>";}	
	if($ac_phones=='all') echo "<option value=null".($ph=="null"?" selected":"").">Нет номера</option>";
	exit();
}
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="css/report.css" rel="stylesheet" type="text/css">
</head>
<body>
<script src='../js/jquery-3.5.1.min.js'></script>
<link rel="stylesheet" href="../js/jquery.datetimepicker.css">
<script src='../js/jquery.datetimepicker.full.min.cp1251.js'></script>
<script>$.datetimepicker.setLocale('ru');</script>
<script src='report.js'></script>
<script src='../js/form2div.cp1251.js'></script>
<script>//эти функции можно переопределить на странице
form2div.allready=function(div_id) {
	alert('Запрос уже выполняется!');	
}

form2div.loading=function(div_id) {
	let div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;"><img src="process.gif"></img><br><a href=\'javascript:form2div_abort("'+div_id+'")\'>прервать загрузку</a></div>';	
}
form2div.aborted=function(div_id) {
	let div=document.getElementById(div_id);
	div.innerHTML='';
}
form2div.done=function(div_id,response) {
	let div=document.getElementById(div_id);
	div.innerHTML=div.innerHTML=response;
}	
//</script>
<script>
function get_phones () {
	form_id=document.getElementById('sel_form').value;
	phone=document.getElementById('sel_phone').value;
	$.post('',
	{
		'get_phones':'y',
		'sel_form':form_id,
		'sel_phone':phone
	},
	function(data){document.getElementById('sel_phone').innerHTML=data;}
	//function(data){document.getElementById('ttt').innerHTML=data;}
	) 
	.fail(function() { alert("Ошибка AJAX"); });
}

function show_rep () {
	start_rep_date=document.getElementById('start_rep_date').value;
	end_rep_date=document.getElementById('end_rep_date').value
	form_id=document.getElementById('sel_form').value;
	phone=document.getElementById('sel_phone').value;
	$.post('report2.php',
	{
		'html_go':'y',
		'start_rep_date':document.getElementById('start_rep_date').value,
		'end_rep_date':document.getElementById('end_rep_date').value,
		'form_id':document.getElementById('sel_form').value,
		'cgpn':document.getElementById('sel_phone').value
	},
	//function(data){document.getElementById('sel_phone').innerHTML=data;}
	function(data){$('#rep_div').html(data);}
	) 
	.fail(function() { alert("Ошибка AJAX"); });
}
</script>
<?php

$_SESSION['last_url']='rep_main2.php';

//Формирование дат

	if (!isset($_SESSION['start_rep_date'])) {
	$start_rep_date = strtotime("now");
	$_SESSION['start_rep_date'] = date("d.m.Y",$start_rep_date);
	}
	
	if (!isset($_SESSION['end_rep_date'])) {
	$end_rep_date = strtotime("now"); //текущая дата
	$_SESSION['end_rep_date'] = date("d.m.Y",$end_rep_date);
	}

//
echo "<form id=frm method=post>";
echo "<div class=rep_head>";
echo "<font size=4> Отчеты - \"".$_SESSION['project']['name']."\"</font><br>";
	if ($_SESSION['rep_period']<>'') {
		$title="Отчеты предоставляются с ".$_SESSION['rep_period'];
		$and_rep_period=" and b.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
		$min_date=$_SESSION['rep_period'];
	}
	else {$and_rep_period=''; $min_date='01.01.2015'; $title='';}

	if(strlen($_SESSION['start_rep_date'])==10) $_SESSION['start_rep_date'].=" 00:00";
	if(strlen($_SESSION['end_rep_date'])==10) $_SESSION['end_rep_date'].=" 23:59";

	echo " c: <INPUT TYPE=TEXT id=start_rep_date NAME=start_rep_date value='".$_SESSION['start_rep_date']."' SIZE=16 title='".$title."'></input>";
	?><script>$('#start_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php
	

	echo " по: <INPUT TYPE=TEXT id=end_rep_date NAME=end_rep_date value='".$_SESSION['end_rep_date']."' SIZE=16></input>";
	?><script>$('#end_rep_date').datetimepicker({dayOfWeekStart:1,timepicker:true,format:'d.m.Y H:i',mask:true,minDate:<?php echo "'".$min_date."'"; ?>,maxDate:'today',formatDate:'d.m.Y'});</script><?php

	//доступ к отчетам
	$forms=array();
	$cdns=array();
	if($_SESSION['admin']=='1' or $_SESSION['allow_view_all_reports']==1) {
		//доступ к формам
		$q=OCIParse($c,"select f.id,f.name from SC_FORMS f
		where f.deleted is null and f.id>0 and f.project_id='".$_SESSION['project']['id']."' 
		order by f.name");
		OCIExecute($q);
		 while(OCIFetch($q)) {
			$forms[OCIResult($q,"ID")]=OCIResult($q,"NAME");	
		}
	}
	else {
		//доступ к формам
		$q=OCIParse($c,"select f.id,f.name from SC_ACC_FORMS af
		left join sc_forms f on f.id=decode(af.form_id,0,f.id,af.form_id)
		where af.project_id='".$_SESSION['project']['id']."' and f.project_id='".$_SESSION['project']['id']."'
		and af.login_id='".$_SESSION['login_id']."'
		order by f.name");
		OCIExecute($q);
		 while(OCIFetch($q)) {
			$forms[OCIResult($q,"ID")]=OCIResult($q,"NAME");	
		}	
	}



	echo "Выберите тип отчета: ";
	echo "<select id=sel_form name=form_id onchange=get_phones()>";
	echo "<option value=all>Все отчеты</option>";
	foreach($forms as $id => $name) {echo "<option value='".$id."'>".$name."</option>";}	
	//звонки без отчета
	if($_SESSION['allow_noreport']==1) {echo "<option value=null>Нет отчета</option>";}
	echo "</select>";

	echo "Выберите номер доступа: ";
	echo "<select id=sel_phone name=cgpn>";
	echo "</select>";

	//
	//echo "<INPUT type=button name=html_go value=\"Показать отчет\" onclick=show_rep()>";
	echo "<INPUT type=button name=html_go value=\"Показать отчет\" onclick=form2div('frm','rep_div',this,'report2.php')>";
	echo "<INPUT type=submit name=xls_go value=\"Скачать в Excel\">";
	echo "<INPUT type=button name=count_go value=\"Количество\" onclick=form2div('frm','rep_div',this,'report2.php')>";
	echo "<script>get_phones();</script>";
	echo "</div>";
	echo "<div id=rep_div class=rep_div></div>";
	echo "</form>";
?>
</body>
</html>