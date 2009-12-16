<?php
/*
+--------------------------------------------------------------------------
|  NovaBoard
|  ========================================
|  By The NovaBoard team
|  Released under the Artistic License 2.0
|  http://www.novaboard.net
|  ========================================|+--------------------------------------------------------------------------
|  autosuggest.php - This handles autosuggest areas
 
*/

define('NOVA_RUN', 1);

include "../../includes/config.php";
include "../../scripts/php/functions.php";
$aUsers_results="";
$aID="";
$aGroup="";

$limit=$_GET['limit'];
$limit=escape_string($limit);

if (isset($_GET['member'])){
$member=$_GET['member'];
$member=escape_string($member);

$queryautomember = "select ID, NAME, EMAIL, ROLE, BANNED, VERIFIED from {$db_prefix}members WHERE ID!='$member' ORDER BY NAME asc" ;
}
else{
$queryautomember = "select ID, NAME, EMAIL, ROLE, BANNED, VERIFIED from {$db_prefix}members ORDER BY NAME asc" ;
}
$resultautomember = mysql_query($queryautomember) or die("autosuggest.php - Error in query1") ;                                  
while ($resultsautomember = mysql_fetch_array($resultautomember)){
$id = $resultsautomember['ID'];	
$name = $resultsautomember['NAME'];
$users_role = $resultsautomember['ROLE'];
$email = $resultsautomember['EMAIL'];
$banned = $resultsautomember['BANNED'];
$verified = $resultsautomember['VERIFIED'];

	// PERMISSIONS! Can the recipient PM???!!!

		$queryautopm = "select CAN_PM from {$db_prefix}groups WHERE GROUP_ID='$users_role'" ;
		$resultautopm = mysql_query($queryautopm) or die("autosuggest.php - Error in query2") ;                                  
		while ($resultsautopm = mysql_fetch_array($resultautopm)){
		$can_pm_this_member = $resultsautopm['CAN_PM'];
		}
		
		if ($banned=='1' OR $verified=='0'){
			$can_pm_this_member="0";
		}
		
		if (isset($_GET['admin'])){
			$can_pm_this_member="1";
		}
		
			if ($can_pm_this_member=='1'){
			
			$querygroupdetails = "select GROUP_NAME, CAN_CHANGE_SITE_SETTINGS, CAN_CHANGE_FORUM_SETTINGS, GROUP_COLOR from {$db_prefix}groups WHERE GROUP_ID='$users_role'" ;
			$resultgroupdetails = mysql_query($querygroupdetails) or die("autosuggest.php - Error in query3") ;                                  
			while ($resultsgroupdetails = mysql_fetch_array($resultgroupdetails)){
				$group_name = $resultsgroupdetails['GROUP_NAME'];	
				$group_color = $resultsgroupdetails['GROUP_COLOR'];
				$group_change_site = $resultsgroupdetails['CAN_CHANGE_SITE_SETTINGS'];
				$group_change_forum = $resultsgroupdetails['CAN_CHANGE_FORUM_SETTINGS'];
				}

				if ($group_change_site=='1' OR $group_change_forum=='1'){
				$aGroup_results .= "$group_name,";
				}
				elseif($verified=='0'){
				$aGroup_results .= "***Unverified***,";
				}
				else{
				$aGroup_results .= ",";
				}
			if (isset($_GET['email'])){
				$aUsers_results .= "$email,";
				$aID_results .= "$id,";
			}
			else{
				$aUsers_results .= "$name,";
				$aID_results .= "$id,";	
			}
			}

	
	}

	$aUsers = explode(",",$aUsers_results);
	$aID = explode(",",$aID_results);
	$aGroup = explode(",",$aGroup_results);		
	
	$input = strtolower( $_GET['input'] );
	$len = strlen($input);
	$limit = isset($limit) ? (int) $limit : 0;
	
	
	$aResults = array();
	$count = 0;
	
	if ($len)
	{
		for ($i=0;$i<count($aUsers);$i++)
		{
			// had to use utf_decode, here
			// not necessary if the results are coming from mysql
			//
			if (strtolower(substr(utf8_decode($aUsers[$i]),0,$len)) == $input)
			{
				$count++;
				$aResults[] = array( "id"=>($aID[$i]) ,"value"=>$aUsers[$i] ,"role"=>$aGroup[$i]);
			}
			
			if ($limit && $count==$limit)
				break;
		}
	}
	
	
	
	
	
	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
	header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header ("Pragma: no-cache"); // HTTP/1.0
	
		header("Content-Type: application/json");
	
		echo "{\"results\": [";
		$arr = array();
		for ($i=0;$i<count($aResults);$i++)
		{
			$arr[] = "{\"id\": \"".$aResults[$i]['id']."\", \"value\": \"".$aResults[$i]['value']."\", \"info\": \"".$aResults[$i]['role']."\"}";
		}
		echo implode(", ", $arr);
		echo "]}";
?>