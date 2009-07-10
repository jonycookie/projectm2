<?php
defined('IN_EXT') or die('Forbidden');

require_once(E_P.'include/cache.class.php');
require_once(R_P.'require/class_cate.php');
$action = GetGP('action');
!$action && $action = "adp";

$adv = new Advert();
$adv->DoIt();

class Advert{
	function adpAdd(){
		global $basename,$action;
		$step = GetGP('step');
		$job = GetGP('job');
		if(!$step){
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		} elseif($step == '2'){
			global $db;
			//一些过滤
			$adpinfo = GetGP('adpinfo');
			$setting = GetGP('setting');
			if(!$adpinfo['name'] || !$adpinfo['jsname']){
				adminmsg('ext_advert_error');
			}
			if(!preg_match("/^[a-zA-Z0-9]{3,}$/",$adpinfo['jsname'])){
				adminmsg('ext_advert_error');
			}
			$rt = $db->get_one("SELECT jsname FROM cms_adposition WHERE jsname='$adpinfo[jsname]'");
			if(!$rt){
				$setting['showtype'] = $setting['showtype'] ? intval($setting['showtype']) : 1;
				$setting['left'] = $setting['left'] ? intval($setting['left']) : 0;
				$setting['top'] = $setting['top'] ? intval($setting['top']) : 0;
				$setting['delta'] = is_numeric($setting['delta']) ? $setting['delta'] : 0.15;
				$setting['floattype'] = $setting['floattype'] ? intval($setting['floattype']) : 1;
				$setting['poptype'] = $setting['poptype'] ? intval($setting['poptype']) : 1;
				$setting['delay'] = $setting['delay'] ? intval($setting['delay']) : 50;
				$setting['cookiehour'] = $setting['cookiehour'] ? intval($setting['cookiehour']) : 0;
				$adpinfo['setting'] = serialize($setting);
				unset($setting);
				$adpinfo['width'] = $adpinfo['width'] ? intval($adpinfo['width']) : 100;
				$adpinfo['height'] = $adpinfo['height'] ? intval($adpinfo['height']) : 100;
				$this->adpUpdate($adpinfo,'IN');
				adminmsg('ext_adpaddok');
			}
			adminmsg('ext_adpjsexists');
		}
	}

	function adpUpdate($adpinfo,$type){
		global $db;

		$id = (int)$adpinfo['pid'];
		$sql = '';
		foreach($adpinfo as $key=>$value){
			if($key != 'pid'){
				$sql .= empty($sql) ? "$key='$value'" : ",$key='$value'";
			}
		}
		if($type == 'IN'){
			$db->update("INSERT INTO cms_adposition SET $sql");
		} elseif($type == 'UP'){
			$db->update("UPDATE cms_adposition SET $sql WHERE pid=$id");
		}
	}

	function adpDel($id){
		global $db;
		empty($id) &&  adminmsg('ext_noselect');
		if(is_array($id)){
			$ids = implode(',',$id);
			$db->update("DELETE FROM cms_adposition WHERE pid IN($ids)");
		}else{
			$id = (int)$id;
			$db->update("DELETE FROM cms_adposition WHERE pid='$id'");
		}
		adminmsg('ext_adpdelok');
	}

	function adpEdit($pid){
		global $basename,$action;
		$step = GetGP('step');
		$job = GetGP('job');
		if(!$step){
			$adpinfo = $this->adpGet($pid);
			$setting = unserialize($adpinfo['setting']);
			unset($adpinfo['setting']);
			${'active_'.$adpinfo['active']}='CHECKED';
			${'poptype_'.$setting['poptype']}='SELECTED';
			${'floattype_'.$setting['floattype']}='SELECTED';
			${'showtype_'.$adpinfo['showtype']}='CHECKED';
			${'type_'.$adpinfo['type']}='SELECTED';

			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		} elseif($step == '2'){
			global $db;
			$adpinfo = GetGP('adpinfo');
			$setting = GetGP('setting');
			//一些过滤
			if(!$adpinfo['name'] || !$adpinfo['jsname']){
				adminmsg('ext_advert_error');
			}
			if(!preg_match("/^[a-zA-Z0-9]{3,}$/",$adpinfo['jsname'])){
				adminmsg('ext_advert_error');
			}
			$rt = $db->get_one("SELECT jsname FROM cms_adposition WHERE jsname='$adpinfo[jsname]' AND pid<>'$adpinfo[pid]'");
			if(!$rt){
				$setting['showtype'] = $setting['showtype'] ? intval($setting['showtype']) : 1;
				$setting['left'] = $setting['left'] ? intval($setting['left']) : 0;
				$setting['top'] = $setting['top'] ? intval($setting['top']) : 0;
				$setting['delta'] = is_numeric($setting['delta']) ? $setting['delta'] : 0.15;
				$setting['floattype'] = $setting['floattype'] ? intval($setting['floattype']) : 1;
				$setting['poptype'] = $setting['poptype'] ? intval($setting['poptype']) : 1;
				$setting['delay'] = $setting['delay'] ? intval($setting['delay']) : 50;
				$setting['cookiehour'] = $setting['cookiehour'] ? intval($setting['cookiehour']) : 0;
				$adpinfo['setting'] = serialize($setting);
				unset($setting);
				$adpinfo['width'] = $adpinfo['width'] ? intval($adpinfo['width']) : 100;
				$adpinfo['height'] = $adpinfo['height'] ? intval($adpinfo['height']) : 100;
				$this->adpUpdate($adpinfo,'UP');
				adminmsg('ext_adpeditok');
			}
			adminmsg('ext_adpjsexit');
		}
	}

	function adpGet($pid){
		global $db;

		$pid = (int)$pid;
		$rt = $db->get_one("SELECT * FROM cms_adposition  WHERE pid='$pid'");
		if(!empty($rt)){
			return $rt;
		}
		return;
		//adminmsg('ext_adpnoexist');
	}

	function adpShow(){
		global $db,$basename,$action;
		$job = GetGP('job');
		$rs = $db->query("SELECT pid,name,type,showtype,active FROM cms_adposition ");
		while($adpdb = $db->fetch_array($rs)){
			$adplist[] = $adpdb;
		}

		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function advAdd(){
		global $basename,$action;
		$job = GetGP('job');
		$step = GetGP('step');
		$pid = GetGP('pid');
		$timestamp = time();
		if(!$step){
			$cate = new Cate();
			$cate_select="<option value=\"0\" >&raquo;Home</option>";
			$cate_select.=$cate->tree();
			$AdvertTree = $this->Tree();
			$AdvertTree = str_replace("value=\"$pid\"","value=\"$pid\" selected ",$AdvertTree);
			$starttime = get_date($timestamp,'Y-m-d');
			$endtime = get_date($timestamp +31536000,'Y-m-d');
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		} elseif($step == '2'){
			$advinfo = GetGP('advinfo');
			//一些过滤
			$config = GetGP('config');
			$cids = GetGP('cids');
			if(!$advinfo['name'] || !$advinfo['type']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'code' && !$advinfo['intro']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'txt' && !$advinfo['intro']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'img' && !$config['url']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'flash' && !$config['url']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'page' && !$config['url']){
				adminmsg('ext_advert_error');
			}
			!$config['url'] && $config['url'] = "";
			!$config['linkurl'] && $config['linkurl'] = "";
			!$config['linktarget'] && $config['linktarget'] = "";
			!$config['linkalt'] && $config['linkalt'] = "";
			$config['width'] = $config['width'] ? intval($config['width']) : 100;
			$config['height'] = $config['height'] ? intval($config['height']) : 100;
			$config['flashwmode'] = $config['flashwmode'] ? intval($config['flashwmode']) : 0;
			$config['priority'] = $config['priority'] ? intval($config['priority']) : 1;
			$advinfo['cid'] = implode(',',$cids);
			foreach($config as $key=>$value){
				$config[$key] = stripslashes($value);
			}
			$advinfo['config'] = serialize($config);
			unset($config);
			$advinfo['starttime'] =  !$advinfo['starttime'] ? $timestamp : strtotime($advinfo['starttime']);
			$advinfo['endtime'] = !$advinfo['endtime'] ? $timestamp +31536000 : strtotime($advinfo['endtime']);
			$advinfo['intro'] = addslashes($advinfo['intro']);
			$advinfo['pid'] = intval($advinfo['pid']);
			$this->advUpdate($advinfo,'IN');
			adminmsg('ext_advaddok');
		}
	}

	function advUpdate($advinfo,$type){
		global $db;

		$id = (int)$advinfo['adid'];
		$sql = '';
		foreach($advinfo as $key=>$value){
			if($key != 'adid'){
				$sql .= empty($sql) ? "$key='$value'" : ",$key='$value'";
			}
		}
		if($type == 'IN'){
			$db->update("INSERT INTO cms_advert SET $sql");
		} elseif($type == 'UP'){
			$db->update("UPDATE cms_advert SET $sql WHERE adid=$id");
		}
	}

	function advDel($id=0){
		global $db;
		empty($id) &&  Showmsg('ext_noselect');
		if(is_array($id)){
			$ids = implode(',',$id);
			$db->update("DELETE FROM cms_advert WHERE adid IN($ids)");
		}else{
			$id = (int)$id;
			$db->update("DELETE FROM cms_advert WHERE adid='$id'");
		}
		adminmsg('ext_advdelok');
	}

	function advEdit($adid){
		global $basename,$action;
		$step = GetGP('step');
		$job = GetGP('job');
		$timestamp = time();
		if(!$step){
			$advinfo = $this->advGet($adid);
			$config = unserialize($advinfo['config']);
			unset($advinfo['config']);
			${'flashwmode_'.$config['flashwmode']}="CHECKED";
			${'linktarget_'.$advinfo['linktarget']}="CHECKED";
			${'type_'.$advinfo['type']}='selected';
			$advinfo['starttime'] = get_date($advinfo['starttime'],'Y-m-d');
			$advinfo['endtime']	 = get_date($advinfo['endtime'],'Y-m-d');
			$advinfo['intro'] = stripslashes($advinfo['intro']);
			$discate = explode(',',$advinfo['cid']);
			$cate = new Cate();
			$cate_select="<option value=\"0\" >&raquo;Home</option>";
			$cate_select.=$cate->tree();
			foreach ($discate as $cid){
				$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
			}

			$AdvertTree = $this->Tree();
			$AdvertTree = str_replace("value=\"$advinfo[pid]\"","value=\"$advinfo[pid]\" selected ",$AdvertTree);

			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		} elseif($step == '2'){
			//一些过滤
			$advinfo = GetGP('advinfo');
			$config = GetGP('config');
			$cids = GetGP('cids');
			if(!$advinfo['name'] || !$advinfo['type']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'code' && !$advinfo['intro']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'txt' && !$advinfo['intro']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'img' && !$config['url']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'flash' && !$config['url']){
				adminmsg('ext_advert_error');
			} elseif($advinfo['type'] == 'page' && !$config['url']){
				adminmsg('ext_advert_error');
			}
			!$config['url'] && $config['url'] = "";
			$config['width'] = $config['width'] ? intval($config['width']) : 100;
			$config['height'] = $config['height'] ? intval($config['height']) : 100;
			$config['flashwmode'] = $config['flashwmode'] ? intval($config['flashwmode']) : 0;
			!$config['linkurl'] && $config['linkurl'] = "";
			!$config['linktarget'] && $config['linktarget'] = "";
			!$config['linkalt'] && $config['linkalt'] = "";
			$config['priority'] = $config['priority'] ? intval($config['priority']) : 1;
			$advinfo['cid'] = implode(',',$cids);

			foreach($config as $key=>$value){
				$config[$key] = stripslashes($value);
			}
			$advinfo['config'] = serialize($config);
			unset($config);
			$advinfo['starttime'] =  !$advinfo['starttime'] ? $timestamp : strtotime($advinfo['starttime']);
			$advinfo['endtime'] = !$advinfo['endtime'] ? $timestamp +31536000 : strtotime($advinfo['endtime']);
			$advinfo['pid'] = intval($advinfo['pid']);
			$advinfo['intro'] = addslashes($advinfo['intro']);
			$this->advUpdate($advinfo,'UP');
			adminmsg('ext_adveditok');
		}
	}

	function advGet($id=0){
		global $db;

		$id = (int)$id;
		$rt = $db->get_one("SELECT * FROM cms_advert WHERE adid='$id'");
		if(!empty($rt)){
			return $rt;
		}
		return;
		//adminmsg('ext_advnoexist');
	}

	function advShow($pid=0,$cid='-1'){
		global $db,$basename,$action;
		$job = GetGP('job');
		$pid =(int)$pid;
		$cid =(int)$cid;
		$sqladd = '1';
		$cids = '';
		$pid && $sqladd .= " AND pid='$pid'";
		$cid!='-1' && $cids = ",$cid,";
		$rs = $db->query("SELECT adid,name,type,priority,endtime,pid,cid FROM cms_advert WHERE $sqladd");
		while($advdb = $db->fetch_array($rs)){
			if(!$cids || strpos(','.$advdb['cid'].',',$cids)!==false){
//				$adpinfo = $this->adpGet($advdb['pid']);
//				$advdb['pid'] = $adpinfo['name'];
				$advdb['endtime'] = get_date($advdb['endtime']);
				$advlist[] = $advdb;
			}
		}

		$cate = new Cate();
		$cate_select="<option value=\"0\" >&raquo;Home</option>";
		$cate_select.=$cate->tree();
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);

		$AdvertTree = $this->Tree();
		$AdvertTree = str_replace("value=\"$pid\"","value=\"$pid\" selected ",$AdvertTree);
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function JsShow($pid){
		global $db,$basename,$action,$very;
		$job = GetGP('job');
		$rt = $db->get_one("SELECT jsname FROM cms_adposition WHERE pid='$pid'");
		if($rt){
			$url = $very['url']."/script/verycms/{$rt[jsname]}.js?cid=\$cid&tid=\$tid";
			$jscode = "<script language=\"javascript\"  src=\"$url\"></script>";
			require PrintExt('header');
			require PrintExt('admin');
			adminbottom();
		}
		adminmsg('ext_advert_error');
	}

	function JsCache($pid){
		global $db;
		$timestamp = time();
		$advinfo = $adpinfo = $type = array();
		$type = array('img'=>1,'flash'=>2,'txt'=>3,'code'=>4,'page'=>5);
		$sqladd='';
		$pid && $sqladd = "WHERE pid='$pid'";
		$prs = $db->query("SELECT * FROM cms_adposition $sqladd");
		while($adpinfo = $db->fetch_array($prs)){
			$adpinfo['setting'] = unserialize($adpinfo['setting']);
			$ars = $db->query("SELECT * FROM cms_advert WHERE pid='$adpinfo[pid]' AND endtime > '$timestamp'");
			$advinfo='';
			while($art = $db->fetch_array($ars)){
				$art['cid'] = ','.$art['cid'].',';
				$art['type'] = $type[$art['type']];
				$art['config'] = unserialize($art['config']);
				$art['intro'] = str_replace("\r\n",'\n',$art['intro']);
				$advinfo[] = $art;
			}
			advCache::jsCache($adpinfo,$advinfo);
		}
	}

	function Tree(){
		global $db;
		$prs = $db->query("SELECT * FROM cms_adposition");
		$tempdb = '';
		while($adpinfo = $db->fetch_array($prs)){
			$tempdb .= "<option value=\"$adpinfo[pid]\">$adpinfo[name]</option>";
		}
		return $tempdb;
	}

	function DoIt(){
		global $action;
		$job = GetGP('job');
		if($action == 'adv'){
			switch($job){
				case 'add':
					$this->advAdd();
					break;
				case 'edit':
					$adid = GetGP('adid');
					$this->advEdit($adid);
					break;
				case 'del':
					$ids = GetGP('ids');
					$this->advDel($ids);
					break;
				default:
					$pid = GetGP('pid');
					$cid = GetGP('cid');
					!$cid && $cid='-1';
					$this->advShow($pid,$cid);
					break;
			}
		} elseif($action == 'adp'){
			switch($job){
				case 'add':
					$this->adpAdd();
					break;
				case 'edit':
					$pid = GetGP('pid');
					$this->adpEdit($pid);
					break;
				case 'del':
					$ids = GetGP('ids');
					$this->adpDel($ids);
					break;
				case 'jsshow':
					$pid = GetGP('pid');
					$this->JsCache($pid);
					$this->JsShow($pid);
					break;
				default:
					$this->adpShow();
					break;
			}
		} elseif($action == 'jscache'){
			$pid = (int)GetGP('pid');
			$this->JsCache($pid);
			adminmsg('ext_jscacheok');
		}
	}

	function Import(){
	}

	function Export(){
	}
}
?>