<?php 
ini_set('session.use_cookies','1');
ini_set('session.use_trans_sid','0');
include("sc/sc_session.php");
session_start();
?>
<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body class="body_marign">
<script>
function ch(obj1) {
	var arr1=obj1.name.split("_");
	if(arr1.length==2) {
		var type1=arr1[0];
		var frmobj1=arr1[1];
			
		//if(type1=='send') return;	
		
		var node_list = document.getElementsByTagName('input');
	
		var all_fix_checked='y';
		var all_obj_checked='y';
		var all_cdn_checked='y';
		var all_frm_unchecked='y';
		var all_cdn_unchecked='y';
		for (var i = 0; i < node_list.length; i++) {
			var obj2 = node_list[i];
			var arr2=obj2.name.split("_");
			if(arr2.length==2) {
				var type2=arr2[0];
				var frmobj2=arr2[1];
				
				//включена или выключена опция всех полей вех типов
				if(type1=='type' && frmobj1=='all') {
					obj2.checked=obj1.checked;
				}

				//включена или выключена опция всех стандартных полей
				if(type1=='fix' && frmobj1=='all') {
					if(type2=='fix' && frmobj2!='all') obj2.checked=obj1.checked;
				}
				//выключена одна из опций стандартных полей
				if(type1=='fix' && obj1.checked==false) {
					if(type2=='fix' && frmobj2=='all') obj2.checked=false;
					if(type2=='type' && frmobj2=='all') obj2.checked=false;
				}
				//включена или выключена опция всех полей отчета
				if(type1=='obj' && frmobj1=='all') {
					if(type2=='obj' && frmobj2!='all') obj2.checked=obj1.checked;
				}
				//выключена одна из опций полей отчета
				if(type1=='obj' && obj1.checked==false) {
					if(type2=='obj' && frmobj2=='all') obj2.checked=false;
					if(type2=='type' && frmobj2=='all') obj2.checked=false;
				}
				if(type2=='fix' && frmobj2!='all' && obj2.checked==false) all_fix_checked='n';
				if(type2=='obj' && frmobj2!='all' && obj2.checked==false) all_obj_checked='n';
				
				if((type1=='type' || type2=='fix' || type2=='obj') && obj2.checked==true) all_frm_unchecked='n';
				
				//включена или выключена опция всех номеров
				if(type1=='cdn' && frmobj1=='all') {
					if(type2=='cdn' && frmobj2!='all') obj2.checked=obj1.checked;
				}
				//выключена одна из опций номеров
				if(type1=='cdn' && obj1.checked==false) {
					if(type2=='cdn' && frmobj2=='all') obj2.checked=false;
				}
				if(type2=='cdn' && frmobj2!='all' && obj2.checked==false) all_cdn_checked='n';	

				if(type2=='cdn' && obj2.checked==true) all_cdn_unchecked='n';
			}
		}
	}
		if(all_fix_checked=='y') document.all['fix_all'].checked=true;
		if(all_obj_checked=='y') document.all['obj_all'].checked=true;
		if(all_cdn_checked=='y') document.all['cdn_all'].checked=true;
		if(all_fix_checked=='y' && all_obj_checked=='y' && all_cdn_checked=='y') document.all['type_all'].checked=true;
		//отключаем емейл, если все выключено
		if(all_frm_unchecked=='y') document.all['sendmail'].checked=false;
}
</script>

<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

extract($_REQUEST);

$login_id=$_SESSION['edit_login']['id'];
if(isset($form_id)) $_SESSION['edit_login']['common_form_id']=$form_id; else $_SESSION['edit_login']['common_form_id']='';
$form_id=$_SESSION['edit_login']['common_form_id'];

if($login_id=='' or $form_id=='') exit();

include("sc/sc_conn_string.php");


if(isset($save)) {
	//удаление всех прав доступа
	$del=OCIParse($c,"delete from SC_ACC_CDN where login_id='".$login_id."' and form_id='".$form_id."' and project_id=0");
	OCIExecute($del,OCI_DEFAULT);
	$del1=OCIParse($c,"delete from SC_ACC_FRM_OBJ where login_id='".$login_id."' and form_id='".$form_id."' and project_id=0");
	OCIExecute($del1,OCI_DEFAULT);
	$del2=OCIParse($c,"delete from SC_ACC_FORMS where login_id='".$login_id."' and form_id='".$form_id."' and project_id=0");
	OCIExecute($del2,OCI_DEFAULT);
	OCICommit($c);
	//OCICommit($c);
	//парсинг имен переменных
	$send_email='';
	foreach($_POST as $varname => $val) {
		$arr=explode("_",$varname);
		if(count($arr)==2) {
			
			$type=$arr[0];
			$obj=$arr[1];
			
			if ($type=='type' and $obj=='all') $frm[$form_id]['all']['all']='all';
			else if ($type=='fix' and $obj=='all') $frm[$form_id]['fix']['all']='all';
			else if ($type=='obj' and $obj=='all') $frm[$form_id]['obj']['all']='all';
			else $frm[$form_id][$type][$obj]=$obj;
		}
	}
	//

	//шаблон с именами стандартных полей
	$fix["dtc"]='';$fix["cdn"]='';$fix["cgn"]='';$fix["agt"]='';$fix["cls"]='';$fix["clm"]='';
	$fix["ivs"]='';$fix["qus"]='';$fix["als"]='';$fix["cts"]='';$fix["ctm"]='';

	//INSERT---------------------------------------------------------
	if(isset($frm[$form_id]['all'])) {
		//echo "все стандартные поля и все поля отчета<br>";
		if(count($send_email)>0) $se='y'; else $se='';
		$ins=OCIParse($c, "insert into SC_ACC_FORMS 
		(project_id,login_id,form_id,date_call, cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min, send_email)
		values
		(0,'".$login_id."','".$form_id."','y','y','y','y','y','y','y','y','y','y','y','".$se."')");
		OCIExecute($ins,OCI_DEFAULT);
		$ins=OCIParse($c, "insert into SC_ACC_FRM_OBJ (project_id,login_id,form_id,obj_id) 
		values (0,'".$login_id."','".$form_id."',0)");
		OCIExecute($ins,OCI_DEFAULT);
		OCICommit($c);
	}
	else {
		$ins_frm=OCIParse($c, "insert into SC_ACC_FORMS 
		(project_id,login_id,form_id,date_call, cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min, send_email)
		values
		(0,'".$login_id."','".$form_id."',:dtc,:cdn,:cgn,:agt,:cls,:clm,:ivs,:qus,:als,:cts,:ctm,:se)");
		
		$ins_obj=OCIParse($c, "insert into SC_ACC_FRM_OBJ (project_id,login_id,form_id,obj_id) 
		values (0,'".$login_id."','".$form_id."',:obj_id)");
		$frm_inserted='';
		if(isset($frm[$form_id]['all']) or isset($frm[$form_id]['fix']['all'])) {
				
			foreach($fix as $key => $val) {$fix[$key]='y';}
			
			if(isset($sendmail)) $se='y'; else $se='';
			foreach($fix as $key => $val) {
				OCIBindByName($ins_frm,":".$key, $fix[$key]);
			}
			OCIBindByName($ins_frm,":se",$se);
			if(isset($frm[$form_id]['all'])) {
				//echo "Форма ".$form_id.", все стандартные поля и все поля отчета<br>";
				OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';
				$obj_id=0;
				OCIBindByName($ins_obj,":obj_id",$obj_id);
				OCIExecute($ins_obj,OCI_DEFAULT);
			}
			else {
				//echo "Форма ".$form_id.", все стандартные поля<br>";
				OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';				
			}
			OCICommit($c);
		}
		else if(isset($frm[$form_id]['fix'])) {
			foreach($frm[$form_id]['fix'] as $obj_id => $x) {
				//echo "Форма ".$form_id.", стандартное поле ".$obj_id."<br>";
				$fix[$obj_id]='y';
			}
			if(isset($sendmail)) $se='y'; else $se='';
			foreach($fix as $key => $val) {
				OCIBindByName($ins_frm,":".$key, $fix[$key]);
			}
			OCIBindByName($ins_frm,":se",$se);
			OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';
			OCICommit($c);
		}
		if(!isset($frm[$form_id]['all'])) {
			if(isset($frm[$form_id]['obj']['all'])) {
				//echo "Форма ".$form_id.", все поля отчета<br>";
				if($frm_inserted<>'y') {
					if(isset($sendmail)) $se='y'; else $se='';
					foreach($fix as $key => $val) {
						OCIBindByName($ins_frm,":".$key, $fix[$key]);
					}
					OCIBindByName($ins_frm,":se",$se);
					OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';					
				}
				$obj_id=0;
				OCIBindByName($ins_obj,":obj_id",$obj_id);
				OCIExecute($ins_obj,OCI_DEFAULT);
				OCICommit($c);				
			}				
			else {
				if(isset($frm[$form_id]['obj'])) {
					foreach($frm[$form_id]['obj'] as $obj_id => $x) {
						if($frm_inserted<>'y') {
							if(isset($sendmail)) $se='y'; else $se='';
							foreach($fix as $key => $val) {
								OCIBindByName($ins_frm,":".$key, $fix[$key]);
							}
							OCIBindByName($ins_frm,":se",$se);
							OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';					
						}	
						//echo "Форма ".$form_id.", поле отчета ".$obj_id."<br>";
						OCIBindByName($ins_obj,":obj_id",$obj_id);
						OCIExecute($ins_obj,OCI_DEFAULT);						
					}
					OCICommit($c);
				}
			}
		}
		if($frm_inserted<>'y') {
			if(isset($sendmail)) $se='y'; else $se='';
			foreach($fix as $key => $val) {
				OCIBindByName($ins_frm,":".$key, $fix[$key]);
			}
			OCIBindByName($ins_frm,":se",$se);
			OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';
			OCICommit($c);
		}
	}		

	//------------------------------------------------------------------------------------
	//НОМЕРА ДОСТУПА
	if(isset($frm[$form_id]['cdn']['all'])) {
		//echo "Форма ".$form_id.", все номера доступа<br>";
		//добавляем разрешение на доступ ко всем номерам
		$ins_cdn=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) 
										values ('".$login_id."',0,'".$form_id."','all')");
		OCIExecute($ins_cdn,OCI_DEFAULT);
		OCICommit($c);
	}
	else if (isset($frm[$form_id]['cdn'])) {
		$ins_cdn=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) 
										values (".$login_id.",0,'".$form_id."',:phone)");
		foreach($frm[$form_id]['cdn'] as $cdn => $x) {
			//echo "Форма ".$form_id.", номер доступа ".$cdn."<br>";
			OCIBindByName($ins_cdn,":phone",$cdn);
			OCIExecute($ins_cdn,OCI_DEFAULT);
		}
		OCICommit($c);
	}	
}			
//==========================================================================================


//список форм
$q_frm=OCIParse($c,"select id form_id, name form_name,
show_call_sec, show_call_min, show_ivr_sec, show_queue_sec, show_alerting_sec, show_connected_sec, show_connected_min
from SC_FORMS t
where project_id='0' and id='".$form_id."' and deleted is null
order by name");

//доступ к отчетам (и стандартным полям)
$q_frm_acc=OCIParse($c,"select af.form_id, af.date_call, af.cdpn, af.cgpn, af.agid, af.call_sec, af.call_min, af.ivr_sec, af.queue_sec, af.alerting_sec, af.connected_sec, af.connected_min, af.send_email 
from SC_ACC_FORMS af
where login_id='".$login_id."' and project_id='0' and form_id='".$form_id."'");

$q_obj=OCIParse($c,"select o.id obj_id, o.name obj_name, (
select min(ao.obj_id) from SC_ACC_FRM_OBJ ao where ao.project_id='0' and ao.login_id='".$login_id."' and ao.form_id=o.form_id and (ao.obj_id=0 or ao.obj_id=o.id) 
) checked
from SC_FORM_OBJECT o
where o.form_id='".$form_id."'
order by o.ordering");

echo "<form action=adm_usr_acc_comrep_obj.php method=post>";
echo "<input type=hidden name=login_id value='".$login_id."'></input>";
echo "<input type=hidden name=form_id value='".$form_id."'></input>";

	$tmp_project_id='0';
	
	OCIExecute($q_frm,OCI_DEFAULT);
	if(OCIFetch($q_frm)) {
		$fix=array();
		$fix_rep=array();
		$all_fix_checked='';
		$tmp_form_id=OCIResult($q_frm,"FORM_ID");
		$tmp_form_name=OCIResult($q_frm,"FORM_NAME");
		$tmp_send_email='';
		
		$fix['date_call']='';	
		$fix['cdpn']='';		
		$fix['cgpn']='';		
		$fix['agid']='';		
		if(OCIResult($q_frm,"SHOW_CALL_SEC")=='y')		{$fix_rep['call_sec']='';		$fix['call_sec']='';}		
		if(OCIResult($q_frm,"SHOW_CALL_MIN")=='y')		{$fix_rep['call_min']='';        $fix['call_min']='';}	
		if(OCIResult($q_frm,"SHOW_IVR_SEC")=='y')		{$fix_rep['ivr_sec']='';         $fix['ivr_sec']='';}		
		if(OCIResult($q_frm,"SHOW_QUEUE_SEC")=='y')		{$fix_rep['queue_sec']='';       $fix['queue_sec']='';}	
		if(OCIResult($q_frm,"SHOW_ALERTING_SEC")=='y')	{$fix_rep['alerting_sec']='';    $fix['alerting_sec']='';}
		if(OCIResult($q_frm,"SHOW_CONNECTED_SEC")=='y')	{$fix_rep['connected_sec']='';   $fix['connected_sec']='';}
		if(OCIResult($q_frm,"SHOW_CONNECTED_MIN")=='y')	{$fix_rep['connected_min']='';	$fix['connected_min']='';}			
		
		OCIExecute($q_frm_acc,OCI_DEFAULT);
		if(OCIFetch($q_frm_acc)) {
			
			if(OCIResult($q_frm_acc,"FORM_ID")=='0') $all_prj_checked='y';
			
			$tmp_send_email=OCIResult($q_frm_acc,"SEND_EMAIL");
			
			$fix['date_call']		=OCIResult($q_frm_acc,"DATE_CALL");
			$fix['cdpn']			=OCIResult($q_frm_acc,"CDPN");
			$fix['cgpn']			=OCIResult($q_frm_acc,"CGPN");
			$fix['agid']			=OCIResult($q_frm_acc,"AGID");
			if(isset($fix_rep['call_sec']))			$fix['call_sec']		=OCIResult($q_frm_acc,"CALL_SEC");
			if(isset($fix_rep['call_min']))			$fix['call_min']		=OCIResult($q_frm_acc,"CALL_MIN");
			if(isset($fix_rep['ivr_sec'])) 			$fix['ivr_sec']			=OCIResult($q_frm_acc,"IVR_SEC");
			if(isset($fix_rep['queue_sec']))		$fix['queue_sec']		=OCIResult($q_frm_acc,"QUEUE_SEC");
			if(isset($fix_rep['alerting_sec']))		$fix['alerting_sec']	=OCIResult($q_frm_acc,"ALERTING_SEC");
			if(isset($fix_rep['connected_sec']))	$fix['connected_sec']	=OCIResult($q_frm_acc,"CONNECTED_SEC");
			if(isset($fix_rep['connected_min']))	$fix['connected_min']	=OCIResult($q_frm_acc,"CONNECTED_MIN");
			
			$ch=0; foreach($fix as $k => $v) {
				if($v=='y') $ch++;
			}
			if(count($fix)==$ch) $all_fix_checked='y';
			
		} 
		$tmp_obj=array();
		$all_obj_checked='';
		OCIExecute($q_obj,OCI_DEFAULT);
		$i=0; while (OCIFetch($q_obj)) {$i++;
			$all_obj_checked=OCIResult($q_obj,"CHECKED");
			$tmp_obj[$i]['id']=OCIResult($q_obj,"OBJ_ID");
			$tmp_obj[$i]['name']=OCIResult($q_obj,"OBJ_NAME");
			$tmp_obj[$i]['checked']=OCIResult($q_obj,"CHECKED");
		}
		if($all_fix_checked=='y' and $all_obj_checked=='0') $all_form_checked='y'; else $all_form_checked='';

		echo "<font size=4>".$tmp_form_name."</font>";
		echo "<form method=post>";
		//echo "<table><tr><td>";

		echo "<table><tr><td valign=top>";
		
		echo "<table class=white_table width=100%>";
		echo "<tr><td>";
		echo "<input name='type_all' type=checkbox".($all_form_checked=='y'?' checked':'')." onclick=ch(this)></input><B><u>".$tmp_form_name."</u></B></td> 
		  <td><input name='sendmail' type=checkbox".($tmp_send_email=='y'?' checked':'')." onclick=ch(this)></input>Отправлять по EMAIL";
		echo "</td></tr>";
		echo "<tr>";
		echo "<td valign=top>";

		echo "<input name='fix_all' type=checkbox".($all_fix_checked=='y'?' checked':'')." onclick=ch(this)></input><B>Стандартные поля</B><hr>";
		
		echo "<input type=checkbox name='fix_dtc'".($fix['date_call']=='y'?' checked':'')." onclick=ch(this)></input>Дата звонка<br>
		<input type=checkbox name='fix_cdn'".($fix['cdpn']=='y'?' checked':'')." onclick=ch(this)></input>АОН<br>
		<input type=checkbox name='fix_cgn'".($fix['cgpn']=='y'?' checked':'')." onclick=ch(this)></input>Номер доступа<br>
		<input type=checkbox name='fix_agt'".($fix['agid']=='y'?' checked':'')." onclick=ch(this)></input>ID Оператора<br>";
		if(count($fix_rep)>0) echo "<hr>";
		if(isset($fix_rep['call_sec']))			echo "<input type=checkbox name='fix_cls'".($fix['call_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.вызова(сек)<br>";
		if(isset($fix_rep['call_min']))			echo "<input type=checkbox name='fix_clm'".($fix['call_min']=='y'?' checked':'')." onclick=ch(this)></input>Длит.вызова(мин)<br>";
		if(isset($fix_rep['ivr_sec'])) 			echo "<input type=checkbox name='fix_ivs'".($fix['ivr_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.IVR(сек)<br>";
		if(isset($fix_rep['queue_sec']))		echo "<input type=checkbox name='fix_qus'".($fix['queue_sec']=='y'?' checked':'')." onclick=ch(this)></input>Время в очереди(сек)<br>";
		if(isset($fix_rep['alerting_sec']))		echo "<input type=checkbox name='fix_als'".($fix['alerting_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.КПВ(сек)<br>";
		if(isset($fix_rep['connected_sec']))	echo "<input type=checkbox name='fix_cts'".($fix['connected_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.разговора(сек)<br>";
		if(isset($fix_rep['connected_min']))	echo "<input type=checkbox name='fix_ctm'".($fix['connected_min']=='y'?' checked':'')." onclick=ch(this)></input>Длит.разговора(мин)<br>";
		
		echo "</td>";
		echo "<td valign=top>";

		echo "<input name='obj_all' type=checkbox".($all_obj_checked=='0'?' checked':'')." onclick=ch(this)></input><B>Поля отчета</B><hr>";

		foreach($tmp_obj as $key => $obj) {
			echo "<input type=checkbox name='obj_".$obj['id']."'".($obj['checked']<>''?' checked':'')." onclick=ch(this)></input>".$obj['name']."<br>";
		}
		echo "</td>";
		echo "</tr>";
		echo "</table><br>";
		
		echo "</td><td> </td><td valign=top>";
		
		//доступ к номерам=================================================================================================
		//echo "<td valign=top>";

		//проверка доступа ко всем номерам
		$q=OCIParse($c,"select max(ac.phone) phone from SC_ACC_CDN ac where ac.project_id=0 and ac.form_id='".$form_id."' and ac.login_id='".$login_id."'");
		OCIExecute($q,OCI_DEFAULT);
		OCIFetch($q);
		if(OCIResult($q,"PHONE")=='all') $all_phone_checked='y'; else $all_phone_checked='';
		
		//список номеров и проектов по отчету
		$q_cdn=OCIParse($c,"select pr.id prject_id, pr.name project_name, ph.phone, ph.phone_name, 
		decode((select count(*) from SC_ACC_CDN ac where ac.project_id=0 and ac.phone=ph.phone and ac.form_id='".$form_id."' and ac.login_id='".$login_id."'),0,null,'y') checked 
		from SC_BODY b, SC_PROJECTS pr, SC_PHONES ph
		where b.form_id='".$form_id."'
		and pr.id=b.project_id
		and ph.project_id=pr.id
		order by pr.name, ph.phone");
		//удаленные из проектов номера
		$q_cdn2=OCIParse($c,"select '' project_id, '' project_name, ac.phone, 'y' checked 
		from SC_ACC_CDN ac where ac.phone<>'all' and ac.project_id=0 and ac.login_id='".$login_id."' and form_id='".$form_id."'
		minus
		select '', '', ph.phone, 'y' from SC_BODY b, SC_FORMS f, SC_PHONES ph
		where b.form_id='".$form_id."' and f.project_id=b.project_id and ph.project_id=f.project_id	
		order by phone");	


		//echo "</td></tr></table>";		
		
		echo "<table class=white_table>";
		echo "<tr><td>";
		echo "<input name='cdn_all' type=checkbox".($all_phone_checked=='y'?' checked':'')." onclick=ch(this)></input><B>ВСЕ НОМЕРА</B>";
		echo "</td><td><b>Проект</b></td><td><b>Название номера</b></td>
		</tr>";
	
		//номера 
		OCIExecute($q_cdn,OCI_DEFAULT);
		while (OCIFetch($q_cdn)) {
			$tmp_project_name=OCIResult($q_cdn,"PROJECT_NAME");
			$tmp_cdn=OCIResult($q_cdn,"PHONE");
			$tmp_cdn_name=OCIResult($q_cdn,"PHONE_NAME");
			if($all_phone_checked=='y') $tmp_cdn_checked='y'; else $tmp_cdn_checked=OCIResult($q_cdn,"CHECKED");
			echo "<tr class='selectable_row'><td><input name='cdn_".$tmp_cdn."' type=checkbox".($tmp_cdn_checked=='y'?' checked':'')." onclick=ch(this)>".$tmp_cdn."</input></td>
			<td>".$tmp_project_name."</td><td>".$tmp_cdn_name."</td></tr>";
		}
		//удаленные из проектов номера	
		OCIExecute($q_cdn2,OCI_DEFAULT);
		$i=0; while (OCIFetch($q_cdn2)) {$i++;
			//if($i==1) echo "<hr><font color=red>Удалённые из проектов:</font><br>";
			$tmp_cdn=OCIResult($q_cdn2,"PHONE");
			echo "<tr class='selectable_row'><td><input name='cdn_".$tmp_cdn."' type=checkbox checked onclick=ch(this)>".$tmp_cdn."</input><td colspan=2><font color=red>номер удален из проекта</font></td></tr>";
		}	
		echo "</table>";	
		
		//=========================================================================================================================
		echo "</td></tr></table>";
		
		
		

		echo "<input type=submit name=save value='СОХРАНИТЬ'>";
		echo "</from>";
}



?>






























