<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_ajax_openlist(){
	global $king;
	$king->access('portal');

	$is=kc_get('is',2,1);
	$space=kc_get('space',2,1);
	$ID=kc_get('ID',4,1);
	$listid=substr($ID,4);//获得listid

	if(!kc_validate($listid,2)) kc_error($king->lang->get('system/error/param'));

	$s=kc_icon($is ? 'l1':'k1');

	$js="\$('#$ID').attr('rel','{CMD:\'openlist\',is:".(1-$is).",ID:\'$ID\',IS:2,listid:$listid,space:$space}');";

	if($is){
		if($res=$king->db->getRows("select listid,isexist from %s_list where listid1=$listid order by norder desc,listid desc;")){

			$array=array();
			$isopen='';
			if(isset($_COOKIE['portal_isopen'])){
				$array=explode(',',$_COOKIE['portal_isopen']);
				$isopen=$_COOKIE['portal_isopen']. (in_array($listid,$array) ? '' : ','.$listid);
			}else{
				$isopen=$listid;
			}

			foreach($res as $rs){
				$info=$king->portal->infoList($rs['listid']);
				$str=$king->tdList(array(
					$info['listid'],
					$info['modelid'],
					$info['klistname'],
					kc_getlang($info['klanguage']),
					$space+1,//nspace
					$info['isexist'],
					$info['ncount'],
					$info['ncountall'],
					addslashes($king->portal->pathList($info)),
				),2);

				$js.="\$('#tr_{$listid}').after($str);\$.kc_ready('#tr_{$info['listid']}');";
				if($rs['isexist']==1 && in_array($rs['listid'],$array)){
					$js.="\$.kc_ajax('{CMD:\'openlist\',is:1,ID:\'ico_{$rs['listid']}\',IS:2,listid:{$rs['listid']},space:".($space+1)."}');";
				}

				unset($info);
			}

		}else{//当没有下级栏目的时候，删掉
			$king->cache->del('portal/list/'.$listid);
			kc_ajax('',kc_icon(''));
		}
	}else{
		$js.="tr_remove($listid,$space);";//\$('#tr_{$listid} ~ tr').remove();

		$array=explode(',',$_COOKIE['portal_isopen']);
		$array=array_diff($array,array($listid));//删掉当前的listid
		$isopen=implode(',',$array);
	}
	kc_setCookie('portal_isopen',$isopen,86400*366);//写isopen值

	kc_ajax('',$s,'',$js);

}
function king_ajax_delete(){
	global $king;
	$king->access('portal_list_delete');

	$_list=kc_getlist();
	$_array=explode(',',$_list);

	foreach($_array as $val){
		$listid=$val['listid'];
		$info=$king->portal->infoList($listid);
		$model=$king->portal->infoModel($info['modelid']);
		//删除栏目下面的内容
		if($info['modelid']>0){
			if($info['npage']==0){//删除静态页面
				if(!$res=$king->db->getRows("select kid,kpath from %s__{$model['modeltable']} where listid=$listid;"))
					$res=array();
				foreach($res as $rs){
					//删除文件，不包含目录，目录用清理空目录的方式去处理
					kc_f_delete($king->getfpath($rs['kpath']));

					//删除缓存文件
					$king->cache->del('portal/_'.$model['modeltable'].'/'.ceil($rs['kid']/1024).'/'.$rs['kid']);
				}
			}

			//删除数据库中的内容
			$king->db->query("delete from %s__{$model['modeltable']} where listid=$listid;");

			//删除单页文件列和表文件
			kc_f_delete($king->getfpath($info['klistpath']));
			kc_f_rd($info['klistpath']);
		}

		//删除缓存文件
		$king->cache->del('portal/list/'.$listid);

		//写log
		$king->log(6,'ListName:'.$info['klistname']);
	}

	$king->db->query("delete from %s_list where listid in ($_list) and isexist=0;");

	//删除缓存
	$king->cache->del('portal/list/id');
	$king->cache->rd('system/cache');

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

} //!king_ajax_delete
 //调用%s_field.khelp
//king_ajax_help
function king_ajax_help(){
	global $king;
	$king->access(0);
	$kid=kc_get('kid',2,1);

	if(!$res=$king->db->getRows_one("select khelp from %s_field where kid=$kid;"))
		kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	$help=nl2br($res['khelp']);
	kc_ajax('',$help);

} //!king_ajax_help
/**
	编辑绑定域名信息
	king_ajax_site()
*/
function king_ajax_site(){
	global $king;

	$king->access('portal_site_edt');

	$siteid=kc_get('siteid',2);
	$is=kc_get('is')?1:0;//是否更新option
	$width=450;
	$height=120;
	$js='';

	if($GLOBALS['ismethod']||$siteid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['siteurl']='http://';
		}
	}else{	//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one('select sitename,siteurl from %s_site where siteid='.$siteid.' limit 1;'))
			kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	$data=kc_data(array('sitename','siteurl'),$data);

	$s='<div>';

	if($siteid){
		$act='edit';
		$sql="select siteid from %s_site where siteurl='".$king->db->escape($data['siteurl'])."' and siteid<>{$siteid};";
	}else{
		$act='add';
		$sql="select siteid from %s_site where siteurl='".$king->db->escape($data['siteurl'])."';";
	}
	//sitename
	$array=array(
		array('sitename',0,1,100),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/site/name'),'<input type="text" maxlength="100" id="sitename" class="k_in w400" value="'.$data['sitename'].'"/>',$array);
	//siteurl
	if(empty($data['siteurl'])){
		$array=array(
			array('sitename',12,$king->lang->get('system/check/none'),$king->db->getRows_one($sql)),
		);
	}else{
		$array=array(
			array('siteurl',0,1,100),
			array('siteurl',6),
			array('siteurl',12,$king->lang->get('portal/check/siteurl'),substr($data['siteurl'],-1,1)=='/'),
			array('siteurl',12,$king->lang->get('system/check/none'),$king->db->getRows_one($sql)),
		);
	}
/*
	if(isset($siteid{0})){
		$array[]=array('siteurl',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select siteid from %s_site where siteurl='".$king->db->escape($data['siteurl'])."' and siteid<>$siteid"));
	}else{
		$array[]=array('siteurl',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select siteid from %s_site where siteurl='".$king->db->escape($data['siteurl'])."'"));
	}
*/
	$is
		? $link='<a href="manage.php?action=site">'.$king->lang->get('portal/title/site').'</a>'
		: $link='';
	$s.=$king->htmForm($king->lang->get('portal/label/site/url'),'<input type="text" id="siteurl" class="k_in w400" value="'.$data['siteurl'].'" />',$array);

	$but=kc_htm_a($king->lang->get('system/common/'.$act),"{CMD:'site',is:$is,siteid:'$siteid',VAL:'sitename,siteurl',IS:1}").$link;
	$s.='</div>';

	if($GLOBALS['ischeck']){
		$_array=array(
			'sitename'=>$data['sitename'],
			'siteurl'=>$data['siteurl'],
		);

		if($siteid){//update
			$king->db->update('%s_site',$_array,'siteid='.$siteid);
			$_nlog=7;
			$js='setTimeout("parent.location=\'manage.php?action=site\'",1000)';
			$act='edt';
		}else{//insert
			$newsiteid=$king->db->insert('%s_site',$_array);

			$_nlog=5;

			if($is){
				$js='setTimeout("addsite('.$newsiteid.',\''.addslashes($data['sitename']).'\');kc_display(\'k_ajax\')",1000)';
			}else{
				$js='setTimeout("parent.location=\'manage.php?action=site\'",1000)';
			}
			$act='add';
		}

		//写log
		$king->log($_nlog,$king->lang->get('portal/label/site/name').':'.$data['sitename']);
		$king->cache->rd('portal/site');

		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/'.$act)."</p>",0,$js);

	}

	kc_ajax($king->lang->get('portal/title/site'),$s,$but,$js,$width,$height+$GLOBALS['check_num']*15);
}
/**
	删除域名信息
	king_ajax_delete_site()
*/
function king_ajax_delete_site(){
	global $king;
	$king->access('portal_list_edt');

	$list=kc_getlist();

	/**/
	if($king->db->getRows_one("select siteid from %s_list where siteid in ($list)")){
		kc_error($king->lang->get('portal/error/url'));
	}
	/**/
	//不能删除最后一个
	$res=$king->db->getRows_one("select count(*) from %s_site;");
	if($res[0]==1){
		kc_error($king->lang->get('portal/error/url1'));
	}
	$king->db->query("delete from %s_site where siteid in ($list)");

	$king->cache->rd('portal/site');

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}
/**
	上移下移
	king_ajax_updown()
*/
function king_ajax_updown(){
	global $king;
	$king->access('portal_list_updown');

	$order=kc_get('norder');//传递过来的只能是数字3、4、5

	$kid=kc_get('kid');

	if($order==3||$order==4||$order==5){

		//删除缓存
		$king->db->updown('%s_list',$kid,' ismenu'.$order.'=1',0,'listid','norder'.$order);
	}else{
		$_res=$king->db->getRows_one("select listid1 from %s_list where listid=$kid;");
			$_res['listid1']==0||$_res['listid1']
				? $listid1=$_res['listid1']
				: kc_error($king->lang->get('system/error/not').$_res['listid1']);
		if(!$kid)
			kc_error($king->lang->get('system/error/not'.$kid));

		$king->db->updown('%s_list',$kid,' listid1='.$listid1,0,'listid','norder');
	}
} //!king_ajax_updown
 //生成
//king_ajax_create
function king_ajax_create(){
	global $king;
	$king->access('portal');

	list($_msec,$_sec)=explode(' ',microtime());
	$time=$_sec+$_msec;//当前时间

	$cmd=substr(CMD,6);
	switch($cmd){
		case '':
			/*
			$list=kc_getlist();
			$s =kc_progress('progresslist').kc_progress('progresslist1').kc_progress('progresspage');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=list&time='.$time.'&listids='.$list);
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=page&time='.$time.'&listids='.$list);
			kc_ajax($king->lang->get('portal/title/create'),$s,0,null,340,140);
			*/
//			$cmd='listpage';//生成列表和页面
//			$
		break;

		case 'list'://只生成列表

		/*
			$list=kc_getlist();
			$s=kc_progress('progresslist').kc_progress('progresslist1');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=list&time='.$time.'&listids='.$list);
			kc_ajax($king->lang->get('portal/title/create'),$s,0,null,340,100);
		*/
		break;

		case 'page'://只生成页面
//			$cmd='onlypage';
		/*
			$list=kc_getlist();
			$s=kc_progress('progresspage');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=page&time='.$time.'&listids='.$list);
			kc_ajax($king->lang->get('portal/title/create'),$s,0,null,340,60);
		*/
		break;

		case 'all';//全站生成
		/*
			$s =kc_progress('progresslist').kc_progress('progresslist1').kc_progress('progresspage');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=list&time='.$time.'&listids=0');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=page&time='.$time.'&listids=0');

			kc_ajax($king->lang->get('portal/title/create'),$s,0,null,340,140);
		*/
		break;

		case 'not'://生成未生成内容
		/*
			$list=kc_getlist();
			$s=kc_progress('progresspage');
			$s.=kc_htm_iframe('manage.content.php?action=iframe&create=not&time='.$time.'&listids='.$list);
			kc_ajax($king->lang->get('portal/title/create'),$s,0,null,340,60);
		*/
		break;

	}

	$s=kc_progress('progress');//($create=='all' ? 'all' : "$create&list=".kc_get('list',3,1))
	$s.='<div class="none" id="k_progress_iframe">'.kc_htm_iframe('manage.content.php?action=iframe&time='.$time.'&CMD=create&create='.($cmd=='all' ? 'all' : "$cmd&list=".kc_getlist()),502,200,'progress_iframe').'</div>';
	$s.="<script>function moreinfo(){var obj=\$('#progress + div');var o=\$('#k_ajaxBox').offset();if(obj.css('display')=='none'){\$('#k_ajaxMain').height(320);\$('#k_ajaxBox').css('top',o.top-160);\$('#k_ajaxBox').height(320+\$('#k_ajaxTitle').height()+\$('#k_ajaxSubmit').height());obj.show()}else{obj.hide();\$('#k_ajaxMain').height(100);\$('#k_ajaxBox').height(100+\$('#k_ajaxTitle').height()+\$('#k_ajaxSubmit').height());\$('#k_ajaxBox').css('top',o.top+160)}}</script>";

	$but='<a href="javascript:;" onclick="moreinfo()">'.$king->lang->get('portal/common/moreinfo').'</a>';
	$but.='<a href="javascript:;" class="k_close">'.$king->lang->get('system/common/close').'</a>';

	kc_ajax($king->lang->get('portal/title/create'),$s,$but,null,546,100);



}
function king_ajax_createpage(){
	call_user_func('king_ajax_create');
}
function king_ajax_createlist(){
	call_user_func('king_ajax_create');
}
function king_ajax_createall(){
	call_user_func('king_ajax_create');
}
function king_ajax_createnot(){
	call_user_func('king_ajax_create');
}

//删除缓存
function king_ajax_deletecachelist(){
	global $king;
	$king->access('portal_delcache');

	$_cmd=substr(CMD,11);
	switch($_cmd){
		case 'list':
			$_list=kc_getlist();
			$_array=explode(',',$_list);
			foreach($_array as $val){
				$king->cache->del('portal/list/'.$val);
			}
			$king->cache->del('portal/list/id');
		break;

		case 'all':
			$king->cache->rd('portal');
			$king->cache->rd('user');
		break;

		default:;
	}

	//删除缓存

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/'.($_cmd=='list'?'delete':'clear'))."</p>",1);
}
function king_ajax_clear_cacheall(){
	call_user_func('king_ajax_deletecachelist');
}
//合并栏目数据
function king_ajax_uniondata(){
	global $king;
	$king->access('portal_list_edt');
	$_list=kc_getlist();
	$_array=explode(',',$_list);
	//至少要选择2个项目
	if(count($_array)<2)
		kc_error($king->lang->get('portal/error/model3'));

	$sel=array();
	//栏目类型验证
	foreach($_array as $val){
		$info=$king->portal->infoList($val);
		if(isset($modelid)){//当modelid有值的时候，比较一下当前的modelid
			if($modelid!=$info['modelid']){//如果两次modelid不一致，则输出错误提示
				kc_error($king->lang->get('portal/error/model1'));
			}

		}else{
			$modelid=$info['modelid'];
			if($modelid<=0)
				kc_error($king->lang->get('portal/error/model2'));
		}
		$sel[$val]=$info['klistname'];
	}
	$s=$king->htmForm($king->lang->get('portal/label/newlist'),kc_htm_select('listid',$sel));
	$but=kc_htm_a($king->lang->get('system/common/union'),"{CMD:'uniondata',list:'{$_list}',IS:1}");

	$listid=kc_get('listid',2);
	if($listid){
		$info=$king->portal->infoList($listid);
		$model=$king->portal->infoModel($info['modelid']);
		$king->db->update('%s__'.$model['modeltable'],array('listid'=>$listid),'listid in ('.$_list.')');
		foreach($_array as $val){
			//更新列表信息
			$king->portal->lastUpdated($val,'list');
			//删除缓存
			$king->cache->del('portal/list/'.$val);
		}
		//删除内容页面缓存
		if($res=$king->db->getRows("select kid from %s__{$model['modeltable']} where listid in({$_list})")){
			foreach($res as $rs){
				$cachepath='portal/_'.$model['modeltable'].'/'.ceil($rs['kid']/1024).'/'.$rs['kid'];
				$king->cache->del($cachepath);
			}
		}
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('portal/ok/union')."</p>");
	}

	kc_ajax($king->lang->get('system/common/uniondata'),$s,$but,'',320,80);

	//验证通过后显示提交框
}
/*
动态加载联动菜单
*/
function king_ajax_linkage(){
	global $king;

	$listid=kc_get('listid',2,1);
	$id=kc_get('id',1,1);
	$is=kc_get('is',2,1);
	$def= $is==0 ? kc_post('def') : NULL;
	$sub=kc_get('sub',2);

	$s=$king->portal->LinkAge($id,$listid,$is,$def,$sub,1);
	$js="\$('#$id').val($listid)";
	kc_ajax('',$s,'',$js);
}
function king_ajax_list_editor(){
	global $king;
	$king->access('portal_editor');

	$listid=kc_get('listid',2,1);

	$info=$king->portal->infoList($listid);

	if($info['modelid']<=0){
		kc_error($king->lang->get('system/error/param'));
	}


	if($res=$king->db->getRows("select e.kid,u.userid,e.issub,u.username from %s_list_editor e,%s_user u where e.listid=$listid and e.userid=u.userid")){

		$s='<table class="k_table" cellspacing="0"><th class="c">'.$king->lang->get('portal/label/listeditor').'</th>';
		$s.='<th class="c w100">'.$king->lang->get('system/common/manage').'</th>';
		$s.='<th class="c w100">'.$king->lang->get('portal/label/issub').'</th></tr>';
		$king->Load('user');
		foreach($res as $rs){
			$s.='<tr><td class="c">'.$rs['username'].'</td>';
			$s.='<td class="c"><a href="javascript:;" class="k_ajax" rel="{CMD:\'delete_list_editor\',kid:'.$rs['kid'].',listid:'.$listid.',IS:1}">'.kc_icon('d6').'</a></td>';
			$s.='<td class="c"><a href="javascript:;" id="k_list_editor_'.$rs['kid'].'" class="k_ajax" rel="{CMD:\'list_editor_issub\',kid:'.$rs['kid'].',IS:2,ID:\'k_list_editor_'.$rs['kid'].'\',issub:'.$rs['issub'].'}">'.kc_icon($rs['issub']?'i4':'h2').'</a></td></tr>';
		}
		$s.='</table>';
		$but=kc_htm_a($king->lang->get('system/common/add'),"{CMD:'list_addeditor',listid:$listid,IS:1,METHOD:'GET'}");
		$but.="<a href=\"javascript:;\" class=\"k_close\">".$king->lang->get('system/common/close')."</a>";
		kc_ajax($king->lang->get('portal/title/listeditor').' - '.$info['klistname'],$s,$but,NULL,420,200);

	}else{
		$js="$.kc_ajax('{CMD:\'list_addeditor\',listid:$listid,METHOD:\'GET\',IS:1}')";
		kc_ajax('','','',$js);
	}

}
function king_ajax_list_editor_issub(){
	global $king;
	$kid=kc_get('kid',2,1);
	$issub=kc_get('issub',2,1) ? 0 : 1;
	$s=kc_icon($issub?'i4':'h2');
	$rel="{CMD:\'list_editor_issub\',kid:$kid,IS:2,ID:\'k_list_editor_{$kid}\',issub:$issub}";
	$js="\$('#".kc_post('ID')."').attr('rel','$rel')";
	kc_ajax('',$s,'',$js);
}
function king_ajax_delete_list_editor(){
	global $king;
	$king->access('portal_editor_delete');
	$kid=kc_get('kid');
	$listid=kc_get('listid');
	$king->db->query("delete from %s_list_editor where kid=$kid and listid=$listid");
	$js="$.kc_ajax('{CMD:\'list_editor\',listid:$listid,IS:1}')";
	kc_ajax('','','',$js);
}
function king_ajax_list_addeditor(){
	global $king;

	$king->access('portal_editor_edt');

	$listid=kc_get('listid',2,1);
//kc_error($listid);
	$array=array(
		array('username',0,3,15),
	);
	if(kc_post('username')){
		$array[]=array('username',12,$king->lang->get('user/check/notuser'),!($res=$king->db->getRows_one("select userid from %s_user where username='".$king->db->escape(kc_post('username'))."';")));
		if($res){
			$array[]=array('username',12,$king->lang->get('user/check/repeatuser'),$king->db->getRows_one("select kid from %s_list_editor where userid={$res['userid']} and listid=$listid"));
		}
	}
	$s=$king->htmForm($king->lang->get('portal/user/name'),kc_htm_input('username',kc_post('username'),15,150),$array);
	$s.=$king->htmForm($king->lang->get('portal/label/issub'),'<span><input id="issub" name="issub" type="checkbox" value="1"/><label for="issub">'.$king->lang->get('portal/help/issub').'</label></span>');


	if($GLOBALS['ischeck']){
		$array=array(
			'userid'=>$res['userid'],
			'issub'=>(kc_post('issub')?1:0),
			'listid'=>$listid,
		);
		$king->db->insert('%s_list_editor',$array);

		$js="$.kc_ajax('{CMD:\'list_editor\',listid:$listid,IS:1}')";
		kc_ajax('','','',$js);
	}

	$but=$king->db->getRows("select kid from %s_list_editor where listid=$listid") ? kc_htm_a($king->lang->get('portal/title/listeditor'),"{CMD:'list_editor',listid:$listid,IS:1}") : '';
	$but.=kc_htm_a($king->lang->get('system/common/add'),"{CMD:'list_addeditor',listid:$listid,IS:1}");
	kc_ajax($king->lang->get('portal/title/listeditoredt'),$s,$but,'',400,120+$GLOBALS['check_num']*15);
}
/*
伪静态规则
*/
function king_ajax_rewriterule(){
	global $king;

	$line=$king->config('rewriteline');
	$end=$king->config('rewriteend');

	$s=$king->htmForm($king->lang->get('portal/label/rule'),'<textarea class="k_in w450" rows="5">'.htmlspecialchars('<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^(.*)(/(list|page)('.preg_quote($line).'[0-9]+)+'.preg_quote($end).')$ $1/index.php$2
RewriteRule ^(.*)(/tag'.preg_quote($line).'.+?('.preg_quote($line).'[0-9]+)?'.preg_quote($end).')$ $1/index.php$2
</IfModule>').'</textarea>');
	$s.='<p>'.$king->lang->get('portal/help/rule').'</p>';

	kc_ajax($king->lang->get('portal/title/rewriterule'),$s,0,'',480,210);
}
/* ------>>> KingCMS for PHP <<<--------------------- */

 //列表页
//king_inc_list
function king_inc_list(){
	global $king;
	$king->portal->isHtm=True;
	$listid=kc_get('listid',2);
	$modelid=kc_get('modelid',22);

	$left=array();
	$right=array();

	$left['']=array(
		'href'=>'manage.php',
		'ico'=>'a1',
		'title'=>$king->lang->get('portal/title/listhome'),
	);

	if($king->acc('portal_list_edt')){//如果有栏目编辑权限的时候显示出来
		if($listid){
			$left['edt']=array(
				'href'=>'manage.php?action=edt&listid='.$listid,
				'ico'=>'i1',
				'title'=>$king->lang->get('portal/title/listedt'),
			);
		}else{
			$left['edt']=array(
				'href'=>'manage.php?action=edt&modelid='.$modelid,
				'ico'=>'h1',
				'title'=>$king->lang->get('portal/title/listadd'),
			);
		}
	}

	if($king->acc('portal_delcache')){
		$left[]=array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'clear_cacheall\'}',
			'ico'=>'d8',
			'title'=>$king->lang->get('portal/list/delcache'),
		);

	}

//	$left.='<a href="javascript:;" class="k_ajax" rel="{CMD:\'deletecacheall\'}">'.kc_icon('d8').$king->lang->get('portal/list/delcache').'</a>';

	if($king->acc('portal_model'))
		$right[]=array(
			'href'=>'manage.model.php',
			'title'=>$king->lang->get('portal/title/model'),
			'ico'=>'a2',
		);
//		$right.='<a href="manage.model.php" class="sub">'.$king->lang->get('portal/title/model').'</a>';
	if($king->acc('portal_tag'))
		$right[]=array(
			'href'=>'manage.tag.php',
			'title'=>$king->lang->get('portal/title/tag'),
			'ico'=>'i5',
		);
//		$right.='<a href="manage.tag.php" class="sub">'.$king->lang->get('portal/title/tag').'</a>';
	if($king->acc('portal_site'))
		$right[]=array(
			'href'=>'manage.php?action=site',
			'title'=>$king->lang->get('portal/title/site'),
			'ico'=>'e8',
		);
//		$right.='<a href="manage.php?action=site" class="sub">'.$king->lang->get('portal/title/site').'</a>';

	return array($left,$right);
} //!king_list


/* ------>>> KingCMS for PHP <<<--------------------- */

 //默认执行页面
//king_def
function king_def(){
	global $king;

	$king->access('portal');

	$_cmd=array(
		$king->lang->get('system/common/create'),
		'create'=>$king->lang->get('portal/common/create'),
		'-',
		'createlist'=>$king->lang->get('portal/common/createlist'),
		'createpage'=>$king->lang->get('portal/common/createpage'),
		'-',
		'createnot'=>$king->lang->get('portal/common/createnot'),
		'-',
		'createall'=>$king->lang->get('portal/common/createall'),
	);
	//准备开始列表
	$_cmd=array_merge($_cmd,array(
		$king->lang->get('system/common/del'),
		'delete'=>$king->lang->get('portal/common/deletelist'),
		'-',
		'deletecachelist'=>$king->lang->get('portal/common/deletecachelist'),
		$king->lang->get('system/common/union'),
		'uniondata'=>$king->lang->get('portal/common/uniondata'),
	));

	$manage="'<a href=\"'+K[8]+'\" target=\"_blank\">'+$.kc_icon('h7','".$king->lang->get('system/common/brow')."')+'</a>'";
	if($king->acc('portal_list_edt'))
		$manage.="+'<a href=\"manage.php?action=edt&listid='+K[0]+'\">'+$.kc_icon('i1','".$king->lang->get('system/common/edit')."')+'</a>'";
	if($king->acc('portal_list_delete'))
		$manage.="+isdelete(K[0],K[5])";
	$manage.="+ismodel(K[0],K[1])";

	if($king->acc('portal_editor'))
		$manage.="+iseditor(K[0],K[1])";
	if($king->acc('portal_list_updown'))
		$manage.="+\$.kc_updown(K[0])";

	$_js=array(
		"$.kc_list(K[0],K[2],isLink(K[0],K[1]),1,1,isico(K[1]),K[4],isexistsub(K[0],K[5],K[4]))",
		$manage,
		"modelname[K[1]]",
		"K[3]",
		"'<i>'+isNil(K[6]+'/'+K[7])+'</i>'",
	);

	$modelnames=$king->portal->getModelNames();

	$s='<script>';
	$s.=kc_js2array('modelname',$modelnames);
	$s.="function isico(modelid){var I1;if(modelid==0){I1='c1'}else if(modelid==-1){I1='e1'}else{I1='d1'};return I1;};";

	$s.="function ismodel(listid,modelid){var I1;if(modelid==-1||modelid==0){I1=$.kc_icon()+$.kc_icon()}else{I1='<a href=\"manage.content.php?listid='+listid+'\">'+$.kc_icon('d5','".$king->lang->get('system/common/objlist')."')+'</a><a href=\"manage.content.php?action=edt&listid='+listid+'\">'+$.kc_icon('f5','".$king->lang->get('system/common/add')."')+'</a>'};return I1;};";

	$s.="function iseditor(listid,modelid){var s;if(modelid>0){s='<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'list_editor\',listid:'+listid+',METHOD:\'GET\'}\">'+$.kc_icon('n9','".$king->lang->get('portal/title/listeditor')."')+'</a>'}else{s=$.kc_icon()}return s};";

	$s.="function isLink(listid,modelid){var I1;if(modelid==-1||modelid==0){I1=".($king->acc('portal_list_edt') ? "'manage.php?action=edt&listid='+listid" : "'javascript:;'")."}else{I1='manage.content.php?listid='+listid}return I1};";
	//是否有子栏目
	$s.="function isexistsub(listid,is,space){var I1= (is=='1')?'<a id=\"ico_'+listid+'\" rel=\"{CMD:\'openlist\',is:'+is+',ID:\'ico_'+listid+'\',IS:2,listid:'+listid+',space:'+space+'}\" href=\"javascript:;\" class=\"ico_isexistsub k_ajax\">'+$.kc_icon('k1')+'</a>':$.kc_icon();return I1;};";

	$s.="function tr_remove(listid,space){\$('#tr_'+listid+' ~ tr').each(function(){

		var marginRight=\$(this).children('td:first').children('label').children('input').css('marginRight');

		var this_space=(Number(marginRight.match(/\d+/)))/20;

		if(this_space>= space){\$(this).remove()}else{return false;}

		});};";////\$('#tr_{$listid} ~ tr').remove();

	$s.="function isdelete(listid,is){var I1=(is=='0')?'<a class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+listid+'}\" href=\"javascript:;\">'+$.kc_icon('g1','".$king->lang->get('system/common/del')."')+'</a>':$.kc_icon('j1','".$king->lang->get('portal/error/sublist')."');return I1;};";

	$s.="function isNil(str){var I1;str=='0/0'?I1='-':I1=str;return I1;};";

	$s.='</script>';

	$s.=$king->openList($_cmd,'',$_js);
	$s.="ll('".$king->lang->get('portal/list/listname')."','manage','".$king->lang->get('portal/list/modelname')."','".$king->lang->get('system/common/language')."','<i>".$king->lang->get('system/common/count')."</i>',1);";


	$array=array();//COOKIE['portal_isopen']
	if(isset($_COOKIE['portal_isopen'])){
		$array=explode(',',$_COOKIE['portal_isopen']);
	}


	if($res=$king->db->getRows("select listid from %s_list where listid1=0 order by norder,listid;")){
		foreach($res as $rs){
			$info=$king->portal->infoList($rs['listid']);
			$s.=$king->tdList(array(
				$info['listid'],
				$info['modelid'],
				$info['klistname'],
				kc_getlang($info['klanguage']),
				1,//nspace
				$info['isexist'],
				$info['ncount'],
				$info['ncountall'],
				addslashes($king->portal->pathList($info)),
			));

			//自动展开的列表
			if($info['isexist']==1 && in_array($info['listid'],$array)){
				$s.="\$.kc_ajax('{CMD:\'openlist\',is:1,ID:\'ico_{$info['listid']}\',IS:2,listid:{$info['listid']},space:1}');";
			}

			unset($info);
		}
	}

	//结束列表
	$s.=$king->closeList();

	if($king->config('cachetip'))
		$s.=$king->cache->info('portal/site/info');

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/list'),$left,$right,$s);

} //!king_def
 //添加&编辑列表
//king_edt
function king_edt(){
	global $king;

	$king->access("portal_list_edt");

	$array_static=array(
		3=>$king->lang->get('portal/static/t3'),
		0=>$king->lang->get('portal/static/t0'),
		1=>$king->lang->get('portal/static/t1'),
		2=>$king->lang->get('portal/static/t2'),
	);

	$listid=kc_get('listid',2);
	$modelid=kc_get('modelid',22);
	$modeltables=$king->portal->getModelTables();

	$line=$king->config('pidline');

	$_sql='modelid,listid1,siteid,klistname,ktitle,kkeywords,kdescription,kimage,isblank,iscontent,kcontent,klistpath,ktemplatelist1,ktemplatelist2,kpathmode,ktemplatepage1,ktemplatepage2,ispublish1,ispublish2,klanguage,ismenu1,ismenu2,ismenu3,ismenu4,ismenu5,ismap,nlistnumber,npagenumber,nlist,npage,gid,gidpublish';

	if($GLOBALS['ismethod']||$listid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['klanguage']=$_COOKIE['language'];
			$data['siteid']=0;
			$data['ismap']=1;
			$data['ismenu1']=1;
			$data['ismenu2']=1;
			$data['listid1']=0;
			$newlistid=$king->db->neworder('%s_list',null,'listid');
			$data['gid']=-1;

			$data['nlist']=1;
			$data['npage']=0;

			$tpath=$king->config('templatepath');
			$tdefa=$king->config('templatedefault');

			switch($modelid){
			case 0:
				$data['klistpath']="list{$line}{$newlistid}".$king->config('rewriteend');
				$data['iscontent']=1;
				$data['ismenu3']=1;
				//默认模板
				$ktemplatelist1=$tpath.'/'.$tdefa;
				$data['ktemplatelist1']=is_file(ROOT.$ktemplatelist1) ? $ktemplatelist1 : '';
				$ktemplatelist2=$tpath.'/inside/onepage/'.$tdefa;
				$data['ktemplatelist2']=is_file(ROOT.$ktemplatelist2) ? $ktemplatelist2 : '';
			break;
			case -1:
				$data['klistpath']='http://';
			break;
			default:
				$data['kpathmode']="page{$line}{$newlistid}{$line}ID".$king->config('rewriteend');
				$data['klistpath']="list{$line}{$newlistid}{$line}PID".$king->config('rewriteend');

				$model=$king->portal->infoModel($modelid);

				$data['ispublish1']=$model['ispublish1'];
				$data['ispublish2']=$model['ispublish2'];

				$data['npagenumber']=$model['npagenumber'];
				$data['nlistnumber']=$model['nlistnumber'];
				//默认模板
				$ktemplatelist1=$tpath.'/'.$tdefa;
				$data['ktemplatelist1']=is_file(ROOT.$ktemplatelist1) ? $ktemplatelist1 : '';
				$data['ktemplatepage1']=is_file(ROOT.$ktemplatelist1) ? $ktemplatelist1 : '';

				$ktemplatelist2=$tpath.'/inside/'.$model['modeltable'].'[list]/'.$tdefa;
				$data['ktemplatelist2']=is_file(ROOT.$ktemplatelist2) ? $ktemplatelist2 : '';

				$ktemplatepage2=$tpath.'/inside/'.$model['modeltable'].'[page]/'.$tdefa;
				$data['ktemplatepage2']=is_file(ROOT.$ktemplatepage2) ? $ktemplatepage2 : '';

			}
		}
	}else{	//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one('select '.$_sql.' from %s_list where listid='.$listid.' limit 1;'))
			kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__.'<br/>'.$_sql);
		if(isset($modelid{0})){//当有URL形式指定的modelid的时候，比较一下，如果modelid值不同，则重新设置默认值
			if($modelid!=$data['modelid']){
				if(!isset($data['klanguage']{0}))
					$data['klanguage']=$_COOKIE['language'];
				switch($modelid){
					case 0://单页
						$data['klistpath']="onepage/list{$line}{$listid}".$king->config('rewriteend');break;
					case -1://超链
						$data['klistpath']='http://';break;
					default:
						$data['klistpath']="list{$line}{$listid}{$line}PID".$king->config('rewriteend');
						if(!isset($data['kpathmode']{0}))
							$data['kpathmode']="page{$line}{$listid}{$line}ID".$king->config('rewriteend');
				}
			}
		}else{
			$modelid=$data['modelid'];
		}
	}
	$fields=explode(',',$_sql);
	$data=kc_data($fields,$data);
	//这个必须要放在下面，不然无法正常获取modelid值
	$modeltable=isset($modeltables[$modelid])?$modeltables[$modelid]:'';

	$s=$king->openForm('manage.php?action=edt',$king->lang->get('portal/caption/basic'));


	////modelid 选择模型
	if(!$res=$king->db->getRows("select modelid,modelname from %s_model"))
		$res=array();
	$_array_select=array(
		0=>$king->lang->get('portal/label/onepage'),
		-1=>$king->lang->get('portal/label/hyperlink'),
	);
	foreach($res as $rs){
		$_array_select+=array($rs['modelid']=>$rs['modelname']);
	}
	if($listid){//如果是编辑列表，则不让修改栏目类型
		if(in_array($modelid,array(0,-1))){//超链和单页允许修改类型
			$s.=$king->htmForm($king->lang->get('portal/label/model'),kc_htm_select('modelid',$_array_select,$modelid,' onChange="jumpmenu(this);"'),null,'modelid');
		}else{
			$s.=kc_htm_hidden(array('listid'=>$listid));
			$s.=$king->htmForm($king->lang->get('portal/label/model'),'<select><option>'.$_array_select[$modelid].'</option></select>',null,'modelid');
		}
	}else{
		$s.=$king->htmForm($king->lang->get('portal/label/model'),kc_htm_select('modelid',$_array_select,$modelid,' onChange="jumpmenu(this);"'),null,'modelid',kc_help('portal/help/model',320,120));
	}
	$s.='<script type="text/javascript">';
	$s.='function jumpmenu(obj){eval("parent.location=\'manage.php?action=edt&listid='.$listid.'&modelid="+obj.options[obj.selectedIndex].value+"\'");}';
	$s.='</script>';
	//listid1
	$s.=$king->htmForm($king->lang->get('portal/label/listid1'),$king->portal->LinkAge('listid1',$data['listid1'],0,$listid),null,'Listid1');
	//siteid
	if(!$res=$king->db->getRows("select siteid,sitename from %s_site;"))
		kc_error($king->lang->get('system/error/notre'));
	$_array_select=array();
	foreach($res as $rs){
	  $_array_select+=array($rs['siteid']=>htmlspecialchars($rs['sitename']));
	}
	if($modelid!=-1){
		if($king->acc('portal_site_edt')){
			$manage='<a class="k_ajax" rel="{CMD:\'site\',is:1,METHOD:\'GET\'}">'.kc_icon('f7',$king->lang->get('portal/common/addsite')).'</a>';
		}
		$s.=$king->htmForm($king->lang->get('portal/label/siteid'),kc_htm_select('siteid',$_array_select,$data['siteid']).$manage,null,'Siteid',kc_help('portal/help/site',320,150));
	}else{
		$s.=kc_htm_hidden(array('siteid'=>$res[0]['siteid']));
	}
	$s.="<script>function addsite(siteid,sitename){var site=document.getElementsByName('siteid');var opt=document.createElement('option');opt.innerHTML=sitename;opt.value=siteid;opt.selected='selected';site[0].appendChild(opt);};</script>";
	//klistname
	$_array=array(
		array('klistname',0,1,100),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/listname').' (1-100)','<input class="k_in w300" type="text" name="klistname" value="'.htmlspecialchars($data['klistname']).'" maxlength="100" />',$_array,'ListName');
	//ktitle
	$_array=array(
		array('ktitle',0,1,100),
	);
	$s.=$king->htmForm($king->lang->get('portal/label/listtitle').' (1-100, '.$king->lang->get('portal/label/listtitle1').')','<input class="k_in w300" type="text" name="ktitle" value="'.htmlspecialchars($data['ktitle']).'" maxlength="100" />',$_array,'ListTitle');
	//kkeywords
	$_array=array(
		array('kkeywords',0,0,100),
	);
	$s.=$king->htmForm($king->lang->get('system/common/keywords').' (0-100)','<input class="k_in w400" type="text" name="kkeywords" value="'.htmlspecialchars($data['kkeywords']).'" maxlength="100" />',$_array,'Keywords');
	//kdescription
	$_array=array(
		array('kdescription',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('system/common/description').' (0-255)','<textarea rows="4" cols="100" class="k_in w400" name="kdescription" maxlength="255" >'.htmlspecialchars($data['kdescription']).'</textarea>',$_array,'Description');
	//kimage
	if($data['kimage']){
		$_array=array(
			array('kimage',0,0,255),
			array('kimage',7)
		);
	}
	$s.=$king->htmForm($king->lang->get('system/common/image').' (0-255)','<input class="k_in w400" type="text" id="kimage" name="kimage" value="'.htmlspecialchars($data['kimage']).'" maxlength="255" />'.kc_f_brow('kimage',$king->config('uppath').'/image',0),$_array,'Image');

	//klanguage
	$s.=$king->htmForm($king->lang->get('system/common/language'),kc_htm_select('klanguage',kc_htm_selectlang(),$data['klanguage']),null,'Language');
	//gid
	if($modelid>0){
		$king->Load('user');
		$array_group=$king->user->getGroup();
		$array_group[-1]=$king->lang->get('user/group/open');
		$s.=$king->htmForm($king->lang->get('user/label/access'),kc_htm_select('gid',$array_group,$data['gid']),null,'gid',kc_help('user/help/access'));
	}


	$s.=$king->splitForm($king->lang->get('portal/caption/list'));

	switch($modelid){
	case 0://单页

		//nlist
		$s.=$king->htmForm($king->lang->get('portal/label/onetype'),kc_htm_select('nlist',$array_static,$data['nlist']));

		//klistpath
		$_array=array(
			array('klistpath',0,0,255),
			array('klistpath',15),
		);
		//判断（编辑/添加）状态
		if($listid){//edt
			if(isset($data['klistpath']{0})){
				array_push($_array,array('klistpath',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select listid from %s_list where klistpath='".$king->db->escape($data['klistpath'])."' and listid<>$listid and modelid<>1;"))
				);
			}else{//如果是空值，则比较同一siteid下面是否有两个
				array_push($_array,array('klistpath',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select listid from %s_list where klistpath='' and siteid=".$king->db->escape($data['siteid'])." and modelid=0 and listid<>$listid;"))
				);
			}
		}else{//add
			if(isset($data['klistpath']{0})){
				array_push($_array,array('klistpath',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select listid from %s_list where klistpath='".$king->db->escape($data['klistpath'])."' and modelid<>1;"))
				);
			}else{
				array_push($_array,array('klistpath',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select listid from %s_list where klistpath='' and siteid=".$king->db->escape($data['siteid'])." and modelid=0;"))
				);
			}
		}

		if(in_array($modelid,array(0))){//单页

			$s.=$king->htmForm($king->lang->get('portal/label/path').' (0-255)','<input class="k_in w400" type="text" name="klistpath" value="'.htmlspecialchars($data['klistpath']).'" maxlength="255" />',$_array,'Path',kc_help('portal/help/path',320,120));
			//ktemplatelist1
			if($modelid==0){
				$_array=array(
					array('ktemplatelist1',0,5,255),
					array('ktemplatelist1',15),
				);
			}else{
				$_array=array();
			}
			$s.=$king->htmForm($king->lang->get('portal/label/template1').' (5-255)','<input class="k_in w400" type="text" id="ktemplatelist1" name="ktemplatelist1" value="'.htmlspecialchars($data['ktemplatelist1']).'" maxlength="255" />'.kc_f_brow('ktemplatelist1',$king->config('templatepath'),2),$_array,null,kc_help('portal/help/template',455,455));
			//ktemplatelist2
			$_array=array(
				array('ktemplatelist2',0,0,255),
				array('ktemplatelist2',15),
			);
			$s.=$king->htmForm($king->lang->get('portal/label/template2').' (0-255)','<input class="k_in w400" type="text" id="ktemplatelist2" name="ktemplatelist2" value="'.htmlspecialchars($data['ktemplatelist2']).'" maxlength="255" />'.kc_f_brow('ktemplatelist2',$king->config('templatepath').'/inside/onepage',2),$_array);
		}

	break;

	case -1://超链
		//klistpath + islink
		$_array=array(
			array('klistpath',0,1,255),
		);
		$data['isblank']==1?$checked=' checked="checked"':$checked='';
		$s.=$king->htmForm($king->lang->get('portal/label/linkpath').' (1-255)','<input class="k_in w500" type="text" name="klistpath" value="'.htmlspecialchars($data['klistpath']).'" maxlength="255" /><br/><span><input type="checkbox" name="isblank" id="isblank" value="1" '.$checked.'/><label for="isblank">'.$king->lang->get('portal/label/isblank').'</label></span>',$_array,'ListPath');
	break;

	default:

		//nlist
		if($data['gid']==-1){
			$_array=array();
		}else{
			$_array=array(
				array('nlist',12,$king->lang->get('user/check/access'),$data['nlist']==0),
			);
		}
		$array_static_list=$array_static;
		$array_static_list[4]=$king->lang->get('portal/static/t4');
		$s.=$king->htmForm($king->lang->get('portal/label/nlist'),kc_htm_select('nlist',$array_static_list,$data['nlist']),$_array);

		//klistpath
		$_array=array(
			array('klistpath',0,1,255),
			array('klistpath',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/listpath').' (1-255)','<input class="k_in w400" type="text" name="klistpath" value="'.htmlspecialchars($data['klistpath']).'" maxlength="255" />',$_array,'ListPath',kc_help('portal/help/listpath'));
		//ktemplatelist1
		$_array=array(
			array('ktemplatelist1',0,5,255),
			array('ktemplatelist1',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/templatelist1').' (5-255)','<input class="k_in w400" type="text" name="ktemplatelist1" id="ktemplatelist1" value="'.htmlspecialchars($data['ktemplatelist1']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatelist1',$king->config('templatepath'),2).kc_help('portal/help/template',455,455));
		//ktemplatelist2
		$_array=array(
			array('ktemplatelist2',0,0,255),
			array('ktemplatelist2',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/templatelist2').' (0-255)','<input class="k_in w400" type="text" name="ktemplatelist2" id="ktemplatelist2" value="'.htmlspecialchars($data['ktemplatelist2']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatelist2',$king->config('templatepath').'/inside/'.$modeltable.'[list]',2));
		//nlistnumber
		$_array=array(
			array('nlistnumber',2),
			array('nlistnumber',16,$king->lang->get('portal/error/listnumber'),1,100),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/listnumber').' (≤100)','<input class="k_in w50" type="text" name="nlistnumber" id="nlistnumber" value="'.htmlspecialchars($data['nlistnumber']).'" maxlength="3" />',$_array);

		$s.=$king->splitForm($king->lang->get('portal/caption/page'));

		//npage
		if($data['gid']==-1){
			$_array=array();
		}else{
			$_array=array(
				array('npage',12,$king->lang->get('user/check/access'),$data['npage']==0),
			);
		}
		$s.=$king->htmForm($king->lang->get('portal/label/npage'),kc_htm_select('npage',$array_static,$data['npage']),$_array);

		//kpathmode
		$_array=array(
			array('kpathmode',0,1,100),
			array('kpathmode',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/pathmode').' (1-100)','<input class="k_in w400" type="text" name="kpathmode" value="'.htmlspecialchars($data['kpathmode']).'" maxlength="100" />',$_array,null,kc_help('portal/help/kpathmode',300,350));
		//ktemplatepage1
		$_array=array(
			array('ktemplatepage1',0,5,255),
			array('ktemplatepage1',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/templatepage1').' (5-255)','<input class="k_in w400" type="text" name="ktemplatepage1" id="ktemplatepage1" value="'.htmlspecialchars($data['ktemplatepage1']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatepage1',$king->config('templatepath'),2).kc_help('portal/help/template',455,455));
		//ktemplatepage2
		$_array=array(
			array('ktemplatepage2',0,0,255),
			array('ktemplatepage2',15),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/templatepage2').' (0-255)','<input class="k_in w400" type="text" name="ktemplatepage2" id="ktemplatepage2" value="'.htmlspecialchars($data['ktemplatepage2']).'" maxlength="255" />',$_array,null,kc_f_brow('ktemplatepage2',$king->config('templatepath').'/inside/'.$modeltable.'[page]',2));

		//npagenumber
		$_array=array(
			array('npagenumber',2),
			array('npagenumber',16,$king->lang->get('portal/error/listnumber'),1,100),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/pagenumber').' (≤100)','<input class="k_in w50" type="text" name="npagenumber" id="npagenumber" value="'.htmlspecialchars($data['npagenumber']).'" maxlength="3" />',$_array,null,kc_help('portal/help/pagenumber',300,160));


	}

	if($modelid>0){

		$s.=$king->splitForm($king->lang->get('portal/caption/access'));

		//gidpublish
		$_array=array(
			array('gidpublish',0,1,6),
			array('gidpublish',22),
		);
		$s.=$king->htmForm($king->lang->get('user/label/publish'),kc_htm_select('gidpublish',$array_group,$data['gidpublish']),$_array,'gidpublish');
		//ispublish1
		$_array_radio=array(
			0=>$king->lang->get('portal/label/pub0'),
			1=>$king->lang->get('portal/label/pub1'),
			2=>$king->lang->get('portal/label/pub2'),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/publish1'),kc_htm_radio('ispublish1',$_array_radio,$data['ispublish1']));

		//ispublish2
		$_array_radio=array(
			0=>$king->lang->get('portal/label/pub0'),
			1=>$king->lang->get('portal/label/pub1'),
			2=>$king->lang->get('portal/label/pub2'),
		);
		$s.=$king->htmForm($king->lang->get('portal/label/publish2'),kc_htm_radio('ispublish2',$_array_radio,$data['ispublish2']));

	}

	$s.=$king->splitForm($king->lang->get('portal/caption/other'));

	if($modelid!=-1){
		//iscontent
		$data['iscontent']==1?$checked=' checked="checked"':$checked='';
		$_checkbox='<span><input type="checkbox" id="iscontent" name="iscontent" value="1" onclick="javascript:seiscontent()" '.$checked.'/><label for="iscontent">'.$king->lang->get('portal/label/havecontent').'</label><var><i onClick="window.clipboardData.setData(\'Text\',\'{king:content/}\');">{king:content/}</i></var></span>';
//		$s.=$king->htmForm($_checkbox.$king->lang->get('system/common/content'),null,array(),'Content');
		//kcontent
		if($data['iscontent']==1){
			$_array=array(
				array('kcontent',0),
				array('kcontent',21),
			);
		}else{
			$_array=array();
		}
		$s.=$king->htmForm($king->lang->get('system/common/content'),$_checkbox.'<div id="iscontent1">'.kc_htm_editor('kcontent',$data['kcontent']).'</div>',$_array);
		$s.="<script>";
		$s.="function seiscontent(){var obj=\$('#iscontent');if(obj.attr('checked')==true){\$('#iscontent1').show();}else{\$('#iscontent1').hide()}};seiscontent();";
		$s.="</script>";

	}

	//菜单设置
	$_menu='<span>';
	for($i=1;$i<=5;++$i){
		$data['ismenu'.$i]==1?$checked=' checked="checked"':$checked='';
		if($i>=3){
			$_menu.='<br/><a href="manage.php?action=menu'.$i.'" target="_blank">'.kc_icon('e7',$king->lang->get('portal/common/edtmenu')).'</a>';
		}
		$_menu.='<input type="checkbox" name="ismenu'.$i.'" id="ismenu'.$i.'" value="1"'.$checked.'/><label for="ismenu'.$i.'">'.$king->lang->get('portal/label/menu'.$i).'</label> ';
	}
	$_menu.='</span>';
	$_menu.='';
	$s.=$king->htmForm($king->lang->get('portal/label/setmenu'),$_menu);

	//地图显示设置
		$data['ismap']==1?$checked=' checked="checked"':$checked='';
	$s.=$king->htmForm($king->lang->get('portal/label/map'),'<span><input type="checkbox" name="ismap" id="ismap" value="1"'.$checked.'/><label for="ismap">'.$king->lang->get('portal/label/maps').'</label></span>');


	$s.=kc_htm_hidden(array('modelid'=>$modelid,'listid'=>$listid));
	$s.=$king->closeForm('save');


	if($GLOBALS['ischeck']){

		$_array_sql=array('isblank','iscontent','ismap');
		foreach($_array_sql as $_value){
			$data[$_value] = $data[$_value] ? 1 :0;
		}

		for($i=1;$i<=5;$i++){
			$data['ismenu'.$i]=$data['ismenu'.$i] ? 1:0;
		}

		$_array=array(
			'modelid'=>($modelid?$modelid:0),
			'listid1'=>$data['listid1'],
			'klistname'=>$data['klistname'],
			'ktitle'=>$data['ktitle'],
			'kkeywords'=>$data['kkeywords'],
			'kdescription'=>$data['kdescription'],
			'kimage'=>$data['kimage'],
			'ismenu1'=>$data['ismenu1'],
			'ismenu2'=>$data['ismenu2'],
			'ismenu3'=>$data['ismenu3'],
			'ismenu4'=>$data['ismenu4'],
			'ismenu5'=>$data['ismenu5'],
			'ismap'=>$data['ismap'],
			'siteid'=>$data['siteid'],
			'klanguage'=>$data['klanguage'],
			'klistpath'=>$data['klistpath'],
			'gid'=>!empty($data['gid'])?$data['gid']:-1,
			'gidpublish'=>isset($data['gidpublish']) ? intval($data['gidpublish']) : -1,
		);
		switch($modelid){
			case 0://单页
				$_array+=array(
					'ktemplatelist1'=>$data['ktemplatelist1'],
					'ktemplatelist2'=>$data['ktemplatelist2'],
					'iscontent'=>$data['iscontent'],
					'kcontent'=>$data['kcontent'],
					'nlist'=>$data['nlist'],
				);
			break;

			case -1://超链
				$_array['isblank']=$data['isblank'];
			break;

			default://自定义模型
				$_array+=array(
					'iscontent'=>$data['iscontent'],
					'kcontent'=>$data['kcontent'],
					'nlistnumber'=>$data['nlistnumber'],
					'npagenumber'=>$data['npagenumber'],
					'klistpath'=>$data['klistpath'],
					'ktemplatelist1'=>$data['ktemplatelist1'],
					'ktemplatelist2'=>$data['ktemplatelist2'],
					'kpathmode'=>$data['kpathmode'],
					'ktemplatepage1'=>$data['ktemplatepage1'],
					'ktemplatepage2'=>$data['ktemplatepage2'],
					'ispublish1'=>$data['ispublish1'],
					'ispublish2'=>$data['ispublish2'],
					'nlist'=>$data['nlist'],
					'npage'=>$data['npage'],
				);
		}


		if($listid){//update
//			kc_error('<pre>'.print_r($_array,1));
			$king->db->update('%s_list',$_array,'listid='.$listid);
			$nlog=7;
			//更新列表信息
			$king->portal->lastUpdated($listid);
		}else{
			$neworder=$king->db->neworder('%s_list');
			$_array+=array(
				'norder'=>$neworder,
				'norder3'=>$neworder,
				'norder4'=>$neworder,
				'norder5'=>$neworder,
			);
			$listid=$king->db->insert('%s_list',$_array);
//			kc_error('<pre>'.print_r($_array,1));
			$nlog=5;

		}

		//删除缓存
		$king->cache->del('portal/list/'.$listid);
		$king->cache->rd('portal/site');
		if($data['listid1'])
			$king->cache->del('portal/list/'.$data['listid1']);

		//单页的时候调用生成列表,列表的时候，加入到增量更新里
		/**/
		if($modelid==0){
			$king->portal->createList($listid);
		}
		/**/

		//写log
		$king->log($nlog,'ListName:'.$data['klistname']);


		kc_goto($king->lang->get('system/goto/is'),'manage.php?action=edt','manage.php');
	}

	list($left,$right)=king_inc_list();
	$king->skin->output($king->lang->get('portal/title/list'.($listid?'edt':'add')),$left,$right,$s);

}
/**
	menu3/4/5的排序
*/
function king_menu4(){
	call_user_func('king_menu3');
}
function king_menu5(){
	call_user_func('king_menu3');
}
function king_menu3(){
	global $king,$action;
	$king->access('portal_list_edt');

	$num=substr($action,-1);

	$_js=array(
		"$.kc_list(K[0],K[1])",
		"$.kc_updown(K[0],'updown')",
	);
	$_cmd=array(
		$king->lang->get('system/common/create'),
		'create'=>$king->lang->get('portal/common/create'),
		'-',
		'createlist'=>$king->lang->get('portal/common/createlist'),
		'createpage'=>$king->lang->get('portal/common/createpage'),
		'-',
		'createnot'=>$king->lang->get('portal/common/createnot'),
		'-',
		'createall'=>$king->lang->get('portal/common/createall'),
	);
	$_cmd=array_merge($_cmd,array(
		$king->lang->get('system/common/del'),
		'delete'=>$king->lang->get('portal/common/deletelist'),
		'-',
		'deletecachelist'=>$king->lang->get('portal/common/deletecachelist'),
		'deletecacheall'=>$king->lang->get('portal/common/deletecache'),
		$king->lang->get('system/common/union'),
		'uniondata'=>$king->lang->get('portal/common/uniondata'),
	));

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist("manage.php?action=menu{$num}&pid=PID&rn=RN",$king->db->getRows_number('%s_list',"ismenu{$num}=1")),array('norder'=>$num));

	if(!$res=$king->db->getRows("select listid,klistname from %s_list where is{$action}=1 order by norder{$num};"))
		$res=array();

	$s.='ll(\''.$king->lang->get('portal/list/listname').'\',\'manage\',1);';
	foreach($res as $rs){
		$s.='ll('.$rs['listid'].',\''.addslashes(htmlspecialchars($rs['klistname'])).'\',0);';
	}

	$s.=$king->closeList();

	$left=array(
		array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/listhome'),
		),
	);
	for($i=3;$i<=5;$i++){
		$left['menu'.$i]=array(
			'href'=>'manage.php?action=menu'.$i,
			'title'=>$king->lang->get('portal/list/menu').$i,
			'ico'=>'b1',
		);
	}

	$right=array(
		array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/listhome'),
		),
	);

	$king->skin->output($king->lang->get('portal/title/list'),$left,$right,$s);
}
/**
	域名绑定管理
*/
function king_site(){

	global $king;

	$king->access('portal_site');

	if(!$res=$king->db->getRows("select siteid,sitename,siteurl from %s_site order by siteid desc;"))
		$res=array();

	$_cmd=array(
		'delete_site'=>$king->lang->get('system/common/del'),
	);
	$_manage="'<a class=\"k_ajax\" rel=\"{CMD:\'site\',METHOD:\'GET\',siteid:'+K[0]+'}\">'+$.kc_icon('e5','".$king->lang->get('system/common/edit')."')+'</a>";
	$_manage.="<a class=\"k_ajax\" rel=\"{CMD:\'delete_site\',list:'+K[0]+'}\" href=\"javascript:;\">'+$.kc_icon('g5','".$king->lang->get('system/common/del')."')+'</a>'";
	$_js=array(
		"$.kc_list(K[0],K[1],'{CMD:\'site\',siteid:'+K[0]+',METHOD:\'GET\'}',1)",
		$_manage,
		"K[2]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=site&pid=PID&rn=RN',$king->db->getRows_number('%s_site')));

	$s.='ll(\''.$king->lang->get('portal/list/listname').'\',\'manage\',\''.$king->lang->get('portal/list/url').'\',1);';
	foreach($res as $rs){
		$s.='ll('.$rs['siteid'].',\''.addslashes(htmlspecialchars($rs['sitename'])).'\',\''.addslashes(htmlspecialchars($rs['siteurl'])).'\',0);';
	}
	$s.=$king->closeList();

	$left=array(
		array(
			'href'=>'manage.php?action=site',
			'class'=>'sel',
			'title'=>$king->lang->get('portal/title/site'),
			'ico'=>'e8',
		),
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'site\',METHOD:\'GET\'}',
			'ico'=>'h8',
			'title'=>$king->lang->get('system/common/add'),
		),
	);
	$right=array(
		array(
			'href'=>'manage.php',
			'title'=>$king->lang->get('portal/title/list'),
			'ico'=>'a1',
		),
	);

	$king->skin->output($king->lang->get('portal/title/site'),$left,$right,$s);
}






?>