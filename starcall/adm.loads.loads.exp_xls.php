<?php 
include("starcall/session.cfg.php");
set_time_limit(0);

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if($_SESSION['user']['rw_src_bd']<>'r' and $_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

if(!isset($mark)) exit();

$load_ids=implode(',',$mark);

header("Content-type: application/xls; charset=windows-1251");
header("Content-Disposition: attachment; filename=\"выгрузка.xls\""); 

echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251">';

include("starcall/conn_string.cfg.php");

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
	
	if(isset($allowed_only)) $where=" and t.allow='y'"; else $where='';
	
	$q_row=OCIParse($c,"select t.id,h.file_name from STC_BASE t, STC_LOAD_HISTORY h
	where t.project_id='".$_SESSION['adm']['project']['id']."' and t.load_hist_id in (".$load_ids.")
	and h.id=t.load_hist_id and h.project_id=t.project_id ".$where." 
	order by id");
		
	$q_val=OCIParse($c,"select t.text_value from STC_FIELD_VALUES t
	where t.base_id=:base_id and t.field_id=:field_id");
	
	$q_phone=OCIParse($c,"select phone,ext from STC_PHONES t
	where t.base_id=:base_id ".$where."
	order by ord");
		
	echo "<table border=1>";
	echo "<tr><th></th><th>№</th>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$key."</th>";
		if($fld['std_name']=='PHONE') echo "<th>".$key."</th>";	
	}
	echo "</tr>";
	echo "<tr><td></td><td>станд.</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['std_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
	}
	echo "</tr>";	
	echo "<tr><td></td><td>код</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['code_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";
	}
	echo "</tr>";
	echo "<tr><th></td><td>ID</td>";
	foreach($flds as $key=>$fld) {
		echo "<th bgcolor=white>".$fld['id']."</th>";
		if($fld['std_name']=='PHONE') echo "<th></th>";	
	}
	echo "</tr>";	
	echo "<tr><td>№</td><td>имя</td>";
	foreach($flds as $key=>$fld) {
		echo "<th>".$fld['text_name']."</th>";
		if($fld['std_name']=='PHONE') echo "<th>".(isset($allowed_only)?'Одобренные телефоны':'Найденные телефоны')."</th>";
	}
	echo "</tr>";
		
	OCIExecute($q_row,OCI_DEFAULT);		
	$i=0; while(OCIFetch($q_row)) {
		$i++;
		echo "<tr><th>$i</td><td>".OCIResult($q_row,"FILE_NAME")."</td>";			
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
				echo "<td>";
				OCIBindByName($q_phone,":base_id",$row_id);
			//	OCIBindByName($q_phone,":field_id",$fld['id']);
				OCIExecute($q_phone,OCI_DEFAULT);				
				//$phones=array();
				$phones='';
				$p=0; $ext=''; while(OCIFetch($q_phone)) {
					if($p>0) $phones.=';';
					$phones.=OCIResult($q_phone,"PHONE");
					if(OCIResult($q_phone,"EXT")<>'') $phones.="#".OCIResult($q_phone,"EXT"); else $ext='no_ext';
					$p++;
				}
				if($p==1 and strlen($phones)>11 and $ext=='no_ext') $phones="'".$phones; //добавляем глупому экселю символ, что бы он не думал, что это число  
				echo $phones;
				echo "</td>"; 
			}
		}
		echo "</tr>";
	}
	echo "</table>";
?>