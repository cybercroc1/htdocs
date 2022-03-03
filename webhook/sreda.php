<?php
//Среда для жизни(обработчик)
ini_set('default_charset','utf-8');
require_once "show_array.php";
require_once "func.php";
require_once "oktell_conn_string_utf8.php";
require_once "phone_conv_single.php";

$body = file_get_contents('php://input');
$arr = json_decode($body, true);
//var_dump($arr);
//Принимаем лиды в базу
if(isset($_GET['add'])){
    $i=0;
    $p=0;
    //var_dump($arr);
    $ip = $arr['ip'];
    $data = $arr['data'];
    $your_name = $data['your-name'];
    $gender = $data['gender'];
    $region = $data['region'];
    $city = $data['city'];
    $age = $data['age'];
    $work = $data['work'];
    $position = $data['position'];
    $tel = phone_norm_single($data['tel'],'ru_dial');
    $email = $data['email'];
    $participation = $data['participation'];
    $accommodation = $data['accommodation'];
    $agree = $data['agree'];
    $badge = $data['badge'];
    //пихаем в базу
    $sql = "INSERT INTO [oktell].[dbo].[ws_sreda] ([ip],[your-name],[gender],[region],[city],[age],[work],[position],[tel],[email],[participation],[accommodation],[agree],[badge]) VALUES('$ip','$your_name','$gender','$region','$city','$age','$work','$position','$tel','$email','$participation','$accommodation','$agree','$badge')";
    //echo $sql."<br>";
    $c_okt->query($sql);
    header("Content-type: application/json; charset=utf-8");
    $result['status']='OK';
    echo json_encode($result);
    exit;
}
echo"Не выбран метод";