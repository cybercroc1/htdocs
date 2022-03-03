<?php

$columns=array();

$c=1;
$columns[$c]['full_name']="Источник рекламы (Авто)".chr(10).$_SESSION['reports']['start_date']." - ".$_SESSION['reports']['end_date'].chr(10).$services_text;
$columns[$c]['width']=50;
$columns[$c]['name']="SOURCE_AUTO_NAME";

$c++;
$columns[$c]['full_name']="Тип ".chr(10)."источника";
$columns[$c]['width']=12;
$columns[$c]['name']="SOURCE_TYPE_NAME";

$c++;
$columns[$c]['full_name']="Стоимость ".chr(10)."заявки";
$columns[$c]['width']=12;
$columns[$c]['name']="COST_COST";
$columns[$c]['format']="#,##0";
//$columns[$c]['hidden']="y";

$c++;
$columns[$c]['full_name']="Всего ".chr(10)."обращений";
$columns[$c]['width']=12;
$columns[$c]['name']="COUNT_ALL";
$columns[$c]['format']="#,##0";
$columns[$c]['hidden']="y";

$c++;
$columns[$c]['full_name']="Принято ".chr(10)."обращений";
$columns[$c]['width']=12;
$columns[$c]['name']="ORDER_COUNT";
$columns[$c]['format']="#,##0";

$c++;
$columns[$c]['full_name']="Пришло ".chr(10)."из принятых";
$columns[$c]['width']=12;
$columns[$c]['name']="VISIT_OF_ORDERS";
$columns[$c]['format']="#,##0";

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма планов" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Сумма планов 2500+" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_OF_ORDERS_2500_PLUS";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['hidden'] = "y";

    $c++;
    $columns[$c]['full_name'] = "Ожидаемая выручка" . chr(10) . " с обращения";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "WAIT_PAY_BY_ORDER_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";
}

$c++;
$columns[$c]['full_name']="Оплачено ".chr(10)."из принятых";
$columns[$c]['width']=12;
$columns[$c]['name']="PAYED_OF_ORDERS";
$columns[$c]['format']="#,##0";

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма проплат" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAYMENT_SUM_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "% закрытия" . chr(10) . "планов";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PRC_CLOSE_PLAN_OF_ORDERS";
    $columns[$c]['format'] = "#,##0.00";

    $c++;
    $columns[$c]['full_name'] = "Выручка с " . chr(10) . "обращения";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_BY_ORDER_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";
}

$c++;
$columns[$c]['full_name']="% пришедших ".chr(10)."от принятых";
$columns[$c]['width']=12;
$columns[$c]['name']="PRC_VISITED_OF_ORDERS";
$columns[$c]['format']="#,##0.00";

$c++;
$columns[$c]['full_name']="% оплаченных ".chr(10)."от принятых";
$columns[$c]['width']=12;
$columns[$c]['name']="PRC_PAYED_OF_ORDERS";
$columns[$c]['format']="#,##0.00";

$c++;
$columns[$c]['full_name']="Пришло ".chr(10)."за период";
$columns[$c]['width']=12;
$columns[$c]['name']="VISIT_BY_PER";
$columns[$c]['format']="#,##0";

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма планов" . chr(10) . "за период";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_BY_PER";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Сумма планов 2500+" . chr(10) . "за период";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_BY_PER_2500_PLUS";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['hidden'] = "y";
}

$c++;
$columns[$c]['full_name']="Оплачено ".chr(10)." за период";
$columns[$c]['width']=12;
$columns[$c]['name']="PAYED_BY_PER";
$columns[$c]['format']="#,##0";

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма проплат " . chr(10) . "за период";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAYMENT_SUM_BY_PER";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Оплата " . chr(10) . "обращений";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_ORDER";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Оплата " . chr(10) . "за период";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_VISIT_PER";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Оплата " . chr(10) . "принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_VISIT";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Доход " . chr(10) . "за период";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "DOHOD_PER";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name'] = "Доход " . chr(10) . "от принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "DOHOD_VISIT";
    $columns[$c]['format'] = "#,##0";

    $c++;
    $columns[$c]['full_name']="Пополнение ".chr(10)."баланса";
    $columns[$c]['width']=12;
    $columns[$c]['name']="PAY_BALANCE";
    $columns[$c]['format']="#,##0";
    /*$c++;
    $columns[$c]['full_name']="Выплаты ".chr(10)."за заявки";
    $columns[$c]['width']=12;
    $columns[$c]['name']="PAY_SUMM";
    $columns[$c]['format']="#,##0";*/
}

$snum=1;
$objPHPExcel->setActiveSheetIndex($snum-1);
$sheet[$snum]=$objPHPExcel->getActiveSheet();
$sheet[$snum]->setTitle(u8("Общий"));

//
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

$rownum=1;
/*
$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
$sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы'));
$sheet[$snum]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

$rownum=2;
$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
$sheet[$snum]->setCellValue($coord, u8($services_text." ".$period_text));
$sheet[$snum]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

$rownum=3;
*/
foreach($columns as $colnum=>$null) {
	$colindex=$colnum-1;
	$colletter=PHPExcel_Cell::stringFromColumnIndex($colindex);
	$coord=$sheet[$snum]->getCellByColumnAndRow($colindex,$rownum)->getCoordinate();
	$sheet[$snum]->getColumnDimension($colletter)->setWidth($columns[$colnum]['width']);
	$sheet[$snum]->setCellValue($coord, u8($columns[$colnum]['full_name']));
}

$highcol=$sheet[$snum]->getHighestColumn();
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->applyFromArray($styleArray);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getFont()->setBold(true);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setWrapText(true);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//$sheet[$snum]->mergeCells('A1:'.$highcol.'1');
//$sheet[$snum]->mergeCells('A2:'.$highcol.'2');

$sheet[$snum]->getRowDimension(1)->setRowHeight(45); // для заголовка

$sheet[$snum]->freezePane('C2');

//сортировка массива
if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
arsort($result_payment_sum,SORT_NUMERIC);
}

//данные
foreach($result_payment_sum as $res_key => $null) {
	$rownum++;
	$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->applyFromArray($styleArray);
	foreach($columns as $colnum => $null) {
		$coord=$sheet[$snum]->getCellByColumnAndRow($colnum-1,$rownum)->getCoordinate();
		
		if($columns[$colnum]['name']=='SOURCE_TYPE_NAME') $sheet[$snum]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		
		//числовой формат
		if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));		
		//Скрытый столбец
		if(isset($columns[$colnum]['hidden'])) {
			$sheet[$snum]->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($colnum-1))->setVisible(FALSE);
		}		
		//данные из результатов запроса
		if(isset($result_arr[$res_key][$columns[$colnum]['name']])) {
			$sheet[$snum]->setCellValue($coord,u8($result_arr[$res_key][$columns[$colnum]['name']]));	
		}
		//кастомные поля, которых нет в запросе
		else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_OF_ORDERS') {
			
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PLAN_SUM_OF_ORDERS']))  
				$val=round($result_arr[$res_key]['PLAN_SUM_OF_ORDERS'] / $result_arr[$res_key]['ORDER_COUNT']);
			else $val='';
			
			$sheet[$snum]->setCellValue($coord,u8($val));						
		}
		else if($columns[$colnum]['name']=='PAY_BY_ORDER_OF_ORDERS') {
			
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS']))  
				$val=round($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS'] / $result_arr[$res_key]['ORDER_COUNT']);
			else $val='';
			
			$sheet[$snum]->setCellValue($coord,u8($val));						
		}
		else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_OF_ORDERS') {
			if(isset($result_arr[$res_key]['PLAN_SUM_OF_ORDERS']) and $result_arr[$res_key]['PLAN_SUM_OF_ORDERS']>0 and isset($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS']))  
				$val=round(($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS'] / $result_arr[$res_key]['PLAN_SUM_OF_ORDERS']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}
		else if($columns[$colnum]['name']=='PRC_VISITED_OF_ORDERS') {
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['VISIT_OF_ORDERS']))  
				$val=round(($result_arr[$res_key]['VISIT_OF_ORDERS'] / $result_arr[$res_key]['ORDER_COUNT']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}		
		else if($columns[$colnum]['name']=='PRC_PAYED_OF_ORDERS') {
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PAYED_OF_ORDERS']))  
				$val=round(($result_arr[$res_key]['PAYED_OF_ORDERS'] / $result_arr[$res_key]['ORDER_COUNT']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}
        else if($columns[$colnum]['name']=='PAY_ORDER') {
            if (isset($result_arr[$res_key]['COST_ORDER']) && isset($result_arr[$res_key]['ORDER_COUNT']))
                $val=$result_arr[$res_key]['COST_ORDER']*$result_arr[$res_key]['ORDER_COUNT'];
            else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
        else if($columns[$colnum]['name']=='PAY_VISIT_PER') {
            if (isset($result_arr[$res_key]['COST_VISIT']) && isset($result_arr[$res_key]['VISIT_BY_PER']))
                $val=$result_arr[$res_key]['COST_VISIT']*$result_arr[$res_key]['VISIT_BY_PER'];
            else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
        else if($columns[$colnum]['name']=='PAY_VISIT') {
            if (isset($result_arr[$res_key]['COST_VISIT']) && isset($result_arr[$res_key]['VISIT_OF_ORDERS']))
                $val=$result_arr[$res_key]['COST_VISIT']*$result_arr[$res_key]['VISIT_OF_ORDERS'];
            else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
        else if($columns[$colnum]['name']=='DOHOD_PER') {
            if (isset($result_arr[$res_key]['PAYMENT_SUM_BY_PER']))
                $val = $result_arr[$res_key]['PAYMENT_SUM_BY_PER'];
            else $val = 0;

            if (isset($result_arr[$res_key]['COST_ORDER']) && isset($result_arr[$res_key]['ORDER_COUNT'])) // вычитаем одно
                $val -= $result_arr[$res_key]['COST_ORDER']*$result_arr[$res_key]['ORDER_COUNT'];
            if (isset($result_arr[$res_key]['COST_VISIT']) && isset($result_arr[$res_key]['VISIT_BY_PER'])) // и другое
                $val -= $result_arr[$res_key]['COST_VISIT']*$result_arr[$res_key]['VISIT_BY_PER'];
            //else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
        else if($columns[$colnum]['name']=='DOHOD_VISIT') {
            if (isset($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS']))
                $val = $result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS'];
            else $val = 0;

            if (isset($result_arr[$res_key]['COST_ORDER']) && isset($result_arr[$res_key]['ORDER_COUNT'])) // вычитаем одно
                $val -= $result_arr[$res_key]['COST_ORDER']*$result_arr[$res_key]['ORDER_COUNT'];
            if (isset($result_arr[$res_key]['COST_VISIT']) && isset($result_arr[$res_key]['VISIT_OF_ORDERS'])) // и другое
                $val -= $result_arr[$res_key]['COST_VISIT']*$result_arr[$res_key]['VISIT_OF_ORDERS'];
            //else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
		/*
		else if($columns[$colnum]['name']=='RESULT_PAYMENT_SUM') {
			$sheet[$snum]->setCellValue($coord,u8($result_payment_sum[$res_key]));						
		}	
		*/
	}
}
$sheet[$snum]->setAutoFilter('A1:'.$highcol.'1');
?>
