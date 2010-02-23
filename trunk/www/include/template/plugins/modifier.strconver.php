<?php
/**
 * template_lite strconver modifier plugin
 *
 * Type:     modifier
 * Name:     strconver
 * Purpose:  ×ª»»×Ö·û
 */
function tpl_modifier_strconver($string){
	$string = str_replace("&amp;" , "&", $string);
	$string = str_replace("&quot;", "\"", $string);
	$string = str_replace("&#092;", "\\", $string);
	$string = str_replace("&#160;", "\r\n", $string);
	$string = str_replace("&#036;", "\$", $string);
	$string = str_replace("&#33;" , "!", $string);
	$string = str_replace("&#39;" , "'", $string);
	$string = str_replace("&lt;"  , "<", $string);
	$string = str_replace("&gt;"  , ">", $string);
	$string = str_replace("&#124;", '|', $string);
	$string = str_replace("&#58;" , ":", $string);
	$string = str_replace("&#91;" , "[", $string);
	$string = str_replace("&#93;" , "]", $string);
	$string = str_replace("&#064;", '@', $string);
	$string = str_replace("&#60;", '<', $string);
	$string = str_replace("&#62;", '>', $string);
	$string = str_replace("&nbsp;", ' ', $string);
	return $string;
}
?>