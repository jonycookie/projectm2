<?php
!defined('IN_CMS') && die('Forbidden');
require_once(D_P.'data/cache/cate.php');

/**
 *  栏目调用的类,用于显示栏目菜单,子栏目,栏目树,以及当前位置
 *
 */
class Cate{
	var $catedb;
	var $position;
	var $cateselect;

	function __construct(){ //PHP5
		global $catedb;
		$this->catedb = $catedb;
		$this->nav();
	}

	function Cate(){ //PHP4
		$this->__construct();
	}

	/**
	 * 返回栏目树状
	 *
	 * @return string
	 */
	function tree($cid=0){
		$this->cateselect = '';
		$this->getTree($this->catedb,$cid);
		return $this->cateselect;
	}

	function treeByMid($mid){
		$this->cateselect = '';
		$this->getTree($this->catedb,0,$mid);
		return $this->cateselect;
	}

	/**
	 * 显示栏目菜单，如果有参数cid，则显示其子栏目
	 *
	 * @param integer $cid
	 * @return array
	 */
	function menu($cid=0){
		global $very;
		$menu = array();
		foreach ($this->catedb as $key=>$c){
			if($c['type']!=1) continue; //跳过不显示的栏目
			$c['url'] = $c['listurl'];
			$c['up']==$cid && $menu[$key]=$c;
		}
		return $menu;
	}

	function child($id=0,$type=null){
		$child = array();
		if($type){
			foreach ($this->catedb as $key=>$c){
				if($c['mid']==$id){
					$c['url'] = $c['listurl'];
					$child[$key]=$c;
				}
			}
		}else{
			foreach ($this->catedb as $key=>$c){
				if($c['up']==$id){
					$c['url'] = $c['listurl'];
					$child[$key]=$c;
				}
			}
		}
		return $child;
	}

	/**
	 * 当前位置
	 *
	 */
	function nav(){
		global $view,$cid,$very,$E_name,$ext_config;
		if(defined('IN_EXT')) {
			$this->position = "<a href=\"$very[url]\">$very[title]</a>&nbsp;".$ext_config[$E_name]['name'];
			return true;
		}
		if($cid){
			$this->findFather($cid);
		}
		if($view['title']){
			$this->position = $this->position." > ".$view['title'];
		}
		$this->position = "<a href=\"$very[url]\">$very[title]</a>". $this->position;
	}

	/**
	 * 返回当前位置
	 *
	 */
	function position(){
		return $this->position;
	}
	function getTree($catedb,$cid=0,$mid=0){
		foreach ($catedb as $cate){
			if($cate['mid']==0) continue;
			if($mid && $cate['mid']!=$mid) continue;
			if($cid==0 && $cate['up']!=0){
				continue;
			}elseif($cid && $cate['up']!=$cid){
				continue;
			}
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
			// unset($catedb[$cid]);
			if(count($catedb)==0){
				return null;
			}
			$this->getTree($catedb,$cate['cid'],$mid);
		}
	}

	/**
	 * 根据当前cid不断向上获取其父ID
	 *
	 * @param integer $cid
	 */
	function findFather($cid){
		$this->position = " &gt; <a href=".$this->catedb[$cid]['listurl'].">".$this->catedb[$cid]['cname']."</a> " . $this->position;
		$up = $this->catedb[$cid]['up'];
		if($up){
			$this->findFather($up);
		}
	}
}
?>