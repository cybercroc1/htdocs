<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка Все-Свои</title>
</head>
<body leftmargin="3" topmargin="3">
<?php
//echo session_name()."--".session_id();

$sid=session_id();

extract($_REQUEST);
if (isset($exit)) {
session_destroy();
header('Location:tex.php');
}

include("../../sup_conf/sup_conn_string");

//Логин
if(!isset($_SESSION['auth'])) {
	if(isset($User) and isset($Pass)) {
	$q=OCIParse($c,"select * from sup_users u 
	where username='".$User."' and password='".$Pass."' and (all_tex is not null or tex_id is not null)");
	OCIExecute($q,OCI_DEFAULT);
		if (OCIFetch($q)) {
			$_SESSION['auth']='y';
			$_SESSION['secr']=OCIResult($q,"SECR"); 
			$_SESSION['all_tex']=OCIResult($q,"ALL_TEX");
			$_SESSION['tex_id']=OCIResult($q,"TEX_ID");
			//$_SESSION['tex_fio']=OCIResult($q,"TEX_FIO");
			$_SESSION['tex_wrkgrp_id']=OCIResult($q,"TEX_WRKGRP_ID");
			$_SESSION['user_info']=OCIResult($q,"FIO");			
			echo "<script>location.reload('tex.php');</script>";
		}
		else {
			session_destroy();
			echo "<font color=red><b>Не верное имя или пароль!</b></font>";
		}
		
	}
	//форма логина
	if(!isset($_SESSION['auth'])) {
	echo "<form method=\"POST\">
  <div align=\"center\"><center><table border=\"0\" width=\"778\" height=\"29\" 
  cellspacing=\"1\" cellpadding=\"0\">
</table>
  </center></div><div align=\"center\"><center><table border=\"0\" width=\"778\" 
  cellspacing=\"0\" cellpadding=\"0\" height=\"137\">
   <tr>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пользователь</strong></font></td>
      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"text\" name=\"User\" size=\"20\"></td>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"></td>
    </tr>
    <tr>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"right\"><p><font color=\"#00000\"><strong>Пароль</strong></font></td>
      <td width=\"20%\" align=\"center\" height=\"25\"><input type=\"password\" name=\"Pass\" size=\"20\"></td>
      <td width=\"20%\" height=\"25\"></td>
      <td width=\"20%\" height=\"25\"><div align=\"center\"></div></td>
    </tr>
    <tr align=\"center\">
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" align=\"center\" height=\"65\"><input type=\"submit\" value=\"Вход\"></td>
      <td width=\"20%\" height=\"65\"></td>
      <td width=\"20%\" height=\"65\">&nbsp;<p></td>
    </tr>
  </table>";
	exit();  
	}
	//
}
//
echo "<form method=get>";

//описание переменных
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if (!isset($klinika_id)) $klinika_id='';
if (!isset($trbl_id)) $trbl_id='';
if (!isset($texnari_id)) $texnari_id='';
if (!isset($tex_grp_id)) $tex_grp_id='';

$klinika_ids=array();
$klinika_names=array();
$trbl_ids=array();
$trbl_names=array();
$texnari_ids=array();
$texnari_names=array();
$tex_grp_ids=array();
$tex_grp_names=array();

$q_where='';
//

//фильтр административных ограничений
if ($_SESSION['tex_wrkgrp_id']=='' and $_SESSION['all_tex']<>'y' and $_SESSION['tex_id']<>'') {
//имеет доступ только к своим и новым заявкам, относящимся к его группе
	$q=OCIParse($c,"select distinct k.id,k.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tw, sup_klinika_grp_alloc kga, sup_klinika k
where tga.tex_id='".$_SESSION['tex_id']."' and tw.id=nvl('".$tex_grp_id."',tw.id) and
tw.id=tga.tex_grp_id and kga.klinika_grp_id=tw.klinika_grp_id and k.id=kga.klinika_id
order by k.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $klinika_ids[$i]=OCIResult($q,"ID"); $klinika_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct tt.id,tt.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tw, sup_trbl_grp_alloc ttga, sup_trbl_type tt
where tga.tex_id='".$_SESSION['tex_id']."' and tw.id=nvl('".$tex_grp_id."',tw.id) and
tw.id=tga.tex_grp_id and ttga.trbl_grp_id=tw.trbl_grp_id and tt.id=ttga.trbl_id
order by tt.name");
	OCIExecute($q,OCI_DEFAULT); 
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_ids[$i]=OCIResult($q,"ID"); $trbl_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct t.id,t.fio from sup_texnari t
where t.id='".$_SESSION['tex_id']."'");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	if($i==1) $no_who=''; 
	
	$q=OCIParse($c,"select tga.tex_grp_id, tg.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tg
where tga.tex_id='".$_SESSION['tex_id']."' and tg.id=tga.tex_grp_id");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $tex_grp_ids[$i]=OCIResult($q,"TEX_GRP_ID"); $tex_grp_names[$i]=OCIResult($q,"NAME");
	}
	if($i==1) $no_grp='';	
}
else if ($_SESSION['tex_wrkgrp_id']=='' and $_SESSION['all_tex']=='y' and $_SESSION['tex_id']<>'') {
	//имеет достук к заявкам всех пользователей всех групп, в которых сам находится
	$q=OCIParse($c,"select distinct k.id,k.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tw, sup_klinika_grp_alloc kga, sup_klinika k
where tga.tex_id='".$_SESSION['tex_id']."' and tw.id=nvl('".$tex_grp_id."',tw.id) and 
tw.id=tga.tex_grp_id and kga.klinika_grp_id=tw.klinika_grp_id and k.id=kga.klinika_id
order by k.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $klinika_ids[$i]=OCIResult($q,"ID"); $klinika_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct tt.id,tt.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tw, sup_trbl_grp_alloc ttga, sup_trbl_type tt
where tga.tex_id='".$_SESSION['tex_id']."' and tw.id=nvl('".$tex_grp_id."',tw.id) and
tw.id=tga.tex_grp_id and ttga.trbl_grp_id=tw.trbl_grp_id and tt.id=ttga.trbl_id
order by tt.name");
	OCIExecute($q,OCI_DEFAULT); 
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_ids[$i]=OCIResult($q,"ID"); $trbl_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct t.id,t.fio from sup_tex_grp_alloc tga, sup_tex_grp_alloc tga2, sup_texnari t
where tga.tex_id='".$_SESSION['tex_id']."' and tga.tex_grp_id=nvl('".$tex_grp_id."',tga.tex_grp_id) and
tga2.tex_grp_id=tga.tex_grp_id and t.id=tga2.tex_id and t.deleted is null
order by t.fio");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	
	$q=OCIParse($c,"select tga.tex_grp_id, tg.name from sup_tex_grp_alloc tga, sup_tex_wrkgrp tg
where tga.tex_id='".$_SESSION['tex_id']."' and tg.id=tga.tex_grp_id");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $tex_grp_ids[$i]=OCIResult($q,"TEX_GRP_ID"); $tex_grp_names[$i]=OCIResult($q,"NAME");
	}
	if($i==1) $no_grp=''; 	
}
else if ($_SESSION['tex_wrkgrp_id']<>'' and $_SESSION['all_tex']=='y' and $_SESSION['tex_id']=='') {
	//имеет доступ ко всем заявка всех пользователей, относящимся к указанной группе
	$q=OCIParse($c,"select distinct k.id,k.name from sup_tex_wrkgrp tw, sup_klinika_grp_alloc kga, sup_klinika k
where tw.id='".$_SESSION['tex_wrkgrp_id']."' and
kga.klinika_grp_id=tw.klinika_grp_id and k.id=kga.klinika_id
order by k.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $klinika_ids[$i]=OCIResult($q,"ID"); $klinika_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct tt.id,tt.name from sup_tex_wrkgrp tw, sup_trbl_grp_alloc tga, sup_trbl_type tt
where tw.id='".$_SESSION['tex_wrkgrp_id']."' and
tga.trbl_grp_id=tw.trbl_grp_id and tt.id=tga.trbl_id
order by tt.name");
	OCIExecute($q,OCI_DEFAULT); 
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_ids[$i]=OCIResult($q,"ID"); $trbl_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct t.id,t.fio from sup_tex_grp_alloc tga, sup_texnari t
where tga.tex_grp_id='".$_SESSION['tex_wrkgrp_id']."' and t.id=tga.tex_id and t.deleted is null
order by t.fio");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	$q=OCIParse($c,"select tg.id, tg.name from sup_tex_wrkgrp tg
where tg.id='".$_SESSION['tex_wrkgrp_id']."'
order by tg.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $tex_grp_ids[$i]=OCIResult($q,"ID"); $tex_grp_names[$i]=OCIResult($q,"NAME");
	}
	if($i==1) $no_grp=''; 	
}
else if ($_SESSION['tex_wrkgrp_id']=='' and $_SESSION['all_tex']=='y' and $_SESSION['tex_id']=='') {
	//имееет доступ ко всем заявкам всех пользователей
	$q=OCIParse($c,"select distinct k.id,k.name from sup_tex_wrkgrp tw, sup_klinika_grp_alloc kga, sup_klinika k
where tw.id=nvl('".$tex_grp_id."',tw.id) and
kga.klinika_grp_id=tw.klinika_grp_id and k.id=kga.klinika_id
order by k.name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $klinika_ids[$i]=OCIResult($q,"ID"); $klinika_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct tt.id,tt.name from sup_tex_wrkgrp tw, sup_trbl_grp_alloc tga, sup_trbl_type tt
where tw.id=nvl('".$tex_grp_id."',tw.id) and
tga.trbl_grp_id=tw.trbl_grp_id and tt.id=tga.trbl_id
order by tt.name");
	OCIExecute($q,OCI_DEFAULT); 
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_ids[$i]=OCIResult($q,"ID"); $trbl_names[$i]=OCIResult($q,"NAME");
	}
	$q=OCIParse($c,"select distinct t.id,t.fio from sup_tex_grp_alloc tga, sup_texnari t
where tga.tex_grp_id=nvl('".$tex_grp_id."',tga.tex_grp_id) and t.id=tga.tex_id and t.deleted is null
order by t.fio");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
	}
	$q=OCIParse($c,"select tg.id,tg.name from sup_tex_wrkgrp tg");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $tex_grp_ids[$i]=OCIResult($q,"ID"); $tex_grp_names[$i]=OCIResult($q,"NAME");
	}
	if($i==1) $no_grp='';	
}
else {echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; exit();}

$q_where.=" and k.id in (".implode(',',$klinika_ids).") --klinika_ids
 and tt.id in (".implode(',',$trbl_ids).") --trbl_ids
 and (b.texnari_id in (".implode(',',$texnari_ids).") or b.texnari_id is null) --tex_ids
 and b.wrkgrp_id in (".implode(',',$tex_grp_ids).") --tex_wrkgrp_id
 ";
//
//фильтр выбора 
if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
if ($trbl_id<>"") $q_where.=" and tt.id='".$trbl_id."' ";
if ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
if ($tex_grp_id<>"") $q_where.=" and b.wrkgrp_id='".$tex_grp_id."' ";
if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
//echo $q_where;
//

$q=OCIParse($c,"select to_char(max(last_change),'DD.MM.YYYY HH24:MI:SS') date_last_change from sup_base");
OCIExecute($q, OCI_DEFAULT);
OCIFetch($q);
echo "<input type=hidden name='date_last_change' value='".OCIResult($q,"DATE_LAST_CHANGE")."'>";


$q=OCIParse($c,"select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI:SS') date_in_call,
       k.name,
       t.fio,
	   t.id texnari_id,
       b.kto,
       b.u_kogo,
       b.oper_comment,
	   b.wrkgrp_id,   
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
	   ph.phone
  from sup_base b, sup_klinika k, sup_texnari t, sup_trbl_alloc ta, sup_trbl_type tt, sup_klinika_phones ph
 where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.id=ta.base_id(+)
   and ta.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
  ".$q_where."
 order by d
");

$q_trbl=OCIParse($c,"select t.id,t.name,
(select decode(count(*),0,null,'y') from sup_tex_wrkgrp tw, sup_trbl_grp_alloc tga
where tw.id=b.wrkgrp_id and tga.trbl_id=t.id and tga.trbl_grp_id=tw.trbl_grp_id) actual
from sup_base b, sup_trbl_alloc a, sup_trbl_type t
where b.id=:base_id and a.base_id=b.id and t.id=a.trbl_type_id
order by t.name");
echo "<table align=center><tr><td>";
echo "<table width=100%><tr><td align=left><font size=4>";

if (isset($no_who)) echo $texnari_names[1]."; ";
else echo $_SESSION['user_info']."; "; 

if(isset($no_grp)) {
	echo $tex_grp_names[1];
}
else {
	echo " группа: <select style='width:280px' name=tex_grp_id onchange=ok.click()>";
	echo "<option value='' style='color:green'>ВСЕ</option>";
	foreach($tex_grp_ids as $key => $val) {
		echo "<option value='".$val."'";
		if($val==$tex_grp_id) echo " selected";
		echo ">".$tex_grp_names[$key]."</option>";
	}
echo "</select>";
}

echo "</font></td><td align=right><a href=tex.php?exit>Выход</a></td></tr></table>";

echo "<table align=center bgcolor=black cellspacing=1 cellpadding=1 width='auto'><tr>
<th bgcolor=white valign=top colspan=2>Дата поступления заявки<br><nobr>
c <input type=text value='"; if (isset($start_date)) echo $start_date; echo "' size=7 name=start_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date)) echo $end_date; echo "' size=7 name=end_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date);return false; HIDEFOCUS' onchange=ok.click()>";
echo "</nobr></th>

<th bgcolor=white valign=top width=150>Объект<br>";

echo "<select style='width:100%' name=klinika_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
foreach($klinika_ids as $key => $val) {
	echo "<option value='".$val."'";
	if($val==$klinika_id) echo " selected";
	echo ">".$klinika_names[$key]."</option>";	
}
echo "</select>";
echo "</th>

<th bgcolor=white valign=top width=150>Тип проблемы<br>";

echo "<select style='width:280px' name=trbl_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
foreach($trbl_ids as $key => $val) {
	echo "<option value='".$val."'";
	if($val==$trbl_id) echo " selected";
	echo ">".$trbl_names[$key]."</option>";
}
echo "</select>";
echo "</th>";

if(!isset($no_who)) {
	echo "<th bgcolor=white valign=top width=150>Кто занимается<br>";

	echo "<select style='width:100%' name=texnari_id onchange=ok.click()>";
	echo "<option value='' style='color:green'>ВСЕ</option>";
	foreach($texnari_ids as $key => $val) {
		echo "<option value='".$val."'";
		if($val==$texnari_id) echo " selected";
		echo ">".$texnari_names[$key]."</option>";	
	}
	echo "</select>";
	echo "</th>";
}

echo "<th bgcolor=white valign=top align=center width=65>Статус<br><nobr>(закр.<input type=checkbox ";
if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()>)</nobr></th>
<th bgcolor=white valign=top align=center width=65>Длит.<br>решен.</th>
<th bgcolor=white valign=top align=center width=45>Оцен-<br>ка</th>";
echo "<th bgcolor=white valign=top align=center>Кто звонил</th>";
echo "</tr>";
$rownum=0;
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
$rownum++;
echo "<tr onmousemove='sel_row(this)' onmouseout='unsel_row(this)' onclick='javascript:open_window(\"".OCIResult($q,"ID")."\",\"".OCIResult($q,"TEXNARI_ID")."\",\"".$sid."\")'>
<td bgcolor=white valign=top align=center width=30";
if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номмером клиники!'";
echo ">".OCIResult($q,"ID")."</td>
<td bgcolor=white valign=top align=center width=120";
if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номмером клиники!'";
echo ">".OCIResult($q,"DATE_IN_CALL")."</td>
<td bgcolor=white valign=top valign=top>".OCIResult($q,"NAME")."</td>
<td bgcolor=white valign=top valign=top title='Кто звонил: ".OCIResult($q,"KTO")."
У кого не работает: ".OCIResult($q,"U_KOGO")."
Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>";

OCIBindByName($q_trbl,":base_id",OCIResult($q,"ID"));
OCIExecute($q_trbl,OCI_DEFAULT);
	while (OCIFetch($q_trbl)) {
		if(OCIResult($q_trbl,"ACTUAL")=='y') echo "<font color=black>";
		else echo "<font color=gray>";
		echo OCIResult($q_trbl,"NAME")."<br>";
	}
echo "</td>";
if(!isset($no_who)) echo "<td bgcolor=white valign=top valign=top>".OCIResult($q,"FIO")."</td>";
echo "<td bgcolor=white valign=top align=center><font color='".OCIResult($q,"COLOR")."'>".OCIResult($q,"STATUS")."</font></td>
<td bgcolor=white valign=top align=center>".OCIResult($q,"DUR")."</td>
<td bgcolor=white valign=top align=center";
if(OCIResult($q,"QUALITY")<>''){
echo " title='Кто оценил: ".OCIResult($q,"QUALITY_WHO")."
Комментарий: ".OCIResult($q,"QUALITY_COMENT")."'";
}
echo "><font color='".OCIResult($q,"Q_COLOR")."'><b>".OCIResult($q,"QUALITY")."</b></font></td>";
echo "<td bgcolor=white valign=top valign=top>".OCIResult($q,"KTO")."</td>";
echo "</tr>";	
}
echo "</table>";
echo "</td></tr></table>";
OCIFreeStatement($q);
?>
<input type="submit" style="display:none" name=ok value="">
</form>
<script>

function sel_row(row) {
for(i=0; i<row.cells.length; i++) {
row.cells[i].bgColor='#66FFFF';
}
}
function unsel_row(row) {
for(i=0; i<row.cells.length; i++) {
row.cells[i].bgColor='white';
}
}
function ch_show_closed() {
if (document.all.show_closed.checked==true) {location.reload('tex.php?show_closed=1');}
else {location.reload('tex.php');}
}
function open_window(base_id,texnari_id,sid) {
win=window.open("tex_edit.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid,"edit_tex","width=550, height=700, toolbar=no, scrollbars=yes, resizable=yes, status=yes, left=1,top=1");
win.focus();
}
</script>
</body>
</html>
<iframe name=check_new src="tex_check_new.php" style="display:none"></iframe>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_tex.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
