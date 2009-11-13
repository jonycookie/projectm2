<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
switch ($operation) {
	case 'manage':
		$Admin->MP("menu_template_manage");
		$dir=trim($_GET["dir"]);
		$L=GetFolderList($dir,"templates","");
		include	iCMS_admincp_tpl('template.manage');
	break;
	case 'edit':
		$path=trim($_GET["path"]);
		$FileData=openfile(iPATH."templates".$path);
		include	iCMS_admincp_tpl('template.edit');
	break;
	case 'clear':
		$path=trim($_GET["path"]);
		$iCMS->clear_compiled_tpl($path);
		redirect('清除完成',__REF__);
	break;
	case 'post':
		if($action=='edit'){
			strpos($_POST['tplpath'],'..')!==false && alert("文件路径不能带有..");
			preg_match("/\.([a-zA-Z0-9]{2,4})$/",$_POST['tplpath'],$exts);
			$FileExt=strtolower($exts[1]);
			strstr($FileExt, 'ph') && alert("文件格式错误！");
			in_array($FileExt,array('cer','htr','cdx','asa','asp','jsp','aspx','cgi'))&& alert("文件格式错误！");
			$FileData=stripslashes($_POST['html']);
			writefile(iPATH.'templates'.$_POST['tplpath'],$FileData);
			redirect('保存成功',__REF__);
		}
	break;
}
?>
