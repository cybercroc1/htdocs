<?php 
ob_start();

echo "<b>HEADERS</b><br>";
show_array(getallheaders());
echo "<b>REQUEST</b><br>";
show_array($_REQUEST);
echo "<b>GET</b><br>";
show_array($_GET);
echo "<b>POST</b><br>";
show_array($_POST);
echo "<b>FILES</b><br>";
show_array($_FILES);
echo "<b>COOKIE</b><br>";
show_array($_COOKIE);
echo "<b>SERVER</b><br>";
show_array($_SERVER);
echo "<b>ENV</b><br>";
show_array($_ENV);

$body=file_get_contents('php://input');
echo "<b>BODY</b><br>";
echo $body."<br>";

echo "<b>JSON</b><br>";
$json=json_decode($body,true);
mb_convert_variables('cp1251','utf-8',$json);
show_array($json);

$contents = ob_get_contents(); ob_end_clean(); file_put_contents('bakeevo'.date('YmdHis').'.html',$contents); 

function show_array($arr,$lvl=0,$varnames=array()) {
	if(count($arr)>0) {echo "<table border=5>";}
	$lvl++;
	foreach($arr as $key=>$val) {
		echo "<tr>";
		if(is_array($val)) {
			$varnames[$lvl]=$key;
			echo "<td>";
			for($i=1; $i<=$lvl; $i++) {echo "[".htmlentities($varnames[$i])."] ";}
			echo "= array(";
			show_array($val,$lvl,$varnames);
			echo ")</td>";
		}
		else {
			$varnames[$lvl]=$key;
			echo "<td>";
			for($i=1; $i<=$lvl; $i++) {echo "[".htmlentities($varnames[$i])."] ";}
			echo "= ".htmlentities($val)."</td>";
		}
		echo "</tr>";
	}
	if(count($arr)>0) {echo "</table>";}
}
?>