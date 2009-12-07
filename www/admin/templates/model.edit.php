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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;自定义模型管理&nbsp;&raquo;&nbsp;<?=empty($id)?'新增':'编辑'?>模型','');</script>
<div class="container" id="cpcontainer">
    <h3><?=empty($id)?'新增':'编辑'?>模型</h3>
  <table class="tb tb2 nobdb" id="tips">
    <tr>
      <th colspan="15" class="partition">技巧提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul id="tipslis">
          <li></li>
        </ul></td>
    </tr>
  </table>
  <form action="<?=__SELF__?>?do=model&operation=post" method="post">
    <input type="hidden" name="action" value="edit" />
    <input type="hidden" name="id" value="<?=$id?>" />
    <table class="tb tb2 ">
      <tr class="nobg">
        <th colspan="2" class="td27">模型名称:</th>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="name" type="text" id="name" value="<?=$rs['name']?>" class="txt" /></td>
        <td class="vtop tips2"></td>
      </tr>
        <tr class="nobg">
        <td colspan="2" class="td27">模型表名:</td>
      </tr>
     <tr>
        <td class="vtop rowform"><input name="table" type="text" id="table" value="<?=$rs['table']?>" class="txt" /></td>
        <td class="vtop tips2">请以字母开头,留空将按模型名称拼音</td>
      </tr>
       <tr class="nobg">
        <td colspan="2" class="td27">模型说明:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><textarea  rows="6" onkeyup="textareasize(this)" name="desc" id="desc" cols="50" class="tarea"><?=$rs['desc']?></textarea></td>
        <td class="vtop tips2">100字以内</td>
      </tr>
       <tr class="nobg">
        <td colspan="2" class="td27">列表文件:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="listpage" type="text" id="listpage" value="<?=$rs['listpage']?>" class="txt" /></td>
        <td class="vtop tips2">可留空</td>
      </tr>
       <tr class="nobg">
        <td colspan="2" class="td27">内容显示文件:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="showpage" type="text" id="showpage" value="<?=$rs['showpage']?>" class="txt" /></td>
        <td class="vtop tips2">可留空</td>
      </tr>
     <tr class="nobg">
        <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" value="提交"  /> </div></td>
      </tr>
    </table>
  </form>
</div>
</body></html>