<html>
<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1251\">
</head>
<body>

����� �������: <input type='text' id='oktell_server_address'> ��������� �������: <input type='text' id='oktell_server_status'><br>
��������� �� 89251166091: <input disabled id=callto_button type=button onclick=callto('','89251166091','111111') value='���������'></input><br>
��������� ������: <input disabled id=endcall_button type=button onclick=endcall() value='��������� ������'></input><br>
�������� ��������� ������<br>
<textarea id=check_active_call></textarea><br>
���� ������<br>
<textarea id=check_active_calling></textarea><br>
ID �������<br>
<textarea id=get_chain_id></textarea><br>

<input type="hidden" id=sra_id name="sra_id" value="112"/>;

<script>

var oktell_server_address='';
var oktell_phone_prefix='';
var source_auto_id=document.getElementById('sra_id').value;
getcurrentserveraddress();
t=setInterval("getcurrentserveraddress()",2000);
function getcurrentserveraddress() {  
    var xml;  
    if(window.XMLHttpRequest) { 
        xml=new window.XMLHttpRequest();  
        xml.open("GET", 'http://127.0.0.1:4059/getcurrentserveraddress', true); 
		xml.timeout = 2000;
        xml.send("");  
		xml.onreadystatechange = function() {
			if (xml.readyState == 4) {
				if(xml.status==200) {
					var response=xml.responseText;
					var regex=/serveraddress.*><!\[CDATA\[([^\]]*)\]/im;
					var matches=response.match(regex);
					if(matches[1]!=oktell_server_address) {
						oktell_server_address=matches[1];
						ch_oktell_server_address(oktell_server_address);
					} 
				}
				else {
					oktell_server_address='';	
					ch_oktell_server_address(oktell_server_address);
				}
			}
		}
	}  
} 
function ch_oktell_server_address(new_oktell_server_address) {
	document.getElementById('oktell_server_address').value=oktell_server_address;
	if(oktell_server_address!='') {
		document.getElementById('oktell_server_status').value='������ ���������';

				//�������� ������� ������
                xml = new window.XMLHttpRequest();
                xml.open("GET", 'get_oktell_out_prefix.php?'
                            +'oktell_server_address='+oktell_server_address
                            +'&source_auto_id='+source_auto_id, false);
                xml.send("");
				//alert(xml.responseText);	
                var response=xml.responseText;
                var regex=/prefix=([^\"]*);/im;
                if(matches=response.match(regex)) {
                    oktell_phone_prefix=matches[1];
					//alert(oktell_phone_prefix);
				}
				else {
					//������ ��������� ��������
					alert(xml.responseText);
					return;
				}
				
		//������ ������ ������
		ch_callto();
	}
	else {
		document.getElementById('oktell_server_status').value='������ �� ���������';
		oktell_phone_prefix='';
		//������ ������ ������
		ch_oktell_disconnected();
	}
} 
function callto(phone_prefix,phone_number,base_id) {
	var call_hist_id='';
	if(document.getElementById('oktell_server_address').value=='') alert('������: ��� ����������� � ������');
	else {
        //�������� ��������� ������ � ������� ������������
		xml=new window.XMLHttpRequest();  
		xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false); 
        xml.send("");
		var response=xml.responseText;
		document.getElementById('check_active_call').innerText=response;
		

                var regex=/IdChain:([^;]*)/im;
                matches=response.match(regex);
                var IdChain=matches[1];
                
                var regex=/IdUser:([^;]*)/im;
                matches=response.match(regex);
                var IdUser=matches[1];				
				
				if(IdChain!='00000000-0000-0000-0000-000000000000') {
                    alert('������� ��������� �������� ������');
                    return;
                }
                var regex=/StatusUser:([^;]*)/im;
                matches=response.match(regex);
                var StatusUser=matches[1];
                if(StatusUser != '1' && matches[1] != '2') {
                    alert('������ ������������ �� ��������� ������� ���. ������');
                    return;
                }
                //���� ��� ��������� ������
                //��������� ���� ������� ������ � �������� ������������� ������� ������
                //������� �������� ���������� � ������� ������ � ������� �������
                xml = new window.XMLHttpRequest();
				xml.open("GET", 'put_call_to_hist.php?'
							+'call_hist_id='
                            +'&base_id='+base_id
                            +'&oktell_server_address='+oktell_server_address
                            +'&okt_IdChain='+IdChain
                            +'&okt_IdUser='+IdUser
                            +'&phone_prefix='+oktell_phone_prefix
                            +'&phone_number='+phone_number
                            +'&base_id='+base_id, false);
				xml.send("");			
                var response=xml.responseText;
				//alert(response);
				var regex=/new_id:([^;]*)/im;
                var matches=response.match(regex);
                var call_hist_id=matches[1];
//alert(call_hist_id);				
		//���� ��� ��������� ������ 
		//�������� ������� �����
		xml=new window.XMLHttpRequest();  
        xml.open("GET", 'http://127.0.0.1:4059/callto?number='+phone_prefix+phone_number, false); 
        xml.send("");		
        //�������� ��������� ������ ����� ������ ������
		//����������� SetTimeout
				var t2=setTimeout(function CallMonitor() {
                    xml=new window.XMLHttpRequest();
                    xml.open("GET", 'http://127.0.0.1:4059/getcurrentcallinfo', false);
                    xml.send("");
                    var response=xml.responseText;
                    var regex=/mode.*value="([^"]*)"/im;
                    var matches=response.match(regex);

                    if(matches[1]=='calling' || matches[1]=='ringing' || matches[1]=='connected' || matches[1]=='flashed') {
                        //document.getElementById('check_active_calling').innerText=response;

                        //������ ������ ������
                        ch_endcall();

                        //���� ����� �������, �� �������� ID ������������ � ID ������� ����������
                        xml = new window.XMLHttpRequest();
                        xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false);
                        xml.send("");
                        var response=xml.responseText;
                        var regex=/IdChain:([^;]*)/im;
                        matches=response.match(regex);
                        var IdChain=matches[1];
						
						//���������� ����, ��������� ID ������������ ��� ������ �������, ����� ��� ��������
                        //var regex=/IdUser:([^;]*)/im;
                        //matches=response.match(regex);
                        //var IdUser=matches[1];
                        
						document.getElementById('get_chain_id').value='user: '+IdUser+' chain: '+IdChain;
						
                        //������� �������� ���������� � ������� ������ � ������� �������
                        
						xml = new window.XMLHttpRequest();
						xml.open("GET", 'put_call_to_hist.php?'
							+'call_hist_id='+call_hist_id
                            +'&base_id='+base_id
                            +'&oktell_server_address='+oktell_server_address
                            +'&okt_IdChain='+IdChain
                            +'&okt_IdUser='+IdUser
                            +'&phone_prefix='+oktell_phone_prefix
                            +'&phone_number='+phone_number
                            +'&base_id='+base_id, false);
                        xml.send("");
						
                        //alert(xml.responseText);

                        //������������� ����
                    }
					else {
						ch_callto();
						t2=setTimeout(CallMonitor,250);
					}
                },0)
	}
}
function endcall() {
		//�������� ��������� ������
		xml=new window.XMLHttpRequest();  
        xml.open("GET", 'http://127.0.0.1:4059/disconnectcall', false); 
        xml.send("");		
        //�������� ��������� ������ ����� ����������
		var t=setInterval(function() {
			xml=new window.XMLHttpRequest();  
			xml.open("GET", 'http://127.0.0.1:4059/getcurrentcallinfo', false); 
			xml.send("");
			var response=xml.responseText;
			var regex=/mode.*value="([^"]*)"/im;
			var matches=response.match(regex);

			if(matches[1]=='none') {
				
				//������ ������ ������
				ch_callto();

				//������������� ����
				clearInterval(t);
			}
		},500)
}
//������� ����� �������� ������
function ch_callto() {
	document.getElementById('callto_button').disabled=false;
	document.getElementById('endcall_button').disabled=true;
}
function ch_endcall() {
	document.getElementById('callto_button').disabled=true;
	document.getElementById('endcall_button').disabled=false;
}
function ch_oktell_disconnected() {
	document.getElementById('callto_button').disabled=true;
	document.getElementById('endcall_button').disabled=true;	
}
</script>
