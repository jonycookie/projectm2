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

// Get the selected flash embed (if available).
var oFakeMedia = dialog.Selection.GetSelectedElement() ;
var oEmbed ;

if ( oFakeMedia )
{
	if ( oFakeMedia.tagName == 'EMBED' && oFakeMedia.getAttribute('_fckmedia') )
		oEmbed = FCK.GetRealElement( oFakeMedia ) ;
	else
		oFakeMedia = null ;
}

window.onload = function()
{
	// Translate the dialog box texts.
	oEditor.FCKLanguageManager.TranslatePage(document) ;

	// Load the selected element information (if any).
	LoadSelection() ;

	dialog.SetAutoSize( true ) ;

	// Activate the "OK" button.
	dialog.SetOkButton( true ) ;

	SelectField( 'txtUrl' ) ;
}

function LoadSelection()
{
	if ( ! oEmbed ) return ;

	GetE('txtUrl').value    = GetAttribute( oEmbed, 'value', '' ) ;
	GetE('txtWidth').value  = GetAttribute( oEmbed, 'width', '' ) ;
	GetE('txtHeight').value = GetAttribute( oEmbed, 'height', '' ) ;
	// Get Advances Attributes
	GetE('chkAutoPlay').checked	= GetAttribute( oEmbed, 'play', 'true' ) == 'true' ;
	GetE('chkLoop').checked		= GetAttribute( oEmbed, 'loop', 'true' ) == 'true' ;

	UpdatePreview() ;
}

//#### The OK button was hit.
function Ok()
{
	if ( GetE('txtUrl').value.length == 0 )
	{
		GetE('txtUrl').focus() ;

		alert( oEditor.FCKLang.DlgAlertUrl ) ;

		return false ;
	}

	oEditor.FCKUndo.SaveUndoStep() ;
	if ( !oEmbed )
	{
		oEmbed		= FCK.EditorDocument.createElement( 'EMBED' ) ;
		oFakeMedia  = null ;
	}
	UpdateEmbed( oEmbed ) ;

	if ( !oFakeMedia ){
		oFakeMedia	= oEditor.FCKDocumentProcessor_CreateFakeImage( 'FCK__Media', oEmbed ) ;
		oFakeMedia.setAttribute( '_fckmedia', 'true', 0 ) ;
		oFakeMedia	= FCK.InsertElement( oFakeMedia ) ;
	}

	oEditor.FCKEmbedAndObjectProcessor.RefreshView( oFakeMedia, oEmbed ) ;

	return true ;
}

function UpdateEmbed( e )
{
	var type=$("input[name=t][checked]").val();
	if(type=="r"){
		SetAttribute( e, 'type'	, 'audio/x-pn-realaudio-plugin' ) ;
		SetAttribute( e, 'quality', 'hight' ) ;
		SetAttribute( e, 'wmode', 'transparent' ) ;
		SetAttribute( e, 'controls'	, 'IMAGEWINDOW,ControlPanel,StatusBar' ) ;
		SetAttribute( e, 'console', 'Clip1' ) ;
		SetAttribute( e, 'autostart',GetE('chkAutoPlay').checked ? 'true' : 'false' ) ;
	}else{
		SetAttribute( e, 'align', 'baseline' ) ;
		SetAttribute( e, 'border', '0' ) ;
		SetAttribute( e, 'type', 'application/x-mplayer2' ) ;
		SetAttribute( e, 'pluginspage', 'http://www.microsoft.com/isapi/redir.dll?prd=windows&amp;sbp=mediaplayer&amp;ar=media&amp;sba=plugin&amp;' ) ;
		SetAttribute( e, 'name', 'MediaPlayer' ) ;
		SetAttribute( e, 'showcontrols', '1' ) ;
		SetAttribute( e, 'showpositioncontrols', '0' ) ;
		SetAttribute( e, 'showaudiocontrols', '1' ) ;
		SetAttribute( e, 'showtracker', '1' ) ;
		SetAttribute( e, 'showdisplay', '0' ) ;
		SetAttribute( e, 'showstatusbar', '1' ) ;
		SetAttribute( e, 'showgotobar', '0' ) ;
		SetAttribute( e, 'showcaptioning', '0' ) ;
		SetAttribute( e, 'autostart', GetE('chkAutoPlay').checked ? '1' : '0') ;
		SetAttribute( e, 'autorewind', '0' ) ;
		SetAttribute( e, 'animationatstart', '0' ) ;
		SetAttribute( e, 'transparentatstart', '0' ) ;
		SetAttribute( e, 'allowscan', '1' ) ;
		SetAttribute( e, 'enablecontextmenu', '1' ) ;
		SetAttribute( e, 'clicktoplay', '0' ) ;
		SetAttribute( e, 'invokeurls', '1' ) ;
		SetAttribute( e, 'defaultframe', 'datawindow' ) ;
	}
	SetAttribute( e, 'src', GetE('txtUrl').value ) ;
	SetAttribute( e, "width" , GetE('txtWidth').value ) ;
	SetAttribute( e, "height", GetE('txtHeight').value ) ;
}

var ePreview ;

function SetPreviewElement( previewEl )
{
	ePreview = previewEl ;

	if ( GetE('txtUrl').value.length > 0 )
		UpdatePreview() ;
}

function UpdatePreview()
{
	if ( !ePreview )
		return ;

	while ( ePreview.firstChild )
		ePreview.removeChild( ePreview.firstChild ) ;

	if ( GetE('txtUrl').value.length == 0 )
		ePreview.innerHTML = '&nbsp;' ;
	else
	{
		var oDoc	= ePreview.ownerDocument || ePreview.document ;
		var e		= oDoc.createElement( 'EMBED' ) ;
		var type=GetE('type').value;
		if(type=="r"){
			SetAttribute( e, 'type'	, 'audio/x-pn-realaudio-plugin' ) ;
			SetAttribute( e, 'quality', 'hight' ) ;
			SetAttribute( e, 'wmode', 'transparent' ) ;
			SetAttribute( e, 'controls'	, 'IMAGEWINDOW,ControlPanel,StatusBar' ) ;
			SetAttribute( e, 'console', 'Clip1' ) ;
			SetAttribute( e, 'autostart',GetE('chkAutoPlay').checked ? 'true' : 'false' ) ;
		}else{
			SetAttribute( e, 'align', 'baseline' ) ;
			SetAttribute( e, 'border', '0' ) ;
			SetAttribute( e, 'type', 'application/x-mplayer2' ) ;
			SetAttribute( e, 'pluginspage', 'http://www.microsoft.com/isapi/redir.dll?prd=windows&amp;sbp=mediaplayer&amp;ar=media&amp;sba=plugin&amp;' ) ;
			SetAttribute( e, 'name', 'MediaPlayer' ) ;
			SetAttribute( e, 'showcontrols', '1' ) ;
			SetAttribute( e, 'showpositioncontrols', '0' ) ;
			SetAttribute( e, 'showaudiocontrols', '1' ) ;
			SetAttribute( e, 'showtracker', '1' ) ;
			SetAttribute( e, 'showdisplay', '0' ) ;
			SetAttribute( e, 'showstatusbar', '1' ) ;
			SetAttribute( e, 'showgotobar', '0' ) ;
			SetAttribute( e, 'showcaptioning', '0' ) ;
			SetAttribute( e, 'autostart', GetE('chkAutoPlay').checked ? '1' : '0') ;
			SetAttribute( e, 'autorewind', '0' ) ;
			SetAttribute( e, 'animationatstart', '0' ) ;
			SetAttribute( e, 'transparentatstart', '0' ) ;
			SetAttribute( e, 'allowscan', '1' ) ;
			SetAttribute( e, 'enablecontextmenu', '1' ) ;
			SetAttribute( e, 'clicktoplay', '0' ) ;
			SetAttribute( e, 'invokeurls', '1' ) ;
			SetAttribute( e, 'defaultframe', 'datawindow' ) ;
		}
		SetAttribute( e, 'src', GetE('txtUrl').value ) ;
		SetAttribute( e, 'width', '100%' ) ;
		SetAttribute( e, 'height', '100%' ) ;
		// Advances Attributes	
		SetAttribute( e, 'loop', GetE('chkLoop').checked ? 'true' : 'false' ) ;

		ePreview.appendChild( e ) ;
	}
}

function SetUrl( url, width, height )
{
	GetE('txtUrl').value = url ;

	if ( width )
		GetE('txtWidth').value = width ;

	if ( height )
		GetE('txtHeight').value = height ;

	UpdatePreview() ;
}
