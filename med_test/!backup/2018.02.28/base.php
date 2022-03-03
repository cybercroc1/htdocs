<?php
DEFINE('DB_OCI', TRUE);
//DEFINE('CONVERT_TO_1251', TRUE);

if (DB_OCI) {
    DEFINE('DB_HOST', '');
    DEFINE('DB_USER', 'MED');
    DEFINE('DB_PWD', 'zubogriz');
    DEFINE('DB_DB', 'SC');
    DEFINE('PATH', '');
    DEFINE('ENCODE_UTF', FALSE);
}
else {
    DEFINE('DB_HOST', 'localhost');
    DEFINE('DB_USER', 'root');
    DEFINE('DB_PWD', '');
    DEFINE('DB_DB', 'medicine');
    DEFINE('PATH', 'med');
    DEFINE('ENCODE_UTF', FALSE);
}

// Роли пользователей
DEFINE("USER_ADMIN", 1);
DEFINE("USER_SUPER", 2);
DEFINE("USER_VIEW", 3);
DEFINE("USER_USER", 4);

// Результат входящего звонка
DEFINE("RESULT_KC", 1);
DEFINE("RESULT_CLINIC", 2);
DEFINE("RESULT_WAIT", 3);
//DEFINE("RESULT_NOT_ANSWER", 3);
define("CALL_RES", array('', 'в КЦ', 'в Клинику', 'Ждет звонка'));

// Статусы исходящих звонков
DEFINE("STATUS_OPEN", 1);
DEFINE("STATUS_WORK", 2);
DEFINE("STATUS_CALL_BACK", 3);
DEFINE("STATUS_CALL_NOT", 4);
DEFINE("STATUS_CALL_STOP", 5);
DEFINE("STATUS_CLINIC", 6);
DEFINE("STATUS_NEGATIVE", 7);
DEFINE("STATUS_ERROR", 8);
DEFINE("STATUS_CLOSED", 9);

// Источники рекламы
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
define("CITIES", array('', 'Москва', 'Питер', 'НН', 'Сочи'));

//Перевод номера в КЦ
DEFINE("TRANS_CONST", 14);
DEFINE("TRANS_NUM", 5);
