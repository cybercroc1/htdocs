<?php 
try{$btx24=new PDO("sqlsrv:server=mssql.wilstream.ru;database=btx24", 'btx24','F7rU5B2s',
array(
PDO::SQLSRV_ATTR_ENCODING=>PDO::SQLSRV_ENCODING_SYSTEM,
PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
} catch (Exception $e) {echo "Error: mssql Connect Error ";/* . $e->getMessage();*/ exit();}
?>