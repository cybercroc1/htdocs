<?php 
include("starcall/session.cfg.php"); 

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') {echo "<font color=red>Access DENY!</font>"; exit();}

include("starcall/conn_string.cfg.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<script>
function sel_quote(id) {
	frm_sel_quote.quote_id.value=id;
	frm_sel_quote.submit();
}
</script>
</head>
<body>
<?php 

extract($_REQUEST);

$src_quoted_fields=array();
$src_idx_fields=array();
//список исходных полей
$q=OCIParse($c,"select id,text_name,t.quoted,t.idx from STC_FIELDS t
where project_id=".$_SESSION['survey']['project']['id']." and t.src_type_id=1 and t.deleted is null and (t.quoted is not null or t.idx is not null)
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	if(OCIResult($q,"QUOTED")<>'') $src_quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
	if(OCIResult($q,"IDX")<>'') $src_idx_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}

if(count($src_quoted_fields)==0) {	
	echo "Нет квотируемых полей";
	exit();
}

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr class=header_tr><td>";

echo "<form name=frm_quotes method=post>";

if(!isset($order_by) and !isset($_SESSION['survey']['src_quotes']['order_by'])) $_SESSION['survey']['src_quotes']['order_by']='order by null';
if(isset($order_by)) $_SESSION['survey']['src_quotes']['order_by']=$order_by;
$order_by=$_SESSION['survey']['src_quotes']['order_by'];

if(!isset($show_type) and !isset($_SESSION['survey']['src_quotes']['show_type'])) $_SESSION['survey']['src_quotes']['show_type']='active_only';
if(isset($show_type)) $_SESSION['survey']['src_quotes']['show_type']=$show_type;
$show_type=$_SESSION['survey']['src_quotes']['show_type'];

if(!isset($_SESSION['survey']['call']['quote_id'])) $_SESSION['survey']['call']['quote_id']='auto';


echo "<font size=4>Выбор квоты</font>";
	echo " | <select name=show_type onchange=document.location='survey.quotes.php?show_type='+this.value>
	 <option value='active_only'".($_SESSION['survey']['src_quotes']['show_type']=='active_only'?' selected':NULL).">показать только доступные</option>
	 <option value='all'".($_SESSION['survey']['src_quotes']['show_type']=='all'?' selected':NULL).">показать все</option>
	 </select> ";
echo " | <input type=submit value='Обновить'>";	 
//

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr class=content_tr><td><div class=content_div>";

//ЗАВИСИМЫЕ КВОТЫ
$select="select * from" ; $from=""; $where="";
//собирает запрос
$lvl=0;
$i=0; foreach($src_quoted_fields as $field_id => $field_name) {$i++;
	if($i==1) {
		$from.="(select ssq.id qid,ssq.src_quote quote,
		ssq.STAT_new,
		ssq.STAT_end_norm, 
		decode(ssq.src_quote,0,'100%',decode(ssq.src_quote,NULL,NULL,round(ssq.STAT_end_norm/ssq.src_quote*100,0)||'%')) proc,		
		ssq.STAT_inwork,
		ssq.STAT_nedoz,
		ssq.STAT_perez,
		case when ssq.lock_by_index='y' then 'lock_by_index' when ssq.src_quote-ssq.STAT_end_norm<=0 then 'lock_by_quote' when ssq.STAT_new+ssq.STAT_inwork+ssq.STAT_nedoz+ssq.STAT_perez<=0 then 'no_bd' end lock_row
		from stc_src_quotes ssq where ssq.project_id=".$_SESSION['survey']['project']['id'].") ssq ";
		if($where=='') $where='where '; else $where.='and '; 
		$where.="s".$i.".src_qid=ssq.qid ";
	}
	$from.=", ";
	$from.="(select sqi.quote_id src_qid, si.value val_".$field_id."
	, si.STAT_end_norm end_norm_".$field_id."
	, si.src_idx_quote quote_".$field_id."
	, decode(si.src_idx_quote,0,'100%',decode(si.src_idx_quote,NULL,NULL,round(si.STAT_end_norm/si.src_idx_quote*100,0)||'%')) proc_".$field_id."
	, case when si.src_idx_quote-si.STAT_end_norm<=0 then 'lock_by_index' end lock_".$field_id."
	from  stc_src_indexes si, stc_src_quote_indexes sqi
where si.project_id=".$_SESSION['survey']['project']['id']." and si.field_id=".$field_id." and sqi.index_id=si.id) s".$i." ";
	if($i>1) {
		$where.=" and s".$i.".src_qid=s".($i-1).".src_qid ";
	}
	$order_by.=",s".$i.".val_".$field_id." ";
}
if($show_type=='active_only') $where.=" and ssq.lock_row is null ";

echo "<table class=white_table>";
	
$q=OCIParse($c,$select.$from.$where.$order_by);
OCIExecute($q);
$rownum=0;
$old_src_quote_id='';
$i=0; while(OCIFetch($q)) {$i++; 
	if($i==1) {
		echo "<tr>";
		echo "<td>ID</td>";
		$j=0; foreach($src_quoted_fields as $field_id => $field_name) {$j++;
			echo "<th><a href='survey.quotes.php?order_by=order by val_".$field_id."'>".($_SESSION['survey']['src_quotes']['order_by']=='order by val_'.$field_id?'<b>':NULL).$field_name."</a></th>";
		}
		echo "<th><a href='survey.quotes.php?order_by=order by quote nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by quote nulls first'?'<b>':NULL)."Квота</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by STAT_end_norm nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by STAT_end_norm nulls first'?'<b>':NULL)."Выполнено</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by STAT_new nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by STAT_new nulls first'?'<b>':NULL)."Новых</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by STAT_nedoz nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by STAT_nedoz nulls first'?'<b>':NULL)."Недозвонов</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by STAT_perez nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by STAT_perez nulls first'?'<b>':NULL)."Перезвонов</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by STAT_inwork nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by STAT_inwork nulls first'?'<b>':NULL)."В работе</a></th>";
		echo "</tr>";
	
		//Получение статистики проекта =================================================
		$q_prj=OCIParse($c,"select p.quote,p.stat_new,p.stat_end_norm,p.stat_inwork,p.stat_nedoz,p.stat_perez,
		decode(p.quote,0,'100%',decode(p.quote,NULL,NULL,round(p.stat_end_norm/p.quote*100,0)||'%')) proc,
		case when p.lock_by_index='y' then 'lock_by_index' when p.quote-p.STAT_end_norm<=0 then 'lock_by_quote' when p.STAT_new+p.STAT_inwork+p.STAT_nedoz+p.STAT_perez<=0 then 'no_bd' end lock_row	
		from STC_PROJECTS p
		where p.id='".$_SESSION['survey']['project']['id']."'
		order by p.name, p.create_date");
		OCIExecute($q_prj);		
		OCIFetch($q_prj);


		$color1='';
		if(OCIResult($q_prj,"LOCK_ROW")=='lock_by_quote') 
			$color1=" style=color:green";
		else if(OCIResult($q_prj,"LOCK_ROW")=='no_bd') 
			$color1=" style=color:red";	

		if($_SESSION['survey']['call']['quote_id']=='auto') $tmp_class='  class=selected_row';
		else if(OCIResult($q_prj,"LOCK_ROW")<>'') $tmp_class='  class=not_selectable_row';
		else $tmp_class='  class=selectable_row';	
	
		if(OCIResult($q_prj,"LOCK_ROW")=='') echo "<tr".$tmp_class." onclick=sel_quote('auto')>";
		else echo "<tr".$tmp_class.">";	
	
		echo "<td></td>";
		echo "<td".$color1." colspan=".$j."><b>Авто</th>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='lock_by_quote'?' style=color:green':NULL).">".OCIResult($q_prj,"QUOTE")."</td>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='lock_by_quote'?' style=color:green':NULL).">".OCIResult($q_prj,"STAT_END_NORM")." (".OCIResult($q_prj,"PROC").")</td>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q_prj,"STAT_NEW")."</td>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q_prj,"STAT_NEDOZ")."</td>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q_prj,"STAT_PEREZ")."</td>";
		echo "<td".(OCIResult($q_prj,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q_prj,"STAT_INWORK")."</td>";
		echo "</tr>";
	}
	$rownum++;
	$color1='';
	$color2='';
	if(OCIResult($q,"LOCK_ROW")=='lock_by_quote') 
		$color1=" style=color:green";
	else if(OCIResult($q,"LOCK_ROW")=='no_bd') 
		$color1=" style=color:red";

	if(OCIResult($q,"QID")==$_SESSION['survey']['call']['quote_id']) $tmp_class='  class=selected_row';
	else if(OCIResult($q,"LOCK_ROW")<>'') $tmp_class='  class=not_selectable_row';
	else $tmp_class='  class=selectable_row';	
	
	if(OCIResult($q,"LOCK_ROW")=='') echo "<tr".$tmp_class." onclick='sel_quote(".OCIResult($q,"QID").")'>";
	else echo "<tr".$tmp_class.">";	
		
	//OCIResult($q,"QID")==$_SESSION['survey']['call']['quote_id']?$tmp_class=' class=clicked_row':$tmp_class='';
	echo "<td>".OCIResult($q,"QID")."</td>";
		$j=0; foreach($src_quoted_fields as $field_id => $field_name) {$j++;
			if(OCIResult($q,"LOCK_".$field_id)=='lock_by_index')
				 $color2=" style=color:green";
			else 
				$color2=$color1;
			echo "<td".$color2."><b>".OCIResult($q,"VAL_".$field_id)."</b> ".OCIResult($q,"END_NORM_".$field_id)."/".OCIResult($q,"QUOTE_".$field_id)."(".OCIResult($q,"PROC_".$field_id).")</td>";
		}
			echo "<td".(OCIResult($q,"LOCK_ROW")=='lock_by_quote'?' style=color:green':NULL).">";
			if(OCIResult($q,"QID")<>$old_src_quote_id){
				$old_src_quote_id=OCIResult($q,"QID");
				echo OCIResult($q,"QUOTE");
			}
				echo "</td>";
				echo "<td".(OCIResult($q,"LOCK_ROW")=='lock_by_quote'?' style=color:green':NULL).">".OCIResult($q,"STAT_END_NORM")." (".OCIResult($q,"PROC").")</td>";
				echo "<td".(OCIResult($q,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q,"STAT_NEW")."</td>";
				echo "<td".(OCIResult($q,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q,"STAT_NEDOZ")."</td>";
				echo "<td".(OCIResult($q,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q,"STAT_PEREZ")."</td>";
				echo "<td".(OCIResult($q,"LOCK_ROW")=='no_bd'?' style=color:red':NULL).">".OCIResult($q,"STAT_INWORK")."</td>";

		echo "</tr>";
	}
	echo "</table>";
//
echo "</form>";	

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";

echo "<form name=frm_sel_quote method=post action='survey.call.php' target='callTopFrame'>
<input type=hidden name=quote_id>
</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
</body>
</html>

