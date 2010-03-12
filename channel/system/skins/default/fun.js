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

