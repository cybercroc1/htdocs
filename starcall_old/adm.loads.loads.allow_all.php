<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_error_handler ("my_error_handler");
set_time_limit(0);
//ignore_user_abort(true);
extract($_POST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

//$commit_interval=100000; //Количество строк в одной транзакции

if(!isset($mark)) {
	echo "<font color=red>Ничего не выбрано</font>";
	//разблокируем все элементы формы
	echo "<script>
		parent.admBottomFrame.document.getElementById('status_div').innerHTML='';
		with(parent.admBottomFrame.frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=false;
			}
		}
	</script>";
	exit();
}

include("../../conf/starcall_conf/conn_string.cfg.php");
echo "<form method=post name=frm>";
echo "Одобрение записей<hr>";

$count_base=0;
$count_phones=0;

if(isset($mark)) {
	$load_ids=implode(',',$mark);
	
	$q_upd_base=OCIParse($c,"update STC_BASE b set b.allow='y' where b.project_id=".$_SESSION['adm']['project']['id']." and b.load_hist_id in (".$load_ids.") and b.allow is null"); 
	
	$q_upd_phone=OCIParse($c,"update STC_PHONES p set allow='y' where p.load_hist_id in (".$load_ids.") and p.project_id='".$_SESSION['adm']['project']['id']."' and p.allow is null");

	//обновление статистики
	$q_upd_stat=OCIParse($c,"update STC_LOAD_HISTORY h 
set 
h.allow_rows=(select count(*) from STC_BASE where project_id=h.project_id and load_hist_id=h.id and allow='y'),
h.allow_phones=(select count(*) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id and allow='y')
where h.project_id='".$_SESSION['adm']['project']['id']."' and h.id in (".$load_ids.")");
	
	OCIExecute($q_upd_base,OCI_DEFAULT);
	$count_base=oci_num_rows($q_upd_base);

	OCIExecute($q_upd_phone,OCI_DEFAULT);
	$count_phones=oci_num_rows($q_upd_phone);
	
	echo "Одобрено записей: <b>$count_base</b>; телефонов: <b>$count_phones</b><br>";
	
	if($count_base>0 or $count_phones>0) { //коммит
		OCIExecute($q_upd_stat,OCI_DEFAULT); //обновление статистики
		echo "Обновлена статистика загрузки<br>";
		OCICommit($c);
	}
	
	if($count_base>0) {
		//обновляем статистику квот
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
		echo "Обновлена статистика и блокировка квот<br>";
	}
}

echo "<script>parent.admBottomFrame.document.getElementById('status_div').innerHTML=''</script>";
echo "<script>parent.admBottomFrame.location=parent.admBottomFrame.location.href;</script>";
//разблокируем все элементы формы
echo "<script>
	with(parent.admBottomFrame.frm) {
		for(i=0; i<elements.length; i++) {
			elements[i].disabled=false;
		}
	}
</script>";

function my_error_handler($code, $msg, $file, $line) {
	echo "<font color=red><hr>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	echo "<script>parent.admBottomFrame.document.getElementById('status_div').innerHTML='<font color=red>ОШИБКА: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';
	</script>";
	exit();
}
?>

