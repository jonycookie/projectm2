/***************************************/
// jQuery Tabber
// By Jordan Boesch
// www.boedesign.com
// Dec 25, 2007 (Merry Christmas!)
/***************************************/

if(jQuery) (function($){
	
	$.extend($.fn, {
		tabber: function(o) {

			// Defaults
			if( !o ) var o = {};
			if( o.tabs == undefined ) o.tabs = '.tabs a';
			if( o.selectedClass == undefined ) o.selectedClass = 'active';
			if( o.contents == undefined ) o.contents = '.content';
			if( o.defaultTab == undefined ) o.defaultTab = ':first';
			if( o.effect == undefined ) o.effect = 'none';
			if( o.effectSpeed == undefined ) o.effectSpeed = 'fast';
			if( o.eventName == undefined ) o.eventName = 'click';

			var target = $(this);
			var tabs = target.find(o.tabs);
			var contents = target.find(o.contents);
			
				
			// If we want to show the first block of content when the page loads
			var tabber = $(tabs).filter(function(){
				return this.hash != '';
			});
			// each anchor
			tabber.each(function(){
				
					function tabsinit(){
						var content = target.find(this.hash);
						
						// once clicked, remove all classes
						tabber.each(function(){
							$(this).removeClass(o.selectedClass);
						})
						
						// hide all content
						contents.hide();
						
						// now lets show the desired information
						$(this).addClass(o.selectedClass);
						


						// ajax
						if($(this).attr('rel')) {                          //如果ajax请求url不为空
							content.html('Loading...');
							$.ajax({
								url: $(this).attr('rel'),
								cache: false,
								success: function(html) {
									content.html(html);
								},
								error:function() {
									content.html('Error');
								}
							});
						}

						
						// effect
						if(o.effect != 'none'){
							
							switch(o.effect){
								
								case 'slide':
								content.slideDown(o.effectSpeed);
								break;
								case 'fade':
								content.fadeIn(o.effectSpeed);
								break;
								
							}
								
						}
						else {
							content.show();
						}
						return false;
					}
					
					$(tabber)
					.bind(o.eventName == 'all' ? 'click mouseover' : o.eventName, tabsinit)
					.filter(o.defaultTab)
					.trigger(o.eventName == 'all' ? 'click' : o.eventName);
			});
			
		}
	});
	
})(jQuery);