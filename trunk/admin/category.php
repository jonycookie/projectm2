<?php
!defined('IN_ADMIN') && die('Forbidden');
require_once(D_P.'data/cache/cate.php');
if ($catedb[$cid]['mid']==-2 && $sys['aggrebbs']){
	require_once(R_P.'require/class_bbs.php');
}elseif ($catedb[$cid]['mid']==-1 && $sys['aggreblog']){
	require_once(R_P.'require/class_blog.php');
}
if($admin_name!=$manager && $admindb['privcate'] && $cid && !in_array($cid,$admindb['privcate'])){
	Showmsg('privilege');
}
$action = GetGP('action');
$action || $action = 'show';
$cate = new Category();
$cate->doIt($action);

class Category{
	/* 对栏目进行操作的类 */
	var $complete; //栏目群体操作的结束标志
	var $catedb;
	var $moduledb;
	var $method;

	function __construct(){
		global $catedb,$moduledb;
		$this->catedb = $catedb;
		$this->moduledb = $moduledb;
	}

	function Category(){
		$this->__construct();
	}

	function doIt($action='show'){
		$this->method = $action;
		switch ($action){
			case 'show':
				$up = GetGP('up','G');
				$this->show($up);
				break;
			case 'add':
				$this->add();
				break;
			case 'edit':
				$this->edit();
				break;
			case 'del':
				$this->del();
				break;
			case 'taxis':
				$this->taxis();
				break;
			case 'total':
				$this->total();
				break;
			case 'publist':
				$this->publist();
				break;
			case 'pubview':
				$this->pub();
				break;
			case 'pubupdate':
				$this->pub();
				break;
			case 'viewlist':
				$this->viewList();
				break;
			case 'selectTpl':
				$this->selectTpl();
				break;
			case 'pubindex':
				$front = GetGP('front');
				require_once(R_P.'require/class_action.php');
				$op = new Action('pubindex');
				$op->doIt();
				adminmsg('operate_success',$front);
				break;
			case 'batpub':
				$this->batpub();
				break;
			case 'pubstatistic':
				$this->pubstatistic();
				break;
			default:
				Showmsg('undefined_request');
				break;
		}
	}

	function show($cid=''){ //显示栏目结构
		global $db,$basename;
		if($cid){
			$cname = $this->catedb[$cid]['cname'];
			$sql = " WHERE up='$cid' ";
		}else{
			$cname = 'ROOT';
			$sql = " WHERE up='0' ";
		}
		$rs = $db->query("SELECT * FROM cms_category $sql ORDER BY taxis DESC");
		$categorydb = array();
		while ($tree = $db->fetch_array($rs)){
			$tree['up'] = $tree['up'] ? $this->catedb[$tree[up]]['cname'] : '------';
			$tree['module'] = $this->moduledb[$tree['mid']]['mname'];
			$categorydb[] = $tree;
		}
		require PrintEot('category');
		adminbottom();
	}

	function add(){ //添加新栏目
		global $action,$basename,$sys;
		$step = GetGP('step');
		$up = GetGP('up');
		if(!$step){
			$mod_select='';
			foreach ($this->moduledb as $key=>$m){
				if(!$sys['aggrebbs'] && $key==-2) continue;
				if(!$sys['aggreblog'] && $key==-1) continue;
				$mod_select.= "<option value=\"$key\">$m[mname]</option>";
			}
			require_once(R_P.'require/class_cate.php');
			$cate = new Cate();
			$cate_select = $cate->tree();
			if($up){
				if($this->catedb[$up]['link']) Showmsg('cate_linkadd');
				$mid = $up_mod=$this->catedb[$up]['mid'];
				$mod_select=str_replace("value=\"$up_mod\"","value=\"$up_mod\" selected",$mod_select);
				$cate_select=str_replace("value=\"$up\"","value=\"$up\" selected",$cate_select);
				//$ifchange="disabled=\"readonly\"";
			}else{
				$mid = 1; //default
				$mod_select=str_replace("value=\"1\"","value=\"1\" selected",$mod_select);
			}

			$bbsinfo['digest'] = $autopub = $comment = $display = $htmlpub = $listpub = 1;
			$bbsinfo['viewtype'] = 0;
			$copyctrl = 0;
			ifcheck($bbsinfo['digest'],'ifdigest');
			ifcheck($bbsinfo['viewtype'],'viewtype');
			ifcheck($htmlpub,'htmlpub');
			ifcheck($listpub,'listpub');
			ifcheck($autopub,'autopub');
			ifcheck($comment,'comment');
			ifcheck($copyctrl,'copyctrl');
			$type_1 = 'checked';
			extract($GLOBALS['checks']);
			require PrintEot('category');
			adminbottom();
		}elseif ($step==2){
			$this->saveCate();
			require_once(R_P.'require/class_cache.php');
			$cache = new Cache();
			$cache->cate();
			$cid = mysql_insert_id();
			$cache->singleCate($cid);
			if($mid && $_POST['listpub']){
				$refreshto = "$admin_file?adminjob=category&cid=$cid&action=publist";
				$msg = "cate_autopublist";
			}else{
				$refreshto = "$admin_file?adminjob=content&action=view&cid=$cid";
				$msg = "cate_addok";
			}
			operate($msg,$refreshto);
		}
	}

	function edit(){ //编辑栏目
		global $db,$basename,$action,$cid,$sys;
		$step = GetGP('step');
		$cateinfo = $db->get_one("SELECT * FROM cms_category WHERE cid='$cid'");
		if(!$step){
			@extract($cateinfo,EXTR_SKIP);
			if(!empty($mid)){
				$mod_name = $this->moduledb[$mid]['mname'];
				$mod_select = "<option value=\"$mid\" selected>$mod_name</option>";
			}
			if($sys['aggrebbs'] && $mid=='-2'){
				$bbsinfo=unserialize($addtion);
				ifcheck($bbsinfo['viewtype'],'viewtype');
				ifcheck($bbsinfo['digest'],'ifdigest');
				ifcheck($bbsinfo['taxis'],'taxis');
			}elseif ($sys['aggreblog'] && $mid=='-1'){
				$bloginfo=unserialize($addtion);
				ifcheck($bloginfo['viewtype'],'viewtype');
				ifcheck($bloginfo['digest'],'ifdigest');
				ifcheck($bloginfo['taxis'],'taxis');
			}
			$ifchange = "disabled=\"disabled\"";
			ifcheck($htmlpub,'htmlpub');
			ifcheck($autopub,'autopub');
			ifcheck($listpub,'listpub');
			ifcheck($comment,'comment');
			ifcheck($copyctrl,'copyctrl');
			$d = 'type_'.$type;
			$$d = 'checked=\"disabled\"';
			//ifcheck($display,'display');
			extract($GLOBALS['checks']);
			require_once(D_P.'data/cache/cate.php');
			require_once(R_P.'require/class_cate.php');
			$cate = new Cate();
			$cate_select = $cate->tree();
			$cate_select = str_replace("value=\"$up\"","value=\"$up\" selected=\"selected\"",$cate_select);

			require_once(R_P.'require/class_const.php');
			$const = new TplConst('CID');
			$vars = $const->getConstByValue($cid);
			require PrintEot('category');
			adminbottom();
		}elseif ($step==2){
			extract(Init_GP(array('filepath','htmlpub','oldmodule'),'P'));
			$this->saveCate();
			require_once(R_P.'require/class_cache.php');
			$cache = new Cache();
			$cache->cate();
			$cache->singleCate($cid);
			//如果动态改为静态，则需要重置所有内容为未发布
			if($filepath!=$cateinfo['filepath'] && $cateinfo['filepath']){
				$filepath = Pcv(R_P.$sys['htmdir'].'/'.$filepath);
				rename(R_P.$sys['htmdir'].'/'.$cateinfo['filepath'],$filepath);
				//如果遇到更名，则更名
			}
			if($cateinfo['htmlpub'] == 0 && $htmlpub == 1 && $oldmodule>0){
				$db->update("UPDATE cms_category SET new=total WHERE cid='$cid'");
				require_once(D_P.'data/cache/cate.php');
				$mid = $this->catedb[$cid]['mid'];
				$db->update("UPDATE cms_contentindex SET ifpub=0 WHERE cid='$cid' AND ifpub=1");
				operate('cate_needpuball',"$admin_file?adminjob=content&action=view&cid=$cid");
			}else{
				operate('cate_editok',"$admin_file?adminjob=content&action=view&cid=$cid");
			}
		}
	}

	function saveCate(){ //栏目信息的存储过程
		global $db,$action,$sys,$user_tplpath;
		extract($_POST,EXTR_SKIP);//
		$sqladd ='';
		empty($cname) && Showmsg('cate_nocname');
		if($action=='edit' && $oldmodule) $mid = $oldmodule;
		if($up){
			$this->catedb[$up]['link'] && Showmsg('cate_linkadd');
			if($cid == $up)
			{
				Showmsg('cate_uperror');
			}
			//$this->catedb[$up]['mid']!=$mid && Showmsg('cate_differentmid'); //子栏目必须和上级栏目内容模型保持一致
		}
		if($mid==0 || $oldmodule==0){ //外部调用
			empty($link) && Showmsg('cate_nolink');
			$link = Char_cv($link);
			$sqladd = ",link='$link',addtion=''";
			$mid = 0; //如果是外部调用,mid设置为0
		}elseif ($mid=='-2'){ //BBS调用
			if (!$sys['aggrebbs']) {
				Showmsg('mod_aggrebbs');
			}
			if($bbsinfo['fid'] && !ereg("^[0-9,]+$",$bbsinfo['fid'])) Showmsg('cate_fiderror');
			$addtion = addslashes(serialize($bbsinfo));
			$sqladd = ",link='',addtion='$addtion'";
		}elseif ($mid=='-1'){ //Blog调用
			if (!$sys['aggreblog']) {
				Showmsg('mod_aggreblog');
			}
			$blog = newBlog($sys['blog_type']);
			if($bloginfo['fid'] && !ereg("^[0-9,]+$",$bloginfo['fid'])) Showmsg('cate_fiderror');
			$blogfids = explode(',',$bloginfo['fid']);
			foreach($blogfids as $key=>$val) {
				if(!$val) {
					unset($blogfids[$key]);
				}
			}
			$blogfids = implode(',',$blogfids);
			$rs = $blog->mysql->query("SELECT catetype FROM {$blog->config['dbpre']}categories WHERE cid IN($blogfids)");
			while($rt = $blog->mysql->fetch_array($rs)) {
				$rt['catetype'] !='blog' && Showmsg('cate_blogerror');
			}
			unset($blog);
			$bloginfo['fid'] = $blogfids;
			$addtion = addslashes(serialize($bloginfo));
			$sqladd = ",link='',addtion='$addtion'";
		}else {
			$sqladd = ",link='',addtion=''";
		}

		$cname			= Char_cv($cname);
		$path			= Char_cv($path);
		$tpl_index		= Char_cv($tpl_index);
		$tpl_content	= Char_cv($tpl_content);
		$file_index		= Char_cv($file_index);
		$file_content	= Char_cv($file_content);
		$metakeyword	= Char_cv($metakeyword);
		$metadescrip	= Char_cv($metadescrip);
		$depth = $up ? $this->catedb[$up]['depth']+1 : 1;
		if($tpl_index && !file_exists($user_tplpath.'/'.$tpl_index)) Showmsg('cate_tplpatherror');
		if($tpl_content && !file_exists($user_tplpath.'/'.$tpl_content)) Showmsg('cate_tplpatherror');

		if(!$cid){
			$rs = $db->get_one("SELECT MAX(cid) as id FROM cms_category");
			$cid = $rs['id']+1;
		}
		if (substr($path,-1) == '/') {
			Showmsg('cate_patherror');
		}
		require_once(R_P.'require/class_const.php');
		$const = new TplConst('CID');
		$oldconst = $const->getConstByValue($cid);
		if($varname && $oldconst) {
			if($oldconst['name']!=$varname) {
				$vararray = array('id'=>$oldconst['id'],'title'=>$cname,'name'=>$varname,'value'=>$cid,'varid'=>$varid);
				$const->setConst($vararray);
			}
		}elseif($oldconst && !$varname) {
			$const->delConstByValue($cid);
		}elseif($varname && !$oldconst) {
				$vararray = array('title'=>$cname,'name'=>$varname,'value'=>$cid,'varid'=>$varid);
				$const->setConst($vararray);
		}
		
		if($type){ //不发布的栏目不需以下操作
			if($path){
				$filepath = $path.'/';
			}else {
				$filepath = $cid.'/';
			}
/*			if($listpub && $htmlpub){
				if(!is_dir(R_P.$sys['htmdir'].'/'.$filepath)){
					mkdir(R_P.$sys['htmdir'].'/'.$filepath);
					chmod(R_P.$sys['htmdir'].'/'.$filepath,0777);
					@fclose(@fopen(R_P.$sys['htmdir'].'/'.$filepath.'/index.html', 'w'));
					@chmod(R_P.$sys['htmdir'].'/'.$filepath.'/index.html', 0777);
				}
			}*/
			if($listpub){
				$listurl = $filepath.'index.'.$sys['htmext'];
			}else{
				$listurl = '';
			}
		}
		$sql = "cms_category SET
			cname='$cname',
			mid='$mid',
			up='$up',
			depth='$depth',
			path='$path',
			htmlpub='$htmlpub',
			listpub='$listpub',
			autopub='$autopub',
			comment='$comment',
			copyctrl='$copyctrl',
			listurl='$listurl',
			autoupdate='$autoupdate',
			type='$type',
			description='$description',
			tpl_index='$tpl_index',
			tpl_content='$tpl_content',
			file_index='$file_index',
			file_content='$file_content',
			metakeyword='$metakeyword',
			metadescrip='$metadescrip'
			$sqladd
		";
		if($action == 'add'){
			$sql = "INSERT INTO ".$sql.",cid='$cid'";
		}elseif ($action == 'edit'){
			$sql = "UPDATE ".$sql. "WHERE cid='$cid'";
		}
		$db->update($sql);
	}

	function taxis($taxis){ //栏目排序
		global $db;
		$taxis = GetGP('taxis');
		foreach ($taxis as $key=>$value){
			if(!is_numeric($value)){
				$value=(int)$value;
			}
			$db->update("UPDATE cms_category SET taxis='$value' WHERE cid='$key'");
		}
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		$cache->cate();
		operate('operate_success');
	}

	function total(){ //栏目统计
		global $db;
		require_once(R_P.'require/class_cache.php');
		$cache = new Cache();
		foreach ($this->catedb as $cid=>$v){
			if($v['mid']<=0){
				$db->update("UPDATE cms_category SET total=0,new=0 WHERE cid='$cid'");
				continue;
			}else{
				//$table = 'cms_content'.$v['mid'];
				$rs1 = $rs2 = array();
				$rs1 = $db->get_one("SELECT COUNT(*) AS count FROM cms_contentindex WHERE cid='$cid'");
				$rs2 = $db->get_one("SELECT COUNT(*) AS count FROM cms_contentindex WHERE cid='$cid' AND ifpub=0");
				$db->update("UPDATE cms_category SET total=$rs1[count],new=$rs2[count] WHERE cid='$cid'");
				$cache->singleCate($cid);
			}
		}
		$cache->cate();
		adminmsg('cate_total');
	}

	function publist(){ //发表分类首页
		global $cid,$page,$sys;
		$page = GetGP('page');
		require_once(R_P.'require/class_action.php');
		$op = new Action('publist');
		$op->cate($cid);
		if($op->catedb[$cid]['listpub'] && !$continue && !$page){
			if($op->catedb[$cid]['path']){
				$filepath = $sys['htmdir'].'/'.$op->catedb[$cid]['path'].'/';
			}else {
				$filepath = $sys['htmdir'].'/'.$cid.'/';
			}
			$fp = opendir(D_P.$filepath);
			while ($filename = readdir($fp)) {
				if($filename=='..' || $filename=='.') continue;
				if(strpos($filename,'index')!==false){
					@unlink(D_P.$filepath.$filename);
				}
			}
			closedir($fp);
		}
		$page = $op->doIt();
		if($page && (!$sys['listpage']||$page<=$sys['listpage'])){
			adminmsg('cate_publistcontinue',"$admin_file?adminjob=category&cid=$cid&action=publist&page=$page");
		}else{
			adminmsg('cate_publistok');
		}
	}

	function pub(){
		global $action,$cid,$db,$basename,$admin_file,$step;
		$step = (int)GetGP('step');
		$tids = $this->getTids($cid,$step);
		require_once(R_P.'require/class_action.php');
		$op = new Action($action);
		$op->cate($cid);
		$op->doIt($tids);
		if($this->complete){
			adminmsg('cate_pubcomplete',"$admin_file?adminjob=content&cid=$cid&action=view");
		}else{
			$step++;
			adminmsg('cate_pubcontinue',"$basename&action=$action&cid=$cid&step=$step");
		}
	}

	function del(){
		global $db,$cid,$action;
		$rs = $db->get_one("SELECT COUNT(*) AS count FROM cms_category WHERE up='$cid'");
		$rs['count']>0 && Showmsg('cate_delchildfirst');
		unset($rs);
		$mid = $this->catedb[$cid]['mid'];
		if($mid>0){
			$tids = $this->getTids($cid,$step);
			if(count($tids)>0){
				require_once(R_P.'require/class_action.php');
				$op = new Action('delete');
				$op->cate($cid);
				$op->doIt($tids);
			}else{
				$this->complete = 1;
			}
			if($this->complete){
				$db->update("DELETE FROM cms_category WHERE cid='$cid'");
				require_once(R_P.'require/class_const.php');
				$const = new TplConst('CID');
				$const->delConstByValue($cid);
				require_once(R_P.'require/class_cache.php');
				$cache = new Cache();
				$cache->cate();
				$cache->delCate($cid);
				operate('cate_pubcomplete');
			}else{
				$step++;
				adminmsg('cate_pubcontinue',"$basename&action=$action&step=$step");
			}
		}else{
			$db->update("DELETE FROM cms_category WHERE cid='$cid'");
			require_once(R_P.'require/class_const.php');
			$const = new TplConst('CID');
			$const->delConstByValue($cid);
			require_once(R_P.'require/class_cache.php');
			$cache = new Cache();
			$cache->cate();
			$cache->delCate($cid);
			operate('operate_success');
		}
	}

	function viewList(){ //查看列表首页
		global $sys;
		$cid = intval($_GET['cid']);
		if($this->catedb[$cid]['listpub']){
			$jumpurl = $sys['url'].'/'.$this->catedb[$cid]['listurl'];
		}else{
			$jumpurl = "list.php?cid=$cid";
		}
		echo "<script language=\"javascript\">";
		echo "window.location='$jumpurl';";
		echo "</script>";
		exit();
	}

	function getTids($cid,$step){ //根据步数来获取一段Tid进行操作
		global $db,$sys;
		!$cid && Showmsg('data_error');
		$mid = $this->catedb[$cid]['mid'];
		(!$step || $step<=0) && $step=1;
		$opnum = $sys['opnum'] ? intval($sys['opnum']) : 50;
		$start = ($step-1)*$opnum;

		$bbsblogAction = array('publist','pubview','pubupdate');
		if($mid < 0 && !in_array($this->method,$bbsblogAction)){
			Showmsg('action_bbsorblog');
		}
		if($mid == -2){
			$cidInfo = unserialize(stripslashes($this->catedb[$cid]['addtion']));
			!$cidInfo['viewtype'] && Showmsg('action_bbsorblog');
//			$bbs = new BBS();
			$bbs = newBBS($sys['bbs_type']);
			$this->bbs->cid = $cid;
			$bbs->readConfig($cidInfo);
			$rs = $bbs->getThread($start,$opnum);
			$tids = array();
			foreach ($rs as $val){
				$tids[] = $val['tid'];
			}
			unset($rs);
			$total = $bbs->total();
		}elseif ($mid == -1){
//			Showmsg('action_bbsorblog'); //当前不对Blog内容在CMS内生成页面
			$cidInfo = unserialize(stripslashes($this->catedb[$cid]['addtion']));
			!$cidInfo['viewtype'] && Showmsg('action_bbsorblog');
			$blog = newBlog($sys['blog_type']);
			$blog->readConfig($cidInfo);
			$blogcategory = $cidInfo['fid'];
			$rs = $blog->getBlog($blogcategory,$start,$opnum);
			$tids = array();
			foreach ($rs as $val){
				$tids[] = $val['tid'];
			}
			unset($rs);
			$total = $blog->total();
		}else {
			$rt = $db->get_one("SELECT COUNT(*) AS total FROM cms_contentindex WHERE cid='$cid'");
			$total = $rt['total'];
			$rs = $db->query("SELECT tid FROM cms_contentindex WHERE cid='$cid' LIMIT $start,$opnum");
			$tids = array();
			while ($t = $db->fetch_array($rs)) {
				$tids[] = $t['tid'];
			}
		}
		$GLOBALS['total'] = $total;
		$GLOBALS['opnum'] = $opnum;
		if($start+$opnum>=$total){
			$this->complete = 1; //表示已经获取完毕
		}else{
			$this->complete = 0; //没有结束
		}
		return $tids;
	}

	function selectTpl(){ //为栏目选择模板
		global $user_tplpath,$basename;
		extract(Init_GP(array('direct','inputname','job')));
		require_once(R_P.'require/class_path.php');
		$p = new path(D_P.$user_tplpath);
		$p->viewurl = "$basename&action=selectTpl&inputname=$inputname&";
		$p->fileurl = "insertTpl";
		$p->setDir($direct);
		if($job=='up') $p->up();
		$files = $p->getDir();
		$direct = $p->currentpath;
		require PrintEot('selecttpl');
		adminbottom(0);
	}

	function batpub(){
		global $basename,$page,$cid,$sys;
		$job  = GetGP('job');
		$step = GetGP('step');
		empty($job) && $job = 'list';
		if($job=='list'){
			$basename = "$basename&action=batpub&job=list";
			if(!$step){
				global $cid;
				require_once(R_P.'require/class_cate.php');
				$cate = new Cate();
				$cate_select = $cate->tree();
				$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
				$catedb = $cate->tree($cid);
				require PrintEot('batpub');
				adminbottom();
			}else{
				$cids = GetGP('cids');
				$page = GetGP('page');
				$key = key(current($cids));
				$cid = $cids[$key];
				if(is_numeric($cid)){
					require_once(R_P.'require/class_action.php');
					$op = new Action('publist');
					$op->cate($cid);
					if($op->catedb[$cid]['listpub'] && !$continue && !$page){
						if($op->catedb[$cid]['path']){
							$filepath = $sys['htmdir'].'/'.$op->catedb[$cid]['path'].'/';
						}else {
							$filepath = $sys['htmdir'].'/'.$cid.'/';
						}
						$fp = opendir(D_P.$filepath);
						while ($filename = readdir($fp)) {
							if($filename=='..' || $filename=='.') continue;
							if(strpos($filename,'index')!==false){
								@unlink(D_P.$filepath.$filename);
							}
						}
						closedir($fp);
					}
					$page = $op->doIt();
					if(empty($page) || $page>$sys['listpage']){
						$page = 1;
						unset($cids[$key]);
					}
				}else{
					unset($cids[$key]);
				}
				if($cids){
					$cids = implode('&cids[]=',$cids);
					adminmsg('cate_batcontinue',"$basename&cids[]=$cids&step=1&page=$page");
				}else{
					adminmsg('cate_batcomplete');
				}
			}
		}elseif($job=='view'){
			$basename = "$basename&action=batpub&job=view";
			if(!$step){
				global $cid;
				require_once(R_P.'require/class_cate.php');
				$cate = new Cate();
				$cate_select = $cate->tree();
				$cate_select = str_replace("value=\"$cid\"","value=\"$cid\" selected ",$cate_select);
				$catedb = $cate->tree($cid);
				require PrintEot('batpub');
				adminbottom();
			}else{
				global $db;
//				print_r($step);flush();exit;
				extract(Init_GP(array('cids','stime','etime','do','count')));
				$count = (int)$count;
				$tids = array();
				$st = $stime ? PwStrtoTime($stime) : 0;
				$et = $etime ? PwStrtoTime($etime) : 0;
				$key = key($cids);
				$cid = $cids[$key];
				$sql = " i.cid='$cid' ";
				$st && $sql .= " AND i.postdate>'$st' ";
				$et && $sql .= " AND i.postdate<'$et' ";
				if($do=='pubview'){
					$sql .= " AND ifpub='0' ";
					$start = 0;
				}else{
					$sql .= " AND ifpub='1' ";
					$start = $count*30;
				}

				$rs = $db->query("SELECT i.tid FROM cms_contentindex i WHERE $sql LIMIT $start,30");
				while($rt=$db->fetch_array($rs)){
					$tids[] = $rt['tid'];
				}
				if(is_numeric($cid) && $tids){
					require_once(R_P.'require/class_action.php');
					$action = new Action($do);
					$action->cate($cid);
					$action->doIt($tids);
					$count++;
				}else{
					unset($cids[$key]);
					$count = 0;
				}
				if($cids){
					$cids = implode('&cids[]=',$cids);
					adminmsg('cate_batcontinue',"$basename&cids[]=$cids&step=1&stime=$stime&etime=$etime&do=$do&count=$count");
				}else{
					adminmsg('cate_batcomplete');
				}
			}
		}
	}
	function pubstatistic() {
		global $cid,$db;
		require_once(R_P.'require/class_cache.php');
		$new	= $db->get_one("SELECT count(*) as new FROM cms_contentindex WHERE cid='$cid' AND ifpub=0");
		$total	= $db->get_one("SELECT count(*) as total FROM cms_contentindex WHERE cid='$cid'");
		$new['new']	= $new['new']?$new['new']:0;
		$total['total']	= $total['total']?$total['total']:0;
		$db->update("UPDATE cms_category SET new='$new[new]',total='$total[total]' WHERE cid='$cid'");
		Cache::writeCache('cate');
		adminmsg('operate_success','admin.php?adminjob=content');
	}
}
?>