<?php
/*
所有前台操作的错误信息
*/
$lang = array(
	/* CMS类错误 */
	'cms_nomidandcid'=>	'模板中使用的调用方法缺少关键性参数Mid以及Cid,这两者必须至少指定一项',
	'cms_nonum'		=>	'模板中使用的调用方法缺少关键性参数Num,该参数制定了程序将要读取的内容数量',
	'cms_miderror'	=>	'调用了不存在的Mid',
	'cms_ciderror'	=>	'调用的Cid格式错误',
	'cms_cidnum'	=>	'当您要调用的内容模型为BBS或者Blog时,指定的栏目Cid只能是个数字,具体该栏目如何和BBS/Blog之间板块挂钩,请到后台栏目处设置',
	'cms_minifielderror'=>	'您所指定的缩略字段不存在，程序无法继续进行，请仔细检查您所设置的调用参数',

	/* BBS类操作 */
	'bbs_sorterror'	=>	'不支持的排行调用类型',
	'bbs_paranum'	=>	'缺少关键性参数--调用排行需要三个参数,依次是排行类型,调用数量,排序依据.',
	'bbs_nocondition'=>	'指定的版块ID不正确,无法读取相关数据.',

	/* 模板 */
	'tpl_fileexists'	=>	"模板路径不正确,模板文件不存在,请检查是否存在该文件 $tplname",
	'tpl_noaction'		=>	"没有选择要执行动作",

	'cachedircannotwrite'=>	'缓存目录不可写，请检查其文件夹属性',

	'data_error'		=>	'数据有误，请求无法完成',
	'nocid'				=>	'数据有误，没有获取到Cid',
	'notid'				=>	'数据有误，没有获取到Tid',
	'searchmiderror'	=>	'搜索了一个不存在的内容模型',
	'commentnotallow'	=>	'抱歉，该栏目不允许进行评论',
	'notpubcate'		=>	'无法查看本栏目下内容',

	'extensions_error'	=>	'扩展名错误',
	'extensions_closed'	=>	'扩展插件没有开启',
	'back'				=>	'返回继续操作',
	'prompt'			=>	'提示信息',
	'cms_nothisid'		=>  '没有填写thisid参数',
	'cms_nostyleid'		=>  '没有填写styleid参数',
	'wap_cid_right'		=>	'该板块没有wap权限',
	'wap_illegal_tid'	=>	'错误的tid参数',
	'wap_closed'		=>	'wap功能未开启',
);
?>