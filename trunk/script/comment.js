function get_object(idname){
	if (document.getElementById){
		return document.getElementById(idname);
	}else if (document.all){
		return document.all[idname];
	}else if (document.layers){
		return document.layers[idname];
	}else{
		return null;
	}
}


function openlogin()
{
	var x    = new XHR("re_openlogin");
	var url  = "comment.php?job=pub";
	x.get(url);
}
function re_openlogin(ret)
{
	//alert(ret);
	if(ret == 400)
	{
		if (get_object('logined').style.display=="none")
		{
			get_object('logined').style.display="block"
			//get_object('loginform').style.display="none";
			get_object('c_author').focus();
		}else{
			get_object('logined').style.display="none";
		}
	}	
}
function closelogin()
{
	
	get_object('logined').style.display="none";

}
function showpost() {
	var x    = new XHR("re_showpost");
	var url  = "comment.php?job=showpost&cid="+cid+"&mid="+mid+"&tid="+tid;
	x.get(url);
}

function re_showpost(ret)
{
	if(ret != null ) get_object('comment__c').innerHTML=ret;
	return false;
}

function sendPwd()
{
	var user = document.login.user.value;
	var pwd  = document.login.pass.value;
	var x    = new XHR("re_sendPwd");
	var url  = "comment.php?p=login&username="+user+"&password="+pwd;
	
	x.get(url);
	return false;
	
}
function re_sendPwd(ret)
{
	
	if(ret=="400")
	{
		get_object('loginform').style.display="none";
		get_object('logined').style.display="block";
	}
	else if(ret == "100")
	{
		get_object('result_message').innerHTML="验证不成功";
	}
	else if(ret == "200")
	{
		get_object('result_message').innerHTML="用户名不存在";
	}
	else
	{
		get_object('result_message').innerHTML="用户名密码错误";
	}
	return false;
	
}
function init_message(num)
{
	var time = new Date();
	var timestamp = time.valueOf();
	var x    = new XHR("re_init_message");
	var url = "comment.php?job=getcomment&tid="+tid+"&shownum="+num+"&mid="+mid+'&time='+timestamp+'&cid='+cid;
	//document.write(url);
	x.get(url);
}

function init_hotcomment(num)
{
	var time = new Date();
	var timestamp = time.valueOf();
	var x   = new XHR("re_init_hotcomment");
	var url = "comment.php?job=gethotcomment&tid="+tid+"&shownum="+num+"&mid="+mid+'&cid='+cid+'&ajax=1&time='+timestamp;
	//document.write(url);
	x.get(url);
}

function re_init_hotcomment(ret)
{
	if(ret != null ) get_object('hotcomment').innerHTML=ret;
	return false;
}

function re_init_message(ret)
{
	if(ret=='wait'){
		alert("休息休息再发表评论:)");
		document.getElementById('postSub').disabled='';
	}else if(ret=='ckerror'){
		alert("验证码错误！");
		document.getElementById('postSub').disabled='';
		var nowtime	 = new Date().getTime();
		get_object('cknum').src='ck.php?nowtime='+nowtime;
		get_object('ck').focus();
	}else if(ret=='success'){
		document.location.href = "comment.php?job=main&cid="+cid+"&mid="+mid+"&tid="+tid;
	}else if(ret != null ) get_object('comment__c').innerHTML=ret;
	return false;
}

 function sendMessag(obj,num,type)
 {
	if(obj.c_message.value == "" )
	{
		alert("你说些什么呢？");
		obj.c_message.focus();
		return false;
	}
	var texts  = obj.c_message.value;
	var author = obj.c_author.value;
	var hideip;
	if (obj.hideip.checked==true) {
		hideip = 1;
	}else {
		hideip = 0;
	}
	var string = new String(texts);
	if(string.length > 300)
	{
		alert("不要太长哦,最多150个汉字");
		return false;
	}
	document.getElementById('postSub').disabled=true;
	//alert('提交评论中...');
	var message = obj.c_message.value.replace(RegExp('\n', 'g'),"::wind::");
	//var support = obj.support.value; 
	var x    = new XHR("re_init_message");
	var url ="comment.php";
	if (!type) {
		url =url+"?ajax=1";
	}
	if(get_object('ck')){
	var ck	= get_object('ck').value;
	}else{
		var ck = '';
	}
	x.post(url,"job=addmsg&c_author="+author+"&c_message="+message+"&tid="+tid+"&mid="+mid+"&cid="+cid+"&ck="+ck+'&shownum='+num+'&hideip='+hideip);
	return false;
 }
function checkInput(){
	var author	= document.getElementById("c_author");
	var message = document.getElementById("c_message");

	var errormsg = "";

	if(author.value.length > 15){
		errormsg = "用户不能超过15个字符\n";
		author.focus();
	}
	if(message.value.match(/^\s*$/)){
		errormsg = errormsg + "请填写评论的内容\n";
		message.focus();
	}
	if(message.value==("请您在这里发表您的个人看法，发言时请各位遵纪守法并注意语言文明！")){
		errormsg = errormsg + "请填写评论的内容\n";
		message.value="";
		message.focus();
	}
	if(message.value.length > 300){
		errormsg = errormsg + "填写评论的内容不能超过150个汉字\n";
		message.focus();
	}
	if(errormsg){
		alert(errormsg);
		return false;
	}
}
 
 function getinfo(query,pageno)
{
	var x = new XHR("re_init_message");
    var url = query+"="+pageno;
	x.get(url);
	
}

function insertFace(id)
{
	
	if(id > 0 && document.mform.support.value != 2 && id < 16 )
	{
		document.mform.support.value = 1;
	}
	else if(id > 15 && document.mform.support.value != 1 && id < 31)
	{
		document.mform.support.value = 2;
	}
	else
	{
		document.mform.support.value = 0;
	}
	document.mform.c_message.value = document.mform.c_message.value + "[:"+id+":]";
}

function quick(v)
{
	document.mform.c_message.value = document.mform.c_message.value + v;
}


function delmsg(msgid,pageno,id,flag,num)
{
	
	var ifdel = confirm("你确定要删除这条评论和回复吗?");
	if(ifdel == true)
	{
		var x = new XHR("re_init_message");
		var url = "comment.php?p=del&id="+msgid+"&pageno="+pageno+"&prgmid="+id+"&flag="+flag+"&num="+num;
		x.get(url);
	}
}


function get_tags(parentobj, tag){
	if (typeof parentobj.getElementsByTagName != 'undefined'){
		return parentobj.getElementsByTagName(tag);
	}else if (parentobj.all && parentobj.all.tags){
		return parentobj.all.tags(tag);
	}else{
		return null;
	}
}
function unhtmlspecialchars(str){
	f = new Array(/&lt;/g, /&gt;/g, /&quot;/g, /&amp;/g);
	r = new Array('<', '>', '"', '&');
	for (var i = 0; i < f.length; i++){
		str = str.replace(f[i], r[i]);
	}
	return str;
}
function htmlspecialchars(str){
	var f = new Array(new RegExp('&', 'g'),new RegExp('<', 'g'),new RegExp('>', 'g'),new RegExp('"', 'g'));
	var r = new Array('&amp;','&lt;','&gt;','&quot;');
	for (var i = 0; i < f.length; i++){
		str = str.replace(f[i], r[i]);
	}
	return str;
}

function replay(id)
{
	var editid = "div_"+id;
	obj = get_object(editid);
	Editor = new AJAX_Editor(obj);
}
function AJAX_Editor(obj){
	obj          = obj;
	div_id       = obj.id.substr(obj.id.lastIndexOf('_') + 1);
    var editid = "div_"+div_id;
	obj = get_object(editid);
	var replayobj = get_object(div_id);
	var clickid   = "click_"+div_id;
	var clickobj = get_object(clickid);
	replayobj.style.display = "none";
	clickobj.style.display = "";
	linkobj      = get_object('div_' + div_id);
	container    = linkobj.parentNode;
	editobj      = null;
	editor_state = false;
	AJAX_edit();
	obj1 = get_object(div_id);
	obj2 = get_object("click_"+div_id);
	obj3 = get_object("author_"+div_id);
	obj4 = get_object("replaylink_"+div_id);
	
}
function AJAX_edit(){
	if (editor_state == false){
		Ajaxobj = AJAX_creat();
		editobj = container.insertBefore(Ajaxobj,linkobj);
		editobj.select();
		linkobj.style.display = 'none';
		editor_state = true;
	}
}

function AJAX_creat(){
	Ajaxobj        = document.createElement('textarea');
	Ajaxobj.className ="pic text";
	Ajaxobj.value = unhtmlspecialchars(linkobj.innerHTML.replace(RegExp('<BR>', 'g'),"\n"));
	Ajaxobj.onblur = AJAX_store;
	return Ajaxobj;
}

function AJAX_save(content,msgid){
	content = content.replace(RegExp('\n', 'g'),"**br**");
	var x = new XHR("re_AJAX_save");
	var url="comment.php?p=reply&tid="+tid+"&content="+content+"&id="+msgid+"&flag="+flag;
	x.get(url);

}	

function re_AJAX_save(ret)
{
}
function AJAX_store(){
	if (editor_state == true){
		var textss = editobj.value;
		var strings = new String(textss);
		//alert(string.len);
		if(strings.len() > 300)
		{
			alert("不要太长哦,最多150个汉字");
			return false;
		}
		AJAX_save(editobj.value,div_id);
		 editobj.value=htmlspecialchars(editobj.value);
	    editobj.value = editobj.value.replace(RegExp('\n', 'g'),'<BR>');
		if(trim(editobj.value))
		{
			linkobj.innerHTML = editobj.value;
			obj3.style.display = '';
			obj4.innerHTML = "编辑";
		}
		else
		{   
		    obj3.style.display = 'none';
			linkobj.innerHTML     = "";
			linkobj.style.display = "none";
			linkobj.style.border  = "0px dashed #000033" ;
			linkobj.style.padding  = "0px" ;
			obj4.innerHTML = "回复";
		}
		
		
		container.removeChild(editobj);
		linkobj.style.display = '';
		
		obj1.style.display = '';
		obj2.style.display = "none";			
		editor_state = false;
		obj = null;
	}
}
