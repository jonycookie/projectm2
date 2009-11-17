<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
function FormArray($mId,$F,$rs=array()){
	global $iCMS;
	//系统默认字段
	$SystemField= getSystemField();
	//字段定义
	$__FIELD__	= $iCMS->cache('field.model','include/syscache',0,true);
	//遍历传入字段数组
	if($F)foreach($F AS $key=>$field){
		//取得字段定义 判读是否为自定义模型
		$info = $__FIELD__[$field][$mId]?$__FIELD__[$field][$mId]: $__FIELD__[$field][0];
		$HA[$key] = form($info,$rs);
	}
	return $HA;
}
//表单
function form($A,$rs=array()){
	$id		= $A['field'];
	$rules	= $A['rules'];
	$rs&&$v	= $rs[$id];
	if($A['hidden']=="1"){
		$FORM['hidden']='<input type="hidden" name="mVal['.$id.']" id="'.$id.'" value="'.$v.'" />';	
	}else{
		//判读是否为特殊字段
		if(in_array($A['field'],array('cid','type','vlink'))){
			switch($A['field']){
				case "cid":
					$catalog =new catalog();
					$cata_option=$catalog->select($v,0,1,'channel=1&list',$rs['mid']);
					if($cata_option){
						$html='<select name="mVal[cid]" id="cid" style="width:auto;">';
						$html.='<option value="0"> == 请选择所属栏目 == </option>';
	          			$html.=$cata_option;
	          		}else{
	          	  		$html='<select name="mVal[cid]" id="cid" onclick="redirect(\''.__SELF__.'?do=catalog&operation=add\');">';
	          			$html.='<option value="0"> == 暂无栏目请先添加 == </option>';
	          		}
	        		$html.='</select>';
				break;
				case "type":
					$html='<select name="mVal[type][]" size="10" multiple="multiple" id="type">';
		          	$html.='<option value="0">默认属性[type=\'0\']</option>';
		          	$html.=contenType("article");
		        	$html.='</select>';
		        	$html.=selected($v,'type','js');
	          	break;
				case "vlink":
					$catalog =new catalog();
					$html='<select name="mVal[vlink][]" size="10" multiple="multiple" id="vlink">';
	          		$html.=$catalog->select(0,0,1,'channel=1&list&page','all');
	          		$html.='</select>';
	          		$html.=selected($v,'vlink','js');
	          	break;
	          //	case in_array($A['field'],array('hits','digg','comments','visible','postype')):	break;
			}
		}else{
			switch($A['type']){
				case in_array($A['type'],array('number','text','email','url')):	
					$html='<input type="text" name="mVal['.$id.']" class="txt" id="'.$id.'" value="'.$v.'" />';
				break;
				case "radio":
					if($rules)foreach($rules AS $value=>$text){
						$checked= ($value==$v) ? ' checked="checked"':'';
						$html.=' <input type="radio" name="mVal['.$id.']" class="radio" id="'.$id.'" value="'.$value.'" />'.$text;
					}
				break;
				case "checkbox":
					if($rules)foreach($rules AS $value=>$text){
						$vArray=explode(',',$v);
						$checked= in_array($value,$vArray) ? ' checked="checked"':'';
						$html.=' <input type="checkbox" name="mVal['.$id.'][]" class="checkbox" id="'.$id.'.'.$value.'" value="'.$value.'" '.$checked.'/>'.$text;
					}
				break;
				case "textarea":
					$html='<textarea name="mVal['.$id.']" id="'.$id.'" onKeyUp="textareasize(this)" class="tarea">'.$v.'</textarea>';
				break;
				case "editor":
					$html='<script type="text/javascript" src="'.$iCMS->dir.'javascript/editor.js"></script>';
					$html.='<textarea name="mVal['.$id.']" id="'.$id.'" rows="30" cols="80" class="editor">'.$v.'</textarea>';
				break;
				case "select":
					$html='<select name="mVal['.$id.']" id="'.$id.'" style="width:auto;">';
					$html.='<option value="0"> == 不选择 == </option>';
					if($rules)foreach($rules AS $value=>$text){
						$selected= ($value==$v) ? ' selected="selected"':'';
						$html.='<option value="'.$value.'"'.$selected.'>'.$text.'</option>';
					}
					$html.='</select>';
				break;
				case "multiple":
					$html='<select name="mVal['.$id.'][]" id="'.$id.'" style="width:auto;" size="10" multiple="multiple">';
					$html.='<option value="0"> == 不选择 == </option>';
					if($rules)foreach($rules AS $value=>$text){
						$vArray=explode(',',$v);
						$selected= in_array($value,$vArray) ? ' selected="selected"':'';
						$html.='<option value="'.$value.'"'.$selected.'>'.$text.'</option>';
					}
					$html.='</select>';
				break;
				case "calendar":
					$value=empty($v)?get_date($v,'Y-m-d H:i:s'):$v;
					$html='<input name="mVal['.$id.']" class="txt" value="'.$value.'" id="'.$id.'" type="text" onclick="showcalendar(event, this)"/>';
				break;
				case "image":	
					$html='<input name="mVal['.$id.']" id="'.$id.'" type="text" value="'.$v.'" class="txt" style="width:450px"/>';
					$html.='<button type="button" class="selectdefault" to="pic"><span>选 择</span></button>';
					$html.='<div id="picmenu" style="display:none;">';
					$html.='<ul>';
					$html.='<li onClick="showDialog(\''.__SELF__.'.?do=dialog&operation=Aupload\',\''.$id.'\',600,140);$(\'.close\').click();">本地上传</li>';
					$html.='<li onClick="showDialog(\''.__SELF__.'?do=dialog&operation=file&hit=file&type=gif,jpg,png,bmp\',\''.$id.'\',600,500);$(\'.close\').click();">从网站选择</li>';
					$html.='<li onClick="showPic(\''.$id.'\');$(\'.close\').click();">查看缩略图</li><li onClick="cutPic(\''.$id.'\');$(\'.close\').click();">剪裁</li>';
					$html.='</ul></div>';
				break;
				case "upload":	$html='<input name="'.$id.'" type="file" class="uploadbtn" id="'.$id.'" />';break;
			}
		}
		$FORM['general']=array(
			'id'=>$id,
			'label'=>$A['name'],
			'description'=>$A['description'],
			'html'=>$html
		);
	}

	if(!in_array($A['field'],getSystemField())){
		$val='$("#'.$id.'").val()';
		//验证
		switch($A['validate']){
			case "0"://不能为空
				if($A['type']=="editor"){
					$js	= 'var '.$id.'_Editor = FCKeditorAPI.GetInstance(\'mVal['.$id.']\') ;
					if('.$id.'_Editor.GetXHTML( true )==""){
						alert("'.$A['name'].'不能为空!");
						'.$id.'_Editor.focus();
						return false;
					}';
				}else{
					$js	= 'if('.$val.'==""){
					alert("'.$A['name'].'不能为空!");
					$("#'.$id.'").focus();
					return false;}';				
				}
			break;
			case "2":
				$js='var '.$id.'_val = '.$val.';
					var pattern = /^\d+(\.\d+)?$/;
					chkFlag = pattern.test('.$id.'_val);
					if(!chkFlag){
						alert("'.$A['name'].'不是数字");
						$("#'.$id.'").focus();
						return false;}';
			break;
			case "4":
				$js='var '.$id.'_val = '.$val.';
					var pattern = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/;
					if(!pattern.test('.$id.'_val)){
						alert("邮箱地址的格式不正确!!");
						$("#'.$id.'").focus();
						return false;}';
			break;
			case "5":
				$js='var '.$id.'_val = '.$val.';
					var pattern = /^[a-zA-z]+:\/\/[^\s]*/;
					if(!pattern.test('.$id.'_val)){
						alert("['.$A['name'].']网址格式不正确!!");
						$("#'.$id.'").focus();
						return false;}';
			break;
		}
	}
	$FORM['js']=$js;
//	var_dump($FORM);
//	var_dump($A);
	return $FORM;
}
function selected($v,$id,$T){
	if(empty($v)) return;
	if($T=='js'){
		$html.='<script language="JavaScript" type="text/javascript">';
		if(strpos($v, ",")){
			$html.='var type=\''.$v.'\';$(\'#'.$id.'\').val(type.split(\',\'));';
		}else{
			$html.='$(\'#'.$id.'\').val('.(int)$v.');';
		}
		$html.='</script>';
	}
	return $html;
}

?>