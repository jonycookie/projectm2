<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
return array(
	'index'=>array(
		'menu_index_home'=>'home',
		'menu_index_catalog_add'=>'catalog&operation=add',
		'menu_index_article_add'=>'article&operation=add',
		'menu_index_comment'=>'comment',
		'menu_index_article_user_draft'=>'article&operation=manage&act=user&type=draft',
		'menu_index_link'=>'link',
		'menu_index_advertise'=>'advertise',
	),
	'setting'=>array(
		'menu_setting_all'=>'setting',
		'menu_setting_config'=>'setting&operation=config',
		'menu_setting_seo'=>'setting&operation=seo',
		'menu_setting_html'=>'setting&operation=html',
		'menu_setting_cache'=>'setting&operation=cache',
		'menu_setting_attachments'=>'setting&operation=attachments',
		'menu_setting_watermark'=>'setting&operation=watermark',
		'menu_setting_publish'=>'setting&operation=publish',
		'menu_setting_time'=>'setting&operation=time',
		'menu_setting_other'=>'setting&operation=other',
		'menu_setting_bbs'=>'setting&operation=bbs',
	),
	'article'=>array(
		'menu_catalog_add'=>'catalog&operation=add',
		'menu_catalog_manage'=>'catalog',
		'menu_article_add'=>'article&operation=add',
		'menu_article_manage'=>'article&operation=manage',
		'menu_article_draft'=>'article&operation=manage&type=draft',
		'menu_article_user_manage'=>'article&operation=manage&act=user',
		'menu_article_user_draft'=>'article&operation=manage&act=user&type=draft',
		'menu_comment'=>'comment',
		'menu_contentype'=>'contentype',
		'menu_article_default'=>'default',
		'menu_filter'=>'filter',
		'menu_tag_manage'=>'tag&operation=manage',
		'menu_keywords'=>'keywords',
		'menu_search'=>'search',
	),
	'user'=>array(
		'menu_user_manage'=>'user&operation=manage',
	//	'menu_user_add'=>'user&operation=add',
	//	'menu_article_user_manage'=>'article&operation=manage&act=user',
	//	'menu_article_user_draft'=>'article&operation=manage&act=user&type=draft',
		'menu_account_manage'=>'account&operation=manage',
		'menu_account_edit'=>'account&operation=edit',
		'menu_group_manage'=>'group&operation=manage',
//		'menu_group_add'=>'group&operation=add',
//		'menu_account_profile'=>'account&operation=profile',
	),
//	'database'=>array(),
	'extend'=>array(
		'menu_model_manage' => 'model&operation=manage',
		'menu_field_manage' => 'field&operation=manage',
//		'menu_plugin_manage' => 'plugin&operation=manage',
//		'menu_modifier_manage' => 'modifier&operation=manage',
	),
	'html'=>array(
		'menu_html_all'=>'html&operation=all',
		'menu_html_index'=>'html&operation=index',
		'menu_html_catalog'=>'html&operation=catalog',
		'menu_html_article'=>'html&operation=article',
		'menu_html_tag'=>'html&operation=tag',
		'menu_html_page'=>'html&operation=page',
		'menu_setting_html'=>'setting&operation=html',
	),
	'tools'=>array(
		'menu_link'=>'link',
		'menu_file_manage'=>'file&operation=manage&method=database',
		'menu_file_upload'=>'file&operation=upload',
		'menu_extract_pic'=>'file&operation=extract',
		'menu_advertise'=>'advertise',
		'menu_message'=>'message',
		'menu_cache'=>'cache',
		'menu_template_manage'=>'template&operation=manage',
		'menu_database_backup'=>'database&operation=backup',
		'menu_database_recover'=>'database&operation=recover',
		'menu_database_repair'=>'database&operation=repair',
		'menu_database_replace'=>'database&operation=replace',
//		'menu_logs_admin'=>'logs',
	)
);
?>