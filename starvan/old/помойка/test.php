<?php 
include("../../conf/starcall_conf/conn_string.cfg.php");
$project_id=25;


$src_fields=array();
$quest_fields=array();


function build_query($c,$project_id) {
	global $src_fields;
	global $quest_fields;
	//список исходных полей
	$q=OCIParse($c,"select id,text_name from STC_FIELDS t
	where project_id=".$project_id." and t.src_type_id=1 and t.quoted is not null and t.deleted is null
	order by t.ord");
	OCIExecute($q);
	while(OCIFetch($q)) {
		$src_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
	}
	//список квотируемых вопросов
	$q=OCIParse($c,"select o.quote_num,f.text_name from STC_OBJECTS o, Stc_Fields f
	where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
	and f.deleted is null and f.id=o.field_id
	order by o.quote_num");
	OCIExecute($q); $i=0; while(OCIFetch($q)) {$i++;
		$quest_fields[OCIResult($q,"QUOTE_NUM")]=OCIResult($q,"TEXT_NAME");
		//$old_qst_quote_id[$i]='';
	}

	//выходим из функции, если нет квотируемых полей
	if(count($src_fields)==0 and count($quest_fields)==0) return NULL;

	$sql1="select * from ";	$sql2=""; $sql3="";	$sql4="order by ";
	//собирает запрос
	//если есть исходные поля
	if(count($src_fields)>0) {
		$i=0; foreach($src_fields as $field_id => $field_name) {$i++;
			if($i==1) {
				$sql2.="(select ssq.id src_quote_id,ssq.src_quote,ssq.src_new,ssq.src_norm from stc_src_quotes ssq where ssq.project_id=".$project_id.") ssq ";
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".q_id".$i."=ssq.src_quote_id ";
			}
			$sql2.=", ";
			$sql2.="(select sqi.quote_id q_id".$i.", si.value sval".$i." from  stc_src_indexes si, stc_src_quote_indexes sqi
where si.project_id=".$project_id." and si.field_id=".$field_id." and sqi.index_id=si.id) s".$i." ";
			if($i>1) {
				if($sql3=='') $sql3='where '; else $sql3.='and '; 
				$sql3.="s".$i.".q_id".$i."=s".($i-1).".q_id".($i-1)." ";
				$sql4.=", ";
			}
			$sql4.="s".$i.".sval".$i." ";
			
		}
		if(count($quest_fields)>0) {
			$sql2.=", ";
			$sql4.=", ";
		} 
	}
	//квоты по вопросам, если есть хотя бы один квотируемый вопрос
	if(count($quest_fields)>0) {
		for($i=1; $i<=count($quest_fields); $i++) {
			if($i>1) {
				$sql2.=", ";
				$sql4.=", ";				
			}
			if($i==1) {
				$sql2.="(select qq.src_quote_id,qq.id qid".$i.",i.value qval".$i.", qq.qst_quote qst_quote".$i.", qq.qst_norm qst_norm".$i." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$i." and qq.index_id=i.id) q".$i." ";
				if(count($src_fields)>0) {
					if($sql3=='') $sql3='where '; else $sql3.='and ';
					$sql3.="q".$i.".src_quote_id=ssq.src_quote_id ";
				}
			}
			else {
				$sql2.="(select qq.parent_id,qq.id qid".$i.",i.value qval".$i.", qq.qst_quote qst_quote".$i.", qq.qst_norm qst_norm".$i." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$i." and qq.index_id=i.id) q".$i." ";
				if($sql3=='') $sql3='where '; else $sql3.='and ';
				$sql3.="q".$i.".parent_id=q".($i-1).".qid".($i-1)." ";
			}
			$sql4.="q".$i.".qval".$i." ";		
		}
	}
	return $sql1.$sql2.$sql3.$sql4;
}
echo "<table border=1>";
$sql=build_query($c,25);
$q=OCIParse($c,$sql);
OCIExecute($q);
$rownum=0;
$old_src_quote_id='';
$i=0; while(OCIFetch($q)) {$i++; 
	if($i==1) {
		echo "<tr><td></td>";
		if(count($src_fields)>0) echo "<th colspan=".(count($src_fields)+1).">И С Х О Д Н Ы Е  П О Л Я</th>";
		
		for($j=1; $j<=count($quest_fields); $j++) {
			echo "<th colspan=2>У Р О В Е Н Ь ".$j."</th>";
		}
		
		echo "</tr>";
				
		echo "<tr><td>№</td>";
		if(count($src_fields)>0) {
			$j=0; foreach($src_fields as $field_id => $field_name) {$j++;
				echo "<th>".$field_name."</th>";
			}
			echo "<th>Квота</th>";
		}
		if(count($quest_fields)>0) {
			$j=0; foreach($quest_fields as $num => $quest_name) {$j++;
				if($j>count($quest_fields)) break;
				echo "<th>".$quest_name."</th>";
				echo "<th>Квота</th>";
			}
		}
		echo "</tr>";
	}
	$rownum++;
	echo "<tr><td>".$rownum."</td>";
	if(count($src_fields)>0) {
		$j=0; foreach($src_fields as $field_id => $field_name) {$j++;
			echo "<td>".OCIResult($q,"SVAL".$j)."</th>";
		}

			echo "<td>";
			//if(OCIResult($q,"Q_ID".$j)<>$old_src_quote_id){
				$old_src_quote_id=OCIResult($q,"Q_ID".$j);
				echo "<input type=text size=1 name=src_quote_id[".OCIResult($q,"Q_ID".$j)."] value='".OCIResult($q,"SRC_QUOTE")."'>";
			//}
			echo "</td>";
	}
	if(count($quest_fields)>0) {
		$j=0; foreach($quest_fields as $field_id => $field_name) {$j++;
			if($j>count($quest_fields)) break;
			echo "<td>".OCIResult($q,"QVAL".$j)."</td>";
				echo "<td>";
				//if(OCIResult($q,"QID".$j)<>$old_qst_quote_id[$j]) {
					$old_qst_quote_id[$j]=OCIResult($q,"QID".$j);
					echo "<input type=text size=1 name=qst_quote_id[".OCIResult($q,"QID".$j)."] value='".OCIResult($q,"QST_QUOTE".$j)."'>";
				//}
				echo "</td>";
		}
	}
	echo "</tr>";
}
echo "</table>";
//

?>
</body>
</html>

