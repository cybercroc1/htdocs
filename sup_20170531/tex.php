<?php
/*if(
	(
		substr($_SERVER['REMOTE_ADDR'],0,11)=='192.168.12.' 
	 or substr($_SERVER['REMOTE_ADDR'],0,11)=='192.168.13.'
	)
	and $_SERVER['REMOTE_ADDR']<>'192.168.12.153'
	and $_SERVER['REMOTE_ADDR']<>'192.168.12.51'
	and $_SERVER['REMOTE_ADDR']<>'192.168.12.61'
) {
header('Location:http://mantis.vse-svoi.net/userapi/kc-1905.php');
}*/

session_name('tex');
session_start();

$sid=session_id();

extract($_REQUEST);
if(!isset($ticketId)) $ticketId=''; //эта переменная нужда для возможности перехода на конкретную заявку по ссылке в письме 
if(!isset($find_id)) $find_id='';
if (isset($exit)) {
//setcookie('login');
//setcookie('pass');
session_destroy();
header('Location:tex.php');
}

include("../../sup_conf/sup_conn_string");

	if(isset($User) and isset($Pass)) {
	$q=OCIParse($c,"select t.id,t.lt_grp_id, t.fio, t.coment, t.look, t.solution, t.redirect, t.eval,t.admin,t.deny_close,t.create_new,t.rep_stat from SUP_USER t
where login='".$User."' and password='".$Pass."' and login is not null and deleted is null");
	OCIExecute($q,OCI_DEFAULT);
		if (OCIFetch($q)) {
			setcookie('login',$User,mktime(0,0,0,1,1,2030));
			if(isset($save_pass)) {
				setcookie('pass',$Pass,mktime(0,0,0,1,1,2030));
			}
			else {
				setcookie('pass');
			}
			$_SESSION['auth']='y';
			$_SESSION['user_id']=OCIResult($q,"ID");
			$_SESSION['lt_grp_id']=OCIResult($q,"LT_GRP_ID"); 
			$_SESSION['look']=OCIResult($q,"LOOK");
			$_SESSION['solution']=OCIResult($q,"SOLUTION");
			$_SESSION['redirect']=OCIResult($q,"REDIRECT");
			$_SESSION['deny_close']=OCIResult($q,"DENY_CLOSE");
			$_SESSION['eval']=OCIResult($q,"EVAL");
			$_SESSION['admin']=OCIResult($q,"ADMIN");
			$_SESSION['fio']=OCIResult($q,"FIO");
			$_SESSION['coment']=OCIResult($q,"COMENT");
			$_SESSION['create_new']=OCIResult($q,"CREATE_NEW");
			$_SESSION['rep_stat']=OCIResult($q,"REP_STAT");
			$upd=OCIParse($c,"update sup_user set last_logon=sysdate where id='".$_SESSION['user_id']."'");
			OCIExecute($upd,OCI_DEFAULT); 
			//пишем лог
			$ins=OCIParse($c,"insert into sup_login_log (datetime,ip,user_id,fio,login,password,result) 
			values (sysdate,'".$_SERVER['REMOTE_ADDR']."','".OCIResult($q,"ID")."','".OCIResult($q,"FIO")."','".$User."','".$Pass."','OK')");	
			OCIExecute($ins,OCI_DEFAULT); 
			//
			OCICommit($c);			
			/*echo "<script>location.reload('tex.php');</script>";*/
			if(isset($ticketId) and $ticketId<>'') 
				echo "<script>document.location='tex.php?ticketId=".$ticketId."';</script>";
			else	
				echo "<script>document.location='tex.php';</script>";
			exit();
		}
		else {
			//пишем лог
			$ins=OCIParse($c,"insert into sup_login_log (datetime,ip,user_id,fio,login,password,result) 
			values (sysdate,'".$_SERVER['REMOTE_ADDR']."','','','".$User."','".$Pass."','WRONG_PASS')");	
			OCIExecute($ins,OCI_DEFAULT); 
			OCICommit($c);			
			//
			session_destroy();
			echo "<font color=red><b>Не верное имя или пароль!</b></font>";
		}
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<script>
function add_options(obj,opt_id,opt_val,opt_selected) {
	len=obj.options.length;
	obj.options[len] = new Option(opt_val,opt_id);
	if(opt_selected=='selected') obj.options[len].selected=true;		
}
function add_optgroup(obj,name) {
	var optgroup = document.createElement("optgroup");
	optgroup.setAttribute("label", name);
	obj.appendChild(optgroup);
}
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
function open_edit(base_id,texnari_id,sid) {
	win=window.open("tex_edit.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid,"edit_tex","width=550, height=700, toolbar=no, scrollbars=yes, resizable=yes, status=yes, left=1,top=1");
	win.focus();
}
function open_new(sid) {
	win=window.open("new_order.php?sid="+sid,"edit_tex","width=550, height=700, toolbar=no, scrollbars=yes, resizable=yes, status=yes, left=1,top=1");
	win.focus();
}
function fn_find_id() {
	if(document.all.find_id.value.length==0 || document.all.find_id.value.length>=3) setTimeout('document.all.ok.click()',3000);
}
window.onresize = function() {
	head_width();
}
function head_width() {
	for(i=0; i<document.all.tbl.rows[0].cells.length; i++) {
		alert(document.all.tbl.rows[0].cells[i].clientLeft);
		document.all.tbl.rows[0].columns[i].width = document.all.tbl_head.rows[0].cells[i].clientWidth-(document.all.tbl_head.rows[0].cells[i].clientLeft*2);

		//document.all.tbl_head.rows[0].cells[i].width = document.all.tbl.rows[0].cells[i].clientWidth-(document.all.tbl.rows[0].cells[i].clientLeft*2);
	}
}
</script>
<body leftmargin="3" topmargin="3">
<?php

//форма логина
if(!isset($_SESSION['auth'])) {
	
	//rawurlencode(iconv("UTF-8","WINDOWS-1251",$subj));
	
	$subj="Запрос на регистрацию в техподдержке";
	$body="Для получения доступа к техподдержке заполните анкету.
	 
Фамилия: 
Имя: 
Местоположение (где Вы работаете): 
Отдел: 
Должность: 
Мобильный телефон для СМС уведомлений: 
Контактный(е) телефон(ы) для обратной связи. 
Рабочий (с добавочным): 
Мобильный (если хотите, что бы инженеры звонили на него): 
email:";

	$body=rawurlencode(iconv("WINDOWS-1251","WINDOWS-1251",$body));
	$subj=rawurlencode(iconv("WINDOWS-1251","WINDOWS-1251",$subj));
	
	echo "<form method='POST'>
	<input type=hidden name=ticketId value='".$ticketId."'>
<div align='center'><center>
<h1>Техническая поддержка</h1>
<font color=red>если у Вас нет достпа к техподдержке, заполните анкету по</font> 
<a href='reg_user_request.php'>этой ссылке</a>

 </center></div><div align='center'><center><table border='0' width='778' 
 cellspacing='0' cellpadding='0' height='137'>
   <tr>
      <td width='20%' height='25'></td>
      <td width='20%' height='25'><div align='right'><p><font color='#00000'><strong>Пользователь</strong></font></td>
      <td width='20%' align='center' height='25'><input type='text' name='User' value='".(isset($_COOKIE['login'])?$_COOKIE['login']:'')."' size='20'></td>
      <td width='20%' height='25'></td>
      <td width='20%' height='25'></td>
    </tr>
    <tr>
      <td width='20%' height='25'></td>
      <td width='20%' height='25'><div align='right'><p><font color='#00000'><strong>Пароль</strong></font></td>
      <td width='20%' align='center' height='25'><input type='password' name='Pass' value='".(isset($_COOKIE['pass'])?$_COOKIE['pass']:'')."' size='20'></td>
      <td width='20%' height='25'></td>
      <td width='20%' height='25'><div align='center'></div></td>
    </tr>
	<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".(isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''?' checked':'')."> запомнить пароль</td></tr>
    <tr align='center'>
      <td width='20%' height='65'></td>
      <td width='20%' height='65'></td>
      <td width='20%' align='center' height='65'><input type='submit' value='Вход'></td>
      <td width='20%' height='65'></td>
      <td width='20%' height='65'>&nbsp;<p></td>
    </tr>
  </table>";

//echo "<b>COOKIE</b><br>";
//extract($_COOKIE);
//foreach($_COOKIE as $key=>$val) {
//echo "$key - $val <br>";
//}
//echo "<hr>";
	exit();  
}
//
echo "<form method=get>";
echo "<input type=hidden name=ticketId value='".$ticketId."'>";
//смена группы. отбнуляем фильтры
if(isset($ch_grp)) {
unset($klinika_id);
unset($trbl_id);
unset($texnari_id);
unset($kto_id);
}
//

//описание переменных (если не выбран фильтр)
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if (!isset($klinika_id)) $klinika_id='';
if (!isset($trbl_id)) $trbl_id='';
if (!isset($texnari_id) and $_SESSION['solution']=='y' and $_SESSION['create_new']<>'y') $texnari_id=$_SESSION['user_id']; elseif(!isset($texnari_id)) $texnari_id='';
if (!isset($kto_id) and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']=='y') $kto_id=$_SESSION['user_id']; 
elseif (!isset($kto_id)) $kto_id='';
if (!isset($lt_grp_id)) $lt_grp_id=$_SESSION['lt_grp_id']; 
if (!isset($ok) and $_SESSION['eval']=='y' and  $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['create_new']<>'y') $show_closed='';

$lt_grp_arr=array();
$loc_grp_arr=array();
$location_arr=array();
$kto_arr=array();
$trbl_arr=array();
$texnari_arr=array();
$trbl_grp_ids=array();

$q_where='';
$q_from='';
//

//фильтр административных ограничений
if ($_SESSION['lt_grp_id']<>'' and ($_SESSION['look']<>'' or $_SESSION['solution']<>'' or $_SESSION['redirect']<>'' or $_SESSION['eval']<>'' or $_SESSION['admin']<>'' or $_SESSION['create_new']<>'')) {
	//Список выбора групп
	if($_SESSION['lt_grp_id']==0) {
		$q=OCIParse($c,"select id,name from sup_lt_group slg
		where id<>0 and type='common'
		order by name");
		OCIExecute($q,OCI_DEFAULT);
		while (OCIFetch($q)) {
			$lt_grp_arr[OCIResult($q,"ID")]=OCIResult($q,"NAME");
		}
		if(count($lt_grp_arr)==1) $no_grp='';
	}
	else {
		$q=OCIParse($c,"select id,name from sup_lt_group slg
		where id='".$lt_grp_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		$lt_grp_arr[OCIResult($q,"ID")]=OCIResult($q,"NAME");
		$no_grp='';
	}
	//	
	//Только создатель
	if($_SESSION['look']<>'y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_only='';
		$no_kto='';
		$kto_id=$_SESSION['user_id'];
		$kto_arr[$_SESSION['user_id']]=$_SESSION['fio'];
		$texnari_id='';
	//
	}
	//Создатель+обозреватель
	elseif ($_SESSION['look']=='y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']=='y') {
		$creator_look='';
		//if($kto_id=='') $kto_id='auth_only'; //если раскомментировать, то заявки от анонимов не увидит создатель+обозреватель
	}
	//

	if($_SESSION['solution']=='y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['look']<>'y' and $_SESSION['create_new']<>'y') {
		$no_texn='';
		$texnari_arr[$_SESSION['user_id']]=$_SESSION['fio'];
		$texnari_id=$_SESSION['user_id'];
	}
	
	//
}
else {echo "ОШИБКА НАЗНАЧЕНИЯ ПРАВ ДОСТУПА"; echo "| <a href=tex.php?exit><font color=red>выход</font></a>"; exit();}
//
if(isset($ticketId) and $ticketId<>'') { //если сюда перешли по сслке из письма и меется номер заявки, то открываем ее
	echo "<script>open_edit('".$ticketId."','".$_SESSION['user_id']."','".$sid."');</script>";
	$ticketId='';
	echo "<script>document.location.href=(document.location.pathname);</script>";
}
echo "<table align=center><tr><td>";
echo "<table width=100%><tr><td align=left><font size=3>";
echo "<nobr>Пользователь: <b>".$_SESSION['fio'].". </b></nobr>"; 
if(isset($no_grp)) {
	echo "<nobr>Группа: <b>".$lt_grp_arr[$_SESSION['lt_grp_id']].". </b></nobr>";
}
else {
	echo " <nobr>группа: <select name=lt_grp_id onchange=ch_grp.click()></nobr>";
	echo "<option value='0' style='color:green'>ВСЕ</option>";
	foreach($lt_grp_arr as $key => $val) {
		echo "<option value='".$key."'";
		if($key==$lt_grp_id) echo " selected";
		echo ">".$lt_grp_arr[$key]."</option>";
	}
echo "</select>";
echo "<script>document.all.lt_grp_id.disabled=true;</script>";
}
echo "</font></td><td align=right>";
if($_SESSION['create_new']=='y') echo "<a style='cursor:pointer' onclick='javascript:open_new(\"".$sid."\")'><font color=green><font size=2><b>создать заявку</b></font></font></a> | ";
echo ".xls:<a href=tex_export.php?week>(нед)</a>|<a href=tex_export.php?month>(мес)</a>|<a href=tex_export.php?year>(год)</a> | ";
if($_SESSION['rep_stat']=='y') echo "<a href=statistic.php>статистика</a> | ";
echo "<a href=adm_pwd.php>сменить пароль</a> | ";
if($_SESSION['admin']=='y') echo "<a href=adm_grp.php>админ</a> | "; 
echo "<a href=tex.php?exit><font color=red>выход</font></a></td></tr></table>";

if ($_SESSION['lt_grp_id']=='' or ($_SESSION['look']<>'y' and $_SESSION['solution']<>'y' and $_SESSION['redirect']<>'y' and $_SESSION['eval']<>'y' and $_SESSION['create_new']<>'y')) exit();
//

if($lt_grp_id<>0) {//ограничение по группе объектов-проблем
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
	
	$q_where.=" and k.id=slt.location_id and tt.id=slt.trbl_id ";
	
	$q_where.=" and ((slt.lt_grp_id='".$lt_grp_id."' ";
	if($i==1) $q_where.=" 
		and (b.trbl_grp_id='".$trbl_grp_ids[1]."' or b.trbl_grp_id is null)";
	elseif($i>1) $q_where.=" 
		and (b.trbl_grp_id in (".implode(',',$trbl_grp_ids).") or b.trbl_grp_id is null)";
	//всегда вижу заявки, которые на меня переадресованы
	$q_where.=") or (b.texnari_id='".$_SESSION['user_id']."'))";
		
	
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

//фильтр выбора 
if ($find_id<>"") $q_where.=" and b.id like '".$find_id."%' "; else {
	if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
	if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
	if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
	if ($trbl_id<>"") $q_where.=" and tt.id='".$trbl_id."' ";
	if ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
	if ($kto_id=="not_auth") $q_where.=" and b.kto_id is null "; elseif ($kto_id=="auth_only") $q_where.=" and b.kto_id is not null "; elseif ($kto_id<>"") $q_where.=" and b.kto_id='".$kto_id."' ";
	if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
}
//


echo "Поиск по номеру заявки: <input type=text name=find_id value='".$find_id."' onkeyup=fn_find_id(); onpaste=fn_find_id();>";

$q=OCIParse($c,"select to_char(max(last_change),'DD.MM.YYYY HH24:MI:SS') date_last_change from sup_base");
OCIExecute($q, OCI_DEFAULT);
OCIFetch($q);
echo "<input type=hidden name='date_last_change' value='".OCIResult($q,"DATE_LAST_CHANGE")."'>";

echo "<table align=center bgcolor=black cellspacing=1 cellpadding=1 width='auto'><tr>
<th bgcolor=white valign=top colspan=2>Дата поступления заявки<br><nobr>
c <input type=text value='"; if (isset($start_date)) echo $start_date; echo "' size=7 name=start_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date)) echo $end_date; echo "' size=7 name=end_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date);return false; HIDEFOCUS' onchange=ok.click()>";
echo "</nobr></th>";
echo "<script>document.all.start_date.disabled=true;document.all.end_date.disabled=true;</script>";
echo "<th bgcolor=white valign=top width=150>Объект<br>";

echo "<select style='width:100%' name=klinika_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
echo "</select>";
echo "</th>";
echo "<script>document.all.klinika_id.disabled=true;</script>";

if(!isset($no_kto)) {
	echo "<th bgcolor=white valign=top width=150>Кто обратился";
	echo "<br><select style='width:100%' name=kto_id onchange=ok.click()>";
	//if (!isset($creator_only) and !isset($creator_look)) {//если раскомментировать, то заявки от анонимов не увидит создатель+обозреватель 
	if (!isset($creator_only)) {//показывать заявки от неавторизованных пользователей всем, кроме только создателей
		echo "<option value='' style='color:green'>ВСЕ</option>";
	}
	if (!isset($creator_only)) {
		echo "<option value='auth_only' style='color:green'"; if($kto_id=='auth_only') echo " selected"; echo ">Авторизованные</option>";
	}
	//if (!isset($creator_only) and !isset($creator_look)) { //если раскомментировать, то заявки от анонимов не увидит создатель+обозреватель 
	if (!isset($creator_only)) {//показывать заявки от неавторизованных пользователей всем, кроме только создателей 
		echo "<option value='not_auth' style='color:red'"; if($kto_id=='not_auth') echo " selected"; echo ">Не авторизованные</option>";
	}
	if ($_SESSION['create_new']=='y') {
		echo "<option value='".$_SESSION['user_id']."' style='color:blue'"; if($kto_id==$_SESSION['user_id']) echo " selected"; echo ">Только мои</option>";
	}
	echo "</select>";
	echo "</th>";
	echo "<script>document.all.kto_id.disabled=true;</script>";
}

echo "<th bgcolor=white valign=top width=150>Тип проблемы<br>";

echo "<select style='width:280px' name=trbl_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
echo "</select>";
echo "</th>";
echo "<script>document.all.trbl_id.disabled=true;</script>";

if(!isset($no_texn)) {
	echo "<th bgcolor=white valign=top width=150>Кто занимается";
		echo "<br><select style='width:100%' name=texnari_id onchange=ok.click()>";
		echo "<option value='' style='color:green'>ВСЕ</option>";
		if ($_SESSION['solution']=='y') {
			echo "<option value='".$_SESSION['user_id']."' style='color:blue'"; if($texnari_id==$_SESSION['user_id']) echo " selected"; echo ">Мои и новые</option>";
		}		
		echo "</select>";
	echo "</th>";
echo "<script>document.all.texnari_id.disabled=true;</script>";
}

echo "<th bgcolor=white valign=top align=center width=65>Статус<br><nobr>(закр.<input type=checkbox ";
if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()>)</nobr></th>";
echo "<script>document.all.show_closed.disabled=true;</script>";
echo "<th bgcolor=white valign=top align=center width=65>Длит.<br>решен.</th>
<th bgcolor=white valign=top align=center width=45>Оцен-<br>ка</th>";
echo "</tr>";

$q_text1="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI') date_in_call,
       k.name,
       k.id location_id,
	   slg.name loc_grp_name,
	   t.fio,
	   t.id texnari_id,
       b.kto,
	   b.kto_id,
       b.u_kogo,
       b.oper_comment,
	   b.trbl_grp_id,
       case
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then
          'Открыта'
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then
          'В работе'
         when b.date_close is null and b.ready_to_close is not null then
		  'Гот.к пров.'
		 when b.date_close is not null then
          'Закрыта'
       end status,
       case  

         when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then
          'blue'
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then
          'green'
         when b.date_close is null and b.ready_to_close is not null then
		  '#001000'
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
	   b.cdpn,
	   b.dublikat,
	   b.krivie_ruki
	   ";
$q_text2="from sup_base b, sup_klinika k, sup_user t, sup_trbl_alloc ta, sup_trbl_type tt, sup_klinika_phones ph, sup_location_group slg".$q_from."
 where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.id=ta.base_id(+)
   and ta.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   and slg.id=k.location_grp_id
   ".$q_where."
   ";
$q_text3="order by d";

$q_text=$q_text1.$q_text2.$q_text3;
$_SESSION['q_text2']=$q_text2; //эта переменная нужна для автоматического олбновления окна с заявками

$q_trbl=OCIParse($c,"select stt.id,stt.name, decode(sb.trbl_grp_id,stt.trbl_grp_id,'y',null) actual
from sup_base sb, sup_trbl_alloc sta,sup_trbl_type stt
where sb.id=:base_id
and sta.base_id=sb.id and stt.id=sta.trbl_type_id
order by stt.name");

$q=OCIParse($c,$q_text);

$rownum=0;
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	$rownum++;
	//собираем список локаций
	
	
	//if(!isset($location_arr[OCIResult($q,"LOCATION_ID")])) $location_arr[OCIResult($q,"LOCATION_ID")]=OCIResult($q,"NAME");
	
	if(!isset($loc_grp_arr[OCIResult($q,"LOC_GRP_NAME")][OCIResult($q,"LOCATION_ID")])) 
		$loc_grp_arr[OCIResult($q,"LOC_GRP_NAME")][OCIResult($q,"LOCATION_ID")]=OCIResult($q,"NAME");
	
	//собираем список заявителей
	if(OCIResult($q,"KTO_ID")<>'' and !isset($no_kto) and (OCIResult($q,"KTO_ID")<>$_SESSION['user_id'] or $_SESSION['create_new']<>'y')) {
		if(!isset($kto_arr[OCIResult($q,"KTO_ID")])) $kto_arr[OCIResult($q,"KTO_ID")]=OCIResult($q,"KTO");
	}
	//собираем список технарей
	if(OCIResult($q,"TEXNARI_ID")<>'' and !isset($no_texn) and (OCIResult($q,"TEXNARI_ID")<>$_SESSION['user_id'] or $_SESSION['solution']<>'y')) {
		if(!isset($tehnari_arr[OCIResult($q,"TEXNARI_ID")])) $texnari_arr[OCIResult($q,"TEXNARI_ID")]=OCIResult($q,"FIO");
	}	
	echo "<tr";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " title='Дубликат'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " title='Ошибка'";
	echo " style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='javascript:open_edit(\"".OCIResult($q,"ID")."\",\"".OCIResult($q,"TEXNARI_ID")."\",\"".$sid."\")'>
<td bgcolor=white valign=top align=center width=30";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	else if(OCIResult($q,"CDPN")=='') echo " title='Нет АОНа'"; 
	else if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номмером клиники!'"; 
	else echo " style='color:green'";
	echo ">".OCIResult($q,"ID")."</td>
<td bgcolor=white valign=top align=center width=120"; 
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo">".OCIResult($q,"DATE_IN_CALL")."</td>
<td bgcolor=white valign=top valign=top";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo ">".OCIResult($q,"NAME")."</td>";

	if(!isset($no_kto)) {
		echo "<td bgcolor=white valign=top valign=top";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
		echo ">".OCIResult($q,"KTO")."</td>";
	}
	echo "<td bgcolor=white valign=top valign=top";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo " title='У кого не работает: ".OCIResult($q,"U_KOGO")."
Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>";
	$base_id=OCIResult($q,"ID");
	OCIBindByName($q_trbl,":base_id",$base_id);
	OCIExecute($q_trbl,OCI_DEFAULT);
		while (OCIFetch($q_trbl)) {
			//собираем список проблем
			if(!isset($trbl_arr[OCIResult($q_trbl,"ID")])) $trbl_arr[OCIResult($q_trbl,"ID")]=OCIResult($q_trbl,"NAME");
			//
			if(OCIResult($q_trbl,"ACTUAL")=='y') echo "<font>";
			else echo "<font color=gray>";
			echo OCIResult($q_trbl,"NAME")."<br>";
		}
	echo "</td>";
	if(!isset($no_texn)) {
		echo "<td bgcolor=white valign=top valign=top"; 
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
		echo ">".OCIResult($q,"FIO")."</td>";
	}
	echo "<td bgcolor=white valign=top align=center";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo "><font color='".OCIResult($q,"COLOR")."'>".OCIResult($q,"STATUS")."</font></td>
	<td bgcolor=white valign=top align=center";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo ">".OCIResult($q,"DUR")."</td>
	<td bgcolor=white valign=top align=center";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
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
$_SESSION['q_count']=$rownum; //эта переменная нужна для автоматического олбновления окна с заявками
echo "кол-во строк: <b>".$rownum."</b>";

echo '<input type="submit" style="display:none" name=ok value="">
<input type="submit" style="display:none" name=ch_grp value="">
</form>';

echo "<script>";
ksort($loc_grp_arr);
foreach ($loc_grp_arr as $grp=>$location_arr) {
	asort($loc_grp_arr[$grp]);
	echo "add_optgroup(document.all.klinika_id,'".$grp."');";
	foreach($loc_grp_arr[$grp] as $key => $val) {
		if($key==$klinika_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.klinika_id,'".$key."','".$val."','".$selected."');";
	}
}
echo "document.all.klinika_id.disabled=false;";
/*asort($location_arr);
foreach($location_arr as $key => $val) {
	if($key==$klinika_id) $selected='selected'; else $selected='';
	echo "add_options(document.all.klinika_id,'".$key."','".$val."','".$selected."');";
}*/
if(!isset($no_kto)) {
	asort($kto_arr);
	foreach($kto_arr as $key => $val) {
		if($key==$kto_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.kto_id,'".$key."','".$val."','".$selected."');";
	}
echo "document.all.kto_id.disabled=false;";
}
asort($trbl_arr);
foreach($trbl_arr as $key => $val) {
	if($key==$trbl_id) $selected='selected'; else $selected='';
	echo "add_options(document.all.trbl_id,'".$key."','".$val."','".$selected."');";
}
echo "document.all.trbl_id.disabled=false;";
if(!isset($no_texn)) {
	asort($texnari_arr);
	foreach($texnari_arr as $key => $val) {
		if($key==$texnari_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.texnari_id,'".$key."','".$val."','".$selected."');";
	}
echo "document.all.texnari_id.disabled=false;";
}
if(!isset($no_grp)) echo "document.all.lt_grp_id.disabled=false;";
echo "document.all.start_date.disabled=false;document.all.end_date.disabled=false;document.all.show_closed.disabled=false;";
echo "</script>";
?>
<script>head_width();</script>
</body>
</html>
<iframe name=check_new src="tex_check_new.php" style="display:none"></iframe>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_tex.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
