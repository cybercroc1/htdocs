<?php 
//header('X-UA-Compatible: IE=EmulateIE7');
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
$_SESSION['last_url']='edit_sc.php';
?>
<!DOCTYPE HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="billing.css" rel="stylesheet" type="text/css">
</head>
<?php if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['ch_sc']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
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
include("sc/sc_path.php");
echo "function open_local(filename) {
open('".str_replace('\\','\\\\',$net_path_to_folders).$_SESSION['project']['name']."\\\'+filename);
}";
?>
function clickactivehead(blog_id,row_num,row_id) {
	targettable=document.getElementById('dyntbl'+blog_id);
	targetrow=document.getElementById(row_id);
	if(targetrow.getAttribute('head_status')=="closed") open_stem(blog_id,row_num,targettable,targetrow.id);
	else if(targetrow.getAttribute('head_status')=="opened") close_stem(blog_id,row_num,targettable,targetrow.id);
	
}
function open_stem(blog_id,row_num,targettable,row_id) {
	start_open="n";
	stop_open_head="n";
	stop_open_content="n";
	targetrow=document.getElementById(row_id);
	first_head_lvl=parseInt(targetrow.getAttribute('active_head_lvl'));
	second_head_lvl=parseInt(targetrow.getAttribute('active_head_lvl'));
	targetrow.setAttribute('head_status','opened'); document.getElementById('img'+blog_id+'rownum'+targetrow.getAttribute('row_num')).src='minus.png'; //открываем ветку
	with(targettable) {
		for(r=targetrow.rowIndex; r<rows.length; r++) {
			
			if(start_open=="n" && parseInt(rows[r].getAttribute('row_num')) > parseInt(row_num)) {start_open="y";}
			if(start_open == "y" && rows[r].getAttribute('active_head_lvl') && parseInt(rows[r].getAttribute('active_head_lvl')) <= first_head_lvl) {stop_open_head="y"; stop_open_content="y";}
				
			if(start_open == "y" && stop_open_head != "y" && parseInt(rows[r].getAttribute('active_head_lvl')) > first_head_lvl && second_head_lvl == first_head_lvl) {second_head_lvl=parseInt(rows[r].getAttribute('active_head_lvl'));}
			if(start_open == "y" && stop_open_head != "y" && parseInt(rows[r].getAttribute('active_head_lvl')) >= first_head_lvl && parseInt(rows[r].getAttribute('active_head_lvl')) <= second_head_lvl) { //отображаем вложенные заголовки
				rows[r].style.display="";
				rows[r].setAttribute('head_status','closed');
				document.getElementById('img'+blog_id+'rownum'+rows[r].getAttribute('row_num')).src='plus.png';	
				stop_open_content="y";					
			}
			if(start_open == "y" && stop_open_content!="y" && !rows[r].getAttribute('active_head_lvl')) {
				rows[r].style.display="";
				
			}
			if(stop_open_content=="y" && stop_open_head=="y") break;			
		}
	}
}
function close_stem(blog_id,row_num,targettable,row_id) {
	start_close="n";
	stop_close="n";	
	targetrow=document.getElementById(row_id);
	first_head_lvl=parseInt(targetrow.getAttribute('active_head_lvl'));
	second_head_lvl=parseInt(targetrow.getAttribute('active_head_lvl'));
	targetrow.setAttribute('head_status','closed'); document.getElementById('img'+blog_id+'rownum'+targetrow.getAttribute('row_num')).src='plus.png'; //закрываем ветку	
	with(targettable) {
		for(r=targetrow.rowIndex; r<rows.length; r++) {		
			if(start_close=="n" && parseInt(rows[r].getAttribute('row_num')) > parseInt(row_num)) {start_close="y";}
			if(start_close=="y" && stop_close != "y" && rows[r].getAttribute('active_head_lvl') && parseInt(rows[r].getAttribute('active_head_lvl')) <= first_head_lvl) {stop_close="y";}
			if(start_close=="y" && stop_close != "y" && rows[r].getAttribute('active_head_lvl')) {
				rows[r].style.display="none";
				rows[r].setAttribute('head_status','closed');
				document.getElementById('img'+blog_id+'rownum'+rows[r].getAttribute('row_num')).src='plus.png';	
			}
			if(start_close=="y" && stop_close != "y" && !rows[r].getAttribute('active_head_lvl')) {
				rows[r].style.display="none";
			}
			if(stop_close=="y") break;		
		}
	}
}
function open_all(blog_id) {
	targettable=document.getElementById('dyntbl'+blog_id);
	with(targettable) {
		for(r=0; r<rows.length; r++) {
			if(rows[r].active_head_lvl != "") {
				rows[r].style.display="";
				rows[r].head_status='opened';
				document.getElementById('img'+blog_id+'rownum'+rows[r].row_num).src='minus.png';					
			}
			else {
				rows[r].style.display="";
			}
		}
	}	
}	
function close_all(blog_id,first_head_lvl) {
	targettable=document.getElementById('dyntbl'+blog_id);
	first_head_lvl=0;
	with(targettable) {
		for(r=0; r<rows.length; r++) {
			
			if(first_head_lvl==0 && rows[r].getAttribute('active_head_lvl')) first_head_lvl=rows[r].getAttribute('active_head_lvl');
			
			if(first_head_lvl>0) {
				if(rows[r].getAttribute('active_head_lvl')) {
					if(rows[r].getAttribute('active_head_lvl')<=first_head_lvl) {
				rows[r].style.display="";
				rows[r].setAttribute('head_status','closed');
				document.getElementById('img'+blog_id+'rownum'+rows[r].getAttribute('row_num')).src='plus.png';							
					}
					else {
				rows[r].style.display="none";
				rows[r].setAttribute('head_status','closed');
				document.getElementById('img'+blog_id+'rownum'+rows[r].getAttribute('row_num')).src='plus.png';	
					}
				
				}
				else {
					rows[r].style.display="none";
				}
			}
		}
	}
}

</script>
<body>
<a name=top></a>
<?php

extract($_REQUEST);

include("sc/sc_conn_string.php");
require_once "../../htdocs_local/sc/func_show_form.php";

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
	where project_id='".$_SESSION['project']['id']."'
	".$where_punkt_id."
	and ordering<'".$ordering."'
	and general='".$general."'
	and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
	if (OCIFetch($q) and OCIResult($q,"PERV_ORDERING")<>NULL) {
		$perv_ordering=OCIResult($q,"PERV_ORDERING");
		$upd=OCIParse($c,"update sc_body set ordering='".$ordering."'
		where project_id='".$_SESSION['project']['id']."'
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
	where project_id='".$_SESSION['project']['id']."'
	".$where_punkt_id."
	and ordering>'".$ordering."'
	and general='".$general."'
	and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
	if (OCIFetch($q) and OCIResult($q,"NEXT_ORDERING")<>NULL) {
		$next_ordering=OCIResult($q,"NEXT_ORDERING");
		$upd=OCIParse($c,"update sc_body set ordering='".$ordering."'
		where project_id='".$_SESSION['project']['id']."'
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
where b.project_id='".$_SESSION['project']['id']."' and b.general='y'
and b.shedule_id=s.id(+)
order by b.ordering");
OCIExecute($q, OCI_DEFAULT);
$TA=0;
while (OCIFetch($q)) {
	//Табличный блок
	if (OCIResult($q,"TYPE")=='TA') {
		show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	
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
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_text_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),OCIResult($q,"TXT_TAG_BEFORE"),OCIResult($q,"TXT_TAG_AFTER"),OCIResult($q,"TXT_ALIGN"),$tree_id);
	}
	//
	//Форма
	if (OCIResult($q,"TYPE")=='FO') {
	//show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	//show_form(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_form($c,OCIResult($q,"FORM_ID"),$_SESSION['project']['id'],'',$tree_id,OCIResult($q,"ID"),OCIResult($q,"HEAD"),OCIResult($q,"NEW_WINDOW"),OCIResult($q,"INVISIBLE"),OCIResult($q,"COLAPSED"),'y');
		if(OCIResult($q,"COLAPSED")=='y') {
			echo "<script>document.all.tbl_".OCIResult($q,"ID").".rows[1].style.display='none';</script>";
		}
	}
	//
	//Внешняя Форма
	if (OCIResult($q,"TYPE")=='FV') {
	//show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_outher_form(OCIResult($q,"HEAD"),OCIResult($q,"INJECT_ID"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	}
	//

	//HTML файл
	if (OCIResult($q,"TYPE")=='FI') {
	show_html_file(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
	}
	//
	//Список переадресации
	if (OCIResult($q,"TYPE")=='LI') {
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));	
	show_forw_list(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORW_LIST_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//
	//Форма исходящего звонка	
	if (OCIResult($q,"TYPE")=='OU') {
		show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));	
		
		if(OCIResult($q,"OUT_PREFIX")<>'') $out_prefix_tmp=OCIResult($q,"OUT_PREFIX");
		elseif ($_SESSION['project']['out_prefix']<>'') $out_prefix_tmp=$_SESSION['project']['out_prefix'];
		else $out_prefix_tmp='';	
		show_out_call_form($out_prefix_tmp,OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
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
where b.project_id='".$_SESSION['project']['id']."' and b.punkt_id".$where_punkt_id." and b.general='n'
and b.shedule_id=s.id(+)
order by b.ordering");
OCIExecute($q, OCI_DEFAULT);
$TA=0;
while (OCIFetch($q)) {
	//Табличный блок
	if (OCIResult($q,"TYPE")=='TA') {
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
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
	//show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_text_blog(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"BODY")->load(),OCIResult($q,"SHEDULE_NAME"),OCIResult($q,"TXT_TAG_BEFORE"),OCIResult($q,"TXT_TAG_AFTER"),OCIResult($q,"TXT_ALIGN"),$tree_id);
	}
	//
	//Форма
	if (OCIResult($q,"TYPE")=='FO') {
	//show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	//show_form(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_form($c,OCIResult($q,"FORM_ID"),$_SESSION['project']['id'],'',$tree_id,OCIResult($q,"ID"),OCIResult($q,"HEAD"),OCIResult($q,"NEW_WINDOW"),OCIResult($q,"INVISIBLE"),OCIResult($q,"COLAPSED"),'y');
	}
	//
	//Внешняя Форма
	if (OCIResult($q,"TYPE")=='FV') {
	//show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));	
	show_outher_form(OCIResult($q,"HEAD"),OCIResult($q,"INJECT_ID"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORM_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"),$c);
	}
	//	
	//HTML файл
	if (OCIResult($q,"TYPE")=='FI') {
show_html_file(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
	}
	//	
	//Список переадресации
	if (OCIResult($q,"TYPE")=='LI') {
	show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));	
	show_forw_list(OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"FORW_LIST_ID"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),$c);
	}
	//
	//Форма исходящего звонка	
	if (OCIResult($q,"TYPE")=='OU') {
		show_edit_buttons(OCIResult($q,"HEAD"),OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),OCIResult($q,"TYPE"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"SHEDULE_NAME"),$tree_id,OCIResult($q,"COLAPSED"),OCIResult($q,"NEW_WINDOW"));	
		
		if(OCIResult($q,"OUT_PREFIX")<>'') $out_prefix_tmp=OCIResult($q,"OUT_PREFIX");
		elseif ($_SESSION['project']['out_prefix']<>'') $out_prefix_tmp=$_SESSION['project']['out_prefix'];
		else $out_prefix_tmp='';	
		show_out_call_form($out_prefix_tmp,OCIResult($q,"INVISIBLE"),OCIResult($q,"ID"),$punkt_id,OCIResult($q,"ORDERING"),OCIResult($q,"GENERAL"),OCIResult($q,"HEAD"),OCIResult($q,"SHEDULE_NAME"),$tree_id);
	}
	//	
}
if ($TA==1) {echo "</table>"; $TA=0;}

//Функция отображения HTML файла
function show_html_file($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$shedule_name,$tree_id) {
	include("sc/sc_path.php");
	include("sc/sc_local_network.php");
	global $c;
	$call_id='';
	$project_id='';
	$cdpn='';
	$cgpn='';
	$agid='';
	$aon='';
	$thrid='';
	$caller_name='';
	$call_direction='';
	$fext=strtolower(substr($head,-3,3)); //Расширение файла
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
	if ($from_local_addr=='y') echo "<a href='file://".$net_path_to_folders.$_SESSION['project']['name']."\\' target='_blank'>";
	echo $head;
	if ($from_local_addr=='y') echo "</a>";
	echo "</font>)";
	
	if ($fext=='htm' or $fext=='html') { //html прогрняем через парсер 
	$fp=fopen($path_to_folders.$_SESSION['project']['name']."\\".$head,'r');
		echo parse_func(fread($fp,filesize($path_to_folders.$_SESSION['project']['name']."\\".$head)));
	fclose($fp);
	}
	else if ($fext=='php') {
		$fp=fopen($path_to_folders.$_SESSION['project']['name']."\\".$head,'r');
		echo htmlentities(fread($fp,filesize($path_to_folders.$_SESSION['project']['name']."\\".$head)));
	}
	else { //остальные инклудим
		include($path_to_folders.$_SESSION['project']['name']."\\".$head);
	}
	echo "<br>";
}//

function parse_func($text) {
	$rgx="/(
	(t(<[^>]*>)*r(<[^>]*>)*a(<[^>]*>)*n(<[^>]*>)*s)|
	(d(<[^>]*>)*t(<[^>]*>)*m(<[^>]*>)*f)|
	(s(<[^>]*>)*h(<[^>]*>)*o(<[^>]*>)*w)
	)
	(<[^>]*>)*\[[^\]]*\]/ismx";
	return preg_replace_callback($rgx,'parse_conv',$text);
}
function parse_conv($text) {
	preg_match_all("/<[^>]*>/ismx",$text[0],$fucking_tags); //копируем все тэги
	$striped_text=strip_tags($text[0]);
	//разделяем найденное выражение на три части: название функции, переметр1 (номер), параметр2 (отображаемое имя)
	$rgx="/([^\[]*)(?:\[)([^\,]*)(?:(\,)(.*))?(?:\])/ismx";	
	preg_match($rgx,$striped_text,$matches);
	//название функции
	if(isset($matches[1])) {
		$func_name=strtolower($matches[1]); //название функции
	}
	else return $text[0];

	if($func_name=='trans' or $func_name=='dtmf' or $func_name=='show') {
		if($func_name=='trans' or $func_name=='transfer') $js_func_name='transfer';
		if($func_name=='dtmf') 							  $js_func_name='senddtmf';
		if($func_name=='show') 							  $js_func_name='show';
		
		if(isset($matches[2]) and $matches[2]<>'') {
			$phone=$matches[2]; //номер
		}
		else return $text[0];
		
		if(isset($matches[4]) and $matches[4]<>'') { //если есть второй парамтр функции - имя ссылки
			$display_phone=$matches[4];
		}
		else {
			$display_phone=$phone;
		}
		if($js_func_name=='transfer') $phone=preg_replace('/[^0-9^w^q^z]/','',$phone); //оставляем только цифры и символы набора
		if($js_func_name=='senddtmf') $phone=preg_replace('/[^a-d^A-D^0-9^\*^\#]/','',$phone); //оставляем только DTMF-символы
		if($js_func_name=='show') $phone=preg_replace('/[^0-9]/','',$phone); //оставляем только цифры
		
		$href="<a href=\"javascript:alert('".$js_func_name."(\'".$phone."\')')\" title='".$phone."'".($js_func_name=='senddtmf'?" style=color:green":"").">".strip_tags($display_phone)."</a>";
		//$href="<a href=\"javascript:".$js_func_name."('".$phone."')\" title='".$phone."'".($js_func_name=='senddtmf'?" style=color:green":"").">".$display_phone."</a>";
		//переносим горёбаные тэги в начало выражения
		return implode('',$fucking_tags[0]).$href;
	}
	else return $text[0];
}

//Функция отображения табличного блока
function show_table_blog($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$body,$shedule_name,$tree_id,$faq) {
	echo "<tr>
	<td bgcolor=white valign=top><nowrap>";
	/*
	echo "<a name=".$blog_id.">#".$blog_id."</a>";
	
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
	*/
	if ($faq=='y') {echo "<input type=checkbox disabled>";}
	echo nl2br(parse_func($head))."</td>
	<td bgcolor=white valign=top>".nl2br(parse_func($body))."</td>
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

$q=OCIParse($c,"select name,style,attrib,project_id from sc_dynamic_table
where id='".$table_id."'");
OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		echo "(таблица".(OCIResult($q,"PROJECT_ID")=='0'?' (Общая))':'').": <font color=red>".OCIResult($q,"NAME")."</font>)";
		echo '<table id="dyntbl'.$blog_id.'"'.OCIResult($q,"ATTRIB").' style="'.OCIResult($q,"STYLE").'">';
	}
$q_row=OCIParse($c,"select row_num,attrib,style,height,active_head_lvl from sc_dynamic_table_rows
where table_id='".$table_id."' order by row_num");
$q_cell=OCIParse($c,"select cell_num,attrib,style,nvl(html,'') html,faq_id,phones,width,height from sc_dynamic_table_cells
where table_id='".$table_id."' and row_num=:row_num and cell_num>0 and display is null
order by cell_num");
OCIExecute($q_row,OCI_DEFAULT);
	$first_active_head_lvl="";
	while(OCIFetch($q_row)) {
		$row_num=OCIResult($q_row,"ROW_NUM");
		
		$active_head_lvl=OCIResult($q_row,"ACTIVE_HEAD_LVL");
		if($active_head_lvl<>"" and $first_active_head_lvl=='') $first_active_head_lvl=$active_head_lvl;

		$row_style=OCIResult($q_row,"STYLE");
		
		if($active_head_lvl<>"") $row_style=$row_style."cursor:pointer;";
		
		if($row_num>0) echo '<tr id="dyntbl'.$blog_id.'rownum'.$row_num.'" row_num="'.$row_num.'"'.OCIResult($q_row,"ATTRIB")
		.($active_head_lvl<>''?' active_head_lvl="'.$active_head_lvl.'"':"")		
		.'" head_status="opened" style="height:'.OCIResult($q_row,"HEIGHT").';'
		.$row_style.'"'.($active_head_lvl<>""?" onclick=clickactivehead('".$blog_id."','".$row_num."','dyntbl".$blog_id."rownum".$row_num."')":"").'>';	
		
		OCIBindByName($q_cell,":row_num",$row_num);
		OCIExecute($q_cell,OCI_DEFAULT);
		$col_num=0;
		while(OCIFetch($q_cell)) {
				$col_num++;
				if($row_num==0) {
					echo '<col width="'.OCIResult($q_cell,"WIDTH").'" style="width:'.OCIResult($q_cell,"WIDTH").';">';
				}
				else {
					echo '<td'.OCIResult($q_cell,"ATTRIB").' style="'.OCIResult($q_cell,"STYLE").'">';
					if($col_num==1 and $active_head_lvl<>"") {
						echo "<img id='img".$blog_id."rownum".$row_num."' src=minus.png align=left></img>";
					}
					//echo $row_num;
					if(OCIResult($q_cell,"HTML")<>'') {
					
						if(OCIResult($q_cell,"FAQ_ID")<>'') {
							echo "<input type=checkbox>";
						}
						if(OCIResult($q_cell,"PHONES")=='y') {
							echo phone_href(OCIResult($q_cell,"HTML")->load());
						}
						else echo parse_func(OCIResult($q_cell,"HTML")->load());
					}
				echo '</td>';
				}
			}
		if($row_num>0) echo '</tr>';	
	}
echo "</table>";
echo "<script>close_all('".$blog_id."','".$first_active_head_lvl."');</script>";
}//

//функция установки ссылок на найденные телефоны
function phone_href($text) {
//добавочные через решетку, через запятую
$rgx_new="/(\[[^\[^\]]+\])
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
      )
      (\d([ ()_{}\[\]\-]|(&nbsp;))*){4}\d{1}
    )
(([ ()_{}\[\]\-]|(&nbsp;))*\#([\#\d\*a-dA-D]|[ ()_{}\[\]\-]|(&nbsp;))*(\,([ ()_{}\[\]\-]|(&nbsp;))*\#([\#\d\*a-dA-D]|[ ()_{}\[\]\-]|(&nbsp;))+)*)?
((?=\D)|(?=$)))
/ix";
	return preg_replace_callback($rgx_new,'conv',$text);
}

//Функция конвертации телефонных номеров
function conv($t) {
	global $phone_href_type;
	if(strpos($t[0],"#")) {
		$phone_src=substr($t[0],0,strpos($t[0],"#"));
		$ext_part=substr($t[0],strpos($t[0],"#"));
		$exts_src=explode(',',$ext_part);
		foreach($exts_src as $key=>$val) {
			$exts_src[$key]=substr($exts_src[$key],strpos($exts_src[$key],"#")+1); //отрезаем первую решетку
			$exts[$key]=preg_replace('/[^a-d^A-D^0-9^\*^\#]/','',$exts_src[$key]); //оставляем только DTMF-символы
		}
	}
	else {
		$phone_src=$t[0];
		$ext_part='';
	}
	$phone=$phone_src;

	$phone=preg_replace('/[^0-9^\]^\[]/','',$phone); //оставляем только квадратные скобки и цифры
	if(preg_match('/\[.+\]/',$phone_src.$ext_part)) { //если номер в квадратных скобках
		$phone=str_replace(array('[',']'),'',$phone); //удаляем скобки
		$ext_part=str_replace(array('[',']'),'',$ext_part); //удаляем скобки
		
		$phone_src=trim($phone_src,'][');
		
		$href="[";
		$href.="<a href=\"javascript:alert('".$phone."')\" title=".$phone.">".$phone_src."</a>"; //ссылка на телефон
		
		if(isset($exts_src)) {
			foreach($exts_src as $key=>$val) {
				$exts_src[$key]=trim($exts_src[$key],'][');
				$exts_href[]="<a href=\"javascript:alert('".$exts[$key]."')\" title='".$exts[$key]."' style=color:green>".$exts_src[$key]."</a>";
			}
			$href.="доб:".implode(', ',$exts_href);
		}
		$href.="]";
		return $href; //возвращаем ссылку		
	}
	elseif(strlen($phone)>=4 and strlen($phone)<=6) {}
	elseif(substr($phone,0,1)=='7' and strlen($phone)=='11') $phone="8".substr($phone,1); 
	elseif(substr($phone,0,4)=="8107") $phone="8".substr($phone,4);
	elseif(strlen($phone)==10) $phone="8".$phone;
	elseif(strlen($phone)>=11 and substr($phone,0,1)<>"8" and substr($phone,0,1)<>"7") $phone="810".$phone;
	if((strlen($phone)==11 or strlen($phone)==10) and substr($phone,-10,3)=='095') $phone='8495'.substr($phone,-7);
	if(preg_match("/^((\d{4,6})|([1-79][\d]{6})|(8(([02-9]\d)|(1[1-9]))\d{8})|(810[1-68-9]\d{10,14}))$/",$phone)) {
		$href="<a href=\"javascript:alert('".$phone."')\" title=".$phone.">".$phone_src."</a>";

		if(isset($exts_src)) {
			foreach($exts_src as $key=>$val) {
				$exts_src[$key]=trim($exts_src[$key],'][');
				$exts_href[]="<a href=\"javascript:alert('".$exts[$key]."')\" title='".$exts[$key]."' style=color:green>".$exts_src[$key]."</a>";
			}
			$href.="доб: ".implode(', ',$exts_href);
		}

		return $href;
	}	
	else return "<font color=red title='Номер ".$phone." не пригоден для набора'>".$t[0]."</font>";
} 
//

/*//Функция конвертации телефонных номеров
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
//*/
//Функция отображения текстового блока
function show_text_blog($invisible,$blog_id,$punkt_id,$ordering,$general,$head,$body,$shedule_name,$txt_tag_before,$txt_tag_after,$txt_align,$tree_id) {
	/*
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
	*/
	echo "<div".$txt_align.">".$txt_tag_before.nl2br(parse_func($body)).$txt_tag_after."</div>";
	//echo "<br>";
}//

//Функция отображения формы для исходящего звонка
function show_out_call_form($out_prefix,$invisible,$blog_id,$punkt_id,$ordering,$general,$head,$shedule_name,$tree_id) {

	/*
	echo  "<a name=".$blog_id.">#".$blog_id."</a>";
	
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=OU&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=OU&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=OU&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=OU&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a>";	
	*/
	echo "<table id=tbl_".$blog_id." border=0 bgcolor=gray cellspacing=1 cellpadding=2>";
	echo "<a name=".$blog_id."></a>";
	echo "<tr><td bgcolor='#EEFFEE'>";
	echo "<FONT size=3 color=green><B>Исходящий звонок</b> (префикс: ".$out_prefix.")<br>";
	echo "<b>Номер: </b></font><input type=text name='out_phone_number_".$blog_id."'></input>
	<input type=button value='Набрать номер' disabled>";
	echo "</FONT></TD></TR></TABLE>";
}
/*
//Функция отображения формы
function show_form($head,$invisible,$blog_id,$punkt_id,$ordering,$general,$form_id,$shedule_name,$tree_id,$colapsed,$new_window,$c) {

	$q=OCIParse($c,"select name,project_id from sc_forms where id='".$form_id."'");
	OCIExecute($q,OCI_DEFAULT);	
	OCIFetch($q);
	$form_name=OCIResult($q,"NAME");

	if(OCIResult($q,"PROJECT_ID")==0) $color='#FFEEEE';
	else if(OCIResult($q,"PROJECT_ID")==$_SESSION['project']['id']) $color='#EEFFEE';
	else $color='';	

	echo "<table style=form_tbl id=tbl_".$blog_id." border=0 bgcolor=gray cellspacing=1 cellpadding=2>";
	//echo "<form name=form_".$form_id."_".$blog_id." action=send.php method=post target=blank_frame>";
	echo "<tr><td bgcolor='".$color."'>";	
	
	//кнопки редактирования
	echo  "<a name=".$blog_id.">#".$blog_id."</a>  ".(OCIResult($q,"PROJECT_ID")=='0'?' (Общая)':'');
	
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
	echo "<a><font size=3><b>".($head==''?$form_name:$head)."</b>".($head!=''?"(".$form_name.")":"")."</a><font color=red> (откроется в новом окне)</font></font>";
	echo "</tr></td>";
	echo "</form>";
	echo "</table>";
	return;
	}	
	elseif ($colapsed=='y') echo "<a href=javascript:show_hide('".$blog_id."')><font size=3><b>".($head==''?$form_name:$head)."</b>".($head!=''?"(".$form_name.")":"")."</b></font></a>";
	else echo "<font size=3><b>".($head==''?$form_name:$head)."</b>".($head!=''?"(".$form_name.")":"")."</b></font>";
	//
	
$q_obj=OCIParse($c,"select * from sc_form_object
where form_id='".$form_id."' and type_id not in ('PU','PA','CP','HI')
order by ordering");
OCIExecute($q_obj,OCI_DEFAULT);

echo "<input type=hidden name=project_id value='".$_SESSION['project']['id']."'>";
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

/*
echo "function form_".$form_id."_".$blog_id.".send.onclick() {
form_".$form_id."_".$blog_id.".send.disabled=true;
form_".$form_id."_".$blog_id.".submit();
form_".$form_id."_".$blog_id.".send.value='ОТПРАВЛЕНА';
}";
*/
/*
echo "</script>";
} //Функция отображения формы
*/
function show_edit_buttons($head,$invisible,$blog_id,$blog_type,$punkt_id,$ordering,$general,$shedule_name,$tree_id,$colapsed,$new_window) {
	echo  "<a name=".$blog_id.">#".$blog_id."</a>  ";
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=".$blog_type."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=".$blog_type."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=".$blog_type."&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=".$blog_type."&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."&colapsed=".$colapsed."&new_window=".$new_window."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a><br>";	
}

//Функция отображения внешней формы
function show_outher_form($head,$inject_id,$invisible,$blog_id,$punkt_id,$ordering,$general,$form_id,$shedule_name,$tree_id,$colapsed,$new_window,$c) {

	$q=OCIParse($c,"select name,project_id,inj_code,bgcolor from SC_INJECTS where id='".$inject_id."'");
	OCIExecute($q,OCI_DEFAULT);	
	if(OCIFetch($q)) {
		$form_name=OCIResult($q,"NAME");
		if(OCIResult($q,"INJ_CODE")<>'') $inj_code=OCIResult($q,"INJ_CODE")->load();
		$bgcolor=OCIResult($q,"BGCOLOR");
	} 
	else {$form_name='';$inj_code=''; $bgcolor='';}

	echo "<table style=form_tbl id=tbl_".$blog_id." border=0 bgcolor=gray cellspacing=1 cellpadding=2>
	<tr><td bgcolor='".$bgcolor."'>";	
	echo  "<a name=".$blog_id.">#".$blog_id."</a>  ".(OCIResult($q,"PROJECT_ID")=='0'?' (Общая)':'');
	
	//кнопки редактирования
	/*
	if ($invisible=='1') {
	echo " <a href=\"body.php?visible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=invisible.gif title=\"Отобразить\" border=0></a>";
	}
	else {
	echo " <a href=\"body.php?invisible=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img src=visible.gif title=\"Скрыть\" border=0></a>";
	if ($shedule_name<>'') echo "(<font color=red>".$shedule_name."</font>)";
	}
	
	echo "<a href=\"body.php?up=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FV&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=up.gif></a>
	<a href=\"body.php?down=1&blog_id=".$blog_id."&ordering=".$ordering."&punkt_id=".$punkt_id."&blog_type=FV&general=".$general."&tree_id=".$tree_id."#".$blog_id."\"><img border=0 src=down.gif></a>
	<a href=\"edit_body.php?add_blog=1&blog_type=FO&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."\"><img src=add_leaf.gif title=\"Добавить блок\" border=0></a>
	<a href=\"edit_body.php?edit_blog=1&blog_id=".$blog_id."&blog_type=FV&ordering=".$ordering."&punkt_id=".$punkt_id."&general=".$general."&tree_id=".$tree_id."&colapsed=".$colapsed."&new_window=".$new_window."\"><img src=edit.gif title=\"Редактировать\" border=0></a>
	<a href=\"edit_body.php?del_blog=1&blog_id=".$blog_id."&punkt_id=".$punkt_id."&tree_id=".$tree_id."\"><img src=del.gif title=\"Удалить\" border=0></a><br>";
	*/
	echo "<a href=javascript:show_hide('".$blog_id."')><font size=3><b>".($head==''?$form_name:$head)."</b>".($head!=''?" (".$form_name.")":"")."</a><font color=black> (внешний модуль)</font></font>";

	echo "</tr></td>
	<tr><td bgcolor='".$bgcolor."'>";
	echo nl2br(htmlentities($inj_code));
	echo "</td></tr>
	</table>";
	echo "<script>";
	echo "document.all.tbl_".$blog_id.".rows[1].style.display='none';";
	echo "</script>";
}

//Функция отображения списка переадресации
function show_forw_list($invisible,$blog_id,$punkt_id,$ordering,$general,$list_id,$shedule_name,$tree_id,$colapsed,$c) {

$q=OCIParse($c,"select * from sc_forw_list where id=".$list_id." and project_id='".$_SESSION['project']['id']."'");
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
echo "<td bgcolor=#FFFFDD colspan=".$colspan.">";
/*
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
*/
if ($colapsed=='y') echo "<a href=javascript:show_hide('".$blog_id."')><font size=3><b>".OCIResult($q,"NAME")."</b></font></a><br>";
else echo "<font size=3><b>".OCIResult($q,"NAME")."</b></font><br>";
echo "<font color=#003366>".OCIResult($q,"HEAD_TEXT")."</font></td></tr>";

$q_phone=OCIParse($c,"select phone,decode(ext,null,null,'Доб. '||ext) ext,name from sc_forw_phone 
where fio_id=:fio_id and project_id='".$_SESSION['project']['id']."' order by ordering");

$q=OCIParse($c,"select * from sc_forw_fio where list_id='".$list_id."' and project_id='".$_SESSION['project']['id']."'".$order_by." ");
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