<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
?>
<h2>Welcome</h2>
<?php include "shortcut-button.php";
/*if(empty($do)){?>
<div class="content-box column-left">
  <div class="content-box-header">
    <h3>网站公告</h3>
  </div>
  <div class="content-box-content">
    <div class="tab-content default-tab">
      <h4>Maecenas dignissim</h4>
      <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in porta lectus. Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies. Maecenas lacinia orci at neque commodo commodo. </p>
    </div>
  </div>
</div>
<div class="content-box column-right">
  <div class="content-box-header">
    <h3>统计</h3>
  </div>
  <div class="content-box-content">
    <div class="tab-content default-tab">
      <h4>This box is closed by default</h4>
      <p> Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed in porta lectus. Maecenas dignissim enim quis ipsum mattis aliquet. Maecenas id velit et elit gravida bibendum. Duis nec rutrum lorem. Donec egestas metus a risus euismod ultricies. Maecenas lacinia orci at neque commodo commodo. </p>
    </div>
  </div>
</div>
<div class="clear"></div>
<?php }*/?>
<div class="content-box">
  <div class="content-box-header">
    <h3>文章管理</h3>
  </div>
  <div class="content-box-content">
    <div class="tab-content default-tab" id="tab1"> <!-- This is the target div. id must match the href of this div's tab -->
      <div class="notification attention png_bg" style="display:none;"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
        <div> This is a Content Box. You can put whatever you want in it. By the way, you can close this notification with the top-right cross. </div>
      </div>
      <table border="0" cellpadding="0" cellspacing="0">
	<form action="<?=__SELF__?>?do=article&operation=post" method="post">
        <thead>
          <tr>
            <th>选择</th>
            <th style="width:40%">标题</th>
            <th>栏目</th>
            <th>发布时间</th>
            <th>点击/评论</th>
            <th>管理</th>
          </tr>
        </thead>
        <tfoot>
          <tr>
            <td colspan="6"><input class="check-all" type="checkbox" />全选 <select name="action">
                <option value="">====选择====</option>
                <option value="del">删除</option>
              </select>
              		<input class="button" type="submit" value="确定" />
              </td>
          </tr>
          <tr>
            <td colspan="6" class="pagination"><?=$pagenav?></td>
          </tr>
        </tfoot>
        <tbody>
          <?php for($i=0;$i<$_count;$i++){
      	$C=$catalog->catalog[$rs[$i]['cid']];
      	$iurlArray=array('id'=>$rs[$i]['id'],'cid'=>$rs[$i]['cid'],'link'=>$rs[$i]['customlink'],'url'=>$rs[$i]['url'],'dir'=>$iCMS->cdir($C),'domain'=>$C['domain'],'pubdate'=>$rs[$i]['pubdate']);
		$rs[$i]['url']=$iCMS->iurl('show',$iurlArray);
	?>
          <tr>
            <td><input type="checkbox" name="id[]" value="<?=$rs[$i]['id']?>"/></td>
            <td><?php if($rs[$i]['visible']=="0"){echo '[审核中]'.$rs[$i]['title'];}else{?><a href="<?=$rs[$i]['url']?>" target="_blank"><?=$rs[$i]['title']?></a><?php }?></td>
            <td><a href="<?=__SELF__?>?do=article&operation=manage&cid=<?=$rs[$i]['cid']?><?=$uri?>"><?=$C['name']?></a></td>
            <td><?=get_date($rs[$i]['pubdate'],'Y-m-d H:i');?></td>
            <td><?=$rs[$i]['hits']?> / <?php if($rs[$i]['visible']=="0"){echo '0';}else{?><a href="<?=$iCMS->dir?>comment.php?aid=<?=$rs[$i]['id']?>" target="_blank"><?=$rs[$i]['comments']?></a><?php }?></td>
            <td><!-- Icons --> <a href="<?=__SELF__?>?do=article&operation=add&id=<?=$rs[$i]['id']?>" title="编辑"><img src="usercp/style/pencil.png" alt="编辑" /></a> <a href="<?=__SELF__?>?do=article&operation=del&id=<?=$rs[$i]['id']?>" onclick="return confirm('确定要删除<?=HTML2JS($rs[$i]['title'])?>');" title="删除"><img src="usercp/style/cross.png" alt="删除" /></a> </td>
          </tr>
          <?php }?>
        </tbody>
        </form>
      </table>
    </div>
  </div>
</div>
<?php /*
<div class="notification attention png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Attention notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification information png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Information notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification success png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Success notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
<div class="notification error png_bg"> <a href="#" class="close"><img src="usercp/style/cross_grey_small.png" title="Close this notification" alt="close" /></a>
  <div> Error notification. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin vulputate, sapien quis fermentum luctus, libero. </div>
</div>
*/?>