<?php 
require_once "auth.php";
if ($_SESSION['project']['view_rep']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
extract($_POST);
?>
<?php

include("lk/lk_ora_conn_string.php");
include("phones_conv.php");
	
include("report_build_query.php");

	//echo "<div class=rep_head>";	
	echo "<font size=4>\"".$_SESSION['project']['name']."\"";
	if ($form_id<>'all') echo " - \"".$form_name."\"";
	if ($cdn<>'all' and $cdn<>'null') echo " - ".$cdn;
	else if ($cdn=='null') echo " - без номера доступа"; 
	echo " - количество</font><br>";
	echo "За период: с <b>".$_SESSION['start_rep_date']."</b> по <b>".$_SESSION['end_rep_date']."</b><hr>";
	echo "<font color=black><b>Сгрупировать отчет по выбранным полям:</b></font><br>";
	
	if(isset($access_fix['date_call']) and $access_fix['date_call']=='y') 	  {echo "<nobr><input type=checkbox name=chk_data"; if(isset($chk_data) or isset($count_go)) echo " checked"; echo">Дата звонка</input></nobr>";}
	
	echo "<nobr><input type=checkbox name=chk_direction"; if(isset($chk_direction)) echo " checked"; echo">Направление звонка</input></nobr>";
	
	if($cdn=='all' and isset($access_fix['cdn']) and $access_fix['cdn']=='y') {echo "<nobr><input type=checkbox name=chk_cgpn"; if(isset($chk_cgpn)) echo " checked"; echo">Номер доступа</input></nobr>";}
	if(isset($access_fix['agid']) and $access_fix['agid']=='y') {echo "<nobr><input type=checkbox name=chk_agid"; if(isset($chk_agid)) echo " checked"; echo">ID оператора</input></nobr>";}
	if ($form_id=='all') {echo "<nobr><input type=checkbox name=chk_form"; if(isset($chk_form)) echo " checked"; echo">Тип отчета</input></nobr>";}
	if ($_SESSION['project']['id']=='0') {echo "<nobr><input type=checkbox name=chk_project"; if(isset($chk_project)) echo " checked"; echo">Проект</input></nobr>";}
	echo "<br>";
	
	//получаем список выборочных полей
	if ($form_id<>'all') {
		if(isset($object_id)) {
			foreach ($object_id as $key=>$id) {
				if($object_selectable[$key]=='y') echo "<nobr><input type=checkbox name=selected_columns[".$id."] value='".$object_name[$key]."'".(isset($selected_columns[$id])?" checked":"").">".$object_name[$key]."</input></nobr>";
			}
		}	
	}
	echo "<hr><input type=checkbox".(isset($order_by_count)?" checked":"")." name=order_by_count>Сортировать по количеству</input>";
	echo "<input type=button class=menubtn name=count_go_go value=\"Показать отчет\" onclick=form2div('frm','count_rep_div',this,'report_count_go.php')><hr>";
	//echo "</div>";	
	//echo "<div id=count_filter_div class=rep_head></div>";	
?>