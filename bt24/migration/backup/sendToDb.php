<?php 

require_once(__DIR__.'/vendor/autoload.php'); 
require_once(__DIR__.'/db.php');

$inputFileName = __DIR__.'/9547.xlsx';
$objReader = PHPExcel_IOFactory::createReader('Excel2007');
$objReader->setReadDataOnly(true);
$objPHPExcel = $objReader->load($inputFileName);
$objWorksheet = $objPHPExcel->getActiveSheet();

$infos = [];

$highestRow = $objWorksheet->getHighestRow();
$highestColumn = $objWorksheet->getHighestColumn();
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
$rows = array();
$z = 0;
$adden = 0;

for ($row = 2; $row <= $highestRow; ++$row) {
  for ($col = 0; $col <= $highestColumnIndex; ++$col) {
    $rows[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
  }
  if(isset($rows[0]) && !empty($rows[0]))   $infos[$z]['code'] = $rows[0]; else{ $infos[$z]['code'] = ''; }
  if(isset($rows[4]) && !empty($rows[4]))   $infos[$z]['manager_pin'] = $rows[4]; else{ $infos[$z]['manager_pin'] = ''; }
  if(isset($rows[5]) && !empty($rows[5]))   $infos[$z]['source'] = $rows[5]; else{ $infos[$z]['source'] = ''; }
  if(isset($rows[6]) && !empty($rows[6]))   $infos[$z]['company_name'] = $rows[6]; else{ $infos[$z]['company_name'] = ''; }
  if(isset($rows[7]) && !empty($rows[7]))   $infos[$z]['sfera'] = $rows[7]; else{ $infos[$z]['sfera'] = ''; }
  if(isset($rows[8]) && !empty($rows[8]))   $infos[$z]['address'] = $rows[8]; else{ $infos[$z]['address'] = ''; }
  if(isset($rows[9]) && !empty($rows[9]))   $infos[$z]['email'] = $rows[9]; else{ $infos[$z]['email'] = ''; }
  if(isset($rows[10]) && !empty($rows[10]))   $infos[$z]['phone'] = $rows[10]; else{ $infos[$z]['phone'] = ''; }
  if(isset($rows[12]) && !empty($rows[12]))   $infos[$z]['fio'] = $rows[12]; else{ $infos[$z]['fio'] = ''; }
  if(isset($rows[13]) && !empty($rows[13]))   $infos[$z]['doljnost'] = $rows[13]; else{ $infos[$z]['doljnost'] = ''; }
  if(isset($rows[14]) && !empty($rows[14]))   $infos[$z]['usluga1'] = $rows[14]; else{ $infos[$z]['usluga1'] = ''; }
  if(isset($rows[15]) && !empty($rows[15]))   $infos[$z]['usluga2'] = $rows[15]; else{ $infos[$z]['usluga2'] = ''; }
  if(isset($rows[16]) && !empty($rows[16]))   $infos[$z]['sostoyanie'] = $rows[16]; else{ $infos[$z]['sostoyanie'] = ''; }
  if(isset($rows[17]) && !empty($rows[17]))   $infos[$z]['budget'] = $rows[17]; else{ $infos[$z]['budget'] = ''; }
  if(isset($rows[21]) && !empty($rows[21]))   $infos[$z]['otkaz'] = $rows[21]; else{ $infos[$z]['otkaz'] = ''; }
  if(isset($rows[22]) && !empty($rows[22]))   $infos[$z]['comment'] = $rows[22]; else{ $infos[$z]['comment'] = ''; }

  $columns = '';
  $values = '';
  foreach ($infos[$z] as $key => $value) {
    if($value != ''){
    $value = $db->real_escape_string($value);
    }
      $columns .= ",`$key`";
      $values .= ",'$value'";
  }
  $columns = ltrim($columns, ',');
  $values = ltrim($values, ',');
  $sql = "INSERT INTO `b24` ($columns) VALUES ($values)"; // Отправляем запрос в базу

  if ($db->query($sql)) {
      $adden++;   // если создали пользователя
  } else {
     echo $infos[$z]['code'].' не добавлен '.printf($db->error).'<br>';  // если пользователь не создался
     exit;
  }


  $z++;

}





echo "Найдено $z строк, в базу добавлено $adden строк";

?>