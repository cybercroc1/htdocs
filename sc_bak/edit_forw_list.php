<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
$_SESSION['last_url']='edit_forw_list.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if (!isset($fio_on)) $fio_on='';
if (!isset($doljnost_on)) $doljnost_on='';
if (!isset($email_on)) $email_on=''; 
if (!isset($grafik_on)) $grafik_on='';
if (!isset($coment_on)) $coment_on='';
if (!isset($otdel_on)) $otdel_on='';
if (!isset($head_text)) $head_text='';
if (isset($save)) $list_id=save_list($list_id,$list_name,$order_by,$fio_on,$doljnost_on,$email_on,$grafik_on,$coment_on,$otdel_on,$row_count,$head_text,$c);
if (!isset($list_name)) $list_name='';
if (!isset($order_by)) $order_by='';

if (!isset($fio)) $fio='';
if (!isset($doljnost)) $doljnost='';
if (!isset($email)) $email=''; 
if (!isset($grafik)) $grafik='';
if (!isset($coment)) $coment='';
if (!isset($otdel)) $otdel='';

if (isset($add_fio)) add_fio($list_id,$fio,$doljnost,$phone,$ext,$email,$grafik,$coment,$otdel,$c);
if (isset($del_fio)) del_fio($fio_id,$c);
if (isset($del_list)) {del_list($list_id,$c); $list_id='';}
if (isset($del_phone)) del_phone($phone_id,$c);
if (isset($add_phone)) add_phone($fio_id,$phone,$ext,$ordering,$c);

if (isset($fio_up)) fio_up($list_id,$fio_id,$ordering,$c);
if (isset($fio_down)) fio_down($list_id,$fio_id,$ordering,$c);

if (isset($phone_up)) phone_up($fio_id,$phone_id,$ordering,$c);
if (isset($phone_down)) phone_down($fio_id,$phone_id,$ordering,$c);

if (isset($sort_fio)) sort_fio($list_id,$column,$c);

echo "<form action=edit_forw_list.php method=post>"; //POST работает некорректно!
	echo "<font size=4>Редактирование списка переадресации</font><br>";

//Выбор списка
	echo "<select name=list_id onchange=document.all.ch_list.click()>";
	if (!isset($list_id) or $list_id=='') {
	echo "<option value=''>СОЗДАТЬ СПИСОК</option>";
	$name='';
	$order_by='';
	$doljnost_on='';
	$email_on=''; 
	$grafik_on='';
	$coment_on='';
	$otdel_on='';
	$row_count='';	
	}
	else {
	
	$q=OCIParse($c,"select 
t.id,replace(t.name,'\"','&quot;') name,t.order_by,t.project_id,
t.email,t.otdel,t.grafik,t.coment,
t.doljnost,t.row_count,t.fio,replace(t.head_text,'\"','&quot;') head_text 
from sc_forw_list t where t.id='".$list_id."'");
	
	//$q=OCIParse($c,"select * from sc_forw_list where id='".$list_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	$name=OCIResult($q,"NAME");
	$order_by=OCIResult($q,"ORDER_BY");
	$fio_on=OCIResult($q,"FIO");
	$doljnost_on=OCIResult($q,"DOLJNOST");
	$grafik_on=OCIResult($q,"GRAFIK");
	$otdel_on=OCIResult($q,"OTDEL");
	$email_on=OCIResult($q,"EMAIL");
	$coment_on=OCIResult($q,"COMENT");
	$row_count=OCIResult($q,"ROW_COUNT");
	$head_text=OCIResult($q,"HEAD_TEXT");				
	echo "<option value=''>СОЗДАТЬ СПИСОК</option>";
	}
	$q=OCIParse($c,"select * from sc_forw_list where project_id='".$_SESSION['project']['id']."' order by name");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>
	<input type=submit name=ch_list value=ВЫБРАТЬ>";
		if (isset($list_id) and $list_id<>'') {
		echo " <a href=\"javascript:del_list('".$list_id."')\"><img src=del.gif title=\"Удалить список\" border=0></a>";
		}
	echo "<hr>";
//

echo "<table>";
echo "<tr><td><font size=3><b>Название:</b></font></td><td><input type=text size=105 name=list_name value=\"".$name."\" onchange=ch_list_name()></td></tr>";
echo "<tr><td><font size=3><b>Сортировка:</b></font></td><td><select name=order_by onchange=ch_order_by()>
<option value=\"".$order_by."\">".$order_by."</option>
<option value=\"случайно\">случайно</option>
<option value=\"по кругу\">по кругу</option>
<option value=\"как есть\">как есть</option>
</select>
</td>
<tr id=tr_row_count><td><font size=3><b>Отображать записей:</b></font></td><td><select name=row_count>
<option>".$row_count."</option>
<option value=''>Все</option><option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option><option>9</option><option>10</option>
</select>
</td>
</tr>
<tr><td><font size=3><b>Поля таблицы:</b></font></td><td>";
echo " ФИО<input type=checkbox name=fio_on "; if($fio_on=='1') echo "checked "; echo "value=1>";
echo " отдел<input type=checkbox name=otdel_on "; if($otdel_on=='1') echo "checked "; echo "value=1>";
echo " должность<input type=checkbox name=doljnost_on "; if($doljnost_on=='1') echo "checked "; echo "value=1>";
echo " график работы<input type=checkbox name=grafik_on "; if($grafik_on=='1') echo "checked "; echo "value=1>";
echo " комментарий<input type=checkbox name=coment_on "; if($coment_on=='1') echo "checked "; echo "value=1>";
echo " e-mail<input type=checkbox name=email_on "; if($email_on=='1') echo "checked "; echo "value=1>";
echo "</td></tr>";
echo "</table>";
echo "<font size=3 color=black><b>Комментарий в шапке списка:</b></font><br>
<textarea cols=100 rows=5 name=head_text>".$head_text."</textarea><br>";
echo "<input type=submit name=save value=Сохранить><hr>";	

if (isset($list_id) and $list_id<>'') {
	//Сотрудники

	echo "<font size=4>Сотрудники</font><br>";
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<tr>
	<td bgcolor=white></td>";
	echo "<td bgcolor=white align=center><a href=\"?sort_fio=1&list_id=".$list_id."&column=3\"><b>ФИО</b></a></td>";
	if ($otdel_on=='1') echo "<td bgcolor=white align=center><a href=\"?sort_fio=1&list_id=".$list_id."&column=8\"><b>Отдел</b></a></td>";
	if ($doljnost_on=='1') echo "<td bgcolor=white align=center><a href=\"?sort_fio=1&list_id=".$list_id."&column=4\"><b>Должность</b></a></td>";
	if ($grafik_on=='1') echo "<td bgcolor=white align=center><b>График работы</b></td>";
	if ($coment_on=='1') echo "<td bgcolor=white align=center><a href=\"?sort_fio=1&list_id=".$list_id."&column=10\"><b>Комментарий</b></a></td>";
	if ($email_on=='1') echo "<td bgcolor=white align=center><b>E-mail</b></td>";
	echo "<td bgcolor=white align=center><b>Тел.номера</b></td>
	<td bgcolor=white align=center><b>Доб.</b></td>";	
	echo "<td bgcolor=white></td>";

	echo "</tr>";

	//Добавить сотрудника
	echo "<tr>";
	echo "<td bgcolor=green></td>";
	echo "<td bgcolor=green align=center><textarea name=fio cols=30 rows=3></textarea></td>";
	if ($otdel_on=='1') echo "<td bgcolor=green align=center><textarea name=otdel cols=20 rows=3></textarea></td>";
	if ($doljnost_on=='1') echo "<td bgcolor=green align=center><textarea name=doljnost cols=20 rows=3></textarea></td>";
	if ($grafik_on=='1') echo "<td bgcolor=green align=center><textarea name=grafik cols=20 rows=3></textarea></td>";
	if ($coment_on=='1') echo "<td bgcolor=green align=center><textarea name=coment cols=40 rows=3></textarea></td>";
	if ($email_on=='1') echo "<td bgcolor=green align=center><textarea name=email cols=20 rows=3></textarea></td>";
	echo "<td bgcolor=green align=center><input type=text name=phone></td>
	<td bgcolor=green align=center><input type=text size=2 name=ext></td>";
	echo "<td bgcolor=green><input type=submit name=add_fio value=\"Добавить\"></td></tr>";
	//
	//Готовим запрос на получение номеров
	$q_phone=OCIParse($c,"select id,name||decode(ext,null,'',' доб. '||ext) name, ordering from sc_forw_phone 
	where fio_id=:fio_id and project_id='".$_SESSION['project']['id']."' 
order by ordering");	
	//
	
	$q=OCIParse($c,"select * from sc_forw_fio 
	where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."' 
order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		$v_id=OCIResult($q,"ID");
		OCIBindByName($q_phone,":fio_id",$v_id);
		echo "<tr>";
		echo "<td bgcolor=white width=3>";
		echo "<a href=\"?fio_up=1&fio_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&list_id=".$list_id."\"><img border=0 src=up.gif></a>";
		echo "<a href=\"?fio_down=1&fio_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&list_id=".$list_id."\"><img border=0 src=down.gif></a>";		
		echo "</td>";
		if (OCIResult($q,"FIO")=='') echo "<td bgcolor=white><b><a href=\"edit_forw_phones.php?list_id=".$list_id."&fio_id=".OCIResult($q,"ID")."\"><img border=0 src=edit.gif></a></b></td>";
		else echo "<td bgcolor=white><b><a href=\"edit_forw_phones.php?list_id=".$list_id."&fio_id=".OCIResult($q,"ID")."\">".OCIResult($q,"FIO")."</a></b></td>";
		if ($otdel_on=='1') echo "<td bgcolor=white align=center><b>".OCIResult($q,"OTDEL")."</b></td>";		
		if ($doljnost_on=='1') echo "<td bgcolor=white align=center><b>".OCIResult($q,"DOLJNOST")."</b></td>";
		if ($grafik_on=='1') echo "<td bgcolor=white align=center><b>".OCIResult($q,"GRAFIK")."</b></td>";
		if ($coment_on=='1') echo "<td bgcolor=white align=center><b>".OCIResult($q,"COMENT")."</b></td>";
		if ($email_on=='1') echo "<td bgcolor=white align=center><b>".OCIResult($q,"EMAIL")."</b></td>";
		echo "<td bgcolor=white colspan=2>";
		OCIExecute($q_phone,OCI_DEFAULT);
		$i=0;
		while(OCIFetch($q_phone)) {
			echo "<a href=\"javascript:add_phone(".OCIResult($q,"ID").",".$list_id.",".OCIResult($q_phone,"ORDERING").")\"><img src=add_leaf.gif title=\"Добавить телефон\" border=0></a>";
			echo "<a href=\"?phone_up=1&phone_id=".OCIResult($q_phone,"ID")."&ordering=".OCIResult($q_phone,"ORDERING")."&list_id=".$list_id."&fio_id=".OCIResult($q,"ID")."\"><img border=0 src=up.gif></a>";
			echo "<a href=\"?phone_down=1&phone_id=".OCIResult($q_phone,"ID")."&ordering=".OCIResult($q_phone,"ORDERING")."&list_id=".$list_id."&fio_id=".OCIResult($q,"ID")."\"><img border=0 src=down.gif></a> ";			
			echo "<b>".OCIResult($q_phone,"NAME")."</b> ";
			echo "<a href=\"?del_phone=1&phone_id=".OCIResult($q_phone,"ID")."&list_id=".$list_id."\"><img src=del.gif title=\"Удалить телефон\" border=0></a><br>";
		$i++;
		}
		if ($i==0) echo "<a href=\"javascript:add_phone(".OCIResult($q,"ID").",".$list_id.",1)\"><img src=add_leaf.gif title=\"Добавить телефон\" border=0></a>";
		echo "</td>";
		echo "<td bgcolor=white align=center>";
		echo "<a href=\"?del_fio=1&list_id=".$list_id."&fio_id=".OCIResult($q,"ID")."\"><img src=del.gif title=\"Удалить сотрудника\" border=0></a>";
		echo "</td></tr>";
		
	}
echo "</table>";
}
	//

echo "</form>";

//Функция добавления и изменения списка
function save_list($list_id,$name,$order_by,$fio_on,$doljnost_on,$email_on,$grafik_on,$coment_on,$otdel_on,$row_count,$head_text,$c) {
	if ($order_by<>'по кругу' and $order_by<>'случайно') {$row_count='';}
	
	if ($list_id=='') {
	$q=OCIParse($c,"select seq_list_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_list_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_forw_list (id,name,order_by,project_id,fio,doljnost,email,grafik,coment,otdel,row_count,head_text) 
	values ('".$new_list_id."','".$name."','".$order_by."','".$_SESSION['project']['id']."','".$fio_on."','".$doljnost_on."','".$email_on."','".$grafik_on."','".$coment_on."','".$otdel_on."','".$row_count."','".$head_text."')");
	if (OCIExecute($ins,OCI_DEFAULT)) { 
	OCICommit($c);
	$list_id=$new_list_id;
	}
	else {echo "<font color=red>ОШИБКА! Список с таким именем уже существует!</font>";}
	}
	else {
	$upd=OCIParse($c,"update sc_forw_list set name='".$name."', order_by='".$order_by."',
	fio='".$fio_on."', doljnost='".$doljnost_on."', email='".$email_on."', grafik='".$grafik_on."', coment='".$coment_on."', otdel='".$otdel_on."', row_count='".$row_count."', head_text='".$head_text."' 
	where id='".$list_id."' and project_id='".$_SESSION['project']['id']."'");
	if (OCIExecute($upd,OCI_DEFAULT)) {OCICommit($c);}
	else {echo "<font color=red>ОШИБКА! Список с таким именем уже существует!</font>";}
	}
return $list_id;
}
//

//Функция удаления списка
function del_list($list_id,$c) {
	$del=OCIParse($c,"delete from sc_forw_list where id='".$list_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//

//Функция добавления сотруднка
function add_fio($list_id,$fio,$doljnost,$phone,$ext,$email,$grafik,$coment,$otdel,$c) {
	if ($fio=='') $fio=$phone;
	$sel=OCIParse($c,"select seq_forw_fio.nextval from dual");
	OCIExecute($sel,OCI_DEFAULT);
	OCIFetch($sel);
	$fio_id=OCIResult($sel,"NEXTVAL");
	$sel=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_forw_fio where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($sel,OCI_DEFAULT);
	OCIFetch($sel);
	$ordering=OCIResult($sel,"ORDERING");	
	$ins=OCIParse($c,"insert into sc_forw_fio (id,list_id,fio,doljnost,ordering,project_id,grafik,email,coment,otdel) 
	values ('".$fio_id."','".$list_id."','".$fio."','".$doljnost."','".$ordering."','".$_SESSION['project']['id']."','".$grafik."','".$email."','".$coment."','".$otdel."')");
	OCIExecute($ins,OCI_DEFAULT);
		if ($phone<>'') {
		$ins2=OCIParse($c,"insert into sc_forw_phone (id,fio_id,phone,ext,name,ordering,project_id) 
		values (seq_forw_phones.nextval,'".$fio_id."',
		regexp_replace('".$phone."','[^0-9]',''),
		'".$ext."',
		'".$phone."',
		'1','".$_SESSION['project']['id']."')"); 
		OCIExecute($ins2,OCI_DEFAULT);
		}
	OCICommit($c);
}
//
//Функция удаления сотрудника
function del_fio($fio_id,$c) {
	$del=OCIParse($c,"delete from sc_forw_fio where id='".$fio_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//
//Функция удаления телефона
function del_phone($phone_id,$c) {
	$del=OCIParse($c,"delete from sc_forw_phone where id='".$phone_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del,OCI_DEFAULT); 
	OCICommit($c);
}
//
//функция добавления телефона
function add_phone($fio_id,$phone,$ext,$ordering,$c) {

$upd=OCIParse($c,"update sc_forw_phone set ordering=ordering+1 
where fio_id='".$fio_id."' and ordering>'".$ordering."' and project_id='".$_SESSION['project']['id']."'");
OCIExecute($upd,OCI_DEFAULT);

$ins=OCIParse($c,"insert into sc_forw_phone (id,fio_id,phone,ext,name,ordering,project_id) 
values (seq_forw_phones.nextval,'".$fio_id."',
regexp_replace('".$phone."','[^0-9]',''),
'".$ext."',
'".$phone."',
'".($ordering+1)."','".$_SESSION['project']['id']."')");
OCIExecute($ins,OCI_DEFAULT);
OCICommit($c);
}
//

//Функция ФИО вверх
	function fio_up($list_id,$fio_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ordering),1) perv_ordering from sc_forw_fio
where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update sc_forw_fio set ordering='".$ordering."'
	where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update sc_forw_fio set ordering='".$perv_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$fio_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//
//Функция ФИО вниз
	function fio_down($list_id,$fio_id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_forw_fio
where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."' and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_forw_fio set ordering='".$ordering."'
		where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_forw_fio set ordering='".$next_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$fio_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//
//Функция телефон вверх
	function phone_up($fio_id,$phone_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ordering),1) perv_ordering from sc_forw_phone
where fio_id='".$fio_id."' and project_id='".$_SESSION['project']['id']."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$perv_ordering=OCIResult($q,"PERV_ORDERING");
	$upd=OCIParse($c,"update sc_forw_phone set ordering='".$ordering."'
	where fio_id='".$fio_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$perv_ordering."'");
	OCIExecute($upd,OCI_DEFAULT);
	$upd2=OCIParse($c,"update sc_forw_phone set ordering='".$perv_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$phone_id."'");
	OCIExecute($upd2,OCI_DEFAULT);
	OCICommit($c);
	}
//
//Функция телефон вниз
	function phone_down($fio_id,$phone_id,$ordering,$c) {
	
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_forw_phone
where fio_id='".$fio_id."' and project_id='".$_SESSION['project']['id']."' and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if (OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_forw_phone set ordering='".$ordering."'
		where fio_id='".$fio_id."' and project_id='".$_SESSION['project']['id']."' and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_forw_phone set ordering='".$next_ordering."' where project_id='".$_SESSION['project']['id']."' and id='".$phone_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//
//Функция сортировки
function sort_fio($list_id,$column,$c) {
	$q=OCIParse($c,"select * from sc_forw_fio where list_id='".$list_id."' order by ".$column." nulls first");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++;
		$upd=OCIParse($c,"update sc_forw_fio set ordering='".$i."' where id='".OCIResult($q,"ID")."'");
		OCIExecute($upd,OCI_DEFAULT);
	}
	OCICommit($c);
}
//
?>
<script language="javascript">
document.all.ch_list.style.display='none';
ch_list_name();
ch_order_by();
function ch_order_by() {
if (document.all.order_by.value=='случайно'||document.all.order_by.value=='по кругу') {document.all.tr_row_count.style.display='';}
else {document.all.tr_row_count.style.display='none';}
}
function ch_list_name() {
if (document.all.list_name.value=='') {document.all.save.disabled=true;}
else {document.all.save.disabled=false;}
}
function ch_list() {
if (document.all.project_id.value=='') {document.all.add_project.disabled=true;}
else {document.all.add_project.disabled=false;}
}
function del_list(list_id) {
if (confirm('Действительно хотите УДАЛИТЬ СПИСОК ПЕРЕАДРЕСАЦИИ ?')) document.location='?del_list=1&list_id='+list_id;
}
function add_phone(fio_id,list_id,ordering) {
	if (document.all.phone.value||'') {
	document.location='?add_phone=1&fio_id='+fio_id+'&ordering='+ordering+'&list_id='+list_id+'&phone='+document.all.phone.value+'&ext='+document.all.ext.value;
	} 
}
</script>