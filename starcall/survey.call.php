<?php include("starcall/session.cfg.php"); ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body class="body_marign" onLoad="parent.callFrameset.rows=Math.round(document.getElementById('end_page').offsetTop)+8+',*,1'">
<?php

//<body class="body_marign" onLoad="parent.callFrameset.rows=document.getElementById('end_page').getBoundingClientRect().top+8+',*,1'">
extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') exit();
include("starcall/conn_string.cfg.php");

if(!isset($_SESSION['survey']['call']['status_type'])) $_SESSION['survey']['call']['status_type']='auto';
if(!isset($_SESSION['survey']['call']['quote_id'])) $_SESSION['survey']['call']['quote_id']='auto';

if(!isset($_SESSION['survey']['ank']['base']['id'])) $_SESSION['survey']['ank']['base']['id']='';
if(!isset($_SESSION['survey']['ank']['base']['status'])) $_SESSION['survey']['ank']['base']['status']='';
if(!isset($_SESSION['survey']['ank']['phone']['id'])) $_SESSION['survey']['ank']['phone']['id']='';
if(!isset($_SESSION['survey']['ank']['phone']['status'])) $_SESSION['survey']['ank']['phone']['status']='';


if(// ������� ����� ��� ��� ������� - ������� ����������, ������������� �������
(isset($status_type) and $status_type<>$_SESSION['survey']['call']['status_type']) 
or
(isset($quote_id) and $quote_id<>$_SESSION['survey']['call']['quote_id'])
) {
	if(isset($status_type)) $_SESSION['survey']['call']['status_type']=$status_type;
	if(isset($quote_id)) $_SESSION['survey']['call']['quote_id']=$quote_id;
	$_SESSION['survey']['ank']['base']['id']='';
	$_SESSION['survey']['ank']['phone']['id']='';
	//$_SESSION['survey']['ank']['phone']['num']='';
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
//��������� ���������� � �������� �������, ������ �� ������� ������� =================================================
$q=OCIParse($c,"
select p.id p_id,p.name p_name, p.from_time p_from_time, p.to_time p_to_time, p.nedoz_interval p_nedoz_interval,
p.status p_status, p.num_src_fields p_num_src_fields, p.num_phone_fields p_num_phone_fields, p.perez_policy p_perez_policy, p.nedoz_count p_nedoz_count, p.nedoz_chance p_nedoz_chance,
p.lock_by_index p_lock_by_index,
p.quote p_quote,
p.stat_new p_stat_new,
p.stat_end_norm p_stat_end_norm,
p.stat_inwork p_stat_inwork,
p.stat_nedoz p_stat_nedoz,
p.stat_perez p_stat_perez,
decode(p.quote,0,'100%',decode(p.quote,NULL,NULL,round(p.stat_end_norm/p.quote*100,0)||'%')) p_proc
from STC_PROJECTS p
where p.id=".$_SESSION['survey']['project']['id']);
OCIExecute($q);

if(OCIFetch($q)) {
	$project_stat['name']=OCIResult($q,"P_NAME");
	$project_stat['status']=OCIResult($q,"P_STATUS");
	$project_stat['num_src_fields']=OCIResult($q,"P_NUM_SRC_FIELDS");
	$project_stat['num_phone_fields']=OCIResult($q,"P_NUM_PHONE_FIELDS");
	$project_stat['perez_policy']=OCIResult($q,"P_PEREZ_POLICY");
	$project_stat['lock_by_index']=OCIResult($q,"P_LOCK_BY_INDEX");
	$project_stat['quote']=OCIResult($q,"P_QUOTE");
	$project_stat['stat_end_norm']=OCIResult($q,"P_STAT_END_NORM");
	$project_stat['proc']=OCIResult($q,"P_PROC");
	$project_stat['stat_new']=OCIResult($q,"P_STAT_NEW");
	$project_stat['stat_nedoz']=OCIResult($q,"P_STAT_NEDOZ");
	$project_stat['stat_perez']=OCIResult($q,"P_STAT_PEREZ");
	$project_stat['stat_inwork']=OCIResult($q,"P_STAT_INWORK");
	
	echo "<nobr><b>����� �� �������:</b> �����: <b>".$project_stat['quote']."</b>; ���������: <b>".$project_stat['stat_end_norm']." (".$project_stat['proc'].")</b>; �����: <b>".$project_stat['stat_new']."</b>; ����������: <b>".$project_stat['stat_nedoz']."</b>; ����������: <b>".$project_stat['stat_perez']."</b>; � ������: <b>".$project_stat['stat_inwork']."</b>;";
}
if($project_stat['status']=='�������������' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr>";
	echo "<font color=orange>������ �������������!</font><hr>";
	echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));
	func_unlock();
	$_SESSION['refresh_lock_project']='n';
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	show_ank();
	exit();
}
if(($project_stat['quote']<>'' and $project_stat['quote']-$project_stat['stat_end_norm']<=0) and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr>";
	echo "<font color=green>����� ����� �� ������� ���������!</font><hr>";
	echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));
	func_unlock();
	$_SESSION['refresh_lock_project']='n';
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	show_ank();
	exit();
}
if($project_stat['lock_by_index']=='y' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr>";
	echo "<font color=green>��� ����������� ����� �� ������� ���������!</font><hr>";
	echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
	OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));
	func_unlock();
	$_SESSION['refresh_lock_project']='n';
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	show_ank();
	exit();
}
if($project_stat['status']=='������' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
	echo "<hr>";
	echo "<font color=red>������ ������!</font><hr>";
	echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";	
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	show_blank_ank();
	exit();
}
//��������� ������� ������ ���������
OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_oper_prj_id='".$_SESSION['survey']['project']['id']."' where id=".$_SESSION['user']['id']));
$_SESSION['refresh_lock_project']='y';
//====================================================================================================================================

//�������� ������������� ������� � ������.
if($_SESSION['survey']['ank']['base']['status']<>'inwork' and $_SESSION['survey']['ank']['base']['id']=='') {
	$q=OCIParse($c,"select b.id from STC_USER_INWORK w, STC_BASE b 
where w.user_id=".$_SESSION['user']['id']." and w.project_id=".$_SESSION['survey']['project']['id']."
and b.id=w.base_id and b.project_id=w.project_id
and b.status='inwork' and b.status_user=".$_SESSION['user']['id']." 
and b.allow='y' and b.lock_by_index is null and b.src_quote_id not in (
    select q.id from STC_SRC_QUOTES q where q.project_id=".$_SESSION['survey']['project']['id']." and q.src_quote-q.STAT_end_norm<=0
)");
	OCIExecute($q, OCI_DEFAULT);
	if(OCIFetch($q)) {
		echo "<hr>";
		echo "<font color=red><b>����� ������� ����������, ��������� ������� �����.</b></font>";
		$_SESSION['survey']['ank']['base']['id']=OCIResult($q,"ID");
		echo "<hr>";
		show_base($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id']);
		$_SESSION['survey']['ank']['phone']['id']=get_phone_id($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id']);
		if($_SESSION['survey']['ank']['phone']['id']<>'') {
			echo "<hr>";
			show_phone($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone']['id']);
		}
		echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
		show_ank(); 
		exit();
	}	
}

//����� ��������� ���������� �� ����� � ������ ====================================================
if($_SESSION['survey']['call']['quote_id']<>'auto') {

	$q=OCIParse($c,"
	select q.id q_id,
	q.lock_by_index q_lock_by_index,
	q.src_quote q_quote,
	q.stat_new q_stat_new,
	q.stat_end_norm q_stat_end_norm,
	q.stat_inwork q_stat_inwork,
	q.stat_nedoz q_stat_nedoz,
	q.stat_perez q_stat_perez,
	decode(q.src_quote,0,'100%',decode(q.src_quote,NULL,NULL,round(q.stat_end_norm/q.src_quote*100,0)||'%')) q_proc
	from STC_SRC_QUOTES q
	where q.project_id=".$_SESSION['survey']['project']['id']." and q.id=".$_SESSION['survey']['call']['quote_id']);
	OCIExecute($q);
	if(OCIFetch($q)) {
		$quote_stat['name']='';
		$quote_stat['lock_by_index']=OCIResult($q,"Q_LOCK_BY_INDEX");
		$quote_stat['quote']=OCIResult($q,"Q_QUOTE");
		$quote_stat['stat_end_norm']=OCIResult($q,"Q_STAT_END_NORM");
		$quote_stat['proc']=OCIResult($q,"Q_PROC");
		$quote_stat['stat_new']=OCIResult($q,"Q_STAT_NEW");
		$quote_stat['stat_nedoz']=OCIResult($q,"Q_STAT_NEDOZ");
		$quote_stat['stat_perez']=OCIResult($q,"Q_STAT_PEREZ");
		$quote_stat['stat_inwork']=OCIResult($q,"Q_STAT_INWORK");
		$q1=OCIParse($c,"select i.value, i.src_idx_quote, i.stat_end_norm,
		decode(i.src_idx_quote,0,'100%',decode(i.src_idx_quote,NULL,NULL,round(i.STAT_end_norm/i.src_idx_quote*100,0)||'%')) proc
		from STC_SRC_QUOTE_INDEXES qi, Stc_Src_Indexes i, Stc_Fields f
 		where qi.project_id=".$_SESSION['survey']['project']['id']." and qi.quote_id=".$_SESSION['survey']['call']['quote_id']."
 		and i.project_id=".$_SESSION['survey']['project']['id']."
 		and i.id=qi.index_id
 		and f.project_id=".$_SESSION['survey']['project']['id']."
 		and f.id=i.field_id
 		order by f.ord");	
 		OCIExecute($q1,OCI_DEFAULT);
 		$i=0; while(OCIFetch($q1)) {$i++;
			if($i>1) $quote_stat['name'].= " | ";
			$quote_stat['name'].="<b>".OCIResult($q1,"VALUE")."</b> ".OCIResult($q1,"STAT_END_NORM")."/".OCIResult($q1,"SRC_IDX_QUOTE")." (".OCIResult($q1,"PROC").")";
 		}
  		echo "<hr>";
		echo "<nobr>".$quote_stat['name'].". �����: <b>".$quote_stat['quote']."</b>; ���������: <b>".$quote_stat['stat_end_norm']." (".$quote_stat['proc'].")</b>; �����: <b>".$quote_stat['stat_new']."</b>; ����������: <b>".$quote_stat['stat_nedoz']."</b>; ����������: <b>".$quote_stat['stat_perez']."</b>; � ������: <b>".$quote_stat['stat_inwork']."</b>;";
	}
	//������� �� �����
	if(($quote_stat['quote']<>'' and $quote_stat['quote']-$quote_stat['stat_end_norm']<=0) and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
		echo "<hr>";
		echo "<font color=green>��������� ����� ���������!</font><hr>";
		echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
		OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));
		func_unlock();
		$_SESSION['refresh_lock_project']='n';
		echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
		show_quotes();
		exit();
	}
	if($quote_stat['lock_by_index']=='y' and $_SESSION['survey']['ank']['base']['status']<>'inwork') {
		echo "<hr>";
		echo "<font color=green>����������� ����� ���������!</font><hr>";
		echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
		OCIExecute(OCIParse($c, "update STC_USERS set last_activity=sysdate, last_php_ssid='".session_id()."', last_ip='".$_SERVER['REMOTE_ADDR']."', last_oper_prj_id='' where id=".$_SESSION['user']['id']));
		func_unlock();
		$_SESSION['refresh_lock_project']='n';
		echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
		show_quotes();
		exit();
	}			
	//
}
//====================================================================================================================================

//���� � ������� ��� �������� �����
if($project_stat['num_src_fields']==0 and $_SESSION['survey']['ank']['base']['id']=='') {
	echo "<hr>";
	show_call_buttons($_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone']['id'],0,0);
	echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
	exit();
}
//

if($_SESSION['survey']['ank']['base']['status']=='inwork') {
			echo "<hr>";
			show_base($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id']);
			if($_SESSION['survey']['ank']['phone']['id']<>'') {
				echo "<hr>";
				show_phone($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone']['id']);
			}
			echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
			show_ank();
			exit();	
}
//

echo "<hr>";
echo "<form name=frm_change_status method=post>";
echo "<nobr>";
//����� �����
show_select_quote($_SESSION['survey']['project']['id']);

echo " | ";

//����� �� �������� =======================================================
show_select_status($_SESSION['survey']['call']['status_type'],$project_stat['perez_policy']);
echo "</nobr>";
echo "</form>";

//===============================================================================

//������ �������===========================================================================

//����. ����� ������, ��������� ============================================================
if(substr($_SESSION['survey']['call']['status_type'],0,4)=='auto' and $_SESSION['survey']['ank']['base']['id']=='') {
	$_SESSION['survey']['ank']['base']['id']=get_base_id($_SESSION['survey']['project']['id'],$_SESSION['survey']['call']['status_type'],$_SESSION['user']['id'],$_SESSION['survey']['call']['quote_id']);
	$_SESSION['survey']['ank']['phone']['id']='';
}
if($_SESSION['survey']['ank']['base']['id']=='' and substr($_SESSION['survey']['call']['status_type'],0,4)<>'auto') 
	show_abonlist();

if($_SESSION['survey']['ank']['base']['id']<>'') {
	echo "<hr>";
	show_base($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id']);
	//����� ��������
	if($_SESSION['survey']['ank']['phone']['id']=='') {//���� �� ������ �������, ���������� ��������� ������� �� ��������, �������������� ������ (������� inwork, ����� ��������, ����� �����, ����� ��������)
		$_SESSION['survey']['ank']['phone']['id']=get_phone_id($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id']);
	}
	if($_SESSION['survey']['ank']['phone']['id']<>'') {//���� ����� �������, �� ���������� ���������� �������
		echo "<hr>";
		show_phone($_SESSION['survey']['project']['id'],$_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone']['id']); 
	}
	//
	$_SESSION['refresh_lock_records']='y';
	show_ank();
}


if($_SESSION['survey']['ank']['base']['id']<>'' or substr($_SESSION['survey']['call']['status_type'],0,4)=='auto') {
	echo "<hr>";
	show_call_buttons($_SESSION['survey']['ank']['base']['id'],$_SESSION['survey']['ank']['phone']['id'],$project_stat['num_src_fields'],$project_stat['num_phone_fields']);
}
echo "<div id=end_page></div>"; //��� ��������� (��� ����������� ������ ������
echo "</form>";

//�������=================================================================================================================================
function get_base_id($project_id,$status_type,$user_id,$quote_id) {
	global $c;
	if(substr($status_type,0,4)=='auto') {
		//������������� �������
		func_unlock();
		
		//��������� �������� �������
		$q=OCIParse($c,"select p.from_time,p.to_time,p.nedoz_chance,p.nedoz_interval,p.perez_policy from STC_PROJECTS p where p.id=".$project_id);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$from_time=OCIResult($q,"FROM_TIME");
		$to_time=OCIResult($q,"TO_TIME");
		$perez_policy=OCIResult($q,"PEREZ_POLICY");
		$nedoz_chance=OCIResult($q,"NEDOZ_CHANCE");
		$nedoz_interval=OCIResult($q,"NEDOZ_INTERVAL");
		
		if($from_time=='00:00' and $to_time=='00:00') $sql_time_limit='';
		else $sql_time_limit="and to_char(sysdate+nvl(b.utc_msk,0)/24,'HH24:MI:SS') between '".$from_time.":00' and '".$to_time.":00' --����� �� �������� �������";

		if($perez_policy=='pub') //�������� ����������: ����� ���������, ������������, �� �������� � ������� �� ������� �������
			$sql_perez_user="(b.status_user=".$user_id." 
			or b.status_user not in (select id from STC_USERS where last_oper_prj_id=".$project_id." and last_logout<=last_activity and last_activity>=sysdate-5/1440))";
		if($perez_policy=='priv') //�������� ����������: ������� ���������
			$sql_perez_user="b.status_user=".$user_id;

		if($status_type=='auto') {
			if(mt_rand(1,100)<=$nedoz_chance or isset($_SESSION['nedoz_lock'])) {//������ ������ ��������� (�� �������� �������) � ����������� ��������� ���������� ��������, ���� ��� ��� �������
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
				(b.status is null or (b.status='nedoz' and b.nedoz_date<=sysdate-".$nedoz_interval."/1440))
				--���� ������� ���������� �����
				and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
				".$sql_time_limit." 
			)
		)
		order by decode(b.status,'perez',1,'nedoz',".$nedoz_ord.",null,3,5), b.perez_date_msk, to_char(sysdate+nvl(b.utc_msk,0)/24,'HH24MISS') desc, b.nedoz_date, b.status_date
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
				b.status = 'nedoz' and b.nedoz_date<=sysdate-".$nedoz_interval."/1440
				--���� ������� ���������� �����
				and (b.src_quote_id is null or b.src_quote_id=decode('".$quote_id."','auto',b.src_quote_id,'".$quote_id."'))
				".$sql_time_limit." 
			)
		)
		order by decode(b.status,'perez',1,2), b.perez_date_msk, to_char(sysdate+nvl(b.utc_msk,0)/24,'HH24MISS') desc, b.nedoz_date, b.status_date
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
		return $base_id;
	}
}
function get_phone_id($project_id,$base_id) {
	global $c;
	$phone_id='';
	$q=OCIParse($c, "select ph.id from STC_PROJECTS pj, STC_PHONES ph
where 
pj.id=".$project_id."
and ph.project_id=pj.id and ph.base_id=".$base_id." and ph.allow='y' 
and (
ph.status is null or ph.status='inwork' 
or (ph.status='perez' and nvl(ph.perez_date_msk,sysdate)<=sysdate) 
or (ph.status='nedoz' and ph.status_date<=sysdate-pj.nedoz_interval/1440 and nvl(ph.nedoz_count,0)<pj.nedoz_count)
)
order by decode(ph.status,'inwork',1,'perez',2,null,3,'nedoz',4), ph.perez_date_msk, ph.ord");

	OCIExecute($q, OCI_DEFAULT);
	if(OCIFetch($q)) $phone_id=OCIResult($q,"ID");
	return $phone_id;
}
function show_abonlist() {
	echo "<script>parent.callBottomFrame.location='survey.call.abonlist.php'</script>";
}
function show_ank() {
	echo "<script>parent.callBottomFrame.document.location='survey.ank.frame.php';</script>";
}
function show_blank_ank() {
	echo "<script>parent.callBottomFrame.document.location='blank_page.php';</script>";
}
function show_quotes() {
	echo "<script>parent.callBottomFrame.location='survey.quotes.php'</script>";
}
function show_select_quote($project_id) {
	global $c;
	$q=OCIParse($c,"select count(*) cnt from STC_FIELDS f
	where f.project_id=".$project_id." and f.src_type_id=1 and f.quoted is not null and f.deleted is null");
	OCIExecute($q);
	OCIFetch($q);
	if(OCIResult($q,"CNT")>0) 
		echo "<a href='survey.quotes.php' target='callBottomFrame'><b>����� �����</b></a>";
	else 
		echo "<b>��� ����</b>";
}
function show_select_status($status_type,$perez_policy) {
	echo "�������:";
	echo "<select name=status_type onchange='frm_change_status.submit()'>";
	echo "<option value=auto>����</option>";
	echo "<option value=auto_nedoz".($_SESSION['survey']['call']['status_type']=='auto_nedoz'?' selected':NULL).">����. ���������</option>";
	echo "<option value=my_perez".($_SESSION['survey']['call']['status_type']=='my_perez'?' selected':NULL).">��� ���������</option>";
	if($perez_policy=='pub')
		echo "<option value=all_perez".($_SESSION['survey']['call']['status_type']=='all_perez'?' selected':NULL).">��� ���������</option>";
	echo "<option value=my_inwork".($_SESSION['survey']['call']['status_type']=='my_inwork'?' selected':NULL).">��� �������������</option>";
	echo "</select>";
}
function show_base($project_id,$base_id) {
	global $c;
	$q=OCIParse($c, "select b.id, b.status, b.allow, b.lock_by_index,
	case when b.utc_msk>=0 then '+'||b.utc_msk else to_char(b.utc_msk) end utc_msk,
	to_char(nvl(sysdate+b.utc_msk/24,sysdate),'HH24:MI') local_time,
	to_char(nvl(sysdate+b.utc_msk/24,sysdate),'DD') local_DD,
	to_char(nvl(sysdate+b.utc_msk/24,sysdate),'MM') local_MM
	from STC_BASE b
	where b.project_id=".$project_id." and b.id=".$base_id);
	OCIExecute($q, OCI_DEFAULT);	
	OCIFetch($q);
	$src_mon=array('01','02','03','04','05','06','07','08','09','10','11','12');
	$rep_mon=array('���','���','���','���','���','���','���','���','���','���','���','���');
	$_SESSION['survey']['ank']['base']['status']=OCIResult($q,"STATUS");
	echo "ID: ".OCIResult($q,"ID")." | ".OCIResult($q,"STATUS")." | ������� ����� (���<b>".OCIResult($q,"UTC_MSK")."</b>): <b>".OCIResult($q,"LOCAL_DD").".".str_replace($src_mon,$rep_mon,OCIResult($q,"LOCAL_MM"))." ".OCIResult($q,"LOCAL_TIME")."</b>";
}

function show_phone($project_id,$base_id,$phone_id) {
	global $c;
	$q=OCIParse($c, "select p.id, p.phone, p.ext, p.status, p.status_date,p.perez_date_msk,p.nedoz_count 
	from STC_PHONES p 
	where p.project_id=".$project_id." and p.base_id=".$base_id." and p.id=".$phone_id." and p.allow='y'");
	OCIExecute($q, OCI_DEFAULT);
	OCIFetch($q);
	$_SESSION['survey']['ank']['phone']['status']=OCIResult($q,"STATUS");
	include('func.segment_phone.php');
	echo "���.: <b>".segment_phone(OCIResult($q,"PHONE"))."</b>".(OCIResult($q,"EXT")<>''?' ���.: <b>'.str_replace("&",",",OCIResult($q,"EXT")).'</b>':NULL)." | ".OCIResult($q,"STATUS");	
}
function show_call_buttons($base_id,$phone_id,$num_src_fields,$num_phone_fields) {
	echo "<form name=frm_set_status method=post action=survey.call.save_status.php target=callLogFrame>";
	echo "<input type=hidden name=set_status>";
	if($num_src_fields==0) {
		echo "<font color=red>� ������� ��� �������� �����</font><hr>";
		echo "<input type=button style='background:green' value='������� ����� ������ � ������ �����' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone_id value='".$phone_id."'>";
		show_ank();
	} 
	elseif($base_id=='') {
		echo "<font color=red>��� �������, ���������� ��� ������ � ������ �����</font> <a href='help.survey.html#no_rec' target='_blank'>?</a><hr>";
		echo "<input type=button value='��������' onclick=document.location='survey.call.php'>";
		show_quotes();
	}
	elseif($num_phone_fields==0) {
		echo "<font color=red>� ������� ��� ��������� ���� \"�������\"</font><hr>";
		echo "<input type=button style='background:green' value='������ ����� ��� ��������' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";	
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone_id value='".$phone_id."'>";
		show_ank();
	}	
	elseif($phone_id=='') {
		echo "<font color=red>��� ������ ��������</font><hr>";
		echo "<input type=button style='background:green' name=inwork value='������ ����� ��� ��������' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=error value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";			
		echo "<input type=hidden name=phone_id value='".$phone_id."'>";
		show_ank();
	}
	elseif($base_id<>'' and $phone_id<>'') {
		echo "<input type=button style='background:green' name=inwork value='������/������ �����' onclick=frm_set_status.set_status.value='inwork';frm_set_status.submit()></input>";
		echo "<input type=button style='background:blue' name=perez value='�����������' onclick=document.all.ifr_perez.src='survey.func.perez_date.php?base_id=".$base_id."&phone_id=".$phone_id."';document.all.ifr_perez.style.display='';></input>";
		echo "<input type=hidden name=perez_phone></input>
			<input type=hidden name=perez_ext></input>
			<input type=hidden name=perez_date></input>
			<input type=hidden name=perez_min></input>";
		echo "<iframe id=ifr_perez name=ifr_perez style='display:none' class=ifr_perez></iframe>";
		echo "<input type=button style='background:orange' name=nedoz value='��������' onclick=frm_set_status.set_status.value='nedoz';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=otkaz value='�����' onclick=frm_set_status.set_status.value='otkaz';frm_set_status.submit()></input>";
		echo "<input type=button style='background:red' name=error value='������' onclick=frm_set_status.set_status.value='error';frm_set_status.submit()></input>";				
		echo "<input type=hidden name=base_id value='".$base_id."'>";
		echo "<input type=hidden name=phone_id value='".$phone_id."'>";
		show_ank();
	}
	echo "</form>";
}
function func_unlock() {
	global $c;
	OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
}
?>
