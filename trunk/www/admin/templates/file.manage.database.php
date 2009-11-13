<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><link rel="stylesheet" href="admin/images/jquery.function.css" type="text/css" media="all" />
<script type="text/javascript" src="javascript/jquery.function.js"></script>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;文件管理&nbsp;&raquo;&nbsp;<select name="filemethod" onchange="parent.main.location.href=\'<?=__SELF__?>?do=file&operation=manage&method=\'+this.value"><option value="database"<?php if($method=="database") echo ' selected="selected"'?>>数据库模式</option></select>','');</script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>文件管理</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage"<?php if(empty($_GET['type'])) echo ' class="current"'?>><a href="<?=__SELF__?>?do=file&operation=manage&method=database"><span>所有文件</span></a></li>
      <li id="nav_image"<?php if($_GET['type']=='image') echo ' class="current"'?>><a href="<?=__SELF__?>?do=file&operation=manage&type=image&method=database"><span>图片文件</span></a></li>
      <li id="nav_other"<?php if($_GET['type']=='other') echo ' class="current"'?>><a href="<?=__SELF__?>?do=file&operation=manage&type=other&method=database"><span>其它文件</span></a></li>
    </ul>
  </div>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li><?=GetFileSize($totalSize)?></li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=file&operation=post" method="post">
    <table class="tb tb2 ">
      <tr>
        <th>删</th>
        <th></th>
        <th>文件名</th>
        <th>内容ID</th>
        <th>文件大小</th>
        <th>上传时间</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
    		$rs[$i]['time']=get_date($rs[$i]['time'],'Y-m-d H:i');;
			$rs[$i]['size']=GetFileSize($rs[$i]['size']);
			$rs[$i]['icon']=geticon($rs[$i]['filename']);
    ?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['id']?>" /></td>
        <td><?=$total-($i+$firstcount)?></td>
        <td><a href="<?=$rs[$i]['path']?>" class="viewpic" target="_blank"><?=$rs[$i]['icon']?></a> <a href="JavaScript:void(0)" title="文件名：<?=$rs[$i]['ofilename']?>"><?=$rs[$i]['filename']?></a></td>
        <td><?php if($rs[$i]['aid']){ ?><a href="<?=__SELF__?>?do=file&operation=manage&aid=<?=$rs[$i]['aid']?>&method=database"> <?=$rs[$i]['aid']?> </a> <?php }else{ ?> 无 <?php }?></td>
        <td><?=$rs[$i]['size']?></td>
        <td><?=$rs[$i]['time']?></td>
        <td><?php if($rs[$i]['type']=='remote'){?><a href="<?=__SELF__?>?do=file&operation=reremote&path=<?=urlencode($rs[$i]['path'])?>&url=<?=urlencode($rs[$i]['ofilename'])?>">重新下载</a> | <?php }?><a href="<?=__SELF__?>?do=file&operation=reupload&fid=<?=$rs[$i]['id']?>">重新上传</a> | <a href="<?=__SELF__?>?do=file&operation=delete&fid=<?=$rs[$i]['id']?>">删除</a></td>
      </tr>
      <?php }?>
    <tr>
      <td height="22" colspan="8" align="right"><?=$pagenav?></td>
    </tr>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
          <label for="chkall">全选</label></td>
        <td colspan="7"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
<script language="JavaScript" type="text/javascript">
$(function(){
	var timeOutID = null;
	var hideSnap = function(){$("#bigsnap").hide().css({"top" : "-400px", "left" : "-400px"});};
	$(".viewpic").mouseover(function(){
		window.clearTimeout(timeOutID);
		if(!in_array(this.href.substr(this.href.lastIndexOf(".")+1), ['gif', 'jpeg', 'jpg', 'png', 'bmp'])||this.href == "admin/images/ajax_loader.gif"){
			return;
		}
		var offset =$(this).offset();
		var snapTop = offset.top+10;
		var snapLeft = offset.left+10;
		$("#bigsnap").css({"top" : snapTop, "left" : snapLeft}).show();
		$("#prewimg").attr("src", this.href).scaling(400,400);
	}).mouseout(function(){
		timeOutID = window.setTimeout(hideSnap, 1000);
	});
	$("#bigsnap").mouseover(function(){
		window.clearTimeout(timeOutID);
		$(this).show();
	}).mouseout(function(){
		$(this).hide();
	});
});
</script>
<style type="text/css">
<!--
img.snap						{border:1px solid #DFDFDF;padding:2px;background:#FFFFFF;}
.bigsnap1						{position:absolute;z-index:10;top:-400px;left:-400px;display:none;border:0;}
.bigsnap1 div.border3			{position:absolute;z-index:2;top:0px;left:0px;}
.bigsnap1 div.view				{margin-left:9px;border:1px solid #cccccc;width:1px;height:1px;background:#FFFFFF;padding:2px;}
.bigsnap1 div.shadow			{position:absolute;z-index:1;top:3px;left:11px;width:1px;height:1px;background:#B8B8B8;filter:alpha(opacity=30);-moz-opacity:0.30;opacity:0.30;}
-->
</style>
<div id="bigsnap" class="bigsnap1">
  <div class="border3">
    <div class="view"><img class="snap2" id="prewimg" src="admin/images/ajax_loader.gif" /></div>
  </div>
  <div class="shadow"></div>
</div>
</body></html>