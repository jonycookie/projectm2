var tab_open			= '1';

function tab_switch_to(tabid)
{
	_getbyid('tabdata-'+tab_open).style.display	= 'none';
	_getbyid('tab-'+tab_open).style.height		= '13px';
	//_getbyid('tab-'+tab_open).style.border		= '1px 1px 0px 1px';
	
	_getbyid('tabdata-'+tabid).style.display	= 'block';
	_getbyid('tab-'+tabid).style.height			= '14px';
	//_getbyid('tab-'+tabid).style.border			= '1px 1px 0px 1px';
	_getbyid('tab-'+tabid).blur();
	
	tab_open									= tabid;
	
	return false;
}

var margin_right_now							= 0;

function tab_bar_move(rol)
{
	if(rol										== 'r')
	{
		_getbyid('tabrow').style.paddingright	= margin_right_now+6;
	}
	else {
		if(margin_right_now					   >= 6)
		{
			_getbyid('tabrow').style.paddingright= margin_right_now-6;
		}
	}

	return false;
}