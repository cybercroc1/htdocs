<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
header('X-UA-Compatible: IE=EmulateIE7');
$_SESSION['last_url']='edit_email.php';
?>
<HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0 and $_SESSION['admin']<>1) exit(); 
if ($_SESSION['project']['ch_email']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

//Функция сохранения значений
/*
if (isset($no_send)) {
$i=0;
while (@$_REQUEST['no_send'][$i]) {
if (!isset($_REQUEST[$no_send[$i]])) {
$upd = OCIParse($c,"update sc_form_email set send_online=null where id=".$no_send[$i]."");
OCIExecute($upd, OCI_DEFAULT);}

$i++;}
OCICommit($c);
}
*/
if (isset($email_form_id)) {

foreach($email_form_id as $id) {

if(isset($is_active[$id])) $send_online=1; else $send_online='';
if(isset($send_record_link[$id])) $send_record=1; else $send_record='';


$upd = OCIParse($c,"update sc_form_email set send_online='".$send_online."', send_record_link='".$send_record."' where id=".$id."");
OCIExecute($upd, OCI_DEFAULT);
}
}
OCICommit($c);

if (isset($go_save) and $form_id<>'send_aband' and $form_id<>'send_not_rep') {
$upd = OCIParse($c,"update sc_forms set post_url='".$post_url."' where id='".$form_id."' and project_id='".$_SESSION['project']['id']."'");
OCIExecute($upd, OCI_DEFAULT);
OCICommit($c);
/*echo "<script>document.location='edit_form.php?form_id=".$form_id."'</script>";*/
}
if (isset($go_save) and $form_id=='send_not_rep') {
	$upd = OCIParse($c,"update sc_projects set SEND_NOT_REP_TIMEOUT='".$not_rep_timeout."' where id='".$_SESSION['project']['id']."'");
	OCIExecute($upd, OCI_DEFAULT);
	OCICommit($c);
}

if (isset($new_email)) new_email($form_id,$new_email_name,$c);
if (isset($add_email)) {
	foreach($add_email as $key => $val) {
		new_email($form_id,$val,$c);
	}
}
if (isset($del_email)) del_email($form_id,$email_id,$c);
if (!isset($form_id)) $form_id='';

echo "<form action=edit_email.php method=post>";	
if ($_SESSION['project']['ch_form']==1) echo "<a href=edit_form.php?form_id=".$form_id.">Редактирование формы</a> ";
echo "| <font size=4>Редактирование е-мейлов</font><hr>";

//Выбор формы
	echo "<select name=form_id onchange=ch_form_id()>";
	echo "<option value=>ВЫБЕРИТЕ ФОРМУ</option>";
	if($office=='1905' and $_SESSION['project']['id']>0) {
		echo "<option value='send_aband'".($form_id=='send_aband'?' selected':'').">Уведомление о пропущенных звонках</option>";
		echo "<option value='send_not_rep'".($form_id=='send_not_rep'?' selected':'').">Уведомление о звонках без отчета</option>";
	}
	$post_url='';

	$q=OCIParse($c,"select f.id,f.post_url,f.name,p.SEND_NOT_REP_TIMEOUT from sc_forms f, sc_projects p
	where f.project_id=".$_SESSION['project']['id']." and f.deleted is null and f.id>0
	and p.id=f.project_id");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".($form_id==OCIResult($q,"ID")?' selected':'').">".OCIResult($q,"NAME")."</option>";
		if($form_id==OCIResult($q,"ID")) $post_url=OCIResult($q,"POST_URL");
		$not_rep_timeout=OCIResult($q,"SEND_NOT_REP_TIMEOUT");
	}
	echo "</select>
	<input type=submit name=ch_form value=ВЫБРАТЬ><hr>";
//
if(!isset($not_rep_timeout) or $not_rep_timeout=='') $not_rep_timeout='5'; 
if (isset($form_id) and $form_id<>'') {

	if($form_id<>'send_aband' and $form_id<>'send_not_rep') {
		echo "URL для отправки отчета методом POST <input type=text name=post_url value='".$post_url."' size='100'><hr>";
	}
	if($form_id=='send_not_rep') {
		echo "Время, после которого отчет считается не заполненным (мин) <input type=text name=not_rep_timeout value='".$not_rep_timeout."' size='20'><hr>";
	}
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white><b>E-mail</b></td>
	<td bgcolor=white><b>Активен</b></td>
	<td bgcolor=white><b>Отправлять запись разговора</b></td>
	<td bgcolor=white><input type=submit name=go_save value=СОХРАНИТЬ></td>";
	echo "</tr>";
	
	//Добавить email
	echo "<tr>
	<td bgcolor=green><input type=text name=new_email_name size=35></td>";
	echo "<td bgcolor=green align=center></td>";
	echo "<td bgcolor=green colspan=3><input type=submit name=new_email onclick=\"javascript:if(!chk_new_email()){alert('email неверен');return false;}\" value=ДОБАВИТЬ></td></tr>";
	//
	//Емейлы
	if($form_id=='send_aband' or $form_id=='send_not_rep') {
		$q=OCIParse($c,"
		select b.email, a.id, a.send_online,a.send_record_link from sc_form_email a,(select distinct (email) from sc_form_email where project_id='".$_SESSION['project']['id']."') b
		where a.std_type(+)='".$form_id."'
		and a.project_id(+)='".$_SESSION['project']['id']."'
		and b.email=a.email(+) order by a.send_online,b.email");
	} 
	else { 
		$q=OCIParse($c,"
		select b.email, a.id, a.send_online,a.send_record_link from sc_form_email a,(select distinct (email) from sc_form_email where project_id='".$_SESSION['project']['id']."') b
		where a.form_id(+)='".$form_id."'
		and a.project_id(+)='".$_SESSION['project']['id']."'
		and b.email=a.email(+) order by a.send_online,b.email");
	}
	OCIExecute($q,OCI_DEFAULT);
	echo "<tr>";
	while (OCIFetch($q)) {
	echo "<td bgcolor=white><b>".OCIResult($q,"EMAIL")."</b></td>";
	
    if(OCIResult($q,"ID")<>'') {
		echo "<td bgcolor=white align=center>";
		echo "<input type=hidden name=email_form_id[] value='".ociresult($q, "ID")."'>";
		echo "<input type=checkbox value=on name='is_active[".ociresult($q, "ID")."]'".(ociresult($q, "SEND_ONLINE")==1?" checked":"")."></td>";
		echo "<td bgcolor=white align=center>";
		echo "<input type=checkbox value=on name='send_record_link[".ociresult($q, "ID")."]'".(ociresult($q, "SEND_RECORD_LINK")==1?" checked":"")."></td>";		
		
		
		//if (ociresult($q, "SEND_ONLINE")==1) 
		//{echo "<td bgcolor=white align=center><input type=hidden name=no_send[] value=".ociresult($q, "ID")."><input type=checkbox name =".ociresult($q, "ID")." checked>";} 
		//else {echo "<td bgcolor=white align=center><input type=checkbox name=ye_send[] value=".ociresult($q, "ID").">";}
		//echo "</td>";

		//if (ociresult($q, "SEND_RECORD_LINK")==1) 
		//{echo "<td bgcolor=white align=center><input type=hidden name=send_record_link[] value=".ociresult($q, "ID")."><input type=checkbox name =".ociresult($q, "ID")." checked>";} 
		//else {echo "<td bgcolor=white align=center><input type=checkbox name=ye_send[] value=".ociresult($q, "ID").">";}
		//echo "</td>";		
		
		//echo "<td bgcolor=white align=center><input type=checkbox name=send_record_link[]".(ociresult($q, "SEND_RECORD_LINK")==1?" checked":"")." value=".ociresult($q, "ID").">";
		//echo "</td>";
		
		echo "<td bgcolor=white align=center>";
		echo "<a href=\"?del_email=1&email_id=".OCIResult($q,"ID")."&form_id=".$form_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";
		echo "</td>";
	}
	else {
		echo "<td bgcolor=white align=center><input type=checkbox name=add_email[] value='".OCIResult($q,"EMAIL")."'>";
		echo "</td>";

		echo "<td bgcolor=white align=center>";
		echo "</td>";
		
		echo "<td bgcolor=white align=center>";
		echo "<a href=\"?del_email=1&email_id=".OCIResult($q,"ID")."&form_id=".$form_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";
		echo "</td>";	
	}
	


	echo "</tr>";
	}
	echo "</table>";

	echo "<script language='javascript'>
	function chk_new_email() {
	 var reg = new RegExp('^[^\.][0-9a-z_\.\-]*@[0-9a-z_\.\-]*\.[a-z\-]$','i');
	if (reg.test(document.all.new_email_name.value)) return true; 
	}
	</script>";

}
echo "</form>";

//Функция добавления адреса
function new_email($form_id,$new_email_name,$c) {
	if($form_id=='send_aband' or $form_id=='send_not_rep') {
		$ins=OCIParse($c,"insert into sc_form_email (id,std_type,email,project_id,send_online)
		values (
		SEQ_EMAIL_ID.nextval,
		'".$form_id."',
		replace('".$new_email_name."',' ',''),
		'".$_SESSION['project']['id']."',
		'1')");
		OCIExecute($ins,OCI_DEFAULT);
		OCICommit($c);	
	}
	else {
		$ins=OCIParse($c,"insert into sc_form_email (id,form_id,email,project_id,send_online)
		values (
		SEQ_EMAIL_ID.nextval,
		'".$form_id."',
		replace('".$new_email_name."',' ',''),
		'".$_SESSION['project']['id']."',
		'1')");
		OCIExecute($ins,OCI_DEFAULT);
		OCICommit($c);	
	}
}
//
//Функция удаления адреса
function del_email($form_id,$email_id,$c) {
	if($form_id=='send_aband' or $form_id=='send_not_rep') {
		$del=OCIParse($c,"delete from sc_form_email 
		where project_id='".$_SESSION['project']['id']."' and std_type='".$form_id."' and id='".$email_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);	
	}
	else {
		$del=OCIParse($c,"delete from sc_form_email 
		where project_id='".$_SESSION['project']['id']."' and form_id='".$form_id."' and id='".$email_id."'");
		OCIExecute($del,OCI_DEFAULT);
		OCICommit($c);	
	}
}
//
?>
<script language='javascript'>
document.all.ch_form.style.display='none';
function ch_form_id() {
	document.all.ch_form.click();
}
</script>
