function func_sess_refresh(param) {
	//if(document.all.admBottomFrame) {
		//проверяем, что в дочернем фрейме не открыта страница с опросом
		//if(!admBottomFrame.document.location.toString().match('survey')) {
		
			if (window.XMLHttpRequest) {
				req = new XMLHttpRequest();
			}
			else {
				if (window.ActiveXObject) {
					try {
						req = new ActiveXObject('Msxml2.XMLHTTP'); 
					}
					catch (e) {}
					try {
						req = new ActiveXObject('Microsoft.XMLHTTP');  
					}
					catch(e) {}
				}
			}   
			req.open('GET', 'session.refresh.php?'+param, true);
			//alert('сессия администратора подтверждена');
			//req.onreadystatechange = function() {setTimeout(aj, 5000)}
			req.send(null);
		//}
	//}
}