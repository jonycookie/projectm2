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
    <h3>文件上传</h3>
  </div>
  <table class="tb tb2 " width="100%">
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" enctype="multipart/form-data">
        <td style="width:60px;">请选择文件</td>
        <td><input name="file" type="file" class="uploadbtn" id="pic" /><input name="action" type="hidden" value="Aupload" /><input name="in" type="hidden" value="<?=$in?>" /> <input type="submit" value="上传" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
  </table>
</div>
</body>