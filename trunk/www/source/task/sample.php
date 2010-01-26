<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: sample.php 9984 2008-11-21 08:57:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//内置变量：$task['done'] (完成标识变量) $task['result'] (结果文字) $task['guide'] (向导文字)

//判断用户是否参与了活动
$done = 0;

//---------------------------------------------------
//	编写代码，判读用户是否完成活动要求 $done = 1;
//---------------------------------------------------

if($done) {

	$task['done'] = 1;//任务完成
	$task['result'] = '......';//用户参与活动看到的文字说明。支持html代码
	
} else {

	//任务完成向导
	$task['guide'] = '......'; //指导用户如何参与活动的文字说明。支持html代码

}

?>