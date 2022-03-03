<?php
if(
$_SERVER['REMOTE_ADDR']<>'172.16.0.10' and $_SERVER['REMOTE_ADDR']<>'192.168.12.51'
) {
echo 'Error: ����������� IP';
exit();
}

include("med/conn_string.cfg.php");
extract($_REQUEST);

if(isset($get_call_info_by_id)) {
/*
���, ������ ���������� ������������� ���� ����.
����� ������ - �������� �����, ����������� ����������� (chr(9)), ���������� �� ����, ������ ������ ������ ��� ���.
���� ������ �������, �� ����������� ������ �������� ������ (chr(13)) � ������, ����������� ����������� (chr(9)).
��������� ��������� � ��������� ������ � ������ ���������.
��������� Windows-1251

������� ��������� ������ � IP: 172.16.0.10 

2 ������� �������:
http://192.168.13.4/med/1c_api.php?get_call_info_by_id=193 
http://192.168.13.4/med/1c_api.php?get_call_info_by_id=129 

���� ������� ����� �����:
ID ������ (number, not null) - ������������� ������, ������� ������ ���������� ����� ����� ������ ��� ������ ��������
SOURCE_TYPE_ID (number, not null) - �������������� ���� ��������� (1 - �������, 2 - E-mail)
BNUMBER (varchar(200), null) - ���������� ����� ��� ������ ���������� �������������
SOURCE_AUTO_ID (number, not null) - ������������� ������������� ������������� ��������� �������
SOURCE_AUTO_NAME (varchar(200), not null) - �������� ������������� ������������� ��������� �������
SOURCE_MAN_NAME (varchar(200), null) - �������� ��������� ������� (����), ���������� ���������� �������
SOURCE_MAN_DET (varchar(200), null) - ����������� ������� (����) ��������� ������� (��������, ��� ��������� ������� "�������� � �����" ����� �������� �����)
*/	
	$q=OCIParse($c,"select b.id, st.name source_type_name, b.bnumber, sa.id source_auto_id, sa.name source_auto_name, sm.name source_man_name, sd.name source_man_det
	from CALL_BASE b, SOURCE_AUTO sa, SOURCE_TYPE st, source_man sm, source_man_detail sd
	where sa.id(+)=b.source_auto_id and st.id(+)=sa.source_type and decode(sm.id(+),0,NULL,sm.id(+))=b.source_man_id
	and sd.id(+)=b.source_man_det_id
	and b.id='{$get_call_info_by_id}'");
	OCIExecute($q);
	for($i=1; $i<=oci_num_fields($q); $i++) {
		echo $i==1?NULL:chr(9);
		echo oci_field_name($q,$i);
	}
	if($row=oci_fetch_assoc($q)) {
		$i=1; foreach($row as $val) {
			echo $i==1?chr(13):chr(9);
			echo trim($val);
			$i++;
		}
	}
	$upd=OCIParse($c,"update call_base set get_callinfo_date_1C=sysdate where id='{$get_call_info_by_id}'");
	OCIExecute($upd);
	OCICommit($c);
	exit();
}
//������ 2
if(isset($get_call_info_by_id2)) {
/*
���, ������ ������ ����������� ��� ��������� ���������� �� ������� (get_call_info_by_id2).
��� �������� ��� ��������� �������� ���������, ������� � ��� ����, ����� � ����� �������, ��� ������ ���� ����� ���������� � 1�, ����� ����� ����� ���������� �� ����� �������.  

��������:
������ ���������� ������������� ���� ����.
����� ������ - �������� �����, ����������� ����������� (chr(9)), ���������� �� ����, ������ ������ ������ ��� ���.
���� ������ �������, �� ����������� ������ �������� ������ (chr(13)) � ������, ����������� ����������� (chr(9)).
��������� ��������� � ��������� ������ � ������ ���������.
��������� Windows-1251

������� ��������� ������ � IP: 172.16.0.10 

2 ������� �������:
http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=59198 
http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869

�������������� �������� html, ��������� ������� ��������� � ������� ����� HTML ������� (��� �������)
��������: http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869&html

���� ������������ �������:
ID (number, not null) - id ������ (number, not null) - ������������� ������, ������� ������ ���������� ����� ����� ������ ��� ������ ��������
DATE_CALL (����, �����, DD.MM.YYYY HH24:MI:SS) - ���� ������
SOURCE_TYPE_NAME (number, not null) - �������������� ���� ��������� (�������, E-mail)
BNUMBER (varchar(200), null) - ���������� ����� ��� ������ ���������� �������������
SOURCE_AUTO_ID (number, not null) - ������������� ������������� ������������� ��������� �������
SOURCE_AUTO (varchar(200), not null) - �������������� �������� �������
SOURCE_IN_MAN (varchar(200), null) - ��������, ��������� �������� ���������� �������
SOURCE_IN_MAN_DET (varchar(200), null) - ����������� ������� ��������� �������� (��������, �� ��������� � source_in_man)
SOURCE_OUT_MAN (varchar(500), null) - �������� �������, ���������� ��������� ���������� ������� (��������, �� ��������� � source_auto)
SOURCE_COMBO (varchar(500), not null) - ��������������� �������� (vnl(source_out_man,source_auto)). (���� source_out_man ������, �� source_auto, ����� source_out_man)

*/	
	$q=OCIParse($c,"select b.id,to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call, st.name source_type_name, b.bnumber, 
	sa.id source_auto_id,
	sa.name source_auto, 
	sm.name source_in_man, 
	sd.name source_in_man_det,
	sad.name source_out_man, 
	nvl(sad.name,sa.name) source_combo
	from CALL_BASE b, SOURCE_AUTO sa, SOURCE_AUTO_DETAIL sad, SOURCE_TYPE st, source_man sm, source_man_detail sd
	where sa.id(+)=b.source_auto_id 
	and sad.id(+)=b.source_man_id_new
	and st.id(+)=sa.source_type and decode(sm.id(+),0,NULL,sm.id(+))=b.source_man_id
	and sd.id(+)=b.source_man_det_id
	and b.id='{$get_call_info_by_id2}'");
	OCIExecute($q);
	if(isset($html)) echo "<table border=1>";
	if(isset($html)) echo "<tr>";
	for($i=1; $i<=oci_num_fields($q); $i++) {
		if(isset($html)) echo "<td>";
		else echo $i==1?NULL:chr(9);
		
		echo oci_field_name($q,$i);
		if(isset($html)) echo "</td>";
	}
	if(isset($html)) echo "</tr>";
	if($row=oci_fetch_assoc($q)) {
		if(isset($html)) echo "<tr>";
		$i=1; foreach($row as $val) {
			if(isset($html)) echo "<td>";
			else echo $i==1?chr(13):chr(9);
			
			echo trim($val);
			$i++;
			if(isset($html)) echo "</td>";
		}
		if(isset($html)) echo "</tr>";
	}
	if(isset($html)) echo "</table>";
	$upd=OCIParse($c,"update call_base set get_callinfo_date_1C=sysdate where id='{$get_call_info_by_id2}'");
	OCIExecute($upd);
	OCICommit($c);
	exit();
}

if(isset($get_list_source_auto)) {
	/*
	get_list_source_auto
	��������� ������ �������������� ���������� �������
	
	��������:
	������ ���������� ������������� ���� ����.
	����� ������ - �������� �����, ����������� ����������� (chr(9)), ���������� �� ����, ������ ������ ������ ��� ���.
	���� ������ �������, �� ����������� ������, ����������� �������� �������� ������ (chr(13)) � ������, ����������� ����������� (chr(9)).
	��������� ��������� � ��������� ������ � ������ ���������.
	��������� Windows-1251
	
	������� ��������� ������ � IP: 172.16.0.10 
	
	������ �������:
	http://192.168.13.4/med/1c_api.php?get_list_source_auto 
	http://192.168.13.4/med/1c_api.php?get_list_source_auto&html
	
	�������������� �������� html, ��������� ������� ��������� � ������� ����� HTML ������� (��� �������)
	��������: http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869&html
	
	���� ������������ �������:
	ID (number, not null) - ������������� ��������������� ��������� �������
	NAME (varchar(200), not null) - �������� ��������������� ��������� �������
	TYPE (phone, email) - ��� ��������� �������
	*/		
	$q=OCIParse($c,"select t.id,t.name,decode(t.source_type,1,'phone',2,'email') type from SOURCE_AUTO t
	where deleted is null and t.source_type in (1,2)
	order by t.id");
	OCIExecute($q);
	if(isset($html)) echo "<table border=1>";
	if(isset($html)) echo "<tr>";
	for($i=1; $i<=oci_num_fields($q); $i++) {
		if(isset($html)) echo "<td>";
		else echo $i==1?NULL:chr(9);
		
		echo oci_field_name($q,$i);
		if(isset($html)) echo "</td>";
	}
	if(isset($html)) echo "</tr>";
	while($row=oci_fetch_assoc($q)) {
		if(isset($html)) echo "<tr>";
		$i=1; foreach($row as $val) {
			if(isset($html)) echo "<td>";
			else echo $i==1?chr(13):chr(9);
			
			echo trim($val);
			$i++;
			if(isset($html)) echo "</td>";
		}
		if(isset($html)) echo "</tr>";
	}
	if(isset($html)) echo "</table>";	
}

if(isset($set_visit_status)) {
/*
���, ������ ������ ��� ���������� ������� ���������:

� �������� ����� ���������
http://192.168.13.4/med/1c_api.php?set_visit_status&ticket_id=633&visit_date=20180101000000
��� � ������ ����� ���������
http://192.168.13.4/med/1c_api.php?set_visit_status&ticket_id=633&visit_date=
*/	
	if(!isset($ticket_id) or ($ticket_id)=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	if (!isset($visit_date)) $visit_date='';
	
	if($visit_date<>'') {
		$q=OCIParse($c,"select count(*) cnt from visit_hist where base_id='{$ticket_id}' and date_visit=to_date('{$visit_date}','YYYYMMDDHH24MISS')");
		OCIExecute($q);
		OCIFetch($q);
		if(OCIResult($q,"CNT")>0) {
			echo "������: ��������� ������";
			exit();
		}
	}
	
	$upd=OCIParse($c,"update call_base set check_visit_date_1c=sysdate,
	visit_date_1c=to_date('{$visit_date}','YYYYMMDDHH24MISS') where id='{$ticket_id}'");
	if(OCIExecute($upd)) {
		if($visit_date<>'' and oci_num_rows($upd)>0) {
			$ins=OCIParse($c,"insert into visit_hist (base_id,date_add,date_visit) values ('{$ticket_id}',sysdate,to_date('{$visit_date}','YYYYMMDDHH24MISS'))");
			OCIExecute($ins);
			OCICommit($c);
		}
		echo "OK:".oci_num_rows($upd);
	}
}

if(isset($set_entry_date)) {
/*���������� ����, �� ������� ������� ������� � �������
�������:
���������� ���� ������:
http://192.168.13.4/med/1c_api.php?set_entry_date&ticket_id=60336&entry_date=20181231235959
�������� ���� ������:
http://192.168.13.4/med/1c_api.php?set_entry_date&ticket_id=60336&entry_date=
*/
	if(!isset($ticket_id) or ($ticket_id)=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	if (!isset($entry_date)) $entry_date='';

    if($entry_date<>'') { //���������� ���� ������, ������ ���� ���� ������ ��� ���, ���� ���� ������ ��� ����, �� ��� ��������� �� �������� - nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS'))
		$upd=OCIParse($c,"update call_base set check_entry_date_1c=sysdate, 
		entry_date_1c=nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS')) where id='{$ticket_id}'");
		if(!OCIExecute($upd,OCI_DEFAULT)) exit();
		
		$upd2=OCIParse($c,"update CALL_BASE_CLINIC set check_entry_date_1c=sysdate, 
		entry_date_1c=nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS')) where id='{$ticket_id}'");
		if(!OCIExecute($upd2,OCI_DEFAULT)) exit();
		
		//��������� ���� ������ � �������
		$ins=OCIParse($c,"insert into write_hist (base_id,date_add,date_write) values ('{$ticket_id}',sysdate,to_date('{$entry_date}','YYYYMMDDHH24MISS'))");
		if(OCIExecute($ins,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($ins);
			OCICommit($c);
		}
	}
	else {//�������� ���� ������
		$upd=OCIParse($c,"update call_base set check_entry_date_1c=sysdate, 
		entry_date_1c=NULL where id='{$ticket_id}'");
		if(!OCIExecute($upd,OCI_DEFAULT)) exit();
		
		$upd2=OCIParse($c,"update CALL_BASE_CLINIC set check_entry_date_1c=sysdate, 
		entry_date_1c=NULL where id='{$ticket_id}'");
		if(!OCIExecute($upd2,OCI_DEFAULT)) exit();
		
		//������� �������
		/*$del=OCIParse($c,"delete from write_hist where base_id='{$ticket_id}'");
		if(OCIExecute($del,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($del);
			OCICommit($c);
		}*/
		//��������� ���� ������ � �������
		$ins=OCIParse($c,"insert into write_hist (base_id,date_add,date_write) values ('{$ticket_id}',sysdate,NULL)");
		if(OCIExecute($ins,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($ins);
			OCICommit($c);
		}		
	}
}	

//���������� ���������� � �������
/*
��������� ��� ���������� ���������� � �������.
����������� ���������:
1) ���������� �������:
add_pay - ��������� �� ���������� ������� (�� ����� ��������)
ticket_id - ID ������ - �������� - ����� ������������� �����
pay_date - ���� ������� - �������� - ���� YYYYMMDDHH24MISS
rub - ����� ������� � ������, ��� ������ - �������� - ����� ����� (������������� - ������� �������)
� ������ ��������� ���������� ������� ���������� "��:1", ��� 1 - ���������� ����������� � ������� �����
������ http://192.168.13.4/med/1c_api.php?add_pay&ticket_id=2676&pay_date=20180101213545&rub=102
OK:1

2) �������� �������
del_pay - ��������� �� �������� ������� (�� ����� ��������)
ticket_id - ID ������ - �������� - ����� ������������� �����
pay_date - ���� ������� - �������� - ���� YYYYMMDDHH24MISS, ���� ������ �������� �����������, �� ��������� ��� �������, �������������� ��������� ����������
rub - ����� ������� � ������, ��� ������ - �������� - ����� ����� (������������� - ������� �������), ���� ������ �������� �����������, �� ��������� ��� �������, �������������� ��������� ����������
� ������ ��������� �������� ������� ���������� "��:1", ��� 1 - ���������� ��������� � ������� �����
������: http://192.168.13.4/med/1c_api.php?del_pay&ticket_id=2676&pay_date=20180101213545&rub=102
OK:1

�������� ����� ��������
get_pay_sum - ���������
ticket_id - ID ������ - �������� - ����� ������������� �����
������: http://192.168.13.4/med/1c_api.php?get_pay_sum&ticket_id=2676
OK:201

� ������ ������������� ������ ������������ ��������� ������:
-- ����� ������� ��� ID ������ �� �������� ����� ������: Warning: ociexecute(): ORA-01722: invalid number in C:\Apache24\htdocs\med\1c_api.php on line 92
-- �������� ���� �������: Warning: ociexecute(): ORA-01830: date format picture ends before converting entire input string in C:\Apache24\htdocs\med\1c_api.php on line 92
-- ������ � ����� ID �� ����������: Warning: ociexecute(): ORA-01830: date format picture ends before converting entire input string in C:\Apache24\htdocs\med\1c_api.php on line 92
-- � �� ��� ���� ������ � ������ ������� (��������� ticket_id,pay_date,rub)
-- ������ ��������� ������
*/

if(isset($add_pay)) {
	if(!isset($pay_date) or $pay_date=='') {
		echo "������: ����������� ���� �������";
		exit();		
	}
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	$ins=OCIParse($c,"insert into payment_hist (base_id,date_add,date_payment,rub)
	values ('{$ticket_id}',sysdate,to_date('{$pay_date}','YYYYMMDDHH24MISS'),'{$rub}')");
	if(OCIExecute($ins)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($ins);		
	}
}
if(isset($del_pay)) {
	/*
	if(!isset($pay_date) or $pay_date=='') {
		echo "������: ����������� ���� �������";
		exit();		
	}
	*/
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
    $deletestr = "delete from payment_hist where base_id='{$ticket_id}' ";
	if (isset($pay_date)) $deletestr .= " and date_payment=to_date('{$pay_date}','YYYYMMDDHH24MISS')";
	if (isset($rub)) $deletestr .= " and rub='{$rub}'";
	$del=OCIParse($c,$deletestr);
	if(OCIExecute($del)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($del);		
	}
}
if(isset($get_pay_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	$q=OCIParse($c,"select nvl(sum(rub),0) sum from PAYMENT_HIST t where base_id='{$ticket_id}'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		echo "OK:".OCIResult($q,"SUM");		
	}
}

//���������� ���������� � ����� �������
/*
��������� ��� ���������� ���������� � ����� �������.
����������� ���������:

1) ���������� �������� ����� ����� �������
ch_plan_sum - ��������� �� ������������� �������� ����� ����� ������� (��������� �������������� ����� ����� �������, ������ �� ������� ������� ����� ������� � ��������)
ticket_id - ID ������ - �������� - ����� ������������� �����
plan_date - ���� ����� ������� - �������� - ���� YYYYMMDDHH24MISS
plan_num - ����� ����� ������� - ��������, ������(100)
rub - �������� ����� ����� ������� � ������, ��� ������ - �������� - ����� ����� (������������� ��� �������������)
� ������ ��������� ���������� ������� ���������� "��:1", ��� 1 - ���������� ����������� � ������� �����
������ http://192.168.13.4/med/1c_api.php?ch_plan_sum&ticket_id=2676&plan_num=A12345678&plan_date=20180101213545&rub=102
���� ������� ����� ������� ������ ������� � �������� ���������� �� ����, �� �������������� ����� ���������, � � ������ �������� ���������� ����������� �����:
OK:1
���� ������� ����� ����, �� ���������� ����������� ����� ����� ����� ����, �.�. ������������� �� ���������.
OK:0

2) ���������� / ������������� ����� �������:
add_plan_hist - ��������� �� ���������� ����� ��� �������������� ����� ����� (�� ����� ��������)
ticket_id - ID ������ - �������� - ����� ������������� �����
plan_date - ���� ����� ������� - �������� - ���� YYYYMMDDHH24MISS
plan_num - ����� ����� ������� - ��������, ������(100)
rub - ������������� ����� ����� ������� � ������, ��� ������ - �������� - ����� ����� (������������� ��� �������������)
� ������ ��������� ���������� ������� ���������� "��:1", ��� 1 - ���������� ����������� � ������� �����
������ http://192.168.13.4/med/1c_api.php?add_plan_delta&ticket_id=2676&plan_num=A12345678&plan_date=20180101213545&rub=102
OK:1

3) �������� ����� �������
del_pay - ��������� �� �������� ����� ������� (�� ����� ��������)
ticket_id - ID ������ - �������� - ����� ������������� �����
plan_num - ����� ����� ������� - ��������, ������(100), ���� ������ �������� �����������, �� ��������� ������, �������������� ��������� ����������
plan_date - ���� ����� ������� - �������� - ���� YYYYMMDDHH24MISS, ���� ������ �������� �����������, �� ��������� ������, �������������� ��������� ����������
rub - ����� ������������� ����� - �������� - ����� ����� (������������� ��� �������������), ���� ������ �������� �����������, �� ��������� ��� ������, �������������� ��������� ����������
� ������ ��������� �������� ������� ���������� "��:1", ��� 1 - ���������� ��������� � ������� �����
������: http://192.168.13.4/med/1c_api.php?del_plan&ticket_id=2676&plan_num=A12345678
OK:1

4) �������� ����� ����� �������. ���������� ������� ����� ����� � ������
get_plan_sum - ���������
ticket_id - ID ������ - �������� - ����� ������������� �����
plan_num - ����� ����� ������� - ������(100), ���� ������ �������� �����������, �� ��������� ����� ���� ������ �� ������� ticket_id
������: http://192.168.13.4/med/1c_api.php?get_plan_sum&ticket_id=2676&plan_num=A12345678
OK:201
*/

if(isset($ch_plan_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	if(!isset($plan_num) or $plan_num=='') {
		echo "������: ����������� ����� ����� �������";
		exit();		
	}
	if(!isset($plan_date) or $plan_date=='') {
		echo "������: ����������� ���� ����� �������";
		exit();		
	}
	if(!isset($rub) or !is_numeric($rub)) {
		echo "������: ����������� ����� �������������";
		exit();		
	}	
	$ins=OCIParse($c,"insert into plan_hist (base_id,date_add,plan_num,plan_date,rub,check_sum)
	values ('{$ticket_id}',sysdate,'{$plan_num}',to_date('{$plan_date}','YYYYMMDDHH24MISS'),
		'{$rub}'-nvl((select sum(rub) from plan_hist where base_id='{$ticket_id}' and plan_num='{$plan_num}'),0),
		'{$rub}'
	) returning rub into :rub_delta");
	OCIBindByName($ins,":rub_delta",$rub_delta,16);
	if(OCIExecute($ins, OCI_DEFAULT)) {
		if($rub_delta<>0) {
			OCICommit($c);
			echo "OK:".oci_num_rows($ins);
		}
		else {
			OCIRollBack($c);
			echo "OK:0";
		}
	}
}

if(isset($add_plan_delta)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	if(!isset($plan_num) or $plan_num=='') {
		echo "������: ����������� ����� ����� �������";
		exit();		
	}
	if(!isset($plan_date) or $plan_date=='') {
		echo "������: ����������� ���� ����� �������";
		exit();		
	}
	if(!isset($rub) or !is_numeric($rub)) {
		echo "������: ����������� ����� �������������";
		exit();		
	}	
	$ins=OCIParse($c,"insert into plan_hist (base_id,date_add,plan_num,plan_date,rub)
	values ('{$ticket_id}',sysdate,'{$plan_num}',to_date('{$plan_date}','YYYYMMDDHH24MISS'),'{$rub}')");
	if(OCIExecute($ins)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($ins);		
	}
}
if(isset($del_plan)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
    $deletestr = "delete from plan_hist where base_id='{$ticket_id}'";
	if (isset($plan_num)) $deletestr .= " and plan_num='{$plan_num}'";
	if (isset($plan_date)) $deletestr .= " and plan_date=to_date('{$plan_date}','YYYYMMDDHH24MISS')";
	if (isset($rub)) $deletestr .= " and rub='{$rub}'";
	$del=OCIParse($c,$deletestr);
	if(OCIExecute($del)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($del);		
	}
}
if(isset($get_plan_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "������: ���������� ticket_id";
		exit();
	}
	$sql="select nvl(sum(rub),0) sum from plan_hist t where base_id='{$ticket_id}'";
	if (isset($plan_num)) $sql .= " and plan_num='{$plan_num}'";
	$q=OCIParse($c,$sql);
	OCIExecute($q);
	if(OCIFetch($q)) {
		echo "OK:".OCIResult($q,"SUM");		
	}
}
?>