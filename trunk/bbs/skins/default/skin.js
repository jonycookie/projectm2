/********************************************
IceBB Default Skin Javascript
(c) 2006 XAOS Interactive
*********************************************/

////////////////////////////////////////////////////
// board timeline
////////////////////////////////////////////////////

var timeline_html;

function show_timeline()
{
	$('board_timeline_title').style.width='50%';
	$('btt_vis').style.display			= 'block';
	$('btt_hide').style.display			= 'none';
	$('board_timeline').innerHTML		= timeline_html;
	
	return true;
}

function hide_timeline()
{
	timeline_html								= $('board_timeline').innerHTML;

	$('board_timeline_title').style.width='1%';
	$('btt_vis').style.display			= 'none';
	$('btt_hide').style.display			= 'block';
	$('board_timeline').innerHTML		= '';
	
	return true;
}

////////////////////////////////////////////////////
// strip HTML
////////////////////////////////////////////////////

function striphtml(t)
{
	regex			= /<\S[^>]*>/g; 
	t				= t.replace(regex,''); 

	return t;
}