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
	$q=OCIParse($c,"select t.id,t.lt_grp_id, t.fio, t.coment, t.look, t.solution, t.redirect, t.eval,t.admin from SUP_USER t
where login='".$User."' and password='".$Pass."' and login is not null and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
		if (OCIFetch($q)) {
			$_SESSION['auth']='y';
			$_SESSION['user_id']=OCIResult($q,"ID");
			$_SESSION['lt_grp_id']=OCIResult($q,"LT_GRP_ID"); 
			$_SESSION['look']=OCIResult($q,"LOOK");
			$_SESSION['solution']=OCIResult($q,"SOLUTION");
			$_SESSION['redirect']=OCIResult($q,"REDIRECT");
			$_SESSION['eval']=OCIResult($q,"EVAL");
			$_SESSION['admin']=OCIResult($q,"ADMIN");
			$_SESSION['fio']=OCIResult($q,"FIO");
			$_SESSION['coment']=OCIResult($q,"COMENT");
			$upd=OCIParse($c,"update sup_user set last_logon=sysdate where id='".$_SESSION['user_id']."'");
			OCIExecute($upd,OCI_DEFAULT); 
			OCICommit($c);			
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
if (!isset($texnari_id) and $_SESSION['solution']=='y') $texnari_id=$_SESSION['user_id']; else if(!isset($texnari_id)) $texnari_id='';
if (!isset($lt_grp_id)) $lt_grp_id=$_SESSION['lt_grp_id']; 
if (!isset($ok) and $_SESSION['eval']=='y') $show_closed='';

$klinika_ids=array();
$klinika_names=array();
$trbl_ids=array();
$trbl_names=array();
$texnari_ids=array();
$texnari_names=array();
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
if ($_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'')) {

	//Список выбора клиники
	$q=OCIParse($c,"select distinct sk.id,sk.name from sup_lt slt, sup_klinika sk
	where slt.lt_grp_id=decode('".$lt_grp_id."','0',slt.lt_grp_id,'".$lt_grp_id."')
	and slt.trbl_id=nvl('".$trbl_id."',slt.trbl_id)
	and sk.id=slt.location_id
	order by name");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $klinika_ids[$i]=OCIResult($q,"ID"); $klinika_names[$i]=OCIResult($q,"NAME");
	}
	//
	
	//Список выбора проблемы
	$q=OCIParse($c,"select distinct st.id,st.name from sup_lt slt, sup_trbl_type st
	where slt.lt_grp_id=decode('".$lt_grp_id."','0',slt.lt_grp_id,'".$lt_grp_id."')
	and slt.location_id=nvl('".$klinika_id."',slt.location_id)
	and st.id=slt.trbl_id
	order by name");
	OCIExecute($q,OCI_DEFAULT); 
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_ids[$i]=OCIResult($q,"ID"); $trbl_names[$i]=OCIResult($q,"NAME");
	}	
	//
	
	//Список выбора технаря
	if($_SESSION['look']=='y') {
		if($lt_grp_id==0) {
			$q=OCIParse($c,"select su.id,su.fio from sup_user su
			where su.solution='y' and su.deleted is null
			order by su.fio");
		}
		else {
			$q=OCIParse($c,"select su.id,su.fio from sup_user su
			where (su.lt_grp_id in (
			select distinct lt2.lt_grp_id from sup_lt lt1, sup_lt lt2  
			where lt1.lt_grp_id='".$lt_grp_id."'
			and lt1.location_id=lt2.location_id and lt1.trbl_id=lt2.trbl_id
			) or su.lt_grp_id=0)
			and su.solution='y' and su.deleted is null
			order by su.fio");
		}
		OCIExecute($q,OCI_DEFAULT);
		$i=0; while (OCIFetch($q)) {
			$i++; $texnari_ids[$i]=OCIResult($q,"ID"); $texnari_names[$i]=OCIResult($q,"FIO");
		}
		if($i==1) $no_who='';
	}
	else if($_SESSION['solution']=='y' or $_SESSION['redirect']=='y' or $_SESSION['eval']=='y') {
		$no_who='';
		$texnari_ids[1]=$_SESSION['user_id'];$texnari_names[1]=$_SESSION['fio'];
		$texnari_id=$_SESSION['user_id'];
	}
	//
	//Список выбора групп
	if($_SESSION['lt_grp_id']==0) {
		$q=OCIParse($c,"select id,name from sup_lt_group slg
		where id<>0 and eval_only is null
		order by name");
		OCIExecute($q,OCI_DEFAULT);
		$i=0; while (OCIFetch($q)) {
			$i++; $lt_grp_ids[$i]=OCIResult($q,"ID"); $lt_grp_names[$i]=OCIResult($q,"NAME");
		}
		if($i==1) $no_grp='';
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

if($lt_grp_id<>0) {
	//Список групп проблем
	$q=OCIParse($c,"select distinct stt.trbl_grp_id from SUP_LT slt, sup_trbl_type stt
	where slt.lt_grp_id='".$lt_grp_id."'
	and stt.id=slt.trbl_id");
	OCIExecute($q,OCI_DEFAULT);
	$i=0; while (OCIFetch($q)) {
		$i++; $trbl_grp_ids[$i]=OCIResult($q,"TRBL_GRP_ID");
	}
	//
	$q_from.=", sup_lt slt ";
	$q_where.="
	 	and k.id=slt.location_id and tt.id=slt.trbl_id and slt.lt_grp_id='".$lt_grp_id."' ";
	if($i==1) $q_where.=" 
		and (b.trbl_grp_id='".$trbl_grp_ids[1]."' or b.trbl_grp_id is null)";
	else if($i>1) $q_where.=" 
		and (b.trbl_grp_id in (".implode(',',$trbl_grp_ids).") or b.trbl_grp_id is null)";
}

//
//фильтр выбора 
if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
if ($trbl_id<>"") $q_where.=" and tt.id='".$trbl_id."' ";
if ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
//echo $q_where;
//

$q=OCIParse($c,"select to_char(max(last_change),'DD.MM.YYYY HH24:MI:SS') date_last_change from sup_base");
OCIExecute($q, OCI_DEFAULT);
OCIFetch($q);
echo "<input type=hidden name='date_last_change' value='".OCIResult($q,"DATE_LAST_CHANGE")."'>";

echo "<table align=center><tr><td>";
echo "<table width=100%><tr><td align=left><font size=3>";

if (isset($no_who)) echo "Пользователь: <b>".$texnari_names[1].". </b>";
else echo "Пользователь: <b>".$_SESSION['fio'].". </b>"; 

if(isset($no_grp)) {
	echo "Группа: <b>".$lt_grp_names[1].". </b>";
}
else {
	echo " группа: <select style='width:280px' name=lt_grp_id onchange=ch_grp.click()>";
	echo "<option value='0' style='color:green'>ВСЕ</option>";
	foreach($lt_grp_ids as $key => $val) {
		echo "<option value='".$val."'";
		if($val==$lt_grp_id) echo " selected";
		echo ">".$lt_grp_names[$key]."</option>";
	}
echo "</select>";
}

echo "</font></td><td align=right>";
echo "<a href=adm_pwd.php>сменить пароль</a> | ";

if($_SESSION['admin']=='y') echo "<a href=adm_usr.php>админ</a> | "; 

echo "<a href=tex.php?exit><font color=red>выход</font></a></td></tr></table>";

echo "<table align=center bgcolor=black cellspacing=1 cellpadding=1 width='auto'><tr>
<th bgcolor=white valign=top colspan=2>Дата поступления заявки<br><nobr>
c <input type=text value='"; if (isset($start_date)) echo $start_date; echo "' size=7 name=start_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date)) echo $end_date; echo "' size=7 name=end_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date);return false; HIDEFOCUS' onchange=ok.click()>";
echo "</nobr></th>

<th bgcolor=white valign=top width=150>Объект<br>";

echo "<select style='width:100%' name=klinika_id onchange=ok.click()>";
if(count($klinika_ids)>1) echo "<option value='' style='color:green'>ВСЕ</option>";
foreach($klinika_ids as $key => $val) {
	echo "<option value='".$val."'";
	if($val==$klinika_id) echo " selected";
	echo ">".$klinika_names[$key]."</option>";	
}
echo "</select>";
echo "</th>

<th bgcolor=white valign=top width=150>Тип проблемы<br>";

echo "<select style='width:280px' name=trbl_id onchange=ok.click()>";
if(count($trbl_ids)>1) echo "<option value='' style='color:green'>ВСЕ</option>";
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
	if(count($texnari_ids)>1) echo "<option value='' style='color:green'>ВСЕ</option>";
	foreach($texnari_ids as $key => $val) {
		echo "<option value='".$val."'";
		if($val==$texnari_id) echo " selected";
		echo ">".$texnari_names[$key]."</option>";	
	}
	echo "</select>";
	echo "</th>";
}
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
	   ph.phone
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

echo "<th bgcolor=white valign=top align=center width=65>Статус<br><nobr>(закр.<input type=checkbox ";
if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()>)</nobr></th>
<th bgcolor=white valign=top align=center width=65>Длит.<br>решен.</th>
<th bgcolor=white valign=top align=center width=45>Оцен-<br>ка</th>";
echo "<th bgcolor=white valign=top align=center>Кто звонил</th>";
echo "</tr>";
$q=OCIParse($c,$q_text);
//echo $q_text;
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
<input type="submit" style="display:none" name=ch_grp value="">
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
