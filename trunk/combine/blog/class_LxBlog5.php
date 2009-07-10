<?php
!defined('IN_CMS') && die('Forbidden');
require_once(R_P.'require/class_blog.php');
/**
 * Blog整合模型
 *
 */
class LxBlog5 extends Blog{
	/**
	 * 读取用户信息
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function user($num,$order='',$cid=0){
		$userinfo = array();
		$num	= intval($num);
		$order	= addslashes($order);
		!$order && $order = 'blogs';
		$cachefile = 'blog_user_'.$num.$order;
		if($userinfo = $this->readcache($cachefile)){
			return $userinfo;
		}
		$rs = $this->mysql->query("SELECT username,uid,icon,$order AS value FROM {$this->config['dbpre']}user ORDER BY $order DESC LIMIT $num");
		while ($userdb = $this->mysql->fetch_array($rs)) {
			$userdb['title']= strip_tags($userdb['username']);
			$userdb['url']	= $this->config['url']."/blog.php?uid=".$userdb['uid'];
			$userdb['value']= $userdb[$order];
			$userdb['icon'] = $this->showfacedesign($userdb['icon']);
			$userinfo[] = $userdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($userinfo));
		return $userinfo;
	}

	/**
	 * 读取TAG信息
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function tags($num,$order='',$cid=0){
		$tagsinfo = array();
		$num	= intval($num);
		$order	= addslashes($order);
		!$order && $order = 'allnum';
		$cachefile = 'blog_tags_'.$num.$order;
		if($tagsinfo = $this->readcache($cachefile)){
			return $tagsinfo;
		}
		$rs = $this->mysql->query("SELECT tagid,tagname,$order AS value FROM {$this->config['dbpre']}tags ORDER BY $order DESC LIMIT $num");
		while ($tagsdb = $this->mysql->fetch_array($rs)) {
			$tagsdb['title'] = strip_tags($tagsdb['tagname']);
			$tagsdb['url'] = $this->config['url']."/tags.php?tagname=".$tagsdb['tagname'];
			$tagsdb['value'] = $tagsdb[$order];
			$tagsinfo[] = $tagsdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($tagsinfo));
		return $tagsinfo;
	}

	/**
	 * 读取图片附件
	 *
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function image($num,$order='',$cid=0){
		$imagesinfo = array();
		$sqladd	= "";
		$num	= intval($num);
		$cid	= intval($cid);
		$order	= addslashes($order);
		$cachefile = 'blog_image_'.$num.$order;
		if($imagesinfo = $this->readcache($cachefile)){
			return $imagesinfo;
		}
		if($cid) {
			$sqladd .= "AND i.cid='$cid'";
		}
		//!$order && $order = 'allnum';
		$imageOrder = array('hits','replies','digest');
		if($order && in_array($order,$imageOrder)){
			$order	= "ORDER BY i.$order DESC";
		}else{
			$order	= "ORDER BY postdate DESC";
		}
		$rs = $this->mysql->query("SELECT i.*,u.attachurl FROM {$this->config['dbpre']}items i LEFT JOIN  {$this->config['dbpre']}upload u USING(itemid) WHERE i.uploads!='' AND i.ifhide=0 AND u.type='img' $sqladd $order LIMIT $num");
		while ($imagesdb = $this->mysql->fetch_array($rs)) {
			$imagesdb['title'] = strip_tags($imagesdb['subject']);
			$imagesdb['photo'] = $this->config['attachurl'].'/'.$imagesdb['attachurl'];
			$imagesdb['url'] = $this->config['url']."/blog.php?do=showone&uid=".$imagesdb['uid']."&type=".$imagesdb['type']."&itemid=".$imagesdb['itemid'];
			$imagesdb['value'] = $imagesdb[$order];
			$imagesinfo[] = $imagesdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($imagesinfo));
		return $imagesinfo;
	}

	/**
	 * blog公告
	 *
	 * @return string
	 */
	function notice($num,$order='',$cid=0){
		global $timestamp;
		$num		= intval($num);
		$order		= addslashes($order);
		$noticeInfo = array();
		$cachefile	= 'blog_links_'.$num.$order;
		if ($noticeInfo = $this->readcache($cachefile)) {
			return $noticeInfo;
		}
		$order = $order ? $order : "vieworder,startdate";

		$rs = $this->mysql->query("SELECT aid as id,vieworder,author,startdate,url,subject,content FROM {$this->config['dbpre']}notice WHERE ORDER BY $order DESC LIMIT $num");

		while($rt = $this->mysql->fetch_array($rs)){
			$noticedb['title']	= strip_tags($rt['subject']);
			$noticedb['content']= $rt['content'];
			$noticedb['author'] = $rt['author'];
			$noticedb['url']	= $rt['url'] ? $rt['url'] : $this->config['url']."notice.php#".$rt['id'];
			$noticedb['url']	= eregi('^(http://)',$noticedb['url']) ? $noticedb['url'] : $this->config['url'].'/'.$noticedb['url'];
			$noticeInfo[]		= $noticedb;
		}

		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($noticeInfo));
		return $noticeInfo;
	}

	/**
	 * blog友情链接
	 *
	 * @return string
	 */
	function links($num,$order='',$cid=0){
		$num		= intval($num);
		$order		= addslashes($order);
		$linksInfo	= array();
		$cachefile	= 'blog_links_'.$num.$order;
		if ($linksInfo = $this->readcache($cachefile)) {
			return $linksInfo;
		}
		$rs = $this->mysql->query("SELECT name,url,descrip,logo FROM {$this->config['dbpre']}share WHERE ifcheck=1 ORDER BY threadorder LIMIT $num");
		while($rt = $this->mysql->fetch_array($rs)){
			$linksdb['title']	= strip_tags($rt['name']);
			$linksdb['descrip'] = $rt['descrip'];
			$linksdb['url']		= $rt['url'];
			$linksdb['photo']	= eregi('^(http://)',$rt['logo']) ? $rt['logo'] : $this->config['url'].'/'.$rt['logo'];
			if($linksdb['photo']){
				$linksdb['type'] = 1;
			}else{
				$linksdb['type'] = 0;
			}
			$linksInfo[] = $linksdb;
		}
		$this->mysql->free_result($rs);
		$this->writecache($cachefile,serialize($linksInfo));
		return $linksInfo;
	}

	/**
	 * 读取blog日志、音乐、相册信息
	 *
	 * @param integer $start
	 * @param integer $num
	 * @param string $type
	 * @param string $order
	 * @return array
	 */
	function getBlogs($start,$num,$cid=0,$type='blog',$order=''){
		global $catedb,$upTids;
		$sqladd	= $where = "";
		$cid	= intval($cid);
		if($cid) {
			$sqladd .= "AND i.cid='$cid'";
			$where  .= "WHERE i.cid='$cid'";
		}
		!in_array($type,array('photo','music','blog')) && $type = 'blog';
		$this->totalQuery = "SELECT COUNT(*) AS total FROM {$this->config['dbpre']}items i LEFT JOIN {$this->config['dbpre']}$type t USING(itemid) $where";
		$order = addslashes($order);
		!$order && $order = 'postdate';
		!$start && $start = 0;
		!$num	&& $num =30;
		$limit = $start.','.$num;
		$where = "WHERE i.type='$type' AND i.ifhide=0";
		$this->sqladd && $where .= $this->sqladd;
		$htm_ext	= $GLOBALS['sys']['htmext'] ? $GLOBALS['sys']['htmext'] : 'html';

		if($this->onlyimg) {
			$rs = $this->mysql->query("SELECT * FROM {$this->config['dbpre']}upload u LEFT JOIN {$this->config['dbpre']}items i USING(itemid) LEFT JOIN {$this->config['dbpre']}$type USING(itemid) $where AND u.type='img' $sqladd ORDER BY i.$order DESC  LIMIT $limit");
		}else {
			$rs = $this->mysql->query("SELECT * FROM {$this->config['dbpre']}$type t LEFT JOIN {$this->config['dbpre']}items i USING(itemid) $where $sqladd ORDER BY i.$order DESC  LIMIT $limit");
		}

		$bl = array();
		while ($bldb = $this->mysql->fetch_array($rs)){
			$bldb['tid']	= $bldb['itemid'];
			$bldb['title']	= $bldb['subject'];
			$bldb['publisher']	= $bldb['author'];
			if($this->viewtype){
				if($catedb[$this->viewtype]['htmlpub']){
					$bldb['url'] = $GLOBALS['sys']['htmdir'].'/'.$this->viewtype.'/'.$bldb['tid'].'.'.$htm_ext;
					if(!file_exists($bldb['url'])){
						$bldb['ifpub'] = 0;
						if($catedb[$this->viewtype]['autopub']){
							$upTids .= $upTids ? '|'.$bldb['tid'] : $bldb['tid'];
							$bldb['ifpub'] = 2;
						}
						$bldb['url']		= $this->config['url']."/blog.php?do=showone&uid=$bldb[uid]&type=$type&itemid=$bldb[itemid]";
					}else{
						$bldb['ifpub']	= 1;
					}
				}else{
					$bldb['url'] = $GLOBALS['sys']['url']."/view.php?tid=".$bldb['tid']."&cid=".$this->viewtype;
					$bldb['ifpub'] = 1;
				}
			}else{//blog原帖地址
				$bldb['url']		= $this->config['url']."/blog.php?do=showone&uid=$bldb[uid]&type=$type&itemid=$bldb[itemid]";
				$bldb['ifpub'] = 1;
			}
			if((SCR=='list'||SCR=='view') && $bldb['ifpub']==0) continue;

			if($bldb['attachurl']) {
				$bldb['photo'] = $this->config['url']."/".$this->config['attachdir']."/".$bldb['attachurl'];
			}
			$bl[]	= $bldb;
		}
		return $bl;
	}

	/**
	 * 读取用户日志
	 *
	 * @param integer $num
	 * @param string $order
	 * @param integer $cid
	 * @return array
	 */
	function weblog($num,$order='',$cid=0){
		$start = 0;
		return $this->getBlogs($start,$num,$cid,'blog',$order);
	}

	/**
	 * 读取音乐信息
	 *
	 * @param integer $num
	 * @param string $order
	 * @param integer $cid
	 * @return array
	 */

	function music($num,$order='',$cid=0){
		$start = 0;
		return $this->getBlogs($start,$num,$cid,'music',$order);
	}

	/**
	 * 读取相册信息
	 *
	 * @param integer $num
	 * @param string $order
	 * @param integer $cid
	 * @return array
	 */

	function photo($num,$order='',$cid=0){
		$start = 0;
		return $this->getBlogs($start,$num,$cid,'photo',$order);
	}

	/**
	 * 通过cid读取blog信息
	 *
	 * @param integer $cid
	 * @param integer $start
	 * @param integer $num
	 * @param string $order
	 * @return array
	 */
	function getBlog($cid,$start,$num,$order='') {
		$cid = (int)$cid;
		$cidtype = $this->mysql->get_one("SELECT catetype FROM {$this->config['dbpre']}categories WHERE cid='$cid'");
		$type = $cidtype['catetype'];
		!in_array($type,array('photo','music','blog')) && $type = 'blog';
		//$type=='blog' && $type = 'webLog';
		return $this->getBlogs($start,$num,$cid,$type,$order);
	}

	/**
	 * 通过usericon获取用户头像
	 *
	 * @param integer $tid
	 * @return array
	 */
	function showfacedesign($usericon){
		if (!$usericon) {
			return $this->config['url'].'/'.$this->config['imgdir'].'/upload/none.gif';
		} elseif (preg_match('/^http/i',$usericon)) {
			return $usericon;
		} else {
			return $this->config['url'].'/'.$this->config['imgdir'].'/upload/'.$usericon;
		}
	}

	/**
	 * 通过tid读取单篇文章信息
	 *
	 * @param integer $tid
	 * @return array
	 */
	function getOne($tid) {
		global $aids,$attachments,$catedb;
		$attachments = array();
		$tid = intval($tid);
		$rs1 = $this->mysql->get_one("SELECT * FROM {$this->config['dbpre']}items i WHERE i.itemid='$tid'");
		!$rs1 && exit("this tid is not exist");
		$type = $rs1['type'];
		$rs2 = $this->mysql->get_one("SELECT * FROM {$this->config['dbpre']}$type WHERE itemid='$tid'");
		$rs = array_merge($rs1,$rs2);
		$rs['uploads'] && $attachments	= unserialize($rs['uploads']);
		foreach($attachments as $key => $val){
			$attachments[$key] = $val['type'] == 'img' ? '<img src="'.$this->config['attachurl'].$val['attachurl'].'" alt="'.$val['desc'].'"/>' : '附件：<a href='.$this->config['url'].'/blog.php?do=showone&uid='.$rs[uid].'&type='.$type.'&itemid='.$rs[itemid].' title="转到blog"/>'.$val[name].'</a>';
		}
		$rs['title']	= $rs['subject'];
		$rs['fromsite'] = $this->config['url'];
		$rs['blogurl']	= $bbsurl = $this->config['url'].'/blog.php?do=showone&uid='.$rs['uid'].'&type='.$type.'&itemid='.$rs['itemid'];
		$rs['content'] 	= BlogCode::convert($rs['content'],'');
		if($this->cid && $catedb[$this->cid]['htmlpub']) {
			$htm_ext	= $GLOBALS['sys']['htmext'] ? $GLOBALS['sys']['htmext'] : 'html';
			$logdir		= $GLOBALS['sys']['htmdir'].'/'.$this->cid.'/'.$rs['itemid'].'.'.$htm_ext;
			if(file_exists($logdir)) {
				$rs['ifpub'] = 1;
			}else {
				$rs['ifpub'] = 0;
			}
		}else {
			$rs['ifpub'] = 1;
		}
		foreach($aids as $value){
			if($attachments[$value]){
				unset($attachments[$value]);
			}
		}
		while($val=array_pop($attachments)){
			$type = substr($val,1,3);
			if($type == 'img'){
				$rs['content']=$val."<br />".$rs['content'];
			}else{
				$rs['content'].="<br />".$val;
			}
		}
		unset($attachments,$aids);
		return $rs;
	}

	function total(){
		$total = $this->mysql->get_one($this->totalQuery);
		return $total['total'];
	}
	
}


$codelang = array(
	'full_screen'	=> 'full_screen'
);
/**
 * Blog字符解析类
 *
 */
class BlogCode {
	function convert($message,$allow = array(),$prtimes = '-1'){
		global $code_num,$code_htm,$phpcode_htm,$sys,$codelang;
		$allow['times'] && $prtimes = $allow['times'];
		$message  = nl2br($message);
		$message = preg_replace('/\[code\](.+?)\[\/code\]/eis',"BlogCode::phpcode('\\1')",$message,$prtimes);
		$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is',"<ol type=\"\\1\" style=\"margin:0 0 0 25px\">\\2</ol>", $message);
		$message = str_replace(
			array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[list]','[li]','[/li]','[/list]','[sub]','[/sub]','[sup]','[/sup]','[strike]','[/strike]','[blockquote]','[/blockquote]','[hr]','[p]','[/p]','p_w_upload','p_w_picpath'),
			array('<u>','</u>','<b>','</b>','<i>','</i>','<ul style="margin:0 0 0 15px">','<li>','</li>', '</ul>','<sub>','</sub>','<sup>','</sup>','<strike>','</strike>','<blockquote>','</blockquote>','<hr />','<p>','</p>',$sys['blog_url'].'/'.$sys['blog_attachdir'],$sys['blog_url'].'/'.$sys['blog_imgdir']),
			$message
		);
		$message = preg_replace(
			array(
				'/\[font=([^\[]+?)\](.+?)\[\/font\]/is',
				'/\[color=([#0-9a-z]{1,10})\](.+?)\[\/color\]/is',
				'/\[backcolor=([#0-9a-z]{1,10})\](.+?)\[\/backcolor\]/is',
				'/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is',
				'/\[email\]([^\[]*)\[\/email\]/is',
				'/\[size=(\d+)\](.+?)\[\/size\]/eis',
				'/\[align=(left|center|right|justify)\](.+?)\[\/align\]/is',
				'/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is'
			),
			array(
				"<font face=\"\\1\">\\2</font>",
				"<font color=\"\\1\">\\2</font>",
				"<font style=\"background-color:\\1\">\\2</font>",
				"<a href=\"mailto:\\1\">\\2</a>",
				"<a href=\"mailto:\\1\">\\1</a>",
				"BlogCode::size('\\1','\\2','$allow[size]')",
				"<div align=\"\\1\">\\2</div>",
				"<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>"
			),
			$message
		);
		$code_num = 0;
		$code_htm = array();

		
		$message  = $allow['ifpic'] ? preg_replace('/\[img\](.+?)\[\/img\]/eis',"BlogCode::cvpic('\\1','$allow[picwidth]','$allow[picheight]')",$message,$prtimes) : preg_replace('/\[img\](.+?)\[\/img\]/eis',"BlogCode::nopic('\\1')",$message,$prtimes);
		$message  = preg_replace(
			array(
				'/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp)([^\[\s]+?)\](.+?)\[\/url\]/eis',
				'/\[url\]www\.([^\[]+?)\[\/url\]/eis',
				'/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp)([^\[]+?)\[\/url\]/eis'
			),
			array(
				"BlogCode::cvurl('\\1','\\2','\\3')",
				"BlogCode::cvurl('\\1')",
				"BlogCode::cvurl('\\1','\\2')"
			),
			$message
		);
		$message = preg_replace(
			array(
				'/\[fly\]([^\[]*)\[\/fly\]/is',
				'/\[move\]([^\[]*)\[\/move\]/is'
			),
			array(
				"<marquee width=\"90%\" behavior=\"alternate\" scrollamount=\"3\">\\1</marquee>",
				"<marquee scrollamount=\"3\">\\1</marquee>"
			),
			$message
		);
		if (strpos($message,'[table') !== false && strpos($message,'[/table]') !== false) {
			for ($t = 0;$t < 5;$t++) {
				$message = preg_replace('/\[table(=(\d{1,3}(%|px)?))?\](.*?)\[\/table\]/eis', "BlogCode::table('\\2','\\3','\\4')",$message);
			}
		}
		$message = preg_replace(
			array(
				'/\[post\](.+?)\[\/post\]/is',
				'/\[hide=(.+?)\](.+?)\[\/hide\]/is',
				'/\[sell=(.+?)\](.+?)\[\/sell\]/is'
			),
			"\\1",
			$message
		);
		$message = preg_replace('/\[quote\](.+?)\[\/quote\]/eis',"BlogCode::qoute('\\1')",$message);
		krsort($code_htm);
		foreach ($code_htm as $codehtm) {
			foreach ($codehtm as $key => $value) {
				$message = str_replace("<\twind_code_$key\t>",$value,$message);
			}
		}
		$message = $allow['ifflash'] ? preg_replace('/\[flash=(\d+?)\,(\d+?)\](.+?)\[\/flash\]/eis',"BlogCode::flaplayer('\\3','\\1','\\2')",$message,$prtimes) : preg_replace('/\[flash=(\d+?)\,(\d+?)\](.+?)\[\/flash\]/eis',"BlogCode::flaplayer('\\3','','','1')",$message,$prtimes);
		if ($allow['ifmpeg']) {
			$message = preg_replace(
				array(
					'/\[wmv=(0|1)\](.+?)\[\/wmv\]/eis',
					'/\[wmv=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/wmv\]/eis',
					'/\[rm\](.+?)\[\/rm\]/eis',
					'/\[rm=([0-9]{1,3})\,([0-9]{1,3})\,(0|1)\](.+?)\[\/rm\]/eis'
				),
				array(
					"BlogCode::wmvplayer('\\2','314','53','\\1')",
					"BlogCode::wmvplayer('\\4','\\1','\\2','\\3')",
					"BlogCode::rmplayer('\\1')",
					"BlogCode::rmplayer('\\4','\\1','\\2','\\3')"
				),
				$message,
				$prtimes
			);
		} else {
			$message = preg_replace(
				array(
					'/\[wmv=[01]{1}\](.+?)\[\/wmv\]/is',
					'/\[wmv=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1}\](.+?)\[\/wmv\]/is',
					'/\[rm\](.+?)\[\/rm\]/is',
					'/\[rm=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1}\](.+?)\[\/rm\]/is'
				),
				"<img src=\"$sys[blog_url]/image/default/music.gif\" align=\"absbottom\"> <a href=\"\\1\" target=\"_blank\">\\1</a>",
				$message,
				$prtimes
			);
		}
		$message = $allow['ififrame'] ? preg_replace('/\[iframe\](.+?)\[\/iframe\]/eis',"<iframe src=\"\\1\" frameborder=\"0\" allowtransparency=\"true\" scrolling=\"yes\" width=\"97%\" height=\"340\"></iframe>",$message,$prtimes) : preg_replace('/\[iframe\](.+?)\[\/iframe\]/is',"Iframe Close: <a href=\"\\1\" target=\"_blank\">\\1</a>",$message,$prtimes);
		if (is_array($phpcode_htm)) {
			foreach ($phpcode_htm as $key => $value) {
				$message = str_replace("<\twind_phpcode_$key\t>",$value,$message);
			}
		}
		$message = BlogCode::attachment($message);
		return $message;
	}
	function qoute($code){
		global $code_num,$code_htm;
		$code_num++;
		$code_htm[2][$code_num] = '<h6 class="quote">Quote:</h6><blockquote>'.stripslashes($code).'</blockquote>';
		return "<\twind_code_$code_num\t>";
	}
	function table($width,$unit,$text){
		global $tdcolor;
		!$tdcolor && $tdcolor = '#D4EFF7';
		if ($width) {
			$unit!='%' && $unit = 'px';
			if ($unit != '%') {
				$unit = 'px';
				$width > 600 && $width = 600;
			} else {
				$width > 98 && $width = 98;
			}
			$width .= $unit;
		} else {
			$width = '98%';
		}
		$table = "<table style=\"border: 1px solid $tdcolor;width: $width\">";
		$text = preg_replace('/\[td=(\d{1,2}),(\d{1,2})(,(\d{1,3}%?))?\]/is','<td colspan="\\1" rowspan="\\2" width="\\4">',$text);
		$text = preg_replace('/\[tr\]/is','<tr class="tr3">',$text);
		$text = preg_replace('/\[td\]/is','<td>',$text);
		$text = preg_replace('/\[\/(tr|td)\]/is','</\\1>',$text);
		$table .= $text;
		$table .= '</table>';
		return stripslashes($table);
	}
	function rmplayer($rmurl,$width='316',$height='241',$auto='1'){
		global $codelang;

		return "<object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"$width\" height=\"$height\" id=\"PlayerR\"><param name=\"src\" value=\"$rmurl\" /><param name=\"controls\" value=\"Imagewindow\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"$auto\" /><embed src=\"$rmurl\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"$auto\" console=\"clip1\" controls=\"Imagewindow\" width=\"$width\" height=\"$height\"></embed></object><br /><object classid=\"clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA\" width=\"$width\" height=\"44\"><param name=\"src\" value=\"$rmurl\" /><param name=\"controls\" value=\"ControlPanel\" /><param name=\"console\" value=\"clip1\" /><param name=\"autostart\" value=\"$rmurl\" /><embed src=\"$rmurl\" type=\"audio/x-pn-realaudio-plugin\" autostart=\"$auto\" console=\"clip1\" controls=\"ControlPanel\" width=\"$width\" height=\"44\"></embed></object><script language=\"javascript\">function FullScreenR(){document.PlayerR.SetFullScreen();}</script><input type=\"button\" onclick=\"javascript:FullScreenR()\" value=\"$codelang[full_screen]\"> ";
	}
	function wmvplayer($wmvurl,$width='314',$height='256',$auto='1'){
		global $codelang;

		return "<object width=\"$width\" height=\"$height\" classid=\"CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6\" id=\"PlayerW\"><param name=\"url\" value=\"$wmvurl\" /><param name=\"autostart\" value=\"$auto\" /><embed width=\"$width\" height=\"$height\" type=\"application/x-mplayer2\" src=\"$wmvurl\"></embed></object><script language=\"javascript\">function FullScreenW(){document.PlayerW.DisplaySize = 3;}</script><input type=\"button\" onclick=\"javascript:FullScreenW()\" value=\"$codelang[full_screen]\"> ";
	}
	function flaplayer($flaurl,$width='420',$height='320',$nofla = null){
		global $codelang,$sys;

		if (!empty($nofla)) {
			return "<img src=\"$sys[blog_url]/image/default/music.gif\" align=\"absbottom\"> <a href=\"$flaurl\" target=\"_blank\">flash: $flaurl</a>";
		} else {
			return "<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\"$width\" height=\"$height\"><param name=\"movie\" value=\"$flaurl\" /><param name=\"quality\" value=\"high\" /><embed src=\"$flaurl\" quality=\"high\" type=\"application/x-shockwave-flash\" width=\"$width\" height=\"$height\"></embed></object>[<a href=\"$flaurl\" target=\"_blank\">$codelang[full_screen]</a>] ";
		}
	}
	function cvurl($http,$url = null,$name = null){
		global $code_num,$code_htm;
		$code_num++;
		if (empty($url)) {
			$url = $name = "www.$http";
			$http = 'http://';
		} elseif (empty($name)) {
			$name = $http.$url;
		}
		$name = stripslashes($name);
		$url = "<a href=\"$http$url\" target=\"_blank\">$name</a>";
		$code_htm[1][$code_num] = $url;
		return "<\twind_code_$code_num\t>";
	}
	function nopic($url){
		global $sys;
		$code_num++;
		$code_htm[0][$code_num] = "<img src=\"$sys[blog_url]/image/default/img.gif\" align=\"absbottom\" border=\"0\" /> <a href=\"$url\" target=\"_blank\">img: $url</a>";
		return "<\twind_code_$code_num\t>";
	}
	function cvpic($url,$picwidth = null,$picheight = null,$type = null,$descrip = null){
		global $code_num,$db_blogurl,$code_htm;
		$code_num++;
		(substr(strtolower($url),0,4)!='http') && $url = "$db_blogurl/$url";
		(strpos(strtolower($url),'login')!==false && (strpos(strtolower($url),'action=quit')!==false || strpos(strtolower($url),'action-quit')!==false)) && $url = str_replace('login','log in',$url);
		$descrip && $descrip = " alt=\"$descrip\"";
		if ($picwidth || $picheight) {
			$onload = ' onload="';
			$picwidth  && $onload .= "if(this.width>'$picwidth')this.width='$picwidth';";
			$picheight && $onload .= "if(this.height>'$picheight')this.height='$picheight';";
			$onload .= '"';
		} else {
			$onload = '';
		}
		$code = "<img src=\"$url\"{$onload}{$descrip} border=\"0\" />";
		$code_htm[0][$code_num] = $code;
		if ($type) {
			return $code;
		} else {
			return "<\twind_code_$code_num\t>";
		}
	}
	function size($size,$code,$allowsize){
		$allowsize && $size > $allowsize && $size = $allowsize;
		return "<font size=\"$size\">".str_replace('\\"','"',$code)."</font>";
	}
	function phpcode($code){
		global $phpcode_htm,$codeid;
		$code = str_replace(array("[attachment=",'\\"'),array("&#91;attachment=",'"'),$code);
		$codeid ++;
		$phpcode_htm[$codeid]="<h6 class=\"quote\"><a href=\"javascript:\"  onclick=\"CopyCode(document.getElementById('code$codeid'));\">Copy code</a></h6><blockquote id=\"code$codeid\">".preg_replace("/^(\<br \/\>)?(.*)/is","\\2",$code)."</blockquote>";
		return "<\twind_phpcode_$codeid\t>";
	}
	function attachment($message,$attachdb,$prtimes='-1'){
		$message = preg_replace('/\[attachment=([0-9]+)\]/eis',"BlogCode::upload('\\1')",$message,$prtimes);
		return $message;
	}
	function upload($aid){
		global $aids,$attachments;
		if ($attachments[$aid]) {
			$aids[] = $aid;
			return $attachments[$aid];
		} else {
			return "[attachment=$aid]";
		}
	}
}

?>