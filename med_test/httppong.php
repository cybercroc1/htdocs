<?php 

echo $_GET['n']." - ";

if(@$c = OCILogon("sc", "kilimangaro", "sc")) {

	$q=OCIParse($c,"select 'Ora:OK' ok from dual");
	OCIExecute($q);
	OCIFetch($q);
	echo OCIResult($q,"OK");
} 
else {
	$err = OCIError(); 
	echo $err['message']; 
	exit();
}
echo " - ";
echo 'REMOTE_ADDR:'.$_SERVER['REMOTE_ADDR'];

echo " - ";
echo 'POST_PARAM:'.$_POST['POST_PARAM'];

?>
