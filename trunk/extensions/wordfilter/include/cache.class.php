<?php
defined('IN_EXT') or die('Forbidden');
class wdfCache{
	function cache(){
		global $db;
		$writeinfo = "<?php";
		$replace = $wordsfb = array();
		$query = $db->query("SELECT * FROM cms_wordfilter ORDER BY id DESC");
		while(@extract($db->fetch_array($query))){
			if($srcword){
				$srcword = preg_quote(str_replace('\\\\','\\',$srcword),'/');
				if($type==0){
					$wordsfb[$srcword] = $tarword;
				} elseif($type==1){
					$replace[$srcword] = $tarword;
				}
			}
		}
		$writeinfo .= "\n\$replace=".pw_var_export($replace,0).";";
		$writeinfo .= "\n\$wordsfb=".pw_var_export($wordsfb,0).";\n?>";
		writeover(D_P."data/cache/wordfilter.php",$writeinfo);
	}
}