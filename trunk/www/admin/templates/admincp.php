<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?'); 
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>iCMS Administrator's Control Panel</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=iCMS_CHARSET?>">
<meta content="iDreamSoft Inc." name="Copyright" />
<link rel="stylesheet" href="admin/images/style.css" type="text/css" media="all" />
<script src="javascript/jquery.js" type="text/javascript"></script>
<script src="javascript/admin.fun.js" type="text/javascript"></script>
</head>
<body style="margin: 0px" scroll="no">
<table cellpadding="0" cellspacing="0" width="100%" height="100%">
  <tr>
    <td colspan="2" height="90"><div class="mainhd">
        <div class="logo">iCMS Administrator's Control Panel</div>
        <div class="uinfo">
          <p>您好, <em><?=$Admin->admin->username?></em> [ <a href="<?=__SELF__?>?do=logout" target="_top">退出</a> ]</p>
          <p class="btnlink"><a href="index.php" target="_blank">网站首页</a></p>
        </div>
        <div class="navbg"></div>
        <div class="nav">
          <ul id="topmenu">
            <?php if($Admin->MP('header_index','F')){?><li><em><a href="javascript:void(0);" id="header_index" onClick="toggleMenu('index', 'home');"><?=lang('header_index')?></a></em></li>									<?php }?>
            <?php if($Admin->MP('header_setting','F')){?><li><em><a href="javascript:void(0);" id="header_setting" onClick="toggleMenu('setting', 'setting&operation=config');"><?=lang('header_setting')?></a></em></li>		<?php }?>
            <?php if($Admin->MP('header_article','F')){?><li><em><a href="javascript:void(0);" id="header_article" onClick="toggleMenu('article', 'catalog');"><?=lang('header_article')?></a></em></li>		<?php }?>
            <?php if($Admin->MP('header_user','F')){?><li><em><a href="javascript:void(0);" id="header_user" onClick="toggleMenu('user', 'user&operation=manage');"><?=lang('header_user')?></a></em></li>									<?php }?>
            <?php if($Admin->MP('header_extend','F')){?><li><em><a href="javascript:void(0);" id="header_extend" onClick="toggleMenu('extend', 'model&operation=manage');"><?=lang('header_extend')?></a></em></li>	<?php }?>
            <?php if($Admin->MP('header_html','F')){?><li><em><a href="javascript:void(0);" id="header_html" onClick="toggleMenu('html', 'html&operation=index');"><?=lang('header_html')?></a></em></li>					<?php }?>
            <?php if($Admin->MP('header_tools','F')){?><li><em><a href="javascript:void(0);" id="header_tools" onClick="toggleMenu('tools', 'link');"><?=lang('header_tools')?></a></em></li>					<?php }?>
            <li><em><a href="javascript:void(0);" class="diffcolor" onClick="window.open('http://www.idreamsoft.cn/doc/iCMS/index.html')"><?=lang('faq')?></a></em></li>
          </ul>
          <div class="currentloca">
            <p id="admincpnav"></p>
          </div>
          <div class="navbd"></div>
          <div class="sitemapbtn"> <span id="add2custom"></span> <a href="javascript:void(0);" id="cpmap" onClick="showMap();return false;"><img src="admin/images/btn_map.gif" title="后台导航" width="72" height="18" /></a> </div>
        </div>
      </div></td>
  </tr>
  <tr>
    <td valign="top" width="160" class="menutd"><div id="leftmenu" class="menu">
    <?php require_once ('admin/menu.inc.php');?>
    </div></td>
    <td valign="top" width="100%"class="mask"><iframe src="<?=__SELF__?>?<?php echo $extra;?>" name="main" width="100%" height="100%" frameborder="0"scrolling="yes" style="overflow: visible;"></iframe></td>
  </tr>
</table>
<div id="cpmap_menu" class="custom" style="display:none">
  <div class="cside">
    <h3><span class="ctitle1">常用操作</span></h3>
    <ul class="cslist" id="custommenu">
          <?php if($Admin->MP('menu_article_manage','F')){?><li><a href="<?=__SELF__?>?do=article&operation=manage" target="main">文章管理</a></li><?php }?>
          <?php if($Admin->MP('menu_catalog','F')){?><li><a href="<?=__SELF__?>?do=catalog" target="main">栏目管理</a></li><?php }?>
          <?php if($Admin->MP('menu_comment','F')){?><li><a href="<?=__SELF__?>?do=comment" target="main">评论管理</a></li><?php }?>
          <?php if($Admin->MP('menu_user','F')){?><li><a href="<?=__SELF__?>?do=user" target="main">用户管理</a></li><?php }?>
          <?php if($Admin->MP('menu_article_user_manage','F')){?><li><a href="<?=__SELF__?>?do=article&operation=manage&act=user" target="main">投稿管理</a></li><?php }?>
          <?php if($Admin->MP('menu_link','F')){?><li><a href="<?=__SELF__?>?do=link" target="main">友情链接</a></li><?php }?>
          <?php if($Admin->MP('menu_advertise','F')){?><li><a href="<?=__SELF__?>?do=advertise" target="main">广告管理</a></li><?php }?>
          <?php if($Admin->MP('menu_message','F')){?><li><a href="<?=__SELF__?>?do=message" target="main">留言管理</a></li><?php }?>
          <?php if($Admin->MP('menu_cache','F')){?><li><a href="<?=__SELF__?>?do=cache" target="main">更新缓存</a></li><?php }?>
    </ul>
  </div>
  <div class="cmain" id="cmain"></div>
  <div class="cfixbd"></div>
</div>
<script type="text/JavaScript">
	var headers = new Array('index', 'setting', 'article', 'user', 'extend', <?php if ($iCMS->config['ishtm']){?>'html',<?php }?> 'tools');
	function toggleMenu(key, url) {
		if(key == 'index' && url == 'home') {
			parent.location.href = '<?=__SELF__?>?frames=yes';
		//	return false;
		}
		for(var k in headers) {
			if($('#menu_' + headers[k])) {
				if(headers[k] == key){
					$('#menu_' + headers[k]).show();
				}else{
					$('#menu_' + headers[k]).hide();
				}
			}
		}
		$("#topmenu").find('li').each(function(i){
			if(this.className == 'navon') this.className = '';
		}); 
		$('#header_' + key).parent().parent().addClass('navon');
		if(url) {
			parent.main.location = '<?=__SELF__?>?do=' + url;
			$('#menu_' + key).find('a').each(function(i){
				this.className = this.href.substr(this.href.indexOf('<?=$admincpfile?>?do=') + <?=strlen($admincpfile)+4?>) == url ? 'tabon' : (this.className == 'tabon' ? '' : this.className);
			});
		}
		return false;
	}
	function initCpMenus(menuContainerid) {
		var key = '';
		$('#'+menuContainerid).find('a').each(function(i){
			if(menuContainerid == 'leftmenu' && !key && this.href.substr(this.href.indexOf('<?=$admincpfile?>?do=') + <?=strlen($admincpfile)+1?>) == '<?php echo $extra;?>') {
				key = this.parentNode.parentNode.id.substr(5);
				this.className = 'tabon';
			}
//			if(!this.getAttribute('ajaxtarget')) this.onclick = function() {
			this.onclick = function() {
				if(menuContainerid != 'custommenu') {
					$('#'+menuContainerid).find('li').each(function(i){
						if(this.firstChild.className != 'menulink') this.firstChild.className = '';
					});
					if(this.className == '') this.className = menuContainerid == 'leftmenu' ? 'tabon' : 'bold';
				}
				if(menuContainerid != 'leftmenu'){
					var hk, currentkey;
					var _this=this;
					$('#leftmenu').find('a').each(function(i){
						hk = this.parentNode.parentNode.id.substr(5);
						if(_this.href.indexOf(this.href) != -1) {
							this.className = 'tabon';
							if(hk != 'index') currentkey = hk;
						} else {
							this.className = '';
						}
					});
					if(currentkey) toggleMenu(currentkey);
					hideMenu();
				}
			}
		});
		return key;
	}
	var header_key = initCpMenus('leftmenu');
	toggleMenu(header_key ? header_key : 'index');
	function initCpMap() {
		var  s = '<ul class="cnote"><li><img src="admin/images/btn_map.gif" /></li><li> 按 “ ESC ” 键展开 / 关闭此菜单</li></ul><table class="cmlist" id="mapmenu"><tr>';

		for(var k in headers) {
			if(headers[k] != 'index') {
				s += '<td valign="top"><ul class="cmblock"><li><h4>' + $('#header_' + headers[k]).html() + '</h4></li>';
				$('#menu_' + headers[k]).find('a').each(function(i){
					s += '<li><a href="' + this.href + '" target="' + this.target + '" k="' + headers[k] + '">' + this.innerHTML + '</a></li>';
				});
				s += '</ul></td>';
			}
		}
		s += '</tr></table>';
		return s;
	}
	function initpowed() {
		var  ss = '<table><tr>';

		for(var k in headers) {
//			if(headers[k] != 'index') {
				ss += '<td valign="top"><ul><li><h4><input name="power[]" type="checkbox" class="checkbox" value="header_'+headers[k]+'" />' + $('#header_' + headers[k]).html() + '</h4></li>';
				$('#menu_' + headers[k]).find('a').each(function(i){
					ss += '<li><input name="power[]" type="checkbox" class="checkbox" value="'+this.id+'" />' + this.innerHTML + '</li>';
				});
				ss += '</ul></td>';
			}
//		}
		ss += '</tr></table>';
		return ss;
	}
	$('#cmain').html(initCpMap());
	initCpMenus('mapmenu');
	function showMap() {
		showMenu('cpmap', true, 3, 3);
	}
	function resetEscAndF5(e) {
		e = e ? e : window.event;
		actualCode = e.keyCode ? e.keyCode : e.charCode;
		if(actualCode == 27) {
			if($('#cpmap_menu').css('display') == 'none') {
				showMap();
			} else {
				hideMenu();
			}
		}
		if(actualCode == 116 && parent.main) {
			parent.main.location.reload();
			if(document.all) {
				e.keyCode = 0;
				e.returnValue = false;
			} else {
				e.cancelBubble = true;
				e.preventDefault();
			}
		}
	}
	_attachEvent(document.documentElement, 'keydown', resetEscAndF5);
</script>
</body>
</html>