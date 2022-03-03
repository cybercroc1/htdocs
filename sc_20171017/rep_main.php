<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="5" topmargin="0">
<script>
function ch_rep_type() {
	if (document.all.form_id_name.value=='') {
	document.all.html_go.disabled=true;
	document.all.xls_go.disabled=true;
	document.all.count_go.disabled=true;
	document.all.td_rep_type.style.color='red';
	}
	else {
	document.all.html_go.disabled=false;
	document.all.xls_go.disabled=false;
	document.all.count_go.disabled=false;
	document.all.td_rep_type.style.color='black';
	}
}	
</script>
<?php if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['view_rep'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

//Формирование дат

if(isset($start_rep_date)) $_SESSION['start_rep_date']=$start_rep_date;
if(isset($end_rep_date)) $_SESSION['end_rep_date']=$end_rep_date;

	if (!isset($_SESSION['start_rep_date'])) {
	$start_rep_date = strtotime("now");
	$_SESSION['start_rep_date'] = date("d.m.Y",$start_rep_date);
	}
	
	if (!isset($_SESSION['end_rep_date'])) {
	$end_rep_date = strtotime("now"); //текущая дата
	$_SESSION['end_rep_date'] = date("d.m.Y",$end_rep_date);
	}

$yesterday = strtotime("- 1 day");
$yesterday = date("d.m.Y",$yesterday);
$curdate = date("d.m.Y");
//

echo "<font size=4> Отчеты - \"".$_SESSION['project_name'][$_SESSION['i']]."\"</font><br>";
	if ($_SESSION['rep_period']<>'') {
	echo "<font color=red>Отчеты предоставляются с <b>".$_SESSION['rep_period']."</b></font>";
	$and_rep_period=" and r.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
	}
	else {$and_rep_period='';}


if (!isset($period_go)) {

	echo "<table><form method=post>";
	
	echo "<tr><td align=right valign=top>c:</td><td nowrap><INPUT TYPE=TEXT NAME=start_rep_date value=".$_SESSION['start_rep_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_rep_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A></td></tr>"; 

	echo "<tr><td align=right valign=top>по:</td><td nowrap><INPUT TYPE=TEXT NAME=end_rep_date value=".$_SESSION['end_rep_date']." SIZE=9>
<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_rep_date);return false; HIDEFOCUS>
<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A><br>
(включительно)</td></tr>"; 

	echo "<tr><td colspan=2><INPUT type=submit name=period_go value=\"Выбрать период\"></td></tr>";
	echo "</form></table>";
}

//выбор формы и номера
if (isset($period_go)) {
	include("../../sc_conf/sc_conn_string");
	echo "<table>";
	echo "<tr><td align=right valign=top>c:</td><td nowrap><b>".$_SESSION['start_rep_date']."</b></td></tr>"; 

	echo "<tr><td align=right valign=top>по:</td><td nowrap><b>".$_SESSION['end_rep_date']." (включительно)</b></td></tr>"; 
	echo "<tr><td colspan=2><a href=rep_main.php>Выбрать другой период</a></td></tr>";
	echo "</table>";

	echo "<table><form method=post action=report.php target=fr2>";
	//список форм
	echo "<tr><td id=td_rep_type style='color:red'><b>Выберите тип отчета:</b><br>";
	
		//проверка доступа к отчетам
		$q=OCIParse($c,"select distinct * from
                    (select form_id from sc_access_form_fix where login_id='".$_SESSION['login_id']."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
                    union
                    select form_id from sc_access_form where login_id='".$_SESSION['login_id']."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."')");
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($q)) {
			$form_ids[$i]="'".OCIResult($q,"FORM_ID")."'";
		$i++;
		}			
		if(isset($form_ids)) {
		$and_form_id=' and r.form_id in ('.implode(",",$form_ids).') ';
		$_SESSION['no_all_forms_access']='no access';
		} 
		else {
		$and_form_id='';
		unset($_SESSION['no_all_forms_access']);
		}
		//

		//проверка доступа к номерам
		$q=OCIParse($c,"select phone from SC_ACCESS_PHONE where login_id=".$_SESSION['login_id']);
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($q)) {
			$cdpns[$i]="'".OCIResult($q,"PHONE")."'";
		$i++;
		}			
		if(isset($cdpns)) {
		$and_cdpns=' and b.cgpn in ('.implode(",",$cdpns).') ';
		$_SESSION['no_all_cdpns_access']='no access';
		} 
		else {
		$and_cdpns='';
		unset($_SESSION['no_all_cdpns_access']);
		}
		//
		
	$q=OCIParse($c,"select distinct r.form_id,replace(r.form_name,'\"','&quot;') form_name from sc_call_report r
	where r.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and 
	r.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY')
	and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_rep_period."
	".$and_form_id."
	order by replace(r.form_name,'\"','&quot;')");
	
	OCIExecute($q,OCI_DEFAULT);
	echo "<select name=form_id_name onchange=ch_rep_type()><option value=></option>";
	if(!isset($form_ids)) echo "<option value=all_report>Все отчеты</option>";
		while(OCIFetch($q)) {
		echo "<option value=\"".OCIResult($q,"FORM_ID")."_".OCIResult($q,"FORM_NAME")."\">".OCIResult($q,"FORM_NAME")."</option>";
		}
	echo "</select>";
	echo "</tr></td>";
	//
	//список номеров
	
	$q=OCIParse($c,"select distinct b.cgpn 
  from sc_call_report r, sc_call_base b
  where r.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and
  b.id=r.call_base_id and
  length(b.cgpn)>4 and
	r.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY')
	and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_rep_period."
	".$and_form_id."
	".$and_cdpns."
	order by b.cgpn");
	
	
	/*$q=OCIParse($c,"select distinct cgpn from sc_call_base
	where cgpn is not null
	and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and 
	date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY')
	and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_rep_period."
	and length(cgpn)>4
	order by cgpn");*/
	
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr><td><b>Выберите номер доступа:</b><br>";
	echo "<select name=cgpn><option value=>Все номера</option>";
		while(OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"CGPN").">".OCIResult($q,"CGPN")."</option>";
		}
	echo "</select>";
	echo "</tr></td>";
	//
	echo "<tr><td><hr>";
	echo "<INPUT type=submit name=html_go value=\"Показать отчет\">";
	echo "</tr></td>";
	echo "<tr><td>";
	echo "<INPUT type=submit name=xls_go value=\"Скачать в Excel\">";
	echo "<tr><td>";
	echo "<INPUT type=submit name=count_go value=\"Количество\">";
	echo "</form></td></tr></table>";
echo "<script>ch_rep_type();</script>";
}//выбор формы и номера

?>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>