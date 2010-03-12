<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

/*
顶/踩
*/
function king_ajax_digg(){
	global $king;
	$kid=kc_get('kid',2,1);
	$type=intval(kc_get('type',2,1));
	if($type<0 || $type>2) return;
	$modelid=kc_get('modelid',22,1);
	$model=$king->portal->infoModel($modelid);
	$digtime=kc_cookie("digtime_{$modelid}_{$kid}");//获得上次操作时间
	$js="\$('#k_digg').attr('title','".$king->lang->get('portal/error/nodigg')."');\$('#k_digg1').removeAttr('onclick');\$('#k_digg0').removeAttr('onclick');";//
	if($type<2){
		if($res=$king->db->getRows_one("select ndigg{$type} from %s__{$model['modeltable']} where kid=$kid")){
			$digg=$res['ndigg'.$type];
			if($digtime<time()-86400){
				setcookie("digtime_{$modelid}_{$kid}",time(),time()+86400,'/');//设置操作时间
				$digg++;
				$_array=array(
					'ndigg'.$type=>$digg,
				);
				$king->db->update('%s__'.$model['modeltable'],$_array,"kid=$kid");
				$js.="\$('#k_digg{$type}').html($digg);";
			}else{
				kc_ajax($king->lang->get('system/common/tip'),$king->lang->get('portal/error/nodigg'),0,$js);
			}
		}else{
			kc_error($king->lang->get('portal/error/notq'));//找不到记录
			return;
		}
	}elseif($res=$king->db->getRows_one("select ndigg1,ndigg0 from %s__{$model['modeltable']} where kid=$kid")){
		if($digtime<time()-86400){
			$js='';
		}
		$digg=$res['ndigg1'];
		$js.="\$('#k_digg1').html($digg);";
		$digg=$res['ndigg0'];
		$js.="\$('#k_digg0').html($digg);";
	}
	
	kc_ajax('','','',$js);
}

/*
内容页访问统计
*/
function king_ajax_hit(){
	global $king;
	//在增加hit统计的时候，不要每次都对数据库进行更新，而是累计20次后一次性进行更新。
	$kid=kc_get('kid',2,1);
	$modelid=kc_get('modelid',22,1);
	$cachepath='portal/hit';

	if(!$array=$king->cache->get($cachepath)){//若读取的是空缓存，则需要设置一个$array['count']默认值，否则下面+1运算的时候出现警告
		$array['count']=0;
	}
	$hittime=kc_cookie("hittime_{$modelid}_{$kid}");//上次访问时间
	if(isset($array[$modelid.'|'.$kid])){//若有数组，则++
		if($hittime<time()-86400){//上次评论时间超过1天则计数并更新Cookies
			$array[$modelid.'|'.$kid]['count']++;
			setcookie("hittime_{$modelid}_{$kid}",time(),time()+86400,'/');
			$array['count']+=1;
		}
		$nhit=($array[$modelid.'|'.$kid]['count'] + $array[$modelid.'|'.$kid]['nhit']);
	}else{
		$model=$king->portal->infoModel($modelid);
		if($res=$king->db->getRows_one("select nhit from %s__{$model['modeltable']} where kid=$kid")){
			$hit=$res['nhit'];
		}else{
			return;
		}
		setcookie("hittime_{$modelid}_{$kid}",time(),time()+86400,'/');//清空缓存后没人访问过的状态直接写Cookies
		$array[$modelid.'|'.$kid]=array('nhit'=>$hit,'count'=>1);
		$array['count']+=1;
		$nhit=$hit+1;
	}
	//循环更新数据
	if($array['count']>5){//这个值过大的话，SQLite会出错。
		foreach($array as $key => $val){
			list($modelid,$kid)=explode('|',$key);//重新获得modelid和kid，和上面无任何关系
			if(kc_validate($kid,2)){
				$model=$king->portal->infoModel($modelid);
				
				$_array=array(
					'nhit'=>"[[nhit+{$val['count']}]]",
					'nhitlate'=>"[[(nhitlate*nhit+".(time()*$val['count']).")/(nhit+{$val['count']})]]",
				);
				$king->db->update('%s__'.$model['modeltable'],$_array,"kid=$kid");
				kc_f_delete($king->config('xmlpath','portal').'/portal/'.$modelid.'/'.wordwrap($kid,1,'/',1).'.xml');
				//$array[$key]=array('nhit'=>$val['nhit']+$val['count'],'count'=>0);
			}
		}
//		kc_error('cc');
		//清空array
		$king->cache->del($cachepath);
	}else{
		$king->cache->put($cachepath,$array);
	}
//kc_error(nl2br(print_r($array,1)));

	$js="\$('#k_hit').html($nhit);";

	kc_ajax('','','',$js);
}

/*
 评论统计
*/
function king_ajax_commentcount(){
	global $king;
	
	$kid=kc_get('kid',2,1);
	$modelid=kc_get('modelid',22,1);
	$model=$king->portal->infoModel($modelid);
	if($res=$king->db->getRows_one("select ncomment from %s__{$model['modeltable']} where kid=$kid")){
		$ncomment=$res['ncomment'];
		$js="\$('#k_comment').html($ncomment);";
		kc_ajax('','','',$js);
	}else{
		kc_error($king->lang->get('portal/error/notq'));
		return;
	}
}
/*
 评论
*/
function king_ajax_comment(){
	global $king;
	
	$kid=kc_get('kid',2,1);
	$modelid=kc_get('modelid',22,1);
	$kcontent=kc_get('kcontent',0,1);
	$commenttime=kc_cookie("commenttime");
	if($commenttime<time()-120){//限制2分钟内只能发一次评论
		setcookie("commenttime",time(),time()+86400,'/');
	}else{
		kc_error($king->lang->get('portal/tip/nocomment'));
	}
	if(kc_strlen($kcontent)>10){
		$kcontent=preg_replace('/<a ([^>]*)>|<\/a>/is','',$kcontent);//过滤链接
		$kcontent=preg_replace('/<(table|tbody|thead|tr|td|th|caption) ?([^>]*)>|<\/(table|tbody|thead|tr|td|th|caption)>/is','',$kcontent);//过滤表格
		$kcontent=preg_replace('/(<([^>]*))( style=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$kcontent);//过滤样式
		$kcontent=preg_replace('/(<([^>]*))( id=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$kcontent);
		$kcontent=preg_replace('/(<([^>]*))( class=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$kcontent);
	}
	if(kc_strlen($kcontent)<5){
		kc_ajax($king->lang->get('system/title/tip'),$king->lang->get('portal/tip/nocontent'));
		return;
	}
	$model=$king->portal->infoModel($modelid);
	if($res=$king->db->getRows_one("select ncomment from %s__{$model['modeltable']} where kid=$kid")){
		$ncomment=$res['ncomment']+1;
		$_array=array(
			'ncomment'	=>$ncomment,
		);
		$king->db->update('%s__'.$model['modeltable'],$_array,"kid=$kid");
	}else{
		kc_error($king->lang->get('portal/error/notq'));
		return;
	}
	$king->load('user');
	if($user=$king->user->checkLogin()){//已登录
		$username=$user['username'];
		unset($user);
	}else{//未登录
		$username='';
	}
	$_array=array(
		'kid'		=>$kid,
		'modelid'	=>$modelid,
		'kcontent'	=>$kcontent,
		'username'	=>$username,
		'nip'		=>kc_getip(),
		'ndate'		=>time(),
		'isshow'	=>1,
	);
	$king->db->insert("%s_comment",$_array);
	$xmlpath=$king->config('xmlpath','portal').'/portal/'.$modelid.'/'.wordwrap($kid,1,'/',1).'.xml';
	kc_f_delete($xmlpath);
	$cachepath='portal/comment/'.$modelid.'/'.$kid;
	$king->cache->del($cachepath);
	$js="\$('#k_comment').html($ncomment);$('#kcontent').html('');";
	kc_ajax('OK','<p class="k_ok">'.$king->lang->get('portal/ok/submit').'</p>',0,$js);
}




function king_edt(){
	global $king;

	$king->load('user');

	//初始化zf
	$listid=kc_get('listid',2,1);//$info['listid'];
	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);

	$kid=kc_get('kid',2);
	$kid1=kc_get('kid1',2);
	$isuser=$kid1?'isuser2':'isuser1';//次页:首页
	$array_field=array_keys($model['field'][$isuser]);
	$sql_field=implode(',',$array_field);//[tablemodel]字段调用
	//读取管理员列表
	$editors=$king->portal->getListEditor($listid);
	if(!is_array($editors)) $editors=array();
	//用户权限及登录验证
	if($info['gidpublish'] == -1 ){
		$user=array(
			'userid'=>0,
			'username'=>'['.$king->lang->get('user/name/guest').']',
		);
	}else{
		$user=$king->user->access();
		if(!in_array($king->user->userid,$editors) && $info['gidpublish']){//非栏目编辑 并 限制组会员 ；栏目编辑员则跳过此验证
			$king->user->access($info['gidpublish']);
		}
	}

	//发帖验证，检查是否为不允许发布
	//0不允许发布|1直接发布|2验证后发布
	if(!in_array($user['userid'],$editors)){//栏目编辑员无需验证

		if((int)$info['ispublish'.($kid1? 2 : 1)]===0){
			$king->portal->error($king->lang->get('portal/title/stop'),$king->lang->get('portal/error/stop'));
		}
	}
	//当kid有值的时候(编辑)，进行所有权验证
	if($kid){
		$user=empty($user) ? $king->user->access() : $user;//如果$user为null则进行登录验证，目的是要获得userid
		$id=$king->portal->infoID($listid,$kid);
		if(in_array($king->user->userid,$editors) || (int)$id['userid']===(int)$king->user->userid){
			if(!in_array($king->user->userid,$editors)){//如果不是栏目管理员，则进行有效期验证
				if((time() - $model['nlocktime']*3600 > $id['ndate']) && (int)$model['nlocktime']!==0){//如果超过可允许编辑时间期限 并 可编辑时间不能为0，则提示错误
					$king->portal->error($king->lang->get('portal/title/stop'),$king->lang->get('portal/error/timeout'));
				}
			}
		}else{
			$king->portal->error($king->lang->get('portal/title/stop'),$king->lang->get('portal/error/noaccess'));
		}
	}

	$fields=explode(',',$sql_field);
	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['kpath']=$king->portal->depathMode($info);

			$data['nshow']=1;
			$array_field_default=$model['field']['default'];
			foreach($array_field_default as $key => $val){
				$data[$key]=$val;
			}

			//从URL中获取初始值
			foreach($fields as $val){
				if(isset($_GET[$val])){
					$data[$val]=$_GET[$val];
				}
			}
		}
	}else{//编辑数据，从数据库读出
		//判断是否为栏目编辑 或 所有人
		if(!$data=$king->db->getRows_one('select '.$sql_field.' from %s__'.$model['modeltable'].' where kid='.$kid.' limit 1;'))
			kc_error($king->lang->get('system/error/param').'<br/>select '.$sql_field.' from %s__'.$model['modeltable'].' where kid='.$kid.' limit 1;'.'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);

	}
	$data=kc_data($fields,$data);

	$data['kid']=$kid;

	if(!$res=$king->db->getRows("select * from %s_field where modelid={$info['modelid']} and {$isuser}=1 and kid1=0 order by norder,kid;"))//全部调用
		$res=array();

	$s=$king->openForm('index.php?action=edt');
	$s.=kc_htm_hidden(array('listid'=>$listid,'kid'=>$kid,'kid1'=>$kid1));//这个隐藏域不要放在下面

	foreach($res as $rs){
		$s.=$king->portal->formdecode($rs,$data,$info,0,($kid1?2:1));
	}

	$s.=$king->closeForm($king->lang->get('system/common/publish'));

	//数据处理
	if($GLOBALS['ischeck']){

		$_array=array();//设置为空数组

		//收集字段的值
		foreach($array_field as $val){
			if(substr($val,0,1)=='n'){
				$_array[$val] = $data[$val] ? 1:0;
			}else{
				if(is_array($data[$val])){
					$_array[$val]=implode(',',$data[$val]);
				}else{
					$_array[$val]=$data[$val];
				}
			}
		}

		//listid & kid1
		$_array['listid']=$data['listid'];
		$_array['kid1']=$data['kid1'] ? $data['kid1'] : 0;

/**
		检查kpath是否在键名列表里，如果有则判断是否为空值
		如果没有，则补充
*/
		if(empty($_array['kpath'])){
			$_array['kpath']=$king->portal->depathMode($info);
		}
/**
		检查kkeywords，如果没有，则自动补充其值
		如果有，则更新列表
*/
		$_array['kkeywords']= !empty($data['kkeywords']) ? $king->portal->getKey($_array['ktitle'],$_array['kkeywords']) : $king->portal->getKey($_array['ktitle']);
/**
		检查ktag，如果没有，则自动补充其值
		如果有，则更新列表
*/
		$_array['ktag']= !empty($data['ktag']) ? $king->portal->getTag($_array['ktitle'],$_array['ktag']) : $king->portal->gettag($_array['ktitle']);
/**
		如果description值为空，则从content中获取
*/
		if(empty($data['kdescription']) && !empty($data['kcontent'])){
			$kdescription=strip_tags($data['kcontent']);
			$kdescription=preg_replace('/\&[a-z]{1,6};/','',$kdescription);
			$_array['kdescription']=kc_substr($kdescription,0,200);
		}

		//副标题长度
		$_array['nsublength']=isset($data['ksubtitle']) ? kc_strlen($data['ksubtitle']) :0;

		//更新时间
		$_array['nlastdate']=time();
		//如果有kid1值，则对kid1对应的nlastdate进行更新
		if($kid1){
			$king->db->update('%s__'.$model['modeltable'],array('nlastdate'=>time()),'kid='.$kid1.' limit 1');
		}

		//添加&更新数据
		if($kid){//update
			$king->db->update('%s__'.$model['modeltable'],$_array,'kid='.$kid);
			$_nlog=7;
		}else{
			$_array['ndate']=time();
			$_array['norder']=$king->db->neworder('%s__'.$model['modeltable']);
			$_array['userid']=$user['userid'];
			$_array['nshow']=$info['ispublish'.($kid1?2:1)];
			$kid=$king->db->insert('%s__'.$model['modeltable'],$_array);
			$_nlog=5;
			if($kid==0){
				kc_error($king->lang->get('system/error/insert').kc_clew(__FILE__,__LINE__,nl2br(print_r($_array,1))));
			}

		}

		//更新列表信息
		$king->portal->lastUpdated($listid,'list');

		//删除缓存重建缓存
		$king->cache->del('portal/list/'.$listid);
		kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml');
		$id=$king->portal->infoID($listid,$kid);
		if($kid1){
			kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid1,1,'/',1).'.xml');
			$id=$king->portal->infoID($listid,$kid1);
		}

		//生成操作
		if($info['npage']==0){
			if($info['npagenumber']==1){
				$king->portal->createPage($listid,($kid1?$kid1:$kid));//$listid,$kid,$pid=1,$is=null
				$subkid=$id['subkid'];
				if(isset($subkid)){
					$subid=explode(',',$subkid);
					foreach($subid as $sid){
						$king->portal->createPage($listid,$sid);
					}
				}
			}else{
				$pcount=ceil($id['ncount']/$info['npagenumber']);
				for($i=1;$i<=$pcount;$i++){
					$king->portal->createPage($listid,($kid1?$kid1:$kid),$i);
				}
			}
		}

		//跳转,当留言反馈类型的时候，这个跳转需要改动
		echo "<script type=\"text/javascript\">parent.location='".$king->portal->pathPage($info,$id['kid'],$id['kpath'])."'</script>";
	}

	$tmp=new KC_Template_class($model['ktemplatepublish']);

	$tmp->assign('inside',$s);
	$tmp->assign('listid',$listid);
	$tmp->assign('title',$info['klistname']);
	$tmp->assign('type','edit');

	echo $tmp->output();

//	list($left,$right)=king_inc_list();
//	$king->skin->output($king->lang->get('portal/title/content'.($kid?'edt':'add')),$left,$right,$s);

}








?>