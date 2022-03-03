<?php
extract($_REQUEST);
include("pass_gen.php");
if(!isset($number)) $number='8';
if(!isset($dif)) $dif="aA1";
echo pass_gen();
?>