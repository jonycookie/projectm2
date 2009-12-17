<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
require_once 'usercp/global.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户中心</title>
<link rel="stylesheet" href="usercp/style/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="usercp/style/style.css" type="text/css" media="screen" />
<link rel="stylesheet" href="usercp/style/invalid.css" type="text/css" media="screen" />
<!--[if lte IE 7]>
<link rel="stylesheet" href="usercp/css/ie.css" type="text/css" media="screen" />
<![endif]-->
<script type="text/javascript" src="javascript/jquery.js"></script>
<script type="text/javascript" src="usercp/scripts/function.js"></script>
</head>
<body>
<div id="body-wrapper"> <?php include 'usercp/templates/sidebar.php';?> <!-- End #sidebar -->
  <div id="main-content"> <?php
$inc = in_array($do, array('home','comment','setting', 'article','dialog','file'))?$do.'.inc.php':'home.inc.php';
require_once iPATH.'usercp/'.$inc;
?>
    <div id="footer"> &#169; Copyright 2007-<?=date("Y")?> <a href="http://www.iDreamSoft.cn" target="_blank">iDreamSoft</a> | Powered by <a href="http://www.iDreamSoft.cn" target="_blank">iCMS</a> <?=Version?> | <a href="#">Top</a> </div>
    <!-- End #footer --> </div>
  <!-- End #main-content --> </div>
</body>
</html>
