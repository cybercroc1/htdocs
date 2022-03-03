<?php
session_name('tex');
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="starcall.css" rel="stylesheet" type="text/css">
<title>Техподдержка</title>
</head>
<script src="func.row_select.js"></script>
<form name=frm method=post>
<body leftmargin="3" topmargin="3">
<?php
extract($_REQUEST);
if(!isset($_SESSION['admin']) or $_SESSION['admin']<>'y') {
	echo "<font size=3 color=red>Не достаточно прав!</font>"; exit();
}
include("../../sup_conf/sup_conn_string");

if(isset($sav_loc)) {
	$loc_id;	
	$sav_loc_phone;
	$upd=OCIParse($c,"update sup_klinika set phone=:loc_phone where id='".$loc_id."'");
	OCIBindByName($upd,":loc_phone",$sav_loc_phone);
	OCIExecute($upd,OCI_DEFAULT);
	OCICommit($c);
}
if(isset($new_loc)) {
	if($new_loc_name<>'' and $new_loc<>'') {
		//добавляем новый объект
		$ins=OCIParse($c,"insert into sup_klinika (id,name,phone,location_grp_id,create_date) 
		values (sup_location_id.nextval,:new_loc_name, :new_loc_phone, :new_loc_grp,sysdate) 
		returning id into :new_loc_id");
		OCIBindByName($ins,":new_loc_name",$new_loc_name);
		OCIBindByName($ins,":new_loc_phone",$new_loc_phone);
		OCIBindByName($ins,":new_loc_grp",$new_loc_grp);
		OCIBindByName($ins,":new_loc_id",$new_loc_id,1024);
		OCIExecute($ins,OCI_DEFAULT);
		echo $new_loc_id;
		//
		OCICommit($c);
	}
}

echo "<form method=post>";

	echo "<font size=3><b>Места</b></font>";
	echo "<table id='tbl' class=white_table>
	<tr>
	<td bgcolor=white><b>Объект</b></td>
	<td bgcolor=white><b>Телефон</b></td>
	<td bgcolor=white><b>Группа объектов</b></td>
	<td bgcolor=white colspan=2><b>Дата последнего обращения</b></td>";
	echo "</tr>";
	
	//Добавить объект
	echo "<tr>
	<td bgcolor=green><input type=text name=new_loc_name size=35></td>
	<td bgcolor=green><input type=text name=new_loc_phone size=40></td>";
	echo "<td bgcolor=green><select name=new_loc_grp onchange=chk_new_loc()><option valie=''></value>";
	$q=OCIParse($c,"select * from sup_location_group order by name");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
		echo "<option value=".OCIResult($q,"ID").">".OCIResult($q,"NAME")."</option>";
	}
	echo "</select></td>";
	
	echo "<td bgcolor=green colspan=2><input type=submit name=new_loc value=ДОБАВИТЬ disabled></td></tr>";
	//
	//Объекты
	$q=OCIParse($c,"select slt.id grp_id,slt.name grp_name,t.id,t.name,t.phone, to_char(t.last_use,'DD.MM.YYYY') last_use
from SUP_KLINIKA t, sup_location_group slt
where slt.id=t.location_grp_id
order by slt.name, t.name");
	OCIExecute($q,OCI_DEFAULT);
	while (OCIFetch($q)) {
	echo "<tr id='tr_".OCIResult($q,"ID")."' class=selectable_row>
	<td>".OCIResult($q,"NAME")."</td>
	<td>".OCIResult($q,"PHONE")."</td>
	<td>".OCIResult($q,"GRP_NAME")."</td>
	<td>".OCIResult($q,"LAST_USE")."</td>";
	echo "<td>";
	echo "<a onclick=\"edit_loc('".OCIResult($q,"ID")."','".OCIResult($q,"NAME")."','".OCIResult($q,"PHONE")."')\"><img src=edit.gif title=\"Редактировать\" border=0></a>";
	echo "</td>";
	echo "</tr>";
	}
	echo "</table>";
	//
echo "</form>";
?>
<script language="javascript">
function edit_loc(loc_id,loc_name,phone) {
	if (!document.all.sav_loc) {
		with(document.all.tbl.rows['tr_'+loc_id]){
			v_name=cells[1].innerText;
			cells[1].innerHTML='<input type=text name=sav_loc_phone size=40 value="'+v_name.replace('"','&quot;')+'">';
			cells[4].innerHTML='<input type=hidden name=loc_id value='+loc_id+'><input type=submit name=sav_loc value=СОХРАНИТЬ>';
		}
	}
}
function chk_new_loc() {
	with(document.all){
		if(new_loc_grp.value=='') new_loc.disbled=true;
		else new_loc.disabled=false;
	}
}
</script>