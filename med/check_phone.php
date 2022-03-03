<?php
include("phone_conv_single.php");
$phone=preg_replace('/[^+0-9]/','',$_GET["phone"]); //удаляем все не числовые символы и минус, плюс остается.
$phone=phone_norm_single($phone,"ru_dial",0);
echo "phone:".$phone.";";
?>