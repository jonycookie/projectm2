/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
**/
var settings = {
	commentURI:"/action.php?do=comment&callback=?&action=",
	diggURI:"/action.php?do=digg&callback=?&action=do"
}
function digg(act,aid,cid){
	if(act=='up'||act=='against'){
		var pars = {'id':aid,'cid':cid,'ajax':'1',t: Math.random()};
		$.getJSON(settings.commentURI+act,pars, function(json){
		 	if(json.state=='1'){
			 	var Num=parseInt($("#"+act+"_"+cid).text());
			 	$("#"+act+"_"+cid).text(Num+1); 
		 	}
		 	if(json.state=='0'){
		 	 	alert(json.text);
		 	 }
		});
		return;
	}
	if(act=='digg'){
		var pars = {'id':aid,'t': Math.random()};
		$.getJSON(settings.diggURI,pars, function(json){
		 	if(json.state=='1'){
			 	var Num=parseInt($("#"+act+"_"+aid).text());
			 	$("#"+act+"_"+aid).text(Num+1); 
		 	}
		 	if(json.state=='0'){
		 	 	alert(json.text);
		 	 }
		});
		return;
	}
}
function quote(cid){
	$("#cQuote").val(cid);
	if (typeof(FCKeditorAPI)=='undefined'){
		addUBB('');
//		addUBB('[quote][span]----- 以下引用 [b][em]' + $("#comment_username_" + cid).text() + '[/em][/b] 于 ' + $("#comment_time_" + cid).text() + ' 的发言 -----[/span][p]' + $("#comment_contents_" + cid).text() + '[/p][/quote]');
	}else{
		var oEditor = FCKeditorAPI.GetInstance('commentext');
		oEditor.InsertHtml("");
		oEditor.focus();
//		addHTML('<div class="quote"><span>----- 以下引用 <strong><em>' + $("#comment_username_" + cid).text() + '</em></strong> 于 ' + $("#comment_time_" + cid).text() + ' 的发言 -----</span><p>' + $("#comment_contents_" + cid).html() + '</p></div>');
	}
}
function reply(cid){
	if (typeof(FCKeditorAPI)=='undefined'){
		addUBB('[reply]---[i]回复[/i] ' + $("#lou_" + cid).text() + ' [' + $("#comment_username_" + cid).text() + '] 时间:[' + $("#comment_time_" + cid).text() + "]---[/reply]\r\n");
	}else{
		addHTML('<span class="reply">---<i>回复</i> ' + $("#lou_" + cid).text() + ' [' + $("#comment_username_" + cid).text() + '] 时间:[' + $("#comment_time_" + cid).text() + ']---</span><br />');
	}
}
function addUBB(ubb){
	$('#cCommentext').val(ubb).focus();;
}
function addHTML(html){
	var oEditor = FCKeditorAPI.GetInstance('commentext');
	if ( oEditor.EditMode == FCK_EDITMODE_WYSIWYG ){
		oEditor.InsertHtml(html);
	}else{
		alert("评论框不支持此功能！");
	}
}
function textareasize(obj) {
	if(obj.scrollHeight > 70) {
		obj.style.height = obj.scrollHeight + 'px';
	}
}