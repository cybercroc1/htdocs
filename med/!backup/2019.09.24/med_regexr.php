<?php 
set_time_limit(0);
session_name('medc');
session_start();

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");// ���� � �������
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
 // ������ ��������������
header("Cache-Control: no-store, no-cache, must-revalidate");// HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");// HTTP/1.0
require_once 'funct.php';
?>

<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
    <link rel="stylesheet" type="text/css" href="./billing.css">
</head>
<?php 
extract($_REQUEST);

if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">C������� ����������!</p>'; exit();
}

if(!isset($regexr_id)) $regexr_id='';

include("med/conn_string.cfg.php");	
include("mailbody2text.php");


if(!isset($test_go) and !isset($save) and !isset($delete) and !isset($parse_go) and !isset($show_unknown) and !isset($delete_unknown)) {
	
	$preg_from=''; 		$mod_from='im';
	$preg_subj=''; 		$mod_subj='im';
	$preg_body=''; 		$mod_body='im';
	$preg_outer_id='';	$mod_outer_id='im'; $location_outer_id='subj';
	
	$from_inverse='';
	$subj_inverse='';
	$body_inverse='';
	
	$preg_phone='(������� *[:-]? *(?:<[^>]*>)*)([^\n^<]*)'; $mod_phone='im';
	$preg_fio='(��� *[:-]? *(?:<[^>]*>)*)([^\n^<]*)'; $mod_fio='im';
	
	$coment='';
	
	$rule_action='create_order';
	
	//if(!isset($test_period)) $test_period=1;
	if(!isset($parse_period)) $parse_period=3;
	
	$source_id='';
	$service_id='';
	$rule_priority='100';
	
	echo "<body topmargin='8'>";
	//echo "<form method=post action='?'>";
	echo "<form method=post target=res_ifr>";
	$q=OCIParse($c,"select r.id,r.source_auto_id,r.service_id,so.name source_name,se.name service_name,
	r.preg_match_from,r.preg_match_subj,r.preg_match_body,
	r.preg_from_inverse,r.preg_subj_inverse,r.preg_body_inverse,
	r.coment,
	r.preg_find_phone,
	r.preg_find_fio,
	r.action,
	r.ord,
	r.LOCATION_OUTER_ID,
	r.PREG_FIND_OUTER_ID
	from MAIL_REGEXR r, source_auto so, services se
	where so.id=r.source_auto_id and se.id=r.service_id
	order by so.name, se.name");
	OCIExecute($q);
	//echo "�������: <select name=regexr_id onchange=sel_regexr.click()><option></option>";
	echo "�������: <select name=regexr_id onchange=if(this.value!=''){document.location='?regexr_id='+this.value}else{check_param()}><option value=''>������� �������</option>";
	while(OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$regexr_id?" selected":"").">".OCIResult($q,"SOURCE_NAME")." | ".OCIResult($q,"SERVICE_NAME")." (id:".OCIResult($q,"ID").")</option>";
		if(OCIResult($q,"ID")==$regexr_id) {
			$from_rgx=OCIResult($q,"PREG_MATCH_FROM");
			$subj_rgx=OCIResult($q,"PREG_MATCH_SUBJ");
			$body_rgx=OCIResult($q,"PREG_MATCH_BODY");
			
			$from_inverse=OCIResult($q,"PREG_FROM_INVERSE");
			$subj_inverse=OCIResult($q,"PREG_SUBJ_INVERSE");
			$body_inverse=OCIResult($q,"PREG_BODY_INVERSE");
			
			$phone_rgx=OCIResult($q,"PREG_FIND_PHONE");
			$fio_rgx=OCIResult($q,"PREG_FIND_FIO");
			$outer_id_rgx=OCIResult($q,"PREG_FIND_OUTER_ID");
			
			$source_id=OCIResult($q,"SOURCE_AUTO_ID");
			$service_id=OCIResult($q,"SERVICE_ID");
			
			$coment=OCIResult($q,"COMENT");
			
			$preg_from=substr($from_rgx,strpos($from_rgx,"/")+1,strrpos($from_rgx,"/")-strpos($from_rgx,"/")-1); $mod_from=substr($from_rgx,strrpos($from_rgx,"/")+1);
			$preg_subj=substr($subj_rgx,strpos($subj_rgx,"/")+1,strrpos($subj_rgx,"/")-strpos($subj_rgx,"/")-1); $mod_subj=substr($subj_rgx,strrpos($subj_rgx,"/")+1);
			$preg_body=substr($body_rgx,strpos($body_rgx,"/")+1,strrpos($body_rgx,"/")-strpos($body_rgx,"/")-1); $mod_body=substr($body_rgx,strrpos($body_rgx,"/")+1);
			
			$preg_phone=substr($phone_rgx,strpos($phone_rgx,"/")+1,strrpos($phone_rgx,"/")-strpos($phone_rgx,"/")-1); $mod_phone=substr($phone_rgx,strrpos($phone_rgx,"/")+1);
			$preg_fio=substr($fio_rgx,strpos($fio_rgx,"/")+1,strrpos($fio_rgx,"/")-strpos($fio_rgx,"/")-1); $mod_fio=substr($fio_rgx,strrpos($fio_rgx,"/")+1);
			$preg_outer_id=substr($outer_id_rgx,strpos($outer_id_rgx,"/")+1,strrpos($outer_id_rgx,"/")-strpos($outer_id_rgx,"/")-1); $mod_outer_id=substr($outer_id_rgx,strrpos($outer_id_rgx,"/")+1);
			$location_outer_id=OCIResult($q,"LOCATION_OUTER_ID");
			
			$rule_action=OCIResult($q,"ACTION");
			$rule_priority=OCIResult($q,"ORD");
		}
	}
	echo "</select><br/>";

	echo "<form method=post target=res_ifr>";
	$q=OCIParse($c,"select id,name from MAIL_REGEXR_ACTIONS order by name");
	OCIExecute($q);
	echo "��������� (��� ������, ��� ����): <input type=text size=4 name=priority value='".$rule_priority."'></input> "; 
	echo "��������: <select name=rule_action>";
	while(OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$rule_action?" selected":"").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>";
	//echo "<input type=submit name=sel_regexr style='display:none' value=��>";
	if($regexr_id<>'') echo " | <input type=submit name=delete style='color:red' value='������� ������� �������'>";
	//echo "</form>";
	
	//echo "<form method=post target=res_ifr>";
	//echo "<input type=hidden name=regexr_id value='".$regexr_id."'>";
	echo "<table width='100%'>";
	echo "<tr>";
	echo "<th>preg_match ��� FROM</th><th>preg_match ��� SUBJECT</th><th>preg_match ��� BODY</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><textarea name=preg_from style='width:100%'>".$preg_from."</textarea><br>
		  mod:<input type=test size=2 name=mod_from value='".$mod_from."'> 
		  inverse: <input type=checkbox name=from_inverse".($from_inverse=='on'?' checked':'')."></td>
		  <td><textarea name=preg_subj style='width:100%'>".$preg_subj."</textarea><br>
		  mod:<input type=test size=2 name=mod_subj value='".$mod_subj."'> 
		  inverse: <input type=checkbox name=subj_inverse".($subj_inverse=='on'?' checked':'')."></td>
		  <td><textarea name=preg_body style='width:100%'>".$preg_body."</textarea><br>
		  mod:<input type=test size=2 name=mod_body value='".$mod_body."'> 
		  inverse: <input type=checkbox name=body_inverse".($body_inverse=='on'?' checked':'')."></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan=3>�����������<br><textarea name=coment rows=3 style='width:100%'>".$coment."</textarea></td>";
	echo "</tr>";
	echo "</table>";
	echo "<table width='100%'>";
	echo "<tr>";
	echo "<td><nobr>preg_match ��� ������ ����� (2-� ������):</td>
		  <td width=100%><input type=text style='width:100%' name=preg_fio value='".$preg_fio."'></td>
		  <td><nobr>mod:<input type=test size=2 name=mod_fio value='".$mod_fio."'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><nobr>preg_match ��� ������ �������� (2-� ������):</td>
		  <td width=100%><input type=text style='width:100%' name=preg_phone value='".$preg_phone."'></td>
		  <td><nobr>mod:<input type=test size=2 name=mod_phone value='".$mod_phone."'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td><nobr>preg_match ��� ������ �������� �������������� (2-� ������):</td>
		  <td width=100%><input type=text style='width:100%' name=preg_outer_id value='".$preg_outer_id."'></td>
		  <td><nobr>mod:<input type=test size=2 name=mod_outer_id value='".$mod_outer_id."'> 
		  
		   ��� ������:<select name=location_outer_id>
		  <option value=from".($location_outer_id=='from'?' selected':'').">�����������</option>
		  <option value=subj".($location_outer_id=='subj'?' selected':'').">����</option>
		  <option value=body".($location_outer_id=='body'?' selected':'').">����</option>
		  </select>
		  </td>";
	echo "</tr>";
	echo "</table>";
	//�� ��������� <input type=number style=width:80 name=test_period value='".$test_period."'> ���� (1 - ������ �� �������, 2 - �� ����� � ������� � �.�.)";
	echo "<hr>";
	
	$q=OCIParse($c,"select id,name from SOURCE_AUTO t where t.source_type=2 order by name");
	OCIExecute($q);
	echo "<table border=0>";
	echo "<tr>";
	echo "<td>";
	//echo "��������:</td><td><select name=source_id".($regexr_id<>''?" disabled":"")." onchange=check_param()><option></option>";
	echo "��������: <select name=source_id onchange=check_param()><option value=''></option>";
	while(OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$source_id?" selected":"").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td>";
	$q=OCIParse($c,"select id,name from SERVICES t
	where id>=0 
	order by name");
	OCIExecute($q);
	//echo " | ������: <select name=service_id".($regexr_id<>''?" disabled":"")."><option></option>";
	echo " | ������: <select name=service_id><option></option>";
	while(OCIFetch($q)) {
		echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$service_id?" selected":"").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select>";		
	echo " | <input type=submit name=save style='color:blue' value='��������� ������� �������'>";
	echo "</td></tr>";
	echo "<tr id=tr_new_source><td colspan=3>������� �����:<input type=text style=width:50% name=new_source_name></input></td></tr>";
	echo "</table>";
	
	echo "<hr>";
	echo "�� ��������� <input type=number style=width:80 name=parse_period value='".$parse_period."'> ����: ";
	echo "<input type=submit name=test_go style=color:green value='���� �������� �������'> | "; 
	echo "<input type=submit name=show_unknown style='color:green' value='�������� ��� �����������'> | ";
	echo "<input type=submit name=parse_go style='color:blue' value='���������� ��� �����������'> | ";
	echo "<input type=submit name=delete_unknown style='color:red' value='������� ��� �����������'>";
	echo "</form><hr>";
	//echo "���������:";
	echo "<script>
	function check_param() {
		if(document.all.regexr_id.value!='') {
			document.getElementById('tr_new_source').style.display='none';
			document.all.source_id.disabled=true;
			document.all.service_id.disabled=true;
		}
		else{
			document.all.source_id.disabled=false;
			document.all.service_id.disabled=false;
			if(document.all.source_id.value!='') {
				document.getElementById('tr_new_source').style.display='none';
			}
			else {
				document.getElementById('tr_new_source').style.display='';
			}
		}
	}
	check_param();
	</script>";
}
//====================================================================================================================
if(isset($parse_go)) {
	include("phone_conv_single.php");
	echo "<body style='margin:0;padding:0'>";
	echo "���������:";
	echo "<textarea rows='10' style='width:100%;height:80%' wrap='on'>";

	$read_count=0;
	$found_count=0;
	$del_count=0;
	$notfound_count=0;

	$newlines=array(chr(13).chr(10),chr(10).chr(13),chr(13),chr(10),chr(9));
	$whitespaces=array('&nbsp;',chr(160));
	
	//������ � �����������
	$q=OCIParse($c,"select t.id,t.preg_match_from,t.preg_match_subj,t.preg_match_body,
	t.preg_from_inverse,t.preg_subj_inverse,t.preg_body_inverse,
	t.source_auto_id,t.service_id,
	t.preg_find_phone,t.preg_find_fio,
	location_outer_id,preg_find_outer_id, --19.09.19
	t.action,
	sa.name source_name, sr.name service_name
	from MAIL_REGEXR t, source_auto sa, services sr
	where sa.id=t.source_auto_id and sr.id=t.service_id
	and t.action<>'off'
	order by t.ord,t.id");

	OCIExecute($q);
	$i=0; while(OCIFetch($q)) { 
		$rgx_ids[$i]=OCIResult($q,"ID");
		$from_rgxs[$i]=OCIResult($q,"PREG_MATCH_FROM");
		$subj_rgxs[$i]=OCIResult($q,"PREG_MATCH_SUBJ");
		$body_rgxs[$i]=OCIResult($q,"PREG_MATCH_BODY");

		if(OCIResult($q,"PREG_FROM_INVERSE")=='on') $from_bools[$i]=FALSE; else $from_bools[$i]=TRUE;
		if(OCIResult($q,"PREG_SUBJ_INVERSE")=='on') $subj_bools[$i]=FALSE; else $subj_bools[$i]=TRUE;
		if(OCIResult($q,"PREG_BODY_INVERSE")=='on') $body_bools[$i]=FALSE; else $body_bools[$i]=TRUE;		

		$fio_rgxs[$i]=OCIResult($q,"PREG_FIND_FIO");
		$phone_rgxs[$i]=OCIResult($q,"PREG_FIND_PHONE");
		$location_outer_ids[$i]=OCIResult($q,"LOCATION_OUTER_ID"); //19.09.19
		$outer_id_rgxs[$i]=OCIResult($q,"PREG_FIND_OUTER_ID"); //19.09.19
		
		$source_ids[$i]=OCIResult($q,"SOURCE_AUTO_ID");
		$source_names[$i]=OCIResult($q,"SOURCE_NAME");
		$service_ids[$i]=OCIResult($q,"SERVICE_ID");
		$service_names[$i]=OCIResult($q,"SERVICE_NAME");
		
		$actions[$i]=OCIResult($q,"ACTION");
		$i++;
	}	
	
	$q_curdate=OCIParse($c,"select to_char(sysdate,'YYYYMMDDHH24MISS') curdate from dual");
	
	$ins=OCIParse($c,"insert into call_base (id,date_call,call_theme_id,source_auto_id,source_man_id,call_type_id,service_id,result_id,status_id,
	last_change,lead_id,source_type_id,client_name,phone_mob,phone_mob_norm
	,outer_order_id --19.09.19
	)
	values (SEQ_CALL_BASE_ID.nextval,sysdate,1,:source_id,0,1,:service_id,3,1,
	to_date(:curdate,'YYYYMMDDHH24MISS'),:lead_id,2,:fio,:phone_seg,:phone_norm
	,:outer_id --19.09.19
	)
	returning id into :call_base_id");
	
	$ins2=OCIParse($c,"insert into CALL_BASE_HIST (id,base_id,date_det,status_id,date_start)
	values (SEQ_CALL_BASE_HIST_ID.nextval,:call_base_id,to_date(:curdate,'YYYYMMDDHH24MISS'),1,to_date(:curdate,'YYYYMMDDHH24MISS'))");

	$upd=OCIParse($c,"update MAIL_LEADCOLLECTOR set pars_source_id=:source_id, pars_service_id=:service_id, pars_regexr_id=:rgx_id, call_base_id=:call_base_id, 
	pars_fio=:fio, pars_phone=:phone_norm
	,pars_outer_id=:outer_id --19.09.19
	where id=:lead_id");
	
	$q=OCIParse($c,"select t.id,t.recieve_date,t.h_from,t.h_subject,t.mail_body,t.mail_body_text,t.p_content_type from MAIL_LEADCOLLECTOR t
  where recieve_date>=trunc(sysdate+1-".$parse_period.")
  and pars_source_id is null and call_base_id is null");
	OCIExecute($q);
	while(OCIFetch($q)) {
		$read_count++;
		$lead_id=OCIResult($q,"ID");
		
		$from=OCIResult($q,"H_FROM");
		$subj=OCIResult($q,"H_SUBJECT");
		//$body_type=OCIResult($q,"P_CONTENT_TYPE");
		if(OCIResult($q,"MAIL_BODY")<>'') {
			$body=OCIResult($q,"MAIL_BODY")->load();
		}
		else $body='';
		$body_text=OCIResult($q,"MAIL_BODY_TEXT");
		
		$isfound='n';
		$deleted='n';
		foreach($source_ids as $key => $val) {
			if(preg_match($from_rgxs[$key],$from)==$from_bools[$key] and preg_match($subj_rgxs[$key],$subj)==$subj_bools[$key] and preg_match($body_rgxs[$key],$body)==$body_bools[$key]) {
				$found_count++;
				$call_base_id='';
				$isfound='y';
				
				echo "FROM: ".$from."\n";
				echo "-------------------------------------\n";
				echo "SUBJECT: ".$subj."\n";
				echo "-------------------------------------\n";
				echo "BODY: \n".$body."\n";
				echo "-------------------------------------------------------------------------------------------------------------------------\n";	
				echo "BODY TEXT: \n".$body_text."\n";
				echo "----------------------------------------------------------------------------------------------\n";
				echo "������� ���������: ".$source_names[$key]." | ".$service_names[$key]."\n";

				$fio='';
				if(preg_match($fio_rgxs[$key],html_entity_decode(str_replace($whitespaces,' ',$body)),$matches)) {	
					
					if(isset($matches[2])) {
						$fio = trim(str_replace($newlines,' ',$matches[2]));
                        $fio = substr($fio,0,64);
					}
				}
				echo "���:".$fio."\n";

				$phone='';
				if(preg_match($phone_rgxs[$key], html_entity_decode(str_replace($whitespaces,' ',$body)), $matches)) {
					if(isset($matches[2])) {
						$phone=trim(str_replace($newlines,' ',$matches[2]));
					}
				}
				echo "������� �� ��������������:".$phone."\n";

				//�������������� �������� � �������� ������ (���������� �����)
				$phone_norm=phone_norm_single($phone,"ru_dial");
				echo "������� ����� ��������������:".$phone_norm."\n";
				
				//��������������� ��� call_base
				$phone_seg=phone_segment($phone_norm,NULL);
				echo "������� ����������������:".$phone_seg."\n";
				
				//19.09.19
				$outer_id='';
				if		($location_outer_ids[$key]=='from') $tmp=$from;
				else if	($location_outer_ids[$key]=='subj') $tmp=$subj;
				else if	($location_outer_ids[$key]=='body') $tmp=$body;
				else $tmp='';
				if(preg_match($outer_id_rgxs[$key], html_entity_decode(str_replace($whitespaces,' ',$tmp)),$matches)) {
					if(isset($matches[2])) {
						$outer_id = trim(str_replace($newlines,' ',$matches[2]));
						$outer_id = substr($outer_id,0,100);
					}
				}
				echo "������� �������������:".$outer_id."\n";
				//
				
				$upd_rgx=OCIParse($c,"update mail_regexr set use_date=sysdate where id='".$rgx_ids[$key]."'");
				OCIExecute($upd_rgx);
				
				if($actions[$key]=='delete_mess') {
					$del=OCIParse($c,"delete from MAIL_LEADCOLLECTOR where id='".$lead_id."'");
					if(OCIExecute($del)) {
						OCICommit($c);
						$deleted='y';
						$del_count++;
						echo "������ �������!\n";
						break;
					}
				}				
				elseif($actions[$key]=='create_order') {
					OCIExecute($q_curdate);
					OCIFetch($q_curdate);
					$curdate=OCIResult($q_curdate,"CURDATE");
					
					OCIBindByName($ins,":curdate",$curdate);
					OCIBindByName($ins,":source_id",$source_ids[$key]);
					OCIBindByName($ins,":service_id",$service_ids[$key]);
					OCIBindByName($ins,":lead_id",$lead_id);
					OCIBindByName($ins,":fio",$fio);
					OCIBindByName($ins,":phone_seg",$phone_seg);
					OCIBindByName($ins,":phone_norm",$phone_norm);
					OCIBindByName($ins,":outer_id",$outer_id); //19.09.19					
					OCIBindByName($ins,":call_base_id",$call_base_id,125);
					OCIExecute($ins);	
					
					OCIBindByName($ins2,":curdate",$curdate);
					OCIBindByName($ins2,":call_base_id",$call_base_id);
					OCIExecute($ins2);
					
					OCIBindByName($upd,":source_id",$source_ids[$key]);
					OCIBindByName($upd,":service_id",$service_ids[$key]);
					OCIBindByName($upd,":lead_id",$lead_id);
					OCIBindByName($upd,":rgx_id",$rgx_ids[$key]);
					OCIBindByName($upd,":call_base_id",$call_base_id);
					OCIBindByName($upd,":fio",$fio);
					OCIBindByName($upd,":phone_norm",$phone_norm);
					OCIBindByName($upd,":outer_id",$outer_id);	//19.09.19				
					OCIExecute($upd);
					OCICommit($c);
					
					echo "��������� � ��!\n";
					break;
				}
			echo "============================================================================================\n";	
			}
		}
	}
	echo "</textarea>";
	echo "���������: ".$read_count."; ����������: ".$found_count."; �������: ".$del_count."; �� ����������: ".$notfound_count.".";
}
if(isset($show_unknown)) {
	include("phone_conv_single.php");
	echo "<body style='margin:0;padding:0'>";
	echo "��������� (�������� 100 ��������� �����):";
	echo "<textarea rows='10' style='width:100%;height:80%' wrap='on'>";	
	$read_count=0;
	$q=OCIParse($c,"select t.id,to_char(t.recieve_date,'DD.MM.YYYY HH24:MI:SS') recieve_date,t.h_from,t.h_subject,t.mail_body,t.mail_body_text,t.p_content_type from MAIL_LEADCOLLECTOR t
  where recieve_date>=trunc(sysdate+1-".$parse_period.")
  and pars_source_id is null and call_base_id is null");
	OCIExecute($q);
	while(OCIFetch($q)) {
		$read_count++;
		$lead_id=OCIResult($q,"ID");
		
		$from=OCIResult($q,"H_FROM");
		$subj=OCIResult($q,"H_SUBJECT");
		$body_type=OCIResult($q,"P_CONTENT_TYPE");
		if(OCIResult($q,"MAIL_BODY")<>'') {
			$body=OCIResult($q,"MAIL_BODY")->load();
		}
		else $body='';
		$body_text=OCIResult($q,"MAIL_BODY_TEXT");

		//$max_len=4000;
		//$body_text=mailbody2text($body,$body_type,$max_len);
		
		echo $read_count.") ".OCIResult($q,"RECIEVE_DATE")." id:".OCIResult($q,"ID")."\n";
		echo "FROM: ".$from."\n";
		echo "-------------------------------------\n";
		echo "SUBJECT: ".$subj."\n";
		echo "-------------------------------------\n";
		echo "BODY: ".$body."\n";
		echo "-------------------------------------\n";
		echo "BODY TEXT: \n".$body_text."\n";
		echo "============================================================================================\n";		
		//if($read_count>=100) break;
	}
	echo "</textarea>";
	echo "���������: ".$read_count;
}

if(isset($delete_unknown)) {
	include("phone_conv_single.php");
	echo "<body style='margin:0;padding:0'>";
	echo "��������� (�������� 100 ��������� �����):";
	echo "<textarea rows='10' style='width:100%;height:80%' wrap='on'>";	
	$read_count=0;
	$q=OCIParse($c,"select t.id,to_char(t.recieve_date,'DD.MM.YYYY HH24:MI:SS') recieve_date,t.h_from,t.h_subject,t.mail_body,t.mail_body_text,t.p_content_type from MAIL_LEADCOLLECTOR t
	where recieve_date>=trunc(sysdate+1-".$parse_period.")
	and pars_source_id is null and call_base_id is null");
	
	$del=OCIParse($c,"delete from MAIL_LEADCOLLECTOR t where id=:lead_id");
	
	OCIExecute($q);
	while(OCIFetch($q)) {
		$read_count++;
		$lead_id=OCIResult($q,"ID");
		
		$from=OCIResult($q,"H_FROM");
		$subj=OCIResult($q,"H_SUBJECT");
		//$body_type=OCIResult($q,"P_CONTENT_TYPE");
		if(OCIResult($q,"MAIL_BODY")<>'') {
			$body=OCIResult($q,"MAIL_BODY")->load();
		}
		else $body='';
		$body_text=OCIResult($q,"MAIL_BODY_TEXT");
		
		echo $read_count."/".$read_count." ".OCIResult($q,"RECIEVE_DATE")." id:".OCIResult($q,"ID")."\n";
		echo "FROM: ".$from."\n";
		echo "-------------------------------------\n";
		echo "SUBJECT: ".$subj."\n";
		echo "-------------------------------------\n";
		echo "BODY: ".$body."\n";
		echo "-------------------------------------\n";
		echo "BODY TEXT: \n".$body_text."\n";
		echo "============================================================================================\n";		
		
		OCIBindByName($del,":lead_id",$lead_id);
		OCIExecute($del);
		OCICommit($c);
		echo "�������!\n";
		echo "============================================================================================\n";	
	}
	echo "</textarea>";
	echo "�������: ".$read_count;
}

if(isset($test_go)) {
	include("phone_conv_single.php");
	$read_count=0;
	$found_count=0;
	
	$from_rgx="/".$preg_from."/".$mod_from;
	$subj_rgx="/".$preg_subj."/".$mod_subj;
	$body_rgx="/".$preg_body."/".$mod_body;
	
	$phone_rgx="/".$preg_phone."/".$mod_phone;
	$fio_rgx="/".$preg_fio."/".$mod_fio;
	$outer_id_rgx="/".$preg_outer_id."/".$mod_outer_id;
	
	if(!isset($from_inverse)) $from_inverse=''; 
	if(!isset($subj_inverse)) $subj_inverse='';
	if(!isset($body_inverse)) $body_inverse='';		
	
	if($from_inverse=='on') $from_bool=FALSE; else $from_bool=TRUE;
	if($subj_inverse=='on') $subj_bool=FALSE; else $subj_bool=TRUE;
	if($body_inverse=='on') $body_bool=FALSE; else $body_bool=TRUE;	

	$newlines=array(chr(13).chr(10),chr(10).chr(13),chr(13),chr(10),chr(9));
	$whitespaces=array('&nbsp;',chr(160));
	
	echo "<body style='margin:0;padding:0'>";
	echo "��������� (�������� 100 ��������� �����):";
	echo "<textarea rows='10' style='width:100%;height:80%' wrap='on'>";
	
	$q=OCIParse($c,"select t.id,to_char(t.recieve_date,'DD.MM.YYYY HH24:MI:SS') recieve_date,t.h_from,t.h_subject,t.mail_body,t.mail_body_text,t.p_content_type from MAIL_LEADCOLLECTOR t
	where recieve_date>=trunc(sysdate+1-".$parse_period.")
	order by recieve_date desc
	");
    //and recieve_date>trunc(sysdate) and rownum<=100
	OCIExecute($q);
	while(OCIFetch($q)) {
		$read_count++;
		$id=OCIResult($q,"ID");
		$from=OCIResult($q,"H_FROM");
		$subj=OCIResult($q,"H_SUBJECT");
		$body_type=OCIResult($q,"P_CONTENT_TYPE");
		if(OCIResult($q,"MAIL_BODY")<>'') {
			$body=OCIResult($q,"MAIL_BODY")->load();
		}
		else $body='';
		$body_text=OCIResult($q,"MAIL_BODY_TEXT");
		
		//$max_len=4000;
		//$body_text=mailbody2text($body,$body_type,$max_len);
		
		if(preg_match($from_rgx,$from)==$from_bool and preg_match($subj_rgx,$subj)==$subj_bool and preg_match($body_rgx,$body)==$body_bool) {
			$found_count++;
			echo $found_count."/".$read_count." ".OCIResult($q,"RECIEVE_DATE")." id:".OCIResult($q,"ID")."\n";
			echo "FROM: ".$from."\n";
			echo "-------------------------------------\n";
			echo "SUBJECT: ".$subj."\n";
			echo "-------------------------------------\n";
			echo "BODY: \n".$body."\n";
			echo "-------------------------------------\n";
			echo "BODY TEXT: \n".$body_text."\n";
			echo "============================================================================================\n";

			$fio='';
			if(preg_match($fio_rgx, html_entity_decode(str_replace($whitespaces,' ',$body)),$matches)) {
				if(isset($matches[2])) {
					$fio = trim(str_replace($newlines,' ',$matches[2]));
                    $fio = substr($fio,0,64);
				}
			}
			echo "���:".$fio."\n";
			
			echo "-------------------------------------\n";	
			$phone='';
			if(preg_match($phone_rgx, html_entity_decode(str_replace($whitespaces,' ',$body)), $matches)) {
				if(isset($matches[2])) {
					$phone=trim(str_replace($newlines,' ',$matches[2]));
				}
			}
			echo "������� �� ��������������:".$phone."\n";

			//�������������� �������� � �������� ������ (���������� �����)
			$phone_norm=phone_norm_single($phone,"ru_dial");
			echo "������� ����� ��������������:".$phone_norm."\n";
				
			//��������������� ��� call_base
			$phone_seg=phone_segment($phone_norm,NULL);
			echo "������� ����������������:".$phone_seg."\n";			
			
			echo "-------------------------------------\n";	
			$outer_id='';
			
			if		($location_outer_id=='from') $tmp=$from;
			else if	($location_outer_id=='subj') $tmp=$subj;
			else if	($location_outer_id=='body') $tmp=$body;
			else $tmp='';
			
			if(preg_match($outer_id_rgx, html_entity_decode(str_replace($whitespaces,' ',$tmp)),$matches)) {
				if(isset($matches[2])) {
					$outer_id = trim(str_replace($newlines,' ',$matches[2]));
                    $outer_id = substr($outer_id,0,64);
				}
			}
			echo "������� �������������:".$outer_id."\n";
			
			echo "============================================================================================\n";
			echo "============================================================================================\n";
		}
	if($found_count>=100) break;
	}
	//echo $count;
	echo "</textarea>";
}
if(isset($save)) {
	
	if($preg_from=='' and $preg_subj=='' and $preg_body=='') {
		echo "<font color=red><b>������ ����������! �� ������ �� ������ ����������� ���������</b></font>";
		exit();
	}
	
	$from_rgx="/".$preg_from."/".$mod_from;
	$subj_rgx="/".$preg_subj."/".$mod_subj;
	$body_rgx="/".$preg_body."/".$mod_body;

	if(!isset($from_inverse)) $from_inverse=''; 
	if(!isset($subj_inverse)) $subj_inverse='';
	if(!isset($body_inverse)) $body_inverse='';	
	
	$phone_rgx="/".$preg_phone."/".$mod_phone;
	$fio_rgx="/".$preg_fio."/".$mod_fio;
	$outer_id_rgx="/".$preg_outer_id."/".$mod_outer_id;
	
	if(!isset($new_source_name)) $new_source_name=''; else $new_source_name=trim($new_source_name);
	
	if($regexr_id=='') {
		
		if($source_id=='' and $new_source_name=='') {
			echo "<font color=red><b>������ ����������! �� ������ ��������</b></font>";
			exit();		
		}
		if($service_id=='') {
			echo "<font color=red><b>������ ����������! �� ������� ������</b></font>";
			exit();		
		}
		
		if($source_id=='') {
			$q=OCIParse($c,"select id from SOURCE_AUTO t where t.name=:source_name and source_type=2");
			OCIBindByname($q,":source_name",$new_source_name);
			OCIExecute($q); 
			if(OCIFetch($q)) {
				$source_id=OCIResult($q,"ID");
			}
			else { 
				$ins=OCIParse($c,"insert into SOURCE_AUTO (id,name,source_type) values (SEQ_SOURCE_AUTO_ID.nextval,:source_name,2) returning id into :id");
				OCIBindByName($ins,":source_name",$new_source_name);
				OCIBindByName($ins,":id",$source_id,16);
				OCIExecute($ins,OCI_DEFAULT);
			}
		}
	
		$ins=OCIParse($c,"insert into MAIL_REGEXR r (id,ord,preg_match_from,preg_match_subj,preg_match_body,preg_from_inverse,preg_subj_inverse,preg_body_inverse,
		source_auto_id,service_id,coment,preg_find_phone,preg_find_fio,location_outer_id,preg_find_outer_id,action,create_date,change_date)
		values (SEQ_MAIL_REGEXR_ID.NEXTVAL,:ord,
		:preg_match_from,:preg_match_subj,:preg_match_body,:preg_from_inverse,:preg_subj_inverse,:preg_body_inverse,
		'".$source_id."','".$service_id."',:coment,:preg_find_phone,:preg_find_fio,'".$location_outer_id."',:preg_find_outer_id,'".$rule_action."',sysdate,sysdate)
		returning id into :id");
		
		if(!is_numeric($priority)) $priority=100; 
		
		OCIBindByName($ins,":ord",$priority);
		
		OCIBindByName($ins,":preg_match_from",$from_rgx);
		OCIBindByName($ins,":preg_match_subj",$subj_rgx);
		OCIBindByName($ins,":preg_match_body",$body_rgx);
		
		OCIBindByName($ins,":preg_from_inverse",$from_inverse);
		OCIBindByName($ins,":preg_subj_inverse",$subj_inverse);
		OCIBindByName($ins,":preg_body_inverse",$body_inverse);
		
		OCIBindByName($ins,":preg_find_phone",$phone_rgx);
		OCIBindByName($ins,":preg_find_fio",$fio_rgx);
		OCIBindByName($ins,":preg_find_outer_id",$outer_id_rgx);
		
		OCIBindByName($ins,":coment",$coment);
		
		OCIBindByName($ins,":id",$regexr_id,16);
		if(!OCIExecute($ins)) exit();
		OCICommit($c);
	}
	else {
		$upd=OCIParse($c,"update MAIL_REGEXR r set
		ord=:ord,		
		preg_match_from=:preg_match_from,
		preg_match_subj=:preg_match_subj,
		preg_match_body=:preg_match_body,

		preg_from_inverse=:preg_from_inverse,
		preg_subj_inverse=:preg_subj_inverse,
		preg_body_inverse=:preg_body_inverse,

		preg_find_phone=:preg_find_phone,
		preg_find_fio=:preg_find_fio,
		location_outer_id='".$location_outer_id."',
		preg_find_outer_id=:preg_outer_id,
		
		coment=:coment,
		action='".$rule_action."',
		change_date=sysdate

		where id='".$regexr_id."'");
		
		if(!is_numeric($priority)) $priority=100;
		
		OCIBindByName($upd,":ord",$priority);
		
		OCIBindByName($upd,":preg_match_from",$from_rgx);
		OCIBindByName($upd,":preg_match_subj",$subj_rgx);
		OCIBindByName($upd,":preg_match_body",$body_rgx);
		
		OCIBindByName($upd,":coment",$coment);

		OCIBindByName($upd,":preg_find_phone",$phone_rgx);
		OCIBindByName($upd,":preg_find_fio",$fio_rgx);
		OCIBindByName($upd,":preg_outer_id",$outer_id_rgx);
		
		OCIBindByName($upd,":preg_from_inverse",$from_inverse);
		OCIBindByName($upd,":preg_subj_inverse",$subj_inverse);
		OCIBindByName($upd,":preg_body_inverse",$body_inverse);		
		
		if(!OCIExecute($upd)) exit();
		OCICommit($c);
	}
	echo "<script>
	parent.location='?regexr_id=".$regexr_id."';
	</script>";
}
if(isset($delete)) {

	$del=OCIParse($c,"delete from MAIL_REGEXR r where r.id='".$regexr_id."'");
	if(!OCIExecute($del)) exit();
	OCICommit($c);
	$regexr_id='';

	echo "<script>
	parent.location='?';
	</script>";
}
?>
<iframe name=res_ifr width="100%" height="100%" scrolling="no" frameborder="0"></iframe>
</body>
</html>
