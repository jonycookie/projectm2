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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;<?=lang('menu_filter')?>','');</script>
<div class="container" id="cpcontainer">
  <h3><?=lang('menu_filter')?></h3>
  <form action="<?=__SELF__?>?do=filter&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <th class="partition">禁用词</th>
        <th class="partition">过滤词</th>
     </tr>
      <tr>
        <td><textarea name="disable" id="source" onKeyUp="textareasize(this)" class="tarea"><?=implode("\r\n",$cache['word.disable'])?></textarea></td>
        <td><textarea name="filter" id="editor" onKeyUp="textareasize(this)" class="tarea"><?=implode("\r\n",$filterArray)?></textarea></td>
      </tr>
      <tr class="nobg">
        <td colspan="3"><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>