<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: xmlrpc.php 6492 2008-03-12 01:41:41Z liguode $
*/

include_once('./common.php');
include_once(S_ROOT.'./source/class_xmlrpc.php');

$siteurl = getsiteurl();

if(isset($_GET['rsd'])) {
	obclean();
	header("Content-type: text/xml, charset=utf-8", true);
	echo '<?xml version="1.0" encoding="utf-8"?>';
?>
<rsd version="1.0" xmlns="http://archipelago.phrasewise.com/rsd">
	<service>
		<engineName>UCenter Home</engineName>
		<engineLink>http://u.discuz.net/</engineLink>
		<homePageLink><?php echo $siteurl; ?></homePageLink>
		<apis>
			<api name="MetaWeblog" blogID="1" preferred="false" apiLink=" <?php echo $siteurl; ?>xmlrpc.php" />
			<api name="WordPress" blogID="1" preferred="true" apiLink="<?php echo $siteurl; ?>xmlrpc.php" />
			<api name="Blogger" blogID="1" preferred="false" apiLink=" <?php echo $siteurl; ?>xmlrpc.php" />
		</apis>
	</service>
</rsd>
<?php

} else {
	$xmlrpc = xmlrpc::xmlrpcSet();
	$data = $xmlrpc->xmlrpcServer();
}

?>