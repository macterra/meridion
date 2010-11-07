<?php
/*****************************************************************************/
/* SSI.php                                                                   */
/*****************************************************************************/
/* YaBB: Yet another Bulletin Board                                          */
/* Open-Source Project started by Zef Hemel (zef@zefnet.com)                 */
/* Software Version: YaBB SE                                                 */
/* ========================================================================= */
/* Software Distributed by:    http://www.yabb.info                          */
/* Support, News, Updates at:  http://www.yabb.info/community                */
/* ========================================================================= */
/* Copyright (c) 2001-2002 Lewis Media - All Rights Reserved                 */
/* Software by: The YaBB SE Development Team                                 */
/*****************************************************************************/
/* This program is free software; you can redistribute it and/or modify it   */
/* under the terms of the GNU General Public License as published by the     */
/* Free Software Foundation; either version 2 of the License, or (at your    */
/* option) any later version.                                                */
/*                                                                           */
/* This program is distributed in the hope that it will be useful, but       */
/* WITHOUT ANY WARRANTY; without even the implied warranty of                */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General */
/* Public License for more details.                                          */
/*                                                                           */
/* The GNU GPL can be found in gpl.txt in this directory                     */
/*****************************************************************************/

$ssiphpver = "YaBB SE 1.1.2";

/*******************************   Settings   ********************************/
	$full_yabbse_path = "/home/httpd/html/yabbinfo/www/community"; 
	$showlatestcount = 8;
	$showBoard = 1;	// set to 1 to show the board the topic belongs to
	$showPoster = 1; // set to 1 to show the poster
	$showTime = 1;	// set to 1 to show the time of the post
/****************   DO NOT MODIFY ANYTHING BELOW THIS LINE!   ****************/
include_once ($full_yabbse_path."/Settings.php");
include_once ($full_yabbse_path."/".$language);
include_once ($full_yabbse_path."/Sources/Subs.php");
include_once ($full_yabbse_path."/Sources/Load.php");
$dbcon = mysql_connect($db_server, $db_user, $db_passwd);
mysql_select_db($db_name);

/* ### Log this click ### */
ClickLog();

/* ### Load the user's cookie (or set to guest) ### */
LoadCookie();

/* ### Load user settings ### */
LoadUserSettings();

if ($function == 'topPoster' || $function == 'recentTopics' || $function == 'whosOnline' || $function == 'welcome' || $function == 'menubar' || $function == 'logout' || $function == 'login') {
	$function(); // Execute Function
	exit;
}

function welcome() {
	global $username,$txt,$cgi,$db_prefix,$realname;
	$tmp = ($realname=='')?$username:$realname;
	$yyuname = ($username == 'Guest') ? "$txt[248] <b>$txt[28]</b>. $txt[249] <a href=\"$cgi;action=login\">$txt[34]</a> $txt[377] <a href=\"$cgi;action=register\">$txt[97]</a>." : "$txt[247] <b>$tmp</b>, ";
	$yyim = '';
	if($username != "Guest") {
		$request = mysql_query("SELECT COUNT(*) FROM {$db_prefix}instant_messages WHERE (toName='$username' && deletedBy!=1)");
		$temp = mysql_fetch_row($request);
		$mnum = $temp[0];
		if($mnum == "1") { $yyim = "$txt[152] <a href=\"$cgi;action=im\" class=\"YaBBbar\">$mnum $txt[471]</a>."; }
		else { $yyim = "$txt[152] <a href=\"$cgi;action=im\" class=\"YaBBbar\">$mnum $txt[153]</a>."; }
		if($maintenance) { $yyim .= "<BR><B>$txt[616]</B>"; }
	}
	print "$yyuname$yyim";
}

function menubar() {
global $scripturl, $menusep, $img, $settings, $username, $enable_notification, $cgi, $helpfile;
$yymenu = "<a href=\"$scripturl\">$img[home]</a>$menusep<a href=\"$helpfile\" target=_blank>$img[help]</a>$menusep<a href=\"$cgi;action=search\">$img[search]</a>";
   if($settings[7] == 'Administrator') { $yymenu = $yymenu.$menusep."<a href=\"$cgi;action=admin\">$img[admin]</a>"; }
   if($username == "Guest") { $yymenu .= $menusep."<a href=\"$cgi;action=login\">$img[login]</a>$menusep<a href=\"$cgi;action=register\">$img[register]</a>";
   } else {
      $yymenu .= "$menusep<a href=\"$cgi;action=profile;user=$username\">$img[profile]</a>";
      if($enable_notification) { $yymenu .= "$menusep<a href=\"$cgi;action=shownotify\">$img[notification]</a>"; }
      $yymenu .= "$menusep<a href=\"$cgi;action=logout\">$img[logout]</a>";
   }
print "$yymenu";
}


function logout() {
	global $username,$txt,$cgi,$db_prefix,$realname;
	$tmp = ($realname=='')?$username:$realname;
	if($username != "Guest") { $yylogout = "<a href=\"$cgi;action=logout\">$txt[108]</a>"; }
	print "$yylogout";
}

function recentTopics(){

	ob_end_clean();

	global $settings,$scripturl,$txt,$censored,$recentsender,$db_prefix, $post, $dummy,$showlatestcount,$showBoard,$showPoster,$showTime;

	# Load Censor List
	LoadCensorList();
	if (!isset($recentsender)) {$recentsender='';}

	$request = mysql_query("SELECT m.posterTime,m.subject,m.ID_TOPIC,t.ID_BOARD,m.posterName,m.ID_MEMBER,t.numReplies,t.ID_FIRST_MSG FROM {$db_prefix}messages as m,{$db_prefix}topics as t,{$db_prefix}boards as b,{$db_prefix}categories as c WHERE (m.ID_MSG=t.ID_LAST_MSG && t.ID_BOARD=b.ID_BOARD && b.ID_CAT=c.ID_CAT && (FIND_IN_SET('$settings[7]',c.memberGroups)!=0 || c.memberGroups='' || '$settings[7]' LIKE 'Administrator' || '$settings[7]' LIKE 'Global Moderator')) ORDER BY m.posterTime DESC LIMIT 0,$showlatestcount");

	$thepost = "";

	if( mysql_num_rows($request) > "0" ) {

		$post = array();

		while ($row = mysql_fetch_array($request)) {

			$request3 = mysql_query ("SELECT name FROM {$db_prefix}boards WHERE (ID_BOARD=$row[ID_BOARD]) LIMIT 1");
			$temp = mysql_fetch_row($request3);
			$bname = $temp[0];

			if ($row['ID_MEMBER'] != -1) {                  
				$request4 = mysql_query ("SELECT realName FROM {$db_prefix}members WHERE ID_MEMBER=$row[ID_MEMBER] LIMIT 1");
				$temp2 = mysql_fetch_row($request4);
				$dummy = "<a href=\"$scripturl?action=viewprofile;user=$row[posterName]\">$temp2[0]</a>";
			} else {
				$dummy = $row['posterName'];
			}
         
			$request2 = mysql_query ("SELECT subject FROM {$db_prefix}messages WHERE ID_MSG=$row[ID_FIRST_MSG] LIMIT 1");
			$row2 = mysql_fetch_array($request2);
			
			$dummy1 = $showBoard?"$txt[yse88] <a href=\"$scripturl?board=$row[ID_BOARD]\">$bname</a> ":"";
			$dummy2 = $showPoster?"$txt[525] $dummy ":"";
			$dummy3 = $showTime?timeformat($row['posterTime']):"";

			$thepost .="»&nbsp;<a href=\"$scripturl?board=$row[ID_BOARD];action=display;threadid=$row[ID_TOPIC];start=$row[numReplies]\" class=\"t11\">$row2[subject]</a> $dummy1$dummy2$dummy3<br>\n";

		}
	
	}

	foreach ($censored as $tmpa=>$tmpb) {
		$thepost = str_replace($tmpa,$tmpb,$thepost );
	}
	
	print $thepost;
}

function topPoster() {
	ob_end_clean();
	global $db_prefix,$scripturl;
	$request = mysql_query("SELECT memberName,realName FROM {$db_prefix}members ORDER BY posts DESC LIMIT 1");
	$row = mysql_fetch_array($request);
	print "<a href=\"$scripturl?action=viewprofile;user=$row[memberName]\">$row[realName]</a>";
}
function whosOnline()
{
	ob_end_clean();
	global $scripturl,$db_prefix,$txt;
	$guests = 0;
	$tmpusers = array();
	$request3 = mysql_query("SELECT identity FROM {$db_prefix}log_online WHERE 1 ORDER BY logTime DESC");

	while ($tmp = mysql_fetch_array($request3))
	{
		$identity = $tmp[0];
		$request4 = mysql_query("SELECT realName, memberGroup FROM {$db_prefix}members WHERE (memberName='$identity') LIMIT 1");
		if (mysql_num_rows($request4) > 0){
			$tmp = mysql_fetch_row($request4);
			if ($tmp[1]=="Administrator")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$identity\"><font color=\"red\">$tmp[0]</font></a>";
            elseif ($tmp[1]=="Global Moderator")
              $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$identity\"><font color=\"green\">$tmp[0]</font></a>";
            else
			  $tmpusers[] = "<a href=\"$scripturl?action=viewprofile;user=$identity\">$tmp[0]</a>";}
		else
			$guests ++;
	}
	$users = implode(", ",$tmpusers);
	$numusersonline = sizeof($tmpusers);
	print "$guests $txt[141], $numusersonline $txt[142]<br>$users";
}
function login()
{
	$user = 'user';
	$pass = 'pass';
	ob_end_clean();
	global $username,$cgi,$txt;
	print <<<EOT
		<form action="$cgi;action=login2" method="post">
<table border=0 cellspacing=1 cellpadding=0>
<tr><td align=right>$user:&nbsp;</td><td><input type=text name="user" size="9" value="$username"></td></tr>
<tr><td align=right>$pass:&nbsp;</td><td><input type=password name="passwrd" size="9"></td></tr>
<tr><td>&nbsp;</td><td><input type=hidden name="cookielength" value="$txt[yse50]"><input type="submit" value="$txt[34]"></td></tr></table></form>
EOT;
}
?>