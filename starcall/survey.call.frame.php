<?php 
include("starcall/session.cfg.php");

if($_SESSION['user']['operator']<>'y') exit();

//extract($_REQUEST);

if(isset($_SESSION['adm']['project']['id'])) $_SESSION['survey']['project']['id']=$_SESSION['adm']['project']['id'];
elseif (!isset($_SESSION['survey']['project']['id'])) $_SESSION['survey']['project']['id']='';

//����� �������� ����������
if(isset($_SESSION['survey']['ank'])) unset($_SESSION['survey']['ank']);
$_SESSION['survey']['ank']['base']['id']='';
$_SESSION['survey']['ank']['base']['status']='';
$_SESSION['survey']['ank']['phone']['id']='';
//$_SESSION['survey']['ank']['phone']['num']='';

//�������� ���� ������� � ������� =================================================
//��� ������������: ���������� $_SESSION['survey']['project']['id'] ����������� ������ ����� ������� ������ � ������, ���� ������������ ���� ������ � �������
include("starcall/conn_string.cfg.php");
if(isset($project_id)) {
	if($_SESSION['user']['operator_only']=='y') $where_prj="and (p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id=".$_SESSION['user']['id']." and gp.group_id=gu.group_id))";
	elseif($_SESSION['user']['all_projects']=='y') $where_prj=''; 
	else $where_prj=" and (
		--������� ��������� ���� ��� ����� ���������
		p.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].")
		or
		--������, � ������� �������� � ��� ��� ������� 
		p.id in (select gp.project_id from STC_USER_GRP_USR gu, STC_USER_GRP_PRJ gp where gu.user_id in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=gu.group_id) 
		or 
		--������ ��������� ���� ��� ����� ���������
		p.id in (select gp.project_id from STC_USER_GROUP g, STC_USER_GRP_PRJ gp where g.creator in (select child_user_id from STC_USER_CHILD where user_id=".$_SESSION['user']['id'].") and gp.group_id=g.id)
		)";

	$q=OCIParse($c,"select p.id from STC_PROJECTS p
where status<>'������' and p.id='".$_SESSION['survey']['project']['id']."'
".$where_prj."
order by p.name, p.create_date");
	OCIExecute($q);

	if(OCIFetch($q)) $_SESSION['survey']['project']['id']=OCIResult($q,"ID");
}

echo '<!DOCTYPE html>
<html>
<head>
<title>StarCall</title>
</head>
	<frameset name=callFrameset id=callFrameset rows="120,*,1">	 
		<frame src=survey.call.php name=callTopFrame id=callTopFrame title=callTopFrame noresize="noresize" scrolling="no"></frame>
		<frame src=survey.ank.frame.php name=callBottomFrame id=callBottomFrame title=callBottomFrame></frame>
		<frame src=blank_page.php name=callLogFrame id=callLogFrame title=callLogFrame></frame>
	</frameset>
</html>';
?>