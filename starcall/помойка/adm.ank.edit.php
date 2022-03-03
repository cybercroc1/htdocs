<?php include("../../starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">
<script src="adm.ank.edit.js"></script>
<?php 

extract($_REQUEST);
if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();

echo " | ";
echo "<font size=4>Редактирование анкеты</font> | ";
echo "<font align=right><a href='help.adm.ank.edit.html' target='_blank'>Справка</a></font>";
echo "<hr>";

include("../../starcall_conf/conn_string.cfg.php");
include("../../starcall_conf/path.cfg.php");

echo "<font size=4>Проект: ".$_SESSION['project']['name']." (id:".$_SESSION['project']['id'].")</font><hr>"; 

echo "<form name=frm method=post action=adm.ank.edit.save.php target='logFrame'>";	
echo "<input type=hidden name=project_id value='".$_SESSION['project']['id']."'>";

echo "<table id=tbl name=tbl style='table-layout:fixed'>";

echo "<tr>";
echo "<th width=20></th>";
echo "<th width=20 style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><font color=blue>+</font></th>";
echo "<th width=20></th>";
echo "<th width=65>С.Г.О(В)<br>(id)</th>";
echo "<th width=175>Тип</th>";
echo "<th width=250>Сообщение</th>";
echo "<th width=250>Имя</th>";
echo "<th width=80>Кодовое имя</th>";
echo "<th width=35>Обяз.</th>";
echo "<th width=35>Квота</th>";
echo "<th width=97>Ротация</th>";
echo "<th width=95>Условие</th>";
echo "</tr>";

$q_grp=OCIParse($c,"select g.page_num,g.id grp_id,g.num_on_page grp_num,g.name grp_name, g.quest_ord_type from STC_OBJECT_GROUP g
where project_id='".$_SESSION['project']['id']."'
order by g.page_num,g.num");

$q_obj=OCIParse($c,"select o.id obj_id, o.obj_type_id, o.num_on_group obj_num, o.quest_num, o.message, nvl(o.answ_ord_type,'По порядку') answ_ord_type, o.must, o.quote_num,
f.id field_id, f.text_name, f.code_name,
ot.id obj_type_id, ot.name obj_type_name
from STC_FIELDS f, STC_OBJECTS o, STC_LI_OBJECT_TYPE ot
where o.group_id=:grp_id and o.project_id='".$_SESSION['project']['id']."'
and o.deleted is null
and f.id(+)=o.field_id
and ot.id=o.obj_type_id
order by o.num");

$page_num=0;
$grp_num=0;	
$last_obj_type='';	
OCIExecute($q_grp);

$i=0; while(OCIFetch($q_grp)) { //группы
	//новая страница и группа
	if(OCIResult($q_grp,"PAGE_NUM")<>$page_num) {$i++;
		$page_num=OCIResult($q_grp,"PAGE_NUM");
		$grp_num=OCIResult($q_grp,"GRP_NUM");
		$grp_id=OCIResult($q_grp,"GRP_ID");	
		$grp_name=OCIResult($q_grp,"GRP_NAME");
		$grp_order_type=OCIResult($q_grp,"QUEST_ORD_TYPE");
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//echo "<th><input type=checkbox name=mark[$i]></th>";
		if($i==1) echo "<th></th>"; //запрещаем удалять первую группу
		else echo "<th style='cursor:pointer' title='Удалить' onClick='del_old_grp(\"".$grp_id."\");del_obj(this)'><font color=red>-</font></th>";
		echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><font color=blue>+</font></th>";
		echo "<th></th>";
		if($i==1) echo "<td>"; //запрещаем перемещать первую группу
		else echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
		echo "<input type=hidden name='obj_id[$i]' value='".$grp_id."'><input type=hidden name='type[$i]' value='page'>";
		echo $page_num.".".$grp_num."<br>(".$grp_id.")";
		echo "</td>";
		echo "<td>";
		echo "<b>Страница $page_num. Группа $grp_num.</b>";
		echo "</td>";
		echo "<td colspan=5>"; 
		echo "<textarea name=grp_name[$i] onchange='notsaved()' style='width:100%'>".$grp_name."</textarea>";
		//echo "<input type=text style='width:100%' name=grp_name[$i] value='".OCIResult($q_grp,"GRP_NAME")."' onchange='notsaved()'>";
		echo "</td>";
		echo "<td><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$grp_order_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
		echo "</tr>";
	}
	//только новая группа
	else {$i++;
		$page_num=OCIResult($q_grp,"PAGE_NUM");
		$grp_num=OCIResult($q_grp,"GRP_NUM");
		$grp_id=OCIResult($q_grp,"GRP_ID");
		$grp_name=OCIResult($q_grp,"GRP_NAME");
		$grp_order_type=OCIResult($q_grp,"QUEST_ORD_TYPE");		
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//echo "<th><input type=checkbox name=mark[$i]></th>";
		echo "<th style='cursor:pointer' title='Удалить' onClick='del_old_grp(\"".$grp_id."\");del_obj(this)'><font color=red>-</font></th>";		
		echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><font color=blue>+</font></th>";
		echo "<th></th>";
		echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
		echo "<input type=hidden name='obj_id[$i]' value='".$grp_id."'><input type=hidden name='type[$i]' value='group'>";
		echo $page_num.".".$grp_num."<br>(".$grp_id.")";
		echo "</td>";
		echo "<td>";
		echo "<b>Группа $grp_num.</b>";
		echo "</td>";
		echo "<td colspan=5>";
		echo "<textarea name=grp_name[$i] onchange='notsaved()' style='width:100%'>".$grp_name."</textarea>";  
		//echo "<input type=text style='width:100%' name=grp_name[$i] value='".OCIResult($q_grp,"GRP_NAME")."' onchange='notsaved()'>";
		echo "</td>";
		echo "<td><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$grp_order_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
		echo "</tr>";
	}
	
	//объекты
	OCIBindByName($q_obj,":grp_id",$grp_id);
	OCIExecute($q_obj);
	while(OCIFetch($q_obj)) {$i++;
	$last_obj_type=OCIResult($q_obj,"OBJ_TYPE_ID");
	
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//echo "<th><input type=checkbox name=mark[$i]></th>";
		echo "<th style='cursor:pointer' title='Удалить' onClick='del_old_obj(\"".OCIResult($q_obj,"OBJ_ID")."\");del_obj(this)'><font color=red>-</font></th>";
		echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><font color=blue>+</font></th>";
		//вопросы одиночные
		if(substr(OCIResult($q_obj,"OBJ_TYPE_ID"),0,5)=='q_sn_') {
			echo "<th></th>";
			echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
			echo "<input type=hidden name='obj_id[$i]' value='".OCIResult($q_obj,"OBJ_ID")."'><input type=hidden name='type[$i]' value='obj'>";
			echo $page_num.".".$grp_num.".".OCIResult($q_obj,"OBJ_NUM")."<b>(".OCIResult($q_obj,"QUEST_NUM").")</b><br>(".OCIResult($q_obj,"OBJ_ID").")"; 
			echo "</td>";
			echo "<td>";
			echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
				<option value='".OCIResult($q_obj,"OBJ_TYPE_ID")."'>".OCIResult($q_obj,"OBJ_TYPE_NAME")."</option>
				<option value='q_sn_text'>Вопрос - Текст</option>
				<option value='q_sn_bigtext'>Вопрос - Большой текст</option>
				<option value='q_sn_integer'>Вопрос - Число</option>
				<option value='q_sn_date'>Вопрос - Дата</option>
				<option value='q_sn_time'>Вопрос - Время</option>
				</select>";
			echo "</td>";
			echo "<td><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".OCIResult($q_obj,"MESSAGE")."</textarea></td>";
			//echo "<td><input type=text name=message[$i] value='".OCIResult($q_obj,"MESSAGE")."' onchange='notsaved()'></td>";
			echo "<td><input style='width:100%' type=text name=text_name[$i] value='".OCIResult($q_obj,"TEXT_NAME")."' onchange='notsaved()'></td>";
			echo "<td><input style='width:100%' type=text name=code_name[$i] value='".OCIResult($q_obj,"CODE_NAME")."' onchange='notsaved()'></td>";
			echo "</td>";
			echo "<td>об<input type=checkbox name=must[$i] ".(OCIResult($q_obj,"MUST")<>''?' checked':NULL)." onchange='notsaved()'></td>";
			echo "<td></td>";
			echo "<td></td>";
			echo "</tr>";
		}
		//вопросы с выбором
		else if(substr(OCIResult($q_obj,"OBJ_TYPE_ID"),0,5)=='q_ls_') {
			//echo "<th><img src='gif/edit.gif' style='cursor:pointer' onclick=edit_obj('".OCIResult($q_obj,"OBJ_ID")."','q_ls_') title='Редактировать'></img></th>";
			echo "<th><a href='adm.ank.list.edit.php?obj_id=".OCIResult($q_obj,"OBJ_ID")."' target='admAnkEditSecondFrame'><img src='gif/edit.gif'></img></a></th>";
			echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
			echo "<input type=hidden name='obj_id[$i]' value='".OCIResult($q_obj,"OBJ_ID")."'><input type=hidden name='type[$i]' value='obj'>";
			echo $page_num.".".$grp_num.".".OCIResult($q_obj,"OBJ_NUM")."<b>(".OCIResult($q_obj,"QUEST_NUM").")</b><br>(".OCIResult($q_obj,"OBJ_ID").")"; 
			echo "</td>";
			echo "<td>";
			echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
				<option value='".OCIResult($q_obj,"OBJ_TYPE_ID")."'>".OCIResult($q_obj,"OBJ_TYPE_NAME")."</option>
				<option value='q_ls_select'>Вопрос - Выбор</option>
				<option value='q_ls_radio'>Вопрос - Радио</option>
				<option value='q_ls_multi'>Вопрос - Множ. выбор</option>
				<option value='q_ls_checkbox'>Вопрос - Галочки</option>
				</select>";
			echo "</td>";
			echo "<td><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".OCIResult($q_obj,"MESSAGE")."</textarea></td>";
			//echo "<td><input type=text name=message[$i] value='".OCIResult($q_obj,"MESSAGE")."' onchange='notsaved()'></td>";
			echo "<td><input style='width:100%' type=text name=text_name[$i] value='".OCIResult($q_obj,"TEXT_NAME")."' onchange='notsaved()'></td>";
			echo "<td><input style='width:100%' type=text name=code_name[$i] value='".OCIResult($q_obj,"CODE_NAME")."' onchange='notsaved()'></td>";
			echo "</td>";
			echo "<td>об<input type=checkbox name=must[$i] ".(OCIResult($q_obj,"MUST")<>''?' checked':NULL)." onchange='notsaved()'></td>";
			echo "<td>кв<input type=checkbox name=quoted[$i] ".(OCIResult($q_obj,"QUOTE_NUM")<>''?' checked':NULL)." onchange='notsaved()'></td>";
			echo "<td><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".OCIResult($q_obj,"ANSW_ORD_TYPE")."</option><option>По порядку</option><option>Случайно</option></select></td>";
			echo "</tr>";
		}	
		//концы
		else if (substr(OCIResult($q_obj,"OBJ_TYPE_ID"),0,4)=='end_') {
			echo "<th></th>";
			echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
			echo "<input type=hidden name='obj_id[$i]' value='".OCIResult($q_obj,"OBJ_ID")."'><input type=hidden name='type[$i]' value='obj'>";
			echo $page_num.".".$grp_num.".".OCIResult($q_obj,"OBJ_NUM")." (".OCIResult($q_obj,"OBJ_ID").")"; 
			echo "</td>";
			echo "<td>";
			echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
				<option value='".OCIResult($q_obj,"OBJ_TYPE_ID")."'>".OCIResult($q_obj,"OBJ_TYPE_NAME")."</option>
				<option value='end_norm'>Конец УСПЕШНЫЙ</option>
				<option value='end_false'>Конец НЕЦЕЛЕВОЙ</option>
				</select>";
			echo "</td>";
			echo "<td colspan=6><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".OCIResult($q_obj,"MESSAGE")."</textarea></td>";
			//echo "<td colspan=5><input type=text style='width:100%' name=message[$i] value='".OCIResult($q_obj,"MESSAGE")."' onchange='notsaved()'></td>";
			echo "</tr>";	
		}
		//все остальное
		else {
			echo "<th></th>";
			echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)'>";
			echo "<input type=hidden name='obj_id[$i]' value='".OCIResult($q_obj,"OBJ_ID")."'><input type=hidden name='type[$i]' value='obj'>";
			echo $page_num.".".$grp_num.".".OCIResult($q_obj,"OBJ_NUM")." (".OCIResult($q_obj,"OBJ_ID").")"; 
			echo "</td>";
			echo "<td><input type=hidden name=obj_type_id[$i] value='".OCIResult($q_obj,"OBJ_TYPE_ID")."'>".OCIResult($q_obj,"OBJ_TYPE_NAME")."</td>";
			echo "<td colspan=6><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".OCIResult($q_obj,"MESSAGE")."</textarea></td>";
			//echo "<td colspan=5><input type=text style='width:100%' name=message[$i] value='".OCIResult($q_obj,"MESSAGE")."' onchange='notsaved()'></td>";
			echo "</tr>";		
		}
	}
}
if($i==0) {$i++; //первая группа и страница для нового проекта
		$page_num=1;
		$grp_num=1;
		$grp_name='';
		$grp_order_type='По порядку';
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		//echo "<th><input type=checkbox name=mark[$i]></th>";
		echo "<th></th>"; //запрещаем удалять первую группу
		echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><font color=blue>+</font></th>";
		echo "<th></th>";
		echo "<td>"; //запрещаем перемещать первую группу
		echo "<input type=hidden name='obj_idx[$i]' value='page'>";
		echo $page_num.".".$grp_num;
		echo "</td>";
		echo "<td>";
		echo "<b>Страница $page_num. Группа $grp_num.</b>";
		echo "</td>";
		echo "<td colspan=5>"; 
		echo "<textarea name=grp_name[$i] onchange='notsaved()' style='width:100%'>".$grp_name."</textarea>";
		//echo "<input type=text style='width:100%' name=grp_name[$i] value='".OCIResult($q_grp,"GRP_NAME")."' onchange='notsaved()'>";
		echo "</td>";
		echo "<td><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$grp_order_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
		echo "</tr>";
}
echo "</table><hr>";

echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=Сохранить onclick=this.disabled=true;frm.cancel.disabled=true;frm.submit();> ";
echo "<input type=button name=cancel value=Отмена onclick={this.style.display='none';frm.frm_submit.value='save';frm.save.value='Сохранить';document.getElementById('save_status').innerHTML='';} style='display:none' >";
//echo "<input type=button name=cancel value=Отмена onclick=document.location.reload(); style='display:none' >";

echo "</form>";
?>
<script>var new_idx=tbl.rows.length;</script>
</body>
</html>
