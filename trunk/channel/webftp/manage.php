<?php require_once '../global.php';

function _path2id($path){
	$id=preg_replace("/[\]\[\/\!\@\#\$\%\^\&\(\)\~\+\;\'\,\.\`\-\=]/",'_',$path);

	return $id;
}

function king_ajax_left(){
	global $king;
	$king->access('webftp');

	$path=kc_post('path');
	$obj=kc_post('obj');
	$space=kc_post('space',2) ? kc_post('space') : 0;
	$ID=kc_post('ID');

	$dirs=kc_f_getdir($path,'dir',array('.','..','.svn',PATH_CACHE));

	$js='';

	//如果isopen==1的话，调用关闭
	if(kc_post('isopen')){//没有值或者0的时候是+号
		$js="\$('#$ID').attr('rel','{CMD:\'left\',path:\'$path\',ID:\'$ID\',obj:\'$obj\',space:$space,isopen:0}');";
		$js.="remove_dir('$obj','$space');";
		kc_ajax('',kc_icon('k1'),'',$js);
	}


	$cachepath="webftp/{$path}index";
	$jscache=$king->cache->get($cachepath,1);
	if(!$jscache){
		$jscache="var kjs='';";
		foreach($dirs as $val){
			$getDir=kc_f_getdir($path.$val.'/','dir');
			$isSub=count($getDir) ? 1 : 0;
			$jscache.="kjs+=lll('$path','$val',$isSub,$space);";
		}
		$jscache.="\$('#$obj').after(kjs);";
		$jscache.="\$.kc_ready(\"[id^='k_brow_obj_"._path2id($path)."']\");";
		$king->cache->put($cachepath,$jscache);
	}
	$js.=$jscache;


	$js.="\$('.k_table_list tr').children('td').removeClass('hover');";
	$js.="\$('.k_table_list tr').unbind('hover').hover(function(){\$(this).children('td').addClass('hover')},function(){\$(this).children('td').removeClass('hover')});";

	if($obj=='ftp_dir'){
//		$js="alert('$obj')";
		kc_ajax('',kc_icon('a1'),'',$js);
	}else{
		$js="\$('#$ID').attr('rel','{CMD:\'left\',path:\'$path\',ID:\'$ID\',obj:\'$obj\',space:$space,isopen:1,IS:2}');".$js;//展开后，重写成关闭的
		kc_ajax('',kc_icon('l1'),'',$js);
	}


}

function king_ajax_right(){
	global $king;
	$king->access('webftp');

	$path=kc_post('path');
	$js="\$('#ftp_file ~ tr').remove();";
	$leftopen=kc_post('leftopen',2);//在右侧的目录里点击进去后，1的时候加入展开左侧按钮的js代码


	$js.="var jsi='';";

	$dirs=kc_f_getdir($path,'dir',array('.','..','.svn',PATH_CACHE));
	foreach($dirs as $val){
		$js.="jsi+=iii('b1','$path','$val','--','".kc_formatdate(kc_f_mtime($path.$val))."');";;
	}

	$files=kc_f_getdir($path,'file');
	foreach($files as $file){
		$js.="jsi+=iii('".kc_f_ico($file)."','$path','$file','". kc_f_size(kc_f_filesize($path.$file)) ."','".kc_formatdate(kc_f_mtime($path.$file))."');";;
	}
	$js.="\$('#ftp_file').after(jsi);";
	$js.="\$.kc_ready('.k_table_list:eq(1)');";
	$js.="\$('.k_table_list tr').children('td').removeClass('hover');";
	$js.="\$('.k_table_list tr').unbind('hover').hover(function(){\$(this).children('td').addClass('hover')},function(){\$(this).children('td').removeClass('hover')});";
/*
	if($leftopen){
		$oPath=_path2id(substr($path,0,-1));
		$js.="\$.kc_ajax({CMD:'left',path:'$path',ID:'k_brow_dir_{$oPath}',obj:'k_brow_obj_{$oPath}',space:1,IS:2});";
	}
*/
	$js.="\$.kc_close();";
	kc_ajax('','','',$js);

}
/**
	重命名文件或文件夹
*/
function king_ajax_rename(){
	global $king;
	$king->access('webftp_rename');

	$isdir=kc_post('isdir',2,1);
	
	$path=kc_post('path');
	$file=kc_post('file',0,1);

	$id=kc_post('id');
	$new=kc_post($id);

	if(!kc_validate($new,'/^[A-Za-z0-9\.\_]+$/')){
		kc_ajax('',kc_icon('a1'),'',"alert('".$king->lang->get('webftp/error/newname')."')");
	}

	kc_f_rename($path.$file,$path.$new);

	$s="<a rel=\"{CMD:'right',path:'$path$new/',ID:'ftp_root',leftopen:1,IS:2}\" class=\"k_ajax\" href=\"javascript:;\">$new</a>";

	$js='';
	$oldID='k_brow_right_'._path2id($path.$file);
	$newID='k_brow_right_'._path2id($path.$new);

	if($isdir){//目录
		//在原有的项目下面添加一个新的，并在下面中删除掉老的
		$js.="\$('#$oldID').after(iii('b1','$path','$new','--','".kc_formatdate(kc_f_mtime($path.$new))."'));";
	}else{
		$js.="\$('#$oldID').after(iii('".kc_f_ico($new)."','$path','$new','".(kc_f_size(kc_f_filesize($path.$new)))."','".kc_formatdate(kc_f_mtime($path.$new))."'));";
	}
	$js.="\$.kc_ready('#$newID');";
	$js.="\$('#$oldID').remove();";

	$cachepath="system/filemanage/{$path}index";
	$king->cache->del($cachepath);//清理缓存

	$js.="\$.kc_close();";
	kc_ajax('','','',$js);
}
/**
	删除文件或文件夹
*/
function king_ajax_delete(){
	global $king;
	$king->access('webftp_delete');

	$isdir=kc_post('isdir',2,1);

	$path=kc_post('path');
	$file=kc_post('file',0,1);
	if($isdir){
		kc_f_rd($path.$file);
	}else{
		kc_f_delete($path.$file);
	}
	$js="\$('#k_brow_right_"._path2id($path.$file)."').remove();";//删除右侧内容
	$js.="\$('#k_brow_obj_"._path2id($path.$file)."').remove();";//同步删除左侧显示内容

	$cachepath="system/filemanage/{$path}index";
	$king->cache->del($cachepath);//清理缓存

	kc_ajax('',kc_icon('a1'),'',$js);
}
/**
	编辑文本
*/
function king_ajax_edit(){
	global $king;
	$king->access('webftp_edit');

	$path=kc_post('path');

	$ext=kc_f_ext($path);
	switch($ext){
		case 'html':$code='html';break;
		case 'htm':$code='html';break;
		case 'shtml':$code='html';break;
		case 'shtm':$code='html';break;
		case 'css':$code='css';break;
		case 'js':$code='js';break;
		case 'php':$code='php';break;
		case 'php3':$code='php';break;
		case 'php4':$code='php';break;
		case 'sql':$code='sql';break;
		case 'xml':$code='xml';break;
		default:$code='';
	}

	if(isset($_POST['webftpcontent'])){
		//写文件
		kc_f_put_contents($path,$_POST['webftpcontent'],1);
		kc_ajax('OK',"<p class=\"k_ok\">".$king->lang->get('system/ok/save')."</p>");
	}

	if(!kc_f_isfile($path))
		kc_error($king->lang->get('system/error/notfile'));

	$content=kc_f_get_contents($path);

	$js="editAreaLoader.init({
			id: \"webftpcontent\"
			,start_highlight: ".(strlen($content)>10240 ? 'false' : 'true')."
			,allow_resize: \"both\"
			,allow_toggle: false
			,word_wrap: true
			,language: \"en\"
			,syntax: \"php\"
		});";

	$s="<textarea id=\"webftpcontent\" name=\"webftpcontent\" rows=\"15\" cols=\"80\" style=\"width:870px;height:420px\">";
	$s.=htmlspecialchars($content);
	$s.="</textarea>";

	$but=kc_htm_a($king->lang->get('system/common/save'),"{CMD:'edit',IS:1,path:'$path'}");

	kc_ajax($king->lang->get('system/common/edit').' : '.$path,$s,$but,$js,900,450);
}

/**
	双击重命名，单击编辑、下载
	创建文件
	添加编辑器
*/
function king_def(){
	global $king;
	$king->access('webftp');

	$path=kc_post('path');
	$dirs=kc_f_getdir($path,'dir');

	$s="<table class=\"k_side w0\">";
	$s.="<tr><td class=\"w200\">";//左侧菜单
	$s.="<table class=\"k_table_list\" cellspacing=\"0\">";
	$s.="<tr id=\"ftp_dir\"><th><em id=\"ftp_root\"><img src=\"../system/images/white.gif\" class=\"os b1\"/></em>";
	$s.="<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:'right'}\">".$king->lang->get('webftp/list/dir')."</a></th></tr>";
	//左侧
	$s.="</table>";
	$s.="</td><td>";
		$s.="<table class=\"k_table_list\" cellspacing=\"0\">";

		$s.="<tr id=\"ftp_file\"><th>".$king->lang->get('system/common/filename')."</th>";
		$s.="<th class=\"w100\">".$king->lang->get('system/common/manage')."</th>";
		$s.="<th class=\"w150\">".$king->lang->get('system/common/filesize')."</th>";
		$s.="<th class=\"w150\">".$king->lang->get('system/common/modifydate')."</th>";
		$s.="</tr>";
		//右侧内容
		$s.="</table>";
	$s.="</td></tr>";
	$s.="</table>";
	$s.="<script language=\"javascript\" type=\"text/javascript\" src=\"edit_area/edit_area_full.js\"></script>";

	$s.="<script type=\"text/javascript\" >

	//调用右侧文件列表
	\$.kc_ajax({CMD:'right'});
	//调用左侧导航菜单
	\$.kc_ajax({CMD:'left',obj:'ftp_dir',ID:'ftp_root',IS:2});

	function lll(path,dir,issub,space){var spath=path+dir;var id=spath.replace(/[\]\[\/\!\@\#\$\%\^\&\(\)\~\+\;\'\,\.\`\-\=]/g,'_');
		var s='<tr id=\"k_brow_obj_'+id+'\"><td>';
		for(i=0;i<space;i++){s+=\$.kc_icon()};//space计算
		
		//+-展开按钮
		s+='<a id=\"k_brow_dir_'+id+'\" href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'left\',path:\''+path+dir+'/\',ID:\'k_brow_dir_'+id+'\',obj:\'k_brow_obj_'+id+'\',space:'+(space*1+1)+',IS:2}\">';
		s+=\$.kc_icon(issub==1?'k1':'')+'</a>';

		//点击后在右侧显示对应目录下面的文件
		s+='<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'right\',path:\''+path+dir+'/\'}\">'+\$.kc_icon('b1')+'<em id=\"k_brow_sub_'+id+'\">'+dir+'</em></a></td>';
		s+='</tr>';
		return s;
	};

	//关闭展开
	function remove_dir(id,space){
		\$(\"[id^='\"+id+\"_']\").remove();
	};

	//右侧文件列表
	function iii(ico,path,file,size,mdate){var s;
		var spath=path+file;
		var id=spath.replace(/[\]\[\/\!\@\#\$\%\^\&\(\)\~\+\;\'\,\.\`\-\=]/g,'_');
		var isdir=(size=='--' ? 1 : 0);
		s='<tr id=\"k_brow_right_'+id+'\" ondblClick=\"iRename(\''+ico+'\',\''+path+'\',\''+file+'\',\''+size+'\',\''+encodeURI(mdate)+'\',\''+id+'\')\"><td>';
		s+=\$.kc_list(id,file,iLink(path,file,isdir,id),0,1,ico);
		s+='</td>';
		s+='<td>';
		//重命名
		s+='<a href=\"javascript:;\" onClick=\"iRename(\''+ico+'\',\''+path+'\',\''+file+'\',\''+size+'\',\''+encodeURI(mdate)+'\',\''+id+'\')\">';
		s+=\$.kc_icon('l4','".$king->lang->get('system/common/rename')."')+'</a>';
		//删除
		s+='<a href=\"javascript:;\" class=\"k_ajax\" rel=\"{CMD:\'delete\',path:\''+path+'\',file:\''+file+'\',isdir:'+isdir+',IS:2,ID:\'ftp_root\'}\">';
		s+=\$.kc_icon('j2','".$king->lang->get('system/common/del')."');
		s+='</td>';
		s+='<td>'+size+'</td>';
		s+='<td>'+mdate+'</td>';
		s+='</tr>';

		return s;
	
	}

	//重命名文件或文件夹
	function iRename(ico,path,file,size,mdate,id){var s;
		var isdir=(size=='--' ? 1 : 0);
		s='<input class=\"k_in w200\" value=\"'+file+'\" id=\"R'+id+'\"/>';

		s+='<a href=\"javascript:;\" class=\"k_ajax\" ';
		s+='rel=\"{CMD:\'rename\',path:\''+path+'\',file:\''+file+'\',id:\'R'+id+'\',VAL:\'R'+id+'\',isdir:'+isdir+'}\">';
		s+=\$.kc_icon('o7')+'</a>';

		s+='<a href=\"javascript:;\" onClick=\"nRename(\''+ico+'\',\''+path+'\',\''+file+'\',\''+size+'\',\''+mdate+'\',\''+id+'\')\">';
		s+=\$.kc_icon('m2')+'</a>';

		\$('#k_brow_right_'+id+'>td>label>u').html(s);
		\$.kc_ready('#k_brow_right_'+id+'>td>label>u');
	}
	//取消重命名
	function nRename(ico,path,file,size,mdate,id){
		\$('#k_brow_right_'+id).replaceWith(iii(ico,path,file,size,decodeURI(mdate)));
	}

	//生成链接
	function iLink(path,file,isdir,id){
		var s;
		if(isdir){
			s='{CMD:\'right\',path:\''+path+file+'/\',leftopen:1}';
		}else{
			var fext=file.substring(file.lastIndexOf('.') + 1);

			var ss={php:'',html:'',htm:''};

			if(ss[fext]!=undefined){
				s='{CMD:\'edit\',path:\''+path+file+'\'}';
			}else{
				s='../'+path+file;
			}

			switch(fext){
				case 'php':
				break;
				case 'html':
				break;
				default:
			}
		}
		return s;
	}";
	$s.="</script>";

	$king->skin->output($king->lang->get('webftp/title/center'),'','',$s);
}



?>