﻿/*
**************图片预加载插件******************
///作者：没剑(2008-06-23)
///http://regedit.cnblogs.com

///说明：在图片加载前显示一个加载标志，当图片下载完毕后显示图片出来
可对图片进行是否自动缩放功能
此插件使用时可让页面先加载，而图片后加载的方式，
解决了平时使用时要在图片显示出来后才能进行缩放时撑大布局的问题
///参数设置：
width       图片最大高
height      图片最大宽
loadpic     加载中的图片路径
*/
jQuery.fn.scaling=function(width,height,loadpic){
    loadpic=loadpic||"admin/images/ajax_loader.gif";
	return this.each(function(){
		var t=$(this);
		var src=$(this).attr("src")
		var img=new Image();
		img.src=src;
		//自动缩放图片
		var autoScaling=function(){
			if(img.width>0 && img.height>0){ 
		        if(img.width/img.height>=width/height){ 
		            if(img.width>width){ 
		                t.width(width);
		                t.height((img.height*width)/img.width); 
		            }else{ 
		                t.width(img.width); 
		                t.height(img.height); 
		            } 
		        }else{ 
		            if(img.height>height){ 
		                t.height(height); 
		                t.width((img.width*height)/img.height); 
		            }else{ 
		                t.width(img.width); 
		                t.height(img.height); 
		            } 
		        } 
		    } 
		}
		//处理ff下会自动读取缓存图片
		if(img.complete){
			autoScaling();
		    return;
		}
//		$("#loadpic").remove();
//		$(this).attr("src","").hide().after("<img id=\"loadpic\" alt=\"加载中...\" title=\"图片加载中...\" src=\""+loadpic+"\" />");
		$(this).attr("src",loadpic);
		var loadimg=new Image();
		loadimg.src=loadpic;
		$(this).width(loadimg.width).height(loadimg.height);
		$(img).load(function(){
			autoScaling();
//			$("#loadpic").remove();
			t.attr("src",this.src).show();
		});
		
	});
}