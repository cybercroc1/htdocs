<?php

extract($_REQUEST);
if (isset($sid)) session_id($sid);
session_start();
$sid=session_id();



if (!isset($_SESSION['auth'])) {
	echo "<font color=red><b>ОШИБКА: У Вас нет прав для просмотра данной страницы. Вы не прошли авторизацию</b></font>";
	exit();
}
//if (!isset($base_id) or $base_id=='') {exit();}

include("sup/sup_conn_string");



/*
if(isset($base_id)) {$num_zayavki=$base_id; $target='logFrame'; $varname='base_id';}
else {$target='oper_ifr'; $varname='num_zayavki';}
*/

//загрузка файла
if(isset($_FILES['file'])) {

		$name=$_FILES['file']['name'];
		$type=$_FILES['file']['type'];
		$tmp_name=$_FILES['file']['tmp_name'];
		$error=$_FILES['file']['error'];
		$size=$_FILES['file']['size'];
		
		if($_FILES['file']['error']<>0) {echo "Файл не загружен. Ошибка: ".$_FILES['file']['error']; exit();} 
		
		$name=iconv('utf-8','windows-1251',basename($_FILES['file']['name']));
		$type=$_FILES['file']['type'];
		$tmp_name=$_FILES['file']['tmp_name'];
		$error=$_FILES['file']['error'];
		$size=$_FILES['file']['size'];
		
		if($size<1) {echo "Файл ".$name." не загружен: нулевой размер"; exit();}
		if($size>10000000) {echo "Файл ".$name." не загружен: размер превышает 10 МБ"; exit();}

		$file_body=fread(fopen($tmp_name,'rb'),filesize($tmp_name));
		
		$q=OCIParse($c,"select count(*) cnt from sup_files where filename='".$name."' and base_id='".$base_id."' and sess_id='".$sid."'");
		OCIExecute($q);
		OCIFetch($q);
		if(OCIResult($q,"CNT")>0) {echo "Файл с именем ".$name." уже загружен"; exit();}
	
		$ins=OCIParse($c,"insert into sup_files (id,file_body,	filename, filetype, tmp_name, fileerror, filesize, load_date, base_id, tmp, sess_id)
		values (sup_file_id.nextval||dbms_random.string('a',40),EMPTY_BLOB(),'".$name."','".$type."','".$tmp_name."','".$error."','".$size."',sysdate,'".$base_id."','y','".$sid."') returning id,file_body into :id,:file_body");
		
		$lob = OCINewDescriptor($c, OCI_D_LOB); 
		OCIBindByName($ins,":file_body",$lob,-1,OCI_B_BLOB);
		OCIBindByName($ins,":id",$file_id,50);
		
		
		if(OCIExecute($ins, OCI_DEFAULT)) {
			$lob->save($file_body);
			
			OCICommit($c);			
			
			echo "OK:".$file_id.":".$type.":".$name;
		}
	exit();
}
//удаление файла
if(isset($delete)) {
	if(!isset($fileid) or $fileid=='') {echo "Отсуствует fileid"; exit();}

	$del=OCIParse($c,"delete from SUP_FILES where id='".$fileid."' and nvl(sess_id,0)=nvl('".$sid."',0) and tmp='y'");
	if(OCIExecute($del)) {	
		OCICommit($c);
		if(oci_num_rows($del)>0) {
			echo "OK:".oci_num_rows($del);
		}
	}
	exit();
}

//выгрузка файла
if(isset($download)) {
	if(!isset($fileid) or $fileid=='') {echo "Отсуствует fileid"; exit();}
	
	$q=OCIParse($c,"select file_body, filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where id='".$fileid."'");
	OCIExecute($q);	
	if(OCIFetch($q)) {
		header("Content-Type: ".OCIResult($q,"FILETYPE"));
		header('Content-Disposition: attachment; filename="'.OCIResult($q,"FILENAME").'"');
		header('Content-Length: '.OCIResult($q,"FILESIZE")); 
		header('accept-ranges: bytes');			
		echo OCIResult($q,"FILE_BODY")->load();	
	}
}

/*
echo "<script>";
foreach (glob($file_dir."*.*") as $filename) {
    if(filetype($filename)=='file') { //только файлы
		echo "add_file_link('".basename($filename)."','".mime_content_type($filename)."','".$file_dir."');"; 
	}
}
echo "</script>";
*/
?>



