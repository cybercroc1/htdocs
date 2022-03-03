<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_error_handler ("my_error_handler");
set_time_limit(0);

extract($_REQUEST);

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


include("../../conf/starcall_conf/conn_string.cfg.php");

$load_ids=implode(',',$mark);

$commit_interval=30000; //Количество строк в одной транзакции
$del_count=0; 

$del=OCIParse($c,"delete from STC_BASE
where load_hist_id=:load_id and project_id='".$_SESSION['adm']['project']['id']."' and status is null and rownum<='".$commit_interval."'");

foreach($mark as $val) {
	OCIBindByName($del,":load_id",$val);
	$del_count_tmp=0;
	while(OCIExecute($del,OCI_DEFAULT)) {
		
		echo "ID загрузки: ".$val." удалено записей: ".oci_num_rows($del)."<br>";
		$del_count+=oci_num_rows($del);
		$del_count_tmp+=oci_num_rows($del);
		if(oci_num_rows($del)<$commit_interval) break;
		OCICommit($c);
	}	
	
	if($del_count_tmp>0) {
		//обновляем статистику загрузок
		$upd=OCIParse($c,"update STC_LOAD_HISTORY h 
		set 
		(h.load_rows,h.allow_rows)=(select count(*),count(allow) from STC_BASE where project_id=h.project_id and load_hist_id=h.id),
		(h.load_phones,h.allow_phones)=(select count(*),count(allow) from STC_PHONES where project_id=h.project_id and load_hist_id=h.id)
		where h.id in ($load_ids) and project_id='".$_SESSION['adm']['project']['id']."'");
		OCIExecute($upd); 
		echo "Обновлена статистика загрузок<br>";
		OCICommit($c);
	}		
	
	//проверяем кол-во оставшихся записей и если их 0, то удаляем загрузку из истории
	$q=OCIParse($c,"select count(*) cnt from STC_BASE t where t.load_hist_id='".$val."' and t.project_id='".$_SESSION['adm']['project']['id']."'");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(OCIResult($q,"CNT")==0) {
		//удаляем запись из истории загрузок
		$del_hist=OCIParse($c,"delete from STC_LOAD_HISTORY where id='".$val."'");
		OCIExecute($del_hist,OCI_DEFAULT);
		OCICommit($c);
	}
}
echo "<hr>ИТОГО удалено: ".$del_count."<hr>";
if($del_count>0) {
	//обновляем статистику квот
	OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$_SESSION['adm']['project']['id']."); end;"));
	echo "Обновлена статистика и блокировка индексов и квот<br>";
//
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