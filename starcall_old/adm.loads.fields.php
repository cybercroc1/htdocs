<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">	
<script src="func.row_select.js"></script>
<script src="adm.loads.fields.js"></script>
<?php 

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if($_SESSION['user']['rw_src_bd']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form name=frm method=post action=adm.loads.fields.save.php target='logFrame'>";

//�����-�����. �����
echo "<table class=content_table><tr><td class=header_td>";

echo " | ";
echo "<a href='adm.loads.loads.php'>�������� ��������</a> | ";
echo "<a href='adm.loads.fields.php'><font size=4>��������� ���. �����</font></a> | ";
echo "<a href='adm.loads.load_data.php'>�������� �� .CSV</a> | ";
echo "<font align=right><a href='help.adm.loads.fields.html' target='_blank'>�������</a></font>";
echo "<hr>";

include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");

	echo "<font size=4>������: ".$_SESSION['adm']['project']['name']." (id:".$_SESSION['adm']['project']['id'].")</font><br>"; 
	
	//�������� �������� ��������
	$q=OCIParse($c,"select id cnt from STC_LOAD_HISTORY t
where project_id='".$_SESSION['adm']['project']['id']."' and status='�����������...'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		$disabled='y';
	} 
	if(isset($disabled)) {
		echo "<font size=3 color=red>��������! ���� ��������. �������������� �������� ����� ���������.</font>";
	}
	//
	
	$base_fields=array();
	
	echo "<input type=hidden name=project_id value='".$_SESSION['adm']['project']['id']."'>";
			
	//�������� �� �� ��� ������������ ���� � ������ base_fields
	$q=OCIParse($c,"select t.id,t.text_name,t.code_name,t.std_field_name,s.description,t.uniq,t.must,t.quoted,t.idx,t.ank_show 
  	from STC_FIELDS t,STC_LI_STANDARD_FIELDS s
  	where s.name(+)=t.std_field_name
	and t.project_id='".$_SESSION['adm']['project']['id']."' and t.src_type_id='1'
	order by ord");
	OCIExecute($q, OCI_DEFAULT);
	$i=0; while(OCIFetch($q)) {$i++;
		$base_fields[$i]['id']=OCIResult($q,"ID");
		$base_fields[$i]['text_name']=OCIResult($q,"TEXT_NAME");
		$base_fields[$i]['code_name']=OCIResult($q,"CODE_NAME");
		$base_fields[$i]['std_field_name']=OCIResult($q,"STD_FIELD_NAME");
		$base_fields[$i]['std_field_desc']=OCIResult($q,"DESCRIPTION");
		$base_fields[$i]['uniq']=OCIResult($q,"UNIQ");
		$base_fields[$i]['must']=OCIResult($q,"MUST");
		$base_fields[$i]['quoted']=OCIResult($q,"QUOTED");
		$base_fields[$i]['idx']=OCIResult($q,"IDX");
		$base_fields[$i]['ank_show']=OCIResult($q,"ANK_SHOW");
		$base_fields[$i]['new_field']='n';
	}
	if($i==0) $first_load='y';
	//

		
		//��������� ������ ����������� �����
		$q=OCIParse($c,"select t.name,t.description from STC_LI_STANDARD_FIELDS t
order by t.description");
		OCIExecute($q);
		echo "<script>";
		$i=0; while(OCIFetch($q)) {
			$std_fields[$i]['name']=OCIResult($q,'NAME');
			$std_fields[$i]['description']=OCIResult($q,'DESCRIPTION');
			echo "std_field_name[$i]='".OCIResult($q,'NAME')."'; std_field_desc[$i]='".OCIResult($q,'DESCRIPTION')."';";
		$i++;
		}
		echo "</script>";
		//

//�����-�����. �������
echo "</td></tr><tr><td class=content_td><div class=content_div>";
		
		echo "<table id=tbl name=tbl style='table-layout:fixed'>";
		echo "<tr>
		<th width=12></th>
		<th width=12></th>
		<th width=380 colspan=4>� ����</th>
		<th width=40 rowspan=2>����.<br>";
		
		echo "<select name=uniq_term>
		<option value=�".($_SESSION['adm']['project']['uniq_term']=='�'?' selected':NULL).">�</option>
		<option value=���".($_SESSION['adm']['project']['uniq_term']=='���'?' selected':NULL).">���</option>
		</select>";
		
		echo "</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th></tr>";
		echo "<tr>";
		echo "<th></th>";
		echo "<th style='cursor:pointer' title='�������� ����' onclick=plus_field(this)><img src='png/plus.png'></img></th>";
		echo "<th width=40>ID</th>
		<th width=150>���</th>
		<th width=80>������� ���</th>
		<th width=150>�����. ����</th>
		<th width=40>����.</th>
		<th width=40>����� / ����.����</th>
		<th width=40>������ / ����.����</th>
		<th width=40>����� � ������</th>
		</tr>";
		foreach($base_fields as $key => $bf) {
			echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
			//echo "<th><input type=checkbox name=mark[".$bf['id']."]></th>";
			echo "<th style='cursor:pointer' title='������� (������� ������)' onDblClick='del_old_field(".$bf['id'].");del_field(this)'><font color=red><img src='png/del.png'></img></font></th>";
			echo "<th style='cursor:pointer' title='�������� ����' onClick=plus_field(this)><font color=blue><img src='png/plus.png'></img></font></th>";
			echo "<th style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick='click_row(this)'><input type=hidden name=base_fields_id[] value='".$bf['id']."'>".$bf['id']."</th>";


				echo "<td onclick='click_row(this)'><input style='width:100%' type=text name=base_fields_text_name[".$bf['id']."] value='".$bf['text_name']."'></td>";
				echo "<td onclick='click_row(this)'><input style='width:100%' type=text name=base_fields_code_name[".$bf['id']."] value='".$bf['code_name']."'></td>";
				echo "<td onclick='click_row(this)'>";
				if($bf['std_field_desc']<>'') {
					echo "<input type=hidden name=base_fields_std_name[".$bf['id']."] value='".$bf['std_field_name']."'>".$bf['std_field_desc']." (".$bf['std_field_name'].")";
				}
				else {
					echo "<select name='base_fields_std_name[".$bf['id']."]'><option></option>";
					foreach($std_fields as $std_field) {
						echo "<option value='".$std_field['name']."'".($std_field['name']==$bf['std_field_name']?' selected':NULL).">".$std_field['description']." (".$std_field['name'].")</option>";
					}
					echo "</select>";
				}
				echo "</td>";
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_uniq[".$bf['id']."]".($bf['uniq']<>''?' checked':NULL)."></td>"; //����������
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_must[".$bf['id']."]".($bf['must']<>''?' checked':NULL)."></td>"; //������������
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_quoted[".$bf['id']."]".($bf['quoted']<>''?' checked':NULL)."></td>"; //�����������
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_idx[".$bf['id']."]".($bf['idx']<>''?' checked':NULL)."></td>"; //�������������
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_ank_show[".$bf['id']."]".($bf['ank_show']<>''?' checked':NULL)."></td>"; //����� � ������				
			
			//echo "<td></td>"; //��� ������ ���� ����� ��� ����������� ����� � ��������� ����� � ������ IE
			echo "</tr>";
		}
		echo "</table>";

//�����-�����. �����
echo "</div></td></tr><tr><td class=footer_td>";

		echo "<hr>";

		if(isset($disabled)) {
			echo "<font size=3 color=red>��������! ���� ��������. �������������� �������� ����� ���������.</font>";
			exit();
		}
		if($_SESSION['user']['rw_src_bd']<>'w') echo "<font color=red>�������������� ���������!</font>";
		else {
		echo "<div id=save_status></div>";
		echo "<input type=hidden name=frm_submit value=save>";
		echo "<input type=button name=save value=��������� onclick=this.disabled=true;frm.cancel.disabled=true;frm.submit();> ";
		echo "<input type=button name=cancel value=������ onclick={this.style.display='none';frm.frm_submit.value='save';frm.save.value='���������';document.getElementById('save_status').innerHTML='';} style='display:none' >";
		//echo "<input type=button name=cancel value=������ onclick=document.location.reload(); style='display:none' >";
		}		
echo "</form>";

//�����-�����. �����
echo "</td></tr></table>";

?>
</body>
</html>
