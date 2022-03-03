<?php include("../../starcall_conf/session.cfg.php"); ?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();

if(isset($project_id)) $_SESSION['survey']['project']['id']=$project_id;
elseif(!isset($_SESSION['survey']['project']['id'])) $_SESSION['survey']['project']['id']=$_SESSION['adm']['project']['id'];


$project_id=$_SESSION['survey']['project']['id'];


include("../../starcall_conf/conn_string.cfg.php");

echo "<form name=frm method=post>";

echo "<table width='100%'><tr><td align=left style='background:none;border:0'>";

echo "<select name=project_id onchange='frm.submit()'>
<option value=''>ВЫБЕРИТЕ ПРОЕКТ</option>";
while (OCIFetch($q)) {
	echo "<option value='".OCIResult($q,"ID")."'".(OCIResult($q,"ID")==$_SESSION['adm']['project']['id']?' selected':'').">".OCIResult($q,"NAME")." (".OCIResult($q,"CREATE_DATE").")</option>";
}
echo "</select>";

//список квотируемых полей
$q=OCIParse($c,"select f.id,f.text_name from STC_FIELDS f
where f.project_id=".$project_id." and f.src_type_id=1 and f.quoted is not null and f.deleted is null 
order by f.ord");
OCIExecute($q);
while(OCIFetch($q)) {
	$quoted_fields[OCIResult($q,"ID")]=OCIResult($q,"TEXT_NAME");
}
//если есть квотируемые исходные поля
if(isset($quoted_fields)) {
	$q_quotes=OCIParse($c,"select q.id,q.src_new,q.src_norm,q.src_quote,q.perez,q.nedoz,q.inwork,
	q.src_quote-q.src_norm estimate,
	decode(q.src_quote,0,'100%',decode(q.src_quote,NULL,NULL,round(q.src_norm/q.src_quote*100,0)||'%')) percent,
	q.lock_by_index 
	from STC_SRC_QUOTES q
	where q.project_id=".$project_id."
	--and q.lock_by_index is null --не показывать заблокированные по независимым
	--and q.src_quote-q.src_norm<=0 --не показывать выполненные
");
	$q_idx=OCIParse($c,"select i.value from STC_SRC_QUOTE_INDEXES qi, Stc_Src_Indexes i
	where qi.project_id=".$project_id." and qi.quote_id=:quote_id
	and i.project_id=".$project_id." 
	and i.id=qi.index_id
	and i.field_id=:field_id");
	echo "<select name=quote_id><option value=auto>Авто</option>";
	OCIExecute($q_quotes);
	while(OCIFetch($q_quotes)) {
		$color=''; $disabled='';
		$quote_id=OCIResult($q_quotes,"ID");
		$quote=OCIResult($q_quotes,"SRC_QUOTE");
		$new_count=OCIResult($q_quotes,"SRC_NEW");
		$norm_count=OCIResult($q_quotes,"SRC_NORM");
		$nedoz_count=OCIResult($q_quotes,"NEDOZ");
		$perez_count=OCIResult($q_quotes,"PEREZ");
		$inwork_count=OCIResult($q_quotes,"INWORK");
		$estim_count=OCIResult($q_quotes,"ESTIMATE");
		$percent_full=OCIResult($q_quotes,"PERCENT");
		if($estim_count<=0 and $estim_count!==NULL) {$color='green'; $disabled=' disabled';}
		elseif($new_count+$nedoz_count+$perez_count+$inwork_count<=0) {$color='red'; $disabled=' disabled';}
		OCIBindByName($q_idx,":quote_id",$quote_id);
		$i=0; foreach($quoted_fields as $field_id => $field_name) {$i++;
			OCIBindByName($q_idx,":field_id",$field_id);
			OCIExecute($q_idx);
			OCIFetch($q_idx);
			if($i==1) $quote_name=OCIResult($q_idx,"VALUE");
			else $quote_name.=" - ".OCIResult($q_idx,"VALUE");
		}
		$quote_name.=" (вып:".$norm_count."/".$quote.";новых:".$new_count.";недоз:".$nedoz_count.";перез:".$perez_count.";в работе:".$inwork_count.")";
		echo "<option style=color:".$color.$disabled." value=".$quote_id.">".$quote_name."</option>";
	}
	echo "</select>";
}


?>
