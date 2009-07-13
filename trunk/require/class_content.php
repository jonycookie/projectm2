<?php
!defined('IN_CMS') && die('Forbidden');
require_once(D_P.'data/cache/cate.php');
require_once(D_P.'data/cache/field.php');
require_once(D_P.'data/cache/select.php');

/**
 * CMS的最主要的类之一：输入编辑类
 * 本类提供各种内容模型的输入输出接口，添加内容、编辑内容的基本管理
 *
 */
class Content{
	/**
	 * 内容模型字段
	 *
	 * @var array
	 */
	var $fields;

	/**
	 * 系统默认公共字段
	 *
	 * @var array
	 */
	var $index;

	/**
	 * 预设值选择器信息
	 *
	 * @var array
	 */
	var $selectdb;

	/**
	 * 当前要操作的表名
	 *
	 * @var string
	 */
	var $table;

	/**
	 * 当前操作内容的mid
	 *
	 * @var integer
	 */
	var $mid;

	/**
	 * 当前插入内容的ID，此变量用于向其他关联类传递
	 *
	 * @var integer
	 */
	var $insertId;

	/**
	 * 对db对象的引用
	 *
	 * @var unknown_type
	 */
	var $mysql;

	/**
	 * 操作类型，增加 or 更新
	 *
	 * @var string
	 */
	var $action;

	/**
	 * 构造函数 初始化一些基本信息
	 *
	 * @param integer $mid
	 */
	function __construct($mid){ //PHP5
		global $db,$fielddb,$selectdb;
		$this->mid		= (int)$mid;
		$this->mysql	= $db;
		$this->mid <=0 && die('Mid Error.');
		$this->table	='cms_content'.$this->mid;
		$this->fields	= $fielddb[$this->mid];
		$this->index	= array('title','postdate','url','linkurl','photo','digest','comnum','publisher','titlestyle');
		$this->selectdb	= $selectdb;
	}

	function Content($mid){ //PHP4
		$this->__construct($mid);
	}

	/**
	 * 添加内容时的输入部分
	 *
	 * @return array
	 */
	function inputArea($type='admin'){
		$inputArea = array();
		foreach ($this->fields as $field){
			@extract($field);
			if($type=='custom' && !$ifcontribute) {
				continue;
			}
			if($value){
				$fieldvalue=$value[$field];
			}else{
				$fieldvalue='';
			}
			$selectValue = $this->selectValue($fieldid,$getvalue,$inputtype);
			switch ($inputtype){
				case 'input':
					$sizelimit = $inputsize ? "size=\"$field[inputsize]\"" : '';
					$field['input']="<input class=\"input\" name=\"$fieldid\" id=\"$fieldid\" value=\"$field[defaultvalue]\" $sizelimit> $selectValue $inputlabel";
					break;
				case 'textarea':
					$sizelimit = $inputsize>60 ? "cols=\"$inputsize\"" : "cols=\"60\"";
					$field['input']="<textarea name=\"$fieldid\" id=\"$fieldid\" $sizelimit rows='8'>$field[defaultvalue]</textarea> $selectValue $inputlabel";
					break;
				case 'radio':
					$defaultvalue = explode('|',$defaultvalue);
					$i=0;
					$str='';
					$inputlabel = explode('|',$inputlabel);
					foreach ($defaultvalue as $value){
						$selected = $i==0 ? "checked" : '';
						$str.="<input type=\"radio\" value=\"$value\" name=\"$fieldid\" $selected /> $inputlabel[$i]";
						$i++;
					}
					$field['input']=$str;
					break;
				case 'checkbox':
					$defaultvalue = explode('|',$defaultvalue);
					$inputlabel = explode('|',$inputlabel);
					$i=0;
					$str='';
					foreach ($defaultvalue as $value){
						//$selected=$i==0 ? "checked" : '';
						$str.="<input type=\"checkbox\" value=\"$value\" name=\"{$fieldid}[]\" $selected> $inputlabel[$i] ";
						$i++;
					}
					$field['input']=$str;
					break;
				case 'select':
					$defaultvalue = explode('|',$defaultvalue);
					$inputlabel = explode('|',$inputlabel);
					$str="<select  name=\"$fieldid\">";
					$i=0;
					foreach ($defaultvalue as $value){
						$str.="<option value=\"$value\">$inputlabel[$i]</option>";
						$i++;
					}
					$str.="</select>";
					$field['input']=$str;
					break;
				case 'mselect':
					$str="<select multiple size=8 name=\"$fieldid\" id=\"$fieldid\">";
					$str.="</select><input type=hidden name=\"".$fieldid."_value\" id=\"".$fieldid."_value\" value=''> $selectValue <br /><img  onclick=del('$fieldid') src='images/admin/delete.gif'><img onclick=moveUp('$fieldid') src='images/admin/up.gif'><img onclick=moveDown('$fieldid') src='images/admin/down.gif'>";
					$field['input']=$str;
					break;
				case 'edit':
					if($type=='custom') {
						$tooltype = 'CBasic';
					}else {
						$tooltype = 'Default';
					}
					$str = $this->Editor($fieldid,$tooltype);
					$str.= "<br />".$selectValue;
					$field['input']=$str;
					break;
				case 'basic':
					if($type=='custom') {
						$tooltype = 'CBasic';
					}else {
						$tooltype = 'Basic';
					}
					$str = $this->Editor($fieldid,$tooltype);
					$str.= $selectValue;
					$field['input']=$str;
					break;
			}
			$inputArea[]=$field;
		}
		return $inputArea;
	}

	/**
	 * 编辑内容是的编辑框
	 *
	 * @param integer $tid
	 * @return array
	 */
	function editArea($tid,$type='admin'){
		global $sys;
		$t = $this->mysql->get_one("SELECT * FROM cms_contentindex i LEFT JOIN $this->table c USING(tid) WHERE i.tid='$tid'");
		$inputArea = array();
		foreach ($this->fields as $field){
			@extract($field);
			if($type=='custom' && !$ifcontribute) {
				continue;
			}
			$fieldvalue=$t[$fieldid];
			$selectValue = $this->selectValue($fieldid,$getvalue,$inputtype);
			switch ($inputtype){
				case 'input':
					$fieldvalue = stripslashes($fieldvalue);
					$sizelimit = $inputsize ? "size=\"$field[inputsize]\"" : '';
					$field['input']="<input class=\"input\" name=\"$fieldid\" id=\"$fieldid\" value=\"$fieldvalue\" $sizelimit> $selectValue $inputlabel";
					break;
				case 'textarea':
					$fieldvalue = stripslashes($fieldvalue);
					$sizelimit = $inputsize>60 ? "cols=\"$inputsize\"" : "cols=\"60\"";
					$field['input']="<textarea name=\"$fieldid\" id=\"$fieldid\" $sizelimit rows='8'>$fieldvalue</textarea> $inputlabel";
					break;
				case 'radio':
					$defaultvalue = explode('|',$defaultvalue);
					$inputlabel = explode('|',$inputlabel);
					$str='';
					$i=0;
					foreach ($defaultvalue as $value){
						$selected = $fieldvalue==$value ? 'checked' : '';
						$str.="<input type=\"radio\" value=\"$value\" name=\"$fieldid\" $selected> $inputlabel[$i]";
						$i++;
					}
					$field['input']=$str;
					break;
				case 'checkbox':
					$defaultvalue = explode('|',$defaultvalue);
					$inputlabel = explode('|',$inputlabel);
					$fieldvalue = explode(',',$fieldvalue);
					$str='';
					$i=0;
					foreach ($defaultvalue as $value){
						$selected = in_array($value,$fieldvalue) ? 'checked' : '';
						$str.="<input type=\"checkbox\" value=\"$value\" name=\"{$fieldid}[]\" $selected> $inputlabel[$i] ";
						$i++;
					}
					$field['input']=$str;
					break;
				case 'select':
					$defaultvalue = explode('|',$defaultvalue);
					$inputlabel = explode('|',$inputlabel);
					$str="<select  name=\"$fieldid\">";
					$i=0;
					foreach ($defaultvalue as $value){
						$str.="<option value=\"$value\">$inputlabel[$i]</option>";
						$i++;
					}
					$str.="</select>";
					$str = str_replace("value=\"$fieldvalue\"","value=\"$fieldvalue\" selected",$str);
					$field['input']=$str;
					break;
				case 'mselect':
					$str="<select multiple size=8 name=\"$fieldid\" id=\"$fieldid\">";
					if($t[$fieldid] && preg_match('/^(\d+\,)*\d+$/',$t[$fieldid])) {
						$rt = $this->mysql->query("SELECT tid,title,url,cid FROM cms_contentindex WHERE tid IN($t[$fieldid])");
						$option = $fieldtids = array();
						while($contentlink = $this->mysql->fetch_array($rt)) {
							$option[$contentlink[tid]] = "<option value=\"$contentlink[tid]\">$contentlink[title]</option>";
						}
						$fieldtids = explode(",",$t[$fieldid]);
						foreach($fieldtids as $val) {
							if(!$val) continue;
							if($option[$val]) {
								$str.=$option[$val];
							}
						}
					}
					$str.="</select><input type=hidden name=\"".$fieldid."_value\" id=\"".$fieldid."_value\" value=''> $selectValue <br /><img  onclick=del('$fieldid') src='images/admin/delete.gif'><img onclick=moveUp('$fieldid') src='images/admin/up.gif'><img onclick=moveDown('$fieldid') src='images/admin/down.gif'>";
					$field['input']=$str;
					break;
				case 'edit':
					$fieldvalue = stripslashes($fieldvalue);
					if($type=='custom') {
						$tooltype = 'CBasic';
					}else {
						$tooltype = 'Default';
					}
					$str = $this->Editor($fieldid,$tooltype,$fieldvalue);
					$field['input']=$str;
					break;
				case 'basic':
					$fieldvalue = stripslashes($fieldvalue);
					if($type=='custom') {
						$tooltype = 'CBasic';
					}else {
						$tooltype = 'Basic';
					}
					$str = $this->Editor($fieldid,$tooltype,$fieldvalue);
					$field['input']=$str;
					break;
			}
			$inputArea[]=$field;
		}
		$inputArea['template']	= $t['template'];
		$inputArea['digest']	= $t['digest'];
		$inputArea['linkurl']	= $t['linkurl'];
		$inputArea['photo']		= $t['photo'];
		$inputArea['template']	= $t['template'];
		$inputArea['titlestyle']= $t['titlestyle'];
		$inputArea['postdate']	= get_date($t['postdate'],'Y-m-d H:i:s');
		return $inputArea;
	}

	/**
	 * FCK编辑器生成
	 *
	 * @param string $inputname
	 * @param string $Value
	 * @return string
	 */
	function Editor($inputname,$tooltype,$Value=''){
		global $sys;
		if($tooltype=='Default' || $tooltype=='CDefault'){
			$height=420;
			$width=650;
		}elseif($tooltype=='Basic'){
			$height=150;
			$width=450;
		}elseif($tooltype=='CBasic') {
			$height=150;
			$width=500;
		}
		require_once(R_P.'require/fckeditor.php');
		$edit = new Fckeditor($inputname);
		$edit->Height = $height;
		$edit->Width = $width;
		$edit->ToolbarSet = $tooltype;
		$edit->BasePath = 'require/';
		$edit->Value = $Value;
		$edit->BaseSrc = $sys['url'];
		$edit->BaseName = $GLOBALS['admin_file'];
		$Html = $edit->CreateHtml();
		if($tooltype=='Default'){
			$Html.="<br /><input type=\"checkbox\" name=\"imagetolocal\" value=1 /> 外部图片本地化";
			$Html.="<br /><input type=\"checkbox\" name=\"selectimage\" value=1 /> 自动提取第一张图片为新闻图片";
			$Html.="<br /><input type=\"checkbox\" name=\"autofpage\" value=1 /> 自动分页处理";
		}
		//$basedir = "editor";
		/*		if($this->IsCompatible()){
		$url = urlencode($basename);
		$Html.="<iframe src=\"$basename&action=wysiswyg&inputname=$inputname&basename=$url\" height=\"$height\" width=\"$width\" frameborder=\"0\" scrolling=\"no\"></iframe>";
		$Value = ltrim($Value);
		$Value = htmlspecialchars($Value);
		$Html.= "<input type=\"hidden\" name=\"$inputname\" id=\"$inputname\" value=\"$Value\">";
		}else{
		$Html = "<textarea name=\"{$inputname}\" rows=\"4\" cols=\"40\" style=\"width: {$width}px; height: {$height}px;\">{$Value}</textarea>" ;
		}*/
		return $Html;
	}

	/**
	 * 判断浏览器
	 *
	 * @return boolean
	 */
	function IsCompatible(){
		global $HTTP_USER_AGENT ;
		if ( isset( $HTTP_USER_AGENT ) )
		$sAgent = $HTTP_USER_AGENT ;
		else
		$sAgent = $_SERVER['HTTP_USER_AGENT'] ;

		if ( strpos($sAgent, 'MSIE') !== false && strpos($sAgent, 'mac') === false && strpos($sAgent, 'Opera') === false ){
			$iVersion = (float)substr($sAgent, strpos($sAgent, 'MSIE') + 5, 3) ;
			return ($iVersion >= 5.5) ;
		}else if ( strpos($sAgent, 'Gecko/') !== false ){
			$iVersion = (int)substr($sAgent, strpos($sAgent, 'Gecko/') + 6, 8) ;
			return ($iVersion >= 20030210) ;
		}
		else{
			return false ;
		}
	}

	/**
	 * 内容预设值选择器
	 *
	 * @param string $inputname
	 * @param integer $getvalue
	 * @return string
	 */
	function selectValue($inputname,$getvalue,$inputtype=''){
		if($getvalue<10){ //10以内作为系统内置的选择器
			switch ($getvalue){
				case 0:
					return ;
				case 1:
					$select = "<img id='show_$inputname' src='images/admin/addcolor.gif' style='cursor:pointer;' align='absmiddle' onclick=\"showColor('show_".$inputname."','".$inputname."');\">";
					break;
				case 2:
					$select = "<img src='images/admin/addatt.gif' style='cursor:pointer;' align='absmiddle' onclick=\"selectAttach('".$inputname."');\">";
					break;
				case 3:
					$select = "<img src='images/admin/addtime.gif' style='cursor:pointer;' align='absmiddle' onclick=\"ShowCalendar('".$inputname."');\">";
					break;
				case 4:
					$select = "<img src='images/admin/rename.gif' style='cursor:pointer;' align='AbsBottom' onclick=\"selectTids('".$inputname."','".$inputtype."');\">";
					break;
				case 5:
					$select = "<img src='images/admin/addimg.gif' style='cursor:pointer;' align='absmiddle' onclick=\"selectImg('".$inputname."');\">";
					break;
			}
		}else{
			$rs = $this->mysql->query("SELECT * FROM cms_selectvalue WHERE selectid='$getvalue' ORDER BY usetime DESC LIMIT 50");
			$select = "<select onchange=\"selectValue('".$inputname."',this.value);\">";
			$select.="<option value=\"\">".$this->selectdb[$getvalue]['selectname']."</option>";
			while ($val = $this->mysql->fetch_array($rs)) {
				$select.="<option value=\"".$val['value']."\">".$val['value']."</option>";
			}
			$select.="</select>";
		}
		return $select;
	}

	/**
	 * 插入内容数据
	 *
	 * @param array $array
	 * @param integer $cid
	 * @param integer $mid
	 * @param integer $type
	 */
	function InsertData(&$array,$cid,$mid='',$type=null){
		global $catedb;
		$cid = (int)$cid;
		if(!$mid){
			$mid = $this->mid;
		}else{
			$mid = intval($mid);
		}

		list($query1,$query2) = $this->ParseData($array,$type);
		if($query1) $query1 .= ' , ';
		if($query2) $query2 .= ' , ';
		$query_index	= "INSERT INTO cms_contentindex SET  ".$query1 ."mid='$mid',cid='$cid',publisher='$GLOBALS[admin_name]'";
		$this->mysql->update($query_index);
		$tid = $this->mysql->insert_id();
		$this->insertId = $tid;

		$query_content	= "INSERT INTO $this->table SET ".$query2."tid='$tid'";
		$this->mysql->update($query_content);

		$newnum = $catedb[$cid]['autopub'] ? 0 : 1;
		$this->mysql->update("UPDATE cms_category SET new=new+1,total=total+1 WHERE cid='$cid'");
		if($array['aid']){
			empty($array['photo']) && $array['aid'] = '';
			$this->AttachLink($array['aid'],$tid,'insert'); //关联附件
		}
		if($array['tagsid']){
			$this->contentTags($array['tagsid'],$tid,'insert');
		}
		if($type=='1') {
			$this->mysql->update("UPDATE cms_contentindex SET ifpub=3 WHERE tid='$tid' AND cid='$cid'");
			$this->mysql->update("UPDATE cms_category SET total=total+1 WHERE cid='$cid'");
			return;
		}
		if($catedb[$cid]['autopub']){ //如果自动发布
			if($catedb[$cid]['htmlpub']){ //如果静态发布
				require_once(R_P.'require/class_action.php');
				$Action = new Action('pubview');
				$Action->cate($cid);
				$Action->doIt($tid);
			}else{
				$this->mysql->update("UPDATE cms_contentindex SET ifpub=1 WHERE tid='$tid' AND cid='$cid'");
				$this->mysql->update("UPDATE cms_category SET total=total+1 WHERE cid='$cid'");
			}
		}
	}

	/**
	 * 修改更新内容数据
	 *
	 * @param array $array
	 * @param integer $tid
	 */
	function UpdateData(&$array,$tid){
		$tid	= intval($tid);
		list($query1,$query2) = $this->ParseData($array);
		$query_index	= "UPDATE cms_contentindex SET " .$query1. " WHERE tid='$tid'";
		$query_content	= "UPDATE $this->table SET " .$query2. " WHERE tid='$tid'";

		$query1 && $this->mysql->update("$query_index"); //分别向两张表中写入数据
		$query2 && $this->mysql->update("$query_content");
		if($array['aid']){
			empty($array['photo']) && $array['aid'] = '';
			$this->AttachLink($array['aid'],$tid,'update');
		}
		if($array['tagsid']){
			$this->contentTags($array['tagsid'],$tid,'update');
		}
	}

	/**
	 * 解析传递过来的内容数据
	 *
	 * @param array $array
	 * @return string 根据数据所构造出的查询语句
	 */
	function ParseData(&$array,$type=null){
		global $sys,$user_tplpath;
		$number_input = array('tinyint','smallint','int');
		foreach ($this->fields as $field){
			$key = $field['fieldid'];
			if (empty($array[$key]) && empty($array[$key.'_value']) && $field['inputtype']!='mselect'){
				$array[$key]='';
			}else{
				$array[$key] = str_replace($sys['url'].'/','',$array[$key]);
				//保持一个相对路径以防域名变动带来的错误
				if($field['inputtype']=='checkbox'){ //复选框内容要特殊处理
					$array[$key]=','.implode(',',$array[$key]).','; //前后附加一个,是为了搜索方便
				}elseif ($field['inputtype']=='edit'){ //如果是编辑器，也需要特殊处理
					if($array['imagetolocal']){ //图片本地化
						require_once(R_P.'require/class_attach.php');
						$attach = new Attach();
						$array[$key] = $attach->imageToLocal(stripslashes($array[$key]));
						$array[$key] = addslashes($array[$key]);
					}
					if($array['selectimage'] && !$array['photo']){ //自动选择第一个图片作为新闻图片
						if (strstr(strtolower($array[$key]), "img") && strstr(strtolower($array[$key]), "src")){
							eregi("(<img[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?[^>]*[/]?>", stripslashes($array[$key]), $images);
							if($images[2]){
								$query1['photo']="`photo`='".$images[2]."'";
								$array['photo']=$images[2];
								//分两步确保了无论photo字段在前还是在后都会被更改
								//$array[$key] = str_replace(addslashes($images[0]),'',$array[$key]);
							}
						}
					}
					$cutpagesize = $GLOBALS['sys']['perpage'] ? $GLOBALS['sys']['perpage']:5;
					if (($array['autofpage'] && strlen($array[$key])>$cutpagesize*1024) || strpos($array[$key],'<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>')>0 || strpos($array[$key],'<div style=\"page-break-after: always;\"><span style=\"display: none;\">&nbsp;</span></div>')>0) { //自动分页
						$query1['fpage'] = "`fpage`=1";
						if(($array['autofpage'] && strlen($array[$key])>$cutpagesize*1024) && !strpos($array[$key],'<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>') && !strpos($array[$key],'<div style=\"page-break-after: always;\"><span style=\"display: none;\">&nbsp;</span></div>')) {
							if(strpos($array[$key],'<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>')){
								$array[$key] = $this->cutPage($array[$key],$cutpagesize,'<div style=\"page-break-after: always\"><span style=\"display: none\">&nbsp;</span></div>');
							}else{
								$array[$key] = $this->cutPage($array[$key],$cutpagesize,'<div style=\"page-break-after: always;\"><span style=\"display: none;\">&nbsp;</span></div>');
							}
						}
					}else{
						$query1['fpage'] = "`fpage`=0";
					}
				}elseif($field['inputtype']=='mselect') {
					if($array[$key.'_value'] && preg_match('/^(\d+\,)*\d+$/',$array[$key.'_value'])) {
						$array[$key] = $array[$key.'_value'];
					}else {
						$array[$key] = '';
					}
					unset($array[$key.'_value']);
				}

				if($field['getvalue']>10 && $type==null){ //预设值选择器要特殊处理
					if($this->mysql->get_one("SELECT * FROM cms_selectvalue WHERE value='$array[$key]' AND selectid='$field[getvalue]'")){
						$this->mysql->update("UPDATE cms_selectvalue SET usetime='$GLOBALS[timestamp]' WHERE value='$array[$key]' AND selectid='$field[getvalue]'");
					}else{
						$this->mysql->update("INSERT INTO cms_selectvalue SET value='$array[$key]',selectid='$field[getvalue]',usetime='$GLOBALS[timestamp]'");
					}
				}
			}
			/* 内容分析完毕 开始构造数据库查询语句 */
			if(in_array($field['fieldtype'],$number_input)){
				$array[$key]=(int)$array[$key];
			}else{
				//$array[$key]=addslashes($array[$key]);
			}
			if(in_array($key,$this->index)){ //索引字段写入一张contentindex表
				$query1[$key] = "`$key`='$array[$key]'";
			}else{ //非索引字段写入另外一张表
				$query2[$key] = "`$key`='$array[$key]'";
			}
		}
		$titlestyle = serialize(array('titlecolor'=>"$array[titlecolor]",'titleb'=>"$array[titleb]",'titleii'=>"$array[titleii]",'titleu'=>"$array[titleu]"));
		if($array['template'] && file_exists(R_P.$user_tplpath.'/'.$array['template'])) {
			$query1['template'] = "`template`='$array[template]'";
		}else {
			$query1['template'] = "`template`=''";
		}
		$query1['digest'] = "`digest`='$array[digest]'";
		$query1['linkurl'] = "`linkurl`='$array[linkurl]'";
		$query1['photo'] = "`photo`='$array[photo]'";
		$query1['titlestyle'] = "`titlestyle`='$titlestyle'";
		$query1['postdate'] = "`postdate`='$array[postdate]'";
		if($query1){
			$query1 = implode(',',$query1);
		}
		if($query2){
			$query2 = implode(',',$query2);
		}
		return array($query1,$query2);
	}

	/**
	 * 对内容的相关Tag进行分析
	 *
	 * @param string $tagsid
	 * @param integer $tid
	 * @param string $action
	 */
	function contentTags($tagsid,$tid,$action){
		if(empty($tagsid) && $action=='insert') return null;
		if($action=='update'){
			$rs = $this->mysql->query("SELECT tagid FROM cms_contenttag WHERE tid='$tid'");
			$old_tagid = $oldid =  array();
			while ($old_tagid = $this->mysql->fetch_array($rs)) {
				$oldid[$old_tagid['tagid']] = $old_tagid['tagid'];
			}
			foreach ($tagsid as $tagid){
				if(!in_array($tagid,$oldid)){ //说明是新增加的Tag
					$this->mysql->update("INSERT INTO cms_contenttag SET tagid='$tagid',tid='$tid',mid='$this->mid'");
					$this->mysql->update("UPDATE cms_tags SET num=num+1 WHERE tagid='$tagid'");
				}else{
					unset($oldid[$tagid]);
				}
			}
			if($oldid && is_array($oldid)){ //说明是以前存在但现已删除的Tag
				$oldid = implode(',',$oldid);
				$this->mysql->update("DELETE FROM cms_contenttag WHERE tid='$tid' AND tagid IN($oldid)");
				$this->mysql->update("UPDATE cms_tags SET num=num-1 WHERE tagid IN($oldid)");
			}
		}elseif ($action=='insert'){
			foreach ($tagsid as $tagid){
				$this->mysql->update("UPDATE cms_tags SET num=num+1 WHERE tagid='$tagid'");
				$this->mysql->update("INSERT INTO cms_contenttag SET tagid='$tagid',tid='$tid',mid='$this->mid'");
			}
		}
		require_once(R_P.'require/class_cache.php');
		Cache::writeCache('tags');
	}

	/**
	 * 对内容的附件资源进行分析
	 *
	 * @param array $aids
	 * @param integer $tid
	 * @param string $optype
	 */
	function AttachLink($aids,$tid,$optype){
		$aids = explode(',',$aids);
		$sqladd = '';
		if($optype=='insert'){
			foreach ($aids as $aid){
				if(empty($aid)) continue;
				$aid = intval($aid);
				$sqladd="INSERT INTO cms_attachindex (mid,tid,aid) VALUES($this->mid,$tid,$aid)";
				$this->mysql->update($sqladd);
			}
		}elseif ($optype=='update'){
			$rs = $this->mysql->query("SELECT aid FROM cms_attachindex WHERE tid='$tid'");
			$old_aid = $oldid =  array();
			while ($old_aid = $this->mysql->fetch_array($rs)) {
				$oldid[$old_aid['aid']] = $old_aid['aid'];
			}
			foreach ($aids as $aid){
				$aid = intval($aid);
				if(!in_array($aid,$oldid)){ //说明是新增加的附件
					$this->mysql->update("INSERT INTO cms_attachindex (mid,tid,aid) VALUES($this->mid,$tid,$aid)");
				}else{ //说明非新增附件
					unset($oldid[$aid]); //过去存在，现在亦存在，屏蔽之
					//unset 之后剩余的均为过去存在但现已不存在的附件
				}
			}

			if($oldid && is_array($oldid)){ //说明是以前存在但现已删除的附件
				$oldid = implode(',',$oldid);
				$this->mysql->update("DELETE FROM cms_attachindex WHERE tid='$tid' AND aid IN($oldid)");
			}
		}
	}

	/**
	 * 获取其关联附件
	 *
	 * @param integer $tid
	 * @return array
	 */
	function getAttach($tid){
		$tid = (int)$tid;
		$aids = array();
		$rs = $this->mysql->query("SELECT aid FROM cms_attachindex WHERE tid=$tid AND mid=$this->mid");
		while ($t = $this->mysql->fetch_array($rs)) {
			$aids[] = $t['aid'];
		}
		$aids = array_unique($aids);
		return $aids;
	}

	/**
	 * 获取一个主题的Tag信息以供编辑
	 *
	 * @param integer $tid
	 * @return string
	 */
	function getTags($tid){
		$rs = $this->mysql->query("SELECT * FROM cms_contenttag c LEFT JOIN cms_tags t USING(tagid) WHERE c.tid='$tid'");
		$tagsInfo = array();
		while ($t = $this->mysql->fetch_array($rs)) {
			if(empty($t['tagname'])) continue;
			$tagsInfo[] = $t['tagname'];
		}
		$tagsInfo = implode(' , ',$tagsInfo);
		return $tagsInfo;
	}
	/**
	 * 自动分页
	 *
	 * @param string  $text
	 * @param integer $spsize
	 * @param string  $sptag
	 * @return string
	 */
	function cutPage($text,$spsize,$sptag){
		$spsize = $spsize*1024;
		if(strlen($text)<$spsize) return $text;
		$bds = explode('<',$text);
		$npageBody = "";
		$istable = 0;
		$text = "";
		foreach($bds as $i=>$k){
			if($i==0){
				$npageBody .= $bds[$i]; 
				continue;
			}
			$bds[$i] = "<".$bds[$i];
			if(strlen($bds[$i])>6){
				$tname = substr($bds[$i],1,5);
				if(strtolower($tname)=='table'){
					$istable++;
				}else if(strtolower($tname)=='/tabl'){
					$istable--;
				}
				if($istable>0){
					$npageBody .= $bds[$i];
					continue;
				}else{
					$npageBody .= $bds[$i];
				}
			}else{
				$npageBody .= $bds[$i];
			}
			if(strlen($npageBody)>$spsize){
				$text .= $npageBody.$sptag;
				$npageBody = "";
			}
		}
		if($npageBody!="") $text .= $npageBody;
		return $text;
	}
}
?>