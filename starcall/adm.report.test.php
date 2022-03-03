<?php include("starcall/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';

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

include("starcall/conn_string.cfg.php");

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

	$q_row=OCIParse($c,"select b.id,
	to_char(b.status_date,'DD.MM.YYYY HH24:MI:SS') status_date,
	s.name status,
	to_char(b.nedoz_date,'DD.MM.YYYY HH24:MI:SS') nedoz_date,
	b.nedoz_count,
	to_char(b.perez_date_msk,'DD.MM.YYYY HH24:MI:SS') perez_date_msk,
	round((b.status_date-b.start_date)*24*60*60) dur_sec,
	u.login,u.fio,
	b.utc_msk,
	p.phone||decode(p.ext,null,null,'#'||p.ext) phone
	from STC_BASE b, Stc_Li_Ank_Status s, Stc_Users u, Stc_Phones p
	where b.project_id=".$_SESSION['adm']['project']['id']."
	and s.id(+)=b.status and u.id(+)=b.status_user and p.id(+)=b.phone_id
	order by b.status_date");
		
	$q_val=OCIParse($c,"select fv.list_value_id, fv.text_value from STC_FIELD_VALUES fv
	where fv.base_id=:base_id and fv.field_id=:field_id");

	$q_list_val=OCIParse($c,"select v.text_value,v.quote_key from STC_LIST_VALUES v
	where id=:val_id");	
	
	$q_phone=OCIParse($c,"select phone,ext,allow from STC_PHONES t
	where t.base_id=:base_id
	order by ord");
		
	echo "<table class=white_table>";
	echo "<tr><th>ID</td>";
	echo "<th>стат. 1</td><th>стат. 2</td><th>стат. 3</td><th>стат. 4</td><th>стат. 5</td><th>стат. 6</td><th>стат. 7</td><th>стат. 8</td><th>стат. 9</td><th>стат. 10</td>";
	$s=0;$a=0;
	foreach($flds as $key=>$fld) {
		if($fld['src_type_id']==1) {$s++; echo "<th>исх. ".$s."</th>";}
		if($fld['src_type_id']==2) {$a++; echo "<th>опр. ".$a."</th>";}
		if($fld['std_name']=='PHONE') echo "<th></th>";	
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "</tr>";
	echo "<tr><td>станд.</td>";
	echo "<th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['std_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "</tr>";	
	echo "<tr><td>код</td>";
	echo "<th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td>";	
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['code_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "</tr>";
	echo "<tr><td>ID</td>";
	echo "<th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td><th></td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['id']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th></th>";
	}
	echo "</tr>";
	echo "<tr><td>имя</td>";
	echo "<th>Дата статуса</td><th>Статус</td><th>Длит.(сек)</td><th>Час. пояс (мск)</td><th>Номер дозвона</td><th>Дата перезв.(мск)</td><th>Дата посл.недоз.</td><th>Кол-во недоз.</td><th>Опер. логин</td><th>Опер. ФИО</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$fld['text_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th>Найденные телефоны</th>";	
		if($fld['quoted']<>'' and $fld['src_type_id']==2) echo "<th>Ключ квоты</th>";
	}
	echo "</tr>";		

	OCIExecute($q_row,OCI_DEFAULT);		
	$i=0; while(OCIFetch($q_row)) {
		if($show_rows<>'all' and $i>=$show_rows) break;
		$i++;
		$row_id=OCIResult($q_row,"ID");
		$status=OCIResult($q_row,"STATUS");
		echo "<tr><th>".$row_id."</td>";
		echo "<th>".OCIResult($q_row,"STATUS_DATE")."</td><th>".OCIResult($q_row,"STATUS")."</td><th>".OCIResult($q_row,"DUR_SEC")."</td><th>".OCIResult($q_row,"UTC_MSK")."</td><th>".OCIResult($q_row,"PHONE")."</td><th>".OCIResult($q_row,"PEREZ_DATE_MSK")."</td><th>".OCIResult($q_row,"NEDOZ_DATE")."</td><th>".OCIResult($q_row,"NEDOZ_COUNT")."</td><th>".OCIResult($q_row,"LOGIN")."</td><th>".OCIResult($q_row,"FIO")."</td>";
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