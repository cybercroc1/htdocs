<?php 
include("../../conf/starcall_conf/session.cfg.php"); 

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') {echo "<font color=red>Access DENY!</font>"; exit();}

include("../../conf/starcall_conf/conn_string.cfg.php");
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<script src="func.row_select.js"></script>
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

echo "<form name=frm_quotes method=post>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

echo "<font size=4>Выбор квоты</font>";
//

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

if(count($src_quoted_fields)==0) {echo "<font size=3><b>Нет квотируемых полей</b></font>"; 
//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";	
	exit();
}

if(!isset($order_by) and !isset($_SESSION['survey']['src_quotes']['order_by'])) $_SESSION['survey']['src_quotes']['order_by']='order by null';
if(isset($order_by)) $_SESSION['survey']['src_quotes']['order_by']=$order_by;
$order_by=$_SESSION['survey']['src_quotes']['order_by'];

if(!isset($_SESSION['survey']['call']['quote_id'])) $_SESSION['survey']['call']['quote_id']='auto';

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";

//ЗАВИСИМЫЕ КВОТЫ
$select="select * from" ; $from=""; $where="";
//собирает запрос
$lvl=0;
$i=0; foreach($src_quoted_fields as $field_id => $field_name) {$i++;
	if($i==1) {
		$from.="(select ssq.id qid,ssq.src_quote quote,
		ssq.STAT_new new,
		ssq.STAT_end_norm end_norm, 
		decode(ssq.src_quote,0,null,null,null,round(ssq.STAT_end_norm/ssq.src_quote*100,2)) proc,
		ssq.STAT_inwork inwork,
		ssq.STAT_end_error end_error,
		ssq.STAT_end_false end_false,
		ssq.STAT_end_nedoz end_nedoz,
		ssq.STAT_end_otkaz end_otkaz,
		ssq.STAT_end_quote end_quote,
		ssq.STAT_nedoz nedoz,
		ssq.STAT_perez perez
		from stc_src_quotes ssq where ssq.project_id=".$_SESSION['survey']['project']['id'].") ssq ";
		if($where=='') $where='where '; else $where.='and '; 
		$where.="s".$i.".src_qid=ssq.qid ";
	}
	$from.=", ";
	$from.="(select sqi.quote_id src_qid, si.value val_".$field_id." from  stc_src_indexes si, stc_src_quote_indexes sqi
where si.project_id=".$_SESSION['survey']['project']['id']." and si.field_id=".$field_id." and sqi.index_id=si.id) s".$i." ";
	if($i>1) {
		$where.=" and s".$i.".src_qid=s".($i-1).".src_qid ";
	}
	$order_by.=",s".$i.".val_".$field_id." ";
}
echo "<table>";
	
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
		echo "<th><a href='survey.quotes.php?order_by=order by end_norm nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by end_norm nulls first'?'<b>':NULL)."Выполнено</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by new nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by new nulls first'?'<b>':NULL)."Новых</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by nedoz nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by nedoz nulls first'?'<b>':NULL)."Недозвонов</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by perez nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by perez nulls first'?'<b>':NULL)."Перезвонов</a></th>";
		echo "<th><a href='survey.quotes.php?order_by=order by inwork nulls first'>".($_SESSION['survey']['src_quotes']['order_by']=='order by inwork nulls first'?'<b>':NULL)."В работе</a></th>";
		echo "</tr>";
		
		$_SESSION['survey']['call']['quote_id']=='auto'?$tmp_class=' class=clicked_row':$tmp_class='';
		echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)' style='cursor:pointer' onclick=sel_quote('auto')>";
		echo "<td".$tmp_class."></td>";
		echo "<td".$tmp_class." colspan=".$j."><b>Авто</th>";
		echo "<td".$tmp_class."></th>";
		echo "<td".$tmp_class."></th>";
		echo "<td".$tmp_class."></th>";
		echo "<td".$tmp_class."></th>";
		echo "<td".$tmp_class."></th>";
		echo "<td".$tmp_class."></th>";
		echo "</tr>";
	}
	$rownum++;
	echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)' style='cursor:pointer' onclick='sel_quote(".OCIResult($q,"QID").")'>";
	OCIResult($q,"QID")==$_SESSION['survey']['call']['quote_id']?$tmp_class=' class=clicked_row':$tmp_class='';
	echo "<td".$tmp_class.">".OCIResult($q,"QID")."</td>";
		$j=0; foreach($src_quoted_fields as $field_id => $field_name) {$j++;
			echo "<td".$tmp_class."><b>".OCIResult($q,"VAL_".$field_id)."</td>";
		}
			echo "<td".$tmp_class."><b>";
			if(OCIResult($q,"QID")<>$old_src_quote_id){
				$old_src_quote_id=OCIResult($q,"QID");
				echo OCIResult($q,"QUOTE");
			}
				echo "</td>";
				echo "<td".$tmp_class.">".OCIResult($q,"END_NORM")." (".OCIResult($q,"PROC").")</td>";
				echo "<td".$tmp_class.">".OCIResult($q,"NEW")."</td>";
				echo "<td".$tmp_class.">".OCIResult($q,"NEDOZ")."</td>";
				echo "<td".$tmp_class.">".OCIResult($q,"PEREZ")."</td>";
				echo "<td".$tmp_class.">".OCIResult($q,"INWORK")."</td>";

		echo "</tr>";
	}
	echo "</table>";
//
echo "</form>";	
echo "<form name=frm_sel_quote method=post action='survey.call.php' target='callTopFrame'>
<input type=hidden name=quote_id>
</form>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

echo "</form>";
?>
</body>
</html>

