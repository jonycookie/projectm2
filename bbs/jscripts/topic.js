//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 0.9.3
//******************************************************//
// topic javascript
// $Id: topic.js 665 2006-12-22 17:03:32Z mutantmonkey0 $
//******************************************************//

var http				= new xmlhttp();

var post_prior			= [];
function quick_edit_start(pid)
{
	if(!http.req)
	{
		return false;
	}
	
	var post_id			= pid;
	post_prior[pid]		= $('ptext-'+pid).innerHTML;
	
	http.request_func	= function()
	{
		if(http.req.readyState!=4 || http.req.status!=200)
		{
			return;
		}
	
		_post			= $('ptext-'+post_id);
		
		// IE workaround
		if(is_ie)
		{
			var d		= document.createElement("div");
			d.innerHTML	= http.req.responseText;
			while(_post.firstChild)_post.removeChild(_post.firstChild);//standard way to achieve x.innerHTML=""
			_post.appendChild(d);
		}
		else {
			_post.innerHTML= http.req.responseText;
		}
	}
	http.open(icebb_base_url+'act=post&edit='+pid+'&get_raw_post=1');
	
	return true;
}

function quick_edit_cancel(pid)
{
	$('ptext-'+pid).innerHTML	= post_prior[pid];
}

function quick_edit_save(pid,security_key)
{
	if(!http.req)
	{
		return false;
	}
	
	var post_id			= pid;
	ptext				= $('postbox').value;
	
	fields				= [];
	fields[0]			= new Array('security_key',security_key);
	fields[1]			= new Array('post',escape(ptext));
	fields[2]			= new Array('ajax','1');
	fields[3]			= new Array('submit','1');
	
	http.request_func	= function()
	{
		if(http.req.readyState!=4 || http.req.status!=200)
		{
			return;
		}
	
		_post			= $('ptext-'+post_id);
	
		_post.innerHTML	= http.req.responseText;
	}
	http.submit(icebb_base_url+'act=post&edit='+pid,fields);
	
	return false;
}