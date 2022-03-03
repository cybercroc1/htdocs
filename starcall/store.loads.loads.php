<?php include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<script src="func.row_select.js"></script>
<script>
function to_xls() {
	frm.action='store.loads.loads.exp_xls.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function to_csv() {
	frm.action='store.loads.loads.exp_csv.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function to_txt() {
	frm.action='store.loads.loads.exp_txt.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function allow_all_() {
	if(confirm('����� �������� ��� ������ � �������� �� ��������� ��������. ����������?')) {
		frm.action='store.loads.loads.allow_all.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='���������...';
		//��������� ��� �������� �����
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function disallow_all_() {
	if(confirm('����� ������������� ��� ������ � �������� �� ��������� ��������. ����������?')) {
		frm.action='store.loads.loads.disallow_all.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='����������...';
		//��������� ��� �������� �����
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function del() {
	if(confirm('����� ������� ������ �������������� (� ������ ��������) � �� ��������������� ���������� ������ �� ��������� ��������. �������?')) {
		frm.action='store.loads.loads.delete.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='��������...';
		//��������� ��� �������� �����
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function allow_from_robot(obj) {
	obj.disabled=true;
	//document.body.style.overflow = 'hidden'; //������ ��������� ���������
	if(document.all.popUpFrame.style.display=='none') {
		topOffset=240; //�������� ���� ����� �� ������� �������
		document.all.popUpFrame.style.left=event.clientX+'px';
		if(event.clientY<topOffset) {
			document.all.popUpFrame.style.top='10px';	
		}
		else {
			document.all.popUpFrame.style.top=event.clientY-topOffset+'px';
		}
		document.all.popUpFrame.style.display='';
		frm.action='store.loads.loads.allow_robot.php';
		frm.target='popUpFrame';
		frm.submit();
		frm.action=null;
		frm.target=null;
		
		//��������� ��� �������� �����
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}
	}
	else {
		document.all.popUpFrame.style.display='none';
	}
}
</script>
<?php 
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

include("starcall/conn_string.cfg.php");

echo "<form method=post action=null name=frm>";

//�����-�����. �����
echo "<table class=content_table><tr class=header_tr><td>";

isset($show_rows)?NULL:$show_rows=100;
isset($load_id)?$_SESSION['adm']['load_id']=$load_id:NULL;

echo " | ";
echo "<a href='store.loads.loads.php'><font size=4>�������� ��������</font></a> | ";
echo "<a href='store.loads.fields.php'>��������� ���. �����</a> | ";
echo "<a href='store.loads.load_data.php'>�������� �� .CSV</a> | ";
echo "<font align=right><a href='help.store.loads.loads.html' target='_blank'>�������</a></font>";
echo "<hr>";

//��������� ����������
if(isset($refresh)) {
	$upd=OCIParse($c,"update STOR_LOAD_HISTORY h 
	set 
	(h.load_rows,h.allow_rows)=(select count(*),count(allow) from STOR_BASE where project_id=h.project_id and load_hist_id=h.id),
	(h.load_phones,h.allow_phones)=(select count(*),count(allow) from STOR_PHONES where project_id=h.project_id and load_hist_id=h.id)
	where (status='�����������...' or status is null) and project_id='".$_SESSION['adm']['project']['id']."'");
	OCIExecute($upd); OCICommit($c);
}
//

echo "<font size=4>��������.</font><br>"; 

echo "<hr>";

if(!isset($order_by) and !isset($_SESSION['adm']['loads']['order_by'])) $_SESSION['adm']['loads']['order_by']='t.start_date desc';
if(isset($order_by)) $_SESSION['adm']['loads']['order_by']=$order_by;
	
	echo "<input type=hidden name=load_id value=''>";
	echo "���������� �����: <input type=text name='show_rows' value='100'> |  <nobr>��������� ������ ���������� <input type=checkbox name=allowed_only>";
	echo "<input type=submit name=show_data style='display:none' value='�������� ������'>";
	echo "<hr>";	
	
//�����-�����. �������
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
	
	echo "<table id=tbl class=white_table>
	<tr><td colspan=3></th><th colspan=2>����</th><th colspan=2>���������</th><th colspan=2>��������</th><th colspan=2>��������� ��� ��������</th><th></th></tr>
	<tr>
	<td align=center></th>
	<td align=center><b>ID</b></th>
	<td align=center><a href='?order_by=t.start_date desc'>".($_SESSION['adm']['loads']['order_by']=='t.start_date desc'?'<b>':NULL)."���� ����.</b></a></td>
	<td align=center><a href='?order_by=t.file_name'>".($_SESSION['adm']['loads']['order_by']=='t.file_name'?'<b>':NULL)."���</b></a></td>
	<td align=center><a href='?order_by=t.file_size_bytes'>".($_SESSION['adm']['loads']['order_by']=='t.file_size_bytes'?'<b>':NULL)."����</b></a></td>
	<td align=center><a href='?order_by=load_rows'>".($_SESSION['adm']['loads']['order_by']=='load_rows'?'<b>':NULL)."C����</b></a></td>
	<td align=center><a href='?order_by=load_phones'>".($_SESSION['adm']['loads']['order_by']=='load_phones'?'<b>':NULL)."���������</b></a></td>
	<td align=center><a href='?order_by=allow_rows'>".($_SESSION['adm']['loads']['order_by']=='allow_rows'?'<b>':NULL)."�����</b></a></td>
	<td align=center><a href='?order_by=allow_phones'>".($_SESSION['adm']['loads']['order_by']=='allow_phones'?'<b>':NULL)."���������</b></a></td>
	<td align=center><a href='?order_by=null_rows'>".($_SESSION['adm']['loads']['order_by']=='null_rows'?'<b>':NULL)."������</b></a></td>
	<td align=center><a href='?order_by=dublicates'>".($_SESSION['adm']['loads']['order_by']=='dublicates'?'<b>':NULL)."����������</b></a></td>
	<td align=center><a href='?order_by=status'>".($_SESSION['adm']['loads']['order_by']=='status'?'<b>':NULL)."������</b></a></td>";
	echo "</tr>";
	
	//������ ��������
	$q=OCIParse($c,"select t.id,to_char(t.start_date,'DD.MM.YYYY HH24:MI') start_date,t.file_name,t.file_size_bytes,
nvl(to_char(t.load_rows),'?')||' �� '||nvl(to_char(t.file_row_count),'?') load_rows,
nvl(to_char(t.load_phones),'?')||' �� '||nvl(to_char(t.found_phones),'?') load_phones,
t.allow_rows, allow_phones, 
t.null_rows, t.dublicates,t.status,t.del_rows
from STOR_LOAD_HISTORY t
where t.project_id='".$_SESSION['adm']['project']['id']."'
order by ".$_SESSION['adm']['loads']['order_by']);
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<tr class=selectable_row onclick=click_row(this,'sel')>
		<td>".(OCIResult($q,"STATUS")<>'����������������...'?"<input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'>":NULL)."</td>
		<td><b>".OCIResult($q,"ID")."</b></td>
		<td><b>".OCIResult($q,"START_DATE")."</b></td>
		<td><b>".OCIResult($q,"FILE_NAME")."</b></td>
		<td><b>".OCIResult($q,"FILE_SIZE_BYTES")."</b></td>
		<td><b>".OCIResult($q,"LOAD_ROWS")."</b></td>
		<td><b>".OCIResult($q,"LOAD_PHONES")."</b></td>
		<td><b>".OCIResult($q,"ALLOW_ROWS")."</b></td>
		<td><b>".OCIResult($q,"ALLOW_PHONES")."</b></td>		
		<td><b>".OCIResult($q,"NULL_ROWS")."</b></td>
		<td><b>".OCIResult($q,"DUBLICATES")."</b></td>
		<td>
		<a style='cursor:pointer' onClick=window.open('store.loads.show_load_data.php?load_id=".OCIResult($q,"ID")."&show_rows='+frm.show_rows.value) title='�������� ������'>".OCIResult($q,"STATUS")."</a>
		".(OCIResult($q,"STATUS")=='�����������...'?" <a href='?refresh' title='��������'><img onClick=this.style.display='none' src='gif/refresh.gif'></img></a>":NULL)."
		".(OCIResult($q,"STATUS")=='�������'?" �����: ".OCIResult($q,"DEL_ROWS"):NULL)."
		</td>";
		echo "</tr>";
	}
	echo "</table>";
	
//�����-�����. �����
echo "</div></td></tr><tr class=footer_tr><td>";

	echo "<div id=status_div></div>";
	echo "<hr>";
	echo "��������� ��������:<br>";
	echo "<input type=button name=xls value='��������� � .xls(HTML)' onclick=to_xls()></input> ";
	echo "<input type=button name=csv value='��������� � .csv' onclick=to_csv()></input> ";
	echo "<input type=button name=csv value='��������� �������� .txt' onclick=to_txt()></input> ";
	echo "<hr>";
	if($_SESSION['user']['rw_src_bd']<>'w') echo "<font color=red>�������������� ���������!</font>";
	else {
	echo "<input type=button name=allow value='�������� �������' onclick=allow_from_robot(this)></input> ";
	echo "<input type=button name=allow_all value='�������� ��' onclick=allow_all_()></input> ";
	echo "<input type=button name=disallow_all value='������������� ��' onclick=disallow_all_()></input> ";
	echo "<input type=button name=delete value='�������' onclick=del()></input> ";
	}
echo "</form>";

//�����-�����. �����
echo "</td></tr></table>";

?>

<iframe name="popUpFrame" style="border:1px solid;width:400px;height:250px;position:fixed;top:100%;left:100%;display:none" src="blank_page.php" scrolling="no" frameborder="yes"></iframe>
</body>
</html>

