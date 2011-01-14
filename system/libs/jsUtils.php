/***********************************
*
* -- This the jsUtils class
*
***********************************/

function jsUtils_class() {
	this.isInt		 = jsUtils_isInt;
	this.isFloat	 = jsUtils_isFloat;
	this.isTime      = jsUtils_isTime;
	this.serialize   = jsUtils_serialize;
	this.pause       = jsUtils_pause;
	this.trim		 = jsUtils_trim;
	this.center      = jsUtils_center;
	this.lock		 = jsUtils_lock;
	this.unlock		 = jsUtils_unlock;
	this.dropShadow  = jsUtils_dropShadow;
	this.clearShadow = jsUtils_clearShadow;
	this.slideDown	 = jsUtils_slideDown;
	this.slideUp	 = jsUtils_slideUp;
}

/***************************************
* ---- IMPLEMENTATION
*/

function jsUtils_isInt(val) {
	tmpStr = '-0123456789';
    val    = String(val);
    for (ii=0; ii<val.length; ii++){
    	if (tmpStr.indexOf(val[ii]) < 0) { return false; }
    	if (val[ii] == '-' && ii != 0) { return false; }
    }
    return true;
}

function jsUtils_isFloat(val) {
	tmpStr = '-.0123456789';
    val    = String(val);
    for (ii=0; ii<val.length; ii++){
    	if (tmpStr.indexOf(val[ii]) < 0) { return false; }
    	if (val[ii] == '-' && ii != 0) { return false; }
    }
    return true;
}

function jsUtils_isTime(val) {
	tmp = val.split(':');
	if (tmp.length != 2) { return false; }
	if (tmp[0] == '' || parseInt(tmp[0]) < 0 || parseInt(tmp[0]) > 23 || !top.jsUtils.isInt(tmp[0])) { return false; }
	if (tmp[1] == '' || parseInt(tmp[1]) < 0 || parseInt(tmp[1]) > 59 || !top.jsUtils.isInt(tmp[1])) { return false; }
	return true;
}

function jsUtils_serialize(obj){
    var res = '';
    switch(typeof(obj)) {
        case 'number':
            if ((obj - Math.round(obj)) == 0) {
                res += 'i:' + obj + ';';
            } else {
                res += 'd:' + obj + ';';
            }
            break;
        case 'string':
        	var tmp = unescape(obj); 
            res += 's:' + tmp.length + ':"' + obj + '";';
            break;
        case 'boolean':
            if (obj) {
                res += 'b:1;';
            } else {
                res += 'b:0;';
            }
            break;
         case 'object' :
            if (obj instanceof Array) {
                res = 'a:';
                var tmpStr = '';
                var cntr = 0;
                for (var key in obj) {
                    tmpStr += top.jsUtils.serialize(key);
                    tmpStr += top.jsUtils.serialize(obj[key]);
                    cntr++;
                }
                res += cntr + ':{' + tmpStr + '}';
            }
         break;

    }
    return res;
}

function jsUtils_pause(msec) {
	var date = new Date();
	var wait = parseInt(date.getTime()) + msec;
	while (parseInt(date.getTime()) < wait) { date = new Date(); };
}

function jsUtils_trim(sstr) {
    if (sstr.substr(sstr.length, 1) == "\n") {
        sstr = sstr.substr(0, sstr.length);
    }
    while (true) {
        if (sstr.substr(0, 1) == " ") {
            sstr = sstr.substr(1, sstr.length - 1);
        } else {
            if (sstr.substr(sstr.length - 1, 1) == " ") {
                sstr = sstr.substr(0, sstr.length - 1);
            } else {
                break;
            }
        }
    }
    return sstr;
}

function jsUtils_center(div) {
    var width  = parseInt(window.innerWidth);
    var height = parseInt(window.innerHeight);
    div.style.left = (width  - parseInt(div.style.width)) / 2;
    div.style.top  = (height - 150 - parseInt(div.style.height)) / 2;
}

function jsUtils_lock(opacity) {
	if (top.document.getElementById('screenLock')) return;
	if (opacity == undefined || opacity == null) opacity = 0;
	var width;
	var height;
	// get width and height
	if (top.innerHeight == undefined) {
		width  = top.document.body.clientWidth;
		height = top.document.body.clientHeight;
	} else {
		width  = top.innerWidth;
		height = top.innerHeight;
	}		
	//lock window
	top.screenLock 	= top.document.createElement('DIV');
	top.screenLock.style.cssText = 'position: absolute; zIndex: 1000; left: 0px; top: 0px; background-color: gray;'+
								   'width: '+ width +'px; height: '+ height +'px; -moz-opacity: '+ opacity +'; opacity: '+ opacity +';';
	top.screenLock.id = 'screenLock';
	top.screenLock.style.filter     = "alpha(opacity="+ (opacity * 100) +")";
	top.document.body.appendChild(top.screenLock);
}

function jsUtils_unlock() {
	top.document.body.removeChild(top.screenLock);
	top.screenLock = null;
}

function jsUtils_dropShadow(shadowel) {
	var sys_path = top.jsLoader.sys_path;
    if (sys_path == '') sys_path = '/system';
    
	var shleft      = parseInt(shadowel.offsetLeft);
	var shtop       = parseInt(shadowel.offsetTop);
	var shwidth     = parseInt(shadowel.offsetWidth);
	var shheight    = parseInt(shadowel.offsetHeight);
    var addshLeft   = 0;
    var addshTop    = 0;
    var addshWidth  = 0;
    var addshHeight = 0;

    if (shtop <= 0 && shadowel.ownerDocument.defaultView) {
	    st = shadowel.ownerDocument.defaultView.getComputedStyle(shadowel, '');
        shleft      = parseInt(st.left);
        shtop       = parseInt(st.top);
	    shwidth     = parseInt(st.width);
	    shheight    = parseInt(st.height);
        addshLeft   = parseInt(st.marginLeft);
        addshTop    = parseInt(st.marginTop);
        addshWidth  = parseInt(st.paddingLeft) + parseInt(st.paddingRight) + parseInt(st.borderLeftWidth) + parseInt(st.borderRightWidth);
        addshHeight = parseInt(st.paddingTop) + parseInt(st.paddingBottom) + parseInt(st.borderTopWidth) + parseInt(st.borderBottomWidth);
    }
	if (String(Number(shleft)) == 'NaN') { shleft = 0; }
	if (String(Number(shtop))  == 'NaN') { shtop = 0; }
    
	var tmp = shadowel.ownerDocument.createElement('DIV');
    tmp.style.position  = 'absolute';
    tmp.style.left      = (shleft - 5 + addshLeft) + 'px';
    tmp.style.top       = (shtop + addshTop) + 'px';
    tmp.style.width     = (shwidth  + 10 + addshWidth) + 'px';
    tmp.style.height    = (shheight + 5 + addshHeight) + 'px';

    var html = '\n';
    html += '<table cellpadding="0" cellspacing="0" width="100%" height="100%" style="font-size: 2px;">\n'+
            '<tr height="10px">\n'+
            '  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_lt.png)">&nbsp;</td>\n'+
            '  <td style="background-image: url('+ sys_path +'/images/shadow_gt.png)">&nbsp;</td>\n'+
            '  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_rt.png)">&nbsp;</td>\n'+
            '</tr>\n'+
            '<tr>\n'+
            '  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_vl.png)">&nbsp;</td>\n'+
            '  <td style="background-image: url('+ sys_path +'/images/shadow_md.png)">&nbsp;</td>\n'+
            '  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_vr.png)">&nbsp;</td>\n'+
            '</tr>\n'+
    		'<tr height="10px">\n'+
            '  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_lb.png)">&nbsp;</td>\n'+
         	'  <td style="background-image: url('+ sys_path +'/images/shadow_gb.png)">&nbsp;</td>\n'+
    		'  <td width="10px" style="background-image: url('+ sys_path +'/images/shadow_rb.png)">&nbsp;</td>\n'+
            '</tr>\n'+
            '</table>\n';

    tmp.innerHTML = html;
    shadowel.parentNode.appendChild(tmp, shadowel);
    if (shadowel.style.zIndex != undefined && shadowel.style.zIndex != null) tmp.style.zIndex = parseInt(shadowel.style.zIndex)-1;
    return tmp;
}

function jsUtils_clearShadow(shadowel) {
	if (shadowel.shadow) shadowel.parentNode.removeChild(shadowel.shadow);
	shadowel.shadow = null;
}

function jsUtils_slideDown(el, onfinish) {
	if (el != undefined && el != null) {
		// initiate
		top.tmp_sd_el 		= el;
		el.style.top  		= -1000;
		el.style.display  	= '';
		top.tmp_sd_height 	= parseInt(el.clientHeight);
		top.tmp_sd_top	  	= -parseInt(el.clientHeight);
		top.tmp_sd_timer  	= setInterval("top.jsUtils.slideDown()", 2);
		top.tmp_sd_step   	= top.tmp_sd_height / 25;
		top.tmp_sd_onfinish	= onfinish;
		// show parent element
		el.parentNode.style.width  = parseInt(el.clientWidth) + 5;
		el.parentNode.style.height = parseInt(el.clientHeight) + 5;
		el.parentNode.style.overflow = 'hidden';
		return;
	}	
	top.tmp_sd_top += top.tmp_sd_step;
	if (top.tmp_sd_top > 0) top.tmp_sd_top = 0;
	if (top.tmp_sd_el) {
		top.tmp_sd_el.style.top = top.tmp_sd_top;
		if (top.tmp_sd_top == 0) { 
			if (top.tmp_sd_onfinish) top.tmp_sd_onfinish(top.tmp_sd_el);
			top.tmp_sd_el.parentNode.style.overflow = '';
			// stop role out
			clearInterval(top.tmp_sd_timer); 
			top.tmp_sd_el 	  	= undefined;
			top.tmp_sd_height 	= undefined;
			top.tmp_sd_top	  	= undefined;
			top.tmp_sd_timer  	= undefined;
			top.tmp_sd_step   	= undefined;
			top.tmp_sd_onfinish = undefined;
		}
	}
}

function jsUtils_slideUp(el, onfinish) {
	if (el != undefined && el != null) {
		// initiate
		top.tmp_sd_el 		= el;
		el.style.display  	= '';
		top.tmp_sd_height 	= parseInt(el.clientHeight);
		top.tmp_sd_top	  	= 0;
		top.tmp_sd_timer  	= setInterval("top.jsUtils.slideUp()", 2);
		top.tmp_sd_step   	= top.tmp_sd_height / 25;
		top.tmp_sd_onfinish	= onfinish;
		// control the parent element
		el.parentNode.style.overflow 	= 'hidden';
		return;
	}	
	top.tmp_sd_top -= top.tmp_sd_step;
	if (-top.tmp_sd_top > top.tmp_sd_height) top.tmp_sd_top = -top.tmp_sd_height - 10;
	top.tmp_sd_el.style.top = top.tmp_sd_top;
	if (top.tmp_sd_top == -top.tmp_sd_height - 10) { 
		if (top.tmp_sd_onfinish) top.tmp_sd_onfinish(top.tmp_sd_el);
		// hide parent element
		top.tmp_sd_el.parentNode.style.width  = 0;
		top.tmp_sd_el.parentNode.style.height = 0;
		// stop role out
		clearInterval(top.tmp_sd_timer); 
		top.tmp_sd_el 	  	= undefined;
		top.tmp_sd_height 	= undefined;
		top.tmp_sd_top	  	= undefined;
		top.tmp_sd_timer  	= undefined;
		top.tmp_sd_step   	= undefined;
		top.tmp_sd_onfinish	= undefined;
	}
}

// Class is Loaded
top.jsUtils = new jsUtils_class();
if (top.jsLoader) top.jsLoader.loadFileDone('jsUtils.js');