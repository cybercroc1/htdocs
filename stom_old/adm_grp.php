<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>������������ ���-����</title>
</head>
<form name=frm method=post>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>'y') {
	echo "<font size=3 color=red>�� ���������� ����!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

echo "<table width=100%><tr><td align=left><font size=3>
<a href=adm_usr.php>������������</a> | 
<b>������</b> | 
<a href=adm_trbl.php>��������</a> | 
<a href=adm_locat.php>�����</a>
</font></td><td align=right>";
echo "<a href=tex.php>������</a> | "; 
echo "<a href=tex.php?exit><font color=red>�����</font></a></td></tr></table><hr>";

//����������/�������������� ������
if(isset($save)) {
	if($lt_grp_id=='') {
		$q_ins=OCIParse($c,"insert into sup_lt_group (id,name,eval_only)
		values ((select max(id)+1 from sup_lt_group),:name,'".$new_type."')
		returning id into :new_id");
		OCIBindByName($q_ins,":name",$new_lg_grp_name);
		OCIBindByName($q_ins,":new_id",$lt_grp_id,1024);
		OCIExecute($q_ins,OCI_DEFAULT);
		OCICommit($c);
	}
	else {
		$q_upd=OCIParse($c,"update sup_lt_group set name=:name where id='".$lt_grp_id."'");
		OCIBindByName($q_upd,":name",$new_lg_grp_name);
		OCIExecute($q_upd,OCI_DEFAULT);
		OCICommit($c);
	}
}
//
//�������� ������
if(isset($del_grp)) {
	$q_upd=OCIParse($c,"update sup_user set deleted=sysdate where lt_grp_id='".$lt_grp_id."'");
	$q_del=OCIParse($c,"delete from sup_lt_group where id='".$lt_grp_id."'");
	OCIExecute($q_upd,OCI_DEFAULT);
	OCIExecute($q_del,OCI_DEFAULT);
	OCICommit($c);
	$lt_grp_id='';
}
//
//���������� ����/������� � ������
if(isset($add_lt) and isset($location_ids) and isset($trbl_ids)) {
	$q_del=OCIParse($c,"delete from sup_lt where lt_grp_id='".$lt_grp_id."' and location_id=:location_id and trbl_id=:trbl_id");
	$q_add=OCIParse($c,"insert into sup_lt (lt_grp_id,location_id,trbl_id) values ('".$lt_grp_id."',:location_id,:trbl_id)");
	foreach($location_ids as $loc_id) {
		foreach($trbl_ids as $trbl_id) {
			OCIBindByName($q_del,":location_id",$loc_id);
			OCIBindByName($q_del,":trbl_id",$trbl_id);
			OCIExecute($q_del,OCI_DEFAULT);
			OCIBindByName($q_add,":location_id",$loc_id);
			OCIBindByName($q_add,":trbl_id",$trbl_id);
			OCIExecute($q_add,OCI_DEFAULT);
		}
	}
	OCICommit($c);
}
//
//�������� ����/������� �� ������
if(isset($del_lt) and (isset($location_ids) and isset($trbl_ids))) {
	$q_del=OCIParse($c,"delete from sup_lt where lt_grp_id='".$lt_grp_id."' and location_id=:location_id and trbl_id=:trbl_id");
	foreach($location_ids as $loc_id) {
		foreach($trbl_ids as $trbl_id) {
			OCIBindByName($q_del,":location_id",$loc_id);
			OCIBindByName($q_del,":trbl_id",$trbl_id);
			OCIExecute($q_del,OCI_DEFAULT);
		}
	}
	OCICommit($c);
}
//
//������ ����/������� �� ���������
if(isset($replace_lt) and (isset($location_ids) and isset($trbl_ids))) {
	$q_del=OCIParse($c,"delete from sup_lt where lt_grp_id='".$lt_grp_id."'");
	$q_add=OCIParse($c,"insert into sup_lt (lt_grp_id,location_id,trbl_id) values ('".$lt_grp_id."',:location_id,:trbl_id)");
	OCIExecute($q_del,OCI_DEFAULT);
	foreach($location_ids as $loc_id) {
		foreach($trbl_ids as $trbl_id) {
			OCIBindByName($q_add,":location_id",$loc_id);
			OCIBindByName($q_add,":trbl_id",$trbl_id);
			OCIExecute($q_add,OCI_DEFAULT);
		}
	}
	OCICommit($c);
}
//

//����� ������
if(!isset($lt_grp_id)) $lt_grp_id='';
if(!isset($lt_grp_name)) $lt_grp_name='';
if(!isset($eval_only)) $eval_only='';
if(!isset($sort) and !isset($_SESSION['sort'])) $_SESSION['sort']='location';
if(isset($sort) and $_SESSION['sort']=='location') $_SESSION['sort']='trbl';
else if (isset($sort) and $_SESSION['sort']=='trbl') $_SESSION['sort']='location';
	
echo "<nobr>�������� ������: <select name=lt_grp_id onchange=document.all.ok.click()>";
echo "<option value='' style='color:green'>������� ������</option>";
echo "<optgroup label='�����������'></optgroup>";
$q=OCIParse($c,"select id,name,eval_only from sup_lt_group where id<>0 and eval_only is null order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID");
	if(OCIResult($q,"ID")==$lt_grp_id) {echo " selected"; $lt_grp_name=OCIResult($q,"NAME"); $eval_only=OCIResult($q,"EVAL_ONLY");}
	echo ">".OCIResult($q,"NAME")."</option>";
}
echo "<optgroup label='���������'></optgroup>";
$q=OCIParse($c,"select id,name,eval_only from sup_lt_group where id<>0 and eval_only is not null order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID");
	if(OCIResult($q,"ID")==$lt_grp_id) {echo " selected"; $lt_grp_name=OCIResult($q,"NAME"); $eval_only=OCIResult($q,"EVAL_ONLY");}
	echo ">".OCIResult($q,"NAME")."</option>";
}
echo "</select>
<input type=submit name=ok value=�������>";

if (isset($lt_grp_id) and $lt_grp_id<>'') {
	echo " <a href=\"javascript:del_grp('".$lt_grp_id."')\"><img src=del.gif title=\"������� ������� ������\" border=0></a> | ";
}
echo "</nobr>";

if($lt_grp_id=='') {
	echo " <nobr>��������: <input type=text name=new_lg_grp_name onkeyup='if(this.value==\"\"){save.disabled=true;}else{save.disabled=false;}'></nobr>";
	echo " <nobr>���: ";
	echo "<input type=radio name=new_type value='' checked>����������� | </input>
	      <input type=radio name=new_type value='y'>��������� </input></nobr>";
}
else  echo " <nobr>�������������: <input type=text name=new_lg_grp_name value='".$lt_grp_name."' onkeyup='if(this.value==\"\"){save.disabled=true;}else{save.disabled=false;}'>";
echo "</nobr>";
echo " <input type=submit name='save' disabled style='background:green' value='���������'>";
//
if($eval_only=='y') {echo "<br><font size=3>��� ������ ������������ ������ ��� <b>������ ������</b></font>";}
echo "<hr>";

if($lt_grp_id<>'') {
//������������
echo "������������: ";
$q=OCIParse($c,"select t.login,t.fio,t.coment,t.send,t.look,t.solution,t.redirect,t.eval,t.admin,decode(t.lt_grp_id,'0','y',null) all_grp 
from SUP_USER t
where t.lt_grp_id='".$lt_grp_id."' and deleted is null
order by t.fio");
OCIExecute($q,OCI_DEFAULT);
$i=0; while(OCIFetch($q)) {
	$i++; if($i==1) {
		echo "<font color=red>��������! � ������ �������� ������ ����� ������� ��������� ������������:</font>";
		echo "<table bgcolor=black cellspacing=1 cellpadding=1>";
		echo "<tr><th bgcolor=white>�����</th>
		<th bgcolor=white>���</th>
		<th bgcolor=white>�����������</th>
		<th bgcolor=white>�������</th>
		<th bgcolor=white>����������</th>
		<th bgcolor=white>�����</th>
		<th bgcolor=white>������</th>
		<th bgcolor=white>����. email</th>
		</tr>";
	}
	echo "<tr>
	<td bgcolor=white align=center>".OCIResult($q,"LOGIN")."</td>
	<td bgcolor=white align=center><b>".OCIResult($q,"FIO")."</b></td>
	<td bgcolor=white align=center>".OCIResult($q,"COMENT")."</td>
	<td bgcolor=white align=center><b>".OCIResult($q,"SOLUTION")."</b></td>
	<td bgcolor=white align=center><b>".OCIResult($q,"REDIRECT")."</b></td>
	<td bgcolor=white align=center><b>".OCIResult($q,"LOOK")."</b></td>
	<td bgcolor=white align=center><b>".OCIResult($q,"EVAL")."</b></td>
	<td bgcolor=white align=center><b>".OCIResult($q,"SEND")."</b></td>
	</tr>";
}
if($i>0) echo "</table>";
else echo "<font color=red>������ ������ �� ��������� �� ������ ������������</font>";
echo "<hr>";
//
//������ ������ � �������
echo "����� ���� � ����� �������, �������� � ������: ";
echo "<table bgcolor=black cellspacing=1 cellpadding=1>";
echo "<tr><td bgcolor=white colspan=2>
<nobr><input type=submit style='background:green' name=add_lt value='�������� ���������'> 
<input type=submit style='background:red' name=del_lt value='������� ���������'> 
<input type=submit style='background:yellow' name=replace_lt value='�������� �� ���������'></nobr>
</td></tr>";
echo "<tr><th bgcolor=white><input type=checkbox name=all_locations onclick='all_location(this)'";
if(isset($all_locations)) echo " checked";
echo ">�����</th>";
echo "<th bgcolor=white><input type=checkbox name=all_trbls onclick='all_trbl(this)'";
if(isset($all_trbls)) echo " checked";
echo ">��������</th></tr>";
echo "<tr>";
//������ �������
echo "<td bgcolor=white>";
$q=OCIParse($c,"select id,name from SUP_KLINIKA
where deleted is null
order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<input type=checkbox location_id name=location_ids[".OCIResult($q,'ID')."] value='".OCIResult($q,'ID')."' onclick='uncheck_location(this)'";
	if(isset($location_ids[OCIResult($q,'ID')])) echo " checked";
	echo ">".OCIResult($q,'NAME')."<br>";
}
echo "</td>";
//
//������ �������
echo "<td bgcolor=white>";
$q1=OCIParse($c,"select id,name from SUP_TRBL_GROUP
order by name");
$q2=OCIParse($c,"select id,name from SUP_TRBL_TYPE
where trbl_grp_id=:trbl_grp_id and deleted is null
order by name");
OCIExecute($q1,OCI_DEFAULT);
$i=0; while(OCIFetch($q1)) {
	$i++; if($i>1) echo "<hr>";
	echo "������ �������: <input type=checkbox trbl_grp trbl_grp_id='".OCIResult($q1,'ID')."'' name=trbl_grp_ids[".OCIResult($q1,'ID')."] onclick='all_trbl_grp(this)'";
	if(isset($trbl_grp_ids[OCIResult($q1,'ID')])) echo " checked";
	echo "><b>".OCIResult($q1,'NAME')."</b><br>";
	$trbl_grp_id=OCIResult($q1,'ID');
	OCIBindByName($q2,":trbl_grp_id",$trbl_grp_id);
	OCIExecute($q2,OCI_DEFAULT);
	while(OCIFetch($q2)) {
		echo "<input type=checkbox trbl_grp_id='".OCIResult($q1,'ID')."' name=trbl_ids[".OCIResult($q2,'ID')."] value='".OCIResult($q2,'ID')."' onclick='uncheck_trbl(this)'";
		if(isset($trbl_ids[OCIResult($q2,'ID')])) echo " checked";
		echo ">".OCIResult($q2,'NAME')."<br>";
	}
}
echo "</td>";
//
echo "</tr>";
echo "</table><hr>";
	//
echo "����� � ���� �������, �������� � ������: ";	
	//������ ������� ������� � �������
	if($_SESSION['sort']=='location') {
		echo "<table bgcolor=black cellspacing=1 cellpadding=1><tr>
		<th bgcolor=white>�����</th><th bgcolor=white>�������� <input type=submit name=sort value='����.'></th></tr>";
	
		$q1=OCIParse($c,"select distinct sk.id,sk.name from sup_lt slt, sup_klinika sk
		where slt.lt_grp_id='".$lt_grp_id."'
		and sk.id=slt.location_id
		order by sk.name");
		
		$q2=OCIParse($c,"select distinct stg.id,stg.name from sup_lt slt, sup_trbl_type stt, sup_trbl_group stg
		where slt.lt_grp_id='".$lt_grp_id."' and slt.location_id=:location_id
		and stt.id=slt.trbl_id and stg.id=stt.trbl_grp_id
		order by stg.name");
	
		$q3=OCIParse($c,"select distinct stt.id,stt.name from sup_lt slt, sup_trbl_type stt
		where slt.lt_grp_id='".$lt_grp_id."' and slt.location_id=:location_id and stt.trbl_grp_id=:trbl_grp_id
		and stt.id=slt.trbl_id
		order by stt.name");
		
		OCIExecute($q1,OCI_DEFAULT);
		while(OCIFetch($q1)) {
			echo "<tr>";
			echo "<td bgcolor=white valign=top><b>".OCIResult($q1,"NAME")."</b></td>";
			$location_id=OCIResult($q1,"ID");
			echo "<td bgcolor=white valign=top>";
			OCIBindByName($q2,":location_id",$location_id);
			OCIExecute($q2,OCI_DEFAULT);
			$i=0; while(OCIFetch($q2)) {
				$i++; if($i>1) echo "<hr>";
				echo "<b>".OCIResult($q2,'NAME')."</b><br>";
				$trbl_grp_id=OCIResult($q2,'ID');
				OCIBindByName($q3,":trbl_grp_id",$trbl_grp_id);
				OCIBindByName($q3,":location_id",$location_id);
				OCIExecute($q3,OCI_DEFAULT);
				while(OCIFetch($q3)) {
					echo OCIResult($q3,'NAME')."<br>";
				}
			}
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
	}	
	if($_SESSION['sort']=='trbl') {
		echo "<table bgcolor=black cellspacing=1 cellpadding=1><tr>
		<th bgcolor=white>��������</th><th bgcolor=white>����� <input type=submit name=sort value='����.'></th></tr>";

		$q1=OCIParse($c,"select distinct stg.id,stg.name from sup_lt slt, sup_trbl_type stt, sup_trbl_group stg
		where slt.lt_grp_id='".$lt_grp_id."'
		and stt.id=slt.trbl_id and stg.id=stt.trbl_grp_id
		order by stg.name");

		$q2=OCIParse($c,"select distinct stt.id,stt.name from sup_lt slt, sup_trbl_type stt
    	where slt.lt_grp_id='".$lt_grp_id."' and stt.trbl_grp_id=:trbl_grp_id
   		and stt.id=slt.trbl_id
	    order by stt.name");

		$q3=OCIParse($c,"select distinct sk.id,sk.name from sup_lt slt, sup_klinika sk
	   	where slt.lt_grp_id='".$lt_grp_id."' and slt.trbl_id=:trbl_type_id
	    and sk.id=slt.location_id
	    order by sk.name");
	
		OCIExecute($q1,OCI_DEFAULT);
		while(OCIFetch($q1)) {
			echo "<tr>";
			echo "<td bgcolor=white align=center colspan=2><b>".OCIResult($q1,"NAME")."</b></td>";
			$trbl_grp_id=OCIResult($q1,"ID");
			echo "</tr>";
			OCIBindByName($q2,":trbl_grp_id",$trbl_grp_id);
			OCIExecute($q2,OCI_DEFAULT);
			while(OCIFetch($q2)) {
				echo "<tr>";
				echo "<td bgcolor=white valign=top>";
	
				echo "<b>".OCIResult($q2,'NAME')."</b><br>";
				$trbl_type_id=OCIResult($q2,'ID');
				echo "</td>";
				echo "<td bgcolor=white valign=top>";
				OCIBindByName($q3,":trbl_type_id",$trbl_type_id);
				OCIExecute($q3,OCI_DEFAULT);
				while(OCIFetch($q3)) {
					echo OCIResult($q3,'NAME')."<br>";
				}
			echo "</td>";
			}
		echo "</tr>";
		}
		echo "</table>";
	}		
}
//
echo "</form>";
?>
<script language="javascript">
document.all.ok.style.display='none';
function del_grp(lt_grp_id) {
	if (confirm('������������� ������ ������� ������� ������ ?')) {
		var obj=document.createElement('input');
		obj.name='del_grp'; frm.appendChild(obj);
		frm.submit();
	}
}
function all_trbl_grp(obj) {
	with(document.all.frm) {
		if(obj.checked==true) v=true; else v=false;
		for(i=0; i<elements.length; i++) {
			if(elements[i].trbl_grp_id==obj.trbl_grp_id) elements[i].checked=v;				
		}
		if(v==false) all_trbls.checked=false;
	}
}
function all_location(obj) {
	with(document.all.frm) {
		if(obj.checked==true) v=true; else v=false;
		for(i=0; i<elements.length; i++) {
			if('location_id' in elements[i]) elements[i].checked=v;				
		}
	}
}
function all_trbl(obj) {
	with(document.all.frm) {
		if(obj.checked==true) v=true; else v=false;
		for(i=0; i<elements.length; i++) {
			if('trbl_grp_id' in elements[i]) elements[i].checked=v;				
		}
	}
}
function uncheck_location(obj) {
	with(document.all.frm) {
		if(obj.checked==false) {
			all_locations.checked=false;
		}
	}
}
function uncheck_trbl(obj) {
	with(document.all.frm) {
		if(obj.checked==false) {
			all_trbls.checked=false;
			for(i=0; i<elements.length; i++) {
				if('trbl_grp' in elements[i] && elements[i].trbl_grp_id==obj.trbl_grp_id) elements[i].checked=false;				
			}
		}
	}
}
</script>
