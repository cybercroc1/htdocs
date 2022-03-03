<?php 
include("../../conf/starcall_conf/session.cfg.php"); 
if($_SESSION['user']['operator']<>'y') exit();

include("../../conf/starcall_conf/conn_string.cfg.php");
//разблокировка записей, очистка переменных
OCIExecute(OCIParse($c,"update STC_BASE t set lock_user='', lock_date='' where lock_user=".$_SESSION['user']['id']." and lock_date is not null"));
if(isset($_SESSION['survey'])) unset($_SESSION['survey']);
//

if(isset($_POST['project_id'])) $_SESSION['survey']['project']['id']=$_POST['project_id'];
else $_SESSION['survey']['project']['id']='';
if($_SESSION['survey']['project']['id']=='' and isset($_SESSION['adm']['project']['id'])) $_SESSION['survey']['project']['id']=$_SESSION['adm']['project']['id'];
if($_SESSION['survey']['project']['id']=='') echo "<script>document.location='survey.projects.php';</script>";
?>
<!DOCTYPE html>
<head>
<title>StarCall</title>
</head>
<script>
//ежеминутное обновление сессии
aj();
window.setInterval(aj,60000);
function aj()
{
     if (window.XMLHttpRequest)
     {
          req = new XMLHttpRequest();
     }
     else
     {
          if (window.ActiveXObject)
          {
               try
               {
                    req = new ActiveXObject('Msxml2.XMLHTTP'); 
               }
			   catch (e) {}
               try
               {
                    req = new ActiveXObject('Microsoft.XMLHTTP');  
               }
			   catch(e) {}
          }
     }   
     req.open('GET', 'session.refresh.php?survey&with_lock', true); //with_lock - с подтверждением блокировки текущих записей
     //alert('сессия оператора подтверждена');
	 //req.onreadystatechange = function() {setTimeout(aj, 5000)}
     req.send(null);
}
</script>
</head>


	<frameset rows="140,*,30">	 

	  <frame src=survey.call.php name=surveyMainTopFrame id=surveyTopFrame title=surveyTopFrame noresize="noresize">

 <frameset cols=*,100>
	  
	  <frameset rows="*,40">
		  <frame src=survey.ank.php name=surveyAnkFrame id=surveyAnkFrame title=surveyAnkFrame>
  
		  <frame src=survey.buttons.php name=surveyButtonFrame id=surveyButtonFrame title=surveyButtonFrame>
	  </frameset>
	  
	<frame src=survey.aboninfo.php name=surveyInfoFrame id=surveyInfoFrame title=surveyInfoFrame>
</frameset>  
	  
 	  <frame src=survey.action.php name=surveyLogFrame id=surveyLogFrame title=surveyLogFrame>
	</frameset>
<noframes></noframes>

