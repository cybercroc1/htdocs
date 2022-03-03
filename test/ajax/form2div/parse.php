<?php 
ini_set( 'default_charset', 'UTF-8' );
//header('Content-type: text/html; charset=windows-1251');
$var_id=0;

if(isset($_GET['pause'])) {
	//sleep($_GET['pause']); 
	unset($_GET['pause']);
}

echo "<b>Парсер Web-запросов</b><br>";
echo date("d.m.Y H:i:s")."<hr>";

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

//sleep(10);

/*function show_array($arr) {
	foreach($arr as $key=>$val) {
		echo "=========================================<br>";
		if(is_array($val)) {
			echo "$key<br>"; show_array($val);
		}
		else echo "$key = $val <br>";
		echo "<hr>";
	}
}*/
function show_array($arr,$lvl=0,$varnames=array()) {
	if(count($arr)>0) {echo "<table border=5>";}
	$lvl++;
	foreach($arr as $key=>$val) {
		echo "<tr>";
		if(is_array($val)) {
			$varnames[$lvl]=$key;
			echo "<td>";
			for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
			echo "= array(";
			show_array($val,$lvl,$varnames);
			echo ")</td>";
		}
		else {
			$varnames[$lvl]=$key;
			echo "<td>";
			for($i=1; $i<=$lvl; $i++) {echo "[".$varnames[$i]."] ";}
			echo "= $val </td>";
		}
		echo "</tr>";
	}
	if(count($arr)>0) {echo "</table>";}
}
?>