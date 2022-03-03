<?php 

ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
header('X-UA-Compatible: IE=edge');
$_SESSION['last_url']='edit_inject.php';
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="codemirror/lib/codemirror.css">
</head>
<script>
function ch_inject_id() {
	frm.ch_inject.click();
}
function del_inject(inject_id) {
	if (confirm('������������� ������ ������� ����� ?')) frm.del_inject.click();
}
</script>
<script src="codemirror/lib/codemirror.js"></script>
<script src="codemirror/addon/edit/matchbrackets.js"></script>
<script src="codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="codemirror/mode/xml/xml.js"></script>
<script src="codemirror/mode/javascript/javascript.js"></script>
<script src="codemirror/mode/css/css.js"></script>
<script src="codemirror/mode/clike/clike.js"></script>
<script src="codemirror/mode/php/php.js"></script>
<body>
<?php if ($_SESSION['admin']<>1) {echo "<font color=red>�������� ����������!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");

if(!isset($inject_id)) $inject_id='';

if(isset($del_inject)) {del_inject($inject_id,$c); $inject_id='';}
if(isset($save)) {
	if($inject_id=='') {
		$ins=OCIparse($c,"insert into SC_INJECTS (id,project_id,name,inj_code,window_left,window_top,window_width,window_height,bgcolor) 
		values (SEQ_INJECT_ID.nextval,'".$_SESSION['project']['id']."','".$inject_name."',EMPTY_CLOB(),:window_left,:window_top,:window_width,:window_height,:bgcolor)
		returning id,inj_code into :id,:inj_code");
		$inj_code_clob = oci_new_descriptor($c, OCI_D_LOB);
		OCIBindByName($ins, ":inj_code", $inj_code_clob, -1, OCI_B_CLOB);
		OCIBindByName($ins,":window_left",$window_left);
		OCIBindByName($ins,":window_top",$window_top);
		OCIBindByName($ins,":window_width",$window_width);
		OCIBindByName($ins,":window_height",$window_height);
		OCIBindByName($ins,":bgcolor",$bgcolor);

		OCIBindByName($ins,":id",$inject_id,16);
		OCIExecute($ins, OCI_DEFAULT);
		$inj_code_clob->save($inj_code);
		OCICommit($c);
	}
	else {
		$upd=OCIparse($c,"update SC_INJECTS 
		set name='".$inject_name."',
		inj_code=EMPTY_CLOB(),
		window_left=:window_left,
		window_top=:window_top,
		window_width=:window_width,
		window_height=:window_height,
		bgcolor=:bgcolor	
		where id='".$inject_id."' and project_id='".$_SESSION['project']['id']."'
		returning inj_code into :inj_code");	
		$inj_code_clob = oci_new_descriptor($c, OCI_D_LOB);
		OCIBindByName($upd,":inj_code", $inj_code_clob, -1, OCI_B_CLOB);
		OCIBindByName($upd,":window_left",$window_left);
		OCIBindByName($upd,":window_top",$window_top);
		OCIBindByName($upd,":window_width",$window_width);
		OCIBindByName($upd,":window_height",$window_height);
		OCIBindByName($upd,":bgcolor",$bgcolor);
		
		OCIExecute($upd, OCI_DEFAULT);
		$inj_code_clob->save($inj_code);
		OCICommit($c);
	}
	
} 

echo "<form name=frm method=post>";	
if ($_SESSION['project']['ch_form']==1) echo "<a href=edit_form.php>�������������� �����</a> ";
if ($_SESSION['project']['ch_email']==1) echo " | <a href=edit_email.php>�������������� �-������</a>";
echo "<font size=4> | ������� ����� (PHP-injects)</font>";
if ($_SESSION['admin']==1) echo " | <a href=edit_call_fields.php>�������������� ���� ������ </a>";
echo "<hr>";

//����� �����
	echo "<select name='inject_id' onchange='ch_inject_id()'>";
	echo "<option value=''>�������</option>";
	$q=OCIParse($c,"select i.id, i.name,i.inj_code,i.window_left,i.window_top,i.window_width,i.window_height,i.bgcolor from SC_INJECTS i
	where i.project_id=".$_SESSION['project']['id']." and id>0 and deleted is null order by name");
	OCIExecute($q,OCI_DEFAULT);
	$inject_name='';
	$inj_code='';
	$window_left='';
	$window_top='';
	$window_width='700';
	$window_height='600';
	$bgcolor='#F0E68C';
	while (OCIFetch($q)) {
		$selected='';
		if($inject_id==OCIResult($q,"ID")) {
			$selected=' selected';
			$inject_name=OCIResult($q,"NAME");
			if(OCIResult($q,"INJ_CODE")<>'') $inj_code=OCIResult($q,"INJ_CODE")->load(); else $inj_code='';
			$window_left=OCIResult($q,"WINDOW_LEFT");
			$window_top=OCIResult($q,"WINDOW_TOP");
			$window_width=OCIResult($q,"WINDOW_WIDTH");
			$window_height=OCIResult($q,"WINDOW_HEIGHT");
			$bgcolor=OCIResult($q,"BGCOLOR");
		}
		echo "<option value='".OCIResult($q,"ID")."'".$selected.">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>";
	if ($inject_id<>'') {
		echo " <a href=\"javascript:del_inject('".$inject_id."')\"><img src=del.gif title=\"������� �����\" border=0></a>";
	}	
	echo "<input type=submit name=ch_inject value=������� style='display:none'><input type=submit name=del_inject value=������� style='display:none'><hr>";
	
	echo "�������� <input type=text name=inject_name value='".$inject_name."' size='80'></select>";
	echo "<br>";
	echo "
	����:<input name=window_left type=text value='".$window_left."' size=5> | 
	����:<input name=window_top type=text value='".$window_top."' size=5> | 

	������:<input name=window_width type=text value='".$window_width."' size=5> | 
	������:<input name=window_height type=text value='".$window_height."' size=5> | 
	����:<input name=bgcolor type=text value='".$bgcolor."' size=10 style='background-color:".$bgcolor."'>
	<br>";
	
	echo "���. ";
	echo "<br><textarea id=inj_code name=inj_code rows=30 cols=150 style='wrap:nowrap;overflow:auto;'>". htmlspecialchars($inj_code)."</textarea><hr>";
	echo "<input type=submit name=save value='���������'>";
	echo "</form>";
	echo '�������� ������������ ��������� ����������:<br>
<b>����������� ��������� ������:</b><br>
<b>$call_id</b> - �����, ������������� ������, ��� �� ������������� ���������� �������� ��������<br>
<b>$blog_id</b> - ������������� �������� ����� �������� (� ��������� ��������� ������� "#")
<b>$date_call</b> - ���� ������ ��.��.���� (������ �������� ��������)<br>
<b>$time_call</b>  - ����� ������ ��:��:�� (������ �������� ��������)<br>
<b>$project_id</b> - �����, ������������� �������, ��� �� ��������<br>
<b>$aon</b> - ���  - ��������, ��������� �������� � ��������� aon ��� cgpn (�����.)<br> 
<b>$cgpn</b> - ���������� ������� $aon (������������ �� ����������)<br>
<b>$aon_norm</b> - ��������������� ��� � ������� 8���������� ��� 810����������.. (��� ��������). ���� ��� ������������� �� �������, �� �������� ���� ���������� ����� ������<br>	
<b>$aon_e164</b> - ��������������� ��� � ������� e164 +[���_������][�����]. ���� ��� ������������� �� �������, �� �������� ���� ���������� ����� ������<br>	
<b>$cdn</b> - ���������� ����� - ��������, ���������� �������� � ��������� cdn ��� cdpn(�����.)<br>
<b>$cdpn</b>  - ���������� ������� $cdn (������������ �� ����������)<br>
<b>$agid</b> - ��� ��������� ������<br>
<b>$uid</b> - ������, ID ��������� ������<br>
<b>$thrid</b> - ������, ������� ������������� ������ (idchain, ������)<br>
<b>$sip_call_id</b> - ������, SIP-������������� ������<br>
<b>$caller_name</b> - �������� �� SIP-��������� caller(name)<br> 
<b>$call_direction</b> - in,out,callback - ����������� ������ (�� ��������� in)<br>
<b>$tel_server_id</b> - ������������� ���������� ������� ���������<br>
<b>$project_name</b> - �������� �������<br>
<b>$url_to_project</b> - url � ������ �������<br>
<b>$aon_for_backcall</b> - ��� ��� ���������, ��������������� �� ������� �������� �����<br>
<b>$custom_data</b> - ��������� ����, ����� ��������� ����� ��, ��� �������� � ��������<br> 
<b>$oktell_task_id</b> - ������, ������������� ����� ������<br>
<b>$out_prefix</b> - ������� ��� ���������� ������<br>
<b>$dialed_number</b> - ����� ��� ���������� ������<br>
<hr><b>�������������� ���� ������:</b><br>
�������������� ���� ������ ����� ������������ �������� ������� POST ��� GET � ���� ������ ��� �������, � ����� ���� ��������, � ����� ����� � ���.<br>
������ ����� ��� ������� �������:';
$q=OCIParse($c,"select id,var_name,name from SC_CALL_FILEDS f where project_id='".$_SESSION['project']['id']."' and deleted is null order by ord");
OCIExecute($q);
while(OCIFetch($q)) {
	echo '<b>$call_data[\''.OCIResult($q,"VAR_NAME").'\']</b> - '.OCIResult($q,"NAME").'<br>';
}
echo "<hr><b>�������:</b><br>
<b>u8( ����� )</b> - ������������� ����� � UTF-8<br>
<b>cp( ����� )</b> - ������������� ����� � Windows-1251<br>
<b>set_additional_field( ��� ����, ����� �������� )</b> - ������������� ����� �������� ��������������� ���� ������. �������� � ���� ������� �� ��������������, ������ ������";	
//
//������� �������� �������
function del_inject($inject_id,$c) {
	$del=OCIParse($c,"update SC_INJECTS set deleted=sysdate 
	where project_id='".$_SESSION['project']['id']."' and id='".$inject_id."'");
	OCIExecute($del,OCI_DEFAULT);
	$del=OCIParse($c,"delete from SC_BODY where type='FV' and inject_id='".$inject_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}//
?>
<script>
var editor = CodeMirror.fromTextArea(document.getElementById("inj_code"), {
	lineNumbers: true,
	matchBrackets: true,
	mode: "application/x-httpd-php",
	indentUnit: 4,
	indentWithTabs: true
});
</script>

