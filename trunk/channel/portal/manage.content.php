<?php require_once '../global.php';

/* ======= >>> KingCMS <<< ========================== *

 *   @License      http://www.KingCMS.com/license/    *

 *   @Link         http://www.KingCMS.com/            *

 *   @E-Mail       KingCMS@Gmail.com                  *

 *   Copyright (c) KingCMS.com All Rights Reserved.   *

 * ================================================== */

function king_ajax_delete(){
	global $king;
	$king->access('portal_content_delete');

	$_list=kc_getlist();
	$_array=explode(',',$_list);
	$listid=kc_get('listid',2,1);

	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);

	if(!$_res=$king->db->getRows("select kid,kpath,ktitle from %s__{$model['modeltable']} where kid in ($_list) and listid=$listid;"))
		$_res=array();

	foreach($_res as $rs){

		$kid=$rs['kid'];

		kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml');
		kc_f_delete($king->getfpath($rs['kpath']));

		//写log
		$king->log(6,$model['modeltable'].':'.$rs['ktitle']);
	}

	//更新列表信息
	$king->portal->lastUpdated($listid,'list');

	$king->cache->del('portal/list/'.$listid);

	$king->db->query("delete from %s__{$model['modeltable']} where kid in ($_list) and listid=$listid;");


	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/delete')."</p>",1);

} //!king_ajax_delete
function king_ajax_create_list(){
	global $king;
	$king->access('portal_content_edt');

	$listid=kc_get('listid',2,1);

	list($_msec,$_sec)=explode(' ',microtime());
	$time=$_sec+$_msec;//当前时间

//	整理要生成的内容
//	生成一个javascript函数，用来记录要生成的对象
//	当iframe中的内容生成完毕后激活.

	$s=kc_progress('progress');
	$s.='<div class="none" id="k_progress_iframe">'.kc_htm_iframe('manage.content.php?action=iframe&CMD=create&create=list&time='.$time.'&list='.$listid,502,200,'progress_iframe').'</div>';
	$s.="<script>function moreinfo(){var obj=\$('#progress + div');var o=\$('#k_ajaxBox').offset();if(obj.css('display')=='none'){\$('#k_ajaxMain').height(320);\$('#k_ajaxBox').css('top',o.top-160);\$('#k_ajaxBox').height(320+\$('#k_ajaxTitle').height()+\$('#k_ajaxSubmit').height());obj.show()}else{obj.hide();\$('#k_ajaxMain').height(100);\$('#k_ajaxBox').height(100+\$('#k_ajaxTitle').height()+\$('#k_ajaxSubmit').height());\$('#k_ajaxBox').css('top',o.top+160)}}</script>";

	$but='<a href="javascript:;" onclick="moreinfo()">'.$king->lang->get('portal/common/moreinfo').'</a>';
	$but.='<a href="javascript:;" class="k_close">'.$king->lang->get('system/common/close').'</a>';

	kc_ajax($king->lang->get('portal/title/create'),$s,$but,null,546,100);
}
function king_ajax_create(){
	global $king;

	$king->access('portal_content_edt');

	$list=kc_getlist();
	$array=explode(',',$list);
	$listid=kc_get('listid',2,1);
	$js='';
	foreach($array as $val){
		$js.="$.kc_ajax('{CMD:\'create_one\',ID:\'page_{$val}\',listid:{$listid},IS:2}');";
	}
	$js.='$.kc_close();';
	kc_ajax('','','',$js);
}
function king_ajax_create_one(){
	global $king;
	$king->access('portal_content_edt');
	$ID=kc_get('ID',4,1);
	$kid=substr($ID,5);
	$listid=kc_get('listid',2,1);
	$id=$king->portal->infoID($listid,$kid);
	$info=$king->portal->infoList($listid);

	if($id['kid1']==0){//如果是主题
		if($info['npagenumber']==1){

			$king->portal->createPage($listid,$kid);//$listid,$kid,$pid=1,$is=null

			$subkid=$id['subkid'];
			if($subkid){
				$subid=explode(',',$subkid);
				foreach($subid as $sid){
					$king->portal->createPage($listid,$sid);
				}
			}
		}else{
			$pcount=ceil($id['ncount']/$info['npagenumber']);
			for($i=1;$i<=$pcount;$i++){
				$king->portal->createPage($listid,$kid,$i);
			}
		}
	}else{
		$king->portal->createPage($listid,$kid);
	}


	$kpath=$king->portal->pathPage($info,$kid,$id['kpath']);
	$s=kc_icon('h7',$king->lang->get('system/common/brow'));

	$js="\$('#{$ID}').attr('href','{$kpath}').attr('target','_blank').unbind('click')";

	kc_ajax('',$s,0,$js);
}
 //上移下移
//king_ajax_updown
function king_ajax_updown(){
	global $king;
	$king->access('portal_content_updown');

	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);

	$kid=kc_get('kid',2,1);
	$kid1=kc_get('kid1');

	if($kid1){
//		kc_error(preg_match('/norder +desc/i',$model['kpageorder']));
		if(preg_match('/norder +desc/i',$model['kpageorder'])){//		if(NULL!==strpos(strtolower($model['kpageorder']),'norder')){
			//文章分列列表的排序是倒序排序
			$king->db->updown('%s__'.$model['modeltable'],$kid," listid={$info['listid']} and (kid1={$kid1} or kid={$kid1})",1);
		}else{
			$king->db->updown('%s__'.$model['modeltable'],$kid," listid={$info['listid']} and (kid1={$kid1} or kid={$kid1})",0);
		}

	}else{
		$king->db->updown('%s__'.$model['modeltable'],$kid," listid={$info['listid']} and kid1=0");
	}

} //!king_ajax_updown

function king_ajax_attrib(){
	global $king;
	$king->access('portal_content_edt');

	$kid=kc_get('list',2,1);
	$listid=kc_get('listid',2,1);
	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);

//	$cmd=CMD;
	$field=kc_get('field',1,1);//substr($cmd,0,strlen($cmd)-1);
	$value=kc_get('value',2,1);//substr($cmd,-1,1);

	$array_is=array('nshow','nhead','ncommend','nup','nfocus','nhot');

	if(!in_array($field,$array_is))//防止非法输入
		kc_error($king->lang->get('system/error/param'));

	$king->db->query("update %s__{$model['modeltable']} set $field=$value where kid=$kid;");

	$value ? $ico='n1':$ico='n2';


	//删除缓存重建缓存
	$king->cache->del('portal/list/'.$listid);
	kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml');

	$id=$king->portal->infoID($listid,$kid);
	$kid1=$id['kid1'];
	if($kid1){
		kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid1,1,'/',1).'.xml');
		$king->portal->infoID($listid,$kid1);
	}

	kc_ajax('',kc_icon($ico),0,"$('#{$field}_{$kid}').attr('rel','{CMD:\'attrib\',field:\'$field\',value:".(1-$value).",ID:\'{$field}_{$kid}\',listid:$listid,list:\'$kid\',IS:2}')");


}


 //显示隐藏
//king_ajax_show
function king_ajax_show0(){
	global $king;
	$king->access('portal_content_edt');

	$_list=kc_getlist();
	$listid=kc_get('listid',2,1);
	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);

	$cmd=substr(CMD,4);
	$king->db->query("update %s__{$model['modeltable']} set nshow=$cmd where kid in ($_list);");

	$king->cache->del('portal/list/'.$listid);

	kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('portal/ok/show'.$cmd)."</p>",1);
}
function king_ajax_show1(){
	call_user_func('king_ajax_show0');
}
/**
	移动
*/
function king_ajax_moveto(){
	global $king;
	$king->access('portal_content_edt');
	$list=kc_getlist();

	$listid=kc_get('listid',2,1);
	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);
	$newid=kc_get('newid',2);
	$newinfo=$king->portal->infoList($newid);

	if($newid){
		$check=array(array('newid',12,$king->lang->get('portal/error/moveto'),$info['modelid']!=$newinfo['modelid']),array('newid',12,$king->lang->get('portal/error/moveto1'),$listid==$newid));
		$default=$newid;
	}else{
		$check=array();
		$default=$listid;
	}

	$s=$king->htmForm($king->lang->get('portal/label/moveto'),$king->portal->LinkAge('newid',$default),$check);

	if($GLOBALS['ischeck']&&$newid){
		$array_list=explode(',',$list);
		foreach($array_list as $kid){
			kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml');

		}
		$king->db->query("update %s__{$model['modeltable']} set listid=$newid where kid in ($list) or kid1 in ($list);");

		//更新列表信息
		$king->portal->lastUpdated($listid,'list');
		$king->portal->lastUpdated($newid,'list');

		//删除缓存
		$king->cache->del('portal/list/'.$listid);
		$king->cache->del('portal/list/'.$newid);

		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/goto/ok')."</p>",1);
	}

	kc_ajax($king->lang->get('portal/common/moveto'),$s,kc_htm_a($king->lang->get('system/common/move'),"{CMD:'moveto',list:'$list',listid:$listid}"),null,420,120+$GLOBALS['check_num']*15);

}
/**
检查重复的路径/标题
*/
function king_ajax_ckre(){
	global $king;
	$king->access('portal_content_edt');
	$listid=kc_get('listid',2,1);
	$obj=kc_post('obj');//ktitle或kpath
	$kobj=$_POST[$obj];//提交来的值，也就是文本框中录入的值
	$kid=kc_get('kid');
	$insql='';

	if(!($obj=='ktitle'||$obj=='kpath'))
		kc_ajax('',kc_icon('a7',$king->lang->get('system/common/ckre')),0,"alert('".$king->lang->get('system/error/param')."')");//验证查询对象是否为kpath或ktitle

	if($kobj=='')
		kc_ajax('',kc_icon('a7',$king->lang->get('system/common/ckre')),1,"alert('".$king->lang->get('portal/tip/kobjnot')."')");//若是空值，则返回提示。

	if($kid)
		$insql=' and kid<>'.$kid;

	//kobj不为空的时候，进行验证
	$info=$king->portal->infoList();//获得info数组
	$model=$king->portal->infoModel($info['modelid']);
	$res=$king->db->getRows_one("select count(*) from %s__{$model['modeltable']} where $obj='".$king->db->escape($kobj)."'$insql;");
		$res[0]
		? kc_ajax('',kc_icon('a7'),0,"alert('".$king->lang->get('portal/tip/isexist1')."')")//若有值
		: kc_ajax('',kc_icon('a7'),0,"alert('".$king->lang->get('portal/tip/isexist0')."')");//若为空值
}
/**
自动指定/删除/加载 相关文章
*/
function king_ajax_relate(){
	global $king;
	$king->access('portal_content_edt');
	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);
	$tip='<table class="k_side" cellspacing="0"><tr><td><a class="k_ajax" rel="{CMD:\'relate\',ID:\'krelateshow\',VAL:\'ktitle,listid,kid\'}">'.$king->lang->get('portal/tip/onclick').'</a></td></tr></table>';
	$insql='';

	switch(CMD){
		case 'relate':
			$title=kc_post('ktitle');
			if(isset($title{0})){
				$insql=kc_likey($king->portal->getkey($title),'ktitle','or');
				if(isset($insql{0})){
					$insql=' and '.$insql;
				}else{
					kc_ajax('',$tip,0,'krelateshow();alert(\''.$king->lang->get('portal/tip/notrelate').'\');');
				}
			}else{
				kc_ajax('',$tip,0,'krelateshow();alert(\''.$king->lang->get('portal/tip/ktitle').'['.addslashes($model['field']['text']['ktitle']).']\');');
			}
			//获得kid，不加入自己在关联列表里
			$kid=kc_get('kid');
			if(isset($kid{0}))
				$insql.=' and kid<>'.$kid;
		break;

		case 'relateload':
			$krelate=kc_get('krelate',3);
			isset($krelate{0})
				? $insql=" and kid in ($krelate)"
				: kc_ajax('',$tip);
		break;

		case 'relatedel':
			$kid=kc_get('kid',2,1);//这里获取的kid是要删除的对象
			$krelate=kc_get('krelate',3);
			if(isset($krelate{0})){
				$array_krelate=explode(',',$krelate);
				$array_delkid=array($kid);
				$array_new=array_diff($array_krelate,$array_delkid);
				count($array_new)//只要有项目
					? $insql=' and kid in ('.implode(',',$array_new).')'
					: kc_ajax('',$tip,0,'$(\'#krelate\').val(\'\')');
			}else{
				kc_ajax('','',0,"alert('".$king->lang->get('system/error/param')."')");
			}
		break;

		default:
			kc_ajax('','',0,"alert('".$king->lang->get('system/error/param')."')");
	}

	$sql="select kid,ktitle,kpath from %s__{$model['modeltable']} where nshow=1 $insql order by norder desc limit 20;";

	if($res=$king->db->getRows($sql)){
		$s='<table class="k_side" cellspacing="0">';
		$i=0;
		$array=array();

		foreach($res as $rs){
			(++$i)%3!=1
				? $s.='<tr>'
				: $s.='<tr class="z">';
			$s.='<td>'.kc_icon('c5').'</td>';
			$s.='<td class="tit"><a href="../'.$rs['kpath'].'" target="_blank">'.htmlspecialchars($rs['ktitle']).'</a></td>';
			$s.='<td><a class="k_ajax" rel="{CMD:\'relatedel\',ID:\'krelateshow\',kid:'.$rs['kid'].',VAL:\'listid,krelate\'}">'.kc_icon('j2',$king->lang->get('system/common/del')).'</a></td></tr>';
			$array[]=$rs['kid'];
		}

		$s.='</table>';

		$id=implode(',',$array);
		$js="$('#krelate').val('{$id}');";

		kc_ajax('',$s,0,$js);
	}else{
		$js="$('#krelate').val('');";
		kc_ajax('',$tip,0,$js);
	}
}
function king_ajax_relatedel(){
	call_user_func('king_ajax_relate');
}
function king_ajax_relateload(){
	call_user_func('king_ajax_relate');
}
/**
图片列表
king_ajax_images()
*/
/*
function king_ajax_imagesload(){
	global $king;
	$king->access('portal_content_edt');
	$info=$king->portal->infoList();

	$label=kc_get('label',4,1);//获得标签
	$value=kc_post($label);//获得标签对应的值
	$value_temp=kc_post($label.'_temp');//临时变量的值

	$js='';
	switch(CMD){
		case 'imagesload':
			$array=isset($value{0}) ? unserialize(base64_decode($value)) : array();

			if(isset($value_temp{0})){//如果有temp
				$title=$value_temp;
				//从%s_upfile中读取对应的title
				if($res=$king->db->getRows_one("select ktitle from %s_upfile where kpath='".$king->db->escape($value_temp)."';")){
					$title=$res['ktitle'];
				}
				$array[$value_temp]=array(
					'title'=>$title,
					'image'=>$value_temp,
				);
			}

			if(empty($array)){
				kc_ajax('',$king->lang->get('portal/tip/notpic'));

			}

		break;

		case 'imagesdel':
			$delfile=kc_post('delfile');//要删除的文件名
			$array=isset($value{0}) ? unserialize(base64_decode($value)) : array();
			$array=array_diff_key($array,array($delfile=>''));
			if(!count($array)){//当删除最后一个图片的时候
				$js="\$('#$label,#{$label}_temp').val('');\$('#k{$label}show').html('".addslashes($king->lang->get('portal/tip/notpic'))."');";
				kc_ajax('','',0,$js);
			}
		break;

		case 'imagestitle':
			$file=kc_post('file');//要修改标题的图片文件名
			$title=kc_post($label.'title');
			$array=isset($value{0}) ? unserialize(base64_decode($value)) : array();
			if(isset($title{0}) && $GLOBALS['ismethod']){//如果有标题并且是提交的时候，更新
				$array[$file]=array(
					'image'=>$file,
					'title'=>$title,
				);
				$js="\$.kc_close();";
			}else{
				$title=kc_val($array[$file],'title');
				$s=$king->htmForm($king->lang->get('system/upfile/title'),'<input type="text" name="'.$label.'title" class="k_in w400" value="'.$title.'" />');

				$but=kc_htm_a($king->lang->get('system/common/save'),"{URL:'../portal/manage.content.php',CMD:'imagestitle',file:'$file',label:'$label',VAL:'$label,{$label}_temp,listid',IS:1}");
				$js='';
				kc_ajax('Title',$s,$but,$js,440,80);
			}
		break;

	}

	$js.="\$('#k{$label}show').html(''";
	foreach($array as $arr){
		$js.="+k{$label}js('{$arr['title']}','{$arr['image']}','$label')";
	}
	$js.=");\$.kc_ready('#k{$label}show');";

	$js.="\$('#$label').val('".base64_encode(serialize($array))."');\$('#{$label}_temp').val('')";
	kc_ajax('','',0,$js);
}
function king_ajax_imagesdel(){
	call_user_func('king_ajax_imagesload');
}
function king_ajax_imagestitle(){
	call_user_func('king_ajax_imagesload');
}
*/
/**
文件列表
king_ajax_files()
*/
/*
function king_ajax_filesload(){
	global $king;
	$king->access('portal_content_edt');
	$info=$king->portal->infoList();

	$label=kc_get('label',4,1);//获得标签
	$value=kc_post($label);//获得标签对应的值
	$value_temp=kc_post($label.'_temp');//临时变量的值

	$js='';
	switch(CMD){
		case 'filesload':

			$array=isset($value{0}) ? unserialize(base64_decode($value)) : array();

			if(isset($value_temp{0})){//如果有temp
				$title=$value_temp;
				//从%s_upfile中读取对应的title
				if($res=$king->db->getRows_one("select ktitle from %s_upfile where kpath='".$king->db->escape($value_temp)."';")){
					$title=htmlspecialchars($res['ktitle']);
				}
				$array[$value_temp]=array(
					'title'=>$title,
					'file'=>$value_temp,
				);
			}

			if(empty($array)){
				kc_ajax('',$king->lang->get('portal/tip/notfile'));

			}
		break;

		case 'filesdel':
			$delfile=kc_post('delfile');//要删除的文件名
			$array=isset($value{0}) ? unserialize(base64_decode($value)) : array();
			$array=array_diff_key($array,array($delfile=>''));
			if(!count($array)){//当删除最后一个图片的时候
				$js="\$('#$label,#{$label}_temp').val('');\$('#k{$label}show').html('".addslashes($king->lang->get('portal/tip/notpic'))."');";
				kc_ajax('','',0,$js);
			}
		break;

	}


	$js.="\$('#k{$label}show').html(''";
	foreach($array as $arr){
		$js.="+k{$label}js('{$arr['title']}','{$arr['file']}','$label')";
	}
	$js.=");\$.kc_ready('#k{$label}show');";

	$js.="\$('#$label').val('".base64_encode(serialize($array))."');\$('#{$label}_temp').val('')";
	kc_ajax('','',0,$js);

}
function king_ajax_filesdel(){
	call_user_func('king_ajax_filesload');
}
*/
/**
手工指定相关文章
*/
function king_ajax_hrelate(){
	global $king;
	$king->access('portal_content_edt');

	$s='';

	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);
	$kquery=kc_post('kquery');

	if(isset($kquery{0})){//如果kquery有值，则读取数据库

		$insql=kc_likey($kquery,'ktitle');
		if(isset($insql{0})){
			$insql=' and '.$insql;
		}else{
			$insql=' and isshow=2 ';
		}

		$krelate=kc_get('krelate',3);
		if(isset($krelate{0}))//如果已经有关键字，则不让在搜索列表里显示
			$insql.=" and kid not in ($krelate)";

		$kid=kc_get('kid');
		if(isset($kid{0}))
			$insql.=" and kid<>$kid";

		$sql="select kid,ktitle,kpath from %s__{$model['modeltable']} where nshow=1 $insql limit 20;";

		$s='<div id="kqueryshow" class="k_in w400">';
		if($res=$king->db->getRows($sql)){

			foreach($res as $rs){
				$id=isset($krelate{0})
					? $krelate.','.$rs['kid']
					: $rs['kid'];
				$s.="<p>".kc_icon('c5')."<a href=\"javascript:;\" onclick=\"\$('#krelate').val('$id');krelateshow();\$.kc_ajax('{CMD:\'hrelate\',VAL:\'krelate,listid,kid,kquery\',IS:1}')\">".htmlspecialchars($rs['ktitle']).'</a></p>';
			}//$kc_ajax(\'manage.content.php?action=ajax\',\'k_ajax\',\'cmd=hrelate&\'+kc_getvalue(\'krelate,listid,kid,kquery\'),1)"

		}else{
			$s.='<p>'.$king->lang->get('system/error/notre').'</p>';
		}

		$s.='</div>';

	}
//$.kc_ajax(\'manage.content.php?action=ajax\',\'k_ajax\',\'cmd=hrelate&\'+kc_getvalue(\'krelate,listid,kid,kquery\'),1)
	$content='<input value="'.htmlspecialchars($kquery).'" id="kquery" type="text" class="k_in w350"/><a href="javascript:;" class="k_ajax" rel="{CMD:\'hrelate\',VAL:\'krelate,listid,kid,kquery\',IS:1}">'.kc_icon('m1').'</a>'.$s;

	$s=$king->htmForm($king->lang->get('portal/label/find'),$content);

	kc_ajax($king->lang->get('portal/title/relate'),$s,0,'',435,360);
}
/**
关键字自动完成
*/
function king_ajax_keywords(){
	global $king;
	$king->access('portal_content_edt');
	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);
	$ktitle=kc_post('ktitle');
	$kkeywords=kc_post('kkeywords');
//kc_error($ktitle);
	if(isset($ktitle{0})){//如果标题不为空,则读取关键字列表进行比较
		if(isset($kkeywords{0})){
			$js='alert(\''.$king->lang->get('portal/tip/kkey').'\');$.kc_close();';
			kc_ajax('',null,0,$js);
		}
		$key=$king->portal->getkey($ktitle,$kkeywords);
		if(isset($key{0})){
			$js='$(\'#kkeywords\').val(\''.$key.'\');';
		}else{
			$js='alert(\''.$king->lang->get('portal/tip/notkey').'\');';
		}
	}else{
		$js='alert(\''.$king->lang->get('portal/tip/ktitle').'['.addslashes($model['field']['text']['ktitle']).']\');';
	}

	kc_ajax('',null,0,$js."\$.kc_close();");

}
/**
Tag自动完成
*/
function king_ajax_tag(){
	global $king;
	$king->access('portal_content_edt');
	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);
	$ktitle=kc_post('ktitle');
	$ktag=kc_post('ktag');
//	kc_error($ktitle);
	if(isset($ktitle{0})){//如果标题不为空,则读取关键字列表进行比较
		/**/
		if(isset($ktag{0})){
			$js='alert(\''.$king->lang->get('portal/tip/ktag').'\');$.kc_close();';
			kc_ajax('',null,0,$js);
		}
		/**/
		$key=$king->portal->getTag($ktitle,$ktag);
//		kc_error($key);
		if(isset($key{0})){
			$js='$(\'#ktag\').val(\''.$key.'\');';
		}else{
			$js='alert(\''.$king->lang->get('portal/tip/nottag').'\');';
		}
	}else{
		$js='alert(\''.$king->lang->get('portal/tip/ktitle').'['.addslashes($model['field']['text']['ktitle']).']\');';
	}

	kc_ajax('',null,0,$js."\$.kc_close();");

}


/* ------>>> KingCMS for PHP <<<--------------------- */

function king_inc_location($safetime,$url){//,$stat
	//每生成一次后，比较一下生成时间和安全周期，如果超出了安全时间，则跳转
	$runtime=kc_script_runtime();//已执行时间
	if($runtime>$safetime && $safetime!=0){//超出安全期，跳转
		$location=$url.'&time='.$_GET['time'];
		exit('<script>window.location=\''.$location.'\'</script>');
	}
}

/**
	生成页面
	对页面进行判断并分割页面
*/
function king_iframe_create(){
	global $king;

	echo '<html><head><script type="text/javascript" charset="UTF-8" src="../system/js/jquery.js"></script><style type="text/css">p{font-size:12px;padding:0px;margin:0px;line-height:14px;width:450px;white-space:nowrap;}</style><meta http-equiv="Content-Type" content="text/html; charset='.PAGE_CHARSET.'" /></head><body></body></html>';

/*

生成方式分类
page  只生成页面
NULL  列表及页面
list  只生成列表
not   只生成未生成内容
all   全部

传递继续项目的时候URL参数
action=iframe
list=列表页的ID

listid  当前正在生成中的列表id
	pid  如果是正在生成列表页的时候，则带pid参数
	kid pagid pid  列表生成完成，正在生成中的kid,pagid 分页id,pid 每页20条数据的时候所属的段

*/

	$max_execution_time=ini_get('max_execution_time');//安全的执行时间
	if($max_execution_time>=20){
		$safetime=$max_execution_time-10;//10秒的余地是比较保险的时间
	}else{
		$safetime=round($max_execution_time/3,4);
	}

	$create=$_GET['create'];
	$Listid=isset($_GET['listid']) ? $_GET['listid'] : '';//url中获得的listid，和下面的listid不要混淆

	if($create=='all'){
		$lists=$king->portal->getListids();
	}else{
		$list=kc_get('list',3,1);
		$lists=explode(',',$list);
	}
	sort($lists,SORT_NUMERIC);//对listid值进行排序
	if($Listid){
		$lists_key=array_search($Listid,$lists);//返回lists数组和listid对应的键值
		$lists=array_slice($lists,$lists_key);//删除小于$Listid的值
	}
	$lists_count=count($lists);


	foreach($lists as $listid){
		$info=$king->portal->infoList($listid);
		//生成列表
		if(
			!(isset($_GET['kid']) && $Listid==$listid) //传递的listid和当前的listid相同 并 没有kid值
			&& (($Listid==$listid && isset($_GET['pid'])) || $Listid!=$listid) //(传递的listid和当前listid相同 并 有pid值) 或 (传递的listid和当前listid不同)
			&& ($info['nlist']==0 && $info['klistpath'] ) //$info['nlist']即指定为静态页 或 单页有路径的时候
			//下面的可以说是公共条件，不用管之
			&& ($info['modelid']>0 || (int)$info['modelid']===0) //文章 或 单页模型都需要生成  --- 是不是直接判断是否为链接类型比较好呢，非链接类型的生成？
			&& in_array($create,array('','list','all')) //$create是生成类型，当取值为空()，list和all时生成
			){

			$pid= ($listid==$Listid && isset($_GET['pid'])) ? $_GET['pid'] : 1;
//			kc_error('<pre>'.print_r($info,1));
			$pcount=($info['modelid'] >0) ? $info['pcount'] :0;


			if( (int)$pcount===0 ){//没有分页的情况，可能是空的列表首页或者单页

				list($msecSta,$secSta)=explode(' ',microtime());//计算 开始生成用时

				$king->portal->createList($listid);

				list($msecEnd,$secEnd)=explode(' ',microtime());//计算 结束生成用时

				$timeDiff=kc_formattime($msecEnd+$secEnd-$msecSta-$secSta);//生成用时
				$timeSleep=kc_formattime(($msecEnd+$secEnd-$msecSta-$secSta)*$king->config('proptime'));//休眠时间

				echo kc_progress('progress',$king->lang->get('portal/progress/create/list').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',1,1,"<p>".$king->lang->get('portal/progress/success')." [{$info['klistname']}|$listid] [1/1] ".$king->lang->get('portal/progress/when').":$timeDiff ".$king->lang->get('portal/progress/sleep').":$timeSleep</p>");

				king_inc_location(
					$safetime,"manage.content.php?action=iframe&CMD=create&create="
					.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
					."&listid=$listid");
				flush();

			}else{
				for($i=$pid;$i<=$pcount;$i++){

					list($msecSta,$secSta)=explode(' ',microtime());//计算 开始生成用时

					$king->portal->createList($listid,$i);

					list($msecEnd,$secEnd)=explode(' ',microtime());//计算 结束生成用时

					$timeDiff=kc_formattime($msecEnd+$secEnd-$msecSta-$secSta);//生成用时
					$timeSleep=kc_formattime(($msecEnd+$secEnd-$msecSta-$secSta)*$king->config('proptime'));//休眠时间

					echo kc_progress('progress',$king->lang->get('portal/progress/create/list').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',$i,$pcount,"<p>".$king->lang->get('portal/progress/success')." [{$info['klistname']}|$listid] [$i/$pcount] ".$king->lang->get('portal/progress/when').":$timeDiff ".$king->lang->get('portal/progress/sleep').":$timeSleep</p>");
					/*
						listid 生成中的listid
						pid 生成到pid页
					*/
					king_inc_location(
						$safetime,"manage.content.php?action=iframe&CMD=create&create="
						.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
						."&listid=$listid&pid=".($pcount>$i?($i+1):''));
					flush();
				}
			}
		}

		//生成页面
		if($info['npage']==0 && in_array($create,array('','page','all','not')) && (($Listid==$listid && isset($_GET['kid']))||$Listid!=$listid)){
			$model=$king->portal->infoModel($info['modelid']);
			$kid= ($listid==$Listid && $_GET['kid']) ? $_GET['kid'] :1;
			$sql= $create=='not' ? 'kid,kpath,ktitle' : 'kid,ktitle';

			$pid= ceil($info['ncount']/20);//每次读取20条数据，否则读取太多会出现无法打开的情况，特别是数据库和php服务器分开的情况下

			$pid_start= isset($_GET['pid']) ? $_GET['pid'] : 1;

			$k=1+($pid_start-1)*20;
			for($j=$pid_start;$j<=$pid;$j++){//分页

				if($res=$king->db->getRows("select $sql from %s__{$model['modeltable']} where listid=$listid and kid1=0 and nshow=1 order by kid desc",1,$j,20)){
					foreach($res as $rs){

						if(isset($_GET['kid'])){
							if($_GET['kid']<$rs['kid'] || ($_GET['pagid']=='next' && $_GET['kid']==$rs['kid']) ){//如果pagid
								$k++;
								continue;
							}
						}

						$id=$king->portal->infoID($listid,$rs['kid']);

						//生成分页面
						if($info['npagenumber']==1){//每页显示数为1的时候

							$subkids=explode(',',$id['subkid'] ? $rs['kid'].','.$id['subkid'] : $rs['kid']);

							//生成次页
							$pagid= $rs['kid']==isset($_GET['kid']) && isset($_GET['pagid']) ? $_GET['pagid']:1;
							$pcount=count($subkids);
							for($i=$pagid;$i<=$pcount;$i++){

								list($msecSta,$secSta)=explode(' ',microtime());//计算 开始生成用时
								if($create=='not'){//生成未生成内容
									if($i==1){//第一个为主题页
										$filepath=$id['kfilepath'];
									}else{
										$id1=$king->portal->infoID($listid,$subkids[$i-1]);
										$filepath=$id1['kfilepath'];
									}

									if(is_file(ROOT.$filepath)){//如果文件存在，则跳出这次循环
										echo kc_progress('progress',$king->lang->get('portal/progress/create/page').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',$k,$info['ncount'],"<p>".$king->lang->get('portal/progress/success')." ID:".$rs['kid'].'('.$i."/".$pcount.") ".$king->lang->get('portal/progress/exist').": ".htmlspecialchars($rs['ktitle'])."</p>");
										flush();
										king_inc_location(
											$safetime,"manage.content.php?action=iframe&CMD=create&create="
											.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
											."&listid=$listid&pid=$j&kid=".($pcount>$i?$rs['kid']."&pagid=".($i+1):$rs['kid']."&pagid=next")
										);
										continue;
									}
								}

								$king->portal->createPage($listid,$subkids[$i-1]);
								list($msecEnd,$secEnd)=explode(' ',microtime());//计算 结束生成用时
								$timeDiff=kc_formattime($msecEnd+$secEnd-$msecSta-$secSta);//生成用时
								$timeSleep=kc_formattime(($msecEnd+$secEnd-$msecSta-$secSta)*$king->config('proptime'));//休眠时间

								echo kc_progress('progress',$king->lang->get('portal/progress/create/page').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',$k,$info['ncount'],"<p>".$king->lang->get('portal/progress/success')." ID:".$rs['kid'].'('.$i."/".$pcount.") ".$king->lang->get('portal/progress/when').":$timeDiff ".$king->lang->get('portal/progress/sleep').":$timeSleep ".htmlspecialchars($rs['ktitle'])."</p>");
								flush();
								king_inc_location(
									$safetime,"manage.content.php?action=iframe&CMD=create&create="
									.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
									."&listid=$listid&pid=$j&kid=".($pcount>$i?$rs['kid']."&pagid=".($i+1):$rs['kid']."&pagid=next")
								);

							}
						}else{
							//当文件分页每页显示数不为1的时候
							$pcount=ceil($id['ncount']/$info['npagenumber']);
							for($i=1;$i<=$pcount;$i++){

								if($create=='not'){//生成未生成内容
									$filepath=$king->portal->pathPage($info,$rs['kid'],$rs['kpath'],1,1);

									if(is_file(ROOT.$filepath)){//如果文件存在，则跳出这次循环
										echo kc_progress('progress',$king->lang->get('portal/progress/create/page').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',$k,$info['ncount'],"<p>".$king->lang->get('portal/progress/success')." ID:".$rs['kid'].'('.$i."/".$pcount.") ".$king->lang->get('portal/progress/exist').": ".htmlspecialchars($rs['ktitle'])."</p>");
										flush();
										king_inc_location(
											$safetime,"manage.content.php?action=iframe&CMD=create&create="
											.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
											."&listid=$listid&pid=$j&kid=".($pcount>$i?$rs['kid']."&pagid=".($i+1):$rs['kid']."&pagid=next")
										);
										continue;
									}
								}

								list($msecSta,$secSta)=explode(' ',microtime());//计算 开始生成用时
								$king->portal->createPage($listid,$rs['kid'],$i);
								list($msecEnd,$secEnd)=explode(' ',microtime());//计算 结束生成用时
								$timeDiff=kc_formattime($msecEnd+$secEnd-$msecSta-$secSta);//生成用时
								$timeSleep=kc_formattime(($msecEnd+$secEnd-$msecSta-$secSta)*$king->config('proptime'));//休眠时间

								echo kc_progress('progress',$king->lang->get('portal/progress/create/page').' ('.$king->lang->get('portal/progress/remainder').':'.($lists_count-1).')',$k,$info['ncount'],"<p>".$king->lang->get('portal/progress/success')." ID:".$rs['kid'].'('.$i."/".$pcount.") ".$king->lang->get('portal/progress/when').":$timeDiff ".$king->lang->get('portal/progress/sleep').":$timeSleep ".htmlspecialchars($rs['ktitle'])."</p>");
								flush();
								/*
									listid 当前生成中的listid
									pid 生成到第pid页，这个pid分页和list里的pid不同，每次读取20条内容数据
									kid 当前生成中的页面id，是否生成列表的区别在于这个kid，若没有kid值，则是生成列表
								*/
								king_inc_location(
									$safetime,"manage.content.php?action=iframe&CMD=create&create="
									.($create=='all' ? 'all' : $create."&list=".kc_get('list',3,1))
									."&listid=$listid&pid=$j&kid=".($pcount>$i?$rs['kid']."&pagid=".($i+1):$rs['kid']."&pagid=next")
								);
							}
						}
						$k++;
					}
				}
			}
		}
		$lists_count--;
	}


	list($_msec,$_sec)=explode(' ',microtime());
	$thistime=$_sec+$_msec;

	$diffstart=$thistime-$_GET['time'];//开始时间差
	$str=$king->lang->get('system/progress/alltime').': '.kc_formattime($diffstart);
	echo '<script>window.parent.$.kc_progress(\'progress\',\''.$king->lang->get('system/progress/ok').'\',\''.$str.'\',500)</script>';


}

/* ------>>> KingCMS for PHP <<<--------------------- */

function king_inc_list(){
	global $king,$action;

	$info=$king->portal->infoList();

	$kid=kc_get('kid');
	$kid1=kc_get('kid1');
	$listid=$info['listid'];

	$left=array();
	$right=array();

	if($king->acc('portal_list'))
		$left[]=array(
			'href'=>'manage.php',
			'ico'=>'a1',
			'title'=>$king->lang->get('portal/title/listhome')
		);
	$left['']=array(
		'href'=>'manage.content.php?listid='.$info['listid'],
		'ico'=>'d5',
		'title'=>$king->lang->get('portal/list/contentlist'),
	);
	if($kid){
		$left['edt']=array(
			'href'=>'manage.content.php?action=edt&listid='.$info['listid'].'&kid='.$kid,
			'ico'=>'e5',
			'title'=>$king->lang->get('system/common/edit'),
		);
	}else{
		$left['edt']=array(
			'href'=>'manage.content.php?action=edt&listid='.$info['listid'],
			'ico'=>'f5',
			'title'=>$king->lang->get('system/common/add'),
		);
	}

	if(($action=='edt'&&$kid)||$kid1){
		$kid1 ? $nkid=$kid1 : $nkid=$kid;
		$left['pag']=array(
			'href'=>'manage.content.php?action=pag&listid='.$listid.'&kid1='.$nkid,
			'ico'=>'e8',
			'title'=>$king->lang->get('portal/title/plist'),
		);
		$left['edtpag']=array(
			'href'=>'manage.content.php?action=edtpag&listid='.$listid.'&kid1='.$nkid,
			'ico'=>'h8',
			'title'=>$king->lang->get('portal/title/addpage'),
		);
	}

	if($king->acc('portal_list_edt')&&$action==''){
		$right[]=array(
			'href'=>'manage.php?action=edt&listid='.$info['listid'],
			'ico'=>'i1',
			'title'=>$king->lang->get('portal/title/listattrib'),
		);

		$array=array(
			'listid'=>$info['listid'],
			'kpath'=>$info['klistpath'],
		);
		$right[]=array(
			'href'=>$king->portal->pathList($info),
			'ico'=>'h7',
			'title'=>$king->lang->get('portal/list/browlist'),
			'target'=>'_blank',
		);
	}

	return array($left,$right);

}

/* ------>>> KingCMS for PHP <<<--------------------- */

 //默认执行页面
//king_def
function king_def(){
	global $king,$action;
	$king->access("portal_content");

	$info=$king->portal->infoList();
	$model=$king->portal->infoModel($info['modelid']);
	$listid=$info['listid'];
	$site=$king->portal->infoSite($info['siteid']);
	$kid1='';

	$islist_keys=count($model['field']['islist']) ? array_keys($model['field']['islist']):'';

	$islist=isset($islist_keys{0})?','.implode(',',$islist_keys):'';
	$sql="kid,kid1,ktitle,kpath,kimage,nshow,nhead,ncommend,nup,nfocus,nhot,nprice,ncount{$islist}";

	switch($action){
		case '':
			$insql=" and kid1=0";
			$order=$model['klistorder'];//" norder desc,kid desc";

			$sql_count=$info['ncountall'];//'select count(*) from %s__'.$model['modeltable'].' where listid='.$listid.';';
		break;

		case 'pag':
			$kid1=kc_get('kid1',2,1);
			$insql=" and (kid1={$kid1} or kid={$kid1})";
			$order=$model['kpageorder'];//" norder desc,kid desc";
			//$order=" norder asc,kid asc";
			if(False===$id=$king->portal->infoID($listid,$kid1)){
				exit("<script>parent.location='manage.content.php?listid=$listid'</script>");
			}
			$sql_count=$king->db->getRows_number('%s__'.$model['modeltable'],"listid={$listid} and (kid={$kid1} or kid1={$kid1})");
		break;
	}

	$order=isset($order{0}) ? "order by $order" : '';

	$_sql="select $sql from %s__{$model['modeltable']} where listid={$listid}{$insql} {$order}";

	if(!$_res=$king->db->getRows($_sql,1))
		$_res=array();

	//准备开始列表
	if((($info['npage']==0) || ($info['nlist']==0)) && (!$kid1 || $info['npagenumber']==1)){
		$_cmd=array();
		$_cmd[]=$king->lang->get('system/common/create');
		if($info['npage']==0)
			$_cmd['create']=$king->lang->get('portal/cmd/createpage');

		if($info['nlist']==0){
			$_cmd['create_list']=$king->lang->get('portal/cmd/createlist');
		}


	}else{
		$_cmd=array();
	}

	$_cmd[]=$king->lang->get('system/common/del');
	$_cmd=array_merge($_cmd,array(
		'delete'=>$king->lang->get('system/common/del'),
		$king->lang->get('system/common/setting'),
		'show1'=>$king->lang->get('portal/common/show1'),
		'show0'=>$king->lang->get('portal/common/show0'),
	));
	if(!$kid1){
		$_cmd=array_merge($_cmd,array(
			$king->lang->get('system/common/move'),
			'moveto'=>$king->lang->get('portal/common/moveto'),
		));
	}

	$_manage=(int)$info['npage']===3 ? "''" : "isexist(K[0],K[4],K[14])";
	$_manage.="+'<a href=\"manage.content.php?action=edt&listid={$listid}&kid='+K[0]+'&kid1='+K[1]+'\">'+$.kc_icon('e5','".$king->lang->get('system/common/edit')."')+'</a>'";
	$_manage.="+'<a class=\"k_ajax\" rel=\"{CMD:\'delete\',list:'+K[0]+',listid:$listid}\">'+$.kc_icon('g5','".$king->lang->get('system/common/del')."')+'</a>'";
	if($action==''){
		$_manage.="+'<a href=\"manage.content.php?action=pag&listid={$listid}&kid1='+K[0]+'\">'+$.kc_icon('e8','".$king->lang->get('portal/common/pagelist')."')+'</a>'";
		$_manage.="+'<a href=\"manage.content.php?action=edtpag&listid={$listid}&kid1='+K[0]+'\">'+$.kc_icon('h8','".$king->lang->get('portal/common/addpage')."')+'</a>'";
		if(strpos(strtolower($model['klistorder']),'norder')){
			$_manage.="+$.kc_updown(K[0],'updown')";//up
		}
		$_js=array("\$.kc_list(K[0],K[3],'manage.content.php?listid={$listid}&action='+(K[13]==1?'edt&kid='+K[0]:'pag&kid1='+K[0]),{$model['isid']},1,null,null,null,iskimage(K[5]))");
	}else{
		if(NULL!==strpos(strtolower($model['kpageorder']),'norder')){
			$_manage.="+$.kc_updown(K[0],'updown')";//up
		}
		$_js=array("\$.kc_list(K[0],K[3],'manage.content.php?action=edt&listid={$listid}&kid1='+K[1]+'&kid='+K[0],{$model['isid']},1,null,null,null,iskimage(K[5]))");
	}
	$_js[]=$_manage;
	$nattrib=array('nshow','nhead','ncommend','nup','nfocus','nhot');//默认的nattrib选项
	$th='';
	$i=6;
	if($action==''){//首页
		//属性
		foreach($nattrib as $val){
			if(array_key_exists($val,$model['field']['isadmin1'])){
				$th.=',\'<i>'.$king->lang->get('portal/label/attrib/is'.substr($val,1)).'</i>\'';
				$_js[]="'<i>'+isset('manage.content.php',K[0],'$val',K[$i])+'</i>'";
				$i++;

			}
		}
		//价格
		if(array_key_exists('nprice',$model['field']['isadmin1'])){
			$th.=',\'<b>'.addslashes($model['field']['isadmin1']['nprice']).'</b>\'';
			$_js[]="'<b>'+K[12]+'</b>'";
		}
	}else{//次页
		//属性
		foreach($nattrib as $val){
			if(array_key_exists($val,$model['field']['isadmin2'])){
				$th.=',\'<i>'.$king->lang->get('portal/label/attrib/is'.substr($val,1)).'</i>\'';
				$_js[]="'<i>'+isset('manage.content.php',K[0],'$val',K[$i])+'</i>'";
				$i++;

			}
		}
		//价格
		if(array_key_exists('nprice',$model['field']['isadmin2'])){
			$th.=',\'<b>'.addslashes($model['field']['isadmin2']['nprice']).'</b>\'';
			$_js[]="'<b>'+K[12]+'</b>'";
		}
	}
	//显示扩展字段
	$count_islist=count($model['field']['islist']);
	for($i=0;$i<$count_islist;$i++){
		$_js[]="'<i>'+\$.kc_nbsp(K['".($i+15)."'])+'</i>'";
	}
	//统计
	if($action=='')
		$_js[]="'<i>'+K[13]+'</i>'";

	//右键菜单
	$_right=array(
		$king->lang->get('system/common/add') => array(
			'href'=>'manage.content.php?action=edt&listid='.$listid,
			'ico'=>'f5',
		),
	);
	if($action=='pag'){
		$_right[$king->lang->get('portal/title/addpage')]=array(
			'href'=>"manage.content.php?action=edtpag&listid=$listid&kid1=$kid1",
			'ico'=>'h8',
		);
	}
	$_right[]='-';
	if($info['npage']==0)
		$_right[$king->lang->get('system/common/create')]=array(
			'href'=>"{CMD:'create',FORM:'k_form_list'}",
			'ico'=>'d7',
		);
	$_right[$king->lang->get('system/common/del')]=array(
		'href'=>"{CMD:'delete',FORM:'k_form_list'}",
		'ico'=>'g5',
	);


	$s=$king->openList($_cmd,$_right,$_js,$king->db->pagelist('manage.content.php?pid=PID&rn=RN&listid='.$listid,$sql_count),array('listid'=>$listid,'kid1'=>$kid1));//&kid1={$kid1}
//	$s.="function ispath(){};";
	$s.="function isset(url,id,attrib,is){var I1,ico;is?ico='n1':ico='n2';if(is==2) ico='n3';";
	$s.="I1='<a id=\"'+attrib+'_'+id+'\" class=\"k_ajax\" rel=\"{CMD:\'attrib\',field:\''+attrib+'\',value:'+ (is==2 ? 0 : 1-is) +',ID:\''+attrib+'_'+id+'\',listid:$listid,list:'+id+',IS:2}\" >'+$.kc_icon(ico)+'</a>';return I1;};";

	$s.="function isexist(id,path,is){var I1;
	if({$info['npagenumber']}==1 || '$kid1'==''){
		if({$info['npage']}==0){
			I1=(is?'<a id=\"page_'+id+'\" href=\"'+path+'\" target=\"_blank\">'+$.kc_icon('h7','".$king->lang->get('system/common/brow')."')+'</a>':'<a id=\"page_'+id+'\" href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'create_one\',ID:\'page_'+id+'\',listid:{$listid},IS:2}\">'+$.kc_icon('i7')+'</a>')
		}else{
			I1='<a href=\"'+path+'\" target=\"_blank\">'+$.kc_icon('h7','".$king->lang->get('system/common/brow')."')+'</a>'
		}
	}else{
		I1=''
	}
	return I1};";

	$s.="function iskimage(img){
		var s;
		if(img==''){
			s='';
		}else{

			s=img.match(/^[a-zA-Z]{3,10}:\/\/[^\s]+$/)
				?'<a href=\"'+img+'\" target=\"_blank\">'
				:'<a href=\"../'+img+'\" target=\"_blank\">';
			s+=\$.kc_icon('i6')+'</a>';
		}
		return s;
	};";

	$str ="ll('".addslashes($model['field']['text']['ktitle'])."','manage'{$th}";//TH标题及Manage
	if($model['field']['islist']){
		foreach($model['field']['islist'] as $val){
			$str.=',\'<i>'.addslashes(htmlspecialchars($val)).'</i>\'';//TH列表里的自定义显示项
		}

	}
	if($action=='')//TH统计
		$str.=",'<i>".$king->lang->get('portal/common/pcount')."</i>'";
	$str.=",1);";
	$s.=$str;

	foreach($_res as $rs){	 //td

		if($info['npage']==0){
			$kpath=$king->portal->pathPage($info,$rs['kid'],$rs['kpath'],1, 1);//根相对地址
			$isexist=is_file(ROOT.$kpath) ? 1 : 0;
		}else{
			$isexist=1;
		}
		$kpath=$king->portal->pathPage($info,$rs['kid'],$rs['kpath']);

		$str='ll('.$rs['kid'].','.$rs['kid1'].','.$listid.',\''.addslashes(htmlspecialchars($rs['ktitle'])).'\',\''.addslashes($kpath).'\',\''.addslashes(htmlspecialchars($rs['kimage'])).'\','.$rs['nshow'].','.$rs['nhead'].','.$rs['ncommend'].','.$rs['nup'].','.$rs['nfocus'].','.$rs['nhot'].',\''.number_format($rs['nprice'],2,'.',',').'\','.$rs['ncount'].','.$isexist;
		foreach($model['field']['islist'] as $key=>$val){
			$str.=',\''.addslashes($rs[$key]).'\'';
		}
		$str.=',0);';
		$s.=$str;
	}

	//结束列表
	$s.=$king->closeList();


/**
//添加测试数据
$str='';
for($i=0;$i<39;$i++){
	$str.=kc_random('120')."<br/>\n";//str_repeat("-/", 100);
}
$max=$king->db->neworder('%s__'.$model['modeltable']);

$j=0;
for($i=0;$i<787;$i++){
	$n=$i+$max;
	$_array=array(
	'ktitle'=>"添加测试文章，标题是{$n}".kc_random('32'),
	'kpath'=>"{$model['modeltable']}/$n.htm",
	'kcontent'=>$str,
	'norder'=>$n,
	'listid'=>$listid,
	'kid1'=>$kid1?$kid1:0,
	'ndate'=>$i,
	'nlastdate'=>$i,
	);

	$newkid=$king->db->insert('%s__'.$model['modeltable'],$_array);

	if($j==15){
		$kid1=0;
		$j=0;
	}
	if($j==1){
		$kid1=$newkid;
	}
	$j++;
}
$king->cache->del('portal/list/'.$listid);;

/**/


	list($left,$right)=king_inc_list();
	$king->skin->output($info['klistname'],$left,$right,$s);


}
function king_pag(){
	call_user_func('king_def');
}
function king_edtpag(){
	call_user_func('king_edt');
}
function king_edt(){
	global $king;
	$king->access('portal_content_edt');

	//初始化
	$listid=kc_get('listid',2,1);//$info['listid'];
	$info=$king->portal->infoList($listid);
	$model=$king->portal->infoModel($info['modelid']);

//	kc_error('<pre>'.print_r($array_field,1));
	$kid=kc_get('kid',2);
	$kid1=kc_get('kid1',2);
	$isadmin=$kid1 ? 'isadmin2' : 'isadmin1';//次页:首页
	$array_field=array_keys($model['field'][$isadmin]);
	$sql_field=implode(',',$array_field);//[tablemodel]字段调用
//	$listid=kc_get('listid',2);


	if($GLOBALS['ismethod']||$kid==''){//POST过程或新添加的过程
		$data=$_POST;
		if(!$GLOBALS['ismethod']){	//初始化新添加的数据
			$data['kpath']=$king->portal->depathMode($info);

			$data['nshow']=1;
			$array_field_default=$model['field']['default'];
			foreach($array_field_default as $key => $val){
				$data[$key]=$val;
			}
		}
	}else{//编辑数据，从数据库读出
		if(!$data=$king->db->getRows_one('select '.$sql_field.' from %s__'.$model['modeltable'].' where kid='.$kid.' limit 1;'))
			kc_error($king->lang->get('system/error/param').'<br/>select '.$sql_field.' from %s__'.$model['modeltable'].' where kid='.$kid.' limit 1;'.'<br/>File:'.basename(__FILE__).';Line:'.__LINE__);
	}
	$data=kc_data($array_field,$data);

	$data['kid']=$kid;
	if(!$res=$king->db->getRows("select * from %s_field where modelid={$info['modelid']} and {$isadmin}=1 and kid1=0 order by norder,kid;"))//全部调用
		$res=array();

	$s=$king->openForm('manage.content.php?action=edt');
	$s.=kc_htm_hidden(array('listid'=>$listid,'kid'=>$kid,'kid1'=>$kid1));//这个隐藏域不要放在下面
//kc_error("select * from %s_field where modelid={$info['modelid']} and {$isshow}=1 and kid1=0 order by norder,kid;");
//kc_error('<pre>'.print_r($res,1));
	foreach($res as $rs){
		$s.=$king->portal->formdecode($rs,$data,$info,1,($kid1?2:1));
	}

	$s.=$king->htmForm($king->lang->get('portal/common/exp'),kc_htm_checkbox('pag',array(1=>$king->lang->get('portal/goto/addpag')),kc_post('pag')));

	$s.=$king->closeForm('save');

	//数据处理
	if($GLOBALS['ischeck']){

		$_array=array();//设置为空数组

		//收集字段的值
		foreach($array_field as $val){
			if(in_array($val,array('nshow','nhead','ncommend','nup','nfocus','nhot')) || array_key_exists($val,$model['field']['offon'])){

				//增加判断offon
				$_array[$val] = $data[$val] ? 1:0;
			}else{
				if(is_array($data[$val])){
					$_array[$val]=implode(',',$data[$val]);
				}else{
					$_array[$val]=$data[$val];
				}
				//抓图和过滤链接
				if($val=='kcontent'){
					if(kc_post('isgrab')){//抓图
						$_array[$val]=kc_grab($_array[$val]);
					}

					if(kc_post('isremovea')){//过滤链接
						$_array[$val]=preg_replace('/<a ([^>]*)>|<\/a>/is','',$_array[$val]);
					}
					if(kc_post('isremovetable')){//过滤表格
						$_array[$val]=preg_replace('/<(table|tbody|thead|tr|td|th|caption) ?([^>]*)>|<\/(table|tbody|thead|tr|td|th|caption)>/is','',$_array[$val]);
					}
					if(kc_post('isremovestyle')){//过滤样式
						$_array[$val]=preg_replace('/(<([^>]*))( style=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$_array[$val]);
					}
					if(kc_post('isremoveid')){//过滤样式
						$_array[$val]=preg_replace('/(<([^>]*))( id=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$_array[$val]);
					}
					if(kc_post('isremoveclass')){//过滤样式
						$_array[$val]=preg_replace('/(<([^>]*))( class=)(["\'])(.*?)\4(([^>]*)\/?>)/is','$1 $6',$_array[$val]);
					}
				}
			}
		}

		if(in_array('kimage',$_array) && in_array('kcontent',$_array)){//如果有选择第一个图作为缩略图 并 kimage在列表里
			if(kc_post('isoneimage')){//抓第一张图为缩略图
				if($oneimage=preg_match('/(<img([^>]*))( src=)(["\'])(.*?)\4(([^>]*)\/?>)/is',$_array['kcontent'],$oneimage_array)){
					$smartimg=$oneimage_array[5];
					if(is_file(ROOT.substr($smartimg,strlen($king->config('inst'))))){//判断是否为本地文件
						$_array['kimage']=substr($smartimg,strlen($king->config('inst')));
					}else{//若是远程文件，则抓取
						if($path=kc_grab_get($smartimg)){//抓取成功
							if($path!=$smartimg){//值不一样，说明抓取成功
								$_array['kimage']=$path;
							}
						}
					}
				}
			}
		}

		//listid & kid1
		$_array['listid']=$data['listid'];
		$_array['kid1']=($data['kid1']?$data['kid1']:0);


/**
		检查kpath是否在键名列表里，如果有则判断是否为空值
		如果没有，则补充
*/
		if(empty($_array['kpath'])){
			$_array['kpath']=$king->portal->depathMode($info);
		}
/**
		检查kkeywords，如果没有，则自动补充其值
		如果有，则更新列表
*/
		$_array['kkeywords']= !empty($data['kkeywords'])
			? $king->portal->getKey($_array['ktitle'],$_array['kkeywords'])
			: $king->portal->getKey($_array['ktitle']);

/**
	关键字替换功能的实现概论

	从$_array['kkeywords']中获得关键字列表，从预置的[关键字链接页/待做的表]中查找相关关键字

	kname 关键字
	kkeywords 关键字相关关键字
	kpath 关键字链接网址

	preg_replace('',$rs['kpath'],$_array['kcontent'],1);

*/


/**
		检查ktag，如果没有，则自动补充其值
		如果有，则更新列表
*/
		$_array['ktag']= !empty($data['ktag']) ? $king->portal->getTag($_array['ktitle'],$_array['ktag']) : $king->portal->gettag($_array['ktitle']);
/**
		如果description值为空，则从content中获取
*/
		if(empty($data['kdescription']) && !empty($data['kcontent'])){
			$kdescription=strip_tags($data['kcontent']);
			$kdescription=preg_replace('/(\&[a-z]{1,6};)|\s/','',$kdescription);
			$_array['kdescription']=kc_substr($kdescription,0,200);
		}

		//副标题长度
		$_array['nsublength']=isset($data['ksubtitle']) ? kc_strlen($data['ksubtitle']) :0;

		//更新时间
		$_array['nlastdate']=time();
		//如果有kid1值，则对kid1对应的nlastdate进行更新
		if($kid1){
			$king->db->update('%s__'.$model['modeltable'],array('nlastdate'=>time()),'kid='.$kid1);
		}
		//图片框写远程路径的时候，抓图
		foreach ($model['field']['image'] as $key => $val) {
			if(isset($_array[$key])){//当有image类型的字段的时候，检查一下其值
				if(kc_validate($_array[$key],6)){//若为网址类型的话，自动抓图到本地
					$_array[$key]=kc_grab_get($_array[$key]);
				}
			}
		}

		//添加&更新数据
		if($kid){//update
			$king->db->update('%s__'.$model['modeltable'],$_array,'kid='.$kid);
			$_nlog=7;
		}else{
			$_array['ndate']=time();
			$_array['adminid']=$king->admin['adminid'];
			$_array['userid']=-1;
			$_array['norder']=$king->db->neworder('%s__'.$model['modeltable']);

			//不同的浏览器不同的分页标签,前台不支持
			switch(strtolower($king->admin['admineditor'])){
			case 'fckeditor':
				$pagebreak='<div style="page-break-after: always"><span style="display: none">&nbsp;</span></div>';
			break;
			case 'tiny_mce':$pagebreak='<!-- pagebreak -->';break;
			case 'edit_area':$pagebreak='<!-- pagebreak -->';break;
			}

			if(isset($pagebreak) && isset($_array['kcontent'])){
				$array=explode($pagebreak,$_array['kcontent']);
				foreach($array as $key => $val){

					$_array['kcontent']=$val;
					$_array['norder']++;

					if($key===0){//第一个
						$kid=$king->db->insert('%s__'.$model['modeltable'],$_array);
					}else{

						$_array['kpath']=$king->portal->depathMode($info);

						$_array['kid1']= $data['kid1'] ? $data['kid1'] : $kid;
						$king->db->insert('%s__'.$model['modeltable'],$_array);

					}
				}
			}else{
				$kid=$king->db->insert('%s__'.$model['modeltable'],$_array);
			}

			$_nlog=5;

			if($kid==0){
				kc_error($king->lang->get('system/error/insert').kc_clew(__FILE__,__LINE__,nl2br(print_r($_array,1))));
			}

		}

		//更新列表信息
		$king->portal->lastUpdated($listid,'list');

		//删除缓存重建缓存
		$king->cache->del('portal/list/'.$listid);
		kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid,1,'/',1).'.xml');
		$id=$king->portal->infoID($listid,$kid);
		if($kid1){
			kc_f_delete($king->config('xmlpath','portal').'/portal/'.$info['modelid'].'/'.wordwrap($kid1,1,'/',1).'.xml');
			$id=$king->portal->infoID($listid,$kid1);
		}

		//生成操作
		if($info['npage']==0){
			if($info['npagenumber']==1){
				$king->portal->createPage($listid,($kid1?$kid1:$kid));//$listid,$kid,$pid=1,$is=null

				$subkid=$id['subkid'];
				if($subkid){
					$subid=explode(',',$subkid);
					foreach($subid as $sid){
						$king->portal->createPage($listid,$sid);
					}
				}
			}else{
				$pcount=ceil($id['ncount']/$info['npagenumber']);
				for($i=1;$i<=$pcount;$i++){
					$king->portal->createPage($listid,($kid1?$kid1:$kid),$i);
				}
			}
		}

		//写log
		$king->log($_nlog,$model['modeltable'].':'.$data['ktitle']);
//		if(kc_post('pag')[0]==1){
		if(kc_post('pag')==1){
			$s=kc_goto($king->lang->get('system/goto/saveok'),'manage.content.php?action=edtpag&listid='.$data['listid'].'&kid1='.($kid1?$kid1:$kid));
		}else{
			if($kid1){
				kc_goto($king->lang->get('system/goto/is'),'manage.content.php?action=edtpag&listid='.$data['listid'].'&kid1='.$kid1,'manage.content.php?action=pag&listid='.$data['listid'].'&kid1='.$kid1);
			}else{
				kc_goto($king->lang->get('system/goto/is'),'manage.content.php?action=edt&listid='.$data['listid'],'manage.content.php?listid='.$data['listid']);
			}
		}
	}

	list($left,$right)=king_inc_list();
	$king->skin->output($info['ktitle'],$left,$right,$s);

}








?>