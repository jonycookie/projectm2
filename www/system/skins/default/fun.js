jQuery(function($){
	
	var menu_lis = $('.k_menu>li'),
	menu_lnks = menu_lis.find('>a'),
	sub_menu_uls = menu_lis.find('>ul');
	
	var _last_t = null;
	function show_this_submenu(t){
		sub_menu_uls.hide();
		if(t && _last_t && (_last_t===t)){
			_last_t=null;
			menu_lnks.unbind('mouseover');
			return;
		}
		t && $('>ul', t.parentNode).show();
		_last_t = t;
	}
	
	menu_lnks.click(function(){
		_log('click', this);
		show_this_submenu(this);
		menu_lnks.bind('mouseover.navmenu',function(){
			show_this_submenu(this);
		});
	})
	.blur(function(){
		_log('blur', this);
		_last_t=null;
		menu_lnks.unbind('mouseover.navmenu');
		sub_menu_uls.fadeOut();
	//	menu_lnks.click(function(){_log('click',this)});
	});	
	
	$("h2 span a").wrapInner('<i class=yc></i>').prepend("<i class=y1/><i class=y2><b/></i>").append("<i class=y2><b/></i><i class=y1/>");

	$(".k_table_list tr").hover(function(){
		$(this).children('td').addClass('hover');
	},function(){
		$(this).children('td').removeClass('hover');
	});

	$(".k_table_form tbody:first tr td, .k_table_form tbody:first tr th").addClass('noborder');

	$('#bottom').prepend("<em/>");

	$('.k_menu li ul li.hr').prepend("<i/>");

	$('.k_table_form , .k_table_list').before("<i class=\"y1_table yc_top\"/><i class=y2_table><b/></i>").after("<i class=y2_table><b/></i><i class=\"y1_table yc_bottom\"/>");

	//IE6
	if($.browser.msie && $.browser.version<7){
		$("h2 span a").each(function(){
			$(this).width($(this).text().length * 12 + 42);
		});
	}

});

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