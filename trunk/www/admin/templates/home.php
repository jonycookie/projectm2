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
<style type="text/css">
#cmsnews {display:block;clear:both;}
.homepop {display:none;width:360px; border:#B5CFD9 solid 1px; background-color:#FFFFFF;}
</style>
<div class="container" id="cpcontainer">
  <script type="text/JavaScript">admincpnav('首页');</script>
  <script src="javascript/jquery.ui.core.js" type="text/javascript"></script>
  <script src="javascript/jquery.draggable.js" type="text/javascript"></script>
  <script src="javascript/jquery.floatDiv.js" type="text/javascript"></script>
  <script type="text/JavaScript">
  function dofeedback(){
  	$('#feedback').toggle().floatdiv("middle").draggable();
  }
	$(function(){
		$.getJSON("http://www.idreamsoft.cn/cms/getLicense.php?callback=?",{license: '<?=$license?>'},
			function(o){
			  	$('#license').html(o.license);
			}
		);
	});
  </script>
  <div class="itemtitle">
    <h3>欢迎进入系统设置</h3>
  </div>
  <div id="cmsnews"></div>
  <table class="tb tb2 ">
    <tr>
      <th  class="partition">iCMS提示</th>
    </tr>
    <tr>
      <td class="tipsblock"><ul>
          <li>授权信息:<span id="license"></span></li>
          <li><a href="http://www.idreamsoft.cn/doc/iCMS/index.html" target="_blank">模版标签说明</a></li>
          <li><a href="http://www.idreamsoft.cn/doc/iCMS.License.html" target="_blank">iCMS使用许可协议</a></li>
          <li><a href="javascript:void(0)" onclick="dofeedback();">提交BUG/问题</a></li>
        </ul></td>
    </tr>
  </table>
  <div id="!license" class="homepop">
    <table class="tb tb2 nobdb">
        <tr>
          <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" onclick="$('#license').hide();"><img src="admin/images/close.gif" /></span>授权信息</th>
        </tr>
      <tr>
          <td class="vtop rowform"></td>
      </tr>
      </table>
  </div>
  <div id="feedback" class="homepop">
    <table class="tb tb2 nobdb">
      <form action="http://www.idreamsoft.cn/cms/feedback.php" method="post" target="postfeedback">
      <input name="sname" type="hidden" value="<?=base64_encode($iCMS->config['name'])?>" />
      <input name="url" type="hidden" value="<?=base64_encode($iCMS->config['url'])?>" />
      <input name="host" type="hidden" value="<?=base64_encode($_SERVER['HTTP_HOST'])?>" />
        <tr>
          <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" class="close" onclick="$('#feedback').hide();"><img src="admin/images/close.gif" /></span>用户反馈</th>
        </tr>
        <tr>
          <td class="rowform vtop" style="width:98%;"><p align="left"><strong>尊敬的用户：</strong><br />
                  ·如果您想了解如何使用iCMS，请参考<a href="http://www.idreamsoft.cn/help.html" target="_blank">帮助中心</a>。<br />
                  ·如果您对我们的产品想提出意见或建议，请填写具体内容。<br />
                  ·如果您留下真实邮箱，将有机会获得我们送出的小礼品。</p></td>
        </tr>
        <tr>
          <td class="rowform vtop"><strong>您遇到的问题类型：（必填）</strong></td>
        </tr>
        <tr>
          <td class="vtop rowform"><select name="type">
              <option value="bug" selected="selected">程序bug或问题</option>
              <option value="advice">新建议或改进</option>
              <option value="other">其他</option>
            </select>          </td>
        </tr>
        <tr>
          <td class="rowform vtop"><strong>问题描述：（建议填写） </strong></td>
        </tr>
        <tr>
          <td class="vtop rowform"><textarea name="msg"  style="width:98%" onkeyup="textareasize(this)" class="tarea"></textarea></td>
        </tr>
        <tr>
          <td class="rowform vtop"><strong>您的邮箱：（建议填写）</strong></td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="email" type="text" class="txt" style="width:98%"/></td>
        </tr>
        <tr>
          <td colspan="2" align="center"><input name=""  type="submit" class="btn" value="提交"/></td>
        </tr>
      </form>
    </table>
    <iframe width="100%" height="100" style="display:none" id="postfeedback" name="postfeedback"></iframe>
  </div>
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
  <table class="tb tb2 fixpadding">
    <tr>
      <th colspan="15" class="partition">iCMS 开发</th>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">版权所有</td>
      <td><span class="bold"><a href="http://www.idreamsoft.cn" class="lightlink2" target="_blank">iDreamSoft</a></span></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">开发</td>
      <td class="lineheight smallfont"><a href="http://www.idreamsoft.cn/coolmoo" class="lightlink smallfont" target="_blank">枯木 (coolmoo)</a></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">安全检测</td>
      <td class="lineheight smallfont"><a href="http://www.slenk.net" class="lightlink smallfont" target="_blank">Lenk技术联盟 (http://www.slenk.net)</a></td>
    </tr>
    <tr>
      <td class="vtop td24 lineheight">相关链接</td>
      <td class="lineheight"><a href="http://www.idreamsoft.cn" class="lightlink" target="_blank">iDreamSoft</a>, <a href="http://www.idreamsoft.cn/forumdisplay.php?fid=6" class="lightlink" target="_blank">iCMS</a>, <a href="http://www.idreamsoft.cn/forumdisplay.php?fid=7" class="lightlink" target="_blank">&#x6A21;&#x677F;</a>, <a href="http://www.idreamsoft.cn/doc/iCMS/index.html" class="lightlink" target="_blank">&#x6587;&#x6863;</a>, <a href="http://www.idreamsoft.cn/forumdisplay.php?fid=6" class="lightlink" target="_blank">&#x8BA8;&#x8BBA;&#x533A;</a></td>
    </tr>
  </table>
</div>
<?=iCMS_admincp_footer();?>
</body></html>