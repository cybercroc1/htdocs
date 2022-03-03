<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Техподдержка Все-Свои</title>
</head>
<form name=frm method=post>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("sup/sup_conn_string");

echo "<font size=3><b>Группы. </b></font>";

//Добавление/переименование группы
if(isset($save_grp)) {
	if($lt_grp_id=='') {
		$q_ins=OCIParse($c,"insert into sup_lt_group (id,name,type)
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
//Удаление группы
if(isset($del_grp)) {
	if($lt_grp_id>0) {
		//$q_upd=OCIParse($c,"update sup_user set deleted=sysdate where lt_grp_id='".$lt_grp_id."'");
		$q_del=OCIParse($c,"delete from sup_lt_group where id='".$lt_grp_id."'");
		//OCIExecute($q_upd,OCI_DEFAULT);
		OCIExecute($q_del,OCI_DEFAULT);
		OCICommit($c);
		$lt_grp_id='';
	}
}
//

//Сохранение мест/проблем, входящих в группу
if(isset($save_lt)) {
	if($lt_grp_id>=0) {
		$q_del=OCIParse($c,"delete from sup_lt where lt_grp_id='".$lt_grp_id."'");
		OCIExecute($q_del,OCI_DEFAULT);
		if(isset($lt)) {
			$q_add=OCIParse($c,"insert into sup_lt (lt_grp_id,location_id,trbl_id) values ('".$lt_grp_id."',:location_id,:trbl_id)");		
			foreach($lt as $loc_trbl) {
				$loc_id=substr($loc_trbl,0,strpos($loc_trbl,':'));
				$trbl_id=substr($loc_trbl,strpos($loc_trbl,':')+1);
				OCIBindByName($q_add,":location_id",$loc_id);
				OCIBindByName($q_add,":trbl_id",$trbl_id);
				OCIExecute($q_add,OCI_DEFAULT);
			}
		}
		OCICommit($c);
	}
	//применяем супергруппу
	if($lt_grp_id>=-1) {
		$q_del=OCIParse($c,"delete from sup_lt where lt_grp_id=-1");
		OCIExecute($q_del,OCI_DEFAULT);	
		$q_add=OCIParse($c,"insert into sup_lt (location_id,trbl_id,lt_grp_id)
select k.id,t.id,-1 from sup_klinika k, sup_trbl_type t");		
		OCIExecute($q_add,OCI_DEFAULT);
		OCICommit($c);
	}
	
	//Применяем антигруппу
	$q_del=OCIParse($c,"delete from sup_lt slt
	where slt.lt_grp_id<>0
	and (slt.location_id,slt.trbl_id) in (select location_id,trbl_id from sup_lt where lt_grp_id=0)");
	OCIExecute($q_del,OCI_DEFAULT);
	OCICommit($c);
}
//

//Выбор группы
if(!isset($lt_grp_id)) $lt_grp_id='';
if(!isset($lt_grp_name)) $lt_grp_name='';
if(!isset($lt_grp_type)) $lt_grp_type='common';
if($lt_grp_id=='0') $checked_color='pink'; else $checked_color='palegreen';
	
echo "<nobr>Выберите группу: <select name=lt_grp_id onchange=document.all.ok.click()>";
echo "<option value='' style='color:green'>СОЗДАТЬ ГРУППУ</option>";
echo "<optgroup label='Общие (ниженерные)'></optgroup>";
$q=OCIParse($c,"select id,name,type from sup_lt_group where type='common' and id>0 order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID");
	if(OCIResult($q,"ID")==$lt_grp_id) {echo " selected"; $lt_grp_name=OCIResult($q,"NAME"); $lt_grp_type=OCIResult($q,"TYPE");}
	echo ">".OCIResult($q,"NAME")." (id:".OCIResult($q,"ID").")</option>";
}
echo "<optgroup label='Анонимные (операторские)'></optgroup>";
$q=OCIParse($c,"select id,name,type from sup_lt_group where type='oper_only' and id>0 order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID");
	if(OCIResult($q,"ID")==$lt_grp_id) {echo " selected"; $lt_grp_name=OCIResult($q,"NAME"); $lt_grp_type=OCIResult($q,"TYPE");}
	echo ">".OCIResult($q,"NAME")." (id:".OCIResult($q,"ID").")</option>";
}
echo "<optgroup label='Ососбые'></optgroup>";
$q=OCIParse($c,"select id,name,type from sup_lt_group where id<1 order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID");
	if(OCIResult($q,"ID")==$lt_grp_id) {echo " selected"; $lt_grp_name=OCIResult($q,"NAME"); $lt_grp_type=OCIResult($q,"TYPE");}
	echo ">".OCIResult($q,"NAME")." (id:".OCIResult($q,"ID").")</option>";
}
echo "</select>
<input type=submit name=ok value=ВЫБРАТЬ>";

if (isset($lt_grp_id) and $lt_grp_id<>'' and $lt_grp_id<>'0') {
	echo " <a href=\"javascript:del_grp('".$lt_grp_id."')\"><img src=del.gif title=\"Удалить текущую группу\" border=0></a> | ";
}
echo "</nobr>";

if($lt_grp_id=='') {
	echo " <nobr>название: <input type=text name=new_lg_grp_name onkeyup='if(this.value==\"\"){save_grp.disabled=true;}else{save_grp.disabled=false;}'></nobr>";
	echo " <nobr>тип: ";
	echo "<input type=radio name=new_type value='common' checked>общая(инженерная) | </input>
	      <input type=radio name=new_type value='oper_only'>Анонимная (операторская) </input>";
	echo " <input type=submit name='save_grp' disabled style='background:green' value='Сохранить'>";
	echo "</nobr>";
}
else if($lt_grp_id>'0') {
	echo " <nobr>переименовать: <input type=text name=new_lg_grp_name value='".$lt_grp_name."' onkeyup='if(this.value==\"\"){save_grp.disabled=true;}else{save_grp.disabled=false;}'>";
	echo " <input type=submit name='save_grp' disabled style='background:green' value='Сохранить'>";
	echo "</nobr>";
}
//
if($lt_grp_type=='oper_only') {
		echo "<br><font size=3>Эта группа используется только для <b>не авторизованного приема заявок (операторы, менеджеры)</b></font><br>";
		echo "<font color=blue>".$lt_grp_name." - http://sup.wilstream.ru/new_order.php?lt_oper_grp=".$lt_grp_id."</font><br>";
	}
if($lt_grp_type=='anti') {
	echo "<br><font size=3>В этой группе задаются исключения <b>(проблемы и места, которые не могу пересекаться друг с другом)</b></font>";
	}
echo "<hr>";

if($lt_grp_id<>'') {
if($lt_grp_id<>'0') {
//пользователи
echo "пользователи группы: ";
$q=OCIParse($c,"select g.id group_id, g.name group_name, r.id role_id, r.role_name,au.* from (

select u.id,u.fio,a.lt_group_id,u.admin,u.registrar,a.create_new,a.solution,a.deny_close,a.redirect,a.look,a.eval,a.rep_stat,
a.em_new,a.em_redir,a.em_prisv,a.em_ready,a.em_delay,a.em_close,a.em_coment,
a.sm_new,a.sm_redir,a.sm_prisv,a.sm_delay,a.sm_ready,a.sm_close 
from SUP_USER_LT_ALLOC a, SUP_USER u
where u.id=a.user_id and u.deleted is null and a.lt_group_id='".$lt_grp_id."' 

) au, SUP_LT_GROUP g, sup_role_pattern r
where g.id(+)=au.lt_group_id
and nvl(au.look,'n')      =  nvl(r.look(+),'n')
and nvl(au.solution,'n')  =  nvl(r.solution(+),'n')
and nvl(au.redirect,'n')  =  nvl(r.redirect(+),'n')
and nvl(au.eval,'n')      =  nvl(r.eval(+),'n')
and nvl(au.deny_close,'n')=  nvl(r.deny_close(+),'n')
and nvl(au.create_new,'n')=  nvl(r.create_new(+),'n')
and nvl(au.rep_stat,'n')  =  nvl(r.rep_stat(+),'n')
and nvl(au.em_new,'n')    =  nvl(r.em_new(+),'n')
and nvl(au.em_coment,'n') =  nvl(r.em_coment(+),'n')
and nvl(au.em_redir,'n')  =  nvl(r.em_redir(+),'n')
and nvl(au.em_prisv,'n')  =  nvl(r.em_prisv(+),'n')
and nvl(au.em_delay,'n')  =  nvl(r.em_delay(+),'n')
and nvl(au.em_ready,'n')  =  nvl(r.em_ready(+),'n')
and nvl(au.em_close,'n')  =  nvl(r.em_close(+),'n')
and nvl(au.sm_new,'n')    =  nvl(r.sm_new(+),'n')
and nvl(au.sm_redir,'n')  =  nvl(r.sm_redir(+),'n')
and nvl(au.sm_prisv,'n')  =  nvl(r.sm_prisv(+),'n')
and nvl(au.sm_delay,'n')  =  nvl(r.sm_delay(+),'n')
and nvl(au.sm_ready,'n')  =  nvl(r.sm_ready(+),'n')
and nvl(au.sm_close,'n')  =  nvl(r.sm_close(+),'n') 

order by au.fio");
OCIExecute($q,OCI_DEFAULT);
$i=0; while(OCIFetch($q)) {
	$i++; if($i==1) {
		echo "Пользователи группы:";
		echo "<table class=white_table>";
		echo "<tr>
		<th>ФИО</th>
		<th>Роль</th>
		<th>Обозреватель</th>
		<th>Исполнитель</th>
		<th>Стрелочник</th>
		<th>Оценщик</th>
		<th>Заявитель</th>
		<th>Запрещено закрывать</th>
		<th>Создана</th>
		<th>Комментарий</th>
		<th>Переадресовано</th>
		<th>Принято в работу</th>
		<th>Отложено</th>
		<th>Готово к проверке</th>
		<th>Закрыта</th>
		</tr>";
	}
	echo "<tr>
	<td align=center><b>".OCIResult($q,"FIO")."</b></td>
	<td align=center><b>".OCIResult($q,"ROLE_NAME")."</b></td>
	<td align=center><b>".OCIResult($q,"LOOK")."</b></td>
	<td align=center><b>".OCIResult($q,"SOLUTION")."</b></td>
	<td align=center><b>".OCIResult($q,"REDIRECT")."</b></td>
	<td align=center><b>".OCIResult($q,"EVAL")."</b></td>
	<td align=center><b>".OCIResult($q,"CREATE_NEW")."</b></td>
	<td align=center><b>".OCIResult($q,"DENY_CLOSE")."</b></td>
	</tr>";
}
if($i>0) echo "</table>";
else echo "<font color=red>данная группа не назначена ни одному пользователю</font>";
echo "<hr>";
}
//

//Таблица мест и локаций
echo "выбор мест и типов проблем, входящих в группу: ";
//Массивы мест (выбираем все места, кроме удаленных, но если удаленные назначенны выбранной группе, то удаленные тоже с пометкой 'deleted').

$q=OCIParse($c,"select distinct slg.id location_grp_id, slg.name location_grp_name, sk.id location_id, sk.name location_name, decode(sk.deleted,null,null,'deleted') deleted
from sup_klinika sk, sup_lt slt, sup_location_group slg
where slt.lt_grp_id(+)='".$lt_grp_id."'
and slt.location_id(+)=sk.id
and slg.id=sk.location_grp_id
and (slt.lt_grp_id is not null or sk.deleted is null)
order by slg.name, sk.name");
OCIExecute($q,OCI_DEFAULT);
$temp='';
$i=0; while(OCIFetch($q)) {$i++;
	if($temp<>OCIResult($q,'LOCATION_GRP_ID')) {
		$location_ids[$i]='';
		$location_grp_ids[$i]=OCIResult($q,'LOCATION_GRP_ID'); 
		$location_grp_names[$i]=OCIResult($q,'LOCATION_GRP_NAME');
		$i++;
	}
	$location_grp_ids[$i]=OCIResult($q,'LOCATION_GRP_ID'); 
	$location_ids[$i]=OCIResult($q,'LOCATION_ID');
	$location_names[$i]=OCIResult($q,'LOCATION_NAME');
	if(OCIResult($q,'DELETED')=='deleted') $location_deleted[$i]='deleted';
	$temp=OCIResult($q,'LOCATION_GRP_ID');
}
$temp='';
//

//Массивы проблем (выбираем все проблеммы, кроме удаленных, но если удаленные назначенны выбранной группе, то удаленные тоже с пометкой 'deleted').

$q=OCIParse($c,"select distinct stg.id trbl_grp_id, stg.name trbl_grp_name, stt.id trbl_id, stt.name trbl_name, stt.ord, stt.color, decode(stt.deleted,null,null,'deleted') deleted
from sup_trbl_type stt, sup_lt slt, sup_trbl_group stg
where slt.lt_grp_id(+)='".$lt_grp_id."'
and slt.trbl_id(+)=stt.id
and stg.id=stt.trbl_grp_id
and (slt.lt_grp_id is not null or stt.deleted is null)
order by stg.name, stt.ord nulls first, stt.name");
OCIExecute($q,OCI_DEFAULT);
$temp='';
$i=0; while(OCIFetch($q)) {$i++;
	if($temp<>OCIResult($q,'TRBL_GRP_ID')) {
		$trbl_ids[$i]='';
		$trbl_grp_ids[$i]=OCIResult($q,'TRBL_GRP_ID'); 
		$trbl_grp_names[$i]=OCIResult($q,'TRBL_GRP_NAME');
		$i++;
	}
	$trbl_grp_ids[$i]=OCIResult($q,'TRBL_GRP_ID');
	$trbl_ids[$i]=OCIResult($q,'TRBL_ID');
	$trbl_names[$i]=OCIResult($q,'TRBL_NAME');
	$trbl_colors[$i]=OCIResult($q,'COLOR');
	if(OCIResult($q,'DELETED')=='deleted') $trbl_deleted[$i]='deleted';
	$temp=OCIResult($q,'TRBL_GRP_ID');
}
$temp='';
//
echo "<table class=white_table>";

echo "<tr><td bgcolor='#E5E5E5'></td><th bgcolor=white colspan='".(count($trbl_ids)+2)."'>Проблемы</th>
<td bgcolor='#E5E5E5'></td></tr>";

echo "<th bgcolor=white rowspan='".(count($location_ids)+2)."'>М<br>е<br>с<br>т<br>а</th>
<td bgcolor='#E5E5E5' align=right valign=bottom><input type=submit style='background:green' name=save_lt value='сохранить'></td>";
foreach($trbl_ids as $key => $trbl_id) {
	if($trbl_id=='') echo "<th bgcolor='#E5E5E5' nowrap valign=bottom><font style='LAYOUT-FLOW: vertical-ideographic;-webkit-transform: rotate(90deg)'>".$trbl_grp_names[$key]."</font></th>";
	else {
		echo "<td bgcolor=white nowrap valign=bottom><font style='LAYOUT-FLOW: vertical-ideographic'";
		if(isset($trbl_deleted[$key])) echo "<td bgcolor=white nowrap valign=bottom title='Эта проблема УДАЛЕНА!'>
			<font color=red style='LAYOUT-FLOW: vertical-ideographic'>".$trbl_names[$key]."</font></td>";
		else echo "<td bgcolor=white nowrap valign=bottom><font color='".$trbl_colors[$key]."' style='LAYOUT-FLOW: vertical-ideographic'>".$trbl_names[$key]."</font></td>";
	}
}
echo "<td bgcolor='#E5E5E5' align=left valign=bottom><input type=submit style='background:green' name=save_lt value='сохранить'></td>";
echo "<th bgcolor=white rowspan='".(count($location_ids)+2)."'>М<br>е<br>с<br>т<br>а</th>";
echo "</tr>";
$q=OCIParse($c,"select * from sup_lt where location_id=:location_id and trbl_id=:trbl_id and lt_grp_id='".$lt_grp_id."'");

foreach($location_ids as $key => $loc_id) {
	echo "<tr>";
	if($loc_id=='') echo "<th bgcolor='#E5E5E5' nowrap align=right>".$location_grp_names[$key]."</th>";
	else {
		if(isset($location_deleted[$key])) echo "<td bgcolor=white nowrap align=right title='Это место УДАЛЕНО!'><font color=red>".$location_names[$key]."</font></td>";
		else echo "<td bgcolor=white nowrap align=right>".$location_names[$key]."</td>";
	}
	foreach($trbl_ids as $key2 => $trbl_id) {
		$checked=''; $bgcolor='white'; $onclick='ch_lt(this)'; $deleted='';
		if($trbl_id<>'' and $loc_id<>'') {
			OCIBindByName($q,":location_id",$loc_id); OCIBindByName($q,":trbl_id",$trbl_id);
			OCIExecute($q,OCI_DEFAULT);	if(OCIFetch($q)) {$checked=" checked"; $bgcolor=$checked_color;}			
			if(isset($location_deleted[$key]) or isset($trbl_deleted[$key2])) {$bgcolor='pink'; $onclick=''; $deleted=" deleted";}
			echo "<td bgcolor='".$bgcolor."' onmouseover='sel(this)' onmouseout='unsel(this)'>
			<input type=checkbox name=lt[] value='".$loc_id.":".$trbl_id."'".$deleted." loc_grp='".$location_grp_ids[$key]."' trbl_grp='".$trbl_grp_ids[$key2]."' loc_id='".$loc_id."' trbl_id='".$trbl_id."'".$checked." onclick='".$onclick."'>";
		}
		else {
			echo "<td bgcolor='#E5E5E5' loc_grp='".$location_grp_ids[$key]."' trbl_grp='".$trbl_grp_ids[$key2]."' loc_id='".$loc_id."' trbl_id='".$trbl_id."' onmouseover='sel(this)' onmouseout='unsel(this)' onclick='grayclick(this)'>";
		}
	echo "</td>";	
	}
	if($location_ids[$key]=='') echo "<th bgcolor='#E5E5E5' nowrap align=left>".$location_grp_names[$key]."</th>";
	else {
		if(isset($location_deleted[$key])) echo "<td bgcolor=white nowrap align=left title='Это место УДАЛЕНО!'><font color=red>".$location_names[$key]."</font></td>";
		else echo "<td bgcolor=white nowrap align=left>".$location_names[$key]."</td>";
	}
	echo "</tr>";	
}
echo "<tr>";
foreach($trbl_ids as $key => $trbl_id) {
	if($key==1) echo "<td bgcolor='#E5E5E5' align=right valign=top><input type=submit style='background:green' name=save_lt value='сохранить'></td>";
	if($trbl_id=='') echo "<th bgcolor='#E5E5E5' nowrap valign=top><font style='LAYOUT-FLOW: vertical-ideographic'>".$trbl_grp_names[$key]."</font></th>";
	else {
		if(isset($trbl_deleted[$key])) echo "<td bgcolor=white nowrap valign=top title='Эта проблема УДАЛЕНА!'>
			<font color=red style='LAYOUT-FLOW: vertical-ideographic'>".$trbl_names[$key]."</font></td>";
		else echo "<td bgcolor=white nowrap valign=top><font style='LAYOUT-FLOW: vertical-ideographic'>".$trbl_names[$key]."</font></td>";	
	}
}
echo "<td bgcolor='#E5E5E5' align=left valign=top><input type=submit style='background:green' name=save_lt value='сохранить'></td>";
echo "</tr>";
echo "<tr><td bgcolor='#E5E5E5'></td><th bgcolor=white colspan='".(count($trbl_ids)+2)."'>Проблемы</th><td bgcolor='#E5E5E5'></td></tr>";
echo "</table>";
//
//
}
echo "</form>";
if($lt_grp_id==0) echo "<script language='javascript'>checked_color='pink';</script>";
else echo "<script language='javascript'>checked_color='palegreen';</script>";
?>
<script language="javascript">
document.all.ok.style.display='none';
function del_grp(lt_grp_id) {
	if (confirm('Действительно хотите УДАЛИТЬ ТЕКУЩУЮ ГРУППУ ?')) {
		var obj=document.createElement('input');
		obj.name='del_grp'; frm.appendChild(obj);
		frm.submit();
	}
}
function sel(cell) {
	tbl=cell.parentNode.parentNode;
	row=cell.parentNode;
	x=cell.cellIndex;
	y=row.rowIndex;
	sel_color='Lightblue';

	tbl.rows[1].cells[x+1].def_color=tbl.rows[1].cells[x+1].bgColor;
	tbl.rows[1].cells[x+1].bgColor=sel_color;

	tbl.rows[tbl.rows.length-2].cells[x].def_color=tbl.rows[tbl.rows.length-2].cells[x].bgColor;
	tbl.rows[tbl.rows.length-2].cells[x].bgColor=sel_color;

	tbl.rows[y].cells[0].def_color=tbl.rows[y].cells[0].bgColor;
	tbl.rows[y].cells[0].bgColor=sel_color;

	tbl.rows[y].cells[row.cells.length-1].def_color=tbl.rows[y].cells[row.cells.length-1].bgColor;
	tbl.rows[y].cells[row.cells.length-1].bgColor=sel_color;
}
function unsel(cell) {
	tbl=cell.parentNode.parentNode;
	row=cell.parentNode;
	x=cell.cellIndex;
	y=row.rowIndex;

	tbl.rows[1].cells[x+1].bgColor=tbl.rows[1].cells[x+1].def_color;
	tbl.rows[tbl.rows.length-2].cells[x].bgColor=tbl.rows[tbl.rows.length-2].cells[x].def_color;
	tbl.rows[y].cells[0].bgColor=tbl.rows[y].cells[0].def_color;
	tbl.rows[y].cells[row.cells.length-1].bgColor=tbl.rows[y].cells[row.cells.length-1].def_color;
}

function ch_lt(obj) {
	if(obj.checked==true) {
		obj.parentNode.bgColor=checked_color;
	}
	else {
		obj.parentNode.bgColor='white';
	}
}
function grayclick(cell) {
	f='';
	with(document.all.frm) {
		for(i=0; i<elements.length; i++) {
			if(
			(cell.loc_id=='' && cell.trbl_id=='' && elements[i].loc_grp==cell.loc_grp && elements[i].trbl_grp==cell.trbl_grp)||
			(elements[i].loc_id==cell.loc_id && elements[i].trbl_grp==cell.trbl_grp)||
			(elements[i].trbl_id==cell.trbl_id && elements[i].loc_grp==cell.loc_grp)
			) {
				if(f!='y') {
					if(elements[i].checked==false) {
						cell.checked=true;
						checked=true;
						bgColor=checked_color;
						f='y';
					}
					else if (elements[i].checked==true) {
						cell.checked=false;
						checked=false;
						bgColor='white';
						f='y';
					}
				}
				elements[i].checked=checked;
				if(!('deleted' in elements[i])) elements[i].parentNode.bgColor=bgColor;
			
			}
		}
	}
}
</script>