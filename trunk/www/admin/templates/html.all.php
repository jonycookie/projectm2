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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;HTML更新&nbsp;&raquo;&nbsp全站更新','');</script>
<div class="container" id="cpcontainer">
  <h3>全站更新</h3>
  <form name="cpform" method="get" action="<?=__SELF__?>" id="cpform" >
    <input type="hidden" name="do" value="html" />
    <input type="hidden" name="operation" value="create" />
    <input type="hidden" name="action" value="all" />
    <table class="tb tb2 ">
      <tr>
        <td class="vtop tips2" align="center">全站HTML更新，很消耗服务器资源．请慎用！</td>
      </tr>
      <tr>
        <td align="center"><input type="submit" class="btn" name="cleanupsubmit" value="提交" /></td>
      </tr>
    </table>
  </form>
</div>
</body></html>
