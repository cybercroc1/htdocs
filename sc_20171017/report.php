<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','600');
include("../../sc_conf/sc_session");
session_start();
if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['view_rep'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php
extract($_REQUEST);

	include("../../sc_conf/sc_conn_string");
	include("../../sc_conf/func_code_phone.php");

//УДАЛЕНИЕ ЗАПИСЕЙ
if (isset($del_records)) {
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "У вас не достаточно прав для удаления!"; exit();}
	if ($and_form_id_name=='') {
	$q_del=OCIParse($c,"delete from sc_call_base b
where date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
".$and_cgpn." ");
	OCIExecute($q_del,OCI_COMMIT_ON_SUCCESS);	
	}
	if ($and_form_id_name<>'') {
	$q=OCIParse($c,"select r.id report_id
  from sc_call_base b, sc_call_report r
 where b.date_call between to_date('".$_SESSION['start_rep_date']."', 'DD.MM.YYYY') and
       to_date('".$_SESSION['end_rep_date']."', 'DD.MM.YYYY') + 1
   ".$and_cgpn."
   and b.id = r.call_base_id
   and b.project_id = '".$_SESSION['project_id'][$_SESSION['i']]."'
	".$and_form_id_name." ");

	$q_del=OCIParse($c,"delete from sc_call_report where id=:report_id");
		OCIExecute($q,OCI_DEFAULT);
		while(OCIFetch($q)) {
			OCIBindByName($q_del,":report_id",OCIResult($q,"REPORT_ID"));
			OCIExecute($q_del,OCI_COMMIT_ON_SUCCESS);	
		}
	}

}//удаление записей

//Отчет
	//проверка доступа к полям
	if(isset($_SESSION['no_all_forms_access']) and isset($form_id_name)) {
		$q=OCIParse($c,"select form_id,date_call,cdpn,cgpn,agid,ivr_sec,queue_sec,alerting_sec,connected_sec,connected_min,call_sec,call_min 
		from sc_access_form_fix where login_id='".$_SESSION['login_id']."' 
		and form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'");
		OCIExecute($q,OCI_DEFAULT);
		if(OCIFetch($q)) {
			if(OCIResult($q,"DATE_CALL")=='y') {$access_fix['date_call']='';}
			if(OCIResult($q,"CDPN")=='y') {$access_fix['aon']='';}
			if(OCIResult($q,"AGID")=='y') {$access_fix['agid']='';}
			if(OCIResult($q,"IVR_SEC")=='y') {$access_fix['ivr_sec']='';}
			if(OCIResult($q,"QUEUE_SEC")=='y') {$access_fix['queue_sec']='';}
			if(OCIResult($q,"ALERTING_SEC")=='y') {$access_fix['alerting_sec']='';}
			if(OCIResult($q,"CONNECTED_SEC")=='y') {$access_fix['connected_sec']='';}
			if(OCIResult($q,"CONNECTED_MIN")=='y') {$access_fix['connected_min']='';}
			if(OCIResult($q,"CALL_SEC")=='y') {$access_fix['call_sec']='';}
			if(OCIResult($q,"CALL_MIN")=='y') {$access_fix['call_min']='';}
		}
		
		$q=OCIParse($c,"select obj_id from sc_access_form where login_id='".$_SESSION['login_id']."' and form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($q)) {
			$obj_ids[$i]="'".OCIResult($q,"OBJ_ID")."'";
		$i++;
		}			
		if(isset($obj_ids)) {
		$and_obj_id=' and b.object_id in ('.implode(",",$obj_ids).') ';
		} 
		else {
		$and_obj_id=" and b.object_id in ('') ";
		}
	}
	else {
	$access_fix=array('date_call'=>'','aon'=>'','agid'=>'');
	$and_obj_id='';
		if(isset($form_id_name) and $form_id_name<>'all_report') {
		$q=OCIParse($c,"select show_ivr_sec,show_queue_sec,show_alerting_sec,show_connected_sec,show_connected_min,show_call_sec,show_call_min,CODED_AON
		  		from sc_forms where id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"SHOW_IVR_SEC")=='y') $access_fix['ivr_sec']='';
		if(OCIResult($q,"SHOW_QUEUE_SEC")=='y') $access_fix['queue_sec']='';
		if(OCIResult($q,"SHOW_ALERTING_SEC")=='y') $access_fix['alerting_sec']='';
		if(OCIResult($q,"SHOW_CONNECTED_SEC")=='y') $access_fix['connected_sec']='';
		if(OCIResult($q,"SHOW_CONNECTED_MIN")=='y') $access_fix['connected_min']='';
		if(OCIResult($q,"SHOW_CALL_SEC")=='y') $access_fix['call_sec']='';
		if(OCIResult($q,"SHOW_CALL_MIN")=='y') $access_fix['call_min']=''; 
		if(OCIResult($q,"CODED_AON")=='y') $CODED_AON=''; 
		}
	}
	
if (!isset($xls_go)) {
echo "<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
</head>
<body topmargin=\"0\">";
}

if (isset($html_go)) {
	if(($form_id_name=='' or $form_id_name=='all_report') and isset($_SESSION['no_all_forms_access'])) exit();
$table="<table bgcolor=gray cellspacing=1 cellpadding=2>";
$tr="<tr>";
$tr_head="<tr>";
$td_head="<td bgcolor=white align=center><b>";
$end_td_head="</b></td>";
$td_date="<td bgcolor=white valign=top>";
$td_text="<td bgcolor=white valign=top>";
$td_common="<td bgcolor=white valign=top>";
$end_td="</td>";
$end_tr="</tr>";
$end_table="</table>";
}

if (isset($xls_go)) {
	if(($form_id_name=='' or $form_id_name=='all_report') and isset($_SESSION['no_all_forms_access'])) exit();
header("Content-type: application/xls");
header("Content-Disposition: attachment; filename=\"rep-".$_SESSION['start_rep_date']."-".$_SESSION['end_rep_date']."-ID".substr($form_id_name,0,strpos($form_id_name,'_')).".xls\""); 

echo "<html xmlns:v=\"urn:schemas-microsoft-com:vml\"
xmlns:o=\"urn:schemas-microsoft-com:office:office\"
xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
xmlns=\"http://www.w3.org/TR/REC-html40\">
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
<meta http-equiv='X-UA-Compatible' content='IE=EmulateIE7'>
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content=\"Microsoft Excel 11\">
<!--[if !mso]>
<style>
v\:* {behavior:url(#default#VML);}
o\:* {behavior:url(#default#VML);}
x\:* {behavior:url(#default#VML);}
.shape {behavior:url(#default#VML);}
</style>
<![endif]-->
<style>
<!--table
	{mso-displayed-decimal-separator:\"\,\";
	mso-displayed-thousand-separator:\" \";}
@page
	{margin:.98in .79in .98in .79in;
	mso-header-margin:.5in;
	mso-footer-margin:.5in;}
.font0
	{color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:\"Arial Cyr\";
	mso-generic-font-family:auto;
	mso-font-charset:204;}
.font6
	{color:windowtext;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:\"Arial Cyr\";
	mso-generic-font-family:auto;
	mso-font-charset:204;}
tr
	{mso-height-source:auto;}
col
	{mso-width-source:auto;}
br
	{mso-data-placement:same-cell;}
.style0
	{mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	white-space:nowrap;
	mso-rotate:0;
	mso-background-source:auto;
	mso-pattern:auto;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:\"Arial Cyr\";
	mso-generic-font-family:auto;
	mso-font-charset:204;
	border:none;
	mso-protection:locked visible;
	mso-style-name:Обычный;
	mso-style-id:0;}
td
	{mso-style-parent:style0;
	padding:0px;
	mso-ignore:padding;
	color:windowtext;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:\"Arial Cyr\";
	mso-generic-font-family:auto;
	mso-font-charset:204;
	mso-number-format:General;
	text-align:general;
	vertical-align:bottom;
	border:none;
	mso-background-source:auto;
	mso-pattern:auto;
	mso-protection:locked visible;
	white-space:nowrap;
	mso-rotate:0;}
.xl
	{mso-style-parent:style0;
	font-weight:700;
	mso-number-format:\"\@\";
	text-align:center;
	border-top:.5pt solid black;
	border-right:.5pt solid black;
	border-bottom:.5pt solid black;
	border-left:.5pt solid black;
	white-space:normal;
	mso-text-control:shrinktofit;}
.x2
	{mso-style-parent:style0;
	mso-number-format:\"dd\/mm\/yy\\ h\:mm\;\@\";
	border-top:.5pt solid black;
	vertical-align:justify;
	border-right:.5pt solid black;
	border-bottom:.5pt solid black;
	border-left:.5pt solid black;
	white-space:normal;}
.x3
	{mso-style-parent:style0;
	mso-number-format:\"\@\";
	border-top:none;
	vertical-align:justify;
	border-right:.5pt solid black;
	border-bottom:.5pt solid black;
	border-left:.5pt solid black;
	white-space:normal;}
.x4
	{mso-style-parent:style0;
	border-top:none;
	vertical-align:top;
	border-right:.5pt solid black;
	border-bottom:.5pt solid black;
	border-left:.5pt solid black;
	white-space:normal;}
-->
</style>
<!--[if gte mso 9]><xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Лист1</x:Name>
    <x:WorksheetOptions>
     <x:Selected/>
     <x:FreezePanes/>
     <x:SplitHorizontal>3</x:SplitHorizontal>
     <x:TopRowBottomPane>3</x:TopRowBottomPane>
     <x:ActivePane>2</x:ActivePane>
     <x:Panes>
      <x:Pane>
       <x:Number>3</x:Number>
      </x:Pane>
      <x:Pane>
       <x:Number>2</x:Number>
       <x:ActiveRow>2</x:ActiveRow>
      </x:Pane>
     </x:Panes>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml><![endif]-->
</head>
<body link=blue vlink=purple topmargin=0>";

$table="<table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse:
 collapse;table-layout:auto;width:auto'>";
$tr="<tr style='height:auto'>";
$tr_head="<tr style='height:38.25pt'>";
$td_head="<td class=xl x:autofilter=\"all\" style='width:auto;height:38.25pt'>";
$end_td_head="</td>";
$td_date="<td class=x2 style='width:auto'>";
$td_text="<td class=x3 style='width:auto'>";
$td_common="<td class=x4 style='width:auto'>";
$end_td="</td>";
$end_tr="</tr>";
$end_table="</table>";
}


if (isset($xls_go) or isset($html_go)) {

if ($_SESSION['rep_period']<>'') $and_rep_period=" and b.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') "; else $and_rep_period='';

echo "<font size=4>\"".$_SESSION['project_name'][$_SESSION['i']]."\"";
if ($form_id_name<>'' and $form_id_name<>'all_report') echo " - \"".substr($form_id_name,strpos($form_id_name,'_')+1)."\"";
if ($cgpn<>'') echo " - ".$cgpn; 
echo "</font><br>";
echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b> (включительно)";
if (isset($_SESSION['admin']) and $_SESSION['admin']==1 and !isset($xls_go)) echo ' <a href="javascript:if(confirm(\'Действительно хотите УДАЛИТЬ ЗАПИСИ ?\')){del_records.submit();}"><img src=del.gif title="Удалить записи" border=0></a>';

if ($form_id_name=='' or $form_id_name=='all_report') {$and_form_id_name="";}
else {$and_form_id_name=" and r.form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'
and r.form_name='".substr($form_id_name,strpos($form_id_name,'_')+1)."'";}
if ($cgpn=='') {$and_cgpn="";}
else {$and_cgpn=" and b.cgpn='".$cgpn."' ";}

	//проверка доступа к номерам
	$q=OCIParse($c,"select phone from SC_ACCESS_PHONE where login_id=".$_SESSION['login_id']);
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while(OCIFetch($q)) {
		$cdpns[$i]="'".OCIResult($q,"PHONE")."'";
		$i++;
	}
	if(isset($cdpns)) {
		$and_cdpns=" and b.cgpn in (".implode(",",$cdpns).") ";
	}			
	else $and_cdpns="";

	if (isset($_SESSION['admin']) and $_SESSION['admin']==1 and !isset($xls_go)) {
	echo '<form name=del_records method=post>
	<input type=hidden name=and_form_id_name value="'.str_replace('"','&quot;',$and_form_id_name).'">
	<input type=hidden name=and_cgpn value="'.$and_cgpn.'">
	<input type=hidden name=del_records value=1>
	</form>';
	}

if ($form_id_name=='' or $form_id_name=='all_report') { //отчет по всем формам
	$q=OCIParse($c,"select b.id call_id,to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call,
	b.cdpn aon, b.cgpn, b.agid, r.id report_id, r.form_id, r.form_name,f.CODED_AON
	from sc_call_base b, sc_call_report r, sc_forms f
	where b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	and b.id=r.call_base_id 
	and f.id=r.form_id
	and b.project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
	".$and_rep_period."
	".$and_cgpn."
	".$and_cdpns." 
	order by b.date_call");
	OCIExecute($q,OCI_DEFAULT);
		echo $table.$tr_head.
		$td_head."Дата звонка".$end_td_head.
		$td_head."АОН".$end_td_head;
		if ($cgpn=='') echo $td_head."Номер доступа".$end_td_head;
		echo $td_head."ID Оператора".$end_td_head
		.$td_head."Отчет".$end_td_head;
		if ($cgpn=='') $col_num=5; else $col_num=4;
		$row_num=0;
		while (OCIFetch($q)) {
		$row_num++;
		echo $tr.
		$td_common.OCIResult($q,"DATE_CALL").$end_td;
		if(OCIResult($q,"CODED_AON")=='y') echo $td_common.phone_conv_coding(OCIResult($q,"AON")).$end_td;  
		else echo $td_common.OCIResult($q,"AON").$end_td;
		if ($cgpn=='') echo $td_common.OCIResult($q,"CGPN")."</td>";
		echo $td_common.OCIResult($q,"AGID").$end_td.
		$td_common.OCIResult($q,"FORM_NAME").$end_td.
		$end_tr;
		}
	if (isset($html_go)) echo $tr."<td bgcolor=white align=left colspan='".$col_num."'><b>ИТОГО: строк ".$row_num."</b></td>".$end_tr;		
	echo $end_table;
	OCIFreeStatement($q);	
	}  //отчет по всем формам
	else { //отчет по конкретной форме
	//получаем список полей таблицы
	$col_num=0;
	echo $table.$tr_head; $col_num++;
	if(isset($access_fix['date_call'])) {echo $td_head."Дата звонка".$end_td_head; $col_num++;}
	if(isset($access_fix['aon'])) {echo $td_head."АОН".$end_td_head; $col_num++;}
	if ($cgpn=='') {echo $td_head."Номер доступа".$end_td_head; $col_num++;}
	if(isset($access_fix['agid'])) {echo $td_head."ID Оператора".$end_td_head; $col_num++;}
	if(isset($access_fix['ivr_sec'])) {echo $td_head."Длит.IVR(сек)".$end_td_head; $col_num++;}
	if(isset($access_fix['queue_sec'])) {echo $td_head."Время в очереди(сек)".$end_td_head; $col_num++;}
	if(isset($access_fix['alerting_sec'])) {echo $td_head."Длит.КПВ(сек)".$end_td_head; $col_num++;}
	if(isset($access_fix['connected_sec'])) {echo $td_head."Длит.разговора(сек)".$end_td_head; $col_num++;}
	if(isset($access_fix['connected_min'])) {echo $td_head."Длит.разговора(мин)".$end_td_head; $col_num++;}			
	if(isset($access_fix['call_sec'])) {echo $td_head."Длит.(сек)".$end_td_head; $col_num++;}
	if(isset($access_fix['call_min'])) {echo $td_head."Длит.(мин)".$end_td_head; $col_num++;}
	$h=OCIParse($c,"select b.object_id,b.object_name,o.type_id,max(b.ordering) from SC_CALL_REPORT_VALUES b, sc_form_object o
	where b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_rep_period."
	and o.id=b.object_id
	and b.form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'
	and b.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' 
	".$and_obj_id." 
	group by b.object_id,b.object_name,o.type_id
	order by max(b.ordering)");

	OCIExecute($h,OCI_DEFAULT);
	$decode=''; 
		$i=0;
		while(OCIFetch($h)) {
		echo $td_head.OCIResult($h,"OBJECT_NAME").$end_td_head;
		$object_id[$i]=OCIResult($h,"OBJECT_ID");
		$object_name[$i]=OCIResult($h,"OBJECT_NAME");
		$object_type[$i]=OCIResult($h,"TYPE_ID");
		$i++;
		}
	$col_num+=$i-1;	
	echo $end_tr;
	//
	//Готовим запросы
	$q_text="select b.id call_id,
       to_char(b.date_call, 'DD.MM.YYYY HH24:MI:SS') date_call,
       b.cdpn aon,
       b.cgpn,
       b.agid,
       b.ivr_sec,
       b.queue_sec,
       b.alerting_sec,
       b.connected_sec,
       case when b.connected_sec<6 then 0 else ceil(b.connected_sec/60) end connected_min,
       case when b.connected_sec<6 then b.connected_sec else b.call_sec end call_sec,
       case when b.connected_sec<6 then 0 else ceil(b.call_sec/60) end call_min,
       r.id report_id
  from sc_call_base b, sc_call_report r
 where r.date_call between to_date('".$_SESSION['start_rep_date']."', 'DD.MM.YYYY') and
       to_date('".$_SESSION['end_rep_date']."', 'DD.MM.YYYY') + 1
	   ".$and_rep_period."   
   ".$and_cgpn."
   ".$and_cdpns."
   and r.project_id = '".$_SESSION['project_id'][$_SESSION['i']]."'
	".$and_form_id_name."
   and b.id = r.call_base_id
   order by b.date_call,r.id";
   //echo $q_text;
	$q=OCIParse($c,$q_text);
	//
	OCIExecute($q,OCI_DEFAULT);
		$row_num=0;
		while (OCIFetch($q)) {
			$row_num++;
			echo $tr;
			if(isset($access_fix['date_call'])) {echo $td_date.OCIResult($q,"DATE_CALL").$end_td;}
			if(isset($access_fix['aon'])) {
				if(isset($CODED_AON)) echo $td_text.phone_conv_coding(OCIResult($q,"AON")).$end_td;
				else echo $td_text.OCIResult($q,"AON").$end_td;
			}
			if ($cgpn=='') echo $td_text.OCIResult($q,"CGPN").$end_td;
			if(isset($access_fix['agid'])) {echo $td_text.OCIResult($q,"AGID").$end_td;}
			if(isset($access_fix['ivr_sec'])) {echo $td_text.OCIResult($q,"IVR_SEC").$end_td;}
			if(isset($access_fix['queue_sec'])) {echo $td_text.OCIResult($q,"QUEUE_SEC").$end_td;}
			if(isset($access_fix['alerting_sec'])) {echo $td_text.OCIResult($q,"ALERTING_SEC").$end_td;}
			if(isset($access_fix['connected_sec'])) {echo $td_text.OCIResult($q,"CONNECTED_SEC").$end_td;}
			if(isset($access_fix['connected_min'])) {echo $td_text.OCIResult($q,"CONNECTED_MIN").$end_td;}
			if(isset($access_fix['call_sec'])) {echo $td_text.OCIResult($q,"CALL_SEC").$end_td;}
			if(isset($access_fix['call_min'])) {echo $td_text.OCIResult($q,"CALL_MIN").$end_td;}
				for($j=0; $j<$i; $j++) {
				
				$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id and object_id='".$object_id[$j]."' and object_name=:object_name");	
				$v_rep_id=OCIResult($q,"REPORT_ID");
				OCIBindByName($q_val,":report_id",$v_rep_id);
				OCIBindByName($q_val,":object_name",$object_name[$j]);			
				OCIExecute($q_val,OCI_DEFAULT);

				echo $td_common;
					$n=0;
					while(OCIFetch($q_val)) {
						if ($n>0) echo "<br>";
						//echo OCIResult($q_val,"VALUE");
						if($object_type[$j]=='CT') {
							echo phone_conv_coding(OCIResult($q_val,"VALUE"));
						}
						else {						
							echo OCIResult($q_val,"VALUE");
						}
						$n++;
					}
				echo $end_td;	
				}		
			echo $end_tr;		
		}
		
	if (isset($html_go)) echo $tr."<td bgcolor=white align=left colspan='".$col_num."'><b>ИТОГО: строк ".$row_num."</b></td>".$end_tr;		
	echo $end_table;
	OCIFreeStatement($q);
	}  //отчет по кнкретной форме
	
}

//количественный отчет
if (isset($count_go) or isset($count_go_go)) {
	if(($form_id_name=='' or $form_id_name=='all_report') and isset($_SESSION['no_all_forms_access'])) exit();
echo "<form action=report.php method=post>";

if ($_SESSION['rep_period']<>'') $and_rep_period=" and date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') "; else $and_rep_period='';

echo "<font size=4>\"".$_SESSION['project_name'][$_SESSION['i']]."\"";
if ($form_id_name<>'' and $form_id_name<>'all_report') echo " - \"".substr($form_id_name,strpos($form_id_name,'_')+1)."\"";
if ($cgpn<>'') echo " - ".$cgpn; 
echo " - количество</font><br>";
echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b> (включительно)<hr>";
echo "<font color=black><b>Сгрупировать отчет по выбранным полям:</b></font><br>";
if(isset($access_fix['date_call'])) {echo "<nobr><input type=checkbox name=chk_data"; if(isset($chk_data) or isset($count_go)) echo " checked"; echo">Дата звонка</input></nobr>";}
//if(isset($access_fix['aon'])) {echo "<nobr><input type=checkbox name=chk_cdpn"; if(isset($chk_cdpn)) echo " checked"; echo">АОН</input></nobr>";}
if ($cgpn=='') {echo "<nobr><input type=checkbox name=chk_cgpn"; if(isset($chk_cgpn)) echo " checked"; echo">Номер доступа</input></nobr>";}
if(isset($access_fix['agid'])) {echo "<nobr><input type=checkbox name=chk_agid"; if(isset($chk_agid)) echo " checked"; echo">ID оператора</input></nobr>";}
if ($form_id_name=='' or $form_id_name=='all_report') {echo "<nobr><input type=checkbox name=chk_form"; if(isset($chk_form)) echo " checked"; echo">Тип отчета</input></nobr>";}
echo "<br>";

//получаем список выборочных полей
if ($form_id_name<>'' and $form_id_name<>'all_report') {
	$h=OCIParse($c,"select object_id,object_name,max(ordering) from SC_CALL_REPORT_VALUES b
where b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
".$and_rep_period."
and b.form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."'
and b.project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
and b.selected='y' 
".$and_obj_id." 
group by object_id,object_name
order by max(ordering)");
	OCIExecute($h,OCI_DEFAULT);
		$i=0; $ii=0;
		while(OCIFetch($h)) {
		echo "<nobr><input type=checkbox name=selected_columns[] value='".$i."'"; if (isset($selected_columns[$ii]) and $selected_columns[$ii]==$i) {echo " checked"; $ii++;} echo ">".OCIResult($h,"OBJECT_NAME")."</input></nobr>";
		$_SESSION['object_ids'][$i]=OCIResult($h,"OBJECT_ID");
		$_SESSION['object_names'][$i]=OCIResult($h,"OBJECT_NAME");
		$i++;
		} //список выборочных полей
	}	
echo "<hr><input type=checkbox"; if(isset($order_by_count)) echo " checked"; echo" name=order_by_count>Сортировать по количеству</input>";
echo "<input type=hidden name=cgpn value=\"".$cgpn."\">
<input type=hidden name=form_id_name value=\"".str_replace('"','&quot;',$form_id_name)."\">
<input type=submit name=count_go_go value=\"Показать отчет\"><hr>";
}

if (isset($count_go_go)) {
	//готовим текст запроса
	$i=0; $ii=0; $j=0;
	$sql1=''; $sql2=''; $sql3=''; $sql4=''; $sql5=''; $sql6=''; $sql7=''; $sql8=''; $sql9='';
	$sql10=" id call_report_id, call_base_id,form_name "; 
	if (isset($order_by_count)) $sql6=' count(*) desc,';
	echo "<table bgcolor=gray cellpadding=2 cellspacing=1>
	<tr>";
	if (!isset($chk_data) and !isset($chk_cdpn) and !isset($chk_cgpn) and !isset($chk_agid) and !isset($chk_form) and !isset($selected_columns)) exit();

	if (isset($chk_data) or isset($chk_cdpn) or isset($chk_cgpn) or isset($chk_agid) or isset($chk_form) or $cgpn<>'') {
	$sql7.="sc_call_base b,";
	if (isset($selected_columns)) $sql8.=" and ";
	$sql8.=" t.call_base_id=b.id ";
	if ($cgpn<>'') $sql8.=" and b.cgpn='".$cgpn."' ";
		if (isset($chk_data) and isset($access_fix['date_call'])) {
		$sql1.=" trunc(b.date_call) date_call,"; 
		$sql5.=" trunc(b.date_call),";
		$sql6.=" trunc(b.date_call),";
		echo "<td bgcolor=white align=center><b>Дата звонка</b></td>";
		$ii++;
		}
		if (isset($chk_cdpn) and isset($access_fix['aon'])) {
		$sql1.=" b.cdpn,"; 
		$sql5.=" b.cdpn,";
		$sql6.=" b.cdpn,";
		echo "<td bgcolor=white align=center><b>АОН</b></td>";
		$ii++;
		}
		if (isset($chk_cgpn)) {
		$sql1.=" b.cgpn,"; 
		$sql5.=" b.cgpn,";
		$sql6.=" b.cgpn,";
		echo "<td bgcolor=white align=center><b>Номер доступа</b></td>";
		$ii++;
		}
		if (isset($chk_agid) and isset($access_fix['agid'])) {
		$sql1.=" b.agid,"; 
		$sql5.=" b.agid,";
		$sql6.=" b.agid,";
		echo "<td bgcolor=white align=center><b>ID Оператора</b></td>";
		$ii++;
		}
		if (isset($chk_form)) {
		$sql1.=" t.form_name,"; 
		$sql5.=" t.form_name,";
		$sql6.=" t.form_name,";
		echo "<td bgcolor=white align=center><b>ID Оператора</b></td>";
		$ii++;
		}
	}	
	
	if (isset($selected_columns)) {
		foreach ($selected_columns as $j) {
			$sql1.="t".$j.".value t".$j.",";			
			$sql2.=",SC_CALL_REPORT_VALUES t".$j;
			if ($i>0) $sql3.="and ";
			$sql3.="t".$j.".object_id(+)='".$_SESSION['object_ids'][$j]."' and t".$j.".object_name(+)='".$_SESSION['object_names'][$j]."' ";
			$sql4.="and t".$j.".call_report_id(+)=t.call_report_id ";
			$sql5.="t".$j.".value,";
			$sql6.="t".$j.".value,";
			echo "<td bgcolor=white align=center><b>".$_SESSION['object_names'][$j]."</b></td>";
			$i++;
		}
	}
	
	if ($form_id_name<>'' and $form_id_name<>'all_report') {
		$sql9.=" form_id='".substr($form_id_name,0,strpos($form_id_name,'_'))."' and form_name='".substr($form_id_name,strpos($form_id_name,'_')+1)."' and ";
	} 
	if (($form_id_name=='' or $form_id_name=='all_report') and !isset($chk_form)) $sql10=" distinct call_base_id ";


echo "<td bgcolor=white align=center><b>Кол-во</b></td></tr>";	

$sql2=rtrim($sql2,",");
$sql5=rtrim($sql5,",");
$sql6=rtrim($sql6,",");

$sql_main="select 
".$sql1." 
count(*) count
from 
".$sql7."
(select ".$sql10." from sc_call_report
where 
".$sql9."
project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and   
date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
".$and_rep_period.") t 
".$sql2."
 where 
".$sql3."
".$sql4."
".$sql8."
 group by 
".$sql5."
 order by 
".$sql6;

$row_cnt=0; $sum=0;
//echo $sql_main;
$q=OCIParse($c,$sql_main);
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr>";
	if (isset($chk_data)) echo "<td bgcolor=white>".OCIResult($q,"DATE_CALL")."</td>";
	if (isset($chk_cdpn)) echo "<td bgcolor=white>".OCIResult($q,"CDPN")."</td>";
	if (isset($chk_cgpn)) echo "<td bgcolor=white>".OCIResult($q,"CGPN")."</td>";
	if (isset($chk_agid)) echo "<td bgcolor=white>".OCIResult($q,"AGID")."</td>";
	if (isset($chk_form)) echo "<td bgcolor=white>".OCIResult($q,"FORM_NAME")."</td>";
		if (isset($selected_columns)) {
			for($j=0; $j<count($selected_columns); $j++) {
			echo "<td bgcolor=white>".OCIResult($q,$j+1+$ii)."</td>";
			}
		}
	echo "<td bgcolor=white><b>".OCIResult($q,"COUNT")."</b></td></tr>";
	$sum+=OCIResult($q,"COUNT");
	$row_cnt++;
	}	
echo "<tr><td bgcolor=white colspan=\"".($j+$ii)."\"><b>ИТОГО: строк ".$row_cnt."</b></td><td bgcolor=white><b>".$sum."</b></td></tr>";
}
//количественный отчет

//отчет

?>
</body>
</html>