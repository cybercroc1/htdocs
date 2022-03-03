<?php 
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
function u8($text) {return iconv('CP1251','UTF-8',$text);}
function cp($text) {return iconv('UTF-8','CP1251',$text);}

include("../../starcall_conf/session.cfg.php"); 
extract($_REQUEST);

if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();
$project_id=$_SESSION['project']['id'];
include("../../starcall_conf/conn_string.cfg.php");

$quoted_src_fields=array();
$idx_src_fields=array();
$quoted_quest_fields=array();
$quest_fields=array();

//список исходных полей
$q=OCIParse($c,"select id,t.quoted,t.idx,text_name from STC_FIELDS t
where project_id=".$project_id." and t.src_type_id=1 and t.quoted is not null and t.deleted is null
order by t.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$quoted_src_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}
//список квотируемых вопросов
$q=OCIParse($c,"select o.quote_num,f.text_name from STC_OBJECTS o, Stc_Fields f
where o.project_id=".$project_id." and o.quote_num is not null and o.deleted is null
and o.project_id=".$project_id." and f.id=o.field_id and  f.deleted is null
order by o.quote_num");
OCIExecute($q); $i=0; while(OCIFetch($q)) {$i++;
	$quoted_quest_fields[OCIResult($q,"QUOTE_NUM")]=OCIResult($q,"TEXT_NAME");
}

/** Include PHPExcel */
require_once dirname(__FILE__) . '/../../Classes/PHPExcel.php';
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//Свойства документа
$objPHPExcel->getProperties()
			->setTitle(u8("Квоты"))
			->setSubject(u8("Квоты"))
			->setDescription(u8("Квоты"))
			->setKeywords(u8("Квоты"))
			->setCategory(u8("Квоты"));


//Зависимые квоты==================================================================
if(count($quoted_src_fields)>0 or count($quoted_quest_fields)>0) {
	//собираем запрос
	//если есть исходные поля
	$sql1="select * from ";	$sql2=""; $sql3="";	$sql4="order by ";
	$lvl=0; //уровень: 0 - исходные поля, далее по вопросам
	if(count($quoted_src_fields)>0) {
		$lvl=0;
		$i=0; foreach($quoted_src_fields as $field_id => $field_name) {$i++;
			if($i==1) {
				$sql2.="(select ssq.id qid0,ssq.src_quote quote0,ssq.src_new new0,ssq.src_norm norm0 from stc_src_quotes ssq where ssq.project_id=".$project_id.") ssq ";
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
		if(count($quoted_quest_fields)>0) {
			$sql2.=", ";
			$sql4.=", ";
		} 
	}
	//квоты по вопросам, если есть хотя бы один квотируемый вопрос
	if(count($quoted_quest_fields)>0) {
		for($i=1; $i<=count($quoted_quest_fields); $i++) {
			$lvl++;
			if($i>1) {
				$sql2.=", ";
				$sql4.=", ";				
			}
			if($i==1) {
				$sql2.="(select qq.src_quote_id src_qid, qq.id qid".$lvl.",i.value val".$lvl.", qq.qst_quote quote".$lvl.", qq.qst_norm norm".$lvl." from STC_QST_INDEXES i, stc_qst_quotes qq
where i.project_id=".$project_id." and qq.quote_level=".$lvl." and qq.index_id=i.id) q".$lvl." ";
				if(count($quoted_src_fields)>0) {
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
	$q=OCIParse($c,$sql1.$sql2.$sql3.$sql4);
	OCIExecute($q);

	//ШАПКА ТАБЛИЦЫ
	$s=0; //счетчик листов
	$j=0; //счетчик полей
	$all_fields=array();
	if(count($quoted_src_fields)>0) {
		$s++; //счетчик листов
		if($s==1) {
			$objPHPExcel->setActiveSheetIndex($s-1);
			$sheet[$s]=$objPHPExcel->getActiveSheet();
			$s_cols[$s]=0;
			$s_rows[$s]=0;
		}
		else {$sheet[$s]=$objPHPExcel->createSheet(); $s_cols[$s]=0; $s_rows[$s]=0;}
		
		foreach($quoted_src_fields as $field_name) {
			$j++; //счетчик полей
			$all_fields[$j]=$field_name;
		
			if($j==1) { 
				$sheet[$s]->setCellValue('A1', u8("Исходные поля (на этом листе можно добавлять квоты)"));
				$sheet[$s]->getStyle('A1')->getFont()->setBold(true);
				$sheet[$s]->setTitle(u8("Исходные поля"));
			}
			$coord=$sheet[$s]->getCellByColumnAndRow($j-1,2)->getCoordinate();
			$sheet[$s]->getStyle($coord)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$sheet[$s]->setCellValue($coord, u8($field_name));
			$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
		}
		$coord=$sheet[$s]->getCellByColumnAndRow($j+2,1)->getCoordinate();
		$sheet[$s]->mergeCells('A1:'.$coord);
		
		$coord=$sheet[$s]->getCellByColumnAndRow($j,2)->getCoordinate();
		$sheet[$s]->setCellValue($coord, u8('Квота'));
		$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
		$coord=$sheet[$s]->getCellByColumnAndRow($j+1,2)->getCoordinate();
		$sheet[$s]->setCellValue($coord, u8('Новых'));
		$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
		$coord=$sheet[$s]->getCellByColumnAndRow($j+2,2)->getCoordinate();
		$sheet[$s]->setCellValue($coord, u8('Выполнено'));
		$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
		$sheet[$s]->getStyle('A2:'.$coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}

	if(count($quoted_quest_fields)>0) {
			foreach($quoted_quest_fields as $level => $field_name) {
			$j++; //счетчик полей
			$all_fields[$j]=$field_name;
			$s++; //счетчик листов
			if(!isset($sheet[$s])) {
				if($s==1) {
					$objPHPExcel->setActiveSheetIndex($s-1);
					$sheet[$s]=$objPHPExcel->getActiveSheet();	
				}
				else {$sheet[$s]=$objPHPExcel->createSheet(); $s_cols[$s]=0; $s_rows[$s]=0;}
			}
			$sheet[$s]->setCellValue('B1', u8('Уровень '.$level));
			$sheet[$s]->getStyle('B1')->getFont()->setBold(true);
			$sheet[$s]->setTitle(u8('Уровень '.$level));
			
			$sheet[$s]->setCellValue('A2', u8('ID квоты'));
			
			foreach($all_fields as $x => $field_name) {
				$coord=$sheet[$s]->getCellByColumnAndRow($x-1+1,2)->getCoordinate();
				$sheet[$s]->getStyle($coord)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
				$sheet[$s]->setCellValue($coord, u8($field_name));
				$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
			}	
			
			$coord=$sheet[$s]->getCellByColumnAndRow(count($all_fields)+2,1)->getCoordinate();
			$sheet[$s]->mergeCells('B1:'.$coord);
			
			$coord=$sheet[$s]->getCellByColumnAndRow(count($all_fields)+1,2)->getCoordinate();
			$sheet[$s]->setCellValue($coord, u8('Квота'));
			$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
			$coord=$sheet[$s]->getCellByColumnAndRow(count($all_fields)+2,2)->getCoordinate();
			$sheet[$s]->setCellValue($coord, u8('Выполнено'));
			$sheet[$s]->getStyle($coord)->getFont()->setBold(true);			
			$sheet[$s]->getStyle('B2:'.$coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		}
	}
	//ДАННЫЕ
	$j=0; //счетчик полей
	$x=0;
	$i=2; 
	$boldtop='';//верхняя шраница нового блока

	if(count($quoted_src_fields)>0) $minlvl=0; else $minlvl=1;

	while(OCIFetch($q)) {$i++;
	
		for($lvl=$minlvl; $lvl<=count($quoted_quest_fields); $lvl++) { //уровень: 0 - исходная квота, далее по номеру вопроса
		
		 	$x=0;
			$j=0;
			$s=$lvl;
			
			//пропускаем лишние строки	
			if(isset($quote_id[$lvl]) and $quote_id[$lvl]==OCIResult($q,"QID".$lvl)) continue;
			$quote_id[$lvl]=OCIResult($q,"QID".$lvl);
			
			//номер строки на каждом листе
			!isset($ilvl[$lvl])?$ilvl[$lvl]=3:$ilvl[$lvl]++;

			if(count($quoted_src_fields)>0) $s++;
				
			if($lvl>0) { //для уровней доаввляем поле с ID квоты
				$x++; 
				$coord=$sheet[$s]->getCellByColumnAndRow('A',$ilvl[$lvl])->getCoordinate();
				$sheet[$s]->setCellValue($coord, u8($quote_id[$lvl]));
			}
			
			if(count($quoted_src_fields)>0) {
				//$s++;

				$j=0; foreach($quoted_src_fields as $field_id => $field_name) {$j++;
					
				$x++; 
				
				$coord=$sheet[$s]->getCellByColumnAndRow($x-1,$ilvl[$lvl])->getCoordinate();
				$sheet[$s]->getStyle($coord)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
				$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"VAL0_".$j)));
				$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				}
				if($lvl==0) { //не показываем квоту для не последнего уровня
					$x++;
					$coord=$sheet[$s]->getCellByColumnAndRow($x-1,$ilvl[$lvl])->getCoordinate();
					$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"QUOTE0")));
					$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
					$sheet[$s]->getStyle($coord)->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
					$sheet[$s]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

					$coord=$sheet[$s]->getCellByColumnAndRow($x-1+1,$ilvl[$lvl])->getCoordinate();
					$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"NEW0")));
					$sheet[$s]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					$coord=$sheet[$s]->getCellByColumnAndRow($x-1+2,$ilvl[$lvl])->getCoordinate();
					$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"NORM0")));
					$sheet[$s]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
				
				}
			}

			if(count($quoted_quest_fields)>0) {
	
					$j=0; 
					foreach($quoted_quest_fields as $quest_lvl => $field_name) {$j++;
					if($j>$lvl) break;
					$x++; 
					$coord=$sheet[$s]->getCellByColumnAndRow($x-1,$ilvl[$lvl])->getCoordinate();
					$sheet[$s]->getStyle($coord)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
					$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"VAL".$quest_lvl)));
					$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
					if($j==$lvl) { //не показываем квоту для не последнего уровня
						$x++;
						$coord=$sheet[$s]->getCellByColumnAndRow($x-1,$ilvl[$lvl])->getCoordinate();
						$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"QUOTE".$quest_lvl)));
						$sheet[$s]->getStyle($coord)->getFont()->setBold(true);
						$sheet[$s]->getStyle($coord)->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
						$sheet[$s]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						
						$coord=$sheet[$s]->getCellByColumnAndRow($x-1+1,$ilvl[$lvl])->getCoordinate();
						$sheet[$s]->setCellValue($coord, u8(OCIResult($q,"NORM".$quest_lvl)));
						$sheet[$s]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
						$sheet[$s]->getStyle($coord)->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
						
					}
				}
			}	
		if($lvl==0) {
			
		
		}
		}
	}
}
//зависимые квоты==конец=====================================================================
//Независимые по исх=========================================================================

if(!isset($sheet)) $s=1; else $s=count($sheet)+1;
$old_name='';
$q=OCIParse($c,"select i.id idx_id,f.text_name,i.value, i.src_idx_quote,i.src_idx_new,i.src_idx_norm
from STC_FIELDS f, STC_SRC_INDEXES i
where f.project_id=".$project_id." and f.deleted is null and f.src_type_id=1 and (f.quoted is not null or f.idx is not null)
and i.project_id=".$project_id." and i.field_id=f.id
order by f.text_name,i.value");
OCIExecute($q);
$i=0; while(OCIFetch($q)) {$i++;
	if($i==1) {
		//шапка таблицы и создаем лист, если надо
		if($s==1) {
			$objPHPExcel->setActiveSheetIndex($s-1);
			$sheet[$s]=$objPHPExcel->getActiveSheet();
		}
		else $sheet[$s]=$objPHPExcel->createSheet();
		
		$sheet[$s]->setCellValue('A1', u8("Независимые квоты по исходным полям (на этом листе можно добавлять квоты)"));
		$sheet[$s]->getStyle('A1')->getFont()->setBold(true);
		$sheet[$s]->mergeCells('A1:E1');
		$sheet[$s]->setTitle(u8("Независимые по исх."));
		$sheet[$s]->setCellValue('A2', u8('Исх. поле'));
		$sheet[$s]->getStyle('A2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('B2', u8('Значение'));
		$sheet[$s]->getStyle('B2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('C2', u8('Квота'));
		$sheet[$s]->getStyle('C2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('D2', u8('Новых'));
		$sheet[$s]->getStyle('D2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('E2', u8('Выполнено'));
		$sheet[$s]->getStyle('E2')->getFont()->setBold(true);				
		$sheet[$s]->getStyle('A2:E2')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}
	$sheet[$s]->getStyle('A'.($i+2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$sheet[$s]->setCellValue('A'.($i+2), u8(OCIResult($q,"TEXT_NAME")));
	$sheet[$s]->getStyle('A'.($i+2))->getFont()->setBold(true);
	$sheet[$s]->getStyle('B'.($i+2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$sheet[$s]->setCellValue('B'.($i+2), u8(OCIResult($q,"VALUE")));

	$sheet[$s]->setCellValue('C'.($i+2), u8(OCIResult($q,"SRC_IDX_QUOTE")));
	$sheet[$s]->getStyle('C'.($i+2))->getFont()->setBold(true);
	$sheet[$s]->getStyle('C'.($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet[$s]->getStyle('C'.($i+2))->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );
	
	$sheet[$s]->setCellValue('D'.($i+2), u8(OCIResult($q,"SRC_IDX_NEW")));
	$sheet[$s]->getStyle('D'.($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$sheet[$s]->setCellValue('E'.($i+2), u8(OCIResult($q,"SRC_IDX_NORM")));
	$sheet[$s]->getStyle('E'.($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet[$s]->getStyle('A'.($i+2).':E'.($i+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	
	if($old_name<>OCIResult($q,"TEXT_NAME")) $sheet[$s]->getStyle('A'.($i+2).':E'.($i+2))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$old_name=OCIResult($q,"TEXT_NAME");
}

//независимые по исходным==конец================================================================

//Независимые по вопросам=========================================================================

if(!isset($sheet)) $s=1; else $s=count($sheet)+1;
$old_name='';
$q=OCIParse($c,"select qi.id idx_id,f.text_name,qi.value, qi.qst_idx_quote, qi.qst_idx_norm
from stc_objects o, stc_fields f, stc_qst_indexes qi 
where o.project_id=".$project_id." and o.deleted is null
and f.project_id=".$project_id." and f.id=o.field_id
and qi.object_id=o.id
order by f.text_name,qi.value");
OCIExecute($q);
$i=0; while(OCIFetch($q)) {$i++;
	if($i==1) {
		//шапка таблицы и создаем лист, если надо
		if($s==1) {
			$objPHPExcel->setActiveSheetIndex($s-1);
			$sheet[$s]=$objPHPExcel->getActiveSheet();
		}
		else $sheet[$s]=$objPHPExcel->createSheet();
		
		$sheet[$s]->setCellValue('A1', u8("Независимые квоты по вопросам"));
		$sheet[$s]->getStyle('A1')->getFont()->setBold(true);
		$sheet[$s]->mergeCells('A1:C1');
		$sheet[$s]->setTitle(u8("Независимые по вопросам"));
		$sheet[$s]->setCellValue('A2', u8('Вопрос'));
		$sheet[$s]->getStyle('A2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('B2', u8('Ключ квоты'));
		$sheet[$s]->getStyle('B2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('C2', u8('Квота'));
		$sheet[$s]->getStyle('C2')->getFont()->setBold(true);
		$sheet[$s]->setCellValue('D2', u8('Выполнено'));
		$sheet[$s]->getStyle('D2')->getFont()->setBold(true);		
		$sheet[$s]->getStyle('A2:D2')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}
	$sheet[$s]->getStyle('A'.($i+2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$sheet[$s]->setCellValue('A'.($i+2), u8(OCIResult($q,"TEXT_NAME")));
	$sheet[$s]->getStyle('A'.($i+2))->getFont()->setBold(true);
	$sheet[$s]->getStyle('B'.($i+2))->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	$sheet[$s]->setCellValue('B'.($i+2), u8(OCIResult($q,"VALUE")));

	$sheet[$s]->setCellValue('C'.($i+2), u8(OCIResult($q,"QST_IDX_QUOTE")));
	$sheet[$s]->getStyle('C'.($i+2))->getFont()->setBold(true);
	$sheet[$s]->getStyle('C'.($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$sheet[$s]->getStyle('C'.($i+2))->getProtection()->setLocked( PHPExcel_Style_Protection::PROTECTION_UNPROTECTED );

	$sheet[$s]->setCellValue('D'.($i+2), u8(OCIResult($q,"QST_IDX_NORM")));
	$sheet[$s]->getStyle('D'.($i+2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$sheet[$s]->getStyle('A'.($i+2).':D'.($i+2))->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	
	if($old_name<>OCIResult($q,"TEXT_NAME")) $sheet[$s]->getStyle('A'.($i+2).':D'.($i+2))->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	$old_name=OCIResult($q,"TEXT_NAME");
}

//независимые по вопросам==конец================================================================


foreach($sheet as $s => $fuck) {
	$highcol=$sheet[$s]->getHighestColumn();
	$highrow=$sheet[$s]->getHighestRow();
	if(cp($sheet[$s]->getTitle())=="Исходные поля") {
		$sheet[$s]->setAutoFilter('A2:'.$highcol.'2');
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);		
	}
	if(substr(cp($sheet[$s]->getTitle()),0,7)=="Уровень") {
		$sheet[$s]->setAutoFilter('B2:'.$highcol.'2');
		$sheet[$s]->getStyle('B3:'.$highcol.$highrow)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$sheet[$s]->getColumnDimension('A')->setVisible(false);			
	}	
	if(cp($sheet[$s]->getTitle())=="Независимые по исх.") {
		$sheet[$s]->setAutoFilter('A2:'.$highcol.'2');
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);		
	}
	if(cp($sheet[$s]->getTitle())=="Независимые по вопросам") {
		$sheet[$s]->setAutoFilter('A2:'.$highcol.'2');
		$sheet[$s]->getStyle('A3:'.$highcol.$highrow)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
	}			
	//устанавливаем автоширину столбцов на каждом листе
	for($i = 'A'; $i <= $highcol; $i++) {
		$sheet[$s]->getColumnDimension($i)->setAutoSize(TRUE);
		$sheet[$s]->freezePane('A3');
	}
}

$objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename=Квоты('.$_SESSION['project']['name'].').xlsx');
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

//==================================================================================================================
?>

