<?php 
include("../../conf/starcall_conf/session.cfg.php");
set_time_limit(0);
ignore_user_abort(true);
set_error_handler ("my_error_handler");
include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");
include("func.phones_conv.php");
ob_implicit_flush();
extract($_POST);

if($_SESSION['user']['rw_src_bd']<>'w') {echo "<font color=red>Access DENY!</font>"; exit();}

$project_id=$_SESSION['adm']['project']['id']; //не используем сессионных переменных при загрузке, что бы не прерывать ее, сменив проект.
if(isset($uniq_term)) $_SESSION['adm']['project']['uniq_term']=$uniq_term;
else $uniq_term=$_SESSION['adm']['project']['uniq_term'];

$commit_interval=15000; //Количество строк в одной транзакции

//===========================================================================================
//проверка ОШИБОК и ПРЕДУПРЕЖДЕНИЙ
$error='';
$warning='';
$info='';

//ОШИБКА: Нечего сохранять
if(!isset($base_fields_id)) {
	$error.="<font color=red>ОШИБКА: Нечего сохранять</font><br>";
	$base_fields_id=array();
}

//ОШИБКА: Не завершена предыдущая загрузка
$q=OCIParse($c,"select count(*) cnt from STC_LOAD_HISTORY 
where status='Загружается...'");
OCIExecute($q,OCI_DEFAULT);OCIFetch($q);
if(OCIResult($q,"CNT")>0) {
	$error.="<font color=red>ОШИБКА: Не завершена предыдущая загрузка. Попробуйте позже.</font><br>";
}
//

//КВОТЫ. Если квоты нарушены, то не индексируем и не привязываем квоты, т.к. все равно их придется индексировать заново
$q=OCIParse($c,"select src_quote_broken from stc_projects where id=".$project_id);
OCIExecute($q,OCI_DEFAULT);OCIFetch($q); if(OCIResult($q,"SRC_QUOTE_BROKEN")<>'') $quote_broken='y';

	
$std_field_phone='no';
//перебираем все схраняемые поля
$ffcount=0; foreach($base_fields_id as $key=>$id) {
	//обрезаем пробелы
	$file_fields_name[$key]=trim($file_fields_name[$key]);
	$base_fields_text_name[$id]=trim($base_fields_text_name[$id]);
	$base_fields_code_name[$id]=trim($base_fields_code_name[$id]);

	if($file_fields_name[$key]<>'') {$ffcount++;}
	//проверка существования стандартного поля "Телефон"
	if(isset($base_fields_std_name[$id]) and $base_fields_std_name[$id]=='PHONE' and $file_fields_num[$key]<>'') $std_field_phone='yes';
			
	//ОШИБКА: пустые имена полей
	if($base_fields_text_name[$id]=='' or $base_fields_code_name[$id]=='') {
		$error.="<font color=red>ОШИБКА: Пустое имя или кодовое имя поля (id:$id)</font><br>";
	}
	//ОШИБКА: обязательность квотируемого поля
	if(isset($base_fields_quoted[$id]) and (!isset($base_fields_must[$id]) or isset($base_fields_uniq[$id]))) {
		$error.="<font color=red>ОШИБКА: Квотируемое поле \"$base_fields_text_name[$id]\" (id:$id) должно быть обязательным и не уникальным</font><br>";
	}
	//ОШИБКА: индексируемое поле не должно быть уникальным
	if(isset($base_fields_idx[$id]) and isset($base_fields_uniq[$id])) {
		$error.="<font color=red>ОШИБКА: Индексируемое поле \"$base_fields_text_name[$id]\" (id:$id) не должно быть уникальным</font><br>";
	}	
	//ОШИБКА: обязательному полю сопоставлено пустое поле файла
	if(isset($base_fields_must[$id]) and $file_fields_num[$key]=='') {
		$error.="<font color=red>ОШИБКА: Обязательному полю \"$base_fields_text_name[$id]\" (id:$id) сопоставлено пустое поле файла</font><br>";
	}
	//ОШИБКА: зарезервированное слово "Квота"
	if(trim($base_fields_text_name[$id])=="Квота") {
		$error.="<font color=red>ОШИБКА: Нельзя называть поле зарезервированным словом \"Квота\"</font><br>";
	}
	//ПРЕДУПРЕЖДЕНИЕ: нет смысла индексировать квотируемое поле, квота предполагает индексацию
	if(isset($base_fields_idx[$id]) and isset($base_fields_quoted[$id])) {
		unset($base_fields_idx[$id]);
		if($load_caption<>'Продолжить загрузку') {
			$warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Нет смысла индексировать квотируемое поле \"$base_fields_text_name[$id]\" (id:$id). Признак \"Индекс\" с этого поля снят.</font><br>";
		}
	}
	foreach($base_fields_id as $key2=>$id2) {
		if($id<>$id2) {
			//ОШИБКА: совпадение имени полей
			if(strtoupper($base_fields_text_name[$id])==strtoupper(trim($base_fields_text_name[$id2]))) {
				$error.="<font color=red>ОШИБКА: Дублируется имя поля \"$base_fields_text_name[$id]\" (id:$id)</font><br>";
			}
			//ОШИБКА: совпадение кодовых имени полей
			if(strtoupper($base_fields_code_name[$id])==strtoupper(trim($base_fields_code_name[$id2]))) {
				$error.="<font color=red>ОШИБКА: Дублируется кодовое имя поля \"$base_fields_code_name[$id]\" (id:$id)</font><br>";
			}
			//ОШИБКА: совпадение стандартных полей
			if($base_fields_std_name[$id]<>'' and $base_fields_std_name[$id]==$base_fields_std_name[$id2]) {
				$error.="<font color=red>ОШИБКА: Дублируется стандартное поле \"$base_fields_std_name[$id]\" (id:$id)</font><br>";
			}
			//
}	}	}
//ОШИБКА: нечего грузить
if($ffcount==0) {
	$error.="<font color=red>ОШИБКА: Нечего грузить</font><br>";
}			
//ОШИБКА: проверка роботом без поля Телефон
if($std_field_phone=='no' and isset($robot_need)) {
	$error.="<font color=red>ОШИБКА: Нет смысла требовать проверки роботом для БД без поля \"Телефон\"</font><br>";
}
//ПРЕДУПРЕЖДЕНИЕ: не выбрано поле "Телефон"
if($std_field_phone=='no' and $load_caption<>'Продолжить загрузку') {
	$warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Не задано поле Телефон (PHONE).</font><br>";
}
//КВОТЫ И ИНДЕКСЫ
if(isset($new_field)) {
	
	foreach ($new_field as $id => $fuck) {
		if(isset($base_fields_quoted[$id])) {$new_quoted[$id]=$id;} //список новых квот
		if(isset($base_fields_idx[$id])) {$new_idx[$id]=$id;} //список новых индексов
	}
}
//ПРЕДУПРЕЖДЕНИЕ: добавлены квотируемые поля 
if(isset($new_quoted)) {
	if($load_caption<>'Продолжить загрузку') $warning.="<font color=red>ПРЕДУПРЕЖДЕНИЕ: Добавлены квотируемые поля. Если продолжить сохранение, то проект будет приостановлен, а квоты, включая зависимые квоты по вопросам придется перестроить и прописать значения заново.</font><br>";
	else $src_quote_broken='y';
}
//==================================================================================================
//если есть ошибки или предупреждения, тормозим загрузку
if($error<>'') {
	echo $error;
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='Повторить';
	parent.admBottomFrame.frm_preview.load_caption.value='Повторить';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='none';
	parent.admBottomFrame.document.getElementById('load_status').innerHTML='".$error."';</script>";	
	exit();
}
if($warning<>'') {
	echo $warning;
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='Продолжить загрузку';
	parent.admBottomFrame.frm_preview.load_caption.value='Продолжить загрузку';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='';
	parent.admBottomFrame.document.getElementById('load_status').innerHTML='".$warning."';</script>";
	exit();
}
//================================================================================================
//открываем файлы загружаемый и файл ошибок
if (!$fp=fopen($uploaded_file,"r") or !$fp_err=fopen($path_to_out."load_errors.csv","w")) {
	echo "<font color=red>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</font><br>";
	echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=red><b>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</b>';</script>";
	exit();
}

//добавляем запись в историю загрузок
$ins=OCIParse($c,"insert into STC_LOAD_HISTORY (ID,PROJECT_ID,STATUS,START_DATE,FILE_NAME,FILE_SIZE_BYTES) 
values (SEQ_STC_LOAD_HIST_ID.Nextval,'".$project_id."','Загружается...',sysdate,'".$file_name."','".$file_size."')
returning id into :load_hist_id");
OCIBindByName($ins,":load_hist_id",$load_id,256);
OCIExecute($ins,OCI_DEFAULT);
	
$_SESSION['adm']['load_id']=$load_id;

OCICommit($c);
//


//==================================================================================================	
//ГРУЗИМ
//всякие переменные
$load_status='Готово';
$load_rows_count=0;
$found_phones_count=0;
$load_phones_count=0;
$wrong_phones_count=0;
$dublicate_phones_count=0;
$error_count=0;
$read_row_count=0;
$file_fields_count=0;
$null_row_count=0;
$null_fields_count=0;
$dublicate_count=0;
if(isset($robot_need)) $allow=''; else $allow='y';
$allow_rows_count=0;
$allow_phones_count=0;
$nlchar=array(chr(10),chr(13));
$quoted_fields=array();
$idx_fields=array();

//обновляем условие проверки уникальности полей
if(isset($uniq_term)) {
	$upd=OCIParse($c,"update STC_PROJECTS set uniq_term='".$uniq_term."' where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
}
//приостанавливаем проект, если поменялить квоты	
if(isset($src_quote_broken)) {
	$upd=OCIParse($c,"update STC_PROJECTS set SRC_QUOTE_BROKEN='yes',QST_QUOTE_BROKEN='yes',QST_STAT_BROKEN='yes', status='Приостановлен' where id='".$project_id."'");
	OCIExecute($upd,OCI_DEFAULT);
} 

//ОБНОВЛЯЕМ ПОЛЯ
//проверяем существование и получаем значение сортировки и уникальности существующих полей
$q=OCIParse($c,"select uniq,must,quoted,idx,std_field_name from STC_FIELDS t where id=:id and project_id='".$project_id."'");

//обновляем сортировку и имя из файла существующих полей
$upd=OCIParse($c,"update STC_FIELDS t set ord=:ord, last_file_field_name=:last_file_field_name
where id=:id and project_id='".$project_id."'");

//добавляем новые поля
$ins=OCIParse($c,"insert into STC_FIELDS (id,project_id,text_name,code_name,ord,src_type_id,std_field_name,uniq,must,quoted,idx,last_file_field_name)
values (:id,'".$project_id."',:text_name,:code_name,:ord,'1',:std_name,:uniq,:must,:quoted,:idx,:last_file_field_name)");

$must_field_count=0; //счетчик количества обязательных полей
$uniq_field_count=0; //счетчик количества уникальных полей
$phone_field_id=''; //идентификатор поля Телефон в БД, если пусто, значит такого поля нет
$phone_field_num=''; //номер поля Телефон в файле

//добавляем поля в БД
foreach($base_fields_id as $key=>$id) {
	OCIBindByName($q,":id",$id);
	OCIExecute($q,OCI_DEFAULT);
	if(OCIFetch($q)) {
		//если поле существует, то получаем значения уникальности и обязательности
		$base_fields_uniq[$id]=OCIResult($q,"UNIQ");
		$base_fields_must[$id]=OCIResult($q,"MUST");
		$base_fields_quoted[$id]=OCIResult($q,"QUOTED");
		$base_fields_idx[$id]=OCIResult($q,"IDX");
		$base_fields_std_name[$id]=OCIResult($q,"STD_FIELD_NAME");
		//и обановляем сортировку
		OCIBindByName($upd,":id",$id);
		OCIBindByName($upd,":ord",$key);
		OCIBindByName($upd,":last_file_field_name",$file_fields_name[$key]);
		OCIExecute($upd,OCI_DEFAULT);
		//признак нового поля, нужен для обучения стандартным полям (в конце загрузки)
		$file_fields_new[$key]='no';
	} 
	else {
		//если такого поля не оказалось, то добавляем его
		OCIBindByName($ins,":id",$id);
		OCIBindByName($ins,":ord",$key);
		OCIBindByName($ins,":text_name",$base_fields_text_name[$id]);
		OCIBindByName($ins,":code_name",$base_fields_code_name[$id]);
		OCIBindByName($ins,":last_file_field_name",$file_fields_name[$key]);
		OCIBindByName($ins,":std_name",$base_fields_std_name[$id]);
		OCIBindByName($ins,":uniq",$base_fields_uniq[$id]);
		OCIBindByName($ins,":must",$base_fields_must[$id]); 
		OCIBindByName($ins,":quoted",$base_fields_quoted[$id]); 
		OCIBindByName($ins,":idx",$base_fields_idx[$id]); 
		OCIExecute($ins,OCI_DEFAULT);
		//признак нового поля, нужен для обучения стандартным полям (в конце загрузки)
		$file_fields_new[$key]='yes';
	}
	//считаем кол-во уникальных обязательных и квотируемых полей
	$base_fields_uniq[$id]<>''?$uniq_field_count++:NULL;
	$base_fields_must[$id]<>''?$must_field_count++:NULL;
	//номер и ID поля Телефон
	if($base_fields_std_name[$id]=='PHONE' and $std_field_phone=='yes') {
		$phone_field_id=$id;
		$phone_field_num=$file_fields_num[$key];
	}
	//КВОТЫ. собираем список квотируемых полей
	$base_fields_quoted[$id]<>''?$quoted_fields[$id]=$id:NULL;	
	//ИНДЕКСЫ. собираем список индексируемых полей
	$base_fields_idx[$id]<>''?$idx_fields[$id]=$id:NULL;	
}
//обновление настроек проекта (кол-во полей)
OCIExecute(OCIParse($c, "update STC_PROJECTS set (num_src_fields,num_phone_fields)=
(select count(*), count(decode(std_field_name,'PHONE',1,NULL)) from STC_FIELDS where project_id=".$project_id.")
where id=".$project_id),OCI_DEFAULT);
//======================================================================================================================================
OCICommit($c);
//

//echo "<font color=red><br>";
	
//собираем запрос проверки на уникальность
if($uniq_field_count>0) {
	if($uniq_term=='ИЛИ') $uniq_sql="select case when count(*)=0 then 'ok' else 'error' end res".chr(13).chr(10);
	if($uniq_term=='И') $uniq_sql="select case when nvl(max(count(*)),0)<'".$uniq_field_count."' then 'ok' else 'error' end res".chr(13).chr(10);
	$uniq_sql.="from STC_FIELD_VALUES t where ".chr(13).chr(10);
	//where project_id='".$project_id."'
	//and (".chr(13).chr(10);
	$i=0; foreach($base_fields_uniq as $key=>$val) {
		if($val<>'') { 
			if($i>0) $uniq_sql.="or".chr(13).chr(10);
			$uniq_sql.="(t.project_id='".$project_id."' and t.field_id='".$key."' and t.text_value=:var".$key.")".chr(13).chr(10);
			$i++;
	}	}
	//$uniq_sql.=")".chr(13).chr(10);
	if($uniq_term=='И') $uniq_sql.="group by t.base_id".chr(13).chr(10);
	$q_uniq=OCIParse($c,$uniq_sql);
}
//

//Запрос проверки телефонов на уникальность
$q_uniq_phone=OCIParse($c,"select phone from STC_PHONES t
where t.project_id='".$project_id."' and t.phone=:phone");
	
//запрос добавления строки
$ins_row=OCIParse($c,"insert into STC_BASE (id,Project_Id,load_hist_id,allow,src_quote_id,utc_msk) 
values (SEQ_STC_BASE_ID.nextval,'".$project_id."','".$load_id."','".$allow."',:quote_id,:utc_msk)
returning id into :base_id");
	
//запрос добавления значений
$ins_val=OCIParse($c,"insert into STC_FIELD_VALUES (project_id,base_id,field_id,text_value,ord)
values ('".$project_id."',:base_id,:field_id,:value,0)");	
	
//запрос добавления телефонов
$q_ins_phone=OCIParse($c,"insert into STC_PHONES (base_id,project_id,phone,base_field_id,ord,allow,load_hist_id) 
values (:base_id,'".$project_id."',:phone,:base_field_id,:ord,'".$allow."','".$load_id."')");

//ИНДЕКСЫ. Запрос на проверку и добавление индексов по квотируемым и индексируемым
if(count($idx_fields)>0 or count($quoted_fields)>0) {
	$q_idx_check=OCIParse($c,"select id, case when i.src_idx_quote-i.src_idx_norm<=0 then 'y' else null end idx_lock from STC_SRC_INDEXES i where field_id=:field_id and value=:value");
	$q_ins_idx=OCIParse($c,"insert into STC_SRC_INDEXES (id,project_id,field_id,Value) values (SEQ_STC_INDEX_ID.nextval,".$project_id.",:field_id,:value) returning id into :index_id");
	if($allow=="y") $q_upd_idx=OCIParse($c,"update STC_SRC_INDEXES set src_idx_new=src_idx_new+1 where id=:index_id");
}
//
//КВОТЫ. Запросы на проверку и добавление квот по квотируемым
if(count($quoted_fields)>0 and !isset($quote_broken)) {
	$sql='';
	$i=0; foreach($quoted_fields as $fuck) {$i++; $i>1?$sql.=",":NULL; $sql.=":i".$i;}
	$q_quote_check=OCIParse($c,"select quote_id from (
select quote_id, count(*) cnt
from STC_SRC_QUOTE_INDEXES
where project_id=".$project_id." and index_id in (".$sql.")
group by quote_id
)
where cnt=".count($quoted_fields));
	$q_ins_quote=OCIParse($c,"insert into STC_SRC_QUOTES (id,project_id,field_count) values (SEQ_STC_QUOTE_ID.nextval,".$project_id.",".count($quoted_fields).") returning id into :quote_id");
	$q_ins_quote_idx=OCIParse($c,"insert into STC_SRC_QUOTE_INDEXES (project_id,quote_id,index_id) values (".$project_id.",:quote_id,:index_id)");
	if($allow=="y") $q_upd_quote=OCIParse($c,"update STC_SRC_QUOTES set src_new=src_new+1 where id=:quote_id");
}
//КВОТЫ И ИНДЕКСЫ. Запрос на привязку записи
if((count($quoted_fields)>0 or count($idx_fields)>0) and !isset($quote_broken)) {
	$q_upd_base=OCIParse($c,"update STC_BASE set src_quote_id=:quote_id, lock_by_index=:lock_by_index where id=:base_id");	
}
//=====================================================================================
$i=0; while($str=fgetcsv($fp,1024*1024,";",'"')) {$i++; //без подсчета байт
	$UTC_MSK='';
	if($i==1) { //первая строка (заголовки полей)
		fput_err($fp_err,'строка','ошибка','ош.данные',implode('";"',$str));
		$file_fields_count=count($str);
continue;
	}
	if($i/500==round($i/500)) echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=black><b>Идет загрузка...</b></font> Строк. Прочитано: $read_row_count; Загружено: $load_rows_count; Пустых: $null_row_count; Дубликатов: $dublicate_count; Найдено телефонов: $found_phones_count; Загружено телефонов: $load_phones_count';</script>";
	$read_row_count++;
	//проверка обязательности и уникальности
	if($must_field_count>0 or $uniq_field_count>0) {
		$ii=0; $must_err=''; 
		foreach($file_fields_num as $key=>$ffnum) { //key - нидекс, ffnum - номер столбца в файле
			//обязательность
			$must_err='';
			if($must_field_count>0) {			
				if($base_fields_must[$base_fields_id[$key]]<>'') {
					if(!isset($str[$ffnum]) or trim($str[$ffnum])=='') { //отсутствующее поле
						$must_err=$base_fields_text_name[$base_fields_id[$key]];
		break;
			}	}	}
			//
			//уникальность (биндим переменные)
			if($uniq_field_count>0) {
				if($base_fields_uniq[$base_fields_id[$key]]<>'') {
					$bindvarname[$ii]=":var".$base_fields_id[$key];
					isset($str[$ffnum])?$bindvalue[$ii]=trim($str[$ffnum]):$bindvalue[$ii]=''; //отсутствующее поле
					OCIBindByName($q_uniq,$bindvarname[$ii],$bindvalue[$ii]);
					$ii++; //привязанная OCBindByName переменная не должна изменяться до момента выполнения запроса, поэтому делаем массив со счетчиком $ii
		}	}	}
		//
	
		//обязательнсть, если ошибка, то пропускаем строку
		if($must_err<>'') {
			$null_row_count++;
			echo "$i: пустое обяз. поле \"$must_err\"<br>";
			fput_err($fp_err,$i,"пустое обяз. поле:","\"\"$must_err\"\"",implode('";"',str_replace('"','""',$str)));
continue;			
		}
		//уникальность (выполняем запрос проверки)
		if($ii>0) {
			OCIExecute($q_uniq,OCI_DEFAULT);
			OCIFetch($q_uniq);
			if(OCIResult($q_uniq,"RES")=='error') {
				$dublicate_count++;
				echo "$i: дубликат по уникальному полю(ям).<br>";
				fput_err($fp_err,$i,"дубликат по уникальному полю(ям).",'',implode('";"',str_replace('"','""',$str)));
continue;			
	}	}	}
	//

	//получаем массив значений для добавления
	$f=0; $f_err=0;
	$ins_values_arr=array();
	foreach($file_fields_num as $key=>$ffnum) { //поля файла нумеруются с нуля
		if($ffnum=='') { //если нет столбца с таким номером в файле
			unset($file_fields_num[$key]); 
	continue;
		} 
		$val=trim($str[$ffnum]);
		if($val=='') { //пустая ячейка в файле
			$null_fields_count++; 
	continue;
		} 
		//проверка ВАЛИДНОСТИ данных
		//проверка валидности часового пояса
		if($base_fields_std_name[$base_fields_id[$key]]=='UTC_MSK') {
			//if(!preg_match("/^[+-]{0,1}\d{1,2}(,\d{1,2}){0,1}$/",$val)) {
			$UTC_MSK=str_replace(",",".",$val);
			if(!is_numeric($UTC_MSK) or $UTC_MSK<-15 or $UTC_MSK>11) {
				echo "$i: не верный часовой пояс<br>";
				$UTC_MSK='';
				fput_err($fp_err,$i,"не верный часовой пояс",$val,implode('";"',str_replace('"','""',$str)));
				$f_err++;			
	break;
			}
			else {$val=str_replace(".",",",$val); $UTC_MSK=$val;}	
				
		}
		//если все в порядке, добавляем значение в строку
		$ins_values_arr[$base_fields_id[$key]]=$val;
		$f++;
	}
	if($f_err>0) {
		$error_count++;
continue;	
	}	
	//пустая строка
	if($f==0) {
		$null_row_count++; 
		echo "$i: пустая строка<br>";
		fput_err($fp_err,$i,"пустая строка",'',implode('";"',str_replace('"','""',$str)));
		$f_err++;
continue;
	}
	//поле Телефон. Получаем массив с телефонами для добавления
	if($phone_field_id<>'') { //если есть поле Телефон
		//поиск и конвертация номеров
		$p=0; $d=0; 
		$ins_phones_arr=array();
		$phones=phones_conv(str_replace($nlchar,';',$str[$phone_field_num]));
		foreach($phones as $phone) {
			//не верный телефон
			if($phone['err']=='y') {
				echo "$i: не верный телефон: \"".$phone['phone']."\"<br>";
				fput_err($fp_err,$i,"не верный телефон:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
				$wrong_phones_count++;
		continue;
			}
			$p++;
			$found_phones_count++;
			//проверка на уникальность
			if($base_fields_uniq[$phone_field_id]<>'') {
				//ищем в текущем массиве
				$d1=0;
				foreach($ins_phones_arr as $phone1) {
					if($phone1==$phone['phone']) {
						echo "$i: дубл. телефон: \"".$phone['phone']."\"<br>";
						fput_err($fp_err,$i,"дубл. телефон в строке:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
						$d++;
						$d1++;
						$dublicate_phones_count++;
					break;					
				}	}
		if($d1>0) continue;		
				//и в БД
				OCIBindByName($q_uniq_phone,":phone",$phone['phone']);
				OCIExecute($q_uniq_phone,OCI_DEFAULT);
				if(OCIFetch($q_uniq_phone)) {
					echo "$i: дубл. телефон в БД: \"".$phone['phone']."\"<br>";
					fput_err($fp_err,$i,"дубл. телефон в БД:","\"\"".$phone['phone']."\"\"",implode('";"',str_replace('"','""',$str)));
					$d++;
					$dublicate_phones_count++;
		continue;		
			}	}			
			$ins_phones_arr[]=$phone['phone'];
		}
		//уникальность, если все найденные телефоны дублирутся
		if($p<>0 and $d==$p and $base_fields_uniq[$phone_field_id]<>'') {
			$dublicate_count++;
			$f_err++;
			echo "$i: дубликат по всем номерам<br>";
			fput_err($fp_err,$i,"дубл. по всем номерам",'',implode('";"',str_replace('"','""',$str)));
continue;
		}
		//обязательность (если не найдено ни одного номера)
		if($p==0 and $base_fields_must[$phone_field_id]<>'') {
			$null_row_count++;
			$f_err++;
			echo "$i: не удалось найти телефон<br>";
			fput_err($fp_err,$i,"не удалось найти телефон",'',implode('";"',str_replace('"','""',$str)));
continue;			
	}	}
	//конец всех проверок
	//=========================================================================
	//Добавление СТРОКИ
	OCIBindByName($ins_row,":base_id",$base_id,256);
	OCIBindByName($ins_row,":quote_id",$quote_id);
	OCIBindByName($ins_row,":utc_msk",$UTC_MSK);
	OCIExecute($ins_row,OCI_DEFAULT);
	
	//Добавление значений
	$quote_index=array();
	$idx_lock='';
	$x=0;
	foreach($ins_values_arr as $field_id => $val) {
		OCIBindByName($ins_val,":base_id",$base_id);
		OCIBindByName($ins_val,":field_id",$field_id);
		OCIBindByName($ins_val,":value",$val);
		OCIExecute($ins_val,OCI_DEFAULT);
		//ИНДЕКСЫ. проверка существования и добавление индексов
		if(isset($idx_fields[$field_id]) or isset($quoted_fields[$field_id])) {
			OCIBindByName($q_idx_check,":field_id",$field_id);
			OCIBindByName($q_idx_check,":value",$val);
			OCIExecute($q_idx_check,OCI_DEFAULT);
			if(OCIFetch($q_idx_check)) { //если индекс существует 
				$index_id=OCIResult($q_idx_check,"ID");
				//блокировка, если превышена квота (выполняем процедуру в конце)
				//$idx_lock=OCIResult($q_idx_check,"IDX_LOCK");
				if(OCIResult($q_idx_check,"IDX_LOCK")=='y') $idx_lock='y';
				//и это поле квотируемое, запоминаем ID индекса квоты
				if(isset($quoted_fields[$field_id])) {
					$x++; 
					$quote_index[$x]=$index_id;
				}
			}
			else { //если не существует, то добавляем индекс
				OCIBindByName($q_ins_idx,":index_id",$index_id,256);
				OCIBindByName($q_ins_idx,":field_id",$field_id);
				OCIBindByName($q_ins_idx,":value",$val);
				OCIExecute($q_ins_idx,OCI_DEFAULT);	
				//если это поле квотируемое, запоминаем ID нового индекса квоты
				if(isset($quoted_fields[$field_id])) {$x++; $quote_index[$x]=$index_id;}							
			}		
			//обновление статистики, прибавляем единичку по независимой квоте (индексу)
			if($allow=='y') {
				OCIBindByName($q_upd_idx,":index_id",$index_id);
				OCIExecute($q_upd_idx,OCI_DEFAULT);
			}
		}
	}
	//КВОТЫ. Проверка существования и добавление квот
	if(!isset($quote_broken)) {
		$quote_id='';
		if(count($quoted_fields)>0) {
			foreach($quote_index as $x => $index_id) {
				OCIBindByName($q_quote_check,":i".$x,$quote_index[$x]);
			}
			OCIExecute($q_quote_check,OCI_DEFAULT);
			//если квота существует
			if(OCIFetch($q_quote_check)) {
				$quote_id=OCIResult($q_quote_check,"QUOTE_ID");
			}
			else { //если квота не существует, то добавление квоты
				$qst_quote_broken='y';
				OCIBindByName($q_ins_quote,":quote_id",$quote_id,256);
				OCIExecute($q_ins_quote,OCI_DEFAULT);
				//добавление индексов в квоту
				foreach($quote_index as $index_id) {
					OCIBindByName($q_ins_quote_idx,":quote_id",$quote_id);
					OCIBindByName($q_ins_quote_idx,":index_id",$index_id);
					OCIExecute($q_ins_quote_idx,OCI_DEFAULT);
				}
			}
			//Привязка записи к ковте по исходным полям и обновление статистики
			if($allow=="y") {
				OCIBindByName($q_upd_quote,":quote_id",$quote_id);
				OCIExecute($q_upd_quote,OCI_DEFAULT);
			}
		}
		//Привязка записи к ковте по исходным полям и обновление статистики
		if($quote_id<>'' or $idx_lock=='y') {
			OCIBindByName($q_upd_base,":base_id",$base_id);
			OCIBindByName($q_upd_base,":quote_id",$quote_id);
			OCIBindByName($q_upd_base,":lock_by_index",$idx_lock);
			OCIExecute($q_upd_base,OCI_DEFAULT);			
		}
	}	
	
	//Добавление телефонов
	if($phone_field_id<>'') { //если есть поле Телефон
		foreach($ins_phones_arr as $ord => $phone) {	
			OCIBindByName($q_ins_phone,":base_id",$base_id);
			OCIBindByName($q_ins_phone,":phone",$phone);
			OCIBindByName($q_ins_phone,":base_field_id",$phone_field_id);
			OCIBindByName($q_ins_phone,":ord",$ord);
			OCIExecute($q_ins_phone,OCI_DEFAULT);
			$load_phones_count++;
		}
	}

	$load_rows_count++; 	
	//коммит через указанный интервал строк
	if($load_rows_count>0 and round($load_rows_count/$commit_interval)==$load_rows_count/$commit_interval) {
		OCICommit($c);
		//отмена загрузки.
		$q_abort_load=OCIParse($c,"select abort_load from STC_LOAD_HISTORY where id=".$load_id);
		OCIExecute($q_abort_load,OCI_DEFAULT);
		OCIFetch($q_abort_load);
		if(OCIResult($q_abort_load,"ABORT_LOAD")<>'') {
			$load_status='Прервано';
break;
		}
	}
}

//КВОТЫ Предупреждаем о необходимости прописать квоты, если добавились значения
if(isset($qst_quote_broken)) { 
	//здесь надо выполнить процедуру добавления квот по вопросам
	OCIExecute(OCIParse($c,"begin stc_add_qst_quotes(".$project_id."); end;"));
	
	$info.="<font color=red>ВНИМАНИЕ! Добавлены новые квоты, не забудьте прописать значения</font><br>";
//	$upd=OCIParse($c,"update STC_PROJECTS set QST_QUOTE_BROKEN='yes' where id='".$project_id."'");
//	OCIExecute($upd,OCI_DEFAULT);
	echo $info;
/*	echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";   */
}
OCICommit($c);

//обучение стандартных полей
$q=OCIParse($c,"select t.std_field_name, t.ok, t.wrong from STC_LI_STANDARD_SYNONYM t
where t.std_synonym=:synon");
$q_ins=OCIParse($c,"insert into STC_LI_STANDARD_SYNONYM (std_field_name, std_synonym, ok) values (:std_name,:synon,1)");
$q_upd=OCIParse($c,"update STC_LI_STANDARD_SYNONYM set std_field_name=:std_name,ok=:ok,wrong=:wrong where std_synonym=:synon and locked is null");
$q_del=OCIParse($c,"delete from STC_LI_STANDARD_SYNONYM where std_synonym=:synon and locked is null");

foreach($file_fields_num as $key=>$ffnum) {
	if(strlen($ffnum)<>'') {
		$std_name='';
		//приводим имя поля в файле к синониму
		$ffsyn=strtoupper(str_replace(' ','',$file_fields_name[$key]));
		//если длина получившегося синонима >= 3
		if(strlen($ffsyn)>=3) {
			//ищем синоним в БД
			OCIBindByName($q,":synon",$ffsyn);
			OCIExecute($q,OCI_DEFAULT);
			if(OCIFetch($q)) {
				$ok=OCIResult($q,"OK");
				$wrong=OCIResult($q,"WRONG");
				$std_name=OCIResult($q,"STD_FIELD_NAME");
			}
			//
			//1. если полю в файле назначено стандартное поле и такого синонима нет в базе
			if($base_fields_std_name[$base_fields_id[$key]]<>'' and $std_name=='') {
echo "1. в файле: ".$file_fields_name[$key]."; синоним: ".$ffsyn."; старое стандартное: ".$std_name."; новое стандартное: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//тогда добаляем его
				OCIBindByName($q_ins,":std_name",$base_fields_std_name[$base_fields_id[$key]]);
				OCIBindByName($q_ins,":synon",$ffsyn);
				OCIExecute($q_ins,OCI_DEFAULT);
			}
			//2. если такой синоним есть, и его стандартное поле совпадает с новым стандартным полем
			else if($std_name<>'' and $std_name==$base_fields_std_name[$base_fields_id[$key]]) {
echo "2. в файле: ".$file_fields_name[$key]."; синоним: ".$ffsyn."; старое стандартное: ".$std_name."; новое стандартное: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//+1 к успешному счетчику
				$ok++;
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);
				OCIExecute($q_upd,OCI_DEFAULT);				
			}
			//3. если такой синоним есть, и его стандартное поле не совпадает с новым стандартным полем или оно пустое и кол-во предыдущих опровергающих загрузок < 1
			else if($std_name<>'' and $std_name<>$base_fields_std_name[$base_fields_id[$key]] and $wrong < 1) {
echo "3. в файле: ".$file_fields_name[$key]."; синоним: ".$ffsyn."; старое стандартное: ".$std_name."; новое стандартное: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//+1 к счетчику опровергающих загрузок
				$wrong++;
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);
				OCIExecute($q_upd,OCI_DEFAULT);
			}
			
			//4. если такой синоним есть, и его стандартное поле пустое и кол-во предыдущих опровергающих загрузок > 0
			else if($std_name<>'' and $base_fields_std_name[$base_fields_id[$key]]=='' and $wrong > 0) {
echo "4. в файле: ".$file_fields_name[$key]."; синоним: ".$ffsyn."; старое стандартное: ".$std_name."; новое стандартное: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//удаляем паразита :)
				OCIBindByName($q_del,":synon",$ffsyn);
				OCIExecute($q_del,OCI_DEFAULT);
			}
			
			//5. если такой синоним есть, и его стандартное поле не совпадает с новым стандартным полем и кол-во предыдущих опровергающих загрузок > 0
			else if ($base_fields_std_name[$base_fields_id[$key]]<>'') {
echo "5. в файле: ".$file_fields_name[$key]."; синоним: ".$ffsyn."; старое стандартное: ".$std_name."; новое стандартное: ".$base_fields_std_name[$base_fields_id[$key]]."<br>"; 
				//переназначаем синоним и сбрасываем счетчики
				$ok=1;
				$wrong=0;
				$std_name=$base_fields_std_name[$base_fields_id[$key]];
				OCIBindByName($q_upd,":ok",$ok);
				OCIBindByName($q_upd,":wrong",$wrong);				
				OCIBindByName($q_upd,":std_name",$std_name);
				OCIBindByName($q_upd,":synon",$ffsyn);	
				OCIExecute($q_upd,OCI_DEFAULT);			
			}			
			//
		}
	}
}
OCICommit($c);

//echo "</font>";
	
//финальная статистика загрузки
if($allow=='y') {
	$allow_rows_count=$load_rows_count;
	$allow_phones_count=$load_phones_count;
	OCIExecute(OCIParse($c,"update STC_PROJECTS p set p.stat_new=p.stat_new+".$allow_rows_count." where p.id=".$project_id));
}
if($load_status=='Прервано') $file_row_count=''; else $file_row_count=$read_row_count;
$upd=OCIParse($c,"update STC_LOAD_HISTORY set end_date=sysdate, load_rows='".$load_rows_count."', allow_rows='".$allow_rows_count."', errors='".$error_count."', file_row_count='".$file_row_count."',file_fields='".$file_fields_count."', null_rows='".$null_row_count."', dublicates='".$dublicate_count."',found_phones='".$found_phones_count."',load_phones='".$load_phones_count."',allow_phones='".$allow_phones_count."',status='".$load_status."'
where id='".$load_id."' and project_id='".$project_id."' returning round((end_date-start_date)*24*60*60) into :dursec");
OCIBindByName($upd,":dursec",$dursec,128);
OCIExecute($upd,OCI_DEFAULT);
OCICommit($c);

//обновляем статистику скорости загрузки
$upd=OCIParse($c,"update STC_SYS_STATISTIC set avg_load_speed=(
select sum(t.file_size_bytes)/sum((end_date-start_date)*24*60*60) bytes_sec from STC_LOAD_HISTORY t
where status='Готово' and end_date-start_date>0 and t.file_size_bytes>0 and t.start_date>to_date('25.07.2015','DD.MM.YYYY')
)");
OCIExecute($upd,OCI_DEFAULT);	
OCICommit($c);

//закрываем файлы
fclose($fp);
fclose($fp_err);
unlink($uploaded_file);
echo "<script>
parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=green><b>Загрузка завершена за $dursec сек (".round($dursec/60)." мин). </b></font>Строк. Прочитано: $read_row_count; Загружено: $load_rows_count; Пустых: $null_row_count; Дубликатов: $dublicate_count; Найдено телефонов: $found_phones_count; Загружено телефонов: $load_phones_count';
parent.admBottomFrame.frm_preview.load_caption.value='';
parent.admBottomFrame.frm_preview.cancel_load.style.display='none';</script>";
echo "<hr>";
echo "ДЛИТЕЛЬНОСТЬ: $dursec сек (".round($dursec/60)." мин)<br>";
echo "ЗАГРУЖЕНО СТРОК: $load_rows_count<br>";
echo "ОДОБРЕНО СТРОК: $allow_rows_count<br>";
echo "НАЙДЕНО ТЕЛЕФОНОВ: $found_phones_count<br>";
echo "ЗАГРУЖЕНО ТЕЛЕФОНОВ: $load_phones_count<br>";
echo "НЕ ВЕРНЫХ ТЕЛЕФОНОВ: $wrong_phones_count<br>";
echo "ДУБЛ. ТЕЛЕФОНОВ: $dublicate_phones_count<br>";
echo "ОДОБРЕНО ТЕЛЕФОНОВ: $allow_phones_count<br>";
echo "ОТКЛОНЕНО ПУСТЫХ СТРОК: $null_row_count<br>";
echo "ОТКЛОНЕНО ДУБЛИКАТОВ: $dublicate_count<br>";
echo "ОТКЛОНЕНО ОШИБОК: $error_count<br>";

echo "<script>parent.admMainTopFrame.location=parent.admMainTopFrame.location.href;</script>";


//ФУНКЦИИ
function fput_err($fp_err,$rownum,$err,$data,$src_row) {
	$row='"'.$rownum.'";"'.$err.'";"'.$data.'";"'.$src_row.'"'.chr(13).chr(10);
	fputs($fp_err,$row,1024*1024);

}

function my_error_handler($code, $msg, $file, $line) {
	global $load_id;
	include("../../conf/starcall_conf/conn_string.cfg.php");
	$upd=OCIParse($c,"update STC_LOAD_HISTORY set end_date=sysdate, status='Ошибка'
where id='".$load_id."'"); OCIExecute($upd,OCI_DEFAULT); OCICommit($c);
	echo "<font color=red><hr>ОШИБКА: ".$code."; ".$msg."; ".$file."; ".$line."</font>";
	echo "<script>parent.admBottomFrame.document.getElementById('load_status').innerHTML='<font color=red>ОШИБКА: ".$code."; ".(str_replace('\'',' ',$msg))."; ".(str_replace('\'',' ',$file))."; ".(str_replace('\'',' ',$line)).".</font>';</script>";
	echo "<script>
	parent.admBottomFrame.frm_preview.load.disabled=false;
	parent.admBottomFrame.frm_preview.load.value='Повторить';
	parent.admBottomFrame.frm_preview.load_caption.value='';
	parent.admBottomFrame.frm_preview.cancel_load.style.display='none';
	</script>";
	exit();
}
?>
