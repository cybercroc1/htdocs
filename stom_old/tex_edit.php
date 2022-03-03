<?php
extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Заявка на техподдержку</title>
</head>
<body>
<?php
//echo session_name()."--".session_id();


if ($_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'')) {
}
else {
echo "<font color=red><b>У Вас нет прав для просмотра данной страницы или Вы не прошли авторизацию</b></font>";
exit();
}

include("../../sup_conf/sup_conn_string");

if(isset($save) or isset($close)) {
	//Отзвон клиенту по проблеме (4)
	if(isset($callback_who) and isset($callback_fio) and $callback_who<>'' and trim($callback_fio)<>'' and ($_SESSION['solution']=='y' or $_SESSION['redirect']=='y')) {

		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$callback_who."',:callback_fio,sysdate,'4')");
		OCIBindByName($ins,":callback_fio",$callback_fio);
		OCIExecute($ins,OCI_DEFAULT);
	
		$upd=OCIParse($c,"update sup_base set 
		callback_date=decode(callback_date,null,sysdate,callback_date), 
		callback_who=decode(callback_date,null,'$callback_who',callback_who), 
		callback_fio=decode(callback_date,null,'$callback_fio',callback_fio),
		last_change=sysdate 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	} 
	//
	//Оценка (7)
	if(isset($quality) and $quality<>'' and $tex_comment<>'' and  $_SESSION['eval']=='y') {
		
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,quality,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'".$quality."',sysdate,'7')");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIExecute($ins,OCI_DEFAULT);
		
		$upd=OCIParse($c,"update sup_base set quality='".$quality."', quality_coment=:coment, quality_who=:fio, last_change=sysdate where id='".$base_id."'");
		OCIBindByName($upd,":coment",$tex_comment);
		OCIBindByName($upd,":fio",$_SESSION['fio']);
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//
	if(isset($save)) {
	//Перевел (5) 
		if(isset($to_user_id) and  $to_user_id<>'' and $to_user_id<>$_SESSION['user_id'] and $_SESSION['redirect']=='y') {
			$q=OCIParse($c,"select fio from sup_user where id='".$to_user_id."'");
			OCIExecute($q,OCI_DEFAULT);
			OCIFetch($q);
			$to_user_fio=OCIResult($q,"FIO");
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,:to_user_fio,sysdate,'5')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIBindByName($ins,":to_user_fio",$to_user_fio);
			OCIExecute($ins,OCI_DEFAULT);

			$upd=OCIParse($c,"update sup_base set texnari_id='".$to_user_id."', last_change=sysdate where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			OCICommit($c);
			
		}
	//
	//Комментарий/открытие (6)
		//если to_user_id есть, но пустой, то технаря не назначаем, в истории пишем комментарий от имени сессионного пользоваетля
		//если to_user_id не существует, то назначем пользователя сессии
		
		//присвоил
		else if($_SESSION['solution']=='y' and (!isset($quality) or $quality=='') and $tex_comment<>'') {
			if($from_user_id=='' and (!isset($to_user_id) or $to_user_id==$_SESSION['user_id'])) {
				$user_id=$_SESSION['user_id'];
				$result='8';
				echo "8";
			}
			else {
				$user_id='';
				$result='6';
				echo "6";
			}
			$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,datetime,result_call)
			values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,sysdate,'".$result."')");
			OCIBindByName($ins,":coment",$tex_comment);
			OCIExecute($ins,OCI_DEFAULT);
			
			$upd=OCIParse($c,"update sup_base set 
			texnari_id=nvl(texnari_id,'".$user_id."'), date_close=null, who_close=null, last_change=sysdate 
			where id='".$base_id."'");
			OCIExecute($upd,OCI_DEFAULT);
			
			OCICommit($c);
		} 	
	//
	}
	//Закрыл (3)
	if(isset($close)) {
		if(!isset($to_user_id) or $to_user_id=='') {
			$user_id=$_SESSION['user_id'];
		}
		else {
			$user_id=$to_user_id;
		}
		if($user_id<>$_SESSION['user_id']) {
			$q=OCIParse($c,"select fio from sup_user where id='".$user_id."'");
			OCIExecute($q,OCI_DEFAULT);
			OCIFetch($q);
			$to_user_fio=OCIResult($q,"FIO");
		}
		else $to_user_fio='';
		
		$ins=OCIParse($c,"insert into sup_texnari_history (id,base_id,texnari_id,texnary_coment,to_who,datetime,result_call)
		values (sup_history_id.nextval, '".$base_id."','".$_SESSION['user_id']."',:coment,'".$to_user_fio."',sysdate,'3')");
		OCIBindByName($ins,":coment",$tex_comment);
		OCIExecute($ins,OCI_DEFAULT);
		
		$upd=OCIParse($c,"update sup_base set 
		texnari_id='".$user_id."', date_close=sysdate, who_close='".$_SESSION['user_id']."', last_change=sysdate 
		where id='".$base_id."'");
		OCIExecute($upd,OCI_DEFAULT);

		OCICommit($c);
	}	
	//

//отправляем отчет о закрытии заявки
if(isset($close)) {
$q=OCIParse($c,"select fio from SUP_USER where id='".$_SESSION['user_id']."'");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);
$mess="
Номер заявки:<b> ".$base_id." </b><br>
Закрыл:<b> ".OCIResult($q,"FIO")."</b>";

send('bulka','it-itil@yandex.ru', 'stomsupport@wilstream.ru', 'Закрыта заявка №'.$base_id,$mess);
}
//

echo "
<script>
//window.opener.location.reload();
//self.close();
</script>
";

exit();

}
//
//функция отправки через сокет

function send($server, $to, $from, $title,$mess) {
	$headers="MIME-Version: 1.0 \r\n";
	$headers.="Content-Type: text/html; charset=\"windows-1251\"\r\n";
	$headers="To: ".$to."\r\nFrom: ".$from."\r\nSubject: ".$title."\r\n".$headers; 
	$fp = fsockopen($server, 25,$errno,$errstr,30); 
	if (!$fp) die("Server $server. Connection failed: $errno, $errstr"); 
		fputs($fp,"HELO bill\r\n"); 
		fputs($fp,"MAIL FROM: ".$from."\r\n"); 
		fputs($fp,"RCPT TO: ".$to."\r\n"); 
		fputs($fp,"DATA\r\n"); 
		fputs($fp,$headers."\r\n".$mess."\r\n"."."."\r\n");  
		fputs($fp,"QUIT\r\n"); 
		while(!feof($fp)) {    
		echo fgets($fp,1024);
		echo "<br>";    
		}
		fclose($fp);
		echo "<hr>";    
}
//

if (isset($base_id)) {
	if(!isset($callback_fio)) $callback_fio='';
	$q=OCIParse($c,"select b.id,
	       to_char(b.date_in_call, 'DD.MM.YYYY HH24:MI:SS') date_in_call,
	       b.cdpn,
	       b.klinika_id,
		   b.texnari_id,
	       k.name,
		   k.phone,
	       b.kto,
	       b.oper_comment,
	       b.u_kogo,
		   b.quality,
		   b.quality_coment,
		   b.quality_who,
		   b.trbl_grp_id,
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
	
	echo "<form name=tex_edit_frm>";
	echo "<input type=hidden name=from_user_id value='".$texnari_id."'>";

	echo "<font size=4>".OCIResult($q,"NAME")."(".OCIResult($q,"PHONE").") <nobr> - ".OCIResult($q,"DATE_IN_CALL")."</nobr></font>";
	
	echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>
	<tr><td bgcolor=white valign=top>
	№ заявки: <b>".$base_id."</b><hr>
	АОН: <b>".OCIResult($q,"CDPN")."</b><hr>
	Кто звонил:<br><b>".OCIResult($q,"KTO")."</b><hr>
	У кого не работает:<br><b>".OCIResult($q,"U_KOGO")."</b><br>
	</td>
	<td bgcolor=white valign=top>Тип проблемы: <br>";

	$q2=OCIParse($c,"select a.id trbl_id,a.name,decode(b.trbl_type_id,null,null,'checked ') checked from 
	(select distinct stt.id, stt.name from sup_lt slt, sup_trbl_type stt
	where slt.location_id='".OCIResult($q,"KLINIKA_ID")."' and slt.lt_grp_id=decode('".$_SESSION['lt_grp_id']."','0',slt.lt_grp_id,'".$_SESSION['lt_grp_id']."') 
	and	stt.trbl_grp_id=decode('".OCIResult($q,"TRBL_GRP_ID")."','0',stt.trbl_grp_id,'".OCIResult($q,"TRBL_GRP_ID")."')
	and stt.id=slt.trbl_id) a,
	(select sta.trbl_type_id from sup_trbl_alloc sta
	where sta.base_id='".$base_id."') b
	where a.id=b.trbl_type_id(+)
	order by a.name");
	OCIExecute($q2,OCI_DEFAULT);
	$i=0;
	while(OCIFetch($q2)) {
		$i++;
		echo "<nobr>";
		echo "<input type=checkbox ";
		if($_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y') echo "disabled ";
		echo OCIResult($q2,"CHECKED")."name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."'>";
		if(OCIResult($q2,"CHECKED")<>NULL) echo "<b>".OCIResult($q2,"NAME")."</b>";
		else echo OCIResult($q2,"NAME");
		echo "</nobr><br>";
		$trbl_ids[$i]=OCIResult($q2,"TRBL_ID");
	}

	$q2=OCIParse($c,"
	select stt.id trbl_id, stt.name from sup_trbl_alloc sta, sup_trbl_type stt
	where sta.base_id='".$base_id."' and sta.trbl_type_id not in (".implode(',',$trbl_ids).")
	and stt.id=sta.trbl_type_id
	order by stt.name");
	OCIExecute($q2,OCI_DEFAULT);
	$i=0;
	while(OCIFetch($q2)) {
		if($i==0) echo "<hr>";
		$i++;
		echo "<input type=checkbox checked disabled><b>".OCIResult($q2,"NAME")."</b>
		<input type='hidden' name='trbl_id[]' value='".OCIResult($q2,"TRBL_ID")."'><br>";
	}


	echo "</b></td>
	</tr>
	<tr>
	<td bgcolor=white valign=top colspan=2>Суть проблемы:<br><b>".OCIResult($q,"OPER_COMMENT")."</b></td>
	</tr>
	</table>";

	echo "<input type=hidden name=base_id value=".OCIResult($q,"ID").">";
	echo "<input type=hidden name=klinika_id value=".OCIResult($q,"KLINIKA_ID").">";
	$quality=OCIResult($q,"QUALITY");
	$quality_who=OCIResult($q,"QUALITY_WHO");
	$quality_coment=OCIResult($q,"QUALITY_COMENT");
	$trbl_grp_id=OCIResult($q,"TRBL_GRP_ID");
	$location_id=OCIResult($q,"KLINIKA_ID");
	$q_color=OCIResult($q,"Q_COLOR");

	if($quality<>'') {
		echo "Оценка:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>Оценил: <b>$quality_who:</b> <font color='$q_color'><b>$quality</b></font><br>$quality_coment</td></tr>";
		echo "</table>";
	}

	$q=OCIParse($c,"select distinct su.id,su.fio from sup_trbl_type stt, sup_lt slt, sup_user su
	where stt.trbl_grp_id='".$trbl_grp_id."' and slt.location_id='".$location_id."'
	and slt.trbl_id=stt.id and slt.lt_grp_id=decode(su.lt_grp_id,'0',slt.lt_grp_id,su.lt_grp_id)
	and su.solution='y' and su.deleted is null
	order by su.fio");
	OCIExecute($q,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	if($i==0) {$texnari_ids[1]=$_SESSION['user_id']; $texnari_names[1]=$_SESSION['fio'];}


	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y') {
		echo "Отзвон клиенту по проблеме:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white><b>Кто отзвонился:</b> ";
		echo "<select name=callback_who onchange=fn_check()>";
		echo "<option></option>";
		if($_SESSION['look']=='y') {
			foreach ($texnari_ids as $key => $val) {
				echo "<option value='".$val."'";
				//if($val==$_SESSION['user_id']) echo " selected";
				echo ">".$texnari_names[$key]."</option>";	
			}
		
		}
		else {
			echo "<option value='".$_SESSION['user_id']."'>".$_SESSION['fio']."</option>";	
		}
		echo "</select></td></tr>";
		echo "<tr><td bgcolor=white><b>Кому отзвонился</b>: <input style='width:98%' type=text name=callback_fio value='".$callback_fio."' onkeyup=fn_check()></td>";
		echo "</table>";
	}


	$q3=OCIParse($c,"select to_char(sth.datetime,'DD.MM.YYYY HH24:MI:SS') datetime, su.fio, sth.texnary_coment, sth.to_who,
	decode(sth.result_call,1,'передал по телефону',2,'не дозвонился',3,'закрыл',4,'отзвонился',5,'переадресовал',6,'комментарий',7,'оценил',8,'присвоил',null) result, 
	decode(sth.result_call,1,'green',2,'blue',3,'red',4,'blue',5,'magenta',6,'purple',7,'black',8,'green',null) color, sth.quality 
	from sup_texnari_history sth, sup_user su
	where sth.base_id='".$base_id."'
	and su.id(+)=sth.texnari_id
	order by sth.datetime");
	OCIExecute($q3,OCI_DEFAULT);
	
	$i=0;
	while (OCIFetch($q3)) {
		$i++; if($i==1) {
			echo "История:";
			echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		}
		echo "<tr><td bgcolor=white valign=top>";
		echo "<b>".OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"COLOR")."'>".OCIResult($q3,"RESULT")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT")=='оценил') echo ": <b>".OCIResult($q3,"QUALITY")."</b>";
		if(OCIResult($q3,"RESULT")<>'отзвонился') echo "</b><br>";

		echo OCIResult($q3,"TEXNARY_COMENT");
		echo "</td></tr>";
	}
	if($i>0) echo "</table>";

	echo "Комментарий:<br><textarea onkeyup=fn_check() style='width:98%' rows=5 name=tex_comment></textarea><hr>";


	if($_SESSION['eval']=='y') {
		echo "<font size=3><b>Оценка: </b></font><select name=quality onchange=fn_check()><option></option>
		<option style='color:red' value='1'>1</option>
		<option style='color:red' value='2'>2</option>
		<option style='color:#CC6633' value='3'>3</option>
		<option style='color:#339966' value='4'>4</option>
		<option style='color:green' value='5'>5</option>
		</select><hr>";
	}
	if($_SESSION['redirect']=='y') {
		echo "Передать заявку: ";

		echo "<select name=to_user_id onchange=fn_check()>";
		if($_SESSION['solution']=='y') $def_usr=$_SESSION['user_id']; else $def_usr=$texnari_id;
		echo "<option value=''></option>";
		//if($def_usr=='') echo "<option></option>";
		foreach ($texnari_ids as $key => $val) {
			echo "<option value='".$val."'";
			//if($val==$def_usr) echo " selected";
			echo ">".$texnari_names[$key]."</option>";	
		}
		echo "</select>";
	echo "<hr>";
	}
	
	if($_SESSION['solution']=='y') {
		echo "<input type=submit disabled name=close style='background-color:#FF6600' value='Закрыть заявку'>";
	}
	if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y') {
		echo "<input type=submit disabled name=save style='background-color:#66FF66' value='Сохранить'>";
	}
	echo "</form>";
}

?>
<script>
function fn_check() {
	with(tex_edit_frm) {
	
	if (callback_fio.value=='' & callback_who.value=='') ena=1;
	else if (callback_fio.value!='' & callback_who.value!='') ena=2;
	else ena=0;
	
	if((ena==2)||(tex_comment.value!=''&ena!=0)) {save.disabled=false;} else {save.disabled=true;}
	
	if ((tex_comment.value=='')||ena==0) {close.disabled=true;}
	else {close.disabled=false;}
	}
}
</script>
</body>
</html>
