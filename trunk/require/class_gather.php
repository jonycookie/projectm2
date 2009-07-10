<?php
!defined('IN_ADMIN') && die('Forbidden');
require_once(R_P.'require/class_content.php');
require_once(R_P.'require/class_attach.php');
require_once(R_P.'require/chinese.php');
/**
 * 采集类
 * @copyright PHPWind
 * @author AileenGuan
 *
 */
class Gather{
	var $config;
	var $scheme,$host,$port,$path;
	var $data;
	var $fp;
	var $starttime;
	var $links;
	var $url;
	var $type;

	/**
	 * 三个计数器
	 */
	var $total;
	var $validNum;
	var $filtreitNum;

	/**
	 * 页面基本BaseHref
	 */
	var $baseHref;

	/**
	 * 替换规则
	 */
	var $replace_str1;
	var $replace_str2;

	/**
	 * 标志是否为测试模式
	 */
	var $testMod;
	var $chs;

	function __construct($testMod){ //PHP5 操作类型
		$this->data = '';
		$this->config = array();
		$this->validNum = 0;
		$this->filtreitNum = 0;
		$this->replace_str1 = array();
		$this->replace_str2 = array();
		$this->testMod = $testMod;
	}

	function Gather($testMod){
		$this->__construct($testMod);
	}

	function open($url,$conv=''){
		$this->starttime = microtime();
		$this->url	= $url;
		$this->data = '';
		$path		= parse_url($url);
		$this->host	= $path['host'];
		$this->port	= $path['port'];
		$this->path	= $path['path'];
		if($path['query']) $this->path .= "?".$path['query'];
		if(empty($this->port)){
			$this->port=80;
		}elseif ($path['scheme']=='https'){
			$this->port=443;
		}elseif ($path['scheme']=='http'){
			$this->port=80;
		}
		$this->scheme = $this->port==80 ? "http://" : "https://";
		if($this->type==1 || ini_get('allow_url_fopen')){
			$urlcontents = file_get_contents($url);
			if(strlen($urlcontents)>200){
				$this->data = $urlcontents;
				$this->type = 1;
			}
		}
		if(strlen($this->data)<200){
			$this->connect(); //开始连接
			$user_agent=$_SERVER['HTTP_USER_AGENT'];
			$http="GET $this->path HTTP/1.1\r\n";
			$http.="Host: $this->host:$this->port\r\n";
			$http.="Accept:*/*\r\nAccept-Encoding: identity\r\n";
			$http.="User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n\r\n";
			fwrite($this->fp,$http);
			$this->getUrlContent();
		}
		
		!$this->chs && $this->ConvertData();
		$conv && is_object($this->chs) && $this->data = $this->chs->Convert($this->data);
		$this->getBaseHref(); //获取Base Href
	}

	/**
	 * 自动识别页面中的Base Href标签
	 *
	 */
	function getBaseHref(){
		if(eregi("<base[[:blank:]]*(href)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}://(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%/?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\"]?[^>]*>",$this->data,$reg)){
			$reg[2] && $this->baseHref = $reg[2];
		}else {
			$this->baseHref = $this->scheme.$this->host;
		}
	}

	/**
	 * 计时器
	 *
	 */
	function startCount(){
		$this->starttime = microtime();
	}

	/**
	 * 配置信息
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	function _set($name,$value){
		$this->config[$name]=$value;
	}

	/**
	 * 连接主机
	 *
	 */
	function connect(){
		$this->config['errorno'] = '';
		$this->config['errornum'] = 0;
		$this->fp=@fsockopen($this->host,$this->port,$this->config['errorno'],$this->config['errornum'],$this->config['timeout']);

		if(!$this->fp){
			$this->error('Can not connect the server');
		}
	}

	/**
	 * 获取网址中的页面内容
	 *
	 */
	function getUrlContent(){
		$status = socket_get_status($this->fp);
		while (!feof($this->fp) && !$status['timed_out']){
			$this->data .= fread($this->fp,8192);
		}
		/*
		if ($status['timed_out'] == 1) {
			$contents['state'] = "timeout";
		} else
			$contents['state'] = "ok";
		*/
		fclose($this->fp);
	}

	/**
	 * 获取到所有有效的内容页网址
	 *
	 * @param string $listArea 有效内容网址区域
	 * @param string $contenturl 必须包含的有效内容url
	 * @param string $debarurl 要排除的url部分
	 */
	function getLinks($listArea,$contenturl,$debarurl){
		$links = array();
		$this->data = $this->getData($listArea); //首先获取到有效区域
		$allLinks = $this->getAllLinks($this->data); //继而获取到区域中所有链接
		$valid = explode('|',$contenturl); //有效块
		$invalid = explode('|',$debarurl); //无效块
		foreach ($allLinks as $link){ //循环所有链接来获取到有效的所需内容页链接
			if(empty($link)) continue;
			$errorno = 0; //错误计数器
			foreach ($invalid as $i){
				if(empty($i)) continue;
				//if(strpos($link,$i)){ //一旦查到了一个不能包含的部分，出错
				if(preg_match("/".$i."/i",$link)){//一旦查到了一个不能包含的部分，出错
					$errorno++;
					break;
				}
			}
			if($errorno>0) continue;
			foreach ($valid as $v){
				if(empty($v)) continue;
				//if(strpos($link,$v)===false){ //一旦有一次没有查到必须包含的部分，出错
				if(preg_match("/".$v."/i",$link)==false){//一旦有一次没有查到必须包含的部分，出错
					$errorno++;
					break;
				}
			}
			if($errorno>0) continue; //出现一次错误，则证明此link无效
			$this->links[] = $link;
		}
		$this->links = array_unique($this->links); //移除重复值
		$this->testMod && $this->links=array(array_shift($this->links));
	}

	/**
	 * 获取到一段内容中的所有链接
	 *
	 * @param string $data
	 * @return array
	 */
	function getAllLinks($data){
		if(!$data) {
			return false;
		}
		$chunklist = array ();
		$chunklist = explode("\n", $data);
		$links = array ();
		$regs = Array ();
		while(list ($id, $chunk) = each($chunklist)){
			if (strstr(strtolower($chunk), "href")){
				while (preg_match("/(href)\s*=\s*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?(\s*rel\s*=\s*[\'\"]?(nofollow)[\'\"]?)?/i", $chunk, $regs)) {
					if(!isset ($regs[10])){
						$links[] = $this->realUrl($regs[2]);
					}
				$chunk = str_replace($regs[0], "", $chunk);
				}
			}
			if (strstr(strtolower($chunk), "frame") && strstr(strtolower($chunk), "src")){
				while (preg_match("/(frame[^>]*src[[:blank:]]*)=[[:blank:]]*['\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?['\" ]?/is", $chunk, $regs)) {
					$links[] = $this->realUrl($regs[2]);
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}

			if (strstr(strtolower($chunk), "window") && strstr(strtolower($chunk), "location")) {
				while (preg_match("/(window[.]location)[[:blank:]]*=[[:blank:]]*['\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?['\" ]?/is", $chunk, $regs)) {
					$links[] = $this->readUrl($regs[2]);
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}

			if (strstr(strtolower($chunk), "http-equiv")) {
				while (preg_match("/(http-equiv=['\"]refresh['\"] *content=['\"][0-9]+;url)[[:blank:]]*=[[:blank:]]*['\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?['\" ]?/is", $chunk, $regs)) {
					$links[] = $this->realUrl($regs[2]);
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}

			if (strstr(strtolower($chunk), "window") && strstr(strtolower($chunk), "open")) {
				while (preg_match("/(window[.]open[[:blank:]]*[(])[[:blank:]]*['\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?['\" ]?/is", $chunk, $regs)) {
					$links[] = $this->realUrl($regs[2]);
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}
		}
		return $links;
	}

	/**
	 * 根据字段的规则来获取该字段所需要的采集内容
	 *
	 * @param string $Reg 字段规则
	 * @return string 有效内容
	 */
	function getData($Reg,$fieldid=''){ //获取数据
		$clearrubbish = $imgtolocal = 0;
		if($pos = strpos($Reg,'{DATA}')){
			$start	= substr($Reg,0,$pos);
			$end	= substr($Reg,$pos+6);
			if($start && $end){
				$startpos = strpos($this->data,$start);
				if($startpos===false) {
					return false;
				}
				$startpos+=strlen($start);
				$endpos = strpos($this->data,$end,$startpos);
				if($endpos===false) {
					return false;
				}
				$length = $endpos - $startpos;
				$value	= substr($this->data,$startpos,$length);
				if($fieldid){
					if($this->config['ifclearhtml'][$fieldid]){ //清除Html标签
						$tags = '';
						foreach ($this->config['clearhtml'][$fieldid] as $tag){
							if($tag=='script') {
								$script = 1;
							}elseif($tag=='img') {
								$img	= 1;
							}
							if($tag!='&nbsp;'){
								$tag='<'.$tag.'>';
							}
							$tags .= $tag;
						}
						if(!$script) {
							$value = preg_replace("/<script[^>]*?>.*?<\/script>/si",'',$value);
						}
						if($img) {
							$value = $this->imageRealUrl($value);
						}
						$value = strip_tags($value,$tags); //过滤掉HTML标签
					}
					if ($this->config['imgtolocal'][$fieldid]) { //图片本地化
						$value = $this->imageToLocal($value);
					}
				}
				return $value;
			}else{
				return null;
			}
		}else{
			return $Reg;
		}
	}

	function imageRealUrl($data) {
		$chunklist = array ();
		$chunklist = explode("\n", $data);
		$links	= array ();
		$regs	= array ();
		$source = array();
		$i = 0;
		while(list ($id, $chunk) = each($chunklist)){
			if (strstr(strtolower($chunk), "img") && strstr(strtolower($chunk), "src")){
				while (preg_match("/(src)\s*=\s*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?~=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?(\s*rel\s*=\s*[\'\"]?(nofollow)[\'\"]?)?/is", $chunk, $regs)) {
					if($regs[2] && strpos($regs[2],'http://')===false){
						$i++;
						$source[$i]		= $regs[2];
						$imglinks[$i]	= $this->realUrl($regs[2]);
					}
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}
		}
		if(!count($source)) {
			return $data;
		}
		return str_replace($source,$imglinks,$data); //再把内容中图片地址更换成对应的本地图片地址
	}

	/**
	 * 图片本地化
	 *
	 * @param string $data
	 * @return string 本地化之后的内容
	 */
	function imageToLocal($data){
		global $very;
		$chunklist = array ();
		$chunklist = explode("\n", $data);
		$links = array ();
		$regs = array ();
		$source = array();
		$i = 0;
		while(list ($id, $chunk) = each($chunklist)){
			if (strstr(strtolower($chunk), "img") && strstr(strtolower($chunk), "src")){
				while (preg_match("/(img[^>]*src[[:blank:]]*)=[[:blank:]]*[\'\"]?(([[a-z]{3,5}:\/\/(([.a-zA-Z0-9-])+(:[0-9]+)*))*([+:%\/\?=&;\\\(\),._a-zA-Z0-9-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?/is", $chunk, $regs)) {
					if($regs[2]){
						$i++;
						$source[$i] = $regs[2];
						$imglinks[$i] = $this->realUrl($regs[2]);
					}
					$chunk = str_replace($regs[0], "", $chunk);
				}
			}
		}
		$newImg = array();
		$savedir = Attach::saveDir('image');
		foreach ($imglinks as $key=>$imgsrc){
			$file_ext = strtolower(end(explode('.',$imgsrc)));
			if(!in_array($file_ext,array('jpg','jpeg','png','gif'))) $file_ext='jpg';
			 //如果不是指定格式，则强制格式，防止本地化可能带来的安全问题
			$imgname = substr(md5($imgsrc),10,10).'.'.$file_ext;
			$newImgSrc = $very['attachdir'].'/'.$savedir.'/'.$nameadd.$imgname;
			$TargetImg = D_P.$newImgSrc;
			if(!file_exists($TargetImg))copy($imgsrc,$TargetImg);
			$newImg[$key] = $newImgSrc;
		}
		return str_replace($source,$newImg,$data); //再把内容中图片地址更换成对应的本地图片地址
	}

	/**
	 * 来返回程序执行所耗服务器端时间
	 *
	 * @return float
	 */
	function countTime(){
		list($start_m,$start_s) = explode(" ",$this->starttime);
		list($end_m,$end_s)=explode(" ",microtime());
		$seconds =  ($end_m-$start_m)+($end_s-$start_s);
		return $seconds;
	}

	/**
	 * 将获取到的链接网址保存到一个临时文件中
	 *
	 */
	function saveLinks(){
		$cache='';
		foreach ($this->links as $url){
			$realurl = $this->realUrl($url);
			$cache.=$realurl."\n";
		}
		if($this->config['tmpfile']==''){
			$this->config['tmpfile'] = time();
		}
		$this->config['tmpfile'] = intval($this->config['tmpfile']);
		writeover(D_P.'data/cache/col_'.$this->config['tmpfile'].'.txt',$cache,'ab+');
	}

	/**
	 * 从临时文件中读取列表页
	 *
	 * @param integer $readnum 读取的网址数量
	 */
	function readUrl($readnum){
		$str =readover(D_P.'data/cache/col_'.$this->config['tmpfile'].'.txt');
		$url_array = explode("\n",$str);
		array_pop($url_array);
		$readnum<=0 && $readnum=10;
		$this->config['total'] = ceil(count($url_array)/$readnum);
		!$this->config['step'] && $this->config['step']=1;
		$start = ($this->config['step']-1)*$readnum;
		$url_array = array_slice($url_array,$start,$readnum);
		$this->links = $url_array;
	}

	/**
	 * 根据字段规则来获取所需要的采集内容
	 *
	 * @param string $fieldrule
	 */
	function getContent($fieldrule){
		global $db,$very;
		$Content = new Content($this->config['mid']);
		$this->validNum=0;
		$fieldrule = unserialize($fieldrule);
		$i = 0;
		foreach ($this->links as $url){
			if(strpos($url,'http') === false) continue;
			$md5Url=substr(md5($url),10,10);
			$filtreitFlag=false;
			if(empty($this->testMod) && $this->config['filtreit']){ //如果开启了过滤
				$rs = $db->get_one("SELECT * FROM cms_collection WHERE md5url='$md5Url'");
				if($rs){
					if($this->config['ignoretime'] && $GLOBALS['timestamp'] - $rs['gathertime'] > $this->config['ignoretime']*3600*24 ){ //设置了忽略时间//超过设置的时间，仍然进行采集
						$filtreitFlag=true;
					}else{
						$this->filtreitNum++; //过滤数+1
						continue;
					}
				}
			}

			$multi = 0;
			$this->open($url,1);
			if(empty($this->data)){
				continue; //获取网址内容失败
			}

			if($this->config['pageurl']){
				$contentPageUrl	= $this->fpageUrl();
				if(count($contentPageUrl)){
					$multi = 1; //分页内容采集
				}
			}
			$fieldvalue=array();
			$error	=	0; //错误计数器
			foreach ($fieldrule as $key=>$value){
				$value = stripslashes($value);
				$fieldvalue[$key] = $this->getData($value,$key);
				if(empty($fieldvalue[$key])){
					$error++;
					break;
				}
				$key!='content' && $this->strReplace($fieldvalue[$key]); //字段替换
			}
			$this->close();

			if($error>0){
				continue; //此采集失败，继续下一项
			}

			if ($multi==1){
				$basePage = $contentPageUrl;
				if(!in_array($this->url,$basePage)) {
					array_push($basePage,$this->url);
				}
				while($pageUrl = array_shift($contentPageUrl)) {
					if(empty($pageUrl)){
						continue;
					}
					$this->close();
					$this->open($pageUrl,1); //开始采集其多页内容
					if(empty($this->data)){
						continue; //获取网址内容失败
					}
					$pageContent = $this->getData(stripslashes($fieldrule['content']),'content');
					if(empty($pageContent)) {
						break;
					}
					$fieldvalue['content'].='<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>'.$pageContent;
					$otherPage = $this->fpageUrl();
					if(count($otherPage)) {
						$otherPage = array_diff($otherPage,$basePage);
					}
					if(count($otherPage) && count($contentPageUrl)) {
						foreach($otherPage as $val) {
							array_push($basePage,$val);
							array_push($contentPageUrl,$val);
						}
					}
				}
				$this->close();
			}

			$this->strReplace($fieldvalue['content']);
			$fieldvalue['intro'] = substrs($fieldvalue['content'],200,1);
			$fieldvalue['tagsid'] = $this->config['tagsid'];
			Add_S($fieldvalue);
			if($this->config['bindcid']){
				$fieldvalue['postdate'] = time();
				$Content->InsertData($fieldvalue,$this->config['bindcid']);
			}else{
				$Content->InsertData($fieldvalue,0);
			}
			$i++;
			$nowtime = time()+$very['cvtime']*60+$i;
			if($filtreitFlag){
				$db->update("UPDATE cms_collection SET gathertime='$nowtime' WHERE md5url='$md5Url'");
			}else{
				$id = $Content->insertId;
				$db->update("INSERT INTO cms_collection SET url='$url',md5url='$md5Url',gathertime='$nowtime',tid='$id',gid='$GLOBALS[gid]'");
			}
			$this->validNum++;
			$this->close();
			$i++;
		}
	}

	function XMLCmp($rule,$data,$fieldrule,&$vector){
		$ruledata = $rule->GetData();
		if($ruledata && in_array($ruledata,$fieldrule)){
			!is_array($vector[$ruledata]) && $vector[$ruledata] = array();
			array_push($vector[$ruledata],$this->chs->Convert($data->GetData()));
		}
		foreach($rule->GetAttr() as $name=>$value){
			if($value && in_array($value,$fieldrule)){
				!is_array($vector[$value]) && $vector[$value] = array();
				array_push($vector[$value],$this->chs->Convert($data->GetProperty($name)));
			}
		}
		$child = $rule->GetChild();
		foreach($child as $ruleC){
			$array = $data->GetChildByTagName($ruleC->GetTagName());
			foreach($array as $dataC){
				$this->XMLCmp($ruleC,$dataC,$fieldrule,$vector);
			}
		}
		return null;
	}

	function getXMLData($fieldrule){
		global $db;
		require_once(R_P.'require/class_xml.php');
		$Content = new Content($this->config['mid']);
		$this->validNum = 0;
		$rule = $fieldrule['_XMLrule'];
		unset($fieldrule['_XMLrule']);
		$gatXML = new XMLDoc();
		$gatXML->SetXMLData($rule);
		$gatXML->parse();
		$XMLDoc = new XMLDoc();
		$XMLDoc->SetXMLData($this->data);
		$XMLDoc->parse();
		$vector = array();
		$this->XMLCmp($gatXML->GetDocumentElement(),$XMLDoc->GetDocumentElement(),$fieldrule,$vector);
		$total = count($vector[current($fieldrule)]);
		for($i=0;$i<$total;$i++){
			$fieldvalue=array();
			$error = 0;
			foreach ($fieldrule as $key=>$value){
				$fieldvalue[$key] = strip_tags($vector[$value][$i]);
			}
			Add_S($fieldvalue);
			$Content->InsertData($fieldvalue,0);
			$id = $Content->insertId;
			$db->update("INSERT INTO cms_collection SET gathertime='$GLOBALS[timestamp]',tid='$id',gid='$GLOBALS[gid]'");

			$this->validNum++;
		}
		unset($vector);
	}

	/**
	 * 对内容页的自动分页进行识别
	 *
	 * @return array
	 */
	function fpageUrl(){
		$pageArea	= $this->getData(stripslashes($this->config['pageurl']));
		if(!$pageArea) return false;
		$pageUrl	= $this->getAllLinks($pageArea);
		if(!count($pageUrl)) {
			return false;
		}
		$validUrl	= array();
		foreach ($pageUrl as $url){
			if(empty($url) || $url=='#' || $url==$this->url) continue;
			$validUrl[]	= $this->realUrl($url);
		}
		$validUrl = array_unique($validUrl);
		return $validUrl;
	}

	function replace($array1,$array2){
		foreach ($array1 as $key=>$val){
			if(empty($val)) continue;
			$this->replace_str1[$key] = stripslashes($val);
		}
		foreach ($array2 as $key=>$val){
			if(!$this->replace_str1[$key]) continue;
			$this->replace_str2[$key] = stripslashes($val);
		}
	}

	/**
	 * 关键字替换
	 *
	 * @param  string $string
	 * @return string 替换完成的字段值
	 */
	function strReplace(&$string){
		if(empty($this->replace_str1)) return ;
		$string = str_replace($this->replace_str1,$this->replace_str2,$string);
	}

	/**
	 * 关闭操作句柄
	 *
	 */
	function close(){
		@fclose($this->fp);
		$this->data='';
	}

	/**
	 * 返回采集的状态传递给Ajax返回函数
	 *
	 */
	function returnStat(){
		$seconds = $this->countTime();
		$seconds = round($seconds,2);
		$stat = $this->config['step'] >= $this->config['total'] ? 'complete' : 'continue';
		if($this->config['action']=='list'){
			$this->testMod && $stat='complete';
			$linksNum = count($this->links);
			echo "$stat|$linksNum|$seconds|".$this->config['tmpfile'];
		}elseif ($this->config['action']=='content'){
			$this->config['tmpfile'] = intval($this->config['tmpfile']);
			$stat =='complete' && unlink(D_P.'data/cache/col_'.$this->config['tmpfile'].'.txt');
			echo "$stat|$this->validNum|$this->filtreitNum|$seconds";
		}
		exit();
	}


	/**
	 * 根据一个采集页中获取到的相对或绝对的地址来判断地址的完整url
	 *
	 * @param string $url
	 * @return string
	 * @author AileenGuan
	 */
	function realUrl($url){
		if($this->baseHref){
			$urlPre = $this->baseHref;
		}else{
			$urlPre = $this->scheme.$this->host;
		}
		if(preg_match("/http:|https:/i",$url)){ //网址
			return $url;
		}elseif(preg_match("/^\//",$url)){ //斜杠开头
			$realurl = $this->scheme.$this->host.$url;
			return $realurl;
		}elseif (preg_match("/^[\.]{2}\//",$url)){ // ../开头 表示上级目录
			$up_num = substr_count($url,"../");
			$path_array = explode("/",$this->path);
			array_pop($path_array);
			for ($i=0;$i<$up_num;$i++){
				array_pop($path_array);
			}
			$url = str_replace("../","",$url);
			$path_array[]=$url;
			$url = implode("/",$path_array);
			$realurl = $urlPre.$url;
			return $realurl;
		}else{
			$path = explode("/",$this->path);
			$filename = array_pop($path);
			$currentdir = substr($this->path,0,-strlen($filename));
			return $this->scheme.$this->host.$currentdir.$url;
		}
	}

	/**
	 * 抛出错误信息
	 *
	 * @param string $msg
	 */
	function error($msg){
		$errmsg="<div style='font-size:12px;font-family:verdana;line-height:180%;color:#000;border:dashed 1px #ccc;padding:10px;margin:20px;'>";
		$errmsg.="<span style='color:red;font-weight:bold'>Error: </span>";
		$errmsg.=$msg;
		$errmsg.="</div>";
		die($errmsg);
	}

	/**
	 * 转换采集页面的编码
	 *
	 */
	function ConvertData(){
		global $charset;
		if(preg_match("/\bcharset=\b([0-9a-zA-Z\-]+)/i",$this->data,$chr)){
			if(substr($chr['1'],0,2) != substr($charset,0,2)){
				$this->chs = new Chinese($chr['1'],$charset);
			}

		}elseif(strtolower($charset) != "utf-8"){
			$this->chs = new Chinese("utf-8",$charset);
		}
	}
}
?>