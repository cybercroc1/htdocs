function select_group(cell) {
	row=cell.parentNode;
	click_row(row,'sel');
	frm_select_group.group_id.value=row.getAttribute('data-group_id');
	frm_select_group.submit();	
}
function del_new_group(cell) {
	cell.parentNode.parentNode.deleteRow(cell.parentNode.rowIndex);
}
function del_old_group(cell) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_group['+cell.parentNode.getAttribute('data-group_id')+']';
	inp.value=cell.parentNode.getAttribute('data-group_id');
	document.frm.appendChild(inp);
	cell.parentNode.parentNode.deleteRow(cell.parentNode.rowIndex);
}
function add_group(cell) {
	new_idx++;
	notsaved();
	with(cell.parentNode.parentNode) { //таблица
		new_row=insertRow(2);
		with(new_row) {//строка
			//setAttribute("onmouseover","sel_row(this)");
			//setAttribute("onmouseout","unsel_row(this)");
			//setAttribute("onclick","click_row(this,'tog')");
			setAttribute('class','selectable_row');
			with(appendChild(document.createElement('th'))) {
				setAttribute("ondblclick","del_new_group(this)");
				setAttribute("title","Удалить группу (двойной щелчок)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","select_group(this)");
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","select_group(this)");
				innerHTML='<input type=hidden name="new_group['+new_idx+']"><input type=text style="width:100%" name="new_name['+new_idx+']">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","select_group(this)");
				innerHTML='<select name=new_default['+new_idx+']><option value="">Нет</option><option value="y">Да</option></select>';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","select_group(this)");
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","select_group(this)");
			}
		click_row(new_row,'add');
		}
	}	
}
function ch_group(group_id) {
	with(frm) {
		with(appendChild(document.createElement('input'))) {
			setAttribute("type","hidden");
			name="ch_group["+group_id+"]";
		}
	}
}
function notsaved() {
	document.getElementById('save_status').innerHTML='<font color=red>Не забудьте сохранить изменения!</font>';
	frm.cancel.style.display='';
}
