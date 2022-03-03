<?php
set_error_handler('MyErrorHandler');

if(!$in_json=json_decode(file_get_contents("php://input"),true)) {
	err("Wrong JSON");
}
if(!isset($in_json['TOKEN']) or $in_json['TOKEN']<>'83a8a547-f1ac-44f8-8a9b-6919d9790868') {
	err("Wrong token");
}
if(!isset($in_json['METHOD']) or $in_json['METHOD']=='') {
	err("Empty method");
}
if(!function_exists($in_json['METHOD'])) {
	err("Wrong method");	
}
else $in_json['METHOD']($in_json);

if($in_json['METHOD']=='get_ids_by_outer_ids') {

}

//print_r($in_json);

function get_ids_by_outer_ids($in_json) {
	$answer=array();
	if(!isset($in_json['DATA']['OUTER_IDS']) or !is_array($in_json['DATA']['OUTER_IDS'])) {
		err("Wrong DATA");
	}
	include("med/conn_string.cfg.php");
	$q=OCIParse($c,"select b.id from CALL_BASE b
	left join source_auto a on a.id=b.source_auto_id
	where a.supplier_id=4 and b.outer_order_id=:outer_id");
	foreach($in_json['DATA']['OUTER_IDS'] as $outer_id) {
		OCIbindByName($q,":outer_id",$outer_id);
		OCIExecute($q);
		if(OCIFetch($q)) $answer[]=array("OUTER_ID"=>$outer_id,"ID"=>OCIResult($q,"ID"));
		else $answer[]=array("OUTER_ID"=>$outer_id,"ID"=>"null");
	}
	return_json($answer);
}

function err($errstr) {
	MyErrorHandler('', $errstr, '', '');
}
function MyErrorHandler($errno, $errstr, $errfile, $errline) {
	$out_json['ERROR']['errno']=$errno;
	$out_json['ERROR']['errstr']=$errstr;
	$out_json['ERROR']['errfile']=$errfile;
	$out_json['ERROR']['errline']=$errline;
	mb_convert_variables('utf8','cp1251',$out_json);
	echo json_encode($out_json);
	exit();			
}
function return_json($data) {
	header("Content-Type: application/json;charset=utf-8");
	mb_convert_variables('utf8','cp1251',$data);
	echo json_encode($data);
}

?>