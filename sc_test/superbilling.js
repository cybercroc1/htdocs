function fBodyLoad () {
	fCheckSelectedFields();
	fCheckDate();
}
function fCheckSelectedFields() {
	with(document.all) {
		for(i=0; i<selected_fields.options.length; i++) {
			for(j=0; j<all_fields.options.length; j++) {
				if(all_fields.options[j].value==selected_fields.options[i].value) {
					selected_fields.options[i].innerText=all_fields.options[j].innerText;
					all_fields.options[j].disabled=true;
					all_fields.options[j].style.color="#a0a0a0";
				}
			}
		}
	}
}

function fSelectFields() {
	with(document.all.selected_fields) {
		for(i=0; i<options.length; i++) {
			options[i].selected=true;
		}
	}
}
function fAddFields() {
	with(document.all) {
		for(i=0; i<all_fields.options.length; i++) {
			if(all_fields.options[i].selected) {
				selected_fields.options[selected_fields.options.length] = new Option(all_fields.options[i].innerText,all_fields.options[i].value, false, false);
				selected_fields.options[selected_fields.options.length-1].selected=true;
				all_fields.options[i].disabled=true;
				all_fields.options[i].style.color="#a0a0a0";
				all_fields.options[i].selected=false;
			}
		}
	}
}

function fDelFields() {
	with(document.all) {
		for(i=0; i<selected_fields.options.length; i++) {
			if(selected_fields.options[i].selected) {
				for(j=0; j<all_fields.options.length; j++) {
					if(all_fields.options[j].value==selected_fields.options[i].value) {
						all_fields.options[j].disabled=false;
						all_fields.options[j].style.color="";						
					}
				}
				selected_fields.options.remove(i); i=i-1;
			}
		}
	}
}

function fUpFields() {
	with(document.all.selected_fields) {
		j=0;
		for(i=0; i<options.length; i++) {
			if(options[i].selected) {j++;}
			if(options[i].selected & i>j-1) {
				tmp_text=options[i].innerText; tmp_value=options[i].value;
				options[i].innerText=options[i-1].innerText; options[i].value=options[i-1].value;
				options[i-1].innerText=tmp_text; options[i-1].value=tmp_value;
				options[i].selected=false; options[i-1].selected=true;
			}		
		}
	}
}

function fDownFields() {
	with(document.all.selected_fields) {
		j=options.length-1;
		for(i=options.length-1; i>=0; i--) {
				if(options[i].selected) {j--;}
				//alert(options[i].value);
				if(options[i].selected & i<=j) {
				tmp_text=options[i].innerText; tmp_value=options[i].value;
				options[i].innerText=options[i+1].innerText; options[i].value=options[i+1].value;
				options[i+1].innerText=tmp_text; options[i+1].value=tmp_value;
				options[i].selected=false; options[i+1].selected=true;
			}		
		}
	}
}
function fCheckDate() {
	with(document.all) {
		if(start_bill_date.value==end_bill_date.value) {
			tr_bill_time.style.display='';
		}
		else {
			tr_bill_time.style.display='none';
		}
	}
}