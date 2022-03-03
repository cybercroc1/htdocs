<?php
$date1='10.02.2014 6:36:45';
$date3='16.02.2014 15:18:12';
echo check_3_1_3($date1,$date3);

function check_3_1_3($date1,$date3) {
$res='';
$date1=strtotime($date1);
$date3=strtotime($date3);

//лимит закрытия заявки в сутках (рабочих) по числам месяца
$close_limit[1]=1;
$close_limit[2]=1;
$close_limit[3]=1;
$close_limit[4]=1;
$close_limit[5]=1;
$close_limit[6]=3;
$close_limit[7]=3;
$close_limit[8]=3;
$close_limit[9]=3;
$close_limit[10]=3;
$close_limit[11]=3;
$close_limit[12]=3;
$close_limit[13]=3;
$close_limit[14]=3;
$close_limit[15]=3;
$close_limit[16]=1;
$close_limit[17]=1;
$close_limit[18]=1;
$close_limit[19]=1;
$close_limit[20]=3;
$close_limit[21]=3;
$close_limit[22]=3;
$close_limit[23]=3;
$close_limit[24]=3;
$close_limit[25]=3;
$close_limit[26]=3;
$close_limit[27]=3;
$close_limit[28]=3;
$close_limit[29]=3;
$close_limit[30]=3;
$close_limit[31]=3;

$close_limit_date='';
$tmp_date=$date1;
$a=0;
echo date('N j',$date1)."<br>";
while($a<=$close_limit[date('j',$date1)]) {

if(date('N',$tmp_date)<6) $a++;

echo date('N j d.m.Y H:i:s',$tmp_date)."<br>";
$tmp_date=$tmp_date+86400;
}
$close_limit_date=$tmp_date;
if($date3>$close_limit_date) $res="3.1.3";
return $res;
}
