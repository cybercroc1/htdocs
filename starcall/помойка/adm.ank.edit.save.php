<?php	
include("../../starcall_conf/session.cfg.php");
include("../../starcall_conf/conn_string.cfg.php");
set_error_handler ("my_error_handler");
extract($_POST);

$error='';
$warning='';
if($frm_submit=='save' or $frm_submit=='continue') {
	echo "���������� ������<hr>";
	//������
	//$error='������';
	//��������������: �������� ��������
	if((isset($del_grp) or isset($del_obj)) and $frm_submit<>'continue') {
		$warning.='<font color=red>��������������: ����� ������� ���� ��� ��������� �������� ������.</font><br>';
	}
	//
	
	//������ ����������� ���� $quoted
	$new_quoted_ids=array();
	$new_quoted=array();
	if(isset($quoted)) {
		$i=0; foreach($quoted as $idx => $fuck) {
			if(!isset($del_obj[$obj_id[$idx]])) {$i++; $new_quoted_ids[$i]=$obj_id[$idx]; $new_quoted[$obj_id[$idx]]=$obj_id[$idx];}		
	}}	
	//������ ������ ����������� ��������
	$old_quoted_ids=array();
	$old_quoted=array();
	$q=OCIParse($c,"select o.id from STC_OBJECTS o
where o.project_id=".$project_id." and o.deleted is null
and o.quote_num is not null and o.deleted is null
order by num");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {$i++;
		$old_quoted_ids[$i]=OCIResult($q,"ID");
		$old_quoted[OCIResult($q,"ID")]=OCIResult($q,"ID");	
		if(!isset($new_quoted[OCIResult($q,"ID")])) {$del_quoted[OCIResult($q,"ID")]=OCIResult($q,"ID");	$changed_quote='y';}
	}
	foreach ($new_quoted_ids as $i => $id) {
		if(count($new_quoted_ids)==count($old_quoted_ids) and $new_quoted_ids[$i]<>$old_quoted_ids[$i]) $changed_quote='y';
		if(!isset($old_quoted[$id])) {if($id<>'new') {$add_quoted[$id]=$id;} $changed_quote='y';} //������ ����������� ����������� ����� ��� ����������
	}	

	//��������������: ���������� �����.
	if(isset($changed_quote) and $frm_submit<>'continue') {
		$warning.='<font color=red>��������������: �������� ���������� ��� ������� ����������� ��������. ���� ���������� ����������, �� ������ ����� �������������, � ����� �� �������� ����������� � ��������� �������� ������.</font><br>';
	}
	//

	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='save';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='���������';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='none';
		</script>";	
		exit();
	}
	if($warning<>'') {
		echo $warning;
		echo "<script>
		parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='".$warning."';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='continue';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='����������';
		parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.disabled=false;
		parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='';
		</script>";	
		exit();
	}
	
	//����������
	$upd_grp=OCIParse($c,"update STC_OBJECT_GROUP set page_num=:page, name=:name, quest_ord_type=:ord_type,num=:num,num_on_page=:g_on_p
					where id=:grp_id and project_id='".$project_id."'");
					
	$ins_grp=OCIParse($c,"insert into STC_OBJECT_GROUP (id, project_id, page_num, name,quest_ord_type,num,num_on_page)
					values(SEQ_STC_OBJECT_GROUP_ID.nextval,'".$project_id."',:page,:name,:ord_type,:num,:g_on_p) returning id into :grp_id");

	$upd_obj=OCIParse($c,"update STC_OBJECTS set obj_type_id=nvl(:type_id,obj_type_id), group_id=:group_id, message=:message, answ_ord_type=:ord_type, num=:num, must=:must, quote_num=:quote_num ,obj_full_num=:full_num, quest_num=:quest_num, num_on_group=:o_on_g
					where id=:obj_id and project_id='".$project_id."'");
	$ins_obj=OCIParse($c,"insert into STC_OBJECTS (id, project_id, field_id, obj_type_id,group_id,message,answ_ord_type,num,must,quote_num,obj_full_num,quest_num,num_on_group)
					values(SEQ_STC_OBJECT_ID.nextval,'".$project_id."',:field_id,:type_id,:group_id,:message,:ord_type,:num,:must,:quote_num,:full_num,:quest_num,:o_on_g) 
					returning id into :id");

	$upd_field=OCIParse($c,"update STC_FIELDS set text_name=:text_name, code_name=:code_name, ord=:ord, quoted=:quoted, must=:must
					where id=(select field_id from STC_OBJECTS where id=:obj_id and project_id='".$project_id."')");
					
	$ins_field=OCIParse($c,"insert into STC_FIELDS (id, project_id, text_name, code_name, ord, src_type_id, must, quoted)
					values(SEQ_STC_FIELDS_ID.nextval,'".$project_id."',
					nvl(:text_name,'������-'||SEQ_STC_FIELDS_ID.nextval),nvl(:code_name,'Q'||SEQ_STC_FIELDS_ID.nextval),:ord,2, :must, :quoted) returning id into :field_id");

	$p=0;$g=0;$g_on_p=0;$o=0;$o_on_g=0;$q=0; //������ ��������, ������, �������, �������
	$current_grp_id=''; //������� ������ ��������
	$last_obj_type=''; //��� ���������� �������
	$nl_replace_ser=array(chr(10),chr(13));
	$nl_replace_rep=array(' ','');
	$quote_num=0;


	if(isset($del_grp)) { //�������� �����
		foreach($del_grp as $id) {
				
			//���� ������ ������ � ������, �� �� ������� ��, � ������ ��������
			$q1=OCIParse($c,"select num from STC_OBJECT_GROUP where project_id='".$project_id."' and id='".$id."'");
			OCIExecute($q1,OCI_DEFAULT);
			OCIFetch($q1);
			//��������� ��� ������� � ���������� ������ � ������� ��
			$q2=OCIParse($c,"select id from STC_OBJECT_GROUP where project_id='".$project_id."' and num=
			(select max(num) from STC_OBJECT_GROUP where project_id='".$project_id."' and num<'".OCIResult($q1,"NUM")."')"); //�������� ID ���������� ������
				
			OCIExecute($q2,OCI_DEFAULT);
			OCIFetch($q2);
			$upd=OCIParse($c,"update STC_OBJECTS set group_id='".OCIResult($q2,"ID")."'
			where group_id='".$id."' and project_id='".$project_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			$del=OCIParse($c,"delete from STC_OBJECT_GROUP where id='".$id."' and project_id='".$project_id."'");					
			OCIExecute($del,OCI_DEFAULT);
		}
	}	
	if(isset($del_obj) and count($del_obj)>0) { //�������� ��������
			//������� �������, ����� �������� 
			$del=OCIParse($c,"delete from STC_OBJECTS where id in (".implode(",",$del_obj).") and project_id=".$project_id." and obj_type_id not like 'q_%'");
			OCIExecute($del,OCI_DEFAULT);
			//������� ��������, ��� ���������
			$upd=OCIParse($c,"update STC_OBJECTS set deleted=sysdate where id in (".implode(",",$del_obj).") and project_id='".$project_id."' and obj_type_id like 'q_%'");
			OCIExecute($upd,OCI_DEFAULT);
			//���� ��������, ��� ���������
			$upd=OCIParse($c,"update STC_FIELDS set deleted=sysdate where id in (
			select field_id from STC_OBJECTS 
			where id in (".implode(",",$del_obj).") and project_id=".$project_id."
			)");
			OCIExecute($upd,OCI_DEFAULT);
			//������ ��������, ��� ���������
			$upd=OCIParse($c,"update STC_LIST_VALUES set deleted=sysdate where object_id in (".implode(",",$del_obj).") and project_id=".$project_id);
			OCIExecute($upd,OCI_DEFAULT);
	}

	$i=0; foreach($obj_id as $idx => $id) {$i++; 	//���� - ������ ������� �� �������� ��������������; ��������: ��� ������������ - ID �������; ��� �����: new
		
		if($p==0 and $type[$idx]=='obj' and $obj_type_id[$idx]<>'') {$p++; $g++; $g_on_p=1; $o_on_g=0; //���� ������ ������� ������ ������ (�� �������� � �� ������) � �� ������, �� ������� �������� � ������
			$def_name='';
			$def_ord_type='�� �������';
			OCIBindByName($ins_grp,":page",$p);
			OCIBindByName($ins_grp,":name",$def_name);
			OCIBindByName($ins_grp,":ord_type",$def_ord_type);
			OCIBindByName($ins_grp,":num",$g);
			OCIBindByName($ins_grp,":grp_id",$current_grp_id,128);
			OCIBindByName($ins_grp,":g_on_p",$g_on_p);
			OCIExecute($ins_grp,OCI_DEFAULT);			
		}
		
		if($type[$idx]=='page' or $type[$idx]=='group') {$g++; $g_on_p++; $o_on_g=0; //�������� � ������
			if($type[$idx]=='page' or $p==0) {$p++; $g_on_p=1;} //���� ������ ������ �� �������� ����� ��������, �� ��������� ��������

			if($id<>'new')	{ //���� ������ ����������, �� ��������� ��� � ���������� �����
				$current_grp_id=$id;
				OCIBindByName($upd_grp,":page",$p);
				OCIBindByName($upd_grp,":name",$grp_name[$idx]);
				OCIBindByName($upd_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_grp,":num",$g);
				OCIBindByName($upd_grp,":grp_id",$current_grp_id);
				OCIBindByName($upd_grp,":g_on_p",$g_on_p);
				OCIExecute($upd_grp,OCI_DEFAULT);
			}
			else { //���� �� ����������, �� ��������� �����

				!isset($grp_name[$idx])?$grp_name[$idx]='':NULL; 
				!isset($order_type[$idx])?$order_type[$idx]='�� �������':NULL;

				OCIBindByName($ins_grp,":page",$p);
				OCIBindByName($ins_grp,":name",$grp_name[$idx]);
				OCIBindByName($ins_grp,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_grp,":num",$g);
				OCIBindByName($ins_grp,":grp_id",$current_grp_id,128);
				OCIBindByName($ins_grp,":g_on_p",$g_on_p);
				OCIExecute($ins_grp,OCI_DEFAULT);
		}}
		//�������
		if($type[$idx]=='obj') {
			if($id=='new' and $obj_type_id[$idx]=='') { //������� ������ ����� ������� 
				continue;
			} 
			$o++;
			$o_on_g++;
			$last_obj_type=$obj_type_id[$idx];
			if(isset($quoted[$idx])) {$quote_num++; $quoted[$idx]=$quote_num;} else $quoted[$idx]='';
			if($id<>'new') { //��������� ������������
				$full_num=$p.".".$g_on_p.".".$o_on_g;
				$field_id='';
				!isset($obj_type_id[$idx])?$obj_type_id[$idx]='':NULL;
				!isset($order_type[$idx])?$order_type[$idx]='':NULL;
				!isset($must[$idx])?$must[$idx]='':NULL;
				
				if(substr($obj_type_id[$idx],0,2)<>'q_') $quoted[$idx]=''; //����� ��������� ������ ��� ��������� ��������
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //��� �������� �������� ���� � ��
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //������� �������� ������
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);
					OCIBindByName($upd_field,":text_name",$text_name[$idx]);
					OCIBindByName($upd_field,":code_name",$code_name[$idx]);
					OCIBindByName($upd_field,":must",$must[$idx]);
					OCIBindByName($upd_field,":quoted",$quoted[$idx]);
					OCIBindByName($upd_field,":ord",$o);
					OCIBindByName($upd_field,":obj_id",$id);
					OCIExecute($upd_field,OCI_DEFAULT);
				}
				else {$q_num='';}
				OCIBindByName($upd_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($upd_obj,":group_id",$current_grp_id);
				OCIBindByName($upd_obj,":message",$message[$idx]);
				OCIBindByName($upd_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($upd_obj,":num",$o);
				OCIBindByName($upd_obj,":must",$must[$idx]);
				OCIBindByName($upd_obj,":quote_num",$quoted[$idx]);
				OCIBindByName($upd_obj,":obj_id",$id);
				OCIBindByName($upd_obj,":full_num",$full_num);
				OCIBindByName($upd_obj,":quest_num",$q_num);
				OCIBindByName($upd_obj,":o_on_g",$o_on_g);				
				OCIExecute($upd_obj,OCI_DEFAULT);	
			}
			else { //��������� �����
				$full_num=$p.".".$g_on_p.".".$o_on_g;
				$field_id='';
				!isset($obj_type_id[$idx])?$obj_type_id[$idx]='':NULL;
				!isset($order_type[$idx])?$order_type[$idx]='�� �������':NULL;
				!isset($must[$idx])?$must[$idx]='':NULL;
				!isset($quoted[$idx])?$quoted[$idx]='':NULL;
				if(substr($obj_type_id[$idx],0,2)<>'q_') $quoted[$idx]=''; //����� ��������� ������ ��� ��������� ��������				
				if(substr($obj_type_id[$idx],0,2)=='q_') {$q++; $q_num=$q; //��� �������� �������� ���� � ��
					$text_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$text_name[$idx]); //������� �������� ������
					$code_name[$idx]=str_replace($nl_replace_ser,$nl_replace_rep,$code_name[$idx]);					
					OCIBindByName($ins_field,":text_name",$text_name[$idx]);
					OCIBindByName($ins_field,":code_name",$code_name[$idx]);
					OCIBindByName($ins_field,":ord",$o);
					OCIBindByName($ins_field,":must",$must[$idx]);
					OCIBindByName($ins_field,":quoted",$quoted[$idx]);					
					OCIBindByName($ins_field,":field_id",$field_id,128);
					OCIExecute($ins_field,OCI_DEFAULT);
				}
				else {$q_num='';}				
				OCIBindByName($ins_obj,":id",$id,256);
				OCIBindByName($ins_obj,":field_id",$field_id);
				OCIBindByName($ins_obj,":type_id",$obj_type_id[$idx]);
				OCIBindByName($ins_obj,":group_id",$current_grp_id);
				OCIBindByName($ins_obj,":message",$message[$idx]);
				OCIBindByName($ins_obj,":ord_type",$order_type[$idx]);
				OCIBindByName($ins_obj,":must",$must[$idx]);
				OCIBindByName($ins_obj,":quote_num",$quoted[$idx]);
				OCIBindByName($ins_obj,":num",$o);
				OCIBindByName($ins_obj,":full_num",$full_num);
				OCIBindByName($ins_obj,":quest_num",$q_num);
				OCIBindByName($ins_obj,":o_on_g",$o_on_g);
				OCIExecute($ins_obj,OCI_DEFAULT);
				if($quoted[$idx]<>'') $add_quoted[$id]=$id;				
	}}}
	//���� ��������� ������ �� ����� ������, �� ��������� �������� �����
	if(substr($last_obj_type,0,4)<>'end_') {
		$field_id='';
		$obj_type_id='end_norm';
		$message='';
		$order_type='';
		$must='';
		$o++;
		$o_on_g++;
		$full_num=$p.".".$g_on_p.".".$o_on_g;
		$q_num='';
		OCIBindByName($ins_obj,":field_id",$field_id);
		OCIBindByName($ins_obj,":type_id",$obj_type_id);
		OCIBindByName($ins_obj,":group_id",$current_grp_id);
		OCIBindByName($ins_obj,":message",$message);
		OCIBindByName($ins_obj,":ord_type",$order_type);
		OCIBindByName($ins_obj,":must",$must);
		OCIBindByName($ins_obj,":num",$o);
		OCIBindByName($ins_obj,":full_num",$full_num);
		OCIBindByName($ins_obj,":quest_num",$q_num);
		OCIBindByName($ins_obj,":o_on_g",$o_on_g);
		OCIExecute($ins_obj,OCI_DEFAULT);				
	}
	
	//�����. ����������� ����������� ����������� ����
	if(isset($add_quoted)) {
	//echo implode(",",$add_quoted);
		OCIExecute(OCIParse($c,"insert into STC_QST_INDEXES (id,project_id,field_id,object_id,value)
select SEQ_STC_INDEX_ID.nextval, a.* from 
(select distinct v.project_id, o.field_id, v.object_id, quote_key from STC_LIST_VALUES v, STC_OBJECTS o
where v.project_id=".$project_id." and v.object_id in (".implode(",",$add_quoted).") and v.deleted is null
and o.id=v.object_id
minus
select i.project_id, i.field_id,i.object_id,i.value from STC_QST_INDEXES i
where i.project_id=".$project_id." and i.object_id in (".implode(",",$add_quoted).")
) a"),OCI_DEFAULT);
		OCICommit($c);
		echo "��������� ������� ����<hr>";		
	}
	//�����. ������� ������� ��� ���������� ����
	if(isset($del_quoted)) {
		OCIExecute(OCIParse($c,"delete from STC_QST_QUOTES q where q.project_id=".$project_id." 
		and index_id in (select id from STC_QST_INDEXES where project_id=".$project_id." and object_id in (".implode(",",$del_quoted)."))"));
		OCIExecute(OCIParse($c,"delete from STC_QST_INDEXES where project_id=".$project_id." and object_id in (".implode(",",$del_quoted).")"));
		OCICommit($c);
		echo "������� ������� ��������� ����<hr>";	
	}
	
	//��������� ������, ���� ���������� ����������� ����
	if(isset($changed_quote)) {
		$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes', status='�������������' where id='".$project_id."'");
		OCIExecute($upd,OCI_DEFAULT);
	}	
	OCICommit($c);

	echo "<font color=green>���������</font><br>";
	echo "<script>
	parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='<font color=green>���������</font>';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='saved';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value='���������';
	parent.admBottomFrame.admAnkEditFirstFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.admAnkEditFirstFrame.location.reload();
	parent.admBottomFrame.admAnkEditSecondFrame.location.reload();
	</script>";
	if(isset($changed_quote)) echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>"; 
}

function my_error_handler($code, $msg, $file, $line) {
	global $c;
	OCIRollback($c);
	echo "<font color=red><br>������: ".$code."; ".$msg."; ".$file."; ".$line."<br></font>";
	echo "<script>parent.admBottomFrame.admAnkEditFirstFrame.document.getElementById('save_status').innerHTML='<font color=red>������: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>parent.admBottomFrame.admAnkEditFirstFrame.frm.save.disabled=false;
	parent.admBottomFrame.admAnkEditFirstFrame.frm.save.value=���������;
	parent.admBottomFrame.admAnkEditFirstFrame.frm.frm_submit.value='save';</script>";
	exit();
}
?>
