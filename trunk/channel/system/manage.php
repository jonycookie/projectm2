<?php

require_once '../global.php';

/**
	novice头信息
	@param int $is  新手首页是否带有链接
	@return
*/
function inc_novice($text,$is=0){
	global $king;
	$king->access('#novice');

	$s='<div id="k_faq">';

	$s.='<p class="k_faq_nav">';
	$s.=$is?'':'<a href="javascript:;" class="k_ajax" rel="{CMD:\'novice\',URL:\'../system/manage.php\',IS:1}">';
	$s.=kc_icon('a1').$king->lang->get('system/novice/home');
	$s.=$is?'':'</a>';
	$s.='</p>';

	$s.=$text;
	$s.='</div>';

	return $s;
}

function king_ajax_novice(){
	global $king;

	$s=inc_novice($king->lang->get('system/novice/main'),1);

	kc_ajax($king->lang->get('system/level/novice'),$s,0,0,500,350);
}

function king_ajax_novice1(){
	global $king;

	$s=inc_novice($king->lang->get('system/novice/step1'));

	kc_ajax($king->lang->get('system/level/novice'),$s,0,0,500,350);
}

 //删除管理员
//king_ajax_delete_admin
function king_ajax_delete_admin(){
	global $king;
	$king->access('admin');
	$_list=kc_getlist();
	$_array=explode(',',$_list);
	if(in_array($king->admin['adminid'],$_array))//检查自己
		kc_error($king->lang->get('system/error/admin'));
	$king->db->update('%a_admin',array('isdelete'=>1),"adminid in ({$_list})");
//	$king->db->query('delete from %a_admin where adminid in ('.$_list.');');

	$king->cache->del('system/admins');

	//写log
	if(!$res=$king->db->getRows("select adminname from %a_admin where adminid in ({$_list})")){
		$res=array();
	}
	foreach($res as $val){
		$array_admin[]=$val['adminname'];
	}
	$king->log(6,'AdminName:'.implode(',',$array_admin));

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
 }//!king_ajax_delete_admin
 //删除日志
//king_ajax_delete_log
function king_ajax_delete_log(){
	global $king;
	$king->access('#log_del');
	$_list=kc_getlist();
	$king->db->query('delete from %s_log where kid in ('.$_list.');');
	$king->log(6,$king->lang->get('system/level/log'));
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}//!king_ajax_delete_log
function king_ajax_delete_lnk(){
	global $king;
	$king->access(0);
	$list=kc_getlist();
	$adminid=kc_get('adminid',2,1);

	if($king->acc('admin')){//若是管理员，则随便可以删除
		$king->db->query("delete from %s_lnk where kid in ($list)");
	}else{
		$king->db->query("delete from %s_lnk where kid in ($list) and aminid=".$king->admin['adminid']);
	}

	$array=explode(',',$list);

	$king->cache->del('system/lnk/'.$adminid);
	$king->cache->del('system/lnk/flo_'.$adminid);

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}
function king_ajax_delete_module(){
	global $king;
	$king->access('#module');
	$list=kc_getlist();
	$lists=explode(',',$list);

	if($res=$king->db->getRows("select kname,kid1 from %s_module where kid in ($list) or kid1 in ($list)")){
		$array=array();
		foreach($res as $rs){
			$array[]=$rs['kname'];
			if((int)$rs['kid1']!==0){
				$lists[]=$rs['kid1'];
			}
		}
		$king->log(6,'Module : '.implode(',',$array));
	}

	$list=implode(',',$lists);//重新设置list值

	$king->db->query("delete from %s_module where kid in ({$list}) or kid1 in ({$list})");
	$king->cache->rd('system/module');
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}
function king_ajax_delete_bot(){
	global $king;
	$king->access('#botdel');
	$kid=kc_getlist();

	$king->db->query("delete from %s_bot where kid in ($kid)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

}
function king_ajax_delete_event(){
	global $king;
	$king->access('#event_del');
	$_list=kc_getlist();
	$king->db->query('delete from %s_event where kid in ('.$_list.');');
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}

function king_ajax_delete_upfile(){

	global $king;
	$king->access('#upfile_delete');

	$_list=kc_getlist();
	$king->db->query('delete from %s_upfile where kid in ('.$_list.');');

	if($res=$king->db->getRows("select kpath from %s_upfile where kid in ('.$_list.')")){
		foreach($res as $rs){
			kc_f_delete($rs['kpath']);//删除文件
		}
	}
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);


}
function king_ajax_delete_conn(){
	global $king;
	$king->access('#conn_del');

	$list=kc_getlist();
	$king->db->query("delete from %s_conn where kid in ($list)");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);
}

function king_ajax_clearcache(){
	global $king;
	$king->access('#systemcache');
	$king->cache->rd();

	//同时删除临时语言包
	kc_f_delete("system/js/lang.".$king->admin['adminlanguage'].".js");

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/clear')."</p>",1);
}
function king_ajax_clear_cache(){
	global $king;
	$king->access('#systemcache');
	$list=kc_getlist();

	if($res=$king->db->getRows("select kpath from %s_module where kid in ($list)")){
		foreach($res as $rs){
			$king->cache->rd($rs['kpath']);
		}
	}


	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/clear')."</p>",1);
}
function king_ajax_clear_event(){
	global $king;
	$king->access('#event_del');
	$king->db->query("delete from %s_event;");
	$king->log(10,$king->lang->get('system/level/event'));
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/clear')."</p>",1);
}
function king_ajax_clear_log(){
	global $king;
	$king->access('#log_delete');
	$king->db->query("delete from %s_log;");
	$king->log(10,$king->lang->get('system/level/log'));
	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/clear')."</p>",1);
}
/**
	上移下移
	king_ajax_updown()
*/
function king_ajax_updown_module(){
	global $king;
	$king->access('#module');
	$kid=kc_get('kid');

	$king->cache->rd('system/module');
	$king->cache->rd('system/mainmenu');
	$king->db->updown('%s_module',$kid,'',0);
} //!king_ajax_updown
function king_ajax_updown_lnk(){
	global $king;
	$king->access(0);
	$kid=kc_get('kid');
	$adminid=kc_get('adminid',2,1);

	$king->cache->del('system/lnk/'.$adminid);
	$king->cache->del('system/lnk/flo_'.$adminid);
	$king->db->updown('%s_lnk',$kid,' adminid='.$adminid);
}
function king_ajax_updown_conn(){
	global $king;
	$king->access('#conn');

	$kid=kc_get('kid');

	$king->db->updown('%s_conn',$kid);
}
//king_ajax_language
function king_ajax_language(){
	global $king;
	$king->access(0);
	$_lang=kc_post('lang');
	$_filepath=dirname(__FILE__).'/language/'.$_lang.'.xml';
	if (file_exists($_filepath)){
		$king->db->update('%a_admin',array('adminlanguage'=>$_lang),'adminid='.$king->admin['adminid']);
		$king->cache->rd('system/mainmenu/'.$king->admin['adminid']);
		$king->cache->del('system/admin/'.$king->admin['adminname']);
		setcookie("language",$_lang,time()+8640000,'/');
		if(!$_goto=$_SERVER['HTTP_REFERER'])//判断REFERER值是否为空
			$_goto='../system/manage.php';
		kc_ajax('','',0,'parent.location=\''.$_goto.'\';');
	}else{
		kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
} //!king_ajax_language
 //设置风格
//king_ajax_skins
function king_ajax_skins(){
	global $king;
	$king->access(0);
	$_skins=kc_post('skins');
	$_filepath=dirname(__FILE__).'/skins/'.$_skins.'/';
	if (is_dir($_filepath)){
		$king->db->update('%a_admin',array('adminskins'=>$_skins),'adminid='.$king->admin['adminid']);
		$king->cache->rd('system/mainmenu/'.$king->admin['adminid']);
		$king->cache->del('system/admin/'.$king->admin['adminname']);
		if(!$_goto=$_SERVER['HTTP_REFERER'])//判断REFERER值是否为空
			$_goto='../system/manage.php';
		kc_ajax('','',0,'parent.location=\''.$_goto.'\';');
	}else{
		kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
} //!king_ajax_skins
//king_ajax_skins
function king_ajax_editor(){
	global $king;
	$king->access(0);
	$_editor=kc_post('editor');
	$_filepath=dirname(__FILE__).'/editor/'.$_editor.'/';
	if (is_dir($_filepath)){
		$king->db->update('%a_admin',array('admineditor'=>$_editor),'adminid='.$king->admin['adminid']);
		$king->cache->rd('system/mainmenu/'.$king->admin['adminid']);
		$king->cache->del('system/admin/'.$king->admin['adminname']);
		if(!$_goto=$_SERVER['HTTP_REFERER'])//判断REFERER值是否为空
			$_goto='../system/manage.php';
		kc_ajax('','',0,'parent.location=\''.$_goto.'\';');
	}else{
		kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
} //!king_ajax_language

 //系统帮助
//king_ajax_help
function king_ajax_help(){
	global $king;
	$king->access(0);
	$path=kc_post('path');
//	$width=kc_post('width');
//	$height=kc_post('height');
//	$module=kc_post('module');

	$s=$king->lang->get($path);
	$help=$s;

	kc_ajax('',$help,0);

} //!king_ajax_help
/**
编辑个人信息
*/
function king_ajax_pass(){
	global $king;
	$king->access(0);
	if(!$king->acc('#resetpass')){
		kc_error($king->lang->get('system/error/resetpass'));
	}

	$data=$king->db->getRows_one('select adminname,adminpass from %a_admin where adminid='.$king->admin['adminid']);

	$_array=array(
		array('oldpass',0,6,30),
		array('oldpass',12,$king->lang->get('system/error/oldpass'),$data['adminpass']!=md5(kc_post('oldpass'))),
	);
	$s=$king->htmForm($king->lang->get('system/admin/oldpass'),'<input class="k_in w150" type="password" id="oldpass" name="oldpass" maxlength="30" />',$_array);
	//密码
	$_array=array(
		array('pass1',0,6,30),
		array('pass1',17,null,'pass2'),
	);
	$s.=$king->htmForm($king->lang->get('system/admin/pass1').' (6-30)','<input class="k_in w150" type="password" id="pass1" name="pass1" maxlength="30" />',$_array);
	$s.=$king->htmForm($king->lang->get('system/admin/pass2'),'<input class="k_in w150" type="password" id="pass2" name="pass2" maxlength="30" />');

	$but=kc_htm_a($king->lang->get('system/common/save'),"{CMD:'pass',URL:'../system/manage.php',IS:1}");

	if($GLOBALS['ischeck']){

		$_md5pass=md5(kc_post('pass1'));

		$_array= array('adminpass'=>$_md5pass);

		$king->db->update('%a_admin',$_array,'adminid='.$king->admin['adminid']);

		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
		setcookie('KingCMS_Admin',$king->admin['adminname']."\t".md5($king->admin['adminname'].$_md5pass),(kc_post('expire') ? time() + (int) kc_post('expire') : 0),'/');

		$king->cache->del('system/admin/'.$king->admin['adminname']);

		//写log
		$king->log(7,'Reset Password:'.$data['adminname']);

		kc_ajax($king->lang->get('system/admin/pass'),"<p class=\"k_ok\">".$king->lang->get('system/admin/passok')."</p>");

	}else{
		kc_ajax($king->lang->get('system/admin/pass'),$s,$but,'',240,170+$GLOBALS['check_num']*15);
	}
}
function king_ajax_module_add(){
	global $king;
	$king->access('#module');

	$modulepath=kc_post('modulepath');
	$is=false;
	if(isset($modulepath{0})){
		is_file(ROOT.$modulepath.'/core.class.php')&&is_file(ROOT.$modulepath.'/manage.php')
			? $is=false
			: $is=true;
	}
	$array=array(
		array('modulepath',0,1,100),
		array('modulepath',4),
		array('modulepath',12,$king->lang->get('system/module/check'),$is),
		array('modulepath',18,null,$king->holdmodule),
	);
	$s=$king->htmForm($king->lang->get('system/module/addpath'),'<input class="k_in w300" type="text" value="'.htmlspecialchars($modulepath).'" id="modulepath" name="modulepath" />',$array);
	$but=kc_htm_a($king->lang->get('system/common/add'),"{CMD:'module_add',IS:1}");

	if($GLOBALS['ischeck']){
		$classname=$modulepath.'_class';
		$kc=new $classname;
		if($kc->install()){
			$king->cache->rd('system/module');
			$king->cache->del('system/modulever');
//			$king->cache->rd('system/tophtm');
			$king->log(5,'Module : '.$modulepath);
			kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/add')."[$modulepath]</p>","<a href=\"manage.php?action=module\">".$king->lang->get('system/common/enter')."</a>");
		}else{
			kc_error($king->lang->get('system/module/error'));
		}
	}
	kc_ajax($king->lang->get('system/module/add'),$s,$but,null,340,80+$GLOBALS['check_num']*15);

}
function king_ajax_module_lock(){
	global $king;
	$king->access('#module');
	$list=kc_getlist();

	$islock= (CMD=='module_lock') ? 1 : 0;
	$array=array(
		'islock'=>$islock,
	);
	$king->db->update('%s_module',$array," kid in ($list) or kid1 in($list)");
	$king->cache->rd('system/module');
	$nlog=$islock ? 8 : 9;
	if($res=$king->db->getRows("select kname from %s_module where kid in ($list) or kid1 in ($list)")){
		$array=array();
		foreach($res as $rs){
			$array[]=$rs['kname'];
		}
		$king->log($nlog,'Module : '.implode(',',$array));
	}

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/set')."</p>",1);
}
function king_ajax_module_unlock(){
	call_user_func('king_ajax_module_lock');
}
function king_ajax_module_show(){
	global $king;
	$king->access('#module');
	$kid=kc_get('kid',2,1);
	$is=kc_get('is',2,1);
	$ico= $is ? 'n1':'n2';

	$king->db->update('%s_module',array('nshow'=>$is),"kid=$kid");

	$king->cache->rd('system/module');

	kc_ajax('',kc_icon($ico),'',"$('#nshow_{$kid}').attr('rel','{CMD:\'module_show\',ID:\'nshow_{$kid}\',is:".(1-$is).",kid:{$kid},IS:2}')");
}
/**
	查找模块
*/
function king_ajax_findmodule(){
	global $king;
	$king->access('#module');

	$array=kc_f_getdir('./','dir',array('.','..','.svn','system'));
	$s='<table class="k_table" cellspacing="0">';
	$s.='<tr><th>'.$king->lang->get('system/module/name').'</th>';
	$s.='<th>'.$king->lang->get('system/module/path').'</th>';
	$s.='<th class="c">'.$king->lang->get('system/common/install').'</th></tr>';
	foreach($array as $val){
		if(is_file(ROOT.$val.'/core.class.php') && is_file(ROOT.$val.'/manage.php')){
			$s.='<tr>';
			if($king->isModule($val)){
				$s.='<td><a href="../'.$val.'/manage.php">'.kc_icon('k7').' '.$king->lang->get($val.'/name').'</a></td>';
				$s.='<td>'.$val.'</td>';
				$s.='<td class="c">'.kc_icon('i4').'</td></tr>';
			}else{
				$s.='<td><a href="../system/manage.php?action=module&module='.$val.'" title="'.$king->lang->get('system/common/install').'">'.kc_icon('k7').' '.$king->lang->get($val.'/name').'</a></td>';
				$s.='<td>'.$val.'</td>';
				$s.='<td class="c"><a href="../system/manage.php?action=module&module='.$val.'" title="'.$king->lang->get('system/common/install').'">'.kc_icon('l7').'</a></td></tr>';
			}
		}
	}
	$s.='</table>';

	kc_ajax($king->lang->get('system/module/find'),$s,0,'',400,250);
}
function king_ajax_msg(){
	global $king;
	$king->access(0);

	$cachepath='system/message/'.$king->admin['adminid'];
	$s=$king->cache->get($cachepath);

	if(!isset($s{0})){
		if(!$res=$king->db->getRows("select issys,adminname,kmsg,ndate,adminid,klink from %s_message where adminid=0 or adminid=".$king->admin['adminid']." or adminname='".$king->admin['adminname']."' order by kid desc limit 17"))
			$res=array();
		krsort($res);
		$s='<table cellspacing="0">';
		foreach($res as $rs){
			$s.='<tr>';
			if($rs['issys']==1){
				$s.='<th class="red w100">';
			}elseif($rs['adminid']==$king->admin['adminid']){
				$s.='<th class="green">';
			}elseif($rs['adminname']==$king->admin['adminname']){
				$s.='<th class="blue">';
			}else{
				$s.='<th>';
			}
			$s.=$rs['adminname'].':</th><td><span>';
			$rs['klink']
				? $s.='<a target="_blank" href="'.$rs['klink'].'">'.htmlspecialchars($rs['kmsg']).'</a>'
				: $s.=htmlspecialchars($rs['kmsg']);
			$s.='<i>['.kc_formatdate($rs['ndate'],'n/j/Y, G:i').']</i></span></td></tr>';
		}
		$s.='</table>';
		$king->cache->put($cachepath,$s);
	}
	kc_ajax('',$s,0,"setTimeout(\"$.kc_ajax('{CMD:\\\'msg\\\',ID:\\\'k_msg\\\',IS:1,URL:\\\'../system/manage.php\\\'}')\",10000)");//10秒刷新一次
}

function king_ajax_msg_add(){
	global $king;
	$king->access(0);
	$cachepath='system/message';

	$message=kc_post('k_message');
	if(isset($message{0})){
		$array=array(
			'adminid'=>kc_post('adminid'),
			'adminname'=>$king->admin['adminname'],
			'kmsg'=>$message,
			'ndate'=>time(),
		);
		$king->db->insert('%s_message',$array);
		$king->cache->rd($cachepath);
		kc_ajax(null,null,0,"$.kc_ajax({CMD:\\\'msg\\\',URL:\\\'../system/manage.php\\\',ID:\\\'k_msg\\\'});$(\'#k_message\').value='';$('#k_message').focus();");
	}else{
		kc_ajax(null,null,0,"alert('".$king->lang->get('system/error/none')."')");
	}

}
function king_ajax_lnkmove(){
	//这个移动只能对自己的进行控制
	global $king;
	$king->access(0);

	$left=kc_get('left',2,1);
	$top=kc_get('top',2,1);
	$id=kc_get('id',2,1);

	$nleft=round($left/5)*5;
	$ntop=round($top/5)*5;

	$array=array(
		'nleft'=>$nleft,
		'ntop'=>$ntop,
	);
	$king->db->update('%s_lnk',$array,"kid=$id and adminid=".$king->admin['adminid']);

	$king->cache->del("system/lnk/flo_".$king->admin['adminid']);

	kc_ajax(null,null,0,"");
}
function king_ajax_botedt(){
	global $king;
	$king->access('#botedt');
	$kid=kc_get('kid');
	$width=450;
	$height=120;

	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
	}else{	//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one('select kname,kmark from %s_bot where kid='.$kid.' limit 1;'))
			kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	$data=kc_data(array('kname','kmark'),$data);

	$s='<div>';
	//kname
	$array=array(
		array('kname',0,1,30),
	);
	$s.=$king->htmForm($king->lang->get('system/bot/kname').'(1-30)','<input type="text" id="kname" name="kname" maxlength="30" class="k_in w400" value="'.htmlspecialchars($data['kname']).'"/>',$array);
	//kmark
	$array=array(
		array('kmark',0,1,255),
	);
	$s.=$king->htmForm($king->lang->get('system/bot/kmark').'(1-100)','<input type="text" id="kmark" name="kmark" class="k_in w400" maxlength="255" value="'.htmlspecialchars($data['kmark']).'" />',$array);
	$but=kc_htm_a($king->lang->get('system/common/save'),"{CMD:'botedt',kid:'$kid',IS:1}");
	$s.='</div>';


	if($GLOBALS['ischeck']){
		$array=array(
			'kname'=>$data['kname'],
			'kmark'=>$data['kmark'],
		);
		if($kid){
			$king->db->update('%s_bot',$array,"kid=$kid");
			$nlog=7;
		}else{
			$kid=$king->db->insert('%s_bot',$array);
			$nlog=5;
		}
		$king->log($nlog,'SpiderId:'.$kid.',Name:'.$data['kname']);
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/save')."</p>",1);
	}

	kc_ajax($king->lang->get('system/bot/edt'),$s,$but,null,$width,$height+$GLOBALS['check_num']*15);

}
function king_ajax_htmlframe(){
	global $king;
	$king->access('#systeminfo');

	$array=array(
		'kvalue'=>'0'
	);
	$king->db->update('%s_system',$array,"kname='htmlframe1'");
	$king->cache->del('system/config/system');


	if(!$_goto=$_SERVER['HTTP_REFERER'])//判断REFERER值是否为空
		$_goto='../system/manage.php';
	kc_ajax('','',0,'parent.location=\''.$_goto.'\';');


}
function king_ajax_close_cachetip(){
	global $king;
	$king->access('#systeminfo');

	$array=array(
		'kvalue'=>'0'
	);
	$king->db->update('%s_system',$array,"kname='cachetip'");
	$king->cache->del('system/config/system');

	if(!$_goto=$_SERVER['HTTP_REFERER'])//判断REFERER值是否为空
		$_goto='../system/manage.php';
	kc_ajax('','',0,'parent.location=\''.$_goto.'\';');
}
function king_ajax_tagmenu(){
	global $king;
	$king->access(0);

	$king->skin->tagmenu();
}
function king_ajax_faq(){
	global $king;

	$king->access(0);

	$modules=$king->getModule();
	$module=kc_post('module');
	$num=kc_post('num');

	$s='<div id="k_faq"><p class="k_faq_nav">';
	$s.='<a class="k_ajax" rel="{CMD:\'faq\',URL:\'../system/manage.php\',IS:1}">'.kc_icon('a1').'HOME</a>';
	$s.='</p>';
	if(!$module){//如果module为空
		$s.='<p>'.kc_icon('l9').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',module:\'system\',URL:\'../system/manage.php\',IS:1}">'.$king->lang->get('system/name').'</a></p>';
		foreach($modules as $val){
			if(file_exists(ROOT.$val.'/faq/'.$king->admin['adminlanguage'].'.xml')){
				$s.='<p>'.kc_icon('l9').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',module:\''.$val.'\',IS:1,URL:\'../system/manage.php\'}">'.$king->lang->get($val.'/name').'</a></p>';
			}
		}
		$s.='<p>&nbsp;</p>';
		$s.='<p>'.kc_icon('e9').'<a href="http://help.kingcms.com/" target="_blank">'.$king->lang->get('system/login/manual').'</a></p>';
		$s.='<p>'.kc_icon('n1').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'novice\',IS:1,URL:\'../system/manage.php\'}">'.$king->lang->get('system/level/novice').'</a></p>';
	}else{

		$doc=new DOMDocument;
		$filepath=ROOT.$module.'/faq/'.$king->admin['adminlanguage'].'.xml';
		$doc->load($filepath);
		$path=new DOMXPath($doc);
		$title=@$path->evaluate('//kingcms/item/title');

		if(kc_validate($num,2)){
			$s.='<h3>'.$title->item($num)->nodeValue.'</h3><hr/>';
			$body=@$path->evaluate('//kingcms/item/body');
			$s.='<p>'.nl2br($body->item($num)->nodeValue).'</p>';
			$s.='<p class="k_faq_nav">'.kc_icon('c9').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',module:\''.$module.'\',URL:\'../system/manage.php\',IS:1}">'.$king->lang->get('system/common/backlist').'</a></p>';
		}else{
//			kc_error($title->length);
			$count=$title->length;
//			kc_error('<pre>'.print_r($title,1));
			for($i=0;$i<$count;$i++){
				$s.='<p>'.kc_icon('n8').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',module:\''.$module.'\',num:'.$i.',URL:\'../system/manage.php\',IS:1}">'.kc_substr($title->item($i)->nodeValue,0,60).'</a></p>';
			}
			$s.='<p class="k_faq_nav">'.kc_icon('c9').'<a href="javascript:;" class="k_ajax" rel="{CMD:\'faq\',IS:1,URL:\'../system/manage.php\'}">'.$king->lang->get('system/login/home').'</a></p>';
		}
	}
	if(kc_validate($num,2)){
	}
	$s.='</div>';

	kc_ajax($king->lang->get('system/common/faq'),$s,0,'',500,350);
}


function king_ajax_view_event(){
	global $king;
	$kid=kc_post('kid');
	$king->access('#event');
	if(kc_validate($kid,2)){
		if(!$res=$king->db->getRows_one("select kfile,nline,kmsg,kurl from %s_event where kid=$kid;"))
			kc_error($king->lang->get('system/error/param'));
		$files=file(ROOT.$res['kfile']);

		$s='<p>URL: <a href="">'.$res['kurl'].'</a></p>';

		$s.='<div class="k_code"><h5>'.$king->lang->get('system/event/code').':</h5>';
		for($i=-3;$i<4;$i++){
			$line=$i+$res['nline'];
			$res['nline']==$line
			 ? $s.='<p class="red"><strong>'.$line.'</strong> &nbsp; '.htmlspecialchars($files[$line-1]).'</p>'
			 : $s.='<p><strong class="gray">'.$line.'</strong> &nbsp; '.htmlspecialchars($files[$line-1]).'</p>';
		}
		$s.='</div>';
		$s.='<p>'.$res['kmsg'].'<p>';

		kc_ajax('File: '.$res['kfile'].' - Line: '.$res['nline'],$s,0,'',600,390);
	}else{
		kc_error($king->lang->get('system/error/param'));
	}
}

function king_ajax_upfile_edt(){
	global $king;
	$king->access('#upfile_edt');

	$kid=kc_post('kid',2,1);
	$ktitle=kc_post('ktitle');

	if($GLOBALS['ismethod']){//提交

	}else{//数据库读取
		$res=$king->db->getRows_one("select ktitle from %s_upfile where kid=$kid");
		$ktitle=$res['ktitle'];
	}
	$_array=array(
		array('klistname',0,0,100),
	);
	$s=$king->htmForm($king->lang->get('system/common/title').' (0-100)','<input class="k_in w300" type="text" name="ktitle" value="'.htmlspecialchars($ktitle).'" maxlength="100" />',$_array);

	$but=kc_htm_a($king->lang->get('system/common/save'),"{CMD:'upfile_edt',kid:$kid,IS:1}");

	if($GLOBALS['ischeck']){
		$array=array('ktitle'=>$ktitle);
		$king->db->update('%s_upfile',$array,"kid=$kid");
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/save')."</p>",1);
	}

	kc_ajax($king->lang->get('system/common/edit'),$s,$but,'',420,80);
}
function king_ajax_conn_edt(){
	global $king;
	$king->access('#conn_edt');

	$kid=kc_get('kid',2);

	$sql="kid,kname,ksign,urlpath";
	$array_sql=explode(',',$sql);

	if($GLOBALS['ismethod'] || empty($kid) || kc_post('reset')==1){//若kid为空，则添加
		$data=$_POST;
		if(!$GLOBALS['ismethod']){//预置项
			$data['ntype']=isset($_POST['ntype']) ? $_POST['ntype'] : 1;
		}
	}else{	//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one("select $sql from %s_conn where kid=$kid limit 1"))
			kc_error($king->lang->get('system/error/notrecord'));
	}
	$data=kc_data($array_sql,$data);

	//kname
	$array=array(
		array('kname',0,1,50),
	);
	//验证重复值
	if(empty($kid)){
		$array[]=array('kname',12,$king->lang->get('system/conn/error/name'),$king->db->getRows_one("select kid from %s_conn where kname='".$king->db->escape($data['kname'])."'"));
	}else{
		$array[]=array('kname',12,$king->lang->get('system/conn/error/name'),$king->db->getRows_one("select kid from %s_conn where kname='".$king->db->escape($data['kname'])."' and kid<>$kid"));
	}

	$s=$king->htmForm($king->lang->get('system/conn/name'),kc_htm_input('kname',$data['kname'],50,400),$array);
	//urlpath
	$array=array(
		array('urlpath',0,0,50),
		array('urlpath',6),
		array('urlpath',12,$king->lang->get('system/conn/error/urlpath'),substr($data['urlpath'],-1,1)=='/'),
	);
	$s.=$king->htmForm($king->lang->get('system/conn/urlpath'),kc_htm_input('urlpath',$data['urlpath'],50,400),$array);
	//ksign
	$array=array(
		array('ksign',0,0,32),
	);
	$s.=$king->htmForm($king->lang->get('system/conn/sign'),kc_htm_input('ksign',$data['ksign'],32,250),$array);

	if($GLOBALS['ischeck']){

		$array=array(
			'kname'=>$data['kname'],
			'urlpath'=>$data['urlpath'],
			'ksign'=>$data['ksign'],
		);

		if(empty($kid)){//insert
			$array['norder']=$king->db->neworder('%s_conn');
			$king->db->insert('%s_conn',$array);
		}else{
			$king->db->update('%s_conn',$array,"kid=$kid");
		}
		$king->cache->del('system/conn/info');
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/'.(empty($kid) ?'add':'edt'))."</p>",1);
	}

	$but=kc_htm_a($king->lang->get("system/common/".(empty($kid)?'add':'edit')),"{URL:'../system/manage.php',CMD:'conn_edt',kid:'$kid',IS:1}");
	kc_ajax($king->lang->get('system/title/conn'),$s,$but,'',440,170+$GLOBALS['check_num']*15);

}
/* ------>>> 文件管理 <<<----------------------------- */
function inc_brow(){
	global $king;

	$king->access('#brow');

	$id=kc_get('id',0);
	$path=kc_get('path',0);//追加展开目录的路径
	$file=kc_get('file',0);//若是指定某个文件的话，取值
	$path_def=kc_get('path_def',0);//原始定位路径,初始路径按照这个展开
	$filetype=kc_get('filetype',2,1);
	$is=kc_get('is',2,1);//是否继续插入，如果继续插入的话
	$jsfun=kc_get('jsfun',0);
	$ID=kc_get('ID',0);
	$obj=kc_get('obj',0);//对象，展开左侧栏目的时候，追加展开数据的对象
	$space=(isset($_POST['space']) || isset($_GET['space'])) ? kc_get('space',2,1) : 0;//目录离左侧的空格
	$verbs="id:'$id',filetype:$filetype,is:$is,jsfun:'$jsfun',URL:'../system/manage.php',IS:1";//不变的传递值

	$info=array(
		'id'=>$id,
		'path'=>$path,
		'path_def'=>$path_def,
		'filetype'=>$filetype,
		'is'=>$is,
		'jsfun'=>$jsfun,
		'file'=>$file,
		'verbs'=>$verbs,
		'ID'=>$ID,
		'obj'=>$obj,
		'space'=>$space,
	);
	return $info;

}
/**
	浏览器
*/
function king_ajax_brow(){
	global $king;

	$verbs='';
	$info=inc_brow();extract($info);

	$s='<div id="browleft">';
	$s.='<p id="brow_root"><em id="k_brow_root"><img src="../system/images/loading.gif" class="os"/></em>'.$king->lang->get('system/common/folder').'</p>';
	$s.='</div>';
	$s.='<div id="browright"></div>';

	/***************
	左侧目录列表相关函数
	@paramissub 如果有下级目录，则issub为1
	@return */
	$s.="<script type=\"text/javascript\" >";
	$s.="function lll(path,dir,issub,space){var spath=path+dir;var id=spath.replace(/[\]\[\/\!\@\#\$\%\^\&\(\)\~\+\;\'\,\.\`\-\=]/g,'_');var s='<p id=\"k_brow_obj_'+id+'\">';";
	$s.="for(i=0;i<space;i++){s+=\$.kc_icon()};";//space计算
	//+-展开按钮
	$s.="s+='<a id=\"k_brow_dir_'+id+'\" href=\"javascript:;\" class=\"k_ajax\" rel=\"{". addslashes($verbs) .",CMD:\'brow_left\',path:\''+path+dir+'/\',ID:\'k_brow_dir_'+id+'\',obj:\'k_brow_obj_'+id+'\',space:'+(space*1+1)+'}\">'";
	$s.="+\$.kc_icon(issub==1?'k1':'')+'</a>'";

	//点击后在右侧显示对应目录下面的文件
	$s.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{".addslashes($verbs).",CMD:\'brow_right\',path:\''+path+dir+'/\',ID:\'browright\'}\">'+\$.kc_icon('b1')+'<em id=\"k_brow_sub_'+id+'\">'+dir+'</em></a></p>';";
	$s.="return s};";
	$s.="function remove_dir(id,space){\$(\"[id^='\"+id+\"_']\").remove()}";//这个是比portal/manage.php里的更简单的方法
	$s.="</script>";/*
	****************/

	$js="\$.kc_ajax({{$verbs},CMD:'brow_left',path:'',ID:'k_brow_root',obj:'brow_root',path_def:'{$path_def}'});";//调用左侧导航菜单
	$js.="\$.kc_ajax({{$verbs},CMD:'brow_right',path:'{$path_def}',ID:'browright'});";//调用右侧文件列表


	$but="<a href=\"javascript:;\" class=\"k_close\">".$king->lang->get('system/common/close')."</a>";
	kc_ajax($king->lang->get('system/common/filemanage'),$s,$but,$js,600,390);
}
/**
	左侧列表
*/
function king_ajax_brow_left(){
	global $king;
	$obj='';
	$path='';
	$path_def='';
	$verbs='';
	$space=0;


	$info=inc_brow();extract($info);

	$js='';

	//如果isopen==1的话，调用关闭
	if(kc_post('isopen')){//没有值或者0的时候是+号
		$js="\$('#$ID').attr('rel','{".addslashes($verbs).",CMD:\'brow_left\',path:\'$path\',ID:\'$ID\',obj:\'$obj\',space:$space,isopen:0}');";
		$js.="remove_dir('$obj','$space');";
		kc_ajax('',kc_icon('k1'),'',$js);
	}

	$cachepath="system/filemanage/{$path}index";
	$jscache=$king->cache->get($cachepath,1);

	if(!$jscache){
		$folders=kc_f_getdir($path,'dir');
		$jscache="var kjs='';";
		foreach($folders as $val){
			$getDir=kc_f_getdir($path.$val.'/','dir');
			$isSub=count($getDir) ? 1 : 0;
			$jscache.="kjs+=lll('$path','$val',$isSub,$space);";
		}
		$jscache.="\$('#$obj').after(kjs);";
		$jscache.="\$.kc_ready(\"[id^='k_brow_obj_"._path2id($path)."']\");";
		$king->cache->put($cachepath,$jscache);
	}

	$js.=$jscache;


	if($obj=='brow_root'){//根目录的话，输出a1
		kc_ajax('',kc_icon('a1'),'',$js);
	}else{
		$js="\$('#$ID').attr('rel','{".addslashes($verbs).",CMD:\'brow_left\',path:\'$path\',ID:\'$ID\',obj:\'$obj\',space:$space,isopen:1}');".$js;//展开后，重写成关闭的
		kc_ajax('',kc_icon('l1'),'',$js);;
	}

}
function _path2id($path){
	$id=preg_replace("/[\]\[\/\!\@\#\$\%\^\&\(\)\~\+\;\'\,\.\`\-\=]/",'_',$path);
	/*
	preg_match('\_',$id)
	*/
	return $id;
}
/**
	右侧列表
*/
function king_ajax_brow_right(){
	global $king;

	$filetype='';
	$is='';
	$jsfun='';
	$path='';

	$info=inc_brow();
	extract($info);

	$s="<table cellspacing=\"0\">";
	$s.="<tr id=\"brow_top\"><th>";

	$s.="<a href=\"javascript:;\" id=\"a_brow_upfile\" class=\"k_ajax fr\" rel=\"{{$verbs},CMD:'brow_upfile',isopen:1,path:'$path',IS:1,ID:'brow_top'}\">";
	$s.=kc_icon('j4').$king->lang->get('system/common/upfile')."</a>";

	$s.="<a href=\"javascript:;\" id=\"a_brow_md\" class=\"k_ajax fr\" rel=\"{{$verbs},CMD:'brow_md',path:'$path',isopen:1,IS:1,ID:'brow_top'}\">";
	$s.=kc_icon('h1').$king->lang->get('system/common/md')."</a>";

	$s.="<tt title=\"$path\">".kc_icon('b1')."/".kc_short($path,12,15)."</tt>";
	$s.="</th></tr></table>";
	$s.="<table cellspacing=\"0\">";
	$s.="<tr>";
	$s.=$filetype==0 ? '<th class="line">&nbsp;Image</th>':'';
	$s.="<th class=\"l line\">&nbsp;FileName</th><th class=\"line r\">Size&nbsp;</th><th class=\"line r\"></th></tr>";

	$i=0;
	kc_f_md($path);
	$folders=kc_f_getdir($path,$king->getfext($filetype));
	foreach($folders as $val){
		$s.="<tr class=\"bg".($i++%2)."\">";
		//插入
		$insert="onClick=\"\$('#$id').val('$path$val');";
		$insert.=$is ? $jsfun : "\$.kc_close()";
		$insert.="\" title=\"".$king->lang->get('system/common/insert')."\"";

		if($filetype==0){//图片
			$s.="<td><span><a $insert href=\"javascript:;\" class=\"img\"><img src=\"../$path$val\" /></a></span></td>";
			$s.="<td><a href=\"../$path$val\" target=\"_blank\" >".kc_short($val)."</a></td>";
		}else{
			$s.="<td $insert><label>".kc_f_kc_icon($val).kc_short($val).'</label></td>';
		}
		$s.='<td class="r">'.kc_f_size(kc_f_filesize($path.$val)).'</td>';
		$s.='<td class="r">';


		$s.="<a href=\"javascript:;\" $insert>".kc_icon('l4')."</a>";
		//删除文件
		$s.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{{$verbs},CMD:'delete_browfile',path:'$path',file:'$val',IS:1}\">".kc_icon('j2',$king->lang->get('system/common/delete'))."</a>";
/*
*/
		$s.='</td></tr>';
	}
	$s.='</table>';
	$js="\$('#browleft>p>a>em').removeClass('sel');\$('#k_brow_sub_"._path2id(substr($path,0,-1))."').addClass('sel')";
	kc_ajax('',$s,'',$js);
}
/**
删除文件
*/
function king_ajax_delete_browfile(){
	global $king;
	$king->access('#brow_delfile');
	$file='';//可以不定义，但是在编辑器报错
	$info=inc_brow();
	$path='';
	extract($info);

	kc_f_delete($path.$file);


	$js="\$.kc_ajax({{$verbs},CMD:'brow_right',path:'{$path}',ID:'browright'});";//调用右侧文件列表

	kc_ajax('','','',$js);

}
/*
创建目录
*/
function king_ajax_brow_md(){
	global $king;
	$king->access('#brow_md');

	$path='';

	$info=inc_brow();extract($info);

	$isopen=kc_post('isopen',2,1);

	if($isopen){
		$cmd="\$.kc_ajax('". addslashes("{{$verbs},CMD:'brow_md',isopen:0,path:'$path',IS:1,ID:'brow_top',VAL:'brow_md_name'}") ."')";
		$s="<tr id=\"brow_md\"><th colspan=\"3\">";
		$s.="<p class=\"c\">".$king->lang->get('system/common/folder')."：".kc_htm_input('brow_md_name','',200,200);
		$s.=" <input onClick=\"$cmd\" type=\"button\" value=\"".$king->lang->get('system/common/new')."\"/></p>";
		$s.="</th></tr>";

		$js="\$('#brow_top').after('". addslashes($s) ."');";
		$js.="\$('#a_brow_md').attr('rel','". addslashes("{{$verbs},CMD:'brow_md',isopen:0,path:'$path',IS:1,ID:'brow_top'}") ."');";
		$js.="\$('#brow_md_name').keydown(function(e){\$(e).unbind();if(e.keyCode==13){$cmd}})";

	}else{
		$js='';
		$brow_md_name=kc_post('brow_md_name');
		if(isset($brow_md_name{0})){//如果有值的话，就创建目录
			if(kc_validate($brow_md_name,24)){//验证ok的话
				kc_f_md($path.$brow_md_name);//创建目录
				$js.="\$.kc_ajax({{$verbs},CMD:'brow_right',path:'$path{$brow_md_name}/',ID:'browright'});";//进入到新建的目录里

			}else{
				$js.="alert('". addslashes($king->lang->get('system/error/dir')) ."');";
			}

		}

		$js.="\$('#brow_md').remove();";
		$js.="\$('#a_brow_md').attr('rel','". addslashes("{{$verbs},CMD:'brow_md',isopen:1,path:'$path',IS:1,ID:'brow_top'}") ."')";
		;
	}

	kc_ajax('','','',$js);

}
function king_ajax_brow_upfile(){
	global $king;
	$king->access('#brow_upfile');

	$is='';
	$jsfun='';
	$isopen='';

	$info=inc_brow();
	extract($info);

	$isopen=kc_post('isopen',2,1);

	if($isopen){
		$s="<tr id=\"brow_upfile\"><th>";
		$s.="<iframe src=\"../system/manage.php?action=iframe&CMD=upfile&id=$id&path=$path&filetype=$filetype&is=$is&jsfun=$jsfun\" frameborder=\"no\" scrolling=\"no\" marginwidth=\"0\" marginheight=\"0\" style=\"width:360px;height:180px;\"></iframe>";
		$s.="</th></tr>";
		$js="\$('#brow_top').after('$s');";
		$js.="\$('#a_brow_upfile').attr('rel','".addslashes("{{$verbs},CMD:'brow_upfile',isopen:0,path:'$path',IS:1,ID:'brow_top'}")."')";
	}else{
		$js="\$('#brow_upfile').remove();";
		$js.="\$('#a_brow_upfile').attr('rel','".addslashes("{{$verbs},CMD:'brow_upfile',isopen:1,path:'$path',IS:1,ID:'brow_top'}")."')";
	}

	kc_ajax('','','',$js);

}
function king_iframe_upfile(){
	global $king;
	$king->access('#brow_upfile');
	$filetype='';
	$is='';
	$jsfun='';
	$path='';

	$info=inc_brow();extract($info);

	$s="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	<html xmlns=\"http://www.w3.org/1999/xhtml\"><head>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".PAGE_CHARSET."\" />
	<script type=\"text/javascript\" src=\"".$king->config('inst')."system/js/jquery.js\"></script>
	<style type=\"text/css\">
		<!--
		*{margin:0;padding:0;font-size:12px;}
		body{background:#F2F9FD;margin-left:20px;}
		.k_in{width:250px;margin-left:5px;font-size:14px;}
		label{width:70px;position:absolute;overflow:hidden;display:block;float:left;
			-ms-filter:\"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)\";filter:alpha(opacity=0);/*IE*/opacity:0;}
		.k_up{width:70px;display:block;font-size:16px;height:24px;;margin-right:-5px;}
		p,tr{height:25px;}
		.k_brow{display:block;width:60px;border:1px solid;padding:2px 0;
			border-color:#787878 #ADADAD #5F5F5F #ADADAD;background:#F2F2F2;}
		p{text-indent:10px;text-align:center;display:block;height:25px;}
		table{margin:0 auto;width:100%;}
		td{text-align:left;position:relative;}
		th{text-align:left;text-indent:10px;}
		.submit{width:80px;margin-left:10px;}
		.c1{color:#3875D7;text-align:left;} /* 蓝 */
		.c2{color:#CF0422;text-align:left;} /* 红 */
		.l{text-align:left;}
		.sel{background:#FC0;}
		-->
	</style>
	</head><body>";
	$s.="<form name=\"form1\" enctype=\"multipart/form-data\" method=\"post\" action=\"manage.php?action=iframe&CMD=upfile\">";

	if($_FILES){

		$ftypes=$king->getfext($filetype);

		$_array_filetype=explode('|',$ftypes);//扩展名转换为数组
		$s.="<p class=\"l\"><strong>".$king->lang->get('system/common/list').":</strong></p>";
		for($i=0;$i<5;$i++){
			//判断file元素是否包含了上传文件
			if($_FILES['file']['name'][$i]!=''){
				//判断文件扩展名
				$ext=kc_f_ext($_FILES['file']['name'][$i]);//文件扩展名
				if(in_array($ext,$_array_filetype)){
					kc_post('isrename')==1
						? $filename=time().$i.'.'.$ext //这里定义的filename和$info数组中的filename不是一个对象
						: $filename=$_FILES['file']['name'][$i];
					//上传操作
					if(move_uploaded_file($_FILES['file']['tmp_name'][$i],ROOT.$path.iconv('UTF-8','',$filename))){
						//上传成功,并记录上传文件地址
						$s.="<p class=\"c1\">".$king->lang->get('system/common/file').($i+1).' : '.$king->lang->get('system/brow/t1');
						$s.='<a href="../'.$path.$filename.'" target="_blank">['.$king->lang->get('system/common/view').': '.$filename.']</a>';

						$insert="onClick=\"window.parent.\$('#$id').val('$path$filename');window.parent.";
						$insert.=$is ? $jsfun : "\$.kc_close()";
						$insert.="\" title=\"".$king->lang->get('system/common/insert')."\"";



						$s.="<a href=\"javascript:;\" $insert>[".$king->lang->get('system/common/insert').']</a></p>';
						//增加插入功能
						$array=array(
							'ktitle'=>kc_post("name$i"),
							'kpath'=>$path.$filename,
							'ndate'=>time(),
							'adminid'=>$king->admin['adminid'],
							'ntype'=>$filetype,
						);
						$king->db->insert('%s_upfile',$array);

					}else{
						$s.="<p class=\"c2\">".$king->lang->get('system/common/file').($i+1).' : '.$king->lang->get('system/brow/t2')."</p>";
					}
				}else{
					$s.="<p class=\"c2\">".$king->lang->get('system/common/file').($i+1).' : '.$king->lang->get('system/brow/t3')."</p>";
				}
			}else{
				$s.="<p class=\"c2\">".$king->lang->get('system/common/file').($i+1).' : '.$king->lang->get('system/brow/t4')."</p>";
			}
		}
		$s.="<p class=\"l\"><input type=\"button\" class=\"submit\" onClick=\"window.parent.\$('#a_brow_upfile').attr('rel','".addslashes("{{$verbs},CMD:'brow_upfile',isopen:1,path:'$path',IS:1,ID:'brow_top'}")."');window.parent.\$('#brow_upfile').remove();\" value=\"".$king->lang->get('system/common/close')."\"/></p>";

	}else{
		$s.="<table cellspacing=\"0\">";
		$s.="<tr><th>".$king->lang->get('system/common/select')."</th><th>".$king->lang->get('system/upfile/title')."</th></tr>";
		for($i=0;$i<5;$i++){
			$s.="<tr><td><label><input onClick=\"\$('#k_brow_$i').addClass('sel')\"  class=\"k_up\" type=\"file\" name=\"file[]\" /></label>";
			$s.="<input id=\"k_brow_$i\" class=\"k_brow\" type=\"button\"value=\"".$king->lang->get('system/common/brow')."..\" /></td>";
			$s.="<td><input type=\"text\" class=\"k_in\" name=\"name$i\"/></td>";
			$s.="</tr>";
		}
		$s.="</table>";
		$s.="<p><input type=\"checkbox\" name=\"isrename\" id=\"isrename\" value=\"1\" checked=\"true\" /> ".$king->lang->get('system/brow/rename');
		$s.="<input type=\"submit\" class=\"submit\" value=\"".$king->lang->get('system/common/upload')."\"/>";
		$s.="<input type=\"button\" class=\"submit\" onClick=\"window.parent.\$('#a_brow_upfile').attr('rel','".addslashes("{{$verbs},CMD:'brow_upfile',isopen:1,path:'$path',IS:1,ID:'brow_top'}")."');window.parent.\$('#brow_upfile').remove();\" value=\"".$king->lang->get('system/common/close')."\"/></p>";
		$s.=kc_htm_hidden($info);
	}

	$s.="</form></body></html>";

	echo $s;
	//成功上传后，Remove #brow_upfile

}
/* ------>>> Iframe <<<---------------------------- */

function king_inc_lnk_left(){
	global $king;
	$kid=kc_get('kid',2);
	$adminid=!empty($_GET['adminid']) ? kc_get('adminid',2,1) : $king->admin['adminid'];

	$left=array(
		'lnk'=>array(
			'href'=>'manage.php?action=lnk&adminid='.$adminid,
			'ico'=>'e8',
			'title'=>$king->lang->get('system/common/list'),
		),
		'lnkedt'=>array(
			'href'=>'manage.php?action=lnkedt&adminid='.$adminid,
			'ico'=>$kid ? 'g8' : 'h8',
			'title'=>$king->lang->get('system/common/'.($kid ? 'edit' : 'add')),
		),
	);
	return $left;
}
function king_inc_admin_left(){
	global $king;

	$adminid=kc_get('adminid');

	$left=array(
		'admin'=>array(
			'href'=>'manage.php?action=admin',
			'ico'=>'a6',
			'title'=>$king->lang->get('system/common/list'),
		),
		'admin_edt'=>array(
			'href'=>'manage.php?action=admin_edt',
			'ico'=>($adminid ? 'b6' : 'c6'),
			'title'=>$king->lang->get('system/common/'.($adminid ? 'edit' : 'add')),
		),
	);
/*
	$s='<a'.$_array['admin'].' href="manage.php?action=admin">'.kc_icon('a6').$king->lang->get('system/common/list').'</a>';
	$adminid
		? $s.='<a'.$_array['admin_edt'].' href="manage.php?action=admin_edt">'.kc_icon('b6').$king->lang->get('system/common/edit').'</a>'
		: $s.='<a'.$_array['admin_edt'].' href="manage.php?action=admin_edt">'.kc_icon('c6').$king->lang->get('system/common/add').'</a>';
	return $s;
*/
	return $left;
}


/* ------>>> KingCMS for PHP <<<--------------------- */

function king_def(){
	global $king;

	$king->access(0);

/*
	//检查是否已经安装模块，如果没有，则提示安装模块
	$module=$king->getModule();
	$s=empty($module) ? "<script>\$.kc_ajax({CMD:'findmodule'})</script>" : '';
*/
	$s='';

	$king->skin->setPath('system.home.htm');//设置为home模板

	$right=array();
	if($king->acc('#novice')){//若启用了新手入门选项

		if(!$res=$king->db->getRows_one("select admincount from %a_admin where adminid=".$king->admin['adminid']))
			kc_error('system/error/param');
		$count=$res['admincount'];
		if($count==1)//如果是第一次访问的话，就自动弹出新手入门
			$s.="<script>\$.kc_ajax({CMD:'novice'})</script>";

		$right[]=array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'novice\'}',
			'title'=>$king->lang->get('system/level/novice'),
			'ico'=>'n1',
		);
	}
	$king->skin->output($king->lang->get('system/title/home'),0,$right,$s);
} //!king_def

//king_log
function king_log(){
	global $king;

	$king->access('#log');

	$_sql='select kid,adminname,nip,nlog,ndate,ktext from %s_log order by kid desc';

	if(!$_res=$king->db->getRows($_sql,1))
		$_res=array();


	//准备开始列表
	$_cmd=array(
		'delete_log'=>$king->lang->get('system/common/del'),
		'-',
		'clear_log'=>$king->lang->get('system/common/clear'),
	);
	$_js=array(
		"$.kc_list(K[0],K[1])",
		"'<span class=\"c'+K[3]+'\">'+logtxt[K[3]]+iski(K[5])+'</span>'",
		"$.kc_long2ip(K[2])",
		"K[4]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=log&pid=PID&rn=RN',$king->db->getRows_number('%s_log')));
	$s.="function iski(txt){var I1;txt==''?I1='':I1=' ['+txt+']';return I1;};";

	//设置数组
	$_array=array();
	for($i=0;$i<=10;$i++){
		$_array[$i]=$king->lang->get('system/log/num'.$i);
	}
	$s.=kc_js2array('logtxt',$_array);

	$s.='ll(\''.$king->lang->get('system/log/name').'\',\''.$king->lang->get('system/log/active').'\',\''.$king->lang->get('system/common/ip').'\',\''.$king->lang->get('system/common/date').'\',1);';	//th

	foreach($_res as $_rs){	 //td
		$s.='ll('.$_rs['kid'].',\''.$_rs['adminname'].'\','.($_rs['nip']).','.$_rs['nlog'].',\''.kc_formatdate($_rs['ndate']).'\',\''.addslashes(htmlspecialchars($_rs['ktext'])).'\',0);';//date('Y-m-d H:i:s',$_rs['logdate'])
	}

	//结束列表
	$s.=$king->closeList();

	$king->skin->output($king->lang->get('system/title/log'),'','',$s);

} //!king_log

//king_config
function king_config(){
	global $king;
	$king->access('#systeminfo');
	$kmodule=kc_get('kmodule',4);
	if(!isset($kmodule{0}))
		$kmodule='system';

	if($GLOBALS['ismethod']){
		$data=$_POST;
	}else{
		if(!$res=$king->db->getRows("select kname,kvalue from %s_system where kmodule='{$kmodule}' and isshow=1;"))
			kc_error($king->lang->get('system/error/param'));

		$data=array();
		foreach($res as $val){
			$data[$val['kname']]=$val['kvalue'];
		}
	}
	$data=kc_data(array('kname','kvalue'),$data);

	$modules=$king->getModule();


	$s='';

	$is=false;
	if($caption=$king->db->getRows("select cid,kpath from %s_system_caption where kmodule='{$kmodule}' order by cid")){
		foreach($caption as $val){
			$s.= $is
				? $king->splitForm($king->lang->get($kmodule.'/caption/'.$val['kpath']))
				: $king->openForm('manage.php?action=config',$king->lang->get($kmodule.'/caption/'.$val['kpath']));
			$is=true;
			if($res=$king->db->getRows("select * from %s_system where kmodule='{$kmodule}' and isshow=1 and cid={$val['cid']} order by norder asc;")){
				foreach($res as $rs){
					$s.=$king->formdecode($rs,$data);
				}
			}
		}
	}


	$s.=kc_htm_hidden(array('kmodule'=>$kmodule));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){
		//从$data数组中去掉kmodule键
		$data=array_diff_key($data,array('kmodule'=>'删除kmodule键值//此为注释,无需翻译'));
		foreach($data as $key => $val){
			$king->db->update('%s_system',array('kvalue'=>$val)," kmodule='{$kmodule}' and kname='{$key}'");
		}
		$king->cache->del('system/config/'.$kmodule);

		//写log
		$king->log(7,$king->lang->get('system/title/system'));


		kc_goto($king->lang->get('system/goto/ok'),'manage.php?action=config&kmodule='.$kmodule);

	}

	$left=array(
		array(
			'href'=>'manage.php?action=config',
			'ico'=>'e7',
			'title'=>$king->lang->get('system/name'),
			'class'=>($kmodule=='system' ? 'sel' :''),
		),
	);

//	$left='<a'.$_array['system'].' href="manage.php?action=config">'.kc_icon('e7').$king->lang->get('system/name').'</a>';
	foreach($modules as $val){
		if($king->db->getRows_one("select kname from %s_system where kmodule='{$val}' and isshow=1;")){
/*
			$kname=$king->lang->get($val.'/name');

			$left.='<a'.$_array[$val].' href="manage.php?action=config&kmodule='.$val.'">'.kc_icon('e7').$kname.'</a>';
*/
			$left[]=array(
				'href'=>'manage.php?action=config&kmodule='.$val,
				'ico'=>'e7',
				'title'=>$king->lang->get($val.'/name'),
				'class'=>($kmodule==$val ? 'sel' :''),
			);
		}
	}

	$king->skin->output($king->lang->get('system/title/system'),$left,'',$s);

}//!king_config


 //管理员列表
//king_admin
function king_admin(){
	global $king;

	$king->access('admin');

	$_sql='select adminid,adminname,admincount,admindate,adminlevel from %a_admin where isdelete=0 order by admincount desc,adminid desc';

	$_res=$king->db->getRows($_sql,1);

	//准备开始列表
	$_cmd=array(
		'delete_admin'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+$.kc_icon('b6','".$king->lang->get('system/common/edit')."')+'</a>'";
	$manage.="+'<a href=\"manage.php?action=lnk&adminid='+K[0]+'\">'+$.kc_icon('e8','".$king->lang->get('system/title/lnk')."')+'</a>'";
	$_js=array(
		"$.kc_list(K[0],K[1],isLink(K[0]))",//'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+K[1]+'</a>'
//		"checkself(K[0])",
		$manage,
		"checklevel(K[4])",
		"'<i>'+K[2]+'</i>'",
		"K[3]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=admin&pid=PID&rn=RN',$king->db->getRows_number('%a_admin')));
	$s.="function checkself(id){var I1;if(id==".$king->admin['adminid']."){I1=''}else{I1='<a href=\"manage.php?action=admin_edt&adminid='+id+'\">'+$.kc_icon('b6')+'</a>'};return I1;};";
	$s.="function isLink(id){var I1;if(id!=".$king->admin['adminid']."){I1='manage.php?action=admin_edt&adminid='+id};return I1;};";
	$s.="function checklevel(level){var I1;";
	$s.="level=='admin'?I1=$.kc_icon('e6')+'".$king->lang->get('system/level/admin')."':I1=$.kc_icon('f6')+'".$king->lang->get('system/level/admini')."';return I1;};";

	$s.='ll(\''.$king->lang->get('system/admin/name').'\',\'manage\',\''.$king->lang->get('system/admin/level').'\',\'<i>'.$king->lang->get('system/admin/count').'</i>\',\''.$king->lang->get('system/admin/date').'\',1);';	//th

	foreach($_res as $_rs){	 //td
		$s.='ll('.$_rs['adminid'].',\''.$_rs['adminname'].'\','.($_rs['admincount']).',\''.kc_formatdate($_rs['admindate']).'\',\''.$_rs['adminlevel'].'\',0);';
	}

	$s.="obj_list=\$('#list_".$king->admin['adminid']."');if(obj_list!=null){obj_list.disabled=true;obj_list.value=0};";
	//结束列表
	$s.=$king->closeList();

	$king->skin->output($king->lang->get('system/title/admin'),king_inc_admin_left(),'',$s);

} //!king_admin
 //添加&编辑管理员信息
//king_admin_edt
function king_admin_edt(){
	global $king;

	$data=array();

	$s=$king->access('admin');

	$_sql="adminname,adminpass,adminlevel,adminlanguage,admineditor,adminmode,adminlogin,siteurl";//,admindiymenu

	$_adminid=kc_get('adminid');

	if($GLOBALS['ismethod']||$_adminid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['adminlanguage']=LANGUAGE;
			$data['adminlogin']='manage.php';
		}
	}else{//编辑数据，从数据库读出
		$data=$king->db->getRows_one('select '.$_sql.' from %a_admin where adminid='.$_adminid.' limit 1;');
	}
	$fields=explode(',',$_sql);
	$data=kc_data($fields,$data);

	$s=$king->openForm('manage.php?action=admin_edt');
	//帐号
	if($_adminid){//update
		$s.=$king->htmForm($king->lang->get('system/admin/name'),'<input class="k_in w100" type="text" disabled="true" value="'.htmlspecialchars($data['adminname']).'" />');
		$s.=kc_htm_hidden(array('adminname'=>$data['adminname']));
	}else{
		$_array=array(
			array('adminname',0,2,12),
			array('adminname',1),
			array('adminname',12,$king->lang->get('system/check/none'),$king->db->getRows_one("select adminid from %a_admin where adminname='".$king->db->escape(kc_post('adminname'))."';"))
		);
		$s.=$king->htmForm($king->lang->get("system/admin/name").' (2-12)','<input class="k_in w150" type="text" name="adminname" value="'.htmlspecialchars($data['adminname']).'" maxlength="12" />',$_array);
	}
	//密码
	if($_adminid){
		$_array=array(
			array('pass1',17,null,'pass2'),
		);
	}else{
		$_array=array(
			array('pass1',0,6,30),
			array('pass1',17,null,'pass2'),
		);
	}
	$s.=$king->htmForm($king->lang->get('system/admin/pass1').' (6-30)','<input class="k_in w150" type="password" name="pass1" maxlength="30" />',$_array);
	$s.=$king->htmForm($king->lang->get('system/admin/pass2'),'<input class="k_in w150" type="password" name="pass2" maxlength="30" />');
	//adminlanguage
	$s.=$king->htmForm($king->lang->get('system/common/language'),kc_htm_select('adminlanguage',kc_htm_selectlang(),$data['adminlanguage']));
	//admineditor
	$array_dir=kc_f_getdir('system/editor/','dir');
	$_array=array();
	foreach($array_dir as $val){
		$_array[$val]=$val;
	}
	$s.=$king->htmForm($king->lang->get('system/common/editor'),kc_htm_select('admineditor',$_array,$data['admineditor']));
	//adminmode
	$_array=array(
		2=>$king->lang->get('system/admin/mode2'),
		1=>$king->lang->get('system/admin/mode1'),
		0=>$king->lang->get('system/admin/mode0')
	);
	$s.=$king->htmForm($king->lang->get('system/admin/mode'),kc_htm_radio('adminmode',$_array,$data['adminmode']));
	//adminlevel
	if($king->admin['adminid']!=$_adminid){

		$data['adminlevel']=='admin'
			? $_checkbox='<input type="checkbox" id="adminlevel" name="adminlevel" value="admin" onclick="javascript:selevel()" checked="checked" />'
			: $_checkbox='<input type="checkbox" id="adminlevel" name="adminlevel" value="admin" onclick="javascript:selevel()" />';
		$_array=array(
			'-'.$king->lang->get('system/name').'-',
			$king->lang->get('system/level/channel').'[',
			'#open_settring'=>$king->lang->get('system/common/setting'),
			'#open_help'=>$king->lang->get('system/common/help'),
			']',
			'[',
			'#resetpass'=>$king->lang->get('system/level/resetpass'),
			']',
			'[',
			'#novice'=>$king->lang->get('system/level/novice'),
			']',
			'|',
			$king->lang->get('system/common/system').'[',
			'#systeminfo'=>$king->lang->get('system/level/config'),
			'#systemcache'=>$king->lang->get('system/level/clearcache'),
			'#module'=>$king->lang->get('system/level/module'),
			'#plugin'=>$king->lang->get('system/level/plugin'),
			'#lnk'=>$king->lang->get('system/level/lnk'),
			'#phpinfo'=>$king->lang->get('system/level/phpinfo'),
			'#timingtask'=>$king->lang->get('system/level/timingtask'),
			']',
			'|',
			$king->lang->get('system/level/log').'[',
			'#log'=>$king->lang->get('system/common/access'),
			'#log_delete'=>$king->lang->get('system/common/del'),
			']',
			'-',
			$king->lang->get('system/common/filemanage').'[',
			'#brow'=>$king->lang->get('system/common/access'),
			'#brow_md'=>$king->lang->get('system/common/md'),
			'#brow_upfile'=>$king->lang->get('system/common/upfile'),
			'#brow_delfile'=>$king->lang->get('system/common/delfile'),
			']',
			'|',
			$king->lang->get('system/level/event').'[',
			'#event'=>$king->lang->get('system/common/access'),
			'#event_delete'=>$king->lang->get('system/common/del'),
			']',
			'-',
			$king->lang->get('system/upfile/manage').'[',
			'#upfile'=>$king->lang->get('system/upfile/access'),
			'#upfile_edt'=>$king->lang->get('system/common/edit'),
			'#upfile_delete'=>$king->lang->get('system/upfile/del'),
			']',
			'-',
			$king->lang->get('system/bot/title').'[',
			'#bot'=>$king->lang->get('system/common/access'),
			'#botedt'=>$king->lang->get('system/common/edit'),
			'#botdel'=>$king->lang->get('system/common/del'),
			']',
			'|',
			$king->lang->get('system/title/conn').'[',
			'#conn'=>$king->lang->get('system/common/access'),
			'#conn_edt'=>$king->lang->get('system/common/edit'),
			'#conn_del'=>$king->lang->get('system/common/del'),
			']',
		);

		$module=$king->getModule();
		foreach($module as $val){
			$language= is_file(ROOT.$val.'/language/'.$king->admin['adminlanguage'].'.xml') ? $king->admin['adminlanguage'] : LANGUAGE;

			$xml=new KC_XML_class;
			$xml->load_file($val.'/language/'.$language.'.xml');

			$array_kingcms=$xml->xml2array();
			$array_access=$array_kingcms['ACCESS'];


			if($array_access){
				$_array[]='|';
				$_array[]='|';
				$_array[]='-'.$king->lang->get($val.'/name').'-';

	//			kc_error('<pre>'.print_r($array_access,1));
				foreach($array_access as $k => $v){
					$v=='|'
						?$_array[]='|'
						:$_array[$k]=$v;
				}

			}

		}

		$_s ='<div id="levels">';
		$_s.=kc_htm_checkbox('level',$_array,$data['adminlevel']);
		$_s.='</div>';

		$s.=$king->htmForm($king->lang->get('system/admin/setlevel'),'<span>'.$_checkbox.'<label for="adminlevel">'.$king->lang->get('system/level/admin').'</label></span>'.$_s);

		$s.="<script>function selevel(){if (\$('#adminlevel').attr('checked')==true){\$('#levels').hide()}";
		$s.="else{\$('#levels').show();}};selevel();</script>";
	}

	//adminlogin
	$_array=array(
		array('adminlogin',0,5,100),
	);
	$array_value=array(
		'../system/manage.php'=>$king->lang->get('system/common/home'),
		'../portal/manage.php'=>$king->lang->get('system/title/list'),
	);
	$s.=$king->htmForm($king->lang->get('system/admin/login').' (5-100)','<input type="text" name="adminlogin" id="adminlogin" class="k_in w300" value="'.htmlspecialchars($data['adminlogin']).'" maxlength="100" />'.kc_htm_setvalue('adminlogin',$array_value),$_array);
	//siteurl
	$_array=array(
		array('siteurl',0,0,100),
	);
	$s.=$king->htmForm($king->lang->get('system/admin/url').' (0-100)','<input type="text" name="siteurl" id="siteurl" class="k_in w300" value="'.htmlspecialchars($data['siteurl']).'" maxlength="100" />',$_array,null,kc_help('system/help/lockurl',350,150));

	$s.=kc_htm_hidden(array('adminid'=>$_adminid));

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){
		$_sql='adminlanguage,admineditor,adminmode,adminlogin,siteurl';//,admindiymenu
		if(!$_adminid)
			$_sql.=',adminname';

		$_array_sql=explode(',',$_sql);
		$_array=array();
		foreach($_array_sql as $val){
			$_array[$val]=$data[$val];
		}
//		$_array=array_combine($_array_sql,array_map('post',$_array_sql));

		if(kc_post('pass1'))
			$_array['adminpass']=md5(kc_post('pass1'));

		if($king->admin['adminid']!=$_adminid){
			if(kc_post('adminlevel')=='admin'){
				$_adminlevel='admin';
			}else{
				$data['level'][]=0;
				/**
				is_array($data['level'])
					? array_push($data['level'],0)
					: $data['level']=array(0);
				*/
				$_adminlevel=implode(',',$data['level']);
			}
			$_array['adminlevel']=$_adminlevel;

		}
		if($_adminid){
			$king->db->update('%a_admin',$_array,'adminid='.$_adminid);
			$_nlog=7;
			$king->cache->del('system/admin/'.$data['adminname']);
		}else{
//			kc_error('<pre>'.print_r($_array,1));
			$king->db->insert('%a_admin',$_array);
			$_nlog=5;
		}

		//写log
		$king->log($_nlog,'AdminName:'.$data['adminname']);
		//更新缓存
		$king->cache->rd('system/mainmenu/'.$king->admin['adminid']);
		$king->cache->del('system/admin/'.$king->admin['adminname']);

		kc_goto($king->lang->get('system/goto/is'),'manage.php?action=admin_edt','manage.php?action=admin');
	}

	$king->skin->output($king->lang->get('system/title/admin'),king_inc_admin_left(),'',$s);


}//!king_admin_edt
function king_phpinfo(){
	global $king;
	$king->access('#phpinfo');
	//phpinfo();
	$array=kc_parsePHPModules();

	$s='';
	$is=true;
	foreach($array as $key => $array_val){
		$s.=$is ? $king->openForm('',$key) : $king->splitForm($key);
		$is=false;
		foreach($array_val as $key => $val){
			$s.='<tbody><tr><td class="w300">'.$key.'</td><td>';

			if(is_array($val)){
				$s.=($val[0]==$val[1] ? $val[0] : $val[0].'<br/>'.$val[1]);
			}else{
				$s.=$val;
			}
			$s.='</td></tr></tbody>';
		}
	}
	$s.=$king->closeForm('none');

	$king->skin->output('PHPINFO()','','',$s);
}

/**
上传文件管理
king_upfile()
*/
function king_upfile(){
	global $king;
	$s=$king->access('#upfile');

	$is=kc_get('is');//$is==1为用户上传
	switch($is){

		case 1:
			if(!$king->isModule('portal')){
				kc_error($king->lang->get('system/error/param'));
			}
			$_sql='select f.kid,f.kpath,f.ndate,u.username,f.ktitle from %s_upfile f,%s_user u where f.adminid=0 and f.userid=u.userid order by f.ndate desc,kid desc';
			$name=$king->lang->get('system/upfile/username');
			$field='adminid';//用户上传的话，ADMINID=0
		break;

		default:
			$_sql='select f.kid,f.kpath,f.ndate,a.adminname,f.ktitle from %s_upfile f,%a_admin a where f.userid=0 and f.adminid=a.adminid order by f.ndate desc,kid desc';
			$name=$king->lang->get('system/admin/name');
			$field='userid';//ADMIN上传的话USERID=0
	}

	//准备开始列表
	$_cmd=array(
		'delete_upfile'=>$king->lang->get('system/common/del'),
	);
	$_manage="'<a href=\"../'+K[1]+'\" target=\"_blank\">'+$.kc_icon('g7')+'</a>'";
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'upfile_edt\',kid:'+K[0]+',METHOD:\'GET\'}\">'+$.kc_icon('e5')+'</a>'";
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete_upfile\',list:'+K[0]+'}\">'+$.kc_icon('g5','".$king->lang->get('system/common/del')."')+'</a>'";
	$_js=array(
		"$.kc_list(K[0],K[1],0,0,1,K[4])",
		$_manage,
		"K[5]",
		"K[2]",
		"K[3]",
	);
/*
id      ID
tit     标题
is      是否显示id
isgray  是否灰度
ico     图标
space   缩进
listico 列表页专用的前置ico
*/
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=upfile&is='.$is.'&pid=PID&rn=RN',$king->db->getRows_number('%s_upfile',"$field=0")));

	$s.='ll(\''.$king->lang->get('system/upfile/files').'\',\'manage\',\''.$king->lang->get('system/common/title').'\',\''.$name.'\',\''.$king->lang->get('system/common/date').'\',1);';

	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	foreach($res as $rs){
		$s.='ll('.$rs['kid'].',\''.$rs['kpath'].'\',\''.$rs['adminname'].'\',\''.kc_formatdate($rs['ndate']).'\',\''.kc_f_ico($rs['kpath']).'\',\''.$rs['ktitle'].'\',0);';
	}
	//结束列表
	$s.=$king->closeList();

	$left=array(
		array(
			'href'=>'manage.php?action=upfile',
			'ico'=>'e6',
			'title'=>$king->lang->get('system/upfile/admin'),
			'class'=>($is ? '' : 'sel'),
		),
	);

	if($king->isModule('portal')){
		$left[]=array(
			'href'=>'manage.php?action=upfile&is=1',
			'ico'=>'a6',
			'title'=>$king->lang->get('system/upfile/user'),
			'class'=>($is ? 'sel' : ''),
		);
	}

	$king->skin->output($king->lang->get('system/title/upfile'),$left,'',$s);

}

function king_module(){
	global $king;
	$s=$king->access('#module');

	$_sql='select kid,kpath,ndbver,islock,nshow from %s_module order by norder';// where kid1=0
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$_cmd=array(
		$king->lang->get('system/common/lockun'),
		'module_lock'=>$king->lang->get('system/common/lock'),
		'module_unlock'=>$king->lang->get('system/common/unlock'),
		$king->lang->get('system/common/del'),
		'delete_module'=>$king->lang->get('system/common/del'),
		'clear_cache'=>$king->lang->get('system/common/clearcache'),
	);
	$_manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete_module\',list:'+K[0]+'}\">'+$.kc_icon('l8','".$king->lang->get('system/common/del')."')+'</a>'";
	$_manage.="+$.kc_updown(K[0],'updown_module')";

	$_js=array(
		"$.kc_list(K[0],K[1],'../'+K[2]+'/manage.php',0,1,islock(K[5]))",//'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+K[1]+'</a>'
		"'<a href=\"../'+K[2]+'/manage.php\" target=\"_blank\">'+K[2]+'</a>'",
		$_manage,
		"'<i>'+isshow(K[0],K[6])+'</i>'",
		"K[3]",
		"K[4]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=module&pid=PID&rn=RN',$king->db->getRows_number('%s_module')));
	$s.='function islock(is){var I1;is?I1=\'b8\':I1=\'c8\';return I1;};';

		$s.='ll(\''.$king->lang->get('system/module/name').'\',\''.$king->lang->get('system/module/path').'\',\'manage\',\'<i>'.$king->lang->get('system/module/show').'</i>\',\''.$king->lang->get('system/module/sub').'\',\''.$king->lang->get('system/common/version').'\',1);';//th
	$s.="function isshow(id,is){var I1,ico;is?ico='n1':ico='n2';";
	$s.="I1='<a id=\"nshow_'+id+'\" class=\"k_ajax\" rel=\"{CMD:\'module_show\',is:'+(1-is)+',ID:\'nshow_'+id+'\',kid:'+id+',IS:2}\" >'+$.kc_icon(ico)+'</a>';return I1;};";

	foreach($res as $rs){//td
		$kname=$king->lang->get($rs['kpath'].'/name');

		$subs='';//获得子模块
		if($resSub=$king->db->getRows("select kname,kpath from %s_module where kid1={$rs['kid']}")){
			foreach($resSub as $val){
				$subs.="[<a href=\"../{$val['kpath']}/manage.php\">".htmlspecialchars($val['kname'])."</a>] ";
			}
		}

		$s.='ll('.$rs['kid'].',\''.$kname.'\',\''.($rs['kpath']).'\',\''.$subs.'\',\''.number_format($rs['ndbver']/100,2).'\','.$rs['islock'].','.$rs['nshow'].',0);';
	}
	//如果有module参数,则安装对应的模块!
	if(!empty($_GET['module'])){
		$s.="$.kc_ajax('{CMD:\'module_add\',modulepath:\'{$_GET['module']}\'}')";
	}
	$s.=$king->closeList();

	$left=array(
		array(
			'class'=>'sel',
			'ico'=>'k7',
			'title'=>$king->lang->get('system/module/list'),
		),
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'module_add\',METHOD:\'GET\'}',
			'ico'=>'l7',
			'title'=>$king->lang->get('system/module/add'),
		),
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'findmodule\'}',
			'ico'=>'m1',
			'title'=>$king->lang->get('system/module/find'),
		),
	);

	$king->skin->output($king->lang->get('system/title/module'),$left,'',$s);

}

function king_conn(){
	global $king;
	$king->access('#conn');

	$_cmd=array(
		'delete_conn'=>$king->lang->get('system/common/del'),
	);
	$manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'conn_edt\',kid:'+K[0]+',METHOD:\'GET\'}\">'+\$.kc_icon('r8','".addslashes($king->lang->get('system/common/edit'))."')+'</a>'";
	$manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete_conn\',list:'+K[0]+'}\">'+\$.kc_icon('r7','".addslashes($king->lang->get('system/common/del'))."')+'</a>'";
	$manage.="+\$.kc_updown(K[0],'updown_conn')";

	$_js=array(
		"\$.kc_list(K[0],K[1],'{CMD:\'conn_edt\',kid:'+K[0]+',METHOD:\'GET\'}')",
		$manage,
		"K[2]",
	);

	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=conn&pid=PID&rn=RN',$king->db->getRows_number('%s_conn')));

	$_sql="select kid,kname,urlpath from %s_conn order by norder desc,kid desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$s.="ll('".$king->lang->get('system/conn/name')."','manage','".$king->lang->get('system/conn/urlpath')."',1);";

	foreach($res as $rs){
		$s.="ll({$rs['kid']},'".addslashes($rs['kname'])."','".addslashes($rs['urlpath'])."',0);";
	}


	$s.=$king->closeList();

	$left=array(
		''=>array(
			'rel'=>'manage.php?action=conn',
			'class'=>'sel',
			'ico'=>'r5',
			'title'=>$king->lang->get('system/common/list'),
		),
		'add'=>array(
			'rel'=>'{CMD:\'conn_edt\',METHOD:\'GET\'}',
			'class'=>'k_ajax',
			'ico'=>'r6',
			'title'=>$king->lang->get('system/common/add'),
		),
	);

	$king->skin->output($king->lang->get('system/title/conn'),$left,'',$s);

}

function king_lnk(){
	global $king;
	$king->access('#lnk');

	$adminid=isset($_GET['adminid']) ? kc_get('adminid',2,1) : $king->admin['adminid'];

	if($adminid!=$king->admin['adminid']) $king->access('admin');

	$_sql="SELECT kid,kname,kimage,kpath,konclick,isblank FROM %s_lnk where adminid={$adminid} order by norder desc";
	if(!$res=$king->db->getRows($_sql,1))
		$res=array();

	$_cmd=array(
		'delete_lnk'=>$king->lang->get('system/common/del'),
	);
	$_manage="'<a href=\"manage.php?action=lnkedt&kid='+K[0]+'\">'+$.kc_icon('g8','".$king->lang->get('system/common/edit')."')+'</a>'";
	$_manage.="+'<a class=\"k_ajax\" rel=\"{CMD:\'delete_lnk\',adminid:$adminid,list:'+K[0]+'}\">'+$.kc_icon('i8','".$king->lang->get('system/common/del')."')+'</a>'";
//	$_manage.="+$.kc_updown(K[0],'manage.php','adminid=$adminid&obj=lnk')";
	$_manage.="+$.kc_updown(K[0],'updown_lnk')";
	//"+'<a class=\"k_updown\" rel=\"{kid:'+K[0]+',CMD:\'updown_lnk\'}\" title=\"".$king->lang->get('system/common/updown')."\">'+$.kc_icon('n5')+'</a>'";


	$_js=array(
		"$.kc_list(K[0],K[1],'manage.php?action=lnkedt&kid='+K[0])",//'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+K[1]+'</a>'
		$_manage,
		"'<a href=\"'+K[3]+'\" onclick=\"'+K[4]+'\" '+istarget(K[5])+'><img style=\"width:32px;height:32px;\" src=\"images/lnk/'+K[2]+'\"/></a>'",//'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+K[1]+'</a>'
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist("manage.php?action=lnk&adminid={$adminid}&pid=PID&rn=RN",$king->db->getRows_number('%s_lnk',"adminid={$adminid}")),array('adminid'=>$adminid));//"select count(*) from %s_lnk where adminid={$adminid};"//"adminid=$adminid"
	$s.='function istarget(is){var I1;is?I1=\'target="_blank"\':I1=\'\';return I1;};';

	$s.='ll(\''.$king->lang->get('system/lnk/kname').'\',\'manage\',\''.$king->lang->get('system/lnk/kimage').'\',1);';//th

	foreach($res as $rs){//td
		$s.='ll('.$rs['kid'].',\''.addslashes(htmlspecialchars($rs['kname'])).'\',\''.$rs['kimage'].'\',\''.addslashes(htmlspecialchars($rs['kpath'])).'\',\''.addslashes(htmlspecialchars($rs['konclick'])).'\','.$rs['isblank'].',0);';
	}
	$s.=$king->closeList();

	$king->skin->output($king->lang->get('system/title/lnk'),king_inc_lnk_left(),'',$s);
}
function king_lnkedt(){
	global $king;
	$king->access('#lnk');
	$kid=kc_get('kid',2);
	$adminid=kc_get('adminid',2)?kc_get('adminid',2,1):$king->admin['adminid'];

	//当提交过来的adminid和当前管理员id不同的时候，验证管理员的级别
	if($adminid!=$king->admin['adminid']) $king->access('admin');

	$sql='kname,ktitle,kpath,adminid,kimage,isblank,konclick,isflo';
	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['kimage']='lnk.gif';
			$data['adminid']=$adminid;
		}
	}else{
		if(!$data=$king->db->getRows_one("select {$sql} from %s_lnk where kid={$kid} limit 1;"))
			kc_error($king->lang->get('system/error/param').'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	$fields=explode(',',$sql);
	$data=kc_data($fields,$data);

	$s=$king->openForm('manage.php?action=lnkedt');
	//kname
	$_array=array(
		array('kname',0,1,20),
	);
	$s.=$king->htmForm($king->lang->get('system/lnk/kname').' (1-20)','<input class="k_in w200" type="text" name="kname" value="'.htmlspecialchars($data['kname']).'" maxlength="20" />',$_array);
	//ktitle
	$_array=array(
		array('ktitle',0,0,100),
	);
	$s.=$king->htmForm($king->lang->get('system/lnk/ktitle').' (0-100)','<input class="k_in w400" type="text" name="ktitle" value="'.htmlspecialchars($data['ktitle']).'" maxlength="100" />',$_array);
	//kpath
	$_array=array(
		array('kpath',0,1,100),
	);
	$s.=$king->htmForm($king->lang->get('system/lnk/kpath').' (1-100)','<input class="k_in w400" type="text" name="kpath" value="'.htmlspecialchars($data['kpath']).'" maxlength="100" />',$_array);
	//konclick
	$_array=array(
		array('konclick',0,0,255),
	);
	$s.=$king->htmForm($king->lang->get('system/lnk/konclick').' (0-255)','<input class="k_in w400" type="text" name="konclick" value="'.htmlspecialchars($data['konclick']).'" maxlength="255" />',$_array);
	//isblank&isflo
	$array_blank=array(1=>$king->lang->get('system/lnk/blank'));
	$array_flo=array(1=>$king->lang->get('system/lnk/flo'));
	$s.=$king->htmForm($king->lang->get('system/lnk/attrib'),kc_htm_checkbox('isblank',$array_blank,$data['isblank']).kc_htm_checkbox('isflo',$array_flo,$data['isflo']));
	//kimage
	$_array=array(
		array('kimage',0,1,100),
	);
	$array=kc_f_getdir('system/images/lnk','gif|png|jpg');
	$image='<div id="lnksel"><p>';
	$i=1;
	foreach($array as $val){
		$image.='<a href="javascript:;" onclick="$(\'#kimage\').val(\''.$val.'\');lnksel();"><img src="images/lnk/'.$val.'"/></a>';
		if($i++==6){
			$image.='</p><p>';
			$i=1;
		}
	}
	$image.='</p></div>';
	$s.=$king->htmForm($king->lang->get('system/lnk/image'),'<table class="k_side" cellspacing="0"><tr><td><img src="images/lnk/'.htmlspecialchars($data['kimage']).'" id="klnkimage"/></td><td>'.$image.'</td></tr></table>',$_array,null,kc_help('system/help/lnkimg'));

	$s.=kc_htm_hidden(array('adminid'=>$data['adminid'],'kimage'=>htmlspecialchars($data['kimage']),'kid'=>$kid));

	$s.='<script>function lnksel(){var kimage=$(\'#kimage\').val();$(\'#klnkimage\').attr(\'src\',\'images/lnk/\'+kimage);}</script>';

	$s.=$king->closeForm('save');

	if($GLOBALS['ischeck']){

//	$sql='kname,ktitle,kpath,adminid,kimage,isblank,konclick,isflo';

		$array=array();
		foreach($fields as $val){
			$array[$val]=$data[$val];
		}

		$_array=array('isblank','isflo');
		foreach($_array as $val)
			$array[$val]= $data[$val] ? 1 : 0;

		if($kid){//update
			$king->db->update('%s_lnk',$array,"kid=$kid");
			$nlog=7;
		}else{//insert
			$array['norder']=$king->db->neworder('%s_lnk',"adminid={$data['adminid']}");
			$nlog=5;
			$king->db->insert('%s_lnk',$array);
		}

		$king->cache->del('system/lnk/'.$data['adminid']);
		$king->cache->del('system/lnk/flo_'.$data['adminid']);

		//写log
		$king->log($nlog,$data['kname']);

		kc_goto($king->lang->get('system/goto/is'),'manage.php?action=lnkedt&adminid='.$data['adminid'],'manage.php?action=lnk&adminid='.$data['adminid']);
	}

	$king->skin->output($king->lang->get('system/title/lnk'),king_inc_lnk_left(),'',$s);

}
function king_bot(){
	global $king;
	$king->access('#bot');


	$_sql="SELECT kid,kname,ncount,nlastdate,ndate FROM %s_bot order by kname,nlastdate desc,ncount desc";
	$res=$king->db->getRows($_sql,1);

	$_cmd=array(
		'delete_bot'=>$king->lang->get('system/common/del'),
	);

	$_manage="'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'botedt\',METHOD:\'GET\',kid:'+K[0]+'}\">'+$.kc_icon('g8','".$king->lang->get('system/common/edit')."')+'</a>'";//ajax方式进行管理
	$_manage.="+'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete_bot\',list:'+K[0]+'}\">'+$.kc_icon('i8','".$king->lang->get('system/common/del')."')+'</a>'";

	$_js=array(
		"$.kc_list(K[0],K[1])",
		$_manage,
		"'<i>'+K[2]+'</i>'",
		"K[3]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist("manage.php?action=bot&pid=PID&rn=RN",$king->db->getRows_number('%s_bot')));
	$s.=$king->tdList(array($king->lang->get('system/bot/kname'),'manage','<i>'.$king->lang->get('system/bot/ncount').'</i>',$king->lang->get('system/bot/nlastdate')),1);

	foreach($res as $rs){//td
		$s.=$king->tdList(array(
			$rs['kid'],
			addslashes(htmlspecialchars($rs['kname'])),
			$rs['ncount'],
			kc_formatdate($rs['nlastdate']),
			$rs['ndate'],
		));
	}
	$s.=$king->closeList();

	$left=array(
		array(
			'href'=>'manage.php?action=lnk',
			'class'=>'sel',
			'ico'=>'e8',
			'title'=>$king->lang->get('system/common/log'),
		),
		array(
			'class'=>'k_ajax',
			'rel'=>'{CMD:\'botedt\',METHOD:\'GET\'}',
			'ico'=>'h8',
			'title'=>$king->lang->get('system/common/add'),
		),
	);

	$king->skin->output($king->lang->get('system/title/bot'),$left,'',$s);
}

//king_log
function king_event(){
	global $king;

	$king->access('#event');
	$s='';

	$_sql='select kid,kfile,nline,ntype,kmsg,kurl,ndate from %s_event order by kid desc';

	if(!$_res=$king->db->getRows($_sql,1))
		$_res=array();


	//准备开始列表
	$_cmd=array(
		'delete_event'=>$king->lang->get('system/common/del'),
		'clear_event'=>$king->lang->get('system/common/clear'),
	);
/*
id      ID
tit     标题
link    链接
is      是否显示id
isgray  是否灰度
ico     图标
space   缩进
listico 列表页专用的前置ico
		"$.kc_list(K[0],K[1],'../'+K[2]+'/manage.php',0,1,islock(K[5]))",//'<a href=\"manage.php?action=admin_edt&adminid='+K[0]+'\">'+K[1]+'</a>'
*/


	$_js=array(
		"$.kc_list(K[0],K[1]+'- Line: <strong>'+K[2]+'</strong>',0,0,1,'g9')",
		"'<i>'+K[3]+'</i>'",
		"'<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'view_event\',kid:'+K[0]+'}\">'+K[4]+'</a>'",
		"K[6]",
		"K[5]",
	);
	$s=$king->openList($_cmd,'',$_js,$king->db->pagelist('manage.php?action=event&pid=PID&rn=RN',$king->db->getRows_number('%s_event')));//'select count(*) from %s_event;'

	$s.='ll(\''.$king->lang->get('system/event/file').'\',\'<i>'.$king->lang->get('system/event/type').'</i>\',\''.$king->lang->get('system/event/msg').'\',\''.$king->lang->get('system/event/url').'\',\''.$king->lang->get('system/common/date').'\',1);';//th

	foreach($_res as $_rs){	 //td
		$s.='ll('.$_rs['kid'].',\''.addslashes($_rs['kfile']).'\','.($_rs['nline']).','.$_rs['ntype'].',\''.addslashes(kc_short($_rs['kmsg'],70,20)).'\',\''.kc_formatdate($_rs['ndate']).'\',\''.$_rs['kurl'].'\',0);';
	}

	//结束列表
	$s.=$king->closeList();

	$king->skin->output($king->lang->get('system/title/event'),'','',$s);

} //!king_log




?>
