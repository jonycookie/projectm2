<?php

// Language definitions used in all admin files
$lang_admin_reindex = array(

'Reindex heading'			=>	'重建搜寻索引以回复搜寻效能',
'Rebuild index legend'		=>	'重建搜寻索引',
'Reindex info'				=>	'如果您在数据库中已经动手做过新增、编辑、删除文章，或者在文章搜寻上有了问题，您应该重建搜寻索引。为了执行效率，您应该先把论坛设定在论坛维护模式，再来执行重建。一经处理完成，您将被重新导回本页。強烈建议在重建时，打开您浏览器的 JavaScript 功能项 (为了在每个处理逾期完成后能自动导回本页)。',
'Reindex warning'			=>	'<strong>重要！</strong> 进行搜寻索引重建程序可能会花上一段时间，而且在处理期间可能会增加服务主机的负载。当您強制中止重建处理程序时，如果您还要继续完成这个处理程序的话，请注意最后处理的主题 ID 数字为何，然后将该主题 ID 加 1 重新在"起始主题 ID"栏位填上当作起始 ID。',
'Empty index warning'		=>	'<strong>警告！</strong> 您将无法继续处理一个被中断的重建工作，如果说 "清空索引" 这项功能有被选用的话。',
'Posts per cycle'			=>	'每个处理逾期的文章数',
'Posts per cycle info'		=>	'每一个浏览页要处理的文章数目。举例来说，您输入 100 的话，代表每回有 100 篇文章将被处理，然后页面再重新刷新。这是为了避免在重建的过程中程序执行的时间过长而中断处理。',
'Starting post'				=>	'起始文章 ID',
'Starting post info'		=>	'重建工作将从哪个文章 ID 开始。它的预设值是数据库中可以取得的第一个文章 ID。在一般正常的情況下您不必更改这个设定值。',
'Empty index'				=>	'清空索引',
'Empty index info'			=>	'重建之前先清空搜寻索引 (見下列)。',
'Rebuilding index title'	=>	'重建搜寻索引 &#8230;',
'Rebuilding index'			=>	'重建索引中 &#8230; 趁著重建索引的时间给自己来杯咖啡吧 :-)',
'Processing post'			=>	'正在处理文章 <strong>%s</strong> 于主题 <strong>%s</strong>。',
'Javascript redirect'		=>	'JavaScript 重新导向失败。',
'Click to continue'			=>	'点击这里继续',
'Rebuild index'				=>	'重建索引',

);