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
else $find_id=trim($find_id);
if (isset($exit)) {
//setcookie('login');
//setcookie('pass');
session_destroy();
header('Location:/');
}

include("sup/sup_conn_string");

	if(isset($User) and isset($Pass)) {
	$q=OCIParse($c,"select u.id, u.fio, u.coment, u.admin, u.registrar,  
max(a.look) look, max(a.solution) solution, max(a.redirect) redirect, max(a.eval) eval, max(a.deny_close) deny_close, max(a.create_new) create_new, max(a.rep_stat) rep_stat
from SUP_USER u, SUP_USER_LT_ALLOC a
where login='".$User."' and password='".$Pass."' and login is not null and deleted is null
and a.user_id(+)=u.id
group by u.id, u.fio, u.coment, u.admin, u.registrar");
	OCIExecute($q,OCI_DEFAULT);
		if (OCIFetch($q)) {
			setcookie('login',$User,mktime(0,0,0,1,1,2030));
			if(isset($save_pass)) {
				setcookie('pass',$Pass,mktime(0,0,0,1,1,2030)); //если выбрана опция сохранения пароля, устанавливаем кукес с паролем
			}
			else {
				setcookie('pass');
				unset($_COOKIE['pass']); //и из текущего сеанса из сеанса надо удалять потому, что куккес уже прочтен и сотался в данном сеансе
			}
			$_SESSION['auth']='y';
			$_SESSION['user_id']=OCIResult($q,"ID");
			$_SESSION['fio']=OCIResult($q,"FIO");
			$_SESSION['coment']=OCIResult($q,"COMENT");
			$_SESSION['admin']=OCIResult($q,"ADMIN");
			$_SESSION['registrar']=OCIResult($q,"REGISTRAR");

			$_SESSION['max_look']=OCIResult($q,"LOOK");
			$_SESSION['max_solution']=OCIResult($q,"SOLUTION");
			$_SESSION['max_redirect']=OCIResult($q,"REDIRECT");
			$_SESSION['max_deny_close']=OCIResult($q,"DENY_CLOSE");
			$_SESSION['max_eval']=OCIResult($q,"EVAL");
			$_SESSION['max_create_new']=OCIResult($q,"CREATE_NEW");
			$_SESSION['max_rep_stat']=OCIResult($q,"REP_STAT");

			$_SESSION['lt_grp_id']=''; 
			
			$upd=OCIParse($c,"update sup_user set last_logon=sysdate where id='".$_SESSION['user_id']."'");
			OCIExecute($upd,OCI_DEFAULT); 
			//пишем лог
			$ins=OCIParse($c,"insert into sup_login_log (datetime,ip,user_id,fio,login,password,result) 
			values (sysdate,'".$_SERVER['REMOTE_ADDR']."','".OCIResult($q,"ID")."','".OCIResult($q,"FIO")."','".$User."','".$Pass."','OK')");	
			OCIExecute($ins,OCI_DEFAULT); 
			//
			OCICommit($c);			
			/*echo "<script>location.reload('/');</script>";*/
			if(isset($ticketId) and $ticketId<>'') 
				echo "<script>document.location='/?ticketId=".$ticketId."';</script>";
			else	
				echo "<script>document.location='/';</script>";
			exit();
		}
		else {
			//пишем лог
			$ins=OCIParse($c,"insert into sup_login_log (datetime,ip,user_id,fio,login,password,result) 
			values (sysdate,'".$_SERVER['REMOTE_ADDR']."','','','".$User."','".$Pass."','WRONG_PASS')");	
			OCIExecute($ins,OCI_DEFAULT); 
			OCICommit($c);			
			//
			
			session_destroy(); //удаляем все сессионные переменные 
			//setcookie('login'); //удаляем кукес с логином из браузера
			//unset($_COOKIE['login']); //и из текущего сеанса
			setcookie('pass'); //удаляем кукес с паролем из браузера
			unset($_COOKIE['pass']); //и из текущего сеанса			
			$err="Не верное имя или пароль!";	
			//echo "<font color=red><b>Не верное имя или пароль!</b></font>";
		}
	}

?>
<html>
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
var sel_color='#66fffe';
var unsel_color='white';
var clicked_color='#cccccc';
var clicked_sel_color='#66ccff';
function sel_row(row) {
	//sel_color='#66FFFF';
	for(i=0; i<row.cells.length; i++) {
		if(row.cells[i].bgColor==sel_color) {//если уже выделена, но не нажата, то ничего не делаем
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата, то выделяем
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата, то ничего не делаем
			row.cells[i].bgColor=clicked_sel_color;
		}
		else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата, то красим в нажато-выделенный цвет
			row.cells[i].bgColor=clicked_sel_color;
		}
	}
}
function unsel_row(row) {
	//unsel_color='white';
	for(i=0; i<row.cells.length; i++) {
		//alert(row.cells[i].bgColor+" - "+unsel_color);
		if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата то ничего не делаем
			row.cells[i].bgColor=unsel_color;
		}		
		else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата то ничего не делаем
			row.cells[i].bgColor=clicked_color;
		}	
		else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата то красим в нажато-невыделенный цвет
			row.cells[i].bgColor=clicked_color;
		}
		else if(row.cells[i].bgColor==sel_color) {//если выделена и не нажата то снимаем выделение*/
			row.cells[i].bgColor=unsel_color;
		}
	}
}
function click_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата
			row.cells[i].bgColor=clicked_color;
		}
		if(row.cells[i].bgColor==sel_color) {//если не выделена и не нажата
			row.cells[i].bgColor=clicked_sel_color;
		}
	}
}
function unclick_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=unsel_color;
	}
}
function sel_click_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_sel_color;
	}
}
function unsel_click_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_color;
	}
}
function ch_show_closed() {
	if (document.all.show_closed.checked==true) {location.reload('/?show_closed=1');}
	else {location.reload('/');}
}
function ch_show_delayed() {
	if (document.all.show_delayed.checked==true) {location.reload('/?show_delayed=1');}
	else {location.reload('/');}
}
function open_edit(base_id,texnari_id,sid) {
	win=window.open("order.edit.form.php?base_id="+base_id+"&texnari_id="+texnari_id+"&sid="+sid,"sup_order_"+base_id,"width=550, height=700, toolbar=no, location=no, scrollbars=yes, resizable=yes, status=yes");
	win.focus();
}
function open_new(sid) {
	win=window.open("new_order.php?sid="+sid,"sup_order_new","width=550, height=700, toolbar=no, scrollbars=yes, resizable=yes, status=yes");
	win.focus();
}
var t;
function fn_find_id() {
	clearTimeout(t);
	if(document.all.find_id.value.length==0 || document.all.find_id.value.length>=3) t=setTimeout('document.all.ok.click()',3000);
}
/*window.onresize = function() {
	head_width();
}
function head_width() {
	head_row=document.getElementById('tbl_head').rows[0];
	content_row=document.getElementById('tbl').rows[0];
	for(i=0; i<document.all.tbl.rows[0].cells.length; i++) {
		//alert(head_row.cells[i].clientWidth);
		if(head_row.cells[i].offsetWidth>content_row.cells[i].offsetWidth) {
			content_row.cells[i].width=head_row.cells[i].offsetWidth-(head_row.cells[i].clientLeft*2);
		}
		else {
			head_row.cells[i].width = content_row.cells[i].offsetWidth-(content_row.cells[i].clientLeft*2);
		}
	}
}*/
</script>

<body class="body_marign">
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
	
	echo "<form method='POST'>";
	echo "<input type=hidden name=ticketId value='".$ticketId."'>
<div align='center'><center>
<h1>Техническая поддержка</h1>

 </center></div><div align='center'><center><table border='0' width='100%' 
 cellspacing='0' cellpadding='0' height='137'>

    <tr align='center'>
      <td width=20%></td>
      <td width=20%></td>
      <td width=150 align='center' width=60><font color='#00000'><strong>Вход</strong></font></td>
      <td width=20%></td>
      <td width=20%>&nbsp;</td>
    </tr> 
 
   <tr>
      <td></td>
      <td align='right'><font color='#00000'><strong>Пользователь: </strong></font></td>
      <td align='center'><input autocomplete='off' type='text' name='User' value='".(isset($_COOKIE['login'])?$_COOKIE['login']:"")."' size='20'></td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td><div align='right'><p><font color='#00000'><strong>Пароль: </strong></font></td>
      <td align='center'><input autocomplete='off' type='password' name='Pass' value='".(isset($_COOKIE['pass'])?$_COOKIE['pass']:'')."' size='20'></td>
      <td></td>
      <td><div align='center'></div></td>
    </tr>";
	//<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".(isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''?' checked':'')."> запомнить пароль</td></tr>
	//если лоигн и пароль уже сохранены в кукесах, то галочка "запомнить пароль" остается влюченной
	//echo "<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".((isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''||isset($save_pass))?' checked':'')."> запомнить пароль</td></tr>
	
	//если лоигн и пароль уже сохранены в кукесах, то галочка "запомнить пароль" остается включенной
	echo "<tr><td colspan=5 align='center'><input type=checkbox name='save_pass' ".((isset($_COOKIE['pass'])&&$_COOKIE['pass']<>''||isset($save_pass))?' checked':'')."> запомнить пароль";
	if(isset($err)) echo "<br><font color=red><b>".$err."</b></font>";
	echo "</td></tr>
    <tr align='center'>
      <td height='50'></td>
      <td height='50'></td>
      <td align='center' height='50'><input type='submit' value='Войти'></td>
      <td height='50'></td>
      <td height='50'>&nbsp;<p></td>
    </tr>
    <tr align='center'>
      <td></td>
      
      <td align='center' colspan=3><font color=red>Если у Вас нет логина и пароля, зарегистрируйтесь. </font><a href='user.reg.request.form.php'>Регистрация</a></td>
      
      <td>&nbsp;<p></td>
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
$_SESSION['export_where']='';

//хедер-футер. ХЕДЕР
echo "<table class=content_table align=center width=100%><tr class=header_tr><td>";

echo "<form method=get><input type=hidden name=refresh value=y>";

echo "<input type=hidden name=ticketId value='".$ticketId."'>";
//смена группы. отбнуляем фильтры
if(isset($ch_grp)) {
unset($klinika_id);
unset($trbl_id);
unset($texnari_id);
unset($kto_id);
}
//

$lt_grp_arr=array();
$lt_grp_arr_all=array();
$lt_grp_arr_new=array();

//Список выбора групп

$q=OCIParse($c,"select g.id,g.name, a.solution, a.redirect, a.look, a.eval
--,case when a.solution is NULL and a.redirect is NULL and a.look is NULL and a.eval='y' then 'y' end eval_only
from SUP_USER_LT_ALLOC a, sup_lt_group g
where a.user_id=".$_SESSION['user_id']." and g.id=a.lt_group_id and g.type='common'
and (a.solution='y' or a.redirect='y' or a.look='y' or a.eval='y' or a.create_new='y')");
OCIExecute($q,OCI_DEFAULT);
$i=0; while (OCIFetch($q)) {$i++;
	$lt_grp_arr[OCIResult($q,"ID")]=OCIResult($q,"NAME"); //список всех групп
	if(OCIResult($q,"LOOK")=='y') $lt_grp_arr_all[]=OCIResult($q,"ID"); //1.1.2. список групп, по которым отбражаются все заявки
	if(OCIResult($q,"SOLUTION")=='y' or OCIResult($q,"REDIRECT")=='y') $lt_grp_arr_new[]=OCIResult($q,"ID"); //1.1.3. список групп, по которым отбражаются открытые заявки
}

$all_grp='n';
if(isset($lt_grp_ids)) $_SESSION['lt_grp_ids']=$lt_grp_ids;
else if (!isset($lt_grp_ids) and isset($refresh)) {unset($_SESSION['lt_grp_ids']); $all_grp='y';}
else if (isset($_SESSION['lt_grp_ids'])) $lt_grp_ids=$_SESSION['lt_grp_ids'];

if((isset($lt_grp_ids) and count($lt_grp_ids)==count($lt_grp_arr)) or ($i==1) or !isset($lt_grp_ids)) $all_grp='y';


//описание переменных (если не выбран фильтр)
if (!isset($start_date)) $start_date=date('d.m.Y',mktime(0,0,0,date("m")-1,date("d"),date("Y")));
if (!isset($end_date)) $end_date=date('d.m.Y',mktime(0,0,0,date("m"),date("d"),date("Y")));
if (!isset($klinika_id)) $klinika_id='';
if (!isset($trbl_id)) $trbl_id='';
//if (!isset($texnari_id) and $_SESSION['max_solution']=='y' and $_SESSION['max_create_new']<>'y') $texnari_id=$_SESSION['user_id']; 
//else
if(!isset($texnari_id)) $texnari_id='';
//if (!isset($kto_id) and  $_SESSION['max_solution']<>'y' and $_SESSION['max_redirect']<>'y' and $_SESSION['max_create_new']=='y') $kto_id=$_SESSION['user_id']; 
//else
if (!isset($kto_id)) $kto_id='';

if (!isset($ok) and $_SESSION['max_eval']=='y' and  $_SESSION['max_solution']<>'y' and $_SESSION['max_redirect']<>'y' and $_SESSION['max_create_new']<>'y') {$show_closed=''; $show_delayed='';}

$loc_grp_arr=array();
$trbl_grp_arr=array();
$location_arr=array();
$kto_arr=array();
$trbl_arr=array();
$texnari_arr=array();
//

if(isset($ticketId) and $ticketId<>'') { //если сюда перешли по сслке из письма и меется номер заявки, то открываем ее
	echo "<script>open_edit('".$ticketId."','".$_SESSION['user_id']."','".$sid."');</script>";
	$ticketId='';
	echo "<script>document.location.href=(document.location.pathname);</script>";
}

echo "<table width=100% align=center><tr><td>";
echo "<table width=100%><tr><td align=left><font size=3>";

echo "Пользователь: <b>".$_SESSION['fio'].". </b>"; 
if(count($lt_grp_arr)>0) {
	if(count($lt_grp_arr)==1) {
		echo "</font></td><td align=left><font size=3>Группа: <b>";
		foreach($lt_grp_arr as $val) echo $val;
		$_SESSION['export_grp_name']=$val;
		echo "</font></a></b>";
	}
	else {
/*		echo " <nobr>группа: <select name=lt_grp_id onchange=ch_grp.click()></nobr>";
		echo "<option value='' style='color:green'>ВСЕ</option>";
		foreach($lt_grp_arr as $key => $val) {
			echo "<option value='".$key."'";
			if($key==$lt_grp_id) {echo " selected"; $selected='y'; $_SESSION['export_grp_name']=$lt_grp_arr[$key];}
			echo ">".$lt_grp_arr[$key]."</option>";
		}
		echo "</select>";
		if(!isset($selected)) {$lt_grp_id=''; $_SESSION['lt_grp_id']=''; $_SESSION['export_grp_name']='';}
		echo "<script>document.all.lt_grp_id.disabled=true;</script>";
	}
}*/
	echo "</font></td><td align=left><font size=3>Группа: ";

	echo "<div id=div_sel_grp style='position:absolute; background-color:white; border:1px solid; padding:5px; display:none'>";
	foreach($lt_grp_arr as $key => $val) {
	//if(count())
		
	echo "<input type=checkbox name='lt_grp_ids[".$key."]' value='".$key."'";
	if(isset($lt_grp_ids[$key]) or $all_grp=='y') {echo " checked";}
	echo ">".$lt_grp_arr[$key]."</input><br>";
	}
	echo "<hr><input type=button value='Выбрать' style='background-color:#33CC33' onclick=ch_grp.click()></input>";
	echo " <input type=button value='отмена' style='background-color:#CC3333' onclick='getElementById(\"div_sel_grp\").style.display=\"none\"'></input>";
	echo "</div>";
	
	echo "<b><a id=a_grp_name style='cursor:pointer' onclick='getElementById(\"div_sel_grp\").style.display=\"\"'>";
	//echo "<input type=checkbox name='lt_grp_ids[all]' value='all' ".(isset($lt_grp_ids['all'])?' checked':'').">ВСЕ</input><hr>";
	if($all_grp=='y') {$_SESSION['export_grp_name']=''; echo "ВСЕ";}
	else if (isset($lt_grp_ids)) {
		$_SESSION['export_grp_name']='';
		$i=0; foreach($lt_grp_ids as $key => $val) { $i++;
			if($i>1) $_SESSION['export_grp_name'].=', ';
			$_SESSION['export_grp_name'].=$lt_grp_arr[$key];
		} 
		if(count($lt_grp_ids)==1) {echo $_SESSION['export_grp_name'];}
		if(count($lt_grp_ids)>1) echo "Выбрано несколько групп";
	}
	echo "</font></a></b>";
	}
}
echo "</font></td><td align=right>";
if($_SESSION['max_create_new']=='y') echo "<a style='cursor:pointer' onclick='javascript:open_new(\"".$sid."\")'><font color=green><font size=2><b>создать заявку</b></font></font></a> | ";
echo ".xls:<a href=tex_export.php?week>(нед)</a>|<a href=tex_export.php?month>(мес)</a>|<a href=tex_export.php?year>(год)</a> | ";
if($_SESSION['max_rep_stat']=='y') echo "<a href=statistic.php>статистика</a> | ";
echo "<a href=adm_pwd.php>сменить пароль</a> | ";
if($_SESSION['admin']=='y') echo "<a href=adm.main.frame.php>админ</a> | "; 
echo "<a href=/?exit><font color=red>выход</font></a></td></tr></table>";

//if(count($lt_grp_arr)==0) exit();

$q_where="and (1=2 ".chr(13);

//1.1.2. отображать все заявки из групп, на которые есть привилегия LOOK
if(count($lt_grp_arr_all)>0) {
	$q_where.=" or slt.lt_grp_id in (".implode(",",$lt_grp_arr_all).") ".chr(13);
}
//1.1.3. отображать все не назначенные заявки, на которые есть привилегии SOLUTION или REDIRECT
if(count($lt_grp_arr_new)>0) {
	$q_where.=" or (b.texnari_id is NULL and slt.lt_grp_id in (".implode(",",$lt_grp_arr_new).")) ".chr(13);
}
//1.1.1. всегда вижу заявки, где я автор или иполнитель
$q_where.=" or (b.texnari_id='".$_SESSION['user_id']."') or b.kto_id='".$_SESSION['user_id']."')".chr(13);

$export_where=$q_where;
if ($all_grp<>'y' and isset($lt_grp_ids)) $export_where.=" and slt.lt_grp_id in (".implode(",",$lt_grp_ids).") ";

//фильтр выбора 
//поиск по ID отменяет все фильтры
//if ($find_id<>"") $q_where.=" and b.id like '".$find_id."%' "; 

$_SESSION['no_check']='n';
if ($find_id<>"") {
	$_SESSION['no_check']='y';
	
	$q_where.=" and (b.id like '".$find_id."%' 
 or upper(replace(b.kto,' ')) like '%'||upper(replace('".$find_id."',' '))||'%' 
 or upper(replace(b.u_kogo,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 or upper(replace(b.oper_comment,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 or upper(replace(t.fio,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 or upper(replace(k.name,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 or upper(replace(tt.name,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 
 or b.id in (
	select base_id from SUP_TEXNARI_HISTORY t
	where upper(replace(t.texnary_coment,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 ) 

 or b.id in (
	select base_id from SUP_FILES t
	where upper(replace(t.filename,' ')) like '%'||upper(replace('".$find_id."',' '))||'%'
 )  
 
 ) ";
}
else {
	//выбор группы отфильтровывает не закрытые заявки
	if ($all_grp<>'y' and isset($lt_grp_ids)) $q_where.=" and slt.lt_grp_id in (".implode(",",$lt_grp_ids).") ";
	//выбор группы НЕ отфильтровывает не закрытые заявки
	//if ($lt_grp_id<>"") $q_where.=" and (slt.lt_grp_id='".$lt_grp_id."' or b.date_close is null) ";
	if ($start_date<>"") $q_where.=" and (b.date_in_call>to_date('$start_date','DD.MM.YYYY') or b.date_close is null) ";
	if ($end_date<>"") $q_where.=" and (b.date_in_call<to_date('$end_date','DD.MM.YYYY')+1 or b.date_close is null) ";
	if ($klinika_id<>"") $q_where.=" and k.id='".$klinika_id."' ";
	if ($trbl_id<>"") $q_where.=" and b.trbl_type_id='".$trbl_id."' ";
	
	if($texnari_id=='my_new') $q_where.=" and (b.texnari_id='".$_SESSION['user_id']."' or b.texnari_id is null) ";
	elseif ($texnari_id<>"") $q_where.=" and (b.texnari_id='".$texnari_id."' or b.texnari_id is null) ";
	
	if ($kto_id=="not_auth") 
		$q_where.=" and b.kto_id is null "; 
	elseif ($kto_id=="auth_only") 
		$q_where.=" and b.kto_id is not null "; 
	elseif ($kto_id=="my_only") 
		$q_where.=" and b.kto_id='".$_SESSION['user_id']."'"; 
	elseif ($kto_id<>"") 
		$q_where.=" and b.kto_id='".$kto_id."' ";
	
	
	if (!isset($show_closed)) $q_where.=" and b.date_close is null ";
	if (!isset($show_delayed)) $q_where.=" and nvl(b.delay_to,sysdate)<=sysdate ";
}
$q_text3=$q_where;


//

echo "<nobr>Поиск: <input type=text name=find_id value='".$find_id."' onkeyup=fn_find_id(); onpaste=fn_find_id(); title='Введите не менее 3-х символов и подождите 3 секунды. Будет выполнен поиск совпадений по всем полям.'> | ";
echo "показать: <font color=red><b>закрытые</b></font> <input type=checkbox ";
if (isset($show_closed)) echo "checked "; echo "name=show_closed onclick=ok.click()> | ";

echo "<font color='#CC6633'><b>отложенные</b></font> <input type=checkbox ";
if (isset($show_delayed)) echo "checked "; echo "name=show_delayed onclick=ok.click()> | ";

echo "показывать текст заявки <input type=checkbox ";
if (isset($show_text)) echo "checked "; echo "name=show_text onclick=ok.click()> | ";

$q=OCIParse($c,"select to_char(max(last_change),'DD.MM.YYYY HH24:MI:SS') date_last_change from sup_base");
OCIExecute($q, OCI_DEFAULT);
OCIFetch($q);
echo "<input type=hidden name='date_last_change' value='".OCIResult($q,"DATE_LAST_CHANGE")."'>";

echo "</td></tr></table>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr class=content_tr><td><div class=content_div>";

echo "<table id=tbl_head align=center bgcolor=black cellspacing=1 cellpadding=1 width='auto'>";
//echo "<table align=center class='white_table' cellspacing=1 cellpadding=1 width='auto'>";
echo "<tr>
<th bgcolor=white valign=top colspan=2>Дата поступления заявки<br><nobr>
c <input type=text value='"; if (isset($start_date)) echo $start_date; echo "' size=7 name=start_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].start_date);return false; HIDEFOCUS' onchange=ok.click()> 
по <input type=text value='"; if (isset($end_date)) echo $end_date; echo "' size=7 name=end_date onclick='if(self.gfPop)gfPop.fPopCalendar(document.forms[0].end_date);return false; HIDEFOCUS' onchange=ok.click()>";
echo "<script>document.all.start_date.disabled=true;document.all.end_date.disabled=true;</script>";
echo "</nobr></th>";
echo "<th bgcolor=white valign=top width=150>Объект<br>";

echo "<select style='width:100%' name=klinika_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
echo "</select>";
echo "</th>";
echo "<script>document.all.klinika_id.disabled=true;</script>";

echo "<th bgcolor=white valign=top width=150>Кто обратился";
echo "<br><select style='width:100%' name=kto_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
echo "<option value='auth_only' style='color:green'"; if($kto_id=='auth_only') echo " selected"; echo ">Авторизованные</option>";
echo "<option value='not_auth' style='color:red'"; if($kto_id=='not_auth') echo " selected"; echo ">Не авторизованные</option>";
echo "<option value='my_only' style='color:blue'"; if($kto_id=='my_only') echo " selected"; echo ">Только мои</option>";

echo "</select>";
echo "</th>";
echo "<script>document.all.kto_id.disabled=true;</script>";

echo "<th bgcolor=white valign=top width=150>Тип проблемы<br>";

echo "<select style='width:280px' name=trbl_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
echo "</select>";
echo "</th>";
echo "<script>document.all.trbl_id.disabled=true;</script>";

echo "<th bgcolor=white valign=top width=150>Кто занимается";
echo "<br><select style='width:100%' name=texnari_id onchange=ok.click()>";
echo "<option value='' style='color:green'>ВСЕ</option>";
if ($_SESSION['max_solution']=='y') {
	echo "<option value='my_new' style='color:blue'"; if($texnari_id=='my_new') echo " selected"; echo ">Мои и новые</option>";
}
echo "</select>";
echo "</th>";
echo "<script>document.all.texnari_id.disabled=true;</script>";

echo "<th bgcolor=white valign=top align=center width=65>Статус<br>";
echo "</th>";
echo "<script>document.all.show_closed.disabled=true;</script>";
echo "<script>document.all.show_delayed.disabled=true;</script>";
echo "<th bgcolor=white valign=top align=center width=65>Длит.<br>решен.</th>
<th bgcolor=white valign=top align=center width=45>Оцен-<br>ка</th>";
echo "</tr>";
//echo "</table>";

$q_text1="select distinct b.id,
       b.date_in_call d,
	   to_char(b.date_in_call,'DD.MM.YYYY HH24:MI') date_in_call,
       k.name,
       k.id location_id,
	   slg.name loc_grp_name,
	   b.trbl_type_id,
	   tt.name trbl_name,
	   stg.name trbl_grp_name,
	   t.fio,
	   t.id texnari_id,
       b.kto,
	   b.kto_id,
       b.u_kogo,
       b.oper_comment,
	   nvl(to_char(b.in_work,'MISS'),0)+nvl(to_char(b.date_close,'MISS'),0)+nvl(to_char(b.ready_to_close,'MISS'),0)+nvl(to_char(b.delay_to,'MMDD'),0) checksum,
       case
         when b.delay_to>sysdate then 300 --Отложена
		 when b.date_close is null and b.ready_to_close is null and b.texnari_id is null then 100 --Открыта
         when b.date_close is null and b.ready_to_close is null and b.texnari_id is not null then 200 --В работе
         when b.date_close is null and b.ready_to_close is not null then 400 --Гот.к пров.
		 when b.date_close is not null then 500 --Закрыта
       end status_id,
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
$q_text2="from sup_base b, sup_klinika k, sup_user t, sup_trbl_type tt, sup_klinika_phones ph, sup_location_group slg, SUP_TRBL_GROUP stg,sup_lt slt 
	where b.klinika_id = k.id(+)
   and b.texnari_id = t.id(+)
   and b.trbl_type_id=tt.id(+)
   and b.cdpn = ph.phone(+)
   and slg.id=k.location_grp_id
   and stg.id=tt.trbl_grp_id
   and k.id=slt.location_id and tt.id=slt.trbl_id 
   ";
$q_text4="order by d";

$q_text=$q_text1.$q_text2.$q_text3.$q_text4;

//echo "<textarea>".$q_text."</textarea>";

$_SESSION['refresh_where']=$q_text2.$q_text3; //эта переменная нужна для автоматического олбновления окна с заявками
$_SESSION['export_where']=$export_where;

/*$q_trbl=OCIParse($c,"select stt.id,stt.name, decode(sb.trbl_grp_id,stt.trbl_grp_id,'y',null) actual
from sup_base sb, sup_trbl_alloc sta,sup_trbl_type stt
where sb.id=:base_id
and sta.base_id=sb.id and stt.id=sta.trbl_type_id
order by stt.name");*/

$q=OCIParse($c,$q_text);

if(isset($show_text)) {
	//файлы
	$q_files=OCIParse($c,"select id,filename from SUP_FILES where base_id=:base_id and tmp is null and hist_id is null order by filename");
}

//echo "<table id=tbl align=center bgcolor=black cellspacing=1 cellpadding=1 width='auto'>";

//статусы
$q_stat=OCIParse($c,"select  name, color from sup_status where id=:id");


$rownum=0;
$checksum=0;
OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	$tmp_base_id=OCIResult($q,"ID");
	$rownum++;
	$checksum+=OCIResult($q,"CHECKSUM");
	//статусы
	$status_id=OCIResult($q,"STATUS_ID");
	OCIBindByName($q_stat,":id",$status_id);
	OCIExecute($q_stat,OCI_DEFAULT);
	OCIFetch($q_stat);
	$status_name=OCIResult($q_stat,"NAME");
	$status_color=OCIResult($q_stat,"COLOR");
	
	//собираем список локаций
	if(!isset($loc_grp_arr[OCIResult($q,"LOC_GRP_NAME")][OCIResult($q,"LOCATION_ID")])) 
		$loc_grp_arr[OCIResult($q,"LOC_GRP_NAME")][OCIResult($q,"LOCATION_ID")]=OCIResult($q,"NAME");
	
	//собираем список заявителей
	//if(OCIResult($q,"KTO_ID")<>'' and !isset($no_kto) and (OCIResult($q,"KTO_ID")<>$_SESSION['user_id'] or $_SESSION['max_create_new']<>'y')) {
		if(!isset($kto_arr[OCIResult($q,"KTO_ID")]) and OCIResult($q,"KTO_ID")<>'') $kto_arr[OCIResult($q,"KTO_ID")]=OCIResult($q,"KTO");
	//}
	//собираем список технарей
	//if(OCIResult($q,"TEXNARI_ID")<>'' and !isset($no_texn) and (OCIResult($q,"TEXNARI_ID")<>$_SESSION['user_id'] or $_SESSION['max_solution']<>'y')) {
		if(!isset($tehnari_arr[OCIResult($q,"TEXNARI_ID")]) and OCIResult($q,"TEXNARI_ID")) $texnari_arr[OCIResult($q,"TEXNARI_ID")]=OCIResult($q,"FIO");
	//}	
	//собираем список проблем
	if(!isset($trbl_grp_arr[OCIResult($q,"TRBL_GRP_NAME")][OCIResult($q,"TRBL_TYPE_ID")])) 
		$trbl_grp_arr[OCIResult($q,"TRBL_GRP_NAME")][OCIResult($q,"TRBL_TYPE_ID")]=OCIResult($q,"TRBL_NAME");

	//if(!isset($trbl_arr[OCIResult($q,"TRBL_TYPE_ID")])) {
	//	$trbl_arr[OCIResult($q,"TRBL_TYPE_ID")]=OCIResult($q,"TRBL_NAME");
	//}	
	
	echo "<tr id='row_".OCIResult($q,"ID")."'";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " title='Дубликат'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " title='Ошибка'";
	echo " style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='javascript:open_edit(\"".OCIResult($q,"ID")."\",\"".OCIResult($q,"TEXNARI_ID")."\",\"".$sid."\")'>
<td bgcolor=white valign=top align=center width=30";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	else if(OCIResult($q,"CDPN")=='') echo " title='Нет АОНа'"; 
	else if(OCIResult($q,"PHONE")=='') echo " style='color:red' title='АОН заявки не совпадает с номмером клиники!'"; 
	else echo " style='color:green'";
	echo ">".OCIResult($q,"ID")."</td>";

	if(isset($show_text)) {

		echo "<td bgcolor=white style='white-space: normal'";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	

		echo " colspan=4>";
		echo "Дата: <b>".OCIResult($q,"DATE_IN_CALL")."</b><br>";
		echo "Где: <b>".OCIResult($q,"NAME")."</b><br>";
		echo "Тип: <b>".OCIResult($q,"TRBL_NAME")."</b><br>";
		echo "Кто: <b>".OCIResult($q,"KTO")."</b><br>";
		;
		echo "У кого: <b>".OCIResult($q,"U_KOGO")."</b><br>";
		echo "Описание: <b>".nl2br(htmlentities(OCIResult($q,"OPER_COMMENT")))."</b><br>";
		
		//файлы
		OCIBindByName($q_files,":base_id",$tmp_base_id);
		OCIExecute($q_files);
		$f=0; while(OCIFetch($q_files)) { $f++;
			if($f==1) {
				echo "<hr>Файлы: ";
			}
			echo "<a href='http://sup.wilstream.ru/files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a>; ";
		}	
		echo "</td>";	
	}
	else {

		echo "<td bgcolor=white valign=top align=center width=120";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";	
	
		echo">".OCIResult($q,"DATE_IN_CALL")."</td>
<td bgcolor=white valign=top valign=top";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
		echo ">".OCIResult($q,"NAME")."</td>";

		echo "<td bgcolor=white valign=top valign=top";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
		echo ">".OCIResult($q,"KTO")."</td>";

		echo "<td bgcolor=white valign=top valign=top";
		if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
		else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	
		echo " title='У кого не работает: ".OCIResult($q,"U_KOGO")."
Описание проблемы: ".OCIResult($q,"OPER_COMMENT")."'>".nl2br(htmlentities(OCIResult($q,"TRBL_NAME")))."</td>";
	
	}

	echo "<td bgcolor=white valign=top valign=top"; 
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo ">".OCIResult($q,"FIO")."</td>";
	echo "<td bgcolor=white valign=top align=center";
	if (OCIResult($q,"DUBLIKAT")=="y") echo " style='color:grey'";
	else if (OCIResult($q,"KRIVIE_RUKI")=="y") echo " style='color:grey'";
	echo "><font color='".$status_color."'>".$status_name."</font></td>
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
//echo "</td></tr></table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr class=footer_tr><td>";

OCIFreeStatement($q);
$_SESSION['q_count']=$rownum; //эта переменная нужна для автоматического олбновления окна с заявками
$_SESSION['q_checksum']=$checksum; //эта переменная нужна для автоматического олбновления окна с заявками
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
if($find_id=='') echo "document.all.klinika_id.disabled=false;";
ksort($trbl_grp_arr);
foreach ($trbl_grp_arr as $grp=>$trbl_arr) {
	asort($trbl_grp_arr[$grp]);
	echo "add_optgroup(document.all.trbl_id,'".$grp."');";
	foreach($trbl_grp_arr[$grp] as $key => $val) {
		if($key==$trbl_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.trbl_id,'".$key."','".$val."','".$selected."');";
	}
}
if($find_id=='') echo "document.all.trbl_id.disabled=false;";

/*asort($location_arr);
foreach($location_arr as $key => $val) {
	if($key==$klinika_id) $selected='selected'; else $selected='';
	echo "add_options(document.all.klinika_id,'".$key."','".$val."','".$selected."');";
}*/
//if(!isset($no_kto)) {
	asort($kto_arr);
	foreach($kto_arr as $key => $val) {
		if($key==$kto_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.kto_id,'".$key."','".$val."','".$selected."');";
	}
if($find_id=='') echo "document.all.kto_id.disabled=false;";
//}
//asort($trbl_arr);
//foreach($trbl_arr as $key => $val) {
//	if($key==$trbl_id) $selected='selected'; else $selected='';
//	echo "add_options(document.all.trbl_id,'".$key."','".$val."','".$selected."');";
//}
//if($find_id=='') echo "document.all.trbl_id.disabled=false;";
//if(!isset($no_texn)) {
	asort($texnari_arr);
	foreach($texnari_arr as $key => $val) {
		if($key==$texnari_id) $selected='selected'; else $selected='';
		echo "add_options(document.all.texnari_id,'".$key."','".$val."','".$selected."');";
	}
if($find_id=='') echo "document.all.texnari_id.disabled=false;";
//}
//if(count($lt_grp_arr)>1 and $find_id=='') echo "document.all.lt_grp_id.disabled=false;";
if($find_id=='') echo "document.all.start_date.disabled=false;document.all.end_date.disabled=false;document.all.show_closed.disabled=false;document.all.show_delayed.disabled=false;";
echo "</script>";

echo '<iframe name=check_new src="tex_check_new.php" style="display:none"></iframe>
<iframe width=174 height=189 name="gToday:normal:agenda.js" id="gToday:normal:agenda.js" src="clndrxp94/ipopeng_tex.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">';

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>
<script>head_width();</script>
</body>
</html>
</iframe>
