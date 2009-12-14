<?php
# dbstructure.php - Generated Tue, 31 Oct 2006 02:32:21 +0000
#  Manually edited Sun, 02 Sep 2007 21:44:00 +0000

$drops[] = "DROP TABLE IF EXISTS icebb_adsess";
$creates[]= "CREATE TABLE `icebb_adsess` (
  `asid` varchar(32) NOT NULL default '',
  `user` varchar(64) NOT NULL default '',
  `ip` varchar(64) NOT NULL default '',
  `logintime` int(16) NOT NULL default '0',
  `location` varchar(64) NOT NULL default '',
  `last_action` int(16) NOT NULL default '0'
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_announcements";
$creates[]= "CREATE TABLE `icebb_announcements` (
  `aid` int(11) NOT NULL default '0',
  `aauthor` varchar(64) NOT NULL default '',
  `aauthorid` int(11) NOT NULL default '0',
  `adate` int(11) NOT NULL default '0',
  `atitle` varchar(128) NOT NULL default '',
  `atext` TEXT NOT NULL default '',
  `aforums` varchar(64) NOT NULL default ''
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_banfilters";
$creates[]= "CREATE TABLE `icebb_banfilters` (
  `bfid` int(11) NOT NULL,
  `type` varchar(64) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bfid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_banfilters CHANGE `bfid` `bfid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_bbcode";
$creates[]= "CREATE TABLE `icebb_bbcode` (
  `id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL default '',
  `replacement` TEXT NOT NULL default '',
  `php` TEXT NOT NULL default '',
  `nosmilies` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_bbcode CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_buddies";
$creates[]= "CREATE TABLE `icebb_buddies` (
  `id` int(11) NOT NULL,
  `owner` int(11) NOT NULL default '0',
  `type` tinyint(1) NOT NULL default '0',
  `uid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Buddies and blocked users'";
$inserts[] = "ALTER TABLE icebb_buddies CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_cache";
$creates[]= "CREATE TABLE `icebb_cache` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL default '',
  `content` longTEXT NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_cache CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_captcha";
$creates[]= "CREATE TABLE `icebb_captcha` (
  `id` varchar(32) NOT NULL default '',
  `word_num` int(11) NOT NULL default '0',
  `ip` varchar(64) NOT NULL default ''
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_failed_login_attempt_block";
$creates[]= "CREATE TABLE `icebb_failed_login_attempt_block` (
  `id` tinyint(11) NOT NULL,
  `ip` varchar(15) NOT NULL default '',
  `time` int(32) NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_failed_login_attempt_block CHANGE `id` `id` tinyint(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_failedlogin_attempts";
$creates[]= "CREATE TABLE `icebb_failedlogin_attempts` (
  `attempt_id` int(11) NOT NULL,
  `attempt_time` int(16) NOT NULL default '0',
  `attempt_ip` varchar(64) NOT NULL default '',
  `attempt_userid` int(11) NOT NULL default '0',
  `attempt_where` enum('acc','board') NOT NULL default 'acc',
  PRIMARY KEY  (`attempt_id`)
) TYPE=MyISAM COMMENT='Failed login attempts - similar to vBulletin'";
$inserts[] = "ALTER TABLE icebb_failedlogin_attempts CHANGE `attempt_id` `attempt_id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_favorites";
$creates[]= "CREATE TABLE `icebb_favorites` (
  `favid` int(11) NOT NULL,
  `favuser` int(11) NOT NULL default '0',
  `favtype` varchar(32) NOT NULL default '',
  `favobjid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`favid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_favorites CHANGE `favid` `favid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_favoritetopics";
$creates[]= "CREATE TABLE `icebb_favoritetopics` (
  `favid` int(11) NOT NULL,
  `favuser` int(11) NOT NULL default '0',
  `favtopic` int(11) NOT NULL default '0',
  PRIMARY KEY  (`favid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_favoritetopics CHANGE `favid` `favid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_forum_permgroups";
$creates[]= "CREATE TABLE `icebb_forum_permgroups` (
  `permid` int(11) NOT NULL,
  `permname` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`permid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_forum_permgroups CHANGE `permid` `permid` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_forum_permgroups VALUES('1','Administrators');";
$inserts[] = "INSERT INTO icebb_forum_permgroups VALUES('2','Members');";
$inserts[] = "INSERT INTO icebb_forum_permgroups VALUES('3','Validating Members');";
$inserts[] = "INSERT INTO icebb_forum_permgroups VALUES('4','Guests');";
$inserts[] = "INSERT INTO icebb_forum_permgroups VALUES('5','Banned');";
$drops[] = "DROP TABLE IF EXISTS icebb_forums";
$creates[]= "CREATE TABLE `icebb_forums` (
  `fid` int(11) NOT NULL,
  `sort` int(11) NOT NULL default '0',
  `name` varchar(64) NOT NULL default '',
  `description` TEXT NOT NULL default '',
  `parent` int(11) NOT NULL default '0',
  `replies` int(11) NOT NULL default '0',
  `topics` int(11) NOT NULL default '0',
  `postable` tinyint(1) NOT NULL default '0',
  `redirecturl` varchar(255) NOT NULL default '',
  `redirect_hits` int(11) NOT NULL default '0',
  `rss` TEXT NOT NULL default '',
  `perms` TEXT NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `moderators` TEXT NOT NULL default '',
  `lastpostid` int(11) NOT NULL default '0',
  `lastpost_time` int(16) NOT NULL default '0',
  `lastpost_title` varchar(64) NOT NULL default '',
  `lastpost_author` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`fid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_forums CHANGE `fid` `fid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_groups";
$creates[]= "CREATE TABLE `icebb_groups` (
  `gid` int(11) NOT NULL auto_increment,
  `g_title` varchar(64) NOT NULL default '',
  `g_view_board` tinyint(1) NOT NULL default '0',
  `g_is_mod` tinyint(1) NOT NULL default '0',
  `g_post_in_locked` tinyint(1) NOT NULL default '0',
  `g_is_admin` tinyint(1) NOT NULL default '0',
  `g_view_offline_board` tinyint(1) NOT NULL default '0',
  `g_permgroup` int(11) NOT NULL default '0',
  `g_desc` TEXT NOT NULL default '',
  `g_prefix` varchar(100) NOT NULL default '',
  `g_suffix` varchar(100) NOT NULL default '',
  `g_mods` varchar(255) NOT NULL default '',
  `g_status` enum('1','2','3') NOT NULL default '2',
  `g_icon` varchar(150) default NULL,
  `g_promote_group` int(11) NOT NULL default '0',
  `g_promote_posts` int(11) NOT NULL default '0',
  `g_flood_control` int(32) NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) TYPE=MyISAM";
$inserts[] = "INSERT INTO icebb_groups VALUES('1','Administrators','1','1','1','1','1','1','Administrators have full control of the forums.','<strong style=\\\"color:#b5133d\\\">','</strong>','1','0','2','0','0','0');";
$inserts[] = "INSERT INTO icebb_groups VALUES('2','Members','1','0','0','0','0','2','','','','','','2','0','0','0');";
$inserts[] = "INSERT INTO icebb_groups VALUES('3','Validating Members','1','0','0','0','0','3','','','','','','2','0','0','0');";
$inserts[] = "INSERT INTO icebb_groups VALUES('4','Guests','1','0','0','0','0','4','','','','','','2','0','0','0');";
$inserts[] = "INSERT INTO icebb_groups VALUES('5','Banned','0','0','0','0','0','5','','','','','','2','0','0','0');";
$inserts[] = "INSERT INTO icebb_groups VALUES('6','Moderators','1','1','1','0','0','1','','','','','2','','0','0','0');";
$drops[] = "DROP TABLE IF EXISTS icebb_help_sections";
$creates[]= "CREATE TABLE `icebb_help_sections` (
  `hsid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`hsid`)
) TYPE=MyISAM COMMENT='IceBB help sections'";
$inserts[] = "ALTER TABLE icebb_help_sections CHANGE `hsid` `hsid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_helpbits";
$creates[]= "CREATE TABLE `icebb_helpbits` (
  `hid` int(11) NOT NULL,
  `hsection` int(11) NOT NULL default '0',
  `hname` TEXT NOT NULL default '',
  `htext` TEXT NOT NULL default '',
  PRIMARY KEY  (`hid`)
) TYPE=MyISAM COMMENT='IceBB Help'";
$inserts[] = "ALTER TABLE icebb_helpbits CHANGE `hid` `hid` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('23','1','Calendar','Not much explaining because this does nothing useful.  It\'s just for show.  It just displays a calendar.  Nothing big about this.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('24','1','Forgotten Passwords','First off, I would like to point out that I have never used this function.  I am gonna try my best to explain how this works.<p>Let\'s say that you know your username but cosigned your password to oblivion.  Well, this sets a new, secure password for you.  Simply click right next to the password box on the Login screen where is says \\\"Forgot your Password?\\\"  Next, enter your email address and do the word verification thing again.  You will then, from what I believe, recieve an email containing your new password.  Just enter this into the password field when logging in and you can go into your User Control Center and change it.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('25','1','Layout','IceBB uses the typical board layout with header, body, and footer.  Each of these sections serve a purpose in IceBB\'s display.  Let\'s go over these in detail.<p><div style=\\\"border:thin solid green;border-left:medium solid green;\\\"><div style=\\\"color:white;font-weight:bold;background-color:green;\\\">Note: Subject to change</div>Different boards may have different skins, or looks, and thus, may not appear the same as the regular skin.  In this guide, we assume you are viewing a board without any modifications to the original skin.</div>
<div class=\\\"help2 helph\\\">Header</div>

The header is the area at the top of the display.  Going from top to bottom, there are 4 items that appear on every page of IceBB.
<div class=\\\"help3 helph\\\">Logo</div>
This is by far the most recognizable section on any website.  People recognize logos before even recognizing names when given both.  This bar has very little purpose but to put a logo on IceBB.  
<div class=\\\"help3 helph\\\">Navigation Bar</div>
This bar can link to you valuable information.  Not all of the links mentioned here will appear, but some boards will have them.
<div class=\\\"help4 helph\\\">Site Home</div>
This link may have different names to it based on what the administrator puts into the text box in the control panel, but it serves one purpose.  This link links to the site\'s homepage.  Take note that this links out of the IceBB package, but we, however, cannot tell you what to expect.
<div class=\\\"help4 helph\\\">Rules</div>
As you can guess it, this links you to the board rules.  These are set by an administrator and set up a set of organization to be followed.  This, along with site home, may not always appear.
<div class=\\\"help4 helph\\\">Home</div>
This is the board home.  This will take you to the section of IceBB called the Board View, the first page that usually comes up on IceBB.
<div class=\\\"help4 helph\\\">Calendar</div>

This links you to a calendar that can inform you of upcoming events that affect the entire community on said board.
<div class=\\\"help4 helph\\\">Members</div>
Isn\'t it nice to know how many there are on a particular board?  This gives you a link to an extensive list of all of the users on the board.  That list also links you to their profiles.
<div class=\\\"help4 helph\\\">Search</div>
This features let\'s you search the board\'s posts for specified words.  This can be useful on company boards and will let you look up solutions to your problem if the problem is located somewhere in the vast abyss of topics and posts.
<div class=\\\"help4 helph\\\">Help</div>
This is what I presume that most of you are reading right now.  This section is a remarkable work of art meant to give users an in depth, yet easy to understand, guide of the IceBB system.<p><div class=\\\"help3 helph\\\">Member Bar</div>
This changes based on your user status.  If you are logged out (logged in as guest), you will see two links, register and login.  Clicking register will let you create a username and password.  Clicking login will allow you to sign into your account.  Upon login, the bar will change.  Your username will become a link to your member profile.  There will also be a link to access your private (personal) messages.  You can also view all of the new posts since your last visit using a link in this bar.  Next is the link to the User Control Center, an area that lets you manage all of your user information and settings.  And finally, you see a Logout link which will log you into a guest account.  And for Administrators, an Administrative Control Center link will also appear on this bar.<p><div class=\\\"help3 helph\\\">Breadcrumbs</div>
In the story Hanzel and Gretel, the two children laid a trail of breadcrumbs to find your way back.  These act for the same purpose.  These breadcrumbs tell you where you are in the board and let you go back.<p><div class=\\\"help2 helph\\\">Body (Content)</div>

There is not much that can be said right now about this section because it changes for each page.  This is where the main content will appear.<p><div class=\\\"help2 helph\\\">Footer</div>
This area is subject to change dramatically.  This section contains just technical information and the copyright.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('26','1','Login','Remember that username and password you registered with?  Well, click Login on the Member Bar.  You can enter your username and password into the text boxes.  When you click Login, assuming you entered the right information, you will be logged into IceBB.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('27','1','Logout','This is probably one of the least used functions in all bulletin board systems.  Quite a few users just keep their cookies, or little bits of data that keep information on what you do on websites that also taste horrible, active and open a potential security risk.  Another user can go onto the computer and start vandalizing your name on a board and can end up getting you banned without knowing.  So logging out can save your data as it makes sure that you are not already logged in when someone else uses the computer after you.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('28','1','Main','IceBB can be a challenge to novice users.  This section of the guide attempts to help them out by giving a detailed explanation of anything they will encounter.<p><b>General</b><br />
<!--a href=\\\"index.php?act=help&title=Overview\\\"-->What is IceBB?<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Layout\\\"-->How the page is organized: Layout<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Structure\\\"-->How the system is organized: Structure<!--/a--><br /><p><b>Basic User Functions</b><br />

<!--a href=\\\"index.php?act=help&title=Registration\\\"-->Getting an account: Registration<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Login\\\"-->Gaining extra privileges: Login<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Logout\\\"-->Keeping idle computers secure: Logout<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Forgotten Passwords\\\"-->Forget something? Retrieving a forgotten password<!--/a--><br /><p><b>Board</b><br />
<!--a href=\\\"index.php?act=help&title=Topics\\\"-->Starting a topic<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Replies\\\"-->Expressing opinions: Replying<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Polls\\\"-->Gathering information: Starting a poll<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Quoting\\\"-->Referring to a previous post: Quotes<!--/a--><br />

<!--a href=\\\"index.php?act=help&title=Edit\\\"-->Woops: Editing a post<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=BBCode\\\"-->Enhancing your posts: BBCode<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Report\\\"-->Reporting a rule-breaking post<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=RSS\\\"-->Getting RSS feeds of topics<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=RSSForum\\\"-->Forums that act as RSS readers<!--/a--><br /><p><b>Navbar Functions</b><br />
<!--a href=\\\"index.php?act=help&title=Rules\\\"-->Board Rules<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Calendar\\\"-->Calendar<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=Members\\\"-->Member List<!--/a--><br />

<!--a href=\\\"index.php?act=help&title=Search\\\"-->Searching the topics: Search<!--/a--><br /><p><b>User Control Center</b><br />
<!--a href=\\\"index.php?act=help&title=UCC Main\\\"-->Main<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=UCC Profile\\\"-->User Profiles and how to set yours<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=UCC Avatar\\\"-->Getting an image under your name: Avatars<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=UCC Signatrue\\\"-->Add a common footer to your posts: Signatures<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=UCC Favorite Topics\\\"-->Favorite Topics<!--/a--><br />
<!--a href=\\\"index.php?act=help&title=UCC Other Settings\\\"-->Other Settings<!--/a--><br /><p><b>Private Messages</b><br />
<!--a href=\\\"index.php?act=help&title=PM Compose\\\"-->Compose<!--/a--><br />

<!--a href=\\\"index.php?act=help&title=PM View\\\"-->View<!--/a--><br />');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('29','1','Members','Ever how many others there are on the board?  This list gives you a comprehensive directory of every user on the board and links to their profiles.  It also displays what user group they are in (most should be under members...but then again, your administrator may think that a large staff will bring people in).<p><div style=\\\"border:thin solid red;border-left:medium solid red;\\\"><div style=\\\"color:white;font-weight:bold;background-color:red;\\\">Warning: Join Date|This feature has not yet been implemented}}<p>Next up is Posts.  This displays the user\'s post count (just for comparitive reasons).  Also, there is a function in which users can email you using this list but that can be disabled user your User Control Center.<p>{{Warning|Avatar|This feature has not yet been implemented}}<p>{{Warning|Photo</div>This feature has not yet been implemented</div>');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('30','1','Overview','IceBB is a powerful, reliable, and completely free forum solution. It\'s designed to be
flexible, scalable, fast, and most importantly, secure. It includes a powerful admin
control center that allows you to change nearly any aspect of your board.<p>IceBB is open-source by definition and always will be. We do offer two premium services:
copyright removal and registration, however those are not required.<p>--MutantMonkey 15:28, 26 Apr 2005 (UTC)');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('31','1','Polls','<ol>
<li>Click attach poll on the New Topic screen</li>
<li>Enter the question for the poll</li>
<li>Choose single (only one answer) or multiple (multiple)</li>
<li>Enter the choices for the poll</li>
<li>Continue adding a topic as normal</li>
</ol>');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('32','1','Registration','Reigstering is a multistep process.  Yet, it is rather quick.<p><div class=\\\"help2 helph\\\">Why should I register?</div>

Registering a username on a board gives you whatever extra permissions the Board Administrators set up.  It also gives you an unique identity on the board.<p><div class=\\\"help2 helph\\\">How do I register?</div>
On the member bar, click Register.  The Terms and Conditions will be displayed.  We recommend reading this.  When done, if you are ok with them, click accept.  Clicking Deny will instantly cancel the registration.<p>Now a form will be displayed.  First, enter in the username you want.  Don\'t worry, if it\'s taken, IceBB will inform you.  Next, enter your password into the password boxes.  The second box is meant to make sure you typed it correctly.  So you essentially type your password twice.  Next, enter your email address.<p><div style=\\\"border:thin solid green;border-left:medium solid green;\\\"><div style=\\\"color:white;font-weight:bold;background-color:green;\\\">Note: Why one?</div>You may notice that Beta 6 doesn\'t have two email boxes anymore.  Validating the password is neccessary because it is hidden.  Email, however, is in plain sight.  So you don\'t need to check your spelling on your email.</div><p>The email will be used for activating your account later.  After entering your email, select your timezone.  I assume you know what it is as I do not want to type all of them into this file.  Anyways, finally, you do the Word Verification.  For those who are curious, this is actually called CAPTCHA.  You just have to enter what you see in the picture into the textbox below.  Now, click Register.<p>You may be told you check your email because the board uses email verification.  The instructions for this are included in the email.  If you don\'t recieve this message, continue and log in.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('33','1','Replies','<div class=\\\"help2 helph\\\">Using Quick Reply</div>
<ol>
<li>At the bottom of the topic view, click Quick Reply</li>
<li>Enter your post in the text area that appears.</li>

<li>Click Add Reply</li>
</ol>
<div class=\\\"help2 helph\\\">Using Add Reply</div>
<ol>
<li>At the bottom of the topic view, click Add Reply</li>
<li>Enter your post in the text area.</li>
<li>Click Add Reply</li>
</ol>');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('34','1','Rules','The board rules are meant to keep order on a message board.  This protects from spamming (but in some cases provokes it) and keeps a system of punishments on hand for those who break the rules.');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('35','1','Search','This feature implements extensive Ajax technology through loaders, reloads, fetch arrays, and more technical jargon you do not need to know.  What this feature actually does is shows a small little windo with a text box in it.  Type in the words you want to find and hit enter.  IceBB will then search all of the posts and Topics for those words.  The results of this search will be returned in a window.<p><div style=\\\"border:thin solid orange;border-left:medium solid orange;\\\"><div style=\\\"color:white;font-weight:bold;background-color:orange;\\\">Caution: Disfunctional Back</div>When you hit back after finding the wrong result to check more, the results will not reappear.  This can be claimed as annoying because you have to retype the search terms.  Don\'t bother hitting back, just create a new search by hitting search.</div><p><div style=\\\"border:thin solid green;border-left:medium solid green;\\\"><div style=\\\"color:white;font-weight:bold;background-color:green;\\\">Note: Classic Search</div>For those who liked the old method (with a functional back button), just right click the search link.</div>');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('36','1','Structure','IceBB has a typical structure.  And by structure, I mean how the information is organized.  Let\'s go the first level you would typically encounter to the last.<p><div class=\\\"help2 helph\\\">Board View</div>
Imagine IceBB as a tree.  The main trunk would be where you start.  This trunk area is called the board view.  It allows you to get to anywhere on the board.<p><div class=\\\"help2 helph\\\">Root Forums</div>
These are sections that act as categories on the board view.<p><div class=\\\"help2 helph\\\">Forums</div>

This is another level into the board.  There will be topic listings on these views usually.  Also, forums may also have forums inside of them, but I won\'t go into this subject as there can be unlimited subforums.<p><div class=\\\"help2 helph\\\">Topics</div>
These are where most discussion takes place.  Each topic starts off with a post and then extends.<p><div class=\\\"help2 helph\\\">Posts</div>
These make up topics.  Each post is a different opinion from a member on the board.  Most of them are replies, but some are topic roots (the first post in the topic).');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('37','1','Topics','A topic is basically a string of posts.  One will start the topic and others will reply to the topic\'s posts.<p><div class=\\\"help2 helph\\\">To start a topic</div>
<ol>
<li>Choose a forum to create a topic in</li>
<li>Click New Topic</li>

<li>Enter a name for your topic</li>
<li>Enter a description (optional)</li>
<li>Create your post</li>
<li>Click New Topic</li>
</ol>');";
$inserts[] = "INSERT INTO icebb_helpbits VALUES('38','1','UCC Main','Something Mutant better change soon <_<.');";
$drops[] = "DROP TABLE IF EXISTS icebb_langbits";
$creates[]= "CREATE TABLE `icebb_langbits` (
  `langbit_id` int(11) NOT NULL,
  `langbit_lang` varchar(5) NOT NULL default '',
  `langbit_group` varchar(64) NOT NULL default '',
  `langbit_name` varchar(64) NOT NULL default '',
  `langbit_text` TEXT NOT NULL default '',
  PRIMARY KEY  (`langbit_id`)
) TYPE=MyISAM COMMENT='IceBB Languages'";
$inserts[] = "ALTER TABLE icebb_langbits CHANGE `langbit_id` `langbit_id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_langs";
$creates[]= "CREATE TABLE `icebb_langs` (
  `lang_id` int(11) NOT NULL,
  `lang_short` varchar(5) NOT NULL default '',
  `lang_name` varchar(64) NOT NULL default '',
  `lang_charset` varchar(32) NOT NULL default '',
  `lang_is_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`lang_id`)
) TYPE=MyISAM COMMENT='IceBB Languages'";
$inserts[] = "ALTER TABLE icebb_langs CHANGE `lang_id` `lang_id` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_langs VALUES('1','en','English','','1');";
$drops[] = "DROP TABLE IF EXISTS icebb_logs";
$creates[]= "CREATE TABLE `icebb_logs` (
  `id` int(11) NOT NULL,
  `time` int(11) NOT NULL default '0',
  `user` varchar(64) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `action` TEXT NOT NULL default '',
  `forum_id` INT( 11 ) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_logs CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_moderators";
$creates[]= "CREATE TABLE `icebb_moderators` (
  `mid` int(11) NOT NULL,
  `mforum` int(11) NOT NULL default '0',
  `muserid` int(11) NOT NULL default '0',
  `muser` varchar(64) NOT NULL default '',
  `medit` tinyint(1) NOT NULL default '0',
  `medit_topic` tinyint(1) NOT NULL default '0',
  `mdel` tinyint(1) NOT NULL default '0',
  `mdel_topic` tinyint(1) NOT NULL default '0',
  `mview_ip` tinyint(1) NOT NULL default '0',
  `mlock` tinyint(1) NOT NULL default '0',
  `munlock` tinyint(1) NOT NULL default '0',
  `m_multi_move` tinyint(1) NOT NULL default '0',
  `m_multi_del` tinyint(1) NOT NULL default '0',
  `mmove` tinyint(1) NOT NULL default '0',
  `mpin` tinyint(1) NOT NULL default '0',
  `munpin` tinyint(1) NOT NULL default '0',
  `mwarn` tinyint(1) NOT NULL default '0',
  `medit_user` tinyint(1) NOT NULL default '0',
  `mgroup_id` int(11) NOT NULL default '0',
  `mgroup` varchar(64) NOT NULL default '',
  `m_is_group` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`mid`)
) TYPE=MyISAM COMMENT='IceBB Moderators'";
$inserts[] = "ALTER TABLE icebb_moderators CHANGE `mid` `mid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_plugins";
$creates[]= "CREATE TABLE `icebb_plugins` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM COMMENT='Sollievo Plugins'";
$inserts[] = "ALTER TABLE icebb_plugins CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_pm_posts";
$creates[]= "CREATE TABLE `icebb_pm_posts` (
  `pid` int(11) NOT NULL,
  `ptopicid` int(11) NOT NULL default '0',
  `pname` varchar(255) NOT NULL default '',
  `picon` varchar(255) NOT NULL default '',
  `pauthor_id` int(11) NOT NULL default '0',
  `pauthor` varchar(64) NOT NULL default '',
  `pauthor_ip` varchar(16) NOT NULL default '',
  `pdate` int(10) NOT NULL default '0',
  `pedit_show` tinyint(1) NOT NULL default '0',
  `pedit_author` varchar(64) NOT NULL default '',
  `pedit_time` int(10) NOT NULL default '0',
  `phide` tinyint(1) NOT NULL default '0',
  `ptext` TEXT NOT NULL default '',
  PRIMARY KEY  (`pid`),
  FULLTEXT KEY `ptext` (`ptext`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_pm_posts CHANGE `pid` `pid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_pm_topics";
$creates[]= "CREATE TABLE `icebb_pm_topics` (
  `tid` int(11) NOT NULL,
  `forum` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `snippet` varchar(255) NOT NULL default '',
  `replies` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `starter` varchar(64) NOT NULL default '',
  `owner` int(11) NOT NULL default '0',
  `lastpost_time` int(10) NOT NULL default '0',
  `lastpost_author` varchar(64) NOT NULL default '',
  `pm_identifier` int(11) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_pm_topics CHANGE `tid` `tid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_poll_voters";
$creates[]= "CREATE TABLE `icebb_poll_voters` (
  `voterid` int(11) NOT NULL,
  `voterpollid` int(11) NOT NULL default '0',
  `voteruser` int(11) NOT NULL default '0',
  `voterip` varchar(16) NOT NULL default '',
  PRIMARY KEY  (`voterid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_poll_voters CHANGE `voterid` `voterid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_polls";
$creates[]= "CREATE TABLE `icebb_polls` (
  `pollid` int(11) NOT NULL,
  `polltid` int(11) NOT NULL default '0',
  `pollq` varchar(255) NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  `pollopt` TEXT NOT NULL default '',
  PRIMARY KEY  (`pollid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_polls CHANGE `pollid` `pollid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_posts";
$creates[]= "CREATE TABLE `icebb_posts` (
  `pid` int(11) NOT NULL default '0',
  `ptopicid` int(11) NOT NULL default '0',
  `pname` varchar(255) NOT NULL default '',
  `picon` varchar(255) NOT NULL default '',
  `pauthor` varchar(64) NOT NULL default '',
  `pauthor_id` int(11) NOT NULL default '0',
  `pauthor_ip` varchar(16) NOT NULL default '',
  `pdate` int(10) NOT NULL default '0',
  `pedit_show` tinyint(1) NOT NULL default '0',
  `pedit_author` varchar(64) NOT NULL default '',
  `pedit_time` int(10) NOT NULL default '0',
  `pedits` TEXT NOT NULL default '',
  `pviews` int(11) NOT NULL default '0',
  `phide` tinyint(1) NOT NULL default '0',
  `pis_firstpost` tinyint(1) NOT NULL default '0',
  `ptext` TEXT NOT NULL default '',
  PRIMARY KEY  (`pid`),
  FULLTEXT KEY `ptext` (`ptext`)
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_ra_logs";
$creates[]= "CREATE TABLE `icebb_ra_logs` (
  `id` int(11) NOT NULL default '0',
  `time` int(11) NOT NULL default '0',
  `user` varchar(64) NOT NULL default '',
  `ip` varchar(16) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `action` varchar(128) NOT NULL default '',
  `forum_id` INT( 11 ) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_ranks";
$creates[]= "CREATE TABLE `icebb_ranks` (
  `rid` int(11) NOT NULL,
  `rposts` int(11) NOT NULL default '0',
  `rtitle` varchar(255) NOT NULL default '',
  `rpips` int(11) NOT NULL default '0',
  `rimg` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rid`)
) TYPE=MyISAM COMMENT='IceBB Ranks'";
$inserts[] = "ALTER TABLE icebb_ranks CHANGE `rid` `rid` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_ranks VALUES('1','0','Newbie','1','');";
$inserts[] = "INSERT INTO icebb_ranks VALUES('2','10','Member','2','');";
$inserts[] = "INSERT INTO icebb_ranks VALUES('3','100','Advanced Member','3','');";
$inserts[] = "INSERT INTO icebb_ranks VALUES('4','500','Senior Member','4','');";
$drops[] = "DROP TABLE IF EXISTS icebb_search_results";
$creates[]= "CREATE TABLE `icebb_search_results` (
  `search_id` varchar(32) NOT NULL default '',
  `search_query` varchar(255) NOT NULL default '',
  `topic_ids` TEXT NOT NULL default '',
  `topic_num` int(11) NOT NULL default '0',
  `post_ids` TEXT NOT NULL default '',
  `post_num` int(11) NOT NULL default '0',
  `search_date` int(16) NOT NULL default '0',
  `search_uid` int(11) NOT NULL default '0',
  `search_uip` varchar(32) NOT NULL default '',
  `search_sort` varchar(64) NOT NULL default '',
  `search_query_cache` TEXT NOT NULL default ''
) TYPE=MyISAM COMMENT='IceBB Search Results'";
$drops[] = "DROP TABLE IF EXISTS icebb_session_data";
$creates[]= "CREATE TABLE `icebb_session_data` (
  `sid` varchar(32) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `ip` varchar(64) NOT NULL default '',
  `user_agent` varchar(255) NOT NULL default '',
  `last_action` int(16) NOT NULL default '0',
  `act` varchar(64) NOT NULL default '',
  `func` varchar(64) NOT NULL default '',
  `topic` int(11) NOT NULL default '0',
  `forum` int(11) NOT NULL default '0',
  `profile` int(11) NOT NULL default '0'
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_settings";
$creates[]= "CREATE TABLE `icebb_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_group` int(11) NOT NULL default '0',
  `setting_title` varchar(255) NOT NULL default '',
  `setting_desc` TEXT NOT NULL default '',
  `setting_key` varchar(255) NOT NULL default '',
  `setting_type` varchar(255) NOT NULL default '',
  `setting_value` TEXT NOT NULL default '',
  `setting_default` TEXT NOT NULL default '',
  `setting_php` TEXT NOT NULL default '',
  `setting_sort` int(11) NOT NULL default '0',
  `setting_protected` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`setting_id`)
) TYPE=MyISAM PACK_KEYS=0";
$inserts[] = "ALTER TABLE icebb_settings CHANGE `setting_id` `setting_id` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_settings VALUES('1','1','Board Name','The name of the board','board_name','input','IceBB','IceBB','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('2','2','Board URL','The URL (web address) to your board
Be very careful when changing this.','board_url','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('3','2','Board Path','The path on the server to your board. This is <i>not</i> the URL to your board.','board_path','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('4','5','Is the board online?','You may want to turn the board off if you are doing maintenance or installing a mod.','is_board_online','yes_no','1','1','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('5','5','Message to display','The message that will appear to your members when the board is offline.','board_offline_msg','textarea','','','','0','0');";
$inserts[] = "INSERT INTO icebb_settings VALUES('29','2','Enable GZIP compression?','GZIP compression can help reduce bandwidth, but it places a slightly higher load on the server.','enable_gzip','yes_no','1','1','','4','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('7','7','Cookie Domain','This is the address you use to access your site. For example, if your site is at http://mysite.com/~mypage/, this would be .mysite.com','cookie_domain','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('8','7','Cookie Path','The path to your forums; this can be left blank','cookie_path','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('9','7','Cookie Prefix','Useful if multiple boards are installed on one site','cookie_prefix','input','icebb_','icebb_','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('10','4','Validate e-mail?','Require e-mail validation for all new users?','validate_email','yes_no','1','1','','2','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('11','8','Board Rules','The rules that will appear when a member selects \\\"Board Rules.\\\" HTML allowed, linebreaks automatically taken care of','board_rules','textarea','','','','2','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('12','8','Board Rules (on Registration page)','The rules that a member must agree to to register. HTML allowed, linebreaks taken care of. If left blank, the rules above will be used instead.','board_rules_reg','textarea','','','','3','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('13','8','Show rules link under navigation bar?','Do you wish to show a link to the forum rules under the navigation bar?','rules_show_link','yes_no','0','0','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('14','6','Log spider visits?','Do you want to keep a log of visits by spiders? This can be resource-intensive for sites that get crawled often.','log_spider_visits','yes_no','0','0','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('15','6','List of spiders','This is the list of spiders that you\'d like to be recognized and their \\\"human names\\\".','spider_list','textarea','googlebot=GoogleBot
msnbot=MSN Spider','googlebot=GoogleBot
msnbot=MSN Spider','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('16','1','Site Name','The name of your site. Leave blank to disable.','site_name','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('17','1','Site URL','The URL to your site.','site_url','input','','','','0','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('23','3','Hide version in copyright?','Do you want to hide the version in the copyright? This can help protect against attacks that exploit security holes in a certain version of IceBB.','hide_version_in_cpy','yes_no','0','0','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('24','3','Use word verification?','Word verification requires that a user proves that they are not a robot by typing the word they see in a box. You should leave this on unless you experience problems.','use_word_verification','yes_no','1','1','','2','1');";
//$inserts[] = "INSERT INTO icebb_settings VALUES('25','3','Use rel=\'nofollow\' in links?','Using this will cause links in topics not to be followed. It\'s recommended that you keep this on to keep spam down.','use_nofollow','yes_no','1','1','','3','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('26','10','Enable tagging?','Do you wish to allow people to tag topics?','tagging_enable','yes_no','1','1','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('27','10','Tag threshold','How many topics must be tagged with a certain tag to show it in the \\\"tagged\\\" view?','tagging_threshold','input','1','1','','2','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('28','2','Use clean URLs?','Do you wish to enable clean URLs like /topic/32? Requires you to edit your .htaccess file','clean_urls','yes_no','0','0','','3','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('30','2','Log SQL Commands?','Enabling this will log every SQL command run. This will add additional load to the server and should only be turned on if you\'re concerned about security or for debugging purposes.','log_sql_commands','yes_no','0','0','','5','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('31','3','Use word verification when posting?','Do you wish to require word verification for guests that are posting? Please note that if word verification is disabled above, then this setting will have no effect.','use_word_verification_posting','yes_no','1','1','','4','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('32','11','Use trash can?','If disabled, these options will have no effect','use_trash_can','yes_no','0','0','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('33','11','Trash can forum','Select the forum from the dropdown','trash_can_forum','forum_select','0','0','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('34','3','Activate account lockdown?','If account lockdown is enabled then user accounts will be locked down after too many failed attempts.','account_lockdown','yes_no','1','1','','5','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('36','3','Account lockdown attempts','This setting controls how many tries the user have to enter his/her correct password.','account_lockdown_tries','input','5','5','','7','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('37','3','Account lockdown time','This setting controls how many attempts members have to correctly enter their password. If they exceed this number, then their IP address will be locked down temporarily.','account_lockdown_time','input','30','30','','8','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('38','2','Type of word verification to use?','Choose GD if unsure. If word verification doesn\'t work using either option, then disable it under Security.','img_engine','dropdown','gd','gd','gd:GD
imagemagick:ImageMagick','6','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('39','2','Path to ImageMagick convert binary','You can ignore this if you have GD set above','imagemagick_convert_path','textbox','/usr/bin/convert','/usr/bin/convert','','7','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('40','4','Enable registration?','Do you want to allow users to register on their own?','enable_registration','yes_no','1','1','','1','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('41','2','Redirect notice type','Set how people will be noticed about a redirection.','redirection_type','dropdown','html','html','silent:Don\'t display a message
html:On a redirection page
js:Using javascript','0','0');";
$inserts[] = "INSERT INTO icebb_settings VALUES('42','2','Default editor','Which editor would you like to be used as the default?','default_editor_style','dropdown','2','2','1:WYSIWYG Editor
2:Extended Editor
3:Basic Editor','8','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES('43','3','Show debug info?','Do you wish to show the debug info at the bottom of each page?','enable_debug','yes_no','1','1','','9','1');";
$inserts[] = "INSERT INTO icebb_settings VALUES (NULL , '4', 'Restrict sessions to IP address?', 'Do you want to restrict sessions to the IP address that was used to log in? This improves security, but may be inconvenient for some members.', 'session_restrict_ip', 'yes_no', '1', '1', '', '4', '1');";
$drops[] = "DROP TABLE IF EXISTS icebb_settings_sections";
$creates[]= "CREATE TABLE `icebb_settings_sections` (
  `st_id` int(11) NOT NULL default '0',
  `st_title` varchar(64) NOT NULL default '',
  `st_desc` TEXT NOT NULL default '',
  `st_sort` int(11) NOT NULL default '0',
  `st_hidden` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`st_id`)
) TYPE=MyISAM";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('1','General Settings','General settings, such as board name and paths','1','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('2','Advanced Options','Board URLs and paths. These shouldn\'t need to be changed unless you are changing web hosts.','2','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('3','Security','Settings that may affect the security of your board','3','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('4','Login & Registration','Settings that affect how users login and register','4','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('5','Board On/Off','Turn your board offline for maintenance, etc.','5','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('6','Search Engine Spiders','This allows you to change whether or not search engine\'s \\\"spiders\\\" will appear on the board, be logged, etc.','6','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('7','Cookies','Cookies are bits of information that web sites store on your computer. IceBB uses cookies to keep track of user\'s sessions and provide the auto-login feature. Here you can change how these cookies are stored.','8','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('8','Board Rules','Change the rules that your users must follow on your board.','7','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('10','Tagging','Options relating to IceBB\'s \\\"tagging\\\" features','10','0');";
$inserts[] = "INSERT INTO icebb_settings_sections VALUES('11','Trash Can','Set up a trash can to store deleted posts','11','0');";
$drops[] = "DROP TABLE IF EXISTS icebb_skin_macros";
$creates[]= "CREATE TABLE `icebb_skin_macros` (
  `id` int(11) NOT NULL,
  `skin_id` int(11) NOT NULL default '0',
  `string` varchar(255) NOT NULL default '',
  `replacement` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_skin_macros CHANGE `id` `id` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('1','1','QUICK_REPLY','<img src=\'skins/<#SKIN#>/images/q_reply.png\' alt=\'Quick Reply\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('2','1','ADD_REPLY','<img src=\'skins/<#SKIN#>/images/add_reply.png\' alt=\'Add Reply\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('3','1','NEW_TOPIC','<img src=\'skins/<#SKIN#>/images/new_topic.png\' alt=\'New Topic\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('4','1','ADD_REPLY_LOCKED','<img src=\'skins/<#SKIN#>/images/closed.png\' alt=\'Topic Locked\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('5','1','P_EDIT','<img src=\'skins/<#SKIN#>/images/edit.png\' alt=\'edit\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('6','1','P_DELETE','<img src=\'skins/<#SKIN#>/images/delete.png\' alt=\'X\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('44','1','CAT_ICON','<img style=\'padding-right:2px\' src=\'skins/<#SKIN#>/images/catPaper.png\' alt=\'\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('9','1','PLUS','<img src=\'skins/<#SKIN#>/images/plus.png\' border=\'0\' alt=\'+\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('10','1','MINUS','<img src=\'skins/<#SKIN#>/images/minus.png\' border=\'0\' alt=\'-\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('11','1','P_REPORT','<img src=\'skins/<#SKIN#>/images/report.png\' alt=\'Report post\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('12','1','P_REPLY','<img src=\'skins/<#SKIN#>/images/reply.png\' alt=\'Reply\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('13','1','T_NONEW','<img src=\'skins/<#SKIN#>/images/t_nonew.png\' alt=\'No new posts\' title=\'No new posts\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('14','1','T_NEW','<img src=\'skins/<#SKIN#>/images/t_new.png\' alt=\'New Post\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('15','1','T_LOCKED','<img src=\'skins/<#SKIN#>/images/t_lock.png\' alt=\'Locked\' title=\'Locked\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('16','1','F_NONEW','<img src=\'skins/<#SKIN#>/images/f_nonew.png\' alt=\'No new posts in this forum\' title=\'No new posts in this forum\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('17','1','F_NEW','<img src=\'skins/<#SKIN#>/images/f_new.png\' alt=\'New posts in this forum\' title=\'New posts in this forum\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('18','1','F_REDIRECT','<img src=\'skins/<#SKIN#>/images/f_redirect.png\' alt=\'Redirect\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('19','1','PIP','<img src=\'skins/<#SKIN#>/images/pip.png\' alt=\'*\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('20','1','STAR','<img src=\'skins/<#SKIN#>/images/star.png\' alt=\'*\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('21','1','STAR_OFF','<img src=\'skins/<#SKIN#>/images/star_off.png\' alt=\'\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('22','1','T_HOTNEW','<img src=\'skins/<#SKIN#>/images/t_hotnew.png\' alt=\'Hot topic - new posts\' title=\'Hot topic - no new posts\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('23','1','T_HOT','<img src=\'skins/<#SKIN#>/images/t_hot.png\' alt=\'Hot topic - no new posts\' title=\'Hot topic - no new posts\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('24','1','loading_ani','<img src=\'skins/<#SKIN#>/images/loading.png\' alt=\'\' />');";
$inserts[] = "INSERT INTO icebb_skin_macros VALUES('43','1','RSS_ICON','<img src=\'skins/<#SKIN#>/images/feed-icon.png\' alt=\'RSS Feed available\' />');";
$drops[] = "DROP TABLE IF EXISTS icebb_skins";
$creates[]= "CREATE TABLE `icebb_skins` (
  `skin_id` int(11) NOT NULL,
  `skin_name` varchar(64) NOT NULL default '',
  `skin_author` varchar(64) NOT NULL default '',
  `skin_site` varchar(255) NOT NULL default '',
  `skin_folder` varchar(255) NOT NULL default '',
  `skin_preview` varchar(255) NOT NULL default '',
  `skin_is_default` tinyint(1) NOT NULL default '0',
  `skin_is_hidden` tinyint(1) NOT NULL default '0',
  `skin_wrapper` mediumTEXT NOT NULL default '',
  `skin_macro_cache` mediumTEXT NOT NULL default '',
  `smiley_set` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`skin_id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_skins CHANGE `skin_id` `skin_id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_smilies";
$creates[]= "CREATE TABLE `icebb_smilies` (
  `id` int(11) NOT NULL,
  `smiley_set` varchar(64) NOT NULL default '',
  `code` varchar(64) NOT NULL default '',
  `image` varchar(255) NOT NULL default '',
  `clickable` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_smilies CHANGE `id` `id` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_smilies VALUES('1','default',':o','oh.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('2','default','&lt;_&lt;','glare.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('3','default','^_^','^_^.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('4','default',':D','biggrin.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('5','default',':P','tounge.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('6','default','LOL','lol.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('7','default',':ph34r:','ninja.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('8','default',':wub:','wub.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('9','default',':monkey:','monkey.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('10','default',':scyth:','scyth.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('11','default',':)','smile.gif','1');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('12','default','&gt;_&lt;','argh.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('13','default',':/','uneasy.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('14','default',':!:','exclam.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('15','default',':arrow_right:','arrows_right.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('16','default','O_o','O_o.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('17','default','O_O','omg.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('18','default',':nerd:','nerd.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('19','default',':(','sad.gif','0');";
$inserts[] = "INSERT INTO icebb_smilies VALUES('20','default',';)','wink.gif','0');";
$drops[] = "DROP TABLE IF EXISTS icebb_subscriptions";
$creates[]= "CREATE TABLE `icebb_subscriptions` (
  `sid` int(11) NOT NULL,
  `suid` int(11) NOT NULL default '0',
  `sforum` int(11) NOT NULL default '0',
  PRIMARY KEY  (`sid`)
) TYPE=MyISAM COMMENT='IceBB Subscriptions'";
$inserts[] = "ALTER TABLE icebb_subscriptions CHANGE `sid` `sid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_tagged";
$creates[]= "CREATE TABLE `icebb_tagged` (
  `tag_id` int(11) NOT NULL default '0',
  `tag_uid` int(11) NOT NULL default '0',
  `tag_type` varchar(255) NOT NULL default '',
  `tag_objid` int(11) NOT NULL default '0',
  `tag_time` int(11) NOT NULL default '0'
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_tags";
$creates[]= "CREATE TABLE `icebb_tags` (
  `id` int(11) NOT NULL,
  `tag` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `count` int(11) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_tags CHANGE `id` `id` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_tasks";
$creates[]= "CREATE TABLE `icebb_tasks` (
  `taskid` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL default '',
  `task_desc` TEXT NOT NULL default '',
  `task_short` varchar(64) NOT NULL default '',
  `task_lastrun` int(16) NOT NULL default '0',
  `task_nextrun` int(16) NOT NULL default '0',
  `task_file` varchar(255) NOT NULL default '',
  `task_day_wk` smallint(1) NOT NULL default '0',
  `task_day_mo` smallint(2) NOT NULL default '0',
  `task_hr` smallint(2) NOT NULL default '0',
  `task_min` smallint(2) NOT NULL default '0',
  `task_enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`taskid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_tasks CHANGE `taskid` `taskid` int(11) NOT NULL auto_increment";
$inserts[] = "INSERT INTO icebb_tasks VALUES('1','Hourly clean-out','Clean up old sessions, CAPTCHAs','cleanout','1162260794','1162264394','cleanout.task.php','0','0','1','0','1');";
$inserts[] = "INSERT INTO icebb_tasks VALUES('2','Update RSS Feeds','Updates the RSS feeds in the forums with a feed specified','update_rss','1152839772','1152843372','update_rss.task.php','0','0','1','0','1');";
$inserts[] = "INSERT INTO icebb_tasks VALUES('3','Daily optimize','Optimize database tables and rebuild caches','optimize','1162064468','1162068068','optimize.task.php','0','0','1','15','1');";
$drops[] = "DROP TABLE IF EXISTS icebb_topics";
$creates[]= "CREATE TABLE `icebb_topics` (
  `tid` int(11) NOT NULL,
  `forum` int(11) NOT NULL default '0',
  `icon` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `snippet` varchar(255) NOT NULL default '',
  `replies` int(11) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `starter` varchar(64) NOT NULL default '',
  `has_poll` tinyint(1) NOT NULL default '0',
  `rating` int(1) NOT NULL default '0',
  `lastpost_time` int(10) NOT NULL default '0',
  `lastpost_author` varchar(64) NOT NULL default '',
  `is_locked` tinyint(1) NOT NULL default '0',
  `is_pinned` tinyint(1) NOT NULL default '0',
  `is_hidden` tinyint(1) NOT NULL default '0',
  `moved_to` int(11) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM";
$inserts[] = "ALTER TABLE icebb_topics CHANGE `tid` `tid` int(11) NOT NULL auto_increment";
/*$drops[] = "DROP TABLE IF EXISTS icebb_topics_ratings";
$creates[]= "CREATE TABLE `icebb_topics_ratings` (
  `rid` int(11) NOT NULL,
  `rtid` int(11) NOT NULL default '0',
  `ruid` int(11) NOT NULL default '0',
  `rating` int(1) NOT NULL default '0',
  PRIMARY KEY  (`rid`)
) TYPE=MyISAM COMMENT='IceBB Topic Ratings'";
$inserts[] = "ALTER TABLE icebb_topics_ratings CHANGE `rid` `rid` int(11) NOT NULL auto_increment";*/
$drops[] = "DROP TABLE IF EXISTS icebb_uploads";
$creates[]= "CREATE TABLE `icebb_uploads` (
  `uid` int(11) NOT NULL,
  `uname` varchar(64) NOT NULL default '',
  `upath` varchar(255) NOT NULL default '',
  `usize` int(64) NOT NULL default '0',
  `uowner` int(11) NOT NULL default '0',
  `upid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`uid`)
) TYPE=MyISAM COMMENT='IceBB Uploads Table'";
$inserts[] = "ALTER TABLE icebb_uploads CHANGE `uid` `uid` int(11) NOT NULL auto_increment";
$drops[] = "DROP TABLE IF EXISTS icebb_users";
$creates[]= "CREATE TABLE `icebb_users` (
  `id` int(11) NOT NULL default '0',
  `username` varchar(64) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `pass_salt` varchar(32) NOT NULL default '',
  `avatar` varchar(255) NOT NULL default '',
  `avtype` varchar(64) NOT NULL default 'none',
  `avsize` varchar(16) NOT NULL default '',
  `title` varchar(64) NOT NULL default '',
  `email` varchar(64) NOT NULL default '',
  `user_group` mediumint(11) NOT NULL default '0',
  `posts` int(11) NOT NULL default '0',
  `joindate` int(10) NOT NULL default '0',
  `siggie` TEXT NOT NULL default '',
  `temp_ban` int(16) NOT NULL default '0',
  `notepad` TEXT NOT NULL default '',
  `location` varchar(100) NOT NULL default '',
  `gender` enum('m','f','u') NOT NULL default 'u',
  `birthdate` int(16) NOT NULL default '0',
  `interests` TEXT NOT NULL default '',
  `icq` int(12) NOT NULL default '0',
  `msn` varchar(100) NOT NULL default '',
  `yahoo` varchar(100) NOT NULL default '',
  `aim` varchar(100) NOT NULL default '',
  `jabber` varchar(64) NOT NULL default '',
  `url` varchar(64) NOT NULL default '',
  `email_member` enum('0','1') NOT NULL default '0',
  `email_admin` enum('0','1') NOT NULL default '1',
  `new_pms` int(11) NOT NULL default '0',
  `gmt` char(3) NOT NULL default '0',
  `dst` tinyint(1) NOT NULL default '0',
  `date_format` varchar(64) NOT NULL default 'F j, Y @ g:i A',
  `view_av` enum('0','1') NOT NULL default '1',
  `view_sig` enum('0','1') NOT NULL default '1',
  `ip` varchar(16) NOT NULL default '',
  `last_visit` int(16) NOT NULL default '0',
  `warn_level` int(11) NOT NULL default '0',
  `banned_from` TEXT NOT NULL default '',
  `skinid` int(11) NOT NULL default '0',
  `langid` varchar(5) NOT NULL default '0',
  `ftread` TEXT NOT NULL default '',
  `editor_style` int(1) NOT NULL default '0',
  `quick_edit` tinyint(1) NOT NULL default '0',
  `disable_pm` tinyint(1) NOT NULL default '0',
  `disable_post` tinyint(1) NOT NULL default '0',
  `last_post` int(32) NOT NULL default '0',
  `away` enum('0','1') NOT NULL default '0',
  `away_reason` TEXT NOT NULL default '',
  `buddies` TEXT NOT NULL default '',
  `view_smileys` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0";
$drops[] = "DROP TABLE IF EXISTS icebb_users_validating";
$creates[]= "CREATE TABLE `icebb_users_validating` (
  `id` varchar(32) NOT NULL default '',
  `user` int(11) NOT NULL default '0',
  `email` varchar(64) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `time` int(16) NOT NULL default '0'
) TYPE=MyISAM";
$drops[] = "DROP TABLE IF EXISTS icebb_wordfilters";
$creates[]= "CREATE TABLE `icebb_wordfilters` (
  `bw_id` int(11) NOT NULL,
  `bw_word` varchar(255) NOT NULL default '',
  `bw_replacement` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`bw_id`)
) TYPE=MyISAM COMMENT='IceBB Word Filters'";
$inserts[] = "ALTER TABLE icebb_wordfilters CHANGE `bw_id` `bw_id` int(11) NOT NULL auto_increment";

// manually added - 02/18/08
$inserts[] = "INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
VALUES (
NULL , '4', 'Enable OpenID?', 'Do you want to allow users to login using their <a href=''http://openid.net/'' target=''_blank''>OpenID</a>?', 'enable_openid', 'yes_no', '1', '1', '', '2', '1'
);";
$creates[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_associations` (
  `server_url` blob NOT NULL,
  `handle` varchar(255) NOT NULL default '',
  `secret` varchar(2047) default NULL,
  `issued` int(11) default NULL,
  `lifetime` int(11) default NULL,
  `assoc_type` varchar(64) default NULL,
  PRIMARY KEY  (`server_url`(255),`handle`)
) ENGINE=InnoDB;";
$creates[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_nonces` (
  `nonce` char(8) NOT NULL default '',
  `expires` int(11) default NULL,
  PRIMARY KEY  (`nonce`),
  UNIQUE KEY `nonce` (`nonce`)
) ENGINE=InnoDB;";
$creates[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_settings` (
  `setting` varchar(128) NOT NULL default '',
  `value` blob,
  PRIMARY KEY  (`setting`),
  UNIQUE KEY `setting` (`setting`)
) ENGINE=InnoDB;";
$creates[] = "CREATE TABLE IF NOT EXISTS `icebb_openid_urls` (
  `uid` int(11) NOT NULL default '0',
  `url` varchar(255) NOT NULL,
  PRIMARY KEY  (`url`)
) ENGINE=MyISAM;";

$inserts[] = "ALTER TABLE `icebb_users` ADD `login_key` VARCHAR( 32 ) NOT NULL ;";
$inserts[] = "UPDATE icebb_users SET login_key=MD5(UNIX_TIMESTAMP() + RAND())";

$inserts[] = "INSERT INTO `icebb_settings_sections` ( `st_id` , `st_title` , `st_desc` , `st_sort` , `st_hidden` )
		VALUES (
		'12', 'CPU Saving', 'Settings that will enable you to disable certain features that will decrease the load on large boards.', '12', '0'
		);";

$inserts[] ="INSERT INTO `icebb_settings` ( `setting_id` , `setting_group` , `setting_title` , `setting_desc` , `setting_key` , `setting_type` , `setting_value` , `setting_default` , `setting_php` , `setting_sort` , `setting_protected` )
		VALUES (
		NULL , '12', 'Disable recent actions', 'This will disable the ''recent actions'' on the main view.', 'cpu_disable_recent_actions', 'yes_no', '0', '0', '', '1', '1'
		), (
		NULL , '12', 'Require login to search', 'If this is turned on, then users who are not logged in will be denied access to the search feature.', 'cpu_login_to_search', 'yes_no', '0', '0', '', '2', '1'
		), (
		NULL , '12', 'Disable online members', 'If this is turned on then the ''users online'' wont be generated and displayed.', 'cpu_disable_online_members', 'yes_no', '0', '0', '', '3', '1'
		), (
		NULL , '12', 'Disable ''users viewing forum/topic''', 'If this is turned on then the ''users viewing forum/topic'' wont be generated and displayed.', 'cpu_disable_users_viewing', 'yes_no', '0', '0', '', '4', '1'
		), (
		NULL , '12', 'Show birthdays', 'If this is turned on then the birthdays of the current day will be shown on the main page.', 'cpu_show_birthdays', 'yes_no', '1', '1', '', '5', '1'
		);";

// ALTER TABLE `icebb_openid_urls` DROP PRIMARY KEY ;
// ALTER TABLE `icebb_openid_urls` CHANGE `url` `url` VARCHAR( 255 ) NOT NULL  ;
// #ALTER TABLE `icebb_openid_urls` ADD UNIQUE (`uid`);
// ALTER TABLE `icebb_openid_urls` ADD PRIMARY KEY ( `url` );
?>
