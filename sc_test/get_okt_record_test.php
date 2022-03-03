<?php
extract($_REQUEST);

if((!isset($idchain) or !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',$idchain)) 
	and (!isset($idconnection) or !preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',$idconnection)
	)
) {echo "error: invalid id"; exit();}

//загрузка одной записи
if(isset($idconnection)) {
	include("../../sc_conf/sc_oktell_conn_string");	
	include("../../sc_conf/sc_path");
	include("../../sc_conf/sc_adm_url");	
	
	$sql="SELECT
	  t.id IdConnection, t.IdChain,
	  convert(varchar(25),timestart,121) timestart,
	  substring(convert(varchar(25),timestart,121),1,4)+ --YYYY
	  substring(convert(varchar(25),timestart,121),6,2)+ --DD
	  substring(convert(varchar(25),timestart,121),9,2)+ --MM
	  '\\'+
	  substring(convert(varchar(25),timestart,121),12,2)+ --HH24
	  substring(convert(varchar(25),timestart,121),15,2)+ --MI
	  '\\' file_path,
	  'mix_'+(case when alinenum<blinenum then alinenum else blinenum end)+'_'+(case when blinenum>alinenum then blinenum else alinenum end)+'__'+
	  substring(convert(varchar(25),timestart,121),1,4)+'_'+ --YYYY
	  substring(convert(varchar(25),timestart,121),6,2)+'_'+ --MM
	  substring(convert(varchar(25),timestart,121),9,2)+'__'+ --DD
	  substring(convert(varchar(25),timestart,121),12,2)+'_'+ --HH24
	  substring(convert(varchar(25),timestart,121),15,2)+'_'+ --MI
	  substring(convert(varchar(25),timestart,121),18,2)+'_'+ --SS
	  substring(convert(varchar(25),timestart,121),21,3)+ --mSS
	  '.mp3' file_name,
	  t.AOutNumber,t.BOutNumber,
	  substring(convert(varchar(25),timestart,121),1,4) YYYY,
	  substring(convert(varchar(25),timestart,121),6,2) MM,
	  substring(convert(varchar(25),timestart,121),9,2) DD,
	  substring(convert(varchar(25),timestart,121),12,2) HH24,
	  substring(convert(varchar(25),timestart,121),15,2) MI,
	  substring(convert(varchar(25),timestart,121),18,2) SS,
	  substring(convert(varchar(25),timestart,121),21,3) mSS,
	  case 
	  when t.ConnectionType=1 then 'Изнутри наружу'
	  when t.ConnectionType=2 then 'Изнутри в IVR'
	  when t.ConnectionType=3 then 'Изнутри внутрь'
	  when t.ConnectionType=4 then 'Снаружи в IVR'
	  when t.ConnectionType=5 then 'Снаружи внутрь'
	  when t.ConnectionType=6 then 'Снаружи наружу'
	  when t.ConnectionType=7 then 'С IVR наружу'
	  when t.ConnectionType=8 then 'С IVR внутрь'
	  end call_direction
	FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
	where Id=:idconnection and IsRecorded=1";
	
	$q=$c_okt->prepare($sql);
	
	$q->bindValue(':idconnection',$idconnection);
	$q->execute();
	//$q=sqlsrv_query($c_okt,$sql);
	
	if($row=$q -> fetch()) {
		
		$file_path=$oktell_records_path.$row['file_path'];
		$file_name=$row['file_name'];
		
		//если передана дата, то она вставляется в имя файла
		if(!isset($datecall)) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
		
		//если передано baseid, то оно втсравляется в имя файла
		if(isset($baseid)) $baseid.='-'; else $baseid='';
		
		//если передан номер части, то она вставляется в имя
		if(isset($partnum)) $partnum.=' '; else $partnum='';
		
		//имя файла на выходе (если передано имя в переменной name, то файл именуется этим именем)
		if(isset($name) and $name<>'') $new_file_name=$name; 
		else { 
			$new_file_name=$baseid.$datecall.str_replace(" ","-",
			" ".
			substr($row['IdChain'],0,8)." ".
			$partnum.
			str_replace(" ","_",$row['call_direction'])." ".
			$row['AOutNumber']." ".
			$row['BOutNumber'].".mp3");
		}
		
		for($i=1;$i<=10000;$i++) {
		echo "time: ".time();
		if(file_exists($file_path.$file_name)) {
			
			$old=time()-filemtime($file_path.$file_name);
			
			echo " | Дата создания: ".filectime($file_path.$file_name);
			echo " | Дата изменения: ".filemtime($file_path.$file_name);
			echo " | Размер: ".filesize($file_path.$file_name);
			echo " | Возраст (сек): ".$old;
			echo "<br>";
	
			//header("Content-Type: ".mime_content_type($oktell_records_path.$filename));
			//header('Content-Type: application/octet-stream');		
			/*
			header('Content-Type: audio/mpeg');
			header('Content-Disposition: attachment; filename="'.$new_file_name.'"');
			header('Content-Length: '.filesize($file_path.$file_name)); 
			header('accept-ranges: bytes');	
			readfile($file_path.$file_name);
			*/
			//exit();		
		}
		else {echo " | error: file not found<br>";}
		usleep(10);
		}

		
		//else {echo "error: file not found"; exit();}
	} 
	else {echo "error: record not found"; exit();}
	
	
	
}
//запрос списка записей по цепочке уоммутаций
else if(isset($idchain)) {
	include("../../sc_conf/sc_oktell_conn_string");	
	include("../../sc_conf/sc_path");
	include("../../sc_conf/sc_adm_url");		
	echo "<!DOCTYPE html>
	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1251'>
	<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
	<title>Запись разговора</title>
	</head>
	<body>";
	$sql="SELECT
		t.id IdConnection, t.IdChain,
		convert(varchar(25),timestart,121) timestart,
		substring(convert(varchar(25),timestart,121),1,4)+
		substring(convert(varchar(25),timestart,121),6,2)+
		substring(convert(varchar(25),timestart,121),9,2)+'\\'+
		substring(convert(varchar(25),timestart,121),12,2)+
		substring(convert(varchar(25),timestart,121),15,2)+'\\' file_path,
		'mix_'+(case when alinenum<blinenum then alinenum else blinenum end)+'_'+(case when blinenum>alinenum then blinenum else alinenum end)+'__'+
		substring(convert(varchar(25),timestart,121),1,4)+'_'+
		substring(convert(varchar(25),timestart,121),6,2)+'_'+
		substring(convert(varchar(25),timestart,121),9,2)+'__'+
		substring(convert(varchar(25),timestart,121),12,2)+'_'+
		substring(convert(varchar(25),timestart,121),15,2)+'_'+
		substring(convert(varchar(25),timestart,121),18,2)+'_'+
		substring(convert(varchar(25),timestart,121),21,3)+'.mp3' file_name,
		t.AOutNumber,t.BOutNumber,
		substring(convert(varchar(25),timestart,121),1,4) YYYY,
		substring(convert(varchar(25),timestart,121),6,2) MM,
		substring(convert(varchar(25),timestart,121),9,2) DD,
		substring(convert(varchar(25),timestart,121),12,2) HH24,
		substring(convert(varchar(25),timestart,121),15,2) MI,
		substring(convert(varchar(25),timestart,121),18,2) SS,
		substring(convert(varchar(25),timestart,121),21,3) mSS,			
		case 
		when t.ConnectionType=1 then 'Изнутри наружу'
		when t.ConnectionType=2 then 'Изнутри в IVR'
		when t.ConnectionType=3 then 'Изнутри внутрь'
		when t.ConnectionType=4 then 'Снаружи в IVR'
		when t.ConnectionType=5 then 'Снаружи внутрь'
		when t.ConnectionType=6 then 'Снаружи наружу'
		when t.ConnectionType=7 then 'С IVR наружу'
		when t.ConnectionType=8 then 'С IVR внутрь'
		end call_direction
		FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
		where IdChain=:idchain and IsRecorded=1
		order by TimeStart";
	//echo "<textarea>$sql</textarea>";


	$q=$c_okt->prepare($sql);
	$q->bindValue(':idchain',$idchain);
	
	$q->execute();

	$partnum=0; while($row=$q -> fetch()) {$partnum++;
	
		if(!isset($datecall) and $partnum==1) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
    
		$file_path=$oktell_records_path.$row['file_path'];
		$file_name=$row['file_name'];
		$new_file_name=$row['file_name'];	

		if(file_exists($file_path.$file_name)) {
			$src=$oktell_records_url.'?idconnection='.$row['IdConnection'].(isset($datecall)?"&datecall=".$datecall:"")."&partnum=".$partnum;
			echo "<a href='".$src."'>".$partnum.". ".$row['call_direction']." (скачать запись)</a>";
			echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";
	
		}
	}	
}
?>