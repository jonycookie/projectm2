<?php
defined('IN_EXT') or die('Forbidden');
require_once(R_P.'require/class_cate.php');
class Contribute extends Cate{
	function treeCommon($cid=0){
		$this->cateselect = '';
		$this->getCommonTree($this->catedb,$cid,0,1);
		return $this->cateselect;
	}

	function getCommonTree($catedb,$cid=0,$mid=0,$type=0){
		foreach ($catedb as $cate){
			if($cate['mid']==0) continue;
			if($mid && $cate['mid']!=$mid) continue;
			if($cid==0 && $cate['up']!=0){
				continue;
			}elseif($cid && $cate['up']!=$cid){
				continue;
			}
			if($type && $cate['mid']<0) continue; 
			$add = '';
			if($cate['depth']==1){
				$add = "&raquo;";
			}else{
				$add = '|-';
				$repeatnum = ($cate['depth']-1);
				$add.= str_repeat('--',$repeatnum);
			}
			if ($cate['mid']==0) {
				$disabled = "disabled=\"disabled\"";
			}else{
				$disabled = '';
			}
			$this->cateselect.="<option value=\"$cate[cid]\" $disabled>$add$cate[cname]</option>";
			if(count($catedb)==0){
				return null;
			}
			$this->getCommonTree($catedb,$cate['cid'],$mid,$type);
		}
	}
}
?>