<?php
include("sup/sup_conn_string");

if(isset($_GET['id'])) {
	$q=OCIParse($c,"select file_body, filename,filetype,tmp_name,fileerror,filesize,coment,load_date,base_id,hist_id from SUP_FILES where rowid='".$_GET['id']."'");
	OCIExecute($q);	
	if(OCIFetch($q)) {
	
 		header("Content-Type: ".OCIResult($q,"FILETYPE"));
	    header('Content-Disposition: attachment; filename="'.OCIResult($q,"FILENAME").'"');
 		echo OCIResult($q,"FILE_BODY")->load();	
	}
}

if(isset($_FILES['new_file'])) {

	foreach($_FILES['new_file']['name'] as $key => $val) {
		
		$name=$_FILES['new_file']['name'][$key];
		$type=$_FILES['new_file']['type'][$key];
		$tmp_name=$_FILES['new_file']['tmp_name'][$key];
		$error=$_FILES['new_file']['error'][$key];
		$size=$_FILES['new_file']['size'][$key];
		
		$file_body=fread(fopen($tmp_name,'rb'),filesize($tmp_name));
	
		$ins=OCIParse($c,"insert into sup_files (file_body,	filename, filetype, tmp_name, fileerror, filesize, coment, load_date, base_id, hist_id)
		values (EMPTY_BLOB(),'".$name."','".$type."','".$tmp_name."','".$error."','".$size."','tmp',sysdate,'".$num_zayavki."','') returning file_body into :file_body");
		
		$lob = OCINewDescriptor($c, OCI_D_LOB); 
		OCIBindByName($ins,":file_body",$lob,-1,OCI_B_BLOB);
		
		
		OCIExecute($ins, OCI_DEFAULT);
		$lob->save($file_body);
		
		OCICommit($c);		
		
				
	}
}
$q=OCIParse($c,"select rowidtochar(rowid) id,filename,filetype,tmp_name,fileerror,filesize,coment,load_date,base_id,hist_id from SUP_FILES where base_id='".$num_zayavki."' and coment='tmp'");
OCIExecute($q);
while(OCIFetch($q)) {
	echo "<a href='add_file.php?id=".OCIResult($q,"ID")."'>".OCIResult($q,"FILENAME")."</a><br>";
}	
?>
