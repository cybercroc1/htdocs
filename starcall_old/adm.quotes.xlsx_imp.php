<?php 

function cp($text) {return iconv('UTF-8','CP1251',$text);}

$project_name=$_SESSION['adm']['project']['name'];
$error='';
$info='';

if(!isset($_FILES['imp_file'])) exit();
if($_SESSION['user']['rw_quote']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "Файл: ".$_FILES['imp_file']['name']."<hr>";

//foreach ($_FILES['imp_file'] as $key => $val) {
//	echo $key." - ".$val;
//	echo "<hr>";
//}

if(!strpos($_FILES['imp_file']['name'],$project_name)) {
	$error.="<font color=red>ОШИБКА: Имя файла ".$_FILES['imp_file']['name']." не соответствует названию проекта ".$project_name.".</font><br>";
	echo $error;
	exit();	
}

//текущий серийный номер квоты
$q=OCIParse($c,"select quote_serial_number from stc_projects where id=".$project_id);
OCIExecute($q);
OCIFetch($q);
$current_serial_number=OCIResult($q,"QUOTE_SERIAL_NUMBER");

/** Include PHPExcel_IOFactory */
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

$sheet=$objPHPExcel->setActiveSheetIndex(0);
$file_serial_number=$sheet->getCell('A1')->getValue();
if($file_serial_number<>$current_serial_number) {
	$error.="<font color=red>ОШИБКА: Не верный серийный номер квоты: $file_serial_number. Текущий серийный номер: $current_serial_number. Выгрузите новый файл.</font><br>";
	echo $error;
	exit();	
}

$sheet_names=$objPHPExcel->getSheetNames();

foreach($sheet_names as $s => $sheet_name) {
	$s_rows=0;
	echo "Лист $s: ".cp($sheet_name)." <br>";
	if(cp($sheet_name)=="Исходные поля") { //составные квоты по исходным
		$quote_col=''; //столбец с квотой
		$sheet=$objPHPExcel->setActiveSheetIndex($s);
		$highcol=$sheet->getHighestColumn(); //максимальный столбец
		echo "Максимальный столбец: $highcol <br>";
		$upd=OCIParse($c,"update STC_SRC_QUOTES q set q.src_quote=:quote
		where q.project_id=".$project_id." and q.id=:quote_id");
		for($col = 'A'; $col <= $highcol; $col++) {
			$val=trim(cp($sheet->getCell($col.'2')->getValue()));
			//echo $val."<hr>";
			if($val=='Квота') { 
				$quote_col=$col; 
				break;
			}
		}
		echo "Столбец с квотой: $quote_col <br>";
		$highrow=$sheet->getHighestRow(); //максимальная строка
		echo "Максимальная строка: $highrow <br>";
		for($r=3; $r<=$highrow; $r++) { //читаем строки
			$quote_id=$sheet->getCell('A'.$r)->getValue();
			$quote=trim($sheet->getCell($quote_col.$r)->getValue());
			if($quote<>'' and !preg_match("/^\d{0,15}$/",$quote)) {
				echo "<font color=red>Ошибка! строка $r не обновлена. Квота \"$quote\" должна быть целым положительным числом</font><br>";
				continue;
			}
			OCIBindByName($upd,":quote_id",$quote_id);
			OCIBindByName($upd,":quote",$quote);
			//echo "Квота: ".$quote."<hr>";
			OCIExecute($upd, OCI_DEFAULT);
			//echo "Обновлено строк: ".oci_num_rows($upd)."<hr>";
			$s_rows+=oci_num_rows($upd);
			if(oci_num_rows($upd)==0) {// если квота не существует то добавляем:
				echo "<font color=red>Ошибка! строка $r не обновлена. Не найдена квота с таким ID ($quote_id)</font><br>";
				continue;				
			}
		}		
	echo "Обновлено строк: ".$s_rows."<hr>";
	}
	OCICommit($c);

	if(substr(cp($sheet_name),0,7)=="Уровень") { //зависимые квоты по вопросам
		$multi='y';
		$quote_col=''; //столбец с квотой
		$sheet=$objPHPExcel->setActiveSheetIndex($s);
		$highcol=$sheet->getHighestColumn(); //максимальный столбец
		echo "Максимальный столбец: $highcol <br>";
		$upd=OCIParse($c,"update STC_QST_QUOTES q set q.qst_quote=:quote
		where q.project_id=".$project_id." and q.id=:quote_id");
		for($col = 'A'; $col <= $highcol; $col++) {
			$val=trim(cp($sheet->getCell($col.'2')->getValue()));
			//echo $val."<hr>";
			if($val=='Квота') { 
				$quote_col=$col; 
				break;
			}
		}
		echo "Столбец с квотой: $quote_col <br>";
		$highrow=$sheet->getHighestRow(); //максимальная строка
		echo "Максимальная строка: $highrow <br>";
		for($r=3; $r<=$highrow; $r++) { //читаем строки
			$quote_id=$sheet->getCell('A'.$r)->getValue();
			$quote=trim($sheet->getCell($quote_col.$r)->getValue());
			if($quote<>'' and !preg_match("/^\d{0,15}$/",$quote)) {
				echo "<font color=red>Ошибка! строка $r не обновлена. Квота \"$quote\" должна быть целым положительным числом</font><br>";
				continue;
			}
			OCIBindByName($upd,":quote_id",$quote_id);
			OCIBindByName($upd,":quote",$quote);
			//echo "Квота: ".$quote."<hr>";
			OCIExecute($upd, OCI_DEFAULT);
			//echo "Обновлено строк: ".oci_num_rows($upd)."<hr>";
			$s_rows+=oci_num_rows($upd);
			if(oci_num_rows($upd)==0) {// если квота не существует то добавляем:
				echo "<font color=red>Ошибка! строка $r не обновлена. Не найдена квота с таким ID ($quote_id)</font><br>";
				continue;				
			}
		}			
	echo "Обновлено строк: ".$s_rows."<hr>";
	}
	OCICommit($c);	

	if(cp($sheet_name)=="Независимые по исх.") { //независимые квоты по исходным (индексы)
		$quote_col=''; //столбец с квотой
		$src_single='y';
		$sheet=$objPHPExcel->setActiveSheetIndex($s);
		$highcol=$sheet->getHighestColumn(); //максимальный столбец
		echo "Максимальный столбец: $highcol <br>";
		$upd=OCIParse($c,"update STC_SRC_INDEXES i set i.src_idx_quote=:quote
		where i.project_id=".$project_id." and i.id=:index_id");
		for($col = 'A'; $col <= $highcol; $col++) {
			$val=trim(cp($sheet->getCell($col.'2')->getValue()));
			//echo $val."<hr>";
			if($val=='Квота') { 
				$quote_col=$col; 
				break;
			}
		}
		echo "Столбец с квотой: $quote_col <br>";
		$highrow=$sheet->getHighestRow(); //максимальная строка
		echo "Максимальная строка: $highrow <br>";
		for($r=3; $r<=$highrow; $r++) { //читаем строки
			$index_id=$sheet->getCell('A'.$r)->getValue();
			$quote=trim($sheet->getCell($quote_col.$r)->getValue());
			if($quote<>'' and !preg_match("/^\d{0,15}$/",$quote)) {
				echo "<font color=red>Ошибка! строка $r не обновлена. Квота \"$quote\" должна быть целым положительным числом</font><br>";
				continue;
			}
			OCIBindByName($upd,":index_id",$index_id);
			OCIBindByName($upd,":quote",$quote);
			//echo "Квота: ".$quote."<hr>";
			OCIExecute($upd, OCI_DEFAULT);
			//echo "Обновлено строк: ".oci_num_rows($upd)."<hr>";
			$s_rows+=oci_num_rows($upd);
			if(oci_num_rows($upd)==0) {// если квота не существует то добавляем:
				echo "<font color=red>Ошибка! строка $r не обновлена. Не найдена квота с таким ID ($quote_id)</font><br>";
				continue;				
			}
		}		
	echo "Обновлено строк: ".$s_rows."<hr>";
	}
	OCICommit($c);

	if(cp($sheet_name)=="Независимые по вопросам") { //независимые квоты по вопросам (индексы)
		$quote_col=''; //столбец с квотой
		$sheet=$objPHPExcel->setActiveSheetIndex($s);
		$highcol=$sheet->getHighestColumn(); //максимальный столбец
		echo "Максимальный столбец: $highcol <br>";
		$upd=OCIParse($c,"update STC_QST_INDEXES i set i.qst_idx_quote=:quote
		where i.project_id=".$project_id." and i.id=:index_id");
		for($col = 'A'; $col <= $highcol; $col++) {
			$val=trim(cp($sheet->getCell($col.'2')->getValue()));
			//echo $val."<hr>";
			if($val=='Квота') { 
				$quote_col=$col; 
				break;
			}
		}
		echo "Столбец с квотой: $quote_col <br>";
		$highrow=$sheet->getHighestRow(); //максимальная строка
		echo "Максимальная строка: $highrow <br>";
		for($r=3; $r<=$highrow; $r++) { //читаем строки
			$index_id=$sheet->getCell('A'.$r)->getValue();
			$quote=trim($sheet->getCell($quote_col.$r)->getValue());
			if($quote<>'' and !preg_match("/^\d{0,15}$/",$quote)) {
				echo "<font color=red>Ошибка! строка $r не обновлена. Квота \"$quote\" должна быть целым положительным числом</font><br>";
				continue;
			}
			OCIBindByName($upd,":index_id",$index_id);
			OCIBindByName($upd,":quote",$quote);
			//echo "Квота: ".$quote."<hr>";
			OCIExecute($upd, OCI_DEFAULT);
			//echo "Обновлено строк: ".oci_num_rows($upd)."<hr>";
			$s_rows+=oci_num_rows($upd);
			if(oci_num_rows($upd)==0) {// если квота не существует то добавляем:
				echo "<font color=red>Ошибка! строка $r не обновлена. Не найдена квота с таким ID ($quote_id)</font><hr>";
				continue;				
			}
		}		
	echo "Обновлено строк: ".$s_rows."<hr>";
	}
	OCICommit($c);
}

//пересчет зависимых значений квот
if(isset($multi)) {
	OCIExecute(OCIParse($c,"begin STC_QUOTE_PARENT_CALC(".$project_id."); end;"));
	echo "Пересчитаны зависимые значения квот (STC_QUOTE_PARENT_CALC)<br>
		Пересчитаны зависимые значения квот (STC_QUOTE_COMMON_CALC)<hr>";
	OCICommit($c);
}
//пересчет только общей квоты по проекту
else {
	OCIExecute(OCIParse($c,"begin STC_QUOTE_COMMON_CALC(".$project_id."); end;"));
	echo "Пересчитана общая квота (STC_QUOTE_COMMON_CALC)<hr>";
	OCICommit($c);	
}
//блокировка записей по исчепанным одиночным исходным квотам
if(isset($src_single)) {
	OCIExecute(OCIParse($c,"begin STC_SRC_SINGLE_QUOTE_LOCK(".$project_id."); end;"));
	echo "Обновлена блокировка записей по независимым исх.квотам (STC_SRC_SINGLE_QUOTE_LOCK)<hr>";
	OCICommit($c);
}
unlink($_FILES['imp_file']['tmp_name']);

?>

