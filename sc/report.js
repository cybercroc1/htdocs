
var sel_color='#66fffe';
var unsel_color='white';
var clicked_color='#cccccc';
var clicked_sel_color='#66ccff';
var now_played_id='';

function click_play(obj,pla_src) {
	pla=document.getElementById("player");
	if(obj.id!=now_played_id) {
		if(old_obj=document.getElementById(now_played_id)) {
			//alert(old_obj.src);
			old_obj.src=old_obj.src.replace(/new|playing|stopped/g,'played');
		}
		pla.style.display='';
		pla.src=pla_src;
		//obj.src=obj.src.replace(/new|played|stopped/g,'playing');
		now_played_id=obj.id;
		pla.play();
	}
	else {
		if(pla.paused) {
			//obj.src=obj.src.replace(/new|played|stopped/g,'playing');
			pla.play();
		}
		else {
			//obj.src=obj.src.replace(/new|played|playing/g,'stopped');
			//pla.currentTime = 0;
			pla.pause();
		}
	}
}
function player_onplay() {
	//alert(now_played_id);
	obj=document.getElementById(now_played_id);
	obj.src=obj.src.replace(/new|played|stopped/g,'playing');
}
function player_onpause() {
	obj=document.getElementById(now_played_id);
	obj.src=obj.src.replace(/new|played|playing/g,'stopped');
	//alert(now_played_id);
}


function sel_cell(cell) {
	row=cell.parentNode;
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

function unsel_cell(cell) {
	row=cell.parentNode;
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




function click_row(row) {
	//row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		if(row.cells[i].bgColor==unsel_color) {//если не выделена и не нажата
			row.cells[i].bgColor=clicked_color;
		}
		if(row.cells[i].bgColor==sel_color) {//если не выделена и не нажата
			row.cells[i].bgColor=clicked_sel_color;
		}
	}
}
function unclick_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=unsel_color;
	}
}
function sel_click_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_sel_color;
	}
}
function unsel_click_row(row_id) {
	row=document.getElementById(row_id);
	for(i=0; i<row.cells.length; i++) {
		row.cells[i].bgColor=clicked_color;
	}
}
function down_click(obj,url) {
	//window.onload=function(){alert('ddd');}
	//document.getElementById('hidden_frame').onload=function(){alert('ddd');}
	if(hidden_frame.location=url){
		temp=obj.onclick;
		obj.src='imgplay/downloaded.png';
		obj.onclick=function(){return false;}
		setTimeout(function(){obj.onclick=temp;},5000);
	}else{}	
}