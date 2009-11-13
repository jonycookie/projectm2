<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
class catalog{
	var $array=array();
	var $parent;
	var $Carray;
	var $catalog;
	
	function catalog($id='',$isurl=false,$ishidden=false,$issend=false){
		$this->__construct($id,$isurl,$ishidden,$issend);
	}
    function __construct($id='',$isurl=false,$ishidden=false,$issend=false){
    	global $iCMS;
    	$this->db			=$iCMS->db;
//		$sql				=' WHERE 1=1 ';
		$isurl 		&& $_sql[]=" `url`!=''";
		$ishidden 	&& $_sql[]=" `ishidden`='0'";
		$id!=""		&& $_sql[]=" `rootid`='$id'";
		$issend 	&& $_sql[]=" `issend`='1'";
		if(is_array($_sql)){
			$sql=' WHERE '.implode(' and ',$_sql);
			echo $sql;
		}
//		$sql	= count($sql)>1?:
		$rs=$this->db->getArray("SELECT * FROM `#iCMS@__catalog`{$sql} ORDER BY `order` , `id` ASC",ARRAY_A);
		$_count=count($rs);
		for ($i=0;$i<$_count;$i++){
			$this->catalog[$rs[$i]['id']] = 
			$this->array[$rs[$i]['rootid']][$rs[$i]['id']] = 
			$this->parent[$rs[$i]['id']][$rs[$i]['rootid']] = $rs[$i];
			
			$this->cacheRootId[$rs[$i]['rootid']][$rs[$i]['id']] = $rs[$i]['id'];
			$this->cacheParent[$rs[$i]['id']]=$rs[$i]['rootid'] ;
		}
		$this->model = $iCMS->cache('model.id','include/syscache',0,true);
    }
    function cache(){
    	global $iCMS;
		if($this->catalog)foreach($this->catalog AS $C){
			$C['ishidden']=="1" && $_catalog_hidden_id[]=$C['id'];
		}
		$_catalog_hidden_id && $_catalog_hidden_id=implode(',',$_catalog_hidden_id);
		$iCMS->cache(false,'include/syscache',0,true,false);
		$iCMS->addcache('catalog.cache',$this->catalog,0);
		$iCMS->addcache('catalog.array',$this->array,0);
		$iCMS->addcache('catalog.rootid',$this->cacheRootId,0);
		$iCMS->addcache('catalog.parent',$this->cacheParent,0);
		$iCMS->addcache('catalog.hidden',$_catalog_hidden_id,0);
    }
    function allArray($cid="0", $level = 0){
    	if(isset($this->array[$cid])){
    		foreach($this->array[$cid] AS $root=>$C){
    			$C['level']=$level;
    			$this->Carray[]=$C;
    			if(isset($this->array[$cid])){
    				$this->allArray($C['id'],$level+1);
    			}
    		}
    	}
    }
    function all($cid =0, $level = 1){
    	global $iCMS,$Admin;
		if(isset($this->array[$cid])){
			if($this->array[$cid])foreach($this->array[$cid] AS $root=>$C){
				$tr.=$this->tr($C,$level,'all').$this->all($C['id'], $level+1);
		   	}
	    }
		return $tr;
    }
    function row($cid =0, $level = 1){
    	global $iCMS,$Admin;
		if($this->array[$cid])foreach($this->array[$cid] AS $C){
			$tr.=$this->tr($C,$level);
	   	}
		return $tr;
    }
    function tr($C,$level,$Q='row'){
    	global $iCMS,$Admin;
		if($Admin->CP($C['id'])){
			$readonly='  class="txt"';
			$CAction=true;
		}else{
			$readonly=' readonly="true" class="readonly"';
			$CAction=false;
//			if($Q=='all')return false;
		}
		$this->model[$C['mid']] && $model=$this->model[$C['mid']];
		$name=empty($model)?'文章':'内容';
		$href=$this->url($C);
	  	$tr.='<tr id="catalog_'.$C['id'].'">';
		$tr.='<td class="td25"><input'.$readonly.' type="text" class="txt" name="order['.$C['id'].']" value="'.$C['order'].'" /></td>';
	    $tr.='<td>';
	    $ls=$level>"0"?str_repeat("│　", $level)."├":"";
	    if($this->array[$C['id']]){
	    	if($level=="0"){
	    		if($Q=='all'){
		    		$ls='<img ids'.$C['id'].'="'.$this->AJAXid($C['id']).'" src="admin/images/desc.gif" alt="收缩下级" onclick="fold('.$C['id'].',1,this);"/> ';
	    		}else{
		    		$ls='<img src="admin/images/add.gif" alt="展开下级" onclick="expand('.$C['id'].',1,this);"/> ';
	    		}
	    	}else{
	    		if($Q=='all'){
				    $ls=str_replace("│　├",'<img ids'.$C['id'].'="'.$this->AJAXid($C['id']).'" src="admin/images/desc.gif" alt="收缩下级" onclick="fold('.$C['id'].','.$level.',this);"/>　├',$ls);
	    		}else{
				    $ls=str_replace("│　├",'<img src="admin/images/add.gif" alt="展开下级" onclick="expand('.$C['id'].','.$level.',this);"/>　├',$ls);
	    		}
	    	}
	    }
	    $tr.=$ls.'<input'.$readonly.' type="text" name="name['.$C['id'].']" value="'.$C['name'].'"';
	    $C['attr']=='channel' && $tr.='style="font-weight:bold"';
	    $tr.='/>[ID:<a href="'.$href.'" target="_blank">'.$C['id'].'</a>]';
	    $C['url'] && $tr.='[外部链接]';
	    $iCMS->config['ishtm'] && $C['domain'] && $tr.='[绑定域名]';
	    $tr.='['.$this->attrname($C['attr'],$name,$C['count']).']</td><td>';
	    if($CAction){
		    if ($C['attr']=='page'){ 
			    $tr.='<a href="'.__SELF__.'?do=catalog&operation=add&rid='.$C['id'].'&type=page" class="addtr">子页面</a> |<a href="'.__SELF__.'?do=file&operation=page&cid='.$C['id'].'" class="act">编辑页面</a>|';
		    }elseif ($C['attr']=='list'||$C['attr']=='channel'){
			    $tr.='<a href="'.__SELF__.'?do=catalog&operation=add&rid='.$C['id'].'" class="addtr">子栏目</a> |';
			    if(empty($model)){
				    $tr.='<a href="'.__SELF__.'?do=article&operation=manage&cid='.$C['id'].'&sub=on" class="act">文章管理</a>|';
				    $tr.='<a href="'.__SELF__.'?do=article&operation=manage&cid='.$C['id'].'&type=draft" class="act">草稿箱</a>|';
			    }else{
				    $tr.='<a href="'.__SELF__.'?do=content&operation=manage&table='.$model['table'].'&mid='.$C['mid'].'&cid='.$C['id'].'&sub=on" class="act">内容管理</a>|';
				    $tr.='<a href="'.__SELF__.'?do=content&operation=manage&table='.$model['table'].'&mid='.$C['mid'].'&cid='.$C['id'].'&type=draft" class="act">草稿箱</a>|';
			    }
			    if ($C['attr']=='list'){
				    if(empty($model)){
					    $tr.=' <a href="'.__SELF__.'?do=article&operation=add&cid='.$C['id'].'" class="addtr">文章</a>|';
				    }else{
					    $tr.=' <a href="'.__SELF__.'?do=content&operation=add&table='.$model['table'].'&mid='.$C['mid'].'&cid='.$C['id'].'"  class="addtr">内容</a>|';
				    }
				}
			}
			if($iCMS->config['ishtm']){
				if($C['attr']=='page'){
					$tr.="<a href='".__SELF__."?do=html&operation=create&action=page&cid={$C['id']}' class='act'>生成页面</a>|";
				}else{
					$tr.="<a href='".__SELF__."?do=html&operation=create&action=catalog&cid={$C['id']}&time=0&p=1' class='act'>生成HTML</a>|";
				}
			}
			$tr.='<a href="'.__SELF__.'?do=catalog&operation=add&cid='.$C['id'].'" class="act" title="编辑栏目设置">编辑</a>|<a href="'.__SELF__.'?do=catalog&operation=del&id='.$C['id'].'" onClick="return confirm(\'确定要删除此栏目和栏目下的所有文章?\');" class="act">删除</a>';
	  	}else{
	  		$tr.='无权限';
	  	}
	  	$tr.='</td></tr>';
	  	return $tr;
    }
    function url($c){
    	global $iCMS;
		if($c['attr']=='page'){
			$url=$iCMS->iurl('page',array('link'=>$c['dir'],'url'=>$c['url'],'domain'=>$c['domain']));
		}else{
			$url=$iCMS->iurl('list',array('id'=>$c['id'],'link'=>$c['dir'],'url'=>$c['url'],'domain'=>$c['domain']));
		}
		return $url;
    }
    function id($cid = "0",$args=NULL){
    	global $Admin;
		$args && parse_str($args,$T);
		if(isset($this->array[$cid])){
			foreach($this->array[$cid] AS $root=>$C){
				if($Admin->CP($C['id']) && empty($C['url'])){
					if(empty($args)){
						$ID.=$C['id'].",";
					}else{
						isset($T['page'])	&&	$C['attr'] == 'page' 	&& $ID.=$C['id'].",";
						isset($T['channel'])&&	$C['attr'] == 'channel'	&& $ID.=$C['id'].",";
						isset($T['list'])	&&	$C['attr'] == 'list'	&& $ID.=$C['id'].",";
					}
				}
				$ID.=$this->id($C['id'],$args);
			}
		}
//    	var_dump(array_intersect($p,$Admin->cpower));
		return $ID;
    }
    function AJAXid($cid = "0"){
		if(isset($this->array[$cid])){
			foreach($this->array[$cid] AS $root=>$C){
				$ID.=$C['id'].",".$this->id($C['id']);
			}
		}
		return substr($ID,0,-1);
    }
	function select($currentid="0",$cid="0",$level = 1,$args=NULL,$mid='0'){
		global $Admin;
		$args && parse_str($args,$T);
		if(isset($this->array[$cid])){
			foreach($this->array[$cid] AS $root=>$C){
				if($C['mid']==$mid||$C['mid']=="-1"||$mid=='all'){
					if($Admin->CP($C['id'])){
						$t=$level=='1'?"":"├ ";
						$c=$level=='1'?"p3":"p4";
						$selected=($currentid==$C['id'])?"selected='selected'":"";
						if(empty($C['url'])){
							if(empty($args)){
								$option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
							}else{
								isset($T['page'])&&$C['attr']=='page' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[p:{$C['dir']}] </option>";
								isset($T['index'])&&$C['attr']=='index' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
								if(isset($T['channel'])&&$C['attr']=='channel'){
									if($T['channel']){
										$option.="<optgroup label=\"{$C['name']}\"></optgroup>";
									}else{
										$option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}] </option>";
									}
								}
								isset($T['list'])&&$C['attr']=='list' && $option.="<option value='{$C['id']}' class='$c' $selected>".str_repeat("│　", $level-1).$t.$C['name']."[ID:{$C['id']}]</option>";
							}
							$option.=$this->select($currentid,$C['id'],$level+1,$args);
						}
					}else{
						$option.=$this->select($currentid,$C['id'],$level+1,$args);
					}
				}
			}
		}
		return $option;
	}
	function attrname($a,$name='文章',$count=0){
		switch ($a) {
		   case 'list':		$R="{$name}:{$count}";break;
		   case 'channel':	$R='频道封面';break;
		   case 'page':		$R='独立页面';break;
		}
		return $R;
	}
}