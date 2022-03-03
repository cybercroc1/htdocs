<?php 
if (@$c = OCILogon("sc", "kilimangaro", "sc")) {} else { $err = OCIError(); echo "Oracle Connect Error " . $err['message']; exit();}

$q=OCIParse($c,"select nvl(max(request_id),0)+1 request_id from php_parse_log"); OCIExecute($q); OCIFetch($q); $request_id=OCIResult($q,"REQUEST_ID");

$ins=OCIParse($c,"insert into php_parse_log2 (date_log,request_id,headers,post,get,cookie,server,env) 
									 values (sysdate,:request_id,:headers,:post,:get,:cookie,:server,:env)");

OCIBindByName($ins,":request_id",$request_id);

$var_id=0;

echo "<hr>";
echo date("d m Y H:i:s")."<hr>";

echo "<b>HEADERS</b><br>";
$headers_dump=var_export(getallheaders(),true);
echo $headers_dump; 
$headers_dump=strlen($headers_dump)>4000?substr($headers_dump,0,3997)."...":$headers_dump;
OCIBindByName($ins,":headers",$headers_dump);
echo "<hr>";

echo "<b>POST</b><br>";
$post_dump=var_export($_POST,true);
echo $post_dump; 
$post_dump=strlen($post_dump)>4000?substr($post_dump,0,3997)."...":$post_dump;
OCIBindByName($ins,":post",$post_dump);
echo "<hr>";

echo "<b>GET</b><br>";
$get_dump=var_export($_GET,true);
echo $get_dump; 
$get_dump=strlen($get_dump)>4000?substr($get_dump,0,3997)."...":$get_dump;
OCIBindByName($ins,":get",$get_dump);
echo "<hr>";

echo "<b>COOKIE</b><br>";
$cookie_dump=var_export($_COOKIE,true);
echo $cookie_dump; 
$cookie_dump=strlen($cookie_dump)>4000?substr($cookie_dump,0,3997)."...":$cookie_dump;
OCIBindByName($ins,":cookie",$cookie_dump);
echo "<hr>";

echo "<b>SERVER</b><br>";
$server_dump=var_export($_SERVER,true);
echo $server_dump; 
$server_dump=strlen($server_dump)<4000?substr($server_dump,0,3997)."...":$server_dump;
OCIBindByName($ins,":server",$server_dump);
echo "<hr>";

echo "<b>ENV</b><br>";
$env_dump=var_export($_ENV,true);
echo $env_dump; 
$env_dump=strlen($env_dump)>4000?substr($env_dump,0,3997)."...":$env_dump;
OCIBindByName($ins,":env",$env_dump);
echo "<hr>";

OCIExecute($ins,OCI_DEFAULT);
OCICommit($c);

?>
