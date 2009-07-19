<?php
!defined('IN_ADMIN') && die('Forbidden');

class Help{

	/**
	 * 获取单条帮助信息
	 *
	 * @param var $hid
	 * @return array
	 */
	function getMSG($hid=''){
		global $db;
		$rt = $db->get_one("SELECT * FROM cms_help WHERE hid='$hid'");
		return $rt;
	}

	/**
	 * 获取分类帮助
	 *
	 * @param string $title
	 * @return array
	 */
	function getHelp($title=''){
		global $db;
		$array = array();
		$title = Char_cv($title);
		$rt = $db->get_one("SELECT hid,hup,title,content FROM cms_help WHERE hup=0 AND title='$title'");
		if(!$rt)return ;
		$rs = $db->query("SELECT hid,title,content FROM cms_help WHERE hup='$rt[hid]'");
		while($h=$db->fetch_array($rs)){
			$array[] = $h;
		}
		return $array;
	}

	/**
	 * 帮助搜索
	 *
	 * @param string $keyword
	 * @return array
	 */
	function search($keyword=''){
		global $db;
		$keyword = Char_cv($keyword);
		$keyword = str_replace(array('%','_'),array('\%','\_'),$keyword);
		$rs = $db->query("SELECT hid,hup,title,content FROM cms_help WHERE title LIKE('%$title%')");
		while($h=$db->fetch_array($rs)){
			if($h['hup']){
				$array[] = $h;
			}
		}
		return $array;
	}

	function doIt($action){
		switch($action){
			case 'search':
				$keyword = GetGP('keyword');
				$helpmsg = $this->search($keyword);
				require PrintEot('faq');
				adminbottom(0);
				break;
			case 'gethelp':
				$title = GetGP('title');
				$helpmsg = $this->getHelp($title);
				require PrintEot('faq');
				adminbottom(0);
				break;
			case 'getmsg':
				$hid = (int)GetGP('hid');
				$helpmsg = $this->getMSG();
				require PrintEot('faq');
				adminbottom(0);
				break;
			case 'minihelp':
				$title = GetGP('title');
				$content = $this->getHelp($title);
				require PrintEot('faq');
				adminbottom(0);
				break;
		}
	}
}

$action = GetGP('action');
$help = new Help();
$help->doIt($action);
?>