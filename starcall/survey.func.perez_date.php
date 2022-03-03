<?php 
include("starcall/session.cfg.php"); 

extract($_REQUEST);

if($_SESSION['user']['operator']<>'y') {echo "<font color=red>Access DENY!</font>"; exit();}

include("starcall/conn_string.cfg.php");
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script>
function ch_perez(){
	if(document.all.perez_over.value.substr(0,3)=='min') {document.getElementById('div_perez_time').style.display='none';}
	else document.getElementById('div_perez_time').style.display='inline-block';
}
</script>
<body class="body_marign">
<?php 

extract($_REQUEST);

$project_id=$_SESSION['survey']['project']['id'];
if(!isset($perez_over)) $perez_over='min15';

//сохранение перезыона
if(isset($_POST['perez_go'])) {
	if(strlen($_POST['phone_ext'])>25) $err="<font color=red>Ошибка: Cлишком длинный добавочный (не более 23 знаков)</font>";
	else if ($_POST['phone_num']=='') $err="<font color=red>Ошибка: Пустой номер телефона</font>";
	else {
		include('func.phones_conv.php');
		$result=phones_conv($_POST['phone_num']);
		if(count($result)==1 and $result[0]['err']<>'y') {
		
		
		$perez_phone=$result[0]['phone'];
		if(!isset($perez_ext)) $perez_ext='';
		else $perez_ext=str_replace(array("\n","	",";",","),"&",$perez_ext);

		if(substr($perez_over,0,3)=='dat') {
			$perez_date=substr($perez_over,3)." ".$perez_hh.":".$perez_mi; //местное дата-время перезвона DD.MM.YYYY HH24:MI
			$perez_min='';
		}
		else if (substr($perez_over,0,3)=='min') {
			$perez_date='';
			$perez_min=substr($perez_over,3); //прирощение к текущему московскому времени в минутах
		}
		else {
			$perez_date='';
			$perez_min='';	
		}
	
		
		echo "<script>
			parent.frm_set_status.set_status.value='perez';
			parent.frm_set_status.perez_phone.value='".$result[0]['phone']."';
			parent.frm_set_status.perez_ext.value='".$phone_ext."';
			parent.frm_set_status.perez_date.value='".$perez_date."';
			parent.frm_set_status.perez_min.value='".$perez_min."';
			parent.document.all.ifr_perez.style.display='none';
			parent.document.all.ifr_perez.src='';
			parent.frm_set_status.submit()
			</script>";
			exit();
		}
		else $err="<font color=red>Ошибка: Введён не верный номер! ".$result[0]['phone']."</font>";
	}
}



$q=OCIParse($c, "select 
pr.from_time, pr.to_time,
case when b.utc_msk>=0 then '+'||b.utc_msk else to_char(b.utc_msk) end utc_msk,
to_char(nvl(sysdate+b.utc_msk/24,sysdate),'HH24') HH,
to_char(nvl(sysdate+b.utc_msk/24,sysdate),'MI') MI,
to_char(nvl(sysdate+b.utc_msk/24,sysdate),'DD') DD,
to_char(nvl(sysdate+b.utc_msk/24,sysdate),'MM') MM,
to_char(nvl(sysdate+b.utc_msk/24,sysdate),'YYYY') YYYY,
p.phone,p.ext	 
from STC_PROJECTS pr, STC_BASE b, STC_PHONES p
where pr.id=".$project_id." and b.project_id=pr.id and b.id=".$base_id." and p.base_id=b.id and p.id=".$phone_id);
OCIExecute($q, OCI_DEFAULT);	
OCIFetch($q);
$from_time=OCIResult($q,"FROM_TIME");
$to_time=OCIResult($q,"TO_TIME");
if(!isset($phone_num)) $phone_num=OCIResult($q,"PHONE");
if(!isset($phone_ext)) $phone_ext=OCIResult($q,"EXT");
if(!isset($perez_hh)) $perez_hh=OCIResult($q,"HH");
if(!isset($perez_mi)) $perez_mi=OCIResult($q,"MI");

echo "<form method=post action=survey.func.perez_date.php>";
echo "<input type=hidden name=base_id value=".$base_id.">";
echo "<input type=hidden name=phone_id value=".$phone_id.">";
echo "<b>Перезвонить</b>";
include('func.segment_phone.php');
echo " куда <input type=text name=phone_num value='".segment_phone($phone_num)."'></input> доб.: <input type=text name=phone_ext value='".str_replace("&",",",$phone_ext)."'></input><hr>";
echo "<nobr>когда ";
echo "<select name=perez_over onchange=ch_perez()>";
echo "<optgroup label='время'>";
echo "<option value='min5'".($perez_over=='min5'?" selected":NULL).">через 5 минут</option>";
echo "<option value='min10'".($perez_over=='min10'?" selected":NULL).">через 10 минут</option>";
echo "<option value='min15'".($perez_over=='min15'?" selected":NULL).">через 15 минут</option>";
echo "<option value='min30'".($perez_over=='min30'?" selected":NULL).">через 30 минут</option>";
echo "<option value='min60'".($perez_over=='min60'?" selected":NULL).">через 1 час</option>";
echo "<option value='min120'".($perez_over=='min120'?" selected":NULL).">через 2 часа</option>";
$i=0;
$src_day=array(1,2,3,4,5,6,7); 
$rep_day=array('пн','вт','ср','чт','пт','СБ','ВС');
$src_mon=array('01','02','03','04','05','06','07','08','09','10','11','12');
$rep_mon=array('янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
$cur_date=mktime(0,0,0,OCIResult($q,"MM"),OCIResult($q,"DD"),OCIResult($q,"YYYY"));
echo "</optgroup>";
echo "<optgroup label='дата, время'></optgroup>";
$w=1;
for($date=mktime(0,0,0,date('m',$cur_date),date('d',$cur_date),date('Y',$cur_date)); $date<=mktime(0,0,0,date('m',$cur_date)+1,date('d',$cur_date),date('Y',$cur_date)); $date=mktime(0,0,0,date('m',$date),date('d',$date)+1,date('Y',$date))) {
	$i++;
	if($i==1) $date_string="Сегодня, ".date('d',$date).".".str_replace($src_mon,$rep_mon,date('m',$date));
	else if($i==2) $date_string="Завтра, ".date('d',$date).".".str_replace($src_mon,$rep_mon,date('m',$date));
	else $date_string=date('d',$date).".".str_replace($src_mon,$rep_mon,date('m',$date));
	$date_string.=", ".str_replace($src_day,$rep_day,date('N',$date));
	echo "<option value='dat".date('d.m.Y',$date)."'".($perez_over=="dat".date('d.m.Y',$date)?" selected":NULL).">".$date_string."</option>";
	if(round($i/7)==$i/7) {$w++; echo "<optgroup label='".$w." неделя'></optgroup>";}
} 
echo "<optgroup label='месяц'></optgroup>";
echo "</select>";
echo " (мск<b>".OCIResult($q,"UTC_MSK")."</b>) ";
echo "<div id=div_perez_time style='display:inline-block'>  мест.время: ";

echo "<select name=perez_hh>";
$min_HH=substr($from_time,0,2);
if(substr($to_time,0,2)=='00') $max_HH='23'; else $max_HH=substr($to_time,0,2);
$HH_arr=array('00','01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23');

foreach($HH_arr as $HH) {
	if($perez_hh==$HH) echo "<option value='".$perez_hh."' selected>".$perez_hh."</option>";
	else if($HH>=$min_HH and $HH<=$max_HH) echo "<option value='".$HH."'".($HH==$perez_hh?' selected':NULL).">".$HH."</option>";
}
$MI_arr=array('00','05','10','15','20','25','30','35','40','45','50','55');
echo "</select>";
echo ":";
echo "<select name=perez_mi>";
foreach($MI_arr as $n => $MI) {
	if($n>1 and $perez_mi>$MI_arr[$n-1] and $perez_mi<$MI) echo "<option value='".$perez_mi."' selected>".$perez_mi."</option>";
	echo "<option value='".$MI."'".($MI==$perez_mi?' selected':NULL).">".$MI."</option>";
}
echo "</select>";
echo "</div>";
echo "<script>ch_perez();</script>";
echo "<hr>";
echo "<input type=submit style='background:blue' name=perez_go value='Перезвонить'></input>";
echo " <input type=button name=cancel value='Отмена' onclick=parent.document.all.ifr_perez.style.display='none';parent.document.all.ifr_perez.src=''></input> ";
if(isset($err)) echo $err;
echo "</form>";
?>
</body>
</html>

