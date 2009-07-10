<?php
defined('IN_EXT') or die('Forbidden');

require_once(E_P.'include/cache.class.php');
$action = GetGP('action');
$type = (int)GetGP('type');
if($type!==0)$type = 1;
empty($action) && $action = 'show';
$action != 'show' && $basename .= "&type=$type";
$wordfilter = new wordfilter();
$wordfilter->doIt($action);

class wordfilter{

	function show(){
		global $db,$type,$basename;
		$page = (int)GetGP('page');
		$keyword = Char_cv(GetGP('keyword'));
		if($page<1)$page = 1;
		$sqladd = " type='$type' ";
		if($keyword){
			$kwd = str_replace(array('%','_'),array('\%','\_'),$keyword);
			$sqladd .= " AND srcword LIKE('%$kwd%')";
		}
		$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_wordfilter WHERE $sqladd");
		$total = $rt['total'];
		$numofpage = ceil($total/30);
		$url = "$basename&action=show&&type=$type&keyword=$keyword&";
		$pages = numofpage($total,$page,$numofpage,$url);
		$start = ($page-1)*30;
		$sqladd .= " ORDER BY id DESC LIMIT $start,30 ";
		$rs = $db->query("SELECT id,srcword,tarword,type FROM cms_wordfilter WHERE $sqladd");
		$wdf = array();
		while ($rt=$db->fetch_array($rs)) {
			$rt['srcword'] = htmlspecialchars($rt['srcword']);
			$rt['tarword'] = htmlspecialchars($rt['tarword']);
			$wdf[] = $rt;
		}
		${'type_'.$type} = 'selected';
		require PrintExt('header');
		require PrintExt('admin');
		adminbottom();
	}

	function del(){
		global $db,$type,$basename;
		$ids = GetGP('ids');
		!is_array($ids) && $ids = array($ids);
		$ids = implode(',',$ids);
		$db->update("DELETE FROM cms_wordfilter WHERE id IN($ids)");
		wdfCache::cache();
		adminmsg('operate_success');
	}

	function edit(){
		global $db,$type,$basename;
		extract(Init_GP(array('ids','type','srcword','tarword'),'P'));
		!is_array($srcword) && $src = array($srcword);
		!is_array($tarword) && $tar = array($tarword);
		foreach($srcword as $key=>$value){
			if($value){
				$value = Char_cv($value);
				$tarword[$key] = Char_cv($tarword[$key]);
				if($ids[$key]){
					$id = $ids[$key];
					$db->update("UPDATE cms_wordfilter SET srcword='$value',tarword='$tarword[$key]',type='$type' WHERE id='$id'");
				}else{
					$db->update("INSERT INTO cms_wordfilter(srcword,tarword,type) VALUES('$value','$tarword[$key]','$type')");
				}
			}
		}
		wdfCache::cache();
		adminmsg('operate_success');
	}

	function import(){
		global $db,$type,$basename;
		include_once(R_P.'require/class_attach.php');
		include_once(D_P."data/cache/wordfilter.php");
		$word = $rep = array();
		if(empty($_FILES)) Showmsg('mod_nofile');
		foreach ($_FILES as $key=>$value){
			if($key!=='upload') continue;
			$i++;
			if(is_array($value)){
				$filename = $value['name'];
				$tmpfile = $value['tmp_name'];
				$filesize = $value['size'];
			}else{
				$filename = ${$key.'_name'};
				$tmpfile = $$key;
				$filesize = ${$key.'_size'};
			}
		}
		$ext = end(explode('.',$filename));
		if(strtolower($ext)!=='txt') Showmsg('mod_fileexterror');
		$newname = $GLOBALS['timestamp'].'.txt';
		$attach = new Attach();
		$attach->postupload($tmpfile,D_P.'data/'.$newname);
		$str = file(D_P.'data/'.$newname);

		foreach($str as $key=>$value){
			list($w,$r)=explode("=>",$value);
			$r = trim($r);
			$w = explode("|",$w);
			foreach($w as $k=>$v){
				$v = preg_quote(trim($v),'/');
				if(!isset($replace[$v]) && !isset($wordsfb[$v])){
					$word[] = $v;
					$rep[] = $r;
				}
			}
		}
		foreach($word as $key=>$value){
			if($value){
				$db->update("INSERT INTO cms_wordfilter(srcword,tarword,type) VALUES('$value','$rep[$key]','$type')");
			}
		}
		unlink(D_P.'data/'.$newname);
		wdfCache::cache();
		adminmsg('operate_success');
	}

	function export(){
		global $db,$type;
		$rs = $db->query("SELECT * FROM cms_wordfilter WHERE type='$type'");
		$filename = 'VeryCMS_wordfilter_'.randomStr(5).'.txt';
		$words = '';
		while($rt = $db->fetch_array($rs)){
			$words .= $rt['srcword']."=>".$rt['tarword']."\r\n";
		}
		$filesize = strlen($words);
		ob_end_clean();
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header('Content-type: txt');
		header('Content-Length: '.$filesize);
		echo $words;
		exit();
	}

	function doIt($action){
		switch($action){
			case 'show':
				$this->show();
				break;
			case 'del':
				$this->del();
				break;
			case 'add':
			case 'edit':
				$this->edit();
				break;
			case 'import':
				$this->import();
				break;
			case 'export':
				$this->export();
				break;
		}
	}
}
?>