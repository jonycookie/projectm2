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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;留言管理','');</script>
<div class="container" id="cpcontainer">
  <h3>留言管理</h3><form action="<?=__SELF__?>?do=message&operation=post" method="post">
  <table class="tb tb2 ">
    <?php for($i=0;$i<$_count;$i++){
		$rs[$i]['user']=unserialize($rs[$i]['user']);
		if($rs[$i]['reply']){
			$reply=explode('||',$rs[$i]['reply']);
			$reply[0]=='admin'&&$rs[$i]['reply']='<strong>管理员回复：</strong>'.$reply[1];
		}
	?>
    <tr>
      <th width="53"><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['id']?>" /></th>
      <th width="67">留言者：</th>
      <th width="118"><?=$rs[$i]['user']['name']?></th>
      <th width="90">主页/博客：</th>
      <th width="195"><?=$rs[$i]['user']['homepage']?></th>
      <th width="55">E-mail：</th>
      <th width="328"><?=$rs[$i]['user']['email']?></th>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <th>QQ/MSN：</th>
      <th><?=empty($rs[$i]['user']['m'])?'':$rs[$i]['user']['m']?></th>
      <th>IP：</th>
      <th><?=$rs[$i]['ip']?></th>
      <th>时间：</th>
      <th><?=get_date($rs[$i]['addtime'],"Y年m月d日 H时i分s秒")?></th>
    </tr>
    <tr>
      <td>留言：</td>
      <td colspan="6"><?=$rs[$i]['text']?>
        <?php if($rs[$i]['reply']){?>
        <blockquote style="background-color:#F7F7F7;border:#E5E5E5 solid 1px; padding:4px;">
          <?=$rs[$i]['reply']?>
        </blockquote>
      <?php }?><blockquote id="reply<?=$rs[$i]['id']?>" style="display:none;background-color:#F7F7F7;border:#E5E5E5 solid 1px; padding:4px;width:405px">回复：<br />
        <textarea id="reply_textarea_<?=$rs[$i]['id']?>" rows="6" onkeyup="textareasize(this)" name="reply" cols="50" class="tarea"><?=$reply[1]?></textarea>
        <div class="fixsel"><input type="button" class="btn" value="回复"  onclick="_reply(<?=$rs[$i]['id']?>);"/> <input type="button" class="btn" value="取消" onclick="$('#reply<?=$rs[$i]['id']?>').hide();"/></div>
      </blockquote></td>
    </tr>
    <tr>
      <td>管理：</td>
      <td colspan="6"><a href="javascript:void(0);" onclick="$('#reply<?=$rs[$i]['id']?>').toggle();">回复</a> | <a href="<?=__SELF__?>?do=message&operation=del&id=<?=$rs[$i]['id']?>"onClick="return confirm('确定要删除?');">删除</a></td>
    </tr>
    
    <tr>
      <td height="2" colspan="7" style="background-color:#DEEFFA;"></td>
    </tr>
    <?php }?>
    <tr>
      <td height="22" colspan="7" align="right"><?=$pagenav?></td>
    </tr>
    <tr class="nobg">
      <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
        <label for="chkall">删?</label></td>
      <td colspan="16"><div class="fixsel">
          <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
        </div></td>
    </tr>
  </table>
  </form>
</div>
<script type="text/javascript">
function _reply(id){
	$.post("<?=__SELF__?>?do=ajax",
		{ "id": id, "replytext": $("#reply_textarea_"+id).val(),'action':'message'},
		function(data){
			if(data=='1'){
				redirect("<?=__SELF__?>?do=message");
			}
		} 
	);
}
</script>

</body></html>