<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','1');
//ini_set('max_execution_time','300');
include("../../sc_conf/sc_session");
session_start();

echo '<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<script language="javascript" src="superbilling.js"></script>
<body topmargin="0">';

extract($_REQUEST);

if (!isset($_SESSION['i'])) exit(); 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/ab_conn_string");

//if(isset($selected_fields)) {
//	$_SESSION['bill_selected_fields']=$selected_fields;
//}

//Формирование дат

//if(isset($start_bill_date)) $_SESSION['start_bill_date']=$start_bill_date;
//if(isset($end_bill_date)) $_SESSION['end_bill_date']=$end_bill_date;

	if (!isset($_SESSION['start_bill_date'])) {
	$start_rep_date = strtotime("-1 day");
	$_SESSION['start_bill_date'] = date("d.m.Y",$start_rep_date);
	}
	
	if (!isset($_SESSION['end_bill_date'])) {
	$end_rep_date = strtotime("-1 day"); //текущая дата
	$_SESSION['end_bill_date'] = date("d.m.Y",$end_rep_date);
	}

$yesterday = strtotime("- 1 day");
$yesterday = date("d.m.Y",$yesterday);
$curdate = date("d.m.Y");
//
//if(isset($xls_go)) report();

echo "<table border=1>";
	echo "<form name=billing_frm method=post action='superbilling_rep.php' target='_blank' onsubmit=fSelectFields()>";
echo "<tr>";
echo "<td valign=top>";
	echo "<font size=3><b> Биллинг - \"".$_SESSION['project_name'][$_SESSION['i']]."\"</b> ";
echo "</td><td align=right rowspan=5>";

//ВЫБОР ПОЛЕЙ
echo "<table border=1><tr>";
echo "<td>";
echo '<select size="24" style="width:240" name=all_fields multiple="multiple" onchange="if(this.options[selectedIndex].disabled)this.options[selectedIndex].selected=false">';

$q=OCIParse($abilling,"select field_name,full_name,def_selected from ab_inc_fields order by ord");
OCIExecute($q,OCI_DEFAULT);
$i=1;
while(OCIFetch($q)) {
	if(OCIResult($q,"DEF_SELECTED")=='y') {$default_fields[$i]=OCIResult($q,"FIELD_NAME"); $i++;}
	echo "<option value='".OCIResult($q,"FIELD_NAME")."'>".OCIResult($q,"FULL_NAME")."</option>";
}
echo "</select>";
echo "</td>";
echo "<td>";

echo '<a href="javascript:fAddFields()"><img border="0" src="green_arrow_right.gif"></a>
<br>
<br>
<br>
<a href="javascript:fDelFields()"><img border="0" src="red_arrow_left.gif"></a>';
echo "</td>";
echo "<td>";
echo '<select size="24" style="width:240" id=selected_fields name=selected_fields[] multiple="multiple">';

if(isset($_SESSION['bill_selected_fields'])) {
	foreach($_SESSION['bill_selected_fields'] as $key => $val) {
		echo "<option value='$val'>$val</option>";
	}
}
else {
	foreach($default_fields as $val) {
		echo "<option value='$val'></option>";
	}
//echo "<option value='START_DATE'></option>";
//echo "<option value='START_TIME'></option>";
//echo "<option value='DNIS'></option>";
//echo "<option value='AON'></option>";
//echo "<option value='CALL_TYPE'></option>";
//echo "<option value='CALL_TYPE_PHONE'></option>";
//echo "<option value='EXT'></option>";
//echo "<option value='FORWARD_NUM'></option>";
//echo "<option value='CODE'></option>";
//echo "<option value='REGION'></option>";
//echo "<option value='TARIF'></option>";
//echo "<option value='DUR_SEC'></option>";
//echo "<option value='DUR_MIN'></option>";
//echo "<option value='IVR_SEC'></option>";
//echo "<option value='IVR_MIN'></option>";
//echo "<option value='CONNECTED_SEC'></option>";
//echo "<option value='CONNECTED_MIN'></option>";
//echo "<option value='STOIMOST'></option>";
}
echo "</select>";
echo "</td>";
echo "<td>";
echo '<a href="javascript:fUpFields()"><img border="0" src="blue_arrow_up.gif"></a>
<br>
<br>
<br>
<br>
<a href="javascript:fDownFields()"><img border="0" src="blue_arrow_down.gif"></a>';
echo "</td></tr></table>";
//

echo "</td></tr>";

echo "<tr>";
echo "<td valign=top>";		
		//список номеров
		$q=OCIParse($c,"select phone from sc_phones
		where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		$opt="";
			while(OCIFetch($q)) {
			$opt.="<option value=".OCIResult($q,"PHONE").">".OCIResult($q,"PHONE")."</option>";
			$phone=OCIResult($q,"PHONE");
			$i++;
			}
		if ($i==0) {echo "<font color=red> Ошибка! Проекту не назначено ни одного номера доступа!"; exit();}
		if ($i==1) {
		echo "<input type=hidden name=cgpn value=".$phone.">";
		}
		if ($i>1) {
		echo "<select name=cgpn>";
		if ($cgpn<>'') echo "<option value=".$cgpn.">".$cgpn."</option>";
		echo "<option value=>Все номера</option>";
		echo $opt;
		echo "</select>";
		}
	//	
	//выбор даты
	
	echo "<table border=0>";
	echo "<tr><td rowspan=2 valign=center><b>с: </b></td><td align=center colspan=2>дата<br>";
	echo "<INPUT TYPE=TEXT NAME=start_bill_date value=".$_SESSION['start_bill_date']." SIZE=8 onClick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_bill_date);return false; HIDEFOCUS' onchange=fCheckDate()>"; 
	echo "</td>";
	echo "<td rowspan=2 valign=center><b>по: </b></td><td align=center colspan=2>дата<br>";
	echo "<INPUT TYPE=TEXT NAME=end_bill_date value=".$_SESSION['end_bill_date']." SIZE=8 onClick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_bill_date);return false; HIDEFOCUS' onchange=fCheckDate()>"; 
	echo "</td>";
	echo "</tr>";
	echo "<tr ID=tr_bill_time>";
	echo "<td align=center>час.<br>";
	echo " <select name=start_bill_hh>";
	if(isset($_SESSION['start_bill_hh']) and $_SESSION['start_bill_hh']<>'0') {
		echo "<option value='".$_SESSION['start_bill_hh']."'>".$_SESSION['start_bill_hh']."</option>";
	} 
	for($i=0; $i<=23; $i++) {
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td align=center>мин.<br>";
	echo "<select name=start_bill_mi>";
	if(isset($_SESSION['start_bill_mi']) and $_SESSION['start_bill_mi']<>'0') {
		echo "<option value='".$_SESSION['start_bill_mi']."'>".$_SESSION['start_bill_mi']."</option>";
	} 
	for($i=0; $i<=55; $i+=5) {
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td align=center>час.<br>";
	echo " <select name=end_bill_hh>";
	if(isset($_SESSION['end_bill_hh']) and $_SESSION['end_bill_hh']<>'0') {
		echo "<option value='".$_SESSION['end_bill_hh']."'>".$_SESSION['end_bill_hh']."</option>";
	} 
	for($i=0; $i<=23; $i++) {
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td align=center>мин.<br>";
	echo "<select name=end_bill_mi>";
	if(isset($_SESSION['end_bill_mi']) and $_SESSION['end_bill_mi']<>'0') {
		echo "<option value='".$_SESSION['end_bill_mi']."'>".$_SESSION['end_bill_mi']."</option>";
	} 
	for($i=0; $i<=55; $i+=5) {
		echo "<option value=$i>$i</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "</tr>";
	echo "</table>";
	//
echo "</td>";
echo "</tr>";
//длительность разговора
echo "<tr><td>";
	echo "<b>Длительность разговора >= </b><input type=text size=2 name=min_conn_dur_sec";
	if(isset($_SESSION['bill_min_conn_dur_sec'])) echo " value=".$_SESSION['bill_min_conn_dur_sec'];
	else echo " value=6";
	echo "> сек.<br>";
	echo "(avayabilling: 6 сек.)";
	

echo "</td></tr>";
//
//тип звонка
echo "<tr><td>";
echo "<input type=radio ID='radio_all' name='call_type' value=''"; if(!isset($_SESSION['bill_call_type']) or $_SESSION['bill_call_type']=='') echo " checked"; 
echo " onclick=fCheckSelectedFields()>ВСЕ</input>";
echo "<input type=radio ID='radio_inc' name='call_type' value='входящие'"; if(isset($_SESSION['bill_call_type']) and $_SESSION['bill_call_type']=='входящие') echo " checked";
echo " onclick=fCheckSelectedFields()>Входящие</input>";
echo "<input type=radio ID='radio_forw' name='call_type' value='переводные'"; if(isset($_SESSION['bill_call_type']) and $_SESSION['bill_call_type']=='переводные') echo " checked"; 
echo " onclick=fCheckSelectedFields()>Переводные</input>";
echo "</td></tr>";
//
echo "<tr>";
echo "<td valign=top>";

	echo "<INPUT type=submit name=report_go value=\"Показать отчет\"><INPUT type=submit name=xls_go value=\"XLS\">";
echo "</td>";
echo "</tr>";
echo "</table>";
	echo "<hr>";

echo '</form>';
flush();
?>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_superbill.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
<script>fBodyLoad();</script>
</body>
</html>

