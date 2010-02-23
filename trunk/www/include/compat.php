<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
if (!function_exists('floatval')) {
	function floatval($string) {
		return ((float) $string);
	}
}

if (!function_exists('is_a')) {
	function is_a($object, $class) {
		// by Aidan Lister <aidan@php.net>
		if (get_class($object) == strtolower($class)) {
			return true;
		} else {
			return is_subclass_of($object, $class);
		}
	}
}

if (!function_exists('ob_clean')) {
	function ob_clean() {
		// by Aidan Lister <aidan@php.net>
		if (@ob_end_clean()) {
			return ob_start();
		}
		return false;
	}
}

/* compatibility with PHP versions older than 4.3 */
if ( !function_exists('file_get_contents') ) {
	function file_get_contents( $file ) {
		$file = file($file);
		return !$file ? false : implode('', $file);
	}
}

/**
 * Replace array_change_key_case()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @link        http://php.net/function.array_change_key_case
 * @author      Stephan Schmidt <schst@php.net>
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 5187 $
 * @since       PHP 4.2.0
 * @require     PHP 4.0.0 (user_error)
 */
if (!function_exists('array_change_key_case')) {
		function array_change_key_case($input, $case = CASE_LOWER)
		{
				if (!is_array($input)) {
						user_error('array_change_key_case(): The argument should be an array',
								E_USER_WARNING);
						return false;
				}

				$output   = array ();
				$keys     = array_keys($input);
				$casefunc = ($case == CASE_LOWER) ? 'strtolower' : 'strtoupper';

				foreach ($keys as $key) {
						$output[$casefunc($key)] = $input[$key];
				}

				return $output;
		}
}

// From php.net
if(!function_exists('http_build_query')) {
	 function http_build_query( $formdata, $numeric_prefix = null, $key = null ) {
			 $res = array();
			 foreach ((array)$formdata as $k=>$v) {
					 $tmp_key = urlencode(is_int($k) ? $numeric_prefix.$k : $k);
					 if ($key) $tmp_key = $key.'['.$tmp_key.']';
					 $res[] = ( ( is_array($v) || is_object($v) ) ? http_build_query($v, null, $tmp_key) : $tmp_key."=".urlencode($v) );
			 }
			 $separator = ini_get('arg_separator.output');
			 return implode($separator, $res);
	 }
}

if ( !function_exists('_') ) {
	function _($string) {
		return $string;
	}
}

// Added in PHP 5.0
if (!function_exists('stripos')) {
	function stripos($haystack, $needle, $offset = 0) {
		return strpos(strtolower($haystack), strtolower($needle), $offset);
	}
}
/**
* Replace array_diff_key()
*
* @category    PHP
* @package    PHP_Compat
* @license    LGPL - [url]http://www.gnu.org/licenses/lgpl.html[/url]
* @copyright  2004-2007 Aidan Lister <[email]aidan@php.net[/email]>, Arpad Ray <[email]arpad@php.net[/email]>
* @link        [url]http://php.net/function.array_diff_key[/url]
* @author      Tom Buskens <[email]ortega@php.net[/email]>
* @version    $Revision: 1.9 $
* @since      PHP 5.0.2
* @require    PHP 4.0.0 (user_error)
*/
if (!function_exists('array_diff_key')) {
    function php_compat_array_diff_key(){
        $args = func_get_args();
        if (count($args) < 2) {
            user_error('Wrong parameter count for array_diff_key()', E_USER_WARNING);
            return;
        }
        // Check arrays
        $array_count = count($args);
        for ($i = 0; $i !== $array_count; $i++) {
            if (!is_array($args[$i])) {
                user_error('array_diff_key() Argument #' .($i + 1) . ' is not an array', E_USER_WARNING);
                return;
            }
        }
        $result = $args[0];
        if (function_exists('array_key_exists')) {
            // Optimize for >= PHP 4.1.0
            foreach ($args[0] as $key => $value) {
                for ($i = 1; $i !== $array_count; $i++) {
                    if (array_key_exists($key,$args[$i])) {
                        unset($result[$key]);
                        break;
                    }
                }
            }
        } else {
            foreach ($args[0] as $key1 => $value1) {
                for ($i = 1; $i !== $array_count; $i++) {
                    foreach ($args[$i] as $key2 => $value2) {
                        if ((string) $key1 === (string) $key2) {
                            unset($result[$key2]);
                            break 2;
                        }
                    }
                }
            }
        }
        return $result; 
    }
    function array_diff_key(){
        $args = func_get_args();
        return call_user_func_array('php_compat_array_diff_key', $args);
    }
}
//比较两数组差异
function array_diff_values($N, $O){
    $diff = array();
 	foreach($N as $Nv) if(!in_array($Nv, $O)) $diff['+'][] = $Nv;
 	foreach($O as $Okey=> $Ov) if(!in_array($Ov, $N)) $diff['-'][$Okey] = $Ov; 
 	if(empty($diff) && $N)$diff['+']=$N;
    return $diff;
} 
?>
