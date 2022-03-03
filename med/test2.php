<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru-ru" lang="ru-ru">
<head>
    <link rel="stylesheet" type="text/css" href="./billing.css">
</head>
<?php
	require_once "med/conn_string.cfg.php";
    require_once "med_player.php"; // остался только сам плеер
	require_once "med/func_get_okt_connections_info.php";
	DEFINE('PATH', '');
	$base_id='438444'; //емейл
	$base_id='446514'; //емейл
	//$base_id='435391'; //телефон

	//Инфа о входящем звонке
	$q=OCIParse($c,"select t.OKTELL_IDCHAIN,t.OKTELL_SERVER_ID from CALL_BASE t where id='".$base_id."'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		$inbound_idchain=OCIResult($q,'OKTELL_IDCHAIN');
		$inbound_oktell_server_id=OCIResult($q,'OKTELL_SERVER_ID');
	}
	else {
		$inbound_idchain='';
		$inbound_oktell_server_id='';
	}
	
    echo "<div id='History'> История событий:<br/>";
	
	$query=OCIParse($c,"
	SELECT BASE_ID,DATE_DET, to_char(DATE_DET,'dd.mm.yyyy hh24:mi:ss') as DATE_DET_C, STATUS_ID, stat.NAME, stat.COLOR,
	USER_ID, OPERATOR || usr.FIO as FIO, COMMENTS, 
	NULL OKTELL_CALL_HIST_ID, NULL OKTELL_SERVER_ID, NULL OKTELL_IDCHAIN, 1 as WHAT_THIS 
	FROM CALL_BASE_HIST hist
	LEFT JOIN USERS usr ON usr.ID = hist.USER_ID
	LEFT JOIN MED_STATUS stat ON hist.STATUS_ID = stat.ID
	WHERE BASE_ID = '".$base_id."'
	UNION
	select oh.base_id,oh.start_date, to_char(oh.start_date,'dd.mm.yyyy hh24:mi:ss'), NULL,'Исходящий звонок','black',
	oh.user_id, usr.FIO as FIO,'' as COMMENTS,
	oh.id,oh.oktell_server_id,oh.oktell_idchain, 2 as WHAT_THIS 
	from OKTELL_CALL_HIST oh
	LEFT JOIN USERS usr ON usr.ID = oh.USER_ID
	where oh.base_id='".$base_id."'
	
	order by 2 ");	// Сортировка по второму полю
    OCIExecute($query, OCI_DEFAULT);
    $nrows = OCI_Fetch_All($query,$array_hist,0,-1,OCI_FETCHSTATEMENT_BY_ROW);
    oci_free_statement($query);

    if ($nrows > 0) {
        echo "<table class='clear_table'>
        <tr><th style='width: 120px;'>Дата</th><th>Оператор</th><th>Статус</th><th>Примечание</th><th>Информация о звонке</th></tr>";

        foreach($array_hist as $rownum => $value) {
            echo "<tr>";
            echo "<td>".$value['DATE_DET_C']."</td>";
            echo "<td>".$value['FIO']."</td>";
            echo "<td>".$value['NAME']."</td>";
            echo "<td>".$value['COMMENTS']."</td>";

            //информация о звонке
            echo "<td style='text-align: left'>";
            //если статус "новый", то присваиваем idchain входящего звонка
            if($value['STATUS_ID']==1) {
                $value['OKTELL_IDCHAIN']=$inbound_idchain;
                $value['OKTELL_SERVER_ID']=$inbound_oktell_server_id;
            }
            //echo $value['OKTELL_IDCHAIN'];
            if($value['OKTELL_IDCHAIN'] == '00000000-0000-0000-0000-000000000000') echo "Ошибка";
            else if($value['OKTELL_IDCHAIN']<>'') {
                $res = get_okt_connections_info($c, $value['OKTELL_SERVER_ID'], $value['OKTELL_IDCHAIN']);
                if(!$res) echo "Запись не найдена";
                else if(isset($res['error'])) echo $res['error'];
                else {
                    $arr_call=get_connections_array22($res, '');
                    foreach($arr_call as $val) {
                        echo $val."<br>";
                    }
                }
            }
            echo "</td>";
            //
            echo "<td>".$value['WHAT_THIS']."</td>";

            echo "</tr>";
        }
        echo "</table>";
    }

//немного переделанная функция из med_player
function get_connections_array22($connections, $type) {
    global $base_id;
    $arr_direction = [0=>'',1=>'in_out',2=>'in_ivr',3=>'in_in',4=>'out_ivr',5=>'out_in',6=>'out_out',7=>'ivr_out',8=>'ivr_in'];
    $arr_call = array();
    foreach($connections as $partnum => $val) {
        if($partnum==1) $datecall=$val['YYYY'].$val['MM'].$val['DD']."-".$val['HH24'].$val['MI'].$val['SS'].".".$val['mSS'];

        if($val['IsRecorded']==1) $src=$val['oktell_records_url'].'?idconnection='.$val['IdConnection'].(isset($base_id)?"&baseid=".$base_id:"")."&datecall=".$datecall."&partnum=".$partnum;
        else $src='';

        $call_time = $val['DD']."-".$val['MM']."-".$val['YYYY']." ".$val['HH24'].":".$val['MI'];
        if ($val['ConnectionType'] != 0 && $val['IsRecorded'] == 1) {
            array_push($arr_call, "<nobr><img id='" . $val['IdConnection'] . "' class='imgplay' alt='" . $val['call_direction'] . "' title='" . $val['call_direction'] . ". Послушать' src='" . PATH . "/images/imgplay/" . $arr_direction[$val['ConnectionType']] . "_new.png' onclick='click_play(this,\"" . $src . "\")'></img>( ".$val['duration_sec']." сек.)<img class='imgplay' title='Скачать' src='" . PATH . "/images/imgplay/download.png' onclick='down_click(this,\"" . $src . "\")'></img>");
        }
        else array_push($arr_call, "<nobr>".$val['ReasonFailed']." ( ".$val['duration_sec']." сек. )</nobr>");
    }
    return($arr_call);
}

?>
