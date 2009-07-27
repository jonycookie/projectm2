<?exit?>
<!--{template header}-->
<!--{eval $ads3 = getad('system', 'news', '3'); }-->
<!--{if !empty($ads3['pageheadad']) }-->
<div class="ad_header">$ads3[pageheadad]</div>
<!--{/if}-->
</div><!--header end-->

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/index.php">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == 'news' }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{block name="category" parameter="type/news/isroot/1/order/c.displayorder/limit/0,100/cachetime/80800/cachename/category"}-->
	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($_SBLOCK['category'])}-->
		<!--{eval $i = 1;}-->
		<!--{loop $_SBLOCK['category'] $value}-->
		<li><a href="$value[url]">$value[name]</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
</div><!--nav end-->

<div class="column">
	<div class="col1">
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption"><h3>你的位置：<a href="{S_URL}">$_SCONFIG[sitename]</a>
				<!--{loop $guidearr $value}-->
				&gt;&gt; <a href="$value[url]">$value[name]</a>
				<!--{/loop}-->
				&gt;&gt; 详细内容
				<a href="{S_URL}/admincp.php?action=spacenews&op=add" title="在线投稿" class="btn_capiton_op btn_capiton_op_r40" target="_blank">在线投稿</a>
				</h3>
			</div>
			<div id="article">
				<h1>$news[subject]</h1>
				<p id="article_extinfo">发布: #date('Y-n-d H:i', $news["dateline"])#&nbsp;|&nbsp;&nbsp;作者: <a href="{S_URL}/space.php?uid=$news[uid]&op=news">$news[newsauthor]</a>&nbsp;|&nbsp;&nbsp;  
				<!--{if !empty($news['newsfrom'])}-->
				来源: <!--{if !empty($news[newsfromurl])}--><a href="$news[newsfromurl]" target="_blank" title="$news[newsfrom]">$news[newsfrom]</a><!--{else}-->$news[newsfrom]<!--{/if}-->&nbsp;|&nbsp;<!--{/if}-->
				查看: $news[viewnum]次</p>
				<div id="article_body">
					<!--{if !empty($news[custom][name])}-->
					<div id="article_summary">
						<!--{loop $news[custom][key] $ckey $cvalue}-->
						<h6>$news[custom][name]</h6>
						<p>$cvalue[name]:$news[custom][value][$ckey]</p>
						<!--{/loop}-->
					</div>
					<!--{/if}-->
					<!--{if !empty($ads3[viewinad])}-->
					<div class="ad_article">
						$ads3[viewinad]
					</div>
					<!--{/if}-->
					$news[message]
					<!--{if empty($multipage)}-->
					<!--{loop $news['attacharr'] $attach}-->
					<!--{if $attach['isimage']}-->
					<p class="article_download"><a href="$attach[url]" target="_blank"><img src="$attach[thumbpath]" alt="$attach[subject]" /><span>$attach[subject]</span></a></p>
					<!--{else}-->
					<p class="article_download"><a href="$attach[url]" target="_blank">$attach[filename]</a>(<!--{eval echo formatsize($attach[size]);}-->)</p>
					<!--{/if}-->
					<!--{/loop}-->
					<!--{/if}-->
				</div>
			</div><!--article end-->

			<!--{if !empty($relativetagarr)}-->
			<div id="article_tag">
				<strong>TAG:</strong> 
				<!--{loop $relativetagarr $value}-->
				<!--{eval $svalue = rawurlencode($value);}-->
				<a href="#action/tag/tagname/$svalue#">$value</a>
				<!--{/loop}-->
			</div>
			<!--{/if}-->

			<!--{if $multipage}-->
				$multipage
			<!--{/if}-->

			<div id="article_op">
	
				<a href="javascript:doPrint();">打印</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="bookmarksite(document.title, window.location.href)">收藏此页</a>&nbsp;|&nbsp;
				<a href="javascript:;" onclick="showajaxdiv('{S_URL}/batch.common.php?action=emailfriend&amp;itemid=$news[itemid]', 400);">推荐给好友</a>&nbsp;|&nbsp;<a href="javascript:;" onclick="report($news[itemid])" >举报</a>
			</div>
			
			<div id="article_pn"><a class="box_l" href="{S_URL}/batch.common.php?action=viewnews&amp;op=up&amp;itemid=$news[itemid]&amp;catid=$news[catid]">上一篇</a> <a class="box_r" href="{S_URL}/batch.common.php?action=viewnews&amp;op=down&amp;itemid=$news[itemid]&amp;catid=$news[catid]">下一篇</a></div>

			<div class="comment">
				<!--{if !empty($commentlist)}-->
				<!--{loop $commentlist $value}-->
				<div class="comment_list">
					<div class="comment_list_caption">
						<div class="box_l">
						<!--{if empty($value[authorid])}-->$value[author]<!--{else}--><a href="{S_URL}/space.php?uid=$value[authorid]" class="author">$value[author]</a><!--{/if}--> (#date("Y-n-d H:i:s", $value["dateline"])#</strong>)
						</div>
						<div class="box_r">
						<!--{if !empty($value[authorid]) && $value[authorid] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1}-->
						<a href="#action/viewcomment/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1#">删除</a> | 
						<!--{/if}--><a href="javascript:;" onclick="getQuote($value[cid])">引用</a>
						</div>
					</div>
					<!--{if !empty($value['message'])}-->
					<div class="comment_content">
						$value[message]
					</div>
					<!--{else}-->
					<div class="scoresnum">评$value[rates]分</div>
					<!--{/if}-->
				</div>
				<!--{/loop}-->
				<!--{/if}-->
			</div><!--comment end-->

			<div class="block" id="xspace-rates">
				<div id="xspace-rates-bg">
					<div id="xspace-rates-star" class="xspace-rates0">&nbsp;</div>
					<div id="xspace-rates-a">
						<a href="javascript:;" onmouseover="rateHover(-5);" onmouseout="rateOut();" onclick="setRateXML('-5', '$news[itemid]');">-5</a>
						<a href="javascript:;" onmouseover="rateHover(-3);" onmouseout="rateOut();" onclick="setRateXML('-3', '$news[itemid]');">-3</a>
						<a href="javascript:;" onmouseover="rateHover(-1);" onmouseout="rateOut();" onclick="setRateXML('-1', '$news[itemid]');">-1</a>
						<a href="javascript:;" onmouseover="rateHover(0);" onmouseout="rateOut();" onclick="setRateXML('0', '$news[itemid]');">-</a>
						<a href="javascript:;" onmouseover="rateHover(1);" onmouseout="rateOut();" onclick="setRateXML('1', '$news[itemid]');">+1</a>
						<a href="javascript:;" onmouseover="rateHover(3);" onmouseout="rateOut();" onclick="setRateXML('3', '$news[itemid]');">+3</a>
						<a href="javascript:;" onmouseover="rateHover(5);" onmouseout="rateOut();" onclick="setRateXML('5', '$news[itemid]');">+5</a>
					</div>
					<input type="hidden" id="xspace-rates-value" name="rates" value="0" />
				</div>
				<p>评分：<span id="xspace-rates-tip">0</span></p>
			</div>

			<div id="sign_msg">
				<form  action="#action/viewcomment/itemid/$news[itemid]/php/1#" method="post">
				<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
				<fieldset>
				<legend>发表评论</legend>
				<textarea id="message" name="message" onfocus="showcode()" onkeydown="ctlent(event,'postcomm');"></textarea><br />
				<!--{if empty($_SCONFIG['noseccode'])}-->
				<div class="security_code">
					<label for="seccode">验证码：</label><input type="text" id="seccode" name="seccode" maxlength="4" style="width:85px;" /> <img id="xspace-imgseccode" src="{S_URL}/do.php?action=seccode" onclick="javascript:newseccode(this);" alt="seccode" title="看不清？点击换一个" /> <a class="c_blue" title="看不清？点击换一个" href="javascript:newseccode($('xspace-imgseccode'));">换一个</a>
				</div>
				<!--{/if}-->
				
				<!--{if $_SGLOBAL['supe_uid']&&$_SCONFIG['allowfeed']}-->
				<div id="add_event_box"><label for="add_event">加入事件</label>
				<input type="checkbox" name="addfeed" <!--{if ($_SCONFIG['customaddfeed']&2)}-->checked="checked"<!--{/if}-->>		
				</div>
				<!--{/if}-->
				<input type="submit" value="提交" id="submit" class="input_search"/>
				<input type="hidden" value="submit" name="submitcomm" />
				<input type="hidden" id="itemid" name="itemid" value="$news[itemid]" />
				</fieldset>
				</form>
			</div><!--sign_msg end-->

			<div id="comment_op"><a href="#action/viewcomment/itemid/$news[itemid]#" class="view" target="_blank">查看全部回复</a><span>【已有$news[replynum]位网友发表了看法】</span></div>
		</div>
		<!--{if !empty($ads3['pagecenterad'])}-->
		<div class="ad_mainbody">$ads3[pagecenterad]</div>
		<!--{/if}-->
		<!--图文资讯显示-->
		<!--{block name="spacenews" parameter="haveattach/2/showattach/1/order/i.lastpost DESC/limit/0,12/subjectlen/14/subjectdot/0/cachetime/8000/cachename/picnews"}-->
		<!--{if $_SBLOCK['picnews']}-->
		<div class="global_module margin_bot10">
			<div class="global_module1_caption"><h3>图文资讯</h3></div>
			<ul class="globalnews_piclist clearfix">
				<!--{loop $_SBLOCK['picnews'] $value}-->
				<li><a href="$value[url]" title="$value[subjectall]"><img src="$value[a_thumbpath]" alt="$value[subjectall]" /></a><span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></span></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
	</div><!--col1 end-->

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		

		<!--{block name="spacenews" parameter="catid/$thecat[subcatid]/order/i.dateline DESC/limit/0,10/subjectlen/26/subjectdot/0/cachetime/13800/cachename/newnews"}-->
		<!--{if !empty($_SBLOCK['newnews'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>最新报道</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['newnews'] $value}-->
				<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->

		<!--{block name="spacenews" parameter="dateline/2592000/digest/1,2,3/order/i.viewnum DESC,i.dateline DESC/limit/0,20/cachetime/89877/subjectlen/30/subjectdot/0/cachename/hotnews2"}-->
		<!--{if $_SBLOCK['hotnews2']}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>精彩推荐</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $_SBLOCK['hotnews2'] $value}-->
					<li><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->

		<!--相关资讯-->
		<!--{if !empty($news[relativeitemids])}-->
		<!--{block name="spacenews" parameter="itemid/$news[relativeitemids]/order/i.dateline DESC/limit/0,20/cachetime/17680/cachename/relativeitem/tpl/data"}-->
		<!--{if !empty($_SBLOCK['relativeitem']) }-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>相关资讯</h3></div>
			<ul class="global_tx_list3">
			<!--{loop $_SBLOCK['relativeitem'] $ikey $value}-->
			<li><span class="box_r">#date('m-d', $value['dateline'])#</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
			<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{/if}-->

		<!--{if !empty($ads3['siderad'])}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>网络资源</h3></div>
			<div class="ad_sidebar">
				$ads3[siderad]
			</div>

		</div>
		<!--{/if}-->

	</div><!--col2 end-->
</div><!--column end-->
<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">$ads3[pagefootad]</div>
<!--{/if}-->

<script language="javascript" type="text/javascript">
<!--
	addMediaAction('article_body');
	addImgLink("article_body");
//-->
</script>
<!--{if !empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])}-->
<!--{if !empty($ads3['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');">
	</div>
</div>
<!--{/if}-->
<!--{if !empty($ads3['pageoutad'])}-->
<div id="floatAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div id="floatFloor" style="position: absolute; right: 6px; bottom:-700px">
		$ads3[pageoutad]
	</div>
</div>
<!--{/if}-->
<script type="text/javascript" src="{S_URL}/include/js/floatadv.js"></script>
<script type="text/javascript">
<!--{if !empty($ads3['pageoutad'])}-->
var lengthobj = getWindowSize();
lsfloatdiv('floatAdv', 0, 0, 'floatFloor' , -lengthobj.winHeight).floatIt();
<!--{/if}-->
<!--{if !empty($ads3['pagemovead'])}-->
lsfloatdiv('coupleBannerAdv', 0, 0, '', 0).floatIt();
<!--{/if}-->
</script>
<!--{/if}-->
<!--{template footer}-->