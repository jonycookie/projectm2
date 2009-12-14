/********************************************
IceBB XMLHttpRequest Library Javascript
(c) 2005 The IceBB Team
*********************************************/

function openURL(url)
{
	xhr				= new Ajax.Request(url);
	return true;
}

// --------------------------------------------------------------------- //

// Created by reading both http://www.webpasties.com/xmlHttpRequest/ and
// http://developer.apple.com/internet/webcontent/xmlhttpreq.html
function xmlhttp()
{
	// move this to the send function if you get complaints

	this.req						= null;
	this.supported					= 0;
	this.request_func				= function(){};

	if(!this.req)
	{
		if(window.XMLHttpRequest)
		{
			try
			{
				this.req			= new XMLHttpRequest();
			}
			catch(e)
			{
				this.req			= false;
			}
		}
		else if(window.ActiveXObject)
		{
			this.req				= new ActiveXObject("Microsoft.XMLHTTP");
		}
		else {
			this.req				= false;
		}
	}
	
	if(this.req)
	{
		this.supported				= 1;
	}
}

// functions for object
xmlhttp.prototype.open					= function(url)
{
	if(!this.req)
	{
		return;
	}

	this.req.open("GET",url,true);
	this.req.onreadystatechange			= this.handle_state_change;
	this.req.send(null);
}

xmlhttp.prototype.submit				= function(url,data)
{
	if(!this.req)
	{
		return;
	}
	
	var data_fields						= '';
	
	/*try
	{*/
		for(i=0;i<data.length;i++)
		{
			data_fields				   += data[i][0]+'='+data[i][1]+'&';
		}
	/*}
	catch(e)
	{
	}*/

	this.req.open("POST",url,true);
	this.req.onreadystatechange			= this.handle_state_change;
	this.req.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	this.req.send(data_fields);
}

xmlhttp.prototype.handle_state_change	= function()
{
	if(!http.req)
	{
		return;
	}

	if(http.req.readyState				== 4)
	{
		if(http.req.status				== 200)
		{
			http.request_func();
		}
		else {
			alert("An error was encounted retrieving the document: "+http.req.status);
		}
	}
}