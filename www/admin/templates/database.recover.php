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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;数据库管理&nbsp;&raquo;&nbsp;数据库恢复','');</script>
<div class="container" id="cpcontainer">
  <h3>数据库恢复</h3>
  <form action="<?=__SELF__?>?do=database&operation=<?=$operation=='backup'?'savebackup':'post'?>" method="post">
    <table class="tb tb2 ">
      <tr>
        <th style="width:60px;">ID</th>
        <th>文件名</th>
        <th>版本</th>
        <th>备份时间</th>
        <th>卷号</th>
        <th>导入</th>
        <th style="width:60px;">删</th>
      </tr>
      <?php foreach($filedb as $n=>$file){?>
      <tr>
        <td><?=$n+1?></td>
        <td><?=$file['name']?></td>
        <td><?=$file['version']?></td>
        <td><?=$file['time']?></td>
        <td><?=$file['num']?></td>
        <td><a href="<?=__SELF__?>?do=database&operation=bakincheck&pre=<?=$file['pre']?>">导入</a></td>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$file['name']?>" /></td>
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
        <td class="td25"><input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll('prefix', this.form, 'delete')" />
          <label for="chkall">全选</label></td>
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>