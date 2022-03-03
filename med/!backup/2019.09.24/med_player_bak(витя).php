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
//1. Подключаем файл с коннектором MSSQL и путями к записям
include("med/adm_url.php");
include("med/oktell_conn_string.php");
include("oktell_records_path.php");

//2. Рисуем на странице скрытый аудиоплеер и скрытый фрейм
echo "<audio id=player controls preload=metadata style='width:100%;position:fixed;bottom:0;display:none' onplay='player_onplay()' onpause='player_onpause()'></audio>";
echo "<iframe name=hidden_frame id=hidden_frame height=0 width=0 style=display:none></iframe>";

//3. Готовим запрос
$sql="SELECT
t.id IdConnection, t.IdChain,
convert(varchar(25),timestart,121) timestart,
substring(convert(varchar(25),timestart,121),1,4)+
substring(convert(varchar(25),timestart,121),6,2)+
substring(convert(varchar(25),timestart,121),9,2)+'\\'+
substring(convert(varchar(25),timestart,121),12,2)+
substring(convert(varchar(25),timestart,121),15,2)+'\\' file_path,
'mix_'+(case when alinenum<blinenum then alinenum else blinenum end)+'_'+(case when blinenum>alinenum then blinenum else alinenum end)+'__'+
substring(convert(varchar(25),timestart,121),1,4)+'_'+
substring(convert(varchar(25),timestart,121),6,2)+'_'+
substring(convert(varchar(25),timestart,121),9,2)+'__'+
substring(convert(varchar(25),timestart,121),12,2)+'_'+
substring(convert(varchar(25),timestart,121),15,2)+'_'+
substring(convert(varchar(25),timestart,121),18,2)+'_'+
substring(convert(varchar(25),timestart,121),21,3)+'.mp3' file_name,
t.AOutNumber,t.BOutNumber,
	  substring(convert(varchar(25),timestart,121),1,4) YYYY,
	  substring(convert(varchar(25),timestart,121),6,2) MM,
	  substring(convert(varchar(25),timestart,121),9,2) DD,
	  substring(convert(varchar(25),timestart,121),12,2) HH24,
	  substring(convert(varchar(25),timestart,121),15,2) MI,
	  substring(convert(varchar(25),timestart,121),18,2) SS,
	  substring(convert(varchar(25),timestart,121),21,3) mSS,
case 
when t.ConnectionType=1 then 'Изнутри наружу'
when t.ConnectionType=2 then 'Изнутри в IVR'
when t.ConnectionType=3 then 'Изнутри внутрь'
when t.ConnectionType=4 then 'Снаружи в IVR'
when t.ConnectionType=5 then 'Снаружи внутрь'
when t.ConnectionType=6 then 'Снаружи наружу'
when t.ConnectionType=7 then 'С IVR наружу'
when t.ConnectionType=8 then 'С IVR внутрь'
end call_direction_text,
case 
when t.ConnectionType=1 then 'in_out'
when t.ConnectionType=2 then 'in_ivr'
when t.ConnectionType=3 then 'in_in'
when t.ConnectionType=4 then 'out_ivr'
when t.ConnectionType=5 then 'out_in'
when t.ConnectionType=6 then 'out_out'
when t.ConnectionType=7 then 'ivr_out'
when t.ConnectionType=8 then 'ivr_in'
end call_direction_type			
FROM [oktell].[dbo].[A_Stat_Connections_1x1] t with (nolock)
where IdChain=:idchain and IsRecorded=1
order by TimeStart";
$q_rec=$c_okt->prepare($sql);

//4. Создаем функцию для отображения ссылок
function show_record_link($idchain,$datecall) {
	global $oktell_records_path;
	global $oktell_records_url;
	global $q_rec;
	$res='';
	//проверка корректности UUID
	$arr_call = array();
	if(preg_match('/^[0-9abcdef]{8}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{4}-[0-9abcdef]{12}$/i',$idchain)) { 
		$q_rec->bindValue(':idchain',$idchain);
				
		$q_rec->execute();
							
		$res.= " | ";
		$partnum=0; while($row=$q_rec->fetch()) {$partnum++;
			//имя файла на выходе (если передано имся в переменной name, то файл именуется этим именем)
			if(!isset($datecall) or $datecall=='') $datecall=$row['YYYY'].$row['MM'].$row['DD']."-".$row['HH24'].$row['MI'].$row['SS']."-".$row['mSS'];
			
			$file_path=$oktell_records_path.$row['file_path'];
			$file_name=$row['file_name'];
			$new_file_name=$row['file_name'];	
			if(file_exists($file_path.$file_name)) {
				//echo $file_path.$file_name."<br>";
				$src=$oktell_records_url.'?idconnection='.$row['IdConnection']."&datecall=".$datecall."&partnum=".$partnum;
				//echo $src."<br>";
				$res.=$partnum.") <nobr><img id='".$row['IdConnection']."' class='imgplay' alt='".$row['call_direction_text']."' title='".$row['call_direction_text'].". Послушать' src='images/imgplay/".$row['call_direction_type']."_new.png' onclick='click_play(this,\"".$src."\")'></img>";
				$res.=" <img class='imgplay' title='Скачать' src='images/imgplay/download.png' onclick='down_click(this,\"".$src."\")'></img>";
				$res.= "&nbsp;|&nbsp;";
				array_push($arr_call, "<nobr><img id='".$row['IdConnection']."' class='imgplay' alt='".$row['call_direction_text']."' title='".$row['call_direction_text'].". Послушать' src='images/imgplay/".$row['call_direction_type']."_new.png' onclick='click_play(this,\"".$src."\")'></img>  <img class='imgplay' title='Скачать' src='images/imgplay/download.png' onclick='down_click(this,\"".$src."\")'></img>");
			}
		}
	}
    //5. Теперь для каждого звонка можно нарисовать ссылку
    //echo "<table class='clear_table'><tr>".$res."</tr></table>";
    // но можем вернуть массив с разбивкой по звонкам
	return $arr_call;
}

//6. Получаем массив ссылок со всеми звонками используя данную функцию по idchain
//передавать вторым параметром дату звонка ГГГГММДДЧЧМИСС
$arr_arr = show_record_link($idchain,'');
//echo show_record_link('b2e7da3a-53de-4f5d-905d-4baf7819011f','');
?>
