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
	if(arr1.length==6) {
		var prj1=arr1[1];
		var frm1=arr1[3];
		var type1=arr1[4];
		var frmobj1=arr1[5];
			
		//if(type1=='send') return;	
		
		var node_list = document.getElementsByTagName('input');
	
		var all_fix_checked='y';
		var all_obj_checked='y';
		var all_prj_checked='y';
		var all_unchecked='y';
		for (var i = 0; i < node_list.length; i++) {
			var obj2 = node_list[i];
			var arr2=obj2.name.split("_");
			if(arr2.length==6) {
				var prj2=arr2[1];
				var frm2=arr2[3];
				var type2=arr2[4];
				var frmobj2=arr2[5];
				
				if(frm1!='all' && obj2.checked==false) document.all['prj_'+prj1+'_frm_all_type_all'].checked=false;
				if(frm1=='all') {
					obj2.checked=obj1.checked;
				} 
				else if(frm2==frm1) {
					//if(type2=='send') continue;
					
					//alert(all_obj_unchecked);
					

					//включена или выключена опция всех полей вех типов
					if(type1=='type' && frmobj1=='all') {
						obj2.checked=obj1.checked;
					}
					//включена или выключена опция всех стандартных полей
					if(type1=='fix' && frmobj1=='all' && frm2==frm1) {
						if(type2=='fix' && frmobj2!='all') obj2.checked=obj1.checked;
					}
					//выключена одна из опций стандартных полей
					if(type1=='fix' && frm2==frm1 && obj1.checked==false) {
						if(type2=='fix' && frmobj2=='all') obj2.checked=false;
						if(type2=='type' && frmobj2=='all') obj2.checked=false;
					}
					//включена или выключена опция всех полей отчета
					if(type1=='obj' && frmobj1=='all') {
						if(type2=='obj' && frmobj2!='all') obj2.checked=obj1.checked;
					}
					//выключена одна из опций полей отчета
					if(type1=='obj' && frm2==frm1 && obj1.checked==false) {
						if(type2=='obj' && frmobj2=='all') obj2.checked=false;
						if(type2=='type' && frmobj2=='all') obj2.checked=false;
					}
					if(type2=='fix' && frmobj2!='all' && obj2.checked==false) all_fix_checked='n';
					if(type2=='obj' && frmobj2!='all' && obj2.checked==false) all_obj_checked='n';
					
					if(obj2.checked==true) all_unchecked='n';
					
				}
				if(type2=='fix' && frmobj2!='all' && obj2.checked==false) all_prj_checked='n';
				if(type2=='obj' && frmobj2!='all' && obj2.checked==false) all_prj_checked='n';
			}
		}
		if(frm1!='all') {
		if(all_fix_checked=='y') document.all['prj_'+prj1+'_frm_'+frm1+'_fix_all'].checked=true;
		if(all_obj_checked=='y') document.all['prj_'+prj1+'_frm_'+frm1+'_obj_all'].checked=true;
		if(all_fix_checked=='y' && all_obj_checked=='y') document.all['prj_'+prj1+'_frm_'+frm1+'_type_all'].checked=true;
		//отключаем емейл, если все выключено
		if(all_unchecked=='y') document.all['prj_'+prj1+'_frm_'+frm1+'_sendmail'].checked=false;
		}		
		if(all_prj_checked=='y') document.all['prj_'+prj1+'_frm_all_type_all'].checked=true;
	}
}
</script>

<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 

$login_id=$_SESSION['edit_login']['id'];
$project_id=$_SESSION['edit_login']['project_id'];

if($login_id=='' or $project_id=='') exit();

extract($_REQUEST);

include("sc/sc_conn_string.php");


if(isset($save)) {
	//удаление всех прав доступа
	$del1=OCIParse($c,"delete from SC_ACC_FRM_OBJ where login_id='".$login_id."' and project_id='".$project_id."'");
	OCIExecute($del1,OCI_DEFAULT);
	$del2=OCIParse($c,"delete from SC_ACC_FORMS where login_id='".$login_id."' and project_id='".$project_id."'");
	OCIExecute($del2,OCI_DEFAULT);
	OCICommit($c);
	//парсинг имен переменных
	$frm=array();
	$send_email=array();
	foreach($_POST as $varname => $val) {
		$arr=explode("_",$varname);
		if(count($arr)==6) {
			
			$frm_id=$arr[3];
			$type=$arr[4];
			$obj=$arr[5];
			
			if($frm_id=='all') $frm['all']['all']['all']='all';
			else if ($type=='type' and $obj=='all') $frm[$frm_id]['all']['all']='all';
			else if ($type=='fix' and $obj=='all') $frm[$frm_id]['fix']['all']='all';
			else if ($type=='obj' and $obj=='all') $frm[$frm_id]['obj']['all']='all';
			else $frm[$frm_id][$type][$obj]=$obj;
		}
		else if (count($arr)==5 and $arr[4]=='sendmail') $send_email[$arr[3]]='y'; 
	}
	if(isset($frm['all']) and count($send_email)<>'0' and count($send_email)<>$frm_count) unset($frm['all']); 	
	//
	
	//INSERT---------------------------------------------------------
	if(isset($frm['all'])) {
		//echo "Все формы, все стандартные поля и все поля отчета<br>";
		if(count($send_email)>0) $se='y'; else $se='';
		$ins=OCIParse($c, "insert into SC_ACC_FORMS 
		(project_id,login_id,form_id,date_call, cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min, send_email)
		values
		('".$project_id."','".$login_id."',0,'y','y','y','y','y','y','y','y','y','y','y','".$se."')");
		OCIExecute($ins,OCI_DEFAULT);
		$ins=OCIParse($c, "insert into SC_ACC_FRM_OBJ (project_id,login_id,form_id,obj_id) 
		values ('".$project_id."','".$login_id."',0,0)");
		OCIExecute($ins,OCI_DEFAULT);
		OCICommit($c);
	}
	else {
		$ins_frm=OCIParse($c, "insert into SC_ACC_FORMS 
		(project_id,login_id,form_id,date_call, cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min, send_email)
		values
		('".$project_id."','".$login_id."',:form_id,:dtc,:cdn,:cgn,:agt,:cls,:clm,:ivs,:qus,:als,:cts,:ctm,:se)");
		
		$ins_obj=OCIParse($c, "insert into SC_ACC_FRM_OBJ (project_id,login_id,form_id,obj_id) 
		values ('".$project_id."','".$login_id."',:form_id,:obj_id)");

		foreach($frm as $form_id => $type) {
			//шаблон с именами стандартных полей
			$fix["dtc"]='';$fix["cdn"]='';$fix["cgn"]='';$fix["agt"]='';$fix["cls"]='';$fix["clm"]='';
			$fix["ivs"]='';$fix["qus"]='';$fix["als"]='';$fix["cts"]='';$fix["ctm"]='';
			$frm_inserted='';
			if(isset($frm[$form_id]['all']) or isset($frm[$form_id]['fix']['all'])) {
				
				foreach($fix as $key => $val) {$fix[$key]='y';}
				
				if(isset($send_email[$form_id])) $se='y'; else $se='';
				OCIBindByName($ins_frm,":form_id", $form_id);
				foreach($fix as $key => $val) {
					OCIBindByName($ins_frm,":".$key, $fix[$key]);
				}
				OCIBindByName($ins_frm,":se",$se);

				if(isset($frm[$form_id]['all'])) {
					//echo "Форма ".$form_id.", все стандартные поля и все поля отчета<br>";
					OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';
					$obj_id=0;
					OCIBindByName($ins_obj,":form_id",$form_id);
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
				if(isset($send_email[$form_id])) $se='y'; else $se='';
				OCIBindByName($ins_frm,":form_id", $form_id); 
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
						if(isset($send_email[$form_id])) $se='y'; else $se='';
						OCIBindByName($ins_frm,":form_id", $form_id); 
						foreach($fix as $key => $val) {
							OCIBindByName($ins_frm,":".$key, $fix[$key]);
						}
						OCIBindByName($ins_frm,":se",$se);
						OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';					
					}
					$obj_id=0;
					OCIBindByName($ins_obj,":form_id",$form_id);
					OCIBindByName($ins_obj,":obj_id",$obj_id);
					OCIExecute($ins_obj,OCI_DEFAULT);
					OCICommit($c);				
				}				
				else {
					if(isset($frm[$form_id]['obj'])) {
						foreach($frm[$form_id]['obj'] as $obj_id => $x) {
							if($frm_inserted<>'y') {
								if(isset($send_email[$form_id])) $se='y'; else $se='';
								OCIBindByName($ins_frm,":form_id", $form_id); 
								foreach($fix as $key => $val) {
									OCIBindByName($ins_frm,":".$key, $fix[$key]);
								}
								OCIBindByName($ins_frm,":se",$se);
								OCIExecute($ins_frm,OCI_DEFAULT); $frm_inserted='y';					
							}	
							//echo "Форма ".$form_id.", поле отчета ".$obj_id."<br>";
							OCIBindByName($ins_obj,":form_id",$form_id);
							OCIBindByName($ins_obj,":obj_id",$obj_id);
							OCIExecute($ins_obj,OCI_DEFAULT);						
						}
						OCICommit($c);
					}
				}
			}
		}
	}
}			
//==========================================================================================

//список проектов
$q_prj=OCIParse($c,"select apr.project_id, pr.name project_name
from SC_ACC_PROJECT apr, SC_PROJECTS pr
where apr.project_id='".$project_id."' and apr.login_id='".$login_id."' and apr.view_rep=1
and pr.id=apr.project_id
order by pr.name");

//список форм
$q_frm=OCIParse($c,"select id form_id, name form_name,
show_call_sec, show_call_min, show_ivr_sec, show_queue_sec, show_alerting_sec, show_connected_sec, show_connected_min
from SC_FORMS t
where project_id='".$project_id."' and deleted is null
order by name");

//доступ к отчетам (и стандартным полям)
$q_frm_acc=OCIParse($c,"select form_id, date_call, cdpn, cgpn, agid, call_sec, call_min, ivr_sec, queue_sec, alerting_sec, connected_sec, connected_min, send_email from SC_ACC_FORMS
where login_id='".$login_id."' and project_id='".$project_id."' and (form_id=:form_id or form_id=0)");

$q_obj=OCIParse($c,"select o.id obj_id, o.name obj_name, (
select min(ao.obj_id) from SC_ACC_FRM_OBJ ao where ao.project_id='".$project_id."' and ao.login_id='".$login_id."' and (ao.form_id=0 or ao.form_id=o.form_id) and (ao.obj_id=0 or ao.obj_id=o.id) 
) checked
from SC_FORM_OBJECT o
where o.form_id=:form_id
order by o.ordering");

echo "<form action=adm_usr_acc_frm.php method=post>";
echo "<input type=hidden name=login_id value='".$login_id."'></input>";
echo "<input type=hidden name=project_id value='".$project_id."'></input>";
$all_prj_checked='';
OCIExecute($q_prj,OCI_DEFAULT);
if(OCIFetch($q_prj)) {
	$tmp_project_id=OCIResult($q_prj,"PROJECT_ID");
	$tmp_project_name=OCIResult($q_prj,"PROJECT_NAME");
	
	OCIExecute($q_frm,OCI_DEFAULT);
	$frm_count=0; while (OCIFetch($q_frm)) {$frm_count++;
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
		
		OCIBindByName($q_frm_acc,":form_id",$tmp_form_id);
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
		OCIBindByName($q_obj,":form_id",$tmp_form_id);
		OCIExecute($q_obj,OCI_DEFAULT);
		$i=0; while (OCIFetch($q_obj)) {$i++;
			$all_obj_checked=OCIResult($q_obj,"CHECKED");
			$tmp_obj[$i]['id']=OCIResult($q_obj,"OBJ_ID");
			$tmp_obj[$i]['name']=OCIResult($q_obj,"OBJ_NAME");
			$tmp_obj[$i]['checked']=OCIResult($q_obj,"CHECKED");
		}
		if($all_fix_checked=='y' and $all_obj_checked=='0') $all_form_checked='y'; else $all_form_checked='';

		if($frm_count==1) {
			echo "<input name='prj_".$tmp_project_id."_frm_all_type_all' type=checkbox".($all_prj_checked=='y'?' checked':'')." onclick=ch(this)></input> ";
			echo "<font size=4>".$tmp_project_name."</font>";
			echo "<form method=post>";
			echo "<table><tr><td>";
		}		
		echo "<table class=white_table width=100%>";
		echo "<tr><td>";
		echo "<input name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_type_all' type=checkbox".($all_form_checked=='y'?' checked':'')." onclick=ch(this)></input><B><u>".$tmp_form_name."</u></B></td> 
		  <td><input name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_sendmail' type=checkbox".($tmp_send_email=='y'?' checked':'')." onclick=ch(this)></input>Отправлять по EMAIL";
		echo "</td></tr>";
		echo "<tr>";
		echo "<td valign=top>";

		echo "<input name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_all' type=checkbox".($all_fix_checked=='y'?' checked':'')." onclick=ch(this)></input><B>Стандартные поля</B><hr>";
		
		echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_dtc'".($fix['date_call']=='y'?' checked':'')." onclick=ch(this)></input>Дата звонка<br>
		<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_cdn'".($fix['cdpn']=='y'?' checked':'')." onclick=ch(this)></input>АОН<br>
		<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_cgn'".($fix['cgpn']=='y'?' checked':'')." onclick=ch(this)></input>Номер доступа<br>
		<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_agt'".($fix['agid']=='y'?' checked':'')." onclick=ch(this)></input>ID Оператора<br>";
		if(count($fix_rep)>0) echo "<hr>";
		if(isset($fix_rep['call_sec']))			echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_cls'".($fix['call_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.вызова(сек)<br>";
		if(isset($fix_rep['call_min']))			echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_clm'".($fix['call_min']=='y'?' checked':'')." onclick=ch(this)></input>Длит.вызова(мин)<br>";
		if(isset($fix_rep['ivr_sec'])) 			echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_ivs'".($fix['ivr_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.IVR(сек)<br>";
		if(isset($fix_rep['queue_sec']))		echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_qus'".($fix['queue_sec']=='y'?' checked':'')." onclick=ch(this)></input>Время в очереди(сек)<br>";
		if(isset($fix_rep['alerting_sec']))		echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_als'".($fix['alerting_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.КПВ(сек)<br>";
		if(isset($fix_rep['connected_sec']))	echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_cts'".($fix['connected_sec']=='y'?' checked':'')." onclick=ch(this)></input>Длит.разговора(сек)<br>";
		if(isset($fix_rep['connected_min']))	echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_fix_ctm'".($fix['connected_min']=='y'?' checked':'')." onclick=ch(this)></input>Длит.разговора(мин)<br>";
		
		echo "</td>";
		echo "<td valign=top>";

		echo "<input name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_obj_all' type=checkbox".($all_obj_checked=='0'?' checked':'')." onclick=ch(this)></input><B>Поля отчета</B><hr>";

		foreach($tmp_obj as $key => $obj) {
			echo "<input type=checkbox name='prj_".$tmp_project_id."_frm_".$tmp_form_id."_obj_".$obj['id']."'".($obj['checked']<>''?' checked':'')." onclick=ch(this)></input>".$obj['name']."<br>";
		}
		echo "</td>";
		echo "</tr>";
		echo "</table><br>";
	}
	echo "</td></tr></table>";
	if($frm_count>0) {
		echo "<input type=hidden name=frm_count value='".$frm_count."'></input>";
		echo "<input type=submit name=save value='СОХРАНИТЬ'>";
		echo "</from>";
	}
	else {
		echo "<font size=4>".$tmp_project_name.".</font><br>";
		echo "<font color=red>В проекте нет форм!</font>";
	}
}

?>






























