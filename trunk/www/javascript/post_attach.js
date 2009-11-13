var aid = 1;
var attachexts = new Array();
var attachwh = new Array();

function delAttach(id) {
	getID('attachbody').removeChild(getID('attach_' + id).parentNode.parentNode);
	getID('attachbody').innerHTML == '' && addAttach();
	getID('localimgpreview_' + id + '_menu') ? document.body.removeChild(getID('localimgpreview_' + id + '_menu')) : null;
}

function addAttach() {
	newnode = getID('attachbodyhidden').firstChild.cloneNode(true);
	var id = aid;
	var tags;
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'attach[]') {
			tags[i].id = 'attach_' + id;
			tags[i].onchange = function() {insertAttach(id)};
			tags[i].unselectable = 'on';
		}
		if(tags[i].name == 'localid[]') {
			tags[i].value = id;
		}
	}
	tags = newnode.getElementsByTagName('span');
	for(i in tags) {
		if(tags[i].id == 'localfile[]') {
			tags[i].id = 'localfile_' + id;
		}
	}
	aid++;
	getID('attachbody').appendChild(newnode);
}

addAttach();

function insertAttach(id) {
	var localimgpreview = '';
	var path = getID('attach_' + id).value;
	var ext = path.lastIndexOf('.') == -1 ? '' : path.substr(path.lastIndexOf('.') + 1, path.length).toLowerCase();
	var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
	var localfile = getID('attach_' + id).value.substr(getID('attach_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);

	if(path == '') {
		return;
	}
	if(extensions != '' && (re.exec(extensions) == null || ext == '')) {
		alert(lang['post_attachment_ext_notallowed']);
		return;
	}
	attachexts[id] = is_ie && in_array(ext, ['gif', 'jpeg', 'jpg', 'png', 'bmp']) ? 2 : 1;

	if(attachexts[id] == 2) {
		getID('img_hidden').alt = id;
		getID('img_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").sizingMethod = 'image';
		try {
			getID('img_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = getID('attach_' + id).value;
		} catch (e) {
			alert(lang['post_attachment_img_invalid']);
			delAttach(id);
			return;
		}
		var wh = {'w' : getID('img_hidden').offsetWidth, 'h' : getID('img_hidden').offsetHeight};
		var aid = getID('img_hidden').alt;
		if(wh['w'] >= thumbwidth || wh['h'] >= thumbheight) {
			wh = attachthumbImg(wh['w'], wh['h']);
		}
		attachwh[id] = wh;
		getID('img_hidden').style.width = wh['w']
		getID('img_hidden').style.height = wh['h'];
		getID('img_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").sizingMethod = 'scale';
		div = document.createElement('div');
		div.id = 'localimgpreview_' + id + '_menu';
		div.style.display = 'none';
		div.style.marginLeft = '20px';
		div.className = 'popupmenu_popup';
		document.body.appendChild(div);
		div.innerHTML = '<img style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=\'scale\',src=\'' + getID('attach_' + id).value+'\');width:'+wh['w']+';height:'+wh['h']+'" src=\'admin/images/none.gif\' border="0" aid="attach_'+ aid +'" alt="" />';
	}

	getID('localfile_' + id).innerHTML = '<a href="###delAttach" onclick="delAttach(' + id + ')">[' + lang['post_attachment_deletelink'] + ']</a> <a href="###insertAttach" title="' + lang['post_attachment_insert'] + '" onclick="insertAttachtext(' + id + ');return false;">[' + lang['post_attachment_insertlink'] + ']</a> ' +
		(attachexts[id] == 2 ? '<span id="localimgpreview_' + id + '" onmouseover="showMenu(this.id, 0, 0, 1, 0)"> <span class="smalltxt">[' +id + ']</span> <a href="###attachment" onclick="insertAttachtext(' + id + ');return false;">' + localfile + '</a></span>' : '<span class="smalltxt">[' + id + ']</span> ' + localfile);
	getID('attach_' + id).style.display = 'none';
	addAttach();
}

function attachpreview(obj, preview, width, height) {
	if(is_ie) {
		getID(preview + '_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").sizingMethod = 'image';
		try {
			getID(preview + '_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = obj.value;
		} catch (e) {
			alert(lang['post_attachment_img_invalid']);
			return;
		}
		var wh = {'w' : getID(preview + '_hidden').offsetWidth, 'h' : getID(preview + '_hidden').offsetHeight};
		var aid = getID(preview + '_hidden').alt;
		if(wh['w'] >= width || wh['h'] >= height) {
			wh = attachthumbImg(wh['w'], wh['h'], width, height);
		}
		getID(preview + '_hidden').style.width = wh['w']
		getID(preview + '_hidden').style.height = wh['h'];
		getID(preview + '_hidden').filters.item("DXImageTransform.Microsoft.AlphaImageLoader").sizingMethod = 'scale';
		getID(preview).style.width = 'auto';
		getID(preview).innerHTML = '<img style="filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=\'scale\',src=\'' + obj.value+'\');width:'+wh['w']+';height:'+wh['h']+'" src=\'admin/images/none.gif\' border="0" alt="" />';
	}
}

function insertAttachtext(id) {
	InsertHTML('[local]' + id + '[/local]');
}

function attachthumbImg(w, h, twidth, theight) {
	twidth = !twidth ? thumbwidth : twidth;
	theight = !theight ? thumbheight : theight;
	var x_ratio = twidth / w;
	var y_ratio = theight / h;
	var wh = new Array();
	if((x_ratio * h) < theight) {
		wh['h'] = Math.ceil(x_ratio * h);
		wh['w'] = twidth;
	} else {
		wh['w'] = Math.ceil(y_ratio * w);
		wh['h'] = theight;
	}
	return wh;
}

function restore(aid) {
	obj = getID('attach'+aid);
	objupdate = getID('attachupdate'+aid);
	obj.style.display = '';
	objupdate.innerHTML = '';

}

function attachupdate(aid) {
	obj = getID('attach'+aid);
	objupdate = getID('attachupdate'+aid);
	obj.style.display = 'none';
	objupdate.innerHTML = '<input type="file" name="attachupdate[paid' + aid + ']" size="15"> <input class="button" type="button" value="' + lang['cancel'] + '" onclick="restore(\'' + aid + '\')">';
}

function insertAttachTag(aid) {
	InsertHTML('[attach]' + aid + '[/attach]');
}

function insertAttachimgTag(aid) {
	eval('var attachimg = getID(\'preview_' + aid + '\')');
	InsertHTML('<img src="' + attachimg.src + '" border="0" aid="attachimg_' + aid + '" width="' + attachimg.width + '" alt="" />');
}
