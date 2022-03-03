<?php
extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
$sid=session_id();
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

$tomorrow=date('d.m.Y',mktime(0,0,0,date("m"),date("d")+1,date("Y")));

if (!isset($_SESSION['auth'])) {
	echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра данной страницы. Вы не прошли авторизацию</b></font>";
	exit();
}
if (!isset($base_id) or $base_id=='') {exit();}

include("sup/sup_conn_string");

//информация о заявке
include("order.get.order.info.php");
extract(get_order_info($c,$base_id));
if(isset($error)) {echo $error; exit();}




	echo "<form name=tex_edit_frm method=post action=order.edit.save.php target=logFrame>";
	echo "<input type=hidden name=document_location value='http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."'>";

	echo "<font size=4>Заявка № ".$base_id.". ".$location_name.($location_phone<>''?' ('.$location_phone.')':'');
	echo ($dublikat?"<font color=red> (дубликат) </font>":"");
	echo ($krivie_ruki?"<font color=red> (ошибка пользователя) </font>":"");
	echo "</font>";
	
	echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>
	<tr><td bgcolor=white valign=top>
	<nobr>№ заявки: <b>".$base_id."</b></nobr><nobr> дата: <b>".$date_in_call."</b></nobr> ";
	if($aon<>'') echo "<nobr>АОН: <b>".$aon."</b></nobr> ";
	echo "<nobr>IP: <b>".$ip_addr."</b></nobr><hr>";
	
	//местоположение
	echo "<div id=div_location_id>Местоположение: <b>".$location_name."</b></div>
	<hr>";
	
		
	echo "Кто обратился: <nobr><b>".$author_name."</b>;</nobr> ";
	if($author_id<>'') {
		$q_tmp=OCIParse($c,"select phone from SUP_TEXNARI_PHONES t where texnari_id='".$author_id."' and contact='y' and valid_date is not null order by ord");
		OCIExecute($q_tmp,OCI_DEFAULT);
		$i=0; while (OCIFetch($q_tmp)) {$i++;
			$phones[$i]=OCIResult($q_tmp,"PHONE");
		}
		if(isset($phones)) {
			echo "<nobr>";
			$phones=implode(', ',$phones);
			echo $phones;
			echo ";</nobr> ";
		}

		$q_tmp=OCIParse($c,"select email from SUP_TEXNARI_emails where texnari_id = '".$author_id."' and valid_date is not null");
		OCIExecute($q_tmp,OCI_DEFAULT);
		//$mailtos=array();
		$i=0; while(OCIFetch($q_tmp)) {$i++;
			$mailtos[$i]=OCIResult($q_tmp,"EMAIL");
		}
		if(isset($mailtos)) {
			echo "<nobr>";
			$mailtos=implode(', ',$mailtos);
			echo "<a href='mailto:".$mailtos."?subject=Заявка №".$base_id." - ответ'>".$mailtos."</a>";
			echo ";</nobr> ";
		}
	}
	echo "<hr>
	У кого не работает: <b>".$u_kogo."</b><hr>";
	
	//тип проблемы
	echo "<div id=div_trbl_type>Тип проблемы: <b>".$trbl_name."</b></div>";
	
	//деталь проблемы
	echo "<div id=div_trbl_detail>".($trbl_det_name<>''?'точнее: '.$trbl_det_name:'')."</b></div>";

	echo "</b></td>
	</tr>
	<tr>
	<td bgcolor=white valign=top>Суть проблемы: <b>".nl2br(htmlentities($coment))."</b>";

	//файлы
	$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id is null order by filename");
	OCIExecute($q_files);
	$f=0; while(OCIFetch($q_files)) { $f++;
		if($f==1) {
			echo "<hr>Файлы: ";
		}
		echo "<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
	}	

	echo "</td></tr></table>";

	echo "<input type=hidden name=base_id value=".$base_id.">";
	echo "<input type=hidden name=sid value='".$sid."'>";
	echo "<input type=hidden name=last_change value='".$last_change."'>";	

	if($quality<>'') {
		echo "Оценка:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>Оценил: <b>$quality_who:</b> <font color='$q_color'><b>$quality</b></font><br>$quality_coment</td></tr>";
		echo "</table>";
	}

	//история
	$q3=OCIParse($c,"select sth.id,to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who,
	decode(sth.result_call,0,'переадресовал на группу',1,'передал по телефону',2,'не дозвонился',3,'закрыл',4,'отзвонился ',5,'переадресовал',6,'комментарий',7,'оценил',8,'присвоил',9,'готово к проверке',10, 'статус \"дубликат\"', 11, 'статус \"ошибка пользоваетля\"',12,'сменил местоположение',13,'сменил тип проблемы',14,'отложил на ',null) result, 
	decode(sth.result_call,0,'maroon',1,'green',2,'blue',3,'red',4,'blue',5,'maroon',6,'indigo',7,'black',8,'green',9,'#006400',10,'red',11,'red',12,'black',13,'black',14,'#CC6633',null) color, sth.quality 
	from sup_texnari_history sth, sup_user su
	where sth.base_id='".$base_id."'
	and su.id(+)=sth.texnari_id
	order by sth.datetime, sth.id");

	//файлы
	$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id=:hist_id order by filename");

	OCIExecute($q3,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q3)) {
		$i++; if($i==1) {
			echo "История:";
			echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		}
		echo "<tr><td bgcolor=white valign=top>";
		echo "<b>".OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"COLOR")."'>".OCIResult($q3,"RESULT")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT")=='оценил') {
			if(OCIResult($q3,"QUALITY")=='1') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') echo ": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') echo ": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') echo ": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT")<>'отзвонился ') echo "</b><br>";

		echo nl2br(htmlentities(OCIResult($q3,"TEXNARY_COMENT")));

		//файлы
		$hist_id=OCIResult($q3,"ID");
		OCIBindByName($q_files,":hist_id",$hist_id);
		OCIExecute($q_files);
		$f=0; while(OCIFetch($q_files)) { $f++;
			if($f==1) {
				echo "<hr>Файлы: ";
			}
			echo "<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
		}			


		echo "</td></tr>";
	}
	if($i>0) echo "</table>";
	//

	//комментировать
	if($solution=='y' or $redirect=='y' or $eval=='y' or ($create_new=='y' and $author_id==$_SESSION['user_id'])) {
		echo "<hr>Комментарий: ";

		echo "<br><textarea onkeyup=fn_check() style='width:98%' rows=5 name=tex_comment></textarea>";

		echo "<hr>";
		
		//прикреплять файлы
		if($solution=='y' or $redirect=='y' or ($create_new=='y' and $author_id==$_SESSION['user_id'])){	
			echo "Прикрепить файлы: <input type=file multiple name=new_file[] onchange=add_file()><input type=submit name=upload_file style='display:none'>";
			echo "<div id=div_files></div>";
			echo "<hr>";
		}

	}

	//оценивать
	if($eval=='y') {
		echo "<font size=3><b>Оценка: </b></font><select name=new_quality onchange=fn_check()><option></option>
		<option style='color:red' value='1'>1</option>
		<option style='color:red' value='2'>2</option>
		<option style='color:#CC6633' value='3'>3</option>
		<option style='color:#339966' value='4'>4</option>
		<option style='color:green' value='5'>5</option>
		</select><hr>";
	}

	if($solution=='y') {
		echo "<input type=hidden name='new_dublikat'><input type='checkbox' name='new_dublikat' value='y'"; echo ($dublikat?" checked":""); echo "><font color=red>Дубликат</font></input>
		 | <input type=hidden name='new_krivie_ruki'><input type='checkbox' name='new_krivie_ruki' value='y'"; echo ($krivie_ruki?" checked":""); echo"><font color=red>Ошибка пользоваетля</font></input><hr>";
	}

	//список технарей для переадресации
	echo "<div id=div_to_user_id></div>";
	//
	echo "<hr>";
	
	if($solution=='y' and $date_close=='') {
		if($deny_close=='y' and $ready_to_close=='') {echo "<input type=submit name=close_z style='background-color:#458B00' value='Готово к проверке'> | ";}
		else if($deny_close<>'y' and $date_close=='') {echo "<input type=submit name=close_z style='background-color:#FF5050' value='Закрыть заявку'> | ";}
	}

	if(($solution=='y' or $redirect=='y' or ($create_new=='y' and $author_id==$_SESSION['user_id'])) and $date_close=='') {
		
		echo "<nobr><input type=text value='".$tomorrow."' size=8 name=delay_to_date style='background-color:#CC6633' onclick='if(self.gfPop)gfPop.fPopCalendar(this);return false; HIDEFOCUS' onchange=ok.click()>";
		echo "<input type=submit name=delay_z style='background-color:#CC6633' value='Отложить'> | </nobr>";
	}

	echo "<input type=submit style='display:none' name=upload_file>";	
	echo "</form>";

echo "<iframe style='display:none' name='logFrame' src='order.edit.func.php?base_id=".$base_id."&new_location_id=".$location_id."&new_trbl_type_id=".$trbl_id."&new_trbl_det_id=".$trbl_det_id."'></iframe>";
echo "<script>
var base_id='".$base_id."';
</script>";
?>
<script>
function fn_check() {
	with(tex_edit_frm) {
	
		if('save' in tex_edit_frm && 'to_user_id' in tex_edit_frm) {
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='green') save.value='Принять в работу';
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='indigo') save.value='Комментировать';
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='maroon') save.value='Переадресовать';
			
			save.style.background=to_user_id.options[to_user_id.selectedIndex].style.color;
			
		}
	
		//if ('delay_z' in tex_edit_frm) if(tex_comment.value!='') delay_z.disabled=false; else delay_z.disabled=true;
	
		if ('new_trbl_type_id' in tex_edit_frm && new_trbl_type_id.value=='') {
			if('close_z' in tex_edit_frm) close_z.disabled=true;
			if('save' in tex_edit_frm) save.disabled=true;	
			if('delay_z' in tex_edit_frm) delay_z.disabled=true;		
			alert('Внимание! Не выбран тип проблемы!');
		}
	/*	else if(
			('quality' in tex_edit_frm && quality.value!='') //оценка
		)
		{
			if('save' in tex_edit_frm) save.disabled=false;
			if('delay_z' in tex_edit_frm) delay_z.disabled=true;
			if('close_z' in tex_edit_frm) close_z.disabled=true;					
		}
		else if('close_z' in tex_edit_frm) {
			if('to_user_id' in tex_edit_frm && (to_user_id.value=='group' || to_user_id.value=='coment')) {
				close_z.disabled=true; 
				if('delay_z' in tex_edit_frm) delay_z.disabled=true;

			}
			else close_z.disabled=false;
			
		}
		else {
			if('close_z' in tex_edit_frm) close_z.disabled=false;
			if('delay_z' in tex_edit_frm) delay_z.disabled=false;
			if('save' in tex_edit_frm) save.disabled=false;
		}
	*/	
	}	
}
function ch_loc_trbl() {
	var new_location_id=tex_edit_frm.new_location_id.value;
	var new_trbl_type_id=tex_edit_frm.new_trbl_type_id.value;
	if('new_trbl_det_id' in tex_edit_frm) var new_trbl_det_id=tex_edit_frm.new_trbl_det_id.value; else var new_trbl_det_id='';
	logFrame.location='order.edit.func.php?base_id='+base_id+'&new_location_id='+new_location_id+'&new_trbl_type_id='+new_trbl_type_id+'&new_trbl_det_id='+new_trbl_det_id;
}
function add_file() {
	with(tex_edit_frm) {
		et=enctype;
		ac=action;
		enctype='multipart/form-data';
		action='files.php';
		submit();
		enctype=et;
		action=ac;	
	}
}
</script>
</body>
</html>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_order_edit_delay.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
