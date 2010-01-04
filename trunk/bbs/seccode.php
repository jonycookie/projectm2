<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: seccode.php 9804 2007-08-15 05:56:19Z cnteacher $
*/

define('CURSCRIPT', 'seccode');
define('NOROBOT', TRUE);

require_once './include/common.inc.php';

$refererhost = parse_url($_SERVER['HTTP_REFERER']);
$refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

if($refererhost['host'] != $_SERVER['HTTP_HOST']) {
	exit('Access Denied');
}

$seccodedata['width'] = $seccodedata['width'] >= 100 && $seccodedata['width'] <= 200 ? $seccodedata['width'] : 150;
$seccodedata['height'] = $seccodedata['height'] >= 50 && $seccodedata['height'] <= 80 ? $seccodedata['height'] : 60;

if($update) {
	$seccode = random(6, 1) + $seccode{0} * 1000000;
	updatesession();
}

seccodeconvert($seccode);

if(!$nocacheheaders) {
	@dheader("Expires: -1");
	@dheader("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@dheader("Pragma: no-cache");
}

if($seccodedata['type'] < 2 && function_exists('imagecreate') && function_exists('imagecolorset') && function_exists('imagecopyresized') &&
	function_exists('imagecolorallocate') && function_exists('imagechar') && function_exists('imagecolorsforindex') &&
	function_exists('imageline') && function_exists('imagecreatefromstring') && (function_exists('imagegif') || function_exists('imagepng') || function_exists('imagejpeg'))) {

	$bgcontent = seccode_background();

	if($seccodedata['animator'] == 1 && function_exists('imagegif')) {

		include_once './include/gifmerge.class.php';
		$trueframe = mt_rand(1, 9);

		for($i = 0; $i <= 9; $i++) {
			$im = imagecreatefromstring($bgcontent);
			$x[$i] = $y[$i] = 0;
			$seccodedata['adulterate'] && seccode_adulterate();
			if($i == $trueframe) {
				$seccodedata['ttf'] && function_exists('imagettftext') || $seccodedata['type'] == 1 ? seccode_ttffont() : seccode_giffont();
				$d[$i] = mt_rand(250, 400);
			} else {
				seccode_adulteratefont();
				$d[$i] = mt_rand(5, 15);
			}
			ob_start();
			imagegif($im);
			imagedestroy($im);
			$frame[$i] = ob_get_contents();
			ob_end_clean();
		}
		$anim = new GifMerge($frame, 255, 255, 255, 0, $d, $x, $y, 'C_MEMORY');
		dheader('Content-type: image/gif');
		echo $anim->getAnimation();

	} else {

		$im = imagecreatefromstring($bgcontent);
		$seccodedata['adulterate'] && seccode_adulterate();
		$seccodedata['ttf'] && function_exists('imagettftext') || $seccodedata['type'] == 1 ? seccode_ttffont() : seccode_giffont();

		if(function_exists('imagepng')) {
			dheader('Content-type: image/png');
			imagepng($im);
		} else {
			dheader('Content-type: image/jpeg');
			imagejpeg($im, '', 100);
		}
		imagedestroy($im);

	}

} elseif($seccodedata['type'] == 2 && extension_loaded('ming')) {

	$spacing = 5;
	$codewidth = ($seccodedata['width'] - $spacing * 5) / 4;
	$strforswdaction = '';
	for($i = 0; $i <= 3; $i++) {
		$strforswdaction .= seccode_swfcode($codewidth, $spacing, $seccode[$i], $i+1);
	}

	ming_setScale(20.00000000);
	ming_useswfversion(6);
	$movie = new SWFMovie();
	$movie->setDimension($seccodedata['width'], $seccodedata['height']);
	$movie->setBackground(255, 255, 255);
	$movie->setRate(31);

	$fontcolor = '0x'.(sprintf('%02s', dechex (mt_rand(0, 255)))).(sprintf('%02s', dechex (mt_rand(0, 128)))).(sprintf('%02s', dechex (mt_rand(0, 255))));
	$strAction = "
	_root.createEmptyMovieClip ( 'triangle', 1 );
	with ( _root.triangle ) {
	lineStyle( 3, $fontcolor, 100 );
	$strforswdaction
	}
	";
	$movie->add(new SWFAction( str_replace("\r", "", $strAction) ));
	header('Content-type: application/x-shockwave-flash');
	$movie->output();

} else {

	$numbers = array
		(
		'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
		'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
		'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
		'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
		'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
		'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
		'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
		'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
		'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
		'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
		'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
		'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
		'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
		'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
		'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
		'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
		'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
		'2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
		'3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
		'4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
		'6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
		'7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
		'8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
		'9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
		);

	foreach($numbers as $i => $number) {
		for($j = 0; $j < 6; $j++) {
			$a1 = substr('012', mt_rand(0, 2), 1).substr('012345', mt_rand(0, 5), 1);
			$a2 = substr('012345', mt_rand(0, 5), 1).substr('0123', mt_rand(0, 3), 1);
			mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
			mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
		}
	}

	$bitmap = array();
	for($i = 0; $i < 20; $i++) {
		for($j = 0; $j < 4; $j++) {
			$n = substr($seccode, $j, 1);
			$bytes = $numbers[$n][$i];
			$a = mt_rand(0, 14);
			array_push($bitmap, $bytes);
		}
	}

	for($i = 0; $i < 8; $i++) {
		$a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
		array_unshift($bitmap, $a);
		array_push($bitmap, $a);
	}

	$image = pack('H*', '424d9e000000000000003e000000280000002000000018000000010001000000'.
			'0000600000000000000000000000000000000000000000000000FFFFFF00'.implode('', $bitmap));

	dheader('Content-Type: image/bmp');
	echo $image;

}

function seccode_background() {
	global $seccodedata, $c;
	$im = imagecreatetruecolor($seccodedata['width'], $seccodedata['height']);
	$backgroundcolor = imagecolorallocate($im, 255, 255, 255);
	$backgrounds = array();
	if($seccodedata['background'] && function_exists('imagecreatefromjpeg') && function_exists('imagecolorat') &&	function_exists('imagecopymerge') &&
		function_exists('imagesetpixel') && function_exists('imageSX') && function_exists('imageSY')) {
		if($handle = @opendir('images/seccode/background/')) {
			while($bgfile = @readdir($handle)) {
				if(preg_match('/\.jpg$/i', $bgfile)) {
					$backgrounds[] = 'images/seccode/background/'.$bgfile;
				}
			}
			@closedir($handle);
		}
		if($backgrounds) {
			$imwm = imagecreatefromjpeg($backgrounds[array_rand($backgrounds)]);
			$colorindex = imagecolorat($imwm, 0, 0);
			$c = imagecolorsforindex($imwm, $colorindex);
			$colorindex = imagecolorat($imwm, 1, 0);
			imagesetpixel($imwm, 0, 0, $colorindex);
			$c[0] = $c['red'];$c[1] = $c['green'];$c[2] = $c['blue'];
			imagecopymerge($im, $imwm, 0, 0, mt_rand(0, 200 - $seccodedata['width']), mt_rand(0, 80 - $seccodedata['height']), imageSX($imwm), imageSY($imwm), 100);
			imagedestroy($imwm);
		}
	}
	if(!$seccodedata['background'] || !$backgrounds) {
		for($i = 0;$i < 3;$i++) {
			$start[$i] = mt_rand(200, 255);$end[$i] = mt_rand(100, 150);$step[$i] = ($end[$i] - $start[$i]) / $seccodedata['width'];$c[$i] = $start[$i];
		}
		for($i = 0;$i < $seccodedata['width'];$i++) {
			$color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
			imageline($im, $i, 0, $i-$angle, $seccodedata['height'], $color);
			$c[0] += $step[0];$c[1] += $step[1];$c[2] += $step[2];
		}
		$c[0] -= 20;$c[1] -= 20;$c[2] -= 20;
	}
	ob_start();
	if(function_exists('imagepng')) {
		imagepng($im);
	} else {
		imagejpeg($im, '', 100);
	}
	imagedestroy($im);
	$bgcontent = ob_get_contents();
	ob_end_clean();
	return $bgcontent;
}

function seccode_giffont() {
	global $seccode, $seccodedata, $im, $c;
	$seccodedir = array();
	if(function_exists('imagecreatefromgif')) {
		$seccoderoot = 'images/seccode/gif/';
		$dirs = opendir($seccoderoot);
		while($dir = readdir($dirs)) {
			if($dir != '.' && $dir != '..' && file_exists($seccoderoot.$dir.'/9.gif')) {
				$seccodedir[] = $dir;
			}
		}
	}
	$widthtotal = 0;
	for($i = 0; $i <= 3; $i++) {
		$imcodefile = $seccodedir ? $seccoderoot.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
		if(!empty($imcodefile) && file_exists($imcodefile)) {
			$font[$i]['file'] = $imcodefile;
			$font[$i]['data'] = getimagesize($imcodefile);
			$font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
			$font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
			$font[$i]['width'] += mt_rand(0, $seccodedata['width'] / 5 - $font[$i]['width']);
			$widthtotal += $font[$i]['width'];
		} else {
			$font[$i]['file'] = '';
			$font[$i]['width'] = 8 + mt_rand(0, $seccodedata['width'] / 5 - 5);
			$widthtotal += $font[$i]['width'];
		}
	}
	$x = mt_rand(1, $seccodedata['width'] - $widthtotal);
	for($i = 0; $i <= 3; $i++) {
		$seccodedata['color'] && $c = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
		if($font[$i]['file']) {
			$imcode = imagecreatefromgif($font[$i]['file']);
			if($seccodedata['size']) {
				$font[$i]['width'] = mt_rand($font[$i]['width'] - $seccodedata['width'] / 20, $font[$i]['width'] + $seccodedata['width'] / 20);
				$font[$i]['height'] = mt_rand($font[$i]['height'] - $seccodedata['width'] / 20, $font[$i]['height'] + $seccodedata['width'] / 20);
			}
			$y = mt_rand(0, $seccodedata['height'] - $font[$i]['height']);
			if($seccodedata['shadow']) {
				$imcodeshadow = $imcode;
				imagecolorset($imcodeshadow, 0 , 255 - $c[0], 255 - $c[1], 255 - $c[2]);
				imagecopyresized($im, $imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
			}
			imagecolorset($imcode, 0 , $c[0], $c[1], $c[2]);
			imagecopyresized($im, $imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
		} else {
			$y = mt_rand(0, $seccodedata['height'] - 20);
			if($seccodedata['shadow']) {
				$text_shadowcolor = imagecolorallocate($im, 255 - $c[0], 255 - $c[1], 255 - $c[2]);
				imagechar($im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
			}
			$text_color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
			imagechar($im, 5, $x, $y, $seccode[$i], $text_color);
		}
		$x += $font[$i]['width'];
	}
}

function seccode_ttffont() {
	global $seccode, $seccodedata, $im, $c, $charset;
	$seccoderoot = $seccodedata['type'] ? 'images/fonts/ch/' : 'images/fonts/en/';
	$dirs = opendir($seccoderoot);
	$seccodettf = array();
	while($entry = readdir($dirs)) {
		if($entry != '.' && $entry != '..' && strtolower(fileext($entry)) == 'ttf') {
			$seccodettf[] = $entry;
		}
	}
	$seccodelength = 4;
	if($seccodedata['type'] && !empty($seccodettf)) {
		if(strtoupper($charset) != 'UTF-8') {
			include DISCUZ_ROOT.'include/chinese.class.php';
			$cvt = new Chinese($charset, 'utf8');
			$seccode = $cvt->Convert($seccode);
		}
		$seccode = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
		$seccodelength = 2;
	}
	$widthtotal = 0;
	for($i = 0; $i < $seccodelength; $i++) {
		$font[$i]['font'] = $seccoderoot.$seccodettf[array_rand($seccodettf)];
		$font[$i]['angle'] = $seccodedata['angle'] ? mt_rand(-30, 30) : 0;
		$font[$i]['size'] = $seccodedata['type'] ? $seccodedata['width'] / 7 : $seccodedata['width'] / 6;
		$seccodedata['size'] && $font[$i]['size'] = mt_rand($font[$i]['size'] - $seccodedata['width'] / 40, $font[$i]['size'] + $seccodedata['width'] / 20);
		$box = imagettfbbox($font[$i]['size'], 0, $font[$i]['font'], $seccode[$i]);
		$font[$i]['zheight'] = max($box[1], $box[3]) - min($box[5], $box[7]);
		$box = imagettfbbox($font[$i]['size'], $font[$i]['angle'], $font[$i]['font'], $seccode[$i]);
		$font[$i]['height'] = max($box[1], $box[3]) - min($box[5], $box[7]);
		$font[$i]['hd'] = $font[$i]['height'] - $font[$i]['zheight'];
		$font[$i]['width'] = (max($box[2], $box[4]) - min($box[0], $box[6])) + mt_rand(0, $seccodedata['width'] / 8);
		$font[$i]['width'] = $font[$i]['width'] > $seccodedata['width'] / $seccodelength ? $seccodedata['width'] / $seccodelength : $font[$i]['width'];
		$widthtotal += $font[$i]['width'];
	}
	$x = mt_rand($font[0]['angle'] > 0 ? cos(deg2rad(90 - $font[0]['angle'])) * $font[0]['zheight'] : 1, $seccodedata['width'] - $widthtotal);
	!$seccodedata['color'] && $text_color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
	for($i = 0; $i < $seccodelength; $i++) {
		if($seccodedata['color']) {
			$c = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			$seccodedata['shadow'] && $text_shadowcolor = imagecolorallocate($im, 255 - $c[0], 255 - $c[1], 255 - $c[2]);
			$text_color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
		} elseif($seccodedata['shadow']) {
			$text_shadowcolor = imagecolorallocate($im, 255 - $c[0], 255 - $c[1], 255 - $c[2]);
		}
		$y = $font[0]['angle'] > 0 ? mt_rand($font[$i]['height'], $seccodedata['height']) : mt_rand($font[$i]['height'] - $font[$i]['hd'], $seccodedata['height'] - $font[$i]['hd']);
		$seccodedata['shadow'] && imagettftext($im, $font[$i]['size'], $font[$i]['angle'], $x + 1, $y + 1, $text_shadowcolor, $font[$i]['font'], $seccode[$i]);
		imagettftext($im, $font[$i]['size'], $font[$i]['angle'], $x, $y, $text_color, $font[$i]['font'], $seccode[$i]);
		$x += $font[$i]['width'];
	}
}

function seccode_adulterate() {
	global $seccodedata, $im, $c;
	$linenums = $seccodedata['height'] / 10;
	for($i=0; $i <= $linenums; $i++) {
		$color = $seccodedata['color'] ? imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)) : imagecolorallocate($im, $c[0], $c[1], $c[2]);
		$x = mt_rand(0, $seccodedata['width']);
		$y = mt_rand(0, $seccodedata['height']);
		if(mt_rand(0, 1)) {
			imagearc($im, $x, $y, mt_rand(0, $seccodedata['width']), mt_rand(0, $seccodedata['height']), mt_rand(0, 360), mt_rand(0, 360), $color);
		} else {
			imageline($im, $x, $y, $linex + mt_rand(0, $linemaxlong), $liney + mt_rand(0, mt_rand($seccodedata['height'], $seccodedata['width'])), $color);
		}
	}
}

function seccode_adulteratefont() {
	global $seccodedata, $im, $c;
	$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
	$x = $seccodedata['width'] / 4;
	$y = $seccodedata['height'] / 10;
	$text_color = imagecolorallocate($im, $c[0], $c[1], $c[2]);
	for($i = 0; $i <= 3; $i++) {
		$adulteratecode = $seccodeunits[mt_rand(0, 23)];
		imagechar($im, 5, $x * $i + mt_rand(0, $x - 10), mt_rand($y, $seccodedata['height'] - 10 - $y), $adulteratecode, $text_color);
	}
}

function seccode_swfcode($width, $d, $code, $order) {
	global $seccodedata;
	$str = '';
	$height = $seccodedata['height'] - $d * 2;
	$x_0 = ($order * ($width + $d) - $width);
	$x_1 = $x_0 + $width / 2;
	$x_2 = $x_0 + $width;
	$y_0 = $d;
	$y_2 = $y_0 + $height;
	$y_1 = $y_2 / 2;
	$y_0_5 = $y_2 / 4;
	$y_1_5 = $y_1 + $y_0_5;
	switch($code) {
		case 'B':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_1_5.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
		case 'C':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
		case 'E':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
		case 'F':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
		case 'G':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
		case 'H':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
		case 'J':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_1_5.');';break;
		case 'K':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
		case 'M':$str .= 'moveTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
		case 'P':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
		case 'Q':$str .= 'moveTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');';break;
		case 'R':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
		case 'T':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');';break;
		case 'V':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
		case 'W':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
		case 'X':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
		case 'Y':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_1.', '.$y_2.');';break;
		case '2':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
		case '3':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
		case '4':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
		case '6':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');';break;
		case '7':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
		case '8':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
		case '9':$str .= 'moveTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');';break;
	}
	return $str;
}
?>