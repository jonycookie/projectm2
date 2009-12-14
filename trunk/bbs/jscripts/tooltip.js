//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// "fancy tooltips" javascript
// $Id: tooltip.js 529 2006-10-02 22:47:10Z mutantmonkey0 $
//******************************************************//

var pixelX			= 0;
var pixelY			= 0;

function mouseMove(e)
{
	if(document.addEventListener)
	{
		if(e.pageX && e.pageY)
		{
			pixelX	= e.pageX;
			pixelY	= e.pageY;
		}
		else {
			pixelX	= e.clientX;
			pixelY	= e.clientY;
		}
		
		hasPixels	= true;
	}
	else if(window.event)
	{
		pixelX = window.event.x+document.body.scrollLeft+65;
		pixelY = window.event.y+document.body.scrollTop+85;
	
		hasPixels	= true;
	}
	
	//oCanvas = document.getElementsByTagName(
	//(document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY"
	//)[0];
	
	//pixelX = window.event ? window.event.x + oCanvas.scrollLeft : e.pageX;
	//pixelY = window.event ? window.event.y + oCanvas.scrollTop : e.pageY;
	//hasPixels=true;
	
	pixelY=pixelY+20;
}

onmousemove			= mouseMove;
if(document.all)
{
	document.all.content.onmouseup= mouseMove;
	document.all.content.onmousemove= mouseMove;
}

function showTip(what)
{
	hideTip(what);
	
	if(document.all)
	{
		ttip			= "<div id='"+what+"_tip' class='tooltip' style='position:absolute;top:"+pixelY+"px;left:"+pixelX+"px'>";
	
		eval("dword		= "+what+".split('[nl]');");
		
		// add text
		for(i=0;i<=dword.length-1;i++)
		{
			ttipb		= document.createElement('div');
			ttip	   += "<div>"+dword[i]+"</div>";
		}

		ttip		   += "</div>";
		
		//document.body.innerHTML=document.body.innerHTML+ttip;
		//document.body.appendChild(ttip);
	}
	else {
		ttip			= document.createElement('div');
		ttip.setAttribute('id',what+'_tip');
		ttip.setAttribute('class','tooltip');
		ttip.setAttribute('style',"position:absolute;top:"+pixelY+"px;left:"+pixelX+"px");
	
		eval("dword		= "+what+".split('[nl]');");
		
		// add text
		for(i=0;i<=dword.length-1;i++)
		{
			ttipb		= document.createElement('div');
			ttip_text	= document.createTextNode(dword[i]);
			ttipb.appendChild(ttip_text);
			ttip.appendChild(ttipb);
		}
	
		//ttip.appendChild(ttip_text);
		
		//obj.appendChild(ttip);
		document.body.appendChild(ttip);
	}
	
	return false;
}

function showTip2(what,text)
{
	hideTip(what);
	
	if(document.all)
	{
		ttip			= "<div id='"+what+"_tip' class='tooltip' style='position:absolute;top:"+pixelY+"px;left:"+pixelX+"px'>";
		ttip		   += text;
		ttip		   += "</div>";
		
		//document.body.innerHTML=document.body.innerHTML+ttip;
		//document.body.appendChild(ttip);
	}
	else {
		ttip			= document.createElement('div');
		ttip.setAttribute('id',what+'_tip');
		ttip.setAttribute('class','tooltip');
		ttip.setAttribute('style',"position:absolute;top:"+pixelY+"px;left:"+pixelX+"px");
	
		ttipb		= document.createElement('div');
		ttip_text	= document.createTextNode(text);
		ttipb.appendChild(ttip_text);
		ttip.appendChild(ttipb);
		
		document.body.appendChild(ttip);
	}
	
	return false;
}

function hideTip(what)
{
	tip				= $(what+'_tip');

	if(tip)
	{
		tip.style.display='none';
	}
}
