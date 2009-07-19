<?php
!defined('IN_ADMIN') && die('Forbidden');

require_once(D_P.'data/cache/cate.php');
$action = GetGP('action');
!$action && $action = 'show';
$recycle = new Recycle();
$recycle->doIt($action);

/**
 * 回收站类
 *
 */
class Recycle{

	/**
	 * 显示回收站内容
	 *
	 */
	function Show(){
		global $db,$cid,$catedb,$basename;
		require_once(R_P.'require/class_cate.php');

		extract(Init_GP(array('num','type','keyword','page')));
		$num = (int)$num;
		$page = (int)$page;
		$type = (int)$type;
		$num<1 && $num=30;
		$page<1 && $page=1;
		empty($catedb[$cid]) && $cid = key($catedb);
		$cname = $catedb[$cid]['cname'];
		$sqladd = " r.cid='$cid' ";
		if($keyword && $type=='1'){//操作员
			$keyword = Char_cv($keyword);
			$sqladd .= " AND r.admin='$keyword' ";
		}elseif($keyword && $type=='2'){//删除时间
			$stime = strtotime($keyword);
			$etime = $stime+86400;
			$sqladd .= " AND r.deltime>'$stime' AND r.deltime<'$etime' ";
		}

		$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_recycle r WHERE $sqladd");
		$total = $rt['total'];
		$numofpage = ceil($total/$num);
		$url = "$basename&action=show&cid=$cid&num=$num&type=$type&keyword=$keyword&";
		$pages = numofpage($total,$page,$numofpage,$url);

		$startnum = $num*($page-1);
		$sqladd .= " ORDER BY r.deltime LIMIT $startnum,$num ";
		if($catedb[$cid]['mid']>0){
			$rs = $db->query("SELECT r.tid,r.deltime,r.admin,c.title,c.publisher FROM cms_recycle r LEFT JOIN cms_contentindex c USING(tid) WHERE $sqladd");
			while ($rt=$db->fetch_array($rs)) {
				$rt['title'] = htmlspecialchars($rt['title']);
				$rt['deltime'] = get_date($rt['deltime']);
				$content[] = $rt;
			}
		}/*elseif($catedb[$cid]['mid']=='-1'){
			Showmsg('undefined_action');
			$blog = new Blog();
		}elseif($catedb[$cid]['mid']=='-2'){
			Showmsg('undefined_action');
			$bbs = new BBS();
		}else{
			Showmsg('pub_nocontent');
		}*/
		$cate = new Cate();
		$cate_select = $cate->tree();
		$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected=\"selected\" ",$cate_select);
		${'type_'.$type} = 'selected=\"selected\"';
		require PrintEot('recycle');
		adminbottom();
	}

	/**
	 * 还原回收站项目
	 *
	 */
	function Undo(){
		global $sys,$cid,$db,$catedb;
		$tids = GetGP('tids');
		$type = GetGP('type');
		if($type=='all'){//还原回收站所有项目
			foreach($catedb as $k=>$v){
				if($v['mid']>0){
					$rs = $db->query("SELECT tid FROM cms_recycle WHERE cid='$v[cid]'");
					if($db->num_rows($rs)==0) continue;
					$num = $db->num_rows($rs);
					$tids = '';
					while($rt=$db->fetch_array($rs)){
						$tids .= $tids ? ','.$rt['tid'] : $rt['tid'];
					}
					$db->update("UPDATE cms_contentindex SET cid='$v[cid]' WHERE tid IN($tids) AND cid='-1'");
					$db->update("UPDATE cms_category SET new=new+$num,total=total+$num WHERE cid='$v[cid]'");
				}
			}
			$db->update("DELETE FROM cms_recycle");
		}else{//还原指定栏目项目
			if(empty($tids)){
				$rs = $db->query("SELECT tid FROM cms_recycle WHERE cid='$cid'");
				if($db->num_rows($rs)==0) adminmsg('operate_success');
				$num = $db->num_rows($rs);
				while($rt=$db->fetch_array($rs)){
					$tids .= $tids ? ','.$rt['tid'] : $rt['tid'];
				}
				$tidadd = " cid='$cid' ";
			}else{
				!is_array($tids) && $tids = array($tids);
				$num = count($tids);
				$tids = implode(',',$tids);
				$tidadd = " tid IN($tids) ";
			}
			if($catedb[$cid]['mid']>0){
				$db->update("UPDATE cms_contentindex SET cid='$cid',ifpub='0' WHERE tid IN($tids)");
				$db->update("UPDATE cms_category SET new=new+$num,total=total+$num WHERE cid='$cid'");
			}
			$db->update("DELETE FROM cms_recycle WHERE $tidadd");
		}
		adminmsg('operate_success');
	}

	/**
	 * 清空回收站项目
	 *
	 */
	function Del(){
		global $sys,$cid,$db,$catedb;
		$tids = GetGP('tids');
		$type = GetGP('type');
		if($type=='all'){//清空回收站所有项目
			foreach($catedb as $k=>$v){
				if($v['mid']>0){
					$rs = $db->query("SELECT tid FROM cms_recycle WHERE cid='$v[cid]'");
					if($db->num_rows($rs)==0) continue;
					$tids = '';
					while($rt=$db->fetch_array($rs)){
						$tids .= $tids ? ','.$rt['tid'] : $rt['tid'];
					}
					$table = 'cms_content'.$v['mid'];
					$db->update("DELETE FROM $table WHERE tid IN($tids)");
					$db->update("DELETE FROM cms_contentindex WHERE tid IN($tids)");
					$db->update("DELETE FROM cms_attachindex WHERE tid IN($tids)");
					$db->update("DELETE FROM cms_contenttag WHERE tid IN($tids)");
				}
			}
			$db->update("DELETE FROM cms_recycle");
		}else{//清空指定栏目项目
			if(empty($tids)){
				$rs = $db->query("SELECT tid FROM cms_recycle WHERE cid='$cid'");
				if($db->num_rows($rs)==0) adminmsg('operate_success');
				while($rt=$db->fetch_array($rs)){
					$tids .= $tids ? ','.$rt['tid'] : $rt['tid'];
				}
				$tidadd = " cid='$cid' ";
			}else{
				!is_array($tids) && $tids = array($tids);
				$tids = implode(',',$tids);
				$tidadd = " tid IN($tids) ";
			}
			if($catedb[$cid]['mid']>0){
				$table = 'cms_content'.$catedb[$cid]['mid'];
				$db->update("DELETE FROM $table WHERE tid IN($tids)");
				$db->update("DELETE FROM cms_contentindex WHERE tid IN($tids)");
				$db->update("DELETE FROM cms_attachindex WHERE tid IN($tids)");
				$db->update("DELETE FROM cms_contenttag WHERE tid IN($tids)");
			}
			$db->update("DELETE FROM cms_recycle WHERE $tidadd");
		}
		adminmsg('operate_success');
	}

	function doIt($action){
		switch($action){
			case 'show':
				$this->Show();
				break;
			case 'del':
				$this->Del();
				break;
			case 'undo':
				$this->Undo();
				break;
		}
	}
}
?>