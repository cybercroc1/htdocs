var src_row_index='x';
var dst_row_index='x';

var std_field_name = new Array();
var std_field_desc = new Array();

document.body.onselectstart=function() {
	if (src_row_index!='x') return false;
}
function fMD(obj) { //������� ������ ����
	src_row_index=obj.parentNode.rowIndex;
	return false;
}
function fMU(obj) { //���������� ������ ����
	dst_row_index=obj.parentNode.rowIndex;
	if(src_row_index!='x' & src_row_index!=dst_row_index) {
	notsaved();
		with(document.all.tbl) {
			//������� �� �������
			//�����
			for(i=src_row_index; i>dst_row_index; i--) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i-1].cells.length;
				for(j=1; j<=rows[0].cells.length; j++) { //���-�� ������ ����� ���-�� ��������
					if(j<=src_cells) rows[i-1].appendChild(rows[i].cells[0]);
					if(j<=dst_cells) rows[i].appendChild(rows[i-1].cells[0]);
			}}
			//����
			for(i=src_row_index; i<dst_row_index; i++) {
				src_cells=rows[i].cells.length;
				dst_cells=rows[i+1].cells.length;
				for(j=1; j<=rows[0].cells.length; j++) { //���-�� ������ ����� ���-�� ��������
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
	with(obj.parentNode.parentNode) { //�������
		with(insertRow(obj.parentNode.rowIndex+1)) {//������
			setAttribute("onmouseover","sel_row(this)");
			setAttribute("onmouseout","unsel_row(this)");
			with(appendChild(document.createElement('th'))) {
				setAttribute("ondblclick","del_obj(this)");
				setAttribute("title","������� (������� ������)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('th'))) {
				setAttribute("onclick","add_obj(this)");
				setAttribute("title","�������� ����");
				style.cursor='pointer';
				innerHTML="<input type=hidden name='obj_id["+new_idx+"]' value='new'><input type=hidden name='type["+new_idx+"]'><img src='png/plus.png'></img>";
			}
			
			//appendChild(document.createElement('th'));
			
			with(appendChild(document.createElement('td'))) {
				setAttribute("onmousedown","fMD(this)");
				setAttribute("onmouseup","fMU(this)");
				setAttribute("onclick","click_row(this)");
				style.cursor='s-resize';
				//innerHTML="<input type=checkbox name='newpage["+new_idx+"]'>���.���.</input>";
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				with(appendChild(document.createElement('select'))) {
					name="obj_type_id["+new_idx+"]";
					id=new_idx;
					style.width='100%';
					setAttribute("onchange","change_type(this)");
					appendChild(document.createElement('option'));
					opt=appendChild(document.createElement('option')); opt.value='page'; opt.innerText='����� ��������';
					opt=appendChild(document.createElement('option')); opt.value='group'; opt.innerText='����� ������ ��������';
					opt=appendChild(document.createElement('option')); opt.value='message'; opt.innerText='���������';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_text'; opt.innerText='������ - �����';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_bigtext'; opt.innerText='������ - ������� �����';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_select'; opt.innerText='������ - �����';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_radio'; opt.innerText='������ - �����';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_multi'; opt.innerText='������ - ����. �����';
					opt=appendChild(document.createElement('option')); opt.value='q_ls_checkbox'; opt.innerText='������ - �������';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_integer'; opt.innerText='������ - �����';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_date'; opt.innerText='������ - ����';
					opt=appendChild(document.createElement('option')); opt.value='q_sn_time'; opt.innerText='������ - �����';
					opt=appendChild(document.createElement('option')); opt.value='end_norm'; opt.innerText='����� ��������';
					opt=appendChild(document.createElement('option')); opt.value='end_false'; opt.innerText='����� ���������';
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
				innerHTML='��<input type=checkbox style="display:none" name=must['+new_idx+'] checked onchange="notsaved()">';
			}
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='��<input type=checkbox style="display:none" name=quoted['+new_idx+'] onchange="notsaved()">';
			}				
			with(appendChild(document.createElement('td'))) {
				setAttribute("onclick","click_row(this)");
				style.display='none';
				innerHTML='<select style="width:100%;display:none" name=order_type['+new_idx+'] onchange="notsaved()"><option>�� �������</option><option>��������</option></select>';
			}
		click_row(cells[0]);
		}
	}	
//alert(frm['obj_id['+new_idx+']']);
}
function del_obj(obj) {
	notsaved();
	with(obj.parentNode.parentNode) { //�������
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
	//������� � ������
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
	with(obj.parentNode.parentNode) {//������������ ������
	if(obj.value=='page') { //����� ��������
	setAttribute('data-type','page');//���������������� ������� ������
		cells[2].innerHTML=""; //�.�.� (id)
		cells[4].setAttribute("colspan",5); //���������
		cells[5].style.display='none'; //���
		cells[6].style.display='none'; //������� ���
		cells[7].style.display='none'; //����
		cells[8].style.display='none'; //�����
		cells[9].style.display=''; //�������
		frm['type['+obj.id+']'].value='page';
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='none';
		frm['code_name['+obj.id+']'].style.display='none';
		frm['must['+obj.id+']'].style.display='none';
		frm['quoted['+obj.id+']'].style.display='none';
		frm['order_type['+obj.id+']'].style.display='';
	}
	else if(obj.value=='group') { //����� ������
	setAttribute('data-type','group');
		cells[2].innerHTML=""; //�.�.� (id)
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
	else if(obj.value.substr(0,5)=='q_sn_') { //������� ���������
	setAttribute('data-type','object');
		cells[2].innerHTML="1���.���.<input type=checkbox name='newpage["+obj.id+"]'>";  //�.�.� (id)
		cells[4].setAttribute("colspan",1);
		cells[5].style.display='';
		cells[6].style.display='';
		cells[7].style.display='';
		cells[8].style.display='';	
		cells[9].style.display='';		
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //���� �������� ����� �������� ��������, �� ���.��� ������ �� ���������
		frm['type['+obj.id+']'].value='obj';		
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='';
		frm['code_name['+obj.id+']'].style.display='';
		frm['must['+obj.id+']'].style.display='';
		frm['quoted['+obj.id+']'].style.display='none';		
		frm['order_type['+obj.id+']'].style.display='none';
	}
	else if(obj.value.substr(0,5)=='q_ls_') { //������� � �������
	setAttribute('data-type','object');	
		cells[2].innerHTML="2���.���.<input type=checkbox name='newpage["+obj.id+"]'>";  //�.�.� (id)
		cells[4].setAttribute("colspan",1);
		cells[5].style.display='';
		cells[6].style.display='';
		cells[7].style.display='';
		cells[8].style.display='';
		cells[9].style.display='';
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //���� �������� ����� �������� ��������, �� ���.��� ������ �� ���������
		frm['type['+obj.id+']'].value='obj';
		frm['message['+obj.id+']'].style.display='';
		frm['text_name['+obj.id+']'].style.display='';
		frm['code_name['+obj.id+']'].style.display='';
		frm['must['+obj.id+']'].style.display='';
		frm['quoted['+obj.id+']'].style.display='';
		frm['order_type['+obj.id+']'].style.display='';
	}	
	else { //��� ���������
	setAttribute('data-type','object');
		cells[2].innerHTML="3���.���.<input type=checkbox name='newpage["+obj.id+"]'>";  //�.�.� (id)
		cells[4].setAttribute("colspan",6);
		cells[5].style.display='none';
		cells[6].style.display='none';
		cells[7].style.display='none';
		cells[8].style.display='none';
		cells[9].style.display='none';
		if(document.all.hide_pages.checked==true) frm['newpage['+obj.id+']'].checked=true; //���� �������� ����� �������� ��������, �� ���.��� ������ �� ���������
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
	document.getElementById('save_status').innerHTML='<font color=red>�� �������� ��������� ���������!</font>';
	frm.logic_test.style.display='none';
}
function fHidePages() {
	with(document.getElementById('tbl')) { //�������
		for(i=1; i<=rows.length-1; i++) {
			if(rows[i].getAttribute('data-type')=='page' || rows[i].getAttribute('data-type')=='group') rows[i].style.display='none';
		}
	}
}
function fShowPages() {
	with(document.getElementById('tbl')) { //�������
		for(i=1; i<=rows.length-1; i++) {
			if(rows[i].getAttribute('data-type')=='page' || rows[i].getAttribute('data-type')=='group') rows[i].style.display='';
		}
	}
}
/*function edit_obj(obj_id,obj_type) {
	//document.body.style.overflow = 'hidden'; //������ ��������� ���������
	if(document.all.popUpFrame.style.display=='none') {
		topOffset=240; //�������� ���� ����� �� ������� �������
		document.all.popUpFrame.style.left=event.clientX+'px';
		if(event.clientY<topOffset) {
			document.all.popUpFrame.style.top='10px';	
		}
		else {
			document.all.popUpFrame.style.top=event.clientY-topOffset+'px';
		}
		document.all.popUpFrame.style.display='';
		document.all.popUpFrame.src='adm.ank.edit.obj.edit.php?obj_id='+obj_id+'&obj_type='+obj_type;
		//��������� ��� �������� �����
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