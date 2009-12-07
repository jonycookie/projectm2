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
<link rel="stylesheet" href="images/style.css" type="text/css" media="all" />
<script type="text/JavaScript">admincpnav('首页&nbsp;&raquo;&nbsp;系统设置','');</script>
<div class="container" id="cpcontainer">
  <h3>系统设置</h3>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=setting&operation=post" id="cpform" >
    <input type="hidden" name="action" value="save">
    <table class="tb tb2 ">
      <?php if(empty($operation)||$operation=="config"){$Admin->MP(array("menu_setting_all","menu_setting_config"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;网站信息配置 </th>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">网站名称:</td>
        <td class="td27">模板标签</td>
      </tr>
      <tr>
        <td width="314" class="vtop rowform"><input name="name" value="<?=$iCMS->config['name']?>" type="text" class="txt" id="name"  /></td>
        <td width="418" class="vtop tips2">网站名称</td>
        <td width="190" class="vtop tips2">&lt;!--{$site.title}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">网站 URL:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="url" value="<?=$iCMS->config['url']?>" type="text" class="txt" id="url"  /></td>
        <td class="vtop tips2">网站 URL，必须以 http:// 开头，不要在网址最后加 / <br />
          如:http://www.idreamsoft.cn/iCMS</td>
        <td class="vtop tips2">&lt;!--{$site.url}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">标题附加字:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="seotitle" type="text" class="txt" id="seotitle" value="<?=$iCMS->config['seotitle']?>"  /></td>
        <td class="vtop tips2"><span class="tdbg">网页标题通常是搜索引擎关注的重点，本附加字设置将出现在标题中论坛名称的后面，如果有多个关键字，建议用 &quot;|&quot;、&quot;,&quot;(不含引号) 等符号分隔</span></td>
        <td class="vtop tips2">&lt;!--{$site.seotitle}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">网站关键字:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="keywords" type="text" class="txt" id="keywords" value="<?=$iCMS->config['keywords']?>"  /></td>
        <td class="vtop tips2"><span class="tdbg">更容易被搜索引擎找到用&quot;,&quot;号隔开</span></td>
        <td class="vtop tips2">&lt;!--{$site.keywords}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">网站描述:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="description" type="text" class="txt" id="description" value="<?=$iCMS->config['description']?>"  /></td>
        <td class="vtop tips2">将被搜索引擎用来说明您网站的主要内容</td>
        <td class="vtop tips2">&lt;!--{$site.description}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">ICP备案号:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="icp" type="text" class="txt" id="icp" value="<?=$iCMS->config['icp']?>"  /></td>
        <td class="vtop tips2">页面底部可以显示 ICP 备案信息，如果网站已备案，在此输入您的授权码，它将显示在页面底部，如果没有请留空</td>
        <td class="vtop tips2">&lt;!--{$site.icp}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">站长信箱:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="masteremail" type="text" class="txt" id="masteremail" value="<?=$iCMS->config['masteremail']?>"  /></td>
        <td class="vtop tips2">&nbsp;</td>
        <td class="vtop tips2">&lt;!--{$site.email}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">默认模板:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="template" type="text" class="txt" id="template" value="<?=$iCMS->config['template']?>" /></td>
        <td class="vtop tips2"><img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=dir','template');"/></td>
        <td class="vtop tips2">&lt;!--{$site.tpl}--&gt;</td>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">首页模板:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="indexTPL" type="text" class="txt" id="indexTPL" value="<?=$iCMS->config['indexTPL']?>" /></td>
        <td class="vtop tips2"><img src="admin/images/selecttpl.gif" width="67" height="19" align="absmiddle" onclick="showDialog('<?=__SELF__?>?do=dialog&operation=template&hit=file&type=htm','indexTPL');"/></td>
        <td class="vtop tips2">&nbsp;</td>
      </tr>
      <?php }if(empty($operation)||$operation=="seo"||$operation=="html"){$Admin->MP(array("menu_setting_all","menu_setting_seo","menu_setting_html"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;生成HTML设置/搜索引擎优化</th>
      </tr>
      <tr class="nobg">
        <td colspan="2" class="td27">是否开启生成HTML:</td>
        <td class="td27">&nbsp;</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="ishtm" value="1">是 </li>
            <li><input class="radio" type="radio" name="ishtm" value="0">否 </li>
            <!--li><input class="radio" type="radio" name="ishtm" value="2">访问后生成</li-->
          </ul></td>
        <td class="vtop tips2">&nbsp;</td>
        <td class="vtop tips2">&nbsp;</td>
      </tr>
      <tbody id='html' style="display:none">
        <tr class="nobg">
          <td class="td27">HTML保存目录:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform">文章:<input name="htmdir" value="<?=$iCMS->config['htmdir']?>" type="text" class="txt" id="htmdir"  /><br />栏目:<input name="listhtmdir" value="<?=$iCMS->config['listhtmdir']?>" type="text" class="txt" id="listhtmdir"  /><br />页面:<input name="pagehtmdir" value="<?=$iCMS->config['pagehtmdir']?>" type="text" class="txt" id="pagehtmdir"/><br />TAG:<input name="taghtmdir" value="<?=$iCMS->config['taghtmdir']?>" type="text" class="txt" id="taghtmdir"  /></td>
          <td class="vtop tips2">必须以 / 结束，留空则直接在程序根目录下生成<br />页面:指独立页面</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr class="nobg">
          <td class="td27">生成文件后缀:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="htmlext" type="text" class="txt" id="htmlext" value="<?=$iCMS->config['htmlext']?>"  /></td>
          <td class="vtop tips2">推荐使用.html</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr class="nobg">
          <td class="td27">栏目文件夹创建规则:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="sortdirrule" value="0" /> 默认[全部保存HTML目录下] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="sortdirrule" value="parent" /> 栏目父目录下 </li>
            </ul></td>
          <td class="vtop tips2">&nbsp;</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr class="nobg">
          <td class="td27">栏目分页前缀:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><input name="sortpagePre" type="text" class="txt" id="sortpagePre" value="<?=$iCMS->config['sortpagePre']?>"  /></td>
          <td class="vtop tips2">默认[list_]<br />C：栏目文件夹<br />CID：栏目id<br /></td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr class="nobg">
          <td class="td27">文章生成文件夹创建规则:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="htmdircreaterule" value="0" rule="C"/> 栏目文件夹/[默认] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmdircreaterule" value="1" rule="Y-m-d"/> <?=get_date('','Y-m-d')?>/ </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmdircreaterule" value="3" rule="ID"/> 文章ID </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmdircreaterule" value="2" rule="C/Y-m-d"/> 自定义 </li>
            </ul>
          <input name="customhtmdircreaterule" value="" type="text" class="txt" id="customhtmdircreaterule" />
          </td>
          <td class="vtop tips2">C：栏目文件夹<br />Y：4位数年份，y：2位数年份<br />
          m：有前导零01-12，n：没前导零1-12<br />
          d：有前导零01-31，j：没前导零1-31<br />注：请不要频繁更换文章生成文件夹创建规则和文章生成文件命名规则</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr class="nobg">
          <td class="td27">文章生成文件命名规则:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="htmnamerule" value="0" /> 默认[文章ID.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmnamerule" value="pinyin" /> 文章标题拼音/自定义链接 [biaoti.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmnamerule" value="pubdate" /> 发布时间[<?=get_date('','mdHis')?>.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="htmnamerule" value="ids" /> 文章ID[00000001.html] </li>
            </ul></td>
          <td class="vtop tips2">&nbsp;</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
         <tr class="nobg">
          <td class="td27">TAG生成方式/命名规则:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="tagrule" value="php" /> 动态 [/tag.php?t=tag] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="tagrule" value="dir" /> 目录 [/tag/index.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="tagrule" value="file" /> 文件 [/tag.html] </li>
            </ul></td>
          <td class="vtop tips2">相对于TAG保存目录</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="taghtmrule" value="id" /> ID [<span class='tagrule1'>/tag/1.html</span>] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="taghtmrule" value="pinyin" /> 拼音 [<span class='tagrule2'>/tag/biaoqian.html</span>] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="taghtmrule" value="md5" /> 16位MD5 [<span class='tagrule3'>/tag/2f66aa86e2aa1c1e.html</span>] </li>
            </ul></td>
          <td class="vtop tips2">TAG生成方式</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
       <tr class="nobg">
          <td class="td27">独立页面生成方式:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="pagerule" value="dir" /> 目录 [/page/index.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="pagerule" value="file" /> 文件 [/page.html] </li>
            </ul></td>
          <td class="vtop tips2">&nbsp;</td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
      </tbody>
      <tbody id='customlink' style="display:none">
        <tr class="nobg">
          <td class="td27">自定义链接格式:</td>
          <td class="td27">&nbsp;</td>
          <td class="td27">&nbsp;</td>
        </tr>
        <tr>
          <td class="vtop rowform"><ul onmouseover="altStyle(this);">
              <li> <input class="radio" type="radio" name="linkmode" value="id" /> 文章ID </li>
              <li> <input class="radio" type="radio" name="linkmode" value="title" /> 文章标题拼音/自定义链接 </li>
            </ul>
            <div class="clear"></div>
            <ul onmouseover="altStyle(this);">
              <li style="clear:both"> <input class="radio" type="radio" name="customlink" value="0" />默认1 [show.php?<span class='c1'>id=1</span>][list.php?<span class='c1'>id=1</span>] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="customlink" value="1" />默认2 [show.php?<span class='c2'>id-1</span>.html][list.php?<span class='c2'>id-1</span>.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="customlink" value="2" />默认3 [show<span class='c3'>/1</span>.html][list<span class='c3'>/1</span>.html] </li>
              <li style="clear:both"> <input class="radio" type="radio" name="customlink" value="custom" />自定义，请在下面填入自定义结构 </li>
            </ul>
            <div class="clear"> 静态目录： <input name="rewrite[dir]" id="dir" value="<?=$iCMS->config['rewrite']['dir']?>" class="txt"> <br />
              分 隔 符： <input name="rewrite[split]" id="split" value="<?=$iCMS->config['rewrite']['split']?>" class="txt"> <br />
              扩 展 名： <input name="rewrite[ext]" id="ext" value="<?=$iCMS->config['rewrite']['ext']?>" class="txt"> </div></td>
          <td class="vtop tips2">注：除默认1和默认2外<br />
            	静态目录不等于.php?时<br />
            	需要服务器支持mod_rewrite<br />
            	可写.htaccess <br />
            注：静态目录、分隔符、扩展名不能同时有两项相同<br />
            	静态目录，分隔符不能使用括号中（% & * | < > ? : " ' .\ ）的特殊字符<br />
          		自定义结构时，静态目录不能为空
          </td>
          <td class="vtop tips2">&nbsp;</td>
        </tr>
      </tbody>
      <?php }if(empty($operation)||$operation=="cache"){$Admin->MP(array("menu_setting_all","menu_setting_cache"));?>
      <tr>
        <th colspan="3" class="partition">缓存设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否缓存：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="iscache" value="1" />是</li>
            <li><input class="radio" type="radio" name="iscache" value="0" />否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">缓存目录：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input class="txt" name="cachedir" type="text" id="cachedir" value="<?=$iCMS->config['cachedir']?>" /></td>
        <td colspan="2" class="vtop tips2">缓存目录</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">缓存目录层级：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input class="txt" name="cachelevel" type="text" id="cachelevel" value="<?=$iCMS->config['cachelevel']?>" /></td>
        <td colspan="2" class="vtop tips2">缓存目录层级</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">缓存时间：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input class="txt" name="cachetime" type="text" id="cachetime" value="<?=$iCMS->config['cachetime']?>" /></td>
        <td colspan="2" class="vtop tips2">缓存时间</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否gzip压缩缓存数据：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="iscachegzip" value="1" />是</li>
            <li><input class="radio" type="radio" name="iscachegzip" value="0" />否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <?php }if(empty($operation)||$operation=="other"){$Admin->MP(array("menu_setting_all","menu_setting_other"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;其它设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否允许评论：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="iscomment" value="1" />是</li>
            <li><input class="radio" type="radio" name="iscomment" value="0" />否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否审核评论：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="isexamine" value="1" />是</li>
            <li><input class="radio" type="radio" name="isexamine" value="0" />否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否允许匿名评论：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="anonymous" value="1" />是</li>
            <li><input class="radio" type="radio" name="anonymous" value="0" />否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">匿名显示：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input class="txt" name="anonymousname" type="text" id="anonymousname" value="<?=$iCMS->config['anonymousname']?>" /></td>
        <td colspan="2" class="vtop tips2">匿名显示</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">搜索每页显示多少条记录：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="searchprepage" type="text" id="searchprepage" value="<?=$iCMS->config['searchprepage']?>" maxlength="6" class="txt"/></td>
        <td colspan="2" class="vtop tips2">搜索每页显示多少条记录</td>
      </tr>
      <?php }if(empty($operation)||$operation=="time"){$Admin->MP(array("menu_setting_all","menu_setting_time"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;时间设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">服务器所在时区：</td>
      </tr>
      <tr>
        <td class="vtop rowform"><select name="ServerTimeZone">
            <option value="-12">(标准时-12:00) 日界线西 </option>
            <option value="-11">(标准时-11:00) 中途岛、萨摩亚群岛 </option>
            <option value="-10">(标准时-10:00) 夏威夷 </option>
            <option value="-9">(标准时-9:00) 阿拉斯加 </option>
            <option value="-8">(标准时-8:00) 太平洋时间(美国和加拿大) </option>
            <option value="-7">(标准时-7:00) 山地时间(美国和加拿大) </option>
            <option value="-6">(标准时-6:00) 中部时间(美国和加拿大)、墨西哥城 </option>
            <option value="-5">(标准时-5:00) 东部时间(美国和加拿大)、波哥大 </option>
            <option value="-4">(标准时-4:00) 大西洋时间(加拿大)、加拉加斯 </option>
            <option value="-3.5">(标准时-3:30) 纽芬兰 </option>
            <option value="-3">(标准时-3:00) 巴西、布宜诺斯艾利斯、乔治敦 </option>
            <option value="-2">(标准时-2:00) 中大西洋 </option>
            <option value="-1">(标准时-1:00) 亚速尔群岛、佛得角群岛 </option>
            <option value="111">(格林尼治标准时) 西欧时间、伦敦、卡萨布兰卡 </option>
            <option value="1">(标准时+1:00) 中欧时间、安哥拉、利比亚 </option>
            <option value="2">(标准时+2:00) 东欧时间、开罗，雅典 </option>
            <option value="3">(标准时+3:00) 巴格达、科威特、莫斯科 </option>
            <option value="3.5">(标准时+3:30) 德黑兰 </option>
            <option value="4">(标准时+4:00) 阿布扎比、马斯喀特、巴库 </option>
            <option value="4.5">(标准时+4:30) 喀布尔 </option>
            <option value="5">(标准时+5:00) 叶卡捷琳堡、伊斯兰堡、卡拉奇 </option>
            <option value="5.5">(标准时+5:30) 孟买、加尔各答、新德里 </option>
            <option value="6">(标准时+6:00) 阿拉木图、 达卡、新亚伯利亚 </option>
            <option value="7">(标准时+7:00) 曼谷、河内、雅加达 </option>
            <option value="8">(北京时间) 北京、重庆、香港、新加坡 </option>
            <option value="9">(标准时+9:00) 东京、汉城、大阪、雅库茨克 </option>
            <option value="9.5">(标准时+9:30) 阿德莱德、达尔文 </option>
            <option value="10">(标准时+10:00) 悉尼、关岛 </option>
            <option value="11">(标准时+11:00) 马加丹、索罗门群岛 </option>
            <option value="12">(标准时+12:00) 奥克兰、惠灵顿、堪察加半岛 </option>
          </select></td>
        <td colspan="2" class="vtop tips2">服务器所在时区</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">服务器时间校正（分钟）:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="cvtime" type="text" id="cvtime" value="<?=$iCMS->config['cvtime']?>" maxlength="6" class="txt"/></td>
        <td colspan="2" class="vtop tips2">此功能用于校正服务器操作系统时间设置错误的问题<br />
          当确认程序默认时区设置正确后，程序显示时间仍有错误，请使用此功能校正</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">默认时间格式:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="dateformat" type="text" id="dateformat" value="<?=$iCMS->config['dateformat']?>" class="txt"/></td>
        <td colspan="2" class="vtop tips2">格式如:Y-m-d H:i:s <br />
          Y:4位数年份,y:2位数年份<br />
          m:有前导零01-12,n:没前导零1-12<br />
          d:有前导零01-31,j:没前导零1-31</td>
      </tr>
      <?php }if(empty($operation)||$operation=="publish"){$Admin->MP(array("menu_setting_all","menu_setting_publish"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;发表文章相关设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">关键字自动转化为标签:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="keywordToTag" value="1">开启 </li>
            <li><input class="radio" type="radio" name="keywordToTag" value="0">关闭 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">开启后发表文章时该选项默认为选中状态</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">下载远程图片和资源:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="remote" value="1">开启 </li>
            <li><input class="radio" type="radio" name="remote" value="0">关闭 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">开启后发表文章时该选项默认为选中状态</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">提取第一个图片为缩略图:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="autopic" value="1">开启 </li>
            <li><input class="radio" type="radio" name="autopic" value="0">关闭 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">开启后发表文章时该选项默认为选中状态</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">自动提取内容摘要:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="autodesc" value="1">开启 </li>
            <li><input class="radio" type="radio" name="autodesc" value="0">关闭 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">开启后发表文章时程序要自动提取文章部分内容为文章摘要</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">自动提取内容摘要字数:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="descLen" type="text" id="descLen" value="<?=$iCMS->config['descLen']?>" class="txt"></td>
        <td colspan="2" class="vtop tips2">设置自动提取内容摘要字数</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否检查文章标题重复:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li><input class="radio" type="radio" name="repeatitle" value="1">开启 </li>
            <li><input class="radio" type="radio" name="repeatitle" value="0">关闭 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">开启后不能发表相同标题的文章</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否分割系统生成自定链接:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="CLsplit" type="text" id="CLsplit" value="<?=$iCMS->config['CLsplit']?>" class="txt"></td>
        <td colspan="2" class="vtop tips2">留空，按紧凑型生成</td>
      </tr>
      <?php }if(empty($operation)||$operation=="attachments"){$Admin->MP(array("menu_setting_all","menu_setting_attachments"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;附件设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">上传文件保存目录:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="uploadfiledir" type="text" id="uploadfiledir" value="<?=$iCMS->config['uploadfiledir']?>" class="txt"></td>
        <td colspan="2" class="vtop tips2">相对于程序根目录</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">上传文件保存方式:</td>
      </tr>
      <tr>
        <td class="vtop rowform">
          <input name="savedir" type="text" id="savedir" value="<?=$iCMS->config['savedir']?>" size="50" class="txt"></td>
        <td colspan="2" class="vtop tips2">为空全部存入同一目录<br />EXT：文件类型<br />Y：4位数年份，y：2位数年份<br />
          m：有前导零01-12，n：没前导零1-12<br />
          d：有前导零01-31，j：没前导零1-31</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">允许上传文件类型:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="fileext" type="text" id="fileext" value="<?=$iCMS->config['fileext']?>" size="50" class="txt"></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否生成缩略图:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li> <input class="radio" type="radio" name="isthumb" value="1" /> 是 </li>
            <li> <input class="radio" type="radio" name="isthumb" value="0" /> 否 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">缩略图宽度/高度:</td>
      </tr>
      <tr>
        <td class="vtop rowform">宽度： <input name="thumbwidth" type="text" value="<?=$iCMS->config['thumbwidth']?>" size=10 maxlength="3" class="txt"/> 像素 <br>
          高度： <input name="thumbhight" type="text" value="<?=$iCMS->config['thumbhight']?>" size=10 maxlength="3" class="txt"/> 像素</td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <?php }if(empty($operation)||$operation=="watermark"){$Admin->MP(array("menu_setting_all","menu_setting_watermark"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;水印设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否使用图片水印功能:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li> <input class="radio" type="radio" name="iswatermark" value="1" /> 是 </li>
            <li> <input class="radio" type="radio" name="iswatermark" value="0" /> 否 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2">将在上传的图片附件中加上您在下面设置的图片或文字水印</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">添加水印的图片大小控制:</td>
      </tr>
      <tr>
        <td class="vtop rowform">宽度： <input size="10" name="waterwidth" value="<?=$iCMS->config['waterwidth']?>" class="txt"/> 像素 <br />
          高度： <input size="10" name="waterheight" value="<?=$iCMS->config['waterheight']?>" class="txt"/> 像素</td>
        <td colspan="2" class="vtop tips2">只对超过程序设置的大小的附件图片才加上水印图片或文字(设置为0不限制)</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印位置:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li style="clear:both;width:100%"><input class="radio" type="radio" name="waterpos" value="0" /> 随机位置 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="1" />顶部居左 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="2" />顶部居中 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="3" />顶部居右 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="4" />中部居左 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="5" />中部居中 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="6" />中部居右 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="7" />底部居左 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="8" />底部居中 </li>
            <li> <input class="radio" type="radio" name="waterpos" value="9" />底部居右 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印图片文件:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="waterimg" value="<?=$iCMS->config['waterimg']?>" class="txt"/></td>
        <td colspan="2" class="vtop tips2">水印图片存放路径：include/watermark/<?=$iCMS->config['waterimg']?>，
          如果水印图片不存在，则使用文字水印</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印文字:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="watertext" value="<?=$iCMS->config['watertext']?>" size="40" class="txt"/></td>
        <td colspan="2" class="vtop tips2">暂不支持中文</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印文字字体大小:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="waterfontsize" value="<?=$iCMS->config['waterfontsize']?>" size="10" class="txt"/></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印文字颜色:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="watercolor" value="<?=$iCMS->config['watercolor']?>" size="10" maxlength="7" class="txt"/></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">水印透明度:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="waterpct" value="<?=$iCMS->config['waterpct']?>" size="10" maxlength="3" class="txt"/></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <?php }if(empty($operation)||$operation=="bbs"){$Admin->MP(array("menu_setting_all","menu_setting_bbs"));?>
      <tr>
        <th colspan="3" class="partition">&nbsp;调用论坛设置</th>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否调用论坛:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li> <input class="radio" type="radio" name="bbs[call]" value="1" /> 调用 </li>
            <li> <input class="radio" type="radio" name="bbs[call]" value="0" /> 不调用 </li>
          </ul></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛程序类型:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><select name="bbs[type]" id="bbs[type]">
      <option value="PHPWind">PHPWind</option><option value="Discuz">Discuz!</option>
    </select></td>
        <td colspan="2" class="vtop tips2"></td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛网址:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[url]" type="text" class="txt" id="bbs_url" value="<?=$iCMS->config['bbs']['url']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">后面不要加/</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库服务器:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[dbhost]" type="text" class="txt" id="bbs_dbhost" value="<?=$iCMS->config['bbs']['dbhost']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">本地请用localhost 远程请用IP 如：222.111.22.11</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库用户名:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[dbuser]" type="text" class="txt" id="bbs_dbuser" value="<?=$iCMS->config['bbs']['dbuser']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">如果论坛跟本程序同数据库可为空</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库密码:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[dbpw]" type="password" class="txt" id="bbs_dbpw" value="<?php //$iCMS->config['bbs']['dbpw'];?>" size="50"><br /> 如果不为空，每次修改请重新填写</td>
        <td colspan="2" class="vtop tips2">如果论坛跟本程序同数据库可为空</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库名:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[dbname]" type="text" class="txt" id="bbs_dbname" value="<?=$iCMS->config['bbs']['dbname']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">论坛数据库名</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库编码:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[charset]" type="text" class="txt" id="bbs_charset" value="<?=$iCMS->config['bbs']['charset']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">为空则跟本程序同一编码 gbk utf8 gb2312</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛数据库前缀:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[dbpre]" type="text" class="txt" id="bbs_dbpre" value="<?=$iCMS->config['bbs']['dbpre']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">论坛数据库前缀<br />PHPWind 常用pw_ &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp;Discuz! 常用cdb_</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛图片目录:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[picpath]" type="text" class="txt" id="bbs_picpath" value="<?=$iCMS->config['bbs']['picpath']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">必须为论坛空间上的目录,如果设置绝对路径,目录分隔符请使用 / 不要使用 \ </td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">论坛附件目录:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[attachdir]" type="text" class="txt" id="bbs_attachdir" value="<?=$iCMS->config['bbs']['attachdir']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">如果为远程附件地址，请填写附件网址</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">是否开启静态目录部署功能:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><ul onmouseover="altStyle(this);">
            <li> <input type="radio" class="radio" name="bbs[htmifopen]" value="1" >是</li>
            <li> <input type="radio" class="radio" name="bbs[htmifopen]" value="0" >否</li>
          </ul></td>
        <td colspan="2" class="vtop tips2">如果论坛未开启伪静态功能,请勿启用</td>
      </tr>
      <tbody id="pw" style="display:none;">
      <tr class="nobg">
        <td colspan="3" class="td27">静态目录:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[htmdir]" type="text" class="txt" id="bbs_htmdir" value="<?=$iCMS->config['bbs']['htmdir']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">保持与论坛设置一致</td>
      </tr>
      <tr class="nobg">
        <td colspan="3" class="td27">静态目录扩展名设置:</td>
      </tr>
      <tr>
        <td class="vtop rowform"><input name="bbs[htmext]" type="text" class="txt" id="bbs_htmext" value="<?=$iCMS->config['bbs']['htmext']?>" size="50"></td>
        <td colspan="2" class="vtop tips2">保持与论坛设置一致</td>
      </tr>
      </tbody>
      <?php }?>
      <tr class="nobg">
        <td colspan="3"><input type="submit" class="btn" name="settingsubmit" value="提交"  /></td>
      </tr>
    </table>
  </form>
</div>
<script type="text/javascript">
$(function(){
<?php if(empty($operation)||$operation=="seo"||$operation=="html"){?>
	$('input[name=ishtm]').click(function(){
		if(this.value!="0"){
			$("#html").show();
			$("#customlink").hide();
		}else{
			$("#html").hide();
			$("#customlink").show();
		}
	});
	$('input[name=linkmode]').click(function(){
		if(this.value=="id"){
			$(".c1").text("id=1");
			$(".c2").text("id-1");
			$(".c3").text("/1");
		}else if(this.value=='title'){
			$(".c1").text("t=biaoti");
			$(".c2").text("t-biaoti");
			$(".c3").text("/biaoti");
		}
	});
	$('input[name=tagrule]').click(function(){
		if(this.value=="dir"){
			$(".tagrule1").text("/1/index.html");
			$(".tagrule2").text("/biaoqian/index.html");
			$(".tagrule3").text("/2f66aa86e2aa1c1e/index.html");
		}else if(this.value=='file'){
			$(".tagrule1").text("/1.html");
			$(".tagrule2").text("/biaoqian.html");
			$(".tagrule3").text("/2f66aa86e2aa1c1e.html");
		}
	});
	$('input[name=htmdircreaterule]').click(function(){
		$('#customhtmdircreaterule').val($(this).attr('rule'));
	});
	$('input[name=customlink]').click(function(){
		if(this.value=="0"){
			$('#dir').attr('readonly',true);
			rewrite('.php?','<?=$iCMS->config['rewrite']['split']?>','<?=$iCMS->config['rewrite']['ext']?>');
		}else if(this.value=="1"){
			$('#dir').attr('readonly',true);
			rewrite('.php?','-','.html');
		}else if(this.value=="2"){
			$('#dir').attr('readonly',true);
//			$('#split').attr('readonly',true);
			rewrite('','/','.html');
		}else if(this.value=="custom"){
			$('#dir').attr('readonly',false);
			rewrite('.php?','<?=$iCMS->config['rewrite']['split']?>','<?=$iCMS->config['rewrite']['ext']?>');
		}
	});
	$('#dir').click(function(){
		$('#dir').attr('readonly',false);
		$('input[name=customlink][value=custom]').click();
	})
	$('input[name=ishtm][value=<?=$iCMS->config['ishtm']?>]').click();
	$('input[name=htmdircreaterule][value=<?=$iCMS->config['htmdircreaterule']?>]').click();
	$('input[name=tagrule][value=<?=$iCMS->config['tagrule']?>]').click();
	checked('ishtm',"<?=$iCMS->config['ishtm']?>");
	checked('htmdircreaterule',"<?=$iCMS->config['htmdircreaterule']?>");
	checked('sortdirrule',"<?=$iCMS->config['sortdirrule']?>");
	$('#customhtmdircreaterule').val("<?=$iCMS->config['customhtmdircreaterule']?>");
	checked('htmnamerule',"<?=$iCMS->config['htmnamerule']?>");
	checked('linkmode',"<?=$iCMS->config['linkmode']?>");
	checked('customlink',"<?=$iCMS->config['customlink']?>");
	checked('tagrule',"<?=$iCMS->config['tagrule']?>");
	checked('taghtmrule',"<?=$iCMS->config['taghtmrule']?>");
	checked('pagerule',"<?=$iCMS->config['pagerule']?>");
	<?php }if(empty($operation)||$operation=="cache"){?>
	checked('iscache',"<?=$iCMS->config['iscache']?>");
	checked('iscachegzip',"<?=$iCMS->config['iscachegzip']?>");
	<?php }if(empty($operation)||$operation=="other"){?>
	checked('iscomment',"<?=$iCMS->config['iscomment']?>");
	checked('isexamine',"<?=$iCMS->config['isexamine']?>");
	checked('anonymous',"<?=$iCMS->config['anonymous']?>");
	<?php }if(empty($operation)||$operation=="time"){?>
	$('select[name=ServerTimeZone]').val(<?=$iCMS->config['ServerTimeZone']?>);
	<?php }if(empty($operation)||$operation=="publish"){?>
	checked('keywordToTag',"<?=$iCMS->config['keywordToTag']?>");
	checked('remote',"<?=$iCMS->config['remote']?>");
	checked('autopic',"<?=$iCMS->config['autopic']?>");
	checked('autodesc',"<?=$iCMS->config['autodesc']?>");
	checked('repeatitle',"<?=$iCMS->config['repeatitle']?>");
	<?php }if(empty($operation)||$operation=="attachments"){?>
	checked('isthumb',"<?=$iCMS->config['isthumb']?>");
	<?php }if(empty($operation)||$operation=="watermark"){?>
	checked('iswatermark',"<?=$iCMS->config['iswatermark']?>");
	checked('waterpos',"<?=$iCMS->config['waterpos']?>");
	<?php }if(empty($operation)||$operation=="bbs"){?>
	checked('\'bbs[call]\'',"<?=$iCMS->config['bbs']['call']?>");
	$("select[name='bbs[type]']").val("<?=$iCMS->config['bbs']['type']?>");
	checked('\'bbs[htmifopen]\'',"<?=$iCMS->config['bbs']['htmifopen']?>");
	var bbstype="<?=$iCMS->config['bbs']['type']?>";
	if(bbstype=="PHPWind"){
		$("#pw").show();
	}else{
		$("#pw").hide();
	}
	$("select[name='bbs[type]']").change(function(){
		if(this.value=="PHPWind"){
			$("#pw").show();
		}else{
			$("#pw").hide();
		}
	}); 
	<?php }?>
});
function checked(n,v){
	$('input[name='+n+'][value='+v+']').attr("checked",true).parent().addClass("checked");
}
function rewrite(d,s,e){
	$("#dir").val(d);
	$("#split").val(s);
	$("#ext").val(e);
}
</script>
</body></html>