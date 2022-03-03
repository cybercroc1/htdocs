<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="billing.css" rel="stylesheet" type="text/css">
<title>Цены</title>
</head>
<body leftmargin="3" topmargin="3">
<form method=post>
<?php
//echo session_name()."--".session_id();
extract($_POST);

if(!isset($name)) $name='';
if(!isset($contact)) $contact='';
if(!isset($coment)) $coment='';
if(!isset($partner_id)) $partner_id='';

include("../../sup_conf/sup_conn_string");

if(isset($save)) {

	if($partner_id=='') {
		$ins=OCIParse($c,"insert into auction_partners (id,name,contact,coment)
values (nvl((select max(id) from auction_partners),0)+1,:name,:contact,:coment)
returning id into :id");
		OCIBindByName($ins,":id",$partner_id);
		OCIBindByName($ins,":name",$name);
		OCIBindByName($ins,":contact",$contact);
		OCIBindByName($ins,":coment",$coment);
		OCIExecute($ins,OCI_DEFAULT);
		$ins=OCIParse($c,"insert into auction_prices (partner_id,nom_id,price)
values ('".$partner_id."',:nom_id,:price)");
		foreach($price as $key => $val) {
			if($val<>'') {
			$val=str_replace('.',',',$val);
			OCIBindByName($ins,":nom_id",$key);
			OCIBindByName($ins,":price",$val);
			OCIExecute($ins,OCI_DEFAULT);
			}
		}
	}
	else {
		$upd=OCIParse($c,"update auction_partners set name=:name,contact=:contact,coment=:coment
where id='".$partner_id."'");
		OCIBindByName($upd,":name",$name);
		OCIBindByName($upd,":contact",$contact);
		OCIBindByName($upd,":coment",$coment);
		OCIExecute($upd,OCI_DEFAULT);

		$del=OCIParse($c,"delete from auction_prices where partner_id='".$partner_id."'");
		OCIExecute($del,OCI_DEFAULT);
		
		$ins=OCIParse($c,"insert into auction_prices (partner_id,nom_id,price)
values ('".$partner_id."',:nom_id,:price)");
		foreach($price as $key => $val) {
			if($val<>'') {
			$val=str_replace('.',',',$val);
			OCIBindByName($ins,":nom_id",$key);
			OCIBindByName($ins,":price",$val);
			OCIExecute($ins,OCI_DEFAULT);
			}
		}		
	}
OCICommit($c);
}
//--------------------------------------------------------------------------------------------------------
echo "<input type=hidden size=60 name='partner_id' value='".$partner_id."'>";


$q=OCIParse($c,"select t.id,t.nomenklatura,t.price from auction_nom t
order by t.nomenklatura");

echo "<div id='div1'><font color='red'>Заполните поля, отмеченные звездочкой</font><hr></div>";

echo "<b>Организация: <font color='red'>*</font></b><input type=text size=60 name='name' value='".$name."' onKeyUp='check_main()'><br>";
echo "<b>Контактная информация: <font color='red'>*</font></b><br><textarea rows=3 cols=55 name='contact' onKeyUp='check_main()'>".$contact."</textarea><br>";
echo "<b>Прочие условия:</b><br><textarea rows=5 cols=55 name='coment'>".$coment."</textarea><br>";

echo "<input type=submit name=save value='СОХРАНИТЬ'><hr>";

echo "<font color='red'>Заполните таблицу с ценами и нажмите \"Сохранить\". Наш менеджер по закупкам свяжется с Вами.</font>";

echo "<table bgcolor=black cellspacing=1 cellpadding=1>";

echo "<tr><td bgcolor=white><b>Номенклатура</b></td>";
echo "<td bgcolor=white><b>Наша цена</b></td>";
echo "<td bgcolor=white><b>Ваша цена</b></td></tr>";

OCIExecute($q,OCI_DEFAULT);

while (OCIFetch($q)) {
	if(isset($price[OCIResult($q,"ID")])) $val=$price[OCIResult($q,"ID")]; else $val='';
	echo "<tr><td bgcolor=white>".OCIResult($q,"NOMENKLATURA")."</td>";
	echo "<td bgcolor=white>".OCIResult($q,"PRICE")."</td>";
	echo "<td bgcolor=white><input size=6 type=text name=price[".OCIResult($q,"ID")."] value='".$val."' onkeypress=check(this,event) onchange=change_check(this)></td></tr>";
}

?>
</form>
<script language="javascript">
check_main();
function check_main() {
if(document.all.name.value!='' && document.all.contact.innerText!='') {
	document.all.save.disabled=false;
	document.all.div1.style.display='none';
	} 
	else {
	document.all.save.disabled=true;
	document.all.div1.style.display='';
	}  	
}

function check(o,e) {
str=String.fromCharCode(e.keyCode);
val=o.value+''+str;
if(val.replace(',','.')/val.replace(',','.')==1) e.returnValue = true;
else e.returnValue = false;

}
function change_check(obj) {
if(obj.value!='' && obj.value.replace(',','.')/obj.value.replace(',','.')!=1) {
alert('Значение должно быть числом'); obj.value='';
}
}
</script>
