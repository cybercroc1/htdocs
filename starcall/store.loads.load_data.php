<?php include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">	
<iframe name=hidden_frame style="display:none"></iframe>
<script src="func.row_select.js"></script>
<script src="store.loads.load_data.js"></script>
<?php 
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form method=post name=frm_sel_file enctype=\"multipart/form-data\">";

//�����-�����. �����
echo "<table class=content_table><tr class=header_tr><td>";

echo " | ";
echo "<a href='store.loads.loads.php'>�������� ��������</a> | ";
//echo "<a href='store.loads.fields.php'>��������� ���. �����</a> | ";
echo "<a href='store.loads.load_data.php'><font size=4>�������� �� .CSV</font></a> | ";
echo "<font align=right><a href='help.store.loads.load_data.html' target='_blank'>�������</a></font>";
echo "<hr>";

include("starcall/conn_string.cfg.php");
include("starcall/path.cfg.php");

	echo "<font size=4>�������� � ���������</font><br>"; 

	echo "�������� ���� <input type=file name=new_file onchange=change()>
	<input type=submit name=preview disabled value='��������� ��������� �� �����'><br>";	

if (!isset($preview) and !isset($load)) {echo "<font size='-1'><b>���������� � ������������ �����:<br>
������ ������ �������� �������� �����
</b></font><br>";}
	
	//�������� �������� ��������
	$q=OCIParse($c,"select id cnt from STOR_LOAD_HISTORY t
where status='�����������...'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		$disabled='y';
	} 
	if(isset($disabled)) {
		echo "<font size=3 color=red>��������! ���� ��������. ��������� �������� ���������.</font><br>";
	}
	//

echo "<hr>";
echo "</form>";



if (isset($preview)) {
	$file_fields=array();
	
	echo "<form name=frm_preview method=post action='store.loads.load_data.load.php' target='logFrame'>";	

	//id �������� � ������ ��� ������
	$q=OCIParse($c, "select SEQ_STC_LOAD_HIST_ID.Nextval load_id, dbms_random.string('A',30) abort_pwd from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$_SESSION['store']['load_id']=OCIResult($q,"LOAD_ID");
	$abort_pwd=OCIResult($q,"ABORT_PWD");
	echo "<input type=hidden name=load_id value=".$_SESSION['store']['load_id'].">";
	echo "<input type=hidden name=abort_pwd value='".$abort_pwd."'>";

	/*//�������� �� �� ��� ������������ ���� � ������ base_fields
	$q=OCIParse($c,"select t.id,t.text_name,t.code_name,t.std_field_name,s.description,t.uniq,t.must,t.quoted,t.last_file_field_name,t.idx 
  	from STC_FIELDS t,STC_LI_STANDARD_FIELDS s
  	where s.name(+)=t.std_field_name
	and t.project_id='".$_SESSION['adm']['project']['id']."' and t.src_type_id='1'
	order by t.ord");
	OCIExecute($q, OCI_DEFAULT);
	$i=0; while(OCIFetch($q)) { //���� �� ���������� � ����
		$base_fields[$i]['id']=OCIResult($q,"ID");
		$base_fields[$i]['text_name']=OCIResult($q,"TEXT_NAME");
		$base_fields[$i]['code_name']=OCIResult($q,"CODE_NAME");
		$base_fields[$i]['last_file_field_name']=OCIResult($q,"LAST_FILE_FIELD_NAME");
		$base_fields[$i]['std_field_name']=OCIResult($q,"STD_FIELD_NAME");
		$base_fields[$i]['std_field_desc']=OCIResult($q,"DESCRIPTION");
		$base_fields[$i]['uniq']=OCIResult($q,"UNIQ");
		$base_fields[$i]['must']=OCIResult($q,"MUST");
		$base_fields[$i]['quoted']=OCIResult($q,"QUOTED");
		$base_fields[$i]['idx']=OCIResult($q,"IDX");
		$base_fields[$i]['new_field']='n';
		$i++;
	}
	if($i==0) $first_load='y';
	//*/
	
	//���������� �������� ��������
	$q=OCIParse($c,"select avg_load_speed from STC_SYS_STATISTIC t");
	OCIExecute($q); OCIFetch($q); $avg_load_speed=OCIResult($q,"AVG_LOAD_SPEED");

	//������ ����
	echo "����: <b>".$_FILES['new_file']["name"]."</b>; ������: <b>".$_FILES['new_file']["size"]." ����</b>; ";
	if($avg_load_speed<>0) echo "�����. ��. ��������: <b>".round($_FILES['new_file']["size"]/$avg_load_speed)."</b> ��� (<b>".round($_FILES['new_file']["size"]/$avg_load_speed/60)."</b> ���)<br>";


//�����-�����. �������
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";

	 
	if($_FILES["new_file"]["size"] > 1024*100*1024) {
		echo ("</font color=red>������ ����� ��������� 3 ���������!</font>"); exit();
	}
	else {
	 	if (!is_uploaded_file($_FILES['new_file']["tmp_name"])) {
			echo "<font color=red>������ �������� �����!</font>";
		}
		else {
			$fp=fopen($_FILES['new_file']["tmp_name"],"r");
			
			$str=fgetcsv($fp,1024*1024,";");
			//�������� �������� ����� �� ����� � ������ file_fields
			foreach ($str as $num => $val) { //���� ����� ���������� � ����
					/*if(trim($val)=='') { //���������� ���� � ������ ���������
						$file_fields[$j]['text_name']='';
						//$file_fields[$j]['num']='';
						$file_fields[$j]['auto_id_std']='';
					}
					else {*/
						$file_fields[$num]['text_name']=trim($val);
						//$file_fields[$j]['num']=$j;
						$file_fields[$num]['auto_id_std']='';
		}}//}
		fclose($fp);
		//
		
		//������ �� ����� ����������� ������������ ���� �� �������� (������ ����������� � 2-� ������)
		$q_std=OCIParse($c,"select t.field_id_std from STOR_FIELD_SYNONYM t where t.field_synonym=:ffsyn");
		
		//���� ���������� �� �������� ����� ����������� �����
		foreach ($file_fields as $ffnum => $ff) {
			if($ff['text_name']<>'') {
				$ffsyn=strtoupper(str_replace(' ','',$ff['text_name']));
				OCIBindByName($q_std,":ffsyn",$ffsyn);
				OCIExecute($q_std,OCI_DEFAULT);
				if(OCIFetch($q_std)) {$file_fields[$ffnum]['auto_id_std']=OCIResult($q_std,"FIELD_ID_STD");}
			}
		}
		
		//���������� ���� ��
		/*//���� ���� ����������� �� ����� ���� � ����� � �� ��� ���������� ��������� ����� ��� ����� �� ��������� ������������ ����, �� ��������� ���� ����� � ������������ � ��:
		foreach ($base_fields as $bfkey => $bf) {
			foreach ($file_fields as $ffkey => $ff) {
				if(
				strtoupper(trim($bf['text_name']))==strtoupper(trim($ff['text_name'])) 
				or (isset($bf['last_file_field_name']) and strtoupper(trim($bf['last_file_field_name']))==strtoupper(trim($ff['text_name'])))
				or ($bf['std_field_name']<>'' and $bf['std_field_name']==$ff['std_field_name'])
				) {
					$bf_temp=$file_fields[$bfkey];
					$file_fields[$bfkey]=$file_fields[$ffkey];
					$file_fields[$ffkey]=$bf_temp;
		}}}*/
		

		
		//����������� ����� �� ����� ����� ����� �� 
		//if(!isset($first_load)) {
		//	foreach($file_fields as $ffkey => $ff) {
		//		if($base_fields[$ffkey]['new_field']=='y') {
		//			$base_fields[$ffkey]['text_name']=$ff['text_name'];
		//		}
		//	}
		//}
		
		
		//��������� ������ �����
		$q=OCIParse($c,"select t.id_std,t.text_name from STOR_FIELDS t
order by t.text_name");
		OCIExecute($q);
		while(OCIFetch($q)) {
			$store_fields[OCIResult($q,'ID_STD')]=OCIResult($q,'TEXT_NAME');
		}
		//
		
		echo "<table id=tbl name=tbl class=white_table>";
		echo "<tr>
		<th width=20></th>
		<th colspan=2>� �����</th>
		<th width=540 colspan=5>� ����</th>
		<th rowspan=2>����.<br>";
		
		echo "<select name=uniq_term>
		<option value=�>�</option>
		<option value=���>���</option>
		</select>";
		
		echo "</th><th></th><th></th><th></th></tr>";
		echo "<tr><th></th>";
		echo "<th width=20>�</th>
		<th width=150>���</th>
		<th width=20>�</th>
		<th width=40>ID</th>
		<th width=150>���</th>
		<th width=80>������� ���</th>
		<th width=170>�����. ����</th>
		<th width=40>����.</th>
		<th width=40>����� / ����.����</th>
		<th width=40>������ / ����.����</th></tr>";
		
		//���������� ���� �� � ������ �������
		foreach($file_fields as $ffnum => $ff) {
			echo "<tr class=selectable_row onclick=click_row(this,'tog')>";
			echo "<th style='cursor:pointer' title='������� (������� ������)' onDblClick=del_file_field(this)><img src='png/del.png'></img></th>";
			echo "<td>".$ffnum."</td>";
			echo "<td><b>".trim($ff['text_name'])."</b>";
			echo "<input type=hidden name=file_fields_name['".$ffnum."'] value='".trim($file_fields[$ffnum]['text_name'])."'></th>";
			echo "<td>".($ffnum+1)."</td>";
			echo "<th></th>";
			echo "<td></td>";
			echo "<td></td>";
			echo "<td>";	
			
			//����� ����������� �������� ������������ ����
			/*$ffsyn=strtoupper(str_replace(' ','',$file_fields[$ffkey]['text_name']));
			OCIBindByName($q_std,":ffsyn",$ffsyn);
			OCIExecute($q_std);
			OCIFetch($q_std);*/
			//����������� ������ ������ ����������� �����
			echo "<select name='fields_id_std[".$ffnum."]'><option></option>";
			foreach($store_fields as $id_std => $text_name) {
				echo "<option value='".$id_std."'".($ff['auto_id_std']==$id_std?' selected':'').">".$text_name." (".$id_std.")</option>";
			}
			echo "</select>";
		
			echo "</td>";					
		}
		
		
	/*	foreach($base_fields as $key => $bf) {
			if($bf['new_field']=='y' and $file_fields[$key]['num']==='') continue; //���� ���� � �� �����, � ��� ���� � ����� ������, �� ������� ���
			echo "<tr class=selectable_row onclick=click_row(this,'tog')>";
			if($bf['new_field']=='y') echo "<th style='cursor:pointer' title='������� (������� ������)' onDblClick=del_field(this)><img src='png/del.png'></img></th>";
			else echo "<th style='cursor:pointer' title='������� (������� ������)' onDblClick=del_file_field(this)><img src='png/del.png'></img></th>";
			echo "<th style='cursor:pointer' title='�������� ������ �����. CTRL-�������� � ������������' onClick=plus_field(this)><img src='png/plus.png'></img></th>";

			echo "<td>".($file_fields[$key]['num']!==''?($file_fields[$key]['num']+1):NULL)."</td>
			<td style='cursor:s-resize' title='CTRL-������� ������ �������' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onMouseMove='return false'>
			<b>".trim($file_fields[$key]['text_name'])."</b>
			<input type=hidden name=file_fields_num[] value='".$file_fields[$key]['num']."'>
			<input type=hidden name=file_fields_name[] value='".trim($file_fields[$key]['text_name'])."'></th>";				
			
			echo "<td>".($key+1)."</td>
			<th style='cursor:s-resize' title='CTRL-������� ������ �������' onMouseDown='fMD(this)' onMouseUp='fMU(this)'><input type=hidden name=base_fields_id[] value='".$bf['id']."'>".$bf['id']."</th>";
			
			
			if($bf['new_field']=='y') { //����� ���� ��������� �������������
				echo "<td><input type=hidden name=new_field[".$bf['id']."]><input type=text style='width:100%' name=base_fields_text_name[".$bf['id']."] value='".$bf['text_name']."'></td>";
				echo "<td><input type=text style='width:100%' name=base_fields_code_name[".$bf['id']."] value='".$bf['code_name']."'></td>";
				echo "<td>";
				
				//����� ����������� �������� ������������ ����
				$ffsyn=strtoupper(str_replace(' ','',$file_fields[$key]['text_name']));
				OCIBindByName($q_std,":ffsyn",$ffsyn);
				OCIExecute($q_std);
				OCIFetch($q_std);
				//����������� ������ ������ ����������� �����
				echo "<select name='base_fields_std_name[".$bf['id']."]' onchange='ch_std_field(".$bf['id'].")'><option></option>";
				foreach($std_fields as $name => $description) {
					echo "<option value='".$name."'".($name==OCIResult($q_std,"STD_FIELD_NAME")?' selected':'').">".$description." (".$name.")</option>";
					//echo $nameOCIResult($q_std,"STD_FIELD_NAME")."<br>";
				}
				echo "</select>";
		
				echo "</td>";
				echo "<td><input type=checkbox name=base_fields_uniq[".$bf['id']."]".($bf['uniq']<>''?' checked':NULL)."></td>"; //����������
				echo "<td><input type=checkbox name=base_fields_must[".$bf['id']."]".($bf['must']<>''?' checked':NULL)."></td>"; //������������
				echo "<td><input type=checkbox name=base_fields_quoted[".$bf['id']."]".($bf['quoted']<>''?' checked':NULL)."></td>"; //�����������
				echo "<td><input type=checkbox name=base_fields_idx[".$bf['id']."]".($bf['idx']<>''?' checked':NULL)."></td>"; //�������������
				echo "<script>ch_std_field(".$bf['id'].");</script>";
			}
			if($bf['new_field']=='n') { //������ ���� ������ ����������, �������������� ���������
				echo "<td><input type=hidden name=base_fields_text_name[".$bf['id']."] value='".$bf['text_name']."'>".$bf['text_name']."</td>";
				echo "<td><input type=hidden name=base_fields_code_name[".$bf['id']."] value='".$bf['code_name']."'>".$bf['code_name']."</td>";
				echo "<td><input type=hidden name=base_fields_std_name[".$bf['id']."] value='".$bf['std_field_name']."'>".$bf['std_field_desc'].($bf['std_field_name']<>''?" (".$bf['std_field_name'].")":NULL)."</td>";
				//��������� �������� �� �������� ����������� (������ ��� �������� ������������ ����)
				echo "<td><input type=checkbox disabled".($bf['uniq']<>''?' checked':NULL).">".($bf['uniq']<>''?'<input type=hidden name=base_fields_uniq['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td><input type=checkbox disabled".($bf['must']<>''?' checked':NULL).">".($bf['must']<>''?'<input type=hidden name=base_fields_must['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td><input type=checkbox disabled".($bf['quoted']<>''?' checked':NULL).">".($bf['quoted']<>''?'<input type=hidden name=base_fields_quoted['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td><input type=checkbox disabled".($bf['idx']<>''?' checked':NULL).">".($bf['idx']<>''?'<input type=hidden name=base_fields_idx['.$bf['id'].'] value=on>':NULL)."</td>";
				
			}
			//echo "<td></td>"; //��� ������ ���� ����� ��� ����������� ����� � ��������� ����� � ������ IE
			echo "</tr>";
		}*/
		echo "</table>";
		move_uploaded_file($_FILES['new_file']["tmp_name"],$path_to_tmp.$_FILES['new_file']["name"]);
		echo "<input type=hidden name='uploaded_file' value='".$path_to_tmp.$_FILES['new_file']["name"]."'>";
		echo "<input type=hidden name='file_name' value='".$_FILES['new_file']["name"]."'>";
		echo "<input type=hidden name='file_size' value='".$_FILES['new_file']["size"]."'>";
	}
	
//�����-�����. �����
echo "</div></td></tr><tr class=footer_tr><td>";	
	
	echo "<hr>";
	if(isset($disabled)) {
		echo "<font size=3 color=red>��������! ���� ��������. ��������� �������� ���������.</font><br>";
		exit();
	}	
	if($_SESSION['user']['rw_src_bd']<>'w') echo "<font color=red>�������������� ���������!</font>";
	else {
	echo "<div id=load_status></div>";
	echo "<input type=checkbox name=robot_need><b> ������� �������� �������</b></input>";
	echo "<input type='hidden' name='load_caption' value=���������><br>";
	echo "<input type='button' name=load value=��������� onClick='start_load()'>  <input type='button' name='cancel_load' style='display:none' value='�������� ��������' onClick=fCancelLoad(".$_SESSION['adm']['load_id'].",'".$abort_pwd."')><br>";
	echo "���� � �������� ��������� �����:<br>";
	
	echo "<a href='http://sc/local/tmp/load_errors.csv' target=_blank>http://sc/local/tmp/load_errors.csv</a><br>";
	//echo "<a href='\\\sc\\htdocs_local\\tmp\\load_errors.csv' target=_blank>\\\sc\\htdocs_local\\tmp\\load_errors.csv</a><br>";
	}
echo "</form>";

//�����-�����. �����
echo "</td></tr></table>";

}
else {
//�����-�����. �������
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
//�����-�����. �����
echo "</div></td></tr><tr class=footer_tr><td>";
//�����-�����. �����
echo "</td></tr></table>";
}
?>
</body>
</html>
