<?php
/**
 * Template Lite iCMS compile plugin ADD iDreamSoft
 *
 * Type:	 compile
 * Name:	 compile_iCMS
 */

function compile_iCMS($arguments, &$object){
	$attrs = $object->_parse_arguments($arguments);
	$arg_list = array();
	$output = '<?php ';
	$hash=substr(uniqid(rand()), -4);
	$iCMS_props = "\$this->_iCMS['G{$hash}']";
	$iCMS_vars_tmp="\$this->_iCMS['_G{$hash}']";
	$output .= "if (isset($iCMS_props)) unset($iCMS_props);\n";	
	foreach ($attrs as $attr_name => $attr_value){
		switch ($attr_name){
			case 'loop':
				$output .= "{$iCMS_props}['loop'] = is_array($attr_value) ? count($attr_value) : max(0, (int)$attr_value);\n";
				$output .="{$iCMS_vars_tmp}={$attr_value};\nunset($attr_value);\n";
				break;
			case 'show':
				if (is_bool($attr_value)){
					$show_attr_value = $attr_value ? 'true' : 'false';
				}else{
					$show_attr_value = "(bool)$attr_value";
				}
				$output .= "{$iCMS_props}['show'] = $show_attr_value;\n";
				break;
			case 'name':
				$output .= "{$iCMS_props}['$attr_name'] = '$attr_value';\n";
				break;
			case 'max':
			case 'start':
				$output .= "{$iCMS_props}['$attr_name'] = (int)$attr_value;\n";
				break;
			case 'step':
				$output .= "{$iCMS_props}['$attr_name'] = ((int)$attr_value) == 0 ? 1 : (int)$attr_value;\n";
				break;
			default:
				$object->trigger_error("unknown section attribute - '$attr_name'", E_USER_ERROR, __FILE__, __LINE__);
				break;
		}
	}

	if (!isset($attrs['show'])){
		$output .= "{$iCMS_props}['show'] = true;\n";
	}

	if (!isset($attrs['loop'])){
		$output .= "{$iCMS_props}['loop'] = 1;\n";
	}

	if (!isset($attrs['max'])){
		$output .= "{$iCMS_props}['max'] = {$iCMS_props}['loop'];\n";
	}else{
		$output .= "if ({$iCMS_props}['max'] < 0)\n" .
					"	{$iCMS_props}['max'] = {$iCMS_props}['loop'];\n";
	}

	if (!isset($attrs['step'])){
		$output .= "{$iCMS_props}['step'] = 1;\n";
	}

	if (!isset($attrs['start'])){
		$output .= "{$iCMS_props}['start'] = {$iCMS_props}['step'] > 0 ? 0 : {$iCMS_props}['loop']-1;\n";
	}else{
		$output .= "if ({$iCMS_props}['start'] < 0){\n" .
				   "	{$iCMS_props}['start'] = max({$iCMS_props}['step'] > 0 ? 0 : -1, {$iCMS_props}['loop'] + {$iCMS_props}['start']);\n" .
				   "}else{\n" .
				   "	{$iCMS_props}['start'] = min({$iCMS_props}['start'], {$iCMS_props}['step'] > 0 ? {$iCMS_props}['loop'] : {$iCMS_props}['loop']-1);\n}";
	}

	$output .= "if ({$iCMS_props}['show']) {\n";
	if (!isset($attrs['start']) && !isset($attrs['step']) && !isset($attrs['max'])){
		$output .= "	{$iCMS_props}['total'] = {$iCMS_props}['loop'];\n";
	}else{
		$output .= "	{$iCMS_props}['total'] = min(ceil(({$iCMS_props}['step'] > 0 ? {$iCMS_props}['loop'] - {$iCMS_props}['start'] : {$iCMS_props}['start']+1)/abs({$iCMS_props}['step'])), {$iCMS_props}['max']);\n";
	}
	$output .= "	if ({$iCMS_props}['total'] == 0){\n" .
			   "		{$iCMS_props}['show'] = false;\n\t}\n" .
			   "} else{\n" .
			   "	{$iCMS_props}['total'] = 0;\n}\n";

	$output .= "if ({$iCMS_props}['show']){\n";
	$output .= "
		for ({$iCMS_props}['index'] = {$iCMS_props}['start'], {$iCMS_props}['iteration'] = 1;
			 {$iCMS_props}['iteration'] <= {$iCMS_props}['total'];
			 {$iCMS_props}['index'] += {$iCMS_props}['step'], {$iCMS_props}['iteration']++){\n";
	$output .= "{$iCMS_props}['rownum'] = {$iCMS_props}['iteration'];\n";
	$output .= "{$iCMS_props}['index_prev'] = {$iCMS_props}['index'] - {$iCMS_props}['step'];\n";
	$output .= "{$iCMS_props}['index_next'] = {$iCMS_props}['index'] + {$iCMS_props}['step'];\n";
	$output .= "{$iCMS_props}['first']	  = ({$iCMS_props}['iteration'] == 1);\n";
	$output .= "{$iCMS_props}['last']	   = ({$iCMS_props}['iteration'] == {$iCMS_props}['total']);\n";
	$output .= "{$attrs['loop']}= array_merge({$iCMS_vars_tmp}[{$iCMS_props}['index']],{$iCMS_props});\n";
	$output .= "?>";

	return $output;
}
?>