<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: index.php 10855 2008-12-29 08:10:45Z liguode $
*/

define('IN_UCHOME', TRUE);

error_reporting(0);
$_SGLOBAL = $_SCONFIG = $_SBLOCK = array();

//程序目录
define('S_ROOT', substr(dirname(__FILE__), 0, -7));

//获取时间
$_SGLOBAL['timestamp'] = time();

include_once(S_ROOT.'./source/function_common.php');
if(!@include_once(S_ROOT.'./config.php')) {
	@include_once(S_ROOT.'./config.new.php');
	show_msg('您需要首先将程序根目录下面的 "config.new.php" 文件重命名为 "config.php"', 999);
}

//GPC过滤
if(!(get_magic_quotes_gpc())) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

//启用GIP
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

$formhash = formhash();

$theurl = 'index.php';
$sqlfile = S_ROOT.'./data/install.sql';
if(!file_exists($sqlfile)) {
	show_msg('请上传最新的 install.sql 数据库结构文件到程序的 ./data 目录下面，再重新运行本程序', 999);
}
$configfile = S_ROOT.'./config.php';

//变量
$step = empty($_GET['step'])?0:intval($_GET['step']);
$action = empty($_GET['action'])?'':trim($_GET['action']);
$nowarr = array('','','','','','','');

$lockfile = S_ROOT.'./data/install.lock';
if(file_exists($lockfile)) {
	show_msg('警告!您已经安装过UCenter Home<br>
		为了保证数据安全，请立即手动删除 install/index.php 文件<br>
		如果您想重新安装UCenter Home，请删除 data/install.lock 文件，再次运行安装文件');
}

//检查config是否可写
if(!@$fp = fopen($configfile, 'a')) {
	show_msg("文件 $configfile 读写权限设置错误，请设置为可写，再执行安装程序");
} else {
	@fclose($fp);
}

//提交处理
if (submitcheck('ucsubmit')) {

	//安装UC配置
	$step = 1;

	//判断域名是否解析
	$ucapi = preg_replace("/\/$/", '', trim($_POST['ucapi']));
	$ucip = trim($_POST['ucip']);

	if(empty($ucapi) || !preg_match("/^(http:\/\/)/i", $ucapi)) {
		show_msg('UCenter的URL地址不正确');
	} else {
		//检查服务器 dns 解析是否正常, dns 解析不正常则要求用户输入ucenter的ip地址
		if(!$ucip) {
			$temp = @parse_url($ucapi);
			$ucip = gethostbyname($temp['host']);
			if(ip2long($ucip) == -1 || ip2long($ucip) === FALSE) {
				$ucip = '';
			}
		}
	}

	//验证UCHome是否安装
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		show_msg('uc_client目录不存在，请上传安装包中的 ./upload/uc_client 到程序根目录');
	}
	$ucinfo = uc_fopen2($ucapi.'/index.php?m=app&a=ucinfo&release='.UC_CLIENT_RELEASE, 500, '', '', 1, $ucip);
	list($status, $ucversion, $ucrelease, $uccharset, $ucdbcharset, $apptypes) = explode('|', $ucinfo);
	$dbcharset = strtolower(trim($_SC['dbcharset'] ? str_replace('-', '', $_SC['dbcharset']) : $_SC['dbcharset']));
	$ucdbcharset = strtolower(trim($ucdbcharset ? str_replace('-', '', $ucdbcharset) : $ucdbcharset));
	$apptypes = strtolower(trim($apptypes));
	if($status != 'UC_STATUS_OK') {
		show_header();
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<table class="datatable">
		<tr><td><strong>UCenter无法正常连接，返回错误 ( $status )，请确认UCenter的IP地址是否正确</strong><br><br></td></tr>
		<tr><td>UCenter服务器的IP地址: <input type="text" name="ucip" value="$ucip"> 例如：192.168.0.1</td></tr>
		</table>
		<table class=button>
		<tr><td>
		<input type="hidden" name="ucapi" value="$ucapi">
		<input type="hidden" name="ucfounderpw" value="$_POST[ucfounderpw]">
		<input type="submit" id="ucsubmit" name="ucsubmit" value="确认IP地址"></td></tr>
		</table>
		<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
		show_footer();
		exit();
	} elseif($dbcharset && $ucdbcharset && $ucdbcharset != $dbcharset) {
		show_msg('UCenter 服务端字符集与当前应用的字符集不同，请下载 '.$ucdbcharset.' 编码的 UCenter Home 进行安装，下载地址：http://download.comsenz.com/');
	} elseif(strexists($apptypes, 'uchome')) {
		show_msg('已经安装过一个UCenter Home产品，如果想继续安装，请先到 UCenter 应用管理中删除已有的UCenter Home！');
	}
	$tagtemplates = 'apptagtemplates[template]='.urlencode('<a href="{url}" target="_blank">{subject}</a>').'&'.
		'apptagtemplates[fields][subject]='.urlencode('日志标题').'&'.
		'apptagtemplates[fields][uid]='.urlencode('用户 ID').'&'.
		'apptagtemplates[fields][username]='.urlencode('用户名').'&'.
		'apptagtemplates[fields][dateline]='.urlencode('日期').'&'.
		'apptagtemplates[fields][spaceurl]='.urlencode('空间地址').'&'.
		'apptagtemplates[fields][url]='.urlencode('日志地址');
		
	$uri = $_SERVER['REQUEST_URI']?$_SERVER['REQUEST_URI']:($_SERVER['PHP_SELF']?$_SERVER['PHP_SELF']:$_SERVER['SCRIPT_NAME']);
	$app_url = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, strpos($_SERVER['SERVER_PROTOCOL'], '/'))).'://'.$_SERVER['HTTP_HOST'].preg_replace("/\/*install$/i", '', substr($uri, 0, strrpos($uri, '/install')));
	
	$postdata = "m=app&a=add&ucfounder=&ucfounderpw=".urlencode($_POST['ucfounderpw'])."&apptype=".urlencode('UCHOME')."&appname=".urlencode('个人家园')."&appurl=".urlencode($app_url)."&appip=&appcharset=".$_SC['charset'].'&appdbcharset='.$_SC['dbcharset'].'&release='.UC_CLIENT_RELEASE.'&'.$tagtemplates;
	$s = uc_fopen2($ucapi.'/index.php', 500, $postdata, '', 1, $ucip);
	if(empty($s)) {
		show_msg('UCenter用户中心无法连接');
	} elseif($s == '-1') {
		show_msg('UCenter管理员帐号密码不正确');
	} else {
		$ucs = explode('|', $s);
		if(empty($ucs[0]) || empty($ucs[1])) {
			show_msg('UCenter返回的数据出现问题，请参考:<br />'.shtmlspecialchars($s));
		} else {

			//处理成功
			$apphidden = '';
			//验证是否可以直接联接MySQL
			$link = mysql_connect($ucs[2], $ucs[4], $ucs[5], 1);
			$connect = $link && mysql_select_db($ucs[3], $link) ? 'mysql' : '';
			//返回
			foreach (array('key', 'appid', 'dbhost', 'dbname', 'dbuser', 'dbpw', 'dbcharset', 'dbtablepre', 'charset') as $key => $value) {
				if($value == 'dbtablepre') {
					$ucs[$key] = '`'.$ucs[3].'`.'.$ucs[$key];
				}
				$apphidden .= "<input type=\"hidden\" name=\"uc[$value]\" value=\"".$ucs[$key]."\" />";
			}
			//内置
			$apphidden .= "<input type=\"hidden\" name=\"uc[connect]\" value=\"$connect\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[api]\" value=\"$_POST[ucapi]\" />";
			$apphidden .= "<input type=\"hidden\" name=\"uc[ip]\" value=\"$ucip\" />";

			show_header();
			print<<<END
			<form id="theform" method="post" action="$theurl">
			<table>
			<tr><td>UCenter注册成功！当前程序ID标识为: $ucs[1]</td></tr>
			</table>

			<table class=button>
			<tr><td>$apphidden
			<input type="submit" id="uc2submit" name="uc2submit" value="进入下一步"></td></tr>
			</table>
			<input type="hidden" name="formhash" value="$formhash">
			</form>
END;
			show_footer();
			exit();
		}
	}

} elseif (submitcheck('uc2submit')) {

	//增加congfig配置
	$step = 2;

	//写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['uc']);
	foreach ($keys as $value) {
		$upkey = strtoupper($value);
		$configcontent = preg_replace("/define\('UC_".$upkey."'\s*,\s*'.*?'\)/i", "define('UC_".$upkey."', '".$_POST['uc'][$value]."')", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

} elseif(!empty($_POST['sqlsubmit'])) {

	$step = 2;

	//先写入config文件
	$configcontent = sreadfile($configfile);
	$keys = array_keys($_POST['db']);
	foreach ($keys as $value) {
		$configcontent = preg_replace("/[$]\_SC\[\'".$value."\'\](\s*)\=\s*[\"'].*?[\"']/is", "\$_SC['".$value."']\\1= '".$_POST['db'][$value]."'", $configcontent);
	}
	if(!$fp = fopen($configfile, 'w')) {
		show_msg("文件 $configfile 读写权限设置错误，请设置为可写后，再执行安装程序");
	}
	fwrite($fp, trim($configcontent));
	fclose($fp);

	//判断UCenter Home数据库
	$havedata = false;
	if(!@mysql_connect($_POST['db']['dbhost'], $_POST['db']['dbuser'], $_POST['db']['dbpw'])) {
		show_msg('您输入的UCenter Home数据库帐号不正确');
	}
	if(mysql_select_db($_POST['db']['dbname'])) {
		if(mysql_query("SELECT COUNT(*) FROM {$_POST['db']['tablepre']}space")) {
			$havedata = true;
		}
	} else {
		if(!mysql_query("CREATE DATABASE `".$_POST['db']['dbname']."`")) {
			show_msg('设定的UCenter Home数据库无权限操作，请先手工操作后，再执行安装程序');
		}
	}

	if($havedata) {
		show_msg('危险!指定的UCenter Home数据库已有数据，如果继续将会清空原有数据!', ($step+1));
	} else {
		show_msg('数据库配置成功，进入下一步操作', ($step+1), 1);
	}

} elseif (submitcheck('opensubmit')) {

	//检查用户身份
	include_once(S_ROOT.'./data/data_config.php');

	$step = 5;

	dbconnect();

	//同步获取用户源
	$_SGLOBAL['timestamp'] = time();

	//UC注册用户
	if(!@include_once S_ROOT.'./uc_client/client.php') {
		showmessage('system_error');
	}
	$uid = uc_user_register($_POST['username'], $_POST['password'], 'webmastor@yourdomain.com');
	if($uid == -3) {
		//已存在，登录
		if(!$passport = getpassport($_POST['username'], $_POST['password'])) {
			show_msg('输入的用户名密码不正确，请确认');
		}
		$setarr = array(
			'uid' => $passport['uid'],
			'username' => addslashes($passport['username'])
		);
	} elseif($uid > 0) {
		$setarr = array(
			'uid' => $uid,
			'username' => $_POST['username']
		);
	} else {
		show_msg('输入的用户名无法注册，请重新确认');
	}
	$setarr['password'] = md5("$setarr[uid]|$_SGLOBAL[timestamp]");//本地密码随机生成

	//更新本地用户库
	inserttable('member', $setarr, 0, true);

	//开通空间
	include_once(S_ROOT.'./source/function_space.php');
	$space = space_open($setarr['uid'], $_POST['username'], 1);

	//反馈受保护
	$result = uc_user_addprotected($_POST['username'], $_POST['username']);
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET flag=1 WHERE username='$_POST[username]'");

	//清理在线session
	insertsession($setarr);

	//设置cookie
	ssetcookie('auth', authcode("$setarr[password]\t$setarr[uid]", 'ENCODE'), 2592000);

	//写log
	if(@$fp = fopen($lockfile, 'w')) {
		fwrite($fp, 'UCenter Home');
		fclose($fp);
	}

	show_msg('<font color="red">恭喜! UCenter Home安装全部完成!</font>
		<br>为了您的数据安全，请登录ftp，删除本安装文件<br><br>
		您的管理员身份已经成功确认，并已经开通空间。接下来，您可以：<br>
		<br><a href="../space.php" target="_blank">进入我的空间</a>
		<br>进入我的主页，开始UCenter Home之旅
		<br><a href="../admincp.php" target="_blank">进入管理平台</a>
		<br>以管理员身份对站点参数进行设置', 999);

}

if(empty($step)) {

	show_header();

	//检查权限设置
	$checkok = true;
	$perms = array();
	if(!checkfdperm(S_ROOT.'./config.php', 1)) {
		$perms['config'] = '失败';
		$checkok = false;
	} else {
		$perms['config'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./attachment/')) {
		$perms['attachment'] = '失败';
		$checkok = false;
	} else {
		$perms['attachment'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./data/')) {
		$perms['data'] = '失败';
		$checkok = false;
	} else {
		$perms['data'] = 'OK';
	}
	if(!checkfdperm(S_ROOT.'./uc_client/data/')) {
		$perms['uc_data'] = '失败';
		$checkok = false;
	} else {
		$perms['uc_data'] = 'OK';
	}

	//安装阅读
	print<<<END
	<script type="text/javascript">
	function readme() {
		var tbl_readme = document.getElementById('tbl_readme');
		if(tbl_readme.style.display == '') {
			tbl_readme.style.display = 'none';
		} else {
			tbl_readme.style.display = '';
		}
	}
	</script>
	<table class="showtable">
	<tr><td>
	<strong>欢迎您使用UCenter Home</strong><br>
	通过 UCenter Home，作为建站者的您，可以轻松构建一个以好友关系为核心的交流网络，让站点用户可以用一句话记录生活中的点点滴滴；方便快捷地发布日志、上传图片；更可以十分方便的与其好友们一起分享信息、讨论感兴趣的话题；轻松快捷的了解好友最新动态。
	<br><a href="javascript:;" onclick="readme()"><strong>请先认真阅读我们的软件使用授权协议</strong></a>
	</td></tr>
	</table>

	<table>
	</td></tr>
	<tr><td>
	<strong>文件/目录权限设置</strong><br>
	在您执行安装文件进行安装之前，先要设置相关的目录属性，以便数据文件可以被程序正确读/写/删/创建子目录。<br>
	推荐您这样做：<br>使用 FTP 软件登录您的服务器，将服务器上以下目录、以及该目录下面的所有文件的属性设置为777，win主机请设置internet来宾帐户可读写属性<br>
	<table class="datatable">
	<tr style="font-weight:bold;"><td>名称</td><td>所需权限属性</td><td>说明</td><td>检测结果</td></tr>
	<tr><td><strong>./config.php</strong></td><td>读/写</td><td>系统配置文件</td><td>$perms[config]</td></tr>
	<tr><td><strong>./attachment/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>附件目录</td><td>$perms[attachment]</td></tr>
	<tr><td><strong>./data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>站点数据目录</td><td>$perms[data]</td></tr>
	<tr><td><strong>./uc_client/data/</strong> (包括本目录、子目录和文件)</td><td>读/写/删</td><td>uc_client数据目录</td><td>$perms[uc_data]</td></tr>
	</table>
	</td></tr>
	</table>
END;

	if(!$checkok) {
		echo "<table><tr><td><b>出现问题</b>:<br>系统检测到以上目录或文件权限没有正确设置<br>强烈建议正常设置权限后再刷新本页面以便继续安装<br>否则系统可能会出现无法预料的问题 [<a href=\"$theurl?step=1\">强制继续</a>]</td></tr></table>";
	} else {
		$ucapi = empty($_POST['ucapi'])?'/':$_POST['ucapi'];
		$ucfounderpw = empty($_POST['ucfounderpw'])?'':$_POST['ucfounderpw'];
		print <<<END
		<form id="theform" method="post" action="$theurl?step=1">
			<table class=button>
				<tr>
					<td><input type="submit" id="startsubmit" name="startsubmit" value="接受授权协议，开始安装UCenter Home"></td>
				</tr>
			</table>
			<input type="hidden" name="ucapi" value="$ucapi" />
			<input type="hidden" name="ucfounderpw" value="$ucfounderpw" />
			<input type="hidden" name="formhash" value="$formhash">
		</form>
END;
	}

	print<<<END
	<table id="tbl_readme" style="display:none;" class="showtable">
	<tr>
	<td><strong>请您务必仔细阅读下面的许可协议:</strong> </td></tr>
	<tr>
	<td>
	<div>中文版授权协议 适用于中文用户
	<p>版权所有 (C) 2001-2008，康盛创想（北京）科技有限公司<br>保留所有权利。
	</p><p>感谢您选择 UCenter Home。希望我们的努力能为您提供一个强大的社会化网络(SNS)解决方案。通过 UCenter Home，建站者可以轻松构建一个以好友关系为核心的交流网络，让站点用户可以用一句话记录生活中的点点滴滴；方便快捷地发布日志、上传图片；更可以十分方便的与其好友们一起分享信息、讨论感兴趣的话题；轻松快捷的了解好友最新动态。
	</p><p>康盛创想（北京）科技有限公司为 UCenter Home 产品的开发商，依法独立拥有 UCenter Home 产品著作权（中国国家版权局 著作权登记号 2006SR12091）。康盛创想（北京）科技有限公司网址为
	http://www.comsenz.com，UCenter Home 官方网站网址为 http://u.discuz.net。
	</p><p>UCenter Home 著作权已在中华人民共和国国家版权局注册，著作权受到法律和国际公约保护。使用者：无论个人或组织、盈利与否、用途如何
	（包括以学习和研究为目的），均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用 UCenter Home 软件。
	</p><p>康盛创想（北京）科技有限公司拥有对本授权协议的最终解释权。
	<ul type=i>
	<p>
	<li><b>协议许可的权利</b>
	<ul type=1>
	<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。
	<li>您可以在协议规定的约束和限制范围内修改 UCenter Home 源代码(如果被提供的话)或界面风格以适应您的网站要求。
	<li>您拥有使用本软件构建的站点中全部会员资料、文章及相关信息的所有权，并独立承担与文章内容的相关法律义务。
	<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，
	自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见
	将被作为首要考虑，但没有一定被采纳的承诺或保证。 </li></ul>
	<p></p>
	<li><b>协议规定的约束和限制</b>
	<ul type=1>
	<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登陆http://www.discuz.com参考相关说明，也可以致电8610-51657885了解详情。
	<li>不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。
	<li>无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用 UCenter Home 的整体或任何部分，未经书面许可，程序页面页脚处
	的 UCenter Home 名称和康盛创想（北京）科技有限公司下属网站（http://www.comsenz.com、http://u.discuz.net） 的 链接都必须保留，而不能清除或修改。
	<li>禁止在 UCenter Home 的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。
	<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。 </li></ul>
	<p></p>
	<li><b>有限担保和免责声明</b>
	<ul type=1>
	<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。
	<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，
	也不承担任何因使用本软件而产生问题的相关责任。
	<li>康盛创想（北京）科技有限公司不对使用本软件构建的站点中的文章或信息承担责任。 </li></ul></li></ul>
	<p>有关 UCenter Home 最终用户授权协议、商业授权与技术服务的详细内容，均由 UCenter Home 官方网站独家提供。康盛创想（北京）科技有限公司拥有在不 事先通知的情况下，修改授权协议和服务价目表的权力，修改后的协议或价目表对自改变之日起的新授权用户生效。
	<p>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始安装 UCenter Home，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。 </p></div>
	</td></tr>
	</table>
END;

	show_footer();

} elseif($step == 1) {

	show_header();
	$ucapi = "http://";
	$ucfounderpw = '';
	$showdiv = 0;
	if($_POST['ucfounderpw']) {
		$showdiv = 1;
		$ucapi = trim($_POST['ucapi']);
		$ucfounderpw = trim($_POST['ucfounderpw']);
	}

	if($showdiv) {
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># UCenter 参数自动获取</strong></td></tr>
				<tr><td id="msg2">UCenter的相关信息已成功获取，请直接下面的按钮提交配置</td></tr>
			</table>
			<br/>
		</div>
		<div>
END;
	} else {
		$plus = '';
		if(!$ucfounderpw) {
			$plus = '<tr><td id="msg2">
					使用UCenter Home，首先需要您的站点安装有统一存储用户帐号信息的UCenter用户中心系统。<br>
					如果您的站点还没有安装过UCenter，请这样操作：<br>
					1. <a href="http://download.comsenz.com/UCenter/" target="_blank"><b>请点击这里下载最新版本的UCenter</b></a>，并阅读程序包中的说明进行UCenter的安装。<br>
					2. 安装完毕 UCenter 后，在下面填入UCenter的相关信息即可继续进行UCenter Home 的安装。<br>
				</td></tr>';
		}
		print<<<END
		<form id="theform" method="post" action="$theurl">
		<div>
			<table class="showtable">
				<tr><td><strong># 请填写 UCenter 的相关参数</strong></td></tr>
				$plus
			</table>
			<br>
			<p style="font-weight:bold;">请输入已安装UCenter的信息:</p>
END;
	}
	print<<<END
		<table class=datatable>
			<tbody>
				<tr>
					<td>UCenter 的 URL:</td>
					<td><input type="text" id="ucapi" name="ucapi" size="60" value="$ucapi"><br>例如：http://www.discuz.net/ucenter</td>
				</tr>
				<tr>
					<td>UCenter 的创始人密码:</td>
					<td><input type="password" id="ucfounderpw" name="ucfounderpw" size="20" value="$ucfounderpw"></td>
				</tr>
			</tbody>
		</table>
		<br>
	</div>
	<table class=button>
	<tr><td><input type="submit" id="ucsubmit" name="ucsubmit" value="提交UCenter配置信息"></td></tr>
	</table>
	<input type="hidden" id="ucfounder" name="ucfounder" size="20" value="">
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 2) {

	//检测目录属性
	show_header();
	//设置数据库配置
	print<<<END
	<form id="theform" method="post" action="$theurl">

	<table class="showtable">
	<tr><td><strong># 设置UCenter Home数据库信息</strong></td></tr>
	<tr><td id="msg1">这里设置UCenter Home的数据库信息</td></tr>
	</table>
	<table class=datatable>
	<tr>
	<td width="25%">数据库服务器本地地址:</td>
	<td><input type="text" name="db[dbhost]" size="20" value="localhost"></td>
	<td width="30%">一般为localhost</td>
	</tr>
	<tr>
	<td>数据库用户名:</td>
	<td><input type="text" name="db[dbuser]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库密码:</td>
	<td><input type="password" name="db[dbpw]" size="20" value=""></td>
	<td>&nbsp;</td>
	</tr>
	<tr>
	<td>数据库字符集:</td>
	<td>
	<select name="db[dbcharset]" onchange="addoption(this)">
	<option value="$_SC[dbcharset]">$_SC[dbcharset]</option>
	<option value="gbk">gbk</option>
	<option value="utf8">utf8</option>
	<option value="big5">big5</option>
	<option value="latin1">latin1</option>
	<option value="addoption" class="addoption">+自定义</option>
	</select>
	</td>
	<td>MySQL版本>4.1才有效</td>
	</tr>
	<tr>
	<td>数据库名:</td>
	<td><input type="text" name="db[dbname]" size="20" value=""></td>
	<td>如果不存在，则会尝试自动创建</td>
	</tr>
	<tr>
	<td>表名前缀:</td>
	<td><input type="text" name="db[tablepre]" size="20" value="uchome_"></td>
	<td>默认为uchome_</td>
	</tr>
	</table>

	<table class=button>
	<tr><td><input type="submit" id="sqlsubmit" name="sqlsubmit" value="设置完毕,检测我的数据库配置"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
END;
	show_footer();

} elseif ($step == 3) {

	//链接数据库
	dbconnect();

	//安装数据库
	//获取最新的sql文
	$newsql = sreadfile($sqlfile);

	if($_SC['tablepre'] != 'uchome_') $newsql = str_replace('uchome_', $_SC['tablepre'], $newsql);//替换表名前缀

	//获取要创建的表
	$tables = $sqls = array();
	if($newsql) {
		preg_match_all("/(CREATE TABLE ([a-z0-9\_\-`]+).+?\s*)(TYPE|ENGINE)+\=/is", $newsql, $mathes);
		$sqls = $mathes[1];
		$tables = $mathes[2];
	}
	if(empty($tables)) {
		show_msg("安装SQL语句获取失败，请确认SQL文件 $sqlfile 是否存在");
	}

	$heaptype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MEMORY".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=HEAP";
	$myisamtype = $_SGLOBAL['db']->version()>'4.1'?" ENGINE=MYISAM".(empty($_SC['dbcharset'])?'':" DEFAULT CHARSET=$_SC[dbcharset]" ):" TYPE=MYISAM";
	$installok = true;
	foreach ($tables as $key => $tablename) {
		if(strpos($tablename, 'session')) {
			$sqltype = $heaptype;
		} else {
			$sqltype = $myisamtype;
		}
		$_SGLOBAL['db']->query("DROP TABLE IF EXISTS $tablename");
		if(!$query = $_SGLOBAL['db']->query($sqls[$key].$sqltype, 'SILENT')) {
			$installok = false;
			break;
		}
	}
	if(!$installok) {
		show_msg("<font color=\"blue\">数据表 ($tablename) 自动安装失败</font><br />反馈: ".mysql_error()."<br /><br />请参照 $sqlfile 文件中的SQL文，自己手工安装数据库后，再继续进行安装操作<br /><br /><a href=\"?step=$step\">重试</a>");
	} else {
		show_msg('数据表已经全部安装完成，进入下一步操作', ($step+1), 1);
	}

} elseif ($step == 4) {

	//插入默认数据
	dbconnect();

	//config
	$datas = array(
		"('sitename', '我的空间')",
		"('sitelogo', 'image/logo.gif')",
		"('template', 'default')",
		"('adminemail', 'webmaster@".$_SERVER['HTTP_HOST']."')",
		"('onlinehold', '1800')",
		"('timeoffset', '8')",
		"('maxpage', '50')",
		"('starcredit', '100')",
		"('starlevelnum', '5')",
		"('cachemode', 'database')",
		"('cachegrade', '0')",
		"('allowcache', '1')",
		"('allowdomain', '0')",
		"('allowrewrite', '0')",
		"('allowwatermark', '0')",
		"('allowftp', '0')",
		"('holddomain', 'www|*blog*|*space*|x')",
		"('mtagminnum', '5')",
		"('feedday', '15')",
		"('feedmaxnum', '50')",
		"('feedfilternum', '10')",
		"('importnum', '100')",
		"('singlesent', '50')",
		"('groupnum', '8')",
		"('closeregister', '0')",
		"('closeinvite', '0')",
		"('close', '0')",
		"('networkpublic', '1')",
		"('networkpage', '1')",
		"('networkupdate', '300')",
		"('seccode_register', '1')",
		"('uc_tagrelated', '1')",
		"('manualmoderator', '1')",
		"('linkguide', '1')",
		"('showall', '1')",
		"('sendmailday', '0')",
		"('realname', '0')",
		"('namecheck', '0')",
		"('namechange', '0')",
		"('name_allowfriend', '1')",
		"('name_allowpoke', '1')",
		"('name_allowdoing', '1')",
		"('name_allowblog', '0')",
		"('name_allowalbum', '0')",
		"('name_allowthread', '0')",
		"('name_allowshare', '0')",
		"('name_allowcomment', '0')",
		"('name_allowpost', '0')",
		"('showallfriendnum', '10')",
		"('feedtargetblank', '1')",
		"('feedread', '1')",
		"('uc_tagrelatedtime', '86400')",
		'(\'privacy\', \'a:2:{s:4:"view";a:10:{s:5:"index";i:0;s:7:"profile";i:0;s:6:"friend";i:0;s:4:"wall";i:0;s:4:"feed";i:0;s:5:"doing";i:0;s:4:"blog";i:0;s:5:"album";i:0;s:5:"share";i:0;s:4:"mtag";i:0;}s:4:"feed";a:11:{s:5:"doing";i:1;s:4:"blog";i:1;s:5:"album";i:1;s:6:"upload";i:1;s:5:"share";i:1;s:6:"thread";i:1;s:4:"post";i:1;s:4:"mtag";i:1;s:6:"friend";i:1;s:7:"comment";i:1;s:5:"trace";i:1;}}\')',
		"('cronnextrun', '$_SGLOBAL[timestamp]')",
		"('my_status', '0')"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('config'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('config')." (var, datavalue) VALUES ".implode(',', $datas));

	//profield
	$datas = array(
		"('自由联盟', 'text', '100', '0', '1')",
		"('地区联盟', 'text', '100', '0', '1')",
		"('兴趣联盟', 'text', '100', '0', '1')"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('profield'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('profield')." (title,formtype,inputnum,manualmoderator,manualmember) VALUES ".implode(',', $datas));

	//用户组
	$datas = array();
	$datas['grouptitle'] = array('站点管理员', '信息管理员', '贵宾VIP', '受限会员', '普通会员', '中级会员', '高级会员', '禁止会员');

	//核心设置
	$datas['gid'] = array(1, 2, 3, 4, 5, 6, 7, 8);
	$datas['system'] = array(-1, -1, 1, 0, 0, 0, 0, 1);
	$datas['creditlower'] = array(0, 0, 0, -999999999, 0, 100, 1000, 0);
	
	$datas['color'] = array('red', 'blue', 'green', '', '', '', '', '');
	$datas['icon'] = array('image/group/admin.gif', 'image/group/infor.gif', 'image/group/vip.gif', '', '', '', '', '');

	//基本设置
	$datas['maxfriendnum'] = array(0, 0, 0, 10, 100, 200, 300, 1);
	$datas['maxattachsize'] = array(0, 0, 0, 10, 20, 50, 100, 1);
	$datas['postinterval'] = array(0, 0, 0, 300, 60, 30, 10, 9999);
	$datas['searchinterval'] = array(0, 0, 0, 600, 60, 30, 10, 9999);

	$datas['allowhtml'] = array(1, 1, 1, 0, 0, 0, 1, 0);
	$datas['allowcss'] = array(1, 1, 1, 0, 0, 0, 1, 0);
	$datas['allowcomment'] = array(1, 1, 1, 0, 1, 1, 1, 0);
	$datas['allowblog'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowdoing'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowupload'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowshare'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowmtag'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowthread'] = array(1, 1, 1, 1, 1, 1, 1, 0);
	$datas['allowpost'] = array(1, 1, 1, 0, 1, 1, 1, 0);
	$datas['allowpoke'] = array(1, 1, 1, 0, 1, 1, 1, 0);
	$datas['allowfriend'] = array(1, 1, 1, 0, 1, 1, 1, 0);
	$datas['allowtrace'] = array(1, 1, 1, 0, 1, 1, 1, 0);

	$datas['domainlength'] = array(1, 3, 3, 0, 0, 5, 3, 99);
	$datas['closeignore'] = array(1, 1, 0, 0, 0, 0, 0, 0);
	$datas['seccode'] = array(0, 0, 0, 1, 0, 0, 0, 1);

	//管理权限
	//站点设置
	foreach (array('config','usergroup','credit','profilefield','profield','censor','ad','sitefeed','cache','block','template','backup','stat','space','cron','app', 'network','name','task','report') as $value) {
		$datas['manage'.$value] = array(1, 0, 0, 0, 0, 0, 0, 0);
	}

	//信息管理
	foreach (array('tag','mtag','feed','share','doing', 'blog','album','comment','thread') as $value) {
		$datas['manage'.$value] = array(1, 1, 0, 0, 0, 0, 0, 0);
	}

	$keys = array_keys($datas);
	$newdatas = array();
	$g_count = count($datas['grouptitle']);
	for ($i=0; $i<$g_count; $i++) {
		$thes = array();
		foreach ($keys as $value) {
			$thes[] = $datas[$value][$i];
		}
		$newdatas[$i] = "(".simplode($thes).")";
	}
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('usergroup'));
	$_SGLOBAL['db']->query("REPLACE INTO ".tname('usergroup')." (".implode(',', $keys).") VALUES ".implode(',', $newdatas));

	//积分规则
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('data'));
	$ins = array(
		'get' => array (
			'blog' => 2,
			'pic' => 1,
			'comment' => 1,
			'thread' => 2,
			'post' => 1,
			'invite' => 10
		),
		'pay' => array (
			'blog' => 2,
			'pic' => 1,
			'comment' => 1,
			'thread' => 2,
			'post' => 1,
			'search' => 1,
			'attach' => 10,
			'xmlrpc' => 5,
			'invite' => 0,
			'domain' => 10,
			'realname' => 10
		)
	);
	data_set('creditrule', $ins);

	//邮件设置
	$mails = array(
		'mailsend' => 1,
		'maildelimiter' => 0,
		'mailusername' => 1
	);
	data_set('mail', $mails);

	//缩略图设置
	$settings = array(
		'thumbwidth' => 100,
		'thumbheight' => 100,
		'watermarkpos' => 4,
		'watermarktrans' => 75
	);
	data_set('setting', $settings);

	//计划任务
	$datas = array(
		"1, 'system', '更新浏览数统计', 'log.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'",
		"1, 'system', '清理过期feed', 'cleanfeed.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 3, '4'",
		"1, 'system', '清理个人通知', 'cleannotification.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 5, '6'",
		"1, 'system', '同步UC的feed', 'getfeed.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, -1, '2	7	12	17	22	27	32	37	42	47	52'",
		"1, 'system', '清理脚印和最新访客', 'cleantrace.php', $_SGLOBAL[timestamp], $_SGLOBAL[timestamp], -1, -1, 2, '3'"
	);
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('cron'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('cron')." (available, type, name, filename, lastrun, nextrun, weekday, day, hour, minute) VALUES (".implode('),(', $datas).")");

	//用户任务
	$datas = array();
	$datas[] = "1, '更新一下自己的头像', '头像就是你在这里的个人形象。<br>设置好了可获得特别惊喜：<br>本站会自动寻找优秀的异性朋友推荐给您。<br>您不赶快试试？', 'avatar.php', 1, 0, 20, 'image/task/avatar.gif'";
	$datas[] = "1, '将个人资料补充完整', '把自己的个人资料填写完整吧。<br>这样您会被更多的朋友找到的，系统也会帮您找到朋友。', 'profile.php', 2, 0, 20, 'image/task/profile.gif'";
	$datas[] = "1, '发表自己的第一篇日志', '现在，就写下自己的第一篇日志吧。<br>与大家一起分享自己的生活感悟。', 'blog.php', 3, 0, 5, 'image/task/blog.gif'";
	$datas[] = "1, '寻找并添加五位好友', '有了好友，您发的日志、图片等会被好友及时看到并传播出去；<br>您也会在首页方便及时的看到好友的最新动态。<br>这会让您在这里的生活变得丰富多彩。', 'friend.php', 4, 0, 50, 'image/task/friend.gif'";
	$datas[] = "1, '验证激活自己的邮箱', '填写自己真实的邮箱地址并验证通过。<br>您可以在忘记密码的时候使用该邮箱取回自己的密码；<br>还可以及时接受站内的好友通知等等。<br>这对您十分有帮助和必要。', 'email.php', 5, 0, 10, 'image/task/email.gif'";
	$datas[] = "1, '邀请10个新朋友加入', '邀请一下自己的QQ好友或者邮箱联系人，让亲朋好友一起来加入我们吧。', 'invite.php', 6, 0, 100, 'image/task/friend.gif'";
	$datas[] = "1, '领取每日积分大礼包', '每天登录访问自己的主页，就可领取积分大礼包。<br>还可免费获赠本周《热门日志排行榜》导读一份。', 'gift.php', 99, 86400, 2, 'image/task/gift.gif'";
	
	$_SGLOBAL['db']->query("TRUNCATE TABLE ".tname('task'));
	$_SGLOBAL['db']->query("INSERT INTO ".tname('task')." (`available`, `name`, `note`, `filename`, `displayorder`, `nexttime`, `credit`, `image`) VALUES (".implode('),(', $datas).")");
	
	
	show_msg('系统默认数据添加完毕，进入下一步操作', ($step+1), 1);

} elseif ($step == 5) {

	//更新缓存
	dbconnect();
	include_once(S_ROOT.'./source/function_cache.php');

	config_cache();//缓存
	usergroup_cache();//用户组
	profilefield_cache();//群组栏目
	profield_cache();//群组栏目
	creditrule_cache();//积分
	task_cache();//任务

	$msg = <<<EOF
	<form method="post" action="$theurl">
	<table>
	<tr><td colspan="2">程序数据安装完成!<br><br>
	最后，请输入您在用户中心UCenter的用户名和密码<br>系统将自动为您开通站内第一个空间，并将您设为管理员!
	</td></tr>
	<tr><td>您的用户名</td><td><input type="text" name="username" value="" size="30"></td></tr>
	<tr><td>您的密码</td><td><input type="password" name="password" value="" size="30"></td></tr>
	<tr><td></td><td><input type="submit" name="opensubmit" value="开通管理员空间"></td></tr>
	</table>
	<input type="hidden" name="formhash" value="$formhash">
	</form>
EOF;

	show_msg($msg, 999);
}

//页面头部
function show_header() {
	global $_SGLOBAL, $nowarr, $step, $theurl, $_SC;

	$nowarr[$step] = ' class="current"';
	print<<<END
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=$_SC[charset]" />
	<title> UCenter Home 程序安装 </title>
	<style type="text/css">
	* {font-size:12px; font-family: Verdana, Arial, Helvetica, sans-serif; line-height: 1.5em; word-break: break-all; }
	body { text-align:center; margin: 0; padding: 0; background: #F5FBFF; }
	.bodydiv { margin: 40px auto 0; width:720px; text-align:left; border: solid #86B9D6; border-width: 5px 1px 1px; background: #FFF; }
	h1 { font-size: 18px; margin: 1px 0 0; line-height: 50px; height: 50px; background: #E8F7FC; color: #5086A5; padding-left: 10px; }
	#menu {width: 100%; margin: 10px auto; text-align: center; }
	#menu td { height: 30px; line-height: 30px; color: #999; border-bottom: 3px solid #EEE; }
	.current { font-weight: bold; color: #090 !important; border-bottom-color: #F90 !important; }
	.showtable { width:100%; border: solid; border-color:#86B9D6 #B2C9D3 #B2C9D3; border-width: 3px 1px 1px; margin: 10px auto; background: #F5FCFF; }
	.showtable td { padding: 3px; }
	.showtable strong { color: #5086A5; }
	.datatable { width: 100%; margin: 10px auto 25px; }
	.datatable td { padding: 5px 0; border-bottom: 1px solid #EEE; }
	input { border: 1px solid #B2C9D3; padding: 5px; background: #F5FCFF; }
	.button { margin: 10px auto 20px; width: 100%; }
	.button td { text-align: center; }
	.button input, .button button { border: solid; border-color:#F90; border-width: 1px 1px 3px; padding: 5px 10px; color: #090; background: #FFFAF0; cursor: pointer; }
	#footer { font-size: 10px; line-height: 40px; background: #E8F7FC; text-align: center; height: 38px; overflow: hidden; color: #5086A5; margin-top: 20px; }
	</style>
	<script type="text/javascript">
	function $(id) {
		return document.getElementById(id);
	}
	//添加Select选项
	function addoption(obj) {
		if (obj.value=='addoption') {
			var newOption=prompt('请输入:','');
			if (newOption!=null && newOption!='') {
				var newOptionTag=document.createElement('option');
				newOptionTag.text=newOption;
				newOptionTag.value=newOption;
				try {
					obj.add(newOptionTag, obj.options[0]); // doesn't work in IE
				}
				catch(ex) {
					obj.add(newOptionTag, obj.selecedIndex); // IE only
				}
				obj.value=newOption;
			} else {
				obj.value=obj.options[0].value;
			}
		}
	}
	</script>
	</head>
	<body id="append_parent">
	<div class="bodydiv">
	<h1>UCenter Home程序安装</h1>
	<div style="width:90%;margin:0 auto;">
	<table id="menu">
	<tr>
	<td{$nowarr[0]}>1.安装开始</td>
	<td{$nowarr[1]}>2.设置UCenter信息</td>
	<td{$nowarr[2]}>3.设置数据库连接信息</td>
	<td{$nowarr[3]}>4.创建数据库结构</td>
	<td{$nowarr[4]}>5.添加默认数据</td>
	<td{$nowarr[5]}>6.安装完成</td>
	</tr>
	</table>
END;
}

//页面顶部
function show_footer() {
	print<<<END
	</div>
	<iframe id="phpframe" name="phpframe" width="0" height="0" marginwidth="0" frameborder="0" src="about:blank"></iframe>
	<div id="footer">&copy; Comsenz Inc. 2001-2008 u.discuz.net</div>
	</div>
	<br>
	</body>
	</html>
END;
}


//显示
function show_msg($message, $next=0, $jump=0) {
	global $theurl;

	$nextstr = '';
	$backstr = '';

	obclean();
	if(empty($next)) {
		$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
	} elseif ($next == 999) {
	} else {
		$url_forward = "$theurl?step=$next";
		if($jump) {
			$nextstr .= "<a href=\"$url_forward\">请稍等...</a><script>setTimeout(\"window.location.href ='$url_forward';\", 1000);</script>";
		} else {
			$nextstr .= "<a href=\"$url_forward\">继续下一步</a>";
			$backstr .= "<a href=\"javascript:history.go(-1);\">返回上一步</a>";
		}
	}

	show_header();
	print<<<END
	<table>
	<tr><td>$message</td></tr>
	<tr><td>&nbsp;</td></tr>
	<tr><td>$backstr $nextstr</td></tr>
	</table>
END;
	show_footer();
	exit();
}

//检查权限
function checkfdperm($path, $isfile=0) {
	if($isfile) {
		$file = $path;
		$mod = 'a';
	} else {
		$file = $path.'./install_tmptest.data';
		$mod = 'w';
	}
	if(!@$fp = fopen($file, $mod)) {
		return false;
	}
	if(!$isfile) {
		//是否可以删除
		fwrite($fp, ' ');
		fclose($fp);
		if(!@unlink($file)) {
			return false;
		}
		//检测是否可以创建子目录
		if(is_dir($path.'./install_tmpdir')) {
			if(!@rmdir($path.'./install_tmpdir')) {
				return false;
			}
		}
		if(!@mkdir($path.'./install_tmpdir')) {
			return false;
		}
		//是否可以删除
		if(!@rmdir($path.'./install_tmpdir')) {
			return false;
		}
	} else {
		fclose($fp);
	}
	return true;
}

?>
