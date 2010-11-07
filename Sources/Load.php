<?php
/*****************************************************************************/
/* Load.php                                                                  */
/*****************************************************************************/
/* YaBB: Yet another Bulletin Board                                          */
/* Open-Source Project started by Zef Hemel (zef@zefnet.com)                 */
/* Software Version: YaBB SE                                                 */
/* ========================================================================= */
/* Software Distributed by:    http://www.yabb.info                          */
/* Support, News, Updates at:  http://www.yabb.info/community                */
/*                             http://yabb.xnull.com/community               */
/* ========================================================================= */
/* Copyright (c) 2001-2002 Lewis Media - All Rights Reserved                 */
/* Software by: The YaBB Development Team                                    */
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

$loadplver="YaBB SE 1.3.1";

/* this function is called from index.php - it loads the cookies for the board
   and places the critical variables into the right place
		$username - the username of the logged in person, or 'Guest'
		$password - the doubly encrypted password stored in the cookie*/
function LoadCookie (){
	global $cookiepassword,$cookieusername,$password,$username,$HTTP_COOKIE_VARS;

	if( isset($HTTP_COOKIE_VARS[$cookiepassword]) && isset($HTTP_COOKIE_VARS['expiretime']) && $HTTP_COOKIE_VARS['expiretime'] > time())
	{
		$password = $HTTP_COOKIE_VARS[$cookiepassword];
		$username = (isset($HTTP_COOKIE_VARS[$cookieusername]) && $HTTP_COOKIE_VARS[$cookieusername] != "") ? $HTTP_COOKIE_VARS[$cookieusername] : 'Guest';
	}
	else
	{
		/* clear the cookies
		   note that in php, setCookie is called in reverse order - so we set it to an
		   expired time 'after' clearing it */
		// no longer in reverse order -- dbm
		setCookie($cookieusername,'',time()-3600,"/");
		setCookie($cookieusername);
		setCookie($cookiepassword,'',time()-3600,"/");
		setCookie($cookiepassword);

		/* clear the password and the username */
		$password = '';
		$username = 'Guest';
	}
}

/* This function is called from index.php to load the list of moderators,
   their realnames, and it loads the variables from the settings table.*/
function LoadBoard()
{
	global $moderators,$threadid,$yyThreadLine,$txt,$num,$thread,$board,$currentboard;
	global $caller,$modSettings,$realNames,$db_prefix,$isAnnouncement;

	/* load the settings from the table 'settings' and put them into
	   an array called modSettings */
	$modSettings = array();
	$request = mysql_query("SELECT variable,value FROM {$db_prefix}settings WHERE (variable!='news' && variable!='agreement')");
	while ($row = mysql_fetch_row($request))
			$modSettings["$row[0]"] = $row[1];
	// explode any lists
	$modSettings['karmaMemberGroups'] = explode(",",$modSettings['karmaMemberGroups']);

	/* load the moderators for this board, if the board is specified */
	if ($board != '')
	{
		$request = mysql_query ("SELECT b.moderators,b.isAnnouncement FROM {$db_prefix}boards as b WHERE (b.ID_BOARD='$currentboard')");
		/* if there aren't any, skip */
		if (mysql_num_rows($request) > 0)
		{
			$row = mysql_fetch_array($request);
			$isAnnouncement = $row['isAnnouncement'];
			$moderators = explode(",",trim($row['moderators']));

			/* now load the real names of the moderators
			   into the realNames array */
			for ($i = 0; $i < sizeof($moderators); $i++)
			{
				$moderators[$i] = trim($moderators[$i]);
				LoadRealName ($moderators[$i]);
			}
		}
		else
		{
			$moderators = array();
			$isAnnouncement = 0;
		}
	}
}

/* load the list of censored words into an associative array.
   Each item is $censored['vulgar term'] = 'replacement' */
function LoadCensorList () {
	global $censored,$txt,$db_prefix;
	$request = mysql_query("SELECT vulgar,proper FROM {$db_prefix}censor WHERE 1");
	if (!$request)
		fatal_error("205 $txt[106]: $txt[23] censor");

	$censored = array();
	while ($row = mysql_fetch_row($request))
		$censored[trim($row[0])] = trim($row[1]);
}

/*	Replace all vulgar word with respective proper words
	Substring or whole words										*/
function CensorTxt(&$Text) {
	global $censored,$modSettings;

	if (!isset($censored))
		LoadCensorList();

	foreach ($censored as $vulgar => $proper) {
		if ($modSettings['censorWholeWord']=='0')
			$Text = preg_replace("/$vulgar/i", $proper, $Text);
		else
			$Text = preg_replace("/\b$vulgar\b/i", $proper, $Text);
	}
}

/* Load the users settings into the array settings.  This array has
   been kept as close to the settings array in Y1G as possible*/
function LoadUserSettings () {
	global $username,$settings,$password,$action,$realname,$realemail,$yySetCookies,$pwseed,$HTTP_COOKIE_VARS;
	global $cookieusername,$cookiepassword,$pwseed,$ID_MEMBER,$realNames,$db_prefix;

	/* Only load this stuff if the username isn't Guest */
	if($username != 'Guest')
	{
		$request = mysql_query("SELECT passwd,realName,emailAddress,websiteTitle,websiteUrl,signature,posts,memberGroup,ICQ,AIM,YIM,gender,personalText,avatar,dateRegistered,location,birthdate,timeFormat,timeOffset,hideEmail,ID_MEMBER,memberName,MSN FROM {$db_prefix}members WHERE memberName='$username' LIMIT 1");
		/* If we found the user... */
		if (mysql_num_rows($request) != 0)
		{
			/* Initialize the settings array */
			$settings = mysql_fetch_row($request);

			/* compare a crypted version of the password in the database
			   with the password stored in the cookie.  Yes, the password
			   stored in the cookie is doubly encrypted */
			$spass = crypt($settings[0],$pwseed);
			if ($spass != $password && $action != 'logout')
			{
				$username = '';
			}
			else
			{

				// now lets get the additional membergroups
				// this will eventually need to be merged into a single query above
/*				$groups = array ($settings[7]);
				$request = mysql_query("SELECT ID_GROUP FROM {$db_prefix}members_groups WHERE ID_MEMBER=$settings[20]");
				while ($row = mysql_fetch_row($request))
					$groups[]=$row[0];
*/
				$username = $settings[21];
				$realname = isset($settings[1])?$settings[1]:$username;
				$realNames[$username] = $realname;
				$realemail = $settings[2];
				$ID_MEMBER = $settings[20];
			}
		}
		/* Otherwise clear everything */
		else
		{
			$username = '';
		}
	}
	/* If the user is a guest, initialize all the critial user settings */
	if ($username == '' || $username == 'Guest') {
		$username = 'Guest';
		$password = '';
		$settings = array(7=>'',18=>'0');
		$realname = '';
		$realemail = '';
		$ID_MEMBER = '-1';
		$HTTP_COOKIE_VARS = array();
	}
}

/* This is called from display, and from search */
function LoadUser ($user){
	global $userprofile,$icqad,$yimon,$modinfo,$memberinfo,$memberstar,$groupinfo,$db_prefix;
	global $scripturl;
	/* If the user isn't yet set, load it */
	if (!isset($userprofile[$user])){
		$request = mysql_query("SELECT * FROM {$db_prefix}members WHERE memberName='$user' LIMIT 1");
		if (mysql_num_rows($request)==0){ return (0); }
		$userprofile[$user] = mysql_fetch_array($request);
		if (!isset($userprofile[$user]['realName'])){ $userprofile[$user]['realName'] = $user; }
		if (!isset($userprofile[$user]['signature'])){ $userprofile[$user]['signature'] = ''; }
		if (!isset($userprofile[$user]['websiteUrl'])){ $userprofile[$user]['websiteUrl'] = ''; }
		if (!isset($userprofile[$user]['websiteTitle'])){ $userprofile[$user]['websiteUrl'] = ''; }
		if (!isset($userprofile[$user]['location'])){ $userprofile[$user]['location'] = ''; }
		if (!isset($userprofile[$user]['ICQ'])){ $userprofile[$user]['ICQ'] = ''; }
		$icqad[$user] = '';
		if (!isset($userprofile[$user]['AIM'])){ $userprofile[$user]['AIM'] = ''; }
		if (!isset($userprofile[$user]['YIM'])){ $userprofile[$user]['YIM'] = ''; }
		$yimon[$user] = '';
        if (!isset($userprofile[$user]['MSN'])){ $userprofile[$user]['MSN'] = ''; }

		if (!isset($userprofile[$user]['gender'])){ $userprofile[$user]['gender'] = ''; }
		if (!isset($userprofile[$user]['personalText'])){ $userprofile[$user]['personalText'] = ''; }
		if (!isset($userprofile[$user]['memberGroup'])){ $userprofile[$user]['memberGroup'] = ''; }
		$modinfo[$user] = '';
		$memberinfo[$user] = '';
		$memberstar[$user] = '';
		$groupinfo[$user] = '';
		if (!isset($userprofile[$user]['avatar'])){ $userprofile[$user]['avatar'] = ''; }
	}
	return 1;
}


/* Load the user's settings as used in display, IM or search */
function LoadUserDisplay ($user){
	global $userprofile,$yyUDLoaded,$userpic_width,$userpic_height,$userpic_tmpwidth,$scripturl;
	global $message,$enable_ubbc,$icqad,$yimon,$showgenderimage,$showusertext,$showuserpic,$sender;
	global $censored,$GodPostNum,$SrPostNum,$FullPostNum,$JrPostNum,$memberinfo,$imagesdir,$txt;
	global $userpic_tmpheight,$username,$allowpics,$sm,$membergroups,$memberstar,$facesurl,$img;
	global $menusep,$color,$cgi,$modinfo,$groupinfo,$realname,$db_prefix,$modSettings,$moderators,$settings;

	/* If the user is already loaded, exit */
	if (isset($yyUDLoaded[$user]))
		return 1;

	/* load the user */
	$set = LoadUser($user);

	/* if the user couldn't be loaded, load garbage*/
	if ($set == 0)
	{
		$userprofile[$user] = array();
		$userprofile[$user]['realName'] = $user;
		$userprofile[$user]['signature'] = '';
		$userprofile[$user]['websiteUrl'] = '';
		$userprofile[$user]['websiteUrl'] = '';
		$userprofile[$user]['location'] = '';
		$userprofile[$user]['avatar'] = '';
		$userprofile[$user]['hideEmail'] = '0';
		$userprofile[$user]['ICQ'] = '';
		$icqad[$user] = '';
		$userprofile[$user]['AIM'] = '';
		$userprofile[$user]['YIM'] = '';
        $userprofile[$user]['MSN'] = '';
		$yimon[$user] = '';
		$userprofile[$user]['gender'] = '';
		$userprofile[$user]['personalText'] = '';
		$userprofile[$user]['memberGroup'] = '';
		$userprofile[$user]['websiteUrl_IM'] = '';
		$userprofile[$user]['posts'] = '';
		$userprofile[$user]['emailAddress'] = '';
		$modinfo[$user] = '';
		$memberinfo[$user] = '';
		$memberstar[$user] = '';
		$groupinfo[$user] = '';
		$yyUDLoaded[$user]=0;
		return 0;
	}

	/* if the user pic limits are specified, implemen them, other wise, no restrictions are present */
	$userpic_tmpwidth = (($userpic_width != "") ? " width=\"$userpic_width\"" : "");
	$userpic_tmpheight = (($userpic_height != "") ? " height=\"$userpic_height\"" : "");

	/* Load the website image/link stuff */
	$userprofile[$user]['websiteUrl_IM'] = (($userprofile[$user]['websiteUrl']  != "") ? "<a href=\"".$userprofile[$user]['websiteUrl']."\" target=\"_blank\">$img[im_website]</a>$menusep" : "");
	if ($sm == 1)
		$userprofile[$user]['websiteUrl'] = (($userprofile[$user]['websiteUrl'] != "") ? "<a href=\"".$userprofile[$user]['websiteUrl']."\" target=\"_blank\">$img[website_sm]</a>$menusep" : "");
	else
		$userprofile[$user]['websiteUrl'] = (($userprofile[$user]['websiteUrl']  != "") ? "<a href=\"".$userprofile[$user]['websiteUrl']."\" target=\"_blank\">$img[website]</a>$menusep" : "");

	/* load the signature, replace the breaks in it */
	$breaks = array("\n\r","\r\n","\n","\r");
	$userprofile[$user]['signature'] = str_replace($breaks,"<br>",$userprofile[$user]['signature']);
	$userprofile[$user]['signature'] = (($userprofile[$user]['signature'] != "") ? "<hr width='100%' size=1 class='windowbg3'><font size=1>".$userprofile[$user]['signature']."</font>" : "");

	/* # do some ubbc on the signature if enabled */
	if($enable_ubbc)
		$userprofile[$user]['signature'] = DoUBBC($userprofile[$user]['signature']);

	/* ICQ and AIM, and YIM should be initialized in load user */
	if( $userprofile[$user]['ICQ'] != "" && is_numeric($userprofile[$user]['ICQ']) ) {
		$icqad[$user] = "<a href=\"http://wwp.icq.com/scripts/search.dll?to=".$userprofile[$user]['ICQ']."\" target=\"_blank\"><img src=\"$imagesdir/icqadd.gif\" alt=\"".$userprofile[$user]['ICQ']."\" border=0></a>";
		$userprofile[$user]['ICQ'] = "<a href=\"$cgi&amp;action=icqpager&amp;UIN=".$userprofile[$user]['ICQ']."\" target=\"_blank\"><img src=\"http://web.icq.com/whitepages/online?icq=".$userprofile[$user]['ICQ']."&amp;img=5\" alt=\"".$userprofile[$user]['ICQ']."\" border=0></a>";
	}
	$userprofile[$user]['AIM'] = ($userprofile[$user]['AIM'] != "") ? "<a href=\"aim:goim?screenname=".$userprofile[$user]['AIM']."&amp;message=Hi.+Are+you+there?\"><img src=\"$imagesdir/aim.gif\" alt=\"".$userprofile[$user]['AIM']."\" border=0></a>" : "";
	if( $userprofile[$user]['YIM'] != "" ) {
		$yimon[$user] = "<a href=\"http://edit.yahoo.com/config/send_webmesg?.target={$userprofile[$user]['YIM']}\"><img SRC=\"http://opi.yahoo.com/online?u=".$userprofile[$user]['YIM']."&amp;m=g&amp;t=0\" BORDER=0 alt=\"".$userprofile[$user]['YIM']."\"></a>";
	}
 
    $userprofile[$user]['MSN'] = ($userprofile[$user]['MSN'] != "") ? "<a href=javascript:MsgrApp.LaunchAddContactUI(\"".$userprofile[$user][MSN]."\")><img src=\"$imagesdir/msnadd.gif\" border=\"0\"></a>&nbsp;&nbsp;<a id=\"lll\" href=javascript:MsgrApp.LaunchIMUI(\"".$userprofile[$user][MSN]."\")><img src=\"$imagesdir/msntalk.gif\" border=\"0\"></a>" : "";

	/* if showing the gender image, and if the gender is specified */
	if( $showgenderimage && $userprofile[$user]['gender'] != "") {
		$userprofile[$user]['gender'] = (stristr($userprofile[$user]['gender'],"Female") ? 'Female' : 'Male');
		$userprofile[$user]['gender'] = "$txt[231]: <img src=\"$imagesdir/".$userprofile[$user]['gender'].".gif\" border=0 alt=\"".$userprofile[$user]['gender']."\"><br>";
	}
	else
	{
		$userprofile[$user]['gender'] = '';
	}

	/* if user text is enabled, add a <br> other wise erase it */
	$userprofile[$user]['personalText'] = $showusertext ? $userprofile[$user]['personalText']."<br>" : '';

	/* user pics is enabled */
	if( $showuserpic && $allowpics) {
		$userprofile[$user]['avatar'] = ($userprofile[$user]['avatar']=='') ? 'blank.gif' : $userprofile[$user]['avatar'];
		$userprofile[$user]['avatar'] = stristr($userprofile[$user]	['avatar'],'http://') ? "<br><img src=\"{$userprofile[$user]['avatar']}\"$userpic_tmpwidth$userpic_tmpheight border=\"0\" alt=\"\"><br><br>" : "<br><img src=\"$facesurl/{$userprofile[$user]['avatar']}\" border=\"0\" alt=\"\"><br><br>";
	}
	else {
		$userprofile[$user]['avatar'] = '<BR>';
	}

	/* ### Censor it ### */

	CensorTxt($userprofile[$user]['signature']);
	CensorTxt($userprofile[$user]['personalText']);

	/* create the memberinfo and memberstars entries */
	if( $userprofile[$user]['posts'] > $GodPostNum ) {
		$memberinfo[$user] = "$membergroups[6]";
		$memberstar[$user] = "<img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\">";
	}
	elseif( $userprofile[$user]['posts'] > $SrPostNum ) {
		$memberinfo[$user] = "$membergroups[5]";
		$memberstar[$user] = "<img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\">";
	}
	elseif( $userprofile[$user]['posts'] > $FullPostNum ) {
		$memberinfo[$user] = "$membergroups[4]";
		$memberstar[$user] = "<img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\">";
	}
	elseif( $userprofile[$user]['posts'] > $JrPostNum ) {
		$memberinfo[$user] = "$membergroups[3]";
		$memberstar[$user] = "<img src=\"$imagesdir/star.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/star.gif\" border=0 alt=\"*\">";
	}
	else {
		$memberinfo[$user] = "$membergroups[2]";
		$memberstar[$user] = "<img src=\"$imagesdir/star.gif\" border=0 alt=\"*\">";
	}

	/* If this user is a moderator, and we aren't calling this from the IM */
    $modcheck=LoadRealName($user);

	if( $userprofile[$user]['memberGroup'] == 'Administrator' ) {
		$memberstar[$user] = "<img src=\"$imagesdir/staradmin.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/staradmin.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/staradmin.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/staradmin.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/staradmin.gif\" border=0 alt=\"*\">";
		$memberinfo[$user] = "<B>$membergroups[0]</B>";
	}
	elseif( $userprofile[$user]['memberGroup'] == 'Global Moderator' ) {
		$memberstar[$user] = "<img src=\"$imagesdir/stargmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/stargmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/stargmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/stargmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/stargmod.gif\" border=0 alt=\"*\">";
		$memberinfo[$user] = "<B>$membergroups[7]</B>";
	}
	elseif($sender != "im" &&  in_array($user,$moderators)) {
		$modinfo[$user] = "<b>$membergroups[1]</b><BR>";
		$memberstar[$user] = "<img src=\"$imagesdir/starmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/starmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/starmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/starmod.gif\" border=0 alt=\"*\"><img src=\"$imagesdir/starmod.gif\" border=0 alt=\"*\">";
	}

	// if the karma mod is enabled, append the karma information after the stars
	$karmaString = '';
	if ($modSettings['karmaMode'] == '1')
	{
		$karmaString = "<br>$modSettings[karmaLabel] ".($userprofile[$user]['karmaGood']-$userprofile[$user]['karmaBad']);
	}
	else if ($modSettings['karmaMode'] == '2')
		$karmaString = "<br>$modSettings[karmaLabel] +{$userprofile[$user]['karmaGood']}/-{$userprofile[$user]['karmaBad']}";

	if ($userprofile[$user]['posts'] < $modSettings['karmaMinPosts'] || ($modSettings['karmaMemberGroups'][0]!='' && sizeof($modSettings['karmaMemberGroups'])==1 && !in_array($settings[7],$modSettings['karmaMemberGroups'])) || $modSettings['karmaMode'] == '0' || $username=='Guest');
	else
	{
		$karmaString .= "<br><a href=\"$scripturl?action=modifykarma;karmaAction=applaud;uid={$userprofile[$user]['ID_MEMBER']}\">$modSettings[karmaApplaudLabel]</a> <a href=\"$scripturl?action=modifykarma;karmaAction=smite;uid={$userprofile[$user]['ID_MEMBER']}\">$modSettings[karmaSmiteLabel]</a>";
	}
	if ($modSettings['karmaMode'] != '0')
	{
		$memberstar[$user] .= $karmaString;
	}


	if( $userprofile[$user]['memberGroup'] != "" && $userprofile[$user]['memberGroup'] != 'Administrator' && $userprofile[$user]['memberGroup'] != 'Global Moderator')
	{
		$groupinfo[$user] = $userprofile[$user]['memberGroup']."<BR>";
	}
	if( $userprofile[$user]['memberGroup'] != 'Administrator' && $userprofile[$user]['memberGroup'] != 'Global Moderator')
	{
		$memberinfo[$user] = "$modinfo[$user]$groupinfo[$user]$memberinfo[$user]";
	}
	if($userprofile[$user]['posts'] > 100000) { $userprofile[$user]['posts'] = "$txt[683]"; }

	/* we've successfully loaded the user */
	$yyUDLoaded[$user] = 1;
	return 1;
}

/* this is called from admin.php only - it's used to load that list
   of administrators on the main admin page */
function LoadAdmins (){
	global $administrators,$db_prefix;
	is_admin();

	$admins = array();
	$request = mysql_query("SELECT realName,memberName FROM {$db_prefix}members WHERE memberGroup='Administrator'");
	while ($row = mysql_fetch_array($request)) {
		$euser=urlencode($row['memberName']);
		$admins[] = "<a href=\"$scripturl?action=viewprofile&amp;username=$euser\">$row[realName]</a>";
	}
	$administrators = implode("<font size=1>,</font>\n",$admins);
}

/* this is called from admin.php - shows the number of clicks
   we've logged.  I don't know why this is a separate function,
   but it was in Y1G and so it remains */
function LoadLogCount (){
	global $yyclicks,$db_prefix;
	is_admin();
	$request = mysql_query("SELECT COUNT(*) FROM {$db_prefix}log_clicks WHERE 1");
	list($yyclicks) = mysql_fetch_row($request);
}

/* this too, is only called from admin.php. It loads
   all the files that haven't already been loaded, so we can
   check the versions set at the top of them */
function loadfiles () {
	global $boardindexplver,$displayplver,$icqpagerplver,$instantmessageplver;
	global $lockthreadplver,$loginoutplver,$maintenanceplver,$manageboardsplver,$managecatsplver;
	global $memberlistplver,$modifymessageplver,$movethreadplver,$notifyplver,$postplver;
	global $profileplver,$recentplver,$registerplver,$removeoldthreadsplver,$removethreadplver;
	global $searchplver,$sendtopicplver,$messageindexplver,$adminplver,$pollplver;
	global $boarddir,$sourcedir,$language,$modsettingsphpver,$karmaphpver,$repairboardsphpver;
	include_once ("$boarddir/$language");
	include_once ("$sourcedir/BoardIndex.php");
	include_once ("$sourcedir/Display.php");
	include_once ("$sourcedir/ICQPager.php");
	include_once ("$sourcedir/InstantMessage.php");
	include_once ("$sourcedir/Admin.php");
	include_once ("$sourcedir/Karma.php");
	include_once ("$sourcedir/LockThread.php");
	include_once ("$sourcedir/LogInOut.php");
	include_once ("$sourcedir/Maintenance.php");
	include_once ("$sourcedir/ManageBoards.php");
	include_once ("$sourcedir/ManageCats.php");
	include_once ("$sourcedir/Memberlist.php");
	include_once ("$sourcedir/MessageIndex.php");
	include_once ("$sourcedir/ModifyMessage.php");
	include_once ("$sourcedir/ModSettings.php");
	include_once ("$sourcedir/MoveThread.php");
	include_once ("$sourcedir/Notify.php");
	include_once ("$sourcedir/Post.php");
	include_once ("$sourcedir/Profile.php");
	include_once ("$sourcedir/Recent.php");
	include_once ("$sourcedir/Register.php");
	include_once ("$sourcedir/RemoveOldThreads.php");
	include_once ("$sourcedir/RemoveThread.php");
	include_once ("$sourcedir/Search.php");
	include_once ("$sourcedir/Security.php");
	include_once ("$sourcedir/SendTopic.php");
	include_once ("$sourcedir/Subs.php");
	include_once ("$sourcedir/Poll.php");
	include_once ("$sourcedir/RepairBoards.php");
}

/* this is, I guess, replacing FormatUserName.  All it does is
   check if the user's name has already been loaded, and if not,
   loads the user's real name into the array $realNames */
function LoadRealName ($username)
{
	global $realNames,$db_prefix;
    $username=mysql_escape_string(stripslashes($username));
	if (!isset($realNames[$username]))
	{
    	$request = mysql_query("SELECT realName FROM {$db_prefix}members WHERE memberName='$username' LIMIT 1");
		if (mysql_num_rows($request) == 0)
		{
			$realNames[$username]=$username;
		}
		else
		{
			$tmp = mysql_fetch_row($request);
			$realNames[$username]=$tmp[0];
		}
	}

	return $realNames[$username];
}
?>
