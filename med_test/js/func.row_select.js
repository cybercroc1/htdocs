var sel_color='#66fffe';
var unsel_color='';
var clicked_color='#cccccc';
var clicked_sel_color='#66ccff';
function sel_row(row) {
	//sel_color='#66FFFF';
	for(i=0; i<row.cells.length; i++) {
		if(row.cells[i].bgColor==sel_color) {//���� ��� ��������, �� �� ������, �� ������ �� ������
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==unsel_color) {//���� �� �������� � �� ������, �� ��������
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==clicked_sel_color) {//���� �������� � ������, �� ������ �� ������
			row.cells[i].bgColor=clicked_sel_color;
		}
		else if(row.cells[i].bgColor==clicked_color) {//���� �� �������� � ������, �� ������ � ������-���������� ����
			row.cells[i].bgColor=clicked_sel_color;
		}
	}
}
function unsel_row(row) {
	//unsel_color='white';
	for(i=0; i<row.cells.length; i++) {
		//alert(row.cells[i].bgColor+" - "+unsel_color);
		if(row.cells[i].bgColor==unsel_color) {//���� �� �������� � �� ������ �� ������ �� ������
			row.cells[i].bgColor=unsel_color;
		}		
		else if(row.cells[i].bgColor==clicked_color) {//���� �� �������� � ������ �� ������ �� ������
			row.cells[i].bgColor=clicked_color;
		}	
		else if(row.cells[i].bgColor==clicked_sel_color) {//���� �������� � ������ �� ������ � ������-������������ ����
			row.cells[i].bgColor=clicked_color;
		}
		else if(row.cells[i].bgColor==sel_color) {//���� �������� � �� ������ �� ������� ���������*/
			row.cells[i].bgColor=unsel_color;
		}
	}
}
//function click_row(row_id) {
//	row=document.getElementById(row_id);
function click_row(row) {
	var res='';
	tbl=row.parentNode // �������
	for(r=1; r<=tbl.rows.length-1; r++) {
		if(row==tbl.rows[r]) { //��� ��������� ������
			for(i=0; i<row.cells.length; i++) {
				if(row.cells[i].bgColor==unsel_color) {//���� �� �������� � �� ������ (��������)
					unsel_click_row(row);
					res='click';
				}
				else if(row.cells[i].bgColor==sel_color) {//�������� � �� ������ (�������� � ��������)
					sel_click_row(row);
					res='click';
				}
				else if(row.cells[i].bgColor==clicked_sel_color) {//������ � �������� (�������� � ��������)
					unclick_row(row);
					res='unclick';
				}
			}
		}
		else {
			unclick_row(tbl.rows[r]); //��� ���� ��������� �������� ���������
		}
	}
	return res;
}
//function unclick_row(row_id) {
//	row=document.getElementById(row_id);
function unclick_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=unsel_color;
	}
}
//function sel_click_row(row_id) {
//	row=document.getElementById(row_id);
function sel_click_row(row) {	
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_sel_color;
	}
}
//function unsel_click_row(row_id) {
//	row=document.getElementById(row_id);
function unsel_click_row(row) {
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_color;
	}
}