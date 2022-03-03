<?php
DEFINE('DB_OCI', TRUE);
DEFINE("SEND_EMAIL", FALSE);
DEFINE("DEBUG_MODE", FALSE);
//DEFINE('CONVERT_TO_1251', TRUE);
DEFINE("IT_PLANET", 135);
DEFINE("APPLELOVERS", 290);
DEFINE("EXPORT_CUT", array(106,107,148,163,178,183,201,229,374));  // Медиагуру, Гусаров, Savitsky, CfB, Zizor, DMS, ДельтаСтрим, Эпплаверс
DEFINE("OUTER_ORDER", array(1,61,69,98,99,100)); // колонка внешних идентификаторов
DEFINE("CALL_STATISTIC", array(1,61,69,98,99,100,10,55,54,131)); // колонка внешних идентификаторов
DEFINE("SHOW_ITOG", array(1,4,61,69,290)); // показать итог пришел/не пришел
DEFINE("SOK_SUPER", array(10,54,55,4)); //супервайзеры Соколовой

DEFINE("SPEC_USER", 117); // 61);//
DEFINE("SPEC_USER_VIEW", array(110,111,112)); // внутренние наблюдатели
DEFINE("SPEC_USER_EDIT", array(84,86,125,291,303)); // изменение статуса почти без следов
DEFINE("SEC_CHANCE_CALL", 21); // Департамент стоматологии второго шанса
DEFINE("SPEC_USER_CALL", array(126,127,128,129,156,157,193,194,197,204,207,241,245,263,264,265)); // перехват звонков стоматологии у косметологов
DEFINE("COST_EDIT", array(1,4,61,69,115,150)); // редактирование поставщиков и стоимости посещений
DEFINE("WRITE_ONLY", array(163)); // в экспорте только Запись в клинику

if (DB_OCI) {
    DEFINE('DB_HOST', '');
    DEFINE('DB_USER', 'MED');
    DEFINE('DB_PWD', 'zubogriz');
    DEFINE('DB_DB', 'WEBMED');
    DEFINE('PATH', '');
    DEFINE('ENCODE_UTF', FALSE);
}
else {
    DEFINE('DB_HOST', 'localhost');
    DEFINE('DB_USER', 'root');
    DEFINE('DB_PWD', '');
    DEFINE('DB_DB', 'medicine');
    DEFINE('PATH', '');
    DEFINE('ENCODE_UTF', TRUE);
}

DEFINE("needs", '#FF733C'); // цвет невыбранного селекта

// Роли пользователей
DEFINE("USER_ADMIN", 1);
DEFINE("USER_SUPER", 2);
DEFINE("USER_VIEW", 3);
DEFINE("USER_USER", 4);

// Возможности пользователей
DEFINE("CAN_PHONE", 1);
DEFINE("CAN_FINANCE", 2);
//DEFINE("CAN_SOME", 3);
DEFINE("CAN_HEAR", 4);
//sva:
//Эти данные перенесены в сессионный массив $_SESSION['access']['data_acc']. 
//Проверку на доступ к данным можно делать так if(isset($_SESSION['access']['data_acc']['CAN_PHONE']))

// Тип звонка
DEFINE("CALL_FIRST", 1); // Первичный
DEFINE("CALL_SECOND", 2); // Повторный

// Направление звонка
DEFINE("CALL_IN", 'in'); // Входящий
DEFINE("CALL_OUT", 'out'); // Исходящий
DEFINE("CALL_BACK", 'callback'); // Автоперезвон
DEFINE("CALL_WAY", array(''=>' ','in'=>'Входящий', 'out'=>'Исходящий', 'callback'=>'Автоперезвон'));

//Темы звонка
DEFINE("THEME_NOT", 0); // Ничего не выбрано
DEFINE("THEME_MED", 1); // Медицинские услуги
DEFINE("THEME_INFO",2); // Информация для руководства
DEFINE("THEME_AUD", 3); // Собеседование в клинику
DEFINE("THEME_ANA", 4); // Анализы
DEFINE("THEME_TRUD",5); // Трудоустройство промоутер
DEFINE("THEME_OUT", 6); // Невыход на работу
DEFINE("THEME_OTHER", 7); // Прочие звонки

// Медицинские услуги
DEFINE("SERVICE_ALL", -1); // Все
DEFINE("SERVICE_NOT",  0); // Ничего не выбрано
DEFINE("SERVICE_STOM", 1); // Стоматология
DEFINE("SERVICE_KOSM", 2); // Косметология
DEFINE("SERVICE_GINE", 3); // Гинекология
DEFINE("SERVICE_PLAS", 4); // Пластика
DEFINE("SERVICE_TRIH", 5); // Трихология
DEFINE("SERVICE_MICH", 6); // Мишлен
DEFINE("SERVICE_LIST", array('Ничего не выбрано', 'Стоматология', 'Косметология', 'Гинекология', 'Пластика', 'Трихология', 'Мишлен'));

// Стоматология
DEFINE("STOM_NOT",    0); // Ничего не выбрано
DEFINE("STOM_VINIR", 100); // Виниры
DEFINE("STOM_CHILD_ORT", 101); // Детская ортодонтия
DEFINE("STOM_CHILD", 102); // Детская стоматология
DEFINE("STOM_IMPLANT", 103); // Имплантация
DEFINE("STOM_LASER", 104); // Лазерная стоматология
DEFINE("STOM_TREAT", 105); // Лечение зубов
DEFINE("STOM_ORTODONT", 106); // Ортодонтия
DEFINE("STOM_ORTOPED", 107); // Ортопедия
DEFINE("STOM_WHITE", 108); // Отбеливание
DEFINE("STOM_PARODONT", 109); // Пародонтология
DEFINE("STOM_PROTEZ", 110); // Протезирование
DEFINE("STOM_HYGIEN", 111); // Профессиональная гигиена
DEFINE("STOM_RESTORE", 112); // Реставрация зубов
DEFINE("STOM_SURGERY", 113); // Хирургия
//DEFINE("STOM_HOUR",   3); // Протезирование за час
//DEFINE("STOM_NEYLON", 2); // Нейлоновый протез
DEFINE("STOM_OTHER", 190); // Другое
//DEFINE("STOM_DET", array('', 'Детская стоматология', 'Нейлоновый протез', 'Протезирование за час'));

// Результат входящего звонка
DEFINE("RESULT_NOT",    0); // Ничего не выбрано
DEFINE("RESULT_KC",     1); // Переведен в КЦ
DEFINE("RESULT_CLINIC", 2); // + STATUS_CLINIC_CALL
DEFINE("RESULT_WAIT",   3); // Ожидает звонка
DEFINE("RESULT_AON",    4); // Не оставил номер
DEFINE("RESULT_KC_SELF",5); // Переведен в КЦ на себя
DEFINE("CALL_RES", array('', 'в КЦ', 'в Клинику', 'Ждет звонка', 'АОН', 'в КЦ (себе)'));
//DEFINE("CALL_RES_LONG", array('', 'Перевели в КЦ', 'Перевели в Клинику', 'Ожидает звонка'));

DEFINE("DEVICE_PHONE", 1); // Телефон
DEFINE("DEVICE_MAIL",  2); // e-mail
DEFINE("DEVICES", array('', 'Телефон', 'E-mail'));

// Статусы исходящих звонков
DEFINE("STATUS_NOT", 0); // Ничего не выбрано
DEFINE("STATUS_OPEN", 1); // Новый звонок
DEFINE("STATUS_WORK", 2); // Назначен оператору
DEFINE("STATUS_CALL_BACK", 3); // бывшее На перезвон, теперь "Проведена консультация"
DEFINE("STATUS_CALL_NOT", 4); // Недозвон+Перезвон
DEFINE("STATUS_CALL_STOP", 5); // Глобальный недозвон
DEFINE("STATUS_CLINIC", 6); // Запись в клинику
DEFINE("STATUS_CL_CANCEL", 7); // Отмена записи
DEFINE("STATUS_ERROR", 8); // Ошибка
DEFINE("STATUS_REPEAT", 9); // Повторный
DEFINE("STATUS_CLINIC_NOT", 10); // Отказ от записи в клинику
DEFINE("STATUS_BREAK_LINE", 11); // Обрыв связи

DEFINE("STATUS_NOT_COME", 20); // Непришедший пациент

// при добавлении нового статуса необходимо проверить использование в коде последнего из имеющегося списка, кроме нижних
DEFINE("STATUS_CLINIC_CALL", 60); // Звонок переведен в клинику (RESULT_CLINIC)
DEFINE("STATUS_CLOSED", 99); // Статус при создании новой записи при открытии окна входящего звонка
DEFINE("STATUS_NEGATIVE", 77); // Отказ/негатив

// Детализация ошибок
DEFINE("STAT_ERR_OTHER", 800); // Другое
DEFINE("STAT_ERR_APPL", 801); // Не оставлял заявку
DEFINE("STAT_ERR_CONTR", 802); // Противопоказания
DEFINE("STAT_ERR_NUM", 803); // Брак в номере
DEFINE("STAT_ERR_NAME", 804); // Брак в имени
DEFINE("STAT_ERR_SERV", 805); // Нет данной услуги
DEFINE("STAT_ERR_DAYS", 806); // Недозвон 2 дня
DEFINE("STAT_ERR_INTER", 807); // Межгород

// Источники рекламы
DEFINE("SOURCE_ALL", -1); // Все
DEFINE("SOURCE_NOT", 0); //Ничего не выбрано
//// С дополнительным списком
////// Станция метро
DEFINE("SOURCE_FLAER", 1); //Листовка в почтовый ящик
DEFINE("SOURCE_CATALOG", 2); //Мини-каталог
DEFINE("SOURCE_FLAER_SUB", 5); //Листовка у метро
DEFINE("SOURCE_FLAER_CAR", 6); //Листовка под дворник/ручку авто
DEFINE("SOURCE_LIFT", 7); //Объявление в подъезде/в лифте
DEFINE("SOURCE_STOP", 8); //Остановки
////// Другое
DEFINE("SOURCE_INTERNET", 3); //Сайт
DEFINE("SOURCE_TV", 4); //ТВ
DEFINE("SOURCE_COUPON", 9); //Купон {купи-бонус, купи-купон, выгода}
DEFINE("SOURCE_RAIL", 10); //ЖД станции
DEFINE("SOURCE_SERT", 11); //Подарочный сертификат
DEFINE("SOURCE_BANNER_SUB", 45); //Подарочный сертификат

// перенесено в раздел детализации Интернета - SOURCE_INTERNET
//DEFINE("SOURCE_FACEBOOK", ); //Facebook
//DEFINE("SOURCE_INSTAGRAM", ); //Instagram
//DEFINE("SOURCE_VK", ); //Vkontakte
//DEFINE("SOURCE_ZOON", ); //Zoon.ru

// без уточнения
DEFINE("SOURCE_2GIS", 20); //2Gis
DEFINE("SOURCE_SMS", 21); //sms рассылка
DEFINE("SOURCE_PAPER", 22); //Газета в почтовом ящике
DEFINE("SOURCE_PAPER_CAR", 23); //Газета в пробке
DEFINE("SOURCE_NEAR", 24); //Живёт рядом
DEFINE("SOURCE_CALENDAR", 25); //Календарь
DEFINE("SOURCE_FLAER_JAM", 26); //Листовка в пробке
DEFINE("SOURCE_STICKER_SUB", 27); //Наклейка в метро
DEFINE("SOURCE_GIFT", 28); //Подарочная карта
DEFINE("SOURCE_RADIO", 29); //Радио
DEFINE("SOURCE_WAS", 30); //Ранее лечился в клинике
DEFINE("SOURCE_ADV_BUS", 31); //Реклама в маршрутке
DEFINE("SOURCE_ADV_BOARD", 32); //Рекламный щит
DEFINE("SOURCE_RECOMMEND", 33); //Рекомендации

// с добавочным текстом
DEFINE("SOURCE_OTHER", 38); //Другое
DEFINE("SOURCE_AMNESY", 39); //Не помнит
DEFINE("SOURCE_ANY", 40); //Везде

DEFINE("DETAILS_PROMO", 999); //На улице у промоутера
DEFINE("DETAILS_OTHER", 1000); //Другое
DEFINE("DETAILS_AMNESY", 1001); //Не помнит

// Города присутствия
DEFINE("CITY_MOSCOW", 1);
DEFINE("CITY_PITER", 2);
DEFINE("CITY_NN", 3);
DEFINE("CITY_SOCHI", 4);
DEFINE("CITIES", array('', 'Москва', 'Питер', 'НН', 'Сочи'));

//Перевод номера в КЦ
//DEFINE("TRANS_CONST", 14);
DEFINE("TRANS_NUM", 5);
DEFINE("WRITE_CLINIC_NUM", 2);

// Экспорт данных (отчеты)
DEFINE("EXPORT_CALL", 1);
DEFINE("EXPORT_OPERATOR", 2);
DEFINE("EXPORT_EFFECT", 3);
DEFINE("EXPORT_EFFECT_ISH", 4);
DEFINE("EXPORT_OPERATOR_ALL", 5);
DEFINE("EXPORT_EFFECT_IDYN", 6);
DEFINE("EXPORT_BILLING", 7);
DEFINE("EXPORT_OPERATOR_SEC", 8);
DEFINE("EXPORT_CALL_SHORT", 9);
DEFINE("EXPORT_CALL_SEC", 10);
DEFINE("EXPORT_LEADS", 11);
DEFINE("EXPORT_OPERATOR_SEC_CALL", 12);
DEFINE("EXPORT_VISITS", 13);
