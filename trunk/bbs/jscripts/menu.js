//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// menu javascript
// $Id: menu.js 529 2006-10-02 22:47:10Z mutantmonkey0 $
//******************************************************/

// some config options
var hide_on_option_click		= 1;
var menuopen					= '';

function open_menu(obj)
{
	mparent						= obj;
	rightalign					= 0;

	// change obj to the menu's ID, not to the menu's parent node
	obj							= obj.id;
	obj							= obj+'-menu';
	obj							= $(obj);

	// menu already open? what a waste then...
	if(menuopen					== obj.id)
	{
		close_menu(obj.id);
		return false;
	}

	if(menuopen				   != '')
	{
		close_menu(menuopen);
	}

	// oops, we need to make it absolute
	obj.style.position			= 'absolute';
	obj.style.zIndex			= 100;

	// calculate position
	pxTop						= get_offset('top',mparent)+mparent.offsetHeight;
	pxLeft						= get_offset('left',mparent);

	// we might need to make an adjustment to the position if the menu is off screen
	objwidth					= parseInt(obj.style.width) ? parseInt(obj.style.width) : obj.offsetWidth;
	if((pxLeft+objwidth)	   >= document.body.clientWidth)
	{
		pxLeft					= pxLeft+mparent.offsetWidth-objwidth;
		rightalign				= 1;
	}

	// IE has a sucky box model!
	if(is_ie)
	{
		pxLeft				   += rightalign ? 2 : -2;
	}

	// kill selects for IE here

	// change position
	obj.style.top				= pxTop+'px';
	obj.style.left				= pxLeft+'px';

	// hide the menu on click
	if(hide_on_option_click)
	{
		obj.onclick				= function(){obj.style.display='none';menuopen='';};
	}

	// show menu
	obj.style.display			= 'block';

	menuopen					= obj.id;

	return false;
}

function close_menu(obj)
{
	if(obj						== '')
	{
		return;
	}

	obj							= $(obj);

	// hide menu
	obj.style.display			= 'none';
	menuopen					= '';

	// revive selects for IE here
}

function close_all_menus()
{
	if(menuopen					== '')
	{
		return;
	}

	close_menu(menuopen);
}

function get_offset(toswitch,obj)
{
	switch(toswitch)
	{
		case 'top':
			result				= obj.offsetTop;
			while(obj			= obj.offsetParent)
			{
				result		   += obj.offsetTop;
			}
			break;
		case 'left':
			result				= obj.offsetLeft;
			while(obj			= obj.offsetParent)
			{
				result		   += obj.offsetLeft;
			}
			break;
	}

	return parseInt(result);
}

var curropacity					= 0;

// register a menu
function icebb_menu(obj)
{
}

menu							= new icebb_menu();
