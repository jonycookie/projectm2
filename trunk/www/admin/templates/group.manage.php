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
  <h3>组管理</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">管理组</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>超级管理员禁止删除</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=group&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="type" value="a" />
    <table class="tb tb2 ">
      <tr>
        <th>排序</th>
        <th>名称</th>
        <th>管理</th>
      </tr>
      <?php
    	$rs	= $group->group['a'];
		$_count	= count($rs);
      	for($i=0;$i<$_count;$i++){
    ?>
      <tr>
        <td><input type="text" name="order[<?=$rs[$i]['gid']?>]" value="<?=$rs[$i]['order']?>" style="width:20px;border:1px #F6F6F6 solid;"/></td>
        <td><input name="name[<?=$rs[$i]['gid']?>]" type="text" class="txt" value="<?=$rs[$i]['name']?>"/></td>
        <td><a href="<?=__SELF__?>?do=group&operation=power&groupid=<?=$rs[$i]['gid']?>">后台权限</a> | 
          <a href="<?=__SELF__?>?do=group&operation=cpower&groupid=<?=$rs[$i]['gid']?>">栏目权限</a>
          <?php if($rs[$i]['gid']!='1'){?> | <a href="<?=__SELF__?>?do=group&operation=del&groupid=<?=$rs[$i]['gid']?>"  onclick='return confirm("确定要删除该管理组?");'>删除</a><?php }?> </td>
      </tr>
      <?php }?>
      <tr>
        <td><input type="text" name="addneworder" value="<?=$i+1?>" style="width:20px;border:1px #F6F6F6 solid;"/></td>
        <td><input name="addnewname" type="text" class="txt" value=""/>添加新组</td>
        <td></td>
      </tr>
      <tr class="nobg">
        <td class="td25"></td>
        <td colspan="6"><div class="fixsel"> <input type="submit" class="btn" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
  		  <?php /*
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">会员组</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li></li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=group&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="type" value="u" />
    <table class="tb tb2 ">
      <tr>
        <th>排序</th>
        <th>名称</th>
      </tr>
      <?php
    	$rs	= $group->group['u'];
		$_count	= count($rs);
      	for($i=0;$i<$_count;$i++){
    ?>
      <tr>
        <td><input type="text" name="order[<?=$rs[$i]['gid']?>]" value="<?=$rs[$i]['order']?>" style="width:20px;border:1px #F6F6F6 solid;"/></td>
        <td><input name="name[<?=$rs[$i]['gid']?>]" type="text" class="txt" value="<?=$rs[$i]['name']?>"/></td>
      </tr>
      <?php }?>
      <tr>
        <td><input type="text" name="addneworder" value="<?=$i+1?>" style="width:20px;border:1px #F6F6F6 solid;"/></td>
        <td><input name="addnewname" type="text" class="txt" value=""/>添加新组</td>
      </tr>
      <tr class="nobg">
        <td class="td25"></td>
        <td colspan="6"><div class="fixsel"> <input type="submit" class="btn" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
  */?>
</div>
</body></html>