<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0 Beta 7
//******************************************************//
// xml parser class
// $Id: xml.lib.php 1 2006-04-25 22:10:16Z mutantmonkey $
//******************************************************//

/*
THANK YOU http://eric.pollmann.net/items/2003/9/2003_09_22_xmlparser/XMLParser.php!
*/

/**
 * A simple XML parser
 *
 * @package		IceBB
 * @version		1.0 Beta 6
 * @date		June 12, 2005
 */
class xml_parser
{
	var $xml_header				= "<?xml version='1.0'?>";
	var $parser;

	// XML READ
	function load_document($path)
	{	
		$data				= file_get_contents($path);

		$this->parser		= xml_parser_create();
		xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($this->parser,$data,$vals,$index);
		xml_parser_free($this->parser);
		
		$i = -1;
		return $this->_xml_get_children($vals,$i);
	}
	
	/**
	 * Somehow, this is the best XML parser I've found for parsing RSS feeds
	 * and it's only one function!
	 *
	 * @author		vladson@pc-labs.info
	 * @param		string		XML to parse
	 * @return		array		Parsed XML
	 **/
	function xml2array($text)
	{
		$reg_exp = '/<(.*?)>(.*?)<\/\\1>/s';
		preg_match_all($reg_exp, $text, $match);
		foreach ($match[1] as $key=>$val) {
		if ( preg_match($reg_exp, $match[2][$key]) ) {
		$array[$val][] = $this->xml2array($match[2][$key]);
		} else {
		$array[$val] = $match[2][$key];
		}
		}
		return $array;
	}
	
	// XML CREATE
	function new_document()
	{	
		$this->parser		= xml_parser_create();
		//xml_parser_set_option($this->parser,XML_OPTION_SKIP_WHITE,1);
		//xml_parse_into_struct($this->parser,$data,$vals,$index);
		xml_parser_free($this->parser);
	}
	
	// internal function: build a node of the tree
	function _xml_build_tag($thisvals, $vals, &$i, $type)
	{

		if (isset($thisvals['attributes']))
			$tag['ATTRIBUTES'] = $thisvals['attributes']; 

		// complete tag, just return it for storage in array
		if ($type === 'complete')
			$tag['VALUE'] = $thisvals['value'];

		// open tag, recurse
		else
			$tag = array_merge($tag, $this->_xml_get_children($vals, $i));

		return $tag;
	}
	
	function _xml_get_children($vals,$i)
	{
		$children = array();     // Contains node data

		// Node has CDATA before it's children
                if ($i > -1 && isset($vals[$i]['value']))
			$children['VALUE'] = $vals[$i]['value'];

		// Loop through children, until hit close tag or run out of tags
		while (++$i < count($vals)) { 

			$type = $vals[$i]['type'];

			// 'cdata':	Node has CDATA after one of it's children
			// 		(Add to cdata found before in this case)
			if ($type === 'cdata')
				$children['VALUE'] .= $vals[$i]['value'];

			// 'complete':	At end of current branch
			// 'open':	Node has children, recurse
			elseif ($type === 'complete' || $type === 'open') {
				$tag = $this->_xml_build_tag($vals[$i], $vals, $i, $type);
				if ($this->index_numeric) {
					$tag['TAG'] = $vals[$i]['tag'];
					$children[] = $tag;
				} else
					$children[$vals[$i]['tag']][] = $tag;
			}

			// 'close:	End of node, return collected data
			//		Do not increment $i or nodes disappear!
			elseif ($type === 'close')
				break;
		} 
		if ($this->collapse_dups)
			foreach($children as $key => $value)
				if (is_array($value) && (count($value) == 1))
					$children[$key] = $value[0];
		return $children;
	}
}
?>