<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?>
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;管理员管理','');</script>
<div class="container" id="cpcontainer">
  <h3>管理员管理</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该管理员</li>
          <li>可单独设置管理员后台权限和栏目权限</li>
          <li>用户权限:综合用户组和管理员单独设置的权限</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=account&operation=post" method="post">
    <input type="hidden" name="action" value="update" />
    <table class="tb tb2 ">
      <tr>
        <td>&nbsp;</td>
        <th>ID</th>
        <th>用户名</th>
        <th>管理组</th>
        <th>最后登陆IP</th>
        <th>最后登陆时间 [登陆次数]</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
    	$rs[$i]['info']=unserialize($rs[$i]['info']);
    ?>
      <tr>
        <td><?php if($rs[$i]['uid']!="1"){?><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['uid']?>" /><?php }?></td>
        <td><?=$rs[$i]['uid']?></td>
        <td><?=$rs[$i]['username']?></td>
        <td><select name="groupid[<?=$rs[$i]['uid']?>]" id="groupid" style="width:auto;"><option value='0'>==无==</option><?=$group->select($rs[$i]['groupid'],'a')?></select></td>
        <td><?=$rs[$i]['lastip']?></td>
        <td><?=get_date($rs[$i]['lastlogintime'],"Y-m-d H:i")?> [<?=$rs[$i]['logintimes']?>]</td>
        <td><a href="<?=__SELF__?>?do=account&operation=edit&uid=<?=$rs[$i]['uid']?>">编辑</a> | <a href="<?=__SELF__?>?do=account&operation=power&uid=<?=$rs[$i]['uid']?>">后台权限</a> | <a href="<?=__SELF__?>?do=account&operation=cpower&uid=<?=$rs[$i]['uid']?>">栏目权限</a> | <a href="<?=__SELF__?>?do=account&operation=del&uid=<?=$rs[$i]['uid']?>"  onclick='return confirm("确定要删除?\n删除管理员不会删除其发表的文章.");'>删除</a></td>
      </tr>
      <?php }?>
      <tr>
        <td colspan="8" align="right"><?=$pagenav?></td>
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