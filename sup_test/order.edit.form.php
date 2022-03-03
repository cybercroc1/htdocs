<?php
extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
$sid=session_id();
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Заявка на техподдержку</title>
<script src='order.edit.js'></script>
</head>
<body>
<?php
//echo session_name()."--".session_id();

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

//отображение заявки
if($author=='y' or $executor=='y' or ($redirect=='y' and $opened=='y') or ($solution=='y' and $opened=='y') or $look=='y') {}
else {echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра этой заявки. </b></font>"; exit();}



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
	
	//исходные данные о заявке	
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
	//
	
	//тип проблемы
	echo "<div id=div_trbl_type>Тип проблемы: <b>".$trbl_name."</b></div>";
	
	//деталь проблемы
	echo "<div id=div_trbl_detail>".($trbl_det_name<>''?'точнее: '.$trbl_det_name:'')."</b></div>";
	
	//исходные данные о заявке
	echo "</b></td>
	</tr>
	<tr>
	<td bgcolor=white valign=top>Суть проблемы: <b>".nl2br(htmlentities($coment))."</b>";

	//файлы
	$q_files=OCIParse($c,"select id,filename,filetype from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id is null order by filename");
	OCIExecute($q_files);
	$f=0; while(OCIFetch($q_files)) { $f++;
		/*
		if($f==1) {
			echo "<hr>Файлы: ";
		}
		echo "<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
		*/
		
		if($f==1) {
			echo "<hr>Файлы: ";
		}
		$fileurl="files2.php?download&fileid=".OCIResult($q_files,"ID");
		echo "<a href='".$fileurl."' target='logFrame'>".OCIResult($q_files,"FILENAME")."</a>;<br>";

		if(substr(OCIResult($q_files,"FILETYPE"),0,5)=='audio') {
			echo '<audio controls preload=metadata style="width:100%"><source src="'.$fileurl.'" type="audio/ogg; codec=vorbis"><source src="'.$fileurl.'" type="'.OCIResult($q_files,"FILETYPE").'"></audio>';
		}
		if(substr(OCIResult($q_files,"FILETYPE"),0,5)=='image') {
			echo '<img width=100% src="'.$fileurl.'" type="'.OCIResult($q_files,"FILETYPE").'"></img>';
		}		

	}	

	echo "</td></tr></table><hr>";

	echo "<input type=hidden name=base_id value=".$base_id.">";
	echo "<input type=hidden name=sid value='".$sid."'>"; //для привязки прикрепленных файлов
	echo "<input type=hidden name=last_change value='".$last_change."'>"; //для проверки модификации заявки при сохранении
	//
	
	//история

	if($quality<>'') {
		echo "Оценка:";
		echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		echo "<tr><td bgcolor=white>Оценил: <b>$quality_who:</b> <font color='$q_color'><b>$quality</b></font><br>$quality_coment</td></tr>";
		echo "</table><hr>";
	}

	$q3=OCIParse($c,"select sth.id,to_char(sth.datetime,'DD.MM.YYYY HH24:MI') datetime, su.fio, sth.texnary_coment, sth.to_who, sth.quality,
	sa.id result_id, sa.name result_name, sa.color result_color
	from sup_texnari_history sth, sup_user su, sup_actions sa
	where sth.base_id='".$base_id."'
	and sa.id=sth.result_call
	and su.id(+)=sth.texnari_id
	order by sth.datetime, sth.id");

	//файлы
	$q_files=OCIParse($c,"select id,filename,filetype from SUP_FILES where base_id='".$base_id."' and tmp is null and hist_id=:hist_id order by filename");

	OCIExecute($q3,OCI_DEFAULT);
	$i=0;
	while (OCIFetch($q3)) {
		$i++; if($i==1) {
			echo "История:";
			echo "<table bgcolor=black cellspacing=1 cellpadding=3 width='98%'>";
		}
		echo "<tr><td bgcolor=white valign=top>";
		echo "<b>".OCIResult($q3,"DATETIME")." ".OCIResult($q3,"FIO")." <font color='".OCIResult($q3,"RESULT_COLOR")."'>".OCIResult($q3,"RESULT_NAME")."</font> ".OCIResult($q3,"TO_WHO")." ";
		if(OCIResult($q3,"RESULT_ID")==7 or OCIResult($q3,"RESULT_ID")==700) { //оценил
			if(OCIResult($q3,"QUALITY")=='1') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='2') echo ": <font color=red><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='3') echo ": <font color=#CC6633><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='4') echo ": <font color=#339966><b>".OCIResult($q3,"QUALITY")."</b></font>";
			if(OCIResult($q3,"QUALITY")=='5') echo ": <font color=green><b>".OCIResult($q3,"QUALITY")."</b></font>";
		}
		if(OCIResult($q3,"RESULT_ID")<>4) echo "</b><br>";

		echo nl2br(htmlentities(OCIResult($q3,"TEXNARY_COMENT")));

		//файлы
		$hist_id=OCIResult($q3,"ID");
		OCIBindByName($q_files,":hist_id",$hist_id);
		OCIExecute($q_files);
		$f=0; while(OCIFetch($q_files)) { $f++;
			if($f==1) {
				echo "<hr>Файлы: ";
			}
			$fileurl="files2.php?download&fileid=".OCIResult($q_files,"ID");
			echo "<a href='".$fileurl."' target='logFrame'>".OCIResult($q_files,"FILENAME")."</a>;<br>";

			if(substr(OCIResult($q_files,"FILETYPE"),0,5)=='audio') {
				echo '<audio controls preload=metadata style="width:100%"><source src="'.$fileurl.'" type="audio/ogg; codec=vorbis"><source src="'.$fileurl.'" type="'.OCIResult($q_files,"FILETYPE").'"></audio>';
			}
			if(substr(OCIResult($q_files,"FILETYPE"),0,5)=='image') {
				echo '<img width=100% src="'.$fileurl.'" type="'.OCIResult($q_files,"FILETYPE").'"></img>';
			}
		}			


		echo "</td></tr>";
	}
	if($i>0) echo "</table><hr>";
	//
	
	//комментировать
	echo "<div id=div_coment></div>";
	
	//файлы 
	echo "<div id=div_add_file style='display:none'></div>";
	echo "<div id=div_coment_hr></div>";
	
	
	echo "<div id=div_tmp_files>";
	//список временных файлов со ссылками
	/*$q_files=OCIParse($c,"select id,filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where base_id='".$base_id."' and tmp='y' and nvl(sess_id,0)=nvl('".$sid."',0)
	order by filename");
	OCIExecute($q_files);
	$i=0; while(OCIFetch($q_files)) { $i++;
		echo "<a href='files2.php?download&fileid=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a> <a href='files.php?del&fileid=".OCIResult($q_files,"ID")."&base_id=".$base_id."&sid=".$sid."' target='logFrame'><font color=red title='Удалить'>x</font></a>; ";
	}
	if($i>0) echo "<hr>";*/
	echo "</div>";

	//оценивать
	echo "<div id=div_eval></div>";
	
	//дубликат, ошибка
	echo "<div id=div_dubl></div>";

	//список технарей для переадресации
	echo "<div id=div_save></div>";
	
	//кнопки
	echo "<div id=div_buttons></div>";
	
	//
		

echo "</form>";
echo "<iframe style='display:none' name='logFrame' src='order.edit.func.php?base_id=".$base_id."&new_location_id=".$location_id."&new_trbl_type_id=".$trbl_id."&new_trbl_det_id=".$trbl_det_id."'></iframe>";
echo "<script>var base_id='".$base_id."';</script>";
?>
<script>
var isfocus='y';
if(window.opener && window.opener.click_row) window.opener.click_row('row_'+base_id); else null;
window.onbeforeunload=function(){
   if(window.opener && window.opener.unclick_row) window.opener.unclick_row('row_'+base_id); else null;
};
window.onfocus=function(){
	isfocus='y';
   //if(window.opener && window.opener.sel_click_row) window.opener.sel_click_row('row_'+base_id); else null;
};
window.onblur=function(){
	isfocus='n';
   //if(window.opener && window.opener.unsel_click_row) window.opener.unsel_click_row('row_'+base_id); else null;
};
setInterval("parent_color('row_'+base_id)",200);
//setInterval("if(window.opener && window.opener.click_row) window.opener.click_row('row_'+base_id); else null;",300);
function parent_color(row_id) {
	if(window.opener && window.opener.click_row) {
		if (isfocus=='y') window.opener.sel_click_row('row_'+base_id);
		else 			  window.opener.unsel_click_row('row_'+base_id);
	} 
	else null;
}
function fn_check() {
	with(tex_edit_frm) {
	
		if('save' in tex_edit_frm && 'to_user_id' in tex_edit_frm && to_user_id.type=='select-one') {
			
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='green') save.value='Принять в работу';
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='indigo') save.value='Комментировать';
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='maroon') save.value='Переадресовать';
			if(to_user_id.options[to_user_id.selectedIndex].style.color=='blue') save.value='Открыть заявку';
			
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
/*function add_file() {
	with(tex_edit_frm) {
		et=enctype;
		ac=action;
		enctype='multipart/form-data';
		action='files.php';
		submit();
		enctype=et;
		action=ac;	
	}
}*/
//РАБОТА С ФАЙЛАМИ
var dropArea = document.getElementById('div_add_file');

dropArea.addEventListener('dragenter', preventDefaults, false);
dropArea.addEventListener('dragover', preventDefaults, false);
dropArea.addEventListener('dragleave', preventDefaults, false);
dropArea.addEventListener('drop', preventDefaults, false);

dropArea.addEventListener('dragenter', highlight, false);
dropArea.addEventListener('dragover', highlight, false);

dropArea.addEventListener('dragleave', unhighlight, false);
dropArea.addEventListener('drop', unhighlight, false);

function preventDefaults (e) {
  e.preventDefault();
  e.stopPropagation();
}
function highlight(e) {
  dropArea.classList.add('highlight');
}
function unhighlight(e) {
  dropArea.classList.remove('highlight');
}
dropArea.addEventListener('drop', handleDrop, false);
function handleDrop(e) {
  var dt = e.dataTransfer;
  var files = dt.files;
  handleFiles(files);
}
function handleFiles(files) {
  for (var i = 0; i < files.length; i++) {
	uploadFile(files[i]);
  }  
}
</script>
</body>
</html>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_order_edit_delay.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
