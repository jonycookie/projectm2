<?php
defined('IN_EXT') or die('Forbidden');
class navCache{
	function cache(){
		global $db;
		$rs=$db->query("select * from cms_nav order by view DESC");
		$nav_head=$nav_foot=array();
		while ($navdb=$db->fetch_array($rs)){
			if($navdb['pos']=='foot'){
				$nav_foot[]=$navdb;
			}elseif ($navdb['pos']=='head'){
				$nav_head[]=$navdb;
			}
		}
		$navdb = $navinfo = array();
		$writemsg="<?php\n";
		$writemsg .= navCache::writeVar('nav_head',$nav_head);
		$writemsg .= navCache::writeVar('nav_foot',$nav_foot);
		foreach ($nav_head as $navdb){
			$target = $navdb['target']==1 ? '_blank' : '_self';
			$style_array=explode('|',$navdb['style']);
			$style_array[1] && $navdb['title']='<b>'.$navdb['title'].'</b>';
			$style_array[2] && $navdb['title']='<i>'.$navdb['title'].'</i>';
			$style_array[3] && $navdb['title']='<u>'.$navdb['title'].'</u>';
			$style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
			$navinfo[] = "<li><a href=\"".$navdb['link']."\" target=\"".$target."\" title=\"".$navdb['alt']."\">".$navdb['title']."</a></li>";
		}
		$navinfo_h = implode(' ',$navinfo);
		$navinfo = array();
		foreach ($nav_foot as $navdb){
			$target = $navdb['target']==1 ? '_blank' : '_self';
			$style_array=explode('|',$navdb['style']);
			$style_array[1] && $navdb['title']='<b>'.$navdb['title'].'</b>';
			$style_array[2] && $navdb['title']='<i>'.$navdb['title'].'</i>';
			$style_array[3] && $navdb['title']='<u>'.$navdb['title'].'</u>';
			$style_array[0] && $navdb['title']="<font color=\"$style_array[0]\">".$navdb['title']."</font>";
			$navinfo[] = "<li><a href=\"".$navdb['link']."\" target=\"".$target."\" title=\"".$navdb['alt']."\">".$navdb['title']."</a></li>";
		}
		$navinfo_f = implode(' ',$navinfo);
		$jsnavhead = "document.write(\"".addslashes($navinfo_h)."\");\n";
		$jsnavfoot = "document.write(\"".addslashes($navinfo_f)."\");\n";
		$jsmsghead = "<script language=\"javascript\" src=\"script/verycms/nav_head.js\"></script>";
		$jsmsgfoot = "<script language=\"javascript\" src=\"script/verycms/nav_foot.js\"></script>";
		$writemsg.= "\$navhead = \"".addslashes($jsmsghead)."\";\n";
		$writemsg.= "\$navfoot = \"".addslashes($jsmsgfoot)."\";\n";
		$writemsg.= '?>';
		require_once(R_P.'require/class_const.php');
		$const = new TplConst('EXT');
		$id = $const->isHave('EXT_navhead');
		$vararray = array('title'=>"EXT_navhead",'name'=>"EXT_navhead",'value'=>$jsmsghead,'id'=>$id);
		$const->setConst($vararray);
		$id = $const->isHave('EXT_navfoot');
		$vararray = array('title'=>"EXT_navfoot",'name'=>"EXT_navfoot",'value'=>$jsmsgfoot,'id'=>$id);
		$const->setConst($vararray);
		writeover(R_P.'script/verycms/nav_head.js',$jsnavhead);
		writeover(R_P.'script/verycms/nav_foot.js',$jsnavfoot);
		writeover(D_P.'data/cache/nav.php',$writemsg);
	}

	function writeVar($varname,$arrayvalue){
		$msg="\$$varname=array(\n";
		$i=0;
		foreach ($arrayvalue as $v){
			$i++;
			$msg.="\t'$i'=>array(\n";
			foreach ($v as $key=>$val) {
				$val=addslashes($val);
				$msg.="\t\t'$key'\t=>'$val',\n";
			}
			$msg.="\t),\n";
		}
		$msg.=");\n";
		return $msg;
	}
}
?>