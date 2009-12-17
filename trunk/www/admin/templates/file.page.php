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
<script type="text/javaScript">admincpnav('首页&nbsp;&raquo;&nbsp;独立页面管理&nbsp;&raquo;&nbsp;编辑页面','');</script>
<script type="text/javascript" src="<?=$iCMS->dir?>javascript/editor.js"></script>
<div class="container" id="cpcontainer">
  <h3>独立页面管理</h3>
  <div id="basic">
    <table class="tb tb2 nobdb" id="tips">
      <tr>
        <th colspan="15" class="partition">技巧提示</th>
      </tr>
      <tr>
        <td class="tipsblock"><ul id="tipslis">
            <li>创建时间：<?=$rs->createtime?> 创建者：<?=$rs->creater?></li>
            <li>最后更新：<?=$rs->updatetime?> 更新者：<?=$rs->updater?></li>
          </ul></td>
      </tr>
    </table>
    <form action="<?=__SELF__?>?do=file&operation=post" method="post">
      <input type="hidden" name="action" value="pagedit" /> <input name="name" type="hidden" id="name" value="<?=$catalog->name?>" /> <input name="id" type="hidden" id="id" value="<?=$rs->id?>" /> <input name="cid" type="hidden" id="cid" value="<?=$catalog->id?>" /> <input name="createtime" type="hidden" id="createtime" value="<?=$rs->createtime?>" />
      <table class="tb tb2 nobdb">
        <tr>
          <th colspan="15" class="partition">编辑页面</th>
        </tr>
        <tr class="nobg">
          <td colspan="2">标题：</td>
        </tr>
        <tr>
          <td><input name="title" id="title" value="<?=$rs->title?>" type="text" class="txt"  /></td>
          <td>页面标题</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">关键字：</td>
        </tr>
        <tr>
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="keyword" id="keyword" cols="50" class="tarea"><?=$rs->keyword?></textarea></td>
          <td>页面关键词用于搜索引擎优化，放在 meta 的 keyword 标签中，多个关键字间请用半角逗号 "," 隔开,HTML代码不可用</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">描述：</td>
        </tr>
        <tr>
          <td><textarea  rows="6" onkeyup="textareasize(this)" name="description" id="description" cols="50" class="tarea"><?=$rs->description?></textarea></td>
          <td>页面描述，不要超边200字，HTML代码不可用</td>
        </tr>
        <tr class="nobg">
          <td colspan="2">代码内容: 支持HTML代码</td>
        </tr>
        <tr>
          <td colspan="2" class="vtop">
        	<textarea name="body" id="body" class="editor" rows="30" cols="80"><?=$rs['body']?></textarea>
    	  </td>
        </tr>
        <tr class="nobg">
          <td colspan="2"><div class="fixsel"> <input type="submit" class="btn" name="forumlinksubmit" value="提交"  /> </div></td>
        </tr>
      </table>
    </form>
  </div>
</div>
</body>
</html>