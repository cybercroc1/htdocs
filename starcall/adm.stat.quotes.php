<?php 
include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_stat']=='') {echo "<font color=red>Access DENY!</font>"; exit();}
$project_id=$_SESSION['adm']['project']['id'];

include("starcall/conn_string.cfg.php");
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php 

//НАЧАЛО СТРАНИЦЫ

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr class=header_tr><td>";

echo "<a href='adm.stat.status.php' target='admBottomFrame'>Статистика по статусам</a> | ";
echo "<font size=4><a href='adm.stat.quotes.php' target='admBottomFrame'>Статистика по квотам</a></font> | ";
echo "Справка | ";
echo "<hr>";

if($_SESSION['adm']['project']['src_quote_broken']<>'') {
	echo "<font color=red>Изменены квоты по исходным полям! Статистика недоступна.</font> ";
	$err='y';
}
else if($_SESSION['adm']['project']['qst_quote_broken']<>'') {
	echo "<font color=red>Изменены квоты по вопросам! Статистика недоступна.</font> ";
	$err='y';
}
else if($_SESSION['adm']['project']['qst_stat_broken']<>'') {
	echo "<font color=red>Нарушена статистика по вопросам! Статистика недоступна.</font> ";
	$err='y';
}
if(isset($err)) {
//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";
	//Хедер-футер. КОНЕЦ
	echo "</td></tr></table>";
	exit();
}
//

echo "<form name=frm_select method=post>";
$src_quoted_fields=array();
$src_idx_fields=array();
//список исходных полей
$q=OCIParse($c,"select id,text_name,t.quoted,t.idx from STC_FIELDS t
where project_id=".$project_id." and t.src_type_id=1 and t.deleted is null and (t.quoted is not null or t.idx is not null)
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	if(OCIResult($q,"QUOTED")<>'') $src_quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
	if(OCIResult($q,"IDX")<>'') $src_idx_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}

$quest_fields=array();
//список квотируемых вопросов
$q=OCIParse($c,"select o.quote_num,f.text_name from STC_OBJECTS o, Stc_Fields f
where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
and f.deleted is null and f.id=o.field_id
order by o.quote_num");
OCIExecute($q); $i=0; while(OCIFetch($q)) {$i++;
	$quest_fields[OCIResult($q,"QUOTE_NUM")]=OCIResult($q,"TEXT_NAME");
	$old_qst_quote_id[$i]='';
}

if(count($src_quoted_fields)==0 and count($quest_fields)==0) {echo "<font size=3><b>Нет квотируемых полей и вопросов</b></font>"; 
//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";	
	exit();
}
if(!isset($level)) $level='null';
echo "Уровень квоты: <select name=level onchange=frm_select.submit()><option value=null>Выберите уровень</option>";
if(count($src_quoted_fields)>0) echo "<option value=0".($level=='0'?' selected':NULL).">Исходные поля</option>";
if(count($quest_fields)>0) {
	foreach($quest_fields as $lvl => $questname) {
		echo "<option value=".$lvl.($level==$lvl?' selected':NULL).">Уровень ".$lvl."</option>";
	}
}
if(count($src_quoted_fields)+count($src_idx_fields>0)) echo "<option value='src'".($level=='src'?' selected':NULL).">Независимые по исходным</option>";
if(count($quest_fields)>0) echo "<option value='qst'".($level=='qst'?' selected':NULL).">Независимые по вопросам</option>";
echo "</select>";

echo "</form><hr>";

if(!isset($level) or $level=='null') {

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";
	exit();
}

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";

if($level<>'src' and $level<>'qst') {
//ЗАВИСИМЫЕ КВОТЫ
	$sql1="select * from" ; $sql2=""; $sql3=""; $sql4="order by ";
	//собирает запрос
	//если есть исходные поля
	$lvl=0;
	if(count($src_quoted_fields)>0) {
		$i=0; foreach($src_quoted_fields as $field_id => $field_name) {$i++;
			if($i==1) {
				$sql2.="(select ssq.id qid0,ssq.src_quote quote0,
				ssq.STAT_new new0,
				ssq.STAT_end_norm norm0, 
				decode(ssq.src_quote,0,null,null,null,round(ssq.STAT_end_norm/ssq.src_quote*100,2)) proc0,
				ssq.STAT_inwork inwork0,
				ssq.STAT_end_error end_error0,
				ssq.STAT_end_false end_false0,
				ssq.STAT_end_nedoz end_nedoz0,
				ssq.STAT_end_otkaz end_otkaz0,
				ssq.STAT_end_quote end_quote0,
				ssq.STAT_nedoz nedoz0,
				ssq.STAT_perez perez0
				from stc_src_quotes ssq where ssq.project_id=".$project_id.") ssq ";
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".src_qid=ssq.qid0 ";
			}
			$sql2.=", ";
			$sql2.="(select sqi.quote_id src_qid, si.value val0_".$i." from  stc_src_indexes si, stc_src_quote_indexes sqi
where si.project_id=".$project_id." and si.field_id=".$field_id." and sqi.index_id=si.id) s".$i." ";
			if($i>1) {
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".src_qid=s".($i-1).".src_qid ";
				$sql4.=", ";
			}
			$sql4.="s".$i.".val0_".$i." ";
			
		}
		if($level>0) {
			$sql2.=", ";
			$sql4.=", ";
		} 
	}
	//квоты по вопросам, если есть хотя бы один квотируемый вопрос
	if(count($quest_fields)>0) {
		for($lvl=1; $lvl<=$level; $lvl++) {
			if($lvl>1) {
				$sql2.=", ";
				$sql4.=", ";				
			}
			if($lvl==1) {
				$sql2.="(select qq.src_quote_id src_qid, qq.id qid".$lvl.",i.value val".$lvl.", qq.qst_quote quote".$lvl.", qq.qst_norm norm".$lvl." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$lvl." and qq.index_id=i.id) q".$lvl." ";

				if(count($src_quoted_fields)>0) {
					if($sql3=='') $sql3='where '; else $sql3.='and ';
					$sql3.="q".$lvl.".src_qid=ssq.qid0 ";
				}
			}
			else {
				$sql2.="(select qq.parent_id,qq.id qid".$lvl.",i.value val".$lvl.", qq.qst_quote quote".$lvl.", qq.qst_norm norm".$lvl." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$lvl." and qq.index_id=i.id) q".$lvl." ";
				if($sql3=='') $sql3='where '; else $sql3.='and ';
				$sql3.="q".$lvl.".parent_id=q".($lvl-1).".qid".($lvl-1)." ";
			}
			$sql4.="q".$lvl.".val".$lvl." ";	
		}
	}
	echo "<table class=white_table>";
	
	$q=OCIParse($c,$sql1.$sql2.$sql3.$sql4);
	OCIExecute($q);
	$rownum=0;
	$old_src_quote_id='';
	$i=0; while(OCIFetch($q)) {$i++; 
		if($i==1) {
			echo "<tr><td></td>";
			if(count($src_quoted_fields)>0) echo "<th colspan=".($level==0?count($src_quoted_fields)+11:count($src_quoted_fields)).">И С Х О Д Н Ы Е  П О Л Я</th>";
			
			for($j=1; $j<=$level; $j++) {
				echo "<th colspan=".($j==$level?'3':'1').">У Р О В Е Н Ь ".$j."</th>";
			}
			
			echo "</tr>";
					
			echo "<tr><td>№</td>";
			if(count($src_quoted_fields)>0) {
				$j=0; foreach($src_quoted_fields as $field_id => $field_name) {$j++;
					echo "<th>".$field_name."</th>";
				}
				if($level==0) {
					echo "<th>Квота</th>";
					echo "<th>Новых</th>";
					echo "<th>Выполнено</th>";
					echo "<th>Нецелевых</th>";
					echo "<th>Глух.Недоз.</th>";
					echo "<th>Прев.Квота.</th>";
					echo "<th>Отказ</th>";
					echo "<th>Ошибка</th>";
					echo "<th>В работе</th>";
					echo "<th>Недозвонов</th>";
					echo "<th>Перезвонов</th>";
				}
			}
			if(count($quest_fields)>0) {
				$j=0; foreach($quest_fields as $num => $quest_name) {$j++;
					if($j>$level) break;
					echo "<th>".$quest_name."</th>";
					if($j==$level) {
						echo "<th>Квота</th>";
						echo "<th>Выполнено</th>";
					}
				}
			}
			echo "</tr>";
		}
		$rownum++;
		echo "<tr><td>".$rownum."</td>";
		if(count($src_quoted_fields)>0) {
			$j=0; foreach($src_quoted_fields as $field_id => $field_name) {$j++;
				echo "<td>".OCIResult($q,"VAL0_".$j)."</th>";
			}
			if($level==0) { //не показываем квоту для не последнего уровня
				echo "<td>";
				if(OCIResult($q,"QID0")<>$old_src_quote_id){
					$old_src_quote_id=OCIResult($q,"QID0");
					echo OCIResult($q,"QUOTE0");
				}
				echo "</td>";
				echo "<td>".OCIResult($q,"NEW0")."</td>";
				echo "<td>".OCIResult($q,"NORM0")."</td>";
				echo "<td>".OCIResult($q,"END_FALSE0")."</td>";
				echo "<td>".OCIResult($q,"END_NEDOZ0")."</td>";
				echo "<td>".OCIResult($q,"END_QUOTE0")."</td>";
				echo "<td>".OCIResult($q,"END_OTKAZ0")."</td>";
				echo "<td>".OCIResult($q,"END_ERROR0")."</td>";				
				echo "<td>".OCIResult($q,"INWORK0")."</td>";
				echo "<td>".OCIResult($q,"NEDOZ0")."</td>";
				echo "<td>".OCIResult($q,"PEREZ0")."</td>";
			}
		}
		if(count($quest_fields)>0) {
			$j=0; foreach($quest_fields as $quest_lvl => $field_name) {$j++;
				if($j>$level) break;
				echo "<td>".OCIResult($q,"VAL".$quest_lvl)."</td>";
				if($j==$level) { //не показываем квоту для не последнего уровня
					echo "<td>";
					if(OCIResult($q,"QID".$quest_lvl)<>$old_qst_quote_id[$quest_lvl]) {
						$old_qst_quote_id[$quest_lvl]=OCIResult($q,"QID".$quest_lvl);
						echo OCIResult($q,"QUOTE".$quest_lvl);
					}
					echo "</td>";
					echo "<td>".OCIResult($q,"NORM".$quest_lvl)."</td>";
				}
			}
		}
		echo "</tr>";
	}
	echo "</table>";
}
//независимые по исходным
if($level=='src') {
	$q=OCIParse($c,"select i.id idx_id,f.text_name,i.value, i.src_idx_quote, 
	i.STAT_new, 
	i.STAT_end_norm,
	i.STAT_inwork,
	i.STAT_end_error,
	i.STAT_end_false,
	i.STAT_end_nedoz,
	i.STAT_end_otkaz,
	i.STAT_end_quote,
	i.STAT_nedoz,
	i.STAT_perez
	from STC_FIELDS f, STC_SRC_INDEXES i
	where f.project_id=".$project_id." and f.deleted is null and f.src_type_id=1 and (f.quoted is not null or f.idx is not null)
	and i.project_id=".$project_id." and i.field_id=f.id
	order by f.text_name,i.value");
	OCIExecute($q);
	$i=0; while(OCIFetch($q)) {$i++;
		if($i==1) {
			echo "<table class=white_table>";
			echo "<tr>";
			echo "<th>Название поля</th>";
			echo "<th>Значение</th>";	
			echo "<th>Квота</th>";
			echo "<th>Новых</th>";
			echo "<th>Выполнено</th>";
			echo "<th>Нецелевых</th>";
			echo "<th>Глух.Недоз.</th>";
			echo "<th>Прев.Квота.</th>";
			echo "<th>Отказ</th>";
			echo "<th>Ошибка</th>";
			echo "<th>В работе</th>";
			echo "<th>Недозвонов</th>";
			echo "<th>Перезвонов</th>";			
			echo "</tr>";
		}
		echo "<tr>";
		echo "<td><b>".OCIResult($q,"TEXT_NAME")."</b></td>";
		echo "<td>".OCIResult($q,"VALUE")."</td>";
		echo "<td>".OCIResult($q,"SRC_IDX_QUOTE")."</td>";
		echo "<td>".OCIResult($q,"STAT_NEW")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_NORM")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_FALSE")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_NEDOZ")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_QUOTE")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_OTKAZ")."</td>";
		echo "<td>".OCIResult($q,"STAT_END_ERROR")."</td>";
		echo "<td>".OCIResult($q,"STAT_INWORK")."</td>";
		echo "<td>".OCIResult($q,"STAT_NEDOZ")."</td>";
		echo "<td>".OCIResult($q,"STAT_PEREZ")."</td>";
		echo "</tr>";	
	}
	if ($i==0) {echo "<font size=3><b>Нет квотируемых или индексируемых исходных полей</b></font>"; exit();}
	echo "</table>";
}
//независимые по вопросам
if($level=='qst') {
	$q=OCIParse($c,"select qi.id idx_id,f.text_name,qi.value, qi.qst_idx_quote, qi.qst_idx_norm 
from stc_objects o, stc_fields f, stc_qst_indexes qi 
where o.project_id=".$project_id." and o.deleted is null
and f.project_id=".$project_id." and f.id=o.field_id
and qi.object_id=o.id
order by f.text_name,qi.value");
	OCIExecute($q);
	$i=0; while(OCIFetch($q)) {$i++;
		if($i==1) {
			echo "<table class=white_table>";
			echo "<tr>";
			echo "<th>Вопрос</th>";
			echo "<th>Значение</th>";	
			echo "<th>Квота</th>";
			echo "<th>Выполнено</th>";
			echo "</tr>";
		}
		echo "<tr>";
		echo "<td><b>".OCIResult($q,"TEXT_NAME")."</b></td>";
		echo "<td>".OCIResult($q,"VALUE")."</td>";
		echo "<td>".OCIResult($q,"QST_IDX_QUOTE")."</td>";
		echo "<td>".OCIResult($q,"QST_IDX_NORM")."</td>";
		echo "</tr>";	
	}
	
	if ($i==0) {echo "<font size=3><b>Нет квотируемых вопросов или в вопросах не ответов</b></font>"; exit();}
	echo "</table>";	
}
//
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";
?>
</body>
</html>

