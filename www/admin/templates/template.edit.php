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
<script src="javascript/jquery.ui.core.js" type="text/javascript"></script>
<script src="javascript/jquery.draggable.js" type="text/javascript"></script>
<script src="javascript/jquery.floatDiv.js" type="text/javascript"></script>
<script type="text/javascript"> 
$(function(){
	$("#tagTip").floatdiv("righttop").draggable();
	$("#shtag").click(function(){$("#tagTip").toggle();});
	$("#close").click(function(){$("#tagTip").hide();});
    $("ul.tag span.t2").click(function(){ 
       insertAtCaret($("#html").get(0),$(this).text()); 
    }); 
	$("#tips .partition").click(function(){
		$("#tips .tipsblock").toggle();
		$("ul.tag").hide();
	});
    $("div.head").click(function(){
    	var _this=$(this);
		$(".tag").each(function(i){
	    	if($(this).css("display")!="none" && this.id!=_this.next("ul").attr("id")){
	    		$(this).slideToggle("slow");
	    	}
		}); 
		_this.next("ul").slideToggle("slow");
    });
});
 </script>
<div class="container" id="cpcontainer">
  <div id="tagTip" style="width:310px; background-color:#FFFFFF; border:#CCCCCC 1px solid;">
    <table class="tb tb2" id="tips">
      <tr>
        <th colspan="15" class="partition"><span style="float:right;margin-top:4px;" id="close"><img src="admin/images/close.gif" /></span>模板标签</th>
      </tr>
      <tr>
        <td class="tipsblock" style="display:none;"><ul id="tipslis">
            <li>
              <div class="head">站点信息标签：<span class="t2">&lt;!--{$site.属性}--&gt;</span><br />
                适用范围:所有模板</div>
              <ul class="tag" id="site">
                <li> 网站名称：<span class="t2">&lt;!--{$site.title}--&gt;</span></li>
                <li> 网站网址：<span class="t2">&lt;!--{$site.url}--&gt;</span></li>
                <li> 首页文件名：<span class="t2">&lt;!--{$site.index}--&gt;</span></li>
                <li> 程序目录：<span class="t2">&lt;!--{$site.dir}--&gt; </span></li>
                <li> 模板目录名：<span class="t2">&lt;!--{$site.tpl}--&gt;</span></li>
                <li> 模板路径：<span class="t2">&lt;!--{$site.tplpath}--&gt;</span></li>
                <li> 关键字：<span class="t2">&lt;!--{$site.keywords}--&gt;</span></li>
                <li> 网站描述：<span class="t2">&lt;!--{$site.description}--&gt;</span></li>
                <li> ICP备案号：<span class="t2">&lt;!--{$site.icp}--&gt;</span></li>
                <li> 站长信箱：<span class="t2">&lt;!--{$site.email}--&gt;</span></li>
              </ul>
            </li>
            <li>
              <div class="head">栏目标签：<span class="t2">&lt;!--{$sort.属性}--&gt;</span><br />
                适用范围:频道页模板,栏目列表模板,内容查看页模板</div>
              <ul class="tag" id="sort">
                <li> 栏目ID：<span class="t2">&lt;!--{$sort.id}--&gt;</span></li>
                <li> 栏目名：<span class="t2">&lt;!--{$sort.name}--&gt;</span></li>
                <li> 栏目链接：<span class="t2">&lt;!--{$sort.url}--&gt;</span></li>
                <li> 栏目链接(带栏目名)：<span class="t2">&lt;!--{$sort.link}--&gt;</span></li>
                <li> 栏目关键字：<span class="t2">&lt;!--{$sort.keywords}--&gt;</span></li>
                <li> 栏目简介：<span class="t2">&lt;!--{$sort.description}--&gt;</span></li>
                <li>当前位置：<span class="t2">&lt;!--{$nav}--&gt;</span></li>
              </ul>
            </li>
            <li>
              <div class="head">文章标签：<span class="t2">&lt;!--{$标记名称}--&gt;</span><br />
                适用范围:内容查看页模板</div>
              <ul class="tag" id="art">
                <li> ID：<span class="t2">&lt;!--{$id}--&gt;</span></li>
                <li> 缩略图：<span class="t2">&lt;!--{$pic}--&gt;</span></li>
                <li> 标题：<span class="t2">&lt;!--{$title}--&gt;</span></li>
                <li> 关键字：<span class="t2">&lt;!--{$keywords}--&gt;</span></li>
                <li> 简介：<span class="t2">&lt;!--{$description}--&gt;</span></li>
                <li> 来源：<span class="t2">&lt;!--{$source}--&gt;</span></li>
                <li> 作者：<span class="t2">&lt;!--{$author}--&gt;</span></li>
                <li> 发布日期:<span class="t2">&lt;!--{$pubdate|date}--&gt;</span></li>
                <li> 标签：<span class="t2">&lt;!--{$tags}--&gt;</span></li>
                <li> 正文：<span class="t2">&lt;!--{$body}--&gt;</span></li>
                <li> 正文分页列表：<span class="t2"> &lt;!--{$pagebreak}--&gt;</span></li>
                <li> 点击数：<span class="t2">&lt;!--{$hits}--&gt;</span></li>
                <li> 评论数：<span class="t2">&lt;!--{$comments}--&gt;</span></li>
                <li> DIGG数：<span class="t2">&lt;!--{$digg}--&gt;</span></li>
                <li>上一篇：<span class="t2">&lt;!--{$show.prev}--&gt;</span></li>
                <li>下一篇：<span class="t2">&lt;!--{$show.next}--&gt;</span></li>
              </ul>
            </li>
            <li>
              <div class="head">栏目列表：<span class="t2">&lt;!--{iCMS:catalog}--&gt;</span><br />
                适用范围:所有模板</div>
              <ul class="tag" id="catalog">
                <li> <span class="t2">&lt;!--{iCMS:catalog loop='true'}--&gt;</span></li>
                <li>栏目ID：<span class="t2">&lt;!--{$catalog.id}--&gt;</span></li>
                <li>栏目名：<span class="t2">&lt;!--{$catalog.name}--&gt;</span></li>
                <li>栏目链接：<span class="t2">&lt;!--{$catalog.url}--&gt;</span></li>
                <li>栏目文章数：<span class="t2">&lt;!--{$catalog.count}--&gt;</span></li>
                <li>栏目链接2：<span class="t2">&lt;!--{$catalog.link}--&gt;</span></li>
                <li>当无值时被执行：<span class="t2">&lt;!--{iCMSelse}--&gt;</span></li>
                <li>结束标签：<span class="t2">&lt;!--{/iCMS}--&gt;</span></li>
              </ul>
            </li>
            <li><div class="head">文章列表：<span class="t2">&lt;!--{iCMS:list}--&gt;</span><br />适用范围:所有模板</div>
			<ul class="tag" id="list">
			  <li><span class="t2">&lt;!--{iCMS:list loop='true'}--&gt;</span></li>
			  <li>ID: <span class="t2">&lt;!--{$list.id}--&gt;</span></li>
			  <li>缩略图: <span class="t2">&lt;!--{$list.pic}--&gt;</span></li>
			  <li>标题: <span class="t2">&lt;!--{$list.title}--&gt;</span></li>
			  <li>文章链接: <span class="t2">&lt;!--{$list.url}--&gt;</span></li>
			  <li>文章链接: <span class="t2">&lt;!--{$list.link}--&gt;</span>(带标题)</li>
			  <li>关键字: <span class="t2">&lt;!--{$list.keywords}--&gt;</span></li>
			  <li>简介: <span class="t2">&lt;!--{$list.description}--&gt;</span></li>
			  <li>来源: <span class="t2">&lt;!--{$list.source}--&gt;</span></li>
			  <li>作者: <span class="t2">&lt;!--{$list.author}--&gt;</span></li>
			  <li>发布日期:<span class="t2">&lt;!--{$list.pubdate|date}--&gt;</span></li>
			  <li>点击数: <span class="t2">&lt;!--{$list.hits}--&gt;</span></li>
			  <li>Digg数: <span class="t2">&lt;!--{$list.digg}--&gt;</span></li>
			  <li>评论数: <span class="t2">&lt;!--{$list.comments}--&gt;</span></li>
			  <li>栏目链接: <span class="t2">&lt;!--{$list.sort.url}--&gt;</span></li>
			  <li>栏目名: <span class="t2">&lt;!--{$list.sort.name}--&gt;</span></li>
			  <li>栏目链接: <span class="t2">&lt;!--{$list.sort.link}--&gt;</span></li>
			  <li>当无值时被执行: <span class="t2">&lt;!--{iCMSelse}--&gt;</span> </li>
			  <li> 结束标签: <span class="t2">&lt;!--{/iCMS}--&gt;</span></li>
			  <li>分页:<span class="t2">&lt;!--{$pagenav}--&gt;</span> page='yes'时可用</li>
			</ul>
            </li>
          </ul></td>
         <tr class="nobg">
        <td class="vtop rowform" style="text-align:right"><a href="http://www.idreamsoft.cn/doc/iCMS" target="_blank">详细模板标签说明</a></td></tr>
      </tr>
    </table>
  </div>
  <form name="cpform" method="post" action="<?=__SELF__?>?do=template&operation=post" id="cpform" >
    <table class="tb tb2 ">
      <tr>
        <th class="partition"><span style="float:right;margin-top:4px;" id="shtag">模板标签</span>编辑模板
          <input name="action" type="hidden" value="edit" />
          <input name="tplpath" type="hidden" value="<?=$_GET['path']?>" />
          <input type="submit" value="保存" class="btn" />
          <!--input type="button" value="预览" class="btn" id="preview"/--></th>
      </tr>
      <tr>
        <td class="vtop rowform" style="width:100%;"><textarea name="html" id="html" class="tarea" style='width:100%;height:410px;font-family: "Courier New", Courier, monospace;font-size: 14px;line-height: 140%;'><?=dhtmlspecialchars($FileData)?></textarea></td>
      </tr>
    </table>
  </form>
</div>
</body></html>