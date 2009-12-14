<?php
$menu_cats[]			= array(
		1				=> array("Settings","Settings for IceBB"),
		2				=> array("Users and Groups","Create, edit, and remove users and groups"),
		3				=> array("Forums","Create, edit, and remove forums"),
		5				=> array("Customize","Customize your IceBB to your liking"),
		4				=> array("Post Display","Manage BBCode, smilies, and other things that are displayed in posts"),
		6				=> array("Advanced","Manage advanced options such as caches and tasks"),
		7				=> array("Root Admin","See logs and manage sql"),
);

$menu_pages				= array(
	1					=> array(
									// settings will be automatically loaded :D 
								),
	2					=> array(
									array("Manage Users","act=users"),
									array("New User","act=users&func=new"),
									array("Prune Users","act=users&func=prune"),
									array("Manage Groups","act=groups"),
									array("New Group","act=groups&func=new"),
									array("Manage Permission Groups","act=groups&func=permgroups"),
									array("Manage Ranks","act=groups&func=ranks"),
									array("IP Tools","act=users&func=iptools"),
									array("Bulk Mail","act=bulkmail"),
								),
	3					=> array(
									array("Manage Forums","act=forums"),
									array("New Forum","act=forums&func=new"),
									array("Manage Announcements","act=forums&func=announce"),
									array("New Announcement","act=forums&func=new_announce"),
								),
	4					=> array(
									array("Smilies","act=smilies"),
									array("Word Filters","act=wordfilters"),
								),					
	5					=> array(
									array("Skins","act=skins&func=manage"),
									array("Languages","act=langs"),
									array("Plugins","act=plugins"),
								),
	6					=> array(
									array("Ban Options","act=ban"),
									//array("Manage Help","act=help_manager"),
									array("Manage Caches","act=cache"),
									array("Manage Tasks","act=tasks"),
									array("Recount","act=recount"),
								),
	7					=> array(
									array("Logs","act=logs"),
									array("SQL Manager","act=sql"),
								),
)
?>