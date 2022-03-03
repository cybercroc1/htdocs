<?php 
if (@$c = OCILogon("sc", "kilimangaro", "sc")) {} else { $err = OCIError(); echo "Oracle Connect Error " . $err['message']; exit();}

$q=OCIParse($c,"select nvl(max(request_id),0)+1 request_id from php_parse_log"); OCIExecute($q); OCIFetch($q); $request_id=OCIResult($q,"REQUEST_ID");

$ins=OCIParse($c,"insert into php_parse_log values (sysdate,:request_id,:method,:var_id,:var_name,:var_value)");

OCIBindByName($ins,":request_id",$request_id);

$var_id=0;

echo "<hr>";
echo date("d m Y H:i:s")."<hr>";

echo "<b>HEADERS</b><br>";
$method='HEADERS';
OCIBindByName($ins,":method",$method);
foreach(getallheaders() as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

echo "<b>POST</b><br>";
$method='POST';
OCIBindByName($ins,":method",$method);
extract($_POST);
foreach($_POST as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

echo "<b>GET</b><br>";
$method='GET';
OCIBindByName($ins,":method",$method);
extract($_GET);
foreach($_GET as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

echo "<b>COOKIE</b><br>";
$method='COOKIE';
OCIBindByName($ins,":method",$method);
extract($_COOKIE);
foreach($_COOKIE as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

echo "<b>SERVER</b><br>";
$method='SERVER';
OCIBindByName($ins,":method",$method);
extract($_SERVER);
foreach($_SERVER as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

echo "<b>ENV</b><br>";
$method='ENV';
OCIBindByName($ins,":method",$method);
extract($_ENV);
foreach($_ENV as $key=>$val) {
echo "$key - $val <br>";
$var_id++;
OCIBindByName($ins,":var_id",$var_id);
OCIBindByName($ins,":var_name",$key);
OCIBindByName($ins,":var_value",$val);
OCIExecute($ins,OCI_DEFAULT);
}
echo "<hr>";
OCICommit($c);

?>
