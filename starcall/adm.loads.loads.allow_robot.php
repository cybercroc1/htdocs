<?php 
include("starcall/session.cfg.php");
set_time_limit(0); 
//ignore_user_abort(true);
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script>
function close_fr() {
	parent.document.all.popUpFrame.src='';
	parent.document.all.popUpFrame.style.display='none';
	parent.document.body.style.overflow = ''; //снятие запрета прокрутки документа
	//разблокируем все элементы родительского окна
	with(parent.frm) {
		for(i=0; i<elements.length; i++) {
			elements[i].disabled='';
		}
	}
	parent.location.reload();
}
function allow_robot() {
	frm.allow.disabled=true;
	frm.close_frame.disabled=true;
	frm.submit();
}

//перемещение фрейма
var pressed='n'; var x; var y;
function body_MD() {
	pressed='y';
	x=event.clientX;
	y=event.clientY;
}
function move_frame () {
	if(pressed=='y') {
		//with(parent.document.all.popUpFrame) {
		with(parent.document.all.popUpFrame) {	
			style.left=(parseInt(style.left)+event.clientX-x)+'px';	
			style.top=(parseInt(style.top)+event.clientY-y)+'px';	
		}
	}
}
function body_MU() {
	pressed='n';
}
//
</script>
<body class="body_marign" style="cursor:move" onmousemove=move_frame() onmousedown=body_MD() onmouseup=body_MU()>
<?php
extract($_POST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

$commit_interval=3000; //Количество строк в одной транзакции

include("starcall/conn_string.cfg.php");

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

echo "<form method=post name=frm>";
echo "<font size=4>Одобрение номеров</font><hr>";
if(isset($mark)) {
	foreach($mark as $val) {
		echo "<input type=hidden name=mark[] value=".$val.">";
	} 
}
else {
	echo "<font color=red>Ничего не выбрано</font><hr>";
	echo "<input type='button' name=close_frame value='Закрыть окно' onclick=close_fr()>";
	exit();
}
echo "<font color=black>Вставьте сюда номера из робота:</font>";
echo "<textarea name=phone_list cols=45 rows=8>";
$count_base=0;
$count_phones=0;
if(isset($phone_list) and isset($mark)) {
	$load_ids=implode(',',$mark);
	
	$q_upd_base=OCIParse($c,"update STC_BASE b set b.allow='y' where b.id in 
(select p.base_id from STC_PHONES p
where p.load_hist_id in (".$load_ids.") and p.project_id='".$_SESSION['adm']['project']['id']."' and p.phone=:phone)
and b.allow is null");
	
	$q_upd_phone=OCIParse($c,"update STC_PHONES set allow='y'
where load_hist_id in (".$load_ids.") and project_id='".$_SESSION['adm']['project']['id']."' and phone=:phone and allow is null");

	//обновление статистики
	$q_upd_stat=OCIParse($c,"update STC_LOAD_HISTORY h 
set 
h.allow_rows=(select count(*) from STC_BASE where project_id=h.project_id and load_hist_id=h.id and allow='y'),
h.allow_phones=(select count(*) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id and allow='y')
where h.project_id='".$_SESSION['adm']['project']['id']."' and h.id in (".$load_ids.")");
	
	$phones_arr=explode("\n",$phone_list);
	foreach($phones_arr as $phones) {
		
		$phones=str_replace(";",",",$phones);
		$phone=explode(",",$phones);
		foreach($phone as $val) {
			
			$val=trim($val);
			if($val=='') continue;
			
			OCIBindByName($q_upd_base,":phone",$val);
			OCIExecute($q_upd_base,OCI_DEFAULT);
			$count_base+=oci_num_rows($q_upd_base);
				
			OCIBindByName($q_upd_phone,":phone",$val);
			OCIExecute($q_upd_phone,OCI_DEFAULT);
			$count_phones+=oci_num_rows($q_upd_phone);
			if($count_base>0 and round($count_base/$commit_interval)==$count_base/$commit_interval) { //коммит
				OCIExecute($q_upd_stat,OCI_DEFAULT); //обновление статистики
				OCICommit($c);
	}	}	}
	if($count_base>0) {
		//обновляем статистику загрузки
		OCIExecute($q_upd_stat,OCI_DEFAULT);
		OCICommit($c);
		echo "Обновлена статистика загрузки".chr(13);
		//обновляем статистику квот
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
		echo "Обновлена статистика и блокировка квот".chr(13);
}	}
echo "</textarea><hr>";
if(isset($phone_list)) {
	echo "Одобрено записей: <b>$count_base</b>; телефонов: <b>$count_phones</b><br>";
}
echo "<input type='button' name=allow value='Одобрить' onclick=allow_robot()> ";
echo " <input type='button' name=close_frame value='Закрыть окно' onclick=close_fr()>";

?>

</form>
</body>
</html>
