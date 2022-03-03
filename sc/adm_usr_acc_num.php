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
	if(arr1.length==4) {
		var prj1=arr1[1];
		var cdn1=arr1[3];
		var all_checked=true;
		
	
		var node_list = document.getElementsByTagName('input');
	
		for (var i = 0; i < node_list.length; i++) {
			var obj2 = node_list[i];
			var arr2=obj2.name.split("_");
			if(arr2.length==4) {
				var prj2=arr2[1];
				var cdn2=arr2[3];
				if(prj2==prj1) {
					if(cdn1=='all' && cdn2!='all') {
						if(obj1.checked==true) obj2.checked=true;
						else obj2.checked=false;
					}
					if(cdn1!='all' && cdn2=='all') {
						if(obj1.checked==false) obj2.checked=false; 
					}
				if(obj2.checked==false && cdn2!='all') all_checked=false;
				}
			}
		}
		if(cdn1!='all' && all_checked==true) document.all['prj_'+prj1+'_cdn_all'].checked=true;
	}
}
</script>
<?php 
if (!isset($_SESSION['admin']) or $_SESSION['admin']<>1) {echo "<font color=red>Страница недоступна!</font>"; exit();} 
?>
<?php

$login_id=$_SESSION['edit_login']['id'];
$project_id=$_SESSION['edit_login']['project_id'];

if($login_id=='' or $project_id=='') exit();

extract($_REQUEST);

include("sc/sc_conn_string.php");

if(isset($save)) {
	$del=OCIParse($c,"delete from SC_ACC_CDN where login_id='".$login_id."' and project_id='".$project_id."'");
	OCIExecute($del,OCI_DEFAULT);
	foreach($_POST as $varname => $val) {
		$arr=explode("_",$varname);
		if(count($arr)==4) {
			$project_id=$arr[1];
			$cdn=$arr[3];
			if(isset($all[$project_id])) continue;
			if($cdn=='all') {
				//добавляем разрешение на доступ ко всем номерам
				//echo "project_id: ".$project_id."; cdn: all<br>";
				$ins=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) values (".$login_id.",".$project_id.",0,'all')");
				OCIExecute($ins,OCI_DEFAULT);
				//
				$all[$project_id]='y';
			}
			else {
				//
				//echo "project_id: ".$project_id."; cdn: ".$cdn."<br>";
				$ins=OCIParse($c,"insert into SC_ACC_CDN (login_id,project_id,form_id,phone) values (".$login_id.",".$project_id.",0,'".$cdn."')");
				OCIExecute($ins,OCI_DEFAULT);
				//
			}
		}
	}
	OCICommit($c);
}

//список номеров и проектов

$q_prj=OCIParse($c,"
select apr.project_id, pr.name project_name, decode(ac.phone,'all','y',NULL) checked
from SC_ACC_PROJECT apr, SC_PROJECTS pr, SC_ACC_CDN ac
where apr.login_id='".$login_id."' and apr.project_id='".$project_id."' and apr.view_rep=1
and ac.phone(+)='all'
and ac.login_id(+)=apr.login_id
and ac.project_id(+)=apr.project_id
and pr.id=apr.project_id
order by pr.name");

$q_cdn=OCIParse($c,"select ph.project_id,ph.phone,ph.phone_name, decode((select count(*) from SC_ACC_CDN ac where ac.project_id=ph.project_id and ac.phone=ph.phone and ac.login_id='".$login_id."'),0,null,'y') checked
from SC_PHONES ph
where ph.project_id=:project_id
order by phone");

//удаленные из проекта номера
$q_cdn2=OCIParse($c,"select ac.project_id,ac.phone, 'y' checked from SC_ACC_CDN ac where ac.project_id=:project_id and ac.login_id='".$login_id."' 
and ac.phone<>'all'
minus
select ph.project_id,ph.phone, 'y' from SC_PHONES ph
where ph.project_id=:project_id");

OCIExecute($q_prj,OCI_DEFAULT);
if(OCIFetch($q_prj)) {

	$tmp_project_id=OCIResult($q_prj,"PROJECT_ID");
	$tmp_project_name=OCIResult($q_prj,"PROJECT_NAME");
	$tmp_prj_checked=OCIResult($q_prj,"CHECKED");

	echo "<font size=4>".$tmp_project_name."</font>";

	echo "<form method=post>";
	echo "<table class=white_table>";

	echo "<tr><td>";
	echo "<input name='prj_".$tmp_project_id."_cdn_all' type=checkbox".($tmp_prj_checked=='y'?' checked':'')." onclick=ch(this)></input><B>ВСЕ НОМЕРА</B>";
	echo "</td><td><b>Проект</b></td><td><b>Название номера</b></td>
	</tr>";
	
	
	OCIBindByName($q_cdn,":project_id",$tmp_project_id);
	OCIExecute($q_cdn,OCI_DEFAULT);
	while (OCIFetch($q_cdn)) {
		$tmp_cdn=OCIResult($q_cdn,"PHONE");
		$tmp_phone_name=OCIResult($q_cdn,"PHONE_NAME");
		if($tmp_prj_checked=='y') $tmp_cdn_checked='y'; else $tmp_cdn_checked=OCIResult($q_cdn,"CHECKED");
		echo "<tr class='selectable_row'><td><input name='prj_".$tmp_project_id."_cdn_".$tmp_cdn."' type=checkbox".($tmp_cdn_checked=='y'?' checked':'')." onclick=ch(this)>".$tmp_cdn."</input></td>
		<td>".$tmp_project_name."</td><td>".$tmp_phone_name."</td></tr>";
	}
	//удаленные из проекта номера
	OCIBindByName($q_cdn2,":project_id",$tmp_project_id);
	OCIExecute($q_cdn2,OCI_DEFAULT);
	$i=0; while (OCIFetch($q_cdn2)) {$i++;
		//if($i==1) echo "<hr><font color=red>Удалённые из проекта:</font><br>";
		$tmp_cdn=OCIResult($q_cdn2,"PHONE");
		echo "<tr class='selectable_row'><td><input name='prj_".$tmp_project_id."_cdn_".$tmp_cdn."' type=checkbox checked onclick=ch(this)>".$tmp_cdn."</input><td colspan=2><font color=red>номер удален из проекта</font></td></tr>";
	}	
	echo "</td></tr>";
	echo "</table><br>";
	echo "<input type=submit name=save value='СОХРАНИТЬ'>";
	echo "</form>";
}
?>