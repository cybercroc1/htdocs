var src_row_index='x';
var dst_row_index='x';
var ctrl_pressed=false;
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
function sel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.toggle('selected_row');
		
}}
function unsel_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.remove('selected_row');
}}
function plus(obj) {
	ctrl_pressed=event.ctrlKey
	if(ctrl_pressed) {
		fPaste(obj);
	}
	else {
		add_val(obj,'','','','');
}}
function fPaste(obj) {
	//т.к. во всех браузерах, кроме ИЕ перехват буфера запрещен, то вставка происходит через окошко обмена
	//если окошко обмена не пустое, то вставляем из него
	if(document.getElementById('buffer').value!='буфер обмена') {vClipBoard=document.getElementById('buffer').value+'\n';}
	//если пустое, то пробуем вставить из буфера (если ИЕ)
	else {vClipBoard=clipboardData.getData('Text')+'\n';}
	
	vRows=vClipBoard.split(/\n|\r\n/);
	n=0;
	for(m=0; m<vRows.length-1; m++) {
		vVals=vRows[m].replace('	',' 	 ').split(/	/);
		if(!vVals[0]) {vVals[0]='';}
		if(!vVals[1]) {vVals[1]='';}
		if(!vVals[2]) {vVals[2]='';}
		if(!vVals[3]) {vVals[3]='';}
		//если убрать эту строчку данные вставятся с обратиной сортировкой
		//if(m>0) {obj=obj.parentNode.parentNode.rows[obj.parentNode.rowIndex+1].cells[obj.cellIndex];
		if(vVals[0]+vVals[1]+vVals[2]+vVals[3]!='')	{
			//если убрать эту строчку данные вставятся с обратиной сортировкой
			if(n>0) obj=obj.parentNode.parentNode.rows[obj.parentNode.rowIndex+1].cells[obj.cellIndex];
			add_val(obj,vVals[0],vVals[1],vVals[2],vVals[3]);
			n++;
		}
	}
	//если что-нибудь добавлено
	if(m>0) {
		//очищаем ококшко обмена
		document.getElementById('buffer').value='буфер обмена';
		//напоминаем сохранить
		notsaved();
}}
function add_val(obj,text_value,code_name,quote_key,foreign_key) {
//notsaved();
	new_idx++;
	with(obj.parentNode.parentNode) { //таблица
		with(insertRow(obj.parentNode.rowIndex+1)) {//строка
			setAttribute("onmouseover","sel_row(this)");
			setAttribute("onmouseout","unsel_row(this)");
			
			with(appendChild(document.createElement('th'))) {
				setAttribute("ondblclick","del_val(this)");
				setAttribute("title","Удалить (двойной щелчок)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onclick","plus(this)");
				setAttribute("title","Добавить ниже. CTRL - вставить из буфера обмена (IE), остальные браузеры - из окошка обмена");
				style.cursor='pointer';
				innerHTML="<input type=hidden name='val_id["+new_idx+"]' value='new'><img src='png/plus.png'></img>";
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				style.cursor='s-resize';
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML='<input type=text style="width:100%" name=text_value['+new_idx+'] value="'+text_value+'" onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML='<input type=text style="width:100%" name=code_value['+new_idx+'] value="'+code_name+'" onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML='<input type=text style="width:100%" name=quote_key['+new_idx+'] value="'+quote_key+'" onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				innerHTML='<input type=text style="width:100%" name=foreign_key['+new_idx+'] value="'+foreign_key+'" onchange="notsaved()">';
			}			
			with(appendChild(document.createElement('td'))) {
				setAttribute("align","center");
				innerHTML='<input type=text size=1 name=other_count['+new_idx+'] onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("align","center");
				innerHTML='<input type=checkbox name=always_bottom['+new_idx+'] onchange="notsaved()">';	
}}}}
function notsaved() {
	document.getElementById('save_status').innerHTML='<font color=red>Не забудьте сохранить изменения!</font>';
}
function del_val(obj) {
	with(obj.parentNode.parentNode) { //таблица
		deleteRow(obj.parentNode.rowIndex);
	}
}
function del_old_val(val_id) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_val['+val_id+']';
	inp.value=val_id;
	document.frm.appendChild(inp);
}