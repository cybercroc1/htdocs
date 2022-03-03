function click_row(cell) {
	with(cell.parentNode.parentNode) {// таблица
		//снимаем выделение со всех строк
		for(i=1; i<=rows.length-1; i++) {
			for(j=0; j<rows[i].cells.length; j++) {
				rows[i].cells[j].classList.remove('clicked_row');
	}}}
	//ставим выделение на строку
	with(cell.parentNode) {//строка
		for(j=0; j<cells.length; j++) {
			cells[j].classList.add('clicked_row');
}}}	
function sel_row(row) {
	if(row.cells[0].classList.contains('clicked_row')) {row.setAttribute('data-clicked','y');}
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.remove('clicked_row');
		row.cells[i].classList.add('selected_row');
		
}}
function unsel_row(row) {
	clicked='';
	//alert(row.getAttribute('data-clicked'));
	if(row.getAttribute('data-clicked')=='y') {row.setAttribute('data-clicked',''); clicked='y';}
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].classList.remove('selected_row');
		if(clicked=='y') row.cells[i].classList.add('clicked_row');
}}