<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================| 
*/

define("NOVA_RUN", 1);

include "../includes/config.php";

global $db_prefix;

include "../scripts/php/functions.php";

$my_address="http://".$_SERVER['HTTP_HOST']."".$_SERVER['PHP_SELF'];

$nova_domain 	= str_replace('/rss.php', '', $my_address); 	// returns 

if ($_COOKIE['nova_name']==''){
$role="3";
}

// Is RSS turned on?
$query21 = "select SHOW_RSS, SHOW_RSS_LIMIT from {$db_prefix}settings" ;
$result21 = mysql_query($query21) or die("Query failed") ;                                
while ($results21 = mysql_fetch_array($result21)){
$show_rss = $results21['SHOW_RSS'];
$show_rss_limit = $results21['SHOW_RSS_LIMIT'];
}

if ($show_rss=='0'){

$rss_title= "Latest Posts";  
$rss_site= "$nova_domain";
$rss_description= "RSS Feed for Posts on $nova_domain";
$rss_language="en";                      
$emailadmin="";   
header("Content-Type: text/xml;charset=utf-8");
$phpversion = phpversion();

ECHO <<<END
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss">
    <channel>
      <title>$rss_title</title>
      <link>$rss_site</link>
      <description>$rss_description</description>
      <language>$rss_language-$rss_language</language>
      <docs>http://backend.userland.com/rss</docs>
      <generator>PHP/$phpversion</generator>
END;

ECHO <<<END
    <item>
<title>$nova_domain</title>
    <link>
$nova_domain
</link>
    <description>
RSS Feeds are turned off for this site.
</description>
<author>$emailadmin</author>
<media:title>$nova_domain</media:title>
    <media:text type="html">
RSS Feeds are turned off for this site.
</media:text>
<p>$nova_domain</p>
</item>
END;

ECHO <<<END
   </channel>
</rss>
END;



}
else{

$nova_name=$_COOKIE['nova_name'];
$nova_name=escape_string($nova_name);

$nova_password=$_COOKIE['nova_password'];
$nova_password=escape_string($nova_password);

$forum_id=$_GET['forum'];
$forum_id=escape_string($forum_id);	

$query21 = "select ROLE from {$db_prefix}members WHERE NAME ='$nova_name' AND PASSWORD='$nova_password'" ;
$result21 = mysql_query($query21) or die("Query failed") ;                                
while ($results21 = mysql_fetch_array($result21)){
$role = $results21['ROLE'];
}

		// PERMISSIONS!!! Can they view this forum???	
		
		$can_view_forum="0";
		$can_read_topics="0";
		$query3 = "select CAN_VIEW_FORUM, CAN_READ_TOPICS from {$db_prefix}permissions WHERE GROUP_ID='$role' AND FORUM_ID='$forum_id'" ;
		$result3 = mysql_query($query3) or die("Query failed") ;                                  
		while ($results3 = mysql_fetch_array($result3)){
		$can_view_forum = $results3['CAN_VIEW_FORUM'];
		$can_read_topics = $results3['CAN_READ_TOPICS'];
		}

if (($can_view_forum=='0') OR ($can_read_topics=='0')){
$rss_title= "Latest Posts";  
$rss_site= "$nova_domain" ;
$rss_description= "RSS Feed for Posts on $nova_domain";
$rss_language="en";                      
$emailadmin="";   
header("Content-Type: text/xml;charset=utf-8");
$phpversion = phpversion();

ECHO <<<END
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss">
    <channel>
      <title>$rss_title</title>
      <link>$rss_site</link>
      <description>$rss_description</description>
      <language>$rss_language-$rss_language</language>
      <docs>http://backend.userland.com/rss</docs>
      <generator>PHP/$phpversion</generator>
END;

ECHO <<<END
    <item>
<title>$nova_domain</title>
    <link>
$nova_domain
</link>
    <description>
You do not have permission to view this forum.
</description>
<author>$emailadmin</author>
<media:title>$nova_domain</media:title>
    <media:text type="html">
You do not have permission to view this forum.
</media:text>
<p>$nova_domain</p>
</item>
END;

ECHO <<<END
   </channel>
</rss>
END;
}

else{

$nova_name=$_COOKIE['nova_name'];
$nova_name=escape_string($nova_name);

$nova_password=$_COOKIE['nova_password'];
$nova_password=escape_string($nova_password);

$forum_id=$_GET['forum'];
$forum_id=escape_string($forum_id);	

$query5 = "select NAME from {$db_prefix}categories WHERE id='$forum_id'" ;
$result5 = mysql_query($query5) or die("Query failed") ;                                            
while ($results5 = mysql_fetch_array($result5))
  {
$name = $results5['NAME']; 

$name=htmlspecialchars($name);

}

$rss_title= "Latest Posts in $name";  
$rss_site= "$nova_domain" ;
$rss_description= "RSS Feed for Posts in $name on $nova_domain";
$rss_language="en";                      
$emailadmin="";   
header("Content-Type: text/xml;charset=utf-8");
$phpversion = phpversion();

ECHO <<<END
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss">
    <channel>
      <title>$rss_title</title>
      <link>$rss_site</link>
      <description>$rss_description</description>
      <language>$rss_language-$rss_language</language>
      <docs>http://backend.userland.com/rss</docs>
      <generator>PHP/$phpversion</generator>
END;



$query = "select TOPIC_ID, TITLE from {$db_prefix}posts WHERE forum_id='$forum_id' AND TITLE!='' ORDER BY LAST_POST_TIME desc LIMIT $show_rss_limit";
$result = mysql_query($query) or die("Query failed");
                                              
while ($results = mysql_fetch_array($result)){
$title = $results['TITLE'] ; 
$title = str_replace("&", "&amp;", $title);
$title = strip_slashes($title);
$topic_id = $results['TOPIC_ID'] ;


$query2 = "select ID, CONTENT from {$db_prefix}posts WHERE topic_id='$topic_id' ORDER BY ID desc LIMIT 1" ;
$result2 = mysql_query($query2) or die("Query failed") ;
while ($results2 = mysql_fetch_array($result2)){
$id = $results2['ID'] ;
$content = $results2['CONTENT'] ;


$content = preg_replace( "#\[hide\](.+?)\[/hide\]#is", "[hide]HIDDEN CONTENT[/hide]", $content );

$content = preg_replace( "#\[spoiler\](.+?)\[/spoiler\]#is", "[spoiler]SPOILER CONTENT[/spoiler]", $content );

$content = str_replace("&", "&amp;", $content);
$content = strip_slashes($content);


$content= strip_tags($content);

$content=htmlspecialchars($content);


}


$counter ++ ;

ECHO <<<END
    <item>
<title>$title</title>
    <link>
$nova_domain/index.php?page=findpost&amp;post=$id
</link>
    <description>
$content
</description>
<author>$emailadmin</author>
<media:title>$title</media:title>
    <media:text type="html">
$content
</media:text>
<p>$nova_domain</p>
</item>
END;

  }

mysql_close();

ECHO <<<END
   </channel>
</rss>
END;
}

}
?>