<?php include("../../starcall_conf/session.cfg.php"); 

if(!isset($_SESSION['project']['id']) or $_SESSION['project']['id']=='') exit();

include("../../starcall_conf/conn_string.cfg.php");

$project_id=$_SESSION['project']['id'];

//удаляем все предыдущие квоты    
$del=OCIParse($c,"delete from stc_quotes where project_id='".$project_id."'");
if(OCIExecute($del)) echo "Предыдущие квоты удалены<br>";
OCICommit($c);


//квоты по исходным полям
//получаем список квотируемых полей в массив
$q=OCIParse($c,"select id from STC_FIELDS t
where project_id='".$project_id."' and quoted is not null and src_type_id=1 order by ord");
OCIExecute($q);
$i=0; while(OCIFetch($q)) {$i++;
	$field_IDs[$i]=OCIResult($q,"ID");
}
$field_count=$i;

if(isset($field_IDs)) {
echo "квотируются $i исходных полей: ".implode(", ",$field_IDs);
	//собираем запрос на выборку всех вариантов значений по исходным полям
	$q_text="select * from ";
	foreach($field_IDs as $i => $field_ID) {
		if($i>1) $q_text.=", ";
		$q_text.="(select distinct v.field_id f$field_ID, v.text_value v$field_ID from stc_field_values v where v.project_id='".$project_id."' and v.field_id=".$field_ID.")";	
	}
	echo "<hr>".$q_text."<hr>";
}
else echo "Нет квотируемых исходных полей!<br>";


?>