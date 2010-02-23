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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;会员管理','');</script>
<div class="container" id="cpcontainer">
  <h3>会员管理</h3>
  <form action="<?=__SELF__?>?do=user&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <td>&nbsp;</td>
        <th>ID</th>
        <th>用户名[昵称]</th>
        <th>最后登陆IP</th>
        <th>最后登陆时间 [登陆次数]</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
    	$rs[$i]['info']=unserialize($rs[$i]['info']);
    ?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['uid']?>" /></td>
        <td><?=$rs[$i]['uid']?></td>
        <td><?php echo $rs[$i]['username'];if($rs[$i]['info']['nickname']) echo "[{$rs[$i]['info']['nickname']}]"?></td>
        <td><?=$rs[$i]['lastip']?></td>
        <td><?=get_date($rs[$i]['lastlogintime'],"Y-m-d H:i")?> [<?=$rs[$i]['logintimes']?>]</td>
        <td><a href="<?=__SELF__?>?do=article&operation=manage&act=user&userid=<?=$rs[$i]['uid']?>">文章</a> | 
          <a href="<?=__SELF__?>?do=article&operation=manage&act=user&userid=<?=$rs[$i]['uid']?>&type=draft">审核</a> | 
          <a href="<?=__SELF__?>?do=user&operation=edit&userid=<?=$rs[$i]['uid']?>">编辑</a> | <a href="<?=__SELF__?>?do=user&operation=del&userid=<?=$rs[$i]['uid']?>"  onclick='return confirm("确定要删除?\n删除会员不会删除其发表的文章.");'>删除</a> </td>
      </tr>
      <?php }?>
      <tr>
        <td colspan="7" align="right"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
          <label for="chkall">删?</label></td>
        <td colspan="6"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>