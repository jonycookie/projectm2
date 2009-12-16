<?php
/*
+--------------------------------------------------------------------------
|   NovaBoard
|   ========================================
|   By Dave Murchison
|   (c) 2009 NovaBoard
|   http://www.novaboard.net
|   ========================================
|   smilies.php - set emoticons to use in forum posts
 
*/

if (!defined('NOVA_RUN')){
	echo "<h1>ACCESS DENIED</h1>You cannot access this file directly.";
	exit();
}

template_hook("pages/admin/smilies.template.php", "start");

if ($can_change_forum_settings=='0'){

	nova_redirect("index.php?page=error&error=11","error/11");

}

else{

if ($_POST['form']!=''){

$location=escape_string($_POST['location']);

$location = str_replace(" ", "%20", $location);

$token_id = escape_string($_POST['token_id']);


$token_name = "token_smilies_$location$token_id";

 if (isset($_POST[$token_name]) && isset($_SESSION[$token_name]) && $_SESSION[$token_name] == $_POST[$token_name]){

$location=str_replace("%20", " ",$location);

// First.. remove what was there already...

mysql_query("DELETE FROM {$db_prefix}smilies WHERE THEME='$location'");

// Now loop and add in the new details..

// How many images were in the directory?

$last = escape_string($_POST['total_files']);

$counted="1";

for ( $counter = $counted; $counter <= $last; $counter += 1) {

$emoticon_on="emoticon_on"."$counter";
$emoticon="emoticon"."$counter";
$file="file"."$counter";

$emoticon_on=escape_string($_POST[$emoticon_on]);
$emoticon=escape_string($_POST[$emoticon]);
$file=escape_string($_POST[$file]);


mysql_query("INSERT INTO {$db_prefix}smilies (code, link, emoticon_on, theme) VALUES ('$emoticon', '$file', '$emoticon_on', '$location')");

}

	template_hook("pages/admin/smilies.template.php", "form");

	nova_redirect("index.php?page=admin&act=smilies","admin/smilies");

}
else{

	nova_redirect("index.php?page=error&error=28","error/28");

}

}
elseif (isset($_GET['location'])){


$token_id = md5(microtime());
$token = md5(uniqid(rand(),true));

$location=escape_string($_GET['location']);

$location = str_replace(" ", "%20", $location);

$token_name = "token_smilies_$location$token_id";

$_SESSION[$token_name] = $token;

template_hook("pages/admin/smilies.template.php", "2");

// Set smiley count to zero..

$smiley_count="0";

$location=str_replace("%20", " ",$location);

function list_emos($dir){

global $theme, $nova_domain, $db_prefix, $emoticon_path, $location, $board_lang, $smiley_count;

  if(is_dir($dir))
  {
    if($handle = opendir($dir))
    {
      while(($file = readdir($handle)) !== false)
      {
        if($file != "Thumbs.db" && $file!="index.html" /*pesky windows, images..*/){

$code="";
$link="";
$emoticon_on="0";

if ($location=='default'){
$query34 = "select CODE, LINK, EMOTICON_ON from {$db_prefix}smilies WHERE LINK='$file' AND THEME='default'";
}
else{
$query34 = "select CODE, LINK, EMOTICON_ON from {$db_prefix}smilies WHERE LINK='$file' AND THEME='$location'";
}
$result34 = mysql_query($query34) or die("smilies.php - Error in query: $query34") ;
$check_any_exist=mysql_num_rows($result34);



if ($check_any_exist=='1'){

while ($results34 = mysql_fetch_array($result34)){
$code = $results34['CODE'];
$link = $results34['LINK'];
$emoticon_on = $results34['EMOTICON_ON'];


global $file, $link, $emoticon_on, $code;

if ($code!=''){

if ($file!="." && $file!=".."){
$smiley_count=$smiley_count+1;
template_hook("pages/admin/smilies.template.php", "3");
}

}


}
}

else{

global $file, $link, $emoticon_on, $code;

if ($file!=''){

if ($file!="." && $file!=".."){
$smiley_count=$smiley_count+1;
template_hook("pages/admin/smilies.template.php", "4");
}
}

}
        }
      }

global $file, $link, $emoticon_on, $code;	

$smiley_count=$smiley_count+1;  
	  
template_hook("pages/admin/smilies.template.php", "5");

      closedir($handle);
    }
  }
}
$theme=str_replace("%20"," ",$theme);

$location=escape_string($_GET['location']);
$location=str_replace("%20", " ", $location);
$emoticon_path = "themes/$location/images/forums/emoticons";

if ($location=='default'){
$emoticon_path="images/forums/emoticons";
}

list_emos("$emoticon_path");

template_hook("pages/admin/smilies.template.php", "6");

}

else{

// select theme here
function list_themes_emos($dir){

global $nova_domain, $board_lang, $db_prefix;

  if(is_dir($dir)){
    if($handle = opendir($dir)){
      while(($file = readdir($handle)) !== false){
        if($file != "." && $file != ".." && $file != "Thumbs.db" && $file!="index.html" /*pesky windows, images..*/){

		if (file_exists("themes/$file/images/forums/emoticons")){
		
	$query34 = "select DISPLAY_NAME from {$db_prefix}themes WHERE THEME_NAME='$file'";
	$result34 = mysql_query($query34) or die("smilies.php - Error in query: $query34") ;
	$display_name=strip_slashes(mysql_result($result34, 0));	
		
				echo "<option value='$file'>$display_name</option>";
			}

		}
	  }
	}
      closedir($handle);
  }
}

$location = str_replace(" ", "%20", $location);

template_hook("pages/admin/smilies.template.php", "7");

}

}

template_hook("pages/admin/smilies.template.php", "end");
?>