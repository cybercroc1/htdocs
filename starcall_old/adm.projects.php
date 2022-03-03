<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<script src="func.row_select.js"></script>
<script language="javascript">
var timerID='';
function find_timer() {
	if(frm_projects.find_string.value.length==0 || frm_projects.find_string.value.length>=3) {clearTimeout(timerID); timerID=setTimeout('frm_projects.submit()',1500);}
}
function ch_new_project_name() {
	if (document.all.new_project_name.value=='') {
	document.all.add_project.disabled=true;
	} else {
	document.all.add_project.disabled=false;
}}
function sel_project(cell) {
	frm_sel_project.project_id.value=cell.parentNode.getAttribute('data-project_id');
	frm_sel_project.submit();
	click_row(cell);
}
</script>
<body>
<?php
extract($_REQUEST);
include("../../conf/starcall_conf/conn_string.cfg.php");
if($_SESSION['user']['rw_projects']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form name=frm_projects method=post>";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";


if(!isset($_SESSION['adm']['projects']['show_closed'])) $_SESSION['adm']['projects']['show_closed']='off';
if(isset($show_closed)) $_SESSION['adm']['projects']['show_closed']=$show_closed; 

if(!isset($order_by) and !isset($_SESSION['adm']['projects']['order_by'])) $_SESSION['adm']['projects']['order_by']='name';
if(isset($order_by)) $_SESSION['adm']['projects']['order_by']=$order_by;

if(!isset($find_string) and !isset($_SESSION['adm']['projects']['find_string'])) $_SESSION['adm']['projects']['find_string']='';
if(isset($find_string)) $_SESSION['adm']['projects']['find_string']=$find_string;

if(!isset($date_filter) and !isset($_SESSION['adm']['projects']['date_filter'])) $_SESSION['adm']['projects']['date_filter']='-1';
if(isset($date_filter)) $_SESSION['adm']['projects']['date_filter']=$date_filter;

echo "<script>parent.logFrame.location='blank_page.php';</script>";

//сохранение
if($_SESSION['user']['rw_projects']=='w') { 
if (isset($add_project)) {
	$new_project_name=trim($new_project_name);
	$q=OCIParse($c,"select count(*) count from STC_PROJECTS where trim(upper(name))=trim(upper('".$new_project_name."')) and trunc(create_date)=trunc(sysdate)");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if(strlen($new_project_name)<3) {echo "<b><font color=red>ОШИБКА! Название проекта не должно быть короче 3-х символов</font><br>";}
	elseif (OCIResult($q,"COUNT")>0) {echo "<b><font color=red>ОШИБКА! Проект с именем \"".$new_project_name."\" уже существует</font><br>";}
	else {
	$ins=OCIParse($c,"insert into STC_PROJECTS (id,name,create_date,status,creator) 
	values (SEQ_STC_PROJECT_ID.nextval,'".$new_project_name."',sysdate,'Приостановлен',".$_SESSION['user']['id'].") returning id into :new_project_id");
	$new_project_id='';
	OCIBindByName($ins,':new_project_id',$new_project_id,256);
	OCIExecute($ins,OCI_DEFAULT);
	//добавляем в группы по умолчанию, в которых состоит пользователь
	OCIExecute(OCIParse($c,"insert into STC_USER_GRP_PRJ 
	select ".$new_project_id.",ug.group_id from STC_USER_GRP_USR ug, STC_USER_GROUP g where g.id=ug.group_id and g.default_group='y' and ug.user_id=".$_SESSION['user']['id']));
	OCICommit($c);
	$order_by='p.create_date desc'; $_SESSION['adm']['projects']['order_by']=$order_by; //если добавлнен проект, то сортируем по дате
	$_SESSION['adm']['project']['id']=$new_project_id;
	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
}}
if((isset($close) or isset($pause) or isset($open)) and isset($mark)) {
	isset($close)?$set_status='Закрыт':NULL;
	isset($pause)?$set_status='Приостановлен':NULL;
	isset($open)?$set_status='Активен':NULL;
	$q=OCIParse($c,"select name,SRC_QUOTE_BROKEN,QST_QUOTE_BROKEN from STC_PROJECTS where id=:id");
	$upd=OCIParse($c,"update STC_PROJECTS set status='".$set_status."', status_date=sysdate where id=:id");
	foreach($mark as $id) {
		OCIBindByName($q,":id",$id);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'' or OCIResult($q,"QST_QUOTE_BROKEN")<>'' and $set_status=='Активен') {
			echo "<font color=red><b>ОШИБКА: Нельзя активировать проект \"".OCIResult($q,"NAME")."\". Необходимо перестроить квоты.</b></font><br>";
			continue;
		}
		OCIBindByName($upd,":id",$id);
		OCIExecute($upd,OCI_DEFAULT);
		OCICommit($c);
	}
	//unset($_SESSION['adm']['project']);
	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";
}
}

	echo "<font size=4>Проекты</font>";
	echo " | ПОИСК:<input type=text name=find_string value='".$_SESSION['adm']['projects']['find_string']."' onkeyup=find_timer(); onpaste=find_timer(); onchange=frm_projects.submit();></input>";
	echo " | <select name=show_closed onchange=frm_projects.submit()>
	 <option value='off'".($_SESSION['adm']['projects']['show_closed']=='off'?' selected':NULL).">скрыть закрытые</option>
	 <option value='on'".($_SESSION['adm']['projects']['show_closed']=='on'?' selected':NULL).">показать закрытые</option>
	 </select> ";


	 echo "<select name=date_filter onchange=frm_projects.submit()>
	 <option value='-1'".($_SESSION['adm']['projects']['date_filter']==-1?" selected":NULL).">дата посл.активности 1 мес назад</option>
	 <option value='-2'".($_SESSION['adm']['projects']['date_filter']==-2?" selected":NULL).">дата посл.активности 2 месяца назад</option>
	 <option value='-3'".($_SESSION['adm']['projects']['date_filter']==-3?" selected":NULL).">дата посл.активности 3 месяца назад</option>
	 <option value='-6'".($_SESSION['adm']['projects']['date_filter']==-6?" selected":NULL).">дата посл.активности пол года назад</option>
	 <option value='-12'".($_SESSION['adm']['projects']['date_filter']==-12?" selected":NULL).">дата посл.активности 1 год назад</option>
	 <option value='-24'".($_SESSION['adm']['projects']['date_filter']==-24?" selected":NULL).">дата посл.активности 2 года назад</option>
	 <option value='-36'".($_SESSION['adm']['projects']['date_filter']==-36?" selected":NULL).">дата посл.активности 3 года назад</option>
	 <option value=''".($_SESSION['adm']['projects']['date_filter']==''?" selected":NULL).">показать все</option>
	 </select> | 
	 ";
	 
	 echo "<script>
	 frm_projects.find_string.focus();
	 frm_projects.find_string.selectionStart = frm_projects.find_string.value.length;
	 if(frm_projects.find_string.value.length!=0) {
		frm_projects.date_filter.disabled=true;
		frm_projects.show_closed.disabled=true;
	}
	 </script>";
	 echo "<font align=right><a href='help.adm.projects.html' target='_blank'>Справка</a></font>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";
	
	echo "<table id=tbl>
	<tr>
	<td align=center></td>
	<td align=center><b>ID</b></td>
	<td align=center><a href='adm.projects.php?order_by=p.name'>".($_SESSION['adm']['projects']['order_by']=='p.name'?'<b>':NULL)."Название</b></a></td>
	<td align=center><a href='adm.projects.php?order_by=p.create_date desc'>".($_SESSION['adm']['projects']['order_by']=='p.create_date desc'?'<b>':NULL)."Дата создания</b></a></td>
	<td align=center><a href='adm.projects.php?order_by=p.creator'>".($_SESSION['adm']['projects']['order_by']=='p.creator'?'<b>':NULL)."Создатель</b></a></td>
	<td align=center><a href='adm.projects.php?order_by=p.status,p.name'>".($_SESSION['adm']['projects']['order_by']=='p.status,p.name'?'<b>':NULL)."Статус</b></a></td>";

	echo "</tr>";
	
	//Добавить проект
	if($_SESSION['user']['rw_projects']=='w') {
	echo "<tr>
	<td style='background-color:green' colspan=3><input type=text name=new_project_name size='80' onkeyup=ch_new_project_name()></td>";
	echo "<td style='background-color:green' colspan=3><input type=submit name=add_project disabled value=\"Создать проект\"></td>";
	echo "</tr>";
	}
	//
	//Список проектов
	if($_SESSION['user']['all_projects']=='y') $where_prj=''; 
	else $where_prj=" and (
		--проекты созданные мной или моими потомками
		p.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
		or
		--группы, в которых участвую я или мои потомки 
		p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=gu.group_id) 
		or 
		--группы созданные мной или моими потомками
		p.id in (select gp.project_id from STC_USER_GROUP g, STC_USER_GRP_PRJ gp where g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=g.id)
		)";
	
	$q=OCIParse($c,"select p.id,p.name,to_char(p.create_date,'DD.MM.YYYY HH24:MI') create_date,p.status,decode(p.status,'Активен','green','Приостановлен','orange','Закрыт','red') color,
	u.fio creator
	from STC_PROJECTS p, STC_USERS u 
	where u.id=p.creator
	".$where_prj."
	".(($_SESSION['adm']['projects']['show_closed']=='on' and $_SESSION['adm']['projects']['find_string']=='')?NULL:" and p.status <> 'Закрыт'")."
	".(($_SESSION['adm']['projects']['date_filter']<>'' and $_SESSION['adm']['projects']['find_string']=='')?" and p.create_date>=add_months(sysdate,".$_SESSION['adm']['projects']['date_filter'].")":NULL)."
	".($_SESSION['adm']['projects']['find_string']<>''?" and upper(replace(p.name,' ','')) like '%".(strtoupper(str_replace(" ","",$_SESSION['adm']['projects']['find_string'])))."%'":NULL)."
	order by  ".$_SESSION['adm']['projects']['order_by']);

	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<tr data-project_id='".OCIResult($q,"ID")."' onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
		OCIResult($q,"ID")==$_SESSION['adm']['project']['id']?$tmp_class=' class=clicked_row':$tmp_class='';
		
		echo "<td".$tmp_class."><input type=checkbox name=mark[] value='".OCIResult($q,"ID")."'></input></td>
		<td style='cursor:pointer' onclick='sel_project(this)'".$tmp_class."><b>".OCIResult($q,"ID")."</b></td>
		<td style='cursor:pointer' onclick='sel_project(this)'".$tmp_class."><b>".OCIResult($q,"NAME")."</b></td>
		<td style='cursor:pointer' onclick='sel_project(this)'".$tmp_class."><b>".OCIResult($q,"CREATE_DATE")."</b></td>
		<td style='cursor:pointer' onclick='sel_project(this)'".$tmp_class."><b>".OCIResult($q,"CREATOR")."</b></td>
		<td style='cursor:pointer' onclick='sel_project(this)'".$tmp_class.">
		<font color=".OCIResult($q,"COLOR")."><b>".OCIResult($q,"STATUS")."</b></font></td>";		 
		 
		echo "</tr>";
	}
	echo "</table>";

//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";

	echo "<hr>";
	if($_SESSION['user']['rw_projects']<>'w')  echo "<font color=red>Редактирование запрещено!</font>";
	else {
	echo "Выбранные проекты:<br>";
	echo "<input type=submit name=pause value='Приостановить'></input> ";
	echo "<input type=submit name=close value='Закрыть'></input> ";
	echo "<input type=submit name=open value='Возобновить'></input> ";
	}
	//

echo "</form>";
echo "<form name=frm_sel_project method=post action=adm.main.menu.php target=admMainTopFrame>
<input type=hidden name=project_id value=''>
<input type=hidden name=norefresh>
</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

?>

</body></html>
