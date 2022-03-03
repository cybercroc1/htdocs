<?php
header('Content-Type: text/html; charset=utf-8');
$f = $_GET['f'];
$file = __DIR__ . '/' . $f;
echo '<pre>';
echo file_get_contents($file);
