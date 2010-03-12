<?php 

/**
	这个文件是Portal模块的一个页面，如果不需要Portal，则可以一并删除
	@param
	@return
*/

define('KC_INDEX',True);

require_once 'global.php';


/*
	默认搜索页，也就是搜索首页
*/
function king_def(){
	global $king;

	$query=kc_get('query',0);

	if(isset($query{0})){//如果有query参数，则调用搜索结果显示页
		call_user_func('king_search');
		exit();
	}

	$king->Load('portal');

	if(!$modelTables=getModelTables()){
		$king->portal->error($king->lang->get('portal/common/error'),$king->lang->get('portal/error/notmodel'));
	}
	$currentArray=current($modelTables);
	$modelid=isset($_GET['modelid']) ? $_GET['modelid'] : $currentArray['modelid'];

	$model=$king->portal->infoModel($modelid);

	foreach($model['field']['issearch'] as $key => $val){
		$getVal=kc_get(substr($key,1),0);
		if(isset($getVal{0})){
			call_user_func('king_search');
			exit();
		}
	}

	$tmp=new KC_Template_class($model['ktemplatesearch'],$king->config('templatepath').'/inside/search/'.strtolower($model['modeltable']).'[home].htm');

	$tmp->assign('title',$king->lang->get('system/common/search'));

	echo $tmp->output();
}
/*

where isshow=1 and (ktitle like '%query%' or k_author like '%query%' or k_source like '%query%')


搜索的项目，query表单搜索所有issearch字段中指定的项目，如果是单独指定的话，就用=查询，如:

_author=sincs

where isshow=1 and k_author='sincs'

*/
/**
	搜索结果显示页

	########## 搜索结果和所属网站做绑定，根据URL判断 ##########
*/
function king_search(){
	global $king;

	$king->Load('portal');

	//获得modelid
	if(!$modelTables=getModelTables()){
		$king->portal->error($king->lang->get('portal/common/error'),$king->lang->get('portal/error/notmodel'));
	}
	$currentArray=current($modelTables);
	$modelid=isset($_GET['modelid']) ? $_GET['modelid'] : $currentArray['modelid'];
	$model=$king->portal->infoModel($modelid);

	$query=kc_get('query',0);

	$querys=preg_split("/[,\*\%\.\(\)\'\`><\}\{ ]/",$query);

	$querys=array_diff($querys,array(''));

	$q=implode("%' or ktitle like '%",$querys);

	if(isset($q{0})){
		$q=" and (ktitle like '%".$q."%')";
	}

	if(is_array($model['field']['issearch'])){
		foreach($model['field']['issearch'] as $key => $val){
			$getVal=kc_get(substr($key,1),0);
			if(isset($getVal{0})){
				$q.=" and $key='".$king->db->escape($getVal)."'";
			}
		}
	}

	foreach($model['field']['id'] as $val){
		$getVal=kc_val($_GET,$val);//kc_get($val,2);
		if(isset($getVal{0})){
			if(kc_validate($getVal,2)){
				$q.=" and $val='$getVal'";
			}else{
				$q.=" and $val in ($getVal)";
			}
		}
	}


	$pid=isset($_GET['pid']) ? kc_get('pid',2,1) :1;
	$rn=isset($_GET['rn']) ? kc_get('rn',2,1) :20;
	if($rn>100) $rn=100;

	$tmp=new KC_Template_class($model['ktemplatesearch'],$king->config('templatepath').'/inside/search/'.strtolower($model['modeltable']).'[page].htm');
	$tmp->assign('type','search');
	$tmp->assign('pid',$pid);
	$tmp->assign('rn',$rn);
	$tmp->assign('search',$q);//传递搜索条件，也就是where条件
	$tmp->assign('modelid',$modelid);//传递模型类型
//	$tmp->assign('siteid');//这个还得获取
	$tmp->assign('title',$king->lang->get('system/common/search'));


	echo $tmp->output();
}
/**
返回搜索列表里的模型信息
*/
function getModelTables(){
	global $king;
	$cachepath='portal/model/table_select';
	$array=$king->cache->get($cachepath,1);
	if(!$array){
		$array=array();

		if(!$res=$king->db->getRows("select modelid,modeltable,modelname,ktemplatesearch from %s_model where issearch=1 order by norder desc;"))
			$res=array();

		foreach($res as $rs){
			$array[$rs['modelid']]=array(
				'modelid'=>$rs['modelid'],
				'modeltable'=>$rs['modeltable'],
				'modelname'=>$rs['modelname'],
				'template'=>$rs['ktemplatesearch'],
			);
		}
		$king->cache->put($cachepath,$array);
	}
	return $array;
}


?> 