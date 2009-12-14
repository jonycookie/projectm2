//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3.1
//******************************************************//
// forum javascript
// $Id: forum.js 529 2006-10-02 22:47:10Z mutantmonkey0 $
//******************************************************/

var topic_menu_open		= '';

function mod_dropdown(tid)
{
	obj					= _getbyid('mod_dropdown_'+tid);

	if(topic_menu_open!='' && topic_menu_open!=tid)
	{
		_getbyid('mod_dropdown_'+topic_menu_open).style.display='none';
	}

	if(obj.style.display== 'none')
	{
		obj.style.display= 'block';
		topic_menu_open	= tid;
	}
	else {
		obj.style.display= 'none';
		topic_menu_open	= '';
	}
}

// -------------------------------------------------------- //

function mod_edit_ttitle(tid)
{
	if(!http || !document.getElementsByTagName)
	{
		return true;
	}

	obj					= _getbyid('topic-title-'+tid);
	node				= obj.getElementsByTagName('a');
	node				= node[0];
	parent_node			= node.parentNode;
	beforephoto			= parent_node.innerHTML;
	
	curr_title			= new String(node.innerHTML);
	curr_title			= curr_title.replace(/'/,"&#39;");
	
	afterphoto			= "<form name='edit_title_form' onsubmit='return mod_edit_ttitle_do(this)'><input type='hidden' name='tid' value='"+tid+"' /><input type='text' name='newtitle' value='"+curr_title+"' class='form_textbox' size='40' onblur='_mod_edit_ttitle_do(document.edit_title_form)' /></form>";
	parent_node.innerHTML= afterphoto;
	document.edit_title_form.newtitle.focus();
	
	return false;
}

function mod_edit_ttitle_do(frm)
{
	parent_node.innerHTML= beforephoto;
	
	// htmlspecialchars()!
	newtitle			= new String(frm.newtitle.value);
	newtitle			= newtitle.replace(/&/g,'&amp;');
	newtitle			= newtitle.replace(/</g,'&lt;');
	newtitle			= newtitle.replace(/>/g,'&gt;');
	
	newobj				= _getbyid('topic-title-'+frm.tid.value).getElementsByTagName('a');
	newobj				= newobj[0];
	newobj.innerHTML	= newtitle;
	
	result				= new Ajax.Request(icebb_base_url+"act=moderate&func=topic_edit_title&topicid="+frm.tid.value+"&newtitle="+escape(frm.newtitle.value));

	return true;
}