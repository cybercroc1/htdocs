<?php

$string[1]='280215 2359 00329     89512833744                    6187           9       703 1      31';
$string[2]='280215 2359 00313     74955807557                    6320           9       710 1      01';
$string[3]='280215 2250 00312                        6881           9       716 1      01';

foreach($string as $key => $string) {



if (strlen($string)=='77' and substr($string,32,1)) {

$string=substr($string,0,18).'            '.substr($string,18);

}

echo "<br>".str_replace(' ','x',$string);
}


?>
