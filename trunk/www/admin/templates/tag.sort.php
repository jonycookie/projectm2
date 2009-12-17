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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;TAG归类管理','');</script>
<div class="container" id="cpcontainer">
    <h3>TAG归类管理</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li>点击ID可查看该TAG</li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=tag&operation=post" method="post">
    <input type="hidden" name="action" value="sortedit" />
    <table class="tb tb2 ">
      <tr>
        <td>&nbsp;</td>
        <th>ID</th>
        <th>分类名称</th>
        <th>管理</th>
      </tr>
      <?php if($rs)foreach($rs as $i=>$val){?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$i?>" /></td>
        <td><?=$val['id']?></td>
        <td><input type="text" class="txt" name="name[<?=$i?>]" value="<?=$val['name']?>" style="width:360px;" /></td>
        <td><a href="<?=__SELF__?>?do=tag&operation=delsort&id=<?=$i?>"onClick="return confirm('确定要删除?');">删除 </a></td>
      </tr>
      <?php }?>
       <tr>
        <td colspan="2">新分类名称</td>
        <td colspan="4"><input type="text" class="txt" name="newsortname" value="" style="width:360px;" /></td>
      </tr>
     <tr>
        <td colspan="6"><?=$pagenav?></td>
      </tr>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
          <label for="chkall">删?</label></td>
        <td colspan="4"><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
</body>
</html>