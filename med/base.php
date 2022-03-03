<?php
DEFINE('DB_OCI', TRUE);
DEFINE("SEND_EMAIL", FALSE);
DEFINE("DEBUG_MODE", FALSE);
//DEFINE('CONVERT_TO_1251', TRUE);
DEFINE("IT_PLANET", 135);
DEFINE("APPLELOVERS", 290);
DEFINE("EXPORT_CUT", array(106,107,148,163,178,183,201,229,374));  // ���������, �������, Savitsky, CfB, Zizor, DMS, �����������, ���������
DEFINE("OUTER_ORDER", array(1,61,69,98,99,100)); // ������� ������� ���������������
DEFINE("CALL_STATISTIC", array(1,61,69,98,99,100,10,55,54,131)); // ������� ������� ���������������
DEFINE("SHOW_ITOG", array(1,4,61,69,290)); // �������� ���� ������/�� ������
DEFINE("SOK_SUPER", array(10,54,55,4)); //������������ ���������

DEFINE("SPEC_USER", 117); // 61);//
DEFINE("SPEC_USER_VIEW", array(110,111,112)); // ���������� �����������
DEFINE("SPEC_USER_EDIT", array(84,86,125,291,303)); // ��������� ������� ����� ��� ������
DEFINE("SEC_CHANCE_CALL", 21); // ����������� ������������ ������� �����
DEFINE("SPEC_USER_CALL", array(126,127,128,129,156,157,193,194,197,204,207,241,245,263,264,265)); // �������� ������� ������������ � ������������
DEFINE("COST_EDIT", array(1,4,61,69,115,150)); // �������������� ����������� � ��������� ���������
DEFINE("WRITE_ONLY", array(163)); // � �������� ������ ������ � �������

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

DEFINE("needs", '#FF733C'); // ���� ������������ �������

// ���� �������������
DEFINE("USER_ADMIN", 1);
DEFINE("USER_SUPER", 2);
DEFINE("USER_VIEW", 3);
DEFINE("USER_USER", 4);

// ����������� �������������
DEFINE("CAN_PHONE", 1);
DEFINE("CAN_FINANCE", 2);
//DEFINE("CAN_SOME", 3);
DEFINE("CAN_HEAR", 4);
//sva:
//��� ������ ���������� � ���������� ������ $_SESSION['access']['data_acc']. 
//�������� �� ������ � ������ ����� ������ ��� if(isset($_SESSION['access']['data_acc']['CAN_PHONE']))

// ��� ������
DEFINE("CALL_FIRST", 1); // ���������
DEFINE("CALL_SECOND", 2); // ���������

// ����������� ������
DEFINE("CALL_IN", 'in'); // ��������
DEFINE("CALL_OUT", 'out'); // ���������
DEFINE("CALL_BACK", 'callback'); // ������������
DEFINE("CALL_WAY", array(''=>' ','in'=>'��������', 'out'=>'���������', 'callback'=>'������������'));

//���� ������
DEFINE("THEME_NOT", 0); // ������ �� �������
DEFINE("THEME_MED", 1); // ����������� ������
DEFINE("THEME_INFO",2); // ���������� ��� �����������
DEFINE("THEME_AUD", 3); // ������������� � �������
DEFINE("THEME_ANA", 4); // �������
DEFINE("THEME_TRUD",5); // ��������������� ���������
DEFINE("THEME_OUT", 6); // ������� �� ������
DEFINE("THEME_OTHER", 7); // ������ ������

// ����������� ������
DEFINE("SERVICE_ALL", -1); // ���
DEFINE("SERVICE_NOT",  0); // ������ �� �������
DEFINE("SERVICE_STOM", 1); // ������������
DEFINE("SERVICE_KOSM", 2); // ������������
DEFINE("SERVICE_GINE", 3); // �����������
DEFINE("SERVICE_PLAS", 4); // ��������
DEFINE("SERVICE_TRIH", 5); // ����������
DEFINE("SERVICE_MICH", 6); // ������
DEFINE("SERVICE_LIST", array('������ �� �������', '������������', '������������', '�����������', '��������', '����������', '������'));

// ������������
DEFINE("STOM_NOT",    0); // ������ �� �������
DEFINE("STOM_VINIR", 100); // ������
DEFINE("STOM_CHILD_ORT", 101); // ������� ����������
DEFINE("STOM_CHILD", 102); // ������� ������������
DEFINE("STOM_IMPLANT", 103); // �����������
DEFINE("STOM_LASER", 104); // �������� ������������
DEFINE("STOM_TREAT", 105); // ������� �����
DEFINE("STOM_ORTODONT", 106); // ����������
DEFINE("STOM_ORTOPED", 107); // ���������
DEFINE("STOM_WHITE", 108); // �����������
DEFINE("STOM_PARODONT", 109); // ��������������
DEFINE("STOM_PROTEZ", 110); // ��������������
DEFINE("STOM_HYGIEN", 111); // ���������������� �������
DEFINE("STOM_RESTORE", 112); // ����������� �����
DEFINE("STOM_SURGERY", 113); // ��������
//DEFINE("STOM_HOUR",   3); // �������������� �� ���
//DEFINE("STOM_NEYLON", 2); // ���������� ������
DEFINE("STOM_OTHER", 190); // ������
//DEFINE("STOM_DET", array('', '������� ������������', '���������� ������', '�������������� �� ���'));

// ��������� ��������� ������
DEFINE("RESULT_NOT",    0); // ������ �� �������
DEFINE("RESULT_KC",     1); // ��������� � ��
DEFINE("RESULT_CLINIC", 2); // + STATUS_CLINIC_CALL
DEFINE("RESULT_WAIT",   3); // ������� ������
DEFINE("RESULT_AON",    4); // �� ������� �����
DEFINE("RESULT_KC_SELF",5); // ��������� � �� �� ����
DEFINE("CALL_RES", array('', '� ��', '� �������', '���� ������', '���', '� �� (����)'));
//DEFINE("CALL_RES_LONG", array('', '�������� � ��', '�������� � �������', '������� ������'));

DEFINE("DEVICE_PHONE", 1); // �������
DEFINE("DEVICE_MAIL",  2); // e-mail
DEFINE("DEVICES", array('', '�������', 'E-mail'));

// ������� ��������� �������
DEFINE("STATUS_NOT", 0); // ������ �� �������
DEFINE("STATUS_OPEN", 1); // ����� ������
DEFINE("STATUS_WORK", 2); // �������� ���������
DEFINE("STATUS_CALL_BACK", 3); // ������ �� ��������, ������ "��������� ������������"
DEFINE("STATUS_CALL_NOT", 4); // ��������+��������
DEFINE("STATUS_CALL_STOP", 5); // ���������� ��������
DEFINE("STATUS_CLINIC", 6); // ������ � �������
DEFINE("STATUS_CL_CANCEL", 7); // ������ ������
DEFINE("STATUS_ERROR", 8); // ������
DEFINE("STATUS_REPEAT", 9); // ���������
DEFINE("STATUS_CLINIC_NOT", 10); // ����� �� ������ � �������
DEFINE("STATUS_BREAK_LINE", 11); // ����� �����

DEFINE("STATUS_NOT_COME", 20); // ����������� �������

// ��� ���������� ������ ������� ���������� ��������� ������������� � ���� ���������� �� ���������� ������, ����� ������
DEFINE("STATUS_CLINIC_CALL", 60); // ������ ��������� � ������� (RESULT_CLINIC)
DEFINE("STATUS_CLOSED", 99); // ������ ��� �������� ����� ������ ��� �������� ���� ��������� ������
DEFINE("STATUS_NEGATIVE", 77); // �����/�������

// ����������� ������
DEFINE("STAT_ERR_OTHER", 800); // ������
DEFINE("STAT_ERR_APPL", 801); // �� �������� ������
DEFINE("STAT_ERR_CONTR", 802); // ����������������
DEFINE("STAT_ERR_NUM", 803); // ���� � ������
DEFINE("STAT_ERR_NAME", 804); // ���� � �����
DEFINE("STAT_ERR_SERV", 805); // ��� ������ ������
DEFINE("STAT_ERR_DAYS", 806); // �������� 2 ���
DEFINE("STAT_ERR_INTER", 807); // ��������

// ��������� �������
DEFINE("SOURCE_ALL", -1); // ���
DEFINE("SOURCE_NOT", 0); //������ �� �������
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
DEFINE("SOURCE_BANNER_SUB", 45); //���������� ����������

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
DEFINE("CITIES", array('', '������', '�����', '��', '����'));

//������� ������ � ��
//DEFINE("TRANS_CONST", 14);
DEFINE("TRANS_NUM", 5);
DEFINE("WRITE_CLINIC_NUM", 2);

// ������� ������ (������)
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
