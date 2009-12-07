/**
 * @package iCMS V3.1
 *
 * jquery.scaling.js v0.1.0
 * jQuery Image Scaling Plugin
 * @author coolmoo<idreamsoft@qq.com>
 * @param int width
 * @param int height
 * @param string loadpic
**/
jQuery.fn.scaling=function(width,height,loadpic){
    loadpic=loadpic||"admin/images/ajax_loader.gif";
	return this.each(function(){
		var obj	= $(this);
		var img	= new Image();
		img.src	= obj.attr("src");
//		//自动缩放图片
		var presize=function(){
			var nw = img.width, nh = img.height;
			if ((nw > width) && width > 0){
				nw = width;
				nh = (width/nw) * nh;
			}
			if ((nh > height) && height > 0){
				nh = height;
				nw = (height/nh) * nw;
			}
			obj.width(nw).height(nh);
		};
//处理FF下会自动读取缓存图片
		if(img.complete){
			presize();
		    return;
		}
		obj.attr("src",loadpic);
		var loadimg	= new Image();
		loadimg.src	= loadpic;
		obj.width(loadimg.width).height(loadimg.height);
		$(img).load(function(){
			presize();
			obj.attr("src",this.src).show();
		});
	});
}
jQuery.fn.snap=function(thisValue,defaultValue,width,height,l,t){
	thisValue 		= thisValue 	|| "src";
    defaultValue	= defaultValue	|| "";
    width			= width		|| "400";
    height			= height	|| "400";
    l				= l	|| 35;
    t				= t	|| 20;
    return this.each(function(){
    	$("#bigsnap").remove();
    	$("body").append('<div id="bigsnap"><div class="border"><div class="view"><img class="snap" id="prewimg" src="admin/images/ajax_loader.gif" /></div></div><div class="shadow"></div></div>');
    	var value		= $(this).attr(thisValue);
		var timeOutID 	= null;
		var hideSnap 	= function(){$("#bigsnap").hide();};
		$(this).mouseover(function(){
			window.clearTimeout(timeOutID);
			if(value==defaultValue){
			   return;
			};
			var offset 	= $(this).offset();
			var snapTop = offset.top+t;
			var snapLeft= offset.left+l;
			$("#bigsnap").css({"top" : snapTop, "left" : snapLeft}).show();
			$("#prewimg").attr("src",value).scaling(width,height);
		}).mouseout(function(){
			timeOutID = window.setTimeout(hideSnap,250);
			$("#bigsnap").mouseover(function(){
				window.clearTimeout(timeOutID);
				$(this).show();
			}).mouseout(function(){
				$(this).hide();
			});
		});
    });
};