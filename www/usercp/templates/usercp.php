<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户中心</title>
<!--                       CSS                       -->
<!-- Reset Stylesheet -->
<link rel="stylesheet" href="usercp/style/reset.css" type="text/css" media="screen" />
<!-- Main Stylesheet -->
<link rel="stylesheet" href="usercp/style/style.css" type="text/css" media="screen" />
<!-- Invalid Stylesheet. This makes stuff look pretty. Remove it if you want the CSS completely valid -->
<link rel="stylesheet" href="usercp/style/invalid.css" type="text/css" media="screen" />
<!-- Colour Schemes
	  
		Default colour scheme is green. Uncomment prefered stylesheet to use it.
		
		<link rel="stylesheet" href="usercp/css/blue.css" type="text/css" media="screen" />
		
		<link rel="stylesheet" href="usercp/css/red.css" type="text/css" media="screen" />  
		-->
<!-- Internet Explorer Fixes Stylesheet -->
<!--[if lte IE 7]>
			<link rel="stylesheet" href="usercp/css/ie.css" type="text/css" media="screen" />
		<![endif]-->
<!--                       Javascripts                       -->
<!-- jQuery -->
<script type="text/javascript" src="javascript/jquery.js"></script>
<!-- jQuery Configuration -->
<script type="text/javascript" src="usercp/scripts/simpla.jquery.configuration.js"></script>
<!-- Facebox jQuery Plugin -->
<script type="text/javascript" src="usercp/scripts/facebox.js"></script>
<!-- jQuery WYSIWYG Plugin -->
<script type="text/javascript" src="usercp/scripts/jquery.wysiwyg.js"></script>
<!-- Internet Explorer .png-fix -->
<!--[if IE 6]>
			<script type="text/javascript" src="usercp/scripts/DD_belatedPNG_0.0.7a.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('.png_bg, img, li');
			</script>
		<![endif]-->
</head>
<body>
<noscript>
<!-- Show a notification if the user has disabled javascript -->
<div class="notification error png_bg">
  <div> Javascript is disabled or is not supported by your browser. Please <a href="http://browsehappy.com/" title="Upgrade to a better browser">upgrade</a> your browser or <a href="http://www.google.com/support/bin/answer.py?answer=23852" title="Enable Javascript in your browser">enable</a> Javascript to navigate the interface properly. </div>
</div>
</noscript>
<div id="body-wrapper">
  <?php 
include 'usercp/templates/sidebar.php';
?>
  <!-- End #sidebar -->
  <div id="main-content">
    <?php
$inc='home.inc.php';
if(in_array($do, array('home','catalog','link','comment','advertise','articletype','message','tag','keywords','cache', 'setting', 'article','default', 'user', 'database', 'model','content', 'field','plugin','modifier', 'file', 'html','ajax','dialog','account','group','template'))) {
	$inc = $do.'.inc.php';
} 
require_once iPATH.'usercp/'.$inc;
?>
    <div id="footer"> <small> &#169; Copyright 2009 Simpla Admin | Powered by <a href="http://themeforest.net/item/simpla-admin-flexible-user-friendly-admin-skin/46073">Simpla Admin</a> | <a href="#">Top</a> </small> </div>
    <!-- End #footer -->
  </div>
  <!-- End #main-content -->
</div>
</body>
</html>
