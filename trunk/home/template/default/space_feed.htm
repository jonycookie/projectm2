<!--{eval $_TPL['titles'] = array('首页动态');}-->
<!--{template header}-->

<div id="content">

<!--{if $space[self]}-->
	<div class="composer_header">
	
		<div class="ar_r_t"><div class="ar_l_t"><div class="ar_r_b"><div class="ar_l_b"><img src="<!--{avatar($_SGLOBAL[supe_uid],middle)}-->" alt="{$_SN[$_SGLOBAL[supe_uid]]}" width="120" /></div></div></div></div>
		
		<div class="composer">
			<h3 class="index_name">
				<a href="space.php?uid=$space[uid]"<!--{eval g_color($space[groupid]);}-->>{$_SN[$space[uid]]}</a>
				<!--{eval g_icon($space[groupid]);}-->
			</h3>
			<p>
				已有 <a href="space.php?uid=$space[uid]&do=friend&view=visitor">$space[viewnum]</a> 人次访问, <a href="cp.php?ac=credit">$space[credit]</a>个积分 <a href="cp.php?ac=credit">$space[creditstar]</a>
			</p>
			<div class="current_status" id="mystate">
				<!--{if $space[mood]}--><a href="space.php?uid=$space[uid]&do=mood" title="同心情"><img src="image/face/{$space[mood]}.gif" alt="同心情" class="face" /></a> <!--{/if}-->
				<!--{if $space[spacenote]}-->
					$space[spacenote]
				<!--{elseif empty($space[mood])}-->
					您在做什么？
				<!--{/if}-->
				&nbsp;(<a href="javascript:;" onclick="mood_from();" title="更新状态">更新状态</a><span class="pipe">|</span><a href="space.php?uid=$space[uid]&do=mood">同心情</a>)
			</div>
			
			<ul class="u_setting">
				<li><a href="cp.php?ac=avatar">修改头像</a></li>
				<li><a href="cp.php?ac=profile">个人资料</a></li>
				<li><a href="cp.php?ac=password">账号设置</a></li>
				<li><a href="cp.php?ac=privacy">隐私筛选</a></li>
			</ul>
		</div>
	</div>
	
	<div class="mgs_list">
		<!--{if !empty($_SGLOBAL['member']['notenum'])}-->
		<div><img src="image/icon/notice.gif"><a href="space.php?do=notice"><strong>{$_SGLOBAL['member']['notenum']}</strong> 条新通知</a></div>
		<!--{/if}-->
		<!--{if $addfriendcount}--><div><img src="image/icon/friend.gif" alt="" /><a href="cp.php?ac=friend&op=request"><strong>$addfriendcount</strong> 个好友请求</a></div><!--{/if}-->
		<!--{if $mtaginvitecount}--><div><img src="image/icon/mtag.gif" alt="" /><a href="cp.php?ac=mtag&op=mtaginvite"><strong>$mtaginvitecount</strong> 个群组邀请</a></div><!--{/if}-->
		<!--{if $myinvitecount}--><div><img src="image/icon/userapp.gif" alt="" /><a href="space.php?do=notice&view=userapp"><strong>$myinvitecount</strong> 个应用消息</a></div><!--{/if}-->
		<!--{if !empty($_SGLOBAL['member']['newpm'])}--><div><img src="image/icon/pm.gif" alt="" /><a href="space.php?do=pm"><strong>{$_SGLOBAL[member][newpm]}</strong> 条新短消息</a></div><!--{/if}-->
		<!--{if $pokecount}--><div><img src="image/icon/poke.gif" alt="" /><a href="cp.php?ac=poke"><strong>$pokecount</strong> 个新招呼</a></div><!--{/if}-->
		<!--{if $newreport}--><div><img src="image/icon/report.gif" alt="" /><a href="admincp.php?ac=report"><strong>$newreport</strong> 个举报</a></div><!--{/if}-->
		<!--{if $namestatus}--><div><img src="image/icon/profile.gif" alt="" /><a href="admincp.php?ac=name&perpage=20&namestatus=0&searchsubmit=1"><strong>$namestatus</strong> 个待认证用户</a></div><!--{/if}-->
	</div>
	
	<div class="tabs_header" style="padding-top:10px;">
		
		<!--{if $_SCONFIG['my_status']}-->
		<ul class="tabs">
			<li id="viewall" onmouseover="showMenu(this.id)"{$my_actives[all]}><a href="$theurl&filter=all"><span>全部动态 <img src="image/tri.gif" alt="" /></span></a></li>
			<li id="viewsite" onmouseover="showMenu(this.id)"{$my_actives[site]}><a href="$theurl&filter=site"><span>站内 <img src="image/tri.gif" alt="" /></span></a></li>
			<li id="viewmyapp" onmouseover="showMenu(this.id)"{$my_actives[myapp]}><a href="$theurl&filter=myapp"><span>应用 <img src="image/tri.gif" alt="" /></span></a></li>
		</ul>
		<!--{else}-->
		<ul class="tabs">
			<li$actives[we]><a href="space.php?do=home&view=we"><span>好友的动态</span></a></li>
			<li$actives[all]><a href="space.php?do=home&view=all"><span>大家的</span></a></li>
			<li$actives[me]><a href="space.php?do=home&view=me"><span>自己的</span></a></li>
		</ul>
		<!--{/if}-->
	</div>
<!--{else}-->
<!--{eval 
	$_TPL['spacetitle'] = "动态";
}-->
	<!--{template space_menu}-->
<!--{/if}-->
	
	<div class="feed">
	
	<!--{if empty($_SCOOKIE['closefeedbox']) && $_SGLOBAL['ad']['feedbox']}-->
	<div id="feed_box" class="ye_r_t"><div class="ye_l_t"><div class="ye_r_b"><div class="ye_l_b">
		<div class="task_notice">
			<a title="忽略" class="float_cancel" href="javascript:;" onclick="close_feedbox();">忽略</a>
			<div class="task_notice_body">
			<!--{ad/feedbox}-->
			</div>
		</div>
	</div></div></div></div>
	<!--{/if}-->
	
	<!--{if $list}-->
		<div id="feed_div" class="enter-content">
		<!--{loop $list $day $values}-->
			<!--{if $day=='yesterday'}--><h4 class="feedtime">昨天</h4><!--{elseif $day!='today'}--><h4 class="feedtime">$day</h4><!--{/if}-->
			<ul>
			<!--{loop $values $value}-->
				<!--{template space_feed_li}-->
			<!--{/loop}-->
			</ul>
		<!--{/loop}-->
		</div>
		<!--{if $space['feedfriend'] && $count==$perpage}-->
		<div class="page" style="padding-top:20px;"><a href="javascript:;" onclick="feed_more();" id="a_feed_more">&gt;&gt; 查看更多动态</a></div>
		<div id="ajax_wait"></div>
		<!--{/if}-->
	<!--{else}-->
		<div class="c_form">
			还没有相关动态，<a href="space.php?do=home&view=all">去看看大家的动态</a>。
		</div>
	<!--{/if}-->
	</div>
</div>
<!--/content-->

<div id="sidebar">
	<!--{if $task}-->
	<div class="ye_r_t"><div class="ye_l_t"><div class="ye_r_b"><div class="ye_l_b">
		<div class="task_notice" style="width:230px;">
			<a title="忽略" class="float_cancel" href="cp.php?ac=task&taskid=$task[taskid]&op=ignore">忽略</a>
			<div class="task_notice_body">
				<img src="$task[image]" alt="" class="icon" />
				<h3><a href="cp.php?ac=task&op=do&taskid=$task[taskid]">$task[name]</a></h3>
				<p>可获得 <span class="num">$task[credit]</span> 积分</p>
			</div>
		</div>
	</div></div></div></div>
	<!--{/if}-->
	
	<!--{if $visitorlist}-->
	<div class="sidebox">
		<h2 class="title">
			<p class="r_option">
				<a href="space.php?uid=$space[uid]&do=friend&view=visitor">全部</a>
			</p>
			最近来访
		</h2>
		<ul class="avatar_list">
			<!--{loop $visitorlist $key $value}-->
			<li>
				<div class="avatar48"><a href="space.php?uid=$value[vuid]"><img src="<!--{avatar($value[vuid],small)}-->" alt="{$_SN[$value[vuid]]}" /></a></div>
				<p<!--{if $ols[$value[vuid]]}--> class="online_icon_p" title="在线"<!--{/if}-->><a href="space.php?uid=$value[vuid]" title="{$_SN[$value[vuid]]}">{$_SN[$value[vuid]]}</a></p>
				<p class="time"><!--{date('n月j日',$value[dateline],1)}--></p>
			</li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{/if}-->
	
	<!--{if $olfriendlist}-->
	<div class="sidebox">
		<h2 class="title">
			<p class="r_option">
				<a href="space.php?uid=$space[uid]&do=friend">全部</a>
			</p>
			我的好友
		</h2>
		<ul class="avatar_list">
			<!--{loop $olfriendlist $key $value}-->
			<li>
				<div class="avatar48"><a href="space.php?uid=$value[uid]"><img src="<!--{avatar($value[uid],small)}-->" alt="{$_SN[$value[uid]]}" /></a></div>
				<p<!--{if $value['isonline']}--> class="online_icon_p" title="在线"<!--{/if}-->><a href="space.php?uid=$value[uid]" title="{$_SN[$value[uid]]}">{$_SN[$value[uid]]}</a></p>
				<p class="time"><!--{if $value[lastactivity]}--><!--{date('H:i',$value[lastactivity],1)}--><!--{else}-->热度($value[num])<!--{/if}--></p>
			</li>
			<!--{/loop}-->
		</ul>
	</div>
	<!--{/if}-->
	
	<!--{if $birthlist}-->
	<div class="searchfirend">
		<div class="ye_r_t"><div class="ye_l_t"><div class="ye_r_b"><div class="ye_l_b">
			<h3>好友生日提醒</h3>
			<div class="box">
			<table cellpadding="2" cellspacing="4">
			<!--{loop $birthlist $key $values}-->
			<tr>
				<td align="right" valign="top" style="padding-left:10px;">
				<!--{if $values[0]['istoday']}-->今天<!--{else}-->{$values[0][birthmonth]}-{$values[0][birthday]}<!--{/if}-->
				</td>
				<td style="padding-left:10px;">
				<ul>
				<!--{loop $values $value}-->
				<li><a href="space.php?uid=$value[uid]">{$_SN[$value[uid]]}</a></li>
				<!--{/loop}-->
				</ul>
				</td>
			</tr>
			<!--{/loop}-->
			</table>
			</div>
		</div></div></div></div>
	</div>
	<!--{/if}-->
	
	<div class="searchfirend">
		<div class="ye_r_t"><div class="ye_l_t"><div class="ye_r_b"><div class="ye_l_b">
			<h3>快速定位</h3>
			<form method="post" action="cp.php?ac=friend">
			<p>
				<!--{if $_SCONFIG['realname']}-->姓名: <!--{else}-->用户名:<!--{/if}--> 
				<input type="text" name="username" value="" class="t_input" size="15" />
				<input type="hidden" name="searchmode" value="1" />
				<input type="hidden" name="findsubmit" value="1" />
				<input type="submit" name="findsubmit_btn" value="找人" class="submit" />
			</p>
			<p><a href="cp.php?ac=friend&op=find">查找我可能认识的人</a><span class="pipe">|</span>
				<a href="cp.php?ac=invite">邀请我的好友</a></p>
			<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
			</form>
		</div></div></div></div>
	</div>
	
</div>
<!--/sidebar-->


<div id="mood_form" style="display:none">
	<form method="post" action="cp.php?ac=doing" id="moodform">
		<table cellpadding="0" cellspacing="0">
			<tr>
				<td width="40"><a href="###" id="face" onclick="showFace(this.id, 'message');"><img src="image/facelist.gif" align="absmiddle" /></a></td>
				<td>
					<input type="text" name="message" id="message" value="" size="30" class="t_input" />
					<input type="hidden" name="addsubmit" value="true" />
					<input type="hidden" name="spacenote" value="true" />
				</td>
				<td>&nbsp;<input type="button" id="add" name="add" value="更新" class="submit" onclick="ajaxpost('moodform', 'mystate', 'reloadMood');" /></td>
				<td>&nbsp;<a href="javascript:;" onclick="mood_form_cancel();">取消</a></td>
			</tr>
		</table>
		<input type="hidden" name="formhash" value="<!--{eval echo formhash();}-->" />
	</form>
</div>


<!--{if $_SCONFIG['my_status']}-->
<ul id="viewall_menu" class="dropmenu_drop" style="display: none">
	<li><a href="space.php?do=home&view=we&filter=all"$actives[we]>好友的</a></li>
	<li><a href="space.php?do=home&view=all&filter=all"$actives[all]>大家的</a></li>
	<li><a href="space.php?do=home&view=me&filter=all"$actives[me]>自己的</a></li>
</ul>
<ul id="viewsite_menu" class="dropmenu_drop" style="display: none">
	<li><a href="space.php?do=home&view=we&filter=site"$actives[we]>好友的</a></li>
	<li><a href="space.php?do=home&view=all&filter=site"$actives[all]>大家的</a></li>
	<li><a href="space.php?do=home&view=me&filter=site"$actives[me]>自己的</a></li>
</ul>
<ul id="viewmyapp_menu" class="dropmenu_drop" style="display: none">
	<li><a href="space.php?do=home&view=we&filter=myapp"$actives[we]>好友的</a></li>
	<li><a href="space.php?do=home&view=all&filter=myapp"$actives[all]>大家的</a></li>
	<li><a href="space.php?do=home&view=me&filter=myapp"$actives[me]>自己的</a></li>
</ul>
<!--{/if}-->

<script type="text/javascript">
	var old_html = '';
	var next = $start;
	function reloadMood(showid, result) {
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=getmood', function(s){
			$('mystate').innerHTML = s;
		});
	}
	function mood_from() {
		old_html = $('mystate').innerHTML;
		$('mystate').innerHTML = $('mood_form').innerHTML;
	}
	function mood_form_cancel() {
		$('mystate').innerHTML = old_html;
	}
	function feed_more() {
		var x = new Ajax('XML', 'ajax_wait');
		next = next + $perpage;
		x.get('cp.php?ac=feed&op=get&start='+next+'&view=$_GET[view]&appid=$_GET[appid]&icon=$_GET[icon]&filter=$_GET[filter]', function(s){
			$('feed_div').innerHTML += s;
		});
	}
	function close_feedbox() {
		var x = new Ajax();
		x.get('cp.php?ac=common&op=closefeedbox', function(s){
			$('feed_box').style.display = 'none';
		});
	}
</script>

<!--{eval my_checkupdate();}-->
<!--{template footer}-->
