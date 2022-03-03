var src_row_index='x';
var dst_row_index='x';

var std_field_name = new Array();
var std_field_desc = new Array();

document.body.onselectstart=function() {
	if (src_row_index!='x') return false;
}
function fMD(obj) { //нажатие кнопки мыши
	src_row_index=obj.parentNode.rowIndex;
	return false;
}
function fMU(obj) { //отпускание кнопки мыши
	dst_row_index=obj.parentNode.rowIndex;
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
	notsaved();
		with(document.all.tbl) {
			//перенос со сдвигом
			//вверх
			for(i=src_row_index; i>dst_row_index; i--) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i-1].cells.length;
				for(j=1; j<=rows[0].cells.length; j++) { //кол-во циклов равно кол-ву столбцов
					if(j<=src_cells) rows[i-1].appendChild(rows[i].cells[0]);
					if(j<=dst_cells) rows[i].appendChild(rows[i-1].cells[0]);
			}}
			//вниз
			for(i=src_row_index; i<dst_row_index; i++) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i+1].cells.length;
				for(j=1; j<=rows[0].cells.length; j++) { //кол-во циклов равно кол-ву столбцов
					if(j<=src_cells) rows[i+1].appendChild(rows[i].cells[0]);
					if(j<=dst_cells) rows[i].appendChild(rows[i+1].cells[0]);
	}}}}
	src_row_index='x';
}

function click_edit(cell) {
	click_row(cell);
	parent.admAnkEditSecondFrame.location='adm.ank.list.edit.php?obj_id='+cell.parentNode.getAttribute('data-object_id');
}
function add_obj (obj) {
//notsaved();
	new_idx++;
	with(obj.parentNode.parentNode) { //таблица
		with(insertRow(obj.parentNode.rowIndex+1)) {//строка
			setAttribute("onmouseover","sel_row(this)");
			setAttribute("onmouseout","unsel_row(this)");
			with(appendChild(document.createElement('th'))) {
				setAttribute("ondblclick","del_obj(this)");
				setAttribute("title","Удалить (двойной щелчок)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onclick","add_obj(this)");
				setAttribute("title","Добавить ниже");
				style.cursor='pointer';
				innerHTML="<input type=hidden name='obj_id["+new_idx+"]' value='new'><input type=hidden name='type["+new_idx+"]'><img src='png/plus.png'></img>";
			}
			
			//appendChild(document.createElement('th'));
			
			with(appendChild(document.createElement('td'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				setAttribute("onclick","click_row(this)");
				style.cursor='s-resize';
				//innerHTML="<input type=checkbox name='newpage["+new_idx+"]'>Нов.стр.</input>";
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				with(appendChild(document.createElement('select'))) {
					name="obj_type_id["+new_idx+"]";
					id=new_idx;
					style.width='100%';
					setAttribute("onchange","change_type(this)");
					appendChild(document.createElement('option'));
					opt=appendChild(document.createElement('option')); opt.value='page'; opt.innerText='Новая страница';
					opt=appendChild(document.createElement('option')); opt.value='group'; opt.innerText='Новая группа объектов';
					opt=appendChild(document.createElement('option')); opt.value='message'; opt.innerText='Сообщение';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_text'; opt.innerText='Вопрос - Текст';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_bigtext'; opt.innerText='Вопрос - Большой текст';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_select'; opt.innerText='Вопрос - Выбор';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_radio'; opt.innerText='Вопрос - Радио';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_multi'; opt.innerText='Вопрос - Множ. выбор';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_checkbox'; opt.innerText='Вопрос - Галочки';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_integer'; opt.innerText='Вопрос - Число';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_date'; opt.innerText='Вопрос - Дата';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_time'; opt.innerText='Вопрос - Время';
					opt=appendChild(document.createElement('option')); opt.value='end_norm'; opt.innerText='Конец УСПЕШНЫЙ';
					opt=appendChild(document.createElement('option')); opt.value='end_false'; opt.innerText='Конец НЕЦЕЛЕВОЙ';
				}
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("colspan",6);
				setAttribute("onclick","click_row(this)");
				innerHTML='</textarea><textarea style="width:100%;display:none" name=message['+new_idx+'] onchange="notsaved()"></textarea>';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='<input type=text style="width:100%;display:none" name=text_name['+new_idx+'] onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='<input type=text style="width:100%;display:none" name=code_name['+new_idx+'] onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='об<input type=checkbox style="display:none" name=must['+new_idx+'] checked onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='кв<input type=checkbox style="display:none" name=quoted['+new_idx+'] onchange="notsaved()">';
			}				
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='<select style="width:100%;display:none" name=order_type['+new_idx+'] onchange="notsaved()"><option>По порядку</option><option>Случайно</option></select>';
			}
		click_row(cells[0]);
		}
	}	
//alert(frm['obj_id['+new_idx+']']);
}
function del_obj(obj) {
	notsaved();
	with(obj.parentNode.parentNode) { //таблица
		deleteRow(obj.parentNode.rowIndex);
	}
}

function del_old_page(page_id,grp_id) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_page['+page_id+']';
	inp.value=page_id;
	document.frm.appendChild(inp);
	//удаляем и группу
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_grp['+grp_id+']';
	inp.value=grp_id;
	document.frm.appendChild(inp);
}
function del_old_grp(grp_id) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_grp['+grp_id+']';
	inp.value=grp_id;
	document.frm.appendChild(inp);
}
function del_old_obj(obj_id) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_obj['+obj_id+']';
	inp.value=obj_id;
	document.frm.appendChild(inp);
}

function change_type(obj) {
	
notsaved();
	with(obj.parentNode.parentNode) {//родительская строка
	if(obj.value=='page') { //новая страница
	setAttribute('data-type','page');//пользовательский атрибут строке
		cells[2].innerHTML=""; //С.Г.О (id)
		cells[4].setAttribute("colspan",5); //сообщение
		cells[5].style.display='none'; //имя
		cells[6].style.display='none'; //кодовое имя
		cells[7].style.display='none'; //обяз
		cells[8].style.display='none'; //квота
		cells[9].style.display=''; //ротация
		frm['type['+obj.id+']'].value='page';
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='none';
		frm['code_name['+obj.id+']'].style.display='none';
		frm['must['+obj.id+']'].style.display='none';
		frm['quoted['+obj.id+']'].style.display='none';
		frm['order_type['+obj.id+']'].style.display='';
	}
	else if(obj.value=='group') { //новая группа
	setAttribute('data-type','group');
		cells[2].innerHTML=""; //С.Г.О (id)
		cells[4].setAttribute("colspan",5);
		cells[5].style.display='none';
		cells[6].style.display='none';
		cells[7].style.display='none';
		cells[8].style.display='none';
		cells[9].style.display='';	
		frm['type['+obj.id+']'].value='group';		
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='none';
		frm['code_name['+obj.id+']'].style.display='none';
		frm['must['+obj.id+']'].style.display='none';
		frm['quoted['+obj.id+']'].style.display='none';
		frm['order_type['+obj.id+']'].style.display='';
	}
	else if(obj.value.substr(0,5)=='q_sn_') { //вопросы одиночные
	setAttribute('data-type','object');
		cells[2].innerHTML="1Нов.стр.<input type=checkbox name='newpage["+obj.id+"]'>";  //С.Г.О (id)
		cells[4].setAttribute("colspan",1);
		cells[5].style.display='';
		cells[6].style.display='';
		cells[7].style.display='';
		cells[8].style.display='';	
		cells[9].style.display='';		
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //если отмечена галка скрывать страницы, но нов.стр ставим по умолчанию
		frm['type['+obj.id+']'].value='obj';		
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='';
		frm['code_name['+obj.id+']'].style.display='';
		frm['must['+obj.id+']'].style.display='';
		frm['quoted['+obj.id+']'].style.display='none';		
		frm['order_type['+obj.id+']'].style.display='none';
	}
	else if(obj.value.substr(0,5)=='q_ls_') { //вопросы с выбором
	setAttribute('data-type','object');	
		cells[2].innerHTML="2Нов.стр.<input type=checkbox name='newpage["+obj.id+"]'>";  //С.Г.О (id)
		cells[4].setAttribute("colspan",1);
		cells[5].style.display='';
		cells[6].style.display='';
		cells[7].style.display='';
		cells[8].style.display='';
		cells[9].style.display='';
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //если отмечена галка скрывать страницы, но нов.стр ставим по умолчанию
		frm['type['+obj.id+']'].value='obj';
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='';
		frm['code_name['+obj.id+']'].style.display='';
		frm['must['+obj.id+']'].style.display='';
		frm['quoted['+obj.id+']'].style.display='';
		frm['order_type['+obj.id+']'].style.display='';
	}	
	else { //все остальное
	setAttribute('data-type','object');
		cells[2].innerHTML="3Нов.стр.<input type=checkbox name='newpage["+obj.id+"]'>";  //С.Г.О (id)
		cells[4].setAttribute("colspan",6);
		cells[5].style.display='none';
		cells[6].style.display='none';
		cells[7].style.display='none';
		cells[8].style.display='none';
		cells[9].style.display='none';
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //если отмечена галка скрывать страницы, но нов.стр ставим по умолчанию
		frm['type['+obj.id+']'].value='obj';
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='none';
		frm['code_name['+obj.id+']'].style.display='none';
		frm['must['+obj.id+']'].style.display='none';
		frm['quoted['+obj.id+']'].style.display='none';
		frm['order_type['+obj.id+']'].style.display='none';
	}
	}
}
function notsaved() {
	document.getElementById('save_status').innerHTML='<font color=red>Не забудьте сохранить изменения!</font>';
	frm.logic_test.style.display='none';
}
function fHidePages() {
	with(document.getElementById('tbl')) { //таблица
		for(i=1; i<=rows.length-1; i++) {
			if(rows[i].getAttribute('data-type')=='page' || rows[i].getAttribute('data-type')=='group') rows[i].style.display='none';
		}
	}
}
function fShowPages() {
	with(document.getElementById('tbl')) { //таблица
		for(i=1; i<=rows.length-1; i++) {
			if(rows[i].getAttribute('data-type')=='page' || rows[i].getAttribute('data-type')=='group') rows[i].style.display='';
		}
	}
}
/*function edit_obj(obj_id,obj_type) {
	//document.body.style.overflow = 'hidden'; //запрет прокрутки документа
	if(document.all.popUpFrame.style.display=='none') {
		topOffset=240; //смещение окна вверх от позиции курсора
		document.all.popUpFrame.style.left=event.clientX+'px';
		if(event.clientY<topOffset) {
			document.all.popUpFrame.style.top='10px';	
		}
		else {
			document.all.popUpFrame.style.top=event.clientY-topOffset+'px';
		}
		document.all.popUpFrame.style.display='';
		document.all.popUpFrame.src='adm.ank.edit.obj.edit.php?obj_id='+obj_id+'&obj_type='+obj_type;
		//блокируем все элементы формы
		with(frm) {
			for(i=0; i<elements.length; i++) {
				elements[i].disabled=true;
			}
		}
	}
	else {
		document.all.popUpFrame.style.display='none';
	}
}
*/