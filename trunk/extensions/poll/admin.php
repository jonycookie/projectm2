<?php
defined('IN_EXT') or die('Forbidden');
//Showmsg('undefined_action');

class ManagePoll{
	function doIt($action){
		switch($action){
			case 'show':
				$this->show();
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'tplapi':
				$this->tplapi();
				break;
			case 'view':
				$this->view();
				break;
			case 'gethtml':
				$this->getHTML();
				break;
		}
	}

	function show(){
		global $db,$timestamp,$basename,$action;
		$key = (int)GetGP('key');
		$sql = "1";
		if($key==1){
			$sql = " etime>'$timestamp' or etime=stime ";
		}elseif($key==2){
			$sql = " etime<'$timestamp' AND etime<>stime ";
		}elseif($key==3){
			$sql = " stime>'$timestamp' ";
		}
		${'key_'.$key} = 'selected';
		$polldb = array();
		$rs = $db->query("SELECT * FROM cms_polls WHERE $sql ");
		while($rt=$db->fetch_array($rs)){
			if($rt['stime'] == $rt['etime']){
				$rt['etime'] = 'Always';
			}else{
				$rt['etime'] = get_date($rt['etime'],'Y-m-j');
			}
			$rt['stime'] = get_date($rt['stime'],'Y-m-j');
			$rt['subject'] = substrs($rt['subject'],30);
			$polldb[] = $rt;
		}
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function add(){
		global $db,$basename,$action,$timestamp;
		$step = GetGP('step');
		if(!$step){
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		}elseif($step==2){
			$poll = GetGP('poll');
			extract($poll);
			$subject = Char_cv($subject);
			$content = Char_cv($content);
			$options = Char_cv($options);
			$mark = Char_cv($mark);
			$ismulti = $ismulti ? 1 : 0;
			$stime = strtotime($stime);
			$etime = strtotime($etime);
			!$stime && $stime = $timestamp;
			if($longtime || $etime<$stime){
				$etime = $stime;
			}
			$options = explode("\n",str_replace("\r",'',$options));
			foreach($options as $val){
				$tmp['value'] = $val;
				$tmp['stats'] = 0;
				$option[] = $tmp;
			}
			$options = serialize($option);
			$sql = "INSERT INTO `cms_polls` SET
			`subject`='$subject',
			`content`='$content',
			`options`='$options',
			`stats`='0',
			`ismulti`='$ismulti',
			`stime`='$stime',
			`etime`='$etime',
			`mark`='$mark'";
			$db->update($sql);
		}
		adminmsg('operate_success');
	}

	function edit(){
		global $db,$basename,$action,$timestamp;
		$step = GetGP('step');
		if(!$step){
			$id = (int)GetGP('id');
			$rt = $db->get_one("SELECT * FROM cms_polls WHERE id='$id'");
			extract($rt);
			if($etime == $stime){
				$longtime = 'checked';
			}
			$etime = get_date($etime,'Y-m-j');
			$stime = get_date($stime,'Y-m-j');
			$opt = unserialize($options);
			$tmp = array();
			foreach($opt as $val){
				$tmp[] = $val['value'];
			}
			$options = implode("\n",$tmp);
			ifcheck($ismulti,'ismulti');
			extract($GLOBALS['checks']);
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		}elseif($step==2){
			$poll = GetGP('poll');
			extract($poll);
			$id = intval($id);
			$rt = $db->get_one("SELECT * FROM cms_polls WHERE id='$id'");
			if(!$rt){
				Showmsg('ext_pollnoid');
			}
			$subject = Char_cv($subject);
			$content = Char_cv($content);
			$options = Char_cv($options);
			$mark = Char_cv($mark);
			$ismulti = $ismulti ? 1 : 0;
			$stime = strtotime($stime);
			$etime = strtotime($etime);
			!$stime && $stime = $timestamp;
			if($longtime || $etime<$stime){
				$etime = $stime;
			}
			$opt = unserialize($rt['options']);
			$options = explode("\n",str_replace("\r",'',$options));
			foreach($options as $key=>$val){
				$tmp['value'] = $val;
				$tmp['stats'] = $opt[$key]['stats'];
				$option[] = $tmp;
			}
			$options = serialize($option);
			$sql = "UPDATE `cms_polls` SET
			`subject`='$subject',
			`content`='$content',
			`options`='$options',
			`ismulti`='$ismulti',
			`stime`='$stime',
			`etime`='$etime',
			`mark`='$mark' WHERE id='$id'";
			$db->update($sql);
		}
		adminmsg('operate_success');
	}

	function getHTML() {
		global $db,$basename,$timestamp,$E_name,$action;
		$id	= (int)GetGP('id');
		$sql= " stime<='$timestamp' AND (stime=etime or etime>='$timestamp') ";
		if($id){
			$sql .= " AND id='$id' ";
		}else{
			Showmsg('ext_pollnoid');
		}

		$rt = $db->get_one("SELECT * FROM cms_polls WHERE $sql ORDER BY stime DESC");
		if(!$rt){
			Showmsg('ext_pollnoid');
		}
		extract($rt);
		$etime	= get_date($etime,'Y-m-j');
		$stime	= get_date($stime,'Y-m-j');
		$options= unserialize($options);
		$result = '';
		$result .= "<form action=\"extensions.php?E_name=$E_name\" method=\"post\" name=\"FORM\">\r\n";
		$result .= "<table border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tbody>\r\n";
		$result .= "<tr bgcolor=\"E0E0E0\"><td><b>$subject</b></td></tr>\r\n";
		$result .= "<tr><td><p>$content</p></td></tr>\r\n";
		foreach($options as $key=>$val){
			$key++;
			if(!$ismulti){
				$result .= "<tr valign=\"top\"><td width=\"25%\"><input name=\"poll\" value=\"$key\" type=\"radio\" />$val[value]</td></tr>\r\n";
			}else {
				$result .= "<tr valign=\"top\"><td width=\"25%\"><input name=\"poll[$key]\" value=\"$key\" type=\"checkbox\" />$val[value]</td></tr>\r\n";
			}
		}
		$result .= "</tbody></table>\r\n";
		$result .= "<input name=\"job\" value=\"vote\" type=\"hidden\" /><input name=\"id\" value=\"$id\" type=\"hidden\" />\r\n";
		$result .= "<input value=\"提交\" type=\"submit\">\r\n";
		$result .= "<input onclick=\"javascript:window.open('extensions.php?E_name=$E_name&job=view&id=$id');\" value=\"查看\" type=\"button\">\r\n";
		$result .= "</form>\r\n";
		$result	= Char_cv($result);
		$jsresult = "<script language=\"javascript\" src=\"extensions.php?E_name=$E_name&id=$id\"></script>";
		$jsresult = Char_cv($jsresult);
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
		
	}

	function tplapi(){
		global $action,$basename,$E_name;
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function view(){
		global $db,$basename,$action,$timestamp,$ext_imgpath;
		$id = (int)GetGP('id');
		$rt = $db->get_one("SELECT * FROM cms_polls WHERE id='$id'");
		extract($rt);
		$etime = get_date($etime,'Y-m-j');
		$stime = get_date($stime,'Y-m-j');
		$options = unserialize($options);
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}
}

$action = GetGP('action');
empty($action) && $action = 'show';
$poll = new ManagePoll();
$poll->doIt($action);
?>