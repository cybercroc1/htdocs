<?php 
session_name('medc');
session_start();
require_once '../funct.php';
if (!isset($_SESSION['user_role']) or $_SESSION['user_role'] != USER_ADMIN) {
    echo '<p style="font-size: 26px; font-weight: bold; color: red;">Cтраница недоступна!</p>'; exit();
}

include("med/conn_string.cfg.php");	

//построение запрса: запрос должен иметь адекватные названия полей на выходе, эти названия будут отразены в шапке таблицы
$sql_text="
select 
r.id as ID,
s.id as ID_IST, s.name source_name, 
r.ord, r.coment, 
sr.name service_name, 
a.name action_name,
to_char(r.create_date,'DD.MM.YYYY') create_date, 
to_char(r.change_date,'DD.MM.YYYY') change_date,
to_char(r.use_date,'DD.MM.YYYY') use_date,
r.preg_match_from,
r.preg_match_subj,
r.preg_match_body
from MAIL_REGEXR r
left join source_auto s on s.id=r.source_auto_id
left join mail_regexr_actions a on a.id=r.action
left join services sr on sr.id=r.service_id
where 1=1
	/*filters*/
	/*orders*/
";

//Настрока полей
//Подмассив name - имя столбца, отображаемое на странице
//Подмассив case - варажение для поля, по которому работает where и order
$fields=array(
	"ID"=>array("name"=>"ID Правила","case"=>"r.id"),
	"ID_IST"=>array("name"=>"ID Источника","case"=>"s.id"),
	"SOURCE_NAME"=>array("name"=>"Источник авто","case"=>"s.name"),
	"COMENT"=>array("name"=>"Комментарий"),
	"SERVICE_NAME"=>array("name"=>"Услуга","case"=>"sr.name"),
	"ACTION_NAME"=>array("name"=>"Действие","case"=>"a.name"),
	"ORD"=>array("name"=>"Приоритет"),
	"CREATE_DATE"=>array("name"=>"Создано","case"=>"r.create_date"),
	"CHANGE_DATE"=>array("name"=>"Изменено","case"=>"r.change_date"),
	"USE_DATE"=>array("name"=>"Использовано","case"=>"r.use_date"),
);

//Включение фильтров: массыв с номерами полей таблицы, начиная с 1. Что бы данные фильтры рботали, должно работать условие where по названию полей запроса
//Место в запросе, куда нужно вставить фитьтр (начиная с and) должно быть обозначено комментарием "/*filters*/"
//если фильтру назначен подмассив со значениями, это массив устанавливает значения фильтра по умолчанию
$filters=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"",
	"SERVICE_NAME"=>"",
	"ACTION_NAME"=>"",
	"ORD"=>""
);

//включение возможности сортировки пустое значение включает возможность сортировки. 
//Для задания парамнтров сортировки по умолчанию можно использовать up,asc - по возрастанию; down,desc - по убыванию
$orders=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"up",
	"COMENT"=>"",
	"SERVICE_NAME"=>"",
	"ACTION_NAME"=>"",
	"ORD"=>"",
	"CREATE_DATE"=>"up",
	"CHANGE_DATE"=>"",
	"USE_DATE"=>""	
);
//Включение поиска по полям:
$finds=array(
	"ID"=>"",
	"ID_IST"=>"",
	"SOURCE_NAME"=>"",
	"COMENT"=>"",
	"SERVICE_NAME"=>"",
	"PREG_MATCH_FROM"=>"",
	"PREG_MATCH_SUBJ"=>"",
	"PREG_MATCH_BODY"=>""
);
//настройки для редактируемых таблиц
$edit_id_name='ID'; //название поля запроса, содержащего ID
$edit_frame='parent.fr_rule_edit'; //Название фрейма, в котором откроется страница для редактирования
$edit_url='rule_edit.php?regexr_id='; //url страницы редактирования

include('lists_include_ora.php');
?>

