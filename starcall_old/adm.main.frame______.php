<?php include("../../conf/starcall_conf/session.cfg.php"); ?>
<!DOCTYPE html>
<head>
<title>StarCall</title>
<script>
//ежеминутное обновление сессии
var param='adm';
func_sess_refresh(param);
window.setInterval(func_sess_refresh(param),60000);
function func_sess_refresh(param) {
	if(document.all.admBottomFrame) {
		//проверяем, что в дочернем фрейме не открыта страница с опросом
		if(!admBottomFrame.document.location.toString().match('survey')) {
		
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
		}
	}
}
</script>
</head>
<frameset rows="50,*,10">
  <frame src=adm.main.menu.php name=admMainTopFrame id=admTopFrame title=admTopFrame>

  <frame src=blank_page.php name=admBottomFrame id=admBottomFrame title=admBottomFrame>
  
  <frame src=blank_page.php name=logFrame id=logFrame title=admBottomFrame>
</frameset>
<noframes></noframes>
