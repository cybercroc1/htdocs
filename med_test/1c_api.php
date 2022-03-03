<?php
if(
$_SERVER['REMOTE_ADDR']<>'172.16.0.10' and $_SERVER['REMOTE_ADDR']<>'192.168.12.51'
) {
echo 'Error: Запрещенный IP';
exit();
}

include("med/conn_string.cfg.php");
extract($_REQUEST);

if(isset($get_call_info_by_id)) {
/*
Оля, запрос возвращает перечисленные ниже поля.
Првая строка - названия полей, разделенные табуляциями (chr(9)), независимо от того, вернул запрос строку или нет.
Если данные найдены, то добавляется символ перевода строки (chr(13)) и данные, разделенные табуляциями (chr(9)).
Появление табуляций и переводов строки в данных исключено.
Кодировка Windows-1251

Запросы разрешены только с IP: 172.16.0.10 

2 примера запроса:
http://192.168.13.4/med/1c_api.php?get_call_info_by_id=193 
http://192.168.13.4/med/1c_api.php?get_call_info_by_id=129 

Поля таблицы будут такие:
ID заявки (number, not null) - идентификатор заявки, который сейчас передается через буфер обмена при записи пациента
SOURCE_TYPE_ID (number, not null) - идиентификатор типа источника (1 - Телефон, 2 - E-mail)
BNUMBER (varchar(200), null) - маршрутный номер или другой маршрутный идентификатор
SOURCE_AUTO_ID (number, not null) - идентификатор автоматически определяемого источника рекламы
SOURCE_AUTO_NAME (varchar(200), not null) - название автоматически определяемого источника рекламы
SOURCE_MAN_NAME (varchar(200), null) - название источника рекламы (вход), выбранного оператором вручную
SOURCE_MAN_DET (varchar(200), null) - детализация ручного (вход) источника рекламы (например, для источника рекламы "листовка у метро" будет название метро)
*/	
	$q=OCIParse($c,"select b.id, st.name source_type_name, b.bnumber, sa.id source_auto_id, sa.name source_auto_name, sm.name source_man_name, sd.name source_man_det
	from CALL_BASE b, SOURCE_AUTO sa, SOURCE_TYPE st, source_man sm, source_man_detail sd
	where sa.id(+)=b.source_auto_id and st.id(+)=sa.source_type and decode(sm.id(+),0,NULL,sm.id(+))=b.source_man_id
	and sd.id(+)=b.source_man_det_id
	and b.id='{$get_call_info_by_id}'");
	OCIExecute($q);
	for($i=1; $i<=oci_num_fields($q); $i++) {
		echo $i==1?NULL:chr(9);
		echo oci_field_name($q,$i);
	}
	if($row=oci_fetch_assoc($q)) {
		$i=1; foreach($row as $val) {
			echo $i==1?chr(13):chr(9);
			echo trim($val);
			$i++;
		}
	}
	$upd=OCIParse($c,"update call_base set get_callinfo_date_1C=sysdate where id='{$get_call_info_by_id}'");
	OCIExecute($upd);
	OCICommit($c);
	exit();
}
//ВЕРСИЯ 2
if(isset($get_call_info_by_id2)) {
/*
Оля, вторая версия интернфейса для получения информации по заявкам (get_call_info_by_id2).
Она содержит все возможные варианты истоников, которые у нас есть, когда с Викой выясним, что именно тебе нужно переливать в 1С, тогда нужно будет переливать по этому запросу.  

Описание:
Запрос возвращает перечисленные ниже поля.
Првая строка - названия полей, разделенные табуляциями (chr(9)), независимо от того, вернул запрос строку или нет.
Если данные найдены, то добавляется символ перевода строки (chr(13)) и данные, разделенные табуляциями (chr(9)).
Появление табуляций и переводов строки в данных исключено.
Кодировка Windows-1251

Запросы разрешены только с IP: 172.16.0.10 

2 примера запроса:
http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=59198 
http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869

необязательный параметр html, позволяет вывести результат в браузер ввиде HTML таблицы (для отладки)
например: http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869&html

Поля возвращаемой таблицы:
ID (number, not null) - id заявки (number, not null) - идентификатор заявки, который сейчас передается через буфер обмена при записи пациента
DATE_CALL (дата, время, DD.MM.YYYY HH24:MI:SS) - дата заявки
SOURCE_TYPE_NAME (number, not null) - идиентификатор типа источника (Телефон, E-mail)
BNUMBER (varchar(200), null) - маршрутный номер или другой маршрутный идентификатор
SOURCE_AUTO_ID (number, not null) - идентификатор автоматически определяемого источника рекламы
SOURCE_AUTO (varchar(200), not null) - автоматический источник рекламы
SOURCE_IN_MAN (varchar(200), null) - источник, выбранный входящим оператором вручную
SOURCE_IN_MAN_DET (varchar(200), null) - детализация ручного входящего истоника (дочерний, по отношению к source_in_man)
SOURCE_OUT_MAN (varchar(500), null) - источник рекламы, выбираемый исходящим оператором вручную (дочерний, по отношению к source_auto)
SOURCE_COMBO (varchar(500), not null) - комбинированный источник (vnl(source_out_man,source_auto)). (Если source_out_man пустой, то source_auto, иначе source_out_man)

*/	
	$q=OCIParse($c,"select b.id,to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call, st.name source_type_name, b.bnumber, 
	sa.id source_auto_id,
	sa.name source_auto, 
	sm.name source_in_man, 
	sd.name source_in_man_det,
	sad.name source_out_man, 
	nvl(sad.name,sa.name) source_combo
	from CALL_BASE b, SOURCE_AUTO sa, SOURCE_AUTO_DETAIL sad, SOURCE_TYPE st, source_man sm, source_man_detail sd
	where sa.id(+)=b.source_auto_id 
	and sad.id(+)=b.source_man_id_new
	and st.id(+)=sa.source_type and decode(sm.id(+),0,NULL,sm.id(+))=b.source_man_id
	and sd.id(+)=b.source_man_det_id
	and b.id='{$get_call_info_by_id2}'");
	OCIExecute($q);
	if(isset($html)) echo "<table border=1>";
	if(isset($html)) echo "<tr>";
	for($i=1; $i<=oci_num_fields($q); $i++) {
		if(isset($html)) echo "<td>";
		else echo $i==1?NULL:chr(9);
		
		echo oci_field_name($q,$i);
		if(isset($html)) echo "</td>";
	}
	if(isset($html)) echo "</tr>";
	if($row=oci_fetch_assoc($q)) {
		if(isset($html)) echo "<tr>";
		$i=1; foreach($row as $val) {
			if(isset($html)) echo "<td>";
			else echo $i==1?chr(13):chr(9);
			
			echo trim($val);
			$i++;
			if(isset($html)) echo "</td>";
		}
		if(isset($html)) echo "</tr>";
	}
	if(isset($html)) echo "</table>";
	$upd=OCIParse($c,"update call_base set get_callinfo_date_1C=sysdate where id='{$get_call_info_by_id2}'");
	OCIExecute($upd);
	OCICommit($c);
	exit();
}

if(isset($get_list_source_auto)) {
	/*
	get_list_source_auto
	Получение списка автоматических источников рекламы
	
	Описание:
	Запрос возвращает перечисленные ниже поля.
	Првая строка - названия полей, разделенные табуляциями (chr(9)), независимо от того, вернул запрос строку или нет.
	Если данные найдены, то добавляются строки, разделенные символом перевода строки (chr(13)) и данные, разделенные табуляциями (chr(9)).
	Появление табуляций и переводов строки в данных исключено.
	Кодировка Windows-1251
	
	Запросы разрешены только с IP: 172.16.0.10 
	
	пример запроса:
	http://192.168.13.4/med/1c_api.php?get_list_source_auto 
	http://192.168.13.4/med/1c_api.php?get_list_source_auto&html
	
	необязательный параметр html, позволяет вывести результат в браузер ввиде HTML таблицы (для отладки)
	например: http://192.168.13.4/med/1c_api.php?get_call_info_by_id2=58869&html
	
	Поля возвращаемой таблицы:
	ID (number, not null) - идентификатор автоматического источника рекламы
	NAME (varchar(200), not null) - название автоматического источника рекламы
	TYPE (phone, email) - тип источника рекламы
	*/		
	$q=OCIParse($c,"select t.id,t.name,decode(t.source_type,1,'phone',2,'email') type from SOURCE_AUTO t
	where deleted is null and t.source_type in (1,2)
	order by t.id");
	OCIExecute($q);
	if(isset($html)) echo "<table border=1>";
	if(isset($html)) echo "<tr>";
	for($i=1; $i<=oci_num_fields($q); $i++) {
		if(isset($html)) echo "<td>";
		else echo $i==1?NULL:chr(9);
		
		echo oci_field_name($q,$i);
		if(isset($html)) echo "</td>";
	}
	if(isset($html)) echo "</tr>";
	while($row=oci_fetch_assoc($q)) {
		if(isset($html)) echo "<tr>";
		$i=1; foreach($row as $val) {
			if(isset($html)) echo "<td>";
			else echo $i==1?chr(13):chr(9);
			
			echo trim($val);
			$i++;
			if(isset($html)) echo "</td>";
		}
		if(isset($html)) echo "</tr>";
	}
	if(isset($html)) echo "</table>";	
}

if(isset($set_visit_status)) {
/*
Оля, готова ссылка для обновления статуса посещения:

с непустой датой посещения
http://192.168.13.4/med/1c_api.php?set_visit_status&ticket_id=633&visit_date=20180101000000
или с пустой датой посещения
http://192.168.13.4/med/1c_api.php?set_visit_status&ticket_id=633&visit_date=
*/	
	if(!isset($ticket_id) or ($ticket_id)=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	if (!isset($visit_date)) $visit_date='';
	
	if($visit_date<>'') {
		$q=OCIParse($c,"select count(*) cnt from visit_hist where base_id='{$ticket_id}' and date_visit=to_date('{$visit_date}','YYYYMMDDHH24MISS')");
		OCIExecute($q);
		OCIFetch($q);
		if(OCIResult($q,"CNT")>0) {
			echo "Ошибка: повторный запрос";
			exit();
		}
	}
	
	$upd=OCIParse($c,"update call_base set check_visit_date_1c=sysdate,
	visit_date_1c=to_date('{$visit_date}','YYYYMMDDHH24MISS') where id='{$ticket_id}'");
	if(OCIExecute($upd)) {
		if($visit_date<>'' and oci_num_rows($upd)>0) {
			$ins=OCIParse($c,"insert into visit_hist (base_id,date_add,date_visit) values ('{$ticket_id}',sysdate,to_date('{$visit_date}','YYYYMMDDHH24MISS'))");
			OCIExecute($ins);
			OCICommit($c);
		}
		echo "OK:".oci_num_rows($upd);
	}
}

if(isset($set_entry_date)) {
/*Обновление даты, на которую пациент записан в клинику
Примеры:
Обновление даты записи:
http://192.168.13.4/med/1c_api.php?set_entry_date&ticket_id=60336&entry_date=20181231235959
Удаление даты записи:
http://192.168.13.4/med/1c_api.php?set_entry_date&ticket_id=60336&entry_date=
*/
	if(!isset($ticket_id) or ($ticket_id)=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	if (!isset($entry_date)) $entry_date='';

    if($entry_date<>'') { //добавление даты записи, только если даты записи еще нет, если дата записи уже есть, то она останется не тронутой - nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS'))
		$upd=OCIParse($c,"update call_base set check_entry_date_1c=sysdate, 
		entry_date_1c=nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS')) where id='{$ticket_id}'");
		if(!OCIExecute($upd,OCI_DEFAULT)) exit();
		
		$upd2=OCIParse($c,"update CALL_BASE_CLINIC set check_entry_date_1c=sysdate, 
		entry_date_1c=nvl(entry_date_1c,to_date('{$entry_date}','YYYYMMDDHH24MISS')) where id='{$ticket_id}'");
		if(!OCIExecute($upd2,OCI_DEFAULT)) exit();
		
		//добавлине даты записи в историю
		$ins=OCIParse($c,"insert into write_hist (base_id,date_add,date_write) values ('{$ticket_id}',sysdate,to_date('{$entry_date}','YYYYMMDDHH24MISS'))");
		if(OCIExecute($ins,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($ins);
			OCICommit($c);
		}
	}
	else {//удаление даты записи
		$upd=OCIParse($c,"update call_base set check_entry_date_1c=sysdate, 
		entry_date_1c=NULL where id='{$ticket_id}'");
		if(!OCIExecute($upd,OCI_DEFAULT)) exit();
		
		$upd2=OCIParse($c,"update CALL_BASE_CLINIC set check_entry_date_1c=sysdate, 
		entry_date_1c=NULL where id='{$ticket_id}'");
		if(!OCIExecute($upd2,OCI_DEFAULT)) exit();
		
		//очистка истории
		/*$del=OCIParse($c,"delete from write_hist where base_id='{$ticket_id}'");
		if(OCIExecute($del,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($del);
			OCICommit($c);
		}*/
		//добавлине даты записи в историю
		$ins=OCIParse($c,"insert into write_hist (base_id,date_add,date_write) values ('{$ticket_id}',sysdate,NULL)");
		if(OCIExecute($ins,OCI_DEFAULT)) {
			echo "OK:".oci_num_rows($ins);
			OCICommit($c);
		}		
	}
}	

//добавление информации о платеже
/*
Интерфейс для добавления информации о платеже.
Принимаемые параметры:
1) добавления платежа:
add_pay - директива на добавление платежа (не имеет значения)
ticket_id - ID заявки - непустое - целое положительное число
pay_date - Дата платежа - непустое - дата YYYYMMDDHH24MISS
rub - сумма платежа в рублях, без копеек - непустое - целое число (отрицательное - возврат платежа)
В случае успешного добавления функция возвращает "ОК:1", где 1 - количество добавленных в запросе строк
пример http://192.168.13.4/med/1c_api.php?add_pay&ticket_id=2676&pay_date=20180101213545&rub=102
OK:1

2) удаление платежа
del_pay - директива на удаление платежа (не имеет значения)
ticket_id - ID заявки - непустое - целое положительное число
pay_date - Дата платежа - непустое - дата YYYYMMDDHH24MISS, если данный параметр отсутствует, то удаляются все платежи, соотвествующие остальным параметрам
rub - сумма платежа в рублях, без копеек - непустое - целое число (отрицательное - возврат платежа), если данный параметр отсутствует, то удаляются все платежи, соотвествующие остальным параметрам
В случае успешного удаления функция возвращает "ОК:1", где 1 - количество удаленных в запросе строк
пример: http://192.168.13.4/med/1c_api.php?del_pay&ticket_id=2676&pay_date=20180101213545&rub=102
OK:1

проверка суммы платежей
get_pay_sum - директива
ticket_id - ID заявки - непустое - целое положительное цисло
пример: http://192.168.13.4/med/1c_api.php?get_pay_sum&ticket_id=2676
OK:201

В случае возникновения ошибок возвращаются оракловые ошибки:
-- Сумма платежа или ID заявки не является целым числом: Warning: ociexecute(): ORA-01722: invalid number in C:\Apache24\htdocs\med\1c_api.php on line 92
-- Неверная дата платежа: Warning: ociexecute(): ORA-01830: date format picture ends before converting entire input string in C:\Apache24\htdocs\med\1c_api.php on line 92
-- Заявка с таким ID не существует: Warning: ociexecute(): ORA-01830: date format picture ends before converting entire input string in C:\Apache24\htdocs\med\1c_api.php on line 92
-- В БД уже есть запись о данном платеже (совпадают ticket_id,pay_date,rub)
-- Прочие оракловые ошибки
*/

if(isset($add_pay)) {
	if(!isset($pay_date) or $pay_date=='') {
		echo "Ошибка: Отсутствует дата платежа";
		exit();		
	}
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	$ins=OCIParse($c,"insert into payment_hist (base_id,date_add,date_payment,rub)
	values ('{$ticket_id}',sysdate,to_date('{$pay_date}','YYYYMMDDHH24MISS'),'{$rub}')");
	if(OCIExecute($ins)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($ins);		
	}
}
if(isset($del_pay)) {
	/*
	if(!isset($pay_date) or $pay_date=='') {
		echo "Ошибка: Отсутствует дата платежа";
		exit();		
	}
	*/
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
    $deletestr = "delete from payment_hist where base_id='{$ticket_id}' ";
	if (isset($pay_date)) $deletestr .= " and date_payment=to_date('{$pay_date}','YYYYMMDDHH24MISS')";
	if (isset($rub)) $deletestr .= " and rub='{$rub}'";
	$del=OCIParse($c,$deletestr);
	if(OCIExecute($del)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($del);		
	}
}
if(isset($get_pay_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	$q=OCIParse($c,"select nvl(sum(rub),0) sum from PAYMENT_HIST t where base_id='{$ticket_id}'");
	OCIExecute($q);
	if(OCIFetch($q)) {
		echo "OK:".OCIResult($q,"SUM");		
	}
}

//добавление информации о плане лечения
/*
Интерфейс для добавления информации о плане лечения.
Принимаемые параметры:

1) добавление итоговой суммы плана лечения
ch_plan_sum - директива на корректировку итоговой суммы плана лечения (добавляет корректирующую сумму плана лечения, исходя из разницы текущей суммы лечения и итоговой)
ticket_id - ID заявки - непустое - целое положительное число
plan_date - Дата плана лечения - непустое - дата YYYYMMDDHH24MISS
plan_num - номер плана лечения - непустое, строка(100)
rub - итоговая сумма плана лечения в рублях, без копеек - непустое - целое число (положительное или отрицательное)
В случае успешного добавления функция возвращает "ОК:1", где 1 - количество добавленных в запросе строк
пример http://192.168.13.4/med/1c_api.php?ch_plan_sum&ticket_id=2676&plan_num=A12345678&plan_date=20180101213545&rub=102
Если разница между текущей суммой лечения и итоговой отличается от нуля, то корректирующая сумма добавится, и в ответе вернется количество добавленных строк:
OK:1
Если разница равна нулю, то количество добавленных строк будет равно нулю, т.к. корректировка не добавится.
OK:0

2) добавление / корректировка плана лечения:
add_plan_hist - директива на добавление плана или корректирующей суммы плана (не имеет значения)
ticket_id - ID заявки - непустое - целое положительное число
plan_date - Дата плана лечения - непустое - дата YYYYMMDDHH24MISS
plan_num - номер плана лечения - непустое, строка(100)
rub - корректировка суммы плана лечения в рублях, без копеек - непустое - целое число (положительное или отрицательное)
В случае успешного добавления функция возвращает "ОК:1", где 1 - количество добавленных в запросе строк
пример http://192.168.13.4/med/1c_api.php?add_plan_delta&ticket_id=2676&plan_num=A12345678&plan_date=20180101213545&rub=102
OK:1

3) удаление плана лечения
del_pay - директива на удаление плана лечения (не имеет значения)
ticket_id - ID заявки - непустое - целое положительное число
plan_num - номер плана лечения - непустое, строка(100), если данный параметр отсутствует, то удаляются записи, соотвествующие остальным параметрам
plan_date - Дата плана лечения - непустое - дата YYYYMMDDHH24MISS, если данный параметр отсутствует, то удаляются записи, соотвествующие остальным параметрам
rub - сумма корректировки плана - непустое - целое число (положительное или отрицательное), если данный параметр отсутствует, то удаляются все строки, соотвествующие остальным параметрам
В случае успешного удаления функция возвращает "ОК:1", где 1 - количество удаленных в запросе строк
пример: http://192.168.13.4/med/1c_api.php?del_plan&ticket_id=2676&plan_num=A12345678
OK:1

4) проверка суммы плана лечения. возвращает текущую сумму плана в рублях
get_plan_sum - директива
ticket_id - ID заявки - непустое - целое положительное цисло
plan_num - номер плана лечения - строка(100), если данный параметр отсутствует, то выводится сумма всех планов по данному ticket_id
пример: http://192.168.13.4/med/1c_api.php?get_plan_sum&ticket_id=2676&plan_num=A12345678
OK:201
*/

if(isset($ch_plan_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	if(!isset($plan_num) or $plan_num=='') {
		echo "Ошибка: Отсутствует номер плана лечения";
		exit();		
	}
	if(!isset($plan_date) or $plan_date=='') {
		echo "Ошибка: Отсутствует дата плана лечения";
		exit();		
	}
	if(!isset($rub) or !is_numeric($rub)) {
		echo "Ошибка: Отсутствует сумма корректировки";
		exit();		
	}	
	$ins=OCIParse($c,"insert into plan_hist (base_id,date_add,plan_num,plan_date,rub,check_sum)
	values ('{$ticket_id}',sysdate,'{$plan_num}',to_date('{$plan_date}','YYYYMMDDHH24MISS'),
		'{$rub}'-nvl((select sum(rub) from plan_hist where base_id='{$ticket_id}' and plan_num='{$plan_num}'),0),
		'{$rub}'
	) returning rub into :rub_delta");
	OCIBindByName($ins,":rub_delta",$rub_delta,16);
	if(OCIExecute($ins, OCI_DEFAULT)) {
		if($rub_delta<>0) {
			OCICommit($c);
			echo "OK:".oci_num_rows($ins);
		}
		else {
			OCIRollBack($c);
			echo "OK:0";
		}
	}
}

if(isset($add_plan_delta)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	if(!isset($plan_num) or $plan_num=='') {
		echo "Ошибка: Отсутствует номер плана лечения";
		exit();		
	}
	if(!isset($plan_date) or $plan_date=='') {
		echo "Ошибка: Отсутствует дата плана лечения";
		exit();		
	}
	if(!isset($rub) or !is_numeric($rub)) {
		echo "Ошибка: Отсутствует сумма корректировки";
		exit();		
	}	
	$ins=OCIParse($c,"insert into plan_hist (base_id,date_add,plan_num,plan_date,rub)
	values ('{$ticket_id}',sysdate,'{$plan_num}',to_date('{$plan_date}','YYYYMMDDHH24MISS'),'{$rub}')");
	if(OCIExecute($ins)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($ins);		
	}
}
if(isset($del_plan)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
    $deletestr = "delete from plan_hist where base_id='{$ticket_id}'";
	if (isset($plan_num)) $deletestr .= " and plan_num='{$plan_num}'";
	if (isset($plan_date)) $deletestr .= " and plan_date=to_date('{$plan_date}','YYYYMMDDHH24MISS')";
	if (isset($rub)) $deletestr .= " and rub='{$rub}'";
	$del=OCIParse($c,$deletestr);
	if(OCIExecute($del)) {
		OCICommit($c);
		echo "OK:".oci_num_rows($del);		
	}
}
if(isset($get_plan_sum)) {
	if(!isset($ticket_id) or $ticket_id=='') {
		echo "Ошибка: Отсуствует ticket_id";
		exit();
	}
	$sql="select nvl(sum(rub),0) sum from plan_hist t where base_id='{$ticket_id}'";
	if (isset($plan_num)) $sql .= " and plan_num='{$plan_num}'";
	$q=OCIParse($c,$sql);
	OCIExecute($q);
	if(OCIFetch($q)) {
		echo "OK:".OCIResult($q,"SUM");		
	}
}
?>