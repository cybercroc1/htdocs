<?php

$columns=array();
if(isset($common_itog)) unset($common_itog);
if(isset($group_itog)) unset($group_itog);

$c=1;
$columns[$c]['full_name']="�������� ������� (����)";
$columns[$c]['width']=50;
$columns[$c]['name']="SOURCE_AUTO_NAME";

$c++;
$columns[$c]['full_name']="��� ".chr(10)."���������";
$columns[$c]['width']=12;
$columns[$c]['name']="SOURCE_TYPE_NAME";

$c++;
$columns[$c]['full_name']="����� ".chr(10)."���������";
$columns[$c]['width']=12;
$columns[$c]['name']="COUNT_ALL";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';
$columns[$c]['hidden']="y";

$c++;
$columns[$c]['full_name']="������� ".chr(10)."���������";
$columns[$c]['width']=12;
$columns[$c]['name']="ORDER_COUNT";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';

$c++;
$columns[$c]['full_name']="������ ".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="VISIT_BY_PER";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="����� ������".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PLAN_SUM_BY_PER";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';
}

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="����� ������ 2500+".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PLAN_SUM_BY_PER_2500_PLUS";
$columns[$c]['format']="#,##0";
$columns[$c]['hidden']="y";
$columns[$c]['group_type']='sum';
}

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="��������� �������".chr(10)." � ��������� �� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="WAIT_PAY_BY_ORDER_BY_PER";
$columns[$c]['format']="#,##0";
}

$c++;
$columns[$c]['full_name']="�������� ".chr(10)." �� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PAYED_BY_PER";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="����� ������� ".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PAYMENT_SUM_BY_PER";
$columns[$c]['format']="#,##0";
$columns[$c]['group_type']='sum';
}

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="% �������� ������".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PRC_CLOSE_PLAN_BY_PER";
$columns[$c]['format']="#,##0.00";
}

if($_SESSION['user_role']==1 or isset($_SESSION['access']['data_acc']['CAN_FINANCE'])) {
$c++;
$columns[$c]['full_name']="������� � ���������".chr(10)."�� ������";
$columns[$c]['width']=12;
$columns[$c]['name']="PAY_BY_ORDER_BY_PER";
$columns[$c]['format']="#,##0";
}

$c++;
$columns[$c]['full_name']="% ��������� ".chr(10)."�� ��������";
$columns[$c]['width']=12;
$columns[$c]['name']="PRC_VISITED_BY_PER";
$columns[$c]['format']="#,##0.00";

$c++;
$columns[$c]['full_name']="% ���������� ".chr(10)."�� ��������";
$columns[$c]['width']=12;
$columns[$c]['name']="PRC_PAYED_BY_PER";
$columns[$c]['format']="#,##0.00";

$snum=3;

$sheet[$snum]=$objPHPExcel->CreateSheet();
$objPHPExcel->setActiveSheetIndex($snum-1);
$sheet[$snum]=$objPHPExcel->getActiveSheet();
$sheet[$snum]->setTitle(u8("�� ������"));

//������ ������ �� ���������
$sheet[$snum]->getDefaultStyle()->getFont()->setSize(12); 

//
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

$rownum=1;
$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
$sheet[$snum]->setCellValue($coord, u8('������������� ���������� ������� (�� ������)'));
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

$sheet[$snum]->getRowDimension(3)->setRowHeight(45); // ��� ���������

$sheet[$snum]->freezePane('B4');

//���������� �������
asort($result_sources,SORT_STRING);
$grp_count=0;
//������
while(current($result_sources)) {
	$res_key=key($result_sources);
	$grp_count++;

	$tmp_grp_name=substr($result_sources[$res_key],0,4);
	
	$rownum++;
	$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->applyFromArray($styleArray);
	foreach($columns as $colnum => $null) {
		
		//������� ������
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
		
		//�������� ������
		if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));		
		//������� �������
		if(isset($columns[$colnum]['hidden'])) {
			$sheet[$snum]->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($colnum-1))->setVisible(FALSE);
		}	
		//������ �� ����������� �������
		if(isset($result_arr[$res_key][$columns[$colnum]['name']])) {
			$sheet[$snum]->setCellValue($coord,u8($result_arr[$res_key][$columns[$colnum]['name']]));	
		}
		//��������� ����, ������� ��� � �������
		else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_BY_PER') {
			
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PLAN_SUM_BY_PER']))  
				$val=round($result_arr[$res_key]['PLAN_SUM_BY_PER'] / $result_arr[$res_key]['ORDER_COUNT']);
			else $val='';
			
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}		
		else if($columns[$colnum]['name']=='PAY_BY_ORDER_BY_PER') {
			
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PAYMENT_SUM_BY_PER']))  
				$val=round($result_arr[$res_key]['PAYMENT_SUM_BY_PER'] / $result_arr[$res_key]['ORDER_COUNT']);
			else $val='';
			
			$sheet[$snum]->setCellValue($coord,u8($val));						
		}
		else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_BY_PER') {
			if(isset($result_arr[$res_key]['PLAN_SUM_BY_PER']) and $result_arr[$res_key]['PLAN_SUM_BY_PER']>0 and isset($result_arr[$res_key]['PAYMENT_SUM_BY_PER']))  
				$val=round(($result_arr[$res_key]['PAYMENT_SUM_BY_PER'] / $result_arr[$res_key]['PLAN_SUM_BY_PER']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}			
		else if($columns[$colnum]['name']=='PRC_VISITED_BY_PER') {
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['VISIT_BY_PER']))  
				$val=round(($result_arr[$res_key]['VISIT_BY_PER'] / $result_arr[$res_key]['ORDER_COUNT']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}		
		else if($columns[$colnum]['name']=='PRC_PAYED_BY_PER') {
			if(isset($result_arr[$res_key]['ORDER_COUNT']) and $result_arr[$res_key]['ORDER_COUNT']>0 and isset($result_arr[$res_key]['PAYED_BY_PER']))  
				$val=round(($result_arr[$res_key]['PAYED_BY_PER'] / $result_arr[$res_key]['ORDER_COUNT']) * 100,2);
			else $val='';
			$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));						
		}		
	}
	//��������� �����
	if(substr(next($result_sources),0,4)<>$tmp_grp_name) {
		if($grp_count>1) $rownum++;
		
		foreach($columns as $colnum => $null) {
			$coord=$sheet[$snum]->getCellByColumnAndRow($colnum-1,$rownum)->getCoordinate();
			//�������� ������
			if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));	
			
			if(isset($columns[$colnum]['group_type'])) {
				//������ �� ����������� �������
				if(isset($group_itog[$columns[$colnum]['name']])) {
					$sheet[$snum]->setCellValue($coord,u8($group_itog[$columns[$colnum]['name']]));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
				}
			}
			//��������� ����, ������� ��� � �������
			else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_BY_PER') {
					
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PLAN_SUM_BY_PER']))  
					$val=round($group_itog['PLAN_SUM_BY_PER'] / $group_itog['ORDER_COUNT']);
				else $val='';
					
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PAY_BY_ORDER_BY_PER') {
					
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PAYMENT_SUM_BY_PER']))  
					$val=round($group_itog['PAYMENT_SUM_BY_PER'] / $group_itog['ORDER_COUNT']);
				else $val='';
					
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_BY_PER') {
				if(isset($group_itog['PLAN_SUM_BY_PER']) and $group_itog['PLAN_SUM_BY_PER']>0 and isset($group_itog['PAYMENT_SUM_BY_PER']))  
					$val=round(($group_itog['PAYMENT_SUM_BY_PER'] / $group_itog['PLAN_SUM_BY_PER']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}			
			else if($columns[$colnum]['name']=='PRC_VISITED_BY_PER') {
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['VISIT_BY_PER']))  
					$val=round(($group_itog['VISIT_BY_PER'] / $group_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}		
			else if($columns[$colnum]['name']=='PRC_PAYED_BY_PER') {
				if(isset($group_itog['ORDER_COUNT']) and $group_itog['ORDER_COUNT']>0 and isset($group_itog['PAYED_BY_PER']))  
					$val=round(($group_itog['PAYED_BY_PER'] / $group_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}			
		}
		$grp_count=0;
		unset($group_itog);
		$rownum++; //��������� ������ ������	
	}
}


//�����
		$rownum++;
		
		$coord=$sheet[$snum]->getCellByColumnAndRow(0,$rownum)->getCoordinate();
		$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
		$sheet[$snum]->getStyle('A'.$rownum.':'.$highcol.$rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$sheet[$snum]->setCellValue($coord,u8("�����:"));
		$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
		
		$coord=$sheet[$snum]->getCellByColumnAndRow(1,$rownum)->getCoordinate();
		$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
		
		foreach($columns as $colnum => $null) {
			$coord=$sheet[$snum]->getCellByColumnAndRow($colnum-1,$rownum)->getCoordinate();
			//�������� ������
			if(isset($columns[$colnum]['format'])) $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));	
			
			if(isset($columns[$colnum]['group_type'])) {
				//������ �� ����������� �������
				if(isset($common_itog[$columns[$colnum]['name']])) {
					$sheet[$snum]->setCellValue($coord,u8($common_itog[$columns[$colnum]['name']]));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
				}
			}
			//��������� ����, ������� ��� � �������
			else if($columns[$colnum]['name']=='WAIT_PAY_BY_ORDER_BY_PER') {
					
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PLAN_SUM_BY_PER']))  
					$val=round($common_itog['PLAN_SUM_BY_PER'] / $common_itog['ORDER_COUNT']);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}		
			else if($columns[$colnum]['name']=='PAY_BY_ORDER_BY_PER') {
					
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PAYMENT_SUM_BY_PER']))  
					$val=round($common_itog['PAYMENT_SUM_BY_PER'] / $common_itog['ORDER_COUNT']);
				else $val='';
					
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}
			else if($columns[$colnum]['name']=='PRC_CLOSE_PLAN_BY_PER') {
				if(isset($common_itog['PLAN_SUM_BY_PER']) and $common_itog['PLAN_SUM_BY_PER']>0 and isset($common_itog['PAYMENT_SUM_BY_PER']))  
					$val=round(($common_itog['PAYMENT_SUM_BY_PER'] / $common_itog['PLAN_SUM_BY_PER']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}			
			else if($columns[$colnum]['name']=='PRC_VISITED_BY_PER') {
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['VISIT_BY_PER']))  
					$val=round(($common_itog['VISIT_BY_PER'] / $common_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}		
			else if($columns[$colnum]['name']=='PRC_PAYED_BY_PER') {
				if(isset($common_itog['ORDER_COUNT']) and $common_itog['ORDER_COUNT']>0 and isset($common_itog['PAYED_BY_PER']))  
					$val=round(($common_itog['PAYED_BY_PER'] / $common_itog['ORDER_COUNT']) * 100,2);
				else $val='';
				$sheet[$snum]->setCellValue($coord,u8(str_replace(",",".",$val)));	
					$sheet[$snum]->getStyle($coord)->applyFromArray($styleArray);
					$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
			}			
		}
$sheet[$snum]->setAutoFilter('A3:'.$highcol.'3');		
		
//��������� ������ �����
//���������
$sheet[$snum]->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);	
//������ ������
$sheet[$snum]->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
//���������� �� ������ �� ���� ��������
$sheet[$snum]->getPageSetup()->setFitToWidth(1);
//�� ������������ ���������� ������ �� ������	
$sheet[$snum]->getPageSetup()->setFitToHeight(0);
//��������� �� ������ �������� ��� ������ ������ � ����������� �����
$sheet[$snum]->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3,3);

?>