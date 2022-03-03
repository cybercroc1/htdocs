<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script src="func.row_select.js"></script>
<body class="body_marign">
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if(!isset($order_by) and !isset($_SESSION['users_order_by'])) $_SESSION['users_order_by']='login';
else if(isset($order_by)) $_SESSION['users_order_by']=$order_by;
if(!isset($find_string)) $find_string='';

echo "<form name=frm>";

//хедер-футер. •…„…ђ
echo "<table class=content_table><tr class=header_tr><td>";

echo "<font size=4>Пользователи</font> ";
echo "<a href=\"javascript:add_usr()\"><img src=plus.png title=\"Создать пользователя\" border=0></a> ";
echo "поиск: <input type=text name=find_string value='".$find_string."' onkeyup=fn_find(this.value); onpaste=fn_find(this.value); onchange=fn_find(this.value); title='Введите не менее 2-х символов и подождите 2 секунды. Будет выполнен поиск совпадений по всем полям.'> ";

//•едер-футер. ЉЋЌ’…Ќ’
//echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";

echo "<table id='tbl_head' class='white_table'>
<tr>
<th>".($_SESSION['users_order_by']=='login'?'<b>Логин *</b>':'<a href=?order_by=login>Логин</a>')."</td>
<th>".($_SESSION['users_order_by']=='fio'?'<b>ФИО *</b>':'<a href=?order_by=fio>ФИО</a>')."</td>
<th>".($_SESSION['users_order_by']=='description'?'<b>Описание *</b>':'<a href=?order_by=description>Описание</a>')."</td>";
echo "<th></th>";
echo "</tr>";
echo "</table>";

//•едер-футер. ЉЋЌ’…Ќ’
echo "</td></tr><tr class=content_tr><td><div class=content_div>";

echo "<table id='tbl' class='white_table'>";

$q_where='';
if ($find_string<>"") $q_where=" and ( 
   upper(replace(login,' ')) like '%'||upper(replace('".$find_string."',' '))||'%' 
or upper(replace(description,' ')) like '%'||upper(replace('".$find_string."',' '))||'%' 
or upper(replace(fio,' ')) like '%'||upper(replace('".$find_string."',' '))||'%' 
) ";

$q=OCIParse($c,"select id,login, description,fio from sc_login where id<>1 
".$q_where."
order by ".$_SESSION['users_order_by']);
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	if(isset($_SESSION['edit_login']['id']) and OCIResult($q,"ID")==$_SESSION['edit_login']['id']) $class='selected_row'; else $class='selectable_row'; 
	echo "<tr class='".$class."' onclick=\"click_row(this,'sel');sel_user('".$_SESSION['adm_usr_last_url']."','".OCIResult($q,"ID")."');\">";
	echo "<td><b>".OCIResult($q,"LOGIN")."</b></td>";
	echo "<td><b>".OCIResult($q,"FIO")."</b></td>";
	echo "<td>".OCIResult($q,"DESCRIPTION")."</td>";
	echo "<td>";
	echo "<a href=\"javascript:del_usr('".OCIResult($q,"ID")."')\"><img src=del.gif title=\"Удалить пользователя\" border=0></a>";
	echo "</td>";
	echo "</tr>";
}
echo "</table>";

//•едер-футер. ”“’…ђ
echo "</div></td></tr><tr class=footer_tr><td>";

echo "</form>";
//•едер-футер. ЉЋЌ…–
echo "</td></tr></table>";


?>
<script>
var t;
head_width();
window.onresize = function() {
	head_width();
}
function head_width() {
	for(i=0; i<document.all.tbl.rows[0].cells.length; i++) {
	head_row=document.getElementById('tbl_head').rows[0];
	content_row=document.getElementById('tbl').rows[0];
		//alert(document.all.tbl.rows[0].cells[i].clientLeft);
		head_row.cells[i].width = content_row.cells[i].clientWidth-(content_row.cells[i].clientLeft*2);
	}
}
function fn_find(val) {
	clearTimeout(t);
	if(val.length==0 || val.length>=2) t=setTimeout('frm.submit()',2000);
}
function sel_user(url,id) {
	//alert(parent.adm_usr_fr2.location.host+parent.adm_usr_fr2.location.pathname);
	parent.adm_usr_fr2.location=parent.adm_usr_fr2.location.pathname+'?login_id='+id;
}
function add_usr(login_id) {
	parent.adm_usr_fr2.location='adm_usr_main.php?add_usr&login_id';
}
function del_usr(login_id) {
	if (confirm('Действительно хотите УДАЛИТЬ ПОЛЬЗОВАТЕЛЯ ?')) parent.adm_usr_fr2.location='adm_usr_main.php?del_usr&login_id='+login_id;
}
</script>
