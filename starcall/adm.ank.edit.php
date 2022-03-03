<?php include("starcall/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">
<script src="func.row_select.js"></script>
<script src="adm.ank.edit.js"></script>
<?php 

extract($_REQUEST);
if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_ank']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form name=frm method=post action=adm.ank.edit.save.php target='logFrame'>";	

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr class=header_tr><td>";

echo " | ";
echo "<font size=4>Редактирование анкеты</font> | ";
echo "<font align=right><a href='help.adm.ank.edit.html' target='_blank'>Справка</a></font>";
echo "<hr>";

include("starcall/conn_string.cfg.php");
include("starcall/path.cfg.php");


echo "<input type=hidden name=project_id value='".$_SESSION['adm']['project']['id']."'>";

echo "<font size=4>Проект: ".$_SESSION['adm']['project']['name']." (id:".$_SESSION['adm']['project']['id'].")</font>
 <input type=checkbox name=hide_pages".(isset($_SESSION['adm']['project']['hide_pages'])?' checked':NULL)." onclick='if(this.checked==true){this.checked=true;fHidePages()}else{this.checked=false;fShowPages()}'>
 Скрыть страницы и группы</input> ";
 echo " <input type=button name=logic_test value='Проверка логики' onclick=window.open('adm.ank.logic_test.php','logic_test','width=420,height=230,resizable=yes,scrollbars=yes')>";
echo "<hr>"; 

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";

echo "<table id=tbl name=tbl class=white_table>";

echo "<tr data-type=head>";
echo "<th width=20></th>";
//echo "<th width=20></th>";
echo "<th width=20 style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><img src='png/plus.png'></img></th>";
//echo "<th width=20></th>";
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

$q_page=OCIParse($c,"select p.id page_id, p.num page_num, p.message page_mess
from STC_OBJECT_PAGE p
where p.project_id=".$_SESSION['adm']['project']['id']."
order by p.num");

$q_grp=OCIParse($c,"select g.id grp_id,g.num_on_page grp_num,g.message grp_mess, g.quest_ord_type
from STC_OBJECT_GROUP g
where g.project_id=".$_SESSION['adm']['project']['id']." and g.page_id=:page_id
order by g.num");

$q_obj=OCIParse($c,"select o.id obj_id, o.obj_type_id, o.num_on_group obj_num, o.quest_num, o.message, nvl(o.answ_ord_type,'По порядку') answ_ord_type, o.must, o.quote_num, 
f.id field_id, f.text_name, f.code_name,
ot.id obj_type_id, ot.name obj_type_name
from STC_OBJECTS o, STC_FIELDS f, STC_LI_OBJECT_TYPE ot
where o.project_id=".$_SESSION['adm']['project']['id']." and o.group_id=:group_id and o.deleted(+) is null
and f.project_id(+)=".$_SESSION['adm']['project']['id']." and f.id(+)=o.field_id
and ot.id(+)=o.obj_type_id
order by o.num");

$page_num=0;
$grp_num=0;	
$g_on_p=0;
$i=0; 
//$last_obj_type='';	
OCIExecute($q_page);
while(OCIFetch($q_page)) {
	//СТРАНИЦЫ
	$i++;
	$g_on_p=0;
	$page_id=OCIResult($q_page,"PAGE_ID");
	$page_num=OCIResult($q_page,"PAGE_NUM");
	$page_mess=OCIResult($q_page,"PAGE_MESS");
	OCIBindByName($q_grp,":page_id",$page_id);
	OCIExecute($q_grp);
	while(OCIFetch($q_grp)) {
		//ГРУППЫ
		$g_on_p++;
		$grp_id=OCIResult($q_grp,"GRP_ID");	
		$grp_num=OCIResult($q_grp,"GRP_NUM");
		$grp_mess=OCIResult($q_grp,"GRP_MESS");
		$grp_order_type=OCIResult($q_grp,"QUEST_ORD_TYPE");	
		if($g_on_p==1) { //если первая группа на странице, то совмещаем в одной строке
			if($i==1) echo "<tr data-type=page>"; //запрещаем выделять первую страницу
			else 
			echo "<tr data-type=page class=selectable_row>";
			if($i==1) echo "<th onclick=click_row(this.parentNode,'sel')>$i</th>"; //запрещаем удалять первую страницу
			else 
			echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick='del_old_page(\"".$page_id."\",\"".$grp_id."\");del_obj(this)' onclick=click_row(this.parentNode,'sel')><img src='png/del.png'></img></th>";
			echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><img src='png/plus.png'></img></th>";
			//echo "<th></th>";
			if($i==1) echo "<td>"; //запрещаем перемещать первую страницу
			else 
			echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
			echo "<input type=hidden name='obj_id[$i]' value='".$page_id."'><input type=hidden name='type[$i]' value='page'><input type=hidden name='page_group[$i]' value='".$grp_id."'>";
			echo $page_num.".".$grp_num."<br>(".$page_id.".".$grp_id.")";
			echo "</td>";
			echo "<td onclick=click_row(this.parentNode,'sel')>";
			echo "<b>Страница $page_num. Группа $grp_num.</b>";
			echo "</td>";
			echo "<td colspan=5 onclick=click_row(this.parentNode,'sel')>"; 
			echo "<textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$page_mess."</textarea>";
			echo "</td>";
			echo "<td onclick=click_row(this.parentNode,'sel')><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$grp_order_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
			echo "</tr>";
			}
			//ГРУППЫ
			if($g_on_p>1) {$i++;
				//if($i==2) echo "<tr data-type=group>";
				echo "<tr data-type=group class=selectable_row>";
				//if($i==2) echo "<th onclick=click_row(this.parentNode,'sel')></th>";
				echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick='del_old_grp(\"".$grp_id."\");del_obj(this)' onclick=click_row(this.parentNode,'sel')><img src='png/del.png'></img></th>";
				echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><img src='png/plus.png'></img></th>";
				//echo "<th onclick=click_row(this.parentNode,'sel')></th>";
				//if($i==2) echo "<td onclick=click_row(this.parentNode,'sel')>";
				echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
				echo "<input type=hidden name='obj_id[$i]' value='".$grp_id."'><input type=hidden name='type[$i]' value='group'>";
				echo $page_num.".".$grp_num."<br>(".$grp_id.")";
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')>";
				echo "<b>Группа $grp_num.</b>";
				echo "</td>";
				echo "<td colspan=5 onclick=click_row(this.parentNode,'sel')>"; 
				echo "<textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$grp_mess."</textarea>";
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$grp_order_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
				echo "</tr>";
			}
		OCIBindByName($q_obj,":group_id",$grp_id);
		OCIExecute($q_obj);
		while(OCIFetch($q_obj)) {
			$i++;			
			//ОБЪЕКТЫ
			$obj_id=OCIResult($q_obj,"OBJ_ID");
			$obj_num=OCIResult($q_obj,"OBJ_NUM");
			$quest_num=OCIResult($q_obj,"QUEST_NUM");
			$obj_type_id=OCIResult($q_obj,"OBJ_TYPE_ID");
			$obj_type_name=OCIResult($q_obj,"OBJ_TYPE_NAME");
			$message=OCIResult($q_obj,"MESSAGE");
			$text_name=OCIResult($q_obj,"TEXT_NAME");
			$code_name=OCIResult($q_obj,"CODE_NAME");
			$must=OCIResult($q_obj,"MUST");
			$quote_num=OCIResult($q_obj,"QUOTE_NUM");
			$answ_ord_type=OCIResult($q_obj,"ANSW_ORD_TYPE");
			echo "<tr data-type=object data-object_id='".$obj_id."' class=selectable_row>";
			echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick='del_old_obj(\"".$obj_id."\");del_obj(this)' onclick=click_row(this.parentNode,'sel')><img src='png/del.png'></img></th>";
			echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><img src='png/plus.png'></img></th>";
			//вопросы одиночные
			if(substr($obj_type_id,0,5)=='q_sn_') {
				///echo "<th onclick=click_row(this.parentNode,'sel')></th>";
				echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
				echo "<input type=hidden name='obj_id[$i]' value='".$obj_id."'><input type=hidden name='type[$i]' value='obj'>";
				echo $page_num.".".$grp_num.".".$obj_num."<b>(".$quest_num.")</b><br>(".$obj_id.")"; 
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')>";
				echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
					<option value='".$obj_type_id."'>".$obj_type_name."</option>
					<option value='q_sn_text'>Вопрос - Текст</option>
					<option value='q_sn_bigtext'>Вопрос - Большой текст</option>
					<option value='q_sn_integer'>Вопрос - Число</option>
					<option value='q_sn_date'>Вопрос - Дата</option>
					<option value='q_sn_time'>Вопрос - Время</option>
					<option value='q_sn_datetime'>Вопрос - Дата и время</option>
					</select>";
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$message."</textarea></td>";
				echo "<td onclick=click_row(this.parentNode,'sel')><input style='width:100%' type=text name=text_name[$i] value='".$text_name."' onchange='notsaved()'></td>";
				echo "<td onclick=click_row(this.parentNode,'sel')><input style='width:100%' type=text name=code_name[$i] value='".$code_name."' onchange='notsaved()'></td>";
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')>об<input type=checkbox name=must[$i] ".($must<>''?' checked':NULL)." onchange='notsaved()'></td>";
				echo "<td onclick=click_row(this.parentNode,'sel')></td>";
				echo "<td onclick=click_row(this.parentNode,'sel')></td>";
				echo "</tr>";
			}
			//вопросы с выбором
			else if(substr($obj_type_id,0,5)=='q_ls_') {
				//echo "<th onclick=click_row(this.parentNode,'sel')><a href='adm.ank.list.edit.php?obj_id=".$obj_id."' target='admAnkEditSecondFrame'><img src='gif/edit.gif'></img></a></th>";
				echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick='click_edit(this)'>";
				echo "<input type=hidden name='obj_id[$i]' value='".$obj_id."'><input type=hidden name='type[$i]' value='obj'>";
				echo $page_num.".".$grp_num.".".$obj_num."<b>(".$quest_num.")</b><br>(".$obj_id.")"; 
				echo "</td>";
				echo "<td onclick='click_edit(this)'>";
				echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
					<option value='".$obj_type_id."'>".$obj_type_name."</option>
					<option value='q_ls_select'>Вопрос - Выбор</option>
					<option value='q_ls_radio'>Вопрос - Радио</option>
					<option value='q_ls_checkbox'>Вопрос - Галочки</option>
					</select>";
				echo "</td>";
				echo "<td onclick='click_edit(this)'><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$message."</textarea></td>";
				echo "<td onclick='click_edit(this)'><input style='width:100%' type=text name=text_name[$i] value='".$text_name."' onchange='notsaved()'></td>";
				echo "<td onclick='click_edit(this)'><input style='width:100%' type=text name=code_name[$i] value='".$code_name."' onchange='notsaved()'></td>";
				echo "</td>";
				echo "<td onclick='click_edit(this)'>об<input type=checkbox name=must[$i] ".($must<>''?' checked':NULL)." onchange='notsaved()'></td>";
				echo "<td onclick='click_edit(this)'>кв<input type=checkbox name=quoted[$i] ".($quote_num<>''?' checked':NULL)." onchange='notsaved()'></td>";
				echo "<td onclick='click_edit(this)'><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>".$answ_ord_type."</option><option>По порядку</option><option>Случайно</option></select></td>";
				echo "</tr>";
			}	
			//концы
			else if (substr($obj_type_id,0,4)=='end_') {
				//echo "<th onclick=click_row(this.parentNode,'sel')></th>";
				echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
				echo "<input type=hidden name='obj_id[$i]' value='".$obj_id."'><input type=hidden name='type[$i]' value='obj'>";
				echo $page_num.".".$grp_num.".".$obj_num." (".$obj_id.")"; 
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')>";
				echo "<select name=obj_type_id[$i] style='width:100%' onchange='notsaved()'>
					<option value='".$obj_type_id."'>".$obj_type_name."</option>
					<option value='end_norm'>Конец УСПЕШНЫЙ</option>
					<option value='end_false'>Конец НЕЦЕЛЕВОЙ</option>
					</select>";
				echo "</td>";
				echo "<td colspan=6 onclick=click_row(this.parentNode,'sel')><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$message."</textarea></td>";
				echo "</tr>";	
			}
			//все остальное
			else {
				//echo "<th onclick=click_row(this.parentNode,'sel')></th>";
				echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
				echo "<input type=hidden name='obj_id[$i]' value='".$obj_id."'><input type=hidden name='type[$i]' value='obj'>";
				echo $page_num.".".$grp_num.".".$obj_num." (".$obj_id.")"; 
				echo "</td>";
				echo "<td onclick=click_row(this.parentNode,'sel')><input type=hidden name=obj_type_id[$i] value='".$obj_type_id."'>".$obj_type_name."</td>";
				echo "<td colspan=6 onclick=click_row(this.parentNode,'sel')><textarea name=message[$i] onchange='notsaved()' style='width:100%'>".$message."</textarea></td>";
				echo "</tr>";		
			}			
		}
	}	
}

if($i==0) { 
	//первая страница для нового проекта
	$i++;
	$page_num=1;
	echo "<tr data-type=page class=selectable_row>";
	echo "<th onclick=click_row(this.parentNode,'sel')></th>";
	echo "<th style='cursor:pointer' title='Добавить ниже' onClick='add_obj(this)'><img src='png/plus.png'></img></th>";
	//echo "<td style='cursor:s-resize' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick=click_row(this.parentNode,'sel')>";
	echo "<td onclick=click_row(this.parentNode,'sel')>";
	echo "<input type=hidden name='obj_id[$i]' value='new'><input type=hidden name='type[$i]' value='page'>";
	echo $page_num;
		echo "</td>";
		echo "<td onclick=click_row(this.parentNode,'sel')>";
		echo "<b>Страница $page_num. Группа 1.</b>";
	echo "</td>";
	echo "<td colspan=5 onclick=click_row(this.parentNode,'sel')>"; 
	echo "<textarea name=message[$i] onchange='notsaved()' style='width:100%'></textarea>";
	echo "</td>";
	echo "<td onclick=click_row(this.parentNode,'sel')><select name='order_type[$i]' style='width:100%' onchange='notsaved()'><option>По порядку</option><option>Случайно</option></select></td>";
	echo "</tr>";
}

echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";

echo "<hr>";

if(isset($_SESSION['adm']['project']['hide_pages'])) echo "<script>fHidePages();</script>";

if($_SESSION['user']['rw_ank']<>'w')  echo "<font color=red>Редактирование запрещено!</font>";
else {
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=Сохранить onclick=this.disabled=true;frm.cancel.disabled=true;frm.submit();> ";
echo " <input type=button name=cancel value=Отмена onclick={this.style.display='none';frm.frm_submit.value='save';frm.save.value='Сохранить';document.getElementById('save_status').innerHTML='';} style='display:none' >";
}
//echo " <input type=button name=logic_test value='Проверка логики' onclick=parent.admAnkEditSecondFrame.location='adm.ank.logic_test.php'>";
//echo "<input type=button name=cancel value=Отмена onclick=document.location.reload(); style='display:none' >";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

echo "</form>";
?>
<script>var new_idx=tbl.rows.length-1;</script>
</body></html>
