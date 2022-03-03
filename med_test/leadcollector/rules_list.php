<?php 
session_name('medc');
session_start();
require_once '../funct.php';
if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">C������� ����������!</p>'; exit();
}

include("med/conn_string.cfg.php");	

//���������� ������: ������ ������ ����� ���������� �������� ����� �� ������, ��� �������� ����� �������� � ����� �������
$sql_text="
select 
r.id as ID,
s.id as ID_IST, s.name source_name, 
r.ord, r.coment, 
sr.name service_name, 
a.name action_name,
to_char(r.create_date,'DD.MM.YYYY') create_date, 
to_char(r.change_date,'DD.MM.YYYY') change_date,
to_char(r.use_date,'DD.MM.YYYY') use_date,
r.preg_match_from,
r.preg_match_subj,
r.preg_match_body
from MAIL_REGEXR r
left join source_auto s on s.id=r.source_auto_id
left join mail_regexr_actions a on a.id=r.action
left join services sr on sr.id=r.service_id
where 1=1
	/*filters*/
	/*orders*/
";

//�������� �����
//��������� name - ��� �������, ������������ �� ��������
//��������� case - ��������� ��� ����, �� �������� �������� where � order
$fields=array(
	"ID"=>array("name"=>"ID �������","case"=>"r.id"),
	"ID_IST"=>array("name"=>"ID ���������","case"=>"s.id"),
	"SOURCE_NAME"=>array("name"=>"�������� ����","case"=>"s.name"),
	"COMENT"=>array("name"=>"�����������"),
	"SERVICE_NAME"=>array("name"=>"������","case"=>"sr.name"),
	"ACTION_NAME"=>array("name"=>"��������","case"=>"a.name"),
	"ORD"=>array("name"=>"���������"),
	"CREATE_DATE"=>array("name"=>"�������","case"=>"r.create_date"),
	"CHANGE_DATE"=>array("name"=>"��������","case"=>"r.change_date"),
	"USE_DATE"=>array("name"=>"������������","case"=>"r.use_date"),
);

//��������� ��������: ������ � �������� ����� �������, ������� � 1. ��� �� ������ ������� �������, ������ �������� ������� where �� �������� ����� �������
//����� � �������, ���� ����� �������� ������ (������� � and) ������ ���� ���������� ������������ "/*filters*/"
//���� ������� �������� ��������� �� ����������, ��� ������ ������������� �������� ������� �� ���������
$filters=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"",
	"SERVICE_NAME"=>"",
	"ACTION_NAME"=>"",
	"ORD"=>""
);

//��������� ����������� ���������� ������ �������� �������� ����������� ����������. 
//��� ������� ���������� ���������� �� ��������� ����� ������������ up,asc - �� �����������; down,desc - �� ��������
$orders=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"up",
	"COMENT"=>"",
	"SERVICE_NAME"=>"",
	"ACTION_NAME"=>"",
	"ORD"=>"",
	"CREATE_DATE"=>"up",
	"CHANGE_DATE"=>"",
	"USE_DATE"=>""	
);
//��������� ������ �� �����:
$finds=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"",
	"COMENT"=>"",
	"SERVICE_NAME"=>"",
	"PREG_MATCH_FROM"=>"",
	"PREG_MATCH_SUBJ"=>"",
	"PREG_MATCH_BODY"=>""
);
//��������� ��� ������������� ������
$edit_id_name='ID'; //�������� ���� �������, ����������� ID
$edit_frame='parent.fr_rule_edit'; //�������� ������, � ������� ��������� �������� ��� ��������������
$edit_url='rule_edit.php?regexr_id='; //url �������� ��������������

include('lists_include_ora.php');
?>

