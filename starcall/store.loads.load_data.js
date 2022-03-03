var src_row_index='x';
var dst_row_index='x';

var std_field_name = new Array();
var std_field_desc = new Array();

var current_row_index='';
var ctrl_pressed=false;

document.body.onselectstart=function() {
	if (src_row_index!='x') return false;
}
function change() {
	if (document.all.new_file.value=="") {
		document.all.preview.disabled=true;
	}
	else {
		document.all.preview.disabled=false;
	}
}
function fMD(obj) { //нажатие кнопки мыши
	src_row_index=obj.parentNode.rowIndex;
	return false;
}
function fMU(obj) { //отпускание кнопки мыши
	dst_row_index=obj.parentNode.rowIndex;
	if(src_row_index!='x' && src_row_index!=dst_row_index) {
		//двигаем строку целиком
		if(event.ctrlKey) {fMovAllRow(src_row_index,dst_row_index);}
		//двигаем поле файла
		else if(obj.cellIndex==3) {fMovFileRow(src_row_index,dst_row_index);}
		//двигаем поле БД
		else if(obj.cellIndex==5) {fMovBaseRow(src_row_index,dst_row_index);}
	}
	src_row_index='x';
}
function fMovBaseRow(from,to) {
	with(document.all.tbl) {
		//двигаем ячейку с минусом
		rows[to].insertBefore(rows[from].cells[0],rows[to].cells[0]);
		rows[from].insertBefore(rows[to].cells[1],rows[from].cells[0]);
		//двигаем все остальные
		for(i=4; i<=12; i++) { //кол-во циклов равно кол-ву столбцов с конца
			rows[src_row_index].appendChild(rows[dst_row_index].cells[4]);
			rows[dst_row_index].appendChild(rows[src_row_index].cells[4]);
}}}
function fMovFileRow(from,to) {
	with(document.all.tbl) {
		//двигаем ячейки с номером и именем
		rows[to].insertBefore(rows[from].cells[2],rows[to].cells[2]);
		rows[from].insertBefore(rows[to].cells[3],rows[from].cells[2]);
		rows[to].insertBefore(rows[from].cells[3],rows[to].cells[3]);
		rows[from].insertBefore(rows[to].cells[4],rows[from].cells[3]);
}}
function fMovAllRow(from,to) { 
	with(document.all.tbl) {
		//вверх
		for(i=from; i>to; i--) {
			src_cells=rows[i].cells.length;
			dst_cells=rows[i-1].cells.length;
			for(j=1; j<=13; j++) { //кол-во циклов равно кол-ву столбцов
				if(j<=src_cells) rows[i-1].appendChild(rows[i].cells[0]);
				if(j<=dst_cells) rows[i].appendChild(rows[i-1].cells[0]);
		}}
		//вниз
		for(i=from; i<to; i++) {
			src_cells=rows[i].cells.length;
			dst_cells=rows[i+1].cells.length;
			for(j=1; j<=13; j++) { //кол-во циклов равно кол-ву столбцов
				if(j<=src_cells) rows[i+1].appendChild(rows[i].cells[0]);
				if(j<=dst_cells) rows[i].appendChild(rows[i+1].cells[0]);
	}}}
	src_row_index='x';
}
function fCancelLoad(load_id,abort_pwd) {
	//alert('store.loads.cancel_load.php?load_id='+load_id+'&abort_pwd='+abort_pwd);
	document.all.hidden_frame.src='store.loads.cancel_load.php?load_id='+load_id+'&abort_pwd='+abort_pwd;
	//frm_preview.load.disabled=false;
	//frm_preview.load.value='Загрузить';
	//frm_preview.load_caption.value='';
	//frm_preview.cancel_load.style.display='none';
	//document.getElementById('load_status').innerHTML='';
}
function start_load() {
	frm_preview.cancel_load.style.display='';
	frm_preview.load.disabled=true
	frm_preview.submit();
	document.getElementById('load_status').innerHTML='<font color=black><b>Идет загрузка...</b></font>';
}

function plus_field(obj) {
	ctrl_pressed=event.ctrlKey
	current_row_index=obj.parentNode.rowIndex; 
	parent.logFrame.location='store.loads.query.add_field.php';
}

function add_field(new_field_id) {
	with(document.all.tbl) {
		new_row=insertRow(current_row_index+1);
		//new_row.setAttribute("onmouseover", "sel_row(this)");
		//new_row.setAttribute("onmouseout", "unsel_row(this)");
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
				setAttribute("onclick","plus_field(this)");
				setAttribute("title","Добавить строку внизу. CTRL-добавить с копированием");
				style.cursor='pointer';
				innerHTML="<img src='png/plus.png'></img>";			
			}	
			
			appendChild(document.createElement('th')); //номер в файле	
			
			with(appendChild(document.createElement('td'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				setAttribute("title","CTRL-двигать строку целиком");
				style.cursor='s-resize';
				new_innerHTML="<input type=hidden name=file_fields_num[] value=''><input type=hidden name=file_fields_name[] value=''>";
				if(ctrl_pressed) {
					innerHTML = parentNode.parentNode.rows[new_row.rowIndex-1].cells[cellIndex].innerHTML;
					parentNode.parentNode.rows[new_row.rowIndex-1].cells[cellIndex].innerHTML = new_innerHTML;
					//перемещаем номер
					parentNode.cells[cellIndex-1].innerHTML = parentNode.parentNode.rows[new_row.rowIndex-1].cells[cellIndex-1].innerHTML;
					parentNode.parentNode.rows[new_row.rowIndex-1].cells[cellIndex-1].innerHTML = '';
				}	
				else innerHTML = new_innerHTML;
			}
			
			appendChild(document.createElement('th')); //номер в БД							
			
			with(appendChild(document.createElement('th'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				setAttribute("title","CTRL-двигать строку целиком");
				style.cursor='s-resize';
				innerHTML="<input type=hidden name=new_field["+new_field_id+"]><input type=hidden name=base_fields_id[] value='"+new_field_id+"'>"+new_field_id;
			}
			
			
			with(appendChild(document.createElement('th'))) {
				if(ctrl_pressed) {
					innerHTML="<input type=text style='width:100%' name=base_fields_text_name["+new_field_id+"] value='"+parentNode.cells[3].innerText+"'>";
				}
				else innerHTML="<input type=text style='width:100%' name=base_fields_text_name["+new_field_id+"] value='Новое поле-"+new_field_id+"'>";				
			}		
			with(appendChild(document.createElement('td'))) {
				innerHTML="<input type=text style='width:100%' name=base_fields_code_name["+new_field_id+"] value='F"+new_field_id+"'>";				
			}
			with(appendChild(document.createElement('td'))) {
				with(appendChild(document.createElement('select'))) {
					name="base_fields_std_name["+new_field_id+"]";
					setAttribute('onchange','ch_std_field('+new_field_id+')');
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
		click_row(new_row,'tog');
		}
	}	
}

function del_field(obj) {
	with(obj.parentNode.parentNode) { //таблица
		deleteRow(obj.parentNode.rowIndex);
}}
function del_file_field(obj) {
	with(obj.parentNode) { //строка
		cells[2].innerHTML="";
		cells[3].innerHTML="<input type=hidden name=file_fields_num[] value=''><input type=hidden name=file_fields_name[] value=''>";
}}
function ch_std_field(idx) {
	if(document.all['base_fields_std_name['+idx+']'].value=='PHONE') {
		document.all['base_fields_uniq['+idx+']'].checked=true;	
		document.all['base_fields_must['+idx+']'].checked=true;
	}
	else {
		document.all['base_fields_uniq['+idx+']'].checked=false;	
		document.all['base_fields_must['+idx+']'].checked=false;	
	}
}