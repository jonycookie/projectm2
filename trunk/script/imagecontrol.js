var imgMaxWidth=450; //控制内容中图片大小
var content = document.getElementById("content");
ImgLoad(content);
function ImgLoad(obj)
{
	for(var i=0;i<obj.getElementsByTagName("img").length;i++){
		var o=obj.getElementsByTagName("img")[i];
		if (o.width>imgMaxWidth){
			if (o.style.width){
				o.style.width="";
			}
			o.width=imgMaxWidth;
			o.removeAttribute("height");
			o.setAttribute("title","ctrl+鼠标滚轮缩放");
			o.style.cursor="hand";
			o.style.display="block";
			o.vspace=5;
			o.resized=1;
			o.onclick=ImgClick;
			o.onmousewheel=bbimg;
		}
	}
}

function ImgClick()
{
	if (this.parentElement){
		if (this.parentElement.tagName!="A"){
			window.open(this.src);
		}
	}else{
		window.open(this.src);
	}
}

function bbimg()
{
	if (event.ctrlKey){
		var zoom=parseInt(this.style.zoom, 10)||100;
		zoom+=event.wheelDelta/12;
		if (zoom>0) this.style.zoom=zoom+'%';
		return false;
	}else{
		return true;
	}
}