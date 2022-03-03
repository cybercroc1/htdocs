<?php
extract($_REQUEST);

	echo "<!DOCTYPE html>
	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1251'>
	<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
	<title>Запись разговора</title>
	</head>
	<body>";

$default_oktell_server_id='1905';

if(isset($secret)) {
	if(!isset($baseid) or !is_numeric($baseid)) {echo "error: invalid id"; exit();}
	if(!preg_match('/^[0-9A-F]{32}$/',$secret)) {echo "error: invalid secret"; exit();}

	include("med/conn_string.cfg.php");	
	include("med/func_get_okt_connections_info.php");
	
	//цепочка коммутаций входящего звонка]
	$q=OCIParse($c,"select cb.id,
	to_char(cb.date_call,'YYYY') YYYY, 
	to_char(cb.date_call,'MM') MM, 
	to_char(cb.date_call,'DD') DD, 
	to_char(cb.date_call,'HH24') HH24, 
	to_char(cb.date_call,'MI') MI,
	to_char(cb.date_call,'SS') SS,  
	cb.anumber,cb.bnumber,cb.oktell_idchain,nvl(cb.oktell_server_id,'".$default_oktell_server_id."') oktell_server_id from CALL_BASE cb 
	where id='".$baseid."' and cb.secret='".$secret."'");

	OCIExecute($q);
	$conncount=0;
	if(OCIFetch($q)) {
		$src_data['idChain']=OCIResult($q,"OKTELL_IDCHAIN");
		$src_data['idUser']='';
		$src_data['oktell_server_id']=OCIResult($q,"OKTELL_SERVER_ID");
		$src_data['AOutNumber']=OCIResult($q,"ANUMBER");
		$src_data['BOutNumber']=OCIResult($q,"BNUMBER");
		$src_data['AStr']='';
		$src_data['BStr']='';
		$src_data['YYYY']=OCIResult($q,"YYYY");
		$src_data['MM']=OCIResult($q,"MM");
		$src_data['DD']=OCIResult($q,"DD");
		$src_data['HH24']=OCIResult($q,"HH24");
		$src_data['MI']=OCIResult($q,"MI");
		$src_data['SS']=OCIResult($q,"SS");
		$src_data['mSS']='000';
		echo "<font size=4><b>Входящая заявка № ".$baseid."</b></font><hr>";
		$base_id=OCIResult($q,"ID");
		$okt_server_id=OCIResult($q,"OKTELL_SERVER_ID");
		$idchain=OCIResult($q,"OKTELL_IDCHAIN");
		if($idchain<>'') {
			$conncount++;
			$res_arr=get_okt_connections_info($c,$okt_server_id,$idchain);

			if(!$res_arr or isset($res_arr['error'])) {
				$info_arr[1]['IdConnection']='';
				$info_arr[1]['call_direction']='Ошибка';
				$info_arr[1]['ConnectionType']='0';
				$info_arr[1]['AOutNumber']=$src_data['AOutNumber'];
				$info_arr[1]['BOutNumber']=$src_data['BOutNumber'];
				$info_arr[1]['AStr']=$src_data['AStr'];
				$info_arr[1]['BStr']=$src_data['BStr'];
				$info_arr[1]['YYYY']=$src_data['YYYY'];
				$info_arr[1]['MM']=$src_data['MM'];
				$info_arr[1]['DD']=$src_data['DD'];
				$info_arr[1]['HH24']=$src_data['HH24'];
				$info_arr[1]['MI']=$src_data['MI'];
				$info_arr[1]['SS']=$src_data['SS'];
				$info_arr[1]['mSS']=$src_data['mSS'];
				$info_arr[1]['IsRecorded']='0';
				$info_arr[1]['oktell_records_url']='';
				$info_arr[1]['recnum']='';
				$info_arr[1]['duration_sec']='0';			
				if(!$res_arr) 					$info_arr[1]['ReasonFailed']='Запись не найдена';
				if(isset($res_arr['error'])) 	$info_arr[1]['ReasonFailed']=$res_arr['error'];
				
				show_connections_info($info_arr,"Ошибка",$src_data);
			}
			else show_connections_info($res_arr,"Входящий",$src_data);
			echo "<hr>";
		}
	}
	//цепочки комутаций исходящих
	$q=OCIParse($c,"select 
	to_char(h.start_date,'YYYY') YYYY, 
	to_char(h.start_date,'MM') MM, 
	to_char(h.start_date,'DD') DD, 
	to_char(h.start_date,'HH24') HH24, 
	to_char(h.start_date,'MI') MI,
	to_char(h.start_date,'SS') SS,  	
	h.oktell_server_id,h.oktell_idchain,h.oktell_iduser,h.phone_prefix,h.phone_number,h.user_id 
	from OKTELL_CALL_HIST h 
	where h.base_id='".$baseid."'
	order by h.start_date");
	OCIExecute($q);
	while(OCIFetch($q)) {
		$conncount++;
		$src_data['idChain']=OCIResult($q,"OKTELL_IDCHAIN");
		$src_data['idUser']=OCIResult($q,"OKTELL_IDUSER");
		$src_data['oktell_server_id']=OCIResult($q,"OKTELL_SERVER_ID");
		$src_data['AOutNumber']='';
		$src_data['BOutNumber']=OCIResult($q,"PHONE_NUMBER");
		$src_data['AStr']='';
		$src_data['BStr']='';
		$src_data['YYYY']=OCIResult($q,"YYYY");
		$src_data['MM']=OCIResult($q,"MM");
		$src_data['DD']=OCIResult($q,"DD");
		$src_data['HH24']=OCIResult($q,"HH24");
		$src_data['MI']=OCIResult($q,"MI");
		$src_data['SS']=OCIResult($q,"SS");
		$src_data['mSS']='000';		
		$okt_server_id=OCIResult($q,"OKTELL_SERVER_ID");
		$idchain=OCIResult($q,"OKTELL_IDCHAIN");
		
		$res_arr=get_okt_connections_info($c,$okt_server_id,$idchain);
		
		if(!$res_arr or isset($res_arr['error'])) {
			$info_arr[1]['IdConnection']='';
			$info_arr[1]['call_direction']='Ошибка';
			$info_arr[1]['ConnectionType']='0';
			$info_arr[1]['AOutNumber']='';
			$info_arr[1]['BOutNumber']=$src_data['BOutNumber'];
			$info_arr[1]['AStr']='';
			$info_arr[1]['BStr']='';			
			$info_arr[1]['YYYY']=$src_data['YYYY'];
			$info_arr[1]['MM']=$src_data['MM'];
			$info_arr[1]['DD']=$src_data['DD'];
			$info_arr[1]['HH24']=$src_data['HH24'];
			$info_arr[1]['MI']=$src_data['MI'];
			$info_arr[1]['SS']=$src_data['SS'];
			$info_arr[1]['mSS']='000';
			$info_arr[1]['IsRecorded']='0';
			$info_arr[1]['oktell_records_url']='';
			$info_arr[1]['recnum']='';
			$info_arr[1]['duration_sec']='0';			
			if(!$res_arr) 					$info_arr[1]['ReasonFailed']='Запись не найдена';
			if(isset($res_arr['error'])) 	$info_arr[1]['ReasonFailed']=$res_arr['error'];
			
			show_connections_info($info_arr,"Ошибка",$src_data);
		}
		else show_connections_info($res_arr,"Исходящий",$src_data);
		echo "<hr>";		
	}		
	if($conncount==0) echo "<font color=red><b>Нет звонков по заявке</b></font><br>";
}
//запрос списка записей по цепочке коммутаций
else if(isset($idchain)) {
	if(!preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',$idchain)) {echo "error: invalid idchain"; exit();}
	
	include("med/conn_string.cfg.php");	
	include("med/func_get_okt_connections_info.php");

	//ищем заявку по цепочке коммутаций
	$q=OCIParse($c,"select t.base_id,
	t.start_date,

	to_char(t.start_date,'YYYY') YYYY, 
	to_char(t.start_date,'MM') MM, 
	to_char(t.start_date,'DD') DD, 
	to_char(t.start_date,'HH24') HH24, 
	to_char(t.start_date,'MI') MI,
	to_char(t.start_date,'SS') SS, 	
	
	t.type,t.oktell_server_id from (
	select b.id base_id,b.date_call start_date,'Входящий' type,oktell_server_id from CALL_BASE b where oktell_idchain='".strtolower($idchain)."' 
	union all
	select h.base_id, h.start_date,'Исходящий' type,oktell_server_id from OKTELL_CALL_HIST h where oktell_idchain='".strtolower($idchain)."'
	order by start_date
	) t
	where rownum=1");

	OCIExecute($q);
	if(OCIFetch($q)) {
		
		$src_data['idChain']=$idchain;
		$src_data['idUser']='';
		$src_data['oktell_server_id']=OCIResult($q,"OKTELL_SERVER_ID");
		$src_data['AOutNumber']='';
		$src_data['BOutNumber']='';
		$src_data['AStr']='';
		$src_data['BStr']='';
		$src_data['YYYY']=OCIResult($q,"YYYY");
		$src_data['MM']=OCIResult($q,"MM");
		$src_data['DD']=OCIResult($q,"DD");
		$src_data['HH24']=OCIResult($q,"HH24");
		$src_data['MI']=OCIResult($q,"MI");
		$src_data['SS']=OCIResult($q,"SS");
		$src_data['mSS']='000';	
		
		$okt_server_id=OCIResult($q,"OKTELL_SERVER_ID");
		$call_type=OCIResult($q,"TYPE");
		
		$res=get_okt_connections_info($c,$okt_server_id,$idchain);
		if(!$res) {echo "Запись не найдена<br>";}
		elseif(isset($res['error'])) {echo $res['error']."<br>";}
		else show_connections_info($res,$call_type,$src_data);
	}	
	
	
}
//загрузка одной записи 
//ПЕРЕНЕСЕНО в http://sc.wilstream.ru/sc/get_okt_record.php 
//после внедрения прослушки исходящих ЭТОТ БЛОК УДАЛИТЬ
/*elseif(isset($idconnection)) {
	include("med/oktell_conn_string.php");
	include("oktell_records_path.php");
	include("med/adm_url.php");

	
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
		
		//имя файла на выходе (если передано имя в переменной name, то файл именуется этим именем)
		if(!isset($datecall)) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
	
		if(isset($partnum)) $partnum.=' '; else $partnum='';
	
		if(isset($name) and $name<>'') $new_file_name=$name; 
		else { 
			$new_file_name=$datecall.str_replace(" ","-",
			" ".
			substr($row['IdChain'],0,8)." ".
			$partnum.
			str_replace(" ","_",$row['call_direction']).
			//" ".$row['AOutNumber'].
			//" ".$row['BOutNumber'].
			".mp3");
		}
		
		if(file_exists($file_path.$file_name)) {
		
			
			
			//header("Content-Type: ".mime_content_type($oktell_records_path.$filename));
			//header('Content-Type: application/octet-stream');		
			header('Content-Type: audio/mpeg');
			header('Content-Disposition: attachment; filename="'.$new_file_name.'"');
			header('Content-Length: '.filesize($file_path.$file_name)); 
			header('accept-ranges: bytes');	
			readfile($file_path.$file_name);
			exit();		
		}
		else {echo "error: file not found"; exit();}
	} 
	else {echo "error: record not found"; exit();}
	
	
	
}*/
	
function show_connections_info($connections,$type,$src_data) {
	global $base_id;
	foreach($connections as $partnum => $val) {
		if($partnum==1) $datecall=$val['YYYY'].$val['MM'].$val['DD']."-".$val['HH24'].$val['MI'].$val['SS']."-".$val['mSS'];
	
		//if($partnum==1) echo "<font size=3><b>".$val['DD'].".".$val['MM'].".".$val['YYYY']." ".$val['HH24'].":".$val['MI'].":".$val['SS']."</b> - <b>".($val['ConnectionType']==0?"Недозвон":$type)."</b></font><br>";
		if($partnum==1) echo "<font size=3><b>".$src_data['DD'].".".$src_data['MM'].".".$src_data['YYYY']." ".$src_data['HH24'].":".$src_data['MI'].":".$src_data['SS']."</b> - <b>".($val['ConnectionType']==0?"Недозвон":$type)."</b></font><br>";
		
		if($val['IsRecorded']==1) $src=$val['oktell_records_url'].'?idconnection='.$val['IdConnection'].(isset($base_id)?"&baseid=".$base_id:"")."&datecall=".$datecall."&partnum=".$partnum; 
		else $src='';
		
		echo "<nobr>Часть ".$partnum.". <b>{$val['DD']}.{$val['MM']}.{$val['YYYY']} {$val['HH24']}:{$val['MI']}:{$val['SS']}.{$val['mSS']}</b>. </nobr>";
		if($val['ConnectionType']==4) echo "<nobr><b>".$val['call_direction']."</b>. </nobr>"; 
		if($val['IsRecorded']==1) echo "<nobr>Запись: <b><a href='".$src."'>".$val['recnum'].". ".$val['call_direction']." (скачать)</a></b>. </nobr>";
		if($val['ConnectionType']==0) echo "<nobr><b>".$val['call_direction']."</b>. Причина: <b>".$val['ReasonFailed']."</b>. </nobr>";
		//if($val['call_direction']=="") 
		if($val['call_direction']=='Ошибка') 
			echo "<nobr>ID Цепочки: <b>{$src_data['idChain']}</b>. </nobr><nobr>ID Пользователя: <b>{$src_data['idUser']}</b>. </nobr><nobr>Номер Б: <b>{$val['BOutNumber']}</b>. </nobr>";
		else
			echo "<nobr>Номер А: <b>{$val['AOutNumber']}</b>. </nobr><nobr>Имя А: <b>{$val['AStr']}</b>. </nobr><nobr>Номер Б: <b>{$val['BOutNumber']}</b>. </nobr><nobr>Имя Б: <b>{$val['BStr']}</b>. </nobr><nobr>Длительность: <b>".(date("H:i:s", mktime(0, 0, $val['duration_sec'])))."</b></nobr>";
		echo "<br>";
		
		if($val['IsRecorded']==1) {echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";}
	}
}

?>