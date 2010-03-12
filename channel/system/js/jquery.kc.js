/* start ready */

if(typeof console != 'undefined'){
window._log=function(){console.log.apply(console, arguments);};
window._profile=function(){(arguments.length?console.profile:console.profileEnd).apply(console, arguments);};
}else{
window._log=function(){};
window._profile=function(){};
}

$(document).ready(function(){

var body = $(document.body);

//插入k_ajax浮动层
body.append('<div id="k_ajax" class="none"></div>');

$.kc_ready();
//用ESC关闭ajax窗口
$($.browser.msie?document:window).keydown(function(e){$.kc_keydown(e.keyCode)});

$('.k_help').reset_href().click(function(){$.kc_help_click(this)}).blur(function(){$('#k_help_Fly').fadeOut(300)});

//右键
$('.k_table_list').bind("contextmenu",function(e){   
	//在这里书写代码，构建个性右键化菜单
	//

	if(!(($.browser.msie && e.button==0) || (!$.browser.msie && e.button==2))) return;
/*
	if($('#k_list_right_Fly').length==0){

		var s='<div id="k_list_right_Fly" class="none" onClick="$(this).fadeOut(300)">';
		s+=$.kc_icon('k8');
		s+='<ul>'+$('#k_cmd_Fly').html()+'</ul></div>'

		$('body').append(s);

		$.kc_ready('#k_list_right_Fly ');
	}
*/
	if($('#k_list_right_Fly').length>0){
		$('#k_list_right_Fly')
			.css('top',e.pageY-1).css('left',e.pageX-1)
			.show();
		return false;
	}else{
		return true;
	}
	//取消默认的右键菜单 
}).click(function(){
	$('#k_list_right_Fly').fadeOut(300);
});

});/* end ready */


/* KingCMS 扩展函数 */

(function ($){
$.fn.reset_href=function(h){
  return this.attr('href',h===undefined ? 'javascript:;' : h);
};

/* 字符串格式化函数，类似 printf
$.strf('%sx%s=%s', 5, 4, 3*4) => '5x4=20'

$.strf('比率为 %s%%', 20) => '比率为 20%'
*/
$.strf = function (s){
  var args = arguments,
      s = args[0],
      idx = 1;
  
  if(!s) return '';
  
  return s.replace(/%([s%])/g,function(a){
      return (''+args[idx++]) || '';
    });
};

$.kc_ready=function(pre){

	if(pre==undefined){
		pre=''
	}else{
		pre =$("head meta[name='generator']").attr('content')=='KingCMS' ? pre+' ': '';
	}

	$(pre+'.k_ajax').reset_href().click(function(){$.kc_ajax($(this).attr('rel'))});
	$(pre+'.k_goto').reset_href().click(function(){$.kc_goto($(this).attr('rel'));return false;});/*不要删除return false,否则IE6下出现无法跳转的bug*/
	//$(pre+'a.k_ajax,a.k_help,a.k_setvalue,a.k_updown').attr('href','javascript:;');//重置k_ajax链接为空->javascript:;

	$(pre+'.k_aselect').click(function(){$("#k_form_list input[name=list]").attr('checked',true)});//全选
	$(pre+'.k_rselect').click(function(){$("#k_form_list input[name=list]").each(function(i){//反选
		this.checked=!this.checked;
    })});

	$(pre+'.k_setvalue').reset_href().click(function(){$.kc_setvalue_click(this)}).blur(function(){$('#k_setvalue_Fly').fadeOut(300)});

	$(pre+'.k_updown').reset_href().click(function(){$.kc_updown_click(this)}).blur(function(){$('#k_updown_Fly').fadeOut(300)});

	$(pre+'.k_cmd').click(function(){$.kc_cmd_click(this)}).blur(function(){$('#k_cmd_Fly').fadeOut(300)});

	$(pre+'.k_calendar').click(function(){$.kc_calendar(this)}).blur(function(){$('#k_calendar_Fly').fadeOut(300)});

	$(pre+'.k_color').click(function(){$.kc_color(this)}).blur(function(){$('#k_color_Fly').fadeOut(300)});

	$(pre+'.k_float').mousedown(function(e){$.kc_float(this,e)}).fadeTo(50,1.0);

	$(pre+'.k_verify').click(function(){$.kc_verify(this)}).focus(function(){$.kc_verify(this)});

	$(pre+"*[class^='k_user_']").reset_href().click(function(){$.kc_user_action(this,pre)});

	$(pre+'.k_close').reset_href().click(function(){
		$.kc_close();
	});

	$(".k_table_list tr").hover(function(){
		$(this).children('td').addClass('hover');
	},function(){
		$(this).children('td').removeClass('hover');
	});

}

$.kc_verify=function(obj){
	var id=obj.id;
	if($('#'+id+'_Fly').length==0){
		$(obj).after('<span id="'+id+'_Fly" class="none k_verify_Fly"></span>');
    $.kc_ajax({URL:$.kc_root()+'system/verify.php',ID:id+'_Fly',CMD:'salt'});
	}
	$('#'+id+'_Fly').css('position','absolute').fadeIn(100);
}
$.kc_help_click=function(obj){
	var rel=eval('('+obj.rel+')');
	var width=rel.width;
	var o=$(obj).children('img').offset();
	var left=17;
	var top=17;

	if($('#k_help_Fly').length==0){
		$('body').append('<div id="k_help_Fly" class="none"></div>');
	}
	$.kc_ajax(obj.rel);

	$('#k_help_Fly').css('top',o.top+top).css('left',o.left+left).css('zIndex',1100).fadeIn(100).click(function(){$(this).fadeOut(300)});
}

$.kc_progress=function(id,title,text,prop){
	$('#'+id).children('label').html(title).end()
    .children('var').html(text).end()
    .find('>span>em').width(prop);
}

$.kc_userstate=function(id){

	var cookie=document.cookie;
/*
	var auth=cookie.match(/auth_[a-zA-Z0-9]+\=\d+\%09[a-zA-Z0-9%]{3,200}\%09\w{32}/);
*/
	var re=new RegExp('auth_[a-zA-Z0-9]+\=(\\d+)\\%09([a-zA-Z0-9%]{3,200})\\%09(\\w{32})');
	var auth=re.exec(cookie);

	if(auth!==null){
		var userid=RegExp.$1;
		var username=decodeURIComponent(RegExp.$2);
		var auth_obj=$('#'+id+'_hide');
		var auth_htm=auth_obj.html();



		auth_htm=auth_htm.replace('[USERID]',userid);
		auth_htm=auth_htm.replace('[USERNAME]',username);
		auth_obj.html(auth_htm);
		auth_obj.show();

		$('#'+id).hide();
	}
}

$.kc_border=function(obj){
	var aa=new Array('paddingLeft','paddingRight','marginLeft','marginRight','borderLeft','borderRight');
	var num=0;
	var tmp
	for (key in aa){
		tmp=$(obj).css(aa[key]);
		if(tmp!=undefined) num=num + Number(tmp.match(/\d+/));//怪事,不能用parseInt获取数字部分?
	}//parseInt(str, 10) || 0 第二个参数是进制，|| 0 是为了防止 NaN
	return num;
}
$.kc_user_action=function(obj,pre){
	var cls=$(obj).attr('class');
	var s=cls.match(/k_user_[a-z]+/i)[0];
	$.kc_ajax({URL:$.kc_root()+'user/index.php',METHOD:'GET',CMD:s.substr(7,s.length-7),IS:(pre==''?0:1)});
}

$.kc_keydown=function(Key){//键盘响应
	switch(Key){
	case 27:
		$.kc_close();
	break;

	case 13:
//			$.kc_ajax();
	break;
	}
}

$.kc_goto=function(Rel){//跳转到

	var rel=eval('('+Rel+')');//rel值
	var url= (rel.url==undefined ? parent.location.href : rel.url);//跳转到

	parent.location.href=url;
}

$.kc_href=function(){//获得当前页地址
	var s=parent.location.href;
	var num;
	num=s.indexOf('#');
	if(num>0) s=s.substring(0,num);
	num=s.indexOf('?');
	if(num>0) s=s.substring(0,num);
	return s;
}


$.kc_root=function(){//返回根目录
	if($.kc_root.path){
    return $.kc_root.path;
  }
	var sc=$('head script:first[src$=\'system/js/jquery.kc.js\']');
	var fp=sc.attr('src');//filepath
	
	return $.kc_root.path = fp.substring(0,fp.length-22);
}

$.kc_ajax=function(Rel){//ajax调用
	var rel=(typeof Rel=='string') ? eval('('+Rel+')') : Rel;//rel值
	var cmd=(rel.CMD==undefined?'':rel.CMD);//cmd
	var url=(rel.URL==undefined?$.kc_href():rel.URL);//post地址
	var id=(rel.ID==undefined?'k_ajax':rel.ID);//返回输出值的id值
	var is=(rel.IS==undefined?0:rel.IS);//loading的显示类型，默认右上角显示,1的时候不显示loading,2的时候显示图片loading
	var form=(rel.FORM==undefined?'#k_ajaxForm':'#'+rel.FORM);//表单域
	var v=rel.VAL;//一并提交的指定值,各个值之间用逗号分开
	//操作验证：删除/清理/退出
	var t_ = cmd.match(/^(delete|clear|close|logout)/);

	if(t_ && !confirm($.kc_lang(t_[0]))){
	return false;
	}
	
	//rel中的预设值
	var postdata='';
	$.each(rel,function(i,n){
		postdata+=(i+"="+n+'&');
	});
	//VAL中指定的值
	if(v!=undefined){
		var array_v=v.split(',');
		$.each(array_v,function(i,n){
			postdata+=(n+'='+$('#'+n).val())+'&';
		});
	}
	//form表单中的值
	var formdata=$(form).serialize();
	var formdatas=formdata.split('&');
	var tmp=new Array;
	if(formdatas.length){
		$.each(formdatas,function(i,n){
			if(n.length>0){
				var nn=n.split('=');
				if(tmp[nn[0]]==undefined){
					tmp[nn[0]]=nn[1];
				}else{
					tmp[nn[0]]+=','+nn[1];
				}
			}
		});
		for (key in tmp){
			postdata+='&'+key+'='+tmp[key];
		}
	}
	if(id=='k_ajax'&&is!=1){
		$('#k_ajax').html('<div id="k_ajaxFly">Loading...</div>');
		$('#k_ajaxFly').css({
      'position':'absolute',
      'top':document.documentElement.scrollTop,
			'left':$(document).width()-$('#k_ajaxFly').width()-$.kc_border($('#k_ajaxFly'))
    });
	}
	if(is==2){
		$('#'+id).html('<img class="k_loading" src="../system/images/loading.gif"/>');
	}
	$.ajax({
		url:url+'?action=ajax',
		type:'POST',
		data:postdata,
		dataType:'html',
		timeout:30000,
		error:function(){if(id=='k_ajax'){$.kc_ajax_show("{title:'Error!',main:'<p class=\"k_err\">'+$.kc_lang('timeout')+'</p>',but:'<a href=\"javascript:;\" class=\"k_close\">'+$.kc_lang('enter')+'</a>',width:320,height:100}");}//else{alert($.kc_lang('timeout'))}//k_msg出错?
		},
		success: function(s){
			if(id=='k_ajax'){
				$.kc_ajax_show(s)
			}else{

				var d=eval('('+s+')');//通过php处理后返回的值
				var main=d.main;
				var js=d.js;

				if(main!=''){
					$('#'+id).html(main);
					$('#'+id+' .k_ajax').attr('href','javascript:;').click(function(){
						$.kc_ajax(this.rel);
					});

				}

				if(js!=''){
					eval(js);
				}
			}
		}
	});
}



$.kc_ajax_show=function(s){//构造ajax交互窗口
	if(s.length<10){//若s为空值,则提示错误
		s="{title:'Error!',main:'<p class=\"k_err\">'+$.kc_lang('empty')+'</p>',but:'<a href=\"javascript:;\" class=\"k_close\">'+$.kc_lang('enter')+'</a>',width:320,height:100}";
	}
	try{
		var d=eval('('+s+')');//通过php处理后返回的值
	}catch(e) {
		alert(e+'\n\n'+(s.length>5002?s.substr(0,5000)+'...':s));
		return;
	}

	var title=d.title;
	var main=d.main;
	var but=d.but;
	var width=d.width;
	var height=d.height;
	var js=d.js;
	// 赋值并显示
	if(title!=''||main!=''){

		$('html').css('overflow','hidden');

		var w=$(window).width();
		var h=$(window).height();

		if($.browser.opera){//opera下$(window).height 并不是可是区域大小,不知道是不是jQuery的bug?
			h=document.documentElement.clientHeight;
		}

		var str='<div id="k_ajaxBg"></div>';
		str+='<form id="k_ajaxForm">';
		str+='<div id="k_ajaxBox">';
		str+='<div id="k_ajaxTitle"><strong id="k_ajaxTtitle"></strong><a class="k_close" href="javascript:;"></a></div>';
		str+='<div id="k_ajaxMain">';
		str+='<div id="k_ajaxContent"></div>';
		str+='</div>';
		str+='<div id="k_ajaxSubmit"></div>';
		str+='</div></form>';

		$('#k_ajax').html(str).show();
		$('#k_ajaxBg').css({
			'top':document.documentElement.scrollTop,
			'width':w+30,
			'height':h+30,
			'left':document.documentElement.scrollLeft
		});
		$('#k_ajaxTitle').width(width);
		$('#k_ajaxTtitle').width(width-$('#k_ajaxTitle a.k_close').width()).html(title);
		$('#k_ajaxMain').css({width:width,height:height});
		$('#k_ajaxContent').html(main);
		$('#k_ajaxSubmit').width(width-$.kc_border($('#k_ajaxSubmit'))).html('<p>'+but+'</p>');
		var nHeight=height+$('#k_ajaxTitle').height()+$('#k_ajaxSubmit').height();//完整的高度

		$('#k_ajaxBox').css({
      'left':document.documentElement.scrollLeft+(w-width)/2,
      'width':width,
      'height':nHeight,
      'zindex':'901',
      'position':'absolute',
      'top':document.documentElement.scrollTop+((h-nHeight)/2)
    });

		$(window).resize(function(){
			w=$(window).width();
			h=$(window).height();

			height=$('#k_ajaxMain').height();
			nHeight=height+$('#k_ajaxTitle').height()+$('#k_ajaxSubmit').height();//完整的高度

			if($.browser.opera){
				h=document.documentElement.clientHeight;
			}

			$('#k_ajaxBg').css('width',w+30).css('height',h+30);
			$('#k_ajaxBox').css('top',document.documentElement.scrollTop+((h-nHeight)/2)).css('left',document.documentElement.scrollLeft+(w-width)/2);

		}).scroll(function(){//Opera下隐藏滚动条的情况下还能上下拖动..
			w=$(window).width();
			h=$(window).height();

			height=$('#k_ajaxMain').height();
			nHeight=height+$('#k_ajaxTitle').height()+$('#k_ajaxSubmit').height();//完整的高度

			$('#k_ajaxBg').css('width',w+30).css('height',h+30).css('top',document.documentElement.scrollTop).css('left',document.documentElement.scrollLeft);
			$('#k_ajaxBox').css('top',document.documentElement.scrollTop+((h-nHeight)/2)).css('left',document.documentElement.scrollLeft+(w-width)/2);
		});


		$('#k_ajaxContent input:first').focus();//焦点定位到第一个input框,IE7/8下失效?

		$.kc_ready('#k_ajax');

		var tipp=$('#k_ajax p.k_err,#k_ajax p.k_ok');
		if(tipp.html()!=null){
			$('#k_ajax #k_ajaxMain').html('<table cellspacing="0" class="k_tip"><tr><th><img src="'+$.kc_root()+'system/images/'+tipp.attr('class').substr(2,tipp.attr('class').length-2)+'.gif"/></th><td>'+tipp.html()+'</td></tr></table>');
		}
		$('#k_ajaxBox input').keydown(function(event){
			if(event.keyCode==13){
				$.kc_ajax($('#k_ajaxSubmit a.k_ajax:first').attr('rel'));//读取第一个k_ajax操作按钮的rel并调用$.kc_ajax
			}
		});
/*
		$('#k_ajaxBox form').each(function(_, form){
           form.onsubmit = function(){
               $('a.k_ajax:first', form).click();
               return false;
           };
       });
*/
		$('#k_ajaxForm').submit(function(){
		  return false;
		});

	}

	if(js!=''){
		eval(js);
	}
}

$.kc_close=function(){//隐藏ajax
	$('html').css('overflow','');
	$('#k_ajax').empty();
}

$.kc_nbsp=function(s){//空值替换为&nbsp;
	return s==''||s==0?'&nbsp;':s;
}

$.kc_long2ip=function(nlong) {
	if (nlong < 0){
		nlong=nlong+4294967296;
	}
	if(nlong > 4294967295){
		return false;
	}
	ip = "";
	for (i=3;i>=0;i--) {
		ip += parseInt(nlong / Math.pow(256,i))
		nlong -= parseInt((nlong / Math.pow(256,i)))*Math.pow(256,i);
		if (i>0) ip += ".";
	}
	return ip;
}
/*
$.kc_formatdate=function(timec){
	var d,s,y,yy,h,ap;
	if (timec==0){
		s='-'
	}else{
		d = new Date(timec*1000);

		h=d.getHours();
		if (h<=12){
			ap='AM';
		}else{
			ap='PM';
			h-=12;
		}
		s=$.kc_double(d.getYear())+'-'
		s+=$.kc_double(d.getMonth() + 1) + "-"
		s+=$.kc_double(d.getDate()) + ",&nbsp;"
		s+=" "+$.kc_double(h);
		s+=":"+$.kc_double(d.getMinutes());
		s+='&nbsp;'+ap;
	}
	return(s);
}

$.kc_double=function(s){
	var I1,ss;
	ss=s.toString(10);
	if (ss.length>2){
		I1=ss.substr(ss.length-2,2);
	}else if(ss.length==2){
		I1=ss;
	}else{
		I1='0'+ss
	}
	return I1;
}
*/


/**
id      自动递增的id
tit     对应的标题
link    链接
isid      是否显示id
isgray  是否为灰调
ico     前置图片，直接写icon.gif中的图片表
space   有多少空格
first   最前面显示的内容，比如列表页的展开关闭之类
after   标题后面显示的内容，比如是否有图片等
*/
$.kc_list=function(id,tit,link,isid,isgray,ico,space,first,after){

	classname= isgray==0 ? ' gray' : '';
	ico= ico==undefined ? '' : $.kc_icon(ico);
	space= space==undefined ? '' : 'style="margin-right:'+20*(space-1)+'px;"';

	if(first==undefined) first='';

	var I1='<label class="k_cklist '+classname+'" for="list_'+id+'" title="'+tit+'"><input '+space+' name="list" id="list_'+id+'" type="checkbox" value="'+id+'"/>'+first+ico+'<u>';

	if(link!='' && link!=undefined && link.substr(0,1)!='{'){
		tit='<a href="'+link+'">'+tit+'</a>';
	}else if(link!=undefined && link!=0 && link!=''){
		if(link.substr(0,1)=='{' && link.substr(link.length-1,1)=='}')
			tit='<a href="javascript:;" class="k_ajax" rel="'+link+'">'+tit+'</a>';
	}

	I1+= isid==1 ? id+') '+ tit : tit;

	I1+='</u>';

	I1+= after==undefined ? '' : after ;

	I1+='</label>';
	return I1;
};
/**
clas   class类，即对应关系
alt    ALT属性
id     给图片设置id
*/
$.kc_icon=function(clas,alt,id){
	if (!alt) alt='';
	
	var hid = id ? ' id="'+id+'"' : '';
	
	return $.strf('<img%s class="os%s" alt="%s" title="%s" src="../system/images/white.gif"/>', 
              hid,
              clas ? ' ' + clas : '',
              alt,
              alt);
};

$.kc_updown=function(kid,cmd){
	if(cmd==undefined) cmd='updown';

	var s='<a href="javascript:;" class="k_updown" rel="{kid:'+kid+',CMD:\''+cmd+'\'}" title="'+$.kc_lang('updown')+'">';
	s+=$.kc_icon('n5',$.kc_lang('updown'))+'</a>';
	return s;
};

$.kc_updown_click=function(obj){
	var rel=eval('('+obj.rel+')');
	var kid=rel.kid;
	var cmd=rel.CMD;
	var o=$(obj).children('img').offset();
	var left=17;
	var top=17;
	

	if($('#k_updown_Fly').length==0){
		$('body').append('<div id="k_updown_Fly" class="none"></div>');
	}
	var I1='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:0,FORM:\'k_form_list\',UPDOWN:\'up\'}">'+$.kc_icon('m6')+' To</a>';
	I1+='<a href="javascript:;">'+$.kc_icon()+'</a>';
	for (var i=1;i<=9;i++){
		I1+='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:'+i+',FORM:\'k_form_list\',UPDOWN:\'up\'}">'+$.kc_icon('n6')+'  &nbsp;'+i+'</a>';
		I1+='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:'+i+',FORM:\'k_form_list\',UPDOWN:\'down\'}">'+$.kc_icon('n7')+' &nbsp;'+i+'</a>';
	}
	var arr=new Array(10,15,20);
	for(i=0;i<arr.length;i++){
		I1+='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:'+arr[i]+',FORM:\'k_form_list\',UPDOWN:\'up\'}">'+$.kc_icon('n6')+'  '+arr[i]+'</a>';
		I1+='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:'+arr[i]+',FORM:\'k_form_list\',UPDOWN:\'down\'}">'+$.kc_icon('n7')+'  '+arr[i]+'</a>';
	}
	I1+='<a href="javascript:;">'+$.kc_icon()+'</a>';
	I1+='<a class="k_ajax" href="javascript:;" rel="{CMD:\''+cmd+'\',kid:'+kid+',NUMBER:0,FORM:\'k_form_list\',UPDOWN:\'down\'}">'+$.kc_icon('m7')+' To</a>';

	//当页面太靠下的时候，显示在上端
	if(o.top+295>$(document).height() && o.top>400){
		top=-295;
	}

	$('#k_updown_Fly').html(I1).css('top',o.top+top).css('left',o.left+left).fadeIn(100);//位置定义

	$('#k_updown_Fly .k_ajax').click(function(){
		$.kc_ajax(this.rel);
	});

};

$.kc_setvalue_click=function(obj){
	var rel=eval('('+obj.rel+')');
	var id=rel.ID;
	var width=rel.width;
	var is=rel.IS;
	var o=$(obj).children('img').offset();
	var left=17;
	var top=17;

	if($('#k_setvalue_Fly').length==0){
		$('body').append('<div id="k_setvalue_Fly" class="none"></div>');
	}
	$('#k_setvalue_Fly').html($('#'+id+'_setvalue').val()).css('top',o.top+top).css('left',o.left+left).css('width',width).fadeIn(100);
	$('#k_setvalue_Fly a').attr('href','javascript:;').click(function(){
		var rel_a=eval('('+this.rel+')');
		$('#'+id).val(rel_a.value);
	
	});
}

$.kc_cmd_click=function(obj){

	var o=$(obj).offset();
	var left=0;
	var top=24;
	//#k_cmd_Fly

	$('#k_cmd_Fly').css({'top':o.top+top, 'left':o.left+left}).fadeIn(100);

}

$.kc_color=function(obj){
	var rel=eval('('+obj.rel+')');
	var id=rel.id;
	var o=$(obj).children('img').offset();

	if($('#k_color_Fly').length==0){
		var colors=new Array('0','3','6','9','C','F');
		var col='<font id="k_fontcolor"><i>KING</i><i style="background:#000;margin-left:10px;font-family:Verdana">CMS</i></font>';
		var color;
		$('body').append('<div id="k_color_Fly" class="none"></div>');
		for (i=0;i<=5;i++){
			for (j=0;j<=5;j++){
				for (k=0;k<=5;k++){
					color=colors[j]+colors[j]+colors[i]+colors[i]+colors[k]+colors[k];
					col+='<a color="#'+color+'" style="background-color:#'+color+'">&nbsp;</a>';
				}
			}
		}
		$('#k_color_Fly').html(col);
		$('#k_color_Fly a').attr('href','javascript:;');
	}
	//颜色
	$('#k_color_Fly').css('top',o.top+17).css('left',o.left+17).fadeIn(100);
	$('#k_color_Fly a').click(function(){
		$('#'+id).val($(this).attr('color')).css('background',$(this).attr('color'));
		$('#k_color_Fly').fadeOut(300);
	}).mousemove(function(){
		$('#k_fontcolor').css('color',$(this).attr('color'));
	});
}


$.kc_calendar=function(obj){
	
//"<a href=\"javascript:;\" class=\"k_calendar\" rel=\"{ID:'{$rs['kfield']}'}\"><img src=\"../system/images/white.gif\" class=\"os k9\"/></a>";
	var rel=eval('('+obj.rel+')');
	var id=rel.id;
	var o=$(obj).children('img').offset();

	if($('#k_calendar_Fly').length==0){
		$('body').append('<div id="k_calendar_Fly" class="none"></div>');
	}

	if($('#k_calendar_Fly').css('display')=='block'){
		$('#k_calendar_Fly').fadeOut(300);
		return;
	}

	var reg=/^((((1[6-9]|[2-9]\d)\d{2})-(0?[13578]|1[02])-(0?[1-9]|[12]\d|3[01]))|(((1[6-9]|[2-9]\d)\d{2})-(0?[13456789]|1[012])-(0?[1-9]|[12]\d|30))|(((1[6-9]|[2-9]\d)\d{2})-0?2-(0?[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))-0?2-29))$/;

	var d=$('#'+id).val();
	if(!reg.test(d)){
		var dd=new Date();
		d=dd.getFullYear()+'-'+(dd.getMonth()+1)+'-'+dd.getDate();
	}
/*
	alert(d);
*/
	//var s=
	$.kc_calendar_show(d,id);

	$('#k_calendar_Fly').css('top',o.top+17).css('left',o.left+17);//定位


	
}

$.kc_calendar_show=function(d,id){

	var dd=d.split('-');

	var fday=new Date(dd[0],dd[1]-1,1).getDay();//每月第一天的前一天星期数
	var dayNum=new Date(dd[0],dd[1],0).getDate();//每月的天数
	var n=0;
	var nday;

	var s='<table class="k_side" cellspacing="0">';
	s+='<tr>';
	s+='<td><a class="block" rel="{month:-12}">&lt;&lt;</a></td>';
	s+='<td><a class="block" rel="{month:-1}">&lt;</a></td>';
	s+='<td colspan="3" id="k_calendar_title"><a rel="{obj:\'year\'}">'+dd[0]+'</a>-<a rel="{obj:\'month\'}">'+dd[1]+'</a></td>';//这边还可以继续扩展展开显示,先不写了
	s+='<td><a class="block" rel="{month:1}">&gt;</a></td>';
	s+='<td><a class="block" rel="{month:12}">&gt;&gt;</a></td>';
	s+='</tr>';

	s+='<tr>'
	for(var i=0;i<7;i++){//填充日历头
		s+='<td class="c'+i+' b">'+$.kc_lang('week'+i)+'</td>';
	}
	s+='</tr>';
	for(var i=0; i < 6; i++){//填充日期
		s+='<tr>';
		for(var j=0;j<7;j++){

			nday=n-fday+1;

			if(n<fday||nday>dayNum){
				s+='<td class="c'+j+'"></td>';
			}else if(nday==dd[2]){
				s+='<td class="c'+j+'"><a class="k_today">'+nday+'</a></td>';
			}else{
				s+='<td class="c'+j+'"><a class="block">'+nday+'</a></td>';
			}

			n++;

		}
		s+='</tr>';
	}
	s+='</table>';

	$('#k_calendar_Fly').html(s).fadeIn(100);
	$('#k_calendar_Fly td:parent').hover(function(){$(this).addClass('hover')},function(){$(this).removeClass('hover')});

	$('#k_calendar_Fly a').attr('href','javascript:;').click(function(){
		if(this.rel==''){//判断是否有rel值，若没有rel值，则直接插入对应的值
			$('#'+id).val(dd[0]+'-'+dd[1]+'-'+$(this).text());
			$('#k_calendar_Fly').fadeOut(300);
		}else{//若有rel值，则读取并进行判断
			var r=eval('('+this.rel+')');
			if(r.month!=undefined){//<<>>来调整上下月份及年份
				var ndd=new Date(dd[0],dd[1]-1+r.month,1);
				var nd=ndd.getFullYear()+'-'+(ndd.getMonth()+1)+'-'+dd[2];
				$.kc_calendar_show(nd,id);
			}else{//这边还可以继续扩展展开显示,先不写了

			}

		}

	});

}
	
$.kc_float=function(obj,e){//鼠标点击动作


	var o=$(obj).offset();
	var x=e.clientX-o.left;
	var y=e.clientY-o.top;
	var is=true;
	var bs=10;//块大小BlockSize
	var left,top;
	var id=$(obj).attr('id');
	var kid=id.substr(8,id.length-8);

	if($('#k_float_shadow').length==0){
		$('body').append('<div id="k_float_shadow" class="none"></div>');
	}
	$('#k_float_shadow').fadeIn(100);

	$(obj).mousemove(function(e){

		if(is==false) return;

		$(obj).css('zIndex',1000).css('top',e.clientY-y).css('left',e.clientX-x).fadeTo(100,0.5);

		top =Math.round((e.clientY-y)/bs)*bs;
		left=Math.round((e.clientX-x)/bs)*bs;

		$('#k_float_shadow').css('top',top).css('left',left)//影子坐标

	}).mouseout(function(){

		if(is==false) return;
		is=false;
		$(obj).css('zIndex',200).css('top',top).css('left',left).fadeTo(0,1);
		if(o.left!=left||o.top!=top){
      $.kc_ajax({URL:'../system/manage.php',CMD:'lnkmove',id:kid,left:left,top:top,IS:1});
		}

	}).mouseup(function(){

		if(is==false) return;
		is=false;
		if((o.left!=left||o.top!=top)&&(top!=undefined||left!=undefined)){
			$(obj).css('zIndex',200).css('top',top).css('left',left).fadeTo(0,1);
      $.kc_ajax({URL:'../system/manage.php',CMD:'lnkmove',id:kid,left:left,top:top,IS:1});
		}

	}).blur(function(){
		is=false;
		$(obj).css('zIndex',200).fadeTo(0,1);
	});



}
/*number_format*/
$.number_format=function(number, decimals, dec_point, thousands_sep){
	

    // http://kevin.vanzonneveld.net
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // %        note 1: For 1000.55 result with precision 1 in FF/Opera is 1,000.5, but in IE is 1,000.6
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
 
    var n = number, prec = decimals;
    n = !isFinite(+n) ? 0 : +n;
    prec = !isFinite(+prec) ? 0 : Math.abs(prec);
    var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
    var dec = (typeof dec_point == "undefined") ? '.' : dec_point;
 
    var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;
 
    var abs = Math.abs(n).toFixed(prec);
    var _, i;
 
    if (abs >= 1000) {
        _ = abs.split(/\D/);
        i = _[0].length % 3 || 3;
 
        _[0] = s.slice(0,i + (n < 0)) +
              _[0].slice(i).replace(/(\d{3})/g, sep+'$1');
 
        s = _.join(dec);
    } else {
        s = s.replace('.', dec);
    }
 
    return s;


}

/* 读取Cookie */
$.COOKIE=function(name){//读取Cookie,仿php的读取Cookie
	var sta=document.cookie.indexOf(name+"=");
	var len=sta+name.length+1;
	if((!sta)&&(name!=document.cookie.substring(0,name.length))){
		return null;
	}
	if(sta==-1) return null;
	var end=document.cookie.indexOf(';',len);
	if(end==-1) end=document.cookie.length;
	return unescape(document.cookie.substring(len,end));
}
/* 设置Cookie */
$.setCookie=function(name,value,expires,path,domain,secure){
	var today=new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
	expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+'='+escape( value ) + ( ( expires ) ? ';expires='+expires_date.toGMTString() : '' ) + 
	( ( path ) ? ';path=' + path : '' ) + ( ( domain ) ? ';domain=' + domain : '' ) + ( ( secure ) ? ';secure' : '' ); 
}


})(jQuery);



var root=$.kc_root();
var cook=$.COOKIE('language');if(cook==null) cook='zh-cn';
//加入语言包
document.write('<script type="text/javascript" src="'+root+'system/js/lang.'+cook+'.js'+'"></script>');

function kc_style(){//设置页面常用的样式
	var I1="abcdefghijklmnopqr";//stuvwxyz
	var s='<style type="text/css">';
	for(var i=0;i<I1.length;i++){
		for(var j=1;j<=9;j++){
			s+='img.'+I1.charAt(i)+j+'{background-position:-'+(16*i+16)+'px -'+16*(j)+'px;}\n';//icon图片的样式
		}
		s+='.w'+((i+1)*50)+'{width:'+((i+1)*50)+'px}\n';//.w50-w800的样式
	}
	s+='.l{text-align:left}\n';//居左
	s+='.r{text-align:right}\n';//居右
	s+='.c{text-align:center}\n';//居中
	s+='.fl{float:left;}\n';//偏左
	s+='.fr{float:right;}\n';//偏右
	s+='.block{display:block;}\n';//打块
	s+='.none{display:none;}\n';//空
	s+='.w0 {width:100%}\n';//全宽
	s+='input.transparent{-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=40)";filter:alpha(opacity=40);/*IE*/opacity:0.4;}';//透明的
	s+='img.white{width:16px;height:16px;vertical-align:middle;margin:0px;margin-right:3px;padding:2px;}';
	

	for(i=1;i<20;i++){
		s+='.w'+i+'{width:'+(i*5)+'%}\n';
	}
	s+='</style>';

	return s;
}

document.write(kc_style());