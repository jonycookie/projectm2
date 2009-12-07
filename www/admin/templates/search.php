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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;搜索统计','');</script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>搜索统计</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage" class="current"><a href="<?=__SELF__?>?do=search"><span>管理</span></a></li>
      <li id="nav_add"><a href="<?=__SELF__?>?do=search&operation=add"><span>添加关键字</span></a></li>
    </ul>
  </div>
  <form action="<?=__SELF__?>?do=search&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <td>&nbsp;</td>
        <th>ID</th>
        <th>关键字</th>
        <th>搜索次数</th>
        <th>管理</th>
      </tr>
      <?php for($i=0;$i<$_count;$i++){
	    $rs[$i]['search']=str_replace(array('\%','\_'),array('%','_'),$rs[$i]['search']);
    ?>
      <tr>
        <td><input type="checkbox" class="checkbox" name="delete[]" value="<?=$rs[$i]['id']?>" /></td>
        <td><?=$rs[$i]['id']?></td>
        <td><input type="text" class="txt" name="search[<?=$rs[$i]['id']?>]" value="<?=dhtmlspecialchars($rs[$i]['search'])?>" style="width:300px;" /></td>
        <td><input type="text" class="txt" name="times[<?=$rs[$i]['id']?>]" value="<?=(int)$rs[$i]['times']?>" style="width:120px;"/></td>
        <td><a href="<?=__SELF__?>?do=search&operation=del&id=<?=$rs[$i]['id']?>"onClick="return confirm('确定要删除?');">删除 </a></td>
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
</div>
</body>
</html>