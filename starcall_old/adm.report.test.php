<?php include("../../conf/starcall_conf/session.cfg.php"); 
if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_report']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

$project_id=$_SESSION['adm']['project']['id'];

?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Просмотр загрузки</title>
</head>
<body>
<?php
extract($_REQUEST);

include("../../conf/starcall_conf/conn_string.cfg.php");

$show_rows=300;

//выводит на экран загруженные в БД данные
	echo "показаны $show_rows строк";
	$q_flds=OCIParse($c,"select t.id,t.src_type_id,t.text_name,t.code_name,t.std_field_name,t.quoted from STC_FIELDS t
	where t.project_id='".$_SESSION['adm']['project']['id']."' and t.deleted is null
	order by t.src_type_id, ord");
	OCIExecute($q_flds,OCI_DEFAULT);
	
	$flds=array();
	$i=0; while(OCIFetch($q_flds)) {$i++;
		$flds[$i]['id']=OCIResult($q_flds,"ID");
		$flds[$i]['text_name']=OCIResult($q_flds,"TEXT_NAME");
		$flds[$i]['code_name']=OCIResult($q_flds,"CODE_NAME");
		$flds[$i]['std_name']=OCIResult($q_flds,"STD_FIELD_NAME");
		$flds[$i]['quoted']=OCIResult($q_flds,"QUOTED");
		$flds[$i]['src_type_id']=OCIResult($q_flds,"SRC_TYPE_ID");
	}

	$q_row=OCIParse($c,"select t.id,t.status from STC_BASE t
	where t.project_id='".$_SESSION['adm']['project']['id']."'
	order by id");
		
	$q_val=OCIParse($c,"select fv.list_value_id, fv.text_value from STC_FIELD_VALUES fv
	where fv.base_id=:base_id and fv.field_id=:field_id");

	$q_list_val=OCIParse($c,"select v.text_value,v.quote_key from STC_LIST_VALUES v
	where id=:val_id");	
	
	$q_phone=OCIParse($c,"select phone,allow from STC_PHONES t
	where t.base_id=:base_id
	order by ord");
		
	echo "<table>";
	echo "<tr><th>№</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$key."</th>";
		if($fld['std_name']=='PHONE') echo "<th>".$key."</th>";	
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "<th></th>";
	echo "</tr>";
	echo "<tr><td>станд.</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['std_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "<th></th>";
	echo "</tr>";	
	echo "<tr><td>код</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['code_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "<th></th>";
	echo "</tr>";
	echo "<tr><td>ID</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['id']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "<th></th>";
	echo "</tr>";
	echo "<tr><td>имя</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$fld['text_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th>Найденные телефоны</th>";	
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th>Ключ квоты</th>";
	}
	echo "<th>статус</th>";
	echo "</tr>";		

	OCIExecute($q_row,OCI_DEFAULT);		
	$i=0; while(OCIFetch($q_row)) {
		if($show_rows<>'all' and $i>=$show_rows) break;
		$i++;
		$row_id=OCIResult($q_row,"ID");
		$status=OCIResult($q_row,"STATUS");
		echo "<tr><th>".$row_id."</td>";
		foreach($flds as $key=>$fld) {
			//значения
			OCIBindByName($q_val,":base_id",$row_id);
			OCIBindByName($q_val,":field_id",$fld['id']);
			OCIExecute($q_val,OCI_DEFAULT);
			$j=0; while(OCIFetch($q_val)) {$j++;

				if(OCIResult($q_val,"LIST_VALUE_ID")<>'') {
					
					$list_value_id=OCIResult($q_val,"LIST_VALUE_ID");
					OCIBindByName($q_list_val,":val_id",$list_value_id);
					OCIExecute($q_list_val,OCI_DEFAULT);
					OCIFetch($q_list_val);
					echo "<td>".OCIResult($q_list_val,"TEXT_VALUE")."</td>";
					if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<td>".OCIResult($q_list_val,"QUOTE_KEY")."</td>";
									
				}
				else echo "<td>".OCIResult($q_val,"TEXT_VALUE")."</td>";
			}
			if($j==0) {
				echo "<td></td>";
				if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<td></td>";
			} 
			
			//телефоны
			if($fld['std_name']=='PHONE') {
				OCIBindByName($q_phone,":base_id",$row_id);
				OCIExecute($q_phone,OCI_DEFAULT);				
				echo "<td>";
				while(OCIFetch($q_phone)) {
					echo OCIResult($q_phone,"PHONE")."<br>";
				}
				echo "</td>"; 
			}
		}
		echo "<td>".$status."</td>";
		echo "</tr>";
	}
	echo "</table>";
	if($i==0) echo "<font size=3 color=red>Нет данных</font>";
?>