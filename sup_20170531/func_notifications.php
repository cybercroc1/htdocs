<?php
function create_notice($base_id,$result,$tex_coment) {
	global $c;
	global $mail_mess;
	global $sms_mess;
	if(!isset($callback_fio)) $callback_fio='';
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
		   b.trbl_grp_id,
		   b.ip_address,
		   b.ready_to_close,
		   b.date_close,
		   case  
			 when b.quality='1' then 'red'
			 when b.quality='2' then 'red'
			 when b.quality='3' then '#CC6633'
			 when b.quality='4' then '#339966'
			 when b.quality='5' then 'green'
	       end q_color	   
	  from sup_base b, sup_klinika k
	 where b.klinika_id=k.id (+)
	 and b.id = '".$base_id."'
	");
	
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$texnari_id=OCIResult($q,"TEXNARI_ID");
	$kto_id=OCIResult($q,"KTO_ID");
	$date_close=OCIResult($q,"DATE_CLOSE");
	$ready_to_close=OCIResult($q,"READY_TO_CLOSE");
	$date_in_call=OCIResult($q,"DATE_IN_CALL");	
	

	$mail_mess.="<font size=4>".OCIResult($q,"NAME").(OCIResult($q,"PHONE")<>''?' ('.OCIResult($q,"PHONE").')':'')."</nobr></font>";
	
	$mail_mess.="<table bgcolor=black cellspacing=1 cellpadding=3 style='max-width:550px'>
	<tr><td bgcolor=white valign=top>
	<nobr>№ заявки: <b>".$base_id."</b></nobr><nobr> дата: <b>".$date_in_call."</b></nobr><hr>";
	if(OCIResult($q,"CDPN")<>'') $mail_mess.="АОН: <b>".OCIResult($q,"CDPN")."</b><br>";
	$mail_mess.="IP: <b>".OCIResult($q,"IP_ADDRESS")."</b><hr>
	Кто обратился:<br><b>".OCIResult($q,"KTO")."</b><br>";
	if(OCIResult($q,"KTO_ID")<>'') {
		$q_tmp=OCIParse($c,"select phone from SUP_TEXNARI_PHONES t where texnari_id='".OCIResult($q,"KTO_ID")."' and contact='y' order by ord");
		OCIExecute($q_tmp,OCI_DEFAULT);
		while (OCIFetch($q_tmp)) {
			$mail_mess.= OCIResult($q_tmp,"PHONE")."<br>";
		}
		$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".OCIResult($q,"KTO_ID")."'");
		OCIExecute($q_tmp,OCI_DEFAULT);
		//$mailtos=array();
		$i=0; while(OCIFetch($q_tmp)) {$i++;
			$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
		}
		if(isset($mailtos)) {
			$mailtos=implode(',',$mailtos);
			$mail_mess.="<a href='mailto:".$mailtos."?subject=Заявка №".$base_id." - ответ'>".$mailtos."</a><br>";
		}
	}
	$mail_mess.="<hr>
	У кого не работает:<br><b>".OCIResult($q,"U_KOGO")."</b><br>
	</td>
	<td bgcolor=white valign=top>Тип проблемы: <br>";

	$q2=OCIParse($c,"
	select stt.name from sup_trbl_type stt, sup_trbl_alloc sta, sup_base b
where sta.base_id='".$base_id."'
and b.id=sta.base_id and stt.id=sta.trbl_type_id and stt.trbl_grp_id=nvl(b.trbl_grp_id,stt.trbl_grp_id)
order by stt.name");
	
	OCIExecute($q2,OCI_DEFAULT);
	$i=0;
	while(OCIFetch($q2)) {
		$i++;
		$mail_mess.="<b>".OCIResult($q2,"NAME")."</b>";
	}
	$mail_mess.="</td>
	</tr>
	<tr>
	<td bgcolor=white valign=top colspan=2>Суть проблемы:<br><b>".nl2br(OCIResult($q,"OPER_COMMENT"))."</b></td>
	</tr>";

	//история
	$q3=OCIParse($c,"select to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who,
	decode(sth.result_call,1,'передал по телефону',2,'не дозвонился',3,'закрыл',4,'отзвонился ',5,'переадресовал на',6,'комментарий',7,'оценил',8,'присвоил',9,'готово к проверке',null) result, 
	decode(sth.result_call,1,'green',2,'blue',3,'red',4,'blue',5,'maroon',6,'indigo',7,'black',8,'green',9,'#006400',null) color, sth.quality 
	from sup_texnari_history sth, sup_user su
	where sth.base_id='".$base_id."'
	and su.id(+)=sth.texnari_id
	order by sth.datetime");
	OCIExecute($q3,OCI_DEFAULT);
	
	$i=0;
	while (OCIFetch($q3)) {
		$mail_mess.="<tr><td bgcolor=white valign=top colspan=2>";
		$mail_mess.=OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"COLOR")."'>".OCIResult($q3,"RESULT")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT")=='оценил') {
			if(OCIResult($q3,"QUALITY")=='1') $mail_mess.=": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') $mail_mess.=": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') $mail_mess.=": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') $mail_mess.=": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') $mail_mess.=": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT")<>'отзвонился ') $mail_mess.="<br>";

		$mail_mess.="<b>".nl2br(OCIResult($q3,"TEXNARY_COMENT"))."</b>";
		$mail_mess.="</td></tr>";
	}
	//
	$mail_mess.="</table>";
	$mail_mess.="<a href='http://sup.wilstream.ru/sup/tex.php?ticketId=".$base_id."' target=_balnk>http://sup.wilstream.ru/sup/tex.php</a>";
}
?>