function form2div(frm_id,div_id,clicked_button,act) {
		
	var allready=function() {}
	var loading=function() {}
	var aborting=function() {}
	var aborted=function() {}
	var done=function() {}

	var frm=document.getElementById(frm_id);
	var url='';
	var method=frm.method;
	var data='';
	
	if(typeof act==='undefined') url=frm.action; else url=act; 

	if(typeof window.form2div_obj ==='undefined') window.form2div_obj=new Array();	
	if(typeof window.form2div_state ==='undefined') window.form2div_state=new Array();
	if(typeof window.form2div_abort_requested ==='undefined') window.form2div_abort_requested=new Array();
	if(typeof window.form2div_state[div_id]==='undefined') window.form2div_state[div_id]='inactive';
	if(typeof window.form2div_abort_requested[div_id]==='undefined') window.form2div_abort_requested[div_id]='n';
	
	var div=document.getElementById(div_id);
	if(window.form2div_state[div_id]=='active') {
		form2div.allready(frm_id,div_id,clicked_button);
		return;
	}
	
	if(method=='post') {
		var boundary = String(Math.random()).slice(2);
		var boundaryMiddle = '--' + boundary + '\r\n';
		var boundaryLast = '--' + boundary + '--\r\n';	
		data = ['\r\n'];
	}
	
	var is_form_button='';
	for (var i = 0, element; element = frm.elements[i++];) {
		
		if(element.type=='button' || element.type=='submit') {
			if(element==clicked_button) {
				is_form_button='y';
				if(method=='post') data.push('Content-Disposition: form-data; name="' + element.name + '"\r\n\r\n' + element.value + '\r\n');
				else data=data+element.name+'='+element.value+'&';
			}
		}
		else if(element.type=='radio' || element.type=='checkbox') {
			if(element.checked==true) {
				if(method=='post') data.push('Content-Disposition: form-data; name="' + element.name + '"\r\n\r\n' + element.value + '\r\n');
				else data=data+element.name+'='+element.value+'&';
			}
		}
		else if(element.type=='select-one') {
				if(method=='post') data.push('Content-Disposition: form-data; name="' + element.name + '"\r\n\r\n' + element.value + '\r\n');
				else data=data+element.name+'='+element.value+'&';
		}
		else if(element.type=='select-multiple') {
			for(var j = 0, opt; opt = element.options[j++];) {
				if(opt.selected==true) {
					if(method=='post') data.push('Content-Disposition: form-data; name="' + element.name + '"\r\n\r\n' + opt.value + '\r\n');
					else data=data+element.name+'='+opt.value+'&';
				}
			}
		}
		else {
			if(method=='post') data.push('Content-Disposition: form-data; name="' + element.name + '"\r\n\r\n' + element.value + '\r\n');
			else data=data+element.name+'='+element.value+'&';
		}
	}	
	if(is_form_button!='y') {
		if(method=='post') data.push('Content-Disposition: form-data; name="' + clicked_button.name + '"\r\n\r\n' + clicked_button.value + '\r\n');
		else data=data+clicked_button.name+'='+clicked_button.value+'&';		
	}
	
	if(method=='post') {
		data = data.join(boundaryMiddle) + boundaryLast;
	}
	else {
		regex=new RegExp(/\?/);
		if(regex.test(url)==true) url=url+'&'+data;
		else url=url+'?'+data;
		data='';
	}

	try {
		form2div_obj[div_id] = new ActiveXObject("Msxml2.XMLHTTP");
	} 
	catch (e) {
		try {
			form2div_obj[div_id] = new ActiveXObject("Microsoft.XMLHTTP");
		} 
		catch (E) {
			form2div_obj[div_id] = false;
		}
	}
	if (!form2div_obj[div_id] && typeof XMLHttpRequest!='undefined') {
		form2div_obj[div_id] = new XMLHttpRequest();
	}
	form2div_obj[div_id].open(method, url, true);
	form2div.loading(frm_id,div_id,clicked_button);
	if(method=='post') form2div_obj[div_id].setRequestHeader( 'Content-Type', 'multipart/form-data; boundary=' + boundary );
	//form2div_obj[div_id].onabort=function() {form2div.aborted(frm_id,div_id,clicked_button);} //это не работает в firefox
	form2div_obj[div_id].onreadystatechange=function() {
		if(form2div_obj[div_id].readyState==4) {
			window.form2div_state[div_id]='inactive';
			if(window.form2div_abort_requested[div_id]=='y') {
				window.form2div_abort_requested[div_id]='n'; 
				//window.form2div_state[div_id]='inactive'; 
				form2div.aborted(frm_id,div_id,clicked_button);
			}
			else {
				form2div.done(frm_id,div_id,clicked_button,form2div_obj[div_id].responseText);
			}
		}
	}
	window.form2div_state[div_id]='active';
	form2div_obj[div_id].send(data);
}
function form2div_abort(frm_id,div_id) {
	if(typeof window.form2div_obj !='undefined') {
		if(typeof window.form2div_obj[div_id]!='undefined') {
			window.form2div_abort_requested[div_id]='y';
			window.form2div.aborting(frm_id,div_id);
			window.form2div_obj[div_id].abort();
		}
	}
}

/*//эти функции можно переопределить на странице
form2div.allready=function(frm_id,div_id,clicked_button) {
	alert('Запрос уже выполняется!');	
}
form2div.loading=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='<input type=button onclick=form2div_abort("'+div_id+'") value=прервать></div>';	
}
form2div.aborted=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='прервано<br>';
}
form2div.done=function(frm_id,div_id,clicked_button,response) {
	var div=document.getElementById(div_id);
	div.innerHTML=div.innerHTML=response;
}	
*/

//эти функции можно переопределить на странице
form2div.allready=function(frm_id,div_id,clicked_button) {
	alert('Запрос уже выполняется!');	
}
form2div.loading=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;"><img src="/js/img/progress.gif"></img><br><a class="abort-href" href=\'javascript:form2div_abort("'+frm_id+'","'+div_id+'")\'>прервать загрузку</a></div>';	
}
form2div.aborting=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;"><img src="/js/img/progress.gif"></img><br><font color=red>отменяется...</font></div>';	
}
form2div.aborted=function(frm_id,div_id,clicked_button) {
	var div=document.getElementById(div_id);
	div.innerHTML='<div style="text-align:center;margin:30px;">загрузка прервана</div>';	
}
form2div.done=function(frm_id,div_id,clicked_button,response) {
	var div=document.getElementById(div_id);
	div.innerHTML=div.innerHTML=response;
}	