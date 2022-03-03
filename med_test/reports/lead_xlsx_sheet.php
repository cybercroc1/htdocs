<?php

$columns=array();

$c=1;
$columns[$c]['full_name']="Источник рекламы (Авто)".chr(10).$_SESSION['reports']['start_date']." - ".$_SESSION['reports']['end_date'].chr(10).$services_text;
$columns[$c]['width']=50;
$columns[$c]['name']="SOURCE_AUTO_NAME";


$from = new DateTime(date('Y-m-d',strtotime($_SESSION['reports']['start_date'])));
$to   = new DateTime(date('Y-m-d',strtotime($_SESSION['reports']['end_date'])));
$to->modify('+1 day');

$service_list = array(1=>'Стоматология', 2=>'Косметология', 3=>'Гинекология', 4=>'Пластика', 5=>'Трихология');

$period = new DatePeriod($from, new DateInterval('P1D'), $to);
$arrayOfDates = array_map(
    function($item){return $item->format('d.m.Y');},
    //function($item){return $item->format('d M');},
    iterator_to_array($period)
);
array_push($arrayOfDates,'Итого');
//var_dump($arrayOfDates); exit;
foreach ($arrayOfDates as $key => $day) {
    $c++;
    $columns[$c]['full_name'] = $day;
    $columns[$c]['width'] = 5;
    $columns[$c]['name'] = $day;
    $columns[$c]['rotation'] = 90;
    if (!strncmp('Итого', trim($day), 5))
        $columns[$c]['color'] = 'FF0000';
}

$snum=1;
$objPHPExcel->setActiveSheetIndex($snum-1);
$sheet[$snum]=$objPHPExcel->getActiveSheet();
//$sheet[$snum]->setTitle(u8("Общий"));
$sheet[$snum]->setTitle(u8(date('M Y',strtotime($_SESSION['reports']['start_date']))));

PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
$styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

//данные
$rownum = 1;
foreach ($columns as $colnum => $null) {
    $colindex = $colnum - 1;
    $colletter = PHPExcel_Cell::stringFromColumnIndex($colindex);
    $coord = $sheet[$snum]->getCellByColumnAndRow($colindex, $rownum)->getCoordinate();
    $sheet[$snum]->getColumnDimension($colletter)->setWidth($columns[$colnum]['width']);
    $sheet[$snum]->getStyle($coord)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ffffcc');
    if (isset($columns[$colnum]['rotation']))
        $sheet[$snum]->getStyle($coord)->getAlignment()->setTextRotation($columns[$colnum]['rotation']);
    if (isset($columns[$colnum]['color'])) {
        $phpColor = new PHPExcel_Style_Color();
        $phpColor->setRGB($columns[$colnum]['color']);
        $sheet[$snum]->getStyle($coord)->getFont()->setColor($phpColor);
    }
    $sheet[$snum]->setCellValue($coord, u8($columns[$colnum]['full_name']));
}

$highcol = $sheet[$snum]->getHighestColumn();
$sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->applyFromArray($styleArray);
$sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->getFont()->setBold(true);
$sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->getAlignment()->setWrapText(true);
$sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//$sheet[$snum]->getRowDimension(1)->setRowHeight(45); // для заголовка
$sheet[$snum]->freezePane('B2');

//данные
/*if(isset($common_itog)) unset($common_itog);
$common_itog = array();
$common_itog[2] = array();
$common_itog[4] = array();
$common_itog[5] = array();*/

foreach($result_arr as $key_prov => $res_prov) {
    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(18);
    $sheet[$snum]->setCellValue($coord, u8($key_prov));

    // Всего заявок
    $count_itog = array();
    $count_itog['Итого'] = 0;
    $result_work[$key_prov]['Итого']['IN_WORK'] = 0;

    //$sheet[$snum]->getStyle($coord)->getFont()->setSize(10);
    foreach ($res_prov as $res_key => $null) {
        $rownum++;
        $sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->applyFromArray($styleArray);
        $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8($res_key));
        foreach ($columns as $colnum => $null) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($colnum - 1, $rownum)->getCoordinate();
            //числовой формат
            if (isset($columns[$colnum]['format']))
                $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));
            //данные из результатов запроса
            $sheet[$snum]->getStyle($coord)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('EBF1DE');
            if (isset($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['COUNT_ALL'])) {
                $sheet[$snum]->setCellValue($coord, u8($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['COUNT_ALL']));

                if (isset($count_itog[$columns[$colnum]['name']]))
                    $count_itog[$columns[$colnum]['name']] += $result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['COUNT_ALL'];
                else $count_itog[$columns[$colnum]['name']] = $result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['COUNT_ALL'];
            }
        }
    }

    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(12);
    $sheet[$snum]->setCellValue($coord, u8('Всего поступило Lead'));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($count_itog[$day])) {
            if (!strncmp('Итого', trim($day), 5))
                $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            $sheet[$snum]->setCellValue($coord, u8($count_itog[$day]));
        }
    }

    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(12);
    $sheet[$snum]->setCellValue($coord, u8('В работе'));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($result_work[$key_prov][$day]['IN_WORK'])) {
            if (!strncmp('Итого', trim($day), 5))
                $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            else $result_work[$key_prov]['Итого']['IN_WORK'] += $result_work[$key_prov][$day]['IN_WORK'];
            $sheet[$snum]->setCellValue($coord, u8($result_work[$key_prov][$day]['IN_WORK']));
        }
    }

// Брак
    foreach ($res_prov as $res_key => $null) {
        $rownum++;
        $sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->applyFromArray($styleArray);
        $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8("Брак " . $res_key));
        foreach ($columns as $colnum => $null) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($colnum - 1, $rownum)->getCoordinate();
            //числовой формат
            if (isset($columns[$colnum]['format']))
                $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));
            //данные из результатов запроса
            $sheet[$snum]->getStyle($coord)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('E4DFEC');
            if (isset($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['BREAK_COUNT'])) {
                $sheet[$snum]->setCellValue($coord, u8($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['BREAK_COUNT']));
            }
        }
    }
// Зачот
    foreach ($res_prov as $res_key => $null) {
        $rownum++;
        $sheet[$snum]->getStyle('A' . $rownum . ':' . $highcol . $rownum)->applyFromArray($styleArray);
        $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8("Принято " . $res_key));
        foreach ($columns as $colnum => $null) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($colnum - 1, $rownum)->getCoordinate();
            //числовой формат
            if (isset($columns[$colnum]['format']))
                $sheet[$snum]->getStyle($coord)->getNumberFormat()->setFormatCode(u8($columns[$colnum]['format']));
            //данные из результатов запроса
            $sheet[$snum]->getStyle($coord)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('CDE1FF');
            if (isset($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['ORDER_COUNT'])) {
                $sheet[$snum]->setCellValue($coord, u8($result_arr[$key_prov][$res_key][$columns[$colnum]['name']]['ORDER_COUNT']));
            }
        }
    }

    // Итоги по поставщику
    foreach ($service_list as $item => $value) {
        if (!isset($result_serv[$key_prov][$item])) continue;
        $rownum++;
        $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
        $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
        $sheet[$snum]->getStyle($coord)->getFont()->setSize(12);
        $sheet[$snum]->setCellValue($coord, u8('Кол-во принятых Lead ('.$value.')'));
        $colnum = 1;
        $result_serv[$key_prov][$item]['Итого']['BY_SERV'] = 0;
        foreach ($arrayOfDates as $key => $day) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
            if (isset($result_serv[$key_prov][$item][$day]['BY_SERV'])) {
                if (strncmp('Итого',trim($day),5))
                    $result_serv[$key_prov][$item]['Итого']['BY_SERV'] += $result_serv[$key_prov][$item][$day]['BY_SERV'];
                else $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
                $sheet[$snum]->setCellValue($coord, u8($result_serv[$key_prov][$item][$day]['BY_SERV']));

                /*if (isset($common_itog[$item][$columns[$colnum]['name']]))
                    $common_itog[$item][$columns[$colnum]['name']] += $result_serv[$key_prov][$item][$day]['BY_SERV'];
                else $common_itog[$item][$columns[$colnum]['name']] = $result_serv[$key_prov][$item][$day]['BY_SERV'];*/
            }
        }
    }
    $rownum++; //добавляем пустую строку
}

// Итого зачот
$rownum++;
$coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
$sheet[$snum]->getStyle($coord)->getFont()->setSize(16);
$sheet[$snum]->setCellValue($coord, u8('Всего принято:'));
foreach ($service_list as $item => $value) {
    if (!isset($result_itog[$item])) continue;
    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
    $sheet[$snum]->setCellValue($coord, u8($value));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($result_itog[$item][$day]['ORDER_COUNT'])) {
            if (strncmp('Итого',trim($day),5)) {
                if (isset($result_itog[$item]['Итого']['ORDER_COUNT']))
                    $result_itog[$item]['Итого']['ORDER_COUNT'] += $result_itog[$item][$day]['ORDER_COUNT'];
                else $result_itog[$item]['Итого']['ORDER_COUNT'] = $result_itog[$item][$day]['ORDER_COUNT'];
            }
            $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            $sheet[$snum]->getStyle($coord)->getFont()->setSize(13);
            $sheet[$snum]->setCellValue($coord, u8($result_itog[$item][$day]['ORDER_COUNT']));
        }
    }
}
// Итого брак
/*$rownum++;
$coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
$sheet[$snum]->getStyle($coord)->getFont()->setSize(16);
$sheet[$snum]->setCellValue($coord, u8('Всего Брак:'));
foreach ($service_list as $item => $value) {
    if (!isset($result_itog[$item])) continue;
    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
    $sheet[$snum]->setCellValue($coord, u8($value));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($result_itog[$item][$day]['BREAK_COUNT'])) {
            $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            $sheet[$snum]->getStyle($coord)->getFont()->setSize(13);
            $sheet[$snum]->setCellValue($coord, u8($result_itog[$item][$day]['BREAK_COUNT']));
        }
    }
}*/
// Всего заявок
$rownum+=2;
$coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
$sheet[$snum]->getStyle($coord)->getFont()->setSize(16);
$sheet[$snum]->setCellValue($coord, u8('Всего заявок:'));
foreach ($service_list as $item => $value) {
    if (!isset($result_itog[$item])) continue;
    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
    $sheet[$snum]->setCellValue($coord, u8($value));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($result_itog[$item][$day]['COUNT_ALL'])) {
            if (strncmp('Итого',trim($day),5)){
                if (isset($result_itog[$item]['Итого']['COUNT_ALL']))
                    $result_itog[$item]['Итого']['COUNT_ALL'] += $result_itog[$item][$day]['COUNT_ALL'];
                else $result_itog[$item]['Итого']['COUNT_ALL'] = $result_itog[$item][$day]['COUNT_ALL'];
            }
            $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
            $sheet[$snum]->setCellValue($coord, u8($result_itog[$item][$day]['COUNT_ALL']));
        }
    }
}

// Глобальные итоги
/*$rownum++;
$coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
$sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
$sheet[$snum]->getStyle($coord)->getFont()->setSize(16);
$sheet[$snum]->setCellValue($coord, u8('Всего заявок:'));
foreach ($service_list as $item => $value) {
    if (!isset($common_itog[$item])) continue;
    $rownum++;
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, $rownum)->getCoordinate();
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
    $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
    $sheet[$snum]->setCellValue($coord, u8($value));
    $colnum = 1;
    foreach ($arrayOfDates as $key => $day) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($colnum++, $rownum)->getCoordinate();
        if (isset($common_itog[2][$day])) {
            $sheet[$snum]->getStyle($coord)->getFont()->setBold(true);
            $sheet[$snum]->getStyle($coord)->getFont()->setSize(14);
            $sheet[$snum]->setCellValue($coord, u8($common_itog[$item][$day]));
        }
    }
}*/

$sheet[$snum]->getStyle('B2:'.$highcol.$rownum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet[$snum]->setAutoFilter('A1:'.$highcol.'1');