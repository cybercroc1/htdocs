<?php 
include("starcall/session.cfg.php");
set_error_handler ("my_error_handler");
set_time_limit(0);
//ignore_user_abort(true);
extract($_POST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

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

include("starcall/conn_string.cfg.php");

//ставим дату последней активности проекту
OCIExecute(OCIParse($c,"update STC_PROJECTS set last_activity=sysdate where id=".$_SESSION['adm']['project']['id']));

echo "<form method=post name=frm>";
echo "Блокировка записей<hr>";

$count_base=0;
$count_phones=0;

if(isset($mark)) {
	//получаем старый статус прокта
	$q=OCIParse($c,"select p.status from STC_PROJECTS p where p.id=".$_SESSION['adm']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_project_status=OCIResult($q,"STATUS");

	//ставим дату последней активности проекту и приостанавливаем проект
	OCIExecute(OCIParse($c,"update STC_PROJECTS p set p.last_activity=sysdate, p.status='Приостановлен' where p.id=".$_SESSION['adm']['project']['id']));
	OCICommit($c);
	echo "Проект приостановлен<br>";

	$common_changed_records=0;
	$common_changed_phones=0;
	foreach($mark as $load_id) {
		$changed_records=0;
		$changed_phones=0;
		$upd=OCIParse($c,"begin STC_DIS_ALLOW_ALL(".$_SESSION['adm']['project']['id'].",".$load_id.",'disallow',:changed_records,:changed_phones); end;");
		OCIBindByName($upd,":changed_records",$changed_records,16);
		OCIBindByName($upd,":changed_phones",$changed_phones,16);
		OCIExecute($upd,OCI_DEFAULT);
		
		echo "ID загрузки: ".$load_id." заблокировано записей: ".$changed_records."; телефонов: ".$changed_phones."<br>";
		$common_changed_records+=$changed_records;	
		$common_changed_phones+=$changed_phones;		
	}
	echo "ИТОГО: заблокировано записей: ".$common_changed_records."; телефонов: ".$common_changed_phones."<br>";
	if($common_changed_records>0) {
		$load_ids=implode(',',$mark);	
		//обновление статистики загрузок
		$q_upd_stat=OCIParse($c,"update STC_LOAD_HISTORY h 
		set 
		h.allow_rows=(select count(*) from STC_BASE where project_id=h.project_id and load_hist_id=h.id and allow='y'),
		h.allow_phones=(select count(*) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id and allow='y')
		where h.project_id='".$_SESSION['adm']['project']['id']."' and h.id in (".$load_ids.")");
		OCIExecute($q_upd_stat,OCI_DEFAULT); //обновление статистики
		echo "Обновлена статистика загрузок<br>";
		OCICommit($c);		

		//обновляем статистику квот
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
		echo "Обновлена статистика и блокировка квот<br>";
	}
	//возвращаем проекту начальный статус
	OCIExecute(OCIParse($c," update STC_PROJECTS p set p.status='".$old_project_status."',p.status_date=sysdate where p.id=".$_SESSION['adm']['project']['id']));
	OCICommit($c);
	echo "Проекту возвращен начальный статус ($old_project_status)<br>";
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

