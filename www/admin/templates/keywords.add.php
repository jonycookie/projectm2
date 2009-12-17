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
<script type="text/javaScript">admincpnav('首页&nbsp;&raquo;&nbsp;关键字管理','');</script>
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/editor.js"></script>
<div class="container" id="cpcontainer">
  <div class="itemtitle">
    <h3>关键字管理</h3>
    <ul class="tab1" id="submenu">
      <li id="nav_manage"><a href="<?=__SELF__?>?do=keywords"><span>管理</span></a></li>
      <li id="nav_add" class="current"><a href="<?=__SELF__?>?do=keywords&operation=add"><span>添加关键字</span></a></li>
    </ul>
  </div>
  <div id="basic">
    <form action="<?=__SELF__?>?do=keywords&operation=post" method="post">
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?=$id?>"  />
    <table class="tb tb2 nobdb">
      <tr>
        <th colspan="15" class="partition">添加关键字</th>
      </tr>
      <tr class="nobg">
        <td colspan="2">关键字:</td>
      </tr>
      <tr>
        <td><input name="keyword" id="keyword" value="<?=$rs->keyword?>" type="text" class="txt"  /></td>
        <td>要替换的文字</td>
      </tr>
      <tr class="nobg">
        <td colspan="2">替换:</td>
      </tr>
      <tr>
        <td colspan="2">
    	<textarea name="replace" id="replace" class="editor-mini" rows="30" cols="80"><?=$rs['replace']?></textarea>
    	</td>
      </tr>
       <tr class="nobg">
        <td colspan="2"><div class="fixsel">
            <input type="submit" class="btn" name="forumlinksubmit" value="提交"  />
          </div></td>
      </tr>
    </table>
    </form>
  </div>
</div>
</body>
</html>