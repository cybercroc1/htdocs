<?php

$columns=array();
if(isset($common_itog)) unset($common_itog);
if(isset($group_itog)) unset($group_itog);

$c=1;
$columns[$c]['full_name']="Источник рекламы (Авто)";
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
$columns[$c]['group_type']='sum';
$columns[$c]['hidden']="y";

$c++;
$columns[$c]['full_name']="Принято ".chr(10)."обращений";
$columns[$c]['width']=12;
$columns[$c]['name']="ORDER_COUNT";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';

$c++;
$columns[$c]['full_name']="Пришло ".chr(10)."из принятых";
$columns[$c]['width']=12;
$columns[$c]['name']="VISIT_OF_ORDERS";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма планов" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name'] = "Сумма планов 2500+" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PLAN_SUM_OF_ORDERS_2500_PLUS";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['hidden'] = "y";
    $columns[$c]['group_type'] = 'sum';

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
$columns[$c]['group_type']='sum';

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Сумма проплат" . chr(10) . "из принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAYMENT_SUM_OF_ORDERS";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['group_type'] = 'sum';

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

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
    $c++;
    $columns[$c]['full_name'] = "Оплата " . chr(10) . "обращений";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_ORDER";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name'] = "Оплата " . chr(10) . "принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "PAY_VISIT";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name'] = "Доход " . chr(10) . "от принятых";
    $columns[$c]['width'] = 12;
    $columns[$c]['name'] = "DOHOD_VISIT";
    $columns[$c]['format'] = "#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name']="Пополнение ".chr(10)."за период";
    $columns[$c]['width']=12;
    $columns[$c]['name']="PAY_BALANCE";
    $columns[$c]['format']="#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name']="Пополнение ".chr(10)."за все время";
    $columns[$c]['width']=12;
    $columns[$c]['name']="PAY_BALANCE_ALL";
    $columns[$c]['format']="#,##0";
    $columns[$c]['group_type'] = 'sum';

    $c++;
    $columns[$c]['full_name']="Текущий ".chr(10)."баланс";
    $columns[$c]['width']=12;
    $columns[$c]['name']="BALANCE";
    $columns[$c]['format']="#,##0";
    $columns[$c]['group_type'] = 'sum';
}

$snum=2;

$sheet[$snum]=$objPHPExcel->CreateSheet();
$objPHPExcel->setActiveSheetIndex($snum-1);
$sheet[$snum]=$objPHPExcel->getActiveSheet();
$sheet[$snum]->setTitle(u8("По дате обр"));

//размер шрифта по умолчанию
$sheet[$snum]->getDefaultStyle()->getFont()->setSize(12); 

//
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

$rownum=1;
$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
$sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (По дате обращения)'));
$sheet[$snum]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

$rownum=2;
$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
$sheet[$snum]->setCellValue($coord, u8($services_text." ".$period_text));
$sheet[$snum]->getStyle($coord)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

$rownum=3;
foreach($columns as $colnum=>$null) {
	$colindex=$colnum-1;
	$colletter=PHPExcel_Cell::stringFromColumnIndex($colindex);
	$coord=$sheet[$snum]->getCellByColumnAndRow($colindex,$rownum)->getCoordinate();
	$sheet[$snum]->getColumnDimension($colletter)->setWidth($columns[$colnum]['width']);
	$sheet[$snum]->setCellValue($coord, u8($columns[$colnum]['full_name']));
}

$highcol=$sheet[$snum]->getHighestColumn();
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->applyFromArray($styleArray);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getFont()->setBold(true)->setSize(11);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setWrapText(true);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$sheet[$snum]->mergeCells('A1:'.$highcol.'1');
$sheet[$snum]->mergeCells('A2:'.$highcol.'2');

$sheet[$snum]->getRowDimension(3)->setRowHeight(45); // для заголовка

$sheet[$snum]->freezePane('B4');

//сортировка массива
asort($result_sources,SORT_STRING);
$grp_count=0;
//данные
while(current($result_sources)) {
	$res_key=key($result_sources);
	$grp_count++;

	$tmp_grp_name=substr($result_sources[$res_key],0,4);
	
	$rownum++;
	$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->applyFromArray($styleArray);
	foreach($columns as $colnum => $null) {
		//подсчет итогов
		if(isset($columns[$colnum]['group_type'])) {
			if(!isset($group_itog[$columns[$colnum]['name']])) $group_itog[$columns[$colnum]['name']]=0;
			if($columns[$colnum]['group_type']=='sum') {
				if(isset($result_arr[$res_key][$columns[$colnum]['name']])) {
					$group_itog[$columns[$colnum]['name']]+=$result_arr[$res_key][$columns[$colnum]['name']];
				}
			}
			if(!isset($common_itog[$columns[$colnum]['name']])) $common_itog[$columns[$colnum]['name']]=0;
			if($columns[$colnum]['group_type']=='sum') {
				if(isset($result_arr[$res_key][$columns[$colnum]['name']])) {
					$common_itog[$columns[$colnum]['name']]+=$result_arr[$res_key][$columns[$colnum]['name']];
				}
			}
		}
		
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
			
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}
		else if($columns[$colnum]['name']=='PAY_BY_ORDER_OF_ORDERS') {
			
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS']))  
				$val=round($result_arr[$res_key]['PAYMENT_SUM_OF_ORDERS'] / $result_arr[$res_key]['ORDER_COUNT']);
			else $val='';
			
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
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
            if (isset($result_arr[$res_key]['COST_ORDER']) && isset($result_arr[$res_key]['ORDER_COUNT'])) {
            	$val=$result_arr[$res_key]['COST_ORDER']*$result_arr[$res_key]['ORDER_COUNT'];
                if (isset($group_itog['PAY_ORDER'])) $group_itog['PAY_ORDER'] += $val;
                else  $group_itog['PAY_ORDER'] = 0;
                if (isset($common_itog['PAY_ORDER'])) $common_itog['PAY_ORDER'] += $val;
                else  $common_itog['PAY_ORDER'] = 0;
            }
            else $val='';
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
        else if($columns[$colnum]['name']=='PAY_VISIT') {
            if (isset($result_arr[$res_key]['COST_VISIT']) && isset($result_arr[$res_key]['VISIT_OF_ORDERS'])) {
            	$val=$result_arr[$res_key]['COST_VISIT']*$result_arr[$res_key]['VISIT_OF_ORDERS'];
                if (isset($group_itog['PAY_VISIT'])) $group_itog['PAY_VISIT'] += $val;
                else  $group_itog['PAY_VISIT'] = 0;
                if (isset($common_itog['PAY_VISIT'])) $common_itog['PAY_VISIT'] += $val;
                else  $common_itog['PAY_VISIT'] = 0;
            }
            else $val='';
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
            if (isset($group_itog['DOHOD_VISIT'])) $group_itog['DOHOD_VISIT'] += $val;
            else $group_itog['DOHOD_VISIT'] = 0;
            if (isset($common_itog['DOHOD_VISIT'])) $common_itog['DOHOD_VISIT'] += $val;
            else  $common_itog['DOHOD_VISIT'] = 0;
            $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
        }
	}

	//ГРУППОВЫЕ ИТОГИ
	if(substr(next($result_sources),0,4)<>$tmp_grp_name) {
		if($grp_count>1) $rownum++;
		
		foreach($columns as $colnum => $null) {
			$coord=$sheet[$snum]->getCellByColumnAndRow($colnum-1,$rownum)->getCoordinate();
			//числовой формат
			if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));	
			
			if(isset($columns[$colnum]['group_type'])) {
				//данные из результатов запроса
				if(isset($group_itog[$columns[$colnum]['name']])) {
					$sheet[$snum]->setCellValue($coord,u8($group_itog[$columns[$colnum]['name']]));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
				}
			}
			//кастомные поля, которых нет в запросе
			else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_OF_ORDERS') {
					
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PLAN_SUM_OF_ORDERS']))  
					$val=round($group_itog['PLAN_SUM_OF_ORDERS'] / $group_itog['ORDER_COUNT']);
				else $val='';
					
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PAY_BY_ORDER_OF_ORDERS') {
					
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PAYMENT_SUM_OF_ORDERS']))  
					$val=round($group_itog['PAYMENT_SUM_OF_ORDERS'] / $group_itog['ORDER_COUNT']);
				else $val='';
					
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_OF_ORDERS') {
				if(isset($group_itog['PLAN_SUM_OF_ORDERS']) and $group_itog['PLAN_SUM_OF_ORDERS']>0 and isset($group_itog['PAYMENT_SUM_OF_ORDERS']))  
					$val=round(($group_itog['PAYMENT_SUM_OF_ORDERS'] / $group_itog['PLAN_SUM_OF_ORDERS']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}				
			else if($columns[$colnum]['name']=='PRC_VISITED_OF_ORDERS') {
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['VISIT_OF_ORDERS']))  
					$val=round(($group_itog['VISIT_OF_ORDERS'] / $group_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}		
			else if($columns[$colnum]['name']=='PRC_PAYED_OF_ORDERS') {
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PAYED_OF_ORDERS']))  
					$val=round(($group_itog['PAYED_OF_ORDERS'] / $group_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
            /*else if($columns[$colnum]['name']=='PAY_ORDER') {
                $val=$group_itog['PAY_ORDER'];
                /*if (isset($group_itog['COST_ORDER']) && isset($group_itog['ORDER_COUNT']))
                    $val=$group_itog['COST_ORDER']*$group_itog['ORDER_COUNT'];
                else $val='';*
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }
            else if($columns[$colnum]['name']=='PAY_VISIT') {
                $val=$group_itog['PAY_VISIT'];
                /*if (isset($group_itog['COST_VISIT']) && isset($group_itog['VISIT_OF_ORDERS']))
                    $val=$group_itog['COST_VISIT']*$group_itog['VISIT_OF_ORDERS'];
                else $val='';*
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }
            else if($columns[$colnum]['name']=='DOHOD_VISIT') {
                if (isset($group_itog['PAYMENT_SUM_OF_ORDERS']))
                    $val = $group_itog['PAYMENT_SUM_OF_ORDERS'];
                else $val = 0;

                if (isset($group_itog['COST_ORDER']) && isset($group_itog['ORDER_COUNT'])) // вычитаем одно
                    $val -= $group_itog['COST_ORDER']*$group_itog['ORDER_COUNT'];
                if (isset($group_itog['COST_VISIT']) && isset($group_itog['VISIT_OF_ORDERS'])) // и другое
                    $val -= $group_itog['COST_VISIT']*$group_itog['VISIT_OF_ORDERS'];
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }*/
		}
		$grp_count=0;
		unset($group_itog);
		$rownum++; //добавляем пустую строку	
	}
}


//ИТОГИ
		$rownum++;
		
		$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
		$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
		$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet[$snum]->setCellValue($coord,u8("ИТОГО:"));
		$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
		
		$coord=$sheet[$snum]->getCellByColumnAndRow(1,$rownum)->getCoordinate();
		$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
		
		foreach($columns as $colnum => $null) {
			$coord=$sheet[$snum]->getCellByColumnAndRow($colnum-1,$rownum)->getCoordinate();
			//числовой формат
			if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));
			
			if(isset($columns[$colnum]['group_type'])) {
				//данные из результатов запроса
				if(isset($common_itog[$columns[$colnum]['name']])) {
					$sheet[$snum]->setCellValue($coord,u8($common_itog[$columns[$colnum]['name']]));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
				}
			}
			//кастомные поля, которых нет в запросе
			else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_OF_ORDERS') {
					
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PLAN_SUM_OF_ORDERS']))  
					$val=round($common_itog['PLAN_SUM_OF_ORDERS'] / $common_itog['ORDER_COUNT']);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PAY_BY_ORDER_OF_ORDERS') {
					
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PAYMENT_SUM_OF_ORDERS']))  
					$val=round($common_itog['PAYMENT_SUM_OF_ORDERS'] / $common_itog['ORDER_COUNT']);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_OF_ORDERS') {
				if(isset($common_itog['PLAN_SUM_OF_ORDERS']) and $common_itog['PLAN_SUM_OF_ORDERS']>0 and isset($common_itog['PAYMENT_SUM_OF_ORDERS']))  
					$val=round(($common_itog['PAYMENT_SUM_OF_ORDERS'] / $common_itog['PLAN_SUM_OF_ORDERS']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}	
			else if($columns[$colnum]['name']=='PRC_VISITED_OF_ORDERS') {
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['VISIT_OF_ORDERS']))  
					$val=round(($common_itog['VISIT_OF_ORDERS'] / $common_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}		
			else if($columns[$colnum]['name']=='PRC_PAYED_OF_ORDERS') {
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PAYED_OF_ORDERS']))  
					$val=round(($common_itog['PAYED_OF_ORDERS'] / $common_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
            /*else if($columns[$colnum]['name']=='PAY_ORDER') {
                $val=$common_itog['PAY_ORDER'];
                /*if (isset($common_itog['COST_ORDER']) && isset($common_itog['ORDER_COUNT']))
                    $val=$common_itog['COST_ORDER']*$common_itog['ORDER_COUNT'];
                else $val='';*
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }
            else if($columns[$colnum]['name']=='PAY_VISIT') {
                $val=$common_itog['PAY_VISIT'];
                /*if (isset($common_itog['COST_VISIT']) && isset($common_itog['VISIT_OF_ORDERS']))
                    $val=$common_itog['COST_VISIT']*$common_itog['VISIT_OF_ORDERS'];
                else $val='';*
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }
            else if($columns[$colnum]['name']=='DOHOD_VISIT') {
                if (isset($group_itog['PAYMENT_SUM_OF_ORDERS']))
                    $val = $group_itog['PAYMENT_SUM_OF_ORDERS'];
                else $val = 0;

                if (isset($common_itog['COST_ORDER']) && isset($common_itog['ORDER_COUNT'])) // вычитаем одно
                    $val -= $common_itog['COST_ORDER']*$common_itog['ORDER_COUNT'];
                if (isset($common_itog['COST_VISIT']) && isset($common_itog['VISIT_OF_ORDERS'])) // и другое
                    $val -= $common_itog['COST_VISIT']*$common_itog['VISIT_OF_ORDERS'];
                $sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
            }*/
		}

$sheet[$snum]->setAutoFilter('A3:'.$highcol.'3');		
		
//настройки печати листа
//альбомная
$sheet[$snum]->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);	
//размер бумаги
$sheet[$snum]->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
//разместить по ширине на одну страницу
$sheet[$snum]->getPageSetup()->setFitToWidth(1);
//не ограничивать количество листов по высоте	
$sheet[$snum]->getPageSetup()->setFitToHeight(0);
//повторять на каждой странице при печати строку с заголовками полей
$sheet[$snum]->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3,3);

?>
