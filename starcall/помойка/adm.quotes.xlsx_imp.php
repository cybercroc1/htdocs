<?php 
set_time_limit(600);
ini_set('memory_limit','64M');
include("../../starcall_conf/session.cfg.php"); 
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">
<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
function cp($text) {return iconv('UTF-8','CP1251',$text);}

if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();
$project_id=$_SESSION['project']['id'];
$project_name=$_SESSION['project']['name'];
$error='';
$info='';
include("../../starcall_conf/conn_string.cfg.php");

if(!isset($_FILES['imp_file'])) exit();

echo "Загрузка фала: ".$_FILES['imp_file']['name']."<hr>";

foreach ($_FILES['imp_file'] as $key => $val) {
	echo $key." - ".$val;
	echo "<hr>";
}

if(!strpos($_FILES['imp_file']['name'],$project_name)) {
	$error.="<font color=red>ОШИБКА: Имя файла ".$_FILES['imp_file']['name']." не соответствует названию проекта ".$project_name.".</font><br>";
	echo $error;
	echo "<script>
	parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$error."';
	parent.admBottomFrame.frm.frm_submit.value='save';
	parent.admBottomFrame.frm.save.value='Сохранить';
	parent.admBottomFrame.frm.save.disabled=false;
	parent.admBottomFrame.frm.cancel.style.display='none';
	</script>";	
	exit();	
}

/** Include PHPExcel_IOFactory */
$err='';
require_once dirname(__FILE__) . '/../../Classes/PHPExcel/IOFactory.php';

// получаем тип файла (xls, xlsx), чтобы правильно его обработать
$file_type = PHPExcel_IOFactory::identify($_FILES['imp_file']['tmp_name']);

// создаем объект для чтения
$objReader = PHPExcel_IOFactory::createReader($file_type);
//только для чтения
$objReader->setReadDataOnly(true);

$objPHPExcel = $objReader->load($_FILES['imp_file']['tmp_name']); // загружаем данные файла в объект

$sheet_count=$objPHPExcel->getSheetCount();
echo "Кол-во листов: $sheet_count <hr>";

$sheet_names=$objPHPExcel->getSheetNames();

foreach($sheet_names as $s => $sheet_name) {
	echo "Лист $s: ".cp($sheet_name)." <hr>";
	if(cp($sheet_name)=="Исходные поля") { //составные квоты по исходным
		$field_names=array(); //имена полей
		$quote_col=''; //столбец с квотой
		$sheet=$objPHPExcel->setActiveSheetIndex($s);
		$highcol=$sheet->getHighestColumn(); //максимальный столбец
		$vars=array(); //имена переменных в запросе
		echo "Максимальный столбец: $highcol <hr>";
		
		$sql_upd_src_quotes="update STC_SRC_QUOTES q set q.src_quote=:quote
		where q.id=
		(select quote_id from 
		(
		select qi.quote_id,count(*) cnt from STC_SRC_INDEXES i, STC_SRC_QUOTE_INDEXES qi, STC_FIELDS f
		where i.project_id=".$project_id."
		and qi.project_id=".$project_id." and qi.index_id=i.id
		and f.project_id=".$project_id." and f.deleted is null
		and f.id=i.field_id
		and 
		(1=2 ";
		
		$sql_quote_check="select quote_id from (
		select quote_id, count(*) cnt
		from STC_SRC_QUOTE_INDEXES
		where project_id=".$project_id." and index_id in (0";		
		
		for($col = 'A'; $col <= $highcol; $col++) {
			$val=trim(cp($sheet->getCell($col.'2')->getValue()));
			echo $val."<hr>";
			if($val=='Квота') { 
				$quote_col=$col; 
				break;
			}
			else {
				$field_names[$col]=$val;
				$sql_upd_src_quotes.="or (f.text_name=:f".$col." and i.value=:v".$col.") ";
				$sql_quote_check.=",:".$col;
				
			}
			
		}
		$sql_upd_src_quotes.=")
		group by qi.quote_id
		)
		where cnt=".count($field_names).")";
		
		$sql_quote_check.=")
		group by quote_id
		)
		where cnt=".count($field_names);
		
		$upd=OCIParse($c,$sql_upd_src_quotes);
		$q_quote_check=OCIParse($c,$sql_quote_check);
		
		$q_src_field_id=OCIParse($c,"select id field_id from STC_FIELDS f
		where f.project_id=".$project_id." and f.src_type_id=1 and f.quoted is not null and f.deleted is null
		and f.text_name=:field_name");

		$q_src_index_id=OCIParse($c,"select id idx_id from STC_SRC_INDEXES i
		where i.project_id=".$project_id." and i.field_id=:field_id and i.value=:val");

		$ins_src_idx=OCIParse($c,"insert into STC_SRC_INDEXES (id,Project_Id,Field_Id,value)
		values (seq_stc_index_id.nextval,".$project_id.",:field_id,:val) returning id into :idx_id");

		$q_ins_quote=OCIParse($c,"insert into STC_SRC_QUOTES (id,project_id,field_count) 
		values (SEQ_STC_QUOTE_ID.nextval,".$project_id.",".count($field_names).") returning id into :quote_id");
		$q_ins_quote_idx=OCIParse($c,"insert into STC_SRC_QUOTE_INDEXES (project_id,quote_id,index_id) values (".$project_id.",:quote_id,:index_id)");		
		
		echo $sql_upd_src_quotes."<hr>";
		echo $sql_quote_check."<hr>";		
		echo "Столбец с квотой: $quote_col <hr>";
		$highrow=$sheet->getHighestRow(); //максимальная строка
		echo "Максимальная строка: $highrow <hr>";
		for($r=3; $r<=$highrow; $r++) { //читаем строки
			$quote='';
			$field_val=array();
			foreach($field_names as $col => $field_name) {
				$field_val[$col]=trim(cp($sheet->getCell($col.$r)->getValue()));
				if($field_val[$col]=='') {
					$err.="<font color=red>Ошибка! строка $r не обновлена. Пустое значение поля</font><br>";
					break;
				}
				$quote=trim($sheet->getCell($quote_col.$r)->getValue());
				OCIBindByName($upd,":f".$col,$field_names[$col]);
				OCIBindByName($upd,":v".$col,$field_val[$col]);
				echo $field_names[$col]." - ".$field_val[$col]."<br>";
			}
			if(!preg_match("/^\d{0,15}$/",$quote)) {
				$err.="<font color=red>Ошибка! строка $r не обновлена. Квота \"$quote\" должна быть целым положительным числом</font><br>";
			}
			if($err<>'') {
				echo $err."<hr>";
				$err='';
				continue;
			}				
			OCIBindByName($upd,":quote",$quote);
			echo "Квота: ".$quote."<hr>";
			OCIExecute($upd, OCI_DEFAULT);
			echo "Обновлено строк: ".oci_num_rows($upd)."<hr>";
			if(oci_num_rows($upd)==0) {// если квота не существует то добавляем:
				$qst_quote_broken='y';
				
				
				
				//echo "<font color=red>Ошибка! строка $r не обновлена. Не найдена квота с такими занчениями</font><hr>";
				//continue;				
			
			
			}
		}		
	OCICommit($c);
	}
}

//предупреждение о том, что добавлены новые квоты
if(isset($qst_quote_broken)) { 
	$info.="<font color=red>ВНИМАНИЕ! Добавлены новые квоты, не забудьте прописать значения</font><br>";
	$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes' where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
	echo $info;
	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>"; 
}
OCICommit($c);

unlink($_FILES['imp_file']['tmp_name']);

?>

