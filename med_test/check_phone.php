<?php
include("phone_norm_single.php");
$phone=phone_conv($_GET["phone"],"ru_dial");
echo "phone:".$phone;
?>