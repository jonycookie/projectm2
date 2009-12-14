var pixelX;
var pixelY;
function mouse_move(e)
{
	if(document.addEventListener)
	{
		pixelX		= e.clientX;
		pixelY		= e.clientY;
		hasPixels	= true;
	}
	else if(window.event)
	{
		hasPixels	= true;
	}
	
	oCanvas = document.getElementsByTagName(
	(document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY"
	)[0];
	
	pixelX = window.event ? window.event.x + oCanvas.scrollLeft : e.pageX;
	pixelY = window.event ? window.event.y + oCanvas.scrollTop : e.pageY;
	hasPixels=true;
}

document.onmousemove=mouse_move;

function show_help(help_item)
{
	_getbyid('help-'+help_item).style.display	= 'block';
	_getbyid('help-'+help_item).style.top		= (pixelY+6)+'px';
	_getbyid('help-'+help_item).style.left		= (pixelX+6)+'px';
}

function hide_help(help_item)
{
	_getbyid('help-'+help_item).style.display	= 'none';
}
