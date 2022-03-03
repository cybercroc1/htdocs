<?php include("../../conf/starcall_conf/session.cfg.php"); 
$_SESSION['refresh_lock_project']='n';
$_SESSION['refresh_lock_records']='n';
?>
<html>
<head>
<meta http-equiv=Content-Type content="text/html; charset=windows-1251">
<link href="starcall.css" rel="stylesheet" type="text/css">
</head>
<body id=bbb topmargin="8">	
<iframe name=hidden_frame style="display:none"></iframe>
<script src="func.row_select.js"></script>
<script src="adm.loads.load_data.js"></script>
<?php 
extract($_REQUEST);

if(!isset($_SESSION['adm']['project']['id']) or $_SESSION['adm']['project']['id']=='') exit();
if($_SESSION['user']['rw_src_bd']=='') {echo "<font color=red>Access DENY!</font>"; exit();}

echo "<form method=post name=frm_sel_file enctype=\"multipart/form-data\">";

//хедер-футер. ХЕДЕР
echo "<table class=content_table><tr><td class=header_td>";

echo " | ";
echo "<a href='adm.loads.loads.php'>Менеджер загрузок</a> | ";
echo "<a href='adm.loads.fields.php'>Настройка исх. полей</a> | ";
echo "<a href='adm.loads.load_data.php'><font size=4>Загрузка из .CSV</font></a> | ";
echo "<font align=right><a href='help.adm.loads.load_data.html' target='_blank'>Справка</a></font>";
echo "<hr>";

include("../../conf/starcall_conf/conn_string.cfg.php");
include("../../conf/starcall_conf/path.cfg.php");

	echo "<font size=4>Проект: ".$_SESSION['adm']['project']['name']." (id:".$_SESSION['adm']['project']['id'].")</font><br>"; 

	echo "<b>Загрузка данных</b><br>";
	echo "Выберите файл <input type=file name=new_file onchange=change()>
	<input type=submit name=preview disabled value='Прочитать заголовки из файла'><br>";	

if (!isset($preview) and !isset($load)) {echo "<font size='-1'><b>Требования к загружаемому файлу:<br>
Первая строка содержит названия полей
</b></font><br>";}
	
	//проверка активной загрузки
	$q=OCIParse($c,"select id cnt from STC_LOAD_HISTORY t
where project_id='".$_SESSION['adm']['project']['id']."' and status='Загружается...'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		$disabled='y';
	} 
	if(isset($disabled)) {
		echo "<font size=3 color=red>ВНИМАНИЕ! Идет загрузка. Повторная загрузка запрещена.</font><br>";
	}
	//

echo "<hr>";
echo "</form>";

//Хедер-футер. КОНТЕНТ
echo "</td></tr><tr><td class=content_td><div class=content_div>";

if (isset($preview)) {
	$file_fields=array();
	$base_fields=array();
	
	echo "<form name=frm_preview method=post action='adm.loads.load_data.load.php' target='logFrame'>";	

	//id загрузки и пароль для отмены
	$q=OCIParse($c, "select SEQ_STC_LOAD_HIST_ID.Nextval load_id, dbms_random.string('A',30) abort_pwd from dual");
	OCIExecute($q,OCI_DEFAULT);
	OCIFetch($q);
	$_SESSION['adm']['load_id']=OCIResult($q,"LOAD_ID");
	$abort_pwd=OCIResult($q,"ABORT_PWD");
	echo "<input type=hidden name=load_id value=".$_SESSION['adm']['load_id'].">";
	echo "<input type=hidden name=abort_pwd value='".$abort_pwd."'>";

	//получаем из БД уже существующие поля в массив base_fields
	$q=OCIParse($c,"select t.id,t.text_name,t.code_name,t.std_field_name,s.description,t.uniq,t.must,t.quoted,t.last_file_field_name,t.idx 
  	from STC_FIELDS t,STC_LI_STANDARD_FIELDS s
  	where s.name(+)=t.std_field_name
	and t.project_id='".$_SESSION['adm']['project']['id']."' and t.src_type_id='1'
	order by t.ord");
	OCIExecute($q, OCI_DEFAULT);
	$i=0; while(OCIFetch($q)) { //поля БД нумеруются с нуля
		$base_fields[$i]['id']=OCIResult($q,"ID");
		$base_fields[$i]['text_name']=OCIResult($q,"TEXT_NAME");
		$base_fields[$i]['code_name']=OCIResult($q,"CODE_NAME");
		$base_fields[$i]['last_file_field_name']=OCIResult($q,"LAST_FILE_FIELD_NAME");
		$base_fields[$i]['std_field_name']=OCIResult($q,"STD_FIELD_NAME");
		$base_fields[$i]['std_field_desc']=OCIResult($q,"DESCRIPTION");
		$base_fields[$i]['uniq']=OCIResult($q,"UNIQ");
		$base_fields[$i]['must']=OCIResult($q,"MUST");
		$base_fields[$i]['quoted']=OCIResult($q,"QUOTED");
		$base_fields[$i]['idx']=OCIResult($q,"IDX");
		$base_fields[$i]['new_field']='n';
		$i++;
	}
	if($i==0) $first_load='y';
	//
	
	//статистика скорости загрузки
	$q=OCIParse($c,"select avg_load_speed from STC_SYS_STATISTIC t");
	OCIExecute($q); OCIFetch($q); $avg_load_speed=OCIResult($q,"AVG_LOAD_SPEED");
	
	//читаем файл
	echo "Файл: <b>".$_FILES['new_file']["name"]."</b>; размер: <b>".$_FILES['new_file']["size"]." байт</b>; ";
	if($avg_load_speed<>0) echo "прибл. вр. загрузки: <b>".round($_FILES['new_file']["size"]/$avg_load_speed)."</b> сек (<b>".round($_FILES['new_file']["size"]/$avg_load_speed/60)."</b> мин)<br>";
	 
	if($_FILES["new_file"]["size"] > 1024*100*1024) {
		echo ("</font color=red>Размер файла превышает 3 мегабайта!</font>"); exit();
	}
	else {
	 	if (!is_uploaded_file($_FILES['new_file']["tmp_name"])) {
			echo "<font color=red>ОШИБКА ЗАГРУЗКИ ФАЙЛА!</font>";
		}
		else {
			$fp=fopen($_FILES['new_file']["tmp_name"],"r");
			
			$str=fgetcsv($fp,1024*1024,";");
			//получаем название полей из файла в массив file_fields
			foreach ($str as $j => $val) { //поля файла нумеруются с нуля
					if(trim($val)=='') { //пропускаем поля с пустым названием
						$file_fields[$j]['text_name']='';
						$file_fields[$j]['num']='';
						$file_fields[$j]['std_field_name']='';
					}
					else {
						$file_fields[$j]['text_name']=trim($val);
						$file_fields[$j]['num']=$j;
						$file_fields[$j]['std_field_name']='';
		}}}
		fclose($fp);
		//
		//добавляем недостающие по количеству поля к базе
		if(count($base_fields)<count($file_fields)) {
			for($i=count($base_fields); $i<count($file_fields); $i++) { //поля БД нумеруются с нуля
				$q=OCIParse($c,"select SEQ_STC_FIELDS_ID.nextval from dual");
				OCIExecute($q, OCI_DEFAULT); OCIFetch($q);
				$base_fields[$i]['id']=OCIResult($q,"NEXTVAL");
				if(isset($first_load)) {
					if(trim($file_fields[$i]['text_name'])=='') $base_fields[$i]['text_name']='Новое поле-'.OCIResult($q,"NEXTVAL");
					else $base_fields[$i]['text_name']=$file_fields[$i]['text_name'];
					//$base_fields[$i]['std_field_name']='';
					//$base_fields[$i]['new_field']='y';
				}
				else {
					$base_fields[$i]['text_name']='Новое поле-'.OCIResult($q,"NEXTVAL");
				}
				$base_fields[$i]['code_name']='F'.OCIResult($q,"NEXTVAL");
				$base_fields[$i]['std_field_name']='';
				$base_fields[$i]['uniq']='';
				$base_fields[$i]['must']='';
				$base_fields[$i]['quoted']='';
				$base_fields[$i]['idx']='';			
				$base_fields[$i]['new_field']='y';
		}}
		//
		//добавляем недостающие по количеству поля к файлу
		if(count($file_fields)<count($base_fields)) { //поля файла нумеруются с нуля
			for($i=count($file_fields); $i<count($base_fields); $i++) {
				$file_fields[$i]['text_name']='';
				$file_fields[$i]['num']='';
				$file_fields[$i]['std_field_name']='';
			}
		}
		//

		
		//запрос на поиск подходящего стандартного поля по синониму (запрос применяется в 2-х местах)
		$q_std=OCIParse($c,"select t.std_field_name from STC_LI_STANDARD_SYNONYM t where t.std_synonym=:ffsyn");
		
		//ищем подходящие по синониму имена стандартных полей
		foreach ($file_fields as $ffkey => $ff) {
			if($ff['text_name']<>'') {
				$ffsyn=strtoupper(str_replace(' ','',$ff['text_name']));
				OCIBindByName($q_std,":ffsyn",$ffsyn);
				OCIExecute($q_std,OCI_DEFAULT);
				if(OCIFetch($q_std)) $ff['std_field_name']=OCIResult($q_std,"STD_FIELD_NAME");
			}
		}
		
		//перебираем поля БД
		//если есть совпадающие по имени поля в файле и БД или предыдущим названием файла или одним из синонимов стандартного поля, то сортируем поля файла в соответствии с БД:
		foreach ($base_fields as $bfkey => $bf) {
			foreach ($file_fields as $ffkey => $ff) {
				if(
				strtoupper(trim($bf['text_name']))==strtoupper(trim($ff['text_name'])) 
				or (isset($bf['last_file_field_name']) and strtoupper(trim($bf['last_file_field_name']))==strtoupper(trim($ff['text_name'])))
				or ($bf['std_field_name']<>'' and $bf['std_field_name']==$ff['std_field_name'])
				) {
					$bf_temp=$file_fields[$bfkey];
					$file_fields[$bfkey]=$file_fields[$ffkey];
					$file_fields[$ffkey]=$bf_temp;
		}}}
		

		
		//если не первая загрузка, то присваиваем имена из файла новым полям бд 
		if(!isset($first_load)) {
			foreach($file_fields as $ffkey => $ff) {
				if($base_fields[$ffkey]['new_field']=='y') {
					$base_fields[$ffkey]['text_name']=$ff['text_name'];
				}
			}
		}
		
		
		//формируем список стандартных полей
		$q=OCIParse($c,"select t.name,t.description from STC_LI_STANDARD_FIELDS t
order by t.description");
		OCIExecute($q);
		echo "<script>";
		$i=0; while(OCIFetch($q)) {
			//$std_fields[$i]['name']=OCIResult($q,'NAME');
			//$std_fields[$i]['description']=OCIResult($q,'DESCRIPTION');
			$std_fields[OCIResult($q,'NAME')]=OCIResult($q,'DESCRIPTION');
			echo "std_field_name[$i]='".OCIResult($q,'NAME')."'; std_field_desc[$i]='".OCIResult($q,'DESCRIPTION')."';";
		$i++;
		}
		echo "</script>";
		//

		echo "<table id=tbl name=tbl style='table-layout:fixed'>";
		echo "<tr>
		<th width=20></th>
		<th width=20></th>
		<th colspan=2>В ФАЙЛЕ</th>
		<th width=540 colspan=5>В БАЗЕ</th>
		<th rowspan=2>УНИК.<br>";
		
		if(isset($first_load)) echo "<select name=uniq_term>
		<option value=И".($_SESSION['adm']['project']['uniq_term']=='И'?' selected':NULL).">И</option>
		<option value=ИЛИ".($_SESSION['adm']['project']['uniq_term']=='ИЛИ'?' selected':NULL).">ИЛИ</option>
		</select>";
		else echo "(".$_SESSION['adm']['project']['uniq_term'].")";
		
		echo "</th><th></th><th></th><th></th></tr>";
		echo "<tr><th></th>";
		echo "<th style='cursor:pointer' title='Добавить строку внизу. CTRL-добавить с копированием' onClick=plus_field(this)><font color=blue>+</font></th>";
		echo "<th width=20>№</th>
		<th width=150>ИМЯ</th>
		<th width=20>№</th>
		<th width=40>ID</th>
		<th width=150>ИМЯ</th>
		<th width=80>КОДОВОЕ ИМЯ</th>
		<th width=170>СТАНД. ПОЛЕ</th>
		<th width=40>ОБЯЗ.</th>
		<th width=40>КВОТА / Перв.ключ</th>
		<th width=40>ИНДЕКС / Перв.ключ</th></tr>";
		
		//перебираем поля БД и рисуем таблицу
		foreach($base_fields as $key => $bf) {
			if($bf['new_field']=='y' and $file_fields[$key]['num']==='') continue; //если поле в БД новое, а имя поля в файле пустое, то игнорим его
			echo "<tr onMouseOver='sel_row(this)' onMouseOut='unsel_row(this)'>";
			if($bf['new_field']=='y') echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick=del_field(this)><img src='png/del.png'></img></th>";
			else echo "<th style='cursor:pointer' title='Удалить (двойной щелчок)' onDblClick=del_file_field(this)><img src='png/del.png'></img></th>";
			echo "<th style='cursor:pointer' title='Добавить строку внизу. CTRL-добавить с копированием' onClick=plus_field(this)><img src='png/plus.png'></img></th>";

			echo "<td>".($file_fields[$key]['num']!==''?($file_fields[$key]['num']+1):NULL)."</td>
			<td style='cursor:s-resize' title='CTRL-двигать строку целиком' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onMouseMove='return false' onclick='click_row(this)'>
			<b>".trim($file_fields[$key]['text_name'])."</b>
			<input type=hidden name=file_fields_num[] value='".$file_fields[$key]['num']."'>
			<input type=hidden name=file_fields_name[] value='".trim($file_fields[$key]['text_name'])."'></th>";				
			
			echo "<td>".($key+1)."</td>
			<th style='cursor:s-resize' title='CTRL-двигать строку целиком' onMouseDown='fMD(this)' onMouseUp='fMU(this)' onclick='click_row(this)'><input type=hidden name=base_fields_id[] value='".$bf['id']."'>".$bf['id']."</th>";

			if($bf['new_field']=='y') { //новые поля разрешаем редактировать
				echo "<td onclick='click_row(this)'><input type=hidden name=new_field[".$bf['id']."]><input type=text style='width:100%' name=base_fields_text_name[".$bf['id']."] value='".$bf['text_name']."'></td>";
				echo "<td onclick='click_row(this)'><input type=text style='width:100%' name=base_fields_code_name[".$bf['id']."] value='".$bf['code_name']."'></td>";
				echo "<td>";
				
				//поиск подходящего синонима стандартного поля
				$ffsyn=strtoupper(str_replace(' ','',$file_fields[$key]['text_name']));
				OCIBindByName($q_std,":ffsyn",$ffsyn);
				OCIExecute($q_std);
				OCIFetch($q_std);
				//отображение списка выбора стандартных полей
				echo "<select name='base_fields_std_name[".$bf['id']."]' onchange='ch_std_field(".$bf['id'].")'><option></option>";
				foreach($std_fields as $name => $description) {
					echo "<option value='".$name."'".($name==OCIResult($q_std,"STD_FIELD_NAME")?' selected':'').">".$description." (".$name.")</option>";
					//echo $nameOCIResult($q_std,"STD_FIELD_NAME")."<br>";
				}
				echo "</select>";
		
				echo "</td>";
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_uniq[".$bf['id']."]".($bf['uniq']<>''?' checked':NULL)."></td>"; //уникальное
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_must[".$bf['id']."]".($bf['must']<>''?' checked':NULL)."></td>"; //обязательное
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_quoted[".$bf['id']."]".($bf['quoted']<>''?' checked':NULL)."></td>"; //квотируемое
				echo "<td onclick='click_row(this)'><input type=checkbox name=base_fields_idx[".$bf['id']."]".($bf['idx']<>''?' checked':NULL)."></td>"; //индексируемое
				echo "<script>ch_std_field(".$bf['id'].");</script>";
			}
			if($bf['new_field']=='n') { //старые поля только отображаем, редактирование запрещено
				echo "<td onclick='click_row(this)'><input type=hidden name=base_fields_text_name[".$bf['id']."] value='".$bf['text_name']."'>".$bf['text_name']."</td>";
				echo "<td onclick='click_row(this)'><input type=hidden name=base_fields_code_name[".$bf['id']."] value='".$bf['code_name']."'>".$bf['code_name']."</td>";
				echo "<td onclick='click_row(this)'><input type=hidden name=base_fields_std_name[".$bf['id']."] value='".$bf['std_field_name']."'>".$bf['std_field_desc'].($bf['std_field_name']<>''?" (".$bf['std_field_name'].")":NULL)."</td>";
				//фальшифые чекбоксы со скрытыми переменными (только для контроля изменившихся квот)
				echo "<td onclick='click_row(this)'><input type=checkbox disabled".($bf['uniq']<>''?' checked':NULL).">".($bf['uniq']<>''?'<input type=hidden name=base_fields_uniq['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td onclick='click_row(this)'><input type=checkbox disabled".($bf['must']<>''?' checked':NULL).">".($bf['must']<>''?'<input type=hidden name=base_fields_must['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td onclick='click_row(this)'><input type=checkbox disabled".($bf['quoted']<>''?' checked':NULL).">".($bf['quoted']<>''?'<input type=hidden name=base_fields_quoted['.$bf['id'].'] value=on>':NULL)."</td>";
				echo "<td onclick='click_row(this)'><input type=checkbox disabled".($bf['idx']<>''?' checked':NULL).">".($bf['idx']<>''?'<input type=hidden name=base_fields_idx['.$bf['id'].'] value=on>':NULL)."</td>";
				
			}
			//echo "<td></td>"; //это пустое поле нужно для исправления глюка с переносом ячеек в старых IE
			echo "</tr>";
		}
		echo "</table>";
		move_uploaded_file($_FILES['new_file']["tmp_name"],$path_to_tmp.$_FILES['new_file']["name"]);
		echo "<input type=hidden name='uploaded_file' value='".$path_to_tmp.$_FILES['new_file']["name"]."'>";
		echo "<input type=hidden name='file_name' value='".$_FILES['new_file']["name"]."'>";
		echo "<input type=hidden name='file_size' value='".$_FILES['new_file']["size"]."'>";
	}
	
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";	
	
	echo "<hr>";
	if(isset($disabled)) {
		echo "<font size=3 color=red>ВНИМАНИЕ! Идет загрузка. Повторная загрузка запрещена.</font><br>";
		exit();
	}	
	if($_SESSION['user']['rw_src_bd']<>'w') echo "<font color=red>Редактирование запрещено!</font>";
	else {
	echo "<div id=load_status></div>";
	echo "<input type=checkbox name=robot_need><b> требует проверки роботом</b></input>";
	echo "<input type='hidden' name='load_caption' value=Загрузить><br>";
	echo "<input type='button' name=load value=Загрузить onClick='start_load()'>  <input type='button' name='cancel_load' style='display:none' value='Прервать загрузку' onClick=fCancelLoad(".$_SESSION['adm']['load_id'].",'".$abort_pwd."')><br>";
	echo "Файл с ошибками находится здесь:<br>";
	
	echo "<a href='http://sc/local/tmp/load_errors.csv' target=_blank>http://sc/local/tmp/load_errors.csv</a><br>";
	//echo "<a href='\\\sc\\htdocs_local\\tmp\\load_errors.csv' target=_blank>\\\sc\\htdocs_local\\tmp\\load_errors.csv</a><br>";
	}
echo "</form>";

//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";

}
else {
//Хедер-футер. КОНТЕНТ
//echo "</td></tr><tr><td class=content_td><div class=content_div>";
//Хедер-футер. ФУТЕР
echo "</div></td></tr><tr><td class=footer_td>";
//Хедер-футер. КОНЕЦ
echo "</td></tr></table>";
}
?>
</body>
</html>
