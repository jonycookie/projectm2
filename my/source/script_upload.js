/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: script_upload.js 7180 2008-04-25 07:13:14Z zhengqingpeng $
*/

var aid = 1;
var attachexts = new Array();
var attachwh = new Array();

var thumbwidth = parseInt(60);
var thumbheight = parseInt(60);
var extensions = 'jpg,gif,png';
var forms;
var nowUid = 0;
var albumid = 0;
var uploadStat = 0;
var picid = 0;
var upid = 0;
var mainForm;
var successState = false;
var lang = new Array();
var ieVersion = userAgent.substr(userAgent.indexOf('msie') + 5, 3);
lang['post_attachment_ext_notallowed']	= '对不起，不支持上传此类扩展名的文件。';
lang['post_attachment_deletelink']	= '删除';
lang['post_attachment_insert']		= '点击这里插入内容中当前光标的位置';
lang['post_attachment_insertlink']	= '插入';
lang['need_ie'] = '对不起，请在IE浏览器下面使用本功能';

function delAttach(id) {
	$('attachbody').removeChild($('attach_' + id).parentNode.parentNode.parentNode);
	if($('attachbody').innerHTML == '') {
		addAttach();
	}
	$('localimgpreview_' + id + '_menu') ? document.body.removeChild($('localimgpreview_' + id + '_menu')) : null;
}

function addAttach() {
	newnode = $('attachbodyhidden').rows[0].cloneNode(true);
	var id = aid;
	var tags;
	tags = newnode.getElementsByTagName('form');
	for(i in tags) {
		if(tags[i].id == 'upload') {
			tags[i].id = 'upload_' + id;
		}
	}
	tags = newnode.getElementsByTagName('input');
	for(i in tags) {
		if(tags[i].name == 'attach') {
			tags[i].id = 'attach_' + id;
			tags[i].name = 'attach';
			tags[i].onchange = function() {insertAttach(id)};
			tags[i].unselectable = 'on';
		}
		if(tags[i].id == 'albumid') {
			tags[i].id = 'albumid_' + id;
		}
	}
	tags = newnode.getElementsByTagName('span');
	for(i in tags) {
		if(tags[i].id == 'localfile') {
			tags[i].id = 'localfile_' + id;
		}
	}
	aid++;
	$('attachbody').appendChild(newnode);
}

addAttach();

function insertAttach(id) {
	var localimgpreview = '';
	var path = $('attach_' + id).value;
	var ext = getExt(path);
	var re = new RegExp("(^|\\s|,)" + ext + "($|\\s|,)", "ig");
	var localfile = $('attach_' + id).value.substr($('attach_' + id).value.replace(/\\/g, '/').lastIndexOf('/') + 1);

	if(path == '') {
		return;
	}
	if(extensions != '' && (re.exec(extensions) == null || ext == '')) {
		alert(lang['post_attachment_ext_notallowed']);
		return;
	}
	attachexts[id] = inArray(ext, ['gif', 'jpg', 'png']) ? 2 : 1;

	var $inhtml = '<table cellspacing="2" cellpadding="2">';
	if(is_ie && ieVersion < 7.0) {
		$inhtml += '<tr><td><img src="' + $('attach_' + id).value+'" width="60" height="80"></td>';
	} else {
		$inhtml += '<tr><td>' + localfile +'</td></tr><tr>';
	}
	$inhtml += '<td>图片描述:<br/><textarea name="pic_title" cols="40" rows="3"></textarea>';
	$inhtml += '<span id="showmsg' + id + '"><a href="javascript:;" onclick="delAttach(' + id + ')">[' + lang['post_attachment_deletelink'] + ']</a></span>';
	if(is_ie && typeof no_insert=='undefined') {
		$inhtml += '<a href="javascript:;" title="' + lang['post_attachment_insert'] + '" onclick="insertAttachimgTag(' + id + ');return false;">[' + lang['post_attachment_insertlink'] + ']</a>';
	}
	$inhtml += '</td></tr></table>';
	
	$('localfile_' + id).innerHTML = $inhtml;
	$('attach_' + id).style.display = 'none';

	addAttach();
}

function inArray(needle, haystack) {
	if(typeof needle == 'string') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function insertAttachimgTag(id) {
	if(is_ie) {
		edit_insert('<img id="_uchome_localimg_' + id + '" src="' + $('attach_' + id).value + '">');
	} else {
		alert(lang['need_ie']);
	}
}

function uploadSubmit(obj) {
	obj.disabled = true;
	mainForm = obj.form;
	forms = $('attachbody').getElementsByTagName("FORM");
	albumid = $('uploadalbum').value;
	upload();
}

//上传页面
function start_upload() {
	$('btnupload').disabled = true;
	mainForm = $('albumresultform');
	forms = $('attachbody').getElementsByTagName("FORM");
	upload();
}

function upload() {
	if(typeof(forms[nowUid]) == 'undefined') return false;
	var nid = forms[nowUid].id.split('_');
	nid = nid[1];
	if(nowUid>0) {
		var upobj = $('showmsg'+upid);
		if(uploadStat==1) {
			upobj.innerHTML = '上传成功';
			successState = true;
			var InputNode;
			//两种生成方式，解决浏览器之间的兼容性问题
			try {
				//IE模式下的创建方式,解决常规setAttribute设置属性带来的一些未知的错误
				var InputNode = document.createElement("<input type=\"hidden\" id=\"picid_" + picid + "\" value=\""+ upid +"\" name=\"picids["+picid+"]\">");
			} catch(e) {
				//非IE模式则须要用下列的常规setAttribute设置属性，否则生成的结果不能正常初始化
				var InputNode = document.createElement("input");
				InputNode.setAttribute("name", "picids["+picid+"]");
				InputNode.setAttribute("type", "hidden");
				InputNode.setAttribute("id", "picid_" + picid);
				InputNode.setAttribute("value", upid);
			}
			mainForm.appendChild(InputNode);

		} else {
			upobj.innerHTML = '上传失败:'+uploadStat;
		}
	}
	if($('showmsg'+nid) != null) {
		$('showmsg'+nid).innerHTML = '上传中，请等待(<a href="javascript:;" onclick="forms[nowUid].submit();">重试</a>)';
		$('albumid_'+nid).value = albumid;
		forms[nowUid].submit();
	} else if(nowUid+1 == forms.length) {
		if(typeof (no_insert) != 'undefined') {
			var albumidcheck = parseInt(parent.albumid);
			$('opalbumid').value = isNaN(albumidcheck)? 0 : albumid;
			if(!successState) return false;
		}
		mainForm.submit();
	}
	upid = nid;
	nowUid++;
	uploadStat = 0;
}
