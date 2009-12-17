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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;数据库管理&nbsp;&raquo;&nbsp;数据库恢复&nbsp;&raquo;&nbsp;导入备份数据','');</script>
<div class="container" id="cpcontainer">
  <h3>导入备份数据</h3>
  <form action="<?=__SELF__?>?do=database&operation=bakin&pre=<?=$_GET['pre']?>" method="post">
    <table class="tb tb2 ">
      <tr>
        <th>提示信息</th>
      </tr>
      <tr>
        <td>备份恢复功能将覆盖原来的数据,您确认要导入备份数据?</td>
      </tr>
      <tr class="nobg">
        <td><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="确认导入备份"  /> <input type='button' value='返回继续操作' onclick='javascript:history.go(-1);' class="btn" ></div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>