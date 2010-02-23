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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;组管理','');</script>
<div class="container" id="cpcontainer">
<h3>分配栏目管理权限</h3>
  <form action="<?=__SELF__?>?do=group&operation=post" method="post">
    <input type="hidden" name="action" value="cpower" />
    <input type="hidden" name="gid" value="<?=$rs->gid?>" />
    <table class="tb tb2 ">
      <tr>
        <td colspan="2">设置[<?=$rs->name?>]组栏目管理权限</td>
      </tr>
      <tr>
        <td colspan="2" style="width:auto;"><ul>
    <?php if($catalog->Carray)foreach($catalog->Carray AS $key=>$C){?>
		  <li style="width:100%;"><?=str_repeat("│　", $C['level'])."├"?><input name="cpower[]" type="checkbox" class="checkbox" value="<?=$C['id']?>" parent="<?=$C['rootid']?>"/> <?=$C['name']?></li>
	      <?php }?>
      </ul>
      </td>
      </tr>
     <tr class="nobg">
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){ 
	var powerText	= '<?=$rs->cpower?>';
	var powerArray	= powerText.split(',');
	for (i=0;i<powerArray.length;i++){
		$("input[name^=cpower][value="+powerArray[i]+"]").attr('checked',true);
	}
});
</script>
</body></html>