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
	

	$(".k_table_list tr").hover(function(){
		$(this).addClass('hover');
	},function(){
		$(this).removeClass('hover');
	});
	$(".k_table_list tr:odd").addClass('odd')


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
	

	for(i=1;i<20;i++){
		s+='.w'+i+'{width:'+(i*5)+'%}\n';
	}
	s+='</style>';

	return s;
}

document.write(kc_style());