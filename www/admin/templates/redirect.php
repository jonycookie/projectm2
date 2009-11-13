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
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<link href="admin/images/style.css" rel="stylesheet" type="text/css" />
<title>系统提示—<?php echo $msg;?> </title>
</head>
<div class="container" id="cpcontainer">
  <div class="infobox">
    <h4 class="infotitle1"><?php echo $msg;?></h4>
    <img src="admin/images/loading.gif" class="marginbot" />
    <p class="marginbot"><a href="<?php echo $url;?>" class="lightlink">如果您的浏览器没有自动跳转，请点击这里</a></p>
    <script type="text/JavaScript">setTimeout("window.location.replace('<?php echo $url;?>');", <?php echo $t;?>*1000);</script>
<?php if($more){?>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis" style="text-align:left;">
<?php foreach($more as $v){?>
          <li><a href="<?=$v['url']?>" class="lightlink" <?=$v['o']?>><?=$v['text']?></a></li>
<?php }?>
        </ul></td>
    </tr>
  </table>
<?php }?>
  </div>
</div>
</BODY>
</HTML>
<?php exit();?>