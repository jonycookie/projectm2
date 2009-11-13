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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;文档自定义属性管理','');</script>
<div class="container" id="cpcontainer">
    <h3>文档自定义属性管理</h3>
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
  <form action="<?=__SELF__?>?do=contentype&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <td>&nbsp;</td>
        <th>ID</th>
        <th>属性名称</th>
        <th>值</th>
        <th>类型</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['id']?>" /></td>
        <td><?=$rs[$i]['id']?></td>
        <td><input type="text" class="txt" name="name[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['name']?>" style="width:360px;" /></td>
        <td><input type="text" class="txt" name="val[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['val']?>" style="width:60px;" /></td>
        <td><input type="text" class="txt" name="type[<?=$rs[$i]['id']?>]" value="<?=$rs[$i]['type']?>" style="width:60px;" /></td>
        <td><a href="<?=__SELF__?>?do=contentype&operation=del&id=<?=$rs[$i]['id']?>"onClick="return confirm('确定要删除?');">删除 </a></td>
      </tr>
      <?php }?>
      <tr>
        <td colspan="5"><?=$pagenav?></td>
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
  <br />
  <br />
    <h3>添加新属性</h3>
  <form action="<?=__SELF__?>?do=contentype&operation=post" method="post">
  	<input type="hidden" name="action" value="add" />
     <table class="tb tb2 ">
      <tr>
        <th>属性名称：<input type="text" class="txt" name="name" value="新属性" style="width:360px;" /></th>
      </tr>
      <tr>
        <th>属 性 值：<input type="text" class="txt" name="val" value="0" style="width:360px;" /></th>
      </tr>
      <tr>
        <th>属性类形：<input type="text" class="txt" name="type" value="article" style="width:60px;" /> article:文章 push:推送</th>
      </tr>
      <tr class="nobg">
        <td><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="添加"  />
          </div></td>
      </tr>
    </table>
 </form>
</div>
</body>
</html>