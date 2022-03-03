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

// ���� �������������
DEFINE("USER_ADMIN", 1);
DEFINE("USER_SUPER", 2);
DEFINE("USER_VIEW", 3);
DEFINE("USER_USER", 4);

// ��������� ��������� ������
DEFINE("RESULT_KC", 1);
DEFINE("RESULT_CLINIC", 2);
DEFINE("RESULT_WAIT", 3);
//DEFINE("RESULT_NOT_ANSWER", 3);
define("CALL_RES", array('', '� ��', '� �������', '���� ������'));

// ������� ��������� �������
DEFINE("STATUS_OPEN", 1);
DEFINE("STATUS_WORK", 2);
DEFINE("STATUS_CALL_BACK", 3);
DEFINE("STATUS_CALL_NOT", 4);
DEFINE("STATUS_CALL_STOP", 5);
DEFINE("STATUS_CLINIC", 6);
DEFINE("STATUS_NEGATIVE", 7);
DEFINE("STATUS_ERROR", 8);
DEFINE("STATUS_CLOSED", 9);

// ��������� �������
//// � �������������� �������
////// ������� �����
DEFINE("SOURCE_FLAER", 1); //�������� � �������� ����
DEFINE("SOURCE_CATALOG", 2); //����-�������
DEFINE("SOURCE_FLAER_SUB", 5); //�������� � �����
DEFINE("SOURCE_FLAER_CAR", 6); //�������� ��� �������/����� ����
DEFINE("SOURCE_LIFT", 7); //���������� � ��������/� �����
DEFINE("SOURCE_STOP", 8); //���������
////// ������
DEFINE("SOURCE_INTERNET", 3); //����
DEFINE("SOURCE_TV", 4); //��
DEFINE("SOURCE_COUPON", 9); //����� {����-�����, ����-�����, ������}
DEFINE("SOURCE_RAIL", 10); //�� �������
DEFINE("SOURCE_SERT", 11); //���������� ����������

// ���������� � ������ ����������� ��������� - SOURCE_INTERNET
//DEFINE("SOURCE_FACEBOOK", ); //Facebook
//DEFINE("SOURCE_INSTAGRAM", ); //Instagram
//DEFINE("SOURCE_VK", ); //Vkontakte
//DEFINE("SOURCE_ZOON", ); //Zoon.ru

// ��� ���������
DEFINE("SOURCE_2GIS", 20); //2Gis
DEFINE("SOURCE_SMS", 21); //sms ��������
DEFINE("SOURCE_PAPER", 22); //������ � �������� �����
DEFINE("SOURCE_PAPER_CAR", 23); //������ � ������
DEFINE("SOURCE_NEAR", 24); //���� �����
DEFINE("SOURCE_CALENDAR", 25); //���������
DEFINE("SOURCE_FLAER_JAM", 26); //�������� � ������
DEFINE("SOURCE_STICKER_SUB", 27); //�������� � �����
DEFINE("SOURCE_GIFT", 28); //���������� �����
DEFINE("SOURCE_RADIO", 29); //�����
DEFINE("SOURCE_WAS", 30); //����� ������� � �������
DEFINE("SOURCE_ADV_BUS", 31); //������� � ���������
DEFINE("SOURCE_ADV_BOARD", 32); //��������� ���
DEFINE("SOURCE_RECOMMEND", 33); //������������

// � ���������� �������
DEFINE("SOURCE_OTHER", 38); //������
DEFINE("SOURCE_AMNESY", 39); //�� ������
DEFINE("SOURCE_ANY", 40); //�����

DEFINE("DETAILS_PROMO", 999); //�� ����� � ����������
DEFINE("DETAILS_OTHER", 1000); //������
DEFINE("DETAILS_AMNESY", 1001); //�� ������

// ������ �����������
DEFINE("CITY_MOSCOW", 1);
DEFINE("CITY_PITER", 2);
DEFINE("CITY_NN", 3);
DEFINE("CITY_SOCHI", 4);
define("CITIES", array('', '������', '�����', '��', '����'));

//������� ������ � ��
DEFINE("TRANS_CONST", 14);
DEFINE("TRANS_NUM", 5);
