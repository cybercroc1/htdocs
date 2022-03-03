<?php
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
set_time_limit(120);
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<iframe name="ifr_edit_table" style="display:'none'"></iframe>
<?php if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['ch_sc'][$_SESSION['i']]<>1 or !isset($_SESSION['login_id'])) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<script src="edit_table.js"></script>
<body leftmargin="3" topmargin="3" onUnload="wTools.close()" onMouseOver="if(vInputObj==null){window.focus()}">
<form name="frm_edit_table" method="post">
<?php

include("../../sc_conf/sc_conn_string");

extract($_REQUEST);
if(!isset($table_id)) $table_id='';
if($table_id=='') $table_name='';
if(!isset($table_name)) $table_name='';
if(!isset($template_id) or $table_id<>'') $template_id='';
if($template_id<>'') $table_id=$template_id;

if(!isset($general)) $general='n';
if(!isset($shedule_id)) $shedule_id='';

$project_id=$_SESSION['project_id'][$_SESSION['i']];
$login_id=$_SESSION['login_id'];
//$table_id_on_change='document.location="?table_id="+this.value';
//$template_id_on_change='document.location="?template_id="+this.value';

//-------------------------------------------------------------
if(isset($add_blog) or isset($edit_blog)) {
	if(!isset($blog_id)) $blog_id='';
	if(isset($add_blog)) {
		echo "<font size=4>Добавление блока</font>";
		echo ' тип: <select name=blog_type onchange=\'document.location="edit_body.php?blog_type="+this.value+"&add_blog&general='.$general.'&punkt_id='.$punkt_id.'&tree_id='.$tree_id.'&ordering='.$ordering.'"\'>';
		$q=OCIParse($c,"select * from sc_blog_type");
		OCIExecute($q,OCI_DEFAULT);
			while (OCIFetch($q)) {
			echo "<option value=".OCIResult($q,"ID");
			if(OCIResult($q,"ID")=='DT') echo " selected";
			echo ">".OCIResult($q,"NAME")."</option>";
		}
		echo "</select>";
		//echo "<input type=hidden name=add_blog>";
	}
	elseif(isset($edit_blog)) {
		echo "<font size=4>Редактирование блока</font>";
		echo "<input type=hidden name=edit_blog>";
	}
	echo "<br>";
	echo "<table>";
	echo "<tr><td align>Расписание:</td>";
	echo "<td>";
	echo "<select name=shedule_id>";
	$q=OCIParse($c,"select b.shedule_id,name,max(decode(b.id,'".$blog_id."',' selected',decode(b.shedule_id,'".$shedule_id."',' selected',null))) selected
from sc_shedule s, sc_body b where s.project_id='".$project_id."'
and b.shedule_id=s.id
	 group by b.shedule_id,name
   order by name");
	echo "<option value=''>Виден всегда</option>";
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"SHEDULE_ID").OCIResult($q,"SELECTED").">".OCIResult($q,"NAME")."</option>";
//		if(isset($shedule_id) and $shedule_id==OCIResult($q,"SHEDULE_ID")) echo " selected";
	}
	echo "</select>";
	echo "</td></tr>";

echo "<td valign=top>Номера доступа: </td><td>";

$q=OCIParse($c,"select count(*) cnt from SC_BODY_CGPN where body_id='".$blog_id."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
if(OCIResult($q,"CNT")>0) {
	$q=OCIParse($c,"select p.phone,decode(c.cgpn,null,null,'checked ') chk from SC_PHONES p,
(select * from SC_BODY_CGPN where body_id='".$blog_id."') c 
where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' 
and p.phone=c.cgpn(+)
order by p.phone");
}
else {
	$q=OCIParse($c,"select p.phone,'checked ' chk from SC_PHONES p 
where project_id='".$_SESSION['project_id'][$_SESSION['i']]."' 
order by p.phone");
	}

OCIExecute($q,OCI_DEFAULT);
	$iii=0;
	while (OCIFetch($q)) {
	$iii++;
	echo "<input type=checkbox name=cgpns[$iii] ".OCIResult($q,"CHK")."value=".OCIResult($q,"PHONE").">".OCIResult($q,"PHONE")."</input>";
	//if(($iii/2)==round($iii/2)) echo "<br>";
	//else 
	echo " | ";
	}
echo "<input type=hidden name=cgpns_count value='$iii'></td></tr>";

echo "<td valign=top>АОНы: </td><td>";
	$q=OCIParse($c,"select aon from SC_BODY_AONS where body_id='".$blog_id."'
order by aon");

OCIExecute($q,OCI_DEFAULT);
	$AONs="";
	while (OCIFetch($q)) {
		$AONs.=OCIResult($q,"AON").",";
	}
echo "<input type=text name=aons value='".trim($AONs,",")."'></input> несколько АОНов указывать через запятую";
echo "</td></tr>";

echo "<td valign=top>Тональный набор: </td><td>";
	$q=OCIParse($c,"select tonedial from SC_BODY_TONEDIAL where body_id='".$blog_id."'");

OCIExecute($q,OCI_DEFAULT);
	$tonedials="";
	while (OCIFetch($q)) {
		$tonedials.=OCIResult($q,"TONEDIAL").",";
	}
echo "<input type=text name=tonedials value='".trim($tonedials,",")."'></input> \"null\" - ничего не набрано, несколько значений указывать через запятую";
echo "</td></tr>";

	echo "<tr><td colspan=2>";
	echo "<input type=checkbox"; 
	if ($general=='y') echo " checked";	
	echo " name=general value='y'>";
	echo "общий для всех пунктов</td></tr>";
	echo "</table>";
	echo "<input type=hidden name=edit_blog>";
	echo "<input type=hidden name=blog_id value='".$blog_id."'>";
	echo "<input type=hidden name=punkt_id value='".$punkt_id."'>";
	echo "<input type=hidden name=tree_id value='".$tree_id."'>";
	echo "<input type=hidden name=ordering value='".$ordering."'>";	
echo "<hr>";
}
//--------------------------------------------

echo "<font size=4>Редактирование таблицы</font><br>";

	echo "<select name=table_id onchange='with(frm_edit_table){save.disabled=true;save_templ.disabled=true;action=\"edit_table.php\";target=\"_self\";submit();}'>";	
	echo "
	<option style='color:green' value=''>СОЗДАТЬ ТАБЛИЦУ</option>";
	$q=OCIParse($c,"select id,name from sc_dynamic_table where project_id='".$project_id."' order by name");
	OCIExecute($q,OCI_DEFAULT);
	while(OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'";
		if(OCIResult($q,"ID")==$table_id) {
			echo " selected";
			$table_name=OCIResult($q,"NAME");
		}	
		echo ">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select> | ";
	if($table_id<>'' and $template_id=='' and !isset($edit_blog)) echo "<a href=\"javascript:del_table('".$table_id."')\"><img src=del.gif title=\"Удалить ТАБЛИЦУ\" border=0></a> | ";
echo "<nobr><font size=3> имя таблицы/шаблона:</font><input type=text name='table_name' value='".$table_name."'></nobr>";

if (count($_SESSION['project_id'])>1 and !isset($edit_blog) and !isset($add_blog)) {
echo "<nobr> | сохранить в другой проект: <select name=other_project_id onchange='vChanged=\"y\";'>";
echo "<option value=''>Выберите проект</option>";
for($i=0; $i<count($_SESSION['project_id']); $i++) {
echo "<option value=".$i.">".$_SESSION['project_name'][$i]."</option>";
}
echo "</select></nobr>";
}


echo "<hr>";
echo '<input type="button" disabled name="save" onClick="fSave(\'table\')" value="СОХРАНИТЬ">';

echo '
 | <font size=3><a href="javascript:this.onclick" onClick="fShowTools()">Инструменты</a>';

if($table_id=='' or $template_id<>'') {
echo "
 | шаблоны: <select name=template_id onchange='with(frm_edit_table){save.disabled=true;save_templ.disabled=true;action=\"edit_table.php\";target=\"_self\";submit();}'>";
echo "
<option style='color:green' value=''>Создать шаблон</option>";
$q=OCIParse($c,"select id,name from sc_dynamic_table where project_id is null and login_id='".$login_id."' order by name");
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	echo "<option value='".OCIResult($q,"ID")."'";
	if(OCIResult($q,"ID")==$table_id) {
		echo " selected";
		$table_name=OCIResult($q,"NAME");
	}	
	echo ">".OCIResult($q,"NAME")."</option>";
}
echo "</select>";
	if($template_id<>'' and !isset($edit_blog)) echo " | <a href=\"javascript:del_template('".$template_id."')\"><img src=del.gif title=\"Удалить ШАБЛОН\" border=0></a>";
}
echo '
 | <input type="button" disabled name="save_templ" onClick="fSave(\'template\')" value="Сохранить шаблон">';

echo "<hr>";
if($table_id<>'') {
$q=OCIParse($c,"select id,name,style,attrib,col_count+1 cols from sc_dynamic_table
where id='".$table_id."' and (project_id='".$project_id."' or login_id='".$login_id."')");
OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		echo '<table id="tbl"'.OCIResult($q,"ATTRIB").' cols="'.OCIResult($q,"COLS").'" style="'.OCIResult($q,"STYLE").'" title="Что бы выделить ячейку кликните на ней, удерживая клавишу CTRL">';
		$table_id=OCIResult($q,"ID");
	}
	else {exit();}
$q_row=OCIParse($c,"select row_num,attrib,style,height from sc_dynamic_table_rows
where table_id='".$table_id."' order by row_num");

$q_cell=OCIParse($c,"select cell_num,attrib,style,nvl(html,''),html,faq_id,width from sc_dynamic_table_cells
where table_id='".$table_id."' and row_num=:row_num
order by cell_num");

OCIExecute($q_row,OCI_DEFAULT);
	while(OCIFetch($q_row)) {
		$row_num=OCIResult($q_row,"ROW_NUM");
		if($row_num==0) $height=20;
		else $height=OCIResult($q_row,"HEIGHT");
		echo '<tr'.OCIResult($q_row,"ATTRIB").' style="height:'.$height.';'.OCIResult($q_row,"STYLE").'">';
		OCIBindByName($q_cell,":row_num",$row_num);
		OCIExecute($q_cell,OCI_DEFAULT);
			while(OCIFetch($q_cell)) {
				if(OCIResult($q_cell,"CELL_NUM")==0 and $row_num==0) {
					echo '<td real_bgcolor="white" real_fontsize="10px" real_lineheight="12px" real_fontcolor="black" style="width:30; background:white; color:; font-size:10px; line-height:12px; vertical-align:middle; text-align:center;" is_selected="n" unselectable="on" faq="" phones="n" onClick="fCellSelect(this.parentNode.rowIndex,this.cellIndex)"></td>
';
				}
				elseif($row_num==0) {
					echo '<td real_bgcolor="white" real_fontsize="10px" real_lineheight="12px" real_fontcolor="black" style="width:'.OCIResult($q_cell,"WIDTH").'; background:white; color:; font-size:10px; line-height:12px; vertical-align:middle; text-align:center;" is_selected="n" unselectable="on" faq="" onMouseDown="fHeadMouseDown(this)" onMouseUp="fHeadMouseUp(this)" onMouseMove="fHeadMouseMove(this)" onMouseOut="fHeadMouseUp(this)">'.OCIResult($q_cell,"WIDTH").'</td>';
				}
				elseif(OCIResult($q_cell,"CELL_NUM")==0) {
					echo '<td real_bgcolor="white" real_fontsize="10px" real_lineheight="12px" real_fontcolor="black" style="background:white; color:; font-size:10px; line-height:12px; vertical-align:middle; text-align:center;" is_selected="n" unselectable="on" faq="" onMouseDown="fHeadMouseDown(this)" onMouseUp="fHeadMouseUp(this)" onMouseMove="fHeadMouseMove(this)" onMouseOut="fHeadMouseUp(this)">'.$height.'</td>';
				}				
				else {
					echo '<td'.OCIResult($q_cell,"ATTRIB").' style="'.OCIResult($q_cell,"STYLE").'" faq="'.OCIResult($q_cell,"FAQ_ID").'" is_selected="n" unselectable="on" edited="n" onmousedown="fCellClick(this)">';
					if(OCIResult($q_cell,"HTML")<>'') echo OCIResult($q_cell,"HTML")->load();
					echo '</td>';
				}
			}
		echo '</tr>';	
	}
}	

if(!isset($table_id) or $table_id=='') {
echo '<table id=tbl bgcolor="#666666" real_bgcolor="#666666" cellspacing="1" cellpadding="3" border="0" align="" style="table-layout:fixed" title="Что бы выделить ячейку кликните на ней, удерживая клавишу CTRL">';
echo '<tr>
<td real_bgcolor="white" real_fontsize="12px" real_lineheight="16px" real_fontcolor="black" style="background:white; color:; width:30; height:20; font-size:10px; line-height:12px; vertical-align:top; text-align:left;" is_selected="n" faq="" phones="n" onClick="fCellSelect(this.parentNode.rowIndex,this.cellIndex)"></td>
</tr>';
}
if($template_id<>'') echo "<script>vChanged='y'; frm_edit_table.table_name.value='".$table_name."';</script>";
else echo "<script>vChanged=null;</script>";
?>
</table>
</form>
</body>
</html>
<script language="javascript"> 
tbl.rows[0].cells[0].innerHTML=tbl.style.tableLayout;
if(tbl.rows.length==1) {fAddRow(0,0);}
if(tbl.rows[0].cells.length==1) {fAddCol(0,0);}

frm_edit_table.save.disabled=false;
frm_edit_table.save_templ.disabled=false;

var wTools;

function fShowTools() {
	wTools=window.showModelessDialog('table_toolbox/toolbox.htm',self,'status:true;dialogWidth:325px;dialogHeight:425px');
}
function del_table(table_id) {
if (confirm('Действительно хотите УДАЛИТЬ ТАБЛИЦУ ?')) ifr_edit_table.location='save_table.php?del_table='+table_id;
}
function del_template(template_id) {
if (confirm('Действительно хотите УДАЛИТЬ ШАБЛОН ?')) ifr_edit_table.location='save_table.php?del_template='+template_id;
}
</script>

