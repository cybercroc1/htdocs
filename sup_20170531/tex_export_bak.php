<?php
session_name('tex');
session_start();
$sid=session_id();
extract($_REQUEST);
include("../../sup_conf/sup_conn_string");

//описание переменных
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if(isset($week)) $start_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d")-7,date("Y")));
if(isset($month)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if(isset($year)) $start_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")-1));

$klinika_id='';
$trbl_id='';
//if (!isset($texnari_id) and $_SESSION['solution']=='y') $texnari_id=$_SESSION['user_id']; elseif(!isset($texnari_id)) 
$texnari_id='';
//if (!isset($kto_id) and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']=='y') $kto_id=$_SESSION['user_id']; 
//elseif (!isset($kto_id)) 
$kto_id='';
if (!isset($lt_grp_id)) $lt_grp_id=$_SESSION['lt_grp_id']; 
//if (!isset($ok) and $_SESSION['eval']=='y' and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']<>'y')


header("Content-type: application/xls");
header("Content-Disposition: attachment; filename=\"rep-".$start_date."-".$end_date.".xls\""); 

$show_closed='';
$klinika_ids=array();
$klinika_names=array();
$trbl_ids=array();
$trbl_names=array();
$texnari_ids=array();
$texnari_names=array();
$kto_ids=array();
$kto_names=array();
$lt_grp_ids=array();
$lt_grp_names=array();
$trbl_grp_ids=array();

$q_where='';
$q_from='';
//

//смена группы
if(isset($ch_grp)) {
$klinika_id='';
$trbl_id='';
$texnari_id='';
}
//

//фильтр административных ограничений
if ($_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or  $_SESSION['create_new']<>'')) {
	
	//Только создатель
	if($_SESSION['look']<>'y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_only='';
		$no_kto='';
		$kto_id=$_SESSION['user_id'];
		$texnari_id='';
	//
	}
	//Создатель+обозреватель
	elseif ($_SESSION['look']=='y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_look='';
		//if($kto_id=='') $kto_id='auth_only'; //если раскомментировать, то заявки от анонимов не увидит создатель+обозреватель (закомментироано еще в 2-х местах
	}
	//
	//Список выбора технаря
	if($_SESSION['look']=='y' or $_SESSION['create_new']=='y') {
	}
	else if(($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y')) {
		$no_texn='';
		$texnari_id=$_SESSION['user_id'];
	}
	//
	//Список выбора групп
	if($_SESSION['lt_grp_id']==0) {
	}
	else {
		$q=OCIParse($c,"select id,name from sup_lt_group slg
		where id='".$lt_grp_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$lt_grp_ids[1]=OCIResult($q,"ID"); $lt_grp_names[1]=OCIResult($q,"NAME");
		$no_grp='';
	}
	//	
}
else {echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; echo "| <a href=tex.php?exit><font color=red>выход</font></a>"; exit();}
//
echo "Пользователь: <b>".$_SESSION['fio'].". </b>"; 

if(isset($no_grp)) {
	echo "Группа: <b>".$lt_grp_names[1].". </b>";
}



//
if($_SESSION['lt_grp_id']<>0) {
	//Список групп проблем
	$q=OCIParse($c,"select distinct stt.trbl_grp_id from SUP_LT slt, sup_trbl_type stt
	where slt.lt_grp_id='".$_SESSION['lt_grp_id']."'
	and stt.id=slt.trbl_id");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_grp_ids[$i]=OCIResult($q,"TRBL_GRP_ID");
	}
	//
	$q_from.=", sup_lt slt ";
	$q_where.="
	 	and k.id=slt.location_id and tt.id=slt.trbl_id and slt.lt_grp_id='".$_SESSION['lt_grp_id']."' ";
	if($i==1) $q_where.=" 
		and (b.trbl_grp_id='".$trbl_grp_ids[1]."' or b.trbl_grp_id is null)";
	elseif($i>1) $q_where.=" 
		and (b.trbl_grp_id in (".implode(',',$trbl_grp_ids).") or b.trbl_grp_id is null)";

	//ограничение по технарям и создателям
	if($_SESSION['solution']=='y' and  $_SESSION['create_new']<>'y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and (b.texnari_id='".$_SESSION['user_id']."' or b.texnari_id is null) ";	
	}
	else
	if($_SESSION['create_new']=='y' and $_SESSION['solution']<>'y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and b.kto_id='".$_SESSION['user_id']."' ";	
	}
	else
	if($_SESSION['create_new']=='y' and $_SESSION['solution']=='y' and $_SESSION['look']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y') {
		$q_where.=" and (b.kto_id='".$_SESSION['user_id']."' or b.texnari_id='".$_SESSION['user_id']."' or b.texnari_id is null) ";
	} 
}
//
//фильтр выбора 
if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
if ($trbl_id<>"") $q_where.=" and tt.id='".$trbl_id."' ";
if ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
if ($kto_id=="not_auth") $q_where.=" and b.kto_id is null "; elseif ($kto_id=="auth_only") $q_where.=" and b.kto_id is not null "; elseif ($kto_id<>"") $q_where.=" and b.kto_id='".$kto_id."' ";
if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
//echo $q_where;
//
//echo $kto_id; //////////////////////////////////////////////////


echo "<table border=1><tr>
<th valign=top>№ заявки<br></th>
<th valign=top>Дата поступления заявки<br></th>

<th valign=top width=150>Объект<br></th>";

//if(!isset($no_kto)) {
	echo "<th valign=top width=150>Кто обратился</th>";
//}

echo "<th valign=top width=150>Тип проблемы<br></th>";

//if(!isset($no_texn)) {
	echo "<th valign=top width=150>Кто занимается</th>";
//}

echo "<th valign=top align=center width=65>Статус</th>
<th valign=top align=center width=65>Длит.".chr(10)."решен.</th>
<th valign=top align=center width=45>Оцен".chr(10)."ка</th>";

//echo "<th valign=top align=center>Кто обратился</th>";
echo "</tr>";

$q_text="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI:SS') date_in_call,
       k.name,
       t.fio,
	   t.id texnari_id,
       b.kto,
       b.u_kogo,
       b.oper_comment,
	   b.trbl_grp_id,   
       case
         when b.date_close is null and b.texnari_id is null then
          'Открыта'
         when b.date_close is null and b.texnari_id is not null then
          'В работе'
         when b.date_close is not null then
          'Закрыта'
       end status,
       case  
		 when b.date_close is null and b.texnari_id is null then
          'blue'
         when b.date_close is null and b.texnari_id is not null then
          'green'
         when b.date_close is not null then
          'red'
       end color,
     '<b>'||to_char(trunc((nvl(b.date_close,sysdate)-b.date_in_call)))||'</b>д. <b>'||
     to_char(trunc(((nvl(b.date_close,sysdate)-b.date_in_call)-trunc((nvl(b.date_close,sysdate)-b.date_in_call)))*24))||'</b>ч.' dur,
	 	 b.quality,
       case
	     when b.quality='1' then 'red'  
		 when b.quality='2' then 'red'
		 when b.quality='3' then '#CC6633'
		 when b.quality='4' then '#339966'
		 when b.quality='5' then 'green'
       end q_color,
	   b.quality_who,
	   b.quality_coment,
	   ph.phone,
	   b.cdpn
  from sup_base b, sup_klinika k, sup_user t, sup_trbl_alloc ta, sup_trbl_type tt, sup_klinika_phones ph".$q_from."
 where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.id=ta.base_id(+)
   and ta.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   ".$q_where."
 order by d
";

$q_trbl=OCIParse($c,"select stt.id,stt.name, decode(sb.trbl_grp_id,stt.trbl_grp_id,'y',null) actual
from sup_base sb, sup_trbl_alloc sta,sup_trbl_type stt
where sb.id=:base_id
and sta.base_id=sb.id and stt.id=sta.trbl_type_id
order by stt.name");

$q=OCIParse($c,$q_text);

//echo $q_text;

$rownum=0;
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
$rownum++;
echo "<tr><td valign=top align=center width=30";
if(OCIResult($q,"CDPN")=='') echo " title='Нет АОНа'"; else if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номмером клиники!'"; else echo " style='color:green'";
echo ">".OCIResult($q,"ID")."</td>
<td valign=top align=center width=120>".OCIResult($q,"DATE_IN_CALL")."</td>
<td valign=top valign=top>".OCIResult($q,"NAME")."</td>";
//if(!isset($no_kto)) {
	echo "<td valign=top valign=top>".OCIResult($q,"KTO")."</td>";
//}
echo "<td valign=top valign=top title='У кого не работает: ".OCIResult($q,"U_KOGO")."
Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>";

OCIBindByName($q_trbl,":base_id",OCIResult($q,"ID"));
OCIExecute($q_trbl,OCI_DEFAULT);
	while (OCIFetch($q_trbl)) {
		if(OCIResult($q_trbl,"ACTUAL")=='y') echo "<font color=black>";
		else echo "<font color=gray>";
		echo OCIResult($q_trbl,"NAME")."<br>";
	}
echo "</td>";
//if(!isset($no_texn)) { 
	echo "<td valign=top valign=top>".OCIResult($q,"FIO")."</td>";
//}
echo "<td valign=top align=center><font color='".OCIResult($q,"COLOR")."'>".OCIResult($q,"STATUS")."</font></td>
<td valign=top align=center>".OCIResult($q,"DUR")."</td>
<td valign=top align=center";
if(OCIResult($q,"QUALITY")<>''){
echo " title='Кто оценил: ".OCIResult($q,"QUALITY_WHO")."
Комментарий: ".OCIResult($q,"QUALITY_COMENT")."'";
}
echo "><font color='".OCIResult($q,"Q_COLOR")."'><b>".OCIResult($q,"QUALITY")."</b></font></td>";
echo "</tr>";	
}
echo "</table>";
echo "</td></tr></table>";
OCIFreeStatement($q);
echo "кол-во строк: <b>".$rownum."</b>";
?>