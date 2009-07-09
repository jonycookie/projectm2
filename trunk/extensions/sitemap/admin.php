<?php
defined('IN_EXT') or die('Forbidden');

require_once(D_P.'data/cache/cate.php');
InitGP(array('action','sitetype','sitecount'));
$sitemap = new Sitemap($mid);
$sitemap->sitecount = intval($sitecount);
$sitemap->doIt();

class Sitemap {
	/**
	 * 更新周期
	 */
	var $updateperi;

	/**
	 * 模型相对应的表
	 */
	var $table;

	/**
	 * 需要生成的模型ID
	 */
	var $mid;
	/**
	 * 需要生成的文章数量
	 */
	var $sitecount;
	/**
	 * 网站地址
	 */
	var $website;
	/**
	 * 网站管理员邮箱
	 */
	var $webadmin;
	/**
	 * 网站静态文件存放地址
	 */
	var $webhtmdir;
	/**
	 * 生成文件位置
	 */
	var $fileaddress;

	function __construct($mid)
	{ //PHP5
		$this->mid = intval($mid) ? intval($mid):1 ;
	}

	function Sitemap($mid){ //PHP4
		$this->__construct();
	}

	function doIt(){
		global $action,$sitetype;
		!$action && $action='show';
		switch ($action) {
			case 'show':
				$this->Show();
				break;
			case 'create':
				$this->createMap($sitetype);
			default:
				break;
		}
	}

	function Show() {
		global $db,$very,$sitetype,$basename;
		$mids = array();
		$query = $db->query("SELECT mid,mname FROM cms_module");
		while($rs = $db->fetch_array($query)) {
			$mids[] = $rs;
		}
		if($sitetype) {
			${$sitetype."_check"}="checked";
		}else {
			$google_check = "checked";
		}
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}
	function createMap($sitetype) {
		global $db,$very,$basename;
		if($this->sitecount && $this->sitecount != $very['sitecount']){
			$key = 'db_sitecount';
			$db->pw_update(
			"SELECT * FROM cms_config WHERE db_name='$key'",
			"UPDATE cms_config SET db_value='$this->sitecount' WHERE db_name='$key'",
			"INSERT INTO cms_config (db_name,db_value) VALUES('$key','$this->sitecount')"
			);
			require_once(R_P.'require/class_cache.php');
			$cache = new Cache();
			$cache->config();
		}
		$this->selectTable();
		$this->website = $very['url'];
		$this->webadmin = str_replace("mailto:","",$very['contact']);
		$this->webhtmdir = $very['htmdir'];
		if($sitetype=='google') {
			$this->fileaddress = "sitemap.xml";
			$this->googleSiteMap();
		}elseif($sitetype=='baidu') {
			$this->fileaddress = "sitemap_".$this->mid.".xml";
			$this->baiduSiteMap();
		}else {
			Showmsg('data_error');
		}
		if($very['aggrebbs']) {
			$bbsdb = new DB($very['bbs_dbhost'],$very['bbs_dbuser'],$very['bbs_dbpw'],$very['bbs_dbname'],$very['bbs_charset'],'0');
		}
		adminmsg("SiteMap：".$this->website."/".$this->fileaddress,$basename."&sitetype=".$sitetype);
	}
	function googleSiteMap(){
		global $db,$catedb;
		$limit = $this->sitecount ? $this->sitecount : 5000;
		$query = $db->query("SELECT cid,tid,url,postdate FROM cms_contentindex WHERE ifpub=1 AND cid>=1 ORDER BY postdate DESC LIMIT $limit");
		$showdb = array();
		while($rs = $db->fetch_array($query)){
			$cid = $rs['cid'];
			$tid = $rs['tid'];
			if($catedb[$cid]['htmlpub']){
				if(empty($rs['url'])){
					continue;
				}
				$url = $this->website."/".$this->webhtmdir."/".$rs['url'];
			}else{
				$url = $this->website."/view.php?tid=".$tid."&cid=".$cid;
			}
			$url = $this->replace_url($url);
			$lastmod = date('Y-m-d',$rs['postdate']);
			$showdb[]="<url>\r\n\t<loc>$url</loc>\r\n\t<lastmod>$lastmod</lastmod>\r\n</url>";
		}
		$show='<?xml version="1.0" encoding="UTF-8"?>'."\r\n";
		$show.='<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'."\r\n";
		$show.=implode("\r\n",$showdb);
		$show.="\r\n".'</urlset>'."\r\n";
		writeover(D_P."$this->fileaddress",$show);
	}

	function baiduSiteMap() {
		global $db,$catedb;
		$limit = $this->sitecount ? $this->sitecount:5000;
		$query = $db->query("SELECT i.title,i.cid,i.tid,i.url,i.postdate,i.photo,t.content,t.intro FROM cms_contentindex i LEFT JOIN $this->table t USING(tid) WHERE ifpub=1 ORDER BY postdate AND cid>=1 DESC LIMIT $limit");
		$showdb = array();
		while($rs = $db->fetch_array($query)){
			$cid = $rs['cid'];
			$tid = $rs['tid'];
			if($catedb[$cid]['htmlpub']){
				if(empty($rs['url'])){
					continue;
				}
				$url = $this->website."/".$this->webhtmdir."/".$rs['url'];
			}else{
				$url = $this->website."/view.php?tid=".$tid."&cid=".$cid;
			}
			$link = $this->replace_url($url);
			$pubdate = date('Y-m-d h:i',$rs['postdate']);
			$text = $this->strimhtml($rs['content']);
			$description = $rs['intro']?$this->strimhtml($rs['intro']):'';
			$image = $rs['photo']?$this->replace_url($rs['photo']):'';
			$title = $this->strimhtml($rs['title']);
			$showdb[]="<item>\r\n\t<title>$title</title>\r\n\t<link>$link</link>\r\n\t<description>$description</description>\r\n\t<text>$text</text>\r\n\t<image>$image</image>\r\n\t<headlineImg/>\r\n\t<pubDate>$pubdate</pubDate>\r\n</item>";
		}
		$show='<?xml version="1.0" encoding="GB2312"?>'."\r\n";
		$show.='<document>'."\r\n";
		$show.="<webSite>$this->website</webSite>"."\r\n";
		$show.="<webMaster>$this->webadmin</webMaster>"."\r\n";
		$show.=implode("\r\n",$showdb);
		$show.="\r\n".'</document>'."\r\n";
		writeover(D_P."$this->fileaddress",$show);
	}
	function selectTable(){
		$this->table='cms_content'.$this->mid;
	}
	function replace_url($url){
		$url=str_replace("&","&amp;",$url);
		$url=str_replace("'","&apos;",$url);
		$url=str_replace("\"","&quot;",$url);
		$url=str_replace(">","&gt;",$url);
		$url=str_replace("<","&lt;",$url);
		return $url;
	}
	function strimhtml($str) {
		$str = strip_tags($str);
		$str = str_replace("&nbsp;","",$str);
		$str = preg_replace( "'<script[^>]*>.*?</script>'si", "", $str );
		$str = preg_replace( "'<style[^>]*>.*?</style>'si", "", $str );
		$str = preg_replace( '/{.+?}/', '', $str);
		$str = $this->replace_url($str);
		return $str;
	}
}
?>