<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?><link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;评论管理','');</script>
<div class="container" id="cpcontainer">
  <h3>评论管理</h3><form action="<?=__SELF__?>?do=comment&operation=post" method="post">
  <table class="tb tb2 ">
    <?php for($i=0;$i<$_count;$i++){
		if($rs[$i]['reply']){
			$reply=explode('||',$rs[$i]['reply']);
			$rs[$i]['reply']=$reply[0]=='admin'?'<strong>管理员回复：</strong>'.$reply[1]:'<strong>文章发布者回复：</strong>'.$reply[1];
		}
	?>
    <tr>
      <th><input type="checkbox" class="checkbox" name="id[]" value="<?=$rs[$i]['id']?>" /><input name="aid[<?=$rs[$i]['id']?>]" type="hidden" value="<?=$rs[$i]['aid']?>"/></th>
      <th>文章：</th>
      <th width="343"><?=$rs[$i]['atitle']?></th>
      <th colspan="2">支持(<?=$rs[$i]['up']?>) 反对(<?=$rs[$i]['against']?>)</th>
      </tr>
    <tr>
      <th width="64">&nbsp;</th>
      <th width="70">评论者：</th>
      <th><?php if ($rs[$i]['uid']!='0'){ ?><a href="user.php?do=edit&amp;id=<?=$rs[$i]['uid']?>"><?=$rs[$i]['username']?></a><?php }else{ echo $rs[$i]['username'];}?> [<?=$rs[$i]['ip']?>]</th>
      <th width="47">时间：</th>
      <th width="390"><?=get_date($rs[$i]['addtime'],'Y-m-d H:i:s');?></th>
      </tr>
    <tr>
      <td>评论：</td>
      <td colspan="4"><?=ubb($rs[$i]['contents'])?>
        <?php if($rs[$i]['reply']){?>
        <blockquote style="background-color:#F7F7F7;border:#E5E5E5 solid 1px; padding:4px;">
          <?=$rs[$i]['reply']?>
        </blockquote>
      <?php }?><blockquote id="reply<?=$rs[$i]['id']?>" style="display:none;background-color:#F7F7F7;border:#E5E5E5 solid 1px; padding:4px;width:405px">回复<?=$rs[$i]['username']?>的评论：<br />
        <textarea id="reply_textarea_<?=$rs[$i]['id']?>" rows="6" onkeyup="textareasize(this)" name="reply" cols="50" class="tarea"><?=$reply[1]?></textarea>
        <div class="fixsel"><input type="button" class="btn" value="回复"  onclick="_reply(<?=$rs[$i]['id']?>);"/> <input type="button" class="btn" value="取消" onclick="$('#reply<?=$rs[$i]['id']?>').hide();"/></div>
      </blockquote></td>
    </tr>
    <tr>
      <td>管理：</td>
      <td colspan="4"><a href="javascript:void(0);" onclick="$('#reply<?=$rs[$i]['id']?>').toggle();">回复</a> |
      <?php if ($rs[$i]['isexamine']=='1'){ ?>
      <a href="<?=__SELF__?>?do=comment&operation=cancelexamine&id=<?=$rs[$i]['id']?>&aid=<?=$rs[$i]['aid']?>">取消审核</a>
      <?php }else{  ?>
      <a href="<?=__SELF__?>?do=comment&operation=examine&id=<?=$rs[$i]['id']?>&aid=<?=$rs[$i]['aid']?>">通过审核</a>
      <?php }?>| <a href="<?=__SELF__?>?do=comment&operation=del&id=<?=$rs[$i]['id']?>&aid=<?=$rs[$i]['aid']?>"onclick="return confirm('确定要删除?');">删除</a></td>
    </tr>
    
    <tr>
      <td height="2" colspan="5" style="background-color:#DEEFFA;"></td>
    </tr>
    <?php }?>
    <tr>
      <td height="22" colspan="5" align="right"><?=$pagenav?></td>
    </tr>
    <tr class="nobg">
      <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'id')" />
        <label for="chkall">全选</label></td>
      <td colspan="14"><div class="fixsel">
          <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> <input name="action" type="radio" class="radio" value="del"/>删除
        </div></td>
    </tr>
  </table>
  </form>
</div>
<script type="text/javascript">
function _reply(id){
	$.post("<?=__SELF__?>?do=ajax",
		{ "id": id, "replytext": $("#reply_textarea_"+id).val(),'action':'comment'},
		function(o){
			if(o=='1'){
				redirect("<?=__SELF__?>?do=comment");
			}
		} 
	);
}
</script>

</body></html>