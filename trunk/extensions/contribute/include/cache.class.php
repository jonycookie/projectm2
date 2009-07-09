<?php
defined('IN_EXT') or die('Forbidden');
class contributeCache{
	function cache(){
		global $db;
		$contribute = array();
		$rs = $db->get_one("SELECT * FROM cms_extension WHERE name='contribute_config'");
		$contribute = unserialize($rs['value']);
		$writemsg	= "<?php\n\$contribute = ".pw_var_export($contribute)."\n?>";
		writeover(D_P.'data/cache/ext_contribute.php',$writemsg);
	}
}
?>