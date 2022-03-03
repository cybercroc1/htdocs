<?php
ini_set('max_execution_time','900');
session_name('medc');
session_start();
//$sid=session_id();
extract($_REQUEST);

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit', '256M');

require_once 'med/adm_url.php';
require_once '../funct.php';

$rep_start_date = (isset($_POST['rep_start_date']) ? $_POST['rep_start_date'] : $rep_start_date);
$rep_end_date = (isset($_POST['rep_end_date']) ? $_POST['rep_end_date'] : $rep_end_date);

if (isset($_POST['all_type'])) {
    $filename = "all-" . date("Ymd", strtotime($rep_start_date));
    if ($rep_start_date != $rep_end_date)
        $filename .= "-" . date("Ymd", strtotime($rep_end_date));
}
elseif (EXPORT_OPERATOR == $_POST['ReportId'] || EXPORT_OPERATOR_ALL == $_POST['ReportId'] || EXPORT_OPERATOR_SEC == $_POST['ReportId']) {
    $filename = "oper-" . date("Ymd", strtotime($rep_start_date));
    if ($rep_start_date != $rep_end_date)
        $filename .= "-" . date("Ymd", strtotime($rep_end_date));
}
elseif (EXPORT_EFFECT == $_POST['ReportId'] || EXPORT_EFFECT_ISH == $_POST['ReportId'] || EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
    $filename = "effect-" . date("Ymd", strtotime($rep_start_date));
    if ($rep_start_date != $rep_end_date)
        $filename .= "-" . date("Ymd", strtotime($rep_end_date));
}
elseif (EXPORT_BILLING == $_POST['ReportId']) {
    $filename = "billing-" . date("Ymd", strtotime($rep_start_date));
    if ($rep_start_date != $rep_end_date)
        $filename .= "-" . date("Ymd", strtotime($rep_end_date));
}
else {
    $filename = "med-" . date("Ymd", strtotime($rep_start_date));
    if ($rep_start_date != $rep_end_date)
        $filename .= "-" . date("Ymd", strtotime($rep_end_date));
}

if (isset($_SESSION['data_acc'])) // Права доступа к разным данным
    $data_acc_arr = explode(',', $_SESSION['data_acc']);
else $data_acc_arr = array();

// Общие фильтры
if (EXPORT_CALL == $_POST['ReportId'] || EXPORT_BILLING == $_POST['ReportId'] || EXPORT_EFFECT == $_POST['ReportId'] ||
    EXPORT_EFFECT_ISH == $_POST['ReportId'] || EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
    /*$q_filt_interval = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1 or
    cb.DATE_CLOSE between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";*/
    if (2 == $DateType)
        $q_filt_interval = " and (cb.LAST_CHANGE between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
    else $q_filt_interval = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";

    $q_text4 = " WHERE ( 1=1"; // !!! Лишняя скобка! В запросах надо добавлять закрывающую! !!!
    if (isset($_POST['UserId']) && !in_array('-1', $_POST['UserId']))
        $q_text4 .= " and cb.FIO_ID in (" . implode(',', $_POST['UserId']) . ")";

    if (isset($_POST['StatusId']) && !in_array('-1', $_POST['StatusId']))
        $q_text4 .= " and cb.STATUS_ID in (" . implode(',', $_POST['StatusId']) . ")";
    else $q_text4 .= " and cb.STATUS_ID between ".STATUS_OPEN." and ".STATUS_NOT_COME;
    //else $q_text4 .= " and cb.STATUS_ID between ".STATUS_CALL_STOP." and ".STATUS_BREAK_LINE;

    if (isset($_POST['StatusId']) && in_array(STATUS_ERROR, $_POST['StatusId']) && count($_POST['StatusId']) == 1) {
        if (isset($_POST['status_det']) && !in_array('-1', $_POST['status_det']))
            $q_text4 .= " and cb.STATUS_DET_ID in (" . implode(',', $_POST['status_det']) . ")";
        //else $q_text4 .= " and cb.STATUS_DET_ID between " . STAT_ERR_APPL . " and " . STAT_ERR_INTER; // 801 - 807
    }

    if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId'])) {
        $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $_POST['ServiceId']) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SERVICE_ID in ( 
        select decode(ad.service_id,-1,cb.service_id,ad.service_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }

    if (isset($_POST['S_Type']) && -1 != $_POST['S_Type']) {
        $q_text4 .= " and cb.SOURCE_TYPE_ID = " . $_POST['S_Type'];
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_TYPE_ID in ( 
        select decode(ad.source_type_id,-1,cb.source_type_id,ad.source_type_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }

    /*if (isset($_POST['Reservoir']) && !in_array(SOURCE_ALL, $_POST['Reservoir'])) {
        $q_text4 .= " and cb.SOURCE_MAN_ID in (" . implode(',', $_POST['Reservoir']) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_MAN_ID in (
        select decode(ad.source_man_id,-1,cb.source_man_id,ad.source_man_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
    }*/

    if (isset($_POST['S_Auto']) && !in_array(SOURCE_ALL, $_POST['S_Auto'])) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in (" . implode(',', $_POST['S_Auto']) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in ( 
        select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }
    if (isset($_POST['not_sent'])) // только неотправленные поставщику ошибочные звонки
        $q_text4 .= " and SENT_MAIL is NULL";

    //$q_text4 .= " )";
}

$c = GetData::GetConnect();

if(isset($Export_but)) {
    include('export.php');
    exit();
}

//ИНИЦИИРУЕМ КНИГУ
/** Include PHPExcel */
require_once 'PHPExcel.php';

$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
$cacheSettings = array( 'memoryCacheSize ' => '256MB');
PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

//Свойства документа
$objPHPExcel->getProperties()
			->setTitle(u8("Экспорт обращений"))
			->setSubject(u8("Экспорт обращений"))
			->setDescription(u8("Экспорт обращений"))
			->setKeywords(u8("Экспорт обращений"))
			->setCategory(u8("Экспорт обращений"));

//ИНИЦИИРУЕМ ЛИСТ

$snum = 1; //номер листа
$objPHPExcel->setActiveSheetIndex($snum-1);
$sheet[$snum]=$objPHPExcel->getActiveSheet();
$sheet[$snum]->setTitle(u8("Sheet1"));
$s_cols[$snum] = $s_rows[$snum] = 0;

//
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);

function cmp_name($a, $b)
{
    if (strcmp($a['NAME'], $b['NAME']) != 0)
        return strcmp($a['NAME'], $b['NAME']);
    else {
        if ($a['SEL_ID'] == $b['SEL_ID']) {
            return 0;
        }
        return ($a['SEL_ID'] < $b['SEL_ID']) ? -1 : 1;
    }
}

function fill_list($num_list, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $itog_call, $itog_mail, $itog_write,
                   $itog_visit_interval, $itog_visit, $itog_pay_interval, $itog_pay, $sum_pay_interval, $sum_pay)
{
    $snum = $num_list; //номер листа
    $objPHPExcel->setActiveSheetIndex($snum-1);
    $sheet[$snum]=$objPHPExcel->getActiveSheet();

    if (isset($_SESSION['data_acc'])) // Права доступа к разным данным
        $data_acc_arr = explode(',', $_SESSION['data_acc']);
    else $data_acc_arr = array();

    if (1 == $num_list)
        $head_arr = array('0'=>"Источник рекламы (Авто)", '1'=>"Тип ".chr(10)."источника",
            '2'=>"Кол-во ".chr(10)."принятых ".chr(10)."обращений",
            //'3'=>"Кол-во ".chr(10)."записанных",
            '4'=>"Пришедшие".chr(10)." из записанных",
            '5'=>"Оплаченных ".chr(10)."обращений".chr(10)." из записанных",
            '6'=>"Сумма оплаты ".chr(10)."из записанных",
            '7'=>"Выручка с ".chr(10)."обращения",
            '8'=>"% пришедших, ".chr(10)."от принятых",
            '9'=>"% оплаченных, ".chr(10)."от принятых".chr(10)." обращений");
    else $head_arr = array('0'=>"Источник рекламы (Авто)", '1'=>"Тип ".chr(10)."источника",
        '2'=>"Кол-во ".chr(10)."принятых ".chr(10)."обращений",
        //'3'=>"Кол-во ".chr(10)."записанных",
        '4'=>"Пришедшие ".chr(10)."за период",
        '5'=>"Оплаченных ".chr(10)."обращений".chr(10)." за период",
        '6'=>"Сумма оплаты ".chr(10)."за период",
        '7'=>"Выручка с ".chr(10)."обращения",
        '8'=>"% пришедших, ".chr(10)."от принятых",
        '9'=>"% оплаченных, ".chr(10)."от принятых".chr(10)." обращений");

    /* устанавливаем ячейкам стиль границ */
    $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

    // сначала третья строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach($head_arr as $key=>$val) { // $key не используется, колонки друг за другом
        if (USER_VIEW != $_SESSION['user_role'] || in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, 3)->getCoordinate(); //находим ячейку по координатам
            $sheet[$snum]->setCellValue($coord, u8($val));
        }
    }
    $highcol=$sheet[$snum]->getHighestColumn();
    $sheet[$snum]->getStyle('A3:'.$highcol.'3')->applyFromArray($styleArray);
    $sheet[$snum]->getStyle('A3:'.$highcol.'3')->getFont()->setBold(true);
    $sheet[$snum]->getStyle('A3:'.$highcol.'3')->getAlignment()->setWrapText(true);
    $sheet[$snum]->getStyle('A3:'.$highcol.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getRowDimension(3)->setRowHeight(45); // для заголовка

    if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId'])) {
        $second_row = '';
        foreach($_POST['ServiceId'] as $key=>$val) {
            $second_row .= SERVICE_LIST[$val] .", ";
        }
        $second_row = substr($second_row, 0, -2);
    }
    else $second_row = "Все услуги";

    if ($rep_start_date != $rep_end_date)
        $second_row .= '. За период c ' . $rep_start_date . " по " . $rep_end_date;
    else $second_row .='. За ' . $rep_start_date;
    $second_row .= ". На дату ".date('d.m.Y');

    $coord=$sheet[$snum]->getCellByColumnAndRow(0,1)->getCoordinate();
    if (1 == $num_list)
        $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (Выбор даты по времени обращения)'));
    else $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (Выбор даты по всем столбцам, за период)'));
    $sheet[$snum]->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

    $coord=$sheet[$snum]->getCellByColumnAndRow(0,2)->getCoordinate();
    $sheet[$snum]->setCellValue($coord, u8($second_row));
    $sheet[$snum]->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

    $rnum = 4; // Данные с четвертой строки
    usort($usluga_auto_arr, 'cmp_name');
    $tmp_row = 0;
    $tmp_name = '!!!!';
    $tmp_call=$tmp_write=$tmp_visit_i=$tmp_visit=$tmp_pay_i=$tmp_pay=$tmp_sum_i=$tmp_sum=0;
    foreach ($usluga_auto_arr as $key =>$value) { // отрисовываем полученные данные
        if (strncmp($value['NAME'], $tmp_name, 4) != 0) { // Промежуточные итоги
            if ($tmp_row > 1) {
                $col = 2;
                $rnum++; //номер строки
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
                //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    if (1 == $num_list) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum / $tmp_call, 2) : 0));
                    }
                    else {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i / $tmp_call, 2) : 0));
                    }
                }

                if (1 == $num_list) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit / $tmp_call, 2) : 0));
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
                } else {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit_i / $tmp_call, 2) : 0));
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay_i / $tmp_call, 2) : 0));
                }
                //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                //$sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_write / $tmp_call, 2) : 0));

                $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);
                $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
                $rnum++; //номер строки
            }
            elseif($rnum != 4) {
                $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);
                $rnum++; //номер строки
            }
            $tmp_row = 0;
            $tmp_call=$tmp_write=$tmp_visit_i=$tmp_visit=$tmp_pay_i=$tmp_pay=$tmp_sum_i=$tmp_sum=0;
        }
        $tmp_row++;
        $tmp_name = $value['NAME'];

        $col = 0;
        $rnum++; //номер строки
        $call_mail = $value['CALL']+$value['MAIL'];
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['NAME'])); // 0
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['TYPE'])); // 1
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $call_mail); // 2 будет либо одно, либо другое
        $tmp_call += $value['CALL']+$value['MAIL'];
        //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['WRITE']); // 3
        $tmp_write += $value['WRITE'];
        if (USER_VIEW != $_SESSION['user_role'] || in_array(CAN_FINANCE, $data_acc_arr)) {
            if (1 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT']); // 4
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY']); // 5
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM']); // 6
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM']/$call_mail,2) : 0)); // 7
                $tmp_visit += $value['VISIT'];
                $tmp_pay += $value['PAY'];
                $tmp_sum += $value['SUM'];
            }
            else {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT_INTERVAL']); // 4
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY_INTERVAL']); // 5
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM_INTERVAL']); // 6
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM_INTERVAL']/$call_mail,2) : 0)); // 7
                $tmp_visit_i += $value['VISIT_INTERVAL'];
                $tmp_pay_i += $value['PAY_INTERVAL'];
                $tmp_sum_i += $value['SUM_INTERVAL'];
            }
        }

        if (1 == $num_list) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['VISIT'] / $call_mail, 2) : 0));
            $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100*$value['PAY']/$call_mail,2) : 0)); // 8
        } else {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['VISIT_INTERVAL'] / $call_mail, 2) : 0));
            $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100*$value['PAY_INTERVAL']/$call_mail,2) : 0)); // 8
        }
        //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
        //$sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100*$value['WRITE']/$call_mail,2) : 0)); // 8

        $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
    }

    if ($tmp_row > 1) { // Промежуточные итоги для последнего блока
        $col = 2;
        $rnum++;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
        //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            if (1 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum/$tmp_call,2) : 0));
            }
            else {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i/$tmp_call,2) : 0));
            }
        }
        if (1 == $num_list) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit / $tmp_call, 2) : 0));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
        } else {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit_i / $tmp_call, 2) : 0));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay_i / $tmp_call, 2) : 0));
        }
        //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        //$sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100*$tmp_write/$tmp_call,2) : 0));

        $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);
        $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
        $rnum++; //номер строки
    }
    elseif($rnum != 4) {
        $sheet[$snum]->getStyle('C'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);
        $rnum++; //номер строки
    }

    $col = 0;
    $rnum++; //номер строки
    $itog_call_mail = $itog_call + $itog_mail;
    $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8('Итого:'));
    $col++;
    $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_call+$itog_mail);
    //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_write);
    if (in_array(CAN_FINANCE, $data_acc_arr)) {
        if (1 == $num_list) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round($sum_pay/$itog_call_mail,2) : 0));
        }
        else {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit_interval);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay_interval);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay_interval);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round($sum_pay_interval/$itog_call_mail,2) : 0));
        }
    }
    if (1 == $num_list) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_visit / $itog_call_mail, 2) : 0));
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_pay / $itog_call_mail, 2) : 0));
    } else {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_visit_interval / $itog_call_mail, 2) : 0));
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_pay_interval / $itog_call_mail, 2) : 0));
    }
    //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
    //$sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100*$itog_write/$itog_call_mail,2) : 0));

    $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
    $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);

    if (in_array(CAN_FINANCE, $data_acc_arr)) {
        $sheet[$snum]->getStyle('C4:G'.$rnum)->getNumberFormat()->setFormatCode('#,##0');
        $sheet[$snum]->getStyle('H4:I'.$rnum)->getNumberFormat()->setFormatCode('#,##0.00');
    }
    else $sheet[$snum]->getStyle('C4:E'.$rnum)->getNumberFormat()->setFormatCode('#,##0');

    $sheet[$snum]->getStyle('A1:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet[$snum]->getStyle('A4:A'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet[$snum]->getStyle('A'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $sheet[$snum]->getStyle('B4:B'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getColumnDimension('A')->setWidth(50); // для имени
    $sheet[$snum]->getColumnDimension('B')->setWidth(12);
    $sheet[$snum]->getColumnDimension('C')->setWidth(13);
    $sheet[$snum]->getColumnDimension('D')->setWidth(13);
    $sheet[$snum]->getColumnDimension('E')->setWidth(14);
    $sheet[$snum]->getColumnDimension('F')->setWidth(15);
    $sheet[$snum]->getColumnDimension('G')->setWidth(14);
    $sheet[$snum]->getColumnDimension('H')->setWidth(14);
    $sheet[$snum]->getColumnDimension('I')->setWidth(15);

    $sheet[$snum]->getPageSetup()->setRowsToRepeatAtTop(3);
}

// Экспорт звонков
if (EXPORT_CALL == $_POST['ReportId']) {
    $q_text1 = "SELECT cb.ID, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID,
them.NAME as THEME, serv.NAME as SRVNAME, serv_det.NAME as SERV_DET, cb.SOURCE_TYPE_ID, sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, 
case
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_PROMO . " then 'У промоутера'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_OTHER . " then '---'
    when cb.SOURCE_MAN_DET_ID=" . DETAILS_AMNESY . " then 'Не помнит'
    when cb.SOURCE_MAN_DET_ID>=500 then srd.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_SERT . " then hosp_det.CITY || '-' || hosp_det.NAME
    when cb.SOURCE_MAN_ID=" . SOURCE_FLAER . " or cb.SOURCE_MAN_ID=" . SOURCE_CATALOG . " or
    cb.SOURCE_MAN_ID=" . SOURCE_FLAER_SUB . " or cb.SOURCE_MAN_ID=" . SOURCE_FLAER_CAR . " or
    cb.SOURCE_MAN_ID=" . SOURCE_LIFT . " or cb.SOURCE_MAN_ID=" . SOURCE_STOP . " then 'м.' || metro.NAME
    else to_char(cb.SOURCE_MAN_DET_ID)
end SOURCE_MAN_DET,
sra_det.NAME as SRADETNAME,
case
    when cb.RESULT_ID=" . RESULT_NOT . " then '---'
    when cb.RESULT_ID=" . RESULT_KC . " then 'в КЦ'
    when cb.RESULT_ID=" . RESULT_CLINIC . " then 'в Клинику'
    when cb.RESULT_ID=" . RESULT_WAIT . " then 'Ждет звонка'
    when cb.RESULT_ID=" . RESULT_AON. " then 'Не оставил номер'
end RESULT,
case
    when cb.RESULT_ID=" . RESULT_KC . " then 'Номер: ' || cb.RESULT_DET
    when cb.RESULT_ID=" . RESULT_CLINIC . " then hosp.CITY || '-' || hosp.NAME
    else to_char(cb.RESULT_DET)
end RESULT_DET,
cb.CLIENT_NAME, cb.PHONE_MOB, cb.COMMENTS, cb.STATUS_ID, stat.NAME as STATUS, stat_det.NAME as STATUS_DET, 
usr.FIO as FIO, cb.CALL_DOUBLE, cb.INTERSTATE, cb.OKTELL_IDCHAIN, 
to_char(cb.entry_date_1c,'dd.mm.yyyy hh24:mi') entry_date_1c,
to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.DATE_CLOSE,'dd.mm.yyyy') DATE_CLOSE";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN CALL_THEME them ON cb.CALL_THEME_ID = them.ID 
    LEFT JOIN MED_STATUS stat ON cb.STATUS_ID = stat.ID
    LEFT JOIN MED_STATUS_DET stat_det ON cb.STATUS_DET_ID = stat_det.ID
    LEFT JOIN SERVICES serv ON cb.SERVICE_ID = serv.ID 
    LEFT JOIN SERVICE_DET serv_det ON cb.SERVICE_DET_ID = serv_det.ID 
    LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID 
    LEFT JOIN SOURCE_MAN sr_man ON cb.SOURCE_MAN_ID = sr_man.ID
    LEFT JOIN SUBWAYS metro ON cb.SOURCE_MAN_DET_ID = metro.ID
    LEFT JOIN HOSPITALS hosp_det ON cb.SOURCE_MAN_DET_ID = hosp_det.ID
    LEFT JOIN SOURCE_MAN_DETAIL srd ON cb.SOURCE_MAN_DET_ID = srd.ID
    LEFT JOIN SOURCE_AUTO_DETAIL_TEST sra_det ON cb.SOURCE_MAN_ID_NEW = sra_det.ID
    LEFT JOIN HOSPITALS hosp ON cb.RESULT_DET = hosp.ID
    LEFT JOIN USERS usr ON cb.FIO_ID = usr.ID";
/* sr_man_new.NAME as SRMNAME_NEW,
case
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_PROMO . " then 'У промоутера'
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_OTHER . " then '---'
    when cb.SOURCE_MAN_DET_ID_NEW=" . DETAILS_AMNESY . " then 'Не помнит'
    when cb.SOURCE_MAN_DET_ID_NEW>=500 then srd_new.NAME
    when cb.SOURCE_MAN_ID_NEW=" . SOURCE_SERT . " then hosp_det_new.CITY || '-' || hosp_det_new.NAME
    when cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_CATALOG . " or
    cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER_SUB . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_FLAER_CAR . " or
    cb.SOURCE_MAN_ID_NEW=" . SOURCE_LIFT . " or cb.SOURCE_MAN_ID_NEW=" . SOURCE_STOP . " then 'м.' || metro_new.NAME
    else to_char(cb.SOURCE_MAN_DET_ID_NEW)
end SOURCE_MAN_DET_NEW, */
    //LEFT JOIN SOURCE_MAN sr_man_new ON cb.SOURCE_MAN_ID_NEW = sr_man_new.ID
    //LEFT JOIN SUBWAYS metro_new ON cb.SOURCE_MAN_DET_ID_NEW = metro_new.ID
    //LEFT JOIN HOSPITALS hosp_det_new ON cb.SOURCE_MAN_DET_ID_NEW = hosp_det_new.ID
    //LEFT JOIN SOURCE_MAN_DETAIL srd_new ON cb.SOURCE_MAN_DET_ID_NEW = srd_new.ID

    if (isset($_POST['all_type'])) // остальные фильтры формируются в начале файла
        $q_text4 .= " or (call_theme_id > " . THEME_MED .
"  and cb.source_auto_id in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id)
     from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id 
     and uda.deleted is NULL and uda.user_id=".$_SESSION['login_id_med'].") )";
    $q_text4 .= ")"; // !!! закрывающая скобка

    $q_text5 = " ORDER BY cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_interval . $q_text5;
//echo "<br><textarea>".$q_text."</textarea><br>";

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);

    $head_arr = array('0'=>"№ ".chr(10)."заявки", '1'=>"Дата ".chr(10)."звонка", '2'=>"ID звонка",
        '3'=>"Проект", '4'=>"ANumber", '5'=>"Оператор", '6'=>"Тема", '7'=>"Услуга",
        '8'=>"Тип ".chr(10)."источника", '9'=>"BNumber", '10'=>"Источник (Авто)",
        '11'=>"Источник".chr(10)." (вход.)", '12'=>"Детализация".chr(10)." источника (вход.)",
        '13'=>"Источник".chr(10)." (исх.)", '14'=>"Результат".chr(10)." входящего", '15'=>"Детализация".chr(10)." результата",
        '16'=>"ФИО", '17'=>"Контактный".chr(10)." телефон", '18'=>"Статус", '19'=>"Уточнение".chr(10)." ошибки",
        '20'=>"Комментарий".chr(10)." вход", '21'=>"Комментарий".chr(10)." последний",
        '22'=>"Назначено", '23'=>"Клиника", '24'=>"Записан", '25'=>"Контакт", '26'=>"Дата".chr(10)." записи",
        '27'=>"Время".chr(10)." события", '28'=>"Закрыто",'29'=>"ID Цепочки",'30'=>"Запись".chr(10)." звонка");
    $remove_column = array(0,2,3,4,5,9,11,12,13,14,15,22,23,24,25,26,29,30);
    $gus_column = array(5,12,13,15,22,23,24,25,26,27,28,29,30); // убираем у Гусарова

    // сначала вторая строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach($head_arr as $key=>$val) {
        //$cnum=$key; //номер столбца
        //$coord=$sheet[$snum]->getCellByColumnAndRow($cnum,2)->getCoordinate(); //находим ячейку по координатам
        if ((IT_PLANET != $_SESSION['login_id_med'] || !in_array($key, $remove_column)) &&
            ($data_acc_arr && in_array(CAN_HEAR, $data_acc_arr) || (30 != $key && 29 != $key))) { // ограничение для прослушивания записей
            if (in_array($_SESSION['login_id_med'],EXPORT_CUT) && in_array($key, $gus_column)) {
                continue;
            }
            else {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, 2)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8($val));
            }
        }
    }

    //$highcol = count($head_arr);
    $highcol=$sheet[$snum]->getHighestColumn();
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getFont()->setBold(true)->setSize(12);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setWrapText(true);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getRowDimension(2)->setRowHeight(30); // для заголовка

    //$sheet[$snum]->mergeCells('A1:'.$highcol.'1');
    $coord=$sheet[$snum]->getCellByColumnAndRow(0,1)->getCoordinate();
    if ($rep_start_date != $rep_end_date)
        $sheet[$snum]->setCellValue($coord, u8('Экспорт заявок с '.$rep_start_date." по ".$rep_end_date));
    else $sheet[$snum]->setCellValue($coord, u8('Экспорт заявок за '.$rep_start_date));
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);
    //$sheet[$snum]->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8,8);

	$rnum=2;
    while (OCIFetch($q)) {
		$rnum++; //номер строки
        $base_id = OCIResult($q, "ID");
        $status_id = OCIResult($q, "STATUS_ID");
        $client_name = OCIResult($q, "CLIENT_NAME");
        //$surname = substr($client_name, 0, strpos($client_name, '/'));
        //$name = substr($client_name, strpos($client_name, '/') + 1, strrpos($client_name, '/') - strpos($client_name, '/') - 1);
        //$patronymic = substr($client_name, strripos($client_name, '/') + 1, strlen($client_name));

        $q_hist = OCIParse($c, "SELECT STATUS_ID, COMMENTS FROM CALL_BASE_HIST WHERE BASE_ID=:id and STATUS_ID=:stat_id ORDER BY ID desc ");
        OCIBindByName($q_hist, ":id", $base_id);
        OCIBindByName($q_hist, ":stat_id", $status_id);
        OCIExecute($q_hist, OCI_DEFAULT);
        //$last_result = OCI_Fetch_Row($q_hist);
        $comment_cut = '';
        while (OCIFetch($q_hist)) {
            $last_comment = trim(OCIResult($q_hist, "COMMENTS"));
            if (STATUS_CALL_BACK == $status_id || STATUS_WORK == $status_id)
                $comment_cut = substr($last_comment, strpos($last_comment,')')+1);
            else $comment_cut = $last_comment;
            break;
        }

        $q_clinic = OCIParse($c, "SELECT (hosp.CITY || '-' || hosp.NAME) AS HOSP_NAME, 
CLIENT_NAME, CLIENT_PHONE, CLIENT_STATUS, to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi:ss') CLIENT_DATE FROM CALL_BASE_CLINIC 
LEFT JOIN HOSPITALS hosp ON HOSPITAL_ID = hosp.ID WHERE BASE_ID=:id");
        OCIBindByName($q_clinic, ":id", $base_id);
        OCIExecute($q_clinic, OCI_DEFAULT);
        OCIFetch($q_clinic);
        $hospital = OCIResult($q_clinic, "HOSP_NAME");
        $clinic_client_name = OCIResult($q_clinic, "CLIENT_NAME");
        $clinic_client_phone = OCIResult($q_clinic, "CLIENT_PHONE");
        $clinic_client_status = OCIResult($q_clinic, "CLIENT_STATUS");
        $clinic_client_date = OCIResult($q_clinic, "CLIENT_DATE");
        $clinic_surname = substr($clinic_client_name, 0, strpos($clinic_client_name, '/'));
        $clinic_name = substr($clinic_client_name, strpos($clinic_client_name, '/') + 1, strrpos($clinic_client_name, '/') - strpos($clinic_client_name, '/') - 1);
        $clinic_patronymic = substr($clinic_client_name, strripos($clinic_client_name, '/') + 1, strlen($clinic_client_name));
        //$nrowhosp = GetData::GetHospitals("hosp.ID = ". $hospital);

        $col = 0;
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($base_id));
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "DATE_CALL") ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_CALL_ID")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_PROJECT_ID")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "ANUMBER")));
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_AGID")));
            }
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "THEME") ));
		$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SRVNAME") ));
		//$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SERV_DET")));
		$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( DEVICES[OCIResult($q, "SOURCE_TYPE_ID")] ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "BNUMBER")));
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SRANAME") ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRMNAME")));
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SOURCE_MAN_DET")));
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRADETNAME")));
            }
            //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SRMNAME_NEW") ));
            //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SOURCE_MAN_DET_NEW") ));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "RESULT")));
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "RESULT_DET")));
            }
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "CLIENT_NAME") ));
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "PHONE_MOB") ));
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
		if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE") || 1 == OCIResult($q, "INTERSTATE")) {
            $tmp_str = "";
            if (CALL_SECOND == OCIResult($q, "CALL_DOUBLE"))
                $tmp_str .= " (Дубль)";
            if (1 == OCIResult($q, "INTERSTATE"))
                $tmp_str .= " (Межгород)";
            if (USER_VIEW == $_SESSION['user_role']) {
                $sheet[$snum]->setCellValue($coord, u8($tmp_str));
            }
            else $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STATUS").$tmp_str));
        }
        else {
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STATUS")));
        }
		$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "STATUS_DET") ));
		$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, trim(u8( OCIResult($q, "COMMENTS") )));
		$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, trim(u8( $comment_cut )));
        if (IT_PLANET != $_SESSION['login_id_med'] && !in_array($_SESSION['login_id_med'],EXPORT_CUT)) { // ограничение для IT Planet и Гусарова
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "FIO")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($hospital));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($clinic_surname . ' ' . $clinic_name . ' ' . $clinic_patronymic));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($clinic_client_phone));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "ENTRY_DATE_1C")));
        }
        if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "LAST_CHANGE")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "DATE_CLOSE")));
        }

        if ($data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) { // ограничение для прослушивания записей
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "OKTELL_IDCHAIN")));
            if (DEVICE_PHONE == OCIResult($q, "SOURCE_TYPE_ID")) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8('Ссылка'));
                //$sheet[$snum]->setCellValue($coord, "<a href=".$oktell_records_url . "?idchain=" . OCIResult($q, "OKTELL_IDCHAIN").">123</a>");
                $sheet[$snum]->getCell($coord)->getHyperlink()->setUrl($oktell_records_url . "?idchain=" . OCIResult($q, "OKTELL_IDCHAIN"));
                //$sheet[$snum]->getCell($coord)->getHyperlink()->setTooltip('Открыть разговор');
                $sheet[$snum]->getStyle($coord)->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);
                $sheet[$snum]->getStyle($coord)->getFont()->getColor()->setRGB('blue');
            }
        }
    }
    //$coord=$sheet[$snum]->getCellByColumnAndRow(0,++$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, '-----');

    $sheet[$snum]->getStyle('A1:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet[$snum]->mergeCells('A1:'.$highcol.'1');

}
// Экспорт данных по работе операторов
elseif (EXPORT_OPERATOR == $_POST['ReportId'] || EXPORT_OPERATOR_ALL == $_POST['ReportId'] || EXPORT_OPERATOR_SEC == $_POST['ReportId']) {
    $q_start = "select ID, FIO from USERS ";
    if (EXPORT_OPERATOR_SEC == $_POST['ReportId']) {
        if (isset($UserIdSpec) && !in_array('-1', $UserIdSpec)) {
            $q_usersSpec = implode(',', $UserIdSpec);
        }
        else {
            $q_usersSpec = implode(',', SPEC_USER_CALL);
        }
        if (strlen($q_usersSpec) > 0)
            $q_start .= " WHERE ID in (" . $q_usersSpec . ")";
    }
    else { // (EXPORT_OPERATOR_ALL == $_POST['ReportId'] && isset($_POST['UserId']))
        if (!in_array('-1', $_POST['UserId']))
            $q_users = implode(',', $_POST['UserId']);
        else {
            $q_users = "";
            $strfilt = " (ROLE_ID = " . USER_USER . " or ROLE_ID = " . USER_SUPER . ") and usr.ID != " . SPEC_USER;
            if (GetData::GetUsersDep(FALSE, $strfilt, NULL, 'not')) { // без удаленных операторов
                foreach(GetData::$array_userd as $key => $value) {
                    $q_users .= $value['ID'] . ",";
                }
                $q_users = substr($q_users, 0, -1);
            }
            //$q_users .= $_SESSION['login_id_med']; // и сам супервизор
        }
        if (strlen($q_users) > 0)
            $q_start .= " WHERE ID in (" . $q_users . ")";
    }
    $q_start .= " order by fio";

    $q = OCIParse($c, $q_start);
    OCIExecute($q);
    $calls_arr = array();
    while (OCIFetch($q)) { // составим и инициируем список всех операторов департамента, включая супервизора
        $operator_id = OCIResult($q, "ID");
        $calls_arr[$operator_id]['FIO'] = OCIResult($q, "FIO");
        $calls_arr[$operator_id]['DAYS'] = 0;
        $calls_arr[$operator_id]['TOTAL'] = 0;
        $calls_arr[$operator_id]['ZACHET'] = 0;
        $calls_arr[$operator_id]['CLINIC'] = 0;
        $calls_arr[$operator_id]['CLINIC_NOT'] = 0;
        $calls_arr[$operator_id]['ERROR_ALL'] = 0;
        $calls_arr[$operator_id]['BREAK'] = 0;
        $calls_arr[$operator_id]['ERROR'] = 0;
        $calls_arr[$operator_id]['REPEAT'] = 0;
        $calls_arr[$operator_id]['OPEN'] = 0;
        $calls_arr[$operator_id]['WORK'] = 0;
        $calls_arr[$operator_id]['CALL_BACK'] = 0;
        $calls_arr[$operator_id]['CALL_NOT'] = 0;
        $calls_arr[$operator_id]['DOUBLE'] = 0;
        $calls_arr[$operator_id]['INTERSTATE'] = 0;
    }

    // Сначала считаем количество смен
    /*$q_text1 = "select user_id, to_char(DATE_DET,'dd.mm.yyyy') as DATEDET";
    $q_text2 = " FROM CALL_BASE_HIST ";
    $q_text3 = " "; //left join users usr on usr.id = hist.user_id ";
    if (EXPORT_OPERATOR_ALL == $_POST['ReportId'])
        $q_text4 = " WHERE STATUS_ID <= " . STATUS_NOT_COME;
    else $q_text4 = " WHERE STATUS_ID between " . STATUS_CALL_STOP . " and " . STATUS_NOT_COME;
    $q_text4 .= " and (DATE_DET between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
    if (isset($_POST['UserId']) && strlen($q_users) > 0)
        $q_text4 .= " and user_id in (" . $q_users . ")";
    $q_text4 .= " and user_id != " . SPEC_USER;
    $q_text5 = " group by user_id, to_char(DATE_DET,'dd.mm.yyyy')";
    $q_text6 = " order by user_id, to_char(DATE_DET,'dd.mm.yyyy')";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_text5 . $q_text6;
    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    $operator_id = -1;
    $date_last = "";
    while (OCIFetch($q)) { // наполняем данными о звонках
        if (OCIResult($q, "USER_ID") != $operator_id) { // новый оператор
            $operator_id = OCIResult($q, "USER_ID");
            $date_last = OCIResult($q, "DATEDET");
            $calls_arr[$operator_id]['DAYS'] = 1;
        }
        if (OCIResult($q, "DATEDET") != $date_last) { // новая смена у оператора
            $calls_arr[$operator_id]['DAYS'] += 1;
            $date_last = OCIResult($q, "DATEDET");
        }
    }*/

    // Считаем количество смен группировкой
    $q_text1 = "select count(*), to_char(date_det,'yyyy.mm.dd') date_det";
    $q_text2 = " FROM CALL_BASE_HIST";
    $q_text5 = " group by to_char(date_det,'yyyy.mm.dd')";
    foreach($calls_arr as $key=>$value) {
        $q_text4 = " where (DATE_DET between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
        $q_text4 .= " and comments like '%(fio_id=" .$key.")%'";
        $q_text = $q_text1 . $q_text2 . $q_text4 . $q_text5;
        $q = OCIParse($c, $q_text);
        OCIExecute($q,OCI_DEFAULT);
        $nrows = OCI_Fetch_All($q, $array_status, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        $calls_arr[$key]['DAYS'] = $nrows;
    }
    oci_free_statement($q);

    // Теперь считаем по статусам
    if (EXPORT_OPERATOR_SEC == $_POST['ReportId']) {
        $q_text1 = "select count(*) as pnum, usr.fio, second_fio_id, to_char(DATE_SECOND_CHANCE,'dd.mm.yyyy') as CALL_DATE, second_status_id";
        $q_text2 = " FROM CALL_BASE cb ";
        $q_text3 = " left join users usr on usr.id = cb.second_fio_id ";
        $q_text4 = " WHERE second_status_id <= " . STATUS_NOT_COME . " and second_fio_id is not null";
        $q_text4 .= " and (DATE_SECOND_CHANCE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        if (isset($_POST['UserIdSec']) && strlen($q_usersSpec) > 0)
            $q_text4 .= " and cb.SECOND_FIO_ID in (" . $q_usersSpec . ")";
        $q_text5 = " group by usr.fio, second_fio_id, to_char(DATE_SECOND_CHANCE,'dd.mm.yyyy'), second_status_id";
        $q_text6 = " order by usr.fio, second_fio_id, to_char(DATE_SECOND_CHANCE,'dd.mm.yyyy'), second_status_id";
    }
    else { //EXPORT_OPERATOR / EXPORT_OPERATOR_ALL
        $q_text1 = "select count(*) as pnum, usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy') as CALL_DATE, status_id";
        $q_text2 = " FROM CALL_BASE cb ";
        $q_text3 = " left join users usr on usr.id = cb.fio_id ";
        $q_text4 = " WHERE STATUS_ID <= " . STATUS_NOT_COME;
        $q_text4 .= " and (DATE_CALL between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        if (isset($_POST['UserId']) && strlen($q_users) > 0)
            $q_text4 .= " and cb.FIO_ID in (" . $q_users . ")";
        $q_text4 .= " and cb.FIO_ID != " . SPEC_USER;
        $q_text5 = " group by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
        $q_text6 = " order by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
    }
    //or LAST_CHANGE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";

    if (EXPORT_OPERATOR_SEC != $_POST['ReportId']) { // операторы второго дозвона не имеют доступа к Стоматологии
        if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId'])) {
            $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $_POST['ServiceId']) . ")";
        }
        if (USER_ADMIN != $_SESSION['user_role']) {
            $q_text4 .= " and cb.SERVICE_ID in ( 
            select decode(ad.service_id,-1,cb.service_id,ad.service_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
            where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
        }
    }
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_text5 . $q_text6;
//var_dump($q_text);

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    $operator_id = -1;
    //$date_last = "";
    $itog_call = $itog_zachet = $itog_clinic = $itog_clinic_not = 0;
    $itog_error_all = $itog_error = $itog_repeat = $itog_break = $itog_double = $itog_interstate = 0;
    $itog_open = $itog_work = $itog_call_back = $itog_call_not = 0;
    while (OCIFetch($q)) { // наполняем данными о звонках
        $pnum = OCIResult($q, "PNUM");
        $itog_call += $pnum;
        if (EXPORT_OPERATOR_SEC == $_POST['ReportId']) {
            $status_id = OCIResult($q, "SECOND_STATUS_ID");
            if (OCIResult($q, "SECOND_FIO_ID") != $operator_id) { // новый оператор
                $operator_id = OCIResult($q, "SECOND_FIO_ID");
            }

        }
        else { // EXPORT_OPERATOR / EXPORT_OPERATOR_ALL
            $status_id = OCIResult($q, "STATUS_ID");
            if (OCIResult($q, "FIO_ID") != $operator_id) { // новый оператор
                $operator_id = OCIResult($q, "FIO_ID");
            }
        }

        $calls_arr[$operator_id]['TOTAL'] += $pnum;
        if (STATUS_CLINIC == $status_id || STATUS_CLINIC_NOT == $status_id || // успешный статус
            $status_id <= STATUS_CALL_NOT) { // для отчета со всеми заявками
            if (STATUS_CLINIC == $status_id || STATUS_CLINIC_NOT == $status_id ||
                (STATUS_CALL_BACK == $status_id || STATUS_WORK == $status_id)) {
                $itog_zachet += $pnum;
                $calls_arr[$operator_id]['ZACHET'] += $pnum;
            }

            if (STATUS_CLINIC == $status_id) {
                $itog_clinic += $pnum;
                $calls_arr[$operator_id]['CLINIC'] += $pnum;
            } elseif (STATUS_CLINIC_NOT == $status_id) {
                $itog_clinic_not += $pnum;
                $calls_arr[$operator_id]['CLINIC_NOT'] += $pnum;
            } elseif (STATUS_OPEN == $status_id) {
                $itog_open += $pnum;
                $calls_arr[$operator_id]['OPEN'] += $pnum;
            } elseif (STATUS_WORK == $status_id) {
                $itog_work += $pnum;
                $calls_arr[$operator_id]['WORK'] += $pnum;
            } elseif (STATUS_CALL_NOT == $status_id) {
                $itog_call_not += $pnum;
                $calls_arr[$operator_id]['CALL_NOT'] += $pnum;
            } elseif (STATUS_CALL_BACK == $status_id) {
                $itog_call_back += $pnum;
                $calls_arr[$operator_id]['CALL_BACK'] += $pnum;
            }
        } else { // ошибки
            $itog_error_all += $pnum;
            if (STATUS_BREAK_LINE != $status_id)
                $calls_arr[$operator_id]['ERROR_ALL'] += $pnum;
            if (STATUS_ERROR == $status_id || STATUS_CL_CANCEL == $status_id) {
                $itog_error += $pnum;
                $calls_arr[$operator_id]['ERROR'] += $pnum;
            } elseif (STATUS_REPEAT == $status_id) {
                $itog_repeat += $pnum;
                $calls_arr[$operator_id]['REPEAT'] += $pnum;
            } elseif (STATUS_BREAK_LINE == $status_id) {
                $itog_break += $pnum;
                $calls_arr[$operator_id]['BREAK'] += $pnum;
            } else { //STATUS_CALL_STOP
                // ???
            }
        }
    }
    oci_free_statement($q);

    // Считаем количество Дублей и Межгорода
    if (EXPORT_OPERATOR_SEC != $_POST['ReportId']) { // для второго дозвона их быть не должно
        $q_text1 = "select count(case when call_double = 2 then 1 else NULL end) as doub, 
count(case when interstate = 1 then 1 else NULL end) as inter, fio_id FROM CALL_BASE cb";
        $q_text4 = " WHERE STATUS_ID in (" . STATUS_CLINIC . "," . STATUS_CLINIC_NOT . "," . STATUS_CALL_BACK . ")";
        $q_text4 .= " and (DATE_CALL between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        /*}
        else {
            $q_text1 = "select count(case when call_double = 2 then 1 else NULL end) as doub,
    count(case when interstate = 1 then 1 else NULL end) as inter, second_fio_id FROM CALL_BASE cb";
            $q_text4 = " WHERE SECOND_STATUS_ID in (".STATUS_CLINIC.",".STATUS_CLINIC_NOT.",".STATUS_CALL_BACK.")";
            $q_text4 .= " and (DATE_SECOND_CHANCE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        }*/
        $q_text4 .= " and (call_double = 2 or interstate = 1)";
        if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId'])) {
            $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $_POST['ServiceId']) . ")";
        }
        if (USER_ADMIN != $_SESSION['user_role']) {
            $q_text4 .= " and cb.SERVICE_ID in ( 
        select decode(ad.service_id,-1,cb.service_id,ad.service_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
        }
        //if (EXPORT_OPERATOR_ALL == $_POST['ReportId'])
            $q_text5 = " group by fio_id";
        //else $q_text5 = " group by second_fio_id";
        $q_text = $q_text1 . $q_text4 . $q_text5;

        $q = OCIParse($c, $q_text);
        OCIExecute($q, OCI_DEFAULT);
        while (OCIFetch($q)) { // наполняем данными
            //if (EXPORT_OPERATOR_ALL == $_POST['ReportId'])
            $operator_id = OCIResult($q, "FIO_ID");
            //else $operator_id = OCIResult($q, "SECOND_FIO_ID");
            $doub = OCIResult($q, "DOUB");
            $inter = OCIResult($q, "INTER");
            $itog_double += $doub;
            $itog_interstate += $inter;
            $calls_arr[$operator_id]['DOUBLE'] += $doub;
            $calls_arr[$operator_id]['INTERSTATE'] += $inter;
            $itog_zachet -= ($doub + $inter);
            $calls_arr[$operator_id]['ZACHET'] -= ($doub + $inter);
        }
    }

    /* STATUS_CALL_BACK; STATUS_CALL_NOT
    STATUS_CALL_STOP;
    STATUS_ERROR; STATUS_REPEAT; STATUS_BREAK_LINE
    STATUS_CLINIC; STATUS_CLINIC_NOT */
    $head_arr = array(
        '0' => "Оператор",
        '1' => "Кол-во" . chr(10) . " смен",
        '2' => "Кол-во звонков" . chr(10) . " (кроме брака)",
        '3' => "Кол-во в среднем" . chr(10) . " (кроме брака)",
        '4' => "Кол-во" . chr(10) . " брака",
        '5' => "% брака",
        '6' => "Кол-во " . chr(10) . " звонков",
        '7' => "Запись " . chr(10) . "в клинику",
        '8' => "Отказ " . chr(10) . "от записи",
        '9' => "Повторный" . chr(10) . " пациент",
        '10' => "Обрыв" . chr(10) . " связи",
        '11' => "Ошибка",
        '12' => "Новые",
        '13' => "Назначены",
        '14' => "Консультация",
        '15' => "Недозвон/" . chr(10) . " перезвон",
        '16' => "Дубль",
        '17' => "Межгород"
    );
    //$remove_column = array(12, 13, 14, 15, 16, 17);

    $sheet[$snum]->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // сначала вторая строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach ($head_arr as $key => $val) {
        //if (EXPORT_OPERATOR_ALL == $_POST['ReportId'] || !in_array($key, $remove_column)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, 2)->getCoordinate(); //находим ячейку по координатам
            $sheet[$snum]->setCellValue($coord, u8($val));
        //}
    }
    //$highcol = count($head_arr);
    $highcol = $sheet[$snum]->getHighestColumn();
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getFont()->setBold(true);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setWrapText(true);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getRowDimension(2)->setRowHeight(30); // для заголовка

    $sheet[$snum]->mergeCells('A1:'.$highcol.'1');
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, 1)->getCoordinate();
    if ($rep_start_date != $rep_end_date)
        $sheet[$snum]->setCellValue($coord, u8('Отчет по количеству звонков c ' . $rep_start_date . " по " . $rep_end_date));
    else $sheet[$snum]->setCellValue($coord, u8('Отчет по количеству звонков на ' . $rep_start_date));
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

    $rnum = 3; // Данные с третьей строки
    //$sheet[$snum]->fromArray($calls_arr, null, 'A3', false);
    foreach ($calls_arr as $value) { // отрисовываем полученные данные
        $col = 0;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['FIO'])); //Оператор
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['DAYS'])); //"Кол-во смен"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['ZACHET'])); //"Кол-во звонков (кроме брака)"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(($value['DAYS'] != 0 ? round($value['ZACHET'] / $value['DAYS'], 2) : 0))); //"Кол-во в среднем (кроме брака)"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['ERROR_ALL'])); //"Кол-во брака"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(($value['TOTAL'] != 0 ? round($value['ERROR_ALL'] / $value['TOTAL'] * 100, 2) : 0))); //"% брака"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['TOTAL'])); //"Кол-во звонков"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['CLINIC'])); //"Запись в клинику"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['CLINIC_NOT'])); //"Отказ от записи"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['REPEAT'])); //"Повторный пациент"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['BREAK'])); //"Обрыв связи"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['ERROR'])); //"Ошибка"
        //if (EXPORT_OPERATOR_ALL == $_POST['ReportId']) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['OPEN'])); //"Новая"
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['WORK'])); //"Назначено"
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['CALL_BACK'])); //"Перезвон"
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['CALL_NOT'])); //"Недозвон"
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['DOUBLE'])); //"Дубль"
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['INTERSTATE'])); //"Межгород"
        //}
        $rnum++; //номер строки
    }

    $col = 0;
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8('Итого:')); //Оператор
    $col++;
    //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(''));      //"Кол-во смен"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_zachet)); //"Кол-во звонков (кроме брака)"
    $col++;
    //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(''));      //"Кол-во в среднем (кроме брака)"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_error_all)); //"Кол-во брака"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(($itog_call != 0 ? round($itog_error_all / $itog_call * 100, 2) : 0))); //"% брака"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_call)); //"Кол-во звонков"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_clinic)); //"Запись в клинику"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_clinic_not)); //"Отказ от записи"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_repeat)); //"Повторный пациент"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_break)); //"Обрыв связи"
    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_error)); //"Ошибка"
    //if (EXPORT_OPERATOR_ALL == $_POST['ReportId']) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_open)); //"Новая"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_work)); //"Назначено"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_call_back)); //"Перезвон"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_call_not)); //"Недозвон"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_double)); //"Дубль"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($itog_interstate)); //"Межгород"
    //}

    $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);
    $sheet[$snum]->getStyle('A'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $sheet[$snum]->getStyle('B3:'.$highcol.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getStyle('A1:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet[$snum]->getColumnDimension('G')->setVisible(false);
    $sheet[$snum]->getColumnDimension('H')->setVisible(false);
    $sheet[$snum]->getColumnDimension('I')->setVisible(false);
    $sheet[$snum]->getColumnDimension('J')->setVisible(false);
    $sheet[$snum]->getColumnDimension('K')->setVisible(false);
    $sheet[$snum]->getColumnDimension('L')->setVisible(false);
    //if (EXPORT_OPERATOR_ALL == $_POST['ReportId']) {
        $sheet[$snum]->getColumnDimension('M')->setVisible(false);
        $sheet[$snum]->getColumnDimension('N')->setVisible(false);
        $sheet[$snum]->getColumnDimension('O')->setVisible(false);
        $sheet[$snum]->getColumnDimension('P')->setVisible(false);
        $sheet[$snum]->getColumnDimension('Q')->setVisible(false);
        $sheet[$snum]->getColumnDimension('R')->setVisible(false);
    //}
}
// Отчеты по эффективности рекламы
elseif (EXPORT_EFFECT == $_POST['ReportId'] || EXPORT_EFFECT_ISH == $_POST['ReportId'] || EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
    $q_filt_date = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
// основные данные - подсчет за период
    if (EXPORT_EFFECT_ISH == $_POST['ReportId']) { // Комбо отчет
        $q_text1 = "SELECT 
        case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000 end SEL_ID,
        case when sra_det.NAME is NULL then '*'||sr_a.NAME else sra_det.NAME end SEL_NAME,
        cb.SOURCE_TYPE_ID, cb.STATUS_ID, cb.VISIT_DATE_1C, vh.DATE_VISIT, ph.RUB, cb.ID as CALL_ID, cb.INTERSTATE, cb.CALL_DOUBLE ";
        $q_text3 = " LEFT JOIN payment_hist ph ON ph.BASE_ID = cb.ID
            LEFT JOIN VISIT_HIST vh ON vh.BASE_ID = cb.ID
            LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID
            LEFT JOIN SOURCE_AUTO_DETAIL_TEST sra_det ON cb.SOURCE_MAN_ID_NEW = sra_det.ID";
        $q_text5 = " ORDER BY SEL_NAME, SEL_ID, cb.ID"; // ибо есть одинаковые имена и несколько платежей на заявку
    }
    else { //(EXPORT_EFFECT == $_POST['ReportId'] || EXPORT_EFFECT_IDYN == $_POST['ReportId'])
        $q_text1 = "SELECT cb.SOURCE_AUTO_ID as SEL_ID, sr_a.NAME as SEL_NAME,
        cb.SOURCE_TYPE_ID, cb.STATUS_ID, cb.VISIT_DATE_1C, vh.DATE_VISIT, ph.RUB, cb.ID as CALL_ID, cb.INTERSTATE, cb.CALL_DOUBLE ";
        $q_text3 = " LEFT JOIN payment_hist ph ON ph.BASE_ID = cb.ID
            LEFT JOIN VISIT_HIST vh ON vh.BASE_ID = cb.ID
            LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID ";
        $q_text5 = " ORDER BY SEL_NAME, SEL_ID, cb.ID"; // ибо есть одинаковые имена и несколько платежей на заявку
    }
    $q_text2 = " FROM call_base cb ";
    //$q_text4 формируется в начале файла
    $q_text4 .= ") and cb.STATUS_ID != " . STATUS_CLOSED; // !!! закрывающая скобка
    //$q_text4 .= " and CALL_TYPE_ID = ". CALL_FIRST; // ???

    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_date . $q_text5;
//var_dump($q_text);

    $usluga_auto_arr = array();
    $call_id = $sel_id = $sra_det_id = -1;
    $itog_call = $itog_mail = $itog_write = $itog_visit = $itog_pay = $sum_pay = 0;

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $source_type_id = OCIResult($q,"SOURCE_TYPE_ID");
        $status_id = OCIResult($q,"STATUS_ID");
        $call_double = OCIResult($q, "CALL_DOUBLE");
        $interstate = OCIResult($q, "INTERSTATE");
        //$date_visit = OCIResult($q, "VISIT_DATE_1C");
        $date_visit = OCIResult($q, "DATE_VISIT");
        if (OCIResult($q,"CALL_ID") != $call_id) {
            if ((STATUS_CLINIC == $status_id || STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id) &&
                $interstate != 1 && $call_double != CALL_SECOND) {
                if (DEVICE_PHONE == $source_type_id)
                    $itog_call++;
                else $itog_mail++;
            }
            if (STATUS_CLINIC == $status_id)
                $itog_write++;
            if ($date_visit != NULL && $date_visit != '')
                $itog_visit++;
        }
        if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '') {
            $sum_pay += OCIResult($q,"RUB");
            if (OCIResult($q,"CALL_ID") != $call_id)
                $itog_pay++;
        }

        if (OCIResult($q,"SEL_ID") != $sel_id) {
            $sel_id = OCIResult($q,"SEL_ID");
            $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
            $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q,"SEL_NAME");
            //$sra_det_id = OCIResult($q,"SRA_DET_ID");
            //$usluga_auto_arr[$sel_id]['SRADETNAME'] = OCIResult($q,"SRADETNAME");
            $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[$source_type_id];
            if ((STATUS_CLINIC == $status_id || STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id) &&
                $interstate != 1 && $call_double != CALL_SECOND) {
                $usluga_auto_arr[$sel_id]['CALL'] = (DEVICE_PHONE == $source_type_id ? 1 : 0);
                $usluga_auto_arr[$sel_id]['MAIL'] = (DEVICE_MAIL == $source_type_id ? 1 : 0);
            }
            else {
                $usluga_auto_arr[$sel_id]['CALL'] = 0;
                $usluga_auto_arr[$sel_id]['MAIL'] = 0;
            }
            if (STATUS_CLINIC == $status_id)
                $usluga_auto_arr[$sel_id]['WRITE'] = 1;
            else $usluga_auto_arr[$sel_id]['WRITE'] = 0;

            $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = 0;
            $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = 0;
            $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 0;
            if ($date_visit != NULL && $date_visit != '' &&
                (STATUS_CLINIC == $status_id /*|| STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id*/))
                $usluga_auto_arr[$sel_id]['VISIT'] = 1;
            else $usluga_auto_arr[$sel_id]['VISIT'] = 0;
            if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '' &&
                (STATUS_CLINIC == $status_id /*|| STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id*/)) {
                $usluga_auto_arr[$sel_id]['SUM'] = OCIResult($q,"RUB");
                $usluga_auto_arr[$sel_id]['PAY'] = 1;
            }
            else {
                $usluga_auto_arr[$sel_id]['SUM'] = 0;
                $usluga_auto_arr[$sel_id]['PAY'] = 0;
            }
        }
        else {
            if (OCIResult($q,"CALL_ID") != $call_id) {
                if ((STATUS_CLINIC == $status_id || STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id) &&
                    $interstate != 1 && $call_double != CALL_SECOND) {
                    if (DEVICE_PHONE == $source_type_id)
                        $usluga_auto_arr[$sel_id]['CALL']++;
                    else $usluga_auto_arr[$sel_id]['MAIL']++;
                }
                if (STATUS_CLINIC == $status_id)
                    $usluga_auto_arr[$sel_id]['WRITE']++;
                if ($date_visit != NULL && $date_visit != '' &&
                    (STATUS_CLINIC == $status_id /*|| STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id*/))
                    $usluga_auto_arr[$sel_id]['VISIT']++;
            }
            if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '' &&
                (STATUS_CLINIC == $status_id /*|| STATUS_CLINIC_NOT == $status_id || STATUS_CALL_BACK == $status_id*/)) {
                $usluga_auto_arr[$sel_id]['SUM'] += OCIResult($q,"RUB");
                if (OCIResult($q,"CALL_ID") != $call_id) {
                    $usluga_auto_arr[$sel_id]['PAY']++;
                }
            }
        }
        $call_id = OCIResult($q, "CALL_ID");
    }
    oci_free_statement($q);

    // добавляем платежи на те же даты для заявок вне выбранного периода
    $itog_visit_interval = $itog_pay_interval = $sum_pay_interval = 0;
    $call_id = $sel_id = -1;
    $q_text1 = "SELECT cb.SOURCE_AUTO_ID as SEL_ID, cb.SOURCE_TYPE_ID, sr_a.NAME as SEL_NAME, ph.RUB, cb.ID as CALL_ID ";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN payment_hist ph ON ph.BASE_ID = cb.ID
        LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID ";
    $q_filt_pay = " and (DATE_PAYMENT between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1) ";
    $q_text5 = " ORDER BY SEL_NAME, SEL_ID, CALL_ID ";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_pay . $q_text5;
//var_dump($q_text);

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '') {
            $sum_pay_interval += OCIResult($q,"RUB");
            if (OCIResult($q,"CALL_ID") != $call_id)
                $itog_pay_interval++;
        }

        if (OCIResult($q,"SEL_ID") != $sel_id) {
            $sel_id = OCIResult($q, "SEL_ID");
            $source_type_id = OCIResult($q, "SOURCE_TYPE_ID");
            if (!isset($usluga_auto_arr[$sel_id])) {
                $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
                $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q,"SEL_NAME");
                $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[$source_type_id];
                $usluga_auto_arr[$sel_id]['CALL'] = 0;
                $usluga_auto_arr[$sel_id]['MAIL'] = 0;
                $usluga_auto_arr[$sel_id]['WRITE'] = 0;
                $usluga_auto_arr[$sel_id]['VISIT'] = 0;
                $usluga_auto_arr[$sel_id]['PAY'] = 0;
                $usluga_auto_arr[$sel_id]['SUM'] = 0;
                $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = 0;
            }
            if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '') {
                $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = OCIResult($q,"RUB");
                $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 1;
            }
            else {
                $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = 0;
                $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 0;
            }
        }
        else {
            if (OCIResult($q,"RUB") != NULL && OCIResult($q,"RUB") != '') {
                $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] += OCIResult($q,"RUB");
                if (OCIResult($q,"CALL_ID") != $call_id) {
                    $usluga_auto_arr[$sel_id]['PAY_INTERVAL']++;
                }
            }
        }
        $call_id = OCIResult($q, "CALL_ID");
    }
    oci_free_statement($q);

    // добавляем визиты в клинику на те же даты для заявок вне выбранного периода
    $q_text1 = "SELECT count(*) as COMING, cb.SOURCE_AUTO_ID as SEL_ID, cb.SOURCE_TYPE_ID, sr_a.NAME as SEL_NAME ";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN VISIT_HIST vh ON vh.BASE_ID = cb.ID
        LEFT JOIN SOURCE_AUTO sr_a ON cb.SOURCE_AUTO_ID = sr_a.ID ";
    //$q_filt_visit = " and (visit_date_1c between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1) ";
    $q_filt_visit = " and (date_visit between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1) ";
    $q_text5 = " GROUP BY SOURCE_AUTO_ID, cb.SOURCE_TYPE_ID, sr_a.NAME ";
    $q_text5 .= " ORDER BY SEL_NAME, SEL_ID ";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_visit . $q_text5;
//var_dump($q_text);

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $sel_id = OCIResult($q, "SEL_ID");
        $source_type_id = OCIResult($q, "SOURCE_TYPE_ID");
        if (!isset($usluga_auto_arr[$sel_id])) {
            $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
            $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q, "SEL_NAME");
            $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[$source_type_id];
            $usluga_auto_arr[$sel_id]['CALL'] = 0;
            $usluga_auto_arr[$sel_id]['MAIL'] = 0;
            $usluga_auto_arr[$sel_id]['WRITE'] = 0;
            $usluga_auto_arr[$sel_id]['VISIT'] = 0;
            $usluga_auto_arr[$sel_id]['PAY'] = 0;
            $usluga_auto_arr[$sel_id]['SUM'] = 0;
            $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 0;
            $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = 0;
        }
        $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = OCIResult($q,"COMING");
        $itog_visit_interval += OCIResult($q,"COMING");
    }
    oci_free_statement($q);

    if (EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
        $objPHPExcel->getProperties()
            ->setTitle(u8("Эффективность рекламы"))
            ->setSubject(u8("Эффективность рекламы"))
            ->setDescription(u8("Эффективность рекламы"))
            ->setKeywords(u8("Эффективность рекламы"))
            ->setCategory(u8("Эффективность рекламы"));

        fill_list(1, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $itog_call, $itog_mail, $itog_write,
            $itog_visit_interval, $itog_visit, $itog_pay_interval, $itog_pay, $sum_pay_interval, $sum_pay);

        $objSheet = clone $sheet[$snum];
        $objSheet->setTitle(u8("Sheet2"));
        $objPHPExcel->addSheet($objSheet);

        $snum2 = 2; //номер листа
        $objPHPExcel->setActiveSheetIndex($snum2 - 1);
        $sheet[$snum2] = $objPHPExcel->getActiveSheet();

        fill_list(2, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $itog_call, $itog_mail, $itog_write,
            $itog_visit_interval, $itog_visit, $itog_pay_interval, $itog_pay, $sum_pay_interval, $sum_pay);
    }
    else {
        $head_arr = array('0' => "", '1' => "Источник рекламы (Авто)", '2' => "Тип " . chr(10) . "источника",
            '3' => "Кол-во " . chr(10) . "принятых " . chr(10) . "обращений",
            '4' => "Кол-во " . chr(10) . "записанных",
            '5' => "Пришедшие " . chr(10) . "за период", '6' => "Пришедшие" . chr(10) . " из записанных",
            '7' => "Оплаченных " . chr(10) . "обращений" . chr(10) . " за период",
            '8' => "Оплаченных " . chr(10) . "обращений" . chr(10) . " из записанных",
            '9' => "Сумма оплаты " . chr(10) . "за период", '10' => "Сумма оплаты " . chr(10) . "из записанных",
            '11' => "Выручка с " . chr(10) . "обращения",
            '12' => "% записанных, " . chr(10) . "от принятых" . chr(10) . " обращений",
            '13' => "% оплаченных " . chr(10) . "обращений");
        if (EXPORT_EFFECT_ISH == $_POST['ReportId'])
            $head_arr['1'] = "Источник рекламы (комбо)";

        $remove_column = array(5, 6, 7, 8, 9, 10, 11, 13);
        $fin_column = array(7, 8, 9, 10, 11, 13);

        /* устанавливаем ячейкам стиль границ */
        $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

        // сначала третья строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
        $col = 0;
        foreach ($head_arr as $key => $val) {
            if (in_array(CAN_FINANCE, $data_acc_arr) || !in_array($key, $remove_column) || !in_array($key, $fin_column)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, 3)->getCoordinate(); //находим ячейку по координатам
                $sheet[$snum]->setCellValue($coord, u8($val));
            }
        }
        $highcol = $sheet[$snum]->getHighestColumn();
        $sheet[$snum]->getStyle('B3:' . $highcol . '3')->applyFromArray($styleArray);

        if (isset($_POST['ServiceId']) && !in_array(SERVICE_ALL, $_POST['ServiceId'])) {
            $second_row = '';
            foreach ($_POST['ServiceId'] as $key => $val) {
                $second_row .= SERVICE_LIST[$val] . ", ";
            }
            $second_row = substr($second_row, 0, -2);
        } else $second_row = "Все услуги";

        if ($rep_start_date != $rep_end_date)
            $second_row .= '. За период c ' . $rep_start_date . " по " . $rep_end_date;
        else $second_row .= '. За ' . $rep_start_date;
        $second_row .= ". На дату " . date('d.m.Y');

        $sheet[$snum]->getStyle('A3:' . $highcol . '3')->getFont()->setBold(true);
        $sheet[$snum]->getStyle('A3:' . $highcol . '3')->getAlignment()->setWrapText(true);
        $sheet[$snum]->getStyle('A3:' . $highcol . '3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet[$snum]->getRowDimension(3)->setRowHeight(45); // для заголовка

        $coord = $sheet[$snum]->getCellByColumnAndRow(1, 1)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (Выбор даты по всем столбцам)'));
        $sheet[$snum]->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

        $coord = $sheet[$snum]->getCellByColumnAndRow(1, 2)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8($second_row));
        $sheet[$snum]->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

        $rnum = 4; // Данные с четвертой строки
        usort($usluga_auto_arr, 'cmp_name');
        $tmp_row = 0;
        $tmp_name = '!!!!';
        $tmp_call = $tmp_write = $tmp_visit_i = $tmp_visit = $tmp_pay_i = $tmp_pay = $tmp_sum_i = $tmp_sum = 0;
        foreach ($usluga_auto_arr as $key => $value) { // отрисовываем полученные данные
            if (strncmp($value['NAME'], $tmp_name, 4) != 0) { // Промежуточные итоги
                if ($tmp_row > 1) {
                    $col = 3;
                    $rnum++; //номер строки
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
                    if (USER_VIEW != $_SESSION['user_role']) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                        if (in_array(CAN_FINANCE, $data_acc_arr)) {
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, $tmp_pay);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, $tmp_sum);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum / $tmp_call, 2) : 0));
                        }
                    }

                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_write / $tmp_call, 2) : 0));
                    if (in_array(CAN_FINANCE, $data_acc_arr)) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
                    }
                    $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->getFont()->setBold(true)->setSize(12);
                    $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->applyFromArray($styleArray);
                    $rnum++; //номер строки
                } elseif ($rnum != 4) {
                    $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->getFont()->setBold(true)->setSize(12);
                    $rnum++; //номер строки
                }
                $tmp_row = 0;
                $tmp_call = $tmp_write = $tmp_visit_i = $tmp_visit = $tmp_pay_i = $tmp_pay = $tmp_sum_i = $tmp_sum = 0;
            }
            $tmp_row++;
            $tmp_name = $value['NAME'];

            $col = 0;
            $rnum++; //номер строки
            $call_mail = $value['CALL'] + $value['MAIL'];
            //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, ($key > 1000 ? $key/1000 : $key)); // 0
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($value['SEL_ID'] > 1000 ? $value['SEL_ID'] / 1000 : $value['SEL_ID'])); // 0
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8($value['NAME'])); // 1
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8($value['TYPE'])); // 2
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, $call_mail); // 3 будет либо одно, либо другое
            $tmp_call += $value['CALL'] + $value['MAIL'];
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, $value['WRITE']); // 4
            $tmp_write += $value['WRITE'];
            if (USER_VIEW != $_SESSION['user_role']) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT_INTERVAL']); // 5
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT']); // 6
                $tmp_visit_i += $value['VISIT_INTERVAL'];
                $tmp_visit += $value['VISIT'];
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $value['PAY_INTERVAL']); // 7
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $value['PAY']); // 8
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $value['SUM_INTERVAL']); // 9
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $value['SUM']); // 10
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM'] / $call_mail, 2) : 0)); // 11
                    $tmp_pay_i += $value['PAY_INTERVAL'];
                    $tmp_sum_i += $value['SUM_INTERVAL'];
                    $tmp_pay += $value['PAY'];
                    $tmp_sum += $value['SUM'];
                }
            }
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['WRITE'] / $call_mail, 2) : 0)); // 12
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['PAY'] / $call_mail, 2) : 0)); // 13
            }
            $sheet[$snum]->getStyle('B' . $rnum . ':' . $highcol . $rnum)->applyFromArray($styleArray);
        }

        if ($tmp_row > 1) { // Промежуточные итоги для последнего блока
            $col = 3;
            $rnum++; //номер строки
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
            if (USER_VIEW != $_SESSION['user_role']) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $tmp_pay);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, $tmp_sum);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum / $tmp_call, 2) : 0));
                }
            }
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_write / $tmp_call, 2) : 0));
            if (USER_VIEW != $_SESSION['user_role'] && in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
            }
            $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->getFont()->setBold(true)->setSize(12);
            $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->applyFromArray($styleArray);
            $rnum++; //номер строки
        } elseif ($rnum != 4) {
            $sheet[$snum]->getStyle('D' . $rnum . ':' . $highcol . $rnum)->getFont()->setBold(true)->setSize(12);
            $rnum++; //номер строки
        }

        $col = 1;
        $rnum++; //номер строки
        $itog_call_mail = $itog_call + $itog_mail;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, u8('Итого:'));
        $col++;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_call + $itog_mail);
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_write);
        if (USER_VIEW != $_SESSION['user_role']) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit_interval);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit);
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, $itog_pay_interval);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, $itog_pay);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, $sum_pay_interval);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, $sum_pay);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round($sum_pay / $itog_call_mail, 2) : 0));
            }
        }
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_write / $itog_call_mail, 2) : 0));
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_pay / $itog_call_mail, 2) : 0));
        }
        $sheet[$snum]->getStyle('B' . $rnum . ':' . $highcol . $rnum)->applyFromArray($styleArray);

        if (USER_VIEW != $_SESSION['user_role']) {
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $sheet[$snum]->getStyle('D4:K'.$rnum)->getNumberFormat()->setFormatCode('#,##0');
                $sheet[$snum]->getStyle('L4:L'.$rnum)->getNumberFormat()->setFormatCode('#,##0.00');
            }
            else $sheet[$snum]->getStyle('D4:F'.$rnum)->getNumberFormat()->setFormatCode('#,##0');
        }

        $sheet[$snum]->getStyle('A1:' . $highcol . '3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet[$snum]->getStyle('A4:' . $highcol . $rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet[$snum]->getStyle('B4:B' . $rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet[$snum]->getStyle('A' . $rnum . ':' . $highcol . $rnum)->getFont()->setBold(true)->setSize(12);
        $sheet[$snum]->getStyle('B' . $rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet[$snum]->getColumnDimension('B')->setWidth(50); // для имени
        $sheet[$snum]->getColumnDimension('D')->setWidth(13);
        $sheet[$snum]->getColumnDimension('E')->setWidth(13);
        $sheet[$snum]->getColumnDimension('F')->setWidth(13);
        if (USER_VIEW != $_SESSION['user_role'] && in_array(CAN_FINANCE, $data_acc_arr)) {
            $sheet[$snum]->getColumnDimension('G')->setWidth(14);
            $sheet[$snum]->getColumnDimension('H')->setWidth(14);
            $sheet[$snum]->getColumnDimension('I')->setWidth(14);
            $sheet[$snum]->getColumnDimension('J')->setWidth(14);
            $sheet[$snum]->getColumnDimension('K')->setWidth(14);
            $sheet[$snum]->getColumnDimension('L')->setWidth(14);
            $sheet[$snum]->getColumnDimension('M')->setWidth(14);
        }

        $sheet[$snum]->getPageSetup()->setRowsToRepeatAtTop(3);
        //$sheet[$snum]->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(3,8);
    }
}

foreach($sheet as $s => $fuck) {
	$highcol=$sheet[$s]->getHighestColumn();
	$highrow=$sheet[$s]->getHighestRow();
	//устанавливаем автоширину столбцов на каждом листе
	for($i = 1; $i <= $highcol; $i++) {
	    $sheet[$s]->getColumnDimension($i)->setAutoSize(true);
    }
    if (EXPORT_EFFECT == $_POST['ReportId'] || EXPORT_EFFECT_ISH == $_POST['ReportId'] || EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
        $sheet[$s]->freezePane('A4');
        if (EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
            $sheet[$s]->mergeCells('A1:' . $highcol . '1');
            $sheet[$s]->mergeCells('A2:' . $highcol . '2');
        }
        else {
            $sheet[$s]->mergeCells('B1:' . $highcol . '1');
            $sheet[$s]->mergeCells('B2:' . $highcol . '2');
        }
        //$sheet[$s]->setAutoFilter('A3:'.$highcol.'3');
    }
    else {
        $sheet[$s]->freezePane('A3');
        $sheet[$s]->setAutoFilter('A2:'.$highcol.'2');
    }

    //$sheet[$s]->getStyle('B3:'.$highcol.$highrow)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
}

/*if(EXPORT_EFFECT_IDYN == $_POST['ReportId']) {
    $snum = count($sheet)+1; //номер листа

    $sheet[$snum]=$objPHPExcel->CreateSheet();
    //$objPHPExcel->addSheet($sheet[$snum]);

    //$objPHPExcel->setActiveSheetIndex($snum);

    //$sheet[$snum]=$objPHPExcel->getActiveSheet();
    $sheet[$snum]->setTitle(u8("Дебуг"));

    $sheet[$snum]->setCellValue('A1', u8($q_text));
}*/

$objPHPExcel->setActiveSheetIndex(0);
// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;