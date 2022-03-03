<?php include("starcall/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Просмотр загрузки</title>
</head>
<body>
<?php
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']<>'r' and $_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

include("starcall/conn_string.cfg.php");

//выводит на экран загруженные в БД данные
	echo "показаны $show_rows строк";
	$q_flds=OCIParse($c,"select t.id,t.text_name,t.code_name,t.std_field_name from STC_FIELDS t
	where t.project_id='".$_SESSION['adm']['project']['id']."' and t.src_type_id='1'
	order by ord");
	OCIExecute($q_flds,OCI_DEFAULT);
	$flds=array();
	$i=0; while(OCIFetch($q_flds)) {$i++;
		$flds[$i]['id']=OCIResult($q_flds,"ID");
		$flds[$i]['text_name']=OCIResult($q_flds,"TEXT_NAME");
		$flds[$i]['code_name']=OCIResult($q_flds,"CODE_NAME");
		$flds[$i]['std_name']=OCIResult($q_flds,"STD_FIELD_NAME");
	}

	$q_row=OCIParse($c,"select t.id from STC_BASE t
	where t.project_id='".$_SESSION['adm']['project']['id']."' and t.load_hist_id=nvl('".$load_id."',t.load_hist_id)
	order by id");
		
	$q_val=OCIParse($c,"select t.text_value from STC_FIELD_VALUES t
	where t.base_id=:base_id and t.field_id=:field_id");
	
	$q_phone=OCIParse($c,"select phone,ext,allow from STC_PHONES t
	where t.base_id=:base_id
	order by ord");
		
	echo "<table class=white_table>";
	echo "<tr><th>№</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$key."</th>";
		if($fld['std_name']=='PHONE') echo "<th>".$key."</th>";	
	}
	echo "<tr><td>станд.</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['std_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
	}
	echo "</tr>";	
	echo "<tr><td>код</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['code_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
	}
	echo "</tr>";
	echo "<tr><td>ID</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['id']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
	}
	echo "</tr>";
	echo "</tr>";
	echo "<tr><td>имя</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$fld['text_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th>Найденные телефоны</th>";	
	}
	echo "</tr>";		

	OCIExecute($q_row,OCI_DEFAULT);		
	$i=0; while(OCIFetch($q_row)) {
		if($show_rows<>'all' and $i>=$show_rows) break;
		$i++;
		echo "<tr><th>$i</td>";			
		$row_id=OCIResult($q_row,"ID");
		foreach($flds as $key=>$fld) {
			//значения
			OCIBindByName($q_val,":base_id",$row_id);
			OCIBindByName($q_val,":field_id",$fld['id']);
			OCIExecute($q_val,OCI_DEFAULT);
			$j=0; while(OCIFetch($q_val)) {$j++;
				echo "<td>".OCIResult($q_val,"TEXT_VALUE")."</td>";
			}
			if($j==0) echo "<td></td>"; 
			
			//телефоны
			if($fld['std_name']=='PHONE') {
				OCIBindByName($q_phone,":base_id",$row_id);
				OCIExecute($q_phone,OCI_DEFAULT);				
				echo "<td>";
				while(OCIFetch($q_phone)) {
					echo OCIResult($q_phone,"PHONE");
					echo OCIResult($q_phone,"EXT")<>''?"#".OCIResult($q_phone,"EXT"):NULL;
					echo "<br>";
				}
				echo "</td>"; 
			}
		}
		echo "</tr>";
	}
	echo "</table>";
	if($i==0) echo "<font size=3 color=red>Нет данных</font>";
?>