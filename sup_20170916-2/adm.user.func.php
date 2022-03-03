<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['registrar']) or $_SESSION['registrar']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("sup/sup_conn_string");

if(isset($user_id) and $user_id<>'') {
	//информация о пользователе
	if($new_role_id=='') {//текущие настройки пользовтеля
		if($grp_id<>'') {
			$sql_user="select u.admin,u.registrar,
		    a.look,a.solution,a.redirect,a.eval,a.create_new,a.deny_close,a.rep_stat, --права
			a.em_new,a.em_coment,a.em_redir,a.em_prisv,a.em_delay,a.em_ready,a.em_close,a.em_resume, --получение отчетов по почте
		    a.sm_new,a.sm_redir,a.sm_prisv,a.sm_delay,a.sm_ready,a.sm_close,a.sm_resume --получение отчетов по СМС
		    from SUP_USER u, SUP_USER_LT_ALLOC a 
		    where u.id=".$user_id." 
		    and a.user_id(+)=u.id and a.lt_group_id(+)=".$grp_id;
		}
		else {
			$sql_user="select u.admin,u.registrar,
		    '' look, '' solution, '' redirect, '' eval, '' create_new, '' deny_close, '' rep_stat, --права
			'' em_new, '' em_coment, '' em_redir, '' em_prisv, '' em_delay, '' em_ready, '' em_close, '' em_resume, --получение отчетов по почте
		    '' sm_new, '' sm_redir, '' sm_prisv, '' sm_delay, '' sm_ready, '' sm_close, '' sm_resume --получение отчетов по СМС
		    from SUP_USER u 
			where u.id=".$user_id;
		}
	}
	elseif($new_role_id=='clear') {
		if($grp_id<>'') {
			$sql_user="select u.admin,u.registrar,
		    '' look, '' solution, '' redirect, '' eval, '' create_new, '' deny_close, '' rep_stat, --права
			'' em_new, '' em_coment, '' em_redir, '' em_prisv, '' em_delay, '' em_ready, '' em_close, '' em_resume, --получение отчетов по почте
		    '' sm_new, '' sm_redir, '' sm_prisv, '' sm_delay, '' sm_ready, '' sm_close, '' sm_resume --получение отчетов по СМС
		    from SUP_USER u 
			where u.id=".$user_id;
		}
		else {
			$sql_user="select '' admin, '' registrar,
		    '' look, '' solution, '' redirect, '' eval, '' create_new, '' deny_close, '' rep_stat, --права
			'' em_new, '' em_coment, '' em_redir, '' em_prisv, '' em_delay, '' em_ready, '' em_close, '' em_resume, --получение отчетов по почте
		    '' sm_new, '' sm_redir, '' sm_prisv, '' sm_delay, '' sm_ready, '' sm_close, '' sm_resume --получение отчетов по СМС
		    from dual";			
		}
	}
	else {
		$sql_user="select 
	    t.look,t.solution,t.redirect,t.eval,t.admin,t.create_new,t.deny_close,t.rep_stat,t.registrar, --права
	    t.em_new,t.em_coment,t.em_redir,t.em_prisv, t.em_delay, t.em_ready,t.em_close,t.em_resume, --получение отчетов по почте
	    t.sm_new,t.sm_redir,t.sm_prisv, t.sm_delay, t.sm_ready,t.sm_close,t.sm_resume --получение отчетов по СМС
	    from sup_role_pattern t where t.id=".$new_role_id;			
	}
	echo "<textarea>".$sql_user."</textarea>";
	$q_user=OCIParse($c,$sql_user);
	OCIExecute($q_user,OCI_DEFAULT);
	OCIFetch($q_user);	
	$role_HTML='';	
	
	//$role_HTML.="<table><tr>"; 
	//$role_HTML.="<td valign=top>";
	
		function show_select($select_name,$select_value) {
			$res='';
			$res.="<select name=".$select_name.">";
			$res.="<option value=''></option>";
			$res.="<option value='my'".($select_value=='my'?' selected':'').">Мои</option>";
			$res.="<option value='all'".($select_value=='all'?' selected':'').">Все</option>";
			$res.="</select>";
			return $res;						
		}
	
		$role_HTML.="<table class=white_table>";
		
		if($grp_id<>'') {
			$role_HTML.="<tr><th>Привилегии пользователя в группе</th>";
			$role_HTML.="<th>Отправлять уведомления:</th><th>email</th><th>СМС</th>";
			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=look type=checkbox value='y'".(OCIResult($q_user,"LOOK")=='y'?' checked':'')."><b>Обозреватель</b></input><br><i>Разрешает работать со всеми заявками в группах</i></td>";
			
			$role_HTML.="<th align=right>Создана заявка</th><td>".show_select('em_new',OCIResult($q_user,"EM_NEW"))."</td>";
			$role_HTML.="<td>".show_select('sm_new',OCIResult($q_user,"SM_NEW"))."</td>";			
			
			$role_HTML.="</tr>";

			$role_HTML.="<tr>";
			$role_HTML.="<td></td>";
			
			$role_HTML.="<th align=right>Возобновлена</th><td>".show_select('em_resume',OCIResult($q_user,"EM_RESUME"))."</td>";
			$role_HTML.="<td>".show_select('sm_resume',OCIResult($q_user,"SM_RESUME"))."</td>";
			
			$role_HTML.="</tr>";

			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=solution type=checkbox value='y'".(OCIResult($q_user,"SOLUTION")=='y'?' checked':'')."><b>Исполнитель</b></input><br><i>Разрешает присваивать, комметировать, закрывать заявки</i></td>"	;
			
			$role_HTML.="<th align=right>Комментарий</th><td>".show_select('em_coment',OCIResult($q_user,"EM_COMENT"))."</td>";
			$role_HTML.="<td></td>";				
			
			$role_HTML.="</tr>";


			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=redirect type=checkbox value='y'".(OCIResult($q_user,"REDIRECT")=='y'?' checked':'')."><b>Стрелочник</b></input><br><i>Разрешает переадресовывать заявки</i></td>";
			
			$role_HTML.="<th align=right>Переадресовано</th><td>".show_select('em_redir',OCIResult($q_user,"EM_REDIR"))."</td>";
			$role_HTML.="<td>".show_select('sm_redir',OCIResult($q_user,"SM_REDIR"))."</td>";	
			
			$role_HTML.="</tr>";
		
			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=eval type=checkbox value='y'".(OCIResult($q_user,"EVAL")=='y'?' checked':'')."><b>Оценщик</b></input><br><i>Разрешает ставить оценку</i></td>";
			
			$role_HTML.="<th align=right>Принята в работу</th><td>".show_select('em_prisv',OCIResult($q_user,"EM_PRISV"))."</td>";
			$role_HTML.="<td>".show_select('sm_prisv',OCIResult($q_user,"SM_PRISV"))."</td>";
			
			$role_HTML.="</tr>";
			
			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=create_new type=checkbox value='y'".(OCIResult($q_user,"CREATE_NEW")=='y'?' checked':'')."><b>Заявитель</b></input><br><i>Разрешает создавать заявки</i></td>";
			
			$role_HTML.="<th align=right>Отложена</th><td>".show_select('em_delay',OCIResult($q_user,"EM_DELAY"))."</td>";
			$role_HTML.="<td>".show_select('sm_delay',OCIResult($q_user,"SM_DELAY"))."</td>";
			
			$role_HTML.="</tr>";

			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=deny_close type=checkbox value='y'".(OCIResult($q_user,"DENY_CLOSE")=='y'?' checked':'')."><b>Запрещено закрывать</b></input><br><i>Запрещает иполнителю закрывать заявки</i></td>";
			
			$role_HTML.="<th align=right>Готова к проверке</th><td>".show_select('em_ready',OCIResult($q_user,"EM_READY"))."</td>";
			$role_HTML.="<td>".show_select('sm_ready',OCIResult($q_user,"SM_READY"))."</td>";
			
			$role_HTML.="</tr>";
		
			$role_HTML.="<tr>";
			$role_HTML.="<td><input name=rep_stat type=checkbox value='y'".(OCIResult($q_user,"REP_STAT")=='y'?' checked':'')."><b>Статистик</b></input><br><i>Разрешает доступ к статистике</i></td>";
			
			$role_HTML.="<th align=right>Закрыта</th><td>".show_select('em_close',OCIResult($q_user,"EM_CLOSE"))."</td>";
			$role_HTML.="<td>".show_select('sm_close',OCIResult($q_user,"SM_CLOSE"))."</td>";
			
			$role_HTML.="</tr>";
												
		}
		$role_HTML.="</tr></table></th></tr>";
		$role_HTML.="</table>";
	$role_HTML.="</td>";
	
echo "<script>
parent.document.getElementById('div_role').innerHTML='".str_replace("'","\'",$role_HTML)."';
</script>";
}
?>
