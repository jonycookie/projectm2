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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;文件管理&nbsp;&raquo;&nbsp;重新上传文件','');</script>
<div class="container" id="cpcontainer">
<h3>重新上传文件</h3>
<table class="tb tb2 nobdb" id="tips">
  <tr>
    <th colspan="15" class="partition">技巧提示</th>
  </tr>
  <tr>
    <td class="tipsblock"><ul id="tipslis">
        <li>文 件 名：<?=$rs->filename?></li>
        <li>原文件名：<?=$rs->ofilename?></li>
        <li>文件路径：<?=$path?></li>
        <li>文件类型：<?=geticon($rs->filename)?>.<?=$rs->ext?></li>
        <li>保存方式：<?=$rs->type=="remote"?"远程":"本地上传"?></li>
        <li>保存时间：<?=get_date($rs->time,'Y-m-d H:i:s')?></li>
      </ul></td>
  </tr>
</table>
<table class="tb tb2 " width="100%">
    <tr>
      <form action="<?=__SELF__?>?do=file&operation=post" method="post" enctype="multipart/form-data" name="uploadfile" target="post" id="uploadfile">
        <td style="width:60px;">重新上传：</td>
        <td><input name="file" type="file" class="uploadbtn" id="pic" /><input name="fid" type="hidden" value="<?=$fid?>" /><input name="action" type="hidden" value="reupload" /> <input type="submit" value="上传" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
  </table>
</div>
<iframe width="100%" height="100" style="display:none" id="post" name="post"></iframe>
</body>
</html>