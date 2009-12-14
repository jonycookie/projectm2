<?php
if(!class_exists('skin_global')) require('global.php');

class skin_post
{
	function skin_post()
	{
		global $icebb,$global;
	
		$global				= new skin_global;
	}

function post_preview($msg)
{
global $icebb,$global;

$this->code .= <<<EOF
<div class='borderwrap' style='margin-bottom:6px'>
	<h2>{$icebb->lang['post_preview']}</h2>
	{$msg}
</div>

EOF;

//return $code;
}

function post_box($title,$smilies,$extra_fields,$t=array(),$editor,$topic_review='',$messages=array())
{
global $icebb,$global;

$code	= $global->header();

$code .= $this->code;

$code .= <<<EOF
<form action='index.php' method='post' name='postFrm' enctype='multipart/form-data'>

<div class='borderwrap'>
	<h2><span style="float:right">
EOF;

if($title==$icebb->lang['add_reply'])
{
	$code .= sprintf($icebb->lang['replying_in'],$t['title']);
}
else if($title==$icebb->lang['edit_post'])
{
	$code .= sprintf($icebb->lang['editing_post_in'],$t['title']);
}
else {
	$code .= sprintf($icebb->lang['creating_topic_in'],$t['forumname']);
}

$code .= <<<EOF
</span>{$title}</h2>

	<input type='hidden' name='act' value='post' />
	{$extra_fields}
	<table width="100%" cellspacing="1" cellpadding="2">

EOF;

if($icebb->user['id']==0)
{
$code .= <<<EOF
<tr>
	<td colspan='4' class='row2'>
		<div class='highlight_error'>
{$icebb->lang['not_logged_in_warning']}
		</div>
	</td>
</tr>

EOF;
}

if(count($messages)>0)
{
	$code .= <<<EOF
<tr>
	<td colspan='4' class='row2'>
		<div class='highlight_error'>
			<span class='title'>{$icebb->lang['errors_title']}</span>
			<ul>
EOF;
	foreach($messages as $message)
	{
		$code .= <<<EOF
				<li>{$message}</li>

EOF;
	}
	$code .= <<<EOF
			</ul>
		</div>
	</td>
</tr>

EOF;
}

$code .= <<<EOF
		<!--TOPIC_TITLE-->
		<tr> 
			<td class="row3" colspan="4">
				<strong>{$icebb->lang['add_content']}</strong>
			</td>
		</tr>
		<tr> 
			<td class="row1" valign="top" width="20%" style='text-align:center'>
				<strong>{$icebb->lang['smilies']}</strong><br />
				<div style='width:60%;margin:0px auto'>
					{$smilies}<br />
				</div>
				<a href='{$icebb->base_url}act=post&amp;func=smilies' onclick="window.open(this.href,'smiliesBox','height=400,width=350,scrollbars=yes');return false">{$icebb->lang['more']}</a>
			</td>
			<td class="row2" valign="top" width="88%" colspan='3'>
				{$editor}
			</td>
		</tr>
		<tr>
			<td class="row3" colspan="4" valign="top" align="center">
				<input type='submit' name='submit' value='{$title}' class='form_button' />
				<input type='submit' name='preview' value='{$icebb->lang['preview_post']}' class='form_button' id='preview-button'				/>
			</td>
		</tr>
	</table>
	
	<!--WORD_VERIFICATION-->
	
	<h2>{$icebb->lang['post_opt']}</h2>
	<table width="100%" cellspacing="0" cellpadding="3">

EOF;

if($title==$icebb->lang['edit_post'])
{

$code .= <<<EOF
		<tr>
			<td class='row3' style='font-weight:bold' valign='top' colspan='2'>
				{$icebb->lang['edit_opt']}
			</td>
		</tr>
		<tr>
			<td class='row1' valign='top' colspan='2'>
				<label><input type='checkbox' name='hide_edit_line' value='1' checked="1" /> {$icebb->lang['hide_edit_line']}</label>
			</td>
		</tr>
EOF;

}

if($icebb->user['g_is_mod']=='1')
{

$code .= <<<EOF
		<tr>
			<td class='row3' style='font-weight:bold' valign='top' colspan='2'>
				{$icebb->lang['mod_options']}
			</td>
		</tr>
		<tr>
			<td class='row1' valign='top' colspan='2'>
				<label><input type='checkbox' name='lock_after_post' value='1' /> {$icebb->lang['lock_after']}</label><br />
				<label><input type='checkbox' name='pin_after_post' value='1' /> {$icebb->lang['pin_after']}</label>
			</td>
		</tr>
EOF;

}

$code .= <<<EOF
		<!--UPLOAD_FORM-->
		<!--POLL_LINK-->
		<!--POLL_FORM-->
	</table>
</div>
</form>

EOF;

if(!empty($topic_review))
{

$code .= <<<EOF
<br />
<div class='borderwrap'>
	<h2>{$icebb->lang['t_rev']}</h2>
	<div style='display:block;height:300px;overflow:auto;padding:4px'>
{$topic_review}
	</div>
</div>

EOF;

}

$code .= $global->footer();

return $code;
}

function basic_editor($formname,$name,$ptext)
{
global $icebb;

$code .= <<<EOF
<div class='textentry basic-editor'>
	<textarea id='postbox' name='{$name}' rows='10' cols='50' class='form_textarea'>{$ptext}</textarea>
</div>
<script type='text/javascript'>
ta_obj=document.{$formname}.{$name};
</script>
<script type="text/javascript" src="jscripts/editor.js"></script>

EOF;

return $code;
}

function richtext_editor($formname,$name,$ptext)
{
global $icebb;

$code .= <<<EOF
<div class='textentry extended-editor'>
	<div class='toolbar'>
	<div style='float:right;'>
			<a onclick="document.{$formname}.{$name}.rows=parseInt(document.{$formname}.{$name}.rows)+5;return false" href="#">+</a>
			<a onclick="document.{$formname}.{$name}.rows=parseInt(document.{$formname}.{$name}.rows)-5;return false" href="#">-</a>
		</div>
		
		<span class='editgroup' style='border-left:0px'>
			<select name='font_family' class='form_dropdown' onmousedown="if(tag_open['font']) { bbcode('font');this.selectedIndex=0; }" onchange="bbcode('font','',this.options[this.selectedIndex].value)">
				<option value=''>{$icebb->lang['font']}</option>
				<option value='arial' style='font-family:arial'>Arial</option>
				<option value='comic sans ms' style='font-family:comic sans ms'>Comic Sans MS</option>
				<option value='courier new' style='font-family:courier new'>Courier New</option>
				<option value='georgia' style="font-family:georgia">Georgia</option>
				<option value='times new roman' style="font-family:times new roman">Times New Roman</option>
				<option value='verdana' style='font-family:verdana'>Verdana</option>
			</select>
			<select name='font_size' class='form_dropdown' onmousedown="if(tag_open['size']) { bbcode('size');this.selectedIndex=0; }" onchange="bbcode('size','',this.options[this.selectedIndex].value)">
				<option value='0'>{$icebb->lang['size']}</option>
				<option value='1'>1</option>
				<option value='2'>2</option>
				<option value='3'>3</option>
				<option value='4'>4</option>
				<option value='5'>5</option>
				<option value='6'>6</option>
				<option value='7'>7</option>
			</select>
		</span>

		<span class='editgroup'>
			<a href='#' id='left-tag' onclick="return bbcode('left')"><img src='skins/<#SKIN#>/images/editor/left_just.gif' alt="{$icebb->lang['j_left']}" /></a>
			<a href='#' id='center-tag' onclick="return bbcode('center')"><img src='skins/<#SKIN#>/images/editor/center.gif' alt="{$icebb->lang['j_center']}" /></a>
			<a href='#' id='right-tag' onclick="return bbcode('right')"><img src='skins/<#SKIN#>/images/editor/right_just.gif' alt="{$icebb->lang['j_right']}" /></a>
		</span>
		
		<span class='editgroup'>
			<a href='#' id='b-tag' onclick="return bbcode('b')"><img src='skins/<#SKIN#>/images/editor/bold.gif' alt="{$icebb->lang['bold']}" /></a>
			<a href='#' id='i-tag' onclick="return bbcode('i')"><img src='skins/<#SKIN#>/images/editor/italic.gif' alt="{$icebb->lang['italic']}" /></a>
			<a href='#' id='u-tag' onclick="return bbcode('u')"><img src='skins/<#SKIN#>/images/editor/underline.gif' alt="{$icebb->lang['underline']}" /></a>
		</span>
		
		<span class='editgroup'>
			<a href='#' id='url-tag' onclick="s2=prompt('{$icebb->lang['link_url']}','');s1=prompt('{$icebb->lang['link_text']}','');return bbcode('url',s1,s2)" style='text-decoration:underline;color:#666699'><img src='skins/<#SKIN#>/images/editor/link.png' alt="{$icebb->lang['add_link']}" /></a>
			<a href='#' id='img-tag' onclick="return bbcode('img',prompt('',''))"><img src="skins/<#SKIN#>/images/editor/img.png" alt="{$icebb->lang['insert_img']}" /></a>
			<a href='#' onclick="return bbcode('quote')">QUOTE</a>
			<a href='#' onclick="return bbcode('code')">CODE</a>
			<!--
			<a href='#' onclick="return bbcode('php')">{$icebb->lang['code_php']}</a>
			<a href='#' onclick="return bbcode('code','','xml')">{$icebb->lang['code_xml']}</a>
			-->
		</span>
		<!--
		<span class='editgroup'>
			<a href='#' onclick='_pop_color()'>Color</a>
			<a href='#' onclick='_pop_bgcolor()'>BG</a>
		</span>
		-->
	</div>


	<div style="padding: 4px;">
	<textarea id='postbox' name='{$name}' rows='16' cols='50' class='form_textarea'>{$ptext}</textarea>
	</div>
</div>
<script type='text/javascript'>
ta_obj=document.{$formname}.{$name};
document.{$formname}.font_family.selectedIndex=0;
document.{$formname}.font_size.selectedIndex=0;
</script>
<script type="text/javascript" src="jscripts/editor.js"></script>

EOF;

return $code;
}

function wysiwyg_editor($formname,$name,$ptext)
{
global $icebb;

$code .= <<<EOF
<script language="javascript" type="text/javascript" src="jscripts/tinymce/tiny_mce.js"></script>
<script language="javascript" type="text/javascript">
// <![CDATA[
tinyMCE.init({
	theme									: "advanced",
	mode									: "exact",
	elements								: "{$name}",
	
	valid_elements							: "a[href],b,i,u,p[align],br,font[face|size|color],img[src]",
	invalid_elements						: "table,tbody,tr,td,h1,h2,h3,h4,h5,h6",
	
	theme_advanced_toolbar_location			: "top",
	theme_advanced_toolbar_align			: "left",
	
	//theme_advanced_statusbar_location		: "bottom",
	//theme_advanced_resizing					: true,
	
	theme_advanced_buttons1					: "fontselect,fontsizeselect,separator,bold,italic,underline,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,image,forecolor",
	theme_advanced_buttons2					: "",
	theme_advanced_buttons3					: "",
	
	debug									: false
});
	
function smiley(code,url)
{
	tinyMCE.execCommand('mceInsertContent',false,"<img src='"+url+"' />");
	return false;
}

addEvent(window,'load',function(){\$('preview-button').style.display='none';});
// ]]>
</script>

<input type='hidden' name='wysiwyg' value='1' />

<div class='textentry wysiwyg-editor'>
	<textarea id='postbox' name='{$name}' rows='16' cols='50' class='form_textarea' >{$ptext}</textarea>
</div>

EOF;

return $code;
}

function topic_title_fields($title='',$desc='',$tags='',$icons='')
{
global $icebb;

$code .= <<<EOF
		<tr> 		
			<td class='row1' width='20%'>
				<strong>{$icebb->lang['t_title']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='ptitle' value='{$title}' class='form_textbox' tabindex='1' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>{$icebb->lang['t_desc']}</strong>
			</td>
			<td class='row2'>
				<input type='text' name='pdesc' value='{$desc}' class='form_textbox' tabindex='2' />
				<!--input type='checkbox' name='eltagso' value='1'  onclick="_toggle_view('tagsentry')" /> <label><strong>Tags?</strong></label-->
			</td>
		<tr>
			<td class='row1' width='20%'>
				<strong>{$icebb->lang['t_tags']}</strong><br />
				<em>{$icebb->lang['sep_with_space']}</em>
			</td>
			<td class='row2'>
				<input type='text' name='tags' value='{$tags}' class='form_textbox' tabindex='3' />
			</td>
		</tr>
		<tr>
			<td class='row1'>
				<strong>Topic Icons</strong>
			</td>
			<td class='row2'>
				{$icons}
			</td>
		</tr>

EOF;

return $code;
}

function quick_edit_form($security_key,$pid,$ptext)
{
global $icebb;

$code .= <<<EOF
<form action='{$icebb->base_url}' method='post' name='quick_edit_form_{$pid}' onsubmit="return quick_edit_save('{$pid}')">
<input type='hidden' name='act' value='post' />
<input type='hidden' name='edit' value='{$pid}' />
<input type='hidden' name='security_key' value='{$security_key}' />
<div class='quick_edit_form'>
	<div class='textentry-small'>
		<textarea id='postbox' name='post' rows='5' cols='50' class='form_textarea' style='width:100%'>{$ptext}</textarea>
	</div>
	<div style='float:left;position:relative'>
		<a href='{$icebb->base_url}act=post&amp;edit={$pid}'>{$icebb->lang['go_adv']}</a>
	</div>
	<div style='text-align:right'>
		<input type='button' name='submit' value="{$icebb->lang['save']}" class='form_button default' onclick="return quick_edit_save('{$pid}','{$security_key}')" />
		<input type='button' name='cancel' value="{$icebb->lang['cancel']}" class='form_button' onclick="return quick_edit_cancel('{$pid}')" />
	</div>
</div>
</form>

EOF;

return $code;
}

function word_verification($captcha_code)
{
global $icebb;

$code .= <<<EOF
	<h2>{$icebb->lang['word_verification']}</h2>
	<div class='Subtitle'>{$icebb->lang['word_verification_desc']} (<a href='#' onclick="document.captcha_img.src='index.php?act=login&amp;func=captcha&amp;s={$captcha_code}&amp;redraw='+Math.random();return false" title="{$icebb->lang['word_verification_link']}">+</a>)</div>
	<table width='100%' cellpadding='2' cellspacing='1'>
		<tr>
			<td class='row2' style='text-align:center'>
				<img src='index.php?act=login&amp;func=captcha&amp;s={$captcha_code}' name='captcha_img' alt='' />
			</td>
		</tr>
		<tr>
			<td class='row1' style='text-align:center'>
				<input type='hidden' name='captcha_code' value='{$captcha_code}' /> 
				<input type='text' name='captcha_word' id='captchaword' value='' class='form_textbox' style='margin-top:2px' />
			</td>
		</tr>
	</table>

EOF;

return $code;
}

function post_attach_form()
{
global $icebb;

if(!empty($icebb->input['forum']))
{
	$appende			= "forum={$icebb->input['forum']}";
}
else if(!empty($icebb->input['topic']))
{
	$appende			= "topic={$icebb->input['topic']}";
}
else if(!empty($icebb->input['reply']))
{
	$appende			= "reply={$icebb->input['reply']}";
}
else {
	$appende			= "edit={$icebb->input['edit']}";
}

$code .= <<<EOF
<tr>
	<td class='row3' style='font-weight:bold' colspan='2'>
		{$icebb->lang['attach']}
	</td>
</tr>
<tr>
	<td colspan='2' style='padding:0px'>
		<iframe src='{$icebb->base_url}act=post&amp;func=upload_form&amp;{$appende}' id='upload_iframe' frameborder='0' style='border:0px;width:100%;height:150px;background:transparent;margin:0px' allowtransparency='true'>
		<table width='100%' cellpadding='3' cellspacing='1'>
			<tr>
				<td class='row2'>
					{$icebb->lang['current_up']}
				</td>
				<td width='40%' class='row2'>
					{$icebb->lang['up_attach']}
				</td>
			</tr>
			<tr>
				<td valign='top' class='row1' id='uploadblock'>
					<#MY_UPLOADS#>
				</td>
				<td valign='top' class='row1'>
					<label><strong>{$icebb->lang['from_file']}</strong> <input type='file' name='file' class='form_textbox' /></label><br />
					<label><strong>{$icebb->lang['from_url']}</strong> <input type='text' name='upload_url' value='http://' class='form_textbox' /></label><br />
					<input type='submit' name='upload' value="{$icebb->lang['upload']}" class='form_button' />
				</td>
			</tr>
		</table>
		</iframe>
	</td>
</tr>

EOF;

return $code;
}

function post_attach_form_iframe($current_uploads='',$extra_js='')
{
global $icebb;

if(!empty($icebb->input['forum']))
{
	$appende			= "<input type='hidden' name='forum' value='{$icebb->input['forum']}' />";
}
else if(!empty($icebb->input['topic']))
{
	$appende			= "<input type='hidden' name='topic' value='{$icebb->input['topic']}' />";
}
else if(!empty($icebb->input['edit']))
{
	$appende			= "<input type='hidden' name='edit' value='{$icebb->input['edit']}' />";
}
else {
	$appende			= "<input type='hidden' name='reply' value='{$icebb->input['reply']}' />";
}

$code .= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>upload form</title>
<link rel='stylesheet' type='text/css' href='skins/<#SKIN#>/css.css' />
<style type='text/css'>
body { margin:0px !important;text-align:left }
</style>
<script type='text/javascript' src='jscripts/prototype/prototype.js'></script>
<script type='text/javascript' src='jscripts/scriptaculous/scriptaculous.js'></script>
<script type='text/javascript' src='jscripts/global.js'></script>
<script type='text/javascript'>
icebb_base_url='{$icebb->base_url}';
icebb_sessid='{$icebb->input['s']}';

function smiley(att)
{
	return window.parent.smiley(att);
}

function form_submit()
{
	document.uploadFrm.upload.disabled= true;
	document.uploadFrm.upload.value= "{$icebb->lang['uploading']}";
	return true;
}

window.onload				= function()
{
	window.parent.document.getElementById('upload_iframe').style.height= document.body.offsetHeight+'px';
}
</script>
{$extra_js}
</head>
<body>
<table width='100%' cellpadding='3' cellspacing='1'>
	<tr>
		<td class='row2'>
			{$icebb->lang['current_up']}
		</td>
		<td width='40%' class='row2'>
			{$icebb->lang['up_attach']}
		</td>
	</tr>
	<tr>
		<td valign='top' class='row1' id='uploadblock'>
			{$current_uploads}
		</td>
		<td valign='top' class='row1'>
			<form action='{$icebb->base_url}' method='post' enctype='multipart/form-data' name='uploadFrm'><!-- onsubmit='return form_submit()'-->
			<input type='hidden' name='act' value='post' />
			<input type='hidden' name='func' value='upload_form' />
			{$appende}
			<label><strong>{$icebb->lang['from_file']}</strong> <input type='file' name='file' class='form_textbox' /></label><br />
			<label><strong>{$icebb->lang['from_url']}</strong> <input type='text' name='upload_url' value='http://' class='form_textbox' /></label><br />
			<input type='submit' name='upload' value="{$icebb->lang['upload']}" class='form_button' />
			</form>
		</td>
	</tr>
</table>
</body>
</html>
					
EOF;

return $code;
}

function post_attach_attachment($attachment)
{
global $icebb;

$code .= <<<EOF
<a href='javascript:smiley("[attachment={$attachment['uid']}]")'>{$attachment['uname']}</a><br />

EOF;

return $code;
}

function post_attach_link()
{
global $icebb;

$code .= <<<EOF


EOF;

return $code;
}

function post_attach_poll_form()
{
global $icebb;

$code .= <<<EOF
		<div id='poll_form' style='display:none'>
			<table width='100%' cellpadding='3' cellspacing='1'>
				<tr>
					<td style='width:12%' class='row1'>
						<strong>Question:</strong>
					</td>
					<td class='row1'>
						<input type='text' name='pollq' value='' class='form_input' /> 
					</td>
				</tr>
				<tr>
					<td style='width:12%' class='row1'>
						<strong>Type:</strong>
					</td>
					<td class='row1'>
						<input type='radio' name='polltype' value='1' class='form_input' id='ptype1' checked='checked' /><label for='ptype1'> Single</label>
						<input type='radio' name='polltype' value='2' class='form_input' id='ptype2' /><label for='ptype2'> Multi</label>
					</td>
				</tr>
				
EOF;

for($i=1;$i<=10;$i++)
{
$ch			= sprintf($icebb->lang['p_choice'],$i);

$code .= <<<EOF
				<tr>
					<td style='width:12%' class='row1'>
						<strong>{$ch}</strong>
					</td>
					<td class='row1'>
						<input type='text' name='pollc[{$i}]' value='' class='form_input' /> 
					</td>
				</tr>

EOF;
}

$code .= <<<EOF
			</table>
		</div>
	</td>
</tr>

EOF;

return $code;
}

function post_attach_poll_link()
{
global $icebb;

$code .= <<<EOF
<tr>
	<td colspan='2' class='row3' style='font-weight:bold'>
		<label><input type='checkbox' value='1' onclick="if(this.checked) { _getbyid('poll_form').style.display=''; } else { _getbyid('poll_form').style.display='none'; }" />{$icebb->lang['p_attach']}</label>
	</td>
</tr>
<tr>
	<td colspan='2' class='row2' style='padding:0px'>

EOF;

return $code;
}

// NOT CURRENTLY USED, however feel free to use them in your skin
function boardrules_line()
{
global $icebb;

$code .= <<<EOF
<tr><td colspan='2'><div class='border lightpadded' style='text-align:center;margin:2px 2px'>This post must follow the <a href='{$icebb->base_url}act=boardrules'>board rules</a>.<!--FORUM_RULES--></div></td></tr>

EOF;

return $code;
}

function forumrules_line($f)
{
global $icebb;

$code .= <<<EOF
 This forum also has some extra <a href='{$icebb->base_url}forum={$f['fid']}&amp;rules=1'>rules</a> you should follow.
EOF;

return $code;
}

}
?>
