/***********************************
/***********************************
*
* -- This the jsEdit class
*
***********************************/

function jsEdit_class(name, box, recid) {
	// public properties
    this.name  	  		= name;
    this.box      		= box; // HTML element that hold this element
    this.tmpl			= '';
    this.items    		= []; // items received from getData
    this.controls   	= [];
    this.groups    		= [];
    this.srvFile  		= '';
    this.srvParams  	= [];
    this.header   		= 'Edit Page';
    this.showHeader 	= true;
	this.showFooter 	= true;
    this.recid      	= recid;
    this.msgEmpty   	= 'Some of the required fields are empty:';
    this.msgWrong   	= 'The type of the following fields is wrong:';
	this.getOneList 	= false; // indicates weather need to refresh one field only

	this.onOutput;
	this.onComplete;
    this.onSave;
    this.onSaveDone;
    this.onData;
    this.onDataRecieved;
	this.onResize;

    // public methods
    this.addControl  	= jsEdit_addControl;
	this.addGroup 	 	= jsEdit_addGroup;
    this.getData     	= jsEdit_getData;
    this.dataReceived 	= jsEdit_dataReceived;
    this.findField   	= jsEdit_findField;
    this.saveData	 	= jsEdit_saveData;
    this.saveDone	 	= jsEdit_saveDone;
    this.output      	= jsEdit_output;
    this.refresh     	= jsEdit_refresh;
    this.resetFields 	= jsEdit_resetFields;
	this.refreshList	= jsEdit_refreshList;
    this.serverCall  	= jsEdit_serverCall;
    this.showStatus 	= jsEdit_showStatus;
    this.hideStatus  	= jsEdit_hideStatus;

    // internal
    this.fieldIndex  	= 0;
    this.fieldList   	= 0;
    this.validate	 	= jsEdit_validate;
    this.getControls 	= jsEdit_getControls;
    this.getList     	= jsEdit_getList;
    this.getListDone 	= jsEdit_getListDone;
	this.resize	 	 	= jsEdit_resize;
	this.lookup_items 	= [];
	this.lookup_keyup  	= jsEdit_lookup_keyup;
	this.lookup_blur 	= jsEdit_lookup_blur;
	this.lookup_show 	= jsEdit_lookup_show

    if (!top.jsUtils) alert('The jsUtils class is not loaded. This class is a must for the jsEdit class.');
    if (!top.jsGroup) alert('The jsGroup class is not loaded. This class is a must for the jsEdit class.');
    if (!top.elements) top.elements = [];
    if (top.elements[this.name]) alert('The element with this name "'+ this.name +'" is already registered.');
    top.elements[this.name] = this;
}
top.jsEdit = jsEdit_class;

// ==============-------------------------------
// -------------- IMPLEMENTATION

// ------------- List Itself ---

function jsEdit_addControl(type, caption, param, inLabel) {
	ind = this.controls.length;
	switch (type) {
    	case 'save':
        	html = '<input id="'+ this.name +'_control'+ ind + '" class="rButton" type="button" onclick="top.elements[\''+ this.name + '\'].saveData();" value="'+ caption + '" '+ inLabel +'>';
            break;
    	case 'back':
        	html = '<input id="'+ this.name +'_control'+ ind + '" type="button" class="rButton" onclick="obj = top.elements[\''+ this.name +'\'].onComplete; if (obj) { if (obj.output) obj.output(); else obj(); }" value="'+ caption + '" '+ inLabel +'>';
            break;
    	case 'button':
        	html = '<input id="'+ this.name +'_control'+ ind + '" class="rButton" type="button" onclick="'+ param + '" value="'+ caption + '" '+ inLabel +'>';
            break;
        default:
        	html = caption;
            break;
    }
	this.controls[this.controls.length] = html;
}

function jsEdit_addGroup(name, header, inTag, outTag) {
	ind = this.groups.length;
	this.groups[ind] = new top.jsGroup(name, header, inTag, outTag);
    this.groups[ind].owner = this;
    return this.groups[ind];
}

function jsEdit_output() {
	if (this.onOutput) { this.onOutput(); }
	// fill drop lists if any
	flag = false;
    for (i=0; i<this.groups.length; i++) {
    	grp = this.groups[i];
        for (j=0; j<grp.fields.length; j++) {
        	fld = grp.fields[j];
	        if (String(fld.type).toUpperCase() == 'LIST' && !fld.items) {
            	this.getList(fld);
            	flag = true;
	        }
	        if (String(fld.type).toUpperCase() == 'RADIO' && !fld.items) {
            	this.getList(fld);
            	flag = true;
	        }
	        if (String(fld.type).toUpperCase() == 'CHECK' && !fld.items) {
            	this.getList(fld);
            	flag = true;
	        }
        }
    }
    if (flag) return;
    // finalize
    this.refresh();
	this.getData();
}

function jsEdit_refresh() {
    html =  '<div id="edit_'+ this.name +'" class="edit_div">';
	// first generate header
    if (this.showHeader) {
	    html += '<div id="header_'+ this.name +'">\n'+
	            '   <table style="width: 100%; height: 28px;" class="editHeader_tbl"><tr>\n'+
	            '       <td id="title_td1_'+ this.name + '" style="width: 95%; padding-left: 5px;">'+
				'			<span id="title_'+ this.name + '">'+ this.header +'&nbsp;</span>'+
	            '			<span style="font-variant: normal; font-size: 10px; font-family: verdana; padding: 1px; display: none; background-color: red; color: white;" id="status_'+ this.name + '"></span>'+
	            '       </td>\n'+
	            '       <td id="title_td2_'+ this.name + '" align="right" style="width: 5%" nowrap="nowrap"></td>\n'+
	            '   </tr></table>\n'+
	            '</div>\n';
    }
    // then groups and controls
    html += '<div id="body_'+ this.name +'" style="padding-top: 8px;">';
    frm   = '<form style="margin: 0px; font-size: 1px;" id="form_'+ this.name +'" name="form_'+ this.name +'" target="frame_'+ this.name +'" enctype="multipart/form-data" method="POST">';
	if (this.tmpl.indexOf('~form~') > 0) {
		html += this.tmpl.replace('~form~', frm);
	} else {
		html += frm;
		html += this.tmpl;
	}	
	
    for (ii=0; ii<this.groups.length; ii++) {
    	var gr = this.groups[ii];
		if (gr.disabled == true) {
			html = html.replace('~'+gr.name+'~', '');
			continue;
		}
		if (gr.object != null) {
			if (gr.height != null) addH = 'style="border: 0px; padding: 0px; height: '+ parseInt(gr.height)+ 'px;"'; else addH = 'style="border: 0px; padding: 0px;"';
			html = html.replace('~'+gr.name+'~', "<div class=\"group\" id=\"group_"+ gr.name +"_object\" "+ addH +"></div>");
		} else {
			html = html.replace('~'+gr.name+'~', gr.build());
		}
    }
	if (this.tmpl.indexOf('~/form~') > 0) {
		html = html.replace('~/form~', '</form>');
	} else {
		html += '</form>';
	}	
	html += '<iframe id="frame_'+ this.name +'" name="frame_'+ this.name +'" frameborder="0" style="width: 1px; height: 1px;"></iframe>';
    html += '</div>';

	if (html.indexOf('~controls~') > 0) {
		html = html.replace('~controls~', this.getControls());
	}
    if (this.showFooter) {
		html += '<div id="footer_'+ this.name +'">\n'+
				'   <table style="width: 100%;" class="editFooter_tbl"><tr>\n'+
				'       <td align="center">'+ this.getControls() +'</td>\n'+
				'   </tr></table>\n'+
				'</div>\n';
    }
    html += '</div>';

	if (this.box) this.box.innerHTML = html;
	this.resize();
	
	// output group objects if any
    for (ii=0; ii<this.groups.length; ii++) {
    	var gr = this.groups[ii];
		if (this.box && gr.object != null) {
			var div = this.box.ownerDocument.getElementById("group_"+ gr.name +"_object");
			gr.object.box = div;
			gr.object.recid = gr.owner.recid;
			gr.object.output();
		}
    }	
}

function jsEdit_resize() {
	if (!this.box) return;
	var width  = parseInt(this.box.style.width);
	var height = parseInt(this.box.style.height);
	if (height > 0) {
		var el = this.box.ownerDocument.getElementById('body_'+ this.name);
		if (el) {
			hheight = height - (this.showHeader ? 33 : 0) - (this.showFooter ? 41 : 0) - (document.all ? 0 : 4);
			el.style.overflow = 'auto';
			el.style.height   = hheight;
		}
	}
	if (this.onResize) this.onResize(width, hheight);
}

function jsEdit_lookup_show(name) {
	if (!this.box) return;
	var html;
	var div;
	var elid;
	div = this.box.ownerDocument.getElementById(name + '_div');
	html = '';
	var  k = 0;
	for (var item in this.lookup_items) {
		elid = name +'_item'+ k;
		if (this.currentField == k) { 
			addstyle = 'background-color: highlight; color: white;'; 
		} else { 
			addstyle = 'background-color: white; color: black;'; 
		}
		html += '<div id="'+ elid +'" style="padding: 2px; margin: 2px; cursor: default;'+ addstyle +'" '+
				'	onclick="this.style.backgroundColor = \'highlight\'; '+
				'			 this.style.color = \'white\'; '+
				'			 document.getElementById(\''+ name +'\').value = \''+ item +'\'; '+
				'			 document.getElementById(\''+ name +'_search\').value = \''+ this.lookup_items[item] +'\'; '+
				'			 document.getElementById(\''+ name +'_div\').style.display = \'none\'; '+
				'			 top.jsUtils.clearShadow(document.getElementById(\''+ name +'_div\')); '+
				'			 var el = document.getElementById(\''+ name +'\'); '+
				'	 		 if (el.onchange) el.onchange(el.value);\"'+
				'>'+
					this.lookup_items[item] +
				'</div>';
		k++;
	}
	if (div && div.innerHTML != html) {
		div.innerHTML = html;
		div.style.display = '';
		if (div.shadow) top.jsUtils.clearShadow(div);
		div.shadow = top.jsUtils.dropShadow(div);
	}
	if (html == '') {
		top.jsUtils.clearShadow(div);
		div.style.display = 'none';
	}
}

function jsEdit_lookup_keyup(el, name, evnt) {
	if (!this.box) return;
	// events
	if (evnt.keyCode == 9 || evnt.keyCode == 37 || evnt.keyCode == 39 ||
		evnt.keyCode == 16||evnt.keyCode == 17|| evnt.keyCode == 18 || evnt.keyCode == 20) return; 
	if (evnt.keyCode == 38) { // up
		this.currentField -= 1;
		if (this.currentField <= 0) this.currentField = 0;
		this.lookup_show(name);
		evnt.cancelBubble = true;
		evnt.stopPropagation();
		return false;
	}
	if (evnt.keyCode == 40) { // down
		this.currentField += 1;
		var cnt = 0;
		for (item in this.lookup_items) cnt++;
		if (this.currentField >= cnt) { this.currentField -= 1; }
		this.lookup_show(name);
		evnt.cancelBubble = true;
		evnt.stopPropagation();
		return false;
	}
	if (evnt.keyCode == 13) { // enter
		// see if there is exact match
		var contFlag = true;
		fld = String(this.box.ownerDocument.getElementById(name +'_search').value);
		k = 0;
		for (var item in this.lookup_items) {
			if (String(this.lookup_items[item]).toLowerCase() == fld.toLowerCase()) {
				this.box.ownerDocument.getElementById(name +'_search').value = this.lookup_items[item];
				this.box.ownerDocument.getElementById(name).value 			 = item;
				this.box.ownerDocument.getElementById(name +'_div').style.display = 'none';
				top.jsUtils.clearShadow(this.box.ownerDocument.getElementById(name +'_div'));
				contFlag = false;
				break;
			}
			k++;
		}
		if (contFlag) {
			k = 0;
			for (var item in this.lookup_items) {
				if (k == this.currentField) {
					this.box.ownerDocument.getElementById(name +'_search').value = this.lookup_items[item];
					this.box.ownerDocument.getElementById(name).value 			 = item;
					this.box.ownerDocument.getElementById(name +'_div').style.display = 'none';
					top.jsUtils.clearShadow(this.box.ownerDocument.getElementById(name +'_div'));
					break;
				}
				k++;
			}
		}
		var el = this.box.ownerDocument.getElementById(name);
		if (el.onchange) el.onchange(el.value);
		
		evnt.cancelBubble = true;
		if (!document.all) evnt.stopPropagation();
		return false;
	}
	el.style.color = 'black';
	this.box.ownerDocument.getElementById(name).value = '';
	if (el.value != '') {
		if (this.timer > 0) clearTimeout(this.timer);
		this.timer = setTimeout("top.elements['"+ this.name + "'].currentField  = -1; "+
								"top.elements['"+ this.name + "'].lookup_items  = []; "+
							    "top.elements['"+ this.name + "'].serverCall('edit_lookup', 'lookup_name::"+ name +";;lookup_search::"+ el.value +"');", 300);
	} else {
		this.lookup_items = [];
		this.lookup_show(name);
	}
}

function jsEdit_lookup_blur(el, name, evnt) {
	setTimeout("if (top.elements['"+ this.name +"'].box.ownerDocument.getElementById('"+ name +"').value == '') {" +
			   "	top.elements['"+ this.name +"'].box.ownerDocument.getElementById('"+ name +"_search').value = 'start typing...';" +
			   "	top.elements['"+ this.name +"'].box.ownerDocument.getElementById('"+ name +"_search').style.color = '#666666';" +
			   "	top.elements['"+ this.name +"'].box.ownerDocument.getElementById('"+ name +"_div').style.display = 'none';"+
			   "	top.jsUtils.clearShadow(top.elements['"+ this.name +"'].box.ownerDocument.getElementById('"+ name +"_div')); "+
			   "}", 400);
}

function jsEdit_getControls() {
	html = '';
	html = '<table cellspacing="0" cellpadding="0" class="rText"><tr>';
    for (i=0; i<this.controls.length; i++) {
    	html += '<td nowrap="nowrap" style="padding-left: 2px; padding-right: 2px">'+ this.controls[i] + '</td>';
    }
    html += '<td>&nbsp;</td></tr></table>';
    return html;
}

function jsEdit_refreshList(name) {
	var fld = this.findField(name);
	this.getOneList = true;
	this.getList(fld);
}

function jsEdit_getList(fld) { 
	if (!this.box) return;
	if (!this.getOneList) this.fieldList++;
	req = this.box.ownerDocument.createElement('SCRIPT');
    param = [];
    // add custom params
    for (obj in this.srvParams) param[obj] = this.srvParams[obj];
    // add list params
    param['req_cmd']    	 = 'edit_field_list';
    param['req_name']   	 = this.name;
    param['req_recid']  	 = this.recid ? this.recid : 'null';
    param['req_index']  	 = fld.index;
    param['req_field']  	 = fld.fieldName;

    if (this.srvFile.indexOf('?') > -1) { cchar = '&'; } else { cchar = '?'; }
    req.src  = this.srvFile + cchar + 'cmd=' + top.jsUtils.serialize(param);
    this.box.ownerDocument.body.appendChild(req);
}

function jsEdit_getListDone(nameOrIndex, param) {
	if (this.getOneList == true) {
		this.getOneList = false;
		var fld = this.findField(nameOrIndex);
	    fld.param = param;
		// refresh that field only;
		var sel = this.box.ownerDocument.getElementById(fld.prefix + '_field' +  fld.index).parentNode;
		sel.innerHTML =  '<select id="'+ fld.prefix + '_field' +  fld.index +'" name="'+ fld.fieldName +'" '+
                         '		class="rText rInput" type="text" '+ fld.inTag +'>\n'+
                         	fld.param +
                         '</select>\n' + fld.outTag;
		var list = this.box.ownerDocument.getElementById(fld.prefix + '_field' +  fld.index);
		if (list) list.value = fld.value;
		return;		
	}
	this.fieldList--;
	fld = this.findField(nameOrIndex);
    fld.param = param;
    if (this.fieldList == 0) {
        this.refresh();
    	this.getData();
    }
}

function jsEdit_findField(indOrName) {
    for (i=0; i<this.groups.length; i++) {
    	grp = this.groups[i];
        for (j=0; j<grp.fields.length; j++) {
        	fld = grp.fields[j];
            if (fld.fieldName == indOrName) return fld;
	        if (fld.index == indOrName) return fld;
        }
    }
    return null;
}

function jsEdit_getData() {
	if (!this.box) return;
	if (this.fieldList != 0) return;
	this.showStatus('Retriving Data...');
	if (this.onData) {
    	ret = this.onData();
        if (ret === false) return;
    }
    this.items = [];
	req = this.box.ownerDocument.createElement('SCRIPT');
    param = [];
    // add custom params
    for (obj in this.srvParams) param[obj] = this.srvParams[obj];
    // add list params
    param['req_cmd']     = 'edit_get_data';
    param['req_name']  	 = this.name;
    param['req_recid'] 	 = this.recid ? this.recid : 'null';

    if (this.srvFile.indexOf('?') > -1) { cchar = '&'; } else { cchar = '?'; }
    req.src    = this.srvFile + cchar + 'cmd=' + top.jsUtils.serialize(param);
    this.box.ownerDocument.body.appendChild(req);
}

function jsEdit_resetFields() {
	if (!this.box) return;
	for (val in this.items) {
    	el = this.box.ownerDocument.getElementById(this.name+'_field'+val);
        if (el) {
        	if (el.tagName == 'LABEL') el.innerHTML = '';
        	el.value = '';
            // check radio buttons
            ir = 0;
            while (el = this.box.ownerDocument.getElementById(this.name+'_field'+val+'_radio'+ir)) {
                if (el.value == this.items[val]) el.checked = true;
                ir++;
            }
        }
    }

}

function jsEdit_dataReceived() {
	if (!this.box) return;
	// the function needs to be called after the data retrieved.
	this.hideStatus();
    // it will go thru the items array and will set data to corresponding fields
	for (val in this.items) {
    	el = this.box.ownerDocument.getElementById(this.name+'_field'+val);
        if (el) {
        	el.value = this.items[val];
			// label type
        	if (el.tagName == 'LABEL') el.innerHTML = this.items[val];
            // check radio buttons
            ir = 0;
            while (el2 = this.box.ownerDocument.getElementById(this.name+'_field'+val+'_radio'+ir)) {
                if (el2.value == this.items[val]) el2.checked = true;
                ir++;
            }
			// lookup type
			el2 = this.box.ownerDocument.getElementById(this.name +'_field'+ val +'_search');
			if (el2) {
				var color = 'black';
				var tmp = String(this.items[val]).split('::');
				if (tmp[1] == undefined) {
					tmp[1] = 'start typing...';
					color  = '#666666';
				}
				el.value  = tmp[0];
				el2.value = tmp[1];
				el2.style.color = color;
			}
        }
    }

	if (this.onDataReceived) {
    	ret = this.onDataReceived();
        if (ret === false) return;
    }
}

function jsEdit_saveData(debug) {
	if (!this.box) return;
	if (this.onSave) {
    	ret = this.onSave();
        if (ret === false) return;
    }
    if (!this.validate()) return;

	frm = this.box.ownerDocument.getElementById('form_' + this.name);
	req = this.box.ownerDocument.getElementById('frame_' + this.name);
    req.style.width  = debug ? '100%' : 1;
    req.style.height = debug ? '400px' : 1;

    param = [];
    // add custom params
    for (obj in this.srvParams) param[obj] = this.srvParams[obj];
    // add list params
    param['req_cmd']  	 = 'edit_save_data';
    param['req_name']  	 = this.name;
    param['req_recid'] 	 = this.recid ? this.recid : 'null';
    param['req_frame']	 = req.id;

    if (this.srvFile.indexOf('?') > -1) { cchar = '&'; } else { cchar = '?'; }
    frm.action = this.srvFile + cchar + 'cmd=' + top.jsUtils.serialize(param);
    frm.submit();
}

function jsEdit_saveDone() {
	if (!this.box) return;
	if (this.onSaveDone) {
    	ret = this.onSaveDone();
        if (ret === false) return;
    }
	
    // go to another page or output a message
	if (this.onComplete) {
		if (this.onComplete.output) { this.onComplete.output(); } else { this.onComplete(); }
	}
}

function jsEdit_serverCall(cmd, params) {
	if (!this.box) return;
    // call sever script
	req = this.box.ownerDocument.createElement('SCRIPT');
    param = [];
    // add custom params
    for (obj in this.srvParams) param[obj] = this.srvParams[obj];
    // add list params
    param['req_cmd']   = cmd;
    param['req_name']  = this.name;
    param['req_recid'] = this.recid ? this.recid : 'null';
	// add passed params
	if (params != undefined && params != '') {
		var tmp = params.split(';;');
		for (var i=0; i<tmp.length; i++) {
			var t = tmp[i].split('::');
			param[t[0]] = t[1];
		}
	}

    if (this.srvFile.indexOf('?') > -1) { cchar = '&'; } else { cchar = '?'; }
    req.src    = this.srvFile + cchar + 'cmd=' + top.jsUtils.serialize(param) + '&rnd=' + Math.random();
    this.box.ownerDocument.body.appendChild(req);
}

function jsEdit_validate() {
	if (!this.box) return;
	// make sure required fields are not empty
	reqFields = '';
    for (i=0; i<this.groups.length; i++) {
    	grp = this.groups[i];
        for (j=0; j<grp.fields.length; j++) {
        	fld = grp.fields[j];
	        if (fld.required && this.box.ownerDocument.getElementById(this.name+'_field'+fld.index).value == '') {
            	reqFields += ' - ' + fld.caption + ' \n';
	        }
        }
    }
    if (reqFields != '') {
    	reqFields = reqFields.substr(0, reqFields.length -2);
        alert(this.msgEmpty + '\n' + reqFields);
        return false;
    }
    // check ints and floats
	reqFields = '';
    for (i=0; i<this.groups.length; i++) {
    	grp = this.groups[i];
        for (j=0; j<grp.fields.length; j++) {
        	fld = grp.fields[j];
	        if (String(fld.type).toUpperCase() == 'INT' && !top.jsUtils.isInt(this.box.ownerDocument.getElementById(this.name+'_field'+fld.index).value)) {
            	reqFields += ' - ' + fld.caption + ' - should be integer \n';
	        }
	        if (String(fld.type).toUpperCase() == 'FLOAT' && !top.jsUtils.isFloat(this.box.ownerDocument.getElementById(this.name+'_field'+fld.index).value)) {
            	reqFields += ' - ' + fld.caption + ' - should be float \n';
	        }
        }
    }
    if (reqFields != '') {
    	reqFields = reqFields.substr(0, reqFields.length -2);
        alert(this.msgWrong + '\n' + reqFields);
        return false;
    }
    return true;
}

function jsEdit_showStatus(msg) {
	if (!this.box) return;
	el = this.box.ownerDocument.getElementById('status_'+this.name);
	if (el) {
		el.innerHTML = msg;
		el.style.display = '';
	}
}

function jsEdit_hideStatus(msg) {
	if (!this.box) return;
	el = this.box.ownerDocument.getElementById('status_'+this.name);
	if (el) {
		el.style.display = 'none';
		el.innerHTML = '';
	}
}

// Class is Loaded
if (top.jsLoader) top.jsLoader.loadFileDone('jsEdit.js');