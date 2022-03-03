/*function ch_new() {
	if (document.all.new_fio.value=='' || document.all.new_login.value=='' || document.all.new_pass.value=='') {
	document.all.add_user.disabled=true;
	} else {
	document.all.add_user.disabled=false;
}}*/
function del_new_user(cell) {
	cell.parentNode.parentNode.deleteRow(cell.parentNode.rowIndex);
}
function del_old_user(cell) {
	notsaved();
	inp = document.createElement( 'input' );
	inp.type = 'hidden';
	inp.name = 'del_user['+cell.parentNode.getAttribute('data-user_id')+']';
	inp.value=cell.parentNode.getAttribute('data-user_id');
	document.frm.appendChild(inp);
	cell.parentNode.parentNode.deleteRow(cell.parentNode.rowIndex);	
}

function plus(cell) {
	//click_row(cell);
	notsaved();
	with(cell.parentNode.parentNode) {//таблица
		//ищем индекс последней строки пользователей в группе
		for(n=rows.length-1; n>=1; n--) {
			if(rows[n].getAttribute('data-in_group')=='y') {
				insertBefore(cell.parentNode,rows[n+1]);
				cell.parentNode.setAttribute('data-in_group','y');
				cell.parentNode.cells[1].setAttribute('onclick','minus(this)');
				cell.parentNode.cells[1].innerHTML="<img src='png/minus.png'></img>";
				for(i=0; i<cell.parentNode.cells.length; i++) {
					cell.parentNode.classList.add('selected_row');
					//cell.parentNode.cells[i].classList.toggle('clicked_row');
				}

				user_id=cell.parentNode.getAttribute('data-user_id');
				
				if(frm["move_user["+user_id+"]"]) {
					new_input=frm["move_user["+user_id+"]"];
				}
				else {
					new_input=frm.appendChild(document.createElement('input'));
					new_input.setAttribute("type","hidden");
					new_input.name="move_user["+user_id+"]";
				}	
				new_input.value="to_group";	
				break;
			}
		}	
	}
}

function minus(cell) {
	//click_row(cell);
	notsaved();
	with(cell.parentNode.parentNode) {//таблица
		//ищем индекс первой строки пользователей не в группе
		for(n=1; n<=rows.length; n++) {
			if(rows[n].getAttribute('data-in_group')=='n') {
				insertBefore(cell.parentNode,rows[n+1]);
				cell.parentNode.setAttribute('data-in_group','n');
				cell.parentNode.cells[1].setAttribute('onclick','plus(this)');
				cell.parentNode.cells[1].innerHTML="<img src='png/plus.png'></img>";
				for(i=0; i<cell.parentNode.cells.length; i++) {
					cell.parentNode.classList.remove('selected_row');
					//cell.parentNode.cells[i].classList.toggle('clicked_row');
					//cell.parentNode.cells[i].classList.remove('clicked_row');
				}
				user_id=cell.parentNode.getAttribute('data-user_id');
				
				if(frm["move_user["+user_id+"]"]) {
					new_input=frm["move_user["+user_id+"]"];
				}
				else {
					new_input=frm.appendChild(document.createElement('input'));
					new_input.setAttribute("type","hidden");
					new_input.name="move_user["+user_id+"]";
				}	
				new_input.value="from_group";								
				break;
			}
		}	
	}
}
function add_user(cell) {
	new_idx++;
	notsaved();
	with(cell.parentNode.parentNode) { //таблица
		if(rows[1].getAttribute('data-type')=='head_in_group') r=2;
		else r=1;
		
		new_row=insertRow(r);
		with(new_row) {//строка
			//setAttribute("onmouseover","sel_row(this)");
			//setAttribute("onmouseout","unsel_row(this)");
			//setAttribute("onclick","click_row(this,'sel')");
			setAttribute('class','selectable_row');
			with(appendChild(document.createElement('th'))) {//добавить
				setAttribute("ondblclick","del_new_user(this)");
				setAttribute("title","Удалить пользователя (двойной щелчок)");
				style.cursor='pointer';
				innerHTML="<img src='png/del.png'></img>";
			}
			with(appendChild(document.createElement('th'))) {//пустая
				//setAttribute("onclick","click_row(this)");
			}			
			with(appendChild(document.createElement('td'))) {//ID
				//setAttribute("onclick","click_row(this)");
			}
			with(appendChild(document.createElement('td'))) {//ФИО
				//setAttribute("onclick","click_row(this)");
				innerHTML='<input type=hidden name="new_user['+new_idx+']"><input type=text style="width:100%" name="new_fio['+new_idx+']">';
			}
			with(appendChild(document.createElement('td'))) {//логин
				//setAttribute("onclick","click_row(this)");
				innerHTML='<input type=text style="width:100%" name="new_login['+new_idx+']">';
			}			
			with(appendChild(document.createElement('td'))) {//пароль
				//setAttribute("onclick","click_row(this)");
				innerHTML='<input type=text style="width:100%" name="new_pass['+new_idx+']">';
				//random_pass(6);
			}		
			with(appendChild(document.createElement('td'))) {//роль
				//setAttribute("onclick","click_row(this)");
				with(appendChild(document.createElement('select'))) {
					name="new_role["+new_idx+"]";
					setAttribute("onchange","default_role=this.value");
					for(var i=0; i<role_ids.length; i++) {
						opt=appendChild(document.createElement('option')); 
						opt.value=role_ids[i]; 
						opt.innerText=role_names[i];
						if(role_ids[i]==default_role) opt.selected=true;
					}
				}
			}					
			with(appendChild(document.createElement('td'))) {//пустая
				//setAttribute("onclick","click_row(this)");
			}
			with(appendChild(document.createElement('td'))) {//пустая
				//setAttribute("onclick","click_row(this)");
			}	
		click_row(new_row,'add');
		}
	}	
}
function notsaved() {
	document.getElementById('save_status').innerHTML='<font color=red>Не забудьте сохранить изменения!</font>';
	frm.cancel.style.display='';
}
function ch_user(user_id) {
	with(frm) {
		with(appendChild(document.createElement('input'))) {
			setAttribute("type","hidden");
			name="ch_user["+user_id+"]";
		}
	}
}
function ch_creator(user_id) {
	with(frm) {
		with(appendChild(document.createElement('input'))) {
			setAttribute("type","hidden");
			name="ch_creator["+user_id+"]";
		}
	}
}
/*function random_pass(count) {

	rand=Math.floor(Math.random()*(3))+1;
	alert(rand);

}*/