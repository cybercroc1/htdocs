//—ќ–“»–ќ¬ ј---------------------------------
function show_all_orders() { //отображеиние всех значков сортировки, в соответствии с настройками
	row=document.getElementById('table_head'); //строка с дивами
	for(i=0;i<row.cells.length;i++) { //перебор €чеек строки с заголовками
		cell=row.cells[i];
		elem=get_order_elem_by_cell(cell);
		if(elem) {
			set_order(elem);
		}
	}
}
function get_order_elem_by_cell(cell) { //получение ссылки на див с параметрами сотрировки дл€ заданной €чейки
	for(j=0;j<cell.childNodes.length;j++) { //перебор дочерних эелементов €чейки
		elem=cell.childNodes[j];
		if(elem.tagName=='DIV') {
			if(elem.childNodes[1]) { //второй элемент в €чейке - это див с отображением значка сортировки
				return elem.childNodes[1];
			}
		}
	}
	return false;
}
function ch_order(obj) { //нажатие на значек сортировки
	cur_ord=obj.getAttribute('order_type');
	cur_ord_num=obj.getAttribute('order_num');
	if(cur_ord=='none') { //с отсутстви€ сортировки к сортировке по возрастанию
		obj.setAttribute('order_type','asc');
		ch_ord_num='to_first';
	}
	else
	if(cur_ord=='asc') { //с возрастани€ к убыванию
		obj.setAttribute('order_type','desc');
		ch_ord_num='to_first';
	}
	else
	if(cur_ord=='desc') { //с убывани€ к отсутствию сортировки
		obj.setAttribute('order_type','none');
		ch_ord_num='cancel';
	}
	row=document.getElementById('table_head'); //строка с дивами
	for(i=0;i<row.cells.length;i++) {
		cell=row.cells[i];
		elem=get_order_elem_by_cell(cell);
		if(elem) {
			if(elem.getAttribute('order_type')!='none') {
				tmp_ord_num=elem.getAttribute('order_num');
				if(ch_ord_num=='to_first') {
					if((cur_ord_num>1 && tmp_ord_num<cur_ord_num) || (cur_ord_num=='')) {
						new_ord_num=Number(tmp_ord_num)+1;
						elem.setAttribute('order_num',new_ord_num);
						set_order(elem);
					}
				}
				else if(ch_ord_num=='cancel') {
					if(tmp_ord_num>cur_ord_num) {
						new_ord_num=Number(tmp_ord_num)-1;
						elem.setAttribute('order_num',new_ord_num);
						set_order(elem);
					}
				}				
				
			}			
		}
	}
	if(ch_ord_num=='to_first') obj.setAttribute('order_num',1);
	if(ch_ord_num=='cancel') obj.setAttribute('order_num','');
	set_order(obj);
	ch_filter();
}
function set_order(obj) { //установка настроек сортировки дл€ выбранного пол€
	order_type=obj.getAttribute('order_type');
	order_num=obj.getAttribute('order_num');
	field_name=obj.getAttribute('field_name');
	if(order_type=='none') { //с отсутстви€ сортировки к сортировке по возрастанию
		obj.innerHTML=" &#9899; <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"none\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //кружок
	}
	else
	if(order_type=='asc') { //с возрастани€ к убыванию
		obj.innerHTML=" &#9650;"+order_num+" <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"asc\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //стрелка вверх
	}
	else
	if(order_type=='desc') { //с убывани€ к отсутствию сортировки
		obj.innerHTML=" &#9660;"+order_num+" <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"desc\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //стрелка вниз
	}	
}
//------------------—ќ–“»–ќ¬