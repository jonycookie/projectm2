<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
function iCMS_message($vars,&$iCMS){
	if(isset($vars['call'])){
		if($vars['call']=='form'){
			$width=$vars['width']?$vars['width']:'98%';
			$height=$vars['height']?$vars['height']:'200';
			include_once(iPATH."include/fckeditor.php");
			$editor = new FCKeditor('messagetext') ;
			$editor->BasePath	= $iCMS->config['url'];
			$editor->Width	= $width ;
			$editor->Height	= $height;
			$editor->ToolbarSet = 'Basic';
			$iCMS->assign('editor',$editor->CreateHtml());
			echo $iCMS->iPrint("iSYSTEM","message.form");
		}
	}else{
		$maxperpage =isset($vars['row'])?(int)$vars['row']:"20";
		$cacheTime =isset($vars['time'])?(int)$vars['time']:-1;
		$offset	= 0;
		if($vars['page']){
			$total=$iCMS->db->getValue("SELECT count(*) FROM `#iCMS@__message` WHERE `secret`='off' order by `id` DESC");
			$pagenav= isset($vars['pagenav'])?$vars['pagenav']:"pagenav";
			$pnstyle= isset($vars['pnstyle'])?$vars['pnstyle']:0;
			$offset	= $iCMS->multi(array('total'=>$total,'perpage'=>$maxperpage,'unit'=>$iCMS->language('page:message'),'url'=>$iCMS->url,'nowindex'=>$GLOBALS['page'],'pagenav'=>$pagenav,'pnstyle'=>$pnstyle));
		}
		if($vars['cache']==false||isset($vars['page'])){
			$iCMS->config['iscache']=false;
			$rs = '';
		}else{
			$iCMS->config['iscache']=true;
			$cacheName='message/cache';
			$rs=$iCMS->cache($cacheName);
		}
		if(empty($rs)){
			$rs=$iCMS->db->getArray("SELECT * FROM `#iCMS@__message` WHERE `secret`='off' order by `id` DESC LIMIT {$offset},{$maxperpage}");
			for ($i=0;$i<count($rs);$i++){
				$rs[$i]['user']=unserialize($rs[$i]['user']);
				if($rs[$i]['reply']){
					$reply=explode('||',$rs[$i]['reply']);
					$reply[0]=='admin'&&$rs[$i]['reply']='<strong>'.$iCMS->language('reply:admin').'</strong>'.$reply[1];
				}
			}
			$iCMS->cache(false)->addcache($cacheName,$rs,$cacheTime);
		}
		return $rs;
	}
}

?>