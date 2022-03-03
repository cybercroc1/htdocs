<?php include("../../conf/starcall_conf/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<script>
function sel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.toggle('selected_row');
		
}}
function unsel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.remove('selected_row');
}}
function to_xls() {
	frm.action='adm.loads.loads.exp_xls.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function to_csv() {
	frm.action='adm.loads.loads.exp_csv.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function to_txt() {
	frm.action='adm.loads.loads.exp_txt.php';
	frm.target='logFrame';
	frm.submit();
	frm.action=null;	
	frm.target=null;
}
function allow_all_() {
	if(confirm('Будут одобрены все записи и телефоны из выбранных загрузок. Продолжить?')) {
		frm.action='adm.loads.loads.allow_all.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='Одобрение...';
		//блокируем все элементы формы
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function disallow_all_() {
	if(confirm('Будут заблокированы все записи и телефоны из выбранных загрузок. Продолжить?')) {
		frm.action='adm.loads.loads.disallow_all.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='Блокировка...';
		//блокируем все элементы формы
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function del() {
	if(confirm('Будут удалены только необработанные (с пустым статусом) и не заблокированные оператором записи из выбранных загрузок. Удалить?')) {
		frm.action='adm.loads.loads.delete.php';
		frm.target='logFrame';
		frm.submit();
		frm.action=null;	
		frm.target=null;
		document.getElementById('status_div').innerHTML='Удаление...';
		//блокируем все элементы формы
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}	
	}
}
function allow_from_robot(obj) {
	obj.disabled=true;
	//document.body.style.overflow = 'hidden'; //запрет прокрутки документа
	if(document.all.popUpFrame.style.display=='none') {
		topOffset=240; //смещение окна вверх от позиции курсора
		document.all.popUpFrame.style.left=event.clientX+'px';
		if(event.clientY<topOffset) {
			document.all.popUpFrame.style.top='10px';	
		}
		else {
			document.all.popUpFrame.style.top=event.clientY-topOffset+'px';
		}
		document.all.popUpFrame.style.display='';
		frm.action='adm.loads.loads.allow_robot.php';
		frm.target='popUpFrame';
		frm.submit();
		frm.action=null;
		frm.target=null;
		
		//блокируем все элементы формы
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

include("../../conf/starcall_conf/conn_string.cfg.php");

echo "<form method=post action=null name=frm>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

isset($show_rows)?NULL:$show_rows=100;
isset($load_id)?$_SESSION['adm']['load_id']=$load_id:NULL;

echo " | ";
echo "<a href='adm.loads.loads.php'><font size=4>Менеджер загрузок</font></a> | ";
echo "<a href='adm.loads.fields.php'>Настройка исх. полей</a> | ";
echo "<a href='adm.loads.load_data.php'>Загрузка из .CSV</a> | ";
echo "<font align=right><a href='help.adm.loads.loads.html' target='_blank'>Справка</a></font>";
echo "<hr>";

//обновляем статистику
if(isset($refresh)) {
	$upd=OCIParse($c,"update STC_LOAD_HISTORY h 
	set 
	(h.load_rows,h.allow_rows)=(select count(*),count(allow) from STC_BASE where project_id=h.project_id and load_hist_id=h.id),
	(h.load_phones,h.allow_phones)=(select count(*),count(allow) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id)
	where (status='Загружается...' or status is null) and project_id='".$_SESSION['adm']['project']['id']."'");
	OCIExecute($upd); OCICommit($c);
}
//

$q=OCIParse($c,"select name from STC_PROJECTS t
where id='".$_SESSION['adm']['project']['id']."'");
OCIExecute($q); OCIFetch($q); $project_name=OCIResult($q,"NAME");

echo "<font size=4>Загрузки. Проект: ".$project_name." (id: ".$_SESSION['adm']['project']['id'].").</font><br>"; 

echo "<hr>";

if(!isset($order_by) and !isset($_SESSION['adm']['loads']['order_by'])) $_SESSION['adm']['loads']['order_by']='t.start_date desc';
if(isset($order_by)) $_SESSION['adm']['loads']['order_by']=$order_by;
	
	echo "<input type=hidden name=load_id value=''>";
	echo "Показывать строк: <input type=text name='show_rows' value='100'> |  <nobr>Выгружать только одобренные <input type=checkbox name=allowed_only>";
	echo "<input type=submit name=show_data style='display:none' value='показать данные'>";
	echo "<hr>";	
	
//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";	
	
	echo "<table id=tbl>
	<tr><td colspan=3></th><th colspan=2>Файл</th><th colspan=2>Загружено</th><th colspan=2>Одобрено</th><th colspan=2>Отклонено при загрузке</th><th></th></tr>
	<tr>
	<td align=center></th>
	<td align=center><b>ID</b></th>
	<td align=center><a href='?order_by=t.start_date desc'>".($_SESSION['adm']['loads']['order_by']=='t.start_date desc'?'<b>':NULL)."Дата загр.</b></a></td>
	<td align=center><a href='?order_by=t.file_name'>".($_SESSION['adm']['loads']['order_by']=='t.file_name'?'<b>':NULL)."Имя</b></a></td>
	<td align=center><a href='?order_by=t.file_size_bytes'>".($_SESSION['adm']['loads']['order_by']=='t.file_size_bytes'?'<b>':NULL)."Байт</b></a></td>
	<td align=center><a href='?order_by=load_rows'>".($_SESSION['adm']['loads']['order_by']=='load_rows'?'<b>':NULL)."Cтрок</b></a></td>
	<td align=center><a href='?order_by=load_phones'>".($_SESSION['adm']['loads']['order_by']=='load_phones'?'<b>':NULL)."Телефонов</b></a></td>
	<td align=center><a href='?order_by=allow_rows'>".($_SESSION['adm']['loads']['order_by']=='allow_rows'?'<b>':NULL)."Строк</b></a></td>
	<td align=center><a href='?order_by=allow_phones'>".($_SESSION['adm']['loads']['order_by']=='allow_phones'?'<b>':NULL)."Телефонов</b></a></td>
	<td align=center><a href='?order_by=null_rows'>".($_SESSION['adm']['loads']['order_by']=='null_rows'?'<b>':NULL)."Пустых</b></a></td>
	<td align=center><a href='?order_by=dublicates'>".($_SESSION['adm']['loads']['order_by']=='dublicates'?'<b>':NULL)."Дубликатов</b></a></td>
	<td align=center><a href='?order_by=status'>".($_SESSION['adm']['loads']['order_by']=='status'?'<b>':NULL)."Статус</b></a></td>";
	echo "</tr>";
	
	//Список загрузок
	$q=OCIParse($c,"select t.id,to_char(t.start_date,'DD.MM.YYYY HH24:MI') start_date,t.file_name,t.file_size_bytes,
nvl(to_char(t.load_rows),'?')||' из '||nvl(to_char(t.file_row_count),'?') load_rows,
nvl(to_char(t.load_phones),'?')||' из '||nvl(to_char(t.found_phones),'?') load_phones,
t.allow_rows, allow_phones, 
t.null_rows, t.dublicates,t.status,t.del_rows
from STC_LOAD_HISTORY t
where t.project_id='".$_SESSION['adm']['project']['id']."'
order by ".$_SESSION['adm']['loads']['order_by']);
	
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>
		<td>".(OCIResult($q,"STATUS")<>'ЫЫЫЫЫЗагружается...'?"<input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'>":NULL)."</td>
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
		<a style='cursor:pointer' onClick=window.open('adm.loads.show_load_data.php?load_id=".OCIResult($q,"ID")."&show_rows='+frm.show_rows.value) title='показать данные'>".OCIResult($q,"STATUS")."</a>
		".(OCIResult($q,"STATUS")=='Загружается...'?" <a href='?refresh' title='обновить'><img onClick=this.style.display='none' src='gif/refresh.gif'></img></a>":NULL)."
		".(OCIResult($q,"STATUS")=='Удалено'?" строк: ".OCIResult($q,"DEL_ROWS"):NULL)."
		</td>";
		echo "</tr>";
	}
	echo "</table>";
	
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";	

	echo "<div id=status_div></div>";
	echo "<hr>";
	echo "Выбранные загрузки:<br>";
	echo "<input type=button name=xls value='Выгрузить в .xls(HTML)' onclick=to_xls()></input> ";
	echo "<input type=button name=csv value='Выгрузить в .csv' onclick=to_csv()></input> ";
	echo "<input type=button name=csv value='Выгрузить телефоны .txt' onclick=to_txt()></input> ";
	echo "<hr>";
	if($_SESSION['user']['rw_src_bd']<>'w') echo "<font color=red>Редактирование запрещено!</font>";
	else {
	echo "<input type=button name=allow value='Одобрить роботом' onclick=allow_from_robot(this)></input> ";
	echo "<input type=button name=allow_all value='Одобрить всё' onclick=allow_all_()></input> ";
	echo "<input type=button name=disallow_all value='Заблокировать всё' onclick=disallow_all_()></input> ";
	echo "<input type=button name=delete value='Удалить' onclick=del()></input> ";
	}
echo "</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>

<iframe name="popUpFrame" style="border:1px solid;width:400px;height:250px;position:fixed;top:100%;left:100%;display:none" src="blank_page.php" scrolling="no" frameborder="yes"></iframe>
</body>
</html>

