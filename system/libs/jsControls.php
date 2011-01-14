/*************************************************************
*
* -- Different types of the Controls
* -- such as: Group, Text, Int, Float, Date
* --          Time, Date&Time, List
*
*************************************************************/

function jsField_class(caption, type, fieldName, inTag, outTag, defValue, required, column) {
	this.caption	= caption;
    this.type		= type;
    this.fieldName  = fieldName;
    this.inTag		= inTag;
    this.outTag		= outTag;
    this.defValue	= defValue;
    this.value		= null; // current value of the field, if needed to be saved. jsList searches.
    this.required 	= required ? required : false;
    this.column     = column ? column : 0;
    this.td1Class   = 'fieldTitle';
    this.td2Class   = 'fieldControl';
	this.onSelect   = ''; // used for LOOKUP field for now

    this.param;  	// parameter of the control, such as OPTIONS from the list page
    this.items;  	// if it is not null, it will be used to build the list
    this.owner;  	// group that ownes this field
	this.ownerName; // name of owner class, if null, owner field is used
    this.prefix; 	// prefix for the id attribute (in case 2 edits on the page)
    this.index;  	// index number of the field in the edit
    this.html;
    this.build		= jsField_build;
}

function jsGroup_class(name, header, inTag, outTag) {
    // public properties
    this.name  		= name;
    this.header 	= header;
    this.inTag		= inTag ? inTag : '';
    this.outTag		= outTag? outTag : '';
    this.fields 	= [];
    this.owner 		= null;
	this.object     = null;
	this.height     = null;
	this.disabled   = false;

    // private properties
    this.inLabel    = '';

    // public methods
    this.build 		= jsGroup_build;
    this.addField   = jsGroup_addField;
    this.addBreak   = jsGroup_addBreak;
	this.initGroup  = jsGroup_initGroup;

    // private methods
}
top.jsField = jsField_class;
top.jsGroup = jsGroup_class;

// ==============-------------------------------
// -------------- IMPLEMENTATION

function jsField_build(inLabel, spanAdd) {
	this.html = '';
    td1_style = 'valign="top" style="padding-top: 4px" '+ inLabel;
    if (this.caption == '') ssign = ''; else ssign = ':';
    if (this.required) rsign = "<span style='padding-top: 3px; padding-left: 2px; color: red' class='rText'>*</span>"; else rsign = "";
    tmpValue = ((this.value != null && this.value != '') ? this.value : this.defValue);

    switch (String(this.type).toUpperCase()) {

    	case 'HIDDEN':
        	this.html += '<td colspan="'+ parseInt(spanAdd+3) +'" style="display: none">'+
        				 '	<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText" type="hidden" value="'+ tmpValue +'" '+ this.inTag +'>'+ 
                         '</td>';
            break;

    	case 'TEXT':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td></td>'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<div style="float:left">'+
            			 '		<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText rInput" type="text" value="'+ tmpValue +'" '+ this.inTag +'>'+
                         '	</div>'+ this.outTag +
                         '</td>\n';
        	break;
			
    	case 'LOOKUP':		
			var tmp = String(tmpValue).split('::');
			if (tmp[1] == undefined) tmp[1] = 'start typing...';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
			this.html += '	<div style="float:left">'+
            			 '		<input size=2 id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText rInput" type="hidden" onchange="'+ this.onSelect +'" value="'+ tmp[0] +'">'+
						 '		<input type="text" autocomplete="off" class="rText rInput" style="background-color: ecf5e4; color: #666666;'+ this.inTag +'"'+
						 '			id="'+ this.prefix + '_field' +  this.index +'_search" value="'+ tmp[1] +'" '+ 
						 '			onclick="if (this.value == \'start typing...\') { this.value = \'\'; this.style.color = \'black\'; } this.select();" '+
						 '			onkeydown="if (this.value ==\'start typing...\') this.value = \'\'; "'+
						 '			onkeyup="top.elements[\''+ (this.ownerName != undefined ? this.ownerName : this.owner.owner.name) +'\'].lookup_keyup(this, \''+ this.prefix + '_field' +  this.index +'\', event)" '+
						 '			onblur ="top.elements[\''+ (this.ownerName != undefined ? this.ownerName : this.owner.owner.name) +'\'].lookup_blur(this, \''+ this.prefix + '_field' +  this.index +'\', event)">'+ 
						 '		<br>'+
						 '		<div id="'+ this.prefix + '_field'+  this.index +'_div" '+ 
						 '			style="position: absolute; z-index: 100; border: 1px solid #a7a6aa; border-top: 0px; background-color: white; display: none; '+
						 '			'+ this.inTag +'"> '+
						 '		</div>'+
                         '	</div>'+ this.outTag +
                         '</td>\n';
        	break;

    	case 'TEXTAREA':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'" valign=top> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'" valign=top> ';
			}
        	this.html += '	<textarea id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText rInput rTextArea" type="text" value="'+ tmpValue +'" '+ this.inTag +'></textarea>'+ 
						 '<div style="float: left">' + this.outTag + '</div>'+
                         '</td>\n';
        	break;

    	case 'PASSWORD':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<div style="float:left">'+
            			 '		<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText rInput" type="password" value="'+ tmpValue +'" '+ this.inTag +'>'+ 
                         '	</div>'+ this.outTag +
                         '</td>\n';
        	break;

        case 'INT':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onkeyup="if (!top.jsUtils.isInt(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isInt(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue +'" '+ this.inTag +'>'+ this.outTag +
                         '</td>\n';
        	break;

        case 'INTRANGE':
		    tmpValue = ((this.value != null && this.value != '') ? this.value : this.defValue);
			tmpValue = tmpValue.split('::');
			if (tmpValue[1] == undefined) tmpValue[1] = '';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onkeyup="if (!top.jsUtils.isInt(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isInt(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue[0] +'" '+ this.inTag +'>'+ this.outTag +
            			 '  - '+
            			 '	<input id="'+ this.prefix + '_field' +  this.index +'_2" name="'+ this.fieldName +'_2" '+
                         '			onkeyup="if (!top.jsUtils.isInt(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isInt(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue[1] +'" '+ this.inTag +'>'+ this.outTag +
                         '</td>\n';
        	break;

        case 'FLOAT':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onkeyup="if (!top.jsUtils.isFloat(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isFloat(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue +'" '+ this.inTag +'>'+ this.outTag +
                         '</td>\n';
        	break;

        case 'FLOATRANGE':
		    tmpValue = ((this.value != null && this.value != '') ? this.value : this.defValue);
			tmpValue = tmpValue.split('::');
			if (tmpValue[1] == undefined) tmpValue[1] = '';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onkeyup="if (!top.jsUtils.isFloat(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isFloat(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue[0] +'" '+ this.inTag +'>'+ this.outTag +
            			 '  - '+
            			 '	<input id="'+ this.prefix + '_field' +  this.index +'_2" name="'+ this.fieldName +'_2" '+
                         '			onkeyup="if (!top.jsUtils.isFloat(this.value)) this.select();"'+
                         '			onblur ="if (!top.jsUtils.isFloat(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue[1] +'" '+ this.inTag +'>'+ this.outTag +
                         '</td>\n';
        	break;

        case 'LIST':
            if (this.items) {
            	this.param = '';
            	for (it=0; it<this.items.length; it++) {
                	itt = this.items[it];
                    tmp = itt.split('::');
                	this.param += '<option value="'+ tmp[0] + '" '+ tmp[2] +'>' + tmp[1] + '</option>';
                }
            }
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '		<select id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" value="'+ tmpValue +'" '+
                         '				class="rText rInput" type="text" '+ this.inTag +'>\n'+
                         			this.param +
                         '	   </select>' + this.outTag +
                         '</td>\n';
        	break;

        case 'RADIO_YESNO':
        	if (tmpValue.toUpperCase() == 'Y' || tmpValue.toUpperCase() == 'T' || tmpValue.toUpperCase() == '1')
        		checkYes = 'checked'; else checkYes = '';
        	if (tmpValue.toUpperCase() == 'N' || tmpValue.toUpperCase() == 'F' || tmpValue.toUpperCase() == '0')
        		checkNo = 'checked'; else checkNo = '';
        	this.param = '<table cellpadding="2" cellspacing="0"><tr>'+
            			 '<td class="rText" style="width: 10px;">'+
            		  	 '	<input tabindex=-1 type="radio" style="position: relative; top: -2px;" id="'+ this.prefix + '_field' +  this.index +'_radio0" '+ checkYes +
                      	 '		name="'+ this.prefix + '_field' +  this.index +'_radio" value="t" '+
                      	 '		onclick="document.getElementById(\''+ this.prefix + '_field' +  this.index +'\').value = \'t\';">'+
                      	 '</td><td class="rText" style="padding: 1px;" valign=top>'+
                      	 '	<label for="'+ this.prefix + '_field' +  this.index +'_radio0" class="rText">Yes</label>'+
                      	 '</td><td style="paddin-left: 5px; padding-right: 5px"></td>'+
            			 '<td class="rText" style="width: 10px;">'+
            		  	 '	<input tabindex=-1 type="radio" style="position: relative; top: -2px;" id="'+ this.prefix + '_field' +  this.index +'_radio1" '+ checkNo +
                      	 '		name="'+ this.prefix + '_field' +  this.index +'_radio" value="f" '+
                      	 '		onclick="document.getElementById(\''+ this.prefix + '_field' +  this.index +'\').value = \'f\';">'+
                      	 '</td><td class="rText" style="padding: 1px;" valign=top>'+
                      	 '	<label for="'+ this.prefix + '_field' +  this.index +'_radio1" class="rText">No</label>'+
                         '</td>'+
                      	 '</tr></table>';

        case 'RADIO':
            if (this.items) {
            	this.param = '<table class="rText" cellpadding="2" cellspacing="0" style="padding:0px;"><tr>';
            	for (var it=0; it<this.items.length; it++) {
                	itt = this.items[it];
                    tmp = itt.split('::');
                    dch = (tmpValue == tmp[0] ? 'checked' : '');
                	this.param += '<td style="padding-top: 0px; padding-bottom: 2px;">'+
								  '	<input tabindex=-1 type=radio name="'+ this.prefix + '_field' +  this.index +'_radio" id="'+ this.prefix +'_field'+ this.index +'_radio'+ it +'" value="'+ tmp[0] + '" '+ dch +' '+ tmp[2] +
								  ' 	onclick="document.getElementById(\''+ this.prefix + '_field' +  this.index +'\').value = this.value;">'+
								  '</td>'+
								  '<td style="padding-top: 0px; padding-right: 15px;">'+
								  '	<label for="'+ this.prefix +'_field'+ this.index +'_radio'+ it +'">' + tmp[1] + '</label>'+
								  '</td>';
					if (tmp[2] == 'newline') this.param += '</tr><tr>';
                }
				this.param += '<td></td></tr></table>';
            }
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input type="hidden" value="'+ tmpValue +'" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+ this.inTag +'>'+
                         			this.param + 
                                    this.outTag +
                         '</td>\n';
        	break;

        case 'CHECK':
            if (this.items) {
            	this.param = '';
            	for (it=0; it<this.items.length; it++) {
                	itt = this.items[it];
                    tmp = itt.split('::');
                    dch = (tmpValue == tmp[0] ? 'checked' : '');
                	this.param += '<option tabindex=-1 value="'+ tmp[0] + '" '+ dch +' '+ tmp[2] +'>' + tmp[1] + '</option>';
                }
            }
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
							 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
	            			 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input type="hidden" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+ this.inTag +'>'+
                         			this.param +
                                    this.outTag +
                         '</td>\n';
        	break;

        case 'DATE':
        	cal  = new top.jsCalendar(''+ this.prefix + '_field' +  this.index+ '_calendar');
        	cal.ownerDocument = (this.owner.owner ? this.owner.owner.box.ownerDocument : this.owner.box.ownerDocument);
        	cal.onSelect = new Function("param", "this.ownerDocument.getElementById('"+ this.prefix + "_field"+ this.index +"').value = param;"+
        										 "this.ownerDocument.getElementById(this.name + '_tbl').parentNode.style.display = 'none';"+
        										 "cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
			cal.onCancel = new Function("cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	calhtml = '<div style="position: absolute; z-Index: 100; padding-top: 1px; display: none;" id="'+ this.prefix + '_field' +  this.index +'_caldiv">'+ cal.get3Months() + '</div>';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input type="text" size="10" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                      	 '		class="rText rInput" value="'+ tmpValue +'" '+ this.inTag +'>'+ calhtml +
            			 '	<input type="button" class="rText" value="..." style="width: 32px" tabindex="-1"'+
            			 '		onclick="cal = document.getElementById(\''+ this.prefix + '_field' +  this.index + '_caldiv\'); '+
            			 '				 if (cal.style.display == \'none\')  { cal.style.display = \'\'; cal.shadow = top.jsUtils.dropShadow(cal); } '+
            			 '												else { cal.style.display = \'none\'; cal.shadow.style.display = \'none\'; } '+
            			 '				 this.blur();">'+ 
                         '	(mm/dd/yyyy)' + this.outTag +
                         '</td>\n';
        	break;

        case 'DATERANGE':
        	// calendar 1
        	cal  = new top.jsCalendar(''+ this.prefix + '_field' +  this.index+ '_calendar');
        	cal.ownerDocument = (this.owner.owner ? this.owner.owner.box.ownerDocument : this.owner.box.ownerDocument);
			cal.onCancel = new Function("cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	cal.onSelect = new Function("param", "this.ownerDocument.getElementById('"+ this.prefix + "_field"+ this.index +"').value = param;"+
        										 "this.ownerDocument.getElementById(this.name + '_tbl').parentNode.style.display = 'none';"+
        										 "cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	calhtml1 = '<div style="position: absolute; z-Index: 100; padding-top: 1px; display: none;" id="'+ this.prefix + '_field' +  this.index +'_caldiv">'+ cal.get3Months() + '</div>';
        	// calendar 2
        	cal  = new top.jsCalendar(''+ this.prefix + '_field' +  this.index+ '_2_calendar');
        	cal.ownerDocument = (this.owner.owner ? this.owner.owner.box.ownerDocument : this.owner.box.ownerDocument);
			cal.onCancel = new Function("cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	cal.onSelect = new Function("param", "this.ownerDocument.getElementById('"+ this.prefix + "_field"+ this.index +"_2').value = param;"+
        										 "this.ownerDocument.getElementById(this.name + '_tbl').parentNode.style.display = 'none';"+
        										 "cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	calhtml2 = '<div style="position: absolute; z-Index: 100; padding-top: 1px; display: none;" id="'+ this.prefix + '_field' +  this.index +'_2_caldiv">'+ cal.get3Months() + '</div>';

		    tmpValue = ((this.value != null && this.value != '') ? this.value : this.defValue);
			tmpValue = tmpValue.split('::');
			if (tmpValue[1] == undefined) tmpValue[1] = '';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '<table cellpadding=0 cellspacing=0 class=rText><tr><td>'+
            			 '	<input type="text" size="10" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                      	 '		class="rText rInput" value="'+ tmpValue[0] +'" '+ this.inTag +'>'+ calhtml1 + calhtml2 +
            			 '	<input type="button" class="rText" value="..." style="width: 32px" tabindex="-1"'+
            			 '		onclick="cal = document.getElementById(\''+ this.prefix + '_field' +  this.index + '_caldiv\'); '+
            			 '				 if (cal.style.display == \'none\')  { cal.style.display = \'\'; cal.shadow = top.jsUtils.dropShadow(cal); } '+
            			 '												else { cal.style.display = \'none\'; cal.shadow.style.display = \'none\'; } '+
            			 '				 this.blur();">'+
            			 '</td><td style="padding-left: 5px; padding-right: 5px;"> through </td><td>'+
            			 '	<input type="text" size="10" id="'+ this.prefix + '_field' +  this.index +'_2" name="'+ this.fieldName +'_2" '+
                      	 '		class="rText rInput" value="'+ tmpValue[0] +'" '+ this.inTag +'>'+
            			 '	<input type="button" class="rText" value="..." style="width: 32px" tabindex="-1"'+
            			 '		onclick="cal = document.getElementById(\''+ this.prefix + '_field' +  this.index + '_2_caldiv\'); '+
            			 '				 if (cal.style.display == \'none\')  { cal.style.display = \'\'; cal.shadow = top.jsUtils.dropShadow(cal); } '+
            			 '												else { cal.style.display = \'none\'; cal.shadow.style.display = \'none\'; } '+
            			 '				 this.blur();">'+ 
                         '	(mm/dd/yyyy)' + this.outTag +
                         '</td>'+
                         '</tr></table>'+
                         '</td>\n';
        	break;

        case 'TIME':
        	cal  = new top.jsCalendar(''+ this.prefix + '_field' +  this.index+ '_time');
        	cal.ownerDocument = (this.owner.owner ? this.owner.owner.box.ownerDocument : this.owner.box.ownerDocument);
        	cal.onSelect = new Function("param", "this.ownerDocument.getElementById('"+ this.prefix + "_field"+ this.index +"').value = param;"+
        										 "this.ownerDocument.getElementById(this.name + '_tbl').parentNode.style.display = 'none';"+
        										 "cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
			cal.onCancel = new Function("cal = this.ownerDocument.getElementById(this.name + '_tbl').parentNode; cal.shadow.style.display = 'none';");
        	calhtml = '<div style="position: absolute; z-Index: 100; padding-top: 1px; display: none;" id="'+ this.prefix + '_field' +  this.index +'_caldiv">'+ cal.getHours(this.prefix + '_field' +  this.index) + '</div>';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input size="10" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onblur ="if (!top.jsUtils.isTime(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue +'" '+ this.inTag +'>\n'+ calhtml +
            			 '	<input type="button" class="rText" value="..." style="width: 32px" tabindex="-1"'+
            			 '		onclick="cal = document.getElementById(\''+ this.prefix + '_field' +  this.index + '_caldiv\'); '+
            			 '				 if (cal.style.display == \'none\')  { cal.style.display = \'\'; cal.shadow = top.jsUtils.dropShadow(cal); } '+
            			 '												else { cal.style.display = \'none\'; cal.shadow.style.display = \'none\'; } '+
            			 '				 this.blur();">'+ this.outTag +
                         '</td>\n';
        	break;

        case 'TIMERANGE':
		    tmpValue = ((this.value != null && this.value != '') ? this.value : this.defValue);
			tmpValue = tmpValue.split('::');
			if (tmpValue[1] == undefined) tmpValue[1] = '';
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<input size="5" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			onblur ="if (!top.jsUtils.isTime(this.value)) this.value = \'\';"'+
                         '			class="rText" type="text" value="'+ tmpValue[0] +'" '+ this.inTag +'>'+ this.outTag +
            			 '  through '+
            			 '	<input size="5" id="'+ this.prefix + '_field' +  this.index +'_2" name="'+ this.fieldName +'_2" '+
                         '			onblur ="if (!top.jsUtils.isTime(this.value)) this.value = \'\';"'+
                         '			class="rText rInput" type="text" value="'+ tmpValue[1] +'" '+ this.inTag +'>'+ this.outTag +
            			 '	(h24:mi ~~ 8:30 or 21:30)'+
                         '</td>\n';
        	break;

        case 'BREAK':
        	this.html += '	<td colspan="'+ parseInt(spanAdd+3) +'"  class="rText" '+ this.inTag +'>'+ this.outTag +'</td>';
        	break;

        case 'UPLOAD':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<div style="float:left">'+
            			 '		<input type="text" id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" class="rText rInput" value="'+ tmpValue +'" '+ this.inTag +'>'+
                         '		<input type="file" id="'+ this.prefix + '_field' +  this.index +'_file" name="'+ this.fieldName +'_file" class="rText" '+
            			 '			onchange="document.getElementById(\''+ this.prefix + '_field' + this.index +'\').value=this.value">'+ 
                         '	</div>'+ this.outTag +
                         '</td>\n';
        	break;

    	case 'READONLY':
			if (this.caption != '') {
	        	this.html += '<td nowrap class="'+ this.td1Class +'" '+ td1_style + '>'+ this.caption + ssign +'</td>\n'+
	            			 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ parseInt(spanAdd) +'" class="'+ this.td2Class +'"> ';
			} else {
	        	this.html += '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
							 '<td colspan="'+ (parseInt(spanAdd)+1) +'" class="'+ this.td2Class +'"> ';
			}
        	this.html += '	<div style="float:left">'+
            			 '		<input tabindex=-1 id="'+ this.prefix + '_field' +  this.index +'" name="'+ this.fieldName +'" '+
                         '			class="rText rInput" style="color: #333333; background-color: #efefef;" type="text" value="'+ tmpValue +'" readonly '+ this.inTag +'>'+ 
                         '	</div>'+ this.outTag +
                         '</td>\n';
        	break;
			
		case 'TITLE':
        	this.html += '<td colspan="'+ parseInt(spanAdd+3) +'"  class="rText" style="padding-top: 4px">'+
            			 '	<div class="rText" style="clear: both; padding: 1px; padding-top: 0px;" ' +this.inTag + '>' + this.outTag + '</div>'+
                         '</td>';
        	break;

        case 'FIELD':
        	this.html += '<td></td>'+
						 '<td style="width: 1px; padding: 0px; padding-top: 2px;" valign=top>'+ rsign +'</td>'+
        				 '<td colspan="'+ parseInt(spanAdd) +'" class="rText" style="padding-top: 4px">'+
            			 '	<div class="rText" style="clear: both; padding: 1px; padding-top: 0px;" ' +this.inTag + '>' + this.outTag + '</div>'+
                         '</td>';
        	break;
	}
    return this.html;
}

function jsGroup_build(el) {
	if (this.height != null) addH = 'height: '+ parseInt(this.height) +'px; overflow: auto;'; else addH = '';
	this.html = '<div class="group" id="group_'+ this.name +'" '+ this.inTag +'>'+
    			'<div class="groupTitle" id="group_title_'+ this.name + '" style="z-Index: 1;">'+ this.header + '</div>'+
				'<div style="clear: both; '+ addH +'" id="group_content_'+ this.name +'">';				

    // render column
    this.html += '<table style="padding-left: 6px; padding-right: 6px; clear: both" cellpadding="2" cellspacing="0" width="100%">';
	this.html += '<tr>';
	var spanFlag = true;
	for (i=0; i<this.fields.length; i++) {
		if (this.fields[i].column == 0 && spanFlag) spanAdd = 4; else spanAdd = 0;
    	this.html += this.fields[i].build(this.inLabel, spanAdd);
		if (this.fields[i].column == 0) { this.html += '</tr><tr>'; spanFlag = true; } else { spanFlag = false; }
    }
    this.html += '</tr>';
	this.html += '</table>';

    this.html += '</div>'+
				 '</div>'+
    			 '<div style="height: 5px; font-size: 1px;"></div>' +
                 this.outTag;
    return this.html;
}

function jsGroup_initGroup(obj) {
	this.object = obj;
	var div = this.owner.box.ownerDocument.getElementById("group_"+ this.name +"_object");
	this.object.box = div;
	this.object.recid = this.owner.recid;
	this.object.output();
}

function jsGroup_addField(caption, type, fieldName, inTag, outTag, defValue, required, column, items) {
    ind = this.fields.length;
    this.fields[ind] = new top.jsField(caption, type, fieldName, inTag, outTag, defValue, required, column);
    if (items) this.fields[ind].items = items;
    if (type.toUpperCase() != 'BREAK') {
	    this.fields[ind].index  = this.owner.fieldIndex;
	    this.fields[ind].prefix = this.owner.name;
	    this.fields[ind].owner  = this;
    	this.owner.fieldIndex++;
    }
    return this.fields[ind];
}

function jsGroup_addBreak(height, column) {
    ind = this.fields.length;
    this.fields[ind] = new top.jsField('', 'break', '', '', '<div style="height: '+ height +'"></div>', '', false, column);
    this.fields[ind].index = ind;
    return this.fields[ind];
}

// Class is Loaded
if (top.jsLoader) top.jsLoader.loadFileDone('jsControls.js');