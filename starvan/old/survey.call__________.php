<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body class="body_marign" onLoad="parent.callFrameset.rows=document.getElementById('end_page').getBoundingClientRect().top+8+',*,1'">
<?php
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("../../conf/starcall_conf/conn_string.cfg.php");

if(!isset($_SESSION['survey']['call']['status_type'])) $_SESSION['survey']['call']['status_type']='auto';
if(!isset($_SESSION['survey']['call']['quote_id'])) $_SESSION['survey']['call']['quote_id']='auto';


if(// ������� ����� ��� ��� ������� - ������� ����������, ������������� �������
(isset($status_type) and $status_type<>$_SESSION['survey']['call']['status_type']) 
or
(isset($quote_id) and $quote_id<>$_SESSION['survey']['call']['quote_id'])
) {
	if(isset($status_type)) $_SESSION['survey']['call']['status_type']=$status_type;
	if(isset($quote_id)) $_SESSION['survey']['call']['quote_id']=$quote_id;
	$_SESSION['survey']['ank']['base']['id']='';
	$_SESSION['survey']['ank']['phone']['ord']=''; //ord �������� ��������������� �������� � �������� ����� ������
	$_SESSION['survey']['ank']['phone']['num']='';
	$set_status='';
	$perez_date='';
}
if($_SESSION['survey']['ank']['base']['id']=='') {
	//������������� �������
	func_unlock();
}
if(!isset($set_status)) $set_status='';
if(!isset($perez_date)) $perez_date='';



//������===================================================
//��������� ���������� � �������� ������� =================================================
$q=OCIParse($c,"select p.id,p.name, p.from_time, p.to_time, p.nedoz_interval,
p.quote,p.stat_new,p.stat_end_norm,p.stat_inwork,p.stat_nedoz,p.stat_perez, p.quote-p.stat_end_norm estimate,
decode(p.quote,0,'100%',decode(p.quote,NULL,NULL,round(p.stat_end_norm/p.quote*100,0)||'%')) percent,
case when p.quote-p.stat_end_norm<=0 then 'quote_full' else p.status end status, 
p.num_src_fields, p.num_phone_fields, p.perez_policy, p.nedoz_count, p.nedoz_chance
from STC_PROJECTS p
where p.id='".$_SESSION['survey']['project']['id']."'
order by p.name, p.create_date");
OCIExecute($q);

if(OCIFetch($q)) {
	$project['name']=OCIResult($q,"NAME");
	$project['status']=OCIResult($q,"STATUS");
	$project['from_time']=OCIResult($q,"FROM_TIME");
	$project['to_time']=OCIResult($q,"TO_TIME");
	$project['nedoz_interval']=OCIResult($q,"NEDOZ_INTERVAL");
	$project['num_src_fields']=OCIResult($q,"NUM_SRC_FIELDS");
	$project['num_phone_fields']=OCIResult($q,"NUM_PHONE_FIELDS");
	$project['perez_policy']=OCIResult($q,"PEREZ_POLICY");
	$project['nedoz_chance']=OCIResult($q,"NEDOZ_CHANCE");
	$project['max_nedoz_count']=OCIResult($q,"NEDOZ_COUNT");
	echo "<nobr>���������: <b>".OCIResult($q,"STAT_END_NORM")."/".OCIResult($q,"QUOTE")."(".OCIResult($q,"PERCENT").")</b>; �����: <b>".OCIResult($q,"STAT_NEW")."</b>; � ������: <b>".OCIResult($q,"STAT_INWORK")."</b>; ����������: <b>".OCIResult($q,"STAT_PEREZ")."</b>; ����������: <b>".OCIResult($q,"STAT_NEDOZ")."</b>;<br>";
}
if($project['status']=='�������������' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr><font color=orange>������ �������������!</font><hr>";
	echo "<input type=button value='��������' onclick=document.location.reload()>";
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$_SESSION['survey']['project']['id']."' where id=".$_SESSION['user']['id']));
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
	/*
	echo "<script>parent.callBottomFrame.ankMainFrame.document.location='survey.ank.php'</script>";
	echo "<script>parent.callBottomFrame.ankInfoFrame.document.location='survey.aboninfo.php'</script>";	
	*/
	exit();
}
if($project['status']=='quote_full' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr><font color=green>���������� ����� ����� �� �������!</font><hr>"; 
	echo "<input type=button value='��������' onclick=document.location.reload()>";
	//��������� ������� ������ ���������
	//$_SESSION['survey']['project']['id']='';
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='".$_SESSION['survey']['project']['id']."' where id=".$_SESSION['user']['id']));
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
	/*
	echo "<script>parent.callBottomFrame.ankMainFrame.document.location='survey.ank.php'</script>";
	echo "<script>parent.callBottomFrame.ankInfoFrame.document.location='survey.aboninfo.php'</script>";
	*/
	exit();
}
if($project['status']=='������' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr><font color=red>������ ������!</font><hr>"; 
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
	/*
	echo "<script>parent.callBottomFrame.ankMainFrame.document.location='survey.ank.php'</script>";
	echo "<script>parent.callBottomFrame.ankInfoFrame.document.location='survey.aboninfo.php'</script>";
	*/
	exit();
}

//��������� ������� ������ ���������
OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_oper_prj_id='".$_SESSION['survey']['project']['id']."' where id=".$_SESSION['user']['id']));
$_SESSION['refresh_lock_project']='y';
//====================================================================================================================================
//====================================================================================================================================


//�������� ������������� �������
if($_SESSION['survey']['ank']['base']['status']<>'inwork') {
	$q=OCIParse($c,"select base_id from STC_USER_INWORK where user_id=".$_SESSION['user']['id']." and project_id=".$_SESSION['survey']['project']['id']);
	OCIExecute($q, OCI_DEFAULT);
	if(OCIFetch($q)) {
		$tmp_base_id=OCIResult($q,"BASE_ID");
		$upd=OCIParse($c,"update STC_BASE b 
		set b.lock_user=".$_SESSION['user']['id']." , b.lock_date=sysdate
		where id=".$tmp_base_id." and project_id=".$_SESSION['survey']['project']['id']." and b.status='inwork' and status_user=".$_SESSION['user']['id']." 
		and allow='y' and lock_by_index is null and src_quote_id not in (
	    select q.id from STC_SRC_QUOTES q where q.project_id=".$_SESSION['survey']['project']['id']." and q.src_quote-q.STAT_end_norm<=0
		)
		returning id into :base_id");
		OCIBindByName($upd, ":base_id", $base_id,16);
		OCIExecute($upd, OCI_DEFAULT);
		OCICommit($c);
		if($base_id<>'') {
			echo "<hr>";
			echo "<font color=red><b>����� ������� ����������, ��������� ������� �����.</b></font>"; 
			$_SESSION['survey']['ank']['base']['id']=$base_id;
			$_SESSION['survey']['ank']['base']['status']='inwork';
			$_SESSION['survey']['ank']['phone']=func_next_phone($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id'],$project['nedoz_interval'],$project['max_nedoz_count']);
		}	
	}
}
//
if($_SESSION['survey']['ank']['base']['status']=='inwork') {
			echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
			echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
			/*
			echo "<script>parent.callBottomFrame.ankMainFrame.document.location='survey.ank.php'</script>";
			echo "<script>parent.callBottomFrame.ankInfoFrame.document.location='survey.aboninfo.php'</script>";			
			*/
			exit();	
}
//

//���� � ������� ��� �������� �����
if($project['num_src_fields']==0) {
	echo "<hr>";
	func_show_call_buttons($_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone'],0,0);
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	exit();
}
//


echo "<form name=frm_change method=post>";
//����� ���� ====================================================
echo "<hr>";
echo "<nobr><a href='survey.quotes.php' target='callBottomFrame'>�����:</a>";

//������ ����������� �����
echo "<select name=quote_id onchange='frm_change.submit()'><option value=auto>����</option>";

$q=OCIParse($c,"select f.id,f.text_name from STC_FIELDS f
where f.project_id=".$_SESSION['survey']['project']['id']." and f.src_type_id=1 and f.quoted is not null and f.deleted is null 
order by f.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}
//���� ���� ����������� �������� ����
if(isset($quoted_fields)) {
	$q_quotes=OCIParse($c,"select q.id,q.STAT_new,q.STAT_end_norm,q.src_quote,q.STAT_perez,q.STAT_nedoz,q.STAT_inwork,
	q.src_quote-q.STAT_end_norm estimate,
	decode(q.src_quote,0,'100%',decode(q.src_quote,NULL,NULL,round(q.STAT_end_norm/q.src_quote*100,0)||'%')) percent,
	q.lock_by_index 
	from STC_SRC_QUOTES q
	where q.project_id=".$_SESSION['survey']['project']['id']."
	--and q.lock_by_index is null --�� ���������� ��������������� �� �����������
	--and q.src_quote-q.src_norm<=0 --�� ���������� �����������
");
	$q_idx=OCIParse($c,"select i.value from STC_SRC_QUOTE_INDEXES qi, Stc_Src_Indexes i
	where qi.project_id=".$_SESSION['survey']['project']['id']." and qi.quote_id=:quote_id
	and i.project_id=".$_SESSION['survey']['project']['id']." 
	and i.id=qi.index_id
	and i.field_id=:field_id");
	
	OCIExecute($q_quotes);
	while(OCIFetch($q_quotes)) {
		$color=''; $disabled='';
		$quote=OCIResult($q_quotes,"SRC_QUOTE");
		$qid=OCIResult($q_quotes,"ID");
		$new_count=OCIResult($q_quotes,"STAT_NEW");
		$norm_count=OCIResult($q_quotes,"STAT_END_NORM");
		$nedoz_count=OCIResult($q_quotes,"STAT_NEDOZ");
		$perez_count=OCIResult($q_quotes,"STAT_PEREZ");
		$inwork_count=OCIResult($q_quotes,"STAT_INWORK");
		$estim_count=OCIResult($q_quotes,"ESTIMATE");
		$percent_full=OCIResult($q_quotes,"PERCENT");
		$lock_by_index=OCIResult($q_quotes,"LOCK_BY_INDEX");
		if($estim_count<=0 and $estim_count!==NULL) {$color='green'; $disabled=' disabled';}
		elseif($lock_by_index=='y') {$color='tomato'; $disabled=' disabled';}
		elseif($new_count+$nedoz_count+$perez_count+$inwork_count<=0) {$color='red'; $disabled=' disabled';}
		OCIBindByName($q_idx,":quote_id",$qid);
		$i=0; foreach($quoted_fields as $field_id => $field_name) {$i++;
			OCIBindByName($q_idx,":field_id",$field_id);
			OCIExecute($q_idx);
			OCIFetch($q_idx);
			if($i==1) $quote_name=OCIResult($q_idx,"VALUE");
			else $quote_name.=" - ".OCIResult($q_idx,"VALUE");
		}
		$quote_name.=" (���:".$norm_count."/".$quote."(".$percent_full.");�����:".$new_count.";�����:".$nedoz_count.";�����:".$perez_count.";� ������:".$inwork_count.")";
		echo "<option style=color:".$color.$disabled." value=".$qid.($qid==$_SESSION['survey']['call']['quote_id']?' selected':'').">".$quote_name."</option>";
	}
	
}
echo "</select>";
//����� ������ ���� =======================================================

//����� �� �������� =======================================================
echo "<nobr>�������:";
echo "<select name=status_type onchange='frm_change.submit()'>";
echo "<option value=auto>����</option>";
echo "<option value=auto_nedoz".($_SESSION['survey']['call']['status_type']=='auto_nedoz'?' selected':NULL).">����. ���������</option>";
echo "<option value=my_perez".($_SESSION['survey']['call']['status_type']=='my_perez'?' selected':NULL).">��� ���������</option>";
if($project['perez_policy']=='pub')
	echo "<option value=all_perez".($_SESSION['survey']['call']['status_type']=='all_perez'?' selected':NULL).">��� ���������</option>";
echo "<option value=my_inwork".($_SESSION['survey']['call']['status_type']=='my_inwork'?' selected':NULL).">��� �������������</option>";
echo "</select>";
//����� ������ �� �������� ================================================
echo "</form>";
//===============================================================================

//������ �������===========================================================================
//����. ����� ������, ��������� ============================================================
if($_SESSION['survey']['ank']['base']['id']=='') {
	if(substr($_SESSION['survey']['call']['status_type'],0,4)=='auto') {
		$_SESSION['survey']['ank']['base']['id']=get_auto($_SESSION['survey']['call']['status_type'],$_SESSION['user']['id'],$_SESSION['survey']['call']['quote_id'],$_SESSION['survey']['project']['id'],$project);
	}
	else {
		echo "sadsadasd";
		echo "<script>parent.callBottomFrame.location='survey.call.abonlist.php'</script>";
	}
}

//����� ����� �������, ����������======================================================

if($_SESSION['survey']['ank']['base']['id']<>'') $_SESSION['refresh_lock_records']='y';

//����� ��������
if($_SESSION['survey']['ank']['base']['id']<>'') {
	if($_SESSION['survey']['ank']['phone']['ord']=='') {//���� �� ������ �������, ���������� ��������� ������� �� ��������, �������������� ������ (������� inwork, ����� ��������, ����� �����, ����� ��������)
		echo "<hr>";
		$_SESSION['survey']['ank']['phone']=func_next_phone($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id'],$project['nedoz_interval'],$project['max_nedoz_count']);
	}
	if($_SESSION['survey']['ank']['phone']['ord']<>'') {//���� ����� �������, �� ���������� ���������� �������
		$q=OCIParse($c, "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t 
		where project_id=".$_SESSION['survey']['project']['id']." and base_id=".$_SESSION['survey']['ank']['base']['id']." and ord=".$_SESSION['survey']['ank']['phone']['ord']." and allow='y'");
		OCIExecute($q, OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"STATUS")=='perez') echo "<script>frm_change.quote_id.disabled=true;</script>";
		$_SESSION['survey']['ank']['phone']['num']=OCIResult($q,"PHONE");
		echo $_SESSION['survey']['ank']['phone']['num']." | ".OCIResult($q,"STATUS");
	}
echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
}

if($_SESSION['survey']['ank']['base']['id']<>'' or substr($_SESSION['survey']['call']['status_type'],0,4)=='auto') {
	echo "<hr>";
	func_show_call_buttons($_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone'],$project['num_src_fields'],$project['num_phone_fields']);
	echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php'</script>";
	/*
	echo "<script>parent.callBottomFrame.ankMainFrame.document.location='survey.ank.php'</script>";
	echo "<script>parent.callBottomFrame.ankInfoFrame.document.location='survey.aboninfo.php'</script>";
	*/
}
echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
echo "</form>";

//�������=================================================================================================================================
function get_auto($status_type,$user_id,$quote_id,$project_id,$project) {
	global $c;
	if(substr($status_type,0,4)=='auto') {
		//������������� �������
		func_unlock();
		
		if($project['from_time']=='00:00' and $project['to_time']=='00:00') $sql_time_limit='';
		else $sql_time_limit="and to_char(sysdate+nvl(b.utc_msk,0)/24,'HH24:MI') between '".$project['from_time']."' and '".$project['to_time']."' --����� �� �������� �������";

		if($project['perez_policy']=='pub') //�������� ����������: ����� ���������, ������������, �� �������� � ������� �� ������� �������
			$sql_perez_user="(b.status_user=".$user_id." 
			or b.status_user not in (select id from STC_USERS where last_oper_prj_id=".$project_id." and last_logout<=last_activity and last_activity>=sysdate-5/1440))";
		if($project['perez_policy']=='priv') //�������� ����������: ������� ���������
			$sql_perez_user="b.status_user=".$user_id;

		if($status_type=='auto') {
			if(mt_rand(1,100)<=$project['nedoz_chance'] or isset($_SESSION['nedoz_lock'])) {//������ ������ ��������� (�� �������� �������) � ����������� ��������� ���������� ��������, ���� ��� ��� �������
				$nedoz_ord=2; $_SESSION['nedoz_lock']='y'; //���������� ��������� ��� ��������� ������ �������
			} else $nedoz_ord=4;
			$sql="
update STC_BASE b set b.lock_user=".$user_id.", b.lock_date=sysdate where b.project_id=".$project_id." and id=
(
	select * from (
		select b.id
		from STC_BASE b, STC_SRC_QUOTES q
		where b.project_id=".$project_id." and b.allow='y' and b.lock_by_index is null 
		and (b.lock_user=".$user_id." or b.lock_date is null or b.lock_date<sysdate-5/1440) --�������� ����������
		--��������� ����������� ����� �� ��������
		and q.project_id(+)=".$project_id." and q.src_quote(+)-q.STAT_end_norm(+)<=0 and b.src_quote_id=q.id(+) and q.id is null
		and (
			(
				--���������
				b.status='perez' and ".$sql_perez_user." and b.perez_date_msk<=sysdate
				".$sql_time_limit."
			)
			or
			(
				--����� ������, ���������
				(b.status is null or (b.status='nedoz' and b.nedoz_date<=sysdate-".$project['nedoz_interval']."/1440))
				--���� ������� ���������� �����
				and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
				".$sql_time_limit." 
			)
		)
		order by decode(b.status,'perez',1,'nedoz',".$nedoz_ord.",null,3,5), b.perez_date_msk, nvl(b.utc_msk,0) desc, b.nedoz_date, b.status_date
	)
where rownum=1
)
returning id into :base_id";
		}

		if($status_type=='auto_nedoz') {
			$sql="
update STC_BASE b set b.lock_user=".$user_id.", b.lock_date=sysdate where b.project_id=".$project_id." and id=
(
	select * from (
		select b.id
		from STC_BASE b, STC_SRC_QUOTES q
		where b.project_id=".$project_id." and b.allow='y' and b.lock_by_index is null 
		and (b.lock_user=".$user_id." or b.lock_date is null or b.lock_date<sysdate-5/1440) --�������� ����������
		--��������� ����������� ����� �� ��������
		and q.project_id(+)=".$project_id." and q.src_quote(+)-q.STAT_end_norm(+)<=0 and b.src_quote_id=q.id(+) and q.id is null
		and (
			(
				--���������
				b.status='perez' and ".$sql_perez_user." and b.perez_date_msk<=sysdate
				".$sql_time_limit."
			)
			or
			(
				--���������
				b.status = 'nedoz' and b.nedoz_date<=sysdate-".$project['nedoz_interval']."/1440
				--���� ������� ���������� �����
				and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
				".$sql_time_limit." 
			)
		)
		order by decode(b.status,'perez',1,2), b.perez_date_msk,nvl(b.utc_msk,0) desc, b.nedoz_date, b.status_date
	)
where rownum=1
)
returning id into :base_id";
		}
		$base_id='';
		//���������� ������������� ��������
		OCIExecute(OCIParse($c,"update STC_X set X='x'"),OCI_DEFAULT);
		$upd=OCIParse($c,$sql);
		OCIBindByName($upd,":base_id",$base_id,16);
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
		$_SESSION['survey']['ank']['base']['id']=$base_id;
		$_SESSION['survey']['ank']['phone']['ord']='';
		$_SESSION['survey']['ank']['phone']['num']=='';
		return $base_id;
	}
}
function func_show_call_buttons($base_id,$phone,$num_src_fields,$num_phone_fields) {
	echo "<form name=frm_set_status method=post action=survey.call.save_status.php target=callLogFrame>";
	echo "<input type=hidden name=set_status>";
	if($num_src_fields==0) {
		echo "<font color=red>� ������� ��� �������� �����</font><hr>";
		echo "<input type=button style='background:green' value='������� ����� ������ � ������ �����' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone[ord] value='".$phone['ord']."'>";
		echo "<input type=hidden name=phone[num] value='".$phone['num']."'>";
	} 
	elseif($base_id=='') {
		echo "<font color=red>��� �������, ���������� ��� ������� � ������ �����</font><hr>";
		echo "<input type=button value='��������' onclick=frm_change.submit()>";
	}
	elseif($num_phone_fields==0) {
		echo "<font color=red>� ������� ��� ��������� ���� \"�������\"</font><hr>";
		echo "<input type=button style='background:green' value='������ ����� ��� ��������' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";	
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone[ord] value='".$phone['ord']."'>";
		echo "<input type=hidden name=phone[num] value='".$phone['num']."'>";
	}	
	elseif($phone['ord']=='') {
		echo "<font color=red>��� ������ ��������</font><hr>";
		echo "<input type=button style='background:green' name=inwork value='������ ����� ��� ��������' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=error value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";			
		echo "<input type=hidden name=phone[ord] value='".$phone['ord']."'>";
		echo "<input type=hidden name=phone[num] value='".$phone['num']."'>";
	}
	elseif($base_id<>'' and $phone['ord']<>'') {
		echo "<input type=button style='background:green' name=inwork value='������/������ �����' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:blue' name=perez value='�����������' onclick=frm_set_status.set_status.value='perez';frm_set_status.submit()></input>";
		echo "<input type=button style='background:orange' name=nedoz value='��������' onclick=frm_set_status.set_status.value='nedoz';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=otkaz value='�����' onclick=frm_set_status.set_status.value='otkaz';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=error value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";				
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone[ord] value='".$phone['ord']."'>";
		echo "<input type=hidden name=phone[num] value='".$phone['num']."'>";
	}
	echo "</form>";
}
function func_next_phone($project_id,$base_id,$nedoz_interval,$max_nedoz_count) {
	global $c;
	$phone['num']='';
	$phone['ord']='';
	$q=OCIParse($c, "select phone, ord, status, status_date,perez_date_msk,nedoz_count from STC_PHONES t
where project_id=".$project_id." and base_id=".$base_id." and allow='y' 
and (
status is null or status='inwork' 
or (status='perez' and nvl(perez_date_msk,sysdate)<=sysdate) 
or (status='nedoz' and status_date<=sysdate-".$nedoz_interval."/1440 and nvl(nedoz_count,0)<".$max_nedoz_count.")
)
order by decode(status,'inwork',1,'perez',2,null,3,'nedoz',4), perez_date_msk, ord");

	OCIExecute($q, OCI_DEFAULT);
	if(OCIFetch($q)) $phone['ord']=OCIResult($q,"ORD");
	return $phone;
}
function func_unlock() {
	global $c;
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
}
?>
