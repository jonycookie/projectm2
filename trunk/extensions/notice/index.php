<?php
!defined('IN_EXT') && die('Forbidden');
unset($_POST,$_GET);
require_once(D_P.'data/cache/cate.php');
require_once(R_P.'require/class_cate.php');
$cate = new Cate();
$rs = $db->query("SELECT * FROM cms_notice LIMIT 10");
!$rs && throwError('data_error');
while ($n = $db->fetch_array($rs)) {
	$notice[] = $n;
}
$metakeyword = $metadescrip = $sys['title'].','.$ext_config[$E_name]['name'];
start($sys['charset']);
require TemplateExt('index');
footer();
?>