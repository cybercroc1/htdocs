function uploadFile(file) {
  var url = 'files2.php';
  var xhr = new XMLHttpRequest();
  var formData = new FormData();
  var base_id = frm.num_zayavki.value;
  xhr.open('POST', url, true);
  //xhr.setRequestHeader("Content-type", "multipart/form-data");
  xhr.addEventListener('readystatechange', function(e) {
    if(xhr.readyState == 4) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			// Готово. Информируем пользователя
			res=xhr.responseText.trim();
			if(res.substr(0,3)=='OK:') {
				//alert(res);
				res_arr=res.split(':');
				add_file_link(res_arr[1],res_arr[2],res_arr[3]);
				
			}
			else alert(xhr.responseText);
		}
		else if (xhr.readyState == 4 && xhr.status != 200) {
		// Ошибка. Информируем пользователя
			alert(xhr.responseText);
		}
	}
  })
  formData.append('file', file);
  formData.append('base_id', base_id);
  xhr.send(formData);
}
function deleteFile(fileid) {
  var url = 'files2.php';
  var xhr = new XMLHttpRequest();
  var formData = new FormData();
  xhr.open('POST', url, true);
  xhr.addEventListener('readystatechange', function(e) {
    if(xhr.readyState == 4) {
		if (xhr.readyState == 4 && xhr.status == 200) {
			// Готово. Информируем пользователя
			res=xhr.responseText.trim();
			if(res.substr(0,3)=='OK:') {
				delete_file_link(fileid);
			}
			else alert(xhr.responseText);
		}
		else if (xhr.readyState == 4 && xhr.status != 200) {
		// Ошибка. Информируем пользователя
			alert(xhr.responseText);
		}
	}
  })
  formData.append('delete', '');
  formData.append('fileid', fileid);
  xhr.send(formData);
}
function add_file_link(fileid,filetype,filename) {
	//alert(fileid+filetype+filename);
	var url = 'files2.php';
	fl=document.createElement("div");
	fl.id=fileid;
	fl.innerHTML='<nobr><a href="'+url+'?download&fileid='+fileid+'">'+filename+'</a> (<a href="javascript:deleteFile(\''+fileid+'\')" title="Удалить файл"><font color=red>x</font></a>);</nobr><br>';
	//alert(fl.innerHTML);
	if(filetype.substr(0,5)=='audio') {
		fl.innerHTML+='<audio controls preload=metadata style="width:100%"><source src="'+url+'?download&fileid='+fileid+'" type="audio/ogg; codec=vorbis"><source src="'+url+'?download&fileid='+fileid+'" type="'+filetype+'"></audio>';
	}
	if(filetype.substr(0,5)=='image') {
		fl.innerHTML+='<img width=100% src="'+url+'?download&fileid='+fileid+'" type="'+filetype+'"></img>';
	}
	document.getElementById('div_add_file').appendChild(fl);
}
function delete_file_link(fileid) {
	fl=document.getElementById(fileid);
	document.getElementById('div_add_file').removeChild(fl);
}