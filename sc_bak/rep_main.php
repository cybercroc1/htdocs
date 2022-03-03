<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body leftmargin="5" topmargin="0">
<script>
function ch_rep_type(form_id_name) {
	
	
	var arr_tmp=form_id_name.split("_");
	var form_id1=arr_tmp[0];
	
	obj2=document.getElementById('sel_cdn');
	
	var sel=document.getElementById('sel_cdn');
	
	for(i=sel.options.length-1; i>=0; i--) {
		sel.removeChild(sel.options[i]); 
	}
	
	//var select_text='';
	for(i=0; i<dn_arr.length; i++) {
		var arr_tmp2=dn_arr[i].split("_");
		var form_id2=arr_tmp2[0];
		var tmp_cdn=arr_tmp2[1];
		var tmp_cdn_name=arr_tmp2[2];
		if(form_id2==form_id1) {
			var opt = document.createElement('option');
			opt.value=form_id2+"_"+tmp_cdn;
			opt.innerText=tmp_cdn_name;
			//alert(sel);
			sel.appendChild(opt);
			
	//		select_text+="<option value='"+form_id2+"'_'"+tmp_cdn+"'>"+tmp_cdn_name+"</option>";
		}
		
	}
	//obj2.innerHTML=select_text;
	
	
	if (document.all.form_id_name.value=='') {
	//document.all.html_go.disabled=true;
	//document.all.xls_go.disabled=true;
	//document.all.count_go.disabled=true;
	document.all.td_rep_type.style.color='red';
	}
	else {
	//document.all.html_go.disabled=false;
	//document.all.xls_go.disabled=false;
	//document.all.count_go.disabled=false;
	document.all.td_rep_type.style.color='black';
	}
}	
</script>
<?php //if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
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

echo "<font size=4> Отчеты - \"".$_SESSION['project']['name']."\"</font><br>";
	if ($_SESSION['rep_period']<>'') {
	echo "<font color=red>Отчеты предоставляются с <b>".$_SESSION['rep_period']."</b></font>";
	$and_rep_period=" and b.date_call>=to_date('".$_SESSION['rep_period']."','DD.MM.YYYY') ";
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
	
	//Доступ к звонкам без отчета
	if($_SESSION['allow_noreport']==1) {
		$and_norep="
			--Звонки без отчета
			and ((r.id is null 
			and b.project_id='".$_SESSION['project']['id']."'    
			)or 
			(
			f.project_id='".$_SESSION['project']['id']."'
			and f.deleted is null
			))
		";
	}
	else {
		$and_norep="
			--Только звонки с отчетом
			and f.project_id='".$_SESSION['project']['id']."'
			and f.deleted is null
		";		
	}
	
	//только отчеты по звонку
	if($_SESSION['allow_nocall']<>'1') {
		$and_nocall="
			--отчеты только по звонку
			and (b.cdpn is not null or b.cgpn is not null or b.agid is not null or b.cdr_thr_id is not null)
		";
	} else $and_nocall="";
	
	
	
	echo "<table><form method=post action=report.php target=fr2>";
	//список форм
		$sql="select distinct b.cgpn, r.form_id, replace(r.form_name,'\"','&quot;') form_name, replace(ph.phone_name,'\"','&quot;') phone_name
		from sc_call_base b
		left join sc_call_report r on r.call_base_id=b.id
		left join sc_forms f on f.id=r.form_id
		left join sc_phones ph on ph.project_id=b.project_id and ph.phone=b.cgpn 
		
		where 
		b.date_call between to_date('".$_SESSION['start_rep_date']."','DD.MM.YYYY') 
						and to_date('".$_SESSION['end_rep_date']."','DD.MM.YYYY')+1
		".$and_rep_period."	
		".$and_nocall."
		".$and_norep."
		".$and_form_ids."
		".$and_cdns."
		
		order by replace(r.form_name,'\"','&quot;'), cgpn nulls first";		
		
		//echo "<textarea>$sql</textarea>";
		
		$q=OCIParse($c,$sql);
		OCIExecute($q,OCI_DEFAULT);
		
		$tmp_form_id2='';
		$forms_arr=array();
		$cdn_from_id=array();
		$cdn_cdn=array();		
		$cdn_coment=array();
		$cdn_uniq=array();
		$x=0; while(OCIFetch($q)) {$x++;
			
			if(OCIResult($q,"CGPN")=='') {$tmp_cdn='null'; $tmp_phone_name='нет номера';}
			else {$tmp_cdn=OCIResult($q,"CGPN"); $tmp_phone_name=OCIResult($q,"PHONE_NAME");}
			
			if(OCIResult($q,"FORM_ID")=='') {$tmp_form_id='null'; $tmp_form_name='нет отчета';}
			else {$tmp_form_id=OCIResult($q,"FORM_ID"); $tmp_form_name=OCIResult($q,"FORM_NAME");}
						
			//if(OCIResult($q,"CGPN")=='') $cdn_uniq['null']='нет номера'; 
			//else
			
			if($tmp_cdn=='null') $cdn_uniq['null']='нет номера';
			elseif(!isset($cdn_uniq[$tmp_cdn])) $cdn_uniq[$tmp_cdn]=$tmp_cdn;
			
			if($tmp_form_id2<>$tmp_form_id) {
				$forms_arr[$tmp_form_id]=$tmp_form_name;
				$tmp_form_id2=$tmp_form_id;
			}
			
			//if(OCIResult($q,"CGPN")=='') {
			//	$cdn_arr[OCIResult($q,"FORM_ID")][]='null';
			//	$cdn_name[OCIResult($q,"FORM_ID")][]='нет номера';
			//}
			//else {
				$cdn_arr[$tmp_form_id][]=$tmp_cdn;
				$cdn_name[$tmp_form_id][]=$tmp_cdn;
			//}
		}
		asort($cdn_uniq);
		if(count($forms_arr)==0) {
			echo "<tr><td><font color=red>Нет отчетов за выбранный период.</b></font></td></tr>";
			exit();
		}
		echo "<tr><td id=td_rep_type style='color:red'><b>Выберите тип отчета:</b><br>";
		
		echo "<select name=form_id_name onchange=ch_rep_type(this.value)>";
		//if(count($forms_arr)>1 and $_SESSION['project']['id']>'0') {
		//if($_SESSION['project']['id']>'0') { 
			echo "<option value=all_all>Все отчеты</option>";
			$all_rep='y';			
		//}
		foreach($forms_arr as $frm_id => $frm_name) {
			echo "<option value=\"".$frm_id."_".$frm_name."\">".$frm_name."</option>";
		}
		echo "</select>";
		echo "</tr></td>";		
		
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr><td><b>Выберите номер доступа:</b><br>";
	echo "<select id=sel_cdn name=cgpn></select>";
	//echo "<div id=div_cdn name=div_cdn></div>";
	echo "<script>";
	echo "var dn_arr = new Array(); ";
	$i=0;
	if(isset($all_rep)) {
		//echo "<option value='all_all'>Все номера</option>";
		echo "dn_arr[$i]='all_all_Все номера';"; $i++; 

		if(isset($cdn_uniq['null'])) {
			echo "dn_arr[$i]='all_null_".$cdn_uniq['null']."';"; $i++;
		} 
		foreach($cdn_uniq as $key=>$cdn) {
			if($key<>'null') {echo "dn_arr[$i]='all_".$cdn."_".$cdn."';"; $i++;}
		} 
	}
	foreach ($cdn_arr as $frm_id => $cdn_tmp_arr) {
		//if(count($cdn_tmp_arr)>1) {
			//echo "<option value='".$frm_id."_all'>Все номера</option>";
			echo "dn_arr[$i]='".$frm_id."_all_Все номера';"; $i++; 
		//}
		foreach($cdn_tmp_arr as $key => $cdn) {
			//if($cdn=='') {$tmp_cdn='null'; $tmp_cdn2='нет номера';} else {$tmp_cdn=$cdn; $tmp_cdn2=$cdn;}
			//echo "<option value='".$frm_id."_".$tmp_cdn."'>".$tmp_cdn2."</option>";
			echo "dn_arr[$i]='".$frm_id."_".$cdn."_".$cdn."';"; $i++;
			//echo "dn_arr[$i]='all_".$tmp_cdn."_".$tmp_cdn2."';"; $i++;
		}
	}
	echo "ch_rep_type(document.all.form_id_name.value);";	
	echo "</script>";
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
//echo "<script>ch_rep_type();</script>";
}//выбор формы и номера

?>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>