/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: script_manage.js 6799 2008-03-27 01:08:23Z zhengqingpeng $
*/

//添加留言
function wall_add(cid, result) {
	if(result) {
		var obj = $('comment_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=comment', function(s){
			newli.innerHTML = s;
		});
		obj.insertBefore(newli, obj.firstChild);
		if($('comment_message')) {
			$('comment_message').value= '';
		}
	}
}
//短消息发送
function pm_add(pmid, result) {
	if(result) {
		var obj = $('pm_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=pm', function(s){
			newli.innerHTML = s;
		});
		obj.appendChild(newli);
		if($('pm_subject')) {
			$('pm_subject').value= '';
		}
		if($('pm_message')) {
			$('pm_message').value= '';
		}
	}
}
//添加分享
function share_add(sid, result) {
	if(result) {
		var obj = $('share_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=share', function(s){
			newli.innerHTML = s;
		});
		obj.insertBefore(newli, obj.firstChild);
		$('share_link').value = 'http://';
		$('share_general').value = '';
	}
}
//添加评论
function comment_add(cid, result) {
	if(result) {
		var obj = $('comment_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=comment', function(s){
			newli.innerHTML = s;
		});
		obj.appendChild(newli);
		if($('comment_message')) {
			$('comment_message').value= '';
		}
		if($('comment_replynum')) {
			var a = parseInt($('comment_replynum').innerHTML);
			var b = a + 1;
			$('comment_replynum').innerHTML = b + '';
		}
	}
}
//编辑
function comment_edit(cid, result) {
	if(result) {
		var obj = $('comment_'+ cid +'_li');
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=comment&cid='+ cid, function(s){
			obj.innerHTML = s;
		});
	}
}
//删除
function comment_delete(cid, result) {
	if(result) {
		var obj = $('comment_'+ cid +'_li');
		obj.style.display = "none";
		if($('comment_replynum')) {
			var a = parseInt($('comment_replynum').innerHTML);
			var b = a - 1;
			$('comment_replynum').innerHTML = b + '';
		}
	}
}
//删除feed
function feed_delete(feedid, result) {
	if(result) {
		var obj = $('feed_'+ feedid +'_li');
		obj.style.display = "none";
	}
}
//删除短消息
function pm_delete(pmid, result) {
	if(result) {
		var obj = $('pm_'+ pmid +'_li');
		obj.style.display = "none";
	}
}
//删除分享
function share_delete(sid, result) {
	if(result) {
		var obj = $('share_'+ sid +'_li');
		obj.style.display = "none";
	}
}
//删除好友
function friend_delete(uid, result) {
	if(result) {
		var obj = $('friend_'+ uid +'_li');
		if(obj != null) obj.style.display = "none";
	}
}
//更改分组
function friend_changegroup(uid, result) {
	if(result) {
		var obj = $('friend_group_'+ uid);
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=getfriendgroup&uid='+uid, function(s){
			obj.innerHTML = s;
		});
	}
}
//添加回帖
function post_add(pid, result) {
	if(result) {
		var obj = $('post_ul');
		var newli = document.createElement("div");
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=post', function(s){
			newli.innerHTML = s;
		});
		obj.appendChild(newli);
		if($('post_message')) {
			$('post_message').value= '';
			$('post_pic').value = 'http://';
		}
		if($('post_replynum')) {
			var a = parseInt($('post_replynum').innerHTML);
			var b = a + 1;
			$('post_replynum').innerHTML = b + '';
		}
	}
}
//编辑回帖
function post_edit(pid, result) {
	if(result) {
		var obj = $('post_'+ pid +'_li');
		var x = new Ajax();
		x.get('do.php?ac=ajax&op=post&pid='+ pid, function(s){
			obj.innerHTML = s;
		});
	}
}
//删除回帖
function post_delete(pid, result) {
	if(result) {
		var obj = $('post_'+ pid +'_li');
		obj.style.display = "none";
		if($('post_replynum')) {
			var a = parseInt($('post_replynum').innerHTML);
			var b = a - 1;
			$('post_replynum').innerHTML = b + '';
		}
	}
}
//打招呼
function poke_send(uid, result) {
	if(result) {
		if($('poke_'+ uid)) {
			$('poke_'+ uid).style.display = "none";
		}
	}
}
//删除好友请求
function myfriend_post(uid, result) {
	if(result) {
		$('friend_'+uid).style.display = "none";
	}
}
//加入选吧
function mtag_join(tagid, result) {
	if(result) {
		location.reload();
	}
}
