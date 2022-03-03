<?php 
set_time_limit(600);
include("../../starcall_conf/session.cfg.php"); 

extract($_REQUEST);

if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();
$project_id=$_SESSION['project']['id'];

include("../../starcall_conf/conn_string.cfg.php");

//*����������
$time=time();

if(isset($frm_submit) and $frm_submit=='save') {
	//���������� ��������� ����
	if($level<>'src' and $level<>'qst') {	
		if(isset($src_quote_id)) {
			$upd=OCIParse($c,"update STC_SRC_QUOTES set src_quote=:quote where project_id=".$project_id." and id=:quote_id");
			foreach($src_quote_id as $quote_id => $quote) {
				OCIBindByName($upd,":quote_id",$quote_id);
				OCIBindByName($upd,":quote",$quote);
					OCIExecute($upd,OCI_DEFAULT);
			}
			echo "��������� �������� ���� �� �������� �����<hr>";
		}
		if(isset($qst_quote_id)) {
			$upd=OCIParse($c,"update STC_QST_QUOTES set qst_quote=:quote where project_id=".$project_id." and id=:quote_id");
			foreach($qst_quote_id as $quote_id => $quote) {
				OCIBindByName($upd,":quote_id",$quote_id);
				OCIBindByName($upd,":quote",$quote);
				OCIExecute($upd,OCI_DEFAULT);
			}
			echo "��������� �������� ���� �� ��������<hr>";
		}
		//�������� �������� ��������� ����
		OCIExecute(OCIParse($c,"begin STC_QUOTE_PARENT_CALC(".$project_id."); end;"));
		echo "����������� �������� ��������� ���� STC_QUOTE_PARENT_CALC()<hr>";
	}
	//���������� ����������� �� ��������
	if($level=='src' and isset($src_index_id)) {
		$upd=OCIParse($c,"update STC_SRC_INDEXES set src_idx_quote=:quote where project_id=".$project_id." and id=:index_id");
		foreach($src_index_id as $index_id => $quote) {
			OCIBindByName($upd,":index_id",$index_id);
			OCIBindByName($upd,":quote",$quote);
			OCIExecute($upd,OCI_DEFAULT);
		}
		echo "��������� �������� ����������� ���� �� �������� �����<hr>";
	}
	//���������� ����������� �� ��������
	if($level=='qst' and isset($qst_index_id)) {
		$upd=OCIParse($c,"update STC_QST_INDEXES set qst_idx_quote=:quote where project_id=".$project_id." and id=:index_id");
		foreach($qst_index_id as $index_id => $quote) {
			OCIBindByName($upd,":index_id",$index_id);
			OCIBindByName($upd,":quote",$quote);
			OCIExecute($upd,OCI_DEFAULT);
		}
		echo "��������� �������� ����������� ���� �� ��������<hr>";		
	}
	OCICommit($c);
	echo "<font color=green>���������</font><br>";
	echo "<script>
	parent.admBottomFrame.document.getElementById('save_status').innerHTML='<font color=green>���������</font>';
	parent.admBottomFrame.frm.frm_submit.value='saved';
	parent.admBottomFrame.frm.save.value='���������';
	//parent.admBottomFrame.frm.cancel.style.display='none';
	parent.admBottomFrame.location.reload();
	</script>";		
	exit();
}
//*
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 
//*������������ ����
if(isset($src_quote_rebuild)) {
	$q=OCIParse($c,"select t.src_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"SRC_QUOTE_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_src_quote_rebuild(".$_SESSION['project']['id']."); end;"))) {
		$_SESSION['project']['src_quote_broken']='';
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
	} 
}

if(isset($qst_quote_rebuild)) {
	$q=OCIParse($c,"select t.qst_quote_broken from STC_PROJECTS t
	where id=".$_SESSION['project']['id']);
	OCIExecute($q); OCIFetch($q);
	if(OCIResult($q,"QST_QUOTE_BROKEN")=='yes') {
		if(OCIExecute(OCIParse($c,"begin stc_qst_quote_rebuild(".$_SESSION['project']['id']."); end;"))) {
		$_SESSION['project']['qst_quote_broken']='';
		echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
		}
	}
}

if($_SESSION['project']['src_quote_broken']<>'') {
	echo "<font color=red><a href='?src_quote_rebuild'>�������� ����� �� �������� �����! ������� ����, ��� �� ����������� ����� (����� ������ ���������� �����) </a></font> ";
	exit();
}
else if($_SESSION['project']['qst_quote_broken']<>'') {
	echo "<font color=red><a href='?qst_quote_rebuild'>�������� ����� �� ��������! ������� ����, ��� �� ���������� ����� (����� ������ ���������� �����) </font> ";
	exit();
}
//*
//������ ��������
echo "<form name=frm_select method=get>";
echo " | ";
echo "<font size=4>�����</font> | ";
$src_fields=array();
//������ �������� �����
$q=OCIParse($c,"select id,text_name from STC_FIELDS t
where project_id=".$project_id." and t.src_type_id=1 and t.quoted is not null and t.deleted is null
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$src_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}

$quest_fields=array();
//������ ����������� ��������
$q=OCIParse($c,"select o.quote_num,f.text_name from STC_OBJECTS o, Stc_Fields f
where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
and f.deleted is null and f.id=o.field_id
order by o.quote_num");
OCIExecute($q); $i=0; while(OCIFetch($q)) {$i++;
	$quest_fields[OCIResult($q,"QUOTE_NUM")]=OCIResult($q,"TEXT_NAME");
	$old_qst_quote_id[$i]='';
}

if(count($src_fields)==0 and count($quest_fields)==0) {echo "<font size=3><b>��� ����������� ����� � ��������</b></font>"; exit();}
if(!isset($level)) $level='null';
echo "<select name=level onchange=frm_select.submit()><option value=null>�������� �������</option>";
if(count($src_fields)>0) echo "<option value=0".($level=='0'?' selected':NULL).">�������� ����</option>";
if(count($quest_fields)>0) {
	foreach($quest_fields as $lvl => $questname) {
		echo "<option value=".$lvl.($level==$lvl?' selected':NULL).">������� ".$lvl."</option>";
	}
}
echo "<option value='src'".($level=='src'?' selected':NULL).">����������� �� ��������</option>";
echo "<option value='qst'".($level=='qst'?' selected':NULL).">����������� �� ��������</option>";
echo "</select> | ";
echo "<input type=submit value='��������� � XLSX' onclick=parent.logFrame.location='adm.quotes.xlsx_exp.php'> | ";
echo "<a href='help.adm.quotes.html' target=_blank>�������</a><hr>";
echo "</form>";

if(!isset($level) or $level=='null') exit();

echo "<form name=imp_from_xlsx method=post action='adm.quotes.xlsx_imp.php' target='logFrame' enctype=\"multipart/form-data\">";
echo "<input type=file name=imp_file onchange=this.value!=''?import_from_file.disabled=false:import_from_file.disabled=true></input>
<input type=submit name=import_from_file disabled value='���������'><br>";	
echo "</form>";

echo "<form name=frm method=post target='logFrame'>";
echo "<input type=hidden name=level value='".$level."'>";

if($level<>'src' and $level<>'qst') {
//��������� �����
	$sql1="select * from" ; $sql2=""; $sql3=""; $sql4="order by ";
	//�������� ������
	//���� ���� �������� ����
	$lvl=0;
	if(count($src_fields)>0) {
		$i=0; foreach($src_fields as $field_id => $field_name) {$i++;
			if($i==1) {
				$sql2.="(select ssq.id qid0,ssq.src_quote quote0,ssq.src_new new0,ssq.src_norm norm0 from stc_src_quotes ssq where ssq.project_id=".$project_id.") ssq ";
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".src_qid=ssq.qid0 ";
			}
			$sql2.=", ";
			$sql2.="(select sqi.quote_id src_qid, si.value val0_".$i." from  stc_src_indexes si, stc_src_quote_indexes sqi
where si.project_id=".$project_id." and si.field_id=".$field_id." and sqi.index_id=si.id) s".$i." ";
			if($i>1) {
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".src_qid=s".($i-1).".src_qid ";
				$sql4.=", ";
			}
			$sql4.="s".$i.".val0_".$i." ";
			
		}
		if($level>0) {
			$sql2.=", ";
			$sql4.=", ";
		} 
	}
	//����� �� ��������, ���� ���� ���� �� ���� ����������� ������
	if(count($quest_fields)>0) {
		for($lvl=1; $lvl<=$level; $lvl++) {
			if($lvl>1) {
				$sql2.=", ";
				$sql4.=", ";				
			}
			if($lvl==1) {
				$sql2.="(select qq.src_quote_id src_qid, qq.id qid".$lvl.",i.value val".$lvl.", qq.qst_quote quote".$lvl.", qq.qst_norm norm".$lvl." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$lvl." and qq.index_id=i.id) q".$lvl." ";

				if(count($src_fields)>0) {
					if($sql3=='') $sql3='where '; else $sql3.='and ';
					$sql3.="q".$lvl.".src_qid=ssq.qid0 ";
				}
			}
			else {
				$sql2.="(select qq.parent_id,qq.id qid".$lvl.",i.value val".$lvl.", qq.qst_quote quote".$lvl.", qq.qst_norm norm".$lvl." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$lvl." and qq.index_id=i.id) q".$lvl." ";
				if($sql3=='') $sql3='where '; else $sql3.='and ';
				$sql3.="q".$lvl.".parent_id=q".($lvl-1).".qid".($lvl-1)." ";
			}
			$sql4.="q".$lvl.".val".$lvl." ";	
		}
	}
	echo "<table>";
	
	$q=OCIParse($c,$sql1.$sql2.$sql3.$sql4);
	OCIExecute($q);
	$rownum=0;
	$old_src_quote_id='';
	$i=0; while(OCIFetch($q)) {$i++; 
		if($i==1) {
			echo "<tr><td></td>";
			if(count($src_fields)>0) echo "<th colspan=".($level==0?count($src_fields)+3:count($src_fields)).">� � � � � � � �  � � � �</th>";
			
			for($j=1; $j<=$level; $j++) {
				echo "<th colspan=".($j==$level?'3':'1').">� � � � � � � ".$j."</th>";
			}
			
			echo "</tr>";
					
			echo "<tr><td>�</td>";
			if(count($src_fields)>0) {
				$j=0; foreach($src_fields as $field_id => $field_name) {$j++;
					echo "<th>".$field_name."</th>";
				}
				if($level==0) {
					echo "<th>�����</th>";
					echo "<th>�����</th>";
					echo "<th>���������</th>";
				}
			}
			if(count($quest_fields)>0) {
				$j=0; foreach($quest_fields as $num => $quest_name) {$j++;
					if($j>$level) break;
					echo "<th>".$quest_name."</th>";
					if($j==$level) {
						echo "<th>�����</th>";
						echo "<th>���������</th>";
					}
				}
			}
			echo "</tr>";
		}
		$rownum++;
		echo "<tr><td>".$rownum."</td>";
		if(count($src_fields)>0) {
			$j=0; foreach($src_fields as $field_id => $field_name) {$j++;
				echo "<td>".OCIResult($q,"VAL0_".$j)."</th>";
			}
			if($level==0) { //�� ���������� ����� ��� �� ���������� ������
				echo "<td>";
				if(OCIResult($q,"QID0")<>$old_src_quote_id){
					$old_src_quote_id=OCIResult($q,"QID0");
					echo "<input type=text size=1 name=src_quote_id[".OCIResult($q,"QID0")."] value='".OCIResult($q,"QUOTE0")."'>";
				}
				echo "</td>";
				echo "<td>".OCIResult($q,"NEW0")."</td>";
				echo "<td>".OCIResult($q,"NORM0")."</td>";
			}
		}
		if(count($quest_fields)>0) {
			$j=0; foreach($quest_fields as $quest_lvl => $field_name) {$j++;
				if($j>$level) break;
				echo "<td>".OCIResult($q,"VAL".$quest_lvl)."</td>";
				if($j==$level) { //�� ���������� ����� ��� �� ���������� ������
					echo "<td>";
					if(OCIResult($q,"QID".$quest_lvl)<>$old_qst_quote_id[$quest_lvl]) {
						$old_qst_quote_id[$quest_lvl]=OCIResult($q,"QID".$quest_lvl);
						echo "<input type=text size=1 name=qst_quote_id[".OCIResult($q,"QID".$quest_lvl)."] value='".OCIResult($q,"QUOTE".$quest_lvl)."'>";
					}
					echo "</td>";
					echo "<td>".OCIResult($q,"NORM".$quest_lvl)."</td>";
				}
			}
		}
		echo "</tr>";
	}
	echo "</table>";
}
//����������� �� ��������
if($level=='src') {
	$q=OCIParse($c,"select i.id idx_id,f.text_name,i.value, i.src_idx_quote, i.src_idx_new, i.src_idx_norm
	from STC_FIELDS f, STC_SRC_INDEXES i
	where f.project_id=".$project_id." and f.deleted is null and f.src_type_id=1 and (f.quoted is not null or f.idx is not null)
	and i.project_id=".$project_id." and i.field_id=f.id
	order by f.text_name,i.value");
	OCIExecute($q);
	$i=0; while(OCIFetch($q)) {$i++;
		if($i==1) {
			echo "<table>";
			echo "<tr>";
			echo "<th>�������� ����</th>";
			echo "<th>��������</th>";	
			echo "<th>�����</th>";
			echo "<th>�����</th>";
			echo "<th>���������</th>";
			echo "</tr>";
		}
		echo "<tr>";
		echo "<td><b>".OCIResult($q,"TEXT_NAME")."</b></td>";
		echo "<td>".OCIResult($q,"VALUE")."</td>";
		echo "<td><input type=text size=1 name=src_index_id[".OCIResult($q,"IDX_ID")."] value='".OCIResult($q,"SRC_IDX_QUOTE")."'></td>";
		echo "<td>".OCIResult($q,"SRC_IDX_NEW")."</td>";
		echo "<td>".OCIResult($q,"SRC_IDX_NORM")."</td>";
		echo "</tr>";	
	}
	if ($i==0) {echo "<font size=3><b>��� ����������� ��� ������������� �������� �����</b></font>"; exit();}
	echo "</table>";
}
//����������� �� ��������
if($level=='qst') {
	$q=OCIParse($c,"select qi.id idx_id,f.text_name,qi.value, qi.qst_idx_quote, qi.qst_idx_norm 
from stc_objects o, stc_fields f, stc_qst_indexes qi 
where o.project_id=".$project_id." and o.deleted is null
and f.project_id=".$project_id." and f.id=o.field_id
and qi.object_id=o.id
order by f.text_name,qi.value");
	OCIExecute($q);
	$i=0; while(OCIFetch($q)) {$i++;
		if($i==1) {
			echo "<table>";
			echo "<tr>";
			echo "<th>�������� ����</th>";
			echo "<th>��������</th>";	
			echo "<th>�����</th>";
			echo "<th>���������</th>";
			echo "</tr>";
		}
		echo "<tr>";
		echo "<td><b>".OCIResult($q,"TEXT_NAME")."</b></td>";
		echo "<td>".OCIResult($q,"VALUE")."</td>";
		echo "<td><input type=text size=1 name=qst_index_id[".OCIResult($q,"IDX_ID")."] value='".OCIResult($q,"QST_IDX_QUOTE")."'></td>";
		echo "<td>".OCIResult($q,"QST_IDX_NORM")."</td>";
		echo "</tr>";	
	}
	
	if ($i==0) {echo "<font size=3><b>��� ����������� �������� ��� � �������� �� �������</b></font>"; exit();}
	echo "</table>";	
}
echo "<hr>";
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=��������� onclick=this.disabled=true;frm.submit();> ";
echo "</form>";
//

?>
</body>
</html>

