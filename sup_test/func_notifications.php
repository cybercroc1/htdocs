<?php
function create_notice($base_id,$result,$tex_coment) {
	global $c;
	global $mail_mess;
	global $sms_mess;
	//информация о заявке
	$q=OCIParse($c,"select b.id,
	       to_char(b.date_in_call, 'DD.MM.YYYY HH24:MI') date_in_call,
	       b.cdpn,
	       b.klinika_id,
		   b.texnari_id,
	       k.name,
		   k.phone,
	       b.kto,
		   b.kto_id,
	       b.oper_comment,
	       b.u_kogo,
		   b.quality,
		   b.quality_coment,
		   b.quality_who,
		   b.ip_address,
		   b.ready_to_close,
		   b.date_close,
		   b.trbl_type_id,
		   b.trbl_detail_id,
		   tt.name trbl_type_name,
           td.name trbl_detail_name,
		   case  
			 when b.quality='1' then 'red'
			 when b.quality='2' then 'red'
			 when b.quality='3' then '#CC6633'
			 when b.quality='4' then '#339966'
			 when b.quality='5' then 'green'
	       end q_color,
		   b.dublikat,
		   b.krivie_ruki	   
	  from sup_base b, sup_klinika k, sup_trbl_type tt, sup_trbl_detail td
	 where b.klinika_id=k.id (+)
     and tt.id(+)=b.trbl_type_id
     and td.id(+)=b.trbl_detail_id	 
	 and b.id = '".$base_id."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$date_in_call=OCIResult($q,"DATE_IN_CALL");
	$trbl_type_name=OCIResult($q,"TRBL_TYPE_NAME");
	$trbl_det_name=OCIResult($q,"TRBL_DETAIL_NAME");				
	

	$mail_mess.="<font size=4>".OCIResult($q,"NAME").(OCIResult($q,"PHONE")<>''?' ('.OCIResult($q,"PHONE").')':'')."</nobr></font>";
	
	$mail_mess.="<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'>
	<tr><td bgcolor=white valign=top>
	<nobr>№ заявки: <b>".$base_id." </b></nobr><nobr> дата: <b>".$date_in_call." </b></nobr>";
	if(OCIResult($q,"CDPN")<>'') $mail_mess.="<nobr> АОН: <b>".OCIResult($q,"CDPN")." </b></nobr>";
	$mail_mess.="<nobr> IP: <b>".OCIResult($q,"IP_ADDRESS")." </b></nobr><hr>
	Кто обратился: <nobr><b>".OCIResult($q,"KTO")." </b></nobr>";
	if(OCIResult($q,"KTO_ID")<>'') {
		$q_tmp=OCIParse($c,"select decode(type,'mob','8'||phone,phone) phone from SUP_TEXNARI_PHONES t where texnari_id='".OCIResult($q,"KTO_ID")."' and contact='y' and valid_date is not null order by ord");
		OCIExecute($q_tmp,OCI_DEFAULT);

		$i=0; while (OCIFetch($q_tmp)) {$i++;
			$phones[$i]=OCIResult($q_tmp,"PHONE");
		}
		if(isset($phones)) {
			$mail_mess.="<nobr>";
			$mail_mess.=implode(', ',$phones);
			$mail_mess.="; </nobr>";
		}
		$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".OCIResult($q,"KTO_ID")."' and valid_date is not null");
		OCIExecute($q_tmp,OCI_DEFAULT);
		//$mailtos=array();
		$i=0; while(OCIFetch($q_tmp)) {$i++;
			$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
		}
		if(isset($mailtos)) {
			$mail_mess.="<nobr>";
			$mailtos=implode(', ',$mailtos);
			$mail_mess.="<a href='mailto:".$mailtos."?subject=Заявка №".$base_id." - ответ'>".$mailtos."</a>";
			$mail_mess.="; </nobr>";
		}
	}
	$mail_mess.="<hr>
	У кого не работает: <b>".OCIResult($q,"U_KOGO")."</b><hr>
	Тип проблемы: <b>".OCIResult($q,"TRBL_TYPE_NAME")."</b><hr>
	Суть проблемы: <b>".nl2br(OCIResult($q,"OPER_COMMENT"))."</b>";
	
	//файлы
	$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id is null order by filename");
	OCIExecute($q_files);
	$i=0; while(OCIFetch($q_files)) { $i++;
		if($i==1) {
			$mail_mess.="<hr>Файлы: ";
		}
		$mail_mess.="<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
	}	

	//история
	$q3=OCIParse($c,"select sth.id,to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who, sth.quality,
	sa.id result_id, sa.name result_name, sa.color result_color
	from sup_texnari_history sth, sup_user su, sup_actions sa
	where sth.base_id='".$base_id."'
	and sa.id=sth.result_call
	and su.id(+)=sth.texnari_id
	order by sth.datetime, sth.id");	
	

	//файлы
	$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id=:hist_id order by filename");
	
	OCIExecute($q3,OCI_DEFAULT);
	
	$i=0;
	while (OCIFetch($q3)) {
		$mail_mess.="<hr>";
		$mail_mess.=OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"RESULT_COLOR")."'>".OCIResult($q3,"RESULT_NAME")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT_ID")==7 or OCIResult($q3,"RESULT_ID")==700) { //оценил	
			
			if(OCIResult($q3,"QUALITY")=='1') $mail_mess.=": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') $mail_mess.=": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') $mail_mess.=": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') $mail_mess.=": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') $mail_mess.=": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT_ID")<>4) $mail_mess.="<br>";
		
		$mail_mess.="<b>".nl2br(OCIResult($q3,"TEXNARY_COMENT"))."</b>";

		//файлы
		$hist_id=OCIResult($q3,"ID");
		OCIBindByName($q_files,":hist_id",$hist_id);
		OCIExecute($q_files);
		$f=0; while(OCIFetch($q_files)) { $f++;
			if($f==1) {
				$mail_mess.="<hr>Файлы: ";
			}
			$mail_mess.="<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
		}	

	}
	//
	$mail_mess.="</tr></table>";
	$mail_mess.="<br>ссылка на заявку: <a href='http://sup.wilstream.ru?ticketId=".$base_id."' target=_balnk>sup.wilstream.ru</a>";
}
?>