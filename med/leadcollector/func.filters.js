//‘»À‹“–€--------------------------
function add_options(obj,opt_id,opt_val,opt_selected) {
	len=obj.options.length;
	obj.options[len] = new Option(opt_val,opt_id);
	if(opt_selected=='selected') obj.options[len].selected=true;
	obj.options[len].title=opt_val;
}
function add_optgroup(obj,name) {
	var optgroup = document.createElement("optgroup");
	optgroup.setAttribute("label", name);
	obj.appendChild(optgroup);
}
function filters_open(row) {
	for(i=0;i<row.cells.length;i++) {
		cell=row.cells[i];
		for(j=0;j<cell.childNodes.length;j++) {
			elem=cell.childNodes[j];
			if(elem.type=='select-multiple') elem.size=10;
		}
	}
}
function filters_close(row) {
	for(i=0;i<row.cells.length;i++) {
		cell=row.cells[i];
		for(j=0;j<cell.childNodes.length;j++) {
			elem=cell.childNodes[j];
			if(elem.type=='select-multiple') elem.size=1;
		}
	}
}
//Ú‡ÈÏÂ ÓÚÔ‡‚ÍË ÙÓÏ˚
var t;
function ch_filter() {
	clearTimeout(t);
	t=setTimeout('frm.submit()',3000);
}
//--------------------‘»À‹“–€