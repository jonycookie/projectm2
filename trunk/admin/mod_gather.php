<?php
!defined('IN_ADMIN') && die('Forbidden');
require_once(R_P.'require/class_gather.php');

class GatherAdmin{
	var $htmlMark;
	var $gather;
	var $xmlTags;

	function __construct(){
		$this->htmlMark = array(
		'a',
		'b',
		'p',
		'br',
		'center',
		'span',
		'font',
		'div',
		'table',
		'tbody',
		'tr',
		'td',
		'li',
		'img',
		'iframe',
		'script',
		'&amp;nbsp;',
		);
	}

	function GatherAdmin(){
		$this->__construct();
	}

	function doIt($action){
		!$action && $action='show';
		$this->$action();
	}

	function show(){
		global $db,$action,$basename;
		$rs = $db->query("SELECT * FROM cms_gather");
		$gather=array();
		while ($gatherdb = $db->fetch_array($rs)){
			$gatherdb['module']=$GLOBALS['moduledb'][$gatherdb[mid]]['mname'];
			$gather[]=$gatherdb;
		}
		foreach ($GLOBALS['moduledb'] as $mid=>$m){
			if($mid<0)
			continue;
			$module_select .= "<option value=$mid>$m[mname]</option>";
		}
		require PrintEot('header');
		require PrintEot('mod_gather');
		adminbottom();
	}

	function add(){
		global $action,$basename,$mid,$step;
		if($mid){
			$mid = intval($mid);
		}else{
			$mid=1;
		}
		if(!$step){
			$type = (int)GetGP('type');
			$module_select = '';
			foreach ($GLOBALS['moduledb'] as $key=>$val){
				if($key<0) continue;
				$module_select.="<option value=\"$key\">$val[mname]</option>";
			}
			$module_select=str_replace("value=\"$mid\"","value=\"$mid\" selected",$module_select);

			$modgather = $GLOBALS['db']->query("SELECT fieldname,fieldid,ifgather FROM cms_field WHERE mid='$mid' AND ifgather=1");
			$modrule = array();
			while ($g =  $GLOBALS['db']->fetch_array($modgather)) {
				foreach ($this->htmlMark as $mark){
					$g['htmlmark'].='<div style="width:15%; float:left;"><input type=checkbox name="clearhtml['.$g['fieldid'].'][]" value="'.$mark.'"';
					$g['htmlmark'].='>'.$mark.'</div>';
				}
				$modrule[] = $g;
			}
			$hottag = $this->hottag();
			ifcheck($multi,'multi');
			extract($GLOBALS['checks']);
			require PrintEot('header');
			require PrintEot('mod_gather');
			adminbottom();
		}elseif ($step==2){
			$this->saveRule($_POST);
			adminmsg('operate_success');
		}
	}

	function edit(){
		global $db,$step,$gid,$action,$basename;
		!$gid && Showmsg('data_error');
		if(!$step){
			$ifchange="disabled=\"disabled\"";
			$module_select = '';
			foreach ($GLOBALS['moduledb'] as $key=>$val){
				if($key<=0) continue;
				$module_select.="<option value=\"$key\">$val[mname]</option>";
			}
			$gather_info = $db->get_one("SELECT * FROM cms_gather WHERE gid='$gid'");
			!$gather_info && Showmsg('data_error');
			extract($gather_info);
			$modgather = $db->query("SELECT fieldname,fieldid,ifgather FROM cms_field WHERE mid='$mid' AND ifgather=1");
			$module_select=str_replace("value=\"$mid\"","value=\"$mid\" selected",$module_select);
			$fieldrule = unserialize($fieldrule);
			$clearhtml = unserialize($clearhtml);
			$imgtolocal = unserialize($imgtolocal);
			$ifclearhtml = unserialize($ifclearhtml);
			$modrule = array();
			while ($g = $db->fetch_array($modgather)) {
				$g['fieldrule'] =  stripslashes($fieldrule[$g['fieldid']]);
				$g['imgtolocal'] = $imgtolocal[$g['fieldid']] ? 'CHECKED' : '';
				$g['ifclearhtml'] = $ifclearhtml[$g['fieldid']] ? 'CHECKED' : '';
				foreach ($this->htmlMark as $mark){
					$g['htmlmark'].='<div style="width:15%; float:left;"><input type=checkbox name="clearhtml['.$g['fieldid'].'][]" value="'.$mark.'"';
					in_array($mark,$clearhtml[$g['fieldid']]) && $g['htmlmark'].="CHECKED ";
					$g['htmlmark'].='>'.$mark.'</div>';
				}
				$modrule[] = $g;
			}
			$str1 = unserialize($str1);
			$str2 = unserialize($str2);
			$strNum=-1;					//替换个数显示
			foreach($str1 as $k=>$val){
				$val && $strNum++;
				$str1[$k]=htmlspecialchars(stripslashes($val));
			}
			foreach($str2 as $k=>$val){
				$str2[$k]=htmlspecialchars(stripslashes($val));
			}
			$hottag = $this->hottag();
			ifcheck($multi,'multi');
			ifcheck($filtreit,'filtreit');
			extract($GLOBALS['checks']);
			require PrintEot('header');
			require PrintEot('mod_gather');
			adminbottom();
		}elseif ($step==2){
			$this->saveRule($_POST);
			adminmsg('operate_success');
		}
	}

	function saveRule(&$array){
		global $mid,$gid;
		extract($array,EXTR_SKIP);
		if(!$gid){
			$sql = "INSERT INTO cms_gather SET	mid='$mid',";
		}else {
			$sql = "UPDATE cms_gather SET ";
		}

		empty($gname) && Showmsg('gat_nogname');
		if(empty($fromurl) && empty($listurl)) Showmsg('gat_nofromurl');

		$gname = Char_cv($gname);
		$startpage = (int)$startpage;
		$endpage = (int)$endpage;
		$ignoretime = (int)$ignoretime;
		$tags = Char_cv($tags);
		$type = $type ? intval($type) : 0;
		$bindcid = (int)$bindcid;
		if($bindcid){
			global $catedb;
			if(!$mid){
				if($gid){
					$rt = $GLOBALS['db']->get_one("SELECT mid FROM cms_gather WHERE gid='$gid'");
					if($rt){
						$thismid = $rt['mid'];
					}else{
						Showmsg('action_fromciderror');
					}
				}else{
					Showmsg('action_fromciderror');
				}
			}else{
				$thismid = $mid;
			}
			$catedb[$bindcid]['mid']!=$thismid && Showmsg('action_fromciderror');
		}
		foreach ($fieldrule as $value){
			empty($value) && Showmsg('gat_emptyfield');
		}

		$fieldrule = addslashes(serialize($fieldrule));
		$clearhtml = addslashes(serialize($clearhtml));
		$imgtolocal = addslashes(serialize($imgtolocal));
		$ifclearhtml = addslashes(serialize($ifclearhtml));
		$str1 = $str2 = array();
		for ($i=1;$i<=10;$i++){
			$varname1 = 'str1_'.$i;
			$varname2 = 'str2_'.$i;
			$str1[$i] = $$varname1;
			$str2[$i] = $$varname2;
		}
		$str1 = addslashes(serialize($str1));
		$str2 = addslashes(serialize($str2));
		$sql.="
				gname='$gname',
				multi='$multi',
				fromurl='$fromurl',
				listarea='$listarea',
				listurl='$listurl',
				startpage='$startpage',
				endpage='$endpage',
				contenturl='$contenturl',
				debarurl='$debarurl',
				pageurl='$pageurl',
				tags='$tags',
				fieldrule='$fieldrule',
				ifclearhtml='$ifclearhtml',
				clearhtml='$clearhtml',
				imgtolocal='$imgtolocal',
				filtreit='$filtreit',
				ignoretime='$ignoretime',
				str1='$str1',
				str2='$str2',
				type='$type',
				bindcid='$bindcid'
		";
		if(!$gid){
			$sql .= " ";
		}else {
			$sql .= " WHERE gid='".$gid."'";
		}
		$GLOBALS['db']->update($sql);
	}

	function del(){
		global $db,$gid;
		!$gid && Showmsg('data_error');
		$rs = $db->get_one("SELECT mid FROM cms_gather WHERE gid='$gid'");
		!$rs && Showmsg('data_error');
		$mid = $rs['mid'];
		$rt = $db->query("SELECT c.tid FROM cms_collection c LEFT JOIN cms_contentindex i USING(tid) WHERE c.gid='$gid' AND i.cid=0");
		$tids = array();
		while ($g = $db->fetch_array($rt)) {
			$tids[]=(int)$g['tid'];
		}
		if(count($tids)>0){
			$tids = implode(',',$tids);
			$db->update("DELETE FROM cms_collection WHERE tid IN($tids)");
			$db->update("DELETE FROM cms_contentindex WHERE tid IN($tids) AND cid=0");
			$db->update("DELETE FROM cms_content{$mid} WHERE tid IN($tids)");
		}
		$db->update("DELETE FROM cms_gather WHERE gid='$gid'");
		exit('100');
	}

	function start(){
		global $gid;
		@set_time_limit(0);
		$testMod = (int)GetGP('testMod');
		$job = GetGP('job');
		!$job && $job = 'getlist';
		$config=$GLOBALS['db']->get_one("SELECT * FROM cms_gather WHERE gid='$gid'");
		!$config && Showmsg('data_error');
		$this->gather = new Gather($testMod);
		if($config['type']){
			$this->gatXML($config,$job);
		}else{
			if($job=='getlist'){
				$this->gatList($config); //根据配置信息进行采集动作
			}elseif ($job=='getcontent'){
				$this->gatContent($config);
			}
		}
	}

	function gatList($config){
		global $step;
		$tmpfile = GetGP('tmpfile');
		extract($config);
		!$step && $step=1;
		if($multi){
			$add = $step-1;
			$pagenow = $startpage+$add;
			$total = $endpage - $startpage+1;
			$url = str_replace("{DATA}",$pagenow,$listurl);
		}else{
			$key = $step-1;
			$urlArray = explode("\n",$fromurl);
			$total = count($urlArray);
			$url = $urlArray[$key];
		}
		$this->gather->_set('timeout',10);
		$this->gather->_set('total',$total);
		$this->gather->_set('step',$step);
		$this->gather->_set('action','list');
		$this->gather->_set('tmpfile',$tmpfile);
		$this->gather->startCount();
		$this->gather->open($url,1);
		$this->gather->getLinks($listarea,$contenturl,$debarurl);
		$this->gather->saveLinks();
		$this->gather->close();
		$this->gather->returnStat();
	}

	function gatContent($config){
		global $step;
		$tmpfile = GetGP('tmpfile');
		extract($config);
		$clearhtml = unserialize($clearhtml);
		$ifclearhtml = unserialize($ifclearhtml);
		$imgtolocal = unserialize($imgtolocal);
		$tags = $this->tags($tags);
		$this->gather->_set('timeout',10);
		$this->gather->_set('mid',$mid);
		$this->gather->_set('tmpfile',$tmpfile);
		$this->gather->_set('filtreit',$filtreit);
		$this->gather->_set('ignoretime',$ignoretime);
		$this->gather->_set('step',$step);
		$this->gather->_set('action','content');
		$this->gather->_set('pageurl',$pageurl);
		$this->gather->_set('clearhtml',$clearhtml);
		$this->gather->_set('ifclearhtml',$ifclearhtml);
		$this->gather->_set('imgtolocal',$imgtolocal);
		$this->gather->_set('tagsid',$tags);
		$this->gather->_set('bindcid',$bindcid);
		if($str1 && $str2){
			$str1 = unserialize($str1);
			$str2 = unserialize($str2);
			$this->gather->replace($str1,$str2);
		}
		$this->gather->startCount();
		$this->gather->readUrl($maxnum);
		$this->gather->getContent($fieldrule);
		$this->gather->returnStat();
	}

	function gatXML($config,$job){
		global $step;
		extract($config);
		$fieldrule = unserialize($fieldrule);
		foreach ($fieldrule as $key=>$value){
			$value = stripslashes($value);
			$value = explode('|',$value);
			$fieldrule[$key] = '{'.$value[0].'}';
		}
		$fieldrule['_XMLrule'] = $listarea;
		$key = $step-1;
		$urlArray = explode("\n",$fromurl);
		$total = count($urlArray);
		$url = trim($urlArray[$key]);
		if($job=='getcontent'){
			$this->gather->_set('timeout',10);
			$this->gather->_set('total',$total);
			$this->gather->_set('step',$step);
			$this->gather->_set('action','content');
			$this->gather->_set('mid',$mid);
			$this->gather->_set('filtreit',$filtreit);
			$this->gather->startCount();
			$this->gather->open($url);
			$this->gather->getXMLData($fieldrule);
			$this->gather->close();
			$this->gather->returnStat();
		}elseif($job=='getlist'){
			$this->gather->links = $urlArray;
			$this->gather->_set('timeout',10);
			$this->gather->_set('total',1);
			$this->gather->_set('step',1);
			$this->gather->_set('action','list');
			$this->gather->startCount();
			$this->gather->returnStat();
		}
	}

	function view(){
		global $db,$gid,$action,$basename;
		$page = (int)GetGP('page');
		$rs = $db->get_one("SELECT * FROM cms_gather WHERE gid='$gid'");
		if(!$rs) Showmsg('data_error');
		extract($rs);
		$ct = $db->get_one("SELECT COUNT(*) as count FROM cms_collection c LEFT JOIN cms_contentindex t USING(tid) WHERE c.gid='$gid' AND t.cid=0");
		$count = $ct['count'];
		$displaynum = 50;
		$numofpage = ceil($count/$displaynum);
		$page <=0 && $page=1;
		$start = ($page-1)*$displaynum;
		$pages = numofpage($count,$page,$numofpage,"$basename&action=view&gid=$gid&");
		$rs = $db->query("SELECT c.*,t.title,t.publisher FROM cms_collection c LEFT JOIN cms_contentindex t USING(tid) WHERE c.gid='$gid' AND t.cid=0 ORDER BY postdate DESC LIMIT $start,$displaynum");
		while ($c = $db->fetch_array($rs)){
			$c['title']=strip_tags(substrs($c['title'],60));
			$c['postdate']=get_date($c['gathertime']);
			$contentdb[] = $c;
		}

		require_once(D_P.'data/cache/cate.php');
		require_once(R_P.'require/class_cate.php');
		$cate = new Cate();
		$cate_select = $cate->tree();
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected",$cate_select);
		require PrintEot('header');
		require PrintEot('mod_gather');
		adminbottom();
	}

	function whole(){
		global $basename,$gid;
		extract(Init_GP(array('method','useall','tocid')));
		require(R_P.'require/class_action.php');
		if($method!='usegather' && $method!='delgather') exit();
		$action = new Action($method);
		$tids = array();
		if($useall){
			$rs = $GLOBALS['db']->query("SELECT t.tid FROM cms_collection c LEFT JOIN cms_contentindex t USING(tid) WHERE c.gid='$gid' AND t.cid=0");
			while ($t = $GLOBALS['db']->fetch_array($rs)){
				$tids[] = $t['tid'];
			}
		}else{
			$tids = GetGP('tids');
		}
		$action->target($tocid);
		$action->doIt($tids);
		if($method=='usegather'){
			adminmsg('mod_usegather',"$basename&action=view&gid=$gid");
		}else{
			adminmsg('operate_success',"$basename&action=view&gid=$gid");
		}
	}

	/**
	 * 删除单条采集结果
	 *
	 */
	function delgather(){
		global $mid,$tid,$gid,$basename;
		if(!$mid || !$tid || !$gid) Showmsg('data_error');
		$table = 'cms_content'.intval($mid);
		$GLOBALS['db']->update("DELETE FROM $table WHERE tid='$tid'");
		$GLOBALS['db']->update("DELETE FROM cms_collection WHERE tid='$tid'");
		adminmsg('operate_success',$basename.'&action=view&gid='.$gid);
	}

	/**
	 * 导出采集规则
	 *
	 */
	function export(){
		global $gid;
		$rs = $GLOBALS['db']->get_one("SELECT * FROM cms_gather WHERE gid='$gid'");
		if(!$rs)
		Showmsg('data_error');
		//$filename = 'gather'.$rs['gid'].'.txt';
		$filename = 'CMS_gat_'.randomStr(10).'.txt';
		if($rs['mid']!=1)
		unset($rs['mid']);
		unset($rs['gid']);
		$exportInfo = serialize($rs);
		$exportInfo = base64_encode($exportInfo);
		$filesize = strlen($exportInfo);
		ob_end_clean();
		//header('Cache-control: max-age=86400');
		//header('Last-Modified: '.gmdate('D, d M Y H:i:s',$GLOBALS['timestamp']+86400).' GMT');
		header('Pragma: no-cache');
		header('Content-Encoding: none');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-type: txt');
		header('Content-Length: '.$filesize);
		echo $exportInfo;
		exit();
	}

	/**
	 * 导入采集规则
	 *
	 */
	function import(){
		global $basename,$mid;
		if(empty($_FILES)) Showmsg('mod_nofile');
		if (empty($mid)) {
			Showmsg('mod_nomid');
		}
		foreach ($_FILES as $key=>$value){
			if($key!=='xmlfile') continue;
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
		require_once(R_P.'require/class_attach.php');
		$attach = new Attach();
		$attach->postupload($tmpfile,D_P.'data/'.$newname);
		$str = file_get_contents(D_P.'data/'.$newname);
		$importInfo = base64_decode($str);
		$importInfo = unserialize($importInfo);
		$importInfo['mid'] = intval($mid);
		$importInfo['fieldrule'] = unserialize($importInfo['fieldrule']);
		$importInfo['clearhtml'] = unserialize($importInfo['clearhtml']);
		$importInfo['imgtolocal'] = unserialize($importInfo['imgtolocal']);
		$importInfo['ifclearhtml'] = unserialize($importInfo['ifclearhtml']);
		$importInfo['str1'] = unserialize($importInfo['str1']);
		$importInfo['str2'] = unserialize($importInfo['str2']);
		Add_S($importInfo);
		foreach($importInfo['fieldrule'] as $key=>$val){
			$importInfo['fieldrule'][$key] = stripslashes($val);
		}
		for ($i=1;$i<=10;$i++){
			$importInfo['str1_'.$i] = $importInfo['str1'][$i];
			$importInfo['str2_'.$i] = $importInfo['str2'][$i];
		}
		$this->saveRule($importInfo);
		unlink(D_P.'data/'.$newname);
		adminmsg('mod_importok');
	}

	/**
	 * 修改采集规则名称
	 *
	 */
	 function editgname(){
	 }

	 function tags($tags){
		global $db;
		$tags = explode(',',$tags);
		array_splice($tags,5);
		$tagid = array();
		foreach($tags as $tag){
			$tag = trim($tag);
			if(!$tag){
				continue;
			}
			$rs = $db->get_one("SELECT tagid FROM cms_tags WHERE tagname='$tag'");
			if($rs){
				$tagid[] = $rs['tagid'];
			}else{
				$db->update("INSERT INTO cms_tags SET tagname='$tag',num=0");
				$tagid[] = $db->insert_id();
			}
		}
		return $tagid;
	}

	function hottag(){
		include(D_P.'data/cache/tagscache.php');
		$tmpText = "";
		foreach($hottags as $tag){
			$tmpText .= addslashes($tag['tagname']).',';
		}
		return $tmpText;
	}
}
InitGP(array('action','step','gid'));
$gid = (int)$gid;
$g = new GatherAdmin();
$g->doIt($action);
?>