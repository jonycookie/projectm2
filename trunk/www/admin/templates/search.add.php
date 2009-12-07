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
      <li id="nav_manage"><a href="<?=__SELF__?>?do=search"><span>管理</span></a></li>
      <li id="nav_add" class="current"><a href="<?=__SELF__?>?do=search&operation=add"><span>添加关键字</span></a></li>
    </ul>
  </div>
  <div id="basic">
    <form action="<?=__SELF__?>?do=search&operation=post" method="post">
    <input type="hidden" name="action" value="save" />
    <input type="hidden" name="id" value="<?=$id?>"  />
    <table class="tb tb2 nobdb">
      <tr>
        <th colspan="15" class="partition">添加关键字</th>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">关键字:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="search" id="search" value="<?=$rs->search?>" type="text" class="txt"  /></td>
        <td class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">次数:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="times" id="times" value="<?=$rs->times?>" type="text" class="txt"  /></td>
    	<td class="vtop tips2"></td>
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