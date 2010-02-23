<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
echo '<html xmlns="http://www.w3.org/1999/xhtml">';
echo '<head><title></title>';
echo '<meta http-equiv="Content-Type" content="text/html; charset='.iCMS_CHARSET.'">';
echo '<link rel="stylesheet" href="admin/images/style.css?v=3.1.2" type="text/css" media="all" />';
echo '<script src="javascript/jquery.js?v=3.1.2" type="text/javascript"></script>';
echo '<script src="javascript/admin.fun.js?v=3.1.2" type="text/javascript"></script>';
echo '<script type="text/JavaScript">';
echo "	if('' != 'no' && !parent.document.getElementById('leftmenu')) redirect(document.URL + (document.URL.indexOf('?') != -1 ? '&' : '?') + 'frames=yes');";
echo '</script>';
echo '</head>';
echo '<body>';
?>