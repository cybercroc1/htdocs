<?php
extract($_REQUEST);
session_start();

if (isset($_SESSION['auth']) and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or $_SESSION['eval']<>'create_new')) {
}
else {
echo "<font color=red><b>������: � ��� ��� ���� ��� ��������� ������ �������� ��� �� �� ������ �����������</b></font>";
exit();
}
include("../../sup_conf/sup_conn_string");

if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
	$location_id_opts='';
	$location_id_HTML='';
	//������ ��������������
	if($_SESSION['redirect']=='y') {//����� ������� ������ ������� �� �����, � ������� ���� ����������� ��� �����������
		$q=OCIParse($c,"select distinct k.id,lg.id group_id,lg.name group_name,k.name location_name from sup_user_lt_alloc sla,sup_lt lt,sup_user u,SUP_LOCATION_GROUP lg, sup_klinika k
		where sla.lt_group_id=lt.lt_grp_id
		and u.id=sla.user_id
	    and u.id=sla.user_id
	    and u.deleted is null and (u.redirect='y' or u.solution='y')
	    and k.id=lt.location_id
	    and k.location_grp_id=lg.id
		and (k.deleted is null or k.id=".$new_location_id.")
		order by lg.name,k.name");
	} else { //���� ������������ �� ����� ����� ����������������, �� �� ����� ������� ������ �� �������, ��� ����� ����� ������ ��������
		$q=OCIParse($c,"select k.id,lg.id group_id,lg.name group_name,k.name location_name from SUP_LOCATION_GROUP lg, sup_klinika k
 		where k.location_grp_id=lg.id
  	 	and k.id in (select lt.location_id from sup_lt lt, sup_user_lt_alloc sla where sla.user_id=".$_SESSION['user_id']." and lt.lt_grp_id=sla.lt_group_id)
		and (k.deleted is null or k.id=".$new_location_id.")
  		order by lg.name,k.name");	
	}
	OCIExecute($q,OCI_DEFAULT);
	//����� ����� ��� ������ ��������������
	while (OCIFetch($q)) {
		if(!isset($tmp) or $tmp<>OCIResult($q,"GROUP_ID")) {
			$location_id_opts.="<optgroup label='".OCIResult($q,"GROUP_NAME")."'></optgroup>";
		}
		$location_id_opts.="<option value='".OCIResult($q,"ID")."'";
		if(OCIResult($q,"ID")==$old_location_id) $location_id_opts.=" style='color:green'";
		if(OCIResult($q,"ID")==$new_location_id) $location_id_opts.=" selected";
		$location_id_opts.=">".OCIResult($q,"LOCATION_NAME")."</option>";	
		$tmp=OCIResult($q,"GROUP_ID");
	}
	$location_id_HTML.="��������������: <select name=new_location_id onchange=ch_loc_trbl()>";
	$location_id_HTML.=$location_id_opts;
	$location_id_HTML.="</select>";
	
	echo "<script>
	parent.document.getElementById('div_location_id').innerHTML='".str_replace("'","\'",$location_id_HTML)."';
	</script>";
}
//������ ����� �������
if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
	//��� ��������
	if($_SESSION['redirect']=='y') { //����� ������� ������ ���� ������� �� �����, � ������� ���� ����������� ��� �����������
		$q=OCIParse($c,"select distinct tg.id group_id,tg.name group_name,t.id trbl_id,t.name trbl_name,t.ord from sup_lt lt, sup_user_lt_alloc sla,sup_user u, SUP_TRBL_TYPE t, Sup_Trbl_Group tg
	    where lt.location_id=".$new_location_id."
	    and lt.lt_grp_id<>0
	    and sla.lt_group_id=lt.lt_grp_id
	    and u.id=sla.user_id
	    and u.deleted is null and (u.redirect='y' or u.solution='y')
	    and t.id=lt.trbl_id
	    and t.trbl_grp_id=tg.id 
	    and (t.deleted is null or t.id='".$new_trbl_type_id."')
	    order by tg.name,t.name");
	} 
	else { //���� ������������ �� ����� ����� ����������������, �� �� ����� ������� ������ �� ��������, ������� ����� ����� ������ ���
		$q=OCIParse($c,"select distinct tg.id group_id,tg.name group_name,t.id trbl_id,t.name trbl_name,t.ord 
		from sup_lt lt, sup_user_lt_alloc sla ,SUP_TRBL_TYPE t, Sup_Trbl_Group tg
	    where lt.location_id=".$new_location_id."
	    and t.id=lt.trbl_id
	    and sla.lt_group_id=lt.lt_grp_id
		and lt.lt_grp_id<>0
	   	and sla.user_id=".$_SESSION['user_id']."
	    and t.trbl_grp_id=tg.id 
	    and (t.deleted is null or t.id='".$new_trbl_type_id."')
   		order by tg.name,t.name");
	}
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	//����� ����� ��� ������ ��������
	$trbl_type_id_opts='<option></option>';
	$trbl_type_id_HTML='';
	while (OCIFetch($q)) {
		if(!isset($tmp) or $tmp<>OCIResult($q,"GROUP_ID")) {
			$trbl_type_id_opts.="<optgroup label='".OCIResult($q,"GROUP_NAME")."'></optgroup>";
		}
		$trbl_type_id_opts.="<option value='".OCIResult($q,"TRBL_ID")."'";
		if(OCIResult($q,"TRBL_ID")==$old_trbl_type_id) $trbl_type_id_opts.=" style='color:green'";
		if(OCIResult($q,"TRBL_ID")==$new_trbl_type_id) $trbl_type_id_opts.=" selected";
		$trbl_type_id_opts.=">".OCIResult($q,"TRBL_NAME")."</option>";	
		$tmp=OCIResult($q,"GROUP_ID");
	}
	$trbl_type_id_HTML.="��� ��������: <font color=red>��������! �� �������� �������� ��� ��������!</font><br>";
	$trbl_type_id_HTML.="<select name='new_trbl_type_id' onchange=ch_loc_trbl()>";
	$trbl_type_id_HTML.=$trbl_type_id_opts;
	$trbl_type_id_HTML.="</select>";
	
	echo "<script>
	parent.document.getElementById('div_trbl_type').innerHTML='".str_replace("'","\'",$trbl_type_id_HTML)."';
	</script>";
	//
	
	//������ ��������
	$q=OCIParse($c,"select t.id,t.name from SUP_TRBL_DETAIL t
	where t.trbl_id='".$new_trbl_type_id."' and (t.deleted is null or t.id='".$new_trbl_det_id."')
	order by name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	//����� ����� ��� ������ ������ ��������
	$trbl_detail_opts='';
	$trbl_detail_HTML='';
	while (OCIFetch($q)) {
		$trbl_detail_opts.="<option value='".OCIResult($q,"ID")."'";
		if(OCIResult($q,"ID")==$old_trbl_det_id) $trbl_detail_opts.=" style='color:green'";
		if(OCIResult($q,"ID")==$new_trbl_det_id) $trbl_detail_opts.=" selected";
		$trbl_detail_opts.=">".OCIResult($q,"NAME")."</option>";
		//echo 	OCIResult($q,"NAME");
	}
	
	if($trbl_detail_opts<>'') {
		$trbl_detail_HTML.="������: <select name=new_trbl_det_id><option value='' style='color:red'>��������, ��� ���������?</option>";
		$trbl_detail_HTML.=$trbl_detail_opts;
		$trbl_detail_HTML.="</select>";	
		
		echo "<script>
		parent.document.getElementById('div_trbl_detail').innerHTML='".str_replace("'","\'",$trbl_detail_HTML)."';
		</script>";
	}
	else {
		echo "<script>
		parent.document.getElementById('div_trbl_detail').innerHTML='';
		</script>";		
	}
}
//
//������ ��������
$q=OCIParse($c,"select distinct su.id, su.fio from sup_lt slt, sup_user_lt_alloc sla, sup_user su
where slt.location_id=".$new_location_id." and slt.trbl_id='".$new_trbl_type_id."'
and sla.lt_group_id=slt.lt_grp_id
and su.id=sla.user_id
and su.solution='y' and su.deleted is null
order by su.fio");
	
OCIExecute($q,OCI_DEFAULT);
$i=0;
//����� ����� ��� ������ ������������� ��� ������� � ��� �������������
$callback_who_opts='';
$callback_who_HTML='';
$to_user_id_opts='';
$to_user_id_HTML='';
while (OCIFetch($q)) {
$i++;	
	$callback_who_opts.="<option value='".OCIResult($q,"ID")."'>".OCIResult($q,"FIO")."</option>";

	$to_user_id_opts.="<option value='".OCIResult($q,"ID")."'";
	if(OCIResult($q,"ID")==$_SESSION['user_id']) $to_user_id_opts.=" style='color:green' selected";
	else $to_user_id_opts.=" style='color:maroon'";
	if($from_user_id=='' and OCIResult($q,"ID")==$_SESSION['user_id']) $to_user_id_opts.=" selected"; //���� ������ ��� �� ������� � ������, �� �� ��������� - ���������
	$to_user_id_opts.=">".OCIResult($q,"FIO")."</option>";	
}

if($i==0) {//���� ��� ��������� ��� ������� ���������� ���� ��������
		
	$callback_who_opts.="<option value='".$_SESSION['user_id']."'>".$_SESSION['fio']."</option>";
	
	$to_user_id_opts.="<option value='' style='color:indigo'>�������� �����������</option>";
	$comm='y';
	$to_user_id_opts.="<option value='".$_SESSION['user_id']."'";
	$to_user_id_opts.=" style='color:green'";
	//if($from_user_id=='') $to_user_id_opts.=" selected"; //���� ������ ��� �� ������� � ������, �� �� ��������� - ���������
	$to_user_id_opts.=">".$_SESSION['fio']."</option>";
	
}
//
//������ �������
if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
	
	$callback_who_HTML.="<b>��� ����������:</b> ";
	$callback_who_HTML.="<select name=callback_who onchange=fn_check()>";
	$callback_who_HTML.="<option></option>";
	if($_SESSION['look']=='y') {
		$callback_who_HTML.=$callback_who_opts;
	}
	else {
		$callback_who_HTML.="<option value='".$_SESSION['user_id']."'>".$_SESSION['fio']."</option>";	
	}
	$callback_who_HTML.="</select>";

	echo "<script>
	parent.document.getElementById('div_callback_who').innerHTML='".str_replace("'","\'",$callback_who_HTML)."';
	</script>";
}
//
//������ �������� ��� �������������
if($_SESSION['redirect']=='y') {
	$to_user_id_HTML.="<font color=indigo>��������������</font> / <font color=maroon>��������������</font> / <font color=green>��������� ������</font>: ";
	$to_user_id_HTML.="<nobr>";
	$to_user_id_HTML.="<select name=to_user_id onchange=fn_check()>";
	//���� ������������ ����� ����� ���������������� � ��� ���� ������ ������� � ��� ��������, ������� �� ����� ����� ������, �� �� ����� �������� ������������, ��� �� ������ ������ ���, ��� ����� ����� ������ ��.	
	$q=OCIParse($c,"select '�' from SUP_USER_LT_ALLOC sla, SUP_LT lt, sup_lt_group slg
	where lt.location_id='".$new_location_id."'
	and lt.trbl_id='".$new_trbl_type_id."'
	and slg.id=lt.lt_grp_id
	and slg.id<>0
	and sla.lt_group_id=slg.id
	and sla.user_id=".$_SESSION['user_id']);
	OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		if(isset($comm)) $to_user_id_HTML.="<option value='' style='color:indigo'>�������� �����������</option>";
	}
	else {
		//if($from_user_id<>'') $to_user_id_HTML.="<option value='group' style='color:maroon'>�������������� � ������ ��������� (������� ������)</option>";
	}
	$to_user_id_HTML.=$to_user_id_opts;
	/*if($from_user_id<>'')*/ $to_user_id_HTML.="<option value='group' style='color:blue'>�������������� �� ������ ��������� (������� ������)</option>";
	$to_user_id_HTML.="</select>";
}
if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y' or $_SESSION['create_new']=='y') {
	$to_user_id_HTML.=" <nobr><input type=submit disabled name=save style='background-color:#66FF66' value='���������'>";
}
$to_user_id_HTML.="</nobr>";

echo "<script>
parent.document.getElementById('div_to_user_id').innerHTML='".str_replace("'","\'",$to_user_id_HTML)."';
</script>";
//



?>
<script>parent.fn_check();</script>