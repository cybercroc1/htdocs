<?php 
require_once "auth.php";
$_SESSION['last_url']='files.php';
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
<link href="css/main.css" rel="stylesheet" type="text/css">
<?php if(strtolower($_SERVER['HTTP_HOST'])=='cclight.wilstream.ru' or strtolower($_SERVER['HTTP_HOST'])=='cclight2.wilstream.ru') { ?>
	<link href="css/cclight.css" rel="stylesheet" type="text/css">
<?php } ?>
<link rel="stylesheet" href="css/jquery.datetimepicker.css">
</head>
<body class=rep-form>
<div id="dropZone">
<div id="dropZone_l1">
Загрузить файл
</div>
</div>
<script src='js/jquery-3.5.1.min.js'></script>
<script src='js/report.js'></script>
<script>
var a;
function sort(sort_type) {
	sort_obj=document.getElementById('sort_'+sort_type);
	current_sort=sort_obj.innerText;
	
	document.getElementById('sort_basename').innerText='';
	document.getElementById('sort_filemtime').innerText='';
	document.getElementById('sort_filesize').innerText='';
	
	if(current_sort=='') sort_direction='▲';
	else if(current_sort=='▲') sort_direction='▼'; 
	else sort_direction='';
	
	sort_obj.innerText=sort_direction;
	

	//gosort(sort_type,sort_direction);
	clearTimeout(a);
	a=setTimeout(gosort,2000,sort_type,sort_direction);
		
}
function gosort(sort_type,sort_direction) {
	document.location='?sort_type='+sort_type+'&sort_direction='+sort_direction;
}
</script>
<?php

if ($_SESSION['project']['id']==0) exit(); 
if ($_SESSION['project']['view_sms_log']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

echo "<form id=frm method=post action=>";
echo "<div class=rep_head>";
echo "<nobr><font size=4>файлы - \"".$_SESSION['project']['name']."\"</font></nobr><br>";
echo "</div>";

?>
<div id=informer></div>
<?php

?>
<script>
var dropZone = document.getElementById('dropZone');
var informer = document.getElementById('informer');

function dropZoneResize() {
	dropZone.style.width=(document.body.clientWidth)+'px';
	dropZone.style.height=(document.body.clientHeight)+'px';
}

window.onresize=dropZoneResize;
dropZoneResize();

if (typeof(window.FileReader) == 'undefined') {
    informer.text('Не поддерживается браузером!');
    informer.classList.add('error');
}  
document.body.ondragover = function() {
    //alert(dropZone);
	dropZone.classList.add('hover');
    return false;
};
    
dropZone.ondragleave = function() {
    dropZone.classList.remove('hover');
    return false;
};
dropZone.ondrop = function(event) {
    event.preventDefault();
    dropZone.classList.remove('hover');
    //dropZone.classList.add('drop');
//}; 
  
var file = event.dataTransfer.files[0];
 
if (file.size > 10000000) {
    dropZone.innerText='Файл слишком большой!';
    dropZone.classList.add('error');
    return false;
}

var data=new FormData();
data.append('file',file);

var xhr = new XMLHttpRequest();
xhr.upload.addEventListener('progress', uploadProgress, false);
xhr.onreadystatechange = stateChange;
xhr.open('POST', 'parse.php');
//xhr.setRequestHeader('X-FILE-NAME', file.name);
xhr.send(data); 

function uploadProgress(event) {
    var percent = parseInt(event.loaded / event.total * 100);
    informer.innerText='Загрузка: ' + percent + '%';
}
function stateChange(event) {
    if (event.target.readyState == 4) {
        if (event.target.status == 200) {
			dropZone.classList.remove('hover');
            dropZone.innerText='Загрузка успешно завершена!';
			//alert(event.target.responseText);
			document.getElementById('answer').innerHTML=document.getElementById.innerHTML+event.target.responseText;
        } else {
			informer.innerText='Не поддерживается браузером!';
			informer.classList.add('error');
        }
    }
}
}; 

</script>
<?php
echo "<div id=rep_div class=rep_div></div>";
echo "</form>";

include("sc/sc_path.php");

if ($_SESSION['project']['id']==0) $path_to_exch_files=$path_to_exch_folders;
else $path_to_exch_files=$path_to_exch_folders.$_SESSION['project']['name'];

if(!isset($_SESSION['files_sort'])) $_SESSION['files_sort']=array('sort_type'=>'basename','sort_direction'=>'▲'); 
if(isset($_GET['sort_type'])) {$_SESSION['files_sort']['sort_type']=$_GET['sort_type']; $_SESSION['files_sort']['sort_direction']=$_GET['sort_direction'];}

echo "<table class='report_table'>
<tr>
<th><a onclick=sort('basename') style='cursor:pointer' title='Отсортировать'>Имя</a> <font id=sort_basename>".($_SESSION['files_sort']['sort_type']=='basename'?$_SESSION['files_sort']['sort_direction']:'')."</font></th>
<th><a onclick=sort('filesize') style='cursor:pointer' title='Отсортировать'>Размер</a> <font id=sort_filesize>".($_SESSION['files_sort']['sort_type']=='filesize'?$_SESSION['files_sort']['sort_direction']:'')."</font></th>
<th><a onclick=sort('filemtime') style='cursor:pointer' title='Отсортировать'>Дата<br>изменения</a> <font id=sort_filemtime>".($_SESSION['files_sort']['sort_type']=='filemtime'?$_SESSION['files_sort']['sort_direction']:'')."</font></th>
<th></th>";
echo "</tr>";

if(!file_exists(iconv('utf-8','windows-1251',$path_to_exch_files))) {mkdir(iconv('utf-8','windows-1251',$path_to_exch_files));}
	
$files=glob(iconv('utf-8','windows-1251',$path_to_exch_files."\\*.*"),GLOB_NOSORT);

if($_SESSION['files_sort']['sort_direction']=='▲') array_multisort(array_map($_SESSION['files_sort']['sort_type'], $files), SORT_NUMERIC, SORT_ASC, $files);
else if($_SESSION['files_sort']['sort_direction']=='▼') array_multisort(array_map($_SESSION['files_sort']['sort_type'], $files), SORT_NUMERIC, SORT_DESC, $files);

$i=0;
foreach ($files as $filename) { 
	if(filetype($filename)=='file') { $i++; //только файлы
		echo "<tr>
		<td style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'><b>".basename($filename)."</b></td>
		<td style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".filesize($filename)."</td>
		<td style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>".date("d.m.Y H:i:s",filemtime($filename))."</td>
		<td style='cursor:pointer' onmouseover='sel_cell(this)' onmouseout='unsel_cell(this)' onclick='click_unclick_row_alone(this.parentNode)'>
		<a href=\"javascript:if(confirm('Действительно хотите УДАЛИТЬ ФАЙЛ ?')){files_frm.del_file.value='".basename($filename)."';files_frm.submit();}\"><img src=img/del.gif title=\"Удалить файл\" border=0></a>
		</td>
		</tr>";
	}
}
if($i==0) echo "<tr>
		<td align=center colspan=4><b>Файлы отсутвуют</b></td>
		</tr>";
	echo "</table></from>";


echo '</body>
</html>';
?>