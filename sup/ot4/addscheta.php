<?php

//в папке PHP7/includes:
include('sc-crm/btx_funct.php');
//лежит рядом
include('conn.php');

$subdomain = 'wilstream';
$user_id = '123'; //ID пользователя в битрикс, от которого выполняется скрипт
$secret = 'iaxnwlkjcs3lkezs';//секрет вебхука в битриксе

function show_json($post_values) {
	mb_convert_variables('utf-8','cp1251',$post_values);
	$j=iconv('utf-8','cp1251',json_encode($post_values,JSON_UNESCAPED_UNICODE));
	echo "json:<br><textarea cols=80 rows=10>".$j."</textarea>";	
}
echo date("d/m/Y");
echo "<br>Добавление счетов в базу:<br>";
$i=1;
while($i != 0){
	$i=0;
//Вытаскиваем последний ID счета
$query = $btx24->query("
SELECT TOP(1) ID 
FROM [btx24].[dbo].[invoice_list] 
ORDER BY ID DESC
");
$query->setFetchMode(PDO::FETCH_ASSOC);
$result=$query->fetch();
$maxid = $result['ID'];
//echo $maxid;

//Запрос новых счетов
$method = 'crm.invoice.list';//вывести счета
$get_values='';
$post_values['filter'][">ID"]=$maxid;//фильтр
$post_values['order']["DATE_INSERT"]='ASC';

$res=btx_request($subdomain,$method,$user_id,$secret,$get_values,$post_values);

//Добавление новых счетов в базу

foreach($res['values']['result'] as $v){
	$ID=$v['ID'];
	$ACCOUNT_NUMBER=$v['ACCOUNT_NUMBER'];
	$COMMENTS=$v['COMMENTS'];
	$CREATED_BY=$v['CREATED_BY'];
	$CURRENCY=$v['CURRENCY'];
	($v['DATE_BILL']!='')?$DATE_BILL= substr($v['DATE_BILL'],0,19):$DATE_BILL=NULL;
	($v['DATE_INSERT']!='')?$DATE_INSERT= substr($v['DATE_INSERT'],0,19):$DATE_INSERT=NULL;
	($v['DATE_MARKED']!='')?$DATE_MARKED= substr($v['DATE_MARKED'],0,19):$DATE_MARKED=NULL;
	($v['DATE_PAY_BEFORE']!='')?$DATE_PAY_BEFORE=substr($v['DATE_PAY_BEFORE'],0,19):$DATE_PAY_BEFORE=NULL;
	($v['DATE_PAYED']!='')?$DATE_PAYED=substr($v['DATE_PAYED'],0,19):$DATE_PAYED=NULL;
	($v['DATE_STATUS']!='')?$DATE_STATUS=substr($v['DATE_STATUS'],0,19):$DATE_STATUS=NULL;
	($v['DATE_UPDATE']!='')?$DATE_UPDATE=substr($v['DATE_UPDATE'],0,19):$DATE_UPDATE=NULL;
	$EMP_PAYED_ID=$v['EMP_PAYED_ID'];
	$EMP_STATUS_ID=	$v['EMP_STATUS_ID'];
	$LID= $v['LID'];
	$IS_RECURRING=$v['IS_RECURRING'];
	$XML_ID=$v['XML_ID'];
	$ORDER_TOPIC=$v['ORDER_TOPIC'];
	$PAY_SYSTEM_ID=	$v['PAY_SYSTEM_ID'];
	$PAY_VOUCHER_DATE=$v['PAY_VOUCHER_DATE'];
	$PAY_VOUCHER_NUM=$v['PAY_VOUCHER_NUM'];
	$PAYED=	$v['PAYED'];
	$PERSON_TYPE_ID=$v['PERSON_TYPE_ID'];
	$PRICE=	$v['PRICE'];
	$REASON_MARKED=	str_replace($v['REASON_MARKED'],"'","''");
	$RESPONSIBLE_EMAIL=	$v['RESPONSIBLE_EMAIL'];
	$RESPONSIBLE_ID= $v['RESPONSIBLE_ID'];
	$RESPONSIBLE_LAST_NAME=	$v['RESPONSIBLE_LAST_NAME'];
	$RESPONSIBLE_LOGIN=	$v['RESPONSIBLE_LOGIN'];
	$RESPONSIBLE_NAME= $v['RESPONSIBLE_NAME'];
	$RESPONSIBLE_PERSONAL_PHOTO= $v['RESPONSIBLE_PERSONAL_PHOTO'];
	$RESPONSIBLE_SECOND_NAME=$v['RESPONSIBLE_SECOND_NAME'];
	$RESPONSIBLE_WORK_POSITION=	$v['RESPONSIBLE_WORK_POSITION'];
	$STATUS_ID= $v['STATUS_ID'];
	$TAX_VALUE= $v['TAX_VALUE'];
	$UF_COMPANY_ID=	$v['UF_COMPANY_ID'];
	$UF_CONTACT_ID=	$v['UF_CONTACT_ID'];
	$UF_MYCOMPANY_ID= $v['UF_MYCOMPANY_ID'];
	$UF_DEAL_ID= $v['UF_DEAL_ID'];
	$UF_QUOTE_ID= $v['UF_QUOTE_ID'];
	$USER_DESCRIPTION= $v['USER_DESCRIPTION'];
	echo "=";
	$sql="INSERT INTO [btx24].[dbo].[invoice_list] ([ID]
	,[ACCOUNT_NUMBER]
	,[COMMENTS]
	,[CREATED_BY]
	,[CURRENCY]
	,[DATE_BILL]
	,[DATE_INSERT]
	,[DATE_MARKED]
	,[DATE_PAY_BEFORE]
	,[DATE_PAYED]
	,[DATE_STATUS]
	,[DATE_UPDATE]
	,[EMP_PAYED_ID]
	,[EMP_STATUS_ID]
	,[LID]
	,[IS_RECURRING]
	,[XML_ID]
	,[ORDER_TOPIC]
	,[PAY_SYSTEM_ID]
	,[PAY_VOUCHER_DATE]
	,[PAY_VOUCHER_NUM]
	,[PAYED]
	,[PERSON_TYPE_ID]
	,[PRICE]
	,[REASON_MARKED]
	,[RESPONSIBLE_EMAIL]
	,[RESPONSIBLE_ID]
	,[RESPONSIBLE_LAST_NAME]
	,[RESPONSIBLE_LOGIN]
	,[RESPONSIBLE_NAME]
	,[RESPONSIBLE_PERSONAL_PHOTO]
	,[RESPONSIBLE_SECOND_NAME]
	,[RESPONSIBLE_WORK_POSITION]
	,[STATUS_ID]
	,[TAX_VALUE]
	,[UF_COMPANY_ID]
	,[UF_CONTACT_ID]
	,[UF_MYCOMPANY_ID]
	,[UF_DEAL_ID]
	,[UF_QUOTE_ID]
	,[USER_DESCRIPTION]) VALUES ('$ID'
	,'$ACCOUNT_NUMBER'
	,'$COMMENTS'
	,'$CREATED_BY'
	,'$CURRENCY'
	,CONVERT(datetime,'$DATE_BILL',126)
	,CONVERT(datetime,'$DATE_INSERT',126)
	,CONVERT(datetime,'$DATE_MARKED',126)
	,CONVERT(datetime,'$DATE_PAY_BEFORE',126)
	,CONVERT(datetime,'$DATE_PAYED',126)
	,CONVERT(datetime,'$DATE_STATUS',126)
	,CONVERT(datetime,'$DATE_UPDATE',126)
	,'$EMP_PAYED_ID'
	,'$EMP_STATUS_ID'
	,'$LID'
	,'$IS_RECURRING'
	,'$XML_ID'
	,'$ORDER_TOPIC'
	,'$PAY_SYSTEM_ID'
	,'$PAY_VOUCHER_DATE'
	,'$PAY_VOUCHER_NUM'
	,'$PAYED'
	,'$PERSON_TYPE_ID'
	,'$PRICE'
	,'$REASON_MARKED'
	,'$RESPONSIBLE_EMAIL'
	,'$RESPONSIBLE_ID'
	,'$RESPONSIBLE_LAST_NAME'
	,'$RESPONSIBLE_LOGIN'
	,'$RESPONSIBLE_NAME'
	,'$RESPONSIBLE_PERSONAL_PHOTO'
	,'$RESPONSIBLE_SECOND_NAME'
	,'$RESPONSIBLE_WORK_POSITION'
	,'$STATUS_ID'
	,'$TAX_VALUE'
	,'$UF_COMPANY_ID'
	,'$UF_CONTACT_ID'
	,'$UF_MYCOMPANY_ID'
	,'$UF_DEAL_ID'
	,'$UF_QUOTE_ID'
	,'$USER_DESCRIPTION')";
	//проверка запроса
	//echo($sql);
	$btx24->exec($sql);
	$i++;
}
echo"<br />";
echo "Добавлено $i строк.";
}
echo"<br />";
echo"<br />";
?>