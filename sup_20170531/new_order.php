<?php
extract($_REQUEST);
if (isset($sid)) {session_id($sid); session_start();}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>����� ������ �� ������������</title>
</head>
<body>
<script>
function fFindLoc() {
	with(document.all) {
		if(find_loc.value.length>=3) {
			//alert(find_loc.value);	
			location_id.options[0].selected=true;
			var pat = new RegExp("^.*"+find_loc.value.toUpperCase()+".*$");
			for(i=0; i<location_id.length; i++) {
				//alert(location_id.options[i].innerText);
				if(pat.test(location_id.options[i].innerText.toUpperCase())) {
					location_id.options[i].selected=true; break;
				}
			}
		}
		else {
			location_id.options[0].selected=true;
		}
	}
}
</script>
<?php
set_error_handler ("my_error_handler");
$err_count=0;

if (!isset($cdpn)) $cdpn='';
if (!isset($cgpn)) $cgpn='';
if (!isset($agid)) $agid='';
if (!isset($location_id)) $location_id='';
if (!isset($trbl_ids)) $trbl_ids=array();
if (!isset($location_names)) $location_names=array();
if (!isset($kto)) $kto='';
if (!isset($kto_id)) $kto_id='';
if (!isset($reply_to_email)) $reply_to_email='';
if (!isset($reply_to_name)) $reply_to_name='';
if (!isset($u_kogo)) $u_kogo='';
if (!isset($oper_coment)) $oper_coment='';

include("../../sup_conf/sup_conn_string");
include("func_send.php");
include("../../sup_conf/send_sms.php");

//�������������� ������������
if(isset($sid)) {
	if(isset($_SESSION['create_new']) and $_SESSION['create_new']=='y') {
		$lt_grp_id=$_SESSION['lt_grp_id'];
		$kto_id=$_SESSION['user_id'];
		$q=OCIParse($c,"select fio from sup_user where id='".$kto_id."'");
		OCIExecute($q,OCI_DEFAULT); OCIFetch($q);
		$kto=OCIResult($q,"FIO");
		$q=OCIParse($c,"select email from sup_texnari_emails where texnari_id='".$kto_id."'");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; $eml=array(); while(OCIFetch($q)) {$i++; $eml[$i]=OCIResult($q,"EMAIL");}
		if($i>0) $reply_to_email=implode(',',$eml);
		$reply_to_name=$_SESSION['fio'];
	}
	else {
		echo "<font color=red><b>������: � ��� ��� ���� ��� �������� ������ ��� �� �� �������������� � �������. ������������� � ������� ���� ���������.</b></font>";
		exit();
	}
}
//
//�� �������������� ������������ (��������)
else {
	if(
		substr($_SERVER['REMOTE_ADDR'],0,8)<>'192.168.'
	) {
		echo "<font color=red><b>������: �� �������������� ������ �������� ������ �� ��������� ����</b></font>";
		exit();
	}
	
	if(isset($lt_oper_grp) and $lt_oper_grp<>'') {
		$lt_grp_id=$lt_oper_grp;
	}
	else {
		echo "<font color=red><b>������: �� ������� ������</b></font>";
		exit();
	}
}
//

if (isset($send)) {
	
	if(!isset($trbl_ids) or count($trbl_ids)=='0') {echo "<font color=red><b>������: �� ������ ��� ��������</b></font><br>"; $err='';}
	if(!isset($kto) or $kto=='') {echo "<font color=red><b>������: �� ��������� ���� \"��� ������\"</b></font><br>"; $err='';}
	if(!isset($u_kogo) or $u_kogo=='') {echo "<font color=red><b>������: �� ��������� ���� \"� ����\"</b></font><br>"; $err='';}
	if(!isset($oper_coment) or $oper_coment=='') {echo "<font color=red><b>������: �� ��������� ���� \"�����������\"</b></font><br>"; $err='';}
	echo "<hr>";
	if(!isset($err)) {
		$trbls=implode(',',$trbl_ids);

		$q=OCIParse($c,"select distinct trbl_grp_id from sup_trbl_type
		where id in ($trbls)");
		OCIExecute($q,OCI_DEFAULT);
		$i=1;	while(OCIFetch($q)) {
			zayavka(OCIResult($q,"TRBL_GRP_ID"),$i);
			$i++;
			if ($err_count==0) echo "<font color=green>������ �<b>$num_zayavki</b> ����������!</font><hr>";
		}
		$num_zayavki=''; $location_id=''; $trbl_ids=array(); $kto=''; $u_kogo=''; $oper_coment='';
		if ($err_count==0) {echo "<script>alert('����������!');
		window.opener.location.reload();
		//parent.document.location.reload;
		self.close();
		</script>";
		exit();
		}
	}
}


echo "<form name=frm method=post>";

echo "<input type=hidden name=cdpn value='$cdpn'>";
echo "<input type=hidden name=cgpn value='$cgpn'>";
echo "<input type=hidden name=agid value='$agid'>";

//����� �������
$location_ids=array(); $location_names=array();
if($location_id<>'') {
	$q=OCIParse($c,"select sk.id,sk.name from sup_klinika sk
	where sk.id='".$location_id."' and sk.deleted is null
	order by sk.name");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$location_name=OCIResult($q,"NAME");
	$location_ids[1]=$location_id;
	$location_names[1]=$location_name;
}
else {
	$q=OCIParse($c,"select distinct sk.id,sk.name from sup_lt slt, sup_klinika sk
	where slt.lt_grp_id=decode('".$lt_grp_id."',0,slt.lt_grp_id,'".$lt_grp_id."') and sk.id=slt.location_id
	and sk.deleted is null
	order by sk.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$location_ids[$i]=OCIResult($q,"ID");
		$location_names[$i]=OCIResult($q,"NAME");
	}
}
if(count($location_ids)==1) {
	$location_id=$location_ids[1];
	$location_name=$location_names[1];
	echo "<font size=4>".$location_name.". <font color=green>����� ������</font></font><br>";
	echo "<font color=red>��������! �� ������ ����� ������� ���������� ������ ������.<br>
	��������, ���� �� ������� �������� � 1�, ������ ���� 2 ������ ������.</font>"; 
	echo "<input type=hidden name=location_id value='".$location_id."'>";
}
elseif(count($location_ids)>1) {
	echo "�����: <input type=text name=find_loc value='' onkeyup=fFindLoc()><br>";
	echo "<select name=location_id onchange='frm.ok.click()'>";
	echo "<option value='' style='color:red'>�������� �����</option>";
	foreach($location_ids as $i => $id) {
		echo "<option value='".$id."'";
		if($id==$location_id) {
			echo " selected";
		}
	echo ">".$location_names[$i]."</option>";
	}
	echo "</select>";
	echo "<input type=submit name='ok' value='ok'>";
	/*echo "<script>frm.ok.style.display='none';</script>";*/
	exit();
}
//����� �������
echo "<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'>
<tr>";
echo "<td bgcolor=white valign=top>";
if(!isset($num_zayavki) or $num_zayavki=='') {
	$q=OCIParse($c,"select sup_base_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$num_zayavki=OCIResult($q,"NEXTVAL");
}
echo "� ������: <b>$num_zayavki</b>";
echo "<input type=hidden name=num_zayavki value='$num_zayavki'>";
echo "<hr>";

if(!isset($_SESSION['user_id'])) echo "<b><font color=red size=4>*</font>��� (��� ������/���������):</b><br><input type=text name=kto style='width:97%' value='".$kto."' onkeyup='check()'></input>";
else echo "<b>".$kto." <input type=hidden name=kto value='".$kto."'></b>";
echo "<hr>";	
echo "<b><font color=red size=4>*</font>���, ����� �����/����������<br>(� ���� �� ��������):</b><br> <input type=text size=30 name=u_kogo style='width:97%' value='".$u_kogo."' onkeyup='check()'></input><br>";
echo "</td>";

echo "<td bgcolor=white valign=top><b><font color=red size=4>*</font>��� ��������: </b><br>";
$q=OCIParse($c,"select distinct stt.id,stt.name from sup_lt slt, sup_trbl_type stt
where slt.lt_grp_id=decode('".$lt_grp_id."',0,slt.lt_grp_id,'".$lt_grp_id."') and slt.location_id='".$location_id."' and stt.id=slt.trbl_id
and stt.deleted is null
and slt.lt_grp_id<>'0'
order by stt.name");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	//echo "<input type=checkbox name=trbl_ids[] value='".OCIResult($q,"ID")."'";
	echo "<input type=radio name=trbl_ids[] value='".OCIResult($q,"ID")."'";
		foreach($trbl_ids as $id) {
			if($id==OCIResult($q,"ID")) echo " checked";
		}
	echo ">".OCIResult($q,"NAME")."</input><br>";
}
echo "</b></td>
</tr>";
echo "<tr><td bgcolor=white valign=top colspan=2>";	
echo "<b><font color=red size=4>*</font>�������� ��������:</b><br>
<textarea rows=7 name=oper_coment style='width:98%' onkeyup='check()'>".$oper_coment."</textarea><br>";
echo "</td></tr></table>";

echo "<table style='max-width:550px'><tr><td align=left>";
echo "<input type=submit name=send value='���������'>";
echo "</td>";
echo "<td align=right width='100%'>";
echo "<b><font color=red size=4>*</font></b> - ������������ ����";
echo "</td></tr></table>";
echo "</form>";
echo "<iframe name=oper_ifr style='display:none'></iframe>";
echo "
<script>
check();
function check() {
//	if (frm.location_id.value=='' || frm.oper_coment.value=='') frm.send.disabled=true;
//	else frm.send.disabled=false;
}
</script>
";


function zayavka($trbl_grp_id,$num_copy) {
global $c;
global $num_zayavki;
global $cdpn;
global $cgpn;
global $agid;
global $oper_coment;
global $location_id;
global $kto_id;
global $kto;
global $reply_to_email;
global $reply_to_name;
global $u_kogo;
global $trbl_ids;
global $trbls;
global $err_count;

if($num_copy>1) {
	$q=OCIParse($c,"select sup_base_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$num_zayavki=OCIResult($q,"NEXTVAL");
}

$q=OCIParse($c,"select id from sup_base where id='$num_zayavki'");
OCIExecute($q,OCI_DEFAULT);
if(OCIFetch($q)) {echo "<font color=green>������ �<b>$num_zayavki</b>��� ����������!</font><hr>"; $err_count++; return;}
$ins=OCIParse($c,"insert into sup_base (id,date_in_call,cdpn,cgpn,agid,oper_comment,klinika_id,kto_id,kto,u_kogo,trbl_grp_id,ip_address)
values ('$num_zayavki',sysdate,'$cdpn','$cgpn','$agid',:oper_coment,'$location_id','$kto_id','$kto','$u_kogo','$trbl_grp_id','".$_SERVER['REMOTE_ADDR']."')");
OCIBindByName($ins,":oper_coment",$oper_coment);
OCIExecute($ins,OCI_DEFAULT);

//��� ��������
foreach($trbl_ids as $value) {
	$ins=OCIParse($c,"insert into sup_trbl_alloc (base_id,trbl_type_id)
values ('$num_zayavki','$value')");
	OCIExecute($ins,OCI_DEFAULT);
}
//

OCICommit($c);

//���������� ��������
$cont_phones=array();
if($kto_id<>'') {
	$q_tmp=OCIParse($c,"select phone from SUP_TEXNARI_PHONES t where texnari_id='".$_SESSION['user_id']."' and contact='y' order by ord");
	OCIExecute($q_tmp,OCI_DEFAULT);
	$i=0; while (OCIFetch($q_tmp)) {$i++;
		$cont_phones[$i]=OCIResult($q_tmp,"PHONE");
	}
}

$q=OCIParse($c,"select name,phone from sup_klinika where id='".$location_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$klinika_name=OCIResult($q,"NAME");
$klinika_phone=OCIResult($q,"PHONE");

//��������� ������
$server='';
if($kto_id<>'') $from_name=$kto; else $from_name='������������';
$from_email='support@wilstream.ru';
$subj='������ �'.$num_zayavki.' - '.$klinika_name;

$mess="<font size=4><nobr>".$klinika_name.($klinika_phone<>''?' ('.$klinika_phone.')':'').".</nobr><font color=green> ����� ������</font></font>";
$mess.="<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'><tr>";
$mess.="<td bgcolor=white valign=top>� ������: <b>".$num_zayavki."</b><hr>";
if($cdpn<>'') $mess.="���: <b>".$cdpn."</b><br>";
$mess.="IP: <b>".$_SERVER['REMOTE_ADDR']."</b><hr>";
$mess.="��� ���������:<br><b>".$kto."</b><br>";
if($kto_id<>'') {
	foreach ($cont_phones as $phone) {
		$mess.=$phone."<br>";
	}
	$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".$kto_id."'");
	OCIExecute($q_tmp,OCI_DEFAULT);
	//$mailtos=array();
	$i=0; while(OCIFetch($q_tmp)) {$i++;
		$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
	}
	if(isset($mailtos)) {
		$mailtos=implode(',',$mailtos);
		$mess.="<a href='mailto:".$mailtos."?subject=������ �".$num_zayavki." - �����'>".$mailtos."</a><br>";
	}
	
}
$mess.="<hr>
� ���� �� ��������:<br><b>".$u_kogo."</b><br></td>
<td bgcolor=white valign=top>��� ��������: <br>";
$q=OCIParse($c,"select t.name from sup_trbl_alloc a, sup_trbl_type t
where a.trbl_type_id=t.id and a.base_id='".$num_zayavki."'");
OCIExecute($q,OCI_DEFAULT);
$m=0; while (OCIFetch($q)) {$m++;
	$trbl_names[$m]=OCIResult($q,"NAME");
}
if(isset($trbl_names)) {
	foreach($trbl_names as $val) {
		$mess.="<b>".$val."<br>";
	}
}
$mess.="</b></td></tr>
<tr><td bgcolor=white valign=top colspan=2>���� ��������:<br><b>".nl2br($oper_coment)."</b></td></tr>
</table>";
$mess.="<a href='http://gw.wilstream.ru/sup/tex.php?ticketId=".$num_zayavki."' target=_balnk>http://gw.wilstream.ru/sup/tex.php</a>";
//

/*$mess="
<b>������:</b> ".$klinika_name." </b><br>
<b>��� ������:</b> ".$kto." <b>���:</b> ".$cdpn."<br>
<b>��� ��������:</b><br>";
$q=OCIParse($c,"select t.name from sup_trbl_alloc a, sup_trbl_type t
where a.trbl_type_id=t.id and a.base_id='$num_zayavki'");
OCIExecute($q,OCI_DEFAULT);
	$m=0; while (OCIFetch($q)) {$m++;
		$trbl_names[$m]=OCIResult($q,"NAME");
	}
	if(isset($trbl_names)) {
		foreach($trbl_names as $val) {
			$mess.=$val."<br>";
		}
	}	
	$mess.="<b>� ���� �� ��������:</b>".$u_kogo."<br>";
	$mess.="<b>�������� ��������:</b><br>
	".nl2br($oper_coment);
	$mess.="<br><br>--<br><br><a href='http://gw.wilstream.ru/sup/tex.php' target=_balnk>http://gw.wilstream.ru/sup/tex.php</a>";
*/
	//echo "trbls".implode(',',$trbl_ids)." -- location".$location_id;

	//��� ������ ����, ��� ������������ �� ������ �������
	/*$q=OCIParse($c,"select distinct ste.email from sup_trbl_type stt, sup_lt slt, sup_user su, sup_texnari_emails ste
	where stt.trbl_grp_id='".$trbl_grp_id."' and slt.location_id='".$location_id."' 
	and slt.trbl_id=stt.id 
	and decode(su.lt_grp_id,0,slt.lt_grp_id,su.lt_grp_id)=slt.lt_grp_id and su.send='y' and su.deleted is null 
	and ste.texnari_id=su.id");*/

	//��� ������ ���, ��� ����� ���������� ���� ���������� ��������� � ���������� �����, ����� ���� ))
	$q=OCIParse($c,"select distinct ste.email from sup_trbl_type stt, sup_lt slt, sup_user su, sup_texnari_emails ste
	where stt.id in (".implode(',',$trbl_ids).") and slt.location_id='".$location_id."' 
	and slt.trbl_id=stt.id 
	and decode(su.lt_grp_id,0,slt.lt_grp_id,su.lt_grp_id)=slt.lt_grp_id 
	and su.send='y' and (su.solution='y' or su.redirect='y' or su.eval='y') 
	and su.id<>nvl('".$kto_id."','0') 
	and su.deleted is null
	and ste.texnari_id=su.id
	");
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		$to_name='';
		$to_email=OCIResult($q,"EMAIL");
		
		//������ ��� �������� �� �������!
		if($to_email=='it-itil@yandex.ru') {
		$mess2="
		<b>������:</b> ".$klinika_name." </b><br>
		<b>��� ������:</b> ".$kto." <b>���:</b> ".$cdpn."<br>
		<b>��� ��������:</b><br>";
		if(isset($trbl_names)) {
			foreach($trbl_names as $val) {
				$mess2.=$val."<br>";
			}
		}	
		$mess2.="<b>� ���� �� ��������:</b>".$u_kogo."<br>";
		$mess2.="<b>�������� ��������:</b><br>
		".nl2br($oper_coment);
		send($server, '', $to_email, '', 'support@wilstream.ru', '', '' ,$subj, $mess2);
		}
		//
		
		else {
			send($server, $to_name, $to_email, $from_name, $from_email, $reply_to_name, $reply_to_email ,$subj, $mess);
		}
	}

	//���� ���-�����������
	$q=OCIParse($c,"select distinct stp.phone from sup_trbl_type stt, sup_lt slt, sup_user su, sup_texnari_phones stp
	where stt.id in (".implode(',',$trbl_ids).") and slt.location_id='".$location_id."' 
	and slt.trbl_id=stt.id 
	and decode(su.lt_grp_id,0,slt.lt_grp_id,su.lt_grp_id)=slt.lt_grp_id 
	and su.sms_new='y' and (su.solution='y' or su.redirect='y') 
	and su.id<>nvl('".$kto_id."','0') 
	and su.deleted is null
	and stp.sms='y'
	and stp.texnari_id=su.id");

	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$sms_phones[$i]=OCIResult($q,"PHONE");
	}
	if(isset($sms_phones)) {
		$from_phone='Wilstream';
		$Phone_list=implode(',',$sms_phones);
		$sms_text=$num_zayavki."-����� ������.".chr(10);
		$sms_text.=$klinika_name.".".chr(10);
		if($kto_id=='' and $klinika_phone<>'') $sms_text.=$klinika_phone.chr(10);
		if(isset($trbl_names)) {
			$n=0; foreach($trbl_names as $val) {$n++;
				if($n>3) {$sms_text.="...".chr(10); break;}		
				$sms_text.=$val.";".chr(10);
			}
		}
		if(trim($oper_coment)<>'') {
			if(count(trim($oper_coment))>30) $sms_text.=substr(trim($oper_coment),0,27).'...'.chr(10);
			else $sms_text.=substr(trim($oper_coment),0,30).';'.chr(10);
		}
		$sms_text.=$kto.chr(10);
		if($kto_id<>'') {
			foreach ($cont_phones as $phone) {
				$sms_text.=$phone.chr(10);
			}
		}	
		send_sms($num_zayavki,$from_phone,$Phone_list,$sms_text);
	}
    //
}
//������� ��������� ������
function my_error_handler($code, $msg, $file, $line) {

global $err_count;
global $c;
$err_count++;
OCIRollback($c);
echo "<font color=red>������! $code - ".(str_replace('\'',' ',$msg))." - ".(str_replace('\'',' ',$file))." - ".(str_replace('\'',' ',$line))."'</font><hr>";
}
?>
</body>
</html>
