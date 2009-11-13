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
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;文件管理&nbsp;&raquo;&nbsp;上传文件','');</script>
<div class="container" id="cpcontainer">
<h3>上传文件</h3>
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
<table class="tb tb2">
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" name="createdir" target="post" id="createdir" onsubmit="return checkdirname();">
        <td class="td25" style="width:70px;">新目录：</td>
        <td class="vtop rowform"><input type='text' name='dirname' value='' style='width:150px'><input name="savedir" type="hidden" value="<?=$dir?>" /><input name="action" type="hidden" value="createdir" /> <input type="submit" value="创建" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
    <tr>
      <form action="<?=__SELF__?>?do=dialog&operation=post" method="post" enctype="multipart/form-data" name="uploadfile" target="post" id="uploadfile" onsubmit="return checkfile();">
        <td class="td25" style="width:70px;">上　传：</td>
        <td class="vtop rowform"><input name="file" type="file" class="uploadbtn" id="pic" /><input name="savedir" type="hidden" value="<?=$dir?>" /><input name="action" type="hidden" value="uploadfile" /> <input type="submit" value="上传" style="border:1px solid #999999;"/></td>
      </form>
    </tr>
    <tr>
        <td class="td25" style="width:70px;">批量上传：</td>
        <td class="vtop rowform"><?php include "multi.upload.php" ?></td>
    </tr>
  </table>
</div>
<iframe width="100%" height="100" style="display:none" id="post" name="post"></iframe>
<script type="text/javascript">
function checkdirname(){
	if($("input[name=dirname]").val()==""){
		alert("请输入目录名!");
		$("input[name=dirname]").focus();
		return false;
	}
}
function checkfile(){
	if($("input[name=file]").val()==""){
		alert("请选择文件!!");
		$("input[name=file]").click();
		return false;
	}
}
</script>

</body>
</html>