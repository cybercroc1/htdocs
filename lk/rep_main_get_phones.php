<?php 
require_once "auth.php";
require_once "lk/lk_ora_conn_string.php";
//получение селектов для выбора номера
if(isset($_POST['get_phones'])) {
	$cdns=array();
	if($_SESSION['admin']=='1' or $_SESSION['allow_view_all_reports']==1) {
		//доступ к номерам
		$q=OCIParse($c,"select p.phone, p.phone_name from SC_PHONES p
		where project_id='".$_SESSION['project']['id']."'
		order by p.phone_name");		
		OCIExecute($q);
		while(OCIFetch($q)) {
			$cdns[OCIResult($q,"PHONE")]=OCIResult($q,"PHONE_NAME");
		}		
	}
	else {
		//доступ к номерам
		$q=OCIParse($c,"select p.phone,p.phone_name,ac.phone ac_phones from SC_ACC_CDN ac
		left join sc_phones p on p.phone = decode(ac.phone,'all',p.phone,ac.phone)
		where ac.project_id='".$_SESSION['project']['id']."' and p.project_id='".$_SESSION['project']['id']."'
		and ac.login_id='".$_SESSION['login_id']."' and decode(ac.form_id,0,'".$_POST['sel_form']."',ac.form_id)='".$_POST['sel_form']."'
		order by p.phone_name");		
		OCIExecute($q);
		$ac_phones='';
		while(OCIFetch($q)) {
			$cdns[OCIResult($q,"PHONE")]=OCIResult($q,"PHONE_NAME");
			$ac_phones=OCIResult($q,"AC_PHONES");
		}			
	}
	echo "<option value=all>Все номера</option>";
	foreach($cdns as $ph => $name) {echo "<option value='".$ph."'".($ph==$sel_phone?" selected":"").">".$ph."</option>";}	
	if($ac_phones=='all') echo "<option value=null".($ph=="null"?" selected":"").">Нет номера</option>";
	exit();
}
?>