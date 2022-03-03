<!DOCTYPE html>
<head>
    <meta charset="windows-1251">
    <link rel="stylesheet" href="style.css" />
    <title>�������</title>
</head>
<body>
<?php
//LastDocOnOfdDateTimeUtc 	 - ����� ���������� ���������
//7702417824 - ��� �������
$inn = 7702417824;
//���������� ���� ��� ��������������
$days = 1;

//��������� ������ ��� ���������� ������
$jsonauth = '{"Login": "smikhaylova90@mail.ru", "Password": "0a~cama@"}';
$options = ['http' => [
    'method' => 'POST',
    'header' => 'Content-Length: 38',
    'header' => 'Content-Type: application/json; charset=utf-8',
    'content' => $jsonauth
]];
$context = stream_context_create($options);
$response = file_get_contents('https://ofd.ru/api/Authorization/CreateAuthToken', false, $context);
//�������� ��������� ������
//var_dump($response);
//echo "<hr />";

$tokencreate = json_decode($response);
//�������� ����������� ������ � ������
//var_dump($tokencreate);
//echo "<hr />";

foreach($tokencreate as $k => $v){
    if($k == "AuthToken"){
        $token = $v;
    }
    elseif($k == "ExpirationDateUtc"){
        $tokendate = $v;
    }
}
//�������� ������������� ������
//echo "$token";
//echo "<hr />";
//echo "$tokendate";
//echo "<hr />";

/*
//�������� ������ �� ���� ������
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'https://ofd.ru/api/integration/v1/inn/7702417824/kkts?AuthToken='.$token);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$out = curl_exec($curl);
$code=curl_getinfo($curl,CURLINFO_HTTP_CODE);
curl_close($curl);
//�������� ��������
//var_dump($out);
//$code=(int)$code;
//echo $code;
*/


$options2 = ['http' => [
    'method' => 'GET',
    'header' => 'Content-Type: application/json; charset="windows-1251"',
]];
$context2 = stream_context_create($options2);
$out = file_get_contents('https://ofd.ru/api/integration/v1/inn/7702417824/kkts?AuthToken='.$token,false,$context2);
$otvet = json_decode($out);
mb_convert_variables('cp1251','utf8',$otvet);
//echo $kassi[Status];
//var_dump($otvet);

//�������� ������� � �������
foreach ($otvet as $k => $v){
    if($k == "Status"){
        if($v != "Success"){
            echo "������:".$v;
            die;
        }
    }
    elseif($k == "Data"){
        $kassi = $v;
    }
}
//�������� �������
//var_dump($kassi);
?>
<table>
<tr class="head_th">
<th>���� ���������� ���������</th>
<th>����� ���������</th>
<th>����� ���������</th>
</tr>
<?php
foreach($kassi as $k => $v){
    foreach($v as $key => $val){
        if($key == "LastDocOnKktDateTime"){
            $t = mktime(substr($val,11,2),substr($val,14,2),substr($val,17,2),substr($val,5,2),substr($val,8,2),substr($val,0,4));
            if((strtotime(date("d.m.Y"))-strtotime(date("d.m.Y",$t))) > ($days*24*60*60)){
                echo"<tr><td class=senderror>".date("d.m.Y",$t)."</td><td class=senderror>".date("H:i",$t)."</td>";    
            }
            else{
                echo"<tr><td>".date("d.m.Y",$t)."</td><td>".date("H:i",$t)."</td>";
            }
        }
        elseif($key == "FiscalAddress"){
            echo"<td>$val</td></tr>";
        }
    }
}
?>
</table>
</body>
</html>





