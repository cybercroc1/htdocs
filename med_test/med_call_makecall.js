    <script>
        var oktell_server_address='';
		//var pervStatusUser='';
        getcurrentserveraddress();
        t=setInterval("getcurrentserveraddress()",2000);
		function getcurrentserveraddress() {
            var xml;
            if(window.XMLHttpRequest) {
                xml = new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/getcurrentserveraddress', true);
                xml.timeout = 2000;
                xml.send("");
                xml.onreadystatechange = function() {
                    //alert(xml.readyState);
					if (4 === xml.readyState) {
						//alert(xml.status);
                        if(xml.status && 200 === xml.status) {
                            var response=xml.responseText;
                            var regex=/serveraddress.*><!\[CDATA\[([^\]]*)\]/im;
                            var matches=response.match(regex);
                            if(matches[1] != oktell_server_address) {
                                oktell_server_address = matches[1];
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
            if(oktell_server_address != '') {
				//������ ������ ������
                ch_callto();
                //
            }
            else {
				//������ ������ ������
                ch_oktell_disconnected();
            }
        }
        function callto(phone_number,base_id) {
            var call_hist_id='';
			var IdChain='00000000-0000-0000-0000-000000000000';
			if(oktell_server_address.value=='') {
				alert('������: ��� ����������� � ������');
				return;
			}
            else {
            //alert('���� ����������� � ������');

                //�������� ��������� ������ � ������� ������������
                xml=new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false);
                xml.send("");
                var response=xml.responseText;
                //document.getElementById('check_active_call').innerText=response;

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
                //pervStatusUser=StatusUser;
				/*if(StatusUser != '1' && StatusUser != '2' && StatusUser != '3') {
					alert('������ ������������ �� ��������� ������� ���. ������');
                    return;
                }*/
				//������ ������������ � �������
                xml=new window.XMLHttpRequest();
                xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-012-Set-User-State&async=0&timeout=10&startparam2=2', false);
                xml.send("");
                var response=xml.responseText;	
				//alert(response);
                var regex=/"returnvalue".*name="([^"]*)/im;
                matches=response.match(regex);
                var NewStatusUser=matches[1];	
				if(NewStatusUser!='2') {
					alert('�� ������� ������� ������ ������������');
					return;
				}
				
                //���� ��� ��������� ������
                //��������� ���� ������� ������ � �������� ������������� ������� ������
                //������� �������� ���������� � ������� ������ � ������� �������
                xml = new window.XMLHttpRequest();
				xml.open("GET", 'put_call_to_hist.php?anonym'
							+'&call_hist_id='
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
				
				//�������� ������� �����
                xml=new window.XMLHttpRequest();
				//alert(oktell_phone_prefix+phone_number);
                xml.open("GET", 'http://127.0.0.1:4059/callto?number='+oktell_phone_prefix+phone_number, false);
                xml.send("");
                //�������� ��������� ������ ����� ������ ������
                //����������� SetTimeout
				var iii=0;
				var t2=setTimeout(function CallMonitor() {
                    iii++;
					xml=new window.XMLHttpRequest();
                    xml.open("GET", 'http://127.0.0.1:4059/getcurrentcallinfo', false);
                    xml.send("");
                    var response=xml.responseText;
                    var regex=/mode.*value="([^"]*)"/im;
                    var matches=response.match(regex);
					//alert(response);

                    if(matches[1]=='calling' || matches[1]=='ringing' || matches[1]=='connected' || matches[1]=='flashed') {
                        //document.getElementById('check_active_calling').innerText=response;

                        //������ ������ ������
                        ch_endcall();

                        //���� ����� �������, �� �������� ID ������������ � ID ������� ����������
                        xml = new window.XMLHttpRequest();
                        xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-013-Get-Call-Info-By-Initiator&async=0&timeout=10', false);
                        xml.send("");
                        var response=xml.responseText;
						//alert(response);
                        var regex=/IdChain:([^;]*)/im;
                        matches=response.match(regex);
                        var IdChain=matches[1];
						
                        //������� �������� ���������� � ������� ������ � ������� �������
                        
						xml = new window.XMLHttpRequest();
                        xml.open("GET", 'put_call_to_hist.php?anonym'
							+'&call_hist_id='+call_hist_id
                            +'&base_id='+base_id
                            +'&oktell_server_address='+oktell_server_address
                            +'&okt_IdChain='+IdChain
                            +'&okt_IdUser='+IdUser
                            +'&phone_prefix='+oktell_phone_prefix
                            +'&phone_number='+phone_number
                            +'&base_id='+base_id, false);
                        xml.send("");
                        //alert(xml.responseText);
                    }
					else if(iii < 7) { //������ 7 ������� ����������� ����� ���������� 1,5 ���.
						t2=setTimeout(CallMonitor,250);
					}
					else {//������������� ����
						ch_callto();
					}					
                },0)
            }
        }
        function endcall() {
            //�������� ��������� ������
            xml = new window.XMLHttpRequest();
            xml.open("GET", 'http://127.0.0.1:4059/disconnectcall', false);
            xml.send("");
            //�������� ��������� ������ ����� ����������
            var t=setInterval(function() {
                xml = new window.XMLHttpRequest();
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
					/*if(pervStatusUser=='3') {
						//������ ������ ������������ �� "����������", ���� �� ����� � ���� ������� �� ���������� ������
						xml=new window.XMLHttpRequest();
						xml.open("GET", 'http://127.0.0.1:4059/execsvcscript?name=3-012-Set-User-State&async=0&timeout=10&startparam2=3', false);
						xml.send("");
					}*/	
                }
            },500)
        }
        //������� ����� �������� ������
        function ch_callto() {
            if (document.getElementById('endcall_button')) {
                document.getElementById('endcall_button').style.display = 'none';
				document.getElementById('endcall_button').disabled = true;
                document.getElementById('endcall_button').style.backgroundImage = '';
            }
			if (document.getElementById('callto_button')) {
                document.getElementById('callto_button').style.display = '';
				document.getElementById('callto_button').disabled = false;
                document.getElementById('callto_button').style.backgroundImage = 'url("<?=PATH?>/images/call.png")';
            }
        }
        function ch_endcall() {
            if (document.getElementById('callto_button')) {
				document.getElementById('callto_button').style.display = 'none';
                document.getElementById('callto_button').disabled = true;
                document.getElementById('callto_button').style.backgroundImage = '';
            }
            if (document.getElementById('endcall_button')) {
				document.getElementById('endcall_button').style.display = '';
                document.getElementById('endcall_button').disabled = false;
                document.getElementById('endcall_button').style.backgroundImage = 'url("<?=PATH?>/images/call_stop.png")';
            }
        }
        function ch_oktell_disconnected() {
			if (document.getElementById('callto_button')) {
				document.getElementById('callto_button').style.display = '';
                document.getElementById('callto_button').disabled = true;
                document.getElementById('callto_button').style.backgroundImage = '';
            }
            if (document.getElementById('endcall_button')) {
				document.getElementById('endcall_button').style.display = 'none';
                document.getElementById('endcall_button').disabled = true;
                document.getElementById('endcall_button').style.backgroundImage = '';
            }			
        }
    </script>