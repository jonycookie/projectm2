<?php
!defined('IN_CMS') && die('Forbidden');
//require_once(R_P.'require/chinese.php');
//$chs = new Chinese('UTF8',$charset);

//From Internet
//xml中的元素
class XMLTag{
	var $parent;//父节点
	var $child;//子节点
	var $attribute;//本节点属性
	var $data;//本节点数据
	var $TagName;//本节点名称
	var $depth;//本节点的深度，根节点为1

	function XMLTag($tag=''){
		$this->attribute = array();
		$this->child = array();
		$this->depth = 0;
		$this->parent = null;
		$this->data = '';
		$this->TagName = $tag;
	}

	function __construct($tag=''){
		$this->XMLTag($tag);
	}

	function SetTagName($tag){
		$this->TagName = $tag;
	}

	function SetParent(&$parent){
		$this->parent = &$parent;
	}

	function SetAttribute($name,$value){
		$this->attribute[$name] = $value;
	}

	function AppendChild(&$child){
		$i = count($this->child);
		$this->child[$i] = &$child;
	}

	function SetData($data){
		$this->data= $data;
	}

	function GetAttr(){
		return $this->attribute;
	}

	function GetProperty($name){
		return $this->attribute[$name];
	}

	function GetData(){
		return $this->data;
	}

	function GetParent(){
		return $this->parent;
	}

	function GetChild(){
		return $this->child;
	}

	function GetTagName(){
		return $this->TagName;
	}

	function GetChildByName($name){
		$total = count($this->child);
		for($i=0;$i<$total;$i++){
			if($this->child[$i]->attribute['name'] == $name){
				return $this->child[$i];
			}
		}
		return null;
	}

	function GetChildByTagName($tag){
		$vector = array();
		$total = count($this->child);
		for($i = 0; $i < $total;$i++){
			if($this->child[$i]->TagName == $tag){
				$vector[] = $this->child[$i];
			}
		}
		return $vector;
	}

	//获取某个tag节点
    function GetElementsByTagName($tag){
		$vector = array();
		$tree = &$this;
		$this->_GetElementByTagName($tree,$tag,$vector);
		return $vector;
	}

    function _GetElementByTagName($tree,$tag,&$vector){
		if($tree->TagName == $tag) array_push($vector,$tree);
		$total = count($tree->child);
		for($i = 0; $i < $total;$i++){
			$this->_GetElementByTagName($tree->child[$i],$tag,$vector);
		}
	}
}

//xml文档解析
class XMLDoc{
	var $parser;//xml解析指针
	var $XMLTree;//生成的xml树
	var $XMLFile;//将要解析的xml文档
	var $XMLData;//将要解析的xml数据
	var $error;//错误信息
	var $NowTag;//当前指向的节点
	var $TreeData;//遍历生成的xml树等到的数据
	var $MaxDepth;//本树最大的深度
	var $encode;//xml文档的编码方式
	var $chs;//字符转换

	function XMLDoc(){
		//采用默认的ISO-8859-1
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser,XML_OPTION_CASE_FOLDING,0);
		xml_set_object($this->parser,&$this);
		xml_set_element_handler($this->parser,'_StartElement','_EndElement');
		xml_set_character_data_handler($this->parser,'_CData');
		$this->stack = array();
		$this->XMLTree = null;
		$this->NowTag = null;
		$this->MaxDepth = 0;
	}

	function __construct(){
		$this->XMLDoc();
	}

	function LoadFromFile($file){
		$this->XMLFile = fopen($file,'r');
		if(!$this->XMLFile){
			$this->error = "Can't open the xml file";
			return false;
		}
		$this->XMLData = '';
		$this->TreeData = '';
		return true;
	}

	function SetXMLData($data){
		if($this->XMLFile) fclose($this->XMLFile);
		$this->XMLData = $data;
		$this->TreeData = '';
	}

	//给树添加一个新的节点
	function AppendChild(&$child){
		if($this->XMLTree == null){
			$child->depth = 1;
			$this->XMLTree = &$child;
			$this->NowTag = &$this->XMLTree;
		}else{
			$i = count($this->NowTag->child);
			$this->NowTag->child[$i] = &$child;
			$child->parent = &$this->NowTag;
			$child->depth = $this->NowTag->depth+1;
			unset($this->NowTag);
			$this->NowTag = &$child;
		}
		$this->MaxDepth = ($this->MaxDepth < $this->NowTag->depth) ? $this->NowTag->depth : $this->MaxDepth;
	}

	//产生一个新的节点
	function &CreateElement($tag){
		$element = new XMLTag($tag);
		return $element;
	}

	function _StartElement($parser,$element,$attribute){
		$tag = new XMLTag();
		$tag->TagName = $element;
		$tag->attribute = $attribute;
		if($this->XMLTree == null){
			$tag->parent = null;
			$tag->depth = 1;
			$this->XMLTree = &$tag;
			$this->NowTag = &$tag;
		}else{
			$i = count($this->NowTag->child);
			$this->NowTag->child[$i] = &$tag;
			$tag->parent = &$this->NowTag;
			$tag->depth = $this->NowTag->depth+1;
			unset($this->NowTag);
			$this->NowTag = &$tag;
		}
		$this->MaxDepth = ($this->MaxDepth < $this->NowTag->depth) ? $this->NowTag->depth : $this->MaxDepth;
	}

	function _CData($paraser,$data){
		$this->NowTag->data = $data;
	}

	function _EndElement($parser,$element){
		$parent = &$this->NowTag->parent;
		unset($this->NowTag);
		$this->NowTag = &$parent;
	}

	//开始解析xml文档
	function parse(){
		if($this->XMLFile){
			$this->XMLData = '';
			while(!feof($this->XMLFile)){
				$this->XMLData .= fread($this->XMLFile,4096);
			}
			fclose($this->XMLFile);
		}

		if($this->XMLData){
			//$this->JudgeEncode();
			if (!xml_parse($this->parser, $this->XMLData)){
				$code = xml_get_error_code($this->parser);
				$col = xml_get_current_column_number($this->parser);
				$line = xml_get_current_line_number($this->parser);
				$this->error = "XML error: $col at line $line:".xml_error_string($code);
				return false;
			}
		}
		xml_parser_free($this->parser);
		return true;
	}
/**
	//确定编码方式
	function JudgeEncode(){
		$start = strpos($this->XMLData,'<?xml');
		$end = strpos($this->XMLData,'>');
		$str = substr($this->XMLData,$start,$end-$start);
		$pos = strpos($str,'encoding');
		if($pos !== false){
			$str = substr($str,$pos);
			$pos = strpos($str,'=');
			$str = substr($str,$pos+1);
			$pos = 0;
			while(empty($str[$pos])) $pos++;
			$this->encode = '';
			while(!empty($str[$pos]) && $str[$pos] != '?'){
				if($str[$pos] != '"' && $str[$pos] != "'"){
					$this->encode .= $str[$pos];
				}
				$pos++;
			}
		}
		$this->chs = new Chinese("UTF-8",$this->encode);
	}
*/
	//根据节点名称修改某个节点的值
	function ChangeValueByName($name,$name,$value){
		return $this->_ChangeValueByName($this->XMLTree,$name,$value);
	}

	function _ChangeValueByName($tree,$name,$value){
		if(is_array($tree->attribute)){
			while (list($k,$v) = each($tree->attribute)){
				if($k = 'name' && $v = $name){
					$tree->data = $value;
					return true;
				}
			}
		}
		$total = count($tree->child);
		for($i = 0;$i<$total;$i++){
			$result = $this->_ChangeValueByName($tree->child[$i],$name,$value);
			if($result == true) break;
		}
		return $result;
	}

	//根据节点名称修改树中某个节点的属性
	function ChangeAttrByName($name,$attr,$value){
		return $this->_ChangeAttrByName($this->XMLTree,$name,$attr,$value);
	}

	function _ChangeAttrByName(&$tree,$name,$attr,$value){
		if(is_array($tree->attribute)){
			while(list($k,$v) = each($tree->atttibute)){
				if($k == 'name' && $v == $name){
					$tree->attribute[$attr] = $value;
					return true;
				}
			}
		}
		$total = count($tree->child);
		for($i = 0;$i<$total;$i++){
			$result = $this->_ChangeAttrByName($tree->child[$i],$name,$attr,$value);
			if($result == true) break;
		}
		return $result;
	}

	//获取根节点
	function &GetDocumentElement(){
		return $this->XMLTree;
	}

	//遍历生成的xml树,重新生成xml文档
	function WalkTree(){
		$this->TreeData = '';
		$this->_WalkTree($this->XMLTree);
		return $this->TreeData;
	}

	//递归遍历
	function _WalkTree($tree){
		$this->TreeData .= '<'.$tree->TagName.' ';
		if(is_array($tree->attribute)){
			while(list($key,$value) = each($tree->attribute)){
				$this->TreeData .="$key=\"$value\" ";
			}
		}
		$this->TreeData .= '>'.$tree->data;
		$total = count($tree->child);
		for($i=0;$i<$total;$i++){
			$this->_WalkTree($tree->child[$i]);
		}
		$this->TreeData .= '</'.$tree->TagName.">\n";
	}

	//获取错误信息
	function GetError(){
		return $this->error;
	}

	//获取树的最大深度
	function GetMaxDepth(){
		return $this->MaxDepth;
	}

	//将xml树写入xml文件
	function WriteToFile($file,$head=''){
		$fp = fopen($file,'w');
		if(!$fp){
			$this->error = 'The file is not writable';
			return false;
		}
		if(empty($this->TreeData)) $this->WalkTree();
		$head = empty($head) ? '<?xml version="1.0" standalone="yes" encoding="gb2312"?>' : $head;
		fwrite($fp,$head);
		fwrite($fp,$this->TreeData);
		fclose($fp);
		return true;
	}
}
?>