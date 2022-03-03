<script>
var now_played_id='';

function click_play(obj,pla_src) {
	pla=document.getElementById("player");
	if(obj.id != now_played_id) {
		if(old_obj=document.getElementById(now_played_id)) {
			//alert(old_obj.src);
			old_obj.src=old_obj.src.replace(/new|playing|stopped/g,'played');
		}
		pla.style.display='';
		pla.src=pla_src;
		//obj.src=obj.src.replace(/new|played|stopped/g,'playing');
		now_played_id=obj.id;
		pla.play();
	}
	else {
		if(pla.paused) {
			//obj.src=obj.src.replace(/new|played|stopped/g,'playing');
			pla.play();
		}
		else {
			//obj.src=obj.src.replace(/new|played|playing/g,'stopped');
			//pla.currentTime = 0;
			pla.pause();
		}
	}
}
function player_onplay() {
	//alert(now_played_id);
	obj=document.getElementById(now_played_id);
	obj.src=obj.src.replace(/new|played|stopped/g,'playing');
}
function player_onpause() {
	obj=document.getElementById(now_played_id);
	obj.src=obj.src.replace(/new|played|playing/g,'stopped');
	//alert(now_played_id);
}
function down_click(obj,url) {
	//window.onload=function(){alert('ddd');}
	//document.getElementById('hidden_frame').onload=function(){alert('ddd');}
	if(hidden_frame.location=url){
		temp=obj.onclick;
		obj.src='images/imgplay/downloaded.png';
		obj.onclick=function(){return false;}
		setTimeout(function(){obj.onclick=temp;},5000);
	}else{}	
}
</script>

<?php
// Рисуем на странице скрытый аудиоплеер и скрытый фрейм
echo "<audio id=player controls preload=metadata style='width:100%;position:fixed;bottom:0;display:none' onplay='player_onplay()' onpause='player_onpause()'></audio>";
echo "<iframe name=hidden_frame id=hidden_frame height=0 width=0 style=display:none></iframe>";

function get_connections_array($connections, $type) {
    global $base_id;
    $arr_direction = [0=>'',1=>'in_out',2=>'in_ivr',3=>'in_in',4=>'out_ivr',5=>'out_in',6=>'out_out',7=>'ivr_out',8=>'ivr_in'];
    $arr_call = array();
    foreach($connections as $partnum => $val) {
        //var_dump($val);
        if($partnum==1) $datecall=$val['YYYY'].$val['MM'].$val['DD']."-".$val['HH24'].$val['MI'].$val['SS'].".".$val['mSS'];
        //if($partnum==1) echo "<font size=3><b>".$val['DD'].".".$val['MM'].".".$val['YYYY']." ".$val['HH24'].":".$val['MI'].":".$val['SS']."</b> - <b>".($val['ConnectionType']==0?"Недозвон":$type)."</b></font><br>";

        if($val['IsRecorded']==1) $src=$val['oktell_records_url'].'?idconnection='.$val['IdConnection'].(isset($base_id)?"&baseid=".$base_id:"")."&datecall=".$datecall."&partnum=".$partnum;
        else $src='';

        $call_time = $val['DD']."-".$val['MM']."-".$val['YYYY']." ".$val['HH24'].":".$val['MI'];
        /* echo "<nobr><b>{$call_time}:{$val['SS']}.{$val['mSS']}</b>. </nobr>".
            ($val['IsRecorded']==1?"<nobr>Часть: <b><a href='".$src."'>".$val['recnum'].". ".$val['call_direction']." (скачать)</a></b>. </nobr>":"").
            ($val['ConnectionType']==0?"<nobr><b>Недозвон</b>. Причина: <b>".$val['ReasonFailed']."</b>. </nobr>":"").
            "<nobr>Номер А: <b>{$val['AOutNumber']}</b>. </nobr><nobr>Номер Б: <b>{$val['BOutNumber']}</b>. </nobr><nobr>Длительность: <b>".(date("H:i:s", mktime(0, 0, $val['duration_sec'])))."</b> сек.</nobr>";
        echo "<br>";*/
        if ($val['ConnectionType'] != 0 && $val['IsRecorded'] == 1) {
            array_push($arr_call, "<nobr>".$call_time."<img id='" . $val['IdConnection'] . "' class='imgplay' alt='" . $val['call_direction'] . "' title='" . $val['call_direction'] . ". Послушать' src='" . PATH . "/images/imgplay/" . $arr_direction[$val['ConnectionType']] . "_new.png' onclick='click_play(this,\"" . $src . "\")'></img>( ".$val['duration_sec']." сек.)<img class='imgplay' title='Скачать' src='" . PATH . "/images/imgplay/download.png' onclick='down_click(this,\"" . $src . "\")'></img>");
        }
        else array_push($arr_call, "<nobr><b>".$val['ReasonFailed']."</b> ( ".$val['duration_sec']." сек. )</nobr>");

        //if($val['IsRecorded']==1) {echo "<audio controls preload=metadata style='width:100%'><source src='".$src."' type='audio/mpeg'></audio>";}
    }
    return($arr_call);
}

//немного переделанная функция из med_player
function get_connections_array2($connections, $type) {
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
