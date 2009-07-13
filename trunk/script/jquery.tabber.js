/***************************************/
// jQuery Tabber
// By Jordan Boesch
// www.boedesign.com
// Dec 25, 2007 (Merry Christmas!)
/***************************************/

(function($){

		$.tabber = function(params){
				
				// parameters
				var tabs = params.tabs;
				var selectedClass = params.selectedClass;
				var contents = params.contents;
				var defaultTab = params.defaultTab;
				var effect = params.effect;
				var effectSpeed = params.effectSpeed;
				
				$(contents).hide();
				
				// If we want to show the first block of content when the page loads
				if(!isNaN(defaultTab)){
					defaultTab--;
					$(contents+":eq("+defaultTab+")").show();
					$(tabs+":eq("+defaultTab+")").addClass(selectedClass);	
				}
				
				// each anchor
				$(tabs).each(function(){
					if (this.hash) {
						
						$(this).mouseover(function(){
							// once clicked, remove all classes
							$(tabs).each(function(){
								$(this).removeClass(selectedClass);
							})
							// hide all content
							$(contents).hide();
							
							// now lets show the desired information
							$(this).addClass(selectedClass);
							var contentObj = this.hash;
							
							if(effect != 'none'){
								
								switch(effect){
									
									case 'slide':
									$(contentObj).slideDown(effectSpeed);
									break;
									case 'fade':
									$(contentObj).fadeIn(effectSpeed);
									break;
									
								}
									
							}
							else {
								$(contentObj).show();
							}
							return false;
						})
						
					}
					
				})
			
			}
	
})(jQuery);	