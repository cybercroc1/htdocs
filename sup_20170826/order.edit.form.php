<?php
extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>������ �� ������������</title>
</head>
<body>
<?php
//echo session_name()."--".session_id();

$tomorrow=date('d.m.Y',mktime(0,0,0,date("m"),date("d")+1,date("Y")));

if (isset($_SESSION['auth']) and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or $_SESSION['eval']<>'create_new')) {
}
else {
echo "<font color=red><b>������: � ��� ��� ���� ��� ��������� ������ �������� ��� �� �� ������ �����������</b></font>";
exit();
}

include("../../sup_conf/sup_conn_string");

if (isset($base_id)) {
/*	//�������� ������ ����� ������������
	$q=OCIParse($c,"select lt_group_id from SUP_USER_LT_ALLOC where user_id=".$_SESSION['user_id']);
	OCIExecute($q,OCI_DEFAULT);
	$i=0; $lt_grp_id=array(); while(OCIFetch($q)) {$i++; $lt_grp_id[$i]=OCIResult($q,"LT_GROUP_ID");}
	$lt_grp_ids=implode(',',$lt_grp_id);*/

	if(!isset($callback_fio)) $callback_fio='';
	
	//���������� � ������
	$q=OCIParse($c,"select b.id,
	       to_char(b.date_in_call, 'DD.MM.YYYY HH24:MI') date_in_call,
	       b.cdpn,
	       b.klinika_id,
		   b.texnari_id,
	       k.name,
		   k.phone,
	       b.kto,
		   b.kto_id,
	       b.oper_comment,
	       b.u_kogo,
		   b.quality,
		   b.quality_coment,
		   b.quality_who,
		   b.ip_address,
		   b.ready_to_close,
		   b.date_close,
		   b.trbl_type_id,
		   b.trbl_detail_id,
		   tt.name trbl_type_name,
           td.name trbl_detail_name,
		   case  
			 when b.quality='1' then 'red'
			 when b.quality='2' then 'red'
			 when b.quality='3' then '#CC6633'
			 when b.quality='4' then '#339966'
			 when b.quality='5' then 'green'
	       end q_color,
		   b.dublikat,
		   b.krivie_ruki	   
	  from sup_base b, sup_klinika k, sup_trbl_type tt, sup_trbl_detail td
	 where b.klinika_id=k.id (+)
     and tt.id(+)=b.trbl_type_id
     and td.id(+)=b.trbl_detail_id	 
	 and b.id = '".$base_id."'");
	
	OCIExecute($q,OCI_DEFAULT);
	if(!OCIFetch($q)) {echo "<font color=red><b>������: ����� ������ �� ����������</b></font>"; exit();}
	$from_user_id=OCIResult($q,"TEXNARI_ID");
	$old_location_id=OCIResult($q,"KLINIKA_ID");
	$old_location_name=OCIResult($q,"NAME");
	$old_trbl_type_id=OCIResult($q,"TRBL_TYPE_ID");
	$old_trbl_det_id=OCIResult($q,"TRBL_DETAIL_ID");
	$kto_id=OCIResult($q,"KTO_ID");
	$date_close=OCIResult($q,"DATE_CLOSE");
	$ready_to_close=OCIResult($q,"READY_TO_CLOSE");
	$date_in_call=OCIResult($q,"DATE_IN_CALL");
	$dublikat=OCIResult($q,"DUBLIKAT");
	$krivie_ruki=OCIResult($q,"KRIVIE_RUKI");	

	echo "<form name=tex_edit_frm method=post action=order.edit.save.php target=logFrame>";
	echo "<input type=hidden name=document_location value='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."'>";
	echo "<input type=hidden name=from_user_id value='".$from_user_id."'>";
	echo "<input type=hidden name=kto_id value='".$kto_id."'>";

	echo "<font size=4>������ � ".$base_id.". ".$old_location_name.(OCIResult($q,"PHONE")<>''?' ('.OCIResult($q,"PHONE").')':'');
	echo ($dublikat?"<font color=red> (��������) </font>":"");
	echo ($krivie_ruki?"<font color=red> (������ ������������) </font>":"");
	echo "</font>";
	
	echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>
	<tr><td bgcolor=white valign=top>
	<nobr>� ������: <b>".$base_id."</b></nobr><nobr> ����: <b>".$date_in_call."</b></nobr> ";
	if(OCIResult($q,"CDPN")<>'') echo "<nobr>���: <b>".OCIResult($q,"CDPN")."</b></nobr> ";
	echo "<nobr>IP: <b>".OCIResult($q,"IP_ADDRESS")."</b></nobr><hr>";
	
	//��������������
	echo "<div id=div_location_id>��������������: <b>".$old_location_name."</b></div>
	<hr>";
	
		
	echo "��� ���������: <nobr><b>".OCIResult($q,"KTO")."</b>;</nobr> ";
	if(OCIResult($q,"KTO_ID")<>'') {
		$q_tmp=OCIParse($c,"select phone from SUP_TEXNARI_PHONES t where texnari_id='".$kto_id."' and contact='y' and valid_date is not null order by ord");
		OCIExecute($q_tmp,OCI_DEFAULT);
		$i=0; while (OCIFetch($q_tmp)) {$i++;
			$phones[$i]=OCIResult($q_tmp,"PHONE");
		}
		if(isset($phones)) {
			echo "<nobr>";
			$phones=implode(', ',$phones);
			echo $phones;
			echo ";</nobr> ";
		}

		$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".$kto_id."' and valid_date is not null");
		OCIExecute($q_tmp,OCI_DEFAULT);
		//$mailtos=array();
		$i=0; while(OCIFetch($q_tmp)) {$i++;
			$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
		}
		if(isset($mailtos)) {
			echo "<nobr>";
			$mailtos=implode(', ',$mailtos);
			echo "<a href='mailto:".$mailtos."?subject=������ �".$base_id." - �����'>".$mailtos."</a>";
			echo ";</nobr> ";
		}
	}
	echo "<hr>
	� ���� �� ��������: <b>".OCIResult($q,"U_KOGO")."</b><hr>";
	
	//��� ��������
	echo "<div id=div_trbl_type>��� ��������: <b>".OCIResult($q,"TRBL_TYPE_NAME")."</b></div>";
	
	//������ ��������
	echo "<div id=div_trbl_detail>".(OCIResult($q,"TRBL_DETAIL_NAME")<>''?'������: '.OCIResult($q,"TRBL_DETAIL_NAME"):'')."</b></div>";

	echo "</b></td>
	</tr>
	<tr>
	<td bgcolor=white valign=top>���� ��������: <b>".nl2br(OCIResult($q,"OPER_COMMENT"))."</b></td>
	</tr>
	</table>";

	echo "<input type=hidden name=base_id value=".$base_id.">";
	echo "<input type=hidden name=old_location_id value=".$old_location_id.">";
	echo "<input type=hidden name=old_location_name value='".$old_location_name."'>";
	echo "<input type=hidden name=old_trbl_type_id value=".$old_trbl_type_id.">";
	echo "<input type=hidden name=old_trbl_det_id value='".$old_trbl_det_id."'>";	
	$quality=OCIResult($q,"QUALITY");
	$quality_who=OCIResult($q,"QUALITY_WHO");
	$quality_coment=OCIResult($q,"QUALITY_COMENT");
	//$location_id=OCIResult($q,"KLINIKA_ID");
	$q_color=OCIResult($q,"Q_COLOR");

	if($quality<>'') {
		echo "������:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>������: <b>$quality_who:</b> <font color='$q_color'><b>$quality</b></font><br>$quality_coment</td></tr>";
		echo "</table>";
	}

	//������ �������
	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
		echo "������ ������� �� ��������:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>
		<div id=div_callback_who>
		</div>
		</td></tr>";
		echo "<tr><td bgcolor=white><b>���� ����������</b>: <input style='width:98%' type=text name=callback_fio value='".$callback_fio."' onkeyup=fn_check()></td>";
		echo "</table>";
	}

	//�������
	$q3=OCIParse($c,"select to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who,
	decode(sth.result_call,0,'������������� �� ������',1,'������� �� ��������',2,'�� ����������',3,'������',4,'���������� ',5,'�������������',6,'�����������',7,'������',8,'��������',9,'������ � ��������',10, '������ \"��������\"', 11, '������ \"������ ������������\"',12,'������ ��������������',13,'������ ��� ��������',14,'������� �� ',null) result, 
	decode(sth.result_call,0,'maroon',1,'green',2,'blue',3,'red',4,'blue',5,'maroon',6,'indigo',7,'black',8,'green',9,'#006400',10,'red',11,'red',12,'black',13,'black',14,'#CC6633',null) color, sth.quality 
	from sup_texnari_history sth, sup_user su
	where sth.base_id='".$base_id."'
	and su.id(+)=sth.texnari_id
	order by sth.datetime, sth.id");
	OCIExecute($q3,OCI_DEFAULT);
	
	$i=0;
	while (OCIFetch($q3)) {
		$i++; if($i==1) {
			echo "�������:";
			echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		}
		echo "<tr><td bgcolor=white valign=top>";
		echo "<b>".OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"COLOR")."'>".OCIResult($q3,"RESULT")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT")=='������') {
			if(OCIResult($q3,"QUALITY")=='1') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') echo ": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') echo ": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') echo ": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT")<>'���������� ') echo "</b><br>";

		echo nl2br(OCIResult($q3,"TEXNARY_COMENT"));
		echo "</td></tr>";
	}
	if($i>0) echo "</table>";
	//

	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y' or $_SESSION['create_new']=='y') {
		echo "�����������: <font color=red>(������������ ����)</font><br><textarea onkeyup=fn_check() style='width:98%' rows=5 name=tex_comment></textarea><hr>";
	}

	if($_SESSION['eval']=='y') {
		echo "<font size=3><b>������: </b></font><select name=quality onchange=fn_check()><option></option>
		<option style='color:red' value='1'>1</option>
		<option style='color:red' value='2'>2</option>
		<option style='color:#CC6633' value='3'>3</option>
		<option style='color:#339966' value='4'>4</option>
		<option style='color:green' value='5'>5</option>
		</select><hr>";
	}

	if($_SESSION['solution']=='y') {
		echo "<input type=hidden name='dublikat'><input type='checkbox' name='dublikat' value='y'"; echo ($dublikat?" checked":""); echo "><font color=red>��������</font></input>
		 | <input type=hidden name='krivie_ruki'><input type='checkbox' name='krivie_ruki' value='y'"; echo ($krivie_ruki?" checked":""); echo"><font color=red>������ ������������</font></input><hr>";
	}

	//������ �������� ��� �������������
	echo "<div id=div_to_user_id></div>";
	//
	echo "<hr>";
	
	if($_SESSION['solution']=='y' and $date_close=='') {
		if($_SESSION['deny_close']=='y' and $ready_to_close=='') {echo "<input type=submit disabled name=close_z style='background-color:#458B00' value='������ � ��������'> | ";}
		else if($_SESSION['deny_close']<>'y' and $date_close=='') {echo "<input type=submit disabled name=close_z style='background-color:brown' value='������� ������'> | ";}
	}
	if(($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $kto_id==$_SESSION['user_id']) and $date_close=='') {
		
		echo "<nobr><input type=text value='".$tomorrow."' size=8 name=delay_to_date style='background-color:#CC6633' onclick='if(self.gfPop)gfPop.fPopCalendar(this);return false; HIDEFOCUS' onchange=ok.click()>";
		echo "<input type=submit disabled name=delay_z style='background-color:#CC6633' value='��������'> | </nobr>";
	}
	
	echo "</form>";
}
echo "<iframe style='display:none' name='logFrame' src='order.edit.func.php?base_id=".$base_id."&from_user_id=".$from_user_id."&old_location_id=".$old_location_id."&new_location_id=".$old_location_id."&old_trbl_type_id=".$old_trbl_type_id."&new_trbl_type_id=".$old_trbl_type_id."&old_trbl_det_id=".$old_trbl_det_id."&new_trbl_det_id=".$old_trbl_det_id."'></iframe>";
echo "<script>
var base_id='".$base_id."';
var from_user_id='".$from_user_id."';
var old_location_id='".$old_location_id."';
var old_trbl_type_id='".$old_trbl_type_id."';
var old_trbl_det_id='".$old_trbl_det_id."';
</script>";
?>
<script>
function fn_check() {
	with(tex_edit_frm) {
		if('delay_z' in tex_edit_frm && tex_comment.value!='') delay_z.disabled=false; else delay_z.disabled=true;
	
		if ('new_trbl_type_id' in tex_edit_frm && new_trbl_type_id.value=='') {
			if('close_z' in tex_edit_frm) close_z.disabled=true;
			if('save' in tex_edit_frm) save.disabled=true;			
			alert('��������! �� ������ ��� ��������!');
		}
		else if(
		('callback_fio' in tex_edit_frm && callback_fio.value!='' && callback_who.value!='') //������
	||	('quality' in tex_edit_frm && tex_comment.value!='' && quality.value!='') //������
	||	('to_user_id' in tex_edit_frm && tex_comment.value!='') //���������������
	||	((!('callback_fio' in tex_edit_frm) || (callback_fio.value=='' && callback_who.value=='')) && !('quality' in tex_edit_frm) && tex_comment.value!='') //��������, �����������, ������
	||  (!('callback_fio' in tex_edit_frm) && 'quality' in tex_edit_frm && ((quality.value!='' && tex_comment.value!='')||tex_comment.value!=''))	
		)
		{
			if('close_z' in tex_edit_frm) {
				if('to_user_id' in tex_edit_frm && to_user_id.value=='group') close_z.disabled=true; 
				else if(tex_comment.value!='') close_z.disabled=false; else close_z.disabled=true;
			}
			if('save' in tex_edit_frm) save.disabled=false;
		}
		else {
			if('close_z' in tex_edit_frm) close_z.disabled=true;
			if('save' in tex_edit_frm) save.disabled=true;
		}
	
	}	
}
function ch_loc_trbl() {
	var new_location_id=tex_edit_frm.new_location_id.value;
	var new_trbl_type_id=tex_edit_frm.new_trbl_type_id.value;
	if('new_trbl_det_id' in tex_edit_frm) var new_trbl_det_id=tex_edit_frm.new_trbl_det_id.value; else var new_trbl_det_id='';
	logFrame.location='order.edit.func.php?base_id='+base_id+'&from_user_id='+from_user_id+'&old_location_id='+old_location_id+'&new_location_id='+new_location_id+'&old_trbl_type_id='+old_trbl_type_id+'&new_trbl_type_id='+new_trbl_type_id+'&old_trbl_det_id='+old_trbl_det_id+'&new_trbl_det_id='+new_trbl_det_id;
}
</script>
</body>
</html>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_order_edit_delay.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
