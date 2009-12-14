//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// extended editor javascript
// $Id: editor.js 529 2006-10-02 22:47:10Z mutantmonkey0 $
//******************************************************/

var tag_open				= new Array();
	tag_open['font_family']	= false;
	tag_open['font_size']	= false;
	tag_open['b']			= false;
	tag_open['u']			= false;
	tag_open['i']			= false;
	tag_open['color']		= false;
	tag_open['bgcolor']		= false;
	tag_open['url']			= false;
	tag_open['img']			= false;
	tag_open['code']		= false;
	tag_open['quote']		= false;

function bbcode(code,val,val2)
{
	if(typeof(val)					== 'undefined')
	{
		val							= '';
	}
	
	if(typeof(val2)					== 'undefined')
	{
		cafter						= '';
	}
	else {
		cafter						= '='+val2;
	}

	if(ta_obj.isTextEdit)						// exploder
	{
		ta_obj.focus();
		var selected				= document.selection;
		var range   				= selected.createRange();
		range.colapse;

		if((selected.type=='Text' || selected.type=='None') && range!=null)
		{
			middle					= range.text;

			if(val				   != '')
			{
				middle				= '['+code+cafter+']'+val+'[/'+code+']';
			}
			else if((sel_start-sel_end)!=0)
			{
				middle				= '['+code+cafter+']'+middle+'[/'+code+']';
			}
			else {
				if(tag_open[code]==true)
				{
					middle			= middle+'[/'+code+']';
					try{_getbyid(code+'-tag').className='';}catch(e){};
					tag_open[code]	= false;
				}
				else {
					middle			= '['+code+cafter+']'+middle;
					try{_getbyid(code+'-tag').className='on';}catch(e){};
					tag_open[code]	= true;
				}
			}

			range.text=middle;
		}
	}
	else if(is_gecko)						// mozilla or other Gecko-based
	{
		var sel_start				= ta_obj.selectionStart;
		var scrollTop				= ta_obj.scrollTop;
		var sel_end  				= ta_obj.selectionEnd;

		if(sel_end<=2)
		{
			sel_end					= ta_obj.textLength;
		}

		var start					= (ta_obj.value).substring(0,sel_start);
		var middle					= (ta_obj.value).substring(sel_start,sel_end);
		var end   					= (ta_obj.value).substring(sel_end,ta_obj.textLength);

		if(val				   != '')
		{
			middle				= '['+code+cafter+']'+val+'[/'+code+']';
		}
		else if((sel_start-sel_end)!=0)
		{
			middle				= '['+code+cafter+']'+middle+'[/'+code+']';
		}
		else {
			if(tag_open[code]==true)
			{
				middle			= middle+'[/'+code+']';
				try{_getbyid(code+'-tag').className='';}catch(e){};
				tag_open[code]	= false;
			}
			else {
				middle			= '['+code+cafter+']'+middle;
				try{_getbyid(code+'-tag').className='on';}catch(e){};
				tag_open[code]	= true;
			}
		}

		ta_obj.value				= start+middle+end;
		
		sel_thingy					= sel_start+middle.length;
		
		ta_obj.selectionStart		= sel_thingy;
		ta_obj.selectionStart		= sel_thingy;
		ta_obj.scrollTop			= scrollTop;
	}
	else {													// Opera?! Safari?! Ebul?!
		if(val					   != '')
		{
			code					= "["+code+cafter+"]"+val+"[/"+code+"]";
		}
		else if(tag_open[code]==true)
		{
			da_code	= '[/'+code+']';
			try{_getbyid(code+'-tag').className='';}catch(e){};
			tag_open[code]=false;
		}
		else {
			da_code	= '['+code+cafter+']';
			try{_getbyid(code+'-tag').className='on';}catch(e){};
			tag_open[code]=true;
		}
		
		ta_obj.value			   += da_code;
	}

	ta_obj.focus();
	return false;
}

function smiley(smiley)
{
	code		= " "+smiley+" ";

	if(ta_obj.isTextEdit)						// exploder
	{
		ta_obj.focus();
		var selected				= document.selection;
		var range   				= selected.createRange();
		range.colapse;

		if((selected.type=='Text' || selected.type=='None') && range!=null)
		{
			middle					= range.text;
			middle					= code+middle;
			range.text=middle;
		}
	}
	else if(is_gecko)						// mozilla or other Gecko-based
	{
		var sel_start				= ta_obj.selectionStart;
		var scrollTop				= ta_obj.scrollTop;
		var sel_end  				= ta_obj.selectionEnd;

		if(sel_end<=2)
		{
			sel_end					= ta_obj.textLength;
		}

		var start					= (ta_obj.value).substring(0,sel_start);
		var middle					= (ta_obj.value).substring(sel_start,sel_end);
		var end   					= (ta_obj.value).substring(sel_end,ta_obj.textLength);
		middle						= code+middle;

		ta_obj.value				= start+middle+end;
		
		sel_thingy					= sel_start+middle.length;
		
		ta_obj.selectionStart		= sel_thingy;
		ta_obj.selectionStart		= sel_thingy;
		ta_obj.scrollTop			= scrollTop;
	}
	else {													// Opera?! Safari?! Ebul?!
		ta_obj.value += code;
	}

	ta_obj.focus();
	//return false;
}

function insert(smiley)
{
	return smiley(smiley);
}
