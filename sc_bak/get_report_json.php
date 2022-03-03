<?php
header('Content-Type: application/json; charset=utf-8');

ignore_user_abort();

extract($_REQUEST);

/*Венда
Отчет по каждому звонку: http://vg.wilstream.ru/sc/get_report_json.php?project_id=921&form_id=12109&start_YYYYMMDDHH24MISS=20170901000000&end_YYYYMMDDHH24MISS=20170926000000
Отчет-заявка: http://vg.wilstream.ru/sc/get_report_json.php?project_id=921&form_id=12108&start_YYYYMMDDHH24MISS=20170901000000&end_YYYYMMDDHH24MISS=20170926000000
*/
if($_SERVER['REMOTE_ADDR']=='91.224.182.101' //тест
or (($_SERVER['REMOTE_ADDR']=='83.137.221.45') and ($form_id=='12108' or $form_id=='12109')) //Венда
or ($token=='55c4d996-6f50-4463-9a5c-6afb12da6c2a' and $project_id=='1874' and ($form_id=='28101' or $form_id=='28102' or $form_id=='28103' or $form_id=='28104')) //Трансэнерго
) {}
else {echo "доступ запрещен"; exit();}


if(!isset($project_id) or !isset($form_id)) exit();

include("../../sc_conf/sc_conn_string");
include("../../sc_conf/func_code_phone.php");

$q=OCIParse($c,"select to_char(b.date_call,'DD.MM.YYYY HH24:MI:SS') date_call,b.cdpn,b.cgpn,b.agid,b.cdr_thr_id,p.name project_name,
f.name form_name,f.send_cdpn,f.send_cgpn,f.send_agid,f.post_url,f.CODED_AON,
r.id report_id
from sc_projects p, sc_forms f ,SC_CALL_REPORT r, sc_call_base b
where p.id=".$project_id." and f.project_id=p.id and f.id=".$form_id." and r.call_base_id=b.id and r.form_id=f.id and b.project_id=".$project_id." 
and b.date_call between to_date('".$start_YYYYMMDDHH24MISS."','YYYYMMDDHH24MISS') and to_date('".$end_YYYYMMDDHH24MISS."','YYYYMMDDHH24MISS')");

$q_val=OCIParse($c,"select value from SC_CALL_REPORT_VALUES where call_report_id=:report_id and object_id=:object_id and object_name=:object_name");	

$q_obj=OCIParse($c,"select id obj_id, name, type_id from sc_form_object where form_id=".$form_id." order by ordering");
OCIExecute($q_obj,OCI_DEFAULT);
while (OCIFetch($q_obj)) {
	$obj[OCIResult($q_obj,"OBJ_ID")]=OCIResult($q_obj,"NAME");
}

OCIExecute($q,OCI_DEFAULT);
while(OCIFetch($q)) {
	$var['Имя формы']=iconv('Windows-1251','utf-8',OCIResult($q,"FORM_NAME"));
	$var['Дата звонка']=iconv('Windows-1251','utf-8',OCIResult($q,"DATE_CALL"));
	$report_id=OCIResult($q,"REPORT_ID");
	if (OCIResult($q,"SEND_CDPN")=='y') {
		if(OCIResult($q,"CODED_AON")=='y') $var['АОН']=iconv('Windows-1251','utf-8',phone_conv_coding(OCIResult($q,"CDPN")));
		else $var['АОН']=iconv('Windows-1251','utf-8',OCIResult($q,"CDPN"));
	}
	if (OCIResult($q,"SEND_CGPN")=='y') $var['Номер доступа']=iconv('Windows-1251','utf-8',OCIResult($q,"CGPN"));
	if (OCIResult($q,"SEND_AGID")=='y') $var['ID оператора']=iconv('Windows-1251','utf-8',OCIResult($q,"AGID"));

	foreach($obj as $obj_id => $obj_name) {
		OCIBindByName($q_val,":object_name",$obj_name);
		OCIBindByName($q_val,":object_id",$obj_id);
		OCIBindByName($q_val,":report_id",$report_id);
		OCIExecute($q_val,OCI_DEFAULT);
		
		$n=0;
		$var[iconv('Windows-1251','utf-8',$obj_name)]='';
		while(OCIFetch($q_val)) {
			$value=(OCIResult($q_val,"VALUE"));	
			//Кодированный телефон
			if (OCIResult($q_obj,"TYPE_ID")=='CT') {
				$value=phone_conv_coding($value);
			}	
			if ($n>0) $var[iconv('Windows-1251','utf-8',$obj_name)].= "; ";
				$var[iconv('Windows-1251','utf-8',$obj_name)].=iconv('Windows-1251','utf-8',$value);
			$n++;
		}
		
	}		
	$a=json_encode($var);
	
	
	echo json_fix_cyr($a);
}

function json_fix_cyr($json_str)
{
    $cyr_chars = array(
        '\u0430' => 'а', '\u0410' => 'А',
        '\u0431' => 'б', '\u0411' => 'Б',
        '\u0432' => 'в', '\u0412' => 'В',
        '\u0433' => 'г', '\u0413' => 'Г',
        '\u0434' => 'д', '\u0414' => 'Д',
        '\u0435' => 'е', '\u0415' => 'Е',
        '\u0451' => 'ё', '\u0401' => 'Ё',
        '\u0436' => 'ж', '\u0416' => 'Ж',
        '\u0437' => 'з', '\u0417' => 'З',
        '\u0438' => 'и', '\u0418' => 'И',
        '\u0439' => 'й', '\u0419' => 'Й',
        '\u043a' => 'к', '\u041a' => 'К',
        '\u043b' => 'л', '\u041b' => 'Л',
        '\u043c' => 'м', '\u041c' => 'М',
        '\u043d' => 'н', '\u041d' => 'Н',
        '\u043e' => 'о', '\u041e' => 'О',
        '\u043f' => 'п', '\u041f' => 'П',
        '\u0440' => 'р', '\u0420' => 'Р',
        '\u0441' => 'с', '\u0421' => 'С',
        '\u0442' => 'т', '\u0422' => 'Т',
        '\u0443' => 'у', '\u0423' => 'У',
        '\u0444' => 'ф', '\u0424' => 'Ф',
        '\u0445' => 'х', '\u0425' => 'Х',
        '\u0446' => 'ц', '\u0426' => 'Ц',
        '\u0447' => 'ч', '\u0427' => 'Ч',
        '\u0448' => 'ш', '\u0428' => 'Ш',
        '\u0449' => 'щ', '\u0429' => 'Щ',
        '\u044a' => 'ъ', '\u042a' => 'Ъ',
        '\u044b' => 'ы', '\u042b' => 'Ы',
        '\u044c' => 'ь', '\u042c' => 'Ь',
        '\u044d' => 'э', '\u042d' => 'Э',
        '\u044e' => 'ю', '\u042e' => 'Ю',
        '\u044f' => 'я', '\u042f' => 'Я',

        '\r' => '',
        '\n' => '<br />',
        '\t' => ''
    );

    foreach ($cyr_chars as $cyr_char_key => $cyr_char) {
        $json_str = str_replace($cyr_char_key, $cyr_char, $json_str);
    }
    return $json_str;
}
?>
