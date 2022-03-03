<!DOCTYPE html>
<HTML>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="oktadmin.css" rel="stylesheet" type="text/css">
<title></title>
</head>
<script src="func.row_select.js"></script>
<script src="func.filters.js"></script>
<script src="func.orders.js"></script>
<script>
function copy_to_clipboard() {
	var text='';
	with(document.getElementById('tbl')) {
		for(i=0; i<rows.length; i++) {
			if(text!='') {
				for(j=0; j<rows[i].cells.length; j++) {
					text+=rows[i].cells[j].innerText+String.fromCharCode(9);
				}
				text+=String.fromCharCode(13)+String.fromCharCode(10);				
			}
			if(rows[i].id=='table_head') {
				for(j=0; j<rows[i].cells.length; j++) {
					text+=rows[i].cells[j].childNodes[0].childNodes[0].rows[0].cells[0].innerText+String.fromCharCode(9);
				}
				text+=String.fromCharCode(13)+String.fromCharCode(10);
			}
		}
	}

  document.getElementById('clipb').value=text;
  
  var copyText = document.getElementById('clipb');
  copyText.select();
  document.execCommand("copy");
  alert("Скопировано в буфер");
	
}
</script>
<BODY style="margin:0">
<?php

//это универсальный файл отображения таблиц по запросу к MSSQL
//этот файл инклудится к файлам с окончанием ...list.php

extract($_REQUEST); 

//фильтры
if(isset($filters) and count($filters)>0) {
if(!isset($filter_no_default)) { //выставление фильтра в значение по умолчанию
	foreach($filters as $filter_name => $filter_defaults) {
		if(is_array($filter_defaults)) {
			$filter_selected_values[$filter_name]=$filter_defaults;
		}
	}
}
$filter_sql='';
$filter_sql_tmp='';
//поиск
if(isset($find_string) and trim($find_string)<>'' and isset($finds) and count($finds>0)) { //если есть строка поиска то заменяем фильтры на поиск
	$n=0;
	foreach($finds as $field_name => $foo) { 
		$n++;
		if(isset($fields[$field_name]['case']) and $fields[$field_name]['case']!="") $filter_case=$fields[$field_name]['case']; else $filter_case=$field_name;
		if($n>1) $filter_sql_tmp.=" || ";
		$filter_sql_tmp.="nvl(".$filter_case.",'')";
	}
	if($filter_sql_tmp<>'') $filter_sql=" and upper(replace(".$filter_sql_tmp.",' ','')) like '%'||upper(replace('".$find_string."',' ',''))||'%' ";
}
//фильтры
else if(isset($filter_selected_values)) {
	foreach($filter_selected_values as $filter_name => $value_arr) {
		if(count($value_arr)>0) {
			if(isset($fields[$filter_name]['case']) and $fields[$filter_name]['case']!="") $filter_case=$fields[$filter_name]['case']; else $filter_case=$filter_name;
			$filter_sql_tmp.=" and nvl(".$filter_case.",'') in (";
				$n=0;
				foreach($value_arr as $val) {
					$n++;
					if($val=='all') {
						unset($filter_selected_values[$filter_name]);
						$filter_sql_tmp='all';
						break;						
					}
					else {
						if($n>1) $filter_sql_tmp.=",";
						$filter_sql_tmp.="'".$val."'";
					}
				}			
			if($filter_sql_tmp=='all') {
				$filter_sql_tmp='';
			}
			else {
				$filter_sql_tmp.=")";
			}
			$filter_sql.=$filter_sql_tmp;
			$filter_sql_tmp='';
		}
	}
}
}
if(isset($filter_sql)) $sql_text=str_replace("/*filters*/",$filter_sql,$sql_text);

//сортировка
if(isset($orders) and count($orders)>0) {
	if(!isset($order_no_default)) { //выставление сортировки в значение по умолчанию	
		$order_num=0;
		foreach($orders as $field_name => $order_type) {
			
			if(in_array($order_type,array('up','asc'))) {
				$order_num++;
				$orders_selected[$field_name]['type']='asc';
				$orders_selected[$field_name]['num']=$order_num;
			}	
			else if(in_array($order_type,array('down','desc'))) {
				$order_num++;
				$orders_selected[$field_name]['type']='desc';
				$orders_selected[$field_name]['num']=$order_num;
			}	
			else {
				$orders_selected[$field_name]['type']='none';
				$orders_selected[$field_name]['num']='';
			}
		}
	}
}

if(isset($orders_selected)) {
	
	foreach($orders_selected as $field_name => $order) {
		if($order['num']<>'') {
			$orders_tmp[$order['num']]['field_name']=$field_name;
			$orders_tmp[$order['num']]['order_type']=$order['type'];
		}
	}
	
	if(isset($orders_tmp)) {
	ksort($orders_tmp);

	foreach($orders_tmp as $order) {
		
		
		if(isset($fields[$order['field_name']]['case']) and $fields[$order['field_name']]['case']!="") $order_case=$fields[$order['field_name']]['case']; 
		else $order_case=$order['field_name'];
		

		$order_sql[]=$order_case.' '.$order['order_type'];
	}
	
	if(isset($order_sql)){
		$order_sql_text="order by ".implode(", ",$order_sql);
		$sql_text=str_replace("/*orders*/",$order_sql_text,$sql_text);
	}
	}
}

//require_once '../../../sc_conf/sc_conn_string';
	//echo "<textarea>$sql_text</textarea>";
	$q = OCIParse($c,$sql_text);
	echo "<form name=frm method=post>"; 
	echo "<table id=tbl class='white_table' align=center>";
	
	OCIExecute($q);
	$fcount=oci_num_fields($q);
	
	for ($i = 1; $i <= $fcount; $i++) {
		$columns[$i] = oci_field_name($q,$i);
	}
	
		//Шапка
	//поиск
	if(isset($finds) and count($finds)>0) {
		echo "<tr><td colspan=".count($fields)."><input type=input style='width:99%' name=find_string placeholder='Поиск без учета фильтров' value='".(isset($find_string)?$find_string:"")."' onkeyup=ch_filter() onpaste=ch_filter() onchange=ch_filter()></td></tr>";
	}
		//фильтры
		if(isset($filters) and count($filters)>0) {
			if(isset($find_string) and trim($find_string)<>'') $filter_hide='y'; //скрываем фильтры, если задана строка поиска
			echo "<tr onmouseover='filters_open(this)' onmouseout='filters_close(this)'".(isset($filter_hide)?" style='display:none'":"").">";
			echo "<input type=hidden name=filter_no_default />"; //это поле нужно, что бы отключить фильтр по-умолчанию после сабмита формы	
				foreach($fields as $cname=>$field_settings) {
					echo "<td>";
					if(isset($filters[$cname])) {
						echo "<select multiple id=\"fil_".$cname."\" name=\"filter_selected_values[".$cname."][]\" onchange=ch_filter() size=1 style='width:100%;'>
						<option value='all'>ВСЕ</option></select>";
					}
					echo "</td>";
				}
			echo "</tr>";			
		}

		echo "<tr id='table_head'>";
		//echo "<tr id='table_head' style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)'>";
			if(isset($orders) and count($orders)>0) {
				echo "<input type=hidden name=order_no_default />"; //это поле нужно, что бы отключить сортировку по-умолчанию после сабмита формы	
			}
			foreach($fields as $cname=>$field_settings) {
				echo "<th align=center>";
				echo "<table style='border:none;width:100%'><tr>";
				
				if(isset($edit_id_name) and $edit_id_name<>'') 
					echo "<td align=center style='border:none;width:100%;cursor:pointer' onmouseover='sel_row(this.parentNode.parentNode.parentNode.parentNode.parentNode)' onmouseout='unsel_row(this.parentNode.parentNode.parentNode.parentNode.parentNode)' onclick='res=click_row(this.parentNode.parentNode.parentNode.parentNode.parentNode);if(res==\"click\"){edit(\"\");}else{edit(\"\")}'>";
				else 
					echo "<td align=center style='border:none;width:100%;cursor:pointer' onmouseover='sel_row(this.parentNode.parentNode.parentNode.parentNode.parentNode)' onmouseout='unsel_row(this.parentNode.parentNode.parentNode.parentNode.parentNode)'>";
				
				if(isset($field_settings['name']) and $field_settings['name']!='') echo $field_settings['name'];
				else echo $cname;
				echo "</td>";
				//сортировка
				if(isset($orders_selected[$cname])) {
					echo "<td style='border:none;color:blue;cursor:pointer;' onclick=ch_order(this) field_name=\"".$cname."\" order_type='".$orders_selected[$cname]['type']."' order_num='".$orders_selected[$cname]['num']."'>
					</td> ";
				}

				echo "</tr></table></th>";
			}
		//применение настроек сортировки
		if(isset($orders_selected)) echo "<script>show_all_orders();</script>";
		echo "</tr>";
	
	$rnum=0; while ($row=oci_fetch_assoc($q)) {$rnum++;
		
		if(isset($edit_id_name) and $edit_id_name<>'') 
			echo "<tr style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='res=click_row(this);if(res==\"click\"){edit(\"".$row[$edit_id_name]."\");}else{edit(\"\")}'>";
		else 
			echo "<tr style='cursor:pointer' onmouseover='sel_row(this)' onmouseout='unsel_row(this)' onclick='click_row(this)'>";
		
		foreach($fields as $cname=>$field_settings) {
			$val=$row[$cname];
			echo "<td>";
			echo $val;
			echo "</td>";
			//сбор значений для фильтров
			if(isset($filters[$cname])) {
				if(!isset($filter_list_values[$cname]) or !in_array($val,$filter_list_values[$cname])) {
					$filter_list_values[$cname][]=$val;
				}
			}
		}
		echo "</tr>";
	}
	//сортировка значений фильтров
	if(isset($filter_list_values)) {
		foreach($filter_list_values as $filter_name => &$list_values) { //Для того, чтобы напрямую изменять элементы массива внутри цикла, переменной $value должен предшествовать знак &
			asort($list_values);
			//передаем методом post значения фильтров, что бы отображать все значения, если это закомменторовать, то будут отображаться только существующие в наборе
			foreach($list_values as $value) {
				echo "<input type=hidden name='filter_list_values[".$filter_name."][]' value='".$value."' />";
			}
			
		}
	}
	
echo "</table>";
echo "</form>";
echo "<textarea id=clipb style='width:10px;height:10px' onclick=copy_to_clipboard() title='Копировать'></textarea>";

//заполнение фильтров в шапке таблицы
if(isset($filters)) {
	echo "<script>";
	//установка значения "ВСЕ" для фильтров, в которых ничего не выбрано
	foreach($filters as $filter_name=>$foo) {
		if(!isset($filter_selected_values[$filter_name])) { //по данному фильтру выбрано ВСЕ
			echo "document.getElementById(\"fil_".$filter_name."\").options[0].selected=true;";
		}		
	}
	//заполнение фильтров значениями
	if(isset($filter_list_values)) {
		foreach($filter_list_values as $filter_name => $values) {
			foreach($values as $value) {
				$selected='';
				if(isset($filter_selected_values[$filter_name]) and count($filter_selected_values[$filter_name]>0)) {
					foreach($filter_selected_values[$filter_name] as $selected_val) {
						if($selected_val==$value) $selected='selected';
					}
				}
				echo "add_options(document.getElementById(\"fil_".$filter_name."\"),'".$value."','".$value."','".$selected."');";
			}
		}
	}
	echo "</script>";
}
if(isset($edit_id_name) and $edit_id_name<>'') {
	echo "<script>function edit(id){".$edit_frame.".location='".$edit_url."'+id}</script>";
}
?>
</BODY>
</HTML>
