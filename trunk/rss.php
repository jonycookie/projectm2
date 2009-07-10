<?php
require_once('global.php');
require_once(R_P.'require/class_cms.php');
require_once(R_P.'require/chinese.php');
/**
 * RSS生成类，为每一个栏目生成内容摘要
 *
 */

class Rss extends Cms {
	/**
	 * xml文件的开头部分
	 *
	 * @var string
	 */
	var $XML_pre;
	
	/**
	 * xml文件的结尾部分
	 *
	 * @var string
	 */
	var $XML_end;
	
	/**
	 * 频道的基本信息
	 *
	 * @var string
	 */
	var $ChannelInfo;
	
	/**
	 * 配置信息
	 *
	 * @var array
	 */
	var $config;
	/**
	 * 频道Cid
	 *
	 * @var integer
	 */
	var $channelId;
	
	/**
	 * 项目内容
	 *
	 * @var string
	 */
	var $itemInfo;
	
	/**
	 * 图片项
	 *
	 * @var string
	 */
	var $imageInfo;
	
	/**
	 * xml要进行特殊转换的字符
	 *
	 * @var array
	 */
	var $str1;
	
	/**
	 * xml特殊转换字符的对应转换
	 *
	 * @var unknown_type
	 */
	var $str2;

	
	/**
	 * 构造函数，来创建一个Rss输出文件的开始部分
	 *
	 * @param string $encoding 文件编码
	 */
	function ShowRSS($cid)
	{
		global $very;
		$cid = intval($cid);
		$this->channelId = $cid;
		$this->config = $very;
		if($this->config['lang'] == 'gbk') {
			$this->config['encode'] = 'gb2312';
		}elseif($this->config['lang'] == 'utf8') {
			$this->config['encode'] = 'utf-8';
		}elseif($this->config['lang'] == 'big5') {
			$this->config['encode'] = 'big5';
		}
		
		
		
		$this->str1 = array(
			'&',
			'\'',
			'"',
			'>',
			'<'
		);
		$this->str2 = array(
			'&amp;',
			'&apos;',
			'&quot;',
			'&gt;',
			'&lt;',
		);
		$cacheFile = D_P.'data/rss/'.$this->channelId.'.php';
		if(time() - filemtime($cacheFile) > $this->config['rss_update']*60){ //如果大于指定时间
			$this->Channel();
			$this->config['rss_itemnum'] && $this->Item();
			$this->config['rss_imagenum'] && $this->Image();
			$this->writeXML($cacheFile);
		}
		header("Content-type: application/xml");
		@readfile($cacheFile);
		exit();
	}
	
	function Channel(){
		global $timestamp;
		$cateinfo = $this->catedb[$this->channelId];
		$this->XML_pre="<?xml version=\"1.0\" encoding=\"".$this->config['encode']."\"?>\r\n";
  		$this->XML_pre.= "<rss version=\"2.0\">\r\n";
  		$this->toamp($cateinfo);
		$this->ChannelInfo = "<channel>\r\n";
		$this->ChannelInfo .= "<title><![CDATA[".$cateinfo['cname']."]]></title>\r\n";
		
		if($cateinfo['listpub'] && $cateinfo['listurl']){
			$channelUrl = $this->config['url'].'/'.$cateinfo['listurl'];
		}else{
			$channelUrl = $this->config['url'].'/list.php?cid='.$this->channelId;
		}
//		$channelUrl = str_replace("&","&amp;",$channelUrl);
		$this->ChannelInfo .= "<link>".$channelUrl."</link>\r\n";
		$this->ChannelInfo .= "<description><![CDATA[".$cateinfo['description']."]]></description>\r\n";
		$this->ChannelInfo .= "<copyright><![CDATA[".$this->config['title']."]]></copyright>\r\n";
		$this->ChannelInfo .= "<generator>VeryCMS Powered By PHPWind </generator>\r\n";
		$this->ChannelInfo .="<pubDate>".date('r',$timestamp)."</pubDate>\r\n";
		$this->XML_end = "</channel>\r\n</rss>";
	}
	
	function Image(){
		$mid = $this->catedb[$this->channelId]['mid'];
		$Query = "num:".$this->config['rss_imagenum'].";cid:all-".$this->channelId.";mid:$mid;where:photo!='';";
		$imageResult = $this->thread($Query);
		$this->toamp($imageResult);
		$this->imageInfo = '';
		foreach ($imageResult as $val){
			$this->imageInfo.= "<image>\n";
			$this->imageInfo.= "<url>".$val['photo']."</url>\n";
			$this->imageInfo.= "<title>".$val['title']."</title>\n";
			$val['url'] = strpos($val['url'],'http://')===false ? $this->config['url'].'/'.$val['url'] : $val['url'];
			$imageLink =  str_replace("&","&amp;",$val['url']);
			$this->imageInfo.= "<link>".$imageLink."</link>\n";
			$this->imageInfo.= "</image>";			
		}
	}
	
	function Item(){
		$mid = $this->catedb[$this->channelId]['mid'];
		$Query = "num:".$this->config['rss_itemnum'].";cid:all-".$this->channelId.";mid:$mid;where:photo=''";
		$itemResult = $this->thread($Query); //传递参数给基类获取需要信息
		$this->itemInfo = '';
		$this->toamp($itemResult);
		foreach ($itemResult as $val){
			$this->itemInfo.= "<item>\r\n";
			$this->itemInfo.= "<title><![CDATA[".$val['title']."]]></title>\r\n";
			$this->itemInfo.= "<description><![CDATA[".substrs($val['content'],250)."]]></description>\r\n";
			$val['url'] = strpos($val['url'],'http://')===false ? $this->config['url'].'/'.$val['url'] : $val['url'];
//			$itemUrl =  str_replace("&","&amp;",$val['url']);
			$this->itemInfo.= "<link>".$val['url']."</link>\r\n";
			$this->itemInfo.= "<pubDate>".get_date($val['postdate'])."</pubDate>\r\n";
			$this->itemInfo.= "</item>\r\n";
		}
	}
	
	
	function writeXML($cacheFile){
//		$cacheContent = $this->XML_pre.$this->toutf8($this->ChannelInfo).$this->toutf8($this->imageInfo).$this->toutf8($this->itemInfo).$this->XML_end;
		$cacheContent = $this->XML_pre.$this->ChannelInfo.$this->imageInfo.$this->itemInfo.$this->XML_end;
		writeover($cacheFile,$cacheContent);
	}
	
	/**
	 * 将输出的内容转换为utf-8格式
	 *
	 * @param string $str
	 * @return string
	 */
	function toutf8($str){
		global $charset;
		if($charset != 'utf8'){
			$chs = new Chinese($charset,'UTF8');
			$str=$chs->Convert($str);
		}
		return $str;
	}
	
	function toamp(&$array){
		foreach ($array as $key=>$val){
			$array[$key] = str_replace("&","&amp;",$val);
		}
	}
}

$rss = new Rss();
$rss->ShowRSS($cid);
?>