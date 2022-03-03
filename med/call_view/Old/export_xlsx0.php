<?php
ini_set('max_execution_time','900');
require_once 'med/check_auth.php';
//$sid=session_id();
extract($_REQUEST);

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit', '256M');

require_once 'med/adm_url.php';
require_once '../funct.php';

//$rep_start_date = (isset($rep_start_date) ? $rep_start_date : $rep_start_date);
//$rep_end_date = (isset($rep_end_date) ? $rep_end_date : $rep_end_date);

if (isset($all_type)) {
    $filename = "all-" . date("Ymd", strtotime($rep_start_date));
}
elseif (EXPORT_OPERATOR == $ReportId || EXPORT_OPERATOR_ALL == $ReportId || EXPORT_OPERATOR_SEC == $ReportId) {
    $filename = "oper-" . date("Ymd", strtotime($rep_start_date));
}
elseif (EXPORT_EFFECT == $ReportId || EXPORT_EFFECT_ISH == $ReportId || EXPORT_EFFECT_IDYN == $ReportId) {
    $filename = "effect-" . date("Ymd", strtotime($rep_start_date));
}
elseif (EXPORT_BILLING == $ReportId) {
    $filename = "billing-" . date("Ymd", strtotime($rep_start_date));
}
elseif (EXPORT_OPERATOR_SEC_CALL == $ReportId) {
    $filename = "stom2-" . date("Ymd", strtotime($rep_start_date));
}
else {
    $filename = "med-" . date("Ymd", strtotime($rep_start_date));
}
if ($rep_start_date != $rep_end_date)
    $filename .= "-" . date("Ymd", strtotime($rep_end_date));

if (isset($_SESSION['data_acc'])) // Права доступа к разным данным
    $data_acc_arr = explode(',', $_SESSION['data_acc']);
else $data_acc_arr = array();

// Общие фильтры
if (EXPORT_CALL == $ReportId || EXPORT_CALL_SEC == $ReportId ||
    EXPORT_BILLING == $ReportId || EXPORT_EFFECT == $ReportId ||
    EXPORT_EFFECT_ISH == $ReportId || EXPORT_EFFECT_IDYN == $ReportId) {
    /*$q_filt_interval = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1 or
    cb.DATE_CLOSE between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";*/
    if (2 == $DateType) {
        if (EXPORT_CALL_SEC == $ReportId)
            $q_filt_interval = " and (cb.SECOND_LAST_CHANGE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        else $q_filt_interval = " and (cb.LAST_CHANGE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
    } else {
        if (EXPORT_CALL_SEC == $ReportId)
            $q_filt_interval = " and (cb.DATE_SECOND_CHANCE between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
        else $q_filt_interval = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
    }

    $q_text4 = " WHERE (1=1"; // !!! Лишняя скобка! В запросах надо добавлять закрывающую! !!!
    if (isset($UserId) && !in_array('-1', $UserId)) {
        if (EXPORT_CALL_SEC == $ReportId)
            $q_text4 .= " and cb.SECOND_FIO_ID in (" . implode(',', $UserId) . ")";
        else $q_text4 .= " and cb.FIO_ID in (" . implode(',', $UserId) . ")";
    }

    if (EXPORT_CALL_SEC == $ReportId) {
        if (isset($StatusId) && !in_array('-1', $StatusId))
            $q_text4 .= " and cb.SECOND_STATUS_ID in (" . implode(',', $StatusId) . ")";
        else $q_text4 .= " and cb.SECOND_STATUS_ID between " . STATUS_OPEN . " and " . STATUS_NOT_COME;

        if (isset($StatusId) && in_array(STATUS_ERROR, $StatusId) && count($StatusId) == 1) {
            if (isset($status_det) && !in_array('-1', $status_det))
                $q_text4 .= " and cb.SECOND_STATUS_DET_ID in (" . implode(',', $status_det) . ")";
        }
    } else {
        if (isset($StatusId) && !in_array('-1', $StatusId))
            $q_text4 .= " and cb.STATUS_ID in (" . implode(',', $StatusId) . ")";
        else $q_text4 .= " and cb.STATUS_ID between " . STATUS_OPEN . " and " . STATUS_NOT_COME;

        if (isset($StatusId) && in_array(STATUS_ERROR, $StatusId) && count($StatusId) == 1) {
            if (isset($status_det) && !in_array('-1', $status_det))
                $q_text4 .= " and cb.STATUS_DET_ID in (" . implode(',', $status_det) . ")";
            //else $q_text4 .= " and cb.STATUS_DET_ID between " . STAT_ERR_APPL . " and " . STAT_ERR_INTER; // 801 - 807
        }
    }
    //else $q_text4 .= " and cb.STATUS_DET_ID between " . STAT_ERR_APPL . " and " . STAT_ERR_INTER; // 801 - 807
    //else $q_text4 .= " and cb.STATUS_ID between ".STATUS_CALL_STOP." and ".STATUS_BREAK_LINE;

    if (EXPORT_CALL_SEC == $ReportId) {
        $q_text4 .= " and cb.CALL_TYPE_ID = ".CALL_SECOND;
    } else {
        if (isset($ServiceId) && !in_array(SERVICE_ALL, $ServiceId)) {
            $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $ServiceId) . ")";
        }
        if (USER_ADMIN != $_SESSION['user_role']) {
            $q_text4 .= " and cb.SERVICE_ID in ( 
        select decode(ad.service_id,-1,cb.service_id,ad.service_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
        }
    }

    if (isset($S_Type) && -1 != $S_Type) {
        $q_text4 .= " and cb.SOURCE_TYPE_ID = " . $S_Type;
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_TYPE_ID in ( 
        select decode(ad.source_type_id,-1,cb.source_type_id,ad.source_type_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }

    /*if (isset($Reservoir) && !in_array(SOURCE_ALL, $Reservoir)) {
        $q_text4 .= " and cb.SOURCE_MAN_ID in (" . implode(',', $Reservoir) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_MAN_ID in (
        select decode(ad.source_man_id,-1,cb.source_man_id,ad.source_man_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . ")";
    }*/

    if (isset($S_Auto) && !in_array(SOURCE_ALL, $S_Auto)) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in (" . implode(',', $S_Auto) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in ( 
        select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }
    if (isset($not_sent)) // только неотправленные поставщику ошибочные звонки
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
    if (strcmp($a['NAME'], $b['NAME']) != 0) // DET_NAME ???
        return strcmp($a['NAME'], $b['NAME']);
    else {
        if ($a['SEL_ID'] == $b['SEL_ID']) {
            return 0;
        }
        return ($a['SEL_ID'] < $b['SEL_ID']) ? -1 : 1;
    }
}

function fill_list($num_list, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $ReportId)
{
    if (0 == $num_list) $snum = 1; //номер листа
    else $snum = $num_list;
    $objPHPExcel->setActiveSheetIndex($snum-1);
    $sheet[$snum]=$objPHPExcel->getActiveSheet();

    if (isset($_SESSION['data_acc'])) // Права доступа к разным данным
        $data_acc_arr = explode(',', $_SESSION['data_acc']);
    else $data_acc_arr = array();

    if (1 == $num_list) {
        $head_arr = array('0' => "Источник рекламы (Авто)",
            '1' => "Тип " . chr(10) . "источника",
            '2' => "Принято " . chr(10) . "обращений",
            //'3'=>"Из них ".chr(10)."записано",
            '4' => "Пришло " . chr(10) . "из принятых",
            '5' => "Оплачено " . chr(10) . "из принятых",
            '6' => "Сумма проплат" . chr(10) . "из принятых",
            '7' => "Выручка с " . chr(10) . "обращения",
            '8' => "% пришедших " . chr(10) . "от принятых",
            '9' => "% оплаченных " . chr(10) . "от принятых",
            '10' => "Оплата " . chr(10) . "обращений",
            '11' => "Оплата " . chr(10) . "визитов",
            '12' => "Доход " . chr(10) . "за период");
        $fin_column = array(5, 6, 7, 9, 10, 11, 12);
    }
    elseif (2 == $num_list) {
        $head_arr = array('0' => "Источник рекламы (Авто)",
            '1' => "Тип " . chr(10) . "источника",
            '2' => "Принято " . chr(10) . "обращений",
            //'3'=>"Записано ".chr(10)."за период",
            '4' => "Пришло " . chr(10) . "за период",
            '5' => "Оплачено " . chr(10) . " за период",
            '6' => "Сумма проплат " . chr(10) . "за период",
            '7' => "Выручка с " . chr(10) . "обращения",
            '8' => "% пришедших " . chr(10) . "от принятых",
            '9' => "% оплаченных " . chr(10) . "от принятых",
            '10' => "Оплата " . chr(10) . "обращений",
            '11' => "Оплата " . chr(10) . "визитов",
            '12' => "Доход " . chr(10) . "за период");
        $fin_column = array(5, 6, 7, 9, 10, 11, 12);
    }
    else { // 0
        $head_arr = array('0'=>"Источник рекламы (Авто)", 
			'1'=>"Детализация", 
			'2'=>"Тип ".chr(10)."источника",
            '3' => "Принято ".chr(10)."обращений",
			//'3'=>"Из них ".chr(10)."записано",
            '4' => "Пришло ".chr(10)."из принятых",
			'5' => "Пришло ".chr(10)."за период",
            '6' => "Оплачено ".chr(10)."из принятых",
            '7' => "Оплачено ".chr(10)."за период",
            '8' => "Сумма проплат " . chr(10) . "из принятых",
            '9' => "Сумма проплат " . chr(10) . "за период",
            '10' => "Выручка с " . chr(10) . "обращения",
            //'11' => "% пришедших " . chr(10) . "от принятых",
            //'12' => "% оплаченных " . chr(10) . "от принятых",
            '11' => "% пришедших " . chr(10) . "за период",
            '12' => "% оплаченных " . chr(10) . "за период",
            '13' => "Оплата " . chr(10) . "обращений",
            '14' => "Оплата " . chr(10) . "визитов",
            '15' => "Доход " . chr(10) . "за период");
        /*if (EXPORT_EFFECT_ISH == $ReportId)
            $head_arr['0'] = "Источник рекламы (комбо)";*/
        $fin_column = array(6, 7, 8, 9, 10, 12, 13, 14, 15);
    }

    /* устанавливаем ячейкам стиль границ */
    $styleArray = array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)));

    // сначала третья строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach($head_arr as $key=>$val) { // $key не используется, колонки друг за другом
        if (in_array(CAN_FINANCE, $data_acc_arr) || !in_array($key, $fin_column))
        {
            if ($key == 1 && EXPORT_EFFECT == $ReportId) continue;
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

    if (isset($ServiceId) && !in_array(SERVICE_ALL, $ServiceId)) {
        $second_row = '';
        foreach($ServiceId as $key=>$val) {
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
        $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (По дате обращения)'));
    elseif (2 == $num_list)
        $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы (За период)'));
    else $sheet[$snum]->setCellValue($coord, u8('Эффективность источников рекламы'));

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
    $itog_call_mail = $itog_visit = $itog_pay = $sum_pay = $itog_visit_i = $itog_pay_i = $sum_pay_i = 0;
    $tmp_order_pay=$itog_order_pay=$tmp_visit_pay=$itog_visit_pay=$tmp_dohod=$itog_dohod=0;
    foreach ($usluga_auto_arr as $key =>$value) { // отрисовываем полученные данные, кроме строк с нулевыми значениями
        if (0 == $value['CALL'] && 0 == $value['VISIT'] && 0 == $value['VISIT_INTERVAL'] &&
            0 == $value['PAY'] && 0 == $value['PAY_INTERVAL'] && 0 == $value['SUM'] && 0 == $value['SUM_INTERVAL'])
            continue;
        if (strncmp($value['NAME'], $tmp_name, 4) != 0) { // Промежуточные итоги
            if ($tmp_row > 1) {
                $col = 2;
                if (EXPORT_EFFECT_ISH == $ReportId) $col++;

                $rnum++; //номер строки
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
                //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
                if (in_array(CAN_FINANCE, $data_acc_arr) || 0 == $num_list) {
                    if (1 == $num_list) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum / $tmp_call, 2) : 0));
                    } elseif (2 == $num_list) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i / $tmp_call, 2) : 0));
                    } else {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                        if (in_array(CAN_FINANCE, $data_acc_arr)) {
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i / $tmp_call, 2) : 0));
                            /*$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i / $tmp_call, 2) : 0));*/
                        }
                    }
                }

                if (1 == $num_list /*|| 0 == $num_list*/) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit / $tmp_call, 2) : 0));
                    if (in_array(CAN_FINANCE, $data_acc_arr)) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
                    }
                } else {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit_i / $tmp_call, 2) : 0));
                    if (in_array(CAN_FINANCE, $data_acc_arr)) {
                        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                        $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay_i / $tmp_call, 2) : 0));
                    }
                }
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_order_pay);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_pay);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_dohod);
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
            $tmp_visit_pay=$tmp_order_pay=$tmp_dohod=0;
        }
        $tmp_row++;
        $tmp_name = $value['NAME'];

        $col = 0;
        $rnum++; //номер строки
        $call_mail = $value['CALL'];//+$value['MAIL'];
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['NAME'])); // 0
        if (EXPORT_EFFECT_ISH == $ReportId) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['DET_NAME'])); // 0
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($value['TYPE'])); // 1
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $call_mail); // 2 будет либо одно, либо другое
        $tmp_call += $value['CALL'];//+$value['MAIL'];
        $itog_call_mail += $value['CALL'];//+$value['MAIL'];

        //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['WRITE']); // 3
        $tmp_write += $value['WRITE'];
        if (in_array(CAN_FINANCE, $data_acc_arr) || 0 == $num_list) {
            if (1 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT']); // 4
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY']); // 5
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM']); // 6
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM']/$call_mail,2) : 0)); // 7
                $tmp_visit += $value['VISIT'];
                $tmp_pay += $value['PAY'];
                $tmp_sum += $value['SUM'];
                $itog_visit += $value['VISIT'];
                $itog_pay += $value['PAY'];
                $sum_pay += $value['SUM'];
            } elseif (2 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT_INTERVAL']); // 4
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY_INTERVAL']); // 5
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM_INTERVAL']); // 6
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM_INTERVAL']/$call_mail,2) : 0)); // 7
                $tmp_visit_i += $value['VISIT_INTERVAL'];
                $tmp_pay_i += $value['PAY_INTERVAL'];
                $tmp_sum_i += $value['SUM_INTERVAL'];
                $itog_visit_i += $value['VISIT_INTERVAL'];
                $itog_pay_i += $value['PAY_INTERVAL'];
                $sum_pay_i += $value['SUM_INTERVAL'];
            } else {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT']); // 4
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['VISIT_INTERVAL']); // 4
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY']); // 5
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['PAY_INTERVAL']); // 5
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM']); // 6
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $value['SUM_INTERVAL']); // 6
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM_INTERVAL'] / $call_mail, 2) : 0)); // 7
                    /*$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round($value['SUM_INTERVAL']/$call_mail,2) : 0)); // 7*/
                }
                $tmp_visit += $value['VISIT']; $tmp_visit_i += $value['VISIT_INTERVAL'];
                $tmp_pay += $value['PAY']; $tmp_pay_i += $value['PAY_INTERVAL'];
                $tmp_sum += $value['SUM']; $tmp_sum_i += $value['SUM_INTERVAL'];
                $itog_visit += $value['VISIT']; $itog_visit_i += $value['VISIT_INTERVAL'];
                $itog_pay += $value['PAY']; $itog_pay_i += $value['PAY_INTERVAL'];
                $sum_pay += $value['SUM']; $sum_pay_i += $value['SUM_INTERVAL'];
            }
        }

        if (1 == $num_list /*|| 0 == $num_list*/) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['VISIT'] / $call_mail, 2) : 0));
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['PAY'] / $call_mail, 2) : 0)); // 8
            }
        } else {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['VISIT_INTERVAL'] / $call_mail, 2) : 0));
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100 * $value['PAY_INTERVAL'] / $call_mail, 2) : 0)); // 8
            }
        }

        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $dohod = $value['SUM'];
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            if (is_numeric($value['COST_ORDER'])) {
                $sheet[$snum]->setCellValue($coord, $value['CALL']*$value['COST_ORDER']);
                $tmp_order_pay += $value['CALL']*$value['COST_ORDER'];
                $itog_order_pay += $value['CALL']*$value['COST_ORDER'];
                $dohod -= $value['CALL']*$value['COST_ORDER'];
            } //
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            if (is_numeric($value['COST_VISIT'])) {
                $sheet[$snum]->setCellValue($coord, $value['VISIT']*$value['COST_VISIT']);
                $tmp_visit_pay += $value['VISIT']*$value['COST_VISIT'];
                $itog_visit_pay += $value['VISIT']*$value['COST_VISIT'];
                $dohod -= $value['VISIT']*$value['COST_VISIT'];
            } //
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, $dohod); //
            $tmp_dohod += $dohod;
            $itog_dohod += $dohod;
        }
        //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
        //$sheet[$snum]->setCellValue($coord, ($call_mail != 0 ? round(100*$value['WRITE']/$call_mail,2) : 0)); // 8

        $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
    }

    if ($tmp_row > 1) { // Промежуточные итоги для последнего блока
        $col = 2;
        if (EXPORT_EFFECT_ISH == $ReportId) $col++;
        $rnum++;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_call);
        //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_write);
        if (in_array(CAN_FINANCE, $data_acc_arr) || 0 == $num_list) {
            if (1 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum/$tmp_call,2) : 0));
            } elseif (2 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i/$tmp_call,2) : 0));
            } else { // 0
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit);
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_i);
                if (in_array(CAN_FINANCE, $data_acc_arr)) {
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_pay_i);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_sum_i);
                    $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i / $tmp_call, 2) : 0));
                    /*$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                    $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round($tmp_sum_i/$tmp_call,2) : 0));*/
                }
            }
        }
        if (1 == $num_list /*|| 0 == $num_list*/) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit / $tmp_call, 2) : 0));
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay / $tmp_call, 2) : 0));
            }
        } else {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_visit_i / $tmp_call, 2) : 0));
            if (in_array(CAN_FINANCE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, ($tmp_call != 0 ? round(100 * $tmp_pay_i / $tmp_call, 2) : 0));
            }
        }
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_order_pay);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_visit_pay);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $tmp_dohod);
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

    // Итоговая строка
    $col = 0;
    $rnum++; //номер строки
    $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8('Итого:'));
    $col++; // сдвиг
    if (EXPORT_EFFECT_ISH == $ReportId) $col++;
    $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_call_mail);
    //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_write);
    if (1 == $num_list) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit);
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay);
        }
    } elseif (2 == $num_list) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit_i);
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay_i);
        }
    } else {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit);
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit_i);
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_pay_i);
        }
    }

    if (1 == $num_list) {
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay);
            /*if (0 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay_i);
            }*/
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round($sum_pay / $itog_call_mail, 2) : 0));
        }
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_visit / $itog_call_mail, 2) : 0));
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_pay / $itog_call_mail, 2) : 0));
        }
    }
    elseif (2 == $num_list || 0 == $num_list) {
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            if (0 == $num_list) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay);
            }
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $sum_pay_i);
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round($sum_pay_i / $itog_call_mail, 2) : 0));
        }
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_visit_i / $itog_call_mail, 2) : 0));
        if (in_array(CAN_FINANCE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100 * $itog_pay_i / $itog_call_mail, 2) : 0));
        }
    }
    if (in_array(CAN_FINANCE, $data_acc_arr)) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_order_pay);
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_visit_pay);
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, $itog_dohod);
    }
    //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate();
    //$sheet[$snum]->setCellValue($coord, ($itog_call_mail != 0 ? round(100*$itog_write/$itog_call_mail,2) : 0));

    $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->applyFromArray($styleArray);
    $sheet[$snum]->getStyle('A'.$rnum.':'.$highcol.$rnum)->getFont()->setBold(true)->setSize(12);

    if (in_array(CAN_FINANCE, $data_acc_arr)) {
        if (0 != $num_list) {
            $sheet[$snum]->getStyle('C4:G' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
            $sheet[$snum]->getStyle('H4:I' . $rnum)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet[$snum]->getStyle('K4:M' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
        } else {
            if (EXPORT_EFFECT_ISH == $ReportId) {
                $sheet[$snum]->getStyle('D4:K' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
                $sheet[$snum]->getStyle('L4:M' . $rnum)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet[$snum]->getStyle('N4:P' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
            } else {
                $sheet[$snum]->getStyle('C4:J' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
                $sheet[$snum]->getStyle('K4:L' . $rnum)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet[$snum]->getStyle('M4:O' . $rnum)->getNumberFormat()->setFormatCode('#,##0');
            }
        }
    }
    else $sheet[$snum]->getStyle('C4:E'.$rnum)->getNumberFormat()->setFormatCode('#,##0');

    $sheet[$snum]->getStyle('A1:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet[$snum]->getStyle('A4:A'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet[$snum]->getStyle('A'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    if (EXPORT_EFFECT_ISH == $ReportId)
        $sheet[$snum]->getStyle('C4:C'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    else $sheet[$snum]->getStyle('B4:B'.$rnum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    $sheet[$snum]->getColumnDimension('A')->setWidth(50); // для имени
    if (EXPORT_EFFECT_ISH == $ReportId)
        $sheet[$snum]->getColumnDimension('B')->setWidth(35); // детализация. и все бы строки ниже сдвинуть на одну
    else $sheet[$snum]->getColumnDimension('B')->setWidth(12);
    $sheet[$snum]->getColumnDimension('C')->setWidth(13);
    $sheet[$snum]->getColumnDimension('D')->setWidth(13);
    $sheet[$snum]->getColumnDimension('E')->setWidth(14);
    $sheet[$snum]->getColumnDimension('F')->setWidth(15);
    $sheet[$snum]->getColumnDimension('G')->setWidth(14);
    $sheet[$snum]->getColumnDimension('H')->setWidth(14);
    $sheet[$snum]->getColumnDimension('I')->setWidth(15);
    if (EXPORT_EFFECT_ISH == $ReportId)
        $sheet[$snum]->getColumnDimension('J')->setWidth(15);

    $sheet[$snum]->getPageSetup()->setRowsToRepeatAtTop(3);
}

// Экспорт заявок
if (EXPORT_CALL == $ReportId || EXPORT_CALL_SEC == $ReportId) {
    $q_text1 = "SELECT cb.ID, cb.secret, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL, cb.ANUMBER, cb.BNUMBER, cb.SC_AGID, cb.SC_CALL_ID, cb.SC_PROJECT_ID,
them.NAME as THEME, cb.SERVICE_ID, serv.NAME as SRVNAME, serv_det.NAME as SERV_DET, cb.SOURCE_TYPE_ID, sr_a.NAME as SRANAME, sr_man.NAME as SRMNAME, 
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
cb.CLIENT_NAME, cb.PHONE_MOB, cb.COMMENTS, stat.NAME as STATUS, stat_det.NAME as STATUS_DET, 
usr.FIO as FIO, cb.CALL_DOUBLE, cb.INTERSTATE, cb.OKTELL_IDCHAIN, 
to_char(cb.ENTRY_DATE_1C,'dd.mm.yyyy hh24:mi') ENTRY_DATE_1C,";
    if (EXPORT_CALL_SEC == $ReportId)
        $q_text1 .= "cb.SECOND_STATUS_ID as STATUS_ID, to_char(cb.SECOND_LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.DATE_SECOND_CLOSE,'dd.mm.yyyy') DATE_CLOSE";
    else $q_text1 .= "cb.STATUS_ID, to_char(cb.LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') LAST_CHANGE, to_char(cb.DATE_CLOSE,'dd.mm.yyyy') DATE_CLOSE";
    $q_text2 = " FROM CALL_BASE cb ";
    $q_text3 = " LEFT JOIN CALL_THEME them ON them.ID = cb.CALL_THEME_ID
    LEFT JOIN SERVICES serv ON serv.ID = cb.SERVICE_ID
    LEFT JOIN SERVICE_DET serv_det ON serv_det.ID = cb.SERVICE_DET_ID 
    LEFT JOIN SOURCE_AUTO sr_a ON sr_a.ID = cb.SOURCE_AUTO_ID
    LEFT JOIN SOURCE_MAN sr_man ON sr_man.ID = cb.SOURCE_MAN_ID
    LEFT JOIN SUBWAYS metro ON metro.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN HOSPITALS hosp_det ON hosp_det.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_MAN_DETAIL srd ON srd.ID = cb.SOURCE_MAN_DET_ID
    LEFT JOIN SOURCE_AUTO_DETAIL sra_det ON sra_det.ID = cb.SOURCE_MAN_ID_NEW
    LEFT JOIN HOSPITALS hosp ON hosp.ID = cb.RESULT_DET ";
    if (EXPORT_CALL_SEC == $ReportId)
        $q_text3 .= "LEFT JOIN MED_STATUS stat ON stat.ID = cb.SECOND_STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.SECOND_STATUS_DET_ID
    LEFT JOIN USERS usr ON usr.ID = cb.SECOND_FIO_ID";
    else $q_text3 .= "LEFT JOIN MED_STATUS stat ON stat.ID = cb.STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.STATUS_DET_ID
    LEFT JOIN USERS usr ON usr.ID = cb.FIO_ID";
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

    if (isset($all_type)) // остальные фильтры формируются в начале файла
        $q_text4 .= " or (call_theme_id > " . THEME_MED .
"  and cb.source_auto_id in
    (select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id)
     from USER_DEP_ALLOC uda, ACCESS_DEP ad where ad.departament_id=uda.dep_id 
     and uda.deleted is NULL and uda.user_id=".$_SESSION['login_id_med'].") )";

    if (isset($S_Auto) && !in_array(SOURCE_ALL, $S_Auto)) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in (" . implode(',', $S_Auto) . ")";
    }
    if (USER_ADMIN != $_SESSION['user_role']) {
        $q_text4 .= " and cb.SOURCE_AUTO_ID in ( 
        select decode(ad.source_auto_id,-1,cb.source_auto_id,ad.source_auto_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
    }
    $q_text4 .= ")"; // !!! закрывающая скобка

    $q_text5 = " ORDER BY cb.DATE_CALL, cb.CALL_BACK_DATE, serv.NAME, sr_a.NAME, sr_man.NAME";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_interval . $q_text5;
//echo "<br><textarea>".$q_text."</textarea><br>";

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);

    $head_arr = array(0=>"№ ".chr(10)."заявки", 1=>"Дата ".chr(10)."заявки", 2=>"ID звонка",
        3=>"Проект", 4=>"ANumber", 5=>"Оператор", 6=>"Тема", 7=>"Услуга",
        8=>"Тип ".chr(10)."источника", 9=>"BNumber", 10=>"Источник (Авто)",
        11=>"Источник".chr(10)." (вход.)", 12=>"Детализация".chr(10)." источника (вход.)",
        13=>"Источник".chr(10)." (исх.)", 14=>"Результат".chr(10)." входящего", 15=>"Детализация".chr(10)." результата",
        16=>"ФИО", 17=>"Контактный".chr(10)." телефон", 18=>"Статус", 19=>"Уточнение".chr(10)." ошибки",
        20=>"Комментарий".chr(10)." вход", 21=>"Комментарий".chr(10)." последний",
        22=>"Назначено", 23=>"Клиника", 24=>"Записан", 25=>"Контакт", 26=>"Дата".chr(10)." записи", 27=>"Итог",
        28=>"Время".chr(10)." события", 29=>"Закрыто", 30=>"ID Цепочки", 31=>"Запись".chr(10)." звонка");
    if (EXPORT_CALL_SEC == $ReportId) { // убираем столбцы с записью разговора
        array_pop($head_arr);
        array_pop($head_arr);
    }
    $remove_column = array(0,2,3,4,5,9,11,12,13,14,15,22,23,24,25,26,27,30,31);
    $gus_column = array(5,12,13,15,22,23,24,25,26,27,28,29,30,31); // убираем у Гусарова
    $contact_column = array(4,17,25); // убираем номера телефонов, кому нет к ним доступа.

    // сначала вторая строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach($head_arr as $key=>$val) {
        //$cnum=$key; //номер столбца
        //$coord=$sheet[$snum]->getCellByColumnAndRow($cnum,2)->getCoordinate(); //находим ячейку по координатам
        if ((IT_PLANET != $_SESSION['login_id_med'] || !in_array($key, $remove_column)) &&
            ($data_acc_arr && in_array(CAN_HEAR, $data_acc_arr) || (30 != $key && 31 != $key))) { // ограничение для прослушивания записей
            if (in_array($_SESSION['login_id_med'],EXPORT_CUT) && in_array($key, $gus_column) ||
                !in_array(CAN_PHONE, $data_acc_arr) && in_array($key, $contact_column) ||
                (109 == $_SESSION['login_id_med'] || 113 == $_SESSION['login_id_med']) && 27 == $key) { // Убираем у Кокоса
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
        $service_id = OCIResult($q, "SERVICE_ID");
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

        $col = 0;
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($base_id));
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "DATE_CALL") ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_CALL_ID")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_PROJECT_ID")));
            if (in_array(CAN_PHONE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "ANUMBER")));
            }
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SC_AGID")));
            }
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "THEME") ));
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
        if (SERVICE_GINE != $service_id) {
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRVNAME") . " / " . OCIResult($q, "SERV_DET")));
        } else {
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRVNAME")));
        }
        //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SERV_DET")));
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( DEVICES[OCIResult($q, "SOURCE_TYPE_ID")] ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "BNUMBER")));
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SRANAME") ));
        if (IT_PLANET != $_SESSION['login_id_med']) { // ограничение для IT Planet
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRMNAME")));
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SOURCE_MAN_DET")));
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "SRADETNAME")));
            }
            //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SRMNAME_NEW") ));
            //$coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "SOURCE_MAN_DET_NEW") ));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "RESULT")));
            if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "RESULT_DET")));
            }
        }
        $coord=$sheet[$snum]->getCellByColumnAndRow($col++,$rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8( OCIResult($q, "CLIENT_NAME") ));
        if (in_array(CAN_PHONE, $data_acc_arr)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "PHONE_MOB")));
        }
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
            $q_clinic = OCIParse($c, "SELECT (hosp.CITY || '-' || hosp.NAME) AS HOSP_NAME, 
CLIENT_NAME, CLIENT_PHONE, CLIENT_STATUS, to_char(CLIENT_DATE,'dd.mm.yyyy hh24:mi:ss') CLIENT_DATE FROM CALL_BASE_CLINIC 
LEFT JOIN HOSPITALS hosp ON HOSPITAL_ID = hosp.ID WHERE BASE_ID=:id");
            OCIBindByName($q_clinic, ":id", $base_id);
            OCIExecute($q_clinic, OCI_DEFAULT);
            OCIFetch($q_clinic);
            $hospital = OCIResult($q_clinic, "HOSP_NAME");
            $clinic_client_name = OCIResult($q_clinic, "CLIENT_NAME");
            $clinic_client_phone = OCIResult($q_clinic, "CLIENT_PHONE");
            //$clinic_client_status = OCIResult($q_clinic, "CLIENT_STATUS");
            $clinic_client_date = OCIResult($q_clinic, "CLIENT_DATE");
            $clinic_surname = substr($clinic_client_name, 0, strpos($clinic_client_name, '/'));
            $clinic_name = substr($clinic_client_name, strpos($clinic_client_name, '/') + 1, strrpos($clinic_client_name, '/') - strpos($clinic_client_name, '/') - 1);
            $clinic_patronymic = substr($clinic_client_name, strripos($clinic_client_name, '/') + 1, strlen($clinic_client_name));
            //$nrowhosp = GetData::GetHospitals("hosp.ID = ". $hospital);

            //$date_write = OCIResult($q, "ENTRY_DATE_1C"); теперь есть таблица с историей перезаписи...
            $q_write = OCIParse($c, "SELECT to_char(DATE_WRITE,'dd.mm.yyyy hh24:mi') DATE_WRITE FROM WRITE_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");
            OCIBindByName($q_write, ":id", $base_id);
            $date_write = '';
            if (OCIExecute($q_write, OCI_DEFAULT) && OCIFetch($q_write))
                $date_write = OCIResult($q_write, "DATE_WRITE");
            if ('' == $date_write) $date_write = $clinic_client_date;

            $q_visit = OCIParse($c, "SELECT to_char(DATE_VISIT,'dd.mm.yyyy hh24:mi') DATE_VISIT FROM VISIT_HIST WHERE BASE_ID=:id ORDER BY DATE_ADD desc");
            OCIBindByName($q_visit, ":id", $base_id);
            $date_visit = '';
            if (OCIExecute($q_visit, OCI_DEFAULT) && OCIFetch($q_visit))
                $date_visit = OCIResult($q_visit, "DATE_VISIT");

            $date = date("d.m.Y");
            if ($date_visit != '')
                $visit = 'Пришел';
            else {
                if ($date_write != '') {
                    if (strtotime($date_write) >= strtotime($date))
                        $visit = 'Ждем';
                    elseif (strtotime($date_write) < strtotime($date))
                        $visit = 'Не пришел';
                }
                else $visit = '';
            }

            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "FIO")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($hospital));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($clinic_surname . ' ' . $clinic_name . ' ' . $clinic_patronymic));
            if (in_array(CAN_PHONE, $data_acc_arr)) {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($clinic_client_phone));
            }
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($date_write));
            if (109 != $_SESSION['login_id_med'] && 113 != $_SESSION['login_id_med']) { // Убираем у Кокоса
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8($visit));
            }
        }
        if (!in_array($_SESSION['login_id_med'],EXPORT_CUT)) {
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "LAST_CHANGE")));
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "DATE_CLOSE")));
        }

        if (/*EXPORT_CALL_SEC != $ReportId &&*/ $data_acc_arr && in_array(CAN_HEAR, $data_acc_arr)) { // ограничение для прослушивания записей
            $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
            $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "OKTELL_IDCHAIN")));
            //if (DEVICE_PHONE == OCIResult($q, "SOURCE_TYPE_ID")) {
			if(OCIResult($q, "SECRET")<>'') {
                $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate();
                $sheet[$snum]->setCellValue($coord, u8('Ссылка'));
                //$sheet[$snum]->setCellValue($coord, "<a href=".$oktell_records_url . "?idchain=" . OCIResult($q, "OKTELL_IDCHAIN").">123</a>");
				//$sheet[$snum]->getCell($coord)->getHyperlink()->setUrl($oktell_records_url . "?idchain=" . OCIResult($q, "OKTELL_IDCHAIN"));
                $sheet[$snum]->getCell($coord)->getHyperlink()->setUrl($oktell_records_url . "?baseid=" . OCIResult($q, "ID")."&secret=".OCIResult($q, "SECRET"));
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
// Обработанные заявки операторов второго шанса
elseif (EXPORT_OPERATOR_SEC_CALL == $ReportId) {
    $col = 0;
    $head_arr = array($col=>"№ ".chr(10)."заявки", ++$col=>"Дата ".chr(10)."заявки",
        ++$col=>"Источник", ++$col=>"АОН", ++$col=>"Телефон",
        ++$col=>"Статус 1", ++$col=>"Уточнение 1", ++$col=>"Дата".chr(10)." закрытия",
        ++$col=>"Дата".chr(10)." второго шанса", ++$col=>"Дата".chr(10)." блокировки", ++$col=>"Назначено",
        ++$col=>"Статус 2", ++$col=>"Уточнение 2", ++$col=>"Изменение".chr(10)." второго шанса",
        ++$col=>"Окончание".chr(10)." блокировки", ++$col=>"Закрыто 2");
    $sheet[$snum]->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    // сначала вторая строка - заголовки полей, чтобы потом сделать объединение ячеек в первой строке
    $col = 0;
    foreach ($head_arr as $key => $val) {
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, 2)->getCoordinate(); //находим ячейку по координатам
        $sheet[$snum]->setCellValue($coord, u8($val));
    }
    $highcol = $sheet[$snum]->getHighestColumn();
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getFont()->setBold(true);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setWrapText(true);
    $sheet[$snum]->getStyle('A2:'.$highcol.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $sheet[$snum]->getRowDimension(2)->setRowHeight(30); // для заголовка

    $sheet[$snum]->mergeCells('A1:'.$highcol.'1');
    $coord = $sheet[$snum]->getCellByColumnAndRow(0, 1)->getCoordinate();
    if ($rep_start_date != $rep_end_date)
        $sheet[$snum]->setCellValue($coord, u8('Заявки второго шанса c ' . $rep_start_date . " по " . $rep_end_date));
    else $sheet[$snum]->setCellValue($coord, u8('Заявки второго шанса на ' . $rep_start_date));
    $sheet[$snum]->getStyle($coord)->getFont()->setBold(true)->setSize(14);

    $q_text1 = "select cbl.BASE_ID, cbl.USER_ID, usr.FIO as FIO, to_char(cb.DATE_CALL,'dd.mm.yyyy hh24:mi:ss') DATE_CALL,
    to_char(LOCK_DATE_START,'dd.mm.yyyy hh24:mi:ss') LOCK_DATE_START, to_char(LOCK_DATE_END,'dd.mm.yyyy hh24:mi:ss') LOCK_DATE_END, 
    to_char(cb.DATE_SECOND_CHANCE,'dd.mm.yyyy hh24:mi:ss') DATE_SECOND_CHANCE, cb.SOURCE_TYPE_ID,
    cb.ANUMBER, cb.PHONE_MOB, cb.STATUS_ID, STATUS_DET_ID, stat.NAME as STAT_NAME, stat_det.NAME as STAT_DET, 
    cb.SECOND_STATUS_ID, cb.SECOND_STATUS_DET_ID, stat2.NAME as STAT_NAME_2, stat_det2.NAME as STAT_DET_2, 
    to_char(cb.SECOND_LAST_CHANGE,'dd.mm.yyyy hh24:mi:ss') SECOND_LAST_CHANGE, 
    to_char(cb.DATE_CLOSE,'dd.mm.yyyy hh24:mi:ss') DATE_CLOSE, to_char(cb.DATE_SECOND_CLOSE,'dd.mm.yyyy hh24:mi:ss') DATE_SECOND_CLOSE";
    $q_text2 = " from CALL_BASE_LOCK cbl";
    $q_text3 = " left join users usr on usr.id = cbl.user_id";
    $q_text3 .= " left join call_base cb on cb.id = cbl.base_id";
    $q_text3 .= " LEFT JOIN MED_STATUS stat ON stat.ID = cb.STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det ON stat_det.ID = cb.STATUS_DET_ID";
    $q_text3 .= " LEFT JOIN MED_STATUS stat2 ON stat2.ID = cb.SECOND_STATUS_ID
    LEFT JOIN MED_STATUS_DET stat_det2 ON stat_det2.ID = cb.SECOND_STATUS_DET_ID";
    $q_text4 = " where (cb.DATE_SECOND_CHANCE between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
    if (isset($UserIdSec) && strlen($q_usersSpec) > 0)
        $q_text4 .= " and cbl.USER_ID in (" . $q_usersSpec . ")";
    //$q_text4 .= ")"; // !!! закрывающая скобка
    $q_text5 = " order by FIO asc, cbl.base_id desc";
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_text5;
//var_dump($q_text);

    $rnum = 3; // Данные с третьей строки
    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $col = 0;
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "BASE_ID")); //Заявка
        //$coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, phone_segment(OCIResult($q, "DATE_CALL"))); //Дата
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "DATE_CALL")); //Дата
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(DEVICES[OCIResult($q, "SOURCE_TYPE_ID")])); //Источник
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "ANUMBER"))); //АОН
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "PHONE_MOB"))); //Оставленный номер
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STAT_NAME"))); //Статус
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STAT_DET"))); //Детализация статуса
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "DATE_CLOSE")); //Дата закрытия
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "DATE_SECOND_CHANCE")); //Дата второго шанса
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "LOCK_DATE_START")); //Дата второго шанса
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "FIO"))); //назначено
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STAT_NAME_2"))); //"Статус 2"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, u8(OCIResult($q, "STAT_DET_2"))); //"Детализация статуса 2"
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "SECOND_LAST_CHANGE")); //изменение второго шанса
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "LOCK_DATE_END")); //Окончание второго шанса
        $coord = $sheet[$snum]->getCellByColumnAndRow($col++, $rnum)->getCoordinate(); $sheet[$snum]->setCellValue($coord, OCIResult($q, "DATE_SECOND_CLOSE")); //Дата второгозакрытия
        $rnum++; //номер строки
    }
    $sheet[$snum]->getStyle('A3:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $sheet[$snum]->getStyle('A3:'.$highcol.$rnum)->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
}
// Экспорт данных по работе операторов
elseif (EXPORT_OPERATOR == $ReportId || EXPORT_OPERATOR_ALL == $ReportId || EXPORT_OPERATOR_SEC == $ReportId) {
    $q_start = "select ID, FIO from USERS ";
    if (EXPORT_OPERATOR_SEC == $ReportId) {
        if (isset($UserIdSpec) && !in_array('-1', $UserIdSpec)) {
            $q_usersSpec = implode(',', $UserIdSpec);
        }
        else {
            $q_usersSpec = implode(',', SPEC_USER_CALL);
        }
        if (strlen($q_usersSpec) > 0)
            $q_start .= " WHERE ID in (" . $q_usersSpec . ")";
    }
    else { // (EXPORT_OPERATOR_ALL == $ReportId && isset($UserId))
        if (!in_array('-1', $UserId))
            $q_users = implode(',', $UserId);
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
    if (EXPORT_OPERATOR_ALL == $ReportId)
        $q_text4 = " WHERE STATUS_ID <= " . STATUS_NOT_COME;
    else $q_text4 = " WHERE STATUS_ID between " . STATUS_CALL_STOP . " and " . STATUS_NOT_COME;
    $q_text4 .= " and (DATE_DET between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
    if (isset($UserId) && strlen($q_users) > 0)
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
    if (EXPORT_OPERATOR_SEC == $ReportId) {
        $q_text1 = "select count(*) as pnum, usr.fio, second_fio_id, to_char(DATE_SECOND_CHANCE,'dd.mm.yyyy') as CALL_DATE, second_status_id";
        $q_text2 = " FROM CALL_BASE cb ";
        $q_text3 = " left join users usr on usr.id = cb.second_fio_id ";
        $q_text4 = " WHERE second_status_id <= " . STATUS_NOT_COME . " and second_fio_id is not null";
        $q_text4 .= " and (DATE_SECOND_CHANCE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";
        if (isset($UserIdSec) && strlen($q_usersSpec) > 0)
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
        if (isset($UserId) && strlen($q_users) > 0)
            $q_text4 .= " and cb.FIO_ID in (" . $q_users . ")";
        $q_text4 .= " and cb.FIO_ID != " . SPEC_USER;
        $q_text5 = " group by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
        $q_text6 = " order by usr.fio, fio_id, to_char(DATE_CALL,'dd.mm.yyyy'), status_id";
    }
    //or LAST_CHANGE between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1)";

    if (EXPORT_OPERATOR_SEC != $ReportId) { // операторы второго дозвона не имеют доступа к Стоматологии
        if (isset($ServiceId) && !in_array(SERVICE_ALL, $ServiceId)) {
            $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $ServiceId) . ")";
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
        if (EXPORT_OPERATOR_SEC == $ReportId) {
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
    if (EXPORT_OPERATOR_SEC != $ReportId) { // для второго дозвона их быть не должно
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
        $q_text4 .= " and (call_double = ".CALL_SECOND." or interstate = 1)";
        if (isset($UserId) && strlen($q_users) > 0)
            $q_text4 .= " and cb.FIO_ID in (" . $q_users . ")";
        if (isset($ServiceId) && !in_array(SERVICE_ALL, $ServiceId)) {
            $q_text4 .= " and cb.SERVICE_ID in (" . implode(',', $ServiceId) . ")";
        }
        if (USER_ADMIN != $_SESSION['user_role']) {
            $q_text4 .= " and cb.SERVICE_ID in ( 
        select decode(ad.service_id,-1,cb.service_id,ad.service_id) from USER_DEP_ALLOC uda, ACCESS_DEP ad 
        where ad.departament_id=uda.dep_id and uda.user_id=" . $_SESSION['login_id_med'] . " and uda.DELETED is null)";
        }
        //if (EXPORT_OPERATOR_ALL == $ReportId)
            $q_text5 = " group by fio_id";
        //else $q_text5 = " group by second_fio_id";
        $q_text = $q_text1 . $q_text4 . $q_text5;

        $q = OCIParse($c, $q_text);
        OCIExecute($q, OCI_DEFAULT);
        while (OCIFetch($q)) { // наполняем данными
            //if (EXPORT_OPERATOR_ALL == $ReportId)
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
        '2' => "Кол-во заявок" . chr(10) . " (кроме брака)",
        '3' => "Кол-во в среднем" . chr(10) . " (кроме брака)",
        '4' => "Кол-во" . chr(10) . " брака",
        '5' => "% брака",
        '6' => "Кол-во " . chr(10) . " заявок",
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
        //if (EXPORT_OPERATOR_ALL == $ReportId || !in_array($key, $remove_column)) {
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
        $sheet[$snum]->setCellValue($coord, u8('Отчет по количеству заявок c ' . $rep_start_date . " по " . $rep_end_date));
    else $sheet[$snum]->setCellValue($coord, u8('Отчет по количеству заявок на ' . $rep_start_date));
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
        //if (EXPORT_OPERATOR_ALL == $ReportId) {
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
    //if (EXPORT_OPERATOR_ALL == $ReportId) {
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
    //if (EXPORT_OPERATOR_ALL == $ReportId) {
        $sheet[$snum]->getColumnDimension('M')->setVisible(false);
        $sheet[$snum]->getColumnDimension('N')->setVisible(false);
        $sheet[$snum]->getColumnDimension('O')->setVisible(false);
        $sheet[$snum]->getColumnDimension('P')->setVisible(false);
        $sheet[$snum]->getColumnDimension('Q')->setVisible(false);
        $sheet[$snum]->getColumnDimension('R')->setVisible(false);
    //}
}

// Отчеты по эффективности рекламы
if (EXPORT_EFFECT == $ReportId || EXPORT_EFFECT_IDYN == $ReportId || EXPORT_EFFECT_ISH == $ReportId) {
    $q_filt_date = " and (cb.DATE_CALL between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1)";
    if (EXPORT_EFFECT_ISH == $ReportId) { // Комбо отчет
//        --case when sra_det.NAME is NULL then '*'||sa.NAME else sra_det.NAME end SEL_NAME,
        $q_text1 = "select 
case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end SEL_ID,
sa.NAME as SEL_NAME, sra_det.NAME as DET_NAME, cb.SOURCE_TYPE_ID,
count(*) as CALL_ALL, count(p.cnt_payment) as PAY, sum(p.rub) as SUM,
count(case when cb.status_id in (" . STATUS_CLINIC . "," . STATUS_CLINIC_NOT . "," . STATUS_CALL_BACK . ") 
    and nvl(cb.call_double,0)<" . CALL_SECOND . " and cb.interstate is null then 1 else NULL end) as CALL,
count(decode(cb.status_id,6,1,NULL)) as WRITE,
sum(v.cnt_visit) as VISIT";
        $q_text2 = " from CALL_BASE cb";
        $q_text3 = " left join (select base_id,count(*) cnt_payment, sum(rub) rub from PAYMENT_HIST group by base_id) p on p.base_id=cb.id ";
        $q_text3 .= " left join (select base_id,count(*) cnt_visit from VISIT_HIST group by base_id) v on v.base_id=cb.id ";
        $q_text3 .= " left join source_auto sa on sa.id=cb.source_auto_id ";
        $q_text3 .= " left join source_auto_detail sra_det on sra_det.id=cb.SOURCE_MAN_ID_NEW ";
        //$q_text3 .= " left join source_auto_cost sac on sac.SOURCE_AUTO_ID=cb.source_auto_id ";
        $q_text4 .= ")"; // !!! закрывающая скобка
        /*$q_text5 = " group by case when sra_det.NAME is NULL then '*'||sa.NAME else sra_det.NAME end,
        case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000 end, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by SEL_NAME, SEL_ID, cb.SOURCE_TYPE_ID";*/
        $q_text5 = " group by case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end,
        sa.NAME, sra_det.NAME, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by SEL_NAME, SEL_ID, DET_NAME, cb.SOURCE_TYPE_ID";
    }
    else {
        $q_text1 = "select sa.name as SEL_NAME, cb.source_auto_id as SEL_ID, cb.SOURCE_TYPE_ID, 
    count(*) as CALL_ALL, count(p.cnt_payment) as PAY, sum(p.rub) as SUM,
count(case when cb.status_id in (" . STATUS_CLINIC . "," . STATUS_CLINIC_NOT . "," . STATUS_CALL_BACK . ") 
    and nvl(cb.call_double,0)<" . CALL_SECOND . " and cb.interstate is null then 1 else NULL end) as CALL,
count(decode(cb.status_id,6,1,NULL)) as WRITE,
sum(v.cnt_visit) as VISIT";
//,count(case when p.base_id is not null then 1 else NULL end) as PAY,
//sum(case when p.base_id is not null then p.rub else NULL end) as SUM";
        $q_text2 = " from CALL_BASE cb";
        $q_text3 = " left join (select base_id,count(*) cnt_payment, sum(rub) rub from PAYMENT_HIST group by base_id) p on p.base_id=cb.id ";
        $q_text3 .= " left join (select base_id,count(*) cnt_visit from VISIT_HIST group by base_id) v on v.base_id=cb.id ";
        $q_text3 .= " left join source_auto sa on sa.id=cb.source_auto_id ";
        //$q_text3 .= " left join source_auto_cost sac on sac.SOURCE_AUTO_ID=cb.source_auto_id ";
        $q_text4 .= ")"; // !!! закрывающая скобка
        $q_text5 = " group by sa.name,cb.source_auto_id, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by SEL_NAME, SEL_ID, cb.SOURCE_TYPE_ID";
    }
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_date . $q_text5;
//var_dump($q_text);
//die();
    $usluga_auto_arr = array();
    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $sel_id = OCIResult($q,"SEL_ID");
        $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
        $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[OCIResult($q,"SOURCE_TYPE_ID")];
        $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q,"SEL_NAME");
        if (EXPORT_EFFECT_ISH == $ReportId)
            $usluga_auto_arr[$sel_id]['DET_NAME'] = OCIResult($q,"DET_NAME");
        else $usluga_auto_arr[$sel_id]['DET_NAME'] = "";
        $usluga_auto_arr[$sel_id]['CALL'] = OCIResult($q,"CALL");
        $usluga_auto_arr[$sel_id]['WRITE'] = OCIResult($q,"WRITE");
        $usluga_auto_arr[$sel_id]['VISIT'] = OCIResult($q,"VISIT");
        $usluga_auto_arr[$sel_id]['SUM'] = OCIResult($q,"SUM");
        $usluga_auto_arr[$sel_id]['PAY'] = OCIResult($q,"PAY");

        $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = 0;
        $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = 0;
        $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 0;

        // стоимости
        $q_cost = OCIParse($c,"select COST_ORDER, COST_VISIT from SOURCE_AUTO_COST where SOURCE_AUTO_ID=:sa_id");
        OCIBindByName($q_cost,":sa_id", $sel_id);
        OCIExecute($q_cost, OCI_DEFAULT);
        OCIFetch($q_cost);
        $cost_order = OCIResult($q_cost,"COST_ORDER");
        $cost_visit = OCIResult($q_cost,"COST_VISIT");
        $usluga_auto_arr[$sel_id]['COST_ORDER'] = $cost_order;
        $usluga_auto_arr[$sel_id]['COST_VISIT'] = $cost_visit;
    }
    oci_free_statement($q);

    // добавляем платежи на те же даты для заявок вне выбранного периода
    $q_filt_pay = " DATE_PAYMENT between to_date('".$rep_start_date."','DD.MM.YYYY') and to_date('".$rep_end_date."','DD.MM.YYYY')+1 ";
    if (EXPORT_EFFECT_ISH == $ReportId) { // Комбо отчет
        $q_text1 = "select case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end SEL_ID,
        sa.NAME as SEL_NAME, sra_det.NAME as DET_NAME, cb.SOURCE_TYPE_ID,
     count(pp.base_id) as PAY_INTERVAL, sum(pp.rub) as SUM_INTERVAL";
        $q_text2 = " from (select base_id, sum(rub) as rub from PAYMENT_HIST p where " . $q_filt_pay . " group by base_id) pp, CALL_BASE cb ";
        $q_text3 = " left join source_auto sa on sa.id = cb.source_auto_id ";
        $q_text3 .= " left join source_auto_detail sra_det on sra_det.id=cb.SOURCE_MAN_ID_NEW ";
        $q_text5 = " group by case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end,
        sa.NAME, sra_det.NAME, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by sa.NAME, sra_det.NAME, cb.SOURCE_TYPE_ID";
    } else {
        $q_text1 = "select sa.name as SEL_NAME, cb.source_auto_id as SEL_ID, cb.SOURCE_TYPE_ID,
     count(pp.base_id) as PAY_INTERVAL, sum(pp.rub) as SUM_INTERVAL";
        $q_text2 = " from (select base_id, sum(rub) as rub from PAYMENT_HIST p where " . $q_filt_pay . " group by base_id) pp, CALL_BASE cb ";
        $q_text3 = " left join source_auto sa on sa.id = cb.source_auto_id ";
        $q_text5 = " group by sa.name, cb.source_auto_id, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by sa.name, cb.source_auto_id, cb.SOURCE_TYPE_ID";
    }
    //$q_text4 формируется в начале файла
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . " and cb.id = pp.base_id" . $q_text5;

    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $sel_id = OCIResult($q, "SEL_ID");
        $source_type_id = OCIResult($q, "SOURCE_TYPE_ID");
        if (!isset($usluga_auto_arr[$sel_id])) {
            $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
            $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[$source_type_id];
            $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q, "SEL_NAME");
            if (EXPORT_EFFECT_ISH == $ReportId)
                $usluga_auto_arr[$sel_id]['DET_NAME'] = OCIResult($q,"DET_NAME");
            else $usluga_auto_arr[$sel_id]['DET_NAME'] = "";
            $usluga_auto_arr[$sel_id]['CALL'] = 0;
            $usluga_auto_arr[$sel_id]['WRITE'] = 0;
            $usluga_auto_arr[$sel_id]['VISIT'] = 0;
            $usluga_auto_arr[$sel_id]['PAY'] = 0;
            $usluga_auto_arr[$sel_id]['SUM'] = 0;
            $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = 0;

            // стоимости
            $q_cost = OCIParse($c,"select COST_ORDER, COST_VISIT from SOURCE_AUTO_COST where SOURCE_AUTO_ID=:sa_id");
            OCIBindByName($q_cost,":sa_id", $sel_id);
            OCIExecute($q_cost, OCI_DEFAULT);
            OCIFetch($q_cost);
            $usluga_auto_arr[$sel_id]['COST_ORDER'] = OCIResult($q_cost,"COST_ORDER");;
            $usluga_auto_arr[$sel_id]['COST_VISIT'] =  OCIResult($q_cost,"COST_VISIT");
        }
        $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = OCIResult($q,"PAY_INTERVAL");
        $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = OCIResult($q,"SUM_INTERVAL");
    }
    oci_free_statement($q);

    // добавляем визиты в клинику на те же даты для заявок вне выбранного периода
    if (EXPORT_EFFECT_ISH == $ReportId) { // Комбо отчет
        $q_text1 = "SELECT count(*) as COMING, 
        case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end SEL_ID,
        sa.NAME as SEL_NAME, sra_det.NAME as DET_NAME, cb.SOURCE_TYPE_ID ";
        $q_text2 = " FROM CALL_BASE cb ";
        $q_text3 = " LEFT JOIN VISIT_HIST vh ON vh.BASE_ID = cb.ID ";
        $q_text3 .= " left join source_auto sa on sa.id = cb.source_auto_id ";
        $q_text3 .= " left join source_auto_detail sra_det on sra_det.id=cb.SOURCE_MAN_ID_NEW ";
        $q_filt_visit = " and (date_visit between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1) ";
        $q_text5 = " group by case when cb.SOURCE_MAN_ID_NEW is NULL then cb.SOURCE_AUTO_ID else cb.SOURCE_MAN_ID_NEW*1000+cb.SOURCE_AUTO_ID end,
        sa.NAME, sra_det.NAME, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by sa.NAME, sra_det.NAME, cb.SOURCE_TYPE_ID";
    } else {
        $q_text1 = "SELECT count(*) as COMING, cb.SOURCE_AUTO_ID as SEL_ID, cb.SOURCE_TYPE_ID, sa.NAME as SEL_NAME ";
        $q_text2 = " FROM CALL_BASE cb ";
        $q_text3 = " LEFT JOIN VISIT_HIST vh ON vh.BASE_ID = cb.ID ";
        $q_text3 .= " left join source_auto sa on sa.id = cb.source_auto_id ";
        $q_filt_visit = " and (date_visit between to_date('" . $rep_start_date . "','DD.MM.YYYY') and to_date('" . $rep_end_date . "','DD.MM.YYYY')+1) ";
        $q_text5 = " group by sa.name, cb.source_auto_id, cb.SOURCE_TYPE_ID ";
        $q_text5 .= " order by sa.name, cb.source_auto_id, cb.SOURCE_TYPE_ID";
    }
    $q_text = $q_text1 . $q_text2 . $q_text3 . $q_text4 . $q_filt_visit . $q_text5;
//var_dump($q_text);
    $q = OCIParse($c, $q_text);
    OCIExecute($q, OCI_DEFAULT);
    while (OCIFetch($q)) {
        $sel_id = OCIResult($q, "SEL_ID");
        $source_type_id = OCIResult($q, "SOURCE_TYPE_ID");
        if (!isset($usluga_auto_arr[$sel_id])) {
            $usluga_auto_arr[$sel_id]['SEL_ID'] = $sel_id;
            $usluga_auto_arr[$sel_id]['TYPE'] = DEVICES[$source_type_id];
            $usluga_auto_arr[$sel_id]['NAME'] = OCIResult($q, "SEL_NAME");
            if (EXPORT_EFFECT_ISH == $ReportId)
                $usluga_auto_arr[$sel_id]['DET_NAME'] = OCIResult($q,"DET_NAME");
            else $usluga_auto_arr[$sel_id]['DET_NAME'] = "";
            $usluga_auto_arr[$sel_id]['CALL'] = 0;
            $usluga_auto_arr[$sel_id]['WRITE'] = 0;
            $usluga_auto_arr[$sel_id]['VISIT'] = 0;
            $usluga_auto_arr[$sel_id]['PAY'] = 0;
            $usluga_auto_arr[$sel_id]['SUM'] = 0;
            $usluga_auto_arr[$sel_id]['PAY_INTERVAL'] = 0;
            $usluga_auto_arr[$sel_id]['SUM_INTERVAL'] = 0;

            // стоимости
            $q_cost = OCIParse($c,"select COST_ORDER, COST_VISIT from SOURCE_AUTO_COST where SOURCE_AUTO_ID=:sa_id");
            OCIBindByName($q_cost,":sa_id", $sel_id);
            OCIExecute($q_cost, OCI_DEFAULT);
            OCIFetch($q_cost);
            $usluga_auto_arr[$sel_id]['COST_ORDER'] = OCIResult($q_cost,"COST_ORDER");;
            $usluga_auto_arr[$sel_id]['COST_VISIT'] =  OCIResult($q_cost,"COST_VISIT");
        }
        $usluga_auto_arr[$sel_id]['VISIT_INTERVAL'] = OCIResult($q,"COMING");
    }

    $objPHPExcel->getProperties()
            ->setTitle(u8("Эффективность рекламы"))
            ->setSubject(u8("Эффективность рекламы"))
            ->setDescription(u8("Эффективность рекламы"))
            ->setKeywords(u8("Эффективность рекламы"))
            ->setCategory(u8("Эффективность рекламы"));

    if (EXPORT_EFFECT == $ReportId || EXPORT_EFFECT_ISH == $ReportId) {
        fill_list(0, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $ReportId);
    }
    else {
        fill_list(1, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $ReportId);

        $objSheet = clone $sheet[$snum];
        $objSheet->setTitle(u8("Sheet2"));
        $objPHPExcel->addSheet($objSheet);

        $snum2 = 2; //номер листа
        $objPHPExcel->setActiveSheetIndex($snum2 - 1);
        $sheet[$snum2] = $objPHPExcel->getActiveSheet();

        fill_list(2, $objPHPExcel, $rep_start_date, $rep_end_date, $usluga_auto_arr, $ReportId);
    }
}

foreach($sheet as $s => $fuck) {
	$highcol=$sheet[$s]->getHighestColumn();
	$highrow=$sheet[$s]->getHighestRow();
	//устанавливаем автоширину столбцов на каждом листе
	for($i = 1; $i <= $highcol; $i++) {
	    $sheet[$s]->getColumnDimension($i)->setAutoSize(true);
    }
    if (EXPORT_EFFECT == $ReportId || EXPORT_EFFECT_ISH == $ReportId || EXPORT_EFFECT_IDYN == $ReportId) {
        $sheet[$s]->freezePane('A4');
        //if (EXPORT_EFFECT == $ReportId || EXPORT_EFFECT_IDYN == $ReportId) {
            $sheet[$s]->mergeCells('A1:' . $highcol . '1');
            $sheet[$s]->mergeCells('A2:' . $highcol . '2');
        /*}
        else {
            $sheet[$s]->mergeCells('B1:' . $highcol . '1');
            $sheet[$s]->mergeCells('B2:' . $highcol . '2');
        }*/
        //$sheet[$s]->setAutoFilter('A3:'.$highcol.'3');
    }
    else {
        $sheet[$s]->freezePane('A3');
        $sheet[$s]->setAutoFilter('A2:'.$highcol.'2');
    }

    //$sheet[$s]->getStyle('B3:'.$highcol.$highrow)->getBorders()->getOutline()->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM);
}

/*if(EXPORT_EFFECT_IDYN == $ReportId) {
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