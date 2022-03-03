// JavaScript Document
var vInputObj;
var vChanged;
var vMouseMoved;
var vSize;
var vMultResize=new Array();

function fHeadMouseDown(obj) { //изменение размера ячеек: кнопка нажата
	fChange();
	if(obj.parentNode.rowIndex==0) {
		vMouseMoved=event.clientX;
		vSize=obj.offsetWidth;
		obj.style.cursor='e-resize';
		vMultiResize=[];
		for(i=1; i<tbl.rows[0].cells.length; i++) {
			if(tbl.rows[0].cells[i].is_selected=='y') vMultiResize[vMultiResize.length]=i;
		}
	}
	if(obj.cellIndex==0) {
		vMouseMoved=event.clientY;
		vSize=obj.offsetHeight;
		obj.style.cursor='n-resize';
		vMultiResize=[];
		for(i=1; i<tbl.rows.length; i++) {
			if(tbl.rows[i].cells[0].is_selected=='y') vMultiResize[vMultiResize.length]=i;
		}
	}
}
function fHeadMouseMove(obj) { //изменение размера ячеек: перетаскиваем
	if(vMouseMoved!=null && obj.parentNode.rowIndex==0) {
		vChanged='y';
		if(vMultiResize.length>0) {
			for(i=0; i<vMultiResize.length; i++) {
				tbl.rows[0].cells[vMultiResize[i]].style.width=vSize+(event.clientX-vMouseMoved); 
				tbl.rows[0].cells[vMultiResize[i]].innerHTML=tbl.rows[0].cells[vMultiResize[i]].style.width;
			}
		}
		else {
			obj.style.width=vSize+(event.clientX-vMouseMoved); obj.innerHTML=obj.style.width;
		}
	}
	if(vMouseMoved!=null && obj.cellIndex==0) {
		vChanged='y';
		if(vMultiResize.length>0) {
			for(i=0; i<vMultiResize.length; i++) {
				tbl.rows[vMultiResize[i]].style.height=vSize+(event.clientY-vMouseMoved); 
				//tbl.rows[vMultiResize[i]].cells[0].innerHTML=tbl.rows[vMultiResize[i]].style.height;
				document.getElementById("info_height_row"+tbl.rows[vMultiResize[i]].row_num).innerHTML=tbl.rows[vMultiResize[i]].style.height;
			}
		}
		else {	
			obj.parentNode.style.height=vSize+(event.clientY-vMouseMoved); //obj.innerHTML=obj.parentNode.style.height;
			document.getElementById("info_height_row"+obj.parentNode.row_num).innerHTML=obj.parentNode.style.height;
		}
	}
}
function fHeadMouseUp(obj) { //изменение размера ячеек: кнопка отжата
	obj.style.cursor='';
	if(obj.parentNode.rowIndex==0) {
		if(vSize==vSize+(event.clientX-vMouseMoved)) fCellSelect(obj.parentNode.rowIndex,obj.cellIndex); //шелчок мышью
		obj.style.cursor='';
	}
	if(obj.cellIndex==0) {
		if(vSize==vSize+(event.clientY-vMouseMoved)) fCellSelect(obj.parentNode.rowIndex,obj.cellIndex); //шелчок мышью
	}
	vMouseMoved=null;
}
function fSave(vSaveAs) {
fChange();
e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='save_as'; 
	e.value=vSaveAs; 
frm_edit_table.appendChild(e);
if(vChanged=='y' || vSaveAs=='template') {
	e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='table_attrib'; 
		e.value=' bgcolor="'+tbl.bgColor
		+'" real_bgcolor="'+tbl.real_bgcolor
		+'" cellspacing="'+tbl.cellSpacing
		+'" cellpadding="'+tbl.cellPadding
		+'" border="'+tbl.border
		+'" align="'+tbl.align	
		+'"'; 
	frm_edit_table.appendChild(e);
	e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='table_style'; 
		e.value='table-layout:'+tbl.style.tableLayout+';width:'+tbl.style.width+';'; 
	frm_edit_table.appendChild(e);
	e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='row_count'; 
		e.value=tbl.rows.length-1;
	frm_edit_table.appendChild(e);
	e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='col_count'; 
		e.value=tbl.rows[0].cells.length-1;
		frm_edit_table.appendChild(e);
		for(i=0; i<tbl.rows.length; i++) {
			e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='row_style['+i+']'; 
				e.value='display:'+tbl.rows[i].style.display+';'; 
			frm_edit_table.appendChild(e);

			e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='row_height['+i+']'; 
				e.value=tbl.rows[i].style.height; 
			frm_edit_table.appendChild(e);
			
			e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='row_active_head_lvl['+i+']'; 
				e.value=tbl.rows[i].active_head_lvl; 
			frm_edit_table.appendChild(e);

			for(j=0; j<tbl.rows[0].cells.length; j++) {
				
				if(i==0) {
					e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='col_width['+j+']'; 
						e.value=tbl.rows[0].cells[j].style.width; 
					frm_edit_table.appendChild(e);
					//alert(e.name+'row'+0+'col'+j+'w'+e.value);
				}
				
				if(i>0 && j>0) {
				
					e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='cell_attrib['+i+']['+j+']'; 
						e.value=' rowspan="'+tbl.rows[i].cells[j].rowSpan
						+'" colspan="'+tbl.rows[i].cells[j].colSpan
						+'" real_bgcolor="'+tbl.rows[i].cells[j].real_bgcolor
						+'" real_fontsize="'+tbl.rows[i].cells[j].real_fontsize
						+'" real_lineheight="'+tbl.rows[i].cells[j].real_lineheight
						+'" real_fontcolor="'+tbl.rows[i].cells[j].real_fontcolor
						+'" phones="'+tbl.rows[i].cells[j].phones+'"';
					frm_edit_table.appendChild(e);
					
					e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='cell_style['+i+']['+j+']'; 
						e.value='display:'+tbl.rows[i].cells[j].style.display
						+';background:'+tbl.rows[i].cells[j].real_bgcolor
						+';text-align:'+tbl.rows[i].cells[j].style.textAlign
						+';vertical-align:'+tbl.rows[i].cells[j].style.verticalAlign
						+';font-size:'+tbl.rows[i].cells[j].real_fontsize
						+';line-height:'+tbl.rows[i].cells[j].real_lineheight
						+';color:'+tbl.rows[i].cells[j].real_fontcolor
						+';';
					frm_edit_table.appendChild(e);
	
					//e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='faq['+i+']['+j+']'; 
					//	if(vSaveAs=='table') e.value=tbl.rows[i].cells[j].faq; else e.value='';
					//frm_edit_table.appendChild(e);				
										
					//if(vSaveAs=='table' || i==0 || j==0) {
					if(vSaveAs=='table') {
						e=document.createElement('input'); e.type='hidden'; e.temp='subm';	e.name='cell_html['+i+']['+j+']'; 
						e.value=tbl.rows[i].cells[j].innerHTML; 
						frm_edit_table.appendChild(e);
					}
				}
			}
		}
	}
	else {
		e=document.createElement('input'); e.type='hidden'; e.temp='subm'; e.name='no_change'; 
			e.value=''; 
		frm_edit_table.appendChild(e);
	}
frm_edit_table.action="save_table.php";
frm_edit_table.target="ifr_edit_table";
frm_edit_table.submit();
	for(i=frm_edit_table.elements.length-1; i>=0; i--) {
		obj=frm_edit_table.elements[i];
		if(frm_edit_table.elements[i].temp=='subm') {
			obj.parentNode.removeChild(obj);
		}
	}
}
//function fFaqShow() {
//	with(tbl) {
//		for(m=1; m<rows.length; m++) {
//			for(n=1; n<rows[m].cells.length; n++) {
//				if(rows[m].cells[n].faq!='') fCellSelect(null,null,'y',rows[m].cells[n]); else fCellSelect(null,null,'n',rows[m].cells[n]);
//			}
//		}
//	}
//}
//function fFaqSave() {
//vChanged='y';	
//	with(tbl) {
//		for(m=1; m<rows.length; m++) {
//			for(n=1; n<rows[m].cells.length; n++) {
//				if(rows[m].cells[n].is_selected=='y') rows[m].cells[n].faq='y'; else rows[m].cells[n].faq='';
//			}
//		}
//	}
//alert('Готово!');
//}
function fPhonesShow() {
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].phones=='y') fCellSelect(null,null,'y',rows[m].cells[n]); else fCellSelect(null,null,'n',rows[m].cells[n]);
			}
		}
	}
}
function fPhonesSave() {
vChanged='y';	
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') rows[m].cells[n].phones='y'; else rows[m].cells[n].phones='n';
			}
		}
	}
alert('Готово!');
}
function fHeadShow() {
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			if(parseInt(rows[m].active_head_lvl)>0) fCellSelect(m,0,'y',null); else fCellSelect(m,0,'n',null);
		}
	}
}
function fHeadSave() {
vChanged='y';	
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			if(rows[m].cells[0].is_selected=='y') rows[m].active_head='y'; else rows[m].active_head='n';
		}
	}
alert('Готово!');
}

function fPlusHeadLevel() {
vChanged='y';	
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			if(rows[m].cells[0].is_selected=='y') {
				if(parseInt(rows[m].active_head_lvl)>0) {
					rows[m].active_head_lvl=parseInt(rows[m].active_head_lvl)+1;
				}
				else {
					rows[m].active_head_lvl=1;
				}
				document.getElementById("info_head_lvl"+rows[m].row_num).innerHTML="lvl:"+rows[m].active_head_lvl;
			} 
		}
	}
//alert('Готово!');
}
function fMinusHeadLevel() {
vChanged='y';	
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			if(rows[m].cells[0].is_selected=='y') {
				if(parseInt(rows[m].active_head_lvl)-1>0) {
					rows[m].active_head_lvl=parseInt(rows[m].active_head_lvl)-1;
					document.getElementById("info_head_lvl"+rows[m].row_num).innerHTML="lvl:"+rows[m].active_head_lvl;
				}
				else {
					rows[m].active_head_lvl="";
					document.getElementById("info_head_lvl"+rows[m].row_num).innerHTML=rows[m].active_head_lvl;
				}
			} 
		}
	}
//alert('Готово!');
}



function fAddTag(vTag) {
	vChanged='y';
	if(vInputObj!=null) { 
		vInputObj.focus();
		if (document.selection) {
		s = document.selection.createRange(); 
			//если что-то выделенно
			if (s.text) {
				if(vTag=='remove') s.text=s.text.replace(/<.*?>/gi,'');
				else if(vTag=='href') {
					
	 if((/^( ?[\w\-]+(\.[\w\-]+)*@[\w\-]+(\.[\w\-]+)*(\.[\w\-]{2,6}) ?[,;]?)+$/i).test(s.text)) //email
	s.text='<a href=\"mailto:'+s.text.replace(/ /g,'')+'\">'+s.text+'</a>';
else if((/^ *([\w\-]+\.)+[\w\-]{2,6}(\/[\w\-]*[\.\w]*)*(\?.*)? *$/i).test(s.text))  //http
	s.text='<a href=\"http://'+s.text.replace(/ /g,'')+'\" target=\'_blank\'>'+s.text+'</a>';
else if((/^ ?#[a-z0-9\_]+/i).test(s.text)) //local
	s.text='<a href=\"'+s.text.replace(/ /g,'')+'\">'+s.text+'</a>';

else if((/^ ?[a-z]{1,6}\:\/\/.* ?$/i).test(s.text)) //полная ниет ссылка
	s.text='<a href=\"'+s.text.replace(/ /g,'')+'\">'+s.text+'</a>';
	
else if((/^#.+/i).test(clipboardData.getData('Text'))) //если буфер обмена содержит якорь
	s.text='<a href=\"'+clipboardData.getData('Text')+'\">'+s.text+'</a>';
else 
	s.text='<a href=\"'+s.text.replace(/ /g,'')+'\">'+s.text+'</a>';				
				
				}
				else if(vTag=='anker') {
					vName=s.text.replace(/[^[а-яa-z\d]+/gi,'_');
					if((/^ ?[a-z0-9]{1,5} ?$/i).test(s.text)) s.text='<a name=\"'+vName+'\"></a>';
					else s.text='<a name=\"'+vName+'\">'+s.text+'</a>';
					clipboardData.setData("Text",'#'+vName);
				}
				else s.text='<'+vTag+'>'+s.text+'</'+vTag+'>';
				s.select();
			}
			//если ничего не выделенно
			else {
				if(vTag=='anker') {
				//1. получить случайное имя
				vName=(Math.floor(Math.random()*(1048575-655360)+1)+655360).toString(16).toUpperCase();
				//2. создать якорь с полученным именем на месте курсора
				s.text='<a name="'+vName+'"></a>';
				//3. добавить перед именем решетку, и скопировать в буфер
				clipboardData.setData("Text",'#'+vName);
				}
			}
		}
	}
	else {
		if(vTag!='href'&&vTag!='anker') {
			with(tbl) {
				for(i=1; i<rows.length; i++) {
					for(j=1; j<rows[0].cells.length; j++) {
						if(rows[i].cells[j].is_selected=='y') {
							if(vTag=='remove') {rows[i].cells[j].innerHTML=rows[i].cells[j].innerHTML.replace(/<(([b^<][^r^<]>)|([^b^<^>][^<]*?>)|(b>))/gi,'');}
							else {
								//re=eval("/^(<.*>)*<"+vTag+">(.*[\\r\\n]*.*)*<\\/"+vTag+">(<.*>)*$/i");
								reBefore=eval("/^[ \\n\\r]*((<[^<^>]*>)[ \\n\\r]*)*<"+vTag+">/i");
								reEnd=eval("/<\\/"+vTag+">[ \\n\\r]*((<[^<^>]*>)[ \\n\\r]*)*$/i");
								
								if(rows[i].cells[j].innerHTML.match(reBefore) && rows[i].cells[j].innerHTML.match(reEnd)) {
									//alert('ужо');
									rows[i].cells[j].innerHTML=rows[i].cells[j].innerHTML.replace(eval("/<\\/?"+vTag+">/gi"),'');
								}
								else rows[i].cells[j].innerHTML='<'+vTag+'>'+rows[i].cells[j].innerHTML.replace(eval("/<\\/?"+vTag+">/gi"),'')+'</'+vTag+'>';
							}
						}
					}
				}
			}
		}
	}
}
function fChangeTblCellSpacing(vTblCellSpacing) {
	vChanged='y';	
	tbl.cellSpacing=vTblCellSpacing;
}
function fChangeTblLayout(vLayout) {
	vChanged='y';
	tbl.style.tableLayout=vLayout;
	tbl.rows[0].cells[0].innerHTML=tbl.style.tableLayout;
}
function fChangeTblWidth(vTblWidth) {
	vChanged='y';
	tbl.style.width=vTblWidth;
}
function fChangeTblAlign(vTblAlign) {
	vChanged='y';
	tbl.align=vTblAlign;
}
function fChangeColWidth(vColWidth) {
vChanged='y';
	//alert(vColWidth);
fChange();
	with(tbl) {
		for(j=1; j<rows[0].cells.length; j++) {
			if(rows[0].cells[j].is_selected=='y') {
				rows[0].cells[j].style.width=vColWidth;
				rows[0].cells[j].innerHTML=rows[0].cells[j].style.width;
				//for(i=0; i<rows.length; i++) {
				//	rows[i].cells[j].style.width=vColWidth;
				//	if(i==0) rows[i].cells[j].innerHTML=rows[i].cells[j].style.width;
				//}
			}
		} 
	}	
}
function fChangeRowHeight(vRowsHeight) {
vChanged='y';	
fChange();
	with(tbl) {
		for(i=1; i<rows.length; i++) {
			if(rows[i].cells[0].is_selected=='y') {
				rows[i].style.height=vRowsHeight;
				rows[i].cells[0].innerHTML=rows[i].style.height;
				//for(j=0; j<rows[i].cells.length; j++) {
				//	rows[i].cells[j].style.height=vRowsHeight;
				//	if(j==0) rows[i].cells[j].innerHTML=rows[i].cells[j].style.height;
				//}
			}
		} 
	}
}
function fChangeAlign(vTextAlign) {
vChanged='y';	
fChange();
	with(tbl) {
		for(i=1; i<rows.length; i++) {
			for(j=1; j<rows[i].cells.length; j++) {
				if(rows[i].cells[j].is_selected=='y') {
					rows[i].cells[j].style.textAlign=vTextAlign;
				}
			}
		}
	}
}
function fChangeValign(vTextValign) {
vChanged='y';
fChange();
	with(tbl) {
		for(i=1; i<rows.length; i++) {
			for(j=1; j<rows[i].cells.length; j++) {
				if(rows[i].cells[j].is_selected=='y') {
					rows[i].cells[j].style.verticalAlign=vTextValign;
				}
			}
		}
	}
}
function fPreviewFSize(obj,vEvent) {
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					if(vEvent=='over') {
						rows[m].cells[n].style.fontSize=obj.style.fontSize; 
						rows[m].cells[n].style.lineHeight=obj.style.lineHeight; 

						rows[m].cells[n].style.background=rows[m].cells[n].real_bgcolor;
					}
					if(vEvent=='out') {
						rows[m].cells[n].style.fontSize=rows[m].cells[n].real_fontsize; 
						rows[m].cells[n].style.lineHeight=rows[m].cells[n].real_lineheight; 
						
						rows[m].cells[n].style.background='yellow';
					} 
				}
			}
		}
	}
}
function fChangeFSize(obj) {
vChanged='y';	
	if(vInputObj!=null) { 
		vInputObj.focus();
		if (document.selection) {
		s = document.selection.createRange(); 
			if (s.text) {
				s.text='<font size="'+obj.style.fontSize+'">'+s.text+'</font>';
				s.select();
			}
		}
	}
else {
fChange();
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					rows[m].cells[n].style.fontSize=obj.style.fontSize;
					rows[m].cells[n].real_fontsize=obj.style.fontSize;
					
					rows[m].cells[n].style.lineHeight=obj.style.lineHeight;
					rows[m].cells[n].real_lineheight=obj.style.lineHeight;
				}
			}
		}
		//fCellSelect(0,0,'n',null);
	}
}
}
function fPreviewBgColor(obj,vEvent) {
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					if(vEvent=='over') rows[m].cells[n].style.background=obj.bgColor;
					if(vEvent=='out') rows[m].cells[n].style.background='yellow'; 
				}
			}
		}
	}
}
function fPreviewTblBgColor(obj,vEvent) {
	if(vEvent=='over') tbl.bgColor=obj.bgColor;
	if(vEvent=='out') tbl.bgColor=tbl.real_bgcolor; 
}
function fChangeTblBgColor(obj) {
	vChanged='y';
	tbl.bgColor=obj.bgColor;
	tbl.real_bgcolor=obj.bgColor;
}
function fPreviewFontColor(obj,vEvent) {
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					//alert(obj.style.color)
					if(vEvent=='over') {rows[m].cells[n].style.color=obj.bgColor; rows[m].cells[n].style.background=rows[m].cells[n].real_bgcolor;}
					if(vEvent=='out') {rows[m].cells[n].style.color=rows[m].cells[n].real_fontcolor; rows[m].cells[n].style.background='yellow';}
				}
			}
		}
	}
}
function fChangeBgColor(obj) {
vChanged='y';
	if(vInputObj!=null) { 
		vInputObj.focus();
		if (document.selection) {
		s = document.selection.createRange(); 
			if (s.text) {
				s.text='<font style="background:'+obj.bgColor+';">'+s.text+'</font>';
				s.select();
			}
		}
	}
else {
fChange();
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					rows[m].cells[n].style.background=obj.bgColor;
					rows[m].cells[n].real_bgcolor=obj.bgColor;
				}
			}
		}
	}
//fCellSelect(0,0,'n',null);
}
}
function fChangeFontColor(obj) {
	vChanged='y';	
	if(vInputObj!=null) { 
		vInputObj.focus();
		if (document.selection) {
		s = document.selection.createRange(); 
			if (s.text) {
				s.text='<font color="'+obj.bgColor+'">'+s.text+'</font>';
				s.select();
			}
		}
	}
else {
fChange();
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					rows[m].cells[n].style.color=obj.bgColor;
					rows[m].cells[n].real_fontcolor=obj.bgColor;
				}
			}
		}
	}
//fCellSelect(0,0,'n',null);
}
}
function fSplitCell() {
vChanged='y';	
fChange();
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[m].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					if(rows[m].cells[n].colSpan>1 || rows[m].cells[n].rowSpan>1) {
						vSplitCols=rows[m].cells[n].colSpan-1;
						rows[m].cells[n].colSpan=1;
						vSplitRows=rows[m].cells[n].rowSpan-1;
						rows[m].cells[n].rowSpan=1;
						for(g=m; g<=m+vSplitRows; g++) {
							for(h=n; h<=n+vSplitCols; h++) {
								rows[g].cells[h].style.display='';
								//rows[g].cells[h].style.width=rows[0].cells[h].style.width;
								//rows[g].cells[h].style.height=rows[g].cells[0].style.height;
								fCellSelect(g,h,'y',null);
							}
						} 							
					}
				}				
			}
		}
		//fCellSelect(0,0,'n',null);
	}
}
function fAppendCell() {
vChanged='y';	
fChange();
var vCount=0;
	with(tbl) {
		for(m=1; m<rows.length; m++) {
			for(n=1; n<rows[0].cells.length; n++) {
				if(rows[m].cells[n].is_selected=='y') {
					if(vCount==0) {
						vMinSelRowIdx=m; vMaxSelRowIdx=m+rows[m].cells[n].rowSpan-1; 
						vMinSelColIdx=n; vMaxSelColIdx=n+rows[m].cells[n].colSpan-1;
					}
					if(m<vMinSelRowIdx) vMinSelRowIdx=m;
					if(m+rows[m].cells[n].rowSpan-1>vMaxSelRowIdx) {
						vMaxSelRowIdx=m+rows[m].cells[n].rowSpan-1;
					}
					if(n<vMinSelColIdx) vMinSelColIdx=n;
					if(n+rows[m].cells[n].colSpan-1>vMaxSelColIdx) {
						vMaxSelColIdx=n+rows[m].cells[n].colSpan-1;
					}
					vCount++;
				}	
			}
		}
		//fCellSelect(0,0,'n',null);
		if(vCount>=2) {
			for(m=vMaxSelRowIdx; m>=vMinSelRowIdx;  m--) {
				for(n=vMaxSelColIdx; n>=vMinSelColIdx; n--) {
					if(m+rows[m].cells[n].rowSpan-1>vMaxSelRowIdx) {
						vMaxSelRowIdx=m+rows[m].cells[n].rowSpan-1; 
						m=vMaxSelRowIdx; 
						n=vMaxSelColIdx;
						}
					if(n+rows[m].cells[n].colSpan-1>vMaxSelColIdx) {
						vMaxSelColIdx=n+rows[m].cells[n].colSpan-1; 
						n=vMaxSelColIdx; 
						m=vMaxSelRowIdx;
						}
					if(m>vMinSelRowIdx || n>vMinSelColIdx)	{
						rows[m].cells[n].colSpan=1;
						rows[m].cells[n].rowSpan=1;
						rows[m].cells[n].style.display='none';
						fCellSelect(m,n,'n',null);
					}				
				}	
			}
			rows[vMinSelRowIdx].cells[vMinSelColIdx].rowSpan=vMaxSelRowIdx-vMinSelRowIdx+1;
			//if(vMaxSelRowIdx-vMinSelRowIdx+1>1) rows[vMinSelRowIdx].cells[vMinSelColIdx].style.height='';
			
			rows[vMinSelRowIdx].cells[vMinSelColIdx].colSpan=vMaxSelColIdx-vMinSelColIdx+1;
			//if(vMaxSelColIdx-vMinSelColIdx+1>1) rows[vMinSelRowIdx].cells[vMinSelColIdx].style.width='';
		}
	}
}
function fAddRowColAfterSelected() {
	with(tbl) {
		for(m=rows.length-1; m>=0; m--) {
			if(rows[m].cells[0].is_selected=='y') {
				fAddRow(m,0);
			}
		}
		for(n=rows[0].cells.length-1; n>=0; n--) {
			if(rows[0].cells[n].is_selected=='y') {
				fAddCol(n,0);
			}
		}
	}
} 
function fAddRowColBeforeSelected() {
	with(tbl) {
		for(m=rows.length-1; m>=1; m--) {
			if(rows[m].cells[0].is_selected=='y') {
				fAddRow(m-1,2);
			}
		}
		for(n=rows[0].cells.length-1; n>=1; n--) {
			if(rows[0].cells[n].is_selected=='y') {
				fAddCol(n-1,2);
			}
		}
	}
} 
function fAddRow(vRowIdx,z) {
vChanged='y';	
fChange();
	with(tbl) {
		//alert(rows.length+rows[0].cells.length);
		if((rows.length-1)*(rows[0].cells.length-1)>=2000) {alert('ОШИБКА! Достигнуто ограничение на количество ячеек: 2000'); return false;}
		var vSpannedCols;
		var aHiddenCells = new Array();
		if(z==0) x=1; else x=0;
		
		new_row=insertRow(vRowIdx+1);
		if(vRowIdx==0 && z==0) new_row.style.height=60;
		else if(rows[vRowIdx+z].style.height!='') new_row.style.height=rows[vRowIdx+z].style.height; 
		else new_row.style.height=25;
		for(j=0; j<rows[0].cells.length; j++) {
			new_cell=new_row.insertCell(j);
			if(vRowIdx<rows.length-2 && (j>0 || z==2)) {		
				for(i=x; i<=vRowIdx+1; i++) {
					
					if(rows[i].cells[j].rowSpan>1 && i<=vRowIdx) {
						
						if(rows[i].cells[j].rowSpan+i-1>=vRowIdx && i<=vRowIdx) {
							
							if(rows[i].cells[j].rowSpan+i-1>vRowIdx) {rows[i].cells[j].rowSpan++;}
							if(rows[i].cells[j].rowSpan+i-1>vRowIdx+1) vSpannedCols=rows[i].cells[j].colSpan+j-1;
							
						}	
					}					
				}
			}
			if(j<=vSpannedCols) aHiddenCells[aHiddenCells.length]=j; 			
			
			new_cell.style.background=rows[vRowIdx+z].cells[j].real_bgcolor;
			new_cell.real_bgcolor=rows[vRowIdx+z].cells[j].real_bgcolor;
			
			new_cell.style.fontSize=rows[vRowIdx+z].cells[j].real_fontsize;
			new_cell.real_fontsize=rows[vRowIdx+z].cells[j].real_fontsize;

			new_cell.style.lineHeight=rows[vRowIdx+z].cells[j].real_lineheight;
			new_cell.real_lineheight=rows[vRowIdx+z].cells[j].real_lineheight;
			
			new_cell.style.color=rows[vRowIdx+z].cells[j].real_fontcolor;
			new_cell.real_fontcolor=rows[vRowIdx+z].cells[j].real_fontcolor;			
			
			new_cell.style.textAlign=rows[vRowIdx+z].cells[j].style.textAlign;
			new_cell.style.verticalAlign=rows[vRowIdx+z].cells[j].style.verticalAlign;
			
			//if(rows[vRowIdx].cells[j].faq!='') new_cell.faq='y'; else new_cell.faq='';
			
			new_cell.phones=rows[vRowIdx].cells[j].phones;
			
			//new_cell.style.width=rows[0].cells[j].style.width;
			
			if(vRowIdx==0 && z==0) {
				//rows[vRowIdx+1].cells[0].style.height=60;
				//new_cell.style.height=60; 
				new_cell.style.textAlign='left'; new_cell.style.verticalAlign='top';	
				new_cell.style.fontSize='12px';	new_cell.real_fontsize='12px';
				new_cell.style.lineHeight='16px'; new_cell.real_lineheight='16px';
			}
			//else if(j==0) new_cell.style.height=rows[vRowIdx+z].cells[0].style.height; 
			
			new_cell.is_selected='n';
			new_cell.unselectable="on";

			if(j==0) {
				//new_cell.faq='';
				new_cell.phones='n';
				//new_cell.onclick=function(){fCellSelect(this.parentNode.rowIndex,this.cellIndex)};
				//new_cell.unselectable="on";				
				new_cell.onmousedown=function(){fHeadMouseDown(this)}; 
				new_cell.onmouseup=function(){fHeadMouseUp(this)};
				new_cell.onmousemove=function(){fHeadMouseMove(this)};
				new_cell.onmouseout=function(){fHeadMouseUp(this)};
				new_cell.innerHTML=new_row.style.height;
				new_cell.style.verticalAlign='middle';
				new_cell.style.textAlign='center';
				new_cell.style.fontSize='10px';
				new_cell.real_fontsize='10px';
			}
			else {
				new_cell.edited='n';
				new_cell.onmousedown=function(){fCellClick(this)};
				//new_cell.onclick=function(){fCellEdit(this)};
				//new_cell.ondblclick=function(){fCellSelect(null,null,null,this)};
			}
		}
	for(i=aHiddenCells.length-1; i>=0; i--) {new_row.cells[aHiddenCells[i]].style.display='none';}
	}
}
function fAddCol(vCellIdx,z) {
vChanged='y';	
fChange();
	with(tbl) {
		if((rows.length-1)*(rows[0].cells.length-1)>=2000) {alert('ОШИБКА! Достигнуто ограничение на количество ячеек: 2000'); return false;}		
		var vSpannedRows=null;
		var aHiddenCells = new Array();
		if(z==0) x=1; else x=0;
		for(i=0; i<rows.length; i++) {
			if(vCellIdx<rows[0].cells.length-2 && (vCellIdx>0 || z==2)) {
			
			for(j=x; j<=vCellIdx+1; j++) {
				
				if (j<=vCellIdx) aHiddenCells[j]=rows[i].cells[j].style.display;
				if (j>=vCellIdx) aHiddenCells[j+1]=rows[i].cells[j].style.display;
				rows[i].cells[j].style.display='';
				
				
				
				if(rows[i].cells[j].colSpan>1 && j<=vCellIdx) {
					aHiddenCells[j+1]='none';
					if(rows[i].cells[j].colSpan+j-1>=vCellIdx && j<=vCellIdx) {
						if(rows[i].cells[j].colSpan+j-1>vCellIdx) {rows[i].cells[j].colSpan++;}
						if(rows[i].cells[j].colSpan+j-1==vCellIdx) {vSpannedRows=rows[i].cells[j].rowSpan+i-1;}
						
					}	
				}

			}

				if(i>1 && rows[i-1].cells[vCellIdx].rowSpan>1 && (rows[i-1].cells[vCellIdx].colSpan<2 || rows[i-1].cells[vCellIdx].colSpan==null)) {
					vSpannedRows=rows[i-1].cells[vCellIdx].rowSpan+i-2;				
				}

			}
			new_cell=rows[i].insertCell(vCellIdx+1);
			for(j=x; j<aHiddenCells.length; j++) {
				if(i>0) {
				rows[i].cells[j].style.display=aHiddenCells[j];
				}
			}
			if(i<=vSpannedRows) {
				
				new_cell.style.display='';
			} 

			new_cell.style.background=rows[i].cells[vCellIdx+z].real_bgcolor;
			new_cell.real_bgcolor=rows[i].cells[vCellIdx+z].real_bgcolor;

			new_cell.style.fontSize=rows[i].cells[vCellIdx+z].real_fontsize;
			new_cell.real_fontsize=rows[i].cells[vCellIdx+z].real_fontsize;

			new_cell.style.lineHeight=rows[i].cells[vCellIdx+z].real_lineheight;
			new_cell.real_lineheight=rows[i].cells[vCellIdx+z].real_lineheight;

			new_cell.style.color=rows[i].cells[vCellIdx+z].real_fontcolor;
			new_cell.real_fontcolor=rows[i].cells[vCellIdx+z].real_fontcolor;
			
			new_cell.style.textAlign=rows[i].cells[vCellIdx+z].style.textAlign;
			new_cell.style.verticalAlign=rows[i].cells[vCellIdx+z].style.verticalAlign;
			
			//new_cell.faq='';
			
			new_cell.phones='';
			
			new_cell.is_selected='n';
			new_cell.unselectable="on";

			//new_cell.style.height=rows[i].cells[0].style.height;
			
			if(vCellIdx==0 && z==0) {
			rows[0].cells[vCellIdx+1].style.width=250;
			//new_cell.style.width=250; 
			new_cell.style.textAlign='left'; new_cell.style.verticalAlign='top';	
			new_cell.style.fontSize='12px';	new_cell.real_fontsize='12px';
			new_cell.style.lineHeight='16px'; new_cell.real_lineheight='16px';
			}
			else if(i==0) new_cell.style.width=rows[0].cells[vCellIdx+z].style.width;		

			if(i==0) {
				//new_cell.unselectable="on";				
				new_cell.onmousedown=function(){fHeadMouseDown(this)}; 
				new_cell.onmouseup=function(){fHeadMouseUp(this)};
				new_cell.onmousemove=function(){fHeadMouseMove(this)};				
				new_cell.onmouseout=function(){fHeadMouseUp(this)};
				//new_cell.onclick=function(){fCellSelect(this.parentNode.rowIndex,this.cellIndex)};
				new_cell.innerHTML=new_cell.style.width;
				new_cell.style.textAlign='center';
				new_cell.style.verticalAlign='middle';
				new_cell.style.fontSize='10px';
				new_cell.real_fontsize='10px';
			}
			else {
				new_cell.edited='n';
				new_cell.onmousedown=function(){fCellClick(this)};
				//new_cell.onclick=function(){fCellEdit(this)};
				//new_cell.ondblclick=function(){fCellSelect(null,null,null,this)};				
			}
		}
	cols=rows[0].cells.length;
	}
}
function fDelSelectedRowCol() {
	if(!confirm('Действительно УДАЛИТЬ выделенные СТОЛБЦЫ и СТРОКИ?')) return false;
	with(tbl) {
		for(m=rows.length-1; m>=1; m--) {
			if(rows[m].cells[0].is_selected=='y') {
				fDelRow(m);
			}
		}
		for(n=rows[0].cells.length-1; n>=1; n--) {
			if(rows[0].cells[n].is_selected=='y') {
				fDelCol(n);
			}
		}
	}
} 
function fDelRow(vRowIdx) {
vChanged='y';	
fChange();
	with(tbl) {
		for(i=0; i<rows[0].cells.length; i++) {
			if(rows[vRowIdx].cells[i].rowSpan>1) {
				rows[vRowIdx+1].cells[i].style.display='';
				rows[vRowIdx+1].cells[i].colSpan=rows[vRowIdx].cells[i].colSpan;
				rows[vRowIdx+1].cells[i].rowSpan=rows[vRowIdx].cells[i].rowSpan-1;
			}
			for(j=1; j<vRowIdx; j++) {
				if(rows[j].cells[i].rowSpan+j-1>=vRowIdx) rows[j].cells[i].rowSpan--;
			}	
		}
		
		
		deleteRow(vRowIdx);
	}
}
function fRowMove(vSrc,vIncr) {
vChanged='y';
fChange();
	with(tbl) {
		if(vIncr>0) {i=rows.length-1; a=0; b=-1;}
		if(vIncr<0) {i=0; a=rows.length; b=1;}		
		for(i=i; i!=a; i=i+b) {
			if(rows[i].cells[0].is_selected=='y' && rows[i+vIncr].cells[0].is_selected!='y') {
				if(i+vIncr>0 && i+vIncr<rows.length) {
					ok='ok';
					for(j=1; j<rows[0].cells.length; j++) {
					
					//	if(rows[i].cells[j].rowSpan<2 && rows[i].cells[j].colSpan>1) {
					//		j=j+rows[i].cells[j].colSpan-1; continue;
					//	}
					//	else if(rows[i+vIncr].cells[j].rowSpan<2 && rows[i+vIncr].cells[j].colSpan>1) {
					//		j=j+rows[i+vIncr].cells[j].colSpan-1; continue;
					//	}
					//	vRowSpan=rows[i].cells[j].rowSpan; vColSpan=rows[i].cells[j].colSpan; vDisplay=rows[i].cells[j].style.display;
					//	rows[i].cells[j].rowSpan=rows[i+vIncr].cells[j].rowSpan;
					//	rows[i].cells[j].colSpan=rows[i+vIncr].cells[j].colSpan;
					//	rows[i].cells[j].style.display=rows[i+vIncr].cells[j].style.display;
					//	rows[i+vIncr].cells[j].rowSpan=vRowSpan;
					//	rows[i+vIncr].cells[j].colSpan=vColSpan;
					//	rows[i+vIncr].cells[j].style.display=vDisplay;
					
						if(rows[i].cells[j].rowSpan>1 || rows[i].cells[j].style.display!=rows[i+vIncr].cells[j].style.display) {
							ok='stop';
							//return false;
						}
					}
					
					if(ok=='ok') moveRow(i,i+vIncr);
				}
				else return false;
			}
		}
	}
}
function fDelCol(vCellIdx) {
vChanged='y';	
fChange();
	with(tbl) {
		for(i=0; i<rows.length; i++) {
			if(rows[i].cells[vCellIdx].colSpan>1) {
				rows[i].cells[vCellIdx+1].style.display='';
				rows[i].cells[vCellIdx+1].colSpan=rows[i].cells[vCellIdx].colSpan-1;
				rows[i].cells[vCellIdx+1].rowSpan=rows[i].cells[vCellIdx].rowSpan;
			}
			for(j=1; j<vCellIdx; j++) {
				if(rows[i].cells[j].colSpan+j-1>=vCellIdx) rows[i].cells[j].colSpan--;
			}	
			rows[i].cells[vCellIdx].style.display='';
			rows[i].deleteCell(rows[i].cells[vCellIdx].cellIndex);
		}
	cols=rows[0].cells.length;
	}	
}

function fCellClick(obj) {
	if(event.ctrlKey) {fCellSelect(null,null,null,obj); return false;}
	else {fCellEdit(obj);}
}

function fChange() {
	for(x=0; x<frm_edit_table.elements.length; x++) {
		if(frm_edit_table.elements[x].temp=='temp') {
			vChanged='y';
			obj=frm_edit_table.elements[x];
			obj.parentNode.edited='n';
			//obj.parentNode.onclick=function(){fCellEdit(this)};
			obj.parentNode.onmousedown=function(){fCellClick(this)};			
			obj.parentNode.innerHTML=obj.value.replace(/\r\n|\r|\n/g,"<BR>").replace(/(  )/,"&nbsp;&nbsp;").replace(/ href=/g,' [href]=').replace(/ src=/g,' [src]=');
			vInputObj=null;
		}
	}
vInputObj=null;
}

function fCellEdit(obj) {
	fChange();
	if(obj.edited=='n') {
		vWdth=obj.offsetWidth;
		vHeight=obj.offsetHeight;
		obj.innerHTML="<textarea wrap=hard onpaste='fPaste(this)' onmousedown='if(event.ctrlKey){fChange();}'>"+obj.innerHTML.replace(/ \[href\]=/g," href=").replace(/ \[src\]=/g,' src=').replace(/\r\n|\r|\n/g,"").replace(/<BR>/g,'\n')+"</textarea>";
		obj.onmousedown='';
		inp=obj.childNodes[0];
		inp.style.textAlign=obj.style.textAlign;
		inp.style.verticalAlign=obj.style.verticalAlign;
		inp.style.fontSize=obj.currentStyle.fontSize;
		inp.style.lineHeight=obj.currentStyle.lineHeight;
		inp.style.fontFamily=obj.currentStyle.fontFamily;		
		inp.style.color=obj.style.color;
		
		inp.style.background=obj.real_bgcolor;
		
		inp.style.border=0;
		inp.style.overflow='hidden';
		inp.style.width='100%';
		inp.style.height=vHeight;
		inp.temp='temp';
		vInputObj=inp;
		obj.edited='y';	
		inp.focus();
	}
}

function fPaste(obj) {
vChanged='y';	
	with(tbl) {
	vRowNum=obj.parentNode.parentNode.rowIndex;
	//vCellNum=obj.parentNode.cellIndex;
	for(n=1; n<rows[0].cells.length; n++) {
		if(obj.parentNode==rows[vRowNum].cells[n]) {vCellNum=n; break;}
	}
	vClipBoard=clipboardData.getData('Text');
		if((vClipBoard.search('	')>-1 || vClipBoard.search(/\n|\r\n/)>-1) && confirm('Буфер обмена содержит многострочный текст и символы табуляции!\n\rОК - разделить текст по ячейкам,\n\rпри этом строки будут добавлены, а занчения по столбцам заменены\n\rОТМЕНА - вставить всё в текущую ячейку')) {
		event.returnValue=false;
		vRows=vClipBoard.split(/\n|\r\n/);
			for(m=0; m<vRows.length; m++) {
				if(m>0) { //вставляем с добавлением строк
				//if(vRowNum+m>=rows.length) { //вставляем с заменой содержимого ячеек
					fAddRow(vRowNum+m-1,0);
				}			
				vVals=vRows[m].replace('	',' 	 ').split(/	/);
				for(n=0; n<vVals.length; n++) {
					if(vCellNum+n>=rows[vRowNum+m-1].cells.length) {
						fAddCol(vCellNum+n-1,0);
					}
					rows[vRowNum+m].cells[vCellNum+n].innerHTML=vVals[n].replace(/(^\s+)|(\s+$)/g, "").replace(/ href=/g,' [href]=').replace(/ src=/g,' [src]=');
					//rows[vRowNum+m].cells[vCellNum+n].onclick=function(){fCellEdit(this)};
					rows[vRowNum+m].cells[vCellNum+n].edited='n';
				}
			}	
		}
	}
}

function fCellSelect(vRowIdx,vCellIdx,vSel,obj) {
fChange();
var vSelColor='yellow';
	with(tbl) {
		if(obj==null) {
			if(vSel==null) {
				if(rows[vRowIdx].cells[vCellIdx].is_selected=='y') {
					vSel='n';
				}
				else {
					vSel='y'
				}
			}
		}
		else {
			if(vSel==null) {
				if(obj.is_selected=='y') {
					vSel='n';
				}
				else {
					vSel='y'
				}
			}
		}
		if(obj==null) {
			if(vRowIdx==0) {vStartRowIdx=0; vEndRowIdx=rows.length-1;} else {vStartRowIdx=vRowIdx; vEndRowIdx=vRowIdx;}
			if(vCellIdx==0) {vStartCellIdx=0; vEndCellIdx=rows[vRowIdx].cells.length-1;} else {vStartCellIdx=vCellIdx; vEndCellIdx=vCellIdx;}
			for(i=vStartRowIdx; i<=vEndRowIdx; i++) {
				for(j=vStartCellIdx; j<=vEndCellIdx; j++) {
					
					if(vSel=='y') rows[i].cells[j].style.background=vSelColor;
					else rows[i].cells[j].style.background=rows[i].cells[j].real_bgcolor;
					
					rows[i].cells[j].is_selected=vSel;
				}
			}		
		}
		else {
			if(vSel=='y') obj.style.background=vSelColor;
			else obj.style.background=obj.real_bgcolor;
			obj.is_selected=vSel;
		}
	}
}