<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<br>
<?php
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_project']=='') {echo "<font color=red>Access DENY!</font>"; exit();}
$project_id=$_SESSION['adm']['project']['id'];

include("../../conf/starcall_conf/conn_string.cfg.php");

if($_SESSION['user']['rw_project']=='w') { 
if(isset($frm_submit) and $frm_submit=='save') {
	echo "Сохранение настроек проекта<hr>";
	$info='';
	$error='';
	$nedoz_count=trim($nedoz_count);
	$nedoz_interval=trim($nedoz_interval);
	$quote=trim($quote);
	if(!isset($project_name)) $project_name='';
	else $project_name=trim($project_name);
	//проверка ошибок
	$q=OCIParse($c,"select count(*) count from STC_PROJECTS where trim(upper(name))=trim(upper('".$project_name."')) and trunc(create_date)=(select trunc(create_date) from STC_PROJECTS where id=".$_SESSION['adm']['project']['id'].") 
	and id<>".$_SESSION['adm']['project']['id']);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	if (OCIResult($q,"COUNT")>0) {echo $error.="<font color=red>Ошибка! Проект с именем \"".$project_name."\" уже существует</font><br>";}
	
	if(!isset($set_status)) $set_status='';
	else if($set_status=='Активен') {
		$q=OCIParse($c,"select name,SRC_QUOTE_BROKEN,QST_QUOTE_BROKEN from STC_PROJECTS where id=".$_SESSION['adm']['project']['id']);
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'' or OCIResult($q,"QST_QUOTE_BROKEN")<>'') {
			$error.="<font color=red>Ошибка! Нельзя активировать проект. Необходимо перестроить квоты.</font><br>";
		}	
	}
	if(!preg_match('/^\d{1,15}$/',$nedoz_count)) {
		$error.="<font color=red>Ошибка! Количество попыток недозвона должно быть целым положительным числом.</font><br>";
	}
	if(!preg_match('/^\d{1,15}$/',$nedoz_interval)) {
		$error.="<font color=red>Ошибка! Интервал недозвонов дожен быть целым положительным числом.</font><br>";
	}
	if(!preg_match('/^\d{0,15}$/',$quote)) {
		$error.="<font color=red>Ошибка! Квота должна быть целым числом или пустая.</font><br>";
	}
	if(!preg_match('/^\d{0,3}$/',$nedoz_chance) or $nedoz_chance>100) {
		$error.="<font color=red>Ошибка! Вероятность недозвона должна быть целым числом от 0 до 100.</font><br>";
	}			
	if($to_time<$from_time) {
		$error.="<font color=red>Ошибка! Время начала меньше времени окончания.</font><br>";
	}	
	if($error<>'') {
		echo $error;
		echo "<script>
		parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$error."';
		parent.admBottomFrame.frm.save.disabled=false;
		</script>";
		exit();		
	}
	//
	//старое кол-во недозвонов
	$q=OCIParse($c,"select nedoz_count, quote from STC_PROJECTS where id=".$project_id);
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$old_nedoz_count=OCIResult($q,"NEDOZ_COUNT");	
	$old_quote=OCIResult($q,"QUOTE");	
	//сохраняем===============================
	$upd=OCIParse($c,"update STC_PROJECTS
	set	name=nvl('".$project_name."',name), status=nvl('".$set_status."',status), nedoz_count=".$nedoz_count.", nedoz_interval=".$nedoz_interval.", from_time='".$from_time."', to_time='".$to_time."', quote='".$quote."', perez_policy='".$perez_policy."', nedoz_chance='".$nedoz_chance."'
	where id=".$project_id);
	OCIExecute($upd, OCI_DEFAULT);
	
	if($nedoz_count<>$old_nedoz_count) {
		if($nedoz_count>$old_nedoz_count) {
			//меняем статус с глухого недозвона на недозвон, для записей, где статус=end_nedoz и кол-во попыток<$nedoz_count
			$upd=OCIParse($c,"update STC_BASE 
			set status='nedoz'
			where project_id=".$project_id." and status='end_nedoz' and nedoz_count<".$nedoz_count);
			OCIExecute($upd, OCI_DEFAULT);
			if(oci_num_rows($upd)>0) {
				$changed_status='y';
				echo "Изменен статус ".oci_num_rows($upd)." записей с \"глухой недозвон\" на \"недозвон\"<hr>";
			}
		}
		if($nedoz_count<$old_nedoz_count) {
			//меняем статус с недозвона на глухой недозвон, для записей, где статус=nedoz и кол-во попыток>=$nedoz_count
			$upd=OCIParse($c,"update STC_BASE 
			set status='end_nedoz'
			where project_id=".$project_id." and status='nedoz' and nedoz_count>=".$nedoz_count);
			OCIExecute($upd, OCI_DEFAULT);
			if(oci_num_rows($upd)>0) {
				$changed_status='y';
				echo "Изменен статус ".oci_num_rows($upd)." записей с \"недозвон\" на \"глухой недозвон\"<hr>";
			}
		}	
	}
	if($quote<>$old_quote) {
		$changed_quote='y';
	}
	if(isset($groups)) {
		$chk_grp=OCIParse($c,"select gp.project_id from STC_USER_GRP_PRJ gp where gp.project_id=".$project_id." and gp.group_id=:group_id");
		$ins_grp=OCIParse($c,"insert into STC_USER_GRP_PRJ gp (gp.project_id,gp.group_id) values (".$project_id.",:group_id)");
		$del_grp=OCIParse($c,"delete from STC_USER_GRP_PRJ gp where gp.project_id=".$project_id." and gp.group_id=:group_id");
		foreach($groups as $grp_id => $fuck) {
			if(isset($checked_groups[$grp_id])) {//добавляем группу
				OCIBindByName($chk_grp,":group_id",$grp_id);
				OCIExecute($chk_grp, OCI_DEFAULT);
				if(!OCIFetch($chk_grp)) {
					OCIBindByName($ins_grp,":group_id",$grp_id);
					OCIExecute($ins_grp, OCI_DEFAULT);
				}
			}
			else {//удаляем группу
				OCIBindByName($del_grp,":group_id",$grp_id);
				OCIExecute($del_grp, OCI_DEFAULT);
			}		
		}
	}
	OCICommit($c);
	//=========================================
	//если поменялись статусы записей, то пересчитываем статистику по исходным полям
	if(isset($changed_status)) {
		OCIExecute(OCIParse($c,"begin STC_SRC_QUOTE_CALC(".$project_id."); end;"));
		echo "Пересчитана статистика квот по исходным полям (STC_SRC_QUOTE_CALC)<hr>";
	}
	//пересчет общей квоты по проекту
	if(isset($changed_quote)) {
		OCIExecute(OCIParse($c,"begin STC_QUOTE_COMMON_CALC(".$project_id."); end;"));
		echo "Пересчитана общая квота (STC_QUOTE_COMMON_CALC)<hr>";
	}
	echo $info;		
	echo "<font color=green>СОХРАНЕНО</font><br>";
	echo "<script>
	parent.admBottomFrame.document.getElementById('save_status').innerHTML='".$info."<font color=green>СОХРАНЕНО</font>';
	parent.admBottomFrame.frm.save.disabled=false;
	parent.admBottomFrame.frm.save.value='Сохранить';
	parent.admBottomFrame.location.reload();
	parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;
	</script>";
	exit();
}
}
$q=OCIParse($c,"select p.name,to_char(p.create_date,'DD.MM.YYYY') create_date,p.status,p.nedoz_count,p.nedoz_interval,p.from_time,p.to_time, p.quote, p.perez_policy, p.nedoz_chance,u.fio creator from STC_PROJECTS p, STC_USERS u 
where p.id=".$project_id."
and u.id=p.creator");
OCIExecute($q,OCI_DEFAULT);
OCIFetch($q);

echo "<form name=frm method=post target=logFrame>";

	echo "<font size=4>Настройки проекта \"".OCIResult($q,"NAME")."\" (id:$project_id)</font><hr>";
	echo "<table id=tbl>";
	echo "<tr>";
	echo "<td colspan=3><b>Дата создания: </b>".OCIResult($q,"CREATE_DATE");
	echo "<b> Статус: </b>";
	if($_SESSION['user']['rw_projects']=='w') {
		echo "<select name=set_status>
		".(OCIResult($q,"STATUS")=='Закрыт'?'<option value=Закрыт style=color:red selected>Закрыт</option>':NULL)."
		<option value='Активен' style=color:green".(OCIResult($q,"STATUS")=='Активен'?' selected':NULL).">Активен</option>
		<option value='Приостановлен' style=color:orange".(OCIResult($q,"STATUS")=='Приостановлен'?' selected':NULL).">Приостановлен</option>
		</select>";
	}
	else {
		echo OCIResult($q,"STATUS")=='Закрыт'?'<font color=red><b>Закрыт</b></font>':NULL;
		echo OCIResult($q,"STATUS")=='Активен'?'<font color=green><b>Активен</b></font>':NULL;
		echo OCIResult($q,"STATUS")=='Приостановлен'?'<font color=orange><b>Приостановлен</b></font>':NULL;
	}
	
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>Название проекта: </b></td>";
	echo "<td colspan=2>";
	if($_SESSION['user']['rw_projects']=='w') {
		echo "<input type=text name=project_name value='".OCIResult($q,"NAME")."'>";
	}
	else {
		echo "<b>".OCIResult($q,"NAME")."</b>";
	}
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>Квота: </b></td><td><input type=text name=quote value='".OCIResult($q,"QUOTE")."'></td>";
	echo "<td><i>Ограничение на кол-во успешных анкет, если не установлено, то не ограничено. Пересчитается автоматически, если есть квоты.</i></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td align=center><b>Разрешенное время </b></td><td> 
	<select name=from_time>
	<option value='".OCIResult($q,"FROM_TIME")."'>".OCIResult($q,"FROM_TIME")."</option>
	<option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option><option>02:00</option><option>02:30</option><option>03:00</option>
	<option>03:30</option><option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option><option>06:00</option><option>06:30</option>
	<option>07:00</option><option>07:30</option><option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option><option>10:00</option>
	<option>10:30</option><option>11:00</option><option>11:30</option><option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
	<option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option><option>16:00</option><option>16:30</option><option>17:00</option>
	<option>17:30</option><option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option><option>20:00</option><option>20:30</option>
	<option>21:00</option><option>21:30</option><option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
	</select> 
	 - 
	<select name=to_time>
	<option value='".OCIResult($q,"TO_TIME")."'>".OCIResult($q,"TO_TIME")."</option>
	<option>00:00</option><option>00:30</option><option>01:00</option><option>01:30</option><option>02:00</option><option>02:30</option><option>03:00</option>
	<option>03:30</option><option>04:00</option><option>04:30</option><option>05:00</option><option>05:30</option><option>06:00</option><option>06:30</option>
	<option>07:00</option><option>07:30</option><option>08:00</option><option>08:30</option><option>09:00</option><option>09:30</option><option>10:00</option>
	<option>10:30</option><option>11:00</option><option>11:30</option><option>12:00</option><option>12:30</option><option>13:00</option><option>13:30</option>
	<option>14:00</option><option>14:30</option><option>15:00</option><option>15:30</option><option>16:00</option><option>16:30</option><option>17:00</option>
	<option>17:30</option><option>18:00</option><option>18:30</option><option>19:00</option><option>19:30</option><option>20:00</option><option>20:30</option>
	<option>21:00</option><option>21:30</option><option>22:00</option><option>22:30</option><option>23:00</option><option>23:30</option>
	</select> 
	</td>";	
	echo "<td><i>Местное время, в котрое разрешён обзвон (зависит от часового пояса записей, если у записи нет часового пояса, то считаеся, что время московсоке). Значение \"00:00 - 00:00\" - Разрешен круглосуточно. </i></td>";
	echo "</tr>";	
	
	echo "<tr>";
	echo "<td align=center><b>Перезвоны: политика перезвонов и незавершенных опросов:</b></td><td>";
	echo "<select name=perez_policy>
	<option value='pub'".(OCIResult($q,"PEREZ_POLICY")=='pub'?' selected':NULL).">общие</option>
	<option value='priv'".(OCIResult($q,"PEREZ_POLICY")=='priv'?' selected':NULL).">частные</option>
	</select>";
	echo "</td>";
	echo "<td><i><b>Общие.</b><br>
	<b>Перезвоны:</b> оператору бедут выводиться чужие перезвоны, если настало время перезвона, а их хозяин в данный момент не залогинен в систему и не работает по данному проекту.
	<b>Незавершенные:</b> оператор будет иметь доступ к чужим незавершенным, если их хозяин в данный момент не залогинен в систему и не работает по данному проекту.
	<hr>
	<b>Частные.</b> Оператор не будет иметь доступ к чужим перезвонам и незавершенным.
	</i></td>";
	echo "</tr>";

	echo "<tr>";
	echo "<td align=center><b>Недозвоны: кол-во попыток </b></td><td><input type=text name=nedoz_count value='".OCIResult($q,"NEDOZ_COUNT")."'></td>";
	echo "<td><i>Непрерывная серия недозвонов, после которой запись повторному обзвону не подлежит (Глухой недозвон)</i></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align=center><b>Недозвоны: интервал(мин) </b></td><td><input type=text name=nedoz_interval value='".OCIResult($q,"NEDOZ_INTERVAL")."'></td>";	
	echo "<td><i>Следующую попытку по недозвону можно совершить, только по истечении этого времени</i></td>";
	echo "</tr>";
	
	echo "<tr>";
	echo "<td align=center><b>Недозвоны: процент недозвонов в автоматическом режиме</b></td><td><nobr><input type=text name=nedoz_chance value='".OCIResult($q,"NEDOZ_CHANCE")."'><b></b></td>";	
	echo "<td><i>Вероятность выдачи недозвона в автоматическо режиме (0-100%)</i></td>";
	echo "</tr>";	
	
	echo "<tr>";
	echo "<td align=center><b>Создатель:</b></td><td>";
	echo OCIResult($q,"CREATOR");
	echo "</td>";
	echo "<td><i>Данный пользователь всегда имеет доступ к этому проекту.</i></td>";
	echo "</tr>";		
	echo "<td align=center><b>Группы пользователей (создатель):</b></td><td>";
	if($_SESSION['user']['all_users']=='y') $where_grp=''; 
	//список групп, в которых состоит пользователь и его потомки, а так же создателями которыя авляются он и его потомки
	else $where_grp=" and (g.id in (
	select gu.group_id from STC_USER_GRP_USR gu
	where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))
	or g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id']."))";
	
	$q=OCIParse($c,"select g.id,g.name,max(gp.project_id) project_id, u.fio from STC_USER_GROUP g, STC_USER_GRP_PRJ gp, STC_USERS u 
	where 1=1
	".$where_grp."
	and gp.project_id(+)=".$_SESSION['adm']['project']['id']." and gp.group_id(+)=g.id
	and u.id=g.creator
	group by g.id,g.name,u.fio
	order by g.name");
	OCIExecute($q);
	$i=0;
	while(OCIFetch($q)) {$i++;
		$grp_ids[$i]=OCIResult($q,"ID");
		echo "<input type=hidden name=groups[".OCIResult($q,"ID")."]>
		<input type=checkbox name=checked_groups[".OCIResult($q,"ID")."]".(OCIResult($q,"PROJECT_ID")<>''?' checked':NULL).">".OCIResult($q,"NAME")." (".OCIResult($q,"FIO").")</input><br>";
	}
	//список остальных групп, которым назначен проект
	if(isset($grp_ids)) {
		$grp_ids=implode(',',$grp_ids);
		$where_grp="and g.id not in (".$grp_ids.")";
	}
	else {
		$where_grp="";
	}
	
	$q=OCIParse($c,"select g.id,g.name,max(gp.project_id) project_id, u.fio from STC_USER_GROUP g, STC_USER_GRP_PRJ gp, STC_USERS u 
	where gp.project_id=".$_SESSION['adm']['project']['id']."
	".$where_grp."
	and g.id=gp.group_id
	and u.id=g.creator
	group by g.id,g.name, u.fio
	order by g.name");
	OCIExecute($q);
	$i=0;
	while(OCIFetch($q)) {$i++;
		if($i==1) echo "<hr>";
		echo "<input type=checkbox checked disabled>".OCIResult($q,"NAME")." (".OCIResult($q,"FIO").")</input><br>";
	}		

	
	echo "</td>";	
	echo "<td><i>Назначение проекта группам. (с данным проектом могут работать пользователи и операторы, входящие в эти группы).</i></td>";
	echo "</tr>";	
	echo "</table>";

echo "<hr>";


if($_SESSION['user']['rw_project']<>'w') echo "<font color=red>Редактирование запрещено!</font>";
else {
echo "<div id=save_status></div>";
echo "<input type=hidden name=frm_submit value=save>";
echo "<input type=button name=save value=Сохранить onclick=this.disabled=true;frm.submit();> ";
}
echo "</form>";

?>
