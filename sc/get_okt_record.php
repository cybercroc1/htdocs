<?php
extract($_REQUEST);

if(isset($idchain)) $idchain=strtoupper($idchain);
if(isset($idconnection)) $idconnection=strtoupper($idconnection);
if(isset($acc)) $acc=strtoupper($acc);

if((!isset($idchain) or !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/',$idchain)) 
	and (!isset($idconnection) or !preg_match('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/',$idconnection)
	)
) {echo "error: invalid id"; exit();}

//�������� ����� ������
if(isset($idconnection)) {
	include("oktell_conn_string.php");	
	include("sc/sc_path.php");
	include("sc/sc_adm_url.php");	
	
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
	  when t.ConnectionType=1 then '������� ������'
	  when t.ConnectionType=2 then '������� � IVR'
	  when t.ConnectionType=3 then '������� ������'
	  when t.ConnectionType=4 then '������� � IVR'
	  when t.ConnectionType=5 then '������� ������'
	  when t.ConnectionType=6 then '������� ������'
	  when t.ConnectionType=7 then '� IVR ������'
	  when t.ConnectionType=8 then '� IVR ������'
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
		
		//���� �������� ����, �� ��� ����������� � ��� �����
		if(!isset($datecall)) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
		
		//���� �������� baseid, �� ��� ������������ � ��� �����
		if(isset($baseid)) $baseid.='-'; else $baseid='';
		
		//���� ������� ����� �����, �� ��� ����������� � ���
		if(isset($partnum)) $partnum.=' '; else $partnum='';
		
		//��� ����� �� ������ (���� �������� ��� � ���������� name, �� ���� ��������� ���� ������)
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

		if(isset($btx24)) {
			$log=fopen('logs/get_okt_record_'.date('Y-m-d-h-i-s').'.log','a+');
			fwrite($log,date('Y-m-d-h-i-s')." ����� 5 ���\n");
			sleep(5); //�������� ��� ��������, ��� �� ���� ����� ���������.
		}
		if(file_exists($file_path.$file_name)) {
		
			//$fileage=time()-filectime($file_path.$file_name); //������� ����� � ��������
			//if($fileage < 600) {//���� ������� ������ 10 �����
			if(isset($btx24)) { //��� ��������
				//����, ���� ������ ����� �� ���������� ��������
				$filesize_tmp=-1;
				$x=0;
				while(filesize($file_path.$file_name)!=$filesize_tmp) {$x++;
					fwrite($log,$x.'. '.date('Y-m-d-h-i-s').' ������ ������ �����: '.$filesize_tmp."\n");
					$filesize_tmp=filesize($file_path.$file_name);
					fwrite($log,$x.'. '.date('Y-m-d-h-i-s').' ����� ������ �����: '.$filesize_tmp."\n");
					fwrite($log,$x.'. '.date('Y-m-d-h-i-s')." ����� 3 ���\n");
					sleep(3);
				}
				fwrite($log,$x.'. '.date('Y-m-d-h-i-s').' �������� ������ �����: '.$filesize_tmp."\n");
				fclose($log);
			}
			
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
	
	
	
}
else if(isset($idchain)) {
	include("oktell_conn_string.php");	
	include("sc/sc_path.php");
	include("sc/sc_adm_url.php");		
	echo "<!DOCTYPE html>
	<head>
	<meta http-equiv=Content-Type content='text/html; charset=windows-1251'>
	<link href=\"billing.css\" rel=\"stylesheet\" type=\"text/css\">
	<title>������ ���������</title>
	</head>
	<body>";
	if($_SERVER['SERVER_NAME']=='sc.wilstream.ru' and isset($acc) and $acc==strtoupper(substr(md5(strtolower($idchain).'-full'),0,8))) { //������ �� ���� ���� �� 1905
		//������ ������ ������� �� ������� ����������
		//������ - ����������� �� 1-001 ����������� ��������
		$sql="select 
		convert(varchar, a.timeStart,120) [���� ������],
		a.id IdConnection,
		a.idChain [ID �������],
		a.route_name [�������� ��������],
		a.in_task_key [���� ������], 
		a.bnumber [���������� �����],
		a.direction_code [���������� �����],
		a.TimeStart,a.OriginateDate,a.QueueDate,a.TimeAnswer,a.TimeStop,
		case when a.ConnectionType='4' then '�������� IVR' 
		when a.ConnectionType='1' and a.ReasonStart='1' and a.call_type='out' then '������������'
		when a.ConnectionType='6' then '����������' 
		when a.ConnectionType='0' then '��������� ��������' 
		when a.ConnectionType='5' and a.ReasonStart='1' then '�������� �� ��������� (���������)' 
		when a.ConnectionType='5' and a.ReasonStart='5' then '�������� �� ��������� (������� �� FLASH)' 
		when a.ConnectionType='5' and a.ReasonStart='2' then '�������� �� ��������� (����������� �� FLASH)' 
		when a.ConnectionType='1' then '���������' 
		end [��� ������],
		
		a.ReasonFailedName [������� ���������],
		case when a.Astr='' then a.from_number else a.Astr end [������� �],
		case when a.Bstr='' then a.to_number else a.Bstr end [������� �],
		
		datediff(ss,a.TimeStart, a.TimeStop) [����� ������������ (���)],
		
		case when a.ConnectionType='4' 
			then datediff(ss,a.TimeStart,isnull(a.OriginateDate,isnull(a.QueueDate, a.TimeStop))) else NULL end [IVR (���)], --'�������� IVR' 
		
		case when a.ConnectionType='4' 
			then datediff(ss,isnull(a.QueueDate, a.OriginateDate),a.TimeStop) else NULL end as [�������+��� (���)],
		
		case when (a.ConnectionType='5' and a.ReasonStart='1') or a.ConnectionType='6' or a.ConnectionType='1' then 
			datediff(ss,a.TimeStart,isnull(a.OriginateDate,isnull(a.QueueDate, a.TimeStop)))-datediff(ss,a.TimeAnswer,a.TimeStop) else NULL end as [��� (���)],
		
		case when a.ConnectionType='6' or a.ConnectionType='1' or a.ConnectionType='5' then datediff(ss,a.TimeAnswer,a.TimeStop) else NULL end as [�������� (���)],
		
		case when a.ConnectionType='6' or a.ConnectionType='1' or a.ConnectionType='5' then 
			(case when datediff(ss,a.TimeAnswer,a.TimeStop) > 5 then ceiling(datediff(ss,a.TimeAnswer,a.TimeStop)/60+1) else 0 end) else NULL end as [�������� (���+)],
		
		a.StopSide [������� �����],
		
		a.IsRecorded,
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
				from_number,to_number,
				substring(convert(varchar(25),timestart,121),1,4) YYYY,
				substring(convert(varchar(25),timestart,121),6,2) MM,
				substring(convert(varchar(25),timestart,121),9,2) DD,
				substring(convert(varchar(25),timestart,121),12,2) HH24,
				substring(convert(varchar(25),timestart,121),15,2) MI,
				substring(convert(varchar(25),timestart,121),18,2) SS,
				substring(convert(varchar(25),timestart,121),21,3) mSS,
				case
				when a.ConnectionType=0 then '������� ������' --��������
				when a.ConnectionType=1 then '������� ������'
				when a.ConnectionType=2 then '������� � IVR'
				when a.ConnectionType=3 then '������� ������'
				when a.ConnectionType=4 then '������� � IVR'
				when a.ConnectionType=5 then '������� ������'
				when a.ConnectionType=6 then '������� ������'
				when a.ConnectionType=7 then '� IVR ������'
				when a.ConnectionType=8 then '� IVR ������'
				end call_direction		
		
		from (
		
		SELECT  s.TimeStart,o.OriginateDate,q.QueueDate,s.TimeAnswer,s.TimeStop, 
		s.IdChain, s.id, r.route_id, rt.��������_�������� route_name, rt.����_������ in_task_key,r.call_type, 
		ctp.name connection_type,
		s.ConnectionType,
		s.ReasonStart,
		s.ReasonStop, 
		case when s.StopSide=0 then '�' when s.StopSide=1 then '�' else NULL end StopSide,
		'' ReasonFailed,
		'' ReasonFailedName,
		s.AOutNumber from_number,s.Astr,s.AlineID,
		s.BOutNumber to_number, s.Bstr,s.BlineID,
		r.bnumber,
		r.direction_code,
		s.IsRecorded,
		s.ALineNum,
		s.BLineNum
				
		FROM oktell.dbo.A_Stat_Connections_1x1 s with (nolock) 
		left join oktell.dbo.SVA_Stat_InboundRoutes r with (nolock) on r.idChain=s.IdChain
		left join oktell.dbo.SVA_Inbound_Routes rt with (nolock) on rt.id=r.route_id
		left join oktell.dbo.SVA_List_ConnectionTypes ctp with (nolock) on ctp.code=s.ConnectionType
		left join oktell.dbo.SVA_Stat_QueueDate q with(nolock) on q.idConnection=s.Id
		left join oktell.dbo.SVA_Stat_OriginateDate o with(nolock) on o.idConnection=s.Id
		
		where s.idChain='".$idchain."' 
		
		union all
		
		--���������
		SELECT f.TimeStart,o.OriginateDate,NULL QueueDate,NULL TimeAnswer,f.TimeStop, 
		f.IdChain, f.id, r.route_id, rt.��������_�������� route_name, rt.����_������ in_task_key, r.call_type,
		'��������' connection_type,
		'0' ConnectionType,
		f.ReasonStart,
		'' ReasonStop,
		'' StopSide,
		f.ReasonFailed,
		rf.name ReasonFailedName,
		f.AOutNumber from_number, f.AStr,f.AlineID,
		f.ANumberdialed to_number,f.BStr,f.BlineID,
		r.bnumber,
		r.direction_code,
		0 IsRecorded,
		f.ALineNum,
		f.BLineNum
		
		FROM 
		oktell.dbo.A_Stat_FailedCalls f with (nolock) 
		left join oktell.dbo.SVA_Stat_InboundRoutes r with (nolock) on r.idChain=f.IdChain
		left join oktell.dbo.SVA_Inbound_Routes rt with (nolock) on rt.id=r.route_id 
		left join oktell.dbo.SVA_Stat_OriginateDate o with(nolock) on o.idConnection=f.Id
		left join oktell.dbo.SVA_List_ReasonFailed rf with (nolock) on rf.code=f.ReasonFailed
		where
		f.idChain='".$idchain."' 
		) a
		order by a.TimeStart";
		//echo "<textarea>$sql</textarea>";
		
		$q=$c_okt->prepare($sql);
		
		$q->execute();
		
		
		
		
		$partnum=0; while($row=$q -> fetch()) {$partnum++;	
	
			if($partnum==1) {
				echo "<font size=3 color=blue><b>".$row['DD'].".".$row['MM'].".".$row['YYYY']." ".$row['HH24'].":".$row['MI'].":".$row['SS'].".".$row['mSS'].
				". ".($row['���� ������']<>''?$row['���� ������']:$row['�������� ��������'])."</b></font><br>";
				echo "<table cellpadding=3 border=0>";
				echo "<tr>
				<th>�����</th>
				<th>�����������</th>
				<th>��� ������</th>
				<th>������� �</th>
				<th>������� �</th>
				<th>������� �����</th>
				<th>���. ����. (���)</th>
				<th>IVR (���)</th>
				<th>����.+��� (���)</th>
				<th>��� (���)</th>
				<th>����. (���)</th>
				<th>���������</th>	
				</tr>";
			}
				
			if(!isset($datecall) and $partnum==1) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
			
			$file_path=$oktell_records_path.$row['file_path'];
			$file_name=$row['file_name'];
			$new_file_name=$row['file_name'];	
			if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/) 
					$src=$oktell_records_url.'?idconnection='.$row['IdConnection'].(isset($datecall)?"&datecall=".$datecall:"")."&partnum=".$partnum;
				
				
			echo "<tr>";
			echo "<th>".$row['HH24'].":".$row['MI'].":".$row['SS']."</th>";
			echo "<td>".$row['call_direction']."</td>";
			echo "<td>";
			//if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/)
			//	echo "<a href='".$src."'>";
			echo $row['��� ������'];
			//if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/)
			//	echo " (������� ������)</a>";
			echo "</td>";
			echo "<td>".$row['������� �']."</td>";
			echo "<td>".$row['������� �']."</td>";
			echo "<td align=center>".$row['������� �����']."</td>";
			echo "<td align=center>".$row['����� ������������ (���)']."</td>";
			echo "<td align=center>".$row['IVR (���)']."</td>";
			echo "<td align=center>".$row['�������+��� (���)']."</td>";
			echo "<td align=center>".$row['��� (���)']."</td>";
			echo "<td align=center>".$row['�������� (���)']."</td>";
			echo "<td align=center>".$row['������� ���������']."</td>";
			echo "</tr>";
				
			if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/) {
				echo "<tr>";
				echo "<td colspan=11>";
				//echo "<a href='".$src."'>".$partnum.". ".$row['��� ������']." (������� ������)</a>";
				echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";			
				echo "</td>";
				echo "</tr>";
			}
		}	
		if($partnum>0) echo "</table>";
		else echo "<font size=3 color=blue><b>�� �������</b></font><br>";
	}
	elseif(isset($acc) and $acc==strtoupper(substr(md5(strtolower($idchain).'-full'),0,8))) { //������ �� ���� ���� �� �����������
		//������ ������ ������� �� ������� ����������
		//������ - ����������� �� 1-001 ����������� ��������
		$sql="select 
		convert(varchar, a.timeStart,120) [���� ������],
		a.id IdConnection,
		a.idChain [ID �������],
		a.TimeStart,a.TimeAnswer,a.TimeStop,
		case when a.ConnectionType='4' then '�������� IVR' 
		when a.ConnectionType='6' then '����������' 
		when a.ConnectionType='0' then '��������� ��������' 
		when a.ConnectionType='5' and a.ReasonStart='1' then '�������� �� ��������� (���������)' 
		when a.ConnectionType='5' and a.ReasonStart='5' then '�������� �� ��������� (������� �� FLASH)' 
		when a.ConnectionType='5' and a.ReasonStart='2' then '�������� �� ��������� (����������� �� FLASH)' 
		when a.ConnectionType='1' then '���������' 
		end [��� ������],
		
		a.ReasonFailedName [������� ���������],
		case when a.Astr='' then a.from_number else a.Astr end [������� �],
		case when a.Bstr='' then a.to_number else a.Bstr end [������� �],
		
		datediff(ss,a.TimeStart, a.TimeStop) [����� ������������ (���)],
		
		case when a.ConnectionType='6' or a.ConnectionType='1' or a.ConnectionType='5' then datediff(ss,a.TimeAnswer,a.TimeStop) else NULL end as [�������� (���)],
		
		case when a.ConnectionType='6' or a.ConnectionType='1' or a.ConnectionType='5' then 
			(case when datediff(ss,a.TimeAnswer,a.TimeStop) > 5 then ceiling(datediff(ss,a.TimeAnswer,a.TimeStop)/60+1) else 0 end) else NULL end as [�������� (���+)],
		
		a.StopSide [������� �����],
		
		a.IsRecorded,
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
				from_number,to_number,
				substring(convert(varchar(25),timestart,121),1,4) YYYY,
				substring(convert(varchar(25),timestart,121),6,2) MM,
				substring(convert(varchar(25),timestart,121),9,2) DD,
				substring(convert(varchar(25),timestart,121),12,2) HH24,
				substring(convert(varchar(25),timestart,121),15,2) MI,
				substring(convert(varchar(25),timestart,121),18,2) SS,
				substring(convert(varchar(25),timestart,121),21,3) mSS,
				case
				when a.ConnectionType=0 then '������� ������' --��������
				when a.ConnectionType=1 then '������� ������'
				when a.ConnectionType=2 then '������� � IVR'
				when a.ConnectionType=3 then '������� ������'
				when a.ConnectionType=4 then '������� � IVR'
				when a.ConnectionType=5 then '������� ������'
				when a.ConnectionType=6 then '������� ������'
				when a.ConnectionType=7 then '� IVR ������'
				when a.ConnectionType=8 then '� IVR ������'
				end call_direction		
		
		from (
		
		SELECT  s.TimeStart,s.TimeAnswer,s.TimeStop, 
		s.IdChain, s.id,
		ctp.name connection_type,
		s.ConnectionType,
		s.ReasonStart,
		s.ReasonStop, 
		case when s.StopSide=0 then '�' when s.StopSide=1 then '�' else NULL end StopSide,
		'' ReasonFailed,
		'' ReasonFailedName,
		s.AOutNumber from_number,s.Astr,s.AlineID,
		s.BOutNumber to_number, s.Bstr,s.BlineID,
		s.IsRecorded,
		s.ALineNum,
		s.BLineNum
		
		FROM oktell.dbo.A_Stat_Connections_1x1 s with (nolock) 
		left join oktell.dbo.SVA_List_ConnectionTypes ctp with (nolock) on ctp.code=s.ConnectionType
		
		where s.idChain='".$idchain."' 
		
		union all
		
		--���������
		SELECT f.TimeStart,NULL TimeAnswer,f.TimeStop, 
		f.IdChain, f.id,
		'��������' connection_type,
		'0' ConnectionType,
		f.ReasonStart,
		'' ReasonStop,
		'' StopSide,
		f.ReasonFailed,
		rf.name ReasonFailedName,
		f.AOutNumber from_number, f.AStr,f.AlineID,
		f.ANumberdialed to_number,f.BStr,f.BlineID,
		0 IsRecorded,
		f.ALineNum,
		f.BLineNum
		
		FROM 
		oktell.dbo.A_Stat_FailedCalls f with (nolock) 
		left join oktell.dbo.SVA_List_ReasonFailed rf with (nolock) on rf.code=f.ReasonFailed
		where
		f.idChain='".$idchain."' 
		) a
		order by a.TimeStart";
		//echo "<textarea>$sql</textarea>";
		
		$q=$c_okt->prepare($sql);
		
		$q->execute();
		$partnum=0; while($row=$q -> fetch()) {$partnum++;		
			if($partnum==1) {
				echo "<font size=3 color=blue><b>".$row['DD'].".".$row['MM'].".".$row['YYYY']." ".$row['HH24'].":".$row['MI'].":".$row['SS'].".".$row['mSS'].
				". </b></font><br>";
				echo "<table cellpadding=3 border=0>";
				echo "<tr>
				<th>�����</th>
				<th>�����������</th>
				<th>��� ������</th>
				<th>������� �</th>
				<th>������� �</th>
				<th>������� �����</th>
				<th>���. ����. (���)</th>
				<th>����. (���)</th>
				<th>���������</th>			
				</tr>";
			}
				
			if(!isset($datecall) and $partnum==1) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
			
			$file_path=$oktell_records_path.$row['file_path'];
			$file_name=$row['file_name'];
			$new_file_name=$row['file_name'];	
			if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/) 
					$src=$oktell_records_url.'?idconnection='.$row['IdConnection'].(isset($datecall)?"&datecall=".$datecall:"")."&partnum=".$partnum;
				
				
			echo "<tr>";
			echo "<th>".$row['HH24'].":".$row['MI'].":".$row['SS']."</th>";
			echo "<td>".$row['call_direction']."</td>";
			echo "<td>";
			//if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/)
			//	echo "<a href='".$src."'>";
			echo $row['��� ������'];
			//if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/)
			//	echo " (������� ������)</a>";
			echo "</td>";
			echo "<td>".$row['������� �']."</td>";
			echo "<td>".$row['������� �']."</td>";
			echo "<td align=center>".$row['������� �����']."</td>";
			echo "<td align=center>".$row['����� ������������ (���)']."</td>";
			echo "<td align=center>".$row['�������� (���)']."</td>";
			echo "<td align=center>".$row['������� ���������']."</td>";
			echo "</tr>";
				
			if($row['IsRecorded']=='1' /*and file_exists($file_path.$file_name)*/) {
				echo "<tr>";
				echo "<td colspan=8>";
				//echo "<a href='".$src."'>".$partnum.". ".$row['��� ������']." (������� ������)</a>";
				echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";			
				echo "</td>";
				echo "</tr>";
			}
		}	
		if($partnum>0) echo "</table>";
		else echo "<font size=3 color=blue><b>�� �������</b></font><br>";
	}	
	else { //������ ������ � ������� ����������
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
		when t.ConnectionType=1 then '������� ������'
		when t.ConnectionType=2 then '������� � IVR'
		when t.ConnectionType=3 then '������� ������'
		when t.ConnectionType=4 then '������� � IVR'
		when t.ConnectionType=5 then '������� ������'
		when t.ConnectionType=6 then '������� ������'
		when t.ConnectionType=7 then '� IVR ������'
		when t.ConnectionType=8 then '� IVR ������'
		end call_direction,
		t.IsRecorded
		FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
		where IdChain=:idchain and IsRecorded=1
		order by TimeStart";
		//echo "<textarea>$sql</textarea>";


		$q=$c_okt->prepare($sql);
		$q->bindValue(':idchain',$idchain);
		
		$q->execute();
		
		$partnum=0; while($row=$q -> fetch()) {
			if($row['IsRecorded']=='1') {
				$partnum++;
				if(!isset($datecall) and $partnum==1) $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
			
				$file_path=$oktell_records_path.$row['file_path'];
				$file_name=$row['file_name'];
				$new_file_name=$row['file_name'];	
		
				if(file_exists($file_path.$file_name)) {
					$src=$oktell_records_url.'?idconnection='.$row['IdConnection'].(isset($datecall)?"&datecall=".$datecall:"")."&partnum=".$partnum;
					echo "<a href='".$src."'>".$partnum.". ".$row['call_direction']." (������� ������)</a>";
					echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";
			
				}
			}
		}				
	}
}

?>