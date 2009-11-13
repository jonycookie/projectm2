var dialog		= window.parent ;
var oEditor		= dialog.InnerDialogLoaded() ;
var FCK			= oEditor.FCK ;
var FCKLang		= oEditor.FCKLang ;
var FCKConfig	= oEditor.FCKConfig ;
var FCKTools	= oEditor.FCKTools ;

// Function called when a dialog tag is selected.
function OnDialogTabChange( tabCode )
{
	//ShowE('divInfo'		, ( tabCode == 'Info' ) ) ;
}

window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	dialog.SetAutoSize( true ) ;

	// Activate the "OK" button.
	dialog.SetOkButton( true ) ;

	SelectField( 'txtUrl' ) ;
}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		GetE('txtUrl').focus() ;

		alert( FCKLang.DlgFilesAlertUrl ) ;

		return false ;
	}
	oDiv = FCK.InsertElement( 'div' ) ;
	InsertFile( oDiv ) ;

	return true ;
}

function InsertFile( e, skipId )
{
	urlVal=GetE('txtUrl').value;
	FNname = urlVal.substr(urlVal.lastIndexOf('/')+1);
	FUrl = encodeURI(urlVal);
	e.className="attachment";
	e.innerHTML="<a href='"+ FUrl +"' target='_blank'><img src='images/attachment.gif' border='0' align='center' alt='"+ FNname +"'></a>&nbsp;<a href='"+ FUrl +"' target='_blank'><u>"+ FNname +"</u></a>";
}

var sActualBrowser ;

function SetUrl( url, width, height, alt )
{
		GetE('txtUrl').value = url ;
}
