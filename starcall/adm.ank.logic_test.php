<?php include("starcall/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body topmargin="8">	
<?php 	
include("starcall/conn_string.cfg.php");

if($_SESSION['user']['rw_ank']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

if(isset($hide_pages)) $_SESSION['adm']['project']['hide_pages']='on'; else unset($_SESSION['adm']['project']['hide_pages']);
$project_id=$_SESSION['adm']['project']['id'];
$error='';
$warning='';

echo "������ ��������:<br><b>
1. ������ ��������<br>
2. ��������� ������ ��������� ������ ���������<br>
3. ��������� ������ ��������� �� ����� �������� � ��������<br>
4. ������ ������ �� �������������� ��� ���������� ����<br>
-5. ������ � ������� �� ����� �������<br>
-6. ����������� ������ �� �������� ������ �����<br>
-7. ��������� ����� ���������� ������� �� ����� �������� ����� ��������� ������ ���������<br>
</b><hr>";
//�������
$q_page=OCIParse($c,"select p.id,p.num from STC_OBJECT_PAGE p
where p.project_id=".$project_id."
order by p.num");
	$q_grp=OCIParse($c,"select g.id,g.num,g.num_on_page,g.page_id from STC_OBJECT_GROUP g
	where g.project_id=".$project_id." and g.page_id=:page_id
	order by g.num_on_page");
		$q_obj=OCIParse($c,"select o.id,o.num,o.quote_num,o.field_id,o.depend_of_field,o.impact_on_field,o.page_num,o.num_on_group,f.text_name from STC_OBJECTS o, STC_FIELDS f
		where o.project_id=".$project_id." and o.group_id=:grp_id and o.deleted is null
		and f.id(+)=o.field_id
		order by o.num_on_group");	
$o_on_p_cnt=0;
//���������� ��������
OCIExecute($q_page,OCI_DEFAULT);
while (OCIFetch($q_page)) {
	$page_id=OCIResult($q_page,"ID");
	$page_num=OCIResult($q_page,"NUM");
	$o_on_p_cnt=0;
	//���������� ������
	OCIBindByName($q_grp,":page_id",$page_id);
	OCIExecute($q_grp,OCI_DEFAULT);
	while (OCIFetch($q_grp)) {	
		$grp_id=OCIResult($q_grp,"ID");
		$grp_num=OCIResult($q_grp,"NUM");
		$g_on_p=OCIResult($q_grp,"NUM_ON_PAGE");
		//���������� �������
		OCIBindByName($q_obj,":grp_id",$grp_id);
		OCIExecute($q_obj,OCI_DEFAULT);
		while (OCIFetch($q_obj)) {
			$o_on_p_cnt++;
			$obj_num=OCIResult($q_obj,"NUM");
			$o_on_g=OCIResult($q_obj,"NUM_ON_GROUP");
			//���� ������ ���������
			if(OCIResult($q_obj,"DEPEND_OF_FIELD")<>'') {
				$q=OCIParse($c,"select o.num,o.page_num,o.num_on_group,o.deleted,g.num grp_num,g.num_on_page, f.text_name from STC_FIELDS f, STC_OBJECTS o, stc_object_group g
				where f.project_id=".$project_id." and f.id=".OCIResult($q_obj,"DEPEND_OF_FIELD")."
				and o.project_id=".$project_id."
				and o.field_id=f.id
				and g.id=o.group_id");
				OCIExecute($q,OCI_DEFAULT);
				if(!OCIFetch($q) or OCIResult($q,"DELETED")<>'') echo "<font color=red>������ \"$page_num.$g_on_p.$o_on_g. ".OCIResult($q_obj,"TEXT_NAME")."\" ������� �� ��������������� �������</font><hr>";
				else {
					if(OCIResult($q_obj,"NUM")<=OCIResult($q,"NUM")) {
						echo "<font color=red>��������� ������ ��������� ������ ��������� (\"$page_num.$g_on_p.$o_on_g. ".OCIResult($q_obj,"TEXT_NAME")."\" ������ �� \"".OCIResult($q,"PAGE_NUM").".".OCIResult($q,"NUM_ON_PAGE").".".OCIResult($q,"NUM_ON_GROUP").". ".OCIResult($q,"TEXT_NAME")."\")</font><hr>";
					}
					if($page_num==OCIResult($q,"PAGE_NUM")) {
						echo "<font color=red>��������� � �������� ������� ��������� �� ����� �������� (\"$page_num.$g_on_p.$o_on_g. ".OCIResult($q_obj,"TEXT_NAME")."\" ������ �� \"".OCIResult($q,"PAGE_NUM").".".OCIResult($q,"NUM_ON_PAGE").".".OCIResult($q,"NUM_ON_GROUP").". ".OCIResult($q,"TEXT_NAME")."\")</font><hr>";
					}
				}
			}
			//���� ������ ��������
			if(OCIResult($q_obj,"IMPACT_ON_FIELD")<>'') {
				$q=OCIParse($c,"select f.uniq,f.text_name from STC_FIELDS f
				where f.project_id=".$project_id." and f.id=".OCIResult($q_obj,"IMPACT_ON_FIELD"));
				OCIExecute($q,OCI_DEFAULT);
				if(!OCIFetch($q)) {
					echo "<font color=red>������ \"$page_num.$g_on_p.$o_on_g. ".OCIResult($q_obj,"TEXT_NAME")."\" ������ �� �������������� ����</font><hr>";
				}
					else if(OCIResult($q,"UNIQ")<>'') {
					echo "<font color=red>������ \"$page_num.$g_on_p.$o_on_g. ".OCIResult($q_obj,"TEXT_NAME")."\" ������ �� ���������� ���� \"".OCIResult($q,"TEXT_NAME")."\"</font><hr>";
				}
			}
		 
			//
		}
	}
	if($o_on_p_cnt==0) echo "<font color=red>������ �������� $page_num</font><hr>";
}
?>
