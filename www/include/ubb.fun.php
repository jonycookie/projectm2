<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function ubb($text){
	$text = preg_replace("/\s*\[quote\](.+?)\[\/quote\]\s*/ies", "quote('\\1')", $text);
	$text = preg_replace("/\s*\[reply\](.+?)\[\/reply\]\s*/ies", "reply('\\1')", $text);
	$text = str_replace(array(
		'[p]','[/p]','[span]','[/span]','[em]','[/em]',
		'[/color]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]',
		'[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
		'[list=A]', '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
	), array(
		'<p>','</p>', '<span>','</span>','<em>','</em>',
		'</font>', '</font>', '</font>', '</p>', '<strong>', '</strong>',
		'<i>','</i>', '<u>', '</u>', '<ul>', '<ul type="1">', '<ul type="a">',
		'<ul type="A">', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
	), preg_replace(array(
		"/\[color=([#\w]+?)\]/i",
		"/\[size=(\d+?)\]/i",
		"/\[size=(\d+(\.\d+)?(px|pt|in|cm|mm|pc|em|ex|%)+?)\]/i",
		"/\[font=([^\[\<]+?)\]/i",
		"/\[align=(left|center|right)\]/i",
		"/\[float=(left|right)\]/i"

	), array(
		"<font color=\"\\1\">",
		"<font size=\"\\1\">",
		"<font style=\"font-size: \\1\">",
		"<font face=\"\\1 \">",
		"<p align=\"\\1\">",
		"<span style=\"float: \\1;\">"
	), $text));
	return $text;
}
function quote($text){
	$text='<div class="quote">'.$text.'</div>';
	return $text;
}
function reply($text){
	$text='<span class="reply">'.$text.'</span>';
	return $text;
}
?>
