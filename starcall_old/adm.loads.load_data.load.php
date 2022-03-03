<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_time_limit(0);
ignore_user_abort(true);
set_error_handler ("my_error_handler");
include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");
include("func.phones_conv.php");
ob_implicit_flush();
extract($_POST);

if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

$project_id=$_SESSION['adm']['project']['id']; //�� ���������� ���������� ���������� ��� ��������, ��� �� �� ��������� ��, ������ ������.
if(isset($uniq_term)) $_SESSION['adm']['project']['uniq_term']=$uniq_term;
else $uniq_term=$_SESSION['adm']['project']['uniq_term'];

$commit_interval=15000; //���������� ����� � ����� ����������

//===========================================================================================
//�������� ������ � ��������������
$error='';
$warning='';
$info='';

//������: ������ ���������
if(!isset($base_fields_id)) {
	$error.="<font color=red>������: ������ ���������</font><br>";
	$base_fields_id=array();
}

//������: �� ��������� ���������� ��������
$q=OCIParse($c,"select count(*) cnt from STC_LOAD_HISTORY 
where status='�����������...'");
OCIExecute($q,OCI_DEFAULT);OCIFetch($q);
if(OCIResult($q,"CNT")>0) {
	$error.="<font color=red>������: �� ��������� ���������� ��������. ���������� �����.</font><br>";
}
//

//�����. ���� ����� ��������, �� �� ����������� � �� ����������� �����, �.�. ��� ����� �� �������� ������������� ������
$q=OCIParse($c,"select src_quote_broken from stc_projects where id=".$project_id);
OCIExecute($q,OCI_DEFAULT);OCIFetch($q); if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'') $quote_broken='y';

	
$std_field_phone='no';
//���������� ��� ���������� ����
$ffcount=0; foreach($base_fields_id as $key=>$id) {
	//�������� �������
	$file_fields_name[$key]=trim($file_fields_name[$key]);
	$base_fields_text_name[$id]=trim($base_fields_text_name[$id]);
	$base_fields_code_name[$id]=trim($base_fields_code_name[$id]);

	if($file_fields_name[$key]<>'') {$ffcount++;}
	//�������� ������������� ������������ ���� "�������"
	if(isset($base_fields_std_name[$id]) and $base_fields_std_name[$id]=='PHONE' and $file_fields_num[$key]<>'') $std_field_phone='yes';
			
	//������: ������ ����� �����
	if($base_fields_text_name[$id]=='' or $base_fields_code_name[$id]=='') {
		$error.="<font color=red>������: ������ ��� ��� ������� ��� ���� (id:$id)</font><br>";
	}
	//������: �������������� ������������ ����
	if(isset($base_fields_quoted[$id]) and (!isset($base_fields_must[$id]) or isset($base_fields_uniq[$id]))) {
		$error.="<font color=red>������: ����������� ���� \"$base_fields_text_name[$id]\" (id:$id) ������ ���� ������������ � �� ����������</font><br>";
	}
	//������: ������������� ���� �� ������ ���� ����������
	if(isset($base_fields_idx[$id]) and isset($base_fields_uniq[$id])) {
		$error.="<font color=red>������: ������������� ���� \"$base_fields_text_name[$id]\" (id:$id) �� ������ ���� ����������</font><br>";
	}	
	//������: ������������� ���� ������������ ������ ���� �����
	if(isset($base_fields_must[$id]) and $file_fields_num[$key]=='') {
		$error.="<font color=red>������: ������������� ���� \"$base_fields_text_name[$id]\" (id:$id) ������������ ������ ���� �����</font><br>";
	}
	//������: ����������������� ����� "�����"
	if(trim($base_fields_text_name[$id])=="�����") {
		$error.="<font color=red>������: ������ �������� ���� ����������������� ������ \"�����\"</font><br>";
	}
	//��������������: ��� ������ ������������� ����������� ����, ����� ������������ ����������
	if(isset($base_fields_idx[$id]) and isset($base_fields_quoted[$id])) {
		unset($base_fields_idx[$id]);
		if($load_caption<>'���������� ��������') {
			$warning.="<font color=red>��������������: ��� ������ ������������� ����������� ���� \"$base_fields_text_name[$id]\" (id:$id). ������� \"������\" � ����� ���� ����.</font><br>";
		}
	}
	foreach($base_fields_id as $key2=>$id2) {
		if($id<>$id2) {
			//������: ���������� ����� �����
			if(strtoupper($base_fields_text_name[$id])==strtoupper(trim($base_fields_text_name[$id2]))) {
				$error.="<font color=red>������: ����������� ��� ���� \"$base_fields_text_name[$id]\" (id:$id)</font><br>";
			}
			//������: ���������� ������� ����� �����
			if(strtoupper($base_fields_code_name[$id])==strtoupper(trim($base_fields_code_name[$id2]))) {
				$error.="<font color=red>������: ����������� ������� ��� ���� \"$base_fields_code_name[$id]\" (id:$id)</font><br>";
			}
			//������: ���������� ����������� �����
			if($base_fields_std_name[$id]<>'' and $base_fields_std_name[$id]==$base_fields_std_name[$id2]) {
				$error.="<font color=red>������: ����������� ����������� ���� \"$base_fields_std_name[$id]\" (id:$id)</font><br>";
			}
			//
}	}	}
//������: ������ �������
if($ffcount==0) {
	$error.="<font color=red>������: ������ �������</font><br>";
}			
//������: �������� ������� ��� ���� �������
if($std_field_phone=='no' and isset($robot_need)) {
	$error.="<font color=red>������: ��� ������ ��������� �������� ������� ��� �� ��� ���� \"�������\"</font><br>";
}
//��������������: �� ������� ���� "�������"
if($std_field_phone=='no' and $load_caption<>'���������� ��������') {
	$warning.="<font color=red>��������������: �� ������ ���� ������� (PHONE).</font><br>";
}
//����� � �������
if(isset($new_field)) {
	
	foreach ($new_field as $id => $fuck) {
		if(isset($base_fields_quoted[$id])) {$new_quoted[$id]=$id;} //������ ����� ����
		if(isset($base_fields_idx[$id])) {$new_idx[$id]=$id;} //������ ����� ��������
	}
}
//��������������: ��������� ����������� ���� 
if(isset($new_quoted)) {
	if($load_caption<>'���������� ��������') $warning.="<font color=red>��������������: ��������� ����������� ����. ���� ���������� ����������, �� ������ ����� �������������, � �����, ������� ��������� ����� �� �������� �������� ����������� � ��������� �������� ������.</font><br>";
	else $src_quote_broken='y';
}
//==================================================================================================
//���� ���� ������ ��� ��������������, �������� ��������
if($error<>'') {
	echo $error;
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='���������';
	parent.admBottomFrame.frm_preview.load_caption.value='���������';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='none';
	parent.admBottomFrame.document.getElementById('load_status').innerHTML='".$error."';</script>";	
	exit();
}
if($warning<>'') {
	echo $warning;
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='���������� ��������';
	parent.admBottomFrame.frm_preview.load_caption.value='���������� ��������';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='';
	parent.admBottomFrame.document.getElementById('load_status').innerHTML='".$warning."';</script>";
	exit();
}
//================================================================================================
//��������� ����� ����������� � ���� ������
if (!$fp=fopen($uploaded_file,"r") or !$fp_err=fopen($path_to_out."load_errors.csv","w")) {
	echo "<font color=red>������ �������� �����!</font><br>";
	echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=red><b>������ �������� �����!</b>';</script>";
	exit();
}

//��������� ������ � ������� ��������
$ins=OCIParse($c,"insert into STC_LOAD_HISTORY (ID,PROJECT_ID,STATUS,START_DATE,FILE_NAME,FILE_SIZE_BYTES) 
values (SEQ_STC_LOAD_HIST_ID.Nextval,'".$project_id."','�����������...',sysdate,'".$file_name."','".$file_size."')
returning id into :load_hist_id");
OCIBindByName($ins,":load_hist_id",$load_id,256);
OCIExecute($ins,OCI_DEFAULT);
	
$_SESSION['adm']['load_id']=$load_id;

OCICommit($c);
//


//==================================================================================================	
//������
//������ ����������
$load_status='������';
$load_rows_count=0;
$found_phones_count=0;
$load_phones_count=0;
$wrong_phones_count=0;
$dublicate_phones_count=0;
$error_count=0;
$read_row_count=0;
$file_fields_count=0;
$null_row_count=0;
$null_fields_count=0;
$dublicate_count=0;
if(isset($robot_need)) $allow=''; else $allow='y';
$allow_rows_count=0;
$allow_phones_count=0;
$nlchar=array(chr(10),chr(13));
$quoted_fields=array();
$idx_fields=array();

//��������� ������� �������� ������������ �����
if(isset($uniq_term)) {
	$upd=OCIParse($c,"update STC_PROJECTS set uniq_term='".$uniq_term."' where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
}
//���������������� ������, ���� ���������� �����	
if(isset($src_quote_broken)) {
	$upd=OCIParse($c,"update STC_PROJECTS set SRC_QUOTE_BROKEN='yes',QST_QUOTE_BROKEN='yes',QST_STAT_BROKEN='yes', status='�������������' where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
} 

//��������� ����
//��������� ������������� � �������� �������� ���������� � ������������ ������������ �����
$q=OCIParse($c,"select uniq,must,quoted,idx,std_field_name from STC_FIELDS t where id=:id and project_id='".$project_id."'");

//��������� ���������� � ��� �� ����� ������������ �����
$upd=OCIParse($c,"update STC_FIELDS t set ord=:ord, last_file_field_name=:last_file_field_name
where id=:id and project_id='".$project_id."'");

//��������� ����� ����
$ins=OCIParse($c,"insert into STC_FIELDS (id,project_id,text_name,code_name,ord,src_type_id,std_field_name,uniq,must,quoted,idx,last_file_field_name)
values (:id,'".$project_id."',:text_name,:code_name,:ord,'1',:std_name,:uniq,:must,:quoted,:idx,:last_file_field_name)");

$must_field_count=0; //������� ���������� ������������ �����
$uniq_field_count=0; //������� ���������� ���������� �����
$phone_field_id=''; //������������� ���� ������� � ��, ���� �����, ������ ������ ���� ���
$phone_field_num=''; //����� ���� ������� � �����

//��������� ���� � ��
foreach($base_fields_id as $key=>$id) {
	OCIBindByName($q,":id",$id);
	OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		//���� ���� ����������, �� �������� �������� ������������ � ��������������
		$base_fields_uniq[$id]=OCIResult($q,"UNIQ");
		$base_fields_must[$id]=OCIResult($q,"MUST");
		$base_fields_quoted[$id]=OCIResult($q,"QUOTED");
		$base_fields_idx[$id]=OCIResult($q,"IDX");
		$base_fields_std_name[$id]=OCIResult($q,"STD_FIELD_NAME");
		//� ���������� ����������
		OCIBindByName($upd,":id",$id);
		OCIBindByName($upd,":ord",$key);
		OCIBindByName($upd,":last_file_field_name",$file_fields_name[$key]);
		OCIExecute($upd,OCI_DEFAULT);
		//������� ������ ����, ����� ��� �������� ����������� ����� (� ����� ��������)
		$file_fields_new[$key]='no';
	} 
	else {
		//���� ������ ���� �� ���������, �� ��������� ���
		OCIBindByName($ins,":id",$id);
		OCIBindByName($ins,":ord",$key);
		OCIBindByName($ins,":text_name",$base_fields_text_name[$id]);
		OCIBindByName($ins,":code_name",$base_fields_code_name[$id]);
		OCIBindByName($ins,":last_file_field_name",$file_fields_name[$key]);
		OCIBindByName($ins,":std_name",$base_fields_std_name[$id]);
		OCIBindByName($ins,":uniq",$base_fields_uniq[$id]);
		OCIBindByName($ins,":must",$base_fields_must[$id]); 
		OCIBindByName($ins,":quoted",$base_fields_quoted[$id]); 
		OCIBindByName($ins,":idx",$base_fields_idx[$id]); 
		OCIExecute($ins,OCI_DEFAULT);
		//������� ������ ����, ����� ��� �������� ����������� ����� (� ����� ��������)
		$file_fields_new[$key]='yes';
	}
	//������� ���-�� ���������� ������������ � ����������� �����
	$base_fields_uniq[$id]<>''?$uniq_field_count++:NULL;
	$base_fields_must[$id]<>''?$must_field_count++:NULL;
	//����� � ID ���� �������
	if($base_fields_std_name[$id]=='PHONE' and $std_field_phone=='yes') {
		$phone_field_id=$id;
		$phone_field_num=$file_fields_num[$key];
	}
	//�����. �������� ������ ����������� �����
	$base_fields_quoted[$id]<>''?$quoted_fields[$id]=$id:NULL;	
	//�������. �������� ������ ������������� �����
	$base_fields_idx[$id]<>''?$idx_fields[$id]=$id:NULL;	
}
//���������� �������� ������� (���-�� �����)
OCIExecute(OCIParse($c, "update STC_PROJECTS set (num_src_fields,num_phone_fields)=
(select count(*), count(decode(std_field_name,'PHONE',1,NULL)) from STC_FIELDS where project_id=".$project_id.")
where id=".$project_id),OCI_DEFAULT);
//======================================================================================================================================
OCICommit($c);
//

//echo "<font color=red><br>";
	
//�������� ������ �������� �� ������������
if($uniq_field_count>0) {
	if($uniq_term=='���') $uniq_sql="select case when count(*)=0 then 'ok' else 'error' end res".chr(13).chr(10);
	if($uniq_term=='�') $uniq_sql="select case when nvl(max(count(*)),0)<'".$uniq_field_count."' then 'ok' else 'error' end res".chr(13).chr(10);
	$uniq_sql.="from STC_FIELD_VALUES t where ".chr(13).chr(10);
	//where project_id='".$project_id."'
	//and (".chr(13).chr(10);
	$i=0; foreach($base_fields_uniq as $key=>$val) {
		if($val<>'') { 
			if($i>0) $uniq_sql.="or".chr(13).chr(10);
			$uniq_sql.="(t.project_id='".$project_id."' and t.field_id='".$key."' and t.text_value=:var".$key.")".chr(13).chr(10);
			$i++;
	}	}
	//$uniq_sql.=")".chr(13).chr(10);
	if($uniq_term=='�') $uniq_sql.="group by t.base_id".chr(13).chr(10);
	$q_uniq=OCIParse($c,$uniq_sql);
}
//

//������ �������� ��������� �� ������������
$q_uniq_phone=OCIParse($c,"select phone from STC_PHONES t
where t.project_id='".$project_id."' and t.phone=:phone");
	
//������ ���������� ������
$ins_row=OCIParse($c,"insert into STC_BASE (id,Project_Id,load_hist_id,allow,src_quote_id,utc_msk) 
values (SEQ_STC_BASE_ID.nextval,'".$project_id."','".$load_id."','".$allow."',:quote_id,:utc_msk)
returning id into :base_id");
	
//������ ���������� ��������
$ins_val=OCIParse($c,"insert into STC_FIELD_VALUES (project_id,base_id,field_id,text_value,ord)
values ('".$project_id."',:base_id,:field_id,:value,0)");	
	
//������ ���������� ���������
$q_ins_phone=OCIParse($c,"insert into STC_PHONES (base_id,project_id,phone,base_field_id,ord,allow,load_hist_id) 
values (:base_id,'".$project_id."',:phone,:base_field_id,:ord,'".$allow."','".$load_id."')");

//�������. ������ �� �������� � ���������� �������� �� ����������� � �������������
if(count($idx_fields)>0 or count($quoted_fields)>0) {
	$q_idx_check=OCIParse($c,"select id, case when i.src_idx_quote-i.src_idx_norm<=0 then 'y' else null end idx_lock from STC_SRC_INDEXES i where field_id=:field_id and value=:value");
	$q_ins_idx=OCIParse($c,"insert into STC_SRC_INDEXES (id,project_id,field_id,Value) values (SEQ_STC_INDEX_ID.nextval,".$project_id.",:field_id,:value) returning id into :index_id");
	if($allow=="y") $q_upd_idx=OCIParse($c,"update STC_SRC_INDEXES set src_idx_new=src_idx_new+1 where id=:index_id");
}
//
//�����. ������� �� �������� � ���������� ���� �� �����������
if(count($quoted_fields)>0 and !isset($quote_broken)) {
	$sql='';
	$i=0; foreach($quoted_fields as $fuck) {$i++; $i>1?$sql.=",":NULL; $sql.=":i".$i;}
	$q_quote_check=OCIParse($c,"select quote_id from (
select quote_id, count(*) cnt
from STC_SRC_QUOTE_INDEXES
where project_id=".$project_id." and index_id in (".$sql.")
group by quote_id
)
where cnt=".count($quoted_fields));
	$q_ins_quote=OCIParse($c,"insert into STC_SRC_QUOTES (id,project_id,field_count) values (SEQ_STC_QUOTE_ID.nextval,".$project_id.",".count($quoted_fields).") returning id into :quote_id");
	$q_ins_quote_idx=OCIParse($c,"insert into STC_SRC_QUOTE_INDEXES (project_id,quote_id,index_id) values (".$project_id.",:quote_id,:index_id)");
	if($allow=="y") $q_upd_quote=OCIParse($c,"update STC_SRC_QUOTES set src_new=src_new+1 where id=:quote_id");
}
//����� � �������. ������ �� �������� ������
if((count($quoted_fields)>0 or count($idx_fields)>0) and !isset($quote_broken)) {
	$q_upd_base=OCIParse($c,"update STC_BASE set src_quote_id=:quote_id, lock_by_index=:lock_by_index where id=:base_id");	
}
//=====================================================================================
$i=0; while($str=fgetcsv($fp,1024*1024,";",'"')) {$i++; //��� �������� ����
	$UTC_MSK='';
	if($i==1) { //������ ������ (��������� �����)
		fput_err($fp_err,'������','������','��.������',implode('";"',$str));
		$file_fields_count=count($str);
continue;
	}
	if($i/500==round($i/500)) echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=black><b>���� ��������...</b></font> �����. ���������: $read_row_count; ���������: $load_rows_count; ������: $null_row_count; ����������: $dublicate_count; ������� ���������: $found_phones_count; ��������� ���������: $load_phones_count';</script>";
	$read_row_count++;
	//�������� �������������� � ������������
	if($must_field_count>0 or $uniq_field_count>0) {
		$ii=0; $must_err=''; 
		foreach($file_fields_num as $key=>$ffnum) { //key - ������, ffnum - ����� ������� � �����
			//��������������
			$must_err='';
			if($must_field_count>0) {			
				if($base_fields_must[$base_fields_id[$key]]<>'') {
					if(!isset($str[$ffnum]) or trim($str[$ffnum])=='') { //������������� ����
						$must_err=$base_fields_text_name[$base_fields_id[$key]];
		break;
			}	}	}
			//
			//������������ (������ ����������)
			if($uniq_field_count>0) {
				if($base_fields_uniq[$base_fields_id[$key]]<>'') {
					$bindvarname[$ii]=":var".$base_fields_id[$key];
					isset($str[$ffnum])?$bindvalue[$ii]=trim($str[$ffnum]):$bindvalue[$ii]=''; //������������� ����
					OCIBindByName($q_uniq,$bindvarname[$ii],$bindvalue[$ii]);
					$ii++; //����������� OCBindByName ���������� �� ������ ���������� �� ������� ���������� �������, ������� ������ ������ �� ��������� $ii
		}	}	}
		//
	
		//�������������, ���� ������, �� ���������� ������
		if($must_err<>'') {
			$null_row_count++;
			echo "$i: ������ ����. ���� \"$must_err\"<br>";
			fput_err($fp_err,$i,"������ ����. ����:","\"\"$must_err\"\"",implode('";"',str_replace('"','""',$str)));
continue;			
		}
		//������������ (��������� ������ ��������)
		if($ii>0) {
			OCIExecute($q_uniq,OCI_DEFAULT);
			OCIFetch($q_uniq);
			if(OCIResult($q_uniq,"RES")=='error') {
				$dublicate_count++;
				echo "$i: �������� �� ����������� ����(��).<br>";
				fput_err($fp_err,$i,"�������� �� ����������� ����(��).",'',implode('";"',str_replace('"','""',$str)));
continue;			
	}	}	}
	//

	//�������� ������ �������� ��� ����������
	$f=0; $f_err=0;
	$ins_values_arr=array();
	foreach($file_fields_num as $key=>$ffnum) { //���� ����� ���������� � ����
		if($ffnum=='') { //���� ��� ������� � ����� ������� � �����
			unset($file_fields_num[$key]); 
	continue;
		} 
		$val=trim($str[$ffnum]);
		if($val=='') { //������ ������ � �����
			$null_fields_count++; 
	continue;
		} 
		//�������� ���������� ������
		//�������� ���������� �������� �����
		if($base_fields_std_name[$base_fields_id[$key]]=='UTC_MSK') {
			//if(!preg_match("/^[+-]{0,1}\d{1,2}(,\d{1,2}){0,1}$/",$val)) {
			$UTC_MSK=str_replace(",",".",$val);
			if(!is_numeric($UTC_MSK) or $UTC_MSK<-15 or $UTC_MSK>11) {
				echo "$i: �� ������ ������� ����<br>";
				$UTC_MSK='';
				fput_err($fp_err,$i,"�� ������ ������� ����",$val,implode('";"',str_replace('"','""',$str)));
				$f_err++;			
	break;
			}
			else {$val=str_replace(".",",",$val); $UTC_MSK=$val;}	
				
		}
		//���� ��� � �������, ��������� �������� � ������
		$ins_values_arr[$base_fields_id[$key]]=$val;
		$f++;
	}
	if($f_err>0) {
		$error_count++;
continue;	
	}	
	//������ ������
	if($f==0) {
		$null_row_count++; 
		echo "$i: ������ ������<br>";
		fput_err($fp_err,$i,"������ ������",'',implode('";"',str_replace('"','""',$str)));
		$f_err++;
continue;
	}
	//���� �������. �������� ������ � ���������� ��� ����������
	if($phone_field_id<>'') { //���� ���� ���� �������
		//����� � ����������� �������
		$p=0; $d=0; 
		$ins_phones_arr=array();
		$phones=phones_conv(str_replace($nlchar,';',$str[$phone_field_num]));
		foreach($phones as $phone) {
			//�� ������ �������
			if($phone['err']=='y') {
				echo "$i: �� ������ �������: \"".$phone['phone']."\"<br>";
				fput_err($fp_err,$i,"�� ������ �������:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
				$wrong_phones_count++;
		continue;
			}
			$p++;
			$found_phones_count++;
			//�������� �� ������������
			if($base_fields_uniq[$phone_field_id]<>'') {
				//���� � ������� �������
				$d1=0;
				foreach($ins_phones_arr as $phone1) {
					if($phone1==$phone['phone']) {
						echo "$i: ����. �������: \"".$phone['phone']."\"<br>";
						fput_err($fp_err,$i,"����. ������� � ������:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
						$d++;
						$d1++;
						$dublicate_phones_count++;
					break;					
				}	}
		if($d1>0) continue;		
				//� � ��
				OCIBindByName($q_uniq_phone,":phone",$phone['phone']);
				OCIExecute($q_uniq_phone,OCI_DEFAULT);
				if(OCIFetch($q_uniq_phone)) {
					echo "$i: ����. ������� � ��: \"".$phone['phone']."\"<br>";
					fput_err($fp_err,$i,"����. ������� � ��:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
					$d++;
					$dublicate_phones_count++;
		continue;		
			}	}			
			$ins_phones_arr[]=$phone['phone'];
		}
		//������������, ���� ��� ��������� �������� ����������
		if($p<>0 and $d==$p and $base_fields_uniq[$phone_field_id]<>'') {
			$dublicate_count++;
			$f_err++;
			echo "$i: �������� �� ���� �������<br>";
			fput_err($fp_err,$i,"����. �� ���� �������",'',implode('";"',str_replace('"','""',$str)));
continue;
		}
		//�������������� (���� �� ������� �� ������ ������)
		if($p==0 and $base_fields_must[$phone_field_id]<>'') {
			$null_row_count++;
			$f_err++;
			echo "$i: �� ������� ����� �������<br>";
			fput_err($fp_err,$i,"�� ������� ����� �������",'',implode('";"',str_replace('"','""',$str)));
continue;			
	}	}
	//����� ���� ��������
	//=========================================================================
	//���������� ������
	OCIBindByName($ins_row,":base_id",$base_id,256);
	OCIBindByName($ins_row,":quote_id",$quote_id);
	OCIBindByName($ins_row,":utc_msk",$UTC_MSK);
	OCIExecute($ins_row,OCI_DEFAULT);
	
	//���������� ��������
	$quote_index=array();
	$idx_lock='';
	$x=0;
	foreach($ins_values_arr as $field_id => $val) {
		OCIBindByName($ins_val,":base_id",$base_id);
		OCIBindByName($ins_val,":field_id",$field_id);
		OCIBindByName($ins_val,":value",$val);
		OCIExecute($ins_val,OCI_DEFAULT);
		//�������. �������� ������������� � ���������� ��������
		if(isset($idx_fields[$field_id]) or isset($quoted_fields[$field_id])) {
			OCIBindByName($q_idx_check,":field_id",$field_id);
			OCIBindByName($q_idx_check,":value",$val);
			OCIExecute($q_idx_check,OCI_DEFAULT);
			if(OCIFetch($q_idx_check)) { //���� ������ ���������� 
				$index_id=OCIResult($q_idx_check,"ID");
				//����������, ���� ��������� ����� (��������� ��������� � �����)
				//$idx_lock=OCIResult($q_idx_check,"IDX_LOCK");
				if(OCIResult($q_idx_check,"IDX_LOCK")=='y') $idx_lock='y';
				//� ��� ���� �����������, ���������� ID ������� �����
				if(isset($quoted_fields[$field_id])) {
					$x++; 
					$quote_index[$x]=$index_id;
				}
			}
			else { //���� �� ����������, �� ��������� ������
				OCIBindByName($q_ins_idx,":index_id",$index_id,256);
				OCIBindByName($q_ins_idx,":field_id",$field_id);
				OCIBindByName($q_ins_idx,":value",$val);
				OCIExecute($q_ins_idx,OCI_DEFAULT);	
				//���� ��� ���� �����������, ���������� ID ������ ������� �����
				if(isset($quoted_fields[$field_id])) {$x++; $quote_index[$x]=$index_id;}							
			}		
			//���������� ����������, ���������� �������� �� ����������� ����� (�������)
			if($allow=='y') {
				OCIBindByName($q_upd_idx,":index_id",$index_id);
				OCIExecute($q_upd_idx,OCI_DEFAULT);
			}
		}
	}
	//�����. �������� ������������� � ���������� ����
	if(!isset($quote_broken)) {
		$quote_id='';
		if(count($quoted_fields)>0) {
			foreach($quote_index as $x => $index_id) {
				OCIBindByName($q_quote_check,":i".$x,$quote_index[$x]);
			}
			OCIExecute($q_quote_check,OCI_DEFAULT);
			//���� ����� ����������
			if(OCIFetch($q_quote_check)) {
				$quote_id=OCIResult($q_quote_check,"QUOTE_ID");
			}
			else { //���� ����� �� ����������, �� ���������� �����
				$qst_quote_broken='y';
				OCIBindByName($q_ins_quote,":quote_id",$quote_id,256);
				OCIExecute($q_ins_quote,OCI_DEFAULT);
				//���������� �������� � �����
				foreach($quote_index as $index_id) {
					OCIBindByName($q_ins_quote_idx,":quote_id",$quote_id);
					OCIBindByName($q_ins_quote_idx,":index_id",$index_id);
					OCIExecute($q_ins_quote_idx,OCI_DEFAULT);
				}
			}
			//�������� ������ � ����� �� �������� ����� � ���������� ����������
			if($allow=="y") {
				OCIBindByName($q_upd_quote,":quote_id",$quote_id);
				OCIExecute($q_upd_quote,OCI_DEFAULT);
			}
		}
		//�������� ������ � ����� �� �������� ����� � ���������� ����������
		if($quote_id<>'' or $idx_lock=='y') {
			OCIBindByName($q_upd_base,":base_id",$base_id);
			OCIBindByName($q_upd_base,":quote_id",$quote_id);
			OCIBindByName($q_upd_base,":lock_by_index",$idx_lock);
			OCIExecute($q_upd_base,OCI_DEFAULT);			
		}
	}	
	
	//���������� ���������
	if($phone_field_id<>'') { //���� ���� ���� �������
		foreach($ins_phones_arr as $ord => $phone) {	
			OCIBindByName($q_ins_phone,":base_id",$base_id);
			OCIBindByName($q_ins_phone,":phone",$phone);
			OCIBindByName($q_ins_phone,":base_field_id",$phone_field_id);
			OCIBindByName($q_ins_phone,":ord",$ord);
			OCIExecute($q_ins_phone,OCI_DEFAULT);
			$load_phones_count++;
		}
	}

	$load_rows_count++; 	
	//������ ����� ��������� �������� �����
	if($load_rows_count>0 and round($load_rows_count/$commit_interval)==$load_rows_count/$commit_interval) {
		OCICommit($c);
		//������ ��������.
		$q_abort_load=OCIParse($c,"select abort_load from STC_LOAD_HISTORY where id=".$load_id);
		OCIExecute($q_abort_load,OCI_DEFAULT);
		OCIFetch($q_abort_load);
		if(OCIResult($q_abort_load,"ABORT_LOAD")<>'') {
			$load_status='��������';
break;
		}
	}
}

//����� ������������� � ������������� ��������� �����, ���� ���������� ��������
if(isset($qst_quote_broken)) { 
	//����� ���� ��������� ��������� ���������� ���� �� ��������
	OCIExecute(OCIParse($c,"begin stc_add_qst_quotes(".$project_id."); end;"));
	
	$info.="<font color=red>��������! ��������� ����� �����, �� �������� ��������� ��������</font><br>";
//	$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes' where id='".$project_id."'");
//	OCIExecute($upd,OCI_DEFAULT);
	echo $info;
/*	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";   */
}
OCICommit($c);

//�������� ����������� �����
$q=OCIParse($c,"select t.std_field_name, t.ok, t.wrong from STC_LI_STANDARD_SYNONYM t
where t.std_synonym=:synon");
$q_ins=OCIParse($c,"insert into STC_LI_STANDARD_SYNONYM (std_field_name, std_synonym, ok) values (:std_name,:synon,1)");
$q_upd=OCIParse($c,"update STC_LI_STANDARD_SYNONYM set std_field_name=:std_name,ok=:ok,wrong=:wrong where std_synonym=:synon and locked is null");
$q_del=OCIParse($c,"delete from STC_LI_STANDARD_SYNONYM where std_synonym=:synon and locked is null");

foreach($file_fields_num as $key=>$ffnum) {
	if(strlen($ffnum)<>'') {
		$std_name='';
		//�������� ��� ���� � ����� � ��������
		$ffsyn=strtoupper(str_replace(' ','',$file_fields_name[$key]));
		//���� ����� ������������� �������� >= 3
		if(strlen($ffsyn)>=3) {
			//���� ������� � ��
			OCIBindByName($q,":synon",$ffsyn);
			OCIExecute($q,OCI_DEFAULT);
			if(OCIFetch($q)) {
				$ok=OCIResult($q,"OK");
				$wrong=OCIResult($q,"WRONG");
				$std_name=OCIResult($q,"STD_FIELD_NAME");
			}
			//
			//1. ���� ���� � ����� ��������� ����������� ���� � ������ �������� ��� � ����
			if($base_fields_std_name[$base_fields_id[$key]]<>'' and $std_name=='') {
echo "1. � �����: ".$file_fields_name[$key]."; �������: ".$ffsyn."; ������ �����������: ".$std_name."; ����� �����������: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//����� �������� ���
				OCIBindByName($q_ins,":std_name",$base_fields_std_name[$base_fields_id[$key]]);
				OCIBindByName($q_ins,":synon",$ffsyn);
				OCIExecute($q_ins,OCI_DEFAULT);
			}
			//2. ���� ����� ������� ����, � ��� ����������� ���� ��������� � ����� ����������� �����
			else if($std_name<>'' and $std_name==$base_fields_std_name[$base_fields_id[$key]]) {
echo "2. � �����: ".$file_fields_name[$key]."; �������: ".$ffsyn."; ������ �����������: ".$std_name."; ����� �����������: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//+1 � ��������� ��������
				$ok++;
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);
				OCIExecute($q_upd,OCI_DEFAULT);				
			}
			//3. ���� ����� ������� ����, � ��� ����������� ���� �� ��������� � ����� ����������� ����� ��� ��� ������ � ���-�� ���������� ������������� �������� < 1
			else if($std_name<>'' and $std_name<>$base_fields_std_name[$base_fields_id[$key]] and $wrong < 1) {
echo "3. � �����: ".$file_fields_name[$key]."; �������: ".$ffsyn."; ������ �����������: ".$std_name."; ����� �����������: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//+1 � �������� ������������� ��������
				$wrong++;
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);
				OCIExecute($q_upd,OCI_DEFAULT);
			}
			
			//4. ���� ����� ������� ����, � ��� ����������� ���� ������ � ���-�� ���������� ������������� �������� > 0
			else if($std_name<>'' and $base_fields_std_name[$base_fields_id[$key]]=='' and $wrong > 0) {
echo "4. � �����: ".$file_fields_name[$key]."; �������: ".$ffsyn."; ������ �����������: ".$std_name."; ����� �����������: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//������� �������� :)
				OCIBindByName($q_del,":synon",$ffsyn);
				OCIExecute($q_del,OCI_DEFAULT);
			}
			
			//5. ���� ����� ������� ����, � ��� ����������� ���� �� ��������� � ����� ����������� ����� � ���-�� ���������� ������������� �������� > 0
			else if ($base_fields_std_name[$base_fields_id[$key]]<>'') {
echo "5. � �����: ".$file_fields_name[$key]."; �������: ".$ffsyn."; ������ �����������: ".$std_name."; ����� �����������: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//������������� ������� � ���������� ��������
				$ok=1;
				$wrong=0;
				$std_name=$base_fields_std_name[$base_fields_id[$key]];
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);	
				OCIExecute($q_upd,OCI_DEFAULT);			
			}			
			//
		}
	}
}
OCICommit($c);

//echo "</font>";
	
//��������� ���������� ��������
if($allow=='y') {
	$allow_rows_count=$load_rows_count;
	$allow_phones_count=$load_phones_count;
	OCIExecute(OCIParse($c,"update STC_PROJECTS p set p.stat_new=p.stat_new+".$allow_rows_count." where p.id=".$project_id));
}
if($load_status=='��������') $file_row_count=''; else $file_row_count=$read_row_count;
$upd=OCIParse($c,"update STC_LOAD_HISTORY set end_date=sysdate, load_rows='".$load_rows_count."', allow_rows='".$allow_rows_count."', errors='".$error_count."', file_row_count='".$file_row_count."',file_fields='".$file_fields_count."', null_rows='".$null_row_count."', dublicates='".$dublicate_count."',found_phones='".$found_phones_count."',load_phones='".$load_phones_count."',allow_phones='".$allow_phones_count."',status='".$load_status."'
where id='".$load_id."' and project_id='".$project_id."' returning round((end_date-start_date)*24*60*60) into :dursec");
OCIBindByName($upd,":dursec",$dursec,128);
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);

//��������� ���������� �������� ��������
$upd=OCIParse($c,"update STC_SYS_STATISTIC set avg_load_speed=(
select sum(t.file_size_bytes)/sum((end_date-start_date)*24*60*60) bytes_sec from STC_LOAD_HISTORY t
where status='������' and end_date-start_date>0 and t.file_size_bytes>0 and t.start_date>to_date('25.07.2015','DD.MM.YYYY')
)");
OCIExecute($upd,OCI_DEFAULT);	
OCICommit($c);

//��������� �����
fclose($fp);
fclose($fp_err);
unlink($uploaded_file);
echo "<script>
parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=green><b>�������� ��������� �� $dursec ��� (".round($dursec/60)." ���). </b></font>�����. ���������: $read_row_count; ���������: $load_rows_count; ������: $null_row_count; ����������: $dublicate_count; ������� ���������: $found_phones_count; ��������� ���������: $load_phones_count';
parent.admBottomFrame.frm_preview.load_caption.value='';
parent.admBottomFrame.frm_preview.cancel_load.style.display='none';</script>";
echo "<hr>";
echo "������������: $dursec ��� (".round($dursec/60)." ���)<br>";
echo "��������� �����: $load_rows_count<br>";
echo "�������� �����: $allow_rows_count<br>";
echo "������� ���������: $found_phones_count<br>";
echo "��������� ���������: $load_phones_count<br>";
echo "�� ������ ���������: $wrong_phones_count<br>";
echo "����. ���������: $dublicate_phones_count<br>";
echo "�������� ���������: $allow_phones_count<br>";
echo "��������� ������ �����: $null_row_count<br>";
echo "��������� ����������: $dublicate_count<br>";
echo "��������� ������: $error_count<br>";

echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";


//�������
function fput_err($fp_err,$rownum,$err,$data,$src_row) {
	$row='"'.$rownum.'";"'.$err.'";"'.$data.'";"'.$src_row.'"'.chr(13).chr(10);
	fputs($fp_err,$row,1024*1024);

}

function my_error_handler($code, $msg, $file, $line) {
	global $load_id;
	include("../../conf/starcall_conf/conn_string.cfg.php");
	$upd=OCIParse($c,"update STC_LOAD_HISTORY set end_date=sysdate, status='������'
where id='".$load_id."'"); OCIExecute($upd,OCI_DEFAULT); OCICommit($c);
	echo "<font color=red><hr>������: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=red>������: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='���������';
	parent.admBottomFrame.frm_preview.load_caption.value='';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='none';
	</script>";
	exit();
}
?>
