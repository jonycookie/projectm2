<?exit?>
<!--{eval include template($tpldir.'/header.html.php', 1);}-->
<!--{eval $ads3 = getad('system', $modelsinfoarr[modelname], '3'); }-->
<!--{if !empty($ads3['pageheadad']) }-->
<div class="ad_header">$ads3[pageheadad]</div>
<!--{/if}-->
</div>

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/index.php">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li <!--{if $key == $modelsinfoarr['modelname'] }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{if !empty($categories)}-->
	<ul class="ext_nav clearfix">
		<!--{eval $dot = '|'}-->
		<!--{eval $total = count($categories)}-->
		<!--{eval $i = 1;}-->
		<!--{loop $categories $key $value}-->
		 <li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_catid=$key" title="$value">$value</a><!--{if $total != $i}--> $dot <!--{/if}--></li>
		<!--{eval $i++;}-->
		<!--{/loop}-->
	</ul>
	<!--{/if}-->
</div>

<div class="column">
	<div class="col1">
		<!--{if !empty($ads3['pagecenterad'])}-->
		<div class="ad_pagebody">$ads3['pagecenterad']</div>
		<!--{/if}-->

		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module3_caption">
			<h3>您的位置：<a href="{S_URL}/">$_SCONFIG[sitename]</a>
			<!--{loop $guidearr $value}-->
			&gt;&gt; <a href="$value[url]">$value[name]</a>
			<!--{/loop}-->
			&gt;&gt; 详细信息
			<!--{if $posturl}-->
			<a href="$posturl" title="在线投稿" class="btn_capiton_op" target="_blank">在线投稿</a>
			<!--{/if}-->
			</h3></div>

			<div id="article">
				<h1>$item[subject]</h1>
				<p id="article_extinfo">
				发布: #date('Y-n-d H:i', $item["dateline"])#&nbsp;|&nbsp;
				作者: <!--{if $item[uid]}--><a href="#uid/$item[uid]#">$item[username]</a><!--{else}-->游客<!--{/if}-->&nbsp;|&nbsp;
				查看: $item[viewnum]次
				</p>

				<div id="article_body" class="job_box">
					<!--{if !empty($item[subjectimage])}-->
						<a href="$item[subjectimage]" target="_blank"><img src="$item[subjectimage]" align="left" class="img_max300"/></a>
					<!--{/if}-->
                    <!--{if !empty($ads3[viewinad])}-->
					<div class="ad_article">
						$ads3[viewinad]
					</div>
					<!--{/if}-->
					<div>$item[message]</div>

					<!--{if !empty($columnsinfoarr[fixed])}-->
					<div class="job_requ">
						<ul>
						<!--{loop $columnsinfoarr[fixed] $ckey $cvalue}-->
							<li>
								<em>$cvalue[fieldcomment]: </em>
							<!--{if !is_array($cvalue[value])}-->
								<!--{if $cvalue[formtype]=='textarea' }-->
								$cvalue[value]
								<!--{elseif $cvalue[formtype]=='timestamp'}-->
								#date("m月d日 H:i", $cvalue[value])#
								<!--{else}-->
								<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$cvalue[fieldname]=<!--{eval echo rawurlencode($cvalue[value]);}-->">$cvalue[value]</a>
								<!--{/if}-->
							<!--{else}-->
								<!--{loop $cvalue[value] $dkey $dvalue}-->
									<a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$cvalue[fieldname]=<!--{eval echo rawurlencode($dvalue);}-->">$dvalue</a>&nbsp;
								<!--{/loop}-->
							<!--{/if}-->
							</li>
						<!--{/loop}-->
						</ul>
					</div>
					<!--{/if}-->

					<!--{if !empty($moreurl)}-->
					<div class="more"><a href="$moreurl">查看详情</a>	</div>
					<!--{/if}-->

					<!--{if !empty($columnsinfoarr[message])}-->
					<!--{loop $columnsinfoarr[message] $ckey $cvalue}-->
					<!--{if !empty($cvalue[isflash])}-->
					<div class="media img_max400">
						<h5>$cvalue[fieldcomment]：</h5>
						<div>
						<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="400" height="300">
						<param name="movie" value="$cvalue[filepath]" />
						<param name="quality" value="high" />
						<embed src="$cvalue[filepath]" type="application/x-shockwave-flash" pluginspage=" http://www.macromedia.com/go/getflashplayer" width="400" height="300"/>
						</object>
						</div>
					</div>
					<!--{elseif !empty($cvalue[isfile])}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div><a href="$siteurl/batch.modeldownload.php?hash=$cvalue[filepath]">下载</a></div>
					</div>
					<!--{elseif !empty($cvalue[isimage])}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div><a href="$cvalue[filepath]" target="_blank"><img src="$cvalue[filepath]" /></a></div>
					</div>
					<!--{else}-->
					<div class="media">
						<h5>$cvalue[fieldcomment]：</h5>
						<div>
							<!--{if !is_array($cvalue[value])}-->
								$cvalue[value]
							<!--{else}-->
								<!--{loop $cvalue[value] $dkey $dvalue}-->
									$dvalue&nbsp;
								<!--{/loop}-->
							<!--{/if}-->
						</div>
					</div>
					<!--{/if}-->
					<!--{/loop}-->
					<!--{/if}-->

				</div>
			</div><!--article end-->

			<!--{if !empty($modelsinfoarr[allowrate])}-->
			<div id="top_btn">
            	<strong id="modelrate">$item[rates]</strong><a href="javascript:;" onclick="setModelRate('$modelsinfoarr[modelname]', '$item[itemid]');">顶一下</a>
            </div>
			<!--{/if}-->
			<div id="article_op"><a href="javascript:doPrint();">打印</a> | <a href="javascript:;" onclick="bookmarksite(document.title, window.location.href);">收藏此页</a></div>

			<!--{if !empty($modelsinfoarr[allowcomment]) && !empty($item[allowreply])}-->
			<div class="comment">
				<!--{if !empty($commentlist)}-->
				<!--{loop $commentlist $value}-->
				<div class="comment_list">
					<div class="comment_list_caption">
						<div class="box_l"><!--{if empty($value[authorid])}-->$value[author]<!--{else}--><a href="{S_URL}/space.php?uid=$value[authorid]" class="author">$value[author]</a><!--{/if}--> (#date("Y-n-d H:i:s", $value["dateline"])#)</div>
						<div class="box_r">
						<!--{if !empty($value[authorid]) && $value[authorid] == $_SGLOBAL['supe_uid'] || $_SGLOBAL['member']['groupid'] == 1}-->
						<a href="#action/modelcomment/name/$modelsinfoarr[modelname]/itemid/$value[itemid]/cid/$value[cid]/op/delete/php/1#">删除</a> |
						<!--{/if}-->
						 <a href="javascript:;" onclick="getModelQuote('$modelsinfoarr[modelname]', '$value[cid]')">引用</a>
						</div>
					</div>
					<!--{if !empty($value['message'])}-->
					<div class="comment_content">$value[message]</div>
					<!--{/if}-->
				</div>
				<!--{/loop}-->
				<!--{/if}-->
			</div><!--comment end-->

			<div id="sign_msg">
				<form  action="#action/modelcomment/itemid/$item[itemid]/name/$modelsinfoarr[modelname]/php/1#" method="post">
				<script language="javascript" type="text/javascript" src="{S_URL}/batch.formhash.php?rand={eval echo rand(1, 999999)}"/></script>
				<fieldset>
				<legend>发表评论</legend>
				<textarea id="messagecomm" name="messagecomm" onfocus="showcode()" onkeydown="ctlent(event,'postcomm');"></textarea><br />
				<!--{if empty($_SCONFIG['noseccode'])}-->
				<div class="security_code">
					<label for="seccode">验证码：</label><input type="text" id="seccode" name="seccode" maxlength="4" style="width:85px;" /> <img id="xspace-imgseccode" src="{S_URL}/do.php?action=seccode" onclick="javascript:newseccode(this);" alt="seccode" title="看不清？点击换一个" /> <a class="c_blue" title="看不清？点击换一个" href="javascript:newseccode($('xspace-imgseccode'));">换一个</a>
				</div>
				<!--{/if}-->

				<!--{if $_SGLOBAL['supe_uid']&&$_SCONFIG['allowfeed']}-->
				<div id="add_event_box"><label for="add_event">加入事件</label>
				<input type="checkbox" name="addfeed" $addfeedcheck>
				</div>
				<!--{/if}-->
				<input type="submit" value="提交" id="submit" class="input_search"/>
				<input type="hidden" value="submit" name="submitcomm" />
				<input type="hidden" id="itemid" name="itemid" value="$item[itemid]" />
				</fieldset>
				</form>
			</div><!--sign_msg end-->
            <div id="comment_op"><a href="#action/modelcomment/itemid/$item[itemid]/name/$modelsinfoarr[modelname]#" class="view" target="_blank">查看全部回复</a><span>【已有$item[replynum]位网友发表了看法】</span></div>
			<!--{/if}-->

		</div>
	</div>

	<div class="col2">
		<div id="user_login">
			<script src="{S_URL}/batch.panel.php?rand={eval echo rand(1, 999999)}" type="text/javascript" language="javascript"></script>
		</div><!--user_login end-->
		<!--{if !empty($childcategories)}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>$cacheinfo[categoryarr][$_GET[mo_catid]][name]</h3></div>
			<ul class="global_tx_list3">
				<!--{loop $childcategories $value}-->
				<li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_catid=$value[catid]">$value[name]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{if !empty($gatherarr)}-->
		<!--{loop $gatherarr $key $value}-->
		<!--{if !empty($value)}-->
		<div class="global_module margin_bot10 bg_fff">
			<div class="global_module2_caption"><h3>$cacheinfo[columns][$key][fieldcomment]</h3></div>
			<ul class="ext_li_short clearfix">
				<!--{loop $value $tmpvalue}-->
					<li><a href="$siteurl/m.php?name=$modelsinfoarr[modelname]&mo_$key=<!--{eval echo rawurlencode($tmpvalue);}-->">$tmpvalue</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/if}-->
		<!--{/loop}-->
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

<script language="javascript" type="text/javascript">
<!--
	addMediaAction('article_body');
	addImgLink("article_body");
//-->
</script>

<div class="clear"></div>
<!--{if !empty($ads3['pagefootad'])}-->
<div class="ad_pagebody">
$ads3[pagefootad]
</div>
<!--{/if}-->

<!--{if !empty($ads3['pagemovead']) || !empty($ads3['pageoutad'])}-->
<!--{if !empty($ads3['pagemovead'])}-->
<div id="coupleBannerAdv" style="z-index: 10; position: absolute; width:100px;left:10px;top:10px;display:none">
	<div style="position: absolute; left: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');" />
	</div>
	<div style="position: absolute; right: 6px; top: 6px;">
		$ads3[pagemovead]
		<br />
		<img src="{S_URL}/images/base/advclose.gif" onMouseOver="this.style.cursor='hand'" onClick="closeBanner('coupleBannerAdv');" />
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
