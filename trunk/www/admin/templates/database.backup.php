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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;数据库管理&nbsp;&raquo;&nbsp;<?=$operation=='backup'?'数据库备份':'数据库修复'?>','');</script>
<div class="container" id="cpcontainer">
  <h3>
    <?=$operation=='backup'?'数据库备份':'数据库修复'?>
  </h3>
  <form action="<?=__SELF__?>?do=database&operation=<?=$operation=='backup'?'savebackup':'post'?>" method="post">
    <table class="tb tb2 ">
      <tr>
        <th width="60">选择</th>
        <th width="60">ID</th>
        <th width="780">数据库表</th>
      </tr>
      <?php foreach($tabledb as $n=>$table){?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="tabledb[]" value="<?=$table?>" /></td>
        <td><?=$n+1?></td>
        <td><?=$table?></td>
      </tr>
      <?php }if ($operation=='backup'){ ?>
      <tr>
        <td colspan="3"><strong>分卷备份</strong></td>
      </tr>
      <tr>
        <td colspan="3"><input name="sizelimit" type=text class="txt" value="2048" size="5">KB 
        每个分卷文件长度</td>
      </tr><?php }?>
      <tr class="nobg">
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'tabledb')" />
          <label for="chkall">全选</label></td>
        <td colspan="2"><?php if ($operation=='repair'){ ?>
          <ul onmouseover="altStyle(this);" style="clear:both; width:100%">
            <li style="float:left; width:80px;"><input name="action" type="radio" class="radio" value="repair"> 修复表</li>
            <li style="float:left; width:80px;"><input name="action" type="radio" class="radio" value="optimize" checked> 优化表</li>
          </ul>
          <?php }?>
          <div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>