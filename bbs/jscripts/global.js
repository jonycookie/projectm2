//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3
//******************************************************//
// global javascript
// $Id: global.js 529 2006-10-02 22:47:10Z mutantmonkey0 $
//******************************************************//

// browser sniffer
ua				= navigator.userAgent.toLowerCase();
is_opera		= (ua.indexOf('opera')			   !=-1);
is_safari		= (ua.indexOf("safari")			   != -1);
is_webtv		= (ua.indexOf('webtv')			   != -1);
is_ie			= ((ua.indexOf('msie')			   !=-1) && !is_opera && !is_safari && !is_webtv); 
is_ie4			= (is_ie && ua.indexOf('msie 4.')  !=-1);
is_gecko		= (ua.indexOf("gecko")			   != -1);
is_konqueror	= (ua.indexOf("konqueror")		   != -1);

// deprecated, use $() instead
function _getbyid(objid)
{
	return $(objid);
}

/* included in prototype.js, so not needed
function $()
{
	var elements = new Array();
	for (var i = 0; i < arguments.length; i++) {
		var element = arguments[i];
		if (typeof element == 'string')
			element = document.getElementById(element);
		if (arguments.length == 1)
			return element;
		elements.push(element);
	}
	return elements;
}*/

function _toggle_view(objid)
{
	obj			= _getbyid(objid);

	if(obj.style.display=='none')
	{
		obj.style.display='';
	}
	else {
		obj.style.display='none';
	}
}

function _toggle_class(id,class1,class2)
{
	item		= _getbyid(id);

	if(item.className==class1)
	{
		item.className=class2;
	}
	else {
		item.className=class1;
	}
}

function _toggle_category(catid,restore)
{
	item		= _getbyid('cat-'+catid);
	
	_toggle_view('cat-'+catid);
	_toggle_view('cat-'+catid+'-collapsed');

	if(restore==1)
	{
		_setcookie('cat-'+catid+'-cstate',0,1);
	}
	else {
		_setcookie('cat-'+catid+'-cstate',1,1);
	}
	
	//alert(_getcookie('cat-'+catid+'-cstate'));
}

// cookies

function _getcookie(name)
{
	var dc = document.cookie;
	var prefix = icebb_cookied_prefix+name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
	begin = dc.indexOf(prefix);
	if (begin != 0) return null;
	} else
	begin += 2;
	var end = document.cookie.indexOf(";", begin);
	if (end == -1)
	end = dc.length;
	return unescape(dc.substring(begin + prefix.length, end));
}

function _setcookie(name,value,sticktoshoe)
{
	expiry='';
	domain='';
	path='';

	if(sticktoshoe)
	{
		expiry="; expires=Thurs, 31 Dec 2020 23:00:00 GMT";
	}
	
	if(icebb_cookied_domain!='')
	{
		domain='; domain='+icebb_cookied_domain;
	}
	
	if(icebb_cookied_path!='')
	{
		domain='; path='+icebb_cookied_path;
	}
	
	document.cookie=icebb_cookied_prefix+name+"="+value+path+domain+expiry;
}

// end cookie stuff

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function addEvent(elm, evType, fn, useCapture)
{
	if (elm.addEventListener) {
		elm.addEventListener(evType, fn, useCapture);
		return true;
	}
	else if (elm.attachEvent) {
		var r = elm.attachEvent('on' + evType, fn);
		return r;
	}
	else {
		elm['on' + evType] = fn;
	}
}