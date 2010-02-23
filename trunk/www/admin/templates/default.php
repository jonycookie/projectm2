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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;预设','');</script>
<div class="container" id="cpcontainer">
  <h3>预设</h3>
  <form action="<?=__SELF__?>?do=default&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <table class="tb tb2 ">
      <tr>
        <th width="50%" class="partition">出处</th>
        <th class="partition">作者</th>
      </tr>
      <tr>
        <td><textarea name="source" id="source" onKeyUp="textareasize(this)" class="tarea"><?=implode("\r\n",$sources)?></textarea></td>
        <td><textarea name="author" id="author" onKeyUp="textareasize(this)" class="tarea"><?=implode("\r\n",$authors)?></textarea></td>
      </tr>
      <tr>
        <th width="50%" class="partition">编辑</th>
        <th class="partition"></th>
      </tr>
      <tr>
        <td><textarea name="editor" id="editor" onKeyUp="textareasize(this)" class="tarea"><?=implode("\r\n",$editors)?></textarea></td>
        <td>&nbsp;</td>
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