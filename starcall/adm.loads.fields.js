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
function fMD(cell) { //нажатие кнопки мыши
	src_row_index=cell.parentNode.rowIndex;
	click_row(cell.parentNode,'sel');
	return false;
}
//перенос строк целиком
//со сдвигом
function fMU(cell) { //отпускание кнопки мыши
	dst_row_index=cell.parentNode.rowIndex;
	//click_row(obj.parentNode,'sel');
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
		notsaved();
		src_row=cell.parentNode.parentNode.rows[src_row_index];
		//вверх
		if(dst_row_index<src_row_index) {
			dst_row=cell.parentNode.parentNode.rows[dst_row_index];
		}
		//вниз
		if(dst_row_index>src_row_index) {
			if(dst_row_index>cell.parentNode.parentNode.rows.length) dst_row=null;
			else dst_row=cell.parentNode.parentNode.rows[dst_row_index+1];
		}
		//глупая функция insertBefore не работает если родителя указывать через переменную, поэтому только так:
		src_row.parentNode.insertBefore(src_row,dst_row);
		src_row_index='x';
	}
}
/*
//перестановка строк местами
function fMU(cell) { //отпускание кнопки мыши
	dst_row_index=cell.parentNode.rowIndex;
	click_row(cell.parentNode,'add');
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
		notsaved();
		//вверх
		if(dst_row_index<src_row_index) {
			src_row=cell.parentNode.parentNode.rows[src_row_index];
			dst_row=cell.parentNode.parentNode.rows[dst_row_index];
			//глупая функция insertBefore не работает если родителя указывать через переменную, поэтому только так:
			src_row.parentNode.insertBefore(src_row,dst_row);
			
			src_row=cell.parentNode.parentNode.rows[dst_row_index+1];
			dst_row=cell.parentNode.parentNode.rows[src_row_index+1];
			
			src_row.parentNode.insertBefore(src_row,dst_row);
	}
		//вниз
		if(dst_row_index>src_row_index) {
			src_row=cell.parentNode.parentNode.rows[src_row_index];
			if(dst_row_index>cell.parentNode.parentNode.rows.length) dst_row=null;
			else dst_row=cell.parentNode.parentNode.rows[dst_row_index+1];
			//глупая функция insertBefore не работает если родителя указывать через переменную, поэтому только так:
			src_row.parentNode.insertBefore(src_row,dst_row);

			src_row=cell.parentNode.parentNode.rows[dst_row_index-1];
			dst_row=cell.parentNode.parentNode.rows[src_row_index];
			
			src_row.parentNode.insertBefore(src_row,dst_row);
		}
		src_row_index='x';
	}
}
*/
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
		with(new_row) {
			setAttribute('class','selectable_row');
			setAttribute("onclick","click_row(this,'tog')");
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
				style.cursor='s-resize';
				innerHTML="<input type=hidden name=base_fields_id[] value='"+new_field_id+"'>"+new_field_id;				
			}
			with(appendChild(document.createElement('th'))) {
				innerHTML="<input style='width:100%' type=text name=base_fields_text_name["+new_field_id+"] value='Новое поле-"+new_field_id+"'>";				
			}		
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input style='width:100%' type=text name=base_fields_code_name["+new_field_id+"] value='F"+new_field_id+"'>";				
			}
			with(appendChild(document.createElement('td'))) {
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
				innerHTML="<input type=checkbox name=base_fields_uniq["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input type=checkbox name=base_fields_must["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input type=checkbox name=base_fields_quoted["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input type=checkbox name=base_fields_idx["+new_field_id+"]>";				
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input type=checkbox name=base_fields_ank_show["+new_field_id+"]>";				
		}	
		click_row(new_row,'tog');
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