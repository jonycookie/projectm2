<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<link rel="stylesheet" href="<?=$iCMS->dir?>admin/images/style.css" type="text/css" media="all" />
<script src="javascript/jquery.js" type="text/javascript"></script>
<script src="javascript/admin.fun.js" type="text/javascript"></script>
<script language="javascript">
window.focus();
</script>
</head>
<body>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>文件管理</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage"<?php if(empty($_GET['type'])) echo ' class="current"'?>><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&in=<?=$in?>"><span>所有文件</span></a></li>
      <li id="nav_image"<?php if($_GET['type']=='image') echo ' class="current"'?>><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&in=<?=$in?>&type=image"><span>图片文件</span></a></li>
      <li id="nav_other"<?php if($_GET['type']=='other') echo ' class="current"'?>><a href="<?=__SELF__?>?do=dialog&operation=<?=$operation?>&in=<?=$in?>&type=other"><span>其它文件</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 " width="100%">
    <tr>
      <th></th>
      <th>文件名</th>
      <th>文件大小</th>
      <th>上传时间</th>
    </tr>
    <?php for($i=0;$i<$_count;$i++){
    		$rs[$i]['time']=get_date($rs[$i]['time'],"Y-m-d H:i");
			$rs[$i]['size']=GetFileSize($rs[$i]['size']);
			$rs[$i]['icon']=geticon($rs[$i]['filename']);
    ?>
    <tr>
      <td><?=$total-($i+$firstcount)?></td>
      <td><?=$rs[$i]['icon']?> <?=$rs[$i]['filename']?></td>
      <td><?=$rs[$i]['size']?></td>
      <td><?=$rs[$i]['time']?></td>
    </tr>
    <?php }?>
    <tr>
      <td height="22" colspan="4" align="right"><?=$pagenav?></td>
    </tr>
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" enctype="multipart/form-data" name="uploadfile" target="post" id="uploadfile">
        <td>上　传：</td>
        <td colspan="3" class="vtop rowform"><input name="file" type="file" class="uploadbtn" id="pic" /><input name="action" type="hidden" value="uploadfile" /> <input type="submit" value="上传" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" name="createdir" target="post" id="createdir">
        <td>新目录：</td>
        <td colspan="3" class="vtop rowform"><input type='text' name='dirname' value='' style='width:150px'><input name="action" type="hidden" value="createdir" /> <input type="submit" value="创建" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
  </table>
</div>
<iframe width="100%" height="100" style="display:" id="post" name="post"></iframe>
</body>
</html>