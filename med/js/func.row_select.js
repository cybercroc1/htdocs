var sel_color='#66fffe';
var unsel_color='';
var clicked_color='#cccccc';
var clicked_sel_color='#66ccff';
function sel_row(row) {
	//sel_color='#66FFFF';
	for(i=0; i<row.cells.length; i++) {
		if(row.cells[i].bgColor==sel_color) {//если уже выделена, но не нажата, то ничего не делаем
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата, то выделяем
			row.cells[i].bgColor=sel_color;
		}
		else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата, то ничего не делаем
			row.cells[i].bgColor=clicked_sel_color;
		}
		else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата, то красим в нажато-выделенный цвет
			row.cells[i].bgColor=clicked_sel_color;
		}
	}
}
function unsel_row(row) {
	//unsel_color='white';
	for(i=0; i<row.cells.length; i++) {
		//alert(row.cells[i].bgColor+" - "+unsel_color);
		if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата то ничего не делаем
			row.cells[i].bgColor=unsel_color;
		}		
		else if(row.cells[i].bgColor==clicked_color) {//если не выделена и нажата то ничего не делаем
			row.cells[i].bgColor=clicked_color;
		}	
		else if(row.cells[i].bgColor==clicked_sel_color) {//если выделена и нажата то красим в нажато-невыделенный цвет
			row.cells[i].bgColor=clicked_color;
		}
		else if(row.cells[i].bgColor==sel_color) {//если выделена и не нажата то снимаем выделение*/
			row.cells[i].bgColor=unsel_color;
		}
	}
}
//function click_row(row_id) {
//	row=document.getElementById(row_id);
function click_row(row) {
	var res='';
	tbl=row.parentNode // таблица
	for(r=1; r<=tbl.rows.length-1; r++) {
		if(row==tbl.rows[r]) { //для кликнутой строки
			for(i=0; i<row.cells.length; i++) {
				if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата (нажимаем)
					unsel_click_row(row);
					res='click';
				}
				else if(row.cells[i].bgColor==sel_color) {//выделена и не нажата (нажимаем и выделяем)
					sel_click_row(row);
					res='click';
				}
				else if(row.cells[i].bgColor==clicked_sel_color) {//нажата и выделена (отжимаем и выделяем)
					unclick_row(row);
					res='unclick';
				}
			}
		}
		else {
			unclick_row(tbl.rows[r]); //для всех остальных отменяем выделение
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