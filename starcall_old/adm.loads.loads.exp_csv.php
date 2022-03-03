<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_time_limit(0);

extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if($_SESSION['user']['rw_src_bd']<>'r' and $_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

if(!isset($mark)) exit();

$load_ids=implode(',',$mark);

header("Content-type: application/csv; charset=windows-1251");
header("Content-Disposition: attachment; filename=\"выгрузка.csv\""); 

//echo '<meta http-equiv=Content-Type content="text/html; charset=windows-1251">';

include("../../conf/starcall_conf/conn_string.cfg.php");

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
	
	$q_phone=OCIParse($c,"select phone from STC_PHONES t
	where t.base_id=:base_id ".$where."
	order by ord");
	
	echo '"";"№"';
	foreach($flds as $key=>$fld) {
		echo ';"'.$key.'"';
		if($fld['std_name']=='PHONE') echo ';"'.$key.'"';	
	}
	echo chr(10);

	echo '"";"станд."';
	foreach($flds as $key=>$fld) {
		echo ';"'.$fld['std_name'].'"';
		if($fld['std_name']=='PHONE') echo ';""';
	}
	echo chr(10);	
	
	echo '"";"код"';
	foreach($flds as $key=>$fld) {
		echo ';"'.$fld['code_name'].'"';
		if($fld['std_name']=='PHONE') echo ';""';
	}
	echo chr(10);
	
	echo '"";"ID"';
	foreach($flds as $key=>$fld) {
		echo ';"'.$fld['id'].'"';
		if($fld['std_name']=='PHONE')  echo ';""';
	}
	echo chr(10);	

	echo '"№";"имя"';	
	foreach($flds as $key=>$fld) {
		echo ';"'.$fld['text_name'].'"';
		if($fld['std_name']=='PHONE') echo ';"'.(isset($allowed_only)?'Одобренные телефоны':'Найденные телефоны').'"';
	}
	echo chr(10);
	
	OCIExecute($q_row,OCI_DEFAULT);		
	$i=0; while(OCIFetch($q_row)) {
		$i++;
		echo '"'.$i.'";"'.OCIResult($q_row,"FILE_NAME").'"';			
		$row_id=OCIResult($q_row,"ID");
		foreach($flds as $key=>$fld) {
			//значения
			OCIBindByName($q_val,":base_id",$row_id);
			OCIBindByName($q_val,":field_id",$fld['id']);
			OCIExecute($q_val,OCI_DEFAULT);
			$j=0; while(OCIFetch($q_val)) {$j++;
				echo ';"'.str_replace('"','""',OCIResult($q_val,"TEXT_VALUE")).'"';
			}
			if($j==0) echo ';""'; 
			
			//телефоны
			if($fld['std_name']=='PHONE') {
				echo ';"';
				OCIBindByName($q_phone,":base_id",$row_id);
				OCIExecute($q_phone,OCI_DEFAULT);				
				$phones='';
				$p=0; while(OCIFetch($q_phone)) {
					if($p>0) $phones.=';';
					$phones.=OCIResult($q_phone,"PHONE");
					$p++;
				}
				if($p==1 and strlen($phones)>11) $phones.=';'; //добавляем глупому экселю символ, что бы он не думал, что это число  
				echo $phones;
				echo '"'; 
			}
		}
		echo chr(10);
	}
?>