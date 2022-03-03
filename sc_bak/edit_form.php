<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
header('X-UA-Compatible: IE=EmulateIE7');
$_SESSION['last_url']='edit_form.php';
?>
<HTML>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php if ($_SESSION['project']['id']==0 and $_SESSION['admin']<>1) exit(); 
if ($_SESSION['project']['ch_form']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if (!isset($new_br)) $new_br='';
if (!isset($new_requre)) $new_requre='';
if (!isset($sav_obj_br)) $sav_obj_br='';
if (!isset($sav_requre)) $sav_requre='';
if (!isset($send_cdpn)) $send_cdpn='';
if (!isset($send_cgpn)) $send_cgpn='';
if (!isset($send_agid)) $send_agid='';

if (!isset($show_ivr_sec)) $show_ivr_sec='';
if (!isset($show_queue_sec)) $show_queue_sec='';
if (!isset($show_alerting_sec)) $show_alerting_sec='';
if (!isset($show_connected_sec)) $show_connected_sec='';
if (!isset($show_connected_min)) $show_connected_min='';

if (!isset($show_call_sec)) $show_call_sec='';
if (!isset($show_call_min)) $show_call_min='';

if(!isset($CODED_AON)) $CODED_AON='';

if (isset($up)) up($form_id,$obj_id,$ordering,$c);
if (isset($down)) down($form_id,$obj_id,$ordering,$c);
if (isset($new_obj)) new_obj($form_id,$new_obj_name,$new_obj_type,$new_width,$new_height,$new_br,$new_tag_before,$new_tag_after,$new_requre,$c);
if (isset($del_obj)) del_obj($form_id,$obj_id,$c);
if (isset($save_form)) $form_id=save_form($form_id,$form_name,$c,$send_cdpn,$send_cgpn,$send_agid,$show_ivr_sec,$show_queue_sec,$show_alerting_sec,$show_connected_sec,$show_connected_min,$show_call_sec,$show_call_min,$CODED_AON);
//if (isset($new_form)) $form_id=new_form($new_form_name,$c);
if (isset($del_form)) {del_form($form_id,$c); $form_id='';}
//if (isset($ren_form)) ren_form($form_id,$ren_form_name,$c);
if (isset($sav_obj)) sav_obj($obj_id,$sav_obj_name,$sav_obj_type,$sav_obj_width,$sav_obj_height,$sav_obj_br,$sav_tag_before,$sav_tag_after,$sav_ordering,$sav_requre,$c);
if (isset($copy_form)) {
	if (isset($copy_to_project) and isset($_SESSION['admin']) and $_SESSION['admin']==1) {
		echo "<form method=post action=edit_form.php>
		<input type=hidden name=form_id value=".$form_id.">
		Скопировать форму в проект: 
		<select name=project_id>";
		$q=OCIParse($c,"select id,name from sc_projects where (type='irs' or type is null) and hidden is null order by type nulls first, name");
		OCIExecute($q,OCI_DEFAULT);
			while (OCIFetch($q)) {
			echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$_SESSION['project']['id']?' selected':'').">".OCIResult($q,"NAME")."</option>";
			}
		echo "</select>";
		echo "<input type=submit name=copy_form value=\"Копировать\">";	
		exit();
	}
	
	if (!isset($project_id)) $project_id='';
	$form_id=copy_form($form_id,$project_id,$c);
}
if (!isset($form_id) or $form_id=='send_aband' or $form_id=='send_not_rep') {$form_id=''; $form_name='';}
if (!isset($form_name)) $form_name='';
echo "<form method=post action=edit_form.php>"; //POST работает некорректно
echo "<font size=4>Редактирование Формы</font> | ";
if ($_SESSION['project']['ch_email']==1) echo "<a href=edit_email.php?form_id=".$form_id.">Редактирование е-мейлов</a>";
if ($_SESSION['admin']==1) echo " | <a href=edit_inject.php?form_id=".$form_id.">Внешние формы (PHP-injects) </a>";
echo "<hr>";

//Выбор формы
$form_name='';
$chk_send_cdpn='checked';
$chk_send_cgpn='checked';
$chk_send_agid='checked';

$chk_show_ivr_sec='';
$chk_show_queue_sec='';
$chk_show_alerting_sec='';
$chk_show_connected_sec='';
$chk_show_connected_min='';

$chk_show_call_sec='';
$chk_show_call_min='';
	
$chk_CODED_AON='';
echo "<select name=form_id onchange=document.all.ch_form.click()>";
echo "<option value=''>СОЗДАТЬ ФОРМУ</option>";
$q=OCIParse($c,"select id,replace(name,'\"','&quot;') name, send_cdpn, send_cgpn, send_agid, show_call_sec, show_call_min,
show_ivr_sec, show_connected_sec, show_connected_min, show_alerting_sec, show_queue_sec, coded_aon
from sc_forms
where project_id='".$_SESSION['project']['id']."' and deleted is null and id>0");
OCIExecute($q,OCI_DEFAULT);
while (OCIFetch($q)) {
	$selected='';
	if(OCIResult($q,"ID")==$form_id) {
		$selected=' selected';
		if (OCIResult($q,"SEND_CDPN")=='y') $chk_send_cdpn='checked'; else $chk_send_cdpn=''; 
		if (OCIResult($q,"SEND_CGPN")=='y') $chk_send_cgpn='checked'; else $chk_send_cgpn=''; 
		if (OCIResult($q,"SEND_AGID")=='y') $chk_send_agid='checked'; else $chk_send_agid='';

		if (OCIResult($q,"SHOW_IVR_SEC")=='y') $chk_show_ivr_sec='checked'; else $chk_show_ivr_sec=''; 
		if (OCIResult($q,"SHOW_QUEUE_SEC")=='y') $chk_show_queue_sec='checked'; else $chk_show_queue_sec=''; 
		if (OCIResult($q,"SHOW_ALERTING_SEC")=='y') $chk_show_alerting_sec='checked'; else $chk_show_alerting_sec=''; 
		if (OCIResult($q,"SHOW_CONNECTED_SEC")=='y') $chk_show_connected_sec='checked'; else $chk_show_connected_sec=''; 
		if (OCIResult($q,"SHOW_CONNECTED_MIN")=='y') $chk_show_connected_min='checked'; else $chk_show_connected_min=''; 

		if (OCIResult($q,"SHOW_CALL_SEC")=='y') $chk_show_call_sec='checked'; else $chk_show_call_sec=''; 
		if (OCIResult($q,"SHOW_CALL_MIN")=='y') $chk_show_call_min='checked'; else $chk_show_call_min='';  
		if (OCIResult($q,"CODED_AON")=='y') $chk_CODED_AON='checked'; else $chk_CODED_AON='';  
		$form_name=OCIResult($q,"NAME");
	}
	echo "<option value='".OCIResult($q,"ID")."'".$selected.">".OCIResult($q,"NAME")."</option>";
}
echo "</select>
<input type=submit name=ch_form value=ВЫБРАТЬ>";

if (isset($form_id) and $form_id<>'') {
echo " <a href=\"javascript:del_form('".$form_id."')\"><img src=del.gif title=\"Удалить форму\" border=0></a>";
}

echo "<hr>";

echo "<table>";
echo "<tr><td><font size=3><b>Название формы:</b></font></td><td><input size=60 type=text name=form_name value=\"".$form_name."\" onkeyup=ch_form_name()> | <font color=red>Кодировать АОН</font><input type=checkbox name=CODED_AON value=y ".$chk_CODED_AON."></td></tr>";
echo "<tr><td><font size=3>Отпавлять на email:</font></td><td>
АОН<input type=checkbox name=send_cdpn value=y ".$chk_send_cdpn."> | 
Номер доступа<input type=checkbox name=send_cgpn value=y ".$chk_send_cgpn."> |
ID оператора<input type=checkbox name=send_agid value=y ".$chk_send_agid."> |
</td></tr>";

echo "<tr><td><font size=3>Показывать в отчете:</font></td><td>

<nobr>длит.IVR(сек)<input type=checkbox name=show_ivr_sec value=y ".$chk_show_ivr_sec."> | </nobr>
<nobr>время в очереди(сек)<input type=checkbox name=show_queue_sec value=y ".$chk_show_queue_sec."> | </nobr>
<nobr>длит.КПВ(сек)<input type=checkbox name=show_alerting_sec value=y ".$chk_show_alerting_sec."> | </nobr>
<nobr>длит.разговора(сек)<input type=checkbox name=show_connected_sec value=y ".$chk_show_connected_sec."> | </nobr>
<nobr>длит.разговора(мин)<input type=checkbox name=show_connected_min value=y ".$chk_show_connected_min."> | </nobr>


<nobr>длит.вызова(сек)<input type=checkbox name=show_call_sec value=y ".$chk_show_call_sec."> | </nobr>
<nobr>длит.вызова(мин)<input type=checkbox name=show_call_min value=y ".$chk_show_call_min."></nobr>
</td></tr>";


echo "</table>";
echo "<input type=submit name=save_form value=Сохранить>";
if (isset($form_id) and $form_id<>'') {
echo "<input type=submit name=copy_form value=\"Копировать форму\">";
if (isset($_SESSION['admin']) and $_SESSION['admin']==1) echo "в другой проект<input type=checkbox name=copy_to_project>";
}
echo "<hr>";
//
if (isset($form_id) and $form_id<>'') {
	
	echo "<font size=4>Объекты Формы</font>";
	
	echo "<table id=tbl bgcolor=gray cellspacing=1 cellpadding=2>
	<tr>
	<td bgcolor=white width=3>Порядок</td>
	<td bgcolor=white width=3><b>Обяз.</b></td>
	<td bgcolor=white width=3><b>Тэг<br>перед</b></td>
	<td bgcolor=white><b>Название</b></td>
	<td bgcolor=white><b>Тип</b></td>
	<td bgcolor=white><b>Ширина</b></td>
	<td bgcolor=white><b>Высота</b></td>
	<td bgcolor=white><b>Перенос<br>строки</b></td>
	<td bgcolor=white width=3><b>Тэг<br>после</b></td>
	<td bgcolor=white></td>";
	echo "</tr>";
	
	//Добавить объект
	echo "<tr>
	<td bgcolor=green width=3></td>
	<td bgcolor=green align=center><input type=checkbox name=new_requre value=Да></td>
	<td bgcolor=green width=3><input type=text name=new_tag_before size=2></td>
	<td bgcolor=green><input type=text name=new_obj_name size=35 onkeyup=ch_new_obj_name()></td>";
	echo "<td bgcolor=green><select name=new_obj_type onchange=ch_new_obj_type()>";
	$q=OCIParse($c,"select * from sc_form_obj_type order by ord");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select></td>";
	
	echo "<td bgcolor=green><input type=text name=new_width size=3></td>
	<td bgcolor=green><input type=text name=new_height size=3></td>
	<td bgcolor=green align=center><input type=checkbox checked name=new_br value=Да></td>
	<td bgcolor=green width=3><input type=text name=new_tag_after size=2 value=\"<hr>\"></td>	
	<td bgcolor=green><input type=submit name=new_obj value=ДОБАВИТЬ disabled></td></tr>";
	//
	//Объекты формы
	$q=OCIParse($c,"select o.id,replace(o.name,'\"','&quot;') name,o.type_id,
	replace(replace(o.tag_before,'<','&lt;'),'>','&gt;') tag_before,
	t.name type_name,o.width,o.height,o.br,
	replace(replace(o.tag_after,'<','&lt;'),'>','&gt;') tag_after,
	o.invisible,o.ordering,o.requre 
	from sc_form_object o, sc_form_obj_type t
	where o.type_id=t.id and o.form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' 
	order by ordering");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr id =tr_".OCIResult($q,"ID").">
	<td bgcolor=white><nobr><a href=\"?up=1&obj_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&form_id=".$form_id."\"><img border=0 src=up.gif></a>
	<a href=\"?down=1&obj_id=".OCIResult($q,"ID")."&ordering=".OCIResult($q,"ORDERING")."&form_id=".$form_id."\"><img border=0 src=down.gif></a>
	".OCIResult($q,"ORDERING")."
	</nobr></td>
	<td bgcolor=white>".OCIResult($q,"REQURE")."</td>
	<td bgcolor=white width=3>".OCIResult($q,"TAG_BEFORE")."</td>
	<td bgcolor=white><b>".OCIResult($q,"NAME")."</b></td>
	<td bgcolor=white>";
	if (OCIResult($q,"TYPE_ID")=='SE' or OCIResult($q,"TYPE_ID")=='MS' or OCIResult($q,"TYPE_ID")=='RA' or OCIResult($q,"TYPE_ID")=='CH') {
	echo "<a href=edit_obj.php?obj_id=".OCIResult($q,"ID")."&form_id=".$form_id.">";
	}
	
	echo OCIResult($q,"TYPE_NAME")."</td>
	<td bgcolor=white>".OCIResult($q,"WIDTH")."</td>
	<td bgcolor=white>".OCIResult($q,"HEIGHT")."</td>
	<td bgcolor=white>".OCIResult($q,"BR")."</td>
	<td bgcolor=white width=3>".OCIResult($q,"TAG_AFTER")."</td>";
	echo "<td bgcolor=white><html>";
	echo "<a onclick=\"edit_obj('".OCIResult($q,"ID")."','".OCIResult($q,"TYPE_ID")."','".OCIResult($q,"TYPE_NAME")."')\"><img src=edit.gif title=\"Редактировать\" border=0></a>
		<a href=\"?del_obj=1&obj_id=".OCIResult($q,"ID")."&form_id=".$form_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";
	echo "</html></td>";
	echo "</tr>";
	}
	echo "</table>";
	//

	echo "<script language='javascript'>
	document.all.new_obj_type.onchange();
	
	function ch_new_obj_type() {
		if (document.all.new_obj_name.value=='') {
		document.all.new_obj.disabled=true;
		} else {
		document.all.new_obj.disabled=false;
		}
		if (document.all.new_obj_type.value=='TE') {
		document.all.new_width.value='500';	
		document.all.new_height.value='';						
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='';	
		document.all.new_tag_after.value='<hr>';
		}
		if (document.all.new_obj_type.value=='CT') {
		document.all.new_width.value='500';	
		document.all.new_height.value='';						
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='';	
		document.all.new_tag_after.value='<hr>';
		}
		if (document.all.new_obj_type.value=='SE') {
		document.all.new_width.value='';	
		document.all.new_height.value='';	
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='';	
		document.all.new_tag_after.value='<hr>';
		}
		if (document.all.new_obj_type.value=='MS') {
		document.all.new_width.value='';
		document.all.new_height.value='5';	
		document.all.new_height.style.display='';
		document.all.new_width.style.display='';	
		document.all.new_tag_after.value='<hr>';
		}	
		if (document.all.new_obj_type.value=='RA') {
		document.all.new_width.value='';
		document.all.new_height.value='';		
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='none';	
		document.all.new_tag_after.value='<hr>';
		}
		if (document.all.new_obj_type.value=='DA') {
		document.all.new_width.value='';
		document.all.new_height.value='';		
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='none';	
		document.all.new_tag_after.value='<hr>';
		}	
		if (document.all.new_obj_type.value=='TI') {
		document.all.new_width.value='';
		document.all.new_height.value='';		
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='none';	
		document.all.new_tag_after.value='<hr>';
		}	
		if (document.all.new_obj_type.value=='LT') {
		document.all.new_width.value='500';
		document.all.new_height.value='5';		
		document.all.new_height.style.display='';
		document.all.new_width.style.display='';	
		document.all.new_tag_after.value='<hr>';
		}
		if (document.all.new_obj_type.value=='CO') {
		document.all.new_width.value='';
		document.all.new_height.value='';		
		document.all.new_height.style.display='none';
		document.all.new_width.style.display='none';	
		document.all.new_tag_after.value='';
		}				
	}
	function ch_new_obj_name() {
		if (document.all.new_obj_name.value=='') {
		document.all.new_obj.disabled=true;
		} else {
		document.all.new_obj.disabled=false;
		}
	}
	</script>";
}

echo "</form>";

if (isset($form_id) and $form_id<>'') show_form($form_id,$c);

//Функция отображения формы
function show_form($form_id,$c) {
echo "<hr>";
echo "Внешний вид формы";

	$q=OCIParse($c,"select name, project_id from sc_forms where id='".$form_id."'");
	OCIExecute($q,OCI_DEFAULT);	
	OCIFetch($q);
	$form_name=OCIResult($q,"NAME")." (ID:".$form_id.")";

	if(OCIResult($q,"PROJECT_ID")==0) $color='#FFEEEE';
	else if(OCIResult($q,"PROJECT_ID")==$_SESSION['project']['id']) $color='#EEFFEE';
	else $color='';
	
	echo "<table border=0 bgcolor=gray cellspacing=1 cellpadding=2><tr><td bgcolor='".$color."'>";
	
$q_obj=OCIParse($c,"select * from sc_form_object
where form_id='".$form_id."' and type_id not in ('PU','PA','CP')
order by ordering");
OCIExecute($q_obj,OCI_DEFAULT);

echo "<font size=3><b>".$form_name."</b></font><hr>";

while(OCIFetch($q_obj)) {
//echo "<hr>";
echo OCIResult($q_obj,"TAG_BEFORE");
	//комментарий поле
	if (OCIResult($q_obj,"TYPE_ID")=='CO') {
		echo OCIResult($q_obj,"NAME");
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
	}
	//

	//номер отчета
	if (OCIResult($q_obj,"TYPE_ID")=='NR' or OCIResult($q_obj,"TYPE_ID")=='NP' or OCIResult($q_obj,"TYPE_ID")=='NZ') {
		echo "<b>".OCIResult($q_obj,"NAME").":</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
	}
	//
	//Непрерывные номера отчетов
	if (in_array(OCIResult($q_obj,"TYPE_ID"),array('N1','N2'))) {
	echo "<b>".OCIResult($q_obj,"NAME").":</b> <font color=red>(отобразится после сохранения формы)</font>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
	}
	//
	//текстовое поле
	if (OCIResult($q_obj,"TYPE_ID")=='TE' or OCIResult($q_obj,"TYPE_ID")=='CT') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<input style=\"width:".OCIResult($q_obj,"WIDTH")."\" type=text> ";
	}
	//
	//Большой текст
	if (OCIResult($q_obj,"TYPE_ID")=='LT') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<textarea style=\"width:".OCIResult($q_obj,"WIDTH")."\" rows=".OCIResult($q_obj,"HEIGHT")."></textarea> ";
	}
	//
	//выбор
	if (OCIResult($q_obj,"TYPE_ID")=='SE') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<select style=\"width:".OCIResult($q_obj,"WIDTH")."\">";

		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<option value=".OCIResult($q_val,"ID").">".OCIResult($q_val,"NAME")."</option>";
			}
		echo "</select> ";
	}
	//
	//множественный выбор
	if (OCIResult($q_obj,"TYPE_ID")=='MS') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<select multiple size=".OCIResult($q_obj,"HEIGHT")." style=\"width:".OCIResult($q_obj,"WIDTH")."\">";
		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<option value=".OCIResult($q_val,"ID").">".OCIResult($q_val,"NAME")."</option>";
			}
		echo "</select> ";
	}
	//
	//радио
	if (OCIResult($q_obj,"TYPE_ID")=='RA') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<input type=radio name=rad value=".OCIResult($q_val,"ID")."><nobr>".OCIResult($q_val,"NAME")."</nobr></input>";
			if (OCIResult($q_val,"BR")) echo "<br>";
			}
	}
	//
	//чекбокс
	if (OCIResult($q_obj,"TYPE_ID")=='CH') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<input type=checkbox value=".OCIResult($q_val,"ID")."><nobr>".OCIResult($q_val,"NAME")."</nobr></input>";
			if (OCIResult($q_val,"BR")) echo "<br>";
			}
	}
	//
	//Дата
	if (OCIResult($q_obj,"TYPE_ID")=='DA') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		echo "<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.form_".$form_id.".obj_".OCIResult($q_obj,"ID").");return false; HIDEFOCUS>
		<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 
		echo "<input type=text size=9 value='дд.мм.гггг'> ";
	}
	//
	//Время
	if (OCIResult($q_obj,"TYPE_ID")=='TI') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		echo "<input type=text size=5 value=чч:мм></input> ";
	}
	//Дата и время
	if (OCIResult($q_obj,"TYPE_ID")=='DT') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		echo "<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.form_".$form_id.".obj_".OCIResult($q_obj,"ID").");return false; HIDEFOCUS>
		<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 
		echo "<input type=text size=17 value='дд.мм.гггг чч:мм'> ";
	}
	//	
	//число
	if (OCIResult($q_obj,"TYPE_ID")=='NU') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b> (ID:".OCIResult($q_obj,"ID").")";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<input size=6 type=text> ";
	}
	//	
	//комментарий поле
	if (OCIResult($q_obj,"TYPE_ID")=='HI') {
		echo "<font color=gray>".OCIResult($q_obj,"NAME")." (ID:".OCIResult($q_obj,"ID").")</font>";
	}
	//	
echo OCIResult($q_obj,"TAG_AFTER");	
//
}
echo "<input type=button disabled value=ОТПРАВИТЬ>";
echo "</b></td></tr></table></form>";
} //Функция отображения формы

//Функция вверх
	function up($form_id,$obj_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(max(ordering),0) max_ord from sc_form_object
where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' and ordering<'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
		if (OCIResult($q,"MAX_ORD")>0) {
		$max_ord=OCIResult($q,"MAX_ORD");
		$upd=OCIParse($c,"update sc_form_object set ordering=ordering+".$ordering."-".$max_ord."+1
		where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' 
		and ordering>='".$max_ord."' and ordering<>'".$ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//
//Функция вниз
	function down($form_id,$obj_id,$ordering,$c) {
	
	$q=OCIParse($c,"select nvl(min(ordering),0) min_ord from sc_form_object
where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' and ordering>'".$ordering."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
		if (OCIResult($q,"MIN_ORD")>0) {
		$min_ord=OCIResult($q,"MIN_ORD");
		$upd=OCIParse($c,"update sc_form_object set ordering=ordering+".$min_ord."-".$ordering."+1
		where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' 
		and (ordering>'".$min_ord."' or ordering='".$ordering."')");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
		}
	}
//
//Функция добавления объекта
function new_obj($form_id,$new_obj_name,$new_obj_type,$new_width,$new_height,$new_br,$new_tag_before,$new_tag_after,$new_requre,$c) {
	$q=OCIParse($c,"select nvl(max(ordering),0)+1 ordering from sc_form_object
where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$ordering=OCIResult($q,"ORDERING");
	$ins=OCIParse($c,"insert into sc_form_object (id,form_id,name,type_id,width,height,br,ordering,project_id,tag_before,tag_after,requre)
	values (
	SEQ_SC_FORM_OBJ_ID.nextval,
	'".$form_id."',
	'".$new_obj_name."',
	'".$new_obj_type."',
	'".$new_width."',
	'".$new_height."',
	'".$new_br."',
	'".$ordering."',
	'".$_SESSION['project']['id']."',
	'".$new_tag_before."',
	'".$new_tag_after."',
	'".$new_requre."'
	)");
	OCIExecute($ins,OCI_DEFAULT);
	OCICommit($c);	
}//
//Функция удаления объекта
function del_obj($form_id,$obj_id,$c) {
	$del=OCIParse($c,"delete from sc_form_object 
	where project_id='".$_SESSION['project']['id']."' and form_id='".$form_id."' and id='".$obj_id."'");
	OCIExecute($del,OCI_DEFAULT);
	OCICommit($c);	
}//
//Функция добавления и изменения формы
function save_form($form_id,$form_name,$c,$send_cdpn,$send_cgpn,$send_agid,$show_ivr_sec,$show_queue_sec,$show_alerting_sec,$show_connected_sec,$show_connected_min,$show_call_sec,$show_call_min,$CODED_AON) {
	if ($form_id=='') {
	$q=OCIParse($c,"select seq_sc_form_id.nextval from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_form_id=OCIResult($q,"NEXTVAL");
	$ins=OCIParse($c,"insert into sc_forms (id,name,project_id,send_cdpn,send_cgpn,send_agid,show_ivr_sec,show_queue_sec,show_alerting_sec,show_connected_sec,show_connected_min,show_call_sec,show_call_min,CODED_AON)
	values (
	'".$new_form_id."',
	'".$form_name."',
	'".$_SESSION['project']['id']."',
	'".$send_cdpn."',
	'".$send_cgpn."',
	'".$send_agid."',
	'".$show_ivr_sec."',
	'".$show_queue_sec."',
	'".$show_alerting_sec."',
	'".$show_connected_sec."',
	'".$show_connected_min."',
	'".$show_call_sec."',
	'".$show_call_min."',
	'".$CODED_AON."')");
		if (@OCIExecute($ins,OCI_DEFAULT)) {
		OCICommit($c);
		$form_id=$new_form_id;
		} 
		else {
		echo "<font color=red>ОШИБКА! форма с таким именем и паролем уже существует!</font>";
		}
	}
	else {
	$upd=OCIParse($c,"update sc_forms set name='".$form_name."', send_cdpn='".$send_cdpn."', send_cgpn='".$send_cgpn."', send_agid='".$send_agid."',	show_ivr_sec='".$show_ivr_sec."',show_queue_sec='".$show_queue_sec."',show_alerting_sec='".$show_alerting_sec."',show_connected_sec='".$show_connected_sec."', show_connected_min='".$show_connected_min."', show_call_sec='".$show_call_sec."', show_call_min='".$show_call_min."', CODED_AON='".$CODED_AON."'
	where id='".$form_id."' and project_id='".$_SESSION['project']['id']."'");
		if (@OCIExecute($upd,OCI_DEFAULT)) {OCICommit($c);}
		else {
		echo "<font color=red>ОШИБКА! Форма с таким именем и паролем уже существует!</font>";
		}
	}
return $form_id;
}//
//Функция копирования формы
function copy_form($form_id,$project_id,$c) {
	$old_project_id=$_SESSION['project']['id'];
	if ($project_id=='') $new_project_id=$_SESSION['project']['id']; else  $new_project_id=$project_id;
	
	$q=OCIParse($c,"select SEQ_SC_FORM_ID.nextval form_id from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$new_form_id=OCIResult($q,"FORM_ID");
	
	$ins=OCIParse($c,"insert into sc_forms (id,name,send_cdpn,send_cgpn,send_agid,show_ivr_sec,show_queue_sec,show_alerting_sec,show_connected_sec,show_connected_min,show_call_sec,show_call_min,project_id,CODED_AON)
	select '".$new_form_id."','Копия-'||name,send_cdpn,send_cgpn,send_agid,show_ivr_sec,show_queue_sec,show_alerting_sec,show_connected_sec,show_connected_min,show_call_sec,show_call_min,'".$new_project_id."',CODED_AON from sc_forms where id='".$form_id."'");
	OCIExecute($ins,OCI_DEFAULT);

	$sel=OCIParse($c,"select 
	SEQ_SC_FORM_OBJ_ID.nextval new_obj_id,id,'".$new_form_id."' form_id,name,type_id,width,height,br,invisible,ordering,project_id,tag_before,tag_after,requre 
	from sc_form_object where form_id='".$form_id."' and project_id='".$old_project_id."'");
	OCIExecute($sel,OCI_DEFAULT);
		while (OCIFetch($sel)) {
	
			$ins2=OCIParse($c,"insert into sc_form_object (id,form_id,name,type_id,width,height,br,invisible,ordering,project_id,tag_before,tag_after,requre)
			values (
			'".OCIResult($sel,"NEW_OBJ_ID")."',
			'".OCIResult($sel,"FORM_ID")."',
			'".OCIResult($sel,"NAME")."',
			'".OCIResult($sel,"TYPE_ID")."',
			'".OCIResult($sel,"WIDTH")."',
			'".OCIResult($sel,"HEIGHT")."',
			'".OCIResult($sel,"BR")."',
			'".OCIResult($sel,"INVISIBLE")."',
			'".OCIResult($sel,"ORDERING")."',
			'".$new_project_id."',
			'".OCIResult($sel,"TAG_BEFORE")."',
			'".OCIResult($sel,"TAG_AFTER")."',
			'".OCIResult($sel,"REQURE")."')");
			OCIExecute($ins2,OCI_DEFAULT);
			
			$ins3=OCIParse($c,"insert into sc_form_values (id,name,dop_info,obj_id,project_id,ordering,br)
	select SEQ_SC_FORM_VAL_ID.nextval,name,dop_info,'".OCIResult($sel,"NEW_OBJ_ID")."','".$new_project_id."',ordering,br from sc_form_values where obj_id='".OCIResult($sel,"ID")."' and project_id='".$old_project_id."'");
		OCIExecute($ins3,OCI_DEFAULT);
		}
	$ins4=OCIParse($c,"insert into sc_form_email (id,email,send_online,form_id,project_id)
	select SEQ_EMAIL_ID.nextval,email,send_online,'".$new_form_id."','".$new_project_id."' from sc_form_email where form_id='".$form_id."' and project_id='".$new_project_id."'");
	OCIExecute($ins4,OCI_DEFAULT);
	OCICommit($c);	
if ($project_id=='') return $new_form_id; else return $form_id;
}//
//Функция удаления формы
function del_form($form_id,$c) {
	
	$del=OCIParse($c,"delete from sc_body where form_id is not null and form_id='".$form_id."'");
	OCIExecute($del,OCI_DEFAULT);
	$del2=OCIParse($c,"delete from sc_acc_forms where form_id='".$form_id."'");
	OCIExecute($del2,OCI_DEFAULT);
	$upd=OCIParse($c,"update sc_forms set deleted=sysdate where id='".$form_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($upd,OCI_DEFAULT);
	
	/*$del2=OCIParse($c,"delete from sc_forms where id='".$form_id."' and project_id='".$_SESSION['project']['id']."'");
	OCIExecute($del2,OCI_DEFAULT);*/

	OCICommit($c);	
}//
//Функция сохранения объекта
function sav_obj($obj_id,$sav_obj_name,$sav_obj_type,$sav_obj_width,$sav_obj_height,$sav_obj_br,$sav_tag_before,$sav_tag_after,$sav_ordering,$sav_requre,$c) {
	
	$q=OCIParse($c,"select ordering,form_id from sc_form_object where project_id='".$_SESSION['project']['id']."' and id='".$obj_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$form_id=OCIResult($q,"FORM_ID");
	if (OCIResult($q,"ORDERING")==trim($sav_ordering)) {}
	else {
		$upd_ord=OCIParse($c,"update sc_form_object set ordering=ordering+1
		where form_id='".$form_id."' and project_id='".$_SESSION['project']['id']."' 
		and ordering>='".$sav_ordering."'");
		OCIExecute($upd_ord,OCI_DEFAULT);
	}
	
	$upd=OCIParse($c,"update sc_form_object 
	set 
	ordering='".$sav_ordering."',
	name='".$sav_obj_name."',
	type_id='".$sav_obj_type."',
	width='".$sav_obj_width."',
	height='".$sav_obj_height."',
	br='".$sav_obj_br."',
	tag_before='".$sav_tag_before."',
	tag_after='".$sav_tag_after."',
	requre='".$sav_requre."'
	where project_id='".$_SESSION['project']['id']."' and id='".$obj_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);	
}//
?>
<script language='javascript'>
document.all.ch_form.style.display='none';
ch_form_name();
function ch_form_name() {
if (document.all.form_name.value=='') {document.all.save_form.disabled=true;}
else {document.all.save_form.disabled=false;}
}

function del_form(form_id) {
if (confirm('Действительно хотите УДАЛИТЬ ФОРМУ ?')) document.location='?del_form=1&form_id='+form_id;
}

function edit_obj(obj_id,type_id,type_name) {
if (!document.all.sav_obj) {
	with(document.all.tbl) {
	rows['tr_'+obj_id].cells[0].innerHTML='<input type=text name=sav_ordering size=2 value="'+rows['tr_'+obj_id].cells[0].innerText+'">';

	if (rows['tr_'+obj_id].cells[1].innerText=='Да') {
	rows['tr_'+obj_id].cells[1].innerHTML='<input type=checkbox checked name=sav_requre value=Да>';}
	else {rows['tr_'+obj_id].cells[1].innerHTML='<input type=checkbox name=sav_requre value=Да>';}	

	rows['tr_'+obj_id].cells[2].innerHTML='<input type=text name=sav_tag_before size=2 value="'+rows['tr_'+obj_id].cells[2].innerText+'">';
	
	v_name=rows['tr_'+obj_id].cells[3].innerText;
	rows['tr_'+obj_id].cells[3].innerHTML='<input type=text name=sav_obj_name size=35 value="'+v_name.replace('"','&quot;')+'">';
	//rows['tr_'+obj_id].cells[1].innerHTML='<input type=text name=sav_obj_name size=35 value="'+rows['tr_'+obj_id].cells[1].innerText+'">';

	rows['tr_'+obj_id].cells[4].innerHTML='<select name=sav_obj_type><option value='+type_id+'>'+type_name+'</option><option value=TE>Текст</option><option value=LT>Большой текст</option><option value=DA>Дата</option><option value=TI>Время</option><option value=SE>Выбор</option><option value=MS>Множественный выбор</option><option value=RA>Радио</option><option value=CH>Галочки</option><option value=CT>Телефон кодированный</option>';
	
	rows['tr_'+obj_id].cells[5].innerHTML='<input type=text name=sav_obj_width size=3 value='+rows['tr_'+obj_id].cells[5].innerText+'>';
	rows['tr_'+obj_id].cells[6].innerHTML='<input type=text name=sav_obj_height size=3 value='+rows['tr_'+obj_id].cells[6].innerText+'>';	
	
	if (rows['tr_'+obj_id].cells[7].innerText=='Да') {
	rows['tr_'+obj_id].cells[7].innerHTML='<input type=checkbox checked name=sav_obj_br value=Да>';}
	else {rows['tr_'+obj_id].cells[7].innerHTML='<input type=checkbox name=sav_obj_br value=Да>';}

	rows['tr_'+obj_id].cells[8].innerHTML='<input type=text name=sav_tag_after size=2 value="'+rows['tr_'+obj_id].cells[8].innerText+'">';
	rows['tr_'+obj_id].cells[9].innerHTML='<input type=hidden name=obj_id value='+obj_id+'><input type=submit name=sav_obj value=СОХРАНИТЬ>';
	}
}
}
</script>