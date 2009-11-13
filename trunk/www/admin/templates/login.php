<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>iCMS Administrator's Control Panel</title>
<meta http-equiv="Content-Type" content="text/html;charset=<?=iCMS_CHARSET?>" />
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="admin/images/style.css" type="text/css" media="all" />
<script src="javascript/jquery.js" type="text/javascript"></script>
<script type="text/javascript">
$(function(){
	$("#seccodeimg").click(function(){
		$(this).attr("src","include/seccode.php?"+Math.random());
	}); 
	$("#username").focus();
	if(self.parent.frames.length != 0) {
		self.parent.location=document.location;
	}
});
</script>
</head>
<body>
<table class="logintb">
  <tr>
    <td class="login"><h1>iCMS Administrator's Control Panel</h1>
      <p>iCMS 是一个采用 PHP 和 MySQL 数据库构建的高效文章管理解决方案</p></td>
    <td><form method="post" name="login" id="loginform" action="<?=$_SERVER['PHP_SELF']?>">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="frames" value="yes">
        <p class="logintitle">用户名: </p>
        <p class="loginform">
          <input name="username" type="text" id="username" size="20" class="txt"/>
        </p>
        <p class="logintitle">密　码:</p>
        <p class="loginform">
          <input name="password" type="password" id="password" class="txt" />
        </p>
        <p class="logintitle">验证码:</p>
        <p class="loginform">
          <input name="seccode" type="text" id="seccode" size="4" maxlength="4"/>
          <img src="include/seccode.php" alt="看不清楚?点击刷新" align="absmiddle" id="seccodeimg"/></p>
        <p class="loginnofloat">
          <input name="submit" value="提交"  tabindex="3" type="submit" class="btn" />
        </p>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2" class="footer"><div class="copyright">
	  <p>Powered by <a href="http://www.iDreamSoft.cn" target="_blank">iCMS</a><?=Version?> </p>
	  <p>&copy;2007-2009, <a href="http://www.iDreamSoft.cn" target="_blank">iDreamSoft</a> Inc.</p>
      </div></td>
  </tr>
</table>
</body>
</html>
<?php exit();?>