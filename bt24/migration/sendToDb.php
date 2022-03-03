<?php 
exit;
require_once(__DIR__.'/vendor/autoload.php'); 
require_once(__DIR__.'/db.php');

$inputFileName = __DIR__.'/6330.xlsx';
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

$all_valls = array(
  '1'=> 'code',
  '5' => 'manager_pin',
  '6' => 'source',
  '7' => 'company_name',
  '8' => 'sfera',
  '9' => 'address',
  '10' => 'secretary',
  '11' => 'fio1',
  '12' => 'doljnost1',
  '13' => 'email1',
  '14' => 'email2',
  '15' => 'email3',
  '17' => 'phone1',
  '18' => 'dob1',
  '20' => 'phone2',
  '21' => 'dob2',
  '23' => 'phone3',
  '24' => 'dob3',
  '25' => 'fio2',
  '26' => 'doljnost2',
  '27' => 'email2_1',
  '29' => 'phone2_1',
  '30' => 'dob4',
  '31' => 'fio3',
  '32' => 'doljnost3',
  '33' => 'email3_1',
  '35' => 'phone3_1',
  '36' => 'dob5',
  '37' => 'usluga1',
  '38' => 'usluga2',
  '39' => 'sostoyanie',
  '40' => 'budget',
  '44' => 'otkaz', 
  '45' => 'comment'

);

for ($row = 2; $row <= $highestRow; ++$row) {
  for ($col = 0; $col <= $highestColumnIndex; ++$col) {
    $rows[$col] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
    
  }
  



 $infos = issetValues($all_valls,$rows);

  $columns = '';
  $values = '';
  foreach ($infos as $key => $value) {
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
     echo $infos['code'].' не добавлен '.printf($db->error).'<br>';  // если пользователь не создался
     exit;
  }


  $z++;

}

function issetValues($values,$rows){
  $infos = [];
foreach ($values as $key => $value) {
  if(isset($rows[$key]) && !empty($rows[$key]))   $infos[$value] = $rows[$key]; else{ $infos[$value] = ''; }
}
return $infos;
}



echo "Найдено $z строк, в базу добавлено $adden строк";

?>