<?php
extract($_REQUEST);
if (isset($sid) and $sid<>'') {session_id($sid); session_start();}
else $sid='';

include("../../sup_conf/sup_conn_string");

if(isset($base_id)) {$num_zayavki=$base_id; $target='logFrame'; $varname='base_id';}
else {$target='oper_ifr'; $varname='num_zayavki';}

$div_InnerHTML='';

//выгрузка файла
if(isset($download)) {
	$q=OCIParse($c,"select file_body, filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where id='".$id."'");
	OCIExecute($q);	
	if(OCIFetch($q)) {
	
 		header("Content-Type: ".OCIResult($q,"FILETYPE"));
	    header('Content-Disposition: attachment; filename="'.OCIResult($q,"FILENAME").'"');
 		echo OCIResult($q,"FILE_BODY")->load();	
	}
}

//удаление файла
if(isset($del)) {
	$del=OCIParse($c,"delete from SUP_FILES where id='".$id."' and nvl(sess_id,0)=nvl('".$sid."',0) and tmp='y'");
	OCIExecute($del);	
	OCICommit($c);
}
//загрузка файла
if(isset($_FILES['new_file'])) {

	foreach($_FILES['new_file']['name'] as $key => $val) {
		
		$name=$_FILES['new_file']['name'][$key];
		$type=$_FILES['new_file']['type'][$key];
		$tmp_name=$_FILES['new_file']['tmp_name'][$key];
		$error=$_FILES['new_file']['error'][$key];
		$size=$_FILES['new_file']['size'][$key];
		
		if($size<1) {
			$div_InnerHTML.="<font color=red>Файл ".$name." не загружен: нулевой размер; ";
			continue;
		}
		if($size>3000000) {
			$div_InnerHTML.="<font color=red>Файл ".$name." не загружен: размер превышает 3 МБ; ";
			continue;
		}		
		$file_body=fread(fopen($tmp_name,'rb'),filesize($tmp_name));
	
		$ins=OCIParse($c,"insert into sup_files (id,file_body,	filename, filetype, tmp_name, fileerror, filesize, load_date, base_id, tmp, sess_id)
		values (sup_file_id.nextval||dbms_random.string('a',40),EMPTY_BLOB(),'".$name."','".$type."','".$tmp_name."','".$error."','".$size."',sysdate,'".$num_zayavki."','y','".$sid."') returning file_body into :file_body");
		
		$lob = OCINewDescriptor($c, OCI_D_LOB); 
		OCIBindByName($ins,":file_body",$lob,-1,OCI_B_BLOB);
		
		
		OCIExecute($ins, OCI_DEFAULT);
		$lob->save($file_body);
		
		OCICommit($c);		
		
				
	}
}

//список файлов со ссылками
$q_files=OCIParse($c,"select id,filename,filetype,tmp_name,fileerror,filesize,load_date,base_id,hist_id from SUP_FILES where base_id='".$num_zayavki."' and tmp='y' and nvl(sess_id,0)=nvl('".$sid."',0)
order by filename");
OCIExecute($q_files);
$div_InnerHTML.="Файлы: ";
$i=0; while(OCIFetch($q_files)) { $i++;
	$div_InnerHTML.="<a href='files.php?download&id=".OCIResult($q_files,"ID")."'>".OCIResult($q_files,"FILENAME")."</a> <a href='files.php?del&id=".OCIResult($q_files,"ID")."&".$varname."=".$num_zayavki."&sid=".$sid."' target='".$target."'><font color=red title='Удалить'>x</font></a>; ";
}
if($i>0) echo "<script>parent.document.getElementById('div_files').innerHTML='".str_replace("'","\'",$div_InnerHTML)."';</script>";
else echo "<script>parent.document.getElementById('div_files').innerHTML='';</script>";
?>
