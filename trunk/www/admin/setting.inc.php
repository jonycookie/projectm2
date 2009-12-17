<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
if($operation=="post"){
	if($action=='save'){
	//	$_POST['rewrite']=addslashes(serialize($_POST['rewrite']));
		foreach($_POST AS $key =>$value){
			if($key=='rewrite'){
				$_POST['customlink']=='2' && $value['split']='/';
				($_POST['customlink']=='custom' && empty($value['dir'])) && $value['dir']='.php?';
				!trim($value['split']) && $value['split']='-';
				(!trim($value['ext'])||$value['ext']=='=') && $value['ext']='.html';
				updateConfig(addslashes(serialize($value)),'rewrite');
			}elseif($key=='bbs'){
				updateConfig(addslashes(serialize($value)),'bbs');
			}elseif($key!='action'){
				updateConfig(dhtmlspecialchars($value),$key);
			}
		}
		if($_POST['ishtm']){
			updateConfig('id','linkmode');
			updateConfig('0','customlink');
		}
		updateConfig('','collect');
		CreateConfigFile();
		if($_POST['ishtm']=="0" && ($_POST['customlink']=='custom'||$_POST['customlink']=='2') && $_POST['rewrite']['dir']!='.php?'){
			$preg_quote_split=preg_quote($_POST['rewrite']['split'],'/');
			$htaccess="RewriteEngine On\n";
			$htaccess.="RewriteBase {$iCMS->dir}\n";
			$htaccess.="# 首页\n";
			$htaccess.="RewriteRule ^index".preg_quote($_POST['rewrite']['ext'],'/')."$ index.php\n";
			$htaccess.="# 独立页面、栏目、文章、评论、搜索、留言、标签\n";
			if($_POST['customlink']=='2' && empty($_POST['rewrite']['dir'])){
				if($_POST['linkmode']=='id'){
					$htaccess.="RewriteRule ^(list|show){$preg_quote_split}(.*)$ $1.php?id{$preg_quote_split}$2\n";
				}elseif($_POST['linkmode']=='title'){
					$htaccess.="RewriteRule ^(list|show){$preg_quote_split}(.*)$ $1.php?t{$preg_quote_split}$2\n";
				}
				$htaccess.="RewriteRule ^index{$preg_quote_split}page{$preg_quote_split}(.*)".preg_quote($_POST['rewrite']['ext'],'/')."$ index.php?page{$preg_quote_split}$1".preg_quote($_POST['rewrite']['ext'],'/')."\n";
				$htaccess.="RewriteRule ^index{$preg_quote_split}(.*)".preg_quote($_POST['rewrite']['ext'],'/')."$ index.php?p{$preg_quote_split}$1".preg_quote($_POST['rewrite']['ext'],'/')."\n";
				$htaccess.="RewriteRule ^comment{$preg_quote_split}(.*)$ comment.php?aid{$preg_quote_split}$1\n";
				$htaccess.="RewriteRule ^tag{$preg_quote_split}(.*)$ tag.php?t{$preg_quote_split}$1\n";
				$htaccess.="RewriteRule ^search{$preg_quote_split}(.*)$ search.php?keyword{$preg_quote_split}$1\n";
			}else{
				$htaccess.="RewriteRule ^(index|list|show|comment|search|message|tag)".preg_quote($_POST['rewrite']['dir'],'/')."(.*)$ $1.php?$2\n";
			}
			writefile(iPATH.'.htaccess',$htaccess);
		}
		if($_POST['ishtm']=="1"){
			delfile(iPATH.'.htaccess');
//			delfile(iPATH.$config['indexname'].'.html');
		}
		$iCMS->clear_compiled_tpl();
		redirect('配置已更新',__REF__);
	}
	exit;
}
include iCMS_admincp_tpl("setting");
?>