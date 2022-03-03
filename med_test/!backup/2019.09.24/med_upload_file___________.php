<?php
session_name('medc');
//т.к. данный скрипт вложенный и сесия уже могла быть начата, то проверяем существование сессии
if(!isset($_SESSION['auth'])) {
	//если сессии нет, то запускаем ее
	session_start();
}
//этот блок должен быть на каждой странице, требующей авторизованного доступа
if(!isset($_SESSION['auth']) or $_SESSION['auth']<>md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])) {echo "<b style='color: red'>Доступ запрещен</b>"; exit();}
//

require_once "med/conn_string.cfg.php";
require_once "med/smtp_conf.php";
require_once "send_email.php";

extract($_REQUEST);
if(!isset($base_id) or $base_id=='') exit();

//загрузка файла
if(isset($_FILES['file'])) {

	require_once "uid.php";

    $uuid=generate_uuid();

    if($_FILES['file']['error']<>0) {echo "Файл не загружен. Ошибка: ".$_FILES['file']['error']; exit();}

    $name=iconv('utf-8','windows-1251',basename ($_FILES['file']['name']));
    $type=$_FILES['file']['type'];
    $tmp_name=$_FILES['file']['tmp_name'];
    $error=$_FILES['file']['error'];
    $size=$_FILES['file']['size'];

    if($size<1) {echo "Файл ".$name." не загружен: нулевой размер"; exit();}
    if($size>10000000) {echo "Файл ".$name." не загружен: размер превышает 10 МБ"; exit();}

    $file_body=fread(fopen($tmp_name,'rb'),filesize($tmp_name));

    $ins=OCIParse($c,"insert into med_tmp_files (uuid,call_base_id,	filename, filetype, filesize, date_add, filecontent)
    values ('".$uuid."',".$base_id.",'".$name."','".$type."',".$size.",sysdate,EMPTY_BLOB()) returning filecontent into :filecontent");

    $lob = OCINewDescriptor($c, OCI_D_LOB);
    OCIBindByName($ins,":filecontent",$lob,-1,OCI_B_BLOB);

    if(OCIExecute($ins, OCI_DEFAULT)) {
        $lob->save($file_body);
        OCICommit($c);
        echo "OK:".$uuid;
    }
	exit();
}

//удаление файла
if(isset($delete)) {
	$del=OCIParse($c,"delete from med_tmp_files where call_base_id='".$base_id."' and uuid='".$uuid."'");
	if(OCIExecute($del, OCI_DEFAULT)) {
		OCICommit($c);		
		echo "OK:".$uuid;
	}
	exit();
}

//выгрузка файла
if(isset($download)) {
	$q=OCIParse($c,"select filename,filetype,filesize,filecontent from MED_TMP_FILES where call_base_id='".$base_id."' and uuid='".$uuid."'");
	OCIExecute($q);	
	if(OCIFetch($q)) {
 		header("Content-Type: ".OCIResult($q,"FILETYPE"));
	    header('Content-Disposition: attachment; filename="'.OCIResult($q,"FILENAME").'"');
		header('Content-Length: '.OCIResult($q,"FILESIZE")); 
		header('accept-ranges: bytes');
		
		// заставляем браузер показать окно сохранения файла
		//header('Content-Description: File Transfer');
		//header('Content-Type: application/octet-stream');
		//header('Content-Disposition: attachment; filename="'.OCIResult($q,"FILENAME").'"');
		//header('Content-Transfer-Encoding: binary');
		//header('Expires: 0');
		//header('Cache-Control: must-revalidate');
		//header('Pragma: public');
		// читаем файл и отправляем его пользователю
		echo OCIResult($q,"FILECONTENT")->load();	
	}
	exit();
}

//отправка письма
if(isset($send_email)) {
	//include("send_email.php");
	$q=OCIParse($c,"select count(*) cnt from MED_TMP_FILES where call_base_id='".$base_id."'");
	OCIExecute($q);
	OCIFetch($q);
	$file_count=OCIResult($q,"CNT");
	
	if ($file_count > 0) { //собираем письмо с файлами
		$separator = md5(uniqid(time())); // разделитель в письме
		// Заголовки для письма
		$headers="MIME-Version: 1.0 \r\n";
		$headers.="Content-Type: multipart/mixed; boundary=\"$separator\"\r\n"; // в заголовке указываем разделитель

        if (10 == $_SESSION['login_id_med'] || 1 == $_SESSION['login_id_med']) {
            $mess_my =
                "--$separator\r\n" . // начало тела письма, выводим разделитель
                "Content-type: text/html; charset=\"windows-1251\"\r\n" . // кодировка письма
                //"Content-Transfer-Encoding: \"quoted-printable\"\r\n". // задаем конвертацию письма
                "\r\n" .// раздел между заголовками и телом html-части
                "Отправлено по адресу: " . $to_email . "\r\n" .
                $mess . "\r\n"; // добавляем текст письма
            $q = OCIParse($c, "select filename,filetype,filecontent from MED_TMP_FILES where call_base_id='" . $base_id . "'");
            OCIExecute($q);
            $file_list = array();
            while (OCIFetch($q)) { //формируем части письма для каждого файла
                $mess_my .= "--" . $separator . "\r\n" .
                    "Content-Type: application/octet-stream; name=\"" . OCIResult($q, "FILENAME") . "\"\r\n" .
                    "Content-Transfer-Encoding: base64\r\n" .
                    "Content-Disposition: attachment; filename=\"" . OCIResult($q, "FILENAME") . "\"\r\n" .
                    "\r\n" .// раздел между заголовками и телом файла
                    chunk_split(base64_encode(OCIResult($q, "FILECONTENT")->load()));
                $file_list[] = OCIResult($q, "FILENAME");
            }
            $mess_my .= "--" . $separator . "--\r\n"; //последний разделитель
        }

        $mess =
		"--$separator\r\n". // начало тела письма, выводим разделитель
        "Content-type: text/html; charset=\"windows-1251\"\r\n". // кодировка письма
        //"Content-Transfer-Encoding: \"quoted-printable\"\r\n". // задаем конвертацию письма
        "\r\n".// раздел между заголовками и телом html-части 
        $mess."\r\n"; // добавляем текст письма
		$q=OCIParse($c,"select filename,filetype,filecontent from MED_TMP_FILES where call_base_id='".$base_id."'");
		OCIExecute($q);	
		$file_list=array();
		while(OCIFetch($q)) {//формируем части письма для каждого файла
            $mess .= "--".$separator."\r\n".
			"Content-Type: application/octet-stream; name=\"".OCIResult($q,"FILENAME")."\"\r\n".
			"Content-Transfer-Encoding: base64\r\n".
			"Content-Disposition: attachment; filename=\"".OCIResult($q,"FILENAME")."\"\r\n".
			"\r\n".// раздел между заголовками и телом файла
			chunk_split(base64_encode(OCIResult($q,"FILECONTENT")->load()));
			$file_list[]=OCIResult($q,"FILENAME");
		}
        $mess .= "--".$separator."--\r\n"; //последний разделитель
	}
	else {
		$headers="MIME-Version: 1.0 \r\n";
		$headers.="Content-Type: text/html; charset=\"windows-1251\"\r\n";
	}
	
	$email_list=explode(',',str_replace(array(' ',';',','),',',$to_email));
	$res=array();
	$general_res='';
	$enum=0;
	foreach($email_list as $to_email) {
		$to_email=trim($to_email);
		if($to_email<>'') { 
			$enum++;
			$res[$enum]['email'] = $to_email;
			$res[$enum]['text'] = send_email($server, $port, $auth_login, $auth_pass, $to_name='', $to_email, $from_name, $from_email, $reply_to_name='', $reply_to_email='' , $mess_subj, $mess, $headers, $debug='y');		

            if (0 == strncmp($res[$enum]['text'], "OK", 2)) {
                if ($file_count == 0)
                    $mess_my = "Отправлено по адресу: " . $to_email . "\r\n" . $mess;
                if (10 == $_SESSION['login_id_med']) // Соколовой дублируем на ее почту
                    $to_email_my = 'anna@wilstream.ru';
                /*elseif (1 == $_SESSION['login_id_med'])
                    $to_email_my = '2392967@gmail.com';*/

                if (10 == $_SESSION['login_id_med'] /*|| 1 == $_SESSION['login_id_med']*/) {
                    $enum++;
                    $res[$enum]['email'] = $to_email_my;
                    $res[$enum]['text'] = send_email($server, $port, $auth_login, $auth_pass, $to_name = '', $to_email_my, $from_name, $from_email, $reply_to_name = '', $reply_to_email = '', $mess_subj, $mess_my, $headers, $debug = 'y');
                }
            }
        }
    }
    
    $ins=OCIParse($c,"insert into send_mail_hist (send_date,base_id,user_id,server,port,from_email,to_email,file_list,result)
	values (sysdate,'".$base_id."','".$_SESSION['login_id_med']."','".$server."','".$port."','".$from_email."',:to_email,'".implode("|",$file_list)."',:res)");
	foreach($res as $key => $val) {
		OCIBindByName($ins,':to_email',$val['email']);
		OCIBindByName($ins,':res',$val['text']);
		OCIExecute($ins);
		if($general_res<>'OK' and substr($val['text'],0,2)=='OK') {
			$general_res='OK';
		}
		OCICommit($c);
	}

	//if (0 == strncmp($res, "OK", 2)) {
	if ($general_res=='OK') {	
        $updatemail = " UPDATE CALL_BASE SET SENT_MAIL = sysdate WHERE ID = ".$base_id;
        $query = OCIParse($c, $updatemail);
        $query_result = OCIExecute($query);
        oci_free_statement($query);
        
		echo "<script>alert('";
		foreach($res as $key => $val) {
			if(substr($val['text'],0,2)=='OK') $val['text']='OK'; 
			echo $val['email']."-".$val['text']."; ";
		}
		echo "');</script>";
		//echo "<script>alert('Письмо отправлено.')</script>";

	}
    else { echo "<script>alert('Ошибка отправки письма!')</script>"; }
	exit();
}
echo "<script>var base_id='".$base_id."';</script>";
?>

<style>
#drop-area {
  border: 2px dashed #ccc;
  border-radius: 20px;
  width: 90%;
  font-family: sans-serif;
  margin: 0 auto;
  padding: 10px;
}
#drop-area.highlight {
  border-color: purple;
}
p {
  margin-top: 0;
}
.button {
  display: inline-block;
  padding: 10px;
  background: #ccc;
  cursor: pointer;
  border-radius: 5px;
  border: 1px solid #ccc;
}
.button:hover {
  background: #ddd;
}
#fileElem {
  display: none;
}
</style>
<hr>
<div id="drop-area">
    <p>Выберите файл или перетащите его в данную область</p>
    <input type="file" id="fileElem" multiple accept="image/*" onchange="handleFiles(this.files)" />
    <label class="button" for="fileElem">Выбрать файл</label>
</div>

<form method='post' target='hidden_ifr'>
    <input type='text' name='server' value='<?=GetData::$array_user[0]['SMTP_SERVER']?>' hidden/>
    <input type='text' name='port' value='<?=GetData::$array_user[0]['SMTP_PORT']?>' hidden/>
    <input type='text' name='from_email' value='<?=GetData::$array_user[0]['SMTP_FROM']?>' hidden/>
    <input type='text' name='from_name' value='<?=$_SESSION['login_name']?>' hidden/>
    <input type='text' name='auth_login' value='<?=GetData::$array_user[0]['SMTP_LOGIN']?>' hidden/>
    <input type='text' name='auth_pass' value='<?=GetData::$array_user[0]['SMTP_PASS']?>' hidden/><hr>

    <label for="to_email" style="font-weight: bold;">*E-mail: </label><input type='text' style='width: 500px' name='to_email' title="Адрес получателя" required /><br>
    <!--label for="to_name" style="font-weight: bold">Кому: </label><input type='text' name='to_name' /><br-->
    <b>Тема письма: <?=$mess_subj_smtp?></b><br>
	<label for="mess_comment" style="font-weight: bold">Комментарий: </label><br>
    <textarea name='mess_comment' title="Комментарий" rows=4 cols=80><?=$_SESSION['last_comment']?></textarea><br>
	<!--label for="subj" style="font-weight: bold">Тема письма: </label><input type='text' name='subj' style='width: 75%;' value='<?=$mess_subj?>'/><br-->
    <!--label for="mess" style="font-weight: bold; position: absolute">Текст: </label><div id="mess">< ?=$mess?></div-->
    <!--textarea name='mess' style='vertical-align: text-top; width: 75%; height: 90px'>< ?= htmlspecialchars($mess)?></textarea-->
    <input type='submit' name='send_email' style='cursor:pointer; width: 117px; height: 75px; background: url("<?=PATH?>/images/envelope2.png"); background-size:100% 100%;' value="" title='Отправить письмо' />
</form>
<?php
$q=OCIParse($c,"select to_char(send_date,'DD.MM.YYYY HH24:MI') send_date,from_email,to_email,file_list,result from send_mail_hist 
where base_id='".$base_id."' and user_id='".$_SESSION['login_id_med']."' order by send_date");
OCIExecute($q);
$iii=0; while(OCIFetch($q)) { $iii++;
	if($iii==1) {
	echo "<div id='SendHistory'><b> История отправки:</b><br>
	<table class=clear_table>
	<tr><th>Дата</th><th>От</th><th>Кому</th><th>Файлы</th><th>Результат отправки</th></tr>";
	}
 	echo "<tr><td>".OCIResult($q,"SEND_DATE")."</td><td>".OCIResult($q,"FROM_EMAIL")."</td><td>".OCIResult($q,"TO_EMAIL")."</td><td>".str_replace("|",";<br>",OCIResult($q,"FILE_LIST"))."</td>
	<td>"; 
	if(substr(OCIResult($q,"RESULT"),0,2)=='OK') echo "<font color=green>Отправлено</font>";
	else echo "<font color=red>".OCIResult($q,"RESULT")."</font>";
	echo "</td></tr>";
}
if($iii>0) {echo "</table></div>";}
?>
<iframe name='hidden_ifr' style='width:90%; display: none'></iframe>

<script>
var dropArea = document.getElementById('drop-area');

dropArea.addEventListener('dragenter', preventDefaults, false);
dropArea.addEventListener('dragenter', highlight, false);

dropArea.addEventListener('dragover', preventDefaults, false);
dropArea.addEventListener('dragover', highlight, false);

dropArea.addEventListener('dragleave', preventDefaults, false);
dropArea.addEventListener('dragleave', unhighlight, false);

dropArea.addEventListener('drop', preventDefaults, false);
dropArea.addEventListener('drop', unhighlight, false);
dropArea.addEventListener('drop', handleDrop, false);

function preventDefaults (e) {
  e.preventDefault();
  e.stopPropagation();
}
function highlight(e) {
  dropArea.classList.add('highlight');
}
function unhighlight(e) {
  dropArea.classList.remove('highlight');
}
function handleDrop(e) {
  var dt = e.dataTransfer;
  var files = dt.files;
  handleFiles(files);
}
function handleFiles(files) {
  for (var i = 0; i < files.length; i++) {
	uploadFile(files[i]);
  }  
}
function uploadFile(file) {
    var url = 'med_upload_file.php';
    //if (< ?PATH?> == "/med")
        //url = '/med/med_upload_file.php';
  var xhr = new XMLHttpRequest();
  var formData = new FormData();
  xhr.open('POST', url, true);
  //xhr.setRequestHeader("Content-type", "multipart/form-data");
  xhr.addEventListener('readystatechange', function(e) {
    if(xhr.readyState == 4) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			// Готово. Информируем пользователя
			res=xhr.responseText.trim();
			if(res.substr(0,3)=='OK:') {
				add_file_link(file.name,file.type,res.substr(3));
			}
			else alert(xhr.responseText);
		}
		else if (xhr.readyState == 4 && xhr.status != 200) {
		// Ошибка. Информируем пользователя
			alert(xhr.responseText);
		}
	}
  })
  formData.append('file', file);
  formData.append('base_id', base_id);
  xhr.send(formData);
}
function add_file_link(filename,filetype,uuid) {
	fl=document.createElement("div");
	fl.id=uuid;
	fl.innerHTML='<nobr><a href=med_upload_file.php?download&base_id='+base_id+'&uuid='+uuid+'>'+filename+'</a> (<a href=javascript:deleteFile("'+uuid+'") title="Удалить файл"><font color=red>x</font></a>)</nobr><br>';
	if(filetype.substr(0,5)=='audio') {
		fl.innerHTML+='<audio controls preload=metadata style="width:100%"><source src="med_upload_file.php?download&base_id='+base_id+'&uuid='+uuid+'" type="audio/ogg; codec=vorbis"><source src="med_upload_file.php?download&base_id='+base_id+'&uuid='+uuid+'" type="'+filetype+'"></audio>';
	}
	if(filetype.substr(0,5)=='image') {
		fl.innerHTML+='<img width=100% src="med_upload_file.php?download&base_id='+base_id+'&uuid='+uuid+'" type="'+filetype+'"></img>';
	}
	document.getElementById('drop-area').appendChild(fl);
}

function deleteFile(uuid) {
    var url = 'med_upload_file.php';
    //if (< ?PATH?> == "/med")
        //url = '/med/med_upload_file.php';
  var xhr = new XMLHttpRequest();
  var formData = new FormData();
  xhr.open('POST', url, true);
  xhr.addEventListener('readystatechange', function(e) {
    if(xhr.readyState == 4) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			// Готово. Информируем пользователя
			res=xhr.responseText.trim();
			if(res.substr(0,2)=='OK') {
				delete_file_link(uuid);
			}
			else alert(xhr.responseText);
		}
		else if (xhr.readyState == 4 && xhr.status != 200) {
		// Ошибка. Информируем пользователя
			alert(xhr.responseText);
		}
	}
  })
  formData.append('delete', '');
  formData.append('base_id', base_id);
  formData.append('uuid', uuid);
  xhr.send(formData);
}
function delete_file_link(uuid) {
	fl=document.getElementById(uuid);
	document.getElementById('drop-area').removeChild(fl);
}
</script>

<?php
$q=OCIParse($c,"select uuid,filename,filetype from MED_TMP_FILES where call_base_id='".$base_id."' order by filename");
OCIExecute($q);
echo "<script>";
while(OCIFetch($q)) {
	echo "add_file_link('".OCIResult($q,"FILENAME")."','".OCIResult($q,"FILETYPE")."','".OCIResult($q,"UUID")."');"; 
}
echo "</script>";
?>



