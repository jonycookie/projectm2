<?php
/**
 * Copyright (c) 2003-07  PHPWind.net. All rights reserved.
 *
 * @filename: charset_mod.php
 * @author: Noizy (noizyfeng@gmail.com), QQ:7703883
 * @modify: Fri Mar 23 18:02:15 CST 2007
 */
!function_exists('readover') && exit('Forbidden');
function convert_charset($incharset,$outcharset,$string,$translit=''){
	if (strtolower($incharset) == strtolower($outcharset)) {
		return $string;
	} else {
		if (function_exists('iconv')) {
			$translit != '//IGNORE' && $translit = '//TRANSLIT';
			return iconvs($incharset,$outcharset,$string,$translit);
		} elseif (function_exists('recode_string')) {
			return recode_string($incharset.'..'.$outcharset,$string);
		} elseif (function_exists('libiconv')) {
			return libiconv($incharset,$outcharset,$string);
		} elseif (function_exists('mb_convert_encoding')) {
			return mb_convert_encoding($string,$outcharset,$incharset);
		} else {
			return charset_string($incharset,$outcharset,$string);
		}
    }
}
function iconvs($incharset,$outcharset,$string,$translit){
	if ((@stristr(PHP_OS,'AIX')) && (@strcasecmp(ICONV_IMPL,'unknown')==0) && (@strcasecmp(ICONV_VERSION,'unknown')==0)) {
		$iconvarray = array(
			/*'iso-8859-1' => 'ISO8859-1',
			'iso-8859-2' => 'ISO8859-2',
			'iso-8859-3' => 'ISO8859-3',
			'iso-8859-4' => 'ISO8859-4',
			'iso-8859-5' => 'ISO8859-5',
			'iso-8859-6' => 'ISO8859-6',
			'iso-8859-7' => 'ISO8859-7',
			'iso-8859-8' => 'ISO8859-8',
			'iso-8859-9' => 'ISO8859-9',*/
			'big5' => 'IBM-eucTW',
			/*'euc-jp' => 'IBM-eucJP',
			'koi8-r' => 'IBM-eucKR',
			'ks_c_5601-1987' => 'KSC5601.1987-0',
			'tis-620' => 'TIS-620',*/
			'utf-8' => 'UTF-8'
		);
		$in_charset = $out_charset = '';
		foreach ($iconvarray as $key => $value) {
			if ($key == strtolower($incharset)) {
				!$in_charset && $in_charset = $value;
			}
			if ($key == strtolower($outcharset)) {
				!$out_charset && $out_charset = $value;
			}
			if ($in_charset && $out_charset) {
				break;
			}
		}
		$in_charset  && $incharset  = $in_charset;
		$out_charset && $outcharset = $out_charset;
	}
	$outcharset .= $translit;
	return iconv($incharset,$outcharset,$string);
}
function charset_string($incharset,$outcharset,$string){
	$incharset  = strtolower($incharset);
	$outcharset = strtolower($outcharset);
	(ereg("^gb[k|0-9]{1,4}",$incharset))  && $incharset  = 'gb';
	(ereg("^gb[k|0-9]{1,4}",$outcharset)) && $outcharset = 'gb';
	$incharsets = ($incharset == 'utf-8' || $incharset == 'unicode') ? 'unicode' : $incharset;
	$num1 = $num2 = '';
	if ($outcharset == 'utf-8') {
		$num1 = 7; $num2 = 6;
		$outcharsets = 'unicode';
	} elseif ($outcharset == 'unicode') {
		$num1 = 9; $num2 = 4;
		$outcharsets = 'unicode';
	} else {
		$num1 = 7; $num2 = 6;
		$outcharsets = $outcharset;
	}
	list($farray,$func) = charsetfarray($incharsets,$outcharsets);
	if ($incharset==$outcharset || !in_array($incharsets,array('gb','big5','unicode')) || !in_array($outcharsets,array('gb','big5','unicode')) || !$farray) {
		return $string;
	}
	$outarray = array();
	if ($incharsets == 'gb' || $incharsets == 'big5') {
		if ($outcharsets == 'unicode') {
			foreach ($farray as $value) {
				$outarray[hexdec(substr($value,0,6))] = substr($value,$num1,$num2);
			}
		}
	} elseif ($incharsets == 'unicode') {
		if ($outcharsets == 'gb' || $outcharsets == 'big5') {
			foreach ($farray as $value) {
				$outarray[hexdec(substr($value,$num1,$num2))] = substr($value,0,6);
			}
		}
	}
	$fp = empty($outarray) ? $farray : $outarray;
	if ($func != 'gbig5_utf8') {
		return $func($string,$fp);
	} else {
		return $func($string,$fp,$incharset,$outcharset);
	}
}
function charsetfarray($incharsets,$outcharsets){
	if ($incharsets == 'gb' && $outcharsets == 'big5') {
		$file = 'gb-big5.table';
		$func = 'gb_big5';
	} elseif ($incharsets == 'big5' && $outcharsets == 'gb') {
		$file = 'big5-gb.table';
		$func = 'gb_big5';
	} elseif (($incharsets == 'gb' && $outcharsets == 'unicode') || ($incharsets == 'unicode' && $outcharsets == 'gb')) {
		$file = 'gb-unicode.table';
		$func = 'gbig5_utf8';
	} elseif (($incharsets == 'big5' && $outcharsets == 'unicode') || ($incharsets == 'unicode' && $outcharsets == 'big5')) {
		$file = 'big5-unicode.table';
		$func = 'gbig5_utf8';
	}
	if ($func != 'gbig5_utf8') {
		return array(fopen(R_P."mod/encode/{$file}","rb"),$func);
	} else {
		return array(@file(R_P."mod/encode/{$file}"),$func);
	}
}
function gb_big5($str,$fp){
	if (is_array($fp)) {
		return $str;
	}
	for ($i=0; $i<(strlen($str)-1); $i++) {
		$h = ord($str[$i]);
		if ($h>=160) {
			$l = ord($str[$i+1]);
			if ($h==161 && $l==64) {
				$gb = '  ';
			} else {
				fseek($fp,($h-160)*510+($l-1)*2);
				$gb = fread($fp,2);
			}
			$str[$i]   = $gb[0];
			$str[$i+1] = $gb[1];
			$i++;
		}
	}
	fclose($fp);
	return $str;
}
function gbig5_utf8($str,$fp,$incharset,$outcharset){
	if (!is_array($fp)) {
		return $str;
	}
	if ($incharset=='gb' || $incharset == 'big5') {
		$return = '';
		while ($str != '') {
			if (ord(substr($str,0,1))>127) {
				if ($incharset=='gb') {
					$utf8 = unicode_utf8(hexdec($fp[hexdec(bin2hex(substr($str,0,2)))-0x8080]));
				} elseif ($incharset == 'big5') {
					$utf8 = unicode_utf8(hexdec($fp[hexdec(bin2hex(substr($str,0,2)))]));
				}
				for ($i=0; $i<strlen($utf8); $i+=3) {
					$return .= chr(substr($utf8,$i,3));
				}
				$str = substr($str,2,strlen($str));
			} else {
				$return .= substr($str,0,1);
				$str	 = substr($str,1,strlen($str));
			}
		}
		unset($fp,$str);
		return $return;
	} elseif ($incharset == 'utf-8') {
		$return = ''; $i = 0;
		while ($i < strlen($str)) {
			$c = ord(substr($str,$i++,1));
			if (($c >> 4) < 8 && ($c >> 4) >= 0) {
				$return .= substr($str,$i-1,1);
			} elseif (($c >> 4) < 14 && ($c >> 4) > 11) {
				$char2 = ord(substr($str,$i++,1));
				$char3 = $fp[(($c & 0x1F) << 6) | ($char2 & 0x3F)];
				if ($outcharset=='gb') {
					$return .= hex2bin(dechex($char3 + 0x8080));
				} elseif ($outcharset=='big5') {
					$return .= hex2bin($char3);
				}
			} elseif (($c >> 4) == '14') {
				$char2 = ord(substr($str,$i++,1));
				$char3 = ord(substr($str,$i++,1));
				$char4 = $fp[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];
				if ($outcharset=='gb') {
					$return .= hex2bin(dechex($char4 + 0x8080));
				} elseif ($outcharset=='big5') {
					$return .= hex2bin($char4);
				}
			}
		}
		return $return;
	} else {
		return false;
	}
}
function unicode_utf8($str){
	$return = '';
	if ($str < 0x80) {
		$return .= $str;
	} elseif ($str < 0x800) {
		$return .= (0xC0 | $str >> 6);
		$return .= (0x80 | $str & 0x3F);
	} elseif ($str < 0x10000) {
		$return .= (0xE0 | $str >> 12);
		$return .= (0x80 | $str >> 6 & 0x3F);
		$return .= (0x80 | $str & 0x3F);
	} elseif ($str < 0x200000) {
		$return .= (0xF0 | $str >> 18);
		$return .= (0x80 | $str >> 12 & 0x3F);
		$return .= (0x80 | $str >> 6 & 0x3F);
		$return .= (0x80 | $str & 0x3F);
	}
	return $return;
}
function hex2bin($hexdata){
	$bindata = '';
	for ($i=0; $i<strlen($hexdata); $i+=2){
		$bindata .= chr(hexdec(substr($hexdata,$i,2)));
	}
	return $bindata;
}
?>