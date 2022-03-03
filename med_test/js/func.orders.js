//����������---------------------------------
function show_all_orders() { //������������ ���� ������� ����������, � ������������ � �����������
	row=document.getElementById('table_head'); //������ � ������
	for(i=0;i<row.cells.length;i++) { //������� ����� ������ � �����������
		cell=row.cells[i];
		elem=get_order_elem_by_cell(cell);
		if(elem) {
			set_order(elem);
		}
	}
}
function get_order_elem_by_cell(cell) { //��������� ������ �� ��� � ����������� ���������� ��� �������� ������
	for(j=0;j<cell.childNodes.length;j++) { //������� �������� ���������� ������
		elem=cell.childNodes[j];
		if(elem.tagName=='DIV') {
			if(elem.childNodes[1]) { //������ ������� � ������ - ��� ��� � ������������ ������ ����������
				return elem.childNodes[1];
			}
		}
	}
	return false;
}
function ch_order(obj) { //������� �� ������ ����������
	cur_ord=obj.getAttribute('order_type');
	cur_ord_num=obj.getAttribute('order_num');
	if(cur_ord=='none') { //� ���������� ���������� � ���������� �� �����������
		obj.setAttribute('order_type','asc');
		ch_ord_num='to_first';
	}
	else
	if(cur_ord=='asc') { //� ����������� � ��������
		obj.setAttribute('order_type','desc');
		ch_ord_num='to_first';
	}
	else
	if(cur_ord=='desc') { //� �������� � ���������� ����������
		obj.setAttribute('order_type','none');
		ch_ord_num='cancel';
	}
	row=document.getElementById('table_head'); //������ � ������
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
function set_order(obj) { //��������� �������� ���������� ��� ���������� ����
	order_type=obj.getAttribute('order_type');
	order_num=obj.getAttribute('order_num');
	field_name=obj.getAttribute('field_name');
	if(order_type=='none') { //� ���������� ���������� � ���������� �� �����������
		obj.innerHTML=" &#9899; <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"none\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //������
	}
	else
	if(order_type=='asc') { //� ����������� � ��������
		obj.innerHTML=" &#9650;"+order_num+" <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"asc\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //������� �����
	}
	else
	if(order_type=='desc') { //� �������� � ���������� ����������
		obj.innerHTML=" &#9660;"+order_num+" <input type='hidden' name=\"orders_selected["+field_name+"][type]\" value=\"desc\" /><input type='hidden' name=\"orders_selected["+field_name+"][num]\" value=\""+order_num+"\" />"; //������� ����
	}	
}
//------------------��������