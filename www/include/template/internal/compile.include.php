<?php
/**
 * Template Lite
 *
 * Type:	 compile
 * Name:	 section_start
 *
 * ADDED: { include file='filename' import=true }
 * @modifier dualface <dualface#gmail.com>
 *
 * ADDED: { include file='./filename' import=true }
 * @modifier ¿ÝÄ¾ <www.iDreamSoft.cn> 16:17 2007-11-8
 */

function compile_include($arguments, &$object){
	$_args = $object->_parse_arguments($arguments);
	$arg_list = array();
	if (empty($_args['file'])){
		$object->trigger_error("missing 'file' attribute in include tag", E_USER_ERROR, __FILE__, __LINE__);
	}

	foreach ($_args as $arg_name => $arg_value){
		if ($arg_name == 'file'){
			strpos($arg_value,'..')!==false && $object->trigger_error("'file' attribute has '..'", E_USER_ERROR);;
			if(strpos($arg_value,'./')=== false){
				$include_file = $arg_value;
			}else{
				$include_file=str_replace('./',dirname($object->_file).'/',$arg_value);
			}
			continue;
		}else if ($arg_name == 'assign'){
			$assign_var = $arg_value;
			continue;
		}
		if (is_bool($arg_value)){
			$arg_value = $arg_value ? 'true' : 'false';
		}
		$arg_list[] = "'$arg_name' => $arg_value";
	}

	if (isset($assign_var)){
		$output = '<?php $_templatelite_tpl_vars = $this->_vars;' .
			"\n\$this->assign(" . $assign_var . ", \$this->_fetch_compile_include(" . $include_file . ", array(".implode(',', (array)$arg_list).")));\n" .
			"\$this->_vars = \$_templatelite_tpl_vars;\n" .
			"unset(\$_templatelite_tpl_vars);\n" .
			' ?>';
	}else{
		$include_file = str_replace('"', '', $include_file);
		$include_file = str_replace("'", '', $include_file);
		$include_file = $object->_get_resource($include_file);
		$output = $object->_fetch_compile($include_file, 0, false);
	}
	return $output;
}
?>