<?php
function segment_phone($phone) {
	//возвращает строку с сегментированным по группам цифр телефоном
	if(preg_match('/\d./',$phone)) {
		if(strlen($phone)==11) $phone=substr($phone,0,1)."(".substr($phone,1,3).")".substr($phone,4,3)."-".substr($phone,7,2)."-".substr($phone,9,2);
		if(strlen($phone)>11 and substr($phone,0,3)=='810') $phone=substr($phone,0,1)."-".substr($phone,1,2)."-".substr($phone,3,1)."(".substr($phone,4,3).")".substr($phone,7,3)."-".substr($phone,10,2)."-".substr($phone,12);
	}
	return $phone;
}
?>