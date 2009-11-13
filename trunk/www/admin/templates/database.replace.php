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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;数据库管理&nbsp;&raquo;&nbsp;数据库操作&nbsp;&raquo;&nbsp;批量替换','');</script>
<div class="container" id="cpcontainer">
  <h3>批量替换</h3>
  <form action="<?=__SELF__?>?do=database&operation=post" method="post">
    <table class="tb tb2 ">
      <tr>
        <th colspan="2">批量替换属直接操作数据库，存在一定危险性，请慎用!!!</th>
      </tr>
      <tr>
        <td class="vtop rowform" colspan="2" ><select name="field" id="field">
            <option value="title">标题</option>
            <option value="customlink">自定义链接</option>
            <option value="comments">评论数</option>
            <option value="pic">缩略图</option>
            <option value="cid">栏目</option>
            <option value="tkd">标题/关键字/简介</option>
            <option value="body">内容</option>
          </select></td>
      </tr><tr class="nobg">
        <td colspan="2" class="td27">查找:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="pattern" id="pattern" cols="50" class="tarea"></textarea></td>
          <td class="vtop tips2">查找</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">替换:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="replacement" id="replacement" cols="50" class="tarea"></textarea></td>
          <td class="vtop tips2">替换</td>
        </tr>
        <tr class="nobg">
          <td colspan="2" class="td27">附加条件:</td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="where" id="where" cols="50" class="tarea"></textarea></td>
          <td class="vtop tips2">where (SQL语句)</td>
        </tr>
      <tr class="nobg">
        <td><div class="fixsel"><input name="action" type="hidden" id="action" value="replace" /> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /></div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>