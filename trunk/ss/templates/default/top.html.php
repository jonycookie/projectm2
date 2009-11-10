<?exit?>
<!--{template header}-->
</div>

<div id="nav">
	<div class="main_nav">
		<ul>
			<!--{if empty($_SCONFIG['defaultchannel'])}-->
			<li><a href="{S_URL}/index.php">首页</a></li>
			<!--{/if}-->
			<!--{loop $channels['menus'] $key $value}-->
			<li<!--{if $key == $channel }--> class="current"<!--{/if}-->><a href="$value[url]">$value[name]</a></li>
			<!--{/loop}-->
		</ul>
	</div>
</div><!--nav end-->

<div class="column">

<!--{if $groupid}-->

	<div id="mood_banner">
		<!--{if $clickgroup[icon]}-->
			<img src="images/click/$clickgroup[icon]" alt="$clickgroup[grouptitle]排行榜">
			<div class="show_toplist">
				<em><a href="javascript:contributeop('top_op');">&gt;&gt;查看其他排行榜</a></em>
				<div style="display: none;" id="top_op">
					<a href="#action/top/#">热度排行榜</a>
					<!--{loop $clickgroups $value}-->
					<a href="#action/top/groupid/$value[groupid]#">$value[grouptitle]排行榜</a>
					<!--{/loop}-->
				</div>
			</div>
		<!--{else}-->
			<div id="top_rank_caption">
				<h3>$clickgroup[grouptitle]排行榜</h3>
				<div class="other_top">
					<em><a href="javascript:contributeop('top_op');">查看其他排行榜</a></em>
					<div style="display: none;" id="top_op">
						<a href="#action/top/#">热度排行榜</a>
						<!--{loop $clickgroups $value}-->
						<a href="#action/top/groupid/$value[groupid]#">$value[grouptitle]排行榜</a>
						<!--{/loop}-->
					</div>
				</div>
			</div>
		<!--{/if}-->
	</div>
	
	<div id="mood_top">
		<!--{loop $click $v}-->
		<!--{if $clickgroup[block]!='spacecomment'}-->
		<!--{block name="$clickgroup[block]" parameter="dateline/86400/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"}-->
		<!--{else}-->
		<!--{block name="$clickgroup[block]" parameter="dateline/86400/order/click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"}-->
		<!--{/if}-->
		<div class="global_module">
			<div class="global_module2_caption"><!--{if $v[icon]}--><img src="images/click/$v[icon]" alt="$v[name]"><!--{/if}--><h3>$v[name]</h3><span class="rank_catalog">日排行</span></div>
				<ul class="global_tx_list1">
				<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
				<li><span class="box_r"><!--{echo $value['click_'.$v['clickid']];}-->票</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{if $clickgroup[block]!='spacecomment'}-->
		<!--{block name="$clickgroup[block]" parameter="dateline/604800/order/i.click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"}-->
		<!--{else}-->
		<!--{block name="$clickgroup[block]" parameter="dateline/604800/order/click_$v[clickid] DESC/limit/0,6/cachetime/4800/subjectlen/60/subjectdot/0/cachename/click_$v[clickid]"}-->
		<!--{/if}-->
		<div class="global_module right_fix">
			<div class="global_module2_caption"><!--{if $v[icon]}--><img src="images/click/$v[icon]" alt="$v[name]"><!--{/if}--><h3>$v[name]</h3><span class="rank_catalog">周排行</span></div>
				<ul class="global_tx_list1">
				<!--{loop $_SBLOCK['click_'.$v[clickid]] $value}-->
				<li><span class="box_r"><!--{echo $value['click_'.$v['clickid']];}-->票</span><a href="$value[url]" title="$value[subjectall]">$value[subject]</a></li>
				<!--{/loop}-->
			</ul>
		</div>
		<!--{/loop}-->
	</div><!--mood_top end-->

<!--{else}-->

	<div id="top_rank">
		<div id="top_rank_caption">
			<h3>热度排行榜</h3>
			<ul>
				<li{$timearr[0]}><a href="#action/top#"><span>全部</span></a></li>
				<li{$timearr[2]}><a href="#action/top/time/2#"><span>2小时内</span></a></li>
				<li{$timearr[4]}><a href="#action/top/time/4#"><span>4小时内</span></a></li>
				<li{$timearr[8]}><a href="#action/top/time/8#"><span>8小时内</span></a></li>
				<li{$timearr[24]}><a href="#action/top/time/24#"><span>24小时内</span></a></li>
				<li{$timearr[168]}><a href="#action/top/time/168#"><span>一周内</span></a></li>
			</ul>
			<div class="other_top">
				<em><a href="javascript:contributeop('top_op');">查看其他排行榜</a></em>
				<div style="display: none;" id="top_op">
					<a href="#action/top/#">热度排行榜</a>
					<!--{loop $clickgroups $value}-->
					<a href="#action/top/groupid/$value[groupid]#">$value[grouptitle]排行榜</a>
					<!--{/loop}-->
				</div>
			</div>
		</div>
		<table>
			<tbody>
				<tr class="top_rank_2caption">
					<td width="40">排名</td>
					<td width="510">标题</td>
					<td width="100">类别</td>
					<td width="130">时间</td>
					<td>热度</td>
				</tr>
				<!--{if $list}-->
				<!--{loop $list $value}-->
				<tr>
					<td>$value[i]</td>
					<td><a href="#action/viewnews/itemid/$value[itemid]#">$value[subject]</a></td>
					<td class="color_gray">
						[<a href="#action/$value[type]#" class="color_gray">$channels[menus][$value[type]][name]</a>] 
						<a href="#action/category/catid/$value[catid]#">$_SGLOBAL[category][$value[catid]][name]</a></td>
					<td class="color_gray">#date('Y-m-d H:i', $value[dateline])#</td>
					<td class="color_brown">$value[hot]票</td>
				</tr>
				<!--{/loop}-->
				<!--{else}-->
				<tr><td colspan="5"><div class="user_no_body">没有符合条件的信息</div></td></tr>
				<!--{/if}-->
			</tbody>
		</table>
	</div><!--top_rank end-->

<!--{/if}-->

</div>

<script type="text/javascript">
function contributeop(id) {
	if($(id).style.display != 'block') {
		$(id).style.display = 'block';
	} else {
		$(id).style.display = 'none';
	}	
}
</script>
<!--{template footer}-->