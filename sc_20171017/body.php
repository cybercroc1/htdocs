<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("../../sc_conf/sc_session");
session_start();
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<?php if (!isset($_SESSION['i'])) exit(); 
if ($_SESSION['ch_sc'][$_SESSION['i']]<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<script>
function show_hide(blog_id) {
aaa=eval('document.all.tbl_'+blog_id);
if (aaa.rows[1].style.display=='') {
	for (i=1; i<aaa.rows.length; i++) {
		aaa.rows[i].style.display='none';}
	}
else {
	for (i=1; i<aaa.rows.length; i++) {
		aaa.rows[i].style.display='';}
	}
}
function show(blog_id) {
aaa=eval('document.all.tbl_'+blog_id);
	for (i=1; i<aaa.rows.length; i++) {
		aaa.rows[i].style.display='';
	}
document.location='#'+blog_id;
}
function transfer() {}
<?php
include("../../sc_conf/sc_path");
echo "function open_local(filename) {
open('".str_replace('\\','\\\\',$net_path_to_folders).$_SESSION['project_name'][$_SESSION['i']]."\\\'+filename);
}";
?>
</script>
<body>
<a name=top></a>
<?php

extract($_REQUEST);

include("../../sc_conf/sc_conn_string");

if (isset($up)) {
		if ($general=='y') {
			$where_punkt_id="";
			} else {
				if ($punkt_id=='') {
				$where_punkt_id=" and punkt_id is null";
				} else {
				$where_punkt_id=" and punkt_id='".$punkt_id."'";
				}
			}
	$q=OCIParse($c,"select max(ordering) perv_ordering from sc_body
	where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
	".$where_punkt_id."
	and ordering<'".$ordering."'
	and general='".$general."'
	and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
	if (OCIFetch($q) and OCIResult($q,"PERV_ORDERING")<>NULL) {
		$perv_ordering=OCIResult($q,"PERV_ORDERING");
		$upd=OCIParse($c,"update sc_body set ordering='".$ordering."'
		where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
		".$where_punkt_id."
		and general='".$general."'
		and ordering='".$perv_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_body set ordering='".$perv_ordering."' where id='".$blog_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
	}
}

if (isset($down)) {
		if ($general=='y') {
			$where_punkt_id="";
			} else {
				if ($punkt_id=='') {
				$where_punkt_id=" and punkt_id is null";
				} else {
				$where_punkt_id=" and punkt_id='".$punkt_id."'";
				}
			}
	$q=OCIParse($c,"select min(ordering) next_ordering from sc_body
	where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
	".$where_punkt_id."
	and ordering>'".$ordering."'
	and general='".$general."'
	and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
	if (OCIFetch($q) and OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_body set ordering='".$ordering."'
		where project_id='".$_SESSION['project_id'][$_SESSION['i']]."'
		".$where_punkt_id."
		and general='".$general."'
		and ordering='".$next_ordering."'");
		OCIExecute($upd,OCI_DEFAULT);
		$upd2=OCIParse($c,"update sc_body set ordering='".$next_ordering."' where id='".$blog_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		OCICommit($c);
	}
}

if (isset($invisible)) {
	$q=OCIParse($c,"select count(*) cnt from sc_body where punkt_id='".$punkt_id."' and general='n' and invisible is null");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
		if (OCIResult($q,"CNT")<=1) {
		$upd2=OCIParse($c,"update sc_punkt set with_blog=null where id='".$punkt_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
		}
$upd=OCIParse($c,"update sc_body set invisible='1' where id='".$blog_id."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

if (isset($visible)) {
	if ($general=='n') {
		$upd2=OCIParse($c,"update sc_punkt set with_blog=1 where id='".$punkt_id."'");
		OCIExecute($upd2,OCI_DEFAULT);
	}
$upd=OCIParse($c,"update sc_body set invisible=null where id='".$blog_id."'");
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);
}

if (!isset($punkt_id)) {$punkt_id=''; $tree_id='';}

//общий текст вверху сценария
echo "<font color=red>Текст вверху сценария, общий для всех пунктов:</font>
<a href=\"edit_body.php?add_blog=1&ordering=1&punkt_id=".$punkt_id."&general=y&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
<br>";

$q=OCIParse($c,"select b.*,s.name shedule_name from sc_body b, sc_shedule s 
where b.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and b.general='y'
and b.shedule_id=s.id(+)
order by b.ordering");
OCIExecute($q, OCI_DEFAULT);
$TA=0;
while (OCIFetch($q)) {
	//Табличный блок
	if (OCIResult($q,"TYPE")=='TA') {
	if ($TA==0) echo "<table border=0 bgcolor=gray cellspacing=1 cellpadding=2>";

	show_table_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"FAQ"));

	$TA=1;
	}
	else {if ($TA==1) {echo "</table>"; $TA=0;}
	}
	//
	//Динамическая таблица
	if (OCIResult($q,"TYPE")=='DT') {
	show_dinamic_table(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"TABLE_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//
	//Текстовый блок
	if (OCIResult($q,"TYPE")=='TE') {
show_text_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),OCIResult($q,"TXT_TAG_BEFORE"),OCIResult($q,"TXT_TAG_AFTER"),OCIResult($q,"TXT_ALIGN"),$tree_id);
	}
	//
	//Форма
	if (OCIResult($q,"TYPE")=='FO') {
show_form(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	}
	//
	//HTML файл
	if (OCIResult($q,"TYPE")=='FI') {
show_html_file(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
	}
	//
	//Список переадресации
	if (OCIResult($q,"TYPE")=='LI') {
show_forw_list(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORW_LIST_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//		
}
if ($TA==1) {echo "</table>"; $TA=0;}
echo "<hr>";
//

//наименование пункта сценария
echo "<a href=\"edit_body.php?add_blog=1&blog_type=1&ordering=1&punkt_id=".$punkt_id."&general=n&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a> ";
if ($punkt_id<>'') {
$q=OCIParse($c,"select p.text from sc_punkt_tree t, sc_punkt p
where t.punkt_id=p.id
connect by prior t.parent_id=t.punkt_id start with t.id='".$tree_id."'  
order by t.lvl");
OCIExecute($q,OCI_DEFAULT);
$i=0;
echo "<a name='p".$punkt_id."'><font size=3><b>";
	while (OCIFetch($q)) {
	if ($i>0) echo " / ";
	echo OCIResult($q,"TEXT");
	$i++;
}
echo "</b></font><br>";
}
//

if (!isset($punkt_id) or $punkt_id=='') $where_punkt_id=" is null"; else $where_punkt_id="='".$punkt_id."'";

$q=OCIParse($c,"select b.*,s.name shedule_name from sc_body b, sc_shedule s
where b.project_id='".$_SESSION['project_id'][$_SESSION['i']]."' and b.punkt_id".$where_punkt_id." and b.general='n'
and b.shedule_id=s.id(+)
order by b.ordering");
OCIExecute($q, OCI_DEFAULT);
$TA=0;
while (OCIFetch($q)) {
	//Табличный блок
	if (OCIResult($q,"TYPE")=='TA') {
	if ($TA==0) echo "<table border=0 bgcolor=gray cellspacing=1 cellpadding=2>";
	show_table_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"FAQ"));
	$TA=1;
	}
	else {if ($TA==1) {echo "</table>"; $TA=0;}
	}
	//
	//Динамическая таблица
	if (OCIResult($q,"TYPE")=='DT') {
	show_dinamic_table(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"TABLE_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//	
	//Текстовый блок
	if (OCIResult($q,"TYPE")=='TE') {
show_text_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),OCIResult($q,"TXT_TAG_BEFORE"),OCIResult($q,"TXT_TAG_AFTER"),OCIResult($q,"TXT_ALIGN"),$tree_id);
	}
	//
	//Форма
	if (OCIResult($q,"TYPE")=='FO') {
show_form(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	}
	//
	//HTML файл
	if (OCIResult($q,"TYPE")=='FI') {
show_html_file(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
	}
	//	
	//Список переадресации
	if (OCIResult($q,"TYPE")=='LI') {
show_forw_list(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORW_LIST_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//		
}
if ($TA==1) {echo "</table>"; $TA=0;}

//Функция отображения HTML файла
function show_html_file($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$shedule_name,$tree_id) {
	include("../../sc_conf/sc_path");
	include("../../sc_conf/sc_local_network");
	global $c;
	$call_id='';
	$project_id='';
	$cdpn='';
	$cgpn='';
	$agid='';
	$aon='';
	echo  "<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FI&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FI&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=FI&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=FI&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a> 
	(файл: <font color=red>";
	if ($from_local_addr=='y') echo "<a href='file://".$net_path_to_folders.$_SESSION['project_name'][$_SESSION['i']]."\\' target='_blank'>";
	echo $head;
	if ($from_local_addr=='y') echo "</a>";
	echo "</font>)";

include($path_to_folders.$_SESSION['project_name'][$_SESSION['i']]."\\".$head);

	echo "<br>";
}//

//Функция отображения табличного блока

function show_table_blog($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$body,$shedule_name,$tree_id,$faq) {
	echo "<tr>
	<td bgcolor=white valign=top><nowrap>
	<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=TA&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=TA&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=TA&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=TA&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."&faq=".$faq."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a></nowrap><br>
	";
	if ($faq=='y') {echo "<input type=checkbox disabled>";}
	echo nl2br($head)."</td>
	<td bgcolor=white valign=top>".nl2br($body)."</td>
	</tr>";
}//
//Функция отображения динамической таблицы
function show_dinamic_table($invisible,$blog_id,$punkt_id,$ordering,$general,$table_id,$shedule_name,$tree_id,$colapsed,$c) {
	
	echo  "<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=DT&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=DT&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_table.php?add_blog=1&blog_type=DT&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_table.php?edit_blog=1&blog_id=".$blog_id."&table_id=".$table_id."&blog_type=DT&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a> ";

$q=OCIParse($c,"select name,style,attrib from sc_dynamic_table
where id='".$table_id."'");
OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		echo "(таблица: <font color=red>".OCIResult($q,"NAME")."</font>)";
		echo '<table id="tbl"'.OCIResult($q,"ATTRIB").' style="'.OCIResult($q,"STYLE").'">';
	}
	$rgx="/(\[[^\[^\]]+\])
	|
	(
	  (\(?
        (
          (((8([ ()_{}\[\]\-]|(&nbsp;))*1([ ()_{}\[\]\-]|(&nbsp;))*0([ ()_{}\[\]\-]|(&nbsp;))*)|\+)
          (\d([ ()_{}\[\]\-]|(&nbsp;))*){6,10}
        )
        |
        ([78]([ ()_{}\[\]\-]|(&nbsp;))*(\d([ ()_{}\[\]\-]|(&nbsp;))*){5}){1}
        |
        ((\d([ ()_{}\[\]\-]|(&nbsp;))*){5}){1}
        |
        (([1-79]([ ()_{}\[\]\-]|(&nbsp;))*){1}(\d([ ()_{}\[\]\-]|(&nbsp;))*){1})
      )
      (\d([ )_{}\[\]\-]|(&nbsp;))*){4}\d{1}
      |
      (\[(([ )_{}\-]|(&nbsp;))*\d([ )_{}\-]|(&nbsp;))*){4,6}\])
    )
	((?=\D)|(?=$)))
/ix";
$q_row=OCIParse($c,"select row_num,attrib,style,height from sc_dynamic_table_rows
where table_id='".$table_id."' order by row_num");
$q_cell=OCIParse($c,"select cell_num,attrib,style,nvl(html,'') html,faq_id,phones,width,height from sc_dynamic_table_cells
where table_id='".$table_id."' and row_num=:row_num and cell_num>0 and display is null
order by cell_num");
OCIExecute($q_row,OCI_DEFAULT);
	while(OCIFetch($q_row)) {
		$row_num=OCIResult($q_row,"ROW_NUM");
		if($row_num>0) echo '<tr'.OCIResult($q_row,"ATTRIB").' style="height:'.OCIResult($q_row,"HEIGHT").';'.OCIResult($q_row,"STYLE").'">';
		OCIBindByName($q_cell,":row_num",$row_num);
		OCIExecute($q_cell,OCI_DEFAULT);
		while(OCIFetch($q_cell)) {
				if($row_num==0) {
					echo '<col style="width:'.OCIResult($q_cell,"WIDTH").';">';
				}
				else {
					echo '<td'.OCIResult($q_cell,"ATTRIB").' style="'.OCIResult($q_cell,"STYLE").'">';
				
					if(OCIResult($q_cell,"HTML")<>'') {
					
						if(OCIResult($q_cell,"FAQ_ID")<>'') {
							echo "<input type=checkbox>";
						}
						if(OCIResult($q_cell,"PHONES")=='y') {
							echo preg_replace_callback($rgx,'conv',OCIResult($q_cell,"HTML")->load());
						}
						else echo OCIResult($q_cell,"HTML")->load();
					}
				echo '</td>';
				}
			}
		echo '</tr>';	
	}
echo "</table>";
}//
//Функция конвертации телефонных номеров
function conv($t) {
	$href=preg_replace('/[^0-9^\]^\[]/','',$t[0]); //оставляем только квадратные скобки и цифры
	if(preg_match('/\[\d+\]/',$href)) { //если номер в квадратных скобках
		$href=str_replace(array('[',']'),'',$href); //удаляем скобки
		return "<a href=\"javascript:alert('$href')\" title='".$href."'>".$t[0]."</a>"; //возвращаем ссылку		
	}
	elseif(strlen($href)>=4 and strlen($href)<=6) {}
	elseif(substr($href,0,1)=='7' and strlen($href)=='11') $href="8".substr($href,1); 
	elseif(substr($href,0,4)=="8107") $href="8".substr($href,4);
	elseif(strlen($href)==10) $href="8".$href;
	elseif(strlen($href)>=11 and substr($href,0,1)<>"8" and substr($href,0,1)<>"7") $href="810".$href;
	if((strlen($href)==11 or strlen($href)==10) and substr($href,-10,3)=='095') $href='8495'.substr($href,-7);
	if(preg_match("/^((\d{4,6})|([1-79][\d]{6})|(8(([02-9]\d)|(1[1-9]))\d{8})|(810[1-68-9]\d{10,14}))$/",$href)) {
		return "<a href=\"javascript:alert('$href')\" title='".$href."'>".$t[0]."</a>";
	}
	else return "<font color=red title='Номер ".$href." не пригоден для набора'>".$t[0]."</font>";
} 
//
//Функция отображения текстового блока
function show_text_blog($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$body,$shedule_name,$txt_tag_before,$txt_tag_after,$txt_align,$tree_id) {
	
	echo  "<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=TE&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=TE&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=TE&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=TE&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0> </a>";
//	if ($head<>NULL) echo $head."<br>";
	echo "<div".$txt_align.">".$txt_tag_before.nl2br($body).$txt_tag_after."</div>";
	//echo "<br>";
}//

//Функция отображения формы
function show_form($invisible,$blog_id,$punkt_id,$ordering,$general,$form_id,$shedule_name,$tree_id,$colapsed,$new_window,$c) {
	echo "<table style=form_tbl id=tbl_".$blog_id." border=0 bgcolor=gray cellspacing=1 cellpadding=2>
	<form name=form_".$form_id."_".$blog_id." action=send.php method=post target=blank_frame><tr><td bgcolor=#EEFFEE>";

	$q=OCIParse($c,"select name from sc_forms where id='".$form_id."'");
	OCIExecute($q,OCI_DEFAULT);	
	OCIFetch($q);
	$form_name=OCIResult($q,"NAME");
	
	//кнопки редактирования
	echo  "<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FO&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FO&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=FO&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=FO&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."&colapsed=".$colapsed."&new_window=".$new_window."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a><br>";
	
	if ($new_window=='y') {
	echo "<a><font size=3><b>".$form_name."</b></a><font color=red> (откроется в новом окне)</font></font>";
	echo "</tr></td></form></table>";
	return;
	}	
	elseif ($colapsed=='y') echo "<a href=javascript:show_hide('".$blog_id."')><font size=3><b>".$form_name."</b></font></a>";
	else echo "<font size=3><b>".$form_name."</b></font>";
	//
	
$q_obj=OCIParse($c,"select * from sc_form_object
where form_id='".$form_id."' and type_id not in ('PU','PA','CP')
order by ordering");
OCIExecute($q_obj,OCI_DEFAULT);

echo "<input type=hidden name=project_id value='".$_SESSION['project_id'][$_SESSION['i']]."'>";
echo "<input type=hidden name=form_id value='".$form_id."'>";
echo "<input type=hidden name=form_name value='".$form_name."'></td></tr>";
echo "<tr><td bgcolor=#EEFFEE>";
while(OCIFetch($q_obj)) {
echo OCIResult($q_obj,"TAG_BEFORE");
	//Комментарий
	if (OCIResult($q_obj,"TYPE_ID")=='CO') {
		echo OCIResult($q_obj,"NAME");
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
	}
	//
	//текстовое поле
	if (OCIResult($q_obj,"TYPE_ID")=='TE' or OCIResult($q_obj,"TYPE_ID")=='CT') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<input style=\"width:".OCIResult($q_obj,"WIDTH")."\" type=text name=obj_".OCIResult($q_obj,"ID")."> ";
	}
	//
	//Большой текст
	if (OCIResult($q_obj,"TYPE_ID")=='LT') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<textarea style=\"width:".OCIResult($q_obj,"WIDTH")."\" rows=".OCIResult($q_obj,"HEIGHT")." name=obj_".OCIResult($q_obj,"ID")."></textarea> ";
	}
	//
	//выбор
	if (OCIResult($q_obj,"TYPE_ID")=='SE') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<select name=obj_".OCIResult($q_obj,"ID")." style=\"width:".OCIResult($q_obj,"WIDTH")."\">";

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
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";
		echo "<select multiple size=".OCIResult($q_obj,"HEIGHT")." name=obj_".OCIResult($q_obj,"ID")."[] style=\"width:".OCIResult($q_obj,"WIDTH")."\">";
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
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<input type=radio name=obj_".OCIResult($q_obj,"ID")." value=".OCIResult($q_val,"ID")."><nobr>".OCIResult($q_val,"NAME")."</nobr></input>";
			if (OCIResult($q_val,"BR")) echo "<br>";
			}
	}
	//
	//чекбокс
	if (OCIResult($q_obj,"TYPE_ID")=='CH') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		$q_val=OCIParse($c,"select * from sc_form_values where obj_id=".OCIResult($q_obj,"ID")." order by ordering");
		OCIExecute($q_val,OCI_DEFAULT);
			while (OCIFetch($q_val)) {
			echo "<input type=checkbox name=obj_".OCIResult($q_obj,"ID")."[] value=".OCIResult($q_val,"ID")."><nobr>".OCIResult($q_val,"NAME")."</nobr></input> ";
			if (OCIResult($q_val,"BR")) echo "<br>";
			}
	}
	//
	//Дата
	if (OCIResult($q_obj,"TYPE_ID")=='DA') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		echo "<a href=javascript:void(0) onClick=if(self.gfPop)gfPop.fPopCalendar(document.form_".$form_id."_".$blog_id.".obj_".OCIResult($q_obj,"ID").");return false; HIDEFOCUS>
		<img class=PopcalTrigger align=absmiddle src=clndrxp94/calbtn.gif width=34 height=22 border=0 alt=Календарь></A>"; 
		echo "<input type=text name=obj_".OCIResult($q_obj,"ID")." size=9> ";
	}
	//
	//Время
	if (OCIResult($q_obj,"TYPE_ID")=='TI') {
		echo "<b>".OCIResult($q_obj,"NAME")."</b>";
		if (OCIResult($q_obj,"BR")=='Да') echo "<br>";		
		echo "<input type=text size=1 maxlength=2 name=obj_".OCIResult($q_obj,"ID")."_hh>ч
		 <input type=text size=1 maxlength=2 name=obj_".OCIResult($q_obj,"ID")."_mi>м ";
	}
	//
echo OCIResult($q_obj,"TAG_AFTER");		
}
echo "<input type=submit name=send disabled value=ОТПРАВИТЬ>";
echo "</b></td></tr></form></table>";
echo "<script>";
if ($colapsed=='y') echo "document.all.tbl_".$blog_id.".rows[1].style.display='none';";
echo "function form_".$form_id."_".$blog_id.".send.onclick() {
form_".$form_id."_".$blog_id.".send.disabled=true;
form_".$form_id."_".$blog_id.".submit();
form_".$form_id."_".$blog_id.".send.value='ОТПРАВЛЕНА';
}
</script>";
} //Функция отображения формы

//Функция отображения списка переадресации
function show_forw_list($invisible,$blog_id,$punkt_id,$ordering,$general,$list_id,$shedule_name,$tree_id,$colapsed,$c) {

$q=OCIParse($c,"select * from sc_forw_list where id=".$list_id." and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$e=OCIResult($q,"EMAIL");
$g=OCIResult($q,"GRAFIK");
$o=OCIResult($q,"OTDEL");
$f=OCIResult($q,"FIO");
$d=OCIResult($q,"DOLJNOST");
$co=OCIResult($q,"COMENT");
$row_count=OCIResult($q,"ROW_COUNT");
$colspan=$e+$g+$o+$f+$d+$co+1;
if (OCIResult($q,"ORDER_BY")=='' or OCIResult($q,"ORDER_BY")=='как есть') $order_by=" order by ordering ";
if (OCIResult($q,"ORDER_BY")=='случайно') $order_by=" order by dbms_random.value ";
if (OCIResult($q,"ORDER_BY")=='по кругу') {
	$order_by=" order by ordering ";
	$sel=OCIParse($c,"select min(ordering) min, max(ordering) max from sc_forw_fio where list_id='".$list_id."'");
	OCIExecute($sel,OCI_DEFAULT);
	OCIFetch($sel);
	$min=OCIResult($sel,"MIN"); $max=OCIResult($sel,"MAX");
	$upd=OCIParse($c,"update sc_forw_fio set ordering=".$max."+1 where list_id='".$list_id."' and ordering='".$min."'");
	OCIExecute($upd,OCI_COMMIT_ON_SUCCESS);
}

echo "<table id=tbl_".$blog_id." border=0 bgcolor=gray cellspacing=1 cellpadding=2><tr>";
echo "<td bgcolor=#FFFFDD colspan=".$colspan."><a name=".$blog_id.">#".$blog_id."</a>";

	//кнопки редактирования
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=LI&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=LI&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=LI&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=LI&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."&colapsed=".$colapsed."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a><br>";
	//

if ($colapsed=='y') echo "<a href=javascript:show_hide('".$blog_id."')><font size=3><b>".OCIResult($q,"NAME")."</b></font></a><br>";
else echo "<font size=3><b>".OCIResult($q,"NAME")."</b></font><br>";
echo "<font color=#003366>".OCIResult($q,"HEAD_TEXT")."</font></td></tr>";

$q_phone=OCIParse($c,"select phone,decode(ext,null,null,'Доб. '||ext) ext,name from sc_forw_phone 
where fio_id=:fio_id and project_id='".$_SESSION['project_id'][$_SESSION['i']]."' order by ordering");

$q=OCIParse($c,"select * from sc_forw_fio where list_id='".$list_id."' and project_id='".$_SESSION['project_id'][$_SESSION['i']]."'".$order_by." ");
OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q) and $row_count<>'0') {
	echo "<tr>";
	if ($f==1) echo "<td valign=top bgcolor=#FFFFDD><font size=3><b>".OCIResult($q,"FIO")."</b></font></td>";
	if ($o==1) echo "<td valign=top bgcolor=#FFFFDD><font color=#003366><b>".OCIResult($q,"OTDEL")."</b></font></td>";
	if ($d==1) echo "<td valign=top bgcolor=#FFFFDD><font color=#003366><b>".OCIResult($q,"DOLJNOST")."</b></font></td>";
	if ($g==1) echo "<td valign=top bgcolor=#FFFFDD><font color=red><b>".OCIResult($q,"GRAFIK")."</b></font></td>";
	if ($co==1) echo "<td valign=top bgcolor=#FFFFDD>".OCIResult($q,"COMENT")."</td>";
	if ($e==1) echo "<td valign=top bgcolor=#FFFFDD><font color=blue><b>".OCIResult($q,"EMAIL")."</b></font></td>";
	echo "<td valign=top bgcolor=#FFFFDD>";
	$v_id=OCIResult($q,"ID");
	OCIBindByName($q_phone,":fio_id",$v_id);
	OCIExecute($q_phone,OCI_DEFAULT);
		echo "<font size=3>";
		while (OCIFetch($q_phone)) {
		echo "<nobr><i><a href=\"javascript:transfer('".OCIResult($q_phone,"PHONE")."')\"><b>".OCIResult($q_phone,"NAME")."</a></i> ".OCIResult($q_phone,"EXT")."</b></nobr><br>";
		}
	echo "</td>";
	echo "</tr>";
	$row_count--;
	}
echo "</table>";
if ($colapsed=='y') echo "<script>
for (i=1; i<document.all.tbl_".$blog_id.".rows.length; i++) {
	document.all.tbl_".$blog_id.".rows[i].style.display='none';
}
</script>";	
} //Функция отображения списка переадресации

?>
<iframe name=blank_frame style="display:none"></iframe>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
</body>
</html>