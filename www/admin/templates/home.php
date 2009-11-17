<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
iCMS_admincp_head();
?>
<div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页');</script>
  <div class="itemtitle">
    <h3>欢迎进入系统设置</h3>
  </div>
  <div id="cmsnews"></div>
  <table class="tb tb2 nobdb nobdt fixpadding">
    <tr>
      <th colspan="17" class="partition">数据统计</th>
    </tr>
    <tr>
      <td width="120" class="vtop td24 lineheight">栏目总数</td>
      <td width="300" class="lineheight smallfont"><?=$c?></td>
      <td width="120" class="vtop td24 lineheight">文章总数</td>
      <td class="lineheight smallfont"><?=$a?></td>
    </tr>
    <tr>
      <td width="120" class="vtop td24 lineheight">侍审文章</td>
      <td width="300" class="lineheight smallfont"><?=$iCMS->db->getValue("SELECT count(id) FROM #iCMS@__article WHERE visible='0'");?></td>
      <td width="120" class="vtop td24 lineheight">评论总数</td>
      <td class="lineheight smallfont"><?=$iCMS->db->getValue("SELECT count(id) FROM #iCMS@__comment")?></td>
    </tr>
    <tr>
      <td width="120" class="vtop td24 lineheight">数据库大小</td>
      <td width="300" class="lineheight smallfont"><?=GetFileSize($datasize+$indexsize)?></td>
      <td width="120" class="vtop td24 lineheight">文章数据大小</td>
      <td class="lineheight smallfont"><?=GetFileSize($content_datasize)?></td>
    </tr>
  </table>
  <table class="tb tb2 nobdb nobdt fixpadding">
    <tr>
      <th colspan="17" class="partition">系统信息</th>
    </tr>
    <tr>
      <td width="120" class="vtop td24 lineheight">当前程序版本</td>
      <td width="300" class="lineheight smallfont">iCMS <?=Version?></td>
      <td width="120" class="vtop td24 lineheight"><a href="http://www.idreamsoft.cn/thread.php?fid=8" class="lightlink smallfont" target="_blank">最新版本</a></td>
      <td class="lineheight smallfont"><span id="newversion"><img src="admin/images/ajax_loader.gif" width="16" height="16" align="absmiddle"></span></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">服务器操作系统</td>
      <td class="lineheight smallfont"><?=PHP_OS?></td>
      <td class="vtop td24 lineheight">服务器端口</td>
      <td class="lineheight smallfont"><?=getenv(SERVER_PORT)?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">服务器剩余空间</td>
      <td class="lineheight smallfont"><?=intval(diskfreespace(".") / (1024 * 1024))."M"?></td>
      <td class="vtop td24 lineheight">服务器时间</td>
      <td class="lineheight smallfont"><?=get_date('',"Y年n月j日H点i分s秒")?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">WEB服务器版本</td>
      <td class="lineheight smallfont"><?=$_SERVER['SERVER_SOFTWARE']?></td>
      <td class="vtop td24 lineheight">服务器语种</td>
      <td class="lineheight smallfont"><?=getenv("HTTP_ACCEPT_LANGUAGE")?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">PHP版本</td>
      <td class="lineheight smallfont"><?=PHP_VERSION?></td>
      <td class="vtop td24 lineheight">ZEND版本</td>
      <td class="lineheight smallfont"><?=zend_version()?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">MySQL 数据库</td>
      <td class="lineheight smallfont"><?=okorno(function_exists("mysql_close"))?></td>
      <td class="vtop td24 lineheight">MySQL 版本</td>
      <td class="lineheight smallfont"><?=mysql_get_server_info()?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">图像函数库</td>
      <td class="lineheight smallfont"><?=function_exists("imageline")==1?okorno(function_exists("imageline")):okorno(function_exists("imageline"))?></td>
      <td class="vtop td24 lineheight">Session支持</td>
      <td class="lineheight smallfont"><?=okorno(function_exists("session_start"))?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">脚本运行可占最大内存</td>
      <td class="lineheight smallfont"><?=get_cfg_var("memory_limit")?get_cfg_var("memory_limit"):"无"?></td>
      <td class="vtop td24 lineheight">脚本上传文件大小限制</td>
      <td class="lineheight smallfont"><?=get_cfg_var("upload_max_filesize")?get_cfg_var("upload_max_filesize"):"不允许上传附件"?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">POST方法提交限制</td>
      <td class="lineheight smallfont"><?=get_cfg_var("post_max_size")?></td>
      <td class="vtop td24 lineheight">脚本超时时间</td>
      <td class="lineheight smallfont"><?=get_cfg_var("max_execution_time")?></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">被屏蔽的函数</td>
      <td colspan="2" class="lineheight smallfont"><?=get_cfg_var("disable_functions")?get_cfg_var("disable_functions"):"无"?></td>
      <td class="lineheight smallfont"></td>
    </tr>
  </table>
</div>
<?=iCMS_admincp_footer();?>
</body></html>