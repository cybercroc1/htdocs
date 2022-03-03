<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
ini_set('max_execution_time','600');
include("../../sc_conf/sc_session");

session_start();

if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php
extract($_REQUEST);

if (!isset($xls_go)) {
/*
echo "<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
</head>
<body topmargin=\"0\">";
*/

	echo "<!DOCTYPE html>
	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1251'>
	<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
	</head>
	<body topmargin=\"0\">";
}

	include("../../sc_conf/sc_conn_string");
	include("../../sc_conf/func_code_phone.php");
	
	//записи разговоров
	if($_SESSION['allow_records']==1) {
		include("../../sc_conf/sc_oktell_conn_string");
		include("../../sc_conf/sc_path");
		include("../../sc_conf/sc_adm_url");
	}	
	//
	
if(!isset($form_id_name) or !isset($cgpn)) exit();
	
$form_id=substr($form_id_name,0,strpos($form_id_name,'_')); //all, ID формы
$form_name=substr($form_id_name,strpos($form_id_name,'_')+1); //Имя формы
	
$cdn=substr($cgpn,strpos($cgpn,'_')+1); //all, null, номер

if(isset($_SESSION['admin']) and $_SESSION['admin']==1) $admin='y'; else $admin='';

//доступ к отчетам
$and_form_ids='';
$and_cdns='';
if($admin<>'y') {
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
	
if($form_id<>'all') {
	//$and_form_id_name="and r.form_id='".$form_id."' and r.form_name='".$form_name."'";
	$and_f_form_id="and f.id='".$form_id."'";
	$and_r_form_id="and r.form_id='".$form_id."'";
}
else {
	//$and_form_id_name='';
	$and_f_form_id='';
	$and_r_form_id='';
}


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

if($admin=='y') {
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
	if($admin=='y') {
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

	$q_obj=OCIParse($c,"select v.object_id,v.object_name,o.type_id,max(v.ordering), max(v.selected) selectable from SC_CALL_REPORT_VALUES v, sc_form_object o
	where v.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_v_rep_period."
	and o.id=v.object_id
	and v.form_id='".$form_id."'
	".($_SESSION['project']['id']==0?'':" and v.project_id='".$_SESSION['project']['id']."' ")."
	".$and_obj_id." 
	group by v.object_id,v.object_name,o.type_id
	order by max(v.ordering)");
	
	OCIExecute($q_obj,OCI_DEFAULT);
	while(OCIFetch($q_obj)) {
		$object_id[$i]=OCIResult($q_obj,"OBJECT_ID");
		$object_name[$i]=OCIResult($q_obj,"OBJECT_NAME");
		$object_type[$i]=OCIResult($q_obj,"TYPE_ID");
		$object_selectable[$i]=OCIResult($q_obj,"SELECTABLE");	
		$i++;
	}

	//запрс на получение данных по полям
	$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id and object_id=:object_id and object_name=:object_name");	
}

if (isset($html_go)) {
	$table="<table bgcolor=gray cellspacing=1 cellpadding=2>";
	$tr="<tr>";
	$tr_head="<tr>";
	$td_head="<td bgcolor=white align=center><b>";
	$end_td_head="</b></td>";
	$td_date="<td bgcolor=white valign=center";
	$td_text="<td bgcolor=white valign=center";
	$td_common="<td bgcolor=white valign=center";
	$end_td="</td>";
	$end_tr="</tr>";
	$end_table="</table>";
}

if (isset($xls_go)) {
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
$td_date="<td class=x2 style='width:auto'";
$td_text="<td class=x3 style='width:auto'";
$td_common="<td class=x4 style='width:auto'";
$end_td="</td>";
$end_tr="</tr>";
$end_table="</table>";
}


if (isset($xls_go) or isset($html_go)) {
	
	//записи разговоров
	if($_SESSION['allow_records']==1) {
		if(isset($html_go)) {
			$link_type='html';
			echo "<audio id=player controls preload=metadata style='width:100%;position:fixed;bottom:0;display:none'>
			</audio>";	
			echo "<iframe name=hidden_frame id=hidden_frame height=0 width=0 style=display:none></iframe>";

		
			$sql="SELECT
			t.id IdConnection, t.IdChain,
			convert(varchar(25),timestart,121) timestart,
			substring(convert(varchar(25),timestart,121),1,4)+
			substring(convert(varchar(25),timestart,121),6,2)+
			substring(convert(varchar(25),timestart,121),9,2)+'\\'+
			substring(convert(varchar(25),timestart,121),12,2)+
			substring(convert(varchar(25),timestart,121),15,2)+'\\' file_path,
			'mix_'+(case when alinenum<blinenum then alinenum else blinenum end)+'_'+(case when blinenum>alinenum then blinenum else alinenum end)+'__'+
			substring(convert(varchar(25),timestart,121),1,4)+'_'+
			substring(convert(varchar(25),timestart,121),6,2)+'_'+
			substring(convert(varchar(25),timestart,121),9,2)+'__'+
			substring(convert(varchar(25),timestart,121),12,2)+'_'+
			substring(convert(varchar(25),timestart,121),15,2)+'_'+
			substring(convert(varchar(25),timestart,121),18,2)+'_'+
			substring(convert(varchar(25),timestart,121),21,3)+'.mp3' file_name,
			t.AOutNumber,t.BOutNumber,
			case 
			when t.ConnectionType=1 then 'Изнутри наружу'
			when t.ConnectionType=2 then 'Изнутри в IVR'
			when t.ConnectionType=3 then 'Изнутри внутрь'
			when t.ConnectionType=4 then 'Снаружи в IVR'
			when t.ConnectionType=5 then 'Снаружи внутрь'
			when t.ConnectionType=6 then 'Снаружи наружу'
			when t.ConnectionType=7 then 'С IVR наружу'
			when t.ConnectionType=8 then 'С IVR внутрь'
			end call_direction
			FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
			where IdChain=:idchain and IsRecorded=1
			order by TimeStart";
			$q_rec=$c_okt->prepare($sql);
		}
		else if (isset($xls_go)) {
			$link_type='xls';
		}		
	}	
	function show_record_link($idchain,$datecall,$link_type) {
		global $oktell_records_path;
		global $oktell_records_url;
		global $q_rec;
		$res='';
		if(preg_match('/^[0-9abcdef]{8}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{12}$/i',$idchain)) { //проверка корректности UUID
			if($link_type=='xls') {
				$src=$oktell_records_url.'?idchain='.$idchain."&datecall=".$datecall;
				$res.="<a href='".$src."' target='wil_records'>Ссылка</a></br>";
			}
			else if($link_type=='html') {
				$q_rec->bindValue(':idchain',$idchain);
				
				$q_rec->execute();
				
				$partnum=0; while($row=$q_rec -> fetch()) {$partnum++;
					
					$file_path=$oktell_records_path.$row['file_path'];
					$file_name=$row['file_name'];
					$new_file_name=$row['file_name'];	

					if(file_exists($file_path.$file_name)) {
						
						if($link_type=='html') {
							$src=$oktell_records_url.'?idconnection='.$row['IdConnection']."&datecall=".$datecall."&partnum=".$partnum;
							$res.="<a href='".$src."' target=hidden_frame onclick='if(pla=document.getElementById(\"player\")){pla.style.display=\"\";pla.src=this.href;pla.play();return false;}else{}'>".
							$partnum.". ".$row['call_direction']."</a>";
						}
						$res.= "<br>";
					}
				}
			}
		}		
		return $res;
	}
	//

	echo "<font size=4>\"".$_SESSION['project']['name']."\"";
	if ($form_id<>'all') echo " - \"".$form_name."\"";
	if ($cdn<>'all' and $cdn<>'null') echo " - ".$cdn;
	else if ($cdn=='null') echo " - без номера доступа"; 
	echo "</font><br>";
	echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b> (включительно)";
	
	$sql_main="select to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call, b.id call_base_id,
	decode(b.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL) direction,
	decode(b.call_direction,'in',b.cdpn,'out',b.dialed_number,'callback',b.cdpn,b.cdpn) aon,
	b.cgpn, b.agid, r.id report_id, r.form_id, 
	replace(r.form_name,'\"','&quot;') form_name, 
	replace(ph.phone_name,'\"','&quot;') phone_name, 
	replace(p.name,'\"','&quot;') project_name,

    b.ivr_sec,
    b.queue_sec,
    b.alerting_sec,
    b.connected_sec,
    case when b.connected_sec<6 then 0 else ceil(b.connected_sec/60) end connected_min,
    case when b.connected_sec<6 then b.connected_sec else b.call_sec end call_sec,
    case when b.connected_sec<6 then 0 else ceil(b.call_sec/60) end call_min,
	b.cdr_thr_id,
    r.id report_id	
	
	from sc_call_base b
	left join sc_projects p on p.id=b.project_id
	left join sc_call_report r on r.call_base_id=b.id
	left join sc_forms f on f.id=r.form_id
	left join sc_phones ph on ph.project_id=b.project_id and ph.phone=b.cgpn 	
	
	where 
	b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') 
					and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_b_rep_period."
		
	".$and_r_form_id."
	
	".$and_b_cdn."

	/*
	--Звонки без отчета
	and ((r.id is null 
	and b.project_id='".$_SESSION['project']['id']."'    
	)or 
	(
	f.project_id='".$_SESSION['project']['id']."'
	and f.deleted is null
	))
	*/
	
	--Только звонки с отчетом
	and f.project_id='".$_SESSION['project']['id']."'
	and f.deleted is null

	".$and_form_ids."
	".$and_cdns."
	
	order by b.date_call, b.id, replace(f.name,'\"','&quot;')";	
	
	//echo "<textarea>$sql_main</textarea>";
	
	$q=OCIParse($c,$sql_main);
	
	OCIExecute($q,OCI_DEFAULT);
		$col_num=1;
		echo $table.$tr_head;
		//if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y') 	{echo $td_head."ID".$end_td_head; $col_num++;}
		if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y') 	{echo $td_head."Дата звонка".$end_td_head; $col_num++;}
		if($_SESSION['project']['id']=='0') {
			echo $td_head.">Проект".$end_td_head;  $col_num++;
		}
		echo $td_head."Направление звонка".$end_td_head; $col_num++;
		if(isset($access_fix['aon']) 			and $access_fix['aon']=='y')		{echo $td_head."АОН".$end_td_head; $col_num++;}
		//if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y')  {echo $td_head."Номер доступа".$end_td_head; $col_num++;}
		if(isset($access_fix['cdn']) 			and $access_fix['cdn']=='y')  		{echo $td_head."Номер доступа".$end_td_head; $col_num++;}
		if(isset($access_fix['agid']) 			and $access_fix['agid']=='y')		{echo $td_head."ID Оператора".$end_td_head; $col_num++;}
		if($form_id=='all') {
			echo $td_head."Отчет".$end_td_head;  $col_num++;
		}
		if($form_id<>'all') {
			if(isset($access_fix['ivr_sec']) 		and $access_fix['ivr_sec']=='y') 		{echo $td_head."Длит.IVR(сек)".$end_td_head; $col_num++;}
			if(isset($access_fix['queue_sec'])	 	and $access_fix['queue_sec']=='y') 		{echo $td_head."Время в очереди(сек)".$end_td_head; $col_num++;}
			if(isset($access_fix['alerting_sec']) 	and $access_fix['alerting_sec']=='y') 	{echo $td_head."Длит.КПВ(сек)".$end_td_head; $col_num++;}
			if(isset($access_fix['connected_sec'])	and $access_fix['connected_sec']=='y')	{echo $td_head."Длит.разговора(сек)".$end_td_head; $col_num++;}
			if(isset($access_fix['connected_min'])	and $access_fix['connected_min']=='y')	{echo $td_head."Длит.разговора(мин)".$end_td_head; $col_num++;}			
			if(isset($access_fix['call_sec']) 		and $access_fix['call_sec']=='y') 		{echo $td_head."Длит.(сек)".$end_td_head; $col_num++;}
			if(isset($access_fix['call_min']) 		and $access_fix['call_min']=='y') 		{echo $td_head."Длит.(мин)".$end_td_head; $col_num++;}			
			
			if(isset($object_id)) {
				foreach ($object_id as $key=>$id) {
					echo $td_head.$object_name[$key].$end_td_head;
					$col_num++;
				}
			}

		}
		if($_SESSION['allow_records']==1)												{echo $td_head."Запись разговора".$end_td_head; $col_num++;}
		echo $end_tr;		
		
		
		$row_num=0;
		$rows_buff='';
		$rows_in_buff='0';
		$perv_call_base_id='';
		while (OCIFetch($q)) {
			$curr_call_base_id=OCIResult($q,"CALL_BASE_ID");
		
			if(isset($xls_go) or ($rows_in_buff>0 and $curr_call_base_id<>$perv_call_base_id)) {
				if($rows_in_buff>1) $rows_buff=str_replace('rowspan=1','rowspan='.$rows_in_buff,$rows_buff);
				echo $rows_buff;
				$rows_buff='';
				$rows_in_buff=0;
			}
		
		$row_num++;
		$rows_in_buff++;
		if($rows_in_buff==1)  $rowspan=' rowspan=1'; else $rowspan="";
		$rows_buff.=$tr;
			
			//if($rowspan!='hidden') {
			//if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y')	$rows_buff.=$td_common.">".OCIResult($q,"CALL_BASE_ID").$end_td;
			if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y')	$rows_buff.=$td_common.">".OCIResult($q,"DATE_CALL").$end_td;
			//}
			//else {
			//if(isset($access_fix['date_call']) 		and $access_fix['date_call']=='y')	$rows_buff.=$td_common." style=display:none>".OCIResult($q,"DATE_CALL").$end_td;
			//}
			
			//if($rowspan!='hidden') {
			if($_SESSION['project']['id']=='0') {
				$rows_buff.=$td_common.">".OCIResult($q,"PROJECT_NAME").$end_td;
			}
			//}
			
			$rows_buff.=$td_common.">".OCIResult($q,"DIRECTION").$end_td;			
			if(isset($access_fix['aon']) 			and $access_fix['aon']=='y')	{
				if(isset($CODED_AON)) $rows_buff.=$td_common.">".phone_conv_coding(OCIResult($q,"AON")).$end_td;  
				else $rows_buff.=$td_common.">".OCIResult($q,"AON").$end_td;
			}
			//if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y')	$rows_buff.=$td_common.">".OCIResult($q,"CGPN").$end_td;
			if(isset($access_fix['cdn']) 				  and $access_fix['cdn']=='y')	$rows_buff.=$td_common.">".OCIResult($q,"CGPN").$end_td;
			if(isset($access_fix['agid']) 				  and $access_fix['agid']=='y')	$rows_buff.=$td_common.">".OCIResult($q,"AGID").$end_td;
			if($form_id=='all') $rows_buff.=$td_common.">".OCIResult($q,"FORM_NAME").$end_td;
			
			if($form_id<>'all') {
				if(isset($access_fix['ivr_sec']) 				and $access_fix['ivr_sec']=='y') 		{$rows_buff.=$td_text.">".OCIResult($q,"IVR_SEC").$end_td;}
				if(isset($access_fix['queue_sec'])	 			and $access_fix['queue_sec']=='y') 		{$rows_buff.=$td_text.">".OCIResult($q,"QUEUE_SEC").$end_td;}
				if(isset($access_fix['alerting_sec']) 			and $access_fix['alerting_sec']=='y') 	{$rows_buff.=$td_text.">".OCIResult($q,"ALERTING_SEC").$end_td;}
				if(isset($access_fix['connected_sec']) 			and $access_fix['connected_sec']=='y')	{$rows_buff.=$td_text.">".OCIResult($q,"CONNECTED_SEC").$end_td;}
				if(isset($access_fix['connected_min'])			and $access_fix['connected_min']=='y')	{$rows_buff.=$td_text.">".OCIResult($q,"CONNECTED_MIN").$end_td;}
				if(isset($access_fix['call_sec']) 				and $access_fix['call_sec']=='y') 		{$rows_buff.=$td_text.">".OCIResult($q,"CALL_SEC").$end_td;}
				if(isset($access_fix['call_min']) 				and $access_fix['call_min']=='y') 		{$rows_buff.=$td_text.">".OCIResult($q,"CALL_MIN").$end_td;}			

				if(isset($object_id)) {
					foreach ($object_id as $key=>$obj_id) {
						
						$tmp_report_id=OCIResult($q,"REPORT_ID");
						OCIBindByName($q_val,":report_id",$tmp_report_id);
						OCIBindByName($q_val,":object_id",$obj_id);
						OCIBindByName($q_val,":object_name",$object_name[$key]);			
						OCIExecute($q_val,OCI_DEFAULT);						

						$rows_buff.=$td_common.">";
							$n=0;
							while(OCIFetch($q_val)) {
								if ($n>0) $rows_buff.="<br>";
								if($object_type[$key]=='CT') {
									$rows_buff.=phone_conv_coding(OCIResult($q_val,"VALUE"));
								}
								else {						
									$rows_buff.=OCIResult($q_val,"VALUE");
								}
								$n++;
							}
						$rows_buff.=$end_td;							
					}
				}			
			}
			
			if($_SESSION['allow_records']==1 and $rows_in_buff==1) {
				$rows_buff.=$td_common.$rowspan.">";
				$rows_buff.=show_record_link(OCIResult($q,"CDR_THR_ID"),
				substr(OCIResult($q,"DATE_CALL"),6,4).
				substr(OCIResult($q,"DATE_CALL"),3,2).
				substr(OCIResult($q,"DATE_CALL"),0,2)."-".
				substr(OCIResult($q,"DATE_CALL"),11,2).
				substr(OCIResult($q,"DATE_CALL"),14,2).
				substr(OCIResult($q,"DATE_CALL"),17,2),
				$link_type
				);
				$rows_buff.=$end_td;
				
			}
			
			
			$rows_buff.=$end_tr;
			$perv_call_base_id=$curr_call_base_id;
		}
		echo $rows_buff;
	if (isset($html_go)) echo $tr."<td bgcolor=white align=left colspan='".$col_num."'><b>ИТОГО: строк ".$row_num."</b></td>".$end_tr;		
	echo $end_table;
	if($_SESSION['allow_records']==1 and isset($html_go)) {echo "<br><br><br>";}
	OCIFreeStatement($q);	
}

//количественный отчет
if (isset($count_go) or isset($count_go_go)) {
	echo "<form action=report.php method=post>";
	
	echo "<font size=4>\"".$_SESSION['project']['name']."\"";
	if ($form_id<>'all') echo " - \"".$form_name."\"";
	if ($cdn<>'all' and $cdn<>'null') echo " - ".$cdn;
	else if ($cdn=='null') echo " - без номера доступа"; 
	echo " - количество</font><br>";
	echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b> (включительно)<hr>";
	echo "<font color=black><b>Сгрупировать отчет по выбранным полям:</b></font><br>";
	
	if(isset($access_fix['date_call']) and $access_fix['date_call']=='y') 	  {echo "<nobr><input type=checkbox name=chk_data"; if(isset($chk_data) or isset($count_go)) echo " checked"; echo">Дата звонка</input></nobr>";}
	
	echo "<nobr><input type=checkbox name=chk_direction"; if(isset($chk_direction)) echo " checked"; echo">Направление звонка</input></nobr>";
	
	if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y') {echo "<nobr><input type=checkbox name=chk_cgpn"; if(isset($chk_cgpn)) echo " checked"; echo">Номер доступа</input></nobr>";}
	if(isset($access_fix['agid']) and $access_fix['agid']=='y') {echo "<nobr><input type=checkbox name=chk_agid"; if(isset($chk_agid)) echo " checked"; echo">ID оператора</input></nobr>";}
	if ($form_id=='all') {echo "<nobr><input type=checkbox name=chk_form"; if(isset($chk_form)) echo " checked"; echo">Тип отчета</input></nobr>";}
	if ($_SESSION['project']['id']=='0') {echo "<nobr><input type=checkbox name=chk_project"; if(isset($chk_project)) echo " checked"; echo">Проект</input></nobr>";}
	echo "<br>";
	
	//получаем список выборочных полей
	if ($form_id<>'all') {
		if(isset($object_id)) {
			foreach ($object_id as $key=>$id) {
				if($object_selectable[$key]=='y') echo "<nobr><input type=checkbox name=selected_columns[".$id."] value='".$object_name[$key]."'".(isset($selected_columns[$id])?" checked":"").">".$object_name[$key]."</input></nobr>";
			}
		}	
	}
	echo "<hr><input type=checkbox".(isset($order_by_count)?" checked":"")." name=order_by_count>Сортировать по количеству</input>";
	echo "<input type=hidden name=cgpn value=\"".$form_id."_".$cdn."\">
	<input type=hidden name=form_id_name value=\"".$form_id."_".str_replace('"','&quot;',$form_name)."\">
	<input type=submit name=count_go_go value=\"Показать отчет\"><hr>";
}
if (isset($count_go_go)) {
	//готовим текст запроса
	$i=0; $ii=0; $j=0;
	$sql1=''; $sql2=''; $sql3=''; $sql4=''; $sql5=''; $sql6=''; $sql7=''; $sql8=''; $sql9='';
	//$sql10=" r.id call_report_id, r.call_base_id,r.form_name "; 
	if (isset($order_by_count)) $sql6=' count(*) desc,';
	echo "<table bgcolor=gray cellpadding=2 cellspacing=1>
	<tr>";
	
	if (!isset($chk_data) and !isset($chk_direction) and !isset($chk_cdpn) and !isset($chk_cgpn) and !isset($chk_agid) and !isset($chk_form) and !isset($chk_project) and !isset($selected_columns)) exit();

	if (isset($chk_data) or isset($chk_direction) or isset($chk_cdpn) or isset($chk_cgpn) or isset($chk_agid) or isset($chk_form) or isset($chk_project) or $cdn<>'all') {

		if (isset($chk_data) and isset($access_fix['date_call']) and $access_fix['date_call']=='y') {
		$sql1.=" trunc(t.date_call) date_call,"; 
		$sql5.=" trunc(t.date_call),";
		$sql6.=" trunc(t.date_call),";
		echo "<td bgcolor=white align=center><b>Дата звонка</b></td>";
		$ii++;
		}
		if (isset($chk_direction)) {
		$sql1.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL) call_direction,"; 
		$sql5.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL),";
		$sql6.=" decode(t.call_direction,'in','Входящий','out','Исходящий','callback','Автоперезвон',NULL),";
		echo "<td bgcolor=white align=center><b>Направлене звонка</b></td>";
		$ii++;
		}		
		if (isset($chk_cgpn) and isset($access_fix['cdn']) and $access_fix['cdn']=='y') {
		$sql1.=" t.cgpn,"; 
		$sql5.=" t.cgpn,";
		$sql6.=" t.cgpn,";
		echo "<td bgcolor=white align=center><b>Номер доступа</b></td>";
		$ii++;
		}
		if (isset($chk_agid) and isset($access_fix['agid']) and $access_fix['agid']=='y') {
		$sql1.=" t.agid,"; 
		$sql5.=" t.agid,";
		$sql6.=" t.agid,";
		echo "<td bgcolor=white align=center><b>ID Оператора</b></td>";
		$ii++;
		}
		if (isset($chk_form)) {
		$sql1.=" t.form_name,"; 
		$sql5.=" t.form_name,";
		$sql6.=" t.form_name,";
		echo "<td bgcolor=white align=center><b>Форма</b></td>";
		$ii++;
		}
		if (isset($chk_project) and $_SESSION['project']['id']=='0') {
		$sql1.=" t.project_name,"; 
		$sql5.=" t.project_name,";
		$sql6.=" t.project_name,";
		echo "<td bgcolor=white align=center><b>Форма</b></td>";
		$ii++;
		}

	}	
	
	if (isset($selected_columns)) {
		foreach ($selected_columns as $obj_id => $obj_name) {
			if($i==0) $sql3.="where ";
			$sql1.="t".$obj_id.".value t".$obj_id.",";			
			$sql2.=",SC_CALL_REPORT_VALUES t".$obj_id;
			if ($i>0) $sql3.="and ";
			$sql3.="t".$obj_id.".object_id(+)='".$obj_id."' and t".$obj_id.".object_name(+)='".$obj_name."' ";
			$sql4.="and t".$obj_id.".call_report_id(+)=t.call_report_id ";
			$sql5.="t".$obj_id.".value,";
			$sql6.="t".$obj_id.".value,";
			echo "<td bgcolor=white align=center><b>".$obj_name."</b></td>";
			$i++;
		}
	}
	
	if(!isset($selected_columns) and !isset($chk_form)) {//только поля SC_CALL_BASE
		$subquery_select="distinct b.id, b.date_call,b.call_direction, b.cgpn,b.agid,p.name project_name";
	}
	else {
		$subquery_select="b.date_call,b.call_direction, b.cgpn,b.agid,p.name project_name, r.id call_report_id, r.call_base_id, r.form_name";
	}

echo "<td bgcolor=white align=center><b>Кол-во</b></td></tr>";	

$sql2=rtrim($sql2,",");
$sql5=rtrim($sql5,",");
$sql6=rtrim($sql6,",");



$sql_main="select 
".$sql1." 
count(*) count
from 
(
select ".$subquery_select."

	from sc_call_base b
	left join sc_projects p on p.id=b.project_id
	left join sc_call_report r on r.call_base_id=b.id
	left join sc_forms f on f.id=r.form_id
	left join sc_phones ph on ph.project_id=b.project_id and ph.phone=b.cgpn 	   
		
	where 
	b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') 
					and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
	".$and_b_rep_period."
		
	".$and_r_form_id."
	
	".$and_b_cdn."

	/*
	--Звонки без отчета
	and ((r.id is null 
	and b.project_id='".$_SESSION['project']['id']."'    
	)or 
	(
	f.project_id='".$_SESSION['project']['id']."'
	and f.deleted is null
	))
	*/
	
	--Только звонки с отчетом
	and f.project_id='".$_SESSION['project']['id']."'
	and f.deleted is null

	".$and_form_ids."
	".$and_cdns."	
	
) t 
".$sql2."
".$sql3."
".$sql4."
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
	if (isset($chk_direction)) echo "<td bgcolor=white>".OCIResult($q,"CALL_DIRECTION")."</td>";
	if (isset($chk_cdpn)) echo "<td bgcolor=white>".OCIResult($q,"CDPN")."</td>";
	if (isset($chk_cgpn)) echo "<td bgcolor=white>".OCIResult($q,"CGPN")."</td>";
	if (isset($chk_agid)) echo "<td bgcolor=white>".OCIResult($q,"AGID")."</td>";
	if (isset($chk_form)) echo "<td bgcolor=white>".OCIResult($q,"FORM_NAME")."</td>";
	if (isset($chk_project) and $_SESSION['project']['id']=='0') echo "<td bgcolor=white>".OCIResult($q,"PROJECT_NAME")."</td>";
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