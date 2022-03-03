var src_row_index='x';
var dst_row_index='x';

var std_field_name = new Array();
var std_field_desc = new Array();

var current_row_index='';

document.body.onselectstart=function() {
	if (src_row_index!='x') return false;
}
/*function change() {
	if (document.all.new_file.value=="") {
		document.all.preview.disabled=true;
	}
	else {
		document.all.preview.disabled=false;
	}
}*/
function fMD(obj) { //нажатие кнопки мыши
	src_row_index=obj.parentNode.rowIndex;
	return false;
}

//перестановка строк местами
/*function fMU(obj) { //отпускание кнопки мыши
	dst_row_index=obj.parentNode.rowIndex;
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
		with(document.all.tbl) {
			src_cells=rows[src_row_index].cells.length;
			dst_cells=rows[dst_row_index].cells.length;
			for(i=1; i<=9; i++) { //кол-во циклов равно кол-ву столбцов
				if(i<=src_cells) rows[dst_row_index].appendChild(rows[src_row_index].cells[0]);
				if(i<=dst_cells) rows[src_row_index].appendChild(rows[dst_row_index].cells[0]);
	}}}
	src_row_index='x';
}*/
//перенос строк со сдвигом
function fMU(obj) { //отпускание кнопки мыши
	dst_row_index=obj.parentNode.rowIndex;
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
		with(document.all.tbl) {
			//вверх
			for(i=src_row_index; i>dst_row_index; i--) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i-1].cells.length;
				for(j=1; j<=11; j++) { //кол-во циклов равно кол-ву столбцов
					if(j<=src_cells) rows[i-1].appendChild(rows[i].cells[0]);
					if(j<=dst_cells) rows[i].appendChild(rows[i-1].cells[0]);
			}}
			//вниз
			for(i=src_row_index; i<dst_row_index; i++) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i+1].cells.length;
				for(j=1; j<=11; j++) { //кол-во циклов равно кол-ву столбцов
					if(j<=src_cells) rows[i+1].appendChild(rows[i].cells[0]);
					if(j<=dst_cells) rows[i].appendChild(rows[i+1].cells[0]);
	}}}}
	src_row_index='x';
}
/*function fCancelSave() {
	frm.save.disabled=false;
	frm.save.value='Сохранить';
	frm.cancel_save.style.display='none';
	document.getElementById('save_status').innerHTML='';
}*/
function plus_field(obj) {
	current_row_index=obj.parentNode.rowIndex; 
	parent.logFrame.location='adm.loads.query.add_field.php';
}

function add_field(new_field_id) {
	with(document.all.tbl) {
		new_row=insertRow(current_row_index+1);
		new_row.setAttribute("onmouseover", "sel_row(this)");
		new_row.setAttribute("onmouseout", "unsel_row(this)");
		with(new_row) {
			with(appendChild(document.createElement('th'))) {
				setAttribute("ondblclick","del_field(this)");
				setAttribute("title","Удалить (двойной щелчок)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onclick","current_row_index=this.parentNode.rowIndex;  parent.logFrame.location='adm.loads.query.add_field.php'");
				setAttribute("title","Добавить ниже");
				style.cursor='pointer';
				innerHTML="<img src='png/plus.png'></img>";			
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				setAttribute("onclick","click_row(this)");
				style.cursor='s-resize';
				innerHTML="<input type=hidden name=base_fields_id[] value='"+new_field_id+"'>"+new_field_id;				
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input style='width:100%' type=text name=base_fields_text_name["+new_field_id+"] value='Новое поле-"+new_field_id+"'>";				
			}		
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input style='width:100%' type=text name=base_fields_code_name["+new_field_id+"] value='F"+new_field_id+"'>";				
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				with(appendChild(document.createElement('select'))) {
					name="base_fields_std_name["+new_field_id+"]";
					style='width:100%';
					appendChild(document.createElement('option'));
					for(i=0; i<std_field_name.length; i++){
						opt=appendChild(document.createElement('option'));
						opt.value=std_field_name[i];
						opt.innerText=std_field_desc[i]+' ('+std_field_name[i]+')';
			}}}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input type=checkbox name=base_fields_uniq["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input type=checkbox name=base_fields_must["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input type=checkbox name=base_fields_quoted["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input type=checkbox name=base_fields_idx["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				innerHTML="<input type=checkbox name=base_fields_ank_show["+new_field_id+"]>";				
		}	
		click_row(cells[0]);
}	}	}
function notsaved() {
	document.getElementById('save_status').innerHTML='<font color=red>Не забудьте сохранить изменения!</font>';
}
function del_field(obj) {
	with(obj.parentNode.parentNode) { //таблица
		deleteRow(obj.parentNode.rowIndex);
}	}
function del_old_field(field_id) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_field['+field_id+']';
	inp.value=field_id;
	document.frm.appendChild(inp);
}