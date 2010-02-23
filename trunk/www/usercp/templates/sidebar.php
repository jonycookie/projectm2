<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
!defined('iPATH') && exit('What are you doing?');
?><!-- Wrapper for the radial gradient background -->
<div id="sidebar">
  <div id="sidebar-wrapper"> <!-- Sidebar with logo and menu -->
    <h1 id="sidebar-title"><a href="#">iCMS用户中心</a></h1>
    <!-- Logo (221px wide) --> <a href="http://www.idreamsoft.cn" target="_blank"><img id="logo" src="usercp/style/iCMS.logo.gif" alt="iCMS logo" /></a> <!-- Sidebar Profile links -->
    <div id="profile-links"> 你好, <a href="usercp.php?do=setting&operation=profile" title="个人资料"><?=$member->user->name?></a><br />
      <br />
      <a href="<?=$iCMS->config['url']?>" title="网站首页" target="_blank">网站首页</a> | <a href="usercp.php?do=logout" title="退出登陆">注 销</a> </div>
    <ul id="main-nav">
      <!-- Accordion Menu -->
      <!-- li><a href="home.php?uid=<?=$member->uId?>" class="nav-top-item no-submenu"> 个人主页 </a></li -->
      <li><a href="usercp.php" class="nav-top-item no-submenu<?php if(empty($do)){?> current<?php } ?>">后台首页</a></li>
      <li> <a href="#" class="nav-top-item<?php if($do=="article"){?> current<?php } ?>">文章</a>
        <ul>
          <li><a <?php if($operation=="add"){?>class="current" <?php } ?>href="usercp.php?do=article&operation=add">撰写新文章</a></li>
          <li><a <?php if($operation=="manage"){?>class="current" <?php } ?>href="usercp.php?do=article&operation=manage">文章管理</a></li>
          <!-- Add class "current" to sub menu items also -->
          <?php /*<li><a <?php if($operation=="comment"){?>class="current" <?php } ?>href="usercp.php?do=article&operation=comment">文章评论</a></li>*/?>
          <!--li><a href="#">Manage Categories</a></li-->
        </ul>
      </li><?php /*
      <li> <a href="#" class="nav-top-item current"> Pages </a>
        <ul>
          <li><a href="#">Create a new Page</a></li>
          <li><a href="#">Manage Pages</a></li>
        </ul>
      </li>
      <li> <a href="#" class="nav-top-item"> Image Gallery </a>
        <ul>
          <li><a href="#">Upload Images</a></li>
          <li><a href="#">Manage Galleries</a></li>
          <li><a href="#">Manage Albums</a></li>
          <li><a href="#">Gallery Settings</a></li>
        </ul>
      </li>
      <li> <a href="#" class="nav-top-item"> Events Calendar </a>
        <ul>
          <li><a href="#">Calendar Overview</a></li>
          <li><a href="#">Add a new Event</a></li>
          <li><a href="#">Calendar Settings</a></li>
        </ul>
      </li>*/?>
      <li> <a href="#" class="nav-top-item<?php if($do=="setting"){?> current<?php } ?>"> 设置 </a>
        <ul>
          <li><a <?php if($operation=="profile"){?>class="current" <?php } ?>href="usercp.php?do=setting&operation=profile">个人资料</a></li>
        </ul>
      </li>
    </ul>
    <!-- End #main-nav --><?php /*
    <div id="messages" style="display: none"> <!-- Messages are shown when a link with these attributes are clicked: href="#messages" rel="modal"  -->
      <h3>3 Messages</h3>
      <p> <strong>17th May 2009</strong> by Admin<br />
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> </p>
      <p> <strong>2nd May 2009</strong> by Jane Doe<br />
        Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est. <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> </p>
      <p> <strong>25th April 2009</strong> by Admin<br />
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. <small><a href="#" class="remove-link" title="Remove message">Remove</a></small> </p>
      <form action="" method="post">
        <h4>New Message</h4>
        <fieldset>
        <textarea class="textarea" name="textfield" cols="79" rows="5"></textarea>
        </fieldset>
        <fieldset>
        <select name="dropdown" class="small-input">
          <option value="option1">Send to...</option>
          <option value="option2">Everyone</option>
          <option value="option3">Admin</option>
          <option value="option4">Jane Doe</option>
        </select>
        <input class="button" type="submit" value="Send" />
        </fieldset>
      </form>
    </div>
    <!-- End #messages --> */?></div>
</div>
<!-- End #sidebar -->